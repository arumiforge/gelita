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
use App\Models\SchoolModel;
use App\Models\ParticipantProgressModel;

/**
 * Siklus hidup akun siswa dan sesi permainan. Satu tahap bermain per studi
 * (perombakan kelas 5: tanpa fase pretest/posttest, tanpa izin wali).
 * Semua operasi menulis memakai transaction; kata sandi mentah tidak pernah
 * disimpan, dicatat, atau dikembalikan kecuali sandi sementara hasil reset guru.
 */
class SessionService
{
    /**
     * `package_mode` when the client did not report one (no JavaScript, or a
     * form without `device_json`). Until the package service worker
     * (asset package Stage 2) exists no device holds a package, so `nonaktif`
     * is true; Stage 2/6 should revisit this once package mode can be on.
     */
    public const DEFAULT_PACKAGE_MODE = 'nonaktif';

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

        // Field mode (28 §6.5): school and class group come from the field session of the token
        $fieldSession = isset($input['field_session']) && is_array($input['field_session']) ? $input['field_session'] : null;

        try {
            if ($fieldSession !== null) {
                $row    = model(SchoolModel::class)->find((int) $fieldSession['school_id']);
                $school = ['id' => (int) $row['id'], 'name' => (string) $row['name']];
                $code   = service('participantCodes')->next($school['id'], (string) $fieldSession['rombel'], (string) $row['research_code']);
            }

            // NPSN → sekolah resmi; nama ketikan → sekolah resmi bila jelas, selain itu
            // entri "belum terverifikasi" (SchoolDirectory::resolve)
            $school ??= service('schoolDirectory')->resolve([
                'npsn'          => $input['school_npsn'] ?? null,
                'name'          => $input['school_name'] ?? null,
                'country_code'  => $input['country_code'] ?? 'ID',
                'province_code' => $input['province_code'] ?? null,
                'district_code' => $input['district_code'] ?? null,
            ]);

            $metrics = (array) ($input['registration_metrics'] ?? []);

            $participantId = $participants->insert([
                'participant_code'         => $code ?? $participants->placeholderCode(),
                'username'                 => $username,
                'password_hash'            => password_hash($password, PASSWORD_DEFAULT),
                'must_change_password'     => 0,
                'password_changed_at'      => date('Y-m-d H:i:s'),
                'display_name'             => $this->nullIfBlank($input['display_name'] ?? null),
                'age'                      => isset($input['age']) && $input['age'] !== '' ? (int) $input['age'] : null,
                'gender'                   => $this->nullIfBlank($input['gender'] ?? null),
                'class_level'              => $this->nullIfBlank($input['class_level'] ?? null),
                'avatar_no'                => isset($input['avatar_no']) && $input['avatar_no'] !== '' ? (int) $input['avatar_no'] : null,
                'school_id'                => $school['id'],
                'school_name_snapshot'     => $school['name'],
                'rombel'                   => $fieldSession === null ? null : (string) $fieldSession['rombel'],
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
                // Part 4 (30 §5.1): which assent text was ticked
                'assent_version'       => $fieldSession === null ? ConsentService::ASSENT_GENERAL : ConsentService::ASSENT_STUDY,
            ], true);

            if ($participantId === false) {
                throw new \RuntimeException('Data pendaftaran ditolak: ' . implode(' ', $participants->errors()));
            }

            $participantId = (int) $participantId;

            if ($fieldSession === null) {
                $participants->assignCode($participantId);
            } else {
                // Part 4 (30 §5.2): the consent row with its defaults, in the same transaction
                service('consentService')->createRow($participantId, (int) $study['id']);
            }

            $device  = (array) ($input['device'] ?? []);
            $session = $this->insertSession(
                $participantId,
                (int) $study['id'],
                $release,
                $locale,
                $device,
                $fieldSession,
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

        // Part 4 (30 §5.3): a withdrawn student's account is closed; told only after the right password
        if ($participant->isDeactivated()) {
            return ['status' => 'inactive', 'session' => null, 'participant' => null];
        }

        if ($participant->mustChangePassword()) {
            $this->bindPhpSession($participant->id, null);

            return ['status' => 'must_change', 'session' => null, 'participant' => $participant];
        }

        $study  = model(ResearchStudyModel::class)->requireActiveStudy();
        $locale = $this->resolveLocale($locale, $study);

        $sessions = model(GameSessionModel::class);
        $existing = $sessions->resumable($participant->id, (int) $study['id']);

        // Field mode: without a game session of an open field session the student types a token first (28 §6.4)
        if ($existing === null && service('fieldSessions')->fieldModeOn()) {
            $this->bindPhpSession($participant->id, null);
            session()->set('locale', $locale);

            return ['status' => 'token', 'session' => null, 'participant' => $participant];
        }

        if ($existing !== null) {
            $sessions->update($existing->id, [
                'status'         => 'active',
                'locale'         => $locale,
                'last_active_at' => date('Y-m-d H:i:s'),
            ]);

            $session = $sessions->find($existing->id);
            service('eventService')->record($session, 'session_resumed', ['via' => 'login']);
            model(DeviceLogModel::class)->record($session->id, $participant->id, 'resume', $device);
            $session = $this->trackPackageOnResume($session, $device);
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
     * Staff reset a student's password (admin by id, researchers by code). Sandi sementara dikembalikan SEKALI
     * untuk ditunjukkan ke siswa dan tidak disimpan di mana pun.
     */
    public function resetPasswordByStaff(int $participantId, int $staffId, string $via = 'id'): string
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
            // `via`: `id` = admin from the student page, `kode` = researcher by participant code (27 §10)
            'metadata'      => ['participant_code' => $participant->participant_code, 'via' => $via],
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
                // Part 7 (33 §5.4): open attempt segments of the session end with the pause
                model(\App\Models\ChallengeAttemptSegmentModel::class)->closeForSession($sessionId, date('Y-m-d H:i:s'));
            }
        }

        session()->destroy();
    }

