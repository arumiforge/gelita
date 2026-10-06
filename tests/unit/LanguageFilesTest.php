<?php

use CodeIgniter\Test\CIUnitTestCase;

/**
 * Berkas bahasa dimuat malas (baru saat lang() pertama dipanggil), jadi galat
 * sintaks di dalamnya tidak tertangkap test lain — halaman berbahasa itu
 * langsung 500. Test ini memuat semuanya, lalu memastikan teks pemain
 * dwibahasa punya kunci yang sama di ID dan EN.
 *
 * @internal
 */
final class LanguageFilesTest extends CIUnitTestCase
{
    /** Berkas yang tampil ke pemain: wajib lengkap di kedua bahasa. */
    private const BILINGUAL = ['Auth', 'Game', 'Js'];

    /** @return array<string, array{string}> */
    public static function languageFiles(): array
    {
        $files = [];

        foreach (glob(APPPATH . 'Language/*/*.php') ?: [] as $path) {
            $files[basename(dirname($path)) . '/' . basename($path)] = [$path];
        }

        return $files;
    }

    /**
     * @dataProvider languageFiles
     */
    public function testFileParsesToArray(string $path): void
    {
        $lines = require $path;

        $this->assertIsArray($lines);
        $this->assertNotSame([], $lines);
    }

    public function testPlayerFacingKeysMatchBetweenIndonesianAndEnglish(): void
    {
        foreach (self::BILINGUAL as $file) {
            $id = array_keys(require APPPATH . "Language/id/{$file}.php");
            $en = array_keys(require APPPATH . "Language/en/{$file}.php");

            $this->assertSame([], array_values(array_diff($id, $en)), "{$file}: kunci hanya ada di id");
            $this->assertSame([], array_values(array_diff($en, $id)), "{$file}: kunci hanya ada di en");
        }
    }

    /**
     * Perombakan kelas 5 (Dok 1 §10, §14): fase penelitian, persetujuan wali,
     * dan gender "lainnya" sudah dihapus. Kunci lama tidak boleh tersisa di
     * berkas mana pun (termasuk sub-array label panel).
     */
    public function testNoPhaseConsentOrThirdGenderKeysRemain(): void
    {
        foreach (glob(APPPATH . 'Language/*/*.php') ?: [] as $path) {
            $label = basename(dirname($path)) . '/' . basename($path);

            foreach ($this->keysOf(require $path) as $key) {
                $this->assertDoesNotMatchRegularExpression('/^(?:phase|consent)|lainnya$/i', $key, "{$label}: kunci {$key}");
            }
        }

        $game = require APPPATH . 'Language/id/Game.php';

        $this->assertArrayHasKey('assentLabel', $game);
        $this->assertSame(
            'Aku mau ikut bermain GELITA. Aku tahu jawaban dan langkahku di dalam permainan dicatat untuk penelitian.',
            $game['assentLabel'],
        );
        $this->assertSame(
            'I want to play GELITA. I know my answers and steps in the game are recorded for research.',
            (require APPPATH . 'Language/en/Game.php')['assentLabel'],
        );
    }

