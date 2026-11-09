<?php

namespace App\Services;

use App\Models\AuditLogModel;
use App\Models\Concerns\ConditionalUpdate;
use App\Models\FieldSessionMemberModel;
use App\Models\FieldSessionModel;
use App\Models\ParticipantModel;

/**
 * The test module on the field-session page (research revision 10, part 8:
 * 34 §5.3, §7.4, §10.1, K1, K3, K10, K15): the start-test button, extra
 * minutes, and each student's test status. Capability `field_sessions.manage`.
 * Never shows scores, answers, or anything about the risk items (§8.2).
 */
class FieldTestService
{
    use ConditionalUpdate;

    /**
     * What the page shows: the meeting's test, whether it may start, and the
     * students' test status (`belum`, `sedang`, `selesai`, `susulan`, `hilang`).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return array{occasion: ?string, started_at: ?string, can_start: bool, need_seats: bool, makeup_late: bool, open: bool,
     *               students: list<array{participant_code: string, status: string, module_done: bool, blocks: list<string>}>}
     */
    public function panel(array $fieldSession): array
    {
        $fields   = service('fieldSessions');
        $occasion = $fields->planEntry($fieldSession)['test'];
        $out      = ['occasion' => $occasion, 'started_at' => $fieldSession['test_started_at'] ?? null, 'can_start' => false, 'need_seats' => false,
            'makeup_late' => false, 'open' => $fields->isOpen($fieldSession), 'students' => []];

        if ($occasion === null) {
            return $out;
        }

        $study = model(\App\Models\ResearchStudyModel::class)->find((int) $fieldSession['study_id']) ?? [];
        $out['makeup_late'] = (int) $fieldSession['makeup_no'] > 0
            && ! service('instrumentGate')->withinMakeupWindow($fieldSession, service('instruments')->settings($study)['makeup_days']);
        $roster             = $this->roster($fieldSession);
        $out['need_seats']  = $occasion === 'pre' && ! $this->allHaveForms($fieldSession, $roster);
        $out['can_start']   = $out['open'] && $out['started_at'] === null && ! $out['need_seats'] && ! $out['makeup_late'];
        $main               = $this->mainSession($fieldSession);
        $instruments        = service('instruments');

        foreach ($roster as $student) {
            $sitting = $instruments->sitting($student['id'], (int) $fieldSession['study_id'], $occasion, 'test');
            $blocks  = $sitting === null ? [] : array_column(model(\App\Models\InstrumentBlockModel::class)->where('sitting_id', (int) $sitting['id'])->where('closed_at', null)->findAll(), 'block');

            $out['students'][] = [
                'participant_code' => $student['participant_code'],
                'status'           => $instruments->testStatus($student['id'], (int) $fieldSession['study_id'], $occasion, $main),
                'module_done'      => $instruments->moduleDone($student['id'], (int) $fieldSession['study_id'], $occasion),
                'blocks'           => $blocks,
            ];
        }

        return $out;
    }

    // Coordinator, wave F (29 §10.3, 34 §14): L6 status tes per siswa in the field-sheet template
    /**
     * Rows of the printed L6 *tes* sheet, filled from the module: per student
     * the pre-test and post-test (date, form, status), the questionnaire
     * status, the risk-items consent and whether the risk step was sent.
     * Empty when the field session is not a test meeting.
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<array<string, string>> keyed by the L6-tes paper headings
     */
    public function testSheetRows(array $fieldSession): array
    {
        if (service('fieldSessions')->planEntry($fieldSession)['test'] === null) {
            return [];
        }

        $studyId     = (int) $fieldSession['study_id'];
        $main        = $this->mainSession($fieldSession);
        $instruments = service('instruments');
        $consents    = service('consentService');
        $rows        = [];

        $test = static function (int $pid, string $occasion) use ($instruments, $studyId, $main): string {
            $sitting = $instruments->sitting($pid, $studyId, $occasion, 'test');
            $status  = $instruments->testStatus($pid, $studyId, $occasion, $main);

            return $sitting === null ? $status
                : implode(', ', array_filter([substr((string) ($sitting['submitted_at'] ?? $sitting['started_at']), 0, 10), (string) $sitting['form'], $status]));
        };
        $sent = static function (?array $sitting): string {
            return $sitting === null ? 'belum' : ($sitting['submitted_at'] === null ? 'sedang' : 'selesai');
        };

        foreach ($this->roster($fieldSession) as $student) {
            $pid    = (int) $student['id'];
            $rows[] = [
                'Kode peserta'                       => (string) $student['participant_code'],
                'Tes awal (tanggal, bentuk, status)' => $test($pid, 'pre'),
                'Angket'                             => $sent($instruments->sitting($pid, $studyId, 'pre', 'questionnaire')),
                'Tes akhir (tanggal, bentuk, status)' => $test($pid, 'post'),
                'Izin butir risiko'                  => $consents->riskItemsAllowed($pid) ? 'Ya' : 'Tidak',
                'Butir risiko'                       => $sent($instruments->sitting($pid, $studyId, 'post', 'risk')),
                'Catatan'                            => '',
            ];
        }

        return $rows;
    }

