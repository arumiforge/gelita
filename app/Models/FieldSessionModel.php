<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Field sessions (`sesi lapangan`, research revision 10, part 2: 28 §6).
 * The state (pending, open, closed) is derived from the hours and never
 * stored: FieldSessionService::state().
 */
class FieldSessionModel extends Model
{
    protected $table         = 'field_sessions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $allowedFields = [
        'study_id', 'code', 'school_id', 'rombel', 'meeting_no', 'makeup_no', 'main_session_id',
        'session_date', 'opens_at', 'closes_at', 'closed_at', 'region_code', 'created_by', 'closed_by',
        // Part 8 (34 §5.3): the start-test button
        'test_started_at', 'test_started_by',
    ];

    /**
     * Field sessions of a study, newest first, with the school's research code.
     *
     * @return list<array<string, mixed>>
     */
    public function forStudy(int $studyId): array
    {
        return $this->select('field_sessions.*, schools.research_code')
            ->join('schools', 'schools.id = field_sessions.school_id', 'left')
            ->where('field_sessions.study_id', $studyId)
            ->orderBy('field_sessions.opens_at', 'DESC')
            ->orderBy('field_sessions.id', 'DESC')
            ->findAll();
    }

    /**
     * Sessions that were open at some point between $since and now (monitoring, 28 §12).
     *
     * @return list<array<string, mixed>>
     */
    public function recent(int $studyId, string $since, string $now): array
    {
        return $this->select('field_sessions.*, schools.research_code')
            ->join('schools', 'schools.id = field_sessions.school_id', 'left')
            ->where('field_sessions.study_id', $studyId)
            ->where('field_sessions.opens_at <=', $now)
            ->groupStart()
                ->where('field_sessions.closed_at >=', $since)
                ->orGroupStart()
                    ->where('field_sessions.closed_at', null)
                    ->where('field_sessions.closes_at >=', $since)
                ->groupEnd()
            ->groupEnd()
            ->orderBy('field_sessions.opens_at', 'ASC')
            ->findAll();
    }
}
