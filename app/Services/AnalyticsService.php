<?php

namespace App\Services;

use App\Models\GameEventLogModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * Sisa layanan ringkasan panel (Dok 3 §8.4): KPI hitungan (`summary()`),
 * profil satu siswa (`participantProfile()`), dan linimasa event satu sesi
 * (`eventTimeline()`). Tabel data mentah dan ekspor ada di RawDataService;
 * skor, persentase, rata-rata, sebaran, dan chart tidak dihitung di sini.
 *
 * Otorisasi: bila $schoolScope bukan NULL, SETIAP query menambahkan
 * participants.school_id = $schoolScope. Batas ini diterapkan di service,
 * bukan hanya di filter route, supaya tidak dapat dilewati lewat jalur lain.
 */
class AnalyticsService
{
    /** Sesi dihitung "aktif sekarang" bila denyut terakhirnya dalam rentang ini (§8.3). */
    public const ACTIVE_MINUTES = 10;

    private ?int $schoolScope;

    private bool $anonymous;

    public function __construct(?int $schoolScope = null, bool $anonymous = false)
    {
        $this->schoolScope = $schoolScope;
        $this->anonymous   = $anonymous;
    }

    public function forStaff(?int $schoolScope, bool $anonymous = false): static
    {
        $this->schoolScope = $schoolScope;
        $this->anonymous   = $anonymous;

        return $this;
    }

    /**
     * KPI hitungan dalam cakupan pemanggil (§8.2: hanya hitungan).
     *
     * @param array<string, mixed> $filters filter penelitian baku (apply_research_filters)
     *
     * @return array{participant_count: int, active_sessions: int, completed_attempts_today: int,
     *               feedback_count: int, session_count: int, completed_sessions: int,
     *               completed_attempts: int, abandoned_attempts: int, item_tries: int, hint_count: int,
     *               audio_plays: int, audio_autoplays: int}
     */
    public function summary(array $filters = []): array
    {
        $filters = $this->scoped($filters);
        $db      = $this->db();

        $participants = $db->table('participants')->where('participants.deleted_at', null);
        $this->participantFilters($participants, $filters);

        $sessions = $this->sessionBuilder($filters)
            ->select('COUNT(*) AS total', false)
            ->select("SUM(CASE WHEN {$this->col('game_sessions.status')} = 'completed' THEN 1 ELSE 0 END) AS completed", false)
            ->get()
            ->getRowArray();

        $active = $this->sessionBuilder($filters)
            ->whereIn('game_sessions.status', ['active'])
            ->where('game_sessions.last_active_at >=', date('Y-m-d H:i:s', time() - self::ACTIVE_MINUTES * 60))
            ->countAllResults();

        $status   = $this->col('challenge_attempts.status');
        $attempts = $this->attemptBuilder($filters)
            ->select("SUM(CASE WHEN {$status} = 'completed' THEN 1 ELSE 0 END) AS completed", false)
            ->select("SUM(CASE WHEN {$status} = 'abandoned' THEN 1 ELSE 0 END) AS abandoned", false)
            ->select('SUM(' . $this->col('challenge_attempts.hint_count') . ') AS hints', false)
            ->get()
            ->getRowArray();

        $today = $this->attemptBuilder($filters)
            ->where('challenge_attempts.status', 'completed')
            ->where('challenge_attempts.completed_at >=', date('Y-m-d') . ' 00:00:00')
            ->countAllResults();

        $tries = $this->attemptBuilder($filters)
            ->join('item_tries', 'item_tries.challenge_attempt_id = challenge_attempts.id')
            ->countAllResults();

        $feedback = $this->sessionBuilder($filters)
            ->join('participant_feedback', 'participant_feedback.session_id = game_sessions.id')
            ->countAllResults();

        $action = $this->col('audio_usage_events.action');
        $audio  = $this->sessionBuilder($filters)
            ->join('audio_usage_events', 'audio_usage_events.session_id = game_sessions.id')
            ->select("SUM(CASE WHEN {$action} = 'play' THEN 1 ELSE 0 END) AS plays", false)
            ->select("SUM(CASE WHEN {$action} = 'autoplay' THEN 1 ELSE 0 END) AS autoplays", false)
            ->get()
            ->getRowArray();

        return [
            'participant_count'        => $participants->countAllResults(),
            'active_sessions'          => $active,
            'completed_attempts_today' => $today,
            'feedback_count'           => $feedback,
            'session_count'            => (int) ($sessions['total'] ?? 0),
            'completed_sessions'       => (int) ($sessions['completed'] ?? 0),
            'completed_attempts'       => (int) ($attempts['completed'] ?? 0),
            'abandoned_attempts'       => (int) ($attempts['abandoned'] ?? 0),
            'item_tries'               => $tries,
            'hint_count'               => (int) ($attempts['hints'] ?? 0),
            'audio_plays'              => (int) ($audio['plays'] ?? 0),
            'audio_autoplays'          => (int) ($audio['autoplays'] ?? 0),
        ];
    }

