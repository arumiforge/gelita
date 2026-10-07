<?php

use App\Libraries\ExportLabels;
use App\Models\DataExportModel;
use App\Services\ExportService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\Database\RawDataWorld;

/**
 * Ekspor data mentah ujung ke ujung (Dok 3 §9, §12.1 `RawExportTest`):
 * lembar dan kolom §9.2, Kamus Data lengkap, mode berlabel/berkode,
 * CSV-ZIP terbaca (`ZipArchive` + `fgetcsv`), anonim tanpa kolom
 * identitas, guru tanpa Event Mentah dan kunci jawaban, ekspor per siswa
 * (§9.3), dan laporan PDF satu siswa (§10).
 *
 * @internal
 */
final class RawExportTest extends CIUnitTestCase
{
    use RawDataWorld;

    private const ADMIN   = 1;
    private const TEACHER = 2;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRawDataWorld();
        Services::resetSingle('rawDataService');
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCsvZipHasEverySheetWithCodedValuesAndADictionary(): void
    {
        $export = $this->build('csv', self::ADMIN, ['anonymized' => 0]);
        $sheets = $this->readZip($export['file_path']);

        $this->assertSame(array_map(static fn (string $s): string => $s . '.csv', ExportService::SHEETS), array_keys($sheets));

        $service = new ExportService();

        foreach (ExportService::SHEETS as $sheet) {
            $this->assertSame($service->columns($sheet, false, true), $sheets[$sheet . '.csv']['header'], "{$sheet}: kolom §9.2, judul = kode");
        }

        // Kamus Data: setiap kolom setiap lembar, dengan arti
        $dictionary = $sheets['kamus.csv']['rows'];

        foreach (ExportService::SHEETS as $sheet) {
            foreach ($service->columns($sheet, false, true) as $column) {
                $entry = array_values(array_filter($dictionary, static fn (array $r): bool => $r['lembar'] === $sheet && $r['kolom'] === $column));
                $this->assertCount(1, $entry, "kamus {$sheet}.{$column}");
                $this->assertNotSame('', $entry[0]['arti'], "kamus {$sheet}.{$column}: arti");
            }
        }

        // Nilai berkode, waktu ISO 8601 dengan milidetik, durasi milidetik
        $attempt = $this->only($sheets['pengerjaan.csv']['rows'], 'participant_code', 'GLT-000001');
        $this->assertSame(['solved', 'completed', 'boleh', 'tmg-3', 'temanggung', 'Candi Pringapus'], [
            $attempt['completion_reason'], $attempt['status'], $attempt['engine_type'], $attempt['node_ref'], $attempt['level'], $attempt['variant_title'],
        ]);
        $this->assertSame('2026-10-01T08:10:00.250+07:00', $attempt['started_at']);
        $this->assertSame('120500', $attempt['duration_ms']);

        $tries = $sheets['percobaan.csv']['rows'];
        $this->assertSame(['{"verdict":"salah"}', '{"verdict":"benar"}'], array_column($tries, 'answer'));
        $this->assertSame(['1200', ''], array_column($tries, 'first_input_ms'));

        $this->assertSame('{"verdict":"benar"}', $sheets['jawaban.csv']['rows'][0]['answer_key'] ?? null);
        $this->assertSame('Ani Lestari', $this->only($sheets['peserta.csv']['rows'], 'participant_code', 'GLT-000001')['display_name']);
        $this->assertSame('11111111', $this->only($sheets['peserta.csv']['rows'], 'participant_code', 'GLT-000001')['school_npsn']);
        $this->assertCount(4, $sheets['event.csv']['rows'], 'event yang ditandai hapus tidak ikut');
        $this->assertSame('=1+1', $sheets['masukan.csv']['rows'][0]['suggestion'], 'teks apa adanya');
        $this->assertStringNotContainsString('RAHASIA-HASH', implode('', array_map(static fn (array $s): string => $s['raw'], $sheets)));

        $this->assertSame(hash_file('sha256', $export['file_path']), $export['file_sha256']);
        $this->assertGreaterThan(0, (int) $export['row_count']);
    }

