<?php

namespace App\Services;

use App\Libraries\RegionDirectory;
use App\Libraries\SchoolName;
use App\Models\AuditLogModel;
use App\Models\SchoolModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Direktori sekolah: sekolah resmi ber-NPSN + nama yang diketik siswa.
 *
 * - Siswa di provinsi berdirektori (Config\Gelita::$schoolDirectoryProvinces)
 *   wajib mengisi NPSN → sekolah resmi (findByNpsn).
 * - Nama yang diketik (NPSN tidak ada di daftar, atau provinsi lain)
 *   dicocokkan ke sekolah resmi bila jelas (matchByName). Bila tidak, disimpan
 *   sebagai sekolah "belum terverifikasi" — satu baris per (wilayah, kunci
 *   nama), jadi "SD 1 Cendono" dan "sd 1 CENDONO" tidak berlipat.
 * - Sisanya dirapikan admin di Panel → Sekolah: merge() dan verify(). Entri
 *   yang sudah digabung menjadi alias: ketikan yang sama berikutnya langsung
 *   tertaut ke sekolah tujuan, jadi kerja admin tidak berulang.
 *
 * Laporan dikelompokkan per `participants.school_id`, jadi satu sekolah =
 * satu baris `schools` adalah inti seluruh direktori ini.
 */
class SchoolDirectory
{
    public const NPSN_PATTERN = '/^\d{8}$/';

    private BaseConnection $db;

    /** @var array<string, list<array<string, mixed>>> sekolah aktif per kab/kota (cache per request) */
    private array $districtRows = [];

    /** @var list<string>|null */
    private ?array $directoryProvinces = null;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /** Provinsi ini memakai daftar resmi → siswa wajib mengisi NPSN. */
    public function requiresNpsn(?string $countryCode, ?string $provinceCode): bool
    {
        return ($countryCode ?? 'ID') === 'ID'
            && in_array((string) $provinceCode, $this->directoryProvinces(), true);
    }

    /**
     * Provinsi berdirektori (Config\Gelita::$schoolDirectoryProvinces) yang daftar
     * resminya sudah terpasang. Sebelum `gelita:schools:import` dijalankan,
     * siswa tetap bisa mendaftar dengan menulis nama sekolah.
     *
     * @return list<string>
     */
    public function directoryProvinces(): array
    {
        return $this->directoryProvinces ??= array_values(array_filter(
            config('Gelita')->schoolDirectoryProvinces,
            fn (string $code): bool => $this->verified()->select('id')->where('province_code', $code)->where('code IS NOT NULL')->first() !== null,
        ));
    }

    /** @return array<string, mixed>|null sekolah terverifikasi & aktif ber-NPSN ini */
    public function findByNpsn(string $npsn): ?array
    {
        $npsn = trim($npsn);

        if (preg_match(self::NPSN_PATTERN, $npsn) !== 1) {
            return null;
        }

        return $this->verified()->where('code', $npsn)->first();
    }

    /**
     * Data publik satu sekolah untuk kartu konfirmasi di formulir daftar.
     *
     * @param array<string, mixed> $school
     *
     * @return array<string, ?string>
     */
    public function describe(array $school): array
    {
        return [
            'npsn'          => $school['code'] ?? null,
            'name'          => $school['name'] ?? null,
            'level'         => $school['level'] ?? null,
            'stage'         => $school['stage'] ?? null,
            'status'        => $school['status'] ?? null,
            'subdistrict'   => $school['subdistrict_name'] ?? null,
            'village'       => $school['village_name'] ?? null,
            'district_code' => $school['district_code'] ?? null,
            'district_name' => RegionDirectory::districtName($school['district_code'] ?? null),
            'meta'          => $this->metaLine($school),
        ];
    }

    /**
     * Baris keterangan di bawah nama sekolah: "SD Negeri · Kec. Dawe ·
     * Kabupaten Kudus". Dirakit di server agar kartu dari PHP dan dari
     * JavaScript selalu sama.
     *
     * @param array<string, mixed> $school
     */
    public function metaLine(array $school): string
    {
        $title  = static fn (?string $text): string => mb_convert_case(trim((string) $text), MB_CASE_TITLE);
        $level  = (string) ($school['level'] ?? '');
        $level  = $level === 'MTS' ? 'MTs' : $level;
        $parts  = [
            trim($level . ' ' . $title($school['status'] ?? null)),
            ($school['subdistrict_name'] ?? '') !== '' ? 'Kec. ' . $title($school['subdistrict_name']) : '',
            (string) RegionDirectory::districtName($school['district_code'] ?? null),
        ];

        return implode(' · ', array_filter($parts, static fn (string $part): bool => $part !== ''));
    }

