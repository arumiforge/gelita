<?php

use App\Entities\ChallengeItem;
use App\Libraries\DifficultyRules;
use App\Libraries\PuzzleCut;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Susun Gambar kelas 5: ukuran papan per tingkat wilayah (mudah 3 × 3
 * kotak, sedang 4 × 4 kotak, sulit 4 × 4 jigsaw) dan potongan keping yang
 * dibuat dari satu gambar utuh. Keping jigsaw bersebelahan harus memakai
 * kurva yang sama sehingga saling mengunci, tepi papan lurus, dan potongan
 * yang sama untuk soal yang sama.
 *
 * @internal
 */
final class PuzzleCutTest extends CIUnitTestCase
{
    public function testLayoutFollowsRegionDifficulty(): void
    {
        $this->assertSame(['grid' => 3, 'shape' => 'square'], DifficultyRules::puzzle('mudah'));
        $this->assertSame(['grid' => 4, 'shape' => 'square'], DifficultyRules::puzzle('sedang'));
        $this->assertSame(['grid' => 4, 'shape' => 'jigsaw'], DifficultyRules::puzzle('sulit'));
        $this->assertSame(['grid' => 3, 'shape' => 'square'], DifficultyRules::puzzle('tidak-dikenal'), 'tingkat tak dikenal = mudah');
    }

    public function testResolvePrefersTheItemsOwnLayout(): void
    {
        $this->assertSame(['grid' => 4, 'shape' => 'jigsaw'], PuzzleCut::resolve(['grid' => 4, 'shape' => 'jigsaw'], range(0, 15), 3, 'mudah'));

        // soal lama: tanpa config, kunci 9 keping → 3 × 3 kotak, walau wilayahnya kini "sedang"
        $this->assertSame(['grid' => 3, 'shape' => 'square'], PuzzleCut::resolve([], range(0, 8), null, 'sedang'));

        // tanpa catatan apa pun → aturan wilayah
        $this->assertSame(['grid' => 4, 'shape' => 'jigsaw'], PuzzleCut::resolve([], null, null, 'sulit'));

        // ukuran lama milik tantangan dipakai sebelum aturan wilayah
        $this->assertSame(['grid' => 3, 'shape' => 'square'], PuzzleCut::resolve([], null, 3, 'sulit'));
    }

