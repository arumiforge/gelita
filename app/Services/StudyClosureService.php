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
     * never shown. Each masked spot can be kept as the ordinary word it is
     * (30 §19.3 (b): "Tantangan dua" is not a name although a student is
     * called Dua); `keep` lists the spots per field with the masked text
     * around them. A tick is never set in advance, not even for a word an
     * earlier check kept: a student added to the study since then may carry
     * that word in their name, so masking stays the default.
     *
     * @return list<array<string, mixed>> id, participant_code, the masked fields, and `keep` (field => list<string> the masked text around each spot)
     */
    public function maskedFeedback(int $studyId): array
    {
        $words = $this->nameWords($studyId);
        $rows  = $this->feedbackRows($studyId);

        foreach ($rows as &$row) {
            $row['keep'] = [];

            foreach (self::FEEDBACK_FIELDS as $field) {
                if ($row[$field] === null) {
                    continue;
                }

                $text        = (string) $row[$field];
                $row[$field] = self::mask($text, $words);

                foreach (self::spotTokens($text, $words)[1] as $token) {
                    $row['keep'][$field][] = self::context($row[$field], $token);
                }
            }
        }

        return $rows;
    }

    /**
     * Saves the checked texts (the masked text plus the head researcher's own
     * replacements, and the spots kept as ordinary words) and records the
     * check. A name typed into the text is masked again; only a ticked spot
     * brings the original word back. Nothing is saved when a ticked spot
     * cannot be matched because the `[nama]` marks of that text were changed.
     *
     * @param array<int|string, array<string, string>>     $texts feedback id => field => text
     * @param array<int|string, array<string, list<mixed>>> $keep  feedback id => field => spot numbers kept as ordinary words
     */
    public function saveFeedbackNames(int $studyId, array $texts, int $staffId, array $keep = []): int
    {
        $study   = model(ResearchStudyModel::class)->find($studyId);
        $db      = db_connect();
        $words   = $this->nameWords($studyId);
        $updates = [];

        foreach ($this->feedbackRows($studyId) as $row) {
            $typed = (array) ($texts[$row['id']] ?? $texts[(string) $row['id']] ?? []);
            $kept  = (array) ($keep[$row['id']] ?? $keep[(string) $row['id']] ?? []);
            $data  = [];

            foreach (self::FEEDBACK_FIELDS as $field) {
                if ($row[$field] === null) {
                    continue;
                }

                $original = (string) $row[$field];
                $spots    = array_values(array_unique(array_map('intval', (array) ($kept[$field] ?? []))));
                $text     = array_key_exists($field, $typed) ? (string) $typed[$field] : self::mask($original, $words);
                $restored = self::restore($text, $original, $words, $spots);

                if ($restored === null) {
                    throw new \DomainException(lang('Admin.closureFeedbackKeepMismatch', [$row['participant_code']]));
                }

                $data[$field] = $restored;
            }

            if ($data !== []) {
                $updates[(int) $row['id']] = $data;
            }
        }

        $db->transBegin();

        try {
            foreach ($updates as $id => $data) {
                $db->table('participant_feedback')->where('id', $id)->update($data);
            }

            $db->table('research_studies')->where('id', $studyId)->update(['feedback_names_checked_at' => date('Y-m-d H:i:s')]);

            model(AuditLogModel::class)->record('feedback_names_masked', [
                'staff_user_id' => $staffId,
                'target_type'   => 'research_study',
                'target_id'     => (string) $studyId,
                'metadata'      => ['study_code' => $study['code'] ?? null, 'rows' => count($updates)],
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return count($updates);
    }

    /**
     * Whole words of at least 3 letters from $words replaced by `[nama]`,
     * ignoring case (29 K13).
     *
     * @param list<string> $words
     */
    public static function mask(string $text, array $words): string
    {
        $pattern = self::pattern($words);

        if ($pattern === null) {
            return $text;
        }

        // A `[nama]` already there stays as it is, even when "nama" is someone's name
        return implode(self::MASK, array_map(static fn (string $part): string => (string) preg_replace($pattern, self::MASK, $part), explode(self::MASK, $text)));
    }

    /**
     * The name-word matches of $text in reading order.
     *
     * @param list<string> $words
     *
     * @return list<string> the matched words as written
     */
    public static function occurrences(string $text, array $words): array
    {
        $pattern = self::pattern($words);
        $found   = [];

        foreach ($pattern === null ? [] : explode(self::MASK, $text) as $part) {
            if (preg_match_all($pattern, $part, $m) > 0) {
                array_push($found, ...$m[0]);
            }
        }

        return $found;
    }

    /**
     * The checked text to store: $typed with every name typed into it masked,
     * and the `[nama]` marks of the spots in $keep given back their original
     * word from $original. Null when a spot is kept but the typed text no
     * longer has one mark per spot, so the marks cannot be matched.
     *
     * @param list<string> $words
     * @param list<int>    $keep  spot numbers (0-based, reading order)
     */
    public static function restore(string $typed, string $original, array $words, array $keep): ?string
    {
        if ($keep === []) {
            return self::mask($typed, $words);
        }

        $segments = explode(self::MASK, $typed);
        $found    = self::occurrences($original, $words);
        [$tokens, $spotAt] = self::spotTokens($original, $words);

        if (count($segments) - 1 !== $tokens) {
            return null;
        }

        $back = [];

        foreach ($keep as $k) {
            if (isset($spotAt[$k])) {
                $back[$spotAt[$k]] = $found[$k];
            }
        }

        $out = self::mask($segments[0], $words);

        for ($t = 0; $t < $tokens; $t++) {
            $out .= ($back[$t] ?? self::MASK) . self::mask($segments[$t + 1], $words);
        }

        return $out;
    }

    /**
     * Where the name-word spots of $original sit among the `[nama]` marks of
     * its masked text: marks already stored (an earlier check) come between.
     *
     * @param list<string> $words
     *
     * @return array{0: int, 1: list<int>} number of marks in the masked text, and the mark number of each spot
     */
    public static function spotTokens(string $original, array $words): array
    {
        $pattern = self::pattern($words);
        $parts   = explode(self::MASK, $original);
        $token   = 0;
        $spotAt  = [];

        foreach ($parts as $i => $part) {
            $matches = $pattern === null ? 0 : (int) preg_match_all($pattern, $part);

            for ($j = 0; $j < $matches; $j++) {
                $spotAt[] = $token++;
            }

            if ($i < count($parts) - 1) {
                $token++;   // a mark stored by an earlier check
            }
        }

        return [$token, $spotAt];
    }

    // ------------------------------------------------------------ internal

    /** @return list<string> words of the study's students' names and usernames (FieldSheetService, 29 K13), longest first */
    private function nameWords(int $studyId): array
    {
        $words = array_map('strval', array_keys(FieldSheetService::nameWords($this->cohort($studyId))));
        usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return $words;
    }

    /** @return list<array<string, mixed>> the study's feedback rows: id, participant_code, and the free-text fields */
    private function feedbackRows(int $studyId): array
    {
        return db_connect()->table('participant_feedback f')
            ->select('f.id, p.participant_code, f.' . implode(', f.', self::FEEDBACK_FIELDS))
            ->join('game_sessions gs', 'gs.id = f.session_id')
            ->join('participants p', 'p.id = f.participant_id')
            ->where('gs.study_id', $studyId)
            ->orderBy('f.id', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * One pattern for all name words, longest first, so a longer name wins
     * over a shorter one inside it; null without words.
     *
     * @param list<string> $words
     */
    private static function pattern(array $words): ?string
    {
        $words = array_values(array_filter($words, static fn (string $w): bool => $w !== ''));

        if ($words === []) {
            return null;
        }

        usort($words, static fn (string $a, string $b): int => mb_strlen($b) <=> mb_strlen($a));

        return '/(?<![\p{L}\p{N}])(?:' . implode('|', array_map(static fn (string $w): string => preg_quote($w, '/'), $words)) . ')(?![\p{L}\p{N}])/iu';
    }

    /** A few masked words around mark number $k (0-based) of $masked, for the keep tick. */
    private static function context(string $masked, int $k): string
    {
        $parts  = explode(self::MASK, $masked);
        $before = preg_split('/\s+/u', trim($parts[$k] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $after  = preg_split('/\s+/u', trim($parts[$k + 1] ?? ''), -1, PREG_SPLIT_NO_EMPTY);
        $left   = array_slice($before, -4);
        $right  = array_slice($after, 0, 4);

        return trim((count($before) > 4 ? '… ' : '') . implode(' ', $left) . ' ' . self::MASK . ' ' . implode(' ', $right) . (count($after) > 4 ? ' …' : ''));
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
