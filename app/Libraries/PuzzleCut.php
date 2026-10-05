<?php

namespace App\Libraries;

use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Pemotong gambar Susun Gambar: dari satu gambar utuh menjadi n × n keping
 * berbentuk kotak atau jigsaw (bertonjolan), tanpa membuat berkas baru.
 *
 * Setiap keping digambar browser sebagai SVG: gambar utuh selebar papan
 * dipotong (clip-path) oleh path keping itu. Satuan koordinat: satu sel =
 * 100 satuan, papan = n × 100. Keping jigsaw melebar `MARGIN` satuan ke
 * segala arah agar tonjolannya muat; tepi papan selalu lurus.
 *
 * Bentuk tonjolan memakai kurva generator jigsaw Draradech (tiga Bezier
 * kubik per sisi). Arah tonjolan dan sedikit getarannya diacak dengan benih
 * `item_key`, sehingga semua siswa melihat potongan yang sama untuk gambar
 * yang sama, dan dua keping yang bersebelahan memakai kurva yang sama
 * (terbalik) sehingga pas satu sama lain.
 *
 * Kelas murni (tanpa framework); ukuran papan per tingkat kesulitan ada di
 * DifficultyRules.
 */
final class PuzzleCut
{
    /** Satu sel papan, dalam satuan SVG. */
    public const CELL = 100;

    /** Ruang di sekeliling keping jigsaw untuk tonjolan (≥ CELL × (3 × TAB + JITTER)). */
    public const MARGIN = 35;

    /** Ukuran tonjolan relatif terhadap panjang sisi. */
    private const TAB = 0.10;

    /** Getaran acak posisi/ukuran tonjolan. */
    private const JITTER = 0.03;

    /**
     * Ukuran papan dan bentuk keping untuk satu soal: isian soal itu sendiri
     * (`config_json.grid/shape`, ditetapkan saat soal dibuat), lalu √(panjang
     * kunci), lalu ukuran lama tantangan, lalu aturan tingkat wilayah. Soal
     * lama tanpa catatan bentuk berupa kotak.
     *
     * @param array<string, mixed> $itemConfig
     * @param list<mixed>|null     $answerOrder kunci urutan (answer_key_json.order)
     *
     * @return array{grid: int, shape: string}
     */
    public static function resolve(array $itemConfig, ?array $answerOrder, mixed $nodeGrid, ?string $difficulty): array
    {
        $rule     = DifficultyRules::puzzle($difficulty);
        $fromRule = false;
        $grid     = (int) ($itemConfig['grid'] ?? 0);

        if ($grid < 2 && is_array($answerOrder) && $answerOrder !== []) {
            $root = (int) round(sqrt(count($answerOrder)));
            $grid = $root * $root === count($answerOrder) ? $root : 0;
        }

        if ($grid < 2) {
            $grid = (int) $nodeGrid;
        }

        if ($grid < 2) {
            $grid     = $rule['grid'];
            $fromRule = true;
        }

        $shape = $itemConfig['shape'] ?? null;

        if (! in_array($shape, DifficultyRules::SHAPES, true)) {
            // ukuran dari soal/kunci/tantangan lama = soal lama berbentuk kotak
            $shape = $fromRule ? $rule['shape'] : 'square';
        }

        return ['grid' => max(2, min(6, $grid)), 'shape' => $shape];
    }

    /**
     * Path dan kotak tampilan setiap keping, urut baris demi baris dari kiri
     * atas (indeks keping = posisi benarnya).
     *
     * @return list<array{index: int, row: int, col: int, path: string, viewBox: string}>
     */
    public static function pieces(int $grid, string $shape, string $seed): array
    {
        $grid = max(2, $grid);
        $s    = self::CELL;

        if ($shape !== 'jigsaw') {
            $out = [];

            for ($r = 0; $r < $grid; $r++) {
                for ($c = 0; $c < $grid; $c++) {
                    $x     = $c * $s;
                    $y     = $r * $s;
                    $out[] = [
                        'index'   => $r * $grid + $c,
                        'row'     => $r,
                        'col'     => $c,
                        'path'    => "M{$x} {$y}H" . ($x + $s) . 'V' . ($y + $s) . "H{$x}Z",
                        'viewBox' => "{$x} {$y} {$s} {$s}",
                    ];
                }
            }

            return $out;
        }

        [$horizontal, $vertical] = self::edges($grid, $seed);

        $m   = self::MARGIN;
        $out = [];

        for ($r = 0; $r < $grid; $r++) {
            for ($c = 0; $c < $grid; $c++) {
                $x = $c * $s;
                $y = $r * $s;

                $d = 'M' . self::num($x) . ' ' . self::num($y);
                $d .= $r === 0 ? 'L' . self::num($x + $s) . ' ' . self::num($y) : self::curve($horizontal[$r][$c], false);
                $d .= $c === $grid - 1 ? 'L' . self::num($x + $s) . ' ' . self::num($y + $s) : self::curve($vertical[$r][$c + 1], false);
                $d .= $r === $grid - 1 ? 'L' . self::num($x) . ' ' . self::num($y + $s) : self::curve($horizontal[$r + 1][$c], true);
                $d .= $c === 0 ? '' : self::curve($vertical[$r][$c], true);
                $d .= 'Z';

                $out[] = [
                    'index'   => $r * $grid + $c,
                    'row'     => $r,
                    'col'     => $c,
                    'path'    => $d,
                    'viewBox' => ($x - $m) . ' ' . ($y - $m) . ' ' . ($s + 2 * $m) . ' ' . ($s + 2 * $m),
                ];
            }
        }

        return $out;
    }

