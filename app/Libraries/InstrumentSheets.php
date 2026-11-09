<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * The test module's download sheets (research revision 10, part 8: 34
 * §11.4; 29 §7.2, §7.6, K9): `tes_jawaban`, `tes_bagian`, `angket`, `sikap`,
 * `risiko_gabungan`, and the `peserta` columns `pre_form` … `post_capped`.
 * RawDataService reads its columns, keys, queries, and values from here.
 *
 * - Preview sittings (`is_preview = 1`) never appear (34 §5.5).
 * - The examples `tes-ct-*` and `skp-ct-01` are left out of `tes_jawaban`;
 *   `skp_ct_01` is a raw column of `sikap` (17).
 * - `risk` sittings never reach a per-student sheet: their kind is excluded in
 *   the queries themselves (34 §8.3). `risiko_gabungan` counts students per
 *   risk item and choice for the whole study, then per school code; a cell
 *   under 5 students reads `<5` (29 §7.6, 26 → P9).
 * - An empty answer is written `-` (13, 16, 17).
 * - `rapid_guess`, statuses, indexes, and scores beyond `raw_score` are part 9's (35).
 */
final class InstrumentSheets
{
    public const DIMENSIONS = ['tes_jawaban', 'tes_bagian', 'angket', 'sikap', 'risiko_gabungan'];

    /** The aggregate sheet: built in PHP, not from a row query. */
    public const AGGREGATE = 'risiko_gabungan';

    /** Answer columns of the attitude scale, in order (17 → Variabel). */
    public const ATTITUDE_UNITS = ['skp_ct_01', 'skp_01', 'skp_02', 'skp_03', 'skp_04', 'skp_05', 'skp_06', 'skp_07', 'skp_08', 'skp_09', 'skp_10', 'skp_11', 'skp_12'];

    /** `peserta` columns from the module (13 → Penskoran; 29 §7.3). */
    public const PARTICIPANT_COLUMNS = [
        'pre_form' => null, 'post_form' => null, 'pre_date' => null, 'post_date' => null, 'pre_makeup' => null, 'post_makeup' => null,
        'pre_d1' => null, 'pre_d2' => null, 'pre_d3' => null, 'post_d1' => null, 'post_d2' => null, 'post_d3' => null,
        'pre_minutes_p1' => null, 'pre_minutes_p23' => null, 'post_minutes_p1' => null, 'post_minutes_p23' => null,
        'pre_capped' => null, 'post_capped' => null,
    ];

    /** Column → SQL expression for sorting (null: computed). */
    public const COLUMNS = [
        'tes_jawaban' => [
            'participant_code' => 'participants.participant_code', 'occasion' => 'instrument_sittings.occasion', 'form' => 'instrument_sittings.form',
            'item_code' => 'instrument_items.code', 'response' => 'instrument_responses.response', 'score' => 'instrument_responses.score',
            'error_codes' => 'instrument_responses.error_codes', 'not_reached' => 'instrument_responses.not_reached',
            'time_on_item' => 'instrument_responses.time_on_item_ms', 'answer_changes' => 'instrument_responses.answer_changes',
            'instrument_version' => 'instrument_versions.version',
        ],
        'tes_bagian' => [
            'participant_code' => 'participants.participant_code', 'occasion' => 'instrument_sittings.occasion', 'part' => 'instrument_parts.part',
            'block' => 'instrument_blocks.block', 'started_at' => 'instrument_parts.opened_at', 'submitted_at' => 'instrument_parts.submitted_at',
            'closed_by_limit' => 'instrument_parts.closed_by_limit', 'extra_minutes' => 'instrument_blocks.extra_minutes',
            'raw_score' => 'instrument_parts.raw_score', 'answered_count' => 'instrument_parts.answered_count',
            'is_makeup' => 'instrument_sittings.is_makeup', 'field_session_code' => 'field_sessions.code',
        ],
        'angket' => [
            'participant_code' => 'participants.participant_code', 'questionnaire_date' => 'instrument_sittings.started_at',
            'questionnaire_makeup' => 'instrument_sittings.is_makeup', 'questionnaire_late' => 'instrument_sittings.late', 'questionnaire_minutes' => null,
            // 41 columns ang-01–ang-17, filled below (16 → Pemasukan data)
        ],
        'sikap' => [
            'participant_code' => 'participants.participant_code',
            // skp_ct_01 … skp_12, skp_01_ms … skp_12_ms, then the rest (columnMap())
        ],
        'risiko_gabungan' => [
            'school_code' => null, 'item_code' => null, 'unit' => null, 'choice' => null, 'students' => null,
        ],
    ];

