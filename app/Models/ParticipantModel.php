<?php

namespace App\Models;

use App\Entities\Participant;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table          = 'participants';
    protected $primaryKey     = 'id';
    protected $returnType     = Participant::class;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';
    protected $useTimestamps  = true;
    protected $dateFormat     = 'datetime';
    protected $allowedFields  = [
        'participant_code', 'username', 'password_hash', 'must_change_password',
        'display_name', 'age', 'gender', 'class_level',
        'school_id', 'school_name_snapshot',
        'country_code', 'country_name_snapshot',
        'province_code', 'province_name_snapshot',
        'district_code', 'district_name_snapshot',
        'pw_first_submit_criteria', 'pw_weak_submit_count',
        'failed_login_count', 'locked_until', 'last_login_at', 'password_changed_at',
    ];
    protected $validationRules = [
        'participant_code' => 'required|max_length[40]|is_unique[participants.participant_code,id,{id}]',
        'username'         => 'required|valid_username|not_reserved_username|is_unique[participants.username,id,{id}]',
        'age'              => 'permit_empty|integer|greater_than[4]|less_than[81]',
        'gender'           => 'permit_empty|in_list[laki-laki,perempuan,lainnya]',
        'class_level'      => 'permit_empty|max_length[20]',
        'display_name'     => 'permit_empty|max_length[150]',
    ];

    /**
     * Kode peserta berurutan `GLT-000001`. Dibuat server, tidak pernah dari input.
     * Bentrok balapan → ulangi hingga 5 kali.
     */
    public function generateCode(): string
    {
        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $max = $this->db->table($this->table)->selectMax('id', 'max_id')->get()->getRowArray();
            $code = participant_code(((int) ($max['max_id'] ?? 0)) + $attempt);

            $taken = $this->db->table($this->table)
                ->where('participant_code', $code)
                ->countAllResults();

            if ($taken === 0) {
                return $code;
            }
        }

        throw new \RuntimeException('Gagal membuat participant_code yang unik.');
    }

    public function findByCode(string $code): ?Participant
    {
        return $this->where('participant_code', trim($code))->first();
    }

    public function findByUsername(string $username): ?Participant
    {
        return $this->where('username', $this->normalizeUsername($username))->first();
    }

    public function usernameAvailable(string $username): bool
    {
        return $this->withDeleted()
            ->where('username', $this->normalizeUsername($username))
            ->countAllResults() === 0;
    }

    public function normalizeUsername(string $username): string
    {
        return mb_strtolower(trim($username));
    }

    /** Satu-satunya jalur penulisan password_hash. */
    public function setPassword(int $id, string $plain, bool $mustChange = false): bool
    {
        return (bool) $this->update($id, [
            'password_hash'        => password_hash($plain, PASSWORD_DEFAULT),
            'password_changed_at'  => date('Y-m-d H:i:s'),
            'must_change_password' => $mustChange ? 1 : 0,
        ]);
    }

    /** Gagal login: increment; melewati batas → kunci sementara dan reset pencacah. */
    public function registerFailedLogin(int $id): void
    {
        $participant = $this->find($id);

        if ($participant === null) {
            return;
        }

        $throttle = config('Gelita')->loginThrottle['participant'];
        $count    = $participant->failed_login_count + 1;

        if ($count >= (int) $throttle['max_fail']) {
            $this->update($id, [
                'failed_login_count' => 0,
                'locked_until'       => date('Y-m-d H:i:s', time() + ((int) $throttle['lock_minutes'] * 60)),
            ]);

            return;
        }

        $this->update($id, ['failed_login_count' => $count]);
    }

    public function clearFailedLogin(int $id): void
    {
        $this->update($id, [
            'failed_login_count' => 0,
            'locked_until'       => null,
            'last_login_at'      => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Metrik literasi keamanan digital dari proses pembuatan sandi.
     * Tidak pernah menyentuh password_hash.
     *
     * @return array{criteria_distribution: array<int, int>, strong_first_try: int,
     *               participants: int, avg_weak_submit: float, weak_submit_distribution: array<int, int>}
     */
    public function digitalSecurityStats(array $filters = []): array
    {
        $builder = $this->scopedForStaff($filters['school_id'] ?? null);
        $this->applyCohortFilters($builder, $filters);

        $rows = $builder
            ->select('pw_first_submit_criteria, pw_weak_submit_count')
            ->where('participants.deleted_at', null)
            ->get()
            ->getResultArray();

        $criteria = array_fill(0, 6, 0);
        $weak     = [];
        $total    = 0;
        $weakSum  = 0;

        foreach ($rows as $row) {
            $total++;

            if ($row['pw_first_submit_criteria'] !== null) {
                $met = max(0, min(5, (int) $row['pw_first_submit_criteria']));
                $criteria[$met]++;
            }

            $count        = (int) $row['pw_weak_submit_count'];
            $weak[$count] = ($weak[$count] ?? 0) + 1;
            $weakSum += $count;
        }

        ksort($weak);

        return [
            'participants'             => $total,
            'criteria_distribution'    => $criteria,
            'strong_first_try'         => $criteria[5],
            'avg_weak_submit'          => $total > 0 ? round($weakSum / $total, 2) : 0.0,
            'weak_submit_distribution' => $weak,
        ];
    }

    /**
     * Builder dengan batas akses: admin ($schoolId NULL) melihat semua,
     * guru hanya sekolahnya.
     */
    public function scopedForStaff(?int $schoolId): BaseBuilder
    {
        $builder = $this->db->table($this->table);

        if ($schoolId !== null) {
            $builder->where('participants.school_id', $schoolId);
        }

        return $builder;
    }

    /**
     * Sebaran peserta untuk dasbor.
     *
     * @return array{total: int, by_gender: array<string, int>,
     *               by_class_level: array<string, int>, by_province: array<string, int>}
     */
    public function cohortSummary(array $filters = []): array
    {
        return [
            'total'          => $this->countCohort($filters),
            'by_gender'      => $this->groupCount('gender', $filters),
            'by_class_level' => $this->groupCount('class_level', $filters),
            'by_province'    => $this->groupCount('province_name_snapshot', $filters),
        ];
    }

    /**
     * Sebaran umur peserta dalam cakupan pemanggil (dasbor & chart umur).
     *
     * @param array<string, mixed> $filters school_id WAJIB diisi untuk guru (StaffScope)
     *
     * @return array<int, int> umur → jumlah peserta
     */
    public function ageDistribution(array $filters = []): array
    {
        $builder = $this->scopedForStaff($filters['school_id'] ?? null)
            ->select('age, COUNT(*) AS total', false)
            ->where('participants.deleted_at', null)
            ->where('age IS NOT NULL');

        foreach (['class_level', 'province_code'] as $key) {
            if (isset($filters[$key])) {
                $builder->where('participants.' . $key, $filters[$key]);
            }
        }

        $rows = $builder->groupBy('age')->orderBy('age', 'ASC')->get()->getResultArray();

        return array_map('intval', array_column($rows, 'total', 'age'));
    }

    private function countCohort(array $filters): int
    {
        $builder = $this->scopedForStaff($filters['school_id'] ?? null);
        $this->applyCohortFilters($builder, $filters);

        return $builder->where('participants.deleted_at', null)->countAllResults();
    }

    /** @return array<string, int> */
    private function groupCount(string $column, array $filters): array
    {
        $builder = $this->scopedForStaff($filters['school_id'] ?? null);
        $this->applyCohortFilters($builder, $filters);

        $rows = $builder
            ->select($column . ' AS bucket, COUNT(*) AS total')
            ->where('participants.deleted_at', null)
            ->groupBy($column)
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $out[(string) ($row['bucket'] ?? '—')] = (int) $row['total'];
        }

        return $out;
    }

    private function applyCohortFilters(BaseBuilder $builder, array $filters): void
    {
        if (! empty($filters['class_level'])) {
            $builder->where('participants.class_level', $filters['class_level']);
        }
        if (! empty($filters['province_code'])) {
            $builder->where('participants.province_code', $filters['province_code']);
        }
        if (! empty($filters['date_from'])) {
            $builder->where('participants.created_at >=', $filters['date_from'] . ' 00:00:00');
        }
        if (! empty($filters['date_to'])) {
            $builder->where('participants.created_at <=', $filters['date_to'] . ' 23:59:59');
        }
    }
}
