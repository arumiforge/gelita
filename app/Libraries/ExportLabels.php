<?php

namespace App\Libraries;

/**
 * Judul, jenis, arti, satuan, dan daftar nilai setiap kolom data mentah
 * (Dok 3 §9.2) — satu sumber untuk tabel `/admin/data/{dimensi}`, ekspor
 * XLSX/CSV-ZIP, dan lembar Kamus Data.
 *
 * Kunci lembar (`peserta`, `jawaban`, …) dan kunci kolom (`attempt_id`, …)
 * adalah kode tetap: dipakai URL tabel, `scope_json`, judul kolom CSV, dan
 * uji. Judul berbahasa Indonesia hanya untuk tampilan dan XLSX berlabel.
 *
 * Jenis kolom (`type`):
 * - `code`, `text`, `int`: apa adanya;
 * - `ms`: bilangan bulat milidetik;
 * - `datetime`: cap waktu ISO 8601 zona Asia/Jakarta dengan milidetik,
 *   mis. `2026-10-07T08:30:15.123+07:00`;
 * - `bool`: 1/0 (berlabel: ya/tidak);
 * - `enum`: kode tersimpan (berlabel: lihat `values()`);
 * - `json`: teks JSON apa adanya.
 *
 * Nilai berlabel ditulis `label()`. Kolom wilayah dan jawaban dilabeli
 * `RawDataService`, karena labelnya berasal dari isi database (nama
 * wilayah, kalimat jawaban per jenis soal).
 */
final class ExportLabels
{
    /** Satuan yang dicantumkan di judul kolom dan Kamus Data. */
    public const UNIT_MS = 'mdtk';

    public const UNIT_TIME = 'ISO 8601, WIB';

    /**
     * Kolom yang sama artinya di banyak lembar:
     * [judul, jenis, arti, kelompok nilai (enum)].
     *
     * @var array<string, array{0: string, 1: string, 2: string, 3?: string}>
     */
    private const COMMON = [
        'participant_code'   => ['Kode siswa', 'code', 'Kode samaran siswa, mis. GLT-000001. Penghubung antarlembar.'],
        'session_code'       => ['Kode sesi', 'code', 'Kode sesi bermain (32 karakter heksadesimal). Penghubung antarlembar.'],
        'attempt_id'         => ['No. pengerjaan', 'int', 'Nomor pengerjaan tantangan. Penghubung lembar Pengerjaan, Jawaban, Riwayat Percobaan, dan Bantuan.'],
        'level'              => ['Wilayah', 'enum', 'Wilayah tantangan.', 'level'],
        'node_ref'           => ['Kode tantangan', 'code', 'Awalan wilayah + urutan tantangan, mis. tmg-1 … wnb-4.'],
        'node_sequence'      => ['Tantangan ke-', 'int', 'Urutan tantangan dalam wilayah (1–4).'],
        'engine_type'        => ['Jenis tantangan', 'enum', 'Jenis tantangan.', 'engine'],
        'variant_group'      => ['Variasi', 'code', 'Variasi soal yang dipakai: a, b, atau c (Susun Gambar a sampai j). Pengerjaan pertama selalu memakai a, b, atau c; d–j hanya muncul pada pengerjaan ulang.'],
        'variant_title'      => ['Judul variasi', 'text', 'Judul tantangan pada variasi itu.'],
        'item_key'           => ['Kode soal', 'code', 'Kode soal {tantangan}-{variasi}{NN}, mis. tmg-2-a01.'],
        'interaction_type'   => ['Jenis soal', 'enum', 'Bentuk interaksi soal.', 'interaction'],
        'bloom_level'        => ['Tingkat berpikir (Bloom)', 'enum', 'Taksonomi Bloom revisi; kosong untuk Susun Gambar dan benda pengecoh.', 'bloom'],
        'skill'              => ['Keterampilan membaca', 'enum', 'Keterampilan yang diukur pernyataan Benar atau Salah; kosong untuk jenis soal lain.', 'skill'],
        'indicator'          => ['Indikator', 'code', 'Kode indikator pembelajaran soal (atau tantangannya).'],
        'prompt'             => ['Soal', 'text', 'Isi soal dalam bahasa Indonesia.'],
        'display_order'      => ['Urutan tampil', 'int', 'Urutan soal pada layar pengerjaan.'],
        'is_correct'         => ['Benar', 'bool', '1 = benar.'],
        'first_pass_correct' => ['Berhasil pada Percobaan Pertama', 'bool', '1 = soal benar pada percobaan pertama.'],
        'answer_key'         => ['Kunci jawaban', 'json', 'Kunci jawaban soal (hanya ketua peneliti). Berlabel: kalimat; berkode: JSON.'],
        'started_at'         => ['Mulai', 'datetime', 'Waktu mulai.'],
        'completed_at'       => ['Selesai', 'datetime', 'Waktu pengerjaan ditutup (selesai atau ditinggalkan).'],
        'answered_at'        => ['Waktu menjawab', 'datetime', 'Waktu percobaan terakhir soal ini.'],
        'occurred_at'        => ['Waktu kejadian', 'datetime', 'Waktu kejadian.'],
        'server_received_at' => ['Diterima server', 'datetime', 'Waktu event diterima server.'],
        'duration_ms'        => ['Durasi (mdtk)', 'ms', 'Lama, dalam milidetik.'],
        'event_type'         => ['Jenis event', 'enum', 'Jenis kejadian (game_event_logs.event_type).', 'event'],
        'sequence_no'        => ['Urutan event', 'int', 'Nomor urut event dalam sesi. Sejak Oktober 2026 nomor dari perangkat berlanjut antarhalaman satu tab; sebelumnya mulai dari 1 di setiap halaman.'],
        'clock_offset_ms'    => ['Selisih jam (mdtk)', 'ms', 'Waktu server menerima kiriman dikurangi jam perangkat saat mengirim. Waktu terkoreksi = Waktu kejadian + selisih ini. 0 untuk event yang dicatat server; kosong bila perangkat tidak mengirim jamnya.'],
        'payload'            => ['Rincian (JSON)', 'json', 'Data tambahan event apa adanya.'],
        'locale'             => ['Bahasa', 'enum', 'Bahasa permainan.', 'locale'],
        'device_type'        => ['Jenis perangkat', 'enum', 'Jenis perangkat menurut pengurai User-Agent.', 'device'],
        'os_name'            => ['Sistem operasi', 'text', 'Nama sistem operasi.'],
        'browser_name'       => ['Peramban', 'text', 'Nama peramban.'],
        'updated_at'         => ['Diperbarui', 'datetime', 'Waktu baris terakhir diubah.'],
        // Part 3 (29 §7.3, §7.4)
        'field_session_code'    => ['Kode sesi lapangan', 'code', 'Sesi lapangan sesi bermain ini, mis. S03-A-P2 (susulan -S, -S2). Penghubung dengan lembar lapangan.'],
        'meeting_no'            => ['Pertemuan ke-', 'int', 'Nomor pertemuan sesi lapangan (1–5).'],
        'school_code'           => ['Kode sekolah', 'code', 'Kode sekolah penelitian, mis. S03, U01.'],
        'rombel'                => ['Rombel', 'code', 'Rombongan belajar, satu huruf.'],
        'study_code'            => ['Kode penelitian', 'code', 'Kode studi (research_studies.code).'],
        'occurred_at_corrected' => ['Waktu kejadian (terkoreksi)', 'datetime', 'Jam perangkat + selisih jam perangkat dan server. Tanpa selisih: jam perangkat apa adanya (Selisih jam diketahui = 0). Event yang dicatat server: waktu server.'],
        'offset_known'          => ['Selisih jam diketahui', 'bool', '1 = selisih jam perangkat dan server diketahui untuk event ini.'],
        'variant'               => ['Variasi (kamus)', 'code', 'Nama 11 untuk variant_group: a, b, x (dipakai semua buklet), atau c–k (Susun Gambar).'],
        'error_code'            => ['Kode kesalahan', 'code', 'Kode kesalahan dari bank soal, diturunkan server untuk percobaan salah.'],
        'sheet_row'             => ['Baris ke-', 'int', 'Nomor baris di lembar yang diunggah.'],
    ];

