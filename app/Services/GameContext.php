<?php

namespace App\Services;

use App\Entities\GameSession;
use App\Entities\Participant;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use App\Models\ParticipantProgressModel;

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
        // Progress follows the student across game sessions (28 §8)
        $this->progress    = model(ParticipantProgressModel::class)->forSession($session);

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
            $this->progress = model(ParticipantProgressModel::class)->forSession($this->session);
        }
    }

    /** Memuat ulang baris sesi setelah service lain mengubahnya. */
    public function refreshSession(): void
    {
        if ($this->session !== null) {
            $this->session = model(GameSessionModel::class)->find($this->session->id);
        }
    }

    // Part 7 (33 K1): the one place that decides research mode in the game

    /**
     * Research mode for a game session: the active study has field mode on
     * and the session belongs to that study (33 K1). Hidden clock, refused
     * small screens, retake and Kenali rules follow it; raw data never does (K2).
     */
    public function researchMode(?GameSession $session = null): bool
    {
        $session ??= $this->session;
        $study     = service('fieldSessions')->fieldModeStudy();

        return $study !== null && $session !== null && (int) $session->study_id === (int) $study['id'];
    }

    /**
     * Research settings of the field-mode study (33 §6), defaults when a
     * column is missing.
     *
     * @return array{idle_threshold_ms: int, rapid_guess_ms: int, min_stage_px: int, test_rapid_guess_ms: int}
     */
    public static function studySettings(?array $study): array
    {
        return [
            'idle_threshold_ms'   => (int) ($study['idle_threshold_ms'] ?? 180000),
            'rapid_guess_ms'      => (int) ($study['rapid_guess_ms'] ?? 3000),
            'min_stage_px'        => (int) ($study['min_stage_px'] ?? 960),
            'test_rapid_guess_ms' => (int) ($study['test_rapid_guess_ms'] ?? 3000),
        ];
    }

    public function reset(): void
    {
        $this->session     = null;
        $this->progress    = null;
        $this->participant = null;
    }
}
