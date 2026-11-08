<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

/** Rows of a typed field sheet (29 §12); `values_json` holds the FieldSheetSpec column codes. */
class FieldSheetRowModel extends Model
{
    use EncodesJson;

    protected $table             = 'field_sheet_rows';
    protected $primaryKey        = 'id';
    protected $returnType        = 'array';
    protected $useTimestamps     = false;
    protected $allowedFields     = ['import_id', 'row_no', 'participant_id', 'seat_no', 'started_at', 'ended_at', 'values_json'];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> */
    protected array $jsonFields = ['values_json'];

    /** @return list<array<string, mixed>> */
    public function forImport(int $importId): array
    {
        return $this->where('import_id', $importId)->orderBy('row_no')->findAll();
    }
}
