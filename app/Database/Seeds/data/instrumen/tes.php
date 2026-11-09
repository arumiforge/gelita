<?php

/**
 * Pre/post test content (research revision 10, part 8: 34 §6.1, K2, K13).
 *
 * Copied as-is from docs/revisi-10-analitik 13 (opening instructions), 14
 * (Bagian 1 Membaca) and 15 (Bagian 2 Aman dan Etis, Bagian 3 Budaya Digital).
 * Status `needs_verification`: the items wait for expert validation and the
 * readability test. Edit this file and raise `version` after every change,
 * then run `php spark gelita:instrumen:impor` (34 §6.2). Indonesian only (K13).
 *
 * `show` is what a page may render; `key` (answer, scores, error codes) never
 * leaves the server. Stimulus `image` names an `instr.*` slot from the Katalog
 * Aset; without it the page shows the text.
 */

return [
    'version'       => 'tes-0.1',
    'status'        => 'needs_verification',
    'source'        => 'docs/revisi-10-analitik 13, 14, 15 (2 November 2026)',
    'intro'         => [
        'lead'  => 'Halo! Di sini ada soal membaca dan soal tentang memakai internet dengan baik. Jawabanmu tidak memengaruhi nilai rapor. Kerjakan sendiri dan sebaik-baiknya, ya.',
        'steps' => [
            'Lihat kode penelitian di bagian atas layar. Kalau itu bukan kodemu, angkat tangan.',
            'Baca teks atau cerita dulu, lalu jawab soalnya.',
            'Ada soal yang dijawab dengan memilih satu jawaban, soal yang dijawab dengan memilih Benar atau Salah (atau Fakta atau Pendapat) di setiap baris, dan soal menjodohkan yang dijawab dengan memilih huruf pasangannya. Petunjuknya ada di setiap soal.',
            'Kamu boleh kembali ke soal sebelumnya dan mengganti jawaban, selama bagian itu belum kamu kirim.',
            'Kalau ada soal yang sulit, lewati dulu, lalu kembali lagi. Sebelum mengirim, lihat daftar nomor soal: soal yang belum dijawab diberi tanda.',
            'Waktunya cukup, jadi tidak perlu terburu-buru.',
        ],
    ],
    'parts'         => [
        'mb' => [
            'no'       => 1,
            'title'    => 'Bagian 1 Membaca',
            'block'    => 'p1',
            'examples' => [
                'tes-ct-01',
                'tes-ct-02',
                'tes-ct-03',
            ],
            'intro'    => null,
        ],
        'ae' => [
            'no'       => 2,
            'title'    => 'Bagian 2 Aman dan Etis',
            'block'    => 'p23',
            'examples' => [
                'tes-ct-04',
            ],
            'intro'    => 'Baca setiap cerita dan gambar layarnya. Lalu pilih tindakan yang paling baik. Setiap soal hanya punya satu jawaban.',
        ],
        'bd' => [
            'no'       => 3,
            'title'    => 'Bagian 3 Budaya Digital',
            'block'    => 'p23',
            'examples' => [
                'tes-ct-05',
            ],
            'intro'    => 'Lihat unggahan dan papan atau kartu di sebelahnya. Lalu jawab soal di bawahnya. Pilih satu jawaban untuk setiap soal. *Kedugram* adalah aplikasi khayalan.',
        ],
    ],
    'type_hints'    => [
        'pg'     => 'Pilihlah satu jawaban yang paling tepat.',
        'pgk_bs' => 'Pilihlah Benar atau Salah untuk setiap pernyataan.',
        'pgk_fp' => 'Pilihlah Fakta atau Pendapat untuk setiap kalimat.',
        'match'  => 'Pilihlah huruf pasangan yang cocok untuk setiap pernyataan. Ada satu pilihan yang tidak dipakai.',
        'd2'     => null,
    ],
    'stimulus_lead' => 'Bacalah teks di bawah ini. Lalu, jawablah soal nomor {0} sampai {1}.',
    'stimuli'       => [
        'tes-ct-mb'   => [
            'body' => '**Jadwal Menyiram Kebun Sekolah**

- Kelompok Melati: Senin dan Rabu
- Kelompok Mawar: Selasa dan Kamis
- Kelompok Anggrek: Jumat

Siram tanaman pukul 07.00, sebelum bel masuk. Ambil air dari ember biru di dekat pagar.',
        ],
        'tes-mb-A-M1' => [
            'body' => '**Mengenal Randai**

(1) Sumatra Barat punya banyak kesenian rakyat. Salah satunya randai. Randai adalah pertunjukan milik masyarakat Minangkabau. Randai memadukan gerak, nyanyian, dan cerita. Para pemainnya disebut anak randai. Satu kelompok randai terdiri atas 14 sampai 25 orang.

(2) Randai dimainkan di tanah lapang. Anak randai bergerak sambil membentuk lingkaran. Gerakan mereka berasal dari silek, yaitu silat khas Minangkabau. Di sela gerakan, mereka bernyanyi dan memerankan sebuah cerita. Para pemain memakai celana lebar yang disebut galembong. Saat bergerak, mereka menepuk celana itu sehingga terdengar irama.

(3) Randai sering ditampilkan di pesta rakyat dan hari raya. Randai juga dilombakan dalam festival. Satu pertunjukan randai bisa berlangsung satu sampai lima jam. Setiap daerah punya gaya randai sendiri. Randai bahkan berkembang sampai ke Kuantan, di Riau.

(4) Randai tidak hanya menghibur. Lewat randai, anak-anak muda belajar bekerja sama dalam kelompok. Cerita randai juga berisi nasihat tentang cara hidup yang baik. Karena itu, masyarakat Minangkabau terus menjaga randai.

(5) Pada tahun 2017, randai ditetapkan sebagai Warisan Budaya Takbenda Indonesia.',
        ],
        'tes-mb-B-M1' => [
            'body' => '**Mengenal Didong**

(1) Aceh punya banyak kesenian rakyat. Salah satunya didong. Didong adalah pertunjukan milik masyarakat Gayo di dataran tinggi Aceh. Didong memadukan syair, nyanyian, dan gerak. Para pemainnya laki-laki. Satu kelompok didong bisa terdiri atas 15 sampai 30 orang.

(2) Didong sering ditampilkan di berbagai acara warga. Didong juga dilombakan. Dalam didong jalu, dua kelompok saling berbalas syair. Mereka bergiliran melontarkan teka-teki dan menjawabnya. Pertandingan itu bisa berlangsung semalam suntuk.

(3) Didong dimainkan di tempat terbuka. Para pemain duduk bersila berdekatan. Beberapa pemain yang disebut ceh melantunkan syair dalam bahasa Gayo. Seorang ceh harus kreatif dan pandai bersyair. Pemain lain ikut bernyanyi sambil menggerakkan badan. Untuk membuat irama, mereka bertepuk tangan dan menepuk bantal kecil.

(4) Didong tidak hanya menghibur. Lewat didong, anak-anak muda belajar menyusun kata dengan cermat. Syair didong juga dipakai untuk menyampaikan pesan kepada warga. Karena itu, masyarakat Gayo terus menjaga didong.

(5) Pada tahun 2014, didong ditetapkan sebagai Warisan Budaya Takbenda Indonesia.',
        ],
        'tes-mb-A-M2' => [
            'body' => '**Dari Sagu Jadi Apa Saja?**

Di Maluku dan Papua, banyak orang makan sagu sebagai makanan pokok. Tepung sagu diambil dari batang pohon sagu. Dari bahan yang sama, orang membuat banyak makanan. Bentuk dan rasanya berbeda karena cara memasaknya berbeda.

Papeda dibuat dengan menyiram tepung sagu dengan air mendidih sambil terus diaduk. Papeda biasanya dimakan dengan kuah ikan kuning. Sagu lempeng dipanggang di dalam cetakan tanah liat yang disebut forna. Sagu lempeng biasanya dicelup ke teh atau kopi sebelum dimakan. Karena tahan lama, sagu lempeng sering dibawa sebagai bekal saat bepergian jauh. Di Luwu, Sulawesi Selatan, ada kapurung. Kapurung dibuat dari bubur sagu seperti papeda, lalu dicampur sayuran dan ikan atau ayam.

| Makanan | Bahan utama | Cara memasak | Hasilnya |
| --- | --- | --- | --- |
| Papeda | tepung sagu | disiram air mendidih sambil diaduk | lembut, kenyal, lengket |
| Sagu lempeng | tepung sagu | dipanggang dalam cetakan tanah liat | padat, keras, tahan lama |
| Kapurung | bubur sagu | dicampur sayuran dan ikan atau ayam | lembut, bercampur sayuran |

*Disusun oleh: Redaksi Buletin Anak* Piring Kita *· 14 Februari 2026 · Gambar: Hana · Sumber: buku* Sagu dan Olahan Khasnya *(Badan Bahasa, 2017)*',
        ],
        'tes-mb-B-M2' => [
            'body' => '**Dari Jagung Jadi Apa Saja?**

Di Nusa Tenggara Timur, banyak orang makan jagung sebagai makanan pokok. Butir jagung dipipil dari tongkolnya, lalu dijemur. Dari bahan yang sama, orang membuat banyak makanan. Bentuk dan rasanya berbeda karena cara memasaknya berbeda.

Jagung bose dibuat dengan merebus butir jagung sampai lunak, lalu dimasak dengan kacang merah dan santan. Jagung bose biasanya dimakan dengan ikan bakar. Jagung titi disangrai di dalam kuali, lalu dipukul dengan batu sampai pipih. Jagung titi biasanya dimakan dengan kenari. Jagung titi ringan, jadi mudah dibawa sebagai bekal saat bepergian jauh. Di Pulau Timor, ada jagung katemak. Katemak direbus seperti jagung bose, tetapi butirnya tetap utuh dan dicampur kacang serta sayuran.

| Makanan | Bahan utama | Cara memasak | Hasilnya |
| --- | --- | --- | --- |
| Jagung bose | jagung | direbus sampai lunak, lalu dimasak dengan kacang merah dan santan | lembut seperti bubur |
| Jagung titi | jagung | disangrai, lalu dipukul dengan batu | pipih, renyah, ringan |
| Jagung katemak | jagung | direbus utuh bersama kacang dan sayuran | butir utuh, bercampur sayuran |

*Disusun oleh: Redaksi Buletin Anak* Dapur Kita *· 14 Februari 2026 · Gambar: Okta · Sumber: laman Pemerintah Kabupaten Alor*',
        ],
        'tes-mb-A-M3' => [
            'body'  => '**Kedugram**

**Rumah Kain Sasirangan** ✓ akun resmi · 5 Mei\\
@rumahkain.sasirangan

*[Foto: kain sasirangan bermotif garis berkelok, bagian gelap dan bagian terang]*

♡ 128 suka

Kain sasirangan berasal dari suku Banjar di Kalimantan Selatan. Namanya berasal dari kata sirang, yang artinya jelujur. Mula-mula, pola digambar di kain. Pola itu dijahit jelujur dengan tangan, lalu benangnya ditarik kencang. Setelah itu, kain dicelup ke pewarna. Bagian yang terikat benang tidak terkena warna, sehingga muncul motif. Sasirangan adalah kain yang sangat indah. Pada tahun 2013, sasirangan ditetapkan sebagai Warisan Budaya Takbenda Indonesia.

Rumah kain kami buka setiap hari, pukul 09.00–15.00. Ayo datang!

**Komentar**

- **Vina:** Motif berkeloknya cantik sekali! · ♡ 24 suka
- **Pak Hasan, perajin:** Betul. Pola harus dijelujur dulu sebelum kain dicelup. · ♡ 6 suka
- **Yogi:** Aku pernah dengar dari teman, sasirangan dibuat dengan cara dilukis pakai kuas, ya? · ♡ 2 suka',
            'image' => 'instr.tes-mb-A-M3',
        ],
        'tes-mb-B-M3' => [
            'body'  => '**Kedugram**

**Rumah Kain Tapis** ✓ akun resmi · 5 Mei\\
@rumahkain.tapis

*[Foto: kain tapis bergaris dengan bidang bersulam benang mengilap]*

♡ 128 suka

Kain tapis berasal dari Lampung. Perempuan Lampung memakainya sebagai sarung dalam upacara adat. Mula-mula, kain ditenun dari benang kapas. Setelah itu, kain disulam dengan benang emas atau perak. Teknik sulam ini disebut cucuk. Benang emas dan perak membuat sulamannya tampak berkilau. Tapis adalah kain yang paling cantik. Sejak tahun 2013, tapis tercatat sebagai Warisan Budaya Takbenda Indonesia.

Rumah kain kami buka setiap hari, pukul 09.00–15.00. Ayo datang!

**Komentar**

- **Mira:** Sulaman emasnya keren sekali! · ♡ 24 suka
- **Bu Lestari, penyulam:** Betul. Sulaman tapis dikerjakan dengan tangan, sedikit demi sedikit. · ♡ 6 suka
- **Farhan:** Tetanggaku bilang, tapis dibuat dengan cara dicetak pakai mesin, ya? · ♡ 2 suka',
            'image' => 'instr.tes-mb-B-M3',
        ],
        'tes-mb-A-M4' => [
            'body'  => '**Grup Pencinta Layang-Layang**\\
↪ *Diteruskan*\\
PENTING!! Lomba kaghati hari Sabtu jam 7 pagi. Layang-layang plastik juga boleh, yang penting terbang tinggi. Kabari teman-temanmu!

**Kedugram**\\
**Panitia Lomba Kaghati Anak** ✓ akun resmi · 20 Juli\\
@panitia.kaghatianak\\
*[Gambar garis: layang-layang dari lembaran daun berangka bambu]*\\
Lomba Kaghati Anak diadakan hari Minggu, 9 Agustus, pukul 14.00. Kaghati adalah layang-layang tradisional dari Muna, Sulawesi Tenggara. Layarnya dari daun kolope, yaitu daun ubi hutan. Rangkanya dari bambu, dan talinya dari serat nanas hutan. Karena itu, layang-layang plastik tidak ikut dinilai.

Niko, Lala, dan Rendi membaca kedua kabar itu.

- **Niko:** "Pesan di grup itu bisa dipercaya. Ada tulisan PENTING dan dua tanda seru."
- **Lala:** "Pesan di grup tidak menyebut siapa pembuatnya. Isinya perlu dicek dulu."
- **Rendi:** "Satu pesan di grup ternyata keliru. Berarti semua pesan di grup pasti bohong."',
            'image' => 'instr.tes-mb-A-M4',
        ],
        'tes-mb-B-M4' => [
            'body'  => '**Grup Penonton Bidar**\\
↪ *Diteruskan*\\
SEGERA BACA!! Lomba bidar dimulai Sabtu pukul 07.00. Perahu bermesin juga boleh ikut, supaya lebih seru. Bagikan ke grup lain!

**Kedugram**\\
**Panitia Lomba Bidar** ✓ akun resmi · 20 Juli\\
@panitia.lombabidar\\
*[Gambar garis: perahu panjang dengan banyak pendayung]*\\
Lomba Bidar diadakan hari Minggu, 9 Agustus, pukul 14.00, di Sungai Musi. Bidar adalah perahu panjang tradisional dari Palembang, Sumatra Selatan. Perahu ini terbuat dari kayu dan panjangnya sekitar 29 meter. Satu perahu didayung bersama-sama oleh puluhan pendayung. Karena itu, perahu yang memakai mesin tidak ikut dinilai.

Dodi, Putri, dan Gilang membaca kedua kabar itu.

- **Putri:** "Kita belum tahu siapa yang menulis kabar di grup itu. Lebih baik dicocokkan dulu."
- **Gilang:** "Kabar di grup itu salah. Jadi, mulai sekarang semua kabar dari grup tidak usah dipercaya."
- **Dodi:** "Kabar di grup itu tentu benar. Ditulis dengan huruf besar, supaya cepat dibaca."',
            'image' => 'instr.tes-mb-B-M4',
        ],
        'tes-ct-ae'   => [
            'body' => 'Pelajaran membaca di kelas sudah selesai. Di layar tablet sekolah muncul pesan dari Bu Guru: "Waktu membaca habis. Simpan tablet di lemari kelas, ya."',
        ],
        'tes-ct-bd'   => [
            'body' => 'Unggahan Kedugram: nama akun `@pojok.kebun`; gambar tiga buah tomat merah di tanaman tomat dalam pot; keterangan "Tomat di pot kelas kami sudah merah! 🍅"; "♡ 24 suka"; komentar **Nayla**: "Wah, segar sekali!"; tombol "Suka", "Komentar", "Bagikan". Tanpa sumber pembanding.',
        ],
        'tes-ae-A01'  => [
            'body'  => 'Kamu ingin membuat kartu ucapan ulang tahun untuk kakekmu. Kamu menemukan situs `kartu-ultah.example` yang gambarnya lucu-lucu. Sebelum kamu bisa mulai, muncul kotak isian: "Isi dulu nama lengkap, alamat rumah, nama sekolah, dan foto kartu pelajarmu. Semua wajib diisi." Kamu belum pernah mendengar tentang situs itu.',
            'image' => 'instr.tes-ae-A01',
        ],
        'tes-ae-B01'  => [
            'body'  => 'Kamu ingin mengunduh gambar mewarnai bertema dinosaurus. Kamu menemukan situs `warnai-yuk.example` yang gambarnya bagus-bagus. Sebelum unduhan dimulai, muncul kotak isian: "Isi dulu nama lengkap, alamat rumah, nomor HP orang tuamu, dan foto wajahmu. Semua wajib diisi." Kamu belum pernah mendengar tentang situs itu.',
            'image' => 'instr.tes-ae-B01',
        ],
        'tes-ae-A02'  => [
            'body'  => 'Kamu menonton siaran langsung sebuah kanal memasak untuk anak. Kakak pembawa acara berkata, "Tulis namamu di kolom obrolan, nanti Kakak sapa! Boleh tambah nama sekolahmu juga, ya." Banyak penonton menulis nama dan sekolahnya. Seorang penonton yang tidak kamu kenal bertanya, "Kamu sekolah di mana? Rumahmu dekat mana?"',
            'image' => 'instr.tes-ae-A02',
        ],
        'tes-ae-B02'  => [
            'body'  => 'Kamu menonton siaran langsung sebuah kanal percobaan sains untuk anak. Kakak pembawa acara berkata, "Tulis namamu di kolom obrolan, nanti Kakak sapa! Boleh tambah nama kampungmu juga, ya." Banyak penonton menulis nama dan kampungnya. Seorang penonton yang tidak kamu kenal bertanya, "Rumahmu di dekat mana? Kamu kelas berapa?"',
            'image' => 'instr.tes-ae-B02',
        ],
        'tes-ae-A03'  => [
            'body'  => 'Bulan ini kamu sudah membaca 15 buku cerita di aplikasi perpustakaan digital. Lalu akun yang tidak kamu kenal mengirim pesan: "Hai! Kamu rajin membaca! Aku bisa memasang namamu di Papan Pembaca Hebat. Kirim kata sandimu, biar aku yang mengurus semuanya. Cepat, ya, papannya ditutup nanti malam!"',
            'image' => 'instr.tes-ae-A03',
        ],
        'tes-ae-B03'  => [
            'body'  => 'Kamu mengunggah gambar perahu buatanmu di aplikasi menggambar. Tidak lama kemudian, akun yang tidak kamu kenal mengirim pesan: "Hai! Gambarmu bagus! Aku bisa memasukkannya ke Galeri Juara minggu ini. Kirim kata sandimu, biar aku yang mengurus semuanya. Cepat, ya, galerinya ditutup nanti malam!"',
            'image' => 'instr.tes-ae-B03',
        ],
        'tes-ae-A04'  => [
            'body'  => 'Kirana, teman les musikmu, mengirim pesan: "Aku lupa kata sandi akun kelas musikku. Padahal sebentar lagi ada latihan daring. Boleh aku masuk pakai akunmu dulu? Nanti ada kode masuk yang terkirim ke HP-mu. Kirim kodenya ke aku, ya. Cuma sebentar, kok!"',
            'image' => 'instr.tes-ae-A04',
        ],
        'tes-ae-B04'  => [
            'body'  => 'Bagas, teman les bahasa Inggrismu, mengirim pesan: "Akun kamus daringku terkunci. Padahal besok ada ulangan kosakata. Boleh aku pinjam akunmu untuk menonton video pelajaran? Nanti ada kode masuk yang terkirim ke HP-mu. Kirim kodenya ke aku, ya. Aku janji tidak mengubah apa pun!"',
            'image' => 'instr.tes-ae-B04',
        ],
        'tes-ae-A05'  => [
            'body'  => 'Saat piknik kelas di taman kota, kamu memotret Yasmin yang sedang tertawa di dekat kolam ikan. Yasmin berkata, "Fotonya untuk kamu simpan saja, ya. Jangan diunggah." Malamnya, kamu melihat lagi foto itu. Cahayanya pas dan senyumnya manis. Kamu ingin mengunggahnya ke akun Kedugram-mu.',
            'image' => 'instr.tes-ae-A05',
        ],
        'tes-ae-B05'  => [
            'body'  => 'Di acara perpisahan kelas 6, kamu merekam Galang yang bernyanyi di panggung. Galang berkata, "Videonya buat kamu simpan saja, ya. Jangan dikirim ke mana-mana." Sorenya, kamu menonton lagi video itu. Suaranya merdu dan gambarnya jelas. Kamu ingin mengunggahnya ke akun Kedugram-mu.',
            'image' => 'instr.tes-ae-B05',
        ],
        'tes-ae-A06'  => [
            'body'  => 'Rara mengirim foto dirinya memakai kostum baru untuk pentas seni lewat pesan pribadi. Ia bertanya, "Bagus, nggak?" Menurutmu kostumnya bagus sekali. Kamu ingin membagikan foto itu ke grup les menggambar supaya teman-teman ikut memuji Rara. Rara juga anggota grup itu, tapi ia jarang menulis di sana.',
            'image' => 'instr.tes-ae-A06',
        ],
        'tes-ae-B06'  => [
            'body'  => 'Hendra mengirim video dirinya mencoba sulap kartu lewat pesan pribadi. Ia bertanya, "Keren, nggak?" Menurutmu sulapnya keren sekali. Kamu ingin membagikan video itu ke grup ekskul futsal supaya teman-teman ikut memuji Hendra. Hendra juga anggota grup itu, tapi ia jarang menulis di sana.',
            'image' => 'instr.tes-ae-B06',
        ],
        'tes-ae-A07'  => [
            'body'  => 'Kamu mengunggah video kucingmu yang sedang bermain bola benang di Kedugram. Banyak teman memberi tanda suka. Lalu akun yang tidak kamu kenal mengirim pesan: "Kucingmu lucu sekali! Aku juga punya kucing. Kamu kelas berapa? Kita mengobrol di aplikasi lain saja, yuk. Jangan bilang orang tuamu, ya."',
            'image' => 'instr.tes-ae-A07',
        ],
        'tes-ae-B07'  => [
            'body'  => 'Kamu mengunggah foto layang-layang buatanmu di Kedugram. Banyak teman memberi tanda suka. Lalu akun yang tidak kamu kenal mengirim pesan: "Layanganmu keren sekali! Aku juga suka membuat layangan. Kamu umur berapa? Kita mengobrol lewat panggilan video saja, yuk. Ini rahasia kita berdua, ya."',
            'image' => 'instr.tes-ae-B07',
        ],
        'tes-ae-A08'  => [
            'body'  => 'Di grup les renang ada pesan berantai: "Mulai besok aplikasi pesan ini berbayar Rp 5.000 sehari! Kirim pesan ini ke 20 temanmu supaya akunmu tetap gratis." Temanmu, Yudha, membalas: "Aku sudah kirim ke 20 orang. Kalian juga, ya!" Beberapa teman lain ikut membalas dengan stiker jempol.',
            'image' => 'instr.tes-ae-A08',
        ],
        'tes-ae-B08'  => [
            'body'  => 'Di grup ekskul pramuka ada pesan berantai: "Hari Senin semua foto di akun Kedugram kalian akan terhapus! Supaya fotomu aman, kirim pesan ini ke 15 orang." Temanmu, Rio, membalas: "Sudah kukirim ke 15 orang. Ayo, kalian juga!" Beberapa teman lain ikut membalas dengan stiker jempol.',
            'image' => 'instr.tes-ae-B08',
        ],
        'tes-bd-A-G1' => [
            'intro' => 'Lihat unggahan di Kedugram ini. Kartu keterangan di sebelahnya dipasang di pameran, di samping kain yang sama.',
            'rows'  => [
                [
                    'Nama akun pengunggah',
                    '`@rak.kain.tia`',
                ],
                [
                    'Gambar',
                    'Selembar kain terbentang dengan motif bunga yang tersusun dari kotak-kotak kecil berlubang halus',
                ],
                [
                    'Tulisan di foto',
                    '"Foto: Galeri Kain Kenari Biru"',
                ],
                [
                    'Jumlah suka',
                    '"♡ 126 suka"',
                ],
                [
                    'Keterangan',
                    '"Kain karawo dari Gorontalo. Motifnya digambar pakai spidol, ya! 🖍️"',
                ],
                [
                    'Komentar 1',
                    '**Citra**: "Cantik sekali! Terima kasih sudah berbagi 👏"',
                ],
                [
                    'Komentar 2',
                    '**Danu**: "Motifnya kayak sarang laba-laba, wkwk 😝"',
                ],
                [
                    'Tombol',
                    '"Suka", "Komentar", "Bagikan", "Kirim pesan ke galeri"',
                ],
                [
                    'Kartu keterangan pameran (pembanding)',
                    '"**Kain Karawo** · Gorontalo. Kain ini disulam dengan tangan. Sebelum disulam, benang kainnya diiris dan dicabut mengikuti pola. *Karawo* berasal dari kata *mokarawo*, artinya mengiris."',
                ],
            ],
            'image' => 'instr.tes-bd-A-G1',
        ],
        'tes-bd-B-G1' => [
            'intro' => 'Lihat unggahan di Kedugram ini. Kartu keterangan di sebelahnya dipasang di pameran, di samping kain yang sama.',
            'rows'  => [
                [
                    'Nama akun pengunggah',
                    '`@lemari.ilham`',
                ],
                [
                    'Gambar',
                    'Selembar kain terbentang dengan motif hiasan mirip huruf Arab dan bunga besar',
                ],
                [
                    'Tulisan di foto',
                    '"Foto: Galeri Kain Camar Putih"',
                ],
                [
                    'Jumlah suka',
                    '"♡ 131 suka"',
                ],
                [
                    'Keterangan',
                    '"Kain besurek dari Bengkulu. Motifnya disemprot pakai cat, ya! 🎨"',
                ],
                [
                    'Komentar 1',
                    '**Lusi**: "Bagus sekali! Terima kasih sudah berbagi 👏"',
                ],
                [
                    'Komentar 2',
                    '**Bimo**: "Motifnya kayak coretan anak TK, wkwk 😝"',
                ],
                [
                    'Tombol',
                    '"Suka", "Komentar", "Bagikan", "Kirim pesan ke galeri"',
                ],
                [
                    'Kartu keterangan pameran (pembanding)',
                    '"**Kain Besurek** · Bengkulu. Kain ini dibatik dengan tangan. Motifnya digambar dengan canting berisi lilin panas, lalu kain dicelup ke pewarna. *Besurek* artinya bertulisan."',
                ],
            ],
            'image' => 'instr.tes-bd-B-G1',
        ],
        'tes-bd-A-G2' => [
            'intro' => 'Lihat unggahan video di Kedugram ini. Papan informasi di sebelahnya dipasang di festival tempat tari itu ditampilkan.',
            'rows'  => [
                [
                    'Nama akun pengunggah',
                    '`@kamera.ryan`',
                ],
                [
                    'Cuplikan video',
                    'Lima penari berbaris di panggung terbuka. Setiap penari memegang tongkat panjang di satu tangan dan bambu pendek di tangan lain',
                ],
                [
                    'Tulisan di video',
                    '"Video: Sanggar Lima Purnama"',
                ],
                [
                    'Jumlah suka',
                    '"♡ 9.400 suka"',
                ],
                [
                    'Keterangan',
                    '"Tari Gantar dari Kalimantan Timur, tarian nelayan menangkap ikan 🎣 Video rekamanku sendiri, lho!"',
                ],
                [
                    'Komentar 1',
                    '**Kevin**: "Oh, tari nelayan, ya. Aku baru tahu! 😍"',
                ],
                [
                    'Komentar 2',
                    '**Tasya**: "Keliru, tuh. Baca papannya dulu, dong, jangan asal tulis! 🙄"',
                ],
                [
                    'Komentar 3',
                    '**Wina**: "Yang suka ribuan, berarti benar dong 👍"',
                ],
                [
                    'Tombol',
                    '"Suka", "Komentar", "Bagikan", "Unduh"',
                ],
                [
                    'Papan informasi festival (pembanding)',
                    '"Tari Gantar berasal dari Kutai Barat, Kalimantan Timur. Gerakannya menggambarkan orang menanam padi. Tongkat panjang dipakai untuk melubangi tanah. Bambu pendek berisi biji-bijian menggambarkan benih padi."',
                ],
            ],
            'image' => 'instr.tes-bd-A-G2',
        ],
        'tes-bd-B-G2' => [
            'intro' => 'Lihat unggahan video di Kedugram ini. Papan informasi di sebelahnya dipasang di festival tempat tari itu ditampilkan.',
            'rows'  => [
                [
                    'Nama akun pengunggah',
                    '`@tontonan.dina`',
                ],
                [
                    'Cuplikan video',
                    'Penari laki-laki dan perempuan berpasangan dalam dua baris, saling berpegangan tangan. Seorang penari perempuan di depan bernyanyi sambil mengangkat tangan',
                ],
                [
                    'Tulisan di video',
                    '"Video: Sanggar Tujuh Pelita"',
                ],
                [
                    'Jumlah suka',
                    '"♡ 9.100 suka"',
                ],
                [
                    'Keterangan',
                    '"Tari Maengket dari Sulawesi Utara, tarian untuk menyambut raja 👑 Video rekamanku sendiri, lho!"',
                ],
                [
                    'Komentar 1',
                    '**Jihan**: "Oh, tari menyambut raja, ya. Aku baru tahu! 😍"',
                ],
                [
                    'Komentar 2',
                    '**Bonar**: "Ngawur, tuh. Makanya baca papannya dulu, jangan asal tulis! 🙄"',
                ],
                [
                    'Komentar 3',
                    '**Ratih**: "Yang suka ribuan, pasti benar dong 👍"',
                ],
                [
                    'Tombol',
                    '"Suka", "Komentar", "Bagikan", "Unduh"',
                ],
                [
                    'Papan informasi festival (pembanding)',
                    '"Tari Maengket berasal dari Minahasa, Sulawesi Utara. Para penari menari sambil bernyanyi, dipimpin seorang pemimpin lagu. Awalnya, tari ini adalah ungkapan syukur atas hasil panen padi."',
                ],
            ],
            'image' => 'instr.tes-bd-B-G2',
        ],
    ],
    'items'         => [
        'tes-ct-01'  => [
            'part'     => 'ct',
            'form'     => null,
            'no'       => 1,
            'stimulus' => 'tes-ct-mb',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kapan Kelompok Anggrek menyiram kebun?',
                'options' => [
                    'A' => 'Senin',
                    'B' => 'Selasa',
                    'C' => 'Kamis',
                    'D' => 'Jumat',
                ],
            ],
            'key'      => [
                'answer' => 'D',
            ],
        ],
        'tes-ct-02'  => [
            'part'     => 'ct',
            'form'     => null,
            'no'       => 2,
            'stimulus' => 'tes-ct-mb',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => '',
                'statements' => [
                    '1' => 'Kelompok Melati menyiram kebun pada hari Rabu.',
                    '2' => 'Tanaman disiram sesudah pulang sekolah.',
                    '3' => 'Air diambil dari ember biru.',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'BSB',
            ],
        ],
        'tes-ct-03'  => [
            'part'     => 'ct',
            'form'     => null,
            'no'       => 3,
            'stimulus' => 'tes-ct-mb',
            'type'     => 'match',
            'show'     => [
                'prompt' => '',
                'left'   => [
                    '1' => 'Kelompok Melati',
                    '2' => 'Kelompok Mawar',
                    '3' => 'Kelompok Anggrek',
                ],
                'right'  => [
                    'A' => 'Selasa dan Kamis',
                    'B' => 'Sabtu',
                    'C' => 'Senin dan Rabu',
                    'D' => 'Jumat',
                ],
            ],
            'key'      => [
                'answer' => '1C 2A 3D',
            ],
        ],
        'tes-ct-04'  => [
            'part'     => 'ct',
            'form'     => null,
            'no'       => 4,
            'stimulus' => 'tes-ct-ae',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Simpan tablet di lemari kelas',
                    'B' => 'Bawa tablet pulang ke rumah',
                    'C' => 'Taruh tablet di bawah mejamu',
                    'D' => 'Pinjamkan tablet ke kelas sebelah',
                ],
            ],
            'key'      => [
                'answer' => 'A',
            ],
        ],
        'tes-ct-05'  => [
            'part'     => 'ct',
            'form'     => null,
            'no'       => 5,
            'stimulus' => 'tes-ct-bd',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa yang ada di gambar unggahan ini?',
                'options' => [
                    'A' => 'Jagung di kebun sekolah',
                    'B' => 'Tomat merah di dalam pot',
                    'C' => 'Cabai di dalam keranjang',
                    'D' => 'Bunga di depan kelas',
                ],
            ],
            'key'      => [
                'answer' => 'B',
            ],
        ],
        'tes-mb-A01' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 1,
            'stimulus' => 'tes-mb-A-M1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut teks, benda apa yang ditepuk anak randai sehingga terdengar irama?',
                'options' => [
                    'A' => 'tongkat kayu',
                    'B' => 'celana galembong',
                    'C' => 'kain sarung',
                    'D' => 'lantai panggung',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'INF-LIT',
                    'C' => 'INF-LIT',
                    'D' => 'INF-LIT',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-A02' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 2,
            'stimulus' => 'tes-mb-A-M1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut teks, siapa yang disebut anak randai?',
                'options' => [
                    'A' => 'anak-anak yang menonton randai',
                    'B' => 'guru yang melatih para pemain',
                    'C' => 'kelompok randai dari Kuantan, Riau',
                    'D' => 'para pemain randai',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'INF-KAT',
                    'B' => 'INF-LIT',
                    'C' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-A03' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 3,
            'stimulus' => 'tes-mb-A-M1',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Menurut teks, mengapa masyarakat Minangkabau terus menjaga randai?',
                'statements' => [
                    '1' => 'Karena lewat randai, anak-anak muda belajar bekerja sama.',
                    '2' => 'Karena para pemainnya memakai celana galembong.',
                    '3' => 'Karena cerita randai berisi nasihat tentang cara hidup yang baik.',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'BSB',
                'errors' => [
                    '1' => 'INF-LIT',
                    '2' => 'INF-REL',
                    '3' => 'INF-HUB',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-A04' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 4,
            'stimulus' => 'tes-mb-A-M1',
            'type'     => 'match',
            'show'     => [
                'prompt' => 'Pasangkan setiap paragraf dengan isi pokoknya.',
                'left'   => [
                    '1' => 'Paragraf 2',
                    '2' => 'Paragraf 3',
                    '3' => 'Paragraf 4',
                ],
                'right'  => [
                    'A' => 'manfaat randai dan alasan randai terus dijaga',
                    'B' => 'cara membuat celana galembong',
                    'C' => 'cara randai dimainkan',
                    'D' => 'randai di pesta, lomba, dan berbagai daerah',
                ],
            ],
            'key'      => [
                'answer' => '1C 2D 3A',
                'errors' => [
                    'B'          => 'INF-KAT',
                    'other_pair' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-A05' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 5,
            'stimulus' => 'tes-mb-A-M2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut teks, mengapa sagu lempeng sering dibawa sebagai bekal saat bepergian jauh?',
                'options' => [
                    'A' => 'Karena sagu lempeng tahan lama.',
                    'B' => 'Karena harganya murah.',
                    'C' => 'Karena bisa dicelup ke teh atau kopi.',
                    'D' => 'Karena sagu lempeng lembut dan kenyal.',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'INF-LIT',
                    'C' => 'INF-REL',
                    'D' => 'INF-HUB',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-A06' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 6,
            'stimulus' => 'tes-mb-A-M2',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Bacalah teks dan tabel. Benar atau salah?',
                'statements' => [
                    '1' => 'Papeda dan sagu lempeng dibuat dari bahan utama yang sama.',
                    '2' => 'Sagu lempeng lebih lembut daripada papeda.',
                    '3' => 'Semua makanan di tabel dimasak dengan air.',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'BSS',
                'errors' => [
                    '1' => 'INF-LIT',
                    '2' => 'INF-HUB',
                    '3' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-A07' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 7,
            'stimulus' => 'tes-mb-A-M2',
            'type'     => 'match',
            'show'     => [
                'prompt' => 'Pasangkan setiap makanan dengan keterangan yang cocok menurut teks dan tabel.',
                'left'   => [
                    '1' => 'Papeda',
                    '2' => 'Sagu lempeng',
                    '3' => 'Kapurung',
                ],
                'right'  => [
                    'A' => 'bercampur sayuran dan ikan atau ayam',
                    'B' => 'lembut dan kenyal; dimakan dengan kuah ikan kuning',
                    'C' => 'padat dan keras; dimakan dengan kuah ikan kuning',
                    'D' => 'padat dan keras; dicelup ke teh atau kopi',
                ],
            ],
            'key'      => [
                'answer' => '1B 2D 3A',
                'errors' => [
                    'C'          => 'INF-HUB',
                    'other_pair' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-A08' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 8,
            'stimulus' => 'tes-mb-A-M2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Siapa yang menyusun infografik ini?',
                'options' => [
                    'A' => 'Badan Bahasa',
                    'B' => 'Hana, pembuat gambarnya',
                    'C' => 'Redaksi Buletin Anak *Piring Kita*',
                    'D' => 'Tidak tertulis di infografik',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'INF-SUM',
                    'B' => 'INF-SUM',
                    'D' => 'INF-SUM',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L1',
            ],
        ],
        'tes-mb-A09' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 9,
            'stimulus' => 'tes-mb-A-M3',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut unggahan, apa arti kata *sirang*?',
                'options' => [
                    'A' => 'ikat',
                    'B' => 'celup',
                    'C' => 'kain khas Banjar',
                    'D' => 'jelujur',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'INF-LIT',
                    'B' => 'INF-LIT',
                    'C' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-A10' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 10,
            'stimulus' => 'tes-mb-A-M3',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa yang akan terjadi kalau benang tidak ditarik kencang sebelum kain dicelup?',
                'options' => [
                    'A' => 'Pewarna bisa masuk ke bagian yang dijahit, sehingga motifnya tidak tampak jelas.',
                    'B' => 'Motifnya tetap tampak jelas, karena polanya sudah digambar lebih dulu.',
                    'C' => 'Kain itu tidak bisa dicelup ke pewarna sama sekali.',
                    'D' => 'Semua kain sasirangan menjadi rusak dan tidak bisa dipakai.',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'INF-HUB',
                    'C' => 'INF-LIT',
                    'D' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-A11' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 11,
            'stimulus' => 'tes-mb-A-M3',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Kalimat berikut ada di unggahan dan komentar. Fakta atau pendapat?',
                'statements' => [
                    '1' => '"Sasirangan adalah kain yang sangat indah."',
                    '2' => '"Pola harus dijelujur dulu sebelum kain dicelup."',
                    '3' => '"Pada tahun 2013, sasirangan ditetapkan sebagai Warisan Budaya Takbenda Indonesia."',
                ],
                'scale'      => 'fp',
            ],
            'key'      => [
                'answer' => 'PFF',
                'errors' => [
                    '1' => 'INF-FOP',
                    '2' => 'INF-FOP',
                    '3' => 'INF-FOP',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-A12' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 12,
            'stimulus' => 'tes-mb-A-M3',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Tentang cara membuat sasirangan, komentar siapa yang paling dapat dipercaya?',
                'options' => [
                    'A' => 'Komentar Yogi, karena ia mendengarnya dari teman.',
                    'B' => 'Komentar Vina, karena komentarnya paling banyak disukai.',
                    'C' => 'Komentar Pak Hasan, karena ia perajin dan isinya cocok dengan unggahan.',
                    'D' => 'Ketiga komentar, karena semuanya ditulis di bawah unggahan akun resmi.',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'INF-SUM',
                    'B' => 'INF-SUM',
                    'D' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-A13' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 13,
            'stimulus' => 'tes-mb-A-M4',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa yang berbeda antara pesan di grup dan pengumuman panitia?',
                'options' => [
                    'A' => 'Semua isinya berbeda; tidak ada yang sama.',
                    'B' => 'Hari dan jam lomba, juga boleh tidaknya layang-layang plastik.',
                    'C' => 'Keduanya sama-sama membicarakan lomba kaghati.',
                    'D' => 'Hanya harinya yang berbeda; jamnya sama.',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'INF-GEN',
                    'C' => 'INF-REL',
                    'D' => 'INF-LIT',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-A14' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 14,
            'stimulus' => 'tes-mb-A-M4',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kabar mana yang lebih dapat dipercaya tentang aturan lomba, dan mengapa?',
                'options' => [
                    'A' => 'Pesan di grup, karena lebih pendek dan mudah dipahami.',
                    'B' => 'Pesan di grup, karena dikirim ke grup yang anggotanya banyak.',
                    'C' => 'Keduanya sama kuat, karena membicarakan lomba yang sama.',
                    'D' => 'Pengumuman panitia, karena ditulis akun resmi panitia dan ada tanggalnya.',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'INF-SUM',
                    'B' => 'INF-SUM',
                    'C' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-A15' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 15,
            'stimulus' => 'tes-mb-A-M4',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Apakah pendapat setiap anak tepat? Pilih Benar kalau tepat, Salah kalau tidak tepat.',
                'statements' => [
                    '1' => 'Niko: "Pesan di grup itu bisa dipercaya. Ada tulisan PENTING dan dua tanda seru."',
                    '2' => 'Lala: "Pesan di grup tidak menyebut siapa pembuatnya. Isinya perlu dicek dulu."',
                    '3' => 'Rendi: "Satu pesan di grup ternyata keliru. Berarti semua pesan di grup pasti bohong."',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'SBS',
                'errors' => [
                    '1' => 'INF-SUM',
                    '2' => 'INF-SUM',
                    '3' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-A16' => [
            'part'     => 'mb',
            'form'     => 'A',
            'no'       => 16,
            'stimulus' => 'tes-mb-A-M4',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa cara yang paling tepat untuk memastikan hari dan jam lomba?',
                'options' => [
                    'A' => 'Mencocokkan dengan pengumuman di akun resmi panitia, atau bertanya langsung kepada panitia.',
                    'B' => 'Bertanya di grup, siapa saja yang setuju dengan pesan itu.',
                    'C' => 'Menunggu pesan terbaru di grup, lalu memercayainya.',
                    'D' => 'Tidak memercayai kabar apa pun tentang lomba itu.',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'INF-SUM',
                    'C' => 'INF-SUM',
                    'D' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-B01' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 1,
            'stimulus' => 'tes-mb-B-M1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut teks, benda apa yang ditepuk pemain didong untuk membuat irama?',
                'options' => [
                    'A' => 'papan kayu',
                    'B' => 'batang bambu',
                    'C' => 'bantal kecil',
                    'D' => 'ikat kepala',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'INF-LIT',
                    'B' => 'INF-LIT',
                    'D' => 'INF-LIT',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-B02' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 2,
            'stimulus' => 'tes-mb-B-M1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut teks, siapa yang disebut ceh?',
                'options' => [
                    'A' => 'beberapa pemain yang melantunkan syair',
                    'B' => 'pelatih yang mengajari para pemain',
                    'C' => 'kelompok lawan dalam didong jalu',
                    'D' => 'orang yang menuliskan syair di buku',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'INF-LIT',
                    'C' => 'INF-REL',
                    'D' => 'INF-KAT',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-B03' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 3,
            'stimulus' => 'tes-mb-B-M1',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Menurut teks, mengapa masyarakat Gayo terus menjaga didong?',
                'statements' => [
                    '1' => 'Karena syair didong dipakai untuk menyampaikan pesan kepada warga.',
                    '2' => 'Karena lewat didong, anak-anak muda belajar menyusun kata dengan cermat.',
                    '3' => 'Karena para pemainnya menepuk bantal kecil.',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'BBS',
                'errors' => [
                    '1' => 'INF-HUB',
                    '2' => 'INF-LIT',
                    '3' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-B04' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 4,
            'stimulus' => 'tes-mb-B-M1',
            'type'     => 'match',
            'show'     => [
                'prompt' => 'Pasangkan setiap paragraf dengan isi pokoknya.',
                'left'   => [
                    '1' => 'Paragraf 2',
                    '2' => 'Paragraf 3',
                    '3' => 'Paragraf 4',
                ],
                'right'  => [
                    'A' => 'didong di acara warga dan pertandingan',
                    'B' => 'manfaat didong dan alasan didong terus dijaga',
                    'C' => 'cara didong dimainkan',
                    'D' => 'cara membuat bantal kecil',
                ],
            ],
            'key'      => [
                'answer' => '1A 2C 3B',
                'errors' => [
                    'D'          => 'INF-KAT',
                    'other_pair' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-B05' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 5,
            'stimulus' => 'tes-mb-B-M2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut teks, mengapa jagung titi mudah dibawa sebagai bekal saat bepergian jauh?',
                'options' => [
                    'A' => 'Karena rasanya manis.',
                    'B' => 'Karena bisa dimakan dengan kenari.',
                    'C' => 'Karena jagung titi lembut seperti bubur.',
                    'D' => 'Karena jagung titi ringan.',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'INF-LIT',
                    'B' => 'INF-REL',
                    'C' => 'INF-HUB',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-B06' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 6,
            'stimulus' => 'tes-mb-B-M2',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Bacalah teks dan tabel. Benar atau salah?',
                'statements' => [
                    '1' => 'Semua makanan di tabel dimasak dengan cara direbus.',
                    '2' => 'Jagung bose lebih lembut daripada jagung titi.',
                    '3' => 'Jagung bose dan jagung titi dibuat dari bahan utama yang berbeda.',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'SBS',
                'errors' => [
                    '1' => 'INF-GEN',
                    '2' => 'INF-HUB',
                    '3' => 'INF-LIT',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-B07' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 7,
            'stimulus' => 'tes-mb-B-M2',
            'type'     => 'match',
            'show'     => [
                'prompt' => 'Pasangkan setiap makanan dengan keterangan yang cocok menurut teks dan tabel.',
                'left'   => [
                    '1' => 'Jagung bose',
                    '2' => 'Jagung titi',
                    '3' => 'Jagung katemak',
                ],
                'right'  => [
                    'A' => 'pipih dan renyah; dimakan dengan ikan bakar',
                    'B' => 'butir utuh, bercampur kacang dan sayuran',
                    'C' => 'pipih dan renyah; dimakan dengan kenari',
                    'D' => 'lembut seperti bubur; dimakan dengan ikan bakar',
                ],
            ],
            'key'      => [
                'answer' => '1D 2C 3B',
                'errors' => [
                    'A'          => 'INF-HUB',
                    'other_pair' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-B08' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 8,
            'stimulus' => 'tes-mb-B-M2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Siapa yang menyusun infografik ini?',
                'options' => [
                    'A' => 'Pemerintah Kabupaten Alor',
                    'B' => 'Redaksi Buletin Anak *Dapur Kita*',
                    'C' => 'Okta, pembuat gambarnya',
                    'D' => 'Nama penyusunnya tidak dicantumkan',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'INF-SUM',
                    'C' => 'INF-SUM',
                    'D' => 'INF-SUM',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L1',
            ],
        ],
        'tes-mb-B09' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 9,
            'stimulus' => 'tes-mb-B-M3',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut unggahan, apa arti kata *cucuk*?',
                'options' => [
                    'A' => 'sulam',
                    'B' => 'tenun',
                    'C' => 'benang emas',
                    'D' => 'kain khas Lampung',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'INF-LIT',
                    'C' => 'INF-LIT',
                    'D' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L1',
            ],
        ],
        'tes-mb-B10' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 10,
            'stimulus' => 'tes-mb-B-M3',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa yang akan terjadi kalau sulaman tapis dibuat dengan benang kapas biasa, bukan benang emas atau perak?',
                'options' => [
                    'A' => 'Sulamannya tetap berkilau, karena kainnya sudah ditenun lebih dulu.',
                    'B' => 'Kain itu tidak bisa disulam sama sekali.',
                    'C' => 'Sulamannya tidak tampak berkilau seperti sulaman benang emas atau perak.',
                    'D' => 'Semua kain tapis menjadi rusak dan tidak bisa dipakai.',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'INF-HUB',
                    'B' => 'INF-LIT',
                    'D' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-B11' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 11,
            'stimulus' => 'tes-mb-B-M3',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Kalimat berikut ada di unggahan dan komentar. Fakta atau pendapat?',
                'statements' => [
                    '1' => '"Sulaman tapis dikerjakan dengan tangan, sedikit demi sedikit."',
                    '2' => '"Sejak tahun 2013, tapis tercatat sebagai Warisan Budaya Takbenda Indonesia."',
                    '3' => '"Tapis adalah kain yang paling cantik."',
                ],
                'scale'      => 'fp',
            ],
            'key'      => [
                'answer' => 'FFP',
                'errors' => [
                    '1' => 'INF-FOP',
                    '2' => 'INF-FOP',
                    '3' => 'INF-FOP',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-B12' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 12,
            'stimulus' => 'tes-mb-B-M3',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Tentang cara membuat tapis, komentar siapa yang paling dapat dipercaya?',
                'options' => [
                    'A' => 'Komentar Mira, karena jumlah sukanya paling banyak.',
                    'B' => 'Semua komentar, karena ditulis di unggahan akun resmi.',
                    'C' => 'Komentar Farhan, karena ia diberi tahu tetangganya.',
                    'D' => 'Komentar Bu Lestari, karena ia penyulam dan keterangannya sama dengan unggahan.',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'INF-SUM',
                    'B' => 'INF-GEN',
                    'C' => 'INF-SUM',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-B13' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 13,
            'stimulus' => 'tes-mb-B-M4',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa yang berbeda antara kabar di grup dan unggahan panitia?',
                'options' => [
                    'A' => 'Hari dan jam lomba, juga boleh tidaknya perahu bermesin ikut.',
                    'B' => 'Hanya jamnya yang berbeda; harinya sama.',
                    'C' => 'Kedua kabar itu tidak punya kesamaan sedikit pun.',
                    'D' => 'Kedua kabar itu sama-sama tentang lomba bidar.',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'INF-LIT',
                    'C' => 'INF-GEN',
                    'D' => 'INF-REL',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1a',
                'level' => 'L2',
            ],
        ],
        'tes-mb-B14' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 14,
            'stimulus' => 'tes-mb-B-M4',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kabar mana yang lebih dapat dipercaya tentang aturan lomba, dan mengapa?',
                'options' => [
                    'A' => 'Kabar di grup, karena kalimatnya singkat dan jelas.',
                    'B' => 'Unggahan panitia, karena dibuat akun resmi panitia dan bertanggal.',
                    'C' => 'Kabar di grup, karena banyak orang di grup itu yang membacanya.',
                    'D' => 'Kedua kabar sama kuat, karena topiknya sama.',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'INF-SUM',
                    'C' => 'INF-SUM',
                    'D' => 'INF-GEN',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-B15' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 15,
            'stimulus' => 'tes-mb-B-M4',
            'type'     => 'pgk',
            'show'     => [
                'prompt'     => 'Apakah pendapat setiap anak tepat? Pilih Benar kalau tepat, Salah kalau tidak tepat.',
                'statements' => [
                    '1' => 'Putri: "Kita belum tahu siapa yang menulis kabar di grup itu. Lebih baik dicocokkan dulu."',
                    '2' => 'Gilang: "Kabar di grup itu salah. Jadi, mulai sekarang semua kabar dari grup tidak usah dipercaya."',
                    '3' => 'Dodi: "Kabar di grup itu tentu benar. Ditulis dengan huruf besar, supaya cepat dibaca."',
                ],
                'scale'      => 'bs',
            ],
            'key'      => [
                'answer' => 'BSS',
                'errors' => [
                    '1' => 'INF-SUM',
                    '2' => 'INF-GEN',
                    '3' => 'INF-SUM',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-mb-B16' => [
            'part'     => 'mb',
            'form'     => 'B',
            'no'       => 16,
            'stimulus' => 'tes-mb-B-M4',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Apa cara yang paling tepat untuk memastikan hari dan jam lomba?',
                'options' => [
                    'A' => 'Mengabaikan semua kabar tentang lomba bidar.',
                    'B' => 'Membuat pilihan suara di grup tentang kabar mana yang benar.',
                    'C' => 'Memercayai kabar yang paling akhir masuk ke grup.',
                    'D' => 'Membaca unggahan akun resmi panitia, atau menanyakannya langsung kepada panitia lomba.',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'INF-GEN',
                    'B' => 'INF-SUM',
                    'C' => 'INF-SUM',
                ],
            ],
            'cell'     => [
                'sub'   => 'D1b',
                'level' => 'L3',
            ],
        ],
        'tes-ae-A01' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 1,
            'stimulus' => 'tes-ae-A01',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Tutup situsnya tanpa bercerita ke siapa pun',
                    'B' => 'Jangan diisi; beri tahu orang tuamu',
                    'C' => 'Isi semuanya supaya kartunya bisa langsung dibuat',
                    'D' => 'Isi dengan nama dan alamat teman sekelasmu',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'scores' => [
                    'A' => 1,
                    'B' => 2,
                    'C' => 0,
                    'D' => 0,
                ],
                'errors' => [
                    'A' => 'DIG-PSF',
                    'C' => 'DIG-PRI',
                    'D' => 'DIG-PRI',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Data pribadi',
                'complexity' => 'Jelas',
                'risk_code'  => 'DIG-PRI',
            ],
        ],
        'tes-ae-A02' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 2,
            'stimulus' => 'tes-ae-A02',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Tulis nama lengkap dan sekolahmu seperti penonton lain',
                    'B' => 'Jawab pertanyaan penonton itu supaya dapat teman baru',
                    'C' => 'Tulis nama lengkapmu saja, tanpa nama sekolah',
                    'D' => 'Tulis nama panggilanmu saja, tanpa nama sekolah dan rumah',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'scores' => [
                    'A' => 0,
                    'B' => 0,
                    'C' => 1,
                    'D' => 2,
                ],
                'errors' => [
                    'A' => 'DIG-PRI',
                    'B' => 'DIG-ASG',
                    'C' => 'DIG-PRI',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Data pribadi',
                'complexity' => 'Samar dan menggoda',
                'risk_code'  => 'DIG-PRI',
            ],
        ],
        'tes-ae-A03' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 3,
            'stimulus' => 'tes-ae-A03',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Kirim kata sandimu supaya namamu cepat dipasang',
                    'B' => 'Minta dia membuktikan dulu bahwa dia pengurus aplikasi',
                    'C' => 'Laporkan dan blokir akunnya bersama orang tua',
                    'D' => 'Tidak membalas pesannya, lalu menutup aplikasi itu',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'scores' => [
                    'A' => 0,
                    'B' => 0,
                    'C' => 2,
                    'D' => 1,
                ],
                'errors' => [
                    'A' => 'DIG-SAN',
                    'B' => 'DIG-ASG',
                    'D' => 'DIG-PSF',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Kata sandi',
                'complexity' => 'Jelas',
                'risk_code'  => 'DIG-SAN',
            ],
        ],
        'tes-ae-A04' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 4,
            'stimulus' => 'tes-ae-A04',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Tolak dengan ramah; sarankan fitur "Lupa kata sandi"',
                    'B' => 'Tolak dengan ketus: "Bikin akun sendiri, dong! Jangan pinjam punyaku!"',
                    'C' => 'Kirim kodenya, karena Kirana teman baikmu dan cuma sebentar',
                    'D' => 'Kirim kata sandimu saja, tapi kodenya jangan dikirim',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'scores' => [
                    'A' => 2,
                    'B' => 1,
                    'C' => 0,
                    'D' => 0,
                ],
                'errors' => [
                    'B' => 'DIG-ETK',
                    'C' => 'DIG-SAN',
                    'D' => 'DIG-SAN',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Kata sandi dan kode masuk (OTP)',
                'complexity' => 'Samar: diminta teman',
                'risk_code'  => 'DIG-SAN',
            ],
        ],
        'tes-ae-A05' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 5,
            'stimulus' => 'tes-ae-A05',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Unggah saja, karena Yasmin jarang membuka Kedugram',
                    'B' => 'Unggah dengan tulisan "Ada yang malu difoto, nih!"',
                    'C' => 'Unggah, tapi tutup wajah Yasmin dengan stiker',
                    'D' => 'Jangan unggah; simpan foto itu untukmu sendiri',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'scores' => [
                    'A' => 0,
                    'B' => 0,
                    'C' => 1,
                    'D' => 2,
                ],
                'errors' => [
                    'A' => 'DIG-IZN',
                    'B' => 'DIG-ETK',
                    'C' => 'DIG-IZN',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Izin foto atau video',
                'complexity' => 'Jelas: teman sudah menolak',
                'risk_code'  => 'DIG-IZN',
            ],
        ],
        'tes-ae-A06' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 6,
            'stimulus' => 'tes-ae-A06',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Langsung bagikan ke grup, karena niatmu baik',
                    'B' => 'Puji Rara, lalu tanya apakah fotonya boleh dibagikan',
                    'C' => 'Bagikan ke grup dengan tulisan "Gaya Rara, wkwk"',
                    'D' => 'Tanya teman-teman di grup, mau melihat fotonya atau tidak',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'scores' => [
                    'A' => 0,
                    'B' => 2,
                    'C' => 0,
                    'D' => 1,
                ],
                'errors' => [
                    'A' => 'DIG-IZN',
                    'C' => 'DIG-ETK',
                    'D' => 'DIG-IZN',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Izin foto atau video',
                'complexity' => 'Samar: niat baik, isi menyenangkan',
                'risk_code'  => 'DIG-IZN, DIG-ETK',
            ],
        ],
        'tes-ae-A07' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 7,
            'stimulus' => 'tes-ae-A07',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Jangan balas; tunjukkan ke orang tua atau guru',
                    'B' => 'Tidak membalas, lalu langsung menghapus pesan itu',
                    'C' => 'Pindah mengobrol ke aplikasi lain seperti ajakannya',
                    'D' => 'Sebutkan kelasmu saja, lalu berhenti mengobrol dengan akun itu',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'scores' => [
                    'A' => 2,
                    'B' => 1,
                    'C' => 0,
                    'D' => 0,
                ],
                'errors' => [
                    'B' => 'DIG-PSF',
                    'C' => 'DIG-ASG',
                    'D' => 'DIG-PRI',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Orang asing',
                'complexity' => 'Jelas',
                'risk_code'  => 'DIG-ASG',
            ],
        ],
        'tes-ae-A08' => [
            'part'     => 'ae',
            'form'     => 'A',
            'no'       => 8,
            'stimulus' => 'tes-ae-A08',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Tidak meneruskan, lalu diam saja di grup',
                    'B' => 'Kirim ke 20 teman supaya akunmu tetap gratis',
                    'C' => 'Jangan teruskan; cek dulu kabar itu di akun resmi aplikasinya',
                    'D' => 'Teruskan, tapi tambahkan "Info ini belum pasti, ya"',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'scores' => [
                    'A' => 1,
                    'B' => 0,
                    'C' => 2,
                    'D' => 0,
                ],
                'errors' => [
                    'A' => 'DIG-PSF',
                    'B' => 'DIG-HOX',
                    'D' => 'DIG-HOX',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Kabar bohong',
                'complexity' => 'Samar: pesan berantai dua langkah',
                'risk_code'  => 'DIG-HOX',
            ],
        ],
        'tes-ae-B01' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 1,
            'stimulus' => 'tes-ae-B01',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Isi semuanya supaya gambarnya bisa langsung diunduh',
                    'B' => 'Isi dengan nomor HP dan alamat tetanggamu',
                    'C' => 'Biarkan kosong, lalu ceritakan ke orang tuamu',
                    'D' => 'Tutup situsnya dan tidak bercerita ke siapa pun',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'scores' => [
                    'A' => 0,
                    'B' => 0,
                    'C' => 2,
                    'D' => 1,
                ],
                'errors' => [
                    'A' => 'DIG-PRI',
                    'B' => 'DIG-PRI',
                    'D' => 'DIG-PSF',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Data pribadi',
                'complexity' => 'Jelas',
                'risk_code'  => 'DIG-PRI',
            ],
        ],
        'tes-ae-B02' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 2,
            'stimulus' => 'tes-ae-B02',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Cukup tulis nama panggilanmu, tanpa nama kampung dan kelas',
                    'B' => 'Tulis nama lengkapmu saja, tanpa nama kampung',
                    'C' => 'Balas penonton itu, siapa tahu kalian seumuran',
                    'D' => 'Tulis nama lengkap dan kampungmu seperti penonton lain',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'scores' => [
                    'A' => 2,
                    'B' => 1,
                    'C' => 0,
                    'D' => 0,
                ],
                'errors' => [
                    'B' => 'DIG-PRI',
                    'C' => 'DIG-ASG',
                    'D' => 'DIG-PRI',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Data pribadi',
                'complexity' => 'Samar dan menggoda',
                'risk_code'  => 'DIG-PRI',
            ],
        ],
        'tes-ae-B03' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 3,
            'stimulus' => 'tes-ae-B03',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Kirim kata sandimu supaya gambarmu masuk galeri',
                    'B' => 'Minta dia mengirim bukti bahwa galerinya sungguhan',
                    'C' => 'Tidak membalas pesannya, lalu keluar dari aplikasi itu',
                    'D' => 'Minta orang tua membantu melaporkan akun itu',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'scores' => [
                    'A' => 0,
                    'B' => 0,
                    'C' => 1,
                    'D' => 2,
                ],
                'errors' => [
                    'A' => 'DIG-SAN',
                    'B' => 'DIG-ASG',
                    'C' => 'DIG-PSF',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Kata sandi',
                'complexity' => 'Jelas',
                'risk_code'  => 'DIG-SAN',
            ],
        ],
        'tes-ae-B04' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 4,
            'stimulus' => 'tes-ae-B04',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Tolak dengan ketus: "Urus saja akunmu sendiri! Jangan pinjam punyaku!"',
                    'B' => 'Tolak dengan ramah; sarankan dia mengganti kata sandinya',
                    'C' => 'Kirim kata sandimu saja, kodenya tetap kamu simpan',
                    'D' => 'Kirim kodenya, karena Bagas berjanji tidak mengubah apa pun',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'scores' => [
                    'A' => 1,
                    'B' => 2,
                    'C' => 0,
                    'D' => 0,
                ],
                'errors' => [
                    'A' => 'DIG-ETK',
                    'C' => 'DIG-SAN',
                    'D' => 'DIG-SAN',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Kata sandi dan kode masuk (OTP)',
                'complexity' => 'Samar: diminta teman',
                'risk_code'  => 'DIG-SAN',
            ],
        ],
        'tes-ae-B05' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 5,
            'stimulus' => 'tes-ae-B05',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Simpan saja videonya, sesuai permintaan Galang',
                    'B' => 'Unggah suaranya saja, tanpa memperlihatkan wajah Galang',
                    'C' => 'Unggah dengan tulisan "Ada penyanyi pemalu, nih!"',
                    'D' => 'Unggah saja, karena Galang tidak punya akun Kedugram',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'scores' => [
                    'A' => 2,
                    'B' => 1,
                    'C' => 0,
                    'D' => 0,
                ],
                'errors' => [
                    'B' => 'DIG-IZN',
                    'C' => 'DIG-ETK',
                    'D' => 'DIG-IZN',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Izin foto atau video',
                'complexity' => 'Jelas: teman sudah menolak',
                'risk_code'  => 'DIG-IZN',
            ],
        ],
        'tes-ae-B06' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 6,
            'stimulus' => 'tes-ae-B06',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Tanya teman-teman di grup, mau menonton videonya atau tidak',
                    'B' => 'Bagikan ke grup dengan tulisan "Pesulap dadakan, wkwk"',
                    'C' => 'Puji Hendra lewat pesan, lalu minta izinnya dulu',
                    'D' => 'Bagikan saja ke grup, toh niatmu baik',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'scores' => [
                    'A' => 1,
                    'B' => 0,
                    'C' => 2,
                    'D' => 0,
                ],
                'errors' => [
                    'A' => 'DIG-IZN',
                    'B' => 'DIG-ETK',
                    'D' => 'DIG-IZN',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Izin foto atau video',
                'complexity' => 'Samar: niat baik, isi menyenangkan',
                'risk_code'  => 'DIG-IZN, DIG-ETK',
            ],
        ],
        'tes-ae-B07' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 7,
            'stimulus' => 'tes-ae-B07',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Sebutkan umurmu saja, lalu berhenti mengobrol dengan akun itu',
                    'B' => 'Terima ajakan panggilan videonya, tapi sebentar saja',
                    'C' => 'Tidak membalas, lalu langsung menghapus pesan itu',
                    'D' => 'Abaikan pesannya; ceritakan ke orang tua atau guru',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'scores' => [
                    'A' => 0,
                    'B' => 0,
                    'C' => 1,
                    'D' => 2,
                ],
                'errors' => [
                    'A' => 'DIG-PRI',
                    'B' => 'DIG-ASG',
                    'C' => 'DIG-PSF',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Orang asing',
                'complexity' => 'Jelas',
                'risk_code'  => 'DIG-ASG',
            ],
        ],
        'tes-ae-B08' => [
            'part'     => 'ae',
            'form'     => 'B',
            'no'       => 8,
            'stimulus' => 'tes-ae-B08',
            'type'     => 'd2',
            'show'     => [
                'prompt'  => 'Apa yang sebaiknya kamu lakukan?',
                'options' => [
                    'A' => 'Kirim ke 15 orang supaya fotomu tidak terhapus',
                    'B' => 'Tahan dulu; periksa kabar itu di halaman resmi Kedugram',
                    'C' => 'Teruskan, tapi beri catatan "Aku juga belum yakin"',
                    'D' => 'Tidak meneruskan, lalu tidak menanggapi apa pun',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'scores' => [
                    'A' => 0,
                    'B' => 2,
                    'C' => 0,
                    'D' => 1,
                ],
                'errors' => [
                    'A' => 'DIG-HOX',
                    'C' => 'DIG-HOX',
                    'D' => 'DIG-PSF',
                ],
            ],
            'cell'     => [
                'sub'        => 'D2',
                'topic'      => 'Kabar bohong',
                'complexity' => 'Samar: pesan berantai dua langkah',
                'risk_code'  => 'DIG-HOX',
            ],
        ],
        'tes-bd-A01' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 1,
            'stimulus' => 'tes-bd-A-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Siapa yang membuat foto kain di unggahan ini?',
                'options' => [
                    'A' => '`@rak.kain.tia`, pemilik akun yang mengunggah',
                    'B' => 'Citra, yang menulis komentar pertama',
                    'C' => 'Galeri Kain Kenari Biru, sesuai tulisan di foto',
                    'D' => 'Panitia pameran yang menulis kartu keterangan',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'BUD-ATR',
                    'B' => 'BUD-ATR',
                    'D' => 'BUD-ATR',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Atribusi',
            ],
        ],
        'tes-bd-A02' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 2,
            'stimulus' => 'tes-bd-A-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Bacalah kartu keterangan. Kalimat mana yang tepat untuk membetulkan keterangan unggahan?',
                'options' => [
                    'A' => 'Motifnya disulam setelah benangnya diiris dan dicabut',
                    'B' => 'Motifnya digambar pakai spidol, seperti tulisan pengunggah',
                    'C' => 'Motifnya ditenun dengan benang emas',
                    'D' => 'Motifnya dicap dengan stempel karet',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'BUD-REP',
                    'C' => 'BUD-REP',
                    'D' => 'BUD-REP',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Representasi',
            ],
        ],
        'tes-bd-A03' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 3,
            'stimulus' => 'tes-bd-A-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin memasang foto kain ini di poster pameran budaya kelasmu. Apa yang sebaiknya kamu lakukan lebih dulu?',
                'options' => [
                    'A' => 'Unduh fotonya, lalu langsung tempel di poster pameran kelas',
                    'B' => 'Minta izin kepada `@rak.kain.tia`, pemilik akun pengunggah',
                    'C' => 'Tangkap layar, lalu potong bagian tulisan nama pembuatnya',
                    'D' => 'Minta izin dulu ke Galeri Kain Kenari Biru',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'BUD-IZN',
                    'B' => 'BUD-IZN',
                    'C' => 'BUD-ATR',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Izin',
            ],
        ],
        'tes-bd-A04' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 4,
            'stimulus' => 'tes-bd-A-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin menulis komentar. Komentar mana yang menghargai kain karawo ini?',
                'options' => [
                    'A' => '"Iya, kayak sarang laba-laba! Danu benar, wkwk"',
                    'B' => '"Motifnya indah. Pasti butuh ketelitian dan kesabaran, ya 👏"',
                    'C' => '"Keren! Hebat sekali bisa menggambar pakai spidol 🖍️"',
                    'D' => '"Kain kuno, mending pakai kaus biasa 😝"',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'BUD-ETK',
                    'C' => 'BUD-REP',
                    'D' => 'BUD-ETK',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Menghargai tradisi',
            ],
        ],
        'tes-bd-A05' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 5,
            'stimulus' => 'tes-bd-A-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut tanda di unggahan, siapa yang membuat video tari ini?',
                'options' => [
                    'A' => '`@kamera.ryan`, karena ia menulis "rekamanku sendiri"',
                    'B' => 'Panitia festival yang memasang papan informasi',
                    'C' => 'Kedugram, aplikasi tempat video itu diunggah',
                    'D' => 'Sanggar Lima Purnama',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'BUD-ATR',
                    'B' => 'BUD-ATR',
                    'C' => 'BUD-ATR',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Atribusi',
            ],
        ],
        'tes-bd-A06' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 6,
            'stimulus' => 'tes-bd-A-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Video ini disukai 9.400 orang, dan Wina menulis bahwa keterangannya benar. Bandingkan dengan papan informasi. Mana yang tepat?',
                'options' => [
                    'A' => 'Keterangannya benar, karena sudah disukai ribuan orang',
                    'B' => 'Keterangannya benar, karena pengunggah merekamnya sendiri di sana',
                    'C' => 'Keterangannya keliru: tari ini tentang menanam padi',
                    'D' => 'Keterangannya keliru: Tari Gantar bukan dari Kalimantan Timur',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'BUD-REP',
                    'B' => 'BUD-REP',
                    'D' => 'BUD-REP',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Representasi',
            ],
        ],
        'tes-bd-A07' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 7,
            'stimulus' => 'tes-bd-A-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin memakai video ini untuk tugas kelompok yang dikumpulkan besok pagi. Apa yang sebaiknya kamu lakukan lebih dulu?',
                'options' => [
                    'A' => 'Unduh sekarang, lalu minta izinnya setelah tugas dikumpulkan',
                    'B' => 'Minta izin ke Sanggar Lima Purnama hari ini',
                    'C' => 'Rekam layarnya, lalu tulis `@kamera.ryan` sebagai pembuat videonya',
                    'D' => 'Pakai saja, karena video itu sudah dibagikan ribuan orang',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'BUD-IZN',
                    'C' => 'BUD-ATR',
                    'D' => 'BUD-IZN',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Izin',
            ],
        ],
        'tes-bd-A08' => [
            'part'     => 'bd',
            'form'     => 'A',
            'no'       => 8,
            'stimulus' => 'tes-bd-A-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin membetulkan keterangan video itu lewat komentar. Komentar mana yang benar dan sopan?',
                'options' => [
                    'A' => '"Kak, menurut papan festival, ini tari menanam padi 🙏"',
                    'B' => '"Keliru! Masa hal semudah ini saja kamu tidak tahu? 🙄"',
                    'C' => '"Setuju, tari nelayan memang keren sekali! 🎣"',
                    'D' => '"Wkwk, tongkatnya kayak sapu lidi"',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'BUD-ETK',
                    'C' => 'BUD-REP',
                    'D' => 'BUD-ETK',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Menghargai tradisi',
            ],
        ],
        'tes-bd-B01' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 1,
            'stimulus' => 'tes-bd-B-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Siapa yang membuat foto kain di unggahan ini?',
                'options' => [
                    'A' => '`@lemari.ilham`, pemilik akun yang mengunggah',
                    'B' => 'Galeri Kain Camar Putih, sesuai tulisan di foto',
                    'C' => 'Panitia pameran yang membuat kartu keterangan',
                    'D' => 'Lusi, yang menulis komentar pertama',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'BUD-ATR',
                    'C' => 'BUD-ATR',
                    'D' => 'BUD-ATR',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Atribusi',
            ],
        ],
        'tes-bd-B02' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 2,
            'stimulus' => 'tes-bd-B-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Bacalah kartu keterangan. Kalimat mana yang tepat untuk membetulkan keterangan unggahan?',
                'options' => [
                    'A' => 'Motifnya disemprot pakai cat, seperti tulisan pengunggah',
                    'B' => 'Motifnya ditenun dengan benang perak',
                    'C' => 'Motifnya ditempel dari potongan kain',
                    'D' => 'Motifnya dicanting dengan lilin, lalu dicelup',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'BUD-REP',
                    'B' => 'BUD-REP',
                    'C' => 'BUD-REP',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Representasi',
            ],
        ],
        'tes-bd-B03' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 3,
            'stimulus' => 'tes-bd-B-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin memasang foto kain ini di buletin sekolah. Apa yang sebaiknya kamu lakukan lebih dulu?',
                'options' => [
                    'A' => 'Hubungi Galeri Kain Camar Putih untuk minta izin',
                    'B' => 'Unduh fotonya, lalu langsung pasang di halaman buletin sekolah',
                    'C' => 'Tangkap layar, lalu hapus bagian tulisan nama pembuatnya',
                    'D' => 'Minta izin kepada `@lemari.ilham`, pemilik akun pengunggah',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'BUD-IZN',
                    'C' => 'BUD-ATR',
                    'D' => 'BUD-IZN',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Izin',
            ],
        ],
        'tes-bd-B04' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 4,
            'stimulus' => 'tes-bd-B-G1',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin menulis komentar. Komentar mana yang menghargai kain besurek ini?',
                'options' => [
                    'A' => '"Kain jadul, siapa yang mau pakai? 😝"',
                    'B' => '"Keren! Hebat sekali bisa disemprot pakai cat 🎨"',
                    'C' => '"Kainnya indah. Membuatnya pasti perlu ketekunan tinggi, ya 👏"',
                    'D' => '"Iya, kayak coretan! Bimo benar, wkwk"',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'BUD-ETK',
                    'B' => 'BUD-REP',
                    'D' => 'BUD-ETK',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Menghargai tradisi',
            ],
        ],
        'tes-bd-B05' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 5,
            'stimulus' => 'tes-bd-B-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Menurut tanda di unggahan, siapa yang membuat video tari ini?',
                'options' => [
                    'A' => 'Sanggar Tujuh Pelita',
                    'B' => 'Kedugram, aplikasi tempat video itu dibagikan',
                    'C' => '`@tontonan.dina`, karena ia menulis "rekamanku sendiri"',
                    'D' => 'Panitia festival yang membuat papan informasi',
                ],
            ],
            'key'      => [
                'answer' => 'A',
                'errors' => [
                    'B' => 'BUD-ATR',
                    'C' => 'BUD-ATR',
                    'D' => 'BUD-ATR',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Atribusi',
            ],
        ],
        'tes-bd-B06' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 6,
            'stimulus' => 'tes-bd-B-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Video ini disukai 9.100 orang, dan Ratih menulis bahwa keterangannya benar. Bandingkan dengan papan informasi. Mana yang tepat?',
                'options' => [
                    'A' => 'Keterangannya keliru: Tari Maengket bukan dari Sulawesi Utara',
                    'B' => 'Keterangannya keliru: ini tari syukur panen padi',
                    'C' => 'Keterangannya benar, karena pengunggah sendiri yang merekamnya',
                    'D' => 'Keterangannya benar, sebab sudah disukai ribuan orang',
                ],
            ],
            'key'      => [
                'answer' => 'B',
                'errors' => [
                    'A' => 'BUD-REP',
                    'C' => 'BUD-REP',
                    'D' => 'BUD-REP',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Representasi',
            ],
        ],
        'tes-bd-B07' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 7,
            'stimulus' => 'tes-bd-B-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin memakai video ini untuk tugas presentasi yang dikumpulkan besok pagi. Apa yang sebaiknya kamu lakukan lebih dulu?',
                'options' => [
                    'A' => 'Pakai saja, toh video itu sudah ditonton ribuan orang',
                    'B' => 'Rekam layarnya, lalu tulis `@tontonan.dina` sebagai pembuat videonya',
                    'C' => 'Unduh dulu sekarang, izinnya diminta setelah tugas selesai',
                    'D' => 'Hubungi Sanggar Tujuh Pelita hari ini untuk izin',
                ],
            ],
            'key'      => [
                'answer' => 'D',
                'errors' => [
                    'A' => 'BUD-IZN',
                    'B' => 'BUD-ATR',
                    'C' => 'BUD-IZN',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Izin',
            ],
        ],
        'tes-bd-B08' => [
            'part'     => 'bd',
            'form'     => 'B',
            'no'       => 8,
            'stimulus' => 'tes-bd-B-G2',
            'type'     => 'pg',
            'show'     => [
                'prompt'  => 'Kamu ingin membetulkan keterangan video itu lewat komentar. Komentar mana yang benar dan sopan?',
                'options' => [
                    'A' => '"Ngawur! Masa hal semudah ini saja kamu tidak tahu? 🙄"',
                    'B' => '"Setuju, tari menyambut raja memang megah! 👑"',
                    'C' => '"Kak, papan festival menyebut ini tari syukur panen 🙏"',
                    'D' => '"Wkwk, gerakannya kayak senam pagi"',
                ],
            ],
            'key'      => [
                'answer' => 'C',
                'errors' => [
                    'A' => 'BUD-ETK',
                    'B' => 'BUD-REP',
                    'D' => 'BUD-ETK',
                ],
            ],
            'cell'     => [
                'sub'  => 'D3',
                'kind' => 'Menghargai tradisi',
            ],
        ],
    ],
];
