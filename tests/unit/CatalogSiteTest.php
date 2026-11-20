<?php

use App\Libraries\CatalogSite;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * `gelita:katalog:halaman` (CatalogSite): the public Katalog Aset page is
 * built from a small fake repository (pictures, review list, sounds,
 * narration), links every file to where the live game serves it instead of
 * copying it, removes stale files, never carries prompt texts, and the
 * committed gelita-katalog/ matches the real sources.
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

    }

    protected function tearDown(): void
    {
        $this->remove($this->root);
        parent::tearDown();
    }

    public function testModelHasEveryPictureOnceWithWhereUsedAndReviewStatus(): void
    {
        $images = array_column((new CatalogSite($this->root))->images(), null, 'id');

        // One story file filling two slides is one entry
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
    }

    public function testEveryPictureLinksToWhereTheGameServesIt(): void
    {
        $images = array_column((new CatalogSite($this->root))->images(), null, 'id');

        $this->assertSame('assets/uploads/char.jaka.idle.1.webp', $images['char.jaka.idle.1']['p']);
        $this->assertSame('assets/uploads/challenge.item.tmg-1-01.webp', $images['challenge.item.tmg-1-01']['p']);
        // A story background is linked under its first slide's key
        $this->assertSame('assets/uploads/bg.story.intro.1.webp', $images['bg.cerita-intro-01']['p']);
        $this->assertSame('assets/uploads/item.skenario.mgl-5-a01.webp', $images['item.skenario.mgl-5-a01']['p']);
        $this->assertMatchesRegularExpression('#^assets/ui/round-back\.webp\?v=[0-9a-f]{8}$#', $images['ui-round-back']['p']);
        $this->assertMatchesRegularExpression('#^assets/app/icon-192\.png\?v=[0-9a-f]{8}$#', $images['app-icon-192']['p']);

        $site = new CatalogSite($this->root);
        $this->assertSame('assets/uploads/bg.story.level_open.magelang.5.webp', $site->servedPath('bg.cerita-dialog-magelang-05'));
        $this->assertSame('assets/uploads/bg.story.scenario_open.wonosobo.1.webp', $site->servedPath('bg.cerita-layar-wonosobo-01'));
        $this->assertSame('assets/uploads/bg.story.ending.2.webp', $site->servedPath('bg.cerita-penutup-02'));
        $this->assertSame('assets/uploads/app.icon-maskable.png', $site->servedPath('app.icon-maskable-512.png'));
        $this->assertSame('assets/uploads/ui.kedu-avatar.webp', $site->servedPath('reference.02'));
        $this->assertSame('assets/uploads/ui.lang.id.webp', $site->servedPath('icon.language-id'));
        $this->assertSame('assets/uploads/library.wonosobo.p2.1.webp', $site->servedPath('library.wonosobo.p2.1'));
        $this->assertNull($site->servedPath('reference.05'));
        $this->assertNull($site->servedPath('app.icon.svg'));
    }

    public function testSoundsLinkToTheGameAudioFolders(): void
    {
        $site = new CatalogSite($this->root, null, 'https://uji.example/');

        $this->assertSame('assets/audio/music/map.mp3', $site->sounds('music')[0]['f']);
        $this->assertSame('Peta Kedu', $site->sounds('music')[0]['t']);
        $this->assertSame('assets/audio/sfx/click.mp3', $site->sounds('sfx')[0]['f']);
        $this->assertContains('https://uji.example/assets/audio/narasi/en/intro-01.mp3', $site->urls());
        $this->assertContains('https://uji.example/assets/uploads/bg.story.intro.1.webp', $site->urls());
    }

    public function testNarrationFollowsTheStoryOrder(): void
    {
        $lines = (new CatalogSite($this->root))->narration();

        $this->assertSame(['intro-01', 'petunjuk-tmg-4-01', 'penutup-01'], array_column($lines, 'id'));
        // Linked whether or not the take is installed; the page shows a placeholder for a missing one
        $this->assertSame(['assets/audio/narasi/id/intro-01.mp3', 'assets/audio/narasi/en/intro-01.mp3'], [$lines[0]['fi'], $lines[0]['fe']]);
        $this->assertSame(['temanggung', 'cari'], [$lines[1]['r'], $lines[1]['c']]);
    }

    public function testBuildWritesOnlyThePageWithoutCopiesOrPrompts(): void
    {
        $out    = $this->root . '/gelita-katalog';
        $site   = new CatalogSite($this->root, $out);
        $report = $site->build();

        $this->assertSame(['data.js', 'index.html', 'katalog.css', 'katalog.js'], $this->sorted($report['written']));
        $this->assertSame([], $site->check());
        $this->assertSame(['data.js', 'index.html', 'katalog.css', 'katalog.js'], $this->sorted(array_values(array_diff(scandir($out), ['.', '..']))));

        $data = (string) file_get_contents($out . '/data.js');
        $this->assertStringContainsString('"base": "https://gelita.id/"', $data);
        $this->assertStringNotContainsString('SECRET', $data);
        $this->assertStringNotContainsString('"prompt"', $data);
        // No master path is linked (review notes may still name files, shown as text)
        $this->assertStringNotContainsString('"_src"', $data);
        $this->assertDoesNotMatchRegularExpression('/"(?:p|f|fi|fe)":"[^"]*ai-studio/', $data);

        // The page itself links only to its own files; pictures and sounds come from the base URL
        foreach (['index.html', 'katalog.css'] as $file) {
            preg_match_all('/(?:src|href)="([^"#]+)"|url\(\'([^\']+)\'\)/', (string) file_get_contents($out . '/' . $file), $m);

            foreach (array_filter(array_merge($m[1], $m[2])) as $link) {
                if (! str_starts_with($link, 'https://gelita.id/')) {
                    $this->assertFileExists($out . '/' . $link, $file . ' links to ' . $link);
                }
            }
        }
    }

    public function testRebuildRemovesTheOldCopiesAndFollowsTheSources(): void
    {
        $out  = $this->root . '/gelita-katalog';
        $site = new CatalogSite($this->root, $out);
        $site->build();

        $again = $site->build();
        $this->assertSame([[], []], [$again['written'], $again['removed']]);

        // Media folders of the earlier self-contained edition, a stray file, a README to keep, and a new review mark
        mkdir($out . '/gambar/kecil', 0777, true);
        file_put_contents($out . '/gambar/kecil/old.webp', 'old');
        mkdir($out . '/suara/musik', 0777, true);
        file_put_contents($out . '/suara/musik/map.mp3', 'old');
        file_put_contents($out . '/old.txt', 'stale');
        file_put_contents($out . '/README.md', 'kept');
        $review = $this->root . '/ai-studio/game-images/REVIEW.md';
        file_put_contents($review, str_replace('| Story | Story |  |  |', '| Story | Story | `keep` |  |', (string) file_get_contents($review)));

        $problems = (new CatalogSite($this->root, $out))->check();
        $this->assertContains('data.js tidak sesuai dengan sumbernya (gambar, daftar tinjauan, suara, atau narasi berubah).', $problems);
        $this->assertContains('Berkas atau folder yang tidak dipakai lagi: gambar', $problems);

        $report = (new CatalogSite($this->root, $out))->build();
        $this->assertSame(['data.js'], $report['written']);
        $this->assertSame(['gambar', 'old.txt', 'suara'], $report['removed']);
        $this->assertDirectoryDoesNotExist($out . '/gambar');
        $this->assertDirectoryDoesNotExist($out . '/suara');
        $this->assertFileExists($out . '/README.md');
        $this->assertMatchesRegularExpression('/\{"id":"bg\.cerita-intro-01",[^\n]*"s":"keep"/', (string) file_get_contents($out . '/data.js'));
        $this->assertSame([], (new CatalogSite($this->root, $out))->check());
    }

    public function testDryRunWritesNothing(): void
    {
        $out    = $this->root . '/gelita-katalog';
        $report = (new CatalogSite($this->root, $out))->build(true);

        $this->assertCount(4, $report['written']);
        $this->assertDirectoryDoesNotExist($out);
    }

    /** @param list<string> $list @return list<string> */
    private function sorted(array $list): array
    {
        sort($list);

        return $list;
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
