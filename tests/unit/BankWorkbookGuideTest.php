<?php

use App\Libraries\BankSource;
use App\Libraries\BankWorkbookGuide;
use App\Services\ContentImportService;
use App\Services\SystemResetService;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Workbook berpanduan harus tetap sejalan dengan importer: setiap kolom yang
 * dibaca ContentImportService punya penjelasan Indonesia, header sheet data
 * persis sama dengan yang diharapkan importer, dan workbook bank soal kelas 5
 * (docs/bank-soal) serta Pustaka (docs/pustaka) ikut dibangun ulang bila data
 * atau kolomnya berubah.
 *
 * @internal
 */
final class BankWorkbookGuideTest extends CIUnitTestCase
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = sys_get_temp_dir() . '/gelita-guide-' . bin2hex(random_bytes(4)) . '.xlsx';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }

        parent::tearDown();
    }

    public function testEveryImportColumnIsDocumented(): void
    {
        $this->assertSame(array_keys(ContentImportService::SHEETS), array_keys(BankWorkbookGuide::COLUMNS));

        foreach (ContentImportService::SHEETS as $sheet => $headers) {
            $this->assertSame($headers, BankWorkbookGuide::headers($sheet), $sheet);

            foreach (BankWorkbookGuide::COLUMNS[$sheet] as $column => $info) {
                $this->assertCount(5, $info, "{$sheet}.{$column}");
                $this->assertContains($info[1], ['Wajib', 'Bersyarat', 'Opsional'], "{$sheet}.{$column}");
                $this->assertNotSame('', trim($info[0]), "{$sheet}.{$column}");
                $this->assertNotSame('', trim($info[2]), "{$sheet}.{$column}");
            }
        }
    }

    public function testSheetPurposesCoverEveryDataSheet(): void
    {
        foreach (array_keys(ContentImportService::SHEETS) as $sheet) {
            $this->assertArrayHasKey($sheet, BankWorkbookGuide::SHEETS, $sheet);
        }
    }

    public function testWrittenWorkbookHasGuideSheetsHeadersAndDropdowns(): void
    {
        (new BankWorkbookGuide())->write(
            ['nodes' => [['tmg-2', 'Judul', 'Title']]],
            $this->path,
            ['title' => 'Uji'],
            ['DAFTAR_MEDIA' => ['headers' => ['asset_key', 'Isi gambar'], 'rows' => [['challenge.tmg-4.scene', 'Pasar']]]],
        );

        $book  = IOFactory::load($this->path);
        $names = $book->getSheetNames();

        $this->assertSame('PETUNJUK', $names[0]);
        $this->assertSame('KAMUS_KOLOM', $names[1]);
        $this->assertSame('DAFTAR_MEDIA', end($names));

        foreach (ContentImportService::SHEETS as $sheet => $headers) {
            $data = $book->getSheetByName($sheet);

            $this->assertNotNull($data, $sheet);

            $row = $data->rangeToArray('A1:' . $data->getHighestColumn() . '1', null, false, false)[0];
            $this->assertSame($headers, $row, $sheet);
        }

        $nodes = $book->getSheetByName('nodes');

        $this->assertSame('tmg-2', $nodes->getCell('A2')->getValue());
        $this->assertSame('Title', $nodes->getCell('C2')->getValue());
        $this->assertNotNull($nodes->getComment('A1')->getText()->getPlainText());

        $validation = $nodes->getCell('A2')->getDataValidation();
        $this->assertSame('list', $validation->getType());
        $this->assertStringContainsString('tmg-1', $validation->getFormula1());

        $book->disconnectWorksheets();
    }

    /**
     * Templat unduhan importer: semua sheet dengan header importer; sheet
     * bank soal berisi contoh dari bank kelas 5, sheet Pustaka tanpa contoh
     * (agar Pustaka tidak tertimpa tanpa sengaja).
     */
    public function testTemplateMatchesImporterHeaders(): void
    {
        $book = IOFactory::load((new ContentImportService())->template());

        foreach (ContentImportService::SHEETS as $sheet => $headers) {
            $data = $book->getSheetByName($sheet);

            $this->assertNotNull($data, "templat: {$sheet}");
            $this->assertSame($headers, $this->headerRow($data), "templat {$sheet}");

            if (! in_array($sheet, ['library', 'library_media'], true)) {
                $this->assertNotSame([], $this->dataRows($data), "templat: {$sheet} punya contoh");
            }
        }

        $this->assertSame([], $this->dataRows($book->getSheetByName('library')), 'templat tanpa contoh Pustaka');
        $book->disconnectWorksheets();
    }

    /**
     * Workbook bank soal kelas 5 (Dok 2 §7.1/§7.3): header sama dengan
     * importer, tanpa sheet Pustaka maupun sheet lama, dan isinya sama persis
     * dengan berkas data — bila data atau kolom berubah, jalankan ulang
     * `php docs/bank-soal/build-workbook.php` dan commit workbook-nya.
     */
    public function testGradeFiveWorkbookIsRebuiltFromTheDataFiles(): void
    {
        $file = ROOTPATH . SystemResetService::DEFAULT_BANK;
        $this->assertFileExists($file);

        $book = IOFactory::load($file);
        $flat = BankSource::flatten(BankSource::load(ROOTPATH . 'docs/bank-soal/data'));

        foreach (['options', 'pieces', 'sources', 'library', 'library_media'] as $gone) {
            $this->assertNull($book->getSheetByName($gone), "workbook kelas 5 tanpa sheet {$gone}");
        }

        foreach ($flat['sheets'] as $sheet => $rows) {
            $data = $book->getSheetByName($sheet);

            $this->assertNotNull($data, $sheet);
            $this->assertSame(ContentImportService::SHEETS[$sheet], $this->headerRow($data), "{$sheet}: bangun ulang workbook setelah kolom impor berubah");
            $this->assertSame($this->expected($sheet, $rows), $this->dataRows($data), "{$sheet}: bangun ulang workbook setelah data bank soal berubah");
        }

        $media = $book->getSheetByName('DAFTAR_MEDIA');
        $this->assertNotNull($media);
        $this->assertCount(67 + 24, $this->dataRows($media), '+ the situation pictures of the 24 scenarios (32 §7.5)');

        $book->disconnectWorksheets();
    }

    /** Workbook Pustaka (Dok 2 §7.1): hanya sheet Pustaka, isinya sama dengan docs/pustaka/data. */
    public function testLibraryWorkbookIsRebuiltFromTheDataFiles(): void
    {
        $book = IOFactory::load(ROOTPATH . 'docs/pustaka/gelita-pustaka.xlsx');
        $sheets = $book->getSheetNames();

        foreach (['nodes', 'variants', 'distractors', 'passages', 'items', 'hints'] as $gone) {
            $this->assertNotContains($gone, $sheets);
        }

        $pages = 0;
        $media = 0;

        foreach (['temanggung', 'magelang', 'wonosobo'] as $region) {
            $library = (static fn (string $path): array => require $path)(ROOTPATH . "docs/pustaka/data/{$region}.php")['library'];
            $pages += count($library);
            $media += array_sum(array_map(static fn (array $page): int => count($page['media'] ?? []), $library));
        }

        $this->assertSame(ContentImportService::SHEETS['library'], $this->headerRow($book->getSheetByName('library')));
        $this->assertCount($pages, $this->dataRows($book->getSheetByName('library')));
        $this->assertCount($media, $this->dataRows($book->getSheetByName('library_media')));
        $this->assertSame(30, $pages);

        $book->disconnectWorksheets();
    }

    // ------------------------------------------------------------- bantu

    /** @return list<string> */
    private function headerRow(PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        return $sheet->rangeToArray('A1:' . $sheet->getHighestColumn() . '1', null, false, false)[0];
    }

    /**
     * Baris data berisi (tanpa header), setiap sel sebagai teks.
     *
     * @return list<list<string>>
     */
    private function dataRows(PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $rows = $sheet->toArray(null, true, false, false);
        array_shift($rows);

        $out = [];

        foreach ($rows as $row) {
            $row = array_map(static fn ($value): string => $value === null ? '' : (string) $value, $row);

            if (implode('', $row) !== '') {
                $out[] = $row;
            }
        }

        return $out;
    }

    /**
     * @param list<array<string, string>> $rows
     *
     * @return list<list<string>>
     */
    private function expected(string $sheet, array $rows): array
    {
        return array_map(
            static fn (array $row): array => array_map(static fn (string $column): string => (string) ($row[$column] ?? ''), ContentImportService::SHEETS[$sheet]),
            $rows,
        );
    }
}