    /**
     * Mulai tes awal/akhir (§5.3, K1): `test_started_at` with a conditional
     * UPDATE `test_started_at IS NULL`; audit `field_test_started`. The
     * pre-test waits until every student has a form (K10).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @throws \InvalidArgumentException with an Admin text
     */
    public function start(array $fieldSession, ?int $staffId): void
    {
        $panel = $this->panel($fieldSession);

        match (true) {
            $panel['occasion'] === null   => throw new \InvalidArgumentException(lang('Admin.fieldTestNoTest')),
            $panel['started_at'] !== null => throw new \InvalidArgumentException(lang('Admin.fieldTestAlready')),
            ! $panel['open']              => throw new \InvalidArgumentException(lang('Admin.fieldTestNotOpen')),
            $panel['makeup_late']         => throw new \InvalidArgumentException(lang('Admin.fieldTestMakeupLate')),
            $panel['need_seats']          => throw new \InvalidArgumentException(lang('Admin.fieldTestNeedSeats')),
            default                       => null,
        };

        if (! $this->updateIf(model(FieldSessionModel::class), (int) $fieldSession['id'], ['test_started_at' => null], ['test_started_at' => date('Y-m-d H:i:s'), 'test_started_by' => $staffId])) {
            throw new \InvalidArgumentException(lang('Admin.fieldTestAlready'));
        }

        service('fieldSessions')->forget();
        service('instruments')->forget();

        model(AuditLogModel::class)->record('field_test_started', [
            'staff_user_id' => $staffId,
            'target_type'   => 'field_session',
            'target_id'     => (string) $fieldSession['id'],
            'metadata'      => ['field_session_code' => $fieldSession['code'], 'occasion' => $panel['occasion']],
        ]);
    }

    /**
     * Tambah waktu for one student's running block (K3).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @throws \InvalidArgumentException
     */
    public function extendTime(array $fieldSession, string $participantCode, string $block, int $minutes, string $reason, ?int $staffId): string
    {
        $occasion = service('fieldSessions')->planEntry($fieldSession)['test'];
        $student  = null;

        foreach ($this->roster($fieldSession) as $row) {
            if (strcasecmp($row['participant_code'], trim($participantCode)) === 0) {
                $student = $row;
            }
        }

        $sitting = $student === null || $occasion === null ? null : service('instruments')->sitting($student['id'], (int) $fieldSession['study_id'], $occasion, 'test');
        $row     = $sitting === null || ! in_array($block, ['p1', 'p23'], true) ? null : service('instruments')->block((int) $sitting['id'], $block);

        if ($row === null) {
            throw new \InvalidArgumentException(lang('Admin.instrumentExtendNoBlock'));
        }

        service('instruments')->extendTime((int) $row['id'], $minutes, $reason, $staffId);

        return (string) $student['participant_code'];
    }

    /** @return list<array{id: int, participant_code: string}> the class group, or a make-up's members */
    public function roster(array $fieldSession): array
    {
        $students = service('fieldSessions')->roster($fieldSession);

        if ((int) $fieldSession['makeup_no'] > 0) {
            $members  = model(FieldSessionMemberModel::class)->memberIds((int) $fieldSession['id']);
            $students = array_values(array_filter($students, static fn (array $s): bool => in_array($s['id'], $members, true)));
        }

        return $students;
    }

    /** K10: every student of the roster has a seat with forms ("Semua siswa sudah punya bentuk tes"). */
    private function allHaveForms(array $fieldSession, array $roster): bool
    {
        if ($roster === []) {
            return false;
        }

        $seats = model(\App\Models\ParticipantSeatModel::class)->forParticipants((int) $fieldSession['study_id'], array_column($roster, 'id'));

        foreach ($roster as $student) {
            if (empty($seats[$student['id']]['pre_form'])) {
                return false;
            }
        }

        return true;
    }

    /** @return array<string, mixed> */
    private function mainSession(array $fieldSession): array
    {
        $mainId = (int) ($fieldSession['main_session_id'] ?? 0);

        return ($mainId > 0 ? model(FieldSessionModel::class)->find($mainId) : null) ?? $fieldSession;
    }
}
