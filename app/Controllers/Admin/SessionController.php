<?php

namespace App\Controllers\Admin;

use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Daftar sesi permainan, detail sesi beserta attempt-nya, dan linimasa event.
 */
class SessionController extends BaseAdminController
{
    private const PER_PAGE = 25;

    public function index(): string
    {
        $sessions = model(GameSessionModel::class);
        $filters  = $this->readFilters();
        $scope    = $this->schoolScope();

        $sessions
            ->select('game_sessions.*, participants.participant_code, participants.display_name, participants.school_id')
            ->select('research_phases.code AS phase_code, research_studies.code AS study_code')
            ->select('session_progress.completed_nodes, session_progress.total_score')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id', 'left')
            ->join('research_studies', 'research_studies.id = game_sessions.study_id', 'left')
            ->join('session_progress', 'session_progress.session_id = game_sessions.id', 'left');

        if ($scope !== null) {
            $sessions->where('participants.school_id', $scope);
        } elseif (isset($filters['school_id'])) {
            $sessions->where('participants.school_id', $filters['school_id']);
        }

        foreach (['study_id' => 'game_sessions.study_id', 'locale' => 'game_sessions.locale', 'class_level' => 'participants.class_level'] as $key => $column) {
            if (isset($filters[$key])) {
                $sessions->where($column, $filters[$key]);
            }
        }

        if (isset($filters['phase_code'])) {
            $sessions->where('research_phases.code', $filters['phase_code']);
        }

        if (isset($filters['date_from'])) {
            $sessions->where('game_sessions.started_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $sessions->where('game_sessions.started_at <=', $filters['date_to'] . ' 23:59:59');
        }

        $rows = $sessions->orderBy('game_sessions.started_at', 'DESC')->paginate(self::PER_PAGE);

        return $this->panel('admin/sessions/index', 'Sesi permainan', [
            'filters' => $filters,
            'rows'    => array_map(static fn ($row): array => $row->toArray(), $rows),
            'pager'   => $sessions->pager,
        ]);
    }

    public function show(int $sessionId): string
    {
        $session     = $this->requireInScope($sessionId);
        $participant = model(ParticipantModel::class)->find($session->participant_id);

        $attempts = model(ChallengeAttemptModel::class)
            ->where('session_id', $sessionId)
            ->orderBy('started_at', 'ASC')
            ->findAll();

        return $this->panel('admin/sessions/show', 'Detail sesi', [
            'session'     => $session,
            'participant' => $participant?->toSafeArray(),
            'progress'    => db_connect()->table('session_progress')->where('session_id', $sessionId)->get()->getRowArray(),
            'phaseCode'   => $this->phaseCode((int) $session->phase_id),
            'attempts'    => $attempts,
            'responses'   => $this->responsesByAttempt(array_map(static fn ($a): int => $a->id, $attempts)),
            'nodes'       => $this->nodeLabels(),
            'engines'     => $this->nodeEngines(),
        ]);
    }

    public function timeline(int $sessionId): string
    {
        $session = $this->requireInScope($sessionId);
        $rows    = $this->analytics()->eventTimeline($sessionId);
        $types   = array_values(array_unique(array_column($rows, 'event_type')));
        $type    = trim((string) ($this->request->getGet('type') ?? ''));

        sort($types);

        if ($type !== '') {
            $rows = array_values(array_filter($rows, static fn (array $row): bool => $row['event_type'] === $type));
        }

        return $this->panel('admin/sessions/timeline', 'Linimasa event', [
            'session' => $session,
            'rows'    => $rows,
            'types'   => $types,
            'type'    => $type,
            'nodes'   => $this->nodeLabels(),
        ]);
    }

    /** Sesi dalam cakupan pemanggil, atau 404. */
    private function requireInScope(int $sessionId): \App\Entities\GameSession
    {
        $session = model(GameSessionModel::class)->find($sessionId);

        if ($session === null) {
            throw PageNotFoundException::forPageNotFound("Sesi {$sessionId} tidak ditemukan.");
        }

        $scope = $this->schoolScope();

        if ($scope !== null) {
            $participant = model(ParticipantModel::class)->find($session->participant_id);

            if ($participant === null || (int) $participant->school_id !== $scope) {
                throw PageNotFoundException::forPageNotFound("Sesi {$sessionId} tidak ditemukan.");
            }
        }

        return $session;
    }

    /**
     * Jawaban per butir untuk setiap attempt sesi ini (drilldown baris attempt).
     *
     * @param list<int> $attemptIds
     *
     * @return array<int, list<array<string, mixed>>>
     */
    private function responsesByAttempt(array $attemptIds): array
    {
        if ($attemptIds === []) {
            return [];
        }

        $rows = db_connect()->table('item_responses ir')
            ->select('ir.challenge_attempt_id, ir.display_order, ir.status, ir.first_pass_correct, ir.is_correct')
            ->select('ir.change_count, ir.hint_used, ir.wrong_click_count, ir.duration_ms, ir.final_answer_json, ir.reason_text')
            ->select('ci.item_key, ci.interaction_type')
            ->join('challenge_items ci', 'ci.id = ir.challenge_item_id')
            ->whereIn('ir.challenge_attempt_id', $attemptIds)
            ->orderBy('ir.challenge_attempt_id', 'ASC')
            ->orderBy('ir.display_order', 'ASC')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $out[(int) $row['challenge_attempt_id']][] = $row;
        }

        return $out;
    }

    private function phaseCode(int $phaseId): ?string
    {
        $row = db_connect()->table('research_phases')->select('code')->where('id', $phaseId)->get()->getRowArray();

        return $row['code'] ?? null;
    }

    /** @return array<int, string> id node → "Wilayah · judul" untuk label tabel */
    private function nodeLabels(): array
    {
        $content = service('contentRepository');
        $labels  = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $labels[$node->id] = $level->text('name', 'id') . ' · ' . $node->sequence . '. ' . $node->text('title', 'id');
            }
        }

        return $labels;
    }

    /** @return array<int, string> id node → engine_type */
    private function nodeEngines(): array
    {
        $content = service('contentRepository');
        $engines = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $engines[$node->id] = (string) $node->engine_type;
            }
        }

        return $engines;
    }
}
