<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Students allowed into a make-up field session (28 §6.7, K12). Main sessions
 * have no members: the school and class group decide there.
 */
class FieldSessionMemberModel extends Model
{
    protected $table         = 'field_session_members';
    protected $primaryKey    = 'field_session_id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = ['field_session_id', 'participant_id', 'added_by', 'created_at'];

    public function isMember(int $fieldSessionId, int $participantId): bool
    {
        return $this->where('field_session_id', $fieldSessionId)
            ->where('participant_id', $participantId)
            ->countAllResults() > 0;
    }

    /** @return list<int> participant ids */
    public function memberIds(int $fieldSessionId): array
    {
        return array_map('intval', array_column(
            $this->select('participant_id')->where('field_session_id', $fieldSessionId)->findAll(),
            'participant_id',
        ));
    }
}
