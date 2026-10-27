# Telaah Butir dan Dimensi Literasi Digital

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 25 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Status: **rumusan keputusan berlaku** sejak kisi-kisi [06](06_KISI_KISI_BANK_SOAL.md) disetujui pengembang (25 Oktober 2026); perbaikan butir sudah dimasukkan ke draf bank soal [08](08_DRAF_BANK_SOAL.md)

Dokumen ini menelaah usulan keputusan pengembang tentang tiga dimensi dan penskoran (25 Oktober 2026), lalu menilai keterkaitan setiap butir bank soal dengan dimensi utamanya. Telaah ini berperan sebagai penyaringan awal sebelum validasi ahli. Telaah ini **tidak** menggantikan penilaian dua ahli dan tidak mengubah sistem apa pun.

## Ringkasan

1. **Usulan pengembang layak disetujui**, dengan enam penajaman: batas D2 dan D3 dipertegas, D3 dibangun sebagai babak baru di Cari Objek, butir Skenario Digital diskor 0–1–2, bacaan diperlakukan sebagai testlet, tebakan cepat ditangani, dan jumlah sampel ditetapkan lebih dulu. Rinciannya di *Telaah atas usulan keputusan*.
2. **Bank soal yang sekarang memuat 99 butir dinilai**, bukan 129. Angka 129 ikut menghitung 30 keping Susun Gambar, padahal Susun Gambar hanya pemanasan dan tidak dinilai. Tiap siswa mengerjakan **41 butir** di pengerjaan pertama: 29 butir D1 dan 12 petunjuk Cari Objek.
3. **D1 kuat sebagai literasi membaca, tapi bukti "digital"-nya tipis.** Dari 29 butir D1 yang dikerjakan seorang siswa, rata-rata hanya sekitar 5 yang benar-benar menilai sumber atau memilah fakta dan pendapat. Sisanya pemahaman bacaan biasa yang kebetulan dibaca di layar.
4. **Tidak satu pun dari 12 petunjuk Cari Objek memenuhi definisi D3 yang baru.** Semuanya mengukur pengenalan benda budaya. Artinya D3 harus dibangun dari butir baru. Usulan kami: Cari Objek jadi dua babak. Babak 1 tetap seperti sekarang dan menjadi skala pendamping *Pengetahuan Budaya Kedu*; babak 2, *Periksa Unggahan*, memuat 4 butir D3 per wilayah.
5. **13 butir D1 berelevansi rendah** karena bisa dijawab tanpa membaca teks atau jawabannya bocor: 12 perlu ditulis ulang, 1 cukup diganti judul variasinya. Selain itu ada **1 kesalahan fakta** (jumlah relief Borobudur yang bercerita), **2 kebocoran jawaban** lewat judul variasi, dan **2 pernyataan yang kuncinya bisa diperdebatkan**.
6. **Nama area DigComp 3.0 di dokumen utama perlu dikoreksi.** "Hak, pilihan, dan tanggung jawab digital" bukan nama area di DigComp 3.0.

## Telaah atas usulan keputusan

Usulan pengembang dikutip per bagian, lalu diberi tanggapan.

### 1. Satu dimensi utama per butir

> Setiap butir memiliki satu dimensi utama yang menjadi dasar penskoran. Kode kesalahan atau label sekunder dapat digunakan untuk analitik diagnostik ...

**Setuju.** Inilah model multidimensi *between-item*: tiap butir hanya memuat satu dimensi (Adams, Wilson, & Wang, 1997). Model ini yang paling mudah diuji dan dijelaskan, dan bisa dijalankan di paket `TAM` (R) atau ConQuest.

Satu konsekuensi perlu ditulis terang-terangan: di desain ini **setiap dimensi diukur dengan satu jenis mekanisme**. D1 lewat Isi Rumpang dan Benar atau Salah, D2 lewat Skenario Digital, D3 lewat Cari Objek babak 2. Kalau model tiga dimensi lebih cocok daripada model satu dimensi, perbedaan itu bisa datang dari dimensinya, bisa juga dari mekanismenya. Klaim "tiga dimensi terbukti terpisah" harus ditulis dengan hati-hati dan dicatat sebagai keterbatasan.

### 2. D1 Literasi Informasi, sekitar 29 butir

**Setuju, dengan dua penajaman.**

*a. Akui bahwa D1 bertumpu pada literasi membaca.* Kerangka besar memang menempatkan membaca sebagai dasar literasi informasi digital. DigComp 3.0 kompetensi 1.2 menuntut *"to assess and compare the credibility and reliability of sources ... to interpret and critically evaluate information"*. PISA 2018 juga memindahkan seluruh unit barunya ke teks digital dan menambah teks multisumber. Di bank kita, butir yang sungguh-sungguh menilai sumber hanya ada di `mgl-3` (fakta dan pendapat) serta `wnb-3-a` dan `wnb-3-c` (pesan berantai vs. sumber resmi). Karena itu D1 sebaiknya diberi dua label sub-aspek (bukan dimensi baru):

| Sub-aspek | Isi | Level AKM | Butir per siswa (perkiraan) |
| --- | --- | --- | --- |
| D1a Memahami informasi | Menemukan dan memadukan informasi di teks | Menemukan informasi; interpretasi dan integrasi | ± 24 |
| D1b Menilai informasi dan sumber | Fakta dan pendapat, kredibilitas, kabar yang bertentangan | Evaluasi dan refleksi | ± 5 (2–7, bergantung variasi) |

*b. Ubah beberapa bacaan menjadi teks bergenre digital.* Ini cara termurah untuk menaikkan bukti "digital" tanpa menambah butir. Contohnya papan informasi digital, unggahan akun resmi dinas pariwisata, pesan di grup kelas, atau halaman hasil pencarian. Level kesulitan tetap bisa diatur: di Temanggung tugasnya tetap menemukan informasi tersurat, hanya bentuk teksnya yang berubah. Prioritas utamanya `wnb-3-b` (Bukit Sikunir). Di variasi ini kedua sumber berupa ucapan lisan, padahal variasi `a` dan `c` memakai pesan berantai dan akun resmi. Akibatnya sepertiga siswa Wonosobo tidak mendapat satu pun butir penilaian sumber digital, sehingga ketiga variasi tidak setara dalam konstruk.

### 3. D2 Aman dan Etis, sekitar 12 butir

**Setuju, dengan tiga penajaman.**

