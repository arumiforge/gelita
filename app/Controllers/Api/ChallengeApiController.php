<?php

namespace App\Controllers\Api;

use App\Entities\ItemResponse;
use App\Models\ChallengeAttemptModel;
use App\Models\ItemResponseModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Inti permainan dari sisi HTTP: membuka node, menerima jawaban, menutup attempt.
 *
 * Tidak ada penilaian di sini. Seluruh benar/salah, skor, dan status level
 * ditentukan ChallengeService memakai kunci dari database; field `correct`
 * yang dikirim browser tidak pernah dipakai.
 *
 * Urutan pembukaan node di dalam satu wilayah ditegakkan layar HTML
 * (ChallengeController); di API yang ditegakkan adalah kunci level, sama
 * seperti ChallengeService::openNode().
 */
class ChallengeApiController extends BaseApiController
{
    public function open(int $nodeId): ResponseInterface
    {
        if ($limited = $this->rateLimited('attempts')) {
            return $limited;
        }

        $node = service('contentRepository')->node($nodeId);

        if ($node === null) {
            return $this->fail('NOT_FOUND', lang('Game.errNotFound'), 404);
        }

        $session = $this->gameSession();
        $level   = service('contentRepository')->levelById($node->level_id);

        if ($level === null || ! $this->levelUnlocked($session, $level->sequence)) {
            return $this->fail('LEVEL_LOCKED', lang('Game.levelLocked'), 409);
        }

        $opened = service('challengeService')->openNode($session, $nodeId);

        return $this->ok($opened['payload']);
    }

