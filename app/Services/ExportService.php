<?php

namespace App\Services;

use App\Libraries\ExcelWriter;
use App\Libraries\ExportLabels;
use App\Libraries\StaffAccess;
use App\Models\AuditLogModel;
use App\Models\DataExportModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Pembangun berkas ekspor data mentah (Dok 3 §9): workbook XLSX (satu
 * lembar per dimensi, `ExcelWriter` bertahap) atau CSV-ZIP (satu CSV per
 * lembar, UTF-8 dengan BOM, pemisah koma), keduanya dengan lembar Kamus
 * Data. Laporan PDF satu siswa diteruskan ke ReportService.
 *
 * Isi baris berasal dari RawDataService; service ini hanya memilih lembar,
 * kolom, mode nilai (berlabel/berkode), dan menulis berkas.
 *
 * Lapis ketiga otorisasi: hak pemohon dibaca ulang dari `staff_users`,
 * bukan dari isi formulir (`context()` → StaffScope). Pemohon non-admin
 * selalu dibatasi sekolahnya (tanpa sekolah → 0, tidak cocok apa pun),
 * selalu anonim, tanpa lembar `event` dan tanpa kolom `answer_key`.
 */
class ExportService
{
    /** Urutan resmi lembar (§9.2). `kamus` selalu ikut. */
    public const SHEETS = [
        'kamus', 'peserta', 'sesi', 'perangkat', 'progres', 'pengerjaan', 'jawaban', 'percobaan',
        'navigasi', 'bantuan', 'selesai', 'event', 'masukan', 'butir',
    ];

    public const ADMIN_ONLY_SHEETS = RawDataService::ADMIN_ONLY_DIMENSIONS;

    public const IDENTITY_COLUMNS = RawDataService::IDENTITY_COLUMNS;

    public const ADMIN_ONLY_COLUMNS = RawDataService::ADMIN_ONLY_COLUMNS;

    /** Kunci filter yang diterima dari formulir / scope_json (§9.2). */
    public const FILTER_KEYS = [
        'study_id', 'level_id', 'node_id', 'school_id', 'province_code', 'locale', 'date_from', 'date_to',
    ];

    /** `data_exports.format`: xlsx = workbook, csv = CSV-ZIP, pdf = laporan satu siswa. */
    public const FORMATS = ['xlsx', 'csv', 'pdf'];

    /** Lembar yang jumlah barisnya dibatasi `Config\Gelita::$exportMaxRawEvents`. */
    public const EVENT_SHEETS = ['event', 'navigasi', 'selesai'];

    private const DICTIONARY_COLUMNS = ['lembar', 'kolom', 'judul', 'arti', 'satuan', 'nilai'];

    /**
     * Membangun berkas untuk satu baris data_exports berstatus queued/running.
     * Tidak melempar exception: kegagalan dicatat sebagai status `failed`.
     *
     * @return array<string, mixed> baris data_exports setelah build
     */
    public function build(int $exportId): array
    {
        $exports = model(DataExportModel::class);
        $export  = $exports->find($exportId);

        if ($export === null) {
            throw new \RuntimeException("Export {$exportId} tidak ditemukan.");
        }

        if (! in_array($export['status'], ['queued', 'running'], true)) {
            return $export;
        }

        $path = null;

        try {
            $context = $this->context($export);

            if ((int) $export['anonymized'] !== (int) $context['anonymized']) {
                $exports->update($exportId, ['anonymized' => $context['anonymized'] ? 1 : 0]);
            }

            $exports->update($exportId, ['status' => 'running']);

            $path = $this->targetPath($exportId, (string) $export['format']);

            ['sha' => $sha, 'rows' => $rows] = match ($export['format']) {
                'xlsx'  => $this->writeXlsx($path, $context),
                'csv'   => $this->writeCsvZip($path, $context),
                'pdf'   => service('reportService')->render($path, $context),
                default => throw new \DomainException("Jenis berkas '{$export['format']}' belum didukung."),
            };

            $exports->markDone($exportId, $path, $sha, $rows);
        } catch (\Throwable $e) {
            if ($path !== null && is_file($path)) {
                unlink($path);
            }

            log_message('error', 'Export {id} gagal: {msg}', ['id' => $exportId, 'msg' => $e->getMessage()]);

            $message = $e instanceof \DomainException || $e instanceof \OverflowException
                ? $e->getMessage()
                : 'Terjadi kesalahan saat membuat berkas. Coba lagi; bila tetap gagal, hubungi petugas teknis.';

            $exports->markFailed($exportId, $message);
        }

        return $exports->find($exportId);
    }