*a. Batas D2 dan D3.* D2 = melindungi diri dan orang lain: data pribadi, kata sandi dan OTP, orang asing, kabar bohong, izin sebelum membagikan foto diri atau foto orang lain. Etika yang menyangkut karya dan tradisi budaya pindah ke D3. Dengan begitu kode `DIG-IZN` dan `DIG-ETK` tidak perlu lagi berlabel ganda D2/D3 (lihat *Dampak ke kode*). Padanannya di DigComp 3.0: 4.2 *Protecting personal data and privacy*, 4.3 *Supporting wellbeing* (termasuk menghadapi bahaya daring), dan 2.2 *Sharing through digital technologies*.

*b. Satu skenario = satu butir, diskor 0–1–2.* Pilihan tindakan sudah punya tiga sifat: tepat, kurang tepat, berisiko. Skornya: tepat = 2, kurang tepat = 1, berisiko = 0, dianalisis dengan *partial credit model* (Masters, 1982). Butir bertingkat memberi informasi lebih banyak daripada butir benar-salah, jadi ini menolong reliabilitas D2 yang hanya 12 butir. Urutan kategori harus dicek saat uji coba: ambang Andrich harus berurutan.

*c. Alasan dicatat untuk diagnosis, tidak ikut skor utama di versi pertama.* Ada tiga alasan:
- Kalau tindakan dan alasan diskor terpisah, keduanya saling bergantung (ketergantungan lokal).
- R-ATUR ("karena disuruh atau dilarang") tidak tepat disebut salah. Penalaran berbasis aturan wajar pada usia 10–11 tahun dan tetap aman. Usulan urutannya: R-ACAK dan R-SOS < R-ATUR < R-RISK dan R-ETIK. Urutan ini dipakai di analisis tambahan setelah uji coba.
- Pertanyaan alasan muncul apa pun tindakan yang dipilih, jadi kalimatnya harus netral terhadap tindakan. Contoh: "Yang paling penting kupikirkan di sini adalah …". Tanpa itu, siswa yang memilih "langsung kirim" bisa disodori alasan "hadiah dari orang tak dikenal bisa jadi tipuan", yang tidak nyambung.

Metrik kata sandi saat daftar tetap dipakai, tapi sebagai **bukti validitas eksternal** D2 (apakah skor D2 sejalan dengan perilaku nyata), bukan sebagai butir, karena stimulusnya tidak sama untuk semua siswa.

### 4. D3 menjadi Partisipasi dan Representasi Budaya Kedu di Ruang Digital

**Setuju, dan ini penajaman paling penting dari usulan pengembang.** Rumusan baru ini sejalan dengan cara Komdigi sendiri mengukur pilar Budaya Digital. Indikator pilar itu di survei *Status Literasi Digital Indonesia 2022* antara lain:
- "Saya mencantumkan nama penulis saat repost"
- "Saya berbagi seni budaya tradisional dan kontemporer Indonesia secara digital"
- "Saya selalu mempertimbangkan dan menyadari keragaman budaya di media sosial saat membagikan pesan"

Ketiganya soal partisipasi dan representasi, bukan pengetahuan budaya. Padanannya di DigComp 3.0 adalah 2.2 (berbagi secara etis), 2.3 (*engaging in citizenship*), 2.5 *Digital behaviour* ("respect cultural, generational and other diversity"), dan 3.3 *Copyright and licences*.

**Konsekuensinya: D3 harus dibangun baru.** Kami menelaah 12 petunjuk Cari Objek satu per satu, dan semuanya meminta siswa menemukan benda budaya dari ciri-cirinya. Tidak ada yang meminta siswa memahami, memvalidasi, merepresentasikan, atau membagikan informasi budaya di ruang digital. Jadi skor D3 dari butir yang ada sekarang = 0 butir.

**Usulan: Cari Objek dua babak.**

| Babak | Isi | Jumlah | Dipakai untuk |
| --- | --- | --- | --- |
| 1 Temukan Benda | Seperti sekarang: 4 petunjuk, cari benda budaya di adegan | 4 per wilayah | Skala pendamping **Pengetahuan Budaya Kedu**, di luar skor literasi digital |
| 2 Periksa Unggahan | Adegan berupa unggahan atau lini masa tiruan tentang benda budaya tadi; siswa mengetuk bagian yang tepat | 4 per wilayah | **D3**, 12 butir per siswa |

Kenapa dua babak, bukan memindahkan D3 ke Skenario Digital:
- **Mekanisme ketuk sudah ada.** Babak 2 cukup memakai engine Cari Objek dengan adegan kedua, termasuk 3 kesempatan per petunjuk.
- **Tantangan kelima tidak jadi terlalu panjang.** Kalau D3 masuk Skenario Digital, tiap wilayah butuh 8 skenario dua langkah, terlalu lama untuk anak 10–11 tahun dalam satu tantangan.
- **Babak 1 tetap berguna.** Judul penelitian menyebut *Etnopedagogi Kedu*. Skala pengetahuan budaya mendukung klaim itu, dan bisa dipakai sebagai kovariat: apakah siswa yang lebih kenal budaya Kedu lebih baik di D3?
- **Pengetahuan yang dibutuhkan disediakan di tugas.** Petunjuk babak 2 harus memuat sendiri fakta budaya yang diperlukan (misalnya "Bundengan adalah alat musik dari Wonosobo"), atau menyediakan sumber pembanding di adegan (papan informasi museum, akun resmi). Dengan begitu yang diukur adalah memeriksa dan merepresentasikan, bukan mengingat.

**Empat jenis butir D3, satu di setiap wilayah:**

| Jenis | Yang diukur | Kode kesalahan baru (usulan) | DigComp 3.0 |
| --- | --- | --- | --- |
| Atribusi | Menemukan atau mencantumkan pembuat dan sumber karya budaya | BUD-ATR | 3.3 |
| Representasi akurat | Memeriksa keterangan tentang asal atau makna budaya dengan sumber pembanding | BUD-REP | 1.2, 2.2 |
| Izin pelaku dan pemilik karya | Meminta izin sebelum mengunggah ulang penampilan atau karya sanggar | BUD-IZN | 3.3, 2.2 |
| Menghargai tradisi | Memilih komentar atau tanggapan yang menghormati tradisi; menolak ejekan | BUD-ETK | 2.5 |

**Contoh babak 2 Wonosobo (sulit).** Adegan: unggahan akun *JelajahDieng* berisi video bundengan, dengan keterangan "Angklung khas Jawa Barat, keren banget! 🎶", dua komentar, dan tombol *Bagikan*. Di pojok adegan ada papan informasi Museum Kailasa Dieng.

