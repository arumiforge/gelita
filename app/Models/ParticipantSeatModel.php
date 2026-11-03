<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Seat, booklet, and test form of a student in one study (L2 sheet,
 * research revision 10, part 2: 28 §11). Booklet and forms are computed by
 * App\Libraries\SeatRules from the row and seat, never typed.
 *
 * Contract with part 5 (31 §11): VariantPicker reads the booklet through
 * bookletFor().
 */
class ParticipantSeatModel extends Model
{
    protected $table         = 'participant_seats';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'participant_id', 'study_id', 'row_no', 'seat_no', 'booklet', 'pre_form', 'post_form',
        'entered_by', 'entered_at', 'updated_at', 'locked_at',
    ];

    /** Booklet `B1`–`B4` of a student in a study, or null when no seat was entered yet. */
    public function bookletFor(int $participantId, int $studyId): ?string
    {
        $row = $this->findFor($participantId, $studyId);

        return $row === null || $row['booklet'] === null || $row['booklet'] === '' ? null : (string) $row['booklet'];
    }

    /** @return array<string, mixed>|null */
    public function findFor(int $participantId, int $studyId): ?array
    {
        return $this->where('participant_id', $participantId)->where('study_id', $studyId)->first();
    }

    /**
     * Seats of these students in a study, keyed by participant id.
     *
     * @param list<int> $participantIds
     *
     * @return array<int, array<string, mixed>>
     */
    public function forParticipants(int $studyId, array $participantIds): array
    {
        if ($participantIds === []) {
            return [];
        }

        $out = [];

        foreach ($this->where('study_id', $studyId)->whereIn('participant_id', $participantIds)->findAll() as $row) {
            $out[(int) $row['participant_id']] = $row;
        }

        return $out;
    }
}
