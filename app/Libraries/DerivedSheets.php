<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * The derived download sheets `turunan_*` (research revision 10, part 9:
 * 35 §10). RawDataService reads their columns, queries, and values here;
 * ExportLabels and ResearchDictionary read the wording and the dictionary
 * entry of every column from META (one place).
 *
 * - Rows come from the run each study uses (`research_studies.derived_run_id`, K7).
 * - Codes are joined from their source tables (35 §12): nothing but ids is
 *   stored in the derived tables.
 * - Never an answer, a key, a risk-item answer, a name, or `participant_id` (K12, §10.3).
 * - Filters choose rows; they never change a computation (§10.1).
 */
final class DerivedSheets
{
    /** Sheet => derived table, official order (35 §10.1). */
    public const TABLES = [
        'turunan_peserta'    => 'derived_participants',
        'turunan_sesi'       => 'derived_field_sessions',
        'turunan_tantangan'  => 'derived_challenges',
        'turunan_pengerjaan' => 'derived_attempts',
        'turunan_butir'      => 'derived_items',
        'turunan_percobaan'  => 'derived_tries',
        'turunan_pengamatan' => 'derived_observations',
        'turunan_tes'        => 'derived_test_responses',
    ];

    /** Columns per sheet: column => SQL expression for sorting, or null. */
    public const COLUMNS = [
        'turunan_peserta' => [
            'participant_code' => 'participants.participant_code', 'school_code' => 'schools.research_code', 'rombel' => 'participants.rombel', 'booklet' => 'participant_seats.booklet',
            'consent_ok' => 'd.consent_ok', 'withdrawn_on' => 'd.withdrawn_on', 'withdrawn_at' => 'd.withdrawn_at', 'cutoff_basis' => 'd.cutoff_basis',
            'is_pilot' => 'd.is_pilot', 'analysable' => 'd.analysable', 'complete' => 'd.complete', 'rapid_share' => 'd.rapid_share',
            'regions_completed' => 'd.regions_completed', 'challenges_completed' => 'd.challenges_completed', 'scored_challenges_completed' => 'd.scored_challenges_completed',
            'registration_corrected' => 'd.registration_corrected', 'questionnaire_status' => 'd.questionnaire_status', 'own_phone' => 'd.own_phone',
            'device_count' => 'd.device_count', 'access_index' => 'd.access_index', 'activity_breadth' => 'd.activity_breadth', 'own_accounts' => 'd.own_accounts',
            'social_media_own' => 'd.social_media_own', 'social_media_use' => 'd.social_media_use', 'parent_active' => 'd.parent_active', 'parent_rules' => 'd.parent_rules',
            'safety_info_sources' => 'd.safety_info_sources', 'game_time_group' => 'd.game_time_group', 'q_flags' => 'd.q_flags', 'test_status' => 'd.test_status',
            'pre_d1' => 'd.pre_d1', 'pre_d2' => 'd.pre_d2', 'pre_d3' => 'd.pre_d3', 'post_d1' => 'd.post_d1', 'post_d2' => 'd.post_d2', 'post_d3' => 'd.post_d3',
            'pre_capped' => 'd.pre_capped', 'post_capped' => 'd.post_capped', 'sikap_score' => 'd.sikap_score', 'sikap_sk1' => 'd.sikap_sk1', 'sikap_sk2' => 'd.sikap_sk2',
            'sikap_sk3' => 'd.sikap_sk3', 'sikap_sk4' => 'd.sikap_sk4', 'sikap_n_answered' => 'd.sikap_n_answered', 'sikap_flag_straight' => 'd.sikap_flag_straight',
            'sikap_missing_reason' => 'd.sikap_missing_reason', 'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_sesi' => [
            'participant_code' => 'participants.participant_code', 'field_session_code' => 'field_sessions.code', 'meeting_no' => 'field_sessions.meeting_no',
            'attended' => 'd.attended', 'session_total_ms' => 'd.session_total_ms', 'session_active_ms' => 'd.session_active_ms', 'library_pages' => 'd.library_pages',
            'library_ms' => 'd.library_ms', 'headset_share' => 'd.headset_share', 'internet' => 'd.internet', 'noise' => 'd.noise', 'teacher_present' => 'd.teacher_present',
            'minutes_available' => 'd.minutes_available', 'minutes_used' => 'd.minutes_used', 'l4_observations' => 'd.l4_observations',
            'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_tantangan' => [
            'participant_code' => 'participants.participant_code', 'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'engine_type' => 'challenge_nodes.engine_type',
            'completion_status' => 'd.completion_status', 'skipped_failed_count' => 'd.skipped_failed_count', 'skipped_left_count' => 'd.skipped_left_count',
            'closed_meeting_no' => 'd.closed_meeting_no', 'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_pengerjaan' => [
            'attempt_id' => 'd.attempt_id', 'participant_code' => 'participants.participant_code', 'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'],
            'attempt_no' => 'challenge_attempts.attempt_no', 'field_session_code' => 'field_sessions.code', 'meeting_no' => 'd.meeting_no',
            'is_scored_attempt' => 'd.is_scored_attempt', 'is_retake' => 'd.is_retake', 'segments' => 'd.segments', 'total_ms' => 'd.total_ms', 'hidden_ms' => 'd.hidden_ms',
            'idle_excess_ms' => 'd.idle_excess_ms', 'active_ms' => 'd.active_ms', 'cumulative_active_ms' => 'd.cumulative_active_ms', 'hint_count' => 'd.hint_count',
            'instruction_replays' => 'd.instruction_replays', 'material_returns' => 'd.material_returns', 'exit_count' => 'd.exit_count', 'reading_max_pct' => 'd.reading_max_pct',
            'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_butir' => [
            'participant_code' => 'participants.participant_code', 'item_key' => 'challenge_items.item_key', 'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'],
            'round' => 'challenge_items.round', 'dimension' => 'challenge_items.dimension', 'subaspect' => 'challenge_items.subaspect', 'akm_level' => 'challenge_items.akm_level',
            'testlet_key' => 'challenge_items.testlet_key',
            'first_try_score' => 'd.first_try_score', 'first_try_correct' => 'd.first_try_correct', 'missing_reason' => 'd.missing_reason', 'final_correct' => 'd.final_correct',
            'final_score' => 'd.final_score', 'tries_used' => 'd.tries_used', 'solved_on_try' => 'd.solved_on_try', 'censored' => 'd.censored', 'censor_reason' => 'd.censor_reason',
            'first_error_code' => 'd.first_error_code', 'first_error_code_analysis' => 'd.first_error_code_analysis', 'change_after_feedback' => 'd.change_after_feedback',
            'change_after_feedback_2' => 'd.change_after_feedback_2', 'answer_unchanged' => 'd.answer_unchanged', 'score_change' => 'd.score_change',
            'near_miss_count' => 'd.near_miss_count', 'empty_tap_count' => 'd.empty_tap_count', 'item_total_ms' => 'd.item_total_ms', 'item_active_ms' => 'd.item_active_ms',
            'first_input_ms' => 'd.first_input_ms', 'last_input_ms' => 'd.last_input_ms', 'first_action_key' => 'd.first_action_key', 'first_action_score' => 'd.first_action_score',
            'first_action_code' => 'd.first_action_code', 'first_reason_code' => 'd.first_reason_code', 'action_reason_pattern' => 'd.action_reason_pattern',
            'reason_level' => 'd.reason_level', 'shared_device' => 'd.shared_device', 'helped_content' => 'd.helped_content', 'rapid_guess' => 'd.rapid_guess',
            'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_percobaan' => [
            'try_id' => 'd.try_id', 'attempt_id' => 'd.attempt_id', 'participant_code' => 'participants.participant_code', 'field_session_code' => 'field_sessions.code',
            'item_key' => 'challenge_items.item_key', 'try_no' => 'item_tries.try_no',
            'is_scored_attempt' => 'd.is_scored_attempt', 'is_retake' => 'd.is_retake', 'scored_try_no' => 'd.scored_try_no', 'anomaly' => 'd.anomaly',
            'error_code' => 'd.error_code', 'error_code_at_try' => 'd.error_code_at_try', 'decision_ms' => 'd.decision_ms', 'rapid_guess' => 'd.rapid_guess',
            'reason_decision_ms' => 'd.reason_decision_ms', 'reason_rapid' => 'd.reason_rapid', 'error_code_analysis' => 'd.error_code_analysis',
            'last_try_exhausted' => 'd.last_try_exhausted', 'hint_before' => 'd.hint_before', 'item_hint_before' => 'd.item_hint_before',
            'item_narration_before' => 'd.item_narration_before', 'reason_code' => 'd.reason_code', 'action_score' => 'd.action_score',
            'shared_device' => 'd.shared_device', 'helped_content' => 'd.helped_content', 'helped_technical' => 'd.helped_technical', 'encouraged' => 'd.encouraged',
            'disturbed' => 'd.disturbed', 'deviation_code' => 'd.deviation_code', 'outside_session' => 'd.outside_session',
            'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_pengamatan' => [
            'participant_code' => 'participants.participant_code', 'field_session_code' => 'field_sessions.code', 'sheet_row' => 'field_sheet_rows.row_no',
            'attempt_id' => 'd.attempt_id', 'item_key' => 'challenge_items.item_key', 'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
        'turunan_tes' => [
            'participant_code' => 'participants.participant_code', 'occasion' => 'instrument_sittings.occasion', 'form' => 'instrument_sittings.form',
            'item_code' => 'instrument_items.code', 'unit' => 'instrument_responses.unit', 'decision_ms' => 'd.decision_ms', 'rapid_guess' => 'd.rapid_guess',
            'run_id' => 'd.run_id', 'dictionary_version' => 'd.dictionary_version',
        ],
    ];

    public const DEFAULT_SORT = [
        'turunan_peserta' => 'participant_code', 'turunan_sesi' => 'participant_code', 'turunan_tantangan' => 'participant_code',
        'turunan_pengerjaan' => 'attempt_id', 'turunan_butir' => 'participant_code', 'turunan_percobaan' => 'try_id',
        'turunan_pengamatan' => 'participant_code', 'turunan_tes' => 'participant_code',
    ];

    /** Unique order of each sheet's rows inside the current runs (offset paging, no keyset). */
    public const ORDER = [
        'turunan_peserta' => ['d.participant_id'], 'turunan_sesi' => ['d.participant_id', 'd.field_session_id'],
        'turunan_tantangan' => ['d.participant_id', 'd.challenge_node_id'], 'turunan_pengerjaan' => ['d.attempt_id'],
        'turunan_butir' => ['d.participant_id', 'd.challenge_item_id'], 'turunan_percobaan' => ['d.try_id'],
        'turunan_pengamatan' => ['d.field_sheet_row_id'], 'turunan_tes' => ['d.instrument_response_id'],
    ];

    private const A1 = 'analitik-1-penyelesaian-aktivitas';
    private const A2 = 'analitik-2-ketepatan-jawaban';
    private const A3 = 'analitik-3-ketepatan-percobaan-pertama';
    private const A4 = 'analitik-4-jumlah-percobaan';
    private const A5 = 'analitik-5-waktu-pengerjaan';
    private const A6 = 'analitik-6-pola-kesalahan';
    private const A7 = 'analitik-7-penggunaan-bantuan';
    private const A8 = 'analitik-8-navigasi-permainan';
    private const A9 = 'analitik-9-perubahan-setelah-umpan-balik';
    private const A10 = 'analitik-10-pilihan-dan-alasan-di-skenario-digital';
    private const MARK = 'penanda-untuk-analisis';
    private const MISS = 'data-hilang';
    private const FIRST = 'pengerjaan-pertama';
    private const PEOPLE = 'variabel-peserta-dan-sesi';
    private const ITEMS = 'variabel-butir';
    private const KEYS = 'kunci-penggabungan-dan-waktu';
    private const FIELD = 'aturan-gabung-lembar-lapangan';
    private const INSTR = 'tes-awalakhir-angket-dan-skala-sikap-11';
    private const VERSIONS = 'versi-dan-cara-berubah';
    private const OK = 'data-siswa-yang-bisa-dianalisis';

    /**
     * Wording and dictionary entry of every derived column:
     * [label, type, meaning, anchor in 11, analytics, enum group].
     * The dictionary status is `usulan` (35 K8) while ResearchDictionary::DRAFT.
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3: string, 4: list<int>, 5?: string}>
     */
    public const META = [
        // keys and labels joined from the raw tables
        'participant_code'   => ['Kode siswa', 'code', 'Kode samaran siswa. Penghubung antarlembar.', self::KEYS, []],
        'school_code'        => ['Kode sekolah', 'code', 'Kode sekolah penelitian, mis. S03, U01.', self::PEOPLE, []],
        'rombel'             => ['Rombel', 'code', 'Rombongan belajar, satu huruf.', self::PEOPLE, []],
        'booklet'            => ['Buklet', 'code', 'Buklet B1–B4 siswa di studi ini.', self::PEOPLE, []],
        'field_session_code' => ['Kode sesi lapangan', 'code', 'Sesi lapangan, mis. S03-A-P2 (susulan -S, -S2).', self::KEYS, []],
        'meeting_no'         => ['Pertemuan ke-', 'int', 'Nomor pertemuan sesi lapangan (1–5).', self::KEYS, [5]],
        'node_ref'           => ['Kode tantangan', 'code', 'Awalan wilayah + urutan tantangan, mis. tmg-1 … wnb-5.', self::KEYS, []],
        'engine_type'        => ['Jenis tantangan', 'enum', 'Jenis tantangan.', self::KEYS, [], 'engine'],
        'attempt_id'         => ['No. pengerjaan', 'int', 'Nomor pengerjaan tantangan; penghubung dengan lembar mentah Pengerjaan dan Riwayat Percobaan.', self::KEYS, []],
        'attempt_no'         => ['Pengerjaan ke-', 'int', 'Urutan pengerjaan tantangan itu dalam sesi bermainnya.', self::KEYS, []],
        'item_key'           => ['Kode soal', 'code', 'Kode soal {tantangan}-{variasi}{NN}.', self::KEYS, []],
        'round'              => ['Babak', 'int', 'Babak Cari Objek: 1 Temukan Benda, 2 Periksa Unggahan.', self::ITEMS, []],
        'dimension'          => ['Dimensi', 'code', 'D1, D2, D3, atau PBK.', self::ITEMS, [2, 3]],
        'subaspect'          => ['Sub-aspek', 'code', 'D1a atau D1b.', self::ITEMS, [2, 3]],
        'akm_level'          => ['Level AKM', 'code', 'L1–L3.', self::ITEMS, [2, 3]],
        'testlet_key'        => ['Kunci testlet', 'code', 'Butir yang berbagi satu bacaan atau adegan.', self::ITEMS, [3]],
        'try_id'             => ['No. percobaan', 'int', 'Nomor baris percobaan (item_tries); penghubung dengan Riwayat Percobaan.', self::KEYS, [3, 4, 6]],
        'try_no'             => ['Percobaan ke-', 'int', 'Nomor percobaan dalam pengerjaan itu, apa adanya.', self::KEYS, [4]],
        'sheet_row'          => ['Baris lembar L4', 'int', 'Nomor baris pengamatan di lembar L4 yang diunggah.', self::FIELD, []],
        'occasion'           => ['Kesempatan', 'code', 'pre = tes awal, post = tes akhir.', self::INSTR, []],
        'form'               => ['Bentuk tes', 'code', 'A atau B.', self::INSTR, []],
        'item_code'          => ['Kode butir tes', 'code', 'Kode butir tes, tanpa contoh soal.', self::INSTR, []],
        'unit'               => ['Satuan jawaban', 'code', 'Satuan jawaban butir (instrument_responses.unit).', self::INSTR, []],
        'run_id'             => ['No. putaran hitung', 'int', 'Putaran hitung lapis turunan yang menghasilkan baris ini (derived_runs).', self::VERSIONS, []],
        'dictionary_version' => ['Versi kamus', 'code', 'Versi kamus yang dipakai putaran hitung, mis. kamus-1.1-usulan.', self::VERSIONS, []],
        // turunan_peserta
        'consent_ok'           => ['Izin lengkap', 'bool', '1 = izin orang tua terisi, persetujuan siswa dicentang, dan siswa tidak mundur dengan hapus data. Siswa mundur dengan data tetap = 1, dengan data sampai saat mundur.', self::MARK, []],
        'withdrawn_on'         => ['Tanggal mundur', 'text', 'Tanggal status mundur (data tetap); kosong bila tidak mundur.', self::MARK, []],
        'withdrawn_at'         => ['Saat mundur', 'datetime', 'Batas data lapis turunan: data sesudahnya tidak dihitung.', self::MARK, []],
        'cutoff_basis'         => ['Dasar saat mundur', 'code', 'deactivated_at (akun dinonaktifkan) atau status_on (akhir hari tanggal status).', self::MARK, []],
        'is_pilot'             => ['Uji coba', 'bool', '1 = sekolah uji coba (kode berawalan U); tidak masuk analisis utama.', self::MARK, []],
        'analysable'           => ['Bisa dianalisis', 'bool', '1 = izin lengkap, bukan uji coba, dan percobaan pertama bukan dari perangkat bersama pada sedikitnya 49 dari 61 butir literasi digital.', self::OK, []],
        'complete'             => ['Lengkap 61 butir', 'bool', '1 = ke-61 butir literasi digital punya percobaan pertama.', self::OK, []],
        'rapid_share'          => ['Bagian tebakan cepat', 'text', 'Proporsi percobaan pertama bertanda tebakan cepat di antara yang punya waktu putus (0–1).', self::PEOPLE, [6]],
        'regions_completed'    => ['Wilayah tuntas', 'int', 'Banyak wilayah yang kelima tantangannya tuntas (0–3).', self::A1, [1]],
        'challenges_completed' => ['Tantangan tuntas', 'int', 'Banyak tantangan tuntas (0–15).', self::A1, [1]],
        'scored_challenges_completed' => ['Tantangan dinilai tuntas', 'int', 'Banyak tantangan yang dinilai (tanpa Susun Gambar) yang tuntas (0–12).', self::A1, [1]],
        'registration_corrected' => ['Data daftar dibetulkan', 'bool', '1 = umur, jenis kelamin, atau kelas pernah dibetulkan admin.', self::MARK, []],
        'questionnaire_status' => ['Status angket', 'code', 'lengkap (paling banyak 2 butir kosong), sebagian, hilang.', self::INSTR, []],
        'own_phone'            => ['Punya HP sendiri', 'bool', '1 bila ang-01 = 2.', self::INSTR, []],
        'device_count'         => ['Banyak alat di rumah', 'int', 'Jumlah HP, tablet, komputer, dan konsol di rumah (0–4).', self::INSTR, []],
        'access_index'         => ['Indeks akses', 'int', 'phone_access + device_count + home_internet (0–9); kosong bila satu komponen hilang.', self::INSTR, []],
        'activity_breadth'     => ['Keragaman kegiatan', 'int', 'Banyak kegiatan ang-07 berkode 1 atau 2 (0–7).', self::INSTR, []],
        'own_accounts'         => ['Akun sendiri', 'int', 'Banyak akun ang-08 berkode 2 (0–6).', self::INSTR, []],
        'social_media_own'     => ['Punya media sosial', 'bool', '1 bila kode 2 di TikTok, Instagram, atau Facebook.', self::INSTR, []],
        'social_media_use'     => ['Memakai media sosial', 'bool', '1 bila kode 1 atau 2 di TikTok, Instagram, atau Facebook.', self::INSTR, []],
        'parent_active'        => ['Pendampingan aktif', 'text', 'Rata-rata parent_couse dan parent_talk (0–3).', self::INSTR, []],
        'parent_rules'         => ['Aturan orang tua', 'int', 'Banyak aturan waktu dan isi yang ada (0–2).', self::INSTR, []],
        'safety_info_sources'  => ['Sumber cara aman', 'int', 'Banyak sumber cara aman di ang-12 (0–5).', self::INSTR, []],
        'game_time_group'      => ['Kelompok lama main', 'code', 'rendah (0–1), sedang (2–3), tinggi (4) dari game_time_school.', self::INSTR, []],
        'q_flags'              => ['Isian bertentangan', 'code', 'Kode butir angket yang isinya bertentangan, dipisah titik koma.', self::INSTR, []],
        'test_status'          => ['Status tes', 'code', 'lengkap, hanya_awal, hanya_akhir, atau hilang (dari modul tes).', self::INSTR, []],
        'pre_d1'  => ['Skor D1 tes awal', 'int', 'Skor mentah bagian D1 tes awal.', self::INSTR, []],
        'pre_d2'  => ['Skor D2 tes awal', 'int', 'Skor mentah bagian D2 tes awal.', self::INSTR, []],
        'pre_d3'  => ['Skor D3 tes awal', 'int', 'Skor mentah bagian D3 tes awal.', self::INSTR, []],
        'post_d1' => ['Skor D1 tes akhir', 'int', 'Skor mentah bagian D1 tes akhir.', self::INSTR, []],
        'post_d2' => ['Skor D2 tes akhir', 'int', 'Skor mentah bagian D2 tes akhir.', self::INSTR, []],
        'post_d3' => ['Skor D3 tes akhir', 'int', 'Skor mentah bagian D3 tes akhir.', self::INSTR, []],
        'pre_capped'  => ['Ditutup batas (tes awal)', 'code', 'Blok yang ditutup batas waktu: p1, p23, atau p1;p23.', self::INSTR, []],
        'post_capped' => ['Ditutup batas (tes akhir)', 'code', 'Blok yang ditutup batas waktu: p1, p23, atau p1;p23.', self::INSTR, []],
        'sikap_score' => ['Skor sikap', 'text', 'Rata-rata skor butir sesudah butir negatif dibalik (1–4); kosong bila kurang dari 10 butir dijawab.', self::INSTR, []],
        'sikap_sk1'   => ['Skor SK1 Menghargai', 'text', 'Rata-rata tiga butir SK1; kosong bila satu tidak dijawab.', self::INSTR, []],
        'sikap_sk2'   => ['Skor SK2 Ingin tahu', 'text', 'Rata-rata tiga butir SK2; kosong bila satu tidak dijawab.', self::INSTR, []],
        'sikap_sk3'   => ['Skor SK3 Mau ikut merawat', 'text', 'Rata-rata tiga butir SK3; kosong bila satu tidak dijawab.', self::INSTR, []],
        'sikap_sk4'   => ['Skor SK4 Bangga', 'text', 'Rata-rata tiga butir SK4; kosong bila satu tidak dijawab.', self::INSTR, []],
        'sikap_n_answered'    => ['Butir sikap dijawab', 'int', 'Banyak butir skala sikap yang dijawab (0–12).', self::INSTR, []],
        'sikap_flag_straight' => ['Jawaban sikap seragam', 'bool', '1 bila ke-12 jawaban mentah sama.', self::INSTR, []],
        'sikap_missing_reason' => ['Alasan skala kosong', 'code', 'absen (tidak hadir di P5 dan susulannya) atau belum_tuntas; kosong bila skala terisi atau P5 siswa belum dibuka saat putaran hitung; tanpa_izin hanya diisi di analisis dari L7.', self::INSTR, []],
        // turunan_sesi
        'attended'          => ['Hadir', 'bool', '1 bila siswa punya sesi bermain di sesi lapangan ini.', self::PEOPLE, [5]],
        'session_total_ms'  => ['Waktu total sesi (mdtk)', 'ms', 'Jumlah semua sesi bermain siswa di sesi lapangan ini.', self::A5, [5]],
        'session_active_ms' => ['Waktu aktif sesi (mdtk)', 'ms', 'Waktu total dikurangi layar tersembunyi dan jeda melebihi ambang.', self::A5, [5]],
        'library_pages'     => ['Halaman Pustaka', 'int', 'Banyak halaman Pustaka yang dibuka.', self::A8, [8]],
        'library_ms'        => ['Waktu Pustaka (mdtk)', 'ms', 'Waktu aktif sejak halaman Pustaka dibuka sampai kejadian berikutnya.', self::A8, [8]],
        'headset_share'     => ['Bagian headset', 'text', 'Headset dipakai dibagi siswa bermain (L1).', self::A7, [7]],
        'internet'          => ['Internet', 'text', 'Isian L1.', self::FIELD, []],
        'noise'             => ['Kebisingan', 'code', 'Isian L1.', self::FIELD, []],
        'teacher_present'   => ['Kehadiran guru', 'text', 'Isian L1.', self::FIELD, []],
        'minutes_available' => ['Menit tersedia', 'int', 'Isian L1.', self::FIELD, []],
        'minutes_used'      => ['Menit dipakai', 'int', 'Isian L1.', self::FIELD, []],
        'l4_observations'   => ['Pengamatan L4', 'int', 'Banyak pengamatan L4 untuk siswa ini; kosong bila L4 belum diunggah.', self::FIELD, []],
        // turunan_tantangan
        'completion_status'    => ['Status penyelesaian', 'code', 'solved, exhausted, unfinished, not_started, atau locked, dari pengerjaan pertama sampai jam putaran hitung.', self::A1, [1]],
        'skipped_failed_count' => ['Dilewati (a)', 'int', 'Petunjuk Cari Objek dan skenario yang ditutup setelah kesempatan ketiga.', self::A1, [1]],
        'skipped_left_count'   => ['Dilewati (b)', 'int', 'Pengerjaan pertama yang ditinggal dan tidak dilanjutkan di sesi lapangan yang sama.', self::A1, [1]],
        'closed_meeting_no'    => ['Tuntas di pertemuan', 'int', 'Pertemuan saat tantangan tuntas.', self::A1, [1]],
        // turunan_pengerjaan
        'is_scored_attempt'    => ['Pengerjaan pertama', 'bool', '1 = pengerjaan yang dinilai (variasi buklet, sebelum tantangan tertutup pertama kali).', self::FIRST, [1, 3]],
        'is_retake'            => ['Pengerjaan ulang', 'bool', '1 = pengerjaan sesudah tantangan tertutup.', self::MARK, [1]],
        'segments'             => ['Potongan', 'int', 'Banyak potongan pengerjaan (sesi bermain).', self::A5, [5]],
        'total_ms'             => ['Waktu total (mdtk)', 'ms', 'Jumlah lama potongan; selang di antara potongan tidak dihitung.', self::A5, [5]],
        'hidden_ms'            => ['Waktu tersembunyi (mdtk)', 'ms', 'Selang layar tersembunyi.', self::A5, [5]],
        'idle_excess_ms'       => ['Jeda berlebih (mdtk)', 'ms', 'Bagian jeda tanpa masukan yang melebihi ambang jeda studi.', self::A5, [5]],
        'active_ms'            => ['Waktu aktif (mdtk)', 'ms', 'total_ms − hidden_ms − idle_excess_ms.', self::A5, [5]],
        'cumulative_active_ms' => ['Waktu aktif kumulatif (mdtk)', 'ms', 'Waktu aktif semua sesi bermain siswa sebelum pengerjaan ini dimulai.', self::A5, [5]],
        'hint_count'           => ['Bantuan dibuka', 'int', 'Banyak Bantuan dibuka.', self::A7, [7]],
        'instruction_replays'  => ['Narasi instruksi diputar', 'int', 'Banyak pemutaran narasi instruksi.', self::A7, [7]],
        'material_returns'     => ['Kembali ke materi', 'int', 'Pustaka dibuka saat pengerjaan belum tertutup ditambah bacaan dibuka lagi.', self::A8, [8]],
        'exit_count'           => ['Keluar', 'int', 'Banyak kali keluar dari tantangan sebelum tertutup.', self::A8, [8]],
        'reading_max_pct'      => ['Bacaan terjauh (%)', 'int', 'Bagian bacaan terjauh yang tergulir.', self::A8, [8]],
        // turunan_butir
        'first_try_score'   => ['Skor percobaan pertama', 'int', '0–1, atau 0–2 untuk D2; kosong bila tanpa percobaan.', self::A3, [3]],
        'first_try_correct' => ['Percobaan pertama maksimal', 'bool', '1 bila skor percobaan pertama maksimal.', self::A3, [3]],
        'missing_reason'    => ['Alasan data hilang', 'code', 'locked, not_started, no_try; kosong bila ada percobaan.', self::MISS, [3]],
        'final_correct'     => ['Benar akhir', 'bool', '1 = benar dalam tiga kesempatan, 0 = tidak pernah benar; kosong bila tanpa percobaan.', self::A2, [2]],
        'final_score'       => ['Skor akhir', 'int', 'D2: skor tindakan percobaan terakhir; lainnya sama dengan Benar akhir.', self::A2, [2]],
        'tries_used'        => ['Percobaan dipakai', 'int', 'Banyak percobaan yang dinilai (1–3).', self::A4, [4]],
        'solved_on_try'     => ['Benar pada percobaan', 'int', 'Percobaan benar pertama; kosong bila tidak pernah benar.', self::A4, [4]],
        'censored'          => ['Tersensor', 'bool', '1 bila tidak pernah benar.', self::A4, [4]],
        'censor_reason'     => ['Alasan tersensor', 'code', 'exhausted (tiga dipakai) atau left (kurang dari tiga).', self::A4, [4]],
        'first_error_code'  => ['Kode kesalahan pertama', 'code', 'Kode bank terbaru percobaan pertama yang salah.', self::A6, [6]],
        'first_error_code_analysis' => ['Kode analisis pertama', 'code', 'Sama, NON-TEB bila tebakan cepat.', self::A6, [6]],
        'change_after_feedback'   => ['Perubahan 1→2', 'code', 'fixed, same_error, other_error, no_second_try.', self::A9, [9]],
        'change_after_feedback_2' => ['Perubahan 2→3', 'code', 'fixed, same_error, other_error, no_second_try.', self::A9, [9]],
        'answer_unchanged'  => ['Jawaban tidak diubah', 'bool', '1 bila jawaban percobaan kedua sama persis dengan pertama.', self::A9, [9]],
        'score_change'      => ['Perubahan skor D2', 'int', 'Skor percobaan kedua dikurangi pertama (D2).', self::A9, [9]],
        'near_miss_count'   => ['Ketukan nyaris', 'int', 'Ketukan kosong paling jauh 24 dp dari bagian ketuk kunci (NON-SEN).', self::A6, [6]],
        'empty_tap_count'   => ['Ketukan kosong lain', 'int', 'Ketukan kosong lebih jauh dari 24 dp, termasuk kelebihan yang dijumlahkan.', self::A6, [6]],
        'item_total_ms'     => ['Waktu total butir (mdtk)', 'ms', 'Cari Objek dan Skenario Digital: dari butir bisa dijawab sampai tertutup, hanya selama butir itu tampil di layar (event item_shown); data tanpa item_shown memakai seluruh rentang.', self::A5, [5]],
        'item_active_ms'    => ['Waktu aktif butir (mdtk)', 'ms', 'Sama, dengan aturan waktu aktif.', self::A5, [5]],
        'first_input_ms'    => ['Masukan pertama (mdtk)', 'ms', 'Dari percobaan pertama (perkiraan untuk Isi Rumpang dan Benar atau Salah).', self::A5, [5]],
        'last_input_ms'     => ['Masukan terakhir (mdtk)', 'ms', 'Dari percobaan pertama.', self::A5, [5]],
        'first_action_key'  => ['Tindakan pertama', 'code', 'Kunci pilihan tindakan pertama (t1, t2, …), bukan teks.', self::A10, [10]],
        'first_action_score' => ['Skor tindakan pertama', 'int', '0, 1, atau 2.', self::A10, [10]],
        'first_action_code' => ['Kode tindakan pertama', 'code', 'Kode DIG-* tindakan pertama; kosong bila tepat.', self::A10, [10]],
        'first_reason_code' => ['Kode alasan pertama', 'code', 'Kode R-* alasan pertama.', self::A10, [10]],
        'action_reason_pattern' => ['Pola tindakan dan alasan', 'code', 'safe_expected, safe_rule, safe_wrong_reason, partial, risky.', self::A10, [10]],
        'reason_level'      => ['Tingkat alasan', 'int', 'Kosong sampai urutan alasan ditetapkan setelah uji coba.', self::A10, [10]],
        // turunan_percobaan
        'scored_try_no'      => ['Percobaan dinilai ke-', 'int', 'Nomor percobaan butir di pengerjaan pertama setelah aturan cadangan (1–3).', self::FIRST, [3, 4]],
        'anomaly'            => ['Anomali', 'code', 'extra_try untuk percobaan sesudah yang ketiga.', self::FIRST, [4]],
        'error_code'         => ['Kode kesalahan', 'code', 'Kode dari bank terbaru untuk pilihan percobaan itu; kosong untuk percobaan benar dan Susun Gambar.', self::A6, [6]],
        'error_code_at_try'  => ['Kode saat percobaan', 'code', 'Kode yang dicatat sistem saat percobaan dinilai.', self::A6, [6, 9]],
        'decision_ms'        => ['Waktu putus (mdtk)', 'ms', 'Masukan yang dinilai dikurangi saat butir bisa dijawab; kosong bila tidak ada data.', self::A6, [5, 6]],
        'rapid_guess'        => ['Tebakan cepat', 'bool', '1 bila waktu putus di bawah ambang tebakan cepat studi; kosong tanpa waktu putus.', self::A6, [6]],
        'reason_decision_ms' => ['Waktu putus alasan (mdtk)', 'ms', 'Skenario Digital: dari pilihan alasan tampil sampai alasan dipilih.', self::A6, [6, 10]],
        'reason_rapid'       => ['Tebakan cepat alasan', 'bool', 'Sama untuk alasan Skenario Digital.', self::A6, [6, 10]],
        'error_code_analysis' => ['Kode analisis', 'code', 'Kode kesalahan; NON-TEB untuk percobaan salah yang tebakan cepat.', self::A6, [6]],
        'last_try_exhausted' => ['Kesempatan terakhir habis', 'bool', '1 pada percobaan salah yang menghabiskan kesempatan terakhir (NON-HABIS).', self::A6, [6]],
        'hint_before'        => ['Bantuan sebelumnya', 'bool', '1 bila Bantuan dibuka di pengerjaan itu sebelum percobaan.', self::A7, [7]],
        'item_hint_before'   => ['Bantuan butir sebelumnya', 'bool', '1 bila Bantuan khusus butir itu dibuka sebelum percobaan.', self::A7, [7]],
        'item_narration_before' => ['Narasi butir sebelumnya', 'bool', '1 bila narasi butir itu diputar siswa sebelum percobaan.', self::A7, [7]],
        'reason_code'        => ['Kode alasan', 'code', 'Kode R-* alasan Skenario Digital.', self::A10, [10]],
        'action_score'       => ['Skor tindakan', 'int', 'Skor tindakan Skenario Digital (0–2).', self::A10, [10]],
        'shared_device'      => ['Perangkat bersama', 'bool', '1 bila L2 mencatat Pakai bareng: Ya; kosong bila L2 belum diunggah.', self::MARK, [3]],
        'helped_content'     => ['Dibantu isi', 'bool', '1 bila ada kejadian L3 ISI, STR, atau BHS dalam jendela waktu.', self::MARK, [3, 7]],
        'helped_technical'   => ['Dibantu teknis', 'bool', 'Sama untuk TKN.', self::MARK, [7]],
        'encouraged'         => ['Disemangati', 'bool', 'Sama untuk SMG.', self::MARK, [7]],
        'disturbed'          => ['Terganggu', 'bool', 'Sama untuk GG-*.', self::MARK, []],
        'deviation_code'     => ['Kode penyimpangan', 'code', 'Kode DEV-* L1 yang mengenai siswa saat percobaan, dipisah titik koma.', self::MARK, []],
        'outside_session'    => ['Di luar sesi', 'bool', '1 bila percobaan di luar jam sesi lapangan; dikeluarkan dari ringkasan.', self::MARK, []],
    ];

    /** Sheet titles and meanings (Kamus Data). */
    public const TITLES = [
        'turunan_peserta'    => ['Turunan Peserta', 'Lapis turunan: satu baris per siswa (izin, kelengkapan, angket, tes, skala sikap).'],
        'turunan_sesi'       => ['Turunan Sesi', 'Lapis turunan: satu baris per siswa × sesi lapangan.'],
        'turunan_tantangan'  => ['Turunan Tantangan', 'Lapis turunan: satu baris per siswa × tantangan.'],
        'turunan_pengerjaan' => ['Turunan Pengerjaan', 'Lapis turunan: satu baris per pengerjaan.'],
        'turunan_butir'      => ['Turunan Butir', 'Lapis turunan: satu baris per siswa × butir yang dinilai, juga tanpa percobaan.'],
        'turunan_percobaan'  => ['Turunan Percobaan', 'Lapis turunan: satu baris per percobaan.'],
        'turunan_pengamatan' => ['Turunan Pengamatan', 'Lapis turunan: satu baris per pengamatan L4, dengan pengerjaan dan butir yang aktif.'],
        'turunan_tes'        => ['Turunan Tes', 'Lapis turunan: satu baris per jawaban tes awal dan akhir, dengan waktu putus dan tebakan cepat.'],
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    public static function has(string $sheet): bool
    {
        return isset(self::TABLES[$sheet]);
    }

    /** @return array<string, list<string>|string|null> */
    public static function columnMap(string $sheet): array
    {
        return self::COLUMNS[$sheet];
    }

    /**
     * Rows of one sheet from the current run of each study.
     *
     * @param array<string, int|string>                  $filters
     * @param callable(BaseBuilder, array, string): void $participantFilters
     */
    public function query(string $sheet, array $filters, callable $participantFilters): BaseBuilder
    {
        $table   = self::TABLES[$sheet];
        $builder = $this->db->table($table . ' d')
            ->join('research_studies', 'research_studies.derived_run_id = d.run_id')
            ->join('participants', 'participants.id = d.participant_id')
            ->select('d.*, participants.participant_code')
            ->where('participants.deleted_at', null);

        switch ($sheet) {
            case 'turunan_peserta':
                $builder->join('schools', 'schools.id = participants.school_id', 'left')
                    ->join('participant_seats', 'participant_seats.participant_id = participants.id AND participant_seats.study_id = research_studies.id', 'left')
                    ->select('schools.research_code AS school_code, participants.rombel, participant_seats.booklet');
                break;

            case 'turunan_sesi':
                $builder->join('field_sessions', 'field_sessions.id = d.field_session_id')
                    ->select('field_sessions.code AS field_session_code, field_sessions.meeting_no AS fs_meeting_no');
                break;

            case 'turunan_tantangan':
                $builder->join('challenge_nodes', 'challenge_nodes.id = d.challenge_node_id')->join('levels', 'levels.id = challenge_nodes.level_id')
                    ->select('levels.code AS level_code, challenge_nodes.sequence AS node_sequence, challenge_nodes.engine_type');
                break;

            case 'turunan_pengerjaan':
                $builder->join('challenge_attempts', 'challenge_attempts.id = d.attempt_id')
                    ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')->join('levels', 'levels.id = challenge_nodes.level_id')
                    ->join('field_sessions', 'field_sessions.id = d.field_session_id', 'left')
                    ->select('levels.code AS level_code, challenge_nodes.sequence AS node_sequence, challenge_attempts.attempt_no, field_sessions.code AS field_session_code');
                break;

            case 'turunan_butir':
                $builder->join('challenge_items', 'challenge_items.id = d.challenge_item_id')
                    ->join('challenge_nodes', 'challenge_nodes.id = challenge_items.challenge_node_id')->join('levels', 'levels.id = challenge_nodes.level_id')
                    ->select('challenge_items.item_key, levels.code AS level_code, challenge_nodes.sequence AS node_sequence, challenge_items.round')
                    ->select('challenge_items.dimension, challenge_items.subaspect, challenge_items.akm_level, challenge_items.testlet_key');
                break;

            case 'turunan_percobaan':
                $builder->join('item_tries', 'item_tries.id = d.try_id')->join('challenge_items', 'challenge_items.id = item_tries.challenge_item_id')
                    ->join('challenge_attempts', 'challenge_attempts.id = d.attempt_id')
                    ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')->join('levels', 'levels.id = challenge_nodes.level_id')
                    ->join('field_sessions', 'field_sessions.id = d.field_session_id', 'left')
                    ->select('challenge_items.item_key, item_tries.try_no, field_sessions.code AS field_session_code');
                break;

            case 'turunan_pengamatan':
                $builder->join('field_sheet_rows', 'field_sheet_rows.id = d.field_sheet_row_id')
                    ->join('field_sheet_imports', 'field_sheet_imports.id = field_sheet_rows.import_id')
                    ->join('field_sessions', 'field_sessions.id = field_sheet_imports.field_session_id')
                    ->join('challenge_items', 'challenge_items.id = d.challenge_item_id', 'left')
                    ->select('field_sessions.code AS field_session_code, field_sheet_rows.row_no AS sheet_row, challenge_items.item_key');
                break;

            case 'turunan_tes':
                $builder->join('instrument_responses', 'instrument_responses.id = d.instrument_response_id')
                    ->join('instrument_sittings', 'instrument_sittings.id = instrument_responses.sitting_id')
                    ->join('instrument_items', 'instrument_items.id = instrument_responses.item_id')
                    ->select('instrument_sittings.occasion, instrument_sittings.form, instrument_items.code AS item_code, instrument_responses.unit');
                break;
        }

        if (isset($filters['study_id'])) {
            $builder->where('research_studies.id', (int) $filters['study_id']);
        }

        // Content filters where the sheet has a challenge
        if (isset($filters['level_id']) && in_array($sheet, ['turunan_tantangan', 'turunan_pengerjaan', 'turunan_butir', 'turunan_percobaan'], true)) {
            $builder->where('challenge_nodes.level_id', (int) $filters['level_id']);
        }

        if (isset($filters['node_id']) && in_array($sheet, ['turunan_tantangan', 'turunan_pengerjaan', 'turunan_butir', 'turunan_percobaan'], true)) {
            $builder->where('challenge_nodes.id', (int) $filters['node_id']);
        }

        if (in_array($sheet, ['turunan_sesi', 'turunan_pengerjaan', 'turunan_percobaan', 'turunan_pengamatan'], true)) {
            if (isset($filters['field_session_code'])) {
                $builder->where('field_sessions.code', $filters['field_session_code']);
            }

            if (isset($filters['meeting_no'])) {
                $builder->where('field_sessions.meeting_no', (int) $filters['meeting_no']);
            }
        }

        $participantFilters($builder, array_diff_key($filters, array_flip(['date_from', 'date_to', 'study_id'])), 'participants.created_at');

        return $builder;
    }

    /** Values of one row (codes joined, node_ref built). @return array<string, mixed> */
    public function values(string $sheet, array $row): array
    {
        if (isset($row['level_code'], $row['node_sequence'])) {
            helper('gelita');
            $row['node_ref'] = node_ref((string) $row['level_code'], (int) $row['node_sequence']);
        }

        if ($sheet === 'turunan_sesi') {
            $row['meeting_no'] = $row['fs_meeting_no'] ?? null;
        }

        return $row;
    }

    // Raw sheets: `after_withdrawal` (35 §7, §10; 29 §7.3)

    /** Raw sheets with `after_withdrawal` and the column holding each row's time. */
    public const AFTER_WITHDRAWAL = [
        'pengerjaan' => 'started_at', 'jawaban' => 'started_at', 'percobaan' => 'occurred_at',
        'navigasi' => 'occurred_at_corrected', 'bantuan' => 'occurred_at_corrected', 'selesai' => 'occurred_at_corrected', 'event' => 'occurred_at_corrected',
    ];

    /** @var array<string, int>|null participant code => withdrawal cutoff (ms) */
    private ?array $cutoffs = null;

    /**
     * 1 when a raw row lies after its student's withdrawal cutoff (status
     * `withdrawn_keep`, same rule as the derived layer: ParticipantCalculator::cutoff()), else 0.
     */
    public function afterWithdrawal(?string $participantCode, mixed $time): int
    {
        if ($this->cutoffs === null) {
            $this->cutoffs = [];

            foreach ($this->db->table('participant_consents')
                ->select('participants.participant_code, participants.deactivated_at, participant_consents.status, participant_consents.status_on')
                ->join('participants', 'participants.id = participant_consents.participant_id')
                ->where('participant_consents.status', 'withdrawn_keep')->get()->getResultArray() as $row) {
                $cut = Derived\ParticipantCalculator::cutoff(['deactivated_at' => Derived\Clock::ms($row['deactivated_at'])], $row);

                if ($cut['withdrawn_at'] !== null) {
                    $this->cutoffs[(string) $row['participant_code']] = $cut['withdrawn_at'];
                }
            }
        }

        $limit = $participantCode === null ? null : ($this->cutoffs[$participantCode] ?? null);
        $at    = Derived\Clock::ms($time);

        return $limit !== null && $at !== null && $at > $limit ? 1 : 0;
    }
}