    public function respond(int $attemptId): ResponseInterface
    {
        if ($limited = $this->rateLimited('attempts')) {
            return $limited;
        }

        $attempt = $this->attemptOrFail($attemptId);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        $body = $this->jsonBody();
        $data = [
            'attempt_id'      => $attempt->id,
            'item_id'         => $body['item_id'] ?? null,
            'client_event_id' => $body['client_event_id'] ?? null,
        ];

        if (! $this->validateData($data, [
            // parameter [attempt_id] menunjuk field yang memuat id attempt di $data
            'item_id'         => 'required|is_natural_no_zero|item_in_attempt[attempt_id]',
            'client_event_id' => 'required|max_length[120]',
        ])) {
            return $this->invalidPayload($this->validator->getErrors(), 'INVALID_RESPONSE');
        }

        if (! isset($body['answer']) || ! is_array($body['answer'])) {
            return $this->invalidPayload(['answer' => lang('Game.errInvalidPayload')], 'INVALID_RESPONSE');
        }

        $itemId = (int) $data['item_id'];

        try {
            $result = service('challengeService')->submitAnswer($attempt, $itemId, $body['answer'], [
                'duration_ms' => $body['duration_ms'] ?? null,
                'reason_text' => $body['reason_text'] ?? null,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->invalidPayload(['item_id' => $e->getMessage()], 'INVALID_RESPONSE');
        }

        $response = model(ItemResponseModel::class)->findOne($attempt->id, $itemId);

        return $this->ok([
            'correct'      => $result['correct'],
            'first_pass'   => $result['first_pass'],
            'wrong_click'  => $result['wrong_click'],
            'feedback'     => $result['feedback'],
            'change_count' => $response?->change_count ?? 0,
            'progress'     => $result['progress'],
        ]);
    }

    public function check(int $attemptId): ResponseInterface
    {
        if ($limited = $this->rateLimited('attempts')) {
            return $limited;
        }

        $attempt = $this->attemptOrFail($attemptId);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        $body    = $this->jsonBody();
        $answers = $body['answers'] ?? null;

        if (! is_array($answers) || $answers === []) {
            return $this->invalidPayload(['answers' => lang('Game.errInvalidPayload')], 'INVALID_RESPONSE');
        }

        $map = [];

        foreach ($answers as $row) {
            if (! is_array($row) || ! isset($row['item_id']) || ! isset($row['answer']) || ! is_array($row['answer'])) {
                return $this->invalidPayload(['answers' => lang('Game.errInvalidPayload')], 'INVALID_RESPONSE');
            }

            $itemId = (int) $row['item_id'];

            // setiap item wajib milik attempt ini, bukan sekadar ada di database
            if (! $attempt->hasItem($itemId)) {
                return $this->invalidPayload(
                    ['item_id' => lang('Validation.item_in_attempt')],
                    'INVALID_RESPONSE',
                );
            }

            $map[$itemId] = $row['answer'] + [
                'duration_ms' => $row['duration_ms'] ?? null,
                'reason_text' => $row['reason_text'] ?? null,
            ];
        }

        $result = service('challengeService')->submitCheck($attempt, $map);

        return $this->ok([
            'results'       => $result['results'],
            'correct_count' => $result['correct_count'],
            'total'         => $result['total'],
            'all_correct'   => $result['all_correct'],
            'check_count'   => $result['check_count'],
            'retry_count'   => $result['retry_count'],
        ]);
    }

    public function hint(int $attemptId): ResponseInterface
    {
        if ($limited = $this->rateLimited('attempts')) {
            return $limited;
        }

        $attempt = $this->attemptOrFail($attemptId);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        $body   = $this->jsonBody();
        $hintId = (int) ($body['hint_id'] ?? 0);
        $itemId = isset($body['item_id']) ? (int) $body['item_id'] : null;

        if ($hintId <= 0) {
            return $this->invalidPayload(['hint_id' => lang('Game.errInvalidPayload')]);
        }

        if ($itemId !== null && ! $attempt->hasItem($itemId)) {
            return $this->invalidPayload(['item_id' => lang('Validation.item_in_attempt')], 'INVALID_RESPONSE');
        }

        try {
            $result = service('challengeService')->useHint($attempt, $hintId, $itemId);
        } catch (\InvalidArgumentException) {
            return $this->fail('NOT_FOUND', lang('Game.errNotFound'), 404);
        }

        return $this->ok(['text' => $result['text'], 'hint_count' => $result['hint_count']]);
    }

    public function complete(int $attemptId): ResponseInterface
    {
        $attempt = $this->attemptOrFail($attemptId);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        $pending = $this->pendingItems($attempt->id);

        if ($pending !== []) {
            return $this->invalidPayload([
                'items' => lang('Game.errItemsPending', [count($pending)]),
            ]);
        }

        $result = service('challengeService')->completeAttempt($attempt);
        $closed = model(ChallengeAttemptModel::class)->find($attempt->id);

        return $this->ok([
            'score'               => $result['score'],
            'stars'               => $result['stars'],
            'first_pass_accuracy' => $result['first_pass_accuracy'],
            'final_accuracy'      => $result['final_accuracy'],
            'duration_ms'         => (int) ($closed?->duration_ms ?? 0),
            'shards'              => $result['shards'],
            'level_completed'     => $result['level_completed'],
            'next_level_unlocked' => $result['next_level_unlocked'],
            'redirect'            => site_url('selesai/' . $attempt->id),
        ]);
    }

    public function abandon(int $attemptId): ResponseInterface
    {
        $attempt = $this->attemptOrFail($attemptId, false);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        service('challengeService')->abandonAttempt($attempt);

        // attempt yang sudah selesai tidak diubah; laporkan status sebenarnya
        $current = model(ChallengeAttemptModel::class)->find($attempt->id);

        return $this->ok(['status' => (string) ($current?->status ?? 'abandoned')]);
    }

    /** @return list<int> id item yang belum dijawab dan belum dilewati */
    private function pendingItems(int $attemptId): array
    {
        $pending = [];

        foreach (model(ItemResponseModel::class)->forAttempt($attemptId) as $itemId => $response) {
            if (! $this->settled($response)) {
                $pending[] = (int) $itemId;
            }
        }

        return $pending;
    }

    private function settled(ItemResponse $response): bool
    {
        return $response->isAnswered() || $response->status === 'skipped';
    }
}
