<?php

use App\Libraries\ResearchReadiness;
use App\Models\GameReleaseModel;
use App\Models\ResearchStudyModel;
use App\Services\AssetPackageBuilder;
use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\PackageWorld;

/**
 * One package per study (docs/rencana-paket-aset.md §7.2, §11.3
 * `ReleasePackageLockTest`, owner decision P10): a study locks the active
 * release's package when it becomes *berjalan*; while it runs, activating a
 * release with another package on Rilis is refused without a confirmation
 * and a reason, and the audit entry carries `package_from`, `package_to`,
 * and `reason`; the research-ready checklist requires a published, verified
 * package.
 *
 * @internal
 */
final class ReleasePackageLockTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use PackageWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const CSRF      = ['gelita_csrf' => self::CSRF_HASH . '00000000000000000000000000000000'];

    /** Signed-in role: releases are the admin's, studies the head researcher's (27 K12). */
    private string $role = 'admin';

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content', 'ui']);
        $this->createPackageWorld();
        config('Gelita')->paket['copyLibraryImages'] = false;
        $this->injectStaff();
        Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(false);
        config('Gelita')->paket['copyLibraryImages'] = true;
        $this->dropPackageWorld();
        parent::tearDown();
    }

    public function testStudyLocksThePackageWhenItStartsRunning(): void
    {
        $first = $this->build();
        $this->useRelease($first);

        // Studies are changed by ketua (studies.manage)
        $this->role = 'ketua';
        $this->injectStaff();

        // Draft: nothing locked yet
        $this->post('admin/studi', $this->study('draft-a', 'draft'));
        $draft = $this->studyRow('draft-a');
        $this->assertNull($draft['package_code']);

        // Draft → berjalan: locked to the active release's package
        $this->post('admin/studi/' . $draft['id'], $this->study('draft-a', 'active'));
        $this->assertSame($first, $this->studyRow('draft-a')['package_code']);
        $this->assertSame($first, $this->lastAudit('study_update')['package_code']);

        // Saving a running study again keeps the lock, even after the package changed
        $second = $this->build();
        $this->useRelease($second);
        $this->post('admin/studi/' . $draft['id'], $this->study('draft-a', 'active') + ['name' => 'Nama baru']);
        $this->assertSame($first, $this->studyRow('draft-a')['package_code']);

        // The Penelitian page shows the lock and that students now get another package
        $page = $this->page('admin/studi');
        $this->assertStringContainsString('Paket aset terkunci', $page);
        $this->assertStringContainsString('<code>' . $first . '</code>', $page);
        $this->assertStringContainsString('Siswa sekarang memakai ' . $second, $page);

        // Created as berjalan with package mode off: locked to "no package"
        $this->useRelease('1');
        $this->post('admin/studi', $this->study('langsung', 'active'));
        $this->assertNotNull($this->studyRow('langsung'));
        $this->assertNull($this->studyRow('langsung')['package_code']);
    }

    public function testAnotherPackageDuringARunningStudyIsRefusedWithoutConfirmation(): void
    {
        $code    = $this->build();
        $old     = model(GameReleaseModel::class)->activeOrNull();
        $release = $this->release('paket-baru', $code);
        $this->conn->table('research_studies')->insert(['code' => 'studi-uji', 'name' => 'Studi Uji', 'status' => 'active']);

        // The release list links to the confirmation page instead of activating at once
        $this->assertStringContainsString('admin/studi/rilis/' . $release . '/aktifkan">', $this->page('admin/studi/rilis'));

        // Plain activation: refused, sent to the confirmation page
        $this->post('admin/studi/rilis/' . $release . '/aktifkan')->assertRedirectTo(site_url('admin/studi/rilis/' . $release . '/aktifkan'));
        $this->assertSame((int) $old['id'], (int) model(GameReleaseModel::class)->activeOrNull()['id']);
        $this->assertSame(0, $this->conn->table('audit_logs')->where('action', 'release_activate')->countAllResults());

        $page = $this->page('admin/studi/rilis/' . $release . '/aktifkan');
        $this->assertStringContainsString('Studi Uji', $page);
        $this->assertStringContainsString($code, $page);
        $this->assertStringContainsString('tanpa paket (mode paket mati)', $page);
        $this->assertStringContainsString('name="confirm_running_study"', $page);
        $this->assertStringContainsString('name="reason"', $page);

        // Tick but no reason, or reason but no tick: still refused
        $this->post('admin/studi/rilis/' . $release . '/aktifkan', ['confirm_running_study' => '1']);
        $this->post('admin/studi/rilis/' . $release . '/aktifkan', ['reason' => 'Perbaikan gambar']);
        $this->assertSame((int) $old['id'], (int) model(GameReleaseModel::class)->activeOrNull()['id']);

        $this->post('admin/studi/rilis/' . $release . '/aktifkan', ['confirm_running_study' => '1', 'reason' => 'Perbaikan gambar'])
            ->assertRedirectTo(site_url('admin/studi/rilis'));
        $this->assertSame($release, (int) model(GameReleaseModel::class)->activeOrNull()['id']);
        $this->packages->forget();
        $this->assertSame($code, $this->packages->activeCode());

        $audit = $this->lastAudit('release_activate');
        $this->assertNull($audit['package_from']);
        $this->assertSame($code, $audit['package_to']);
        $this->assertSame('Perbaikan gambar', $audit['reason']);
        $this->assertSame('studi-uji', $audit['running_study']);
        $this->assertSame('paket-baru', $audit['release_code']);

        // Going back to the release without a package is a change as well
        $this->post('admin/studi/rilis/' . $old['id'] . '/aktifkan')->assertRedirectTo(site_url('admin/studi/rilis/' . $old['id'] . '/aktifkan'));
        $this->assertSame($release, (int) model(GameReleaseModel::class)->activeOrNull()['id']);
    }

    public function testSamePackageOrNoRunningStudyNeedsNoConfirmation(): void
    {
        $code = $this->build();

        // No running study: activates at once, audited without a reason
        $first = $this->release('mit-paket', $code);
        $this->post('admin/studi/rilis/' . $first . '/aktifkan')->assertRedirectTo(site_url('admin/studi/rilis'));
        $this->assertSame($first, (int) model(GameReleaseModel::class)->activeOrNull()['id']);
        $audit = $this->lastAudit('release_activate');
        $this->assertSame($code, $audit['package_to']);
        $this->assertNull($audit['reason']);
        $this->assertNull($audit['running_study']);

        // Running study, same package (e.g. a new content version): no confirmation needed
        $this->conn->table('research_studies')->insert(['code' => 'studi-uji', 'name' => 'Studi Uji', 'status' => 'active']);
        $second = $this->release('konten-2', $code, 'kelas5-2');
        $this->post('admin/studi/rilis/' . $second . '/aktifkan')->assertRedirectTo(site_url('admin/studi/rilis'));
        $this->assertSame($second, (int) model(GameReleaseModel::class)->activeOrNull()['id']);
        $this->assertSame($code, $this->lastAudit('release_activate')['package_from']);

        // The confirmation page sends a release without a package change straight back
        $this->assertSame(302, $this->asAdmin()->withHeaders([])->call(Method::GET, 'admin/studi/rilis/' . $first . '/aktifkan')->response()->getStatusCode());
    }

    public function testResearchReadinessRequiresAPublishedVerifiedPackage(): void
    {
        $item = $this->readinessItem();
        $this->assertFalse($item['ok']);
        $this->assertStringContainsString('Mode paket masih mati', $item['detail']);
        $this->assertSame('admin/studi/rilis/paket', $item['link']);

        $code = $this->build();
        $this->useRelease($code);
        $item = $this->readinessItem();
        $this->assertTrue($item['ok'], $item['detail']);
        $this->assertStringContainsString($code, $item['detail']);

        // A damaged package fails once checked again (the panel's Periksa button)
        unlink($this->public . $this->packages->manifest($code)['files'][0]['url']);
        $this->post('admin/studi/rilis/paket/periksa')->assertRedirectTo(site_url('admin/studi/rilis/paket'));
        $this->assertFalse($this->readinessItem()['ok']);

        // Server kill switch: the checklist says so
        config('Gelita')->paket['aktif'] = false;
        $this->packages->forget();
        $this->assertStringContainsString('dimatikan dari server', $this->readinessItem()['detail']);
        config('Gelita')->paket['aktif'] = true;

        // Shown on the Penelitian page
        $this->assertStringContainsString('Paket terbit, lengkap, dan terverifikasi', $this->page('admin/studi'));
    }

    // ------------------------------------------------------------ helpers

    private function build(): string
    {
        $summary = (new AssetPackageBuilder($this->packages))->build(['copy_library' => false]);
        $this->packages->forget();

        return $summary['package_code'];
    }

    private function release(string $code, string $assetVersion, string $content = 'kelas5-1'): int
    {
        $this->conn->table('game_releases')->insert([
            'release_code' => $code, 'app_version' => '2.0.0', 'content_version' => $content,
            'asset_version' => $assetVersion, 'scoring_version' => 'bintang-1', 'is_active' => 0,
        ]);

        return (int) $this->conn->insertID();
    }

    /** @return array<string, string> */
    private function study(string $code, string $status): array
    {
        return [
            'code' => $code, 'name' => 'Studi ' . $code, 'status' => $status, 'default_locale' => 'id',
            'unlock_mode' => 'sequential', 'retention_days' => '365', 'confirm_not_ready' => '1',
        ];
    }

    /** @return array<string, mixed>|null */
    private function studyRow(string $code): ?array
    {
        return model(ResearchStudyModel::class)->where('code', $code)->first();
    }

    /** @return array{key: string, label: string, ok: bool, detail: string, link: string} */
    private function readinessItem(): array
    {
        $this->packages->forget();
        $items = array_column((new ResearchReadiness())->check()['items'], null, 'key');

        return $items['package'];
    }

    /** @return array<string, mixed> */
    private function lastAudit(string $action): array
    {
        $row = $this->conn->table('audit_logs')->where('action', $action)->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertNotNull($row, $action);

        return json_decode((string) $row['metadata_json'], true);
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

    private function asAdmin(): self
    {
        return $this->withSession(['staff_id' => 1, 'staff_role' => $this->role, 'gelita_csrf' => self::CSRF_HASH]);
    }

    private function injectStaff(bool $on = true): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if (! $on) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 1, 'username' => 'staf_uji', 'email' => null, 'role' => $this->role, 'display_name' => 'Staf Uji',
                'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
