<?php

use App\Controllers\Admin\StaffGuideController;
use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * Staff guide /admin/panduan-staf (16 November 2026): every page a step
 * links to is a real panel page guarded by the capability the step names,
 * anchors resolve, no secrets, the reader's own chapter first and open, and
 * page links only for pages the reader may open.
 *
 * @internal
 */
final class StaffGuideTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    private const STAFF = ['admin' => 1, 'ketua' => 2, 'peneliti' => 3];

    protected function tearDown(): void
    {
        if (db_connect()->tableExists('participants')) {
            $this->dropResetTables();
        }
        parent::tearDown();
    }

    public function testEveryLinkIsAGuardedPanelPage(): void
    {
        $routes = service('routes');
        $routes->loadRoutes();
        $routes->setHTTPVerb('GET');
        $known = array_map('strval', array_keys($routes->getRoutes('GET')));

        foreach ($this->links() as [$href, $label, $capability]) {
            $this->assertContains($href, $known, "{$label}: {$href} is a GET route");
            $this->assertContains($capability, StaffAccess::capabilities(), $label);

            $guards = array_map(
                static fn (string $filter): array => explode(',', substr($filter, strlen('staffCan:'))),
                array_values(array_filter($routes->getFiltersForRoute($href), static fn (string $f): bool => str_starts_with($f, 'staffCan:'))),
            );

            if ($href !== 'admin/akun/sandi') {
                $this->assertContains($capability, array_merge(...$guards), "{$href} is guarded by {$capability}");
            }
        }

        $this->assertContains('panduan-staf', array_map(static fn (string $p): string => substr($p, 6), $known));
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-staf'), 'public (owner, 16 Nov 2026)');
    }

    public function testAnchorsAreUniqueAndResolve(): void
    {
        $ids  = [];
        $text = '';

        foreach (StaffGuideContent::chapters() as $chapter) {
            $ids[] = $chapter['id'];
            $text .= json_encode($chapter, JSON_UNESCAPED_UNICODE);

            foreach ($chapter['sections'] as $section) {
                $ids[] = $section['id'];
            }

            $this->assertSame([], array_diff($chapter['roles'], StaffAccess::ROLES), $chapter['id']);
        }

        $this->assertSame(count($ids), count(array_unique($ids)), 'unique anchors');
        preg_match_all('/\]\(#([a-z0-9-]+)\)/', $text, $matches);
        $this->assertNotEmpty($matches[1]);

        foreach ($matches[1] as $anchor) {
            $this->assertContains($anchor, $ids, "#{$anchor}");
        }

        // No secrets or real people: no passwords, hosts, addresses, or the English word for kata sandi
        $this->assertDoesNotMatchRegularExpression('/password|https?:|@[a-z]|\b\d{1,3}(\.\d{1,3}){3}\b/i', $text);
    }

    public function testInlineMarkupEscapesEverythingElse(): void
    {
        $this->assertSame(
            '<b>Simpan</b> lalu <code>RESET</code>, lihat <a href="#lembar">L0</a> &lt;script&gt;',
            StaffGuideContent::inline('**Simpan** lalu `RESET`, lihat [L0](#lembar) <script>'),
        );
    }

    public function testOwnChapterComesFirstAndOpen(): void
    {
        $ids = static fn (string $role): array => array_column(StaffGuideController::order(StaffGuideContent::chapters(), $role), 'id');

        $this->assertSame(['mulai', 'lapangan', 'ketua', 'admin', 'larangan'], $ids('peneliti'));
        $this->assertSame(['mulai', 'ketua', 'lapangan', 'admin', 'larangan'], $ids('ketua'));
        $this->assertSame(['mulai', 'admin', 'lapangan', 'ketua', 'larangan'], $ids('admin'));

        $open = array_column(StaffGuideController::order(StaffGuideContent::chapters(), 'peneliti'), 'open', 'id');
        $this->assertSame(['mulai' => true, 'lapangan' => true, 'ketua' => false, 'admin' => false, 'larangan' => true], $open);
    }

    public function testPageRendersPerRole(): void
    {
        $this->createRawDataWorld();

        $peneliti = $this->get('peneliti');
        $this->assertStringContainsString('<details class="guide-chapter" id="lapangan" open>', $peneliti);
        $this->assertStringContainsString('<details class="guide-chapter" id="admin">', $peneliti, 'other roles folded');
        $this->assertStringContainsString('href="' . base_url('admin/sesi-lapangan') . '"', $peneliti);
        $this->assertStringNotContainsString('class="guide-page-link" href="' . base_url('admin/ekspor') . '"', $peneliti, 'no link to a page the role cannot open');
        $this->assertStringContainsString('Halaman Unduh data (bukan untuk peran Anda)', $peneliti);
        $this->assertStringContainsString('aria-current="page"', $peneliti);

        $admin = $this->get('admin');
        $this->assertStringContainsString('<details class="guide-chapter" id="admin" open>', $admin);
        $this->assertStringContainsString('class="guide-page-link" href="' . base_url('admin/staf') . '"', $admin);
        $this->assertStringContainsString(base_url('admin/panduan-staf'), $admin, 'sidebar and account menu');
    }

    public function testGuestReadsItWithoutSigningIn(): void
    {
        $this->createRawDataWorld();
        $_SESSION = [];
        Services::resetSingle('renderer');
        Services::resetSingle('staffContext');

        $guest = $this->call(Method::GET, 'admin/panduan-staf');
        $guest->assertOK();
        $page = (string) $guest->getBody();
        $this->assertStringNotContainsString('admin-sidebar', $page, 'no panel menu');
        $this->assertStringContainsString(lang('Admin.guideLeadGuest'), $page);
        $this->assertStringContainsString('<details class="guide-chapter" id="lapangan">', $page, 'no role picked: role chapters folded');
        $this->assertStringContainsString('<details class="guide-chapter" id="mulai" open>', $page);
        $this->assertStringNotContainsString('class="guide-page-link" href=', $page, 'no panel links for a guest');
        $this->assertStringContainsString(base_url('admin/login'), $page);

        Services::resetSingle('renderer');
        $picked = (string) $this->call(Method::GET, 'admin/panduan-staf?peran=ketua')->getBody();
        $this->assertStringContainsString('<details class="guide-chapter" id="ketua" open>', $picked);
        $this->assertStringContainsString('aria-current="page">Ketua peneliti', $picked);

        Services::resetSingle('renderer');
        $odd = (string) $this->call(Method::GET, 'admin/panduan-staf?peran=root')->getBody();
        $this->assertStringContainsString(lang('Admin.guideLeadGuest'), $odd, 'unknown role ignored');
    }

    public function testLoginPageOffersTheGuideOnTheRight(): void
    {
        $_SESSION = [];
        Services::resetSingle('renderer');

        $page = (string) $this->call(Method::GET, 'admin/login')->getBody();
        $this->assertStringContainsString('auth-shell has-aside', $page);
        $this->assertMatchesRegularExpression('/<\/form>.*class="auth-card auth-guide"/s', $page, 'login card first, guide card second');
        $this->assertStringContainsString(base_url('admin/panduan-staf') . '?peran=peneliti', $page);
    }

    /** @return list<array{0: string, 1: string, 2: string}> */
    private function links(): array
    {
        $links = [];

        foreach (StaffGuideContent::chapters() as $chapter) {
            foreach ($chapter['sections'] as $section) {
                foreach ($section['steps'] ?? [] as $step) {
                    if (is_array($step)) {
                        $links[] = $step[1];
                    }
                }
            }
        }

        $this->assertGreaterThan(20, count($links));

        return $links;
    }

    private function get(string $role): string
    {
        foreach (['security', 'renderer', 'staffContext'] as $name) {
            Services::resetSingle($name);
        }
        $_SESSION = [];

        $response = $this->withSession(['staff_id' => self::STAFF[$role], 'staff_role' => $role, 'staff_name' => ucfirst($role)])
            ->call(Method::GET, 'admin/panduan-staf');
        $response->assertOK();

        return (string) $response->getBody();
    }
}
