<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Coded consent per student from L7 (research revision 10, part 4: 30 §5.2,
 * §15). One row per student, keyed by `participant_id`; never a name.
 * Writes go through App\Services\ConsentService (conditional on `updated_at`).
 */
class ParticipantConsentModel extends Model
{
    protected $table          = 'participant_consents';
    protected $primaryKey     = 'participant_id';
    protected $returnType     = 'array';
    protected $useAutoIncrement = false;
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'participant_id', 'study_id', 'parent_consent_on', 'recording_consent', 'recording_withdrawn_on',
        'risk_items_consent', 'risk_items_withdrawn_on', 'status', 'status_on',
        'entered_by', 'updated_by', 'created_at', 'updated_at',
    ];
    protected $validationRules = [
        'status' => 'permit_empty|in_list[active,left,withdrawn_keep,withdrawn_delete]',
    ];

    /**
     * Rows of these students, keyed by participant id.
     *
     * @param list<int> $participantIds
     *
     * @return array<int, array<string, mixed>>
     */
    public function forParticipants(array $participantIds): array
    {
        if ($participantIds === []) {
            return [];
        }

        $rows = $this->whereIn('participant_id', $participantIds)->findAll();

        return array_combine(array_map(static fn (array $r): int => (int) $r['participant_id'], $rows), $rows);
    }
}
