<?php

/**
 * Bank soal kelas 5 — TEMANGGUNG (wilayah 1, tingkat mudah).
 * Fokus literasi: menemukan informasi yang tersurat di dalam teks.
 *
 * Isi final yang disetujui peneliti sebagai bank soal penelitian kelas 5
 * (docs/perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md §9, Oktober 2026).
 * Jangan diubah kecuali untuk memperbaiki salah ketik yang jelas.
 *
 * Bentuk data dijelaskan di docs/bank-soal/README.md. Teks berpasangan
 * [Indonesia, English]. Kode soal {node}-{variasi}{nn} (mis. tmg-2-a01) dan kode
 * bacaan {node}-{variasi} (tmg-3-a) diberi build-workbook.php menurut urutan di
 * berkas ini — jangan menyisipkan atau menghapus soal di tengah variasi setelah
 * bank dipakai siswa. Potongan Isi Rumpang ditulis persis seperti disimpan:
 * ___ = tempat kosong, "\n\n" = jeda paragraf di tengah potongan sesudah ___.
 * `bloom_reason` hanya catatan penyusun (tidak ikut workbook).
 */

$ref = [
    'geo'     => 'Pemkab Temanggung (profil daerah); BPS Kab. Temanggung; data ketinggian gunung PVMBG',
    'jumprit' => 'Wikipedia: Umbul Jumprit; Mongabay (2018) Berkah Air dari Umbul Jumprit; Perhutani',
    'art'     => 'DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Pemprov Jateng: Nyadran Jaran Kepang',
    'history' => 'Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jateng',
    'coffee'  => 'Dinas Pertanian Kab. Temanggung; MPIG Kopi Java Arabika Sindoro Sumbing',
    'hunt'    => 'DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Dinas Pertanian Kab. Temanggung; MPIG Kopi Java Arabika Sindoro Sumbing; Pemkab Temanggung (Sejarah Temanggung); BPK Wilayah X Jateng; DJKI (indikasi geografis ikan uceng)',
];

