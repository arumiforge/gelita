<?php

use App\Libraries\Derived\Clock;
use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * `sikap_missing_reason` (35 §6.6; owner, 9 November 2026): empty until a
 * P5 meeting of the student has opened by the run time, then `absen`, or
 * `belum_tuntas` when the student attended P5 without sending the scale.
 *
 * @internal
 */
final class AttitudeMissingReasonTest extends CIUnitTestCase
{
    public function testEmptyBeforeP5AbsentAfterItOpened(): void
    {
        $p5 = ['id' => 5, 'meeting_no' => 5, 'makeup_no' => 0, 'date' => '2026-12-01', 'opens_at' => '2026-12-01 08:00:00', 'closes_at' => '2026-12-01 10:00:00', 'closed_at' => null, 'regions' => []];

        $this->assertNull($this->reason([5 => $p5], [], '2026-11-20 09:00:00'), 'P5 not held yet');
        $this->assertSame('absen', $this->reason([5 => $p5], [], '2026-12-02 09:00:00'));
        $this->assertSame('belum_tuntas', $this->reason([5 => $p5], [1 => ['id' => 1, 'field_session_id' => 5, 'started_at' => '2026-12-01 08:05:00', 'last_active_at' => null, 'ended_at' => null]], '2026-12-01 08:30:00'));
        $this->assertNull($this->reason([], [], '2027-01-01 09:00:00'), 'no P5 planned for the student');
    }

    private function reason(array $fieldSessions, array $sessions, string $asOf): ?string
    {
        $in = RunInput::fromArray([
            'participant'   => ['id' => 1, 'code' => 'S03-A01', 'school_code' => 'S03', 'booklet' => 'B1', 'deactivated_at' => null, 'assented_at' => null, 'corrected_at' => null],
            'consent'       => ['parent_consent_on' => '2026-11-01', 'status' => 'active', 'status_on' => '2026-11-01'],
            'fieldSessions' => $fieldSessions,
            'sessions'      => $sessions,
            'asOf'          => Clock::ms($asOf),
        ]);

        $row = ParticipantRun::compute($in, new Thresholds())['derived_participants'][0];
        $this->assertArrayHasKey('sikap_missing_reason', $row);

        return $row['sikap_missing_reason'];
    }
}
