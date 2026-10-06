<?php

/**
 * Bank soal kelas 5 — MAGELANG (wilayah 2, tingkat sedang).
 * Fokus literasi: menghubungkan informasi dan menentukan gagasan pokok.
 *
 * Isi final yang disetujui peneliti sebagai bank soal penelitian kelas 5
 * (docs/perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md §9, Oktober 2026).
 * Jangan diubah kecuali untuk memperbaiki salah ketik yang jelas.
 *
 * Bentuk data dijelaskan di docs/bank-soal/README.md. Teks berpasangan
 * [Indonesia, English]. Kode soal {node}-{variasi}{nn} (mis. mgl-2-a01) dan kode
 * bacaan {node}-{variasi} (mgl-3-a) diberi build-workbook.php menurut urutan di
 * berkas ini — jangan menyisipkan atau menghapus soal di tengah variasi setelah
 * bank dipakai siswa. Potongan Isi Rumpang ditulis persis seperti disimpan:
 * ___ = tempat kosong, "\n\n" = jeda paragraf di tengah potongan sesudah ___.
 * `bloom_reason` hanya catatan penyusun (tidak ikut workbook).
 */

$ref = [
    'borobudur' => 'UNESCO World Heritage Centre (No. 592); Kemenag Bimas Buddha; Kompas Travel (2025)',
    'topeng'    => 'detikEdu (2022) Tari Topeng Ireng; Visit Jawa Tengah; Kompas Regional (2024)',
    'waisak'    => 'BPK Wilayah X; visitmagelang.id; Liputan6 (Umbul Jumprit & Waisak)',
    'klipoh'    => 'Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur; detikFood (2025)',
    'tidar'     => 'Tirto.id (Sejarah Gunung Tidar); Kompas Regional (2024); Pemkot Magelang (Prasasti Mantyasih)',
    'hunt'      => 'UNESCO World Heritage Centre (No. 592); detikEdu (2022) Tari Topeng Ireng; Kompas Travel (2022) Desa Klipoh; detikFood (2025); Pemkab Ponorogo (Reog) untuk pengecoh dadak merak',
];

