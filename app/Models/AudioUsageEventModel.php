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

    /** Actions that open a new playback (next `play_index`); see nextPlayIndex(). */
    public const OPENING_ACTIONS = ['play', 'autoplay', 'replay'];

    protected $table         = 'audio_usage_events';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'session_id', 'challenge_attempt_id', 'audio_asset_id', 'action',
        'play_index', 'listened_ms', 'completed', 'occurred_at', 'server_received_at',
        // Part 7 (33 K10)
        'clock_offset_ms',
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

        return in_array($action, self::OPENING_ACTIONS, true) ? $current + 1 : max(1, $current);
    }
}
