<?php

namespace App\Libraries;

/**
 * Error codes of the research bank (docs/revisi-10-analitik/03_KODE_DAN_ARTI.md,
 * 31 §7.1, §8.2). One code describes why a wrong choice is wrong; the server
 * copies it into `item_tries.error_code` when the try is graded.
 *
 * Pure (no framework): docs/bank-soal/build-workbook.php uses it too.
 *
 * - `*-LAIN` codes exist only for manual coding (`item_tries.manual_code`,
 *   L5 import of part 3) and never appear in the bank.
 * - `NON-*` codes are not error codes of a choice (no try, or a derived
 *   flag of part 9) and never appear in the bank either.
 */
final class ErrorCodes
{
    /** Code → [meaning (Indonesian, as in 03), dimension]. */
    public const CODES = [
        'INF-LIT'  => ['Salah membaca informasi tersurat', 'D1'],
        'INF-KAT'  => ['Terkecoh kesamaan kata', 'D1'],
        'INF-REL'  => ['Benar, tapi tidak menjawab pertanyaan', 'D1'],
        'INF-HUB'  => ['Salah menghubungkan informasi (sebab-akibat, urutan)', 'D1'],
        'INF-SUM'  => ['Salah menilai sumber', 'D1'],
        'INF-FOP'  => ['Mencampur fakta dan pendapat', 'D1'],
        'INF-BUD'  => ['Tertukar benda budaya yang mirip', 'PBK'],
        'INF-GEN'  => ['Menggeneralisasi berlebihan', 'D1'],
        'INF-LAIN' => ['Kesalahan informasi lain (dikode manual)', 'D1'],
        'DIG-PRI'  => ['Membagikan data pribadi berlebihan', 'D2'],
        'DIG-SAN'  => ['Membagikan kata sandi atau kode OTP', 'D2'],
        'DIG-IZN'  => ['Membagikan foto atau obrolan tanpa izin', 'D2'],
        'DIG-ASG'  => ['Menanggapi orang asing', 'D2'],
        'DIG-HOX'  => ['Meneruskan kabar yang belum dicek', 'D2'],
        'DIG-ETK'  => ['Berkomunikasi tidak etis', 'D2'],
        'DIG-PSF'  => ['Terlalu pasif, tidak melapor', 'D2'],
        'DIG-LAIN' => ['Kesalahan tindakan lain (dikode manual)', 'D2'],
        'BUD-ATR'  => ['Salah menunjuk pembuat dan sumber karya budaya', 'D3'],
        'BUD-REP'  => ['Menerima atau menyebarkan keterangan budaya yang keliru', 'D3'],
        'BUD-IZN'  => ['Mengunggah ulang karya budaya tanpa izin', 'D3'],
        'BUD-ETK'  => ['Menanggapi tradisi dengan tidak menghormati', 'D3'],
        'BUD-LAIN' => ['Kesalahan budaya digital lain (dikode manual)', 'D3'],
        'R-RISK'   => ['Paham risikonya', 'D2'],
        'R-ETIK'   => ['Empati atau izin', 'D2'],
        'R-ATUR'   => ['Karena disuruh atau dilarang', 'D2'],
        'R-SOS'    => ['Tekanan teman atau ingin disukai', 'D2'],
        'R-ACAK'   => ['Alasannya tidak nyambung', 'D2'],
    ];

    /** Manual-only codes: never in the bank (31 §8.2), only in `item_tries.manual_code`. */
    public const MANUAL = ['INF-LAIN', 'DIG-LAIN', 'BUD-LAIN'];

    /**
     * Codes allowed per kind of wrong choice (31 §8.2). `verdict_D1a` keeps
     * INF-GEN as well: 08 uses it on a D1a statement (mgl-3-a02).
     */
    public const BY_CHOICE = [
        'word'        => ['INF-LIT', 'INF-KAT', 'INF-REL', 'INF-HUB', 'INF-GEN'],
        'verdict_D1a' => ['INF-LIT', 'INF-KAT', 'INF-REL', 'INF-HUB', 'INF-GEN'],
        'verdict_D1b' => ['INF-SUM', 'INF-FOP', 'INF-GEN'],
        'object'      => ['INF-BUD', 'INF-LIT'],
        'part'        => ['BUD-ATR', 'BUD-REP', 'BUD-IZN', 'BUD-ETK'],
        'action'      => ['DIG-PRI', 'DIG-SAN', 'DIG-IZN', 'DIG-ASG', 'DIG-HOX', 'DIG-ETK', 'DIG-PSF'],
        'reason'      => ['R-RISK', 'R-ETIK', 'R-ATUR', 'R-SOS', 'R-ACAK'],
    ];

    /** Round-2 clue type (`d3_clue_type`) → the code of every non-main wrong part (08 → Cara membaca). */
    public const CLUE_TYPE_CODES = ['ATR' => 'BUD-ATR', 'REP' => 'BUD-REP', 'IZN' => 'BUD-IZN', 'ETK' => 'BUD-ETK'];

    /** Skenario Digital topics (`d2_topic`, 06 → Topik Skenario Digital). */
    public const D2_TOPICS = ['PRI', 'SAN', 'IZN', 'ASG'];

    public static function isKnown(?string $code): bool
    {
        return isset(self::CODES[strtoupper(trim((string) $code))]);
    }

    public static function isManual(?string $code): bool
    {
        return in_array(strtoupper(trim((string) $code)), self::MANUAL, true);
    }

    /** True for codes that may never stand in the bank: unknown, `*-LAIN`, `NON-*`. */
    public static function forbiddenInBank(?string $code): bool
    {
        $code = strtoupper(trim((string) $code));

        return str_starts_with($code, 'NON-') || self::isManual($code) || ! self::isKnown($code);
    }

    /** @param string $kind a key of BY_CHOICE */
    public static function allowedFor(string $kind, ?string $code): bool
    {
        return in_array(strtoupper(trim((string) $code)), self::BY_CHOICE[$kind] ?? [], true);
    }

    /** "INF-LIT · Salah membaca informasi tersurat"; unknown codes as they are. */
    public static function label(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return isset(self::CODES[$code]) ? $code . ' · ' . self::CODES[$code][0] : $code;
    }
}