return [
    'difficulty' => 'sedang',

    'nodes' => [
        // ----------------------------------------------- mgl-1 Susun Gambar
        'mgl-1' => [
            'engine'      => 'puzzle',
            'variant'     => 'susun_gambar',
            'indicator'   => 'budaya',
            'title'       => ['Susun Gambar Magelang', 'Magelang Picture Puzzle'],
            'instruction' => [
                'Gambarnya terbagi menjadi 4 × 4 keping. Ketuk dua keping untuk menukar tempatnya sampai gambarnya utuh, lalu baca kisahnya.',
                'The picture is cut into 4 × 4 pieces. Tap two pieces to swap them until the picture is complete, then read its story.',
            ],
            'description' => [
                'Di Magelang, gambar-gambar warisan budaya berserakan seperti kepingan relief. Susun kembali, lalu hubungkan gambar itu dengan kisah di bawahnya.',
                'In Magelang, pictures of cultural heritage are scattered like pieces of a relief. Put them back together, then connect the picture with the story below it.',
            ],
            'hints'       => [
                ['Mulai dari kepingan yang punya garis langit atau garis tanah.', 'Start with the pieces that show the sky line or the ground line.'],
                [
                    'Kepingan dengan warna dan garis yang bersambung pasti bersebelahan.',
                    'Pieces whose colours and lines continue must sit next to each other.',
                ],
                [
                    'Kerjakan baris demi baris: lengkapi dulu empat keping di baris paling atas.',
                    'Work row by row: first complete the four pieces of the top row.',
                ],
            ],
            'variants' => [
                'a' => [
                    'title' => ['Stupa Borobudur', 'The Stupas of Borobudur'],
                    'ref'   => $ref['borobudur'],
                    'items' => [
                        [   // mgl-1-a01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Borobudur dibangun pada abad ke-8 dan ke-9. Di teras bundarnya berdiri 72 stupa berlubang, masing-masing berisi arca Buddha.',
                                'Borobudur Temple was built in the 8th and 9th centuries. On its round terraces stand 72 perforated stupas, each holding a Buddha statue.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-1-01', 'label' => 'Stupa Borobudur', 'note' => 'stupa berlubang teras atas Borobudur, stupa induk di latar', 'source' => 'https://commons.wikimedia.org/wiki/File:Borobudur_stupas_on_upper_terrace.jpg'],
                        ],
                    ],
                ],
                'b' => [
                    'title' => ['Penari Topeng Ireng', 'Topeng Ireng Dancers'],
                    'ref'   => $ref['topeng'],
                    'items' => [
                        [   // mgl-1-b01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Penari Topeng Ireng memakai hiasan kepala dari bulu berwarna-warni dan lonceng kecil di kaki. Tarian ini tumbuh di Desa Tuksongo, Borobudur.',
                                'Topeng Ireng dancers wear colourful feather headdresses and small bells on their ankles. The dance grew in Tuksongo Village, Borobudur.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-1-02', 'label' => 'Penari Topeng Ireng', 'note' => 'penari Topeng Ireng dengan kuluk bulu warna-warni', 'source' => 'https://commons.wikimedia.org/wiki/File:Penari_Topeng_Ireng_03.jpg'],
                        ],
                    ],
                ],
                'c' => [
                    'title' => ['Candi Mendut', 'Mendut Temple'],
                    'ref'   => $ref['waisak'],
                    'items' => [
                        [   // mgl-1-c01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Mendut menyimpan arca Buddha setinggi sekitar tiga meter. Setiap Waisak, prosesi umat Buddha dimulai dari candi ini.',
                                'Mendut Temple holds a Buddha statue about three metres tall. Every Vesak, the Buddhist procession starts from this temple.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-1-03', 'label' => 'Candi Mendut', 'note' => 'Candi Mendut tampak depan dengan tangga masuk', 'source' => 'https://commons.wikimedia.org/wiki/File:Mendut_Temple.jpg'],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------------ mgl-2 Isi Rumpang
        'mgl-2' => [
            'engine'           => 'rumpang',
            'variant'          => 'rumpang_bacaan',
            'indicator'        => 'literasi',
            'distractor_count' => 2,
            'title'            => ['Lengkapi Bacaan Magelang', 'Complete the Magelang Reading'],
            'instruction'      => [
                'Baca bacaannya sampai habis. Pilih kata yang tepat untuk setiap kotak kosong. Ada dua kata yang tidak terpakai.',
                'Read the whole text. Choose the right word for each empty box. Two words will not be used.',
            ],
            'description'      => [
                'Juru kunci candi menitipkan catatan tentang Magelang, tetapi beberapa katanya hilang. Hubungkan informasi di dalam bacaan, lalu lengkapi catatannya.',
                'The temple keeper left notes about Magelang, but some words are missing. Connect the information in the reading, then complete the notes.',
            ],
            'hints'            => [
                [
                    'Jawabannya tidak selalu tertulis langsung. Hubungkan dua informasi di dalam bacaan.',
                    'The answer is not always written directly. Connect two pieces of information in the reading.',
                ],
                [
                    'Baca kalimat sebelum dan sesudah kotak kosong, lalu pilih kata yang membuat kalimatnya masuk akal.',
                    'Read the sentences before and after the box, then choose the word that makes sense.',
                ],
                ['Ada dua kata pengecoh. Setiap kata hanya dipakai satu kali.', 'There are two decoy words. Each word is used only once.'],
            ],
            'variants' => [
                'a' => [
                    'title'       => ['Candi Borobudur', 'Borobudur Temple'],
                    'ref'         => $ref['borobudur'],
                    'distractors' => [['enam', 'six'], ['arca', 'statues']],
                    'items'       => [
                        [   // mgl-2-a01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Candi Borobudur berdiri di Kabupaten Magelang. Candi Buddha ini dibangun pada abad ke-8 dan ke-9. Bangunannya bertingkat-tingkat. Di bagian bawah ada enam teras persegi, lalu di atasnya ada tiga teras bundar. Jadi, Borobudur memiliki ___ teras.',
                                'Borobudur Temple stands in Magelang Regency. This Buddhist temple was built in the 8th and 9th centuries. The building rises in levels. At the bottom there are six square terraces, and above them there are three round terraces. So Borobudur has ___ terraces.',
                            ],
                            'answer'       => ['sembilan', 'nine'],
                            'bloom_reason' => 'Menerapkan penjumlahan 6 + 3; pengecoh *enam* menjebak pembaca yang tergesa.',
                        ],
                        [   // mgl-2-a02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                "Di teras bundar berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng. Bangunan berbentuk lonceng yang paling besar ada di puncak candi dan disebut ___ induk.\n\nDinding candi dihiasi ribuan pahatan gambar yang menceritakan sebuah kisah.",
                                "On the round terraces stand 72 perforated stupas. A stupa is a bell-shaped structure. The largest bell-shaped structure is at the top of the temple and is called the main ___.\n\nThe temple walls are decorated with thousands of carved pictures that tell a story.",
                            ],
                            'answer'       => ['stupa', 'stupa'],
                            'bloom_reason' => 'Menerapkan definisi stupa pada bangunan di puncak.',
                        ],
                        [   // mgl-2-a03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => ['Pahatan bercerita seperti itu disebut ___.', 'Carved story pictures like these are called ___.'],
                            'answer'       => ['relief', 'reliefs'],
                            'bloom_reason' => 'Memahami istilah dari penjelasannya.',
                        ],
                        [   // mgl-2-a04
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => ['Bila ingin melihatnya, perhatikan bagian ___ candi.', 'To see them, look at the temple ___.'],
                            'answer'       => ['dinding', 'walls'],
                            'bloom_reason' => 'Memakai informasi letak relief untuk tugas baru.',
                        ],
                        [   // mgl-2-a05
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Batu-batu candi bisa rusak bila diinjak dan dipanjat. Oleh karena itu, pengunjung ___ memanjat stupa.',
                                'The temple stones can be damaged when people step or climb on them. That is why visitors are ___ to climb the stupas.',
                            ],
                            'answer'       => ['dilarang', 'forbidden'],
                            'bloom_reason' => 'Memahami hubungan sebab–akibat.',
                        ],
                    ],
                ],
                'b' => [
                    'title'       => ['Tari Topeng Ireng', 'The Topeng Ireng Dance'],
                    'ref'         => $ref['topeng'],
                    'distractors' => [['nelayan', 'fishermen'], ['diam', 'silent']],
                    'items'       => [
                        [   // mgl-2-b01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Topeng Ireng adalah tari rakyat dari Kabupaten Magelang. Tarian ini mulai tumbuh sekitar tahun 1950-an di Desa Tuksongo. Desa itu berada di Kecamatan Borobudur, tidak jauh dari Candi ___.',
                                'Topeng Ireng is a folk dance from Magelang Regency. It began to grow around the 1950s in Tuksongo Village. The village is in Borobudur District, not far from ___ Temple.',
                            ],
                            'answer'       => ['Borobudur', 'Borobudur'],
                            'bloom_reason' => 'Memahami: menghubungkan nama kecamatan dengan candi terdekat.',
                        ],
                        [   // mgl-2-b02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                "Penarinya memakai kuluk, yaitu hiasan kepala dari bulu yang berwarna-warni. Di pergelangan kaki mereka terpasang lonceng-lonceng kecil. Setiap kali kaki dihentakkan, lonceng itu ___.\n\nSatu kelompok Topeng Ireng bisa berisi puluhan penari yang bergerak serempak.",
                                "The dancers wear a kuluk, a head decoration made of colourful feathers. Small bells are tied around their ankles. Every time they stamp their feet, the bells ___.\n\nOne Topeng Ireng group can have dozens of dancers moving at the same time.",
                            ],
                            'answer'       => ['berbunyi', 'ring'],
                            'bloom_reason' => 'Memahami fungsi lonceng; pengecoh *diam* bertentangan dengan "dihentakkan".',
                        ],
                        [   // mgl-2-b03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Karena itu, para penari harus berlatih bersama supaya ___.',
                                'That is why the dancers must practise together so that they move in ___.',
                            ],
                            'answer'       => ['kompak', 'harmony'],
                            'bloom_reason' => 'Menerapkan: gerak serempak menuntut kekompakan.',
                        ],
                        [   // mgl-2-b04
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Tarian ini lahir di desa-desa lereng gunung yang warganya banyak bertani. Gerakannya yang tegas menggambarkan semangat para ___ itu.',
                                'The dance was born in mountain-slope villages where many people farm. Its firm movements show the spirit of those ___.',
                            ],
                            'answer'       => ['petani', 'farmers'],
                            'bloom_reason' => 'Memahami rujukan "warganya banyak bertani"; pengecoh *nelayan* menguji ketelitian.',
                        ],
                        [   // mgl-2-b05
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Kalau kamu menonton pertunjukannya, duduklah dengan tenang dan jangan ___ para penari.',
                                'If you watch a performance, sit quietly and do not ___ the dancers.',
                            ],
                            'answer'       => ['mengganggu', 'disturb'],
                            'bloom_reason' => 'Menerapkan sikap menonton pertunjukan.',
                        ],
                    ],
                ],
                'c' => [
                    'title'       => ['Getuk dan Gerabah Klipoh', 'Getuk and Klipoh Pottery'],
                    'ref'         => $ref['klipoh'],
                    'distractors' => [['beras', 'rice'], ['sawah', 'field']],
                    'items'       => [
                        [   // mgl-2-c01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Magelang dijuluki Kota Getuk. Getuk dibuat dari singkong yang dikukus, ditumbuk halus, lalu diberi gula. Jadi, bahan utama getuk adalah ___.',
                                'Magelang is nicknamed the Getuk Town. Getuk is made from cassava that is steamed, pounded smooth, and then sweetened. So the main ingredient of getuk is ___.',
                            ],
                            'answer'       => ['singkong', 'cassava'],
                            'bloom_reason' => 'Memahami bahan utama dari langkah pembuatan; pengecoh *beras* ada di kalimat lain.',
                        ],
                        [   // mgl-2-c02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                "Julukan Kota Getuk bermula pada masa penjajahan Jepang. Saat itu beras sulit didapat, sehingga warga memakai singkong sebagai ___ beras.\n\nSekitar tiga kilometer dari Candi Borobudur ada Dusun Klipoh. Warga Klipoh membuat kendil, cobek, dan kuali dari tanah liat. Tanah liat itu dibentuk, lalu dibakar agar keras.",
                                "The nickname began during the Japanese occupation. Rice was hard to find then, so people used cassava as a ___ for rice.\n\nAbout three kilometres from Borobudur Temple lies Klipoh Hamlet. The people of Klipoh make cooking pots, grinding bowls, and woks from clay. The clay is shaped and then fired to make it hard.",
                            ],
                            'answer'       => ['pengganti', 'substitute'],
                            'bloom_reason' => 'Menerapkan hubungan sebab–akibat pada peristiwa sejarah.',
                        ],
                        [   // mgl-2-c03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Benda-benda dari tanah liat yang dibakar seperti itu disebut ___.',
                                'Objects made of fired clay like these are called ___.',
                            ],
                            'answer'       => ['gerabah', 'pottery'],
                            'bloom_reason' => 'Mengklasifikasikan benda menurut ciri; kata *gerabah* muncul lagi di kalimat terakhir.',
                        ],
                        [   // mgl-2-c04
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Kendil, cobek, dan kuali dipakai untuk memasak, jadi semuanya berguna di ___.',
                                'Cooking pots, grinding bowls, and woks are used for cooking, so they are all useful in the ___.',
                            ],
                            'answer'       => ['dapur', 'kitchen'],
                            'bloom_reason' => 'Menerapkan kegunaan benda; pengecoh *sawah*.',
                        ],
                        [   // mgl-2-c05
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C2',
                            'prompt'       => [
                                'Keahlian membuat gerabah diwariskan turun-temurun, yaitu diajarkan orang tua kepada ___ mereka.',
                                'The skill of making pottery is passed down: parents teach it to their ___.',
                            ],
                            'answer'       => ['anak', 'children'],
                            'bloom_reason' => 'Memahami arti "turun-temurun".',
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------- mgl-3 Benar atau Salah
        'mgl-3' => [
            'engine'      => 'boleh',
            'variant'     => 'benar_salah_bacaan',
            'indicator'   => 'literasi',
            'title'       => ['Benar atau Salah? Magelang', 'True or False? Magelang'],
            'instruction' => [
                'Baca kedua paragraf. Hubungkan informasinya, lalu pilih BENAR atau SALAH untuk setiap pernyataan.',
                'Read both paragraphs. Connect the information, then choose TRUE or FALSE for each statement.',
            ],
            'description' => [
                'Pengunjung Magelang saling bercerita. Baca bacaannya, hubungkan informasinya, lalu tentukan mana yang benar dan mana yang salah.',
                'Visitors in Magelang are telling each other stories. Read the text, connect the information, then decide which are true and which are false.',
            ],
            'hints'       => [
                [
                    'Gagasan pokok adalah hal yang dibicarakan seluruh paragraf, bukan satu kalimat saja.',
                    'The main idea is what the whole paragraph is about, not just one sentence.',
                ],
                ['Hubungkan informasi dari paragraf pertama dan kedua.', 'Connect information from the first and second paragraphs.'],
                [
                    'Tindakan yang baik sesuai dengan aturan atau pesan di bacaan; tindakan yang melanggarnya tidak.',
                    'A good action follows the rules or advice in the reading; an action that breaks them does not.',
                ],
            ],
            'variants' => [
                'a' => [
                    'passage' => [
                        'title' => ['Menjaga Candi Borobudur', 'Caring for Borobudur Temple'],
                        'body'  => [
                            "Candi Borobudur adalah candi Buddha terbesar di dunia. Candi ini dibangun pada abad ke-8 dan ke-9 tanpa semen. Batu-batunya disusun saling mengunci. Dindingnya dihiasi 2.672 panel relief yang bercerita. Pada tahun 1991, UNESCO menetapkan Borobudur sebagai Warisan Dunia. Artinya, Borobudur dianggap berharga bagi seluruh umat manusia.\n\nKarena sangat berharga, Borobudur harus dijaga bersama. Pengunjung diminta mengikuti rute dan aturan dari pengelola. Pengunjung tidak boleh memanjat stupa atau menyentuh relief, sebab minyak dan keringat tangan dapat merusak batu. Pengunjung juga tidak boleh mencoret-coret dinding dan membuang sampah sembarangan.",
                            "Borobudur Temple is the largest Buddhist temple in the world. It was built in the 8th and 9th centuries without cement. Its stones are fitted together so that they lock. Its walls are decorated with 2,672 relief panels that tell stories. In 1991, UNESCO named Borobudur a World Heritage Site. This means it is considered precious for all of humanity.\n\nBecause it is so precious, Borobudur must be looked after together. Visitors are asked to follow the routes and rules of the site managers. Visitors may not climb the stupas or touch the reliefs, because oil and sweat from hands can damage the stone. Visitors also may not scribble on the walls or drop litter.",
                        ],
                        'ref'   => $ref['borobudur'],
                    ],
                    'items'   => [
                        [   // mgl-3-a01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'gagasan_pokok',
                            'answer' => 'benar',
                            'prompt' => [
                                'Gagasan pokok paragraf kedua adalah cara menjaga Candi Borobudur bersama-sama.',
                                'The main idea of the second paragraph is how to look after Borobudur Temple together.',
                            ],
                        ],
                        [   // mgl-3-a02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Borobudur harus dijaga bersama karena dianggap berharga bagi seluruh umat manusia.',
                                'Borobudur must be looked after together because it is considered precious for all of humanity.',
                            ],
                        ],
                        [   // mgl-3-a03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C3',
                            'skill'  => 'mengklasifikasikan_tindakan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Menyentuh relief dengan tangan termasuk tindakan menjaga Borobudur.',
                                'Touching the reliefs with your hands is a way of looking after Borobudur.',
                            ],
                        ],
                        [   // mgl-3-a04
                            'type'   => 'verdict_card',
                            'bloom'  => 'C3',
                            'skill'  => 'mengklasifikasikan_tindakan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Membawa pulang sampah sendiri termasuk tindakan menjaga Borobudur.',
                                'Taking your own rubbish home is a way of looking after Borobudur.',
                            ],
                        ],
                        [   // mgl-3-a05
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'salah',
                            'prompt' => ['Batu-batu Borobudur direkatkan dengan semen.', 'The stones of Borobudur are stuck together with cement.'],
                        ],
                    ],
                ],
                'b' => [
                    'passage' => [
                        'title' => ['Perjalanan Waisak', 'The Vesak Walk'],
                        'body'  => [
                            "Tidak jauh dari Candi Borobudur ada dua candi Buddha lain, yaitu Candi Pawon dan Candi Mendut. Bila ditarik garis dari timur ke barat, Candi Mendut, Candi Pawon, dan Candi Borobudur berada hampir pada satu garis lurus. Candi Pawon terletak di tengah. Candi Mendut diperkirakan lebih tua daripada Candi Borobudur.\n\nSetiap perayaan Waisak, ribuan umat Buddha berjalan kaki dari Candi Mendut ke Candi Pawon, lalu berakhir di Candi Borobudur. Mereka membawa api dan air suci. Air suci itu diambil dari Umbul Jumprit di Temanggung. Saat menyaksikan perayaan agama lain, kita menghormatinya dengan menjaga ketenangan, memberi jalan, dan tidak mengganggu umat yang beribadah.",
                            "Not far from Borobudur Temple there are two other Buddhist temples, Pawon Temple and Mendut Temple. If a line is drawn from east to west, Mendut, Pawon, and Borobudur lie almost on one straight line. Pawon Temple is in the middle. Mendut Temple is thought to be older than Borobudur.\n\nEvery Vesak, thousands of Buddhists walk from Mendut Temple to Pawon Temple, ending at Borobudur Temple. They carry fire and holy water. The holy water is taken from Umbul Jumprit in Temanggung. When we watch another religion's celebration, we show respect by keeping quiet, making way, and not disturbing the people who are worshipping.",
                        ],
                        'ref'   => $ref['waisak'],
                    ],
                    'items'   => [
                        [   // mgl-3-b01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'gagasan_pokok',
                            'answer' => 'benar',
                            'prompt' => [
                                'Paragraf pertama terutama menjelaskan letak tiga candi yang hampir segaris.',
                                'The first paragraph mainly explains where the three temples lie in almost one line.',
                            ],
                        ],
                        [   // mgl-3-b02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Dalam perjalanan Waisak, umat Buddha melewati Candi Pawon sebelum tiba di Candi Borobudur.',
                                'On the Vesak walk, Buddhists pass Pawon Temple before they reach Borobudur Temple.',
                            ],
                        ],
                        [   // mgl-3-b03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Air suci untuk perayaan Waisak di Magelang diambil dari wilayah Wonosobo.',
                                'The holy water for the Vesak celebration in Magelang is taken from Wonosobo.',
                            ],
                        ],
                        [   // mgl-3-b04
                            'type'   => 'verdict_card',
                            'bloom'  => 'C3',
                            'skill'  => 'mengklasifikasikan_tindakan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Memotret dari dekat sambil menghalangi jalan umat yang berjalan termasuk sikap menghormati perayaan.',
                                'Taking close-up photos while blocking the path of the worshippers is a way of showing respect for the celebration.',
                            ],
                        ],
                        [   // mgl-3-b05
                            'type'   => 'verdict_card',
                            'bloom'  => 'C3',
                            'skill'  => 'mengklasifikasikan_tindakan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Berbicara pelan saat melihat umat beribadah termasuk sikap menghormati perayaan agama lain.',
                                'Speaking softly while watching people worship is a way of respecting another religion\'s celebration.',
                            ],
                        ],
                    ],
                ],
                'c' => [
                    'passage' => [
                        'title' => ['Gunung Tidar', 'Mount Tidar'],
                        'body'  => [
                            "Di tengah Kota Magelang ada bukit hijau bernama Gunung Tidar. Tingginya sekitar 503 meter di atas permukaan laut. Banyak orang mendaki tangganya untuk berolahraga. Dalam cerita rakyat, Gunung Tidar disebut paku tanah Jawa. Konon, Pulau Jawa dulu bergoyang-goyang, lalu dipaku di Gunung Tidar agar tenang. Cerita seperti ini disebut legenda, bukan penjelasan ilmiah.\n\nDi lembah Gunung Tidar berdiri Akademi Militer sejak tahun 1957. Di sana para taruna dididik menjadi perwira TNI Angkatan Darat. Kota Magelang berulang tahun setiap tanggal 11 April. Tanggal itu diambil dari Prasasti Mantyasih yang dibuat pada tahun 907 Masehi.",
                            "In the middle of Magelang City is a green hill called Mount Tidar. It is about 503 metres above sea level. Many people climb its steps to exercise. In folklore, Mount Tidar is called the nail of Java. It is said that the island of Java once wobbled, and was nailed down at Mount Tidar to keep it still. A story like this is called a legend, not a scientific explanation.\n\nIn the valley of Mount Tidar, the Military Academy has stood since 1957. There, cadets are trained to become officers of the Indonesian Army. Magelang City celebrates its birthday every 11 April. The date comes from the Mantyasih Inscription, which was made in 907 CE.",
                        ],
                        'ref'   => $ref['tidar'],
                    ],
                    'items'   => [
                        [   // mgl-3-c01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'gagasan_pokok',
                            'answer' => 'benar',
                            'prompt' => [
                                'Paragraf pertama terutama menceritakan Gunung Tidar dan legendanya.',
                                'The first paragraph mainly tells about Mount Tidar and its legend.',
                            ],
                        ],
                        [   // mgl-3-c02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Cerita bahwa Pulau Jawa dipaku di Gunung Tidar adalah penjelasan ilmiah.',
                                'The story that Java was nailed down at Mount Tidar is a scientific explanation.',
                            ],
                        ],
                        [   // mgl-3-c03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C2',
                            'skill'  => 'mengklasifikasikan_tindakan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Mendaki tangga Gunung Tidar dapat digolongkan sebagai kegiatan berolahraga.',
                                'Climbing the steps of Mount Tidar can be grouped as exercise.',
                            ],
                        ],
                        [   // mgl-3-c04
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Hari ulang tahun Kota Magelang berasal dari tanggal Prasasti Mantyasih.',
                                'Magelang City\'s birthday comes from the date of the Mantyasih Inscription.',
                            ],
                        ],
                        [   // mgl-3-c05
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'menghubungkan_informasi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Prasasti Mantyasih dibuat sesudah Akademi Militer berdiri.',
                                'The Mantyasih Inscription was made after the Military Academy was built.',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------------- mgl-4 Cari Objek
        'mgl-4' => [
            'engine'      => 'cari',
            'variant'     => 'cari_objek',
            'indicator'   => 'budaya',
            'title'       => ['Cari Objek Magelang', 'Find the Objects: Magelang'],
            'instruction' => [
                'Baca petunjuknya dengan teliti, lalu ketuk benda yang tepat. Hati-hati, ada benda yang mirip!',
                'Read each clue carefully, then tap the right object. Careful, some objects look alike!',
            ],
            'description' => [
                'Di galeri budaya Magelang, benda-benda khas tercampur dengan benda yang mirip. Baca petunjuknya dengan teliti dan temukan empat benda yang dimaksud.',
                'In Magelang\'s culture gallery, special objects are mixed with look-alikes. Read the clues carefully and find the four objects.',
            ],
            'scene'       => ['key' => 'challenge.mgl-4.scene', 'label' => 'Galeri Budaya Magelang', 'note' => 'galeri/pendapa budaya, dua deret meja pajang kayu, stupa & Gunung Tidar di latar'],
            'hints'       => [
                [
                    'Petunjuknya tidak selalu menyebut nama benda. Cocokkan ciri-cirinya: bahan, bentuk, dan kegunaannya.',
                    'The clue does not always name the object. Match its features: material, shape, and use.',
                ],
                [
                    'Hati-hati dengan benda yang mirip, misalnya hiasan dari bulu atau jajanan.',
                    'Watch out for objects that look alike, such as feather decorations or snacks.',
                ],
                [
                    'Ada benda dari Temanggung, Wonosobo, dan daerah lain. Pilih yang sesuai petunjuk.',
                    'There are objects from Temanggung, Wonosobo, and other places. Choose the one that matches the clue.',
                ],
            ],
            'variants' => [
                'a' => [
                    'title' => ['Galeri Budaya Magelang', 'Magelang Culture Gallery'],
                    'ref'   => $ref['hunt'],
                    'items' => [
                        [   // mgl-4-a01
                            'type'   => 'find_object',
                            'bloom'  => 'C2',
                            'x'      => 23.5,
                            'y'      => 17,
                            'w'      => 12,
                            'prompt' => [
                                'Temukan bangunan kecil berbentuk lonceng yang berlubang-lubang. Di teras bundar Borobudur ada 72 bangunan seperti ini.',
                                'Find the small bell-shaped structure full of holes. Borobudur has 72 of these on its round terraces.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a01', 'label' => 'Stupa berlubang', 'note' => 'stupa kecil berlubang (miniatur)'],
                        ],
                        [   // mgl-4-a02
                            'type'   => 'find_object',
                            'bloom'  => 'C3',
                            'x'      => 4,
                            'y'      => 56,
                            'w'      => 12,
                            'prompt' => [
                                'Temukan hiasan kepala dari bulu warna-warni yang dipakai penari tari rakyat dari Desa Tuksongo, Magelang.',
                                'Find the colourful feather headdress worn by dancers of the folk dance from Tuksongo Village, Magelang.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a02', 'label' => 'Kuluk Topeng Ireng', 'note' => 'kuluk bulu warna-warni Topeng Ireng'],
                        ],
                        [   // mgl-4-a03
                            'type'   => 'find_object',
                            'bloom'  => 'C2',
                            'x'      => 62.5,
                            'y'      => 17,
                            'w'      => 12,
                            'prompt' => [
                                'Temukan jajanan dari singkong yang dikukus, ditumbuk, lalu diberi gula. Jajanan ini membuat Magelang punya julukan khusus.',
                                'Find the snack made of cassava that is steamed, pounded, and sweetened. This snack gave Magelang a special nickname.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a03', 'label' => 'Getuk', 'note' => 'getuk tiga warna di atas daun pisang'],
                        ],
                        [   // mgl-4-a04
                            'type'   => 'find_object',
                            'bloom'  => 'C3',
                            'x'      => 62.5,
                            'y'      => 56,
                            'w'      => 12,
                            'prompt' => [
                                'Temukan benda dari tanah liat yang dibentuk lalu dibakar, buatan warga Dusun Klipoh.',
                                'Find the object made of clay that is shaped and then fired, made by the people of Klipoh Hamlet.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a04', 'label' => 'Kendil gerabah', 'note' => 'kendil gerabah cokelat kemerahan'],
                        ],
                        [   // mgl-4-a05 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 4,
                            'y'      => 17,
                            'w'      => 12,
                            'prompt' => ['Kuda kepang bambu (pengecoh)', 'Bamboo hobby horse (decoy)'],
                            'wrong'  => [
                                'Ini kuda kepang dari Temanggung, wilayah yang sudah kamu jelajahi. Baca lagi petunjuknya.',
                                'This is a kuda kepang from Temanggung, the region you have already explored. Read the clue again.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a05', 'label' => 'Kuda kepang bambu', 'note' => 'kuda kepang anyaman bambu'],
                        ],
                        [   // mgl-4-a06 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 82,
                            'y'      => 17,
                            'w'      => 12,
                            'prompt' => ['Manisan carica dalam stoples (pengecoh)', 'Sweet carica in a jar (decoy)'],
                            'wrong'  => ['Ini manisan carica, oleh-oleh khas Wonosobo.', 'This is sweet carica in syrup, a souvenir from Wonosobo.'],
                            'media'  => ['key' => 'challenge.item.mgl-4-a06', 'label' => 'Manisan carica dalam stoples', 'note' => 'stoples manisan carica'],
                        ],
                        [   // mgl-4-a07 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 43,
                            'y'      => 17,
                            'w'      => 12,
                            'prompt' => ['Dadak merak Reog (pengecoh)', 'Reog dadak merak (decoy)'],
                            'wrong'  => [
                                'Ini dadak merak dari Reog Ponorogo, Jawa Timur. Bulunya bulu merak, bukan kuluk penari dari Magelang.',
                                'This is the dadak merak from Reog Ponorogo in East Java. It is made of peacock feathers, not the headdress of the Magelang dancers.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a07', 'label' => 'Dadak merak Reog', 'note' => 'dadak merak Reog Ponorogo'],
                        ],
                        [   // mgl-4-a08 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 43,
                            'y'      => 56,
                            'w'      => 12,
                            'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'],
                            'wrong'  => [
                                'Ini angklung dari Jawa Barat — bukan benda yang dimaksud petunjuk.',
                                'This is an angklung from West Java — not the object in the clue.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a08', 'label' => 'Angklung', 'note' => 'angklung bambu'],
                        ],
                        [   // mgl-4-a09 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 23.5,
                            'y'      => 56,
                            'w'      => 12,
                            'prompt' => ['Bakpia (pengecoh)', 'Bakpia (decoy)'],
                            'wrong'  => [
                                'Ini bakpia, jajanan dari Yogyakarta. Getuk dibuat dari singkong.',
                                'This is bakpia, a snack from Yogyakarta. Getuk is made from cassava.',
                            ],
                            'media'  => ['key' => 'challenge.item.mgl-4-a09', 'label' => 'Bakpia', 'note' => 'beberapa bakpia'],
                        ],
                        [   // mgl-4-a10 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 82,
                            'y'      => 56,
                            'w'      => 12,
                            'prompt' => ['Panci logam (pengecoh)', 'Metal pot (decoy)'],
                            'wrong'  => ['Ini panci dari logam, bukan gerabah dari tanah liat.', 'This is a metal pot, not pottery made of clay.'],
                            'media'  => ['key' => 'challenge.item.mgl-4-a10', 'label' => 'Panci logam', 'note' => 'panci logam'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
