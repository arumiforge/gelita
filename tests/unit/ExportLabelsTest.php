<?php

use App\Libraries\ExcelWriter;
use App\Libraries\ExportLabels;
use App\Services\ExportService;
use CodeIgniter\Test\CIUnitTestCase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Berkas Excel unduhan data seluruhnya berbahasa Indonesia sehari-hari:
 * nama lembar, judul kolom, dan nilai berkode. Kode internal (nama lembar
 * Inggris, nama kolom snake_case, kode status/tahap) tidak boleh muncul di
 * berkas.
 *
 * @internal
 */
final class ExportLabelsTest extends CIUnitTestCase
{
    public function testEveryColumnOfEverySheetHasAnIndonesianTitle(): void
    {
        $export = new ExportService();

        foreach (ExportService::SHEETS as $sheet) {
            $columns = $export->columns($sheet, false, true);   // mode terlengkap
            $headers = ExportLabels::headers($sheet, $columns);

            $this->assertSame(count($columns), count(array_unique($headers)), "{$sheet}: judul kolom tidak boleh kembar");

            foreach ($columns as $i => $column) {
                $this->assertTrue(ExportLabels::has($sheet, $column), "{$sheet}.{$column} belum punya judul");
                $this->assertNotSame($column, $headers[$i], "{$sheet}.{$column}");
                $this->assertDoesNotMatchRegularExpression('/_|\b(?:code|ms|id)\b/', $headers[$i], "{$sheet}.{$column}: judul masih berbentuk kode");
            }
        }
    }

    public function testSheetTitlesAreIndonesianAndFitExcel(): void
    {
        $titles = array_map(ExportLabels::sheetTitle(...), ExportService::SHEETS);

        $this->assertSame(['Data siswa', 'Sesi bermain', 'Hasil per wilayah', 'Hasil per tantangan', 'Jawaban per soal',
            'Catatan aktivitas', 'Pemakaian suara', 'Capaian indikator', 'Ringkasan kelompok', 'Kritik dan saran'], $titles);

        foreach ($titles as $title) {
            $this->assertLessThanOrEqual(31, mb_strlen($title));
            $this->assertDoesNotMatchRegularExpression('/[\[\]:*?\/\\\\]/', $title);
        }

        $this->assertSame(count($titles), count(array_unique(array_map('mb_strtolower', $titles))));
    }

    public function testTechnicalColumnsAreGone(): void
    {
        $export = new ExportService();
        $all    = [];

        foreach (ExportService::SHEETS as $sheet) {
            $all = [...$all, ...$export->columns($sheet, false, true)];
        }

        foreach (['response_id', 'event_uuid', 'client_event_id', 'audio_asset_id', 'indicator_code', 'variant_code',
            'content_version', 'scoring_version', 'level_code', 'study_code'] as $technical) {
            $this->assertNotContains($technical, $all, "kolom teknis {$technical} tidak lagi diunduh");
        }

        foreach (['node_title', 'prompt', 'bloom_level', 'item_types', 'level'] as $readable) {
            $this->assertContains($readable, $all, "kolom {$readable} membantu membaca berkas tanpa kamus kode");
        }
    }

    public function testCodedValuesBecomeWords(): void
    {
        $this->assertSame('Tes awal', ExportLabels::value('Sessions', 'phase_code', 'pretest'));
        $this->assertSame('Bermain biasa', ExportLabels::value('Feedback', 'phase_code', 'umum'));
        $this->assertSame('selesai', ExportLabels::value('Sessions', 'status', 'completed'));
        $this->assertSame('ponsel', ExportLabels::value('Sessions', 'device_type', 'mobile'));
        $this->assertSame('Indonesia', ExportLabels::value('Sessions', 'locale', 'id'));
        $this->assertSame('Perempuan', ExportLabels::value('Participants', 'gender', 'perempuan'));
        $this->assertSame('Kelas 5 SD/MI', ExportLabels::value('Participants', 'class_level', '5'));
        $this->assertSame('Isi rumpang (pilih kata)', ExportLabels::value('Item Responses', 'interaction_type', 'fill_blank_bank'));
        $this->assertSame('C3 · Menerapkan', ExportLabels::value('Item Responses', 'bloom_level', 'C3'));
        $this->assertSame('Susun Gambar', ExportLabels::value('Challenge Summary', 'engine_type', 'puzzle'));
        $this->assertSame('Urutkan kartu', ExportLabels::value('Challenge Summary', 'item_types', ['ordering', 'ordering']));
        $this->assertSame('Membuka petunjuk', ExportLabels::value('Raw Events', 'event_type', 'hint_opened'));
        $this->assertSame('diputar ulang', ExportLabels::value('Audio Usage', 'action', 'replay'));
        $this->assertSame('Mbah Kedu', ExportLabels::value('Audio Usage', 'character_code', 'mbah_kedu'));
        $this->assertSame('Cerita pembuka', ExportLabels::value('Audio Usage', 'context_code', 'intro'));
        $this->assertSame('Jenis kelamin', ExportLabels::value('Demographic Summary', 'dimension', 'gender'));
    }

