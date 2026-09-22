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
    private const BILINGUAL = ['Auth', 'Game'];

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
}
