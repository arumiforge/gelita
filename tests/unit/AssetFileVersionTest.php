<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Code URLs outside an asset package follow the file itself (owner,
 * 18 October 2026): a server updated without the deploy scripts keeps the
 * same `gelita.assetVersion`, yet a changed CSS/JS file must get a new `?v=`,
 * because nginx serves `?v=` URLs as immutable.
 *
 * @internal
 */
final class AssetFileVersionTest extends CIUnitTestCase
{
    /** Under the gitignored test upload folder, never a real game file. */
    private const PATH = 'uploads-test/version-test.css';

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        @mkdir(dirname(FCPATH . 'assets/' . self::PATH), 0777, true);
    }

    protected function tearDown(): void
    {
        @unlink(FCPATH . 'assets/' . self::PATH);
        parent::tearDown();
    }

    public function testChangedFileGetsANewUrlWithTheSameAssetVersion(): void
    {
        $file = FCPATH . 'assets/' . self::PATH;
        file_put_contents($file, 'a { color: red; }');
        touch($file, 1_760_000_000);
        clearstatcache();

        $before = asset_url_versioned(self::PATH);
        $this->assertStringStartsWith(base_url('assets/' . self::PATH) . '?v=' . config('Gelita')->assetVersion . '.', $before);
        $this->assertSame($before, asset_url_versioned(self::PATH), 'stable while the file is unchanged');

        file_put_contents($file, 'a { color: blue; }');
        touch($file, 1_760_000_060);
        clearstatcache();

        $this->assertNotSame($before, asset_url_versioned(self::PATH));
    }

    /**
     * The owner's UI pictures follow the file too (owner, 20 October 2026: the
     * replaced shard plate kept its URL and stayed cached after a refresh).
     */
    public function testUiPicturesCarryTheFileVersion(): void
    {
        foreach (['ui/hud-shards-frame.webp', 'ui/kenal-plate-navy.webp', 'ui/status-done.webp', 'ui/star.webp'] as $path) {
            $this->assertSame(base_url('assets/' . $path) . '?v=' . rawurlencode(asset_file_version($path)), asset_url_versioned($path), $path);
            $this->assertNotSame(config('Gelita')->assetVersion, asset_file_version($path), $path . ' has a file hash');
        }

        $this->assertStringContainsString('ui/status-done.webp?v=', (string) status_badge_src(['status' => 'completed']));

        // No view builds an assets/ui picture URL without the version any more
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator(APPPATH . 'Views', FilesystemIterator::SKIP_DOTS)) as $file) {
            $this->assertStringNotContainsString("game_asset_url('ui/", (string) file_get_contents((string) $file), (string) $file);
        }
    }

    public function testMissingFileKeepsTheAssetVersion(): void
    {
        $this->assertSame(config('Gelita')->assetVersion, asset_file_version('uploads-test/missing.js'));
    }
}