    /**
     * Dok 2 §12: kunci yang dihapus tidak tersisa di berkas bahasa mana pun
     * dan tidak lagi dipakai kode (lang('Game.x') di PHP, t('x') di JS);
     * kunci baru ada dengan teks persis tabel.
     */
    public function testDocTwoTextKeys(): void
    {
        $removedGame = ['score', 'totalScore', 'meanFirstPass', 'engine_pilihan', 'variant_urutkan_informasi', 'verdictPendapat',
            'factVsOpinion', 'factWord', 'opinionWord', 'reasonLabel', 'reasonPlaceholder', 'typeAnswer', 'orderHint',
            'moveUp', 'moveDown', 'questionOf', 'sourceText', 'sourceOfficial', 'sourceAnonymous', 'gender_lainnya'];
        $removedJs = ['noRetry', 'finishAnyway', 'reasonSaved', 'cardMoved', 'orderOkTitle', 'orderOkText',
            'orderWrongTitle', 'orderWrongText', 'orderWrongPlain'];

        foreach (['id', 'en'] as $locale) {
            $game = require APPPATH . "Language/{$locale}/Game.php";
            $js   = require APPPATH . "Language/{$locale}/Js.php";

            foreach ($removedGame as $key) {
                $this->assertArrayNotHasKey($key, $game, "{$locale}/Game.{$key}");
            }

            foreach ($removedJs as $key) {
                $this->assertArrayNotHasKey($key, $js, "{$locale}/Js.{$key}");
            }
        }

        $php = '';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(APPPATH, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() === 'php' && ! str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR)) {
                $php .= file_get_contents($file->getPathname());
            }
        }

        $js = '';

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(FCPATH . 'assets/js', \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->getExtension() === 'js') {
                $js .= file_get_contents($file->getPathname());
            }
        }

        foreach ($removedGame as $key) {
            $this->assertStringNotContainsString("'Game.{$key}'", $php, "Game.{$key} masih dipakai");
        }

        foreach ($removedJs as $key) {
            $this->assertDoesNotMatchRegularExpression("/\\bt\\('{$key}'/", $js, "Js.{$key} masih dipakai");
        }

        $id = require APPPATH . 'Language/id/Game.php';
        $en = require APPPATH . 'Language/en/Game.php';
        $this->assertSame('Pernyataan {0}', $id['cardN']);
        $this->assertSame('Statement {0}', $en['cardN']);
        $this->assertSame('Kesempatan', $id['triesLabel']);
        $this->assertSame('Bintang per wilayah', $id['perRegion']);

        $jsId = require APPPATH . 'Language/id/Js.php';
        $jsEn = require APPPATH . 'Language/en/Js.php';
        $this->assertSame('Kesempatanmu tinggal {0} lagi.', $jsId['triesLeft']);
        $this->assertSame('Tinggal satu kesempatan lagi. Baca pelan-pelan, ya!', $jsId['triesLast']);
        $this->assertSame('Kesempatanmu sudah habis', $jsId['triesExhaustedTitle']);
        $this->assertSame('Petunjuk ini dilewati', $jsId['clueFailedTitle']);
        $this->assertSame('No more chances', $jsEn['triesExhaustedTitle']);

        $admin = (require APPPATH . 'Language/id/Admin.php')['event'];
        $this->assertSame('Mengecek jawaban', $admin['challenge_checked']);
        $this->assertSame('Petunjuk dilewati', $admin['clue_failed']);
        $this->assertSame('Ukuran layar berubah', $admin['viewport_changed']);
    }

    /**
     * Setiap label menu sidebar panel (termasuk "Rekaman narasi", tahap 5)
     * harus ada di Language/id/Admin.php; kunci yang hilang tampil mentah
     * (Admin.menuX).
     */
    public function testEveryAdminSidebarLabelExists(): void
    {
        $admin  = require APPPATH . 'Language/id/Admin.php';
        $source = (string) file_get_contents(APPPATH . 'Views/components/admin-sidebar.php');

        preg_match_all("/'Admin\\.([A-Za-z]+)'/", $source, $matches);

        $this->assertContains('menuNarration', $matches[1]);

        foreach (array_unique($matches[1]) as $key) {
            $this->assertArrayHasKey($key, $admin, "Admin.{$key}");
        }

        $this->assertSame('Rekaman narasi', $admin['menuNarration']);
    }

    /**
     * Tahap 4: di dalam wilayah namanya "Pustaka {wilayah}", di peta tetap
     * "Pustaka Kedu". Kalimat kunci memakai pola ICU (MessageFormatter), jadi
     * pola yang rusak baru ketahuan saat diformat — diuji di sini.
     */
    public function testRegionLibraryNamesAndLockTextFormat(): void
    {
        $language = service('language');

        try {
            $language->setLocale('id');
            $this->assertSame('Pustaka Kedu', lang('Game.library'));
            $this->assertSame('Pustaka Magelang', lang('Game.libraryRegion', ['Magelang']));
            $this->assertSame(
                'Selesaikan kelima tantangan di Magelang untuk membuka Pustaka Magelang. Isinya bacaan, gambar, dan video tentang Magelang.',
                lang('Game.libraryLockedText', ['Magelang', 5]),
            );
            $this->assertStringStartsWith('Selesaikan semua tantangan di', lang('Game.libraryLockedText', ['Magelang', 7]));
            $this->assertSame('Pustaka Magelang terbuka!', lang('Game.libraryUnlockedTitle', ['Magelang']));

            $language->setLocale('en');
            $this->assertSame('Kedu Library', lang('Game.library'));
            $this->assertSame('Magelang Library', lang('Game.libraryRegion', ['Magelang']));
            $this->assertStringStartsWith('Finish all 5 challenges in Magelang', lang('Game.libraryLockedText', ['Magelang', 5]));
            $this->assertSame('1 page', lang('Game.libraryPageCount', [1]));
            $this->assertSame('6 pages', lang('Game.libraryPageCount', [6]));
        } finally {
            $language->setLocale(config('App')->defaultLocale);
        }
    }

    /** Kunci Tahap 3 berada di blok berkomentar `// Tahap 3` sendiri di kedua bahasa. */
    public function testStageThreeKeysLiveInTheirOwnBlock(): void
    {
        $keys = ['kenalRegion', 'kenalTitle', 'kenalUnheard', 'enterRegionName', 'curtainRegionTitle', 'curtainSkip',
            'challengeLead_puzzle', 'challengeLead_cari', 'chapterOf', 'chapterTap', 'doneTitle', 'readLibraryRegion', 'watchEnding'];

        foreach (['id', 'en'] as $locale) {
            $source = (string) file_get_contents(APPPATH . "Language/{$locale}/Game.php");
            $block  = strstr($source, '// Tahap 3');

            $this->assertNotFalse($block, "{$locale}: blok // Tahap 3 tidak ada");

            foreach ($keys as $key) {
                $this->assertStringContainsString("'{$key}'", $block, "{$locale}: {$key} di luar blok Tahap 3");
            }
        }
    }

    /** Teks Tahap 3 yang memakai argumen terformat dengan benar di kedua bahasa. */
    public function testStageThreeTextFormat(): void
    {
        $language = service('language');

        try {
            $language->setLocale('id');
            $this->assertSame('Kenali Magelang', lang('Game.kenalRegion', ['Magelang']));
            $this->assertSame('Menuju Magelang…', lang('Game.curtainRegionTitle', ['Magelang']));
            $this->assertSame('Bab 2 · Magelang', lang('Game.chapterOf', [2, 'Magelang']));
            $this->assertSame('Serpihan Magelang kembali!', lang('Game.doneTitle', ['Magelang']));
            $this->assertSame('Baca Pustaka Magelang', lang('Game.readLibraryRegion', ['Magelang']));

            $language->setLocale('en');
            $this->assertSame('Heading to Magelang…', lang('Game.curtainRegionTitle', ['Magelang']));
            $this->assertSame('Chapter 2 · Magelang', lang('Game.chapterOf', [2, 'Magelang']));
            $this->assertSame('Read the Magelang Library', lang('Game.readLibraryRegion', ['Magelang']));
        } finally {
            $language->setLocale(config('App')->defaultLocale);
        }
    }

    /** Kunci Tahap 4 berada di blok berkomentar `// Tahap 4` sendiri di kedua bahasa. */
    public function testStageFourKeysLiveInTheirOwnBlock(): void
    {
        foreach (['id', 'en'] as $locale) {
            $source = (string) file_get_contents(APPPATH . "Language/{$locale}/Game.php");
            $block  = strstr($source, '// Tahap 4');

            $this->assertNotFalse($block, "{$locale}: blok // Tahap 4 tidak ada");

            foreach (['libraryRegion', 'libraryLockedText', 'libraryUnlockedTitle', 'libraryCredit', 'libraryViewSource'] as $key) {
                $this->assertStringContainsString("'{$key}'", $block, "{$locale}: {$key} di luar blok Tahap 4");
            }
        }
    }

    /**
     * Semua kunci, termasuk kunci sub-array (bertitik: `event.phase_x` → `phase_x`).
     *
     * @param array<string, mixed> $lines
     *
     * @return list<string>
     */
    private function keysOf(array $lines): array
    {
        $keys = [];

        foreach ($lines as $key => $value) {
            $keys[] = (string) $key;

            if (is_array($value)) {
                array_push($keys, ...$this->keysOf($value));
            }
        }

        return $keys;
    }
}
