<?php

/**
 * Isi bank soal & Pustaka — TEMANGGUNG (wilayah 1, mudah).
 * Fokus literasi: menemukan informasi yang TERSURAT dalam teks.
 *
 * Bentuk data dijelaskan di docs/bank-soal/README.md. Teks berpasangan
 * [Indonesia, English]. Kode butir (tmg-2-01, …) dan opsi (…-a … -d)
 * diberi otomatis oleh build-workbook.php sesuai urutan di berkas ini —
 * JANGAN menyisipkan butir di tengah setelah bank soal dipakai siswa;
 * tambahkan di akhir daftar.
 */

$ref = [
    'geo'     => 'Pemkab Temanggung (profil daerah); BPS Kab. Temanggung; data ketinggian gunung PVMBG',
    'tobacco' => 'Dinas Pertanian Kab. Temanggung; DJKI Kemenkumham (indikasi geografis); MPIG Kopi Java Arabika Sindoro Sumbing',
    'history' => 'Pemkab Temanggung (Sejarah Temanggung); Kompas.com (2023) Situs Liyangan; BPK Wilayah X Jateng',
    'jumprit' => 'Wikipedia: Umbul Jumprit; Mongabay (2018) Berkah Air dari Umbul Jumprit; Perhutani',
    'art'     => 'DPRD Jateng: Kuda Lumping, Kesenian Khas Temanggung; Pemprov Jateng: Nyadran Jaran Kepang',
    'parakan' => 'NU Online: KH Subchi Parakan, Kiai Bambu Runcing; Gatra: Parakan Kota Pusaka',
];

$checked = 'Fakta dicocokkan dengan sumber pada kolom reference_source (Sep 2026). Tinjau oleh guru/ahli, lalu ubah review_status menjadi verified.';

