<?php

namespace App\Libraries;

/**
 * Columns of the field sheets L1–L8 (research revision 10, part 3:
 * docs/revisi-10-analitik/29_IMPLEMENTASI_DATA_PENELITIAN.md §10.1, §10.7, K11).
 *
 * One source for the typed template, the import, the `ketua` download, and
 * the static site (`php spark gelita:lembar:templat --kolom=kolom.json`).
 * Every sheet follows the table tagged `<!-- lembar: {marker} -->` in
 * docs/revisi-10-analitik/02_LEMBAR_LAPANGAN.md: `paper` lists its headings
 * exactly as 02 writes them (for a `fields` sheet: the first cell of each
 * row), each with the column codes it is typed into. FieldSheetSpecTest
 * compares `paper` with 02, so 02, template, import, and site cannot drift.
 * Any change to a tagged table raises VERSION; the version is locked at G4
 * together with the data dictionary (K17).
 *
 * A heading with no codes is printed but never imported (L1 token, L5 quote
 * of the child's words). `extra` codes are typed but have no column in the
 * tagged table (the L5 sheet head). L7 never reaches the server (§10.6).
 */
final class FieldSheetSpec
{
    public const VERSION = 'lembar-1.0';

    public const DOC = 'docs/revisi-10-analitik/02_LEMBAR_LAPANGAN.md';

    /** Lists of allowed codes, from 02. */
    public const CODES = [
        'deviation' => ['DEV-NET', 'DEV-LIS', 'DEV-WKT', 'DEV-PRT', 'DEV-TIM', 'DEV-LAIN'],
        'helper'    => ['TMN', 'GRU', 'PNL', 'LN'],
        'help'      => ['ISI', 'STR', 'BHS', 'TKN', 'SMG', 'GG-KLR', 'GG-LAIN', 'GG-OBR', 'GG-TEK'],
        'behavior'  => ['ON', 'ONK', 'OFF', 'TBK', '?'],
        'emotion'   => ['FOK', 'BNG', 'FRS', 'BSN', 'SNG', 'NTR', '?'],
        'booklet'   => ['B1', 'B2', 'B3', 'B4'],
        'form'      => ['A', 'B'],
        'noise'     => ['1', '2', '3'],
        'group'     => ['rendah', 'sedang', 'tinggi'],
        'yesno'     => ['Ya', 'Tidak'],
    ];

