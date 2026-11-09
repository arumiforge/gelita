<?php

use App\Libraries\ExportLabels;
use App\Services\ExportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Keterangan kolom data mentah (Dok 3 §9.2): setiap kolom setiap lembar
 * punya judul Indonesia bersatuan, jenis, dan arti untuk Kamus Data; nilai
 * berlabel hanya mengganti kode, tidak mengolah angka.
 *
 * @internal
 */
final class ExportLabelsTest extends CIUnitTestCase
{
    private const TYPES = ['code', 'text', 'int', 'ms', 'datetime', 'bool', 'enum', 'json'];

    public function testEveryColumnOfEverySheetHasATitleTypeAndMeaning(): void
    {
        $export = new ExportService();

        foreach (ExportService::SHEETS as $sheet) {
            $columns = $export->columns($sheet, false, true);   // mode terlengkap
            $headers = ExportLabels::headers($sheet, $columns, true);

            $this->assertSame(count($columns), count(array_unique($headers)), "{$sheet}: judul kolom tidak boleh kembar");
            $this->assertSame($columns, ExportLabels::headers($sheet, $columns, false), "{$sheet}: judul berkode = kode kolom");

            foreach ($columns as $i => $column) {
                $spec = ExportLabels::column($sheet, $column);

                $this->assertTrue(ExportLabels::has($sheet, $column), "{$sheet}.{$column} belum punya keterangan");
                $this->assertNotSame($column, $headers[$i], "{$sheet}.{$column}");
                $this->assertDoesNotMatchRegularExpression('/_|\b(?:code|ms|id)\b/', $headers[$i], "{$sheet}.{$column}: judul masih berbentuk kode");
                $this->assertContains($spec['type'], self::TYPES, "{$sheet}.{$column}");
                $this->assertNotSame('', $spec['meaning'], "{$sheet}.{$column}: arti kosong");

                if ($spec['type'] === 'ms') {
                    $this->assertStringContainsString('(mdtk)', $headers[$i], "{$sheet}.{$column}: satuan di judul");
                }

                if ($spec['type'] === 'enum') {
                    $this->assertNotSame([], ExportLabels::values($spec['group']), "{$sheet}.{$column}: daftar nilai kosong");
                }
            }
        }
    }

    public function testSheetTitlesFollowTheDocumentAndFitExcel(): void
    {
        $titles = array_map(ExportLabels::sheetTitle(...), ExportService::SHEETS);

        $this->assertSame(['Kamus Data', 'Peserta', 'Sesi Lapangan', 'Sesi', 'Progres', 'Pengerjaan Tantangan', 'Jawaban Soal',
            'Riwayat Percobaan', 'Navigasi', 'Bantuan', 'Penyelesaian', 'Event Mentah', 'Kritik & Saran', 'Bank Soal', 'Kode Pilihan',
            // Part 9 (35 §10.1)
            'Turunan Peserta', 'Turunan Sesi', 'Turunan Tantangan', 'Turunan Pengerjaan', 'Turunan Butir', 'Turunan Percobaan', 'Turunan Pengamatan', 'Turunan Tes',
            // Part 8 (34 §11.4)
            'Jawaban Tes', 'Bagian Tes', 'Angket', 'Skala Sikap', 'Risiko Gabungan',
            'Izin Berkode', 'L1 Kondisi sesi', 'L1 Log penyimpangan', 'L2 Tempat duduk', 'L3 Bantuan dan gangguan', 'L4 Observasi', 'L5 Wawancara',
            'L6 Catatan sesi tes'], $titles);

        foreach ($titles as $title) {
            $this->assertLessThanOrEqual(31, mb_strlen($title));
            $this->assertDoesNotMatchRegularExpression('/[\[\]:*?\/\\\\]/', $title);
        }

        $this->assertSame(array_slice(ExportService::SHEETS, 0, 16 + 5 /* part 8 (34 §11.4) */ + 8 /* part 9 (35 §10.1) */), ExportLabels::sheets(), 'field sheets take their titles from FieldSheetSpec');
    }