    public function testEntityExposesLayoutToThePlayerWithoutTheKey(): void
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'               => 21,
            'item_key'         => 'wnb-1-05',
            'interaction_type' => 'puzzle_arrange',
            'answer_key_json'  => json_encode(['order' => range(0, 15)]),
            'config_json'      => '{"grid":4,"shape":"jigsaw"}',
            'prompt_id'        => 'Telaga Warna',
        ]);

        $payload = $item->toPlayerArray('id');

        $this->assertSame(4, $payload['config']['grid']);
        $this->assertSame('jigsaw', $payload['config']['shape']);
        $this->assertArrayNotHasKey('answer_key_json', $payload);

        $legacy = new ChallengeItem();
        $legacy->injectRawData([
            'id'               => 22,
            'item_key'         => 'tmg-1-01',
            'interaction_type' => 'puzzle_arrange',
            'answer_key_json'  => json_encode(['order' => range(0, 8)]),
        ]);

        $this->assertSame(['grid' => 3, 'shape' => 'square'], $legacy->puzzleLayout('sedang'));
    }

    public function testSquarePiecesAreCells(): void
    {
        $pieces = PuzzleCut::pieces(3, 'square', 'tmg-1-01');

        $this->assertCount(9, $pieces);
        $this->assertSame('M100 100H200V200H100Z', $pieces[4]['path']);
        $this->assertSame('100 100 100 100', $pieces[4]['viewBox']);
        $this->assertSame([0, 1, 2, 3, 4, 5, 6, 7, 8], array_column($pieces, 'index'));
        $this->assertSame(0.0, PuzzleCut::overhang('square'));
        $this->assertSame(0.0, PuzzleCut::boardInset(3, 'square'));
    }

    public function testJigsawIsDeterministicPerItem(): void
    {
        $a = PuzzleCut::pieces(4, 'jigsaw', 'wnb-1-05');

        $this->assertSame($a, PuzzleCut::pieces(4, 'jigsaw', 'wnb-1-05'), 'potongan sama untuk soal yang sama');
        $this->assertNotSame(array_column($a, 'path'), array_column(PuzzleCut::pieces(4, 'jigsaw', 'wnb-1-06'), 'path'), 'soal lain, potongan lain');
        $this->assertCount(16, $a);
    }

    public function testJigsawBordersAreStraightAndInteriorEdgesCurved(): void
    {
        $pieces = PuzzleCut::pieces(4, 'jigsaw', 'wnb-1-07');

        // sudut: 2 sisi lurus, tepi: 1, tengah: 0; setiap sisi dalam = 3 Bezier
        $expect = static fn (int $r, int $c): int => (int) ($r === 0 || $r === 3) + (int) ($c === 0 || $c === 3);

        foreach ($pieces as $piece) {
            $straight = $expect($piece['row'], $piece['col']);
            $curves   = substr_count($piece['path'], 'C');
            // sisi kiri papan ditutup Z (tanpa L), jadi hitung sisi lurus = 4 sisi − sisi melengkung
            $this->assertSame((4 - $straight) * 3, $curves, "keping {$piece['row']},{$piece['col']}");
            $this->assertStringEndsWith('Z', $piece['path']);
        }
    }

    public function testNeighbouringPiecesShareTheSameCurve(): void
    {
        $pieces = PuzzleCut::pieces(4, 'jigsaw', 'wnb-1-05');
        $byPos  = [];

        foreach ($pieces as $piece) {
            $byPos[$piece['row']][$piece['col']] = $this->points($piece['path']);
        }

        // Sisi kanan keping (r,c) = sisi kiri keping (r,c+1) dibalik
        for ($r = 0; $r < 4; $r++) {
            for ($c = 0; $c < 3; $c++) {
                $right = $this->sideBetween($byPos[$r][$c], [($c + 1) * 100, $r * 100], [($c + 1) * 100, ($r + 1) * 100]);
                $left  = $this->sideBetween($byPos[$r][$c + 1], [($c + 1) * 100, ($r + 1) * 100], [($c + 1) * 100, $r * 100]);

                $this->assertNotEmpty($right);
                $this->assertSame($right, array_reverse($left), "sisi tegak di kanan keping {$r},{$c}");
            }
        }

        // Sisi bawah keping (r,c) = sisi atas keping (r+1,c) dibalik
        for ($r = 0; $r < 3; $r++) {
            for ($c = 0; $c < 4; $c++) {
                $bottom = $this->sideBetween($byPos[$r][$c], [($c + 1) * 100, ($r + 1) * 100], [$c * 100, ($r + 1) * 100]);
                $top    = $this->sideBetween($byPos[$r + 1][$c], [$c * 100, ($r + 1) * 100], [($c + 1) * 100, ($r + 1) * 100]);

                $this->assertSame($bottom, array_reverse($top), "sisi datar di bawah keping {$r},{$c}");
            }
        }
    }

    public function testEveryPointStaysInsideThePieceViewBox(): void
    {
        foreach (PuzzleCut::pieces(4, 'jigsaw', 'wnb-1-06') as $piece) {
            [$x, $y, $w, $h] = array_map('floatval', explode(' ', $piece['viewBox']));

            foreach ($this->points($piece['path']) as [$px, $py]) {
                $this->assertGreaterThanOrEqual($x, $px);
                $this->assertLessThanOrEqual($x + $w, $px);
                $this->assertGreaterThanOrEqual($y, $py);
                $this->assertLessThanOrEqual($y + $h, $py);
            }
        }

        $this->assertSame(35.0, PuzzleCut::overhang('jigsaw'));
        $this->assertSame(7.447, PuzzleCut::boardInset(4, 'jigsaw'));
    }

    public function testJigsawArtIsNotCappedByTheGlobalSvgRule(): void
    {
        // base.css: svg { max-width: 100% } — tanpa pengecualian, keping jigsaw
        // menciut menjadi 100/170 slotnya dan papan tampak berlubang.
        $this->assertMatchesRegularExpression('/\bsvg\b[^{]*\{[^}]*max-width:\s*100%/', (string) file_get_contents(FCPATH . 'assets/css/base.css'));

        preg_match('/\.piece-art\s*\{([^}]*)\}/', (string) file_get_contents(FCPATH . 'assets/css/game.css'), $rule);

        $this->assertNotEmpty($rule, 'aturan .piece-art ada di game.css');
        $this->assertMatchesRegularExpression('/max-width:\s*none/', $rule[1]);
        $this->assertMatchesRegularExpression('/width:\s*calc\(100% \+ 2 \* var\(--overhang/', $rule[1]);
    }

    /** @return list<array{float, float}> semua titik path, urut */
    private function points(string $path): array
    {
        preg_match_all('/-?\d+(?:\.\d+)?/', str_replace(['H', 'V'], [' H', ' V'], $path), $numbers);

        // path kotak memakai H/V; path jigsaw hanya M, L, C → pasangan x y
        $values = array_map('floatval', $numbers[0]);
        $out    = [];

        for ($i = 0; $i + 1 < count($values); $i += 2) {
            $out[] = [$values[$i], $values[$i + 1]];
        }

        return $out;
    }

    /**
     * Titik-titik di antara titik awal dan akhir sebuah sisi (termasuk
     * keduanya), mengikuti arah path keping.
     *
     * @param list<array{float, float}> $points
     * @param array{int, int}           $from
     * @param array{int, int}           $to
     *
     * @return list<array{float, float}>
     */
    private function sideBetween(array $points, array $from, array $to): array
    {
        $start = null;

        foreach ($points as $i => $point) {
            if ($start === null && $point == $from) {
                $start = $i;
            } elseif ($start !== null && $point == $to) {
                return array_slice($points, $start, $i - $start + 1);
            }
        }

        // sisi terakhir (kiri) ditutup Z: kembali ke titik awal path
        if ($start !== null && $points[0] == $to) {
            return [...array_slice($points, $start), $points[0]];
        }

        return [];
    }
}