| No | Petunjuk | Target yang tepat | Pengecoh dan kodenya |
| --- | --- | --- | --- |
| 1 | Keterangan video ini keliru. Bandingkan dengan papan museum, lalu ketuk bagian yang perlu dibetulkan. | Teks "Angklung khas Jawa Barat" | Judul akun (BUD-REP), jumlah suka (NON-SEN) |
| 2 | Ketuk komentar yang membetulkan keterangan dengan sopan dan menyebut sumbernya. | "Itu bundengan dari Wonosobo, Kak. Ada di papan Museum Kailasa 🙏" | "Salah! Gitu aja nggak tahu" (BUD-ETK); "Wkwk sama aja" (BUD-REP) |
| 3 | Video ini milik Sanggar Ngesti Laras. Ketuk tanda yang menunjukkan siapa pembuatnya. | Tulisan kecil "Video: Sanggar Ngesti Laras" | Nama akun pengunggah (BUD-ATR) |
| 4 | Kamu ingin memakai video ini untuk tugas sekolah. Ketuk langkah pertama yang tepat. | "Kirim pesan ke sanggar minta izin" | *Bagikan* langsung (BUD-IZN); *Unduh* (BUD-IZN) |

Nama sanggar di contoh ini fiktif dan harus diganti atau dicek sebelum dipakai. Kerumitan naik per wilayah dengan pola yang sama seperti Skenario Digital. Di Temanggung tanda kelirunya jelas dan pengecohnya sedikit. Di Magelang tandanya samar, misalnya keterangan menyebut Topeng Ireng dari Temanggung padahal kartu informasi menyebut Desa Tuksongo, Magelang. Di Wonosobo ada nilai yang bertentangan, seperti contoh di atas.

### 5. Target jumlah butir sementara; Rasch sebagai uji empiris

**Setuju.** Ada empat tambahan yang perlu masuk rencana analisis.

*a. Bacaan diperlakukan sebagai testlet.* Butir dalam satu bacaan Isi Rumpang atau Benar atau Salah berbagi teks yang sama. Di Isi Rumpang mereka juga berbagi bank kata, sehingga jawaban satu rumpang mempersempit pilihan rumpang lain. Di Temanggung (3 rumpang, 4 kata), kalau dua rumpang sudah benar, rumpang ketiga tinggal pilihan 1 dari 2. Ketergantungan seperti ini membuat reliabilitas tampak lebih tinggi dari aslinya, dan cloze termasuk jenis soal dengan ketergantungan lokal paling besar (Baghaei & Ravand, 2016). Langkahnya:
- Periksa statistik Q3 Yen. Pasangan butir yang Q3-nya lebih dari 0,2 di atas rata-rata Q3 dianggap bergantung (Christensen, Makransky, & Horton, 2017).
- Kalau ketergantungannya terbukti, satukan butir satu bacaan menjadi satu butir bertingkat (*super-item*, partial credit), atau pakai model Rasch testlet (Wang & Wilson, 2005).

Konsekuensinya perlu jujur dicatat: 29 butir D1 seorang siswa sebenarnya berasal dari **6 bacaan**. Jumlah informasi yang efektif lebih kecil daripada kesan dari angka 29.

*b. Kriteria pembanding model satu dan tiga dimensi.* Bandingkan deviance, AIC, dan BIC. Laporkan korelasi laten antardimensi; kalau korelasinya di atas 0,90, kedua dimensi praktis tidak terbedakan dan sebaiknya digabung. Laporkan reliabilitas EAP/PV per dimensi.

*c. Interpretasi skor perorangan.* Dengan 12 butir, reliabilitas orang di D2 dan D3 kemungkinan besar di bawah patokan umum 0,80 untuk keputusan perorangan. D2 dan D3 dilaporkan di tingkat kelompok dan butir. Skor perorangan cukup untuk skor gabungan dan D1, selalu disertai galat bakunya.

*d. Jumlah sampel menentukan desain variasi.* Ini perlu diputuskan sebelum pengambilan data. Linacre (1994) memberi patokan: sekitar 50 orang memberi taksiran kesulitan butir dalam ±1 logit (keyakinan 99%), sekitar 200 orang dalam ±0,5 logit. Masalahnya, siswa hanya mengerjakan satu dari tiga variasi (a–c) per tantangan, jadi setiap butir D1 hanya dikerjakan sepertiga sampel.

| Sampel total | Siswa per butir variasi | Ketelitian taksiran butir |
| --- | --- | --- |
| 150 | ± 50 | ± 1 logit: cukup untuk melihat urutan kasar |
| 300 | ± 100 | Di antara keduanya |
| 600 | ± 200 | ± 0,5 logit |

Kalau sampel di bawah sekitar 300, ada dua pilihan, masing-masing dengan harganya:
- **Satu variasi penelitian per tantangan.** Semua siswa mengerjakan butir yang sama, tapi data untuk memeriksa kesetaraan variasi hilang dan risiko siswa mencontek tetangga naik.
- **Dua variasi (a/b) diselang-seling per kursi** menurut denah L2. Setiap butir dikerjakan setengah sampel, dan mencontek tetap dicegah.

### 6. Percobaan pertama untuk skor utama

**Setuju.** Tiga rincian perlu ditetapkan:

- **Arti "percobaan pertama" per mekanisme.** Untuk Isi Rumpang dan Benar atau Salah: Cek pertama, saat semua butir dinilai sekaligus. Untuk Cari Objek babak 2: ketukan pertama per petunjuk. Untuk Skenario Digital: pilihan tindakan pertama.
- **Tebakan cepat.** Jawaban dengan waktu di bawah ambang NON-TEB diperlakukan sebagai data hilang dalam analisis kepekaan, mengikuti *effort-moderated scoring* (Wise & DeMars, 2006). Ini penting terutama untuk Benar atau Salah, yang peluang tebakannya 50%.
- **Percobaan kedua tidak setara dengan percobaan pertama.** Di Isi Rumpang, rumpang yang sudah benar terkunci dan pilihan kata berkurang. Karena itu percobaan berikutnya cukup dianalisis sebagai matriks perpindahan (diperbaiki, kesalahan sama, kesalahan lain), tidak diskor ulang.

## Usulan rumusan keputusan

Rumusan pengembang, disesuaikan dengan telaah di atas. Kalau disetujui, teks ini masuk [04_CATATAN_KEPUTUSAN.md](04_CATATAN_KEPUTUSAN.md) dan dokumen utama diperbarui.

