<?php

use App\Entities\LibraryMedia;
use App\Libraries\CommonsImage;
use App\Libraries\MediaStore;
use App\Models\AssetPackageModel;
use App\Models\LibraryMediaModel;
use App\Services\AssetPackageBuilder;
use App\Services\AssetPackageMaintenance;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\PackageWorld;

/**
 * Asset package builder, Stage 1 (docs/rencana-paket-aset.md §5, §6.3, §11.3):
 * manifest per §5.3, sha256 and size per file, content-addressed URLs, the
 * same file keeping its URL across packages, warnings, groups per §5.1, the
 * batch API, the changed-asset count, the Commons copy (P4a), and the
 * check/clean-up of built packages.
 *
 * @internal
 */
final class AssetPackageBuilderTest extends CIUnitTestCase
{
    use PackageWorld;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->createPackageWorld();
    }

    protected function tearDown(): void
    {
        $this->dropPackageWorld();
        parent::tearDown();
    }

    public function testManifestFollowsTheFormatAndHoldsActiveMediaApprovedNarrationAndCode(): void
    {
        $summary  = $this->build();
        $manifest = $this->packages->manifest($summary['package_code']);

        $this->assertMatchesRegularExpression('/^pkt-\d{4}-\d{2}-\d{2}-01$/', $summary['package_code']);
        $this->assertSame(
            ['format', 'package_code', 'release_code', 'app_version', 'code_version', 'code_dir', 'content_version', 'created_at', 'total_bytes', 'file_count', 'groups', 'files'],
            array_keys($manifest),
        );
        $this->assertSame(1, $manifest['format']);
        $this->assertSame('GELITA-UJI', $manifest['release_code']);
        $this->assertSame('2.0.0', $manifest['app_version']);
        $this->assertSame('kelas5-1', $manifest['content_version']);
        $this->assertMatchesRegularExpression('/^2\.0\.0-[0-9a-f]{8}$/', $manifest['code_version']);
        $this->assertSame('assets/k/' . $manifest['code_version'] . '/', $manifest['code_dir']);
        $this->assertSame(['kode', 'inti', 'wilayah-temanggung', 'wilayah-magelang', 'wilayah-wonosobo', 'penutup'], array_keys($manifest['groups']));
        $this->assertSame(array_sum(array_column($manifest['files'], 'bytes')), $manifest['total_bytes']);
        $this->assertSame(array_sum($manifest['groups']), $manifest['total_bytes']);
        $this->assertSame(count($manifest['files']), $manifest['file_count']);

        $keys = $this->keys($manifest);

        foreach (['bg.welcome', 'ui.btn-start', 'ui.btn-start.en', 'char.jaka.idle.1', 'audio.sfx.click', 'audio.narasi.id.intro-01', 'audio.narasi.en.intro-01', 'challenge.item.mgl-4-a01'] as $key) {
            $this->assertArrayHasKey($key, $keys, $key);
        }

        $this->assertArrayNotHasKey('audio.narasi.id.kenal-magelang-01', $keys, 'draft narration stays out');
        $this->assertSame('en', $keys['audio.narasi.en.intro-01']['locale']);
        $this->assertSame('audio/mpeg', $keys['audio.narasi.en.intro-01']['type']);

        // Code: game files only, in the version folder, with the folder layout kept
        foreach (['css/base.css', 'js/game.js', 'js/core/config.js', 'vendor/howler.min.js', 'fonts/a.woff2', 'offline.html'] as $key) {
            $this->assertSame($manifest['code_dir'] . $key, $keys[$key]['url'], $key);
            $this->assertSame('kode', $keys[$key]['group']);
        }

        $this->assertSame('text/javascript', $keys['js/game.js']['type']);
        $this->assertArrayNotHasKey('css/admin.css', $keys);
        $this->assertArrayNotHasKey('js/admin.js', $keys);
        $this->assertArrayNotHasKey('js/admin/tables.js', $keys);

        // Database row and the files outside public/
        $row = model(AssetPackageModel::class)->findByCode($summary['package_code']);
        $this->assertSame(AssetPackageModel::STATUS_BUILT, $row['status']);
        $this->assertSame(hash_file('sha256', $this->packages->manifestPath($summary['package_code'])), $row['manifest_sha256']);
        $this->assertSame($manifest['total_bytes'], (int) $row['total_bytes']);
        $this->assertFileExists($this->packages->mapPath($summary['package_code']));
        $this->assertFileDoesNotExist($this->packages->statePath($summary['package_code']));
        $this->assertNull($summary['compared_with']);
        $this->assertSame($manifest['file_count'], $summary['new_files']);
    }

    public function testFilesAreHashedSizedAndContentAddressed(): void
    {
        $manifest = $this->packages->manifest($this->build()['package_code']);

        foreach ($manifest['files'] as $file) {
            $path = $this->public . $file['url'];
            $this->assertFileExists($path, $file['url']);
            $this->assertSame(hash_file('sha256', $path), $file['sha256'], $file['url']);
            $this->assertSame(filesize($path), $file['bytes'], $file['url']);

            if ($file['group'] !== 'kode') {
                $extension = pathinfo($file['url'], PATHINFO_EXTENSION);
                $this->assertSame('assets/p/' . substr($file['sha256'], 0, 16) . '.' . $extension, $file['url']);
            }
        }

        // Same content under two keys → one file, the second key as alias
        $keys = $this->keys($manifest);
        $this->assertSame($keys['bg.welcome']['url'], $keys['bg.auth']['url']);
        $this->assertCount(1, array_filter($manifest['files'], static fn (array $f): bool => $f['url'] === $keys['bg.welcome']['url']));
    }

    public function testSecondBuildWithOneChangedImageChangesExactlyOneAssetUrl(): void
    {
        $first = $this->build();
        $this->useRelease($first['package_code']);
        $this->assertSame(0, $this->packages->changedSinceActive()['count']);

        $this->replaceMedia('bg.temanggung.region', 'tmg-bg-final');
        $changed = $this->packages->changedSinceActive();
        $this->assertSame(1, $changed['count']);
        $this->assertSame(['bg.temanggung.region'], $changed['changed']);

        $second = $this->build();
        $this->assertStringEndsWith('-02', $second['package_code']);
        $this->assertSame($first['package_code'], $second['compared_with']);
        $this->assertSame(1, $second['new_files']);
        $this->assertSame(strlen('tmg-bg-final'), $second['new_bytes']);

        $old  = $this->urls($this->packages->manifest($first['package_code']));
        $new  = $this->urls($this->packages->manifest($second['package_code']));
        $diff = array_keys(array_filter($new, static fn (string $url, string $key): bool => ($old[$key] ?? null) !== $url, ARRAY_FILTER_USE_BOTH));
        $this->assertSame(['bg.temanggung.region'], $diff);

        // Unchanged code reuses the same version folder
        $this->assertSame($old['js/game.js'], $new['js/game.js']);

        $this->useRelease($second['package_code']);
        $this->assertSame(0, $this->packages->changedSinceActive()['count']);
    }

    public function testChangedCountSeesNewAndRemovedAssets(): void
    {
        $this->useRelease($this->build()['package_code']);

        $this->addMedia('bg.reflection', 'assets/uploads/bg.reflection.jpg', 'image', 'image/jpeg', 'reflection');
        $this->conn->table('media_assets')->where('asset_key', 'library.candi')->update(['is_active' => 0]);
        // Approving draft narration makes it a new asset for students
        $this->conn->table('audio_assets')->where('approval_status', 'draft')->update(['approval_status' => 'approved']);

        $changed = $this->packages->changedSinceActive();
        $this->assertSame(3, $changed['count']);
        $this->assertSame(['audio.narasi.id.kenal-magelang-01', 'bg.reflection'], $changed['added']);
        $this->assertSame(['library.candi'], $changed['removed']);
    }

    public function testMockupsDraftNarrationMissingFilesAndOversizedFilesWarn(): void
    {
        config('Gelita')->paket['budgets'] = ['ui.btn-start' => 3] + config('Gelita')->paket['budgets'];
        unlink($this->public . 'assets/uploads/library.candi.jpg');

        $warnings = $this->build()['warnings'];
        $byCode   = [];

        foreach ($warnings as $warning) {
            $byCode[$warning['code']][] = $warning['key'];
        }

        $this->assertSame(['challenge.item.mgl-4-a01'], $byCode['placeholder']);
        $this->assertSame(['audio.narasi.id.kenal-magelang-01'], $byCode['narration_not_approved']);
        $this->assertSame(['library.candi'], $byCode['file_missing']);
        $this->assertSame(['ui.btn-start'], $byCode['over_budget']);
    }

    public function testGroupsFollowThePlan(): void
    {
        $keys = $this->keys($this->packages->manifest($this->build()['package_code']));

        $expected = [
            'bg.welcome'                         => 'inti',
            'ui.btn-start.en'                    => 'inti',
            'char.jaka.idle.1'                   => 'inti',
            'audio.sfx.click'                    => 'inti',
            'audio.narasi.id.intro-01'           => 'inti',
            'bg.temanggung.region'               => 'wilayah-temanggung',
            'audio.narasi.id.petunjuk-tmg-4-01'  => 'wilayah-temanggung',
            'challenge.item.mgl-4-a01'           => 'wilayah-magelang',
            'library.candi'                      => 'wilayah-magelang',
            'audio.narasi.id.dialog-wonosobo-03' => 'wilayah-wonosobo',
            'audio.narasi.id.penutup-01'         => 'penutup',
            'audio.music.challenge'              => 'penutup',
        ];

        foreach ($expected as $key => $group) {
            $this->assertSame($group, $keys[$key]['group'], $key);
        }

        $this->assertSame('inti', AssetPackageBuilder::groupFor('map.kedu'));
        $this->assertSame('inti', AssetPackageBuilder::groupFor('audio.music.map'));
        // Background music (owner, 19 October 2026): home/account and opening tracks with the core, region tracks with their region
        $this->assertSame('inti', AssetPackageBuilder::groupFor('audio.music.welcome'));
        $this->assertSame('inti', AssetPackageBuilder::groupFor('audio.music.opening'));
        $this->assertSame('wilayah-temanggung', AssetPackageBuilder::groupFor('audio.music.kenal-temanggung'));
        $this->assertSame('wilayah-magelang', AssetPackageBuilder::groupFor('audio.music.dialog-magelang'));
        $this->assertSame('wilayah-wonosobo', AssetPackageBuilder::groupFor('audio.music.tuntas-wonosobo'));
        $this->assertSame('penutup', AssetPackageBuilder::groupFor('audio.music.ending'));
        $this->assertSame('wilayah-wonosobo', AssetPackageBuilder::groupFor('map.region.wonosobo'));
        $this->assertSame('wilayah-magelang', AssetPackageBuilder::groupFor('reward.badge.magelang'));
        $this->assertSame('wilayah-temanggung', AssetPackageBuilder::groupFor('challenge.tmg-1.scene'));
        $this->assertSame('wilayah-magelang', AssetPackageBuilder::groupFor('audio.narasi.en.tuntas-magelang-02'));
        $this->assertSame('wilayah-wonosobo', AssetPackageBuilder::groupFor('bg.dieng', 'wonosobo'));
        $this->assertSame('inti', AssetPackageBuilder::groupFor('bg.dieng'));
    }

    public function testBatchBuildReportsProgressAndRefusesToFinishEarly(): void
    {
        $builder  = new AssetPackageBuilder($this->packages);
        $progress = $builder->start(['copy_library' => false, 'notes' => 'uji batch']);
        $code     = $progress['package_code'];

        $this->assertSame(0, $progress['done']);
        $this->assertFalse($progress['finished']);
        $this->assertSame(AssetPackageModel::STATUS_BUILDING, model(AssetPackageModel::class)->findByCode($code)['status']);

        $progress = $builder->step($code, 3);
        $this->assertSame(3, $progress['done']);

        try {
            $builder->finish($code);
            $this->fail('finish() before the last batch must be refused');
        } catch (RuntimeException) {
            // expected
        }

        $steps = 1;

        while (! $progress['finished']) {
            $progress = $builder->step($code, 3);
            $steps++;
        }

        $this->assertSame($progress['total'], $progress['done']);
        $this->assertGreaterThan(3, $steps);

        $summary = $builder->finish($code);
        $this->assertSame('uji batch', model(AssetPackageModel::class)->findByCode($code)['notes']);
        $this->assertSame($summary['file_count'], $this->packages->manifest($code)['file_count']);
        $this->assertSame([], (new AssetPackageMaintenance($this->packages))->verify($code));
    }

    public function testCommonsLibraryImagesAreCopiedLocallyWithTheirCredit(): void
    {
        $this->conn->table('library_media')->insert([
            'library_page_id' => 1, 'sequence' => 2, 'media_kind' => 'image',
            'external_url'    => 'https://commons.wikimedia.org/wiki/File:Candi_Mendut.jpg',
            'credit'          => 'Foto: Gunawan Kartapranata, CC BY-SA 3.0',
        ]);
        $libraryId = (int) $this->conn->insertID();
        $jpeg      = $this->jpeg();
        $commons   = $this->commonsStub($jpeg);

        $summary = (new AssetPackageBuilder($this->packages, $commons))->build(['copy_library' => true]);

        $row = model(LibraryMediaModel::class)->find($libraryId);
        $this->assertNotNull($row->media_asset_id);
        $this->assertSame('https://commons.wikimedia.org/wiki/File:Candi_Mendut.jpg', $row->external_url);
        $this->assertSame(['https://commons.wikimedia.org/wiki/Special:FilePath/Candi_Mendut.jpg?width=1280'], $commons->requested);

        $media = $this->conn->table('media_assets')->where('id', $row->media_asset_id)->get()->getRowArray();
        $this->assertSame(CommonsImage::assetKey('https://commons.wikimedia.org/wiki/File:Candi_Mendut.jpg'), $media['asset_key']);
        $this->assertStringStartsWith('library.commons.candi-mendut-', strtolower($media['asset_key']));
        $this->assertSame('Foto: Gunawan Kartapranata, CC BY-SA 3.0', $media['credit']);

        $keys = $this->keys($this->packages->manifest($summary['package_code']));
        $this->assertSame('wilayah-magelang', $keys[$media['asset_key']]['group']);
        $this->assertSame(hash('sha256', $jpeg), $keys[$media['asset_key']]['sha256']);

        // Students now load the local copy; the credit still links to Commons
        $this->useRelease($summary['package_code']);
        $resolved = $row->resolve();
        $this->assertSame('commons', $resolved['provider']);
        $this->assertSame(base_url($keys[$media['asset_key']]['url']), $resolved['src']);
        $this->assertSame('https://commons.wikimedia.org/wiki/File:Candi_Mendut.jpg', $resolved['href']);

        // A second build has nothing left to copy
        (new AssetPackageBuilder($this->packages, $commons))->build(['copy_library' => true]);
        $this->assertCount(1, $commons->requested);
    }

    public function testFailedCommonsCopyOnlyWarns(): void
    {
        $this->conn->table('library_media')->insert([
            'library_page_id' => 1, 'sequence' => 2, 'media_kind' => 'image',
            'external_url'    => 'https://commons.wikimedia.org/wiki/File:Candi_Mendut.jpg',
        ]);
        $libraryId = (int) $this->conn->insertID();

        $summary = (new AssetPackageBuilder($this->packages, $this->commonsStub(null)))->build(['copy_library' => true]);

        $this->assertContains('library_copy_failed', array_column($summary['warnings'], 'code'));
        $this->assertNull(model(LibraryMediaModel::class)->find($libraryId)->media_asset_id);
    }

    public function testCheckFindsTamperedFilesAndPruneKeepsWhatPackagesUse(): void
    {
        $first = $this->build();
        $this->useRelease($first['package_code']);
        $this->replaceMedia('bg.temanggung.region', 'tmg-bg-final');
        $second      = $this->build();
        $maintenance = new AssetPackageMaintenance($this->packages);

        $this->assertSame([], $maintenance->verify($first['package_code']));
        $this->assertSame([], $maintenance->verify($second['package_code']));

        // A stray file and the first package's old image are older than an hour
        $oldUrl = $this->urls($this->packages->manifest($first['package_code']))['bg.temanggung.region'];
        file_put_contents($this->public . 'assets/p/ffffffffffffffff.jpg', 'stray');
        mkdir($this->public . 'assets/k/0.9-deadbeef', 0775, true);
        file_put_contents($this->public . 'assets/k/0.9-deadbeef/game.js', 'old');

        foreach (glob($this->public . 'assets/p/*') as $file) {
            touch($file, time() - 7200);
        }

        // The release still names the first package, so it cannot be removed
        $refused = $maintenance->prune([$first['package_code']]);
        $this->assertSame([$first['package_code']], $refused['refused']);
        $this->assertSame(1, $refused['media_files']);
        $this->assertSame(['0.9-deadbeef'], $refused['code_dirs']);
        $this->assertFileDoesNotExist($this->public . 'assets/p/ffffffffffffffff.jpg');
        $this->assertFileExists($this->public . $oldUrl);

        // Once the release moves on, the first package and its old image go
        $this->useRelease($second['package_code']);
        $dry = $maintenance->prune([$first['package_code']], true);
        $this->assertSame([$first['package_code']], $dry['removed_packages']);
        $this->assertSame(1, $dry['media_files']);
        $this->assertFileExists($this->public . $oldUrl);

        $maintenance->prune([$first['package_code']]);
        $this->assertFileDoesNotExist($this->public . $oldUrl);
        $this->assertNull(model(AssetPackageModel::class)->findByCode($first['package_code']));
        $this->assertSame([], $maintenance->verify($second['package_code']));

        // Tampering is detected
        $url = $this->urls($this->packages->manifest($second['package_code']))['ui.btn-start'];
        file_put_contents($this->public . $url, 'xtart-id');
        $issues = $maintenance->verify($second['package_code']);
        $this->assertCount(1, $issues);
        $this->assertSame('ui.btn-start', $issues[0]['key']);
        $this->assertStringContainsString('sha256', $issues[0]['issue']);
    }

    // ------------------------------------------------------------ helpers

    /** @return array<string, mixed> */
    private function build(): array
    {
        return (new AssetPackageBuilder($this->packages))->build(['copy_library' => false]);
    }

    /**
     * Manifest entries by key, aliases included.
     *
     * @return array<string, array<string, mixed>>
     */
    private function keys(array $manifest): array
    {
        $out = [];

        foreach ($manifest['files'] as $file) {
            foreach (array_merge([$file['key']], $file['aliases'] ?? []) as $key) {
                $out[$key] = $file;
            }
        }

        return $out;
    }

    /** @return array<string, string> key => url */
    private function urls(array $manifest): array
    {
        return array_map(static fn (array $file): string => $file['url'], $this->keys($manifest));
    }

    private function jpeg(): string
    {
        $image = imagecreatetruecolor(4, 3);
        ob_start();
        imagejpeg($image);

        return (string) ob_get_clean();
    }

    /**
     * CommonsImage without network: download() answers with $bytes (null =
     * failure), and the copy is written into the test's public folder.
     */
    private function commonsStub(?string $bytes): CommonsImage
    {
        $public = $this->public;
        $store  = new class ($public) extends MediaStore {
            public function __construct(private string $public)
            {
            }

            public function storeBytes(string $bytes, string $assetKey, ?int $staffId = null, string $via = 'download'): array
            {
                $path = 'assets/uploads/' . $assetKey . '.jpg';
                file_put_contents($this->public . $path, $bytes);
                $id = model(\App\Models\MediaAssetModel::class)->insert([
                    'asset_key' => $assetKey, 'asset_type' => 'image', 'storage_path' => $path, 'mime_type' => 'image/jpeg',
                    'file_size' => strlen($bytes), 'sha256' => hash('sha256', $bytes), 'is_active' => 1,
                ], true);

                return ['id' => (int) $id, 'type' => 'image', 'error' => null];
            }
        };

        return new class ($bytes, $store) extends CommonsImage {
            /** @var list<string> */
            public array $requested = [];

            public function __construct(private ?string $bytes, MediaStore $store)
            {
                parent::__construct(null, $store);
            }

            protected function download(string $url): array
            {
                $this->requested[] = $url;

                return $this->bytes === null ? ['body' => null, 'error' => 'gagal diunduh'] : ['body' => $this->bytes, 'error' => null];
            }
        };
    }
}
