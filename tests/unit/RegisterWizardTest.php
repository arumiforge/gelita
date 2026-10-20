<?php

use App\Controllers\Game\RegisterController;
use App\Models\ParticipantModel;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Pendaftaran tiga langkah (perombakan kelas 5, Dok 1 §10), lewat router
 * sungguhan dan tanpa JavaScript: urutan langkah, penjaga lompat langkah,
 * aturan isian, kata sandi yang tidak pernah masuk session, validasi ulang
 * di langkah 3, metrik sandi, dan baris yang ditulis saat akun dibuat.
 *
 * @internal
 */
final class RegisterWizardTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ResetTables;

    /** Hash CSRF (mode session); token POST = hash . kunci nol (tokenRandomize). */
    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private const STRONG = 'Kopi-Kedu7!';
    private const WEAK   = 'kopikedu';

    private const ABOUT = ['display_name' => 'Sekar Ayu', 'age' => '10', 'gender' => 'perempuan'];

    /** Kabupaten Magelang, NPSN SD resmi dari direktori */
    private const SCHOOL = ['country_code' => 'ID', 'province_code' => '33', 'district_code' => '33.08', 'school_npsn' => '20307000'];

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = $this->createResetTables();
        $this->conn->table('research_studies')->insert(['code' => 'STUDI-UJI', 'name' => 'Studi uji', 'status' => 'active']);
        $this->conn->table('game_releases')->insert([
            'release_code' => 'GELITA-UJI', 'app_version' => '2.0.0', 'content_version' => 'kelas5-1',
            'asset_version' => '1', 'scoring_version' => 'bintang-1', 'is_active' => 1,
        ]);
        $this->conn->table('schools')->insertBatch([
            ['code' => '20307000', 'name' => 'SD Negeri Uji 1', 'stage' => 'sd', 'province_code' => '33', 'district_code' => '33.08', 'is_verified' => 1, 'is_active' => 1],
            ['code' => '20307999', 'name' => 'SMP Negeri Uji 1', 'stage' => 'smp', 'province_code' => '33', 'district_code' => '33.08', 'is_verified' => 1, 'is_active' => 1],
        ]);

        foreach (['security', 'schoolDirectory', 'sessionService', 'eventService', 'gameContext'] as $name) {
            Services::resetSingle($name);
        }

        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        Factories::reset('models');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testStepsGoInOrderAndCannotBeSkipped(): void
    {
        $this->assertRedirectsTo('daftar/1', $this->send(Method::GET, 'daftar'));
        $this->assertRedirectsTo('daftar/1', $this->send(Method::GET, 'daftar/2'));
        $this->assertRedirectsTo('daftar/1', $this->send(Method::GET, 'daftar/3'));
        $this->assertRedirectsTo('daftar/1', $this->send(Method::POST, 'daftar/3', ['username' => 'sekar', 'password' => self::STRONG]));
        $this->assertSame(0, $this->conn->table('participants')->countAllResults());

        $page = $this->send(Method::GET, 'daftar/1');
        $page->assertOK();
        $html = (string) $page->getBody();

        $this->assertStringContainsString('name="display_name"', $html);
        $this->assertStringContainsString('name="age"', $html);
        $this->assertSame(2, substr_count($html, 'name="gender"'), 'hanya laki-laki dan perempuan');
        $this->assertStringNotContainsString('lainnya', $html);
        $this->assertStringContainsString('Kelas 5 SD/MI', $html, 'kelas tidak diisi, dikunci 5');

        $this->assertRedirectsTo('daftar/2', $this->send(Method::POST, 'daftar/1', self::ABOUT));
        $this->assertRedirectsTo('daftar/2', $this->send(Method::GET, 'daftar'));
        $this->assertRedirectsTo('daftar/2', $this->send(Method::GET, 'daftar/3'));
        $this->send(Method::GET, 'daftar/1')->assertOK();   // kembali ke langkah selesai tetap boleh
    }

    /** Owner redesign (18 October 2026): the gate picture, Jaka as one still picture, the ornate panel on the right. */
    public function testStepsShowTheGatePictureWithTheOrnatePanel(): void
    {
        $_SESSION[RegisterController::WIZARD] = ['1' => self::ABOUT, '2' => self::SCHOOL];

        foreach (['1', '2', '3'] as $step) {
            $html = (string) $this->send(Method::GET, 'daftar/' . $step)->getBody();

            $this->assertStringContainsString('register-panel ornate-panel stage-box', $html, "step {$step}");
            $this->assertStringContainsString('class="ornate"', $html, "step {$step}: gold frame");
            $this->assertStringNotContainsString('panel-parchment', $html, "step {$step}");
            $this->assertStringContainsString('src="' . asset_url_versioned('ui/bg-auth-register.webp') . '"', $html, "step {$step}");
            $this->assertStringNotContainsString('data-character="jaka"', $html, "step {$step}: no animated figure");
            $this->assertSame(1, substr_count($html, 'class="auth-jaka stage-box"'), "step {$step}: Jaka once, still");
        }
    }

    /** Owner, 18 October 2026: one HUD everywhere; before login Back comes first, then the logo (text brand without one). */
    public function testBackSitsInTheHudBeforeTheLogo(): void
    {
        $back = ['1' => 'mulai', '2' => 'daftar/1', '3' => 'daftar/2'];
        $_SESSION[RegisterController::WIZARD] = ['1' => self::ABOUT, '2' => self::SCHOOL];

        foreach ($back as $step => $target) {
            $html = (string) $this->send(Method::GET, 'daftar/' . $step)->getBody();
            $at   = (int) strpos($html, '<header class="hud hud-menu-style is-compact"');
            $hud  = substr($html, $at, (int) strpos($html, '</header>') - $at);

            $this->assertStringContainsString('class="btn btn-quiet hud-back" href="' . base_url($target) . '"', html_entity_decode($hud), "step {$step}");
            $this->assertLessThan(strpos($hud, 'hud-brand'), strpos($hud, 'hud-back'), "step {$step}: Back, then the brand");
            $this->assertStringNotContainsString('hud-shards', $hud, "step {$step}: no shards before login");

            $actions = substr($html, (int) strpos($html, 'register-actions'));
            $this->assertStringNotContainsString('btn-quiet', substr($actions, 0, (int) strpos($actions, '</div>')), "step {$step}: no Back in the panel");
        }
    }

    public function testStepOneRules(): void
    {
        $invalid = [
            'umur 7'           => [['age' => '7'], 'age'],
            'umur 16'          => [['age' => '16'], 'age'],
            'umur bukan angka' => [['age' => 'sepuluh'], 'age'],
            'gender lainnya'   => [['gender' => 'lainnya'], 'gender'],
            'gender kosong'    => [['gender' => ''], 'gender'],
            'nama 1 huruf'     => [['display_name' => 'S'], 'display_name'],
        ];

        foreach ($invalid as $label => [$change, $field]) {
            $_SESSION = [];

            $this->assertRedirectsTo('daftar/1', $this->send(Method::POST, 'daftar/1', $change + self::ABOUT), $label);
            $this->assertArrayHasKey($field, $_SESSION['errors'] ?? [], $label);
            $this->assertArrayNotHasKey(RegisterController::WIZARD, $_SESSION, $label);
        }

        foreach (['8', '15'] as $age) {
            $_SESSION = [];

            $this->assertRedirectsTo('daftar/2', $this->send(Method::POST, 'daftar/1', ['age' => $age] + self::ABOUT), "umur {$age}");
            $this->assertSame($age, $_SESSION[RegisterController::WIZARD]['1']['age']);
        }

        $this->assertSame([], array_diff_key($_SESSION[RegisterController::WIZARD]['1'], array_flip(['display_name', 'age', 'gender', 'avatar_no'])), 'hanya isian langkah 1');
    }

    public function testErrorsNameTheFieldAsShownOnScreen(): void
    {
        // A name of only spaces passes the browser's `required` but is empty after trimming
        $this->send(Method::POST, 'daftar/1', ['display_name' => '   '] + self::ABOUT);
        $this->assertSame('Nama lengkap wajib diisi.', $_SESSION['errors']['display_name'] ?? null);

        $_SESSION = [];
        $this->send(Method::POST, 'daftar/1', self::ABOUT);
        $this->send(Method::POST, 'daftar/2', ['country_code' => 'ID', 'school_name' => 'SD Uji']);

        foreach (['province_code' => 'Provinsi', 'district_code' => 'Kabupaten / kota'] as $field => $label) {
            $this->assertSame($label . ' wajib diisi.', $_SESSION['errors'][$field] ?? null, $field);
        }
    }

    public function testFullRegistrationWithNpsnNeverKeepsThePassword(): void
    {
        $this->send(Method::POST, 'daftar/1', self::ABOUT + ['class_level' => '6']);

        // Langkah 2: NPSN wajib di provinsi berdirektori, jenjang harus SD/SLB
        $this->assertRedirectsTo('daftar/2', $this->send(Method::POST, 'daftar/2', ['school_npsn' => ''] + self::SCHOOL));
        $this->assertArrayHasKey('school_npsn', $_SESSION['errors']);
        $this->assertRedirectsTo('daftar/2', $this->send(Method::POST, 'daftar/2', ['school_npsn' => '20307999'] + self::SCHOOL));
        $this->assertArrayHasKey('school_npsn', $_SESSION['errors'], 'SMP ditolak');
        $this->assertRedirectsTo('daftar/3', $this->send(Method::POST, 'daftar/2', self::SCHOOL));

        // Kembali ke langkah 2: isian tetap ada, kartu sekolah tampil
        $back = (string) $this->send(Method::GET, 'daftar/2')->getBody();
        $this->assertStringContainsString('value="20307000"', $back);
        $this->assertStringContainsString('SD Negeri Uji 1', $back);
        $this->assertSame(0, $this->conn->table('participants')->countAllResults(), 'belum ada data peserta sebelum langkah 3');

        $step3 = (string) $this->send(Method::GET, 'daftar/3')->getBody();
        $this->assertStringContainsString('name="assent"', $step3);
        $this->assertStringContainsString('name="device_json"', $step3);

        // Sandi lemah: kembali ke langkah 3, metrik tercatat, sandi tidak disimpan
        $this->assertRedirectsTo('daftar/3', $this->send(Method::POST, 'daftar/3', $this->account(self::WEAK)));
        $this->assertArrayHasKey('password', $_SESSION['errors']);
        $this->assertSame(1, $_SESSION['reg_weak_count']);
        $firstCriteria = $_SESSION['reg_first_criteria'];
        $this->assertIsInt($firstCriteria);
        $this->assertSame('sekar_ayu', $_SESSION['_ci_old_input']['post']['username'] ?? null, 'nama pengguna boleh diisi ulang');
        $this->assertStringNotContainsString(self::WEAK, json_encode($_SESSION));

        // Sandi kuat tetapi belum mencentang persetujuan
        $this->assertRedirectsTo('daftar/3', $this->send(Method::POST, 'daftar/3', ['assent' => ''] + $this->account(self::STRONG)));
        $this->assertArrayHasKey('assent', $_SESSION['errors']);
        $this->assertSame(1, $_SESSION['reg_weak_count'], 'sandi kuat tidak menambah hitungan');
        $this->assertSame($firstCriteria, $_SESSION['reg_first_criteria'], 'hanya kiriman pertama');
        $this->assertStringNotContainsString(self::STRONG, json_encode($_SESSION));

        // Berhasil
        $device = json_encode(['screen_w' => 1366, 'screen_h' => 768, 'touch_points' => 0, 'timezone' => 'Asia/Jakarta']);
        $this->assertRedirectsTo('intro', $this->send(Method::POST, 'daftar/3', ['device_json' => $device] + $this->account(self::STRONG)));
        $this->assertStringNotContainsString(self::STRONG, json_encode($_SESSION));
        $this->assertArrayNotHasKey(RegisterController::WIZARD, $_SESSION);
        $this->assertArrayNotHasKey('reg_weak_count', $_SESSION);

        $participant = $this->conn->table('participants')->get()->getRowArray();
        $this->assertSame('GLT-000001', $participant['participant_code']);
        $this->assertSame(['sekar_ayu', 'Sekar Ayu', 10, 'perempuan', '5'], [
            $participant['username'], $participant['display_name'], (int) $participant['age'], $participant['gender'], $participant['class_level'],
        ]);
        $this->assertNotNull($participant['assented_at']);
        $this->assertSame(1, (int) $participant['school_id']);
        $this->assertSame([$firstCriteria, 1], [(int) $participant['pw_first_submit_criteria'], (int) $participant['pw_weak_submit_count']]);
        $this->assertTrue(password_verify(self::STRONG, $participant['password_hash']));
        $this->assertSame((int) $participant['id'], (int) $_SESSION['participant_id']);

        $log = $this->conn->table('device_logs')->get()->getRowArray();
        $this->assertSame(['register', 1366, 768, 'Asia/Jakarta'], [$log['via'], (int) $log['screen_w'], (int) $log['screen_h'], $log['timezone']]);
        $this->assertSame((int) $_SESSION['game_session_id'], (int) $log['session_id']);
        $this->assertSame('1366x768', $this->conn->table('game_sessions')->get()->getRow('screen_size'));

        $events = array_column($this->conn->table('game_event_logs')->select('event_type, payload_json')->get()->getResultArray(), 'payload_json', 'event_type');
        $this->assertArrayHasKey('session_started', $events);
        $this->assertStringNotContainsString(self::STRONG, implode(' ', $events));
    }

    /**
     * Two students finishing step 3 together: the other registration commits
     * between this one's start and its INSERT. Under the old MAX(id) + 1 code
     * both picked the same code and this sign-up failed with HTTP 500.
     */
    public function testRegistrationCommittedAlongsideGetsItsOwnCode(): void
    {
        Factories::injectMock('models', ParticipantModel::class, new class () extends ParticipantModel {
            public function insert($row = null, bool $returnID = true)
            {
                $rival = (int) $this->db->table('participants')->selectMax('id', 'max_id')->get()->getRow('max_id') + 1;
                $this->db->table('participants')->insert([
                    'participant_code' => participant_code($rival),
                    'username'         => 'siswa_lain',
                    'password_hash'    => 'x',
                ]);

                return parent::insert($row, $returnID);
            }
        });

        $result = service('sessionService')->registerAndStart([
            'username' => 'sekar_ayu', 'password' => self::STRONG, 'assent' => true, 'school_npsn' => '20307000',
            'country_code' => 'ID', 'province_code' => '33', 'district_code' => '33.08',
        ]);

        $codes = array_column($this->conn->table('participants')->orderBy('id')->get()->getResultArray(), 'participant_code', 'username');
        $this->assertSame(['siswa_lain' => 'GLT-000001', 'sekar_ayu' => 'GLT-000002'], $codes);
        $this->assertSame('GLT-000002', $result['participant']->participant_code);
    }

    public function testStepThreeRevalidatesTheSavedSteps(): void
    {
        $_SESSION[RegisterController::WIZARD] = [
            '1' => ['gender' => 'lainnya'] + self::ABOUT,     // session diubah dari luar
            '2' => self::SCHOOL + ['country_other' => '', 'school_manual' => '', 'school_name' => ''],
        ];

        $this->assertRedirectsTo('daftar/1', $this->send(Method::POST, 'daftar/3', $this->account(self::STRONG)));
        $this->assertArrayHasKey('gender', $_SESSION['errors']);
        $this->assertArrayNotHasKey('1', $_SESSION[RegisterController::WIZARD]);
        $this->assertSame(0, $this->conn->table('participants')->countAllResults());
    }

    public function testOtherCountryWritesTheSchoolName(): void
    {
        $this->send(Method::POST, 'daftar/1', self::ABOUT);

        $abroad = ['country_code' => 'XX', 'country_other' => 'Malaysia'];   // "negara lain" di formulir

        $this->assertRedirectsTo('daftar/2', $this->send(Method::POST, 'daftar/2', $abroad + ['school_name' => '']));
        $this->assertArrayHasKey('school_name', $_SESSION['errors']);
        $this->assertRedirectsTo('daftar/3', $this->send(Method::POST, 'daftar/2', $abroad + ['school_name' => 'Sekolah Indonesia Kuala Lumpur']));
        $this->assertRedirectsTo('intro', $this->send(Method::POST, 'daftar/3', $this->account(self::STRONG)));

        $participant = $this->conn->table('participants')->get()->getRowArray();
        $this->assertSame(['XX', 'Malaysia', null], [$participant['country_code'], $participant['country_name_snapshot'], $participant['province_code']]);
        $this->assertSame('Sekolah Indonesia Kuala Lumpur', $participant['school_name_snapshot']);
        $this->assertSame(0, (int) $this->conn->table('schools')->where('name', 'Sekolah Indonesia Kuala Lumpur')->get()->getRow('is_verified'), 'nama ketikan = sekolah belum terverifikasi');
    }

    // ------------------------------------------------------------ bantu

    /** @return array<string, string> */
    private function account(string $password): array
    {
        return ['username' => 'Sekar_Ayu', 'password' => $password, 'password_confirm' => $password, 'assent' => '1', 'locale' => 'id'];
    }

    /**
     * Satu request dengan session yang berlanjut dari request sebelumnya
     * (seperti peramban) dan token CSRF yang sah.
     *
     * @param array<string, string> $params
     */
    private function send(string $method, string $path, array $params = []): TestResponse
    {
        Services::resetSingle('security');
        unset($_SESSION['errors'], $_SESSION['_ci_old_input'], $_SESSION['__ci_vars']);

        $session = ['gelita_csrf' => self::CSRF_HASH] + $_SESSION;

        if ($method === Method::POST) {
            $params['gelita_csrf'] = self::CSRF_HASH . str_repeat('0', 32);
        }

        return $this->withSession($session)->call($method, $path, $method === Method::POST ? $params : null);
    }

    private function assertRedirectsTo(string $path, TestResponse $response, string $message = ''): void
    {
        $this->assertTrue($response->isRedirect(), trim($message . ' (bukan pengalihan: ' . $response->response()->getStatusCode() . ')'));
        $this->assertSame(site_url($path), $response->getRedirectUrl(), $message);
    }
}
