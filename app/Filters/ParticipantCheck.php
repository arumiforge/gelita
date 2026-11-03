<?php

namespace App\Filters;

/**
 * Pemeriksaan bersama untuk ParticipantAuthFilter, GameSessionFilter, dan ApiSessionFilter.
 * Satu tempat, sehingga ketiga filter selalu memakai aturan yang sama.
 */
trait ParticipantCheck
{
    /**
     * @return array{status: string, participant_id: int, game_session_id: int, target?: string}
     *               status: OK | NO_LOGIN | MUST_CHANGE | NO_SESSION | CLOSED. CLOSED (field
     *               mode, 28 §7) carries `target`: `token` (no game session of a field session
     *               yet) or `sampai-jumpa` (its field session closed); the game context is loaded
     *               whenever the student has a game session.
     */
    protected function checkParticipant(bool $requireGameSession): array
    {
        $participantId = (int) session('participant_id');
        $result        = ['status' => 'NO_LOGIN', 'participant_id' => $participantId, 'game_session_id' => 0];

        // 1. belum login
        if ($participantId <= 0) {
            return $result;
        }

        // 2. peserta tidak ada / terhapus (soft delete)
        $participant = db_connect()->table('participants')
            ->select('id, must_change_password')
            ->where('id', $participantId)
            ->where('deleted_at', null)
            ->get()
            ->getRow();

        if ($participant === null) {
            session()->destroy();

            return $result;
        }

        if (! $requireGameSession) {
            return ['status' => 'OK'] + $result;
        }

        // 3. sandi wajib diganti setelah reset guru
        if ((int) $participant->must_change_password === 1) {
            return ['status' => 'MUST_CHANGE'] + $result;
        }

        // 4. sesi permainan harus ada, milik peserta ini, dan belum ditinggalkan
        $gameSessionId = (int) session('game_session_id');
        $gameSession   = null;

        if ($gameSessionId > 0) {
            $gameSession = db_connect()->table('game_sessions')
                ->select('id, status')
                ->where('id', $gameSessionId)
                ->where('participant_id', $participantId)
                ->get()
                ->getRow();
        }

        $fields = service('fieldSessions');

        if ($gameSession === null || $gameSession->status === 'abandoned') {
            return $fields->fieldModeOn() ? ['status' => 'CLOSED', 'target' => 'token'] + $result : ['status' => 'NO_SESSION'] + $result;
        }

        // 5. muat konteks permainan untuk request ini
        if (! service('gameContext')->load($participantId, $gameSessionId)) {
            return ['status' => 'NO_SESSION'] + $result;
        }

        // 6. field mode: the game only opens inside an open field session (28 §7)
        if ($fields->fieldModeOn()) {
            $session = service('gameContext')->session;
            $closed  = ['status' => 'CLOSED', 'participant_id' => $participantId, 'game_session_id' => $gameSessionId];

            if ($session->field_session_id === null) {
                return $closed + ['target' => 'token'];
            }

            if (! $fields->sessionIsOpen($session)) {
                // First request after the closing time writes the pause once (28 §6.6)
                $fields->pauseForClosedSession($session);

                return $closed + ['target' => 'sampai-jumpa'];
            }
        }

        return ['status' => 'OK', 'participant_id' => $participantId, 'game_session_id' => $gameSessionId];
    }
}
