<?php

use App\Libraries\ExcelWriter;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * ExcelWriter menulis XLSX bertahap tanpa PhpSpreadsheet di memori. Test ini
 * membaca ulang hasilnya dengan PhpSpreadsheet untuk memastikan paketnya sah,
 * teks tidak pernah menjadi rumus, dan berkas sementara tidak tertinggal.
 *
 * @internal
 */
final class ExcelWriterTest extends CIUnitTestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir() . '/gelita-xlsx-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/{,.}*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        @rmdir($this->dir);

        parent::tearDown();
    }

    public function testWritesReadableWorkbookWithTypedCells(): void
    {
        $path   = $this->dir . '/export.xlsx';
        $writer = new ExcelWriter($path);

        $writer->startSheet('Participants', ['participant_code', 'age', 'ratio', 'note', 'flag', 'empty']);
        $writer->row(['GLT-000001', 11, 0.75, '=HYPERLINK("http://x")', true, null]);
        $writer->row(['GLT-000002', 12, 1.5, "a<b & \"c\"\x01 ünï", false, '']);
        $writer->startSheet('Raw Events', ['n']);
        $writer->feed(static function (): Generator {
            for ($i = 1; $i <= 2500; $i++) {
                yield [$i];
            }
        });

        $sha = $writer->finish();

        $this->assertSame(hash_file('sha256', $path), $sha);
        $this->assertSame(2502, $writer->rowCount(), 'jumlah baris data tanpa header');
        $this->assertSame([], glob($this->dir . '/.tmp-*'), 'folder sementara dibersihkan');

        $book  = IOFactory::load($path);
        $sheet = $book->getSheetByName('Participants');

        $this->assertSame(['Participants', 'Raw Events'], $book->getSheetNames());
        $this->assertSame('participant_code', (string) $sheet->getCell('A1')->getValue());
        $this->assertSame(11, (int) $sheet->getCell('B2')->getValue());
        $this->assertSame(0.75, (float) $sheet->getCell('C2')->getValue());
        $this->assertSame('inlineStr', $sheet->getCell('D2')->getDataType(), 'teks berawalan = tidak pernah menjadi rumus');
        $this->assertSame('=HYPERLINK("http://x")', (string) $sheet->getCell('D2')->getValue());
        $this->assertSame('a<b & "c" ünï', (string) $sheet->getCell('D3')->getValue(), 'karakter kontrol dibuang, entitas XML dipulihkan');
        $this->assertSame(1, (int) $sheet->getCell('E2')->getValue());
        $this->assertNull($sheet->getCell('F2')->getValue());
        $this->assertSame(2501, $book->getSheetByName('Raw Events')->getHighestRow());
    }

    public function testSheetNamesAreSanitizedAndUnique(): void
    {
        $path   = $this->dir . '/names.xlsx';
        $writer = new ExcelWriter($path);

        $writer->startSheet('Data: [a/b]?', ['x']);
        $writer->startSheet('Data  a b', ['x']);
        $writer->startSheet(str_repeat('Panjang', 10), ['x']);
        $writer->finish();

        $names = IOFactory::load($path)->getSheetNames();

        $this->assertSame('Data   a b', $names[0]);
        $this->assertSame('Data  a b', $names[1]);
        $this->assertLessThanOrEqual(31, mb_strlen($names[2]));
    }

    public function testAbortRemovesPartialFiles(): void
    {
        $path   = $this->dir . '/batal.xlsx';
        $writer = new ExcelWriter($path);

        $writer->startSheet('Sessions', ['a']);
        $writer->row([1]);
        $writer->abort();

        $this->assertFileDoesNotExist($path);
        $this->assertSame([], glob($this->dir . '/.tmp-*'));
    }

    public function testColumnNames(): void
    {
        $this->assertSame('A', ExcelWriter::columnName(0));
        $this->assertSame('Z', ExcelWriter::columnName(25));
        $this->assertSame('AA', ExcelWriter::columnName(26));
        $this->assertSame('BA', ExcelWriter::columnName(52));
    }
}
