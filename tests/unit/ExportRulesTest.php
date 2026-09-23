<?php

use App\Services\ExportService;
use App\Services\RetentionService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Aturan export dan penghapusan yang tidak butuh database: kolom identitas
 * pada mode anonim, kunci jawaban hanya untuk admin, sheet guru, cakupan
 * sekolah yang dipaksa service, dan penjaga eksekusi penghapusan.
 *
 * @internal
 */
final class ExportRulesTest extends CIUnitTestCase
{
    private ExportService $export;

    protected function setUp(): void
    {
        parent::setUp();

        $this->export = new ExportService();
    }

    public function testAnonymousExportHasNoIdentityColumnsAtAll(): void
    {
        foreach (ExportService::SHEETS as $sheet) {
            $columns = $this->export->columns($sheet, true, true);

            foreach (ExportService::IDENTITY_COLUMNS as $identity) {
                $this->assertNotContains($identity, $columns, "{$sheet} anonim tidak membuat kolom {$identity}");
            }
        }

        $named = $this->export->columns('Participants', false, true);

        $this->assertContains('display_name', $named);
        $this->assertContains('username', $named);
        $this->assertContains('school_name', $named);
        $this->assertContains('school_ref', $this->export->columns('Participants', true, false), 'kode samaran sekolah tetap ada untuk analisis');
    }

    public function testSecretsNeverExported(): void
    {
        foreach (ExportService::SHEETS as $sheet) {
            foreach ([[true, true], [false, true], [true, false]] as [$anonymized, $isAdmin]) {
                $columns = $this->export->columns($sheet, $anonymized, $isAdmin);

                foreach (['password_hash', 'password', 'failed_login_count', 'locked_until', 'guardian_name', 'ip_hash'] as $secret) {
                    $this->assertNotContains($secret, $columns, "{$sheet} tidak boleh memuat {$secret}");
                }
            }
        }
    }

    public function testAnswerKeyOnlyForAdmin(): void
    {
        $this->assertContains('answer_key', $this->export->columns('Item Responses', true, true));
        $this->assertNotContains('answer_key', $this->export->columns('Item Responses', true, false));
    }

    public function testGameplaySheetsCarryPhase(): void
    {
        foreach (['Sessions', 'Levels', 'Challenge Summary', 'Item Responses', 'Raw Events', 'Audio Usage', 'Indicators', 'Feedback'] as $sheet) {
            $this->assertContains('phase_code', $this->export->columns($sheet, true, true), "{$sheet} wajib punya kolom fase (FITUR 18)");
        }
    }

    public function testTeacherNeverGetsRawEvents(): void
    {
        $this->assertNotContains('Raw Events', $this->export->allowedSheets(['Raw Events', 'Participants'], false));
        $this->assertSame(['Participants'], $this->export->allowedSheets(['Raw Events', 'Participants'], false));
        $this->assertNotContains('Raw Events', $this->export->allowedSheets(['Raw Events'], false), 'pilihan terlarang saja → semua sheet yang boleh, tanpa Raw Events');
        $this->assertContains('Raw Events', $this->export->allowedSheets(['Raw Events'], true));
        $this->assertSame(ExportService::SHEETS, $this->export->allowedSheets([], true));
        $this->assertSame(['Participants', 'Feedback'], $this->export->allowedSheets(['Feedback', 'Participants', 'Bogus'], true), 'urutan resmi, nama asing dibuang');
    }

    public function testSchoolScopeIsForcedAndFiltersSanitized(): void
    {
        $filters = $this->export->sanitizeFilters([
            'school_id'   => '99',
            'study_id'    => '1',
            'date_from'   => '2026-01-01',
            'date_to'     => '2026-13-01; DROP',
            'phase_code'  => ' pretest ',
            'password'    => 'x',
            'class_level' => '',
        ], 7);

        $this->assertSame(7, $filters['school_id'], 'school_id guru dari staff_users menang atas isi formulir');
        $this->assertSame(1, $filters['study_id']);
        $this->assertSame('2026-01-01', $filters['date_from']);
        $this->assertArrayNotHasKey('date_to', $filters);
        $this->assertSame('pretest', $filters['phase_code']);
        $this->assertArrayNotHasKey('password', $filters);
        $this->assertArrayNotHasKey('class_level', $filters);

        $this->assertSame(99, $this->export->sanitizeFilters(['school_id' => '99'], null)['school_id'], 'admin boleh memilih sekolah');
    }

    public function testDeletionScopeTakesExactlyOnePrimaryKey(): void
    {
        $retention = new RetentionService();

        $this->assertSame(['participant_id' => 4], $retention->normalizeScope(['participant_id' => '4', 'study_id' => '1', 'phase_code' => 'pretest']));
        $this->assertSame(['session_id' => 9], $retention->normalizeScope(['session_id' => '9', 'date_to' => '2026-01-01']));
        $this->assertSame(
            ['study_id' => 1, 'phase_code' => 'posttest', 'date_to' => '2026-06-30'],
            $retention->normalizeScope(['study_id' => '1', 'phase_code' => 'posttest', 'date_from' => 'kemarin', 'date_to' => '2026-06-30']),
        );
        $this->assertSame(['study_id' => 1], $retention->normalizeScope(['study_id' => '1', 'phase_code' => 'fase-lain']));
        $this->assertNull($retention->normalizeScope(['participant_id' => '0', 'session_id' => '', 'study_id' => '-3']));
    }

    public function testDeletionDriftGuard(): void
    {
        $retention = new RetentionService();

        $this->assertFalse($retention->driftExceeded(0, 10), 'selisih kecil pada cakupan kecil masih ditoleransi');
        $this->assertTrue($retention->driftExceeded(0, 11));
        $this->assertFalse($retention->driftExceeded(1000, 1100));
        $this->assertTrue($retention->driftExceeded(1000, 1101));
        $this->assertTrue($retention->driftExceeded(1000, 850), 'berkurang jauh juga dibatalkan');
        $this->assertTrue($retention->driftExceeded(103, 143));
    }
}
