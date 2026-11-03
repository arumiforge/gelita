<?php

namespace App\Models;

use App\Entities\GameSession;
use CodeIgniter\Model;

class GameSessionModel extends Model
{
    protected $table         = 'game_sessions';
    protected $primaryKey    = 'id';
    protected $returnType    = GameSession::class;
    protected $useTimestamps = false;
    protected $allowedFields = [
        'session_code', 'participant_id', 'study_id', 'field_session_id', 'meeting_no', 'release_id',
        'locale', 'status', 'started_at', 'last_active_at', 'ended_at', 'duration_ms',
        'last_level_id', 'last_challenge_node_id',
        'device_type', 'os_name', 'browser_name', 'screen_size', 'is_touch',
        'app_client_version', 'package_code', 'package_mode', 'ip_hash',
    ];
    protected $validationRules = [
        'session_code'   => 'required|max_length[64]|is_unique[game_sessions.session_code,id,{id}]',
        'participant_id' => 'required|is_natural_no_zero',
        'study_id'       => 'required|is_natural_no_zero',
        'release_id'     => 'required|is_natural_no_zero',
        'locale'         => 'required|valid_locale',
        'status'         => 'permit_empty|in_list[active,paused,completed,abandoned]',
        'package_mode'   => 'permit_empty|in_list[lengkap,daring,nonaktif]',
    ];

    public function findByCode(string $sessionCode): ?GameSession
    {
        return $this->where('session_code', trim($sessionCode))->first();
    }

    /**
     * Sesi yang masih dapat dilanjutkan (satu tahap bermain per studi). In
     * field mode only a session whose field session is open right now
     * (research revision 10, part 2: 28 §6.4, §6.6).
     */
    public function resumable(int $participantId, int $studyId): ?GameSession
    {
        $this->select('game_sessions.*')
            ->where('game_sessions.participant_id', $participantId)
            ->where('game_sessions.study_id', $studyId)
            ->whereIn('game_sessions.status', ['active', 'paused']);

        if (service('fieldSessions')->fieldModeOn()) {
            $now = date('Y-m-d H:i:s');

            $this->join('field_sessions', 'field_sessions.id = game_sessions.field_session_id')
                ->where('game_sessions.ended_at', null)
                ->where('field_sessions.closed_at', null)
                ->where('field_sessions.opens_at <=', $now)
                ->where('field_sessions.closes_at >', $now);
        }

        return $this->orderBy('game_sessions.last_active_at', 'DESC')->first();
    }

    /** The student's game session in one field session that can still be resumed (K7). */
    public function inFieldSession(int $participantId, int $fieldSessionId): ?GameSession
    {
        return $this->where('participant_id', $participantId)
            ->where('field_session_id', $fieldSessionId)
            ->whereIn('status', ['active', 'paused', 'completed'])
            ->where('ended_at', null)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function latestCompleted(int $participantId, int $studyId): ?GameSession
    {
        return $this->where('participant_id', $participantId)
            ->where('study_id', $studyId)
            ->where('status', 'completed')
            ->orderBy('ended_at', 'DESC')
            ->first();
    }

    /** Memperbarui last_active_at tanpa menyentuh kolom lain. */
    public function touch(int $sessionId): void
    {
        $this->db->table($this->table)
            ->where('id', $sessionId)
            ->update(['last_active_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Sesi `active` yang tidak aktif lebih dari $idleMinutes → `paused`.
     *
     * @return int jumlah baris yang berubah
     */
    public function markStale(int $idleMinutes): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($idleMinutes * 60));

        $this->db->table($this->table)
            ->where('status', 'active')
            ->where('last_active_at <', $cutoff)
            ->update(['status' => 'paused']);

        return $this->db->affectedRows();
    }
}
