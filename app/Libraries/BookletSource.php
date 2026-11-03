<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Where VariantPicker reads the research switch and a student's booklet
 * (31 §11.2). Part 2 (28 §14) owns the data: `research_studies.field_mode`,
 * the study `status`, and `participant_seats.booklet` (B1–B4) per
 * (participant_id, study_id).
 *
 * This default reads those tables directly, and only when they exist: on a
 * database without part 2 every study counts as "field mode off", so the
 * first attempt stays random. Tests stub it with Factories::injectMock or a
 * subclass passed to VariantPicker.
 *
 * Wiring after part 2 is merged: replace the two queries below with
 * ParticipantSeatModel (bookletFor) and the study model (fieldModeActive);
 * nothing else changes.
 */
class BookletSource
{
    private ?BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db;
    }

    /** Field mode is on and the study is `active` (28 §5.1). */
    public function fieldModeActive(int $studyId): bool
    {
        $db = $this->db();

        if ($studyId <= 0 || ! $db->tableExists('research_studies') || ! $db->fieldExists('field_mode', 'research_studies')) {
            return false;
        }

        $row = $db->table('research_studies')->select('field_mode, status')->where('id', $studyId)->get()->getRowArray();

        return $row !== null && (int) $row['field_mode'] === 1 && (string) $row['status'] === 'active';
    }

    /** B1–B4 from the student's seat in this study, or null without a seat. */
    public function bookletFor(int $participantId, int $studyId): ?string
    {
        $db = $this->db();

        if (! $db->tableExists('participant_seats')) {
            return null;
        }

        $row = $db->table('participant_seats')->select('booklet')
            ->where('participant_id', $participantId)->where('study_id', $studyId)
            ->get()->getRowArray();
        $booklet = strtoupper(trim((string) ($row['booklet'] ?? '')));

        return BookletRules::isBooklet($booklet) ? $booklet : null;
    }

    private function db(): BaseConnection
    {
        return $this->db ??= db_connect();
    }
}
