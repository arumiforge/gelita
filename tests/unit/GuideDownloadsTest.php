<?php

use App\Libraries\GuideDownloads;
use App\Libraries\StaffGuideContent;
use App\Libraries\ValidatorGuideContent;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * Research PDFs offered by the public guides (owner, 20 November 2026):
 * the whitelist (App\Libraries\GuideDownloads) holds only files that may be
 * public, every file is there, each download serves its file under a
 * readable name and nothing else is served, and the guide pages link them
 * (the validator guide per panel).
 *
 * @internal
 */
final class GuideDownloadsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    /** Print versions the owner allowed for the staff guide (02, 03, 11, 13–17, 23, 24). */
    private const PRINT_NUMBERS = ['02', '03', '11', '13', '14', '15', '16', '17', '23', '24'];

    protected function tearDown(): void
    {
        if (db_connect()->tableExists('participants')) {
            $this->dropResetTables();
        }
        parent::tearDown();
    }

    public function testEveryFileIsHereAndMayBePublic(): void
    {
        $this->assertNotEmpty(GuideDownloads::forGuide(GuideDownloads::STAFF));

        foreach (GuideDownloads::FILES as $key => $file) {
            $this->assertMatchesRegularExpression('/^[a-z0-9]+(-[a-z0-9]+)*$/', $key, 'keys are URL slugs');
            $this->assertContains($file['guide'], [GuideDownloads::VALIDATOR, GuideDownloads::STAFF], $key);
            $this->assertGreaterThan(0, GuideDownloads::size($file) ?? 0, "{$key}: {$file['path']} is in the checkout and not empty");

            // Repository-relative, under docs/, never outside it
            $this->assertMatchesRegularExpression('#^docs/[A-Za-z0-9._/-]+$#', $file['path'], $key);
            $this->assertStringNotContainsString('..', $file['path'], $key);
            $this->assertStringNotContainsString('/', $file['name'], $key);
            $this->assertSame(strtolower(pathinfo($file['path'], PATHINFO_EXTENSION)), strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), "{$key}: saved under its own kind");
            $this->assertNotSame('', $file['type'], $key);

            // Never offered, whatever happens (owner, 20 Nov 2026)
            $this->assertStringNotContainsString('khusus-peneliti/', $file['path'], "{$key}: the B.5a key for researchers");
            $this->assertStringNotContainsString('materi-b5a-buklet-buta', $file['path'], "{$key}: the B.5a booklet lists every game item with its key (held back, 21 Nov 2026)");
            $this->assertStringNotContainsString('/arsip/', $file['path'], "{$key}: archived drafts");
            $this->assertStringEndsNotWith('serah-terima-peneliti.pdf', $file['path'], "{$key}: the developer's handover file");
            $this->assertDoesNotMatchRegularExpression('#^docs/revisi-10-analitik/dokumen-pdf/.*\.xlsx$#', $file['path'], "{$key}: no validator worksheet");

            if (preg_match('#/cetak/(\d\d)_#', $file['path'], $m) === 1) {
                $this->assertContains($m[1], self::PRINT_NUMBERS, "{$key}: only the print versions the owner listed");
                $this->assertSame(GuideDownloads::STAFF, $file['guide'], "{$key}: print versions are the staff guide's");
            }
        }

        // Each validator panel has exactly its own file
        $byPanel = [];
        foreach (GuideDownloads::forGuide(GuideDownloads::VALIDATOR) as $key => $file) {
            $this->assertNotEmpty($file['panels'], $key);
            $this->assertSame([], array_diff($file['panels'], array_keys(ValidatorGuideContent::PANELS)), $key);
            foreach ($file['panels'] as $panel) {
                $byPanel[$panel][] = basename($file['path']);
            }
        }
        $this->assertSame([
            'materi' => ['instrumen-validasi-ahli-materi-v2.1.pdf'],
            'b5a'    => [],
            'budaya' => ['instrumen-validasi-pengamat-budaya-v2.1.pdf'],
            'media'  => ['instrumen-validasi-ahli-media-v1.1.pdf'],
        ], array_merge(array_fill_keys(array_keys(ValidatorGuideContent::PANELS), []), $byPanel));
    }

    public function testLabelsFollowTheGuidesRules(): void
    {
        foreach (GuideDownloads::FILES as $key => $file) {
            $text = $file['label'] . ' ' . $file['text'];
            $this->assertNotSame('', trim($file['label']), $key);
            $this->assertNotSame('', trim($file['text']), $key);

            // Readers' words: no document numbers, file names, paths, secrets or server talk
            $this->assertDoesNotMatchRegularExpression('/\bdokumen \d|\.(pdf|xlsx|zip|md)\b|docs\/|app\/|password|https?:|@[a-z]/i', $text, $key);
            $this->assertDoesNotMatchRegularExpression('/server|deploy|migrasi|composer|spark|gelita:|php|baris perintah/i', $text, $key);

            if ($file['guide'] === GuideDownloads::VALIDATOR) {
                // The validator guide's blinding (ValidatorGuideTest::testNothingValidatorsMustNotSee)
                $this->assertDoesNotMatchRegularExpression('/\b[a-z]{3}-\d-[abx]\d{2}\b|\btes-(mb|ae|bd)\b/', $text, $key);
                $this->assertDoesNotMatchRegularExpression('/\b(INF|DIG|BUD)(-[A-Z]{3})?\b|\bR-[A-Z]{3,}\b/', $text, $key);
                $this->assertDoesNotMatchRegularExpression('/\b(D[123][ab]?|L[123]|C[1-6])\b/', $text, $key);
                $this->assertDoesNotMatchRegularExpression('/kunci-peneliti|khusus-peneliti|ElevenLabs|ChatGPT|Claude/i', $text, $key);
            }
        }
    }

    public function testWhitelistLookups(): void
    {
        $this->assertNotNull(GuideDownloads::find(GuideDownloads::STAFF, 'kamus-data'));
        $this->assertNull(GuideDownloads::find(GuideDownloads::VALIDATOR, 'kamus-data'), 'a key of the other guide');
        $this->assertNull(GuideDownloads::find(GuideDownloads::STAFF, 'instrumen-ahli-materi'), 'a key of the other guide');
        $this->assertNull(GuideDownloads::find(GuideDownloads::STAFF, '../../.env'));
        // The B.5a booklet holds every game item with its key: held back until the owner confirms (21 November 2026)
        $this->assertSame([], GuideDownloads::forPanels(['b5a']));
        $this->assertNull(GuideDownloads::find(GuideDownloads::VALIDATOR, 'buklet-b5a'));
        $this->assertCount(3, GuideDownloads::forPanels([]), 'no panel: the three instruments');

        $this->assertSame('PDF · 687 KB', GuideDownloads::meta(['name' => 'a.pdf'], 703033));
        $this->assertSame('XLSX · 1,5 MB', GuideDownloads::meta(['name' => 'a.xlsx'], 1_572_864));
        $this->assertSame('ZIP', GuideDownloads::meta(['name' => 'a.zip'], null));
        $this->assertSame(base_url('admin/panduan-validator/unduh/instrumen-ahli-media'), GuideDownloads::url(GuideDownloads::VALIDATOR, 'instrumen-ahli-media'));
    }

    public function testRoutesArePublic(): void
    {
        $routes = service('routes');
        $routes->loadRoutes();
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-validator/unduh/instrumen-ahli-media'));
        $this->assertSame([], $routes->getFiltersForRoute('admin/panduan-staf/unduh/kamus-data'));
    }

    public function testEachDownloadServesItsFile(): void
    {
        foreach (GuideDownloads::FILES as $key => $file) {
            $base     = $file['guide'] === GuideDownloads::VALIDATOR ? 'admin/panduan-validator' : 'admin/panduan-staf';
            $response = $this->fetch("{$base}/unduh/{$key}");

            $this->assertInstanceOf(DownloadResponse::class, $response, $key);
            $this->assertSame(200, $response->getStatusCode(), $key);
            $this->assertSame($file['type'], $response->getHeaderLine('Content-Type'), $key);
            $this->assertSame('attachment; filename="' . $file['name'] . '"; filename*=UTF-8\'\'' . rawurlencode($file['name']), $response->getHeaderLine('Content-Disposition'), $key);
            $this->assertSame('noindex', $response->getHeaderLine('X-Robots-Tag'), $key);
            $this->assertMatchesRegularExpression('/^"[0-9a-f]{32}"$/', $response->getHeaderLine('ETag'), $key);
            $this->assertNotSame('', $response->getHeaderLine('Last-Modified'), $key);
            $this->assertSame((string) GuideDownloads::size($file), $response->getHeaderLine('Content-Length'), $key);

            ob_start();
            $response->sendBody();
            $body = (string) ob_get_clean();
            $this->assertSame(hash_file('sha256', GuideDownloads::fullPath($file)), hash('sha256', $body), "{$key}: the whitelisted file itself");
        }
    }

    public function testRepeatedRequestWithTheSameTagIsNotModified(): void
    {
        $first = $this->fetch('admin/panduan-staf/unduh/lembar-kosong');
        $etag  = $first->getHeaderLine('ETag');

        $again = $this->withHeaders(['If-None-Match' => $etag])->call(Method::GET, 'admin/panduan-staf/unduh/lembar-kosong')->response();
        $this->assertSame(304, $again->getStatusCode());
        $this->assertSame($etag, $again->getHeaderLine('ETag'));
        $this->assertSame('', (string) $again->getBody());
    }

    public function testNothingElseIsServed(): void
    {
        foreach ([
            'admin/panduan-staf/unduh/tidak-ada',                     // unknown key
            'admin/panduan-staf/unduh/instrumen-ahli-materi',         // the validator guide's key
            'admin/panduan-validator/unduh/kamus-data',               // the staff guide's key
            'admin/panduan-validator/unduh/serah-terima-peneliti',    // never offered
            'admin/panduan-staf/unduh/..%2F..%2F.env',                // path traversal, encoded
            'admin/panduan-staf/unduh/..%2Fdokumen-pdf%2Fserah-terima-peneliti.pdf',
            'admin/panduan-validator/unduh/%2E%2E%2Fberkas-validator%2Fkhusus-peneliti',
        ] as $path) {
            $this->assertNotFound($path);
        }
    }

    public function testMissingFileIsNotFound(): void
    {
        $file = GuideDownloads::FILES['kamus-data'];
        $this->assertNull(GuideDownloads::size($file, '/nonexistent/'));
        $this->assertSame([], GuideDownloads::rows(['x' => ['path' => 'docs/tidak-ada.pdf'] + $file]), 'a missing file is not listed');

        $this->expectException(PageNotFoundException::class);
        GuideDownloads::respond(GuideDownloads::STAFF, 'tidak-ada', service('request'), service('response'));
    }

    public function testValidatorGuideLinksThePanelsFiles(): void
    {
        $this->createRawDataWorld();
        $url = static fn (string $key): string => 'href="' . GuideDownloads::url(GuideDownloads::VALIDATOR, $key) . '"';

        // No panel picked: all four, each with its panel's name
        $home = $this->page('admin/panduan-validator');
        $this->assertStringContainsString(lang('Admin.guideDownloadsTitle'), $home);
        $this->assertStringContainsString(lang('Admin.vguideDownloadsLead'), $home);
        foreach (GuideDownloads::forGuide(GuideDownloads::VALIDATOR) as $key => $file) {
            $this->assertStringContainsString($url($key), $home);
            $this->assertStringContainsString($file['label'], $home);
        }
        foreach (GuideDownloads::forGuide(GuideDownloads::VALIDATOR) as $file) {
            $this->assertStringContainsString('<span class="gd-dl-eyebrow">' . ValidatorGuideContent::PANELS[$file['panels'][0]] . '</span>', $home);
        }
        $this->assertMatchesRegularExpression('/PDF · \d+(,\d)? (KB|MB)/', $home, 'kind and size');
        foreach (GuideDownloads::forGuide(GuideDownloads::STAFF) as $key => $file) {
            $this->assertStringNotContainsString('/unduh/' . $key . '"', $home, 'no staff file on the validator guide');
        }

        // A panel picked: only its own file, without the panel name
        foreach (GuideDownloads::forGuide(GuideDownloads::VALIDATOR) as $key => $file) {
            $panel  = $file['panels'][0];
            $picked = $this->page('admin/panduan-validator?panel=' . $panel);
            $this->assertStringContainsString(lang('Admin.vguideDownloadsLeadPicked'), $picked);
            $this->assertSame(1, substr_count($picked, 'class="gd-dl"'), $panel);
            $this->assertStringContainsString($url($key), $picked, $panel);
            $this->assertStringNotContainsString('gd-dl-eyebrow', $picked);
        }

        // The panel chapter's page marked `downloads` lists the chapter's file
        $pages = ['materi-kiriman' => 'instrumen-ahli-materi', 'budaya-tugas' => 'instrumen-pengamat-budaya', 'media-kiriman' => 'instrumen-ahli-media'];
        foreach ($pages as $section => $key) {
            $page = $this->page('admin/panduan-validator/' . $section);
            $this->assertSame(1, substr_count($page, 'class="gd-dl"'), $section);
            $this->assertStringContainsString($url($key), $page, $section);
        }
        $this->assertStringNotContainsString('class="gd-dl"', $this->page('admin/panduan-validator/masuk'), 'only marked pages');
        $this->assertStringNotContainsString('class="gd-dl"', $this->page('admin/panduan-validator/b5a-urutan'), 'no B.5a booklet');
        $this->assertStringNotContainsString('class="gd-dl"', $this->page('admin/panduan-validator?panel=b5a'), 'no B.5a booklet');
    }

    public function testStaffGuideHasADownloadPage(): void
    {
        $this->createRawDataWorld();

        $page = $this->page('admin/panduan-staf/dokumen-cetak');
        $this->assertStringContainsString('<h1 class="gd-title" id="gd-title">Lembar dan dokumen untuk dicetak</h1>', $page);
        $staff = GuideDownloads::forGroup(GuideDownloads::STAFF, GuideDownloads::DEFAULT_GROUP);
        $this->assertSame(count($staff), substr_count($page, 'class="gd-dl"'));
        foreach ($staff as $key => $file) {
            $this->assertStringContainsString('href="' . GuideDownloads::url(GuideDownloads::STAFF, $key) . '"', $page);
            $this->assertStringContainsString($file['label'], $page);
        }
        $this->assertStringNotContainsString('/panduan-validator/unduh/', $page);

        // In the field-work chapter, and a card on the first page
        $chapters = array_column(StaffGuideContent::chapters(), 'sections', 'id');
        $this->assertContains('dokumen-cetak', array_column($chapters['lapangan'], 'id'));
        $this->assertStringContainsString('href="' . base_url('admin/panduan-staf/dokumen-cetak') . '"', $this->page('admin/panduan-staf'));
        $this->assertStringNotContainsString('class="gd-dl"', $this->page('admin/panduan-staf/token'), 'only the marked page');
    }

    public function testStaffGuideOffersTheSimulatedSampleOnItsOwnPage(): void
    {
        $this->createRawDataWorld();

        // Owner, 20 November 2026: the 300-student sample download, in the head researcher's chapter, never on the print page
        $sample = GuideDownloads::forGroup(GuideDownloads::STAFF, 'contoh');
        $this->assertSame(['contoh-data-excel', 'contoh-data-csv'], array_keys($sample));

        $page = $this->page('admin/panduan-staf/contoh-data');
        $this->assertSame(2, substr_count($page, 'class="gd-dl"'));
        $this->assertStringContainsString('bukan data penelitian', strtolower($page));

        foreach (array_keys($sample) as $key) {
            $this->assertStringContainsString('href="' . GuideDownloads::url(GuideDownloads::STAFF, $key) . '"', $page);
            $this->assertStringNotContainsString(GuideDownloads::url(GuideDownloads::STAFF, $key), $this->page('admin/panduan-staf/dokumen-cetak'));
        }

        $chapters = array_column(StaffGuideContent::chapters(), 'sections', 'id');
        $this->assertContains('contoh-data', array_column($chapters['ketua'], 'id'));
    }

    private function fetch(string $path): \CodeIgniter\HTTP\ResponseInterface
    {
        $_SESSION = [];
        Services::resetSingle('response');

        $response = $this->call(Method::GET, $path)->response();

        if ($response instanceof DownloadResponse) {
            $response->buildHeaders();
        }

        return $response;
    }

    private function assertNotFound(string $path): void
    {
        try {
            $this->call(Method::GET, $path);
        } catch (PageNotFoundException) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->fail("{$path} is served");
    }

    private function page(string $path): string
    {
        foreach (['renderer', 'staffContext', 'contentRepository'] as $name) {
            Services::resetSingle($name);
        }
        service('cache')->clean();
        $_SESSION = [];

        $response = $this->call(Method::GET, $path);
        $response->assertOK();

        // The rendered page writes "·" and "–" as entities
        return html_entity_decode((string) $response->getBody(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
