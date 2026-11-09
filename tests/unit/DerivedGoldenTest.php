<?php

use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Part 9 (35 §16.1 DerivedGoldenTest): the hand-computed sample class
 * (tests/_support/Derived/golden-class.php) through the calculators; every
 * derived table equals the CSV beside it on the columns that CSV lists,
 * row for row. The CSVs were written by hand from 11 and 35, never by
 * running the calculator.
 *
 * @internal
 */
final class DerivedGoldenTest extends CIUnitTestCase
{
    private const FILES = [
        'derived_tries'          => ['golden-tries.csv', ['try_id']],
        'derived_items'          => ['golden-items.csv', ['participant_id', 'challenge_item_id']],
        'derived_attempts'       => ['golden-attempts.csv', ['attempt_id']],
        'derived_challenges'     => ['golden-challenges.csv', ['participant_id', 'challenge_node_id']],
        'derived_participants'   => ['golden-participants.csv', ['participant_id']],
        'derived_field_sessions' => ['golden-field-sessions.csv', ['participant_id', 'field_session_id']],
        'derived_observations'   => ['golden-observations.csv', ['field_sheet_row_id']],
    ];

    public function testEveryTableEqualsTheHandComputedRows(): void
    {
        $rows = [];

        foreach (require SUPPORTPATH . 'Derived/golden-class.php' as $student) {
            foreach (ParticipantRun::compute(RunInput::fromArray($student), new Thresholds(180000, 3000, 3000)) as $table => $list) {
                $rows[$table] = [...($rows[$table] ?? []), ...$list];
            }
        }

        foreach (self::FILES as $table => [$file, $keys]) {
            [$header, $expected] = $this->csv(SUPPORTPATH . 'Derived/' . $file);
            $actual              = array_map(static fn (array $row): array => array_map(
                static fn (mixed $v): string => $v === null ? '' : (is_float($v) ? rtrim(rtrim(sprintf('%.4F', $v), '0'), '.') : (string) $v),
                array_intersect_key(array_merge(array_flip($header), $row), array_flip($header)),
            ), $rows[$table] ?? []);
            $sort = static fn (array $a, array $b): int => array_map(static fn (string $k): int => (int) $a[$k], $keys) <=> array_map(static fn (string $k): int => (int) $b[$k], $keys);
            usort($actual, $sort);
            usort($expected, $sort);

            $this->assertSame($expected, $actual, $table);
        }
    }

    /** @return array{0: list<string>, 1: list<array<string, string>>} */
    private function csv(string $path): array
    {
        $lines  = array_values(array_filter(file($path, FILE_IGNORE_NEW_LINES), static fn (string $l): bool => $l !== '' && $l[0] !== '#'));
        $header = str_getcsv(array_shift($lines), ',', '"', '');

        return [$header, array_map(static fn (string $l): array => array_combine($header, str_getcsv($l, ',', '"', '')), $lines)];
    }
}
