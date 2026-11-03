<?php

namespace App\Services;

use App\Libraries\SeatRules;
use App\Models\AuditLogModel;
use App\Models\ParticipantSeatModel;

/**
 * L2 entry: rows and seats of one class group, with booklet and test form
 * computed by SeatRules (research revision 10, part 2: 28 §11, K10). A seat
 * can change until `locked_at` is set (part 8, when the student's pre-test
 * starts); every new seat and change is audited with old and new values.
 */
class SeatService
{
    /**
     * Rows of the seat page: every student of the field session's class
     * group with their seat (null when none yet).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<array{id: int, participant_code: string, seat: array<string, mixed>|null}>
     */
    public function roster(array $fieldSession): array
    {
        $students = service('fieldSessions')->roster($fieldSession);
        $seats    = model(ParticipantSeatModel::class)->forParticipants((int) $fieldSession['study_id'], array_column($students, 'id'));

        return array_map(static fn (array $s): array => $s + ['seat' => $seats[$s['id']] ?? null], $students);
    }

    /**
     * Saves the typed rows and seats (participant id => [row, seat]); blank
     * pairs are skipped, out-of-range pairs are reported back.
     *
     * @param array<string, mixed>            $fieldSession
     * @param array<int|string, array<mixed>> $input
     *
     * @return array{saved: int, changed: int, invalid: list<string>, locked: list<string>}
     */
    public function save(array $fieldSession, array $input, ?int $staffId): array
    {
        $studyId = (int) $fieldSession['study_id'];
        $seats   = model(ParticipantSeatModel::class);
        $result  = ['saved' => 0, 'changed' => 0, 'invalid' => [], 'locked' => []];

        foreach ($this->roster($fieldSession) as $student) {
            $typed = $input[$student['id']] ?? $input[(string) $student['id']] ?? null;
            $row   = trim((string) ($typed['row'] ?? ''));
            $seat  = trim((string) ($typed['seat'] ?? ''));

            if ($row === '' && $seat === '') {
                continue;
            }

            if (! SeatRules::validNo($row) || ! SeatRules::validNo($seat)) {
                $result['invalid'][] = $student['participant_code'];

                continue;
            }

            $row   = (int) $row;
            $seat  = (int) $seat;
            $old   = $student['seat'];
            $forms = SeatRules::forms($row, $seat);
            $data  = [
                'row_no'     => $row,
                'seat_no'    => $seat,
                'booklet'    => SeatRules::booklet($row, $seat),
                'pre_form'   => $forms['pre'],
                'post_form'  => $forms['post'],
                'entered_by' => $staffId,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($old === null) {
                // A second save at the same moment hits the unique key and changes nothing
                db_connect()->table('participant_seats')->ignore(true)->insert($data + [
                    'participant_id' => $student['id'],
                    'study_id'       => $studyId,
                    'entered_at'     => date('Y-m-d H:i:s'),
                ]);

                if (db_connect()->affectedRows() === 1) {
                    $result['saved']++;
                    $this->audit('seat_saved', $student, null, $data);
                }

                continue;
            }

            if ((int) $old['row_no'] === $row && (int) $old['seat_no'] === $seat) {
                continue;
            }

            if ($old['locked_at'] !== null) {
                $result['locked'][] = $student['participant_code'];

                continue;
            }

            // Conditional on what was read and still unlocked (28 §11)
            $seats->where(['row_no' => $old['row_no'], 'seat_no' => $old['seat_no'], 'locked_at' => null])->update((int) $old['id'], $data);

            if (db_connect()->affectedRows() === 1) {
                $result['changed']++;
                $this->audit('seat_changed', $student, $old, $data);
            }
        }

        return $result;
    }

    /**
     * Booklet balance of the class group and whether every student has a
     * test form (28 §11).
     *
     * @param list<array{seat: array<string, mixed>|null}> $roster
     *
     * @return array{counts: array<string, int>, balanced: bool, all_have_form: bool, missing: int}
     */
    public function summary(array $roster): array
    {
        $booklets = [];
        $missing  = 0;

        foreach ($roster as $student) {
            if ($student['seat'] === null) {
                $missing++;
            } else {
                $booklets[] = (string) $student['seat']['booklet'];
            }
        }

        return SeatRules::balance($booklets) + ['all_have_form' => $roster !== [] && $missing === 0, 'missing' => $missing];
    }

    /**
     * @param array{id: int, participant_code: string} $student
     * @param array<string, mixed>|null                $old
     * @param array<string, mixed>                     $new
     */
    private function audit(string $action, array $student, ?array $old, array $new): void
    {
        $pick = static fn (?array $row): ?array => $row === null ? null : [
            'row_no' => (int) $row['row_no'], 'seat_no' => (int) $row['seat_no'], 'booklet' => $row['booklet'],
        ];

        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $new['entered_by'] ?? null,
            'target_type'   => 'participant',
            'target_id'     => (string) $student['id'],
            'metadata'      => ['participant_code' => $student['participant_code'], 'from' => $pick($old), 'to' => $pick($new)],
        ]);
    }
}