    /**
     * Column types: session (field session code), text, free (free text,
     * checked for names), date (Y-m-d or d/m/Y), time (HH:MM:SS WIB), int,
     * code (one of CODES[list]), yesno, participant (participant code of the
     * class group), seat (a seat of this session's L2), seats (a seat, a
     * range "05–08", or "semua"), item (item key), try (1–3), manual (L5
     * manual error code).
     */
    private const SHEETS = [
        'l1_kondisi' => [
            'marker' => 'L1-kondisi', 'title' => 'L1 Kondisi sesi', 'imported' => true, 'layout' => 'fields', 'key' => [],
            'paper'  => [
                'Kode sesi lapangan'                     => ['field_session_code'],
                'Isi pertemuan'                          => ['meeting_content'],
                'Tanggal P5 (hanya susulan skala sikap)' => ['p5_date'],
                'Token'                                  => [],
                'Tanggal; jam mulai dan selesai'         => ['session_date', 'start_time', 'end_time'],
                'Peneliti'                               => ['researchers'],
                'Siswa hadir / berizin / bermain'        => ['present_count', 'consented_count', 'playing_count'],
                'Ruang dan perangkat'                    => ['room_devices'],
                'Internet'                               => ['internet'],
                'Headset'                                => ['headset_count', 'headset_of'],
                'Kebisingan'                             => ['noise_level'],
                'Kehadiran guru'                         => ['teacher_presence'],
                'Menit tersedia dan dipakai'             => ['minutes_available', 'minutes_used'],
                'Jeda'                                   => ['breaks'],
            ],
            'columns' => [
                'field_session_code' => ['Kode sesi lapangan', 'session'],
                'meeting_content'    => ['Isi pertemuan', 'text'],
                'p5_date'            => ['Tanggal P5 (hanya susulan skala sikap)', 'date'],
                'session_date'       => ['Tanggal', 'date'],
                'start_time'         => ['Jam mulai (WIB)', 'time'],
                'end_time'           => ['Jam selesai (WIB)', 'time'],
                'researchers'        => ['Peneliti', 'text'],
                'present_count'      => ['Siswa hadir', 'int'],
                'consented_count'    => ['Siswa berizin', 'int'],
                'playing_count'      => ['Siswa bermain', 'int'],
                'room_devices'       => ['Ruang dan perangkat', 'text'],
                'internet'           => ['Internet', 'text'],
                'headset_count'      => ['Headset dipakai', 'int'],
                'headset_of'         => ['Headset: dari siswa bermain', 'int'],
                'noise_level'        => ['Kebisingan', 'code', 'noise'],
                'teacher_presence'   => ['Kehadiran guru', 'text'],
                'minutes_available'  => ['Menit tersedia', 'int'],
                'minutes_used'       => ['Menit dipakai', 'int'],
                'breaks'             => ['Jeda', 'free'],
            ],
        ],
        'l1_penyimpangan' => [
            'marker' => 'L1-penyimpangan', 'title' => 'L1 Log penyimpangan', 'imported' => true, 'layout' => 'columns', 'key' => ['start_time'],
            'paper'  => [
                'Jam mulai (WIB)'   => ['start_time'],
                'Jam selesai (WIB)' => ['end_time'],
                'Kode'              => ['deviation_code'],
                'Kejadian'          => ['description'],
                'Siswa terdampak'   => ['affected_seats'],
                'Tindakan'          => ['action'],
            ],
            'columns' => [
                'start_time'     => ['Jam mulai (WIB)', 'time'],
                'end_time'       => ['Jam selesai (WIB)', 'time'],
                'deviation_code' => ['Kode', 'code', 'deviation'],
                'description'    => ['Kejadian', 'free'],
                'affected_seats' => ['Siswa terdampak', 'seats'],
                'action'         => ['Tindakan', 'free'],
            ],
        ],
        'l2_kursi' => [
            'marker' => 'L2-kursi', 'title' => 'L2 Tempat duduk', 'imported' => true, 'layout' => 'columns', 'key' => ['participant_code'],
            'paper'  => [
                'Kursi'            => ['seat_no'],
                'Baris'            => ['row_no'],
                'Kursi di baris'   => ['seat_in_row'],
                'Perangkat'        => ['device_no'],
                'Kode peserta'     => ['participant_code'],
                'Buklet'           => ['booklet'],
                'Bentuk tes awal'  => ['pre_form'],
                'Jam masuk (WIB)'  => ['entry_time'],
                'Pakai bareng'     => ['shared_device', 'shared_with'],
                'Pindah perangkat' => ['switch_time', 'switch_device'],
                'Catatan'          => ['note'],
            ],
            'columns' => [
                'seat_no'          => ['Kursi', 'int'],
                'row_no'           => ['Baris', 'int'],
                'seat_in_row'      => ['Kursi di baris', 'int'],
                'device_no'        => ['Perangkat', 'text'],
                'participant_code' => ['Kode peserta', 'participant'],
                'booklet'          => ['Buklet', 'code', 'booklet'],
                'pre_form'         => ['Bentuk tes awal', 'code', 'form'],
                'entry_time'       => ['Jam masuk (WIB)', 'time'],
                'shared_device'    => ['Pakai bareng', 'yesno'],
                'shared_with'      => ['Pakai bareng dengan (kode peserta)', 'participant'],
                'switch_time'      => ['Pindah perangkat: jam (WIB)', 'time'],
                'switch_device'    => ['Pindah perangkat: ke perangkat', 'text'],
                'note'             => ['Catatan', 'free'],
            ],
        ],
        'l3_bantuan' => [
            'marker' => 'L3-bantuan', 'title' => 'L3 Bantuan dan gangguan', 'imported' => true, 'layout' => 'columns', 'key' => ['start_time', 'seats'],
            'paper'  => [
                'Jam mulai (WIB)'   => ['start_time'],
                'Jam selesai (WIB)' => ['end_time'],
                'Kursi'             => ['seats'],
                'Kode peserta'      => ['participant_code'],
                'Di layar'          => ['screen'],
                'Pemberi'           => ['helper'],
                'Jenis'             => ['help_type'],
                'Diminta siswa?'    => ['asked'],
                'Uraian singkat'    => ['description'],
            ],
            'columns' => [
                'start_time'       => ['Jam mulai (WIB)', 'time'],
                'end_time'         => ['Jam selesai (WIB)', 'time'],
                'seats'            => ['Kursi', 'seats'],
                'participant_code' => ['Kode peserta', 'participant'],
                'screen'           => ['Di layar', 'text'],
                'helper'           => ['Pemberi', 'code', 'helper'],
                'help_type'        => ['Jenis', 'code', 'help'],
                'asked'            => ['Diminta siswa?', 'yesno'],
                'description'      => ['Uraian singkat', 'free'],
            ],
        ],
        'l4_bromp' => [
            'marker' => 'L4-bromp', 'title' => 'L4 Observasi', 'imported' => true, 'layout' => 'columns', 'key' => ['round_no', 'seat_no'],
            'paper'  => [
                'Putaran'      => ['round_no'],
                'Jam (WIB)'    => ['obs_time'],
                'Kursi'        => ['seat_no'],
                'Kode peserta' => ['participant_code'],
                'Di layar'     => ['screen'],
                'Perilaku'     => ['behavior'],
                'Emosi'        => ['emotion'],
                'Catatan'      => ['note'],
            ],
            'columns' => [
                'round_no'         => ['Putaran', 'int'],
                'obs_time'         => ['Jam (WIB)', 'time'],
                'seat_no'          => ['Kursi', 'seat'],
                'participant_code' => ['Kode peserta', 'participant'],
                'screen'           => ['Di layar', 'text'],
                'behavior'         => ['Perilaku', 'code', 'behavior'],
                'emotion'          => ['Emosi', 'code', 'emotion'],
                'note'             => ['Catatan', 'free'],
            ],
        ],
        'l5_wawancara' => [
            'marker' => 'L5-wawancara', 'title' => 'L5 Wawancara', 'imported' => true, 'layout' => 'columns', 'key' => ['participant_code', 'item_key', 'try_no'],
            'paper'  => [
                'Kode peserta'          => ['participant_code'],
                'Butir'                 => ['item_key'],
                'Percobaan'             => ['try_no'],
                'Jawaban pertama siswa' => ['first_answer'],
                'Kode dari game'        => ['game_code'],
                'Kutipan ucapan siswa'  => [],   // stays in the encrypted transcript folder (26 → A10)
                'Pengode 1'             => ['coder_1'],
                'Pengode 2'             => ['coder_2'],
                'Sepakat'               => ['agree'],
                'Kode manual'           => ['manual_code'],
            ],
            // The sheet head of 02 (one per student), typed on every row
            'extra'   => ['pretest_group', 'interviewer', 'start_time', 'end_time', 'recorded'],
            'columns' => [
                'participant_code' => ['Kode peserta', 'participant'],
                'pretest_group'    => ['Kelompok tes awal', 'code', 'group'],
                'interviewer'      => ['Pewawancara', 'text'],
                'start_time'       => ['Jam mulai wawancara (WIB)', 'time'],
                'end_time'         => ['Jam selesai wawancara (WIB)', 'time'],
                'recorded'         => ['Rekaman', 'yesno'],
                'item_key'         => ['Butir', 'item'],
                'try_no'           => ['Percobaan', 'try'],
                'first_answer'     => ['Jawaban pertama siswa', 'text'],
                'game_code'        => ['Kode dari game', 'text'],
                'coder_1'          => ['Pengode 1', 'text'],
                'coder_2'          => ['Pengode 2', 'text'],
                'agree'            => ['Sepakat', 'yesno'],
                'manual_code'      => ['Kode manual', 'manual'],
            ],
        ],
        'l6_sesi' => [
            'marker' => 'L6-sesi', 'title' => 'L6 Catatan sesi tes', 'imported' => true, 'layout' => 'columns', 'key' => ['field_session_code'],
            'paper'  => [
                'Kode sesi lapangan'                        => ['field_session_code'],
                'Tes'                                       => ['test'],
                'Jam mulai Bagian 1'                        => ['part1_start'],
                'Jam mulai Bagian 2–3'                      => ['part23_start'],
                'Jam siswa terakhir selesai'                => ['last_finish'],
                'Perangkat cadangan dipakai'                => ['spare_devices'],
                'Pertanyaan yang sering diajukan siswa (P3)' => ['frequent_questions'],
            ],
            'columns' => [
                'field_session_code' => ['Kode sesi lapangan', 'session'],
                'test'               => ['Tes', 'text'],
                'part1_start'        => ['Jam mulai Bagian 1', 'time'],
                'part23_start'       => ['Jam mulai Bagian 2–3', 'time'],
                'last_finish'        => ['Jam siswa terakhir selesai', 'time'],
                'spare_devices'      => ['Perangkat cadangan dipakai', 'int'],
                'frequent_questions' => ['Pertanyaan yang sering diajukan siswa (P3)', 'free'],
            ],
        ],
        // Printed only (§10.2): never imported
        'l4_pelatihan' => [
            'marker' => 'L4-pelatihan', 'title' => 'L4 Pelatihan pengamat', 'imported' => false, 'layout' => 'columns',
            'paper'  => ['Pengamat (inisial)' => [], 'Tanggal pelatihan' => [], 'Sesi uji coba' => [], 'Jumlah pengamatan berpasangan' => [], 'κ perilaku' => [], 'κ emosi' => [], 'Lulus' => [], 'Pemeriksa' => []],
        ],
        'l6_tes' => [
            'marker' => 'L6-tes', 'title' => 'L6 Status tes per siswa', 'imported' => false, 'layout' => 'columns',
            'paper'  => ['Kode peserta' => [], 'Tes awal (tanggal, bentuk, status)' => [], 'Angket' => [], 'Tes akhir (tanggal, bentuk, status)' => [], 'Izin butir risiko' => [], 'Butir risiko' => [], 'Catatan' => []],
        ],
        'l7_izin' => [
            'marker' => 'L7-izin', 'title' => 'L7 Izin orang tua', 'imported' => false, 'layout' => 'columns', 'offline' => true,
            'paper'  => ['No' => [], 'Nama siswa' => [], 'Rombel' => [], 'Izin orang tua (tanggal)' => [], 'Izin rekaman suara' => [], 'Izin butir risiko daring' => [], 'Persetujuan siswa' => [], 'Kode peserta' => [], 'Status' => []],
        ],
        'l8_profil_sekolah' => [
            'marker' => 'L8-profil-sekolah', 'title' => 'L8 Profil sekolah', 'imported' => false, 'layout' => 'fields',
            'paper'  => [
                'Letak' => [], 'Status sekolah' => [], 'Kelas 5' => [], 'Bahasa sehari-hari di kelas' => [], 'Perangkat sekolah' => [],
                'Perangkat yang cukup untuk penelitian' => [], 'Internet' => [], 'Listrik' => [], 'Pengalaman asesmen di layar' => [],
                'Pelajaran digital' => [], 'Catatan' => [],
            ],
        ],
    ];