    public const KEYS = [
        'tes_jawaban' => 'instrument_responses.id',
        'tes_bagian'  => 'instrument_parts.id',
        'angket'      => 'instrument_sittings.id',
        'sikap'       => 'instrument_sittings.id',
        'risiko_gabungan' => '_key',
    ];

    public const DEFAULT_SORT = [
        'tes_jawaban' => 'participant_code', 'tes_bagian' => 'participant_code', 'angket' => 'participant_code',
        'sikap' => 'participant_code', 'risiko_gabungan' => 'item_code',
    ];

    /** Smallest group written as a number (29 §7.6). */
    public const MIN_CELL = 5;

    /** Choice categories that are no answer code (29 §7.6). */
    public const NOT_OFFERED = 'tidak_diberi';
    public const REFUSED     = 'tidak_mau_mengisi';
    public const EMPTY       = '-';

    /** @var array<int, array<string, array<string, mixed>>> sitting id → unit → response row */
    private array $responses = [];

    /** @var array<string, array<string, mixed>> participant columns per "participant:study" */
    private array $participants = [];

    public function __construct(private BaseConnection $db)
    {
    }

    /** @return array<string, string|null> column map of one sheet */
    public static function columnMap(string $sheet): array
    {
        $map = self::COLUMNS[$sheet];

        if ($sheet === 'angket') {
            foreach (InstrumentImporter::QUESTIONNAIRE_UNITS as $unit) {
                $map[$unit] = null;
            }
        }

        if ($sheet === 'sikap') {
            foreach (self::ATTITUDE_UNITS as $unit) {
                $map[$unit] = null;
            }

            foreach (array_slice(self::ATTITUDE_UNITS, 1) as $unit) {
                $map[$unit . '_ms'] = null;
            }

            $map += ['skp_changes' => null, 'sikap_makeup' => 'instrument_sittings.is_makeup', 'sikap_submitted_at' => 'instrument_sittings.submitted_at',
                'sikap_version' => 'instrument_versions.version', 'feedback_rating' => null];
        }

        return $map;
    }

