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
            'attempt_no'          => ['Pengerjaan ke-', 'int', 'Pengerjaan ke-n tantangan ini dalam sesi.'],
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
            'ready_ms'           => ['Siap dijawab (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka saat butir bisa dijawab untuk percobaan ini (Benar atau Salah, Cari Objek); kosong untuk Isi Rumpang dan Susun Gambar.'],
            'first_input_ms'     => ['Masukan pertama (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai masukan pertama soal ini (klien).'],
            'last_input_ms'      => ['Masukan terakhir (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai masukan terakhir sebelum percobaan (klien). Cari Objek sebelum November 2026: sejak petunjuk tampil.'],
            'occurred_at'        => ['Waktu percobaan', 'datetime', 'Waktu server saat percobaan dinilai.'],
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
            'event_type' => ['Jenis event', 'enum', 'Event penyelesaian: challenge_completed, challenge_abandoned, challenge_tries_exhausted, clue_failed, level_completed, session_completed, intro_completed, feedback_submitted.', 'event'],
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