    /**
     * Kolom satu lembar menurut mode anonim dan role pemohon.
     *
     * @return list<string>
     */
    public function columns(string $sheet, bool $anonymized, bool $fullDetail): array
    {
        if ($sheet === 'kamus') {
            return self::DICTIONARY_COLUMNS;
        }

        $scope = StaffScope::of($anonymized, $fullDetail);

        return $this->rawData()->columns($sheet, $scope);
    }

    /**
     * Lembar yang boleh diterima pemohon, urutan resmi, Kamus Data selalu
     * pertama. Daftar kosong = semua lembar yang boleh.
     *
     * @param list<string> $requested
     *
     * @return list<string>
     */
    public function allowedSheets(array $requested, bool $fullDetail): array
    {
        $allowed = $fullDetail ? self::SHEETS : array_values(array_diff(self::SHEETS, self::ADMIN_ONLY_SHEETS));
        $picked  = array_values(array_intersect($allowed, array_map('strval', $requested)));

        if ($picked === [] || $picked === ['kamus']) {
            return $allowed;
        }

        return in_array('kamus', $picked, true) ? $picked : array_merge(['kamus'], $picked);
    }

    /**
     * Filter dari scope_json yang disaring: hanya kunci resmi dan tanggal
     * berformat Y-m-d. There is no school scope since revision 10 part 1.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, int|string>
     */
    public function sanitizeFilters(array $filters): array
    {
        return $this->rawData()->sanitizeFilters(array_intersect_key($filters, array_flip(self::FILTER_KEYS)));
    }

    /**
     * Hak dan cakupan ekspor, dibaca ulang dari staff_users.
     *
     * @return array{export_id: int, format: string, filters: array<string, int|string>, sheets: list<string>,
     *               anonymized: bool, labeled: bool, full_detail: bool, scope: StaffScope,
     *               template: ?string, participant_id: ?int, requested_by: int, requester: string}
     */
    public function context(array $export): array
    {
        $staff = $this->db()->table('staff_users')
            ->select('id, username, display_name, role, is_active')
            ->where('id', (int) $export['requested_by'])
            ->get()
            ->getRowArray();

        if ($staff === null || ! (int) $staff['is_active']) {
            throw new \DomainException('Akun yang meminta unduhan ini tidak ditemukan atau sudah dinonaktifkan.');
        }

        // Only `research.export` (ketua) downloads, always coded (27 K14)
        if (! StaffAccess::allows((string) $staff['role'], 'research.export')) {
            throw new \DomainException('Akun yang meminta unduhan ini tidak berhak mengunduh data penelitian.');
        }

        $scope = StaffScope::fromStaff($staff);

        $json = is_array($export['scope_json'])
            ? $export['scope_json']
            : (json_decode((string) $export['scope_json'], true) ?: []);

        $format        = (string) $export['format'];
        $participantId = (int) ($json['participant_id'] ?? 0);
        $filters       = $this->sanitizeFilters((array) ($json['filters'] ?? []));

        // Ekspor per siswa (§9.3): lembar yang sama, difilter satu peserta
        if ($participantId > 0) {
            $filters['participant_id'] = $participantId;
        }

        $labels = (string) ($json['labels'] ?? '');

        return [
            'export_id'      => (int) $export['id'],
            'format'         => $format,
            'filters'        => $filters,
            'sheets'         => $this->allowedSheets((array) ($json['sheets'] ?? []), $scope->fullDetail),
            'anonymized'     => $scope->anonymous,
            'labeled'        => $labels === '' ? $format === 'xlsx' : $labels === 'label',
            'full_detail'    => $scope->fullDetail,
            'scope'          => $scope,
            'template'       => ($json['template'] ?? null) === 'participant' ? 'participant' : null,
            'participant_id' => $participantId > 0 ? $participantId : null,
            'requested_by'   => (int) $staff['id'],
            'requester'      => (string) ($staff['display_name'] ?: $staff['username']),
        ];
    }

    // ------------------------------------------------------------- berkas

