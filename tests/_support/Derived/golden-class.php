<?php

/**
 * Part 9 (35 §16.1): the hand-computed sample class ("kelas contoh"), raw
 * data as RunInput arrays, never a database. Three students of S03 / U01:
 *
 * - A (1, seat 1, booklet B1): P2 (field session 1, 10 Nov) plays Susun
 *   Gambar, Isi Rumpang (exhausted), Benar atau Salah with its anchor card,
 *   and Cari Objek round 1, leaves it (challenge_abandoned) and finishes
 *   round 2 at P5 (field session 2, a make-up on 14 Nov), then Skenario Digital.
 * - B (2, seat 2, school U01 = pilot): P2 only, Isi Rumpang on a shared
 *   device (L2 until 08:12), then a retake.
 * - C (3, seat 3): withdrawn with data kept, account switched off at 08:12.
 *
 * Field sheets of P2: L1 conditions and a DEV-NET for every seat at 08:11,
 * L2, L3 ISI for seat 1 at 08:08:30, L4 seat 1 at 08:12:05. The expected
 * rows are in golden-*.csv beside this file, computed by hand from 11 and 35.
 */

$t = static fn (string $time, string $day = '2026-11-10'): string => $day . ' ' . $time;

$nodes = [
    1 => ['id' => 1, 'level_seq' => 1, 'sequence' => 1, 'engine' => 'puzzle'],
    2 => ['id' => 2, 'level_seq' => 1, 'sequence' => 2, 'engine' => 'rumpang'],
    3 => ['id' => 3, 'level_seq' => 1, 'sequence' => 3, 'engine' => 'boleh'],
    4 => ['id' => 4, 'level_seq' => 1, 'sequence' => 4, 'engine' => 'cari'],
    5 => ['id' => 5, 'level_seq' => 1, 'sequence' => 5, 'engine' => 'skenario'],
    6 => ['id' => 6, 'level_seq' => 2, 'sequence' => 1, 'engine' => 'rumpang'],
];
$item  = static fn (int $id, int $node, string $variant, string $type, string $dim, ?array $box = null): array => [
    'id' => $id, 'item_key' => 'i' . $id, 'node_id' => $node, 'variant_group' => $variant, 'interaction_type' => $type, 'dimension' => $dim, 'scorable' => 1, 'key_box' => $box,
];
$items = [
    21 => $item(21, 2, 'a', 'fill_blank_bank', 'D1'), 22 => $item(22, 2, 'a', 'fill_blank_bank', 'D1'),
    31 => $item(31, 3, 'a', 'verdict_card', 'D1'), 32 => $item(32, 3, 'x', 'verdict_card', 'D1'),
    33 => $item(33, 3, 'b', 'verdict_card', 'D1'),   // variant b: never the B1 booklet's
    41 => $item(41, 4, 'x', 'find_object', 'PBK', [10.0, 10.0, 10.0, 17.78]), 42 => $item(42, 4, 'a', 'find_part', 'D3', [50.0, 50.0, 20.0, 10.0]),
    51 => $item(51, 5, 'a', 'scenario_choice', 'D2'),
    61 => $item(61, 6, 'a', 'fill_blank_bank', 'D1'),
];
$fs1 = ['id' => 1, 'meeting_no' => 2, 'makeup_no' => 0, 'date' => '2026-11-10', 'opens_at' => $t('07:30:00'), 'closes_at' => $t('10:00:00'), 'closed_at' => null, 'regions' => [1]];
$fs2 = ['id' => 2, 'meeting_no' => 5, 'makeup_no' => 1, 'date' => '2026-11-14', 'opens_at' => $t('07:30:00', '2026-11-14'), 'closes_at' => $t('10:00:00', '2026-11-14'), 'closed_at' => null, 'regions' => [1, 2]];
$imported = [1 => ['l1_kondisi', 'l1_penyimpangan', 'l2_kursi', 'l3_bantuan', 'l4_bromp']];
$shared   = [
    ['id' => 101, 'field_session_id' => 1, 'sheet' => 'l1_kondisi', 'mine' => false, 'seat_no' => null, 'started_at' => $t('07:30:00'), 'ended_at' => null,
        'values' => ['headset_count' => 10, 'headset_of' => 20, 'internet' => 'wifi sekolah', 'noise_level' => 'sedang', 'teacher_presence' => 'ada', 'minutes_available' => 80, 'minutes_used' => 70]],
    ['id' => 102, 'field_session_id' => 1, 'sheet' => 'l1_penyimpangan', 'mine' => false, 'seat_no' => null, 'started_at' => $t('08:11:00'), 'ended_at' => null, 'values' => ['deviation_code' => 'DEV-NET', 'affected_seats' => 'semua']],
    ['id' => 103, 'field_session_id' => 1, 'sheet' => 'l3_bantuan', 'mine' => false, 'seat_no' => 1, 'started_at' => $t('08:08:30'), 'ended_at' => null, 'values' => ['seats' => '1', 'help_type' => 'ISI']],
    ['id' => 105, 'field_session_id' => 1, 'sheet' => 'l4_bromp', 'mine' => false, 'seat_no' => 1, 'started_at' => $t('08:12:05'), 'ended_at' => null, 'values' => ['behavior' => 'ON', 'emotion' => 'FOK']],
];
$consent = ['parent_consent_on' => '2026-11-01', 'status' => 'active', 'status_on' => null];
$try     = static fn (int $id, int $attempt, int $itemId, int $no, int $correct, string $at, array $extra = []): array => $extra + [
    'id' => $id, 'attempt_id' => $attempt, 'item_id' => $itemId, 'try_no' => $no, 'is_correct' => $correct, 'occurred_at' => $at,
    'hint_opened_before' => 0, 'answer_hash' => 'h' . $id, 'error_code' => null, 'error_code_latest' => null, 'ready_ms' => null, 'last_input_ms' => null,
];
$attempt = static fn (int $id, int $session, int $node, string $start, ?string $end, int $retake = 0): array => [
    'id' => $id, 'session_id' => $session, 'node_id' => $node, 'attempt_no' => 1, 'variant_group' => 'a', 'status' => $end === null ? 'in_progress' : 'completed',
    'started_at' => $start, 'completed_at' => $end, 'is_retake' => $retake, 'hint_count' => 0,
];
$event = static fn (int $id, int $session, string $type, string $at, ?int $attemptId = null, ?int $itemId = null, array $payload = []): array => [
    'id' => $id, 'session_id' => $session, 'type' => $type, 'at' => $at, 'offset_known' => 1, 'attempt_id' => $attemptId, 'item_id' => $itemId, 'payload' => $payload,
];

