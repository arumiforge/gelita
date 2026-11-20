<?php

namespace App\Libraries\Simulation;

use App\Entities\Level;
use App\Libraries\InstrumentImporter;
use App\Models\FieldSessionModel;
use App\Models\ParticipantModel;
use App\Models\ResearchStudyModel;
use App\Models\SchoolModel;
use App\Services\FieldTestService;
use App\Services\InstrumentScorer;
use App\Services\InstrumentService;
use App\Services\SeatService;
use CodeIgniter\Config\Factories;
use Config\Services;

/**
 * Simulated students in research mode (`gelita:simulasi` while the active
 * study has `field_mode = 1` and the `lima_pertemuan` plan; owner, 20
 * November 2026). The same real code paths as Simulator, now in field
 * sessions as a field day runs them (28, 33, 34):
 *
 * - research schools `Sekolah Simulasi Kedu N` with codes S01, S02, … (the
 *   next free ones), one or more class groups (rombel) of at most
 *   ROMBEL_MAX students;
 * - one school finishes P1–P5 (and its make-ups) before the next starts;
 * - per meeting: the researcher opens the field session
 *   (FieldSessionService::create, or createMakeup for absent students),
 *   students enter with the token (registration at P1, login + token
 *   after), answer the sound check, play the meeting's regions with the
 *   challenge primitives of Simulator, and the researcher closes the
 *   session (game sessions paused, open attempts left open for later);
 * - P1: seats from L2 (SeatService), the L7 consent register
 *   (ConsentService), Mulai tes awal (FieldTestService), pre-test and
 *   questionnaire (InstrumentService), then the opening story;
 * - P5: leftovers of every region, Mulai tes akhir, post-test, risk items
 *   (when consented), ending, attitude scale and feedback;
 * - absences with make-up sessions, a few withdrawals (`withdrawn_keep`)
 *   and students who left school (`left`), rapid guessers in the tests.
 *
 * Time: field sessions are opened on the real clock (their tokens must be
 * open while the services check them) and moved to their simulated date
 * and hours once the school is finished; every student's rows move with
 * SimClock as in Simulator; the test module runs on the simulation clock
 * itself (SimInstrumentService).
 */
final class FieldSimulator extends Simulator
{
    public const SCHOOL_NAME = 'Sekolah Simulasi Kedu';

    /** Largest class group; a bigger school gets class groups A, B, C, … */
    public const ROMBEL_MAX = 28;

    /** School day of each meeting, counted from the school's first day (P1 to P5 in about two weeks, 13). */
    private const MEETING_DAYS = [1 => 0, 2 => 2, 3 => 4, 4 => 7, 5 => 9];

    /** Length of each meeting in minutes. */
    private const MEETING_MINUTES = [1 => 110, 2 => 80, 3 => 80, 4 => 80, 5 => 140];

    /** Opening time of class groups A, B, C, D on a meeting day. */
    private const ROMBEL_START = ['07:30', '10:00', '12:45', '07:15'];

    /** Kedu districts for the schools, in turn: Temanggung, Magelang, Wonosobo. */
    private const DISTRICTS = ['33.23', '33.08', '33.07'];

    /** @var array<int, array<string, mixed>> student state by number */
    private array $roll = [];

    private ?int $staffId = null;

    /** @var array<int, array<string, \DateTimeImmutable|string|null>> simulated times per field session id */
    private array $sessionTimes = [];

    /** @var array<string, int> */
    private array $fieldStats = [
        'sekolah' => 0, 'rombel' => 0, 'sesi_lapangan' => 0, 'sesi_susulan' => 0, 'tidak_hadir' => 0,
        'tes_awal' => 0, 'tes_akhir' => 0, 'angket' => 0, 'butir_risiko' => 0, 'skala_sikap' => 0,
        'mundur' => 0, 'pindah' => 0, 'tanpa_izin_ortu' => 0,
    ];

    public function __construct(\CodeIgniter\Database\BaseConnection $db, int $seed, \DateTimeImmutable $start, ?\Closure $log = null)
    {
        parent::__construct($db, $seed, $start, $log);
        $this->reopenAfterExit = false;
    }

    /**
     * Number of school days the run needs for $schools schools, for the
     * command's start date (each school about two weeks, then a free week).
     */
    public static function weeksNeeded(int $schools): int
    {
        return 3 * max(1, $schools) + 1;
    }

    /**
     * Plays the given schools one after another; $sizes are the students per
     * school.
     *
     * @param list<int> $sizes
     *
     * @return array<string, int>
     */
    public function runSchools(array $sizes): array
    {
        $study = model(ResearchStudyModel::class)->fieldModeStudy();

        if ($study === null || (string) ($study['meeting_plan'] ?? '') !== 'lima_pertemuan') {
            throw new \RuntimeException('mode penelitian dengan rencana lima pertemuan dibutuhkan.');
        }

        $this->phpSession();
        $clock = $this->clock;
        Services::injectMock('instruments', new SimInstrumentService(static fn (): \DateTimeImmutable => $clock->now()));
        Services::resetSingle('instrumentGate');
        service('fieldSessions')->forget();

        try {
            $this->staffId = $this->researcherId();
            $number        = $this->nextNumber();
            $day           = $this->schoolDay($this->clock->now(), 0);
            $schools       = $this->researchSchools(count($sizes), $day);

            foreach ($sizes as $index => $size) {
                $school = $schools[$index];
                $last   = $this->school($school, $size, $number, $day);
                $number += $size;
                $this->fieldStats['sekolah']++;
                $this->say(sprintf('  %s (%s): %d siswa selesai', $school['name'], $school['research_code'], $size));

                // The next school starts on the Monday after a free week (one school at a time)
                $day = $last->modify('monday next week')->modify('+1 week');
            }

            $this->clock->finish();
            $this->tally();
        } finally {
            Factories::reset('models');
            Services::resetSingle('instruments');
            Services::resetSingle('instrumentGate');
            service('fieldSessions')->forget();

            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }
        }

