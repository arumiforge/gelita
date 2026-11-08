<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One typed field sheet per upload (research revision 10, part 3: 29 §10.4,
 * §12). Only `purpose = main` rows without `superseded_at` count.
 */
class FieldSheetImportModel extends Model
{
    public const PURPOSES = ['main', 'check'];

    protected $table         = 'field_sheet_imports';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'field_session_id', 'sheet', 'spec_version', 'purpose', 'file_sha256', 'row_count',
        'warning_count', 'names_confirmed', 'uploaded_by', 'created_at', 'superseded_at', 'superseded_by',
    ];

    /** The main import that counts for one sheet of one field session, if any. */
    public function current(int $fieldSessionId, string $sheet): ?array
    {
        return $this->where('field_session_id', $fieldSessionId)->where('sheet', $sheet)
            ->where('purpose', 'main')->where('superseded_at', null)
            ->orderBy('id', 'DESC')->first();
    }

    /** @return list<array<string, mixed>> imports of one field session, newest first */
    public function forSession(int $fieldSessionId): array
    {
        return $this->where('field_session_id', $fieldSessionId)->orderBy('id', 'DESC')->findAll();
    }
}
