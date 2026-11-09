<?php

namespace App\Libraries;

/**
 * Structured copy of the research data dictionary
 * (docs/revisi-10-analitik/11_KAMUS_DATA.md), research revision 10, part 3
 * (29 §8, K10). Meaning and labels stay in ExportLabels (one place for the
 * wording); this class adds, per download column, its name in the
 * dictionary, the analytics that use it, the anchor of its section in 11,
 * and whether 11 marks it as existing (`ada`) or new (`baru`).
 *
 * VERSION is written on every download (the Kamus Data header and
 * `data_exports.dictionary_version`) and is the one version constant the
 * derived layer reads (part 9, 35 §8). Locked at G4 with the game version
 * (K17). Every column of every research sheet has an entry and no entry
 * names a column that is not downloaded (DictionarySheetTest).
 *
 * Field sheets (l1_… – l6_…) take their entries from FieldSheetSpec.
 * A later part adds its sheet or columns here in one place, e.g. part 6
 * (32 §10): `percobaan` action_key … reason_last_input_ms.
 */
final class ResearchDictionary
{
    public const VERSION = 'kamus-1.0';

    public const DOC = '11_KAMUS_DATA.md';

    /** Anchors of 11 used below (checked against 11 by DictionarySheetTest). */
    private const PEOPLE  = 'variabel-peserta-dan-sesi';
    private const KEYS    = 'kunci-penggabungan-dan-waktu';
    private const ITEMS   = 'variabel-butir';
    private const FIELD   = 'aturan-gabung-lembar-lapangan';
    private const MARKERS = 'penanda-untuk-analisis';
    // Part 8 (34 §11.4): 11 → Tes awal–akhir, angket, dan skala sikap (1.1)
    private const INSTRUMENTS = 'tes-awalakhir-angket-dan-skala-sikap-11';

