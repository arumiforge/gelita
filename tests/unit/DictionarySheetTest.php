<?php

use App\Libraries\ExportLabels;
use App\Libraries\ResearchDictionary;
use App\Models\DataExportModel;
use App\Services\ExportService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\RawDataWorld;

/**
 * The Kamus Data sheet and `dictionary_version` (research revision 10,
 * part 3: 29 §8, K10): every research column has a ResearchDictionary and
 * an ExportLabels entry, no entry names a column that is not downloaded,
 * every anchor exists in 11, and the version is in the sheet and in
 * data_exports.
 *
 * @internal
 */
final class DictionarySheetTest extends CIUnitTestCase
{
    use RawDataWorld;

    /** @var list<string> */
    private array $files = [];

    protected function tearDown(): void
    {
        array_map(static fn (string $f) => is_file($f) && unlink($f), $this->files);
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEveryColumnHasOneEntryAndNoMore(): void
    {
        $export = new ExportService();

        foreach (array_diff(ExportService::SHEETS, ['kamus']) as $sheet) {
            $columns = $export->columns($sheet, true, true);

            foreach ($columns as $column) {
                $this->assertNotNull(ResearchDictionary::entry($sheet, $column), "ResearchDictionary {$sheet}.{$column}");
                $this->assertTrue(ExportLabels::has($sheet, $column), "ExportLabels {$sheet}.{$column}");
                $this->assertNotSame('', ExportLabels::column($sheet, $column)['meaning'], "{$sheet}.{$column}: arti");
            }

            $this->assertSame([], array_values(array_diff(ResearchDictionary::columns($sheet), $columns)), "{$sheet}: no entry for a column that is not downloaded");
        }

        $this->assertEqualsCanonicalizing(array_values(array_diff(ExportService::SHEETS, ['kamus'])), ResearchDictionary::sheets());
    }

    public function testAnchorsExistIn11(): void
    {
        $doc     = (string) file_get_contents(ROOTPATH . 'docs/revisi-10-analitik/' . ResearchDictionary::DOC);
        $anchors = [];

        foreach (preg_split('/\R/', $doc) as $line) {
            if (preg_match('/^#{2,4} (.+)$/', $line, $m) === 1) {
                $slug      = mb_strtolower(preg_replace('/[^\p{L}\p{N} -]/u', '', strip_tags(str_replace('`', '', $m[1]))));
                $anchors[] = str_replace(' ', '-', $slug);
            }
        }

        foreach (ResearchDictionary::refs() as $ref) {
            $this->assertContains($ref, $anchors, $ref);
        }
    }

    public function testVersionInTheSheetAndInDataExports(): void
    {
        $this->createRawDataWorld();
        $id = model(DataExportModel::class)->insert([
            'requested_by' => 2, 'format' => 'csv', 'scope_json' => ['filters' => ['study_id' => 1], 'sheets' => ['peserta']], 'anonymized' => 1, 'status' => 'running',
        ], true);

        $export        = (new ExportService())->build((int) $id);
        $this->files[] = (string) $export['file_path'];

        $this->assertSame('done', $export['status'], (string) $export['error_message']);
        $this->assertSame(ResearchDictionary::VERSION, $export['dictionary_version']);
        $this->assertSame(2, (int) $export['participant_count']);

        $zip = new ZipArchive();
        $zip->open((string) $export['file_path']);
        $kamus = (string) $zip->getFromName('kamus.csv');

        $this->assertStringContainsString('"Versi kamus",,' . ResearchDictionary::VERSION, $kamus);
        $this->assertStringContainsString('"Kode penelitian",,STUDI-UJI', $kamus);
        $this->assertStringContainsString('"Definisi lengkap",,"11_KAMUS_DATA.md, versi yang sama"', $kamus);
        $this->assertStringContainsString('lembar,kolom,judul,arti,satuan,nilai,nama_kamus,analitik,rujukan_kamus', $kamus);
        $this->assertStringContainsString('11_KAMUS_DATA.md#variabel-peserta-dan-sesi', $kamus);
    }
}
