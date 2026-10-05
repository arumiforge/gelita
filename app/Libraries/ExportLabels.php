<?php

namespace App\Libraries;

/**
 * Isi berkas Excel unduhan data dalam bahasa Indonesia sehari-hari: nama
 * lembar, judul kolom, dan nilai berkode (status, jenis soal, …).
 *
 * Kode lembar (`Participants`, …) dan kode kolom (`attempt_id`, …) tetap
 * kode internal: keduanya dipakai formulir, scope_json, riwayat aktivitas,
 * dan tes. Terjemahan terjadi saat berkas ditulis (ExportService::writeXlsx).
 * Judul kolom ditetapkan per lembar karena satu kode bisa berbeda arti:
 * `duration_ms` di lembar sesi dihitung dalam menit, di lembar jawaban dalam
 * detik. Satuan ditulis di judul kolom, jadi tidak perlu lembar keterangan.
 *
 * Cara menulis nilai:
 * - `int`, `number`: angka; `seconds`/`minutes`: milidetik → detik/menit
 *   (1 desimal); `ratio`: 0–1 → persen; nilai yang sudah 0–100 tetap.
 * - `yesno`: 1/0 → ya/tidak; `date`: sel tanggal Excel (dd-mm-yyyy jj:mm:dd).
 * - `label:<kelompok>`: kode → admin_label() (daftar kode → dipisah koma);
 *   `class`: kelas → "Kelas 5 SD/MI"; `engine`: jenis tantangan;
 *   `details`: isi JSON catatan aktivitas → kalimat (AnswerText).
 */
final class ExportLabels
{
    /** Kolom yang sama di banyak lembar. */
    private const COMMON = [
        'participant_code' => ['Kode siswa', 'text'],
        'session_code'     => ['Kode sesi', 'text'],
        'attempt_id'       => ['No. pengerjaan', 'int'],
        'level'            => ['Wilayah', 'text'],
        'node_sequence'    => ['Tantangan ke-', 'int'],
        'node_title'       => ['Nama tantangan', 'text', 28],
        'item_key'         => ['Kode soal', 'text'],
    ];

