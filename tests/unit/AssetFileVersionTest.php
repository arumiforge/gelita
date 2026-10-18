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
        helper('gelita');
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

    public function testMissingFileKeepsTheAssetVersion(): void
    {
        $this->assertSame(config('Gelita')->assetVersion, asset_file_version('uploads-test/missing.js'));
    }
}
