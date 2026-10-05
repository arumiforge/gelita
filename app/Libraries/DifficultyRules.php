<?php

namespace App\Libraries;

/**
 * Aturan per tingkat kesulitan wilayah (`levels.difficulty`: mudah, sedang,
 * sulit) untuk siswa kelas 5 SD:
 *
 * - Susun Gambar: ukuran papan dan bentuk keping (3 × 3 persegi, 4 × 4
 *   persegi, 4 × 4 jigsaw).
 * - Isi Rumpang bacaan: jumlah rumpang dan jumlah kata pengecoh di bank kata.
 * - Rentang Taksonomi Bloom revisi (Anderson & Krathwohl, 2001) untuk soal
 *   rumpang di tingkat itu.
 *
 * Satu-satunya sumber aturan ini. Tidak bergantung pada framework, karena
 * dibaca juga oleh docs/bank-soal/build-workbook.php yang berjalan tanpa
 * CodeIgniter.
 */
final class DifficultyRules
{
    /** Ranah kognitif Taksonomi Bloom revisi: kode → nama Indonesia. */
    public const BLOOM = [
        'C1' => 'Mengingat',
        'C2' => 'Memahami',
        'C3' => 'Menerapkan',
        'C4' => 'Menganalisis',
        'C5' => 'Mengevaluasi',
        'C6' => 'Mencipta',
    ];

    /** Bentuk keping Susun Gambar. */
    public const SHAPES = ['square', 'jigsaw'];

    public const DEFAULT = 'mudah';

    /**
     * reading.blanks/distractors = [minimum, maksimum]; reading.default_distractors
     * dipakai isi bawaan dan sebagai saran di panel.
     */
    public const BY_DIFFICULTY = [
        'mudah' => [
            'puzzle'  => ['grid' => 3, 'shape' => 'square'],
            'reading' => ['blanks' => [3, 3], 'distractors' => [0, 0], 'default_distractors' => 0],
            'bloom'   => ['C1', 'C2'],
        ],
        'sedang' => [
            'puzzle'  => ['grid' => 4, 'shape' => 'square'],
            'reading' => ['blanks' => [4, 5], 'distractors' => [1, 3], 'default_distractors' => 2],
            'bloom'   => ['C2', 'C3'],
        ],
        'sulit' => [
            'puzzle'  => ['grid' => 4, 'shape' => 'jigsaw'],
            'reading' => ['blanks' => [6, 7], 'distractors' => [1, 3], 'default_distractors' => 2],
            'bloom'   => ['C3', 'C4'],
        ],
    ];

    /** Aturan satu tingkat; tingkat yang tidak dikenal dianggap mudah. */
    public static function for(?string $difficulty): array
    {
        return self::BY_DIFFICULTY[self::normalize($difficulty)];
    }

    public static function normalize(?string $difficulty): string
    {
        $difficulty = strtolower(trim((string) $difficulty));

        return isset(self::BY_DIFFICULTY[$difficulty]) ? $difficulty : self::DEFAULT;
    }

    /** @return array{grid: int, shape: string} */
    public static function puzzle(?string $difficulty): array
    {
        return self::for($difficulty)['puzzle'];
    }

    /** @return array{blanks: array{int, int}, distractors: array{int, int}, default_distractors: int} */
    public static function reading(?string $difficulty): array
    {
        return self::for($difficulty)['reading'];
    }

    /**
     * Semua kode Bloom dalam rentang tingkat itu, mis. sedang → [C2, C3].
     *
     * @return list<string>
     */
    public static function bloomRange(?string $difficulty): array
    {
        [$from, $to] = self::for($difficulty)['bloom'];
        $codes       = array_keys(self::BLOOM);
        $start       = (int) array_search($from, $codes, true);
        $end         = (int) array_search($to, $codes, true);

        return array_slice($codes, $start, $end - $start + 1);
    }

    public static function isBloom(?string $code): bool
    {
        return isset(self::BLOOM[strtoupper(trim((string) $code))]);
    }

    public static function bloomInRange(?string $code, ?string $difficulty): bool
    {
        return in_array(strtoupper(trim((string) $code)), self::bloomRange($difficulty), true);
    }

    /** "C2 · Memahami"; kode kosong → '' dan kode tak dikenal apa adanya. */
    public static function bloomLabel(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        if ($code === '') {
            return '';
        }

        return isset(self::BLOOM[$code]) ? $code . ' · ' . self::BLOOM[$code] : $code;
    }

    /** "C1–C2 (Mengingat, Memahami)" untuk teks bantuan panel dan workbook. */
    public static function bloomRangeLabel(?string $difficulty): string
    {
        $codes = self::bloomRange($difficulty);
        $names = array_map(static fn (string $code): string => self::BLOOM[$code], $codes);

        return $codes[0] . '–' . $codes[count($codes) - 1] . ' (' . implode(', ', $names) . ')';
    }

    /** "3 × 3 kotak", "4 × 4 jigsaw" */
    public static function layoutLabel(int $grid, string $shape): string
    {
        return $grid . ' × ' . $grid . ' ' . ($shape === 'jigsaw' ? 'jigsaw' : 'kotak');
    }
}
