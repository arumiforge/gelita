<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Perangkat siswa per daftar/masuk/lanjut sesi (Dok 1 §5.6, §11). Isinya
 * hasil App\Libraries\DeviceProfile::capture(). Hanya ditambah.
 */
class DeviceLogModel extends Model
{
    public const VIA = ['register', 'login', 'resume'];

    protected $table         = 'device_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    // Device details beyond OS and browser are not stored (revision 10, part 3: 29 §5.5)
    protected $allowedFields = [
        'session_id', 'participant_id', 'via',
        'device_type', 'os_name', 'os_version',
        'browser_name', 'browser_version', 'browser_engine', 'is_bot',
        'screen_w', 'screen_h', 'viewport_w', 'viewport_h',
        'orientation', 'is_standalone', 'corrected', 'detector', 'ip_hash',
    ];
    protected $validationRules = [
        'session_id'     => 'required|is_natural_no_zero',
        'participant_id' => 'required|is_natural_no_zero',
        'via'            => 'required|in_list[register,login,resume]',
    ];

    /**
     * @param array<string, mixed> $profile hasil DeviceProfile::capture()
     */
    public function record(int $sessionId, int $participantId, string $via, array $profile): int
    {
        $row = array_intersect_key($profile, array_flip($this->allowedFields));

        $id = $this->insert([
            'session_id'     => $sessionId,
            'participant_id' => $participantId,
            'via'            => in_array($via, self::VIA, true) ? $via : 'login',
        ] + $row, true);

        return (int) $id;
    }

    /** @return list<array<string, mixed>> */
    public function forParticipant(int $participantId): array
    {
        return $this->where('participant_id', $participantId)->orderBy('created_at', 'ASC')->orderBy('id', 'ASC')->findAll();
    }
}
