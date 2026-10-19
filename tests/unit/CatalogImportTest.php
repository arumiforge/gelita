<?php

use App\Libraries\CatalogImporter;
use App\Libraries\MediaStore;
use App\Libraries\MockupGenerator;
use App\Models\MediaAssetModel;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;
use Tests\Support\RealUploadsGuard;

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
    use RealUploadsGuard;

    private BaseConnection $sqlite;

    private string $catalog;

    protected function setUp(): void
    {
        parent::setUp();

        // The importer stores files under these official names; the copies in
        // the real upload folder must survive every test of this class
        $this->guardRealUploads(['ui.logo-hero.webp', 'app.icon-192.png', 'app.favicon.png', 'char.jaka.idle.1.webp', 'bg.story.intro.1.webp']);

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
            'ui.btn-start'                    => ['antarmuka/ui.btn-start.webp', 720, 240, true],
            'char.jaka.idle.1'                => ['karakter/char.jaka.idle.1.webp', 700, 900, true],
            'char.jaka.idle.2'                => ['karakter/char.jaka.idle.2.webp', 700, 900, true],
            'challenge.item.tmg-1-01'         => ['tantangan/challenge.item.tmg-1-01.webp', 1200, 1200, false],
            'library.temanggung.p1.1'         => ['pustaka/library.temanggung.p1.1.webp', 960, 640, false],
            'library.temanggung.p7.1'         => ['pustaka/library.temanggung.p7.1.webp', 960, 640, false],
            'library.temanggung.p8.2'         => ['pustaka/library.temanggung.p8.2.webp', 960, 640, false],
            'library.temanggung.p8.2.poster'  => ['pustaka/library.temanggung.p8.2.poster.webp', 1280, 720, false],
            'reference.01'                    => ['referensi/reference.01.webp', 64, 64, false],
            'reference.19'                    => ['referensi/reference.19.webp', 256, 256, true],
            'reference.20'                    => ['referensi/reference.20.webp', 256, 256, true],
            'icon.language-id'                => ['ikon/icon.language-id.webp', 256, 256, true],
            'icon.language-en'                => ['ikon/icon.language-en.webp', 256, 256, true],
            'icon.home'                       => ['ikon/icon.home.webp', 24, 24, true],
        ]);
    }

    protected function tearDown(): void
    {
        foreach (model(MediaAssetModel::class)->like('storage_path', MediaStore::uploadDir(), 'after')->findAll() as $row) {
            if (is_file(FCPATH . $row['storage_path'])) {
                unlink(FCPATH . $row['storage_path']);
            }
        }

        $this->removeDir($this->catalog);
        $this->dropResetTables();
        parent::tearDown();
        $this->assertRealUploadsUntouched();
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
        $this->assertStringStartsWith(MediaStore::uploadDir(), $logo['storage_path']);
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

    /** Owner, 19 October 2026: region slide backgrounds use the narration words kenal/dialog/tuntas. */
    public function testRegionSlideBackgroundsAreLinkedToTheirSlide(): void
    {
        $levels = array_column($this->sqlite->table('levels')->select('id, code')->get()->getResultArray(), 'id', 'code');

        foreach (['region_intro', 'level_open', 'level_done'] as $context) {
            foreach (['temanggung', 'magelang'] as $code) {
                $this->sqlite->table('dialogues')->insert(['level_id' => $levels[$code], 'context_code' => $context, 'sequence' => 2, 'character_code' => 'jaka', 'text_id' => 'a', 'text_en' => 'a']);
            }
        }

        $this->writeCatalog([
            'bg.cerita-kenal-temanggung-02'  => ['latar/bg.cerita-kenal-temanggung-02.webp', 1920, 1080, false],
            'bg.cerita-dialog-temanggung-02' => ['latar/bg.cerita-dialog-temanggung-02.webp', 1920, 1080, false],
            'bg.cerita-tuntas-temanggung-02' => ['latar/bg.cerita-tuntas-temanggung-02.webp', 1920, 1080, false],
            'bg.cerita-dialog-temanggung-09' => ['latar/bg.cerita-dialog-temanggung-09.webp', 1920, 1080, false],
            'bg.cerita-dialog-atlantis-01'   => ['latar/bg.cerita-dialog-atlantis-01.webp', 1920, 1080, false],
        ]);

        $this->importer()->run(true);
        $this->assertNull(model(MediaAssetModel::class)->findByKey('bg.story.level_open.temanggung.2'), 'dry run');

        $report = array_column($this->importer()->run(), null, 'source');

        foreach (['region_intro' => 'kenal', 'level_open' => 'dialog', 'level_done' => 'tuntas'] as $context => $word) {
            $media = model(MediaAssetModel::class)->findByKey("bg.story.{$context}.temanggung.2");
            $this->assertNotNull($media, $context);
            $this->assertSame('installed', $report["bg.cerita-{$word}-temanggung-02"]['status']);

            $linked = fn (string $code): ?string => $this->sqlite->table('dialogues')
                ->where(['level_id' => $levels[$code], 'context_code' => $context, 'sequence' => 2])->get()->getRow('background_media_id');
            $this->assertSame((int) $media['id'], (int) $linked('temanggung'), $context);
            $this->assertNull($linked('magelang'), 'the other region keeps its own slide');
        }

        // A slide the script does not have, or an unknown region: skipped, nothing stored
        $this->assertSame('skipped', $report['bg.cerita-dialog-temanggung-09']['status']);
        $this->assertSame('skipped', $report['bg.cerita-dialog-atlantis-01']['status']);
        $this->assertNull(model(MediaAssetModel::class)->findByKey('bg.story.level_open.temanggung.9'));
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

    public function testMeasuredSceneMovesObjectsOntoItsTables(): void
    {
        $this->writeCatalog(['challenge.tmg-4.scene' => ['tantangan/challenge.tmg-4.scene.webp', 1280, 720, false]]);
        $sha     = hash_file('sha256', $this->catalog . '/assets/tantangan/challenge.tmg-4.scene.webp');
        $objects = [
            'challenge.item.tmg-4-02' => [[44.0, 6.0, 12.0], [14.0, 31.0, 10.0]],
            'challenge.item.tmg-4-03' => [[84.0, 6.0, 12.0], [76.0, 31.0, 10.0]],
            'challenge.item.tmg-4-11' => [[64.0, 6.0, 12.0], [45.0, 31.0, 10.0]],
        ];
        $this->setObject('challenge.item.tmg-4-02', [44.0, 6.0, 12.0]);  // old bank position
        $this->setObject('challenge.item.tmg-4-03', [50.0, 50.0, 9.0]);  // placed by an admin
        $this->setObject('challenge.item.tmg-4-11', [45.0, 31.0, 10.0]); // already on the table

        $importer = new CatalogImporter($this->catalog, null, [], [$sha => ['challenge.tmg-4.scene', $objects]]);
        $notes    = static fn (array $report): array => array_column(array_filter($report, static fn (array $r): bool => str_starts_with($r['target'], 'challenge_items')), 'status', 'target');

        $this->assertSame(['planned', 'skipped', 'skipped'], array_values($notes($importer->run(true))));
        $this->assertSame([44.0, 6.0, 12.0], $this->object('challenge.item.tmg-4-02'), 'a dry run changes nothing');

        $importer->run();
        $this->assertSame([14.0, 31.0, 10.0], $this->object('challenge.item.tmg-4-02'));
        $this->assertSame([50.0, 50.0, 9.0], $this->object('challenge.item.tmg-4-03'), "an admin's placement is kept");
        $this->assertSame([45.0, 31.0, 10.0], $this->object('challenge.item.tmg-4-11'));

        $importer->run(false, true);
        $this->assertSame([76.0, 31.0, 10.0], $this->object('challenge.item.tmg-4-03'), '--timpa moves it too');
    }

    /** The measured positions belong to the catalogue scenes, and the question bank carries the same ones. */
    public function testMeasuredScenesMatchTheCatalogueAndTheBank(): void
    {
        foreach (CatalogImporter::SCENE_OBJECTS as $sha => [$scene, $objects]) {
            $file = ROOTPATH . 'gelita-katalog/assets/tantangan/' . $scene . '.webp';

            $this->assertFileExists($file);
            $this->assertSame($sha, hash_file('sha256', $file), "{$scene} changed: measure its tables again");

            foreach ($objects as $key => [, $to]) {
                $this->assertSame($to, $this->object($key), "{$key} in the bank differs from SCENE_OBJECTS");
            }
        }

        $this->assertSame(8 + 10 + 12, array_sum(array_map(static fn (array $s): int => count($s[1]), CatalogImporter::SCENE_OBJECTS)));
    }

    /**
     * Every Susun Gambar slot of the bank (variants a–j, 30) has a square
     * catalogue image of at least 1200 px, and the importer would install it.
     */
    public function testCatalogueHasEveryPuzzleImageOfTheBank(): void
    {
        $slots = array_values(array_filter((new MockupGenerator())->slots(), static fn (array $s): bool => $s['kind'] === 'puzzle'));
        $keys  = array_column($slots, 'asset_key');
        sort($keys);

        $catalog = new CatalogImporter(ROOTPATH . 'gelita-katalog');
        $files   = array_column(array_filter($catalog->entries(), static fn (array $e): bool => in_array($e['key'], $keys, true)), 'file', 'key');
        ksort($files);

        $this->assertCount(30, $keys);
        $this->assertSame($keys, array_keys($files), 'puzzle slots without a catalogue entry');

        foreach ($files as $key => $file) {
            $this->assertFileExists($file);
            [$width, $height] = getimagesize($file) ?: [0, 0];
            $this->assertSame($width, $height, "{$key} is not square");
            $this->assertGreaterThanOrEqual(1200, $width, "{$key} is smaller than the puzzle minimum");
        }

        $report  = $catalog->run(true, false, null, ['challenge.item.*-1-*']);
        $targets = array_column($report, 'target');
        sort($targets);
        $this->assertSame($keys, $targets);
        $this->assertSame(['planned'], array_values(array_unique(array_column($report, 'status'))));
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

    /** The flag icons icon.language-id / -en are the HUD language icons (owner request 18 October 2026). */
    public function testLanguageIconsComeFromTheFlagIcons(): void
    {
        $report = array_column($this->importer()->run(), null, 'source');

        $this->assertSame(['ui.lang.id', 'installed'], [$report['icon.language-id']['target'], $report['icon.language-id']['status']]);
        $this->assertSame(['ui.lang.en', 'installed'], [$report['icon.language-en']['target'], $report['icon.language-en']['status']]);
        foreach (['reference.01', 'reference.19', 'reference.20', 'icon.home'] as $key) {
            $this->assertArrayNotHasKey($key, $report, $key . ' is not a slot (the old language references included)');
        }

        foreach (['ui.lang.id', 'ui.lang.en'] as $key) {
            $row = model(MediaAssetModel::class)->findByKey($key);
            $this->assertSame(CatalogImporter::CREDIT, $row['credit'], $key);
            $this->assertSame([256, 256], [(int) $row['width_px'], (int) $row['height_px']], $key);
        }
    }

    /**
     * Owner, 19 October 2026: Jaka's avatar is the owner's framed picture from
     * the Kenali mockup package; the bust reference.03 (its source since
     * 18 October 2026) is no longer a slot, and a bust installed by an earlier
     * run is replaced.
     */
    public function testJakaAvatarComesFromTheFramedPictureInTheKenaliPackage(): void
    {
        $this->writeCatalog([
            'reference.01' => ['referensi/reference.01.webp', 64, 64, false],
            'reference.03' => ['referensi/reference.03.webp', 1024, 1024, true],
        ]);

        $path = array_search('ui.jaka-avatar', CatalogImporter::MOCKUP_FILES, true);
        $this->assertSame('mockup/gambar-mockup/kenali/aset/ui.jaka-avatar.webp', $path);
        $this->assertFileExists(ROOTPATH . 'gelita-katalog/' . $path, 'the owner picture ships with the catalogue');

        // Before the mockup file arrives nothing fills the slot: reference.03 is not a slot any more
        $report = $this->importer()->run();
        $this->assertNotContains('ui.jaka-avatar', array_column($report, 'target'));
        $this->assertNotContains('reference.03', array_column($report, 'source'));
        $this->assertNotContains('reference.01', array_column($report, 'source'));

        // A bust installed by an older run, then the framed picture
        model(MediaAssetModel::class)->where('asset_key', 'ui.jaka-avatar')->set(['credit' => CatalogImporter::CREDIT, 'sha256' => str_repeat('0', 64), 'is_active' => 1])->update();
        $file = $this->catalog . '/' . $path;
        mkdir(dirname($file), 0777, true);
        $image = imagecreatetruecolor(1200, 1200);
        imagewebp($image, $file, 90);

        $report = array_column($this->importer()->run(), null, 'target');
        $this->assertSame(['ui.jaka-avatar.webp', 'installed'], [$report['ui.jaka-avatar']['source'], $report['ui.jaka-avatar']['status']]);

        $row = model(MediaAssetModel::class)->findByKey('ui.jaka-avatar');
        $this->assertSame([512, 512], [(int) $row['width_px'], (int) $row['height_px']]);
        $this->assertNull(model(MediaAssetModel::class)->findByKey('reference.03'));

        // --kunci reaches it by its slot key
        model(MediaAssetModel::class)->where('asset_key', 'ui.jaka-avatar')->set(['credit' => null])->update();
        $this->assertSame(['ui.jaka-avatar' => 'installed'], array_column($this->importer()->run(false, true, null, ['ui.jaka-avatar']), 'status', 'target'));
    }

    /** Owner, 19 October 2026: the Mbah Kedu avatar reference.02 is the Kenali overlay's portrait. */
    public function testKeduAvatarComesFromTheMbahKeduReference(): void
    {
        $this->writeCatalog([
            'reference.02' => ['referensi/reference.02.webp', 1024, 1024, true],
            'reference.14' => ['referensi/reference.14.webp', 64, 64, false],
        ]);

        $report = $this->importer()->run();

        $this->assertContains('reference.02 → ui.kedu-avatar: installed', array_map(static fn (array $r): string => $r['source'] . ' → ' . $r['target'] . ': ' . $r['status'], $report));
        $this->assertNotContains('reference.14', array_column($report, 'source'));

        $row = model(MediaAssetModel::class)->findByKey('ui.kedu-avatar');
        $this->assertSame([512, 512], [(int) $row['width_px'], (int) $row['height_px']]);
        $this->assertNull(model(MediaAssetModel::class)->findByKey('reference.02'));
    }

    /** Result screen redesign (18 October 2026): its skin slots, and avatar.2 copied to ui.result-medal. */
    public function testResultScreenSlotsAndTheMedalCopy(): void
    {
        $this->writeCatalog([
            'avatar.2'             => ['ikon/avatar.2.webp', 512, 512, true],
            'ui.result-panel'      => ['antarmuka/ui.result-panel.webp', 1536, 752, true],
            'ui.result-star'       => ['antarmuka/ui.result-star.webp', 128, 128, true],
            'bg.result.temanggung' => ['latar/bg.result.temanggung.webp', 1920, 1080, false],
        ]);

        $report = $this->importer()->run();
        $pairs  = array_map(static fn (array $r): string => $r['source'] . ' → ' . $r['target'] . ': ' . $r['status'], $report);

        foreach (['avatar.2 → avatar.2', 'avatar.2 → ui.result-medal', 'ui.result-panel → ui.result-panel', 'ui.result-star → ui.result-star', 'bg.result.temanggung → bg.result.temanggung'] as $pair) {
            $this->assertContains($pair . ': installed', $pairs);
        }

        $medal = model(MediaAssetModel::class)->findByKey('ui.result-medal');
        $this->assertSame([512, 512], [(int) $medal['width_px'], (int) $medal['height_px']]);
        $this->assertNotSame($medal['storage_path'], model(MediaAssetModel::class)->findByKey('avatar.2')['storage_path'], 'a file of its own');

        // --kunci on the copy's slot reaches its catalogue entry, and only that slot's entry
        model(MediaAssetModel::class)->where('asset_key', 'ui.result-medal')->set(['credit' => null])->update();
        $only = array_column($this->importer()->run(false, true, null, ['ui.result-medal']), 'status', 'target');
        $this->assertSame('installed', $only['ui.result-medal']);
        $this->assertArrayNotHasKey('avatar.2', $only, 'the avatar itself is not overwritten');
        $this->assertArrayNotHasKey('ui.result-panel', $only);
    }

    /** Page redesign 2: the Peta Kedu diorama is installed from its mockup package (not in data.json). */
    public function testDioramaComesFromTheMockupPackage(): void
    {
        $file = $this->catalog . '/' . array_key_first(CatalogImporter::MOCKUP_FILES);
        mkdir(dirname($file), 0777, true);
        $image = imagecreatetruecolor(1774, 887);
        imagewebp($image, $file, 90);

        $report = array_column($this->importer()->run(), null, 'target');
        $this->assertSame(['diorama.kedu-tiga-wilayah.webp', 'installed'], [$report['map.kedu-diorama']['source'], $report['map.kedu-diorama']['status']]);

        $row = model(MediaAssetModel::class)->findByKey('map.kedu-diorama');
        $this->assertSame([1774, 887], [(int) $row['width_px'], (int) $row['height_px']]);

        // --kunci reaches it by its slot key
        $only = array_column($this->importer()->run(true, true, null, ['map.kedu-*']), 'target');
        $this->assertSame(['map.kedu-diorama'], $only);
    }

    /** --kunci limits the run, and so --timpa, to the named slots. */
    public function testOnlyLimitsTheOverwriteToMatchingSlots(): void
    {
        $this->importer()->run();
        // Both slots now hold an admin upload (e.g. the old lurik frame, an old button)
        model(MediaAssetModel::class)->whereIn('asset_key', ['char.jaka.idle.1', 'ui.logo-hero'])->set(['credit' => null])->update();

        $report = $this->importer()->run(false, true, null, ['char.jaka.*', 'ui.lang.*']);
        $byTarget = array_column($report, null, 'target');

        $this->assertSame('installed', $byTarget['char.jaka.idle.1']['status']);
        $this->assertSame('installed', $byTarget['ui.lang.en']['status'], 'a pattern also matches the slot an icon fills');
        $this->assertArrayNotHasKey('ui.logo-hero', $byTarget);
        $this->assertArrayNotHasKey('bg.story.intro.1', $byTarget);
        $this->assertSame(CatalogImporter::CREDIT, model(MediaAssetModel::class)->findByKey('char.jaka.idle.1')['credit']);
        $this->assertNull(model(MediaAssetModel::class)->findByKey('ui.logo-hero')['credit'], 'other admin uploads stay');
    }

    /** A kept upload among catalogue frames would make the loop switch drawings. */
    public function testPoseWithFramesFromTwoSourcesIsReported(): void
    {
        $this->importer()->run();
        model(MediaAssetModel::class)->where('asset_key', 'char.jaka.idle.1')->set(['credit' => null])->update();

        $warnings = array_values(array_filter($this->importer()->run(), static fn (array $r): bool => $r['status'] === 'warning'));

        $this->assertCount(1, $warnings);
        $this->assertSame('char.jaka.idle', $warnings[0]['target']);
        $this->assertStringContainsString('gambar gerak 1 bukan dari katalog', $warnings[0]['note']);
        $this->assertStringContainsString('--timpa --kunci=char.jaka.idle.*', $warnings[0]['note']);

        $this->assertNotContains('warning', array_column($this->importer()->run(true, true), 'status'), 'overwriting fixes it');
        $this->importer()->run(false, true, null, ['char.jaka.idle.*']);
        $this->assertNotContains('warning', array_column($this->importer()->run(), 'status'));
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
        return new CatalogImporter($this->catalog, null, [], []);
    }

    /** @return array<string, mixed> */
    private function node(string $region, int $sequence): array
    {
        return $this->sqlite->table('challenge_nodes n')->select('n.map_x, n.map_y')
            ->join('levels l', 'l.id = n.level_id')
            ->where('l.code', $region)->where('n.sequence', $sequence)
            ->get()->getRowArray();
    }

    /** @return list<float> x, y, w of the Cari Objek item showing this object */
    private function object(string $mediaKey): array
    {
        $config = json_decode((string) $this->objectRow($mediaKey)['config_json'], true);

        return [(float) $config['x'], (float) $config['y'], (float) $config['w']];
    }

    /** @param list<float> $position */
    private function setObject(string $mediaKey, array $position): void
    {
        $row    = $this->objectRow($mediaKey);
        $config = json_decode((string) $row['config_json'], true);
        [$config['x'], $config['y'], $config['w']] = $position;
        $this->sqlite->table('challenge_items')->where('id', $row['id'])->update(['config_json' => json_encode($config)]);
    }

    /** @return array<string, mixed> */
    private function objectRow(string $mediaKey): array
    {
        return $this->sqlite->table('challenge_items i')->select('i.id, i.config_json')
            ->join('media_assets m', 'm.id = i.media_asset_id')
            ->where('m.asset_key', $mediaKey)->where('i.interaction_type', 'find_object')
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