> **Keputusan desain dimensi dan penskoran asesmen GELITA.** Rancangan tiga dimensi literasi digital disetujui. Setiap butir punya satu dimensi utama yang menjadi dasar skor; kode kesalahan dan label sekunder hanya untuk analisis diagnostik.
>
> **D1 Literasi Informasi** diukur lewat Isi Rumpang dan Benar atau Salah (target awal 29 butir per siswa). D1 diberi dua label sub-aspek: memahami informasi dan menilai informasi serta sumber. Sebagian bacaan diubah ke genre teks digital, dan ketiga variasi tiap tantangan dibuat setara dalam bentuk teks.
>
> **D2 Aman dan Etis Bermedia Digital** diukur lewat Skenario Digital (target awal 12 skenario per siswa), sebagai adaptasi konseptual khusus GELITA atas pilar Aman dan Etis. D2 mencakup perlindungan data pribadi, kata sandi dan OTP, orang asing, kabar bohong, dan izin membagikan foto diri atau orang lain. Tindakan diskor 0–1–2 (berisiko, kurang tepat, tepat); alasan dianalisis sebagai data diagnostik dan tidak masuk skor utama di versi pertama.
>
> **D3 Partisipasi dan Representasi Budaya Kedu di Ruang Digital** diukur lewat babak kedua Cari Objek, *Periksa Unggahan* (target awal 12 butir per siswa): atribusi, representasi akurat, izin pelaku dan pemilik karya, dan sikap menghargai tradisi. Babak pertama Cari Objek tetap ada sebagai skala pendamping Pengetahuan Budaya Kedu dan tidak masuk skor literasi digital.
>
> Semua target jumlah butir sementara sampai validasi isi dan uji coba selesai. Analisis Rasch dipakai untuk memeriksa kecocokan butir, dimensionalitas, ketergantungan lokal (bacaan sebagai testlet), dan ketelitian pengukuran. Perbandingan model satu dan tiga dimensi adalah uji empiris, bukan asumsi, dan ditafsirkan dengan mengingat bahwa setiap dimensi diukur dengan mekanisme yang berbeda. Keterbatasan jumlah butir D2 dan D3 dilaporkan terbuka, dan skor perorangan hanya ditafsirkan sejauh reliabilitas dan galat bakunya mengizinkan.
>
> Skor utama memakai percobaan pertama; tebakan cepat ditangani lewat analisis kepekaan. Percobaan berikutnya dianalisis terpisah sebagai indikator proses belajar, pemakaian bantuan, dan perubahan strategi.

## Telaah per butir

### Cara menilai

Setiap butir dinilai pada skala relevansi 1–4. Skala ini sama dengan yang nanti dipakai ahli untuk menghitung indeks validitas isi (I-CVI; Lynn, 1986) atau V Aiken (Aiken, 1985), jadi hasil telaah ini bisa langsung dibandingkan dengan penilaian ahli.

| Skor | Arti untuk dimensi utama |
| --- | --- |
| 4 | Sangat relevan: tugas langsung menuntut kompetensi inti dimensi (untuk D1: menilai sumber, memilah fakta dan pendapat, menangani kabar yang bertentangan) |
| 3 | Relevan: tugas menuntut proses membaca atau memahami informasi dari teks yang menjadi dasar dimensi, tapi belum ada ciri digitalnya |
| 2 | Kurang relevan: butir bisa dijawab tanpa teks, dari pengetahuan umum, norma, atau bentuk kata saja |
| 1 | Tidak relevan untuk dimensi ini |

Level AKM ditulis L1 (menemukan informasi), L2 (interpretasi dan integrasi), atau L3 (evaluasi dan refleksi). Untuk butir fakta dan pendapat, labelnya L3 sementara: ringkasan kerangka AKM menempatkan menilai keandalan teks di evaluasi dan refleksi, tapi istilah "fakta dan opini" belum kami temukan di dokumen resmi Pusmendik. Ini perlu disepakati dua penilai.

Kolom "Jawab" memuat kunci jawaban. Butir yang diistirahatkan (`mgl-2-a04`, `mgl-2-c04`, `wnb-2-c05`, `wnb-2-c07`) tidak ditelaah.

### Temanggung (mudah)

**`tmg-2` Isi Rumpang, D1.** Tiga rumpang, satu kata pengecoh per variasi. Judul variasi tampil di layar.

| Kode | Inti soal | Jawab | AKM | Relevansi | Catatan |
| --- | --- | --- | --- | --- | --- |
| tmg-2-a01 | Diapit Gunung Sindoro dan Gunung ___ | Sumbing | L1 | 2 | Kata "Sumbing" tidak ada di teks; terjawab dari pengetahuan awal atau karena satu-satunya nama diri di bank kata. **Tulis ulang** |
| tmg-2-a02 | Bentuknya mirip, disebut gunung ___ | kembar | L2 | 2 | **Bocor:** judul variasi "Gunung Kembar" tampil di layar. Ganti judulnya |
| tmg-2-a03 | Tanah di lereng ___ karena abu gunung api | subur | L2 | 3 | Baik; pengecoh "tandus" berlawanan arti |
| tmg-2-b01 | Tidak pernah kering, bahkan pada musim ___ | kemarau | L2 | 3 | Baik; menuntut pemahaman "bahkan" |
| tmg-2-b02 | Air dari Umbul ___ mengalir ke hulu Progo | Jumprit | L1 | 3 | Tersurat di kalimat sebelumnya |
| tmg-2-b03 | Tidak membuang ___ di sekitar mata air | sampah | L1 | 2 | Terjawab dari norma umum. **Tulis ulang** supaya bergantung pada teks |
| tmg-2-c01 | Kuda-kudaan ___ itu dicat | bambu | L1 | 3 | Tersurat di kalimat yang sama |
| tmg-2-c02 | Lebih dari 1.500 kelompok, jadi sangat ___ | populer | L2 | 3 | Baik; menyimpulkan dari angka |
| tmg-2-c03 | Anak muda diajak ___ menari | berlatih | L1 | 2 | Terjawab dari kolokasi kata. **Tulis ulang** |

**`tmg-3` Benar atau Salah, D1.**

| Kode | Inti pernyataan | Jawab | AKM | Relevansi | Catatan |
| --- | --- | --- | --- | --- | --- |
| tmg-3-a01 | Liyangan ditemukan tahun 2008 | benar | L1 | 3 | — |
| tmg-3-a02 | Tertimbun letusan Merapi | salah | L1 | 3 | Pengecoh detail (INF-LIT) |
| tmg-3-a03 | Gambar: benda temuan Liyangan (butir padi hangus) | benar | L1 | 3 | Mencocokkan gambar dan teks; mutu gambar menentukan |
| tmg-3-b01 | Pringapus candi Buddha | salah | L1 | 3 | — |
| tmg-3-b02 | Gondosuli dibuat 832 M | benar | L1 | 3 | — |
| tmg-3-b03 | Gambar: arca di ruang Pringapus (gambar arca Buddha) | salah | L2 | 3 | Baik: bacaan menjelaskan Nandi = arca lembu, jadi siswa bisa membandingkan |
| tmg-3-c01 | Kopi tumbuh di atas 900 m | benar | L1 | 3 | — |
| tmg-3-c02 | Kopi cocok untuk anak-anak | salah | L1 | 3 | — |
| tmg-3-c03 | Gambar: biji kopi sangrai | benar | L1 | 3 | — |

