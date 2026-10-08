<?php

namespace App\Libraries\Simulation;

use CodeIgniter\Database\BaseConnection;

/**
 * Jam simulasi `gelita:simulasi`.
 *
 * Simulator memanggil layanan sungguhan (SessionService, ChallengeService,
 * EventService), yang selalu menulis waktu server saat ini. Supaya riwayat
 * siswa simulasi tersebar wajar (beberapa hari sekolah, jeda antarlangkah),
 * setiap langkah dijalankan lewat act(): sesudah layanan selesai, kolom waktu
 * baris milik siswa itu yang baru ditulis pada langkah tersebut (nilainya ≥
 * penanda awal langkah) digeser ke jam simulasi. Baris langkah sebelumnya
 * sudah bergeser ke masa lalu, jadi tidak ikut tersentuh.
 *
 * Isi baris (status, jawaban, hitungan, event) tidak diubah sama sekali.
 * Yang dihitung ulang hanya kolom yang diturunkan dari jam dinding:
 * `challenge_attempts.duration_ms` (selisih completed_at − started_at) dan
 * `game_sessions.duration_ms` (jumlah jeda antarlangkah yang tidak melewati
 * batas menganggur, seperti SessionService::heartbeat()).
 */
final class SimClock
{
    /** Jarak antar-event server dalam satu langkah, agar urutan occurred_at sama dengan urutan tulis. */
    private const EVENT_SPACING_US = 20_000;

    private \DateTimeImmutable $now;
    private int $participantId = 0;
    private ?int $sessionId    = null;

    /** @var array<int, array{last: ?\DateTimeImmutable, active_ms: int}> */
    private array $sessions = [];

    /** @var list<int> */
    private array $participants = [];

