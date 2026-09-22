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
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id', 'left');

        if ($scope !== null) {
            $sessions->where('participants.school_id', $scope);
        }

        foreach (['study_id' => 'game_sessions.study_id', 'locale' => 'game_sessions.locale'] as $key => $column) {
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
            'rows'    => $rows,
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
            'participant' => $participant,
            'attempts'    => $attempts,
            'nodes'       => $this->nodeLabels(),
        ]);
    }

    public function timeline(int $sessionId): string
    {
        $session = $this->requireInScope($sessionId);

        return $this->panel('admin/sessions/timeline', 'Linimasa event', [
            'session' => $session,
            'rows'    => $this->analytics()->eventTimeline($sessionId),
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

    /** @return array<int, string> id node → "Wilayah · judul" untuk label tabel */
    private function nodeLabels(): array
    {
        $content = service('contentRepository');
        $labels  = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $labels[$node->id] = $level->text('name', 'id') . ' · ' . $node->text('title', 'id');
            }
        }

        return $labels;
    }
}
