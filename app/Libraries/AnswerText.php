<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;

/**
 * Jawaban siswa, kunci jawaban, dan isi catatan aktivitas dalam kalimat
 * biasa untuk guru: tabel panel, berkas Excel, dan laporan. Data aslinya
 * tetap tersimpan sebagai JSON; kelas ini hanya mengubah cara tampilnya.
 */
final class AnswerText
{
    /** Isi jawaban yang tampil tanpa nama kuncinya. */
    private const BARE = ['text', 'verdict', 'option_key', 'order', 'answer', 'value'];

    /** Nama Indonesia untuk kunci data; kunci lain tampil apa adanya. */
    private const NAMES = [
        'reason_text' => 'alasan', 'item_id' => 'benda nomor', 'hint_id' => 'petunjuk nomor',
        'level_id' => 'wilayah nomor', 'node_id' => 'tantangan nomor', 'duration_ms' => 'lama',
        'x' => 'kiri %', 'y' => 'atas %', 'w' => 'lebar %', 'from' => 'dari', 'to' => 'ke', 'first' => 'pertama kali',
        // Isi catatan aktivitas (game_event_logs.payload_json)
        'attempt_no' => 'pengerjaan ke', 'item_count' => 'jumlah soal', 'check_count' => 'cek ke',
        'check_no' => 'cek ke', 'try_no' => 'percobaan ke', 'exhausted' => 'kesempatan habis',
        'correct_count' => 'benar', 'total' => 'dari', 'correct' => 'benar', 'first_pass' => 'pada percobaan pertama',
        'change_count' => 'jawaban diubah', 'stars' => 'bintang', 'variant_group' => 'variasi',
        'first_pass_correct' => 'berhasil pada percobaan pertama', 'final_correct' => 'benar di akhir',
        'scorable_items' => 'soal dinilai', 'completion_reason' => 'alasan selesai', 'level_stars' => 'bintang wilayah',
        'total_stars' => 'jumlah bintang', 'screen' => 'layar', 'path' => 'alamat', 'params' => 'parameter',
        'overlay' => 'jendela', 'level' => 'wilayah', 'seq' => 'tantangan ke', 'action' => 'tindakan',
        't_ms' => 'ms sejak dibuka', 'percent' => 'persen', 'passage_id' => 'bacaan nomor', 'try_count' => 'jumlah percobaan',
        'orientation' => 'orientasi', 'rotate_gate' => 'layar putar tampil', 'scale' => 'skala', 'level_code' => 'wilayah', 'code' => 'kode', 'asset_key' => 'kode berkas',
        'via' => 'lewat', 'clicked_item_id' => 'benda yang diklik nomor', 'decoy' => 'jebakan',
        'rating' => 'nilai bintang', 'open_answers' => 'isian yang diisi', 'resumed' => 'dilanjutkan',
        'attempt_open' => 'tantangan masih terbuka', 'index' => 'slide ke', 'context' => 'bagian cerita',
        'character' => 'tokoh', 'page' => 'halaman ke', 'page_id' => 'halaman nomor', 'reason' => 'alasan',
        'idle_days' => 'hari tidak aktif', 'last_active_at' => 'terakhir aktif',
        'listened_ms' => 'lama didengar', 'elapsed_ms' => 'lama berjalan', 'play_index' => 'pemutaran ke',
    ];

    /** Nilai berkode yang punya kata Indonesia, per kunci. */
    private const VALUES = [
        'via' => [
            'register' => 'pendaftaran', 'login' => 'masuk', 'logout' => 'keluar', 'new_session' => 'sesi baru',
            'self' => 'oleh siswa sendiri', 'reset' => 'setelah sandi direset', 'user_exit' => 'tombol Keluar',
            'retention' => 'pembersihan otomatis', 'pagehide' => 'halaman ditutup', 'cli' => 'petugas teknis',
            'content_changed' => 'isi tantangan diperbarui',
        ],
    ];

    private const VERDICTS = ['benar' => 'Benar', 'salah' => 'Salah'];

