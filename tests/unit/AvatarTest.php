<?php

use App\Controllers\Game\RegisterController;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Display plan Phase B (docs/arsip/rencana-perbaikan-tampilan.md): avatars.
 *
 * - Only uploaded `avatar.N` slots are offered; with none uploaded the picker
 *   is not rendered and the HUD/profile show the initial as before.
 * - Sign-up step 1 keeps the choice in the wizard and step 3 saves it.
 * - The profile saves a new choice with one tap (POST /profil/avatar); an
 *   avatar that is not uploaded is refused.
 * - `avatar_no` is not research data: not in the exports.
 *
 * @internal
 */
final class AvatarTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $this->conn = $this->createGradeFiveWorld();
        $_SESSION   = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        Factories::reset('models');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testOnlyUploadedAvatarsAreOffered(): void
    {
        $this->assertSame([], avatar_choices());
        $this->assertNull(avatar_src(2));

        $this->upload(2);
        $this->upload(5);

        $this->assertSame([2, 5], array_keys(avatar_choices()));
        $this->assertStringContainsString('assets/uploads/avatar.5.png?v=', (string) avatar_src(5));
        $this->assertNull(avatar_src(3), 'an empty slot falls back to the initial');
        $this->assertNull(avatar_src(null));
    }

    public function testPickerIsHiddenUntilAvatarsAreUploaded(): void
    {
        $this->assertStringNotContainsString('class="field avatar-picker', $this->page('daftar/1'));

        $this->upload(1);
        $this->upload(2);
        $html = $this->page('daftar/1');

        $this->assertStringContainsString('class="field avatar-picker', $html);
        $this->assertSame(3, substr_count($html, 'name="avatar_no"'), 'initial + two avatars');
        $this->assertMatchesRegularExpression('/<input type="radio" name="avatar_no" value="" checked>/', $html, 'the initial is the default');
        $this->assertStringContainsString(lang('Game.avatarInitial'), $html);
    }

    public function testSignUpKeepsAndSavesTheChoice(): void
    {
        $this->upload(3);

        $about = ['display_name' => 'Sekar Ayu', 'age' => '10', 'gender' => 'perempuan'];

        // An avatar that is not uploaded is refused
        $this->send(Method::POST, 'daftar/1', $about + ['avatar_no' => '4']);
        $this->assertSame(lang('Game.avatarInvalid'), $_SESSION['errors']['avatar_no'] ?? null);

        $_SESSION = [];
        $this->send(Method::POST, 'daftar/1', $about + ['avatar_no' => '3']);
        $this->assertSame('3', $_SESSION[RegisterController::WIZARD]['1']['avatar_no']);

        // Coming back to step 1 shows the choice
        $this->assertMatchesRegularExpression('/value="3" checked/', $this->page('daftar/1'));

        $service = service('sessionService');
        $result  = $service->registerAndStart([
            'username' => 'sekar_ayu', 'password' => 'Kopi-Kedu7!', 'display_name' => 'Sekar Ayu', 'age' => 10, 'gender' => 'perempuan',
            'class_level' => '5', 'school_name' => 'SD Uji', 'country_code' => 'XX', 'country_name' => 'Negara Uji',
            'locale' => 'id', 'assent' => true, 'avatar_no' => '3', 'device' => [],
        ]);
        $this->assertSame(3, (int) $this->conn->table('participants')->where('id', $result['participant']->id)->get()->getRow('avatar_no'));
    }

    public function testProfileChangesTheAvatarWithOneTap(): void
    {
        $this->upload(1);
        $this->upload(2);

        $profile = $this->page('profil', true);
        $this->assertStringContainsString(lang('Game.avatarChange'), $profile);
        $this->assertStringContainsString('name="avatar_no" value="2" aria-pressed="false"', $profile);
        $this->assertMatchesRegularExpression('/<span class="user-avatar" aria-hidden="true">J<\/span>/', $profile, 'HUD shows the initial');

        $this->send(Method::POST, 'profil/avatar', ['avatar_no' => '2'], true)->assertRedirectTo(site_url('profil'));
        $this->assertSame(2, (int) $this->participantRow()['avatar_no']);
        $this->assertSame(lang('Game.avatarSaved'), $_SESSION['message'] ?? null);

        $profile = $this->page('profil', true);
        $this->assertStringContainsString('<span class="user-avatar has-art" aria-hidden="true"><img src="', $profile);
        $this->assertStringContainsString('<span class="profile-avatar has-art" aria-hidden="true"><img src="', $profile);
        $this->assertStringContainsString('name="avatar_no" value="2" aria-pressed="true"', $profile);

        // Not uploaded → refused, unchanged
        $this->send(Method::POST, 'profil/avatar', ['avatar_no' => '9'], true);
        $this->assertSame(2, (int) $this->participantRow()['avatar_no']);
        $this->assertSame(lang('Game.avatarInvalid'), $_SESSION['error'] ?? null);

        // Back to the initial
        $this->send(Method::POST, 'profil/avatar', ['avatar_no' => ''], true);
        $this->assertNull($this->participantRow()['avatar_no']);
    }

    public function testAvatarIsNotResearchData(): void
    {
        $columns = (new ReflectionClassConstant(App\Services\RawDataService::class, 'COLUMNS'))->getValue();
        $this->assertNotContains('avatar_no', $columns['peserta']);
    }

    /** Marks `avatar.N` as uploaded (the file itself is not needed to render URLs). */
    private function upload(int $n): void
    {
        $key = "avatar.{$n}";
        $row = ['asset_type' => 'image', 'storage_path' => "assets/uploads/{$key}.png", 'mime_type' => 'image/png', 'is_active' => 1, 'sha256' => str_repeat((string) $n, 64)];

        if ($this->conn->table('media_assets')->where('asset_key', $key)->countAllResults() > 0) {
            $this->conn->table('media_assets')->where('asset_key', $key)->update($row);
        } else {
            $this->conn->table('media_assets')->insert(['asset_key' => $key, 'version' => '1'] + $row);
        }

        service('contentRepository')->flush();
        service('cache')->clean();
    }

    /** @return array<string, mixed> */
    private function participantRow(): array
    {
        return $this->conn->table('participants')->where('id', 1)->get()->getRowArray();
    }

    private function page(string $path, bool $playing = false): string
    {
        $response = $this->send(Method::GET, $path, [], $playing);
        $response->assertOK();

        return (string) $response->response()->getBody();
    }

    /** @param array<string, string> $params */
    private function send(string $method, string $path, array $params = [], bool $playing = false): TestResponse
    {
        foreach (['security', 'gameContext', 'sessionService', 'eventService'] as $name) {
            Services::resetSingle($name);
        }
        unset($_SESSION['errors'], $_SESSION['_ci_old_input'], $_SESSION['__ci_vars'], $_SESSION['message'], $_SESSION['error']);

        $session = ['gelita_csrf' => self::CSRF_HASH] + ($playing ? ['participant_id' => 1, 'game_session_id' => 1] : []) + $_SESSION;

        if ($method === Method::POST) {
            $params['gelita_csrf'] = self::CSRF_HASH . str_repeat('0', 32);
        }

        return $this->withSession($session)->call($method, $path, $method === Method::POST ? $params : null);
    }
}