    /**
     * sheet => [ref, analytics, columns]; a column is `status` (same name in
     * 11, the sheet's ref and analytics) or [status, name in 11, ref, analytics].
     *
     * @var array<string, array{0: string, 1: list<int>, 2: array<string, string|list<mixed>>}>
     */
    private const SHEETS = [
        'peserta' => [self::PEOPLE, [], [
            'participant_code' => 'ada', 'school_code' => 'baru', 'rombel' => 'baru', 'booklet' => 'baru',
            'age' => 'ada', 'gender' => 'ada', 'class_level' => 'ada', 'registered_at' => 'ada', 'assented_at' => 'ada',
            'intro_seen_at' => ['ada', 'intro_seen_at', 'analitik-8-navigasi-permainan', [8]],
            'pw_first_submit_criteria' => 'ada', 'pw_weak_submit_count' => 'ada',
            // Part 8 (34 §11.4)
            'pre_form' => ['baru', 'pre_form', self::INSTRUMENTS, []], 'post_form' => ['baru', 'post_form', self::INSTRUMENTS, []], 'pre_date' => ['baru', 'pre_date', self::INSTRUMENTS, []], 'post_date' => ['baru', 'post_date', self::INSTRUMENTS, []], 'pre_makeup' => ['baru', 'pre_makeup', self::INSTRUMENTS, []], 'post_makeup' => ['baru', 'post_makeup', self::INSTRUMENTS, []], 'pre_d1' => ['baru', 'pre_d1', self::INSTRUMENTS, []], 'pre_d2' => ['baru', 'pre_d2', self::INSTRUMENTS, []], 'pre_d3' => ['baru', 'pre_d3', self::INSTRUMENTS, []], 'post_d1' => ['baru', 'post_d1', self::INSTRUMENTS, []], 'post_d2' => ['baru', 'post_d2', self::INSTRUMENTS, []], 'post_d3' => ['baru', 'post_d3', self::INSTRUMENTS, []], 'pre_minutes_p1' => ['baru', 'pre_minutes_p1', self::INSTRUMENTS, []], 'pre_minutes_p23' => ['baru', 'pre_minutes_p23', self::INSTRUMENTS, []], 'post_minutes_p1' => ['baru', 'post_minutes_p1', self::INSTRUMENTS, []], 'post_minutes_p23' => ['baru', 'post_minutes_p23', self::INSTRUMENTS, []], 'pre_capped' => ['baru', 'pre_capped', self::INSTRUMENTS, []], 'post_capped' => ['baru', 'post_capped', self::INSTRUMENTS, []],
        ]],
        'sesi_lapangan' => [self::PEOPLE, [1, 5], [
            'field_session_code' => 'baru', 'school_code' => 'baru', 'rombel' => 'baru', 'meeting_no' => 'baru',
            'makeup_no' => 'baru', 'main_session_code' => 'baru', 'region_opened' => 'baru', 'session_date' => 'baru',
            'opens_at' => 'baru', 'closes_at' => 'baru', 'closed_at' => 'baru',
        ]],
        'sesi' => [self::PEOPLE, [5], [
            'session_code' => 'ada', 'participant_code' => 'ada', 'study_code' => 'baru', 'field_session_code' => 'baru',
            'meeting_no' => 'baru', 'release_code' => 'ada', 'locale' => 'ada', 'status' => 'ada',
            'started_at' => 'ada', 'last_active_at' => 'ada', 'ended_at' => 'ada', 'duration_ms' => 'ada',
            'device_type' => 'ada', 'screen_size' => 'ada',
        ]],
        'progres' => ['analitik-1-penyelesaian-aktivitas', [1], [
            'study_code' => 'baru', 'participant_code' => 'ada', 'current_level' => 'ada', 'current_node_ref' => 'ada',
            'unlocked_level_sequence' => 'ada', 'completed_nodes' => 'ada', 'total_nodes' => 'ada',
            'completed_levels' => 'ada', 'total_stars' => 'ada', 'updated_at' => 'ada',
        ]],
        'pengerjaan' => ['pengerjaan-pertama', [1, 3, 4, 5], [
            'attempt_id' => ['ada', 'attempt_id', self::KEYS, [1, 3, 4, 5]], 'session_code' => 'ada', 'participant_code' => 'ada',
            'field_session_code' => 'baru', 'meeting_no' => 'baru', 'level' => 'ada', 'node_ref' => 'ada',
            'node_sequence' => 'ada', 'engine_type' => 'ada', 'variant_group' => ['ada', 'variant', self::ITEMS, [1]],
            'variant_title' => 'ada', 'variant_source' => ['baru', 'variant_source', self::MARKERS, [1, 3]],
            'booklet_at_open' => ['baru', 'booklet_at_open', self::MARKERS, [1, 3]],
            'attempt_no' => 'ada', 'status' => ['ada', 'status', 'analitik-1-penyelesaian-aktivitas', [1]],
            'completion_reason' => ['ada', 'completion_reason', 'analitik-1-penyelesaian-aktivitas', [1]],
            'scorable_items' => 'ada', 'first_pass_correct' => ['ada', 'first_pass_correct', 'analitik-3-ketepatan-percobaan-pertama', [3]],
            'final_correct' => ['ada', 'final_correct', 'analitik-2-ketepatan-jawaban', [2]],
            'check_count' => ['ada', 'check_count', 'analitik-4-jumlah-percobaan', [4]], 'max_tries' => 'ada',
            'hint_count' => ['ada', 'hint_count', 'analitik-7-penggunaan-bantuan', [7]],
            'answer_change_count' => ['ada', 'answer_change_count', 'analitik-9-perubahan-setelah-umpan-balik', [9]],
            'audio_use_count' => ['ada', 'audio_use_count', 'analitik-7-penggunaan-bantuan', [7]], 'stars' => 'ada',
            'started_at' => ['ada', 'started_at', 'analitik-5-waktu-pengerjaan', [5]],
            'completed_at' => ['ada', 'completed_at', 'analitik-5-waktu-pengerjaan', [5]],
            'duration_ms' => ['ada', 'duration_ms', 'analitik-5-waktu-pengerjaan', [5]],
            // Part 7 (33 K6)
            'is_retake' => ['baru', 'is_retake', self::MARKERS, [1, 3, 5]],
        ]],
        'jawaban' => ['analitik-2-ketepatan-jawaban', [2, 3], [
            'attempt_id' => ['ada', 'attempt_id', self::KEYS, [2, 3]], 'session_code' => 'ada', 'participant_code' => 'ada',
            'field_session_code' => 'baru', 'level' => 'ada', 'node_ref' => 'ada', 'variant_group' => ['ada', 'variant', self::ITEMS, [2]],
            'item_key' => ['ada', 'item_key', self::KEYS, [2, 3]], 'interaction_type' => 'ada', 'bloom_level' => ['ada', 'bloom_level', self::ITEMS, [2]],
            'skill' => 'ada', 'indicator' => 'ada', 'prompt' => 'ada', 'display_order' => 'ada', 'status' => 'ada',
            'try_count' => ['ada', 'try_count', 'analitik-4-jumlah-percobaan', [4]], 'first_answer' => 'ada', 'final_answer' => 'ada',
            'answer_key' => 'baru', 'first_pass_correct' => ['ada', 'first_pass_correct', 'analitik-3-ketepatan-percobaan-pertama', [3]],
            'is_correct' => 'ada', 'solved_on_try' => ['ada', 'solved_on_try', 'analitik-4-jumlah-percobaan', [4]],
            'change_count' => ['ada', 'change_count', 'analitik-9-perubahan-setelah-umpan-balik', [9]],
            'hint_used' => ['ada', 'hint_used', 'analitik-7-penggunaan-bantuan', [7]],
            'wrong_click_count' => ['ada', 'wrong_click_count', 'analitik-6-pola-kesalahan', [6]],
            'started_at' => 'ada', 'answered_at' => 'ada', 'duration_ms' => ['ada', 'duration_ms', 'analitik-5-waktu-pengerjaan', [5]],
        ]],
        'percobaan' => ['analitik-4-jumlah-percobaan', [3, 4, 6], [
            'attempt_id' => ['ada', 'attempt_id', self::KEYS, [3, 4, 6]], 'session_code' => 'ada', 'participant_code' => 'ada',
            'field_session_code' => 'baru', 'node_ref' => 'ada', 'variant_group' => ['ada', 'variant', self::ITEMS, [4]],
            'item_key' => ['ada', 'item_key', self::KEYS, [3, 4, 6]], 'try_no' => ['ada', 'try_no', self::KEYS, [3, 4, 6]],
            'check_no' => 'ada', 'answer' => 'ada', 'is_correct' => 'ada',
            'error_code' => ['baru', 'error_code', 'analitik-6-pola-kesalahan', [6]],
            'manual_code' => ['baru', 'manual_code', 'analitik-6-pola-kesalahan', [6]],
            'hint_opened_before' => ['ada', 'hint_before', 'analitik-7-penggunaan-bantuan', [7]],
            'ready_ms' => ['baru', 'ready_ms', 'analitik-6-pola-kesalahan', [5, 6]],
            'first_input_ms' => ['ada', 'first_input_ms', 'analitik-5-waktu-pengerjaan', [5]],
            'last_input_ms' => ['ada', 'last_input_ms', 'analitik-5-waktu-pengerjaan', [5, 6]],
            'occurred_at' => ['ada', 'occurred_at', self::KEYS, [5]],
            // Part 6 (32 §10): Skenario Digital
            'action_key' => 'baru', 'action_score' => 'baru', 'reason_code' => ['baru', 'reason_code', 'analitik-6-pola-kesalahan', [6]],
            'reason_ready_ms' => ['baru', 'reason_ready_ms', 'analitik-5-waktu-pengerjaan', [5]],
            'action_last_input_ms' => ['baru', 'action_last_input_ms', 'analitik-5-waktu-pengerjaan', [5]],
            'reason_last_input_ms' => ['baru', 'reason_last_input_ms', 'analitik-5-waktu-pengerjaan', [5]],
        ]],
        'navigasi' => ['analitik-8-navigasi-permainan', [8], [
            'session_code' => 'ada', 'participant_code' => 'ada', 'field_session_code' => 'baru', 'sequence_no' => 'ada',
            'occurred_at_corrected' => ['ada', 'occurred_at_corrected', self::KEYS, [5, 8]],
            'offset_known' => ['baru', 'offset_known', self::KEYS, [5, 8]],
            'event_type' => 'ada', 'screen' => 'ada', 'from_screen' => 'ada', 'level' => 'ada', 'node_ref' => 'ada',
            'attempt_id' => ['ada', 'attempt_id', self::KEYS, [8]], 'payload' => 'ada',
        ]],
        'bantuan' => ['analitik-7-penggunaan-bantuan', [7], [
            'session_code' => 'ada', 'participant_code' => 'ada', 'field_session_code' => 'baru',
            'occurred_at_corrected' => ['ada', 'occurred_at_corrected', self::KEYS, [5, 7]],
            'jenis' => 'ada', 'level' => 'ada', 'node_ref' => 'ada', 'attempt_id' => ['ada', 'attempt_id', self::KEYS, [7]],
            'item_key' => 'ada', 'hint_id' => 'ada', 'asset_key' => 'ada', 'audio_context' => 'ada', 'audio_locale' => 'ada',
            'action' => 'ada', 'play_index' => 'ada', 'listened_ms' => ['ada', 'listened_ms', 'analitik-7-penggunaan-bantuan', [5, 7]],
            'completed' => 'ada',
        ]],
        'selesai' => ['analitik-1-penyelesaian-aktivitas', [1], [
            'session_code' => 'ada', 'participant_code' => 'ada', 'field_session_code' => 'baru',
            'occurred_at_corrected' => ['ada', 'occurred_at_corrected', self::KEYS, [1, 5]],
            'offset_known' => ['baru', 'offset_known', self::KEYS, [1, 5]],
            'event_type' => 'ada', 'level' => 'ada', 'node_ref' => 'ada', 'attempt_id' => ['ada', 'attempt_id', self::KEYS, [1]], 'payload' => 'ada',
        ]],
        'event' => [self::KEYS, [1, 5, 6, 8], [
            'session_code' => 'ada', 'participant_code' => 'ada', 'field_session_code' => 'baru', 'level' => 'ada',
            'node_ref' => 'ada', 'attempt_id' => 'ada', 'item_key' => 'ada', 'event_type' => 'ada', 'sequence_no' => 'ada',
            'occurred_at_corrected' => 'ada', 'offset_known' => 'baru', 'payload' => 'ada',
        ]],
        'masukan' => [self::PEOPLE, [], [
            'participant_code' => 'ada', 'session_code' => 'ada', 'rating' => ['ada', 'feedback_rating', self::PEOPLE, []],
            'liked_most' => 'ada', 'hardest_part' => 'ada', 'new_learning' => 'ada', 'suggestion' => 'ada', 'submitted_at' => 'ada',
        ]],
        // Part 8 (34 §11.4): the test module's sheets; risiko_gabungan is aggregate only (29 K9)
        'tes_jawaban' => [self::INSTRUMENTS, [], [
            'participant_code' => 'ada', 'occasion' => 'baru', 'form' => 'baru', 'item_code' => 'baru', 'response' => 'baru', 'score' => 'baru',
            'error_codes' => 'baru', 'not_reached' => 'baru', 'time_on_item' => 'baru', 'answer_changes' => 'baru', 'instrument_version' => 'baru',
        ]],
        'tes_bagian' => [self::INSTRUMENTS, [], [
            'participant_code' => 'ada', 'occasion' => 'baru', 'part' => 'baru', 'block' => 'baru', 'started_at' => 'baru', 'submitted_at' => 'baru',
            'closed_by_limit' => 'baru', 'extra_minutes' => 'baru', 'raw_score' => 'baru', 'answered_count' => 'baru', 'is_makeup' => 'baru',
            'field_session_code' => 'baru',
        ]],
        'angket' => [self::INSTRUMENTS, [], [
            'participant_code' => 'ada', 'questionnaire_date' => 'baru', 'questionnaire_makeup' => 'baru', 'questionnaire_late' => 'baru',
            'questionnaire_minutes' => 'baru', 'phone_access' => 'baru', 'home_phone' => 'baru', 'home_tablet' => 'baru', 'home_computer' => 'baru', 'home_console' => 'baru', 'home_device_none' => 'baru', 'home_internet' => 'baru', 'first_use_grade' => 'baru', 'screen_time_school' => 'baru', 'screen_time_holiday' => 'baru', 'game_time_school' => 'baru', 'game_time_holiday' => 'baru', 'act_video' => 'baru', 'act_game' => 'baru', 'act_chat' => 'baru', 'act_schoolwork' => 'baru', 'act_search' => 'baru', 'act_read' => 'baru', 'act_post' => 'baru', 'acct_whatsapp' => 'baru', 'acct_youtube' => 'baru', 'acct_tiktok' => 'baru', 'acct_instagram' => 'baru', 'acct_facebook' => 'baru', 'acct_game' => 'baru', 'parent_couse' => 'baru', 'parent_talk' => 'baru', 'parent_rule_time' => 'baru', 'parent_rule_content' => 'baru', 'safety_info_parent' => 'baru', 'safety_info_teacher' => 'baru', 'safety_info_family' => 'baru', 'safety_info_friend' => 'baru', 'safety_info_media' => 'baru', 'safety_info_none' => 'baru', 'safety_info_dk' => 'baru', 'school_device_use' => 'baru', 'digital_quiz_exp' => 'baru', 'read_medium_pref' => 'baru', 'home_indonesian' => 'baru', 'books_home' => 'baru',
        ]],
        'sikap' => [self::INSTRUMENTS, [], [
            'participant_code' => 'ada', 'skp_ct_01' => 'baru', 'skp_01' => 'baru', 'skp_02' => 'baru', 'skp_03' => 'baru', 'skp_04' => 'baru', 'skp_05' => 'baru', 'skp_06' => 'baru', 'skp_07' => 'baru', 'skp_08' => 'baru', 'skp_09' => 'baru', 'skp_10' => 'baru', 'skp_11' => 'baru', 'skp_12' => 'baru', 'skp_01_ms' => 'baru', 'skp_02_ms' => 'baru', 'skp_03_ms' => 'baru', 'skp_04_ms' => 'baru', 'skp_05_ms' => 'baru', 'skp_06_ms' => 'baru', 'skp_07_ms' => 'baru', 'skp_08_ms' => 'baru', 'skp_09_ms' => 'baru', 'skp_10_ms' => 'baru', 'skp_11_ms' => 'baru', 'skp_12_ms' => 'baru', 'skp_changes' => 'baru', 'sikap_makeup' => 'baru',
            'sikap_submitted_at' => 'baru', 'sikap_version' => 'baru', 'feedback_rating' => 'baru',
        ]],
        'risiko_gabungan' => [self::INSTRUMENTS, [], [
            'school_code' => 'baru', 'item_code' => 'baru', 'unit' => 'baru', 'choice' => 'baru', 'students' => 'baru',
        ]],
        // Part 4 (30 §10.1, keputusan no. 9)
        'izin' => [self::PEOPLE, [], [
            'participant_code' => 'ada', 'parent_consent_on' => 'baru', 'recording_consent' => 'baru', 'recording_withdrawn_on' => 'baru',
            'risk_items_consent' => 'baru', 'risk_items_withdrawn_on' => 'baru', 'assented_at' => 'ada', 'assent_version' => 'baru',
            'status' => 'baru', 'status_on' => 'baru',
        ]],
        'butir' => [self::ITEMS, [2, 3, 6], [
            'item_key' => 'ada', 'level' => 'ada', 'node_ref' => 'ada', 'variant_group' => 'ada', 'variant_title' => 'ada',
            'sequence' => 'ada', 'interaction_type' => 'ada', 'bloom_level' => 'ada', 'skill' => 'ada', 'indicator' => 'ada',
            'prompt' => 'ada', 'passage_key' => 'ada', 'answer_key' => 'baru', 'decoy' => 'ada', 'scorable' => 'ada',
            'is_active' => 'ada', 'media_asset_key' => 'ada', 'round' => 'baru', 'variant' => 'baru', 'booklet_shared' => 'baru',
            'dimension' => 'baru', 'subaspect' => 'baru', 'akm_level' => 'baru', 'akm_level_source' => 'baru',
            'pirls_process' => 'baru', 'bloom_level_source' => 'baru', 'text_genre' => 'baru', 'testlet_key' => 'baru',
            'd2_topic' => 'baru', 'd3_clue_type' => 'baru', 'gap_count' => 'baru', 'distractor_word_count' => 'baru',
            'passage_words' => 'baru', 'decoy_count' => 'baru', 'option_count' => 'baru', 'reason_option_count' => 'baru',
            'puzzle_grid' => 'baru', 'tap_min_dp' => 'baru', 'review_status' => 'baru', 'reference_source' => 'baru',
        ]],
        'kode_pilihan' => ['analitik-6-pola-kesalahan', [6, 10], [
            'item_key' => 'baru', 'node_ref' => 'baru', 'variant' => 'baru', 'choice_type' => 'baru', 'choice_ref' => 'baru',
            'error_code' => 'baru', 'is_main' => 'baru', 'option_score' => 'baru',
        ]],
    ];

