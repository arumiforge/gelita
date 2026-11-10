<?php

use App\Commands\ContentVerify;
use App\Models\AssetPackageModel;
use App\Models\GameReleaseModel;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\PackageWorld;

/**
 * Rilis → Paket aset, the package mode switch (docs/rencana-paket-aset.md
 * §6.3, §8.7, §9.2, §11.3 `PackageSwitchTest`): switching on builds in
 * batches and activates a release that names the new package; switching off
 * activates one without; both need a confirmation and a reason during a
 * running study; the changed-asset notice; the server kill switch; the lab
 * online permission; staff preview.
 *
 * @internal
 */
final class PackageSwitchTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use PackageWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const CSRF      = ['gelita_csrf' => self::CSRF_HASH . '00000000000000000000000000000000'];

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content', 'ui']);
        $this->createPackageWorld();
        config('Gelita')->paket['copyLibraryImages'] = false;
        config('Gelita')->paket['aktif']             = true;
        $this->injectStaff('admin');
        Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        config('Gelita')->paket['aktif']             = true;
        config('Gelita')->paket['copyLibraryImages'] = true;
        $this->dropPackageWorld();
        parent::tearDown();
    }

    public function testSwitchingOnBuildsInBatchesAndActivatesAReleaseWithThePackage(): void
    {
        $before = model(GameReleaseModel::class)->activeOrNull();
        $page   = $this->page('admin/studi/rilis/paket');
        $this->assertStringContainsString('data-package-mode="off"', $page);
        $this->assertStringContainsString('Nyalakan', $page);

        $code = $this->buildViaPanel();

        // Build summary waits for confirmation; students still have no package
        $page = $this->page('admin/studi/rilis/paket');
        $this->assertStringContainsString('data-package-pending="' . $code . '"', $page);
        $this->assertStringContainsString('Ringkasan paket baru', $page);
        $this->assertStringContainsString('Wilayah Temanggung', $page);
        $this->assertStringContainsString('Gambar contoh yang belum diganti: 1', $page);
        $this->assertStringContainsString('Rekaman narasi belum disetujui (tidak ikut paket): 1', $page);
        $this->assertStringContainsString('?paket=' . $code, $page, 'preview link');
        $this->assertNull($this->apiData()['package_code']);

        $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan')->assertRedirectTo(site_url('admin/studi/rilis/paket'));

        $release = model(GameReleaseModel::class)->activeOrNull();
        $this->assertSame($code, $release['asset_version']);
        $this->assertSame($code, $release['release_code']);
        $this->assertNotSame((int) $before['id'], (int) $release['id']);
        $this->assertSame($before['app_version'], $release['app_version']);
        $this->assertSame($before['content_version'], $release['content_version']);
        $this->assertSame($before['scoring_version'], $release['scoring_version']);
        $this->assertSame($code, $this->apiData()['package_code']);
        $this->assertSame(AssetPackageModel::STATUS_PUBLISHED, model(AssetPackageModel::class)->findByCode($code)['status']);

        $audit = $this->lastAudit('release_activate');
        $this->assertNull($audit['package_from']);
        $this->assertSame($code, $audit['package_to']);
        $this->assertNull($audit['reason']);
        $this->assertSame('paket', $audit['via']);

        $page = $this->page('admin/studi/rilis/paket');
        $this->assertStringContainsString('data-package-mode="on"', $page);
        $this->assertStringContainsString($code, $page);
        $this->assertStringContainsString('Matikan', $page);
    }

    public function testBuildWorksWithoutJavaScriptAndNeverRunsTwice(): void
    {
        $this->post('admin/studi/rilis/paket/bangun')->assertRedirectTo(site_url('admin/studi/rilis/paket'));
        $code = model(AssetPackageModel::class)->orderBy('id', 'DESC')->first()['package_code'];

        // A second Nyalakan while building resumes the same build
        $this->post('admin/studi/rilis/paket/bangun');
        $this->assertSame(1, model(AssetPackageModel::class)->countAllResults());

        $page = $this->page('admin/studi/rilis/paket');
        $this->assertStringContainsString('action="' . base_url('admin/studi/rilis/paket/bangun/' . $code . '/lanjut') . '"', $page);
        $this->assertStringContainsString('Lanjutkan membangun', $page);

        $this->post('admin/studi/rilis/paket/bangun/' . $code . '/lanjut')->assertRedirectTo(site_url('admin/studi/rilis/paket'));
        $this->assertSame(AssetPackageModel::STATUS_BUILT, model(AssetPackageModel::class)->findByCode($code)['status']);
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'package_build')->countAllResults());

        // A late batch request after the end changes nothing
        $this->assertTrue($this->ajax('admin/studi/rilis/paket/bangun/' . $code . '/lanjut')['finished']);
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'package_build')->countAllResults());

        // Unknown code
        $response = $this->asAdmin()->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->call(Method::POST, 'admin/studi/rilis/paket/bangun/pkt-2000-01-01-01/lanjut', self::CSRF);
        $this->assertSame(409, $response->response()->getStatusCode());
    }

    public function testSwitchingOffActivatesAReleaseWithoutAPackage(): void
    {
        $code = $this->switchOn();

        $this->post('admin/studi/rilis/paket/matikan')->assertRedirectTo(site_url('admin/studi/rilis/paket'));

        $release = model(GameReleaseModel::class)->activeOrNull();
        $this->assertSame('', $release['asset_version']);
        $this->assertSame('tanpa-paket-' . date('Y-m-d') . '-01', $release['release_code']);
        $this->assertNull($this->apiData()['package_code']);
        $this->assertNull($this->packages->urlForKey('bg.welcome'), 'helpers use normal URLs again');
        $this->assertSame(AssetPackageModel::STATUS_WITHDRAWN, model(AssetPackageModel::class)->findByCode($code)['status']);

        $audit = $this->lastAudit('release_activate');
        $this->assertSame($code, $audit['package_from']);
        $this->assertNull($audit['package_to']);

        // Already off: nothing to do, no extra release
        $count = model(GameReleaseModel::class)->countAllResults();
        $this->post('admin/studi/rilis/paket/matikan');
        $this->assertSame($count, model(GameReleaseModel::class)->countAllResults());

        // A second switch-off on the same day gets the next code
        $this->switchOn();
        $this->post('admin/studi/rilis/paket/matikan');
        $this->assertSame('tanpa-paket-' . date('Y-m-d') . '-02', model(GameReleaseModel::class)->activeOrNull()['release_code']);
    }

    public function testBothDirectionsNeedConfirmationAndReasonDuringARunningStudy(): void
    {
        $this->conn->table('research_studies')->insert(['code' => 'studi-uji', 'name' => 'Studi Uji', 'status' => 'active']);
        $releases = model(GameReleaseModel::class)->countAllResults();
        $code     = $this->buildViaPanel();

        // No tick, no reason: refused before any release is created
        $response = $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan');
        $response->assertRedirectTo(site_url('admin/studi/rilis/paket'));
        $this->assertStringContainsString('Studi Uji', (string) $_SESSION['error']);
        $this->assertNull($this->apiData()['package_code']);
        $this->assertSame($releases, model(GameReleaseModel::class)->countAllResults());

        // Tick without a reason: still refused
        $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan', ['confirm_running_study' => '1', 'reason' => '   ']);
        $this->assertNull($this->apiData()['package_code']);

        // The page shows the confirmation fields in the publish form
        $this->assertStringContainsString('name="confirm_running_study"', $this->page('admin/studi/rilis/paket'));

        $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan', ['confirm_running_study' => '1', 'reason' => 'Gambar Magelang diperbaiki']);
        $this->assertSame($code, $this->apiData()['package_code']);
        $audit = $this->lastAudit('release_activate');
        $this->assertSame('Gambar Magelang diperbaiki', $audit['reason']);
        $this->assertSame('studi-uji', $audit['running_study']);
        $this->assertSame($code, $audit['package_to']);

        // Switching off is locked the same way once the study runs on a package
        $this->conn->table('research_studies')->where('code', 'studi-uji')->update(['package_code' => $code]);
        $this->post('admin/studi/rilis/paket/matikan');
        $this->assertSame($code, $this->apiData()['package_code']);

        $this->post('admin/studi/rilis/paket/matikan', ['confirm_running_study' => '1', 'reason' => 'Uji coba selesai']);
        $this->assertNull($this->apiData()['package_code']);
        $audit = $this->lastAudit('release_activate');
        $this->assertSame($code, $audit['package_from']);
        $this->assertNull($audit['package_to']);
        $this->assertSame('Uji coba selesai', $audit['reason']);

        // Back to the locked package: no tick or reason needed, and the publish form asks for none
        $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan');
        $this->assertSame($code, $this->apiData()['package_code']);
        $this->assertNull($this->lastAudit('release_activate')['reason']);
    }

    public function testChangedAssetNoticeAppearsAfterOneReplacementAndClearsAfterARebuild(): void
    {
        $notice = 'Ada 1 gambar/suara yang berubah sejak paket terakhir. Siswa belum melihatnya.';

        // Package mode off: no notice (new uploads reach students directly)
        $this->replaceMedia('bg.welcome', 'welcome-v0');
        $this->assertStringNotContainsString('data-package-notice', $this->page('admin/studi/rilis/paket'));

        $this->switchOn();
        $this->assertSame(0, $this->packages->changedSinceActive()['count']);
        $this->assertStringNotContainsString('data-package-notice', $this->page('admin/studi/rilis/paket'));

        $this->replaceMedia('bg.welcome', 'welcome-v2');
        $this->packages->forget();
        $this->assertSame(1, $this->packages->changedSinceActive()['count']);

        foreach (['admin/studi/rilis/paket', 'admin/studi/rilis', 'admin/media', 'admin/konten/narasi'] as $path) {
            $html = $this->page($path);
            $this->assertStringContainsString($notice, $html, $path);
            $this->assertStringContainsString('Bangun ulang paket', $html, $path);
            $this->assertStringContainsString('action="' . base_url('admin/studi/rilis/paket/bangun') . '"', $html, $path);
        }

        // Bangun ulang paket → summary counts the one changed file → publish
        $code = $this->buildViaPanel();
        $this->assertStringContainsString('data-package-pending="' . $code . '"', $this->page('admin/studi/rilis/paket'));
        $summary = $this->packages->summary($code);
        $this->assertSame(1, $summary['new_files'], 'only the replaced image is new for devices');
        $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan');
        $this->packages->forget();

        $this->assertSame(0, $this->packages->changedSinceActive()['count']);
        $this->assertStringNotContainsString($notice, $this->page('admin/media'));
        $this->assertStringNotContainsString($notice, $this->page('admin/konten/narasi'));
    }

    public function testServerKillSwitchOverridesTheActivePackageAndIsShown(): void
    {
        $code = $this->switchOn();

        config('Gelita')->paket['aktif'] = false;
        $this->packages->forget();

        $data = $this->apiData();
        $this->assertNull($data['package_code']);
        $this->assertTrue($data['disabled_by_server']);
        $this->assertNull($this->packages->urlForKey('bg.welcome'));

        $page = $this->page('admin/studi/rilis/paket');
        $this->assertStringContainsString('Mode paket dimatikan dari server', $page);
        $this->assertStringContainsString('data-package-mode="off"', $page);
        $this->assertStringContainsString('Mode paket dimatikan dari server', $this->page('admin/studi/rilis'));

        // The release still names the package: lifting the kill switch brings it back
        config('Gelita')->paket['aktif'] = true;
        $this->packages->forget();
        $this->assertSame($code, $this->apiData()['package_code']);
    }

    public function testLabOnlinePermissionIsStoredAuditedAndExposed(): void
    {
        $this->assertFalse($this->apiData()['online_lab_allowed'], 'off by default');
        $this->assertStringContainsString('Izinkan mode daring untuk perangkat lab', $this->page('admin/studi/rilis/paket'));

        $this->post('admin/studi/rilis/paket/izin-daring', ['online_lab_allowed' => '1'])->assertRedirectTo(site_url('admin/studi/rilis/paket'));
        $this->assertTrue($this->packages->onlineLabAllowed());
        $this->assertFileExists($this->data . 'pengaturan.json');
        $this->assertTrue($this->apiData()['online_lab_allowed'], 'stored value, also while package mode is off');
        $this->assertTrue($this->lastAudit('package_online_lab')['online_lab_allowed']);
        $this->assertMatchesRegularExpression('/id="online-lab"[^>]*checked/', $this->page('admin/studi/rilis/paket'));

        $this->switchOn();
        $this->assertTrue($this->apiData()['online_lab_allowed']);

        // Unticked checkbox sends only the hidden 0
        $this->post('admin/studi/rilis/paket/izin-daring', ['online_lab_allowed' => '0']);
        $this->assertFalse($this->apiData()['online_lab_allowed']);
        $this->assertFalse($this->lastAudit('package_online_lab')['online_lab_allowed']);
    }

    public function testStaffPreviewIsForStaffOnlyAndNeverChangesTheActivePackage(): void
    {
        $code = $this->buildViaPanel(); // built, not published: package mode stays off
        $url  = $this->packages->map($code)['keys']['bg.welcome'];

        // A student (no staff login) asking for ?paket= gets nothing
        $this->previewRequest($code, ['participant_id' => 7]);
        $this->assertNull($this->packages->previewCode());
        $this->assertNull($this->packages->urlForKey('bg.welcome'));
        $this->assertStringNotContainsString('package-preview"', view('components/package-preview', [], ['saveData' => false]), 'no preview pill for students');

        // Staff without releases.manage (ketua) gets no preview (27 K13)
        $this->injectStaff('ketua');
        $this->previewRequest($code, ['staff_id' => 1, 'staff_role' => 'ketua']);
        $this->assertNull($this->packages->previewCode());
        $this->injectStaff('admin');

        // Staff with releases.manage (admin, 27 K13): the helpers serve the package, the active package stays null
        $this->previewRequest($code, ['staff_id' => 1, 'staff_role' => 'admin']);
        $this->assertSame($code, $this->packages->previewCode());
        $this->assertSame($url, $this->packages->urlForKey('bg.welcome'));
        $this->assertSame(base_url($url), media_key_src('bg.welcome'));
        $this->assertNull($this->packages->activeCode());
        $this->assertSame($code, $_SESSION['package_preview']);

        // The game shows the staff member a preview pill with a way out (owner, 9 November 2026)
        $pill = html_entity_decode(view('components/package-preview', [], ['saveData' => false]), ENT_QUOTES | ENT_HTML5);
        $this->assertStringContainsString('data-package-preview="' . $code . '"', $pill);
        $this->assertStringContainsString('Pratinjau paket ' . $code, $pill);
        $this->assertStringContainsString('?paket=mati">Akhiri pratinjau', $pill);

        // Next page without ?paket=: the preview follows the staff session
        $this->previewRequest(null, $_SESSION);
        $this->assertSame($url, $this->packages->urlForKey('bg.welcome'));

        // /api/paket/aktif never reflects a preview
        $response = $this->withSession($_SESSION)->withHeaders([])->call(Method::GET, 'api/paket/aktif', ['paket' => $code]);
        $this->assertNull(json_decode($response->getJSON(), true)['data']['package_code']);

        // A staff account that was deactivated loses the preview
        $this->injectStaff('admin', 0);
        $this->previewRequest(null, $_SESSION);
        $this->assertNull($this->packages->urlForKey('bg.welcome'));
        $this->injectStaff('admin');

        // ?paket=mati ends it; an unknown code changes nothing
        $this->previewRequest('pkt-2000-01-01-01', $_SESSION);
        $this->assertSame($code, $this->packages->previewCode());
        $this->previewRequest('mati', $_SESSION);
        $this->assertNull($this->packages->previewCode());
        $this->assertArrayNotHasKey('package_preview', $_SESSION);
    }

    public function testResearchReadyVerifyRequiresAPublishedIntactPackage(): void
    {
        $this->assertStringContainsString('Mode paket mati', $this->verifyOutput());

        $code = $this->switchOn();
        $this->assertStringNotContainsString('paket ' . $code, $this->verifyOutput());

        unlink($this->public . $this->packages->manifest($code)['files'][0]['url']);
        $out = $this->verifyOutput();
        $this->assertStringContainsString('paket ' . $code, $out);
        $this->assertStringContainsString('berkas tidak ada', $out);
        $this->assertFalse($this->packages->verification($code)['ok'], 'the command refreshes the cached check');
    }

    // ------------------------------------------------------------ helpers

    /** Drive a build the way the page does (AJAX batches) and return its code. */
    private function buildViaPanel(): string
    {
        $start = $this->ajax('admin/studi/rilis/paket/bangun');
        $code  = $start['package_code'];
        $this->assertFalse($start['finished']);
        $this->assertStringEndsWith('admin/studi/rilis/paket/bangun/' . $code . '/lanjut', $start['step_url']);

        // While building, the page shows the progress driver
        $page = $this->page('admin/studi/rilis/paket');
        $this->assertStringContainsString('data-package-build="' . $code . '"', $page);
        $this->assertStringContainsString('js/admin/package-build.js', $page);

        for ($i = 0, $data = $start; ! $data['finished'] && $i < 50; $i++) {
            $data = $this->ajax('admin/studi/rilis/paket/bangun/' . $code . '/lanjut');
        }

        $this->assertTrue($data['finished']);
        $this->assertSame(100, $data['percent']);
        $this->assertSame(AssetPackageModel::STATUS_BUILT, model(AssetPackageModel::class)->findByCode($code)['status']);
        $this->packages->forget();

        return $code;
    }

    private function switchOn(): string
    {
        $code = $this->buildViaPanel();
        $this->post('admin/studi/rilis/paket/' . $code . '/terbitkan');
        $this->packages->forget();
        $this->assertSame($code, $this->packages->activeCode());

        return $code;
    }

    /** @return array<string, mixed> */
    private function ajax(string $path): array
    {
        $response = $this->asAdmin()->withHeaders(['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'])
            ->call(Method::POST, $path, self::CSRF);
        $response->assertOK();
        $json = json_decode($response->getJSON(), true);
        $this->assertTrue($json['success'], (string) $response->getJSON());

        return $json['data'];
    }

    /** @param array<string, string> $params */
    private function post(string $path, array $params = []): TestResponse
    {
        $response = $this->asAdmin()->withHeaders([])->call(Method::POST, $path, $params + self::CSRF);
        $this->packages->forget();

        return $response;
    }

    private function page(string $path): string
    {
        $this->packages->forget();
        $result = $this->asAdmin()->withHeaders([])->call(Method::GET, $path);
        $result->assertOK();

        return html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5);
    }

    /** @return array<string, mixed> */
    private function apiData(): array
    {
        $this->packages->forget();
        $response = $this->withSession([])->withHeaders([])->call(Method::GET, 'api/paket/aktif');
        $this->packages->forget();

        return json_decode($response->getJSON(), true)['data'];
    }

    /** @return array<string, mixed> metadata of the newest audit row with this action */
    private function lastAudit(string $action): array
    {
        $row = $this->conn->table('audit_logs')->where('action', $action)->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertNotNull($row, $action);

        return json_decode((string) $row['metadata_json'], true);
    }

    /**
     * Simulate a game page request: query string and session as given, then
     * ask the package service fresh.
     *
     * @param array<string, mixed> $session
     */
    private function previewRequest(?string $paket, array $session): void
    {
        $_SESSION = $session;
        $request  = Services::incomingrequest(null, false);
        $request->setGlobal('get', $paket === null ? [] : ['paket' => $paket]);
        Services::injectMock('request', $request);
        $this->packages->forget();
    }

    private function verifyOutput(): string
    {
        $io = new MockInputOutput();
        CLI::setInputOutput($io);

        try {
            (new ContentVerify(Services::logger(), Services::commands()))->run(['siap-penelitian' => null]);
        } finally {
            CLI::resetInputOutput();
        }

        $this->packages->forget();

        return $io->getOutput();
    }

    private function asAdmin(): self
    {
        return $this->withSession(['staff_id' => 1, 'staff_role' => 'admin', 'gelita_csrf' => self::CSRF_HASH]);
    }

    private function injectStaff(?string $role, int $active = 1): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 1, 'username' => 'admin_uji', 'email' => null, 'role' => $role, 'display_name' => 'Admin Uji',
                'is_active' => $active, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
