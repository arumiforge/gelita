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
        'variant_group'      => ['Variasi', 'code', 'Variasi soal yang dipakai (a, b, atau c).'],
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
        'answer_key'         => ['Kunci jawaban', 'json', 'Kunci jawaban soal (hanya admin). Berlabel: kalimat; berkode: JSON.'],
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
        ],
        'peserta' => [
            'display_name'             => ['Nama siswa', 'text', 'Nama asli (hanya ekspor beridentitas).'],
            'username'                 => ['Nama pengguna', 'text', 'Nama pengguna untuk masuk (hanya ekspor beridentitas).'],
            'school_name'              => ['Nama sekolah', 'text', 'Nama sekolah saat mendaftar (hanya ekspor beridentitas).'],
            'school_npsn'              => ['NPSN sekolah', 'code', 'NPSN sekolah resmi (hanya ekspor beridentitas).'],
            'school_ref'               => ['Kode sekolah (samaran)', 'code', 'Kode samaran sekolah SCH-nnnnnn; tetap ada pada ekspor anonim.'],
            'age'                      => ['Umur (tahun)', 'int', 'Umur saat mendaftar.'],
            'gender'                   => ['Jenis kelamin', 'enum', 'Jenis kelamin.', 'gender'],
            'class_level'              => ['Kelas', 'enum', 'Kelas saat mendaftar.', 'class'],
            'country'                  => ['Negara', 'text', 'Negara saat mendaftar.'],
            'province'                 => ['Provinsi', 'text', 'Provinsi saat mendaftar.'],
            'district'                 => ['Kabupaten/kota', 'text', 'Kabupaten/kota saat mendaftar.'],
            'registered_at'            => ['Terdaftar', 'datetime', 'Waktu akun dibuat.'],
            'assented_at'              => ['Setuju ikut', 'datetime', 'Waktu siswa mencentang persetujuan ikut bermain.'],
            'intro_seen_at'            => ['Selesai cerita pembuka', 'datetime', 'Pertama kali menyelesaikan cerita pembuka.'],
            'pw_first_submit_criteria' => ['Syarat sandi terpenuhi pada kiriman pertama', 'int', 'Jumlah syarat kata sandi (0–5) yang terpenuhi pada kiriman pertama formulir pendaftaran.'],
            'pw_weak_submit_count'     => ['Kiriman sandi terlalu lemah', 'int', 'Berapa kali formulir pendaftaran ditolak karena sandi belum kuat.'],
        ],
        'sesi' => [
            'study_code'     => ['Kode penelitian', 'code', 'Kode penelitian (research_studies.code).'],
            'release_code'   => ['Versi permainan', 'code', 'Kode rilis permainan yang aktif saat sesi dibuat.'],
            'app_client_version' => ['Versi aplikasi & paket', 'code', 'Versi aplikasi + kode paket aset yang dipakai perangkat saat sesi dibuat, mis. 2.0.0+pkt-2026-10-20-01; tanpa "+…" bila perangkat tidak memakai paket.'],
            'package_code'   => ['Kode paket aset', 'code', 'Paket aset (gambar dan suara) di perangkat saat sesi dibuat; kosong bila tanpa paket.'],
            'package_mode'   => ['Mode paket', 'enum', 'Cara perangkat memuat gambar dan suara selama sesi.', 'packageMode'],
            'status'         => ['Status sesi', 'enum', 'Status sesi bermain.', 'sessionStatus'],
            'started_at'     => ['Mulai', 'datetime', 'Waktu sesi dibuat.'],
            'last_active_at' => ['Terakhir aktif', 'datetime', 'Denyut terakhir dari permainan.'],
            'ended_at'       => ['Berakhir', 'datetime', 'Waktu sesi selesai atau ditinggalkan siswa; kosong bila belum.'],
            'duration_ms'    => ['Durasi aktif (mdtk)', 'ms', 'Akumulasi waktu aktif antar-denyut; jeda panjang tidak dihitung.'],
            'device_type'    => ['Jenis perangkat', 'enum', 'Ringkasan perangkat saat sesi dibuat.', 'device'],
            'screen_size'    => ['Ukuran layar', 'text', 'Lebar × tinggi layar saat sesi dibuat, mis. 1366x768.'],
            'is_touch'       => ['Layar sentuh', 'bool', '1 = layar sentuh.'],
        ],
        'perangkat' => [
            'via'             => ['Asal catatan', 'enum', 'Saat perangkat dicatat.', 'deviceVia'],
            'created_at'      => ['Dicatat', 'datetime', 'Waktu catatan perangkat dibuat.'],
            'device_brand'    => ['Merek perangkat', 'text', 'Merek perangkat.'],
            'device_model'    => ['Model perangkat', 'text', 'Model perangkat.'],
            'os_version'      => ['Versi sistem operasi', 'text', 'Versi sistem operasi.'],
            'os_platform'     => ['Platform sistem operasi', 'text', 'Arsitektur, mis. x64, ARM.'],
            'browser_version' => ['Versi peramban', 'text', 'Versi peramban.'],
            'browser_engine'  => ['Mesin peramban', 'text', 'Mesin peramban, mis. Blink, WebKit.'],
            'is_bot'          => ['Bot', 'bool', '1 = dikenali sebagai bot.'],
            'screen_w'        => ['Lebar layar (px)', 'int', 'Lebar layar dalam piksel CSS.'],
            'screen_h'        => ['Tinggi layar (px)', 'int', 'Tinggi layar dalam piksel CSS.'],
            'viewport_w'      => ['Lebar jendela (px)', 'int', 'Lebar jendela peramban dalam piksel CSS.'],
            'viewport_h'      => ['Tinggi jendela (px)', 'int', 'Tinggi jendela peramban dalam piksel CSS.'],
            'pixel_ratio'     => ['Rasio piksel', 'text', 'devicePixelRatio, mis. 2.00.'],
            'touch_points'    => ['Titik sentuh', 'int', 'Jumlah titik sentuh yang didukung.'],
            'orientation'     => ['Orientasi', 'text', 'Orientasi layar, mis. landscape-primary.'],
            'is_standalone'   => ['Aplikasi terpasang', 'bool', '1 = dibuka sebagai aplikasi terpasang.'],
            'language'        => ['Bahasa peramban', 'text', 'Bahasa peramban, mis. id-ID.'],
            'timezone'        => ['Zona waktu', 'text', 'Zona waktu perangkat.'],
            'corrected'       => ['Koreksi', 'code', 'Koreksi pengurai yang diterapkan, mis. ipad_desktop_ua.'],
            'detector'        => ['Pengurai', 'text', 'Nama dan versi pengurai perangkat.'],
            'user_agent'      => ['User-Agent', 'text', 'Teks User-Agent apa adanya.'],
            'client_hints'    => ['Client Hints (JSON)', 'json', 'Client Hints mentah: {headers, ua_data}.'],
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
            'first_input_ms'     => ['Masukan pertama (mdtk)', 'ms', 'Milidetik sejak tantangan dibuka sampai masukan pertama soal ini (klien).'],
            'last_input_ms'      => ['Masukan terakhir (mdtk)', 'ms', 'Milidetik sampai masukan terakhir sebelum percobaan (klien); Cari Objek: sejak petunjuk tampil.'],
            'occurred_at'        => ['Waktu percobaan', 'datetime', 'Waktu server saat percobaan dinilai.'],
        ],
        'navigasi' => [
            'screen'      => ['Layar', 'code', 'Kode layar (payload.screen).'],
            'from_screen' => ['Dari layar', 'code', 'Kode layar sebelumnya (payload.from) untuk screen_viewed.'],
            'event_type'  => ['Jenis event', 'enum', 'Event navigasi: screen_viewed, overlay_opened (payload overlay: kenal, hint, exit, lightbox, statement_image, hunt_zoom), dialogue_advanced, library_opened, library_page_viewed, locale_changed, page_hidden, page_visible, viewport_changed, reading_progress, package_changed (paket aset berganti; payload from, to, reason), events_dropped (catatan yang tidak muat di memori perangkat; payload dropped, channel).', 'event'],
        ],
        'bantuan' => [
            'jenis'         => ['Jenis bantuan', 'enum', 'petunjuk = petunjuk dibuka (event hint_opened); audio = satu aksi pemutar suara.', 'helpKind'],
            'hint_id'       => ['No. petunjuk', 'int', 'Nomor petunjuk yang dibuka (hints.id).'],
            'asset_key'     => ['Kode rekaman', 'code', 'Kunci berkas suara.'],
            'audio_context' => ['Bagian permainan', 'enum', 'Tempat rekaman diputar.', 'audioContext'],
            'audio_locale'  => ['Bahasa rekaman', 'enum', 'Bahasa rekaman.', 'locale'],
            'action'        => ['Tindakan suara', 'enum', 'Aksi pemutar suara.', 'audioAction'],
            'play_index'    => ['Pemutaran ke-', 'int', 'Pemutaran ke-n rekaman itu dalam sesi.'],
            'listened_ms'   => ['Lama didengar (mdtk)', 'ms', 'Milidetik rekaman didengar.'],
            'completed'     => ['Didengar sampai habis', 'bool', '1 = rekaman didengar sampai habis.'],
        ],
        'selesai' => [
            'event_type' => ['Jenis event', 'enum', 'Event penyelesaian: challenge_completed, challenge_abandoned, challenge_tries_exhausted, clue_failed, level_completed, session_completed, intro_completed, feedback_submitted.', 'event'],
        ],
        'event' => [
            'id'                   => ['No. event', 'int', 'game_event_logs.id.'],
            'event_uuid'           => ['UUID event', 'code', 'Pengenal unik event, dibuat server.'],
            'client_event_id'      => ['Kode event klien', 'code', 'Kunci idempotensi dari klien; event server memakai srv-{uuid}.'],
            'session_id'           => ['No. sesi', 'int', 'game_sessions.id.'],
            'participant_id'       => ['No. siswa', 'int', 'participants.id.'],
            'level_id'             => ['No. wilayah', 'int', 'levels.id.'],
            'challenge_node_id'    => ['No. tantangan', 'int', 'challenge_nodes.id.'],
            'challenge_attempt_id' => ['No. pengerjaan', 'int', 'challenge_attempts.id.'],
            'challenge_item_id'    => ['No. soal', 'int', 'challenge_items.id.'],
            'payload_json'         => ['Rincian (JSON)', 'json', 'payload_json apa adanya.'],
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
        ],
    ];

    /** Nama lembar (XLSX, tab tabel) dan artinya untuk Kamus Data. */
    private const SHEET_TITLES = [
        'kamus'      => ['Kamus Data', 'Arti setiap lembar dan kolom, satuan, dan daftar nilai berkode.'],
        'peserta'    => ['Peserta', 'Satu baris per siswa.'],
        'sesi'       => ['Sesi', 'Satu baris per sesi bermain.'],
        'perangkat'  => ['Perangkat', 'Satu baris setiap siswa mendaftar, masuk, atau melanjutkan sesi.'],
        'progres'    => ['Progres', 'Keadaan terbaru satu sesi.'],
        'pengerjaan' => ['Pengerjaan Tantangan', 'Satu baris per pengerjaan tantangan.'],
        'jawaban'    => ['Jawaban Soal', 'Keadaan akhir satu soal dalam satu pengerjaan.'],
        'percobaan'  => ['Riwayat Percobaan', 'Satu baris per percobaan satu soal.'],
        'navigasi'   => ['Navigasi', 'Event perpindahan layar dan membaca.'],
        'bantuan'    => ['Bantuan', 'Petunjuk yang dibuka dan setiap aksi pemutar suara.'],
        'selesai'    => ['Penyelesaian', 'Event selesai, ditinggalkan, dan kesempatan habis.'],
        'event'      => ['Event Mentah', 'Semua kolom game_event_logs, tanpa baris yang ditandai hapus (hanya admin).'],
        'masukan'    => ['Kritik & Saran', 'Satu baris per kiriman kritik dan saran.'],
        'butir'      => ['Bank Soal', 'Satu baris per soal di bank soal.'],
    ];

    /** Nilai berlabel kelompok yang tidak ada di Language/id/Admin.php. */
    private const EXTRA_VALUES = [
        'sessionStatus'  => ['active', 'paused', 'completed', 'abandoned'],
        'attemptStatus'  => ['in_progress', 'completed', 'abandoned'],
        'responseStatus' => ['pending', 'answered', 'skipped'],
        'helpKind'       => ['petunjuk' => 'Petunjuk dibuka', 'audio' => 'Pemutaran suara'],
        'deviceVia'      => ['register' => 'Mendaftar', 'login' => 'Masuk', 'resume' => 'Melanjutkan sesi'],
        'level'          => ['temanggung' => 'Temanggung', 'magelang' => 'Magelang', 'wonosobo' => 'Wonosobo'],
        'packageMode'    => [
            'lengkap'  => 'Paket lengkap di perangkat',
            'daring'   => 'Daring (perangkat tanpa paket)',
            'nonaktif' => 'Mode paket mati',
        ],
    ];

    /** @return list<string> */
    public static function sheets(): array
    {
        return array_keys(self::SHEET_TITLES);
    }

    public static function sheetTitle(string $sheet): string
    {
        return self::SHEET_TITLES[$sheet][0] ?? $sheet;
    }

    public static function sheetMeaning(string $sheet): string
    {
        return self::SHEET_TITLES[$sheet][1] ?? '';
    }

    /** Apakah kolom ini punya keterangan lengkap. Dipakai uji. */
    public static function has(string $sheet, string $column): bool
    {
        return isset(self::SHEETS[$sheet][$column]) || isset(self::COMMON[$column]);
    }

    /**
     * Keterangan satu kolom.
     *
     * @return array{key: string, label: string, type: string, meaning: string, unit: string, group: ?string}
     */
    public static function column(string $sheet, string $column): array
    {
        $spec = self::SHEETS[$sheet][$column] ?? self::COMMON[$column] ?? [$column, 'text', ''];

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
     * Baris lembar Kamus Data untuk lembar-lembar yang diekspor.
     *
     * @param array<string, list<string>> $columnsBySheet kode lembar → kolom
     *
     * @return list<array{lembar: string, kolom: string, judul: string, arti: string, satuan: string, nilai: string}>
     */
    public static function dictionary(array $columnsBySheet): array
    {
        $rows = [];

        foreach ($columnsBySheet as $sheet => $columns) {
            $rows[] = [
                'lembar' => $sheet,
                'kolom'  => '',
                'judul'  => self::sheetTitle($sheet),
                'arti'   => self::sheetMeaning($sheet),
                'satuan' => '',
                'nilai'  => '',
            ];

            foreach ($columns as $column) {
                $spec   = self::column($sheet, $column);
                $values = self::values($spec['group']);

                if ($spec['type'] === 'bool') {
                    $values = ['1' => 'ya', '0' => 'tidak'];
                }

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
                ];
            }
        }

        return $rows;
    }
}
