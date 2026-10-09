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
];
