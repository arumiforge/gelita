<?php

/**
 * Bank soal kelas 5 — TEMANGGUNG (wilayah 1, tingkat mudah).
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
    'difficulty' => 'mudah',
    'nodes'      => [
        'tmg-1' => [
            'engine'      => 'puzzle',
            'variant'     => 'susun_gambar',
            'indicator'   => '',
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
                [
                    'Selesaikan baris paling atas dulu, lalu turun baris demi baris.',
                    'Finish the top row first, then work down row by row.',
                ],
            ],
            'variants'    => [
                'a' => [
                    'title' => ['Pasar Papringan', 'Papringan Market'],
                    'ref'   => 'GNFI (2022) Pasar Papringan',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Pasar Papringan di Dusun Ngadiprono, Desa Ngadimulyo, Kecamatan Kedu, digelar di bawah rumpun bambu setiap Minggu Wage dan Minggu Pon. Pembeli membayar dengan pring, alat tukar dari bambu, dan pasar ini tidak memakai plastik.',
                                'Papringan Market in Ngadiprono Hamlet, Ngadimulyo Village, Kedu District, is held under a bamboo grove on the Javanese market days Minggu Wage and Minggu Pon. Shoppers pay with pring, tokens made of bamboo, and no plastic is used.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-i01',
                                'label'  => 'Pasar Papringan',
                                'note'   => 'pasar pagi di bawah rumpun bambu: lapak bambu, jajanan di tampah daun pisang, mainan kayu, pembeli membayar dengan keping bambu, tanpa plastik',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P09; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P09 di panduan gambar puzzle). Fakta baru yang wajib dicek: semua isi keterangan (sumber: GNFI 2022). Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title' => ['Wayang Kedu', 'Wayang Kedu'],
                    'ref'   => '08 → Keputusan pengembang (26 Oktober 2026): asal Wayang Kedu Temanggungan dikonfirmasi pengembang',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Wayang Kedu adalah wayang kulit asli wilayah Kedu yang berkembang di Temanggung. Dalang memainkan wayang di balik kelir, yaitu kain putih yang disinari lampu, dengan iringan gamelan.',
                                'Wayang Kedu is the shadow puppetry native to the Kedu area that grew in Temanggung. The dalang, or puppeteer, plays the puppets behind the kelir, a white cloth lit by a lamp, to the sound of gamelan.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-k01',
                                'label'  => 'Wayang Kedu',
                                'note'   => 'pertunjukan wayang kulit di pendapa desa: dalang dewasa di balik kelir yang disinari lampu, bayangan wayang di kelir, pemain gamelan, anak-anak menonton sambil duduk; tanpa gunung, kebun kopi, candi, kuda kepang, ikan, dan tulisan',
                                'source' => 'Baru: usulan prompt TMG-P11 (08 → Susun Gambar selama studi). Gambar dari tim katalog, lalu dicek ulang terhadap butir; sampai ada, gelita:mockups memasang gambar contoh.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi 10 (31 §14): variasi baru b Temanggung (Wayang Kedu, keterangan dari 08, tanpa status Warisan Budaya Takbenda). Teks Inggris: draf AI, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'c' => [
                    'title' => ['Gunung Sindoro dan Sumbing', 'Mount Sindoro and Mount Sumbing'],
                    'ref'   => 'Global Volcanism Program (Sindoro, Sumbing); Pemkab Temanggung (profil daerah)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Gunung Sindoro (sekitar 3.150 m) dan Gunung Sumbing (sekitar 3.370 m) disebut gunung kembar. Keduanya mengapit Temanggung dan membuat tanahnya subur.',
                                'Mount Sindoro (about 3,150 m) and Mount Sumbing (about 3,370 m) are called the twin mountains. They stand on either side of Temanggung and make its soil fertile.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-01',
                                'label'  => 'Gunung Sindoro dan Sumbing',
                                'note'   => 'Sindoro (kanan) dan Sumbing (kiri) dilihat dari ladang bertingkat di Temanggung; awan khas dan burung di celah puncak; petani dan kebun kopi di latar depan',
                                'source' => 'Sudah dibuat ulang di Katalog Aset (14 Oktober 2026, prompt TMG-P01; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang. Rujukan: https://commons.wikimedia.org/wiki/File:Sindoro_sumbing.jpg',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: catatan isi gambar diganti ke suasana siang (gambar katalog malam hari disarankan diganti). Perlu dicek ulang peneliti. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'd' => [
                    'title' => ['Candi Pringapus', 'Pringapus Temple'],
                    'ref'   => 'Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jateng',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Pringapus adalah candi Hindu dari abad ke-9. Di dalam ruang candinya tersimpan arca Nandi, arca lembu yang dihormati umat Hindu.',
                                'Pringapus Temple is a Hindu temple from the 9th century. Its chamber holds a statue of Nandi, the bull honoured by Hindus.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-03',
                                'label'  => 'Candi Pringapus',
                                'note'   => 'Candi Pringapus tampak depan',
                                'source' => 'Sudah dibuat ulang di Katalog Aset (14 Oktober 2026, prompt TMG-P02; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang. Rujukan: https://commons.wikimedia.org/wiki/File:Candi_Pringapus_Temanggung_Jateng.jpg',
                            ],
                            'review' => [
                                'status' => 'verified',
                                'note'   => 'Revisi Oktober 2026: gambar katalog berlatar putih gagal uji keping; buat ulang dengan prompt TMG-P02 (kode berkas tetap). Keterangan tidak berubah. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'e' => [
                    'title' => ['Kuda Lumping', 'Kuda Lumping'],
                    'ref'   => 'DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Pemprov Jateng: Nyadran Jaran Kepang',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kuda lumping atau jaran kepang memakai kuda-kudaan dari anyaman bambu. Di Temanggung ada ratusan kelompok kuda lumping.',
                                'Kuda lumping, or jaran kepang, uses horses made of woven bamboo. Temanggung has hundreds of kuda lumping groups.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-04',
                                'label'  => 'Kuda Lumping',
                                'note'   => 'penari kuda lumping dengan kuda anyaman bambu berwarna',
                                'source' => 'Boleh dipakai (lulus dengan catatan). Bila ingin satu gaya siang hari, buat ulang dengan prompt TMG-P03. Rujukan: https://commons.wikimedia.org/wiki/File:Kuda_lumping_Temanggung.jpg',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'f' => [
                    'title' => ['Situs Liyangan', 'The Liyangan Site'],
                    'ref'   => 'Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jateng',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Situs Liyangan di Desa Purbosari, Ngadirejo, ditemukan pada tahun 2008 saat warga menambang pasir. Para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi hangus yang tertimbun pasir dan batu dari letusan Gunung Sindoro.',
                                'The Liyangan Site in Purbosari Village, Ngadirejo, was found in 2008 while villagers were digging for sand. Researchers found the remains of wooden houses, temple buildings, stone paths and burnt grains of rice, all buried under sand and stones from an eruption of Mount Sindoro.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-d01',
                                'label'  => 'Situs Liyangan',
                                'note'   => 'situs ekskavasi di lereng Sindoro: talud batu bertingkat, kaki candi, jalan batu kuno, dinding galian berlapis, peneliti dewasa bekerja',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P04; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P04 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'g' => [
                    'title' => ['Prasasti Gondosuli', 'The Gondosuli Inscription'],
                    'ref'   => 'Pemkab Temanggung (Sejarah Temanggung); BPK Wilayah X Jateng',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Prasasti Gondosuli diperkirakan dibuat sekitar tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.',
                                'The Gondosuli Inscription is thought to have been made around 832 CE. An inscription is writing on stone or metal that records an important event.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-e01',
                                'label'  => 'Prasasti Gondosuli',
                                'note'   => 'situs Prasasti Gondosuli: batu andesit besar beraksara kuno di halaman situs, susunan pelindung dan pagar sesuai aslinya, batu-batu candi kuno di sekitarnya',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P05; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P05 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'h' => [
                    'title' => ['Umbul Jumprit', 'Umbul Jumprit Spring'],
                    'ref'   => 'Wikipedia: Umbul Jumprit; Mongabay (2018) Berkah Air dari Umbul Jumprit; Liputan6 (Umbul Jumprit & Waisak)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Umbul Jumprit adalah mata air di lereng Gunung Sindoro. Airnya jernih dan tidak pernah kering, lalu mengalir menjadi hulu Sungai Progo. Setiap Waisak, air suci untuk perayaan di Candi Borobudur diambil dari sini.',
                                'Umbul Jumprit is a spring on the slopes of Mount Sindoro. Its clear water never dries up and flows on to become the source of the Progo River. Every Vesak, holy water for the celebration at Borobudur Temple is taken from here.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-f01',
                                'label'  => 'Umbul Jumprit',
                                'note'   => 'mata air keluar dari bawah batu berlumut dan pohon besar, kolam jernih berkerikil, aliran awal Sungai Progo, pinus, monyet ekor panjang, gapura batu',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P06; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P06 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'i' => [
                    'title' => ['Panen Kopi di Lereng Sindoro–Sumbing', 'Coffee Harvest on the Sindoro–Sumbing Slopes'],
                    'ref'   => 'Dinas Pertanian Kab. Temanggung; MPIG Kopi Java Arabika Sindoro Sumbing',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Petani di lereng Gunung Sindoro dan Sumbing menanam kopi arabika di ketinggian lebih dari 900 meter. Buah kopi yang sudah merah dipetik, diolah, lalu dikeringkan.',
                                'Farmers on the slopes of Mount Sindoro and Mount Sumbing grow arabica coffee at more than 900 metres. The ripe red coffee cherries are picked, processed and then dried.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-g01',
                                'label'  => 'Panen Kopi di Lereng Sindoro–Sumbing',
                                'note'   => 'panen kopi arabika di lereng bertingkat: petani dewasa memetik buah merah, pohon peneduh, buah dijemur di depan pondok, Sindoro di latar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P07; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P07 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'j' => [
                    'title' => ['Ikan Uceng dan Sungai Jernih', 'Uceng Fish and the Clear River'],
                    'ref'   => 'DJKI (indikasi geografis ikan uceng)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Ikan uceng adalah ikan sungai kecil yang menjadi makanan khas Temanggung. Ikan ini hidup di sungai berarus, misalnya Sungai Progo, dan biasanya digoreng kering.',
                                'Uceng is a small river fish and a special food of Temanggung. It lives in flowing rivers, such as the Progo River, and is usually fried until crispy.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-h01',
                                'label'  => 'Ikan Uceng dan Sungai Jernih',
                                'note'   => 'sungai pegunungan jernih berbatu, pencari ikan dengan serok kecil, ikan uceng kecil di atas kerikil, warung tepi sungai dengan uceng goreng',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P08; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P08 di panduan gambar puzzle). Fakta baru yang wajib dicek: "hidup di sungai yang airnya jernih" dan "biasanya digoreng kering". Gambar belum dibuat. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'k' => [
                    'title' => ['Perajin Kuda Kepang', 'The Kuda Kepang Makers'],
                    'ref'   => 'DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Pemprov Jateng: Nyadran Jaran Kepang',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Kuda kepang untuk tari kuda lumping dibuat dari anyaman bambu, lalu dicat dan dihias kain warna-warni. Agar kesenian ini tidak hilang, anak-anak muda diajak berlatih menari kuda lumping.',
                                'The hobby horses for the kuda lumping dance are made of woven bamboo, then painted and decorated with colourful cloth. So that this art does not disappear, young people are invited to practise the dance.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.tmg-1-j01',
                                'label'  => 'Perajin Kuda Kepang',
                                'note'   => 'halaman bengkel perajin: menganyam dan mengecat kuda kepang, kuda jadi digantung, tumpukan bilah bambu, remaja siap berlatih, Sumbing di latar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt TMG-P10; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (TMG-P10 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'tmg-2' => [
            'engine'           => 'rumpang',
            'variant'          => 'rumpang_bacaan',
            'indicator'        => 'D1a',
            'distractor_count' => 2,
            'genre'            => null,
            'title'            => ['Lengkapi Bacaan Temanggung', 'Complete the Temanggung Reading'],
            'instruction'      => [
                'Baca bacaannya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Ada dua kata yang tidak terpakai.',
                'Read the text. Tap an empty box, then tap the right word. Two of the words are not used.',
            ],
            'description'      => [
                'Catatan Jaka tentang Temanggung ada yang terhapus hujan. Lengkapi bacaannya supaya bisa dibaca lagi.',
                'Some of Jaka\'s notes about Temanggung were washed away by the rain. Complete the reading so it can be read again.',
            ],
            'hints'            => [
                [
                    'Baca seluruh paragraf sampai habis dulu, baru isi kotaknya.',
                    'Read the whole paragraph first, then fill in the boxes.',
                ],
                [
                    'Perhatikan kata-kata di sekitar kotak kosong. Kadang jawabannya sudah disebut di kalimat sebelumnya.',
                    'Look at the words around each box. Sometimes the answer was already mentioned in an earlier sentence.',
                ],
                [
                    'Ada dua kata pengecoh. Setiap kata hanya dipakai satu kali.',
                    'There are two decoy words. Each word is used only once.',
                ],
            ],
            'variants'         => [
                'a' => [
                    'title'       => ['Dua Gunung Temanggung', 'Two Mountains of Temanggung'],
                    'ref'         => 'Pemkab Temanggung (profil daerah); data ketinggian gunung PVMBG; Dinas Pertanian Kab. Temanggung dan MPIG Kopi Java Arabika Sindoro Sumbing (kopi di atas 900 m)',
                    'distractors' => [
                        ['Sindoro', 'Sindoro'],
                        ['pasir', 'sand'],
                    ],
                    'items'       => [
                        [
                            'key'    => 'tmg-2-a01',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Kabupaten Temanggung di Jawa Tengah diapit dua gunung api, yaitu Gunung Sindoro dan Gunung Sumbing. Orang menyebut keduanya gunung kembar karena bentuknya mirip. Tinggi Gunung Sindoro sekitar 3.150 meter, sedangkan Gunung ___ tingginya sekitar 3.370 meter.',
                                'Temanggung Regency in Central Java lies between two volcanoes, Mount Sindoro and Mount Sumbing. People call them the twin mountains because they look alike. Mount Sindoro is about 3,150 metres high, while Mount ___ is about 3,370 metres high.',
                            ],
                            'answer' => ['Sumbing', 'Sumbing'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'trap'   => [
                                'sindoro' => 'INF-LIT',
                            ],
                            'note'   => 'Nama sudah disebut di kalimat pertama; perbaikan tmg-2-a01 lama',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-2-a02',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Dahulu kedua gunung itu pernah meletus dan menyebarkan ___. Abu itu membuat tanah di lerengnya subur.',
                                'Long ago, both mountains erupted and spread ___. The ash made the soil on their slopes fertile.',
                            ],
                            'answer' => ['abu', 'ash'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'trap'   => [
                                'pasir' => 'INF-HUB',
                            ],
                            'note'   => 'Jawaban ada di kalimat berikutnya ("Abu itu …"); pasir menangkap siswa yang tidak menghubungkan dua kalimat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-2-a03',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Di tanah yang ___ itu, petani menanam kopi arabika. Kopi itu tumbuh di ketinggian lebih dari 900 meter.',
                                'In this ___ soil, farmers grow arabica coffee. The coffee grows at a height of more than 900 metres.',
                            ],
                            'answer' => ['subur', 'fertile'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'note'   => 'Tersurat di kalimat sebelumnya; tinggi tetap bersaing',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-2-a04',
                            'type'   => 'fill_blank_bank',
                            'prompt' => ['Jadi, kebun kopinya berada di tempat yang ___.', 'So the coffee gardens are in a ___ place.'],
                            'answer' => ['tinggi', 'high'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'note'   => 'Menyimpulkan dari "lebih dari 900 meter"',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title'       => ['Mata Air di Lereng Sindoro', 'A Spring on the Slopes of Sindoro'],
                    'ref'         => 'Wikipedia Umbul Jumprit; Mongabay (2018) Berkah Air dari Umbul Jumprit; Liputan6 (air suci Waisak dari Umbul Jumprit). Kalimat "akar pohon membantu menyimpan air" adalah pengetahuan IPA umum',
                    'distractors' => [
                        ['hujan', 'rainy'],
                        ['penuh', 'overflow'],
                    ],
                    'items'       => [
                        [
                            'key'    => 'tmg-2-b01',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Di lereng Gunung Sindoro ada mata air bernama Umbul Jumprit. Airnya jernih dan tidak pernah kering, bahkan pada musim ___. Musim kemarau adalah musim ketika hujan jarang turun.',
                                'On the slopes of Mount Sindoro there is a spring called Umbul Jumprit. Its water is clear and never dries up, even in the ___ season. The dry season is the time of year when rain rarely falls.',
                            ],
                            'answer' => ['kemarau', 'dry'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'trap'   => [
                                'hujan' => 'INF-HUB',
                            ],
                            'note'   => 'Kata "kemarau" ada di kalimat berikutnya; hujan menangkap siswa yang mengabaikan "bahkan"',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-2-b02',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Air dari Umbul ___ mengalir menjadi hulu Sungai Progo. Mata air ini juga penting bagi umat Buddha. Setiap Waisak, mereka mengambil air suci dari sini.',
                                'Water from Umbul ___ flows on to become the source of the Progo River. This spring is also important to Buddhists. Every Vesak, they take holy water from here.',
                            ],
                            'answer' => ['Jumprit', 'Jumprit'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'note'   => 'Nama sudah disebut',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-2-b03',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Air ___ itu lalu dibawa ke Candi Borobudur. Warga menjaga pepohonan di sekitar mata air, karena akar pohon membantu menyimpan air.',
                                'This ___ water is then carried to Borobudur Temple. Local people look after the trees around the spring, because tree roots help store water.',
                            ],
                            'answer' => ['suci', 'holy'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'note'   => 'Tersurat di kalimat sebelumnya; mengganti tmg-2-b03 lama ("sampah") yang terjawab dari norma',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-2-b04',
                            'type'   => 'fill_blank_bank',
                            'prompt' => ['Kalau pohon-pohon itu ditebang, mata air bisa ___.', 'If those trees are cut down, the spring could ___.'],
                            'answer' => ['kering', 'dry up'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'integrate',
                            'bloom'  => 'C2',
                            'trap'   => [
                                'penuh' => 'INF-HUB',
                            ],
                            'note'   => 'Sebab–akibat: pohon menyimpan air → tanpa pohon, mata air kering',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'tmg-3' => [
            'engine'      => 'boleh',
            'variant'     => 'benar_salah_bacaan',
            'indicator'   => 'D1a',
            'title'       => ['Benar atau Salah? Temanggung', 'True or False? Temanggung'],
            'instruction' => [
                'Baca layar informasi, lalu pilih BENAR atau SALAH untuk setiap pernyataan.',
                'Read the information screen, then choose TRUE or FALSE for each statement.',
            ],
            'description' => [
                'Di pameran budaya, sebuah layar informasi bercerita tentang peninggalan Temanggung. Baca layarnya, lalu periksa setiap pernyataan: benar atau salah?',
                'At the culture exhibition, an information screen tells about Temanggung\'s heritage. Read the screen, then check each statement: true or false?',
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
            'variants'    => [
                'a' => [
                    'passage' => [
                        'title'  => ['Situs Liyangan', 'The Liyangan Site'],
                        'body'   => [
                            ">> LAYAR INFORMASI · Pameran Budaya Temanggung\n~~ Dipasang oleh: Panitia Pameran Budaya SD Lereng Sindoro · Diperbarui Sabtu, 12 Juli\n\nSitus Liyangan berada di Desa Purbosari, Kecamatan Ngadirejo, Temanggung. Situs ini ditemukan pada tahun 2008 ketika warga sedang menambang pasir. Di dalam tanah, para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi yang hangus. Semua itu tertimbun pasir dan batu dari letusan Gunung Sindoro pada masa lalu.",
                            ">> INFORMATION SCREEN · Temanggung Culture Exhibition\n~~ Put up by: Culture Exhibition Committee, Lereng Sindoro Primary School · Updated Saturday, 12 July\n\nThe Liyangan Site is in Purbosari Village, Ngadirejo District, Temanggung. The site was found in 2008 while local people were digging for sand. Under the ground, researchers found the remains of wooden houses, a temple building, a stone road, and burnt grains of rice. All of them had been buried by sand and rocks from an eruption of Mount Sindoro long ago.",
                        ],
                        'genre'  => 'papan_info',
                        'layout' => 'info_screen',
                        'ref'    => 'Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jawa Tengah. Panitia dan sekolah di layar informasi fiktif',
                    ],
                    'items'   => [
                        [
                            'key'    => 'tmg-3-a01',
                            'type'   => 'verdict_card',
                            'prompt' => ['Situs Liyangan ditemukan pada tahun 2008.', 'The Liyangan Site was found in 2008.'],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-3-a02',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Situs Liyangan tertimbun pasir dan batu dari letusan Gunung Merapi.',
                                'The Liyangan Site was buried by sand and rocks from an eruption of Mount Merapi.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-3-a03',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Layar ini tidak menyebutkan siapa pemasangnya, jadi kita tidak tahu siapa yang bisa ditanya kalau isinya keliru.',
                                'This screen does not say who put it up, so we do not know who to ask if something on it is not accurate.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_penanda_sumber',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-3-a04',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Gambar ini menunjukkan salah satu benda yang ditemukan di Situs Liyangan.',
                                'This picture shows one of the things found at the Liyangan Site.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_gambar',
                            'media'  => [
                                'key'   => 'challenge.item.tmg-3-a03',
                                'label' => 'Butir padi hangus',
                                'note'  => 'butir padi hangus kehitaman (temuan arkeologi)',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'passage' => [
                        'title'  => ['Candi Pringapus dan Prasasti Gondosuli', 'Pringapus Temple and the Gondosuli Inscription'],
                        'body'   => [
                            ">> LAYAR INFORMASI · Pameran Budaya Temanggung\n~~ Dipasang oleh: Panitia Pameran Budaya SD Lereng Sindoro · Diperbarui Sabtu, 12 Juli\n\nCandi Pringapus adalah candi Hindu peninggalan abad ke-9 di Temanggung. Di dalam ruang candinya ada arca Nandi, yaitu arca lembu yang dihormati umat Hindu. Temanggung juga punya Prasasti Gondosuli yang diperkirakan dibuat sekitar tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.",
                            ">> INFORMATION SCREEN · Temanggung Culture Exhibition\n~~ Put up by: Culture Exhibition Committee, Lereng Sindoro Primary School · Updated Saturday, 12 July\n\nPringapus Temple is a Hindu temple from the 9th century in Temanggung. Inside its chamber is a statue of Nandi, the bull honoured by Hindus. Temanggung also has the Gondosuli Inscription, which is thought to have been made around 832 CE. An inscription is writing on stone or metal that records an important event.",
                        ],
                        'genre'  => 'papan_info',
                        'layout' => 'info_screen',
                        'ref'    => 'Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jawa Tengah. Panitia dan sekolah di layar informasi fiktif',
                    ],
                    'items'   => [
                        [
                            'key'    => 'tmg-3-b01',
                            'type'   => 'verdict_card',
                            'prompt' => ['Candi Pringapus adalah candi Buddha.', 'Pringapus Temple is a Buddhist temple.'],
                            'answer' => 'salah',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-3-b02',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Prasasti Gondosuli diperkirakan dibuat sekitar tahun 832 Masehi.',
                                'The Gondosuli Inscription is thought to have been made around 832 CE.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C1',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-3-b03',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Layar ini dipasang tanpa nama pemasang, jadi isinya tidak bisa ditanyakan kepada siapa pun.',
                                'This screen was put up with no name on it, so there is no one to ask about what it says.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-SUM',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_penanda_sumber',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'tmg-3-b04',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Gambar ini menunjukkan arca yang ada di dalam ruang Candi Pringapus.',
                                'This picture shows the statue inside the chamber of Pringapus Temple.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C2',
                            'skill'  => 'mengenali_gambar',
                            'media'  => [
                                'key'    => 'challenge.item.tmg-3-b04',
                                'label'  => 'Arca lembu duduk',
                                'note'   => 'arca lembu Nandi duduk dari batu',
                                'source' => 'Baru (08 → Gambar yang dibutuhkan): gambar lama tmg-3-b03 sengaja arca Buddha',
                            ],
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
                        ">> Grup Kelas 5A · Bu Sari (wali kelas) · 07.15\nAnak-anak, besok Kamis kita berkunjung ke Pameran Budaya di balai desa. Kumpul di sekolah pukul 07.30, ya. Bawa topi dan botol minum sendiri. Pameran ditutup pukul 11.00.",
                        ">> Class 5A Group · Bu Sari (class teacher) · 07:15\nChildren, tomorrow, Thursday, we are visiting the Culture Exhibition at the village hall. Meet at school at 07:30, OK? Bring a hat and your own water bottle. The exhibition closes at 11:00.",
                    ],
                    'genre'  => 'pesan_grup',
                    'layout' => 'chat',
                    'ref'    => 'Pesan grup kelas rekaan (kartu jangkar); tanpa klaim fakta budaya',
                ],
                'items'   => [
                    [
                        'key'    => 'tmg-3-x01',
                        'type'   => 'verdict_card',
                        'prompt' => [
                            'Menurut pesan itu, siswa berkumpul di balai desa pukul 07.30.',
                            'According to the message, students meet at the village hall at 07:30.',
                        ],
                        'answer' => 'salah',
                        'code'   => 'INF-KAT',
                        'dim'    => 'D1',
                        'sub'    => 'D1a',
                        'akm'    => 'L1',
                        'pirls'  => 'retrieve',
                        'bloom'  => 'C1',
                        'skill'  => 'informasi_tersurat',
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'tmg-3-x02',
                        'type'   => 'verdict_card',
                        'prompt' => ['Siswa diminta membawa botol minum sendiri.', 'Students are asked to bring their own water bottle.'],
                        'answer' => 'benar',
                        'code'   => 'INF-LIT',
                        'dim'    => 'D1',
                        'sub'    => 'D1a',
                        'akm'    => 'L1',
                        'pirls'  => 'retrieve',
                        'bloom'  => 'C1',
                        'skill'  => 'informasi_tersurat',
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
            ],
        ],
        'tmg-4' => [
            'engine'      => 'cari',
            'variant'     => 'cari_objek',
            'indicator'   => 'D3',
            'title'       => ['Cari Objek Temanggung', 'Find the Objects: Temanggung'],
            'instruction' => [
                'Baca petunjuk Mbah Kedu, lalu ketuk benda yang tepat. Ada empat benda yang harus kamu temukan.',
                'Read Mbah Kedu\'s clue, then tap the right object. There are four objects to find.',
            ],
            'description' => [
                'Di pasar budaya, benda khas Temanggung tercampur dengan benda dari daerah lain. Temukan empat benda yang dimaksud Mbah Kedu. Setelah itu, periksa sebuah unggahan tentang budaya Temanggung.',
                'At the culture fair, typical Temanggung objects are mixed with objects from other regions. Find the four objects Mbah Kedu describes. After that, check a post about Temanggung\'s culture.',
            ],
            'scene'       => [
                'key'    => 'challenge.tmg-4.scene',
                'label'  => 'Pasar Budaya Temanggung',
                'note'   => 'halaman balai desa/pasar budaya di kaki Sindoro–Sumbing, tiga meja panjang kosong berjajar ke depan (permukaan meja ± 47 %, 61 %, 76 % tinggi gambar)',
                'source' => 'Ilustrasi buatan tim / ilustrator (hindari foto dengan benda yang sudah ada)',
            ],
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
            'round1'      => [
                'title' => ['Pasar Budaya Temanggung', 'Temanggung Culture Fair'],
                'ref'   => 'DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Dinas Pertanian Kab. Temanggung; MPIG Kopi Java Arabika Sindoro Sumbing; Pemkab Temanggung (Sejarah Temanggung); BPK Wilayah X Jateng; DJKI (indikasi geografis ikan uceng)',
                'items' => [
                    [
                        'key'          => 'tmg-4-x01',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan kuda-kudaan dari anyaman bambu yang dipakai untuk menari kuda lumping.',
                            'Find the woven bamboo horse used for the kuda lumping dance.',
                        ],
                        'x'            => 76,
                        'y'            => 31,
                        'w'            => 10,
                        'dim'          => 'PBK',
                        'bloom'        => 'C1',
                        'answer_label' => 'kuda kepang bambu',
                        'media'        => [
                            'key'   => 'challenge.item.tmg-4-03',
                            'label' => 'Kuda kepang bambu',
                            'note'  => 'kuda kepang anyaman bambu berwarna (tanpa penari)',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'tmg-4-x02',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan biji kopi yang tumbuh di lereng Gunung Sindoro dan Sumbing.',
                            'Find the coffee beans that grow on the slopes of Mount Sindoro and Mount Sumbing.',
                        ],
                        'x'            => 14,
                        'y'            => 31,
                        'w'            => 10,
                        'dim'          => 'PBK',
                        'bloom'        => 'C1',
                        'answer_label' => 'biji kopi',
                        'media'        => [
                            'key'   => 'challenge.item.tmg-4-02',
                            'label' => 'Biji kopi',
                            'note'  => 'segenggam biji kopi',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'tmg-4-x03',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan arca lembu Nandi, seperti yang ada di dalam Candi Pringapus.',
                            'Find the statue of Nandi the bull, like the one inside Pringapus Temple.',
                        ],
                        'x'            => 12,
                        'y'            => 56.5,
                        'w'            => 12,
                        'dim'          => 'PBK',
                        'bloom'        => 'C1',
                        'answer_label' => 'arca Nandi',
                        'media'        => [
                            'key'   => 'challenge.item.tmg-4-06',
                            'label' => 'Arca Nandi',
                            'note'  => 'arca lembu Nandi duduk dari batu',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'tmg-4-x04',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan ikan uceng, ikan sungai kecil yang menjadi makanan khas Temanggung.',
                            'Find the uceng fish, a small river fish that is a special food of Temanggung.',
                        ],
                        'x'            => 29,
                        'y'            => 43,
                        'w'            => 11,
                        'dim'          => 'PBK',
                        'bloom'        => 'C1',
                        'answer_label' => 'ikan uceng',
                        'media'        => [
                            'key'   => 'challenge.item.tmg-4-04',
                            'label' => 'Ikan uceng',
                            'note'  => 'beberapa ikan uceng kecil memanjang',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'tmg-4-x05',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 76,
                        'y'      => 56.5,
                        'w'      => 12,
                        'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'],
                        'wrong'  => [
                            'Ini angklung, alat musik bambu dari Jawa Barat — bukan benda yang dimaksud petunjuk.',
                            'This is an angklung, a bamboo instrument from West Java — not the object in the clue.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.tmg-4-09',
                            'label' => 'Angklung',
                            'note'  => 'angklung bambu',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'tmg-4-x06',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 44,
                        'y'      => 56.5,
                        'w'      => 12,
                        'prompt' => ['Ondel-ondel (pengecoh)', 'Ondel-ondel (decoy)'],
                        'wrong'  => [
                            'Ini ondel-ondel, boneka raksasa dari budaya Betawi di Jakarta — bukan dari Temanggung.',
                            'This is an ondel-ondel, a giant puppet from Betawi culture in Jakarta — not from Temanggung.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.tmg-4-10',
                            'label' => 'Ondel-ondel',
                            'note'  => 'ondel-ondel kecil',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'tmg-4-x07',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 45,
                        'y'      => 31,
                        'w'      => 10,
                        'prompt' => ['Perahu pinisi (pengecoh)', 'Pinisi boat (decoy)'],
                        'wrong'  => [
                            'Ini perahu pinisi dari Sulawesi Selatan. Temanggung ada di pegunungan, jauh dari laut.',
                            'This is a pinisi boat from South Sulawesi. Temanggung is in the mountains, far from the sea.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.tmg-4-11',
                            'label' => 'Perahu pinisi',
                            'note'  => 'perahu pinisi berlayar',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'tmg-4-x08',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 60,
                        'y'      => 43,
                        'w'      => 11,
                        'prompt' => ['Sasando (pengecoh)', 'Sasando (decoy)'],
                        'wrong'  => [
                            'Ini sasando, alat musik petik dari Pulau Rote, Nusa Tenggara Timur — bukan dari Temanggung.',
                            'This is a sasando, a plucked instrument from Rote Island, East Nusa Tenggara — not from Temanggung.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.tmg-4-12',
                            'label' => 'Sasando',
                            'note'  => 'sasando',
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
                        'title' => ['Unggahan foto kuda lumping', 'A kuda lumping photo post'],
                        'media' => [
                            'key'   => 'challenge.item.tmg-1-04',
                            'label' => 'Kuda Lumping',
                        ],
                        'ref'   => 'kuda kepang dari anyaman bambu: DPRD Jateng (Kuda Lumping, Kesenian Khas Temanggung); uceng ikan sungai dan makanan khas Temanggung: DJKI (indikasi geografis), Antara Jateng (dicek di 05). Akun, sanggar, dan warung fiktif',
                    ],
                    'parts' => [
                        [
                            'key'   => 'A1',
                            'kind'  => 'account',
                            'label' => ['Nama akun pengunggah', 'Uploader\'s account name'],
                            'text'  => ['@jalan2_bima', '@jalan2_bima'],
                            'x'     => 3,
                            'y'     => 3,
                            'w'     => 38,
                            'h'     => 8,
                        ],
                        [
                            'key'   => 'A2',
                            'kind'  => 'credit',
                            'label' => ['Tulisan kecil di pojok foto', 'Small text in the corner of the photo'],
                            'text'  => ['Foto: Sanggar Kuda Pelangi', 'Photo: Sanggar Kuda Pelangi'],
                            'x'     => 5,
                            'y'     => 42,
                            'w'     => 26,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'A3',
                            'kind'  => 'caption',
                            'label' => ['Keterangan unggahan', 'Caption'],
                            'text'  => [
                                'Tari kuda lumping di lapangan desa! Kuda-kudaannya dari kayu jati 😍',
                                'Kuda lumping dance at the village field! The horses are made of teak wood 😍',
                            ],
                            'x'     => 3,
                            'y'     => 55,
                            'w'     => 38,
                            'h'     => 12,
                        ],
                        [
                            'key'   => 'A4',
                            'kind'  => 'board',
                            'label' => ['Papan informasi pameran', 'Exhibition information board'],
                            'text'  => [
                                'Kuda lumping memakai kuda-kudaan dari anyaman bambu yang dicat warna-warni.',
                                'Kuda lumping uses horses made of woven bamboo, painted in bright colours.',
                            ],
                            'x'     => 46,
                            'y'     => 3,
                            'w'     => 51,
                            'h'     => 22,
                        ],
                        [
                            'key'   => 'A5',
                            'kind'  => 'comment',
                            'label' => ['Komentar 1', 'Comment 1'],
                            'text'  => [
                                'Nadia: Keren! Terima kasih sudah berbagi foto kesenian Temanggung 👏',
                                'Nadia: Awesome! Thank you for sharing a photo of Temanggung\'s art 👏',
                            ],
                            'x'     => 46,
                            'y'     => 30,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'A6',
                            'kind'  => 'comment',
                            'label' => ['Komentar 2', 'Comment 2'],
                            'text'  => ['Doni: Tarian kuno, nggak zaman! 😝', 'Doni: Ancient dance, so uncool! 😝'],
                            'x'     => 46,
                            'y'     => 44,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'A7',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Kirim pesan ke sanggar', 'Send a message to the studio'],
                            'x'     => 3,
                            'y'     => 87,
                            'w'     => 25,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'A8',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Bagikan', 'Share'],
                            'x'     => 31,
                            'y'     => 87,
                            'w'     => 13,
                            'h'     => 9,
                        ],
                    ],
                    'items' => [
                        [
                            'key'       => 'tmg-4-a01',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Foto ini bukan buatan pemilik akun. Ketuk tulisan yang menunjukkan siapa pembuat fotonya.',
                                'This photo was not made by the account owner. Tap the text that shows who made the photo.',
                            ],
                            'answer'    => 'A2',
                            'clue_type' => 'ATR',
                            'main'      => [
                                'A1' => 'BUD-ATR',
                                'A4' => 'BUD-ATR',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'tmg-4-a02',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Bacalah papan informasi. Ketuk bagian unggahan yang isinya tidak sesuai dengan papan itu.',
                                'Read the information board. Tap the part of the post that does not match the board.',
                            ],
                            'answer'    => 'A3',
                            'clue_type' => 'REP',
                            'main'      => [
                                'A4' => 'BUD-REP',
                                'A6' => 'BUD-ETK',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'tmg-4-a03',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Kamu ingin memasang foto ini di majalah dinding kelas. Ketuk langkah pertama yang tepat.',
                                'You want to put this photo on your class noticeboard. Tap the right first step.',
                            ],
                            'answer'    => 'A7',
                            'clue_type' => 'IZN',
                            'main'      => [
                                'A8' => 'BUD-IZN',
                                'A1' => 'BUD-IZN',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C3',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'tmg-4-a04',
                            'type'      => 'find_part',
                            'prompt'    => ['Ketuk komentar yang menghargai kesenian ini.', 'Tap the comment that shows respect for this art.'],
                            'answer'    => 'A5',
                            'clue_type' => 'ETK',
                            'main'      => [
                                'A6' => 'BUD-ETK',
                                'A3' => 'BUD-ETK',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'scene' => [
                        'title' => ['Unggahan video ikan uceng', 'An uceng fish video post'],
                        'media' => [
                            'key'    => 'challenge.post.tmg-4-b',
                            'label'  => 'Uceng goreng di piring',
                            'note'   => 'ikan uceng goreng kering di atas piring, foto makanan dari atas; tanpa tulisan',
                            'source' => 'Baru (08 → Gambar yang dibutuhkan), atau potongan gambar Susun Gambar tmg-1-h01',
                        ],
                        'ref'   => 'kuda kepang dari anyaman bambu: DPRD Jateng (Kuda Lumping, Kesenian Khas Temanggung); uceng ikan sungai dan makanan khas Temanggung: DJKI (indikasi geografis), Antara Jateng (dicek di 05). Akun, sanggar, dan warung fiktif',
                    ],
                    'parts' => [
                        [
                            'key'   => 'B1',
                            'kind'  => 'account',
                            'label' => ['Nama akun pengunggah', 'Uploader\'s account name'],
                            'text'  => ['@sekar.kuliner', '@sekar.kuliner'],
                            'x'     => 3,
                            'y'     => 3,
                            'w'     => 38,
                            'h'     => 8,
                        ],
                        [
                            'key'   => 'B2',
                            'kind'  => 'credit',
                            'label' => ['Tulisan kecil di pojok video', 'Small text in the corner of the video'],
                            'text'  => ['Video: Warung Tepi Kali Bu Tini', 'Video: Warung Tepi Kali Bu Tini'],
                            'x'     => 5,
                            'y'     => 42,
                            'w'     => 26,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'B3',
                            'kind'  => 'caption',
                            'label' => ['Keterangan unggahan', 'Caption'],
                            'text'  => ['Uceng goreng, ikan laut khas Temanggung. Kriuk! 😋', 'Fried uceng, a sea fish from Temanggung. So crunchy! 😋'],
                            'x'     => 3,
                            'y'     => 55,
                            'w'     => 38,
                            'h'     => 12,
                        ],
                        [
                            'key'   => 'B4',
                            'kind'  => 'board',
                            'label' => ['Papan informasi pameran', 'Exhibition information board'],
                            'text'  => [
                                'Ikan uceng adalah ikan sungai kecil, makanan khas Temanggung.',
                                'Uceng is a small river fish, a special food of Temanggung.',
                            ],
                            'x'     => 46,
                            'y'     => 3,
                            'w'     => 51,
                            'h'     => 22,
                        ],
                        [
                            'key'   => 'B5',
                            'kind'  => 'comment',
                            'label' => ['Komentar 1', 'Comment 1'],
                            'text'  => ['Raka: Wah, jadi pengin coba. Terima kasih infonya 🙏', 'Raka: Wow, now I want to try it. Thanks for the info 🙏'],
                            'x'     => 46,
                            'y'     => 30,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'B6',
                            'kind'  => 'comment',
                            'label' => ['Komentar 2', 'Comment 2'],
                            'text'  => ['Tio: Ih, ikan kecil gitu, jijik! 🤢', 'Tio: Ew, such tiny fish, gross! 🤢'],
                            'x'     => 46,
                            'y'     => 44,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'B7',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Kirim pesan ke warung', 'Send a message to the food stall'],
                            'x'     => 3,
                            'y'     => 87,
                            'w'     => 25,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'B8',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Bagikan', 'Share'],
                            'x'     => 31,
                            'y'     => 87,
                            'w'     => 13,
                            'h'     => 9,
                        ],
                    ],
                    'items' => [
                        [
                            'key'       => 'tmg-4-b01',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Video ini bukan buatan pemilik akun. Ketuk tulisan yang menunjukkan siapa pembuat videonya.',
                                'This video was not made by the account owner. Tap the text that shows who made the video.',
                            ],
                            'answer'    => 'B2',
                            'clue_type' => 'ATR',
                            'main'      => [
                                'B1' => 'BUD-ATR',
                                'B4' => 'BUD-ATR',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'tmg-4-b02',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Bacalah papan informasi. Ketuk bagian unggahan yang isinya tidak sesuai dengan papan itu.',
                                'Read the information board. Tap the part of the post that does not match the board.',
                            ],
                            'answer'    => 'B3',
                            'clue_type' => 'REP',
                            'main'      => [
                                'B4' => 'BUD-REP',
                                'B6' => 'BUD-ETK',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'tmg-4-b03',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Kamu ingin memakai video ini untuk tugas kelas. Ketuk langkah pertama yang tepat.',
                                'You want to use this video for a class assignment. Tap the right first step.',
                            ],
                            'answer'    => 'B7',
                            'clue_type' => 'IZN',
                            'main'      => [
                                'B8' => 'BUD-IZN',
                                'B1' => 'BUD-IZN',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C3',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'tmg-4-b04',
                            'type'      => 'find_part',
                            'prompt'    => ['Ketuk komentar yang menghargai makanan khas ini.', 'Tap the comment that shows respect for this special food.'],
                            'answer'    => 'B5',
                            'clue_type' => 'ETK',
                            'main'      => [
                                'B6' => 'BUD-ETK',
                                'B3' => 'BUD-ETK',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L1',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'tmg-5' => [
            'engine'      => 'skenario',
            'variant'     => 'skenario_digital',
            'indicator'   => 'D2',
            'title'       => ['Skenario Digital Temanggung', 'Digital Scenarios: Temanggung'],
            'instruction' => [
                'Bacalah ceritanya. Pilih apa yang akan kamu lakukan, lalu pilih alasanmu.',
                'Read the story. Choose what you would do, then choose your reason.',
            ],
            'description' => [
                'Serpihan kelima Temanggung tersangkut di balik pesan dan kabar di layar tablet. Bayangkan setiap cerita terjadi padamu.',
                'Temanggung\'s fifth shard is caught behind the messages and news on a tablet screen. Imagine each story is happening to you.',
            ],
            'scenarios'   => [
                'a' => [
                    [
                        'key'         => 'tmg-5-a01',
                        'topic'       => 'PRI',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu menemukan situs kuis berhadiah. Tulisannya: "Isi nama lengkap, alamat rumah, dan nama sekolahmu, lalu ikut undian sepeda!" Kamu tidak tahu siapa pembuat situs itu.',
                            'You find a quiz website with prizes. It says: "Fill in your full name, home address, and school name, and enter the draw to win a bicycle!" You don\'t know who made the website.',
                        ],
                        'actions'     => [
                            [
                                'text'     => [
                                    'Isi nama, alamat, dan sekolah supaya bisa ikut undian',
                                    'Fill in your name, address, and school so you can enter the draw',
                                ],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Alamat rumah dan nama sekolah bisa dipakai orang yang tidak kita kenal untuk menemukanmu. Data seperti itu sebaiknya disimpan.',
                                    'Strangers could use your home address and school name to find you. Information like that is best kept to yourself.',
                                ],
                            ],
                            [
                                'text'     => ['Tutup situsnya dan cerita ke orang tua', 'Close the website and tell your parents'],
                                'score'    => 2,
                                'feedback' => [
                                    'Pilihan yang aman. Orang tua bisa membantu memeriksa apakah kuis itu sungguhan.',
                                    'A safe choice. Your parents can help check whether the quiz is real.',
                                ],
                            ],
                            [
                                'text'     => ['Tutup situsnya dan lanjut bermain yang lain', 'Close the website and go on playing something else'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu sudah aman. Akan lebih baik lagi kalau kamu cerita ke orang tua, supaya mereka tahu ada situs seperti itu.',
                                    'You are safe now. It would be even better to tell your parents, so they know websites like that exist.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Alamat dan sekolahku bisa dipakai orang asing', 'Strangers could use my address and school'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Pokoknya tidak boleh mengisi apa pun di internet', 'We\'re just not allowed to fill in anything online'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-temanku sudah ikut semua undian itu', 'My friends have all entered that draw'],
                                'code' => 'R-SOS',
                            ],
                        ],
                        'explanation' => [
                            'Situs yang tidak jelas pembuatnya bisa memakai alamat dan nama sekolahmu. Dengan menutup situs dan bercerita ke orang tua, datamu aman dan orang tua bisa memeriksa kuis itu.',
                            'A website with an unknown maker could use your address and school name. By closing the website and telling your parents, your data stays safe and your parents can check the quiz.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'tmg-5-a02',
                        'topic'       => 'SAN',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu sedang bermain game di tablet. Seseorang yang tidak kamu kenal mengirim pesan: "Aku bisa bikin levelmu naik cepat. Kirim kata sandimu, ya!"',
                            'You are playing a game on a tablet. Someone you don\'t know sends a message: "I can level you up fast. Send me your password!"',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Kirim kata sandinya supaya levelmu cepat naik', 'Send your password so you level up fast'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Orang yang tahu kata sandimu bisa masuk dan memakai akunmu. Kata sandi hanya untuk pemilik akun.',
                                    'Anyone who knows your password can get into your account and use it. A password is only for the account owner.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan pesan itu ke orang tua, lalu laporkan', 'Show the message to your parents, then report it'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat sekali. Kata sandimu aman, dan orang dewasa bisa membantu melapor.',
                                    'Good choice. Your password is safe, and a grown-up can help you report it.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak membalas, lalu keluar dari game itu', 'Don\'t reply, then leave the game'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kata sandimu aman. Ceritakan juga ke orang tua supaya akun itu bisa dilaporkan.',
                                    'Your password is safe. Tell your parents too, so the account can be reported.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Orang yang tahu sandiku bisa memakai akunku', 'Someone who knows my password could use my account'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kata guru, kata sandi tidak boleh diberikan', 'My teacher says passwords must not be given away'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Aku sedang malas bermain game hari ini', 'I don\'t feel like playing games today'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Kata sandi hanya untuk pemilik akun. Dengan menunjukkan pesan ke orang tua lalu melapor, kata sandimu aman dan orang dewasa bisa membantu menghentikan akun itu.',
                            'A password is only for the account owner. By showing the message to your parents and reporting it, your password stays safe and a grown-up can help stop that account.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'tmg-5-a03',
                        'topic'       => 'IZN',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu punya foto dirimu saat menari kuda lumping. Kamu ingin mengunggahnya ke akun media sosial milik ibumu. Ibumu sedang memasak di dapur.',
                            'You have a photo of yourself dancing kuda lumping. You want to post it on your mum\'s social media account. Your mum is cooking in the kitchen.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Unggah sendiri lewat HP ibu, nanti baru bilang', 'Post it yourself with Mum\'s phone and tell her later'],
                                'score'    => 0,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Akun itu milik ibumu. Sebelum mengunggah apa pun di akun orang lain, kita perlu izin pemiliknya.',
                                    'That account belongs to your mum. Before posting anything on someone else\'s account, we need the owner\'s permission.',
                                ],
                            ],
                            [
                                'text'     => ['Tanya kakakmu dulu, boleh diunggah atau tidak', 'Ask your older brother or sister first whether it can be posted'],
                                'score'    => 1,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Bertanya itu bagus. Tapi yang punya akun adalah ibumu, jadi izinnya perlu dari ibu.',
                                    'Asking is good. But the account belongs to your mum, so the permission has to come from her.',
                                ],
                            ],
                            [
                                'text'     => ['Tunggu ibu selesai memasak, lalu tanya ibu', 'Wait until Mum finishes cooking, then ask her'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Ibu bisa memutuskan, dan bisa membantu memilih siapa saja yang boleh melihat foto itu.',
                                    'Good choice. Mum can decide, and she can help choose who is allowed to see the photo.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Ibu pemilik akunnya, jadi ibu yang memutuskan', 'Mum owns the account, so Mum decides'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Kalau ketahuan tanpa izin, nanti aku dimarahi', 'If I get caught without asking, I\'ll be told off'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Biar teman-temanku cepat melihat aku menari', 'So my friends can quickly see me dance'],
                                'code' => 'R-SOS',
                            ],
                        ],
                        'explanation' => [
                            'Akun itu milik ibumu, jadi ibu yang berhak memutuskan. Dengan menunggu lalu bertanya ke ibu, kamu meminta izin kepada pemilik akunnya.',
                            'That account belongs to your mum, so she is the one who decides. By waiting and then asking her, you ask the account owner for permission.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'tmg-5-a04',
                        'topic'       => 'ASG',
                        'steps'       => 1,
                        'situation'   => [
                            'Di grup keluarga muncul pesan: "Mulai besok semua sekolah di Temanggung libur seminggu! Sebarkan ke semua grup!" Pesan itu tidak menyebut siapa pembuatnya.',
                            'A message appears in the family group: "Starting tomorrow, all schools in Temanggung are closed for a week! Share this with every group!" The message does not say who made it.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Teruskan ke grup kelas supaya teman-teman tahu', 'Forward it to the class group so your friends know'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Kabar tanpa sumber belum tentu benar. Kalau diteruskan, banyak teman bisa ikut bingung.',
                                    'News without a source isn\'t always true. If you forward it, many friends could get confused too.',
                                ],
                            ],
                            [
                                'text'     => ['Tanyakan dulu ke wali kelas apakah itu benar', 'First ask your class teacher whether it is true'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Guru dan orang tua bisa memeriksa ke sumber yang jelas, misalnya pengumuman sekolah.',
                                    'Good choice. Teachers and parents can check with a clear source, such as a school announcement.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak meneruskan dan tidak bertanya ke siapa pun', 'Don\'t forward it and don\'t ask anyone'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak ikut menyebarkan, bagus. Bertanya ke guru atau orang tua akan lebih membantu.',
                                    'You didn\'t help spread it, well done. Asking a teacher or your parents would help even more.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Kabar tanpa sumber belum tentu benar', 'News without a source isn\'t always true'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Ayah melarangku membalas pesan di grup', 'Dad doesn\'t let me reply to messages in groups'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Biar teman-teman tahu duluan dari aku', 'So my friends hear it from me first'],
                                'code' => 'R-SOS',
                            ],
                        ],
                        'explanation' => [
                            'Kabar tanpa pembuat belum tentu benar. Dengan bertanya dulu ke wali kelas, kamu tahu kebenarannya dari sumber yang jelas sebelum ada yang ikut bingung.',
                            'News with no maker isn\'t always true. By asking your class teacher first, you learn the truth from a clear source before anyone else gets confused.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
                'b' => [
                    [
                        'key'         => 'tmg-5-b01',
                        'topic'       => 'PRI',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu ingin ikut lomba menggambar daring. Formulirnya meminta nama, kelas, nomor HP orang tua, dan alamat rumah lengkap. Di halaman itu tidak ada nama penyelenggara.',
                            'You want to enter an online drawing competition. The form asks for your name, class, your parent\'s phone number, and your full home address. The page does not show the organiser\'s name.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Isi semua data supaya bisa langsung ikut lomba', 'Fill in everything so you can enter right away'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Nomor HP orang tua dan alamat rumah adalah data pribadi. Jangan diberikan ke penyelenggara yang tidak jelas.',
                                    'Your parent\'s phone number and your home address are personal data. Don\'t give them to an organiser you don\'t know.',
                                ],
                            ],
                            [
                                'text'     => ['Berhenti mengisi dan tanya orang tua dulu', 'Stop filling it in and ask your parents first'],
                                'score'    => 2,
                                'feedback' => [
                                    'Pilihan yang aman. Orang tua bisa mencari tahu siapa penyelenggaranya.',
                                    'A safe choice. Your parents can find out who the organiser is.',
                                ],
                            ],
                            [
                                'text'     => ['Berhenti mengisi, lalu cari lomba yang lain', 'Stop filling it in, then look for another competition'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Datamu aman. Akan lebih baik kalau orang tua juga tahu.',
                                    'Your data is safe. It would be better if your parents knew too.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Data pribadi bisa dipakai orang asing', 'Strangers could use personal data'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Pokoknya tidak boleh mengisi formulir apa pun', 'We\'re just not allowed to fill in any forms'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Aku memang tidak terlalu suka menggambar', 'I don\'t really like drawing anyway'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Penyelenggara lomba ini tidak jelas, padahal ia meminta nomor HP orang tua dan alamat rumah. Dengan berhenti dan bertanya ke orang tua, datamu aman dan orang tua bisa mencari tahu siapa penyelenggaranya.',
                            'The organiser of this competition is unknown, yet it asks for your parent\'s phone number and your home address. By stopping and asking your parents, your data stays safe and they can find out who the organiser is.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'tmg-5-b02',
                        'topic'       => 'SAN',
                        'steps'       => 1,
                        'situation'   => [
                            'Di ruang obrolan game, akun yang tidak kamu kenal menulis: "Halo, aku bisa kasih koin gratis. Tulis kata sandimu di sini, ya!"',
                            'In a game chat room, an account you don\'t know writes: "Hi, I can give you free coins. Write your password here!"',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Tulis kata sandinya supaya dapat koin gratis', 'Write your password to get free coins'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Kata sandi yang ditulis di ruang obrolan bisa dibaca orang lain. Akunmu bisa diambil alih.',
                                    'A password written in a chat room can be read by others. Your account could be taken over.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan ke orang tua, lalu laporkan akunnya', 'Show your parents, then report the account'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Akunmu aman dan akun itu bisa dilaporkan.',
                                    'Good choice. Your account is safe and that account can be reported.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak menjawab, lalu keluar dari ruang obrolan', 'Don\'t answer, then leave the chat room'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => ['Akunmu aman. Ceritakan juga ke orang tua, ya.', 'Your account is safe. Tell your parents too, OK?'],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Orang yang tahu sandiku bisa mengambil akunku', 'Someone who knows my password could take my account'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kata guru, kata sandi itu harus dirahasiakan', 'My teacher says passwords must be kept secret'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-temanku juga tidak menulis kata sandi', 'My friends don\'t write their passwords either'],
                                'code' => 'R-SOS',
                            ],
                        ],
                        'explanation' => [
                            'Kata sandi yang ditulis di ruang obrolan bisa dibaca orang lain. Dengan menunjukkan ke orang tua lalu melaporkan akunnya, akunmu aman dan akun itu bisa dihentikan.',
                            'A password written in a chat room can be read by others. By showing your parents and reporting the account, your account stays safe and that account can be stopped.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'tmg-5-b03',
                        'topic'       => 'IZN',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu punya video dirimu sedang belajar menari. Kamu ingin mengunggahnya ke akun media sosial milik ayahmu. Ayahmu sedang di kebun kopi.',
                            'You have a video of yourself learning to dance. You want to post it on your dad\'s social media account. Your dad is at the coffee farm.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Unggah sendiri lewat HP ayah, nanti baru bilang', 'Post it yourself with Dad\'s phone and tell him later'],
                                'score'    => 0,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Akun itu milik ayahmu. Perlu izin pemiliknya dulu sebelum mengunggah.',
                                    'That account belongs to your dad. You need the owner\'s permission before posting.',
                                ],
                            ],
                            [
                                'text'     => ['Tanya temanmu dulu, boleh diunggah atau tidak', 'Ask a friend first whether it can be posted'],
                                'score'    => 1,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Bertanya itu bagus. Tapi yang berhak memberi izin adalah pemilik akun, yaitu ayahmu.',
                                    'Asking is good. But the one who can give permission is the account owner, your dad.',
                                ],
                            ],
                            [
                                'text'     => ['Tunggu ayah pulang, lalu tanya ayah', 'Wait until Dad comes home, then ask him'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Ayah bisa memutuskan dan membantu mengatur siapa yang boleh menonton.',
                                    'Good choice. Dad can decide and help set who is allowed to watch.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Ayah pemilik akunnya, jadi ayah yang memutuskan', 'Dad owns the account, so Dad decides'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Kalau ketahuan tanpa izin, nanti aku dimarahi', 'If I get caught without asking, I\'ll be told off'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Biar banyak orang menyukai video menariku', 'So lots of people will like my dance video'],
                                'code' => 'R-SOS',
                            ],
                        ],
                        'explanation' => [
                            'Akun itu milik ayahmu, jadi ayah yang berhak memberi izin. Dengan menunggu ayah pulang lalu bertanya, kamu meminta izin kepada pemilik akunnya.',
                            'That account belongs to your dad, so he is the one who can give permission. By waiting for him to come home and then asking, you ask the account owner for permission.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'tmg-5-b04',
                        'topic'       => 'ASG',
                        'steps'       => 1,
                        'situation'   => [
                            'Di grup kelas ada pesan: "Minum kopi bikin anak-anak jadi pintar! Sebarkan ke semua teman!" Pesan itu tidak menyebut siapa pembuatnya.',
                            'A message appears in the class group: "Drinking coffee makes children smarter! Share this with all your friends!" The message does not say who made it.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Teruskan ke grup lain supaya banyak yang tahu', 'Forward it to other groups so many people know'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Kabar tanpa sumber belum tentu benar. Kopi mengandung kafein dan bukan minuman untuk anak-anak.',
                                    'News without a source isn\'t always true. Coffee contains caffeine and is not a drink for children.',
                                ],
                            ],
                            [
                                'text'     => ['Tanyakan dulu ke guru apakah itu benar', 'First ask your teacher whether it is true'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Guru dan orang tua bisa membantu mencari sumber yang dapat dipercaya.',
                                    'Good choice. Teachers and parents can help find a source you can trust.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak meneruskan dan tidak bertanya ke siapa pun', 'Don\'t forward it and don\'t ask anyone'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak ikut menyebarkan, bagus. Bertanya akan lebih membantu teman-temanmu.',
                                    'You didn\'t help spread it, well done. Asking would help your friends more.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Kabar tanpa sumber belum tentu benar', 'News without a source isn\'t always true'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Ibu melarangku membuka pesan di grup kelas', 'Mum doesn\'t let me open messages in the class group'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Aku memang tidak suka minum kopi', 'I just don\'t like drinking coffee'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Pesan tanpa pembuat belum tentu benar. Dengan bertanya dulu ke guru, kamu tidak ikut menyebarkan kabar yang belum pasti.',
                            'A message with no maker isn\'t always true. By asking your teacher first, you avoid spreading news that isn\'t certain.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L1',
                        'bloom'       => 'C3',
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
