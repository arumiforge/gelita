<?php

use App\Libraries\CatalogImporter;
use App\Libraries\MediaStore;
use App\Models\MediaAssetModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * `gelita:katalog:impor` (CatalogImporter): images of a small fake Katalog
 * Aset land in their slots, story slides and library pages are linked, app
 * icons become PNG, measured region maps move the challenge buttons, and a
 * second run or an admin upload is left alone unless overwriting.
 *
 * @internal
 */
final class CatalogImportTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $sqlite;

    private string $catalog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite      = $this->createGradeFiveWorld();
        $this->catalog = sys_get_temp_dir() . '/gelita-katalog-' . bin2hex(random_bytes(4));
        mkdir($this->catalog . '/assets', 0777, true);

        $levelId = (int) $this->sqlite->table('levels')->where('code', 'temanggung')->get()->getRow('id');
        $this->sqlite->table('dialogues')->insert(['level_id' => null, 'context_code' => 'intro', 'sequence' => 1, 'character_code' => 'narator', 'text_id' => 'a', 'text_en' => 'a']);
        $this->sqlite->table('library_pages')->insertBatch([
            ['level_id' => $levelId, 'sequence' => 1, 'title_id' => 'Selamat Datang', 'title_en' => 'Welcome', 'body_id' => 'x', 'body_en' => 'x'],
            ['level_id' => $levelId, 'sequence' => 7, 'title_id' => 'Parakan', 'title_en' => 'Parakan', 'body_id' => 'x', 'body_en' => 'x'],
            ['level_id' => $levelId, 'sequence' => 8, 'title_id' => 'Kuda Lumping', 'title_en' => 'Kuda Lumping', 'body_id' => 'x', 'body_en' => 'x'],
        ]);
        $pages = array_column($this->sqlite->table('library_pages')->get()->getResultArray(), 'id', 'sequence');
        $this->sqlite->table('library_media')->insert(['library_page_id' => $pages[1], 'sequence' => 1, 'media_kind' => 'image', 'external_url' => 'https://commons.wikimedia.org/wiki/File:Posong.jpg', 'caption_id' => 'Posong', 'credit' => 'Commons']);
        $this->sqlite->table('library_media')->insert(['library_page_id' => $pages[8], 'sequence' => 2, 'media_kind' => 'video', 'external_url' => 'https://www.youtube.com/watch?v=IXDk03NgHqQ']);

        $this->writeCatalog([
            'ui.logo-hero'                    => ['logo/ui.logo-hero.webp', 1600, 600, true],
            'app.icon-192.png'                => ['ikon/app.icon-192.webp', 192, 192, true],
            'app.icon-maskable-512.png'       => ['ikon/app.icon-maskable-512.webp', 512, 512, true],
            'app.favicon.ico'                 => ['ikon/app.favicon.webp', 32, 32, true],
            'bg.cerita-intro-01'              => ['latar/bg.cerita-intro-01.webp', 1920, 1080, false],
            'map.region.temanggung'           => ['peta/map.region.temanggung.webp', 1400, 900, false],
            'char.jaka.idle.2'                => ['karakter/char.jaka.idle.2.webp', 700, 900, true],
            'challenge.item.tmg-1-01'         => ['tantangan/challenge.item.tmg-1-01.webp', 1200, 1200, false],
            'library.temanggung.p1.1'         => ['pustaka/library.temanggung.p1.1.webp', 960, 640, false],
            'library.temanggung.p7.1'         => ['pustaka/library.temanggung.p7.1.webp', 960, 640, false],
            'library.temanggung.p8.2'         => ['pustaka/library.temanggung.p8.2.webp', 960, 640, false],
            'library.temanggung.p8.2.poster'  => ['pustaka/library.temanggung.p8.2.poster.webp', 1280, 720, false],
            'reference.01'                    => ['referensi/reference.01.webp', 64, 64, false],
            'icon.home'                       => ['ikon/icon.home.webp', 24, 24, true],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (model(MediaAssetModel::class)->like('storage_path', MediaStore::UPLOAD_DIR, 'after')->findAll() as $row) {
            if (is_file(FCPATH . $row['storage_path'])) {
                unlink(FCPATH . $row['storage_path']);
            }
        }

        $this->removeDir($this->catalog);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testDryRunChangesNothing(): void
    {
        $report = $this->importer()->run(true);

        $this->assertContains('planned', array_column($report, 'status'));
        $this->assertNotContains('installed', array_column($report, 'status'));
        $this->assertNull(model(MediaAssetModel::class)->findByKey('bg.story.intro.1'));
        $this->assertEqualsWithDelta(18.0, (float) $this->node('temanggung', 1)['map_x'], 0.001);
    }

    public function testInstallsSlotsAndLinksContent(): void
    {
        $report = $this->importer()->run();
        $byKey  = static fn (string $key): ?array => model(MediaAssetModel::class)->findByKey($key);

        $this->assertNotContains('failed', array_column($report, 'status'), json_encode($report));
        $this->assertNotContains('reference.01', array_column($report, 'source'), 'references are not slots');
        $this->assertNotContains('icon.home', array_column($report, 'source'), 'vector icons are not slots');

        $logo = $byKey('ui.logo-hero');
        $this->assertSame(CatalogImporter::CREDIT, $logo['credit']);
        $this->assertSame(1, (int) $logo['is_active']);
        $this->assertStringStartsWith(MediaStore::UPLOAD_DIR, $logo['storage_path']);
        $this->assertFileExists(FCPATH . $logo['storage_path']);
        $this->assertSame('image/webp', $logo['mime_type'], 'stored as lossy WebP');

        // App icons are PNG (the app.* slots take PNG only); favicon made from the 192 icon
        $favicon = $byKey('app.favicon');
        $this->assertSame('image/png', $favicon['mime_type']);
        $this->assertSame([48, 48], [(int) $favicon['width_px'], (int) $favicon['height_px']]);
        $maskable = imagecreatefrompng(FCPATH . $byKey('app.icon-maskable')['storage_path']);
        $this->assertSame(0, (imagecolorat($maskable, 0, 0) >> 24) & 0x7F, 'maskable icon has no transparency');

        // Character frame 2 gets its own sprite_frame row
        $this->assertSame('sprite_frame', $byKey('char.jaka.idle.2')['asset_type']);

        // Challenge image fills the bank slot
        $this->assertSame(1, (int) $byKey('challenge.item.tmg-1-01')['is_active']);

        // Story slide background, linked to its slide
        $story = $byKey('bg.story.intro.1');
        $this->assertSame((int) $story['id'], (int) $this->sqlite->table('dialogues')->where('context_code', 'intro')->get()->getRow('background_media_id'));

        // Library: Commons link replaced, new media for a page without one, video keeps its link and gets a cover
        $media = $this->sqlite->table('library_media lm')->select('lm.*, lp.sequence AS page')
            ->join('library_pages lp', 'lp.id = lm.library_page_id')->get()->getResultArray();
        $pageMedia = array_column($media, null, 'page');
        $this->assertSame((int) $byKey('library.temanggung.p1.1')['id'], (int) $pageMedia[1]['media_asset_id']);
        $this->assertNull($pageMedia[1]['external_url']);
        $this->assertSame('Posong', $pageMedia[1]['caption_id'], 'caption is kept');
        $this->assertSame(CatalogImporter::LIBRARY_CREDIT, $pageMedia[1]['credit']);
        $this->assertSame((int) $byKey('library.temanggung.p7.1')['id'], (int) $pageMedia[7]['media_asset_id']);
        $this->assertSame('video', $pageMedia[8]['media_kind']);
        $this->assertSame('https://www.youtube.com/watch?v=IXDk03NgHqQ', $pageMedia[8]['external_url']);
        $this->assertSame((int) $byKey('library.temanggung.p8.2.poster')['id'], (int) $pageMedia[8]['poster_media_id']);
        $this->assertNull($byKey('library.temanggung.p8.2'), 'the picture of a video entry is not used');
    }

    public function testMeasuredMapMovesChallengeButtons(): void
    {
        $sha  = hash_file('sha256', $this->catalog . '/assets/peta/map.region.temanggung.webp');
        $stop = [1 => [29.0, 35.0], 2 => [82.0, 46.0], 3 => [35.0, 65.0], 4 => [80.0, 72.0]];

        (new CatalogImporter($this->catalog, null, [$sha => ['temanggung', $stop]]))->run();

        foreach ($stop as $sequence => [$x, $y]) {
            $node = $this->node('temanggung', $sequence);
            $this->assertEqualsWithDelta($x, (float) $node['map_x'], 0.001);
            $this->assertEqualsWithDelta($y, (float) $node['map_y'], 0.001);
        }

        $this->assertEqualsWithDelta(18.0, (float) $this->node('magelang', 1)['map_x'], 0.001, 'other regions untouched');
    }

    public function testMeasuredStopsMatchTheCatalogueMaps(): void
    {
        foreach (CatalogImporter::MAP_STOPS as $sha => [$region, $stops]) {
            $file = ROOTPATH . 'gelita-katalog/assets/peta/map.region.' . $region . '.webp';

            $this->assertFileExists($file);
            $this->assertSame($sha, hash_file('sha256', $file), "map.region.{$region} changed: measure its clearings again");
            $this->assertSame([1, 2, 3, 4], array_keys($stops));
        }
    }

    /** A new catalogue edition (e.g. the 24-frame poses) replaces earlier catalogue images, not admin uploads. */
    public function testChangedCatalogueFileIsReinstalled(): void
    {
        $this->importer()->run();
        $before = model(MediaAssetModel::class)->findByKey('char.jaka.idle.2');

        // Redraw the frame in the catalogue
        $file  = $this->catalog . '/assets/karakter/char.jaka.idle.2.webp';
        $image = imagecreatetruecolor(700, 900);
        imagesavealpha($image, true);
        imagealphablending($image, false);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        imagefilledrectangle($image, 300, 100, 400, 876, imagecolorallocate($image, 200, 40, 40));
        imagewebp($image, $file, 90);

        $dry = array_values(array_filter($this->importer()->run(true), static fn (array $r): bool => $r['target'] === 'char.jaka.idle.2'));
        $this->assertSame('planned', $dry[0]['status']);
        $this->assertStringContainsString('berubah', $dry[0]['note']);

        $rows = $this->importer()->run();
        $byTarget = array_column($rows, null, 'target');
        $this->assertSame('installed', $byTarget['char.jaka.idle.2']['status']);
        $this->assertSame('skipped', $byTarget['ui.logo-hero']['status'], 'unchanged catalogue images stay');
        $this->assertStringContainsString('gambarnya sama', $byTarget['ui.logo-hero']['note']);

        $after = model(MediaAssetModel::class)->findByKey('char.jaka.idle.2');
        $this->assertSame((int) $before['id'], (int) $after['id'], 'same slot row, new file');
        $this->assertNotSame($before['sha256'], $after['sha256']);
        $this->assertSame(CatalogImporter::CREDIT, $after['credit']);
    }

    public function testSecondRunAndAdminUploadsAreKept(): void
    {
        $this->importer()->run();
        $report = $this->importer()->run();

        $this->assertNotContains('installed', array_column($report, 'status'), 'second run changes nothing');

        // An admin upload under the same key is kept, unless overwriting
        model(MediaAssetModel::class)->where('asset_key', 'ui.logo-hero')->set(['credit' => null])->update();
        $skipped = array_values(array_filter($this->importer()->run(), static fn (array $r): bool => $r['target'] === 'ui.logo-hero'));
        $this->assertSame('skipped', $skipped[0]['status']);
        $this->assertStringContainsString('unggahan admin', $skipped[0]['note']);

        $forced = array_values(array_filter($this->importer()->run(false, true), static fn (array $r): bool => $r['target'] === 'ui.logo-hero'));
        $this->assertSame('installed', $forced[0]['status']);
    }

    public function testAdminUploadClearsTheCatalogueCredit(): void
    {
        $this->importer()->run();

        $source = $this->catalog . '/assets/logo/ui.logo-hero.webp';
        $result = (new MediaStore())->storeCopy($source, 'ui.logo-hero', ['image'], null, true);

        $this->assertNull($result['error']);
        $this->assertNull(model(MediaAssetModel::class)->findByKey('ui.logo-hero')['credit']);
    }

    public function testMissingCatalogueIsAnError(): void
    {
        $this->expectException(RuntimeException::class);
        (new CatalogImporter($this->catalog . '/tidak-ada'))->run();
    }

    private function importer(): CatalogImporter
    {
        return new CatalogImporter($this->catalog, null, []);
    }

    /** @return array<string, mixed> */
    private function node(string $region, int $sequence): array
    {
        return $this->sqlite->table('challenge_nodes n')->select('n.map_x, n.map_y')
            ->join('levels l', 'l.id = n.level_id')
            ->where('l.code', $region)->where('n.sequence', $sequence)
            ->get()->getRowArray();
    }

    /** @param array<string, array{0: string, 1: int, 2: int, 3: bool}> $files */
    private function writeCatalog(array $files): void
    {
        $data = [];

        foreach ($files as $key => [$path, $width, $height, $alpha]) {
            $absolute = $this->catalog . '/assets/' . $path;

            if (! is_dir(dirname($absolute))) {
                mkdir(dirname($absolute), 0777, true);
            }

            $image = imagecreatetruecolor($width, $height);
            imagesavealpha($image, $alpha);
            imagealphablending($image, false);
            imagefill($image, 0, 0, $alpha ? imagecolorallocatealpha($image, 0, 0, 0, 127) : imagecolorallocate($image, 30, 40, 70));
            imagefilledellipse($image, intdiv($width, 2), intdiv($height, 2), intdiv($width, 2), intdiv($height, 2), imagecolorallocate($image, 223, 192, 135));
            imagewebp($image, $absolute, 90);

            $data[] = ['id' => (string) count($data), 'key' => $key, 'file' => 'assets/' . $path, 'size' => $width . ' × ' . $height, 'cat' => 'Uji'];
        }

        $data[] = ['id' => 'audio', 'key' => 'audio.music.map', 'file' => 'assets/audio/map.mp3', 'size' => 'Audio', 'cat' => 'Musik'];
        file_put_contents($this->catalog . '/data.json', json_encode($data));
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($dir);
    }
}