    /**
     * Sekolah resmi yang pasti dimaksud nama ketikan, atau NULL bila tidak ada
     * atau kandidatnya lebih dari satu (1.174 sekolah resmi Jateng bernama sama
     * dengan sekolah lain di kab/kota yang sama — nama saja memang tidak cukup).
     *
     * 1. kunci nama sama, di kab/kota yang dipilih;
     * 2. sama setelah nama kecamatan & kab/kota sekolah dibuang dari kedua sisi
     *    ("SD N 3 Tumiyang" ≡ "SD NEGERI 3 TUMIYANG KECAMATAN PEKUNCEN"), asal
     *    ketikan masih punya kata nama — "SD 1" saja terlalu umum;
     * 3. kunci nama sama dan satu-satunya se-provinsi.
     *
     * @return array<string, mixed>|null
     */
    public function matchByName(string $name, ?string $districtCode, ?string $provinceCode = null): ?array
    {
        $key = SchoolName::key($name);

        if ($key === '') {
            return null;
        }

        if ($districtCode !== null && $districtCode !== '') {
            $exact = $this->verified()->where('district_code', $districtCode)->where('match_key', $key)->findAll(2);

            if (count($exact) > 0) {
                return count($exact) === 1 ? $exact[0] : null;
            }

            $loose = $this->looseMatch($name, $districtCode);

            if ($loose !== false) {
                return $loose;
            }
        }

        if ($provinceCode !== null && $provinceCode !== '') {
            $wide = $this->verified()->where('province_code', $provinceCode)->where('match_key', $key)->findAll(2);

            if (count($wide) === 1) {
                return $wide[0];
            }
        }

        return null;
    }

    /**
     * Sekolah untuk pendaftaran siswa.
     *
     * @param array{npsn?: ?string, name?: ?string, country_code?: ?string,
     *              province_code?: ?string, district_code?: ?string} $input
     *
     * @return array{id: ?int, name: ?string, verified: bool}
     */
    public function resolve(array $input): array
    {
        $npsn = trim((string) ($input['npsn'] ?? ''));

        if ($npsn !== '' && ($school = $this->findByNpsn($npsn)) !== null) {
            return ['id' => (int) $school['id'], 'name' => $school['name'], 'verified' => true];
        }

        $name = trim((string) preg_replace('/\s+/u', ' ', (string) ($input['name'] ?? '')));

        if ($name === '') {
            return ['id' => null, 'name' => null, 'verified' => false];
        }

        $country  = trim((string) ($input['country_code'] ?? '')) ?: 'ID';
        $province = $this->blankToNull($input['province_code'] ?? null);
        $district = $this->blankToNull($input['district_code'] ?? null);

        if (($alias = $this->mergedAlias($name, $country, $province, $district)) !== null) {
            return ['id' => (int) $alias['id'], 'name' => $alias['name'], 'verified' => (int) $alias['is_verified'] === 1];
        }

        if ($country === 'ID' && ($match = $this->matchByName($name, $district, $province)) !== null) {
            return ['id' => (int) $match['id'], 'name' => $match['name'], 'verified' => true];
        }

        return [
            'id'       => $this->unverifiedId($name, $country, $province, $district),
            'name'     => $name,
            'verified' => false,
        ];
    }

