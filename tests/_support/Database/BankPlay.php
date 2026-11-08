<?php

namespace Tests\Support\Database;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;

/**
 * Helpers for the bank tests of research revision 10, part 5 (31 §19.1) on
 * top of GradeFiveWorld: open a challenge, find its round-1 and round-2
 * clues, and build the opaque refs the server expects for a tap.
 *
 * The using class sets $conn and $service in setUp().
 */
trait BankPlay
{
    use GradeFiveWorld;

    private BaseConnection $conn;
    private ChallengeService $service;

    private function session(int $id = 1): GameSession
    {
        return model(GameSessionModel::class)->find($id);
    }

    private function open(string $ref, int $sessionId = 1): ChallengeAttempt
    {
        $nodeId = $this->nodeId($this->conn, $ref);
        $this->service->openNode($this->session($sessionId), $nodeId);

        return model(ChallengeAttemptModel::class)->currentInProgress($sessionId, $nodeId);
    }

    private function reload(ChallengeAttempt $attempt): ChallengeAttempt
    {
        return model(ChallengeAttemptModel::class)->find($attempt->id);
    }

    /** @return list<ChallengeItem> items of the attempt in selection order */
    private function attemptItems(ChallengeAttempt $attempt): array
    {
        $bank = [];

        foreach (service('contentRepository')->itemBank($attempt->challenge_node_id) as $item) {
            $bank[$item->id] = $item;
        }

        return array_values(array_filter(array_map(static fn (int $id): ?ChallengeItem => $bank[$id] ?? null, $attempt->selectedItemIds())));
    }

    /** @return list<ChallengeItem> */
    private function itemsOfType(ChallengeAttempt $attempt, string $type, bool $decoys = false): array
    {
        return array_values(array_filter($this->attemptItems($attempt), static fn (ChallengeItem $i): bool => $i->interaction_type === $type && $i->isDecoy() === $decoys));
    }

    private function item(string $key): ChallengeItem
    {
        return model(\App\Models\ChallengeItemModel::class)->findByKey($key);
    }

    private function objectRef(ChallengeAttempt $attempt, int $itemId): string
    {
        return (string) (new \ReflectionMethod(ChallengeService::class, 'objectRef'))->invoke($this->service, $attempt->id, $itemId);
    }

    /** Ref of one post part of the attempt's variant, by its part_key. */
    private function partRef(ChallengeAttempt $attempt, string $partKey): string
    {
        foreach (service('contentRepository')->postScene($attempt->challenge_node_id, (string) $attempt->variant_group)['parts'] as $part) {
            if ($part['part_key'] === $partKey) {
                return (string) (new \ReflectionMethod(ChallengeService::class, 'partRef'))->invoke($this->service, $attempt->id, (int) $part['id']);
            }
        }

        $this->fail("Part {$partKey} not found.");
    }

    private function tapObject(ChallengeAttempt $attempt, ChallengeItem $clue, ChallengeItem $tapped): array
    {
        return $this->service->submitAnswer($this->reload($attempt), $clue->id, ['object' => $this->objectRef($attempt, $tapped->id)], ['last_input_ms' => 800]);
    }

    private function tapPart(ChallengeAttempt $attempt, ChallengeItem $clue, string $partKey): array
    {
        return $this->service->submitAnswer($this->reload($attempt), $clue->id, ['object' => $this->partRef($attempt, $partKey)], ['last_input_ms' => 800]);
    }

    /** Every round-1 clue found on the first tap. */
    private function solveRoundOne(ChallengeAttempt $attempt): void
    {
        foreach ($this->itemsOfType($attempt, 'find_object') as $clue) {
            $this->tapObject($attempt, $clue, $clue);
        }
    }

    /** @return list<array<string, mixed>> item_tries rows of the attempt, oldest first */
    private function triesOf(ChallengeAttempt $attempt): array
    {
        return $this->conn->table('item_tries')->where('challenge_attempt_id', $attempt->id)->orderBy('id')->get()->getResultArray();
    }

    private function newStudent(int $n): GameSession
    {
        $this->conn->table('participants')->insert([
            'participant_code' => sprintf('GLT-%06d', $n), 'username' => 'siswa' . $n, 'password_hash' => 'x', 'display_name' => 'Siswa ' . $n,
        ]);
        $participantId = (int) $this->conn->insertID();

        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-' . $n, 'participant_id' => $participantId, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);

        return model(GameSessionModel::class)->find((int) $this->conn->insertID());
    }
}
