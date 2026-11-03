<?php

/**
 * Bank soal kelas 5 — MAGELANG (wilayah 2, tingkat sedang).
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
    'difficulty' => 'sedang',
    'nodes'      => [
        'mgl-1' => [
            'engine'      => 'puzzle',
            'variant'     => 'susun_gambar',
            'indicator'   => '',
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
                [
                    'Mulai dari kepingan yang punya garis langit atau garis tanah.',
                    'Start with the pieces that show the sky line or the ground line.',
                ],
                [
                    'Kepingan dengan warna dan garis yang bersambung pasti bersebelahan.',
                    'Pieces whose colours and lines continue must sit next to each other.',
                ],
                [
                    'Kerjakan baris demi baris: lengkapi dulu empat keping di baris paling atas.',
                    'Work row by row: first complete the four pieces of the top row.',
                ],
            ],
            'variants'    => [
                'a' => [
                    'title' => ['Candi Mendut', 'Mendut Temple'],
                    'ref'   => 'BPK Wilayah X; visitmagelang.id; Liputan6 (Umbul Jumprit & Waisak)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Mendut menyimpan arca Buddha setinggi sekitar tiga meter. Setiap Waisak, prosesi umat Buddha dimulai dari candi ini.',
                                'Mendut Temple holds a Buddha statue about three metres tall. Every Vesak, the Buddhist procession starts from this temple.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-03',
                                'label'  => 'Candi Mendut',
                                'note'   => 'Candi Mendut tampak depan dengan tangga masuk',
                                'source' => 'Lulus uji keping. Cek bentuk atap dan tangga dengan foto Candi Mendut asli; bila berbeda, buat ulang dengan prompt MGL-P03. Rujukan: https://commons.wikimedia.org/wiki/File:Mendut_Temple.jpg',
                            ],
                            'review' => [
                                'status' => 'verified',
                                'note'   => 'Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title' => ['Candi Pawon', 'Pawon Temple'],
                    'ref'   => 'BPK Wilayah X; visitmagelang.id',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Pawon berdiri di antara Candi Mendut dan Candi Borobudur. Ketiga candi Buddha itu berada hampir pada satu garis lurus.',
                                'Pawon Temple stands between Mendut Temple and Borobudur Temple. These three Buddhist temples lie almost in one straight line.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-e01',
                                'label'  => 'Candi Pawon',
                                'note'   => 'Candi Pawon di taman desa di antara sawah: tangga, panel berukir, stupa kecil di atap, rumah beratap merah, Perbukitan Menoreh di latar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P05; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (MGL-P05 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10: gambar diganti (stupa kecil di atap mirip sasaran mgl-4-x01); gambar baru dari tim katalog. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'c' => [
                    'title' => ['Stupa Borobudur', 'The Stupas of Borobudur'],
                    'ref'   => 'UNESCO World Heritage Centre (No. 592); Kemenag Bimas Buddha; Kompas Travel (2025)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Borobudur dibangun pada abad ke-8 dan ke-9. Di teras bundarnya berdiri 72 stupa berlubang, masing-masing berisi arca Buddha.',
                                'Borobudur Temple was built in the 8th and 9th centuries. On its round terraces stand 72 perforated stupas, each holding a Buddha statue.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-01',
                                'label'  => 'Stupa Borobudur',
                                'note'   => 'teras bundar Borobudur saat fajar: stupa berlubang pada berbagai jarak, arca Buddha terlihat dari lubang stupa terdekat, stupa induk, Merapi dan Merbabu di cakrawala',
                                'source' => 'Sudah dibuat ulang di Katalog Aset (14 Oktober 2026, prompt MGL-P01; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang. Rujukan: https://commons.wikimedia.org/wiki/File:Borobudur_stupas_on_upper_terrace.jpg',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: catatan isi gambar diperbarui; gambar katalog gagal uji keping dan stupa induk salah letak, wajib diganti. Perlu dicek ulang peneliti. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'd' => [
                    'title' => ['Penari Topeng Ireng', 'Topeng Ireng Dancers'],
                    'ref'   => 'detikEdu (2022) Tari Topeng Ireng; Visit Jawa Tengah; Kompas Regional (2024)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Penari Topeng Ireng memakai hiasan kepala dari bulu berwarna-warni dan lonceng kecil di kaki. Tarian ini tumbuh di Desa Tuksongo, Borobudur.',
                                'Topeng Ireng dancers wear colourful feather headdresses and small bells on their ankles. The dance grew in Tuksongo Village, Borobudur.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-02',
                                'label'  => 'Penari Topeng Ireng',
                                'note'   => 'penari Topeng Ireng dengan kuluk bulu warna-warni',
                                'source' => 'Boleh dipakai (lulus dengan catatan, K6~K7 bertekstur sama). Cek kostum dengan foto Topeng Ireng asli. Rujukan: https://commons.wikimedia.org/wiki/File:Penari_Topeng_Ireng_03.jpg',
                            ],
                            'review' => [
                                'status' => 'verified',
                                'note'   => 'Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'e' => [
                    'title' => ['Relief Borobudur', 'The Reliefs of Borobudur'],
                    'ref'   => 'UNESCO World Heritage Centre (No. 592); Wikipedia: Borobudur (arah membaca relief)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Dinding Candi Borobudur dihiasi 2.672 panel relief. Relief dibaca berurutan sambil berjalan searah jarum jam, dimulai dari sisi timur, supaya ceritanya utuh. Pengunjung boleh melihat, tetapi tidak boleh menyentuhnya.',
                                'Borobudur Temple\'s walls are decorated with 2,672 relief panels. The reliefs are read in order while walking clockwise, starting from the east side, so the story stays whole. Visitors may look, but they must not touch them.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-d01',
                                'label'  => 'Relief Borobudur',
                                'note'   => 'lorong galeri Borobudur: panel relief (kapal bercadik, orang di pantai, pohon, hewan) dengan cahaya samping, pemandu menunjuk tanpa menyentuh, dua anak mengamati',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P04; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (MGL-P04 di panduan gambar puzzle). Fakta baru yang wajib dicek: "searah jarum jam, dimulai dari sisi timur" (sumber: Wikipedia Borobudur). Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'f' => [
                    'title' => ['Gunung Tidar', 'Mount Tidar'],
                    'ref'   => 'Tirto.id (Sejarah Gunung Tidar); Kompas Regional (2024)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Gunung Tidar adalah bukit hijau di tengah Kota Magelang, tingginya sekitar 503 meter di atas permukaan laut. Banyak orang mendaki tangganya untuk berolahraga. Dalam legenda, Gunung Tidar disebut paku tanah Jawa.',
                                'Mount Tidar is a green hill in the middle of Magelang City, about 503 metres above sea level. Many people climb its steps to exercise. In legend, Mount Tidar is called the nail of Java.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-f01',
                                'label'  => 'Gunung Tidar',
                                'note'   => 'tangga batu mendaki di hutan Gunung Tidar, keluarga dan orang berolahraga, celah pohon memperlihatkan atap kota Magelang dan Gunung Sumbing',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P06; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (MGL-P06 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'g' => [
                    'title' => ['Getuk Magelang', 'Magelang Getuk'],
                    'ref'   => 'detikFood (2025); Badan Otorita Borobudur',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Magelang dijuluki Kota Getuk. Getuk dibuat dari singkong yang direbus atau dikukus, ditumbuk halus, lalu diberi gula.',
                                'Magelang is nicknamed the Getuk Town. Getuk is made from cassava that is boiled or steamed, pounded smooth and then sweetened.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-g01',
                                'label'  => 'Getuk Magelang',
                                'note'   => 'lapak pasar dari sudut agak tinggi (bukan tampak atas): getuk tiga warna di tampah, singkong, lumpang dan alu, dandang beruap, latar pasar tajam',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P07; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (MGL-P07 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10: keterangan diperbaiki dari cek fakta 25. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'h' => [
                    'title' => ['Gerabah Klipoh', 'Klipoh Pottery'],
                    'ref'   => 'Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Warga Dusun Klipoh, sekitar tiga kilometer dari Candi Borobudur, membuat kendil, cobek, dan kuali dari tanah liat. Tanah liat dibentuk, lalu dibakar agar keras. Keahlian ini diwariskan turun-temurun.',
                                'The people of Klipoh Hamlet, about three kilometres from Borobudur Temple, make cooking pots, grinding bowls and woks from clay. The clay is shaped, then fired to make it hard. This skill is passed down from parents to children.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-h01',
                                'label'  => 'Gerabah Klipoh',
                                'note'   => 'halaman perajin gerabah Klipoh: membentuk tanah liat di alat putar tangan, kendil/cobek/kuali dijemur, pembakaran terbuka kecil, anggota keluarga belajar',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P08; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (MGL-P08 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'i' => [
                    'title' => ['Perjalanan Waisak', 'The Vesak Walk'],
                    'ref'   => 'BPK Wilayah X; visitmagelang.id; Liputan6 (Umbul Jumprit & Waisak)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Setiap Waisak, umat Buddha berjalan kaki dari Candi Mendut ke Candi Pawon, lalu berakhir di Candi Borobudur. Mereka membawa air suci dari Umbul Jumprit, Temanggung, dan api dari Mrapen, Grobogan. Kita menghormatinya dengan menjaga ketenangan dan memberi jalan.',
                                'Every Vesak, Buddhists walk from Mendut Temple to Pawon Temple and end at Borobudur Temple. They carry holy water from Umbul Jumprit in Temanggung and fire from Mrapen in Grobogan. We show respect by keeping quiet and making way.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-i01',
                                'label'  => 'Perjalanan Waisak',
                                'note'   => 'prosesi Waisak yang tenang di jalan desa: biksu membawa air suci dan api, umat berbaju putih dengan bunga teratai, bendera Buddhis, penonton tertib di balik tali, Borobudur di kejauhan',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P09; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi v2 (17 Okt 2026): sumber air suci (Umbul Jumprit) dan api (Mrapen, Grobogan) dipisah; versi draf 1 menyiratkan api juga dari Jumprit. | Sebelumnya: Revisi Oktober 2026: gambar & keterangan baru (MGL-P09 di panduan gambar puzzle). Fakta diambil dari bank soal; cek ulang rumusannya. Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
                'j' => [
                    'title' => ['Borobudur di Antara Dua Pasang Gunung Kembar', 'Borobudur between Two Pairs of Twin Mountains'],
                    'ref'   => 'Wikipedia: Borobudur; UNESCO World Heritage Centre (No. 592)',
                    'items' => [
                        [
                            'type'   => 'puzzle_arrange',
                            'prompt' => [
                                'Candi Borobudur berdiri di antara dua pasang gunung kembar, Sindoro–Sumbing dan Merbabu–Merapi, serta dua sungai, Progo dan Elo. Saat kabut pagi tersibak, candi batu itu terlihat jelas dari bukit di sekitarnya.',
                                'Borobudur Temple stands between two pairs of twin volcanoes, Sindoro–Sumbing and Merbabu–Merapi, and two rivers, the Progo and the Elo. When the morning mist clears, the stone temple can be seen clearly from the hills around it.',
                            ],
                            'media'  => [
                                'key'    => 'challenge.item.mgl-1-j01',
                                'label'  => 'Borobudur di Antara Dua Pasang Gunung Kembar',
                                'note'   => 'fajar dari bukit di barat Borobudur: candi terlihat jelas di tengah, kabut tipis hanya di cekungan, Merbabu dan Merapi di cakrawala timur, dek pandang dengan pengunjung',
                                'source' => 'Tersedia di Katalog Aset (14 Oktober 2026, prompt MGL-P10; uji keping otomatis lulus). Pasang dengan php spark gelita:katalog:impor. Sebelum pengambilan data: cek fakta dengan foto rujukan dan uji susun oleh dua orang.',
                            ],
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Revisi Oktober 2026: gambar & keterangan baru (MGL-P10 di panduan gambar puzzle). Fakta baru yang wajib dicek: "dua pasang gunung kembar … dua sungai, Progo dan Elo" (sumber: Wikipedia Borobudur). Gambar belum dibuat. | Revisi 10 (31 §14): huruf variasi dipetakan ulang; a dan b = dua variasi bersih untuk pengerjaan pertama selama studi, huruf lain untuk pengerjaan ulang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'mgl-2' => [
            'engine'           => 'rumpang',
            'variant'          => 'rumpang_bacaan',
            'indicator'        => 'D1a',
            'distractor_count' => 2,
            'genre'            => null,
            'title'            => ['Lengkapi Bacaan Magelang', 'Complete the Magelang Reading'],
            'instruction'      => [
                'Baca bacaannya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Ada dua kata yang tidak terpakai.',
                'Read the text. Tap an empty box, then tap the right word. Two of the words are not used.',
            ],
            'description'      => [
                'Sebuah artikel majalah anak tentang Magelang kehilangan beberapa kata. Hubungkan informasi di dalam bacaan, lalu lengkapi artikelnya.',
                'A children\'s magazine article about Magelang has lost some words. Connect the information in the reading, then complete the article.',
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
                [
                    'Ada dua kata pengecoh. Setiap kata hanya dipakai satu kali.',
                    'There are two decoy words. Each word is used only once.',
                ],
            ],
            'variants'         => [
                'a' => [
                    'title'       => ['Candi Borobudur', 'Borobudur Temple'],
                    'ref'         => 'UNESCO World Heritage Centre (No. 592); Wikipedia Borobudur (jumlah relief, dicek di 05)',
                    'distractors' => [
                        ['enam', 'six'],
                        ['sedikit', 'fewer'],
                    ],
                    'items'       => [
                        [
                            'key'    => 'mgl-2-a01',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Candi Borobudur berdiri di Kabupaten Magelang. Candi Buddha ini dibangun pada abad ke-8 dan ke-9. Di bagian bawah ada enam teras persegi, lalu di atasnya ada tiga teras bundar. Jadi, Borobudur memiliki ___ teras. Di teras bundar berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng.',
                                'Borobudur Temple stands in Magelang Regency. This Buddhist temple was built in the 8th and 9th centuries. At the bottom there are six square terraces, and above them there are three round terraces. So Borobudur has ___ terraces. On the round terraces stand 72 stupas with holes. A stupa is a bell-shaped building.',
                            ],
                            'answer' => ['sembilan', 'nine'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'trap'   => [
                                'enam' => 'INF-KAT',
                            ],
                            'note'   => 'Memadukan dua angka; enam menangkap siswa yang hanya mencocokkan kata di kalimat sebelumnya',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-a02',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Bangunan berbentuk lonceng yang paling besar ada di puncak candi dan disebut ___ induk. Di dalam setiap stupa berlubang duduk sebuah arca Buddha.',
                                'The biggest bell-shaped building is at the top of the temple and is called the main ___. Inside each stupa with holes sits a statue of the Buddha.',
                            ],
                            'answer' => ['stupa', 'stupa'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C2',
                            'note'   => 'Istilah didefinisikan di kalimat sebelumnya',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-a03',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Jadi, di teras bundar ada ___ arca Buddha di dalam stupa. Dinding candi dihiasi 2.672 panel relief, yaitu pahatan gambar. Sebanyak 1.460 panel bercerita, sedangkan 1.212 panel hanya hiasan.',
                                'So on the round terraces there are ___ Buddha statues inside stupas. The temple walls are decorated with 2,672 relief panels, which are carved pictures. Of these, 1,460 panels tell stories, while 1,212 panels are only decoration.',
                            ],
                            'answer' => ['72', '72'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'note'   => 'Satu arca di setiap stupa',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-a04',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Jadi, relief yang bercerita lebih ___ daripada relief hiasan. Batu-batu candi bisa rusak bila diinjak dan dipanjat.',
                                'So there are ___ story panels than decoration panels. The temple stones can be damaged if people step on them or climb them.',
                            ],
                            'answer' => ['banyak', 'more'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'trap'   => [
                                'sedikit' => 'INF-HUB',
                            ],
                            'note'   => 'Membandingkan 1.460 dan 1.212; mengganti mgl-2-a03 lama ("relief") yang butuh kosakata awal',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-a05',
                            'type'   => 'fill_blank_bank',
                            'prompt' => ['Oleh karena itu, pengunjung ___ memanjat stupa.', 'That is why visitors are ___ to climb the stupas.'],
                            'answer' => ['dilarang', 'forbidden'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'note'   => 'Sebab–akibat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
                'b' => [
                    'title'       => ['Tari Topeng Ireng', 'The Topeng Ireng Dance'],
                    'ref'         => 'detikEdu (2022) Tari Topeng Ireng; GNFI (2024) (Tuksongo, 1950-an; dicek di 05); Sulistiyarini & Handayani (2023), Metahumaniora 13(2), dan Kuncoro dkk. (2022), STRUKTURAL (makna keprajuritan; dicek di 25)',
                    'distractors' => [
                        ['diam', 'stop'],
                        ['penjajah', 'colonisers'],
                    ],
                    'items'       => [
                        [
                            'key'    => 'mgl-2-b01',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Topeng Ireng adalah tari rakyat dari Kabupaten Magelang. Tarian ini mulai tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur. Penarinya memakai kuluk, yaitu hiasan kepala dari bulu yang berwarna-warni. Saat penari melompat, bulu-bulu pada ___ itu ikut bergoyang. Di pergelangan kaki penari terpasang lonceng kecil.',
                                'Topeng Ireng is a folk dance from Magelang Regency. The dance began to grow around the 1950s in Tuksongo Village, Borobudur District. The dancers wear a kuluk, a headdress made of colourful feathers. When a dancer jumps, the feathers on the ___ sway too. Small bells are tied around the dancers\' ankles.',
                            ],
                            'answer' => ['kuluk', 'kuluk'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L1',
                            'pirls'  => 'retrieve',
                            'bloom'  => 'C2',
                            'note'   => 'Istilah didefinisikan di kalimat sebelumnya',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-b02',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Setiap kali kaki dihentakkan, lonceng itu ___. Satu kelompok bisa berisi banyak penari yang bergerak serempak.',
                                'Every time the feet stamp, the bells ___. One group can have many dancers who move as one.',
                            ],
                            'answer' => ['berbunyi', 'ring'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'trap'   => [
                                'diam' => 'INF-HUB',
                            ],
                            'note'   => 'Sebab–akibat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-b03',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Kalau ada satu penari yang bergerak lebih cepat daripada teman-temannya, tarian mereka menjadi tidak ___. Konon, tarian ini lahir dari kisah warga desa yang dahulu berjuang mengusir penjajah.',
                                'If one dancer moves faster than the others, their dance is no longer ___. It is said that the dance was born from the story of village people who once fought to drive out the colonisers.',
                            ],
                            'answer' => ['kompak', 'together'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'note'   => 'Menyimpulkan dari "bergerak serempak"',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-b04',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Karena itu, gerakannya tegas dan bersemangat, seperti barisan ___ yang siap bertempur. Dalam pertunjukan di lapangan desa, panitia memasang tali pembatas karena penari melompat ke segala arah.',
                                'That is why its moves are firm and full of spirit, like a line of ___ ready for battle. At a show on the village field, the organisers put up a rope barrier because the dancers jump in every direction.',
                            ],
                            'answer' => ['prajurit', 'soldiers'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'trap'   => [
                                'penjajah' => 'INF-LIT',
                            ],
                            'note'   => 'Dari "berjuang mengusir penjajah" dan "siap bertempur"; penjajah ada di kalimat yang sama, tetapi tarian menggambarkan pihak yang melawannya. Ditulis ulang 27 Oktober 2026 dari cek fakta ([25](25_CEK_FAKTA_BUDAYA.md)): kunci lama petani tidak didukung sumber',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-2-b05',
                            'type'   => 'fill_blank_bank',
                            'prompt' => [
                                'Penonton yang duduk di belakang tali tetap ___ dari lompatan penari.',
                                'The audience sitting behind the rope stays ___ from the dancers\' leaps.',
                            ],
                            'answer' => ['aman', 'safe'],
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'note'   => 'Sebab–akibat dari tali pembatas; diam dan kompak tetap bersaing secara bentuk kata. Mengganti mgl-2-b05 lama ("mengganggu") yang terjawab dari norma',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                    ],
                ],
            ],
        ],
        'mgl-3' => [
            'engine'      => 'boleh',
            'variant'     => 'benar_salah_bacaan',
            'indicator'   => 'D1a',
            'title'       => ['Benar atau Salah? Magelang', 'True or False? Magelang'],
            'instruction' => [
                'Baca unggahan dan komentarnya, lalu pilih BENAR atau SALAH untuk setiap pernyataan.',
                'Read the post and its comments, then choose TRUE or FALSE for each statement.',
            ],
            'description' => [
                'Sebuah akun resmi mengunggah kabar tentang Magelang, lalu orang-orang menulis komentar. Ada yang menyampaikan fakta, ada yang menyampaikan pendapat. Baca semuanya, lalu pilih BENAR atau SALAH untuk setiap pernyataan.',
                'An official account posted news about Magelang, and people wrote comments. Some share facts, others share opinions. Read everything, then choose TRUE or FALSE for each statement.',
            ],
            'hints'       => [
                [
                    'Fakta dapat dibuktikan kebenarannya, misalnya tahun, jumlah, atau nama tempat. Pendapat (opini) adalah penilaian atau perasaan seseorang, misalnya "paling indah". Hati-hati: ucapan seseorang bisa berisi fakta, dan pendapat tidak selalu diawali "menurut saya".',
                    'A fact can be proven, such as a year, a number, or a place name. An opinion is someone\'s judgement or feeling, such as "the most beautiful". Careful: what a person says can be a fact, and an opinion does not always start with "in my opinion".',
                ],
                [
                    'Gagasan pokok (ide pokok) adalah hal yang dibicarakan seluruh paragraf. Untuk pernyataan lain, hubungkan informasi dari paragraf pertama dan kedua.',
                    'The main idea is what the whole paragraph is about. For the other statements, connect information from the first and second paragraphs.',
                ],
                [
                    'Tindakan yang baik sesuai dengan aturan atau pesan di bacaan; tindakan yang melanggarnya tidak.',
                    'A good action follows the rules or advice in the reading; an action that breaks them does not.',
                ],
            ],
            'variants'    => [
                'a' => [
                    'passage' => [
                        'title'  => ['Menjaga Candi Borobudur', 'Looking After Borobudur Temple'],
                        'body'   => [
                            ">> @pengelola.candi ✓ akun resmi · 5 Mei\n\nCandi Borobudur adalah candi Buddha terbesar di dunia. Candi ini dibangun pada abad ke-8 dan ke-9 tanpa semen; batu-batunya disusun saling mengunci. Dindingnya dihiasi 2.672 panel relief. Sebanyak 1.460 panel bercerita, sedangkan sisanya hiasan. Pada tahun 1991, UNESCO menetapkan Borobudur sebagai Warisan Dunia.\n\nMari jaga Borobudur bersama. Ikuti rute dan aturan dari pengelola. Jangan memanjat stupa dan jangan menyentuh relief, sebab sentuhan tangan yang terus-menerus dapat membuat batu aus dan rusak. Jangan mencoret dinding dan jangan membuang sampah sembarangan.\n\n:: 💬 Dimas: Borobudur candi paling indah di dunia! 😍\n:: 💬 Bu Rina: Borobudur ditetapkan sebagai Warisan Dunia pada tahun 1991.\n:: 💬 Nanda: Katanya boleh kok pegang relief, asal pelan-pelan.",
                            ">> @pengelola.candi ✓ official account · 5 May\n\nBorobudur Temple is the largest Buddhist temple in the world. It was built in the 8th and 9th centuries without cement; its stones were fitted together so that they lock. Its walls are decorated with 2,672 relief panels. Of these, 1,460 panels tell stories, while the rest are decoration. In 1991, UNESCO named Borobudur a World Heritage Site.\n\nLet's look after Borobudur together. Follow the routes and rules from the temple managers. Do not climb the stupas and do not touch the reliefs, because being touched by hands again and again can wear the stone down and damage it. Do not draw on the walls and do not drop litter.\n\n:: 💬 Dimas: Borobudur is the most beautiful temple in the world! 😍\n:: 💬 Bu Rina: Borobudur was named a World Heritage Site in 1991.\n:: 💬 Nanda: I heard it's fine to touch the reliefs, as long as you do it gently.",
                        ],
                        'genre'  => 'unggahan_resmi',
                        'layout' => 'post',
                        'ref'    => 'UNESCO World Heritage Centre (No. 592); Wikipedia Borobudur; Kompas Travel (2025) (aturan pengunjung)',
                    ],
                    'items'   => [
                        [
                            'key'    => 'mgl-3-a01',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Paragraf kedua unggahan terutama menjelaskan cara menjaga Candi Borobudur.',
                                'The second paragraph of the post mainly explains how to look after Borobudur Temple.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-REL',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'skill'  => 'gagasan_pokok',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-a02',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Menurut unggahan, semua relief Borobudur bercerita.',
                                'According to the post, all of Borobudur\'s reliefs tell stories.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-GEN',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'skill'  => 'menilai_pilihan',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-a03',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Pengunjung boleh menyentuh relief asalkan tangannya sudah dicuci bersih.',
                                'Visitors may touch the reliefs as long as their hands have been washed clean.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-HUB',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'skill'  => 'menghubungkan_informasi',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-a04',
                            'type'   => 'verdict_card',
                            'prompt' => ['Komentar Dimas berisi pendapat.', 'Dimas\'s comment is an opinion.'],
                            'answer' => 'benar',
                            'code'   => 'INF-FOP',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C2',
                            'skill'  => 'membedakan_fakta_pendapat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-a05',
                            'type'   => 'verdict_card',
                            'prompt' => ['Komentar Bu Rina berisi pendapat.', 'Bu Rina\'s comment is an opinion.'],
                            'answer' => 'salah',
                            'code'   => 'INF-FOP',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C2',
                            'skill'  => 'membedakan_fakta_pendapat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-a06',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Tentang boleh tidaknya menyentuh relief, unggahan akun resmi lebih dapat dipercaya daripada komentar Nanda.',
                                'On whether the reliefs may be touched, the official account\'s post is more trustworthy than Nanda\'s comment.',
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
                    ],
                ],
                'b' => [
                    'passage' => [
                        'title'  => ['Gunung Tidar', 'Mount Tidar'],
                        'body'   => [
                            ">> @infowisata.kotamagelang ✓ akun resmi · 11 April\n\nDi tengah Kota Magelang ada bukit hijau bernama Gunung Tidar. Tingginya sekitar 503 meter di atas permukaan laut. Banyak orang mendaki tangganya untuk berolahraga. Pendaki diminta tetap berjalan di jalur tangga. Dalam cerita rakyat, Gunung Tidar disebut paku tanah Jawa. Konon, Pulau Jawa dulu bergoyang-goyang, lalu dipaku di Gunung Tidar agar tenang. Cerita seperti ini disebut legenda, bukan penjelasan ilmiah.\n\nDi lembah Gunung Tidar berdiri Akademi Militer sejak tahun 1957. Kota Magelang berulang tahun setiap tanggal 11 April. Tanggal itu diambil dari Prasasti Mantyasih yang dibuat pada tahun 907 Masehi.\n\n:: 💬 Kak Dewi: Pemandangan dari Gunung Tidar paling indah saat pagi! 🌄\n:: 💬 Pak Anton: Prasasti Mantyasih dibuat pada tahun 907 Masehi.\n:: 💬 Raffi: Katanya boleh lewat jalan pintas di hutan biar cepat sampai atas.",
                            ">> @infowisata.kotamagelang ✓ official account · 11 April\n\nIn the middle of Magelang City there is a green hill called Mount Tidar. It is about 503 metres above sea level. Many people climb its stairs to exercise. Climbers are asked to stay on the stairway path. In folk tales, Mount Tidar is called the nail of the island of Java. It is said that Java once wobbled and shook, and was then nailed down at Mount Tidar to keep it still. A story like this is called a legend, not a scientific explanation.\n\nIn the valley of Mount Tidar, the Military Academy has stood since 1957. Magelang City celebrates its birthday every 11 April. The date was taken from the Mantyasih Inscription, which was made in 907 CE.\n\n:: 💬 Kak Dewi: The view from Mount Tidar is most beautiful in the morning! 🌄\n:: 💬 Pak Anton: The Mantyasih Inscription was made in 907 CE.\n:: 💬 Raffi: I heard you can take a shortcut through the forest to get to the top faster.",
                        ],
                        'genre'  => 'unggahan_resmi',
                        'layout' => 'post',
                        'ref'    => 'Tirto.id (Sejarah Gunung Tidar); Kompas Regional (2024); Pemkot Magelang (Prasasti Mantyasih). Kedua akun fiktif',
                    ],
                    'items'   => [
                        [
                            'key'    => 'mgl-3-b01',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Paragraf pertama unggahan terutama menceritakan Gunung Tidar.',
                                'The first paragraph of the post is mainly about Mount Tidar.',
                            ],
                            'answer' => 'benar',
                            'code'   => 'INF-REL',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'skill'  => 'gagasan_pokok',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-b02',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Prasasti Mantyasih dibuat sesudah Akademi Militer berdiri.',
                                'The Mantyasih Inscription was made after the Military Academy was built.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-HUB',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'skill'  => 'menghubungkan_informasi',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-b03',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Menurut unggahan, Pulau Jawa benar-benar pernah bergoyang lalu dipaku di Gunung Tidar.',
                                'According to the post, the island of Java really did once wobble and was then nailed down at Mount Tidar.',
                            ],
                            'answer' => 'salah',
                            'code'   => 'INF-LIT',
                            'dim'    => 'D1',
                            'sub'    => 'D1a',
                            'akm'    => 'L2',
                            'pirls'  => 'infer',
                            'bloom'  => 'C2',
                            'skill'  => 'informasi_tersurat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-b04',
                            'type'   => 'verdict_card',
                            'prompt' => ['Komentar Kak Dewi berisi fakta.', 'Kak Dewi\'s comment is a fact.'],
                            'answer' => 'salah',
                            'code'   => 'INF-FOP',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C2',
                            'skill'  => 'membedakan_fakta_pendapat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-b05',
                            'type'   => 'verdict_card',
                            'prompt' => ['Komentar Pak Anton berisi fakta.', 'Pak Anton\'s comment is a fact.'],
                            'answer' => 'benar',
                            'code'   => 'INF-FOP',
                            'dim'    => 'D1',
                            'sub'    => 'D1b',
                            'akm'    => 'L3',
                            'pirls'  => 'evaluate',
                            'bloom'  => 'C2',
                            'skill'  => 'membedakan_fakta_pendapat',
                            'review' => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'    => 'mgl-3-b06',
                            'type'   => 'verdict_card',
                            'prompt' => [
                                'Tentang jalur mendaki Gunung Tidar, unggahan akun resmi lebih dapat dipercaya daripada komentar Raffi.',
                                'On the path for climbing Mount Tidar, the official account\'s post is more trustworthy than Raffi\'s comment.',
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
                    ],
                ],
            ],
            'anchor'      => [
                'passage' => [
                    'title'  => ['', ''],
                    'body'   => [
                        ">> 🔍 asal tari Topeng Ireng\n\n>> [Iklan] Sewa Kostum Tari Murah! · kostum-tari.example\nKostum Topeng Ireng lengkap mulai Rp50.000. Pesan sekarang!\n\n>> Catatan Jalan-jalan Rani · rani-jalan.example\nAku nonton Topeng Ireng kemarin, seru banget! Katanya tarian ini dari Bali, ya?\n\n>> Ensiklopedia Budaya Jawa Tengah · budaya-jateng.example\nTopeng Ireng adalah tari rakyat yang tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur, Magelang.",
                        ">> 🔍 where the Topeng Ireng dance comes from\n\n>> [Ad] Cheap Dance Costume Hire! · kostum-tari.example\nFull Topeng Ireng costumes from Rp50,000. Order now!\n\n>> Rani's Travel Notes · rani-jalan.example\nI watched Topeng Ireng yesterday, it was so much fun! I heard this dance is from Bali, right?\n\n>> Central Java Culture Encyclopaedia · budaya-jateng.example\nTopeng Ireng is a folk dance that grew around the 1950s in Tuksongo Village, Borobudur District, Magelang.",
                    ],
                    'genre'  => 'hasil_pencarian',
                    'layout' => 'search',
                    'ref'    => 'GNFI (2024) (Topeng Ireng dari Desa Tuksongo, dicek di 05); hasil pencarian, iklan, dan situs rekaan',
                ],
                'items'   => [
                    [
                        'key'    => 'mgl-3-x01',
                        'type'   => 'verdict_card',
                        'prompt' => [
                            'Dari tiga hasil pencarian itu, hanya satu yang menjawab dengan jelas dari mana Topeng Ireng berasal.',
                            'Of the three search results, only one clearly answers where Topeng Ireng comes from.',
                        ],
                        'answer' => 'benar',
                        'code'   => 'INF-REL',
                        'dim'    => 'D1',
                        'sub'    => 'D1a',
                        'akm'    => 'L2',
                        'pirls'  => 'infer',
                        'bloom'  => 'C2',
                        'skill'  => 'gagasan_pokok',
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-3-x02',
                        'type'   => 'verdict_card',
                        'prompt' => [
                            'Hasil pertama paling dapat dipercaya karena letaknya paling atas.',
                            'The first result is the most trustworthy because it is at the very top.',
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
        'mgl-4' => [
            'engine'      => 'cari',
            'variant'     => 'cari_objek',
            'indicator'   => 'D3',
            'title'       => ['Cari Objek Magelang', 'Find the Objects: Magelang'],
            'instruction' => [
                'Baca petunjuknya dengan teliti, lalu ketuk benda yang tepat. Hati-hati, ada benda yang mirip!',
                'Read each clue carefully, then tap the right object. Careful, some objects look alike!',
            ],
            'description' => [
                'Di galeri budaya Magelang, benda-benda khas tercampur dengan benda yang mirip. Baca petunjuknya dengan teliti dan temukan empat benda yang dimaksud. Lalu, periksa sebuah unggahan tentang budaya Magelang.',
                'In Magelang\'s culture gallery, special objects are mixed with look-alikes. Read the clues carefully and find the four objects. Then check a post about Magelang\'s culture.',
            ],
            'scene'       => [
                'key'   => 'challenge.mgl-4.scene',
                'label' => 'Galeri Budaya Magelang',
                'note'  => 'galeri/pendapa budaya, meja pajang panjang di depan kiri dan kanan (permukaan ± 70 % tinggi gambar) dan meja kecil di belakangnya dekat lorong tengah (permukaan ± 56 %), stupa & Gunung Tidar di latar; benda ditata ulang 3 November 2026 supaya berjarak paling sedikit 24 dp',
            ],
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
            'round1'      => [
                'title' => ['Galeri Budaya Magelang', 'Magelang Culture Gallery'],
                'ref'   => 'UNESCO World Heritage Centre (No. 592); detikEdu (2022) Tari Topeng Ireng; Kompas Travel (2022) Desa Klipoh; detikFood (2025); Pemkab Ponorogo (Reog) untuk pengecoh dadak merak',
                'items' => [
                    [
                        'key'          => 'mgl-4-x01',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan bangunan kecil berbentuk lonceng yang berlubang-lubang. Di teras bundar Borobudur ada 72 bangunan seperti ini.',
                            'Find the small bell-shaped structure full of holes. Borobudur has 72 of these on its round terraces.',
                        ],
                        'x'            => 38.3,
                        'y'            => 45.33,
                        'w'            => 6,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'stupa berlubang',
                        'media'        => [
                            'key'   => 'challenge.item.mgl-4-a01',
                            'label' => 'Stupa berlubang',
                            'note'  => 'stupa kecil berlubang (miniatur)',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'mgl-4-x02',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan hiasan kepala dari bulu warna-warni yang dipakai penari tari rakyat dari Desa Tuksongo, Magelang.',
                            'Find the colourful feather headdress worn by dancers of the folk dance from Tuksongo Village, Magelang.',
                        ],
                        'x'            => 1,
                        'y'            => 58.44,
                        'w'            => 6.5,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'kuluk Topeng Ireng',
                        'media'        => [
                            'key'   => 'challenge.item.mgl-4-a02',
                            'label' => 'Kuluk Topeng Ireng',
                            'note'  => 'kuluk bulu warna-warni Topeng Ireng',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'mgl-4-x03',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan jajanan dari singkong yang dimasak, ditumbuk, lalu diberi gula. Jajanan ini membuat Magelang punya julukan khusus.',
                            'Find the snack made of cassava that is cooked, pounded, and sweetened. This snack gave Magelang a special nickname. Diterjemahkan ulang: bank lama steamed; 08 kini "dimasak" (cek fakta MGL-35 di [25](25_CEK_FAKTA_BUDAYA.md): getuk dikukus atau direbus).',
                        ],
                        'x'            => 55.7,
                        'y'            => 45.33,
                        'w'            => 6,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'getuk',
                        'media'        => [
                            'key'   => 'challenge.item.mgl-4-a03',
                            'label' => 'Getuk',
                            'note'  => 'getuk tiga warna di atas daun pisang',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'          => 'mgl-4-x04',
                        'type'         => 'find_object',
                        'prompt'       => [
                            'Temukan benda dari tanah liat yang dibentuk lalu dibakar, buatan warga Dusun Klipoh.',
                            'Find the object made of clay that is shaped and then fired, made by the people of Klipoh Hamlet.',
                        ],
                        'x'            => 82.5,
                        'y'            => 58.44,
                        'w'            => 6.5,
                        'dim'          => 'PBK',
                        'bloom'        => 'C2',
                        'answer_label' => 'kendil gerabah',
                        'media'        => [
                            'key'   => 'challenge.item.mgl-4-a04',
                            'label' => 'Kendil gerabah',
                            'note'  => 'kendil gerabah cokelat kemerahan',
                        ],
                        'review'       => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-4-x05',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 10.5,
                        'y'      => 58.44,
                        'w'      => 6.5,
                        'prompt' => ['Kuda kepang bambu (pengecoh)', 'Bamboo hobby horse (decoy)'],
                        'wrong'  => [
                            'Ini kuda kepang dari Temanggung, wilayah yang sudah kamu jelajahi. Baca lagi petunjuknya.',
                            'This is a kuda kepang from Temanggung, the region you have already explored. Read the clue again.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.mgl-4-a05',
                            'label' => 'Kuda kepang bambu',
                            'note'  => 'kuda kepang anyaman bambu',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-4-x06',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 64.5,
                        'y'      => 45.33,
                        'w'      => 6,
                        'prompt' => ['Manisan carica dalam stoples (pengecoh)', 'Sweet carica in a jar (decoy)'],
                        'wrong'  => ['Ini manisan carica, oleh-oleh khas Wonosobo.', 'This is sweet carica in syrup, a souvenir from Wonosobo.'],
                        'media'  => [
                            'key'   => 'challenge.item.mgl-4-a06',
                            'label' => 'Manisan carica dalam stoples',
                            'note'  => 'stoples manisan carica',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-4-x07',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 29.5,
                        'y'      => 45.33,
                        'w'      => 6,
                        'prompt' => ['Dadak merak Reog (pengecoh)', 'Reog dadak merak (decoy)'],
                        'wrong'  => [
                            'Ini dadak merak dari Reog Ponorogo, Jawa Timur. Bulunya bulu merak, bukan kuluk penari dari Magelang.',
                            'This is the dadak merak from Reog Ponorogo in East Java. It is made of peacock feathers, not the headdress of the Magelang dancers.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.mgl-4-a07',
                            'label' => 'Dadak merak Reog',
                            'note'  => 'dadak merak Reog Ponorogo',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-4-x08',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 73,
                        'y'      => 58.44,
                        'w'      => 6.5,
                        'prompt' => ['Angklung (pengecoh)', 'Angklung (decoy)'],
                        'wrong'  => [
                            'Ini angklung dari Jawa Barat — bukan benda yang dimaksud petunjuk.',
                            'This is an angklung from West Java — not the object in the clue.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.mgl-4-a08',
                            'label' => 'Angklung',
                            'note'  => 'angklung bambu',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-4-x09',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 20,
                        'y'      => 58.44,
                        'w'      => 6.5,
                        'prompt' => ['Bakpia (pengecoh)', 'Bakpia (decoy)'],
                        'wrong'  => [
                            'Ini bakpia, jajanan dari Yogyakarta. Getuk dibuat dari singkong.',
                            'This is bakpia, a snack from Yogyakarta. Getuk is made from cassava.',
                        ],
                        'media'  => [
                            'key'   => 'challenge.item.mgl-4-a09',
                            'label' => 'Bakpia',
                            'note'  => 'beberapa bakpia',
                        ],
                        'review' => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'    => 'mgl-4-x10',
                        'type'   => 'find_object',
                        'decoy'  => true,
                        'code'   => 'INF-BUD',
                        'x'      => 92,
                        'y'      => 58.44,
                        'w'      => 6.5,
                        'prompt' => ['Panci logam (pengecoh)', 'Metal pot (decoy)'],
                        'wrong'  => ['Ini panci dari logam, bukan gerabah dari tanah liat.', 'This is a metal pot, not pottery made of clay.'],
                        'media'  => [
                            'key'   => 'challenge.item.mgl-4-a10',
                            'label' => 'Panci logam',
                            'note'  => 'panci logam',
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
                        'title' => ['Unggahan video Topeng Ireng', 'A Topeng Ireng video post'],
                        'media' => [
                            'key'   => 'challenge.item.mgl-1-02',
                            'label' => 'Penari Topeng Ireng',
                        ],
                        'ref'   => 'Topeng Ireng dari Tuksongo: GNFI (2024), dicek di 05. Dusun Klipoh dekat Borobudur dan hasil gerabahnya: Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur. Akun, sanggar, dan rumah gerabah fiktif',
                    ],
                    'parts' => [
                        [
                            'key'   => 'C1',
                            'kind'  => 'account',
                            'label' => ['Nama akun pengunggah', 'Uploader\'s account name'],
                            'text'  => ['@vlog.arka', '@vlog.arka'],
                            'x'     => 3,
                            'y'     => 3,
                            'w'     => 38,
                            'h'     => 8,
                        ],
                        [
                            'key'   => 'C2',
                            'kind'  => 'credit',
                            'label' => ['Tulisan kecil di pojok video', 'Small text in the corner of the video'],
                            'text'  => ['Video: Sanggar Cahya Kuluk', 'Video: Sanggar Cahya Kuluk'],
                            'x'     => 5,
                            'y'     => 42,
                            'w'     => 26,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'C3',
                            'kind'  => 'caption',
                            'label' => ['Keterangan unggahan', 'Caption'],
                            'text'  => ['Tari Topeng Ireng, tarian asli dari Temanggung! 🔥', 'Topeng Ireng dance, an original dance from Temanggung! 🔥'],
                            'x'     => 3,
                            'y'     => 55,
                            'w'     => 38,
                            'h'     => 12,
                        ],
                        [
                            'key'   => 'C4',
                            'kind'  => 'board',
                            'label' => ['Kartu informasi', 'Info card'],
                            'text'  => [
                                'Topeng Ireng: tari rakyat yang tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur, Magelang.',
                                'Topeng Ireng: a folk dance that grew around the 1950s in Tuksongo Village, Borobudur District, Magelang.',
                            ],
                            'x'     => 46,
                            'y'     => 3,
                            'w'     => 51,
                            'h'     => 22,
                        ],
                        [
                            'key'   => 'C5',
                            'kind'  => 'meta',
                            'label' => ['Jumlah suka', 'Number of likes'],
                            'text'  => ['540 suka', '540 likes'],
                            'x'     => 3,
                            'y'     => 73,
                            'w'     => 14,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'C6',
                            'kind'  => 'comment',
                            'label' => ['Komentar 1', 'Comment 1'],
                            'text'  => [
                                'Salsa: Bukan dari Temanggung, Kak. Di kartu info tertulis dari Tuksongo, Magelang 🙏',
                                'Salsa: It\'s not from Temanggung. The info card says it\'s from Tuksongo, Magelang 🙏',
                            ],
                            'x'     => 46,
                            'y'     => 30,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'C7',
                            'kind'  => 'comment',
                            'label' => ['Komentar 2', 'Comment 2'],
                            'text'  => ['Bayu: Haha, kostumnya kayak ayam jago 🐔', 'Bayu: Haha, the costume looks like a rooster 🐔'],
                            'x'     => 46,
                            'y'     => 44,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'C8',
                            'kind'  => 'comment',
                            'label' => ['Komentar 3', 'Comment 3'],
                            'text'  => ['Lina: Keren! Temanggung memang hebat 👍', 'Lina: Cool! Temanggung really is great 👍'],
                            'x'     => 46,
                            'y'     => 58,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'C9',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Unduh', 'Download'],
                            'x'     => 3,
                            'y'     => 87,
                            'w'     => 12,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'C10',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Kirim pesan ke sanggar', 'Send a message to the studio'],
                            'x'     => 18,
                            'y'     => 87,
                            'w'     => 25,
                            'h'     => 9,
                        ],
                    ],
                    'items' => [
                        [
                            'key'       => 'mgl-4-a01',
                            'type'      => 'find_part',
                            'prompt'    => ['Ketuk tulisan yang menunjukkan siapa pembuat video ini.', 'Tap the text that shows who made this video.'],
                            'answer'    => 'C2',
                            'clue_type' => 'ATR',
                            'main'      => [
                                'C1' => 'BUD-ATR',
                                'C3' => 'BUD-ATR',
                                'C4' => 'BUD-ATR',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'mgl-4-a02',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Bandingkan keterangan video dengan kartu informasi. Ketuk komentar yang membetulkan keterangan itu dengan benar dan sopan.',
                                'Compare the video caption with the info card. Tap the comment that corrects the caption accurately and politely.',
                            ],
                            'answer'    => 'C6',
                            'clue_type' => 'REP',
                            'main'      => [
                                'C8' => 'BUD-REP',
                                'C3' => 'BUD-REP',
                                'C7' => 'BUD-ETK',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
                            'bloom'     => 'C4',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'mgl-4-a03',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Kamu ingin memakai video ini untuk tugas kelompok. Ketuk langkah pertama yang tepat.',
                                'You want to use this video for a group assignment. Tap the right first step.',
                            ],
                            'answer'    => 'C10',
                            'clue_type' => 'IZN',
                            'main'      => [
                                'C9' => 'BUD-IZN',
                                'C1' => 'BUD-IZN',
                                'C5' => 'BUD-IZN',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
                            'bloom'     => 'C3',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'mgl-4-a04',
                            'type'      => 'find_part',
                            'prompt'    => ['Ketuk komentar yang kurang menghargai para penari.', 'Tap the comment that does not show respect for the dancers.'],
                            'answer'    => 'C7',
                            'clue_type' => 'ETK',
                            'main'      => [
                                'C8' => 'BUD-ETK',
                                'C6' => 'BUD-ETK',
                                'C3' => 'BUD-REP',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
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
                        'title' => ['Unggahan foto gerabah Klipoh', 'A Klipoh pottery photo post'],
                        'media' => [
                            'key'   => 'challenge.item.mgl-1-h01',
                            'label' => 'Gerabah Klipoh',
                        ],
                        'ref'   => 'Topeng Ireng dari Tuksongo: GNFI (2024), dicek di 05. Dusun Klipoh dekat Borobudur dan hasil gerabahnya: Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur. Akun, sanggar, dan rumah gerabah fiktif',
                    ],
                    'parts' => [
                        [
                            'key'   => 'D1',
                            'kind'  => 'account',
                            'label' => ['Nama akun pengunggah', 'Uploader\'s account name'],
                            'text'  => ['@dapur.nayla', '@dapur.nayla'],
                            'x'     => 3,
                            'y'     => 3,
                            'w'     => 38,
                            'h'     => 8,
                        ],
                        [
                            'key'   => 'D2',
                            'kind'  => 'credit',
                            'label' => ['Tulisan kecil di pojok foto', 'Small text in the corner of the photo'],
                            'text'  => ['Foto: Rumah Gerabah Sekar Lempung', 'Photo: Rumah Gerabah Sekar Lempung'],
                            'x'     => 5,
                            'y'     => 42,
                            'w'     => 26,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'D3',
                            'kind'  => 'caption',
                            'label' => ['Keterangan unggahan', 'Caption'],
                            'text'  => ['Gerabah cantik dari Dusun Klipoh, Wonosobo! 🏺', 'Beautiful pottery from Klipoh Hamlet, Wonosobo! 🏺'],
                            'x'     => 3,
                            'y'     => 55,
                            'w'     => 38,
                            'h'     => 12,
                        ],
                        [
                            'key'   => 'D4',
                            'kind'  => 'board',
                            'label' => ['Kartu informasi', 'Info card'],
                            'text'  => [
                                'Dusun Klipoh berada sekitar 3 km dari Candi Borobudur, Magelang. Warganya membuat kendil, cobek, dan kuali dari tanah liat.',
                                'Klipoh Hamlet is about 3 km from Borobudur Temple, Magelang. The people there make kendil pots, cobek mortars, and kuali pans from clay.',
                            ],
                            'x'     => 46,
                            'y'     => 3,
                            'w'     => 51,
                            'h'     => 22,
                        ],
                        [
                            'key'   => 'D5',
                            'kind'  => 'meta',
                            'label' => ['Jumlah suka', 'Number of likes'],
                            'text'  => ['610 suka', '610 likes'],
                            'x'     => 3,
                            'y'     => 73,
                            'w'     => 14,
                            'h'     => 7,
                        ],
                        [
                            'key'   => 'D6',
                            'kind'  => 'comment',
                            'label' => ['Komentar 1', 'Comment 1'],
                            'text'  => [
                                'Tegar: Klipoh itu di Magelang, Kak, dekat Borobudur. Ada di kartu info 🙏',
                                'Tegar: Klipoh is in Magelang, near Borobudur. It\'s on the info card 🙏',
                            ],
                            'x'     => 46,
                            'y'     => 30,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'D7',
                            'kind'  => 'comment',
                            'label' => ['Komentar 2', 'Comment 2'],
                            'text'  => ['Rani: Wkwk kendil jadul, siapa yang masih pakai 😂', 'Rani: LOL, old-fashioned kendil, who still uses those 😂'],
                            'x'     => 46,
                            'y'     => 44,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'D8',
                            'kind'  => 'comment',
                            'label' => ['Komentar 3', 'Comment 3'],
                            'text'  => ['Ayu: Cantik! Wonosobo memang keren 👍', 'Ayu: Pretty! Wonosobo really is cool 👍'],
                            'x'     => 46,
                            'y'     => 58,
                            'w'     => 51,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'D9',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Unduh', 'Download'],
                            'x'     => 3,
                            'y'     => 87,
                            'w'     => 12,
                            'h'     => 9,
                        ],
                        [
                            'key'   => 'D10',
                            'kind'  => 'button',
                            'label' => ['Tombol', 'Button'],
                            'text'  => ['Kirim pesan ke rumah gerabah', 'Send a message to the pottery workshop'],
                            'x'     => 18,
                            'y'     => 87,
                            'w'     => 25,
                            'h'     => 9,
                        ],
                    ],
                    'items' => [
                        [
                            'key'       => 'mgl-4-b01',
                            'type'      => 'find_part',
                            'prompt'    => ['Ketuk tulisan yang menunjukkan siapa pembuat foto ini.', 'Tap the text that shows who made this photo.'],
                            'answer'    => 'D2',
                            'clue_type' => 'ATR',
                            'main'      => [
                                'D1' => 'BUD-ATR',
                                'D3' => 'BUD-ATR',
                                'D4' => 'BUD-ATR',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
                            'bloom'     => 'C2',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'mgl-4-b02',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Bandingkan keterangan foto dengan kartu informasi. Ketuk komentar yang membetulkan keterangan itu dengan benar dan sopan.',
                                'Compare the photo caption with the info card. Tap the comment that corrects the caption accurately and politely.',
                            ],
                            'answer'    => 'D6',
                            'clue_type' => 'REP',
                            'main'      => [
                                'D8' => 'BUD-REP',
                                'D3' => 'BUD-REP',
                                'D7' => 'BUD-ETK',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
                            'bloom'     => 'C4',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'mgl-4-b03',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Kamu ingin memakai foto ini untuk tugas kelompok. Ketuk langkah pertama yang tepat.',
                                'You want to use this photo for a group assignment. Tap the right first step.',
                            ],
                            'answer'    => 'D10',
                            'clue_type' => 'IZN',
                            'main'      => [
                                'D9' => 'BUD-IZN',
                                'D1' => 'BUD-IZN',
                                'D5' => 'BUD-IZN',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
                            'bloom'     => 'C3',
                            'review'    => [
                                'status' => 'needs_verification',
                                'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                            ],
                        ],
                        [
                            'key'       => 'mgl-4-b04',
                            'type'      => 'find_part',
                            'prompt'    => [
                                'Ketuk komentar yang kurang menghargai kerajinan ini.',
                                'Tap the comment that does not show respect for this craft.',
                            ],
                            'answer'    => 'D7',
                            'clue_type' => 'ETK',
                            'main'      => [
                                'D8' => 'BUD-ETK',
                                'D6' => 'BUD-ETK',
                                'D3' => 'BUD-REP',
                            ],
                            'dim'       => 'D3',
                            'akm'       => 'L2',
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
        'mgl-5' => [
            'engine'      => 'skenario',
            'variant'     => 'skenario_digital',
            'indicator'   => 'D2',
            'title'       => ['Skenario Digital Magelang', 'Digital Scenarios: Magelang'],
            'instruction' => [
                'Bacalah ceritanya. Pilih apa yang akan kamu lakukan, lalu pilih alasanmu.',
                'Read the story. Choose what you would do, then choose your reason.',
            ],
            'description' => [
                'Ponsel di rumah baca terus berbunyi. Serpihan kelima Magelang tersembunyi di balik pesan-pesan itu. Bayangkan setiap cerita terjadi padamu.',
                'The phones at the reading house keep chiming. Magelang\'s fifth shard is hidden behind those messages. Imagine each story is happening to you.',
            ],
            'scenarios'   => [
                'a' => [
                    [
                        'key'         => 'mgl-5-a01',
                        'topic'       => 'PRI',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu mencoba kuis "Kamu Mirip Tokoh Wayang Siapa?" di sebuah aplikasi. Sebelum hasilnya keluar, aplikasi meminta tanggal lahir, nama lengkap ibumu, dan nama sekolahmu.',
                            'You try a quiz called "Which Wayang Character Are You?" in an app. Before showing the result, the app asks for your date of birth, your mum\'s full name, and the name of your school.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Isi semuanya supaya hasil kuisnya cepat keluar', 'Fill in everything so the quiz result comes out fast'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Tanggal lahir dan nama ibu sering dipakai untuk membuka akun. Data itu bisa disalahgunakan.',
                                    'Dates of birth and mothers\' names are often used to open accounts. That data could be misused.',
                                ],
                            ],
                            [
                                'text'     => ['Isi tanggal lahir saja, yang lain dikosongkan', 'Fill in only your date of birth and leave the rest empty'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Tanggal lahir juga data pribadi. Kuis untuk hiburan tidak perlu data seperti itu.',
                                    'Your date of birth is personal data too. A quiz for fun doesn\'t need data like that.',
                                ],
                            ],
                            [
                                'text'     => ['Berhenti, lalu tanya orang tua apakah aman', 'Stop, then ask your parents whether it\'s safe'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Kuis yang meminta data pribadi patut dicurigai, dan orang tua bisa membantu memeriksanya.',
                                    'Good choice. A quiz that asks for personal data should make us suspicious, and your parents can help check it.',
                                ],
                            ],
                            [
                                'text'     => ['Berhenti main tanpa cerita ke siapa pun', 'Stop playing without telling anyone'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Datamu aman. Cerita ke orang tua akan membantu kalau aplikasi itu nanti muncul lagi.',
                                    'Your data is safe. Telling your parents would help if the app shows up again.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Data itu bisa dipakai orang untuk membobol akun', 'People could use that data to break into accounts'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Ibu melarangku bermain kuis di aplikasi apa pun', 'Mum doesn\'t let me play quizzes in any app'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-temanku sudah membagikan hasil kuisnya', 'My friends have already shared their quiz results'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku memang tidak terlalu suka cerita wayang', 'I don\'t really like wayang stories anyway'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Kuis ini meminta tanggal lahir dan nama lengkap ibumu, padahal data itu tidak perlu untuk sebuah kuis. Dengan berhenti dan bertanya ke orang tua, datamu tetap aman.',
                            'This quiz asks for your date of birth and your mum\'s full name, even though a quiz does not need that data. By stopping and asking your parents, your data stays safe.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'mgl-5-a02',
                        'topic'       => 'SAN',
                        'steps'       => 1,
                        'situation'   => [
                            'Sahabatmu, Laras, berkata, "Pinjam akun game-mu, dong. Kasih tahu kata sandinya, aku cuma mau lihat-lihat."',
                            'Your best friend, Laras, says, "Lend me your game account, please. Tell me the password, I just want to look around."',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Beri tahu kata sandinya karena Laras sahabatmu', 'Tell Laras the password, because Laras is your best friend'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Walaupun sahabat, kata sandi tetap untuk pemilik akun. Kata sandi yang sudah dibagikan bisa tersebar tanpa sengaja.',
                                    'Even with a best friend, a password is only for the account owner. A password that has been shared can spread by accident.',
                                ],
                            ],
                            [
                                'text'     => ['Beri tahu sekarang, nanti kata sandinya diganti', 'Tell Laras now, and change the password later'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Selama belum diganti, akunmu bisa dipakai orang lain. Lebih aman kalau tidak dibagikan sama sekali.',
                                    'Until it is changed, someone else can use your account. It\'s safer not to share it at all.',
                                ],
                            ],
                            [
                                'text'     => ['Tolak dengan ramah, lalu ajak main bersama', 'Say no kindly, then ask Laras to play together'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Akunmu aman dan persahabatan kalian tetap terjaga.',
                                    'Good choice. Your account is safe and your friendship stays strong.',
                                ],
                            ],
                            [
                                'text'     => ['Tolak tanpa menjelaskan apa pun ke Laras', 'Say no without explaining anything to Laras'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Menolak memberi kata sandi itu benar. Menjelaskan alasannya dengan ramah membuat Laras mengerti dan tidak salah paham.',
                                    'Not giving your password is the right thing to do. Explaining why in a kind way helps Laras understand and not take it the wrong way.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Kata sandi bisa tersebar walau tidak sengaja', 'A password can spread even by accident'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kata guru, kata sandi itu harus dirahasiakan', 'My teacher says passwords must be kept secret'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Nanti Laras marah kalau tidak kuberi tahu', 'Laras will be angry if I don\'t say'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Level akunku memang masih rendah sekali', 'My account level is still very low anyway'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Kata sandi hanya untuk pemilik akun, walaupun yang meminta sahabatmu sendiri. Dengan menolak dengan ramah lalu mengajak main bersama, akunmu aman dan kalian tetap bersahabat.',
                            'A password is only for the account owner, even when your own best friend asks. By saying no kindly and asking Laras to play together, your account is safe and you stay friends.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'mgl-5-a03',
                        'topic'       => 'IZN',
                        'steps'       => 1,
                        'situation'   => [
                            'Saat lomba tari di sekolah, kamu memotret Dimas yang terpeleset dan jatuh terduduk di panggung. Fotonya lucu. Kamu ingin mengirimnya ke grup kelas.',
                            'At a school dance competition, you take a photo of Dimas slipping and landing sitting down on the stage. The photo is funny. You want to send it to the class group.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Kirim ke grup kelas supaya semua ikut tertawa', 'Send it to the class group so everyone can laugh'],
                                'score'    => 0,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Foto itu tentang Dimas. Membagikannya tanpa izin bisa membuatnya malu.',
                                    'The photo is about Dimas. Sharing it without permission could embarrass Dimas.',
                                ],
                            ],
                            [
                                'text'     => ['Tunjukkan ke teman-teman sambil tertawa', 'Show it to your friends while laughing'],
                                'score'    => 0,
                                'code'     => 'DIG-ETK',
                                'feedback' => [
                                    'Menertawakan teman yang jatuh bisa menyakiti hatinya, walaupun fotonya tidak dikirim.',
                                    'Laughing at a friend who fell can hurt their feelings, even if the photo is not sent.',
                                ],
                            ],
                            [
                                'text'     => ['Tanya Dimas dulu, boleh dibagikan atau tidak', 'Ask Dimas first whether it can be shared'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Foto orang lain hanya dibagikan kalau orangnya setuju.',
                                    'Good choice. Photos of other people are only shared if they agree.',
                                ],
                            ],
                            [
                                'text'     => ['Simpan fotonya di HP tanpa bertanya ke Dimas', 'Keep the photo on your phone without asking Dimas'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak membagikannya, bagus. Lebih baik lagi kalau kamu bertanya ke Dimas, karena itu fotonya.',
                                    'You didn\'t share it, well done. It would be even better to ask Dimas, because it\'s a photo of Dimas.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Dimas mungkin malu kalau fotonya disebar', 'Dimas might be embarrassed if the photo is shared'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Di sekolah ada aturan tidak boleh memotret', 'There\'s a school rule against taking photos'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Teman-teman pasti suka melihat foto lucu', 'My friends will surely like seeing a funny photo'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Memori HP-ku sebentar lagi hampir penuh', 'My phone\'s memory is almost full'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Dimas mungkin malu kalau foto itu tersebar. Dengan bertanya dulu ke Dimas, kamu hanya membagikan fotonya kalau ia setuju.',
                            'Dimas might be embarrassed if the photo spreads. By asking Dimas first, you only share the photo if Dimas agrees.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'mgl-5-a04',
                        'topic'       => 'ASG',
                        'steps'       => 1,
                        'situation'   => [
                            'Di grup kelas, seorang teman meneruskan pesan: "Info dari teman ibuku: tiket masuk Candi Borobudur gratis untuk anak SD selama bulan ini! Sebarkan!" Pesan itu tidak menyebut lembaga atau sumber resmi.',
                            'In the class group, a friend forwards a message: "Info from my mum\'s friend: entry tickets to Borobudur Temple are free for primary school children all this month! Share it!" The message does not name any organisation or official source.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Teruskan ke grup keluarga supaya semua kebagian', 'Forward it to the family group so everyone gets the chance'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Kabar ini belum jelas sumbernya. Kalau ternyata tidak benar, banyak keluarga bisa kecewa.',
                                    'This news has no clear source. If it turns out to be untrue, many families could be disappointed.',
                                ],
                            ],
                            [
                                'text'     => ['Ajak teman-teman langsung berangkat besok', 'Ask your friends to go there straight away tomorrow'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Sebaiknya kabar dicek dulu sebelum dipakai untuk membuat rencana.',
                                    'It\'s better to check the news first before using it to make plans.',
                                ],
                            ],
                            [
                                'text'     => ['Cek dulu di situs resmi candi bersama orang tua', 'First check the temple\'s official website with your parents'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Sumber resmi bisa memastikan benar atau tidaknya kabar itu.',
                                    'Good choice. An official source can confirm whether the news is true.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak meneruskan dan tidak menanggapi apa pun', 'Don\'t forward it and don\'t respond at all'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak ikut menyebarkan, bagus. Mengecek ke sumber resmi akan membantu teman-temanmu juga.',
                                    'You didn\'t help spread it, well done. Checking an official source would help your friends too.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Kabar tanpa sumber jelas belum tentu benar', 'News with no clear source isn\'t always true'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Ayah melarangku membuka pesan di grup kelas', 'Dad doesn\'t let me open messages in the class group'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Biar aku dianggap tahu banyak informasi', 'So people think I know lots of things'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku sudah pernah berkunjung ke Borobudur', 'I\'ve already been to Borobudur'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Pesan itu tidak menyebut sumber resmi. Dengan mengecek situs resmi candi bersama orang tua, kamu tahu benar atau tidaknya sebelum ikut menyebarkan.',
                            'The message does not name an official source. By checking the temple\'s official website with your parents, you find out whether it is true before passing it on.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                ],
                'b' => [
                    [
                        'key'         => 'mgl-5-b01',
                        'topic'       => 'PRI',
                        'steps'       => 1,
                        'situation'   => [
                            'Ada lomba video "Jajanan Khas Magelang". Peserta diminta menyebut nama lengkap dan alamat rumah di awal video. Semua video akan diunggah ke akun yang bisa dilihat siapa saja.',
                            'There is a video competition called "Special Snacks of Magelang". Entrants must say their full name and home address at the start of the video. All the videos will be posted on an account that anyone can see.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Sebut nama lengkap dan alamat rumah sesuai aturan', 'Say your full name and home address, as the rules say'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Video di akun umum bisa ditonton siapa saja. Alamat rumah sebaiknya tidak disebutkan.',
                                    'A video on a public account can be watched by anyone. It\'s best not to say your home address.',
                                ],
                            ],
                            [
                                'text'     => ['Sebut nama lengkap dan nama sekolah saja', 'Say only your full name and the name of your school'],
                                'score'    => 0,
                                'code'     => 'DIG-PRI',
                                'feedback' => [
                                    'Nama lengkap dan sekolah juga bisa dipakai orang asing untuk menemukanmu.',
                                    'Your full name and school can also be used by strangers to find you.',
                                ],
                            ],
                            [
                                'text'     => ['Tanya orang tua; sebut nama panggilan saja', 'Ask your parents; say only your nickname'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Kamu tetap bisa ikut lomba tanpa membuka data pribadi.',
                                    'Good choice. You can still enter the competition without giving away personal data.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak jadi ikut tanpa cerita ke siapa pun', 'Decide not to enter, without telling anyone'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Datamu aman. Orang tua mungkin bisa membantumu ikut lomba dengan cara yang aman.',
                                    'Your data is safe. Your parents might be able to help you enter in a safe way.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Orang asing bisa tahu alamat rumahku', 'Strangers could find out my home address'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Pokoknya tidak boleh ikut lomba di internet', 'We\'re just not allowed to enter online competitions'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Peserta lain juga menyebut alamat rumahnya', 'Other entrants say their home address too'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku lebih suka getuk daripada jajanan lain', 'I like getuk more than other snacks'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Video itu bisa dilihat siapa saja, jadi nama lengkap dan alamat rumahmu bisa diketahui orang asing. Dengan bertanya ke orang tua dan menyebut nama panggilan saja, kamu tetap bisa ikut lomba dengan aman.',
                            'Anyone can watch that video, so strangers could learn your full name and home address. By asking your parents and saying only your nickname, you can still enter the competition safely.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'mgl-5-b02',
                        'topic'       => 'SAN',
                        'steps'       => 1,
                        'situation'   => [
                            'Teman sekelompokmu, Arif, berkata, "Kasih tahu kata sandi akun belajarmu, ya. Biar aku yang mengerjakan tugas kelompok dari rumahku."',
                            'Arif, a friend in your group, says, "Tell me the password for your learning account. Then I can do our group assignment from my house."',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Beri tahu kata sandinya supaya tugas cepat selesai', 'Tell Arif the password so the assignment gets done fast'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Akun belajarmu berisi data dan nilaimu. Kata sandi tetap untuk pemilik akun.',
                                    'Your learning account holds your data and marks. A password is only for the account owner.',
                                ],
                            ],
                            [
                                'text'     => ['Beri tahu sekarang, nanti kata sandinya diganti', 'Tell Arif now, and change the password later'],
                                'score'    => 0,
                                'code'     => 'DIG-SAN',
                                'feedback' => [
                                    'Selama belum diganti, akunmu bisa dipakai orang lain. Lebih aman tidak dibagikan.',
                                    'Until it is changed, someone else can use your account. It\'s safer not to share it.',
                                ],
                            ],
                            [
                                'text'     => ['Tolak dengan ramah, lalu ajak kerja bersama', 'Say no kindly, then ask Arif to work together'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Akunmu aman dan tugas kelompok tetap jalan.',
                                    'Good choice. Your account is safe and the group assignment keeps going.',
                                ],
                            ],
                            [
                                'text'     => ['Tolak tanpa menjelaskan apa pun ke Arif', 'Say no without explaining anything to Arif'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Menolak memberi kata sandi itu benar. Menjelaskan alasannya dengan ramah membuat Arif mengerti dan tugas tetap jalan.',
                                    'Not giving your password is the right thing to do. Explaining why in a kind way helps Arif understand, and the assignment keeps going.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Kata sandi bisa tersebar walau tidak sengaja', 'A password can spread even by accident'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Kata guru, kata sandi itu harus dirahasiakan', 'My teacher says passwords must be kept secret'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Nanti Arif tidak mau berteman lagi denganku', 'Arif won\'t want to be my friend anymore'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Tugas kelompok kali ini memang sulit', 'This group assignment is really hard'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Kata sandi hanya untuk pemilik akun, walaupun yang meminta teman sekelompok. Dengan menolak dengan ramah dan mengajak kerja bersama, akunmu aman dan tugas tetap selesai.',
                            'A password is only for the account owner, even when a groupmate asks. By saying no kindly and asking Arif to work together, your account is safe and the assignment still gets done.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'mgl-5-b03',
                        'topic'       => 'IZN',
                        'steps'       => 1,
                        'situation'   => [
                            'Kamu memotret teman-teman saat latihan Topeng Ireng. Di foto itu, Sekar belum memakai kuluk dan rambutnya berantakan. Kamu ingin mengunggah foto itu ke akun kelas.',
                            'You take a photo of your friends at Topeng Ireng practice. In the photo, Sekar is not wearing the kuluk yet and Sekar\'s hair is messy. You want to post the photo on the class account.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Unggah ke akun kelas karena fotonya ramai', 'Post it on the class account because the photo is lively'],
                                'score'    => 0,
                                'code'     => 'DIG-IZN',
                                'feedback' => [
                                    'Sekar ada di foto itu. Sebelum diunggah, ia perlu ditanya dulu.',
                                    'Sekar is in that photo. Sekar needs to be asked before it is posted.',
                                ],
                            ],
                            [
                                'text'     => ['Kirim ke beberapa teman sambil mengejek rambut Sekar', 'Send it to a few friends and make fun of Sekar\'s hair'],
                                'score'    => 0,
                                'code'     => 'DIG-ETK',
                                'feedback' => ['Mengejek penampilan teman bisa menyakiti hatinya.', 'Making fun of how a friend looks can hurt their feelings.'],
                            ],
                            [
                                'text'     => ['Tanya Sekar dulu, atau pilih foto yang lain', 'Ask Sekar first, or choose another photo'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Setiap orang di foto berhak setuju atau tidak.',
                                    'Good choice. Everyone in a photo has the right to say yes or no.',
                                ],
                            ],
                            [
                                'text'     => ['Simpan fotonya di HP tanpa bertanya ke Sekar', 'Keep the photo on your phone without asking Sekar'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak membagikannya, bagus. Bertanya ke Sekar akan lebih baik.',
                                    'You didn\'t share it, well done. Asking Sekar would be even better.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Sekar mungkin malu kalau fotonya diunggah', 'Sekar might be embarrassed if the photo is posted'],
                                'code' => 'R-ETIK',
                            ],
                            [
                                'text' => ['Di sekolah ada aturan tidak boleh mengunggah', 'There\'s a school rule against posting photos'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Akun kelas jadi ramai kalau ada foto lucu', 'The class account gets busy when there are funny photos'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Kamera HP-ku baru dan hasilnya bagus', 'My phone camera is new and takes good pictures'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Sekar mungkin tidak ingin fotonya dilihat banyak orang dalam keadaan begitu. Dengan bertanya ke Sekar dulu atau memilih foto lain, kamu menghargai perasaannya.',
                            'Sekar might not want lots of people to see the photo like that. By asking Sekar first or choosing another photo, you respect Sekar\'s feelings.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
                        'bloom'       => 'C3',
                        'review'      => [
                            'status' => 'needs_verification',
                            'note'   => 'Bank baru dari 08 (revisi 10, perubahan sistem no. 1, 3, 4, 8, 17, 20): menunggu validasi ahli. Teks Inggris: draf AI dari 36, terjemahan belum ada yang diperiksa pengembang.',
                        ],
                    ],
                    [
                        'key'         => 'mgl-5-b04',
                        'topic'       => 'ASG',
                        'steps'       => 1,
                        'situation'   => [
                            'Di grup kelas ada pesan: "Info dari teman pamanku: getuk yang dijual di pasar minggu ini bikin sakit perut! Jangan beli, sebarkan!" Pesan itu tidak menyebut sumber atau bukti.',
                            'A message appears in the class group: "Info from my uncle\'s friend: the getuk sold at the market this week gives you a stomach ache! Don\'t buy it, share this!" The message gives no source or proof.',
                        ],
                        'actions'     => [
                            [
                                'text'     => ['Teruskan ke grup keluarga supaya semua waspada', 'Forward it to the family group so everyone is careful'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Kabar tanpa bukti bisa merugikan para penjual getuk yang tidak bersalah.',
                                    'News without proof can harm innocent getuk sellers.',
                                ],
                            ],
                            [
                                'text'     => ['Unggah tulisan "Jangan beli getuk!" di media sosial', 'Post "Don\'t buy getuk!" on social media'],
                                'score'    => 0,
                                'code'     => 'DIG-HOX',
                                'feedback' => [
                                    'Sebelum menyebarkan peringatan, kabarnya perlu dicek dulu.',
                                    'Before spreading a warning, the news needs to be checked first.',
                                ],
                            ],
                            [
                                'text'     => ['Cek dulu di berita tepercaya bersama orang tua', 'First check trusted news with your parents'],
                                'score'    => 2,
                                'feedback' => [
                                    'Tepat. Mengecek ke sumber yang jelas mencegah kabar bohong menyebar.',
                                    'Good choice. Checking a clear source stops false news from spreading.',
                                ],
                            ],
                            [
                                'text'     => ['Tidak meneruskan dan tidak menanggapi apa pun', 'Don\'t forward it and don\'t respond at all'],
                                'score'    => 1,
                                'code'     => 'DIG-PSF',
                                'feedback' => [
                                    'Kamu tidak ikut menyebarkan, bagus. Mengecek dan memberi tahu teman akan lebih membantu.',
                                    'You didn\'t help spread it, well done. Checking and telling your friends would help more.',
                                ],
                            ],
                        ],
                        'reasons'     => [
                            [
                                'text' => ['Kabar tanpa bukti bisa merugikan penjual', 'News without proof can harm the sellers'],
                                'code' => 'R-RISK',
                            ],
                            [
                                'text' => ['Ibu melarangku membuka pesan di grup kelas', 'Mum doesn\'t let me open messages in the class group'],
                                'code' => 'R-ATUR',
                            ],
                            [
                                'text' => ['Biar aku yang pertama memberi tahu teman', 'So I\'m the first to tell my friends'],
                                'code' => 'R-SOS',
                            ],
                            [
                                'text' => ['Aku jarang sekali pergi ke pasar', 'I very rarely go to the market'],
                                'code' => 'R-ACAK',
                            ],
                        ],
                        'explanation' => [
                            'Pesan itu tidak menyebut sumber atau bukti. Dengan mengecek berita tepercaya bersama orang tua, kamu tidak ikut menyebarkan kabar yang belum pasti.',
                            'The message gives no source or proof. By checking trusted news with your parents, you avoid spreading news that isn\'t certain.',
                        ],
                        'dim'         => 'D2',
                        'akm'         => 'L2',
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
