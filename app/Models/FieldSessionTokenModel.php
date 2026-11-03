<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Token history of the field sessions (28 §6.3). Only the latest token that
 * is not revoked is valid; older ones stay for the L1 sheet.
 */
class FieldSessionTokenModel extends Model
{
    protected $table         = 'field_session_tokens';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['field_session_id', 'token', 'created_by', 'created_at', 'revoked_at'];

    /** @return array<string, mixed>|null the valid token row of a field session */
    public function current(int $fieldSessionId): ?array
    {
        return $this->where('field_session_id', $fieldSessionId)
            ->where('revoked_at', null)
            ->orderBy('id', 'DESC')
            ->first();
    }

    /** @return array<string, mixed>|null */
    public function findToken(string $token): ?array
    {
        return $this->where('token', $token)->first();
    }

    /** @return list<array<string, mixed>> oldest first */
    public function history(int $fieldSessionId): array
    {
        return $this->where('field_session_id', $fieldSessionId)->orderBy('id', 'ASC')->findAll();
    }
}
