<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class DataDeletionRequestModel extends Model
{
    use EncodesJson;

    protected $table         = 'data_deletion_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'requested_by', 'scope_json', 'reason', 'mode', 'status',
        'affected_count', 'approved_by', 'executed_at',
    ];
    protected $validationRules = [
        'requested_by' => 'required|is_natural_no_zero',
        'mode'         => 'permit_empty|in_list[soft,hard]',
        'status'       => 'permit_empty|in_list[preview,approved,executed,cancelled]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['scope_json'];

    /** Permintaan yang menunggu persetujuan admin kedua. */
    public function pendingApproval(): array
    {
        return $this->where('status', 'preview')->orderBy('created_at', 'ASC')->findAll();
    }
}
