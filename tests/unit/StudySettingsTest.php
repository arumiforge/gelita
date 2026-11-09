<?php

use CodeIgniter\Database\Forge;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * Study settings for data collection (research revision 10, part 7: 33 §6,
 * §10, §13.1 `StudySettingsTest`; `test_rapid_guess_ms`: owner, 4 November
 * 2026): only the head researcher changes them, within limits, with an audit
 * of old and new values; the idle threshold never goes below the 20 s
 * logging limit; once part 9 locks the thresholds only the stage width moves.
 *
 * @internal
 */
final class StudySettingsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testDefaults(): void
    {
        $row = $this->study();
        $this->assertSame([180000, 3000, 960, 3000], [(int) $row['idle_threshold_ms'], (int) $row['rapid_guess_ms'], (int) $row['min_stage_px'], (int) $row['test_rapid_guess_ms']]);
    }

    public function testHeadResearcherChangesThemWithAnAudit(): void
    {
        $this->save('ketua', ['idle_threshold_s' => '120', 'rapid_guess_s' => '2,5', 'test_rapid_guess_s' => '4', 'min_stage_px' => '1024']);

        $this->assertSame(lang('Admin.studySettingsSaved'), $_SESSION['message'] ?? null);
        $row = $this->study();
        $this->assertSame([120000, 2500, 1024, 4000], [(int) $row['idle_threshold_ms'], (int) $row['rapid_guess_ms'], (int) $row['min_stage_px'], (int) $row['test_rapid_guess_ms']]);

        $audit = $this->conn->table('audit_logs')->where('action', 'study_settings_changed')->get()->getRowArray();
        $this->assertSame([
            'from' => ['idle_threshold_ms' => 180000, 'rapid_guess_ms' => 3000, 'test_rapid_guess_ms' => 3000, 'min_stage_px' => 960],
            'to'   => ['idle_threshold_ms' => 120000, 'rapid_guess_ms' => 2500, 'test_rapid_guess_ms' => 4000, 'min_stage_px' => 1024],
        ], json_decode((string) $audit['metadata_json'], true));
        $this->assertSame(['3', 'research_study', '1'], [(string) $audit['staff_user_id'], $audit['target_type'], $audit['target_id']]);
    }

    public function testOthersCannotChangeThem(): void
    {
        foreach (['admin', 'peneliti'] as $role) {
            try {
                $this->save($role, ['min_stage_px' => '1024']);
            } catch (CodeIgniter\Exceptions\PageNotFoundException) {
                // staffCan hides the page from roles without studies.manage
            }

            $this->assertSame(960, (int) $this->study()['min_stage_px'], $role);
        }

        $this->assertSame(0, $this->conn->table('audit_logs')->where('action', 'study_settings_changed')->countAllResults());
    }

    public function testLimits(): void
    {
        foreach ([
            [['idle_threshold_s' => '15'], 'Admin.studyIdleTooLow'],
            [['idle_threshold_s' => '29'], 'Admin.studyIdleTooLow'],
            [['idle_threshold_s' => '1801'], 'Admin.studyIdleRange'],
            [['rapid_guess_s' => '0.4'], 'Admin.studyRapidRange'],
            [['rapid_guess_s' => '11'], 'Admin.studyRapidRange'],
            [['test_rapid_guess_s' => 'abc'], 'Admin.studyRapidRange'],
            [['min_stage_px' => '600'], 'Admin.studyMinStageRange'],
            [['min_stage_px' => '1300'], 'Admin.studyMinStageRange'],
        ] as [$values, $key]) {
            $this->save('ketua', $values);
            $this->assertSame(lang($key), $_SESSION['error'] ?? null, json_encode($values));
        }

        $this->assertSame([180000, 3000, 960], [(int) $this->study()['idle_threshold_ms'], (int) $this->study()['rapid_guess_ms'], (int) $this->study()['min_stage_px']]);
    }

    public function testLoggingLimitAboveThirtySecondsStillGuards(): void
    {
        config('Gelita')->inputIdleLogMs = 45000;

        try {
            $this->save('ketua', ['idle_threshold_s' => '40']);
            $this->assertSame(lang('Admin.studyIdleTooLow'), $_SESSION['error'] ?? null);
        } finally {
            config('Gelita')->inputIdleLogMs = 20000;
        }
    }

    public function testLockedThresholdsStayButTheStageWidthMoves(): void
    {
        // Part 9's column (35 §12, migration 004300) is in the test schema
        $this->conn->table('research_studies')->update(['thresholds_locked_at' => date('Y-m-d H:i:s')]);

        $this->save('ketua', ['idle_threshold_s' => '120']);
        $this->assertSame(lang('Admin.studyThresholdsLocked'), $_SESSION['error'] ?? null);

        $this->save('ketua', ['test_rapid_guess_s' => '5']);
        $this->assertSame(lang('Admin.studyThresholdsLocked'), $_SESSION['error'] ?? null);

        $this->save('ketua', ['idle_threshold_s' => '180', 'min_stage_px' => '900']);
        $this->assertSame(lang('Admin.studySettingsSaved'), $_SESSION['message'] ?? null);
        $this->assertSame([180000, 900], [(int) $this->study()['idle_threshold_ms'], (int) $this->study()['min_stage_px']]);

        $this->asStaff('ketua');
        $page = (string) $this->send(Method::GET, 'admin/studi')->getBody();
        $this->assertStringContainsString(lang('Admin.studyThresholdsLocked'), $page);
        $this->assertStringContainsString('readonly aria-readonly="true"', $page);
    }

    /** @return array<string, mixed> */
    private function study(): array
    {
        return $this->conn->table('research_studies')->where('id', 1)->get()->getRowArray();
    }

    private function save(string $role, array $values): void
    {
        unset($_SESSION['error'], $_SESSION['message']);
        $this->asStaff($role);
        $this->send(Method::POST, 'admin/studi/1/pengaturan', $values);
    }
}