    /** @return list<string> every sheet code (imported sheets first) */
    public static function sheets(): array
    {
        return array_keys(self::SHEETS);
    }

    /** @return list<string> the sheets typed and imported (the download sheets l1_… – l6_…) */
    public static function imported(): array
    {
        return array_keys(array_filter(self::SHEETS, static fn (array $s): bool => $s['imported']));
    }

    public static function has(string $sheet): bool
    {
        return isset(self::SHEETS[$sheet]);
    }

    /** @return array<string, mixed> one sheet's spec */
    public static function sheet(string $sheet): array
    {
        return self::SHEETS[$sheet] ?? throw new \InvalidArgumentException("Unknown field sheet '{$sheet}'.");
    }

    public static function title(string $sheet): string
    {
        return (string) self::sheet($sheet)['title'];
    }

    /** @return array<string, list<string>> heading as in 02 → column codes */
    public static function paper(string $sheet): array
    {
        return self::sheet($sheet)['paper'];
    }

    /**
     * Typed columns of an imported sheet, in template order.
     *
     * @return array<string, array{label: string, type: string, list: ?string}>
     */
    public static function columns(string $sheet): array
    {
        $out = [];

        foreach (self::sheet($sheet)['columns'] ?? [] as $code => $spec) {
            $out[$code] = ['label' => $spec[0], 'type' => $spec[1], 'list' => $spec[2] ?? null];
        }

        return $out;
    }

