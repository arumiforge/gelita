<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * Small screens are refused before a field session is entered (research
 * revision 10, part 7: 33 §7.2, K7, §17 no. 7, §13.1 `SmallScreenTokenTest`):
 * `/token` and step 0 of `/daftar` take `stage_w` and refuse a stage below
 * the study's `min_stage_px` in field mode, accept an empty value, and write
 * the audit `small_screen_refused` without student data. The monitoring
 * panel counts it per field session.
 *
 * @internal
 */
final class SmallScreenTokenTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita']);
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testTokenScreenRefusesAPhoneAndAcceptsALaptopOrNoWidth(): void
    {
        $fs = $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->signIn(1);

        $this->assertStringContainsString('name="stage_w"', (string) $this->send(Method::GET, 'token')->getBody());

        $this->assertRedirectsTo('token', $this->send(Method::POST, 'token', ['token' => 'GHJKMN', 'stage_w' => '693']));
        $this->assertSame(lang('Game.smallScreenBody'), $_SESSION['errors']['token'] ?? null);
        $this->assertSame(0, $this->conn->table('game_sessions')->where('field_session_id', $fs['id'])->countAllResults(), 'no game session from a phone');

        $audit = $this->conn->table('audit_logs')->where('action', 'small_screen_refused')->get()->getResultArray();
        $this->assertCount(1, $audit);
        $this->assertSame(['field_session', (string) $fs['id'], null, null], [$audit[0]['target_type'], $audit[0]['target_id'], $audit[0]['staff_user_id'], $audit[0]['ip_hash']]);
        $this->assertSame(['stage_w' => 693, 'min_stage_px' => 960, 'via' => 'token'], json_decode((string) $audit[0]['metadata_json'], true));

        // Counted on the monitoring panel
        $summary = service('analyticsService')->fieldSessionSummary(1);
        $this->assertSame(1, $summary[0]['small_screen']);

        $this->signIn(1);
        $this->assertRedirectsTo('peta', $this->send(Method::POST, 'token', ['token' => 'GHJKMN', 'stage_w' => '1024']));

        $_SESSION = [];
        $this->signIn(1);
        $this->conn->table('game_sessions')->where('field_session_id', $fs['id'])->delete();
        $this->assertRedirectsTo('peta', $this->send(Method::POST, 'token', ['token' => 'GHJKMN', 'stage_w' => '']), 'JavaScript off: accepted');
    }

    public function testTheStudySettingDecides(): void
    {
        $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->conn->table('research_studies')->update(['min_stage_px' => 640]);
        $this->signIn(1);

        $this->assertRedirectsTo('peta', $this->send(Method::POST, 'token', ['token' => 'GHJKMN', 'stage_w' => '693']));
    }

    public function testRegistrationStepZeroRefusesAPhone(): void
    {
        $fs = $this->fieldSession($this->conn, 1, 'GHJKMN');

        $this->assertRedirectsTo('daftar/0', $this->send(Method::POST, 'daftar/0', ['token' => 'GHJKMN', 'stage_w' => '700']));
        $this->assertSame(lang('Game.smallScreenBody'), $_SESSION['errors']['token'] ?? null);

        $audit = $this->conn->table('audit_logs')->where('action', 'small_screen_refused')->get()->getRowArray();
        $this->assertSame([(string) $fs['id'], 'daftar'], [$audit['target_id'], json_decode((string) $audit['metadata_json'], true)['via']]);

        $this->assertRedirectsTo('daftar/1', $this->send(Method::POST, 'daftar/0', ['token' => 'GHJKMN', 'stage_w' => '1366']));
    }

    public function testOutsideFieldModeNothingIsRefused(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $this->resetFieldServices();

        $this->assertFalse(service('fieldSessions')->refuseSmallScreen(['id' => 1], '600', 'token'));
        $this->assertSame(0, $this->conn->table('audit_logs')->where('action', 'small_screen_refused')->countAllResults());
    }
}