    /**
     * Profil satu siswa dalam tabel formal mentah (laporan PDF §10, detail
     * siswa): identitas sesuai hak, sesi, perangkat, progres per tantangan
     * aktif, kritik & saran, dan hitungan pengerjaan. Tanpa skor atau
     * kalimat penilaian.
     *
     * @return array{participant: array<string, mixed>, sessions: list<array<string, mixed>>,
     *               devices: list<array<string, mixed>>, progress: list<array<string, mixed>>,
     *               feedback: list<array<string, mixed>>, completed_attempts: int, first_pass_correct: int,
     *               final_correct: int, scorable_items: int, stars: int, answer_changes: int, hint_uses: int,
     *               total_duration_ms: int, audio: array{total_plays: int, total_autoplays: int}}
     */
    public function participantProfile(int $participantId, ?int $schoolScope = null): array
    {
        if ($schoolScope !== null) {
            $this->schoolScope = $schoolScope;
        }

        $participant = model(ParticipantModel::class)->find($participantId);

        if ($participant === null) {
            throw new \RuntimeException("Peserta {$participantId} tidak ditemukan.");
        }

        if ($this->schoolScope !== null && $participant->school_id !== $this->schoolScope) {
            throw new \RuntimeException('Peserta ini di luar cakupan akses Anda.');
        }

        $db = $this->db();

        $person = [
            'participant_code' => $participant->participant_code,
            'school_ref'       => RawDataService::schoolRef($participant->school_id),
            'age'              => $participant->age,
            'gender'           => $participant->gender,
            'class_level'      => $participant->class_level,
            'registered_at'    => $participant->created_at === null ? null : (string) $participant->created_at,
        ];

        if (! $this->anonymous) {
            $person += [
                'display_name' => $participant->display_name,
                'username'     => $participant->username,
                'school_name'  => $participant->school_name_snapshot,
            ];
        }

        $sessions = $db->table('game_sessions')
            ->select('game_sessions.id, game_sessions.session_code, game_sessions.locale, game_sessions.status')
            ->select('game_sessions.started_at, game_sessions.last_active_at, game_sessions.ended_at, game_sessions.duration_ms')
            ->select('game_sessions.device_type, game_sessions.os_name, game_sessions.browser_name, game_sessions.screen_size')
            ->where('game_sessions.participant_id', $participantId)
            ->orderBy('game_sessions.started_at', 'ASC')
            ->get()
            ->getResultArray();

        $devices = $db->table('device_logs')
            ->select('device_logs.created_at, device_logs.via, device_logs.device_type')
            ->select('device_logs.os_name, device_logs.os_version, device_logs.browser_name, device_logs.browser_version')
            ->select('device_logs.screen_w, device_logs.screen_h, device_logs.viewport_w, device_logs.viewport_h')
            ->where('device_logs.participant_id', $participantId)
            ->orderBy('device_logs.created_at', 'ASC')
            ->get()
            ->getResultArray();

        $attempts = $db->table('challenge_attempts')
            ->select('challenge_attempts.*')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->where('game_sessions.participant_id', $participantId)
            ->orderBy('challenge_attempts.started_at', 'ASC')
            ->orderBy('challenge_attempts.id', 'ASC')
            ->get()
            ->getResultArray();

        $feedback = $db->table('participant_feedback')
            ->select('rating, liked_most, hardest_part, new_learning, suggestion, submitted_at')
            ->where('participant_id', $participantId)
            ->orderBy('submitted_at', 'ASC')
            ->get()
            ->getResultArray();

        $action = $this->col('audio_usage_events.action');
        $audio  = $db->table('audio_usage_events')
            ->join('game_sessions', 'game_sessions.id = audio_usage_events.session_id')
            ->select("SUM(CASE WHEN {$action} = 'play' THEN 1 ELSE 0 END) AS plays", false)
            ->select("SUM(CASE WHEN {$action} = 'autoplay' THEN 1 ELSE 0 END) AS autoplays", false)
            ->where('game_sessions.participant_id', $participantId)
            ->get()
            ->getRowArray();

        $totals = ['attempts' => 0, 'changes' => 0, 'hints' => 0, 'duration' => 0, 'first_pass' => 0, 'final' => 0, 'scorable' => 0, 'stars' => 0];

        foreach ($attempts as $row) {
            if ($row['status'] !== 'completed') {
                continue;
            }

            $totals['attempts']++;
            $totals['changes'] += (int) $row['answer_change_count'];
            $totals['hints'] += (int) $row['hint_count'];
            $totals['duration'] += (int) $row['duration_ms'];
            $totals['first_pass'] += (int) $row['first_pass_correct'];
            $totals['final'] += (int) $row['final_correct'];
            $totals['scorable'] += (int) $row['scorable_items'];
            $totals['stars'] += (int) $row['stars'];
        }

        return [
            'participant'        => $person,
            'sessions'           => $sessions,
            'devices'            => $devices,
            'progress'           => $this->progressRows($attempts),
            'feedback'           => $feedback,
            'completed_attempts' => $totals['attempts'],
            'first_pass_correct' => $totals['first_pass'],
            'final_correct'      => $totals['final'],
            'scorable_items'     => $totals['scorable'],
            'stars'              => $totals['stars'],
            'answer_changes'     => $totals['changes'],
            'hint_uses'          => $totals['hints'],
            'total_duration_ms'  => $totals['duration'],
            'audio'              => ['total_plays' => (int) ($audio['plays'] ?? 0), 'total_autoplays' => (int) ($audio['autoplays'] ?? 0)],
        ];
    }

