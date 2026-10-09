<?php

/**
 * Panel admin satu bahasa (Indonesia). LocaleFilter memaksa locale 'id'
 * untuk /admin dan /api/admin, sehingga tidak ada berkas en/Admin.php.
 *
 * Pembaca panel adalah guru dan admin sekolah, bukan orang teknis: pakai
 * kata sehari-hari, hindari istilah pemrograman. Kelompok label di bagian
 * bawah menerjemahkan kode yang tersimpan di database (status, alasan selesai, jenis
 * soal, …) lewat admin_label(); kode yang belum punya label tampil apa adanya.
 */
return [
    'panelTitle'     => 'Panel GELITA',
    'logout'         => 'Keluar',
    'changePassword' => 'Ganti kata sandi',
    'mainMenu'       => 'Menu utama',
    'loginTitle'     => 'Masuk Panel Guru & Admin',

    'closeMenu'        => 'Tutup menu',
    'openMenu'         => 'Buka menu',
    'groupData'        => 'Data penelitian',
    'groupContent'     => 'Konten',
    'groupSettings'    => 'Pengaturan',

    'menuDashboard'    => 'Ringkasan',
    'menuParticipants' => 'Siswa',
    'menuSessions'     => 'Sesi bermain',
    'menuRawData'      => 'Data mentah',
    'menuFeedback'     => 'Kritik & saran',
    'menuExport'       => 'Unduh data',
    'menuContent'      => 'Tantangan',
    'menuBankImport'   => 'Impor bank soal',
    'menuNarration'    => 'Rekaman narasi',
    'menuMedia'        => 'Gambar & mockup',
    'menuChecklist'    => 'Kelengkapan aset',
    'menuStudy'        => 'Penelitian',
    'menuReleases'     => 'Rilis',
    'menuSchools'      => 'Sekolah',
    'menuStaff'        => 'Staf',
    'menuGovernance'   => 'Tata kelola',
    'quickSearch'        => 'Cari kode, nama pengguna, atau nama siswa',
    'quickSearchTeacher' => 'Cari kode siswa',
    'accountMenu'        => 'Menu akun',
    'skipToContent'      => 'Langsung ke isi halaman',
    'breadcrumbLabel'    => 'Lokasi halaman',
    'activeStudyTitle'   => 'Penelitian yang sedang berjalan',

    'roleAdmin'    => 'Admin',
    'roleGuru'     => 'Guru',
    'emptyDefault' => 'Belum ada data untuk ditampilkan.',

    // ------------------------------------------------ label kode (admin_label)

    /** Status sesi bermain, percobaan tantangan, dan jawaban per soal. */
    'status' => [
        'active'      => 'aktif',
        'paused'      => 'jeda',
        'completed'   => 'selesai',
        'abandoned'   => 'ditinggalkan',
        'in_progress' => 'sedang dikerjakan',
        'pending'     => 'belum dijawab',
        'answered'    => 'dijawab',
        'skipped'     => 'dilewati',
        'locked'      => 'belum dikerjakan',
    ],

    /** Alasan pengerjaan selesai (challenge_attempts.completion_reason). */
    'completion' => [
        'solved'          => 'Semua soal benar',
        'tries_exhausted' => 'Kesempatan habis',
    ],

    'locale' => [
        'id' => 'Indonesia',
        'en' => 'Inggris',
    ],

    'gender' => [
        'laki-laki' => 'Laki-laki',
        'perempuan' => 'Perempuan',
    ],

    /** Pose tokoh dan efek layar pada slide cerita (dialogues.pose / dialogues.effect). */
    'pose' => [
        'idle'       => 'diam',
        'happy'      => 'senang',
        'bow'        => 'membungkuk',
        'sad'        => 'sedih',
        'afraid'     => 'takut',
        'determined' => 'bertekad',
        'smile'      => 'tersenyum',
        'worried'    => 'cemas',
        'weak'       => 'lemah',
    ],

    'effect' => [
        'fog'      => 'kabut datang',
        'fog-lift' => 'kabut tersibak',
        'glow'     => 'cahaya berdenyut',
        'flash'    => 'kilat cahaya',
        'shake'    => 'layar bergetar',
        'dim'      => 'layar meredup',
    ],

    /** Jenis perangkat sesi bermain (game_sessions.device_type). */
    'device' => [
        'desktop'    => 'komputer',
        'mobile'     => 'ponsel',
        'smartphone' => 'ponsel',
        'phablet'    => 'ponsel besar',
        'tablet'  => 'tablet',
    ],

    /** Jenis soal (interaction_type). */
    'interaction' => [
        'puzzle_arrange'  => 'Susun kepingan gambar',
        'fill_blank_bank' => 'Isi rumpang (pilih kata)',
        'verdict_card'    => 'Kartu benar/salah',
        'find_object'     => 'Cari benda di gambar',
    ],

    /** Status tinjauan isi soal. */
    'review' => [
        'draft'              => 'draf',
        'needs_verification' => 'fakta perlu dicek',
        'verified'           => 'sudah dicek',
    ],

    /** Status persetujuan rekaman suara. */
    'approval' => [
        'draft'    => 'menunggu persetujuan',
        'review'   => 'sedang ditinjau',
        'approved' => 'disetujui',
        'rejected' => 'ditolak',
        'inactive' => 'berkas dinonaktifkan',
        'none'     => 'belum ada',
    ],

    'studyStatus' => [
        'draft'  => 'Draf (belum dipakai)',
        'active' => 'Berjalan',
        'closed' => 'Ditutup',
    ],

    'exportStatus' => [
        'queued'  => 'menunggu',
        'running' => 'sedang dibuat',
        'done'    => 'siap diunduh',
        'failed'  => 'gagal',
    ],

    'deletionStatus' => [
        'preview'   => 'menunggu keputusan',
        'executed'  => 'sudah dihapus',
        'cancelled' => 'dibatalkan',
    ],

    /** Jenis data yang terdampak penghapusan (nama tabel database). */
    'dataTable' => [
        'game_event_logs'      => 'Catatan aktivitas bermain',
        'audio_usage_events'   => 'Catatan pemakaian suara',
        'item_tries'           => 'Percobaan per soal',
        'item_responses'       => 'Jawaban per soal',
        'challenge_attempts'   => 'Pengerjaan tantangan',
        'device_logs'          => 'Catatan perangkat',
        'session_progress'     => 'Kemajuan sesi',
        'participant_feedback' => 'Kritik & saran',
        'game_sessions'        => 'Sesi bermain',
        'participants'         => 'Data siswa',
    ],

    /** Tingkat berpikir Taksonomi Bloom revisi per soal (challenge_items.bloom_level). */
    'bloom' => [
        'C1' => 'C1 · Mengingat',
        'C2' => 'C2 · Memahami',
        'C3' => 'C3 · Menerapkan',
        'C4' => 'C4 · Menganalisis',
        'C5' => 'C5 · Mengevaluasi',
        'C6' => 'C6 · Mencipta',
    ],

    /** Bentuk keping Susun Gambar (config_json.shape soal). */
    'shape' => [
        'square' => 'kotak',
        'jigsaw' => 'jigsaw (bertonjolan)',
    ],

    /** Tindakan pada catatan pemakaian suara (audio_usage_events.action). */
    'audioAction' => [
        'play'     => 'diputar siswa',
        'autoplay' => 'diputar otomatis',
        'pause'    => 'dijeda',
        'replay'   => 'diputar ulang',
        'complete' => 'didengar sampai habis',
    ],

    /** Jenis catatan aktivitas di linimasa sesi (game_event_logs). */
    'event' => [
        'session_started'      => 'Mulai bermain',
        'session_resumed'      => 'Lanjut bermain',
        'session_paused'       => 'Berhenti sejenak',
        'session_abandoned'    => 'Sesi ditinggalkan',
        'session_completed'    => 'Sesi selesai',
        'locale_changed'       => 'Ganti bahasa',
        'level_opened'         => 'Membuka wilayah',
        'level_completed'      => 'Wilayah tuntas',
        'library_opened'       => 'Membuka Pustaka',
        'library_page_viewed'  => 'Membaca halaman Pustaka',
        'dialogue_advanced'    => 'Melanjutkan cerita/dialog',
        'challenge_opened'     => 'Membuka tantangan',
        'challenge_checked'    => 'Mengecek jawaban',
        'challenge_completed'  => 'Tantangan selesai',
        'challenge_abandoned'  => 'Tantangan ditinggalkan',
        'challenge_tries_exhausted' => 'Kesempatan habis',
        'answer_submitted'     => 'Mengirim jawaban',
        'answer_changed'       => 'Mengubah jawaban',
        'answer_selected'      => 'Memilih jawaban',
        'wrong_target_clicked' => 'Salah pilih benda',
        'clue_failed'          => 'Petunjuk dilewati',
        'reading_progress'     => 'Membaca bacaan',
        'screen_viewed'        => 'Membuka layar',
        'overlay_opened'       => 'Membuka jendela',
        'page_hidden'          => 'Meninggalkan layar',
        'page_visible'         => 'Kembali ke layar',
        'viewport_changed'     => 'Ukuran layar berubah',
        'hint_opened'          => 'Membuka petunjuk',
        'audio_play'           => 'Memutar suara',
        'audio_autoplay'       => 'Suara diputar otomatis',
        'audio_pause'          => 'Menjeda suara',
        'audio_replay'         => 'Mengulang suara',
        'audio_completed'      => 'Suara didengar sampai habis',
        'feedback_submitted'   => 'Mengirim kritik & saran',
        'password_changed'     => 'Mengganti kata sandi',
        'intro_completed'      => 'Selesai menonton cerita pembuka',
    ],

    /** Jenis tindakan di riwayat aktivitas (audit_logs). */
    'audit' => [
        'login'                        => 'Masuk panel',
        'login_failed'                 => 'Gagal masuk panel',
        'logout'                       => 'Keluar panel',
        'content_update'               => 'Mengubah konten',
        'content_import'               => 'Mengimpor soal dari Excel',
        'media_upload'                 => 'Mengunggah gambar/berkas',
        'media_deactivate'             => 'Menonaktifkan gambar/berkas',
        'media_scan'                   => 'Memeriksa berkas',
        'audio_upload'                 => 'Mengunggah rekaman suara',
        'audio_approve'                => 'Menyetujui rekaman suara',
        'audio_approve_bulk'           => 'Menyetujui banyak rekaman sekaligus',
        'narration_import'             => 'Mengimpor rekaman narasi',
        'narration_list_download'      => 'Mengunduh daftar rekaman',
        'export'                       => 'Membuat unduhan data',
        'export_download'              => 'Mengunduh data',
        'export_csv'                   => 'Mengunduh CSV tabel data mentah',
        'export_purge'                 => 'Membuang unduhan lama',
        'delete_preview'               => 'Menghitung data yang akan dihapus',
        'delete_execute'               => 'Menghapus data',
        'delete_cancel'                => 'Membatalkan penghapusan',
        'retention_run'                => 'Menjalankan perawatan data',
        'participant_password_reset'   => 'Membuat sandi sementara siswa',
        'staff_create'                 => 'Membuat akun guru/admin',
        'staff_update'                 => 'Mengubah akun guru/admin',
        'staff_deactivate'             => 'Menonaktifkan akun guru/admin',
        'staff_password_reset'         => 'Membuat sandi sementara guru/admin',
        'staff_password_change'        => 'Mengganti kata sandi sendiri',
        'staff_password_change_failed' => 'Gagal mengganti kata sandi',
        'study_create'                 => 'Membuat penelitian',
        'study_update'                 => 'Mengubah penelitian',
        'release_create'               => 'Membuat versi permainan',
        'release_activate'             => 'Mengaktifkan versi permainan',
        'system_reset'                 => 'Reset total (gelita:reset)',
        'media_mockups'                => 'Membuat gambar contoh (gelita:mockups)',
        'school_merge'                 => 'Menggabungkan nama sekolah',
        'school_verify'                => 'Mengesahkan sekolah baru',
    ],
];
