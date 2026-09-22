<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Entities\Participant;
use App\Libraries\PasswordPolicy;
use App\Models\AuditLogModel;
use App\Models\GameReleaseModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantConsentModel;
use App\Models\ParticipantModel;
use App\Models\ResearchPhaseModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use App\Models\SessionProgressModel;

/**
 * Siklus hidup akun siswa dan sesi permainan.
 * Semua operasi menulis memakai transaction; kata sandi mentah tidak pernah
 * disimpan, dicatat, atau dikembalikan kecuali sandi sementara hasil reset guru.
 */
class SessionService
{
    /**
     * Mendaftarkan siswa + akun + consent, lalu membuat sesi pertama.
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

        $study = model(ResearchStudyModel::class)->requireActiveStudy();

        $parentConsent = ! empty($input['parent_guardian_consented']);

        if ((int) $study['require_consent'] === 1 && ! $parentConsent) {
            throw new \RuntimeException('Persetujuan orang tua/wali wajib untuk studi ini.');
        }

        $phaseCode = $this->resolvePhaseCode($study, $input['phase_code'] ?? null);
        $phase     = model(ResearchPhaseModel::class)->requireByCode((int) $study['id'], $phaseCode);
        $release   = model(GameReleaseModel::class)->active();
        $locale    = $this->resolveLocale($input['locale'] ?? null, $study);

        $db = db_connect();
        $db->transBegin();

        try {
            $schoolId   = null;
            $schoolName = trim((string) ($input['school_name'] ?? ''));

            if ($schoolName !== '') {
                $schoolId = model(SchoolModel::class)->findOrCreateByName($schoolName, [
                    'country_code'  => $input['country_code'] ?? 'ID',
                    'province_code' => $input['province_code'] ?? null,
                    'district_code' => $input['district_code'] ?? null,
                ]);
            }

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
                'school_id'                => $schoolId,
                'school_name_snapshot'     => $schoolName !== '' ? $schoolName : null,
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
            ], true);

            if ($participantId === false) {
                throw new \RuntimeException('Data pendaftaran ditolak: ' . implode(' ', $participants->errors()));
            }

            $participantId = (int) $participantId;

            model(ParticipantConsentModel::class)->insert([
                'participant_id'            => $participantId,
                'study_id'                  => (int) $study['id'],
                'consent_version'           => (string) ($input['consent_version'] ?? '1'),
                'participant_consented'     => ! empty($input['participant_consented']) ? 1 : 0,
                'parent_guardian_consented' => $parentConsent ? 1 : 0,
                'guardian_name'             => $this->nullIfBlank($input['guardian_name'] ?? null),
                'consent_text_snapshot'     => (string) ($input['consent_text'] ?? ''),
                'consented_at'              => date('Y-m-d H:i:s'),
            ], false);

            $session = $this->insertSession(
                $participantId,
                (int) $study['id'],
                (int) $phase['id'],
                (int) $release['id'],
                $locale,
                (array) ($input['device'] ?? []),
            );

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
     * Login siswa, lalu melanjutkan atau membuat sesi pada fase yang dipilih.
     * Pesan 'invalid' sengaja identik untuk akun tak ada, sandi salah, dan akun nonaktif.
     *
     * @return array{status: string, session: ?GameSession, participant: ?Participant, lock_minutes?: int}
     */
    public function login(string $username, string $password, string $phaseCode, string $locale, array $device): array
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

        $study     = model(ResearchStudyModel::class)->requireActiveStudy();
        $phaseCode = $this->resolvePhaseCode($study, $phaseCode);
        $phase     = model(ResearchPhaseModel::class)->requireByCode((int) $study['id'], $phaseCode);
        $locale    = $this->resolveLocale($locale, $study);

        $sessions = model(GameSessionModel::class);
        $existing = $sessions->resumable($participant->id, (int) $study['id'], (int) $phase['id']);

        if ($existing !== null) {
            $sessions->update($existing->id, [
                'status'         => 'active',
                'locale'         => $locale,
                'last_active_at' => date('Y-m-d H:i:s'),
            ]);

            $session = $sessions->find($existing->id);
            service('eventService')->record($session, 'session_resumed', ['via' => 'login']);
        } else {
            $session = $this->startNewSession($participant->id, $phaseCode, $locale, $device, 'login');
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

    /** Sesi baru untuk peserta yang sudah terdaftar (mis. posttest). */
    public function startNewSession(
        int $participantId,
        string $phaseCode,
        string $locale,
        array $device,
        string $via = 'new_session',
    ): GameSession {
        $study     = model(ResearchStudyModel::class)->requireActiveStudy();
        $phase     = model(ResearchPhaseModel::class)->requireByCode((int) $study['id'], $this->resolvePhaseCode($study, $phaseCode));
        $release   = model(GameReleaseModel::class)->active();
        $locale    = $this->resolveLocale($locale, $study);

        $db = db_connect();
        $db->transBegin();

        try {
            $session = $this->insertSession(
                $participantId,
                (int) $study['id'],
                (int) $phase['id'],
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

    /** Mengubah bahasa TANPA menyentuh progres atau skor. */
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
            'total_score' => (float) $progress['total_score'],
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

    private function insertSession(
        int $participantId,
        int $studyId,
        int $phaseId,
        int $releaseId,
        string $locale,
        array $device,
    ): GameSession {
        $sessions = model(GameSessionModel::class);
        $now      = date('Y-m-d H:i:s');

        $sessionId = $sessions->insert([
            'session_code'       => random_code(16),
            'participant_id'     => $participantId,
            'study_id'           => $studyId,
            'phase_id'           => $phaseId,
            'release_id'         => $releaseId,
            'locale'             => $locale,
            'status'             => 'active',
            'started_at'         => $now,
            'last_active_at'     => $now,
            'duration_ms'        => 0,
            'device_type'        => $this->nullIfBlank($device['device_type'] ?? null),
            'os_name'            => $this->nullIfBlank($device['os_name'] ?? null),
            'browser_name'       => $this->nullIfBlank($device['browser_name'] ?? null),
            'screen_size'        => $this->nullIfBlank($device['screen_size'] ?? null),
            'is_touch'           => isset($device['is_touch']) ? (int) (bool) $device['is_touch'] : null,
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

    /** Studi menentukan fase aktif; siswa hanya boleh memilih bila diizinkan. */
    private function resolvePhaseCode(array $study, ?string $requested): string
    {
        $requested = trim((string) $requested);

        if ((int) $study['allow_phase_choice'] === 1
            && in_array($requested, config('Gelita')->phases, true)) {
            return $requested;
        }

        return (string) $study['active_phase_code'];
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
