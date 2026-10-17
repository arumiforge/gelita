<?php

/**
 * Bank soal kelas 5 — WONOSOBO (wilayah 3, tingkat sulit).
 * Fokus literasi: membandingkan informasi, menilai pilihan, dan menentukan solusi berdasarkan bukti.
 *
 * October 2026 revision (bank draft 2: gelita-katalog/update/bank-soal/bank-soal-kelas5.xlsx,
 * see gelita-katalog/update/catatan-revisi-gelita.md §2 and §7) of the bank the
 * researcher approved in docs/perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md §9.
 * Revised and new items carry `review` status `needs_verification` until the
 * researcher checks them; untouched items keep the default `verified` status.
 * Explicit `key` values (moved or inserted items) and `active => false` (retired
 * items, kept in the workbook) keep students' stored answers linked to their item.
 *
 * Bentuk data dijelaskan di docs/bank-soal/README.md. Teks berpasangan
 * [Indonesia, English]. Kode soal {node}-{variasi}{nn} (mis. wnb-2-a01) dan kode
 * bacaan {node}-{variasi} (wnb-3-a) diberi build-workbook.php menurut urutan di
 * berkas ini. Once students have used the bank, never insert, move, or delete an
 * item without an explicit `key`: the automatic keys after it would shift.
 * Potongan Isi Rumpang ditulis persis seperti disimpan:
 * ___ = tempat kosong, "\n\n" = jeda paragraf di tengah potongan sesudah ___.
 * `bloom_reason` hanya catatan penyusun (tidak ikut workbook).
 */

$ref = [
    'dieng'   => 'Wikipedia: Dataran Tinggi Dieng; Tempo & Kompas Travel (Desa Sembungan); Indonesia Kaya (Mie Ongklok)',
    'ongklok' => 'Indonesia Kaya: Mie Ongklok; Pemkab Wonosobo (kuliner)',
    'upas'    => 'BMKG via Kompas.com (Juli 2026) embun upas; detikEdu',
    'news'    => 'Panduan cek fakta Siberkreasi & Mafindo; laman resmi PVMBG/MAGMA Indonesia; pengelola kawasan Dieng',
    'sikunir' => 'Tempo (Desa Sembungan); Kompas Travel (2022); Indonesia Kaya',
    'hunt'    => 'Indonesia Kaya; BRIN Audiovisual (Bundengan); Kompas Travel (2023) Kebun Teh Tambi; Wikipedia (Pepaya gunung)',
];

// Review notes shared by several items of the October 2026 revision (draft 2).
$note = [
    'linker' => 'Revisi Oktober 2026: label Bloom C4 → C3 (kata hubung di dalam satu kalimat: menerapkan, belum menganalisis). Perlu dicek ulang peneliti.',
    'retry'  => ' | Revisi v2 (17 Okt 2026): Penelitian: pengerjaan pertama Susun Gambar memakai variasi a–c (dinilai); variasi d–j hanya untuk pengerjaan ulang. Perlu perubahan pemilihan variasi di aplikasi sebelum dipakai.',
    'hunt'   => 'Revisi v2 (17 Okt 2026): label C4 → C3: mencocokkan ciri (bahan, bentuk, kegunaan) yang kini juga dikenalkan di narasi Kenali Wonosobo. | Sebelumnya: Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026).',
];