    /** Lebar tonjolan keping di luar selnya, dalam persen sel (CSS inset negatif). */
    public static function overhang(string $shape): float
    {
        return $shape === 'jigsaw' ? self::MARGIN / self::CELL * 100 : 0.0;
    }

    /**
     * Jarak kisi keping dari tepi papan, dalam persen lebar papan, agar
     * tonjolan keping yang belum di tempatnya tetap terlihat di dalam papan.
     */
    public static function boardInset(int $grid, string $shape): float
    {
        return $shape === 'jigsaw' ? round(self::MARGIN / ($grid * self::CELL + 2 * self::MARGIN) * 100, 3) : 0.0;
    }

    /**
     * Titik kendali setiap sisi dalam, dalam koordinat papan.
     * H[r][c]: sisi mendatar di atas baris r (r = 1 … n−1), kolom c.
     * V[r][c]: sisi tegak di kiri kolom c (c = 1 … n−1), baris r.
     *
     * @return array{0: array<int, array<int, list<array{float, float}>>>, 1: array<int, array<int, list<array{float, float}>>>}
     */
    private static function edges(int $grid, string $seed): array
    {
        $random = new Randomizer(new Mt19937(crc32('jigsaw|' . $seed . '|' . $grid)));
        $s      = self::CELL;

        $horizontal = [];
        $vertical   = [];

        for ($r = 1; $r < $grid; $r++) {
            for ($c = 0; $c < $grid; $c++) {
                $horizontal[$r][$c] = array_map(
                    static fn (array $p): array => [$c * $s + $p[0] * $s, $r * $s + $p[1] * $s],
                    self::edgeShape($random),
                );
            }
        }

        for ($r = 0; $r < $grid; $r++) {
            for ($c = 1; $c < $grid; $c++) {
                $vertical[$r][$c] = array_map(
                    static fn (array $p): array => [$c * $s + $p[1] * $s, $r * $s + $p[0] * $s],
                    self::edgeShape($random),
                );
            }
        }

        return [$horizontal, $vertical];
    }

    /**
     * Sepuluh titik satu sisi dalam koordinat sisi (u sepanjang sisi 0…1,
     * v tegak lurus); tanda v menentukan arah tonjolan.
     *
     * @return list<array{float, float}>
     */
    private static function edgeShape(Randomizer $random): array
    {
        $jitter = static fn (): float => $random->getInt(-1000, 1000) / 1000 * self::JITTER;

        $flip = $random->getInt(0, 1) === 1 ? 1.0 : -1.0;
        $b    = $jitter();
        $c    = $jitter();
        $d    = $jitter();
        $t    = self::TAB;

        $points = [
            [0.0, 0.0],
            [0.2, 0.0],
            [0.5 + $b + $d, -$t + $c],
            [0.5 - $t + $b, $t + $c],
            [0.5 - 2 * $t + $b - $d, 3 * $t + $c],
            [0.5 + 2 * $t + $b - $d, 3 * $t + $c],
            [0.5 + $t + $b, $t + $c],
            [0.5 + $b + $d, -$t + $c],
            [0.8, 0.0],
            [1.0, 0.0],
        ];

        return array_map(static fn (array $p): array => [$p[0], $p[1] * $flip], $points);
    }

    /**
     * Tiga Bezier kubik satu sisi, maju (P1 … P9) atau mundur (P8 … P0).
     *
     * @param list<array{float, float}> $points
     */
    private static function curve(array $points, bool $reverse): string
    {
        $order = $reverse ? [8, 7, 6, 5, 4, 3, 2, 1, 0] : [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $out   = '';

        foreach (array_chunk($order, 3) as $triple) {
            $out .= 'C' . implode(' ', array_map(
                static fn (int $i): string => self::num($points[$i][0]) . ' ' . self::num($points[$i][1]),
                $triple,
            ));
        }

        return $out;
    }

    private static function num(float|int $value): string
    {
        $text = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($text, '0'), '.') ?: '0';
    }
}
