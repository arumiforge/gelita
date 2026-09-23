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
    'errNetwork'      => 'Koneksi ke server terputus. Periksa internet sekolah, lalu coba lagi.',
    'errNetworkRetry' => 'Koneksi terputus. Tunggu sebentar, lalu ketuk lagi.',
    'errUnknown'      => 'Ada yang tidak beres. Coba muat ulang halaman ini.',
    'errRateLimited'  => 'Terlalu cepat. Tunggu sebentar, lalu coba lagi.',
    'sessionTitle'    => 'Sesi berakhir',
    'sessionExpired'  => 'Sesimu sudah berakhir. Silakan masuk lagi.',
    'pageExpiredTitle' => 'Halaman kedaluwarsa',
    'reloadPage'      => 'Muat ulang',
    'loginAgain'      => 'Masuk lagi',
    'savedOffline'    => 'Koneksi terputus — langkahmu tersimpan sementara dan dikirim saat koneksi kembali.',

    // HUD & audio
    'soundOn'      => 'Suara menyala',
    'soundOff'     => 'Suara mati',
    'audioNoSound' => 'Suara belum dapat diputar. Baca teksnya, ya.',

    // Refleksi
    'needTwo' => 'Isi minimal dua pertanyaan, ya.',

    // Kerangka tantangan
    'itemsPending'  => 'Masih ada soal yang belum dijawab.',
    'noRetry'       => 'Tantangan ini hanya boleh dijawab sekali. Jawabanmu sudah tersimpan.',
    'fixAnswers'    => 'Perbaiki',
    'finishAnyway'  => 'Selesaikan dengan jawaban ini',
    'hintTitle'     => 'Petunjuk',
    'hintPenalty'   => 'Memakai petunjuk akan sedikit menurunkan nilai kemandirianmu. Buka petunjuk?',
    'hintOpen'      => 'Buka petunjuk',
    'hintAllOpened' => 'Semua petunjuk sudah kamu buka:',
    'hintNone'      => 'Belum ada petunjuk untuk soal ini.',
    'hintUsed'      => 'petunjuk dipakai',
    'exitTitle'     => 'Tinggalkan tantangan ini?',
    'exitText'      => 'Jawabanmu yang sudah diperiksa tetap tersimpan, tetapi tantangan ini belum selesai.',
    'exitYes'       => 'Ya, keluar',
    'exitNo'        => 'Lanjutkan tantangan',

    // Puzzle & urutan
    'piecesSwapped'    => 'Keping {0} dan {1} ditukar.',
    'puzzleOkTitle'    => 'Gambarnya utuh!',
    'puzzleOkText'     => 'Semua keping sudah di tempatnya.',
    'puzzleWrongTitle' => 'Belum utuh',
    'puzzleWrongText'  => 'Gambarnya belum utuh, masih ada {0} keping yang keliru. Keping bergaris merah belum di tempatnya.',
    'puzzleWrongPlain' => 'Gambarnya belum utuh. Tukar lagi kepingnya, ya.',
    'cardMoved'        => 'Kartu dipindah ke urutan {0} dari {1}.',
    'orderOkTitle'     => 'Urutannya tepat!',
    'orderOkText'      => 'Semua langkah sudah berurutan.',
    'orderWrongTitle'  => 'Urutan belum tepat',
    'orderWrongText'   => '{0} dari {1} kartu sudah di tempat yang benar. Geser lagi kartunya, ya.',
    'orderWrongPlain'  => 'Urutannya belum tepat. Geser lagi kartunya, ya.',

    // Rumpang
    'blankFilled'     => '{0} diisi: {1}',
    'blankEmptyTitle' => 'Masih ada yang kosong',
    'blankEmptyText'  => 'Ada {0} kotak yang belum diisi. Isi semua kotak dulu, ya.',
    'blankOkTitle'    => 'Semua kalimat benar!',
    'blankOkText'     => 'Kamu mengisi semua bagian rumpang dengan tepat.',
    'blankWrongTitle' => 'Belum semuanya tepat',
    'blankWrongText'  => '{0} dari {1} sudah benar. Ketuk kotak merah untuk menggantinya.',

    // Boleh
    'readText'         => 'Baca teks',
    'cardsEmptyTitle'  => 'Masih ada kartu kosong',
    'cardsEmptyText'   => 'Ada {0} kartu yang belum kamu tentukan.',
    'cardsOkTitle'     => 'Semua kartu tepat!',
    'cardsOkText'      => 'Kamu menilai semua pernyataan dengan tepat.',
    'cardsWrongTitle'  => 'Belum semuanya tepat',
    'cardsWrongText'   => '{0} dari {1} kartu sudah tepat. Kartu bergaris merah perlu kamu pikirkan lagi.',
    'reasonSaved'      => 'Alasanmu tersimpan',

    // Cari
    'clueLabel'      => 'Petunjuk {0}',
    'huntFoundMark'  => 'ditemukan',
    'huntDoneTitle'  => 'Semua objek ketemu!',
    'huntDoneText'   => 'Kamu menemukan semua warisan budaya yang dicari.',
    'huntDecoyTitle' => 'Itu bukan dari Kedu',
    'huntDecoyText'  => 'Benda itu memang budaya Indonesia, tetapi berasal dari daerah lain.',
    'huntOtherTitle' => 'Bukan yang ini',
    'huntOtherText'  => 'Benda itu juga warisan {0}, tetapi bukan yang dimaksud petunjuk kali ini.',
];
