<?php

namespace App\Libraries;

use App\Models\ParticipantSeatModel;
use App\Models\ResearchStudyModel;

/**
 * Where VariantPicker reads the research switch and a student's booklet
 * (31 §11.2), from the data of part 2 (28 §14): `research_studies.field_mode`
 * with the study `status`, and `participant_seats.booklet` (B1–B4) per
 * (participant_id, study_id). Tests stub it with Factories::injectMock.
 */
class BookletSource
{
    /** Field mode is on and the study is `active` (28 §5.1). */
    public function fieldModeActive(int $studyId): bool
    {
        $study = $studyId > 0 ? model(ResearchStudyModel::class)->find($studyId) : null;

        return $study !== null && (int) ($study['field_mode'] ?? 0) === 1 && (string) $study['status'] === 'active';
    }

    /** B1–B4 from the student's seat in this study, or null without a seat. */
    public function bookletFor(int $participantId, int $studyId): ?string
    {
        $booklet = strtoupper(trim((string) model(ParticipantSeatModel::class)->bookletFor($participantId, $studyId)));

        return BookletRules::isBooklet($booklet) ? $booklet : null;
    }
}
