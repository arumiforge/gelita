<?php

namespace App\Libraries;

/**
 * Isi Rumpang sebagai satu bacaan utuh (node `rumpang` dengan
 * config_json.as_reading = true).
 *
 * Setiap soal (`fill_blank_bank`) adalah satu potongan bacaan dengan tepat
 * satu tempat kosong `___`; potongan-potongan disambung menurut urutannya
 * menjadi paragraf. Baris kosong di dalam potongan memulai paragraf baru
 * (importer memangkas tepi sel, jadi jeda paragraf ditulis di tengah
 * potongan, setelah rumpangnya).
 *
 * Aturan jumlah rumpang, kata pengecoh, dan tingkat Bloom per tingkat
 * kesulitan wilayah ada di DifficultyRules dan diperiksa BankRules. Kelas
 * ini murni (tanpa framework) agar dipakai bersama oleh permainan dan
 * BankRules (skrip penyusun workbook berjalan tanpa CodeIgniter).
 */
final class ClozeReading
{
    /** Tempat kosong: tiga garis bawah atau lebih. */
    public const BLANK = '/_{3,}/';

    /**
     * Potongan bacaan → paragraf berisi teks dan rumpang, urut tampil.
     *
     * @param list<array{id: int|string, prompt: string}> $chunks
     *
     * @return list<list<array{type: 'text', text: string}|array{type: 'blank', id: int|string, index: int}>>
     */
    public static function paragraphs(array $chunks): array
    {
        $paragraphs = [[]];

        foreach (array_values($chunks) as $n => $chunk) {
            $parts = preg_split(self::BLANK, (string) $chunk['prompt'], 2);

            self::appendText($paragraphs, $parts[0] ?? '', $n > 0);

            if (count($parts) === 2) {
                $paragraphs[array_key_last($paragraphs)][] = ['type' => 'blank', 'id' => $chunk['id'], 'index' => $n + 1];
                self::appendText($paragraphs, $parts[1], false);
            }
        }

        $out = [];

        foreach ($paragraphs as $tokens) {
            $tokens = self::trimEdges($tokens);

            if ($tokens !== []) {
                $out[] = $tokens;
            }
        }

        return $out;
    }

    public static function blankCount(string $text): int
    {
        return (int) preg_match_all(self::BLANK, $text);
    }

    /**
     * Menambah teks ke paragraf terakhir; baris kosong di dalam teks memulai
     * paragraf baru. Potongan berikutnya dipisah satu spasi dari sebelumnya.
     *
     * @param list<list<array<string, mixed>>> $paragraphs
     */
    private static function appendText(array &$paragraphs, string $text, bool $newChunk): void
    {
        $segments = preg_split('/\R[ \t]*\R\s*/u', $text) ?: [$text];

        foreach ($segments as $i => $segment) {
            if ($i > 0) {
                $paragraphs[] = [];
            }

            $last = array_key_last($paragraphs);

            if ($i === 0 && $newChunk && $paragraphs[$last] !== []) {
                $segment = ' ' . ltrim($segment);
            }

            if ($segment !== '') {
                $paragraphs[$last][] = ['type' => 'text', 'text' => $segment];
            }
        }
    }

    /**
     * Spasi di awal dan akhir paragraf dibuang.
     *
     * @param list<array<string, mixed>> $tokens
     *
     * @return list<array<string, mixed>>
     */
    private static function trimEdges(array $tokens): array
    {
        if ($tokens !== [] && $tokens[0]['type'] === 'text') {
            $tokens[0]['text'] = ltrim((string) $tokens[0]['text']);
        }

        $last = array_key_last($tokens);

        if ($last !== null && $tokens[$last]['type'] === 'text') {
            $tokens[$last]['text'] = rtrim((string) $tokens[$last]['text']);
        }

        return array_values(array_filter(
            $tokens,
            static fn (array $token): bool => $token['type'] !== 'text' || $token['text'] !== '',
        ));
    }
}
