<?php

namespace App\Libraries\Simulation;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Exceptions\ChallengeRuleException;
use App\Libraries\DeviceProfile;
use App\Libraries\PasswordPolicy;
use App\Libraries\RegionDirectory;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeItemModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantFeedbackModel;
use App\Models\ParticipantModel;
use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Siswa simulasi untuk `gelita:simulasi`: riwayat bermain lengkap yang
 * ditulis lewat jalur kode sungguhan, sehingga barisnya persis seperti data
 * siswa asli.
 *
 * - Pendaftaran tiga langkah: isian langkah 1–2 dan metrik sandi disusun
 *   seperti RegisterController, lalu SessionService::registerAndStart().
 * - Masuk/keluar, sesi baru, reset sandi guru: SessionService.
 * - Tantangan: ChallengeService (openNode, submitCheck, submitAnswer,
 *   useHint, abandonAttempt), dengan bentuk jawaban yang sama dengan
 *   ChallengeApiController.
 * - Event klien (answer_selected, dialogue_advanced, library_page_viewed,
 *   reading_progress, overlay_opened, level_opened, page_hidden, …):
 *   EventService::ingest() dengan amplop yang sama dengan core/events.js;
 *   audio: EventService::recordAudio().
 * - Event yang ditulis controller/filter (screen_viewed, intro_completed,
 *   library_opened, feedback_submitted): urutan panggilan yang sama dengan
 *   ScreenViewFilter, GateController, LibraryController, ReflectionController.
 *
 * Semua keputusan simulasi (profil siswa, jawaban, jeda) memakai satu
 * pengacak berbenih, dan pemilihan variasi soal memakai SeededItemModel,
 * sehingga `--seed` yang sama pada database awal yang sama menghasilkan isi
 * yang sama (kecuali id acak seperti uuid dan kode sesi). Jam simulasi:
 * SimClock.
 */
final class Simulator
{
    public const USERNAME_PREFIX = 'sim_';
    public const SCHOOL_PREFIX   = 'Sekolah Simulasi';

    /** Sandi semua siswa simulasi (memenuhi kebijakan sandi), agar sesi lain dapat masuk sebagai mereka. */
    public const PASSWORD = 'Gelita#Sim2026';

    /** Sekolah simulasi: NPSN tidak ada di daftar (centang "Sekolahku tidak ada di daftar") atau provinsi tanpa direktori. */
    private const SCHOOLS = [
        ['name' => 'Sekolah Simulasi Temanggung', 'province' => '33', 'district' => '33.23', 'start' => '07:30'],
        ['name' => 'Sekolah Simulasi Magelang', 'province' => '33', 'district' => '33.08', 'start' => '08:15'],
        ['name' => 'Sekolah Simulasi Wonosobo', 'province' => '33', 'district' => '33.07', 'start' => '09:00'],
        ['name' => 'Sekolah Simulasi Yogyakarta', 'province' => '34', 'district' => '34.71', 'start' => '10:00'],
    ];

    private const FIRST_NAMES = [
        'Adi', 'Bayu', 'Citra', 'Dewi', 'Eka', 'Fajar', 'Gita', 'Hana', 'Indra', 'Joko', 'Kirana', 'Laras',
        'Maya', 'Nanda', 'Oki', 'Putri', 'Rafi', 'Sari', 'Tegar', 'Umi', 'Wulan', 'Yoga', 'Zahra', 'Bima',
    ];

    private const DEVICES = [
        'tablet' => [
            'ua'     => 'Mozilla/5.0 (Linux; Android 13; SM-X200) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36',
            'client' => ['screen_w' => 1340, 'screen_h' => 800, 'viewport_w' => 1340, 'viewport_h' => 708, 'pixel_ratio' => 1.5, 'touch_points' => 5, 'orientation' => 'landscape-primary', 'language' => 'id-ID', 'timezone' => 'Asia/Jakarta'],
        ],
        'phone' => [
            'ua'     => 'Mozilla/5.0 (Linux; Android 12; Redmi Note 11) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Mobile Safari/537.36',
            'client' => ['screen_w' => 393, 'screen_h' => 873, 'viewport_w' => 844, 'viewport_h' => 390, 'pixel_ratio' => 2.75, 'touch_points' => 5, 'orientation' => 'landscape-primary', 'language' => 'id-ID', 'timezone' => 'Asia/Jakarta'],
        ],
        'laptop' => [
            'ua'     => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/127.0.0.0 Safari/537.36',
            'client' => ['screen_w' => 1366, 'screen_h' => 768, 'viewport_w' => 1366, 'viewport_h' => 657, 'pixel_ratio' => 1, 'touch_points' => 0, 'orientation' => 'landscape-primary', 'language' => 'id', 'timezone' => 'Asia/Jakarta'],
        ],
    ];

    /** Hasil pengerjaan batch yang dipaksa untuk siswa pertama, agar setiap jenis hasil pasti ada. */
    private const SHOWCASE = [
        'first', 'fix', 'exhausted', 'first',     // Temanggung: puzzle, rumpang, boleh, cari
        'fix', 'first', 'first', 'exhausted',     // Magelang
        'exhausted', 'fix', 'fix', 'fix',         // Wonosobo
    ];

    private readonly Randomizer $rng;
    private readonly SimClock $clock;
    private readonly ChallengeService $challenges;
    private readonly \ReflectionMethod $objectRef;

    /** Kode layar terakhir per sesi (session PHP `last_screen` di ScreenViewFilter). */
    private ?string $lastScreen = null;

    /** Nomor urut event klien per halaman (core/events.js memulai dari 1 tiap halaman). */
    private int $pageSequence = 0;

    private ?GameSession $session = null;

    /** Belum ada siswa yang lupa sandi (reset guru) pada jalannya perintah ini. */
    private bool $resetDue = true;
    private string $locale        = 'id';
    private string $device        = 'tablet';

    /** @var array<string, int> */
    private array $stats = [
        'siswa' => 0, 'tuntas' => 0, 'belum_selesai' => 0, 'sesi' => 0, 'pengerjaan' => 0,
        'ditinggalkan' => 0, 'kesempatan_habis' => 0, 'petunjuk' => 0, 'ketukan_salah' => 0,
        'petunjuk_dilewati' => 0, 'kritik_saran' => 0, 'event_audio' => 0,
    ];

