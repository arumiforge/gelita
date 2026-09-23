<?php

use App\Libraries\BankWorkbookGuide;
use App\Services\ContentImportService;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Workbook berpanduan harus tetap sejalan dengan importer: setiap kolom yang
 * dibaca ContentImportService punya penjelasan Indonesia, header sheet data
 * persis sama dengan yang diharapkan importer, dan workbook produksi di
 * docs/bank-soal ikut diperbarui bila kolom berubah.
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

    public function testProductionWorkbookMatchesImporterHeaders(): void
    {
        $file = ROOTPATH . 'docs/bank-soal/gelita-bank-soal-produksi.xlsx';

        $this->assertFileExists($file, 'Jalankan php docs/bank-soal/build-workbook.php');

        $book = IOFactory::load($file);

        foreach (ContentImportService::SHEETS as $sheet => $headers) {
            $data = $book->getSheetByName($sheet);

            $this->assertNotNull($data, $sheet);

            $row = $data->rangeToArray('A1:' . $data->getHighestColumn() . '1', null, false, false)[0];
            $this->assertSame($headers, $row, "{$sheet}: bangun ulang workbook produksi setelah kolom impor berubah");
            $this->assertGreaterThan(1, $data->getHighestDataRow(), "{$sheet} kosong");
        }

        $book->disconnectWorksheets();
    }
}
