<?php

namespace App\Services;

/**
 * Participant codes `S03-A07` for field mode (research revision 10, part 2:
 * 28 §10.2, K11): `{school research code}-{class group}{two digits}`.
 *
 * The number comes from `participant_code_counters` (one row per school and
 * class group, not per study, so codes never repeat across tests). The row is
 * made when the class group's first field session is created; registering
 * only moves it on with a conditional UPDATE, retried a few times when another
 * registration won. The unique key on `participants.participant_code` stays
 * the last guard.
 */
class ParticipantCodeService
{
    private const TRIES = 5;

    /** Creates the counter row if it is missing (INSERT IGNORE). */
    public function ensureCounter(int $schoolId, string $rombel): void
    {
        db_connect()->table('participant_code_counters')->ignore(true)->insert([
            'school_id' => $schoolId,
            'rombel'    => $rombel,
            'next_no'   => 1,
        ]);
    }

    /**
     * Takes the next number of a class group and returns the code.
     *
     * @throws ParticipantCodeFullException after the 99th code of a class group
     * @throws \RuntimeException            when another registration kept winning
     */
    public function next(int $schoolId, string $rombel, string $schoolCode): string
    {
        $this->ensureCounter($schoolId, $rombel);

        $max = config('Gelita')->participantCodeMax;

        for ($try = 0; $try < self::TRIES; $try++) {
            $row = $this->counter($schoolId, $rombel);

            if ($row === null) {
                break;
            }

            $no = (int) $row['next_no'];

            if ($no > $max) {
                throw new ParticipantCodeFullException(lang('Game.codeFull'));
            }

            $db = db_connect();
            $db->table('participant_code_counters')
                ->where('id', (int) $row['id'])
                ->where('next_no', $no)
                ->update(['next_no' => $no + 1]);

            if ($db->affectedRows() === 1) {
                return participant_code($no, $schoolCode, $rombel);
            }
        }

        throw new \RuntimeException('Nomor kode peserta gagal diambil.');
    }

    /** Codes already used in a class group (panel warning at 99, 28 §10.2). */
    public function used(int $schoolId, string $rombel): int
    {
        $row = $this->counter($schoolId, $rombel);

        return $row === null ? 0 : max(0, (int) $row['next_no'] - 1);
    }

    /** @return array<string, mixed>|null */
    protected function counter(int $schoolId, string $rombel): ?array
    {
        return db_connect()->table('participant_code_counters')
            ->where('school_id', $schoolId)
            ->where('rombel', $rombel)
            ->get()
            ->getRowArray();
    }
}
