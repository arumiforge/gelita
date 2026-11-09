<?php

namespace App\Libraries\Derived;

/**
 * `derived_participants`, one row per student (35 §6.5, §7): consent and
 * the withdrawal cutoff, completeness, the questionnaire indexes of 16, the
 * attitude scores of 17, and the test status of 13. Pure.
 */
final class ParticipantCalculator
{
    /** Codes counted as missing in the questionnaire (16 → Pemasukan data). */
    public const MISSING = ['-', '8', '9', ''];

    /** Attitude scale (17 → Kisi-kisi): indicator => items; negatives are reversed (5 − code). */
    public const ATTITUDE_INDICATORS = [
        'sikap_sk1' => ['skp_02', 'skp_09', 'skp_06'],
        'sikap_sk2' => ['skp_05', 'skp_11', 'skp_03'],
        'sikap_sk3' => ['skp_04', 'skp_12', 'skp_08'],
        'sikap_sk4' => ['skp_01', 'skp_07', 'skp_10'],
    ];

    public const ATTITUDE_NEGATIVES = ['skp_03', 'skp_06', 'skp_08', 'skp_10'];

    public const ATTITUDE_MIN_ANSWERED = 10;

    /** Questionnaire items allowed empty for `lengkap` (16). */
    public const QUESTIONNAIRE_MAX_EMPTY = 2;

    /**
     * The withdrawal cutoff (35 §7): for `withdrawn_keep`, `deactivated_at`
     * when filled, else the end of `status_on` (23:59:59.999 WIB).
     *
     * @return array{withdrawn_on: ?string, withdrawn_at: ?int, cutoff_basis: ?string}
     */
    public static function cutoff(array $participant, ?array $consent): array
    {
        if (($consent['status'] ?? null) !== 'withdrawn_keep') {
            return ['withdrawn_on' => null, 'withdrawn_at' => null, 'cutoff_basis' => null];
        }

        $on = $consent['status_on'] ?? null;

        if (($participant['deactivated_at'] ?? null) !== null) {
            return ['withdrawn_on' => $on, 'withdrawn_at' => (int) $participant['deactivated_at'], 'cutoff_basis' => 'deactivated_at'];
        }

        return ['withdrawn_on' => $on, 'withdrawn_at' => $on === null ? null : Clock::endOfDay((string) $on), 'cutoff_basis' => $on === null ? null : 'status_on'];
    }

    /** consent_ok (35 §7): parent consent filled, child assent ticked, status active, left or withdrawn_keep. */
    public static function consentOk(array $participant, ?array $consent): int
    {
        if ($consent === null || empty($consent['parent_consent_on']) || ($participant['assented_at'] ?? null) === null) {
            return 0;
        }

        return in_array($consent['status'] ?? 'active', ['active', 'left', 'withdrawn_keep'], true) ? 1 : 0;
    }

    /**
     * Questionnaire derivations (16 → Variabel turunan). An index is empty
     * when one of its parts is missing (`-`, `8`, `9`); no imputation.
     *
     * @param array<string, string>|null $answers unit => raw answer; null = no questionnaire
     * @param array<string, string>      $units   unit => item code (ang-01 …)
     *
     * @return array<string, mixed>
     */
    public static function questionnaire(?array $answers, array $units): array
    {
        $out = array_fill_keys(['questionnaire_status', 'own_phone', 'device_count', 'access_index', 'activity_breadth', 'own_accounts',
            'social_media_own', 'social_media_use', 'parent_active', 'parent_rules', 'safety_info_sources', 'game_time_group', 'q_flags'], null);

        if ($answers === null || $answers === [] || array_filter($answers, static fn ($v): bool => ! in_array(trim((string) $v), self::MISSING, true)) === []) {
            $out['questionnaire_status'] = 'hilang';

            return $out;
        }

        $v    = static fn (string $unit): ?int => in_array($raw = trim((string) ($answers[$unit] ?? '-')), self::MISSING, true) || ! is_numeric($raw) ? null : (int) $raw;
        $all  = static fn (array $list): ?array => in_array(null, $values = array_map($v, $list), true) ? null : $values;
        $sum  = static fn (array $list): ?int => ($vals = $all($list)) === null ? null : array_sum($vals);
        $hits = static fn (array $list, array $codes): ?int => ($vals = $all($list)) === null ? null : count(array_filter($vals, static fn (int $x): bool => in_array($x, $codes, true)));

        // Empty items: every unit of the item is empty
        $byItem = [];

        foreach ($units as $unit => $item) {
            $byItem[$item][] = $unit;
        }

        $empty = 0;

        foreach ($byItem as $list) {
            if (array_filter($list, static fn (string $u): bool => trim((string) ($answers[$u] ?? '-')) !== '-') === []) {
                $empty++;
            }
        }

        $out['questionnaire_status'] = $empty <= self::QUESTIONNAIRE_MAX_EMPTY ? 'lengkap' : 'sebagian';

        $phone             = $v('phone_access');
        $out['own_phone']  = $phone === null ? null : ($phone === 2 ? 1 : 0);
        $devices           = $sum(['home_phone', 'home_tablet', 'home_computer', 'home_console']);
        $out['device_count'] = $devices;
        $internet          = $v('home_internet');
        $out['access_index'] = $phone === null || $devices === null || $internet === null ? null : $phone + $devices + $internet;
        $out['activity_breadth'] = $hits(['act_video', 'act_game', 'act_chat', 'act_schoolwork', 'act_search', 'act_read', 'act_post'], [1, 2]);
        $out['own_accounts']     = $hits(['acct_whatsapp', 'acct_youtube', 'acct_tiktok', 'acct_instagram', 'acct_facebook', 'acct_game'], [2]);
        $social                  = ['acct_tiktok', 'acct_instagram', 'acct_facebook'];
        $own                     = $hits($social, [2]);
        $use                     = $hits($social, [1, 2]);
        $out['social_media_own'] = $own === null ? null : ($own > 0 ? 1 : 0);
        $out['social_media_use'] = $use === null ? null : ($use > 0 ? 1 : 0);
        $parent                  = $all(['parent_couse', 'parent_talk']);
        $out['parent_active']    = $parent === null ? null : round(array_sum($parent) / 2, 4);
        $out['parent_rules']     = $hits(['parent_rule_time', 'parent_rule_content'], [1]);
        $out['safety_info_sources'] = $sum(['safety_info_parent', 'safety_info_teacher', 'safety_info_family', 'safety_info_friend', 'safety_info_media']);
        $game                    = $v('game_time_school');
        $out['game_time_group']  = $game === null ? null : ($game <= 1 ? 'rendah' : ($game <= 3 ? 'sedang' : 'tinggi'));

        // q_flags (16): answers that contradict each other; the raw data stays as it is
        $flags = [];
        $one   = static fn (string $unit): bool => $v($unit) === 1;

        if ($one('home_device_none') && array_filter(['home_phone', 'home_tablet', 'home_computer', 'home_console'], $one) !== []) {
            $flags[] = 'ang-02';
        }

        if (($one('safety_info_none') || $one('safety_info_dk')) && array_filter(['safety_info_parent', 'safety_info_teacher', 'safety_info_family', 'safety_info_friend', 'safety_info_media'], $one) !== []) {
            $flags[] = 'ang-12';
        }

        if ($phone === 0 && array_filter(['acct_whatsapp', 'acct_youtube', 'acct_tiktok', 'acct_instagram', 'acct_facebook', 'acct_game'], static fn (string $u): bool => $v($u) === 2) !== []) {
            $flags[] = 'ang-01;ang-08';
        }

        if ($v('game_time_school') === 0 && $v('game_time_holiday') === 0 && $v('act_game') === 2) {
            $flags[] = 'ang-06;ang-07';
        }

        $out['q_flags'] = $flags === [] ? null : implode(';', $flags);

        return $out;
    }