    public function __construct(
        private readonly BaseConnection $db,
        private readonly int $seed,
        \DateTimeImmutable $start,
        private readonly ?\Closure $log = null,
    ) {
        $this->rng        = new Randomizer(new Mt19937($seed));
        $this->clock      = new SimClock($db, $start);
        $this->challenges = service('challengeService');
        $this->objectRef  = new \ReflectionMethod(ChallengeService::class, 'objectRef');

        // Variasi soal berbenih (lihat SeededItemModel); dipulihkan di run()
        $items = new SeededItemModel();
        $items->useRandomizer(new Randomizer(new Mt19937($seed ^ 0x5eed)));
        Factories::injectMock('models', ChallengeItemModel::class, $items);
    }

    /**
     * Membuat $count siswa simulasi baru (nomor melanjutkan siswa simulasi
     * yang sudah ada).
     *
     * @return array<string, int>
     */
    public function run(int $count): array
    {
        $this->phpSession();

        try {
            $first = $this->nextNumber();
            $start = $this->clock->now();

            for ($i = 0; $i < $count; $i++) {
                $number = $first + $i;
                $school = self::SCHOOLS[$i % count(self::SCHOOLS)];
                $day    = $this->schoolDay($start, intdiv($i, count(self::SCHOOLS)) % 3);

                $this->clock->startAt($this->at($day, $school['start'], $this->rng->getInt(0, 600)));
                $this->student($number, $school, $this->profile($i), $i === 0);
                $this->stats['siswa']++;
                $this->say(sprintf('  %s selesai', $this->username($number)));
            }

            $this->clock->finish();
        } finally {
            Factories::reset('models');

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
        }

        return $this->stats;
    }

