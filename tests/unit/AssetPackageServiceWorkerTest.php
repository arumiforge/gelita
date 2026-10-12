<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Service worker and page downloader of the asset package, checked statically
 * (docs/rencana-paket-aset.md §9.1 items 1–3; replaces the old
 * "the worker only caches offline.html" test of InstallableAppTest):
 *
 *  1. every cache write goes through one guard that accepts only package URLs
 *     (assets/p/*, assets/k/*, paket/pkt-*.json) and the offline page;
 *  2. navigations (HTML), /api, /admin, /masuk, /daftar, /keluar, POST, and
 *     responses with Set-Cookie or Cache-Control private/no-store are never
 *     cached;
 *  3. /api/* and /admin* go straight to the network without respondWith().
 *
 * tools/qa/package-check.mjs checks the same at run time in Chromium.
 *
 * @internal
 */
final class AssetPackageServiceWorkerTest extends CIUnitTestCase
{
    private const PACKAGE_URLS = [
        'assets/p/0123456789abcdef.jpg',
        'assets/p/9b1e00aa11bb22cc.mp3',
        'assets/k/2.0.0-ab12cd34/js/game.js',
        'assets/k/2.0.0-ab12cd34/js/engines/cari.js',
        'assets/k/2.0.0-ab12cd34/fonts/cinzel-latin-700-normal.woff2',
        'assets/k/2.0.0-ab12cd34/offline.html',
        'paket/pkt-2026-10-20-01.json',
    ];

    private const NEVER_CACHED = [
        '',
        'peta',
        'masuk',
        'daftar/3',
        'keluar',
        'api/events',
        'api/paket/aktif',
        'admin',
        'admin/media',
        'assets/css/game.css',
        'assets/js/game.js',
        'assets/uploads/bg.welcome.jpg',
        'assets/audio/music/map.mp3',
        'assets/p/0123456789ABCDEF.jpg',
        'assets/p/short.jpg',
        'assets/k/',
        'assets/k/v1',
        'paket/aktif.json',
        'paket/pkt-2026-10-20-01.php',
        'gelita-meta/aktif.json',
        'sw.js',
    ];

    private string $sw;

    private string $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sw   = (string) file_get_contents(FCPATH . 'sw.js');
        $this->page = (string) file_get_contents(FCPATH . 'assets/js/core/package.js');
    }

    public function testEveryWorkerCacheWriteGoesThroughTheGuard(): void
    {
        $this->assertSame(1, substr_count($this->sw, 'cache.put('), 'one cache write in the worker');
        $this->assertStringContainsString('cache.put(', $this->jsFunction($this->sw, 'putChecked'));
        $this->assertStringNotContainsString('cache.add(', $this->sw);
        $this->assertStringNotContainsString('.addAll(', $this->sw);

        // The worker itself only stores the offline page; package files come from the page downloader
        preg_match_all('/(?<!function )putChecked\(([^,)]+)/', $this->sw, $calls);
        $this->assertSame(['OFFLINE_URL'], array_values(array_unique(array_map('trim', $calls[1]))));
        $this->assertStringContainsString("new URL('offline.html', self.registration.scope)", $this->sw);
    }

    public function testGuardAcceptsOnlyPackageUrlsAndTheOfflinePage(): void
    {
        $package = $this->regex($this->sw, 'PACKAGE_PATH');
        $never   = $this->regex($this->sw, 'NEVER_CACHE');

        foreach (self::PACKAGE_URLS as $path) {
            $this->assertMatchesRegularExpression($package, $path);
            $this->assertDoesNotMatchRegularExpression($never, $path);
        }

        foreach (self::NEVER_CACHED as $path) {
            $this->assertDoesNotMatchRegularExpression($package, $path, $path);
        }

        foreach (['api', 'api/events', 'admin', 'admin/login', 'masuk', 'daftar', 'daftar/2', 'keluar'] as $path) {
            $this->assertMatchesRegularExpression($never, $path, $path);
        }

        $cacheFor = $this->jsFunction($this->sw, 'cacheFor');
        $this->assertLessThan(strpos($cacheFor, 'OFFLINE_CACHE'), strpos($cacheFor, 'NEVER_CACHE.test(path)'), 'refused paths are checked first');
        $this->assertStringContainsString("path === 'offline.html'", $cacheFor);
        $this->assertStringContainsString('PACKAGE_PATH.test(path) ? ASSET_CACHE : null', $cacheFor);

        $guard = $this->jsFunction($this->sw, 'putChecked');
        $this->assertLessThan(strpos($guard, 'cache.put('), strpos($guard, 'cacheFor(url)'), 'the guard runs before the write');
    }

    public function testGuardRefusesResponsesThatMayHoldPrivateData(): void
    {
        $guard = $this->jsFunction($this->sw, 'putChecked');

        $this->assertStringContainsString('response.status !== 200', $guard);
        $this->assertStringContainsString('response.redirected', $guard);
        $this->assertStringContainsString("response.headers.has('Set-Cookie')", $guard);
        $this->assertStringContainsString('(?:private|no-store)', $guard);
        $this->assertStringContainsString('return false;', $guard);
    }

    public function testNavigationsAndPostAreNeverCached(): void
    {
        $listener = $this->listener($this->sw, 'fetch');

        $this->assertStringStartsWith("const { request } = event;\n  if (request.method !== 'GET') return;", trim($listener));
        $this->assertStringContainsString("if (request.mode === 'navigate') {\n    event.respondWith(navigate(event));", $listener);

        $navigate = $this->jsFunction($this->sw, 'navigate');
        $this->assertStringNotContainsString('put', $navigate, 'HTML never goes into a cache');
        $this->assertStringContainsString('event.preloadResponse', $navigate);
        $this->assertStringContainsString('caches.match(OFFLINE_URL', $navigate);

        $package = $this->jsFunction($this->sw, 'fromPackage');
        $this->assertStringNotContainsString('put', $package, 'a cache miss is fetched, not stored');
    }

    public function testApiAndAdminPassStraightToTheNetwork(): void
    {
        $listener = $this->listener($this->sw, 'fetch');
        $pass     = $this->regex($this->sw, 'PASS_THROUGH');

        foreach (['api', 'api/events', 'api/paket/aktif', 'admin', 'admin/login', 'admin/media/3?x=1'] as $path) {
            $this->assertMatchesRegularExpression($pass, $path, $path);
        }

        foreach (['peta', 'administrasi', 'assets/p/0123456789abcdef.jpg', 'apis'] as $path) {
            $this->assertDoesNotMatchRegularExpression($pass, $path, $path);
        }

        $return = strpos($listener, 'if (path === null || PASS_THROUGH.test(path)) return;');
        $this->assertNotFalse($return);
        $this->assertLessThan(strpos($listener, 'respondWith'), $return, 'no respondWith before the pass-through');
        $this->assertSame(2, substr_count($this->sw, 'event.respondWith('), 'navigations and package files only');
    }

    public function testAudioRangeRequestsGetPartialContent(): void
    {
        $range = $this->jsFunction($this->sw, 'rangeResponse');

        $this->assertStringContainsString('status: 206', $range);
        $this->assertStringContainsString('`bytes ${start}-${end}/${size}`', $range);
        $this->assertStringContainsString('status: 416', $range);
        $this->assertStringContainsString("request.headers.has('Range') ? rangeResponse(", $this->jsFunction($this->sw, 'fromPackage'));
    }

    public function testWorkerKeepsThePackageCachesOnUpdate(): void
    {
        $this->assertStringContainsString("const ASSET_CACHE = 'gelita-aset';", $this->sw);
        $this->assertStringContainsString("const META_CACHE = 'gelita-meta';", $this->sw);
        $this->assertStringContainsString('const KEEP = [OFFLINE_CACHE, ASSET_CACHE, META_CACHE];', $this->sw);
        $this->assertStringContainsString("key.startsWith('gelita-') && !KEEP.includes(key)", $this->listener($this->sw, 'activate'));
    }

    public function testPageDownloaderUsesTheSameGuardAndVerifiesBeforeWriting(): void
    {
        $this->assertSame(1, substr_count($this->page, 'cache.put('), 'one cache write in the downloader');
        $this->assertStringContainsString('cache.put(', $this->jsFunction($this->page, 'putChecked'));
        $this->assertStringNotContainsString('cache.add(', $this->page);
        $this->assertStringNotContainsString('.addAll(', $this->page);
        $this->assertSame($this->regex($this->sw, 'PACKAGE_PATH'), $this->regex($this->page, 'PACKAGE_PATH'), 'same URL pattern as the worker');

        $guard = $this->jsFunction($this->page, 'putChecked');
        $this->assertStringContainsString('(cacheName === ASSET_CACHE && path !== null && PACKAGE_PATH.test(path))', $guard);
        $this->assertStringContainsString('(cacheName === META_CACHE && href === markerUrl())', $guard);

        // sha256 before cache.put (§5.5), 4 in parallel, retries 2/4/8 s (§5.6), quota × 1.2 (§8.4)
        $this->assertStringContainsString("crypto.subtle.digest('SHA-256'", $this->page);
        $run = $this->jsFunction($this->page, 'runDownload');
        $this->assertLessThan(strpos($run, 'await putChecked(ASSET_CACHE, file.href'), strpos($run, 'await fetchVerified(file, options)'));
        $this->assertStringContainsString('const PARALLEL = 4;', $this->page);
        $this->assertStringContainsString('const RETRY_DELAYS = [2000, 4000, 8000];', $this->page);
        $this->assertStringContainsString('const QUOTA_FACTOR = 1.2;', $this->page);
        $this->assertStringContainsString("export const ASSET_CACHE = 'gelita-aset';", $this->page);
        $this->assertStringContainsString("export const META_CACHE = 'gelita-meta';", $this->page);

        // The marker is written last: the switch to a new package is atomic
        $this->assertGreaterThan(strpos($run, 'await fetchAll(todo);'), strpos($run, 'await putChecked(META_CACHE, markerUrl()'));

        // The online-mode choice never outlives the browser session (§8.7)
        $this->assertStringContainsString("storageSet('sessionStorage', ONLINE_KEY, reason);", $this->page);
        $this->assertStringNotContainsString("storageSet('localStorage', ONLINE_KEY", $this->page);
    }

    public function testKillSwitchWorkerHasNoFetchHandler(): void
    {
        $kill = (string) file_get_contents(FCPATH . 'sw-kosong.js');

        $this->assertStringNotContainsString("addEventListener('fetch'", $kill);
        $this->assertStringContainsString("caches.delete('gelita-meta')", $kill);
        $this->assertStringNotContainsString("caches.delete('gelita-aset')", $kill, 'package files stay for later');
        $this->assertStringNotContainsString('cache.put(', $kill);

        $install = (string) file_get_contents(FCPATH . 'assets/js/game/install.js');
        $this->assertStringContainsString("window.GELITA_PACKAGE_BOOT?.killed ? 'sw-kosong.js' : 'sw.js'", $install);
    }

    public function testBootScriptRunsBeforeTheGameModule(): void
    {
        $layout = (string) file_get_contents(APPPATH . 'Views/layouts/game.php');
        $boot   = strpos($layout, "<script src=\"<?= asset_url_versioned('js/core/package-boot.js') ?>\"<?= asset_package_attrs() ?>></script>");

        $this->assertNotFalse($boot, 'classic script with the package attributes');
        $this->assertLessThan(strpos($layout, "asset_url_versioned('js/game.js')"), $boot);
        $this->assertLessThan(strpos($layout, '<link rel="stylesheet"'), $boot, 'in <head>, before the stylesheets');

        $script = (string) file_get_contents(FCPATH . 'assets/js/core/package-boot.js');
        $this->assertStringContainsString("document.documentElement.setAttribute('data-assets-local', '1')", $script);
        $this->assertStringContainsString("window.sessionStorage.getItem('gelita.paket.daring')", $script);
        $this->assertStringNotContainsString('import ', $script, 'a classic script, not a module');
    }

    /** Body of `function name(` / `async function name(` in a JS source (brace matching). */
    private function jsFunction(string $source, string $name): string
    {
        $this->assertSame(1, preg_match('/(?:async )?function ' . preg_quote($name, '/') . '\(/', $source, $m, PREG_OFFSET_CAPTURE), "function {$name}");

        return $this->block($source, (int) $m[0][1]);
    }

    /** Body of `self.addEventListener('type', (event) => {…})`. */
    private function listener(string $source, string $type): string
    {
        $start = strpos($source, "self.addEventListener('{$type}'");
        $this->assertNotFalse($start, "listener {$type}");

        return $this->block($source, $start);
    }

    private function block(string $source, int $from): string
    {
        $open  = strpos($source, '{', strpos($source, ')', $from));
        $depth = 0;

        for ($i = $open, $n = strlen($source); $i < $n; $i++) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}' && --$depth === 0) {
                return substr($source, $open + 1, $i - $open - 1);
            }
        }

        $this->fail('unbalanced braces');
    }

    /** `const NAME = /…/;` in a JS source, as a PCRE pattern. */
    private function regex(string $source, string $name): string
    {
        $this->assertSame(1, preg_match('/^const ' . $name . ' = \/(.+)\/;$/m', $source, $m), "regex {$name}");

        return '~' . str_replace('~', '\~', $m[1]) . '~';
    }
}