    /**
     * Attitude scale (17 → Penskoran).
     *
     * @param array<string, string>|null $answers unit => raw 1–4 or `-`; null = no sitting submitted
     *
     * @return array<string, mixed>
     */
    public static function attitude(?array $answers): array
    {
        $out = array_fill_keys(['sikap_score', 'sikap_sk1', 'sikap_sk2', 'sikap_sk3', 'sikap_sk4', 'sikap_n_answered', 'sikap_flag_straight'], null);

        if ($answers === null) {
            return $out;
        }

        $raw    = [];
        $scores = [];

        foreach (self::ATTITUDE_INDICATORS as $items) {
            foreach ($items as $unit) {
                $code = trim((string) ($answers[$unit] ?? '-'));

                if (in_array($code, ['1', '2', '3', '4'], true)) {
                    $raw[$unit]    = (int) $code;
                    $scores[$unit] = in_array($unit, self::ATTITUDE_NEGATIVES, true) ? 5 - (int) $code : (int) $code;
                }
            }
        }

        $out['sikap_n_answered']    = count($scores);
        $out['sikap_score']         = count($scores) >= self::ATTITUDE_MIN_ANSWERED ? round(array_sum($scores) / count($scores), 4) : null;
        $out['sikap_flag_straight'] = count($raw) === 12 && count(array_unique($raw)) === 1 ? 1 : 0;

        foreach (self::ATTITUDE_INDICATORS as $column => $items) {
            $have           = array_intersect_key($scores, array_flip($items));
            $out[$column]   = count($have) === 3 ? round(array_sum($have) / 3, 4) : null;
        }

        return $out;
    }

    /**
     * Test status and part scores (13): `lengkap`, `hanya_awal`, `hanya_akhir`, `hilang`.
     *
     * @param list<array<string, mixed>> $sittings the student's test sittings (cut at the withdrawal)
     *
     * @return array<string, mixed>
     */
    public static function tests(array $sittings): array
    {
        $out  = array_fill_keys(['test_status', 'pre_d1', 'pre_d2', 'pre_d3', 'post_d1', 'post_d2', 'post_d3', 'pre_capped', 'post_capped'], null);
        $done = ['pre' => false, 'post' => false];

        foreach ($sittings as $sitting) {
            $o = (string) $sitting['occasion'];

            if (! isset($done[$o])) {
                continue;
            }

            $parts = (array) ($sitting['parts'] ?? []);

            foreach (['mb' => 'd1', 'ae' => 'd2', 'bd' => 'd3'] as $part => $column) {
                $part = $parts[$part] ?? null;
                $out[$o . '_' . $column] = $part === null || $part['raw_score'] === null ? null : (int) $part['raw_score'];

                if ($part !== null && (($part['submitted_at'] ?? null) !== null || ($part['closed_at'] ?? null) !== null)) {
                    $done[$o] = true;
                }
            }

            $done[$o] = $done[$o] || ($sitting['submitted_at'] ?? null) !== null;
            $capped   = array_keys(array_filter((array) ($sitting['blocks'] ?? []), static fn ($reason): bool => $reason === 'limit'));
            sort($capped);
            $out[$o . '_capped'] = $capped === [] ? null : implode(';', $capped);
        }

        $out['test_status'] = match (true) {
            $done['pre'] && $done['post'] => 'lengkap',
            $done['pre']                  => 'hanya_awal',
            $done['post']                 => 'hanya_akhir',
            default                       => 'hilang',
        };

        return $out;
    }
}
