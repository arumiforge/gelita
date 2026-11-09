<?php

/**
 * Digital-access questionnaire and the P5 online-risk items (research
 * revision 10, part 8: 34 §6.1, §7.7, §8, K2, K13).
 *
 * Copied as-is from docs/revisi-10-analitik/16_ANGKET_AKSES_DIGITAL.md
 * (Petunjuk untuk siswa, A–H, ang-01–ang-17, Keputusan pengembang:
 * ang-21–ang-23). Status `needs_verification`. Edit this file and raise
 * `version` after every change, then run `php spark gelita:instrumen:impor`.
 * Indonesian only (K13).
 *
 * Item `units` are the data columns (16 → Variabel): one per variable, 41 in
 * ang-01–ang-17 (K7). `single` items store the code of the option; `rows` or
 * `columns` make one unit per row/column. `multi` items store 1/0 per box.
 * Codes `8` (not applicable) and `9` (do not know) are options; `7` (do not
 * want to answer) exists only in the risk items (16).
 */

$parentCodes = [
    '3' => 'Hampir selalu',
    '2' => 'Kadang-kadang',
    '1' => 'Jarang',
    '0' => 'Tidak pernah',
    '8' => 'Aku tidak memakai HP atau internet di rumah',
    '9' => 'Tidak tahu',
];

$explainParent = 'Orang tua artinya ayah, ibu, atau orang dewasa yang merawatmu di rumah, misalnya kakek, nenek, om, atau tante.';
$explainHour   = 'Satu jam itu, misalnya, dari pukul empat sampai pukul lima sore.';
$explainDays   = 'Hari sekolah artinya hari ketika kamu masuk sekolah. Hari libur artinya hari Minggu atau hari ketika sekolah libur.';
$explainUnsure = 'Kalau tidak tahu, pilih Tidak tahu. Kalau tidak ingin menjawab, lewati saja.';

