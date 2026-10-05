<?php

namespace App\Services;

use App\Models\AudioUsageEventModel;
use App\Models\GameEventLogModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\BaseBuilder;

/**
 * Angka ringkas untuk panel admin — HANYA hitungan data mentah (perombakan
 * kelas 5, Dok 1 §12 / K7). Indeks kesulitan, daya pembeda, capaian
 * indikator, pretest–posttest, skor, dan chart sudah dihapus; pengolahan
 * dilakukan peneliti dari ekspor data mentah.
 *
 * Otorisasi: bila $schoolScope bukan NULL, SETIAP query menambahkan
 * participants.school_id = $schoolScope. Batas ini diterapkan di service,
 * bukan hanya di filter route, supaya tidak dapat dilewati lewat jalur lain.
 */
class AnalyticsService
{
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
     * Hitungan baris mentah dalam cakupan pemanggil.
     *
     * @return array{participant_count: int, session_count: int, completed_sessions: int,
     *               completed_attempts: int, abandoned_attempts: int, item_tries: int,
     *               hint_count: int, audio_usage: array<string, mixed>}
     */
    public function summary(array $filters = []): array
    {
        $filters = $this->scoped($filters);

        $sessions = $this->sessionBuilder($filters)
            ->select('COUNT(DISTINCT game_sessions.id) AS session_count', false)
            ->select('COUNT(DISTINCT game_sessions.participant_id) AS participant_count', false)
            ->select("SUM(CASE WHEN game_sessions.status = 'completed' THEN 1 ELSE 0 END) AS completed", false)
            ->get()
            ->getRowArray();

        $attempts = $this->attemptBuilder($filters)
            ->select("SUM(CASE WHEN challenge_attempts.status = 'completed' THEN 1 ELSE 0 END) AS completed", false)
            ->select("SUM(CASE WHEN challenge_attempts.status = 'abandoned' THEN 1 ELSE 0 END) AS abandoned", false)
            ->select('SUM(challenge_attempts.hint_count) AS hints', false)
            ->get()
            ->getRowArray();

        $tries = $this->attemptBuilder($filters)
            ->join('item_tries', 'item_tries.challenge_attempt_id = challenge_attempts.id')
            ->select('COUNT(*) AS total', false)
            ->get()
            ->getRowArray();

        return [
            'participant_count'  => (int) ($sessions['participant_count'] ?? 0),
            'session_count'      => (int) ($sessions['session_count'] ?? 0),
            'completed_sessions' => (int) ($sessions['completed'] ?? 0),
            'completed_attempts' => (int) ($attempts['completed'] ?? 0),
            'abandoned_attempts' => (int) ($attempts['abandoned'] ?? 0),
            'item_tries'         => (int) ($tries['total'] ?? 0),
            'hint_count'         => (int) ($attempts['hints'] ?? 0),
            'audio_usage'        => model(AudioUsageEventModel::class)->usageStats($filters),
        ];
    }

    /**
     * Profil satu peserta: data mentah pengerjaan selesai (tanpa skor).
     * Identitas hanya disertakan bila mode anonim mati dan pemanggil memang
     * berhak atas sekolah peserta tersebut.
     *
     * @return array<string, mixed>
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

        $filters = $this->scoped([]);
        $rows    = $this->attemptBuilder($filters)
            ->select('challenge_attempts.*, challenge_nodes.level_id', false)
            ->where('game_sessions.participant_id', $participantId)
            ->where('challenge_attempts.status', 'completed')
            ->orderBy('challenge_nodes.level_id', 'ASC')
            ->get()
            ->getResultArray();

        $totals = ['attempts' => 0, 'changes' => 0, 'hints' => 0, 'duration' => 0, 'first_pass' => 0, 'final' => 0, 'scorable' => 0, 'stars' => 0];

        foreach ($rows as $row) {
            $totals['attempts']++;
            $totals['changes'] += (int) $row['answer_change_count'];
            $totals['hints'] += (int) $row['hint_count'];
            $totals['duration'] += (int) $row['duration_ms'];
            $totals['first_pass'] += (int) $row['first_pass_correct'];
            $totals['final'] += (int) $row['final_correct'];
            $totals['scorable'] += (int) $row['scorable_items'];
            $totals['stars'] += (int) $row['stars'];
        }

        $profile = [
            'participant'        => $this->anonymous
                ? ['code' => $participant->anonLabel()]
                : $participant->toSafeArray(),
            'completed_attempts' => $totals['attempts'],
            'first_pass_correct' => $totals['first_pass'],
            'final_correct'      => $totals['final'],
            'scorable_items'     => $totals['scorable'],
            'stars'              => $totals['stars'],
            'answer_changes'     => $totals['changes'],
            'hint_uses'          => $totals['hints'],
            'total_duration_ms'  => $totals['duration'],
            'audio'              => model(AudioUsageEventModel::class)->usageStats(['participant_id' => $participantId] + $filters),
        ];

        if ($this->anonymous) {
            unset($profile['participant']['username'], $profile['participant']['display_name']);
        }

        return $profile;
    }

    /**
     * Metrik literasi keamanan digital saat pendaftaran (hitungan per nilai).
     *
     * @return array<string, mixed>
     */
    public function digitalSecurityLiteracy(array $filters = []): array
    {
        $filters = $this->scoped($filters);

        return ['password' => model(ParticipantModel::class)->digitalSecurityStats($filters)];
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

    private function db(): \CodeIgniter\Database\BaseConnection
    {
        return db_connect();
    }
}
