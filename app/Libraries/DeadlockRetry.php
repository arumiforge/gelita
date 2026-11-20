<?php

namespace App\Libraries;

use Closure;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Exceptions\DatabaseException;
use mysqli_sql_exception;
use Throwable;

/**
 * Runs a database write again when MySQL refused it because of a lock:
 * 1213 (deadlock: InnoDB rolled the whole transaction back) or 1205 (lock
 * wait timeout). Both are answers MySQL itself documents as "try again".
 *
 * Found by the remote load test of production (decision log, *Remote load
 * test, play rounds and deadlocks*, 20–21 November 2026): two batches of
 * re-sent events deadlocked on the end of `game_event_logs`, and the batch
 * that lost was answered with HTTP 500.
 *
 * Only the outermost unit of work is retried:
 *  - run() is for work without a transaction of its own (one statement, or
 *    statements that are safe to repeat). Inside a caller's transaction it
 *    runs the work once, because a deadlock has already undone everything
 *    that caller wrote; the owner of the transaction has to start over.
 *  - transaction() owns a transaction. A failed statement throws (the
 *    framework stays silent inside a transaction by default), the
 *    transaction is rolled back, and the whole closure runs again.
 *
 * The work must be safe to run more than once. It does not replace the
 * conditional UPDATEs (CLAUDE.md → Concurrency): those decide who wins a
 * race, this only repeats a write the database gave up on.
 */
final class DeadlockRetry
{
    /** MySQL/MariaDB error numbers that mean "run it again". */
    public const RETRYABLE = [1213, 1205];

    /** Runs in total: the first one and up to three more. */
    public const MAX_RUNS = 4;

    /**
     * No further run once this many seconds have passed since the first one
     * began. A deadlock is answered at once, but a lock wait timeout (1205)
     * comes after `innodb_lock_wait_timeout` (50 s by default): repeating
     * that four times would hold one PHP process for minutes.
     */
    public const MAX_SECONDS = 10.0;

    /** MAX_SECONDS, replaceable in tests. */
    public static float $maxSeconds = self::MAX_SECONDS;

    /** Replaces the wait between runs (tests): receives microseconds. */
    public static ?Closure $sleeper = null;

    /**
     * @template T
     *
     * @param callable(): T $work
     *
     * @return T
     */
    public static function run(BaseConnection $db, callable $work, string $what = 'write')
    {
        if ($db->transDepth > 0) {
            return $work();
        }

        $started = microtime(true);

        for ($run = 1; ; $run++) {
            try {
                return $work();
            } catch (Throwable $e) {
                if (! self::mayRunAgain($e, $run, $started)) {
                    throw $e;
                }

                self::wait($run, $what, $e);
            }
        }
    }

    /**
     * Runs $work inside a transaction and commits it. Nested in another
     * transaction it joins that one and never retries.
     *
     * @template T
     *
     * @param callable(): T $work
     *
     * @return T
     */
    public static function transaction(BaseConnection $db, callable $work, string $what = 'transaction')
    {
        $depth     = $db->transDepth;
        $outermost = $depth === 0;
        $throwing  = (bool) $db->transException;
        $started   = microtime(true);

        for ($run = 1; ; $run++) {
            // Inside a transaction the framework returns false for a failed statement
            // unless asked to throw; a deadlock would then go unnoticed (and the rest
            // of the work would be committed alone, the transaction being gone already)
            $db->transException(true);
            $db->transBegin();

            try {
                $result = $work();

                if (! $db->transCommit() && $db->transDepth > $depth) {
                    throw new DatabaseException('The transaction could not be committed.');
                }

                return $result;
            } catch (Throwable $e) {
                // A statement that threw has rolled back already (BaseConnection::query());
                // anything else, and a transaction $work left open, is undone here
                while ($db->transDepth > $depth && $db->transRollback()) {
                    // one level per call
                }

                if ($outermost) {
                    // Strict mode keeps the failure flag for the whole connection; nothing of
                    // this transaction is left, so later transactions start clean
                    $db->resetTransStatus();
                }

                if (! $outermost || ! self::mayRunAgain($e, $run, $started)) {
                    throw $e;
                }

                self::wait($run, $what, $e);
            } finally {
                $db->transException($throwing);
            }
        }
    }

    /** A lock error, runs left, and still inside the time allowed. */
    private static function mayRunAgain(Throwable $e, int $run, float $started): bool
    {
        return $run < self::MAX_RUNS
            && microtime(true) - $started < self::$maxSeconds
            && self::isRetryable($e);
    }

    /** True for a database error 1213 or 1205 anywhere in the exception chain. */
    public static function isRetryable(Throwable $e): bool
    {
        for ($cause = $e; $cause !== null; $cause = $cause->getPrevious()) {
            $fromDatabase = $cause instanceof DatabaseException || $cause instanceof mysqli_sql_exception;

            if ($fromDatabase && in_array((int) $cause->getCode(), self::RETRYABLE, true)) {
                return true;
            }
        }

        return false;
    }

    /** A short, growing, random wait, so two losers do not meet again at once. */
    private static function wait(int $run, string $what, Throwable $e): void
    {
        log_message('warning', 'Database lock error {code} on {what}: run {run} of {max} failed, trying again.', [
            'code' => (int) $e->getCode(),
            'what' => $what,
            'run'  => $run,
            'max'  => self::MAX_RUNS,
        ]);

        $microseconds = random_int(10_000, 40_000) * $run;

        if (self::$sleeper !== null) {
            (self::$sleeper)($microseconds);

            return;
        }

        usleep($microseconds);
    }
}
