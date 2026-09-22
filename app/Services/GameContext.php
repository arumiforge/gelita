<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Entities\Participant;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use App\Models\SessionProgressModel;

/**
 * Wadah state satu request: sesi, progres, dan peserta yang sedang bermain.
 * Tidak memuat business rule — hanya memuat dan menyimpan referensi.
 */
class GameContext
{
    public ?GameSession $session = null;

    /** @var array<string, mixed>|null */
    public ?array $progress = null;

    public ?Participant $participant = null;

    /** Memuat sesi milik peserta; false bila tidak cocok atau sudah ditinggalkan. */
    public function load(int $participantId, int $gameSessionId): bool
    {
        $this->reset();

        if ($participantId <= 0 || $gameSessionId <= 0) {
            return false;
        }

        $session = model(GameSessionModel::class)->find($gameSessionId);

        if ($session === null || $session->participant_id !== $participantId || $session->status === 'abandoned') {
            return false;
        }

        $participant = model(ParticipantModel::class)->find($participantId);

        if ($participant === null) {
            return false;
        }

        $this->session     = $session;
        $this->participant = $participant;
        $this->progress    = model(SessionProgressModel::class)->ensure($session->id);

        return true;
    }

    public function locale(): string
    {
        if ($this->session !== null) {
            return $this->session->resolvedLocale();
        }

        $locale = service('request')->getLocale() ?: 'id';

        return in_array($locale, config('Gelita')->locales, true) ? $locale : 'id';
    }

    public function isReady(): bool
    {
        return $this->session !== null && $this->participant !== null;
    }

    public function refreshProgress(): void
    {
        if ($this->session !== null) {
            $this->progress = model(SessionProgressModel::class)->ensure($this->session->id);
        }
    }

    /** Memuat ulang baris sesi setelah service lain mengubahnya. */
    public function refreshSession(): void
    {
        if ($this->session !== null) {
            $this->session = model(GameSessionModel::class)->find($this->session->id);
        }
    }

    public function reset(): void
    {
        $this->session     = null;
        $this->progress    = null;
        $this->participant = null;
    }
}