    /**
     * Kolom khusus per lembar (menimpa COMMON bila kuncinya sama).
     *
     * @var array<string, array<string, array{0: string, 1: string, 2: string, 3?: string}>>
     */
    private const SHEETS = [
        'kamus' => [
            'lembar' => ['Lembar', 'code', 'Kode lembar (nama berkas CSV).'],
            'kolom'  => ['Kolom', 'code', 'Kode kolom (judul kolom berkode).'],
            'judul'  => ['Judul', 'text', 'Judul kolom pada berkas berlabel.'],
            'arti'   => ['Arti', 'text', 'Arti kolom.'],
            'satuan' => ['Satuan', 'text', 'Satuan nilai.'],
            'nilai'  => ['Nilai', 'text', 'Daftar nilai berkode: kode = label.'],
            'nama_kamus'    => ['Nama di kamus', 'code', 'Nama variabel di kamus data penelitian (11), bila berbeda dari kode kolom.'],
            'analitik'      => ['Analitik', 'text', 'Nomor analitik (1–10) yang memakai kolom ini.'],
            'rujukan_kamus' => ['Rujukan kamus', 'text', 'Bagian kamus data penelitian yang mendefinisikan kolom ini.'],
        ],
        'peserta' => [
            'booklet'                  => ['Buklet', 'code', 'Buklet B1–B4 siswa di studi ini, dari baris dan kursi L2 (participant_seats).'],
            'display_name'             => ['Nama siswa', 'text', 'Nama asli (hanya ekspor beridentitas).'],
            'username'                 => ['Nama pengguna', 'text', 'Nama pengguna untuk masuk (hanya ekspor beridentitas).'],
            'school_name'              => ['Nama sekolah', 'text', 'Nama sekolah saat mendaftar (hanya ekspor beridentitas).'],
            'school_npsn'              => ['NPSN sekolah', 'code', 'NPSN sekolah resmi (hanya ekspor beridentitas).'],
            'school_ref'               => ['Kode sekolah (samaran)', 'code', 'Kode samaran sekolah SCH-nnnnnn; tetap ada pada ekspor anonim.'],
            'age'                      => ['Umur (tahun)', 'int', 'Umur saat mendaftar.'],
            'gender'                   => ['Jenis kelamin', 'enum', 'Jenis kelamin.', 'gender'],
            'class_level'              => ['Kelas', 'enum', 'Kelas saat mendaftar.', 'class'],
            'registered_at'            => ['Terdaftar', 'datetime', 'Waktu akun dibuat.'],
            'assented_at'              => ['Setuju ikut', 'datetime', 'Waktu siswa mencentang persetujuan ikut bermain.'],
            'intro_seen_at'            => ['Selesai cerita pembuka', 'datetime', 'Pertama kali menyelesaikan cerita pembuka.'],
            'pw_first_submit_criteria' => ['Syarat sandi terpenuhi pada kiriman pertama', 'int', 'Jumlah syarat kata sandi (0–5) yang terpenuhi pada kiriman pertama formulir pendaftaran.'],
            'pw_weak_submit_count'     => ['Kiriman sandi terlalu lemah', 'int', 'Berapa kali formulir pendaftaran ditolak karena sandi belum kuat.'],
            // Part 8 (34 §11.4; 13 → Penskoran)
            'pre_form' => ['Bentuk tes awal', 'code', 'A atau B (dari baris dan kursi L2).'],
            'post_form' => ['Bentuk tes akhir', 'code', 'A atau B (kebalikan tes awal).'],
            'pre_date' => ['Tanggal tes awal', 'text', 'TTTT-BB-HH.'],
            'post_date' => ['Tanggal tes akhir', 'text', 'TTTT-BB-HH.'],
            'pre_makeup' => ['Tes awal susulan', 'bool', '1 = tes awal di sesi susulan.'],
            'post_makeup' => ['Tes akhir susulan', 'bool', '1 = tes akhir di sesi susulan.'],
            'pre_d1' => ['Skor D1 tes awal', 'int', 'Skor mentah bagian D1 tes awal (0–16).'],
            'pre_d2' => ['Skor D2 tes awal', 'int', 'Skor mentah bagian D2 tes awal (0–16).'],
            'pre_d3' => ['Skor D3 tes awal', 'int', 'Skor mentah bagian D3 tes awal (0–8).'],
            'post_d1' => ['Skor D1 tes akhir', 'int', 'Skor mentah bagian D1 tes akhir (0–16).'],
            'post_d2' => ['Skor D2 tes akhir', 'int', 'Skor mentah bagian D2 tes akhir (0–16).'],
            'post_d3' => ['Skor D3 tes akhir', 'int', 'Skor mentah bagian D3 tes akhir (0–8).'],
            'pre_minutes_p1' => ['Lama Bagian 1 tes awal (menit)', 'text', 'Menit kerja Bagian 1, satu desimal.'],
            'pre_minutes_p23' => ['Lama Bagian 2–3 tes awal (menit)', 'text', 'Menit kerja Bagian 2 dan 3, satu desimal.'],
            'post_minutes_p1' => ['Lama Bagian 1 tes akhir (menit)', 'text', 'Menit kerja Bagian 1, satu desimal.'],
            'post_minutes_p23' => ['Lama Bagian 2–3 tes akhir (menit)', 'text', 'Menit kerja Bagian 2 dan 3, satu desimal.'],
            'pre_capped' => ['Ditutup batas (tes awal)', 'code', 'Blok yang ditutup batas waktu: p1, p23, atau keduanya (p1;p23); kosong bila tidak ada.'],
            'post_capped' => ['Ditutup batas (tes akhir)', 'code', 'Blok yang ditutup batas waktu: p1, p23, atau keduanya (p1;p23); kosong bila tidak ada.'],
        ],
        'sesi_lapangan' => [
            'makeup_no'         => ['Susulan ke-', 'int', '0 = sesi utama; 1, 2 = susulan pertama, kedua.'],
            'main_session_code' => ['Kode sesi utama', 'code', 'Sesi lapangan utama dari sesi susulan ini.'],
            'region_opened'     => ['Isi yang dibuka', 'code', 'Wilayah yang dibuka sesi ini menurut rencana pertemuan studi; dipisah koma.'],
            'session_date'      => ['Tanggal sesi', 'text', 'Tanggal sesi lapangan (YYYY-MM-DD).'],
            'opens_at'          => ['Jam buka', 'datetime', 'Sesi lapangan dibuka.'],
            'closes_at'         => ['Jam tutup', 'datetime', 'Sesi lapangan ditutup menurut jadwal.'],
            'closed_at'         => ['Ditutup peneliti', 'datetime', 'Waktu sesi ditutup lebih awal oleh peneliti; kosong bila tidak.'],
        ],
        'sesi' => [
            'study_code'     => ['Kode penelitian', 'code', 'Kode penelitian (research_studies.code).'],
            'release_code'   => ['Versi permainan', 'code', 'Kode rilis permainan yang aktif saat sesi dibuat.'],
            'status'         => ['Status sesi', 'enum', 'Status sesi bermain.', 'sessionStatus'],
            'started_at'     => ['Mulai', 'datetime', 'Waktu sesi dibuat.'],
            'last_active_at' => ['Terakhir aktif', 'datetime', 'Denyut terakhir dari permainan.'],
            'ended_at'       => ['Berakhir', 'datetime', 'Waktu sesi selesai atau ditinggalkan siswa; kosong bila belum.'],
            'duration_ms'    => ['Durasi aktif (mdtk)', 'ms', 'Akumulasi waktu aktif antar-denyut; jeda panjang tidak dihitung.'],
            'device_type'    => ['Jenis perangkat', 'enum', 'Ringkasan perangkat saat sesi dibuat.', 'device'],
            'screen_size'    => ['Ukuran layar', 'text', 'Lebar × tinggi layar saat sesi dibuat, mis. 1366x768.'],
        ],
        'progres' => [
            'current_level'           => ['Wilayah saat ini', 'enum', 'Wilayah terakhir yang dibuka.', 'level'],
            'current_node_ref'        => ['Tantangan saat ini', 'code', 'Kode tantangan terakhir yang dibuka.'],
            'unlocked_level_sequence' => ['Wilayah terbuka sampai ke-', 'int', 'Urutan wilayah tertinggi yang terbuka.'],
            'completed_nodes'         => ['Tantangan tuntas', 'int', 'Jumlah tantangan tuntas = Serpihan Cahaya (0–12).'],
            'total_nodes'             => ['Jumlah tantangan', 'int', 'Jumlah tantangan aktif saat ekspor.'],
            'completed_levels'        => ['Wilayah tuntas', 'int', 'Wilayah yang keempat tantangannya tuntas.'],
            'total_stars'             => ['Jumlah bintang', 'int', 'Jumlah bintang terbaik per tantangan (0–36).'],
        ],
        'pengerjaan' => [
            'variant_source'      => ['Asal variasi', 'code', 'booklet = dari buklet siswa (pengerjaan pertama dalam mode penelitian), random = acak, retake = pengerjaan ulang.'],
            'booklet_at_open'     => ['Buklet saat dibuka', 'code', 'Buklet siswa (B1–B4) saat pengerjaan dibuka; kosong di luar mode penelitian.'],
            // Part 7 (33 K6)
            'is_retake'           => ['Pengerjaan ulang', 'bool', '1 = siswa sudah pernah menuntaskan tantangan ini saat pengerjaan dibuat. Pengerjaan ulang tetap memberi bintang, tetapi tidak dipakai sebagai pengerjaan pertama.'],
            'attempt_no'          => ['Pengerjaan ke-', 'int', 'Pengerjaan ke-n tantangan ini oleh siswa (lintas sesi sejak bagian 7).'],
            'status'              => ['Status', 'enum', 'Status pengerjaan.', 'attemptStatus'],
            'completion_reason'   => ['Alasan selesai', 'enum', 'Diisi saat pengerjaan selesai.', 'completion'],
            'scorable_items'      => ['Soal dinilai', 'int', 'Jumlah soal yang dinilai.'],
            'first_pass_correct'  => ['Berhasil pada Percobaan Pertama', 'int', 'Jumlah soal dinilai yang benar pada percobaan pertama.'],
            'final_correct'       => ['Benar di akhir', 'int', 'Jumlah soal dinilai yang benar di akhir.'],
            'check_count'         => ['Cek jawaban', 'int', 'Berapa kali "Cek jawabanku" ditekan (Cari Objek: 0).'],
            'max_tries'           => ['Batas percobaan', 'int', 'Batas percobaan per soal untuk pengerjaan ini.'],
            'hint_count'          => ['Petunjuk dibuka', 'int', 'Jumlah petunjuk yang dibuka.'],
            'answer_change_count' => ['Perubahan jawaban', 'int', 'Jumlah perubahan jawaban antarpercobaan, semua soal.'],
            'audio_use_count'     => ['Pemakaian suara', 'int', 'Jumlah event suara yang terkait pengerjaan ini.'],
            'stars'               => ['Bintang', 'int', '0–3, aturan bintang-1.'],
            'duration_ms'         => ['Durasi (mdtk)', 'ms', 'Selesai − mulai; hanya untuk pengerjaan yang selesai.'],
        ],
        'jawaban' => [
            'status'            => ['Status jawaban', 'enum', 'Keadaan akhir soal.', 'responseStatus'],
            'try_count'         => ['Jumlah percobaan', 'int', 'Jumlah percobaan soal ini (= baris Riwayat Percobaan).'],
            'first_answer'      => ['Jawaban pertama', 'json', 'Jawaban percobaan pertama. Berlabel: kalimat; berkode: JSON.'],
            'final_answer'      => ['Jawaban akhir', 'json', 'Jawaban terakhir yang dinilai. Berlabel: kalimat; berkode: JSON.'],
            'first_pass_correct' => ['Berhasil pada Percobaan Pertama', 'bool', '1 = benar pada percobaan pertama.'],
            'is_correct'        => ['Benar di akhir', 'bool', '1 = benar pada keadaan akhir.'],
            'solved_on_try'     => ['Benar pada percobaan ke-', 'int', 'Percobaan ke berapa soal ini benar; kosong = tidak pernah benar.'],
            'change_count'      => ['Perubahan jawaban', 'int', 'Berapa kali jawaban berubah antarpercobaan.'],
            'hint_used'         => ['Memakai petunjuk soal', 'bool', '1 = petunjuk soal ini dibuka.'],
            'wrong_click_count' => ['Salah ketuk benda', 'int', 'Cari Objek: ketukan salah selama petunjuk ini dicari.'],
            'started_at'        => ['Mulai', 'datetime', 'Waktu baris soal dibuat (pengerjaan dibuka).'],
            'duration_ms'       => ['Durasi (mdtk)', 'ms', 'Milidetik sampai masukan terakhir, dari klien.'],
        ],
        'percobaan' => [
            'try_no'             => ['Percobaan ke-', 'int', 'Percobaan ke-n soal ini (1–batas percobaan).'],
            'check_no'           => ['Cek ke-', 'int', 'Nomor "Cek jawabanku" yang menghasilkan percobaan ini; kosong untuk Cari Objek.'],
            'answer'             => ['Jawaban', 'json', 'Jawaban percobaan ini. Berlabel: kalimat; berkode: JSON.'],
            'is_correct'         => ['Benar', 'bool', '1 = percobaan ini benar.'],
            'hint_opened_before' => ['Sudah membuka petunjuk', 'bool', '1 = ada petunjuk yang dibuka di pengerjaan ini sebelum percobaan.'],
            'manual_code'        => ['Kode manual', 'code', 'Kode kesalahan dari pengodean manual wawancara L5, bila kedua pengode sepakat dan berbeda dari kode game.'],
            'ready_ms'           => ['Siap dijawab (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka saat butir bisa dijawab untuk percobaan ini (Benar atau Salah, Cari Objek, Skenario Digital: tindakan bisa dipilih); kosong untuk Isi Rumpang dan Susun Gambar.'],
            'first_input_ms'     => ['Masukan pertama (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai masukan pertama soal ini (klien).'],
            'last_input_ms'      => ['Masukan terakhir (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai masukan terakhir sebelum percobaan (klien). Cari Objek sebelum November 2026: sejak petunjuk tampil.'],
            'occurred_at'        => ['Waktu percobaan', 'datetime', 'Waktu server saat percobaan dinilai.'],
            // Part 6 (32 §10): Skenario Digital only
            'action_key'           => ['Tindakan dipilih', 'code', 'Kode tindakan yang dipilih (Skenario Digital).'],
            'action_score'         => ['Skor tindakan', 'int', 'Skor bank tindakan itu: 2 = aman, 1 = sebagian, 0 = berisiko (Skenario Digital).'],
            'reason_code'          => ['Kode alasan', 'code', 'Kode alasan yang dipilih (Skenario Digital).'],
            'reason_ready_ms'      => ['Alasan siap dipilih (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka saat pilihan alasan tampil (Skenario Digital).'],
            'action_last_input_ms' => ['Masukan terakhir tindakan (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai tindakan terakhir dipilih (Skenario Digital).'],
            'reason_last_input_ms' => ['Masukan terakhir alasan (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai alasan terakhir dipilih (Skenario Digital).'],
        ],
        'navigasi' => [
            'screen'      => ['Layar', 'code', 'Kode layar (payload.screen).'],
            'from_screen' => ['Dari layar', 'code', 'Kode layar sebelumnya (payload.from) untuk screen_viewed.'],
            'event_type'  => ['Jenis event', 'enum', 'Event navigasi: screen_viewed, overlay_opened (payload overlay: kenal, hint, exit, lightbox, statement_image, hunt_zoom), dialogue_completed (slide terakhir sebuah cerita; payload context, total), passage_reopened (bacaan dibuka lagi sesudah masukan pertama; payload via, passage, t_ms), library_opened, library_page_viewed, locale_changed, page_hidden, page_visible, reading_progress.', 'event'],
        ],
        'bantuan' => [
            'jenis'         => ['Jenis bantuan', 'enum', 'petunjuk = petunjuk dibuka (event hint_opened); audio = satu pemutaran rekaman.', 'helpKind'],
            'hint_id'       => ['No. petunjuk', 'int', 'Nomor petunjuk yang dibuka (hints.id).'],
            'asset_key'     => ['Kode rekaman', 'code', 'Kunci berkas suara.'],
            'audio_context' => ['Bagian permainan', 'enum', 'Tempat rekaman diputar.', 'audioContext'],
            'audio_locale'  => ['Bahasa rekaman', 'enum', 'Bahasa rekaman.', 'locale'],
            'action'        => ['Tindakan suara', 'enum', 'Aksi yang membuka pemutaran ini (putar, putar otomatis, ulang).', 'audioAction'],
            'play_index'    => ['Pemutaran ke-', 'int', 'Pemutaran ke-n rekaman itu dalam sesi.'],
            'listened_ms'   => ['Lama didengar (mdtk)', 'ms', 'Lama didengar terbesar yang tercatat untuk pemutaran ini.'],
            'completed'     => ['Didengar sampai habis', 'bool', '1 = pemutaran ini didengar sampai habis.'],
        ],
        'selesai' => [
            'event_type' => ['Jenis event', 'enum', 'Event penyelesaian: challenge_completed, challenge_abandoned, challenge_tries_exhausted, clue_failed, scenario_failed, level_completed, session_completed, intro_completed, feedback_submitted.', 'event'],
        ],
        'event' => [
            'item_key'   => ['Kode soal', 'code', 'Soal yang dirujuk event, bila ada.'],
            'event_type' => ['Jenis event', 'enum', 'Semua event bertanda Peneliti di katalog event (App\\Libraries\\EventCatalog::RESEARCH).', 'event'],
        ],
        'masukan' => [
            'rating'       => ['Bintang (1–5)', 'int', 'Penilaian siswa terhadap permainan.'],
            'liked_most'   => ['Paling disukai', 'text', 'Jawaban "yang paling disukai".'],
            'hardest_part' => ['Paling sulit', 'text', 'Jawaban "bagian yang paling sulit".'],
            'new_learning' => ['Hal baru', 'text', 'Jawaban "hal baru yang dipelajari".'],
            'suggestion'   => ['Saran', 'text', 'Saran siswa.'],
            'submitted_at' => ['Dikirim', 'datetime', 'Waktu kritik & saran dikirim.'],
        ],
        // Part 4 (30 §10.1): codes as stored; 1 = Ya, 0 = Tidak
        'izin' => [
            'parent_consent_on'       => ['Tanggal izin orang tua', 'text', 'Tanggal di surat izin orang tua (L7), TTTT-BB-HH.'],
            'recording_consent'       => ['Izin rekaman suara', 'bool', '1 = orang tua mengizinkan rekaman suara.'],
            'recording_withdrawn_on'  => ['Izin rekaman ditarik', 'text', 'Tanggal izin rekaman ditarik (TTTT-BB-HH), bila ada.'],
            'risk_items_consent'      => ['Izin pertanyaan internet', 'bool', '1 = orang tua mengizinkan pertanyaan pengalaman di internet (ang-21–ang-23).'],
            'risk_items_withdrawn_on' => ['Izin pertanyaan internet ditarik', 'text', 'Tanggal izin pertanyaan internet ditarik (TTTT-BB-HH), bila ada.'],
            'assented_at'             => ['Persetujuan siswa', 'datetime', 'Waktu siswa mencentang persetujuan di layar daftar.'],
            'assent_version'          => ['Versi teks persetujuan', 'code', 'Versi teks yang dicentang siswa, mis. t4-1.'],
            'status'                  => ['Status izin', 'code', 'active, left (pindah atau keluar), withdrawn_keep (mundur, data dipakai), withdrawn_delete (mundur, data dihapus).'],
            'status_on'               => ['Tanggal status', 'text', 'Tanggal status izin terakhir berubah, TTTT-BB-HH.'],
        ],
        'butir' => [
            'sequence'        => ['Urutan', 'int', 'Urutan soal dalam tantangan.'],
            'passage_key'     => ['Kode bacaan', 'code', 'Bacaan Benar atau Salah, mis. tmg-3-a.'],
            'decoy'           => ['Benda pengecoh', 'bool', '1 = benda pengecoh Cari Objek.'],
            'scorable'        => ['Dinilai', 'bool', '0 = tidak dinilai (benda pengecoh).'],
            'is_active'       => ['Aktif', 'bool', '0 = soal diistirahatkan.'],
            'media_asset_key' => ['Kode gambar', 'code', 'Kunci slot gambar soal.'],
            'variant_group'   => ['Variasi', 'code', 'Variasi butir: a, b, x, atau c–k (Susun Gambar).'],
            // Research revision 10, part 5 (31 §13.3; 11 → Variabel butir)
            'round'                 => ['Babak', 'int', 'Cari Objek: 1 = Temukan Benda, 2 = Periksa Unggahan. Kosong untuk tantangan lain.'],
            'booklet_shared'        => ['Dipakai semua buklet', 'bool', '1 = butir x (kartu jangkar, babak 1 Cari Objek).'],
            'dimension'             => ['Dimensi', 'code', 'D1, D2, D3, atau PBK (pendamping).'],
            'subaspect'             => ['Sub-aspek', 'code', 'D1a memahami informasi, D1b menilai informasi dan sumber.'],
            'akm_level'             => ['Level AKM', 'code', 'L1, L2, atau L3; D2 dan D3: padanan dari kerumitan.'],
            'akm_level_source'      => ['Asal label AKM', 'code', 'developer = label pengembang, expert = hasil pelabelan buta.'],
            'pirls_process'         => ['Proses PIRLS', 'code', 'retrieve, infer, integrate, atau evaluate (butir D1).'],
            'bloom_level_source'    => ['Asal label Bloom', 'code', 'developer = label pengembang, expert = hasil pelabelan buta.'],
            'text_genre'            => ['Genre teks', 'code', 'Genre bacaan digital menurut kisi-kisi 06.'],
            'testlet_key'           => ['Kunci testlet', 'code', 'Butir yang berbagi bacaan, kartu, atau adegan.'],
            'd2_topic'              => ['Topik D2', 'code', 'PRI, SAN, IZN, atau ASG (Skenario Digital).'],
            'd3_clue_type'          => ['Jenis petunjuk D3', 'code', 'ATR, REP, IZN, atau ETK (Periksa Unggahan).'],
            'gap_count'             => ['Jumlah rumpang', 'int', 'Rumpang di bacaan butir itu (Isi Rumpang).'],
            'distractor_word_count' => ['Jumlah kata pengecoh', 'int', 'Kata pengecoh di bank kata (Isi Rumpang).'],
            'passage_words'         => ['Jumlah kata bacaan', 'int', 'Kata di bacaan atau kartu butir itu; emoji tidak dihitung.'],
            'decoy_count'           => ['Jumlah pengecoh', 'int', 'Benda pengecoh (babak 1) atau bagian selain kunci (babak 2).'],
            'option_count'          => ['Jumlah pilihan', 'int', 'Tindakan (skenario) atau area ketuk (Cari Objek).'],
            'reason_option_count'   => ['Jumlah alasan', 'int', 'Pilihan alasan (skenario).'],
            'puzzle_grid'           => ['Ukuran papan', 'int', 'n untuk papan n × n (Susun Gambar).'],
            'tap_min_dp'            => ['Jarak ketuk terkecil (dp)', 'int', 'Jarak terkecil antara kotak kunci dan kotak lain di adegan itu.'],
            'review_status'         => ['Status pemeriksaan', 'code', 'draft, needs_verification, atau verified.'],
            'reference_source'      => ['Sumber rujukan', 'text', 'Asal fakta butir.'],
        ],
        // Part 8 (34 §11.4): the test module's sheets and columns; `-` = left empty
        'tes_jawaban' => [
            'occasion'           => ['Kesempatan', 'code', 'pre = tes awal (P1), post = tes akhir (P5).'],
            'form'               => ['Bentuk tes', 'code', 'A atau B.'],
            'item_code'          => ['Kode butir tes', 'code', 'tes-{bagian}-{bentuk}{nn}, mis. tes-mb-A07; contoh soal tidak ikut.'],
            'response'           => ['Jawaban mentah', 'code', 'Huruf (PG, D2, D3), pola tiga huruf (PGK, mis. BSB), atau pasangan (menjodohkan, mis. 1C 2A 3D); - = tidak dijawab.'],
            'score'              => ['Skor', 'int', 'Dari kunci: 0–1 (D2: 0–2); kosong bila tidak sampai.'],
            'error_codes'        => ['Kode kesalahan', 'code', 'Dari kunci; PGK dan menjodohkan satu kode per pernyataan atau pasangan salah, dipisah titik koma.'],
            'not_reached'        => ['Tidak sampai', 'bool', '1 = bagian ditutup batas waktu sebelum butir ini pernah tampil.'],
            'time_on_item'       => ['Waktu tampil (mdtk)', 'ms', 'Jumlah waktu butir tampil (jam server), dibatasi batas waktu blok.'],
            'answer_changes'     => ['Ganti jawaban', 'int', 'Berapa kali jawaban yang sudah tersimpan diganti.'],
            'instrument_version' => ['Versi isi', 'code', 'Versi berkas isi instrumen, mis. tes-0.1.'],
        ],
        'tes_bagian' => [
            'occasion'        => ['Kesempatan', 'code', 'pre = tes awal (P1), post = tes akhir (P5).'],
            'part'            => ['Bagian', 'code', 'mb = Bagian 1 Membaca, ae = Bagian 2 Aman dan Etis, bd = Bagian 3 Budaya Digital.'],
            'block'           => ['Blok waktu', 'code', 'p1 = Bagian 1; p23 = Bagian 2 dan 3 bersama.'],
            'started_at'      => ['Mulai', 'datetime', 'Waktu bagian dibuka.'],
            'submitted_at'    => ['Dikirim', 'datetime', 'Waktu siswa mengirim bagian; kosong bila ditutup batas waktu.'],
            'closed_by_limit' => ['Ditutup batas waktu', 'bool', '1 = bagian ditutup otomatis oleh batas waktu tersembunyi.'],
            'extra_minutes'   => ['Menit tambahan', 'int', 'Menit yang ditambahkan peneliti untuk blok ini (perangkat bermasalah).'],
            'raw_score'       => ['Skor mentah', 'int', 'Jumlah skor bagian (D1 0–16, D2 0–16, D3 0–8).'],
            'answered_count'  => ['Butir dijawab', 'int', 'Banyak butir yang punya jawaban.'],
            'is_makeup'       => ['Susulan', 'bool', '1 = dikerjakan di sesi susulan.'],
        ],
        'angket' => [
            'questionnaire_date'    => ['Tanggal angket', 'text', 'Tanggal mulai mengisi angket, TTTT-BB-HH.'],
            'questionnaire_makeup'  => ['Angket susulan', 'bool', '1 = diisi di sesi susulan.'],
            'questionnaire_late'    => ['Angket terlambat', 'bool', '1 = diisi setelah siswa mulai bermain (questionnaire_late).'],
            'questionnaire_minutes' => ['Lama mengisi (menit)', 'text', 'Menit dari mulai sampai Selesai, satu desimal; kosong bila belum dikirim.'],
            'phone_access' => ['ang-01 punya HP sendiri', 'code', 'Angket ang-01 punya HP sendiri (2 ya, 1 memakai HP orang lain, 0 tidak); - = dilewati.'],
            'home_phone' => ['ang-02 HP di rumah', 'code', 'Angket ang-02 HP di rumah (1/0); - = dilewati.'],
            'home_tablet' => ['ang-02 tablet', 'code', 'Angket ang-02 tablet (1/0); - = dilewati.'],
            'home_computer' => ['ang-02 laptop atau komputer', 'code', 'Angket ang-02 laptop atau komputer (1/0); - = dilewati.'],
            'home_console' => ['ang-02 konsol game', 'code', 'Angket ang-02 konsol game (1/0); - = dilewati.'],
            'home_device_none' => ['ang-02 tidak ada satu pun', 'code', 'Angket ang-02 tidak ada satu pun (1/0); - = dilewati.'],
            'home_internet' => ['ang-03 bisa memakai internet di rumah', 'code', 'Angket ang-03 bisa memakai internet di rumah (3 hampir selalu … 0 tidak pernah, 9 tidak tahu); - = dilewati.'],
            'first_use_grade' => ['ang-04 mulai memakai HP atau tablet', 'code', 'Angket ang-04 mulai memakai HP atau tablet (4 sebelum SD … 1 kelas 5, 0 belum pernah, 9 tidak ingat); - = dilewati.'],
            'screen_time_school' => ['ang-05 lama memakai layar, hari sekolah', 'code', 'Angket ang-05 lama memakai layar, hari sekolah (0–4, 9); - = dilewati.'],
            'screen_time_holiday' => ['ang-05 lama memakai layar, hari libur', 'code', 'Angket ang-05 lama memakai layar, hari libur (0–4, 9); - = dilewati.'],
            'game_time_school' => ['ang-06 lama main game, hari sekolah', 'code', 'Angket ang-06 lama main game, hari sekolah (0–4, 9); - = dilewati.'],
            'game_time_holiday' => ['ang-06 lama main game, hari libur', 'code', 'Angket ang-06 lama main game, hari libur (0–4, 9); - = dilewati.'],
            'act_video' => ['ang-07a menonton video', 'code', 'Angket ang-07a menonton video (2, 1, 0, 9); - = dilewati.'],
            'act_game' => ['ang-07b bermain game', 'code', 'Angket ang-07b bermain game (2, 1, 0, 9); - = dilewati.'],
            'act_chat' => ['ang-07c pesan atau telepon', 'code', 'Angket ang-07c pesan atau telepon (2, 1, 0, 9); - = dilewati.'],
            'act_schoolwork' => ['ang-07d tugas sekolah', 'code', 'Angket ang-07d tugas sekolah (2, 1, 0, 9); - = dilewati.'],
            'act_search' => ['ang-07e mencari tahu', 'code', 'Angket ang-07e mencari tahu (2, 1, 0, 9); - = dilewati.'],
            'act_read' => ['ang-07f membaca', 'code', 'Angket ang-07f membaca (2, 1, 0, 9); - = dilewati.'],
            'act_post' => ['ang-07g membuat dan mengunggah', 'code', 'Angket ang-07g membuat dan mengunggah (2, 1, 0, 9); - = dilewati.'],
            'acct_whatsapp' => ['ang-08a WhatsApp', 'code', 'Angket ang-08a WhatsApp (0 tidak memakai, 1 akun orang lain, 2 akun sendiri, 9); - = dilewati.'],
            'acct_youtube' => ['ang-08b YouTube', 'code', 'Angket ang-08b YouTube (0, 1, 2, 9); - = dilewati.'],
            'acct_tiktok' => ['ang-08c TikTok', 'code', 'Angket ang-08c TikTok (0, 1, 2, 9); - = dilewati.'],
            'acct_instagram' => ['ang-08d Instagram', 'code', 'Angket ang-08d Instagram (0, 1, 2, 9); - = dilewati.'],
            'acct_facebook' => ['ang-08e Facebook', 'code', 'Angket ang-08e Facebook (0, 1, 2, 9); - = dilewati.'],
            'acct_game' => ['ang-08f game online berakun', 'code', 'Angket ang-08f game online berakun (0, 1, 2, 9); - = dilewati.'],
            'parent_couse' => ['ang-09 ditemani orang tua', 'code', 'Angket ang-09 ditemani orang tua (3 … 0, 8 tidak memakai di rumah, 9); - = dilewati.'],
            'parent_talk' => ['ang-10 mengobrol dengan orang tua', 'code', 'Angket ang-10 mengobrol dengan orang tua (3 … 0, 8, 9); - = dilewati.'],
            'parent_rule_time' => ['ang-11a aturan lama memakai', 'code', 'Angket ang-11a aturan lama memakai (1 ada, 0 tidak, 9); - = dilewati.'],
            'parent_rule_content' => ['ang-11b aturan isi yang dibuka', 'code', 'Angket ang-11b aturan isi yang dibuka (1, 0, 9); - = dilewati.'],
            'safety_info_parent' => ['ang-12 orang tua', 'code', 'Angket ang-12 orang tua (1/0); - = dilewati.'],
            'safety_info_teacher' => ['ang-12 guru', 'code', 'Angket ang-12 guru (1/0); - = dilewati.'],
            'safety_info_family' => ['ang-12 kakak atau saudara', 'code', 'Angket ang-12 kakak atau saudara (1/0); - = dilewati.'],
            'safety_info_friend' => ['ang-12 teman', 'code', 'Angket ang-12 teman (1/0); - = dilewati.'],
            'safety_info_media' => ['ang-12 video, TV, atau internet', 'code', 'Angket ang-12 video, TV, atau internet (1/0); - = dilewati.'],
            'safety_info_none' => ['ang-12 belum ada yang memberi tahu', 'code', 'Angket ang-12 belum ada yang memberi tahu (1/0); - = dilewati.'],
            'safety_info_dk' => ['ang-12 tidak ingat', 'code', 'Angket ang-12 tidak ingat (1/0); - = dilewati.'],
            'school_device_use' => ['ang-13 memakai alat di sekolah', 'code', 'Angket ang-13 memakai alat di sekolah (2, 1, 0, 9); - = dilewati.'],
            'digital_quiz_exp' => ['ang-14 kuis di layar', 'code', 'Angket ang-14 kuis di layar (2, 1, 0, 9); - = dilewati.'],
            'read_medium_pref' => ['ang-15 lebih mudah membaca di', 'code', 'Angket ang-15 lebih mudah membaca di (1 kertas, 2 sama, 3 layar, 9); - = dilewati.'],
            'home_indonesian' => ['ang-16 bahasa Indonesia di rumah', 'code', 'Angket ang-16 bahasa Indonesia di rumah (3 … 0); - = dilewati.'],
            'books_home' => ['ang-17 buku di rumah', 'code', 'Angket ang-17 buku di rumah (1–5, 9); - = dilewati.'],
        ],
        'sikap' => [
            'skp_ct_01' => ['Contoh skala sikap', 'code', 'Jawaban contoh skp-ct-01 (tidak dianalisis): 1–4 atau -.'],
            'skp_01' => ['skp-01', 'code', 'Jawaban mentah skp-01: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_02' => ['skp-02', 'code', 'Jawaban mentah skp-02: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_03' => ['skp-03', 'code', 'Jawaban mentah skp-03: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_04' => ['skp-04', 'code', 'Jawaban mentah skp-04: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_05' => ['skp-05', 'code', 'Jawaban mentah skp-05: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_06' => ['skp-06', 'code', 'Jawaban mentah skp-06: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_07' => ['skp-07', 'code', 'Jawaban mentah skp-07: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_08' => ['skp-08', 'code', 'Jawaban mentah skp-08: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_09' => ['skp-09', 'code', 'Jawaban mentah skp-09: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_10' => ['skp-10', 'code', 'Jawaban mentah skp-10: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_11' => ['skp-11', 'code', 'Jawaban mentah skp-11: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_12' => ['skp-12', 'code', 'Jawaban mentah skp-12: 1 tidak setuju, 2 kurang setuju, 3 setuju, 4 sangat setuju; - = dilewati. Tidak dibalik.'],
            'skp_01_ms' => ['skp-01 waktu (mdtk)', 'ms', 'Lama pernyataan skp-01 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_02_ms' => ['skp-02 waktu (mdtk)', 'ms', 'Lama pernyataan skp-02 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_03_ms' => ['skp-03 waktu (mdtk)', 'ms', 'Lama pernyataan skp-03 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_04_ms' => ['skp-04 waktu (mdtk)', 'ms', 'Lama pernyataan skp-04 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_05_ms' => ['skp-05 waktu (mdtk)', 'ms', 'Lama pernyataan skp-05 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_06_ms' => ['skp-06 waktu (mdtk)', 'ms', 'Lama pernyataan skp-06 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_07_ms' => ['skp-07 waktu (mdtk)', 'ms', 'Lama pernyataan skp-07 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_08_ms' => ['skp-08 waktu (mdtk)', 'ms', 'Lama pernyataan skp-08 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_09_ms' => ['skp-09 waktu (mdtk)', 'ms', 'Lama pernyataan skp-09 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_10_ms' => ['skp-10 waktu (mdtk)', 'ms', 'Lama pernyataan skp-10 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_11_ms' => ['skp-11 waktu (mdtk)', 'ms', 'Lama pernyataan skp-11 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_12_ms' => ['skp-12 waktu (mdtk)', 'ms', 'Lama pernyataan skp-12 tampil sampai dijawab, waktu terakhir bila siswa kembali.'],
            'skp_changes'        => ['Ganti jawaban skala', 'int', 'Berapa kali siswa mengganti jawaban sebelum mengirim.'],
            'sikap_makeup'       => ['Skala susulan', 'bool', '1 = diisi di sesi susulan P5.'],
            'sikap_submitted_at' => ['Skala dikirim', 'datetime', 'Waktu skala sikap dikirim.'],
            'sikap_version'      => ['Versi skala', 'code', 'Versi isi skala, mis. sikap-0.1.'],
            'feedback_rating'    => ['Bintang kritik dan saran', 'int', 'Bintang 1–5 di kritik dan saran; kosong bila dilewati.'],
        ],
        'risiko_gabungan' => [
            'school_code' => ['Cakupan', 'code', 'semua = seluruh studi, atau kode sekolah penelitian.'],
            'item_code'   => ['Kode butir', 'code', 'ang-21, ang-22, ang-23.'],
            'unit'        => ['Variabel', 'code', 'risk_upset, risk_stranger_contact, support_*.'],
            'choice'      => ['Pilihan', 'code', 'Kode jawaban (7 = tidak mau menjawab); - = kosong; tidak_mau_mengisi = memilih Tidak di T11; tidak_diberi = tanpa izin butir ini.'],
            'students'    => ['Banyak siswa', 'text', 'Banyak siswa; kurang dari 5 ditulis <5. Tidak pernah per siswa.'],
        ],
        'kode_pilihan' => [
            'choice_type' => ['Jenis pilihan', 'code', 'word (kata Isi Rumpang), object (benda Cari Objek), part (bagian unggahan), action atau reason (Skenario Digital).'],
            'choice_ref'  => ['Pilihan', 'code', 'Kata, kode benda, kode bagian (A1, C10, …), atau kode pilihan skenario.'],
            'error_code'  => ['Kode kesalahan', 'code', 'Kode kesalahan bank bila pilihan ini dipilih (tindakan skenario: kode tindakan).'],
            'is_main'     => ['Kode utama', 'bool', '1 = kode utama butir itu.'],
            'option_score' => ['Skor tindakan', 'int', 'Skor tindakan Skenario Digital (0–2); kosong untuk pilihan lain.'],
        ],
    ];

    /** Nama lembar (XLSX, tab tabel) dan artinya untuk Kamus Data. */
    private const SHEET_TITLES = [
        'kamus'      => ['Kamus Data', 'Arti setiap lembar dan kolom, satuan, daftar nilai berkode, dan rujukannya di kamus data penelitian.'],
        'peserta'    => ['Peserta', 'Satu baris per siswa.'],
        'sesi_lapangan' => ['Sesi Lapangan', 'Satu baris per sesi lapangan (tanpa riwayat token).'],
        'sesi'       => ['Sesi', 'Satu baris per sesi bermain.'],
        'progres'    => ['Progres', 'Kemajuan satu siswa di satu studi.'],
        'pengerjaan' => ['Pengerjaan Tantangan', 'Satu baris per pengerjaan tantangan.'],
        'jawaban'    => ['Jawaban Soal', 'Keadaan akhir satu soal dalam satu pengerjaan.'],
        'percobaan'  => ['Riwayat Percobaan', 'Satu baris per percobaan satu soal.'],
        'navigasi'   => ['Navigasi', 'Event perpindahan layar dan membaca.'],
        'bantuan'    => ['Bantuan', 'Petunjuk yang dibuka dan setiap pemutaran rekaman.'],
        'selesai'    => ['Penyelesaian', 'Event selesai, ditinggalkan, dan kesempatan habis.'],
        'event'      => ['Event Mentah', 'Semua event bertanda Peneliti, dengan kode sebagai pengganti nomor internal (hanya ketua peneliti).'],
        'masukan'    => ['Kritik & Saran', 'Satu baris per kiriman kritik dan saran.'],
        'butir'      => ['Bank Soal', 'Satu baris per soal di bank soal.'],
        'kode_pilihan' => ['Kode Pilihan', 'Kode kesalahan bank per pilihan dan pilihan skenario (hanya ketua peneliti).'],
        // Part 8 (34 §11.4; 29 §7.2, §7.6)
        'tes_jawaban' => ['Jawaban Tes', 'Satu baris per butir per siswa per tes (pre/post), tanpa contoh soal.'],
        'tes_bagian'  => ['Bagian Tes', 'Satu baris per bagian tes per siswa per kesempatan.'],
        'angket'      => ['Angket', 'Satu baris per siswa: angket akses digital ang-01–ang-17 (41 kolom).'],
        'sikap'       => ['Skala Sikap', 'Satu baris per siswa: skala sikap skp-01–skp-12 dan bintang kritik dan saran.'],
        'risiko_gabungan' => ['Risiko Gabungan', 'Angka gabungan butir risiko daring per butir dan pilihan; sel di bawah 5 siswa ditulis <5. Tidak pernah per siswa.'],
        'izin'       => ['Izin Berkode', 'Izin orang tua dan persetujuan siswa per siswa, hanya kode (hanya ketua peneliti).'],
        // Field sheets: titles from FieldSheetSpec (sheetTitle())
    ];

    /** Nilai berlabel kelompok yang tidak ada di Language/id/Admin.php. */
    private const EXTRA_VALUES = [
        'sessionStatus'  => ['active', 'paused', 'completed', 'abandoned'],
        'attemptStatus'  => ['in_progress', 'completed', 'abandoned'],
        'responseStatus' => ['pending', 'answered', 'skipped'],
        'helpKind'       => ['petunjuk' => 'Petunjuk dibuka', 'audio' => 'Pemutaran suara'],
        'level'          => ['temanggung' => 'Temanggung', 'magelang' => 'Magelang', 'wonosobo' => 'Wonosobo'],
    ];

    /** @return list<string> */
    public static function sheets(): array
    {
        return array_keys(self::SHEET_TITLES);
    }

    public static function sheetTitle(string $sheet): string
    {
        if (! isset(self::SHEET_TITLES[$sheet]) && FieldSheetSpec::has($sheet)) {
            return FieldSheetSpec::title($sheet);
        }

        return self::SHEET_TITLES[$sheet][0] ?? $sheet;
    }

    public static function sheetMeaning(string $sheet): string
    {
        if (! isset(self::SHEET_TITLES[$sheet]) && FieldSheetSpec::has($sheet)) {
            return 'Lembar lapangan yang diketik dan diunggah (impor utama yang berlaku); kolom seperti 02_LEMBAR_LAPANGAN.md.';
        }

        return self::SHEET_TITLES[$sheet][1] ?? '';
    }

    /** Apakah kolom ini punya keterangan lengkap. Dipakai uji. */
    public static function has(string $sheet, string $column): bool
    {
        return isset(self::SHEETS[$sheet][$column]) || isset(self::COMMON[$column]) || self::fieldColumn($sheet, $column) !== null;
    }

    /**
     * A field-sheet column (29 §10.7), its wording from FieldSheetSpec.
     *
     * @return array{0: string, 1: string, 2: string}|null
     */
    private static function fieldColumn(string $sheet, string $column): ?array
    {
        if (! FieldSheetSpec::has($sheet) || isset(self::COMMON[$column])) {
            return null;
        }

        $spec = FieldSheetSpec::columns($sheet)[$column] ?? null;

        if ($spec === null) {
            return null;
        }

        $type = match ($spec['type']) {
            'int', 'try'      => 'int',
            'yesno'           => 'text',
            'participant', 'session', 'item', 'manual', 'code' => 'code',
            default           => 'text',
        };

        return [$spec['label'], $type, 'Isian lembar ' . FieldSheetSpec::title($sheet) . ': ' . $spec['label'] . ($spec['list'] ? ' (kode: ' . implode(', ', FieldSheetSpec::CODES[$spec['list']]) . ')' : '') . '.'];
    }

    /**
     * Keterangan satu kolom.
     *
     * @return array{key: string, label: string, type: string, meaning: string, unit: string, group: ?string}
     */
    public static function column(string $sheet, string $column): array
    {
        $spec = self::SHEETS[$sheet][$column] ?? self::fieldColumn($sheet, $column) ?? self::COMMON[$column] ?? [$column, 'text', ''];

        return [
            'key'     => $column,
            'label'   => $spec[0],
            'type'    => $spec[1],
            'meaning' => $spec[2],
            'unit'    => match ($spec[1]) {
                'ms'       => self::UNIT_MS,
                'datetime' => self::UNIT_TIME,
                default    => '',
            },
            'group' => $spec[3] ?? null,
        ];
    }

    /**
     * Judul kolom: kode kolom (berkode) atau judul Indonesia (berlabel).
     *
     * @param list<string> $columns
     *
     * @return list<string>
     */
    public static function headers(string $sheet, array $columns, bool $labeled): array
    {
        return $labeled
            ? array_map(static fn (string $c): string => self::column($sheet, $c)['label'], $columns)
            : $columns;
    }

    /**
     * Lebar kolom XLSX (karakter), cukup untuk judul atau isi khasnya.
     *
     * @param list<string> $columns
     *
     * @return list<int>
     */
    public static function widths(string $sheet, array $columns, bool $labeled): array
    {
        return array_map(static function (string $column) use ($sheet, $labeled): int {
            $spec  = self::column($sheet, $column);
            $title = mb_strlen($labeled ? $spec['label'] : $column) + 3;

            return match ($spec['type']) {
                'datetime' => max(31, $title),
                'json'     => max(40, min(60, $title)),
                'text'     => max(16, min(48, $title)),
                default    => max(10, min(40, $title)),
            };
        }, $columns);
    }

    /**
     * Daftar kode → label satu kelompok nilai (kosong bila tidak berkode).
     *
     * @return array<string, string>
     */
    public static function values(?string $group): array
    {
        if ($group === null) {
            return [];
        }

        $extra = self::EXTRA_VALUES[$group] ?? null;

        if ($extra !== null) {
            if (array_is_list($extra)) {
                return array_combine($extra, array_map(static fn (string $c): string => admin_label('status', $c), $extra));
            }

            return $extra;
        }

        return match ($group) {
            'class'        => array_combine(['5'], [class_label('5', 'id')]),
            'engine'       => array_combine(
                config('Gelita')->engineTypes,
                array_map(static fn (string $c): string => lang('Game.engine_' . $c, [], 'id'), config('Gelita')->engineTypes),
            ),
            'skill'        => DifficultyRules::SKILLS,
            'audioContext' => NarrationCatalog::CONTEXT_LABELS,
            default        => (array) lang('Admin.' . $group, [], 'id'),
        };
    }

    /**
     * Nilai berlabel untuk kolom berjenis `bool` dan `enum`; jenis lain dan
     * kolom wilayah/jawaban dikembalikan apa adanya (dilabeli RawDataService).
     */
    public static function label(string $sheet, string $column, mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $spec = self::column($sheet, $column);

        if ($spec['type'] === 'bool') {
            return (int) $value === 1 ? 'ya' : 'tidak';
        }

        if ($spec['type'] !== 'enum' || $spec['group'] === null || $spec['group'] === 'level') {
            return $value;
        }

        if ($spec['group'] === 'class') {
            return class_label((string) $value, 'id');
        }

        return self::values($spec['group'])[(string) $value] ?? $value;
    }

    /**
     * Baris lembar Kamus Data untuk lembar-lembar yang diekspor, with the
     * research dictionary's name, analytics, and anchor in 11 per column
     * (ResearchDictionary, 29 §8). `$head` rows come first (file header).
     *
     * @param array<string, list<string>> $columnsBySheet kode lembar → kolom
     * @param list<array{0: string, 1: string}> $head label → value
     *
     * @return list<array<string, string>>
     */
    public static function dictionary(array $columnsBySheet, array $head = []): array
    {
        $rows = [];

        foreach ($head as [$label, $value]) {
            $rows[] = ['lembar' => $label, 'kolom' => '', 'judul' => $value, 'arti' => '', 'satuan' => '', 'nilai' => '', 'nama_kamus' => '', 'analitik' => '', 'rujukan_kamus' => ''];
        }

        foreach ($columnsBySheet as $sheet => $columns) {
            $rows[] = [
                'lembar' => $sheet,
                'kolom'  => '',
                'judul'  => self::sheetTitle($sheet),
                'arti'   => self::sheetMeaning($sheet),
                'satuan' => '',
                'nilai'  => '',
                'nama_kamus'    => '',
                'analitik'      => '',
                'rujukan_kamus' => '',
            ];

            foreach ($columns as $column) {
                $spec   = self::column($sheet, $column);
                $values = self::values($spec['group']);

                if ($spec['type'] === 'bool') {
                    $values = ['1' => 'ya', '0' => 'tidak'];
                }

                $entry  = ResearchDictionary::entry($sheet, $column);
                $rows[] = [
                    'lembar' => $sheet,
                    'kolom'  => $column,
                    'judul'  => $spec['label'],
                    'arti'   => $spec['meaning'],
                    'satuan' => $spec['unit'],
                    'nilai'  => implode('; ', array_map(
                        static fn (string|int $code, string $label): string => $code . ' = ' . $label,
                        array_keys($values),
                        $values,
                    )),
                    'nama_kamus'    => $entry === null ? '' : (string) $entry['kamus'],
                    'analitik'      => $entry === null ? '' : implode(', ', $entry['analytics']),
                    'rujukan_kamus' => $entry === null ? '' : ResearchDictionary::DOC . '#' . $entry['ref'],
                ];
            }
        }

        return $rows;
    }
}