return [
    // ================================================================ BACAAN
    'passages' => [
        'tmg-teks-a' => [
            'title' => ['Tanah Subur di Kaki Dua Gunung', 'Fertile Land at the Foot of Two Mountains'],
            'body'  => [
                "Kabupaten Temanggung berada di Provinsi Jawa Tengah. Wilayahnya diapit dua gunung besar, yaitu Gunung Sindoro dan Gunung Sumbing. Gunung Sindoro tingginya 3.136 meter di atas permukaan laut, sedangkan Gunung Sumbing lebih tinggi, yaitu 3.371 meter. Karena bentuknya mirip, keduanya sering disebut gunung kembar.\n\nTanah di lereng kedua gunung itu subur karena berasal dari abu gunung api. Banyak warga bekerja sebagai petani. Mereka menanam tembakau, kopi, dan sayuran di lereng gunung yang sejuk.\n\nTemanggung terbagi menjadi 20 kecamatan. Setiap tanggal 10 November, warga memperingati hari jadi Kabupaten Temanggung. Tanggal itu berasal dari tahun 1834, ketika nama Kabupaten Temanggung disahkan.",
                "Temanggung Regency is in the province of Central Java. It lies between two large mountains, Mount Sindoro and Mount Sumbing. Mount Sindoro is 3,136 metres above sea level, while Mount Sumbing is higher, at 3,371 metres. Because they look alike, the two are often called the twin mountains.\n\nThe soil on the slopes of both mountains is fertile because it comes from volcanic ash. Many people work as farmers. They grow tobacco, coffee, and vegetables on the cool mountain slopes.\n\nTemanggung is divided into 20 districts. Every 10 November, people celebrate the anniversary of Temanggung Regency. The date comes from the year 1834, when the name Temanggung Regency was made official.",
            ],
            'ref' => $ref['geo'],
        ],
        'tmg-teks-b' => [
            'title' => ['Tembakau Srintil dan Kopi Lereng Gunung', 'Srintil Tobacco and Mountain-Slope Coffee'],
            'body'  => [
                "Temanggung terkenal sebagai Kota Tembakau. Panen tembakau biasanya berlangsung sekitar bulan Agustus. Daun tembakau diperam, dirajang, lalu dijemur di atas rigen, yaitu anyaman bambu berbentuk persegi panjang.\n\nTembakau paling istimewa dari Temanggung disebut tembakau srintil. Warnanya hitam keemasan dan aromanya harum. Srintil hanya dihasilkan di tempat tertentu di lereng Sindoro dan Sumbing, sehingga harganya sangat mahal.\n\nSelain tembakau, petani juga menanam kopi. Kopi arabika Java Sindoro Sumbing tumbuh di ketinggian lebih dari 900 meter. Temanggung punya beberapa produk indikasi geografis, yaitu tembakau srintil, kopi arabika, kopi robusta, dan ikan uceng. Indikasi geografis adalah tanda bahwa mutu sebuah produk ditentukan oleh daerah asalnya.\n\nTembakau adalah hasil kebun untuk orang dewasa. Asap rokok berbahaya bagi kesehatan, terutama bagi anak-anak.",
                "Temanggung is famous as the Tobacco Town. The tobacco harvest usually takes place around August. The leaves are cured, shredded, and then dried on a rigen, a rectangular sheet of woven bamboo.\n\nThe most special tobacco from Temanggung is called srintil tobacco. It is golden black and smells fragrant. Srintil is only produced in certain places on the slopes of Sindoro and Sumbing, so it is very expensive.\n\nBesides tobacco, farmers also grow coffee. Java Sindoro Sumbing arabica coffee grows at more than 900 metres above sea level. Temanggung has several products with a geographical indication: srintil tobacco, arabica coffee, robusta coffee, and uceng fish. A geographical indication is a sign that the quality of a product comes from the place where it is made.\n\nTobacco is a crop for adults. Cigarette smoke is harmful to health, especially for children.",
            ],
            'ref' => $ref['tobacco'],
        ],
        'tmg-teks-c' => [
            'title' => ['Jejak Mataram Kuno di Temanggung', 'Traces of Ancient Mataram in Temanggung'],
            'body'  => [
                "Temanggung menyimpan banyak peninggalan dari masa Kerajaan Mataram Kuno. Salah satunya Situs Liyangan di Desa Purbosari, Kecamatan Ngadirejo. Situs ini ditemukan pada tahun 2008 ketika warga sedang menambang pasir. Di dalam tanah, para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi yang hangus. Semua itu tertimbun material letusan Gunung Sindoro pada masa lalu.\n\nPeninggalan lain adalah Candi Pringapus, candi Hindu dari abad ke-9. Di dalam ruang candinya ada arca Nandi, yaitu arca lembu. Ada pula Prasasti Gondosuli yang dibuat pada tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.",
                "Temanggung holds many remains from the time of the Ancient Mataram Kingdom. One of them is the Liyangan Site in Purbosari Village, Ngadirejo District. The site was found in 2008 while villagers were digging for sand. Under the ground, researchers found the remains of wooden houses, temple buildings, stone paths, and burnt grains of rice. All of it had been buried by material from an eruption of Mount Sindoro long ago.\n\nAnother remain is Pringapus Temple, a Hindu temple from the 9th century. Inside its chamber there is a statue of Nandi, the sacred bull. There is also the Gondosuli Inscription, made in the year 832 CE. An inscription is writing on stone or metal that records an important event.",
            ],
            'ref' => $ref['history'],
        ],
        'tmg-teks-d' => [
            'title' => ['Umbul Jumprit, Hulu Kali Progo', 'Umbul Jumprit, Source of the Progo River'],
            'body'  => [
                "Umbul Jumprit adalah mata air di lereng Gunung Sindoro, sekitar 26 kilometer dari pusat Kota Temanggung. Airnya mengalir menjadi hulu Sungai Progo, sungai besar yang melintasi Temanggung dan Magelang. Mata air ini tidak pernah kering, bahkan pada musim kemarau.\n\nSejak tahun 1987, air dari Umbul Jumprit diambil untuk upacara Waisak di Candi Borobudur. Beberapa hari sebelum Waisak, para biksu datang dan mendoakan air itu sebelum dibawa. Karena itu, warga menjaga kebersihan mata air dan hutan di sekitarnya.",
                "Umbul Jumprit is a spring on the slopes of Mount Sindoro, about 26 kilometres from the centre of Temanggung Town. Its water flows out to become the source of the Progo River, a large river that runs through Temanggung and Magelang. The spring never dries up, even in the dry season.\n\nSince 1987, water from Umbul Jumprit has been taken for the Vesak ceremony at Borobudur Temple. A few days before Vesak, Buddhist monks come and pray over the water before it is carried away. That is why local people keep the spring and the forest around it clean.",
            ],
            'ref' => $ref['jumprit'],
        ],
        'tmg-teks-e' => [
            'title' => ['Kuda Lumping, Kesenian Rakyat Temanggung', 'Kuda Lumping, a Folk Art of Temanggung'],
            'body'  => [
                "Kuda lumping atau jaran kepang adalah tarian rakyat yang sangat populer di Temanggung. Penari menari sambil \"menunggang\" kuda-kudaan yang terbuat dari anyaman bambu. Kuda-kudaan itu dicat dan dihias dengan kain berwarna-warni. Satu kelompok kuda lumping terdiri atas penari, pemain musik, dan penabuh gamelan.\n\nDi Temanggung tercatat lebih dari 1.500 kelompok kuda lumping. Setiap tahun pemerintah daerah menggelar Nyadran Jaran Kepang, yaitu pentas kuda lumping bersama yang ditonton banyak warga dan wisatawan. Kesenian ini terus dijaga agar anak-anak muda tetap mengenal budaya daerahnya.",
                "Kuda lumping, or jaran kepang, is a folk dance that is very popular in Temanggung. The dancers dance while \"riding\" horses made of woven bamboo. The horses are painted and decorated with colourful cloth. One kuda lumping group is made up of dancers, musicians, and gamelan players.\n\nMore than 1,500 kuda lumping groups are recorded in Temanggung. Every year the local government holds Nyadran Jaran Kepang, a joint kuda lumping performance watched by many residents and visitors. This art is kept alive so that young people still know the culture of their region.",
            ],
            'ref' => $ref['art'],
        ],
        'tmg-teks-f' => [
            'title' => ['Parakan, Kota Bambu Runcing', 'Parakan, the Town of the Bamboo Spear'],
            'body'  => [
                "Parakan adalah kota kecil di Temanggung yang punya sejarah panjang. Sebelum tahun 1834, Parakan menjadi pusat pemerintahan Kabupaten Menoreh. Pusat pemerintahan kemudian dipindah ke Temanggung, dan peristiwa itu dikenang dengan nama Boyong Menoreh.\n\nPada masa perang kemerdekaan, Parakan dikenal sebagai kota bambu runcing. Seorang ulama bernama Kiai Subchi mendoakan bambu runcing yang dibawa para pejuang. Karena itu, ia dijuluki Kiai Bambu Runcing. Kini Parakan disebut kota pusaka karena banyak bangunan dan kisah bersejarahnya.",
                "Parakan is a small town in Temanggung with a long history. Before 1834, Parakan was the seat of government of Menoreh Regency. The seat of government was later moved to Temanggung, and the event is remembered as Boyong Menoreh.\n\nDuring the war of independence, Parakan was known as the town of the bamboo spear. A Muslim scholar named Kiai Subchi prayed over the sharpened bamboo spears carried by the freedom fighters. That is why he was nicknamed the Kiai of the Bamboo Spear. Today Parakan is called a heritage town because of its many historic buildings and stories.",
            ],
            'ref' => $ref['parakan'],
        ],
    ],

    // ============================================================ TANTANGAN
    'nodes' => [
        // ----------------------------------------------------------- tmg-1
        'tmg-1' => [
            'engine'          => 'puzzle',
            'indicator'       => 'budaya',
            'items_per_round' => 1,
            'note'            => $checked,
            'ref'             => $ref['geo'],
            'title'           => ['Susun Gambar Budaya Temanggung', 'Arrange the Temanggung Culture Picture'],
            'instruction'     => ['Tukar kepingan gambar (kisi 3×3) hingga utuh, lalu baca keterangannya.', 'Swap the picture pieces (3×3 grid) until the picture is complete, then read the caption.'],
            'description'     => [
                'Angin gunung mengacak sebuah lukisan tentang Temanggung. Bantu Jaka menyusunnya kembali, lalu baca keterangan di bawahnya untuk mengenal wilayah pertama ini.',
                'A mountain wind has scrambled a painting of Temanggung. Help Jaka put it back together, then read the caption below it to get to know this first region.',
            ],
            'hints' => [
                ['Mulailah dari kepingan sudut: sudut punya dua sisi lurus.', 'Start with the corner pieces: corners have two straight edges.'],
                ['Cocokkan warna langit, gunung, dan tanah pada kepingan yang bersebelahan.', 'Match the colours of the sky, mountains, and ground on neighbouring pieces.'],
                ['Angka kecil di kepingan menunjukkan letak benarnya: 1 di kiri atas sampai 9 di kanan bawah.', 'The small number on each piece shows where it belongs: 1 at the top left to 9 at the bottom right.'],
            ],
            'items' => [
                [
                    'type'   => 'puzzle_arrange',
                    'prompt' => ['Gunung Sindoro (3.136 m) dan Gunung Sumbing (3.371 m) disebut gunung kembar. Keduanya mengapit Temanggung dan membuat tanahnya subur.', 'Mount Sindoro (3,136 m) and Mount Sumbing (3,371 m) are called the twin mountains. They stand on either side of Temanggung and make its soil fertile.'],
                    'media'  => ['key' => 'challenge.item.tmg-1-01', 'note' => 'Foto lanskap Gunung Sindoro dan Sumbing dari arah Temanggung/Kledung, langit cerah, kebun di kaki gunung.', 'source' => 'https://commons.wikimedia.org/wiki/File:Sindoro_sumbing.jpg (cek lisensi & catat kredit)'],
                ],
                [
                    'type'   => 'puzzle_arrange',
                    'prompt' => ['Petani Temanggung menanam tembakau di lereng Sindoro–Sumbing. Daun tembakau dipanen sekitar bulan Agustus, lalu dirajang dan dijemur di atas rigen.', 'Temanggung farmers grow tobacco on the slopes of Sindoro and Sumbing. The leaves are harvested around August, then shredded and dried on bamboo rigen trays.'],
                    'ref'    => $ref['tobacco'],
                    'media'  => ['key' => 'challenge.item.tmg-1-02', 'note' => 'Foto kebun tembakau hijau di lereng gunung Temanggung (tanpa orang merokok).', 'source' => 'https://commons.wikimedia.org/wiki/File:Perkebunan_Tembakau_di_Lereng_Gunung_Sindoro.jpg'],
                ],
                [
                    'type'   => 'puzzle_arrange',
                    'prompt' => ['Candi Pringapus adalah candi Hindu dari abad ke-9. Di dalam ruang candinya tersimpan arca Nandi, arca lembu yang dihormati umat Hindu.', 'Pringapus Temple is a Hindu temple from the 9th century. Its chamber holds a statue of Nandi, the bull honoured by Hindus.'],
                    'ref'    => $ref['history'],
                    'media'  => ['key' => 'challenge.item.tmg-1-03', 'note' => 'Foto Candi Pringapus tampak depan, utuh dan jelas.', 'source' => 'https://commons.wikimedia.org/wiki/File:Candi_Pringapus_Temanggung_Jateng.jpg'],
                ],
                [
                    'type'   => 'puzzle_arrange',
                    'prompt' => ['Kuda lumping atau jaran kepang memakai kuda-kudaan dari anyaman bambu. Di Temanggung ada lebih dari 1.500 kelompok kuda lumping.', 'Kuda lumping, or jaran kepang, uses horses made of woven bamboo. Temanggung has more than 1,500 kuda lumping groups.'],
                    'ref'    => $ref['art'],
                    'media'  => ['key' => 'challenge.item.tmg-1-04', 'note' => 'Foto penari kuda lumping Temanggung dengan kuda anyaman bambu berwarna-warni.', 'source' => 'https://commons.wikimedia.org/wiki/File:Kuda_lumping_Temanggung.jpg'],
                ],
            ],
        ],

        // ----------------------------------------------------------- tmg-2
        'tmg-2' => [
            'engine'           => 'rumpang',
            'indicator'        => 'literasi',
            'items_per_round'  => 4,
            'use_word_bank'    => true,
            'distractor_count' => 3,
            'note'             => $checked,
            'ref'              => $ref['geo'],
            'title'            => ['Lengkapi Kalimat Temanggung', 'Complete the Temanggung Sentence'],
            'instruction'      => ['Baca informasinya, lalu pilih satu kata dari bank kata untuk mengisi ___.', 'Read the information, then choose one word from the word bank to fill in the ___.'],
            'description'      => [
                'Catatan perjalanan Jaka di Temanggung ada yang terhapus hujan. Temukan kata yang hilang dari informasi yang tertulis jelas.',
                'Some of Jaka\'s travel notes about Temanggung were washed away by the rain. Find the missing words in the information that is written clearly.',
            ],
            'distractors' => [
                ['Merapi', 'Merapi'], ['salju', 'snow'], ['Juli', 'July'], ['rotan', 'rattan'],
                ['rendah', 'lower'], ['danau', 'lake'], ['desa', 'villages'],
            ],
            'hints' => [
                ['Baca kalimat informasinya dulu, lalu cari kata yang sama maknanya dengan bagian yang kosong.', 'Read the information sentence first, then look for the word that fits the blank.'],
                ['Jawabannya tertulis di informasi. Jangan menebak dari ingatan.', 'The answer is written in the information. Do not guess from memory.'],
                ['Hati-hati dengan kata pengecoh: kata itu terdengar cocok, tetapi tidak ada di informasi.', 'Watch out for decoy words: they sound right but are not in the information.'],
            ],
            'items' => [
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Gunung Sindoro tingginya 3.136 meter, sedangkan Gunung Sumbing 3.371 meter.', 'Mount Sindoro is 3,136 metres high, while Mount Sumbing is 3,371 metres.'],
                    'prompt' => ['Di antara kedua gunung itu, Gunung ___ adalah yang lebih tinggi.', 'Of the two mountains, Mount ___ is the higher one.'],
                    'answer' => ['Sumbing', 'Sumbing'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Hari jadi Kabupaten Temanggung diperingati setiap tanggal 10 November.', 'The anniversary of Temanggung Regency is celebrated every 10 November.'],
                    'prompt' => ['Warga Temanggung merayakan hari jadi kabupatennya pada bulan ___.', 'The people of Temanggung celebrate their regency\'s anniversary in ___.'],
                    'answer' => ['November', 'November'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Tembakau terbaik Temanggung berwarna hitam keemasan dan harum. Namanya tembakau srintil.', 'Temanggung\'s best tobacco is golden black and fragrant. It is called srintil tobacco.'],
                    'prompt' => ['Tembakau Temanggung yang hitam keemasan disebut tembakau ___.', 'Temanggung\'s golden-black tobacco is called ___ tobacco.'],
                    'answer' => ['srintil', 'srintil'],
                    'ref'    => $ref['tobacco'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Umbul Jumprit adalah mata air di lereng Sindoro. Airnya menjadi hulu Sungai Progo.', 'Umbul Jumprit is a spring on the slopes of Sindoro. Its water is the source of the Progo River.'],
                    'prompt' => ['Air dari Umbul Jumprit mengalir menjadi Sungai ___.', 'The water from Umbul Jumprit flows on to become the ___ River.'],
                    'answer' => ['Progo', 'Progo'],
                    'ref'    => $ref['jumprit'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Situs Liyangan adalah permukiman kuno yang tertimbun material letusan Gunung Sindoro.', 'The Liyangan Site is an ancient settlement buried by material from an eruption of Mount Sindoro.'],
                    'prompt' => ['Permukiman Liyangan tertimbun letusan Gunung ___.', 'The Liyangan settlement was buried by an eruption of Mount ___.'],
                    'answer' => ['Sindoro', 'Sindoro'],
                    'ref'    => $ref['history'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Penari kuda lumping menunggang kuda-kudaan yang dibuat dari anyaman bambu.', 'Kuda lumping dancers ride horses made of woven bamboo.'],
                    'prompt' => ['Kuda-kudaan untuk tari jaran kepang dibuat dari ___ yang dianyam.', 'The horses for the jaran kepang dance are made of woven ___.'],
                    'answer' => ['bambu', 'bamboo'],
                    'ref'    => $ref['art'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Kiai Subchi dari Parakan mendoakan bambu runcing para pejuang, sehingga ia dijuluki Kiai Bambu Runcing.', 'Kiai Subchi of Parakan prayed over the fighters\' sharpened bamboo, so he was nicknamed the Kiai of the Bamboo Spear.'],
                    'prompt' => ['Kiai Subchi dijuluki Kiai Bambu ___.', 'Kiai Subchi was nicknamed the Kiai of the Bamboo ___.'],
                    'answer' => ['Runcing', 'Spear'],
                    'ref'    => $ref['parakan'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Kopi arabika Java Sindoro Sumbing tumbuh di ketinggian lebih dari 900 meter di atas permukaan laut.', 'Java Sindoro Sumbing arabica coffee grows more than 900 metres above sea level.'],
                    'prompt' => ['Kopi arabika Sindoro Sumbing tumbuh lebih dari 900 meter di atas permukaan ___.', 'Sindoro Sumbing arabica coffee grows more than 900 metres above ___ level.'],
                    'answer' => ['laut', 'sea'],
                    'ref'    => $ref['tobacco'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Kabupaten Temanggung terbagi menjadi 20 kecamatan.', 'Temanggung Regency is divided into 20 districts.'],
                    'prompt' => ['Temanggung memiliki 20 ___.', 'Temanggung has 20 ___.'],
                    'answer' => ['kecamatan', 'districts'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Setelah dirajang, tembakau dijemur di atas rigen, yaitu anyaman bambu berbentuk persegi panjang.', 'After shredding, the tobacco is dried on a rigen, a rectangular sheet of woven bamboo.'],
                    'prompt' => ['Tembakau rajangan dijemur di atas ___.', 'Shredded tobacco is dried on a ___.'],
                    'answer' => ['rigen', 'rigen'],
                    'ref'    => $ref['tobacco'] . '; ANTARA Jateng: Ruwat Rigen',
                ],
            ],
        ],

        // ----------------------------------------------------------- tmg-3
        'tmg-3' => [
            'engine'          => 'boleh',
            'indicator'       => 'literasi',
            'items_per_round' => 8,
            'verdict_options' => 'benar,salah',
            'require_reason'  => false,
            'note'            => $checked,
            'ref'             => $ref['geo'],
            'title'           => ['Benar atau Salah?', 'True or False?'],
            'instruction'     => ['Baca teks di samping, lalu tentukan apakah setiap pernyataan BENAR atau SALAH menurut teks.', 'Read the text alongside, then decide whether each statement is TRUE or FALSE according to the text.'],
            'description'     => [
                'Pedagang di pasar Temanggung bercerita macam-macam. Mbah Kedu berpesan: jangan langsung percaya — cocokkan setiap cerita dengan teks yang kamu baca.',
                'Traders at the Temanggung market tell all sorts of stories. Mbah Kedu says: do not believe them straight away — check each story against the text you read.',
            ],
            'hints' => [
                ['Cari kata kunci pernyataan (nama, angka, tempat) di dalam teks.', 'Find the key words of the statement (names, numbers, places) in the text.'],
                ['Pernyataan BENAR bila isinya sama dengan teks. Satu kata atau angka yang berbeda membuatnya SALAH.', 'A statement is TRUE when it says the same as the text. One different word or number makes it FALSE.'],
                ['Tekan label "Berdasarkan" pada kartu untuk melompat ke teksnya.', 'Press the "Based on" label on the card to jump to its text.'],
            ],
            'items' => [
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-a', 'answer' => 'benar', 'prompt' => ['Temanggung diapit oleh Gunung Sindoro dan Gunung Sumbing.', 'Temanggung lies between Mount Sindoro and Mount Sumbing.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-a', 'answer' => 'salah', 'prompt' => ['Gunung Sindoro lebih tinggi daripada Gunung Sumbing.', 'Mount Sindoro is higher than Mount Sumbing.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-a', 'answer' => 'salah', 'prompt' => ['Kabupaten Temanggung terbagi menjadi 12 kecamatan.', 'Temanggung Regency is divided into 12 districts.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-a', 'answer' => 'salah', 'prompt' => ['Hari jadi Kabupaten Temanggung diperingati setiap 17 Agustus.', 'The anniversary of Temanggung Regency is celebrated every 17 August.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-b', 'answer' => 'benar', 'ref' => $ref['tobacco'], 'prompt' => ['Panen tembakau di Temanggung biasanya berlangsung sekitar bulan Agustus.', 'The tobacco harvest in Temanggung usually takes place around August.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-b', 'answer' => 'salah', 'ref' => $ref['tobacco'], 'prompt' => ['Tembakau srintil berwarna putih dan tidak berbau.', 'Srintil tobacco is white and has no smell.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-b', 'answer' => 'benar', 'ref' => $ref['tobacco'], 'prompt' => ['Kopi arabika Java Sindoro Sumbing tumbuh di ketinggian lebih dari 900 meter.', 'Java Sindoro Sumbing arabica coffee grows at more than 900 metres.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-b', 'answer' => 'benar', 'ref' => $ref['tobacco'], 'prompt' => ['Ikan uceng termasuk produk indikasi geografis Temanggung.', 'Uceng fish is one of Temanggung\'s geographical indication products.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-c', 'answer' => 'benar', 'ref' => $ref['history'], 'prompt' => ['Situs Liyangan ditemukan ketika warga sedang menambang pasir.', 'The Liyangan Site was found while villagers were digging for sand.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-c', 'answer' => 'salah', 'ref' => $ref['history'], 'prompt' => ['Situs Liyangan tertimbun material letusan Gunung Merapi.', 'The Liyangan Site was buried by material from an eruption of Mount Merapi.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-c', 'answer' => 'salah', 'ref' => $ref['history'], 'prompt' => ['Candi Pringapus adalah candi Buddha dari abad ke-20.', 'Pringapus Temple is a Buddhist temple from the 20th century.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-c', 'answer' => 'benar', 'ref' => $ref['history'], 'prompt' => ['Prasasti Gondosuli dibuat pada tahun 832 Masehi.', 'The Gondosuli Inscription was made in 832 CE.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-d', 'answer' => 'benar', 'ref' => $ref['jumprit'], 'prompt' => ['Umbul Jumprit adalah hulu Sungai Progo.', 'Umbul Jumprit is the source of the Progo River.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-d', 'answer' => 'salah', 'ref' => $ref['jumprit'], 'prompt' => ['Mata air Umbul Jumprit selalu kering pada musim kemarau.', 'The Umbul Jumprit spring always dries up in the dry season.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-e', 'answer' => 'benar', 'ref' => $ref['art'], 'prompt' => ['Kuda-kudaan dalam kuda lumping terbuat dari anyaman bambu.', 'The horses in kuda lumping are made of woven bamboo.']],
                ['type' => 'verdict_card', 'passage' => 'tmg-teks-e', 'answer' => 'salah', 'ref' => $ref['art'], 'prompt' => ['Di Temanggung hanya ada sepuluh kelompok kuda lumping.', 'There are only ten kuda lumping groups in Temanggung.']],
            ],
        ],

        // ----------------------------------------------------------- tmg-4
        'tmg-4' => [
            'engine'          => 'cari',
            'indicator'       => 'budaya',
            'items_per_round' => 4,
            'note'            => $checked,
            'ref'             => $ref['tobacco'],
            'scene'           => ['key' => 'challenge.tmg-4.scene', 'note' => 'Ilustrasi 16:9 halaman balai desa/pasar budaya di kaki Sindoro–Sumbing: meja pajangan kosong, tikar, gunung kembar di latar. Benda-benda TIDAK digambar di adegan — ditempel terpisah sebagai gambar objek.', 'source' => 'Ilustrasi buatan tim / ilustrator (hindari foto dengan benda yang sudah ada)'],
            'title'           => ['Temukan Budaya Temanggung', 'Find the Temanggung Culture'],
            'instruction'     => ['Klik benda sesuai petunjuk Mbah Kedu. Hati-hati, ada benda yang bukan dari Temanggung!', 'Click the object that matches Mbah Kedu\'s clue. Careful, some objects are not from Temanggung!'],
            'description'     => [
                'Di pasar budaya, benda-benda khas Temanggung tercampur dengan benda dari daerah lain. Temukan yang benar-benar milik Temanggung.',
                'At the culture fair, typical Temanggung objects are mixed with objects from other regions. Find the ones that truly belong to Temanggung.',
            ],
            'hints' => [
                ['Baca petunjuknya pelan-pelan: bentuk, bahan, dan kegunaan benda disebut di sana.', 'Read the clue slowly: it names the shape, material, and use of the object.'],
                ['Ingat Pustaka Temanggung: tembakau, kopi, kuda lumping, Parakan, Candi Pringapus.', 'Remember the Temanggung Library: tobacco, coffee, kuda lumping, Parakan, Pringapus Temple.'],
                ['Benda dari daerah lain di Indonesia juga berharga, tetapi bukan jawaban tantangan ini.', 'Objects from other parts of Indonesia are valuable too, but they are not the answer to this challenge.'],
            ],
            'items' => [
                ['type' => 'find_object', 'x' => 4, 'y' => 6, 'w' => 12, 'prompt' => ['Temukan daun tembakau, tanaman kebun andalan petani Temanggung.', 'Find the tobacco leaf, the main garden crop of Temanggung farmers.'], 'media' => ['key' => 'challenge.item.tmg-4-01', 'note' => 'Daun tembakau hijau lebar (satu helai), latar transparan.']],
                ['type' => 'find_object', 'x' => 44, 'y' => 6, 'w' => 12, 'prompt' => ['Temukan biji kopi dari lereng Sindoro–Sumbing.', 'Find the coffee beans from the slopes of Sindoro and Sumbing.'], 'media' => ['key' => 'challenge.item.tmg-4-02', 'note' => 'Segenggam biji kopi sangrai cokelat, latar transparan.']],
                ['type' => 'find_object', 'x' => 84, 'y' => 6, 'w' => 12, 'ref' => $ref['art'], 'prompt' => ['Temukan kuda-kudaan anyaman bambu untuk menari jaran kepang.', 'Find the woven bamboo horse used for the jaran kepang dance.'], 'media' => ['key' => 'challenge.item.tmg-4-03', 'note' => 'Kuda lumping anyaman bambu berwarna (tanpa penari), latar transparan.']],
                ['type' => 'find_object', 'x' => 14, 'y' => 40, 'w' => 12, 'prompt' => ['Temukan ikan uceng, ikan sungai kecil yang menjadi produk indikasi geografis Temanggung.', 'Find the uceng fish, a small river fish that is one of Temanggung\'s geographical indication products.'], 'media' => ['key' => 'challenge.item.tmg-4-04', 'note' => 'Ilustrasi beberapa ikan uceng kecil memanjang (ikan sungai), latar transparan.']],
                ['type' => 'find_object', 'x' => 54, 'y' => 40, 'w' => 12, 'ref' => $ref['parakan'], 'prompt' => ['Temukan bambu runcing, lambang perjuangan kota Parakan.', 'Find the sharpened bamboo spear, the symbol of Parakan\'s struggle.'], 'media' => ['key' => 'challenge.item.tmg-4-05', 'note' => 'Sebatang bambu runcing (ujung diruncingkan), dengan pita merah putih kecil, latar transparan.']],
                ['type' => 'find_object', 'x' => 4, 'y' => 72, 'w' => 12, 'ref' => $ref['history'], 'prompt' => ['Temukan arca lembu Nandi seperti yang tersimpan di Candi Pringapus.', 'Find the statue of Nandi the bull, like the one kept in Pringapus Temple.'], 'media' => ['key' => 'challenge.item.tmg-4-06', 'note' => 'Arca lembu duduk dari batu andesit (gaya Nandi), latar transparan.']],
                ['type' => 'find_object', 'x' => 44, 'y' => 72, 'w' => 12, 'prompt' => ['Temukan kupat tahu, kuliner Temanggung berkuah kecap dan kacang.', 'Find kupat tahu, a Temanggung dish with sweet soy and peanut sauce.'], 'media' => ['key' => 'challenge.item.tmg-4-07', 'note' => 'Sepiring kupat tahu: ketupat, tahu goreng, tauge, kuah kecap-kacang, latar transparan.']],
                ['type' => 'find_object', 'x' => 84, 'y' => 72, 'w' => 12, 'prompt' => ['Temukan rigen, anyaman bambu tempat menjemur tembakau rajangan.', 'Find the rigen, the woven bamboo tray for drying shredded tobacco.'], 'media' => ['key' => 'challenge.item.tmg-4-08', 'note' => 'Rigen: anyaman bambu persegi panjang dengan tembakau rajangan kuning-cokelat di atasnya, latar transparan.']],
                ['type' => 'find_object', 'decoy' => true, 'x' => 74, 'y' => 40, 'w' => 12, 'prompt' => ['Angklung (objek jebakan).', 'Angklung (decoy object).'], 'wrong' => ['Ini angklung, alat musik bambu dari Jawa Barat — bukan benda khas Temanggung.', 'This is an angklung, a bamboo instrument from West Java — not a typical Temanggung object.'], 'media' => ['key' => 'challenge.item.tmg-4-09', 'note' => 'Angklung bambu, latar transparan.']],
                ['type' => 'find_object', 'decoy' => true, 'x' => 24, 'y' => 72, 'w' => 12, 'prompt' => ['Ondel-ondel (objek jebakan).', 'Ondel-ondel (decoy object).'], 'wrong' => ['Ini ondel-ondel, boneka raksasa dari budaya Betawi di Jakarta — bukan dari Temanggung.', 'This is an ondel-ondel, a giant puppet from Betawi culture in Jakarta — not from Temanggung.'], 'media' => ['key' => 'challenge.item.tmg-4-10', 'note' => 'Ondel-ondel kecil (kepala & badan), latar transparan.']],
                ['type' => 'find_object', 'decoy' => true, 'x' => 64, 'y' => 6, 'w' => 12, 'prompt' => ['Perahu pinisi (objek jebakan).', 'Pinisi boat (decoy object).'], 'wrong' => ['Ini perahu pinisi dari Sulawesi Selatan. Temanggung ada di pegunungan, jauh dari laut.', 'This is a pinisi boat from South Sulawesi. Temanggung is in the mountains, far from the sea.'], 'media' => ['key' => 'challenge.item.tmg-4-11', 'note' => 'Perahu pinisi dengan layar, latar transparan.']],
            ],
        ],

        // ----------------------------------------------------------- tmg-5
        'tmg-5' => [
            'engine'          => 'pilihan',
            'indicator'       => 'literasi',
            'items_per_round' => 3,
            'note'            => $checked,
            'ref'             => $ref['geo'],
            'title'           => ['Temukan Jawabannya di Teks', 'Find the Answer in the Text'],
            'instruction'     => ['Baca teks, lalu pilih satu jawaban yang tepat. Kamu hanya punya satu kesempatan per soal.', 'Read the text, then choose the one correct answer. You only get one try per question.'],
            'description'     => [
                'Serpihan Cahaya terakhir Temanggung tersimpan di balik pertanyaan. Jawabannya selalu tertulis di teks — temukan dengan teliti.',
                'The last Shard of Light in Temanggung is hidden behind questions. The answer is always written in the text — find it carefully.',
            ],
            'hints' => [
                ['Temukan kata kunci pertanyaan di dalam teks, lalu baca kalimat di sekitarnya.', 'Find the key words of the question in the text, then read the sentences around them.'],
                ['Coret dulu pilihan yang jelas tidak ada di teks.', 'First rule out the options that are clearly not in the text.'],
                ['Pastikan jawabanmu menjawab pertanyaan: kapan → waktu, di mana → tempat, mengapa → alasan.', 'Make sure your answer fits the question: when → time, where → place, why → reason.'],
            ],
            'items' => [
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-a',
                    'prompt' => ['Berapa tinggi Gunung Sumbing menurut teks?', 'How high is Mount Sumbing according to the text?'],
                    'options' => [
                        ['3.136 meter', '3,136 metres', false, 'Itu tinggi Gunung Sindoro. Baca lagi kalimat tentang Sumbing.', 'That is the height of Mount Sindoro. Read the sentence about Sumbing again.'],
                        ['3.371 meter', '3,371 metres', true, 'Tepat! Teks menyebut Gunung Sumbing setinggi 3.371 meter.', 'Correct! The text says Mount Sumbing is 3,371 metres high.'],
                        ['2.000 meter', '2,000 metres', false, 'Angka ini tidak ada di teks.', 'This number is not in the text.'],
                        ['1.834 meter', '1,834 metres', false, '1834 adalah tahun, bukan tinggi gunung.', '1834 is a year, not the height of a mountain.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-a',
                    'prompt' => ['Mengapa tanah di lereng Sindoro dan Sumbing subur?', 'Why is the soil on the slopes of Sindoro and Sumbing fertile?'],
                    'options' => [
                        ['Karena sering disiram air laut', 'Because it is often watered with seawater', false, 'Teks tidak menyebut air laut.', 'The text does not mention seawater.'],
                        ['Karena berasal dari abu gunung api', 'Because it comes from volcanic ash', true, 'Tepat! Teks menyebut tanahnya berasal dari abu gunung api.', 'Correct! The text says the soil comes from volcanic ash.'],
                        ['Karena ditutup salju setiap tahun', 'Because it is covered in snow every year', false, 'Tidak ada salju di teks.', 'There is no snow in the text.'],
                        ['Karena ada 20 kecamatan', 'Because there are 20 districts', false, 'Jumlah kecamatan tidak menjelaskan kesuburan tanah.', 'The number of districts does not explain why the soil is fertile.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-b', 'ref' => $ref['tobacco'],
                    'prompt' => ['Apa ciri tembakau srintil menurut teks?', 'What are the features of srintil tobacco according to the text?'],
                    'options' => [
                        ['Hitam keemasan dan harum', 'Golden black and fragrant', true, 'Tepat! Ciri itu tertulis jelas di teks.', 'Correct! Those features are written clearly in the text.'],
                        ['Putih dan pahit', 'White and bitter', false, 'Warna dan rasa ini tidak disebut di teks.', 'This colour and taste are not in the text.'],
                        ['Hijau muda dan tidak berbau', 'Light green and odourless', false, 'Teks menyebut srintil harum, bukan tidak berbau.', 'The text says srintil is fragrant, not odourless.'],
                        ['Murah dan tumbuh di mana saja', 'Cheap and grows anywhere', false, 'Teks menyebut srintil mahal dan hanya dari tempat tertentu.', 'The text says srintil is expensive and only from certain places.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-b', 'ref' => $ref['tobacco'],
                    'prompt' => ['Menurut teks, apa yang dimaksud indikasi geografis?', 'According to the text, what is a geographical indication?'],
                    'options' => [
                        ['Peta jalan menuju kebun kopi', 'A road map to the coffee gardens', false, 'Indikasi geografis bukan peta.', 'A geographical indication is not a map.'],
                        ['Tanda bahwa mutu produk ditentukan daerah asalnya', 'A sign that a product\'s quality comes from where it is made', true, 'Tepat! Begitulah teks menjelaskannya.', 'Correct! That is how the text explains it.'],
                        ['Nama gunung tertinggi di Temanggung', 'The name of the highest mountain in Temanggung', false, 'Itu tidak dijelaskan sebagai indikasi geografis.', 'That is not what the text calls a geographical indication.'],
                        ['Jadwal panen tembakau', 'The tobacco harvest schedule', false, 'Jadwal panen disebut di bagian lain teks.', 'The harvest time is mentioned elsewhere in the text.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-c', 'ref' => $ref['history'],
                    'prompt' => ['Kapan Situs Liyangan ditemukan?', 'When was the Liyangan Site found?'],
                    'options' => [
                        ['Tahun 832', 'In 832', false, '832 adalah tahun Prasasti Gondosuli.', '832 is the year of the Gondosuli Inscription.'],
                        ['Tahun 1834', 'In 1834', false, '1834 berhubungan dengan nama Kabupaten Temanggung.', '1834 is linked to the name of Temanggung Regency.'],
                        ['Tahun 1987', 'In 1987', false, '1987 berhubungan dengan air Umbul Jumprit.', '1987 is linked to the water of Umbul Jumprit.'],
                        ['Tahun 2008', 'In 2008', true, 'Tepat! Situs Liyangan ditemukan tahun 2008 saat warga menambang pasir.', 'Correct! The Liyangan Site was found in 2008 while villagers were digging for sand.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-c', 'ref' => $ref['history'],
                    'prompt' => ['Arca apa yang ada di dalam Candi Pringapus?', 'What statue is inside Pringapus Temple?'],
                    'options' => [
                        ['Arca Buddha', 'A Buddha statue', false, 'Candi Pringapus adalah candi Hindu.', 'Pringapus is a Hindu temple.'],
                        ['Arca Nandi, yaitu arca lembu', 'A statue of Nandi, the sacred bull', true, 'Tepat! Teks menyebut arca Nandi di ruang candi.', 'Correct! The text mentions the Nandi statue in the temple chamber.'],
                        ['Arca kuda lumping', 'A kuda lumping horse statue', false, 'Kuda lumping adalah tarian, bukan arca candi.', 'Kuda lumping is a dance, not a temple statue.'],
                        ['Arca harimau', 'A tiger statue', false, 'Harimau tidak disebut di teks.', 'A tiger is not mentioned in the text.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-d', 'ref' => $ref['jumprit'],
                    'prompt' => ['Sejak tahun berapa air Umbul Jumprit diambil untuk upacara Waisak?', 'Since what year has water from Umbul Jumprit been taken for the Vesak ceremony?'],
                    'options' => [
                        ['1987', '1987', true, 'Tepat! Teks menyebut tahun 1987.', 'Correct! The text says 1987.'],
                        ['1834', '1834', false, 'Tahun ini tidak ada di teks tentang Umbul Jumprit.', 'This year is not in the text about Umbul Jumprit.'],
                        ['2008', '2008', false, 'Tahun ini berhubungan dengan Situs Liyangan.', 'This year is linked to the Liyangan Site.'],
                        ['1945', '1945', false, 'Tahun ini tidak ada di teks.', 'This year is not in the text.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'tmg-teks-f', 'ref' => $ref['parakan'],
                    'prompt' => ['Mengapa Kiai Subchi dijuluki Kiai Bambu Runcing?', 'Why was Kiai Subchi nicknamed the Kiai of the Bamboo Spear?'],
                    'options' => [
                        ['Karena ia menjual bambu di pasar', 'Because he sold bamboo at the market', false, 'Teks tidak menyebut Kiai Subchi berjualan bambu.', 'The text does not say he sold bamboo.'],
                        ['Karena ia menanam bambu di Umbul Jumprit', 'Because he planted bamboo at Umbul Jumprit', false, 'Umbul Jumprit tidak berhubungan dengan julukannya.', 'Umbul Jumprit has nothing to do with his nickname.'],
                        ['Karena ia mendoakan bambu runcing para pejuang', 'Because he prayed over the fighters\' bamboo spears', true, 'Tepat! Alasan itu tertulis di teks tentang Parakan.', 'Correct! That reason is written in the text about Parakan.'],
                        ['Karena ia pandai menari kuda lumping', 'Because he was good at dancing kuda lumping', false, 'Teks tidak menyebut hal itu.', 'The text does not say that.'],
                    ],
                ],
            ],
        ],
    ],

    // ============================================================ PUSTAKA
    'library' => [
        [
            'title' => ['Selamat Datang di Temanggung', 'Welcome to Temanggung'],
            'body'  => [
                "Temanggung adalah kabupaten di tengah Provinsi Jawa Tengah. Di utara berbatasan dengan Kabupaten Kendal, di timur dengan Kabupaten Semarang, di selatan dengan Kabupaten Magelang, dan di barat dengan Kabupaten Wonosobo.\n\n## Angka-angka Temanggung\n- Luas wilayah sekitar **870 km²**.\n- Terbagi menjadi **20 kecamatan**, 266 desa, dan 23 kelurahan.\n- Penduduknya sekitar **800 ribu jiwa**.\n- Hari jadinya diperingati setiap **10 November**.\n\nKarena kebun tembakaunya sangat luas, Temanggung dijuluki **Kota Tembakau**. Udara di sebagian besar wilayahnya sejuk karena berada di lereng pegunungan.\n\n> Tahukah kamu? Dari tempat tinggi di Temanggung, saat cuaca cerah, kamu bisa melihat banyak puncak gunung sekaligus, seperti Sindoro, Sumbing, Merapi, Merbabu, dan Slamet.\n\nSumber: Pemerintah Kabupaten Temanggung; BPS Kabupaten Temanggung.",
                "Temanggung is a regency in the middle of Central Java Province. To the north it borders Kendal Regency, to the east Semarang Regency, to the south Magelang Regency, and to the west Wonosobo Regency.\n\n## Temanggung in numbers\n- Its area is about **870 km²**.\n- It is divided into **20 districts**, 266 villages, and 23 urban wards.\n- About **800 thousand people** live there.\n- Its anniversary is celebrated every **10 November**.\n\nBecause its tobacco gardens are so large, Temanggung is nicknamed the **Tobacco Town**. The air in most of the region is cool because it lies on mountain slopes.\n\n> Did you know? From high places in Temanggung, on a clear day, you can see many mountain peaks at once, such as Sindoro, Sumbing, Merapi, Merbabu, and Slamet.\n\nSources: Temanggung Regency Government; Statistics Indonesia (BPS) Temanggung.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Taman_Wisata_Alam_Posong,_Temanggung.jpg', 'caption' => ['Taman Wisata Alam Posong di Kledung, tempat melihat gunung-gunung Jawa Tengah', 'Posong Nature Park in Kledung, a place to view the mountains of Central Java'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Kabut_Tipis_Temanggung.jpg', 'caption' => ['Kabut tipis pagi hari di Temanggung', 'Thin morning mist in Temanggung'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
            ],
        ],
        [
            'title' => ['Si Kembar Sindoro dan Sumbing', 'The Twins: Sindoro and Sumbing'],
            'body'  => [
                "Dua gunung api menjaga Temanggung: **Gunung Sindoro** (3.136 meter) dan **Gunung Sumbing** (3.371 meter). Keduanya bertipe stratovolcano, yaitu gunung api berbentuk kerucut yang tersusun dari lapisan abu dan lava. Dari kejauhan bentuknya mirip, sehingga orang menyebutnya gunung kembar.\n\n## Nama yang penuh cerita\nMenurut cerita masyarakat, nama Sindoro berasal dari kata *senduro* yang berarti tunggal, karena gunung ini tampak berdiri sendiri. Cerita semacam ini disebut legenda: menarik untuk didengar, tetapi berbeda dengan fakta ilmiah.\n\n## Hadiah dari gunung api\n- Abu letusan zaman dulu membuat tanah lereng subur.\n- Mata air dari hutan gunung mengalir menjadi sungai, seperti Kali Progo.\n- Udara sejuk cocok untuk tembakau, kopi, dan sayuran.\n\n## Menikmati dengan aman\nEmbung Kledung dan Taman Wisata Alam Posong adalah tempat favorit melihat kedua gunung. Pendaki wajib mendaftar di jalur resmi, membawa jaket, dan membawa turun sampahnya.\n\n> Tahukah kamu? Gunung Sumbing lebih tinggi 235 meter daripada Gunung Sindoro.\n\nSumber: PVMBG (data gunung api); Perhutani; Pemerintah Kabupaten Temanggung.",
                "Two volcanoes watch over Temanggung: **Mount Sindoro** (3,136 metres) and **Mount Sumbing** (3,371 metres). Both are stratovolcanoes, cone-shaped volcanoes built from layers of ash and lava. From afar they look alike, so people call them the twin mountains.\n\n## Names full of stories\nAccording to local stories, the name Sindoro comes from the word *senduro*, meaning alone, because the mountain seems to stand by itself. A story like this is called a legend: fun to hear, but different from a scientific fact.\n\n## Gifts from the volcanoes\n- Ash from eruptions long ago made the slopes fertile.\n- Springs in the mountain forests flow into rivers such as the Progo.\n- The cool air suits tobacco, coffee, and vegetables.\n\n## Enjoying them safely\nKledung Reservoir and Posong Nature Park are favourite places to see both mountains. Climbers must register on official trails, bring a jacket, and carry their rubbish back down.\n\n> Did you know? Mount Sumbing is 235 metres higher than Mount Sindoro.\n\nSources: Centre for Volcanology (PVMBG); Perhutani; Temanggung Regency Government.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Sindoro_sumbing.jpg', 'caption' => ['Gunung Sindoro dan Gunung Sumbing, si gunung kembar', 'Mount Sindoro and Mount Sumbing, the twin mountains'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Gunung_Sindoro_dari_puncak_Gunung_Sumbing.jpg', 'caption' => ['Gunung Sindoro dilihat dari puncak Gunung Sumbing', 'Mount Sindoro seen from the summit of Mount Sumbing'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Gunung_Sumbing_Temanggung.jpg', 'caption' => ['Gunung Sumbing dari arah Temanggung', 'Mount Sumbing seen from Temanggung'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
            ],
        ],
        [
            'title' => ['Asal-usul Nama dan Sejarah Temanggung', 'The Origin of the Name and History of Temanggung'],
            'body'  => [
                "Nama Temanggung diyakini berasal dari kata **Tumenggung**, gelar pejabat tinggi dalam pemerintahan Jawa zaman dulu. Seorang tumenggung memimpin sebuah wilayah, kira-kira seperti bupati sekarang.\n\n## Dari Menoreh ke Temanggung\nDahulu wilayah ini bernama **Kabupaten Menoreh** dengan pusat pemerintahan di **Parakan**. Pada 7 April 1826, Raden Ngabehi Djojonegoro diangkat menjadi Bupati Menoreh dengan gelar Raden Tumenggung Aria Djojonegoro.\n\nSetelah Perang Diponegoro, pusat pemerintahan dipindahkan dari Parakan ke Temanggung. Nama kabupatennya pun diganti menjadi Temanggung. Perubahan itu disahkan dengan Resolusi Pemerintah Hindia Belanda Nomor 4 tanggal **10 November 1834** — tanggal yang kini diperingati sebagai hari jadi Kabupaten Temanggung.\n\n## Boyong Menoreh\nPerpindahan pusat pemerintahan itu dikenang dengan nama **Boyong Menoreh**. Pada peringatan hari jadi, warga kadang menampilkan kirab yang menceritakan kembali peristiwa ini.\n\n> Tahukah kamu? Jejak sejarah Temanggung jauh lebih tua dari tahun 1834. Prasasti-prasasti dari abad ke-8 sampai ke-10 menunjukkan wilayah ini sudah ramai pada masa Mataram Kuno.\n\nSumber: Pemerintah Kabupaten Temanggung (Sejarah Temanggung); Pemprov Jawa Tengah (Boyong Menoreh).",
                "The name Temanggung is believed to come from the word **Tumenggung**, the title of a high official in the old Javanese government. A tumenggung led a region, roughly like a regent today.\n\n## From Menoreh to Temanggung\nThis area used to be called **Menoreh Regency**, with its seat of government in **Parakan**. On 7 April 1826, Raden Ngabehi Djojonegoro was appointed Regent of Menoreh with the title Raden Tumenggung Aria Djojonegoro.\n\nAfter the Diponegoro War, the seat of government was moved from Parakan to Temanggung, and the regency was renamed Temanggung. The change was made official by Resolution No. 4 of the Dutch East Indies Government on **10 November 1834** — the date now celebrated as the anniversary of Temanggung Regency.\n\n## Boyong Menoreh\nThe move of the seat of government is remembered as **Boyong Menoreh**. At anniversary celebrations, people sometimes hold a parade that retells this event.\n\n> Did you know? Temanggung's history is much older than 1834. Inscriptions from the 8th to 10th centuries show that the area was already busy in the time of Ancient Mataram.\n\nSources: Temanggung Regency Government (History of Temanggung); Central Java Provincial Government (Boyong Menoreh).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Lambang_Kabupaten_Temanggung.png', 'caption' => ['Lambang Kabupaten Temanggung', 'Coat of arms of Temanggung Regency'], 'credit' => 'Wikimedia Commons — lambang daerah; rincian di halaman berkas'],
            ],
        ],
        [
            'title' => ['Tembakau Srintil dan Kopi Temanggung', 'Srintil Tobacco and Temanggung Coffee'],
            'body'  => [
                "Lereng Sindoro–Sumbing adalah ladang bagi dua hasil kebun terkenal: tembakau dan kopi.\n\n## Tembakau srintil\nPanen tembakau berlangsung sekitar **bulan Agustus**. Daun diperam beberapa hari, dirajang tipis, lalu dijemur di atas **rigen**, anyaman bambu persegi panjang. Menjelang panen, petani di Kledung menggelar tradisi **Ruwat Rigen**: rigen dibersihkan bersama-sama sebagai tanda syukur.\n\nTembakau terbaik disebut **srintil**. Warnanya hitam keemasan, aromanya harum, dan hanya muncul di tempat tertentu, sehingga harganya sangat mahal.\n\n## Kopi lereng gunung\n**Kopi Java Arabika Sindoro Sumbing** tumbuh di atas 900 meter. Petani sering menanamnya berdampingan dengan tembakau, sehingga sebagian penikmat kopi merasakan aroma khas di dalamnya.\n\n## Produk indikasi geografis\nTemanggung tercatat punya banyak produk berindikasi geografis: **tembakau srintil, kopi arabika, kopi robusta, dan ikan uceng**. Artinya, mutu produk itu diakui berasal dari daerahnya.\n\n> Ingat: tembakau adalah hasil kebun untuk orang dewasa. Asap rokok berbahaya bagi kesehatan — jauhi asapnya dan jangan mencoba merokok.\n\nSumber: Dinas Pertanian Kab. Temanggung; Pemprov Jateng (penghargaan indikasi geografis); ANTARA Jateng (Ruwat Rigen).",
                "The slopes of Sindoro and Sumbing are home to two famous crops: tobacco and coffee.\n\n## Srintil tobacco\nThe tobacco harvest takes place around **August**. The leaves are cured for a few days, shredded thinly, and dried on a **rigen**, a rectangular sheet of woven bamboo. Before the harvest, farmers in Kledung hold the **Ruwat Rigen** tradition: they clean their rigen together as a sign of gratitude.\n\nThe best tobacco is called **srintil**. It is golden black, fragrant, and only appears in certain places, so it is very expensive.\n\n## Mountain-slope coffee\n**Java Sindoro Sumbing Arabica coffee** grows above 900 metres. Farmers often plant it next to tobacco, so some coffee lovers taste a special aroma in it.\n\n## Geographical indication products\nTemanggung has many products with a geographical indication: **srintil tobacco, arabica coffee, robusta coffee, and uceng fish**. This means their quality is recognised as coming from the region.\n\n> Remember: tobacco is a crop for adults. Cigarette smoke is harmful to health — stay away from it and never try smoking.\n\nSources: Temanggung Agriculture Office; Central Java Provincial Government (geographical indication award); ANTARA Central Java (Ruwat Rigen).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Perkebunan_Tembakau_di_Lereng_Gunung_Sindoro.jpg', 'caption' => ['Kebun tembakau di lereng Gunung Sindoro', 'Tobacco gardens on the slopes of Mount Sindoro'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Petani_Tembakau_memeriksa_daun_Tembakau_yang_akan_dipanen.jpg', 'caption' => ['Petani memeriksa daun tembakau sebelum panen', 'A farmer checks tobacco leaves before the harvest'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Tobacco_Fields_on_the_slopes_of_Mt._Sumbing.jpg', 'caption' => ['Ladang tembakau di lereng Gunung Sumbing', 'Tobacco fields on the slopes of Mount Sumbing'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
            ],
        ],
        [
            'title' => ['Jejak Mataram Kuno', 'Traces of Ancient Mataram'],
            'body'  => [
                "Lebih dari seribu tahun lalu, Temanggung sudah menjadi tempat tinggal yang ramai pada masa **Kerajaan Mataram Kuno**.\n\n## Situs Liyangan, kampung yang terkubur\nSitus Liyangan berada di Dusun Liyangan, Desa Purbosari, Kecamatan Ngadirejo, sekitar 1.200 meter di atas permukaan laut. Situs ini ditemukan tahun **2008** ketika warga menambang pasir. Penelitian menemukan:\n- sisa **rumah kayu** yang hangus,\n- bangunan **candi** dan talud (dinding penahan tanah),\n- **jalan batu**, dan\n- butir **padi** yang terbakar.\n\nSemua itu terkubur material letusan **Gunung Sindoro**. Liyangan menunjukkan bahwa warga zaman dulu tinggal, beribadah, dan bertani di tempat yang sama.\n\n## Candi Pringapus dan Prasasti Gondosuli\n**Candi Pringapus** adalah candi Hindu dari abad ke-9. Di ruangnya ada **arca Nandi**, lembu yang dihormati umat Hindu. Di Desa Gondosuli, Kecamatan Bulu, terdapat **Prasasti Gondosuli** berangka tahun **832 Masehi**, peninggalan masa Mataram Kuno.\n\n> Tahukah kamu? Butir padi di Liyangan membantu peneliti mengetahui bahwa warga zaman itu sudah bertani padi di lereng gunung.\n\nSumber: Kompas.com (2023) Situs Liyangan; Tempo (Situs Liyangan); Pemkab Temanggung (Prasasti Gondosuli).",
                "More than a thousand years ago, Temanggung was already a busy place to live in the time of the **Ancient Mataram Kingdom**.\n\n## The Liyangan Site, a buried village\nThe Liyangan Site is in Liyangan Hamlet, Purbosari Village, Ngadirejo District, about 1,200 metres above sea level. It was found in **2008** while villagers were digging for sand. Research uncovered:\n- burnt remains of **wooden houses**,\n- **temple** buildings and retaining walls,\n- **stone paths**, and\n- burnt grains of **rice**.\n\nAll of it had been buried by material from **Mount Sindoro**. Liyangan shows that people long ago lived, worshipped, and farmed in the same place.\n\n## Pringapus Temple and the Gondosuli Inscription\n**Pringapus Temple** is a Hindu temple from the 9th century. Its chamber holds a statue of **Nandi**, the bull honoured by Hindus. In Gondosuli Village, Bulu District, there is the **Gondosuli Inscription**, dated **832 CE**, a remain of the Ancient Mataram period.\n\n> Did you know? The rice grains at Liyangan helped researchers learn that people at that time already grew rice on the mountain slopes.\n\nSources: Kompas.com (2023) Liyangan Site; Tempo (Liyangan Site); Temanggung Regency Government (Gondosuli Inscription).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Situs_liyangan.jpg', 'caption' => ['Situs Liyangan di lereng Gunung Sindoro', 'The Liyangan Site on the slopes of Mount Sindoro'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Candi_Pringapus_Temanggung_Jateng.jpg', 'caption' => ['Candi Pringapus, candi Hindu abad ke-9', 'Pringapus Temple, a 9th-century Hindu temple'], 'credit' => 'Wikimedia Commons, CC BY-SA 4.0 — pengarang di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Arca_Nandi_dalam_bilik_Candi_Pringapus,_Mei_2022.jpg', 'caption' => ['Arca Nandi di dalam bilik Candi Pringapus', 'The Nandi statue inside the chamber of Pringapus Temple'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
            ],
        ],
        [
            'title' => ['Umbul Jumprit, Hulu Kali Progo', 'Umbul Jumprit, Source of the Progo River'],
            'body'  => [
                "Di lereng Gunung Sindoro, sekitar 26 kilometer dari Kota Temanggung, ada mata air bernama **Umbul Jumprit**. Airnya jernih dan tidak pernah kering, bahkan pada musim kemarau.\n\n## Awal perjalanan Sungai Progo\nAir Umbul Jumprit menjadi **hulu Sungai Progo**. Sungai ini mengalir melewati Temanggung dan Magelang, mengairi sawah, lalu terus ke selatan sampai ke laut.\n\n## Air untuk Waisak\nSejak tahun **1987**, air dari Umbul Jumprit diambil untuk upacara **Tri Suci Waisak** di Candi Borobudur. Beberapa hari sebelum Waisak, para biksu dari berbagai aliran datang, mendoakan air, lalu membawanya ke Borobudur.\n\n## Cerita Ki Jumprit\nMasyarakat mengenal cerita tentang **Ki Jumprit**, seorang ahli nujum dari Kerajaan Majapahit yang disebut dalam Serat Centhini. Pada tahun 1987 pula, pemerintah daerah menjadikan kawasan ini wana wisata (wisata hutan).\n\n> Menjaga mata air berarti menjaga sungai. Jangan membuang sampah di sekitar mata air, dan biarkan pohon di hulu tetap tumbuh.\n\nSumber: Mongabay (2018) Berkah Air dari Umbul Jumprit; Liputan6; Perhutani; Wikipedia (Umbul Jumprit).",
                "On the slopes of Mount Sindoro, about 26 kilometres from Temanggung Town, there is a spring called **Umbul Jumprit**. Its water is clear and never dries up, even in the dry season.\n\n## The start of the Progo River\nThe water of Umbul Jumprit is the **source of the Progo River**. The river flows through Temanggung and Magelang, waters the rice fields, and continues south all the way to the sea.\n\n## Water for Vesak\nSince **1987**, water from Umbul Jumprit has been taken for the **Tri Suci Vesak** ceremony at Borobudur Temple. A few days before Vesak, monks from different Buddhist traditions come, pray over the water, and carry it to Borobudur.\n\n## The story of Ki Jumprit\nPeople know the story of **Ki Jumprit**, a stargazer from the Majapahit Kingdom mentioned in the Serat Centhini. Also in 1987, the local government made the area a forest recreation site.\n\n> Protecting a spring means protecting a river. Never drop rubbish near a spring, and let the trees upstream keep growing.\n\nSources: Mongabay (2018) Blessings of Water from Umbul Jumprit; Liputan6; Perhutani; Wikipedia (Umbul Jumprit).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Segoro_Wedi_Gunung_Sindoro_dengan_latar_Gunung_Sumbing.jpg', 'caption' => ['Lereng Gunung Sindoro, kawasan hulu mata air', 'The slopes of Mount Sindoro, where springs begin'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
            ],
        ],
        [
            'title' => ['Parakan, Kota Bambu Runcing', 'Parakan, the Town of the Bamboo Spear'],
            'body'  => [
                "**Parakan** adalah kota kecil di Temanggung yang menyimpan banyak kisah.\n\n## Bekas pusat pemerintahan\nSebelum tahun 1834, Parakan adalah pusat pemerintahan **Kabupaten Menoreh**. Setelah pusat pemerintahan pindah ke Temanggung, Parakan tetap menjadi kota dagang yang ramai.\n\n## Kiai Bambu Runcing\nSaat perang mempertahankan kemerdekaan Indonesia, banyak pejuang datang ke Parakan membawa **bambu runcing**. Seorang ulama, **Kiai Subchi**, mendoakan bambu runcing mereka. Karena itu, ia dikenal sebagai **Kiai Bambu Runcing**, dan Parakan dijuluki kota bambu runcing. Selain ulama, Kiai Subchi juga dikenal sebagai petani yang mengolah tanahnya sendiri.\n\n## Kota pusaka\nParakan kini disebut **kota pusaka** karena memiliki banyak rumah tua, bangunan bersejarah, dan cerita perjuangan. Warga mengusulkan kota ini menjadi semacam museum terbuka agar sejarahnya mudah dipelajari.\n\n> Pahlawan tidak hanya yang memegang senjata. Petani, guru, dan tokoh agama juga ikut berjuang dengan cara masing-masing.\n\nSumber: NU Online (KH Subchi Parakan); Gatra (Parakan Kota Pusaka); Media Center Kab. Temanggung.",
                "**Parakan** is a small town in Temanggung that holds many stories.\n\n## A former seat of government\nBefore 1834, Parakan was the seat of government of **Menoreh Regency**. After the seat of government moved to Temanggung, Parakan remained a busy trading town.\n\n## The Kiai of the Bamboo Spear\nDuring the war to defend Indonesia's independence, many fighters came to Parakan carrying **sharpened bamboo spears**. A Muslim scholar, **Kiai Subchi**, prayed over their spears. So he became known as the **Kiai of the Bamboo Spear**, and Parakan was nicknamed the town of the bamboo spear. Besides being a scholar, Kiai Subchi was also known as a farmer who worked his own land.\n\n## A heritage town\nParakan is now called a **heritage town** because it has many old houses, historic buildings, and stories of struggle. Residents have proposed turning it into a kind of open-air museum so that its history is easy to learn.\n\n> Heroes are not only people who carry weapons. Farmers, teachers, and religious leaders also struggled in their own ways.\n\nSources: NU Online (KH Subchi of Parakan); Gatra (Parakan Heritage Town); Temanggung Regency Media Center.",
            ],
        ],
        [
            'title' => ['Kuda Lumping dan Tradisi Temanggung', 'Kuda Lumping and Temanggung Traditions'],
            'body'  => [
                "## Kuda lumping (jaran kepang)\nKuda lumping adalah tarian rakyat yang penarinya \"menunggang\" kuda-kudaan dari **anyaman bambu**. Kuda itu dicat dan dihias kain warna-warni. Satu kelompok terdiri atas penari, pemain musik, dan penabuh gamelan.\n\nDi Temanggung tercatat **lebih dari 1.500 kelompok** kuda lumping — tanda bahwa kesenian ini benar-benar hidup di desa-desa. Para perajin setempat juga membuat kuda-kudaan bambu yang dikirim ke berbagai daerah.\n\n## Nyadran Jaran Kepang\nSetiap tahun Pemerintah Kabupaten Temanggung menggelar **Nyadran Jaran Kepang**, pentas kuda lumping bersama yang menjadi agenda wisata budaya, misalnya saat peringatan hari jadi.\n\n## Tradisi syukur petani\n- **Ruwat Rigen**: petani tembakau di Kledung membersihkan rigen bersama sebelum panen.\n- **Grebeg**: arak-arakan gunungan hasil bumi sebagai ungkapan syukur.\n\n> Saat menonton pertunjukan tradisi, jaga sikap: duduk tertib, tidak mengganggu penari, dan minta izin sebelum memotret dari dekat.\n\nSumber: DPRD Jateng (Kuda Lumping, Kesenian Khas Temanggung); Pemprov Jateng (Nyadran Jaran Kepang); ANTARA Jateng (Ruwat Rigen).",
                "## Kuda lumping (jaran kepang)\nKuda lumping is a folk dance in which the dancers \"ride\" horses made of **woven bamboo**. The horses are painted and decorated with colourful cloth. A group is made up of dancers, musicians, and gamelan players.\n\nMore than **1,500 groups** are recorded in Temanggung — a sign that this art is truly alive in the villages. Local craftspeople also make bamboo horses that are sent to many other regions.\n\n## Nyadran Jaran Kepang\nEvery year the Temanggung government holds **Nyadran Jaran Kepang**, a joint kuda lumping performance that is part of the cultural tourism calendar, for example at the regency's anniversary.\n\n## Farmers' thanksgiving traditions\n- **Ruwat Rigen**: tobacco farmers in Kledung clean their rigen trays together before the harvest.\n- **Grebeg**: a parade of harvest offerings shaped like mountains, as a way of giving thanks.\n\n> When you watch a traditional performance, mind your manners: sit in an orderly way, do not disturb the dancers, and ask permission before taking close-up photos.\n\nSources: Central Java Regional Parliament (Kuda Lumping of Temanggung); Central Java Provincial Government (Nyadran Jaran Kepang); ANTARA Central Java (Ruwat Rigen).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Kuda_lumping_Temanggung.jpg', 'caption' => ['Pertunjukan kuda lumping di Temanggung', 'A kuda lumping performance in Temanggung'], 'credit' => 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas'],
                ['url' => 'https://www.youtube.com/watch?v=IXDk03NgHqQ', 'kind' => 'video', 'caption' => ['Video: perajin kuda-kudaan jaran kepang di Temanggung', 'Video: craftspeople making jaran kepang horses in Temanggung'], 'credit' => 'YouTube — kanal pengunggah; guru mohon meninjau isi video sebelum studi'],
            ],
        ],
        [
            'title' => ['Rasa Temanggung', 'Tastes of Temanggung'],
            'body'  => [
                "Udara sejuk dan sungai yang jernih memberi Temanggung banyak makanan khas.\n\n## Kupat tahu Temanggung\nKetupat, tahu goreng, dan sayuran disiram kuah dari **kecap manis, bawang putih, dan kacang goreng** yang ditumbuk. Rasanya manis, gurih, dan sedikit pedas.\n\n## Ikan dari Kali Progo\n- **Ikan uceng**: ikan sungai kecil yang biasanya digoreng renyah. Uceng Temanggung sudah tercatat sebagai produk **indikasi geografis**.\n- **Mangut beong**: ikan beong dari aliran Sungai Progo yang dimasak dengan bumbu mangut berempah dan pedas.\n- **Iwak kali**: aneka ikan sungai segar dengan sambal.\n\n## Kopi Temanggung\nOrang dewasa di Temanggung bangga dengan kopi arabika dan robusta dari lereng Sindoro–Sumbing. Anak-anak cukup menikmati aromanya — kopi mengandung kafein yang kurang baik untuk anak.\n\n> Tahukah kamu? Makanan khas sering lahir dari bahan yang paling mudah didapat di sekitar. Ikan uceng dan beong ada karena Temanggung punya sungai yang jernih.\n\nSumber: Liputan6 (Makanan Khas Temanggung); Pemkab Temanggung (kuliner); Pemprov Jateng (indikasi geografis).",
                "The cool air and clear rivers give Temanggung many special foods.\n\n## Temanggung kupat tahu\nRice cake, fried tofu, and vegetables are covered with a sauce of **sweet soy sauce, garlic, and ground fried peanuts**. It tastes sweet, savoury, and a little spicy.\n\n## Fish from the Progo River\n- **Uceng fish**: small river fish usually fried until crispy. Temanggung uceng is registered as a **geographical indication** product.\n- **Mangut beong**: beong fish from the Progo River cooked in a spicy, aromatic mangut sauce.\n- **Iwak kali**: a mix of fresh river fish served with chilli sauce.\n\n## Temanggung coffee\nAdults in Temanggung are proud of the arabica and robusta coffee from the slopes of Sindoro and Sumbing. Children can simply enjoy the smell — coffee contains caffeine, which is not good for children.\n\n> Did you know? Local dishes are often born from the ingredients that are easiest to find nearby. Uceng and beong fish exist because Temanggung has clear rivers.\n\nSources: Liputan6 (Typical Foods of Temanggung); Temanggung Regency Government (culinary); Central Java Provincial Government (geographical indications).",
            ],
        ],
        [
            'title' => ['Tips Jaka: Membaca Informasi Tersurat', 'Jaka\'s Tips: Reading Stated Information'],
            'body'  => [
                "Di Temanggung, Jaka belajar menemukan **informasi tersurat**, yaitu informasi yang tertulis jelas di dalam teks.\n\n## Empat langkah menemukan jawaban\n- **Baca pertanyaannya dulu.** Apa yang ditanyakan: siapa, apa, kapan, di mana, mengapa, atau berapa?\n- **Tandai kata kunci.** Misalnya pada pertanyaan \"Kapan Situs Liyangan ditemukan?\", kata kuncinya *Liyangan* dan *ditemukan*.\n- **Pindai teks.** Gerakkan mata cepat untuk mencari kata kunci itu di dalam teks.\n- **Baca kalimat di sekitarnya dengan teliti.** Jawaban biasanya ada di kalimat yang memuat kata kunci.\n\n## Waspadai jebakan\nPilihan jawaban yang salah sering memakai angka atau nama yang **ada di teks, tetapi untuk hal lain**. Contohnya, tahun 832 memang ada di teks, tetapi itu tahun Prasasti Gondosuli, bukan tahun penemuan Liyangan.\n\n> Jaka berkata: \"Jawaban yang baik bukan tebakan. Aku bisa menunjuk kalimat di teks yang membuktikannya.\"\n\nSumber: Pedoman literasi membaca Kemendikbudristek (menemukan informasi tersurat).",
                "In Temanggung, Jaka learns to find **stated information**, information that is written clearly in the text.\n\n## Four steps to find the answer\n- **Read the question first.** What does it ask: who, what, when, where, why, or how many?\n- **Mark the key words.** For the question \"When was the Liyangan Site found?\", the key words are *Liyangan* and *found*.\n- **Scan the text.** Move your eyes quickly to look for those key words in the text.\n- **Read the sentences around them carefully.** The answer is usually in the sentence that contains the key words.\n\n## Watch out for traps\nWrong options often use numbers or names that **are in the text, but for something else**. For example, the year 832 is in the text, but it is the year of the Gondosuli Inscription, not the year Liyangan was found.\n\n> Jaka says: \"A good answer is not a guess. I can point to the sentence in the text that proves it.\"\n\nSource: Reading literacy guidelines of the Ministry of Education (finding stated information).",
            ],
        ],
    ],
];
