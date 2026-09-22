<?php

namespace App\Filters;

/**
 * Pemeriksaan bersama untuk ParticipantAuthFilter, GameSessionFilter, dan ApiSessionFilter.
 * Satu tempat, sehingga ketiga filter selalu memakai aturan yang sama.
 */
trait ParticipantCheck
{
    /**
     * @return array{status: string, participant_id: int, game_session_id: int}
     *               status: OK | NO_LOGIN | MUST_CHANGE | NO_SESSION
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

        if ($gameSession === null || $gameSession->status === 'abandoned') {
            return ['status' => 'NO_SESSION'] + $result;
        }

        // 5. muat konteks permainan untuk request ini
        if (! service('gameContext')->load($participantId, $gameSessionId)) {
            return ['status' => 'NO_SESSION'] + $result;
        }

        return ['status' => 'OK', 'participant_id' => $participantId, 'game_session_id' => $gameSessionId];
    }
}
