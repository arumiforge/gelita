<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class AuditLogModel extends Model
{
    use EncodesJson;

    protected $table         = 'audit_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'staff_user_id', 'action', 'target_type', 'target_id', 'metadata_json', 'ip_hash', 'occurred_at',
    ];
    protected $validationRules = [
        'action' => 'required|max_length[50]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['metadata_json'];

    /**
     * Mencatat tindakan staf. Kata sandi mentah tidak pernah masuk metadata.
     *
     * @param array{staff_user_id?: ?int, target_type?: ?string, target_id?: ?string,
     *              metadata?: array<string, mixed>} $opts
     */
    public function record(string $action, array $opts = []): void
    {
        $staffId = $opts['staff_user_id'] ?? null;

        if ($staffId === null) {
            $sessionStaffId = (int) session('staff_id');
            $staffId        = $sessionStaffId > 0 ? $sessionStaffId : null;
        }

        $this->insert([
            'staff_user_id' => $staffId,
            'action'        => $action,
            'target_type'   => $opts['target_type'] ?? null,
            'target_id'     => isset($opts['target_id']) ? (string) $opts['target_id'] : null,
            'metadata_json' => $opts['metadata'] ?? null,
            'ip_hash'       => hash_ip(service('request')->getIPAddress()),
            'occurred_at'   => date('Y-m-d H:i:s'),
        ], false);
    }

    /** @return list<array<string, mixed>> */
    public function recent(int $limit = 100): array
    {
        return $this->orderBy('occurred_at', 'DESC')->findAll(max(1, $limit));
    }
}
