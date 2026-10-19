<?php

use App\Libraries\AssetMinifier;
use App\Services\AssetPackageBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Database\PackageWorld;

/**
 * Minified JS/CSS mirror (AssetMinifier, `gelita:aset:minify`): the sources
 * stay untouched, the mirror keeps names, folders, template text, and font
 * URLs, it is used only while every source still matches, and a package
 * built with it carries the minified bytes under the usual keys. The last
 * test compares the syntax trees of every real game file and its minified
 * copy (tools/qa/minify-check.mjs) when Node and acorn are available.
 *
 * @internal
 */
final class AssetMinifierTest extends CIUnitTestCase
{
    use PackageWorld;

    private const GAME_JS = "// Entry point\nimport { label } from './core/text.js';\n\n/* Counts */\nexport const text = (items, nodes) => `\${items > 0 ? `\${items} soal untuk \${nodes} tantangan` : 'Isi berkas ini'} siap.`;\nconsole.log(label);\n";
    private const TEXT_JS = "export const label = 'Halo,   Jaka';\n";
    private const BASE_CSS = "/* Fonts */\n@font-face {\n  font-family: 'Cinzel';\n  src: url('../fonts/a.woff2') format('woff2');\n}\n\n.stage {\n  width: calc(100% - 12px);\n}\n";

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->createPackageWorld();
        config('Gelita')->minifyAssets = true;

