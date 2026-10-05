<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Entities\Participant;
use App\Libraries\DeviceProfile;
use App\Libraries\PasswordPolicy;
use App\Models\AuditLogModel;
use App\Models\DeviceLogModel;
use App\Models\GameReleaseModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\SessionProgressModel;

/**
 * Siklus hidup akun siswa dan sesi permainan. Satu tahap bermain per studi
 * (perombakan kelas 5: tanpa fase pretest/posttest, tanpa izin wali).
 * Semua operasi menulis memakai transaction; kata sandi mentah tidak pernah
 * disimpan, dicatat, atau dikembalikan kecuali sandi sementara hasil reset guru.
 */
class SessionService
{
    /**
     * Mendaftarkan siswa + akun (langkah 3 pendaftaran), mencatat persetujuan
     * siswa di `participants.assented_at`, lalu membuat sesi pertama dan baris
     * `device_logs` (`via = register`).
     *
     * $input['device'] = hasil DeviceProfile::capture().
     *
     * @param array<string, mixed> $input
     *
     * @return array{participant: Participant, session: GameSession}
     */
    public function registerAndStart(array $input): array
    {
        $participants = model(ParticipantModel::class);

        $username = $participants->normalizeUsername((string) ($input['username'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        $this->assertStrongPassword($password, $username);

        if (! $participants->usernameAvailable($username)) {
            throw new \InvalidArgumentException('Nama pengguna sudah dipakai.');
        }

        if (empty($input['assent'])) {
            throw new \InvalidArgumentException('Persetujuan siswa wajib dicentang.');
        }

        $study   = model(ResearchStudyModel::class)->requireActiveStudy();
        $release = model(GameReleaseModel::class)->active();
        $locale    = $this->resolveLocale($input['locale'] ?? null, $study);

        $db = db_connect();
        $db->transBegin();

        try {
            // NPSN → sekolah resmi; nama ketikan → sekolah resmi bila jelas, selain itu
            // entri "belum terverifikasi" (SchoolDirectory::resolve)
            $school = service('schoolDirectory')->resolve([
                'npsn'          => $input['school_npsn'] ?? null,
                'name'          => $input['school_name'] ?? null,
                'country_code'  => $input['country_code'] ?? 'ID',
                'province_code' => $input['province_code'] ?? null,
                'district_code' => $input['district_code'] ?? null,
            ]);

            $metrics = (array) ($input['registration_metrics'] ?? []);

            $participantId = $participants->insert([
                'participant_code'         => $participants->generateCode(),
                'username'                 => $username,
                'password_hash'            => password_hash($password, PASSWORD_DEFAULT),
                'must_change_password'     => 0,
                'password_changed_at'      => date('Y-m-d H:i:s'),
                'display_name'             => $this->nullIfBlank($input['display_name'] ?? null),
                'age'                      => isset($input['age']) && $input['age'] !== '' ? (int) $input['age'] : null,
                'gender'                   => $this->nullIfBlank($input['gender'] ?? null),
                'class_level'              => $this->nullIfBlank($input['class_level'] ?? null),
                'school_id'                => $school['id'],
                'school_name_snapshot'     => $school['name'],
                'country_code'             => $input['country_code'] ?? 'ID',
                'country_name_snapshot'    => $this->nullIfBlank($input['country_name'] ?? null),
                'province_code'            => $this->nullIfBlank($input['province_code'] ?? null),
                'province_name_snapshot'   => $this->nullIfBlank($input['province_name'] ?? null),
                'district_code'            => $this->nullIfBlank($input['district_code'] ?? null),
                'district_name_snapshot'   => $this->nullIfBlank($input['district_name'] ?? null),
                'pw_first_submit_criteria' => isset($metrics['first_submit_criteria'])
                    ? max(0, min(5, (int) $metrics['first_submit_criteria']))
                    : null,
                'pw_weak_submit_count' => max(0, (int) ($metrics['weak_submit_count'] ?? 0)),
                'assented_at'          => date('Y-m-d H:i:s'),
            ], true);

            if ($participantId === false) {
                throw new \RuntimeException('Data pendaftaran ditolak: ' . implode(' ', $participants->errors()));
            }

            $participantId = (int) $participantId;

            $device  = (array) ($input['device'] ?? []);
            $session = $this->insertSession(
                $participantId,
                (int) $study['id'],
                (int) $release['id'],
                $locale,
                $device,
            );

            model(DeviceLogModel::class)->record($session->id, $participantId, 'register', $device);

            service('eventService')->record($session, 'session_started', ['via' => 'register']);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        $this->bindPhpSession($participantId, $session->id);

        return [
            'participant' => $participants->find($participantId),
            'session'     => $session,
        ];
    }

    /**
     * Login siswa, lalu melanjutkan sesi studi aktif (atau membuat sesi baru)
     * dan menulis `device_logs` (`via = resume` bila melanjutkan, `login` bila
     * sesi baru). Pesan 'invalid' sengaja identik untuk akun tak ada, sandi
     * salah, dan akun nonaktif.
     *
     * $device = hasil DeviceProfile::capture().
     *
     * @return array{status: string, session: ?GameSession, participant: ?Participant, lock_minutes?: int}
     */
    public function login(string $username, string $password, string $locale, array $device): array
    {
        $participants = model(ParticipantModel::class);
        $participant  = $participants->findByUsername($username);

        $invalid = ['status' => 'invalid', 'session' => null, 'participant' => null];

        if ($participant === null) {
            return $invalid;
        }

        if ($participant->isLocked()) {
            return [
                'status'       => 'locked',
                'session'      => null,
                'participant'  => null,
                'lock_minutes' => $participant->lockMinutesLeft(),
            ];
        }

        if (! $participant->verifyPassword($password)) {
            $participants->registerFailedLogin($participant->id);

            $refreshed = $participants->find($participant->id);

            if ($refreshed !== null && $refreshed->isLocked()) {
                return [
                    'status'       => 'locked',
                    'session'      => null,
                    'participant'  => null,
                    'lock_minutes' => $refreshed->lockMinutesLeft(),
                ];
            }

            return $invalid;
        }

        $participants->clearFailedLogin($participant->id);
        $participant = $participants->find($participant->id);

        if ($participant->mustChangePassword()) {
            $this->bindPhpSession($participant->id, null);

            return ['status' => 'must_change', 'session' => null, 'participant' => $participant];
        }

        $study  = model(ResearchStudyModel::class)->requireActiveStudy();
        $locale = $this->resolveLocale($locale, $study);

        $sessions = model(GameSessionModel::class);
        $existing = $sessions->resumable($participant->id, (int) $study['id']);

        if ($existing !== null) {
            $sessions->update($existing->id, [
                'status'         => 'active',
                'locale'         => $locale,
                'last_active_at' => date('Y-m-d H:i:s'),
            ]);

            $session = $sessions->find($existing->id);
            service('eventService')->record($session, 'session_resumed', ['via' => 'login']);
            model(DeviceLogModel::class)->record($session->id, $participant->id, 'resume', $device);
        } else {
            $session = $this->startNewSession($participant->id, $locale, $device, 'login');
            model(DeviceLogModel::class)->record($session->id, $participant->id, 'login', $device);
        }

        $this->bindPhpSession($participant->id, $session->id);

        return ['status' => 'ok', 'session' => $session, 'participant' => $participant];
    }

    /**
     * Siswa mengganti sandinya sendiri. Wajib setelah reset guru.
     *
     * @return array{status: string, message?: string}
     */
    public function changePassword(int $participantId, string $current, string $new): array
    {
        $participants = model(ParticipantModel::class);
        $participant  = $participants->find($participantId);

        if ($participant === null) {
            return ['status' => 'not_found'];
        }

        if (! $participant->verifyPassword($current)) {
            return ['status' => 'invalid_current'];
        }

        $check = (new PasswordPolicy())->check($new, $participant->username);

        if (! $check['acceptable']) {
            return ['status' => 'weak'];
        }

        $participants->setPassword($participantId, $new, false);

        $sessionId = (int) session('game_session_id');

        if ($sessionId > 0) {
            $session = model(GameSessionModel::class)->find($sessionId);

            if ($session !== null) {
                service('eventService')->record($session, 'password_changed', ['via' => 'self']);
            }
        }

        return ['status' => 'ok'];
    }

    /**
     * Guru/admin mereset sandi siswa. Sandi sementara dikembalikan SEKALI
     * untuk ditunjukkan ke siswa dan tidak disimpan di mana pun.
     */
    public function resetPasswordByStaff(int $participantId, int $staffId): string
    {
        $participants = model(ParticipantModel::class);
        $participant  = $participants->find($participantId);

        if ($participant === null) {
            throw new \RuntimeException("Peserta {$participantId} tidak ditemukan.");
        }

        $temporary = $this->generateTemporaryPassword($participant->username);

        $participants->setPassword($participantId, $temporary, true);

        model(AuditLogModel::class)->record('participant_password_reset', [
            'staff_user_id' => $staffId,
            'target_type'   => 'participant',
            'target_id'     => (string) $participantId,
            'metadata'      => ['participant_code' => $participant->participant_code],
        ]);

        return $temporary;
    }

    /**
     * Keluar. Attempt yang sedang berjalan sengaja TIDAK ditinggalkan,
     * agar siswa dapat melanjutkan tantangan yang sama saat masuk kembali.
     */
    public function logout(): void
    {
        $sessionId = (int) session('game_session_id');

        if ($sessionId > 0) {
            $sessions = model(GameSessionModel::class);
            $session  = $sessions->find($sessionId);

            if ($session !== null && $session->status === 'active') {
                $sessions->update($sessionId, ['status' => 'paused', 'last_active_at' => date('Y-m-d H:i:s')]);
                service('eventService')->record($session, 'session_paused', ['via' => 'logout']);
            }
        }

        session()->destroy();
    }

    /** Sesi baru untuk peserta yang sudah terdaftar (sesi lama selesai/ditinggalkan). */
    public function startNewSession(
        int $participantId,
        string $locale,
        array $device,
        string $via = 'new_session',
    ): GameSession {
        $study   = model(ResearchStudyModel::class)->requireActiveStudy();
        $release = model(GameReleaseModel::class)->active();
        $locale  = $this->resolveLocale($locale, $study);

        $db = db_connect();
        $db->transBegin();

        try {
            $session = $this->insertSession(
                $participantId,
                (int) $study['id'],
                (int) $release['id'],
                $locale,
                $device,
            );

            service('eventService')->record($session, 'session_started', ['via' => $via]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return $session;
    }

    /** Mengubah bahasa TANPA menyentuh progres atau bintang. */
    public function setLocale(int $sessionId, string $locale): void
    {
        if (! in_array($locale, config('Gelita')->locales, true)) {
            throw new \InvalidArgumentException("Locale '{$locale}' tidak didukung.");
        }

        $sessions = model(GameSessionModel::class);
        $session  = $sessions->find($sessionId);

        if ($session === null || $session->locale === $locale) {
            return;
        }

        $sessions->update($sessionId, ['locale' => $locale, 'last_active_at' => date('Y-m-d H:i:s')]);

        service('eventService')->record($session, 'locale_changed', ['from' => $session->locale, 'to' => $locale]);
    }

    /** Update last_active_at + akumulasi duration_ms sejak denyut terakhir. */
    public function heartbeat(int $sessionId): void
    {
        $sessions = model(GameSessionModel::class);
        $session  = $sessions->find($sessionId);

        if ($session === null || $session->isFinished()) {
            return;
        }

        $lastActive = strtotime((string) $session->last_active_at) ?: time();
        $elapsedMs  = max(0, (time() - $lastActive) * 1000);
        $idleLimit  = config('Gelita')->sessionIdleMinutes * 60 * 1000;

        $sessions->update($sessionId, [
            'last_active_at' => date('Y-m-d H:i:s'),
            // waktu menganggur panjang tidak dihitung sebagai waktu bermain
            'duration_ms'    => $session->duration_ms + ($elapsedMs > $idleLimit ? 0 : $elapsedMs),
        ]);
    }

    /** Menandai sesi selesai bila seluruh level sudah tuntas. */
    public function completeIfFinished(int $sessionId): bool
    {
        $sessions = model(GameSessionModel::class);
        $session  = $sessions->find($sessionId);

        if ($session === null || $session->isFinished()) {
            return false;
        }

        $progress   = model(SessionProgressModel::class)->ensure($sessionId);
        $levelCount = count(service('contentRepository')->levels());

        if ($levelCount === 0 || (int) $progress['completed_levels'] < $levelCount) {
            return false;
        }

        $sessions->update($sessionId, [
            'status'         => 'completed',
            'ended_at'       => date('Y-m-d H:i:s'),
            'last_active_at' => date('Y-m-d H:i:s'),
        ]);

        service('eventService')->record($session, 'session_completed', [
            'total_stars' => (int) $progress['total_stars'],
        ]);

        return true;
    }

    public function abandon(int $sessionId, string $reason = 'idle'): void
    {
        $sessions = model(GameSessionModel::class);
        $session  = $sessions->find($sessionId);

        if ($session === null || $session->isFinished()) {
            return;
        }

        $sessions->update($sessionId, ['status' => 'abandoned', 'ended_at' => date('Y-m-d H:i:s')]);

        service('eventService')->record($session, 'session_abandoned', ['reason' => $reason]);
    }

    /** Kebijakan sandi diputuskan PasswordPolicy, juga di service — bukan hanya di controller. */
    private function assertStrongPassword(string $password, string $username): void
    {
        $check = (new PasswordPolicy())->check($password, $username);

        if (! $check['acceptable']) {
            throw new \InvalidArgumentException('Kata sandi belum memenuhi kebijakan keamanan.');
        }
    }

    /** Sandi sementara yang dijamin memenuhi kebijakan dan tidak memuat nama pengguna. */
    private function generateTemporaryPassword(string $username): string
    {
        $upper   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lower   = 'abcdefghijkmnpqrstuvwxyz';
        $digit   = '23456789';
        $symbol  = '!@#$%*?';
        $policy  = new PasswordPolicy();

        for ($attempt = 0; $attempt < 10; $attempt++) {
            $chars = [
                $upper[random_int(0, strlen($upper) - 1)],
                $lower[random_int(0, strlen($lower) - 1)],
                $digit[random_int(0, strlen($digit) - 1)],
                $symbol[random_int(0, strlen($symbol) - 1)],
            ];

            $pool = $upper . $lower . $digit;

            for ($i = 0; $i < 6; $i++) {
                $chars[] = $pool[random_int(0, strlen($pool) - 1)];
            }

            $candidate = implode('', seeded_shuffle($chars, random_code(4)));

            if ($policy->check($candidate, $username)['acceptable']) {
                return $candidate;
            }
        }

        throw new \RuntimeException('Gagal membuat sandi sementara yang memenuhi kebijakan.');
    }

    /**
     * @param array<string, mixed> $device hasil DeviceProfile::capture(); kolom ringkas
     *                                     game_sessions diisi dari sini
     */
    private function insertSession(
        int $participantId,
        int $studyId,
        int $releaseId,
        string $locale,
        array $device,
    ): GameSession {
        $sessions = model(GameSessionModel::class);
        $now      = date('Y-m-d H:i:s');
        $summary  = DeviceProfile::sessionSummary($device);

        $sessionId = $sessions->insert([
            'session_code'       => random_code(16),
            'participant_id'     => $participantId,
            'study_id'           => $studyId,
            'release_id'         => $releaseId,
            'locale'             => $locale,
            'status'             => 'active',
            'started_at'         => $now,
            'last_active_at'     => $now,
            'duration_ms'        => 0,
            'device_type'        => $this->nullIfBlank($summary['device_type']),
            'os_name'            => $this->nullIfBlank($summary['os_name']),
            'browser_name'       => $this->nullIfBlank($summary['browser_name']),
            'screen_size'        => $this->nullIfBlank($summary['screen_size']),
            'is_touch'           => $summary['is_touch'],
            'app_client_version' => $this->nullIfBlank($device['app_client_version'] ?? null),
            'ip_hash'            => hash_ip(service('request')->getIPAddress()),
        ], true);

        if ($sessionId === false) {
            throw new \RuntimeException('Sesi permainan gagal dibuat: ' . implode(' ', $sessions->errors()));
        }

        $session = $sessions->find((int) $sessionId);

        model(SessionProgressModel::class)->ensure($session->id);

        return $session;
    }

    private function resolveLocale(?string $locale, array $study): string
    {
        $locale = trim((string) $locale);

        return in_array($locale, config('Gelita')->locales, true)
            ? $locale
            : (string) $study['default_locale'];
    }

    /** Identitas siswa dipegang session CI4, bukan cookie khusus. */
    private function bindPhpSession(int $participantId, ?int $gameSessionId): void
    {
        $session = session();
        $session->regenerate(true);
        $session->set('participant_id', $participantId);

        if ($gameSessionId === null) {
            $session->remove('game_session_id');

            return;
        }

        $session->set('game_session_id', $gameSessionId);
    }

    private function nullIfBlank($value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