    /**
     * Enters a field session after an accepted token (28 §6.4): the student's
     * game session of this field session is resumed when there is one (K7),
     * otherwise a new one is made with `field_session_id` and `meeting_no`
     * and `session_started {via: token}`.
     *
     * @param array<string, mixed> $fieldSession
     * @param array<string, mixed> $device       DeviceProfile::capture()
     */
    public function enterFieldSession(int $participantId, array $fieldSession, string $locale, array $device): GameSession
    {
        $sessions = model(GameSessionModel::class);
        $existing = $sessions->inFieldSession($participantId, (int) $fieldSession['id']);

        if ($existing !== null) {
            $sessions->update($existing->id, ['status' => $existing->status === 'completed' ? 'completed' : 'active', 'last_active_at' => date('Y-m-d H:i:s')]);
            $session = $sessions->find($existing->id);
            service('eventService')->record($session, 'session_resumed', ['via' => 'token']);
            model(DeviceLogModel::class)->record($session->id, $participantId, 'resume', $device);
            $this->bindPhpSession($participantId, $session->id);

            return $session;
        }

        $study   = model(ResearchStudyModel::class)->requireActiveStudy();
        $release = model(GameReleaseModel::class)->active();
        $locale  = $this->resolveLocale($locale, $study);

        $db = db_connect();
        $db->transBegin();

        try {
            $session = $this->insertSession($participantId, (int) $study['id'], $release, $locale, $device, $fieldSession);
            service('eventService')->record($session, 'session_started', ['via' => 'token']);
            model(DeviceLogModel::class)->record($session->id, $participantId, 'login', $device);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        $this->bindPhpSession($participantId, $session->id);

        return $session;
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
                $release,
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

    /** Seconds between heartbeats taken from event batches (rencana-log-asinkron R3). */
    public const BATCH_HEARTBEAT_SECONDS = 50;

    /**
     * heartbeat() at most once per BATCH_HEARTBEAT_SECONDS for this session,
     * for callers that run often (every POST /api/events). The throttle lives
     * in the cache, so it holds across tabs and requests.
     *
     * @return bool true when the heartbeat ran
     */
    public function heartbeatIfDue(int $sessionId): bool
    {
        $cache = service('cache');
        $key   = 'gelita_heartbeat_' . $sessionId;

        if ($cache->get($key) !== null) {
            return false;
        }

        $cache->save($key, time(), self::BATCH_HEARTBEAT_SECONDS);
        $this->heartbeat($sessionId);

        return true;
    }

    /**
     * Update last_active_at + akumulasi duration_ms sejak denyut terakhir.
     *
     * Conditional on the `last_active_at` it read (ChallengeService::updateIf()
     * pattern): when two tabs beat at once, only one adds the elapsed time.
     */
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

        // The loser of a race changes nothing: the winner already counted this time
        $sessions->where('last_active_at', $session->last_active_at)->update($sessionId, [
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

        $progress   = model(ParticipantProgressModel::class)->forSession($session);
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

    /** Sandi sementara yang dijamin memenuhi kebijakan dan tidak memuat nama pengguna (also gelita:uji:akun). */
    public function generateTemporaryPassword(string $username): string
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
     * Package columns of a new session (rencana-paket-aset §7.1):
     * `app_client_version` = `{app_version}+{package_code}` (just
     * `{app_version}` when the device has no package), `package_code`, and
     * `package_mode` (DEFAULT_PACKAGE_MODE when the client did not say).
     *
     * @param array<string, mixed> $release game_releases row
     * @param array<string, mixed> $device  DeviceProfile::capture()
     *
     * @return array{app_client_version: ?string, package_code: ?string, package_mode: string}
     */
    public static function packageColumns(array $release, array $device): array
    {
        $signals    = DeviceProfile::packageSignals($device);
        $appVersion = trim((string) ($release['app_version'] ?? ''));
        $code       = $signals['package_code'];
        $version    = $appVersion === '' ? $code : ($code === null ? $appVersion : $appVersion . '+' . $code);

        return [
            'app_client_version' => $version === null ? null : mb_substr($version, 0, 100),
            'package_code'       => $code,
            'package_mode'       => $signals['package_mode'] ?? self::DEFAULT_PACKAGE_MODE,
        ];
    }

    /**
     * A resumed session keeps the package it started with (S8). Sessions from
     * before Stage 5 get their columns filled; a device that reports another
     * package or mode is logged as `package_changed` (reason `resume`).
     *
     * @param array<string, mixed> $device DeviceProfile::capture()
     */
    private function trackPackageOnResume(GameSession $session, array $device): GameSession
    {
        $signals = DeviceProfile::packageSignals($device);

        if ($signals['package_mode'] === null) {
            return $session;   // the client said nothing (no JavaScript, no device_json)
        }

        $sessions = model(GameSessionModel::class);

        if ($session->package_mode === null) {
            $release = model(GameReleaseModel::class)->find($session->release_id);
            $sessions->update($session->id, self::packageColumns(is_array($release) ? $release : [], $device));

            return $sessions->find($session->id);
        }

        if ($signals['package_code'] !== $session->package_code || $signals['package_mode'] !== $session->package_mode) {
            service('eventService')->record($session, 'package_changed', [
                'from'      => $session->package_code,
                'to'        => $signals['package_code'],
                'reason'    => 'resume',
                'from_mode' => $session->package_mode,
                'to_mode'   => $signals['package_mode'],
            ]);
        }

        return $session;
    }

    /**
     * @param array<string, mixed> $release game_releases row (active release)
     * @param array<string, mixed> $device  hasil DeviceProfile::capture(); kolom ringkas
     *                                      game_sessions diisi dari sini
     */
    private function insertSession(
        int $participantId,
        int $studyId,
        array $release,
        string $locale,
        array $device,
        ?array $fieldSession = null,
    ): GameSession {
        $sessions = model(GameSessionModel::class);
        $now      = date('Y-m-d H:i:s');
        $summary  = DeviceProfile::sessionSummary($device);
        $package  = self::packageColumns($release, $device);

        $sessionId = $sessions->insert([
            'session_code'       => random_code(16),
            'participant_id'     => $participantId,
            'study_id'           => $studyId,
            // One game session belongs to exactly one field session (28 K6)
            'field_session_id'   => $fieldSession === null ? null : (int) $fieldSession['id'],
            'meeting_no'         => $fieldSession === null ? null : (int) $fieldSession['meeting_no'],
            'release_id'         => (int) $release['id'],
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
            'app_client_version' => $package['app_client_version'],
            'package_code'       => $package['package_code'],
            'package_mode'       => $package['package_mode'],
            'ip_hash'            => hash_ip(service('request')->getIPAddress()),
        ], true);

        if ($sessionId === false) {
            throw new \RuntimeException('Sesi permainan gagal dibuat: ' . implode(' ', $sessions->errors()));
        }

        $session = $sessions->find((int) $sessionId);

        model(ParticipantProgressModel::class)->forSession($session);

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
