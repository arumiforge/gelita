<?php

use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §6.4; 11 → Analitik 1): the five completion statuses and the
 * kinds (a) and (b) of "dilewati".
 *
 * @internal
 */
final class ChallengeCalculatorTest extends CIUnitTestCase
{
    public function testTheFiveStatuses(): void
    {
        $tries = [
            1 => $this->try(1, 1, 1, 1, '08:05:00'),
            2 => $this->try(2, 2, 2, 0, '08:10:00'), 3 => $this->try(3, 2, 2, 0, '08:11:00', 2), 4 => $this->try(4, 2, 2, 0, '08:12:00', 3),
            5 => $this->try(5, 3, 6, 0, '08:31:00'),
        ];
        $rows = $this->compute([
            1 => $this->attempt(1, 1, '08:01:00', '08:06:00'),
            2 => $this->attempt(2, 2, '08:07:00', '08:13:00'),
            3 => $this->attempt(3, 6, '08:30:00', null),
        ], $tries);

        $status = array_column($rows['derived_challenges'], 'completion_status', 'challenge_node_id');
        $this->assertSame(['solved', 'exhausted', 'not_started', 'locked', 'locked', 'unfinished', 'locked'], [$status[1], $status[2], $status[3], $status[4], $status[5], $status[6], $status[7]]);
        $this->assertSame(2, array_column($rows['derived_challenges'], 'closed_meeting_no', 'challenge_node_id')[1]);

        $missing = array_column($rows['derived_items'], 'missing_reason', 'challenge_item_id');
        $this->assertSame(['not_started', 'locked'], [$missing[3], $missing[4]]);

        $person = $rows['derived_participants'][0];
        $this->assertSame([2, 1, 0], [$person['challenges_completed'], $person['scored_challenges_completed'], $person['regions_completed']], 'tmg-1 here is a puzzle');
    }

    public function testKindsAAndBAreCountedApartAndBStaysWhenDoneAtP5(): void
    {
        $sessions = [
            1 => ['id' => 1, 'field_session_id' => 1, 'meeting_no' => 2, 'started_at' => '2026-11-10 08:00:00'],
            2 => ['id' => 2, 'field_session_id' => 2, 'meeting_no' => 5, 'started_at' => '2026-11-14 08:00:00'],
        ];
        $attempt = $this->attempt(1, 6, '08:01:00', null);
        $attempt['status'] = 'completed';
        $attempt['completed_at'] = '2026-11-14 08:20:00';
        $events = [
            ['id' => 1, 'session_id' => 1, 'type' => 'clue_failed', 'at' => '2026-11-10 08:05:00', 'attempt_id' => 1, 'item_id' => 6, 'payload' => []],
            ['id' => 2, 'session_id' => 1, 'type' => 'challenge_abandoned', 'at' => '2026-11-10 08:06:00', 'attempt_id' => 1, 'item_id' => null, 'payload' => []],
        ];
        $segments = [
            ['attempt_id' => 1, 'session_id' => 1, 'field_session_id' => 1, 'segment_no' => 1, 'opened_at' => '2026-11-10 08:01:00', 'left_at' => '2026-11-10 08:06:00'],
            ['attempt_id' => 1, 'session_id' => 2, 'field_session_id' => 2, 'segment_no' => 2, 'opened_at' => '2026-11-14 08:10:00', 'left_at' => '2026-11-14 08:20:00'],
        ];
        $tries = [1 => $this->try(1, 1, 6, 0, '2026-11-10 08:04:00'), 2 => $this->try(2, 1, 6, 1, '2026-11-14 08:15:00', 2)];

        $rows = $this->compute([1 => $attempt], $tries, $events, $segments, $sessions);
        $row  = array_column($rows['derived_challenges'], null, 'challenge_node_id')[6];

        $this->assertSame(['solved', 1, 1, 5], [$row['completion_status'], $row['skipped_failed_count'], $row['skipped_left_count'], $row['closed_meeting_no']]);
        $attempts = $rows['derived_attempts'][0];
        $this->assertSame([2, 900000, 1], [$attempts['segments'], $attempts['total_ms'], $attempts['exit_count']], 'the gap between meetings never counts');
    }