    public function testXlsxIsLabeledByDefaultWithIndonesianTitles(): void
    {
        $export = $this->build('xlsx', self::ADMIN, ['anonymized' => 1]);
        $book   = IOFactory::load($export['file_path']);

        $this->assertSame(array_map(ExportLabels::sheetTitle(...), ExportService::SHEETS), $book->getSheetNames());

        $attempts = $book->getSheetByName('Pengerjaan Tantangan')->toArray(null, false, false);
        $header   = $attempts[0];
        $row      = array_combine($header, $attempts[1]);

        $this->assertContains('Alasan selesai', $header);
        $this->assertSame('Semua soal benar', $row['Alasan selesai']);
        $this->assertSame('Temanggung', $row['Wilayah']);
        $this->assertSame('Benar atau Salah', $row['Jenis tantangan']);
        $this->assertSame(120500, (int) $row['Durasi (mdtk)']);

        $answers = $book->getSheetByName('Jawaban Soal')->toArray(null, false, false);
        $answer  = array_combine($answers[0], $answers[1]);
        $this->assertSame('ya', $answer['Benar di akhir']);
        $this->assertSame('Menemukan informasi tersurat', $answer['Keterampilan membaca']);
        $this->assertStringNotContainsString('{', (string) $answer['Jawaban pertama'], 'jawaban dalam kalimat');

        // Anonim: kolom identitas tidak dibuat, kode samaran sekolah tetap
        $people = $book->getSheetByName('Peserta')->toArray(null, false, false);
        $this->assertNotContains('Nama siswa', $people[0]);
        $this->assertNotContains('NPSN sekolah', $people[0]);
        $this->assertContains('Kode sekolah (samaran)', $people[0]);
    }

    public function testCodedXlsxAndLabeledCsvOnRequest(): void
    {
        $coded = IOFactory::load($this->build('xlsx', self::ADMIN, ['labels' => 'code'])['file_path']);
        $rows  = $coded->getSheetByName('Pengerjaan Tantangan')->toArray(null, false, false);
        $this->assertSame('completion_reason', $rows[0][array_search('completion_reason', $rows[0], true)]);
        $this->assertContains('solved', array_column(array_map(static fn (array $r): array => array_combine($rows[0], $r), array_slice($rows, 1)), 'completion_reason'));

        $labeled = $this->readZip($this->build('csv', self::ADMIN, ['labels' => 'label'])['file_path']);
        $this->assertContains('Alasan selesai', $labeled['pengerjaan.csv']['header']);
    }

    public function testTeacherExportIsScopedAnonymousWithoutRawEventsOrKeys(): void
    {
        $export = $this->build('csv', self::TEACHER, ['anonymized' => 0, 'filters' => ['school_id' => 2], 'sheets' => ['event', 'peserta', 'jawaban', 'butir']]);

        $this->assertSame(1, (int) $export['anonymized'], 'guru selalu anonim');

        $sheets = $this->readZip($export['file_path']);
        $this->assertSame(['kamus.csv', 'peserta.csv', 'jawaban.csv', 'butir.csv'], array_keys($sheets));

        $this->assertSame(['GLT-000001'], array_column($sheets['peserta.csv']['rows'], 'participant_code'), 'sekolah dipaksa dari staff_users');

        foreach ($sheets as $name => $sheet) {
            $this->assertNotContains('answer_key', $sheet['header'], $name);
            $this->assertNotContains('display_name', $sheet['header'], $name);
            $this->assertStringNotContainsString('Ani Lestari', $sheet['raw'], $name);
        }

        $this->assertStringNotContainsString('event', implode(',', array_column($sheets['kamus.csv']['rows'], 'lembar')));
    }

    public function testPerStudentExportFiltersOneParticipant(): void
    {
        $sheets = $this->readZip($this->build('csv', self::ADMIN, ['participant_id' => 2])['file_path']);

        $this->assertSame(['GLT-000002'], array_column($sheets['peserta.csv']['rows'], 'participant_code'));
        $this->assertSame(['GLT-000002'], array_column($sheets['sesi.csv']['rows'], 'participant_code'));
        $this->assertSame([], $sheets['masukan.csv']['rows']);
        $this->assertCount(2, $sheets['butir.csv']['rows'], 'bank soal tidak bergantung siswa');

        // Guru meminta siswa sekolah lain → lembar kosong, bukan bocor
        $other = $this->readZip($this->build('csv', self::TEACHER, ['participant_id' => 2])['file_path']);
        $this->assertSame([], $other['peserta.csv']['rows']);
    }

    public function testEventSheetLimitRejectsTooLargeExports(): void
    {
        $config                     = config('Gelita');
        $original                   = $config->exportMaxRawEvents;
        $config->exportMaxRawEvents = 1;

        try {
            $export = $this->build('xlsx', self::ADMIN, ['sheets' => ['navigasi']]);
        } finally {
            $config->exportMaxRawEvents = $original;
        }

        $this->assertSame('failed', $export['status']);
        $this->assertStringContainsString('Navigasi', (string) $export['error_message']);
    }

