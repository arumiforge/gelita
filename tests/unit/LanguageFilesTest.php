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
}