    /**
     * Pencarian panel admin: NPSN persis, atau setiap kata cocok dengan awal
     * kata nama sekolah, kecamatan, atau desa.
     *
     * @param array{district_code?: ?string, verified?: ?int} $filters
     *
     * @return list<array<string, mixed>> baris `schools` + participant_count
     */
    public function search(string $query, array $filters = [], int $limit = 50): array
    {
        $builder = $this->model()->where('is_active', 1);
        $query   = trim($query);

        if (! empty($filters['district_code'])) {
            $builder->where('district_code', $filters['district_code']);
        }

        if (isset($filters['verified'])) {
            $builder->where('is_verified', (int) $filters['verified']);
        }

        if (preg_match(self::NPSN_PATTERN, $query) === 1) {
            $builder->where('code', $query);
        } else {
            foreach (array_unique(SchoolName::tokens($query)) as $token) {
                $builder->groupStart();

                if (ctype_digit($token)) {
                    // angka = kata utuh: "1" tidak ikut menemukan "SD 11"
                    $builder->where('match_key', $token)
                        ->orLike('match_key', $token . ' ', 'after')
                        ->orLike('match_key', ' ' . $token, 'before')
                        ->orLike('match_key', ' ' . $token . ' ');
                } else {
                    $builder->like('match_key', $token, 'after')
                        ->orLike('match_key', ' ' . $token)
                        ->orLike('subdistrict_name', $token, 'after')
                        ->orLike('village_name', $token, 'after');
                }

                $builder->groupEnd();
            }
        }

        $rows   = $builder->orderBy('name', 'ASC')->findAll(max(1, $limit));
        $counts = $this->participantCounts(array_map(static fn (array $row): int => (int) $row['id'], $rows));

        foreach ($rows as &$row) {
            $row['participant_count'] = $counts[(int) $row['id']] ?? 0;
        }

        unset($row);

        return $rows;
    }

    /**
     * Entri belum terverifikasi yang masih aktif, terbanyak siswanya dulu.
     *
     * @return list<array<string, mixed>> baris `schools` + participant_count
     */
    public function unverified(): array
    {
        $rows   = $this->model()->where('is_verified', 0)->where('is_active', 1)->findAll();
        $counts = $this->participantCounts(array_map(static fn (array $row): int => (int) $row['id'], $rows));

        foreach ($rows as &$row) {
            $row['participant_count'] = $counts[(int) $row['id']] ?? 0;
        }

        unset($row);
        usort($rows, static fn (array $a, array $b): int => [$b['participant_count'], $a['name']] <=> [$a['participant_count'], $b['name']]);

        return $rows;
    }

    /**
     * Kandidat penggabungan untuk satu entri: sekolah aktif di kab/kota yang
     * sama yang berbagi kata nama (angka saja tidak cukup); yang resmi dan
     * berjenjang sama didahulukan.
     *
     * @param array<string, mixed> $school
     *
     * @return list<array<string, mixed>>
     */
    public function suggestions(array $school, int $limit = 3): array
    {
        $district = $school['district_code'] ?? null;

        if ($district === null || $district === '') {
            return [];
        }

        $tokens = array_unique(SchoolName::tokens((string) $school['name']));
        $stage  = null;
        $words  = [];

        foreach ($tokens as $token) {
            if (in_array($token, SchoolName::STAGE_TOKENS, true)) {
                $stage ??= $token;
            } else {
                $words[] = $token;
            }
        }

        $scored = [];

        foreach ($this->districtRows($district) as $row) {
            if ((int) $row['id'] === (int) $school['id']) {
                continue;
            }

            $candidate = explode(' ', (string) $row['match_key']);
            $shared    = array_intersect($words, $candidate);
            $alpha     = count(array_filter($shared, static fn (string $token): bool => ! ctype_digit($token)));

            if ($alpha === 0) {
                continue;
            }

            $score = $alpha * 3 + (count($shared) - $alpha)
                + ((int) $row['is_verified'] === 1 ? 2 : 0)
                + ($stage !== null && in_array($stage, $candidate, true) ? 2 : 0);

            $scored[] = [$score, $row];
        }

        usort($scored, static fn (array $a, array $b): int => [$b[0], $a[1]['name']] <=> [$a[0], $b[1]['name']]);

        return array_map(static fn (array $pair): array => $pair[1], array_slice($scored, 0, $limit));
    }