    public function testParticipantPdfHasRawTablesAndRespectsScope(): void
    {
        $context = (new ExportService())->context([
            'id' => 0, 'requested_by' => self::ADMIN, 'format' => 'pdf', 'anonymized' => 1,
            'scope_json' => ['template' => 'participant', 'participant_id' => 1],
        ]);
        $data = service('reportService')->participantData($context);
        $html = view('pdf/report-participant', $data, ['saveData' => false]);

        $this->assertStringContainsString('GLT-000001', $html);
        $this->assertStringNotContainsString('Ani Lestari', $html, 'anonim');
        $this->assertStringContainsString('Progres tantangan', $html);
        $this->assertStringContainsString('tmg-3', $html);
        $this->assertStringContainsString('0 dari 1', $html, 'percobaan pertama x dari y');
        $this->assertStringContainsString('Ceritanya', $html, 'kritik & saran');
        $this->assertDoesNotMatchRegularExpression('/skor|persetujuan wali|indikator/i', $html);

        $this->assertSame(['tmg-1', 'tmg-3'], array_column($data['profile']['progress'], 'node_ref'));
        $this->assertSame(['not_started', 'completed'], array_column($data['profile']['progress'], 'status'));

        $pdf = $this->build('pdf', self::ADMIN, ['participant_id' => 1]);
        $this->assertSame('done', $pdf['status'], (string) $pdf['error_message']);
        $this->assertStringStartsWith('%PDF', (string) file_get_contents((string) $pdf['file_path'], false, null, 0, 4));

        $teacher = (new ExportService())->context([
            'id' => 0, 'requested_by' => self::TEACHER, 'format' => 'pdf', 'anonymized' => 0,
            'scope_json' => ['template' => 'participant', 'participant_id' => 2],
        ]);

        $this->expectException(DomainException::class);
        service('reportService')->participantData($teacher);
    }

    // ------------------------------------------------------------ bantuan

    /**
     * @param array{anonymized?: int, labels?: string, sheets?: list<string>, filters?: array<string, mixed>, participant_id?: int} $options
     *
     * @return array<string, mixed> baris data_exports sesudah build
     */
    private function build(string $format, int $staffId, array $options = []): array
    {
        $scope = ['filters' => $options['filters'] ?? [], 'sheets' => $options['sheets'] ?? []];

        if (isset($options['labels'])) {
            $scope['labels'] = $options['labels'];
        }

        if (isset($options['participant_id'])) {
            $scope['participant_id'] = $options['participant_id'];
        }

        if ($format === 'pdf') {
            $scope['template'] = 'participant';
        }

        $id = model(DataExportModel::class)->insert([
            'requested_by' => $staffId, 'format' => $format, 'scope_json' => $scope,
            'anonymized' => $options['anonymized'] ?? 1, 'status' => 'running',
        ], true);

        $export = (new ExportService())->build((int) $id);

        if ($export['file_path'] !== null) {
            $this->files[] = (string) $export['file_path'];
        }

        if (($export['status'] ?? '') === 'done') {
            $this->assertFileExists((string) $export['file_path']);
        }

        return $export;
    }

    /** @return array<string, array{header: list<string>, rows: list<array<string, string>>, raw: string}> */
    private function readZip(string $path): array
    {
        $this->assertStringEndsWith('.zip', $path);

        $zip = new ZipArchive();
        $this->assertTrue($zip->open($path) === true);

        $out = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            $raw  = (string) $zip->getFromIndex($i);

            $this->assertStringStartsWith("\xEF\xBB\xBF", $raw, $name . ': BOM UTF-8');

            $handle = fopen('php://temp', 'w+b');
            fwrite($handle, substr($raw, 3));
            rewind($handle);

            $header = fgetcsv($handle, null, ',', '"', '');
            $rows   = [];

            while (($line = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $rows[] = array_combine($header, $line);
            }

            fclose($handle);

            $out[$name] = ['header' => $header, 'rows' => $rows, 'raw' => $raw];
        }

        $zip->close();

        return $out;
    }

    /** @param list<array<string, string>> $rows */
    private function only(array $rows, string $column, string $value): array
    {
        $match = array_values(array_filter($rows, static fn (array $r): bool => $r[$column] === $value));
        $this->assertCount(1, $match, "{$column} = {$value}");

        return $match[0];
    }
}