    public function testFlagsUnitsAndRatios(): void
    {
        $this->assertSame('ya', ExportLabels::value('Item Responses', 'is_correct', 1));
        $this->assertSame('tidak', ExportLabels::value('Item Responses', 'first_pass_correct', '0'));
        $this->assertSame('tidak', ExportLabels::value('Sessions', 'is_touch', 0));
        $this->assertNull(ExportLabels::value('Item Responses', 'is_correct', null), 'belum dinilai = sel kosong');

        $this->assertSame(12.5, ExportLabels::value('Item Responses', 'duration_ms', 12_480), 'detik, 1 desimal');
        $this->assertSame(2.5, ExportLabels::value('Sessions', 'duration_ms', 150_000), 'lama sesi dalam menit');
        $this->assertSame(66.7, ExportLabels::value('Indicators', 'mastery_ratio', 0.6667), 'rasio 0–1 → persen');
        $this->assertSame(80.0, ExportLabels::value('Challenge Summary', 'first_pass_accuracy', '80.00'), 'sudah persen, tetap');
        $this->assertSame(7, ExportLabels::value('Challenge Summary', 'hint_count', '7'));
    }

    public function testDemographicGroupsFollowTheirDimension(): void
    {
        $this->assertSame('Laki-laki', ExportLabels::value('Demographic Summary', 'group', 'laki-laki', ['dimension' => 'gender']));
        $this->assertSame('Kelas 5 SD/MI', ExportLabels::value('Demographic Summary', 'group', '5', ['dimension' => 'class_level']));
        $this->assertSame('Tes akhir', ExportLabels::value('Demographic Summary', 'group', 'posttest', ['dimension' => 'phase']));
        $this->assertSame('Jawa Tengah', ExportLabels::value('Demographic Summary', 'group', 'Jawa Tengah', ['dimension' => 'province']));
        $this->assertSame('(tidak diisi)', ExportLabels::value('Demographic Summary', 'group', '(kosong)', ['dimension' => 'gender']));
    }

    public function testActivityDetailsAndDatesAreReadable(): void
    {
        $details = ExportLabels::value('Raw Events', 'payload', '{"attempt_no":2,"via":"content_changed","duration_ms":4200}');

        $this->assertSame('percobaan ke: 2 · lewat: isi tantangan diperbarui · lama: 4,2 detik', $details);

        $date = ExportLabels::value('Sessions', 'started_at', '2026-10-05 08:30:15');

        $this->assertInstanceOf(DateTimeInterface::class, $date);
        $this->assertSame('2026-10-05 08:30:15', $date->format('Y-m-d H:i:s'));
    }

    public function testWidthsLeaveRoomForDatesAndLongText(): void
    {
        $columns = ['answered_at', 'prompt', 'status'];
        $widths  = array_combine($columns, ExportLabels::widths('Item Responses', $columns));

        $this->assertGreaterThanOrEqual(20, $widths['answered_at'], 'tanggal tidak menjadi ####');
        $this->assertSame(50, $widths['prompt']);
        $this->assertGreaterThanOrEqual(10, $widths['status']);
    }

    public function testDatesAreRealExcelDatesInLocalTime(): void
    {
        $dir  = sys_get_temp_dir() . '/gelita-labels-' . bin2hex(random_bytes(4));
        $path = $dir . '/tanggal.xlsx';
        mkdir($dir);

        try {
            $writer = new ExcelWriter($path);
            $writer->startSheet('Sesi bermain', ['Kode sesi', 'Mulai'], [12, 20]);
            $writer->row(['S-1', new DateTimeImmutable('2026-10-05 08:30:15', new DateTimeZone('Asia/Jakarta'))]);
            $writer->finish();

            $sheet = IOFactory::load($path)->getSheetByName('Sesi bermain');
            $cell  = $sheet->getCell('B2');

            $this->assertTrue(ExcelDate::isDateTime($cell), 'sel tanggal, bukan teks');
            $this->assertSame('dd-mm-yyyy hh:mm:ss', $cell->getStyle()->getNumberFormat()->getFormatCode());
            $this->assertSame('2026-10-05 08:30:15', ExcelDate::excelToDateTimeObject($cell->getValue())->format('Y-m-d H:i:s'), 'jam setempat tidak bergeser');
            $this->assertSame(20.0, (float) $sheet->getColumnDimension('B')->getWidth());
        } finally {
            array_map('unlink', glob($dir . '/*') ?: []);
            rmdir($dir);
        }
    }
}
