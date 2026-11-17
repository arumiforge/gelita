<?php

use App\Libraries\CatalogSite;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * `gelita:katalog:halaman` (CatalogSite): the public Katalog Aset site is
 * built from a small fake repository (pictures, review list, sounds,
 * narration), rebuilds only what changed, removes stale files, never carries
 * prompt texts, and the committed gelita-katalog/ matches the real sources.
 *
 * @internal
 */
final class CatalogSiteTest extends CIUnitTestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = sys_get_temp_dir() . '/gelita-katalog-site-' . bin2hex(random_bytes(4));

        foreach (['ai-studio/game-images/assets/karakter', 'ai-studio/game-images/assets/latar', 'ai-studio/game-images/assets/tantangan',
            'ai-studio/game-images/mockup/gambar-mockup/skenario/aset', 'ai-studio/elevenlabs-sound', 'ai-studio/elevenlabs-voice/out/id',
            'ai-studio/elevenlabs-voice/out/en', 'public/assets/ui', 'public/assets/app', 'public/assets/audio/music', 'public/assets/audio/sfx',
            'public/assets/fonts'] as $dir) {
            mkdir($this->root . '/' . $dir, 0777, true);
        }

        $img = 'ai-studio/game-images/assets/';
        $this->picture($img . 'karakter/char.jaka.idle.1.webp', 600, 900);
        $this->picture($img . 'karakter/char.jaka.idle.2.webp', 600, 900);
        $this->picture($img . 'latar/bg.cerita-intro-01.webp', 1920, 1080);
        $this->picture($img . 'tantangan/challenge.item.tmg-1-01.webp', 1200, 1200);
        $this->picture('ai-studio/game-images/mockup/gambar-mockup/skenario/aset/item.skenario.mgl-5-a01.webp', 1536, 1024);
        $this->picture('public/assets/ui/round-back.webp', 200, 200);
        $this->picture('public/assets/ui/node-card-cari.webp', 640, 360);
        file_put_contents($this->root . '/public/assets/ui/placeholder.svg', '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"/>');
        $this->picture('public/assets/app/icon-192.png', 192, 192, 'png');

        $entry = static fn (string $key, string $file, string $cat, string $title): array => [
            'key' => $key, 'file' => $file, 'cat' => $cat, 'title' => $title, 'region' => 'Umum',
            'desc' => 'Keterangan ' . $title, 'prompt' => 'SECRET PROMPT TEXT ' . $key,
        ];
        file_put_contents($this->root . '/ai-studio/game-images/data.json', json_encode([
            $entry('char.jaka.idle.1', 'assets/karakter/char.jaka.idle.1.webp', 'Karakter', 'Jaka diam'),
            $entry('char.jaka.idle.2', 'assets/karakter/char.jaka.idle.2.webp', 'Karakter', 'Jaka diam 2'),
            $entry('bg.cerita-intro-01', 'assets/latar/bg.cerita-intro-01.webp', 'Background', 'Dataran yang Bercahaya'),
            $entry('bg.cerita-intro-02', 'assets/latar/bg.cerita-intro-01.webp', 'Background', 'Dataran yang Bercahaya'),
            ['region' => 'Temanggung'] + $entry('challenge.item.tmg-1-01', 'assets/tantangan/challenge.item.tmg-1-01.webp', 'Tantangan', 'Gunung Sindoro dan Sumbing'),
        ]));

        file_put_contents($this->root . '/ai-studio/game-images/REVIEW.md', implode("\n", [
            '# Picture review',
            '',
            '| No | Picture | Shows | Used in the game | Status | Notes |',
            '|---|---|---|---|---|---|',
            '| 1 | [`round-back`](../../public/assets/ui/round-back.webp) | Button | Ships | `keep` |  |',
            '| 2 | [`char.jaka.idle.1`](assets/karakter/char.jaka.idle.1.webp) | Jaka | Jaka | `redo later: the scarf is too dark` | See [`TASKS.md`](x) row 3 |',
            '| 3 | [`challenge.item.tmg-1-01`](assets/tantangan/challenge.item.tmg-1-01.webp) | Puzzle | Puzzle | `redo: wrong mountain` |  |',
            '| 4 | [`bg.cerita-intro-01`](assets/latar/bg.cerita-intro-01.webp) | Story | Story |  |  |',
        ]));

        file_put_contents($this->root . '/ai-studio/elevenlabs-sound/sounds.csv', "kode,jenis,putar_ulang,durasi_detik,kbps,lufs,puncak_db,layar,suasana,prompt\n"
            . "music/map,music,ya,58,128,-20.4,,Peta Kedu,\"megah, mengajak menjelajah\",SECRET MUSIC PROMPT\n"
            . "sfx/click,sfx,tidak,0.15,128,,-3.5,Tombol suara,\"pendek, lembut\",SECRET SFX PROMPT\n");
        file_put_contents($this->root . '/public/assets/audio/music/map.mp3', 'ID3 map');
        file_put_contents($this->root . '/public/assets/audio/sfx/click.mp3', 'ID3 click');

        file_put_contents($this->root . '/ai-studio/elevenlabs-voice/lines.csv', "kode_berkas,konteks,wilayah,urutan,tokoh,teks_id,teks_en,berkas_id,berkas_en\n"
            . "penutup-01,ending,,1,Narator,Akhir cerita.,The end.,ada,ada\n"
            . "intro-01,intro,,1,Narator,Dahulu kala.,Long ago.,ada,belum\n"
            . "petunjuk-tmg-4-01,hunt_clue,temanggung,1,Mbah Kedu,Cari kopi.,Find the coffee.,belum,belum\n");
        file_put_contents($this->root . '/ai-studio/elevenlabs-voice/out/id/intro-01.mp3', 'ID3 intro id');
        file_put_contents($this->root . '/ai-studio/elevenlabs-voice/out/id/penutup-01.mp3', 'ID3 end id');
        file_put_contents($this->root . '/ai-studio/elevenlabs-voice/out/en/penutup-01.mp3', 'ID3 end en');

        foreach (CatalogSite::FONT_FILES as $source) {
            file_put_contents($this->root . '/' . $source, 'font ' . $source);
        }
    }

    protected function tearDown(): void
    {
        $this->remove($this->root);
        parent::tearDown();
    }

    public function testModelHasEveryPictureOnceWithWhereUsedAndReviewStatus(): void
    {
        $images = array_column((new CatalogSite($this->root))->images(), null, 'id');

        // Frame 2 of a pose is left out; one story file filling two slides is one entry
        $this->assertArrayNotHasKey('char.jaka.idle.2', $images);
        $this->assertSame(['bg.cerita-intro-01', 'bg.cerita-intro-02'], $images['bg.cerita-intro-01']['k']);
        $this->assertSame('story', $images['bg.cerita-intro-01']['g']);
        $this->assertSame(['Cerita pembuka, slide 1', 'Cerita pembuka, slide 2'], $images['bg.cerita-intro-01']['u']);
        $this->assertSame('none', $images['bg.cerita-intro-01']['s']);

        $this->assertSame('Jaka, pose diam', $images['char.jaka.idle.1']['t']);
        $this->assertSame('redo-later', $images['char.jaka.idle.1']['s']);
        $this->assertSame('the scarf is too dark — See TASKS.md row 3', $images['char.jaka.idle.1']['sn']);

        $puzzle = $images['challenge.item.tmg-1-01'];
        $this->assertSame(['challenge', 'temanggung', 'puzzle', 'redo', 3], [$puzzle['g'], $puzzle['r'], $puzzle['c'], $puzzle['s'], $puzzle['no']]);
        $this->assertSame([1200, 1200], $puzzle['wh']);
        $this->assertStringContainsString('Tantangan Susun Gambar Temanggung', $puzzle['u'][0]);

        // Mock-up folder picture, shipped UI art, app icon, SVG
        $this->assertSame(['challenge', 'magelang', 'skenario'], [$images['item.skenario.mgl-5-a01']['g'], $images['item.skenario.mgl-5-a01']['r'], $images['item.skenario.mgl-5-a01']['c']]);
        $this->assertSame(['ui', 'keep'], [$images['ui-round-back']['g'], $images['ui-round-back']['s']]);
        $this->assertSame('cari', $images['ui-node-card-cari']['c']);
        $this->assertSame('app', $images['app-icon-192']['g']);
        $this->assertSame(['gambar/ui-placeholder.svg', 'gambar/ui-placeholder.svg'], [$images['ui-placeholder']['img'], $images['ui-placeholder']['th']]);
    }

    public function testNarrationFollowsTheStoryOrderWithTheTakesThatExist(): void
    {
        $lines = (new CatalogSite($this->root))->narration();

        $this->assertSame(['intro-01', 'petunjuk-tmg-4-01', 'penutup-01'], array_column($lines, 'id'));
        $this->assertSame('suara/narasi/id/intro-01.mp3', $lines[0]['fi']);
        $this->assertArrayNotHasKey('fe', $lines[0]);
        $this->assertSame(['temanggung', 'cari'], [$lines[1]['r'], $lines[1]['c']]);
        $this->assertArrayNotHasKey('fi', $lines[1]);
    }

    public function testBuildWritesASelfContainedSiteWithoutPrompts(): void
    {
        $out    = $this->root . '/gelita-katalog';
        $site   = new CatalogSite($this->root, $out);
        $report = $site->build();

        $this->assertSame([], $report['failed']);
        $this->assertSame([], $site->check());

        foreach (['index.html', 'katalog.css', 'katalog.js', 'data.js', 'gambar/challenge.item.tmg-1-01.webp', 'gambar/kecil/challenge.item.tmg-1-01.webp',
            'suara/musik/map.mp3', 'suara/efek/click.mp3', 'suara/narasi/en/penutup-01.mp3', 'huruf/marcellus-400.woff2'] as $file) {
            $this->assertFileExists($out . '/' . $file);
        }

        // Web size: the 1920 px story master is at most PREVIEW_MAX wide, the thumbnail THUMB_MAX
        $this->assertSame(CatalogSite::PREVIEW_MAX, getimagesize($out . '/gambar/bg.cerita-intro-01.webp')[0]);
        $this->assertSame(CatalogSite::THUMB_MAX, getimagesize($out . '/gambar/kecil/bg.cerita-intro-01.webp')[0]);
        // Small shipped UI art is copied as it is
        $this->assertFileEquals($this->root . '/public/assets/ui/round-back.webp', $out . '/gambar/ui-round-back.webp');
        // Audio is copied byte for byte
        $this->assertFileEquals($this->root . '/public/assets/audio/music/map.mp3', $out . '/suara/musik/map.mp3');

        $data = (string) file_get_contents($out . '/data.js');
        $this->assertStringNotContainsString('SECRET', $data);
        $this->assertStringNotContainsString('"prompt"', $data);
        $this->assertStringNotContainsString('"_src"', $data);

        // Every link of the page stays inside the folder
        foreach (['index.html', 'katalog.css'] as $file) {
            preg_match_all('/(?:src|href)="([^"#]+)"|url\(\'([^\']+)\'\)/', (string) file_get_contents($out . '/' . $file), $m);

            foreach (array_filter(array_merge($m[1], $m[2])) as $link) {
                $this->assertFileExists($out . '/' . $link, $file . ' links to ' . $link);
            }
        }
    }

    public function testRebuildChangesOnlyWhatChangedAndRemovesStaleFiles(): void
    {
        $out  = $this->root . '/gelita-katalog';
        $site = new CatalogSite($this->root, $out);
        $site->build();
        $data = (string) file_get_contents($out . '/data.js');

        $again = $site->build();
        $this->assertSame([[], [], [], []], [$again['encoded'], $again['copied'], $again['written'], $again['removed']]);
        $this->assertSame($data, file_get_contents($out . '/data.js'));

        // A replaced master, a stale preview and a new review mark
        $this->picture('ai-studio/game-images/assets/tantangan/challenge.item.tmg-1-01.webp', 1200, 1200, 'webp', [10, 200, 30]);
        file_put_contents($out . '/gambar/old-picture.webp', 'stale');
        $review = $this->root . '/ai-studio/game-images/REVIEW.md';
        file_put_contents($review, str_replace('| 4 | [`bg.cerita-intro-01`](assets/latar/bg.cerita-intro-01.webp) | Story | Story |  |', '| 4 | [`bg.cerita-intro-01`](assets/latar/bg.cerita-intro-01.webp) | Story | Story | `keep` |', (string) file_get_contents($review)));

        $problems = (new CatalogSite($this->root, $out))->check();
        $this->assertContains('data.js tidak sesuai dengan sumbernya (gambar, daftar tinjauan, suara, atau narasi berubah).', $problems);
        $this->assertContains('Berkas lama yang tidak dipakai lagi: gambar/old-picture.webp', $problems);

        $report = (new CatalogSite($this->root, $out))->build();
        $this->assertSame(['gambar/challenge.item.tmg-1-01.webp'], $report['encoded']);
        $this->assertSame(['gambar/old-picture.webp'], $report['removed']);
        $this->assertSame(['data.js'], $report['written']);
        $this->assertFileDoesNotExist($out . '/gambar/old-picture.webp');
        $this->assertStringContainsString('"id":"bg.cerita-intro-01"', (string) file_get_contents($out . '/data.js'));
        $this->assertSame([], (new CatalogSite($this->root, $out))->check());
    }

    public function testDryRunWritesNothing(): void
    {
        $out    = $this->root . '/gelita-katalog';
        $report = (new CatalogSite($this->root, $out))->build(true);

        $this->assertNotSame([], $report['encoded']);
        $this->assertDirectoryDoesNotExist($out);
    }

    /**
     * The committed site matches the game's sources. When this fails, run
     * `php spark gelita:katalog:halaman` and commit gelita-katalog/.
     */
    public function testCommittedSiteIsCurrent(): void
    {
        $this->assertSame([], (new CatalogSite())->check(), 'gelita-katalog/ is stale: run php spark gelita:katalog:halaman and commit the folder.');
    }

    /** @param array{0: int, 1: int, 2: int} $rgb */
    private function picture(string $path, int $width, int $height, string $type = 'webp', array $rgb = [200, 150, 60]): void
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, ...$rgb));
        imagefilledrectangle($image, 0, 0, intdiv($width, 3), intdiv($height, 3), imagecolorallocate($image, 20, 30, 60));
        $type === 'png' ? imagepng($image, $this->root . '/' . $path) : imagewebp($image, $this->root . '/' . $path, 80);
        imagedestroy($image);
    }

    private function remove(string $dir): void
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
