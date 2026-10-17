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

    // Under "Aktivitas terbaru" on Ringkasan; {0} = seconds between event batches
    'recentEventsLag' => 'Diperbarui tiap ±{0} detik.',

    // Answer view switch on the raw data tables
    'answerView'         => 'Tampilan jawaban',
    'answerViewReadable' => 'Mudah dibaca',
    'answerViewRaw'      => 'Asli (JSON)',
    'answerViewHint'     => 'Jawaban siswa dan kunci jawaban ditulis sebagai kalimat. Pilih Asli (JSON) untuk melihat data persis seperti tersimpan. Tombol Unduh CSV mengikuti pilihan ini.',

    // Rilis → Paket aset (docs/rencana-paket-aset.md §6.3, §7.2, §8.7)
    'packageTitle'        => 'Paket aset',
    'packageLead'         => 'Dengan mode paket, setiap perangkat mengunduh semua gambar dan suara sekali di awal, sehingga perpindahan layar terasa seketika. Selama gambar dan suara masih sering berubah, biarkan mode paket mati: permainan berjalan seperti biasa. Teks, soal, dan cerita selalu langsung tampil, apa pun pilihan di sini.',
    'packageBackToReleases' => 'Kembali ke Rilis',
    'packageOpen'         => 'Buka Paket aset',
    'packageMode'         => 'Mode paket',
    'packageOn'           => 'Nyala',
    'packageOff'          => 'Mati',
    'packageKilled'       => 'Mode paket dimatikan dari server',
    'packageKilledHelp'   => 'Pengaturan server mematikan mode paket untuk semua perangkat, apa pun pilihan di halaman ini. Pilihan di halaman ini berlaku lagi setelah pengelola server menyalakannya kembali.',
    'packageOnHelp'       => 'Perangkat mengunduh paket ini sebelum siswa dapat menekan Mulai. Sesi yang sedang berjalan tidak berubah sampai sesi berikutnya.',
    'packageOffHelp'      => 'Gambar dan suara diambil langsung dari server setiap kali dibutuhkan, dan unggahan baru langsung terlihat siswa. Cocok selama konten masih berubah dan untuk uji coba.',
    'packageActiveCode'   => 'Paket yang dipakai siswa',
    'packageSize'         => 'Ukuran unduhan',
    'packageFiles'        => 'Jumlah berkas',
    'packageBuiltAt'      => 'Dibangun',
    'packagePublishedAt'  => 'Diterbitkan',
    'packageTurnOn'       => 'Nyalakan',
    'packageTurnOnHelp'   => 'Nyalakan membangun paket dari semua gambar, suara, dan kode permainan yang aktif sekarang. Pembangunan butuh beberapa menit; biarkan halaman ini terbuka. Sesudahnya ringkasan paket tampil untuk diperiksa sebelum diterbitkan.',
    'packageTurnOff'      => 'Matikan',
    'packageTurnOffConfirm' => 'Matikan mode paket?',
    'packageTurnOffText'  => 'Versi permainan tanpa paket menjadi aktif. Perangkat tidak lagi mengunduh paket mulai sesi bermain berikutnya; berkas yang sudah terunduh dibiarkan.',
    'packageRebuild'      => 'Bangun ulang paket',
    'packageChangedNotice' => 'Ada {0} gambar/suara yang berubah sejak paket terakhir. Siswa belum melihatnya.',
    'packageChangedHelp'  => 'Bangun ulang paket lalu terbitkan agar siswa melihat perubahan ini. Perangkat hanya mengunduh berkas yang berubah.',
    'packageVerified'     => 'Lengkap dan utuh (diperiksa {0}).',
    'packageVerifyIssues' => '{0} berkas tidak cocok (diperiksa {1}). Bangun ulang paket.',
    'packageVerifyNow'    => 'Periksa sekarang',

    'packageBuilding'     => 'Paket sedang dibangun',
    'packageBuildingHelp' => 'Biarkan halaman ini terbuka. Bila halaman tertutup, buka lagi halaman ini untuk melanjutkan dari berkas terakhir.',
    'packageProgress'     => '{0} dari {1} berkas',
    'packageContinue'     => 'Lanjutkan membangun',
    'packageRetry'        => 'Coba lagi',
    'packageBuildDone'    => 'Selesai. Menampilkan ringkasan…',
    'packageBuildError'   => 'Pembangunan terhenti karena gangguan jaringan atau server. Tekan Coba lagi untuk melanjutkan dari berkas terakhir.',

    'packageSummary'      => 'Ringkasan paket baru',
    'packageSummaryHelp'  => 'Paket ini belum terlihat siswa. Periksa ringkasannya, buka pratinjau bila perlu, lalu terbitkan.',
    'packageNewFiles'     => 'Berkas baru atau berubah',
    'packageNewFilesHint' => 'yang diunduh perangkat yang sudah punya paket {0}',
    'packageNewFilesAll'  => 'perangkat mengunduh seluruh paket',
    'packageGroups'       => 'Ukuran per bagian',
    'packageGroupColumn'  => 'Bagian',
    'packageWarnings'     => 'Peringatan',
    'packageNoWarnings'   => 'Tidak ada peringatan.',
    'packageMoreKeys'     => 'dan {0} lainnya',
    'packagePublishOn'    => 'Terbitkan dan nyalakan',
    'packagePublish'      => 'Terbitkan paket ini',
    'packagePublishConfirm' => 'Terbitkan paket {0}?',
    'packagePublishText'  => 'Versi permainan baru dengan paket ini menjadi aktif. Perangkat mengunduhnya di awal sesi bermain berikutnya, sebelum siswa dapat menekan Mulai.',
    'packagePreview'      => 'Pratinjau',

    'packageRunningStudy' => 'Penelitian "{0}" sedang berjalan. Mengganti atau mematikan paket sekarang membuat sesi dalam penelitian ini memakai paket berbeda.',
    'packageConfirmStudy' => 'Saya mengerti dan tetap ingin mengganti paket selama penelitian berjalan.',
    'packageReason'       => 'Alasan (dicatat di riwayat aktivitas)',
    'packageReasonHint'   => 'Mis. gambar Magelang diperbaiki sebelum gelombang kedua.',

    'packageOnlineLab'    => 'Izinkan mode daring untuk perangkat lab',
    'packageOnlineLabHelp' => 'Untuk perangkat lab yang menghapus data peramban setiap kali siswa keluar. Bila diizinkan, guru dapat memilih Main daring di perangkat ini di layar unduhan. Sesi seperti ini tercatat sebagai mode daring, sehingga peneliti dapat memisahkannya. Bawaannya tidak diizinkan.',
    'packageSave'         => 'Simpan',

    'packagePreviewTitle' => 'Pratinjau untuk staf',
    'packagePreviewHelp'  => 'Tombol Pratinjau membuka permainan dengan gambar dan suara dari paket itu, hanya untuk Anda selama masuk sebagai staf, juga saat mode paket mati. Perangkat siswa tidak terpengaruh.',
    'packagePreviewNow'   => 'Anda sedang melihat pratinjau paket {0}.',
    'packagePreviewStop'  => 'Hentikan pratinjau',

    'packageHistory'      => 'Riwayat paket',
    'packageHistoryEmpty' => 'Belum ada paket. Tekan Nyalakan untuk membangun paket pertama.',
    'packageStatusColumn' => 'Status',

    'packageAutoReleaseOn'  => 'Dibuat otomatis saat paket {0} diterbitkan di Paket aset (dari versi {1}).',
    'packageAutoReleaseOff' => 'Dibuat otomatis saat mode paket dimatikan di Paket aset (dari versi {0}).',
    'packageBuildStarted'   => 'Paket mulai dibangun. Biarkan halaman ini terbuka sampai selesai.',
    'packageBuildStartFailed' => 'Paket belum dapat mulai dibangun. Coba lagi sebentar; bila tetap gagal, hubungi pengelola server.',
    'packageBuildFailed'    => 'Pembangunan paket terhenti. Tekan Lanjutkan membangun untuk mencoba lagi; bila tetap gagal, hubungi pengelola server.',
    'packageBuildFinished'  => 'Paket selesai dibangun. Periksa ringkasannya, lalu terbitkan.',
    'packageBuildContinue'  => 'Sebagian berkas sudah diproses. Tekan Lanjutkan membangun untuk meneruskan.',
    'packageNotFound'       => 'Paket tidak ditemukan atau belum selesai dibangun.',
    'packageAlreadyActive'  => 'Paket {0} sudah dipakai siswa.',
    'packageAlreadyOff'     => 'Mode paket sudah mati.',
    'packageNeedsReason'    => 'Penelitian "{0}" sedang berjalan. Centang konfirmasi dan tulis alasannya agar pergantian paket tercatat.',
    'packageSwitchFailed'   => 'Mode paket belum dapat diubah. Coba lagi sebentar.',
    'packageSwitchedOn'     => 'Paket {0} diterbitkan dengan versi permainan {1}. Perangkat mengunduhnya di awal sesi bermain berikutnya.',
    'packageSwitchedOff'    => 'Mode paket dimatikan dengan versi permainan {0}. Mulai sesi bermain berikutnya gambar dan suara diambil langsung dari server.',
    'packageSettingFailed'  => 'Pengaturan belum dapat disimpan. Coba lagi sebentar.',
    'packageOnlineLabOn'    => 'Perangkat lab sekarang boleh bermain dalam mode daring.',
    'packageOnlineLabOff'   => 'Mode daring untuk perangkat lab tidak diizinkan lagi.',
    'packageVerifyNone'     => 'Mode paket mati, jadi belum ada paket yang perlu diperiksa.',
    'packageVerifyOk'       => 'Paket {0} lengkap dan utuh.',
    'packageVerifyBad'      => 'Paket {0} bermasalah: {1} berkas tidak cocok. Bangun ulang paket.',

    // Activating a release during a running study (§7.2)
    'releaseConfirmTitle'   => 'Aktifkan versi saat penelitian berjalan',
    'releaseConfirmLead'    => 'Versi {0} memakai paket aset yang berbeda dengan yang dipakai siswa sekarang.',
    'releaseNeedsReason'    => 'Penelitian "{0}" sedang berjalan dan versi ini memakai paket aset lain. Centang konfirmasi dan tulis alasannya.',
    'releasePackageFrom'    => 'Paket sekarang',
    'releasePackageTo'      => 'Paket versi ini',
    'releaseNoPackage'      => 'tanpa paket (mode paket mati)',
    'releaseActivate'       => 'Aktifkan versi ini',
    'releaseCancel'         => 'Batal',
    'releaseActivateMore'   => 'Aktifkan…',
    'releaseAssetVersionHelp' => 'Kode paket aset (pkt-…), atau 1 untuk tanpa paket. Biasanya diisi otomatis lewat Paket aset.',

    // Study package lock (§7.2)
    'studyPackage'          => 'Paket aset terkunci',
    'studyPackageNone'      => 'tanpa paket (mode paket mati saat penelitian dijalankan)',
    'studyPackageDiffers'   => 'Siswa sekarang memakai {0}, berbeda dengan paket yang terkunci untuk penelitian ini.',

    // Research-ready checklist item (§7.2)
    'readyPackageLabel'     => 'Paket terbit, lengkap, dan terverifikasi',
    'readyPackageOff'       => 'Mode paket masih mati. Nyalakan di Rilis → Paket aset.',
    'readyPackageKilled'    => 'Mode paket dimatikan dari server. Hubungi pengelola server.',
    'readyPackageOk'        => 'Paket {0} lengkap dan utuh (diperiksa {1}).',
    'readyPackageBad'       => 'Paket {0}: {1} berkas tidak cocok. Bangun ulang paket.',

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

    /** asset_packages.status */
    'packageStatus' => [
        'membangun' => 'sedang dibangun',
        'dibangun'  => 'belum diterbitkan',
        'terbit'    => 'dipakai siswa',
        'ditarik'   => 'pernah dipakai',
    ],

    /** Package parts (manifest groups, plan §5.1); regions are labelled in the view. */
    'packageGroup' => [
        'kode'    => 'Kode permainan',
        'inti'    => 'Inti (semua layar)',
        'penutup' => 'Penutup',
    ],

    /** Build warnings (AssetPackageBuilder). */
    'packageWarning' => [
        'placeholder'            => 'Gambar contoh yang belum diganti',
        'narration_not_approved' => 'Rekaman narasi belum disetujui (tidak ikut paket)',
        'over_budget'            => 'Berkas lebih besar dari anggaran ukuran',
        'file_missing'           => 'Berkas tidak ditemukan di server (tidak ikut paket)',
        'hash_mismatch'          => 'Isi berkas berbeda dengan catatan gambar',
        'library_copy_failed'    => 'Gambar Pustaka belum dapat disalin dari Wikimedia (tetap tautan luar)',
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
        'package_changed'      => 'Paket aset berganti',
        'events_dropped'       => 'Catatan tidak tersimpan (memori perangkat penuh)',
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
        'catalog_import'               => 'Memasang gambar dari Katalog Aset',
        'audio_upload'                 => 'Mengunggah rekaman suara',
        'audio_approve'                => 'Menyetujui rekaman suara',
        'audio_approve_bulk'           => 'Menyetujui banyak rekaman sekaligus',
        'narration_import'             => 'Mengimpor rekaman narasi',
        'narration_list_download'      => 'Mengunduh daftar rekaman',
        'export'                       => 'Membuat unduhan data',
        'export_download'              => 'Mengunduh data',
        'export_csv'                   => 'Mengunduh CSV tabel data mentah',
        'list_csv'                     => 'Mengunduh CSV tabel daftar (siswa, sesi, linimasa, kritik & saran)',
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
        'package_build'                => 'Membangun paket aset',
        'package_prune'                => 'Membersihkan berkas paket aset lama',
        'package_online_lab'           => 'Mengubah izin mode daring perangkat lab',
        'system_reset'                 => 'Reset total (gelita:reset)',
        'media_mockups'                => 'Membuat gambar contoh (gelita:mockups)',
        'school_merge'                 => 'Menggabungkan nama sekolah',
        'school_verify'                => 'Mengesahkan sekolah baru',
    ],
];
