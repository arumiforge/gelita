<?php

namespace App\Libraries;

/**
 * Aturan per tingkat kesulitan wilayah (`levels.difficulty`: mudah, sedang,
 * sulit) untuk siswa kelas 5 SD (perombakan kelas 5, Dok 2 §2):
 *
 * - Susun Gambar: ukuran papan dan bentuk keping (3 × 3 persegi, 4 × 4
 *   persegi, 4 × 4 jigsaw).
 * - Isi Rumpang: jumlah rumpang dan kata pengecoh di bank kata (tepat, bukan
 *   rentang, agar ketiga variasi satu wilayah setara) dan rentang Bloom.
 * - Benar atau Salah: jumlah paragraf bacaan, jumlah pernyataan, rentang
 *   Bloom, dan keterampilan membaca yang diukur.
 * - Cari Objek: jumlah benda dicari, jumlah pengecoh, dan rentang Bloom
 *   petunjuknya.
 *
 * Taksonomi Bloom revisi (Anderson & Krathwohl, 2001). Satu-satunya sumber
 * aturan ini. Tidak bergantung pada framework, karena dibaca juga oleh
 * docs/bank-soal/build-workbook.php yang berjalan tanpa CodeIgniter.
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

    /** Jenis soal yang punya rentang Bloom sendiri (bloomRange()). */
    public const BLOOM_KINDS = ['reading', 'truefalse', 'hunt'];

    public const DEFAULT = 'mudah';

    /**
     * reading.blanks/distractors = [minimum, maksimum] — sejak Dok 2 keduanya
     * sama (jumlah tepat); reading.default_distractors dipakai isi bawaan.
     * `bloom` = rentang Isi Rumpang; Benar atau Salah dan Cari Objek punya
     * rentangnya sendiri.
     */
    public const BY_DIFFICULTY = [
        'mudah' => [
            'puzzle'    => ['grid' => 3, 'shape' => 'square'],
            'reading'   => ['blanks' => [3, 3], 'distractors' => [1, 1], 'default_distractors' => 1],
            'bloom'     => ['C1', 'C2'],                       // Isi Rumpang
            'truefalse' => ['paragraphs' => 1, 'statements' => 3, 'bloom' => ['C1', 'C2'],
                            'skills' => ['informasi_tersurat', 'mengenali_fakta', 'mengenali_gambar']],
            'hunt'      => ['targets' => 4, 'decoys' => 4, 'bloom' => ['C1', 'C2']],
        ],
        'sedang' => [
            'puzzle'    => ['grid' => 4, 'shape' => 'square'],
            'reading'   => ['blanks' => [5, 5], 'distractors' => [2, 2], 'default_distractors' => 2],
            'bloom'     => ['C2', 'C3'],
            'truefalse' => ['paragraphs' => 2, 'statements' => 5, 'bloom' => ['C2', 'C4'],
                            'skills' => ['gagasan_pokok', 'menghubungkan_informasi', 'mengklasifikasikan_tindakan', 'membedakan_fakta_pendapat']],
            'hunt'      => ['targets' => 4, 'decoys' => 6, 'bloom' => ['C2', 'C3']],
        ],
        'sulit' => [
            'puzzle'    => ['grid' => 4, 'shape' => 'jigsaw'],
            'reading'   => ['blanks' => [7, 7], 'distractors' => [2, 2], 'default_distractors' => 2],
            'bloom'     => ['C3', 'C4'],
            'truefalse' => ['paragraphs' => 3, 'statements' => 6, 'bloom' => ['C4', 'C5'],
                            'skills' => ['membandingkan_informasi', 'menilai_pilihan', 'menentukan_solusi']],
            'hunt'      => ['targets' => 4, 'decoys' => 8, 'bloom' => ['C3', 'C4']],
        ],
    ];

    /** Kode keterampilan Benar atau Salah → label Indonesia (ditampilkan admin & ekspor). */
    public const SKILLS = [
        'informasi_tersurat'          => 'Menemukan informasi tersurat',
        'mengenali_fakta'             => 'Mengenali fakta yang sesuai',
        'mengenali_gambar'            => 'Mengenali gambar yang sesuai',
        'gagasan_pokok'               => 'Menentukan gagasan pokok',
        'menghubungkan_informasi'     => 'Menghubungkan informasi',
        'mengklasifikasikan_tindakan' => 'Mengklasifikasikan tindakan',
        'membedakan_fakta_pendapat'   => 'Membedakan fakta dan pendapat',
        'membandingkan_informasi'     => 'Membandingkan informasi',
        'menilai_pilihan'             => 'Menilai pilihan',
        'menentukan_solusi'           => 'Menentukan solusi berdasarkan bukti',
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

    /** @return array{paragraphs: int, statements: int, bloom: array{string, string}, skills: list<string>} */
    public static function trueFalse(?string $difficulty): array
    {
        return self::for($difficulty)['truefalse'];
    }

    /** @return array{targets: int, decoys: int, bloom: array{string, string}} */
    public static function hunt(?string $difficulty): array
    {
        return self::for($difficulty)['hunt'];
    }

    /**
     * Semua kode Bloom dalam rentang tingkat itu untuk satu jenis soal, mis.
     * sedang + reading → [C2, C3]; sedang + truefalse → [C2, C3, C4].
     *
     * @param string $kind reading (Isi Rumpang) | truefalse (Benar atau Salah) | hunt (petunjuk Cari Objek)
     *
     * @return list<string>
     */
    public static function bloomRange(?string $difficulty, string $kind = 'reading'): array
    {
        [$from, $to] = self::bloomBounds($difficulty, $kind);
        $codes       = array_keys(self::BLOOM);
        $start       = (int) array_search($from, $codes, true);
        $end         = (int) array_search($to, $codes, true);

        return array_slice($codes, $start, $end - $start + 1);
    }

    public static function isBloom(?string $code): bool
    {
        return isset(self::BLOOM[strtoupper(trim((string) $code))]);
    }

    public static function bloomInRange(?string $code, ?string $difficulty, string $kind = 'reading'): bool
    {
        return in_array(strtoupper(trim((string) $code)), self::bloomRange($difficulty, $kind), true);
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
    public static function bloomRangeLabel(?string $difficulty, string $kind = 'reading'): string
    {
        $codes = self::bloomRange($difficulty, $kind);
        $names = array_map(static fn (string $code): string => self::BLOOM[$code], $codes);

        return $codes[0] . '–' . $codes[count($codes) - 1] . ' (' . implode(', ', $names) . ')';
    }

    public static function isSkill(?string $code): bool
    {
        return isset(self::SKILLS[trim((string) $code)]);
    }

    /** "Menilai pilihan"; kode kosong → '' dan kode tak dikenal apa adanya. */
    public static function skillLabel(?string $code): string
    {
        $code = trim((string) $code);

        return self::SKILLS[$code] ?? $code;
    }

    /** "3 × 3 kotak", "4 × 4 jigsaw" */
    public static function layoutLabel(int $grid, string $shape): string
    {
        return $grid . ' × ' . $grid . ' ' . ($shape === 'jigsaw' ? 'jigsaw' : 'kotak');
    }

    /** @return array{string, string} */
    private static function bloomBounds(?string $difficulty, string $kind): array
    {
        $rule = self::for($difficulty);

        return match ($kind) {
            'truefalse' => $rule['truefalse']['bloom'],
            'hunt'      => $rule['hunt']['bloom'],
            'reading'   => $rule['bloom'],
            default     => throw new \InvalidArgumentException("Jenis soal Bloom '{$kind}' tidak dikenal (reading, truefalse, hunt)."),
        };
    }
}
