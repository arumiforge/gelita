<?php

/**
 * Panel admin satu bahasa (Indonesia). LocaleFilter memaksa locale 'id'
 * untuk /admin dan /api/admin, sehingga tidak ada berkas en/Admin.php.
 *
 * Pembaca panel adalah guru dan admin sekolah, bukan orang teknis: pakai
 * kata sehari-hari, hindari istilah pemrograman. Kelompok label di bagian
 * bawah menerjemahkan kode yang tersimpan di database (status, fase, jenis
 * soal, …) lewat admin_label(); kode yang belum punya label tampil apa adanya.
 */
return [
    'panelTitle'     => 'Panel GELITA',
    'logout'         => 'Keluar',
    'changePassword' => 'Ganti kata sandi',
    'mainMenu'       => 'Menu utama',
    'loginTitle'     => 'Masuk Panel Guru & Admin',

    'closeMenu'        => 'Tutup menu',
    'groupData'        => 'Data siswa',
    'groupReport'      => 'Laporan',
    'groupManage'      => 'Pengelolaan',

    'menuDashboard'    => 'Beranda',
    'menuParticipants' => 'Peserta',
    'menuSessions'     => 'Sesi bermain',
    'menuAnalytics'    => 'Hasil belajar',
    'menuAnLevel'      => 'Per wilayah',
    'menuAnNode'       => 'Per tantangan',
    'menuAnItem'       => 'Per soal',
    'menuAnIndicator'  => 'Per indikator',
    'menuAnPrePost'    => 'Pretest & posttest',
    'menuFeedback'     => 'Kritik & saran',
    'menuExport'       => 'Unduh data',
    'menuContent'      => 'Konten permainan',
    'menuBankImport'   => 'Impor soal (Excel)',
    'menuNarration'    => 'Rekaman narasi',
    'menuMedia'        => 'Gambar & suara',
    'menuStudy'        => 'Pengaturan penelitian',
    'menuGovernance'   => 'Hapus data & riwayat',
    'menuSchools'      => 'Sekolah',
    'menuStaff'        => 'Akun guru & admin',

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
    ],

    'phase' => [
        'umum'     => 'Umum',
        'pretest'  => 'Pretest (tes awal)',
        'posttest' => 'Posttest (tes akhir)',
    ],

    'locale' => [
        'id' => 'Indonesia',
        'en' => 'Inggris',
    ],

    'gender' => [
        'laki-laki' => 'Laki-laki',
        'perempuan' => 'Perempuan',
        'lainnya'   => 'Lainnya',
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
        'desktop' => 'komputer',
        'mobile'  => 'ponsel',
        'tablet'  => 'tablet',
    ],

    /** Jenis soal (interaction_type). */
    'interaction' => [
        'puzzle_arrange'  => 'Susun kepingan gambar',
        'ordering'        => 'Urutkan kartu',
        'fill_blank_bank' => 'Isi rumpang (pilih kata)',
        'fill_blank_free' => 'Isi rumpang (ketik jawaban)',
        'verdict_card'    => 'Kartu benar/salah',
        'verdict_reason'  => 'Kartu benar/salah/pendapat + alasan',
        'single_choice'   => 'Pilihan ganda',
        'source_trust'    => 'Pilih sumber tepercaya',
        'find_object'     => 'Cari benda di gambar',
    ],

    /** Pilar literasi digital (Wonosobo tantangan 5). */
    'pillar' => [
        'digital_skills'  => 'Cakap digital',
        'digital_ethics'  => 'Etika digital',
        'digital_safety'  => 'Aman digital',
        'digital_culture' => 'Budaya digital',
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
        'active' => 'Aktif',
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
        'item_responses'       => 'Jawaban per soal',
        'challenge_attempts'   => 'Percobaan tantangan',
        'session_progress'     => 'Kemajuan sesi',
        'participant_feedback' => 'Kritik & saran',
        'game_sessions'        => 'Sesi bermain',
        'participant_consents' => 'Persetujuan orang tua/wali',
        'participants'         => 'Data peserta',
    ],

    /** Lembar pada berkas Excel hasil unduhan data. */
    'exportSheet' => [
        'Participants'        => 'Data peserta',
        'Sessions'            => 'Sesi bermain',
        'Levels'              => 'Skor per wilayah',
        'Challenge Summary'   => 'Ringkasan per tantangan',
        'Item Responses'      => 'Jawaban per soal',
        'Raw Events'          => 'Catatan aktivitas lengkap',
        'Audio Usage'         => 'Pemakaian suara',
        'Indicators'          => 'Capaian per indikator',
        'Demographic Summary' => 'Ringkasan per kelompok',
        'Feedback'            => 'Kritik & saran',
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
        'challenge_checked'    => 'Menekan Periksa',
        'challenge_completed'  => 'Tantangan selesai',
        'challenge_skipped'    => 'Melewati tantangan',
        'challenge_abandoned'  => 'Tantangan ditinggalkan',
        'answer_submitted'     => 'Mengirim jawaban',
        'answer_changed'       => 'Mengubah jawaban',
        'wrong_target_clicked' => 'Salah pilih benda',
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
        'study_create'                 => 'Membuat studi',
        'study_update'                 => 'Mengubah studi',
        'release_create'               => 'Membuat versi permainan',
        'release_activate'             => 'Mengaktifkan versi permainan',
        'scoring_create'               => 'Membuat aturan penilaian',
        'scoring_activate'             => 'Mengaktifkan aturan penilaian',
        'score_recompute'              => 'Menghitung ulang skor',
        'school_merge'                 => 'Menggabungkan nama sekolah',
        'school_verify'                => 'Mengesahkan sekolah baru',
    ],
];
