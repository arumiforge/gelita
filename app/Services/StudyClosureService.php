<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\ResearchStudyModel;

/**
 * Study closure (research revision 10, part 4: 30 §10.2): the conditions,
 * the name check of the free-text feedback, and the replacement of names,
 * usernames, passwords, and schools with codes. Two steps like a deletion:
 * a preview, then the admin types `GANTI`. Afterwards the data can no longer
 * be linked to a child.
 */
class StudyClosureService
{
    public const CONFIRM_WORD = 'GANTI';

    /** Free-text feedback fields checked for names (30 K13 via 29). */
    public const FEEDBACK_FIELDS = ['liked_most', 'hardest_part', 'new_learning', 'suggestion'];

    public const MASK = '[nama]';

    public function identitiesReplaced(?int $studyId): bool
    {
        if ($studyId === null) {
            return false;
        }

        $study = model(ResearchStudyModel::class)->find($studyId);

        return $study !== null && $study['identities_replaced_at'] !== null;
    }

    /** True when the student belongs to a study whose identities were replaced. */
    public function identitiesReplacedFor(int $participantId): bool
    {
        $studyId = db_connect()->table('participant_consents')->select('study_id')->where('participant_id', $participantId)->get()->getRow('study_id');

        return $this->identitiesReplaced($studyId === null ? null : (int) $studyId);
    }

    /**
     * The four conditions shown as ticks; *Hitung* needs all of them.
     *
     * @param array<string, mixed> $study
     *
     * @return array<string, bool> closed, requests_done, register_current, feedback_checked
     */
    public function conditions(array $study): array
    {
        $studyId = (int) $study['id'];
        $codes   = array_column(db_connect()->table('participant_consents c')->select('p.participant_code')
            ->join('participants p', 'p.id = c.participant_id')->where('c.study_id', $studyId)->get()->getResultArray(), 'participant_code');
        $open    = $codes === [] ? 0 : db_connect()->table('data_deletion_requests')->where('kind IS NOT NULL', null, false)
            ->whereIn('participant_code', $codes)->whereNotIn('status', ['completed', 'cancelled'])->countAllResults();
        $last     = service('consentService')->lastChange($studyId);
        $exported = $study['consent_register_exported_at'];

        return [
            'closed'           => $study['status'] === 'closed' && $study['closed_at'] !== null,
            'requests_done'    => $open === 0,
            'register_current' => $exported !== null && ($last === null || (string) $exported >= substr((string) $last, 0, 19)),
            'feedback_checked' => $study['feedback_names_checked_at'] !== null,
        ];
    }

    /**
     * What *Hitung* shows: accounts and schools that will change.
     *
     * @return array{participants: int, schools: int}
     */
    public function preview(int $studyId): array
    {
        return ['participants' => count($this->cohort($studyId)), 'schools' => count($this->schools($studyId))];
    }