    public function testResumedInTheSameFieldSessionIsNotKindB(): void
    {
        $attempt = $this->attempt(1, 6, '08:01:00', '08:30:00');
        $events  = [['id' => 1, 'session_id' => 1, 'type' => 'challenge_abandoned', 'at' => '2026-11-10 08:06:00', 'attempt_id' => 1, 'item_id' => null, 'payload' => []]];
        $tries   = [1 => $this->try(1, 1, 6, 1, '08:20:00')];

        $rows = $this->compute([1 => $attempt], $tries, $events);

        $this->assertSame(0, array_column($rows['derived_challenges'], null, 'challenge_node_id')[6]['skipped_left_count']);
    }

    private function compute(array $attempts, array $tries, array $events = [], array $segments = [], ?array $sessions = null): array
    {
        $nodes = [];
        $items = [];
        $engines = [1 => 'puzzle', 2 => 'rumpang', 3 => 'boleh', 4 => 'cari', 5 => 'skenario'];

        foreach ([1, 2] as $level) {
            foreach ($level === 1 ? [1, 2, 3, 4, 5] : [1, 2] as $seq) {
                $id         = $level === 1 ? $seq : 5 + $seq;
                $nodes[$id] = ['id' => $id, 'level_seq' => $level, 'sequence' => $seq, 'engine' => $level === 1 ? $engines[$seq] : $engines[$seq + 2]];
                $items[$id] = ['id' => $id, 'item_key' => 'n' . $id, 'node_id' => $id, 'variant_group' => 'a', 'interaction_type' => 'verdict_card', 'dimension' => 'D1', 'scorable' => 1, 'key_box' => null];
            }
        }

        return ParticipantRun::compute(RunInput::fromArray([
            'participant'   => ['id' => 1, 'code' => 'S03-A01', 'school_code' => 'S03', 'booklet' => 'B1', 'assented_at' => '2026-11-01 08:00:00'],
            'consent'       => ['parent_consent_on' => '2026-11-01', 'status' => 'active', 'status_on' => null],
            'nodes'         => $nodes,
            'items'         => $items,
            'fieldSessions' => [
                1 => ['id' => 1, 'meeting_no' => 2, 'makeup_no' => 0, 'date' => '2026-11-10', 'opens_at' => '2026-11-10 07:30:00', 'closes_at' => '2026-11-10 10:00:00', 'closed_at' => null, 'regions' => [1, 2]],
                2 => ['id' => 2, 'meeting_no' => 5, 'makeup_no' => 0, 'date' => '2026-11-14', 'opens_at' => '2026-11-14 07:30:00', 'closes_at' => '2026-11-14 10:00:00', 'closed_at' => null, 'regions' => [1, 2]],
            ],
            'sessions'      => $sessions ?? [1 => ['id' => 1, 'field_session_id' => 1, 'meeting_no' => 2, 'started_at' => '2026-11-10 08:00:00']],
            'attempts'      => $attempts,
            'segments'      => $segments,
            'tries'         => $tries,
            'events'        => $events,
        ]), new Thresholds());
    }

    private function attempt(int $id, int $node, string $start, ?string $end): array
    {
        $day = static fn (?string $t): ?string => $t === null || strlen($t) > 8 ? $t : '2026-11-10 ' . $t;

        return ['id' => $id, 'session_id' => 1, 'node_id' => $node, 'attempt_no' => 1, 'variant_group' => 'a', 'status' => $end === null ? 'in_progress' : 'completed',
            'started_at' => $day($start), 'completed_at' => $day($end), 'is_retake' => 0, 'hint_count' => 0];
    }

    private function try(int $id, int $attempt, int $item, int $correct, string $at, int $no = 1): array
    {
        return ['id' => $id, 'attempt_id' => $attempt, 'item_id' => $item, 'try_no' => $no, 'is_correct' => $correct,
            'occurred_at' => strlen($at) > 8 ? $at : '2026-11-10 ' . $at, 'hint_opened_before' => 0, 'answer_hash' => 'h' . $id];
    }
}
