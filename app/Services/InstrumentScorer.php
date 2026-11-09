<?php

namespace App\Services;

/**
 * Raw scoring of the test module (research revision 10, part 8: 34 §7.6, K8;
 * rules of 13 → Penskoran, 14 → Aturan kode, 15 → Kode).
 *
 * Raw responses: PG, D2, D3 one letter; PGK three letters (`BSB`, `PFF`, `-`
 * for a statement left out); matching `1C 2A 3D` (`2-` for a pair left out).
 * Scores: PG, PGK, matching, and D3 1 when exactly right, else 0; D2 2, 1, or 0
 * from the item's score map. Error codes come from the key: one for PG, D2,
 * D3; for PGK and matching one per wrong statement or pair, joined by `;`.
 *
 * `rapid_guess`, Rasch measures, and every derived variable are part 9's (35).
 */
class InstrumentScorer
{
    /**
     * Score and error codes of one raw response, or null score when the item
     * is not scored (examples, questionnaire, attitude).
     *
     * @param array<string, mixed> $item decoded item (InstrumentRepository)
     *
     * @return array{score: ?int, error_codes: ?string}
     */
    public static function score(array $item, ?string $response): array
    {
        $key = (array) ($item['key'] ?? []);

        if ($item['part'] === 'ct' || $key === [] || ! in_array($item['type'], ['pg', 'pgk', 'match', 'd2'], true)) {
            return ['score' => null, 'error_codes' => null];
        }

        $answer = (string) ($key['answer'] ?? '');
        $errors = (array) ($key['errors'] ?? []);

        if ($response === null || $response === '') {
            return ['score' => 0, 'error_codes' => null];
        }

        switch ($item['type']) {
            case 'pg':
                return ['score' => $response === $answer ? 1 : 0, 'error_codes' => $response === $answer ? null : self::join([$errors[$response] ?? null])];

            case 'd2':
                $scores = array_map('intval', (array) ($key['scores'] ?? []));

                return ['score' => $scores[$response] ?? 0, 'error_codes' => self::join([$errors[$response] ?? null])];

            case 'pgk':
                $codes = [];

                for ($i = 0; $i < 3; $i++) {
                    $given = $response[$i] ?? '-';

                    if ($given !== '-' && $given !== ($answer[$i] ?? '')) {
                        $codes[] = $errors[(string) ($i + 1)] ?? null;
                    }
                }

                return ['score' => $response === $answer ? 1 : 0, 'error_codes' => self::join($codes)];

            default: // match
                $right = self::pairs($answer);
                $given = self::pairs($response);
                $codes = [];

                foreach ($right as $no => $letter) {
                    $chosen = $given[$no] ?? '-';

                    if ($chosen === '-' || $chosen === $letter) {
                        continue;
                    }

                    // A choice that belongs to another statement: INF-REL (14 → Aturan kode no. 3)
                    $codes[] = in_array($chosen, $right, true) ? ($errors['other_pair'] ?? null) : ($errors[$chosen] ?? null);
                }

                return ['score' => $given === $right ? 1 : 0, 'error_codes' => self::join($codes)];
        }
    }

    /**
     * `1C 2A 3D` → ['1' => 'C', '2' => 'A', '3' => 'D']; `-` for a pair left out.
     *
     * @return array<string, string>
     */
    public static function pairs(string $value): array
    {
        $out = [];

        foreach (preg_split('/\s+/', trim($value)) ?: [] as $pair) {
            if (preg_match('/^([1-3])([A-D-])$/', $pair, $m) === 1) {
                $out[$m[1]] = $m[2];
            }
        }

        ksort($out);

        return $out;
    }

    /** @param list<?string> $codes */
    private static function join(array $codes): ?string
    {
        $codes = array_values(array_filter($codes, static fn (?string $c): bool => $c !== null && $c !== ''));

        return $codes === [] ? null : implode(';', $codes);
    }
}
