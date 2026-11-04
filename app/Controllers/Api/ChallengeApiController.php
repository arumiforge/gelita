<?php

namespace App\Controllers\Api;

use App\Exceptions\ChallengeRuleException;
use App\Models\ChallengeAttemptModel;
use App\Models\ItemResponseModel;
use App\Models\GameSessionModel;
use App\Models\ParticipantProgressModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Inti permainan dari sisi HTTP: membuka node, menerima jawaban, menutup attempt.
 *
 * Tidak ada penilaian di sini. Seluruh benar/salah, bintang, dan status level
 * ditentukan ChallengeService memakai kunci dari database; field `correct`
 * yang dikirim browser tidak pernah dipakai.
 *
 * Urutan pembukaan node di dalam satu wilayah ditegakkan layar HTML
 * (ChallengeController) dan juga ChallengeService::openNode() (NODE_LOCKED,
 * 28 K17), bersama kunci level.
 *
 * Aturan 3 kesempatan (Dok 2 §5): /check dan /responses menutup pengerjaan
 * sendiri bila semua benar atau kesempatan habis (`completed`, `redirect`).
 * Pelanggaran aturan dibalas 409 (TRIES_EXHAUSTED, CLUE_CLOSED,
 * TRIES_REMAINING) atau 422 (INCOMPLETE).
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

        try {
            $opened = service('challengeService')->openNode($session, $nodeId);
        } catch (ChallengeRuleException $e) {
            return $this->ruleFailed($e);   // NODE_LOCKED: challenges in a region open in order (28 K17)
        }

        // Pengerjaan lama ternyata sudah tuntas dan baru saja ditutup
        if (isset($opened['redirect'])) {
            return $this->ok(['completed' => true, 'attempt_id' => $opened['attempt']->id, 'redirect' => $opened['redirect']]);
        }

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
                'ready_ms'      => $body['ready_ms'] ?? null,
                'last_input_ms' => $body['last_input_ms'] ?? null,
            ]);
        } catch (ChallengeRuleException $e) {
            return $this->ruleFailed($e);
        } catch (\InvalidArgumentException $e) {
            return $this->invalidPayload(['item_id' => $e->getMessage()], 'INVALID_RESPONSE');
        }

        $response = model(ItemResponseModel::class)->findOne($attempt->id, $itemId);

        return $this->ok([
            'correct'           => $result['correct'],
            'first_pass'        => $result['first_pass'],
            'wrong_click'       => $result['wrong_click'],
            'decoy'             => $result['decoy'],
            'feedback'          => $result['feedback'],
            'try_no'            => $result['try_no'],
            'tries_left'        => $result['tries_left'],
            'clue_closed'       => $result['clue_closed'],
            'change_count'      => $response?->change_count ?? 0,
            'wrong_click_count' => $response?->wrong_click_count ?? 0,
            'progress'          => $result['progress'],
            'completed'         => $result['completed'],
            'completion_reason' => $result['completion_reason'],
            'redirect'          => $result['redirect'],
            'shards'            => $result['shards'],
        ]);
    }

    public function check(int $attemptId): ResponseInterface
    {
        if ($limited = $this->rateLimited('attempts')) {
            return $limited;
        }

        $attempt = $this->attemptOrFail($attemptId, false);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        // Cek ke-(max_tries + 1) ditolak TRIES_EXHAUSTED, juga ketika
        // pengerjaannya sudah ditutup otomatis pada cek ke-3 (Dok 2 §5.2).
        if ($attempt->isExhausted()) {
            return $this->ruleFailed(ChallengeRuleException::triesExhausted(
                $attempt->status === 'completed' ? site_url('selesai/' . $attempt->id) : null,
            ));
        }

        if (! $attempt->isInProgress()) {
            return $this->fail('ATTEMPT_CLOSED', lang('Game.errAttemptClosed'), 409);
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

            // waktu isian per soal (ms sejak tantangan dibuka) — catatan proses dari klien
            $map[$itemId] = $row['answer'] + [
                'first_input_ms' => $row['first_input_ms'] ?? null,
                'last_input_ms'  => $row['last_input_ms'] ?? null,
                'ready_ms'       => $row['ready_ms'] ?? null,
            ];
        }

        // Nomor cek menurut layar klien (opsional): cek dari tampilan usang ditolak ATTEMPT_CHANGED
        $checkNo = isset($body['check_no']) && is_numeric($body['check_no']) ? (int) $body['check_no'] : null;

        try {
            $result = service('challengeService')->submitCheck($attempt, $map, $checkNo);
        } catch (ChallengeRuleException $e) {
            return $this->ruleFailed($e);
        } catch (\InvalidArgumentException $e) {
            return $this->invalidPayload(['answers' => $e->getMessage()], 'INVALID_RESPONSE');
        }

        return $this->ok([
            'results'           => (object) $result['results'],
            'correct_count'     => $result['correct_count'],
            'total'             => $result['total'],
            'all_correct'       => $result['all_correct'],
            'check_count'       => $result['check_count'],
            'try_no'            => $result['try_no'],
            'tries_left'        => $result['tries_left'],
            'exhausted'         => $result['exhausted'],
            // pieces_correct + misplaced untuk Susun Gambar — dihitung server dari kunci
            'detail'            => (object) $result['detail'],
            'completed'         => $result['completed'],
            'completion_reason' => $result['completion_reason'],
            'redirect'          => $result['redirect'],
            'shards'            => $result['shards'],
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

    /**
     * Kompatibilitas (Dok 2 §5.2): pengerjaan biasanya sudah ditutup oleh
     * /check atau /responses. Idempoten — pengerjaan yang sudah selesai
     * dibalas hasil dan `redirect` yang sama. Pengerjaan yang masih berjalan
     * hanya ditutup bila semua benar atau kesempatannya memang habis.
     */
    public function complete(int $attemptId): ResponseInterface
    {
        $attempt = $this->attemptOrFail($attemptId, false);

        if ($attempt instanceof ResponseInterface) {
            return $attempt;
        }

        if ($attempt->status === 'abandoned') {
            return $this->fail('ATTEMPT_CLOSED', lang('Game.errAttemptClosed'), 409);
        }

        if ($attempt->isInProgress()) {
            try {
                service('challengeService')->completeAttempt($attempt);
            } catch (ChallengeRuleException $e) {
                return $this->ruleFailed($e);
            }
        }

        $closed = model(ChallengeAttemptModel::class)->find($attempt->id);

        return $this->ok([
            'stars'              => (int) $closed->stars,
            'first_pass_correct' => (int) $closed->first_pass_correct,
            'final_correct'      => (int) $closed->final_correct,
            'scorable_items'     => (int) $closed->scorable_items,
            'completion_reason'  => $closed->completion_reason,
            'duration_ms'        => (int) ($closed->duration_ms ?? 0),
            'shards'             => (int) (model(ParticipantProgressModel::class)->forSession(model(GameSessionModel::class)->find($attempt->session_id))['completed_nodes'] ?? 0),
            'redirect'           => site_url('selesai/' . $attempt->id),
        ]);
    }

    /** Pelanggaran aturan permainan → respons gagal dengan kode dan status dari pengecualiannya. */
    private function ruleFailed(ChallengeRuleException $e): ResponseInterface
    {
        $message = match ($e->errorCode) {
            'INCOMPLETE'      => lang('Game.errIncomplete'),
            'TRIES_EXHAUSTED' => lang('Game.errTriesExhausted'),
            'CLUE_CLOSED'     => lang('Game.errClueClosed'),
            'TRIES_REMAINING' => lang('Game.errTriesRemaining'),
            'ATTEMPT_CHANGED' => lang('Game.errAttemptChanged'),
            'BOOKLET_MISSING' => lang('Game.bookletMissing'),
            default           => $e->getMessage(),
        };

        return $this->fail($e->errorCode, $message, $e->status, $e->extra);
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
}
