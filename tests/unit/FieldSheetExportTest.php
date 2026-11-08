<?php

use App\Models\DataExportModel;
use App\Services\ExportService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\FieldSheetWorld;

/**
 * The imported field sheets in the `ketua` download (research revision 10,
 * part 3: 29 §7.2, §10): sheets l1_kondisi … l6_sesi, only the main import
 * that counts.
 *
 * @internal
 */
final class FieldSheetExportTest extends CIUnitTestCase
{
    use FieldSheetWorld;

    public function testFieldSheetsInTheDownloadOnlyTheCountingImport(): void
    {
        $fieldSession = $this->createFieldSheetWorld(2);
        $this->conn->table('staff_users')->insert(['username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1]);
        $ketua = (int) $this->conn->insertID();
        $file  = fn (string $code): string => $this->csv('l1_penyimpangan.csv', [
            ['start_time', 'end_time', 'deviation_code', 'description', 'affected_seats', 'action'],
            ['08:01:10', '08:04:20', $code, 'Wi-Fi putus', 'semua', '—'],
        ]);

        foreach (['DEV-LIS', 'DEV-NET'] as $code) {
            $this->sheets()->save($fieldSession, $this->sheets()->preview($fieldSession, $file($code), 'l1_penyimpangan.csv', 'main'), 2, false);
        }

        $this->assertSame(['kamus', 'peserta'], array_slice(ExportService::SHEETS, 0, 2));
        $this->assertSame(['l1_kondisi', 'l1_penyimpangan', 'l2_kursi', 'l3_bantuan', 'l4_bromp', 'l5_wawancara', 'l6_sesi'], array_slice(ExportService::SHEETS, -7), 'field sheets last (29 §19 no. 13)');

        $id     = model(DataExportModel::class)->insert(['requested_by' => $ketua, 'format' => 'csv', 'scope_json' => ['filters' => ['study_id' => 1], 'sheets' => ['l1_penyimpangan']], 'anonymized' => 1, 'status' => 'running'], true);
        $export = (new ExportService())->build((int) $id);
        $this->tempFiles[] = (string) $export['file_path'];
        $this->assertSame('done', $export['status'], (string) $export['error_message']);

        $zip = new ZipArchive();
        $zip->open((string) $export['file_path']);
        $csv = (string) $zip->getFromName('l1_penyimpangan.csv');
        $zip->close();

        $this->assertStringContainsString('field_session_code,sheet_row,start_time,end_time,deviation_code', $csv);
        $this->assertStringContainsString('S03-A-P2,2,08:01:10,08:04:20,DEV-NET', $csv);
        $this->assertStringNotContainsString('DEV-LIS', $csv, 'the superseded import is history only');
    }
}
