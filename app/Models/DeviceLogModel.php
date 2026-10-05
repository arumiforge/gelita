<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

/**
 * Perangkat siswa per daftar/masuk/lanjut sesi (Dok 1 §5.6, §11). Isinya
 * hasil App\Libraries\DeviceProfile::capture(). Hanya ditambah.
 */
class DeviceLogModel extends Model
{
    use EncodesJson;

    public const VIA = ['register', 'login', 'resume'];

    protected $table         = 'device_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'session_id', 'participant_id', 'via', 'user_agent',
        'device_type', 'device_brand', 'device_model', 'os_name', 'os_version', 'os_platform',
        'browser_name', 'browser_version', 'browser_engine', 'is_bot', 'client_hints_json',
        'screen_w', 'screen_h', 'viewport_w', 'viewport_h', 'pixel_ratio', 'touch_points',
        'orientation', 'is_standalone', 'language', 'timezone', 'corrected', 'detector', 'ip_hash',
    ];
    protected $validationRules = [
        'session_id'     => 'required|is_natural_no_zero',
        'participant_id' => 'required|is_natural_no_zero',
        'via'            => 'required|in_list[register,login,resume]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['client_hints_json'];

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
