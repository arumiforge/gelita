<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Memasang daftar sekolah resmi (docs/sekolah/, `sekolah-jateng.csv`) ke
 * tabel `schools`: satu baris per NPSN, `is_verified = 1`.
 *
 * - Berkas diperiksa seluruhnya dulu; satu baris rusak = tidak ada yang ditulis.
 * - Hanya menulis yang berubah: impor ulang berkas yang sama = 0 perubahan,
 *   jadi aman dijalankan di setiap deploy.
 * - Tidak pernah menghapus: siswa atau guru mungkin sudah tertaut. NPSN resmi
 *   yang hilang dari berkas hanya dilaporkan.
 * - Entri ketikan siswa (`is_verified = 0`) tidak disentuh, kecuali kunci
 *   `match_key`-nya disegarkan bila aturan SchoolName berubah.
 */
final class SchoolImporter
{
    public const DEFAULT_FILE = APPPATH . 'Database/Seeds/data/sekolah-jateng.csv';

    public const COLUMNS = ['npsn', 'nama', 'bentuk', 'jenjang', 'status', 'kode_kabupaten', 'kecamatan', 'desa'];

    private const STAGES = ['sd', 'smp', 'slb'];

    private const BATCH = 500;

    /** Kolom yang dibandingkan untuk menentukan "berubah". */
    private const COMPARED = [
        'name', 'level', 'stage', 'status', 'country_code', 'province_code', 'district_code',
        'subdistrict_name', 'village_name', 'is_verified', 'is_active', 'match_key',
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * @return array{total: int, added: int, updated: int, unchanged: int, missing: list<string>, rekeyed: int}
     */
    public function import(string $file = self::DEFAULT_FILE, bool $dryRun = false): array
    {
        $rows      = $this->read($file);
        $provinces = array_values(array_unique(array_column($rows, 'province_code')));
        $existing  = [];

        foreach ($this->db->table('schools')->select('id, code, ' . implode(', ', self::COMPARED))->where('code IS NOT NULL')->get()->getResultArray() as $row) {
            $existing[(string) $row['code']] = $row;
        }

        $insert    = [];
        $update    = [];
        $unchanged = 0;

        foreach ($rows as $code => $data) {
            $current = $existing[(string) $code] ?? null;

            if ($current === null) {
                $insert[] = $data;
            } elseif ($this->differs($current, $data)) {
                $update[] = ['id' => (int) $current['id']] + $data;
            } else {
                $unchanged++;
            }
        }

        $missing = [];

        foreach ($existing as $code => $row) {
            if ((int) $row['is_verified'] === 1 && in_array((string) $row['province_code'], $provinces, true) && ! isset($rows[(string) $code])) {
                $missing[] = (string) $code;
            }
        }

        $rekey = $this->staleTypedKeys();

        if (! $dryRun) {
            $this->db->transBegin();

            try {
                foreach (array_chunk($insert, self::BATCH) as $chunk) {
                    $this->db->table('schools')->insertBatch($chunk);
                }

                foreach (array_chunk($update, self::BATCH) as $chunk) {
                    $this->db->table('schools')->updateBatch($chunk, 'id');
                }

                foreach ($rekey as $id => $key) {
                    $this->db->table('schools')
                        ->set('match_key', $key)
                        ->set('updated_at', 'updated_at', false)
                        ->where('id', $id)
                        ->update();
                }

                $this->db->transCommit();
            } catch (\Throwable $e) {
                $this->db->transRollback();

                throw $e;
            }
        }

        return [
            'total'     => count($rows),
            'added'     => count($insert),
            'updated'   => count($update),
            'unchanged' => $unchanged,
            'missing'   => $missing,
            'rekeyed'   => count($rekey),
        ];
    }

    /**
     * Isi berkas sebagai baris `schools`, diperiksa lengkap.
     *
     * @return array<string, array<string, mixed>> NPSN => kolom `schools`
     */
    public function read(string $file): array
    {
        $handle = is_file($file) ? fopen($file, 'rb') : false;

        if ($handle === false) {
            throw new \RuntimeException("Berkas sekolah tidak ditemukan: {$file}");
        }

        try {
            $header = fgetcsv($handle, null, ',', '"', '');

            if ($header !== self::COLUMNS) {
                throw new \RuntimeException('Kolom berkas sekolah harus: ' . implode(',', self::COLUMNS));
            }

            $rows   = [];
            $errors = [];
            $line   = 1;

            while (($cells = fgetcsv($handle, null, ',', '"', '')) !== false) {
                $line++;

                if ($cells === [null]) {
                    continue; // baris kosong
                }

                if (count($cells) !== count(self::COLUMNS)) {
                    $errors[] = "baris {$line}: " . count($cells) . ' kolom';

                    continue;
                }

                $cell  = array_combine(self::COLUMNS, array_map(static fn ($value): string => trim((string) $value), $cells));
                $error = $this->rowError($cell, $rows);

                if ($error !== null) {
                    $errors[] = "baris {$line}: {$error}";

                    continue;
                }

                $rows[$cell['npsn']] = [
                    'code'             => $cell['npsn'],
                    'name'             => $cell['nama'],
                    'level'            => $cell['bentuk'] !== '' ? $cell['bentuk'] : null,
                    'stage'            => $cell['jenjang'],
                    'status'           => $cell['status'] !== '' ? $cell['status'] : null,
                    'country_code'     => 'ID',
                    'province_code'    => substr($cell['kode_kabupaten'], 0, 2),
                    'district_code'    => $cell['kode_kabupaten'],
                    'subdistrict_name' => $cell['kecamatan'] !== '' ? $cell['kecamatan'] : null,
                    'village_name'     => $cell['desa'] !== '' ? $cell['desa'] : null,
                    'is_verified'      => 1,
                    'is_active'        => 1,
                    'match_key'        => SchoolName::key($cell['nama']),
                ];
            }
        } finally {
            fclose($handle);
        }

        if ($errors !== []) {
            throw new \RuntimeException(sprintf(
                "Berkas sekolah ditolak (%d galat), tidak ada yang ditulis:\n - %s",
                count($errors),
                implode("\n - ", array_slice($errors, 0, 10)),
            ));
        }

        return $rows;
    }

    /**
     * @param array<string, string>               $cell
     * @param array<string, array<string, mixed>> $seen
     */
    private function rowError(array $cell, array $seen): ?string
    {
        return match (true) {
            preg_match('/^\d{8}$/', $cell['npsn']) !== 1              => "NPSN '{$cell['npsn']}' bukan 8 angka",
            isset($seen[$cell['npsn']])                               => "NPSN {$cell['npsn']} ganda",
            $cell['nama'] === '' || mb_strlen($cell['nama']) > 200    => 'nama kosong atau lebih dari 200 karakter',
            preg_match('/^\d{2}\.\d{2}$/', $cell['kode_kabupaten']) !== 1 => "kode kab/kota '{$cell['kode_kabupaten']}' tidak dikenal",
            ! in_array($cell['jenjang'], self::STAGES, true)          => "jenjang '{$cell['jenjang']}' bukan sd/smp/slb",
            mb_strlen($cell['bentuk']) > 20 || mb_strlen($cell['status']) > 10
                || mb_strlen($cell['kecamatan']) > 120 || mb_strlen($cell['desa']) > 120 => 'isian terlalu panjang',
            default => null,
        };
    }

    /**
     * @param array<string, mixed> $current
     * @param array<string, mixed> $data
     */
    private function differs(array $current, array $data): bool
    {
        foreach (self::COMPARED as $column) {
            if ((string) ($current[$column] ?? '') !== (string) ($data[$column] ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Entri ketikan siswa yang kuncinya berbeda dari aturan SchoolName sekarang.
     *
     * @return array<int, string> id => kunci baru
     */
    private function staleTypedKeys(): array
    {
        $stale = [];

        foreach ($this->db->table('schools')->select('id, name, match_key')->where('is_verified', 0)->get()->getResultArray() as $row) {
            $key = SchoolName::key((string) $row['name']);

            if ($key !== (string) $row['match_key']) {
                $stale[(int) $row['id']] = $key;
            }
        }

        return $stale;
    }
}
