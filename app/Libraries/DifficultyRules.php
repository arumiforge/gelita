<?php

namespace App\Libraries;

/**
 * Blueprint counts per region difficulty (`levels.difficulty`: mudah,
 * sedang, sulit) of the grade 5 research bank: 06 → Kisi-kisi per wilayah,
 * 31 §8.1. Counts are design, not content (31 K2): a revised blueprint
 * changes one constant here, revised content only changes the data files.
 *
 * - Susun Gambar: board size and piece shape.
 * - Isi Rumpang: exact number of blanks and decoy words.
 * - Benar atau Salah: statements, key split, sub-aspects per variant, and
 *   the anchor card shared by both variants (`x`).
 * - Cari Objek: round 1 (targets + decoys, one scene for all), round 2
 *   (clues, tap parts, main lures per clue).
 * - Skenario Digital: scenarios, actions, reasons, steps.
 * - Bloom: one range per region (19); AKM levels and text genres expected;
 *   error codes every region should cover (06 → Sebaran kode yang dituju).
 *
 * Revised Bloom taxonomy (Anderson & Krathwohl, 2001). Pure (no framework):
 * docs/bank-soal/build-workbook.php reads it without CodeIgniter.
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

    /** Kinds that used to have their own Bloom range; since 31 §8.1 all share the region range. */
    public const BLOOM_KINDS = ['reading', 'truefalse', 'hunt', 'scenario'];

    public const AKM_LEVELS = ['L1', 'L2', 'L3'];

    /** Dimensions and D1 sub-aspects (06 → Pemetaan dimensi ke kerangka). */
    public const DIMENSIONS = ['D1', 'D2', 'D3', 'PBK'];

    public const SUBASPECTS = ['D1a', 'D1b'];

    /** PIRLS 2026 processes (D1 items). */
    public const PIRLS = ['retrieve', 'infer', 'integrate', 'evaluate'];

    /** Text genres of 06 → Genre teks digital (`reading_passages.text_genre`). */
    public const GENRES = [
        'artikel_pustaka', 'artikel_majalah', 'papan_info', 'unggahan_resmi', 'pesan_grup',
        'hasil_pencarian', 'situs_wisata', 'dua_sumber', 'unggahan_bersponsor',
    ];

    /** Display templates of a digital text (`reading_passages.layout`, 31 §6.2). */
    public const LAYOUTS = ['plain', 'info_screen', 'chat', 'search', 'post'];

    public const DEFAULT = 'mudah';

    public const BY_DIFFICULTY = [
        'mudah' => [
            'puzzle'    => ['grid' => 3, 'shape' => 'square'],
            'reading'   => ['blanks' => [4, 4], 'distractors' => [2, 2], 'default_distractors' => 2, 'genre' => 'artikel_pustaka'],
            'bloom'     => ['C1', 'C3'],
            'truefalse' => ['statements' => 4, 'key_split' => [2, 2], 'subaspects' => ['D1a' => 3, 'D1b' => 1], 'genre' => 'papan_info',
                            'skills' => ['informasi_tersurat', 'mengenali_fakta', 'mengenali_gambar', 'mengenali_penanda_sumber']],
            'anchor'    => ['statements' => 2, 'key_split' => [1, 1], 'subaspects' => ['D1a' => 2], 'genre' => 'pesan_grup'],
            'hunt'      => ['targets' => 4, 'decoys' => 4, 'clues' => 4, 'parts' => 8, 'main' => 2],
            'scenario'  => ['count' => 4, 'actions' => 3, 'reasons' => 3, 'steps' => 1, 'all_reasons' => false],
            'akm'       => ['D1a' => ['L1', 'L2'], 'D1b' => ['L1'], 'D2' => ['L1'], 'D3' => ['L1']],
            'codes'     => ['INF-LIT', 'INF-KAT', 'INF-REL', 'INF-HUB', 'INF-SUM'],
        ],
        'sedang' => [
            'puzzle'    => ['grid' => 4, 'shape' => 'square'],
            'reading'   => ['blanks' => [5, 5], 'distractors' => [2, 2], 'default_distractors' => 2, 'genre' => 'artikel_majalah'],
            'bloom'     => ['C2', 'C4'],
            'truefalse' => ['statements' => 6, 'key_split' => [3, 3], 'subaspects' => ['D1a' => 3, 'D1b' => 3], 'genre' => 'unggahan_resmi',
                            'skills' => ['gagasan_pokok', 'menghubungkan_informasi', 'mengklasifikasikan_tindakan', 'membedakan_fakta_pendapat', 'menilai_kredibilitas', 'membandingkan_sumber']],
            'anchor'    => ['statements' => 2, 'key_split' => [1, 1], 'subaspects' => ['D1a' => 1, 'D1b' => 1], 'genre' => 'hasil_pencarian'],
            'hunt'      => ['targets' => 4, 'decoys' => 6, 'clues' => 4, 'parts' => 10, 'main' => 3],
            'scenario'  => ['count' => 4, 'actions' => 4, 'reasons' => 4, 'steps' => 1, 'all_reasons' => false],
            'akm'       => ['D1a' => ['L2'], 'D1b' => ['L3'], 'D2' => ['L2'], 'D3' => ['L2']],
            'codes'     => ['INF-LIT', 'INF-KAT', 'INF-REL', 'INF-HUB', 'INF-GEN', 'INF-FOP', 'INF-SUM'],
        ],
        'sulit' => [
            'puzzle'    => ['grid' => 4, 'shape' => 'jigsaw'],
            'reading'   => ['blanks' => [6, 6], 'distractors' => [2, 2], 'default_distractors' => 2, 'genre' => 'situs_wisata'],
            'bloom'     => ['C2', 'C5'],
            'truefalse' => ['statements' => 6, 'key_split' => [3, 3], 'subaspects' => ['D1a' => 2, 'D1b' => 4], 'genre' => 'dua_sumber',
                            'skills' => ['membandingkan_informasi', 'menilai_pilihan', 'menentukan_solusi', 'menilai_kredibilitas', 'membandingkan_sumber']],
            'anchor'    => ['statements' => 2, 'key_split' => [1, 1], 'subaspects' => ['D1b' => 2], 'genre' => 'unggahan_bersponsor'],
            'hunt'      => ['targets' => 4, 'decoys' => 8, 'clues' => 4, 'parts' => 12, 'main' => 3],
            'scenario'  => ['count' => 4, 'actions' => 4, 'reasons' => 5, 'steps' => 2, 'all_reasons' => true],
            'akm'       => ['D1a' => ['L2'], 'D1b' => ['L3'], 'D2' => ['L3'], 'D3' => ['L3']],
            'codes'     => ['INF-LIT', 'INF-KAT', 'INF-REL', 'INF-HUB', 'INF-GEN', 'INF-SUM'],
        ],
    ];

    /** BUD codes and main risky DIG codes every region covers (06 → Sebaran kode yang dituju). */
    public const CODES_EVERY_REGION = ['BUD-ATR', 'BUD-REP', 'BUD-IZN', 'BUD-ETK', 'DIG-PRI', 'DIG-SAN', 'DIG-IZN', 'DIG-HOX'];

    /** Kode keterampilan Benar atau Salah → label Indonesia (ditampilkan admin & ekspor). D1b skills since 31 §8.1. */
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
        'mengenali_penanda_sumber'    => 'Mengenali penanda sumber',
        'menilai_kredibilitas'        => 'Menilai kredibilitas sumber',
        'mengenali_iklan'             => 'Mengenali iklan',
        'membandingkan_sumber'        => 'Membandingkan sumber',
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

    /** @return array{blanks: array{int, int}, distractors: array{int, int}, default_distractors: int, genre: string} */
    public static function reading(?string $difficulty): array
    {
        return self::for($difficulty)['reading'];
    }

    /** @return array{statements: int, key_split: array{int, int}, subaspects: array<string, int>, genre: string, skills: list<string>} */
    public static function trueFalse(?string $difficulty): array
    {
        return self::for($difficulty)['truefalse'];
    }

    /** @return array{statements: int, key_split: array{int, int}, subaspects: array<string, int>, genre: string} */
    public static function anchor(?string $difficulty): array
    {
        return self::for($difficulty)['anchor'];
    }

    /** @return array{targets: int, decoys: int, clues: int, parts: int, main: int} */
    public static function hunt(?string $difficulty): array
    {
        return self::for($difficulty)['hunt'];
    }

    /** @return array{count: int, actions: int, reasons: int, steps: int, all_reasons: bool} */
    public static function scenario(?string $difficulty): array
    {
        return self::for($difficulty)['scenario'];
    }

    /**
     * AKM levels expected for a sub-aspect (D1a, D1b) or dimension (D2, D3).
     *
     * @return list<string> empty for PBK
     */
    public static function akm(?string $difficulty, string $scope): array
    {
        return self::for($difficulty)['akm'][$scope] ?? [];
    }

    /** INF codes this region should cover at least once. @return list<string> */
    public static function expectedCodes(?string $difficulty): array
    {
        return [...self::for($difficulty)['codes'], ...self::CODES_EVERY_REGION];
    }

    /**
     * Every Bloom code in the region range, e.g. sedang → [C2, C3, C4].
     * Since 31 §8.1 one range covers every kind of item (19 reviews labels
     * per region); $kind stays for callers and must be one of BLOOM_KINDS.
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
        if (! in_array($kind, self::BLOOM_KINDS, true)) {
            throw new \InvalidArgumentException("Jenis soal Bloom '{$kind}' tidak dikenal (" . implode(', ', self::BLOOM_KINDS) . ').');
        }

        return self::for($difficulty)['bloom'];
    }
}
