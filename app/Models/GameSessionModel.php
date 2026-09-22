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
        'session_code', 'participant_id', 'study_id', 'phase_id', 'release_id',
        'locale', 'status', 'started_at', 'last_active_at', 'ended_at', 'duration_ms',
        'last_level_id', 'last_challenge_node_id',
        'device_type', 'os_name', 'browser_name', 'screen_size', 'is_touch',
        'app_client_version', 'ip_hash',
    ];
    protected $validationRules = [
        'session_code'   => 'required|max_length[64]|is_unique[game_sessions.session_code,id,{id}]',
        'participant_id' => 'required|is_natural_no_zero',
        'study_id'       => 'required|is_natural_no_zero',
        'phase_id'       => 'required|is_natural_no_zero',
        'release_id'     => 'required|is_natural_no_zero',
        'locale'         => 'required|valid_locale',
        'status'         => 'permit_empty|in_list[active,paused,completed,abandoned]',
    ];

    public function findByCode(string $sessionCode): ?GameSession
    {
        return $this->where('session_code', trim($sessionCode))->first();
    }

    /** Sesi yang masih dapat dilanjutkan pada fase tertentu */
    public function resumable(int $participantId, int $studyId, int $phaseId): ?GameSession
    {
        return $this->where('participant_id', $participantId)
            ->where('study_id', $studyId)
            ->where('phase_id', $phaseId)
            ->whereIn('status', ['active', 'paused'])
            ->orderBy('last_active_at', 'DESC')
            ->first();
    }

    public function latestCompleted(int $participantId, int $studyId, int $phaseId): ?GameSession
    {
        return $this->where('participant_id', $participantId)
            ->where('study_id', $studyId)
            ->where('phase_id', $phaseId)
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