**`tmg-4` Cari Objek, babak 1, skala Pengetahuan Budaya Kedu (bukan D3).**

| Kode | Yang dicari | Relevansi D3 | Catatan |
| --- | --- | --- | --- |
| tmg-4-a01 | Kuda-kudaan anyaman bambu | 1 | Pengenalan benda; pengecoh antardaerah (angklung, ondel-ondel, pinisi, sasando) sudah baik untuk skala pengetahuan budaya |
| tmg-4-a02 | Biji kopi lereng Sindoro–Sumbing | 1 | — |
| tmg-4-a03 | Arca lembu Nandi | 1 | — |
| tmg-4-a04 | Ikan uceng | 1 | Fakta dicek: olahan uceng Temanggung terdaftar Indikasi Geografis di DJKI. Bentuk ikan kecil sulit dibedakan di gambar; pastikan cirinya jelas |

### Magelang (sedang)

**`mgl-2` Isi Rumpang, D1.** Lima rumpang aktif, dua kata pengecoh per variasi.

| Kode | Inti soal | Jawab | AKM | Relevansi | Catatan |
| --- | --- | --- | --- | --- | --- |
| mgl-2-a01 | 6 teras persegi + 3 bundar = ___ teras | sembilan | L2 | 3 | Memadukan dua angka; pengecoh "enam" baik (INF-LIT) |
| mgl-2-a02 | Bangunan lonceng terbesar = ___ induk | stupa | L1 | 3 | Istilah didefinisikan di teks |
| mgl-2-a06 | Ada ___ arca Buddha di dalam stupa | 72 | L2 | 3 | Simpulan kecil: satu arca per stupa |
| mgl-2-a03 | Pahatan bercerita disebut ___ | relief | L1 | 2 | Kata "relief" tidak ada di teks; menuntut kosakata awal. **Tulis ulang** (definisikan di teks) |
| mgl-2-a05 | Batu rusak bila dipanjat, jadi pengunjung ___ memanjat | dilarang | L2 | 3 | Baik; sebab–akibat |
| mgl-2-b01 | Kecamatan Borobudur, dekat Candi ___ | Borobudur | L1 | 3 | — |
| mgl-2-b02 | Kaki dihentakkan, lonceng ___ | berbunyi | L2 | 3 | — |
| mgl-2-b03 | Satu penari lebih cepat, tarian tidak ___ | kompak | L2 | 3 | Baik; menyimpulkan dari "bergerak serempak" |
| mgl-2-b04 | Gerakan menggambarkan semangat para ___ | petani | L1 | 3 | — |
| mgl-2-b05 | Duduk tenang, jangan ___ para penari | mengganggu | L1 | 2 | Terjawab dari norma; pengecoh "diam" tidak gramatikal sehingga mudah dibuang. **Tulis ulang** |
| mgl-2-c01 | Bahan utama getuk = ___ | singkong | L1 | 3 | — |
| mgl-2-c02 | Singkong sebagai ___ beras | pengganti | L2 | 3 | Baik; pengecoh "beras" (INF-KAT) |
| mgl-2-c03 | Benda tanah liat dibakar disebut ___ | gerabah | L1 | 2 | Kata "gerabah" tidak didefinisikan di teks dan **bocor** lewat judul variasi "Getuk dan Gerabah Klipoh". **Tulis ulang** dan ganti judulnya |
| mgl-2-c06 | Dina membuat sambal, memakai ___ | cobek | L2 | 3 | Sangat baik: menerapkan informasi; pengecoh "kuali" |
| mgl-2-c05 | Diwariskan dari orang tua kepada ___ | anak | L1 | 2 | Terjawab dari arti "turun-temurun" atau kolokasi. **Tulis ulang** |

**`mgl-3` Benar atau Salah, D1.** Strukturnya setara di ketiga variasi: satu gagasan pokok, satu memadukan informasi, satu klasifikasi tindakan, dua fakta atau pendapat.

| Kode | Inti pernyataan | Jawab | AKM | Relevansi | Catatan |
| --- | --- | --- | --- | --- | --- |
| mgl-3-a01 | Gagasan pokok paragraf 2: menjaga Borobudur | benar | L2 | 3 | — |
| mgl-3-a02 | Relief lebih dari 2.000 panel | benar | L2 | 3 | **Fakta perlu dibetulkan di bacaan:** 2.672 panel adalah jumlah semua relief; yang bercerita 1.460, sisanya 1.212 hiasan. Ganti jadi "2.672 panel relief; lebih dari separuhnya bercerita". Kuncinya tetap benar |
| mgl-3-a03 | Ucapan Pak Darto = fakta | salah | L3 | 4 | Fakta dan pendapat (INF-FOP) |
| mgl-3-a04 | Membawa pulang sampah = menjaga | benar | L2 | 2 | Terjawab dari norma umum. **Tulis ulang** supaya menuntut aturan khusus dari bacaan |
| mgl-3-a05 | "Menjaga Borobudur tugas mulia" = fakta | salah | L3 | 4 | Baik: pendapat penulis tanpa penanda "menurut saya" |
| mgl-3-b01 | Paragraf 1 terutama soal Waisak | salah | L2 | 3 | — |
| mgl-3-b02 | Candi awal lebih tua dari candi akhir | benar | L2 | 3 | Sangat baik: tiga langkah. Fakta dicek: Mendut umumnya disebut yang tertua dari ketiganya |
| mgl-3-b03 | Ucapan Bu Ratna = pendapat | benar | L3 | 4 | — |
| mgl-3-b04 | Memotret menghalangi umat = menghormati | salah | L2 | 2 | Terjawab dari norma. Ini **kandidat babak 2 D3** kalau ditambah unsur mengunggah foto upacara |
| mgl-3-b05 | Ucapan biksu (perjalanan dimulai dari Mendut) = fakta | benar | L3 | 4 | Baik dan menantang: kutipan bisa berisi fakta |
| mgl-3-c01 | Paragraf 1 terutama soal Gunung Tidar dan legendanya | benar | L2 | 3 | **Bisa diperdebatkan:** paragraf 1 juga memuat tinggi bukit, pendakian, aturan, dan ucapan Kak Dewi, jadi "terutama ... legendanya" tidak tegas. Ubah jadi "terutama menceritakan Gunung Tidar" |
| mgl-3-c02 | Ucapan Kak Dewi = fakta | salah | L3 | 4 | — |
| mgl-3-c03 | Turun lewat tangga sambil bawa sampah = sesuai aturan | benar | L2 | 2 | Terjawab dari norma. **Tulis ulang** |
| mgl-3-c04 | "Magelang berulang tahun 11 April" = fakta | benar | L3 | 4 | Mungkin terlalu mudah; cek di uji coba |
| mgl-3-c05 | Mantyasih dibuat sesudah Akmil | salah | L2 | 3 | Baik: membandingkan dua tahun |