        $this->writeFile('assets/js/game.js', self::GAME_JS);
        $this->writeFile('assets/js/core/text.js', self::TEXT_JS);
        $this->writeFile('assets/css/base.css', self::BASE_CSS);
    }

    protected function tearDown(): void
    {
        config('Gelita')->minifyAssets = false;
        Services::resetSingle('assetMinifier');
        $this->dropPackageWorld();
        parent::tearDown();
    }

    public function testMirrorKeepsNamesTextAndFontUrlsAndLeavesSourcesAlone(): void
    {
        $result = $this->minifier()->refresh();
        $min    = $this->public . AssetMinifier::MIN_DIR . $result['version'] . '/';

        $this->assertTrue($result['rebuilt']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{12}$/', $result['version']);
        $this->assertSame(self::GAME_JS, file_get_contents($this->public . 'assets/js/game.js'), 'source untouched');
        $this->assertSame(self::BASE_CSS, file_get_contents($this->public . 'assets/css/base.css'), 'source untouched');

        foreach (['js/game.js', 'js/core/text.js', 'js/core/config.js', 'js/admin.js', 'js/admin/tables.js', 'css/base.css', 'css/admin.css', 'fonts/a.woff2'] as $key) {
            $this->assertFileExists($min . $key, $key);
        }

        $game = (string) file_get_contents($min . 'js/game.js');
        $this->assertLessThan(strlen(self::GAME_JS), strlen($game));
        $this->assertStringNotContainsString('Entry point', $game);
        $this->assertStringContainsString("from'./core/text.js'", str_replace(' ', '', $game));
        $this->assertStringContainsString('`${items} soal untuk ${nodes} tantangan`', $game, 'nested template text keeps its spaces');
        $this->assertStringContainsString("'Halo,   Jaka'", (string) file_get_contents($min . 'js/core/text.js'), 'string literals keep their spaces');

        $css = (string) file_get_contents($min . 'css/base.css');
        $this->assertStringContainsString("url('../fonts/a.woff2')", $css);
        $this->assertStringContainsString('calc(100% - 12px)', $css);
        $this->assertStringNotContainsString('Fonts', $css);
        $this->assertSame('wOF2-font', file_get_contents($min . 'fonts/a.woff2'));
        $this->assertFalse($this->minifier()->refresh()['rebuilt'], 'a fresh mirror is kept');
    }

    public function testEveryBuildGetsNewUrlsAndKeepsOnlyThePreviousOne(): void
    {
        $first = $this->minifier()->refresh()['version'];

        $this->writeFile('assets/js/core/text.js', "export const label = 'Dua';\n");
        $second = $this->minifier()->refresh()['version'];

        $this->writeFile('assets/js/core/text.js', "export const label = 'Tiga';\n");
        $third = $this->minifier()->refresh()['version'];

        $this->assertCount(3, array_unique([$first, $second, $third]));
        $this->assertSame('min/' . $third . '/js/game.js', $this->minifier()->path('js/game.js'));
        $this->assertDirectoryDoesNotExist($this->public . AssetMinifier::MIN_DIR . $first, 'older builds are removed');
        $this->assertFileExists($this->public . AssetMinifier::MIN_DIR . $second . '/js/core/text.js', 'an open page may still lazy-load the previous build');

        // Same content again (only the mtime changed): same version, previous kept
        touch($this->public . 'assets/js/core/text.js', time() + 5);
        $this->assertSame($third, $this->minifier()->refresh()['version']);
        $this->assertDirectoryExists($this->public . AssetMinifier::MIN_DIR . $second);
        $this->assertSame([], glob($this->public . AssetMinifier::MIN_DIR . '.*-*') ?: [], 'no temporary files left');
    }

    public function testMirrorIsUsedOnlyWhileEverySourceMatches(): void
    {
        $v = $this->minifier()->refresh()['version'];

        $this->assertSame("min/{$v}/js/game.js", $this->minifier()->path('js/game.js'));
        $this->assertSame("min/{$v}/fonts/a.woff2", $this->minifier()->path('/fonts/a.woff2'));
        $this->assertNull($this->minifier()->path('vendor/howler.min.js'), 'vendor files are not mirrored');
        $this->assertNull($this->minifier()->path('ui/kenali.webp'));

        // One imported module edited without a new run: the whole set falls back
        $this->writeFile('assets/js/core/text.js', "export const label = 'Baru';\n");
        $this->assertNull($this->minifier()->path('js/game.js'));
        $this->assertNull($this->minifier()->path('css/base.css'));

        $v = $this->minifier()->refresh()['version'];
        $this->assertSame("min/{$v}/js/game.js", $this->minifier()->path('js/game.js'));

        $this->writeFile('assets/js/core/new.js', "export const y = 2;\n");
        $this->assertNull($this->minifier()->path('js/game.js'), 'a new source makes the mirror stale');

        $this->minifier()->refresh();
        unlink($this->public . 'assets/js/core/new.js');
        $this->assertNull($this->minifier()->path('js/game.js'), 'a removed source makes the mirror stale');

        $this->minifier()->refresh();
        config('Gelita')->minifyAssets = false;
        $this->assertNull($this->minifier()->path('js/game.js'), 'switched off');
    }

    public function testFailedRunKeepsTheOldMirror(): void
    {
        $v      = $this->minifier()->refresh()['version'];
        $before = file_get_contents($this->public . AssetMinifier::MIN_DIR . $v . '/js/game.js');
        $state  = file_get_contents($this->public . AssetMinifier::MIN_DIR . AssetMinifier::STATE_FILE);

        $this->writeFile('assets/js/core/broken.js', "export const y = 2;\n");

        try {
            $this->failingMinifier()->refresh();
            $this->fail('A failed file must stop the run.');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('js/core/broken.js', $e->getMessage());
        }

        $this->assertSame($before, file_get_contents($this->public . AssetMinifier::MIN_DIR . $v . '/js/game.js'));
        $this->assertSame($state, file_get_contents($this->public . AssetMinifier::MIN_DIR . AssetMinifier::STATE_FILE));
        $this->assertSame([$this->public . AssetMinifier::MIN_DIR . $v], glob($this->public . AssetMinifier::MIN_DIR . '*', GLOB_ONLYDIR));
        $this->assertSame([], glob($this->public . AssetMinifier::MIN_DIR . '.*-*') ?: [], 'no temporary folders left');
        $this->assertNull($this->minifier()->path('js/game.js'), 'stale, so the originals are served');
    }

    public function testHelpersServeTheMirrorWhileFreshAndTheOriginalsOtherwise(): void
    {
        $minifier = new AssetMinifier($this->public);
        Services::injectMock('assetMinifier', $minifier);

        $this->assertStringStartsWith(base_url('assets/js/game.js') . '?v=', asset_url_versioned('js/game.js'));
        $this->assertSame(base_url('assets/fonts/a.woff2'), game_asset_url('fonts/a.woff2'));

        $v = $minifier->refresh()['version'];
        Services::injectMock('assetMinifier', new AssetMinifier($this->public));

        $this->assertStringStartsWith(base_url("assets/min/{$v}/js/game.js") . '?v=', asset_url_versioned('js/game.js'));
        $this->assertStringStartsWith(base_url("assets/min/{$v}/css/admin.css") . '?v=', asset_url_versioned('css/admin.css'));
        $this->assertStringStartsWith(base_url('assets/vendor/howler.min.js') . '?v=', asset_url_versioned('vendor/howler.min.js'));
        $this->assertSame(base_url("assets/min/{$v}/fonts/a.woff2"), game_asset_url('fonts/a.woff2'), 'preload matches the minified CSS');
    }

    public function testPackageCarriesTheMinifiedCodeUnderTheUsualKeys(): void
    {
        $summary  = (new AssetPackageBuilder($this->packages))->build(['copy_library' => false]);
        $manifest = $this->packages->manifest($summary['package_code']);
        $files    = array_column($manifest['files'], null, 'key');
        $min      = $this->public . 'assets/' . dirname((string) $this->minifier()->path('js/game.js'), 2) . '/';

        $this->assertSame([], array_values(array_filter($summary['warnings'], static fn (array $w): bool => $w['code'] === 'minify_failed')));

        foreach (['js/game.js', 'js/core/text.js', 'css/base.css'] as $key) {
            $this->assertSame($manifest['code_dir'] . $key, $files[$key]['url']);
            $this->assertSame(hash_file('sha256', $min . $key), $files[$key]['sha256'], $key);
            $this->assertNotSame(hash_file('sha256', $this->public . 'assets/' . $key), $files[$key]['sha256'], $key);
            $this->assertSame(hash_file('sha256', $this->public . $files[$key]['url']), $files[$key]['sha256'], $key . ' copy on disk');
        }

        $this->assertSame(hash_file('sha256', $this->public . 'assets/fonts/a.woff2'), $files['fonts/a.woff2']['sha256'], 'fonts as they are');
        $this->assertArrayNotHasKey('js/admin.js', $files);
    }

    public function testPackageFallsBackToTheOriginalsWhenMinifyingFails(): void
    {
        $this->writeFile('assets/js/core/broken.js', "export const y = 2;\n");

        $summary  = (new AssetPackageBuilder($this->packages, null, $this->failingMinifier()))->build(['copy_library' => false]);
        $manifest = $this->packages->manifest($summary['package_code']);
        $files    = array_column($manifest['files'], null, 'key');

        $this->assertSame(['minify_failed'], array_values(array_unique(array_column(array_filter($summary['warnings'], static fn (array $w): bool => $w['code'] === 'minify_failed'), 'code'))));
        $this->assertSame(hash_file('sha256', $this->public . 'assets/js/game.js'), $files['js/game.js']['sha256']);
    }

    public function testEveryRealGameFileKeepsItsSyntaxTree(): void
    {
        $node = trim((string) shell_exec('command -v node 2>/dev/null'));

        if ($node === '') {
            $this->markTestSkipped('Node is not installed; run tools/qa/minify-check.mjs where it is.');
        }

        // Mirror the real sources in the temporary public/ (the repo's own mirror is never touched)
        foreach (['js', 'css'] as $dir) {
            $this->removeDir($this->public . 'assets/' . $dir);
            $items = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(FCPATH . 'assets/' . $dir, FilesystemIterator::SKIP_DOTS));

            foreach ($items as $file) {
                $this->writeFile('assets/' . $dir . '/' . substr($file->getPathname(), strlen(FCPATH . 'assets/' . $dir) + 1), (string) file_get_contents($file->getPathname()));
            }
        }

        $v = $this->minifier()->refresh()['version'];

        $command = escapeshellarg($node) . ' ' . escapeshellarg(ROOTPATH . 'tools/qa/minify-check.mjs')
            . ' ' . escapeshellarg('--src=' . $this->public . 'assets/js')
            . ' ' . escapeshellarg('--min=' . $this->public . AssetMinifier::MIN_DIR . $v . '/js') . ' 2>&1';
        exec($command, $output, $code);

        if ($code === 2) {
            $this->markTestSkipped('acorn is not installed; see tools/qa/minify-check.mjs.');
        }

        $this->assertSame(0, $code, implode("\n", $output));
    }

    private function minifier(): AssetMinifier
    {
        return new AssetMinifier($this->public);
    }

    /** Fails on js/core/broken.js, as a read or tokenizer error would. */
    private function failingMinifier(): AssetMinifier
    {
        return new class ($this->public) extends AssetMinifier {
            protected function minifyFile(string $key, string $mode): string
            {
                if ($key === 'js/core/broken.js') {
                    throw new RuntimeException("Cannot minify {$key}: test failure.");
                }

                return parent::minifyFile($key, $mode);
            }
        };
    }
}