    /**
     * @param array<string, mixed> $context hasil context()
     *
     * @return array{sha: string, rows: int}
     */
    public function writeXlsx(string $path, array $context): array
    {
        $this->assertEventVolume($context);

        $writer = new ExcelWriter($path);
        $rows   = 0;

        try {
            foreach ($context['sheets'] as $sheet) {
                $columns = $this->columns($sheet, $context['anonymized'], $context['full_detail']);

                $writer->startSheet(
                    ExportLabels::sheetTitle($sheet),
                    ExportLabels::headers($sheet, $columns, $context['labeled']),
                    ExportLabels::widths($sheet, $columns, $context['labeled']),
                );

                foreach ($this->sheetRows($sheet, $context) as $row) {
                    $writer->row(array_map(static fn (string $c): mixed => $row[$c] ?? null, $columns));

                    if ($sheet !== 'kamus') {
                        $rows++;
                    }
                }
            }

            $sha = $writer->finish();
        } catch (\Throwable $e) {
            $writer->abort();

            throw $e;
        }

        return ['sha' => $sha, 'rows' => $rows];
    }

    /**
     * ZIP berisi `{lembar}.csv` per lembar (UTF-8 + BOM, pemisah koma, judul
     * kolom = kode kolom pada mode berkode).
     *
     * @param array<string, mixed> $context hasil context()
     *
     * @return array{sha: string, rows: int}
     */
    public function writeCsvZip(string $path, array $context): array
    {
        $this->assertEventVolume($context);

        $dir = dirname($path);

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException("Folder tujuan export tidak dapat dibuat: {$dir}");
        }

        $tmpDir = $dir . DIRECTORY_SEPARATOR . '.tmp-' . bin2hex(random_bytes(6));

        if (! mkdir($tmpDir, 0700)) {
            throw new \RuntimeException('Folder sementara export tidak dapat dibuat.');
        }

        $files = [];
        $rows  = 0;

        try {
            foreach ($context['sheets'] as $sheet) {
                $columns = $this->columns($sheet, $context['anonymized'], $context['full_detail']);
                $file    = $tmpDir . DIRECTORY_SEPARATOR . $sheet . '.csv';
                $handle  = fopen($file, 'wb');

                if ($handle === false) {
                    throw new \RuntimeException('Berkas sementara CSV tidak dapat dibuka.');
                }

                fwrite($handle, "\xEF\xBB\xBF");
                RawDataService::writeCsvLine($handle, ExportLabels::headers($sheet, $columns, $context['labeled']));

                foreach ($this->sheetRows($sheet, $context) as $row) {
                    RawDataService::writeCsvLine($handle, array_map(static fn (string $c): mixed => $row[$c] ?? null, $columns));

                    if ($sheet !== 'kamus') {
                        $rows++;
                    }
                }

                fclose($handle);
                $files[$sheet . '.csv'] = $file;
            }

            if (is_file($path)) {
                unlink($path);
            }

            $zip = new \ZipArchive();

            if ($zip->open($path, \ZipArchive::CREATE | \ZipArchive::EXCL) !== true) {
                throw new \RuntimeException('Berkas ZIP tidak dapat dibuat.');
            }

            foreach ($files as $name => $file) {
                $zip->addFile($file, $name);
            }

            if (! $zip->close()) {
                throw new \RuntimeException('Berkas ZIP gagal ditutup.');
            }
        } finally {
            array_map('unlink', glob($tmpDir . DIRECTORY_SEPARATOR . '*') ?: []);
            rmdir($tmpDir);
        }

        $sha = hash_file('sha256', $path);

        if ($sha === false) {
            throw new \RuntimeException('SHA-256 berkas export tidak dapat dihitung.');
        }