    /**
     * Judul, cara menulis, dan (opsional) lebar kolom per lembar.
     *
     * @var array<string, array<string, array{0: string, 1: string, 2?: int}>>
     */
    private const SHEETS = [
        'Participants' => [
            'display_name'             => ['Nama siswa', 'text', 24],
            'username'                 => ['Nama pengguna', 'text'],
            'school_name'              => ['Nama sekolah', 'text', 30],
            'school_npsn'              => ['NPSN sekolah', 'text'],
            'school_ref'               => ['Kode sekolah (samaran)', 'text'],
            'age'                      => ['Umur (tahun)', 'int'],
            'class_level'              => ['Kelas', 'class', 16],
            'gender'                   => ['Jenis kelamin', 'label:gender'],
            'country'                  => ['Negara', 'text'],
            'province'                 => ['Provinsi', 'text', 18],
            'district'                 => ['Kabupaten/kota', 'text', 22],
            'registered_at'            => ['Tanggal daftar', 'date'],
            'assented_at'              => ['Setuju ikut bermain', 'date'],
            'pw_first_submit_criteria' => ['Syarat sandi yang langsung dipenuhi (dari 5)', 'int'],
            'pw_weak_submit_count'     => ['Mencoba sandi terlalu lemah (kali)', 'int'],
        ],
        'Sessions' => [
            'study'            => ['Penelitian', 'text', 24],
            'release_code'     => ['Versi permainan', 'text'],
            'locale'           => ['Bahasa', 'label:locale'],
            'status'           => ['Status sesi', 'label:status'],
            'started_at'       => ['Mulai', 'date'],
            'last_active_at'   => ['Terakhir aktif', 'date'],
            'ended_at'         => ['Selesai', 'date'],
            'duration_ms'      => ['Lama bermain (menit)', 'minutes'],
            'device_type'      => ['Perangkat', 'label:device'],
            'os_name'          => ['Sistem perangkat', 'text'],
            'browser_name'     => ['Peramban', 'text'],
            'screen_size'      => ['Ukuran layar', 'text'],
            'is_touch'         => ['Layar sentuh', 'yesno'],
            'shards'           => ['Serpihan cahaya (tantangan tuntas)', 'int'],
            'completed_levels' => ['Wilayah tuntas', 'int'],
            'total_stars'      => ['Jumlah bintang', 'int'],
        ],
        'Levels' => [
            'level_sequence'  => ['Wilayah ke-', 'int'],
            'completed_nodes' => ['Tantangan selesai', 'int'],
            'total_nodes'     => ['Jumlah tantangan', 'int'],
            'stars'           => ['Bintang', 'int'],
            'duration_ms'     => ['Lama mengerjakan (menit)', 'minutes'],
        ],
        'Challenge Summary' => [
            'engine_type'         => ['Jenis tantangan', 'engine', 18],
            'item_types'          => ['Jenis soal', 'label:interaction', 28],
            'attempt_no'          => ['Pengerjaan ke-', 'int'],
            'variant_group'       => ['Variasi', 'text'],
            'status'              => ['Status', 'label:status'],
            'completion_reason'   => ['Alasan selesai', 'label:completion', 20],
            'scorable_items'      => ['Jumlah soal dinilai', 'int'],
            'first_pass_correct'  => ['Soal Berhasil pada Percobaan Pertama', 'int'],
            'final_correct'       => ['Soal benar di akhir', 'int'],
            'check_count'         => ['Menekan Cek jawabanku (kali)', 'int'],
            'max_tries'           => ['Batas percobaan per soal', 'int'],
            'hint_count'          => ['Membuka petunjuk (kali)', 'int'],
            'answer_change_count' => ['Mengubah jawaban (kali)', 'int'],
            'audio_use_count'     => ['Memutar suara (kali)', 'int'],
            'stars'               => ['Bintang', 'int'],
            'started_at'          => ['Mulai', 'date'],
            'completed_at'        => ['Selesai', 'date'],
            'duration_ms'         => ['Lama mengerjakan (detik)', 'seconds'],
        ],
        'Item Responses' => [
            'variant_group'      => ['Variasi', 'text'],
            'prompt'             => ['Soal', 'text', 50],
            'interaction_type'   => ['Jenis soal', 'label:interaction', 26],
            'indicator'          => ['Indikator', 'text', 22],
            'bloom_level'        => ['Tingkat berpikir (Bloom)', 'label:bloom', 20],
            'display_order'      => ['Urutan tampil', 'int'],
            'status'             => ['Status jawaban', 'label:status'],
            'first_answer'       => ['Jawaban pertama', 'text', 30],
            'final_answer'       => ['Jawaban akhir', 'text', 30],
            'answer_key'         => ['Kunci jawaban', 'text', 30],
            'first_pass_correct' => ['Berhasil pada Percobaan Pertama', 'yesno'],
            'is_correct'         => ['Benar di akhir', 'yesno'],
            'try_count'          => ['Jumlah percobaan', 'int'],
            'solved_on_try'      => ['Benar pada percobaan ke-', 'int'],
            'change_count'       => ['Mengubah jawaban (kali)', 'int'],
            'hint_used'          => ['Memakai petunjuk', 'yesno'],
            'wrong_click_count'  => ['Salah pilih benda (kali)', 'int'],
            'answered_at'        => ['Waktu menjawab', 'date'],
            'duration_ms'        => ['Lama menjawab (detik)', 'seconds'],
        ],
        'Raw Events' => [
            'event_type'         => ['Aktivitas', 'label:event', 26],
            'sequence_no'        => ['Urutan dalam sesi', 'int'],
            'occurred_at'        => ['Waktu kejadian', 'date'],
            'server_received_at' => ['Waktu diterima server', 'date'],
            'payload'            => ['Rincian', 'details', 50],
        ],
        'Audio Usage' => [
            'asset_key'         => ['Kode rekaman', 'text', 28],
            'context_code'      => ['Bagian permainan', 'audio_context', 22],
            'character_code'    => ['Tokoh', 'character'],
            'audio_locale'      => ['Bahasa rekaman', 'label:locale'],
            'asset_duration_ms' => ['Panjang rekaman (detik)', 'seconds'],
            'action'            => ['Tindakan', 'label:audioAction', 22],
            'play_index'        => ['Pemutaran ke-', 'int'],
            'listened_ms'       => ['Lama didengar (detik)', 'seconds'],
            'completed'         => ['Didengar sampai habis', 'yesno'],
            'occurred_at'       => ['Waktu', 'date'],
        ],
        'Indicators' => [
            'indicator'        => ['Indikator', 'text', 24],
            'evidence_count'   => ['Jumlah jawaban', 'int'],
            'correct_count'    => ['Berhasil pada Percobaan Pertama', 'int'],
        ],
        'Demographic Summary' => [
            'dimension'          => ['Dikelompokkan menurut', 'label:exportDimension'],
            'group'              => ['Kelompok', 'group', 30],
            'participants'       => ['Jumlah siswa', 'int'],
            'sessions'           => ['Jumlah sesi', 'int'],
            'completed_sessions' => ['Sesi selesai', 'int'],
        ],
        'Feedback' => [
            'rating'       => ['Bintang (1–5)', 'int'],
            'liked_most'   => ['Yang paling disukai', 'text', 40],
            'hardest_part' => ['Bagian tersulit', 'text', 40],
            'new_learning' => ['Hal baru yang dipelajari', 'text', 40],
            'suggestion'   => ['Saran', 'text', 40],
            'submitted_at' => ['Waktu dikirim', 'date'],
        ],
    ];

