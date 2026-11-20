<?php

use App\Libraries\DeadlockRetry;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

/**
 * DeadlockRetry: a write MySQL refused with 1213 (deadlock) or 1205 (lock
 * wait timeout) is run again; everything else is not (decision log, *Remote
 * load test, play rounds and deadlocks*, 21 November 2026).
 *
 * SQLite never raises these errors, so they are thrown by hand with the
 * codes the MySQLi driver passes on. The real ones were run on MySQL 8.
 *
 * @internal
 */
final class DeadlockRetryTest extends CIUnitTestCase
{
    /** @var list<int> waits asked for, in microseconds */
    private array $waits = [];

    protected function setUp(): void
    {
        parent::setUp();

        DeadlockRetry::$sleeper = function (int $microseconds): void {
            $this->waits[] = $microseconds;
        };

        $forge = Database::forge('tests');
        $forge->dropTable('retry_probe', true);
        $forge->addField(['id' => ['type' => 'INTEGER', 'auto_increment' => true], 'label' => ['type' => 'VARCHAR', 'constraint' => 20]]);
        $forge->addPrimaryKey('id');
        $forge->createTable('retry_probe');
    }

    protected function tearDown(): void
    {
        DeadlockRetry::$sleeper    = null;
        DeadlockRetry::$maxSeconds = DeadlockRetry::MAX_SECONDS;
        Database::connect('tests')->transException(false)->resetTransStatus();
        Database::forge('tests')->dropTable('retry_probe', true);
        parent::tearDown();
    }

    private static function lockError(int $code): DatabaseException
    {
        $message = $code === 1213 ? 'Deadlock found when trying to get lock; try restarting transaction' : 'Lock wait timeout exceeded; try restarting transaction';

        // The shape BaseConnection::query() throws: the driver's exception as the cause
        return new DatabaseException($message, $code, new DatabaseException($message, $code));
    }

    public function testRunRepeatsAfterADeadlockAndALockWaitTimeout(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        $result = DeadlockRetry::run($db, static function () use (&$calls): string {
            $calls++;

            if ($calls === 1) {
                throw self::lockError(1213);
            }

            if ($calls === 2) {
                throw self::lockError(1205);
            }

            return 'written';
        });

        $this->assertSame('written', $result);
        $this->assertSame(3, $calls);
        $this->assertCount(2, $this->waits);
        $this->assertGreaterThanOrEqual(10_000, $this->waits[0]);
        $this->assertLessThanOrEqual(80_000, $this->waits[1], 'the wait stays short: the request is still open');
    }

    public function testRunGivesUpAfterTheLastRun(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        try {
            DeadlockRetry::run($db, static function () use (&$calls): void {
                $calls++;

                throw self::lockError(1213);
            });
            $this->fail('The deadlock of the last run must reach the caller.');
        } catch (DatabaseException $e) {
            $this->assertSame(1213, $e->getCode());
        }

        $this->assertSame(DeadlockRetry::MAX_RUNS, $calls);
        $this->assertCount(DeadlockRetry::MAX_RUNS - 1, $this->waits);
    }

    public function testOtherErrorsAreNotRepeated(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        foreach ([new DatabaseException('Duplicate entry', 1062), new RuntimeException('not a database error', 1213)] as $error) {
            try {
                DeadlockRetry::run($db, static function () use (&$calls, $error): void {
                    $calls++;

                    throw $error;
                });
                $this->fail('The error must reach the caller.');
            } catch (Throwable $e) {
                $this->assertSame($error, $e);
            }
        }

        $this->assertSame(2, $calls);
        $this->assertSame([], $this->waits);
    }

    public function testRunInsideACallersTransactionRunsOnce(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        $db->transBegin();

        try {
            DeadlockRetry::run($db, static function () use (&$calls): void {
                $calls++;

                throw self::lockError(1213);
            });
            $this->fail('A deadlock has undone the caller\'s transaction: it must hear about it.');
        } catch (DatabaseException) {
            // expected
        } finally {
            $db->transRollback();
        }

        $this->assertSame(1, $calls);
    }