return [
    'difficulty' => 'sulit',

    'nodes' => [
        // ----------------------------------------------- wnb-1 Susun Gambar
        'wnb-1' => [
            'engine'      => 'puzzle',
            'variant'     => 'susun_gambar',
            'indicator'   => 'budaya',
            'title'       => ['Susun Gambar Wonosobo', 'Wonosobo Picture Puzzle'],
            'instruction' => [
                'Kepingannya berbentuk puzzle (4 × 4). Ketuk dua keping untuk menukar tempatnya sampai gambarnya utuh, lalu baca keterangannya.',
                'The pieces are jigsaw-shaped (4 × 4). Tap two pieces to swap them until the picture is complete, then read the caption.',
            ],
            'description' => [
                'Kabut tebal Dieng memotong-motong sebuah lukisan Wonosobo seperti kepingan puzzle. Susun kembali kepingnya, lalu kenali tempat di gambar itu.',
                'The thick Dieng mist has cut a painting of Wonosobo into jigsaw pieces. Put the pieces back together, then get to know the place in the picture.',
            ],
            'hints'       => [
                [
                    'Cari keping yang punya sisi lurus: keping itu ada di tepi gambar.',
                    'Find the pieces with a straight side: they belong on the edge of the picture.',
                ],
                [
                    'Tonjolan sebuah keping harus pas masuk ke lekukan keping di sebelahnya.',
                    'Each piece\'s tab must fit into the gap of the piece next to it.',
                ],
                [
                    'Cocokkan warna langit, air, atau tanah, lalu susun baris demi baris.',
                    'Match the colours of the sky, water, or ground, then build the picture row by row.',
                ],
            ],
            'variants' => [
                'a' => [
                    'title' => ['Telaga Warna', 'Telaga Warna'],
                    'ref'   => $ref['dieng'],
                    'items' => [
                        [   // wnb-1-a01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Air Telaga Warna di Dieng mengandung belerang. Saat terkena cahaya matahari, warnanya tampak berganti: hijau, biru, atau kekuningan. Airnya tidak untuk diminum.',
                                'The water of Telaga Warna in Dieng contains sulphur. In sunlight its colour seems to change: green, blue, or yellowish. The water is not for drinking.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-05', 'label' => 'Telaga Warna', 'note' => 'Telaga Warna dari titik pandang tinggi: air hijau, biru, dan kekuningan berbelerang, Telaga Pengilon yang lebih gelap, hutan cemara, jalan papan dengan pengunjung kecil', 'source' => 'Lulus uji keping. Warna air sudah hijau-biru; pastikan ada bagian kekuningan di tepi telaga sesuai keterangan baru. Rujukan: https://commons.wikimedia.org/wiki/File:Telaga_Warna_Dieng_Jawa_Tengah.jpg'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: warna telaga diselaraskan dengan naskah: hijau, biru, atau kekuningan. Perlu dicek ulang peneliti.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title' => ['Matahari Terbit di Sikunir', 'Sunrise at Sikunir'],
                    'ref'   => $ref['dieng'],
                    'items' => [
                        [   // wnb-1-b01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Bukit Sikunir ada di Desa Sembungan, desa tertinggi di Pulau Jawa (sekitar 2.300 meter). Wisatawan mendaki sebelum subuh untuk melihat matahari terbit di atas awan.',
                                'Sikunir Hill is in Sembungan Village, the highest village in Java (about 2,300 metres). Visitors climb before dawn to watch the sun rise above the clouds.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-06', 'label' => 'Matahari Terbit di Sikunir', 'note' => 'fajar dari puncak Sikunir: lautan awan bertekstur (emas dekat matahari, ungu-biru menjauh), kerucut Sindoro menembus awan, pengunjung berjaket di balik pagar, anak memegang senter', 'source' => 'Boleh dipakai (lulus dengan catatan, K3~K4 langit mirip). Rujukan: https://commons.wikimedia.org/wiki/File:Sunrise_di_Bukit_Sikunir.jpg'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: catatan isi gambar diperbarui (lautan awan bertekstur, Sindoro menembus awan). Perlu dicek ulang peneliti.',
                            ],
                        ],
                    ],
                ],
                'c' => [
                    'title' => ['Mi Ongklok', 'Ongklok Noodles'],
                    'ref'   => $ref['dieng'],
                    'items' => [
                        [   // wnb-1-c01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Mi ongklok adalah makanan khas Wonosobo. Mi dan sayur direbus di keranjang bambu kecil bernama ongklok, lalu disiram kuah kental dan disajikan bersama sate sapi.',
                                'Ongklok noodles are Wonosobo\'s special dish. The noodles and vegetables are boiled in a small bamboo basket called an ongklok, then covered with a thick sauce and served with beef satay.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-07', 'label' => 'Mi Ongklok', 'note' => 'warung mi ongklok dari sudut 3/4 (bukan tampak atas): mangkuk mi ongklok, sate sapi, tempe kemul, juru masak mencelup keranjang ongklok, jendela ke perbukitan berkabut', 'source' => 'DISARANKAN GANTI: tampak atas membuat keping K10~K11 berpola sama, dan kuahnya tampak bening seperti ramen, bukan kuah kanji kental kecokelatan. Buat ulang dengan prompt WNB-P03 (sudut 3/4). Rujukan: https://commons.wikimedia.org/wiki/File:Mi_ongklok_sate_sapi_Wonosobo.JPG'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: catatan isi gambar: sudut 3/4, bukan tampak atas; kuah kental kecokelatan. Perlu dicek ulang peneliti.',
                            ],
                        ],
                    ],
                ],
                // Variants d–j are new in draft 2. Per their review note, the
                // research scores the first attempt on variants a–c only and
                // uses d–j for retries, which needs an app change first.
                'd' => [
                    'title' => ['Kompleks Candi Arjuna', 'The Arjuna Temple Complex'],
                    'ref'   => 'Wikipedia: Dataran Tinggi Dieng; Wikipedia: Kompleks Candi Arjuna',
                    'items' => [
                        [   // wnb-1-d01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kompleks Candi Arjuna di Dataran Tinggi Dieng adalah salah satu kompleks candi Hindu tertua di Jawa. Candi-candinya diberi nama tokoh wayang, seperti Arjuna, Semar, Srikandi, dan Puntadewa.',
                                'The Arjuna Temple complex on the Dieng Plateau is one of the oldest Hindu temple complexes in Java. Its temples are named after wayang characters such as Arjuna, Semar, Srikandi and Puntadewa.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-d01', 'label' => 'Kompleks Candi Arjuna', 'note' => 'deretan candi Hindu kecil dari andesit gelap di lapangan rumput Dieng, Candi Semar menghadap Candi Arjuna, bukit berteras kentang, pengunjung berjaket', 'source' => 'BARU. Buat dengan prompt WNB-P04 di panduan gambar puzzle; foto rujukan WAJIB. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P04 di panduan gambar puzzle). Fakta baru yang wajib dicek: "diberi nama tokoh wayang". Lokasi administratifnya Kabupaten Banjarnegara, jadi tulis "di Dataran Tinggi Dieng", jangan "di Wonosobo".. Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
                'e' => [
                    'title' => ['Kawah di Dieng: Tetap di Balik Pagar', 'Dieng Crater: Stay Behind the Fence'],
                    'ref'   => 'Laman resmi PVMBG/MAGMA Indonesia; pengelola kawasan Dieng',
                    'items' => [
                        [   // wnb-1-e01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api, sehingga di sana ada beberapa kawah. Gas di sekitar kawah dapat berbahaya, jadi pengunjung harus mengikuti rambu dan tetap berdiri di belakang pagar pembatas.',
                                'The Dieng Plateau was formed by volcanic activity, so it has several craters. Gas around a crater can be harmful, so visitors must follow the signs and stay behind the safety fence.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-e01', 'label' => 'Kawah di Dieng: Tetap di Balik Pagar', 'note' => 'kawah beruap di Dieng: kolam lumpur abu-abu, endapan belerang kuning, pagar pengaman dan jalan papan, rambu bergambar tanpa tulisan, petugas, pengunjung di balik pagar', 'source' => 'BARU. Buat dengan prompt WNB-P05 di panduan gambar puzzle; foto rujukan disarankan. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P05 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
                'f' => [
                    'title' => ['Carica, Pepaya Gunung', 'Carica, the Mountain Papaya'],
                    'ref'   => 'Wikipedia (Pepaya gunung); Indonesia Kaya',
                    'items' => [
                        [   // wnb-1-f01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Carica adalah pepaya gunung yang buahnya kecil. Carica tumbuh baik di udara sejuk pada ketinggian 1.400–2.400 meter, dan buahnya sering dibuat manisan.',
                                'Carica is a mountain papaya with small fruit. It grows well in cool air at 1,400–2,400 metres, and its fruit is often made into sweets.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-f01', 'label' => 'Carica, Pepaya Gunung', 'note' => 'kebun carica di teras Dieng: pohon carica berbuah kuning kecil bergerigi, petani berjaket memetik, teras rumah dengan buah dikupas dan stoples manisan carica', 'source' => 'BARU. Buat dengan prompt WNB-P06 di panduan gambar puzzle; foto rujukan disarankan. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P06 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
                'g' => [
                    'title' => ['Bundengan', 'Bundengan'],
                    'ref'   => 'BRIN Audiovisual (Bundengan); GNFI (2019); Indonesia Kaya',
                    'items' => [
                        [   // wnb-1-g01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Bundengan adalah alat musik khas Wonosobo. Dulu benda ini berupa kowangan, tudung anyaman bambu yang melindungi penggembala bebek dari panas dan hujan. Tudung itu kemudian diberi senar dan dipetik; suaranya mirip gamelan.',
                                'The bundengan is a musical instrument from Wonosobo. It began as a kowangan, a woven bamboo hood that protected duck herders from the sun and rain. Strings were later added to pluck, and it sounds like a gamelan.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-g01', 'label' => 'Bundengan', 'note' => 'pemusik dewasa memetik bundengan di teras rumah, dua anak mendengarkan; di sawah, penggembala bebek memakai kowangan (tudung bambu) di punggung', 'source' => 'BARU. Buat dengan prompt WNB-P07 di panduan gambar puzzle; foto rujukan WAJIB. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P07 di panduan gambar puzzle). Fakta baru yang wajib dicek: "suaranya mirip gamelan" (sumber: GNFI 2019). Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
                'h' => [
                    'title' => ['Kebun Teh Tambi', 'The Tambi Tea Garden'],
                    'ref'   => 'Kompas Travel (2023) Kebun Teh Tambi',
                    'items' => [
                        [   // wnb-1-h01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kebun teh Tambi di Wonosobo sudah ada sejak tahun 1865. Pucuk daun teh dipetik, diolah, lalu diseduh menjadi minuman hangat.',
                                'The Tambi tea estate in Wonosobo has been there since 1865. The young tea leaves are picked, processed and then brewed into a warm drink.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-h01', 'label' => 'Kebun Teh Tambi', 'note' => 'kebun teh Tambi: deretan teh di lereng, pemetik dewasa bercaping dengan keranjang, pohon peneduh, truk kecil di jalan berkelok, bangunan pabrik lama, Sindoro di latar', 'source' => 'BARU. Buat dengan prompt WNB-P08 di panduan gambar puzzle; foto rujukan disarankan. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P08 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
                'i' => [
                    'title' => ['Tari Topeng Lengger', 'The Topeng Lengger Dance'],
                    'ref'   => 'Visit Jawa Tengah (Tari Topeng Lengger); Kemendikbudristek: Penetapan WBTb 2020',
                    'items' => [
                        [   // wnb-1-i01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Tari Topeng Lengger adalah tari khas Wonosobo. Penarinya memakai topeng yang menggambarkan tokoh-tokoh dalam cerita. Pada tahun 2020, tarian ini ditetapkan sebagai Warisan Budaya Takbenda Indonesia.',
                                'The Topeng Lengger dance is a traditional dance of Wonosobo. The dancers wear masks that show the characters in a story. In 2020, the dance was named part of Indonesia\'s Intangible Cultural Heritage.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-i01', 'label' => 'Tari Topeng Lengger', 'note' => 'pentas Topeng Lengger di pendapa desa pada sore hari: penari bertopeng kayu dan penari berkebaya dengan sampur, pemain gamelan, penonton berjaket, bukit berkabut', 'source' => 'BARU. Buat dengan prompt WNB-P09 di panduan gambar puzzle; foto rujukan WAJIB. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P09 di panduan gambar puzzle). Fakta baru yang wajib dicek: "topeng menggambarkan tokoh" dan "Warisan Budaya Takbenda 2020" (sumber: Visit Jawa Tengah). Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
                'j' => [
                    'title' => ['Embun Upas', 'Embun Upas (Mountain Frost)'],
                    'ref'   => $ref['upas'],
                    'items' => [
                        [   // wnb-1-j01
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Pada musim kemarau, suhu pagi di Dataran Tinggi Dieng bisa turun di bawah 0 °C, sehingga embun membeku menjadi es tipis. Warga menyebutnya embun upas. Upas berarti racun, sebab es ini bisa merusak tanaman kentang.',
                                'In the dry season, early-morning temperatures on the Dieng Plateau can drop below 0 °C, so the dew freezes into thin ice. People call it embun upas. Upas means poison, because the ice can damage potato plants.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-1-j01', 'label' => 'Embun Upas', 'note' => 'pagi berembun beku di Dieng: es tipis putih menutupi sebagian rumput dan daun kentang, petani berjaket memeriksa tanaman, pengunjung memotret, siluet candi kecil di latar', 'source' => 'BARU. Buat dengan prompt WNB-P10 di panduan gambar puzzle; foto rujukan disarankan. Jalankan uji keping sebelum diunggah.'],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P10 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat.' . $note['retry'],
                            ],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------------ wnb-2 Isi Rumpang
        'wnb-2' => [
            'engine'           => 'rumpang',
            'variant'          => 'rumpang_bacaan',
            'indicator'        => 'literasi',
            'distractor_count' => 2,
            'title'            => ['Lengkapi Bacaan Wonosobo', 'Complete the Wonosobo Reading'],
            'instruction'      => [
                'Baca bacaannya sampai habis. Pikirkan hubungan antarkalimat, lalu pilih kata yang tepat. Ada dua kata yang tidak terpakai.',
                'Read the whole text. Think about how the sentences are connected, then choose the right words. Two words will not be used.',
            ],
            'description'      => [
                'Mbah Kedu menyiapkan catatan tentang Wonosobo, tetapi beberapa katanya hilang. Pikirkan hubungan antarkalimat, lalu lengkapi catatannya.',
                'Mbah Kedu prepared notes about Wonosobo, but some words are missing. Think about how the sentences are connected, then complete the notes.',
            ],
            'hints'            => [
                [
                    'Baca kalimat sebelum dan sesudah kotak kosong. Apakah isinya sebab, akibat, atau pertentangan?',
                    'Read the sentence before and after the empty box. Does it show a cause, a result, or a contrast?',
                ],
                [
                    '"Sehingga" menunjukkan akibat, "karena" menunjukkan sebab, dan "tetapi" menunjukkan pertentangan.',
                    '"So" shows a result, "because" shows a cause, and "but" shows a contrast.',
                ],
                [
                    'Ada dua kata pengecoh. Setelah semua kotak terisi, baca lagi: apakah setiap kalimat masuk akal?',
                    'There are two decoy words. When every box is filled, read it again: does each sentence make sense?',
                ],
            ],
            'variants' => [
                'a' => [
                    'title'       => ['Negeri di Atas Awan', 'The Land Above the Clouds'],
                    'ref'         => $ref['dieng'],
                    'distractors' => [['panas', 'hot'], ['agar', 'unless']],
                    'items'       => [
                        [   // wnb-2-a01
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Sebagian Dataran Tinggi Dieng berada di Kabupaten Wonosobo. Dataran tinggi ini terletak lebih dari 2.000 meter di atas permukaan laut. Dieng sering tertutup kabut ___ dijuluki negeri di atas awan.',
                                'Part of the Dieng Plateau lies in Wonosobo Regency. The plateau is more than 2,000 metres above sea level. Dieng is often covered in mist, ___ it is called the land above the clouds.',
                            ],
                            'answer'       => ['sehingga', 'so'],
                            'bloom_reason' => 'Menerapkan kata hubung sebab → akibat di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-a02
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Pada musim kemarau, embun pagi bisa membeku menjadi es ___ suhunya turun di bawah 0 °C.',
                                'In the dry season, the morning dew can freeze into ice ___ the temperature drops below 0 °C.',
                            ],
                            'answer'       => ['karena', 'because'],
                            'bloom_reason' => 'Menerapkan kata hubung akibat ← sebab di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-a03
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                "Wisatawan yang datang pada musim itu sebaiknya membawa pakaian ___.\n\nDieng juga cocok untuk carica, yaitu pepaya gunung.",
                                "Visitors who come in that season should bring ___ clothes.\n\nDieng is also good for carica, a mountain papaya.",
                            ],
                            'answer'       => ['hangat', 'warm'],
                            'bloom_reason' => 'Informasi suhu dipakai untuk keputusan baru.',
                        ],
                        [   // wnb-2-a04
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Carica tumbuh baik di ketinggian 1.400–2.400 meter, ___ sulit tumbuh di dataran rendah yang panas.',
                                'Carica grows well at 1,400–2,400 metres, ___ it struggles to grow in hot lowlands.',
                            ],
                            'answer'       => ['tetapi', 'but'],
                            'bloom_reason' => 'Menerapkan kata hubung pertentangan dua keadaan di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-a05
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Desa Sembungan berada di ketinggian sekitar 2.300 meter, jadi carica ___ tumbuh di desa itu.',
                                'Sembungan Village is about 2,300 metres high, so carica ___ grow in that village.',
                            ],
                            'answer'       => ['dapat', 'can'],
                            'bloom_reason' => 'Menerapkan rentang ketinggian pada kasus baru.',
                        ],
                        [   // wnb-2-a06
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Daerah pantai termasuk dataran rendah yang panas, jadi carica ___ tumbuh di sana.',
                                'Beaches are hot lowlands, so carica is ___ to grow there.',
                            ],
                            'answer'       => ['sulit', 'hard'],
                            'bloom_reason' => 'Menerapkan aturan pada kasus baru.',
                        ],
                        [   // wnb-2-a07
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C4',
                            'prompt'       => [
                                'Dari semua keterangan itu, kita tahu bahwa carica membutuhkan udara yang ___.',
                                'From all this information, we know that carica needs ___ air.',
                            ],
                            'answer'       => ['sejuk', 'cool'],
                            'bloom_reason' => 'Kesimpulan dari beberapa kalimat; pengecoh *panas* menguji ketelitian.',
                        ],
                    ],
                ],
                'b' => [
                    'title'       => ['Mi Ongklok', 'Ongklok Noodles'],
                    'ref'         => $ref['ongklok'],
                    'distractors' => [['agar', 'unless'], ['panas', 'hot']],
                    // Draft 2 moved wnb-2-b02 to the end of the passage, so every
                    // item here has an explicit key.
                    'items'       => [
                        [   // wnb-2-b01
                            'key'          => 'wnb-2-b01',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Mi ongklok adalah makanan khas Wonosobo. Mi kuning, kol, dan daun kucai dimasukkan ke dalam ongklok, yaitu keranjang bambu kecil, ___ keranjang itu dicelupkan berulang kali ke air mendidih.',
                                'Ongklok noodles are a special dish from Wonosobo. Yellow noodles, cabbage, and chives are put into an ongklok, a small bamboo basket, ___ the basket is dipped again and again into boiling water.',
                            ],
                            'answer'       => ['lalu', 'then'],
                            'bloom_reason' => 'Menerapkan urutan langkah memasak.',
                            'review'       => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: nama ongklok disebut di sini ("ongklok, yaitu keranjang bambu kecil") agar wnb-2-b02 harus menghubungkan informasi yang berjauhan. Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-2-b03
                            'key'          => 'wnb-2-b03',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                "Setelah ditiriskan, mi disiram kuah kental. Kuahnya kental ___ dibuat dari tepung kanji.\n\nMi ongklok biasanya dimakan bersama sate sapi dan tempe kemul.",
                                "After draining, the noodles are covered with a thick sauce. The sauce is thick ___ it is made from tapioca starch.\n\nOngklok noodles are usually eaten with beef satay and tempe kemul.",
                            ],
                            'answer'       => ['karena', 'because'],
                            'bloom_reason' => 'Menerapkan kata hubung akibat ← sebab di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-b04
                            'key'          => 'wnb-2-b04',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Udara Wonosobo dingin, ___ warga senang menyantap makanan berkuah yang hangat seperti mi ongklok.',
                                'The air in Wonosobo is cold, ___ people love to eat warm dishes with sauce, like ongklok noodles.',
                            ],
                            'answer'       => ['sehingga', 'so'],
                            'bloom_reason' => 'Menerapkan kata hubung sebab → akibat di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-b05
                            'key'          => 'wnb-2-b05',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Rina ingin memasak mi ongklok. Di rumahnya hanya ada mi kuning dan kol, jadi ia masih harus membeli daun ___.',
                                'Rina wants to cook ongklok noodles. At home she only has yellow noodles and cabbage, so she still needs to buy some ___.',
                            ],
                            'answer'       => ['kucai', 'chives'],
                            'bloom_reason' => 'Menerapkan daftar bahan pada situasi baru (Rina kekurangan satu bahan).',
                            'review'       => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: diubah menjadi situasi baru (Rina kekurangan satu bahan) agar sungguh C3; jawaban tetap. Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-2-b06
                            'key'          => 'wnb-2-b06',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Mi ongklok enak disantap selagi hangat, ___ kuahnya bisa sangat panas. Tiuplah dulu sebelum menyuapkannya.',
                                'Ongklok noodles taste best while warm, ___ the sauce can be very hot. Blow on it before you take a bite.',
                            ],
                            'answer'       => ['tetapi', 'but'],
                            'bloom_reason' => 'Menerapkan kata hubung pertentangan di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-b02
                            'key'          => 'wnb-2-b02',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C4',
                            'prompt'       => [
                                'Sekarang kamu tahu bahwa nama mi ongklok berasal dari nama ___ untuk merebusnya.',
                                'Now you know that the name of ongklok noodles comes from the name of the ___ used to boil them.',
                            ],
                            'answer'       => ['alat', 'tool'],
                            'bloom_reason' => 'Menganalisis asal-usul nama dengan menghubungkan informasi yang berjauhan (nama ongklok disebut di awal bacaan).',
                            'review'       => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: dipindah ke akhir bacaan; kalimat "Keranjang itu bernama ongklok." dihapus, jadi siswa menghubungkan informasi jauh (C4 sungguhan). Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-2-b07
                            'key'          => 'wnb-2-b07',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C4',
                            'prompt'       => [
                                'Dari semua keterangan itu, mi ongklok paling cocok disantap saat udara ___.',
                                'From all this information, ongklok noodles are best eaten when the air is ___.',
                            ],
                            'answer'       => ['dingin', 'cold'],
                            'bloom_reason' => 'Kesimpulan dari beberapa kalimat; pengecoh *panas*.',
                        ],
                    ],
                ],
                'c' => [
                    'title'       => ['Embun Upas', 'Embun Upas (Mountain Frost)'],
                    'ref'         => $ref['upas'],
                    'distractors' => [['berawan', 'cloudy'], ['siang', 'afternoon']],
                    // Draft 2 retired wnb-2-c05 and wnb-2-c07 (listed last) and
                    // added wnb-2-c08 and wnb-2-c09, so every item has an explicit key.
                    'items'       => [
                        [   // wnb-2-c01
                            'key'          => 'wnb-2-c01',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Pada musim kemarau, langit Dataran Tinggi Dieng sering sangat cerah. Pada malam hari, panas bumi cepat lepas ke angkasa ___ tidak ada awan yang menahannya.',
                                'In the dry season, the sky over the Dieng Plateau is often very clear. At night, the earth\'s heat escapes quickly into space ___ there are no clouds to hold it in.',
                            ],
                            'answer'       => ['karena', 'because'],
                            'bloom_reason' => 'Menerapkan kata hubung akibat ← sebab di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-c02
                            'key'          => 'wnb-2-c02',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Udara dingin lalu turun dan terkumpul di cekungan dataran tinggi, ___ suhu di dekat tanah bisa turun di bawah 0 °C.',
                                'Cold air then sinks and collects in the hollows of the plateau, ___ the temperature near the ground can drop below 0 °C.',
                            ],
                            'answer'       => ['sehingga', 'so'],
                            'bloom_reason' => 'Menerapkan kata hubung sebab → akibat di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-c03
                            'key'          => 'wnb-2-c03',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                "Saat suhu serendah itu, embun pagi ___ menjadi es tipis.\n\nWarga Dieng menyebut es tipis itu embun upas.",
                                "At such a low temperature, the morning dew ___ into thin ice.\n\nThe people of Dieng call this thin ice embun upas.",
                            ],
                            'answer'       => ['membeku', 'freezes'],
                            'bloom_reason' => 'Menerapkan pengetahuan suhu di bawah 0 °C.',
                        ],
                        [   // wnb-2-c04
                            'key'          => 'wnb-2-c04',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Bagi wisatawan, embun upas tampak indah, ___ bagi petani es itu bisa merusak daun kentang.',
                                'To visitors, embun upas looks beautiful, ___ for farmers the ice can damage potato leaves.',
                            ],
                            'answer'       => ['tetapi', 'but'],
                            'bloom_reason' => 'Menerapkan kata hubung pertentangan dua sudut pandang di dalam satu kalimat.',
                            'review'       => ['status' => 'needs_verification', 'note' => $note['linker']],
                        ],
                        [   // wnb-2-c08 (replaces wnb-2-c05)
                            'key'          => 'wnb-2-c08',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Dalam bahasa Jawa, upas berarti racun. Es itu dijuluki begitu karena bisa membuat tanaman kentang ___ lalu mati.',
                                'In Javanese, upas means poison. The ice got this nickname because it can make potato plants ___ and then die.',
                            ],
                            'answer'       => ['layu', 'wilt'],
                            'bloom_reason' => 'Menyimpulkan akibat es pada tanaman dari arti kata upas.',
                            'review'       => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: soal baru (C3, menyimpulkan akibat es pada tanaman; menggantikan wnb-2-c05 yang jawabannya tersurat di klausa sebab). Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-2-c06
                            'key'          => 'wnb-2-c06',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Wisatawan yang ingin melihat embun upas harus datang pada ___ hari, karena es itu cepat mencair saat matahari mulai terik.',
                                'Visitors who want to see embun upas must come in the ___, because the ice melts quickly when the sun gets strong.',
                            ],
                            'answer'       => ['pagi', 'morning'],
                            'bloom_reason' => 'Menerapkan informasi pada rencana kunjungan; pengecoh *siang*.',
                        ],
                        [   // wnb-2-c09 (replaces wnb-2-c07)
                            'key'          => 'wnb-2-c09',
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C4',
                            'prompt'       => [
                                'Dari semua keterangan itu, embun upas paling mudah terbentuk setelah malam yang ___.',
                                'From all this information, embun upas forms most easily after a ___ night.',
                            ],
                            'answer'       => ['cerah', 'clear'],
                            'bloom_reason' => 'Kesimpulan syarat terbentuknya embun upas dari seluruh bacaan; pengecoh *berawan*.',
                            'review'       => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: soal baru (C4, menyimpulkan syarat terbentuknya embun upas dari seluruh bacaan; menggantikan wnb-2-c07). Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-2-c05 · retired
                            'key'          => 'wnb-2-c05',
                            'active'       => false,
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Kata upas dalam bahasa Jawa berarti ___, sebab es itu seolah meracuni tanaman petani.',
                                'In Javanese, the word upas means ___, because the ice seems to poison the farmers\' plants.',
                            ],
                            'answer'       => ['racun', 'poison'],
                            'bloom_reason' => 'Menerapkan petunjuk konteks untuk arti kata.',
                            'review'       => [
                                'status' => 'verified',
                                'note'   => 'Revisi Oktober 2026: diistirahatkan (is_active = 0), diganti wnb-2-c08. Baris tidak dihapus agar data lama tetap tersambung.',
                            ],
                        ],
                        [   // wnb-2-c07 · retired
                            'key'          => 'wnb-2-c07',
                            'active'       => false,
                            'type'         => 'fill_blank_bank',
                            'bloom'        => 'C3',
                            'prompt'       => [
                                'Mereka juga perlu membawa jaket tebal karena udaranya sangat ___.',
                                'They also need a thick jacket because the air is very ___.',
                            ],
                            'answer'       => ['dingin', 'cold'],
                            'bloom_reason' => 'Menerapkan informasi suhu.',
                            'review'       => [
                                'status' => 'verified',
                                'note'   => 'Revisi Oktober 2026: diistirahatkan (is_active = 0), diganti wnb-2-c09. Baris tidak dihapus agar data lama tetap tersambung.',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------- wnb-3 Benar atau Salah
        'wnb-3' => [
            'engine'      => 'boleh',
            'variant'     => 'benar_salah_bacaan',
            'indicator'   => 'literasi',
            'title'       => ['Benar atau Salah? Wonosobo', 'True or False? Wonosobo'],
            'instruction' => [
                'Baca ketiga paragraf. Bandingkan informasinya, lalu pilih BENAR atau SALAH berdasarkan bukti di bacaan.',
                'Read all three paragraphs. Compare the information, then choose TRUE or FALSE based on the evidence in the text.',
            ],
            'description' => [
                'Kabar tentang Dieng beredar cepat, tetapi tidak semuanya benar. Bandingkan informasinya, nilai setiap pilihan, lalu putuskan berdasarkan bukti.',
                'News about Dieng spreads fast, but not all of it is true. Compare the information, judge each choice, then decide based on the evidence.',
            ],
            'hints'       => [
                [
                    'Bandingkan: siapa yang membuat setiap kabar? Apakah nama dan tanggalnya jelas?',
                    'Compare: who made each piece of news? Are the name and date clear?',
                ],
                [
                    'Pilihan yang baik didukung bukti di bacaan, bukan hanya karena terdengar menarik.',
                    'A good choice is supported by evidence in the reading, not just because it sounds exciting.',
                ],
                [
                    'Pada pesan berantai, waspadai kata seperti "pasti", "sebarkan!", atau "semua orang juga begitu".',
                    'In chain messages, watch out for words like "surely", "share this!", or "everyone does it".',
                ],
            ],
            'variants' => [
                'a' => [
                    'passage' => [
                        'title' => ['Dua Kabar tentang Telaga Warna', 'Two Pieces of News about Telaga Warna'],
                        'body'  => [
                            "Raka membaca dua kabar tentang Telaga Warna di Dieng. Kabar pertama berasal dari papan informasi pengelola kawasan wisata. Isinya: air Telaga Warna mengandung belerang, sehingga warnanya bisa tampak hijau, biru, atau kekuningan. Air telaga tidak untuk diminum.\n\nKabar kedua adalah pesan berantai di grup percakapan. Pesan itu tidak mencantumkan nama pembuat maupun tanggal. Isinya: \"Minum air Telaga Warna bikin awet muda! Sebarkan ke semua grup!\"\n\nRaka ingin tahu kabar mana yang dapat dipercaya. Ia memeriksa siapa pembuat setiap kabar, lalu bertanya kepada gurunya. Gurunya menjelaskan bahwa air yang mengandung belerang tidak aman untuk diminum.",
                            "Raka read two pieces of news about Telaga Warna in Dieng. The first came from an information board put up by the managers of the tourist area. It said: the water of Telaga Warna contains sulphur, so its colour can look green, blue, or yellowish. The lake water is not for drinking.\n\nThe second was a chain message in a chat group. It did not show the name of whoever made it or a date. It said: \"Drinking Telaga Warna water keeps you young! Share this with every group!\"\n\nRaka wanted to know which news he could trust. He checked who had made each piece of news, then asked his teacher. His teacher explained that water containing sulphur is not safe to drink.",
                        ],
                        'ref'   => $ref['news'],
                    ],
                    'items'   => [
                        [   // wnb-3-a01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Kabar pertama lebih dapat dipercaya daripada kabar kedua karena pembuatnya jelas.',
                                'The first piece of news is more trustworthy than the second because its maker is clear.',
                            ],
                        ],
                        [   // wnb-3-a02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_informasi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Kedua kabar sama-sama membicarakan air Telaga Warna, tetapi isinya bertentangan.',
                                'Both pieces of news talk about the water of Telaga Warna, but they say opposite things.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi v2 (17 Okt 2026): satu pernyataan "sama-sama" kini BENAR, agar kata "sama" tidak selalu berarti salah; kunci salah → benar. | Sebelumnya: Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026).',
                            ],
                        ],
                        [   // wnb-3-a03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_informasi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Kedua kabar sama-sama menyarankan pengunjung meminum air telaga.',
                                'Both pieces of news suggest that visitors drink the lake water.',
                            ],
                        ],
                        [   // wnb-3-a04
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Langkah Raka memeriksa pembuat kabar lalu bertanya kepada guru adalah pilihan yang tepat.',
                                'Raka\'s steps of checking who made the news and then asking his teacher were the right choice.',
                            ],
                        ],
                        [   // wnb-3-a05
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Bertanya kepada teman sekelas akan lebih membantu Raka daripada bertanya kepada guru.',
                                'Asking a classmate would have helped Raka more than asking his teacher.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: pernyataan SALAH baru tanpa kata ekstrem; menilai sumber (guru vs teman) berdasarkan bukti. Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-3-a06
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menentukan_solusi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Berdasarkan bukti dalam bacaan, pengunjung yang haus boleh meminum air Telaga Warna asalkan airnya tampak jernih.',
                                'Based on the evidence in the text, thirsty visitors may drink the water of Telaga Warna as long as it looks clear.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi v2 (17 Okt 2026): solusi SALAH yang masuk akal (air jernih dianggap aman) untuk menjaga imbangan 3 : 3; kunci benar → salah. | Sebelumnya: Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026).',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'passage' => [
                        'title' => ['Berangkat ke Bukit Sikunir', 'Off to Sikunir Hill'],
                        'body'  => [
                            "Kelas Sinta akan berwisata ke Bukit Sikunir di Desa Sembungan, Wonosobo. Desa Sembungan berada di ketinggian sekitar 2.300 meter dan disebut desa tertinggi di Pulau Jawa. Wisatawan biasanya mendaki Bukit Sikunir sebelum subuh untuk melihat matahari terbit.\n\nPengelola desa wisata memberi pesan: suhu subuh di puncak sangat dingin, jadi bawalah jaket, sarung tangan, dan senter. Setiap tahun sekitar 250.000 wisatawan datang ke Sembungan. Agar alamnya tetap indah, wisatawan diminta tetap berjalan di jalur yang disediakan. Di puncak bukit tidak ada tempat sampah.\n\nTeman Sinta, Bayu, berkata, \"Tidak usah bawa jaket. Di puncak pasti hangat seperti di kota.\" Sinta lalu membandingkan ucapan Bayu dengan pesan pengelola. Ia memutuskan tetap membawa jaket.",
                            "Sinta's class is going on a trip to Sikunir Hill in Sembungan Village, Wonosobo. Sembungan Village is about 2,300 metres high and is called the highest village in Java. Visitors usually climb Sikunir Hill before dawn to watch the sun rise.\n\nThe managers of the tourist village give this advice: dawn at the top is very cold, so bring a jacket, gloves, and a torch. About 250,000 visitors come to Sembungan every year. To keep nature beautiful, visitors are asked to stay on the paths provided. There are no rubbish bins at the top of the hill.\n\nSinta's friend Bayu said, \"No need for a jacket. It must be warm at the top, just like in town.\" Sinta compared what Bayu said with the managers' advice. She decided to bring her jacket anyway.",
                        ],
                        'ref'   => $ref['sikunir'],
                    ],
                    'items'   => [
                        [   // wnb-3-b01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_informasi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Ucapan Bayu dan pesan pengelola memberikan keterangan yang sama tentang suhu di puncak.',
                                'Bayu\'s words and the managers\' advice say the same thing about the temperature at the top.',
                            ],
                        ],
                        [   // wnb-3-b02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Keputusan Sinta membawa jaket didukung oleh pesan pengelola desa wisata.',
                                'Sinta\'s decision to bring a jacket is supported by the tourist village managers\' advice.',
                            ],
                        ],
                        [   // wnb-3-b03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_informasi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Senter perlu dibawa karena pendakian dilakukan sebelum matahari terbit.',
                                'A torch is needed because the climb happens before sunrise.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi v2 (17 Okt 2026): isi ditukar dengan wnb-3-b04 agar kunci variasi b tidak berselang-seling S-B-S-B-S-B. | Sebelumnya: Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026).',
                            ],
                        ],
                        [   // wnb-3-b04
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Ucapan Bayu adalah bukti yang kuat bahwa puncak Sikunir hangat.',
                                'Bayu\'s words are strong evidence that the top of Sikunir is warm.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi v2 (17 Okt 2026): isi ditukar dengan wnb-3-b03 agar kunci variasi b tidak berselang-seling S-B-S-B-S-B. | Sebelumnya: Disetujui peneliti sebagai bank soal final penelitian kelas 5 (Oktober 2026).',
                            ],
                        ],
                        [   // wnb-3-b05
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Karena wisatawan di Sembungan sangat banyak, keluar sebentar dari jalur untuk berfoto bukanlah masalah.',
                                'Because Sembungan has so many visitors, leaving the path for a moment to take a photo is not a problem.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: pernyataan SALAH baru tanpa kata ekstrem; memakai alasan jumlah wisatawan yang masuk akal di permukaan. Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-3-b06
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menentukan_solusi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Karena wisatawannya sangat banyak, solusi yang tepat agar Sikunir tetap bersih adalah setiap wisatawan membawa pulang sampahnya sendiri.',
                                'Because there are so many visitors, the right solution to keep Sikunir clean is for every visitor to take their own rubbish home.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: solusi tidak lagi tersurat di bacaan (pesan membawa pulang sampah dihapus, diganti fakta tidak ada tempat sampah); kini sungguh C5. Perlu dicek ulang peneliti.',
                            ],
                        ],
                    ],
                ],
                'c' => [
                    'passage' => [
                        'title' => ['Kabar tentang Kawah', 'News about the Crater'],
                        'body'  => [
                            "Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api. Karena itu, di sana ada beberapa kawah. Di sekitar kawah dapat keluar gas yang berbahaya bagi kesehatan. Petugas memasang rambu dan pagar pembatas agar pengunjung tetap berada pada jarak aman.\n\nSuatu hari, keluarga Dinda hendak berwisata ke sebuah kawah di Dieng. Pagi itu, beredar pesan berantai tanpa nama pembuat: \"Kawah aman kok, dekati saja biar fotonya bagus!\" Pada saat yang sama, akun resmi badan gunung api (PVMBG) mengumumkan bahwa pengunjung harus mengikuti rambu dan menjaga jarak aman dari kawah.\n\nDinda membandingkan kedua kabar itu. Ia melihat bahwa pengumuman resmi menyebutkan lembaga pembuatnya, sedangkan pesan berantai tidak. Keluarga Dinda akhirnya tetap berwisata, tetapi mereka berdiri di belakang pagar pembatas saat berfoto.",
                            "The Dieng Plateau was formed by volcanic activity. That is why it has several craters. Gas that is harmful to health can come out around the craters. Officers put up signs and safety fences so that visitors stay a safe distance away.\n\nOne day, Dinda's family planned to visit a crater in Dieng. That morning, a chain message with no maker's name was spreading: \"The crater is safe, just go close for a nice photo!\" At the same time, the official account of the volcano agency (PVMBG) announced that visitors must follow the signs and keep a safe distance from the crater.\n\nDinda compared the two pieces of news. She noticed that the official announcement named the agency that made it, but the chain message did not. In the end, Dinda's family still went on the trip, but they stood behind the safety fence when taking photos.",
                        ],
                        'ref'   => $ref['news'],
                    ],
                    'items'   => [
                        [   // wnb-3-c01
                            'type'   => 'verdict_card',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_informasi',
                            'answer' => 'benar',
                            'prompt' => [
                                'Pesan berantai dan pengumuman resmi memberi saran yang berbeda tentang mendekati kawah.',
                                'The chain message and the official announcement give different advice about going near the crater.',
                            ],
                        ],
                        [   // wnb-3-c02
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Pesan berantai itu dapat dipercaya karena sudah banyak beredar pagi itu.',
                                'The chain message can be trusted because it was spreading widely that morning.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: pernyataan SALAH baru tanpa kata ekstrem; menguji alasan keliru (banyak beredar = benar). Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-3-c03
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Pengumuman resmi dapat dipercaya karena lembaga pembuatnya jelas.',
                                'The official announcement can be trusted because the agency that made it is clear.',
                            ],
                        ],
                        [   // wnb-3-c04
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'benar',
                            'prompt' => [
                                'Rambu dan pagar dari petugas lebih pantas diikuti daripada pesan berantai, karena petugas mengetahui bahaya gas di kawah.',
                                'The officers\' signs and fences deserve to be followed more than the chain message, because the officers know about the dangerous gas at the crater.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: pernyataan BENAR baru tingkat C5 (menilai sumber mana yang pantas diikuti); menggantikan sebab–akibat tersurat. Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-3-c05
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_pilihan',
                            'answer' => 'salah',
                            'prompt' => [
                                'Keluarga Dinda boleh mendekati kawah asalkan cuaca pagi itu cerah.',
                                'Dinda\'s family may go near the crater as long as the morning weather is clear.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: pernyataan SALAH baru tanpa kata ekstrem; syarat palsu (cuaca cerah). Perlu dicek ulang peneliti.',
                            ],
                        ],
                        [   // wnb-3-c06
                            'type'   => 'verdict_card',
                            'bloom'  => 'C5',
                            'skill'  => 'menentukan_solusi',
                            'answer' => 'salah',
                            'prompt' => [
                                'Berdasarkan bukti dalam bacaan, keluarga Dinda harus membatalkan wisata karena pengumuman resmi melarang pengunjung datang ke kawah.',
                                'Based on the evidence in the text, Dinda\'s family must cancel the trip because the official announcement bans visitors from coming to the crater.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi v2 (17 Okt 2026): premis keliru ("pengumuman resmi melarang datang") menggantikan "sebaiknya membatalkan wisata", yang bisa mengajarkan bahwa sikap hati-hati itu salah. | Sebelumnya: Revisi Oktober 2026: pernyataan SALAH baru tanpa "pasti/semua"; tokoh Dimas → Dinda. Perlu dicek ulang peneliti.',
                            ],
                        ],
                    ],
                ],
            ],
        ],

        // ------------------------------------------------- wnb-4 Cari Objek
        'wnb-4' => [
            'engine'      => 'cari',
            'variant'     => 'cari_objek',
            'indicator'   => 'budaya',
            'title'       => ['Cari Objek Wonosobo', 'Find the Objects: Wonosobo'],
            'instruction' => [
                'Petunjuk Mbah Kedu berupa teka-teki. Pikirkan ciri-ciri bendanya, lalu ketuk yang tepat.',
                'Mbah Kedu\'s clues are riddles. Think about each object\'s features, then tap the right one.',
            ],
            'description' => [
                'Kabut Dieng menyembunyikan benda-benda khas Wonosobo di antara benda yang mirip. Pecahkan teka-teki Mbah Kedu dan temukan keempatnya.',
                'The Dieng mist hides Wonosobo\'s special objects among look-alikes. Solve Mbah Kedu\'s riddles and find all four.',
            ],
            'scene'       => ['key' => 'challenge.wnb-4.scene', 'label' => 'Pasar Oleh-oleh Dieng', 'note' => 'pasar oleh-oleh Dieng berkabut tipis, rak bambu beratap, meja kayu, dan bangku bambu di depan (permukaan ± 43 %, 58 %, 75 % tinggi gambar), kebun teh & candi di latar'],
            'hints'       => [
                [
                    'Petunjuknya berupa teka-teki. Pikirkan bentuk, bahan, dan kegunaan benda.',
                    'The clues are riddles. Think about each object\'s shape, material, and use.',
                ],
                [
                    'Ada benda yang mirip: sama-sama dari bambu, sama-sama buah, atau sama-sama alat musik. Baca setiap ciri di petunjuk.',
                    'Some objects look alike: both made of bamboo, both fruit, or both instruments. Check every feature in the clue.',
                ],
                ['Ingat cerita Mbah Kedu di Kenali Wonosobo.', 'Remember Mbah Kedu\'s story in Get to Know Wonosobo.'],
            ],
            'variants' => [
                'a' => [
                    'title' => ['Pasar Oleh-oleh Dieng', 'Dieng Souvenir Market'],
                    'ref'   => $ref['hunt'],
                    'items' => [
                        [   // wnb-4-a01
                            'type'   => 'find_object',
                            'bloom'  => 'C3',
                            'x'      => 31,
                            'y'      => 26,
                            'w'      => 10,
                            'prompt' => [
                                'Temukan alat musik berbentuk tudung lengkung dari anyaman bambu. Dulu tudung itu melindungi penggembala bebek dari panas dan hujan, lalu diberi senar untuk dipetik.',
                                'Find the musical instrument shaped like a curved hood of woven bamboo. The hood once protected duck herders from the sun and rain, and then strings were added to pluck.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a01', 'label' => 'Bundengan', 'note' => 'bundengan (tudung bambu lengkung bersenar)'],
                            'review' => ['status' => 'needs_verification', 'note' => $note['hunt']],
                        ],
                        [   // wnb-4-a02
                            'type'   => 'find_object',
                            'bloom'  => 'C3',
                            'x'      => 75,
                            'y'      => 41.5,
                            'w'      => 10,
                            'prompt' => [
                                'Temukan buah pegunungan yang mirip pepaya, tetapi ukurannya kecil. Buah ini tumbuh baik di udara sejuk dan sering dibuat manisan.',
                                'Find the mountain fruit that looks like a papaya but is small. It grows well in cool air and is often made into sweets.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a02', 'label' => 'Buah carica', 'note' => 'beberapa buah carica kuning kecil'],
                            'review' => ['status' => 'needs_verification', 'note' => $note['hunt']],
                        ],
                        [   // wnb-4-a03
                            'type'   => 'find_object',
                            'bloom'  => 'C3',
                            'x'      => 35,
                            'y'      => 41.5,
                            'w'      => 10,
                            'prompt' => [
                                'Temukan keranjang bambu kecil yang dicelup-celupkan ke air mendidih. Nama alat ini dipakai untuk menamai mi khas Wonosobo.',
                                'Find the small bamboo basket that is dipped again and again into boiling water. Its name was used to name Wonosobo\'s special noodles.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a03', 'label' => 'Keranjang ongklok', 'note' => 'keranjang ongklok bambu kecil bergagang'],
                            'review' => ['status' => 'needs_verification', 'note' => $note['hunt']],
                        ],
                        [   // wnb-4-a04
                            'type'   => 'find_object',
                            'bloom'  => 'C3',
                            'x'      => 65,
                            'y'      => 59,
                            'w'      => 10,
                            'prompt' => [
                                'Temukan daun yang diseduh menjadi minuman hangat. Daun ini dipetik di perkebunan Tambi yang sudah ada sejak tahun 1865.',
                                'Find the leaves that are brewed into a warm drink. They are picked at the Tambi estate, which has existed since 1865.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a04', 'label' => 'Daun teh', 'note' => 'daun teh segar (pucuk)'],
                        ],
                        [   // wnb-4-a05 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 45,
                            'y'      => 59,
                            'w'      => 10,
                            'prompt' => ['Pepaya biasa (pengecoh)', 'Ordinary papaya (decoy)'],
                            'wrong'  => [
                                'Ini pepaya biasa yang tumbuh di dataran rendah yang panas. Carica lebih kecil dan tumbuh di pegunungan yang sejuk.',
                                'This is an ordinary papaya that grows in hot lowlands. Carica is smaller and grows in cool mountains.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a05', 'label' => 'Pepaya biasa', 'note' => 'pepaya biasa (besar)'],
                        ],
                        [   // wnb-4-a06 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 7,
                            'y'      => 59,
                            'w'      => 10,
                            'prompt' => ['Rigen (pengecoh)', 'Rigen (decoy)'],
                            'wrong'  => [
                                'Ini rigen dari Temanggung, anyaman bambu lebar untuk menjemur. Rigen tidak dipakai untuk merebus mi.',
                                'This is a rigen from Temanggung, a wide woven bamboo tray for drying. It is not used to boil noodles.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a06', 'label' => 'Rigen', 'note' => 'rigen (anyaman bambu persegi panjang, kosong)'],
                        ],
                        [   // wnb-4-a07 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 25,
                            'y'      => 59,
                            'w'      => 10,
                            'prompt' => ['Semangkuk mi ongklok (pengecoh)', 'A bowl of ongklok noodles (decoy)'],
                            'wrong'  => [
                                'Ini semangkuk mi ongklok. Petunjuk meminta alat yang namanya dipakai untuk menamai mi itu, bukan minya.',
                                'This is a bowl of ongklok noodles. The clue asks for the tool that gave the noodles their name, not the noodles themselves.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a07', 'label' => 'Semangkuk mi ongklok', 'note' => 'semangkuk mi ongklok'],
                        ],
                        [   // wnb-4-a08 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 47,
                            'y'      => 26,
                            'w'      => 10,
                            'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'],
                            'wrong'  => [
                                'Ini angklung dari Jawa Barat. Angklung digoyang, bukan dipetik, dan tidak berasal dari tudung penggembala.',
                                'This is an angklung from West Java. It is shaken, not plucked, and it did not come from a herder\'s hood.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a08', 'label' => 'Angklung', 'note' => 'angklung bambu'],
                        ],
                        [   // wnb-4-a09 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 15,
                            'y'      => 41.5,
                            'w'      => 10,
                            'prompt' => ['Sasando (pengecoh)', 'Sasando (decoy)'],
                            'wrong'  => [
                                'Ini sasando dari Pulau Rote, Nusa Tenggara Timur. Sasando juga dipetik, tetapi tidak berasal dari tudung penggembala bebek.',
                                'This is a sasando from Rote Island, East Nusa Tenggara. It is plucked too, but it did not come from a duck herder\'s hood.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a09', 'label' => 'Sasando', 'note' => 'sasando'],
                        ],
                        [   // wnb-4-a10 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 83,
                            'y'      => 59,
                            'w'      => 10,
                            'prompt' => ['Biji kopi (pengecoh)', 'Coffee beans (decoy)'],
                            'wrong'  => [
                                'Ini biji kopi dari lereng Sindoro–Sumbing di Temanggung. Petunjuk meminta daun, bukan biji.',
                                'These are coffee beans from the Sindoro–Sumbing slopes in Temanggung. The clue asks for leaves, not beans.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a10', 'label' => 'Biji kopi', 'note' => 'segenggam biji kopi'],
                        ],
                        [   // wnb-4-a11 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 55,
                            'y'      => 41.5,
                            'w'      => 10,
                            'prompt' => ['Kuluk Topeng Ireng (pengecoh)', 'Topeng Ireng headdress (decoy)'],
                            'wrong'  => [
                                'Ini kuluk penari Topeng Ireng dari Magelang — bukan benda yang dimaksud petunjuk.',
                                'This is the headdress of Topeng Ireng dancers from Magelang — not the object in the clue.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a11', 'label' => 'Kuluk Topeng Ireng', 'note' => 'kuluk bulu Topeng Ireng'],
                        ],
                        [   // wnb-4-a12 · pengecoh
                            'type'   => 'find_object',
                            'decoy'  => true,
                            'x'      => 63,
                            'y'      => 26,
                            'w'      => 10,
                            'prompt' => ['Getuk (pengecoh)', 'Getuk (decoy)'],
                            'wrong'  => [
                                'Ini getuk dari Magelang — bukan benda yang dimaksud petunjuk.',
                                'This is getuk from Magelang — not the object in the clue.',
                            ],
                            'media'  => ['key' => 'challenge.item.wnb-4-a12', 'label' => 'Getuk', 'note' => 'getuk'],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