    public function __construct(private readonly BaseConnection $db, \DateTimeImmutable $start)
    {
        $this->now = $start;
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    /** Waktu klien ISO-8601 UTC bermilidetik (seperti `new Date().toISOString()`), $offsetMs dari jam simulasi. */
    public function iso(int $offsetMs = 0): string
    {
        return $this->shift($this->now, $offsetMs)
            ->setTimezone(new \DateTimeZone('UTC'))
            ->format('Y-m-d\TH:i:s.v\Z');
    }

    public function advance(int $ms): void
    {
        $this->now = $this->shift($this->now, max(0, $ms));
    }

    /** Maju ke $time (tidak pernah mundur selama satu siswa). */
    public function jumpTo(\DateTimeImmutable $time): void
    {
        if ($time > $this->now) {
            $this->now = $time;
        }
    }

    /** Siswa baru boleh mulai di hari yang lebih awal dari siswa sebelumnya. */
    public function startAt(\DateTimeImmutable $time): void
    {
        $this->unbind();
        $this->now = $time;
    }

    /** Milidetik sejak $since menurut jam simulasi. */
    public function msSince(\DateTimeImmutable $since): int
    {
        return $this->diffMs($since->format('Y-m-d H:i:s.u'), $this->now->format('Y-m-d H:i:s.u'));
    }

    /** Siswa yang barisnya digeser oleh act() berikutnya. */
    public function bindParticipant(int $participantId): void
    {
        $this->participantId = $participantId;

        if (! in_array($participantId, $this->participants, true)) {
            $this->participants[] = $participantId;
        }
    }

    /** Siswa berikutnya belum ada: langkah tidak menggeser baris siapa pun. */
    public function unbind(): void
    {
        $this->participantId = 0;
        $this->sessionId     = null;
    }

    /** Sesi permainan yang sedang dimainkan (untuk lama bermain sesi). */
    public function bindSession(?int $sessionId): void
    {
        $this->sessionId = $sessionId;

        if ($sessionId !== null && ! isset($this->sessions[$sessionId])) {
            $this->sessions[$sessionId] = ['last' => null, 'active_ms' => 0];
        }
    }

    /**
     * Satu langkah siswa pada jam simulasi saat ini.
     *
     * @template T
     *
     * @param callable(): T             $step
     * @param (callable(T): int)|null $owner siswa pemilik baris bila baru dibuat langkah ini (pendaftaran)
     *
     * @return T
     */
    public function act(callable $step, ?callable $owner = null): mixed
    {
        // Penanda: detik sebelum langkah dimulai, dalam zona aplikasi maupun
        // UTC (nilai bawaan CURRENT_TIMESTAMP SQLite ditulis dalam UTC).
        $mark = min(date('Y-m-d H:i:s', time() - 1), gmdate('Y-m-d H:i:s', time() - 1));

        if ($this->now->format('Y-m-d H:i:s') >= $mark) {
            throw new \RuntimeException('Jam simulasi harus di masa lalu: ' . $this->now->format('Y-m-d H:i:s'));
        }

        try {
            $result = $step();

            if ($owner !== null) {
                $this->bindParticipant($owner($result));
            }

            return $result;
        } finally {
            if ($this->participantId > 0) {
                $this->retime($mark);
            }

            $this->tick();
        }
    }

    /**
     * Sesudah semua langkah: lama pengerjaan dan lama bermain sesi dihitung
     * dari jam simulasi.
     */
    public function finish(): void
    {
        foreach ($this->sessions as $sessionId => $row) {
            $this->db->table('game_sessions')->where('id', $sessionId)->update(['duration_ms' => $row['active_ms']]);
        }

        if ($this->participants === []) {
            return;
        }

        $sessionIds = array_map('intval', array_column(
            $this->db->table('game_sessions')->select('id')->whereIn('participant_id', $this->participants)->get()->getResultArray(),
            'id',
        ));

        if ($sessionIds === []) {
            return;
        }

        $attempts = $this->db->table('challenge_attempts')
            ->select('id, started_at, completed_at')
            ->whereIn('session_id', $sessionIds)
            ->where('completed_at IS NOT NULL')
            ->get()->getResultArray();

        foreach ($attempts as $row) {
            $ms = $this->diffMs((string) $row['started_at'], (string) $row['completed_at']);
            $this->db->table('challenge_attempts')->where('id', $row['id'])->update(['duration_ms' => $ms]);
        }
    }

    // ------------------------------------------------------------- internal

    /** Lama bermain: jeda sejak langkah sebelumnya di sesi ini, bila belum melewati batas menganggur. */
    private function tick(): void
    {
        if ($this->sessionId === null) {
            return;
        }

        $row  = &$this->sessions[$this->sessionId];
        $idle = config('Gelita')->sessionIdleMinutes * 60 * 1000;

        if ($row['last'] !== null) {
            $gap = $this->diffMs($row['last']->format('Y-m-d H:i:s.u'), $this->now->format('Y-m-d H:i:s.u'));

            if ($gap <= $idle) {
                $row['active_ms'] += $gap;
            }
        }

        $row['last'] = $this->now;
    }

    private function retime(string $mark): void
    {
        $pid        = $this->participantId;
        $sessionIds = $this->ids('game_sessions', 'participant_id', [$pid]);
        $attemptIds = $sessionIds === [] ? [] : $this->ids('challenge_attempts', 'session_id', $sessionIds);
        $time       = $this->now->format('Y-m-d H:i:s.u');
        $schoolId   = (int) ($this->db->table('participants')->select('school_id')->where('id', $pid)->get()->getRow('school_id') ?? 0);

        $specs = [
            // Entri sekolah "belum terverifikasi" yang dibuat pendaftaran siswa ini
            ['schools', 'id', $schoolId > 0 ? [$schoolId] : [], ['created_at', 'updated_at']],
            ['participants', 'id', [$pid], ['created_at', 'updated_at', 'last_login_at', 'password_changed_at', 'intro_seen_at', 'assented_at', 'locked_until']],
            ['game_sessions', 'participant_id', [$pid], ['started_at', 'last_active_at', 'ended_at', 'created_at']],
            ['device_logs', 'participant_id', [$pid], ['created_at']],
            ['participant_feedback', 'participant_id', [$pid], ['submitted_at']],
            ['session_progress', 'session_id', $sessionIds, ['updated_at']],
            ['challenge_attempts', 'session_id', $sessionIds, ['started_at', 'completed_at', 'created_at']],
            ['audio_usage_events', 'session_id', $sessionIds, ['occurred_at', 'server_received_at']],
            ['item_responses', 'challenge_attempt_id', $attemptIds, ['started_at', 'answered_at', 'created_at', 'updated_at']],
            ['item_tries', 'challenge_attempt_id', $attemptIds, ['occurred_at']],
        ];

        foreach ($specs as [$table, $scope, $ids, $columns]) {
            if ($ids !== []) {
                $this->shiftColumns($table, $scope, $ids, $columns, $mark, $time);
            }
        }

        // Reset sandi oleh guru (audit) milik siswa ini
        $this->shiftColumns('audit_logs', 'target_id', [(string) $pid], ['occurred_at'], $mark, $time, "target_type = 'participant'");

        $this->retimeEvents($pid, $mark);
    }

    /**
     * @param list<int|string> $ids
     * @param list<string>     $columns
     */
    private function shiftColumns(string $table, string $scope, array $ids, array $columns, string $mark, string $time, ?string $extra = null): void
    {
        $markSql = $this->db->escape($mark);
        $timeSql = $this->db->escape($time);
        $sets    = [];
        $any     = [];

        foreach ($columns as $column) {
            // Kolom ON UPDATE (updated_at) juga disetel eksplisit agar tidak diisi jam dinding lagi
            $sets[] = "{$column} = CASE WHEN {$column} >= {$markSql} THEN {$timeSql} ELSE {$column} END";
            $any[]  = "{$column} >= {$markSql}";
        }

        $in  = implode(', ', array_map(fn ($id): string => (string) $this->db->escape($id), $ids));
        $sql = 'UPDATE ' . $this->db->prefixTable($table) . ' SET ' . implode(', ', $sets)
            . " WHERE {$scope} IN ({$in}) AND (" . implode(' OR ', $any) . ')'
            . ($extra !== null ? " AND {$extra}" : '');

        $this->db->query($sql);
    }

    /**
     * Event langkah ini: event server berurutan 20 ms dari jam simulasi;
     * event klien memakai occurred_at kiriman simulator (sudah jam simulasi),
     * hanya waktu terimanya yang digeser.
     */
    private function retimeEvents(int $pid, string $mark): void
    {
        $rows = $this->db->table('game_event_logs')
            ->select('id, occurred_at')
            ->where('participant_id', $pid)
            ->where('server_received_at >=', $mark)
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        $offset = 0;

        foreach ($rows as $row) {
            $at       = $this->shiftUs($this->now, $offset);
            $received = $at->format('Y-m-d H:i:s.u');
            $update   = ['server_received_at' => $received];

            if ((string) $row['occurred_at'] >= $mark) {
                $update['occurred_at'] = $received;
            }

            $this->db->table('game_event_logs')->where('id', $row['id'])->update($update);
            $offset += self::EVENT_SPACING_US;
        }
    }

    /**
     * @param list<int> $values
     *
     * @return list<int>
     */
    private function ids(string $table, string $column, array $values): array
    {
        return array_map('intval', array_column(
            $this->db->table($table)->select('id')->whereIn($column, $values)->get()->getResultArray(),
            'id',
        ));
    }

    private function shift(\DateTimeImmutable $time, int $ms): \DateTimeImmutable
    {
        return $this->shiftUs($time, $ms * 1000);
    }

    private function shiftUs(\DateTimeImmutable $time, int $us): \DateTimeImmutable
    {
        $seconds = intdiv($us, 1_000_000);
        $rest    = $us % 1_000_000;

        return $time->modify(sprintf('%+d seconds %+d microseconds', $seconds, $rest));
    }

    private function diffMs(string $from, string $to): int
    {
        $a = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $this->withMicro($from));
        $b = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', $this->withMicro($to));

        if ($a === false || $b === false) {
            return 0;
        }

        $us = ((int) $b->format('U') - (int) $a->format('U')) * 1_000_000 + ((int) $b->format('u') - (int) $a->format('u'));

        return max(0, intdiv($us, 1000));
    }

    private function withMicro(string $value): string
    {
        return str_contains($value, '.') ? str_pad($value, 26, '0') : $value . '.000000';
    }
}