    /**
     * Menggabungkan entri belum terverifikasi $fromId ke sekolah $toId:
     * peserta dan akun guru pindah, snapshot nama peserta ikut nama tujuan,
     * entri asal dinonaktifkan dengan jejak `merged_into_id`.
     *
     * @return array{participants: int, staff: int}
     */
    public function merge(int $fromId, int $toId): array
    {
        $from = $this->model()->find($fromId);
        $to   = $this->model()->find($toId);

        if ($from === null || $to === null || $fromId === $toId) {
            throw new \InvalidArgumentException('Sekolah asal atau tujuan tidak ditemukan.');
        }

        if ((int) $from['is_verified'] === 1) {
            throw new \InvalidArgumentException("Sekolah resmi \"{$from['name']}\" tidak bisa digabungkan ke sekolah lain.");
        }

        if ((int) $from['is_active'] !== 1 || (int) $to['is_active'] !== 1) {
            throw new \InvalidArgumentException('Salah satu sekolah sudah tidak aktif (mungkin sudah digabungkan).');
        }

        $this->db->transBegin();

        try {
            $this->db->table('participants')
                ->set('school_id', $toId)
                ->set('school_name_snapshot', $to['name'])
                ->where('school_id', $fromId)
                ->update();
            $participants = $this->db->affectedRows();

            $this->db->table('staff_users')->set('school_id', $toId)->where('school_id', $fromId)->update();
            $staff = $this->db->affectedRows();

            // entri yang dulu digabung ke asal ikut menunjuk tujuan akhir
            $this->db->table('schools')->set('merged_into_id', $toId)->where('merged_into_id', $fromId)->update();
            $this->db->table('schools')->set(['is_active' => 0, 'merged_into_id' => $toId])->where('id', $fromId)->update();

            (new AuditLogModel($this->db))->record('school_merge', [
                'target_type' => 'school',
                'target_id'   => $toId,
                'metadata'    => [
                    'from_id'      => $fromId,
                    'from_name'    => $from['name'],
                    'to_name'      => $to['name'],
                    'to_npsn'      => $to['code'],
                    'participants' => $participants,
                    'staff'        => $staff,
                ],
            ]);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();

            throw $e;
        }

        unset($this->districtRows[(string) $from['district_code']]);

        return ['participants' => $participants, 'staff' => $staff];
    }

    /**
     * Mengesahkan entri belum terverifikasi sebagai sekolah (baru) ber-NPSN,
     * agar siswa berikutnya bisa memakai NPSN itu. Bila NPSN kelak muncul di
     * data resmi, importer menimpa baris ini dengan data resmi.
     *
     * @return array<string, mixed> baris sesudah disahkan
     */
    public function verify(int $id, string $npsn, ?string $name = null): array
    {
        $school = $this->model()->find($id);
        $npsn   = trim($npsn);

        if ($school === null || (int) $school['is_active'] !== 1) {
            throw new \InvalidArgumentException('Sekolah tidak ditemukan.');
        }

        if ((int) $school['is_verified'] === 1) {
            throw new \InvalidArgumentException('Sekolah ini sudah terverifikasi.');
        }

        if (preg_match(self::NPSN_PATTERN, $npsn) !== 1) {
            throw new \InvalidArgumentException('NPSN harus 8 angka.');
        }

        $owner = $this->model()->where('code', $npsn)->first();

        if ($owner !== null) {
            throw new \InvalidArgumentException("NPSN {$npsn} sudah dipakai \"{$owner['name']}\" — gabungkan ke sekolah itu saja.");
        }

        $name = trim((string) preg_replace('/\s+/u', ' ', (string) $name)) ?: (string) $school['name'];

        $this->model()->update($id, [
            'code'        => $npsn,
            'name'        => mb_substr($name, 0, 200),
            'is_verified' => 1,
            'match_key'   => SchoolName::key($name),
        ]);

        (new AuditLogModel($this->db))->record('school_verify', [
            'target_type' => 'school',
            'target_id'   => $id,
            'metadata'    => ['npsn' => $npsn, 'name' => $name, 'typed_name' => $school['name']],
        ]);

        return $this->model()->find($id);
    }

    /**
     * Menggabungkan setiap entri belum terverifikasi yang cocok jelas dengan
     * sekolah resmi (aturan matchByName).
     *
     * @return list<array{from: array<string, mixed>, to: array<string, mixed>}>
     */
    public function autoMerge(bool $dryRun = false): array
    {
        $pairs = [];

        foreach ($this->model()->where('is_verified', 0)->where('is_active', 1)->where('country_code', 'ID')->findAll() as $school) {
            $match = $this->matchByName((string) $school['name'], $school['district_code'], $school['province_code']);

            if ($match === null) {
                continue;
            }

            $pairs[] = ['from' => $school, 'to' => $match];

            if (! $dryRun) {
                $this->merge((int) $school['id'], (int) $match['id']);
            }
        }

        return $pairs;
    }