    /**
     * Replaces names, usernames, passwords, and schools with codes, in one
     * transaction (30 §10.2, K11). Conditional on `identities_replaced_at`
     * still empty, so a second press changes nothing.
     *
     * @return array{participants: int, schools: int}
     */
    public function replaceIdentities(int $studyId, int $staffId): array
    {
        $study = model(ResearchStudyModel::class)->find($studyId);

        if ($study === null || in_array(false, $this->conditions($study), true)) {
            throw new \DomainException(lang('Admin.closureNotReady'));
        }

        $db  = db_connect();
        $now = date('Y-m-d H:i:s');

        $db->transBegin();

        try {
            $db->table('research_studies')->where('id', $studyId)->where('identities_replaced_at', null)->update(['identities_replaced_at' => $now]);

            if ($db->affectedRows() !== 1) {
                throw new \DomainException(lang('Admin.closureReplaced'));
            }

            $schools = $this->schools($studyId);

            foreach ($schools as $oldId => $researchCode) {
                // The code moves to a new row named by the code, without NPSN or address (K11)
                $db->table('schools')->where('id', $oldId)->update(['research_code' => null]);
                $db->table('schools')->insert([
                    'name' => $researchCode, 'research_code' => $researchCode, 'is_verified' => 1, 'is_active' => 1,
                    'country_code' => null, 'created_at' => $now, 'updated_at' => $now,
                ]);
                $newId = (int) $db->insertID();

                foreach (['participants', 'field_sessions', 'participant_code_counters'] as $table) {
                    $db->table($table)->where('school_id', $oldId)->update(['school_id' => $newId]);
                }
            }

            foreach ($this->cohort($studyId) as $row) {
                $code = (string) $row['participant_code'];
                $db->table('participants')->where('id', (int) $row['id'])->update([
                    'display_name'         => $code,
                    'username'             => strtolower($code),
                    // A hash of random bytes: no password signs in with it
                    'password_hash'        => password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT),
                    'school_name_snapshot' => $row['research_code'] ?? $schools[(int) $row['school_id']] ?? null,
                    'deactivated_at'       => $row['deactivated_at'] ?? $now,
                ]);
            }

            $counts = ['participants' => count($this->cohort($studyId)), 'schools' => count($schools)];

            model(AuditLogModel::class)->record('study_identities_replaced', [
                'staff_user_id' => $staffId,
                'target_type'   => 'research_study',
                'target_id'     => (string) $studyId,
                'metadata'      => ['study_code' => $study['code']] + $counts,
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return $counts;
    }

    /**
     * Feedback rows of the study with the words that match a student's
     * display name or username replaced by `[nama]` (30 §10.2). Names are
     * never shown.
     *
     * @return list<array<string, mixed>> id, participant_code, and the masked fields
     */
    public function maskedFeedback(int $studyId): array
    {
        $words = $this->nameWords($studyId);
        $rows  = db_connect()->table('participant_feedback f')
            ->select('f.id, p.participant_code, f.' . implode(', f.', self::FEEDBACK_FIELDS))
            ->join('game_sessions gs', 'gs.id = f.session_id')
            ->join('participants p', 'p.id = f.participant_id')
            ->where('gs.study_id', $studyId)
            ->orderBy('f.id', 'ASC')
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            foreach (self::FEEDBACK_FIELDS as $field) {
                $row[$field] = $row[$field] === null ? null : self::mask((string) $row[$field], $words);
            }
        }

        return $rows;
    }

    /**
     * Saves the checked texts (the masked text plus the head researcher's own
     * replacements) and records the check.
     *
     * @param array<int|string, array<string, string>> $texts feedback id => field => text
     */
    public function saveFeedbackNames(int $studyId, array $texts, int $staffId): int
    {
        $study = model(ResearchStudyModel::class)->find($studyId);
        $db    = db_connect();
        $rows  = 0;
        $words = $this->nameWords($studyId);

        $db->transBegin();

        try {
            foreach ($this->maskedFeedback($studyId) as $row) {
                $typed = (array) ($texts[$row['id']] ?? $texts[(string) $row['id']] ?? []);
                $data  = [];

                foreach (self::FEEDBACK_FIELDS as $field) {
                    if ($row[$field] === null) {
                        continue;
                    }

                    // A typed text is masked again, so a name can never be typed back in
                    $data[$field] = self::mask(array_key_exists($field, $typed) ? (string) $typed[$field] : (string) $row[$field], $words);
                }

                if ($data !== []) {
                    $db->table('participant_feedback')->where('id', (int) $row['id'])->update($data);
                    $rows++;
                }
            }

            $db->table('research_studies')->where('id', $studyId)->update(['feedback_names_checked_at' => date('Y-m-d H:i:s')]);

            model(AuditLogModel::class)->record('feedback_names_masked', [
                'staff_user_id' => $staffId,
                'target_type'   => 'research_study',
                'target_id'     => (string) $studyId,
                'metadata'      => ['study_code' => $study['code'] ?? null, 'rows' => $rows],
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return $rows;
    }

    /**
     * Whole words of at least 3 letters from $words replaced by `[nama]`,
     * ignoring case (29 K13).
     *
     * WAVE-E seam (part 3): use the name check of the field-sheet import (29 K13) here instead of this copy.
     *
     * @param list<string> $words
     */
    public static function mask(string $text, array $words): string
    {
        foreach ($words as $word) {
            $text = (string) preg_replace('/(?<![\p{L}\p{N}])' . preg_quote($word, '/') . '(?![\p{L}\p{N}])/iu', self::MASK, $text);
        }

        return $text;
    }

    // ------------------------------------------------------------ internal

    /** @return list<string> words of the study's students' names and usernames, longest first */
    private function nameWords(int $studyId): array
    {
        $words = [];

        foreach ($this->cohort($studyId) as $row) {
            foreach (preg_split('/[^\p{L}]+/u', (string) $row['display_name'] . ' ' . (string) $row['username']) ?: [] as $word) {
                if (mb_strlen($word) >= 3) {
                    $words[mb_strtolower($word)] = true;
                }
            }
        }

        $words = array_keys($words);
        usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $words;
    }

    /** @return list<array<string, mixed>> students with a consent row in the study */
    private function cohort(int $studyId): array
    {
        return db_connect()->table('participant_consents c')
            ->select('p.id, p.participant_code, p.display_name, p.username, p.school_id, p.deactivated_at, s.research_code')
            ->join('participants p', 'p.id = c.participant_id')
            ->join('schools s', 's.id = p.school_id', 'left')
            ->where('c.study_id', $studyId)
            ->get()->getResultArray();
    }

    /** @return array<int, string> named school id => research code, for the study's students and field sessions */
    private function schools(int $studyId): array
    {
        $ids = array_merge(
            array_column($this->cohort($studyId), 'school_id'),
            array_column(db_connect()->table('field_sessions')->select('school_id')->where('study_id', $studyId)->get()->getResultArray(), 'school_id'),
        );
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if ($ids === []) {
            return [];
        }

        $rows = db_connect()->table('schools')->select('id, research_code')->whereIn('id', $ids)->where('research_code IS NOT NULL', null, false)->get()->getResultArray();

        return array_combine(array_map('intval', array_column($rows, 'id')), array_column($rows, 'research_code'));
    }
}
