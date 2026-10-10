<?php

use App\Libraries\MediaStore;
use App\Services\AssetPackageBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\PackageWorld;

/**
 * Media helpers and the asset package (docs/rencana-paket-aset.md §5.2, §6.3):
 * media_src(), media_key_src(), media_key_src_locale(), and audio_src() return
 * package URLs while the active release names a built package, and the normal
 * `?v=` URLs without one, with the `.env` kill switch, or for assets added
 * after the build.
 *
 * @internal
 */
final class AssetPackageHelperTest extends CIUnitTestCase
{
    use PackageWorld;

    /** @var array<string, string> key => package URL */
    private array $urls = [];

    private string $code;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->createPackageWorld();

        $summary    = (new AssetPackageBuilder($this->packages))->build(['copy_library' => false]);
        $this->code = $summary['package_code'];

        foreach ($this->packages->manifest($this->code)['files'] as $file) {
            foreach (array_merge([$file['key']], $file['aliases'] ?? []) as $key) {
                $this->urls[$key] = $file['url'];
            }
        }
    }

    protected function tearDown(): void
    {
        $this->dropPackageWorld();
        parent::tearDown();
    }

    public function testWithoutPackageHelpersReturnNormalUrls(): void
    {
        // Built, but the release still says asset_version '1'
        $this->assertNull($this->packages->activeCode());
        $this->assertSame($this->normal('bg.welcome'), media_src($this->media['bg.welcome']));
        $this->assertSame($this->normal('bg.welcome'), media_key_src('bg.welcome'));
        $this->assertSame($this->normal('ui.btn-start.en'), media_key_src_locale('ui.btn-start', 'en'));
        $this->assertSame($this->normal('audio.narasi.id.intro-01'), audio_src($this->audioId('audio.narasi.id.intro-01')));
    }

    public function testActivePackageSwitchesEveryHelperToPackageUrls(): void
    {
        $this->useRelease($this->code);

        $this->assertSame($this->code, $this->packages->activeCode());
        $this->assertSame(base_url($this->urls['bg.welcome']), media_src($this->media['bg.welcome']));
        $this->assertStringStartsWith(base_url('assets/p/'), media_src($this->media['bg.welcome']));
        $this->assertSame(base_url($this->urls['bg.auth']), media_key_src('bg.auth'));
        $this->assertSame(base_url($this->urls['ui.btn-start.en']), media_key_src_locale('ui.btn-start', 'en'));
        $this->assertSame(base_url($this->urls['ui.btn-start']), media_key_src_locale('ui.btn-start', 'id'));
        $this->assertSame(base_url($this->urls['char.jaka.idle.1']), character_frame_src('jaka', 'happy'));
        $this->assertSame(base_url($this->urls['audio.narasi.en.intro-01']), audio_src($this->audioId('audio.narasi.en.intro-01')));

        // Unknown media and draft narration behave as before
        $this->assertSame(base_url('assets/ui/placeholder.svg'), media_src(99999));
        $this->assertNull(media_key_src('bg.tidak-ada'));
        $this->assertNull(audio_src($this->audioId('audio.narasi.id.kenal-magelang-01')));
    }

    public function testAssetAddedAfterTheBuildKeepsItsNormalUrl(): void
    {
        $this->useRelease($this->code);
        $id = $this->addMedia('bg.reflection', 'assets/uploads/bg.reflection.jpg', 'image', 'image/jpeg', 'reflection');
        service('contentRepository')->flush();
        service('cache')->clean();

        $this->assertSame($this->normal('bg.reflection'), media_src($id));
        $this->assertSame($this->normal('bg.reflection'), media_key_src('bg.reflection'));
    }

    public function testServerKillSwitchOverridesTheActivePackage(): void
    {
        $this->useRelease($this->code);
        config('Gelita')->paket['aktif'] = false;

        $this->assertFalse($this->packages->enabled());
        $this->assertNull($this->packages->activeCode());
        $this->assertSame($this->code, $this->packages->releasePackageCode());
        $this->assertSame($this->normal('bg.welcome'), media_src($this->media['bg.welcome']));
        $this->assertSame($this->normal('bg.welcome'), media_key_src('bg.welcome'));
        $this->assertSame($this->normal('audio.narasi.id.intro-01'), audio_src($this->audioId('audio.narasi.id.intro-01')));
    }

    public function testReleaseNamingAnUnknownOrUnsafeCodeMeansNoPackage(): void
    {
        foreach (['pkt-2099-01-01-01', '../../etc/passwd', 'PKT UPPER'] as $value) {
            $this->useRelease($value);
            $this->assertNull($this->packages->activeCode(), $value);
            $this->assertSame($this->normal('bg.welcome'), media_src($this->media['bg.welcome']), $value);
        }
    }

    private function normal(string $key): string
    {
        $row = $this->conn->table('media_assets')->where('asset_key', $key)->get()->getRowArray();

        return base_url(MediaStore::versionedPath((string) $row['storage_path'], $row['sha256']));
    }

    private function audioId(string $key): int
    {
        return (int) $this->conn->table('audio_assets')->where('media_asset_id', $this->media[$key])->get()->getRow('id');
    }
}
