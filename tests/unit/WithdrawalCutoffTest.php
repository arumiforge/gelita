<?php

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\DerivedWorld;
use Tests\Support\Derived\ExportReader;

/**
 * Part 9 (35 §7, K4): data after the withdrawal cutoff is left out of every
 * derived table; the raw data stays, marked `after_withdrawal = 1`.
 *
 * @internal
 */
final class WithdrawalCutoffTest extends CIUnitTestCase
{
    use DerivedWorld;
    use ExportReader;

    private BaseConnection $conn;
    private int $attempt;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn    = $this->createDerivedWorld();
        $this->attempt = $this->addAttempt($this->conn, 'tmg-3', '2026-11-10 08:10:00', '2026-11-10 08:40:00');
        $items         = $this->scoredItems($this->conn, 'tmg-3');
        $this->addTry($this->conn, $this->attempt, $items[0], 1, false, '2026-11-10 08:12:00');
        $this->addTry($this->conn, $this->attempt, $items[0], 2, true, '2026-11-10 08:30:00');
        $this->addEvent($this->conn, 'challenge_abandoned', '2026-11-10 08:31:00', [], $this->attempt);
    }

    protected function tearDown(): void
    {
        $this->removeExports();
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testNothingAfterDeactivatedAtEntersTheDerivedLayer(): void
    {
        $this->conn->table('participant_consents')->update(['status' => 'withdrawn_keep', 'status_on' => '2026-11-10']);
        $this->conn->table('participants')->update(['deactivated_at' => '2026-11-10 08:20:00']);

        $run = (int) service('derivedData')->compute(1)['run']['id'];

        $tries = $this->conn->table('derived_tries')->where('run_id', $run)->get()->getResultArray();
        $this->assertCount(1, $tries, 'only the try before the cutoff');
        $person = $this->conn->table('derived_participants')->where('run_id', $run)->get()->getRowArray();
        $this->assertSame([1, '2026-11-10', 'deactivated_at'], [(int) $person['consent_ok'], $person['withdrawn_on'], $person['cutoff_basis']]);
        $this->assertStringStartsWith('2026-11-10 08:20:00', (string) $person['withdrawn_at']);

        $challenge = $this->conn->table('derived_challenges')->where('run_id', $run)->where('challenge_node_id', $this->nodeId($this->conn, 'tmg-3'))->get()->getRowArray();
        $this->assertSame('unfinished', $challenge['completion_status'], 'the attempt closed after the cutoff');
        $item = $this->conn->table('derived_items')->where('run_id', $run)->where('tries_used IS NOT NULL')->get()->getRowArray();
        $this->assertSame([1, 'left'], [(int) $item['censored'], $item['censor_reason']]);
        $attempt = $this->conn->table('derived_attempts')->where('run_id', $run)->get()->getRowArray();
        $this->assertSame(600000, (int) $attempt['total_ms'], 'cut at 08:20');

        // The raw data stays, marked
        $this->assertSame(2, $this->conn->table('item_tries')->countAllResults());
        $sheets = $this->readSheets((string) $this->buildExport(2)['file_path']);
        $this->assertSame(['0', '1'], array_column($sheets['percobaan'], 'after_withdrawal'));
        $this->assertSame(['1'], array_column(array_values(array_filter($sheets['selesai'], static fn (array $r): bool => $r['event_type'] === 'challenge_abandoned')), 'after_withdrawal'));
        $this->assertSame(['0'], array_column($sheets['pengerjaan'], 'after_withdrawal'), 'started before the cutoff');
    }

    public function testEndOfTheStatusDayWithoutDeactivation(): void
    {
        $this->conn->table('participant_consents')->update(['status' => 'withdrawn_keep', 'status_on' => '2026-11-09']);

        $run = (int) service('derivedData')->compute(1)['run']['id'];

        $this->assertSame(0, $this->conn->table('derived_tries')->where('run_id', $run)->countAllResults());
        $this->assertSame('status_on', $this->conn->table('derived_participants')->where('run_id', $run)->get()->getRow('cutoff_basis'));
    }
}