    /**
     * Builder of one per-student sheet. $participantFilters applies the
     * raw-data participant filters and exclusions (test accounts, simulated
     * students) on `participants`.
     *
     * @param array<string, int|string>           $filters
     * @param callable(BaseBuilder, array, string): void $participantFilters
     */
    public function query(string $sheet, array $filters, callable $participantFilters): BaseBuilder
    {
        $builder = match ($sheet) {
            'tes_jawaban' => $this->db->table('instrument_responses')
                ->join('instrument_sittings', 'instrument_sittings.id = instrument_responses.sitting_id')
                ->join('instrument_items', 'instrument_items.id = instrument_responses.item_id')
                ->select('instrument_responses.id AS _key, instrument_sittings.occasion, instrument_sittings.form, instrument_items.code AS item_code')
                ->select('instrument_responses.response, instrument_responses.score, instrument_responses.error_codes, instrument_responses.not_reached')
                ->select('instrument_responses.time_on_item_ms AS time_on_item, instrument_responses.answer_changes')
                ->where('instrument_sittings.kind', 'test')->where('instrument_items.part !=', 'ct'),
            'tes_bagian' => $this->db->table('instrument_parts')
                ->join('instrument_sittings', 'instrument_sittings.id = instrument_parts.sitting_id')
                ->join('instrument_blocks', 'instrument_blocks.id = instrument_parts.block_id', 'left')
                ->join('field_sessions', 'field_sessions.id = instrument_sittings.field_session_id', 'left')
                ->select('instrument_parts.id AS _key, instrument_sittings.occasion, instrument_parts.part, instrument_blocks.block')
                ->select('instrument_parts.opened_at AS started_at, instrument_parts.submitted_at, instrument_parts.closed_by_limit')
                ->select('instrument_blocks.extra_minutes, instrument_parts.raw_score, instrument_parts.answered_count')
                ->select('instrument_sittings.is_makeup, field_sessions.code AS field_session_code')
                ->where('instrument_sittings.kind', 'test')->whereIn('instrument_parts.part', ['mb', 'ae', 'bd']),
            'angket' => $this->db->table('instrument_sittings')
                ->select('instrument_sittings.id AS _key, instrument_sittings.started_at, instrument_sittings.submitted_at')
                ->select('instrument_sittings.is_makeup, instrument_sittings.late')
                ->where('instrument_sittings.kind', 'questionnaire'),
            'sikap' => $this->db->table('instrument_sittings')
                ->select('instrument_sittings.id AS _key, instrument_sittings.participant_id, instrument_sittings.study_id')
                ->select('instrument_sittings.is_makeup, instrument_sittings.submitted_at')
                ->where('instrument_sittings.kind', 'attitude'),
        };

        // Every sheet: never a preview sitting, never a risk sitting (34 §8.3), the content version, the student
        $builder->join('instrument_versions', 'instrument_versions.id = instrument_sittings.version_id')
            ->join('participants', 'participants.id = instrument_sittings.participant_id')
            ->select('participants.participant_code, instrument_versions.version AS instrument_version')
            ->where('instrument_sittings.is_preview', 0)->where('instrument_sittings.kind !=', 'risk')
            ->where('participants.deleted_at', null);

        if (isset($filters['study_id'])) {
            $builder->where('instrument_sittings.study_id', (int) $filters['study_id']);
        }

        if (isset($filters['field_session_code'])) {
            $builder->whereIn('instrument_sittings.field_session_id', static fn (BaseBuilder $sub): BaseBuilder => $sub->select('id')->from('field_sessions')->where('code', $filters['field_session_code']));
        }

        $participantFilters($builder, array_diff_key($filters, array_flip(['date_from', 'date_to'])), 'instrument_sittings.started_at');

        return $builder;
    }

    /**
     * Values of one row (before the column order and type normalisation).
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, mixed>
     */
    public function values(string $sheet, array $row): array
    {
        switch ($sheet) {
            case 'tes_jawaban':
                $row['response'] ??= self::EMPTY;

                return $row;

            case 'angket':
                $answers = $this->responses((int) $row['_key']);
                $out     = $row + [
                    'questionnaire_date'    => $row['started_at'] === null ? null : substr((string) $row['started_at'], 0, 10),
                    'questionnaire_makeup'  => $row['is_makeup'],
                    'questionnaire_late'    => $row['late'],
                    'questionnaire_minutes' => self::minutes($row['started_at'], $row['submitted_at']),
                ];

                foreach (InstrumentImporter::QUESTIONNAIRE_UNITS as $unit) {
                    $out[$unit] = $answers[$unit]['response'] ?? self::EMPTY;
                }

                return $out;

            case 'sikap':
                $answers = $this->responses((int) $row['_key']);
                $out     = $row + ['sikap_makeup' => $row['is_makeup'], 'sikap_submitted_at' => $row['submitted_at'], 'sikap_version' => $row['instrument_version']];
                $changes = 0;

                foreach (self::ATTITUDE_UNITS as $unit) {
                    $out[$unit] = $answers[$unit]['response'] ?? self::EMPTY;

                    if ($unit !== 'skp_ct_01') {
                        $out[$unit . '_ms'] = $answers[$unit]['last_view_ms'] ?? null;
                        $changes += (int) ($answers[$unit]['answer_changes'] ?? 0);
                    }
                }

                $out['skp_changes']     = $changes;
                $out['feedback_rating'] = $this->feedbackRating((int) $row['participant_id'], (int) $row['study_id']);

                return $out;
        }

        return $row;
    }