        return $this->fieldStats + $this->stats;
    }

    /** Students registered, and those who completed every challenge, for the command's table. */
    private function tally(): void
    {
        $levels = service('contentRepository')->levels();

        foreach ($this->roll as $student) {
            if ($student['pid'] === null) {
                continue;
            }

            $this->stats['siswa']++;
            $all = true;

            foreach ($levels as $level) {
                $all = $all && $this->regionComplete((int) $student['pid'], $level);
            }

            $this->stats[$all ? 'tuntas' : 'belum_selesai']++;
        }
    }

    // ------------------------------------------------------------ schools

    /**
     * One school: class groups, five meetings per class group, make-ups,
     * then its field sessions move to their simulated hours. Returns the
     * last school day used.
     *
     * @param array<string, mixed> $school
     */
    private function school(array $school, int $size, int $firstNumber, \DateTimeImmutable $day): \DateTimeImmutable
    {
        $groups  = (int) ceil($size / self::ROMBEL_MAX);
        $rombels = [];
        $offset  = 0;

        for ($g = 0; $g < $groups; $g++) {
            $count               = intdiv($size, $groups) + ($g < $size % $groups ? 1 : 0);
            $letter              = chr(ord('A') + $g);
            $rombels[$letter]    = range($firstNumber + $offset, $firstNumber + $offset + $count - 1);
            $offset             += $count;
            $this->fieldStats['rombel']++;

            foreach ($rombels[$letter] as $number) {
                $this->roll[$number] = $this->newStudent($number, $school, $letter);
            }
        }

        $last  = $day;
        $first = array_key_first($this->sessionTimes) === null ? 0 : max(array_keys($this->sessionTimes));

        foreach (self::MEETING_DAYS as $no => $offsetDays) {
            $date = $this->schoolDay($day, $offsetDays);
            $last = $date;
            $g    = 0;

            foreach ($rombels as $letter => $numbers) {
                $opens = $this->at($date, self::ROMBEL_START[$g % count(self::ROMBEL_START)], $this->rng->getInt(0, 4) * 60);
                $main  = $this->meeting($school, $letter, $no, $opens, $numbers, null);
                $g++;

                // Absent students who come to the make-up the next school day, in the afternoon
                $makeup = array_values(array_filter($numbers, fn (int $n): bool => ($this->roll[$n]['absent'][$no] ?? false) && ($this->roll[$n]['makeup'][$no] ?? false) && $this->available($n, $no)));

                if ($makeup !== []) {
                    $makeupDay = $this->schoolDay($date->modify('+1 day'), 0);
                    $last      = max($last, $makeupDay);
                    $this->meeting($school, $letter, $no, $this->at($makeupDay, '13:00', $g * 600), $makeup, $main);
                }
            }
        }

        $this->retimeSchoolSessions($first);

        return $last;
    }

    /** @return array<string, mixed> */
    private function newStudent(int $number, array $school, string $rombel): array
    {
        $absent = [];
        $makeup = [];

        foreach (array_keys(self::MEETING_DAYS) as $no) {
            $absent[$no] = $this->rng->getInt(1, 100) <= ($no === 1 ? 4 : 7);
            $makeup[$no] = $this->rng->getInt(1, 100) <= 80;
        }

        $roll = $this->rng->getInt(1, 1000);

        return [
            'number'   => $number,
            'username' => $this->username($number),
            'pid'      => null,
            'session'  => null,
            'now'      => null,
            'school'   => $school,
            'rombel'   => $rombel,
            'locale'   => $this->rng->getInt(1, 100) <= 6 ? 'en' : 'id',
            'device'   => $this->pick(['chromebook' => 55, 'tablet' => 30, 'laptop' => 15]),
            'skill'    => $this->rng->getInt(30, 92) / 100,
            'rapid'    => $this->rng->getInt(1, 100) <= 6,
            'absent'   => $absent,
            'makeup'   => $makeup,
            // 2% withdraw (data kept) after P2–P4, 1.5% move school after P2–P4, 2% without a parent consent date
            'withdraw' => $roll <= 20 ? $this->rng->getInt(2, 4) : null,
            'leaves'   => $roll > 20 && $roll <= 35 ? $this->rng->getInt(2, 4) : null,
            'noParent' => $this->rng->getInt(1, 100) <= 2,
            'visited'  => [],
            'screen'   => null,
        ];
    }

    /** True when the student still takes part at meeting $no (not withdrawn or gone before it). */
    private function available(int $number, int $no): bool
    {
        $s = $this->roll[$number];

        return ($s['withdraw'] === null || $no <= $s['withdraw']) && ($s['leaves'] === null || $no <= $s['leaves']);
    }

    /**
     * Research schools of the run: `Sekolah Simulasi Kedu N` with the next
     * free S codes, as the admin enters them in the panel.
     *
     * @return list<array<string, mixed>>
     */
    private function researchSchools(int $count, \DateTimeImmutable $day): array
    {
        $used = array_column($this->db->table('schools')->select('research_code')->like('research_code', 'S', 'after')->get()->getResultArray(), 'research_code');
        $next = 1;
        $out  = [];

        while (count($out) < $count) {
            $code = sprintf('S%02d', $next++);

            if (in_array($code, $used, true)) {
                continue;
            }

            $name = sprintf('%s %d', self::SCHOOL_NAME, (int) substr($code, 1));
            $id   = $this->clock->staffAct($day->modify('-7 days')->setTime(13, 0), [], fn () => model(SchoolModel::class)->insert([
                'name'          => $name,
                'research_code' => $code,
                'level'         => 'SD',
                'country_code'  => 'ID',
                'province_code' => '33',
                'district_code' => self::DISTRICTS[count($out) % count(self::DISTRICTS)],
                'is_active'     => 1,
                'is_verified'   => 1,
            ], true));

            $this->db->table('schools')->where('id', (int) $id)->update(['created_at' => $day->modify('-7 days')->format('Y-m-d 13:00:00'), 'updated_at' => $day->modify('-7 days')->format('Y-m-d 13:00:00')]);
            $out[] = model(SchoolModel::class)->find((int) $id);
        }

        return $out;
    }

    private function researcherId(): ?int
    {
        foreach (['peneliti', 'ketua', 'admin'] as $role) {
            $id = $this->db->table('staff_users')->select('id')->where('role', $role)->where('is_active', 1)->orderBy('id', 'ASC')->get()->getRow('id');

            if ($id !== null) {
                return (int) $id;
            }
        }

        return null;
    }

    // ------------------------------------------------------------ meetings

    /**
     * One field session of one class group: open, students in, the
     * meeting's work, close. $main is the main session for a make-up.
     *
     * @param array<string, mixed>      $school
     * @param list<int>                 $numbers students expected (main: the class group; make-up: its members)
     * @param array<string, mixed>|null $main
     *
     * @return array<string, mixed> the field session
     */
    private function meeting(array $school, string $rombel, int $no, \DateTimeImmutable $opens, array $numbers, ?array $main): array
    {
        $closes  = $opens->modify('+' . self::MEETING_MINUTES[$no] . ' minutes');
        $window  = $this->realWindow();
        $members = $main === null ? [] : array_values(array_filter(array_map(fn (int $n): ?int => $this->roll[$n]['pid'], $numbers)));
        $fields  = service('fieldSessions');

        $created = $this->clock->staffAct($opens->modify('-15 minutes'), [], fn (): array => $main === null
            ? $fields->create(['school_id' => (int) $school['id'], 'rombel' => $rombel, 'meeting_no' => $no] + $window, $this->staffId)
            : $fields->createMakeup((int) $main['id'], $window, $members, $this->staffId));

        $fieldSession = $created['field_session'];
        $token        = $created['token'];
        $fields->forget();

        $this->sessionTimes[(int) $fieldSession['id']] = [
            'date' => $opens->format('Y-m-d'), 'opens' => $opens, 'closes' => $closes,
            'created' => $opens->modify('-15 minutes'), 'test' => null, 'closed' => null,
        ];
        $this->fieldStats[$main === null ? 'sesi_lapangan' : 'sesi_susulan']++;

        // Who comes: everyone still taking part, except those absent from the main meeting
        $present = array_values(array_filter($numbers, fn (int $n): bool => $this->available($n, $no) && ($main !== null || ! $this->roll[$n]['absent'][$no])));
        $this->fieldStats['tidak_hadir'] += count($numbers) - count($present);

        $entry = $fields->planEntry($fieldSession);

        // Status changes typed from L7 before the meeting (withdrawn or moved after the previous one)
        if ($main === null && $no > 1) {
            $this->statusChanges($fieldSession, $numbers, $no, $opens->modify('-5 minutes'));
        }

        foreach ($present as $number) {
            $this->enter($number, $fieldSession, $token, $opens->modify('+' . $this->rng->getInt(2, 12) . ' minutes'), $entry['register']);
        }

        $deadline = $closes->modify('-8 minutes');
        $testAt   = null;

        if ($entry['register']) {
            // P1: L2 seats, the L7 consent register, Mulai tes awal, then the module and the opening story
            $testAt = $opens->modify('+' . $this->rng->getInt(22, 28) . ' minutes');
            $this->seats($fieldSession, $present, $testAt->modify('-6 minutes'));
            $this->consents($fieldSession, $present, $testAt->modify('-4 minutes'));
            $this->startTest($fieldSession, $testAt);

            foreach ($present as $number) {
                $this->turn($number, $testAt->modify('+' . $this->rng->getInt(0, 90) . ' seconds'), function () use ($number): void {
                    $this->module($number);
                    $this->intro();
                });
            }
        } elseif ($entry['test'] === 'post') {
            // P5: leftovers first, then Mulai tes akhir; the module, the ending, Balai Refleksi
            $testAt = $opens->modify('+' . $this->rng->getInt(55, 70) . ' minutes');

            foreach ($present as $number) {
                $this->turn($number, null, fn () => $this->play($number, $entry['regions'], $testAt->modify('-3 minutes')));
            }

            $this->startTest($fieldSession, $testAt);

            foreach ($present as $number) {
                $this->turn($number, $testAt->modify('+' . $this->rng->getInt(0, 90) . ' seconds'), function () use ($number): void {
                    $this->module($number);
                    $this->closing($number);
                });
            }
        } else {
            foreach ($present as $number) {
                $this->turn($number, null, fn () => $this->play($number, $entry['regions'], $deadline));
            }
        }

        // Some press Keluar before the end; the rest are paused when the session closes
        foreach ($present as $number) {
            if ($this->rng->getInt(1, 100) <= 35) {
                $this->turn($number, null, function (): void {
                    $this->clock->advance($this->rng->getInt(5_000, 40_000));
                    $this->clock->act(fn () => $this->logout());
                });
            }
        }

        $closedAt = $closes->modify('-' . $this->rng->getInt(0, 6) . ' minutes');
        $entered  = $fields->enteredIds((int) $fieldSession['id']);
        $this->clock->staffAct($closedAt, $entered, fn () => $fields->close((int) $fieldSession['id'], $this->staffId));
        $this->sessionTimes[(int) $fieldSession['id']]['closed'] = $closedAt;

        if ($testAt !== null) {
            $this->sessionTimes[(int) $fieldSession['id']]['test'] = $testAt;
        }

        $fields->forget();

        return model(FieldSessionModel::class)->find((int) $fieldSession['id']);
    }

    /**
     * Date and hours that keep a field session open on the real clock while
     * the run plays it (moved to the simulated hours later). Waits for the
     * next day when fewer than 30 minutes are left before midnight.
     *
     * @return array{session_date: string, opens: string, closes: string}
     */
    private function realWindow(): array
    {
        if ((int) date('G') === 23 && (int) date('i') >= 30) {
            sleep(max(1, 86_400 - (time() - strtotime('today')) + 5));
        }

        $opens  = max(strtotime('today'), time() - 120);
        $closes = min(strtotime('today 23:59'), $opens + 4 * 3600 - 60);

        return ['session_date' => date('Y-m-d'), 'opens' => date('H:i', $opens), 'closes' => date('H:i', $closes)];
    }

    /**
     * A student comes in: registration with the token at P1 (or its
     * make-up), else login and the token; then the sound check.
     *
     * @param array<string, mixed> $fieldSession
     */
    private function enter(int $number, array $fieldSession, string $token, \DateTimeImmutable $at, bool $register): void
    {
        $student = &$this->roll[$number];

        if ($student['pid'] === null) {
            $this->clock->startAt($at);
            $this->session    = null;
            $this->lastScreen = null;
            $this->locale     = $student['locale'];
            $this->device     = $student['device'];

            // Absent from P1 and its make-up: never registered, so never in later meetings either
            if (! $register) {
                return;
            }

            if (service('fieldSessions')->checkToken($token, null, true)['status'] !== 'ok') {
                throw new \RuntimeException('token pendaftaran ditolak untuk ' . $student['username']);
            }

            $student['pid'] = $this->registerInField($number, $fieldSession);
            $this->soundCheck();
            $this->save($number);

            return;
        }

        $this->turn($number, $at, function () use ($number, $token): void {
            $student = $this->roll[$number];
            $login   = $this->clock->act(fn () => service('sessionService')->login($student['username'], self::PASSWORD, $this->locale, $this->deviceProfile()));

            if ($login['status'] !== 'token') {
                throw new \RuntimeException('masuk ' . $student['username'] . ': ' . $login['status']);
            }

            $participant = model(ParticipantModel::class)->find((int) $student['pid']);
            $check       = service('fieldSessions')->checkToken($token, $participant);

            if ($check['status'] !== 'ok') {
                throw new \RuntimeException('token ' . $student['username'] . ': ' . $check['status']);
            }

            $this->clock->advance($this->rng->getInt(10_000, 40_000));
            $session = $this->clock->act(fn () => service('sessionService')->enterFieldSession($participant->id, $check['field_session'], $this->locale, $this->deviceProfile()));
            $this->useSession($session);
            $this->soundCheck();
            $this->screen('gate', '/gerbang');
            $this->screen('map', '/peta');
        });
    }

    /**
     * Step 0 to 3 of the sign-up with the token's field session, as
     * RegisterController::registerWith() sends it.
     *
     * @param array<string, mixed> $fieldSession
     */
    private function registerInField(int $number, array $fieldSession): int
    {
        $weak  = $this->rng->getInt(0, 2);
        $first = (new \App\Libraries\PasswordPolicy())->check($weak > 0 ? 'kedu123' : self::PASSWORD, $this->username($number));

        $this->clock->advance($this->rng->getInt(60_000, 180_000));

        $result = $this->clock->act(fn () => service('sessionService')->registerAndStart([
            'username'      => $this->username($number),
            'password'      => self::PASSWORD,
            'display_name'  => self::FIRST_NAMES[($number - 1) % count(self::FIRST_NAMES)] . ' ' . sprintf('Simulasi %03d', $number),
            'age'           => $this->pick(['10' => 55, '11' => 38, '12' => 7]),
            'gender'        => $this->pick(['laki-laki' => 50, 'perempuan' => 50]),
            'class_level'   => config('Gelita')->registrationClass,
            'field_session' => $fieldSession,
            'school_npsn'   => null,
            'school_name'   => null,
            'country_code'  => 'ID',
            'country_name'  => 'Indonesia',
            'province_code' => null, 'province_name' => null,
            'district_code' => null, 'district_name' => null,
            'locale'        => $this->locale,
            'assent'        => true,
            'registration_metrics' => ['first_submit_criteria' => $first['met'], 'weak_submit_count' => $weak],
            'device'        => $this->deviceProfile(),
        ]), static fn (array $r): int => (int) $r['participant']->id);

        $this->useSession($result['session']);

        return (int) $result['participant']->id;
    }

    /** The research-mode sound check (33 §7.6): heard at once, or after one more play. */
    private function soundCheck(): void
    {
        $this->screen('sound_check', '/cek-suara');
        $first = $this->rng->getInt(1, 100) <= 6 ? 'tidak' : 'ya';

        foreach ($first === 'tidak' ? ['tidak', 'ya'] : ['ya'] as $answer) {
            $this->clock->advance($this->rng->getInt(4_000, 15_000));
            $this->clock->act(fn () => service('eventService')->record($this->session, 'sound_check_answered', [
                'heard' => $answer === 'ya', 'plays' => $answer === 'ya' && $first === 'tidak' ? 2 : 1,
            ]));
        }
    }

    // ------------------------------------------------------------ staff steps

    /** L2: seat rows and numbers of the students who came (rows of six), typed by the researcher. */
    private function seats(array $fieldSession, array $present, \DateTimeImmutable $at): void
    {
        $taken = (int) $this->db->table('participant_seats s')->join('participants p', 'p.id = s.participant_id')
            ->where('p.school_id', (int) $fieldSession['school_id'])->where('p.rombel', (string) $fieldSession['rombel'])->countAllResults();
        $input = [];

        foreach ($present as $i => $number) {
            $place = $taken + $i;
            $input[(int) $this->roll[$number]['pid']] = ['row' => (string) (intdiv($place, 6) + 1), 'seat' => (string) ($place % 6 + 1)];
        }

        $ids = array_keys($input);
        $this->clock->staffAct($at, $ids, fn () => (new SeatService())->save($fieldSession, $input, $this->staffId));
    }

    /** L7 typed: parent consent date, recording and risk-item consents, status active. */
    private function consents(array $fieldSession, array $present, \DateTimeImmutable $at): void
    {
        $input = [];

        foreach ($present as $number) {
            $student = $this->roll[$number];

            if ($student['noParent']) {
                $this->fieldStats['tanpa_izin_ortu']++;

                continue;
            }

            $input[(int) $student['pid']] = [
                'parent_consent_on'  => $at->modify('-' . $this->rng->getInt(3, 10) . ' days')->format('Y-m-d'),
                'recording_consent'  => $this->rng->getInt(1, 100) <= 75 ? '1' : '0',
                'risk_items_consent' => $this->rng->getInt(1, 100) <= 85 ? '1' : '0',
                'status'             => 'active',
            ];
        }

        $this->clock->staffAct($at, array_keys($input), fn () => service('consentService')->save($fieldSession, $input, $this->staffId));
    }

    /** Withdrawals and moves typed from L7 before meeting $no. */
    private function statusChanges(array $fieldSession, array $numbers, int $no, \DateTimeImmutable $at): void
    {
        $input = [];

        foreach ($numbers as $number) {
            $s = $this->roll[$number];

            if ($s['pid'] === null || $s['noParent']) {
                continue;
            }

            $status = match (true) {
                $s['withdraw'] === $no - 1 => 'withdrawn_keep',
                $s['leaves'] === $no - 1   => 'left',
                default                    => null,
            };

            if ($status !== null) {
                $input[(int) $s['pid']] = ['status' => $status, 'status_on' => $at->format('Y-m-d')];
                $this->fieldStats[$status === 'left' ? 'pindah' : 'mundur']++;
            }
        }

        if ($input !== []) {
            $result = $this->clock->staffAct($at, array_keys($input), fn () => service('consentService')->save($fieldSession, $input, $this->staffId));

            if ($result['errors'] !== []) {
                throw new \RuntimeException('status izin: ' . implode('; ', $result['errors']));
            }
        }
    }

    private function startTest(array $fieldSession, \DateTimeImmutable $at): void
    {
        $fresh = model(FieldSessionModel::class)->find((int) $fieldSession['id']);
        $this->clock->staffAct($at, [], fn () => (new FieldTestService())->start($fresh, $this->staffId));
        service('fieldSessions')->forget();
        service('instrumentGate')->forget();
    }

    /**
     * Once a school is done: its field sessions, tokens and members move to
     * their simulated dates and hours.
     */
    private function retimeSchoolSessions(int $afterId): void
    {
        foreach ($this->sessionTimes as $id => $t) {
            if ($id <= $afterId) {
                continue;
            }

            $fmt = static fn (?\DateTimeImmutable $d): ?string => $d?->format('Y-m-d H:i:s');

            $this->db->table('field_sessions')->where('id', $id)->update(array_filter([
                'session_date'    => $t['date'],
                'opens_at'        => $fmt($t['opens']),
                'closes_at'       => $fmt($t['closes']),
                'closed_at'       => $fmt($t['closed']),
                'test_started_at' => $fmt($t['test']),
                'created_at'      => $fmt($t['created']),
                'updated_at'      => $fmt($t['closed'] ?? $t['created']),
            ], static fn ($v): bool => $v !== null));

            $this->db->table('field_session_tokens')->where('field_session_id', $id)->update(['created_at' => $fmt($t['created'])]);

            if ($this->db->fieldExists('created_at', 'field_session_members')) {
                $this->db->table('field_session_members')->where('field_session_id', $id)->update(['created_at' => $fmt($t['created'])]);
            }
        }
    }

    // ------------------------------------------------------------ students

    /**
     * One step of a student's own timeline: their state is swapped in, the
     * clock resumes at their time (or $at when later), and swapped out after.
     */
    private function turn(int $number, ?\DateTimeImmutable $at, \Closure $step): void
    {
        $s = $this->roll[$number];

        if ($s['pid'] === null) {
            return;
        }

        $now = $s['now'] ?? $at;
        $now = $at !== null && $at > $now ? $at : $now;

        $this->session    = $s['session'];
        $this->lastScreen = $s['screen'];
        $this->locale     = $s['locale'];
        $this->device     = $s['device'];
        $this->clock->resume($now, (int) $s['pid'], $s['session']?->id);

        $step();
        $this->save($number);
    }

    private function save(int $number): void
    {
        $this->roll[$number]['session'] = $this->session;
        $this->roll[$number]['screen']  = $this->lastScreen;
        $this->roll[$number]['now']     = $this->clock->now();
        $this->roll[$number]['locale']  = $this->locale;
    }

    /**
     * The meeting's regions until $deadline: the challenges not completed
     * yet, in order; an open attempt from an earlier meeting goes on in a
     * new segment. Near the deadline a challenge may be left open.
     *
     * @param list<string> $regions
     */
    private function play(int $number, array $regions, \DateTimeImmutable $deadline): void
    {
        $pid  = (int) $this->roll[$number]['pid'];
        $repo = service('contentRepository');

        foreach ($regions as $code) {
            $level = $repo->level($code);

            if (! $level instanceof Level) {
                continue;
            }

            $pending = array_values(array_filter($repo->nodesForLevel($level->id), fn ($node): bool => ! $this->nodeDone($pid, (int) $node->id)));

            if ($pending === []) {
                continue;
            }

            $this->openRegion($level, isset($this->roll[$number]['visited'][$code]));
            $this->roll[$number]['visited'][$code] = true;

            foreach ($pending as $node) {
                $left = $deadline->getTimestamp() - $this->clock->now()->getTimestamp();

                if ($left < 4 * 60) {
                    return;
                }

                $leaveOpen = $left < 12 * 60 && $this->rng->getInt(1, 100) <= 50;
                $this->challenge($level, $node, $this->roll[$number]['skill'], null, $leaveOpen, false);

                if ($leaveOpen) {
                    return;
                }
            }

            if ($this->regionComplete($pid, $level)) {
                $this->regionDone($level);

                // Kenali opens after the region in research mode (33)
                if ($this->rng->getInt(1, 100) <= 50) {
                    $this->clientEvents([['overlay_opened', ['level_id' => $level->id], ['overlay' => 'kenal', 'level' => $level->code]]]);
                    $this->clock->advance($this->rng->getInt(8_000, 25_000));
                }

                $this->library($level);
            }
        }
    }

    private function nodeDone(int $pid, int $nodeId): bool
    {
        return $this->db->table('challenge_attempts a')->join('game_sessions s', 's.id = a.session_id')
            ->where('s.participant_id', $pid)->where('a.challenge_node_id', $nodeId)->where('a.status', 'completed')
            ->countAllResults() > 0;
    }

    private function regionComplete(int $pid, Level $level): bool
    {
        foreach (service('contentRepository')->nodesForLevel($level->id) as $node) {
            if (! $this->nodeDone($pid, (int) $node->id)) {
                return false;
            }
        }

        return true;
    }

    /** P5 after the module: the ending (all regions done), the attitude scale, feedback. */
    private function closing(int $number): void
    {
        $pid = (int) $this->roll[$number]['pid'];
        $all = true;

        foreach (service('contentRepository')->levels() as $level) {
            $all = $all && $this->regionComplete($pid, $level);
        }

        if ($all) {
            $this->ending();
        }

        $participant = model(ParticipantModel::class)->find($pid);
        service('instrumentGate')->forget();
        $state = service('instrumentGate')->reflectionState($participant, $this->session);

        if (($state['state'] ?? null) === 'scale' && isset($state['context'])) {
            $this->screen('reflection', '/refleksi');
            $this->attitude($state['context']);
        }

        if ($all) {
            $this->feedback(false);
        }
    }

    // ------------------------------------------------------------ test module

    /** The module steps open for the student now (34 §5.1): test, questionnaire, risk items. */
    private function module(int $number): void
    {
        $gate        = service('instrumentGate');
        $participant = model(ParticipantModel::class)->find((int) $this->roll[$number]['pid']);

        for ($guard = 0; $guard < 6; $guard++) {
            $gate->forget();
            service('instruments')->forget();
            $context = $gate->context($participant, $this->session);

            match ($context['step'] ?? 'closed') {
                'test'          => $this->test($number, $context),
                'questionnaire' => $this->questionnaire($context),
                'risk'          => $this->risk($context),
                default         => $guard = 99,
            };
        }

        $gate->forget();
    }

    /** One test sitting: examples, then Bagian 1–3 item by item, each part sent from its review page. */
    private function test(int $number, array $context): void
    {
        $instruments = service('instruments');
        $sitting     = $this->clock->act(fn () => $instruments->startTest($context));
        $content     = service('instrumentRepository')->content((int) $sitting['version_id']);
        $meta        = $content['version']['meta'];
        $student     = $this->roll[$number];
        $p           = $student['skill'] + ($context['occasion'] === 'post' ? 0.06 : -0.12);

        foreach (InstrumentService::TEST_PARTS as $index => $part) {
            if ($index === 1) {
                // Stretch between Bagian 1 and Bagian 2–3 (§7.3 step 6)
                $this->clock->advance($this->rng->getInt(60_000, 150_000));
            }

            $examples = [];

            foreach ((array) ($meta['parts'][$part]['examples'] ?? []) as $code) {
                $item = $content['items'][$code] ?? null;

                if ($item !== null) {
                    $examples += $this->answer($item, 0.9, $meta);
                }
            }

            $this->clock->advance($this->rng->getInt(30_000, 90_000));
            $row = $this->clock->act(fn () => $instruments->startPart($instruments->sittingById((int) $sitting['id']), $part, $examples));

            if ($row === null) {
                continue;
            }

            foreach ($instruments->items($sitting, $part) as $item) {
                $this->clock->act(fn () => $instruments->markShown($sitting, $item['units'], $item['id']));
                $rapid = $student['rapid'] && $this->rng->getInt(1, 100) <= 40;
                $this->clock->advance($rapid ? $this->rng->getInt(900, 2_800) : $this->itemTime($item));
                $values = $this->rng->getInt(1, 100) <= 3 ? [] : $this->answer($item, $rapid ? 0.25 : $p, $meta);
                $this->clock->act(fn () => $instruments->save($instruments->sittingById((int) $sitting['id']), $row, $values));
            }

            $this->clock->advance($this->rng->getInt(8_000, 30_000));
            $this->clock->act(fn () => $instruments->submitPart($instruments->sittingById((int) $sitting['id']), $part));
        }

        $this->fieldStats[$context['occasion'] === 'post' ? 'tes_akhir' : 'tes_awal']++;
    }

    /** The digital-access questionnaire after the pre-test: every section, then Selesai. */
    private function questionnaire(array $context): void
    {
        $instruments = service('instruments');
        $sitting     = $this->clock->act(fn () => $instruments->openQuestionnaire($context));
        $meta        = service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];

        foreach (InstrumentImporter::QUESTIONNAIRE_SECTIONS as $section) {
            $part = $instruments->part((int) $sitting['id'], $section);

            if ($part === null) {
                continue;
            }

            foreach ($instruments->items($sitting, $section) as $item) {
                $this->clock->act(fn () => $instruments->markShown($sitting, $item['units'], $item['id']));
                $this->clock->advance($this->rng->getInt(4_000, 18_000));
                $this->clock->act(fn () => $instruments->save($sitting, $part, $this->answer($item, 0.5, $meta)));
            }
        }

        $this->clock->advance($this->rng->getInt(5_000, 20_000));
        $this->clock->act(fn () => $instruments->submitSitting($instruments->sittingById((int) $sitting['id'])));
        $this->fieldStats['angket']++;
    }

    /** Risk items after the post-test (30 §5.4, 34 §8.1): T11 asked again, T12 when Ya. */
    private function risk(array $context): void
    {
        $instruments = service('instruments');
        $sitting     = $this->clock->act(fn () => $instruments->openRisk($context));

        if ((int) $sitting['risk_offered'] === 1) {
            $yes = $this->rng->getInt(1, 100) <= 88;
            $this->clock->advance($this->rng->getInt(5_000, 15_000));
            $this->clock->act(fn () => $instruments->reconsent($sitting, $yes));
            $sitting = $instruments->sittingById((int) $sitting['id']);
            $part    = $instruments->part((int) $sitting['id'], 'risk');

            if ($yes && $part !== null) {
                $meta = service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];

                foreach ($instruments->items($sitting, 'risk') as $item) {
                    $this->clock->act(fn () => $instruments->markShown($sitting, $item['units'], $item['id']));
                    $this->clock->advance($this->rng->getInt(6_000, 20_000));
                    $this->clock->act(fn () => $instruments->save($sitting, $part, $this->answer($item, 0.5, $meta)));
                }

                $this->fieldStats['butir_risiko']++;
            }
        }

        $this->clock->advance($this->rng->getInt(3_000, 10_000));
        $this->clock->act(fn () => $instruments->submitSitting($instruments->sittingById((int) $sitting['id'])));
    }

    /** The attitude scale in Balai Refleksi (17, 34 §9). */
    private function attitude(array $context): void
    {
        $instruments = service('instruments');
        $sitting     = $this->clock->act(fn () => $instruments->openAttitude($context));
        $part        = $instruments->part((int) $sitting['id'], 'sikap');
        $meta        = service('instrumentRepository')->content((int) $sitting['version_id'])['version']['meta'];

        if ($part === null) {
            return;
        }

        foreach ($instruments->items($sitting, 'sikap') as $item) {
            $this->clock->act(fn () => $instruments->markShown($sitting, $item['units'], $item['id']));
            $this->clock->advance($this->rng->getInt(4_000, 14_000));
            $this->clock->act(fn () => $instruments->save($sitting, $part, $this->answer($item, 0.7, $meta)));
        }

        $this->clock->advance($this->rng->getInt(4_000, 12_000));
        $this->clock->act(fn () => $instruments->submitSitting($instruments->sittingById((int) $sitting['id'])));
        $this->fieldStats['skala_sikap']++;
    }

    /** Reading and answer time of one test item by its type. */
    private function itemTime(array $item): int
    {
        return match ($item['type']) {
            'match', 'pgk' => $this->rng->getInt(35_000, 95_000),
            default        => $this->rng->getInt(18_000, 70_000),
        };
    }

    /**
     * Raw responses of one item in the shape the module pages post
     * (InstrumentPages::postedValues): right by the key with probability $p
     * on scored items, a plausible choice elsewhere.
     *
     * @param array<string, mixed> $item
     * @param array<string, mixed> $meta
     *
     * @return array<string, ?string> unit → raw response
     */
    private function answer(array $item, float $p, array $meta): array
    {
        $p     = max(0.1, min(0.95, $p));
        $right = $this->rng->getFloat(0, 1) < $p;
        $show  = (array) $item['show'];
        $key   = (array) ($item['key'] ?? []);
        $unit  = (string) $item['units'][0];

        switch ($item['type']) {
            case 'pg':
            case 'd2':
            case 'd3':
                $options = array_map('strval', array_keys((array) ($show['options'] ?? [])));
                $best    = (string) ($key['answer'] ?? '');

                if ($item['type'] === 'd2' && ! empty($key['scores'])) {
                    $scores = array_map('intval', (array) $key['scores']);
                    arsort($scores);
                    $best = (string) array_key_first($scores);
                }

                $others = array_values(array_diff($options, [$best]));

                return [$unit => $right || $others === [] ? ($best !== '' ? $best : ($options[0] ?? null)) : $others[$this->rng->getInt(0, count($others) - 1)]];

            case 'pgk':
                $letters = array_map('strval', array_keys((array) ($meta['scales'][$show['scale'] ?? 'bs'] ?? ['B' => 1, 'S' => 1])));
                $answer  = (string) ($key['answer'] ?? str_repeat($letters[0], count((array) ($show['statements'] ?? [1, 2, 3]))));

                if (! $right) {
                    $i          = $this->rng->getInt(0, strlen($answer) - 1);
                    $others     = array_values(array_diff($letters, [$answer[$i]]));
                    $answer[$i] = $others[0] ?? $answer[$i];
                }

                return [$unit => $answer];

            case 'match':
                $pairs = InstrumentScorer::pairs((string) ($key['answer'] ?? ''));

                if (! $right && count($pairs) > 1) {
                    $nos             = array_keys($pairs);
                    [$a, $b]         = [$nos[0], $nos[1]];
                    [$pairs[$a], $pairs[$b]] = [$pairs[$b], $pairs[$a]];
                }

                return [$unit => implode(' ', array_map(static fn ($no, $letter): string => $no . $letter, array_keys($pairs), $pairs))];

            case 'multi':
                $out = [];
                $any = false;

                foreach ($item['units'] as $u) {
                    $tick    = $this->rng->getInt(1, 100) <= 35;
                    $any     = $any || $tick;
                    $out[$u] = $tick ? '1' : '0';
                }

                if (! $any) {
                    $out[(string) $item['units'][0]] = '1';
                }

                return $out;

            case 'single':
            case 'likert4':
                $out = [];

                foreach ($item['units'] as $u) {
                    $options = array_map('strval', array_keys($item['type'] === 'likert4' ? (array) ($meta['choices'] ?? []) : (array) ($show['options'] ?? [])));

                    if ($options === []) {
                        continue;
                    }

                    // Attitude and access answers lean to the agreeing end, as children's answers do
                    $index   = $item['type'] === 'likert4' && $right ? count($options) - 1 - $this->rng->getInt(0, 1) : $this->rng->getInt(0, count($options) - 1);
                    $out[$u] = $options[max(0, $index)];
                }

                return $out;
        }

        return [];
    }
}
