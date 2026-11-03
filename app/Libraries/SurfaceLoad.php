<?php

namespace App\Libraries;

/**
 * Surface-load variables of one item (31 §13.2, names of 11 → Variabel
 * butir), computed at import from the bank content and frozen with it (K14):
 *
 * - `gap_count`, `distractor_word_count` (Isi Rumpang);
 * - `passage_words`: words of the reading or card of the item;
 * - `decoy_count`: round-1 decoys, or round-2 parts other than the key;
 * - `option_count`: actions (scenario) or tap areas (Cari Objek);
 *   `reason_option_count`: reasons (scenario);
 * - `puzzle_grid`: board size (Susun Gambar);
 * - `tap_min_dp`: smallest gap in dp between the key box and any other box
 *   of the same scene (HuntGeometry).
 *
 * Pure (no framework).
 */
final class SurfaceLoad
{
    public const KEYS = [
        'gap_count', 'distractor_word_count', 'passage_words', 'decoy_count',
        'option_count', 'reason_option_count', 'puzzle_grid', 'tap_min_dp',
    ];

    /**
     * Words by the rule of 11: pieces separated by white space that hold at
     * least one letter or digit, so emoji and lone marks do not count; line
     * markers of digital texts (`>>`, `~~`, `::`) do not either. Account names
     * (`@jalan2_bima`) and numbers (3.150) count as one word each.
     */
    public static function words(string $text): int
    {
        $count = 0;

        foreach (preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $piece) {
            if (preg_match('/[\p{L}\p{N}]/u', $piece) === 1) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Load of every item of one challenge.
     *
     * @param array<string, mixed>                $node     BankRules node shape
     * @param array<string, array<string, mixed>> $passages passage_key → passage (body_id)
     * @param array<string, int>                  $grids    item_key → puzzle grid
     *
     * @return array<string, array<string, int|float|null>> item_key → load
     */
    public static function forNode(array $node, array $passages = [], array $grids = []): array
    {
        $out = [];

        foreach ((array) ($node['variants'] ?? []) as $group => $variant) {
            $items       = array_values((array) ($variant['items'] ?? []));
            $distractors = count((array) ($variant['distractors'] ?? []));
            $reading     = implode(' ', array_map(static fn (array $i): string => (string) $i['prompt_id'], $items));
            $objects     = array_values(array_filter($items, static fn (array $i): bool => $i['type'] === 'find_object'));
            $post        = $node['posts'][$group] ?? null;

            foreach ($items as $item) {
                $load = array_fill_keys(self::KEYS, null);

                switch ($item['type']) {
                    case 'fill_blank_bank':
                        $load['gap_count']             = count($items);
                        $load['distractor_word_count'] = $distractors;
                        $load['passage_words']         = self::words($reading);
                        break;

                    case 'verdict_card':
                        $load['passage_words'] = self::words((string) ($passages[(string) ($item['passage'] ?? '')]['body_id'] ?? ''));
                        break;

                    case 'find_object':
                        if (! empty($item['decoy'])) {
                            break;
                        }

                        $load['decoy_count']  = count(array_filter($objects, static fn (array $o): bool => ! empty($o['decoy'])));
                        $load['option_count'] = count($objects);
                        $load['tap_min_dp']   = self::minGap(
                            BankRules::box($item),
                            array_map(BankRules::box(...), array_filter($objects, static fn (array $o): bool => $o['key'] !== $item['key'])),
                        );
                        break;

                    case 'find_part':
                        $parts = (array) ($post['parts'] ?? []);
                        $boxes = [];
                        $key   = null;

                        foreach ($parts as $part) {
                            $box = [(float) $part['x'], (float) $part['y'], (float) $part['w'], (float) $part['h']];

                            if ((string) $part['key'] === (string) $item['answer_id']) {
                                $key = $box;
                            } else {
                                $boxes[] = $box;
                            }
                        }

                        $load['decoy_count']   = max(0, count($parts) - 1);
                        $load['option_count']  = count($parts);
                        $load['passage_words'] = self::words(implode(' ', array_map(static fn (array $p): string => (string) $p['text_id'], $parts)));
                        $load['tap_min_dp']    = $key === null ? null : self::minGap($key, $boxes);
                        break;

                    case 'scenario_choice':
                        $scenario                     = $node['scenarios'][$item['key']] ?? [];
                        $load['option_count']         = count((array) ($scenario['actions'] ?? []));
                        $load['reason_option_count']  = count((array) ($scenario['reasons'] ?? []));
                        $load['passage_words']        = self::words((string) $item['prompt_id']);
                        break;

                    case 'puzzle_arrange':
                        $load['puzzle_grid'] = $grids[$item['key']] ?? null;
                        break;
                }

                $out[(string) $item['key']] = array_filter($load, static fn ($v): bool => $v !== null);
            }
        }

        return $out;
    }

    /** @param list<array{0: float, 1: float, 2: float, 3: float}> $others */
    private static function minGap(array $box, array $others): ?float
    {
        $gap = HuntGeometry::nearestGapDp($box, array_values($others));

        return $gap === null ? null : round($gap, 1);
    }
}