**`mgl-4` Cari Objek, babak 1, skala Pengetahuan Budaya Kedu.**

| Kode | Yang dicari | Relevansi D3 | Catatan |
| --- | --- | --- | --- |
| mgl-4-a01 | Stupa berlubang | 1 | — |
| mgl-4-a02 | Kuluk Topeng Ireng | 1 | Fakta dicek: Topeng Ireng muncul sekitar 1950-an di Desa Tuksongo, Borobudur. Pengecoh dadak merak Reog baik |
| mgl-4-a03 | Getuk | 1 | Pengecoh bakpia dan manisan carica baik (antardaerah) |
| mgl-4-a04 | Gerabah Klipoh | 1 | Pengecoh panci logam baik (bahan berbeda) |

### Wonosobo (sulit)

**`wnb-2` Isi Rumpang, D1.** Tujuh rumpang, dua kata pengecoh. Sebagian besar menguji kata penghubung (sehingga, karena, tetapi, lalu). Butir semacam ini masuk **L2 interpretasi dan integrasi**, bukan L3. Artinya, di Wonosobo hanya Benar atau Salah yang mewakili L3; klaim "Wonosobo = evaluasi dan refleksi" hanya berlaku untuk separuh butirnya.

| Kode | Inti soal | Jawab | AKM | Relevansi | Catatan |
| --- | --- | --- | --- | --- | --- |
| wnb-2-a01 | Tertutup kabut ___ dijuluki negeri di atas awan | sehingga | L2 | 3 | Pengecoh "agar" baik (INF-HUB) |
| wnb-2-a02 | Embun membeku ___ suhu di bawah 0 °C | karena | L2 | 3 | — |
| wnb-2-a03 | Sebaiknya membawa pakaian ___ | hangat | L2 | 3 | Pengecoh "panas" |
| wnb-2-a04 | Carica tumbuh di 1.400–2.400 m, ___ sulit di dataran rendah | tetapi | L2 | 3 | — |
| wnb-2-a05 | Sembungan 2.300 m, jadi carica ___ tumbuh | dapat | L2 | 3 | Sangat baik: membandingkan angka dengan rentang |
| wnb-2-a06 | Pantai panas, jadi carica ___ tumbuh | sulit | L2 | 3 | Bergantung kuat pada a04–a05 (testlet) |
| wnb-2-a07 | Carica membutuhkan udara yang ___ | sejuk | L2 | 3 | Simpulan dari seluruh teks |
| wnb-2-b01 | Dimasukkan ke ongklok, ___ dicelupkan | lalu | L2 | 3 | Urutan |
| wnb-2-b03 | Kuah kental ___ dari tepung kanji | karena | L2 | 3 | — |
| wnb-2-b04 | Udara dingin, ___ warga suka makanan berkuah | sehingga | L2 | 3 | — |
| wnb-2-b05 | Rina punya mi dan kol, masih perlu daun ___ | kucai | L2 | 3 | Sangat baik: menerapkan daftar bahan |
| wnb-2-b06 | Enak selagi hangat, ___ kuahnya bisa sangat panas | tetapi | L2 | 3 | — |
| wnb-2-b02 | Nama mi berasal dari nama ___ perebusnya | alat | L2 | 3 | — |
| wnb-2-b07 | Paling cocok disantap saat udara ___ | dingin | L2 | 3 | — |
| wnb-2-c01 | Panas lepas ___ tidak ada awan | karena | L2 | 3 | — |
| wnb-2-c02 | Udara dingin terkumpul, ___ suhu turun | sehingga | L2 | 3 | — |
| wnb-2-c03 | Embun pagi ___ menjadi es | membeku | L2 | 3 | — |
| wnb-2-c04 | Indah bagi wisatawan, ___ merusak kentang | tetapi | L2 | 3 | — |
| wnb-2-c08 | Upas = racun, membuat kentang ___ lalu mati | layu | L2 | 3 | Sebagian bergantung pada pengetahuan tanaman |
| wnb-2-c06 | Datang pada ___ hari, es cepat mencair | pagi | L2 | 3 | Pengecoh "siang" baik |
| wnb-2-c09 | Embun upas terbentuk setelah malam yang ___ | cerah | L2 | 3 | Pengecoh "berawan" baik; simpulan sebab–akibat |

**`wnb-3` Benar atau Salah, D1.** Variasi `a` dan `c` adalah butir D1b terbaik di bank: dua sumber bergenre digital (pesan berantai vs. papan pengelola atau akun resmi PVMBG). Variasi `b` memakai ucapan lisan, sehingga tidak setara (lihat telaah bagian 2).

