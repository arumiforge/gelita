<?php

/**
 * Isi bank soal & Pustaka — WONOSOBO (wilayah 3, sulit).
 * Fokus literasi: MENILAI informasi dan sumbernya sebelum percaya/berbagi,
 * menyimpulkan, dan bertindak bertanggung jawab (empat pilar literasi
 * digital: cakap, etika, aman, budaya).
 *
 * Bentuk data: lihat docs/bank-soal/README.md. Teks berpasangan
 * [Indonesia, English]. Tambahkan butir baru di AKHIR daftar agar kode
 * butir yang sudah dipakai siswa tidak bergeser.
 */

$ref = [
    'region'  => 'Pemkab Wonosobo (Sejarah Singkat); Kompas Stori (2021) Sejarah Wonosobo; Wikipedia: Dataran Tinggi Dieng',
    'dieng'   => 'Wikipedia: Dataran Tinggi Dieng; Tempo & Kompas Travel (Desa Sembungan); BMKG via Kompas (Juli 2026) embun upas',
    'culture' => 'Indonesia Kaya: Tari Topeng Lengger; Kompas Stori (2022) Tari Lengger; BRIN Audiovisual: Bundengan; Pemprov Jateng (DCF)',
    'food'    => 'Indonesia Kaya: Mie Ongklok; Kompas Travel (2023) Kebun Teh Tambi; Wikipedia: Pepaya gunung',
    'digital' => 'Siberkreasi & Ditjen Aptika Kominfo: 4 Pilar Literasi Digital (Cakap, Etika, Aman, Budaya); Kemendikbudristek: literasi digital',
    'sources' => 'Panduan cek fakta Siberkreasi & Mafindo; laman resmi BMKG, PVMBG/MAGMA Indonesia',
];

$checked = 'Fakta dicocokkan dengan sumber pada kolom reference_source (Sep 2026). Tinjau oleh guru/ahli, lalu ubah review_status menjadi verified.';
$scenario = 'Butir skenario literasi digital/sikap. Kunci dicek tim terhadap pedoman 4 pilar literasi digital.';
$commons  = 'Wikimedia Commons — lisensi bebas; pengarang & lisensi di halaman berkas';

