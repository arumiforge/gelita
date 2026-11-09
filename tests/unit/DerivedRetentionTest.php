<?php

use App\Services\RetentionService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\DerivedWorld;

/**
 * Part 9 (35 §9, §12; 30 §7.3): deleting one student removes their rows in
 * every run; runs nobody reads are pruned, runs a download still holds stay.
 *
 * @internal
 */
final class DerivedRetentionTest extends CIUnitTestCase
{
    use DerivedWorld;

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createDerivedWorld();
        $attempt    = $this->addAttempt($this->conn, 'tmg-3', '2026-11-10 08:10:00', '2026-11-10 08:20:00');
        $this->addTry($this->conn, $attempt, $this->scoredItems($this->conn, 'tmg-3')[0], 1, true, '2026-11-10 08:15:00');
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testDeletingAStudentRemovesTheirDerivedRowsInEveryRun(): void
    {
        $derived = service('derivedData');
        $first   = (int) $derived->compute(1)['run']['id'];
        // A download still holds the first run, so it is kept
        $this->conn->table('data_exports')->insert(['requested_by' => 2, 'format' => 'csv', 'scope_json' => '{}', 'status' => 'done', 'file_path' => '/tmp/x.zip', 'derived_run_id' => $first]);
        $second = (int) $derived->compute(1)['run']['id'];

        $this->assertSame(2, $this->conn->table('derived_participants')->where('participant_id', 1)->countAllResults());

        $counts = (new RetentionService())->countAffected(['participant_id' => 1]);
        $this->assertSame(2, $counts['derived_participants'], 'the preview counts every run');

        (new RetentionService())->deletePlayData(1);

        foreach (RetentionService::DERIVED_TABLES as $table) {
            $this->assertSame(0, $this->conn->table($table)->where('participant_id', 1)->countAllResults(), $table);
        }

        $this->assertSame([$first, $second], array_map('intval', array_column($this->conn->table('derived_runs')->orderBy('id')->get()->getResultArray(), 'id')));
    }

    public function testRunsNobodyReadsArePruned(): void
    {
        $derived = service('derivedData');
        $first   = (int) $derived->compute(1)['run']['id'];
        $this->conn->table('data_exports')->insert(['requested_by' => 2, 'format' => 'csv', 'scope_json' => '{}', 'status' => 'done', 'file_path' => '/tmp/x.zip', 'derived_run_id' => $first]);
        $derived->compute(1);
        $third = (int) $derived->compute(1)['run']['id'];

        // The second run is neither current, nor held by a download, nor the newest: pruned at the end of the third
        $this->assertSame(2, $this->conn->table('derived_runs')->countAllResults());
        $this->assertSame(0, $this->conn->table('derived_tries')->where('run_id', 2)->countAllResults());

        // The download's file is purged: the retention run prunes the first one too
        $this->conn->table('data_exports')->update(['file_path' => null]);
        $this->assertSame(1, $derived->pruneRuns());
        $this->assertSame([$third], array_map('intval', array_column($this->conn->table('derived_runs')->get()->getResultArray(), 'id')));
    }

    public function testTheTablesSitInTheDeletionAndResetLists(): void
    {
        foreach (RetentionService::DERIVED_TABLES as $table) {
            $this->assertLessThan(array_search('participants', RetentionService::HARD_ORDER, true), array_search($table, RetentionService::HARD_ORDER, true), $table);
            $this->assertContains($table, App\Services\SystemResetService::GROUP_A);
            $this->assertLessThan(array_search('participants', App\Services\SystemResetService::CLEAR_ORDER, true), array_search($table, App\Services\SystemResetService::CLEAR_ORDER, true), $table);
        }

        $this->assertContains('derived_runs', App\Services\SystemResetService::GROUP_A);
    }
}
