<?php

namespace App\Libraries;

/**
 * Kunci pencocokan nama sekolah.
 *
 * Siswa menulis sekolah yang sama dengan banyak cara: "SD 1 CENDONO",
 * "SD NEGERI 1 CENDONO", "sdn 01 Cendono". key() menyeragamkan semuanya:
 * huruf besar, singkatan jenjang dibakukan, kata status (NEGERI/SWASTA) dan
 * "NO/NOMOR" dibuang, angka tanpa nol di depan, dan urutan kata setelah
 * jenjang diabaikan. Kunci ini hanya untuk mencocokkan — yang ditampilkan
 * tetap nama resmi dari data induk.
 *
 * Aturannya sengaja sempit: dua sekolah berbeda (SD 1 vs SD 2, SD vs MI)
 * harus tetap berbeda kuncinya. Kecocokan yang ragu diserahkan ke admin
 * (halaman Sekolah), bukan ditebak di sini.
 */
final class SchoolName
{
    /** Token jenjang; diletakkan paling depan pada kunci. */
    public const STAGE_TOKENS = ['SD', 'MI', 'SMP', 'MTS', 'SLB', 'SDLB', 'SMPLB', 'SDTK', 'SPK'];

    /** Frasa panjang → singkatan baku (dicocokkan sebagai kata utuh). */
    private const PHRASES = [
        'SEKOLAH MENENGAH PERTAMA' => 'SMP',
        'SEKOLAH DASAR LUAR BIASA' => 'SDLB',
        'SEKOLAH LUAR BIASA'       => 'SLB',
        'SEKOLAH DASAR'            => 'SD',
        'MADRASAH IBTIDAIYAH'      => 'MI',
        'MADRASAH TSANAWIYAH'      => 'MTS',
        'ISLAM TERPADU'            => 'IT',
    ];

    /** Singkatan yang menempel → token baku; N/S (negeri/swasta) ikut dibuang. */
    private const GLUED = [
        'SDN'           => ['SD'],
        'SDS'           => ['SD'],
        'SMPN'          => ['SMP'],
        'SMPS'          => ['SMP'],
        'MIN'           => ['MI'],
        'MIS'           => ['MI'],
        'MTSN'          => ['MTS'],
        'MTSS'          => ['MTS'],
        'SLBN'          => ['SLB'],
        'SLBS'          => ['SLB'],
        'SDIT'          => ['SD', 'IT'],
        'SMPIT'         => ['SMP', 'IT'],
        'MUH'           => ['MUHAMMADIYAH'],
        'MUHAMMADIYYAH' => ['MUHAMMADIYAH'],
    ];

    /** Kata yang tidak membedakan satu sekolah dari yang lain. */
    private const NOISE = ['NEGERI', 'SWASTA', 'NO', 'NOMOR', 'NOMER', 'KEC', 'KECAMATAN'];

    private const ROMAN = [
        'I' => '1', 'II' => '2', 'III' => '3', 'IV' => '4', 'V' => '5',
        'VI' => '6', 'VII' => '7', 'VIII' => '8', 'IX' => '9', 'X' => '10',
    ];

    /**
     * Kunci pencocokan: token jenjang di depan (urutan asli), sisanya diurutkan.
     * "sdn 01 Cendono" → "SD 1 CENDONO".
     */
    public static function key(string $name): string
    {
        $key = self::keyFromTokens(self::tokens($name));

        // nama tanpa huruf/angka bermakna ("---", "NEGERI") tetap punya kunci sendiri
        return $key !== '' ? $key : mb_substr(mb_strtoupper(trim((string) preg_replace('/\s+/u', ' ', $name))), 0, 200);
    }

    /** @param list<string> $tokens hasil tokens(), boleh sudah dikurangi */
    public static function keyFromTokens(array $tokens): string
    {
        $stages = [];
        $rest   = [];

        foreach ($tokens as $token) {
            if (in_array($token, self::STAGE_TOKENS, true)) {
                $stages[] = $token;
            } else {
                $rest[] = $token;
            }
        }

        sort($rest, SORT_STRING);

        return mb_substr(implode(' ', [...$stages, ...$rest]), 0, 200);
    }

