<?php

use App\Commands\GuideCheck;
use App\Libraries\GelitaGuideContent as Guide;
use App\Libraries\GuideSourceCheck;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * GELITA guide /admin/panduan-gelita (owner, 17 November 2026): public and
 * in full detail, every page with its reasons and references, references
 * only to works the repository's documents cite, links and terms resolve,
 * nothing secret or personal, and every page still matches the document
 * sections it was written from (GuideSourceCheck; the fix when it fails is
 * printed in the message).
 *
 * @internal
 */
final class GelitaGuideTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        if (db_connect()->tableExists('participants')) {
            $this->dropResetTables();
        }
        parent::tearDown();
    }

    public function testGuideMatchesItsSourceDocuments(): void
    {
        $check = GuideSourceCheck::forGelitaGuide();
        $this->assertTrue($check->docsPresent());
        $this->assertSame('docs/', $check->docsDir());

        $problems = $check->problems(Guide::sources());

        $this->assertSame([], $problems, "The documents behind these GELITA-guide pages changed:\n"
            . implode("\n", array_map(static fn (string $ref, string $state): string => "  {$state}: docs/{$ref}", array_keys($problems), $problems))
            . "\nCheck the pages listed by `php spark gelita:panduan:cek --panduan=gelita` against the changed sections, update "
            . "app/Libraries/GelitaGuideContent.php where needed, then run `php spark gelita:panduan:cek --panduan=gelita --tandai` and commit the lock file.");

        $this->assertSame([], array_diff(array_keys($check->locked()), Guide::sources()), 'no stale lock entries');

        // The command names the page behind a changed source
        $ref = 'revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md#tujuan';
        $this->assertSame(['tujuan' => [$ref => GuideSourceCheck::CHANGED]], GuideCheck::pagesFor([$ref => GuideSourceCheck::CHANGED], Guide::class));
    }

    public function testMarkingNeedsOneGuide(): void
    {
        // Coordinator review, 17 Nov 2026: --tandai without --panduan would mark
        // the other guide's unread pages as checked; it is refused, nothing written
        $locks  = [ROOTPATH . GuideSourceCheck::VALIDATOR_LOCK, ROOTPATH . GuideSourceCheck::GELITA_LOCK];
        $before = array_map('sha1_file', $locks);

        command('gelita:panduan:cek --tandai');
        $this->assertStringContainsString('--panduan=validator atau --panduan=gelita', $this->getStreamFilterBuffer());
        $this->assertSame($before, array_map('sha1_file', $locks));

        $this->resetStreamFilterBuffer();
        command('gelita:panduan:cek --panduan=gelita');
        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('Panduan Mengenal GELITA sesuai', $output);
        $this->assertStringNotContainsString('Panduan validator', $output);
    }

    public function testStructureLinksTermsAndReasons(): void
    {
        $ids   = [];
        $text  = '';
        $terms = Guide::terms();

        foreach (Guide::chapters() as $chapter) {
            $ids[] = $chapter['id'];
            $this->assertNotEmpty($chapter['lead'], $chapter['id']);

            foreach ($chapter['sections'] as $section) {
                $ids[] = $section['id'];
                $text .= json_encode($section, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $this->assertMatchesRegularExpression('/^[a-z0-9-]+$/', $section['id']);

                if (! empty($section['glossary'])) {
                    continue;
                }

                // Every page explains why and cites its works (owner, 17 Nov 2026);
                // repository documents are no longer shown (owner, 18 Nov 2026)
                $this->assertNotEmpty($section['why'] ?? [], $section['id'] . ' gives its reasons');
                $this->assertNotEmpty($section['sources'] ?? [], $section['id'] . ' names its sources');
                $this->assertNotEmpty($section['refs'] ?? [], $section['id'] . ' gives references');
                $this->assertArrayNotHasKey('docs', $section, $section['id'] . ' shows no repository documents');
            }
        }

        $this->assertSame(count($ids), count(array_unique($ids)), 'unique ids');

        preg_match_all('/\]\(#([a-z0-9-]+)\)/', $text, $links);
        $this->assertSame([], array_diff($links[1], $ids), 'guide links resolve');

        // Outside links: the asset catalogue only; site links: the other two guides only
        preg_match_all('/\]\((https:[^)]+)\)/', $text, $outside);
        foreach ($outside[1] as $url) {
            $this->assertStringStartsWith(Guide::CATALOGUE, $url, 'outside links go to the asset catalogue only');
        }
        $this->assertContains(Guide::CATALOGUE . '#musik', $outside[1], 'music is explained in the catalogue, not here');
        preg_match_all('/\]\((\/[^)]*)\)/', $text, $site);
        $this->assertSame([], array_diff(array_unique($site[1]), ['/admin/panduan-staf', '/admin/panduan-validator']));

        preg_match_all('/\[\[([^\]]*)\]\]/u', $text, $raw);
        foreach ($raw[1] as $mark) {
            $key = str_contains($mark, '|') ? substr($mark, strpos($mark, '|') + 1) : $mark;
            $this->assertArrayHasKey($key, $terms, "[[{$mark}]]");
        }

        foreach ($terms as $key => $term) {
            $this->assertTrue($term['more'] === null || in_array($term['more'], $ids, true), $key);
        }

        $this->assertSame([], array_diff(Guide::POPULAR, $ids));

        foreach ($terms as $key => $term) {
            // A popover holds no other popover term (it would open nothing)
            $this->assertStringNotContainsString('[[', $term['def'] . $term['example'], $key);
            if (isset($term['catalogue'])) {
                $this->assertMatchesRegularExpression('~^(gambar(/[a-z0-9._-]+)?|musik|efek|narasi|tantangan/(puzzle|rumpang|boleh|cari|skenario)|kelompok/[a-z]+)$~', $term['catalogue'], $key);
            }
        }
        $this->assertDoesNotMatchRegularExpression('/(?<!\*)\*[^*\s][^*]*\*(?!\*)/u', $text, 'single-star italics do not render');
    }

    public function testReferencesAreWorksTheRepositoryDocumentsCite(): void
    {
        // Never invent a citation: the first author and the year of every
        // reference appear in the research documents that cite them
        $corpus = '';
        foreach ([
            'revisi-10-analitik/dokumen-pendukung-peneliti/model-penelitian-pengembangan.html',
            'revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md',
            'revisi-10-analitik/26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md',
        ] as $doc) {
            $corpus .= html_entity_decode((string) file_get_contents(ROOTPATH . 'docs/' . $doc));
        }

        $refs = [];
        foreach (Guide::chapters() as $chapter) {
            foreach ($chapter['sections'] as $section) {
                array_push($refs, ...($section['refs'] ?? []));
            }
        }
        $this->assertGreaterThan(40, count(array_unique($refs)));

        // The reference page lists every work cited on any other page
        $all = Guide::sectionsById(Guide::chapters())['rujukan']['refs'];
        $this->assertSame([], array_diff(array_unique($refs), $all), 'every cited work is on the reference page');

        foreach (array_unique($refs) as $ref) {
            preg_match('/^([\p{L}-]+)/u', $ref, $author);
            preg_match('/\b(1[89]\d\d|20\d\d)\b/', $ref, $year);
            $this->assertStringContainsString($author[1], $corpus, $ref);
            $this->assertStringContainsString($year[1], $corpus, $ref);
        }
    }

    public function testWrittenForReadersNotDevelopers(): void
    {
        // Owner, 18 Nov 2026: no repository documents or file names, no
        // developer terms, no dates on the journey page, and every code the
        // text keeps opens a popover
        $text  = json_encode(array_map(static fn (array $s): array => array_diff_key($s, ['sources' => 0]), Guide::sectionsById(Guide::chapters())), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            . json_encode(Guide::terms(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $plain = (string) preg_replace('/\[\[(?:[^\]|]+\|)?[a-z0-9-]+\]\]/u', '', $text);

        $this->assertDoesNotMatchRegularExpression('~docs/|\.md\b|\.php\b|README|PROGRESS|repositori~iu', $text, 'no repository documents');
        $this->assertDoesNotMatchRegularExpression('~CodeIgniter|bundler|\bcommit|\bmigrasi|\bbranch\b|cabang pendek|gelita:|PHPUnit|ES module|\bCDN\b|Bootstrap|user agent|\bdp\b|\brem\b|`~u', $text, 'no developer terms or code marks');

        $journey = Guide::sectionsById(Guide::chapters())['perjalanan'];
        $this->assertDoesNotMatchRegularExpression('/\b(19|20)\d\d\b|Oktober|November/u', json_encode($journey['table'], JSON_UNESCAPED_UNICODE), 'the journey shows no dates');

        foreach (['teknologi', 'server-kebenaran', 'tanpa-bundler', 'peta-dokumen', 'dokumen-revisi'] as $gone) {
            $this->assertArrayNotHasKey($gone, Guide::sectionsById(Guide::chapters()) + array_column(Guide::chapters(), null, 'id'), $gone);
        }

        // Every code in the text opens a popover where it first appears: a code
        // may stand plain only on a page that already uses its term
        $codes = '/(?<![\\p{L}\\d-])(D[123][ab]?|L[0-8]|P[1-5]|C[1-6]|SK[1-4]|KG\\d[ab]?|G[1-5]|B[1-4]|(?:INF|DIG|BUD|R|NON)-[A-Z]+)(?![\\p{L}\\d])/u';
        $mark  = '/\\[\\[(?:[^\\]|]+\\|)?([a-z0-9-]+)\\]\\]/u';
        $loose = [];
        foreach (Guide::sectionsById(Guide::chapters()) as $id => $section) {
            $body = (string) json_encode(array_diff_key($section, ['sources' => 0, 'refs' => 0, 'id' => 0, 'chapter' => 0]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            preg_match_all($mark, $body, $used);
            preg_match_all($codes, (string) preg_replace($mark, '', $body), $m);
            foreach (array_unique($m[1]) as $code) {
                if (! in_array(self::termForCode($code), $used[1], true)) {
                    $loose[] = "{$id}: {$code}";
                }
            }
        }
        $this->assertNotSame('', $plain);
        $this->assertSame([], $loose, 'codes without a popover on their page');

        foreach (['d1', 'd2', 'd3', 'l3', 'pertemuan', 'gerbang', 'buklet', 'kegiatan-anak', 'aspek-sikap', 'bloom', 'inf-sum', 'kode-r'] as $key) {
            $this->assertArrayHasKey($key, Guide::terms(), $key);
        }
    }

    /** The glossary term that explains a code. */
    private static function termForCode(string $code): string
    {
        $key = strtolower($code);

        return match (true) {
            str_starts_with($code, 'D')  => substr($key, 0, 2),
            str_starts_with($code, 'L')  => $key,
            str_starts_with($code, 'P')  => 'pertemuan',
            str_starts_with($code, 'C')  => 'bloom',
            str_starts_with($code, 'SK') => 'aspek-sikap',
            str_starts_with($code, 'KG') => 'kegiatan-anak',
            str_starts_with($code, 'G')  => 'gerbang',
            str_starts_with($code, 'B')  => 'buklet',
            isset(Guide::terms()[$key])  => $key,
            default                      => 'kode-' . strtolower(strtok($code, '-')),
        };
    }

    public function testNothingSecretOrPersonal(): void
    {
        $text = json_encode([Guide::chapters(), Guide::terms()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // Owner, 17 Nov 2026: full detail is fine, but never passwords, secrets,
        // test-account credentials, server or hosting details, or personal data
        $this->assertDoesNotMatchRegularExpression('/password|\.env\b|encryption|ipSalt|\bsalt\b/i', $text);
        $this->assertDoesNotMatchRegularExpression('/\buji\.[a-z]+\d*\b/', $text, 'no test-account names');
        $this->assertDoesNotMatchRegularExpression('/@[a-z]|\b\d{1,3}(\.\d{1,3}){3}\b|\bssh\b|\bvps\b|hosting|nginx|apache/i', $text, 'no addresses or server details');
        $this->assertDoesNotMatchRegularExpression('/\bNPSN \d|\b\d{8}\b/', $text, 'no school numbers');
    }

    public function testPublicPagesRender(): void
    {
        $this->createRawDataWorld();

        $routes = service('routes');
        $routes->loadRoutes();
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-gelita'), 'public (owner, 17 Nov 2026)');
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-gelita/addie'));

        $home = $this->guest('admin/panduan-gelita');
        $this->assertStringContainsString(lang('Admin.gguideHomeTitle'), $home);
        $this->assertStringContainsString('href="' . Guide::CATALOGUE . '" rel="noopener"', $home);
        $this->assertStringContainsString('href="' . base_url('admin/panduan-staf') . '"', $home);
        $this->assertStringNotContainsString('class="gd-roles"', $home, 'no reader picker');
        $this->assertSame(count(Guide::chapters()), substr_count($home, 'class="gd-card-meta"'));

        foreach (array_keys(Guide::sectionsById(Guide::chapters())) as $id) {
            $page = $this->guest('admin/panduan-gelita/' . $id);
            $this->assertStringContainsString('id="' . $id . '"', $page, $id);
        }

        $addie = $this->guest('admin/panduan-gelita/addie');
        $this->assertStringContainsString(lang('Admin.gguideWhy'), $addie);
        $this->assertStringContainsString(lang('Admin.gguideRefs'), $addie);
        $this->assertStringNotContainsString('Dokumen di repositori', $addie);
        // Breadcrumb: guide home › chapter (its card on the home page) › topic
        $this->assertMatchesRegularExpression('~<nav class="gd-crumbs"[^>]*>.*href="' . preg_quote(base_url('admin/panduan-gelita'), '~') . '".*href="[^"]*#bab-model".*<span aria-current="page">ADDIE dan alasannya</span>~s', $addie);
        $this->assertStringContainsString('id="bab-model"', $home);
        // The title is a menu to the other guides and pages, with the lantern, no "GELITA" word mark
        $this->assertMatchesRegularExpression('~<details class="gd-switch">.*class="gd-emblem.*<span class="gd-brand-title">Mengenal GELITA</span>~s', $addie);
        $this->assertStringNotContainsString('class="sidebar-mark"', $addie);
        foreach (['admin/panduan-staf', 'admin/panduan-validator', 'admin/login'] as $path) {
            $this->assertStringContainsString('href="' . base_url($path) . '"', $addie, $path);
        }
        $this->assertStringContainsString('href="' . base_url('admin/panduan-gelita') . '" aria-current="page"', $addie);

        // Rich popover: kind, picture when installed, and the catalogue
        $five = $this->guest('admin/panduan-gelita/lima-tantangan');
        $this->assertMatchesRegularExpression('~id="istilah-pop-isi-rumpang".*?class="term-pop-kind">Tantangan 2 &middot; D1<.*?<img class="term-pop-picture" src="[^"]*node-card-rumpang\.webp\?v=.*?href="' . preg_quote(Guide::CATALOGUE, '~') . '#tantangan/rumpang" target="_blank" rel="noopener"~s', $five);
        $this->assertStringContainsString('<th scope="col">', $five);
        $this->assertStringContainsString('Branch, R. M. (2009)', $addie);
        $this->assertStringContainsString('id="istilah-pop-addie"', $addie);
        $this->assertStringNotContainsString(lang('Admin.gguideStale', ['']), $addie);

        $glossary = $this->guest('admin/panduan-gelita/istilah');
        $this->assertSame(count(Guide::terms()), substr_count($glossary, 'class="gd-term"'));

        foreach (['renderer', 'staffContext'] as $name) {
            Services::resetSingle($name);
        }
        $staff = $this->withSession(['staff_id' => 2, 'staff_role' => 'ketua', 'staff_name' => 'Ketua'])
            ->call(Method::GET, 'admin/panduan-gelita/addie');
        $staff->assertOK();
        $this->assertStringContainsString(lang('Admin.guideBackToPanel'), (string) $staff->getBody());
    }

    public function testUnknownPageIsNotFound(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->call(Method::GET, 'admin/panduan-gelita/tidak-ada');
    }

    public function testLoginPageOffersTheGuideAfterTheOtherTwo(): void
    {
        $this->createRawDataWorld();
        $_SESSION = [];
        Services::resetSingle('renderer');

        $page = (string) $this->call(Method::GET, 'admin/login')->getBody();
        $this->assertMatchesRegularExpression('/auth-guide-title.*auth-vguide-title.*auth-gguide-title/s', $page);
        $this->assertStringContainsString('href="' . base_url('admin/panduan-gelita') . '"', $page);
        $this->assertStringContainsString(lang('Admin.gguideCardTitle'), $page);
    }

    private function guest(string $path): string
    {
        foreach (['renderer', 'staffContext', 'contentRepository'] as $name) {
            Services::resetSingle($name);
        }
        service('cache')->clean();
        $_SESSION = [];

        $response = $this->call(Method::GET, $path);
        $response->assertOK();

        return (string) $response->getBody();
    }
}
