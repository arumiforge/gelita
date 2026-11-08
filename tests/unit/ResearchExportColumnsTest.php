<?php

use App\Libraries\EventCatalog;
use App\Services\ExportService;
use App\Services\RawDataService;
use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\RawDataWorld;

/**
 * Columns of the `ketua` download (research revision 10, part 3: 29 §7):
 * no admin or dropped column, no `perangkat` sheet, the `event` sheet with
 * research types only and codes instead of internal ids, `answer_key` for
 * `ketua`, no test or simulated accounts, and always one study (K16).
 *
 * @internal
 */
final class ResearchExportColumnsTest extends CIUnitTestCase
{
    use RawDataWorld;

    private const ADMIN_OR_DROPPED = [
        'os_name', 'os_version', 'browser_name', 'browser_version', 'is_touch', 'app_client_version', 'package_code', 'package_mode',
        'user_agent', 'client_hints', 'device_brand', 'device_model', 'pixel_ratio', 'touch_points', 'timezone', 'language', 'os_platform',
        'clock_offset_ms', 'server_received_at', 'ip_hash', 'event_uuid', 'client_event_id', 'session_id', 'participant_id',
        'level_id', 'challenge_node_id', 'challenge_item_id', 'challenge_attempt_id', 'payload_json', 'country', 'province', 'district', 'school_ref',
    ];

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createRawDataWorld();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testNoAdminOrDroppedColumnInAnyResearchSheet(): void
    {
        $export = new ExportService();

        $this->assertNotContains('perangkat', ExportService::SHEETS);

        foreach (ExportService::SHEETS as $sheet) {
            foreach ($export->columns($sheet, true, true) as $column) {
                $this->assertNotContains($column, self::ADMIN_OR_DROPPED, "{$sheet}.{$column}");
            }
        }

        $this->assertContains('answer_key', $export->columns('jawaban', true, true));
        $this->assertContains('answer_key', $export->columns('butir', true, true));
        $this->assertSame(['kamus', 'peserta'], array_slice($export->allowedSheets([], true), 0, 2));
        $this->assertContains('event', $export->allowedSheets([], true), 'ketua');
        $this->assertNotContains('event', $export->allowedSheets([], false));
    }

    public function testEventSheetHoldsResearchEventsWithCodes(): void
    {
        $this->conn->table('game_event_logs')->insert([
            'event_uuid' => '00000000-0000-4000-8000-000000000777', 'client_event_id' => 'vp-1', 'session_id' => 1, 'participant_id' => 1,
            'event_type' => 'viewport_changed', 'occurred_at' => '2026-10-01 08:03:00', 'sequence_no' => 9,
        ]);

        $rows = iterator_to_array(service('rawDataService')->rows('event', ['study_id' => 1], StaffScope::head()), false);

        $this->assertNotEmpty($rows);
        foreach ($rows as $row) {
            $this->assertTrue(EventCatalog::isResearch((string) $row['event_type']), $row['event_type']);
        }

        $hint = array_values(array_filter($rows, static fn (array $r): bool => $r['event_type'] === 'hint_opened'))[0];
        $this->assertSame(['S-ANI', 'GLT-000001', 'temanggung', 'tmg-3', 1], [$hint['session_code'], $hint['participant_code'], $hint['level'], $hint['node_ref'], $hint['attempt_id']]);
        $this->assertSame('{"hint_id":5}', $hint['payload']);
    }

    public function testTestAndSimulatedAccountsNeverAppear(): void
    {
        $this->conn->table('schools')->insert(['name' => 'Sekolah Uji Validator', 'research_code' => 'V01']);
        $school = (int) $this->conn->insertID();

        foreach ([['sim_001', null], ['uji.bahasa1', $school], ['siswa.v01', $school]] as $n => [$username, $schoolId]) {
            $this->conn->table('participants')->insert(['participant_code' => 'X-' . $n, 'username' => $username, 'password_hash' => 'x', 'school_id' => $schoolId]);
            $id = (int) $this->conn->insertID();
            $this->conn->table('game_sessions')->insert(['session_code' => 'S-X' . $n, 'participant_id' => $id, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id']);
        }

        $raw = service('rawDataService');

        foreach (['peserta', 'sesi'] as $sheet) {
            $codes = array_column(iterator_to_array($raw->rows($sheet, ['study_id' => 1], StaffScope::head()), false), 'participant_code');
            $this->assertEqualsCanonicalizing(['GLT-000001', 'GLT-000002'], $codes, $sheet);
        }

        $this->assertSame(2, (new ExportService())->participantCount(['format' => 'csv', 'filters' => ['study_id' => 1], 'scope' => StaffScope::head(), 'participant_id' => null]));
    }

    public function testEveryDownloadNeedsAStudyAlsoWithFieldModeOff(): void
    {
        $this->assertSame(0, (int) $this->conn->table('research_studies')->get()->getRow('field_mode'));

        $this->expectException(DomainException::class);
        $this->expectExceptionMessage(lang('Admin.exportNeedsStudy'));
        (new ExportService())->context(['id' => 0, 'requested_by' => 2, 'format' => 'xlsx', 'anonymized' => 1, 'scope_json' => ['filters' => []]]);
    }
}