| Kode | Inti pernyataan | Jawab | AKM | Relevansi | Catatan |
| --- | --- | --- | --- | --- | --- |
| wnb-3-a01 | Kabar pertama lebih dipercaya karena pembuatnya jelas | benar | L3 | 4 | Kredibilitas sumber (INF-SUM) |
| wnb-3-a02 | Kedua kabar membahas hal sama tapi bertentangan | benar | L3 | 4 | Mengenali kabar yang bertentangan |
| wnb-3-a03 | Kedua kabar sama-sama menyarankan meminum air | salah | L2 | 4 | Membandingkan dua sumber |
| wnb-3-a04 | Memeriksa pembuat lalu bertanya ke guru = tepat | benar | L3 | 4 | Strategi verifikasi |
| wnb-3-a05 | Bertanya ke teman lebih membantu daripada ke guru | salah | L3 | 3 | **Bisa diperdebatkan:** bacaan tidak menilai teman sama sekali. Ubah jadi "teman yang juga hanya membaca pesan berantai itu" |
| wnb-3-a06 | Boleh minum asal airnya jernih | salah | L3 | 4 | Menerapkan bukti |
| wnb-3-b01 | Ucapan Bayu dan pesan pengelola sama | salah | L2 | 3 | Sumber lisan, bukan digital |
| wnb-3-b02 | Keputusan Sinta didukung pesan pengelola | benar | L3 | 3 | — |
| wnb-3-b03 | Senter perlu karena mendaki sebelum matahari terbit | benar | L2 | 3 | — |
| wnb-3-b04 | Ucapan Bayu bukti kuat bahwa puncak hangat | salah | L3 | 3 | Akan jadi 4 kalau ucapan Bayu diubah menjadi pesan di grup kelas |
| wnb-3-b05 | Keluar jalur sebentar untuk berfoto bukan masalah | salah | L3 | 2 | Terjawab dari norma umum. **Tulis ulang** |
| wnb-3-b06 | Solusi: setiap wisatawan membawa pulang sampahnya | benar | L3 | 2 | Terjawab dari norma umum. **Tulis ulang**, misalnya dengan dua solusi yang sama-sama masuk akal dan bukti di bacaan yang menentukan |
| wnb-3-c01 | Pesan berantai dan pengumuman resmi berbeda saran | benar | L2 | 4 | — |
| wnb-3-c02 | Pesan berantai dipercaya karena banyak beredar | salah | L3 | 4 | Viral bukan berarti benar (INF-SUM) |
| wnb-3-c03 | Pengumuman resmi dipercaya karena lembaganya jelas | benar | L3 | 4 | — |
| wnb-3-c04 | Rambu petugas lebih pantas diikuti | benar | L3 | 4 | Keahlian sumber |
| wnb-3-c05 | Boleh mendekati kawah asal cerah | salah | L3 | 3 | — |
| wnb-3-c06 | Harus batal karena pengumuman melarang datang | salah | L3 | 4 | Sangat baik: menguji ketelitian membaca isi sumber resmi |

**`wnb-4` Cari Objek, babak 1, skala Pengetahuan Budaya Kedu.**

| Kode | Yang dicari | Relevansi D3 | Catatan |
| --- | --- | --- | --- |
| wnb-4-a01 | Bundengan (dari ciri tudung penggembala bebek) | 1 | Teka-teki ciri; pengecoh angklung dan sasando baik |
| wnb-4-a02 | Carica | 1 | Pengecoh pepaya biasa sangat baik |
| wnb-4-a03 | Ongklok (keranjang bambu) | 1 | Pengecoh semangkuk mi ongklok sangat baik (benda vs. makanan) |
| wnb-4-a04 | Daun teh Tambi | 1 | Fakta "sejak 1865" dicek ulang saat validasi ahli |

### Rekap

| Wilayah | Butir dinilai di bank | Relevansi 4 | 3 | 2 | Per siswa, pengerjaan pertama |
| --- | --- | --- | --- | --- | --- |
| Temanggung (D1) | 18 | 0 | 14 | 4 | 6 butir D1 + 4 Cari Objek |
| Magelang (D1) | 30 | 6 | 17 | 7 | 10 butir D1 + 4 Cari Objek |
| Wonosobo (D1) | 39 | 10 | 27 | 2 | 13 butir D1 + 4 Cari Objek |
| **Jumlah D1** | **87** | **16** | **58** | **13** | **29** |
| Cari Objek babak 1 (pendamping) | 12 | — | — | — | 12, di luar skor literasi digital |
| D2 Skenario Digital | 0 (belum ditulis) | | | | 12 |
| D3 Periksa Unggahan | 0 (belum ditulis) | | | | 12 |

Butir berelevansi 4 hanya ada di Magelang dan Wonosobo, sesuai rancangan kesulitan. Tapi itu juga berarti bukti D1b sepenuhnya bergantung pada dua tantangan Benar atau Salah.

## Daftar perbaikan butir

Semua perbaikan ini dilakukan sebelum validasi ahli. Kode butir tidak berubah; isi boleh diganti karena data penelitian utama belum diambil. Butir yang diubah kembali berstatus `needs_verification`.

| Prioritas | Butir | Masalah | Usulan |
| --- | --- | --- | --- |
| Tinggi | Bacaan `mgl-3-a` | Kesalahan fakta: "2.672 panel relief yang bercerita" | "2.672 panel relief; lebih dari separuhnya bercerita" |
| Tinggi | Judul `tmg-2-a`, `mgl-2-c` | Judul variasi membocorkan jawaban (*kembar*, *gerabah*) | Ganti judul, misalnya "Dua Gunung Temanggung", "Jajanan dan Kerajinan Magelang" |
| Tinggi | `wnb-3-b` | Satu-satunya variasi Wonosobo tanpa sumber digital | Ucapan Bayu jadi pesan di grup kelas; pesan pengelola jadi unggahan akun resmi desa wisata |
| Sedang | tmg-2-a01, tmg-2-b03, tmg-2-c03, mgl-2-a03, mgl-2-b05, mgl-2-c03, mgl-2-c05 | Isi Rumpang yang terjawab tanpa teks | Pastikan jawaban atau petunjuknya ada di bacaan; untuk istilah, definisikan dulu di teks |
| Sedang | mgl-3-a04, mgl-3-b04, mgl-3-c03, wnb-3-b05, wnb-3-b06 | Klasifikasi tindakan yang terjawab dari norma umum | Buat tindakan yang hanya bisa dinilai dengan aturan khusus di bacaan, atau pindahkan `mgl-3-b04` ke babak 2 D3 |
| Sedang | mgl-3-c01, wnb-3-a05 | Kunci jawaban bisa diperdebatkan | Rumusan lihat tabel per butir |
| Pertimbangkan | `tmg-2` (semua variasi) | Satu pengecoh untuk tiga rumpang: rumpang terakhir hampir terpaksa | Dua pengecoh di Temanggung juga. Ini mengubah aturan jumlah pengecoh di `DifficultyRules`, jadi perlu keputusan |
| Pertimbangkan | Bacaan `tmg-3` dan `mgl-3` | Belum bergenre digital | Ubah satu bacaan per tantangan menjadi teks digital (papan informasi digital, unggahan akun resmi) |

## Dampak ke dokumen lain

Kalau rumusan keputusan disetujui, perubahan berikut ikut dilakukan:

