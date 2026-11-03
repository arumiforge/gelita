<?php

use App\Libraries\PasswordPolicy;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * What each staff role sees in the panel (revision 10, part 1:
 * docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md §9, §10, §14.1
 * `ResearcherNeverSeesIdentityTest`, `AdminHasNoResearchDataTest`,
 * `ResetByCodeTest`, `StudyAccessTest`).
 *
 * RawDataWorld: Ani Lestari (`ani.5`, GLT-000001, SD Negeri Alpha, NPSN
 * 11111111) and Budi Santoso (`budi.5`, GLT-000002, SD Negeri Beta).
 *
 * @internal
 */
final class ResearcherPanelTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';
    private const CSRF      = ['gelita_csrf' => self::CSRF_HASH . '00000000000000000000000000000000'];

    /** Identity strings no researcher page may show. */
    private const IDENTITY = ['Ani Lestari', 'ani.5', 'Budi Santoso', 'budi.5', 'SD Negeri Alpha', 'SD Negeri Beta', '11111111', '22222222'];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRawDataWorld();
        Services::resetSingle('contentRepository');
        Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropResetTables();

        parent::tearDown();
    }

    public function testResearchersNeverSeeIdentity(): void
    {
        foreach (['peneliti', 'ketua'] as $role) {
            foreach (['admin', 'admin/peserta', 'admin/peserta/1', 'admin/peserta/1?tab=sesi', 'admin/peserta/1?tab=jawaban'] as $path) {
                $this->assertNoIdentity($this->page($role, $path), "{$role} {$path}");
            }

            $csv = $this->page($role, 'admin/peserta', ['format' => 'csv']);
            $this->assertStringContainsString('GLT-000001', $csv, $role);
            $this->assertNoIdentity($csv, "{$role} CSV");
        }

        foreach (['admin/sesi', 'admin/sesi/1', 'admin/masukan', 'admin/data/perangkat', 'admin/data/jawaban', 'admin/ekspor'] as $path) {
            $this->assertNoIdentity($this->page('ketua', $path), "ketua {$path}");
        }

        $show = $this->page('peneliti', 'admin/peserta/1');
        $this->assertStringContainsString('SCH-000001', $show, 'the school as its code');
        $this->assertStringContainsString('<title>GLT-000001', $show, 'the page title is the code');
        $this->assertStringNotContainsString('admin/ekspor/xlsx', $show, 'peneliti cannot download');
        $this->assertStringContainsString('admin/peserta/reset-sandi', $show);
    }

    public function testAdminSeesAccountsButNoResearchData(): void
    {
        foreach (['admin/sesi', 'admin/sesi/1', 'admin/data/jawaban', 'admin/masukan', 'admin/ekspor', 'admin/peserta/reset-sandi'] as $path) {
            $this->assertNotFound('admin', $path);
        }

        $show = $this->page('admin', 'admin/peserta/1', ['tab' => 'jawaban']);
        $this->assertStringContainsString('Ani Lestari', $show, 'the account card');
        $this->assertStringContainsString('ani.5', $show);
        $this->assertStringNotContainsString('?tab=jawaban', $show, 'no research tabs');
        $this->assertStringNotContainsString('Syarat sandi dipenuhi', $show, 'password metrics are research data');
        $this->assertStringNotContainsString('admin/ekspor', $show, 'no downloads');
        $this->assertStringContainsString('id="reset-sandi"', $show, 'reset by id stays');

        $dashboard = $this->page('admin', 'admin');
        $this->assertStringNotContainsString('GLT-000001', $dashboard, 'no per-student lists for the admin');
        $this->assertStringNotContainsString('Aktivitas terbaru', $dashboard);
    }

    public function testApiAnswers403WithoutTheRight(): void
    {
        foreach (['admin', 'peneliti'] as $role) {
            $result = $this->asStaff($role)->call(Method::GET, 'api/admin/sessions/1/timeline');
            $this->assertSame(403, $result->response()->getStatusCode(), $role);
        }

        $this->asStaff('ketua')->call(Method::GET, 'api/admin/sessions/1/timeline')->assertOK();
        $this->asStaff('peneliti')->call(Method::GET, 'api/admin/summary')->assertOK();
    }

    public function testResetByCodeGivesATemporaryPasswordWithoutAName(): void
    {
        $result = $this->post('peneliti', 'admin/peserta/reset-sandi', ['participant_code' => ' glt-000001 ', 'confirm' => 'RESET']);
        $result->assertOK();
        $html = (string) $result->getBody();

        $this->assertStringContainsString('no-store', $result->response()->getHeaderLine('Cache-Control'));
        $this->assertStringContainsString('GLT-000001', $html);
        $this->assertNoIdentity($html, 'reset result');
        $this->assertSame(1, preg_match('/id="temp-password">([^<]+)</', $html, $match));
        $this->assertTrue((new PasswordPolicy())->check($match[1], 'ani.5')['acceptable']);

        $participant = db_connect()->table('participants')->where('id', 1)->get()->getRowArray();
        $this->assertSame(1, (int) $participant['must_change_password']);
        $this->assertTrue(password_verify($match[1], $participant['password_hash']));

        $audit = db_connect()->table('audit_logs')->where('action', 'participant_password_reset')->get()->getRowArray();
        $this->assertSame(['participant_code' => 'GLT-000001', 'via' => 'kode'], json_decode((string) $audit['metadata_json'], true));
    }

    public function testResetByCodeRefusesUnknownCodesAndMissingConfirmation(): void
    {
        $before = db_connect()->table('participants')->where('id', 1)->get()->getRow('password_hash');

        $unknown = $this->post('ketua', 'admin/peserta/reset-sandi', ['participant_code' => 'GLT-999999', 'confirm' => 'RESET']);
        $unknown->assertRedirectTo(site_url('admin/peserta/reset-sandi'));
        $unknown->assertSessionHas('error', lang('Admin.resetCodeNotFound'));
        $this->post('ketua', 'admin/peserta/reset-sandi', ['participant_code' => 'GLT-000001'])
            ->assertRedirectTo(site_url('admin/peserta/reset-sandi'));

        $this->assertSame($before, db_connect()->table('participants')->where('id', 1)->get()->getRow('password_hash'));
        $this->assertSame(0, db_connect()->table('audit_logs')->where('action', 'participant_password_reset')->countAllResults());

        $this->expectException(PageNotFoundException::class);
        $this->post('admin', 'admin/peserta/reset-sandi', ['participant_code' => 'GLT-000001', 'confirm' => 'RESET']);
    }

    public function testKetuaChangesStudiesAdminOnlyLooks(): void
    {
        $study = db_connect()->table('research_studies')->get()->getRowArray();
        $form  = ['code' => $study['code'], 'name' => 'Nama baru', 'status' => 'draft', 'default_locale' => 'id', 'unlock_mode' => 'sequential', 'retention_days' => ''];

        $locked = $this->page('admin', 'admin/studi');
        $this->assertStringContainsString('class="form-readonly" disabled', $locked);
        $this->assertStringNotContainsString('Buat penelitian baru', $locked);
        $this->assertStringNotContainsString('Simpan penelitian', $locked);

        $this->assertNotFound('peneliti', 'admin/studi');

        $this->post('ketua', 'admin/studi/' . $study['id'], $form)->assertRedirectTo(site_url('admin/studi'));
        $this->assertSame('Nama baru', db_connect()->table('research_studies')->get()->getRow('name'));

        $this->expectException(PageNotFoundException::class);
        $this->post('admin', 'admin/studi/' . $study['id'], ['name' => 'Admin'] + $form);
    }

    public function testMenuFollowsTheRole(): void
    {
        $ketua = $this->page('ketua', 'admin');
        $this->assertStringContainsString('href="' . base_url('admin/ekspor') . '"', $ketua);
        $this->assertStringContainsString('Ketua peneliti', $ketua, 'role label in the header');
        $this->assertStringNotContainsString('href="' . base_url('admin/staf') . '"', $ketua);

        $admin = $this->page('admin', 'admin');
        $this->assertStringContainsString('href="' . base_url('admin/staf') . '"', $admin);
        $this->assertStringNotContainsString('href="' . base_url('admin/ekspor') . '"', $admin);
        $this->assertStringNotContainsString('href="' . base_url('admin/sesi') . '"', $admin);
    }

    // ---------------------------------------------------------------- helpers

    private function assertNoIdentity(string $html, string $where): void
    {
        foreach (self::IDENTITY as $identity) {
            $this->assertStringNotContainsString($identity, $html, "{$where} shows {$identity}");
        }
    }

    private function assertNotFound(string $role, string $path): void
    {
        try {
            $this->asStaff($role)->call(Method::GET, $path);
            $this->fail("{$role} {$path} must be 404");
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);
        }
    }

    /** @param array<string, string> $query */
    private function page(string $role, string $path, array $query = []): string
    {
        Services::resetSingle('rawDataService');

        if (str_contains($path, '?')) {
            [$path, $string] = explode('?', $path, 2);
            parse_str($string, $more);
            $query += $more;
        }

        return (string) $this->asStaff($role)->call(Method::GET, $path, $query)->getBody();
    }

    /** @param array<string, string> $params */
    private function post(string $role, string $path, array $params)
    {
        return $this->asStaff($role)->withHeaders([])->call(Method::POST, $path, $params + self::CSRF);
    }

    private function asStaff(string $role): self
    {
        $this->injectStaff($role);
        // The shared renderer keeps view data (breadcrumb) from the previous request
        Services::resetSingle('renderer');

        return $this->withSession(['staff_id' => 7, 'staff_role' => $role, 'staff_name' => 'Uji', 'gelita_csrf' => self::CSRF_HASH]);
    }

    /** null removes the injected staff so later tests read the database again. */
    private function injectStaff(?string $role): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 7, 'username' => 'staf_uji', 'email' => null, 'role' => $role, 'display_name' => 'Staf Uji',
                'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
