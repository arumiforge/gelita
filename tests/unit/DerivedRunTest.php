<?php

use App\Libraries\ResearchDictionary;
use App\Services\DerivedDataService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\DerivedWorld;

/**
 * Part 9 (35 §9, §16.1 DerivedRunTest): a run records its version,
 * thresholds, and fingerprint; it goes stale when the thresholds, the field
 * sheets, the consent, or the raw data change; one claim per study; a failed
 * run keeps the old one current.
 *
 * @internal
 */
final class DerivedRunTest extends CIUnitTestCase
{
    use DerivedWorld;

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createDerivedWorld();
        $attempt    = $this->addAttempt($this->conn, 'tmg-3', '2026-11-10 08:10:00', '2026-11-10 08:20:00');

        foreach ($this->scoredItems($this->conn, 'tmg-3') as $i => $item) {
            $this->addTry($this->conn, $attempt, $item, 1, $i > 0, '2026-11-10 08:15:00', ['ready_ms' => 1000, 'last_input_ms' => 3500 + $i]);
        }
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testARunRecordsVersionThresholdsAndFingerprint(): void
    {
        $result = service('derivedData')->compute(1, null, 'command');

        $this->assertSame('done', $result['status'], (string) ($result['run']['error'] ?? ''));
        $run = $result['run'];
        $this->assertSame(ResearchDictionary::VERSION, $run['dictionary_version']);
        $this->assertSame([180000, 3000, 3000], [(int) $run['idle_threshold_ms'], (int) $run['rapid_guess_ms'], (int) $run['test_rapid_guess_ms']]);
        $this->assertSame(service('derivedData')->fingerprint(1), $run['input_fingerprint']);
        $this->assertSame((int) $run['id'], (int) $this->conn->table('research_studies')->where('id', 1)->get()->getRow('derived_run_id'));
        $this->assertNull($this->conn->table('research_studies')->where('id', 1)->get()->getRow('derived_running_since'), 'claim released');
        $this->assertSame(1, (int) $run['participant_count']);
        $this->assertSame(15, $this->conn->table('derived_challenges')->where('run_id', $run['id'])->countAllResults());
        $this->assertGreaterThan(0, $this->conn->table('derived_tries')->where('run_id', $run['id'])->countAllResults());
        $this->assertSame(ResearchDictionary::VERSION, $this->conn->table('derived_tries')->where('run_id', $run['id'])->get()->getRow('dictionary_version'));
        $this->assertFalse(service('derivedData')->isStale(1));
    }

    public function testStaleAfterThresholdsSheetsConsentOrDeletion(): void
    {
        $derived = service('derivedData');
        $changes = [
            'threshold' => fn () => $this->conn->table('research_studies')->update(['idle_threshold_ms' => 120000]),
            'sheet'     => fn () => $this->conn->table('field_sheet_imports')->insert(['field_session_id' => 1, 'sheet' => 'l3_bantuan', 'spec_version' => 'lembar-1.0', 'file_sha256' => str_repeat('a', 64)]),
            'consent'   => fn () => $this->conn->table('participant_consents')->update(['status' => 'withdrawn_keep', 'status_on' => '2026-11-10', 'updated_at' => '2026-11-11 09:00:00']),
            'deletion'  => fn () => $this->conn->table('item_tries')->where('id', 1)->delete(),
        ];

        foreach ($changes as $name => $change) {
            $this->assertSame('done', $derived->compute(1)['status'], $name);
            $this->assertFalse($derived->isStale(1), $name);
            $change();
            $this->assertTrue($derived->isStale(1), $name);
        }
    }

    public function testOnlyOneRunComputesAtATime(): void
    {
        $derived = service('derivedData');
        $this->assertTrue($derived->claim(1, date('Y-m-d H:i:s')));
        $this->assertFalse($derived->claim(1, date('Y-m-d H:i:s')), 'the second claim loses');
        $this->assertSame('busy', $derived->compute(1)['status']);
        $this->assertSame(0, $this->conn->table('derived_runs')->countAllResults(), 'the loser writes nothing');

        // A claim older than 30 minutes is taken over
        $this->conn->table('research_studies')->update(['derived_running_since' => date('Y-m-d H:i:s', time() - 31 * 60)]);
        $this->assertSame('done', $derived->compute(1)['status']);
    }

    public function testTheSwitchIsConditionalOnTheOldRun(): void
    {
        $derived = service('derivedData');
        $first   = (int) $derived->compute(1)['run']['id'];
        $this->assertFalse($derived->switchRun(1, null, 999), 'a writer that read an older current run loses');
        $this->assertSame($first, (int) $this->conn->table('research_studies')->get()->getRow('derived_run_id'));
    }

    public function testAFailedRunKeepsTheOldRun(): void
    {
        $derived = new DerivedDataService();
        $first   = (int) $derived->compute(1)['run']['id'];
        $this->conn->table('research_studies')->update(['rapid_guess_ms' => 2000]);

        $derived->afterParticipant = static function (): void {
            throw new RuntimeException('disk full');
        };
        $result = $derived->compute(1);

        $this->assertSame('failed', $result['status']);
        $this->assertSame('disk full', $result['run']['error']);
        $this->assertSame($first, (int) $this->conn->table('research_studies')->get()->getRow('derived_run_id'));
        $this->assertNull($this->conn->table('research_studies')->get()->getRow('derived_running_since'));

        $this->expectException(DomainException::class);
        $derived->ensureFresh(1);
    }

    public function testFieldModeOffComputesNothing(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->assertSame('off', service('derivedData')->compute(1)['status']);
        $this->assertNull(service('derivedData')->ensureFresh(1));
    }
}