return [
    'difficulty' => 'mudah',

    'nodes' => [
        // ----------------------------------------------- tmg-1 Susun Gambar
        'tmg-1' => [
            'engine'      => 'puzzle',
            'variant'     => 'susun_gambar',
            'indicator'   => 'budaya',
            'title'       => ['Susun Gambar Temanggung', 'Temanggung Picture Puzzle'],
            'instruction' => [
                'Ketuk dua keping untuk menukar tempatnya. Susun sampai gambarnya utuh, lalu baca keterangannya.',
                'Tap two pieces to swap them. Keep going until the picture is complete, then read the caption.',
            ],
            'description' => [
                'Angin gunung mengacak sebuah lukisan tentang Temanggung. Bantu Jaka menyusunnya kembali, lalu baca keterangannya.',
                'A mountain wind has scrambled a painting of Temanggung. Help Jaka put it back together, then read its caption.',
            ],
            'hints'       => [
                [
                    'Mulailah dari kepingan sudut: sudut punya dua sisi yang menyentuh bingkai.',
                    'Start with the corner pieces: a corner touches the frame on two sides.',
                ],
                ['Cocokkan warna dan garis pada kepingan yang bersebelahan.', 'Match the colours and lines on neighbouring pieces.'],
                ['Selesaikan baris paling atas dulu, lalu turun baris demi baris.', 'Finish the top row first, then work down row by row.'],
            ],
            'variants' => [
                'a' => [
                    'title' => ['Gunung Sindoro dan Sumbing', 'Mount Sindoro and Mount Sumbing'],
                    'ref'   => $ref['geo'],
                    'items' => [
                        [   // tmg-1-a01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Gunung Sindoro (3.136 m) dan Gunung Sumbing (3.371 m) disebut gunung kembar. Keduanya mengapit Temanggung dan membuat tanahnya subur.',
                                'Mount Sindoro (3,136 m) and Mount Sumbing (3,371 m) are called the twin mountains. They stand on either side of Temanggung and make its soil fertile.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-1-01', 'label' => 'Gunung Sindoro dan Sumbing', 'note' => 'lanskap Gunung Sindoro & Sumbing, langit cerah, kebun di kaki gunung', 'source' => 'https://commons.wikimedia.org/wiki/File:Sindoro_sumbing.jpg (cek lisensi & catat kredit)'],
                        ],
                    ],
                ],
                'b' => [
                    'title' => ['Candi Pringapus', 'Pringapus Temple'],
                    'ref'   => $ref['history'],
                    'items' => [
                        [   // tmg-1-b01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Pringapus adalah candi Hindu dari abad ke-9. Di dalam ruang candinya tersimpan arca Nandi, arca lembu yang dihormati umat Hindu.',
                                'Pringapus Temple is a Hindu temple from the 9th century. Its chamber holds a statue of Nandi, the bull honoured by Hindus.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-1-03', 'label' => 'Candi Pringapus', 'note' => 'Candi Pringapus tampak depan', 'source' => 'https://commons.wikimedia.org/wiki/File:Candi_Pringapus_Temanggung_Jateng.jpg'],
                        ],
                    ],
                ],
                'c' => [
                    'title' => ['Kuda Lumping', 'Kuda Lumping'],
                    'ref'   => $ref['art'],
                    'items' => [
                        [   // tmg-1-c01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kuda lumping atau jaran kepang memakai kuda-kudaan dari anyaman bambu. Di Temanggung ada lebih dari 1.500 kelompok kuda lumping.',
                                'Kuda lumping, or jaran kepang, uses horses made of woven bamboo. Temanggung has more than 1,500 kuda lumping groups.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-1-04', 'label' => 'Kuda Lumping', 'note' => 'penari kuda lumping dengan kuda anyaman bambu berwarna', 'source' => 'https://commons.wikimedia.org/wiki/File:Kuda_lumping_Temanggung.jpg'],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------------ tmg-2 Isi Rumpang
        'tmg-2' => [
            'engine'           => 'rumpang',
            'variant'          => 'rumpang_bacaan',
            'indicator'        => 'literasi',
            'distractor_count' => 0,
            'title'            => ['Lengkapi Bacaan Temanggung', 'Complete the Temanggung Reading'],
            'instruction'      => [
                'Baca paragrafnya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Setiap kata dipakai satu kali.',
                'Read the paragraph. Tap an empty box, then tap the right word. Each word is used once.',
            ],
            'description'      => [
                'Catatan Jaka tentang Temanggung ada yang terhapus hujan. Lengkapi bacaannya supaya bisa dibaca lagi.',
                'Some of Jaka\'s notes about Temanggung were washed away by the rain. Complete the reading so it can be read again.',
            ],
            'hints'            => [
                ['Baca seluruh paragraf sampai habis dulu, baru isi kotaknya.', 'Read the whole paragraph first, then fill in the boxes.'],
                [
                    'Perhatikan kata-kata di sekitar kotak kosong. Kadang jawabannya sudah disebut di kalimat sebelumnya.',
                    'Look at the words around each box. Sometimes the answer was already mentioned in an earlier sentence.',
                ],
                ['Setiap kata di bank kata dipakai satu kali.', 'Each word in the word bank is used once.'],
            ],
            'variants' => [
                'a' => [
                    'title'       => ['Gunung Kembar', 'The Twin Mountains'],
                    'ref'         => $ref['geo'],
                    'distractors' => [],
                    'items'       => [
                        [   // tmg-2-a01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C1',
                            'prompt'       => [
                                'Kabupaten Temanggung berada di Provinsi Jawa Tengah. Wilayahnya diapit dua gunung besar, yaitu Gunung Sindoro dan Gunung ___.',
                                'Temanggung Regency is in the province of Central Java. It lies between two large mountains, Mount Sindoro and Mount ___.',
                            ],
                            'answer'       => ['Sumbing', 'Sumbing'],
                            'bloom_reason' => 'Mengingat nama gunung yang dikenalkan Mbah Kedu di Kenali Temanggung.',
                        ],
                        [   // tmg-2-a02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Karena bentuknya mirip, kedua gunung itu sering disebut gunung ___.',
                                'Because they look alike, the two mountains are often called the ___ mountains.',
                            ],
                            'answer'       => ['kembar', 'twin'],
                            'bloom_reason' => 'Memahami: disimpulkan dari "bentuknya mirip".',
                        ],
                        [   // tmg-2-a03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Tanah di lerengnya ___ karena berasal dari abu gunung api. Petani pun dapat menanam kopi dan sayuran di sana.',
                                'The soil on their slopes is ___ because it comes from volcanic ash, so farmers can grow coffee and vegetables there.',
                            ],
                            'answer'       => ['subur', 'fertile'],
                            'bloom_reason' => 'Memahami sebab (abu gunung api) dan akibat (dapat menanam).',
                        ],
                    ],
                ],
                'b' => [
                    'title'       => ['Umbul Jumprit', 'Umbul Jumprit'],
                    'ref'         => $ref['jumprit'],
                    'distractors' => [],
                    'items'       => [
                        [   // tmg-2-b01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Di lereng Gunung Sindoro ada mata air bernama Umbul Jumprit. Airnya jernih dan tidak pernah kering, bahkan pada musim ___.',
                                'On the slopes of Mount Sindoro there is a spring called Umbul Jumprit. Its water is clear and never dries up, even in the ___ season.',
                            ],
                            'answer'       => ['kemarau', 'dry'],
                            'bloom_reason' => 'Memahami: "tidak pernah kering, bahkan pada musim …" menunjuk musim yang biasanya kering.',
                        ],
                        [   // tmg-2-b02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C1',
                            'prompt'       => [
                                'Air dari Umbul ___ mengalir menjadi hulu Sungai Progo.',
                                'The water from Umbul ___ flows on to become the source of the Progo River.',
                            ],
                            'answer'       => ['Jumprit', 'Jumprit'],
                            'bloom_reason' => 'Mengenali kembali nama yang sudah disebut di kalimat pertama.',
                        ],
                        [   // tmg-2-b03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Warga menjaga mata air itu dengan tidak membuang ___ di sekitarnya.',
                                'People look after the spring by never throwing ___ around it.',
                            ],
                            'answer'       => ['sampah', 'rubbish'],
                            'bloom_reason' => 'Memahami cara menjaga mata air.',
                        ],
                    ],
                ],
                'c' => [
                    'title'       => ['Kuda Lumping', 'Kuda Lumping'],
                    'ref'         => $ref['art'],
                    'distractors' => [],
                    'items'       => [
                        [   // tmg-2-c01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C1',
                            'prompt'       => [
                                'Kuda lumping disebut juga jaran kepang. Penarinya menari sambil menunggang kuda-kudaan dari anyaman bambu. Kuda-kudaan ___ itu dicat dan dihias dengan kain warna-warni.',
                                'Kuda lumping is also called jaran kepang. The dancers dance while riding horses made of woven bamboo. The ___ horses are painted and decorated with colourful cloth.',
                            ],
                            'answer'       => ['bambu', 'bamboo'],
                            'bloom_reason' => 'Mengenali kata yang baru saja disebut ("anyaman bambu").',
                        ],
                        [   // tmg-2-c02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Di Temanggung ada lebih dari 1.500 kelompok kuda lumping, jadi kesenian ini sangat ___.',
                                'Temanggung has more than 1,500 kuda lumping groups, so this art is very ___.',
                            ],
                            'answer'       => ['populer', 'popular'],
                            'bloom_reason' => 'Memahami: banyaknya kelompok menunjukkan kesenian itu digemari.',
                        ],
                        [   // tmg-2-c03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Agar kesenian ini tidak hilang, anak-anak muda diajak ___ menari kuda lumping.',
                                'So that this art does not disappear, young people are invited to ___ the kuda lumping dance.',
                            ],
                            'answer'       => ['berlatih', 'practise'],
                            'bloom_reason' => 'Memahami tujuan ("agar tidak hilang").',
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------- tmg-3 Benar atau Salah
        'tmg-3' => [
            'engine'      => 'boleh',
            'variant'     => 'benar_salah_bacaan',
            'indicator'   => 'literasi',
            'title'       => ['Benar atau Salah? Temanggung', 'True or False? Temanggung'],
            'instruction' => [
                'Baca bacaannya, lalu pilih BENAR atau SALAH untuk setiap pernyataan. Ada pernyataan yang memakai gambar.',
                'Read the text, then choose TRUE or FALSE for each statement. One statement uses a picture.',
            ],
            'description' => [
                'Warga di pasar Temanggung bercerita macam-macam. Cocokkan setiap cerita dengan bacaan: benar atau salah?',
                'People at the Temanggung market tell all sorts of stories. Check each one against the reading: true or false?',
            ],
            'hints'       => [
                [
                    'Cari kata kunci pernyataan (nama, angka, tempat) di dalam bacaan.',
                    'Find the key words of the statement (names, numbers, places) in the reading.',
                ],
                [
                    'Pernyataan BENAR bila isinya sama dengan bacaan. Satu kata atau angka yang berbeda membuatnya SALAH.',
                    'A statement is TRUE when it says the same as the reading. One different word or number makes it FALSE.',
                ],
                [
                    'Untuk pernyataan bergambar, cocokkan isi gambar dengan benda yang disebut di bacaan.',
                    'For a picture statement, match what the picture shows with the thing named in the reading.',
                ],
            ],
            'variants' => [
                'a' => [
                    'passage' => [
                        'title' => ['Situs Liyangan', 'The Liyangan Site'],
                        'body'  => [
                            'Situs Liyangan berada di Desa Purbosari, Kecamatan Ngadirejo, Temanggung. Situs ini ditemukan pada tahun 2008 ketika warga sedang menambang pasir. Di dalam tanah, para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi yang hangus. Semua itu tertimbun material letusan Gunung Sindoro pada masa lalu.',
                            'The Liyangan Site is in Purbosari Village, Ngadirejo District, Temanggung. It was found in 2008 while villagers were digging for sand. Under the ground, researchers found the remains of wooden houses, temple buildings, stone paths, and burnt grains of rice. All of it had been buried by material from an eruption of Mount Sindoro long ago.',
                        ],
                        'ref'   => $ref['history'],
                    ],
                    'items'   => [
                        [   // tmg-3-a01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'answer' => 'benar',
                            'prompt' => ['Situs Liyangan ditemukan pada tahun 2008.', 'The Liyangan Site was found in 2008.'],
                        ],
                        [   // tmg-3-a02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C1',
                            'skill'  => 'mengenali_fakta',
                            'answer' => 'salah',
                            'prompt' => [
                                'Situs Liyangan tertimbun material letusan Gunung Merapi.',
                                'The Liyangan Site was buried by material from an eruption of Mount Merapi.',
                            ],
                        ],
                        [   // tmg-3-a03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_gambar',
                            'answer' => 'benar',
                            'prompt' => [
                                'Gambar ini menunjukkan salah satu benda yang ditemukan di Situs Liyangan.',
                                'This picture shows one of the things found at the Liyangan Site.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-3-a03', 'label' => 'Butir padi hangus', 'note' => 'butir padi hangus kehitaman (temuan arkeologi)'],
                        ],
                    ],
                ],
                'b' => [
                    'passage' => [
                        'title' => ['Candi Pringapus dan Prasasti Gondosuli', 'Pringapus Temple and the Gondosuli Inscription'],
                        'body'  => [
                            'Candi Pringapus adalah candi Hindu peninggalan abad ke-9 di Temanggung. Di dalam ruang candinya ada arca Nandi, yaitu arca lembu yang dihormati umat Hindu. Temanggung juga punya Prasasti Gondosuli yang dibuat pada tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.',
                            'Pringapus Temple is a Hindu temple from the 9th century in Temanggung. Inside its chamber is a statue of Nandi, the bull honoured by Hindus. Temanggung also has the Gondosuli Inscription, made in the year 832 CE. An inscription is writing on stone or metal that records an important event.',
                        ],
                        'ref'   => $ref['history'],
                    ],
                    'items'   => [
                        [   // tmg-3-b01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C1',
                            'skill'  => 'mengenali_fakta',
                            'answer' => 'salah',
                            'prompt' => ['Candi Pringapus adalah candi Buddha.', 'Pringapus Temple is a Buddhist temple.'],
                        ],
                        [   // tmg-3-b02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'answer' => 'benar',
                            'prompt' => ['Prasasti Gondosuli dibuat pada tahun 832 Masehi.', 'The Gondosuli Inscription was made in 832 CE.'],
                        ],
                        [   // tmg-3-b03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_gambar',
                            'answer' => 'salah',
                            'prompt' => [
                                'Gambar ini menunjukkan arca yang ada di dalam ruang Candi Pringapus.',
                                'This picture shows the statue inside the chamber of Pringapus Temple.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-3-b03', 'label' => 'Arca Buddha duduk', 'note' => 'arca Buddha duduk dari batu (sengaja tidak sesuai bacaan)'],
                        ],
                    ],
                ],
                'c' => [
                    'passage' => [
                        'title' => ['Kopi dari Lereng Gunung', 'Coffee from the Mountain Slopes'],
                        'body'  => [
                            'Petani di lereng Gunung Sindoro dan Gunung Sumbing menanam kopi. Kopi arabika dari lereng itu tumbuh di ketinggian lebih dari 900 meter. Setelah dipetik, buah kopi diolah dan dikeringkan. Bijinya lalu disangrai sampai berwarna cokelat dan harum. Kopi mengandung kafein, jadi kopi adalah minuman untuk orang dewasa, bukan untuk anak-anak.',
                            'Farmers on the slopes of Mount Sindoro and Mount Sumbing grow coffee. Arabica coffee from those slopes grows at more than 900 metres above sea level. After it is picked, the coffee fruit is processed and dried. The beans are then roasted until they are brown and fragrant. Coffee contains caffeine, so it is a drink for adults, not for children.',
                        ],
                        'ref'   => $ref['coffee'],
                    ],
                    'items'   => [
                        [   // tmg-3-c01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'answer' => 'benar',
                            'prompt' => [
                                'Kopi arabika di lereng Sindoro dan Sumbing tumbuh di ketinggian lebih dari 900 meter.',
                                'Arabica coffee on the slopes of Sindoro and Sumbing grows at more than 900 metres.',
                            ],
                        ],
                        [   // tmg-3-c02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C1',
                            'skill'  => 'mengenali_fakta',
                            'answer' => 'salah',
                            'prompt' => [
                                'Menurut bacaan, kopi adalah minuman yang cocok untuk anak-anak.',
                                'According to the text, coffee is a drink that suits children.',
                            ],
                        ],
                        [   // tmg-3-c03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_gambar',
                            'answer' => 'benar',
                            'prompt' => ['Gambar ini menunjukkan biji kopi yang sudah disangrai.', 'This picture shows coffee beans that have been roasted.'],
                            'media'  => ['key' => 'challenge.item.tmg-3-c03', 'label' => 'Biji kopi sangrai', 'note' => 'biji kopi sangrai berwarna cokelat'],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------------- tmg-4 Cari Objek
        'tmg-4' => [
            'engine'      => 'cari',
            'variant'     => 'cari_objek',
            'indicator'   => 'budaya',
            'title'       => ['Cari Objek Temanggung', 'Find the Objects: Temanggung'],
            'instruction' => [
                'Baca petunjuk Mbah Kedu, lalu ketuk benda yang tepat. Ada empat benda yang harus kamu temukan.',
                'Read Mbah Kedu\'s clue, then tap the right object. There are four objects to find.',
            ],
            'description' => [
                'Di pasar budaya, benda khas Temanggung tercampur dengan benda dari daerah lain. Temukan empat benda yang dimaksud Mbah Kedu.',
                'At the culture fair, typical Temanggung objects are mixed with objects from other regions. Find the four objects Mbah Kedu describes.',
            ],
            'scene'       => ['key' => 'challenge.tmg-4.scene', 'label' => 'Pasar Budaya Temanggung', 'note' => 'halaman balai desa/pasar budaya di kaki Sindoro–Sumbing, tiga meja panjang kosong berjajar ke depan (permukaan meja ± 47 %, 61 %, 76 % tinggi gambar)', 'source' => 'Ilustrasi buatan tim / ilustrator (hindari foto dengan benda yang sudah ada)'],
            'hints'       => [
                [
                    'Baca petunjuknya pelan-pelan: nama, bahan, atau kegunaan benda disebut di sana.',
                    'Read the clue slowly: it names the object, its material, or its use.',
                ],
                ['Ingat cerita Mbah Kedu di Kenali Temanggung.', 'Remember Mbah Kedu\'s story in Get to Know Temanggung.'],
                [
                    'Benda dari daerah lain juga berharga, tetapi bukan jawaban tantangan ini.',
                    'Objects from other regions are valuable too, but they are not the answers in this challenge.',
                ],
            ],
            'variants' => [
                'a' => [
                    'title' => ['Pasar Budaya Temanggung', 'Temanggung Culture Fair'],
                    'ref'   => $ref['hunt'],
                    'items' => [
                        [   // tmg-4-a01
                            'type'   => 'find_object',
                            'bloom'  => 'C1',
                            'x'      => 76,
                            'y'      => 31,
                            'w'      => 10,
                            'prompt' => [
                                'Temukan kuda-kudaan dari anyaman bambu yang dipakai untuk menari kuda lumping.',
                                'Find the woven bamboo horse used for the kuda lumping dance.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-03', 'label' => 'Kuda kepang bambu', 'note' => 'kuda kepang anyaman bambu berwarna (tanpa penari)'],
                        ],
                        [   // tmg-4-a02
                            'type'   => 'find_object',
                            'bloom'  => 'C1',
                            'x'      => 14,
                            'y'      => 31,
                            'w'      => 10,
                            'prompt' => [
                                'Temukan biji kopi yang tumbuh di lereng Gunung Sindoro dan Sumbing.',
                                'Find the coffee beans that grow on the slopes of Mount Sindoro and Mount Sumbing.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-02', 'label' => 'Biji kopi', 'note' => 'segenggam biji kopi'],
                        ],
                        [   // tmg-4-a03
                            'type'   => 'find_object',
                            'bloom'  => 'C1',
                            'x'      => 12,
                            'y'      => 56.5,
                            'w'      => 12,
                            'prompt' => [
                                'Temukan arca lembu Nandi, seperti yang ada di dalam Candi Pringapus.',
                                'Find the statue of Nandi the bull, like the one inside Pringapus Temple.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-06', 'label' => 'Arca Nandi', 'note' => 'arca lembu Nandi duduk dari batu'],
                        ],
                        [   // tmg-4-a04
                            'type'   => 'find_object',
                            'bloom'  => 'C2',
                            'x'      => 29,
                            'y'      => 43,
                            'w'      => 11,
                            'prompt' => [
                                'Temukan ikan uceng, ikan sungai kecil yang menjadi makanan khas Temanggung.',
                                'Find the uceng fish, a small river fish that is a special food of Temanggung.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-04', 'label' => 'Ikan uceng', 'note' => 'beberapa ikan uceng kecil memanjang'],
                        ],
                        [   // tmg-4-a05 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 76,
                            'y'      => 56.5,
                            'w'      => 12,
                            'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'],
                            'wrong'  => [
                                'Ini angklung, alat musik bambu dari Jawa Barat — bukan benda yang dimaksud petunjuk.',
                                'This is an angklung, a bamboo instrument from West Java — not the object in the clue.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-09', 'label' => 'Angklung', 'note' => 'angklung bambu'],
                        ],
                        [   // tmg-4-a06 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 44,
                            'y'      => 56.5,
                            'w'      => 12,
                            'prompt' => ['Ondel-ondel (pengecoh)', 'Ondel-ondel (decoy)'],
                            'wrong'  => [
                                'Ini ondel-ondel, boneka raksasa dari budaya Betawi di Jakarta — bukan dari Temanggung.',
                                'This is an ondel-ondel, a giant puppet from Betawi culture in Jakarta — not from Temanggung.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-10', 'label' => 'Ondel-ondel', 'note' => 'ondel-ondel kecil'],
                        ],
                        [   // tmg-4-a07 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 45,
                            'y'      => 31,
                            'w'      => 10,
                            'prompt' => ['Perahu pinisi (pengecoh)', 'Pinisi boat (decoy)'],
                            'wrong'  => [
                                'Ini perahu pinisi dari Sulawesi Selatan. Temanggung ada di pegunungan, jauh dari laut.',
                                'This is a pinisi boat from South Sulawesi. Temanggung is in the mountains, far from the sea.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-11', 'label' => 'Perahu pinisi', 'note' => 'perahu pinisi berlayar'],
                        ],
                        [   // tmg-4-a08 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 60,
                            'y'      => 43,
                            'w'      => 11,
                            'prompt' => ['Sasando (pengecoh)', 'Sasando (decoy)'],
                            'wrong'  => [
                                'Ini sasando, alat musik petik dari Pulau Rote, Nusa Tenggara Timur — bukan dari Temanggung.',
                                'This is a sasando, a plucked instrument from Rote Island, East Nusa Tenggara — not from Temanggung.',
                            ],
                            'media'  => ['key' => 'challenge.item.tmg-4-12', 'label' => 'Sasando', 'note' => 'sasando'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