    /** Lebar kolom tanggal agar "05-10-2026 08:30:15" tidak menjadi ####. */
    private const DATE_WIDTH = 20;

    /** Nama lembar di berkas Excel. */
    public static function sheetTitle(string $sheet): string
    {
        return admin_label('exportSheet', $sheet);
    }

    /**
     * @param list<string> $columns
     *
     * @return list<string>
     */
    public static function headers(string $sheet, array $columns): array
    {
        return array_map(static fn (string $column): string => self::spec($sheet, $column)[0], $columns);
    }

    /**
     * Lebar kolom dalam satuan karakter Excel: isian khusus, tanggal, atau
     * cukup untuk judulnya.
     *
     * @param list<string> $columns
     *
     * @return list<int>
     */
    public static function widths(string $sheet, array $columns): array
    {
        return array_map(static function (string $column) use ($sheet): int {
            $spec = self::spec($sheet, $column);

            if (isset($spec[2])) {
                return $spec[2];
            }

            $title = min(40, mb_strlen($spec[0]) + 3);

            return $spec[1] === 'date' ? max(self::DATE_WIDTH, $title) : max(10, $title);
        }, $columns);
    }

    /** Apakah kolom ini punya judul Indonesia. Dipakai tes. */
    public static function has(string $sheet, string $column): bool
    {
        return isset(self::SHEETS[$sheet][$column]) || isset(self::COMMON[$column]);
    }

    /**
     * Nilai siap tulis. $row = seluruh baris, untuk kolom yang artinya
     * bergantung pada kolom lain (kelompok di Ringkasan kelompok).
     *
     * @param array<string, mixed> $row
     */
    public static function value(string $sheet, string $column, mixed $value, array $row = []): mixed
    {
        if ($value === null || $value === '' || $value === []) {
            return null;
        }

        $format = self::spec($sheet, $column)[1];

        if (str_starts_with($format, 'label:')) {
            return self::label(substr($format, 6), $value);
        }

        return match ($format) {
            'int'           => (int) $value,
            'seconds'       => round((float) $value / 1000, 1),
            'minutes'       => round((float) $value / 60000, 1),
            'yesno'         => (bool) (int) $value ? 'ya' : 'tidak',
            'date'          => self::date($value),
            'class'         => class_label((string) $value, 'id'),
            'engine'        => self::engine((string) $value),
            'details'       => AnswerText::fromData($value),
            'audio_context' => NarrationCatalog::CONTEXT_LABELS[(string) $value] ?? (string) $value,
            'character'     => NarrationCatalog::CHARACTER_LABELS[(string) $value] ?? (string) $value,
            'group'         => self::group((string) ($row['dimension'] ?? ''), (string) $value),
            default         => is_scalar($value) ? $value : AnswerText::fromData($value),
        };
    }

    /** @return array{0: string, 1: string, 2?: int} */
    private static function spec(string $sheet, string $column): array
    {
        return self::SHEETS[$sheet][$column] ?? self::COMMON[$column] ?? [$column, 'text'];
    }

    /** Kode (atau daftar kode) → label panel; daftar disatukan dengan koma. */
    private static function label(string $group, mixed $value): string
    {
        $codes = is_array($value) ? array_values(array_unique(array_map('strval', $value))) : [(string) $value];

        return implode(', ', array_map(static fn (string $code): string => admin_label($group, $code), $codes));
    }

    private static function engine(string $code): string
    {
        $text = lang('Game.engine_' . $code, [], 'id');

        return $text === 'Game.engine_' . $code ? $code : $text;
    }

    /** Waktu database (waktu setempat) → objek tanggal untuk sel tanggal Excel. */
    private static function date(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Exception) {
            return (string) $value;
        }
    }

    /** Isi kolom Kelompok mengikuti pengelompokannya. */
    private static function group(string $dimension, string $value): string
    {
        if ($value === '(kosong)') {
            return '(tidak diisi)';
        }

        return match ($dimension) {
            'gender'      => admin_label('gender', $value),
            'class_level' => class_label($value, 'id'),
            default       => $value,
        };
    }
}
