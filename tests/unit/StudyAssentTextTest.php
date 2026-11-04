<?php

use App\Controllers\Game\RegisterController;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * The research assent text T4 in field mode, the general text outside it
 * (research revision 10, part 4: 30 §5.1, §5.2, §19.1 `StudyAssentTextTest`).
 *
 * @internal
 */
final class StudyAssentTextTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private const ABOUT = ['display_name' => 'Sekar Ayu', 'age' => '10', 'gender' => 'perempuan'];

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

    public function testFieldModeUsesTheStudyKeysAndMakesTheConsentRow(): void
    {
        $this->fieldSession($this->conn, 1, 'GHJKMN');
        $this->assertRedirectsTo('daftar/1', $this->send(Method::POST, 'daftar/0', ['token' => 'GHJKMN']));
        $this->assertRedirectsTo('daftar/3', $this->send(Method::POST, 'daftar/1', self::ABOUT));

        $page = (string) $this->send(Method::GET, 'daftar/3')->getBody();
        $this->assertStringContainsString(esc(lang('Game.assentLabelStudy')), $page);
        $this->assertStringContainsString(lang('Game.assentNameStudy'), $page);
        $this->assertStringNotContainsString(esc(lang('Game.assentLabel')), $page);

        $account = ['username' => 'sekar_ayu', 'password' => 'Kopi-Kedu7!', 'password_confirm' => 'Kopi-Kedu7!', 'locale' => 'id'];
        $this->send(Method::POST, 'daftar/3', $account);
        $this->assertSame(lang('Game.assentRequiredStudy'), $_SESSION['errors']['assent'] ?? null);

        $this->assertRedirectsTo('intro', $this->send(Method::POST, 'daftar/3', $account + ['assent' => '1']));
        $row = $this->conn->table('participants')->where('username', 'sekar_ayu')->get()->getRowArray();
        $this->assertSame('t4-1', $row['assent_version']);
        $this->assertNotNull($row['assented_at']);

        $consent = service('consentService')->forParticipant((int) $row['id']);
        $this->assertSame([1, null, 0, 0, 'active'], [(int) $consent['study_id'], $consent['parent_consent_on'], (int) $consent['recording_consent'], (int) $consent['risk_items_consent'], $consent['status']]);
    }

    public function testOutsideFieldModeTheGeneralTextStays(): void
    {
        $this->conn->table('research_studies')->update(['field_mode' => 0]);
        $_SESSION[RegisterController::WIZARD] = [
            '1' => self::ABOUT,
            '2' => ['country_code' => 'MY', 'country_other' => 'Malaysia', 'school_manual' => '', 'school_name' => 'Sekolah Indonesia Kuala Lumpur'],
        ];

        $page = (string) $this->send(Method::GET, 'daftar/3')->getBody();
        $this->assertStringContainsString(esc(lang('Game.assentLabel')), $page);
        $this->assertStringNotContainsString(esc(lang('Game.assentLabelStudy')), $page);

        $result = service('sessionService')->registerAndStart([
            'username' => 'bayu_aji', 'password' => 'Kopi-Kedu7!', 'assent' => true, 'country_code' => 'MY', 'school_name' => 'SD Luar',
        ]);
        $this->assertSame('umum-1', $result['participant']->assent_version);
        $this->assertNull(service('consentService')->forParticipant($result['participant']->id), 'no consent row outside field mode');
    }
}