    /** @return list<array<string, mixed>> */
    public function eventTimeline(int $sessionId, int $limit = 500): array
    {
        if ($this->schoolScope !== null) {
            $owned = $this->db()->table('game_sessions')
                ->join('participants', 'participants.id = game_sessions.participant_id')
                ->where('game_sessions.id', $sessionId)
                ->where('participants.school_id', $this->schoolScope)
                ->countAllResults();

            if ($owned === 0) {
                throw new \RuntimeException('Sesi ini di luar cakupan akses Anda.');
            }
        }

        return model(GameEventLogModel::class)->timeline($sessionId, $limit);
    }

    // ------------------------------------------------------------- internal

    // ------------------------------------------------ research revision 10, part 2 (28 §12)

    /**
     * Field sessions open today or closed less than 24 hours ago, with the
     * counts every monitoring viewer sees (admin: numbers only, K2).
     *
     * @return list<array<string, mixed>>
     */
    public function fieldSessionSummary(int $studyId): array
    {
        $fields = service('fieldSessions');
        $now    = date('Y-m-d H:i:s');
        $out    = [];

        foreach (model(\App\Models\FieldSessionModel::class)->recent($studyId, date('Y-m-d H:i:s', time() - 86400), $now) as $row) {
            $roster = $this->fieldSessionRoster($row);

            $out[] = [
                'id'           => (int) $row['id'],
                'code'         => (string) $row['code'],
                'opens_at'     => (string) $row['opens_at'],
                'closes_at'    => (string) ($row['closed_at'] ?? $row['closes_at']),
                'state'        => $fields->state($row),
                'roster'       => $roster,
                'entered'      => count(array_filter($roster, static fn (array $r): bool => $r['entered'])),
                'active'       => count(array_filter($roster, static fn (array $r): bool => $r['active'])),
                'not_entered'  => count(array_filter($roster, static fn (array $r): bool => ! $r['entered'])),
                'all_done'     => count(array_filter($roster, static fn (array $r): bool => $r['entered'] && $r['total'] > 0 && $r['done'] >= $r['total'])),
                'dropped'      => array_sum(array_column($roster, 'dropped')),
                'no_seat'      => count(array_filter($roster, static fn (array $r): bool => ! $r['has_seat'])),
                'small_screen' => $this->db()->table('audit_logs')->where('action', 'small_screen_refused')
                    ->where('target_type', 'field_session')->where('target_id', (string) $row['id'])->countAllResults(),
            ];
        }

        return $out;
    }

