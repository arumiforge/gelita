<?php

namespace App\Models;

use CodeIgniter\Model;

class SessionProgressModel extends Model
{
    protected $table            = 'session_progress';
    protected $primaryKey       = 'session_id';
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;
    protected $allowedFields    = [
        'session_id', 'current_level_id', 'current_node_id', 'unlocked_level_sequence',
        'completed_nodes', 'completed_levels', 'total_score', 'total_stars',
    ];

    /** Baris progres sesi; dibuat bila belum ada. */
    public function ensure(int $sessionId): array
    {
        $row = $this->find($sessionId);

        if ($row !== null) {
            return $row;
        }

        $this->insert([
            'session_id'              => $sessionId,
            'unlocked_level_sequence' => 1,
            'completed_nodes'         => 0,
            'completed_levels'        => 0,
            'total_score'             => 0,
            'total_stars'             => 0,
        ], false);

        return $this->find($sessionId);
    }
}
