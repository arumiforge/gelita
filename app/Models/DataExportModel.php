<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class DataExportModel extends Model
{
    use EncodesJson;

    protected $table         = 'data_exports';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'requested_by', 'study_id', 'format', 'scope_json', 'anonymized', 'status',
        'row_count', 'file_path', 'file_sha256', 'error_message', 'completed_at', 'expires_at',
    ];
    protected $validationRules = [
        'requested_by' => 'required|is_natural_no_zero',
        'format'       => 'required|in_list[xlsx,csv,pdf]',
        'status'       => 'permit_empty|in_list[queued,running,done,failed]',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['scope_json'];

    /** Berkas export yang sudah lewat masa simpan dan siap dihapus. */
    public function expired(): array
    {
        return $this->where('status', 'done')
            ->where('expires_at IS NOT NULL')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->findAll();
    }

    public function markDone(int $id, string $path, string $sha, int $rows): bool
    {
        return (bool) $this->update($id, [
            'status'       => 'done',
            'file_path'    => $path,
            'file_sha256'  => $sha,
            'row_count'    => $rows,
            'completed_at' => date('Y-m-d H:i:s'),
            'expires_at'   => date('Y-m-d H:i:s', time() + (config('Gelita')->exportRetentionDays * 86400)),
        ]);
    }

    public function markFailed(int $id, string $message): bool
    {
        return (bool) $this->update($id, [
            'status'        => 'failed',
            'error_message' => mb_substr($message, 0, 500),
            'completed_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