    public function testLabelsReplaceCodesOnly(): void
    {
        $this->assertSame('Kesempatan habis', ExportLabels::label('pengerjaan', 'completion_reason', 'tries_exhausted'));
        $this->assertSame('Laki-laki', ExportLabels::label('peserta', 'gender', 'laki-laki'));
        $this->assertSame('Kelas 5 SD/MI', ExportLabels::label('peserta', 'class_level', '5'));
        $this->assertSame('ditinggalkan', ExportLabels::label('pengerjaan', 'status', 'abandoned'));
        $this->assertSame('Benar atau Salah', ExportLabels::label('pengerjaan', 'engine_type', 'boleh'));
        $this->assertSame('Menemukan informasi tersurat', ExportLabels::label('jawaban', 'skill', 'informasi_tersurat'));
        $this->assertSame('Petunjuk dibuka', ExportLabels::label('bantuan', 'jenis', 'petunjuk'));
        $this->assertSame('Cerita pembuka', ExportLabels::label('bantuan', 'audio_context', 'intro'));
        $this->assertSame(['ya', 'tidak'], [ExportLabels::label('jawaban', 'is_correct', 1), ExportLabels::label('jawaban', 'is_correct', 0)]);
        $this->assertSame('kode-baru', ExportLabels::label('pengerjaan', 'status', 'kode-baru'), 'kode tak dikenal apa adanya');

        // Angka, durasi, dan waktu tidak diolah
        $this->assertSame(120500, ExportLabels::label('pengerjaan', 'duration_ms', 120500));
        $this->assertSame('2026-10-01T08:12:00.000+07:00', ExportLabels::label('jawaban', 'answered_at', '2026-10-01T08:12:00.000+07:00'));
        $this->assertNull(ExportLabels::label('pengerjaan', 'completion_reason', null));
    }

    public function testDictionaryCoversEveryColumnWithBothCodesAndLabels(): void
    {
        $export  = new ExportService();
        $columns = [];

        foreach (ExportService::SHEETS as $sheet) {
            $columns[$sheet] = $export->columns($sheet, false, true);
        }

        $rows = ExportLabels::dictionary($columns);

        foreach ($columns as $sheet => $list) {
            $listed = array_column(array_filter($rows, static fn (array $r): bool => $r['lembar'] === $sheet && $r['kolom'] !== ''), 'kolom');
            $this->assertSame($list, $listed, "{$sheet}: kamus memuat semua kolom, urut sama");
        }

        $reason = array_values(array_filter($rows, static fn (array $r): bool => $r['lembar'] === 'pengerjaan' && $r['kolom'] === 'completion_reason'))[0];
        $this->assertStringContainsString('tries_exhausted = Kesempatan habis', $reason['nilai']);
        $this->assertStringContainsString('solved = Semua soal benar', $reason['nilai']);

        $duration = array_values(array_filter($rows, static fn (array $r): bool => $r['lembar'] === 'jawaban' && $r['kolom'] === 'duration_ms'))[0];
        $this->assertSame('mdtk', $duration['satuan']);

        $flag = array_values(array_filter($rows, static fn (array $r): bool => $r['lembar'] === 'jawaban' && $r['kolom'] === 'is_correct'))[0];
        $this->assertSame('1 = ya; 0 = tidak', $flag['nilai']);
    }

    public function testWidthsLeaveRoomForTimestampsAndText(): void
    {
        $widths = ExportLabels::widths('jawaban', ['answered_at', 'prompt', 'try_count'], true);

        $this->assertGreaterThanOrEqual(29, $widths[0], 'ISO 8601 dengan milidetik dan zona');
        $this->assertGreaterThanOrEqual(16, $widths[1]);
        $this->assertGreaterThanOrEqual(10, $widths[2]);
    }
}