    /**
     * Data JSON → teks biasa: {"text":"kopi"} → "kopi", {"order":["c","a"]} →
     * "c, a", {"verdict":"salah","reason_text":"…"} → "salah · alasan: …".
     * Isian kosong (null) dilewati; lama dalam milidetik ditulis dalam detik.
     */
    public static function fromData(mixed $data): string
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);

            if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_bool($decoded))) {
                $data = $decoded;
            }
        }

        if (is_bool($data)) {
            return $data ? 'ya' : 'tidak';
        }

        if (! is_array($data)) {
            return (string) $data;
        }

        if (array_is_list($data)) {
            return implode(', ', array_map(self::fromData(...), $data));
        }

        $parts = [];

        foreach ($data as $key => $item) {
            if ($item === null || $item === '' || $item === []) {
                continue;
            }

            $key = (string) $key;

            if (str_ends_with($key, '_ms') && is_numeric($item)) {
                $text = self::seconds((float) $item);
            } elseif (is_scalar($item) && isset(self::VALUES[$key][(string) $item])) {
                $text = self::VALUES[$key][(string) $item];
            } else {
                $text = self::fromData($item);
            }

            $parts[] = in_array($key, self::BARE, true) ? $text : (self::NAMES[$key] ?? $key) . ': ' . $text;
        }

        return implode(' · ', $parts);
    }

    /** Kunci jawaban dalam bentuk singkat yang dapat dibaca guru. */
    public static function key(ChallengeItem $item): string
    {
        $order = $item->answerKey('order');

        $text = match ((string) $item->interaction_type) {
            'verdict_card'    => self::verdict($item->verdict()),
            'fill_blank_bank' => trim((string) ($item->answerKey('text_id') ?? '')),
            'find_object'     => $item->isDecoy() ? 'benda jebakan' : 'benda yang dicari',
            'puzzle_arrange'  => is_array($order) && $order !== []
                ? 'gambar utuh (' . count($order) . ' keping)'
                : 'gambar utuh',
            default => self::fromData($item->answerKey()),
        };

        return $text === '' ? '—' : $text;
    }

    /**
     * Jawaban siswa untuk satu soal, menurut jenis soalnya. $answer boleh
     * berupa JSON tersimpan (first_answer_json / final_answer_json).
     */
    public static function response(ChallengeItem $item, mixed $answer): string
    {
        if (is_string($answer)) {
            $decoded = json_decode($answer, true);
            $answer  = json_last_error() === JSON_ERROR_NONE ? $decoded : $answer;
        }

        if ($answer === null || $answer === '' || $answer === []) {
            return '';
        }

        if (! is_array($answer)) {
            return (string) $answer;
        }

        switch ((string) $item->interaction_type) {
            case 'fill_blank_bank':
                return trim((string) ($answer['text'] ?? ''));

            case 'verdict_card':
                return self::verdict(isset($answer['verdict']) ? (string) $answer['verdict'] : null);

            case 'puzzle_arrange':
                [$correct, $total] = self::orderMatch($answer['order'] ?? null, $item->answerKey('order'));

                if ($total === 0) {
                    return '';
                }

                return $correct === $total
                    ? 'gambar utuh (' . $total . ' keping tepat)'
                    : $correct . ' dari ' . $total . ' keping di tempat yang benar';

            case 'find_object':
                $clicked = (int) ($answer['item_id'] ?? 0);

                if ($clicked <= 0) {
                    return '';
                }

                return $clicked === (int) $item->id ? 'benda yang dicari' : 'salah pilih benda';
        }

        return self::fromData($answer);
    }

    /** Lama dalam milidetik → "12,5 detik" / "3 menit 5 detik". */
    public static function seconds(float $ms): string
    {
        $seconds = $ms / 1000;

        if ($seconds < 60) {
            return number_format($seconds, $seconds < 10 ? 1 : 0, ',', '.') . ' detik';
        }

        $minutes = intdiv((int) round($seconds), 60);
        $rest    = (int) round($seconds) % 60;

        return $minutes . ' menit' . ($rest > 0 ? ' ' . $rest . ' detik' : '');
    }

    private static function verdict(?string $verdict): string
    {
        $verdict = strtolower(trim((string) $verdict));

        return self::VERDICTS[$verdict] ?? $verdict;
    }

    /**
     * Jumlah posisi yang sama antara urutan jawaban dan kunci.
     *
     * @return array{0: int, 1: int} [tepat, jumlah]
     */
    private static function orderMatch(mixed $given, mixed $key): array
    {
        $given = is_array($given) ? array_values(array_map('strval', $given)) : [];
        $key   = is_array($key) ? array_values(array_map('strval', $key)) : [];
        $total = $key !== [] ? count($key) : count($given);

        $correct = 0;

        foreach ($key as $slot => $expected) {
            if (($given[$slot] ?? null) === $expected) {
                $correct++;
            }
        }

        return [$correct, $total];
    }
}
