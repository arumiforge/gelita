<?php

use App\Controllers\Admin\StaffGuideController;
use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * Staff guide /admin/panduan-staf (16 November 2026; docs layout the same
 * day): every page a step links to is a real panel page guarded by the
 * capability the step names, guide links and glossary terms resolve, no
 * secrets and no server or repository talk, the reader's own chapter first
 * and open, and page links only for pages the reader may open.
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
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-staf/token'), 'every topic page public');
    }

    public function testLinksAndTermsResolve(): void
    {
        $ids  = [];
        $text = '';

        foreach (StaffGuideContent::chapters() as $chapter) {
            $ids[] = $chapter['id'];
            $text .= json_encode($chapter, JSON_UNESCAPED_UNICODE);

            foreach ($chapter['sections'] as $section) {
                $ids[] = $section['id'];
                $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $section['id']);
            }

            $this->assertSame([], array_diff($chapter['roles'], StaffAccess::ROLES), $chapter['id']);
        }

        $this->assertSame(count($ids), count(array_unique($ids)), 'unique ids');
        $this->assertSame([], array_diff(StaffGuideContent::POPULAR, $ids), 'first-page cards');

        preg_match_all('/\]\(#([a-z0-9-]+)\)/', $text, $links);
        $this->assertNotEmpty($links[1]);
        $this->assertSame([], array_diff($links[1], $ids), 'guide links');

        $terms = StaffGuideContent::terms();
        preg_match_all('/\[\[(?:[^\]|]+\|)?([a-z0-9-]+)\]\]/u', $text, $used);
        $this->assertGreaterThan(10, count($used[1]));
        $this->assertSame([], array_diff($used[1], array_keys($terms)), 'every [[term]] has an entry');

        foreach ($terms as $key => $term) {
            $this->assertNotSame('', $term['def'], $key);
            $this->assertNotSame('', $term['example'], $key);
            $this->assertTrue($term['more'] === null || in_array($term['more'], $ids, true), "{$key} → {$term['more']}");
            $text .= json_encode($term, JSON_UNESCAPED_UNICODE);
        }

        // No secrets or real people: no passwords, hosts, addresses, or the English word for kata sandi
        $this->assertDoesNotMatchRegularExpression('/password|https?:|@[a-z]|\b\d{1,3}(\.\d{1,3}){3}\b/i', $text);
        // Staff-facing only (owner, 16 Nov 2026): no server, deployment, code, or repository documents
        $this->assertDoesNotMatchRegularExpression('/server|deploy|migrasi|composer|spark|gelita:|php|\.md\b|docs\/|baris perintah/i', $text);
    }

    public function testInlineMarkupEscapesEverythingElse(): void
    {
        $this->assertSame(
            '<b>Simpan</b> lalu <code>RESET</code>, lihat <a href="' . base_url('admin/panduan-staf/lembar') . '?peran=ketua">L0</a> &lt;script&gt;',
            StaffGuideContent::inline('**Simpan** lalu `RESET`, lihat [L0](#lembar) <script>', '?peran=ketua'),
        );
        $this->assertSame(
            // A term with a mark right after it stays on one line with the mark (owner, 18 Nov 2026)
            'buka <button type="button" class="term" popovertarget="istilah-pop-token">token baru</button> dan <span class="term-glue"><button type="button" class="term" popovertarget="istilah-pop-rombel">Rombel</button>,</span> tidak-ada',
            StaffGuideContent::inline('buka [[token baru|token]] dan [[rombel]], [[tidak-ada]]'),
        );
        $this->assertStringNotContainsString('<b>x', StaffGuideContent::inline('[[<b>x</b>|token]]'), 'term text escaped');
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

    public function testPagesRenderPerRole(): void
    {
        $this->createRawDataWorld();

        $home = $this->get('peneliti');
        $this->assertStringContainsString(lang('Admin.guideLead', ['Peneliti']), $home);
        $this->assertStringContainsString('<details class="gd-group" data-chapter="lapangan" open>', $home);
        $this->assertStringContainsString('<details class="gd-group" data-chapter="admin">', $home, 'other roles folded');
        $this->assertStringContainsString('class="gd-card is-mine" href="' . base_url('admin/panduan-staf/ringkas') . '"', $home);
        $this->assertStringContainsString(lang('Admin.guideBackToPanel'), $home);
        $this->assertStringNotContainsString('admin-sidebar', $home, 'the guide has its own menu');

        $steps = $this->get('peneliti', 'sebelum');
        $this->assertStringContainsString('<h1 class="gd-title" id="gd-title">Sebelum kunjungan</h1>', $steps);
        $this->assertStringContainsString('href="' . base_url('admin/sesi-lapangan') . '"', $steps);
        $this->assertStringContainsString('href="' . base_url('admin/panduan-staf/sebelum') . '" data-search="', $steps);
        $this->assertMatchesRegularExpression('/data-search="[^"]*sebelum kunjungan|data-search="[^"]*buat sesi lapangan/', $steps);
        $this->assertStringContainsString('rel="prev"', $steps);
        $this->assertStringContainsString('rel="next"', $steps);

        // Glossary: the term in the text, its popover with example and detail link
        $this->assertStringContainsString('<button type="button" class="term" popovertarget="istilah-pop-mode-penelitian">Mode penelitian</button>', $steps);
        $this->assertStringContainsString('<div class="term-pop" id="istilah-pop-mode-penelitian" popover role="dialog"', $steps);
        $this->assertStringContainsString('href="' . base_url('admin/panduan-staf/mode-penelitian') . '">Selengkapnya: Yang dialami siswa dalam mode penelitian', $steps);
        $this->assertStringNotContainsString('istilah-pop-kunci-ambang', $steps, 'only the terms of this page');

        $download = $this->get('peneliti', 'unduh');
        $this->assertStringNotContainsString('class="guide-page-link" href="' . base_url('admin/ekspor') . '"', $download, 'no link to a page the role cannot open');
        $this->assertStringContainsString('Halaman Unduh data (bukan untuk peran Anda)', $download);

        $glossary = $this->get('peneliti', 'istilah');
        $this->assertStringContainsString('<div class="gd-term" id="istilah-kode-peserta">', $glossary);
        $this->assertSame(count(StaffGuideContent::terms()), substr_count($glossary, 'class="gd-term"'));

        $admin = $this->get('admin', 'akun-staf');
        $this->assertStringContainsString('class="guide-page-link" href="' . base_url('admin/staf') . '"', $admin);
        $this->assertStringContainsString('<details class="gd-group" data-chapter="admin" open>', $admin);
    }

    public function testUnknownTopicIsNotFound(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->call(Method::GET, 'admin/panduan-staf/tidak-ada');
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
        $this->assertStringContainsString('<details class="gd-group" data-chapter="lapangan">', $page, 'no role picked: role chapters folded');
        $this->assertStringContainsString('<details class="gd-group" data-chapter="mulai" open>', $page);
        $this->assertStringContainsString(base_url('admin/login'), $page);

        Services::resetSingle('renderer');
        $topic = (string) $this->call(Method::GET, 'admin/panduan-staf/sebelum')->getBody();
        $this->assertStringNotContainsString('class="guide-page-link" href=', $topic, 'no panel links for a guest');
        $this->assertStringContainsString('Di panel: Sesi lapangan', $topic);

        Services::resetSingle('renderer');
        $picked = (string) $this->call(Method::GET, 'admin/panduan-staf/token?peran=ketua')->getBody();
        $this->assertStringContainsString('<details class="gd-group" data-chapter="ketua" open>', $picked);
        $this->assertStringContainsString('aria-current="page">Ketua peneliti', $picked);
        $this->assertStringContainsString('href="' . base_url('admin/panduan-staf/izin') . '?peran=ketua" rel="next"', $picked, 'the pick travels with the links');

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

    private function get(string $role, string $topic = ''): string
    {
        foreach (['security', 'renderer', 'staffContext'] as $name) {
            Services::resetSingle($name);
        }
        $_SESSION = [];

        $response = $this->withSession(['staff_id' => self::STAFF[$role], 'staff_role' => $role, 'staff_name' => ucfirst($role)])
            ->call(Method::GET, 'admin/panduan-staf' . ($topic === '' ? '' : '/' . $topic));
        $response->assertOK();

        return (string) $response->getBody();
    }
}