return [
    // Tantangan Wonosobo memakai teks sumber per butir (sumber A/B, informasi
    // pendek) alih-alih bacaan panjang, jadi tidak ada sheet passages.
    'passages' => [],

    // ============================================================ TANTANGAN
    'nodes' => [
        // ----------------------------------------------------------- wnb-1
        'wnb-1' => [
            'engine'          => 'puzzle',
            'indicator'       => 'literasi',
            'items_per_round' => 1,
            'note'            => $checked,
            'ref'             => $ref['food'],
            'title'           => ['Susun Urutan yang Benar', 'Put in the Right Order'],
            'instruction'     => ['Seret kartu atau pakai tombol panah untuk menyusun langkah dari yang pertama sampai terakhir.', 'Drag the cards or use the arrow buttons to put the steps in order from first to last.'],
            'description'     => [
                'Di negeri di atas awan, semua butuh urutan yang tepat — dari menyajikan mi ongklok sampai memeriksa kabar. Susun langkahnya agar tidak ada yang terlewat.',
                'In the land above the clouds, everything needs the right order — from serving ongklok noodles to checking the news. Arrange the steps so that nothing is missed.',
            ],
            'hints' => [
                ['Cari langkah yang harus dilakukan paling awal: biasanya menyiapkan atau membaca.', 'Find the step that must come first: usually preparing or reading.'],
                ['Cari langkah terakhir: biasanya menyajikan, memutuskan, atau membereskan.', 'Find the last step: usually serving, deciding, or tidying up.'],
                ['Tanyakan pada setiap kartu: "Bisakah ini dilakukan sebelum kartu sebelumnya?"', 'Ask about each card: "Could this be done before the card before it?"'],
            ],
            'items' => [
                [
                    'type'   => 'ordering',
                    'prompt' => ['Susun langkah menyajikan mi ongklok khas Wonosobo.', 'Put the steps for serving Wonosobo\'s ongklok noodles in order.'],
                    'order'  => 'c,e,a,d,b',
                    'pieces' => [
                        ['a', 'Tiriskan mi dan sayur, lalu tuang ke mangkuk.', 'Drain the noodles and vegetables, then pour them into a bowl.'],
                        ['b', 'Sajikan bersama sate sapi dan tempe kemul.', 'Serve with beef satay and tempe kemul.'],
                        ['c', 'Siapkan mi kuning, kol, dan daun kucai.', 'Prepare the yellow noodles, cabbage, and chives.'],
                        ['d', 'Siram dengan kuah kental dari tepung kanji.', 'Pour over the thick tapioca-starch sauce.'],
                        ['e', 'Masukkan mi dan sayur ke keranjang ongklok, lalu celupkan ke air mendidih.', 'Put the noodles and vegetables in the ongklok basket, then dip it into boiling water.'],
                    ],
                ],
                [
                    'type'      => 'ordering',
                    'indicator' => 'sikap',
                    'ref'       => $ref['digital'],
                    'note'      => $scenario,
                    'prompt'    => ['Susun langkah memeriksa sebuah kabar sebelum membagikannya.', 'Put the steps for checking a piece of news before sharing it in order.'],
                    'order'     => 'b,d,a,e,c',
                    'pieces'    => [
                        ['a', 'Bandingkan dengan sumber resmi atau media tepercaya.', 'Compare it with official sources or trusted media.'],
                        ['b', 'Baca judul dan isi kabar sampai habis.', 'Read the headline and the whole story.'],
                        ['c', 'Putuskan: bagikan hanya bila benar dan bermanfaat.', 'Decide: share it only if it is true and useful.'],
                        ['d', 'Periksa siapa pembuatnya dan kapan kabar itu dibuat.', 'Check who made it and when it was made.'],
                        ['e', 'Tanyakan kepada guru atau orang tua bila masih ragu.', 'Ask a teacher or parent if you are still unsure.'],
                    ],
                ],
                [
                    'type'   => 'ordering',
                    'ref'    => $ref['dieng'],
                    'prompt' => ['Susun urutan perjalanan melihat matahari terbit di Bukit Sikunir.', 'Put the steps of a trip to see the sunrise at Sikunir Hill in order.'],
                    'order'  => 'c,e,a,d,b',
                    'pieces' => [
                        ['a', 'Mendaki jalan setapak menuju puncak Bukit Sikunir.', 'Climb the footpath to the top of Sikunir Hill.'],
                        ['b', 'Turun kembali sambil membawa pulang semua sampah.', 'Walk back down, taking all your rubbish home.'],
                        ['c', 'Tiba di Desa Sembungan sebelum subuh dengan jaket tebal dan senter.', 'Arrive in Sembungan Village before dawn with a thick jacket and a torch.'],
                        ['d', 'Menunggu di puncak sampai matahari terbit.', 'Wait at the top until the sun rises.'],
                        ['e', 'Membeli tiket di pos masuk desa wisata.', 'Buy a ticket at the tourist village entrance post.'],
                    ],
                ],
                [
                    'type'      => 'ordering',
                    'indicator' => 'sikap',
                    'ref'       => $ref['digital'],
                    'note'      => $scenario,
                    'prompt'    => ['Susun langkah membuat kata sandi yang kuat.', 'Put the steps for making a strong password in order.'],
                    'order'     => 'b,d,a,e,c',
                    'pieces'    => [
                        ['a', 'Tambahkan angka dan simbol.', 'Add numbers and symbols.'],
                        ['b', 'Pikirkan satu kalimat yang mudah kamu ingat, tetapi sulit ditebak orang lain.', 'Think of a sentence that is easy for you to remember but hard for others to guess.'],
                        ['c', 'Simpan kata sandi itu sebagai rahasia dan jangan berikan kepada siapa pun.', 'Keep the password secret and never give it to anyone.'],
                        ['d', 'Ambil huruf awal setiap kata, pakai huruf besar dan kecil.', 'Take the first letter of each word, using capital and small letters.'],
                        ['e', 'Periksa lagi: jangan memuat nama, tanggal lahir, atau nama pengguna.', 'Check again: it must not contain your name, birthday, or username.'],
                    ],
                ],
            ],
        ],

        // ----------------------------------------------------------- wnb-2
        'wnb-2' => [
            'engine'           => 'rumpang',
            'indicator'        => 'literasi',
            'items_per_round'  => 4,
            'use_word_bank'    => true,
            'distractor_count' => 3,
            'note'             => $checked,
            'ref'              => $ref['dieng'],
            'title'            => ['Lengkapi Kesimpulan', 'Complete the Conclusion'],
            'instruction'      => ['Baca informasinya, lalu pilih kata yang membuat kesimpulan menjadi tepat.', 'Read the information, then choose the word that makes the conclusion correct.'],
            'description'      => [
                'Kesimpulan yang baik lahir dari informasi, bukan dari tebakan. Bantu Jaka menyimpulkan apa yang ia pelajari di Wonosobo.',
                'A good conclusion comes from information, not from guessing. Help Jaka draw conclusions from what he learned in Wonosobo.',
            ],
            'distractors' => [
                ['tipis', 'thin'], ['garam', 'salt'], ['gelap', 'dark'], ['terendah', 'lowest'],
                ['Jepang', 'Japanese'], ['hiasan', 'decoration'], ['piring', 'plate'],
            ],
            'hints' => [
                ['Kesimpulan harus sesuai isi informasi, tidak boleh menambah hal yang tidak ada.', 'The conclusion must match the information and must not add anything new.'],
                ['Cari kata di informasi yang menjelaskan "karena" atau "sehingga".', 'Look for words in the information that explain "because" or "so".'],
                ['Kata pengecoh sering berlawanan arti dengan jawaban, misalnya tinggi–rendah.', 'Decoy words often mean the opposite of the answer, such as high and low.'],
            ],
            'items' => [
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Pada musim kemarau, suhu pagi di Dieng bisa turun di bawah 0 °C sehingga embun membeku menjadi es.', 'In the dry season, morning temperatures in Dieng can drop below 0 °C, so the dew freezes into ice.'],
                    'prompt' => ['Kesimpulan: wisatawan yang datang pada musim kemarau sebaiknya membawa pakaian ___.', 'Conclusion: visitors who come in the dry season should bring ___ clothes.'],
                    'answer' => ['hangat', 'warm'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Dataran Tinggi Dieng berada lebih dari 2.000 meter di atas permukaan laut dan sering tertutup kabut.', 'The Dieng Plateau is more than 2,000 metres above sea level and is often covered in mist.'],
                    'prompt' => ['Kesimpulan: Dieng dijuluki negeri di atas awan karena letaknya sangat ___.', 'Conclusion: Dieng is called the land above the clouds because it is very ___.'],
                    'answer' => ['tinggi', 'high'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Air Telaga Warna mengandung belerang. Karena itu warnanya tampak berubah-ubah saat terkena cahaya matahari.', 'The water of Telaga Warna contains sulphur. That is why its colour seems to change in the sunlight.'],
                    'prompt' => ['Kesimpulan: warna Telaga Warna berubah karena kandungan ___ dalam airnya.', 'Conclusion: the colour of Telaga Warna changes because of the ___ in its water.'],
                    'answer' => ['belerang', 'sulphur'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Air Telaga Pengilon sangat jernih sehingga memantulkan pemandangan di sekitarnya seperti kaca.', 'The water of Telaga Pengilon is so clear that it reflects the scenery around it like glass.'],
                    'prompt' => ['Kesimpulan: nama "pengilon" cocok karena telaga itu seperti ___ raksasa.', 'Conclusion: the name "pengilon" fits because the lake is like a giant ___.'],
                    'answer' => ['cermin', 'mirror'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'ref'    => $ref['food'],
                    'source' => ['Carica atau pepaya gunung tumbuh baik di ketinggian 1.400–2.400 meter yang berudara sejuk, seperti Dieng.', 'Carica, or mountain papaya, grows well at 1,400–2,400 metres where the air is cool, like Dieng.'],
                    'prompt' => ['Kesimpulan: carica sulit tumbuh di dataran ___ yang panas.', 'Conclusion: carica struggles to grow in hot ___ areas.'],
                    'answer' => ['rendah', 'lowland'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'ref'    => $ref['food'],
                    'source' => ['Mi ongklok direbus di dalam keranjang bambu kecil bernama ongklok yang dicelup-celupkan ke air mendidih.', 'Ongklok noodles are boiled in a small bamboo basket called an ongklok, which is dipped again and again into boiling water.'],
                    'prompt' => ['Kesimpulan: nama mi ongklok berasal dari nama ___ untuk merebusnya.', 'Conclusion: ongklok noodles are named after the ___ used to boil them.'],
                    'answer' => ['alat', 'tool'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'ref'    => $ref['food'],
                    'source' => ['Kebun Teh Tambi dibuka tahun 1865, ketika Wonosobo masih berada di bawah pemerintahan Hindia Belanda.', 'Tambi Tea Estate was opened in 1865, when Wonosobo was still under the Dutch East Indies government.'],
                    'prompt' => ['Kesimpulan: Kebun Teh Tambi sudah ada sejak masa penjajahan ___.', 'Conclusion: Tambi Tea Estate has existed since the time of ___ colonial rule.'],
                    'answer' => ['Belanda', 'Dutch'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'ref'    => $ref['culture'],
                    'source' => ['Bundengan berasal dari kowangan, tudung anyaman bambu yang dipakai penggembala bebek untuk berlindung dari panas dan hujan.', 'The bundengan comes from the kowangan, a woven bamboo hood worn by duck herders to shelter from the sun and rain.'],
                    'prompt' => ['Kesimpulan: sebelum menjadi alat musik, bundengan berfungsi sebagai ___ bagi penggembala.', 'Conclusion: before it became an instrument, the bundengan served as a ___ for herders.'],
                    'answer' => ['pelindung', 'shelter'],
                ],
                [
                    'type'   => 'fill_blank_bank',
                    'source' => ['Desa Sembungan berada di ketinggian sekitar 2.300 meter, lebih tinggi daripada desa-desa lain di Pulau Jawa.', 'Sembungan Village lies at about 2,300 metres, higher than any other village in Java.'],
                    'prompt' => ['Kesimpulan: Desa Sembungan adalah desa ___ di Pulau Jawa.', 'Conclusion: Sembungan is the ___ village in Java.'],
                    'answer' => ['tertinggi', 'highest'],
                ],
                [
                    'type'      => 'fill_blank_bank',
                    'indicator' => 'sikap',
                    'ref'       => $ref['sources'],
                    'note'      => $scenario,
                    'source'    => ['Pesan berantai itu tidak mencantumkan sumber, tanggal, maupun nama pembuatnya.', 'The chain message does not show a source, a date, or the name of whoever made it.'],
                    'prompt'    => ['Kesimpulan: isi pesan itu belum dapat ___ sebelum diperiksa.', 'Conclusion: the message cannot be ___ until it has been checked.'],
                    'answer'    => ['dipercaya', 'trusted'],
                ],
            ],
        ],

        // ----------------------------------------------------------- wnb-3
        'wnb-3' => [
            'engine'          => 'boleh',
            'indicator'       => 'sikap',
            'items_per_round' => 6,
            'verdict_options' => 'benar,salah',
            'require_reason'  => false,
            'note'            => $scenario,
            'ref'             => $ref['sources'],
            'title'           => ['Sumber Mana yang Benar?', 'Which Source Is Right?'],
            'instruction'     => ['Bandingkan Sumber A dan Sumber B, lalu nilai apakah pernyataannya BENAR atau SALAH.', 'Compare Source A and Source B, then judge whether the statement is TRUE or FALSE.'],
            'description'     => [
                'Kabar tentang Dieng beredar cepat, tetapi tidak semuanya benar. Bandingkan dua sumber, percayai yang jelas pembuatnya, lalu putuskan.',
                'News about Dieng spreads fast, but not all of it is true. Compare two sources, trust the one whose maker is clear, then decide.',
            ],
            'hints' => [
                ['Periksa siapa pembuat setiap sumber: lembaga resmi atau akun tanpa nama?', 'Check who made each source: an official body or an account with no name?'],
                ['Pesan berantai dan komentar tanpa nama tidak otomatis salah, tetapi harus dicek dengan sumber resmi.', 'Chain messages and anonymous comments are not automatically wrong, but they must be checked against official sources.'],
                ['Kata "pasti", "sebarkan!", atau "semua orang juga begitu" sering menjadi tanda kabar yang perlu dicurigai.', 'Words like "definitely", "share this!", or "everyone does it" are often signs of news that needs checking.'],
            ],
            'items' => [
                [
                    'type' => 'verdict_card', 'answer' => 'salah',
                    'prompt'  => ['Pengunjung boleh memanjat bangunan candi di Dieng untuk berfoto.', 'Visitors may climb the temple buildings in Dieng to take photos.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Pengumuman pengelola cagar budaya: pengunjung dilarang memanjat bangunan candi demi menjaga kelestariannya.', 'Heritage site notice: visitors must not climb the temple buildings, to keep them preserved.'],
                        ['Sumber B', 'Source B', 'chain_message', 'Pesan berantai: naik ke atas candi aman kok, banyak yang melakukannya.', 'Chain message: climbing the temple is safe, lots of people do it.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'benar',
                    'prompt'  => ['Pengunjung sebaiknya membawa pulang atau membuang sampah di tempat sampah.', 'Visitors should take their rubbish home or put it in the bins.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Papan informasi pengelola kawasan wisata: buanglah sampah pada tempat yang disediakan.', 'Tourist area management board: put your rubbish in the bins provided.'],
                        ['Sumber B', 'Source B', 'anonymous', 'Komentar akun tanpa nama: sampah kecil dibuang sembarangan tidak apa-apa.', 'Anonymous comment: dropping small bits of rubbish anywhere is fine.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'benar', 'ref' => $ref['dieng'], 'note' => $checked,
                    'prompt'  => ['Embun es (embun upas) di Dieng biasanya muncul pada musim kemarau.', 'Frost (embun upas) in Dieng usually appears in the dry season.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Penjelasan BMKG: embun upas muncul saat kemarau, paling sering Juni sampai Agustus, ketika langit cerah dan udara malam sangat dingin.', 'BMKG explanation: frost appears in the dry season, most often from June to August, when the sky is clear and the nights are very cold.'],
                        ['Sumber B', 'Source B', 'blog', 'Tulisan blog pribadi: embun es di Dieng hanya muncul saat musim hujan.', 'Personal blog post: frost in Dieng only appears in the rainy season.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'salah', 'ref' => $ref['dieng'], 'note' => $checked,
                    'prompt'  => ['Air Telaga Warna aman untuk diminum langsung.', 'The water of Telaga Warna is safe to drink straight from the lake.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Informasi pengelola kawasan: air Telaga Warna mengandung belerang dan tidak untuk diminum.', 'Site management information: the water of Telaga Warna contains sulphur and is not for drinking.'],
                        ['Sumber B', 'Source B', 'chain_message', 'Pesan berantai: minum air Telaga Warna bikin awet muda, coba saja!', 'Chain message: drinking Telaga Warna water keeps you young, just try it!'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'salah',
                    'prompt'  => ['Festival budaya di Dieng tahun ini dibatalkan.', 'This year\'s cultural festival in Dieng has been cancelled.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Akun resmi dinas pariwisata: festival tetap berlangsung sesuai jadwal. Pantau info resmi hanya di akun ini.', 'Official tourism office account: the festival will go ahead as scheduled. Follow official updates only on this account.'],
                        ['Sumber B', 'Source B', 'chain_message', 'Pesan berantai tanpa tanggal: FESTIVAL BATAL!! Sebarkan ke semua grup!', 'Undated chain message: FESTIVAL CANCELLED!! Share this with every group!'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'benar', 'ref' => $ref['culture'], 'note' => $checked,
                    'prompt'  => ['Tari Topeng Lengger sudah ditetapkan sebagai Warisan Budaya Tak Benda Indonesia.', 'The Topeng Lengger dance has been recognised as Indonesian Intangible Cultural Heritage.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Laman kementerian bidang kebudayaan: Tari Topeng Lengger Wonosobo ditetapkan sebagai Warisan Budaya Tak Benda pada tahun 2020.', 'Culture ministry website: the Topeng Lengger dance of Wonosobo was recognised as Intangible Cultural Heritage in 2020.'],
                        ['Sumber B', 'Source B', 'anonymous', 'Komentar anonim: itu cuma tarian desa biasa, tidak pernah diakui siapa-siapa.', 'Anonymous comment: it is just an ordinary village dance, nobody has ever recognised it.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'salah', 'ref' => $ref['food'], 'note' => $checked,
                    'prompt'  => ['Carica bisa tumbuh subur di pantai yang panas.', 'Carica can grow well on hot beaches.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Dinas pertanian: carica cocok ditanam di ketinggian 1.400–2.400 meter yang berudara sejuk.', 'Agriculture office: carica suits cool places at 1,400–2,400 metres.'],
                        ['Sumber B', 'Source B', 'blog', 'Blog tanpa nama penulis: carica bisa ditanam di mana saja, termasuk di pantai.', 'Blog with no author name: carica can be planted anywhere, including on the beach.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'benar', 'ref' => $ref['dieng'], 'note' => $checked,
                    'prompt'  => ['Pendaki Bukit Sikunir perlu membawa jaket karena udara pagi sangat dingin.', 'Hikers at Sikunir Hill need a jacket because the morning air is very cold.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Pengelola desa wisata Sembungan: suhu subuh di puncak sangat dingin. Bawalah jaket, sarung tangan, dan senter.', 'Sembungan tourist village managers: dawn at the top is very cold. Bring a jacket, gloves, and a torch.'],
                        ['Sumber B', 'Source B', 'anonymous', 'Komentar anonim: tidak perlu jaket, di puncak hangat seperti di kota.', 'Anonymous comment: no jacket needed, the top is as warm as the city.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'salah',
                    'prompt'  => ['Jalan menuju Dieng ditutup total selama sebulan penuh.', 'The road to Dieng is completely closed for a whole month.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Akun resmi kepolisian setempat: hanya ada pengalihan arus sementara pada jam acara. Jalan tetap dibuka.', 'Official local police account: there is only a temporary diversion during the event hours. The road stays open.'],
                        ['Sumber B', 'Source B', 'chain_message', 'Pesan suara berantai: jalan ke Dieng ditutup sebulan, jangan ke sana!', 'Forwarded voice message: the road to Dieng is closed for a month, do not go!'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'benar', 'ref' => $ref['culture'], 'note' => $checked,
                    'prompt'  => ['Bundengan dimainkan dengan cara dipetik.', 'The bundengan is played by plucking.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Laman lembaga riset nasional: pemain bundengan memetik senar dan bilah bambu sehingga bunyinya menyerupai gamelan.', 'National research agency website: bundengan players pluck strings and bamboo blades so that it sounds like a gamelan.'],
                        ['Sumber B', 'Source B', 'blog', 'Blog tanpa sumber: bundengan dimainkan dengan cara ditiup seperti seruling.', 'Blog with no sources: the bundengan is played by blowing into it like a flute.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'benar', 'ref' => $ref['food'], 'note' => $checked,
                    'prompt'  => ['Mi ongklok disajikan dengan kuah yang kental.', 'Ongklok noodles are served with a thick sauce.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Laman pariwisata daerah: kuah mi ongklok kental karena dibuat dari tepung kanji, ebi, dan gula jawa.', 'Regional tourism website: ongklok sauce is thick because it is made from tapioca starch, dried shrimp, and palm sugar.'],
                        ['Sumber B', 'Source B', 'anonymous', 'Komentar anonim: kuah mi ongklok bening dan encer seperti air.', 'Anonymous comment: ongklok sauce is clear and runny like water.'],
                    ],
                ],
                [
                    'type' => 'verdict_card', 'answer' => 'salah',
                    'prompt'  => ['Kawah di Dieng boleh didekati sesuka hati tanpa mengikuti rambu.', 'Craters in Dieng can be approached freely without following the signs.'],
                    'sources' => [
                        ['Sumber A', 'Source A', 'official', 'Badan vulkanologi (PVMBG): ikuti rambu dan jaga jarak aman; gas di sekitar kawah dapat berbahaya.', 'Volcanology agency (PVMBG): follow the signs and keep a safe distance; gas around craters can be dangerous.'],
                        ['Sumber B', 'Source B', 'chain_message', 'Pesan berantai: aman kok, dekati saja kawahnya biar fotonya bagus.', 'Chain message: it is safe, just go close to the crater for a nice photo.'],
                    ],
                ],
            ],
        ],

        // ----------------------------------------------------------- wnb-4
        'wnb-4' => [
            'engine'          => 'pilihan',
            'indicator'       => 'sikap',
            'items_per_round' => 4,
            'note'            => $scenario,
            'ref'             => $ref['sources'],
            'title'           => ['Pilih Sumber Terpercaya', 'Choose the Trusted Source'],
            'instruction'     => ['Baca situasinya, lalu pilih sumber informasi yang paling dapat dipercaya.', 'Read the situation, then choose the most trustworthy source of information.'],
            'description'     => [
                'Informasi datang dari mana-mana: grup percakapan, komentar, blog, dan lembaga resmi. Pilih sumber yang paling layak dipercaya untuk setiap kebutuhan.',
                'Information comes from everywhere: chat groups, comments, blogs, and official bodies. Choose the source that deserves the most trust for each need.',
            ],
            'hints' => [
                ['Sumber tepercaya jelas siapa pembuatnya dan bertanggung jawab atas isinya.', 'A trustworthy source clearly shows who made it and takes responsibility for it.'],
                ['Pilih lembaga yang memang bertugas di bidang itu: cuaca → BMKG, gunung api → PVMBG, sekolah → pihak sekolah.', 'Choose the body whose job it is: weather → BMKG, volcanoes → PVMBG, school → the school itself.'],
                ['Informasi lama atau tanpa tanggal bisa sudah tidak berlaku.', 'Old or undated information may no longer be correct.'],
            ],
            'items' => [
                [
                    'type' => 'source_trust',
                    'prompt' => ['Kamu ingin tahu jadwal festival budaya di Dieng. Sumber mana yang paling dapat dipercaya?', 'You want to know the schedule of the cultural festival in Dieng. Which source is the most trustworthy?'],
                    'options' => [
                        ['Laman atau akun resmi panitia dan dinas pariwisata', 'The official website or account of the organisers and tourism office', true, 'Tepat! Pembuat jadwal adalah sumber paling tepat.', 'Correct! The people who make the schedule are the best source.'],
                        ['Pesan berantai tanpa nama pengirim', 'A chain message with no sender name', false, 'Tidak jelas siapa pembuatnya, jadi perlu dicek.', 'It is not clear who made it, so it needs checking.'],
                        ['Komentar akun anonim di media sosial', 'A comment from an anonymous social media account', false, 'Akun tanpa identitas tidak bertanggung jawab atas isinya.', 'An account with no identity takes no responsibility for what it says.'],
                        ['Tebakan teman sekelas', 'A classmate\'s guess', false, 'Tebakan bukan informasi.', 'A guess is not information.'],
                    ],
                ],
                [
                    'type' => 'source_trust',
                    'prompt' => ['Kamu mencari prakiraan cuaca dan suhu Dieng untuk besok pagi. Sumber mana yang paling dapat dipercaya?', 'You are looking for tomorrow morning\'s weather and temperature forecast for Dieng. Which source is the most trustworthy?'],
                    'options' => [
                        ['Unggahan foto embun es dari tahun lalu', 'A photo of frost posted last year', false, 'Foto lama tidak menunjukkan cuaca besok.', 'An old photo does not show tomorrow\'s weather.'],
                        ['Laman atau aplikasi resmi BMKG', 'The official BMKG website or app', true, 'Tepat! BMKG adalah lembaga resmi cuaca di Indonesia.', 'Correct! BMKG is Indonesia\'s official weather agency.'],
                        ['Pesan berantai di grup keluarga', 'A chain message in the family group', false, 'Pesan berantai tidak jelas sumbernya.', 'Chain messages have unclear sources.'],
                        ['Gambar tanpa keterangan tanggal', 'A picture with no date', false, 'Tanpa tanggal, kita tidak tahu apakah masih berlaku.', 'Without a date, we cannot tell if it is still valid.'],
                    ],
                ],
                [
                    'type' => 'source_trust',
                    'prompt' => ['Kamu ingin tahu aturan berkunjung ke kompleks candi di Dieng. Sumber mana yang paling tepat?', 'You want to know the rules for visiting the temple complex in Dieng. Which source is best?'],
                    'options' => [
                        ['Cerita kakak kelas yang pernah ke sana', 'A story from an older pupil who went there once', false, 'Pengalaman orang lain bisa berbeda atau sudah lama.', 'Other people\'s experiences may differ or be out of date.'],
                        ['Blog perjalanan tanpa tanggal tulisan', 'A travel blog with no posting date', false, 'Tanpa tanggal, aturannya mungkin sudah berubah.', 'Without a date, the rules may have changed.'],
                        ['Pengumuman resmi pengelola cagar budaya', 'The official notice from the heritage site managers', true, 'Tepat! Pengelola yang membuat dan menegakkan aturan.', 'Correct! The managers make and enforce the rules.'],
                        ['Komentar di sebuah video lucu', 'A comment under a funny video', false, 'Komentar bukan sumber aturan resmi.', 'Comments are not an official source of rules.'],
                    ],
                ],
                [
                    'type' => 'source_trust',
                    'prompt' => ['Terdengar kabar kawah di Dieng sedang aktif. Sumber mana yang paling dapat dipercaya untuk memeriksanya?', 'You hear that a crater in Dieng is active. Which source is the most trustworthy for checking this?'],
                    'options' => [
                        ['Badan vulkanologi (PVMBG) atau aplikasi MAGMA Indonesia', 'The volcanology agency (PVMBG) or the MAGMA Indonesia app', true, 'Tepat! PVMBG bertugas memantau gunung api dan kawah.', 'Correct! PVMBG\'s job is to monitor volcanoes and craters.'],
                        ['Video viral tanpa keterangan sumber', 'A viral video with no source', false, 'Video viral bisa lama, dipotong, atau dari tempat lain.', 'Viral videos can be old, edited, or from another place.'],
                        ['Akun gosip di media sosial', 'A gossip account on social media', false, 'Akun gosip tidak bertugas memantau gunung api.', 'Gossip accounts do not monitor volcanoes.'],
                        ['Pesan suara berantai', 'A forwarded voice message', false, 'Tidak jelas siapa pembicaranya.', 'It is not clear who is speaking.'],
                    ],
                ],
                [
                    'type' => 'source_trust', 'ref' => $ref['region'], 'note' => $checked,
                    'prompt' => ['Kamu menulis tugas tentang hari jadi Kabupaten Wonosobo. Sumber mana yang paling tepat?', 'You are writing an assignment about the anniversary of Wonosobo Regency. Which source is best?'],
                    'options' => [
                        ['Meme lucu tentang Wonosobo', 'A funny meme about Wonosobo', false, 'Meme dibuat untuk hiburan, bukan sumber sejarah.', 'Memes are made for fun, not as history sources.'],
                        ['Laman resmi atau buku sejarah terbitan Pemkab Wonosobo', 'The official website or a history book published by the Wonosobo government', true, 'Tepat! Sumber resmi pemerintah daerah jelas penanggung jawabnya.', 'Correct! Official local government sources have a clear owner.'],
                        ['Komentar di video jalan-jalan', 'A comment under a travel video', false, 'Komentar tidak bisa dicek kebenarannya.', 'Comments cannot be checked.'],
                        ['Tebakan teman di grup kelas', 'A friend\'s guess in the class group', false, 'Tebakan bukan fakta.', 'A guess is not a fact.'],
                    ],
                ],
                [
                    'type' => 'source_trust',
                    'prompt' => ['Kamu ingin tahu manfaat buah carica bagi kesehatan. Sumber mana yang paling dapat dipercaya?', 'You want to know the health benefits of carica fruit. Which source is the most trustworthy?'],
                    'options' => [
                        ['Iklan yang menjanjikan carica menyembuhkan semua penyakit', 'An advert promising carica cures every illness', false, 'Iklan ingin menjual; janji "semua penyakit" patut dicurigai.', 'Adverts want to sell; a promise to cure "every illness" is suspicious.'],
                        ['Testimoni tanpa nama di kolom komentar', 'An anonymous testimonial in the comments', false, 'Testimoni tanpa nama tidak dapat dicek.', 'Anonymous testimonials cannot be checked.'],
                        ['Artikel ahli gizi dari lembaga kesehatan yang jelas', 'An article by a nutritionist from a known health institution', true, 'Tepat! Ahli di bidangnya dan lembaganya jelas.', 'Correct! An expert in the field from a known institution.'],
                        ['Pesan berantai dari grup tetangga', 'A chain message from the neighbours\' group', false, 'Pesan berantai tidak jelas sumbernya.', 'Chain messages have unclear sources.'],
                    ],
                ],
                [
                    'type' => 'source_trust',
                    'prompt' => ['Beredar kabar sekolah libur besok. Dari mana sebaiknya kamu memastikannya?', 'There is a rumour that school is closed tomorrow. Where should you check it?'],
                    'options' => [
                        ['Tangkapan layar tanpa kop surat sekolah', 'A screenshot with no school letterhead', false, 'Tangkapan layar mudah dipalsukan.', 'Screenshots are easy to fake.'],
                        ['Pesan berantai di grup bermain', 'A chain message in the game group', false, 'Belum tentu berasal dari sekolah.', 'It may not come from the school at all.'],
                        ['Kabar dari kakak kelas', 'News from an older pupil', false, 'Bisa saja salah dengar.', 'They might have heard it wrong.'],
                        ['Pengumuman resmi sekolah atau wali kelas', 'The official school announcement or your class teacher', true, 'Tepat! Sekolah yang berwenang menentukan hari libur.', 'Correct! The school decides when there is a holiday.'],
                    ],
                ],
                [
                    'type' => 'source_trust',
                    'prompt' => ['Kamu ingin tahu harga tiket masuk Telaga Warna tahun ini. Sumber mana yang paling tepat?', 'You want to know this year\'s entry price for Telaga Warna. Which source is best?'],
                    'options' => [
                        ['Laman atau loket resmi pengelola kawasan', 'The official website or ticket office of the site managers', true, 'Tepat! Pengelola yang menetapkan harga terbaru.', 'Correct! The managers set the latest price.'],
                        ['Ulasan wisatawan dari tahun 2019', 'A visitor review from 2019', false, 'Harga lama mungkin sudah berubah.', 'The old price may have changed.'],
                        ['Komentar akun anonim', 'A comment from an anonymous account', false, 'Tidak jelas pembuatnya.', 'The maker is unclear.'],
                        ['Tebakan teman', 'A friend\'s guess', false, 'Tebakan bukan informasi.', 'A guess is not information.'],
                    ],
                ],
            ],
        ],

        // ----------------------------------------------------------- wnb-5
        'wnb-5' => [
            'engine'          => 'pilihan',
            'indicator'       => 'sikap',
            'items_per_round' => 3,
            'note'            => $scenario,
            'ref'             => $ref['digital'],
            'title'           => ['Apa yang Sebaiknya Kamu Lakukan?', 'What Should You Do?'],
            'instruction'     => ['Bacalah situasinya, lalu pilih tindakan yang paling tepat dan bertanggung jawab.', 'Read the situation, then choose the most appropriate and responsible action.'],
            'description'     => [
                'Serpihan Cahaya terakhir hanya menyala untuk anak yang bijak di dunia digital. Pilih tindakan yang aman, sopan, cakap, dan menghargai budaya.',
                'The last Shard of Light only shines for a child who is wise in the digital world. Choose actions that are safe, polite, skilful, and respectful of culture.',
            ],
            'hints' => [
                ['Pikirkan akibat setiap pilihan bagi dirimu dan orang lain.', 'Think about what each choice means for you and for others.'],
                ['Data pribadi — kata sandi, kode OTP, alamat rumah — tidak boleh dibagikan.', 'Personal data — passwords, OTP codes, home addresses — must never be shared.'],
                ['Bila ragu, berhenti dulu dan tanyakan kepada guru atau orang tua.', 'If in doubt, stop first and ask a teacher or parent.'],
            ],
            'items' => [
                [
                    'type' => 'single_choice', 'pillar' => 'digital_safety',
                    'prompt' => ['Temanmu meminta kata sandi akun gamemu supaya bisa membantu menaikkan level. Apa yang sebaiknya kamu lakukan?', 'A friend asks for your game account password so they can help you level up. What should you do?'],
                    'options' => [
                        ['Memberikannya karena dia teman dekat', 'Give it because they are a close friend', false, 'Teman dekat pun tidak boleh tahu kata sandimu.', 'Even close friends should not know your password.'],
                        ['Menuliskannya di papan kelas agar tidak lupa', 'Write it on the class board so you do not forget', false, 'Semua orang jadi bisa melihatnya.', 'Then everyone can see it.'],
                        ['Menolak dengan sopan dan menjaga kata sandi tetap rahasia', 'Politely refuse and keep the password secret', true, 'Tepat! Kata sandi hanya untuk dirimu sendiri.', 'Correct! Your password is only for you.'],
                        ['Mengganti kata sandi menjadi 12345 lalu memberikannya', 'Change it to 12345 and then give it', false, '12345 sangat mudah ditebak.', '12345 is very easy to guess.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_safety',
                    'prompt' => ['Muncul pesan: "Selamat! Kamu dapat pulsa gratis. Klik tautan ini dan kirim kode OTP dari SMS." Apa yang sebaiknya kamu lakukan?', 'A message says: "Congratulations! You won free phone credit. Click this link and send the OTP code from your SMS." What should you do?'],
                    'options' => [
                        ['Mengklik tautannya dan mengirim kode OTP', 'Click the link and send the OTP code', false, 'Kode OTP adalah kunci akun; penipu memakainya untuk mengambil alih akun.', 'An OTP code is a key to your account; scammers use it to take it over.'],
                        ['Tidak mengklik, tidak membalas, dan memberi tahu orang tua', 'Do not click, do not reply, and tell a parent', true, 'Tepat! Ini ciri penipuan: hadiah mendadak dan permintaan kode rahasia.', 'Correct! These are signs of a scam: a sudden prize and a request for a secret code.'],
                        ['Meneruskannya ke teman-teman agar mereka juga dapat', 'Forward it so your friends can win too', false, 'Kamu malah menyebarkan penipuan.', 'You would be spreading the scam.'],
                        ['Membalas dengan alamat rumah agar hadiah dikirim', 'Reply with your home address so the prize can be sent', false, 'Alamat rumah adalah data pribadi.', 'Your home address is personal data.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_ethics',
                    'prompt' => ['Kamu memotret temanmu saat ia terjatuh dan fotonya terlihat lucu. Apa yang sebaiknya kamu lakukan?', 'You took a photo of your friend falling over and it looks funny. What should you do?'],
                    'options' => [
                        ['Langsung mengunggahnya agar semua tertawa', 'Post it straight away so everyone laughs', false, 'Temanmu bisa malu dan sedih.', 'Your friend could feel embarrassed and hurt.'],
                        ['Mengirimnya ke grup kelas dengan keterangan mengejek', 'Send it to the class group with a mocking caption', false, 'Itu termasuk perundungan.', 'That is a form of bullying.'],
                        ['Meminta izin temanmu dulu dan menghapusnya bila ia tidak setuju', 'Ask your friend first and delete it if they say no', true, 'Tepat! Menghormati perasaan dan privasi orang lain adalah etika digital.', 'Correct! Respecting other people\'s feelings and privacy is digital ethics.'],
                        ['Menyimpannya untuk dipakai mengancam nanti', 'Keep it to use as a threat later', false, 'Mengancam orang lain tidak pernah dibenarkan.', 'Threatening others is never acceptable.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_ethics',
                    'prompt' => ['Kamu memakai foto Telaga Warna dari internet untuk poster tugas sekolah. Apa yang sebaiknya kamu lakukan?', 'You use a photo of Telaga Warna from the internet for a school poster. What should you do?'],
                    'options' => [
                        ['Menulis nama pembuat dan sumber foto di poster', 'Write the photographer\'s name and the source on the poster', true, 'Tepat! Mencantumkan sumber menghargai karya orang lain.', 'Correct! Crediting the source respects other people\'s work.'],
                        ['Mengaku foto itu hasil jepretanmu sendiri', 'Say you took the photo yourself', false, 'Mengaku karya orang lain adalah tidak jujur.', 'Claiming someone else\'s work is dishonest.'],
                        ['Menghapus tanda air (watermark) pembuatnya', 'Remove the photographer\'s watermark', false, 'Tanda air menunjukkan pemilik karya; menghapusnya tidak etis.', 'A watermark shows who owns the work; removing it is unethical.'],
                        ['Memakai foto tanpa memeriksa apa pun', 'Use the photo without checking anything', false, 'Periksa dulu izin pemakaiannya dan cantumkan sumbernya.', 'Check the permission to use it first and credit the source.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_skills',
                    'prompt' => ['Kamu ingin mencari informasi resmi tentang Dieng Culture Festival. Cara mana yang paling cakap?', 'You want to find official information about the Dieng Culture Festival. Which way is the most skilful?'],
                    'options' => [
                        ['Mengetik "festival" saja lalu membuka hasil pertama', 'Type just "festival" and open the first result', false, 'Kata kunci terlalu umum; hasilnya bisa festival lain.', 'The keyword is too general; you may get other festivals.'],
                        ['Mengetik "Dieng Culture Festival jadwal resmi" lalu memilih laman lembaga resmi', 'Type "Dieng Culture Festival official schedule" and choose an official body\'s page', true, 'Tepat! Kata kunci yang spesifik dan memilih sumber resmi adalah kecakapan digital.', 'Correct! Specific keywords and choosing an official source are digital skills.'],
                        ['Bertanya di kolom komentar video acak', 'Ask in the comments of a random video', false, 'Jawabannya belum tentu benar.', 'The answers may not be correct.'],
                        ['Menunggu pesan berantai di grup', 'Wait for a chain message in a group', false, 'Pesan berantai tidak jelas sumbernya.', 'Chain messages have unclear sources.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_skills',
                    'prompt' => ['Kamu sedang mengerjakan tugas di komputer sekolah. Bagaimana agar tugasmu tidak hilang?', 'You are working on an assignment on a school computer. How can you make sure it is not lost?'],
                    'options' => [
                        ['Tidak menyimpannya sampai tugas selesai', 'Do not save it until it is finished', false, 'Bila listrik padam, pekerjaanmu hilang.', 'If the power goes out, your work is gone.'],
                        ['Menyimpannya sesekali dengan nama berkas asal-asalan', 'Save it now and then with a random file name', false, 'Nama asal-asalan membuat berkas sulit ditemukan.', 'Random names make files hard to find.'],
                        ['Menyimpan berkala dengan nama jelas dan membuat salinan cadangan', 'Save regularly with a clear name and keep a backup copy', true, 'Tepat! Menyimpan berkala dan membuat cadangan adalah kebiasaan cakap digital.', 'Correct! Saving regularly and keeping a backup are good digital habits.'],
                        ['Meminta teman mengingat isinya', 'Ask a friend to remember what you wrote', false, 'Ingatan teman bukan tempat menyimpan berkas.', 'A friend\'s memory is not a place to store files.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_culture', 'ref' => $ref['digital'] . '; ' . $ref['culture'],
                    'prompt' => ['Kamu merekam pentas Tari Topeng Lengger dan ingin mengunggahnya. Apa yang paling tepat?', 'You filmed a Topeng Lengger dance performance and want to post it. What is the best thing to do?'],
                    'options' => [
                        ['Mengunggah dengan keterangan yang benar, menyebut nama sanggar, dan memakai kata-kata sopan', 'Post it with a correct caption, name the dance group, and use polite words', true, 'Tepat! Kamu ikut memperkenalkan budaya dengan cara yang menghargai.', 'Correct! You help share culture in a respectful way.'],
                        ['Menambahkan efek yang mengolok-olok penarinya', 'Add effects that make fun of the dancers', false, 'Mengolok-olok budaya tidak menghargai para pelakunya.', 'Mocking culture disrespects the people who keep it alive.'],
                        ['Menulis keterangan palsu bahwa tarian itu dari luar negeri', 'Write a false caption saying the dance is from abroad', false, 'Informasi palsu merugikan budaya asli.', 'False information harms the real culture.'],
                        ['Mengaku sebagai penari utama', 'Claim to be the lead dancer', false, 'Tidak jujur dan tidak menghargai penari asli.', 'Dishonest and disrespectful to the real dancers.'],
                    ],
                ],
                [
                    'type' => 'single_choice', 'pillar' => 'digital_culture',
                    'prompt' => ['Di kolom komentar, seseorang mengejek budaya daerah lain. Apa yang sebaiknya kamu lakukan?', 'In the comments, someone mocks the culture of another region. What should you do?'],
                    'options' => [
                        ['Ikut mengejek agar dianggap lucu', 'Join in so people think you are funny', false, 'Ikut mengejek memperburuk suasana.', 'Joining in makes things worse.'],
                        ['Membalas dengan kata-kata kasar', 'Reply with rude words', false, 'Kata kasar tidak menyelesaikan masalah.', 'Rude words do not solve anything.'],
                        ['Tidak ikut mengejek, menulis tanggapan sopan, atau melaporkannya bila perlu', 'Do not join in, write a polite reply, or report it if needed', true, 'Tepat! Menghargai keberagaman adalah bagian budaya digital.', 'Correct! Respecting diversity is part of digital culture.'],
                        ['Menyebarkan tangkapan layarnya dengan komentar marah', 'Share a screenshot of it with angry comments', false, 'Menyebarkan kemarahan membuat ejekan makin luas.', 'Spreading anger spreads the mockery further.'],
                    ],
                ],
            ],
        ],
    ],

    // ============================================================ PUSTAKA
    'library' => [
        [
            'title' => ['Selamat Datang di Wonosobo', 'Welcome to Wonosobo'],
            'body'  => [
                "**Kabupaten Wonosobo** terletak di jantung Jawa Tengah, di antara Gunung Sindoro, Gunung Sumbing, dan Pegunungan Dieng.\n\n## Wonosobo dalam angka\n- Luas wilayah sekitar **984,68 km²**.\n- Ketinggiannya **250 sampai 2.250 meter** di atas permukaan laut; separuh wilayahnya berada di ketinggian 500–1.000 meter.\n- Hari jadinya diperingati setiap **24 Juli**, mengenang perpindahan pusat pemerintahan pada **24 Juli 1825**.\n\n## Asal-usul nama\nNama Wonosobo dikaitkan dengan dusun **Wanasaba** yang didirikan Kiai Wanasaba. Dalam bahasa Jawa, *wana* berarti hutan dan *saba* berarti tempat berkumpul — jadi Wonosobo dapat dimaknai **tempat berkumpul di hutan**.\n\n## Negeri di atas awan\nKarena letaknya tinggi, udara Wonosobo sejuk sampai dingin. Kabut sering turun, dan dari tempat tinggi awan terlihat berada di bawah kita.\n\n> Tahukah kamu? Kata *wana* dan *saba* juga muncul dalam nama dua bagian kisah Mahabharata: *Wanaparwa* dan *Sabhaparwa*.\n\nSumber: Pemkab Wonosobo (Sejarah Singkat); Kompas Stori (2021, 2022); Good News From Indonesia.",
                "**Wonosobo Regency** lies in the heart of Central Java, between Mount Sindoro, Mount Sumbing, and the Dieng Mountains.\n\n## Wonosobo in numbers\n- Its area is about **984.68 km²**.\n- It lies **250 to 2,250 metres** above sea level; half of the region is at 500–1,000 metres.\n- Its anniversary is celebrated every **24 July**, remembering the move of the seat of government on **24 July 1825**.\n\n## Where the name comes from\nThe name Wonosobo is linked to **Wanasaba** hamlet, founded by Kiai Wanasaba. In Javanese, *wana* means forest and *saba* means gathering place — so Wonosobo can mean **a gathering place in the forest**.\n\n## The land above the clouds\nBecause it is so high, Wonosobo's air is cool to cold. Mist often rolls in, and from high places the clouds seem to be below you.\n\n> Did you know? The words *wana* and *saba* also appear in the names of two parts of the Mahabharata epic: *Wanaparwa* and *Sabhaparwa*.\n\nSources: Wonosobo Regency Government (Brief History); Kompas Stori (2021, 2022); Good News From Indonesia.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Malam_di_Wonosobo.jpg', 'caption' => ['Suasana malam di Kota Wonosobo', 'Wonosobo town at night'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Lambang_Kabupaten_Wonosobo.webp', 'caption' => ['Lambang Kabupaten Wonosobo', 'Coat of arms of Wonosobo Regency'], 'credit' => 'Wikimedia Commons — lambang daerah; rincian di halaman berkas'],
            ],
        ],
        [
            'title' => ['Dieng, Tempat Para Dewa', 'Dieng, the Place of the Gods'],
            'body'  => [
                "**Dataran Tinggi Dieng** adalah kawasan pegunungan di ketinggian **lebih dari 2.000 meter**. Namanya berasal dari bahasa Jawa Kuno *Di* (tempat, gunung) dan *Hyang* (dewa), sehingga Dieng berarti **tempat para dewa bersemayam**.\n\n## Satu dataran, beberapa kabupaten\nDieng tidak hanya milik Wonosobo. Secara pemerintahan, kawasan ini terbagi antara **Kecamatan Kejajar (Wonosobo)**, **Kecamatan Batur dan sebagian Pejawaran (Banjarnegara)**, serta sebagian **Kabupaten Batang**. Pusat wisatanya ada di **Desa Dieng Kulon (Banjarnegara)** dan **Desa Dieng atau Dieng Wetan (Wonosobo)**.\n\n## Dari gunung api purba\nDieng terbentuk oleh kegiatan gunung api. Karena itu di sini ada kawah, telaga, sumber air panas, dan tanah yang subur untuk kentang serta sayuran.\n\n## Menjelajah dengan bijak\n- Ikuti rambu di sekitar kawah; gasnya dapat berbahaya.\n- Bawa jaket — pagi hari sangat dingin.\n- Periksa informasi resmi sebelum berangkat, terutama saat cuaca buruk.\n\n> Tahukah kamu? Petani Dieng terkenal dengan kentangnya. Udara dingin dan tanah vulkanik membuat kentang tumbuh baik.\n\nSumber: Wikipedia (Dataran Tinggi Dieng); Geopark Dieng; Dinas Pariwisata Banjarnegara & Wonosobo.",
                "The **Dieng Plateau** is a mountain area at **more than 2,000 metres**. Its name comes from Old Javanese *Di* (place, mountain) and *Hyang* (gods), so Dieng means **the place where the gods dwell**.\n\n## One plateau, several regencies\nDieng does not belong only to Wonosobo. For government purposes, it is shared between **Kejajar District (Wonosobo)**, **Batur District and part of Pejawaran (Banjarnegara)**, and part of **Batang Regency**. Its tourism centres are **Dieng Kulon Village (Banjarnegara)** and **Dieng or Dieng Wetan Village (Wonosobo)**.\n\n## Made by ancient volcanoes\nDieng was formed by volcanic activity. That is why it has craters, lakes, hot springs, and soil that is good for potatoes and vegetables.\n\n## Exploring wisely\n- Follow the signs around craters; the gas can be dangerous.\n- Bring a jacket — mornings are very cold.\n- Check official information before you go, especially in bad weather.\n\n> Did you know? Dieng farmers are famous for their potatoes. The cold air and volcanic soil help potatoes grow well.\n\nSources: Wikipedia (Dieng Plateau); Dieng Geopark; Banjarnegara & Wonosobo tourism offices.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Dieng_Plateau_view_from_complex_of_Candi_Arjuna.JPG', 'caption' => ['Pemandangan Dataran Tinggi Dieng', 'A view of the Dieng Plateau'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Peta_Kawasan_Dataran_Tinggi_Dieng.jpg', 'caption' => ['Peta kawasan Dataran Tinggi Dieng', 'Map of the Dieng Plateau area'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Telaga Warna dan Telaga Pengilon', 'Telaga Warna and Telaga Pengilon'],
            'body'  => [
                "Di **Desa Dieng Wetan, Kecamatan Kejajar**, dua telaga cantik berdampingan di ketinggian sekitar 2.000 meter.\n\n## Telaga Warna — si pengubah warna\nAir **Telaga Warna** mengandung **belerang** cukup tinggi. Saat terkena cahaya matahari, permukaannya tampak berganti warna: hijau, kebiruan, putih, bahkan gelap. Karena kandungan belerangnya, ikan sulit hidup di telaga ini, dan airnya **tidak untuk diminum**.\n\n## Telaga Pengilon — si cermin\nTepat di sebelahnya, **Telaga Pengilon** berair sangat jernih. Permukaannya memantulkan pepohonan dan langit seperti kaca. Karena itu warga menamainya *pengilon*, bahasa Jawa untuk **cermin**. Berbeda dengan Telaga Warna, ikan dapat hidup di Telaga Pengilon.\n\n## Belajar dari dua telaga\nDua telaga yang bersebelahan ternyata sangat berbeda. Ini mengajari kita untuk **tidak menyimpulkan terlalu cepat** — periksa dulu, baru putuskan.\n\n> Tahukah kamu? Fotografer Belanda Isidore van Kinsbergen sudah memotret kawasan Telaga Warna dan Telaga Pengilon sekitar tahun 1864–1869.\n\nSumber: Badan Penghubung Pemprov Jateng; Jogjasuper (Telaga Warna & Pengilon); Wikimedia Commons (arsip KITLV).",
                "In **Dieng Wetan Village, Kejajar District**, two beautiful lakes lie side by side at about 2,000 metres.\n\n## Telaga Warna — the colour-changing lake\nThe water of **Telaga Warna** contains quite a lot of **sulphur**. In sunlight its surface seems to change colour: green, bluish, white, or even dark. Because of the sulphur, fish struggle to live in this lake, and the water is **not for drinking**.\n\n## Telaga Pengilon — the mirror\nRight next to it, **Telaga Pengilon** has very clear water. Its surface reflects the trees and sky like glass. That is why people named it *pengilon*, Javanese for **mirror**. Unlike Telaga Warna, fish can live in Telaga Pengilon.\n\n## Learning from two lakes\nTwo lakes side by side turn out to be very different. This teaches us **not to jump to conclusions** — check first, then decide.\n\n> Did you know? The Dutch photographer Isidore van Kinsbergen photographed the area around Telaga Warna and Telaga Pengilon in about 1864–1869.\n\nSources: Central Java Provincial Liaison Office; Jogjasuper (Telaga Warna & Pengilon); Wikimedia Commons (KITLV archive).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Telaga_Warna_Dieng_Jawa_Tengah.jpg', 'caption' => ['Telaga Warna di Dataran Tinggi Dieng', 'Telaga Warna on the Dieng Plateau'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Telaga_Warna_in_The_Dieng_Plateau.jpg', 'caption' => ['Warna air Telaga Warna yang tampak berubah-ubah', 'The changing colours of Telaga Warna'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Telaga_Warna_Dieng_02.jpg', 'caption' => ['Telaga Warna dan pepohonan di sekelilingnya', 'Telaga Warna and the trees around it'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Sembungan dan Sikunir', 'Sembungan and Sikunir'],
            'body'  => [
                "## Desa tertinggi di Pulau Jawa\n**Desa Sembungan** di Kecamatan Kejajar berada di ketinggian sekitar **2.300 meter** — desa **tertinggi di Pulau Jawa**. Udaranya sangat dingin, tetapi warganya tetap memulai kegiatan sejak pagi, bertani kentang dan sayuran di lereng.\n\n## Golden sunrise di Bukit Sikunir\nDari Sembungan, wisatawan mendaki jalan setapak ke puncak **Bukit Sikunir** sebelum subuh. Di sana mereka menanti matahari terbit keemasan yang disebut **golden sunrise**. Bila cerah, tampak pula Gunung Sindoro, Sumbing, dan gunung-gunung lain di kejauhan.\n\n## Telaga Cebong\nDi kaki bukit ada **Telaga Cebong**, telaga di lembah yang dikelilingi ladang hijau bertingkat. Banyak wisatawan berkemah di tepinya.\n\n## Wisata yang bertanggung jawab\nSekitar **250.000 wisatawan** datang ke Sembungan setiap tahun. Supaya alamnya tetap indah:\n- bawa pulang sampahmu,\n- tetap di jalur yang disediakan,\n- hormati warga dan ladang mereka.\n\n> Tahukah kamu? Satu tiket di Sembungan dapat dipakai untuk mengunjungi Bukit Sikunir sekaligus Telaga Cebong.\n\nSumber: Tempo (Desa Sembungan); Kompas Travel (2022); Indonesia Kaya; detikTravel (2024).",
                "## The highest village in Java\n**Sembungan Village** in Kejajar District lies at about **2,300 metres** — the **highest village in Java**. The air is very cold, but the villagers still start their day early, farming potatoes and vegetables on the slopes.\n\n## Golden sunrise at Sikunir Hill\nFrom Sembungan, visitors climb a footpath to the top of **Sikunir Hill** before dawn. There they wait for the golden sunrise. On clear days, Mount Sindoro, Mount Sumbing, and other mountains can be seen in the distance.\n\n## Telaga Cebong\nAt the foot of the hill lies **Telaga Cebong**, a lake in a valley surrounded by green terraced fields. Many visitors camp on its shore.\n\n## Responsible tourism\nAbout **250,000 visitors** come to Sembungan every year. To keep nature beautiful:\n- take your rubbish home,\n- stay on the paths provided,\n- respect the villagers and their fields.\n\n> Did you know? One ticket in Sembungan lets you visit both Sikunir Hill and Telaga Cebong.\n\nSources: Tempo (Sembungan Village); Kompas Travel (2022); Indonesia Kaya; detikTravel (2024).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Sunrise_di_Bukit_Sikunir.jpg', 'caption' => ['Matahari terbit dari Bukit Sikunir', 'Sunrise from Sikunir Hill'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:The_Golden_Sunrise_Sikunir.jpg', 'caption' => ['Golden sunrise di Sikunir', 'The golden sunrise at Sikunir'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Embun Upas, Es di Pulau Tropis', 'Embun Upas, Ice on a Tropical Island'],
            'body'  => [
                "Indonesia adalah negeri tropis yang hangat. Namun, di Dieng kadang pagi hari rumput dan daun tertutup **es tipis**. Warga menyebutnya **embun upas** atau *bun upas*.\n\n## Kapan muncul?\nEmbun upas biasanya muncul pada **musim kemarau**, mulai Mei, semakin sering pada Juni–Juli, dan paling banyak sekitar **Agustus**. Pada **9 Juli 2026**, BMKG mencatat suhu di Dieng turun hingga **−2,1 °C**.\n\n## Mengapa bisa terjadi?\nMenurut BMKG:\n- Saat kemarau, **angin muson timur** yang kering bertiup dari Australia.\n- **Langit sangat cerah**, sehingga panas bumi cepat lepas ke angkasa pada malam hari.\n- Udara dingin yang berat **turun dan terperangkap** di cekungan dataran tinggi.\n- Ketika suhu di dekat tanah turun di bawah 0 °C, embun pagi **membeku menjadi es**.\n\n## Dampaknya\nBagi wisatawan, embun upas tampak indah. Bagi petani, es ini dapat membuat daun tanaman seperti kentang menjadi layu dan rusak. Jadi, satu peristiwa bisa dipandang berbeda oleh orang yang berbeda.\n\n> Kata *upas* dalam bahasa Jawa berarti racun — karena embun es dapat \"meracuni\" tanaman petani.\n\nSumber: BMKG via Kompas.com (Juli 2026); detikEdu; IDN Times; Universitas Muhammadiyah Malang.",
                "Indonesia is a warm tropical country. Yet in Dieng, grass and leaves are sometimes covered in **thin ice** in the morning. Locals call it **embun upas** or *bun upas*.\n\n## When does it appear?\nFrost usually appears in the **dry season**, starting in May, more often in June and July, and most of all around **August**. On **9 July 2026**, BMKG recorded a temperature in Dieng as low as **−2.1 °C**.\n\n## Why does it happen?\nAccording to BMKG:\n- In the dry season, the dry **east monsoon** wind blows from Australia.\n- **The sky is very clear**, so the ground loses heat quickly into space at night.\n- Heavy cold air **sinks and gets trapped** in the basins of the plateau.\n- When the temperature near the ground falls below 0 °C, the morning dew **freezes into ice**.\n\n## Its effects\nTo visitors, frost looks beautiful. To farmers, the ice can make the leaves of crops such as potatoes wilt and die. So one event can be seen differently by different people.\n\n> The Javanese word *upas* means poison — because frost can \"poison\" the farmers' crops.\n\nSources: BMKG via Kompas.com (July 2026); detikEdu; IDN Times; Muhammadiyah University of Malang.",
            ],
        ],
        [
            'title' => ['Candi-candi Dieng', 'The Temples of Dieng'],
            'body'  => [
                "Dataran Tinggi Dieng menyimpan **kompleks candi Hindu** yang dikenal sebagai salah satu yang **tertua di Pulau Jawa**, diperkirakan dibangun pada **abad ke-7 sampai ke-8**.\n\n## Kompleks Candi Arjuna\nBangunan utamanya **Candi Arjuna**, didampingi **Candi Semar, Candi Srikandi, Candi Puntadewa, dan Candi Sembadra**. Nama-nama itu diambil dari tokoh wayang kisah **Mahabharata** — warga memberikannya jauh setelah candi dibangun. Kompleks ini berada di **Desa Dieng Kulon, Kabupaten Banjarnegara**, bersebelahan dengan wilayah Wonosobo.\n\n## Jejak tulisan\nDi kawasan Dieng ditemukan prasasti berangka tahun **731 Saka atau 808 Masehi** yang ditulis dengan aksara Jawa Kuno. Prasasti-prasasti membantu para ahli mengetahui kapan kawasan ini ramai.\n\n## Candi lainnya\nSelain kompleks Arjuna, ada **Candi Gatotkaca** dan **Candi Bima**. Candi-candi Dieng berukuran kecil dan sederhana dibandingkan Borobudur, tetapi sangat penting untuk memahami awal seni bangunan candi di Jawa.\n\n> Ingat: satu kawasan wisata bisa berada di lebih dari satu kabupaten. Saat menulis tugas, sebutkan lokasinya dengan tepat.\n\nSumber: Dinas Pariwisata dan Kebudayaan Banjarnegara (Candi Arjuna); Geopark Dieng; detikJateng (2025); Desa Dieng Kulon.",
                "The Dieng Plateau holds a **complex of Hindu temples** known as one of the **oldest in Java**, thought to have been built in the **7th to 8th centuries**.\n\n## The Arjuna Temple Complex\nIts main building is **Arjuna Temple**, together with **Semar, Srikandi, Puntadewa, and Sembadra** temples. The names come from wayang characters of the **Mahabharata** — local people gave them long after the temples were built. The complex is in **Dieng Kulon Village, Banjarnegara Regency**, right next to Wonosobo.\n\n## Traces of writing\nAn inscription dated **731 Saka, or 808 CE**, written in Old Javanese script, was found in the Dieng area. Inscriptions help experts learn when the area was busy.\n\n## Other temples\nBesides the Arjuna complex there are **Gatotkaca Temple** and **Bima Temple**. The Dieng temples are small and simple compared with Borobudur, but they are very important for understanding the beginnings of temple building in Java.\n\n> Remember: one tourist area can lie in more than one regency. When you write an assignment, name the location precisely.\n\nSources: Banjarnegara Tourism and Culture Office (Arjuna Temple); Dieng Geopark; detikJateng (2025); Dieng Kulon Village.",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Kompleks_Candi_Arjuna_-_Dieng_Plateau.jpg', 'caption' => ['Kompleks Candi Arjuna di Dieng', 'The Arjuna Temple Complex in Dieng'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Pagi_Candi_Arjuna_Dieng.jpg', 'caption' => ['Pagi berkabut di Candi Arjuna', 'A misty morning at Arjuna Temple'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Tari Topeng Lengger dan Bundengan', 'The Topeng Lengger Dance and the Bundengan'],
            'body'  => [
                "## Tari Topeng Lengger\n**Tari Topeng Lengger** biasanya ditarikan oleh **dua penari**: seorang laki-laki yang memakai **topeng** dan seorang perempuan yang disebut **lengger**. Penari perempuan memakai kain jarit dan selendang (sampur). Tarian ini diiringi **gamelan** — kendang, saron, bonang, kenong, dan gong — serta tembang berisi pesan kebaikan.\n\nKata *lengger* sering dijelaskan sebagai singkatan **\"elingo ngger\"**, artinya *ingatlah, Nak* — ingat kepada Sang Pencipta. Tari Topeng Lengger dirintis oleh **Gondowinangun sekitar tahun 1910** dan pada **Oktober 2020** ditetapkan sebagai **Warisan Budaya Tak Benda Indonesia**.\n\n## Bundengan, alat musik dari tudung bebek\n**Bundengan** berasal dari **kowangan**, tudung anyaman bambu berbentuk seperti tempurung besar yang dipakai **penggembala bebek** untuk berlindung dari panas dan hujan. Tudung itu lalu diberi **senar** dan **bilah bambu**.\n- Senar yang dipetik berbunyi seperti **bendhe** (gong kecil).\n- Bilah bambu yang dipetik berbunyi seperti **kendang** dan **gong**.\n\nKini bundengan diajarkan sebagai **muatan lokal** di sekolah-sekolah Wonosobo dan sering mengiringi Tari Lengger.\n\n> Tahukah kamu? Satu bundengan dapat meniru bunyi beberapa alat gamelan sekaligus — seperti orkestra kecil dari bambu!\n\nSumber: Indonesia Kaya; Kompas Stori (2022) Tari Lengger; BRIN Audiovisual (Bundengan); Badan Penghubung Pemprov Jateng.",
                "## The Topeng Lengger dance\nThe **Topeng Lengger** dance is usually performed by **two dancers**: a man wearing a **mask** (topeng) and a woman called the **lengger**. The female dancer wears a jarit cloth and a sash (sampur). The dance is accompanied by **gamelan** — kendang drum, saron, bonang, kenong, and gong — and songs with good messages.\n\nThe word *lengger* is often explained as short for **\"elingo ngger\"**, meaning *remember, child* — remember the Creator. The dance was pioneered by **Gondowinangun around 1910**, and in **October 2020** it was recognised as **Indonesian Intangible Cultural Heritage**.\n\n## The bundengan, an instrument from a duck herder's hood\nThe **bundengan** comes from the **kowangan**, a woven bamboo hood shaped like a big shell that **duck herders** wore to shelter from the sun and rain. **Strings** and **bamboo blades** were later added to it.\n- Plucked strings sound like a **bendhe** (small gong).\n- Plucked bamboo blades sound like a **kendang** drum and a **gong**.\n\nToday the bundengan is taught as **local content** in Wonosobo schools and often accompanies the Lengger dance.\n\n> Did you know? One bundengan can copy the sounds of several gamelan instruments at once — like a small bamboo orchestra!\n\nSources: Indonesia Kaya; Kompas Stori (2022) Lengger Dance; BRIN Audiovisual (Bundengan); Central Java Provincial Liaison Office.",
            ],
            'media' => [
                ['url' => 'https://www.youtube.com/watch?v=id_j81YL5QI', 'kind' => 'video', 'caption' => ['Video: Bundengan, alat musik unik dari Wonosobo', 'Video: the bundengan, a unique instrument from Wonosobo'], 'credit' => 'YouTube — Idenesia (Metro TV)'],
            ],
        ],
        [
            'title' => ['Tradisi Rambut Gimbal Dieng', 'The Dreadlock Tradition of Dieng'],
            'body'  => [
                "Di Dieng ada anak-anak yang rambutnya tumbuh **gimbal** (menggumpal) secara alami. Masyarakat menyebut mereka **anak bajang** atau anak gimbal, dan memperlakukan mereka dengan penuh kasih sayang.\n\n## Ruwatan rambut gimbal\nMenurut tradisi, rambut gimbal **tidak boleh dipotong sembarangan**. Rambut itu dipotong dalam upacara **ruwatan**, dan sebelumnya si anak boleh **mengajukan satu permintaan** yang harus dipenuhi keluarganya. Permintaannya beragam, dari sepeda sampai jajanan kesukaan. Masyarakat percaya, bila permintaan belum dipenuhi, rambut gimbal bisa tumbuh lagi.\n\n## Dieng Culture Festival\nKini ruwatan rambut gimbal menjadi acara puncak **Dieng Culture Festival (DCF)**, yang digelar di Desa Dieng Kulon. Festival ini juga dimeriahkan pertunjukan seni, lampion, dan musik. Pada DCF Agustus 2026, **13 anak bajang** menjalani ruwatan.\n\n## Menghormati tradisi\nTradisi adalah keyakinan dan kebiasaan yang dijaga masyarakat. Kita boleh bertanya dan mempelajarinya, tetapi tetap **menghormati** — tidak mengejek dan tidak memotret anak tanpa izin keluarganya.\n\n> Setiap daerah punya tradisi unik. Menghargai tradisi orang lain sama pentingnya dengan bangga pada tradisi sendiri.\n\nSumber: Pemprov Jateng (DCF); ANTARA (Ruwatan Anak Bajang); Pemkab Banjarnegara (Agustus 2026); Koran Jakarta (2026).",
                "In Dieng, some children's hair naturally grows into **dreadlocks** (gimbal). People call them **anak bajang** or gimbal children, and treat them with great affection.\n\n## The dreadlock-cutting ceremony\nBy tradition, the dreadlocks **must not be cut just anyhow**. They are cut in a **ruwatan** ceremony, and beforehand the child may **make one request** that the family must fulfil. The requests vary, from a bicycle to a favourite snack. People believe that if the request is not granted, the dreadlocks may grow back.\n\n## Dieng Culture Festival\nToday the dreadlock ceremony is the high point of the **Dieng Culture Festival (DCF)**, held in Dieng Kulon Village. The festival also features art performances, lanterns, and music. At the August 2026 DCF, **13 anak bajang** took part in the ceremony.\n\n## Respecting traditions\nA tradition is a belief and custom that a community keeps. We can ask questions and learn about it, but we must stay **respectful** — no mocking, and no photographing children without their family's permission.\n\n> Every region has unique traditions. Respecting other people's traditions matters as much as being proud of your own.\n\nSources: Central Java Provincial Government (DCF); ANTARA (Anak Bajang ceremony); Banjarnegara Regency Government (August 2026); Koran Jakarta (2026).",
            ],
            'media' => [
                ['url' => 'https://www.youtube.com/watch?v=om5yK2B3DII', 'kind' => 'video', 'caption' => ['Video: Dieng Culture Festival 2025 "Back to the Culture"', 'Video: Dieng Culture Festival 2025 "Back to the Culture"'], 'credit' => 'YouTube — Humas Pemprov Jawa Tengah'],
            ],
        ],
        [
            'title' => ['Rasa Wonosobo', 'Tastes of Wonosobo'],
            'body'  => [
                "## Mi ongklok\nMakanan paling terkenal dari Wonosobo. **Mi kuning, kol, dan daun kucai** dimasukkan ke **ongklok**, keranjang kecil dari anyaman bambu, lalu dicelup-celupkan ke air mendidih — bunyinya *klok-klok-klok*! Setelah itu mi disiram **kuah kental** yang disebut *loh*, terbuat dari **tepung kanji, ebi, dan gula jawa**. Mi ongklok biasanya dimakan bersama **sate sapi** dan **tempe kemul**, tempe goreng berbalut tepung renyah.\n\n## Carica, pepaya gunung\n**Carica** (*Vasconcellea pubescens*) berasal dari **Pegunungan Andes di Amerika Selatan** dan tumbuh baik di ketinggian **1.400–2.400 meter** seperti Dieng. Buahnya kecil, harum, dan biasanya diolah menjadi manisan dalam sirup — oleh-oleh khas Wonosobo.\n\n## Kebun Teh Tambi\n**Kebun Teh Tambi** sudah ada sejak **1865**, pada masa Hindia Belanda. Luasnya sekitar **830 hektare** di ketinggian 800–2.000 meter. Kini kebun ini juga menjadi agrowisata.\n\n## Minuman hangat\nUdara dingin membuat warga gemar minuman hangat, seperti teh Tambi dan minuman herbal **purwaceng** yang tumbuh di Dieng.\n\n> Tahukah kamu? Nama makanan sering berasal dari cara atau alat memasaknya — seperti mi ongklok yang dinamai dari keranjang ongklok.\n\nSumber: Indonesia Kaya (Mie Ongklok); Kompas Travel (2023) Kebun Teh Tambi; Wikipedia (Pepaya gunung); Halodoc (Carica).",
                "## Ongklok noodles\nWonosobo's most famous dish. **Yellow noodles, cabbage, and chives** are put into an **ongklok**, a small woven bamboo basket, and dipped again and again into boiling water — it goes *klok-klok-klok*! Then the noodles are covered with a **thick sauce** called *loh*, made from **tapioca starch, dried shrimp, and palm sugar**. Ongklok noodles are usually eaten with **beef satay** and **tempe kemul**, tempe fried in a crispy batter.\n\n## Carica, the mountain papaya\n**Carica** (*Vasconcellea pubescens*) comes from the **Andes mountains of South America** and grows well at **1,400–2,400 metres**, like Dieng. Its fruit is small and fragrant, and is usually made into sweets in syrup — a typical souvenir of Wonosobo.\n\n## Tambi Tea Estate\n**Tambi Tea Estate** has existed since **1865**, in Dutch East Indies times. It covers about **830 hectares** at 800–2,000 metres. Today it is also an agro-tourism site.\n\n## Warm drinks\nThe cold air makes people love warm drinks, such as Tambi tea and the herbal drink **purwaceng**, a plant that grows in Dieng.\n\n> Did you know? Dishes are often named after how or with what they are cooked — like ongklok noodles, named after the ongklok basket.\n\nSources: Indonesia Kaya (Ongklok Noodles); Kompas Travel (2023) Tambi Tea Estate; Wikipedia (Mountain papaya); Halodoc (Carica).",
            ],
            'media' => [
                ['url' => 'https://commons.wikimedia.org/wiki/File:Mi_ongklok_sate_sapi_Wonosobo.JPG', 'caption' => ['Mi ongklok dengan sate sapi', 'Ongklok noodles with beef satay'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Mountain_papaya_(Vasconcellea_pubescens).jpg', 'caption' => ['Buah carica atau pepaya gunung', 'Carica, the mountain papaya'], 'credit' => $commons],
                ['url' => 'https://commons.wikimedia.org/wiki/File:Es_carica_dieng_wonosobo.jpg', 'caption' => ['Es carica khas Dieng', 'Iced carica from Dieng'], 'credit' => $commons],
            ],
        ],
        [
            'title' => ['Tips Jaka: Cek Dulu Sebelum Percaya dan Berbagi', 'Jaka\'s Tips: Check Before You Believe and Share'],
            'body'  => [
                "Di Wonosobo, Jaka belajar **menilai informasi** sebelum mempercayainya.\n\n## Lima pertanyaan untuk setiap kabar\n- **Siapa** yang membuat? Lembaga resmi, ahli, atau akun tanpa nama?\n- **Kapan** dibuat? Kabar lama bisa sudah tidak berlaku.\n- **Apa buktinya?** Adakah data, foto asli, atau tautan ke sumber resmi?\n- **Apakah sumber lain** mengatakan hal yang sama?\n- **Untuk apa** kabar itu dibuat? Memberi tahu, menjual, atau membuat orang panik?\n\n## Tanda kabar yang perlu dicurigai\n- Huruf kapital semua dan banyak tanda seru: **\"SEBARKAN!!!\"**\n- Tidak ada nama pembuat dan tanggal.\n- Janji hadiah mendadak atau permintaan kode rahasia.\n\n## Empat pilar literasi digital\n- **Cakap digital**: pandai mencari dan mengolah informasi.\n- **Etika digital**: sopan, jujur, menghargai karya dan privasi orang lain.\n- **Aman digital**: menjaga kata sandi, kode OTP, dan data pribadi.\n- **Budaya digital**: memakai internet untuk hal baik, termasuk memperkenalkan budaya daerah dengan hormat.\n\n> Jaka berkata: \"Saring dulu sebelum sharing. Kalau ragu, tanya guru atau orang tua.\"\n\nSumber: Siberkreasi & Ditjen Aptika Kominfo (4 Pilar Literasi Digital); Mafindo (panduan cek fakta).",
                "In Wonosobo, Jaka learns to **judge information** before believing it.\n\n## Five questions for every piece of news\n- **Who** made it? An official body, an expert, or an account with no name?\n- **When** was it made? Old news may no longer be true.\n- **What is the evidence?** Is there data, an original photo, or a link to an official source?\n- **Do other sources** say the same thing?\n- **Why** was it made? To inform, to sell, or to make people panic?\n\n## Signs of news that needs checking\n- All capital letters and lots of exclamation marks: **\"SHARE THIS!!!\"**\n- No author name and no date.\n- A sudden prize or a request for a secret code.\n\n## The four pillars of digital literacy\n- **Digital skills**: good at finding and using information.\n- **Digital ethics**: polite, honest, respecting other people's work and privacy.\n- **Digital safety**: protecting passwords, OTP codes, and personal data.\n- **Digital culture**: using the internet for good, including sharing local culture respectfully.\n\n> Jaka says: \"Filter before you share. If in doubt, ask a teacher or parent.\"\n\nSources: Siberkreasi & Ministry of Communication (4 Pillars of Digital Literacy); Mafindo (fact-checking guide).",
            ],
        ],
    ],
];