    /** @return list<string> sheets with entries, field sheets included */
    public static function sheets(): array
    {
        return [...array_keys(self::SHEETS), ...FieldSheetSpec::imported()];
    }

    /**
     * Entry of one download column, or null when it has none.
     *
     * @return array{kamus: string, analytics: list<int>, ref: string, status: string}|null
     */
    public static function entry(string $sheet, string $column): ?array
    {
        if (! isset(self::SHEETS[$sheet])) {
            return self::fieldEntry($sheet, $column);
        }

        [$ref, $analytics, $columns] = self::SHEETS[$sheet];
        $spec = $columns[$column] ?? null;

        if ($spec === null) {
            return null;
        }

        if (is_string($spec)) {
            return ['kamus' => $column, 'analytics' => $analytics, 'ref' => $ref, 'status' => $spec];
        }

        return ['kamus' => $spec[1], 'analytics' => $spec[3], 'ref' => $spec[2], 'status' => $spec[0]];
    }

    /** @return list<string> columns with an entry in one sheet */
    public static function columns(string $sheet): array
    {
        if (isset(self::SHEETS[$sheet])) {
            return array_keys(self::SHEETS[$sheet][2]);
        }

        return FieldSheetSpec::has($sheet) ? ['field_session_code', 'sheet_row', ...array_keys(FieldSheetSpec::columns($sheet))] : [];
    }

    /** @return list<string> every anchor used */
    public static function refs(): array
    {
        $refs = [self::FIELD];

        foreach (self::SHEETS as [$ref, , $columns]) {
            $refs[] = $ref;

            foreach ($columns as $spec) {
                if (is_array($spec)) {
                    $refs[] = $spec[2];
                }
            }
        }

        return array_values(array_unique($refs));
    }

    /**
     * Field sheet columns: the codes of 29 §10.7, joined to the log by 11's
     * merge rules.
     *
     * @return array{kamus: string, analytics: list<int>, ref: string, status: string}|null
     */
    private static function fieldEntry(string $sheet, string $column): ?array
    {
        if (! in_array($column, self::columns($sheet), true)) {
            return null;
        }

        return ['kamus' => $column, 'analytics' => [], 'ref' => self::FIELD, 'status' => 'baru'];
    }
}
