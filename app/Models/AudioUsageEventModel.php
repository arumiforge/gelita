<?php

namespace App\Models;

use CodeIgniter\Model;

class AudioUsageEventModel extends Model
{
    /**
     * Aksi telemetry audio. `play` = pemain menekan tombol putar; `autoplay`
     * = narasi diputar otomatis oleh layar bernarasi (ketuk-untuk-mulai,
     * maju otomatis). Keduanya membuka pemutaran baru, tetapi dihitung
     * terpisah agar peneliti dapat membedakan pilihan pemain dari putar
     * otomatis. `replay` = pemain memutar ulang dari awal.
     */
    public const ACTIONS = ['play', 'autoplay', 'pause', 'replay', 'complete'];

    protected $table         = 'audio_usage_events';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'session_id', 'challenge_attempt_id', 'audio_asset_id', 'action',
        'play_index', 'listened_ms', 'completed', 'occurred_at', 'server_received_at',
    ];
    protected $validationRules = [
        'session_id'     => 'required|is_natural_no_zero',
        'audio_asset_id' => 'required|is_natural_no_zero',
        'action'         => 'required|in_list[play,autoplay,pause,replay,complete]',
    ];

    /** @return list<array<string, mixed>> */
    public function usageForSession(int $sessionId): array
    {
        return $this->where('session_id', $sessionId)
            ->orderBy('occurred_at', 'ASC')
            ->findAll();
    }

    /**
     * Nomor pemutaran ke-n untuk aset ini dalam sesi (01_DATABASE: `play_index`
     * = "pemutaran ke-n untuk aset itu").
     *
     * `play`, `autoplay`, dan `replay` membuka pemutaran baru; `pause` dan
     * `complete` termasuk pemutaran yang sedang berjalan. Klien tidak mengirim
     * `play` saat melanjutkan dari jeda, jadi `play` selalu berarti mulai dari awal.
     * Nomor dihitung server — hitungan klien dimulai ulang setiap halaman.
     */
    public function nextPlayIndex(int $sessionId, int $audioAssetId, string $action = 'play'): int
    {
        $row = $this->db->table($this->table)
            ->selectMax('play_index', 'max_index')
            ->where('session_id', $sessionId)
            ->where('audio_asset_id', $audioAssetId)
            ->get()
            ->getRowArray();

        $current = (int) ($row['max_index'] ?? 0);

        return in_array($action, ['play', 'autoplay', 'replay'], true) ? $current + 1 : max(1, $current);
    }

    /**
     * `total_plays` hanya menghitung putar manual (`play`); putar otomatis
     * layar bernarasi ada di `total_autoplays`.
     *
     * @return array{sessions_with_audio: int, total_plays: int, total_autoplays: int,
     *               total_replays: int, completion_rate: float, mean_listened_ms: int}
     */
    public function usageStats(array $filters = []): array
    {
        $row = $this->statsBuilder($filters)
            ->select('COUNT(DISTINCT aue.session_id) AS sessions_with_audio', false)
            ->select("SUM(CASE WHEN aue.action = 'play' THEN 1 ELSE 0 END) AS total_plays", false)
            ->select("SUM(CASE WHEN aue.action = 'autoplay' THEN 1 ELSE 0 END) AS total_autoplays", false)
            ->select("SUM(CASE WHEN aue.action = 'replay' THEN 1 ELSE 0 END) AS total_replays", false)
            ->select('SUM(aue.completed) AS completed', false)
            ->select('COUNT(*) AS total_events', false)
            ->select('AVG(aue.listened_ms) AS mean_listened_ms', false)
            ->get()
            ->getRowArray();

        $totalEvents = (int) ($row['total_events'] ?? 0);

        return [
            'sessions_with_audio' => (int) ($row['sessions_with_audio'] ?? 0),
            'total_plays'         => (int) ($row['total_plays'] ?? 0),
            'total_autoplays'     => (int) ($row['total_autoplays'] ?? 0),
            'total_replays'       => (int) ($row['total_replays'] ?? 0),
            'completion_rate'     => $totalEvents > 0 ? round((int) ($row['completed'] ?? 0) / $totalEvents, 4) : 0.0,
            'mean_listened_ms'    => (int) round((float) ($row['mean_listened_ms'] ?? 0)),
        ];
    }

    private function statsBuilder(array $filters): \CodeIgniter\Database\BaseBuilder
    {
        $builder = $this->db->table('audio_usage_events aue')
            ->join('game_sessions', 'game_sessions.id = aue.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters, ['level_id', 'node_id']);
    }
}
