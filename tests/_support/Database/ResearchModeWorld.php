<?php

namespace Tests\Support\Database;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use CodeIgniter\Database\BaseConnection;

/**
 * Research mode for part 7 tests (33 §13.1): the field world (FieldWorld)
 * with an open P2 field session (Temanggung), student 1 seated with booklet
 * B1, and game session 2 of student 1 bound to that field session. Plus
 * helpers to open and finish challenges.
 *
 * The using class sets $conn in setUp(); it also uses FeatureTestTrait.
 */
trait ResearchModeWorld
{
    use FieldWorld;

    private BaseConnection $conn;

    /** @return array{field: array{id: int, token: string}, session: GameSession} */
    private function createResearchModeWorld(int $meetingNo = 2): array
    {
        $this->conn = $this->createFieldWorld();
        $field      = $this->fieldSession($this->conn, $meetingNo, 'GHJKMN');

        $this->conn->table('participant_seats')->insert([
            'participant_id' => 1, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B',
        ]);
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-P' . $meetingNo, 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id',
            'status' => 'active', 'field_session_id' => $field['id'], 'meeting_no' => $meetingNo, 'last_active_at' => date('Y-m-d H:i:s'),
        ]);
        $this->resetFieldServices();

        return ['field' => $field, 'session' => model(GameSessionModel::class)->find((int) $this->conn->insertID())];
    }

    private function gameSession(int $id): GameSession
    {
        return model(GameSessionModel::class)->find($id);
    }

    /** Completed attempt rows for the given challenge refs (e.g. `tmg-1`) in game session $sessionId. */
    private function markCompleted(int $sessionId, array $refs, int $stars = 2): void
    {
        foreach ($refs as $ref) {
            $this->conn->table('challenge_attempts')->insert([
                'session_id' => $sessionId, 'challenge_node_id' => $this->nodeId($this->conn, $ref), 'attempt_no' => 1,
                'status' => 'completed', 'completion_reason' => 'solved', 'stars' => $stars, 'selected_item_ids_json' => '[]',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /** Opens a challenge in a game session through the service; returns the open attempt. */
    private function openIn(int $sessionId, string $ref): ChallengeAttempt
    {
        $nodeId = $this->nodeId($this->conn, $ref);
        $this->resetFieldServices();
        service('challengeService')->openNode($this->gameSession($sessionId), $nodeId);

        return model(ChallengeAttemptModel::class)->openForParticipant($this->gameSession($sessionId)->participant_id, $nodeId, 1);
    }

    /** Every item of a batch attempt answered right in one check (closes it `solved`). */
    private function solveBatch(ChallengeAttempt $attempt): array
    {
        $answers = [];

        foreach (service('contentRepository')->itemBank($attempt->challenge_node_id) as $item) {
            if ($attempt->hasItem($item->id) && ! $item->isDecoy() && $item->scorable) {
                $answers[$item->id] = self::rightAnswer($item);
            }
        }

        return service('challengeService')->submitCheck(model(ChallengeAttemptModel::class)->find($attempt->id), $answers);
    }

    /** @return array<string, mixed> */
    private static function rightAnswer(ChallengeItem $item): array
    {
        return match ($item->interaction_type) {
            'puzzle_arrange'  => ['order' => array_values((array) $item->answerKey('order'))],
            'fill_blank_bank' => ['text' => (string) $item->answerKey('text_id')],
            'verdict_card'    => ['verdict' => $item->verdict()],
        };
    }

    /** @return list<array<string, mixed>> segments of an attempt, in order */
    private function segments(int $attemptId): array
    {
        return $this->conn->table('challenge_attempt_segments')->where('challenge_attempt_id', $attemptId)->orderBy('segment_no')->get()->getResultArray();
    }
}