    /**
     * Token nama yang sudah dibakukan, dalam urutan aslinya.
     *
     * @return list<string>
     */
    public static function tokens(string $name): array
    {
        $text = mb_strtoupper($name, 'UTF-8');
        $text = (string) preg_replace("/['`\u{2018}\u{2019}]/u", '', $text);   // MA'ARIF → MAARIF
        // singkatan bertitik: S.D.N. → SDN, H.M. → HM ("SD N. 1" tetap tiga kata)
        $text = (string) preg_replace_callback(
            '/(?<![\p{L}\p{N}])(?:\p{L}\.){2,}/u',
            static fn (array $m): string => str_replace('.', '', $m[0]) . ' ',
            $text,
        );
        $text = (string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);        // tanda baca → spasi
        $text = (string) preg_replace('/(?<=\p{L})(?=\p{N})|(?<=\p{N})(?=\p{L})/u', ' ', $text); // SDN1 → SDN 1
        $text = ' ' . trim((string) preg_replace('/\s+/u', ' ', $text)) . ' ';

        foreach (self::PHRASES as $phrase => $short) {
            $text = str_replace(' ' . $phrase . ' ', ' ' . $short . ' ', $text);
        }

        $tokens     = [];
        $afterStage = false;

        foreach (preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $word) {
            foreach (self::GLUED[$word] ?? [$word] as $part) {
                if (in_array($part, self::NOISE, true)) {
                    continue;
                }

                // "SD N 1", "SMP S Kristen": huruf status tepat setelah jenjang
                if ($afterStage && ($part === 'N' || $part === 'S')) {
                    $afterStage = false;

                    continue;
                }

                if (ctype_digit($part)) {
                    $part = ltrim($part, '0');
                    $part = $part === '' ? '0' : $part;
                } elseif (isset(self::ROMAN[$part])) {
                    $part = self::ROMAN[$part];
                }

                $tokens[]   = $part;
                $afterStage = in_array($part, self::STAGE_TOKENS, true);
            }
        }

        return $tokens;
    }

    /**
     * Token kata tempat — nama kecamatan dan kab/kota sekolah, plus KAB/KABUPATEN/KOTA —
     * untuk withoutPlaces().
     *
     * @return list<string>
     */
    public static function placeTokens(string ...$places): array
    {
        $tokens = ['KAB', 'KABUPATEN', 'KOTA'];

        foreach ($places as $place) {
            array_push($tokens, ...self::tokens($place));
        }

        return array_values(array_unique($tokens));
    }

    /**
     * Token tanpa kata tempat: "SD N 3 Tumiyang" cocok dengan nama resmi
     * "SEKOLAH DASAR NEGERI 3 TUMIYANG KECAMATAN PEKUNCEN" setelah kecamatan
     * sekolah itu dibuang dari kedua sisi. Nama desa sengaja tidak termasuk:
     * "SD 1 CENDONO" tanpa CENDONO tidak lagi menunjuk satu sekolah.
     *
     * @param list<string> $tokens
     * @param list<string> $placeTokens hasil placeTokens()
     *
     * @return list<string>
     */
    public static function withoutPlaces(array $tokens, array $placeTokens): array
    {
        $drop = array_flip($placeTokens);

        return array_values(array_filter($tokens, static fn (string $token): bool => ! isset($drop[$token])));
    }

    /**
     * Masih ada kata nama (bukan jenjang, bukan angka)? "SD 1" saja terlalu umum
     * untuk ditautkan otomatis, "SD 3 TUMIYANG" cukup.
     *
     * @param list<string> $tokens
     */
    public static function hasIdentity(array $tokens): bool
    {
        foreach ($tokens as $token) {
            if (! ctype_digit($token) && ! in_array($token, self::STAGE_TOKENS, true)) {
                return true;
            }
        }

        return false;
    }
}