    /**
     * SessionService mengikat siswa ke session PHP (regenerate + set). CI4
     * tidak memulai session di CLI, jadi dipakai session PHP bawaan tanpa
     * cookie di folder sementara, dimulai lagi sesudah logout().
     */
    private function phpSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_save_path(sys_get_temp_dir());
        session_start(['use_cookies' => 0, 'use_only_cookies' => 0, 'cache_limiter' => '']);
    }

    // ---------------------------------------------------------------- siswa

    /**
     * Profil perjalanan siswa:
     * - `tuntas`: ketiga wilayah, Pustaka, penutup, kritik & saran;
     * - `berhenti`: berhenti di tengah (pengerjaan terakhir dibiarkan terbuka);
     * - `baru`: daftar dan cerita pembuka, paling banyak satu tantangan lalu Keluar.
     */
    private function profile(int $index): string
    {
        return match ($index) {
            0       => 'tuntas',
            1       => 'berhenti',
            2       => 'baru',
            default => $this->pick(['tuntas' => 60, 'berhenti' => 28, 'baru' => 12]),
        };
    }

    /** @param array{name: string, province: string, district: string, start: string} $school */
    private function student(int $number, array $school, string $profile, bool $showcase): void
    {
        $username     = $this->username($number);
        $this->session    = null;
        $this->lastScreen = null;
        $this->locale = $this->rng->getInt(1, 100) <= 15 ? 'en' : 'id';
        $this->device = $this->pick(['tablet' => 55, 'phone' => 30, 'laptop' => 15]);
        $skill        = $this->rng->getInt(35, 95) / 100;

        $participantId = $this->register($number, $username, $school);
        $this->intro();

        $plan = match ($profile) {
            'tuntas'   => 12,
            'berhenti' => $this->rng->getInt(2, 10),
            default    => $this->rng->getInt(0, 1),
        };

        $done    = 0;
        $sitting = 1;

        foreach (service('contentRepository')->levels() as $level) {
            if ($done >= $plan && $profile !== 'baru') {
                break;
            }

            $this->openRegion($level);

            foreach (service('contentRepository')->nodesForLevel($level->id) as $node) {
                if ($done >= $plan) {
                    break;
                }

                // Satu pertemuan kelas ±4–5 tantangan: sesudahnya keluar dan masuk lagi hari lain
                if ($done > 0 && $done % 4 === 0) {
                    $this->nextSitting($participantId, $username, $school, ++$sitting, $showcase);
                    $this->openRegion($level, true);
                }

                $mode = $showcase ? self::SHOWCASE[$done % count(self::SHOWCASE)] : null;
                $last = $profile === 'berhenti' && $done === $plan - 1;

                $this->challenge($level, $node, $skill, $mode, $last, $profile === 'baru');
                $done++;
            }

            if ($this->levelCompleted($level)) {
                $this->regionDone($level);
                $this->library($level);
            }
        }

        if ($profile === 'baru' && $plan === 0) {
            // Belum membuka tantangan: hanya melihat peta, lalu Pustaka yang masih terkunci
            $first = service('contentRepository')->levels()[0];
            $this->screen('library_locked', '/pustaka/' . $first->code, ['level' => $first->code], ['level_id' => $first->id]);
        }

        if ($profile === 'berhenti' && $this->rng->getInt(1, 100) <= 40) {
            $this->returnLater($username, $school);
        }

        if ($profile === 'tuntas') {
            $this->ending();
            $this->feedback($showcase);
            $this->stats['tuntas']++;
        } else {
            $this->stats['belum_selesai']++;
        }

        // Sebagian siswa menekan Keluar, sisanya menutup tab (sesi tetap `active`)
        if ($profile !== 'berhenti' && $this->rng->getInt(1, 100) <= 70) {
            $this->clock->advance($this->rng->getInt(5_000, 30_000));
            $this->clock->act(fn () => $this->logout());
        }
    }

    /** @param array{name: string, province: string, district: string, start: string} $school */
    private function register(int $number, string $username, array $school): int
    {
        $policy = new PasswordPolicy();
        $name   = self::FIRST_NAMES[($number - 1) % count(self::FIRST_NAMES)] . ' ' . sprintf('Simulasi %03d', $number);

        // Langkah 3 seperti RegisterController: kiriman pertama kadang sandi lemah
        $weak    = $this->rng->getInt(0, 2);
        $first   = $weak > 0 ? $policy->check(['kedu123', 'Kedu2026', 'kedu#2026'][$this->rng->getInt(0, 2)], $username) : $policy->check(self::PASSWORD, $username);
        $region  = $this->region($school);
        $device  = $this->deviceProfile();

        // Langkah 1–3 diisi berselang ±1–3 menit
        $this->clock->advance($this->rng->getInt(60_000, 180_000));

        $result = $this->clock->act(fn () => service('sessionService')->registerAndStart([
            'username'      => $username,
            'password'      => self::PASSWORD,
            'display_name'  => $name,
            'age'           => $this->pick(['10' => 55, '11' => 35, '12' => 10]),
            'gender'        => $this->pick(['laki-laki' => 50, 'perempuan' => 50]),
            'class_level'   => config('Gelita')->registrationClass,
            'school_npsn'   => null,
            // Jalur NPSN: "Sekolahku tidak ada di daftar" → nama sekolah ketikan
            'school_name'   => $school['name'],
            'country_code'  => $region['country_code'],
            'country_name'  => $region['country_name'],
            'province_code' => $region['province_code'],
            'province_name' => $region['province_name'],
            'district_code' => $region['district_code'],
            'district_name' => $region['district_name'],
            'locale'        => $this->locale,
            'assent'        => true,
            'registration_metrics' => [
                'first_submit_criteria' => $first['met'],
                'weak_submit_count'     => $weak,
            ],
            'device' => $device,
        ]), static fn (array $r): int => (int) $r['participant']->id);

        $this->useSession($result['session']);

        return (int) $result['participant']->id;
    }

    /** Cerita pembuka: layar gerbang, slide intro, `/intro/selesai`. */
    private function intro(): void
    {
        $this->screen('gate', '/gerbang');
        $this->screen('intro', '/intro');

        $slides = service('contentRepository')->dialogues(null, 'intro');
        $this->slides($slides, 'intro', null, 3_500);

        $this->clock->advance(1_500);
        $this->clock->act(function (): void {
            $first = model(ParticipantModel::class)->markIntroSeen($this->session->participant_id);
            service('eventService')->record($this->session, 'intro_completed', ['first' => $first]);
        });

        $this->screen('map', '/peta');
        $this->clientEvents([['viewport_changed', [], ['width' => self::DEVICES[$this->device]['client']['viewport_w'], 'height' => self::DEVICES[$this->device]['client']['viewport_h']]]]);
    }

    /** Masuk wilayah: peta, Kenali (kadang), layar wilayah, dialog pembuka. */
    private function openRegion(Level $level, bool $again = false): void
    {
        if (! $again) {
            $this->clientEvents([['level_opened', ['level_id' => $level->id], ['code' => $level->code]]]);

            if ($this->rng->getInt(1, 100) <= 40) {
                $this->clientEvents([['overlay_opened', ['level_id' => $level->id], ['overlay' => 'kenal', 'level' => $level->code]]]);
                $this->clock->advance($this->rng->getInt(8_000, 25_000));
            }
        }

        $this->screen('region', '/wilayah/' . $level->code, ['level' => $level->code], ['level_id' => $level->id]);

        if (! $again) {
            $this->screen('dialogue', '/dialog/' . $level->code, ['code' => $level->code], ['level_id' => $level->id]);
            $this->slides(service('contentRepository')->dialogues($level->id, 'level_open'), 'level_open', $level->id, 4_000);
        }
    }

    /**
     * Satu tantangan: misi → tantangan → hasil. Kadang Keluar di tengah lalu
     * dibuka lagi (pengerjaan baru), atau dibiarkan terbuka (siswa berhenti).
     */
    private function challenge(Level $level, ChallengeNode $node, float $skill, ?string $mode, bool $leaveOpen, bool $quitEarly): void
    {
        $refs = ['level_id' => $level->id];
        $this->screen('brief', "/misi/{$level->code}/{$node->sequence}", ['level' => $level->code, 'seq' => $node->sequence], $refs);
        $this->screen('challenge', "/tantangan/{$level->code}/{$node->sequence}", ['level' => $level->code, 'seq' => $node->sequence], $refs);

        $opened  = $this->clock->act(fn () => $this->challenges->openNode($this->session, $node->id));
        $attempt = $opened['attempt'];
        $payload = $opened['payload'];
        $this->stats['pengerjaan']++;

        if ($payload === null) {
            return;
        }

        // Keluar di tengah tantangan (tombol Keluar dikonfirmasi), lalu dibuka lagi
        $exitFirst = $quitEarly || ($mode === null && $this->rng->getInt(1, 100) <= 8);

        if ($exitFirst) {
            $this->clock->advance($this->rng->getInt(20_000, 90_000));
            $this->clientEvents([['overlay_opened', $this->attemptRefs($attempt, $level), ['overlay' => 'exit', 'level' => $level->code]]]);
            $this->clock->advance($this->rng->getInt(2_000, 6_000));
            $this->clock->act(fn () => $this->challenges->abandonAttempt($this->reload($attempt), ['via' => 'user_exit']));
            $this->stats['ditinggalkan']++;
            $this->screen('region', '/wilayah/' . $level->code, ['level' => $level->code], $refs);

            if ($quitEarly) {
                return;
            }

            $this->clock->advance($this->rng->getInt(30_000, 120_000));
            $this->screen('challenge', "/tantangan/{$level->code}/{$node->sequence}", ['level' => $level->code, 'seq' => $node->sequence], $refs);
            $opened  = $this->clock->act(fn () => $this->challenges->openNode($this->session, $node->id));
            $attempt = $opened['attempt'];
            $payload = $opened['payload'];
            $this->stats['pengerjaan']++;
        }

        if ($node->engine_type === 'cari') {
            $this->playHunt($level, $attempt, $payload, $skill, $mode, $leaveOpen);
        } else {
            $this->playBatch($level, $node, $attempt, $payload, $skill, $mode, $leaveOpen);
        }

        $attempt = $this->reload($attempt);

        if ($attempt->isCompleted()) {
            $this->screen('finished', '/selesai/' . $attempt->id, ['attempt' => $attempt->id], $this->attemptRefs($attempt, $level));
            $this->clock->advance($this->rng->getInt(4_000, 12_000));
            $this->screen('result', "/hasil/{$level->code}/{$node->sequence}", ['level' => $level->code, 'seq' => $node->sequence], $refs);
        }
    }

    /**
     * Susun Gambar, Isi Rumpang, Benar atau Salah: isian satu per satu
     * (answer_selected), lalu "Cek jawabanku" sampai benar semua atau
     * kesempatan habis.
     *
     * $mode (siswa pertama): `first` benar sejak cek pertama; `fix` salah lalu
     * benar; `exhausted` tetap ada yang salah sampai cek ke-3.
     *
     * @param array<string, mixed> $payload
     */
    private function playBatch(Level $level, ChallengeNode $node, ChallengeAttempt $attempt, array $payload, float $skill, ?string $mode, bool $leaveOpen): void
    {
        $refs   = $this->attemptRefs($attempt, $level);
        $items  = $this->items($payload);
        $bank   = array_values(array_map('strval', (array) ($payload['word_bank'] ?? [])));
        $locked = [];
        $opened = $this->clock->now();
        $t      = 0; // ms sejak tantangan dibuka (first_input_ms / last_input_ms)
        $ease   = ['temanggung' => 0.15, 'magelang' => 0.0, 'wonosobo' => -0.15][$level->code] ?? 0.0;

        if (in_array($node->engine_type, ['rumpang', 'boleh'], true)) {
            $this->reading($refs, $payload);
        }

        $this->maybeHint($level, $attempt, $payload, $skill);

        for ($check = 1; $check <= 3; $check++) {
            $answers = [];
            $events  = [];
            $since   = $this->clock->msSince($opened);
            $t       = max($t, $since);
            $wrongs  = $this->wrongPlan($mode, $check, array_diff_key($items, $locked), $skill + $ease);

            foreach ($items as $itemId => $item) {
                if (isset($locked[$itemId])) {
                    continue;
                }

                $right  = ! isset($wrongs[$itemId]);
                $answer = $this->batchAnswer($node, $item, $right, $bank, $check);
                $first  = $t + $this->rng->getInt(2_000, 9_000);
                $t      = $first + $this->rng->getInt(0, 6_000);

                $events[] = ['answer_selected', $refs + ['item_id' => $itemId], $this->selection($item, $answer) + ['t_ms' => $t], $t - $since];
                $answers[$itemId] = $answer + ['first_input_ms' => $first, 'last_input_ms' => $t];
            }

            // Berhenti di tengah: isian sudah dimulai, tetapi tidak pernah dicek
            if ($leaveOpen && $check === 1 && $this->rng->getInt(1, 100) <= 60) {
                $this->clientEvents(array_slice($events, 0, max(1, intdiv(count($events), 2))));
                $this->pageHide($attempt, $level);

                return;
            }

            $this->clientEvents($events);
            $this->clock->advance($this->rng->getInt(1_500, 5_000));

            try {
                $result = $this->clock->act(fn () => $this->challenges->submitCheck($this->reload($attempt), $answers));
            } catch (ChallengeRuleException) {
                return;
            }

            foreach ($result['results'] as $itemId => $ok) {
                if ($ok) {
                    $locked[$itemId] = true;
                }
            }

            if ($result['completed']) {
                if ($result['completion_reason'] === 'tries_exhausted') {
                    $this->stats['kesempatan_habis']++;
                }

                $this->clock->advance($this->rng->getInt(3_000, 8_000));

                return;
            }

            if ($leaveOpen) {
                $this->pageHide($attempt, $level);

                return;
            }

            // Modal "Belum semuanya tepat" → Perbaiki; kadang membuka petunjuk dulu
            $this->clock->advance($this->rng->getInt(3_000, 9_000));
            $this->maybeHint($level, $attempt, $payload, $skill - 0.2);
        }
    }

    /**
     * Cari Objek: satu ketukan = satu percobaan untuk petunjuk aktif. Benar
     * sejak ketukan pertama, salah (jebakan atau benda lain) lalu benar, atau
     * tiga ketukan salah sehingga petunjuk dilewati.
     *
     * @param array<string, mixed> $payload
     */
    private function playHunt(Level $level, ChallengeAttempt $attempt, array $payload, float $skill, ?string $mode, bool $leaveOpen): void
    {
        $clues   = (array) ($payload['clues'] ?? []);
        $refs    = array_map(static fn (array $o): string => (string) $o['ref'], (array) ($payload['objects'] ?? []));
        $ease    = ['temanggung' => 0.1, 'magelang' => 0.0, 'wonosobo' => -0.1][$level->code] ?? 0.0;
        $total   = count($clues);

        $this->maybeHint($level, $attempt, $payload, $skill);

        foreach (array_values($clues) as $index => $clue) {
            $itemId  = (int) $clue['item_id'];
            $correct = (string) $this->objectRef->invoke($this->challenges, $attempt->id, $itemId);
            $others  = array_values(array_filter($refs, static fn (string $ref): bool => $ref !== $correct));

            // Clue narration plays only when the ▶ button is tapped (no autoplay since 12 October 2026)
            $this->clueAudio($attempt, $clue, 'play');

            // Ketukan salah sebelum benar: 0, 1, 2, atau 3 (= dilewati)
            $misses = match (true) {
                $mode === 'exhausted' && $index === $total - 1 => 3,
                $mode === 'fix' && $index === 0                => 1,
                $mode === 'first'                              => 0,
                default                                        => $this->misses($skill + $ease),
            };

            if ($leaveOpen && $index === intdiv($total, 2)) {
                $this->pageHide($attempt, $level);

                return;
            }

            $shown = 0;

            for ($tap = 1; $tap <= min(3, $misses + 1); $tap++) {
                $wrong  = $tap <= $misses;
                $target = $wrong && $others !== [] ? $others[$this->rng->getInt(0, count($others) - 1)] : $correct;
                $shown += $this->rng->getInt(2_500, 14_000);

                $this->clock->advance($this->rng->getInt(2_500, 9_000));

                try {
                    $result = $this->clock->act(fn () => $this->challenges->submitAnswer(
                        $this->reload($attempt),
                        $itemId,
                        ['object' => $target],
                        ['duration_ms' => $shown],
                    ));
                } catch (ChallengeRuleException) {
                    return;
                }

                if ($result['wrong_click']) {
                    $this->stats['ketukan_salah']++;
                    // Modal penjelasan → "Coba lagi"
                    $this->clock->advance($this->rng->getInt(2_000, 6_000));
                }

                if ($result['wrong_click'] && $result['clue_closed']) {
                    $this->stats['petunjuk_dilewati']++;
                }

                if ($result['completed']) {
                    if ($result['completion_reason'] === 'tries_exhausted') {
                        $this->stats['kesempatan_habis']++;
                    }

                    $this->clock->advance($this->rng->getInt(3_000, 8_000));

                    return;
                }

                if ($result['clue_closed']) {
                    break;
                }
            }
        }
    }

    // --------------------------------------------------------- jawaban batch

    /**
     * Soal yang salah pada cek ini.
     *
     * @param array<int, ChallengeItem> $open soal yang belum terkunci
     *
     * @return array<int, true>
     */
    private function wrongPlan(?string $mode, int $check, array $open, float $skill): array
    {
        if ($open === []) {
            return [];
        }

        $ids = array_keys($open);

        if ($mode !== null) {
            return match (true) {
                $mode === 'first'                     => [],
                $mode === 'fix' && $check === 1       => [$ids[0] => true],
                $mode === 'fix'                       => [],
                default /* exhausted */               => [$ids[count($ids) - 1] => true],
            };
        }

        // Peluang benar naik tiap cek (siswa memperbaiki dari tanda ✓/✗)
        $p     = min(0.97, max(0.2, $skill + 0.22 * ($check - 1)));
        $wrong = [];

        foreach ($ids as $id) {
            if ($this->rng->getFloat(0, 1) > $p) {
                $wrong[$id] = true;
            }
        }

        return $wrong;
    }

    /**
     * Jawaban satu soal batch, dalam bentuk kiriman klien.
     *
     * @param list<string> $bank
     *
     * @return array<string, mixed>
     */
    private function batchAnswer(ChallengeNode $node, ChallengeItem $item, bool $right, array $bank, int $check): array
    {
        if ($item->interaction_type === 'puzzle_arrange') {
            $order = array_values(array_map('intval', (array) $item->answerKey('order')));

            if (! $right && count($order) > 1) {
                // Dua keping tertukar (makin sedikit yang tertukar pada cek berikutnya)
                $swaps = max(1, 3 - $check);

                for ($s = 0; $s < $swaps; $s++) {
                    $a = $this->rng->getInt(0, count($order) - 1);
                    $b = ($a + $this->rng->getInt(1, count($order) - 1)) % count($order);
                    [$order[$a], $order[$b]] = [$order[$b], $order[$a]];
                }

                if ($order === array_values(array_map('intval', (array) $item->answerKey('order')))) {
                    [$order[0], $order[1]] = [$order[1], $order[0]];
                }
            }

            return ['order' => $order];
        }

        if ($item->interaction_type === 'fill_blank_bank') {
            $key = (string) ($item->answerKey('text_' . $this->locale) ?? $item->answerKey('text_id') ?? '');

            if ($right) {
                return ['text' => $key];
            }

            $choices = array_values(array_filter($bank, static fn (string $w): bool => mb_strtolower(trim($w)) !== mb_strtolower(trim($key))));

            return ['text' => $choices === [] ? $key . 'x' : $choices[$this->rng->getInt(0, count($choices) - 1)]];
        }

        $verdict = (string) $item->verdict();

        if ($right) {
            return ['verdict' => $verdict];
        }

        $options = array_values(array_diff($node->verdictOptions(), [$verdict]));

        return ['verdict' => $options[0] ?? ($verdict === 'benar' ? 'salah' : 'benar')];
    }

    /**
     * Payload `answer_selected` seperti engine klien.
     *
     * @param array<string, mixed> $answer
     *
     * @return array<string, mixed>
     */
    private function selection(ChallengeItem $item, array $answer): array
    {
        return match ($item->interaction_type) {
            'puzzle_arrange'  => ['action' => 'swap', 'value' => null, 'from' => $this->rng->getInt(0, 8), 'to' => $this->rng->getInt(0, 8)],
            'fill_blank_bank' => ['action' => 'set', 'value' => $answer['text']],
            default           => ['action' => 'set', 'value' => $answer['verdict']],
        };
    }

    private function misses(float $skill): int
    {
        $roll = $this->rng->getFloat(0, 1);

        return match (true) {
            $roll < $skill        => 0,
            $roll < $skill + 0.15 => 1,
            $roll < $skill + 0.22 => 2,
            default               => 3,
        };
    }

    // ------------------------------------------------------------- petunjuk

    /** @param array<string, mixed> $payload */
    private function maybeHint(Level $level, ChallengeAttempt $attempt, array $payload, float $skill): void
    {
        $hints = (array) ($payload['hints'] ?? []);

        if ($hints === [] || $this->rng->getFloat(0, 1) < $skill) {
            return;
        }

        $used = (int) $this->reload($attempt)->hint_count;
        $hint = $hints[$used] ?? null;

        if ($hint === null || ! $this->reload($attempt)->isInProgress()) {
            return;
        }

        $this->clientEvents([['overlay_opened', $this->attemptRefs($attempt, $level), ['overlay' => 'hint', 'level' => $level->code]]]);

        // Kadang batal di dialog konfirmasi
        if ($this->rng->getInt(1, 100) <= 20) {
            $this->clock->advance($this->rng->getInt(1_000, 3_000));

            return;
        }

        $this->clock->advance($this->rng->getInt(1_500, 4_000));
        $this->clock->act(fn () => $this->challenges->useHint(
            $this->reload($attempt),
            (int) $hint['id'],
            isset($hint['item_id']) ? (int) $hint['item_id'] : null,
        ));
        $this->stats['petunjuk']++;
        $this->clock->advance($this->rng->getInt(5_000, 15_000));
    }

    // ---------------------------------------------------------- navigasi

    private function regionDone(Level $level): void
    {
        $this->screen('region_done', '/tuntas/' . $level->code, ['level' => $level->code], ['level_id' => $level->id]);
        $this->slides(service('contentRepository')->dialogues($level->id, 'level_done'), 'level_done', $level->id, 4_000);
    }

    /** Pustaka wilayah yang sudah tuntas: halaman demi halaman, sebagian siswa berhenti di tengah. */
    private function library(Level $level): void
    {
        if ($this->rng->getInt(1, 100) > 70) {
            return;
        }

        $this->screen('library_index', '/pustaka');
        $this->screen('library', '/pustaka/' . $level->code, ['level' => $level->code], ['level_id' => $level->id], function () use ($level): void {
            service('eventService')->record($this->session, 'library_opened', [], ['level_id' => $level->id]);
        });

        $pages = service('contentRepository')->libraryPages($level->id);
        $read  = $pages === [] ? 0 : $this->rng->getInt(1, count($pages));
        $rows  = [];
        $t     = 0;

        foreach (array_slice($pages, 0, $read) as $index => $page) {
            $t     += $this->rng->getInt(6_000, 40_000);
            $rows[] = ['library_page_viewed', ['level_id' => $level->id], ['page' => $index + 1, 'total' => count($pages), 'page_id' => $page->id], $t];
        }

        if ($rows !== []) {
            if ($this->rng->getInt(1, 100) <= 25) {
                $t     += 4_000;
                $rows[] = ['page_hidden', [], ['screen' => 'library'], $t];
                $t     += $this->rng->getInt(20_000, 120_000);
                $rows[] = ['page_visible', [], ['screen' => 'library'], $t];
            }

            $this->clientEvents($rows);
        }
    }

    private function ending(): void
    {
        $this->screen('ending', '/penutup');
        $this->slides(service('contentRepository')->dialogues(null, 'ending'), 'ending', null, 4_500);
    }

    /** Balai Refleksi: seperti ReflectionController::store(), minimal dua jawaban terbuka. */
    private function feedback(bool $always): void
    {
        if (! $always && $this->rng->getInt(1, 100) > 80) {
            return;
        }

        $this->screen('reflection', '/refleksi');
        $this->clock->advance($this->rng->getInt(60_000, 240_000));

        $texts = [
            'liked_most'   => ['Cari benda di candi seru sekali.', 'Aku suka cerita Jaka dan Mbah Kedu.', 'Menyusun gambar candi.'],
            'hardest_part' => ['Bacaan Wonosobo panjang.', 'Isi rumpang yang katanya mirip.', 'Benar atau salah di Magelang.'],
            'new_learning' => ['Aku jadi tahu tari Lengger.', 'Tembakau Temanggung terkenal.', 'Candi Borobudur ada di Magelang.'],
            'suggestion'   => ['Tambah wilayah lagi.', 'Suaranya dibuat lebih keras.', null],
        ];
        $answers = [];

        foreach ($texts as $field => $choices) {
            $answers[$field] = $choices[$this->rng->getInt(0, count($choices) - 1)];
        }

        $filled = count(array_filter($answers, static fn ($v): bool => $v !== null));
        $rating = $this->pick(['5' => 50, '4' => 35, '3' => 12, '2' => 3]);

        $this->clock->act(function () use ($answers, $filled, $rating): void {
            model(ParticipantFeedbackModel::class)->insert([
                'participant_id' => $this->session->participant_id,
                'session_id'     => $this->session->id,
                'rating'         => (int) $rating,
                'submitted_at'   => date('Y-m-d H:i:s'),
            ] + $answers, false);

            service('eventService')->record($this->session, 'feedback_submitted', [
                'rating'       => (int) $rating,
                'open_answers' => $filled,
            ]);
        });

        $this->stats['kritik_saran']++;
        $this->screen('reflection', '/refleksi');
    }

    // ------------------------------------------------------------- sesi

    /**
     * Pertemuan berikutnya: hari sekolah lain. Sesi lama ditutup tab
     * (tetap active), Keluar (paused), atau sesekali dibiarkan sampai
     * retensi menandainya ditinggalkan (masuk lagi = sesi baru). Satu dari
     * sepuluh siswa lupa sandi: guru mereset, siswa mengganti sandi.
     *
     * @param array{name: string, province: string, district: string, start: string} $school
     */
    private function nextSitting(int $participantId, string $username, array $school, int $sitting, bool $showcase): void
    {
        $roll = $this->rng->getInt(1, 100);

        if ($roll <= 55) {
            $this->clock->act(fn () => $this->logout());
        }

        $day = $this->schoolDay($this->clock->now()->modify('+1 day'), $this->rng->getInt(0, 2));
        $this->clock->jumpTo($this->at($day, $school['start'], $this->rng->getInt(0, 900)));
        $this->lastScreen   = null;

        // Lupa sandi: siswa pertama (selain siswa pertama simulasi) yang sampai pertemuan kedua, lalu ±1 dari 12
        if ($sitting === 2 && ! $showcase && ($this->resetDue || $this->rng->getInt(1, 12) === 1)) {
            $this->resetDue = false;
            $this->forgotPassword($participantId, $username);

            return;
        }

        $login = $this->clock->act(fn () => service('sessionService')->login($username, self::PASSWORD, $this->locale, $this->deviceProfile()));
        $this->useSession($login['session']);
        $this->screen('gate', '/gerbang');
        $this->screen('map', '/peta');

        // Sesekali pindah bahasa di tengah perjalanan (progres tidak berubah)
        if ($this->rng->getInt(1, 100) <= 8) {
            $to = $this->locale === 'id' ? 'en' : 'id';
            $this->clock->act(fn () => service('sessionService')->setLocale($this->session->id, $to));
            $this->locale = $to;
            $this->useSession(model(GameSessionModel::class)->find($this->session->id));
        }
    }

    /**
     * Siswa yang berhenti kembali sepekan kemudian: sesi lamanya sudah
     * ditinggalkan (SessionService::abandon(), seperti retensi untuk sesi yang
     * lama tidak tersentuh), jadi masuk membuka sesi baru dari awal. Ia
     * melihat peta lalu menutup tab.
     *
     * @param array{name: string, province: string, district: string, start: string} $school
     */
    private function returnLater(string $username, array $school): void
    {
        $this->clock->advance(7 * 86_400_000);
        $this->clock->act(fn () => service('sessionService')->abandon($this->session->id, 'idle'));

        $day = $this->schoolDay($this->clock->now()->modify('+1 day'), 0);
        $this->clock->jumpTo($this->at($day, $school['start'], $this->rng->getInt(0, 900)));
        $this->lastScreen = null;

        $login = $this->clock->act(fn () => service('sessionService')->login($username, self::PASSWORD, $this->locale, $this->deviceProfile()));
        $this->useSession($login['session']);
        $this->screen('gate', '/gerbang');
        $this->screen('map', '/peta');
    }

    private function forgotPassword(int $participantId, string $username): void
    {
        // Dua kali salah ketik, lalu guru mereset sandi di panel
        foreach ([1, 2] as $miss) {
            $this->clock->act(fn () => service('sessionService')->login($username, 'gelita' . $miss, $this->locale, $this->deviceProfile()));
            $this->clock->advance($this->rng->getInt(5_000, 15_000));
        }

        $staffId   = (int) ($this->db->table('staff_users')->select('id')->orderBy('id', 'ASC')->get()->getRow('id') ?? 0);
        $temporary = $this->clock->act(fn () => service('sessionService')->resetPasswordByStaff($participantId, $staffId));
        $this->clock->advance($this->rng->getInt(60_000, 180_000));

        // Seperti LoginController: must_change → ganti sandi → login → event password_changed (via reset)
        $this->clock->act(fn () => service('sessionService')->login($username, $temporary, $this->locale, $this->deviceProfile()));
        $this->clock->advance($this->rng->getInt(30_000, 90_000));
        $login = $this->clock->act(function () use ($participantId, $username, $temporary): array {
            service('sessionService')->changePassword($participantId, $temporary, self::PASSWORD);
            $login = service('sessionService')->login($username, self::PASSWORD, $this->locale, $this->deviceProfile());
            service('eventService')->record($login['session'], 'password_changed', ['via' => 'reset']);

            return $login;
        });

        $this->useSession($login['session']);
        $this->screen('gate', '/gerbang');
        $this->screen('map', '/peta');
    }

    /** Tombol Keluar: SessionService::logout() menghapus session PHP; dimulai lagi untuk siswa berikutnya. */
    private function logout(): void
    {
        service('sessionService')->logout();
        $this->phpSession();
    }

    private function useSession(GameSession $session): void
    {
        if ($this->session === null || $this->session->id !== $session->id) {
            $this->stats['sesi']++;
        }

        $this->session = $session;
        $this->clock->bindSession($session->id);
    }

    private function levelCompleted(Level $level): bool
    {
        $summary = service('scoringService')->levelSummary($this->session->id, $level->id);

        return (int) $summary['completed_nodes'] >= (int) $summary['total_nodes'] && (int) $summary['total_nodes'] > 0;
    }

    // ------------------------------------------------------------- event

    /**
     * Layar game yang dirender (event `screen_viewed` seperti ScreenViewFilter).
     *
     * @param array<string, int|string> $params
     * @param array<string, int>        $refs
     */
    private function screen(string $screen, string $path, array $params = [], array $refs = [], ?\Closure $controller = null): void
    {
        $this->clock->advance($this->rng->getInt(1_200, 4_000));
        $this->pageSequence = 0;

        $this->clock->act(function () use ($screen, $path, $params, $refs, $controller): void {
            $controller?->__invoke();
            service('eventService')->record($this->session, 'screen_viewed', [
                'screen' => $screen,
                'path'   => $path,
                'from'   => $this->lastScreen,
                'params' => $params === [] ? (object) [] : $params,
            ], $refs);
        });

        $this->lastScreen = $screen;
    }

    /**
     * Slide narasi/dialog: `dialogue_advanced` per slide, narasinya diputar
     * otomatis dan biasanya didengar sampai habis.
     *
     * @param list<object> $slides
     */
    private function slides(array $slides, string $context, ?int $levelId, int $perSlide): void
    {
        $rows  = [];
        $plays = [];
        $t     = 0;
        $total = count($slides);
        $base  = $this->clock->now();

        foreach ($slides as $index => $slide) {
            $t     += $this->rng->getInt($perSlide - 1_500, $perSlide + 3_000);
            $rows[] = ['dialogue_advanced', ['level_id' => $levelId], [
                'index' => $index + 1, 'total' => $total, 'context' => $context, 'character' => $slide->character_code ?? null,
            ], $t];

            $audioId = (int) ($this->locale === 'en' ? ($slide->audio_en_asset_id ?? 0) : ($slide->audio_id_asset_id ?? 0));

            if ($audioId > 0) {
                $plays[] = [$audioId, $index === 0 ? 'play' : 'autoplay', $t, null];

                if ($this->rng->getInt(1, 100) <= 85) {
                    $plays[] = [$audioId, 'complete', $t + $perSlide - 1_000, $perSlide - 1_000];
                }
            }
        }

        $this->clientEvents($rows);

        // Telemetry audio dikirim kanal tersendiri (emitAudio), sesudah slide-nya
        foreach ($plays as [$audioId, $action, $at, $listened]) {
            $this->audio($audioId, $action, null, $this->isoAt($base, $at), $listened);
        }
    }

    /** @param array<string, mixed> $clue */
    private function clueAudio(ChallengeAttempt $attempt, array $clue, string $action): void
    {
        $audioId = (int) ($clue['audio_id'] ?? 0);

        if ($audioId <= 0) {
            // Narasi petunjuk yang belum disetujui tidak dikirim ke klien; pakai rekaman draft-nya
            $item    = model(ChallengeItemModel::class)->find((int) $clue['item_id']);
            $audioId = (int) ($item?->promptAudioId($this->locale) ?? 0);
        }

        if ($audioId > 0 && $this->rng->getInt(1, 100) <= 60) {
            $this->audio($audioId, $action, $attempt->id, $this->clock->iso());
            $this->clock->advance(3_200);
            $this->audio($audioId, 'complete', $attempt->id, $this->clock->iso(-200), 3_000);
        }
    }

    private function audio(int $audioId, string $action, ?int $attemptId, string $occurredAt, ?int $listened = null): void
    {
        $this->clock->act(fn () => service('eventService')->recordAudio($this->session, [
            'audio_asset_id'  => $audioId,
            'action'          => $action,
            'listened_ms'     => $listened ?? 0,
            'completed'       => $action === 'complete',
            'attempt_id'      => $attemptId,
            'occurred_at'     => $occurredAt,
            'client_event_id' => uuid4(),
        ]));
        $this->stats['event_audio']++;
    }

    /** Batas gulir panel bacaan 25/50/75/100 % (core/device.js trackReading). */
    private function reading(array $refs, array $payload): void
    {
        $passage = (array) (array_values((array) ($payload['passages'] ?? []))[0] ?? []);
        $marks   = [25, 50, 75, 100];
        $reach   = $this->rng->getInt(2, 4);
        $rows    = [];
        $t       = 0;

        foreach (array_slice($marks, 0, $reach) as $mark) {
            $t     += $this->rng->getInt(5_000, 20_000);
            $rows[] = ['reading_progress', $refs, (isset($passage['id']) ? ['passage_id' => (int) $passage['id']] : []) + ['percent' => $mark], $t];
        }

        $this->clientEvents($rows);
    }

    /** Siswa menutup tab di tengah tantangan: hanya event `challenge_abandoned` lewat beacon; pengerjaan tetap terbuka. */
    private function pageHide(ChallengeAttempt $attempt, Level $level): void
    {
        $this->clientEvents([
            ['page_hidden', [], ['screen' => 'challenge'], 1_000],
            ['challenge_abandoned', $this->attemptRefs($attempt, $level), ['via' => 'pagehide', 'attempt_open' => true], 1_050],
        ]);
        $this->stats['ditinggalkan']++;
    }

    /**
     * Event klien lewat EventService::ingest(), amplop sama dengan core/events.js.
     * Setiap baris: [tipe, refs, payload, ms sejak awal rangkaian]. Jam
     * simulasi maju sampai event terakhir; batch dikirim saat itu.
     *
     * @param list<array{0: string, 1: array<string, ?int>, 2: array<string, mixed>, 3?: int}> $rows
     */
    private function clientEvents(array $rows): void
    {
        if ($rows === []) {
            return;
        }

        $base   = $this->clock->now();
        $end    = max(array_map(static fn (array $r): int => (int) ($r[3] ?? 0), $rows));
        $events = [];

        foreach ($rows as $row) {
            [$type, $refs, $payload] = $row;

            $events[] = [
                'client_event_id' => uuid4(),
                'event_type'      => $type,
                // jam simulasi + jarak baris ini dari awal rangkaian
                'occurred_at'     => $this->isoAt($base, (int) ($row[3] ?? 0)),
                'sequence_no'     => ++$this->pageSequence,
                'level_id'        => $refs['level_id'] ?? null,
                'node_id'         => $refs['node_id'] ?? null,
                'attempt_id'      => $refs['attempt_id'] ?? null,
                'item_id'         => $refs['item_id'] ?? null,
                'payload'         => $payload,
            ];
        }

        $this->clock->advance($end + $this->rng->getInt(200, 3_000));
        $this->clock->act(fn () => service('eventService')->ingest($this->session, $events));
    }

    private function isoAt(\DateTimeImmutable $base, int $ms): string
    {
        return $base->modify(sprintf('+%d seconds +%d microseconds', intdiv($ms, 1000), ($ms % 1000) * 1000))
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.v\Z');
    }

    // ------------------------------------------------------------- bantuan

    /** @return array<string, int> */
    private function attemptRefs(ChallengeAttempt $attempt, Level $level): array
    {
        return ['level_id' => $level->id, 'node_id' => (int) $attempt->challenge_node_id, 'attempt_id' => (int) $attempt->id];
    }

    /**
     * Soal payload yang diminta dijawab (tanpa pengecoh), keyed by id.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<int, ChallengeItem>
     */
    private function items(array $payload): array
    {
        $out = [];

        foreach ((array) ($payload['items'] ?? []) as $row) {
            $item = model(ChallengeItemModel::class)->find((int) $row['id']);

            if ($item === null || $item->isDecoy() || ! $item->is_active) {
                continue;
            }

            $out[$item->id] = $item;
        }

        return $out;
    }

    private function reload(ChallengeAttempt $attempt): ChallengeAttempt
    {
        return model(ChallengeAttemptModel::class)->find($attempt->id);
    }

    /** @return array<string, mixed> */
    private function deviceProfile(): array
    {
        $device = self::DEVICES[$this->device];

        return DeviceProfile::fromParts($device['ua'], [], $device['client'], '10.20.' . $this->rng->getInt(0, 3) . '.' . $this->rng->getInt(2, 250));
    }

    /**
     * Wilayah terverifikasi seperti RegisterController::verifiedRegion().
     *
     * @param array{province: string, district: string} $school
     *
     * @return array<string, ?string>
     */
    private function region(array $school): array
    {
        $directory = RegionDirectory::all();
        $province  = RegionDirectory::province($school['province']);

        return [
            'country_code'  => 'ID',
            'country_name'  => $directory['country']['name'] ?? null,
            'province_code' => $province === null ? null : $school['province'],
            'province_name' => $province['name'] ?? null,
            'district_code' => $school['district'],
            'district_name' => RegionDirectory::districtName($school['district']),
        ];
    }

    private function username(int $number): string
    {
        return self::USERNAME_PREFIX . sprintf('%03d', $number);
    }

    /** Nomor siswa simulasi berikutnya (perintah dapat dijalankan berulang). */
    private function nextNumber(): int
    {
        $rows = $this->db->table('participants')->select('username')
            ->like('username', self::USERNAME_PREFIX, 'after')
            ->get()->getResultArray();
        $max = 0;

        foreach ($rows as $row) {
            if (preg_match('/^' . preg_quote(self::USERNAME_PREFIX, '/') . '(\d+)$/', (string) $row['username'], $m) === 1) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max + 1;
    }

    /** Hari sekolah (Senin–Sabtu) ke-$n sesudah $from (hari $from sendiri dihitung bila hari sekolah). */
    private function schoolDay(\DateTimeImmutable $from, int $n): \DateTimeImmutable
    {
        $day = $from->setTime(0, 0);

        while ((int) $day->format('N') === 7) {
            $day = $day->modify('+1 day');
        }

        for ($k = 0; $k < $n; $k++) {
            $day = $day->modify('+1 day');

            while ((int) $day->format('N') === 7) {
                $day = $day->modify('+1 day');
            }
        }

        return $day;
    }

    private function at(\DateTimeImmutable $day, string $hhmm, int $plusSeconds): \DateTimeImmutable
    {
        [$h, $m] = array_map('intval', explode(':', $hhmm));

        return $day->setTime($h, $m)->modify('+' . $plusSeconds . ' seconds');
    }

    /**
     * Pilihan berbobot.
     *
     * @param array<string, int> $weights
     */
    private function pick(array $weights): string
    {
        $roll = $this->rng->getInt(1, array_sum($weights));

        foreach ($weights as $value => $weight) {
            $roll -= $weight;

            if ($roll <= 0) {
                return (string) $value;
            }
        }

        return (string) array_key_last($weights);
    }

    private function say(string $line): void
    {
        $this->log?->__invoke($line);
    }
}