**[01_REVISI_10_ANALITIK.md](01_REVISI_10_ANALITIK.md)**
- Tabel dimensi: nama D3 baru; kolom "Diukur lewat" (D3 = Cari Objek babak 2; Cari Objek babak 1 = skala pendamping); kolom "Area DigComp 3.0" dibetulkan:

  | Dimensi | Area dan kompetensi DigComp 3.0 |
  | --- | --- |
  | D1 | 1 *Information search, evaluation and management* (1.1, 1.2) |
  | D2 | 4 *Safety, wellbeing and responsible use* (4.2, 4.3); 2.2 *Sharing* |
  | D3 | 2 *Communication and collaboration* (2.2, 2.3, 2.5); 3.3 *Copyright and licences* |

- "129 butir yang dinilai" di langkah pelevelan dan daftar kerja diganti "99 butir yang dinilai (di luar 30 keping Susun Gambar)".
- Pertanyaan terbuka tentang konstruk dicentang. Pertanyaan baru ditambahkan: jumlah sampel dan desain variasi (bagian 5d di atas), dan dua pengecoh di Temanggung.
- Catatan perubahan sistem: tambah Cari Objek babak 2, skor 0–1–2 untuk tindakan Skenario Digital, dan label sub-aspek D1a/D1b di bank soal.

**[03_KODE_DAN_ARTI.md](03_KODE_DAN_ARTI.md)**
- Arti D3 diganti.
- Kode baru BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK (dimensi D3).
- Label ganda dihapus: INF-BUD hanya untuk D1 dan skala pendamping; DIG-IZN dan DIG-ETK hanya D2.
- Tambah D1a dan D1b.

**[04_CATATAN_KEPUTUSAN.md](04_CATATAN_KEPUTUSAN.md):** entri baru dengan rumusan keputusan di atas.

## Rujukan

**Kerangka dan kebijakan (dibuka 25 Oktober 2026)**

- Cosgrove, J., & Cachia, R. (2025). *DigComp 3.0: European Digital Competence Framework* (edisi ke-5). Joint Research Centre, [DOI](https://doi.org/10.2760/0001149). Nama area dan deskripsi kompetensi 1.2, 2.2, 2.3, 2.5, 3.3, 4.2, dan 4.3 dikutip dari Tabel 2: [PDF](https://digitalekompetenzen.oead.at/fileadmin/Dokumente/oead.at/Bildung_Digital/Digitale_Kompetenzen/NRDK/2025_DigComp_3.0_European_Digital_Competence_Framework.pdf), [repositori JRC](https://publications.jrc.ec.europa.eu/repository/handle/JRC144121).
- Kementerian Kominfo & Katadata Insight Center. (2023). *Status Literasi Digital di Indonesia 2022* (diluncurkan Februari 2023; pilarnya disebut Kecakapan, Etika, Keamanan, dan Budaya Digital). Definisi empat pilar dan indikator pilar Budaya Digital: [PDF](https://cdn1.katadata.co.id/media/microsites/litdik/ReportSurveiStatusLiterasiDigitalIndonesia2022.pdf).
- OECD. (2019). *PISA 2018 Assessment and Analytical Framework*. Proses membaca dan teks multisumber digital: [OECD](https://www.oecd.org/en/publications/pisa-2018-assessment-and-analytical-framework_b25efab8-en/full-report/component-3.html).
- Ringkasan level kognitif AKM: [akupintar.id](https://akupintar.id/info-pintar/-/blogs/komponen-asesmen-kompetensi-minimum). Dokumen kerangka resminya tetap perlu diunduh dari Pusmendik.
- Helsper, E. J., Schneider, L. S., van Deursen, A. J. A. M., & van Laar, E. (2020). *The youth Digital Skills Indicator*. ySKILLS: [Zenodo](https://dx.doi.org/10.5281/zenodo.4476540). Pembanding: memisahkan pengetahuan dan keterampilan, fungsional dan kritis.

**Pengukuran**

- Adams, R. J., Wilson, M., & Wang, W.-C. (1997). The multidimensional random coefficients multinomial logit model. *Applied Psychological Measurement, 21*(1), 1–23.
- Aiken, L. R. (1985). Three coefficients for analyzing the reliability and validity of ratings. *Educational and Psychological Measurement, 45*(1), 131–142.
- Baghaei, P., & Ravand, H. (2016). Modeling local item dependence in cloze and reading comprehension test items using testlet response theory. *Psicológica, 37*, 85–104: [PDF](https://www.uv.es/revispsi/articulos1.16/5BAGHAEI.pdf).
- Christensen, K. B., Makransky, G., & Horton, M. (2017). Critical values for Yen's Q3: Identification of local dependence in the Rasch model using residual correlations. *Applied Psychological Measurement, 41*(3), 178–194.
- Linacre, J. M. (1994). Sample size and item calibration stability. *Rasch Measurement Transactions, 7*(4), 328: [rasch.org](https://www.rasch.org/rmt/rmt74m.htm).
- Lynn, M. R. (1986). Determination and quantification of content validity. *Nursing Research, 35*(6), 382–385.
- Masters, G. N. (1982). A Rasch model for partial credit scoring. *Psychometrika, 47*(2), 149–174.
- Wang, W.-C., & Wilson, M. (2005). The Rasch testlet model. *Applied Psychological Measurement, 29*(2), 126–149.
- Wise, S. L., & DeMars, C. E. (2006). An application of item response time: The effort-moderated IRT model. *Journal of Educational Measurement, 43*(1), 19–38.

**Cek fakta isi bacaan (dibuka 25 Oktober 2026)**

| Fakta | Hasil | Sumber |
| --- | --- | --- |
| 2.672 panel relief Borobudur, semuanya bercerita | **Dikoreksi:** 1.460 bercerita, 1.212 hiasan | [Wikipedia: Borobudur](https://en.wikipedia.org/wiki/Borobudur) |
| Mendut lebih tua daripada Borobudur | Benar menurut rujukan umum (Mendut disebut yang tertua dari tiga candi); kaitannya dengan prasasti Karangtengah masih diperdebatkan | [Wikipedia: Mendut](https://en.wikipedia.org/wiki/Mendut) |
| Topeng Ireng muncul sekitar 1950-an di Desa Tuksongo, Borobudur | Benar | [GNFI 2024](https://www.goodnewsfromindonesia.id/2024/07/09/merayakan-warisan-budaya-topeng-kawedar-melalui-grebeg-topeng-ireng) |
| Ikan uceng makanan khas Temanggung | Benar; olahan uceng Temanggung terdaftar Indikasi Geografis | [Hukumonline](https://www.hukumonline.com/berita/a/perlindungan-dan-pengembangan-produk-berbasis-ig-dapat-kembangkan-daya-saing-lt5d5685c7bfe5f/?page=2), [Antara Jateng](https://jateng.antaranews.com/berita/175036/peranakan-uceng-hasil-penelitian-ditebar-di-sungai) |
