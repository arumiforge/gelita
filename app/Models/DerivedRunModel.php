<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * One computation of a study's derived data layer (research revision 10,
 * part 9: 35 §4, §9, K6). The derived rows themselves are written by
 * DerivedDataService through the query builder, in batches, without entities.
 */
class DerivedRunModel extends Model
{
    public const STATUSES = ['running', 'done', 'failed'];

    public const VIA = ['export', 'button', 'command'];

    protected $table          = 'derived_runs';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useTimestamps  = false;
    protected $allowedFields  = [
        'study_id', 'status', 'dictionary_version', 'idle_threshold_ms', 'rapid_guess_ms', 'test_rapid_guess_ms',
        'input_fingerprint', 'participant_count', 'notes_json', 'error', 'started_by', 'via', 'started_at', 'finished_at',
    ];

    /** The study's newest run (any status), or null. */
    public function latestFor(int $studyId): ?array
    {
        return $this->where('study_id', $studyId)->orderBy('id', 'DESC')->first();
    }
}
