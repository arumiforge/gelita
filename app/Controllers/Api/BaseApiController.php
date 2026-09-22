<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Controllers\Concerns\GameProgress;
use App\Entities\ChallengeAttempt;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Dasar seluruh endpoint JSON permainan.
 *
 * Aturan sistem nomor 2: setiap endpoint memeriksa sesi valid → attempt/node
 * milik sesi itu → item milik attempt itu. Pemeriksaan kedua ada di sini
 * supaya seragam dan tidak terlewat di salah satu controller.
 */
abstract class BaseApiController extends BaseController
{
    use GameProgress;

    /** Batas request per menit per sesi untuk endpoint yang ramai. */
    protected const RATE_PER_MINUTE = 120;

    /** Sesi permainan request ini; filter `apiSession` sudah memuatnya. */
    protected function gameSession(): GameSession
    {
        $session = service('gameContext')->session;

        if ($session === null) {
            throw new \RuntimeException('Sesi permainan belum dimuat; route ini harus memakai filter apiSession.');
        }

        return $session;
    }

    /**
     * Attempt milik sesi berjalan.
     *
     * @return ChallengeAttempt|ResponseInterface attempt, atau respons gagal siap kirim
     */
    protected function attemptOrFail(int $attemptId, bool $requireOpen = true): ChallengeAttempt|ResponseInterface
    {
        $attempt = model(ChallengeAttemptModel::class)->find($attemptId);

        if ($attempt === null) {
            return $this->fail('NOT_FOUND', lang('Game.errNotFound'), 404);
        }

        // attempt_id dari URL selalu dicocokkan dengan sesi berjalan
        if ($attempt->session_id !== $this->gameSession()->id) {
            return $this->fail('FORBIDDEN', lang('Game.errForbidden'), 403);
        }

        if ($requireOpen && ! $attempt->isInProgress()) {
            return $this->fail('ATTEMPT_CLOSED', lang('Game.errAttemptClosed'), 409);
        }

        return $attempt;
    }

    /**
     * @param array<string, string> $errors
     */
    protected function invalidPayload(array $errors, string $code = 'INVALID_PAYLOAD'): ResponseInterface
    {
        return $this->fail($code, lang('Game.errInvalidPayload'), 422, ['errors' => $errors]);
    }

    /**
     * Rate limit sederhana per sesi memakai cache CI4, agar satu tab bermasalah
     * tidak membanjiri tabel event.
     *
     * @return ResponseInterface|null respons 429 bila jatah habis
     */
    protected function rateLimited(string $bucket, int $capacity = self::RATE_PER_MINUTE): ?ResponseInterface
    {
        $throttler = service('throttler');
        $key       = 'gelita.' . $bucket . '.' . $this->gameSession()->id;

        if ($throttler->check($key, $capacity, MINUTE)) {
            return null;
        }

        return $this->fail('RATE_LIMITED', lang('Game.errRateLimited'), 429, [
            'retry_after' => $throttler->getTokenTime(),
        ]);
    }

    /** Waktu server dengan mikrodetik dan offset, mis. 2026-09-21T09:14:22.481000+07:00 */
    protected function serverTime(): string
    {
        return (new \DateTimeImmutable('now'))->format('Y-m-d\TH:i:s.uP');
    }
}