        return ['sha' => $sha, 'rows' => $rows];
    }

    /**
     * Baris satu lembar (kolom → nilai siap tulis).
     *
     * @param array<string, mixed> $context
     *
     * @return iterable<array<string, int|string|null>>
     */
    public function sheetRows(string $sheet, array $context): iterable
    {
        if ($sheet === 'kamus') {
            $columns = [];

            foreach ($context['sheets'] as $other) {
                $columns[$other] = $this->columns($other, $context['anonymized'], $context['full_detail']);
            }

            return ExportLabels::dictionary($columns);
        }

        return $this->rawData()->rows($sheet, $context['filters'], $context['scope'], $context['labeled']);
    }

    /**
     * Hitung baris lembar event sebelum menulis; di atas ambang, ekspor ditolak.
     *
     * @param array<string, mixed> $context
     */
    public function assertEventVolume(array $context): void
    {
        $limit = config('Gelita')->exportMaxRawEvents;

        foreach (array_intersect(self::EVENT_SHEETS, $context['sheets']) as $sheet) {
            $count = $this->rawData()->count($sheet, $context['filters'], $context['scope']);

            if ($count > $limit) {
                throw new \OverflowException(sprintf(
                    'Lembar %s berisi sekitar %s baris, melebihi batas %s. Persempit rentang tanggal atau pilihan data, lalu coba lagi.',
                    ExportLabels::sheetTitle($sheet),
                    number_format($count, 0, ',', '.'),
                    number_format($limit, 0, ',', '.'),
                ));
            }
        }
    }

    /** writable/exports/{id}-{timestamp}.{xlsx|zip|pdf} — di luar public/. */
    private function targetPath(int $exportId, string $format): string
    {
        $extension = $format === 'csv' ? 'zip' : $format;

        return WRITEPATH . 'exports' . DIRECTORY_SEPARATOR . $exportId . '-' . date('Ymd-His') . '.' . $extension;
    }

    /**
     * Membuang berkas export kedaluwarsa. Dipakai RetentionService.
     *
     * @return list<int> id export yang berkasnya dibuang
     */
    public function purgeExpired(?int $staffId = null): array
    {
        $exports = model(DataExportModel::class);
        $removed = [];

        foreach ($exports->expired() as $export) {
            $path = (string) ($export['file_path'] ?? '');

            if ($path === '') {
                continue;   // berkasnya sudah dibuang pada retensi sebelumnya
            }

            if (is_file($path) && $this->isInsideExportDir($path)) {
                unlink($path);
            }

            // `status` tetap `done`: daftar statusnya ditetapkan skema
            // (queued|running|done|failed). Yang hilang adalah berkasnya.
            $exports->update((int) $export['id'], [
                'file_path'     => null,
                'error_message' => 'Berkas sudah dihapus otomatis karena melewati batas waktu.',
            ]);

            $removed[] = (int) $export['id'];
        }

        if ($removed !== []) {
            model(AuditLogModel::class)->record('export_purge', [
                'staff_user_id' => $staffId,
                'target_type'   => 'data_export',
                'metadata'      => ['export_ids' => $removed],
            ]);
        }

        return $removed;
    }

    /**
     * Part 4 (30 §7.4): a parent's request purges every download file still on
     * the server that was made since the student registered ($since). The row
     * stays, with `file_path` empty and `purged_by_request_id` set.
     *
     * @return list<int> export ids purged
     */
    public function purgeForRequest(int $requestId, string $since, ?int $staffId = null): array
    {
        $exports = model(DataExportModel::class);
        $removed = [];

        foreach ($exports->where('file_path IS NOT NULL')->where('file_path !=', '')->where('created_at >=', $since)->findAll() as $export) {
            $path = (string) $export['file_path'];

            if (is_file($path) && $this->isInsideExportDir($path)) {
                unlink($path);
            }

            $exports->update((int) $export['id'], [
                'file_path'            => null,
                'purged_by_request_id' => $requestId,
                'error_message'        => 'Berkas sudah dihapus karena permintaan hapus data orang tua.',
            ]);
            $removed[] = (int) $export['id'];
        }

        model(AuditLogModel::class)->record('export_purge', [
            'staff_user_id' => $staffId,
            'target_type'   => 'data_deletion_request',
            'target_id'     => (string) $requestId,
            'metadata'      => ['export_ids' => $removed, 'request_id' => $requestId],
        ]);

        return $removed;
    }

    /** Jalur berkas harus berada di writable/exports/ — pertahanan terhadap file_path yang rusak. */
    public function isInsideExportDir(string $path): bool
    {
        $dir  = realpath(WRITEPATH . 'exports');
        $real = realpath($path);

        return $dir !== false && $real !== false && str_starts_with($real, $dir . DIRECTORY_SEPARATOR);
    }

    /** MIME berkas unduhan per format. */
    public static function mimeType(string $format): string
    {
        return match ($format) {
            'pdf'   => 'application/pdf',
            'csv'   => 'application/zip',
            default => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        };
    }

    private function rawData(): RawDataService
    {
        return service('rawDataService');
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