    /**
     * @param list<int> $ids
     *
     * @return array<int, int> school_id => jumlah peserta (belum dihapus)
     */
    public function participantCounts(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $rows = $this->db->table('participants')
            ->select('school_id, COUNT(*) AS total')
            ->whereIn('school_id', $ids)
            ->where('deleted_at', null)
            ->groupBy('school_id')
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'total', 'school_id'));
    }

    // ------------------------------------------------------------------ internal

    /**
     * Aturan 2 matchByName.
     *
     * @return array<string, mixed>|false|null sekolah; NULL = ambigu; FALSE = tidak ada kandidat
     */
    private function looseMatch(string $name, string $districtCode): array|false|null
    {
        $typed        = SchoolName::tokens($name);
        $districtName = (string) RegionDirectory::districtName($districtCode);
        $places       = [];
        $found        = [];

        foreach ($this->districtRows($districtCode) as $row) {
            if ((int) $row['is_verified'] !== 1) {
                continue;
            }

            $subdistrict = (string) ($row['subdistrict_name'] ?? '');
            $drop        = $places[$subdistrict] ??= SchoolName::placeTokens($subdistrict, $districtName);
            $mine        = SchoolName::withoutPlaces($typed, $drop);

            if (! SchoolName::hasIdentity($mine)) {
                continue;
            }

            $theirs = SchoolName::withoutPlaces(explode(' ', (string) $row['match_key']), $drop);

            if (SchoolName::keyFromTokens($mine) === SchoolName::keyFromTokens($theirs)) {
                $found[] = $row;

                if (count($found) > 1) {
                    return null;
                }
            }
        }

        return $found === [] ? false : $found[0];
    }

    /**
     * Sekolah aktif di satu kab/kota (resmi & belum terverifikasi), disimpan
     * selama request — halaman admin memanggilnya untuk banyak entri sekaligus.
     *
     * @return list<array<string, mixed>>
     */
    private function districtRows(string $districtCode): array
    {
        return $this->districtRows[$districtCode] ??= $this->model()
            ->select('id, code, name, is_verified, match_key, subdistrict_name, village_name, district_code, stage, level')
            ->where('district_code', $districtCode)
            ->where('is_active', 1)
            ->findAll();
    }

    /**
     * Sekolah tujuan bila ketikan yang sama (wilayah + kunci nama) pernah
     * digabung admin.
     *
     * @return array<string, mixed>|null
     */
    private function mergedAlias(string $name, string $country, ?string $province, ?string $district): ?array
    {
        $alias = $this->model()->select('merged_into_id')
            ->where('is_active', 0)
            ->where('merged_into_id IS NOT NULL')
            ->where('country_code', $country)
            ->where('province_code', $province)
            ->where('district_code', $district)
            ->where('match_key', SchoolName::key($name))
            ->orderBy('id', 'DESC')
            ->first();

        if ($alias === null) {
            return null;
        }

        return $this->model()->where('is_active', 1)->find((int) $alias['merged_into_id']);
    }

    private function unverifiedId(string $name, string $country, ?string $province, ?string $district): int
    {
        $key = SchoolName::key($name);

        $existing = $this->model()->select('id')
            ->where('is_verified', 0)
            ->where('is_active', 1)
            ->where('country_code', $country)
            ->where('province_code', $province)
            ->where('district_code', $district)
            ->where('match_key', $key)
            ->first();

        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $model = $this->model();
        $id    = $model->insert([
            'name'          => mb_substr($name, 0, 200),
            'country_code'  => $country,
            'province_code' => $province,
            'district_code' => $district,
            'is_active'     => 1,
            'is_verified'   => 0,
            'match_key'     => $key,
        ], true);

        if ($id === false) {
            throw new \RuntimeException('Sekolah tidak tersimpan: ' . implode(' ', $model->errors()));
        }

        return (int) $id;
    }

    private function verified(): SchoolModel
    {
        return $this->model()->where('is_verified', 1)->where('is_active', 1);
    }

    private function model(): SchoolModel
    {
        return new SchoolModel($this->db);
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