return [
    'version' => 'angket-0.1',
    'status'  => 'needs_verification',
    'source'  => 'docs/revisi-10-analitik 16 (3 November 2026)',
    'intro'   => [
        'paragraphs' => [
            'Halo! Bagian ini berisi pertanyaan tentang kebiasaanmu memakai HP, komputer, dan internet. Ini bukan tes. Tidak ada jawaban yang benar atau salah, dan tidak ada nilainya.',
            'Guru dan orang tuamu tidak akan melihat jawabanmu. Jawabanmu hanya dibaca tim peneliti. Jawabanmu disimpan dengan kode, bukan dengan namamu.',
            'Jawablah sesuai keadaanmu yang sebenarnya, ya. Kalau ada pertanyaan yang tidak ingin kamu jawab, lewati saja. Tidak apa-apa.',
        ],
        'steps' => [
            'Baca setiap pertanyaan, lalu pilih jawabanmu.',
            'Kalau kamu tidak tahu atau tidak ingat, pilih Tidak tahu atau Tidak ingat. Itu juga jawaban yang baik.',
            'Kalau ada kata yang kurang jelas, baca keterangan kecil di bawah pertanyaan, atau angkat tangan.',
            'Kamu boleh kembali ke pertanyaan sebelumnya dan mengganti jawaban sebelum menekan Selesai.',
        ],
    ],
    // One section per screen (16 → Cara pelaksanaan no. 2)
    'sections' => [
        'A' => ['title' => 'Perangkat dan internet di rumah', 'note' => null],
        'B' => ['title' => 'Lama dan waktu memakai', 'note' => null],
        'C' => ['title' => 'Kegiatan di internet', 'note' => null],
        'D' => ['title' => 'Akun dan media sosial', 'note' => null],
        'E' => ['title' => 'Pendampingan orang tua', 'note' => 'Orang tua di sini artinya ayah, ibu, atau orang dewasa yang merawatmu di rumah (misalnya kakek, nenek, om, atau tante).'],
        'F' => ['title' => 'Belajar tentang internet dan memakai alat di sekolah', 'note' => null],
        'G' => ['title' => 'Membaca di layar', 'note' => null],
        'H' => ['title' => 'Bahasa dan buku di rumah', 'note' => null],
    ],
    // Shown under every section screen (16 → Cara pelaksanaan no. 5, last standard explanation)
    'footnote' => $explainUnsure,
    'items' => [
        'ang-01' => [
            'part' => 'A', 'no' => 1, 'type' => 'single', 'units' => ['phone_access'],
            'show' => [
                'prompt'  => 'Apakah kamu punya HP sendiri?',
                'hint'    => 'Pilih satu.',
                'options' => [
                    '2' => 'Ya, aku punya HP sendiri',
                    '1' => 'Tidak, tapi aku memakai HP orang lain di rumah (misalnya HP ayah, ibu, atau kakak)',
                    '0' => 'Tidak, aku tidak memakai HP',
                ],
            ],
        ],
        'ang-02' => [
            'part' => 'A', 'no' => 2, 'type' => 'multi', 'units' => ['home_phone', 'home_tablet', 'home_computer', 'home_console', 'home_device_none'],
            'show' => [
                'prompt' => 'Di rumahmu, ada alat apa saja yang bisa kamu pakai?',
                'hint'   => 'Centang semua yang ada.',
                'boxes'  => [
                    'home_phone'       => 'HP (punyamu atau punya keluargamu)',
                    'home_tablet'      => 'Tablet',
                    'home_computer'    => 'Laptop atau komputer',
                    'home_console'     => 'Konsol game (misalnya PlayStation atau Nintendo)',
                    'home_device_none' => 'Tidak ada satu pun',
                ],
            ],
        ],
        'ang-03' => [
            'part' => 'A', 'no' => 3, 'type' => 'single', 'units' => ['home_internet'],
            'show' => [
                'prompt'  => 'Kalau kamu ingin memakai internet di rumah, apakah bisa?',
                'hint'    => 'Pilih satu.',
                'options' => ['3' => 'Hampir selalu bisa', '2' => 'Kadang-kadang bisa', '1' => 'Jarang bisa', '0' => 'Tidak pernah bisa', '9' => 'Tidak tahu'],
            ],
        ],
        'ang-04' => [
            'part' => 'B', 'no' => 4, 'type' => 'single', 'units' => ['first_use_grade'],
            'show' => [
                'prompt'  => 'Sejak kapan kamu mulai memakai HP atau tablet?',
                'hint'    => 'Pilih satu.',
                'options' => [
                    '4' => 'Sebelum masuk SD',
                    '3' => 'Kelas 1 atau kelas 2',
                    '2' => 'Kelas 3 atau kelas 4',
                    '1' => 'Kelas 5',
                    '0' => 'Aku belum pernah memakai HP atau tablet',
                    '9' => 'Tidak ingat',
                ],
            ],
        ],
        'ang-05' => [
            'part' => 'B', 'no' => 5, 'type' => 'single', 'units' => ['screen_time_school', 'screen_time_holiday'],
            'show' => [
                'prompt'  => 'Berapa lama kamu biasanya memakai HP, tablet, atau komputer di luar jam sekolah?',
                'hint'    => 'Pilih satu di setiap kolom.',
                'options' => ['0' => 'Tidak memakai', '1' => 'Kurang dari 1 jam', '2' => 'Sekitar 1–2 jam', '3' => 'Sekitar 3–4 jam', '4' => 'Lebih dari 4 jam', '9' => 'Tidak tahu'],
                'columns' => ['screen_time_school' => 'Hari sekolah', 'screen_time_holiday' => 'Hari Minggu atau hari libur'],
                'explain' => [$explainHour, $explainDays],
            ],
        ],
        'ang-06' => [
            'part' => 'B', 'no' => 6, 'type' => 'single', 'units' => ['game_time_school', 'game_time_holiday'],
            'show' => [
                'prompt'  => 'Berapa lama kamu biasanya bermain game di HP, tablet, komputer, atau konsol?',
                'hint'    => 'Pilih satu di setiap kolom.',
                'options' => ['0' => 'Tidak bermain game', '1' => 'Kurang dari setengah jam', '2' => 'Setengah jam sampai 1 jam', '3' => 'Lebih dari 1 jam sampai 2 jam', '4' => 'Lebih dari 2 jam', '9' => 'Tidak tahu'],
                'columns' => ['game_time_school' => 'Hari sekolah', 'game_time_holiday' => 'Hari Minggu atau hari libur'],
                'explain' => [$explainHour, $explainDays],
            ],
        ],
        'ang-07' => [
            'part' => 'C', 'no' => 7, 'type' => 'single', 'units' => ['act_video', 'act_game', 'act_chat', 'act_schoolwork', 'act_search', 'act_read', 'act_post'],
            'show' => [
                'prompt'  => 'Seberapa sering kamu melakukan hal-hal ini di HP, tablet, atau komputer?',
                'hint'    => 'Pilih satu di setiap baris.',
                'options' => ['2' => 'Hampir setiap hari', '1' => 'Kadang-kadang', '0' => 'Tidak pernah', '9' => 'Tidak tahu'],
                'rows'    => [
                    'act_video'      => 'a. Menonton video (misalnya di YouTube atau TikTok)',
                    'act_game'       => 'b. Bermain game',
                    'act_chat'       => 'c. Mengirim pesan atau menelepon keluarga dan teman (misalnya lewat WhatsApp)',
                    'act_schoolwork' => 'd. Mengerjakan tugas sekolah',
                    'act_search'     => 'e. Mencari tahu sesuatu (misalnya lewat Google)',
                    'act_read'       => 'f. Membaca cerita, berita, atau buku',
                    'act_post'       => 'g. Membuat foto atau video, lalu mengunggahnya ke internet',
                ],
            ],
        ],
        'ang-08' => [
            'part' => 'D', 'no' => 8, 'type' => 'single', 'units' => ['acct_whatsapp', 'acct_youtube', 'acct_tiktok', 'acct_instagram', 'acct_facebook', 'acct_game'],
            'show' => [
                'lead'    => 'Ada anak yang punya akun sendiri, ada yang memakai akun orang lain, ada juga yang tidak memakai aplikasi ini. Semua jawaban boleh.',
                'prompt'  => 'Bagaimana denganmu?',
                'hint'    => 'Pilih satu di setiap baris.',
                'options' => ['0' => 'Tidak memakai', '1' => 'Memakai, tapi bukan akunku sendiri', '2' => 'Punya akun sendiri', '9' => 'Tidak tahu'],
                'rows'    => [
                    'acct_whatsapp'  => 'a. WhatsApp',
                    'acct_youtube'   => 'b. YouTube',
                    'acct_tiktok'    => 'c. TikTok',
                    'acct_instagram' => 'd. Instagram',
                    'acct_facebook'  => 'e. Facebook',
                    'acct_game'      => 'f. Game online yang memakai akun (misalnya Roblox, Free Fire, atau Mobile Legends)',
                ],
                'explain' => ['Akun sendiri artinya akun yang dibuat untukmu dan biasanya hanya kamu yang memakainya.'],
            ],
        ],
        'ang-09' => [
            'part' => 'E', 'no' => 9, 'type' => 'single', 'units' => ['parent_couse'],
            'show' => [
                'prompt'  => 'Saat kamu memakai HP atau internet di rumah, seberapa sering orang tuamu menemanimu?',
                'hint'    => 'Pilih satu.',
                'options' => $parentCodes,
                'explain' => [$explainParent],
            ],
        ],
        'ang-10' => [
            'part' => 'E', 'no' => 10, 'type' => 'single', 'units' => ['parent_talk'],
            'show' => [
                'prompt'  => 'Seberapa sering orang tuamu mengobrol denganmu tentang hal yang kamu lihat atau lakukan di internet?',
                'hint'    => 'Pilih satu.',
                'options' => $parentCodes,
                'explain' => [$explainParent],
            ],
        ],
        'ang-11' => [
            'part' => 'E', 'no' => 11, 'type' => 'single', 'units' => ['parent_rule_time', 'parent_rule_content'],
            'show' => [
                'prompt'  => 'Apakah orang tuamu punya aturan tentang hal-hal ini?',
                'hint'    => 'Pilih satu di setiap baris.',
                'options' => ['1' => 'Ada aturan', '0' => 'Tidak ada aturan', '9' => 'Tidak tahu'],
                'rows'    => [
                    'parent_rule_time'    => 'a. Berapa lama kamu boleh memakai HP atau internet',
                    'parent_rule_content' => 'b. Aplikasi, game, atau tontonan yang boleh kamu buka',
                ],
                'explain' => [$explainParent],
            ],
        ],
        'ang-12' => [
            'part' => 'F', 'no' => 12, 'type' => 'multi',
            'units' => ['safety_info_parent', 'safety_info_teacher', 'safety_info_family', 'safety_info_friend', 'safety_info_media', 'safety_info_none', 'safety_info_dk'],
            'show' => [
                'prompt' => 'Siapa saja yang pernah memberi tahu kamu cara memakai internet dengan aman?',
                'hint'   => 'Centang semua yang benar.',
                'boxes'  => [
                    'safety_info_parent'  => 'Orang tua',
                    'safety_info_teacher' => 'Guru',
                    'safety_info_family'  => 'Kakak atau saudara',
                    'safety_info_friend'  => 'Teman',
                    'safety_info_media'   => 'Video, TV, atau internet',
                    'safety_info_none'    => 'Belum ada yang pernah memberi tahu',
                    'safety_info_dk'      => 'Tidak ingat',
                ],
            ],
        ],
        'ang-13' => [
            'part' => 'F', 'no' => 13, 'type' => 'single', 'units' => ['school_device_use'],
            'show' => [
                'prompt'  => 'Di sekolah, seberapa sering kamu memakai komputer, laptop, atau tablet untuk belajar?',
                'hint'    => 'Pilih satu.',
                'options' => ['2' => 'Hampir setiap minggu', '1' => 'Kadang-kadang', '0' => 'Belum pernah', '9' => 'Tidak ingat'],
            ],
        ],
        'ang-14' => [
            'part' => 'F', 'no' => 14, 'type' => 'single', 'units' => ['digital_quiz_exp'],
            'show' => [
                'prompt'  => 'Pernahkah kamu mengerjakan kuis atau soal di HP, tablet, atau komputer? (Misalnya kuis di kelas, Kahoot, atau latihan ANBK.)',
                'hint'    => 'Pilih satu.',
                'options' => ['2' => 'Sering', '1' => 'Pernah, satu atau dua kali', '0' => 'Belum pernah', '9' => 'Tidak ingat'],
            ],
        ],
        'ang-15' => [
            'part' => 'G', 'no' => 15, 'type' => 'single', 'units' => ['read_medium_pref'],
            'show' => [
                'prompt'  => 'Menurutmu, membaca di mana yang lebih mudah?',
                'hint'    => 'Pilih satu.',
                'options' => ['1' => 'Di kertas atau buku', '2' => 'Sama saja', '3' => 'Di layar HP, tablet, atau komputer', '9' => 'Tidak tahu'],
            ],
        ],
        'ang-16' => [
            'part' => 'H', 'no' => 16, 'type' => 'single', 'units' => ['home_indonesian'],
            'show' => [
                'prompt'  => 'Seberapa sering kamu memakai bahasa Indonesia saat berbicara di rumah?',
                'hint'    => 'Pilih satu.',
                'options' => ['3' => 'Selalu', '2' => 'Hampir selalu', '1' => 'Kadang-kadang', '0' => 'Tidak pernah'],
            ],
        ],
        'ang-17' => [
            'part' => 'H', 'no' => 17, 'type' => 'single', 'units' => ['books_home'],
            'show' => [
                'prompt'  => 'Kira-kira ada berapa buku di rumahmu? Jangan hitung majalah, koran, dan buku pelajaran sekolahmu.',
                'hint'    => 'Pilih satu.',
                'options' => [
                    '1' => 'Tidak ada atau sangat sedikit (0–10 buku)',
                    '2' => 'Cukup untuk satu rak (11–25 buku)',
                    '3' => 'Cukup untuk satu lemari buku (26–100 buku)',
                    '4' => 'Cukup untuk dua lemari buku (101–200 buku)',
                    '5' => 'Cukup untuk tiga lemari buku atau lebih (lebih dari 200 buku)',
                    '9' => 'Tidak tahu',
                ],
            ],
        ],

        // P5 online-risk items, after T11 (16 → Keputusan pengembang no. 2; 34 §8). How many (two or three)
        // follows this file; the module does not assume a fixed number (16 → Keputusan pengembang no. 2).
        'ang-21' => [
            'part' => 'risk', 'no' => 1, 'type' => 'single', 'units' => ['risk_upset'],
            'show' => [
                'prompt'  => 'Dalam setahun ini, pernahkah ada sesuatu di internet atau HP yang membuatmu tidak nyaman, takut, atau sedih?',
                'hint'    => 'Pilih satu.',
                'options' => ['0' => 'Tidak pernah', '1' => 'Pernah, sekali atau dua kali', '2' => 'Beberapa kali atau lebih', '9' => 'Tidak tahu', '7' => 'Tidak mau menjawab'],
            ],
        ],
        'ang-22' => [
            'part' => 'risk', 'no' => 2, 'type' => 'single', 'units' => ['risk_stranger_contact'],
            'show' => [
                'prompt'  => 'Dalam setahun ini, pernahkah orang yang tidak kamu kenal di dunia nyata mengirim pesan kepadamu lewat internet atau HP?',
                'hint'    => 'Pilih satu.',
                'options' => ['0' => 'Tidak', '1' => 'Ya', '9' => 'Tidak tahu', '7' => 'Tidak mau menjawab'],
            ],
        ],
        'ang-23' => [
            'part' => 'risk', 'no' => 3, 'type' => 'multi',
            'units' => ['support_parent', 'support_family', 'support_friend', 'support_teacher', 'support_adult', 'support_none', 'support_na'],
            'show' => [
                'prompt'  => 'Kalau ada sesuatu di internet yang membuatmu tidak nyaman, kepada siapa kamu biasanya bercerita?',
                'hint'    => 'Centang semua yang benar.',
                'boxes'   => [
                    'support_parent'  => 'Orang tua',
                    'support_family'  => 'Kakak atau saudara',
                    'support_friend'  => 'Teman',
                    'support_teacher' => 'Guru',
                    'support_adult'   => 'Orang dewasa lain',
                    'support_none'    => 'Aku tidak bercerita kepada siapa pun',
                    'support_na'      => 'Belum pernah ada hal seperti itu',
                ],
                // 34 §8.1 no. 3: "Tidak mau menjawab" (code 7) on every risk item; here it writes 7 into every box
                'decline' => 'Tidak mau menjawab',
            ],
        ],
    ],
    'risk_intro' => 'Pertanyaan berikut tentang hal-hal yang bisa terjadi di internet. Kalau tidak ingin menjawab, pilih "Tidak mau menjawab" atau lewati saja.',
];
