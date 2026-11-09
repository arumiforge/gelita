<?php

use App\Libraries\DerivedSheets;
use App\Libraries\ResearchDictionary;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\DerivedWorld;
use Tests\Support\Derived\ExportReader;

/**
 * Part 9 (35 §9, §10, K12): the derived sheets in the ketua's download of a
 * field-mode study, recomputed first when stale, `data_exports.derived_run_id`,
 * the Kamus Data header, `tes_jawaban.rapid_guess`, and nothing that must
 * never leave the server.
 *
 * @internal
 */
final class DerivedExportTest extends CIUnitTestCase
{
    use DerivedWorld;
    use ExportReader;

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createDerivedWorld();
        $attempt    = $this->addAttempt($this->conn, 'tmg-3', '2026-11-10 08:10:00', '2026-11-10 08:20:00');

        foreach ($this->scoredItems($this->conn, 'tmg-3') as $i => $item) {
            $this->addTry($this->conn, $attempt, $item, 1, true, '2026-11-10 08:15:00', ['ready_ms' => 1000, 'last_input_ms' => 6000]);
        }

        $this->seedTest();
    }

    protected function tearDown(): void
    {
        $this->removeExports();
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAStaleStudyIsComputedFirstAndTheRunIsRecorded(): void
    {
        $this->assertSame(0, $this->conn->table('derived_runs')->countAllResults());

        $export = $this->buildExport(2);
        $this->assertSame('done', $export['status'], (string) $export['error_message']);
        $run = (int) $this->conn->table('research_studies')->get()->getRow('derived_run_id');
        $this->assertSame($run, (int) $export['derived_run_id']);

        // Fresh: the next download reuses the run
        $this->assertSame($run, (int) $this->buildExport(2)['derived_run_id']);
        $this->assertSame(1, $this->conn->table('derived_runs')->countAllResults());

        // Stale after a threshold change: a new run first
        $this->conn->table('research_studies')->update(['idle_threshold_ms' => 120000]);
        $this->assertGreaterThan($run, (int) $this->buildExport(2)['derived_run_id']);
    }

    public function testDerivedSheetsWithTheRunInTheHeaderAndNothingSecret(): void
    {
        $sheets = $this->readSheets((string) $this->buildExport(2)['file_path']);

        foreach (array_keys(DerivedSheets::TABLES) as $sheet) {
            $this->assertArrayHasKey($sheet, $sheets, $sheet);
        }

        $this->assertCount(15, $sheets['turunan_tantangan']);
        $this->assertSame('S03-A01', $sheets['turunan_peserta'][0]['participant_code']);
        $this->assertSame(['B1', 'S03', '1'], [$sheets['turunan_peserta'][0]['booklet'], $sheets['turunan_peserta'][0]['school_code'], $sheets['turunan_peserta'][0]['consent_ok']]);

        foreach (array_keys(DerivedSheets::TABLES) as $sheet) {
            $header = array_keys($sheets[$sheet][0] ?? array_flip(array_keys(DerivedSheets::COLUMNS[$sheet])));

            foreach (['participant_id', 'display_name', 'username', 'school_name', 'school_npsn', 'answer', 'answer_key', 'response', 'first_answer', 'final_answer'] as $secret) {
                $this->assertNotContains($secret, $header, "{$sheet}.{$secret}");
            }
        }

        $this->assertStringNotContainsString('risk', implode(',', array_keys(DerivedSheets::META)), 'no risk item per student (29 K9)');

        $kamus = array_column($sheets['kamus'], 'judul', 'lembar');
        $this->assertSame(ResearchDictionary::VERSION, $kamus['Versi kamus']);
        $this->assertArrayNotHasKey('Status kamus', $kamus, 'kamus-1.1 is approved (9 November 2026)');
        $this->assertSame((string) $this->conn->table('research_studies')->get()->getRow('derived_run_id'), $kamus['Putaran hitung']);
        $this->assertSame(['180000', '3000'], [$kamus['Ambang jeda (mdtk)'], $kamus['Ambang tebakan cepat (mdtk)']]);

        // tes_jawaban.rapid_guess comes from derived_test_responses (35 §19 no. 9)
        $this->assertSame([['tes-mb-A01', '1'], ['tes-mb-A02', '']], array_map(static fn (array $r): array => [$r['item_code'], $r['rapid_guess']], $sheets['tes_jawaban']));
        $this->assertSame([['tes-mb-A01', '1'], ['tes-mb-A02', '']], array_map(static fn (array $r): array => [$r['item_code'], $r['rapid_guess']], $sheets['turunan_tes']), 'no example, no preview sitting');
    }

    public function testNoDerivedSheetsWithFieldModeOff(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $export = $this->buildExport(2);
        $sheets = $this->readSheets((string) $export['file_path']);

        $this->assertArrayNotHasKey('turunan_peserta', $sheets);
        $this->assertNull($export['derived_run_id']);
        $this->assertSame(0, $this->conn->table('derived_runs')->countAllResults());
    }

    public function testAFailedRunFailsTheDownload(): void
    {
        $this->conn->table('research_studies')->update(['derived_running_since' => date('Y-m-d H:i:s')]);
        $derived = new class () extends App\Services\DerivedDataService {
            public function compute(int $studyId, ?int $staffId = null, string $via = 'command'): array
            {
                return ['status' => 'failed', 'run' => ['error' => 'disk full']];
            }
        };
        Config\Services::injectMock('derivedData', $derived);

        $export = $this->buildExport(2);

        $this->assertSame('failed', $export['status']);
        $this->assertStringContainsString('disk full', (string) $export['error_message']);
        Config\Services::resetSingle('derivedData');
    }

    /** A pre-test with an example item, two answers, and a preview sitting that must not count. */
    private function seedTest(): void
    {
        $db = $this->conn;
        $db->table('instrument_versions')->insert(['instrument' => 'tes', 'version' => 'uji-9', 'content_hash' => str_repeat('0', 64), 'imported_at' => '2026-11-01 08:00:00']);
        $version = (int) $db->insertID();
        $ids     = [];

        foreach ([['tes-ct-01', 'ct', 1], ['tes-mb-A01', 'mb', 2], ['tes-mb-A02', 'mb', 3]] as [$code, $part, $pos]) {
            $db->table('instrument_items')->insert(['version_id' => $version, 'code' => $code, 'part' => $part, 'form' => 'A', 'position' => $pos, 'type' => 'pg', 'show_json' => '{}', 'units_json' => '["' . $code . '"]']);
            $ids[$code] = (int) $db->insertID();
        }

        foreach ([0, 1] as $preview) {
            $db->table('instrument_sittings')->insert(['participant_id' => 1, 'study_id' => 1, 'field_session_id' => 1, 'occasion' => 'pre', 'kind' => 'test', 'form' => 'A', 'version_id' => $version, 'is_preview' => $preview, 'started_at' => '2026-11-10 08:30:00', 'updated_at' => '2026-11-10 08:30:00']);
            $sitting = (int) $db->insertID();

            foreach (['tes-ct-01' => 1000, 'tes-mb-A01' => 1500, 'tes-mb-A02' => 4000] as $code => $ms) {
                $db->table('instrument_responses')->insert(['part_id' => 1, 'sitting_id' => $sitting, 'item_id' => $ids[$code], 'unit' => $code, 'response' => $code === 'tes-mb-A02' ? '-' : 'B', 'last_view_ms' => $ms, 'first_shown_at' => '2026-11-10 08:31:00', 'updated_at' => '2026-11-10 08:31:00']);
            }
        }
    }
}