    /** @return list<string> row key of a sheet (matching of the second typing, §10.5) */
    public static function key(string $sheet): array
    {
        return self::sheet($sheet)['key'] ?? [];
    }

    /**
     * Column code for a typed heading: the code itself, its label, or a 02
     * heading of exactly one code (case and spaces do not matter). Null for a
     * heading that is not imported (token, quote) or unknown.
     */
    public static function codeFor(string $sheet, string $heading): ?string
    {
        $want    = self::normalize($heading);
        $columns = self::columns($sheet);

        foreach ($columns as $code => $spec) {
            if ($want === self::normalize($code) || $want === self::normalize($spec['label'])) {
                return $code;
            }
        }

        foreach (self::paper($sheet) as $paper => $codes) {
            if (count($codes) === 1 && $want === self::normalize($paper)) {
                return $codes[0];
            }
        }

        return null;
    }

    /** True for a 02 heading that is printed but never stored (L1 token, L5 quote). */
    public static function isDropped(string $sheet, string $heading): bool
    {
        foreach (self::paper($sheet) as $paper => $codes) {
            if ($codes === [] && self::normalize($paper) === self::normalize($heading)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The whole spec as data, for `kolom.json` (the static site, K17).
     *
     * @return array<string, mixed>
     */
    public static function toArray(): array
    {
        $sheets = [];

        foreach (self::SHEETS as $code => $spec) {
            $sheets[$code] = [
                'marker'   => $spec['marker'],
                'title'    => $spec['title'],
                'imported' => $spec['imported'],
                'layout'   => $spec['layout'],
                'key'      => $spec['key'] ?? [],
                'paper'    => $spec['paper'],
                'columns'  => $spec['imported'] ? self::columns($code) : [],
            ];
        }

        return ['version' => self::VERSION, 'source' => self::DOC, 'codes' => self::CODES, 'sheets' => $sheets];
    }

    private static function normalize(string $text): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($text)) ?? '');
    }
}
