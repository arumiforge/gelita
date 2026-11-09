<?php

namespace App\Libraries\Derived;

/**
 * Field-sheet markers per try (35 §6.1, 11 → Aturan gabung lembar lapangan):
 *
 * - L2 `shared_device`: the student's L2 row of that field session says
 *   *Pakai bareng: Ya*, until the time of a device switch;
 * - L3 `helped_content` (ISI, STR, BHS), `helped_technical` (TKN),
 *   `encouraged` (SMG), `disturbed` (GG-*): a row for the student or their
 *   seat, from 1 minute before its start until 2 minutes after its end (or start);
 * - L1 `deviation_code`: DEV-* rows naming the student's seat (or `semua`),
 *   from the start until its end, or 5 minutes when no end is written;
 *   several codes joined by `;`.
 *
 * A marker is empty (null) when that sheet was never imported for the field
 * session: no data, not 0. Sheet times are the sheets' WIB clock times on
 * the field session's date (`field_sheet_rows.started_at`/`ended_at`).
 * Pure.
 */
final class FieldSheetFlags
{
    public const HELP = [
        'helped_content'   => ['ISI', 'STR', 'BHS'],
        'helped_technical' => ['TKN'],
        'encouraged'       => ['SMG'],
    ];

    public const L3_BEFORE_MS = 60_000;

    public const L3_AFTER_MS = 120_000;

    public const L1_DEFAULT_MS = 300_000;

    /**
     * @param list<array<string, mixed>>  $rows     the student's candidate rows: id, field_session_id, sheet, mine (bool), seat_no, started_at, ended_at (ms), values
     * @param array<int, list<string>>    $imported field session id => sheets imported (main, not superseded)
     * @param array<int, int>             $seats    field session id => the student's seat
     * @param array<int, string>          $dates    field session id => session date Y-m-d
     */
    public function __construct(
        private array $rows,
        private array $imported,
        private array $seats,
        private array $dates = [],
    ) {
    }

    /** @return array{shared_device: ?int, helped_content: ?int, helped_technical: ?int, encouraged: ?int, disturbed: ?int, deviation_code: ?string} */
    public function at(?int $fieldSessionId, int $t): array
    {
        $out = ['shared_device' => null, 'helped_content' => null, 'helped_technical' => null, 'encouraged' => null, 'disturbed' => null, 'deviation_code' => null];

        if ($fieldSessionId === null) {
            return $out;
        }

        $sheets = $this->imported[$fieldSessionId] ?? [];
        $seat   = $this->seats[$fieldSessionId] ?? null;
        $codes  = [];

        if (in_array('l2_kursi', $sheets, true)) {
            $out['shared_device'] = 0;
        }

        if (in_array('l3_bantuan', $sheets, true)) {
            $out['helped_content'] = $out['helped_technical'] = $out['encouraged'] = $out['disturbed'] = 0;
        }

        foreach ($this->rows as $row) {
            if ((int) $row['field_session_id'] !== $fieldSessionId) {
                continue;
            }

            $values = (array) $row['values'];

            switch ($row['sheet']) {
                case 'l2_kursi':
                    if (! empty($row['mine']) && self::yes($values['shared_device'] ?? null)) {
                        $switch = $this->clock($fieldSessionId, $values['switch_time'] ?? null);

                        if ($switch === null || $t < $switch) {
                            $out['shared_device'] = 1;
                        }
                    }
                    break;

                case 'l3_bantuan':
                    if (! self::concerns($row, $values['seats'] ?? null, $seat) || $row['started_at'] === null) {
                        break;
                    }

                    if ($t < $row['started_at'] - self::L3_BEFORE_MS || $t > ($row['ended_at'] ?? $row['started_at']) + self::L3_AFTER_MS) {
                        break;
                    }

                    $type = strtoupper(trim((string) ($values['help_type'] ?? '')));

                    foreach (self::HELP as $column => $types) {
                        if (in_array($type, $types, true)) {
                            $out[$column] = 1;
                        }
                    }

                    if (str_starts_with($type, 'GG-')) {
                        $out['disturbed'] = 1;
                    }
                    break;

                case 'l1_penyimpangan':
                    if ($row['started_at'] === null || ! self::seatListed($values['affected_seats'] ?? null, $seat)) {
                        break;
                    }

                    $end = $row['ended_at'] ?? $row['started_at'] + self::L1_DEFAULT_MS;

                    if ($t >= $row['started_at'] && $t <= $end) {
                        $code = strtoupper(trim((string) ($values['deviation_code'] ?? '')));

                        if ($code !== '' && ! in_array($code, $codes, true)) {
                            $codes[] = $code;
                        }
                    }
                    break;
            }
        }

        $out['deviation_code'] = $codes === [] ? null : implode(';', $codes);

        return $out;
    }

    /** A row is the student's when it names them, or their seat is among its seats. */
    private static function concerns(array $row, mixed $seats, ?int $seat): bool
    {
        return ! empty($row['mine']) || self::seatListed($seats, $seat, false);
    }

    /** `semua` (all students), or a list of seat numbers containing $seat. */
    public static function seatListed(mixed $list, ?int $seat, bool $allowAll = true): bool
    {
        $list = strtolower(trim((string) $list));

        if ($list === '') {
            return false;
        }

        if (str_contains($list, 'semua')) {
            return $allowAll || $seat !== null;
        }

        if ($seat === null) {
            return false;
        }

        preg_match_all('/\d+/', $list, $m);

        return in_array($seat, array_map('intval', $m[0]), true);
    }

    private static function yes(mixed $value): bool
    {
        return strtolower(trim((string) $value)) === 'ya';
    }

    private function clock(int $fieldSessionId, mixed $time): ?int
    {
        $time = trim((string) $time);
        $date = $this->dates[$fieldSessionId] ?? null;

        if ($time === '' || $date === null) {
            return null;
        }

        return Clock::ms($date . ' ' . (strlen($time) === 5 ? $time . ':00' : $time));
    }
}