    /**
     * The `peserta` columns of one student (13): per occasion the form, the
     * date, make-up, the raw score of the three parts, minutes per block,
     * and the blocks closed by their limit (`p1`, `p23`, `p1;p23`, or empty).
     *
     * @return array<string, mixed>
     */
    public function participantColumns(int $participantId, ?int $studyId): array
    {
        $key = $participantId . ':' . (int) $studyId;

        if (isset($this->participants[$key])) {
            return $this->participants[$key];
        }

        $sittings = $this->db->table('instrument_sittings')
            ->where('participant_id', $participantId)->where('kind', 'test')->where('is_preview', 0);

        if ($studyId !== null) {
            $sittings->where('study_id', $studyId);
        }

        $out = self::PARTICIPANT_COLUMNS;

        foreach ($sittings->orderBy('id', 'ASC')->get()->getResultArray() as $sitting) {
            $o = (string) $sitting['occasion'];

            if (! in_array($o, ['pre', 'post'], true)) {
                continue;
            }

            $parts  = array_column($this->db->table('instrument_parts')->where('sitting_id', (int) $sitting['id'])->get()->getResultArray(), null, 'part');
            $blocks = array_column($this->db->table('instrument_blocks')->where('sitting_id', (int) $sitting['id'])->get()->getResultArray(), null, 'block');

            $out[$o . '_form']   = $sitting['form'];
            $out[$o . '_date']   = substr((string) $sitting['started_at'], 0, 10);
            $out[$o . '_makeup'] = (int) $sitting['is_makeup'];

            foreach (['mb' => 'd1', 'ae' => 'd2', 'bd' => 'd3'] as $part => $column) {
                $out[$o . '_' . $column] = $parts[$part]['raw_score'] ?? null;
            }

            foreach (['p1' => ['mb'], 'p23' => ['ae', 'bd']] as $block => $codes) {
                $last = null;

                foreach ($codes as $code) {
                    $end  = $parts[$code]['submitted_at'] ?? $parts[$code]['closed_at'] ?? null;
                    $last = $end !== null && ($last === null || $end > $last) ? $end : $last;
                }

                $out[$o . '_minutes_' . $block] = isset($blocks[$block]) ? self::minutes($blocks[$block]['started_at'], $blocks[$block]['closed_at'] ?? $last) : null;
            }

            $capped = array_keys(array_filter($blocks, static fn (array $b): bool => $b['closed_reason'] === 'limit'));
            sort($capped);
            $out[$o . '_capped'] = $capped === [] ? null : implode(';', $capped);
        }

        return $this->participants[$key] = $out;
    }

