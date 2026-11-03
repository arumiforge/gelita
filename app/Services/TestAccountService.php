<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;

/**
 * `uji.*` accounts for the validators' test install (`gelita:uji:akun`,
 * research revision 10, part 2: 28 §13, K15). Outside the token flow: the
 * accounts are made directly, at school `Sekolah Uji Validator` (`V01`),
 * class group `A`, with codes `UJI-01`, `UJI-02`, …. Passwords are random,
 * shown once, and never stored in a file.
 */
class TestAccountService
{
    public const PREFIX = 'uji.';

    /** Default accounts (03). */
    public const DEFAULT_NAMES = [
        'uji.bahasa1', 'uji.bahasa2', 'uji.digital1', 'uji.digital2', 'uji.ukur',
        'uji.budaya1', 'uji.budaya2', 'uji.budaya3', 'uji.media1', 'uji.media2', 'uji.media3',
        'uji.cadangan1', 'uji.cadangan2',
    ];

    public const SCHOOL_NAME = 'Sekolah Uji Validator';

    public const SCHOOL_CODE = 'V01';

    /** Why the tool must not run here, or null. */
    public function refusal(string $environment): ?string
    {
        if ($environment === 'production') {
            return 'gelita:uji:akun tidak boleh dijalankan di production (CI_ENVIRONMENT = production).';
        }

        $others = db_connect()->table('participants')->notLike('username', self::PREFIX, 'after')->countAllResults();

        if ($others > 0) {
            return sprintf('ada %d siswa yang nama penggunanya tidak berawalan uji.; alat ini hanya untuk instalasi uji validator.', $others);
        }

        if (model(ResearchStudyModel::class)->activeStudy() === null) {
            return 'tidak ada studi aktif (research_studies.status = active).';
        }

        return null;
    }

    /**
     * Creates the accounts that do not exist yet.
     *
     * @param list<string> $names
     *
     * @return list<array{username: string, code: string, password: ?string, status: string}>
     *               status `created` (password shown once) or `exists`
     */
    public function create(array $names): array
    {
        $participants = model(ParticipantModel::class);
        $schoolId     = $this->ensureSchool();
        $out          = [];

        foreach ($names as $name) {
            $username = $participants->normalizeUsername($name);

            if (! str_starts_with($username, self::PREFIX)) {
                throw new \InvalidArgumentException("Nama akun uji harus berawalan uji.: {$name}");
            }

            $existing = $participants->withDeleted()->where('username', $username)->first();

            if ($existing !== null) {
                $out[] = ['username' => $username, 'code' => (string) $existing->participant_code, 'password' => null, 'status' => 'exists'];

                continue;
            }

            $password = service('sessionService')->generateTemporaryPassword($username);
            $code     = $this->nextCode();

            $id = $participants->insert([
                'participant_code'     => $code,
                'username'             => $username,
                'password_hash'        => password_hash($password, PASSWORD_DEFAULT),
                'must_change_password' => 0,
                'password_changed_at'  => date('Y-m-d H:i:s'),
                // Letters only (20, prerequisite no. 8): the account name without digits
                'display_name'         => strtoupper((string) preg_replace('/[^a-z]+/', ' ', substr($username, strlen(self::PREFIX)))) ?: 'UJI',
                'age'                  => 11,
                'class_level'          => config('Gelita')->registrationClass,
                'school_id'            => $schoolId,
                'school_name_snapshot' => self::SCHOOL_NAME,
                'rombel'               => 'A',
                'country_code'         => 'ID',
                'assented_at'          => date('Y-m-d H:i:s'),
            ], true);

            if ($id === false) {
                throw new \RuntimeException('Akun uji gagal dibuat: ' . implode(' ', $participants->errors()));
            }

            // shortcut: no participant_consents row yet; part 4 (30 §5.2) adds the table and this row
            model(AuditLogModel::class)->record('test_account_created', [
                'staff_user_id' => null,
                'target_type'   => 'participant',
                'target_id'     => (string) $id,
                'metadata'      => ['username' => $username, 'participant_code' => $code, 'via' => 'cli'],
            ]);

            $out[] = ['username' => $username, 'code' => $code, 'password' => $password, 'status' => 'created'];
        }

        return $out;
    }

    /**
     * Deletes the play data of one `uji.*` account (game sessions, attempts,
     * tries, answers, events, progress, seat); the account stays.
     *
     * @return array<string, int> rows deleted per table
     */
    public function reset(string $username): array
    {
        $participants = model(ParticipantModel::class);
        $participant  = $participants->findByUsername($username);

        if ($participant === null || ! str_starts_with((string) $participant->username, self::PREFIX)) {
            throw new \InvalidArgumentException("Akun uji {$username} tidak ditemukan.");
        }

        $deleted = service('retentionService')->deletePlayData($participant->id);

        model(AuditLogModel::class)->record('test_account_reset', [
            'staff_user_id' => null,
            'target_type'   => 'participant',
            'target_id'     => (string) $participant->id,
            'metadata'      => ['username' => $participant->username, 'deleted' => $deleted, 'via' => 'cli'],
        ]);

        return $deleted;
    }

    /**
     * @return list<array{username: string, code: string, last_play: ?string}>
     */
    public function list(): array
    {
        $rows = model(ParticipantModel::class)->like('username', self::PREFIX, 'after')->orderBy('participant_code', 'ASC')->findAll();
        $out  = [];

        foreach ($rows as $row) {
            $last = model(GameSessionModel::class)->selectMax('last_active_at', 'last')->where('participant_id', $row->id)->first();

            $out[] = ['username' => (string) $row->username, 'code' => (string) $row->participant_code, 'last_play' => $last?->last ?? null];
        }

        return $out;
    }

    private function ensureSchool(): int
    {
        $schools = model(SchoolModel::class);
        $school  = $schools->where('research_code', self::SCHOOL_CODE)->first();

        if ($school !== null) {
            return (int) $school['id'];
        }

        return (int) $schools->insert([
            'name'          => self::SCHOOL_NAME,
            'research_code' => self::SCHOOL_CODE,
            'country_code'  => 'ID',
            'is_active'     => 1,
            'is_verified'   => 1,
        ], true);
    }

    /** `UJI-01`, `UJI-02`, … after the highest one in use. */
    private function nextCode(): string
    {
        $max = 0;

        foreach (db_connect()->table('participants')->select('participant_code')->like('participant_code', 'UJI-', 'after')->get()->getResultArray() as $row) {
            $max = max($max, (int) substr((string) $row['participant_code'], 4));
        }

        return sprintf('UJI-%02d', $max + 1);
    }
}
