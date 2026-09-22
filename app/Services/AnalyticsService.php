<?php

namespace App\Services;

use App\Entities\ChallengeNode;
use App\Models\AudioUsageEventModel;
use App\Models\ChallengeAttemptModel;
use App\Models\GameEventLogModel;
use App\Models\ItemResponseModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\BaseBuilder;

/**
 * Dataset ternormalisasi untuk dasbor dan export. Tidak menggambar chart,
 * tidak membentuk HTML.
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
     * @return array{participant_count: int, session_count: int, completion_rate: float,
     *               avg_score: float, avg_first_pass_accuracy: float, avg_duration_ms: int,
     *               hint_usage: float, audio_usage: array<string, mixed>,
     *               pretest_posttest_delta: ?float}
     */
    public function summary(array $filters = []): array
    {
        $filters = $this->scoped($filters);

        $sessions = $this->sessionBuilder($filters)
            ->select('COUNT(DISTINCT game_sessions.id) AS session_count', false)
            ->select('COUNT(DISTINCT game_sessions.participant_id) AS participant_count', false)
            ->select("SUM(CASE WHEN game_sessions.status = 'completed' THEN 1 ELSE 0 END) AS completed", false)
            ->select('AVG(game_sessions.duration_ms) AS avg_duration_ms', false)
            ->get()
            ->getRowArray();

        $attempts = $this->attemptBuilder($filters)
            ->select('AVG(challenge_attempts.score) AS avg_score', false)
            ->select('AVG(challenge_attempts.first_pass_accuracy) AS avg_first_pass', false)
            ->select('AVG(challenge_attempts.hint_count) AS avg_hint', false)
            ->where('challenge_attempts.status', 'completed')
            ->get()
            ->getRowArray();

        $sessionCount = (int) ($sessions['session_count'] ?? 0);
        $delta        = $this->prePostComparison($filters);

        return [
            'participant_count'       => (int) ($sessions['participant_count'] ?? 0),
            'session_count'           => $sessionCount,
            'completion_rate'         => $sessionCount > 0
                ? round((int) ($sessions['completed'] ?? 0) / $sessionCount, 4)
                : 0.0,
            'avg_score'               => round((float) ($attempts['avg_score'] ?? 0), 2),
            'avg_first_pass_accuracy' => round((float) ($attempts['avg_first_pass'] ?? 0), 2),
            'avg_duration_ms'         => (int) round((float) ($sessions['avg_duration_ms'] ?? 0)),
            'hint_usage'              => round((float) ($attempts['avg_hint'] ?? 0), 2),
            'audio_usage'             => model(AudioUsageEventModel::class)->usageStats($filters),
            'pretest_posttest_delta'  => $delta['delta'],
        ];
    }

    /**
     * @return array<int, array{level_id: int, code: string, name: string, avg_score: float,
     *                          avg_first_pass: float, completed_attempts: int, avg_duration_ms: int}>
     */
    public function levelBreakdown(array $filters = []): array
    {
        $filters = $this->scoped($filters);
        $out     = [];

        foreach (service('contentRepository')->levels() as $level) {
            $row = $this->attemptBuilder($filters)
                ->select('AVG(challenge_attempts.score) AS avg_score', false)
                ->select('AVG(challenge_attempts.first_pass_accuracy) AS avg_first_pass', false)
                ->select('AVG(challenge_attempts.duration_ms) AS avg_duration_ms', false)
                ->select('COUNT(*) AS completed_attempts', false)
                ->where('challenge_nodes.level_id', $level->id)
                ->where('challenge_attempts.status', 'completed')
                ->get()
                ->getRowArray();

            $out[$level->id] = [
                'level_id'           => $level->id,
                'code'               => (string) $level->code,
                'name'               => $level->text('name'),
                'avg_score'          => round((float) ($row['avg_score'] ?? 0), 2),
                'avg_first_pass'     => round((float) ($row['avg_first_pass'] ?? 0), 2),
                'completed_attempts' => (int) ($row['completed_attempts'] ?? 0),
                'avg_duration_ms'    => (int) round((float) ($row['avg_duration_ms'] ?? 0)),
            ];
        }

        return $out;
    }

    /**
     * Kesulitan per node. difficulty_index memakai rumus 01_DATABASE.md:
     * 40% ketepatan percobaan pertama, 20% retry, 20% hint, 10% durasi, 10% skip.
     *
     * @return array<int, array<string, mixed>>
     */
    public function nodeDifficulty(array $filters = []): array
    {
        $filters  = $this->scoped($filters);
        $attempts = model(ChallengeAttemptModel::class);
        $content  = service('contentRepository');

        $stats = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $stats[$node->id] = ['node' => $node] + $attempts->statsForNode($node->id, $filters);
            }
        }

        $maxRetry    = $this->maxOf($stats, 'mean_retry');
        $maxHint     = $this->maxOf($stats, 'mean_hint');
        $maxDuration = $this->maxOf($stats, 'median_duration_ms');

        $out = [];

        foreach ($stats as $nodeId => $row) {
            /** @var ChallengeNode $node */
            $node = $row['node'];

            $index = 0.40 * (100 - $row['mean_first_pass'])
                + 0.20 * $this->normalize($row['mean_retry'], $maxRetry)
                + 0.20 * $this->normalize($row['mean_hint'], $maxHint)
                + 0.10 * $this->normalize((float) $row['median_duration_ms'], $maxDuration)
                + 0.10 * ($row['skip_rate'] * 100);

            $out[$nodeId] = [
                'node_id'            => $nodeId,
                'level_id'           => $node->level_id,
                'sequence'           => $node->sequence,
                'engine_type'        => $node->engine_type,
                'title'              => $node->text('title'),
                'attempts'           => $row['attempts'],
                'mean_first_pass'    => $row['mean_first_pass'],
                'mean_final'         => $row['mean_final'],
                'mean_retry'         => $row['mean_retry'],
                'mean_hint'          => $row['mean_hint'],
                'median_duration_ms' => $row['median_duration_ms'],
                'skip_rate'          => $row['skip_rate'],
                'difficulty_index'   => round(clamp($index, 0, 100), 2),
            ];
        }

        return $out;
    }

    /**
     * Analisis butir: p (kesukaran) dan D (daya beda 27% atas vs bawah).
     *
     * @return array<int, array<string, mixed>>
     */
    public function itemAnalysis(array $filters = []): array
    {
        $filters = $this->scoped($filters);
        $items   = model(ItemResponseModel::class)->itemAnalysis($filters);

        if ($items === []) {
            return [];
        }

        $groups = $this->discriminationGroups($filters);

        foreach (array_keys($items) as $itemId) {
            $items[$itemId]['d'] = $this->discrimination($itemId, $groups, $filters);
        }

        return $items;
    }

    /**
     * Penguasaan per indikator — rasio bukti, bukan label biner lulus/tidak.
     *
     * @return array<string, array{code: string, name: string, evidence_count: int,
     *                             correct_count: int, mastery_ratio: float, mean_response_ms: int}>
     */
    public function indicatorMastery(array $filters = []): array
    {
        $filters = $this->scoped($filters);

        $rows = $this->responseBuilder($filters)
            ->select('li.code, li.name_id, li.name_en', false)
            ->select('COUNT(*) AS evidence_count', false)
            ->select('SUM(CASE WHEN ir.first_pass_correct = 1 THEN 1 ELSE 0 END) AS correct_count', false)
            ->select('AVG(ir.duration_ms) AS mean_response_ms', false)
            // indikator item lebih spesifik daripada indikator node; COALESCE tidak boleh di-escape
            ->join('learning_indicators li', 'li.id = COALESCE(ci.indicator_id, challenge_nodes.indicator_id)', 'inner', false)
            ->where('ir.status', 'answered')
            ->groupBy('li.code, li.name_id, li.name_en')
            ->orderBy('li.code', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $evidence = (int) $row['evidence_count'];

            $out[(string) $row['code']] = [
                'code'             => (string) $row['code'],
                'name'             => tr($row, 'name'),
                'evidence_count'   => $evidence,
                'correct_count'    => (int) $row['correct_count'],
                'mastery_ratio'    => $evidence > 0 ? round((int) $row['correct_count'] / $evidence, 4) : 0.0,
                'mean_response_ms' => (int) round((float) ($row['mean_response_ms'] ?? 0)),
            ];
        }

        return $out;
    }

    /**
     * Profil satu peserta. Identitas hanya disertakan bila mode anonim mati
     * dan pemanggil memang berhak atas sekolah peserta tersebut.
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

        $byLevel     = [];
        $hardestNode = null;
        $totals      = ['attempts' => 0, 'changes' => 0, 'hints' => 0, 'duration' => 0];

        foreach ($rows as $row) {
            $levelId = (int) $row['level_id'];

            $byLevel[$levelId][] = (float) $row['score'];
            $totals['attempts']++;
            $totals['changes'] += (int) $row['answer_change_count'];
            $totals['hints'] += (int) $row['hint_count'];
            $totals['duration'] += (int) $row['duration_ms'];

            if ($hardestNode === null || (float) $row['first_pass_accuracy'] < $hardestNode['first_pass_accuracy']) {
                $hardestNode = [
                    'node_id'             => (int) $row['challenge_node_id'],
                    'first_pass_accuracy' => (float) $row['first_pass_accuracy'],
                ];
            }
        }

        $levelScores = [];

        foreach ($byLevel as $levelId => $scores) {
            $levelScores[$levelId] = round(array_sum($scores) / count($scores), 2);
        }

        // Indikator dan audio dihitung hanya dari sesi peserta ini.
        $ownFilters = ['participant_id' => $participantId] + $filters;

        $profile = [
            'participant'       => $this->anonymous
                ? ['code' => $participant->anonLabel()]
                : $participant->toSafeArray(),
            'level_scores'      => $levelScores,
            'total_score'       => $levelScores === [] ? 0.0 : round(array_sum($levelScores) / max(1, count($levelScores)), 2),
            'completed_nodes'   => $totals['attempts'],
            'answer_changes'    => $totals['changes'],
            'hint_uses'         => $totals['hints'],
            'total_duration_ms' => $totals['duration'],
            'mean_response_ms'  => $totals['attempts'] > 0 ? (int) round($totals['duration'] / $totals['attempts']) : 0,
            'hardest_node'      => $hardestNode,
            'indicators'        => $this->indicatorMastery($ownFilters),
            'audio'             => model(AudioUsageEventModel::class)->usageStats($ownFilters),
            'pre_post'          => $this->prePostComparison($ownFilters),
        ];

        if ($this->anonymous) {
            unset($profile['participant']['username'], $profile['participant']['display_name']);
        }

        return $profile;
    }

    /**
     * Perbandingan pretest–posttest. Hanya sesi dengan release + scoring_version +
     * content_version yang kompatibel yang dibandingkan; pasangan tak kompatibel
     * diberi label terpisah dan tidak dicampur ke dalam delta.
     *
     * @return array{pretest: float, posttest: float, delta: ?float, pairs: int, incompatible_pairs: int}
     */
    public function prePostComparison(array $filters = []): array
    {
        $filters = $this->scoped($filters);
        unset($filters['phase_code']);

        $rows = $this->sessionBuilder($filters)
            ->select('game_sessions.participant_id, game_sessions.release_id', false)
            ->select('research_phases.code AS phase_code', false)
            ->select('session_progress.total_score', false)
            ->join('session_progress', 'session_progress.session_id = game_sessions.id')
            ->whereIn('research_phases.code', ['pretest', 'posttest'])
            ->where('game_sessions.status', 'completed')
            ->orderBy('game_sessions.ended_at', 'ASC')
            ->get()
            ->getResultArray();

        $byParticipant = [];

        foreach ($rows as $row) {
            $byParticipant[(int) $row['participant_id']][(string) $row['phase_code']] = [
                'score'      => (float) $row['total_score'],
                'release_id' => (int) $row['release_id'],
            ];
        }

        $pre          = [];
        $post         = [];
        $incompatible = 0;

        foreach ($byParticipant as $pair) {
            if (! isset($pair['pretest'], $pair['posttest'])) {
                continue;
            }

            if ($pair['pretest']['release_id'] !== $pair['posttest']['release_id']) {
                $incompatible++;

                continue;
            }

            $pre[]  = $pair['pretest']['score'];
            $post[] = $pair['posttest']['score'];
        }

        $pairs       = count($pre);
        $preMean     = $pairs > 0 ? round(array_sum($pre) / $pairs, 2) : 0.0;
        $postMean    = $pairs > 0 ? round(array_sum($post) / $pairs, 2) : 0.0;

        return [
            'pretest'            => $preMean,
            'posttest'           => $postMean,
            'delta'              => $pairs > 0 ? round($postMean - $preMean, 2) : null,
            'pairs'              => $pairs,
            'incompatible_pairs' => $incompatible,
        ];
    }

    /**
     * Literasi keamanan digital: dari proses pembuatan sandi (tanpa pernah
     * menyentuh password_hash) dan dari ketepatan item berpilar digital.
     *
     * @return array<string, mixed>
     */
    public function digitalSecurityLiteracy(array $filters = []): array
    {
        $filters = $this->scoped($filters);

        return [
            'password' => model(ParticipantModel::class)->digitalSecurityStats($filters),
            'pillars'  => $this->pillarAccuracy($filters),
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

    /** Ketepatan per digital_pillar (Wonosobo node 5). */
    private function pillarAccuracy(array $filters): array
    {
        $rows = $this->responseBuilder($filters)
            ->select('ci.config_json, ir.first_pass_correct', false)
            ->where('ir.status', 'answered')
            ->where('ci.config_json IS NOT NULL')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $config = json_decode((string) $row['config_json'], true) ?: [];
            $pillar = (string) ($config['digital_pillar'] ?? '');

            if ($pillar === '') {
                continue;
            }

            $out[$pillar]['appeared'] = ($out[$pillar]['appeared'] ?? 0) + 1;
            $out[$pillar]['correct']  = ($out[$pillar]['correct'] ?? 0) + ((int) $row['first_pass_correct'] === 1 ? 1 : 0);
        }

        foreach ($out as $pillar => $counts) {
            $out[$pillar]['accuracy'] = $counts['appeared'] > 0
                ? round($counts['correct'] / $counts['appeared'], 4)
                : 0.0;
        }

        return $out;
    }

    /**
     * Kelompok 27% teratas dan terbawah berdasarkan total ketepatan peserta.
     *
     * @return array{upper: list<int>, lower: list<int>}
     */
    private function discriminationGroups(array $filters): array
    {
        $rows = $this->responseBuilder($filters)
            ->select('game_sessions.participant_id', false)
            ->select('AVG(CASE WHEN ir.first_pass_correct = 1 THEN 1 ELSE 0 END) AS accuracy', false)
            ->where('ir.status', 'answered')
            ->groupBy('game_sessions.participant_id')
            ->orderBy('accuracy', 'DESC')
            ->get()
            ->getResultArray();

        $count = count($rows);

        if ($count < 4) {
            return ['upper' => [], 'lower' => []];
        }

        $size = max(1, (int) round($count * 0.27));

        return [
            'upper' => array_map(static fn ($r): int => (int) $r['participant_id'], array_slice($rows, 0, $size)),
            'lower' => array_map(static fn ($r): int => (int) $r['participant_id'], array_slice($rows, -$size)),
        ];
    }

    /** @param array{upper: list<int>, lower: list<int>} $groups */
    private function discrimination(int $itemId, array $groups, array $filters): ?float
    {
        if ($groups['upper'] === [] || $groups['lower'] === []) {
            return null;
        }

        $upper = $this->groupAccuracy($itemId, $groups['upper'], $filters);
        $lower = $this->groupAccuracy($itemId, $groups['lower'], $filters);

        if ($upper === null || $lower === null) {
            return null;
        }

        return round($upper - $lower, 4);
    }

    /** @param list<int> $participantIds */
    private function groupAccuracy(int $itemId, array $participantIds, array $filters): ?float
    {
        $row = $this->responseBuilder($filters)
            ->select('COUNT(*) AS appeared', false)
            ->select('SUM(CASE WHEN ir.first_pass_correct = 1 THEN 1 ELSE 0 END) AS correct', false)
            ->where('ir.challenge_item_id', $itemId)
            ->where('ir.status', 'answered')
            ->whereIn('game_sessions.participant_id', $participantIds)
            ->get()
            ->getRowArray();

        $appeared = (int) ($row['appeared'] ?? 0);

        return $appeared > 0 ? (int) $row['correct'] / $appeared : null;
    }

    private function sessionBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('game_sessions')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters, ['level_id', 'node_id']);
    }

    private function attemptBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('challenge_attempts')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters);
    }

    private function responseBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('item_responses ir')
            ->join('challenge_items ci', 'ci.id = ir.challenge_item_id')
            ->join('challenge_attempts ca', 'ca.id = ir.challenge_attempt_id')
            ->join('game_sessions', 'game_sessions.id = ca.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->join('challenge_nodes', 'challenge_nodes.id = ca.challenge_node_id')
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

    private function maxOf(array $stats, string $key): float
    {
        $max = 0.0;

        foreach ($stats as $row) {
            $max = max($max, (float) $row[$key]);
        }

        return $max;
    }

    private function normalize(float $value, float $max): float
    {
        return $max > 0 ? ($value / $max) * 100 : 0.0;
    }

    private function db(): \CodeIgniter\Database\BaseConnection
    {
        return db_connect();
    }
}
