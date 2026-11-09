<?php

namespace App\Libraries\Derived;

/**
 * The raw data of one student in one study, loaded once by
 * DerivedDataService (35 §4.1, K5) or written by hand in a test. Times may
 * be given as stored WIB strings or as ms; fromArray() turns them into ms
 * (event and playback times already corrected with their clock offset).
 *
 * Shapes (all lists in any order; the calculators sort):
 * - participant: id, code, school_code, booklet, deactivated_at, assented_at, corrected_at
 * - consent: parent_consent_on (Y-m-d), status, status_on (Y-m-d), or null (no row)
 * - nodes: id => [id, level_seq, sequence, engine]
 * - items: id => [id, item_key, node_id, variant_group, interaction_type, dimension, scorable, key_box ([x, y, w, h] % or null)]
 * - itemAudio: audio asset id => item id (the item's own narration)
 * - hintItems: hint id => item id (a Bantuan of one item)
 * - fieldSessions: id => [id, meeting_no, makeup_no, date, opens_at, closes_at, closed_at, regions (level sequences)]
 * - sessions: id => [id, field_session_id, meeting_no, started_at, last_active_at, ended_at]
 * - attempts: id => [id, session_id, node_id, attempt_no, variant_group, status, started_at, completed_at, is_retake, hint_count]
 * - segments: [attempt_id, session_id, field_session_id, segment_no, opened_at, left_at]
 * - tries: id => [id, attempt_id, item_id, try_no, is_correct, error_code, error_code_latest, answer_hash,
 *   ready_ms, first_input_ms, last_input_ms, occurred_at, hint_opened_before, action_key, action_score,
 *   reason_code, reason_ready_ms, action_last_input_ms, reason_last_input_ms]
 * - events: [id, session_id, type, at, offset_known, attempt_id, item_id, payload]
 * - audio: [session_id, attempt_id, audio_asset_id, action, play_index, at, listened_ms, context]
 * - fieldRows: [id, field_session_id, sheet, mine, seat_no, started_at, ended_at, values]
 * - importedSheets: field session id => sheets; seats: field session id => seat number
 * - sittings: [id, occasion, kind, started_at, submitted_at, field_session_id, parts (part => [raw_score, submitted_at, closed_at]),
 *   blocks (block => closed_reason), responses: [id, unit, item_code, response, not_reached, last_view_ms, first_shown_at, after_stimulus]]
 * - questionnaireItems: unit => item code (ang-02 …)
 */
final class RunInput
{
    private const TIMES = [
        'participant'   => ['deactivated_at', 'assented_at', 'corrected_at'],
        'fieldSessions' => ['opens_at', 'closes_at', 'closed_at'],
        'sessions'      => ['started_at', 'last_active_at', 'ended_at'],
        'attempts'      => ['started_at', 'completed_at'],
        'segments'      => ['opened_at', 'left_at'],
        'tries'         => ['occurred_at'],
        'events'        => ['at'],
        'audio'         => ['at'],
        'fieldRows'     => ['started_at', 'ended_at'],
        'sittings'      => ['started_at', 'submitted_at'],
    ];

    public array $participant = [];
    public ?array $consent    = null;
    public array $nodes       = [];
    public array $items       = [];
    public array $itemAudio   = [];
    public array $hintItems   = [];
    public array $fieldSessions = [];
    public array $sessions    = [];
    public array $attempts    = [];
    public array $segments    = [];
    public array $tries       = [];
    public array $events      = [];
    public array $audio       = [];
    public array $fieldRows   = [];
    public array $importedSheets = [];
    public array $seats       = [];
    public array $sittings    = [];
    public array $questionnaireItems = [];

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $input = new self();

        foreach (get_object_vars($input) as $name => $default) {
            if (! array_key_exists($name, $data)) {
                continue;
            }

            $value = $data[$name];

            if (isset(self::TIMES[$name]) && is_array($value)) {
                $value = $name === 'participant' ? self::times($value, self::TIMES[$name]) : array_map(static fn (array $row): array => self::times($row, self::TIMES[$name]), $value);
            }

            $input->{$name} = $value;
        }

        foreach ($input->sittings as &$sitting) {
            foreach ((array) ($sitting['parts'] ?? []) as $part => $row) {
                $sitting['parts'][$part] = self::times($row, ['submitted_at', 'closed_at']);
            }

            $sitting['responses'] = array_map(static fn (array $r): array => self::times($r, ['first_shown_at']), (array) ($sitting['responses'] ?? []));
        }
        unset($sitting);

        return $input;
    }

    /** @param list<string> $columns */
    private static function times(array $row, array $columns): array
    {
        foreach ($columns as $column) {
            if (array_key_exists($column, $row)) {
                $row[$column] = Clock::ms($row[$column]);
            }
        }

        return $row;
    }
}
