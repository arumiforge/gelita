<?php

namespace App\Models;

use App\Models\Traits\EncodesJson;
use CodeIgniter\Model;

class ParticipantConsentModel extends Model
{
    use EncodesJson;

    protected $table         = 'participant_consents';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'participant_id', 'study_id', 'consent_version', 'participant_consented',
        'parent_guardian_consented', 'guardian_name', 'consent_text_snapshot',
        'consented_at', 'withdrawn_at', 'metadata_json',
    ];
    protected $validationRules = [
        'participant_id'        => 'required|is_natural_no_zero',
        'consent_version'       => 'required|max_length[50]',
        'consent_text_snapshot' => 'required',
    ];
    protected $beforeInsert      = ['encodeJson'];
    protected $beforeUpdate      = ['encodeJson'];
    protected $beforeInsertBatch = ['encodeJsonBatch'];

    /** @var list<string> kolom JSON milik tabel ini */
    protected array $jsonFields = ['metadata_json'];

    public function latestFor(int $participantId): ?array
    {
        return $this->where('participant_id', $participantId)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
