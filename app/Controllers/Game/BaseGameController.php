<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Controllers\Concerns\GameProgress;
use App\Entities\GameSession;
use App\Entities\Participant;
use App\Services\GameContext;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Dasar controller area game: akses sesi/peserta request ini dan data HUD.
 *
 * Route di grup `gameSession` dijamin sudah memuat GameContext oleh filter,
 * sehingga session() dan participant() tidak perlu memeriksa ulang kepemilikan.
 */
abstract class BaseGameController extends BaseController
{
    use GameProgress;

    protected function context(): GameContext
    {
        return service('gameContext');
    }

    protected function session(): GameSession
    {
        $session = $this->context()->session;

        if ($session === null) {
            throw new \RuntimeException('Sesi permainan belum dimuat; route ini harus memakai filter gameSession.');
        }

        return $session;
    }

    protected function participant(): Participant
    {
        $participant = $this->context()->participant;

        if ($participant === null) {
            throw new \RuntimeException('Peserta belum dimuat; route ini harus memakai filter gameSession.');
        }

        return $participant;
    }

    /**
     * Data yang dipakai HUD pada setiap layar dalam permainan.
     *
     * Peserta dikirim dalam bentuk aman (toSafeArray): `password_hash` tidak
     * pernah sampai ke view mana pun (aturan 11, 05_VIEW_UI.md).
     *
     * @return array<string, mixed>
     */
    protected function hudData(): array
    {
        $session = $this->session();

        return [
            'locale'      => $session->resolvedLocale(),
            'participant' => $this->participant()->toSafeArray(),
            'progress'    => $this->progressSummary($session),
            'lantern'     => $this->lanternMap($session),
        ];
    }

    /** Level menurut kode URL; 404 bila tidak ada atau tidak aktif. */
    protected function requireLevel(string $code): \App\Entities\Level
    {
        $level = service('contentRepository')->level($code);

        if ($level === null) {
            throw PageNotFoundException::forPageNotFound("Wilayah '{$code}' tidak ditemukan.");
        }

        return $level;
    }

    /** Node ke-$sequence pada level; 404 bila tidak ada. */
    protected function requireNode(int $levelId, int $sequence): \App\Entities\ChallengeNode
    {
        foreach (service('contentRepository')->nodesForLevel($levelId) as $node) {
            if ($node->sequence === $sequence) {
                return $node;
            }
        }

        throw PageNotFoundException::forPageNotFound("Tantangan ke-{$sequence} tidak ditemukan.");
    }
}
