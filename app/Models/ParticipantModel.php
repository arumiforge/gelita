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
        'failed_login_count', 'locked_until', 'last_login_at', 'password_changed_at', 'assented_at',
        'intro_seen_at',
    ];
    protected $validationRules = [
        'participant_code' => 'required|max_length[40]|is_unique[participants.participant_code,id,{id}]',
        'username'         => 'required|valid_username|not_reserved_username|is_unique[participants.username,id,{id}]',
        'age'              => 'permit_empty|integer|greater_than[4]|less_than[81]',
        'gender'           => 'permit_empty|in_list[laki-laki,perempuan]',
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

    /**
     * Tandai cerita pembuka sudah ditonton. Idempoten: waktu pertama tidak
     * pernah ditimpa. TRUE bila baris ini baru saja ditandai.
     */
    public function markIntroSeen(int $id): bool
    {
        $this->builder()
            ->where('id', $id)
            ->where('intro_seen_at', null)
            ->update(['intro_seen_at' => date('Y-m-d H:i:s')]);

        return $this->db->affectedRows() > 0;
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
}