$base = ['nodes' => $nodes, 'items' => $items, 'importedSheets' => $imported];

return [
    // ------------------------------------------------------------ A
    $base + [
        'participant'   => ['id' => 1, 'code' => 'S03-A01', 'school_code' => 'S03', 'booklet' => 'B1', 'assented_at' => $t('07:40:00', '2026-11-03')],
        'consent'       => $consent,
        'fieldSessions' => [1 => $fs1, 2 => $fs2],
        'seats'         => [1 => 1, 2 => 1],
        'sessions'      => [
            1 => ['id' => 1, 'field_session_id' => 1, 'meeting_no' => 2, 'started_at' => $t('08:00:00')],
            2 => ['id' => 2, 'field_session_id' => 2, 'meeting_no' => 5, 'started_at' => $t('08:00:00', '2026-11-14')],
        ],
        'attempts' => [
            1 => $attempt(1, 1, 1, $t('08:01:00'), $t('08:03:00')),
            2 => $attempt(2, 1, 2, $t('08:03:00'), $t('08:06:00')),
            3 => $attempt(3, 1, 3, $t('08:06:00'), $t('08:10:00')),
            4 => $attempt(4, 1, 4, $t('08:10:00'), $t('08:05:00', '2026-11-14')),
            5 => $attempt(5, 2, 5, $t('08:05:00', '2026-11-14'), $t('08:08:00', '2026-11-14')),
        ],
        'segments' => [
            ['attempt_id' => 4, 'session_id' => 1, 'field_session_id' => 1, 'segment_no' => 1, 'opened_at' => $t('08:10:00'), 'left_at' => $t('08:20:00')],
            ['attempt_id' => 4, 'session_id' => 2, 'field_session_id' => 2, 'segment_no' => 2, 'opened_at' => $t('08:01:00', '2026-11-14'), 'left_at' => $t('08:05:00', '2026-11-14')],
        ],
        'tries' => [
            1  => $try(1, 2, 21, 1, 1, $t('08:05:00')),
            2  => $try(2, 2, 22, 1, 0, $t('08:05:00'), ['error_code' => 'INF-LIT', 'error_code_latest' => 'INF-LIT', 'answer_hash' => 'h1']),
            3  => $try(3, 2, 22, 2, 0, $t('08:05:30'), ['error_code' => 'INF-LIT', 'error_code_latest' => 'INF-LIT', 'answer_hash' => 'h1']),
            4  => $try(4, 2, 22, 3, 0, $t('08:06:00'), ['error_code' => 'INF-KAT', 'error_code_latest' => 'INF-KAT']),
            5  => $try(5, 3, 31, 1, 1, $t('08:08:00'), ['ready_ms' => 1000, 'last_input_ms' => 2500]),
            6  => $try(6, 3, 32, 1, 0, $t('08:08:00'), ['ready_ms' => 1000, 'last_input_ms' => 5000, 'error_code' => 'INF-SUM', 'error_code_latest' => 'INF-SUM']),
            7  => $try(7, 3, 32, 2, 1, $t('08:09:00'), ['ready_ms' => 0, 'last_input_ms' => 3500]),
            8  => $try(8, 4, 41, 1, 0, $t('08:12:00'), ['ready_ms' => 2000, 'last_input_ms' => 4000, 'error_code' => 'INF-LIT', 'error_code_latest' => 'INF-BUD']),
            9  => $try(9, 4, 41, 2, 1, $t('08:13:00'), ['ready_ms' => 500, 'last_input_ms' => 4500]),
            10 => $try(10, 4, 42, 1, 1, $t('08:03:00', '2026-11-14'), ['ready_ms' => 1000, 'last_input_ms' => 2000]),
            11 => $try(11, 5, 51, 1, 0, $t('08:06:00', '2026-11-14'), ['ready_ms' => 1000, 'action_last_input_ms' => 3000, 'reason_ready_ms' => 4000, 'reason_last_input_ms' => 9000,
                'action_key' => 't1', 'action_score' => 0, 'reason_code' => 'R-ACAK', 'error_code' => 'DIG-PRI', 'error_code_latest' => 'DIG-PRI']),
            12 => $try(12, 5, 51, 2, 1, $t('08:07:00', '2026-11-14'), ['ready_ms' => 1000, 'action_last_input_ms' => 5000, 'action_key' => 't2', 'action_score' => 2, 'reason_code' => 'R-RISK']),
        ],
        'events' => [
            $event(1, 1, 'input_idle', $t('08:09:50'), 3, null, ['idle_ms' => 200000]),
            $event(2, 1, 'empty_tap', $t('08:11:00'), 4, 41, ['x_pct' => 12.0, 'y_pct' => 50.0, 'round' => 1]),
            $event(3, 1, 'empty_tap', $t('08:11:30'), 4, 41, ['x_pct' => 21.0, 'y_pct' => 20.0, 'round' => 1]),
            $event(4, 1, 'page_hidden', $t('08:15:00')),
            $event(5, 1, 'page_visible', $t('08:16:00')),
            $event(6, 1, 'challenge_abandoned', $t('08:20:00'), 4),
        ],
        'fieldRows' => [...$shared, ['id' => 104, 'field_session_id' => 1, 'sheet' => 'l2_kursi', 'mine' => true, 'seat_no' => 1, 'started_at' => $t('07:55:00'), 'ended_at' => null, 'values' => ['shared_device' => 'Tidak']]],
    ],
    // ------------------------------------------------------------ B
    $base + [
        'participant'   => ['id' => 2, 'code' => 'U01-A02', 'school_code' => 'U01', 'booklet' => 'B1', 'assented_at' => $t('07:40:00', '2026-11-03')],
        'consent'       => $consent,
        'fieldSessions' => [1 => $fs1],
        'seats'         => [1 => 2],
        'sessions'      => [3 => ['id' => 3, 'field_session_id' => 1, 'meeting_no' => 2, 'started_at' => $t('08:00:00')]],
        'attempts'      => [6 => $attempt(6, 3, 2, $t('08:10:00'), $t('08:15:00')), 7 => $attempt(7, 3, 2, $t('08:16:00'), $t('08:18:00'), 1)],
        'tries'         => [13 => $try(13, 6, 21, 1, 1, $t('08:11:00')), 14 => $try(14, 6, 22, 1, 1, $t('08:11:00')), 15 => $try(15, 7, 21, 1, 1, $t('08:17:00'))],
        'fieldRows'     => [...$shared, ['id' => 106, 'field_session_id' => 1, 'sheet' => 'l2_kursi', 'mine' => true, 'seat_no' => 2, 'started_at' => $t('07:55:00'), 'ended_at' => null, 'values' => ['shared_device' => 'Ya', 'switch_time' => '08:12']]],
    ],
    // ------------------------------------------------------------ C
    $base + [
        'participant'   => ['id' => 3, 'code' => 'S03-A03', 'school_code' => 'S03', 'booklet' => 'B1', 'assented_at' => $t('07:40:00', '2026-11-03'), 'deactivated_at' => $t('08:12:00')],
        'consent'       => ['parent_consent_on' => '2026-11-01', 'status' => 'withdrawn_keep', 'status_on' => '2026-11-10'],
        'fieldSessions' => [1 => $fs1],
        'seats'         => [1 => 3],
        'sessions'      => [4 => ['id' => 4, 'field_session_id' => 1, 'meeting_no' => 2, 'started_at' => $t('08:00:00')]],
        'attempts'      => [8 => $attempt(8, 4, 2, $t('08:05:00'), $t('08:20:00'))],
        'tries'         => [
            16 => $try(16, 8, 21, 1, 1, $t('08:10:00')),
            17 => $try(17, 8, 22, 1, 0, $t('08:10:00'), ['error_code' => 'INF-REL', 'error_code_latest' => 'INF-REL']),
            18 => $try(18, 8, 22, 2, 1, $t('08:15:00')),   // after the cutoff: never in the derived layer
        ],
        'fieldRows'     => $shared,
    ],
];
