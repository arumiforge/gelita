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
 * kesulitan wilayah ada di DifficultyRules. Kelas ini murni (tanpa
 * framework) agar dipakai bersama oleh permainan, ContentVerifier, dan
 * docs/bank-soal/build-workbook.php.
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
     * Pemeriksaan satu bacaan terhadap aturan tingkat kesulitan wilayahnya.
     * Galat = bacaan tidak dapat dimainkan dengan benar; peringatan = tidak
     * sesuai aturan kelas 5 (jumlah rumpang, pengecoh, Bloom).
     *
     * @param list<array{key: string, prompt_id: string, prompt_en?: string, answer_id: string, answer_en?: string, bloom?: string|null}> $blanks
     * @param list<array{id: string, en?: string}>                                                                                          $distractors
     *
     * @return array{errors: list<string>, warnings: list<string>}
     */
    public static function problems(array $blanks, array $distractors, int $distractorCount, bool $useBank, ?string $difficulty): array
    {
        $errors   = [];
        $warnings = [];
        $rule     = DifficultyRules::reading($difficulty);
        $level    = DifficultyRules::normalize($difficulty);

        if ($blanks === []) {
            return ['errors' => ['Bacaan belum punya soal (rumpang) yang aktif.'], 'warnings' => []];
        }

        $answers = ['id' => [], 'en' => []];

        foreach ($blanks as $blank) {
            $key = (string) $blank['key'];

            if (self::blankCount((string) $blank['prompt_id']) !== 1) {
                $errors[] = "Soal {$key}: potongan bacaan bahasa Indonesia harus punya tepat satu ___.";
            }

            if (trim((string) ($blank['prompt_en'] ?? '')) !== '' && self::blankCount((string) $blank['prompt_en']) !== 1) {
                $errors[] = "Soal {$key}: potongan bacaan bahasa Inggris harus punya tepat satu ___.";
            }

            foreach (['id' => 'answer_id', 'en' => 'answer_en'] as $lang => $column) {
                $word = mb_strtolower(trim((string) ($blank[$column] ?? '')));

                if ($word === '') {
                    continue;
                }

                if (isset($answers[$lang][$word])) {
                    $errors[] = "Soal {$key}: jawaban '{$word}' sama dengan jawaban soal {$answers[$lang][$word]}, jadi bank kata menjadi ambigu.";
                }

                $answers[$lang][$word] = $key;
            }

            $bloom = strtoupper(trim((string) ($blank['bloom'] ?? '')));

            if ($bloom === '') {
                $warnings[] = "Soal {$key}: tingkat berpikir (Bloom) belum diisi.";
            } elseif (! DifficultyRules::bloomInRange($bloom, $difficulty)) {
                $warnings[] = "Soal {$key}: tingkat berpikir {$bloom} di luar anjuran tingkat {$level} (" . DifficultyRules::bloomRangeLabel($difficulty) . ').';
            }
        }

        foreach ($distractors as $distractor) {
            foreach (['id' => 'id', 'en' => 'en'] as $lang => $field) {
                $word = mb_strtolower(trim((string) ($distractor[$field] ?? '')));

                if ($word !== '' && isset($answers[$lang][$word])) {
                    $errors[] = "Kata pengecoh '{$word}' sama dengan jawaban soal {$answers[$lang][$word]}.";
                }
            }
        }

        if (count($distractors) < $distractorCount) {
            $errors[] = 'Kata pengecoh yang tersedia (' . count($distractors) . ') kurang dari jumlah yang harus tampil (' . $distractorCount . ').';
        }

        if (! $useBank) {
            $warnings[] = 'Bacaan Isi Rumpang kelas 5 memakai bank kata: nyalakan "pilihan kata" (use_word_bank = 1).';
        }

        [$min, $max] = $rule['blanks'];

        if (count($blanks) < $min || count($blanks) > $max) {
            $warnings[] = 'Bacaan punya ' . count($blanks) . " rumpang; aturan tingkat {$level} adalah " . ($min === $max ? $min : "{$min}–{$max}") . ' rumpang.';
        }

        [$dMin, $dMax] = $rule['distractors'];

        if ($distractorCount < $dMin || $distractorCount > $dMax) {
            $warnings[] = "Jumlah kata pengecoh yang tampil {$distractorCount}; aturan tingkat {$level} adalah " . ($dMin === $dMax ? $dMin : "{$dMin}–{$dMax}") . '.';
        }

        return ['errors' => $errors, 'warnings' => $warnings];
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
