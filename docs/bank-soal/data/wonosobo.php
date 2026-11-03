<?php

/**
 * Bank soal kelas 5 — WONOSOBO (wilayah 3, tingkat sulit).
 *
 * Research bank of revision 10 (docs/revisi-10-analitik/08_DRAF_BANK_SOAL.md;
 * English: 36, AI draft), format of 31 §6.4, written by
 * docs/bank-soal/tools/import_08.py on 3 November 2026 and edited by hand
 * from then on. Every item of 08 is `needs_verification` until expert
 * validation. The old bank is archived in docs/arsip/bank-soal-lama/.
 *
 * Shape (docs/bank-soal/README.md → Bentuk berkas data): texts are pairs
 * [Indonesia, English]; labels per item `dim`, `sub`, `akm`, `pirls`,
 * `bloom` (developer labels; `labels` => 'expert' once they come from the
 * blind labelling); `trap`/`special` (Isi Rumpang), `code` (statements and
 * round-1 decoys), `main` (round-2 lures). build-workbook.php derives the
 * full error-code matrices (K6). Line markers of digital texts: `>> ` header,
 * `~~ ` small meta line, `:: ` entry (comment, message, search result).
 */

return [
    'difficulty' => 'sulit',
    'nodes'      => [
        'wnb-1' => [
            'engine'      => 'puzzle',
            'variant'     => 'susun_gambar',
            'indicator'   => '',
            'title'       => ['Susun Gambar Wonosobo', 'Wonosobo Picture Puzzle'],
            'instruction' => [
                'Kepingannya berbentuk puzzle (4 × 4). Ketuk dua keping untuk menukar tempatnya sampai gambarnya utuh, lalu baca keterangannya.',
                'The pieces are jigsaw-shaped (4 × 4). Tap two pieces to swap them until the picture is complete, then read the caption.',
            ],
            'description' => [
                'Kabut tebal Dieng memotong-motong sebuah lukisan Wonosobo dan Dieng seperti kepingan puzzle. Susun kembali kepingnya, lalu kenali tempat di gambar itu.',
                'The thick Dieng mist has cut a painting of Wonosobo and Dieng into jigsaw pieces. Put the pieces back together, then get to know the place in the picture.',
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
            'variants'    => [
                'a' => [
                    'title' => ['Kompleks Candi Arjuna', 'The Arjuna Temple Complex'],
                    'ref'   => 'Wikipedia: Dataran Tinggi Dieng; Wikipedia: Kompleks Candi Arjuna',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kompleks Candi Arjuna ada di Desa Dieng Kulon, Banjarnegara, tetangga Wonosobo di Dataran Tinggi Dieng. Kompleks ini salah satu kompleks candi Hindu tertua di Jawa. Candi-candinya diberi nama tokoh wayang, seperti Arjuna, Semar, Srikandi, dan Puntadewa.',
                                'The Arjuna Temple complex is in Dieng Kulon Village, Banjarnegara, Wonosobo\'s neighbour on the Dieng Plateau. It is one of the oldest Hindu temple complexes in Java. Its temples are named after wayang characters such as Arjuna, Semar, Srikandi and Puntadewa.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-d01',
                                'label'  => 'Kompleks Candi Arjuna',
                                'note'   => 'deretan candi Hindu kecil dari andesit gelap di lapangan rumput Dieng, Candi Semar menghadap Candi Arjuna, bukit berteras kentang, pengunjung berjaket',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P04; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P04 di panduan gambar puzzle). Fakta baru yang wajib dicek: "diberi nama tokoh wayang". Lokasi administratifnya Kabupaten Banjarnegara, jadi tulis "di Dataran Tinggi Dieng", jangan "di Wonosobo".. Gambar belum dibuat. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title' => ['Tari Topeng Lengger', 'The Topeng Lengger Dance'],
                    'ref'   => 'Visit Jawa Tengah (Tari Topeng Lengger); Kemendikbudristek: Penetapan WBTb 2020',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Tari Topeng Lengger adalah tari khas Wonosobo. Penarinya memakai topeng yang menggambarkan tokoh-tokoh dalam cerita. Pada tahun 2020, tarian ini ditetapkan sebagai Warisan Budaya Takbenda Indonesia.',
                                'The Topeng Lengger dance is a traditional dance of Wonosobo. The dancers wear masks that show the characters in a story. In 2020, the dance was named part of Indonesia\'s Intangible Cultural Heritage.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-i01',
                                'label'  => 'Tari Topeng Lengger',
                                'note'   => 'pentas Topeng Lengger di pendapa desa pada sore hari: penari bertopeng kayu dan penari berkebaya dengan sampur, pemain gamelan, penonton berjaket, bukit berkabut',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P09; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P09 di panduan gambar puzzle). Fakta baru yang wajib dicek: "topeng menggambarkan tokoh" dan "Warisan Budaya Takbenda 2020" (sumber: Visit Jawa Tengah). Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'c' => [
                    'title' => ['Telaga Warna', 'Telaga Warna'],
                    'ref'   => 'Wikipedia: Dataran Tinggi Dieng; Tempo & Kompas Travel (Desa Sembungan); Indonesia Kaya (Mie Ongklok)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Air Telaga Warna di Dieng mengandung belerang. Saat terkena cahaya matahari, warnanya tampak berganti: hijau, biru, atau kekuningan. Airnya tidak untuk diminum.',
                                'The water of Telaga Warna in Dieng contains sulphur. In sunlight its colour seems to change: green, blue, or yellowish. The water is not for drinking.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-05',
                                'label'  => 'Telaga Warna',
                                'note'   => 'Telaga Warna dari titik pandang tinggi: air hijau, biru, dan kekuningan berbelerang, Telaga Pengilon yang lebih gelap, hutan cemara, jalan papan dengan pengunjung kecil',
                                'source' => 'Lulus uji keping. Warna air sudah hijau-biru; pastikan ada bagian kekuningan di tepi telaga sesuai keterangan baru. Rujukan: https://commons.wikimedia.org/wiki/File:Telaga_Warna_Dieng_Jawa_Tengah.jpg',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: warna telaga diselaraskan dengan naskah: hijau, biru, atau kekuningan. Perlu dicek ulang peneliti. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'd' => [
                    'title' => ['Matahari Terbit di Sikunir', 'Sunrise at Sikunir'],
                    'ref'   => 'Wikipedia: Dataran Tinggi Dieng; Tempo & Kompas Travel (Desa Sembungan); Indonesia Kaya (Mie Ongklok)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Bukit Sikunir ada di Desa Sembungan, yang disebut-sebut sebagai desa tertinggi di Pulau Jawa (sekitar 2.300 meter). Wisatawan mendaki sebelum subuh untuk melihat matahari terbit di atas awan.',
                                'Sikunir Hill is in Sembungan Village, often called the highest village in Java (about 2,300 metres). Visitors climb before dawn to watch the sun rise above the clouds.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-06',
                                'label'  => 'Matahari Terbit di Sikunir',
                                'note'   => 'fajar dari puncak Sikunir: lautan awan bertekstur (emas dekat matahari, ungu-biru menjauh), kerucut Sindoro menembus awan, pengunjung berjaket di balik pagar, anak memegang senter',
                                'source' => 'Boleh dipakai (lulus dengan catatan, K3~K4 langit mirip). Rujukan: https://commons.wikimedia.org/wiki/File:Sunrise_di_Bukit_Sikunir.jpg',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: catatan isi gambar diperbarui (lautan awan bertekstur, Sindoro menembus awan). Perlu dicek ulang peneliti. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'e' => [
                    'title' => ['Mi Ongklok', 'Ongklok Noodles'],
                    'ref'   => 'Wikipedia: Dataran Tinggi Dieng; Tempo & Kompas Travel (Desa Sembungan); Indonesia Kaya (Mie Ongklok)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Mi ongklok adalah makanan khas Wonosobo. Mi dan sayur direbus di keranjang bambu kecil bernama ongklok, lalu disiram kuah kental dan disajikan bersama sate sapi.',
                                'Ongklok noodles are Wonosobo\'s special dish. The noodles and vegetables are boiled in a small bamboo basket called an ongklok, then covered with a thick sauce and served with beef satay.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-07',
                                'label'  => 'Mi Ongklok',
                                'note'   => 'warung mi ongklok dari sudut 3/4 (bukan tampak atas): mangkuk mi ongklok, sate sapi, tempe kemul, juru masak mencelup keranjang ongklok, jendela ke perbukitan berkabut',
                                'source' => 'Sudah dibuat ulang di Katalog Aset (14 Oktober 2026, prompt WNB-P03; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang. Rujukan: https://commons.wikimedia.org/wiki/File:Mi_ongklok_sate_sapi_Wonosobo.JPG',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: catatan isi gambar: sudut 3/4, bukan tampak atas; kuah kental kecokelatan. Perlu dicek ulang peneliti. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'f' => [
                    'title' => ['Kawah di Dieng: Tetap di Balik Pagar', 'Dieng Crater: Stay Behind the Fence'],
                    'ref'   => 'Laman resmi PVMBG/MAGMA Indonesia; pengelola kawasan Dieng',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api, sehingga di sana ada beberapa kawah. Gas di sekitar kawah dapat berbahaya, jadi pengunjung harus mengikuti rambu dan tetap berdiri di belakang pagar pembatas.',
                                'The Dieng Plateau was formed by volcanic activity, so it has several craters. Gas around a crater can be harmful, so visitors must follow the signs and stay behind the safety fence.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-e01',
                                'label'  => 'Kawah di Dieng: Tetap di Balik Pagar',
                                'note'   => 'kawah beruap di Dieng: kolam lumpur abu-abu, endapan belerang kuning, pagar pengaman dan jalan papan, rambu bergambar tanpa tulisan, petugas, pengunjung di balik pagar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P05; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P05 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'g' => [
                    'title' => ['Carica, Pepaya Gunung', 'Carica, the Mountain Papaya'],
                    'ref'   => 'Wikipedia (Pepaya gunung); Indonesia Kaya',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Carica adalah pepaya gunung yang buahnya kecil. Carica tumbuh baik di udara sejuk pada ketinggian 1.400–2.400 meter, dan buahnya sering dibuat manisan.',
                                'Carica is a mountain papaya with small fruit. It grows well in cool air at 1,400–2,400 metres, and its fruit is often made into sweets.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-f01',
                                'label'  => 'Carica, Pepaya Gunung',
                                'note'   => 'kebun carica di teras Dieng: pohon carica berbuah kuning kecil bergerigi, petani berjaket memetik, teras rumah dengan buah dikupas dan stoples manisan carica',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P06; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P06 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'h' => [
                    'title' => ['Bundengan', 'Bundengan'],
                    'ref'   => 'BRIN Audiovisual (Bundengan); GNFI (2019); Indonesia Kaya',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Bundengan adalah alat musik khas Wonosobo. Dulu benda ini berupa kowangan, tudung anyaman bambu yang melindungi penggembala bebek dari panas dan hujan. Tudung itu kemudian diberi senar dan dipetik; suaranya mirip gamelan.',
                                'The bundengan is a musical instrument from Wonosobo. It began as a kowangan, a woven bamboo hood that protected duck herders from the sun and rain. Strings were later added to pluck, and it sounds like a gamelan.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-g01',
                                'label'  => 'Bundengan',
                                'note'   => 'pemusik dewasa memetik bundengan di teras rumah, dua anak mendengarkan; di sawah, penggembala bebek memakai kowangan (tudung bambu) di punggung',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P07; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P07 di panduan gambar puzzle). Fakta baru yang wajib dicek: "suaranya mirip gamelan" (sumber: GNFI 2019). Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'i' => [
                    'title' => ['Kebun Teh Tambi', 'The Tambi Tea Garden'],
                    'ref'   => 'Kompas Travel (2023) Kebun Teh Tambi',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kebun teh Tambi di Wonosobo sudah ada sejak zaman Belanda, lebih dari 150 tahun lalu. Pucuk daun teh dipetik, diolah, lalu diseduh menjadi minuman hangat.',
                                'The Tambi tea estate in Wonosobo has been there since Dutch colonial times, more than 150 years ago. The young tea leaves are picked, processed and then brewed into a warm drink.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-h01',
                                'label'  => 'Kebun Teh Tambi',
                                'note'   => 'kebun teh Tambi: deretan teh di lereng, pemetik dewasa bercaping dengan keranjang, pohon peneduh, truk kecil di jalan berkelok, bangunan pabrik lama, Sindoro di latar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P08; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P08 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'j' => [
                    'title' => ['Embun Upas', 'Embun Upas (Mountain Frost)'],
                    'ref'   => 'BMKG via Kompas.com (Juli 2026) embun upas; detikEdu',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Pada musim kemarau, suhu di dekat tanah di Dataran Tinggi Dieng bisa turun di bawah 0 °C pada pagi hari, sehingga embun membeku menjadi es tipis. Warga menyebutnya embun upas. Upas berarti racun. Es ini memang bisa merusak tanaman kentang.',
                                'In the dry season, the temperature near the ground on the Dieng Plateau can drop below 0 °C in the early morning, so the dew freezes into thin ice. People call it embun upas. Upas means poison. This ice can indeed damage potato plants.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.wnb-1-j01',
                                'label'  => 'Embun Upas',
                                'note'   => 'pagi berembun beku di Dieng: es tipis putih menutupi sebagian rumput dan daun kentang, petani berjaket memeriksa tanaman, pengunjung memotret, siluet candi kecil di latar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt WNB-P10; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (WNB-P10 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'wnb-2' => [
            'engine'           => 'rumpang',
            'variant'          => 'rumpang_bacaan',
            'indicator'        => 'D1a',
            'distractor_count' => 2,
            'genre'            => null,
            'title'            => ['Lengkapi Bacaan Wonosobo', 'Complete the Wonosobo Reading'],
            'instruction'      => [
                'Baca artikelnya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Ada dua kata yang tidak terpakai.',
                'Read the article. Tap an empty box, then tap the right word. Two of the words are not used.',
            ],
            'description'      => [
                'Sebuah artikel di situs wisata tentang Wonosobo kehilangan beberapa kata. Baca artikelnya sampai habis, lalu lengkapi kata yang hilang.',
                'An article about Wonosobo on a travel website has lost some words. Read the whole article, then fill in the missing words.',
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
            'variants'         => [
                'a' => [
                    'title'       => ['Negeri di Atas Awan', 'The Land Above the Clouds'],
                    'ref'         => 'Wikipedia Dataran Tinggi Dieng; Tempo dan Kompas Travel (Desa Sembungan)',
                    'distractors' => [
                        ['agar', 'so that'],
                        ['panas', 'hot'],
                    ],
                    'special'     => [
                        'agar' => [
                            'wnb-2-a02' => 'INF-HUB',
                            'wnb-2-a03' => 'INF-HUB',
                        ],
                    ],
                    'items'       => [
                        [
                            'key'    => 'wnb-2-a01',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                "🌐 dieng-wisata.example › Artikel · Ditulis oleh Tim Redaksi · 3 Agustus\n\nSebagian Dataran Tinggi Dieng berada di Kabupaten Wonosobo. Dataran tinggi ini terletak lebih dari 2.000 meter di atas permukaan laut. Dieng sering tertutup kabut ___ dijuluki negeri di atas awan.",
                                "🌐 dieng-wisata.example › Articles · Written by the Editorial Team · 3 August\n\nPart of the Dieng Plateau is in Wonosobo Regency. The plateau lies more than 2,000 metres above sea level. Dieng is often covered in mist, ___ it is called the land above the clouds.",
                            ],
                            'answer' => ['sehingga', 'so'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'trap'   => [
                                'agar' => 'INF-HUB',
                            ],
                            'note'   => 'Akibat, bukan tujuan',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-a02',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                "Pada musim kemarau, embun pagi bisa membeku menjadi es ___ suhu di dekat tanah turun di bawah 0 °C. Wisatawan yang datang pada musim itu sebaiknya membawa pakaian hangat.\n\nDieng juga cocok untuk carica, yaitu pepaya gunung.",
                                "In the dry season, the morning dew can freeze into ice ___ the temperature near the ground drops below 0 °C. Visitors who come in that season should bring warm clothes.\n\nDieng is also a good place for carica, a mountain papaya.",
                            ],
                            'answer' => ['karena', 'because'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Sebab',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-a03',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Carica tumbuh baik di ketinggian 1.400–2.400 meter, ___ sulit tumbuh di dataran rendah yang panas.',
                                'Carica grows best at 1,400–2,400 metres, ___ it struggles to grow in hot lowlands.',
                            ],
                            'answer' => ['tetapi', 'but'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Pertentangan',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-a04',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Desa Sembungan berada di ketinggian sekitar 2.300 meter, jadi carica ___ tumbuh di desa itu.',
                                'Sembungan Village is about 2,300 metres high, so carica grows ___ in that village.',
                            ],
                            'answer' => ['dapat', 'well'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Membandingkan 2.300 dengan rentang 1.400–2.400',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-a05',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Daerah pantai termasuk dataran rendah yang panas, jadi carica ___ tumbuh di sana.',
                                'Coastal areas are hot lowlands, so carica grows ___ there.',
                            ],
                            'answer' => ['sulit', 'poorly'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Menerapkan keterangan dataran rendah',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-a06',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Dari semua keterangan itu, kita tahu bahwa carica membutuhkan udara yang ___.',
                                'From all this information, we know that carica needs ___ air.',
                            ],
                            'answer' => ['sejuk', 'cool'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C4',
                            'trap'   => [
                                'panas' => 'INF-LIT',
                            ],
                            'note'   => 'Simpulan dari seluruh teks',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title'       => ['Embun Upas', 'Embun Upas in Dieng'],
                    'ref'         => 'BMKG melalui Kompas.com (Juli 2026) dan detikEdu (embun upas). Situs dan penulis fiktif',
                    'distractors' => [
                        ['siang', 'afternoon'],
                        ['berawan', 'cloudy'],
                    ],
                    'items'       => [
                        [
                            'key'    => 'wnb-2-b01',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                "🌐 jelajah-dieng.example › Cerita Pemandu · Ditulis oleh Kak Laras, pemandu wisata · 20 Juli\n\nPada musim kemarau, langit Dataran Tinggi Dieng sering sangat cerah. Pada malam hari, panas dari permukaan bumi cepat lepas ke angkasa ___ tidak ada awan yang menahannya.",
                                "🌐 jelajah-dieng.example › Guide Stories · Written by Kak Laras, tour guide · 20 July\n\nIn the dry season, the sky over the Dieng Plateau is often very clear. At night, heat from the earth's surface quickly escapes into space ___ there are no clouds to hold it in.",
                            ],
                            'answer' => ['karena', 'because'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Sebab',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-b02',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Udara dingin ___ turun dan terkumpul di cekungan dataran tinggi.',
                                'Cold air ___ sinks and collects in the hollows of the plateau.',
                            ],
                            'answer' => ['lalu', 'then'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Urutan',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-b03',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Suhu di dekat tanah bisa turun di bawah 0 °C, ___ embun pagi membeku menjadi es tipis. Warga Dieng menyebut es tipis itu embun upas.',
                                'The temperature near the ground can drop below 0 °C, ___ the morning dew freezes into thin ice. People in Dieng call this thin ice embun upas.',
                            ],
                            'answer' => ['sehingga', 'so'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Akibat. Mengganti rumpang "membeku", satu-satunya kata kerja di bank kata, yang bisa diisi tanpa membaca',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-b04',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Bagi wisatawan, embun upas tampak indah, ___ bagi petani es itu bisa merusak daun kentang.',
                                'To visitors, embun upas looks beautiful, ___ for farmers the ice can damage potato leaves.',
                            ],
                            'answer' => ['tetapi', 'but'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C3',
                            'note'   => 'Pertentangan',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-b05',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Wisatawan yang ingin melihat embun upas harus datang pada ___ hari, karena es itu cepat mencair saat matahari mulai terik.',
                                'Visitors who want to see embun upas must come in the ___, because the ice melts quickly once the sun gets strong.',
                            ],
                            'answer' => ['pagi', 'morning'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'trap'   => [
                                'siang' => 'INF-LIT',
                            ],
                            'note'   => 'Dari "cepat mencair saat matahari mulai terik"',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-2-b06',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Dari semua keterangan itu, embun upas paling mudah terbentuk setelah malam yang ___.',
                                'From all this information, embun upas forms most easily after a ___ night.',
                            ],
                            'answer' => ['cerah', 'clear'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C4',
                            'trap'   => [
                                'berawan' => 'INF-HUB',
                            ],
                            'note'   => 'Simpulan sebab–akibat dari paragraf pertama',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'wnb-3' => [
            'engine'      => 'boleh',
            'variant'     => 'benar_salah_bacaan',
            'indicator'   => 'D1a',
            'title'       => ['Benar atau Salah? Wonosobo', 'True or False? Wonosobo'],
            'instruction' => [
                'Baca kedua kabar dan ceritanya, lalu pilih BENAR atau SALAH untuk setiap pernyataan.',
                'Read both messages and the story, then choose TRUE or FALSE for each statement.',
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
            'variants'    => [
                'a' => [
                    'passage' => [
                        'title'  => ['Dua Kabar tentang Telaga Warna', 'Two Messages about Telaga Warna'],
                        'body'   => [
                            ">> 💬 Grup Keluarga Besar Raka · ↪ Diteruskan berkali-kali\n\"Minum air Telaga Warna bikin awet muda! Sebarkan ke semua grup!\"\n~~ (tanpa nama pembuat dan tanggal)\n\n>> 📢 @pengelola.telagawarna ✓ akun resmi · 2 Agustus\n\"Air Telaga Warna mengandung belerang, sehingga warnanya bisa tampak hijau, biru, atau kekuningan. Air telaga tidak untuk diminum. Bawalah air minum sendiri saat berkunjung.\"\n\nRaka ingin tahu kabar mana yang dapat dipercaya. Ia memeriksa siapa pembuat setiap kabar, lalu bertanya kepada gurunya. Gurunya menjelaskan bahwa air yang mengandung belerang tidak aman untuk diminum. Dito, sepupu Raka, juga hanya membaca pesan berantai itu. Ia berkata, \"Kayaknya benar, deh.\"",
                            ">> 💬 Raka's Big Family Group · ↪ Forwarded many times\n\"Drinking water from Telaga Warna keeps you young! Share this with every group!\"\n~~ (no maker's name and no date)\n\n>> 📢 @pengelola.telagawarna ✓ official account · 2 August\n\"The water of Telaga Warna contains sulphur, so its colour can look green, blue, or yellowish. The lake water is not for drinking. Bring your own drinking water when you visit.\"\n\nRaka wants to know which message can be trusted. Raka checks who made each message, then asks the class teacher. The teacher explains that water containing sulphur is not safe to drink. Dito, Raka's cousin, has also only read the chain message. Dito says, \"I reckon it's true.\"",
                        ],
                        'genre'  => 'dua_sumber',
                        'layout' => 'chat',
                        'ref'    => 'Telaga Warna dan belerang: Indonesia Travel, Suara.com (dicek di 01); kawah dan gas di Dieng: laman PVMBG/MAGMA Indonesia; panduan cek fakta Siberkreasi dan Mafindo. Akun pengelola fiktif; lembaga pemantau gunung api ditulis tanpa nama dan logo',
                    ],
                    'items'   => [
                        [
                            'key'    => 'wnb-3-a01',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Kedua kabar sama-sama membicarakan air Telaga Warna, tetapi isinya bertentangan.',
                                'Both messages talk about the water of Telaga Warna, but they say opposite things.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-a02',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Pesan berantai itu pasti benar karena sudah diteruskan berkali-kali.',
                                'The chain message must be true because it has been forwarded many times.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C4',
                            'skill'  => 'menilai_kredibilitas',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-a03',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Hanya satu dari dua kabar itu yang menyarankan orang meminum air telaga.',
                                'Only one of the two messages suggests that people drink the lake water.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-a04',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Unggahan pengelola lebih dapat dipercaya daripada pesan berantai karena pembuat dan tanggalnya jelas.',
                                'The managers\' post is more trustworthy than the chain message because its maker and date are clear.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_sumber',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-a05',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Bertanya kepada Dito sama membantunya dengan bertanya kepada guru.',
                                'Asking Dito is just as helpful as asking the teacher.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C4',
                            'skill'  => 'menilai_kredibilitas',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-a06',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Berdasarkan bukti dalam bacaan, pengunjung yang haus boleh meminum air Telaga Warna asalkan airnya tampak jernih.',
                                'Based on the evidence in the text, thirsty visitors may drink the water of Telaga Warna as long as it looks clear.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-GEN',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_kredibilitas',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'passage' => [
                        'title'  => ['Kabar tentang Kawah', 'News about the Craters'],
                        'body'   => [
                            "Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api. Karena itu, di sana ada beberapa kawah. Di sekitar kawah dapat keluar gas yang berbahaya bagi kesehatan. Petugas memasang rambu dan pagar pembatas agar pengunjung tetap berada pada jarak aman.\n\n>> 💬 Grup Wisata Keluarga Dinda · ↪ Diteruskan berkali-kali\n\"Kawah aman kok, dekati saja biar fotonya bagus!\"\n~~ (tanpa nama pembuat)\n\n>> 📢 Akun resmi badan pemantau gunung api ✓ · 2 Agustus\n\"Pengunjung kawah di Dieng harus mengikuti rambu dan menjaga jarak aman dari kawah.\"\n\nKeluarga Dinda akhirnya tetap berwisata, tetapi mereka berdiri di belakang pagar pembatas saat berfoto.",
                            "The Dieng Plateau was formed by volcanic activity. That is why there are several craters there. Gas that is harmful to health can come out around the craters. Officers put up signs and barrier fences so that visitors stay at a safe distance.\n\n>> 💬 Dinda's Family Travel Group · ↪ Forwarded many times\n\"The craters are safe, just go up close so your photos look good!\"\n~~ (no maker's name)\n\n>> 📢 Official account of the volcano monitoring agency ✓ · 2 August\n\"Visitors to the craters in Dieng must follow the signs and keep a safe distance from the craters.\"\n\nIn the end, Dinda's family still went on their trip, but they stood behind the barrier fence when taking photos.",
                        ],
                        'genre'  => 'dua_sumber',
                        'layout' => 'chat',
                        'ref'    => 'Telaga Warna dan belerang: Indonesia Travel, Suara.com (dicek di 01); kawah dan gas di Dieng: laman PVMBG/MAGMA Indonesia; panduan cek fakta Siberkreasi dan Mafindo. Akun pengelola fiktif; lembaga pemantau gunung api ditulis tanpa nama dan logo',
                    ],
                    'items'   => [
                        [
                            'key'    => 'wnb-3-b01',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Pesan berantai dan pengumuman resmi memberi saran yang berbeda tentang mendekati kawah.',
                                'The chain message and the official announcement give different advice about going near the craters.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-b02',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Menurut bacaan, petugas memasang pagar pembatas karena kawah di Dieng terlalu panas untuk didekati.',
                                'According to the text, officers put up barrier fences because the craters in Dieng are too hot to go near.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-HUB',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'skill'  => 'menghubungkan_informasi',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-b03',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Pesan berantai itu dapat dipercaya karena sudah banyak beredar.',
                                'The chain message can be trusted because it has spread widely.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C4',
                            'skill'  => 'menilai_kredibilitas',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-b04',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Pengumuman resmi dapat dipercaya karena lembaga pembuatnya jelas.',
                                'The official announcement can be trusted because the organisation that made it is clear.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C4',
                            'skill'  => 'mengenali_penanda_sumber',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-b05',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Rambu dan pagar dari petugas lebih pantas diikuti daripada pesan berantai, karena petugas mengetahui bahaya gas di kawah.',
                                'The officers\' signs and fences are better to follow than the chain message, because the officers know about the danger of the gas at the craters.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C4',
                            'skill'  => 'membandingkan_sumber',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'wnb-3-b06',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Berdasarkan bukti dalam bacaan, keluarga Dinda harus membatalkan wisata karena pengumuman resmi melarang pengunjung datang ke kawah.',
                                'Based on the evidence in the text, Dinda\'s family had to cancel their trip because the official announcement banned visitors from coming to the craters.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-GEN',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C5',
                            'skill'  => 'menilai_kredibilitas',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
            ],
            'anchor'      => [
                'passage' => [
                    'title'  => ['', ''],
                    'body'   => [
                        ">> @carica.mantap · Bersponsor\n\"Manisan carica TERENAK se-Dieng! Dijamin bikin pintar dan tidak pernah sakit! Pesan sekarang, diskon 50% hari ini saja!\"\n~~ ❤️ 2.300 suka",
                        ">> @carica.mantap · Sponsored\n\"The TASTIEST sweet carica in all of Dieng! Guaranteed to make you smart and never get sick! Order now, 50% off today only!\"\n~~ ❤️ 2,300 likes",
                    ],
                    'genre'  => 'unggahan_bersponsor',
                    'layout' => 'post',
                    'ref'    => 'Unggahan bersponsor rekaan (kartu jangkar); tanpa klaim fakta budaya',
                ],
                'items'   => [
                    [
                        'key'    => 'wnb-3-x01',
                        'type'   => 'verdict_card',
                        'prompt' => [
                            'Unggahan ini adalah iklan, terlihat dari tanda "Bersponsor".',
                            'This post is an advert, as shown by the label "Sponsored".',
                        ],
                        'answer' => 'benar',
                        'code'   => 'INF-SUM',
                        'dim'    => 'D1',
                        'sub'    => 'D1b',
                        'akm'    => 'L3',
                        'pirls'  => 'evaluate',
                        'bloom'  => 'C2',
                        'skill'  => 'mengenali_iklan',
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-3-x02',
                        'type'   => 'verdict_card',
                        'prompt' => [
                            'Karena disukai 2.300 orang, isi unggahan itu pasti benar.',
                            'Because 2,300 people liked it, what the post says must be true.',
                        ],
                        'answer' => 'salah',
                        'code'   => 'INF-SUM',
                        'dim'    => 'D1',
                        'sub'    => 'D1b',
                        'akm'    => 'L3',
                        'pirls'  => 'evaluate',
                        'bloom'  => 'C4',
                        'skill'  => 'menilai_kredibilitas',
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
            ],
        ],
        'wnb-4' => [
            'engine'      => 'cari',
            'variant'     => 'cari_objek',
            'indicator'   => 'D3',
            'title'       => ['Cari Objek Wonosobo', 'Find the Objects: Wonosobo'],
            'instruction' => [
                'Petunjuk Mbah Kedu berupa teka-teki. Pikirkan ciri-ciri bendanya, lalu ketuk yang tepat.',
                'Mbah Kedu\'s clues are riddles. Think about each object\'s features, then tap the right one.',
            ],
            'description' => [
                'Kabut Dieng menyembunyikan benda-benda khas Wonosobo di antara benda yang mirip. Pecahkan teka-teki Mbah Kedu dan temukan keempatnya. Lalu, periksa sebuah unggahan tentang budaya Wonosobo.',
                'The Dieng mist hides Wonosobo\'s special objects among look-alikes. Solve Mbah Kedu\'s riddles and find all four. Then check a post about Wonosobo\'s culture.',
            ],
            'scene'       => [
                'key'   => 'challenge.wnb-4.scene',
                'label' => 'Pasar Oleh-oleh Dieng',
                'note'  => 'pasar oleh-oleh Dieng berkabut tipis: rak bambu beratap (permukaan ± 43 %), meja kayu (± 58 %), dan bangku bambu di depan (± 77 %), kebun teh & candi di latar; benda ditata ulang 3 November 2026 supaya berjarak paling sedikit 24 dp',
            ],
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
            'round1'      => [
                'title' => ['Pasar Oleh-oleh Dieng', 'Dieng Souvenir Market'],
                'ref'   => 'Indonesia Kaya; BRIN Audiovisual (Bundengan); Kompas Travel (2023) Kebun Teh Tambi; Wikipedia (Pepaya gunung)',
                'items' => [
                    [
                        'key'          => 'wnb-4-x01',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan alat musik berbentuk tudung lengkung dari anyaman bambu. Dulu tudung itu melindungi penggembala bebek dari panas dan hujan, lalu diberi senar untuk dipetik.',
                            'Find the musical instrument shaped like a curved hood of woven bamboo. The hood once protected duck herders from the sun and rain, and then strings were added to pluck.',
                        ],
                        'x'            => 25,
                        'y'            => 28.8,
                        'w'            => 8,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'bundengan',
                        'media'        => [
                            'key'   => 'challenge.item.wnb-4-a01',
                            'label' => 'Bundengan',
                            'note'  => 'bundengan (tudung bambu lengkung bersenar)',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'wnb-4-x02',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan buah pegunungan yang mirip pepaya, tetapi ukurannya kecil. Buah ini tumbuh baik di udara sejuk dan sering dibuat manisan.',
                            'Find the mountain fruit that looks like a papaya but is small. It grows well in cool air and is often made into sweets.',
                        ],
                        'x'            => 80,
                        'y'            => 43.8,
                        'w'            => 8,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'carica',
                        'media'        => [
                            'key'   => 'challenge.item.wnb-4-a02',
                            'label' => 'Buah carica',
                            'note'  => 'beberapa buah carica kuning kecil',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'wnb-4-x03',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan keranjang bambu kecil yang dicelup-celupkan ke air mendidih. Nama alat ini dipakai untuk menamai mi khas Wonosobo.',
                            'Find the small bamboo basket that is dipped again and again into boiling water. Its name was used to name Wonosobo\'s special noodles.',
                        ],
                        'x'            => 36,
                        'y'            => 43.8,
                        'w'            => 8,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'ongklok',
                        'media'        => [
                            'key'   => 'challenge.item.wnb-4-a03',
                            'label' => 'Keranjang ongklok',
                            'note'  => 'keranjang ongklok bambu kecil bergagang',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'wnb-4-x04',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan daun yang diseduh menjadi minuman hangat. Daun ini dipetik di perkebunan Tambi yang sudah ada sejak zaman Belanda.',
                            'Find the leaves that are brewed into a warm drink. They are picked at the Tambi estate, which has been there since Dutch colonial times. Diterjemahkan ulang: bank lama since 1865; 08 kini "sejak zaman Belanda" (cek fakta 27 Oktober 2026, [25](25_CEK_FAKTA_BUDAYA.md)).',
                        ],
                        'x'            => 61,
                        'y'            => 63.1,
                        'w'            => 8,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'daun teh',
                        'media'        => [
                            'key'   => 'challenge.item.wnb-4-a04',
                            'label' => 'Daun teh',
                            'note'  => 'daun teh segar (pucuk)',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x05',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 42,
                        'y'      => 63.1,
                        'w'      => 8,
                        'prompt' => ['Pepaya biasa (pengecoh)', 'Ordinary papaya (decoy)'],
                        'wrong'  => [
                            'Ini pepaya biasa yang tumbuh di dataran rendah yang panas. Carica lebih kecil dan tumbuh di pegunungan yang sejuk.',
                            'This is an ordinary papaya that grows in hot lowlands. Carica is smaller and grows in cool mountains.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a05',
                            'label' => 'Pepaya biasa',
                            'note'  => 'pepaya biasa (besar)',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x06',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 4,
                        'y'      => 63.1,
                        'w'      => 8,
                        'prompt' => ['Rigen (pengecoh)', 'Rigen (decoy)'],
                        'wrong'  => [
                            'Ini rigen dari Temanggung, anyaman bambu lebar untuk menjemur. Rigen tidak dipakai untuk merebus mi.',
                            'This is a rigen from Temanggung, a wide woven bamboo tray for drying. It is not used to boil noodles.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a06',
                            'label' => 'Rigen',
                            'note'  => 'rigen (anyaman bambu persegi panjang, kosong)',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x07',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 23,
                        'y'      => 63.1,
                        'w'      => 8,
                        'prompt' => ['Semangkuk mi ongklok (pengecoh)', 'A bowl of ongklok noodles (decoy)'],
                        'wrong'  => [
                            'Ini semangkuk mi ongklok. Petunjuk meminta alat yang namanya dipakai untuk menamai mi itu, bukan minya.',
                            'This is a bowl of ongklok noodles. The clue asks for the tool that gave the noodles their name, not the noodles themselves.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a07',
                            'label' => 'Semangkuk mi ongklok',
                            'note'  => 'semangkuk mi ongklok',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x08',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 47,
                        'y'      => 28.8,
                        'w'      => 8,
                        'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'],
                        'wrong'  => [
                            'Ini angklung dari Jawa Barat. Angklung digoyang, bukan dipetik, dan tidak berasal dari tudung penggembala.',
                            'This is an angklung from West Java. It is shaken, not plucked, and it did not come from a herder\'s hood.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a08',
                            'label' => 'Angklung',
                            'note'  => 'angklung bambu',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x09',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 14,
                        'y'      => 43.8,
                        'w'      => 8,
                        'prompt' => ['Sasando (pengecoh)', 'Sasando (decoy)'],
                        'wrong'  => [
                            'Ini sasando dari Pulau Rote, Nusa Tenggara Timur. Sasando juga dipetik, tetapi tidak berasal dari tudung penggembala bebek.',
                            'This is a sasando from Rote Island, East Nusa Tenggara. It is plucked too, but it did not come from a duck herder\'s hood.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a09',
                            'label' => 'Sasando',
                            'note'  => 'sasando',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x10',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 80,
                        'y'      => 63.1,
                        'w'      => 8,
                        'prompt' => ['Biji kopi (pengecoh)', 'Coffee beans (decoy)'],
                        'wrong'  => [
                            'Ini biji kopi dari lereng Sindoro–Sumbing di Temanggung. Petunjuk meminta daun, bukan biji.',
                            'These are coffee beans from the Sindoro–Sumbing slopes in Temanggung. The clue asks for leaves, not beans.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a10',
                            'label' => 'Biji kopi',
                            'note'  => 'segenggam biji kopi',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x11',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 58,
                        'y'      => 43.8,
                        'w'      => 8,
                        'prompt' => ['Kuluk Topeng Ireng (pengecoh)', 'Topeng Ireng headdress (decoy)'],
                        'wrong'  => [
                            'Ini kuluk penari Topeng Ireng dari Magelang — bukan benda yang dimaksud petunjuk.',
                            'This is the headdress of Topeng Ireng dancers from Magelang — not the object in the clue.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a11',
                            'label' => 'Kuluk Topeng Ireng',
                            'note'  => 'kuluk bulu Topeng Ireng',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'wnb-4-x12',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 69,
                        'y'      => 28.8,
                        'w'      => 8,
                        'prompt' => ['Getuk (pengecoh)', 'Getuk (decoy)'],
                        'wrong'  => [
                            'Ini getuk dari Magelang — bukan benda yang dimaksud petunjuk.',
                            'This is getuk from Magelang — not the object in the clue.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.wnb-4-a12',
                            'label' => 'Getuk',
                            'note'  => 'getuk',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
            ],
            'round2'      => [
                'a' => [
                    'scene' => [
                        'title' => ['Unggahan video bundengan', 'A bundengan video post'],
                        'media' => [
                            'key'   => 'challenge.item.wnb-1-g01',
                            'label' => 'Bundengan',
                        ],
                        'ref'   => 'bundengan dari kowangan, Wonosobo: GNFI (2019, 2025), dicek di 01. Mi ongklok dan keranjang ongklok: Indonesia Kaya; Pemkab Wonosobo (kuliner). Akun, sanggar, warung, dan Rumah Budaya fiktif. Telaah 05 memakai contoh sanggar dan museum yang namanya perlu diganti; di sini keduanya sudah diganti nama fiktif',
                    ],
                    'parts' => [
                        [
                            'key'   => 'E1',
                            'kind'  => 'account',
                            'label' => ['Nama akun pengunggah', 'Uploader\'s account name'],
                            'text'  => ['@jelajah.awan', '@jelajah.awan'],
                            'x'     => 3,
                            'y'     => 3,
                            'w'     => 38,
                            'h'     => 8,
                        ],
                        [
                            'key'   => 'E2',
                            'kind'  => 'credit',
                            'label' => ['Tulisan kecil di pojok video', 'Small text in the corner of the video'],
                            'text'  => ['Video: Sanggar Sekar Kowangan', 'Video: Sanggar Sekar Kowangan'],
                            'x'     => 5,
                            'y'     => 42,
                            'w'     => 26,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'E3',
                            'kind'  => 'caption',
                            'label' => ['Keterangan unggahan', 'Caption'],
                            'text'  => [
                                'Angklung khas Jawa Barat, keren banget! 🎶 Video bikinan aku sendiri, lho.',
                                'Angklung from West Java, so cool! 🎶 I made this video myself, you know.',
                            ],
                            'x'     => 3,
                            'y'     => 55,
                            'w'     => 38,
                            'h'     => 12,
                        ],
                        [
                            'key'   => 'E4',
                            'kind'  => 'board',
                            'label' => ['Papan informasi Rumah Budaya', 'Culture House information board'],
                            'text'  => [
                                'Bundengan adalah alat musik dari Wonosobo. Bentuknya berasal dari kowangan, tudung bambu para penggembala bebek.',
                                'Bundengan is a musical instrument from Wonosobo. Its shape comes from the kowangan, the bamboo hood worn by duck herders.',
                            ],
                            'x'     => 46,
                            'y'     => 3,
                            'w'     => 51,
                            'h'     => 22,
                        ],
                        [
                            'key'   => 'E5',
                            'kind'  => 'meta',
                            'label' => ['Jumlah suka', 'Number of likes'],
                            'text'  => ['8.000 suka', '8,000 likes'],
                            'x'     => 3,
                            'y'     => 73,
                            'w'     => 14,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'E6',
                            'kind'  => 'comment',
                            'label' => ['Komentar 1', 'Comment 1'],
                            'text'  => [
                                'Wulan: Itu bundengan dari Wonosobo, Kak. Ada di papan Rumah Budaya 🙏',
                                'Wulan: That\'s a bundengan from Wonosobo. It\'s on the Culture House board 🙏',
                            ],
                            'x'     => 46,
                            'y'     => 30,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'E7',
                            'kind'  => 'comment',
                            'label' => ['Komentar 2', 'Comment 2'],
                            'text'  => ['Galih: SALAH! Gitu aja nggak tahu, malu-maluin! 😡', 'Galih: WRONG! You don\'t even know that, how embarrassing! 😡'],
                            'x'     => 46,
                            'y'     => 44,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'E8',
                            'kind'  => 'comment',
                            'label' => ['Komentar 3', 'Comment 3'],
                            'text'  => ['Putra: Wkwk sama aja lah, sama-sama dari bambu.', 'Putra: LOL, same thing, they\'re both made of bamboo.'],
                            'x'     => 46,
                            'y'     => 58,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'E9',
                            'kind'  => 'comment',
                            'label' => ['Komentar 4', 'Comment 4'],
                            'text'  => ['Intan: Aku juga kira angklung 😅', 'Intan: I thought it was an angklung too 😅'],
                            'x'     => 46,
                            'y'     => 72,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'E10',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Bagikan', 'Share'],
                            'x'     => 3,
                            'y'     => 87,
                            'w'     => 13,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'E11',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Unduh', 'Download'],
                            'x'     => 19,
                            'y'     => 87,
                            'w'     => 12,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'E12',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Kirim pesan ke sanggar', 'Send a message to the studio'],
                            'x'     => 34,
                            'y'     => 87,
                            'w'     => 25,
                            'h'     => 9,
                        ],
                    ],
                    'items' => [
                        [
                            'key'       => 'wnb-4-a01',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Pengunggah menulis bahwa ia membuat video ini sendiri. Ketuk tanda yang menunjukkan siapa pembuat video yang sebenarnya.',
                                'The uploader wrote that they made this video themselves. Tap the sign that shows who really made the video.',
                            ],
                            'answer'    => 'E2',
                            'clue_type' => 'ATR',
                            'main'      => [
                                'E1' => 'BUD-ATR',
                                'E3' => 'BUD-ATR',
                                'E4' => 'BUD-ATR',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'wnb-4-a02',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Unggahan ini disukai 8.000 orang, tapi ada yang keliru. Bandingkan dengan papan informasi, lalu ketuk bagian yang paling perlu dibetulkan supaya orang lain tidak ikut salah paham.',
                                '8,000 people liked this post, but something in it is not accurate. Compare it with the information board, then tap the part that most needs correcting, so other people don\'t get the wrong idea too.',
                            ],
                            'answer'    => 'E3',
                            'clue_type' => 'REP',
                            'main'      => [
                                'E5' => 'BUD-REP',
                                'E8' => 'BUD-REP',
                                'E9' => 'BUD-REP',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C4',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'wnb-4-a03',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Kamu ingin memakai video ini untuk tugas sekolah yang dikumpulkan besok. Ketuk langkah pertama yang tepat.',
                                'You want to use this video for a school assignment that is due tomorrow. Tap the right first step.',
                            ],
                            'answer'    => 'E12',
                            'clue_type' => 'IZN',
                            'main'      => [
                                'E11' => 'BUD-IZN',
                                'E10' => 'BUD-IZN',
                                'E1'  => 'BUD-IZN',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C3',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'wnb-4-a04',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Ketuk komentar yang membetulkan keterangan dengan benar, sopan, dan menyebut sumbernya.',
                                'Tap the comment that corrects the caption accurately and politely, and names its source.',
                            ],
                            'answer'    => 'E6',
                            'clue_type' => 'ETK',
                            'main'      => [
                                'E7' => 'BUD-ETK',
                                'E8' => 'BUD-REP',
                                'E9' => 'BUD-REP',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C4',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'scene' => [
                        'title' => ['Unggahan video mi ongklok', 'An ongklok noodle video post'],
                        'media' => [
                            'key'   => 'challenge.item.wnb-1-07',
                            'label' => 'Mi Ongklok',
                        ],
                        'ref'   => 'bundengan dari kowangan, Wonosobo: GNFI (2019, 2025), dicek di 01. Mi ongklok dan keranjang ongklok: Indonesia Kaya; Pemkab Wonosobo (kuliner). Akun, sanggar, warung, dan Rumah Budaya fiktif. Telaah 05 memakai contoh sanggar dan museum yang namanya perlu diganti; di sini keduanya sudah diganti nama fiktif',
                    ],
                    'parts' => [
                        [
                            'key'   => 'F1',
                            'kind'  => 'account',
                            'label' => ['Nama akun pengunggah', 'Uploader\'s account name'],
                            'text'  => ['@makan.terus', '@makan.terus'],
                            'x'     => 3,
                            'y'     => 3,
                            'w'     => 38,
                            'h'     => 8,
                        ],
                        [
                            'key'   => 'F2',
                            'kind'  => 'credit',
                            'label' => ['Tulisan kecil di pojok video', 'Small text in the corner of the video'],
                            'text'  => ['Video: Warung Kabut Pagi', 'Video: Warung Kabut Pagi'],
                            'x'     => 5,
                            'y'     => 42,
                            'w'     => 26,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'F3',
                            'kind'  => 'caption',
                            'label' => ['Keterangan unggahan', 'Caption'],
                            'text'  => [
                                'Mi ongklok, kuliner khas Magelang! 😋 Video bikinan aku sendiri, lho.',
                                'Ongklok noodles, a special dish from Magelang! 😋 I made this video myself, you know.',
                            ],
                            'x'     => 3,
                            'y'     => 55,
                            'w'     => 38,
                            'h'     => 12,
                        ],
                        [
                            'key'   => 'F4',
                            'kind'  => 'board',
                            'label' => ['Papan informasi Rumah Budaya', 'Culture House information board'],
                            'text'  => [
                                'Mi ongklok adalah makanan khas Wonosobo. Namanya berasal dari ongklok, keranjang bambu kecil untuk merebus mi.',
                                'Ongklok noodles (mi ongklok) are a special food of Wonosobo. The name comes from the ongklok, a small bamboo basket used to boil the noodles.',
                            ],
                            'x'     => 46,
                            'y'     => 3,
                            'w'     => 51,
                            'h'     => 22,
                        ],
                        [
                            'key'   => 'F5',
                            'kind'  => 'meta',
                            'label' => ['Jumlah suka', 'Number of likes'],
                            'text'  => ['7.500 suka', '7,500 likes'],
                            'x'     => 3,
                            'y'     => 73,
                            'w'     => 14,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'F6',
                            'kind'  => 'comment',
                            'label' => ['Komentar 1', 'Comment 1'],
                            'text'  => [
                                'Dewi: Mi ongklok itu dari Wonosobo, Kak. Ada di papan Rumah Budaya 🙏',
                                'Dewi: Ongklok noodles are from Wonosobo. It\'s on the Culture House board 🙏',
                            ],
                            'x'     => 46,
                            'y'     => 30,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'F7',
                            'kind'  => 'comment',
                            'label' => ['Komentar 2', 'Comment 2'],
                            'text'  => ['Aldi: NGACO! Makanya belajar dulu! 😡', 'Aldi: NONSENSE! Go and learn first! 😡'],
                            'x'     => 46,
                            'y'     => 44,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'F8',
                            'kind'  => 'comment',
                            'label' => ['Komentar 3', 'Comment 3'],
                            'text'  => ['Yoga: Wkwk sama aja lah, sama-sama mi.', 'Yoga: LOL, same thing, they\'re all just noodles.'],
                            'x'     => 46,
                            'y'     => 58,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'F9',
                            'kind'  => 'comment',
                            'label' => ['Komentar 4', 'Comment 4'],
                            'text'  => ['Nisa: Aku juga kira dari Magelang 😅', 'Nisa: I thought they were from Magelang too 😅'],
                            'x'     => 46,
                            'y'     => 72,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'F10',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Bagikan', 'Share'],
                            'x'     => 3,
                            'y'     => 87,
                            'w'     => 13,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'F11',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Unduh', 'Download'],
                            'x'     => 19,
                            'y'     => 87,
                            'w'     => 12,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'F12',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Kirim pesan ke warung', 'Send a message to the food stall'],
                            'x'     => 34,
                            'y'     => 87,
                            'w'     => 25,
                            'h'     => 9,
                        ],
                    ],
                    'items' => [
                        [
                            'key'       => 'wnb-4-b01',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Pengunggah menulis bahwa ia membuat video ini sendiri. Ketuk tanda yang menunjukkan siapa pembuat video yang sebenarnya.',
                                'The uploader wrote that they made this video themselves. Tap the sign that shows who really made the video.',
                            ],
                            'answer'    => 'F2',
                            'clue_type' => 'ATR',
                            'main'      => [
                                'F1' => 'BUD-ATR',
                                'F3' => 'BUD-ATR',
                                'F4' => 'BUD-ATR',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'wnb-4-b02',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Unggahan ini disukai 7.500 orang, tapi ada yang keliru. Bandingkan dengan papan informasi, lalu ketuk bagian yang paling perlu dibetulkan supaya orang lain tidak ikut salah paham.',
                                '7,500 people liked this post, but something in it is not accurate. Compare it with the information board, then tap the part that most needs correcting, so other people don\'t get the wrong idea too.',
                            ],
                            'answer'    => 'F3',
                            'clue_type' => 'REP',
                            'main'      => [
                                'F5' => 'BUD-REP',
                                'F8' => 'BUD-REP',
                                'F9' => 'BUD-REP',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C4',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'wnb-4-b03',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Kamu ingin memakai video ini untuk tugas sekolah yang dikumpulkan besok. Ketuk langkah pertama yang tepat.',
                                'You want to use this video for a school assignment that is due tomorrow. Tap the right first step.',
                            ],
                            'answer'    => 'F12',
                            'clue_type' => 'IZN',
                            'main'      => [
                                'F11' => 'BUD-IZN',
                                'F10' => 'BUD-IZN',
                                'F1'  => 'BUD-IZN',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C3',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'wnb-4-b04',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Ketuk komentar yang membetulkan keterangan dengan benar, sopan, dan menyebut sumbernya.',
                                'Tap the comment that corrects the caption accurately and politely, and names its source.',
                            ],
                            'answer'    => 'F6',
                            'clue_type' => 'ETK',
                            'main'      => [
                                'F7' => 'BUD-ETK',
                                'F8' => 'BUD-REP',
                                'F9' => 'BUD-REP',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L3',
                            'bloom'     => 'C4',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'wnb-5' => [
            'engine'      => 'skenario',
            'variant'     => 'skenario_digital',
            'indicator'   => 'D2',
            'title'       => ['Skenario Digital Wonosobo', 'Digital Scenarios: Wonosobo'],
            'instruction' => [
                'Bacalah ceritanya. Pilih apa yang akan kamu lakukan, lalu pilih alasanmu.',
                'Read the story. Choose what you would do, then choose your reason.',
            ],
            'description' => [
                'Serpihan terakhir tersembunyi di layar-layar di seluruh Dieng. Bayangkan setiap cerita terjadi padamu.',
                'The last shard is hidden in screens all over Dieng. Imagine each story is happening to you.',
            ],
            'scenarios'   => [
                'a' => [
                    [
                        'key'         => 'wnb-5-a01',
                        'topic'       => 'PRI',
                        'steps'       => 2,
                        'situation'   => [
                            'Akun yang tidak kamu kenal mengirim pesan: "Selamat! Kamu menang wisata gratis ke Telaga Warna untuk sekeluarga!" Beberapa menit kemudian, ia menulis lagi: "Kirim foto kartu pelajar dan nomor HP orang tuamu sekarang, kuotanya tinggal 3!"',
                            'An account you don\'t know sends a message: "Congratulations! You\'ve won a free family trip to Telaga Warna!" A few minutes later, it writes again: "Send a photo of your student card and your parent\'s phone number now, only 3 places left!"',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Langsung kirim fotonya sebelum kuotanya habis', 'Send the photo straight away before the places run out'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Hadiah dari orang tak dikenal bisa jadi tipuan untuk mengambil data. Desakan "tinggal 3" adalah tanda bahaya.',
                                    'A prize from a stranger can be a trick to get your data. Pressure like "only 3 left" is a warning sign.',
                                ],
                            ],
                            [
                                'text'     => ['Balas dulu untuk menanyakan dia itu siapa', 'Reply first to ask who they are'],
                                'score'    => 0,
                                'code'     => 'DIG-ASG',
                                'feedback' => [
                                    'Membalas membuat orang asing tahu akunmu aktif, dan ia bisa terus membujukmu.',
                                    'Replying lets the stranger know your account is active, and they can keep trying to talk you into it.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan ke orang tua, lalu blokir akunnya', 'Show your parents, then block the account'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Datamu aman, dan akun penipu bisa dihentikan.',
                                    'Good choice. Your data is safe, and the scam account can be stopped.',
                                ],
                            ],
                            [
                                'text'     => ['Diamkan saja tanpa cerita ke siapa pun', 'Just ignore it without telling anyone'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Datamu aman. Cerita ke orang tua akan membantu melindungi orang lain juga.',
                                    'Your data is safe. Telling your parents would help protect other people too.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Hadiah dari orang asing bisa jadi tipuan', 'A prize from a stranger can be a trick'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Data orang tua bukan milikku untuk dibagikan', 'My parents\' data isn\'t mine to share'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Kata guru tidak boleh membalas orang asing', 'My teacher says not to reply to strangers'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-temanku juga ikut mengirim datanya', 'My friends are sending their data too'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku memang tidak terlalu suka jalan-jalan', 'I don\'t really like going on trips anyway'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Hadiah dari akun yang tidak dikenal, apalagi yang mendesak, bisa jadi tipuan untuk mengambil data. Dengan menunjukkan ke orang tua lalu memblokir akunnya, data keluargamu aman.',
                            'A prize from an unknown account, especially one that rushes you, can be a trick to get your data. By showing your parents and blocking the account, your family\'s data stays safe.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C4',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'wnb-5-a02',
                        'topic'       => 'SAN',
                        'steps'       => 2,
                        'situation'   => [
                            'Kamu mendapat pesan dari akun bernama "Tim Resmi Game Bintang ✓": "Akunmu akan dihapus hari ini! Kirim kode yang baru masuk ke HP ibumu untuk menyelamatkannya." Tak lama kemudian, HP ibumu menerima SMS berisi kode enam angka.',
                            'You get a message from an account called "Official Game Bintang Team ✓": "Your account will be deleted today! Send the code that just arrived on your mum\'s phone to save it." Soon after, your mum\'s phone gets a text message with a six-digit code.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Cepat kirim kodenya supaya akun tidak dihapus', 'Quickly send the code so the account isn\'t deleted'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Kode seperti itu adalah kunci akun. Tim yang asli tidak pernah memintanya, walaupun akunnya bertanda centang.',
                                    'A code like that is a key to an account. The real team never asks for it, even if the account has a tick.',
                                ],
                            ],
                            [
                                'text'     => ['Balas dulu, tanya apakah mereka benar tim resmi', 'Reply first to ask whether they are really the official team'],
                                'score'    => 0,
                                'code'     => 'DIG-ASG',
                                'feedback' => [
                                    'Penipu akan selalu menjawab "benar". Membalas membuatmu terus dibujuk.',
                                    'A scammer will always say "yes". Replying means they keep trying to talk you into it.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan pesan dan SMS-nya ke ibu', 'Show the message and the text to your mum'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Kode tetap aman, ibu tahu ada yang mencoba masuk ke akunnya, dan kalian bisa melaporkan akun itu bersama.',
                                    'Good choice. The code stays safe, your mum knows someone is trying to get into her account, and you can report the account together.',
                                ],
                            ],
                            [
                                'text'     => ['Hapus pesan itu dan tidak cerita ke ibu', 'Delete the message and don\'t tell your mum'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kodenya aman. Ibu perlu tahu, karena kode itu masuk ke HP-nya.',
                                    'The code is safe. Your mum needs to know, because the code came to her phone.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Tim resmi tidak pernah meminta kode itu', 'An official team never asks for that code'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kode itu masuk ke HP ibu, bukan milikku', 'The code came to Mum\'s phone, it isn\'t mine'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Aku dilarang membalas pesan orang tak dikenal', 'I\'m not allowed to reply to messages from strangers'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Temanku pernah mengirim kode dan aman saja', 'My friend once sent a code and it was fine'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku sebenarnya jarang main game itu', 'I actually don\'t play that game much'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Kode dari SMS adalah kunci untuk masuk ke akun, dan akun yang tampak resmi pun bisa palsu. Dengan menunjukkan pesan dan SMS-nya ke ibu, kode tetap aman dan kalian bisa melapor bersama.',
                            'A code from a text message is a key for getting into an account, and even an account that looks official can be fake. By showing the message and the text to your mum, the code stays safe and you can report it together.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C4',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'wnb-5-a03',
                        'topic'       => 'IZN',
                        'steps'       => 2,
                        'situation'   => [
                            'Bima bercerita lewat pesan pribadi bahwa ia sedih karena kalah lomba bundengan. Sore harinya, teman-teman di grup kelas bertanya, "Bima kenapa sih? Kok diam terus?" Kamu masih menyimpan obrolanmu dengan Bima.',
                            'In a private message, Bima tells you about feeling sad after losing a bundengan competition. That afternoon, friends in the class group ask, "What\'s up with Bima? Why so quiet all the time?" You still have your chat with Bima.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Teruskan tangkapan layar obrolan Bima ke grup', 'Forward a screenshot of Bima\'s chat to the group'],
                                'score'    => 0,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Obrolan pribadi Bima hanya boleh dibagikan kalau ia setuju. Tangkapan layar bisa tersebar ke mana-mana.',
                                    'Bima\'s private chat may only be shared if Bima agrees. Screenshots can spread anywhere.',
                                ],
                            ],
                            [
                                'text'     => [
                                    'Ceritakan isi obrolannya di grup dengan kata-katamu sendiri',
                                    'Tell the group what the chat said in your own words',
                                ],
                                'score'    => 0,
                                'code'     => 'DIG-ETK',
                                'feedback' => [
                                    'Walaupun tanpa tangkapan layar, cerita pribadi Bima tetap tersebar tanpa izinnya.',
                                    'Even without a screenshot, Bima\'s private story still spreads without Bima\'s permission.',
                                ],
                            ],
                            [
                                'text'     => [
                                    'Minta grup bertanya langsung ke Bima, lalu sapa Bima',
                                    'Ask the group to ask Bima directly, then say hello to Bima',
                                ],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Rahasia Bima terjaga, dan kamu tetap peduli padanya.',
                                    'Good choice. Bima\'s secret is kept, and you still show you care.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak menjawab grup dan tidak menyapa Bima', 'Don\'t answer the group and don\'t say hello to Bima'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Rahasia Bima aman. Menyapa Bima secara pribadi akan membuatnya merasa ditemani.',
                                    'Bima\'s secret is safe. Saying hello to Bima privately would make Bima feel less alone.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Bima belum tentu mau ceritanya disebar', 'Bima may not want the story shared'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Tangkapan layar bisa tersebar ke mana-mana', 'Screenshots can spread anywhere'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Ada aturan kelas tidak boleh bergosip di grup', 'There\'s a class rule against gossiping in the group'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-teman di grup sudah sangat penasaran', 'My friends in the group are really curious'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku juga suka mendengar bunyi bundengan', 'I like the sound of the bundengan too'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Cerita Bima di pesan pribadi adalah rahasianya. Dengan meminta teman-teman bertanya langsung ke Bima, rahasia Bima terjaga dan kamu tetap bisa menyapanya.',
                            'What Bima said in a private message is Bima\'s secret. By asking your friends to ask Bima directly, the secret is kept and you can still say hello to Bima.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C5',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'wnb-5-a04',
                        'topic'       => 'ASG',
                        'steps'       => 2,
                        'situation'   => [
                            'Di grup kelas beredar pesan dengan foto jalan rusak: "Jalan ke Telaga Warna putus karena longsor hari ini! Sebarkan!" Temanmu, Fikri, membalas: "Sudah kusebar ke grup keluarga." Lalu kamu melihat foto yang sama di sebuah berita lama dari daerah lain.',
                            'A message with a photo of a damaged road is going round the class group: "The road to Telaga Warna was cut off by a landslide today! Share this!" Your friend Fikri replies: "I\'ve already shared it to my family group." Then you see the same photo in an old news story from another area.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Ikut menyebarkan ke grup lain supaya orang berhati-hati', 'Share it to other groups too, so people are careful'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Fotonya ternyata foto lama dari tempat lain. Menyebarkannya membuat lebih banyak orang salah paham.',
                                    'The photo turns out to be an old one from another place. Sharing it makes more people get the wrong idea.',
                                ],
                            ],
                            [
                                'text'     => ['Balas di grup: "Fikri bodoh, itu hoaks!"', 'Reply in the group: "Fikri, you\'re stupid, that\'s a hoax!"'],
                                'score'    => 0,
                                'code'     => 'DIG-ETK',
                                'feedback' => [
                                    'Kamu benar soal fotonya, tapi kata-kata kasar membuat Fikri malu dan teman lain enggan mendengar.',
                                    'You\'re right about the photo, but rude words embarrass Fikri and make others not want to listen.',
                                ],
                            ],
                            [
                                'text'     => ['Beri tahu Fikri dengan sopan bahwa itu foto lama', 'Politely tell Fikri that it\'s an old photo'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Kabar bohong bisa berhenti, dan Fikri tetap dihargai. Ajak juga mengecek akun resmi pengelola.',
                                    'Good choice. The false news can stop, and Fikri is still respected. You can also suggest checking the managers\' official account.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak menyebarkan dan tidak menanggapi apa pun', 'Don\'t share it and don\'t respond at all'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak ikut menyebarkan, bagus. Memberi tahu dengan sopan bisa menghentikan kabar bohong itu.',
                                    'You didn\'t help spread it, well done. Telling someone politely can stop false news.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Foto lama bisa membuat orang salah paham', 'An old photo can make people get the wrong idea'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kalau ditegur dengan sopan, Fikri tidak malu', 'If I tell Fikri politely, Fikri won\'t feel embarrassed'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Guru bilang tidak boleh menyebarkan berita', 'My teacher said we mustn\'t spread news'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Semua orang di grup sudah percaya kabar itu', 'Everyone in the group already believes it'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku belum pernah pergi ke Telaga Warna', 'I\'ve never been to Telaga Warna'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Foto yang sama ada di berita lama dari daerah lain, jadi kabar itu belum tentu benar. Dengan memberi tahu Fikri dengan sopan, kabar itu bisa berhenti tanpa membuat Fikri malu.',
                            'The same photo is in an old news story from another area, so the message may not be true. By politely telling Fikri, the news can stop without embarrassing Fikri.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C4',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
                'b' => [
                    [
                        'key'         => 'wnb-5-b01',
                        'topic'       => 'PRI',
                        'steps'       => 2,
                        'situation'   => [
                            'Akun "Undian Carica Dieng" mengumumkan: "Ikuti undian, hadiahnya sepeda!" Untuk ikut, peserta harus mengirim foto Kartu Keluarga dan nomor HP orang tua. Temanmu, Sinta, berkata, "Aku sudah ikut, kok. Katanya aman."',
                            'The account "Dieng Carica Prize Draw" announces: "Join our prize draw and win a bicycle!" To join, you must send a photo of your Family Card and your parent\'s phone number. Your friend Sinta says, "I\'ve already joined. They say it\'s safe."',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Kirim foto Kartu Keluarga supaya bisa ikut', 'Send a photo of the Family Card so you can join'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Kartu Keluarga memuat data seluruh keluargamu. Undian sungguhan tidak memintanya.',
                                    'A Family Card holds data about your whole family. A real prize draw does not ask for it.',
                                ],
                            ],
                            [
                                'text'     => [
                                    'Kirim nomor HP orang tua dulu, foto KK menyusul',
                                    'Send your parent\'s phone number first, and the Family Card photo later',
                                ],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Nomor HP orang tua juga data pribadi yang bisa dipakai untuk menipu.',
                                    'Your parent\'s phone number is also personal data that can be used to trick people.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan ke orang tua, dan ingatkan Sinta juga', 'Show your parents, and warn Sinta too'],
                                'score'    => 2,
                                'feedback' => ['Tepat. Datamu aman, dan kamu ikut menolong Sinta.', 'Good choice. Your data is safe, and you help Sinta too.'],
                            ],
                            [
                                'text'     => ['Tidak ikut, dan tidak membahasnya dengan Sinta', 'Don\'t join, and don\'t talk about it with Sinta'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Datamu aman. Sinta mungkin perlu bantuan karena sudah mengirim datanya.',
                                    'Your data is safe. Sinta may need help, because Sinta has already sent the data.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Data keluarga bisa disalahgunakan orang asing', 'Strangers could misuse my family\'s data'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kartu Keluarga memuat data seluruh keluargaku', 'The Family Card has data on my whole family'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Kata guru tidak boleh ikut undian di internet', 'My teacher says not to join online prize draws'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Sinta sudah ikut, jadi pasti aman-aman saja', 'Sinta has joined, so it must be safe'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku sudah punya sepeda di rumah', 'I already have a bicycle at home'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Kartu Keluarga dan nomor HP orang tua adalah data pribadi keluarga, walaupun temanmu bilang aman. Dengan menunjukkan ke orang tua dan mengingatkan Sinta, kamu menjaga datamu dan menolong temanmu.',
                            'A Family Card and your parent\'s phone number are your family\'s personal data, even if your friend says it\'s safe. By showing your parents and warning Sinta, you keep your data safe and help your friend.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C4',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'wnb-5-b02',
                        'topic'       => 'SAN',
                        'steps'       => 2,
                        'situation'   => [
                            'Kamu mendapat pesan dari "Admin Sekolah": "Akun belajarmu akan diblokir. Masuk ulang lewat sekolah-kita.example/masuk dan tulis kata sandimu." Kamu melihat alamat itu berbeda dari alamat situs sekolah yang biasa kamu pakai.',
                            'You get a message from "School Admin": "Your learning account will be blocked. Sign in again at sekolah-kita.example/masuk and type your password." You notice the address is different from the school website you usually use.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Buka tautannya dan tulis kata sandimu di sana', 'Open the link and type your password there'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Alamat yang berbeda adalah tanda bahaya. Situs palsu bisa mencuri kata sandimu.',
                                    'A different address is a warning sign. A fake website can steal your password.',
                                ],
                            ],
                            [
                                'text'     => ['Balas pesannya, tanya apakah benar admin sekolah', 'Reply to ask whether it\'s really the school admin'],
                                'score'    => 0,
                                'code'     => 'DIG-ASG',
                                'feedback' => [
                                    'Pengirim palsu akan menjawab "benar". Lebih aman bertanya langsung ke guru.',
                                    'A fake sender will say "yes". It\'s safer to ask your teacher directly.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan pesan itu ke guru tanpa membukanya', 'Show the message to your teacher without opening it'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Guru bisa memastikan, dan teman-teman lain bisa diperingatkan.',
                                    'Good choice. Your teacher can make sure, and other friends can be warned.',
                                ],
                            ],
                            [
                                'text'     => ['Hapus pesannya dan tidak cerita ke siapa pun', 'Delete the message and don\'t tell anyone'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kata sandimu aman. Memberi tahu guru akan melindungi teman-temanmu juga.',
                                    'Your password is safe. Telling your teacher would protect your friends too.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Alamatnya berbeda, bisa jadi situs palsu', 'The address is different, so it could be a fake website'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Data teman sekelas bisa ikut terlihat', 'My classmates\' data could be seen too'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Aku dilarang membuka tautan apa pun di HP', 'I\'m not allowed to open any links on a phone'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-teman lain sepertinya sudah masuk ulang', 'Other friends seem to have signed in again already'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku sedang tidak ada tugas sekolah', 'I don\'t have any schoolwork right now'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Alamat situs itu berbeda dari situs sekolah yang biasa, jadi pesannya belum tentu dari sekolah. Dengan menunjukkan pesan ke guru tanpa membukanya, kata sandimu aman dan guru bisa memastikan.',
                            'The website address is different from the usual school website, so the message may not be from the school. By showing the message to your teacher without opening it, your password stays safe and your teacher can check.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C4',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'wnb-5-b03',
                        'topic'       => 'IZN',
                        'steps'       => 2,
                        'situation'   => [
                            'Saat studi wisata ke Dieng, kamu memotret Nadia yang tertidur di bus dengan mulut terbuka. Teman-teman di grup kelas tahu, lalu menulis: "Kirim dong fotonya, pasti lucu!"',
                            'On a school trip to Dieng, you take a photo of Nadia asleep on the bus with mouth wide open. Friends in the class group find out and write: "Send us the photo, it must be funny!"',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Kirim fotonya ke grup karena teman-teman meminta', 'Send the photo to the group because your friends asked'],
                                'score'    => 0,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Nadia tidak tahu dirinya difoto. Foto itu hanya boleh dibagikan kalau ia setuju.',
                                    'Nadia doesn\'t know about the photo. It may only be shared if Nadia agrees.',
                                ],
                            ],
                            [
                                'text'     => ['Jadikan stiker lucu, lalu kirim ke grup', 'Turn it into a funny sticker, then send it to the group'],
                                'score'    => 0,
                                'code'     => 'DIG-ETK',
                                'feedback' => [
                                    'Stiker dari foto orang yang sedang tidur bisa membuatnya malu dan terus beredar.',
                                    'A sticker made from a photo of someone asleep can embarrass them and keep going round.',
                                ],
                            ],
                            [
                                'text'     => ['Bilang di grup bahwa kamu perlu izin Nadia dulu', 'Say in the group that you need Nadia\'s permission first'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Kamu menjaga perasaan Nadia walaupun teman-teman mendesak.',
                                    'Good choice. You protect Nadia\'s feelings even when your friends push you.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak menjawab grup dan tetap menyimpan fotonya', 'Don\'t answer the group, and keep the photo'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Fotonya tidak tersebar, bagus. Menjelaskan alasanmu bisa membuat teman lain ikut berpikir.',
                                    'The photo didn\'t spread, well done. Explaining your reason could make other friends think too.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Nadia belum tentu mau fotonya disebar', 'Nadia may not want the photo shared'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Foto yang terkirim bisa tersebar ke mana-mana', 'A photo that\'s been sent can spread anywhere'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Guru melarang memotret teman di dalam bus', 'Our teacher doesn\'t allow photos of friends on the bus'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-teman di grup sudah memintanya', 'My friends in the group asked for it'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Perjalanan ke Dieng memang jauh sekali', 'The trip to Dieng is really long'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Nadia tidak tahu dirinya difoto dan mungkin malu kalau foto itu tersebar. Dengan mengatakan di grup bahwa kamu perlu izin Nadia dulu, kamu menjaga perasaannya walaupun teman-teman mendesak.',
                            'Nadia doesn\'t know about the photo and might be embarrassed if it spreads. By saying in the group that you need Nadia\'s permission first, you protect Nadia\'s feelings even when your friends push you.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C5',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'wnb-5-b04',
                        'topic'       => 'ASG',
                        'steps'       => 2,
                        'situation'   => [
                            'Di grup kelas ada video berjudul "Embun upas bisa membuat orang sakit, jangan keluar rumah pagi-pagi!" Video itu tidak menyebut sumber. Temanmu, Fikri, membalas: "Sudah kukirim ke grup keluarga."',
                            'A video in the class group is titled "Embun upas can make people sick, don\'t go outside early in the morning!" The video doesn\'t give a source. Your friend Fikri replies: "I\'ve already sent it to my family group."',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Ikut mengirim ke grup lain supaya orang berhati-hati', 'Send it to other groups too, so people are careful'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Video tanpa sumber belum tentu benar. Menyebarkannya bisa membuat banyak orang cemas.',
                                    'A video with no source isn\'t always true. Spreading it can make lots of people worried.',
                                ],
                            ],
                            [
                                'text'     => [
                                    'Balas di grup: "Fikri bodoh, mana ada begitu!"',
                                    'Reply in the group: "Fikri, you\'re stupid, that\'s not even a thing!"',
                                ],
                                'score'    => 0,
                                'code'     => 'DIG-ETK',
                                'feedback' => [
                                    'Mengingatkan itu bagus, tapi kata-kata kasar membuat Fikri malu.',
                                    'Reminding someone is good, but rude words embarrass Fikri.',
                                ],
                            ],
                            [
                                'text'     => ['Ingatkan Fikri dengan sopan untuk cek sumbernya', 'Politely remind Fikri to check the source'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Kabar dicek dulu, dan Fikri tetap dihargai.',
                                    'Good choice. The news gets checked first, and Fikri is still respected.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak mengirim dan tidak menanggapi apa pun', 'Don\'t send it and don\'t respond at all'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak ikut menyebarkan, bagus. Memberi tahu dengan sopan bisa menghentikan kabar itu.',
                                    'You didn\'t help spread it, well done. Telling someone politely can stop the news.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Video tanpa sumber bisa membuat orang cemas', 'A video with no source can make people worried'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kalau ditegur dengan sopan, Fikri tidak malu', 'If I tell Fikri politely, Fikri won\'t feel embarrassed'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Guru bilang tidak boleh menonton video di grup', 'Our teacher said we mustn\'t watch videos in the group'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Banyak orang di grup sudah percaya video itu', 'Lots of people in the group already believe the video'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku belum pernah melihat embun upas', 'I\'ve never seen embun upas'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Video tanpa sumber belum tentu benar, dan sudah mulai tersebar. Dengan mengingatkan Fikri dengan sopan untuk mengecek sumbernya, kabar itu bisa dicek dulu sebelum menyebar lebih jauh.',
                            'A video with no source isn\'t always true, and it has started to spread. By politely reminding Fikri to check the source, the news can be checked before it spreads further.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L3',
                        'bloom'       => 'C4',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