    /**
     * Rows of `risiko_gabungan` (29 §7.6): per risk unit and choice, the
     * number of students in the whole study (`school_code` = `semua`), then
     * per school code. `risk_offered = 0` counts as `tidak_diberi`,
     * `risk_reconsent = 0` as `tidak_mau_mengisi`, code `7` as itself
     * (tidak mau menjawab), an empty answer as `-`. Under 5 → `<5`.
     *
     * @param array<string, int|string>                 $filters
     * @param callable(BaseBuilder, array, string): void $participantFilters
     *
     * @return list<array<string, mixed>>
     */
    public function riskAggregate(array $filters, callable $participantFilters): array
    {
        $version = $this->db->table('instrument_versions')->where('instrument', 'angket')->where('is_active', 1)->orderBy('id', 'DESC')->get(1)->getRowArray();

        if ($version === null) {
            return [];
        }

        $items = $this->db->table('instrument_items')->where('version_id', (int) $version['id'])->where('part', 'risk')->orderBy('position', 'ASC')->get()->getResultArray();

        $sittings = $this->db->table('instrument_sittings')
            ->join('participants', 'participants.id = instrument_sittings.participant_id')
            ->join('schools', 'schools.id = participants.school_id', 'left')
            ->select('instrument_sittings.id, instrument_sittings.risk_offered, instrument_sittings.risk_reconsent, schools.research_code')
            ->where('instrument_sittings.kind', 'risk')->where('instrument_sittings.is_preview', 0)->where('participants.deleted_at', null);

        if (isset($filters['study_id'])) {
            $sittings->where('instrument_sittings.study_id', (int) $filters['study_id']);
        }

        $participantFilters($sittings, array_diff_key($filters, array_flip(['date_from', 'date_to', 'participant_id', 'participant_code'])), 'instrument_sittings.started_at');
        $sittings = $sittings->get()->getResultArray();
        $answers  = [];

        foreach (array_chunk(array_column($sittings, 'id'), 500) as $ids) {
            foreach ($this->db->table('instrument_responses')->select('sitting_id, unit, response')->whereIn('sitting_id', $ids)->get()->getResultArray() as $row) {
                $answers[(int) $row['sitting_id']][(string) $row['unit']] = $row['response'];
            }
        }

        $counts = [];
        $scopes = [];

        foreach ($sittings as $sitting) {
            $school = (string) ($sitting['research_code'] ?? '—');
            $scopes[$school] = true;

            foreach ($items as $item) {
                foreach ((array) json_decode((string) $item['units_json'], true) as $unit) {
                    $choice = match (true) {
                        (int) $sitting['risk_offered'] !== 1   => self::NOT_OFFERED,
                        (int) $sitting['risk_reconsent'] !== 1 => self::REFUSED,
                        default                                => (string) ($answers[(int) $sitting['id']][$unit] ?? self::EMPTY),
                    };

                    foreach (['semua', $school] as $scope) {
                        $counts[$scope][$item['code']][$unit][$choice] = ($counts[$scope][$item['code']][$unit][$choice] ?? 0) + 1;
                    }
                }
            }
        }

        ksort($scopes);
        $rows = [];
        $key  = 0;

        foreach (['semua', ...array_keys($scopes)] as $scope) {
            foreach ($items as $item) {
                $show    = (array) json_decode((string) $item['show_json'], true);
                $choices = $item['type'] === 'multi' ? ['1', '0', '7'] : array_map('strval', array_keys((array) ($show['options'] ?? [])));

                foreach ((array) json_decode((string) $item['units_json'], true) as $unit) {
                    foreach ([...$choices, self::EMPTY, self::REFUSED, self::NOT_OFFERED] as $choice) {
                        $n      = $counts[$scope][$item['code']][$unit][$choice] ?? 0;
                        $rows[] = [
                            '_key' => ++$key, 'school_code' => $scope, 'item_code' => $item['code'], 'unit' => $unit,
                            'choice' => $choice, 'students' => $n < self::MIN_CELL ? '<5' : (string) $n,
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    // ------------------------------------------------------------ internal

    /** @return array<string, array<string, mixed>> */
    private function responses(int $sittingId): array
    {
        return $this->responses[$sittingId] ??= array_column(
            $this->db->table('instrument_responses')->select('unit, response, last_view_ms, answer_changes')->where('sitting_id', $sittingId)->get()->getResultArray(),
            null,
            'unit',
        );
    }

    /** `feedback_rating` (17): the newest rating of the student's feedback in the study, NULL when left empty. */
    private function feedbackRating(int $participantId, int $studyId): ?int
    {
        $row = $this->db->table('participant_feedback')
            ->join('game_sessions', 'game_sessions.id = participant_feedback.session_id', 'left')
            ->select('participant_feedback.rating')
            ->where('participant_feedback.participant_id', $participantId)
            ->groupStart()->where('game_sessions.study_id', $studyId)->orWhere('participant_feedback.session_id', null)->groupEnd()
            ->orderBy('participant_feedback.id', 'DESC')->get(1)->getRowArray();

        return $row === null || $row['rating'] === null ? null : (int) $row['rating'];
    }

    /** Minutes between two stored times, one decimal; null when either is missing. */
    private static function minutes(mixed $from, mixed $to): ?string
    {
        if ($from === null || $to === null) {
            return null;
        }

        $ms = \App\Services\InstrumentService::ms((string) $to) - \App\Services\InstrumentService::ms((string) $from);

        return number_format(max(0, $ms) / 60000, 1, '.', '');
    }
}
