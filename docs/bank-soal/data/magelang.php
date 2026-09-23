<?php

/**
 * Isi bank soal & Pustaka — MAGELANG (wilayah 2, sedang).
 * Fokus literasi: MENGHUBUNGKAN informasi (gambar ↔ teks, sebab ↔ akibat)
 * dan membedakan FAKTA dari PENDAPAT.
 *
 * Bentuk data: lihat docs/bank-soal/README.md. Teks berpasangan
 * [Indonesia, English]. Tambahkan butir baru di AKHIR daftar agar kode
 * butir yang sudah dipakai siswa tidak bergeser.
 */

$ref = [
    'borobudur' => 'UNESCO World Heritage Centre: Borobudur Temple Compounds (No. 592); Kemenag Bimas Buddha: Tentang Borobudur; Kompas Travel (2025) Struktur Candi Borobudur',
    'mendut'    => 'BPK Wilayah X; visitmagelang.id: Candi Mendut Lebih Tua dari Borobudur; Telusuri.id: Candi-candi Buddha di sekitar Borobudur',
    'topeng'    => 'detikEdu (2022) Tari Topeng Ireng; Pemprov Jateng (Visit Jawa Tengah): Tari Topeng Ireng; Kompas Regional (2024)',
    'tidar'     => 'Tirto.id: Sejarah Gunung Tidar; Kompas Regional (2024) Gunung Tidar; Pemkot Magelang (Prasasti Mantyasih); Museum Nasional (Prasasti Canggal)',
    'craft'     => 'Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur (Gerabah Klipoh); detikFood (2025) Kuliner Legendaris Magelang',
    'region'    => 'Pemkab Magelang (profil); Good News From Indonesia (2025) Julukan Kota Magelang',
];

$checked = 'Fakta dicocokkan dengan sumber pada kolom reference_source (Sep 2026). Tinjau oleh guru/ahli, lalu ubah review_status menjadi verified.';
$opinion  = 'Butir literasi (fakta vs pendapat). Kunci & contoh alasan dicek tim; alasan siswa dinilai guru dengan rubrik.';
$commons  = 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas';