    /**
     * The monitoring panel's data (28 §12). With $detail (researchers and
     * the head researcher) the token and per-code lists come along; without
     * it (admin, K2) only numbers. Never answers, scores, or stars.
     *
     * @return list<array<string, mixed>>
     */
    public function monitoring(int $studyId, bool $detail): array
    {
        $out = [];

        foreach ($this->fieldSessionSummary($studyId) as $row) {
            $roster = $row['roster'];
            unset($row['roster']);

            if ($detail) {
                $row['token']    = $row['state'] === 'closed' ? null : service('fieldSessions')->currentToken($row['id']);
                $row['students'] = $roster;
                // Part 4 (30 §5.2): "izin belum diisi" as a number, researchers and the head researcher only
                $row['consent_missing'] = service('consentService')->missingForCodes(array_column($roster, 'participant_code'));
            }

            $out[] = $row;
        }

        return $out;
    }

    /**
     * One row per student of the field session's class group (make-ups: its
     * members): entered, active now, challenges done in the meeting's regions,
     * challenges left over (meeting 5), lost events, and whether a seat exists.
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<array{participant_code: string, entered: bool, active: bool, done: int, total: int, leftovers: int, dropped: int, has_seat: bool}>
     */
    public function fieldSessionRoster(array $fieldSession): array
    {
        $fields   = service('fieldSessions');
        $id       = (int) $fieldSession['id'];
        $studyId  = (int) $fieldSession['study_id'];
        $plan     = $fields->planEntry($fieldSession);
        $students = $fields->roster($fieldSession);

        if ((int) $fieldSession['makeup_no'] > 0) {
            $members  = model(\App\Models\FieldSessionMemberModel::class)->memberIds($id);
            $students = array_values(array_filter($students, static fn (array $s): bool => in_array($s['id'], $members, true)));
        }

        $ids      = array_column($students, 'id');
        $sessions = $ids === [] ? [] : $this->db()->table('game_sessions')
            ->select('id, participant_id, last_active_at')
            ->where('field_session_id', $id)
            ->whereIn('participant_id', $ids)
            ->get()->getResultArray();
        $cutoff  = date('Y-m-d H:i:s', time() - self::ACTIVE_MINUTES * 60);
        $entered = [];
        $active  = [];

        foreach ($sessions as $session) {
            $entered[(int) $session['participant_id']] = true;

            if ((string) $session['last_active_at'] >= $cutoff) {
                $active[(int) $session['participant_id']] = true;
            }
        }

        $content = service('contentRepository');
        $nodeIds = [];

        foreach ($plan['regions'] as $code) {
            $level = $content->level($code);

            foreach ($level === null ? [] : $content->nodesForLevel($level->id) as $node) {
                $nodeIds[] = (int) $node->id;
            }
        }

        $allNodes = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $allNodes[] = (int) $node->id;
            }
        }

        $dropped = $this->droppedEvents(array_map('intval', array_column($sessions, 'id')));
        $seats   = model(\App\Models\ParticipantSeatModel::class)->forParticipants($studyId, $ids);
        $out     = [];

        foreach ($students as $student) {
            $pid  = $student['id'];
            $done = array_keys(model(\App\Models\ChallengeAttemptModel::class)->completedForParticipant($pid, $studyId));
            $lost = 0;

            foreach ($sessions as $session) {
                if ((int) $session['participant_id'] === $pid) {
                    $lost += $dropped[(int) $session['id']] ?? 0;
                }
            }

            $out[] = [
                'participant_code' => $student['participant_code'],
                'entered'          => isset($entered[$pid]),
                'active'           => isset($active[$pid]),
                'done'             => count(array_intersect($nodeIds, $done)),
                'total'            => count($nodeIds),
                'leftovers'        => $plan['leftovers'] ? count(array_diff($allNodes, $done)) : 0,
                'dropped'          => $lost,
                'has_seat'         => isset($seats[$pid]),
            ];
        }

        return $out;
    }

    /**
     * Lost device events per game session: the sum of `dropped` in the
     * `events_dropped` events of these sessions (28 §12).
     *
     * @param list<int> $sessionIds
     *
     * @return array<int, int> session id => dropped events
     */
    public function droppedEvents(array $sessionIds): array
    {
        if ($sessionIds === []) {
            return [];
        }

        $out  = [];
        $rows = $this->db()->table('game_event_logs')
            ->select('session_id, payload_json')
            ->where('event_type', 'events_dropped')
            ->whereIn('session_id', $sessionIds)
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $payload = json_decode((string) $row['payload_json'], true);
            $out[(int) $row['session_id']] = ($out[(int) $row['session_id']] ?? 0) + max(0, (int) ($payload['dropped'] ?? 0));
        }

        return $out;
    }

    /**
     * Satu baris per tantangan aktif (urutan wilayah lalu tantangan): status
     * (`not_started` | `in_progress` | `completed` | `abandoned`), jumlah
     * pengerjaan, dan isi pengerjaan yang ditampilkan — pengerjaan selesai
     * terbaik (bintang terbanyak, paling awal), selain itu yang terakhir.
     *
     * @param list<array<string, mixed>> $attempts semua pengerjaan siswa, urut mulai
     *
     * @return list<array<string, mixed>>
     */
    private function progressRows(array $attempts): array
    {
        $nodes = $this->db()->table('challenge_nodes')
            ->select('challenge_nodes.id, challenge_nodes.sequence, challenge_nodes.engine_type, challenge_nodes.title_id')
            ->select('levels.code AS level_code, levels.name_id AS level_name')
            ->join('levels', 'levels.id = challenge_nodes.level_id')
            ->where('challenge_nodes.is_active', 1)
            ->orderBy('levels.sequence', 'ASC')
            ->orderBy('challenge_nodes.sequence', 'ASC')
            ->get()
            ->getResultArray();

        $byNode = [];

        foreach ($attempts as $attempt) {
            $byNode[(int) $attempt['challenge_node_id']][] = $attempt;
        }

        $rows = [];

        foreach ($nodes as $node) {
            $list  = $byNode[(int) $node['id']] ?? [];
            $shown = null;

            foreach ($list as $attempt) {
                if ($attempt['status'] === 'completed' && ($shown === null || (int) $attempt['stars'] > (int) $shown['stars'])) {
                    $shown = $attempt;
                }
            }

            $shown ??= $list === [] ? null : $list[array_key_last($list)];

            $rows[] = [
                'node_ref'           => node_ref((string) $node['level_code'], (int) $node['sequence']),
                'level'              => $node['level_name'],
                'title'              => $node['title_id'],
                'engine_type'        => $node['engine_type'],
                'status'             => $shown === null ? 'not_started' : $shown['status'],
                'attempts'           => count($list),
                'variant_group'      => $shown['variant_group'] ?? null,
                'check_count'        => $shown === null ? null : (int) $shown['check_count'],
                'first_pass_correct' => $shown === null ? null : (int) $shown['first_pass_correct'],
                'scorable_items'     => $shown === null ? null : (int) $shown['scorable_items'],
                'stars'              => $shown === null ? null : (int) $shown['stars'],
                'duration_ms'        => $shown === null || $shown['duration_ms'] === null ? null : (int) $shown['duration_ms'],
            ];
        }

        return $rows;
    }

    /** Filter milik siswa (tanpa sesi) untuk hitungan siswa terdaftar. */
    private function participantFilters(BaseBuilder $builder, array $filters): void
    {
        foreach (['school_id', 'class_level', 'province_code'] as $key) {
            if (isset($filters[$key]) && $filters[$key] !== '') {
                $builder->where('participants.' . $key, $filters[$key]);
            }
        }
    }

    private function sessionBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('game_sessions')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters, ['level_id', 'node_id']);
    }

    private function attemptBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('challenge_attempts')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters);
    }

    /** Batas akses sekolah dipaksakan di sini, bukan di pemanggil. */
    private function scoped(array $filters): array
    {
        if ($this->schoolScope !== null) {
            $filters['school_id'] = $this->schoolScope;
        }

        return $filters;
    }

    /** Kolom ber-prefix dan ber-quote untuk ekspresi SQL mentah (SUM(CASE …)). */
    private function col(string $column): string
    {
        return $this->db()->protectIdentifiers($column);
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
