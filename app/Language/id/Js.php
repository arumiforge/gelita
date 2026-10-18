<?php

/**
 * Teks UI yang ditulis JavaScript area game.
 *
 * Seluruh berkas ini dikirim ke browser lewat `#app-config.text`
 * (js_config()), jadi hanya berisi teks tampilan — tidak ada data rahasia.
 * Placeholder {0}, {1} diganti fungsi t() di core/config.js.
 */
return [
    // Umum
    'ok'           => 'Oke',
    'yes'          => 'Ya',
    'cancel'       => 'Batal',
    'next'         => 'Lanjut',
    'tryAgain'     => 'Coba lagi',
    'markCorrect'  => 'Benar',
    'markWrong'    => 'Belum tepat',

    // Galat & sesi
    'errNetwork'      => 'Koneksi ke server terputus. Coba cek internet sekolah, lalu ulangi.',
    'errNetworkRetry' => 'Koneksi terputus. Tunggu sebentar, lalu ketuk lagi.',
    'errUnknown'      => 'Ada yang tidak beres. Coba muat ulang halaman ini.',
    'errRateLimited'  => 'Terlalu cepat. Tunggu sebentar, lalu coba lagi.',
    'sessionTitle'    => 'Sesi berakhir',
    'sessionExpired'  => 'Sesimu sudah berakhir. Silakan masuk lagi.',
    'pageExpiredTitle' => 'Halaman kedaluwarsa',
    'reloadPage'      => 'Muat ulang',
    'loginAgain'      => 'Masuk lagi',
    'savedOffline'    => 'Koneksi terputus — langkahmu tersimpan sementara dan dikirim saat koneksi kembali.',
    'eventQueueFull'  => 'Memori catatan di perangkat ini penuh, jadi sebagian catatan permainan tidak tersimpan. Sambungkan ke internet, ya, lalu beri tahu gurumu.',

    // HUD & audio
    'soundOn'      => 'Suara menyala',
    'soundOff'     => 'Suara mati',
    'audioNoSound' => 'Suara belum dapat diputar. Baca teksnya, ya.',

    // Toast
    'toastClose' => 'Ketuk untuk menutup',

    // Refleksi
    'needTwo' => 'Isi minimal dua pertanyaan, ya.',

    // Kerangka tantangan
    'itemsPending'  => 'Masih ada soal yang belum dijawab.',
    'fixAnswers'    => 'Perbaiki',
    'hintTitle'     => 'Petunjuk',
    'hintPenalty'   => 'Petunjuk bisa membantumu. Buka petunjuk sekarang?',
    'hintOpen'      => 'Buka petunjuk',
    'hintAllOpened' => 'Semua petunjuk sudah kamu buka:',
    'hintNone'      => 'Belum ada petunjuk untuk soal ini.',
    'hintUsed'      => 'petunjuk dipakai',
    'exitTitle'     => 'Tinggalkan tantangan ini?',
    'exitText'      => 'Jawaban yang sudah kamu cek tetap tersimpan, tetapi tantangan ini belum selesai.',
    'exitYes'       => 'Ya, keluar',
    'exitNo'        => 'Lanjutkan tantangan',

    // Aturan 3 kesempatan (Dok 2 §5.3)
    'triesLeft'            => 'Kesempatanmu tinggal {0} lagi.',
    'triesLast'            => 'Tinggal satu kesempatan lagi. Baca pelan-pelan, ya!',
    'triesExhaustedTitle'  => 'Kesempatanmu sudah habis',
    'triesExhaustedText'   => 'Tidak apa-apa! Jawabanmu disimpan apa adanya. Ayo lanjut.',
    'triesExhaustedPuzzle' => 'Tidak apa-apa! Ini gambar utuhnya. Jawabanmu disimpan apa adanya.',
    'clueFailedTitle'      => 'Petunjuk ini dilewati',
    'clueFailedText'       => 'Kesempatan untuk petunjuk ini sudah habis. Kita lanjut ke petunjuk berikutnya.',
    'clueTriesLeft'        => 'Kesempatan untuk petunjuk ini: {0}',

    // Puzzle
    'piecesSwapped'    => 'Keping di {0} dan {1} ditukar.',
    'puzzleOkTitle'    => 'Gambarnya utuh!',
    'puzzleOkText'     => 'Semua keping sudah di tempatnya.',
    'puzzleWrongTitle' => 'Belum utuh',
    'puzzleWrongText'  => 'Gambarnya belum utuh, masih ada {0} keping yang keliru. Keping bertanda ✗ belum di tempatnya.',
    'puzzleWrongTextOne' => 'Gambarnya belum utuh, masih ada 1 keping yang keliru. Keping bertanda ✗ belum di tempatnya.',
    'puzzleWrongPlain' => 'Gambarnya belum utuh. Tukar lagi kepingnya, ya.',
    'pieceMisplaced'   => 'belum di tempatnya',

    // Rumpang
    'blankFilled'     => '{0} diisi: {1}',
    'blankEmptyTitle' => 'Masih ada yang kosong',
    'blankEmptyText'  => 'Ada {0} kotak yang belum diisi. Isi semua kotak dulu, ya.',
    'blankEmptyTextOne' => 'Ada 1 kotak yang belum diisi. Isi semua kotak dulu, ya.',
    'blankOkTitle'    => 'Semua kalimat benar!',
    'blankOkText'     => 'Kamu mengisi semua bagian rumpang dengan tepat.',
    'blankWrongTitle' => 'Belum semuanya tepat',
    'blankWrongText'  => '{0} dari {1} sudah benar. Ketuk kotak merah untuk menggantinya.',

    // Boleh
    'readText'         => 'Baca teks',
    'close'            => 'Tutup',
    'cardsEmptyTitle'  => 'Masih ada kartu kosong',
    'cardsEmptyText'   => 'Ada {0} kartu yang belum kamu tentukan.',
    'cardsEmptyTextOne' => 'Ada 1 kartu yang belum kamu tentukan.',
    'cardsOkTitle'     => 'Semua kartu tepat!',
    'cardsOkText'      => 'Kamu menilai semua pernyataan dengan tepat.',
    'cardsWrongTitle'  => 'Belum semuanya tepat',
    'cardsWrongText'   => '{0} dari {1} pernyataan sudah tepat. Pikirkan lagi yang bergaris merah.',

    // Cari
    'clueLabel'      => 'Petunjuk {0}',
    'huntFoundMark'  => 'ditemukan',
    'huntDoneTitle'  => 'Semua objek ketemu!',
    'huntDoneText'   => 'Kamu menemukan semua warisan budaya yang dicari.',
    'huntDecoyTitle' => 'Bukan yang ini',
    'huntDecoyText'  => 'Benda itu bukan yang dimaksud petunjuk. Baca lagi petunjuknya, ya.',
    'huntOtherTitle' => 'Bukan yang ini',
    'huntOtherText'  => 'Benda itu juga warisan {0}, tetapi bukan yang dimaksud petunjuk kali ini.',

    // Isian formulir siswa (game/form-check.js, game/login.js)
    'formMissingTitle'  => 'Masih ada yang kosong',
    'formMissingText'   => 'Isi dulu bagian ini, ya: {0}.',
    'formInvalidTitle'  => 'Ada isian yang belum pas',
    'formInvalidText'   => 'Periksa lagi bagian ini, ya: {0}.',
    'formAnd'           => 'dan',
    'formFix'           => 'Oke, aku isi',
    'loginFailedTitle'  => 'Belum bisa masuk',

    // Asset package Stage 3: Siapkan GELITA (game/prepare.js) and Periksa paket (game/package-inspect.js)
    'packageSizeOf'        => '{0} dari {1}',
    'packageChecking'      => 'Memeriksa perangkat…',
    'packageDownloading'   => 'Sedang menyiapkan gambar dan suara. Tunggu sebentar, ya.',
    'packageActivating'    => 'Hampir selesai…',
    'packageGroupDone'     => 'Siap',
    'packageGroupWaiting'  => 'Menunggu',
    'packageReady'         => 'GELITA siap dimainkan! Tekan Mulai.',
    'packageOnline'        => 'GELITA dimainkan daring di perangkat ini. Gambar dan suara diambil langsung dari internet, jadi bisa sedikit lebih lambat.',
    'packageRetrying'      => 'Mencoba lagi…',
    'packageErrNetwork'    => 'Sambungan internet terputus. Bagian yang sudah tersimpan tidak hilang. GELITA mencoba lagi sendiri, atau tekan Coba lagi.',
    'packageErrServer'     => 'GELITA belum bisa menghubungi server. GELITA mencoba lagi sendiri, atau tekan Coba lagi.',
    'packageErrQuota'      => 'Ruang penyimpanan perangkat ini kurang sekitar {0}. Untuk guru: kosongkan ruang lalu tekan Coba lagi, atau pilih Main daring di perangkat ini.',
    'packageErrQuotaFull'  => 'Ruang penyimpanan perangkat ini sudah penuh. Untuk guru: kosongkan ruang lalu tekan Coba lagi, atau pilih Main daring di perangkat ini.',
    'packageErrPrivate'    => 'Mode privat: GELITA tetap bisa dimainkan, tetapi lebih lambat. Untuk guru: pilih Main daring di perangkat ini, atau buka GELITA di jendela biasa.',
    'packageErrCorrupt'    => 'Ada berkas yang rusak saat diunduh. Tekan Coba lagi, atau beri tahu guru, ya. Untuk guru: berkas rusak: {0}.',
    'packageErrMissing'    => 'Ada berkas yang belum ada di server. Beri tahu guru, ya. Untuk guru: berkas {0} tidak ditemukan.',
    'packageErrManifest'   => 'Daftar isi paket belum bisa dibaca. Tekan Coba lagi, atau beri tahu guru, ya.',
    'packageErrIncomplete' => 'Ada bagian yang hilang sebelum selesai. Tekan Coba lagi, ya.',
    'packageErrWorker'     => 'Perangkat ini belum siap memakai paket. Tekan Coba lagi untuk memuat ulang halaman.',
    'inspectNone'          => 'Belum ada',
    'inspectDeviceValue'   => '{0}: {1} berkas, {2}, disimpan {3}',
    'inspectServerValue'   => '{0}: {1} berkas, {2}',
    'inspectServerOff'     => 'Mode paket mati',
    'inspectStorageValue'  => '{0} terpakai dari {1}',
    'inspectUnknown'       => 'Tidak diketahui',
    'inspectYes'           => 'Ya',
    'inspectNo'            => 'Belum',
    'inspectStateReady'    => 'Lengkap dan siap dipakai.',
    'inspectStateDownload' => 'Belum lengkap. Tekan Unduh yang kurang.',
    'inspectStateOld'      => 'Paket di perangkat ini sudah lama. Tekan Unduh yang kurang; hanya berkas yang berubah yang diunduh.',
    'inspectStateOff'      => 'Mode paket mati. Siswa memakai gambar dan suara langsung dari server.',
    'inspectStateUnsupported' => 'Peramban ini tidak bisa menyimpan paket. Siswa bermain daring di perangkat ini.',
    'inspectStateOnline'   => 'Mode daring dipilih untuk sesi peramban ini.',
    'inspectStateWorker'   => 'Paket lengkap, tetapi perangkat belum siap memakainya. Muat ulang halaman ini.',
    'inspectVerifying'     => 'Memeriksa berkas {0} dari {1}…',
    'inspectVerifyOk'      => 'Semua {0} berkas cocok. Paket siap dipakai.',
    'inspectVerifyBad'     => '{0} berkas hilang dan {1} berkas rusak. Tekan Unduh yang kurang.',
    'inspectVerifyNone'    => 'Belum ada paket di perangkat ini. Tekan Unduh yang kurang.',
    'inspectVerifyKeys'    => 'Berkas: {0}',
    'inspectRepairing'     => 'Mengunduh berkas {0} dari {1}…',
    'inspectRepairDone'    => 'Paket lengkap: {0} berkas diunduh, {1} berkas sudah ada.',
    'inspectClearTitle'    => 'Hapus paket di perangkat ini?',
    'inspectClearText'     => 'Semua gambar dan suara GELITA dihapus dari perangkat ini. Selama mode paket menyala, perangkat ini harus mengunduh paket lagi sebelum siswa dapat menekan Mulai.',
    'inspectClearConfirm'  => 'Hapus paket',
    'inspectClearDone'     => 'Paket sudah dihapus dari perangkat ini.',
    'inspectPersistOk'     => 'Peramban menyimpan paket secara permanen.',
    'inspectPersistNo'     => 'Peramban belum mengizinkan penyimpanan permanen. Paket tetap tersimpan, tetapi bisa terhapus bila ruang penyimpanan penuh.',
];