return [
    // ================================================================ BACAAN
    'passages' => [
        'mgl-teks-a' => [
            'title' => ['Candi Borobudur', 'Borobudur Temple'],
            'body'  => [
                "Candi Borobudur berdiri di Kecamatan Borobudur, Kabupaten Magelang. Candi Buddha ini dibangun pada abad ke-8 dan ke-9, pada masa pemerintahan Wangsa Syailendra.\n\nBangunannya bertingkat seperti gunung. Di bagian bawah ada enam teras persegi, lalu tiga teras bundar, dan di puncaknya satu stupa induk yang besar. Stupa adalah bangunan berbentuk lonceng. Di teras bundar berdiri 72 stupa berlubang, dan di dalam setiap stupa itu duduk sebuah arca Buddha. Seluruhnya, Borobudur dulu memiliki 504 arca Buddha.\n\nDinding Borobudur dihiasi 2.672 panel relief. Relief adalah gambar pahatan pada batu yang menceritakan kisah dan ajaran. Pada tahun 1991, UNESCO menetapkan Borobudur sebagai Warisan Dunia.",
                "Borobudur Temple stands in Borobudur District, Magelang Regency. This Buddhist temple was built in the 8th and 9th centuries, during the reign of the Syailendra dynasty.\n\nThe building rises in levels like a mountain. At the bottom there are six square terraces, then three round terraces, and on top one large main stupa. A stupa is a bell-shaped structure. On the round terraces stand 72 perforated stupas, and inside each of them sits a Buddha statue. Altogether, Borobudur once had 504 Buddha statues.\n\nThe walls of Borobudur are decorated with 2,672 relief panels. A relief is a picture carved in stone that tells stories and teachings. In 1991, UNESCO named Borobudur a World Heritage Site.",
            ],
            'ref' => $ref['borobudur'],
        ],
        'mgl-teks-b' => [
            'title' => ['Mendut, Pawon, dan Perjalanan Waisak', 'Mendut, Pawon, and the Vesak Journey'],
            'body'  => [
                "Tidak jauh dari Borobudur ada dua candi Buddha lain, yaitu Candi Mendut dan Candi Pawon. Bila ditarik garis dari timur ke barat, Candi Mendut, Candi Pawon, dan Candi Borobudur berada hampir pada satu garis lurus. Candi Pawon terletak di tengah-tengah.\n\nDi dalam ruang Candi Mendut ada arca Buddha setinggi sekitar tiga meter yang diapit dua arca lain. Candi Mendut diperkirakan lebih tua daripada Borobudur.\n\nSetiap perayaan Waisak, ribuan umat Buddha berjalan kaki dari Candi Mendut ke Candi Pawon, lalu berakhir di Candi Borobudur. Perjalanan ini melambangkan perjalanan batin menuju pencerahan. Air suci untuk upacara ini diambil dari Umbul Jumprit di Temanggung.",
                "Not far from Borobudur there are two other Buddhist temples, Mendut Temple and Pawon Temple. If a line is drawn from east to west, Mendut, Pawon, and Borobudur lie almost on one straight line. Pawon Temple is in the middle.\n\nInside the chamber of Mendut Temple there is a Buddha statue about three metres tall, flanked by two other statues. Mendut Temple is thought to be older than Borobudur.\n\nEvery Vesak, thousands of Buddhists walk from Mendut Temple to Pawon Temple, ending at Borobudur Temple. The walk symbolises an inner journey towards enlightenment. The holy water for the ceremony is taken from Umbul Jumprit in Temanggung.",
            ],
            'ref' => $ref['mendut'],
        ],
        'mgl-teks-c' => [
            'title' => ['Topeng Ireng, Tarian Penuh Semangat', 'Topeng Ireng, a Spirited Dance'],
            'body'  => [
                "Topeng Ireng adalah tari rakyat dari Kabupaten Magelang. Tarian ini mulai tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur. Namanya dijelaskan sebagai singkatan Toto Lempeng Irama Kenceng: toto berarti menata, lempeng berarti lurus, irama berarti musik, dan kenceng berarti keras.\n\nPenari memakai hiasan kepala dari bulu angsa atau ayam yang berwarna-warni, rumbai-rumbai keemasan, dan sepatu bot. Di pergelangan kaki penari terpasang lonceng-lonceng kecil yang berbunyi gemerincing mengikuti hentakan kaki. Wajah penari dirias dengan garis-garis hitam di atas bedak putih.\n\nTopeng Ireng berkembang di lereng Merapi dan Merbabu sampai ke Boyolali. Tarian ini menggambarkan semangat para petani di lereng gunung dan dulu juga dipakai sebagai sarana syiar agama Islam.",
                "Topeng Ireng is a folk dance from Magelang Regency. It began to grow around the 1950s in Tuksongo Village, Borobudur District. Its name is explained as short for Toto Lempeng Irama Kenceng: toto means to arrange, lempeng means straight, irama means music, and kenceng means loud.\n\nThe dancers wear colourful headdresses made of goose or chicken feathers, golden tassels, and boots. Small bells are tied around their ankles and jingle with every stamp of their feet. Their faces are made up with black stripes over white powder.\n\nTopeng Ireng spread across the slopes of Merapi and Merbabu as far as Boyolali. The dance shows the spirit of the farmers on the mountain slopes and was once also used to spread the teachings of Islam.",
            ],
            'ref' => $ref['topeng'],
        ],
        'mgl-teks-d' => [
            'title' => ['Gunung Tidar dan Prasasti Kuno', 'Mount Tidar and Ancient Inscriptions'],
            'body'  => [
                "Di tengah Kota Magelang berdiri Gunung Tidar, bukit setinggi sekitar 503 meter di atas permukaan laut. Dalam cerita rakyat Jawa, Gunung Tidar dijuluki paku tanah Jawa, karena dipercaya menjaga Pulau Jawa tetap tenang. Di lembah Gunung Tidar berdiri Akademi Militer sejak tahun 1957. Tempat ini mendidik calon perwira TNI Angkatan Darat.\n\nMagelang juga menyimpan prasasti tua. Prasasti Mantyasih dibuat pada 11 April 907 oleh Raja Dyah Balitung dari Mataram Kuno. Tanggal itu kini dijadikan hari jadi Kota Magelang. Prasasti yang lebih tua lagi adalah Prasasti Canggal dari tahun 732 Masehi, yang ditemukan di Gunung Wukir, Kecamatan Salam. Prasasti ini memperingati pendirian lingga oleh Raja Sanjaya dan kini disimpan di Museum Nasional, Jakarta.",
                "In the middle of Magelang City stands Mount Tidar, a hill about 503 metres above sea level. In Javanese folklore, Mount Tidar is called the nail of Java, because it is believed to keep the island of Java calm. In the valley of Mount Tidar, the Military Academy has stood since 1957. It trains future officers of the Indonesian Army.\n\nMagelang also keeps old inscriptions. The Mantyasih Inscription was made on 11 April 907 by King Dyah Balitung of Ancient Mataram. That date is now Magelang City's anniversary. An even older one is the Canggal Inscription from 732 CE, found on Mount Wukir in Salam District. It commemorates the setting up of a lingga by King Sanjaya and is now kept in the National Museum in Jakarta.",
            ],
            'ref' => $ref['tidar'],
        ],
        'mgl-teks-e' => [
            'title' => ['Getuk dan Gerabah Klipoh', 'Getuk and Klipoh Pottery'],
            'body'  => [
                "Magelang dijuluki Kota Getuk. Getuk dibuat dari singkong yang dikukus, ditumbuk halus, lalu diberi gula dan sedikit garam. Getuk Magelang sering berwarna cokelat dari gula jawa, putih, dan hijau. Julukan Kota Getuk bermula dari kreasi makanan pada masa penjajahan Jepang, ketika beras sulit didapat.\n\nSekitar tiga kilometer dari Candi Borobudur ada Dusun Klipoh di Desa Karanganyar. Warga Klipoh membuat gerabah, yaitu benda dari tanah liat yang dibentuk lalu dibakar. Mereka membuat kendil, cobek, kuali, dan anglo. Menurut cerita turun-temurun, warga Klipoh sudah membuat gerabah sejak lama sekali, bahkan sebelum Candi Borobudur ditemukan kembali.",
                "Magelang is nicknamed the Getuk Town. Getuk is made from cassava that is steamed, pounded smooth, and then mixed with sugar and a little salt. Magelang getuk is often brown from palm sugar, white, and green. The nickname began with food ideas during the Japanese occupation, when rice was hard to find.\n\nAbout three kilometres from Borobudur Temple lies Klipoh Hamlet in Karanganyar Village. The people of Klipoh make pottery, objects of clay that are shaped and then fired. They make cooking pots, stone-like grinding bowls, woks, and charcoal stoves. According to stories passed down the generations, Klipoh people have made pottery for a very long time, even before Borobudur Temple was rediscovered.",
            ],
            'ref' => $ref['craft'],
        ],
    ],

    // ============================================================ TANTANGAN
    'nodes' => [
        // ----------------------------------------------------------- mgl-1
        'mgl-1' => [
            'engine'          => 'puzzle',
            'indicator'       => 'budaya',
            'items_per_round' => 1,
            'note'            => $checked,
            'ref'             => $ref['borobudur'],
            'title'           => ['Susun Gambar Magelang', 'Arrange the Magelang Picture'],
            'instruction'     => ['Tukar kepingan gambar (kisi 3×3) hingga utuh, lalu baca kisahnya.', 'Swap the picture pieces (3×3 grid) until the picture is complete, then read its story.'],
            'description'     => [
                'Di Magelang, gambar-gambar warisan budaya berserakan seperti kepingan relief. Susun kembali, lalu hubungkan gambar itu dengan kisah di bawahnya.',
                'In Magelang, pictures of cultural heritage are scattered like pieces of a relief. Put them back together, then connect the picture with the story below it.',
            ],
            'hints' => [
                ['Mulai dari kepingan yang punya garis langit atau garis tanah.', 'Start with the pieces that show the sky line or the ground line.'],
                ['Kepingan dengan warna dan garis yang bersambung pasti bersebelahan.', 'Pieces whose colours and lines continue must sit next to each other.'],
                ['Angka kecil di kepingan menunjukkan letak benarnya: 1 di kiri atas sampai 9 di kanan bawah.', 'The small number on each piece shows where it belongs: 1 at the top left to 9 at the bottom right.'],
            ],
            'items' => [
                [
                    'type'   => 'puzzle_arrange',
                    'prompt' => ['Candi Borobudur dibangun pada abad ke-8 dan ke-9. Di teras bundarnya berdiri 72 stupa berlubang, masing-masing berisi arca Buddha.', 'Borobudur Temple was built in the 8th and 9th centuries. On its round terraces stand 72 perforated stupas, each holding a Buddha statue.'],
                    'media'  => ['key' => 'challenge.item.mgl-1-01', 'note' => 'Foto stupa-stupa berlubang di teras atas Borobudur dengan stupa induk di latar.', 'source' => 'https://commons.wikimedia.org/wiki/File:Borobudur_stupas_on_upper_terrace.jpg'],
                ],
                [
                    'type'   => 'puzzle_arrange',
                    'ref'    => $ref['topeng'],
                    'prompt' => ['Penari Topeng Ireng memakai hiasan kepala dari bulu berwarna-warni dan lonceng kecil di kaki. Tarian ini tumbuh di Desa Tuksongo, Borobudur.', 'Topeng Ireng dancers wear colourful feather headdresses and small bells on their ankles. The dance grew in Tuksongo Village, Borobudur.'],
                    'media'  => ['key' => 'challenge.item.mgl-1-02', 'note' => 'Foto penari Topeng Ireng dengan kuluk bulu warna-warni, tampak jelas.', 'source' => 'https://commons.wikimedia.org/wiki/File:Penari_Topeng_Ireng_03.jpg'],
                ],
                [
                    'type'   => 'puzzle_arrange',
                    'ref'    => $ref['mendut'],
                    'prompt' => ['Candi Mendut menyimpan arca Buddha setinggi sekitar tiga meter. Setiap Waisak, prosesi umat Buddha dimulai dari candi ini.', 'Mendut Temple holds a Buddha statue about three metres tall. Every Vesak, the Buddhist procession starts from this temple.'],
                    'media'  => ['key' => 'challenge.item.mgl-1-03', 'note' => 'Foto Candi Mendut tampak depan dengan tangga masuk.', 'source' => 'https://commons.wikimedia.org/wiki/File:Mendut_Temple.jpg'],
                ],
                [
                    'type'   => 'puzzle_arrange',
                    'ref'    => $ref['craft'],
                    'prompt' => ['Magelang dijuluki Kota Getuk. Getuk dibuat dari singkong yang dikukus, ditumbuk, lalu diberi gula.', 'Magelang is nicknamed the Getuk Town. Getuk is made from cassava that is steamed, pounded, and sweetened.'],
                    'media'  => ['key' => 'challenge.item.mgl-1-04', 'note' => 'Foto getuk Magelang warna-warni (cokelat, putih, hijau) di atas piring/daun pisang.', 'source' => 'https://commons.wikimedia.org/wiki/File:Getuk_Magelang.JPG'],
                ],
            ],
        ],

        // ----------------------------------------------------------- mgl-2
        'mgl-2' => [
            'engine'          => 'rumpang',
            'indicator'       => 'literasi',
            'items_per_round' => 4,
            'use_word_bank'   => false,
            'note'            => $checked,
            'ref'             => $ref['borobudur'],
            'title'           => ['Ketik Jawabanmu', 'Type Your Answer'],
            'instruction'     => ['Baca informasinya, lalu ketik satu kata atau angka untuk mengisi ___.', 'Read the information, then type one word or number to fill in the ___.'],
            'description'     => [
                'Juru kunci candi menitipkan catatan yang belum selesai. Tidak ada bank kata kali ini — hubungkan informasi dan tulis sendiri jawabannya.',
                'The temple keeper left some unfinished notes. There is no word bank this time — connect the information and write the answer yourself.',
            ],
            'hints' => [
                ['Jawabannya ada di kalimat informasi, tetapi mungkin ditulis dengan susunan yang berbeda.', 'The answer is in the information sentence, but it may be written in a different order.'],
                ['Ketik satu kata atau angka saja, tanpa titik di akhir.', 'Type just one word or number, without a full stop at the end.'],
                ['Periksa ejaanmu. Huruf besar atau kecil tidak masalah.', 'Check your spelling. Capital or small letters do not matter.'],
            ],
            'items' => [
                [
                    'type'   => 'fill_blank_free',
                    'source' => ['Candi Borobudur dibangun pada abad ke-8 dan ke-9, pada masa pemerintahan Wangsa Syailendra.', 'Borobudur Temple was built in the 8th and 9th centuries, during the reign of the Syailendra dynasty.'],
                    'prompt' => ['Raja-raja yang membangun Borobudur berasal dari Wangsa ___.', 'The kings who built Borobudur came from the ___ dynasty.'],
                    'answer' => ['Syailendra|Sailendra|Syailendera', 'Syailendra|Sailendra|Shailendra'],
                    'hints'  => [['Nama wangsanya diawali huruf S.', 'The dynasty\'s name starts with the letter S.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'source' => ['Di teras bundar Borobudur berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng.', 'On Borobudur\'s round terraces stand 72 perforated stupas. A stupa is a bell-shaped structure.'],
                    'prompt' => ['Bangunan berbentuk lonceng di teras bundar Borobudur disebut ___.', 'The bell-shaped structures on Borobudur\'s round terraces are called ___.'],
                    'answer' => ['stupa', 'stupas|stupa'],
                    'hints'  => [['Kata itu terdiri atas lima huruf.', 'It is the same word in English and Indonesian.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'source' => ['UNESCO menetapkan Borobudur sebagai Warisan Dunia pada tahun 1991.', 'UNESCO named Borobudur a World Heritage Site in 1991.'],
                    'prompt' => ['Borobudur menjadi Warisan Dunia UNESCO pada tahun ___.', 'Borobudur became a UNESCO World Heritage Site in ___.'],
                    'answer' => ['1991', '1991'],
                    'hints'  => [['Tulis empat angka tahunnya.', 'Write the four digits of the year.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'ref'    => $ref['topeng'],
                    'source' => ['Tari Topeng Ireng tumbuh di Desa Tuksongo, Kecamatan Borobudur, Kabupaten Magelang.', 'The Topeng Ireng dance grew in Tuksongo Village, Borobudur District, Magelang Regency.'],
                    'prompt' => ['Topeng Ireng adalah tarian rakyat dari Kabupaten ___.', 'Topeng Ireng is a folk dance from ___ Regency.'],
                    'answer' => ['Magelang', 'Magelang'],
                    'hints'  => [['Cari kata setelah "Kabupaten" dalam informasi.', 'Look for the word after "Regency" in the information.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'ref'    => $ref['mendut'],
                    'source' => ['Saat Waisak, umat Buddha berjalan dari Candi Mendut ke Candi Pawon, lalu berakhir di Candi Borobudur.', 'At Vesak, Buddhists walk from Mendut Temple to Pawon Temple, ending at Borobudur Temple.'],
                    'prompt' => ['Prosesi Waisak berakhir di Candi ___.', 'The Vesak procession ends at ___ Temple.'],
                    'answer' => ['Borobudur', 'Borobudur'],
                    'hints'  => [['Perhatikan kata "berakhir" di informasi.', 'Look at the word "ending" in the information.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'ref'    => $ref['tidar'],
                    'source' => ['Dalam cerita rakyat, Gunung Tidar di Magelang dijuluki paku tanah Jawa.', 'In folklore, Mount Tidar in Magelang is called the nail of Java.'],
                    'prompt' => ['Gunung Tidar dijuluki paku tanah ___.', 'Mount Tidar is called the nail of ___.'],
                    'answer' => ['Jawa', 'Java|Jawa'],
                    'hints'  => [['Nama sebuah pulau besar di Indonesia.', 'The name of a large island in Indonesia.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'ref'    => $ref['craft'],
                    'source' => ['Getuk dibuat dari singkong yang dikukus, ditumbuk halus, lalu diberi gula.', 'Getuk is made from cassava that is steamed, pounded smooth, and sweetened.'],
                    'prompt' => ['Bahan utama getuk adalah ___.', 'The main ingredient of getuk is ___.'],
                    'answer' => ['singkong|ketela pohon|ubi kayu|ketela', 'cassava|manioc'],
                    'hints'  => [['Bahan ini adalah umbi dari tanaman yang juga disebut ketela pohon.', 'It is a root crop also called manioc.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'ref'    => $ref['craft'],
                    'source' => ['Warga Dusun Klipoh membuat kendil, cobek, dan kuali dari tanah liat. Kerajinan tanah liat yang dibakar itu disebut gerabah.', 'The people of Klipoh make pots, grinding bowls, and woks from clay. This fired clay craft is called pottery.'],
                    'prompt' => ['Kerajinan tanah liat buatan warga Klipoh disebut ___.', 'The clay craft made by the people of Klipoh is called ___.'],
                    'answer' => ['gerabah|tembikar', 'pottery|earthenware'],
                    'hints'  => [['Kata itu ada di kalimat kedua informasi.', 'The word is in the second sentence of the information.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'source' => ['Dinding Borobudur dihiasi 2.672 panel relief, yaitu gambar pahatan pada batu yang menceritakan kisah.', 'Borobudur\'s walls are decorated with 2,672 relief panels, pictures carved in stone that tell stories.'],
                    'prompt' => ['Gambar pahatan bercerita pada dinding candi disebut ___.', 'A carved picture on a temple wall that tells a story is called a ___.'],
                    'answer' => ['relief|relif', 'relief'],
                    'hints'  => [['Kata itu muncul sebelum kata "yaitu" dalam informasi.', 'The word comes just before the explanation in the information.']],
                ],
                [
                    'type'   => 'fill_blank_free',
                    'ref'    => $ref['region'],
                    'source' => ['Kabupaten Magelang beribu kota di Kota Mungkid.', 'The capital of Magelang Regency is Mungkid.'],
                    'prompt' => ['Pusat pemerintahan Kabupaten Magelang berada di ___.', 'The seat of government of Magelang Regency is in ___.'],
                    'answer' => ['Mungkid|Kota Mungkid', 'Mungkid'],
                    'hints'  => [['Ibu kota kabupaten sama dengan pusat pemerintahannya.', 'A regency\'s capital is the same as its seat of government.']],
                ],
            ],
        ],

        // ----------------------------------------------------------- mgl-3
        'mgl-3' => [
            'engine'          => 'boleh',
            'indicator'       => 'sikap',
            'items_per_round' => 6,
            'verdict_options' => 'benar,salah,pendapat',
            'require_reason'  => true,
            'note'            => $opinion,
            'ref'             => $ref['borobudur'],
            'title'           => ['Benar, Salah, atau Pendapat?', 'True, False, or Opinion?'],
            'instruction'     => ['Tentukan apakah pernyataan itu fakta yang BENAR, fakta yang SALAH, atau sekadar PENDAPAT, lalu tulis alasanmu.', 'Decide whether each statement is a TRUE fact, a FALSE fact, or just an OPINION, then write your reason.'],
            'description'     => [
                'Pengunjung candi saling bercerita. Ada yang menyampaikan fakta, ada yang keliru, ada pula yang hanya menyampaikan perasaannya. Bantu Jaka memilah semuanya.',
                'Temple visitors are telling each other stories. Some share facts, some are mistaken, and some only share their feelings. Help Jaka sort them all out.',
            ],
            'hints' => [
                ['Fakta dapat dibuktikan dengan data: angka, tahun, tempat, nama.', 'A fact can be proven with data: numbers, years, places, names.'],
                ['Pendapat biasanya memakai kata penilaian seperti paling, indah, enak, seru, atau menyenangkan.', 'Opinions usually use judging words such as most, beautiful, tasty, exciting, or fun.'],
                ['Untuk fakta, cocokkan dengan teks: sama berarti BENAR, berbeda berarti SALAH.', 'For facts, check them against the text: the same means TRUE, different means FALSE.'],
            ],
            'items' => [
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-a', 'answer' => 'benar', 'note' => $checked,
                    'prompt' => ['Candi Borobudur ditetapkan UNESCO sebagai Warisan Dunia pada tahun 1991.', 'Borobudur Temple was named a World Heritage Site by UNESCO in 1991.'],
                    'reason' => ['Teks menyebut UNESCO menetapkan Borobudur sebagai Warisan Dunia pada tahun 1991, jadi pernyataan ini fakta yang benar.', 'The text says UNESCO named Borobudur a World Heritage Site in 1991, so this statement is a true fact.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-b', 'answer' => 'benar', 'note' => $checked, 'ref' => $ref['mendut'],
                    'prompt' => ['Prosesi Waisak berjalan dari Candi Mendut ke Candi Pawon, lalu ke Candi Borobudur.', 'The Vesak procession goes from Mendut Temple to Pawon Temple, then to Borobudur Temple.'],
                    'reason' => ['Urutan Mendut–Pawon–Borobudur tertulis di teks tentang Waisak.', 'The order Mendut–Pawon–Borobudur is written in the text about Vesak.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-d', 'answer' => 'benar', 'note' => $checked, 'ref' => $ref['tidar'],
                    'prompt' => ['Gunung Tidar tingginya sekitar 503 meter di atas permukaan laut.', 'Mount Tidar is about 503 metres above sea level.'],
                    'reason' => ['Teks menyebut tinggi Gunung Tidar sekitar 503 meter.', 'The text says Mount Tidar is about 503 metres high.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-e', 'answer' => 'benar', 'note' => $checked, 'ref' => $ref['craft'],
                    'prompt' => ['Getuk dibuat dari singkong yang dikukus lalu ditumbuk.', 'Getuk is made from cassava that is steamed and then pounded.'],
                    'reason' => ['Cara membuat getuk itu tertulis di teks tentang Magelang sebagai Kota Getuk.', 'That way of making getuk is written in the text about Magelang as the Getuk Town.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-a', 'answer' => 'salah', 'note' => $checked,
                    'prompt' => ['Candi Borobudur adalah candi Hindu yang dibangun pada abad ke-15.', 'Borobudur Temple is a Hindu temple built in the 15th century.'],
                    'reason' => ['Menurut teks, Borobudur adalah candi Buddha dari abad ke-8 dan ke-9, jadi pernyataan ini salah.', 'According to the text, Borobudur is a Buddhist temple from the 8th and 9th centuries, so the statement is false.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-c', 'answer' => 'salah', 'note' => $checked, 'ref' => $ref['topeng'],
                    'prompt' => ['Tari Topeng Ireng berasal dari Pulau Bali.', 'The Topeng Ireng dance comes from the island of Bali.'],
                    'reason' => ['Teks menyebut Topeng Ireng tumbuh di Desa Tuksongo, Magelang, bukan di Bali.', 'The text says Topeng Ireng grew in Tuksongo Village, Magelang, not in Bali.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-a', 'answer' => 'salah', 'note' => $checked,
                    'prompt' => ['Candi Borobudur hanya memiliki 72 arca Buddha.', 'Borobudur Temple has only 72 Buddha statues.'],
                    'reason' => ['72 adalah jumlah stupa berlubang. Teks menyebut Borobudur dulu memiliki 504 arca Buddha.', '72 is the number of perforated stupas. The text says Borobudur once had 504 Buddha statues.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-d', 'answer' => 'salah', 'note' => $checked, 'ref' => $ref['tidar'],
                    'prompt' => ['Akademi Militer di lembah Gunung Tidar berdiri sejak tahun 1907.', 'The Military Academy in the valley of Mount Tidar has stood since 1907.'],
                    'reason' => ['Teks menyebut Akademi Militer berdiri sejak tahun 1957, bukan 1907.', 'The text says the Military Academy has stood since 1957, not 1907.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-a', 'answer' => 'pendapat',
                    'prompt' => ['Candi Borobudur adalah bangunan paling indah di dunia.', 'Borobudur Temple is the most beautiful building in the world.'],
                    'reason' => ['Kata "paling indah" adalah penilaian pribadi. Setiap orang bisa punya penilaian berbeda, jadi ini pendapat.', 'The words "most beautiful" are a personal judgement. People can judge differently, so this is an opinion.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-c', 'answer' => 'pendapat', 'ref' => $ref['topeng'],
                    'prompt' => ['Tari Topeng Ireng lebih seru daripada semua tarian lain.', 'The Topeng Ireng dance is more exciting than every other dance.'],
                    'reason' => ['"Lebih seru" adalah perasaan pribadi dan tidak dapat diukur dengan data, jadi ini pendapat.', '"More exciting" is a personal feeling that cannot be measured with data, so it is an opinion.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-e', 'answer' => 'pendapat', 'ref' => $ref['craft'],
                    'prompt' => ['Getuk adalah jajanan paling enak di Jawa Tengah.', 'Getuk is the tastiest snack in Central Java.'],
                    'reason' => ['Rasa enak berbeda bagi setiap orang. Kata "paling enak" menunjukkan pendapat.', 'Taste is different for everyone. The words "the tastiest" show an opinion.']],
                ['type' => 'verdict_reason', 'passage' => 'mgl-teks-b', 'answer' => 'pendapat', 'ref' => $ref['mendut'],
                    'prompt' => ['Berjalan kaki dari Candi Mendut ke Borobudur adalah pengalaman yang sangat menyenangkan.', 'Walking from Mendut Temple to Borobudur is a very enjoyable experience.'],
                    'reason' => ['"Sangat menyenangkan" adalah perasaan pribadi; orang lain mungkin merasa lelah. Jadi ini pendapat.', '"Very enjoyable" is a personal feeling; someone else might feel tired. So this is an opinion.']],
            ],
        ],

        // ----------------------------------------------------------- mgl-4
        'mgl-4' => [
            'engine'          => 'pilihan',
            'indicator'       => 'budaya',
            'items_per_round' => 4,
            'note'            => $checked,
            'ref'             => $ref['borobudur'],
            'title'           => ['Cocokkan Fungsi & Asal', 'Match Function & Origin'],
            'instruction'     => ['Baca teks, lalu pilih jawaban yang tepat tentang kegunaan atau asal warisan budaya Magelang.', 'Read the text, then choose the correct answer about the use or origin of Magelang\'s cultural heritage.'],
            'description'     => [
                'Mbah Kedu mengajak Jaka ke museum. Setiap benda punya kegunaan dan asal-usul. Hubungkan benda dengan fungsi dan tempat asalnya.',
                'Mbah Kedu takes Jaka to a museum. Every object has a use and a story of where it came from. Connect each object with its function and origin.',
            ],
            'hints' => [
                ['Fungsi berarti "untuk apa". Asal berarti "dari mana". Perhatikan mana yang ditanyakan.', 'Function means "what it is for". Origin means "where it comes from". Check which one is asked.'],
                ['Cari nama benda atau tempat di teks, lalu baca kalimat sesudahnya.', 'Find the name of the object or place in the text, then read the sentence after it.'],
                ['Pilihan yang menyebut daerah lain biasanya bukan jawaban tentang Magelang.', 'Options that name other regions are usually not the answer about Magelang.'],
            ],
            'items' => [
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-a',
                    'prompt' => ['Apa fungsi relief pada dinding Candi Borobudur?', 'What is the purpose of the reliefs on the walls of Borobudur Temple?'],
                    'options' => [
                        ['Menceritakan kisah dan ajaran', 'To tell stories and teachings', true, 'Tepat! Relief adalah gambar pahatan yang bercerita.', 'Correct! Reliefs are carved pictures that tell stories.'],
                        ['Menahan air banjir', 'To hold back flood water', false, 'Relief adalah gambar pahatan, bukan dinding penahan air.', 'Reliefs are carved pictures, not flood walls.'],
                        ['Tempat menyimpan padi', 'To store rice', false, 'Teks tidak menyebut relief untuk menyimpan padi.', 'The text does not say reliefs are for storing rice.'],
                        ['Penanda batas desa', 'To mark village borders', false, 'Relief ada di dinding candi, bukan di batas desa.', 'Reliefs are on the temple walls, not at village borders.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-c', 'ref' => $ref['topeng'],
                    'prompt' => ['Di desa mana Tari Topeng Ireng mulai tumbuh?', 'In which village did the Topeng Ireng dance begin to grow?'],
                    'options' => [
                        ['Desa Klipoh, Temanggung', 'Klipoh Village, Temanggung', false, 'Klipoh terkenal dengan gerabah dan berada di Magelang, bukan Temanggung.', 'Klipoh is known for pottery and is in Magelang, not Temanggung.'],
                        ['Desa Tuksongo, Kecamatan Borobudur', 'Tuksongo Village, Borobudur District', true, 'Tepat! Teks menyebut Topeng Ireng tumbuh di Desa Tuksongo.', 'Correct! The text says Topeng Ireng grew in Tuksongo Village.'],
                        ['Desa Sembungan, Wonosobo', 'Sembungan Village, Wonosobo', false, 'Sembungan ada di Wonosobo.', 'Sembungan is in Wonosobo.'],
                        ['Kota Denpasar, Bali', 'Denpasar City, Bali', false, 'Topeng Ireng bukan tarian Bali.', 'Topeng Ireng is not a Balinese dance.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-c', 'ref' => $ref['topeng'],
                    'prompt' => ['Apa gunanya lonceng kecil di pergelangan kaki penari Topeng Ireng?', 'What are the small bells on the Topeng Ireng dancers\' ankles for?'],
                    'options' => [
                        ['Memanggil ternak pulang', 'To call the cattle home', false, 'Teks tidak menyebut ternak.', 'The text does not mention cattle.'],
                        ['Berbunyi gemerincing mengikuti hentakan kaki', 'To jingle with every stamp of the feet', true, 'Tepat! Bunyi lonceng menyatu dengan hentakan kaki penari.', 'Correct! The bells\' sound goes with the dancers\' stamping feet.'],
                        ['Menahan sepatu agar tidak lepas', 'To keep the boots from falling off', false, 'Fungsi itu tidak ada di teks.', 'That purpose is not in the text.'],
                        ['Menandai juara lomba', 'To mark the winner of a contest', false, 'Lonceng dipakai semua penari, bukan tanda juara.', 'All dancers wear bells; they are not winners\' marks.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-e', 'ref' => $ref['craft'],
                    'prompt' => ['Benda apa yang dibuat warga Dusun Klipoh?', 'What do the people of Klipoh Hamlet make?'],
                    'options' => [
                        ['Kendil, cobek, dan kuali dari tanah liat', 'Pots, grinding bowls, and woks from clay', true, 'Tepat! Itulah gerabah buatan Klipoh.', 'Correct! That is the pottery made in Klipoh.'],
                        ['Kain batik tulis', 'Hand-drawn batik cloth', false, 'Teks tidak menyebut batik.', 'The text does not mention batik.'],
                        ['Kuda-kudaan anyaman bambu', 'Woven bamboo horses', false, 'Kuda-kudaan bambu dikenal di Temanggung untuk kuda lumping.', 'Bamboo horses are known in Temanggung for kuda lumping.'],
                        ['Perahu kayu', 'Wooden boats', false, 'Teks tidak menyebut perahu.', 'The text does not mention boats.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-d', 'ref' => $ref['tidar'],
                    'prompt' => ['Prasasti Canggal (732 Masehi) dibuat untuk memperingati apa?', 'What does the Canggal Inscription (732 CE) commemorate?'],
                    'options' => [
                        ['Hari jadi Kota Magelang', 'The anniversary of Magelang City', false, 'Hari jadi Kota Magelang berasal dari Prasasti Mantyasih (907).', 'Magelang City\'s anniversary comes from the Mantyasih Inscription (907).'],
                        ['Pendirian lingga oleh Raja Sanjaya', 'King Sanjaya setting up a lingga', true, 'Tepat! Teks menyebut Prasasti Canggal memperingati pendirian lingga oleh Raja Sanjaya.', 'Correct! The text says it commemorates King Sanjaya setting up a lingga.'],
                        ['Pembukaan Akademi Militer', 'The opening of the Military Academy', false, 'Akademi Militer baru berdiri tahun 1957.', 'The Military Academy only opened in 1957.'],
                        ['Panen tembakau pertama', 'The first tobacco harvest', false, 'Teks tidak menyebut tembakau.', 'The text does not mention tobacco.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-b', 'ref' => $ref['mendut'],
                    'prompt' => ['Apa peran Candi Mendut dalam perayaan Waisak?', 'What role does Mendut Temple play in the Vesak celebration?'],
                    'options' => [
                        ['Tempat akhir prosesi', 'The end point of the procession', false, 'Prosesi berakhir di Borobudur.', 'The procession ends at Borobudur.'],
                        ['Tempat mengambil air suci', 'The place where holy water is taken', false, 'Air suci diambil dari Umbul Jumprit di Temanggung.', 'The holy water is taken from Umbul Jumprit in Temanggung.'],
                        ['Titik awal prosesi', 'The starting point of the procession', true, 'Tepat! Prosesi Waisak dimulai dari Candi Mendut.', 'Correct! The Vesak procession starts from Mendut Temple.'],
                        ['Tempat pertunjukan Topeng Ireng', 'The stage for Topeng Ireng', false, 'Teks tentang Waisak tidak menyebut Topeng Ireng.', 'The text about Vesak does not mention Topeng Ireng.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-d', 'ref' => $ref['tidar'],
                    'prompt' => ['Mengapa Prasasti Mantyasih penting bagi Kota Magelang?', 'Why is the Mantyasih Inscription important to Magelang City?'],
                    'options' => [
                        ['Tanggalnya dijadikan hari jadi Kota Magelang', 'Its date became Magelang City\'s anniversary', true, 'Tepat! 11 April 907 kini diperingati sebagai hari jadi kota.', 'Correct! 11 April 907 is now celebrated as the city\'s anniversary.'],
                        ['Prasasti itu ditemukan di puncak Borobudur', 'It was found at the top of Borobudur', false, 'Teks tidak menyebut tempat itu.', 'The text does not say that.'],
                        ['Prasasti itu berisi resep getuk', 'It contains a getuk recipe', false, 'Prasasti tidak berisi resep.', 'The inscription is not a recipe.'],
                        ['Prasasti itu dibuat Raja Sanjaya tahun 732', 'King Sanjaya made it in 732', false, 'Itu keterangan Prasasti Canggal.', 'That describes the Canggal Inscription.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'passage' => 'mgl-teks-d', 'ref' => $ref['tidar'],
                    'prompt' => ['Apa fungsi Akademi Militer di lembah Gunung Tidar?', 'What is the Military Academy in the valley of Mount Tidar for?'],
                    'options' => [
                        ['Mendidik calon perwira TNI Angkatan Darat', 'Training future officers of the Indonesian Army', true, 'Tepat! Fungsi itu tertulis di teks.', 'Correct! That purpose is written in the text.'],
                        ['Menyimpan prasasti kuno', 'Keeping ancient inscriptions', false, 'Prasasti Canggal disimpan di Museum Nasional, Jakarta.', 'The Canggal Inscription is kept in the National Museum, Jakarta.'],
                        ['Membuat gerabah', 'Making pottery', false, 'Gerabah dibuat di Dusun Klipoh.', 'Pottery is made in Klipoh Hamlet.'],
                        ['Menggelar prosesi Waisak', 'Holding the Vesak procession', false, 'Prosesi Waisak berlangsung di Mendut–Pawon–Borobudur.', 'The Vesak procession takes place at Mendut–Pawon–Borobudur.'],
                    ],
                ],
            ],
        ],

        // ----------------------------------------------------------- mgl-5
        'mgl-5' => [
            'engine'          => 'pilihan',
            'indicator'       => 'literasi',
            'items_per_round' => 3,
            'note'            => $checked,
            'ref'             => $ref['borobudur'],
            'title'           => ['Bandingkan Gambar dan Teks', 'Compare Picture and Text'],
            'instruction'     => ['Baca keterangan gambar dan teksnya, hubungkan keduanya, lalu pilih jawaban.', 'Read the picture description and the text, connect the two, then choose the answer.'],
            'description'     => [
                'Serpihan Cahaya Magelang tersembunyi di antara gambar dan tulisan. Jawaban benar ada pada informasi yang sama-sama muncul di gambar dan teks.',
                'The Shard of Light of Magelang is hidden between pictures and writing. The right answer is the information that appears in both the picture and the text.',
            ],
            'hints' => [
                ['Baca keterangan gambar dulu, lalu cari hal yang sama di dalam teks.', 'Read the picture description first, then look for the same thing in the text.'],
                ['Jawaban yang benar harus cocok dengan gambar DAN teks, bukan salah satunya saja.', 'The right answer must match the picture AND the text, not just one of them.'],
                ['Kata "keduanya" di pertanyaan berarti gambar dan teks harus sepakat.', 'The word "both" in a question means the picture and the text must agree.'],
            ],
            'items' => [
                [
                    'type'   => 'single_choice',
                    'source' => ["Gambar: bangunan berbentuk lonceng berlubang-lubang berjajar melingkar; di dalam salah satunya tampak arca yang duduk.\nTeks: Di teras bundar Borobudur berdiri 72 stupa berlubang. Di dalam setiap stupa duduk sebuah arca Buddha.", "Picture: rows of perforated bell-shaped structures in a circle; inside one of them a seated statue can be seen.\nText: On Borobudur's round terraces stand 72 perforated stupas. Inside each stupa sits a Buddha statue."],
                    'prompt' => ['Apa yang ada di dalam bangunan berlubang itu menurut gambar dan teks?', 'According to the picture and the text, what is inside the perforated structures?'],
                    'options' => [
                        ['Arca Buddha', 'A Buddha statue', true, 'Tepat! Gambar memperlihatkan arca duduk dan teks menyebut arca Buddha.', 'Correct! The picture shows a seated statue and the text names a Buddha statue.'],
                        ['Lonceng gamelan', 'A gamelan bell', false, 'Bentuknya seperti lonceng, tetapi isinya bukan lonceng.', 'The structures are bell-shaped, but they do not hold bells.'],
                        ['Tumpukan padi', 'A pile of rice', false, 'Gambar dan teks tidak menunjukkan padi.', 'Neither the picture nor the text shows rice.'],
                        ['Air suci Waisak', 'Holy Vesak water', false, 'Air suci tidak disebut di gambar maupun teks.', 'Holy water is not in the picture or the text.'],
                    ],
                ],
                [
                    'type'   => 'single_choice', 'ref' => $ref['topeng'],
                    'source' => ["Gambar: penari berhias kepala bulu warna-warni, wajah bergaris hitam, memakai sepatu bot, dan ada benda kecil mengilap di pergelangan kakinya.\nTeks: Di pergelangan kaki penari Topeng Ireng terpasang lonceng-lonceng kecil yang berbunyi gemerincing mengikuti hentakan kaki.", "Picture: a dancer with a colourful feather headdress, black-striped face, boots, and small shiny objects around the ankles.\nText: Small bells are tied around the ankles of Topeng Ireng dancers and jingle with every stamp of their feet."],
                    'prompt' => ['Benda mengilap di pergelangan kaki penari itu adalah…', 'The shiny objects around the dancer\'s ankles are…'],
                    'options' => [
                        ['Gelang emas perhiasan', 'Gold jewellery bracelets', false, 'Teks menjelaskan benda itu berbunyi gemerincing, jadi bukan sekadar perhiasan.', 'The text says they jingle, so they are not just jewellery.'],
                        ['Lonceng-lonceng kecil', 'Small bells', true, 'Tepat! Gambar memperlihatkan benda kecil di kaki dan teks menyebutnya lonceng.', 'Correct! The picture shows small objects on the ankles and the text calls them bells.'],
                        ['Kerikil dari sungai', 'Pebbles from the river', false, 'Teks tidak menyebut kerikil.', 'The text does not mention pebbles.'],
                        ['Tali sepatu', 'Shoelaces', false, 'Tali sepatu tidak berbunyi gemerincing.', 'Shoelaces do not jingle.'],
                    ],
                ],
                [
                    'type'   => 'single_choice', 'ref' => $ref['mendut'],
                    'source' => ["Gambar: peta kecil dengan tiga titik candi yang berjajar dari timur ke barat; titik tengah paling kecil.\nTeks: Candi Mendut, Candi Pawon, dan Candi Borobudur berada hampir pada satu garis lurus dari timur ke barat. Candi Pawon terletak di tengah.", "Picture: a small map with three temple dots in a row from east to west; the middle dot is the smallest.\nText: Mendut, Pawon, and Borobudur temples lie almost on one straight line from east to west. Pawon Temple is in the middle."],
                    'prompt' => ['Titik candi di tengah peta adalah candi apa?', 'Which temple is the dot in the middle of the map?'],
                    'options' => [
                        ['Candi Mendut', 'Mendut Temple', false, 'Mendut ada di ujung timur, tempat prosesi dimulai.', 'Mendut is at the eastern end, where the procession starts.'],
                        ['Candi Borobudur', 'Borobudur Temple', false, 'Borobudur ada di ujung barat dan paling besar.', 'Borobudur is at the western end and is the largest.'],
                        ['Candi Pawon', 'Pawon Temple', true, 'Tepat! Teks menyebut Candi Pawon terletak di tengah.', 'Correct! The text says Pawon Temple is in the middle.'],
                        ['Candi Pringapus', 'Pringapus Temple', false, 'Candi Pringapus ada di Temanggung.', 'Pringapus Temple is in Temanggung.'],
                    ],
                ],
                [
                    'type'   => 'single_choice',
                    'source' => ["Gambar: denah bertingkat — enam lapis persegi di bawah, tiga lingkaran di atasnya, dan satu stupa besar di puncak.\nTeks: Borobudur memiliki enam teras persegi, lalu tiga teras bundar, dan satu stupa induk di puncaknya.", "Picture: a layered plan — six square layers at the bottom, three circles above them, and one big stupa at the top.\nText: Borobudur has six square terraces, then three round terraces, and one main stupa at the top."],
                    'prompt' => ['Berapa jumlah teras bundar menurut gambar dan teks?', 'How many round terraces are there according to the picture and the text?'],
                    'options' => [
                        ['Satu', 'One', false, 'Satu adalah jumlah stupa induk di puncak.', 'One is the number of main stupas at the top.'],
                        ['Tiga', 'Three', true, 'Tepat! Gambar memperlihatkan tiga lingkaran dan teks menyebut tiga teras bundar.', 'Correct! The picture shows three circles and the text says three round terraces.'],
                        ['Enam', 'Six', false, 'Enam adalah jumlah teras persegi.', 'Six is the number of square terraces.'],
                        ['Tujuh puluh dua', 'Seventy-two', false, '72 adalah jumlah stupa berlubang, bukan teras.', '72 is the number of perforated stupas, not terraces.'],
                    ],
                ],
                [
                    'type'   => 'single_choice', 'ref' => $ref['craft'],
                    'source' => ["Gambar: perajin membentuk tanah liat di atas meja putar; di sampingnya berjajar pot bundar, cobek, dan kuali berwarna cokelat kemerahan.\nTeks: Warga Dusun Klipoh membuat gerabah, yaitu benda dari tanah liat yang dibentuk lalu dibakar.", "Picture: a craftsperson shaping clay on a turning table; next to them stand round pots, grinding bowls, and woks in reddish brown.\nText: The people of Klipoh Hamlet make pottery, objects of clay that are shaped and then fired."],
                    'prompt' => ['Dari bahan apa benda-benda di gambar dibuat menurut teks?', 'According to the text, what are the objects in the picture made of?'],
                    'options' => [
                        ['Tanah liat', 'Clay', true, 'Tepat! Gambar menunjukkan perajin membentuk tanah liat dan teks menyebut gerabah dari tanah liat.', 'Correct! The picture shows clay being shaped and the text says pottery is made of clay.'],
                        ['Batu andesit', 'Andesite stone', false, 'Batu andesit dipakai untuk candi, bukan gerabah.', 'Andesite is used for temples, not pottery.'],
                        ['Kayu jati', 'Teak wood', false, 'Teks tidak menyebut kayu.', 'The text does not mention wood.'],
                        ['Plastik', 'Plastic', false, 'Gerabah tradisional tidak dibuat dari plastik.', 'Traditional pottery is not made of plastic.'],
                    ],
                ],
                [
                    'type'   => 'single_choice', 'ref' => $ref['craft'],
                    'source' => ["Gambar: potongan kue lembut berwarna cokelat, putih, dan hijau di atas daun pisang; di belakangnya ada umbi panjang berkulit cokelat.\nTeks: Getuk dibuat dari singkong yang dikukus, ditumbuk halus, lalu diberi gula.", "Picture: soft pieces of cake in brown, white, and green on a banana leaf; behind them is a long root with brown skin.\nText: Getuk is made from cassava that is steamed, pounded smooth, and sweetened."],
                    'prompt' => ['Umbi berkulit cokelat di belakang kue itu adalah…', 'The brown-skinned root behind the cake is…'],
                    'options' => [
                        ['Ubi jalar', 'Sweet potato', false, 'Teks menyebut singkong, bukan ubi jalar.', 'The text says cassava, not sweet potato.'],
                        ['Talas', 'Taro', false, 'Talas tidak disebut di teks.', 'Taro is not in the text.'],
                        ['Singkong', 'Cassava', true, 'Tepat! Kuenya getuk dan teks menyebut getuk dibuat dari singkong.', 'Correct! The cake is getuk and the text says it is made from cassava.'],
                        ['Kentang', 'Potato', false, 'Kentang tidak disebut di teks.', 'Potato is not in the text.'],
                    ],
                ],
            ],
        ],
    ],

    // ============================================================ PUSTAKA
    'library' => [
        [
            'title' => ['Selamat Datang di Magelang', 'Welcome to Magelang'],
            'body'  => [
                "Magelang punya dua pemerintahan: **Kabupaten Magelang** dan **Kota Magelang**. Kota Magelang berada di tengah, dikelilingi wilayah Kabupaten Magelang.\n\n## Kabupaten Magelang\n- Ibu kotanya **Kota Mungkid**.\n- Terdiri atas **21 kecamatan**, 367 desa, dan 5 kelurahan.\n- Dikelilingi lima gunung yang disebut **Panca Arga**: Merapi, Merbabu, Sumbing, Telomoyo, dan Pegunungan Menoreh.\n\n## Kota Magelang\n- Hari jadinya **11 April 907**, dari Prasasti Mantyasih.\n- Mottonya **Kota Sejuta Bunga**.\n- Sejak masa Hindia Belanda dijuluki **Tuin van Java**, artinya taman Pulau Jawa, karena udaranya sejuk dan tanahnya subur.\n\n> Tahukah kamu? Karena dikelilingi gunung, Magelang juga dijuluki \"cincin emas\". Dari banyak tempat, kamu bisa melihat gunung di segala arah.\n\nSumber: Pemerintah Kabupaten Magelang; Pemerintah Kota Magelang; Good News From Indonesia (2025).",
                "Magelang has two governments: **Magelang Regency** and **Magelang City**. The city sits in the middle, surrounded by the regency.\n\n## Magelang Regency\n- Its capital is **Mungkid**.\n- It has **21 districts**, 367 villages, and 5 urban wards.\n- It is surrounded by five mountains called the **Panca Arga**: Merapi, Merbabu, Sumbing, Telomoyo, and the Menoreh Hills.\n\n## Magelang City\n- Its anniversary is **11 April 907**, from the Mantyasih Inscription.\n- Its motto is **City of a Million Flowers**.\n- Since Dutch colonial times it has been nicknamed **Tuin van Java**, the garden of Java, because the air is cool and the soil is fertile.\n\n> Did you know? Because it is ringed by mountains, Magelang is also called the \"golden ring\". From many places you can see mountains in every direction.\n\nSources: Magelang Regency Government; Magelang City Government; Good News From Indonesia (2025).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Pemandangan_Gunung_Andong_Magelang.jpg', 'caption' => ['Pemandangan dari Gunung Andong, Magelang', 'The view from Mount Andong, Magelang'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Lambang_Kabupaten_Magelang.jpg', 'caption' => ['Lambang Kabupaten Magelang', 'Coat of arms of Magelang Regency'], 'credit' => 'Wikimedia Commons — lambang daerah; rincian di halaman berkas'],
            ],
        ],
        [
            'title' => ['Candi Borobudur, Mahakarya Dunia', 'Borobudur Temple, a World Masterpiece'],
            'body'  => [
                "**Candi Borobudur** adalah candi Buddha terbesar di dunia. Candi ini dibangun pada **abad ke-8 dan ke-9** oleh raja-raja **Wangsa Syailendra**, tanpa semen — batu-batunya disusun saling mengunci.\n\n## Angka-angka Borobudur\n- **2.672 panel relief**: 1.460 relief cerita dan 1.212 relief hiasan.\n- **504 arca Buddha** pada awalnya.\n- **72 stupa berlubang** di teras bundar, masing-masing berisi arca Buddha.\n- **1 stupa induk** di puncak.\n\n## Warisan Dunia\nPada tahun **1991**, UNESCO menetapkan Kompleks Candi Borobudur — bersama Candi Mendut dan Candi Pawon — sebagai **Warisan Dunia**. Artinya, Borobudur dianggap berharga bagi seluruh umat manusia.\n\n## Menjaga Borobudur bersama\n- Ikuti rute dan aturan dari pengelola.\n- Jangan memanjat stupa atau menyentuh relief: minyak dan keringat tangan dapat merusak batu.\n- Jangan mencoret-coret dan jangan membuang sampah.\n\n> Tahukah kamu? Jika semua panel relief Borobudur dijajarkan, panjangnya mencapai beberapa kilometer!\n\nSumber: UNESCO World Heritage Centre (Borobudur Temple Compounds); Kemenag Bimas Buddha; Kompas Travel (2025).",
                "**Borobudur Temple** is the largest Buddhist temple in the world. It was built in the **8th and 9th centuries** by the kings of the **Syailendra dynasty**, without cement — its stones are fitted together so that they lock.\n\n## Borobudur in numbers\n- **2,672 relief panels**: 1,460 narrative reliefs and 1,212 decorative ones.\n- **504 Buddha statues** originally.\n- **72 perforated stupas** on the round terraces, each holding a Buddha statue.\n- **1 main stupa** at the top.\n\n## World Heritage\nIn **1991**, UNESCO named the Borobudur Temple Compounds — together with Mendut and Pawon temples — a **World Heritage Site**. This means Borobudur is considered precious for all of humanity.\n\n## Caring for Borobudur together\n- Follow the routes and rules of the site managers.\n- Do not climb the stupas or touch the reliefs: oil and sweat from hands can damage the stone.\n- Do not scribble and do not litter.\n\n> Did you know? If all of Borobudur's relief panels were placed in a row, they would stretch for several kilometres!\n\nSources: UNESCO World Heritage Centre (Borobudur Temple Compounds); Ministry of Religious Affairs, Buddhist Guidance; Kompas Travel (2025).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Borobudur_temple_from_above.jpg', 'caption' => ['Candi Borobudur dilihat dari atas', 'Borobudur Temple seen from above'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Borobudur_stupas_on_upper_terrace.jpg', 'caption' => ['Stupa-stupa berlubang dan stupa induk di teras atas', 'Perforated stupas and the main stupa on the upper terrace'], 'credit' => $commons],
                ['url' => 'https://www.youtube.com/watch?v=txujqGtB_6g', 'kind' => 'video', 'caption' => ['Video UNESCO/NHK: Kompleks Candi Borobudur', 'UNESCO/NHK video: Borobudur Temple Compounds'], 'credit' => 'YouTube — UNESCO/NHK World Heritage'],
            ],
        ],
        [
            'title' => ['Tiga Alam di Borobudur', 'The Three Realms of Borobudur'],
            'body'  => [
                "Borobudur dirancang seperti perjalanan dari bawah ke atas. Tingkat-tingkatnya melambangkan **tiga alam** dalam ajaran Buddha.\n\n## Kamadhatu — alam keinginan\nBagian **kaki candi**. Reliefnya, **Karmawibhangga**, menggambarkan perbuatan manusia dan akibatnya: perbuatan baik membawa kebaikan, perbuatan buruk membawa keburukan. Sebagian besar relief ini kini tertutup batu tambahan, dan sebagiannya bisa dilihat di Museum Karmawibhangga.\n\n## Rupadhatu — alam bentuk\n**Enam teras persegi** dengan lorong-lorong berdinding relief. Relief di sini menceritakan kehidupan Sang Buddha dan kisah-kisah teladan.\n\n## Arupadhatu — alam tanpa bentuk\n**Tiga teras bundar** tanpa relief, dengan 72 stupa berlubang, dan **stupa induk** di puncak. Suasananya lapang dan tenang.\n\n## Berjalan searah jarum jam\nUmat Buddha mengelilingi candi searah jarum jam dengan candi di sebelah kanan. Cara ini disebut **pradaksina**, sambil membaca relief dari tingkat ke tingkat.\n\n> Tahukah kamu? Di Museum Karmawibhangga ada arca Buddha yang belum selesai dipahat. Arca itu dulu ditemukan di stupa induk.\n\nSumber: Kompas Travel (2025) Struktur Candi Borobudur; Kemenag Bimas Buddha; Museum Karmawibhangga.",
                "Borobudur is designed like a journey from bottom to top. Its levels stand for the **three realms** in Buddhist teaching.\n\n## Kamadhatu — the realm of desire\nThe **foot of the temple**. Its reliefs, the **Karmawibhangga**, show human deeds and their results: good deeds bring good, bad deeds bring bad. Most of these reliefs are now covered by extra stones, and some can be seen at the Karmawibhangga Museum.\n\n## Rupadhatu — the realm of form\n**Six square terraces** with corridors whose walls are lined with reliefs. The reliefs here tell the life of the Buddha and stories of good examples.\n\n## Arupadhatu — the formless realm\n**Three round terraces** without reliefs, with 72 perforated stupas and the **main stupa** at the top. The space feels open and calm.\n\n## Walking clockwise\nBuddhists walk around the temple clockwise, keeping it on their right. This is called **pradaksina**, and pilgrims read the reliefs level by level.\n\n> Did you know? The Karmawibhangga Museum has an unfinished Buddha statue. It was once found in the main stupa.\n\nSources: Kompas Travel (2025) Structure of Borobudur; Ministry of Religious Affairs, Buddhist Guidance; Karmawibhangga Museum.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Borobudur_-_Karmawibhangga_-_013_Karmavibhanga_Section_Uncovered_(11831905453).jpg', 'caption' => ['Bagian relief Karmawibhangga yang dibuka di kaki candi', 'The uncovered section of the Karmawibhangga reliefs at the temple foot'], 'credit' => 'Wikimedia Commons, CC BY 2.0 — pengarang di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Relief_Di_Candi_Borobudur.jpg', 'caption' => ['Relief cerita di lorong Borobudur', 'A narrative relief in a Borobudur corridor'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Stupa_Borobudur.jpg', 'caption' => ['Stupa induk di puncak Borobudur', 'The main stupa at the top of Borobudur'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Menemukan dan Memugar Borobudur', 'Rediscovering and Restoring Borobudur'],
            'body'  => [
                "Selama ratusan tahun, Borobudur tertutup tanah, abu gunung api, dan semak belukar.\n\n## Ditemukan kembali, 1814\nPada tahun **1814**, Letnan Gubernur Jenderal **Thomas Stamford Raffles** mendengar kabar tentang bukit berisi batu berukir. Ia mengutus **H.C. Cornelius** untuk memeriksanya. Cornelius bersama ratusan warga membersihkan pepohonan dan tanah yang menutupi candi.\n\n## Pemugaran pertama, 1907–1911\n**Theodoor van Erp** memimpin pemugaran pertama. Fokusnya bagian puncak: tiga teras bundar dan stupa induk.\n\n## Pemugaran besar, 1975–1983\nPemerintah Indonesia bersama **UNESCO** melakukan pemugaran besar. Batu-batu dibongkar, dibersihkan, diberi saluran air dan lapisan kedap air, lalu dipasang kembali. Delapan tahun kemudian, pada **1991**, Borobudur ditetapkan sebagai Warisan Dunia.\n\n## Tugas kita\nPemugaran memerlukan banyak ahli dan waktu. Tugas kita lebih sederhana: menjaga kebersihan, tidak merusak, dan menceritakan pentingnya Borobudur kepada orang lain.\n\n> Tahukah kamu? Borobudur juga pernah tertutup abu letusan Gunung Merapi. Para petugas membersihkan abu itu dengan hati-hati agar batu candi tidak rusak.\n\nSumber: UNESCO World Heritage Centre; World History Encyclopedia (Borobudur); Kemenag Bimas Buddha.",
                "For hundreds of years, Borobudur was covered by soil, volcanic ash, and bushes.\n\n## Rediscovered, 1814\nIn **1814**, Lieutenant Governor **Thomas Stamford Raffles** heard about a hill full of carved stones. He sent **H.C. Cornelius** to look into it. Cornelius and hundreds of local people cleared away the trees and soil that covered the temple.\n\n## First restoration, 1907–1911\n**Theodoor van Erp** led the first restoration. It focused on the top: the three round terraces and the main stupa.\n\n## Major restoration, 1975–1983\nThe Indonesian government and **UNESCO** carried out a major restoration. The stones were taken apart, cleaned, given drainage and waterproof layers, and put back. Eight years later, in **1991**, Borobudur was named a World Heritage Site.\n\n## Our job\nRestoration takes many experts and a lot of time. Our job is simpler: keep the site clean, do not damage it, and tell others why Borobudur matters.\n\n> Did you know? Borobudur has also been covered by ash from eruptions of Mount Merapi. Workers cleaned the ash carefully so that the temple stones would not be damaged.\n\nSources: UNESCO World Heritage Centre; World History Encyclopedia (Borobudur); Ministry of Religious Affairs, Buddhist Guidance.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Borobudur-Temple-Park_Indonesia_Stupas-of-Borobudur-01.jpg', 'caption' => ['Stupa-stupa Borobudur setelah pemugaran', 'The stupas of Borobudur after restoration'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Mendut, Pawon, dan Perjalanan Waisak', 'Mendut, Pawon, and the Vesak Journey'],
            'body'  => [
                "Borobudur tidak berdiri sendirian. Di sebelah timurnya ada **Candi Pawon** dan **Candi Mendut**.\n\n## Tiga candi, satu garis\nBila ditarik garis dari timur ke barat, **Mendut – Pawon – Borobudur** berada hampir pada satu garis lurus. Banyak ahli meyakini ketiganya dibangun sebagai satu kesatuan.\n\n## Candi Mendut\nDi dalam ruang Candi Mendut duduk **arca Buddha setinggi sekitar tiga meter** dari batu andesit, diapit dua arca lain. Candi ini diperkirakan **lebih tua** daripada Borobudur.\n\n## Candi Pawon\nCandi Pawon berukuran kecil dan terletak **di tengah** antara Mendut dan Borobudur. Relief di dindingnya indah meskipun candinya mungil.\n\n## Perjalanan Waisak\nSetiap Waisak, ribuan umat Buddha berjalan kaki dari **Mendut ke Pawon, lalu ke Borobudur**, membawa api dan **air suci**. Air sucinya diambil dari **Umbul Jumprit** di Temanggung. Perjalanan ini melambangkan perjalanan batin menuju pencerahan.\n\n> Saat menyaksikan perayaan agama lain, kita menghormatinya: menjaga ketenangan, memberi jalan, dan tidak mengganggu umat yang beribadah.\n\nSumber: visitmagelang.id; Telusuri.id; Wikipedia (Mendut); Liputan6 (Umbul Jumprit & Waisak).",
                "Borobudur does not stand alone. To its east are **Pawon Temple** and **Mendut Temple**.\n\n## Three temples, one line\nIf a line is drawn from east to west, **Mendut – Pawon – Borobudur** lie almost on one straight line. Many experts believe the three were built as one whole.\n\n## Mendut Temple\nInside Mendut Temple sits a **Buddha statue about three metres tall** made of andesite stone, flanked by two other statues. The temple is thought to be **older** than Borobudur.\n\n## Pawon Temple\nPawon Temple is small and lies **in the middle** between Mendut and Borobudur. Its wall reliefs are beautiful even though the temple is tiny.\n\n## The Vesak journey\nEvery Vesak, thousands of Buddhists walk from **Mendut to Pawon, then to Borobudur**, carrying fire and **holy water**. The holy water is taken from **Umbul Jumprit** in Temanggung. The walk stands for an inner journey towards enlightenment.\n\n> When we watch another religion's celebration, we show respect: keep quiet, make way, and do not disturb people who are worshipping.\n\nSources: visitmagelang.id; Telusuri.id; Wikipedia (Mendut); Liputan6 (Umbul Jumprit & Vesak).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Mendut_Temple.jpg', 'caption' => ['Candi Mendut', 'Mendut Temple'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Buddha%27s_statue_inside_candi_Mendut.JPG', 'caption' => ['Arca Buddha di dalam Candi Mendut', 'The Buddha statue inside Mendut Temple'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Candi_Pawon_6.jpg', 'caption' => ['Candi Pawon, candi kecil di antara Mendut dan Borobudur', 'Pawon Temple, the small temple between Mendut and Borobudur'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Topeng Ireng, Tarian Penuh Semangat', 'Topeng Ireng, a Spirited Dance'],
            'body'  => [
                "**Topeng Ireng** adalah tari rakyat yang lahir di lereng-lereng gunung Magelang. Tarian ini mulai tumbuh sekitar **tahun 1950-an** di **Desa Tuksongo, Kecamatan Borobudur**, dari kesenian Kubro Siswo yang diiringi shalawatan.\n\n## Arti nama\nNamanya dijelaskan sebagai singkatan **To**to **Le**mpeng **I**rama **Ke**nceng: menata barisan yang lurus dengan irama musik yang keras dan bersemangat.\n\n## Kostum yang mencolok\n- **Kuluk** (hiasan kepala) dari bulu angsa atau ayam yang berwarna-warni.\n- Rumbai-rumbai keemasan dan **sepatu bot**.\n- **Lonceng kecil** di pergelangan kaki yang gemerincing setiap kaki dihentakkan.\n- Riasan wajah bergaris hitam di atas bedak putih.\n\n## Makna tarian\nGerakannya yang tegas menggambarkan **semangat petani lereng gunung**. Dulu tarian ini juga dipakai sebagai sarana **syiar agama Islam**. Kini Topeng Ireng tersebar di lereng **Merapi–Merbabu sampai Boyolali** dan sering tampil di festival.\n\n> Tahukah kamu? Sekelompok penari Topeng Ireng bisa berjumlah puluhan orang yang bergerak serempak. Kekompakan adalah kunci tarian ini.\n\nSumber: detikEdu (2022); Visit Jawa Tengah (Pemprov Jateng); Kompas Regional (2024).",
                "**Topeng Ireng** is a folk dance born on the mountain slopes of Magelang. It began to grow around the **1950s** in **Tuksongo Village, Borobudur District**, from the Kubro Siswo art accompanied by devotional songs.\n\n## The meaning of the name\nThe name is explained as short for **To**to **Le**mpeng **I**rama **Ke**nceng: arranging straight rows to loud, lively music.\n\n## A striking costume\n- A **kuluk** (headdress) of colourful goose or chicken feathers.\n- Golden tassels and **boots**.\n- **Small bells** on the ankles that jingle with every stamp.\n- Face make-up with black stripes over white powder.\n\n## The meaning of the dance\nIts firm movements show the **spirit of the mountain-slope farmers**. In the past the dance was also used to **spread the teachings of Islam**. Today Topeng Ireng is found on the slopes of **Merapi and Merbabu as far as Boyolali** and often appears at festivals.\n\n> Did you know? A Topeng Ireng troupe can have dozens of dancers moving together. Teamwork is the key to this dance.\n\nSources: detikEdu (2022); Visit Central Java (Central Java Provincial Government); Kompas Regional (2024).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Penari_Topeng_Ireng_03.jpg', 'caption' => ['Penari Topeng Ireng dengan kuluk bulu warna-warni', 'A Topeng Ireng dancer with a colourful feather headdress'], 'credit' => 'Wikimedia Commons, CC BY-SA 4.0 — pengarang di halaman berkas'],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Topeng_Ireng_(4).jpg', 'caption' => ['Barisan penari Topeng Ireng', 'A row of Topeng Ireng dancers'], 'credit' => $commons],
                ['url' => 'https://www.youtube.com/watch?v=CicQwPmwCWQ', 'kind' => 'video', 'caption' => ['Video: Kesenian khas Magelang, Tari Topeng Ireng', 'Video: Magelang\'s Topeng Ireng dance'], 'credit' => 'YouTube — Wonderful Indonesia'],
            ],
        ],
        [
            'title' => ['Gunung Tidar, Paku Tanah Jawa', 'Mount Tidar, the Nail of Java'],
            'body'  => [
                "Di tengah **Kota Magelang** ada bukit hijau bernama **Gunung Tidar**, tingginya sekitar **503 meter** di atas permukaan laut.\n\n## Legenda paku tanah Jawa\nDalam cerita rakyat Jawa, Gunung Tidar disebut **paku tanah Jawa**. Konon, Pulau Jawa dulu bergoyang-goyang, lalu \"dipaku\" di Gunung Tidar agar tenang. Ini **legenda**, bukan penjelasan ilmiah — tetapi legenda menunjukkan betapa pentingnya tempat itu bagi masyarakat.\n\n## Akademi Militer\nDi lembah Gunung Tidar berdiri **Akademi Militer** sejak **11 November 1957**, diresmikan Presiden Soekarno. Di sinilah para taruna dididik menjadi perwira **TNI Angkatan Darat**.\n\n## Kota Sejuta Bunga\nKota Magelang berulang tahun setiap **11 April**, mengikuti tanggal **Prasasti Mantyasih (907 M)** yang dibuat Raja Dyah Balitung. Taman-taman kotanya membuat Magelang berjuluk **Kota Sejuta Bunga**.\n\n> Tahukah kamu? Di puncak Gunung Tidar ada tugu dengan huruf Jawa \"Sa\" di tiga sisinya. Banyak orang mendaki tangganya untuk berolahraga dan berziarah.\n\nSumber: Tirto.id (Sejarah Gunung Tidar); Kompas Regional (2024); detikTravel; Pemerintah Kota Magelang.",
                "In the middle of **Magelang City** is a green hill called **Mount Tidar**, about **503 metres** above sea level.\n\n## The legend of the nail of Java\nIn Javanese folklore, Mount Tidar is called the **nail of Java**. It is said that the island of Java once wobbled, and was \"nailed\" at Mount Tidar to keep it still. This is a **legend**, not a scientific explanation — but legends show how important a place is to people.\n\n## The Military Academy\nIn the valley of Mount Tidar, the **Military Academy** has stood since **11 November 1957**, opened by President Soekarno. Here cadets are trained to become officers of the **Indonesian Army**.\n\n## City of a Million Flowers\nMagelang City celebrates its birthday every **11 April**, following the date of the **Mantyasih Inscription (907 CE)** made by King Dyah Balitung. Its many gardens give Magelang the nickname **City of a Million Flowers**.\n\n> Did you know? At the top of Mount Tidar there is a monument with the Javanese letter \"Sa\" on three sides. Many people climb its steps to exercise and to make pilgrimages.\n\nSources: Tirto.id (History of Mount Tidar); Kompas Regional (2024); detikTravel; Magelang City Government.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Makam_Syekh_Subakir.jpg', 'caption' => ['Petilasan di Gunung Tidar yang banyak dikunjungi peziarah', 'A shrine on Mount Tidar visited by many pilgrims'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Prasasti Canggal dan Awal Mataram Kuno', 'The Canggal Inscription and the Start of Ancient Mataram'],
            'body'  => [
                "Salah satu tulisan tertua tentang Kerajaan Mataram Kuno ditemukan di Magelang.\n\n## Prasasti Canggal (732 M)\n**Prasasti Canggal** ditemukan di kompleks **Candi Gunung Wukir**, Dusun Canggal, Desa Kadiluwih, **Kecamatan Salam**. Prasasti ini berangka tahun **654 Saka atau 732 Masehi**, ditulis dengan **aksara Pallawa** dalam **bahasa Sanskerta**.\n\n## Isinya\n- Pujian kepada dewa Siwa, Brahma, dan Wisnu.\n- Kisah **Raja Sanjaya** yang memulihkan kerajaannya dan memerintah dengan aman.\n- Peringatan **pendirian lingga** di atas bukit sebagai tanda syukur.\n\nKarena itu, Prasasti Canggal dipandang sebagai tanda awal kekuasaan Raja Sanjaya di Mataram Kuno. Batu prasastinya kini disimpan di **Museum Nasional, Jakarta**.\n\n## Membaca masa lalu\nPrasasti adalah \"surat\" dari masa lalu. Para ahli membacanya dengan teliti, membandingkannya dengan prasasti lain, lalu menyusun sejarah — mirip seperti kita membandingkan beberapa sumber sebelum menyimpulkan sesuatu.\n\n> Tahukah kamu? Angka tahun Prasasti Canggal ditulis dengan candrasengkala, yaitu kalimat bermakna yang setiap katanya melambangkan angka.\n\nSumber: Museum Nasional (Prasasti Canggal); Kompas Stori (2023) Candi Gunung Wukir; Good News From Indonesia (2024).",
                "One of the oldest writings about the Ancient Mataram Kingdom was found in Magelang.\n\n## The Canggal Inscription (732 CE)\nThe **Canggal Inscription** was found at the **Gunung Wukir Temple** site, Canggal Hamlet, Kadiluwih Village, **Salam District**. It is dated **654 Saka, or 732 CE**, and written in **Pallava script** in the **Sanskrit language**.\n\n## What it says\n- Praise for the gods Shiva, Brahma, and Vishnu.\n- The story of **King Sanjaya**, who restored his kingdom and ruled it safely.\n- A record of **setting up a lingga** on a hill as a sign of gratitude.\n\nThat is why the Canggal Inscription is seen as a sign of the start of King Sanjaya's rule in Ancient Mataram. The stone is now kept in the **National Museum in Jakarta**.\n\n## Reading the past\nAn inscription is a \"letter\" from the past. Experts read it carefully, compare it with other inscriptions, and then piece history together — much like we compare several sources before drawing a conclusion.\n\n> Did you know? The year of the Canggal Inscription is written as a candrasengkala, a meaningful sentence in which each word stands for a number.\n\nSources: National Museum (Canggal Inscription); Kompas Stori (2023) Gunung Wukir Temple; Good News From Indonesia (2024).",
            ],
        ],
        [
            'title' => ['Rasa dan Karya Magelang', 'Tastes and Crafts of Magelang'],
            'body'  => [
                "## Kota Getuk\n**Getuk** dibuat dari **singkong** yang dikukus, ditumbuk halus, lalu diberi gula dan sedikit garam. Warnanya bisa cokelat (gula jawa), putih, atau hijau (pandan). Julukan **Kota Getuk** bermula dari kreasi makanan pada **masa penjajahan Jepang**, ketika beras sulit didapat dan singkong menjadi pengganti.\n\n## Makanan legendaris lainnya\n- **Kupat tahu Magelang**: ketupat, tahu goreng, kol, dan tauge dengan kuah kacang manis gurih.\n- **Sop senerek**: sup daging dengan **kacang merah**, wortel, kentang, dan sayuran.\n- **Mangut beong**: ikan beong dari Kali Progo berbumbu mangut pedas.\n\n## Gerabah Klipoh\nSekitar 3 km dari Borobudur, warga **Dusun Klipoh, Desa Karanganyar** membuat **gerabah**: kendil, cobek, kuali, dan anglo dari tanah liat. Keahlian ini diwariskan turun-temurun. Wisatawan bahkan bisa mencoba membuat gerabah sendiri.\n\n## Pahat batu Muntilan\nDi sekitar **Muntilan**, para pemahat batu membuat arca dan hiasan dari batu andesit — keterampilan yang mengingatkan kita pada para pemahat Borobudur.\n\n> Tahukah kamu? Relief Borobudur juga memperlihatkan gambar periuk dan tempayan. Artinya, gerabah sudah dipakai masyarakat Jawa sejak dahulu kala.\n\nSumber: Kompas Travel (2022) Desa Klipoh; Badan Otorita Borobudur; detikFood (2025); Kompas.id (2023) Kuliner Khas Magelang.",
                "## The Getuk Town\n**Getuk** is made from **cassava** that is steamed, pounded smooth, and mixed with sugar and a little salt. It can be brown (palm sugar), white, or green (pandan). The nickname **Getuk Town** began with food ideas during the **Japanese occupation**, when rice was scarce and cassava took its place.\n\n## Other legendary dishes\n- **Magelang kupat tahu**: rice cake, fried tofu, cabbage, and bean sprouts with a sweet and savoury peanut sauce.\n- **Sop senerek**: meat soup with **red beans**, carrots, potatoes, and vegetables.\n- **Mangut beong**: beong fish from the Progo River in a spicy mangut sauce.\n\n## Klipoh pottery\nAbout 3 km from Borobudur, the people of **Klipoh Hamlet, Karanganyar Village** make **pottery**: cooking pots, grinding bowls, woks, and charcoal stoves from clay. The skill is passed down from generation to generation. Visitors can even try making pottery themselves.\n\n## Muntilan stone carving\nAround **Muntilan**, stone carvers make statues and ornaments from andesite — a skill that reminds us of the carvers of Borobudur.\n\n> Did you know? Borobudur's reliefs also show pots and jars. This means pottery has been used by the people of Java since ancient times.\n\nSources: Kompas Travel (2022) Klipoh Village; Borobudur Authority; detikFood (2025); Kompas.id (2023) Typical Magelang Food.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Getuk_Magelang.JPG', 'caption' => ['Getuk Magelang yang berwarna-warni', 'Colourful Magelang getuk'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Tips Jaka: Fakta, Pendapat, dan Menghubungkan Informasi', 'Jaka\'s Tips: Facts, Opinions, and Connecting Information'],
            'body'  => [
                "Di Magelang, Jaka belajar dua keterampilan membaca yang penting.\n\n## 1. Membedakan fakta dan pendapat\n- **Fakta** dapat dibuktikan: ada angka, tahun, nama, atau tempat yang bisa dicek. Contoh: *Borobudur memiliki 72 stupa berlubang.*\n- **Pendapat** adalah penilaian atau perasaan seseorang. Biasanya memakai kata seperti **paling, indah, enak, seru, bagus, menyenangkan**. Contoh: *Borobudur adalah candi paling indah.*\n- Fakta bisa **benar** atau **salah**. Cocokkan dengan sumber yang tepercaya untuk mengetahuinya.\n\n## 2. Menghubungkan gambar dan teks\n- Lihat gambarnya dengan teliti: apa saja yang tampak?\n- Baca teksnya: apa yang dijelaskan?\n- Cari **bagian yang sama** di keduanya. Jawaban yang kuat didukung gambar **dan** teks.\n\n## Latihan kecil\nManakah fakta dan manakah pendapat?\n- Getuk dibuat dari singkong.\n- Getuk adalah jajanan paling enak.\n\n> Jaka berkata: \"Pendapat boleh berbeda-beda dan harus dihormati. Tapi fakta harus dicek dulu kebenarannya.\"\n\nSumber: Pedoman literasi membaca Kemendikbudristek (fakta dan opini; membaca teks multimoda).",
                "In Magelang, Jaka learns two important reading skills.\n\n## 1. Telling facts from opinions\n- A **fact** can be proven: it has numbers, years, names, or places that can be checked. Example: *Borobudur has 72 perforated stupas.*\n- An **opinion** is someone's judgement or feeling. It usually uses words like **most, beautiful, tasty, exciting, good, fun**. Example: *Borobudur is the most beautiful temple.*\n- A fact can be **true** or **false**. Check it against a trustworthy source to find out.\n\n## 2. Connecting pictures and text\n- Look at the picture carefully: what can you see?\n- Read the text: what does it explain?\n- Find **what is the same** in both. A strong answer is supported by the picture **and** the text.\n\n## A small exercise\nWhich is a fact and which is an opinion?\n- Getuk is made from cassava.\n- Getuk is the tastiest snack.\n\n> Jaka says: \"Opinions can differ and should be respected. But facts must be checked first.\"\n\nSource: Reading literacy guidelines of the Ministry of Education (facts and opinions; reading multimodal texts).",
            ],
        ],
    ],
];
