<?php

namespace Tests\Support\Database;

use App\Entities\ChallengeAttempt;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;
use Config\Encryption;

/**
 * Part 6 (32 §15.1): plays the Skenario Digital challenge of the grade 5
 * world through ChallengeService. Option refs are derived the way the server
 * derives them (optionRef()), so a test can choose an action by its bank score.
 *
 * Needs GradeFiveWorld; call prepareScenarioPlay() in setUp() and
 * finishScenarioPlay() in tearDown().
 */
trait ScenarioPlay
{
    use GradeFiveWorld;

    private BaseConnection $conn;
    private ChallengeService $service;
    private string $originalKey = '';

    private function prepareScenarioPlay(): void
    {
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-skenario';

        $this->conn    = $this->createGradeFiveWorld();
        $this->service = new ChallengeService();
    }

    private function finishScenarioPlay(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
    }

    private function session(int $id = 1): GameSession
    {
        return model(GameSessionModel::class)->find($id);
    }

    /** @return array{attempt: ChallengeAttempt, payload: array<string, mixed>} */
    private function openScenario(string $ref = 'tmg-5', int $sessionId = 1): array
    {
        $opened = $this->service->openNode($this->session($sessionId), $this->nodeId($this->conn, $ref));

        return ['attempt' => $opened['attempt'], 'payload' => $opened['payload']];
    }

    private function reload(ChallengeAttempt $attempt): ChallengeAttempt
    {
        return model(ChallengeAttemptModel::class)->find($attempt->id);
    }

    /** @return list<int> scenario item ids of the attempt, in order */
    private function scenarioIds(ChallengeAttempt $attempt): array
    {
        return array_values($attempt->selectedItemIds());
    }

    /** Option key of the first action with $score (0, 1, 2), and of the first reason. */
    private function keys(int $itemId, int $score): array
    {
        $action = null;
        $reason = null;

        foreach (service('contentRepository')->scenarioOptions($itemId) as $option) {
            if ($option['kind'] === 'action' && $action === null && (int) $option['score'] === $score) {
                $action = (string) $option['option_key'];
            }

            if ($option['kind'] === 'reason' && $reason === null) {
                $reason = (string) $option['option_key'];
            }
        }

        return ['action' => $action, 'reason' => $reason];
    }

    /** `{action, reason}` refs for an action with $score, as the browser would send them. */
    private function answer(ChallengeAttempt $attempt, int $itemId, int $score): array
    {
        $keys = $this->keys($itemId, $score);

        return [
            'action' => $this->optionRef($attempt->id, $itemId, 'action', (string) $keys['action']),
            'reason' => $this->optionRef($attempt->id, $itemId, 'reason', (string) $keys['reason']),
        ];
    }

    private function optionRef(int $attemptId, int $itemId, string $kind, string $key): string
    {
        return (string) (new \ReflectionMethod(ChallengeService::class, 'optionRef'))->invoke($this->service, $attemptId, $itemId, $kind, $key);
    }

    /** @return array<string, mixed> */
    private function send(ChallengeAttempt $attempt, int $itemId, int $score, array $meta = []): array
    {
        return $this->service->submitAnswer($this->reload($attempt), $itemId, $this->answer($attempt, $itemId, $score), $meta);
    }

    /** @return list<array<string, mixed>> */
    private function tries(int $attemptId, ?int $itemId = null): array
    {
        $query = $this->conn->table('item_tries')->where('challenge_attempt_id', $attemptId);

        if ($itemId !== null) {
            $query->where('challenge_item_id', $itemId);
        }

        return $query->orderBy('id')->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> payload of every event of $type */
    private function events(string $type): array
    {
        $rows = $this->conn->table('game_event_logs')->where('event_type', $type)->orderBy('id')->get()->getResultArray();

        return array_map(static fn (array $row): array => json_decode((string) $row['payload_json'], true), $rows);
    }
}