    public function testRunStopsRepeatingOnceTheTimeIsUp(): void
    {
        // A lock wait timeout arrives after 50 s: no second wait of that length
        DeadlockRetry::$maxSeconds = 0.0;
        $db                        = Database::connect('tests');
        $calls                     = 0;

        try {
            DeadlockRetry::run($db, static function () use (&$calls): void {
                $calls++;

                throw self::lockError(1205);
            });
            $this->fail('The error must reach the caller.');
        } catch (DatabaseException $e) {
            $this->assertSame(1205, $e->getCode());
        }

        $this->assertSame(1, $calls);
    }

    public function testTransactionRollsBackAndRunsAgain(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        $result = DeadlockRetry::transaction($db, static function () use ($db, &$calls): int {
            $calls++;
            $db->table('retry_probe')->insert(['label' => 'run ' . $calls]);

            if ($calls === 1) {
                // As on MySQL: the statement itself fails, the framework rolls back,
                // sets its failure flag and throws with the driver's error number
                try {
                    $db->query('INSERT INTO no_such_table (x) VALUES (1)');
                } catch (DatabaseException) {
                    throw self::lockError(1213);
                }
            }

            return $calls;
        });

        $this->assertSame(2, $result);
        $this->assertSame(0, $db->transDepth, 'committed');
        $this->assertSame(['run 2'], array_column($db->table('retry_probe')->get()->getResultArray(), 'label'), 'the first run left nothing behind');
        $this->assertTrue($db->transStatus(), 'the failure flag of the first run is cleared');
    }

    public function testTransactionUndoesAWorkThatLeftItsOwnTransactionOpen(): void
    {
        $db = Database::connect('tests');

        try {
            DeadlockRetry::transaction($db, static function () use ($db): void {
                $db->transBegin();
                $db->table('retry_probe')->insert(['label' => 'lost']);

                throw new LogicException('thrown before the inner commit');
            });
            $this->fail('The error must reach the caller.');
        } catch (LogicException) {
            // expected
        }

        $this->assertSame(0, $db->transDepth, 'no transaction left open');
        $this->assertSame(0, $db->table('retry_probe')->countAllResults());
    }

    public function testTransactionRestoresTheCallersThrowSetting(): void
    {
        $db = Database::connect('tests');

        DeadlockRetry::transaction($db, static fn (): bool => true);
        $this->assertFalse($db->transException, 'off before, off after');

        // A caller that asked for exceptions keeps them after a nested call
        $db->transException(true);
        DeadlockRetry::transaction($db, static fn (): bool => true);
        $this->assertTrue($db->transException);
    }

    public function testTransactionRollsBackOtherErrorsWithoutRepeating(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        try {
            DeadlockRetry::transaction($db, static function () use ($db, &$calls): void {
                $calls++;
                $db->table('retry_probe')->insert(['label' => 'lost']);

                throw new LogicException('a bug, not a lock');
            });
            $this->fail('The error must reach the caller.');
        } catch (LogicException) {
            // expected
        }

        $this->assertSame(1, $calls);
        $this->assertSame(0, $db->transDepth);
        $this->assertSame(0, $db->table('retry_probe')->countAllResults());
    }

    public function testAFailedStatementInsideTheTransactionIsNotSilent(): void
    {
        $db = Database::connect('tests');

        // Without transException the framework returns false here and the commit goes on
        $this->expectException(DatabaseException::class);

        try {
            DeadlockRetry::transaction($db, static function () use ($db): void {
                $db->table('retry_probe')->insert(['label' => 'kept?']);
                $db->query('INSERT INTO no_such_table (x) VALUES (1)');
            });
        } finally {
            $this->assertSame(0, $db->transDepth);
            $this->assertSame(0, $db->table('retry_probe')->countAllResults(), 'rolled back with the failed statement');
            $db->resetTransStatus();
        }
    }

    public function testNestedTransactionJoinsTheOuterOneAndNeverRepeats(): void
    {
        $db    = Database::connect('tests');
        $calls = 0;

        $db->transBegin();

        try {
            DeadlockRetry::transaction($db, static function () use (&$calls): void {
                $calls++;

                throw self::lockError(1213);
            });
            $this->fail('The outer transaction is gone: its owner must hear about it.');
        } catch (DatabaseException) {
            // expected
        } finally {
            $db->transRollback();
        }

        $this->assertSame(1, $calls);
        $this->assertSame([], $this->waits);
    }
}
