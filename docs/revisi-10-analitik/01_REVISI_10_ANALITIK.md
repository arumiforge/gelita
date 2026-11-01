# Revisi 10 Analitik Penelitian GELITA

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 23 Oktober 2026 · Dokumen revisi, belum implementasi

Mulai sekarang, 10 analitik penelitian jadi pegangan kita untuk GELITA. Sesuai judul penelitian, literasi digital jadi konstruk utama dengan tiga dimensi, dan Skenario Digital masuk sebagai tantangan kelima. Admin dan peneliti jadi dua peran yang terpisah, dan log yang tidak dibutuhkan dihapus. Status setiap dokumen dan urutan bacanya ada di [00_INDEKS.md](00_INDEKS.md). Data yang tidak bisa direkam game kita kumpulkan lewat lembar lapangan L0–L8 ([02_LEMBAR_LAPANGAN.md](02_LEMBAR_LAPANGAN.md)). Arti semua kode ada di [03_KODE_DAN_ARTI.md](03_KODE_DAN_ARTI.md), dan riwayat keputusan di [04_CATATAN_KEPUTUSAN.md](04_CATATAN_KEPUTUSAN.md). Telaah setiap butir terhadap dimensinya ada di [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md). Kisi-kisi bank soal baru ada di [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md), telaah instrumen validasi ahli di [07_TELAAH_INSTRUMEN_VALIDASI.md](07_TELAAH_INSTRUMEN_VALIDASI.md), draf bank soal baru di [08_DRAF_BANK_SOAL.md](08_DRAF_BANK_SOAL.md), telaah pendahuluan bank soal di [09_TELAAH_PENDAHULUAN_BANK_SOAL.md](09_TELAAH_PENDAHULUAN_BANK_SOAL.md), telaah pendahuluan media di [10_TELAAH_PENDAHULUAN_MEDIA.md](10_TELAAH_PENDAHULUAN_MEDIA.md), cara menghitung setiap data penelitian di kamus data [11_KAMUS_DATA.md](11_KAMUS_DATA.md), dan cerita penghubung serta narasi tantangan kelima di [12_CERITA_PENGHUBUNG_DAN_NARASI.md](12_CERITA_PENGHUBUNG_DAN_NARASI.md).

Dokumen ini belum mengubah sistem apa pun. Isinya rencana kerja tim; dokumen implementasi yang lengkap disusun setelah revisi ini selesai.

## Keputusan yang sudah diambil

Ada sepuluh keputusan awal, dicatat pengembang. Semuanya berlaku mulai dokumen ini. Judul penelitian dan 10 analitik berasal dari peneliti (lihat *Peran pengembang, peneliti, dan ahli*).

| Keputusan | Artinya buat penelitian |
| --- | --- |
| Judul penelitian ditetapkan (lihat di atas) | Literasi digital jadi konstruk utama. Literasi membaca dan pengetahuan budaya Kedu masuk sebagai dimensinya (lihat bagian berikut). |
| Literasi digital **wajib** ada | Game butuh tugas baru soal keamanan dan etika digital. Analitik 10 (pilihan dan alasan di skenario digital) tetap dipakai. |
| Skenario Digital jadi **tantangan kelima** di tiap wilayah | Totalnya jadi 15 tantangan dan 15 Serpihan Cahaya. Cerita, narasi, dan peta perlu menyesuaikan. |
| **Semua rekomendasi telaah** diterima | Evidence-Centered Design, level AKM/PIRLS sebagai acuan utama, skema kategori kesalahan, waktu aktif, uji Rasch, butir jangkar, dan observasi lapangan masuk rencana. |
| **Admin dan peneliti** dua peran yang berbeda | Admin mengurus sistem dan identitas. Peneliti memegang data penelitian yang sudah disamarkan. Lihat bagian *Admin dan peneliti*. |
| Log yang tidak dibutuhkan **dihapus**, sisanya **dipisah** | Setiap log masuk salah satu dari tiga: dihapus, hanya untuk admin, atau untuk peneliti. Lihat bagian *Log: dihapus atau dipisah*. |
| Guru **tidak ikut** dalam penelitian | Guru cukup membantu menyiapkan perangkat dan internet. Kolom "Informasi bagi guru" kita ganti jadi "Makna bagi penelitian". Semua pengamatan dilakukan peneliti. |
| Laporan untuk guru cuma **ide tambahan** | Laporan kelas untuk guru di luar lingkup penelitian. Kita simpan sebagai gagasan pengembangan nanti. |
| Semua yang ada di game **boleh dirombak total** | Tidak ada fitur yang wajib dipertahankan. Batasnya hanya kebutuhan penelitian: kunci jawaban tetap di server, kode butir yang sudah punya data siswa tidak dipakai ulang untuk isi lain, dan data mentah tetap lengkap. |
| **Belum ada implementasi** di sistem | Sekarang fokusnya desain instrumen, validasi, dan persiapan lapangan. Perubahan sistem dicatat di bagian akhir dan baru dikerjakan setelah desainnya disetujui. |

Karena guru tidak ikut, bantuan guru pada isi soal harus dicegah lewat arahan sebelum sesi. Kalau tetap terjadi, kita catat di lembar L3.

## Peran pengembang, peneliti, dan ahli

Ditetapkan 27 Oktober 2026 ([04](04_CATATAN_KEPUTUSAN.md)). Bagian ini berlaku untuk semua dokumen di folder revisi 10 analitik.

Revisi ini dikerjakan di dua jalur. Setiap isi, keputusan, dan perubahan menyebut jalur asalnya, supaya jelas mana yang masih usulan pengembang dan mana yang sudah ditetapkan peneliti bersama ahli.

| Jalur | Siapa | Yang dikerjakan | Kedudukannya |
| --- | --- | --- | --- |
| **Pengembang** (berbantuan AI) | Pengembang GELITA, dibantu AI | Draf, label, skor, telaah, cek fakta, usulan revisi, dan berkas siap kirim. Pengembang boleh berperan sebagai ahli, komite, atau pihak lain untuk latihan dan penyaringan awal, misalnya memberi skor pada butir instrumen validasi | Sementara. Ditandai sebagai kerja pengembang |
| **Peneliti** | Ketua peneliti dan tim peneliti | Menerima kerja pengembang, menindaklanjutinya, menyerahkannya ke ahli, komite, dan pihak terkait, lalu menetapkannya | Sah setelah disetujui peneliti |
| **Peneliti bersama ahli dan komite** | Peneliti dengan validator ([03](03_KODE_DAN_ARTI.md#peran)), *Lembaga Komite Etik*, sekolah, dan pihak lain yang terlibat | Validasi ahli, izin etik, izin sekolah | Final, setelah ditetapkan peneliti |

**Judul penelitian dan 10 analitik berasal dari peneliti.** Pengembang menurunkannya menjadi rancangan, instrumen, dan berkas.

Aturannya:

1. **Kerja pengembang bukan validasi ahli.** Skor dan label pengembang, termasuk yang dibuat dengan bantuan AI, tidak ikut dihitung dalam V Aiken, I-CVI, S-CVI, κ, atau ICC, dan tidak dilaporkan sebagai penilaian ahli (T6 di [07](07_TELAAH_INSTRUMEN_VALIDASI.md#t6-penelaah-pendahuluan-berbantuan-ai)).
2. **Ahli menilai tanpa melihat skor atau label pengembang.** Pelabelan buta B.5a tetap buta, dan lembar lain juga dikirim tanpa skor pengembang supaya penilaian ahli tidak terpengaruh. Skor pengembang baru dibandingkan sesudahnya, di matriks revisi ([20](20_PAKET_VALIDASI.md)).
3. **Tidak ada yang beku penuh sebelum final.** "Dibekukan" berarti versinya dicatat (tanggal dan *commit* di [04](04_CATATAN_KEPUTUSAN.md)), bukan tidak boleh diubah. Perubahan sesudahnya tetap boleh, mendadak atau nanti, asalkan dicatat di 04 dengan asal dan akibatnya: perlu dinilai ulang ahli, perlu amandemen ke komite etik, atau tidak perlu apa-apa. Versi yang mengikat pihak lain:
   - bahan paket validasi, karena ahli menilai versi itu ([20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan), prasyarat no. 4);
   - bahan untuk anak yang dilampirkan ke izin etik, karena perubahannya diajukan sebagai amandemen protokol ([22](22_PERMOHONAN_IZIN_ETIK.md));
   - skema kode kesalahan setelah uji coba (*Skema kategori kesalahan* di bawah);
   - kamus data sebelum data diambil ([11](11_KAMUS_DATA.md)).
4. **Satu orang yang memegang dua peran menandai keputusannya per peran.** Keputusan yang diambil sebagai pengembang tetap kerja pengembang sampai disetujui sebagai peneliti.
5. **Keputusan yang dicatat sebelum 27 Oktober 2026** sebagai keputusan "pemilik proyek" sekarang ditulis sebagai keputusan pengembang. Kedudukannya *disetujui pengembang*; peneliti mengukuhkannya saat menindaklanjuti ([00 → Label asal dan status](00_INDEKS.md#label-asal-dan-status)).
6. **Kerja pengembang yang sudah lengkap mempercepat tindak lanjut.** Peneliti tidak mulai dari nol: ia menyetujui, menolak, atau merevisi bahan yang sudah siap, lalu membawanya ke ahli atau komite. Peneliti tetap bertanggung jawab atas setiap keputusan final.
7. **Di naskah penelitian, bantuan AI ditulis terbuka** sebagai *telaah pendahuluan berbantuan AI oleh tim pengembang*, bagian dari tahap pengembangan, bukan bagian dari validasi ahli.

## Judul penelitian dan konstruk

Judulnya menyebut GELITA sebagai **model asesmen literasi digital**. Jadi yang kita ukur adalah literasi digital siswa, dan Kedu adalah konteks budayanya. Literasi membaca dan pengetahuan budaya tidak lagi berdiri sebagai konstruk sendiri; keduanya jadi dimensi literasi digital. Penyelarasan ini sudah disetujui pengembang (25 Oktober 2026), bersama rumusan dimensi di [05](05_TELAAH_BUTIR_DAN_DIMENSI.md) dan kisi-kisi di [06](06_KISI_KISI_BANK_SOAL.md).

| Dimensi | Artinya | Pilar Komdigi | Area DigComp 3.0 | Diukur lewat | Kode kesalahan | Acuan level |
| --- | --- | --- | --- | --- | --- | --- |
| D1 Literasi informasi | Bertumpu pada literasi membaca: menemukan, menafsirkan, dan menilai informasi dari teks dan gambar di layar, termasuk teks bergenre digital. Sub-aspek D1a memahami informasi dan D1b menilai informasi dan sumber | Cakap Bermedia Digital | 1 *Information search, evaluation and management* (1.1, 1.2) | Isi Rumpang, Benar atau Salah, kartu jangkar | INF-* | Level AKM, padanan PIRLS 2026 |
| D2 Aman dan etis | Menjaga data pribadi, kata sandi, dan OTP; minta izin sebelum membagikan foto atau obrolan diri sendiri dan teman; menghadapi orang asing dan kabar bohong | Aman dan Etis Bermedia Digital | 4 *Safety, wellbeing and responsible use* (4.2, 4.3); 2.2 *Sharing* | Skenario Digital (tindakan diskor 0–1–2); metrik kata sandi saat daftar sebagai bukti validitas eksternal | DIG-*, R-* | Kerumitan skenario per wilayah |
| D3 Partisipasi dan representasi budaya Kedu di ruang digital | Atribusi, representasi akurat, izin pelaku dan pemilik karya, dan sikap menghargai tradisi saat budaya Kedu tampil di ruang digital | Budaya Bermedia Digital | 2 *Communication and collaboration* (2.2, 2.3, 2.5); 3.3 *Copyright and licences* | Cari Objek babak 2 *Periksa Unggahan*. Babak 1 jadi skala pendamping Pengetahuan Budaya Kedu (PBK) | BUD-* | Kerumitan adegan per wilayah |

Kerangka payungnya: empat pilar literasi digital Komdigi dan Siberkreasi, dengan DigComp 3.0 (Joint Research Centre, 2025) sebagai padanan internasional. Untuk dimensi D1, level kognitifnya tetap mengikuti AKM dan PIRLS 2026.

## Kerangka: peta bukti

Kita pakai cara pikir Evidence-Centered Design: tentukan dulu apa yang mau kita klaim, lalu bukti apa yang mendukung, lalu tugas apa yang menghasilkan bukti itu. Klaimnya tentang literasi digital siswa di tiga dimensi.

![Peta bukti: tugas menghasilkan bukti, bukti mendukung klaim tentang literasi digital siswa](gambar/peta-bukti.svg)

Setiap analitik harus bisa ditelusuri ke minimal satu dimensi. Data yang tidak nyambung ke dimensi mana pun masuk daftar log yang dihapus.

## 10 analitik versi revisi

Dari 10 analitik, 3 sudah terekam penuh, 5 baru sebagian, dan 2 belum ada sama sekali: pola kesalahan dan skenario digital. Sekarang setiap analitik punya definisi yang bisa dihitung dari log atau dari lembar lapangan.

| No | Analitik | Definisi operasional (revisi) | Makna bagi penelitian | Sumber data | Status di sistem | Yang perlu dilakukan |
| --- | --- | --- | --- | --- | --- | --- |
| 1 | Penyelesaian aktivitas | Tantangan tuntas per wilayah. "Dilewati" dipecah tiga: (a) petunjuk Cari Objek gagal 3 kali, (b) pengerjaan ditinggal dan tidak dilanjutkan di sesi yang sama, (c) tantangan yang sudah terbuka tapi tidak pernah dimulai sampai pengambilan data selesai. | Seberapa banyak dan seberapa tekun siswa menyelesaikan; dasar untuk menentukan data siswa lengkap atau tidak | Log game + L1 | Sebagian | Definisi "dilewati" dan tiga jenisnya ada di [kamus data](11_KAMUS_DATA.md#analitik-1-penyelesaian-aktivitas) |
| 2 | Ketepatan jawaban | Benar atau salah akhir per butir, dikelompokkan per dimensi (D1–D3) dan per level AKM | Indikator yang sudah atau belum dikuasai | Log game | Sebagian | Susun indikator per dimensi, beri label level AKM per butir. Indikator `sikap` jadi skala pendamping lewat Balai Refleksi (diputuskan 25 Oktober 2026) |
| 3 | Ketepatan percobaan pertama | Jawaban di Cek pertama (Isi Rumpang, Benar atau Salah), ketukan pertama per petunjuk (Cari Objek), atau pilihan pertama (Skenario Digital), sebelum ada umpan balik | Ukuran utama kemampuan awal; data inti untuk uji Rasch | Log game | Sudah | Ingat peluang tebakan 50% di Benar atau Salah; butir level tinggi sebaiknya punya 3 pilihan atau lebih |
| 4 | Jumlah percobaan | Percobaan ke berapa butir jadi benar (1, 2, 3), atau "belum berhasil setelah 3" | Butir yang butuh pengulangan | Log game | Sudah | Saat analisis, "belum berhasil" diperlakukan sebagai data tersensor, bukan angka 3 |
| 5 | Waktu pengerjaan | Waktu aktif = waktu total dikurangi (a) selang layar tersembunyi dan (b) bagian jeda tanpa masukan yang melebihi 3 menit (ambang sementara, keputusan 26 Oktober 2026; berupa pengaturan yang mudah diubah dan ditetapkan ulang setelah uji coba). Waktu per butir di tantangan yang dicek bersamaan hanya perkiraan | Tugas yang makan waktu lebih lama | Log game + L3 | Sebagian | Hitung waktu aktif; laporkan waktu total dan waktu aktif berdampingan |
| 6 | Pola kesalahan | Kode kategori kesalahan di setiap percobaan salah, dari skema yang ditetapkan sebelum pengambilan data | Jenis kesulitan: salah memilih informasi atau salah memilih tindakan digital | Log game + pengodean manual | Belum | Beri kode di setiap kata pengecoh, benda pengecoh, pernyataan, dan pilihan skenario |
| 7 | Penggunaan bantuan | Membuka tombol Bantuan, memutar atau mengulang narasi soal, mengulang instruksi; di setiap percobaan dicatat apakah Bantuan sudah dibuka sebelumnya | Dukungan apa saja yang dipakai siswa | Log game + L1 (headset) + L3 (bantuan orang) | Sebagian | Pisahkan rekaman narasi instruksi dengan kode konteks sendiri; bantuan dari teman, guru, atau peneliti hanya tercatat lewat L3 |
| 8 | Navigasi permainan | Urutan layar, membuka Pustaka, menggulir bacaan, keluar dari tantangan. "Kembali ke materi" = membuka Pustaka wilayah atau kembali ke bacaan di dalam tantangan | Alur siswa saat mencari informasi | Log game | Sudah | Sudah berlebih; event yang tidak menjawab pertanyaan penelitian dihapus (lihat *Log: dihapus atau dipisah*) |
| 9 | Perubahan setelah umpan balik | Untuk butir yang salah di percobaan pertama: diperbaiki, tetap salah dengan kesalahan yang sama, atau pindah ke kesalahan lain | Kemampuan memperbaiki jawaban | Log game | Sebagian | Data mentahnya sudah ada; ukuran turunannya dihitung di lapis data turunan |
| 10 | Pilihan dan alasan di skenario digital | Pilihan tindakan pertama dan alasan yang dipilih di skenario data pribadi, kata sandi, izin berbagi, dan pesan orang asing (ditambah kabar bohong dan etika) | Pemahaman keamanan dan etika digital, termasuk "tindakannya benar, alasannya keliru" | Log game + L5 + metrik kata sandi saat daftar | Belum | Bangun tantangan Skenario Digital (bagian berikut) |

Kolom status bisa diubah tim seiring pekerjaan berjalan.

## Skenario Digital: tantangan kelima

Sudah diputuskan: tiap wilayah dapat tantangan kelima, **Skenario Digital** (kode kerja `tmg-5`, `mgl-5`, `wnb-5`). Isinya mengacu ke empat pilar literasi digital dari Komdigi dan Siberkreasi: Cakap, Etis, Aman, dan Budaya Bermedia Digital. Sekarang game baru merekam literasi digital dari pembuatan kata sandi waktu daftar; tantangan literasi digital yang lama sudah dihapus saat perombakan kelas 5.

**Topiknya sama di tiap wilayah, kerumitannya yang naik.** Keempat topik dari analitik 10 muncul di setiap wilayah, jadi topik tidak tercampur dengan tingkat kesulitan. Izin atas karya dan tradisi budaya pindah ke D3 (*Periksa Unggahan*, BUD-IZN), jadi topik izin di D2 cukup foto dan obrolan diri sendiri atau teman ([06](06_KISI_KISI_BANK_SOAL.md#topik-skenario-digital-d2)).

| Topik (pilar) | Temanggung (mudah) | Magelang (sedang) | Wonosobo (sulit) |
| --- | --- | --- | --- |
| Data pribadi (Aman) | Tanda bahayanya jelas | Tanda bahayanya samar | Ada hadiah yang menggoda |
| Kata sandi dan kode OTP (Aman) | Orang asing yang minta | Teman dekat yang minta | Permintaannya kelihatan resmi |
| Izin membagikan foto atau obrolan (Etis) | Foto diri sendiri | Foto teman | Tangkapan layar obrolan teman mau diteruskan ke grup |
| Pesan orang asing dan kabar bohong (Aman, Cakap) | Pesannya jelas mencurigakan | Pesan berantai dari grup | Situasinya berkembang dua langkah |
| Jumlah pilihan tindakan | 3 | 4 | 4 |
| Jumlah pilihan alasan | 3 | 4 | 5 |

**Alur satu skenario:**

1. Situasi pendek berlatar Kedu, bergambar dan bernarasi, dengan sudut pandang "kamu".
2. Langkah 1, pilih tindakan. Satu pilihan aman dan tepat, satu kurang tepat (aman tapi belum lengkap), sisanya berisiko. Setiap pilihan punya kode kesalahan.
3. Langkah 2, pilih alasan. Ditanyakan apa pun tindakan yang dipilih, supaya "tindakannya benar, alasannya keliru" kelihatan.
4. Umpan balik yang ramah tentang akibat pilihannya, tanpa kata "salah" atau "gagal".
5. Yang dianalisis adalah pilihan dan alasan **pertama**. Siswa boleh mencoba lagi untuk belajar dengan aturan yang sama seperti tantangan lain: **tiga kesempatan** (diputuskan 25 Oktober 2026). Setiap kesempatan berisi pilihan tindakan dan alasan. Skenario selesai saat tindakan yang tepat dipilih; pilihan "kurang tepat" mendapat umpan balik dan memakai satu kesempatan. Setelah kesempatan ketiga, tindakan yang aman ditampilkan dan dijelaskan.

**Contoh skenario Wonosobo (pesan orang asing, sulit).** Akun yang tidak kamu kenal mengirim pesan: "Selamat! Kamu menang wisata gratis ke Telaga Warna untuk sekeluarga. Kirim foto kartu pelajar dan nomor HP orang tuamu sekarang, kuotanya tinggal 3!"

| Pilihan tindakan | Kode | Sifat |
| --- | --- | --- |
| Langsung kirim sebelum kuotanya habis | DIG-PRI | berisiko |
| Balas dulu, tanya dia siapa | DIG-ASG | berisiko |
| Jangan dibalas; tunjukkan ke orang tua, lalu blokir dan laporkan akunnya | — | tepat |
| Diamkan saja tanpa cerita ke siapa pun | DIG-PSF | kurang tepat |

| Pilihan alasan | Kode |
| --- | --- |
| Hadiah dari orang yang tidak dikenal bisa jadi tipuan untuk mengambil data | R-RISK |
| Data orang tua bukan milikku untuk dibagikan tanpa izin | R-ETIK |
| Kata guru tidak boleh membalas orang asing | R-ATUR |
| Teman-temanku juga ikut mengirim | R-SOS |
| Aku memang tidak terlalu suka jalan-jalan | R-ACAK |

Latar contoh ini sudah dicek: Telaga Warna memang ada di Wonosobo. Contoh sebelumnya memakai Dieng Culture Festival, padahal festival itu digelar di Banjarnegara, jadi kita ganti (lihat *Cek fakta dan rujukan*). Skenario tidak boleh menakut-nakuti dan tidak memuat tautan sungguhan. Metrik kata sandi saat daftar tetap dipakai sebagai bukti tambahan dimensi D2.

## Skema kategori kesalahan

Setiap pilihan salah diberi satu kode **sebelum** data diambil, lalu kode itu ikut tercatat di setiap percobaan yang salah. Skema ini dibekukan setelah uji coba dan tidak diubah selama pengambilan data utama. Kode INF masuk dimensi D1, kecuali di Cari Objek babak 1 yang menjadi skala pendamping PBK; INF-BUD hanya dipakai di sana. Kode DIG hanya masuk D2, termasuk DIG-IZN dan DIG-ETK. Kode BUD masuk D3. Arti setiap kode ada di [03](03_KODE_DAN_ARTI.md).

**A. Salah memilih informasi (INF)**

| Kode | Kategori | Contoh dalam konteks Kedu |
| --- | --- | --- |
| INF-LIT | Salah membaca informasi tersurat | Memilih "Sindoro" padahal teksnya menyebut "Sumbing" |
| INF-KAT | Terkecoh kesamaan kata | Menilai salah sebuah pernyataan hanya karena ada kata "sama-sama" |
| INF-REL | Benar, tapi tidak menjawab | Ditanya fungsi sebuah tarian, malah memilih "tarian itu berasal dari Wonosobo" |
| INF-HUB | Salah menghubungkan informasi (sebab-akibat, urutan) | Mengisi "karena" di rumpang yang butuh "sehingga" |
| INF-SUM | Salah menilai sumber | Lebih percaya unggahan viral tanpa nama daripada pengumuman resmi tentang Telaga Warna |
| INF-FOP | Mencampur fakta dan pendapat | Menganggap "Dieng tempat paling indah" sebagai fakta |
| INF-BUD | Tertukar benda budaya yang mirip | Mengetuk angklung (Jawa Barat) untuk petunjuk tentang bundengan (Wonosobo) |
| INF-GEN | Menggeneralisasi berlebihan | Menerima pernyataan yang memakai "selalu" atau "semua" |

**B. Salah memilih tindakan digital (DIG)**

| Kode | Kategori | Contoh | Sifat |
| --- | --- | --- | --- |
| DIG-PRI | Membagikan data pribadi berlebihan | Mengunggah foto di Borobudur lengkap dengan nama sekolah dan alamat rumah | berisiko |
| DIG-SAN | Membagikan kata sandi atau kode OTP | Memberi teman kode supaya bisa main GELITA pakai akunmu | berisiko |
| DIG-IZN | Membagikan tanpa izin | Mengunggah video sepupu menari tanpa bertanya dulu | berisiko |
| DIG-ASG | Menanggapi orang asing | Membalas pesan "kirim nomormu untuk tur gratis ke Dieng" | berisiko |
| DIG-HOX | Meneruskan kabar yang belum dicek | Meneruskan "Air Telaga Warna beracun, sebarkan!" | berisiko |
| DIG-ETK | Berkomunikasi tidak etis | Mengejek tradisi ruwatan rambut gimbal Dieng di kolom komentar | berisiko |
| DIG-PSF | Terlalu pasif, tidak melapor | Mendiamkan orang asing tapi tidak cerita ke orang tua atau guru | kurang tepat |

**C. Budaya digital Kedu (BUD)**, untuk D3 di Cari Objek babak 2 *Periksa Unggahan*

| Kode | Kategori |
| --- | --- |
| BUD-ATR | Tidak mencantumkan atau salah menunjuk pembuat dan sumber karya budaya |
| BUD-REP | Menerima atau menyebarkan keterangan budaya yang keliru |
| BUD-IZN | Mengunggah ulang penampilan atau karya budaya tanpa izin |
| BUD-ETK | Menanggapi tradisi dengan cara yang tidak menghormati |

**D. Prosedural (NON):** NON-TEB menebak cepat (menjawab kurang dari 3 detik tanpa membaca; ambang sementara, keputusan 26 Oktober 2026, ditetapkan ulang setelah uji coba), NON-SEN salah ketuk di dekat sasaran, NON-HABIS jawaban terakhir saat kesempatan habis. Kesalahan yang tidak cocok dengan kode mana pun diberi INF-LAIN, DIG-LAIN, atau BUD-LAIN, lalu dikode manual.

**E. Alasan di Skenario Digital (R):** R-RISK paham risikonya, R-ETIK empati atau izin, R-ATUR "karena disuruh atau dilarang", R-SOS tekanan teman atau ingin disukai, R-ACAK alasannya tidak nyambung.

**Cara kode menempel di tiap jenis tantangan:**

- Isi Rumpang: matriks rumpang × kata. Setiap pasangan "kata ini di rumpang itu" punya satu kode, termasuk kata yang sebenarnya benar untuk rumpang lain.
- Benar atau Salah: setiap pernyataan punya satu kode jebakan. Kalau siswa keliru menilai pernyataan itu, kodenya yang dipakai.
- Cari Objek babak 1 (*Temukan Benda*, PBK): setiap benda pengecoh punya satu kode (INF-BUD atau INF-LIT); mengetuk tempat kosong di dekat sasaran = NON-SEN.
- Cari Objek babak 2 (*Periksa Unggahan*, D3): setiap bagian unggahan yang salah untuk sebuah petunjuk punya satu kode BUD; bagian netral (misalnya jumlah suka) = NON-SEN.
- Skenario Digital: setiap pilihan tindakan dan setiap alasan punya satu kode.
- Susun Gambar: tidak dikode, karena dipakai sebagai pemanasan.

**Validasi:** dua pengode bekerja terpisah pada sampel 20% jawaban dan semua transkrip berpikir lantang (L5). Kesepakatan minimal Cohen's κ = 0,70. Kalau beda, didiskusikan dan dicatat. Skema dan rencana analisis didaftarkan lebih dulu (pra-registrasi di OSF) di gerbang G4, setelah uji coba dan sebelum pengambilan data utama (diputuskan 25 Oktober 2026).

**Contoh data mentah** (bentuk ekspor percobaan dengan kolom baru `error_code`, `reason_code`, dan `rapid_guess`, memakai format kode peserta baru). Kode kesalahan dari bank tidak pernah ditimpa; tebakan cepat (NON-TEB) menjadi penanda tersendiri. Semua kolom dan aturannya dibekukan di [kamus data](11_KAMUS_DATA.md#analitik-6-pola-kesalahan).

```csv
participant_code,item_key,try_no,answer,is_correct,error_code,reason_code,rapid_guess,hint_before,last_input_ms
S03-A07,wnb-2-a01,1,karena,0,INF-HUB,,,0,14200
S03-A07,wnb-2-a01,2,sehingga,1,,,,1,9800
S03-A07,wnb-3-a01,1,salah,0,INF-LIT,,0,0,21500
S03-A07,wnb-4-x03,1,tap:angklung,0,INF-BUD,,0,0,6100
S03-A07,wnb-5-a01,1,kirim_data,0,DIG-PRI,R-SOS,0,0,18300
S03-A07,wnb-5-a01,2,lapor_ortu,1,,R-RISK,0,0,7400
S03-A12,wnb-5-a02,1,diamkan,0,DIG-PSF,R-ATUR,0,0,5200
S03-A12,mgl-3-b02,1,benar,0,INF-HUB,,1,0,900
```

Contoh ringkasan untuk laporan penelitian: "Di wilayah Wonosobo, kesalahan terbanyak adalah INF-HUB (34%) dan INF-SUM (22%). Sebanyak 41% siswa memilih tindakan aman di skenario orang asing, tapi dengan alasan R-ATUR." Angka ini cuma ilustrasi.

## Pelevelan wilayah

Urutan Temanggung (mudah), Magelang (sedang), Wonosobo (sulit) tetap dipakai. Bedanya, tingkat kesulitan sekarang dibuktikan dengan data, bukan cuma dengan label Bloom. Untuk dimensi D1, acuan utamanya level kognitif AKM Literasi Membaca dengan padanan proses membaca PIRLS 2026. Untuk D2, tingkatnya dari kerumitan skenario. Bloom tetap ditulis sebagai label kedua.

| Wilayah | Level AKM utama (D1) | Padanan PIRLS 2026 | Bloom (label kedua) | Skenario Digital (D2) |
| --- | --- | --- | --- | --- |
| Temanggung (mudah) | Menemukan informasi | Fokus dan mengambil informasi tersurat | C1–C3 | Tanda bahaya jelas, 3 pilihan |
| Magelang (sedang) | Interpretasi dan integrasi | Membuat inferensi langsung; menafsirkan dan memadukan gagasan | C2–C4 | Tanda bahaya samar, 4 pilihan |
| Wonosobo (sulit) | Evaluasi dan refleksi | Mengevaluasi dan mengkritisi isi teks | C2–C5 | Nilai yang bertentangan, dua langkah |

**Kenapa AKM/PIRLS jadi acuan utama.** AKM adalah kerangka nasional untuk literasi membaca, jadi hasil kita gampang dibaca pemangku kepentingan di Indonesia. Sampai 2025, AKM diikuti sampel siswa kelas 5 lewat Asesmen Nasional. Mulai 2026 aturannya ada di Kepmendikdasmen Nomor 56 Tahun 2026. Beberapa sumber menyebut soal literasi membaca kini masuk ke TKA Bahasa Indonesia di kelas 6, artinya siswa kelas 5 kita akan menghadapinya setahun lagi. Teks resmi keputusan itu belum kita baca langsung, jadi perlu dicek. PIRLS 2026 memberi padanan internasional untuk jurnal. Bloom mengelompokkan tujuan belajar, bukan tingkat kesulitan nyata. Pembanding lain (SOLO, Depth of Knowledge Webb) kurang pas untuk soal pilihan tertutup.

**Lima langkah supaya klaim tingkat kesulitan bisa dipertahankan:**

1. Dua penilai ahli memberi label level AKM pada semua butir yang dinilai (116 butir literasi digital, lihat [06](06_KISI_KISI_BANK_SOAL.md) dan [08](08_DRAF_BANK_SOAL.md)) secara terpisah, dengan kesepakatan minimal κ = 0,70. Label Bloom pengembang sudah diperiksa ulang per butir ([19](19_TELAAH_LABEL_BLOOM.md), 26 Oktober 2026): petunjuk Cari Objek diberi label menurut prosesnya (PBK C1–C2, Periksa Unggahan C2–C4), Benar atau Salah paling tinggi C4 kecuali dua butir yang memeriksa simpulan terhadap bukti (C5), dan rentang Bloom wilayah menjadi C1–C3, C2–C4, dan C2–C5.
2. Tambahkan **butir jangkar**: 6 pernyataan D1 (2 per wilayah) di kartu jangkar yang sama untuk variasi a dan b, supaya kedua variasi tersambung di satu skala. Diputuskan 25 Oktober 2026 menggantikan rencana awal (butir yang diulang di ketiga wilayah), karena siswa yang sama memainkan ketiga wilayah; lihat [06](06_KISI_KISI_BANK_SOAL.md#butir-jangkar-usulan).
3. Catat beban permukaan sebagai variabel sendiri: jumlah rumpang, jumlah kata pengecoh, panjang bacaan (kata), jumlah benda pengecoh, dan ukuran kotak Susun Gambar.
4. Setelah data terkumpul, jalankan model Rasch pada ketepatan percobaan pertama, per dimensi (atau model Rasch multidimensi). Periksa urutan kesulitan butir dibandingkan wilayah dan level AKM, kecocokan butir, dan kesetaraan variasi a dan b (DIF).
5. Urutan wilayah tidak bisa diacak karena mengikuti cerita. Ini kita tulis sebagai keterbatasan, dengan waktu aktif kumulatif dan nomor pertemuan sebagai kovariat.

## Admin dan peneliti: dua peran berbeda

Admin dan peneliti adalah dua entitas yang berbeda. **Admin** menjaga sistem berjalan dan memegang identitas. **Peneliti** memegang data penelitian yang sudah disamarkan (pseudonim). Sekarang sistem baru punya dua peran staf: `admin` yang bisa melihat semuanya, termasuk nama siswa, dan `guru` yang melihat data sekolahnya sendiri tanpa nama. Revisi ini menambah peran `peneliti` dan menghapus peran `guru` seluruhnya (diputuskan 25 Oktober 2026). Peneliti memakai dua peran sistem: `peneliti` untuk kerja lapangan dan `ketua` untuk ketua peneliti, yang juga memegang data semua siswa, unduh data, pengaturan studi, dan penanda tindak lanjut butir risiko daring (keputusan pengembang 30 Oktober 2026; rinciannya per halaman di [27 → Hak akses per peran](27_IMPLEMENTASI_PERAN_PENELITI.md#4-hak-akses-per-peran); tabel ini diselaraskan sesi koordinator, 30 Oktober 2026, `asal: pengembang`).

| Fitur atau data | Admin | Peneliti | Ketua |
| --- | --- | --- | --- |
| Akun staf (buat, nonaktifkan, ganti peran) | Ya | — | — |
| Akun siswa, reset kata sandi, nama dan nama pengguna | Ya | Reset kata sandi lewat kode peserta saja, tanpa melihat nama | Sama dengan peneliti |
| Daftar sekolah, NPSN, dan pemetaan ke kode sekolah | Ya | Kode sekolah saja | Kode sekolah saja |
| Konten, bank soal, media, narasi | Ya | Lihat saja (untuk validasi) | Lihat saja |
| Rilis dan paket aset | Ya | — | — |
| Log teknis: perangkat, paket, event hilang, audit | Ya | Ringkasan event hilang di panel pemantauan | Sama dengan peneliti; riwayat unduhannya sendiri |
| Sesi lapangan, token, isian L2, dan izin berkode dari L7 | — | Ya | Ya |
| Panel pemantauan lapangan | Lihat angka saja, tanpa kode peserta | Ya | Ya |
| Data penelitian satu siswa lewat kodenya | — | Ya | Ya |
| Data penelitian semua siswa (jawaban, percobaan, navigasi, bantuan, waktu) dan unduhannya | — | — | Ya, dengan kode peserta |
| Data turunan, kamus data, dan daftar izin berkode | — | — | Ya |
| Penelitian (studi), statusnya, dan mode penelitian | Lihat | — | Ya |
| Permintaan hapus data peserta | Menjalankan | Mengajukan | Mengajukan, dan mengonfirmasi permintaan peneliti |
| Penanda tindak lanjut butir risiko daring | — | — | Ya, hanya dilihat, tidak pernah diunduh |

**Aturan pemisahan:**

- Peneliti tidak pernah melihat nama atau nama pengguna siswa di sistem. Nama hanya ada di akun (dikelola admin) dan di lembar L7 di kertas, yang dipegang P1 dan ketua peneliti.
- Admin tidak mengunduh data penelitian. Kalau admin butuh memeriksa masalah teknis, cukup log teknis.
- Di antara peneliti, hanya akun `ketua` yang mengunduh data penelitian. Akun `peneliti` (P1–P3) melihat data satu siswa lewat kodenya untuk kerja lapangan ([27](27_IMPLEMENTASI_PERAN_PENELITI.md#9-data-yang-terlihat-per-peran)).
- Satu orang boleh memegang dua peran, tapi lewat dua akun terpisah, supaya jejak audit jelas.
- Peran `guru` dihapus seluruhnya, termasuk halaman dan hak aksesnya (diputuskan 25 Oktober 2026). Kalau nanti laporan untuk guru dibuat, perannya dibangun ulang dengan hak yang baru.
- Nama lengkap siswa tetap disimpan di akun dan hanya terlihat oleh admin (dikukuhkan 25 Oktober 2026).
- Siswa yang lupa kata sandi di lapangan: P1 mencari kode pesertanya di L2, lalu me-reset kata sandi dari panel peneliti lewat kode itu. Sistem memberi kata sandi sementara dan mencatatnya di log audit; peneliti tetap tidak melihat nama.

## Log: dihapus atau dipisah

Log yang tidak menjawab 10 analitik dihapus, bukan cuma disembunyikan. Log yang masih perlu untuk menjaga sistem hanya bisa dilihat admin. Sisanya untuk peneliti. Alasannya minimalisasi data anak, sesuai UU Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi.

Baris `answer_submitted`, `answer_changed`, `audio_pause`, dan `dialogue_advanced`, dan tiga event baru di akhir tabel, diselaraskan dengan pencarian kode di [29 → Katalog event](29_IMPLEMENTASI_DATA_PENELITIAN.md#4-katalog-event) dan keputusan pengembang 30 Oktober 2026 (sesi koordinator, 30 Oktober 2026, `asal: pengembang`). Katalog di 29 adalah satu-satunya daftar jenis event untuk implementasi.

| Log atau data | Isinya | Keputusan | Alasan |
| --- | --- | --- | --- |
| `session_started`, `session_resumed`, `session_paused`, `session_abandoned`, `session_completed` | Awal, jeda, dan akhir sesi bermain | Peneliti | Analitik 1 dan 5 |
| `level_opened`, `level_completed`, `intro_completed` | Wilayah dibuka dan tuntas, cerita pembuka selesai | Peneliti | Analitik 1 |
| `challenge_opened`, `challenge_checked`, `challenge_completed`, `challenge_abandoned`, `challenge_tries_exhausted`, `clue_failed` | Alur satu tantangan | Peneliti | Analitik 1, 3, 4 |
| `answer_submitted`, `wrong_target_clicked` | Jawaban dikirim, ketukan salah di Cari Objek | Peneliti | Untuk urutan kejadian; analitik 2 dan 6 memakai tabel percobaan (`item_tries`) sebagai sumber analisis, jadi duplikasi sudah diputuskan ([29](29_IMPLEMENTASI_DATA_PENELITIAN.md#4-katalog-event)) |
| `answer_selected` | Setiap kali siswa mengganti pilihan sebelum Cek | Dihapus | Tidak dipakai 10 analitik; cukup hitungan perubahan per butir |
| `answer_changed` | Kiriman Cari Objek yang dinilai, saat jawaban berbeda dari kiriman sebelumnya (ditulis server; bukan perubahan sebelum Cek) | Digabung ke `answer_submitted`, dengan `changed` di payload | Kiriman yang dinilai tetap perlu; baris lama diubah di migrasi ([29 → §5.2](29_IMPLEMENTASI_DATA_PENELITIAN.md#52-answer_changed-digabung-ke-answer_submitted)) |
| `hint_opened` dan `audio_play`, `audio_autoplay`, `audio_replay`, `audio_completed` | Bantuan dan narasi | Peneliti | Analitik 7 |
| `audio_pause` | Narasi dijeda | Tidak lagi ditulis sebagai event mentah | Baris `pause` di tabel audio tetap, dengan `listened_ms`, karena hanya baris itu yang membawa lama mendengar narasi yang belum selesai ([29 → §5.4](29_IMPLEMENTASI_DATA_PENELITIAN.md#54-audio_pause-dan-lama-mendengar)) |
| `screen_viewed`, `overlay_opened`, `library_opened`, `library_page_viewed`, `reading_progress` | Layar, Pustaka, dan gulir bacaan | Peneliti | Analitik 8 |
| `page_hidden`, `page_visible` | Layar disembunyikan atau tampil lagi | Peneliti | Analitik 5 (waktu aktif) |
| `dialogue_advanced` | Setiap geser slide cerita | Dihapus, diganti `dialogue_completed` | Per slide tidak dipakai 10 analitik; game butuh tanda "cerita selesai" untuk lencana "Belum didengar" ([29 → §5.3](29_IMPLEMENTASI_DATA_PENELITIAN.md#53-dialogue_advanced-diganti-dialogue_completed)) |
| `locale_changed` | Ganti bahasa | Peneliti | Kovariat bahasa |
| `feedback_submitted` dan isi kritik dan saran | Masukan siswa | Peneliti | Pelengkap kualitatif, bukan 10 analitik |
| Jawaban skala sikap di Balai Refleksi | Skala pendamping sikap menjaga warisan | Peneliti | Skala pendamping, di luar skor literasi digital (keputusan 25 Oktober 2026) |
| `password_changed` | Kata sandi diganti | Admin | Urusan akun |
| `viewport_changed`, `package_changed`, `clock_offset_ms` | Ukuran layar berubah, paket aset berganti, selisih jam perangkat | Admin | Teknis; koreksi jam tetap dipakai server untuk data peneliti |
| `events_dropped` | Event yang hilang di perangkat | Admin | Mutu data; jumlahnya tampil di panel pemantauan peneliti |
| Rincian perangkat: merek, model, user agent, client hints, rasio piksel, titik sentuh, zona waktu, bahasa browser | Catatan perangkat | Dihapus | Tidak dipakai dan menambah risiko identifikasi |
| Nama OS dan browser beserta versinya | Catatan perangkat | Admin | Untuk memeriksa masalah teknis |
| Jenis perangkat dan ukuran layar | Catatan sesi | Peneliti | Kovariat |
| Log audit staf dan riwayat unduhan | Siapa melakukan apa di panel | Admin | Keamanan; peneliti melihat riwayat unduhannya sendiri |
| Nama, nama pengguna, nama sekolah, NPSN, provinsi, kabupaten | Identitas dan pendaftaran | Admin | Identitas; peneliti cukup kode peserta dan kode sekolah |
| Usia, jenis kelamin, kelas, rombel | Data peserta | Peneliti | Kovariat |
| Metrik kata sandi saat daftar | Jumlah syarat terpenuhi dan jumlah penolakan | Peneliti | Bukti dimensi D2 |
| `dialogue_completed` (baru) | Slide terakhir sebuah cerita tercapai, sekali per cerita | Peneliti | Analitik 8; pengganti `dialogue_advanced` |
| `passage_reopened` (baru) | Bacaan dibuka lagi setelah masukan pertama | Peneliti | Analitik 8, kembali ke bahan bagian (b) ([29 → §6.3](29_IMPLEMENTASI_DATA_PENELITIAN.md#63-membuka-lagi-bacaan-passage_reopened)) |
| `empty_tap` (baru) | Ketukan Cari Objek di luar bagian ketuk, dengan koordinatnya; jaraknya dihitung lapis turunan | Peneliti | Analitik 6, NON-SEN ([29 → §6.2](29_IMPLEMENTASI_DATA_PENELITIAN.md#62-ketukan-di-luar-bagian-ketuk-empty_tap)) |

Bintang dan Serpihan Cahaya tetap ada di game sebagai hadiah, tapi tidak dianalisis sebagai prestasi karena dihitung dari jumlah percobaan. Susun Gambar dipakai sebagai pemanasan dan tidak dinilai.

## Olah data: di sistem atau di alat lain?

Saran kita: **dua-duanya, dengan batas yang jelas.** Sistem tetap menghasilkan data mentah, ditambah satu lapis data turunan yang dihitung sistem. Analisis statistiknya dikerjakan peneliti di perangkat lunak statistik dengan skrip yang disimpan.

![Lapis data: data mentah dan turunan di sistem, analisis di luar](gambar/lapis-data.svg)

| Lapis | Dikerjakan di | Isinya | Kenapa di situ |
| --- | --- | --- | --- |
| 1. Data mentah | Sistem GELITA | Setiap ketukan, jawaban, percobaan, dan event, hanya ditambah dan tidak pernah diubah | Sumber kebenaran; bisa ditelusuri ulang kapan saja |
| 2. Data turunan | Sistem GELITA | Kode kesalahan per percobaan, waktu aktif, tiga jenis "dilewati", penanda tersensor, penanda tebakan cepat, label level AKM dan dimensi | Aturannya bergantung pada logika game, yang paling dipahami sistem. Dihitung sekali, sama untuk semua peneliti, bisa diuji otomatis, dan dijelaskan di kamus data |
| 3. Analisis | R, jamovi, Winsteps, atau Python | Rasch, model campuran, κ, grafik, tabel untuk naskah | Reviewer jurnal mengharapkan perangkat lunak statistik standar dan skrip yang bisa diulang. Membangun statistik di dalam game mahal dan rawan salah |

**Lembar lapangan** diketik ke templat yang diunduh per sesi lapangan dari panel peneliti (atau ke situs statis revisi ini), lalu diunggah ke sistem paling lambat 24 jam setelah sesi. Lembar L1–L6 yang diunggah tersimpan berkode dan tanpa nama, dan ikut unduhan `ketua`, lalu digabung dengan data di lapis 3 lewat skrip. Kuncinya kode peserta, jam WIB, dan kode sesi. Kutipan ucapan siswa di L5 dan token di L1 tidak diunggah, dan L7 yang bernama tidak pernah masuk server (keputusan pengembang 30 Oktober 2026, [29 → Templat dan impor lembar lapangan](29_IMPLEMENTASI_DATA_PENELITIAN.md#10-templat-dan-impor-lembar-lapangan-perubahan-no-15); sebelumnya impor ke sistem opsional).

**Yang tetap perlu ada di sistem** adalah panel pemantauan sederhana selama pengambilan data: siapa yang belum selesai, sesi mana yang aktif, dan apakah ada event yang hilang. Panel ini untuk operasional di lapangan, bukan untuk hasil penelitian.

## Token sesi: perlu atau tidak?

**Diputuskan (25 Oktober 2026): dipakai, versi ringan, dan token sekaligus mengatur wilayah yang dibuka di tiap pertemuan.** Bukan sistem ujian seperti ANBK, cukup konsep tokennya. GELITA bukan untuk umum, dan data kita hanya bersih kalau siswa bermain di sesi yang kita amati.

**Yang kita dapat dari token sesi:**

- Siswa hanya bisa bermain di sesi yang diamati peneliti, jadi tidak ada data dari rumah yang dibantu orang tua atau kakak dan tidak tercatat di lembar lapangan.
- Setiap sesi game otomatis tersambung ke sesi lapangan (L1), jadi penggabungan dengan lembar tidak perlu dicocokkan manual.
- Token membawa sekolah dan rombel. Saat siswa mendaftar dengan token, sekolah dan kelasnya terisi sendiri: tidak perlu mengetik NPSN atau memilih sekolah, dan guru kelas tidak perlu membantu mendaftar.
- Token juga mengatur wilayah yang dibuka di pertemuan itu, supaya laju bermain antar kelas setara: P1 pendaftaran dan cerita pembuka, P2 Temanggung, P3 Magelang, P4 Wonosobo, P5 tantangan yang tertinggal dan cerita penutup.

**Cara kerjanya:**

1. Sebelum pertemuan, P1 membuat sesi lapangan di panel peneliti: sekolah, rombel, pertemuan ke berapa, tanggal, dan jam buka–tutup.
2. Sistem membuat token 6 huruf dan angka tanpa tanda yang mudah tertukar (tanpa O, 0, I, 1, L), misalnya `KD7RMA`. P1 menulisnya di papan tulis. Huruf besar dan kecil tidak dibedakan (diselaraskan dengan [28 → Token](28_IMPLEMENTASI_SESI_LAPANGAN.md#63-token), sesi koordinator 30 Oktober 2026).
3. Siswa masuk atau daftar, lalu mengetik token sekali per sesi.
4. Token hanya berlaku di jam sesi itu. P1 bisa menutupnya lebih awal atau membuat token baru kalau token bocor.
5. Di luar sesi, seluruh game tertutup sampai penelitian selesai, termasuk Pustaka dan Profil. Game menampilkan pesan ramah: "Permainan dibuka waktu sesi bersama kakak peneliti, ya." Setelah status studi ditutup, game terbuka seperti biasa.

**Bedanya dengan ANBK:** tidak ada aplikasi ujian khusus, tidak ada proktor, dan token tidak berganti tiap 15 menit. Itu wajar untuk ujian berisiko tinggi, tapi berlebihan untuk penelitian kita.

## Kode peserta baru

Usulan kode peserta yang memuat sekolah dan nomor urut bisa dipakai, dengan satu tambahan: huruf rombel. Satu sekolah bisa punya dua kelas 5, masing-masing sampai 50 siswa, jadi nomor urut per sekolah saja bisa lewat dari 99.

**Format: `S03-A07`** (disetujui pengembang, 25 Oktober 2026)

| Bagian | Arti | Contoh |
| --- | --- | --- |
| `S03` | Kode sekolah penelitian, dari daftar yang dibuat peneliti (bukan NPSN) | Sekolah ke-3 dalam daftar |
| `A` | Rombel kelas 5 di sekolah itu | 5A |
| `07` | Nomor urut siswa di rombel itu, dua angka (01–99) | Siswa ke-7 yang mendaftar |

**Aturannya:**

- Kode sekolah dibuat peneliti, bukan NPSN, supaya kode tetap samaran (pseudonim) dan pendek. Pemetaan NPSN ke kode sekolah hanya dilihat admin dan disimpan juga bersama L7.
- Dua angka di setiap bagian sudah cukup (99 sekolah, 99 siswa per rombel) dan urutannya tetap rapi kalau disortir.
- Sekolah yang cuma punya satu kelas 5 memakai `A`.
- Sekolah uji coba memakai awalan `U`, misalnya `U01-A03`, supaya datanya tidak tercampur dengan data utama.
- Awalan `GLT-` dihapus karena tidak menambah informasi.
- Kode dibuat sistem saat siswa mendaftar. Sekolah dan rombel diambil dari token sesi, nomor urut dari urutan daftar. Setelah ada data, kode tidak pernah diubah.
- Rombel juga berguna untuk analisis, karena kelas adalah satu tingkat dalam model campuran.

## Kerja lapangan: cara kerjanya

Log game hanya melihat apa yang terjadi di layar. Lembar lapangan mengisi yang tidak kelihatan: siapa yang dibantu, perangkat yang dipakai bareng, kondisi kelas, emosi siswa, dan alasan di balik jawaban. Semua lembar diisi **peneliti**, bukan guru. Setiap lembar memakai dua kunci yang sama dengan log: **kode peserta** dan **jam WIB**. Isi lengkap tiap lembar ada di [02_LEMBAR_LAPANGAN.md](02_LEMBAR_LAPANGAN.md).

**Peran untuk satu kelas (sekitar 28 siswa):**

| Peran | Tugas selama sesi | Lembar |
| --- | --- | --- |
| P1 Koordinator sesi | Membuka sesi, memberi arahan, membantu masalah teknis saja (tidak pernah isi soal), mencatat kondisi sesi | L0, L1, L2, L7 |
| P2 Pengamat keterlibatan | Mengamati siswa bergiliran dengan urutan tetap | L4 |
| P3 Pengamat bantuan | Mencatat setiap bantuan dan gangguan; setelah sesi mewawancarai beberapa siswa | L3, L5 |
| Guru kelas | Menyiapkan perangkat dan internet; diminta tidak membantu isi soal | — |

Pengambilan data utama memakai **tiga peneliti per kelas** (diputuskan 25 Oktober 2026). Kalau pada hari itu satu peneliti berhalangan, P1 juga mengisi L3, wawancara L5 dipindah ke pertemuan lain, dan penyimpangannya dicatat di L1.

**Alur satu pertemuan:**

1. **Sebelum pertemuan.** Izin orang tua (L7) dikumpulkan sebelum P1: amplop tertutup dibagikan H-14 sampai H-10, pengingat H-5, batas kembali H-3; formulir yang tidak kembali sampai batas itu berarti tidak ada izin ([22 → Rekrutmen](22_PERMOHONAN_IZIN_ETIK.md#rekrutmen), keputusan pengembang 27 Oktober 2026). Siswa tanpa izin tidak dibuatkan akun GELITA dan ikut kegiatan pengganti di ruang yang sama, jadi tidak ada data tentang mereka (diputuskan 26 Oktober 2026). Di H-1: tempel stiker nomor di setiap perangkat, cetak lembar, dan jalankan daftar periksa L0. Buat sesi lapangannya di panel peneliti, termasuk wilayah yang dibuka.
2. **Sebelum siswa masuk.** Samakan jam: semua pengamat memakai jam ponsel yang diatur otomatis dari jaringan (WIB), dicatat sampai detik. Uji internet dan isi bagian atas L1.
3. **Pembukaan (5 menit).** P1 memberi arahan ke siswa dan guru, lalu menulis token di papan tulis.
4. **Masuk atau daftar.** Setelah siswa masuk, P1 menyalin kode peserta ("kode penelitian", misalnya S03-A07) ke L2, di samping nomor kursi, baris, kursi di baris, dan nomor perangkat. Di P1 kode disalin dari bagian atas layar modul tes, di pertemuan lain dari halaman Profil. Di P1, langsung setelah semua siswa mendaftar, P1 memasukkan kode peserta, baris, dan kursi ke panel peneliti (± 5 menit), dan tes awal baru dimulai setelah semua siswa punya bentuk tes (diputuskan 26 Oktober 2026). Buklet dan bentuk tes dihitung panel dari baris dan kursi, lalu disalin ke L2 (keputusan pengembang 30 Oktober 2026, [28 → Isian L2](28_IMPLEMENTASI_SESI_LAPANGAN.md#11-isian-l2-kursi-buklet-dan-bentuk-tes)).
5. **Bermain.** P3 mengisi L3 terus-menerus. P2 mengisi L4 dalam putaran tetap. P1 mencatat gangguan besar dan penyimpangan di L1.
6. **Penutupan.** P1 menutup L1 (jam selesai dan catatan) dan menutup token. Di P2–P4, P3 mewawancarai tiga siswa dengan L5, satu dari setiap kelompok nilai tes awal; setiap siswa paling banyak sekali (diputuskan 26 Oktober 2026).
7. **Paling lambat 24 jam setelah sesi.** Lembar dipindah ke templat spreadsheet atau ke situs statis revisi ini. 10% baris diketik ulang orang lain untuk mengecek salah ketik. Tim berdiskusi singkat soal penyimpangan hari itu.

**Cara menyatukan lembar dengan log:**

- L2 menghubungkan kursi dan perangkat dengan kode peserta. Perangkat yang dipakai bareng diberi tanda; datanya dianalisis terpisah atau dikeluarkan.
- Kejadian di L3 menandai percobaan siswa itu dari 1 menit sebelum sampai 2 menit sesudah jam kejadian sebagai "dibantu". Analisis utama dijalankan dengan dan tanpa percobaan yang dibantu.
- Pengamatan L4 dipasangkan dengan aktivitas log terdekat dengan kode dan jam yang sama, misalnya kebingungan di tengah Isi Rumpang.
- L1 jadi kovariat tingkat sesi: headset, kualitas internet, gangguan.
- L7 menentukan siapa yang boleh dianalisis (`consent_ok`). Siswa tanpa izin tidak punya akun; data siswa yang kemudian mengundurkan diri dihapus sesuai protokol.

**Siswa yang absen** di satu pertemuan ikut sesi susulan dalam tiga hari. P1 membuka sesi lapangan susulan dengan token sendiri dan kode berakhiran `-S` (misalnya `S03-A-P3-S`), diamati sedikitnya satu peneliti yang mengisi L1–L3. Sisa tantangan tetap dilanjutkan di susulan P5 (diputuskan 26 Oktober 2026).
- Nama siswa hanya ada di L7. L7 disimpan terpisah dan terkunci; lembar lain cuma memakai kode peserta dan nomor kursi.

## Rencana analisis singkat

Data inti analisis adalah ketepatan percobaan pertama. Lembar lapangan dipakai sebagai penanda dan kovariat. Setiap analisis utama dijalankan dua kali: dengan dan tanpa percobaan yang dibantu (L3) atau perangkat yang dipakai bareng (L2).

| Pertanyaan | Data | Metode |
| --- | --- | --- |
| Apakah urutan kesulitan wilayah terbukti? | Percobaan pertama, butir jangkar, label AKM | Model Rasch per dimensi, peta Wright, kecocokan butir (infit dan outfit 0,5–1,5), DIF antarvariasi |
| Apakah tiga dimensi literasi digital terbukti terpisah? | Percobaan pertama per dimensi | Perbandingan model Rasch satu dimensi dan multidimensi |
| Butir mana yang butuh pengulangan? | Jumlah percobaan, tersensor di 3 | Model ordinal atau model kejadian waktu-diskret dengan sensor |
| Kesalahan apa yang paling sering, dan di mana? | Kode kesalahan per percobaan | Frekuensi per wilayah, dimensi, dan level AKM; uji chi-kuadrat atau model multinomial |
| Apakah siswa memperbaiki jawaban setelah umpan balik? | Percobaan 1 ke percobaan 2 | Matriks perpindahan: diperbaiki, kesalahan sama, kesalahan lain |
| Tugas mana yang lama? | Waktu aktif | Median dan rentang antarkuartil; model campuran dengan efek acak siswa dan rombel |
| Apakah bantuan menolong? | Bantuan dibuka sebelum percobaan | Regresi logistik campuran |
| Bagaimana pemahaman keamanan dan etika digital siswa? | Pilihan dan alasan skenario, angket | Tabulasi silang tindakan × alasan; proporsi "tindakannya benar, alasannya keliru" |
| Apakah skor game sejalan dengan kemampuan nyata? | Kemampuan Rasch game, tes awal dan akhir per bagian ([13](13_TES_AWAL_AKHIR.md#analisis)) | Pola korelasi tes awal per bagian dengan kemampuan game per dimensi; perubahan tes awal–akhir per bagian dengan ukuran efek, dilaporkan sebagai perubahan eksploratif, bukan pengaruh game (26 Oktober 2026) |
| Apakah data pengamatan bisa dipercaya? | L4, pengodean kesalahan | Cohen's κ; reliabilitas orang di model Rasch |

## Peta jalan dan daftar kerja

Pekerjaan berjalan dalam enam tahap. Tahap berikutnya baru dimulai setelah gerbangnya terpenuhi. Sistem baru diubah di tahap 3, setelah instrumen lolos validasi ahli dan izin etik. Urutan ini dikukuhkan pengembang pada 25 Oktober 2026: dokumen implementasi dan perubahan sistem tidak dimulai sebelum gerbang G2, termasuk persetujuan etik. Pengecualian sejak 30 Oktober 2026: dokumen implementasi untuk perubahan sistem yang tidak bergantung pada hasil validasi (no. 7, 10–15, 23, dan 24) boleh didraf sekarang dalam empat bagian ([00 → Tahap dokumen 3](00_INDEKS.md), [04](04_CATATAN_KEPUTUSAN.md)); sistemnya tetap baru diubah di tahap 3, setelah G2. Izin etik diajukan minggu kedua–keempat November 2026 dan persetujuannya ditargetkan paling lambat akhir September sampai minggu pertama November 2027, jadi tahap 3 bisa baru dimulai sekitar waktu itu. Pengembang memilih tetap menunggu persetujuan (25 Oktober 2026). Uji keterbacaan dengan anak di tahap 2, uji coba, dan pengambilan data juga baru boleh berjalan setelah persetujuan terbit.

![Peta jalan: enam tahap, lima gerbang](gambar/peta-jalan.svg)

**Diganti 1 November 2026 (`asal: peneliti`):** model penelitian dan pengembangan menjadi ADDIE (Branch, 2009). Uji kelompok kecil (8–12 anak, setelah berpikir lantang) dan uji kelompok menengah (± 80 siswa) ada di fase *Develop*, di luar data utama; uji besar adalah pengambilan data utama pada versi yang dikunci di G4 ([04](04_CATATAN_KEPUTUSAN.md), 1 November 2026). Bagian dan tabel di bawah masih memuat model Plomp dan diperbarui di sesi berikutnya, bersama paket izin etik.

**Model penelitian dan fasenya** (diputuskan 26 Oktober 2026, rinciannya di [dokumen pendukung peneliti](dokumen-pendukung-peneliti/README.md) dan [04](04_CATATAN_KEPUTUSAN.md)). Penelitian ini penelitian desain pendidikan model Plomp (2013), jenis studi pengembangan, dengan tiga fase:

| Fase Plomp | Tahap GELITA | Prototipe | Isi |
| --- | --- | --- | --- |
| Penelitian pendahuluan | sebelum tahap 1 | — (usulan desain) | Analisis kebutuhan dan konteks, kajian pustaka, kerangka konseptual: konstruk dan peta bukti di dokumen ini, telaah butir lama ([05](05_TELAAH_BUTIR_DAN_DIMENSI.md)), telaah media pendahuluan ([10](10_TELAAH_PENDAHULUAN_MEDIA.md)), game kelas 5 yang ada sekarang, dan data konteks sekolah sasaran dari lembar L8 Profil sekolah ([02](02_LEMBAR_LAPANGAN.md#l8-profil-sekolah)) |
| Prototipe | 1–2 (G1, G2) | **Prototipe I**: bank, instrumen, dan mock-up berbasis kertas | Telaah pendahuluan, validasi ahli, uji keterbacaan |
| Prototipe | 3 (G3) | **Prototipe II**: sistem di instalasi uji | Ahli media langkah 2, uji delapan perangkat, tes otomatis |
| Prototipe | 4 (G4) | **Prototipe III**: lengkap | Berpikir lantang, uji coba satu kelas |
| Penilaian | 5–6 | Produk final dan prinsip desain | Pengambilan data utama, analisis, prinsip desain |

Setiap entri di 04 yang merevisi produk menyebut prototipe yang direvisinya (misalnya "Prototipe I, revisi dari validasi ahli"), supaya iterasinya bisa ditelusuri ke buktinya.

Tanggal tiap tahap belum ditetapkan dan perlu diisi tim. Daftar kerja di bawah memuat pekerjaan tahap 1 sampai 4, kecuali perubahan sistem (lihat bagian terakhir).

Gerbang G1 berarti konstruk disetujui dan semua dokumen tahap 1 selesai dan disetujui pengembang. Kesepakatan dua penilai pada label (κ ≥ 0,70) diperiksa sekali saja, pada pelabelan buta dua ahli di tahap 2, dan menjadi bagian gerbang G2 (diputuskan 25 Oktober 2026).

Ahli media menilai dalam dua langkah (diputuskan 25 Oktober 2026): di tahap 2, game yang ada sekarang ditambah rancangan layar baru (Skenario Digital, *Periksa Unggahan*, token) berupa papan cerita atau mock-up; setelah tahap 3, telaah singkat layar yang sudah jadi di instalasi uji sebelum uji coba, sebagai bagian gerbang G3.

| No | Pekerjaan | Tahap | Penanggung jawab | Status |
| --- | --- | --- | --- | --- |
| 1 | Setujui penyelarasan konstruk: literasi digital dengan tiga dimensi | 1 | Pengembang | Selesai |
| 2 | Bekukan peta bukti dan definisi 10 analitik di kamus data ([11](11_KAMUS_DATA.md)) | 1 | AI (draf), pengembang | Selesai: `kamus-1.0` disetujui 26 Oktober 2026 |
| 3 | Pelabelan buta level AKM dan dimensi pada semua butir yang dinilai oleh dua ahli (B.5a di [07](07_TELAAH_INSTRUMEN_VALIDASI.md)); label pengembang sudah ada di 08 | 2 | AI (berkas), Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1 | Penilai ditetapkan pengembang (27 Oktober 2026): Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1; keduanya menerima paket B.5a saja lebih dulu. Urutan kirim di [20](20_PAKET_VALIDASI.md), daftar buta ber-ID netral dan lembar kerja di [21](21_BERKAS_BUTIR_VALIDATOR.md) (draf) |
| 4 | Periksa ulang label Bloom, terutama Cari Objek dan Benar atau Salah C5 | 1 | AI (telaah), pengembang | Selesai: [19](19_TELAAH_LABEL_BLOOM.md); keputusan pengembang 26 Oktober 2026 dimasukkan ke 08 dan 06 |
| 5 | Beri kode kesalahan pada kata pengecoh, benda pengecoh, dan pernyataan | 1 | AI | Selesai di draf 08, menunggu validasi |
| 6 | Tulis Skenario Digital untuk tiga wilayah, lengkap dengan pilihan tindakan dan alasan berkode | 1 | AI | Selesai di draf 08, menunggu validasi |
| 7 | Tulis cerita penghubung dan narasi untuk tantangan kelima | 1 | AI (draf), pengembang (persetujuan) | Selesai: [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) disetujui 26 Oktober 2026 |
| 8 | Tentukan 4–6 butir jangkar | 1 | AI | Selesai di draf 08 (6 butir), menunggu validasi |
| 9 | Susun tes literasi membaca dan tes literasi digital, bentuk A dan B: bagian membaca diadaptasi dari contoh soal AKM/ANBK yang terbuka untuk umum, bagian D2 dan D3 disusun sendiri dan tidak sama dengan butir game | 1 | AI (draf), pengembang | Draf di [13](13_TES_AWAL_AKHIR.md), [14](14_TES_BAGIAN_MEMBACA.md), dan [15](15_TES_BAGIAN_DIGITAL.md); keputusan pengembang 26 Oktober 2026 (modul tes GELITA, batas waktu longgar, topik di luar Kedu); menunggu validasi |
| 10 | Susun angket akses digital | 1 | AI (draf), pengembang | Draf di [16](16_ANGKET_AKSES_DIGITAL.md); keputusan pengembang 26 Oktober 2026 (di modul, tidak dibacakan; butir risiko daring di P5); menunggu validasi |
| 11 | Finalkan lembar L0–L7 | 1 | AI (draf), pengembang | Selesai: [02](02_LEMBAR_LAPANGAN.md) disetujui 26 Oktober 2026 |
| 12 | Buat daftar kode sekolah penelitian (S01, S02, …) | 1 | Ketua peneliti; admin memasukkan kode ke sistem | Ditunda (27 Oktober 2026): belum diputuskan, tahap 2 tetap dimulai. Dibutuhkan sebelum sekolah direkrut dan L8 diisi. Penanggung jawab disamakan dengan [Kode peserta baru](#kode-peserta-baru) (31 Oktober 2026): ketua peneliti memberi kode kepada sekolah yang direkrut dan menyimpan pemetaan NPSN bersama L7 di luar repo |
| 13 | Tentukan siapa pemegang akun admin dan akun peneliti | 1 | Pengembang (`admin`); ketua peneliti (akun peneliti) | Sementara (27 Oktober 2026): nama samaran per peran, `admin`, `koordinator` (P1), `pengamat` (P2), `pewawancara` (P3), dan `ketua` (ketua peneliti) ([03](03_KODE_DAN_ARTI.md#akun-staf)); pemegang sebenarnya belum ditetapkan. Dibagi 31 Oktober 2026: pengembang menetapkan pemegang `admin`, ketua peneliti menetapkan pemegang `koordinator`, `pengamat`, `pewawancara`, dan `ketua`; semua nama dicatat ketua di luar repo |
| 14 | Validasi ahli: budaya Kedu, bahasa anak, literasi digital, pengukuran, media | 2 | AI (paket), pengembang, sebelas validator | Paket di [20](20_PAKET_VALIDASI.md) dan berkas per butir di [21](21_BERKAS_BUTIR_VALIDATOR.md) (draf; keputusan pengembang 27 Oktober 2026: dikirim digital, honorarium "sesuai ketentuan yang berlaku", ahli media langkah 1 tidak menunggu L8, ID netral untuk B.5a); dikirim setelah instrumen v2.1 dan v1.1 disetujui |
| 15 | Ajukan izin etik ke *Lembaga Komite Etik* (nama samaran) pada minggu kedua–keempat November 2026, dan siapkan surat izin orang tua | 2 | AI (draf), pengembang | Draf protokol di [22](22_PERMOHONAN_IZIN_ETIK.md) dan surat di [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md) (27 Oktober 2026; keputusan pengembang diambil hari itu). Diajukan setelah dokumen tahap 1 yang dilampirkan disetujui. Komite dan formulir dipilih pengembang 27 Oktober 2026: Komisi Etik Bidang Sosial Humaniora BRIN dengan formulir BRIN ([22 → Pengajuan lewat sistem klirens etik BRIN](22_PERMOHONAN_IZIN_ETIK.md#pengajuan-lewat-sistem-klirens-etik-brin)); nomor pasal PP No. 33 Tahun 2026 dicek ke salinan naskahnya; tempat kosong yang tersisa ada di [22 → Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan). Penilaian dampak lengkap (lampiran 18) ada di [26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md) (draf 27 Oktober 2026, `disetujui pengembang` 31 Oktober 2026). Panduan klirens etik BRIN menjadi pedoman utama, PSEPPKN 2021 cadangan (27 Oktober 2026). Patokan telaah BRIN ± 1–2 bulan sejak berkas lengkap, jauh lebih cepat daripada target persetujuan di awal bagian ini (akhir September sampai minggu pertama November 2027); target itu ditinjau pengembang setelah komite dipilih, dan persetujuan yang lebih awal tidak mengubah urutan gerbang |
| 16 | Latih pengamat L4 sampai κ ≥ 0,60 | 4 | | Belum mulai |
| 17 | Uji coba satu kelas dengan semua lembar | 4 | | Belum mulai. Diganti 1 November 2026 (`asal: peneliti`): uji kelompok kecil 8–12 anak, lalu uji kelompok menengah ± 80 siswa ([04](04_CATATAN_KEPUTUSAN.md)); baris ini diperbarui bersama tabel peta jalan |
| 18 | Pra-registrasi skema kesalahan dan rencana analisis di OSF, di gerbang G4 | 4 | | Belum mulai |
| 19 | Susun skala pendamping sikap menjaga warisan untuk Balai Refleksi, lalu masukkan ke validasi ahli (no. 14) | 1 | AI (draf), pengembang | Draf di [17](17_SKALA_SIKAP.md); keputusan pengembang 26 Oktober 2026 (12 butir empat pilihan tanpa titik tengah, 4 butir dibalik, hanya di layar, sebelum kritik dan saran); menunggu validasi |
| 20 | Buat papan cerita atau mock-up layar baru (Skenario Digital, *Periksa Unggahan*, token, modul tes dan angket) untuk ahli media langkah 1 | 1 | AI (draf), pengembang | Selesai: [18](18_MOCKUP_LAYAR_BARU.md) disetujui 26 Oktober 2026 (27 gambar, termasuk layar skala sikap dari [17](17_SKALA_SIKAP.md)) |
| 21 | Telaah ahli media langkah 2 pada layar jadi di instalasi uji, sebelum uji coba (G3) | 3 | | Belum mulai |
| 22 | Tulis penelitian pendahuluan sebagai fase tersendiri dari bahan yang ada, dan isi L8 Profil sekolah untuk setiap sekolah sasaran (draf lembar di [02](02_LEMBAR_LAPANGAN.md#l8-profil-sekolah)) | pendahuluan | AI (draf), pengembang; L8 diisi P1 | Lembar L8 draf (26 Oktober 2026); menunggu daftar kode sekolah (no. 12) |
| 23 | Susun protokol uji keterbacaan dengan anak kelas 5 (bank, tes, angket, skala sikap, salinan layar baru), dijalankan setelah izin etik terbit dan revisi validasi ahli | 2 | AI (draf), pengembang | Draf di [24](24_UJI_KETERBACAAN.md) (27 Oktober 2026); keputusan pengembang: empat anak ditambah satu di putaran kedua, inti untuk semua dan bank dalam enam blok, sekolah di wilayah Kedu di luar sampel dan uji coba, butir risiko daring diparafrasekan saja |

## Pertanyaan yang masih terbuka

Pertanyaan ini perlu dijawab sebelum desain instrumen dibekukan. Centang kalau tim sudah memutuskan.

- [x] Skenario Digital jadi tantangan kelima atau menggantikan Susun Gambar? **Diputuskan: tantangan kelima.**
- [x] Admin dan peneliti satu peran atau dipisah? **Diputuskan: dua peran berbeda.**
- [x] Setuju literasi digital jadi konstruk utama dengan tiga dimensi? **Diputuskan (25 Oktober 2026): ya, model multidimensi *between-item*, D1 bertumpu pada literasi membaca dengan sub-aspek D1a dan D1b. D3 menjadi *Partisipasi dan Representasi Budaya Kedu di Ruang Digital* di Cari Objek babak 2 (lihat [05](05_TELAAH_BUTIR_DAN_DIMENSI.md) dan [06](06_KISI_KISI_BANK_SOAL.md)).**
- [x] Berapa variasi per tantangan? **Diputuskan (25 Oktober 2026): dua (a/b).**
- [x] Ahli media ikut memvalidasi (daftar kerja no. 14)? **Diputuskan (25 Oktober 2026): ya.**
- [x] Peran `guru` dinonaktifkan selama penelitian, atau dihapus sama sekali? **Diputuskan (25 Oktober 2026): dihapus seluruhnya.**
- [x] Skenario Digital memakai aturan 3 kesempatan, atau satu pilihan lalu umpan balik? **Diputuskan (25 Oktober 2026): tiga kesempatan; yang dinilai tindakan dan alasan pertama.**
- [x] Token sesi dipakai? **Diputuskan (25 Oktober 2026): ya, versi ringan, dan token mengatur wilayah yang dibuka di tiap pertemuan.**
- [x] Di luar sesi, apakah Pustaka dan Profil tetap boleh dibuka, atau semuanya tertutup? **Diputuskan (25 Oktober 2026): semuanya tertutup sampai penelitian selesai.**
- [x] Format kode peserta `S03-A07` disetujui? **Diputuskan (25 Oktober 2026): ya.**
- [x] Berapa pertemuan per kelas, dan tantangan mana yang dibuka di tiap pertemuan? **Diputuskan (25 Oktober 2026): lima pertemuan; satu wilayah per pertemuan (lihat [06](06_KISI_KISI_BANK_SOAL.md#waktu-bermain)).**
- [x] Siapa yang menulis skenario dan cerita penghubungnya (Mbah Kedu dan Jaka), dan siapa yang mengisi suara narasinya? **Diputuskan (25 Oktober 2026): skenario sudah ada di draf 08; cerita penghubung dan narasinya didraf AI dan disetujui pengembang; suaranya direkam pengisi suara yang sama dengan narasi lain, di sesi rekaman yang sama.**
- [ ] Siapa validator ahlinya: isi budaya Kedu, bahasa anak, literasi digital, pengukuran, dan media? (Panel ahli materi lima orang, lihat [07](07_TELAAH_INSTRUMEN_VALIDASI.md).) **Sementara (25 Oktober 2026): tujuh orang dengan nama samaran sampai pengembang menetapkan orangnya: Ahli Bahasa Anak 1 dan 2, Ahli Literasi Digital 1 dan 2, dan Ahli Pengukuran (panel ahli materi lima orang), Ahli Budaya Kedu (pengamat budaya), dan Ahli Media Pembelajaran.** **Diputuskan (26 Oktober 2026): panel pengamat budaya dan panel ahli media masing-masing tiga orang (Ahli Budaya Kedu 1–3, Ahli Media Pembelajaran 1–3), jadi validatornya sebelas orang. Nama aslinya belum ditetapkan.** Templat daftar padanan (di luar repo) dan pesan kesediaan yang dikirim ketua peneliti ada di [20 → Prasyarat no. 9](20_PAKET_VALIDASI.md#prasyarat-no-9-nama-asli-dan-kesediaan-validator) (27 Oktober 2026); di repo hanya dicatat "selesai" dan tanggalnya.
- [x] Tes awal dan akhir disusun sendiri atau mengadaptasi contoh soal AKM/TKA yang terbuka untuk umum? **Diputuskan (25 Oktober 2026): bagian literasi membaca diadaptasi dari contoh soal AKM/ANBK yang terbuka; bagian D2 dan D3 disusun sendiri, sejajar dengan kisi-kisi tapi tidak sama dengan butir game; divalidasi bersama bank di tahap 2.**
- [x] Berapa lama tes awal–akhir, dan di media apa? **Diputuskan (26 Oktober 2026): lama P1 dan P5 mengikuti kebutuhan, tidak terikat JP sekolah, karena izin penelitian mencakup seluruh JP sampai setelah jam sekolah. Setiap bagian tes diberi batas longgar tanpa jam di layar (Bagian 1 ± 40 menit, Bagian 2 dan 3 ± 35 menit). Tes dikerjakan di modul tes GELITA dengan tampilan Bootstrap yang tidak seperti game (perubahan sistem no. 22). Lihat [13](13_TES_AWAL_AKHIR.md#keputusan-pengembang).**
- [x] Topik teks dan adegan tes? **Diputuskan (26 Oktober 2026): budaya Nusantara di luar Kedu.**
- [x] Angket akses digital: media, dan perlu butir pengalaman risiko daring? **Diputuskan (26 Oktober 2026): angket di modul yang sama, langsung setelah tes awal, hanya di layar dan tidak dibacakan; 2–3 butir risiko daring diisi sendiri di layar di P5 setelah tes akhir, dengan prosedur rujukan dan disebut di izin etik ([16](16_ANGKET_AKSES_DIGITAL.md)).**
- [x] Kapan variabel tes dan angket masuk kamus data? **Diputuskan (26 Oktober 2026): di `kamus-1.1`, setelah uji coba; `kamus-1.0` tidak diubah.**
- [x] Pertemuan wilayah P2–P4 tetap 2 JP? **Diputuskan (26 Oktober 2026): tidak; waktu bermain ± 90 menit per pertemuan, sisa tantangan tetap dilanjutkan di susulan P5 (paling lama 20 menit).**
- [x] Izin orang tua untuk butir risiko daring? **Diputuskan (26 Oktober 2026): centang terpisah di surat izin orang tua dan L7.**
- [x] Berapa sekolah, kelas, dan siswa dalam sampel? **Diputuskan (25 Oktober 2026): target 300 siswa yang datanya bisa dianalisis, merekrut sekitar 360 (± 13 kelas). Jumlah sekolah mengikuti ketersediaan kelas.** **Diperbarui (1 November 2026, `asal: peneliti`): target tetap 300 siswa yang datanya bisa dianalisis, boleh lebih atau kurang, dari sekitar enam sekolah utama di wilayah Kedu (boleh lebih); rekrut ± 360 tetap perkiraan ([04](04_CATATAN_KEPUTUSAN.md)).**
- [x] Berapa peneliti per kelas: tiga (ideal) atau dua? **Diputuskan (25 Oktober 2026): tiga (P1, P2, P3).**
- [x] Izin etik diajukan ke komisi etik mana, dan kapan? **Sementara (25 Oktober 2026): nama samaran *Lembaga Komite Etik*; diajukan minggu kedua sampai keempat November 2026; persetujuan diperoleh sebelum kegiatan yang mensyaratkannya dimulai, paling lambat antara akhir September dan minggu pertama November 2027. Nama komite sebenarnya belum ditetapkan.** **Draf permohonan di [22](22_PERMOHONAN_IZIN_ETIK.md) (27 Oktober 2026): satu permohonan untuk semua kegiatan dengan anak (KG1–KG5).** **Diputuskan (27 Oktober 2026): Komisi Etik Bidang Sosial Humaniora BRIN dengan formulir BRIN ([22 → Keputusan pengembang](22_PERMOHONAN_IZIN_ETIK.md#keputusan-pengembang) no. 22); diajukan minggu kedua–keempat November 2026.** Target persetujuan masih ditinjau pengembang (daftar kerja no. 15).
- [x] Skema kesalahan dan rencana analisis dipra-registrasi (misalnya di OSF) atau tidak? **Diputuskan (25 Oktober 2026): ya, di OSF pada gerbang G4.**
- [x] Indikator `sikap` dipakai (misalnya lewat Balai Refleksi) atau dihapus? **Diputuskan (25 Oktober 2026): dipakai sebagai skala pendamping sikap lewat Balai Refleksi, di luar skor literasi digital; instrumennya disusun di tahap 1 dan divalidasi di tahap 2.**
- [x] Uji beda tes awal–akhir: temuan utama atau eksploratif? **Diputuskan (26 Oktober 2026): tes awal–akhir terutama bukti validitas kriteria; uji bedanya dengan ukuran efek dilaporkan sebagai perubahan eksploratif ([13](13_TES_AWAL_AKHIR.md#analisis)).**
- [x] Kepraktisan diukur dari mana, karena guru tidak ikut? **Diputuskan (26 Oktober 2026): log, L1, L3, L4, dan bintang 1–5 di kritik dan saran (`feedback_rating`) cukup untuk sekarang. Perlu tidaknya pertanyaan kepraktisan tambahan untuk siswa diputuskan setelah uji coba tahap 4, dengan data berpikir lantang (L5) sebagai bahan kualitatif.**
- [x] Penelitian pendahuluan ditulis sebagai fase tersendiri? **Diputuskan (26 Oktober 2026): ya, ditambah lembar L8 Profil sekolah untuk data konteks sekolah sasaran (daftar kerja no. 22).**
- [x] Prototipe diberi nomor? **Diputuskan (26 Oktober 2026): ya, Prototipe I (tahap 1–2), II (tahap 3), III (tahap 4); lihat [Peta jalan](#peta-jalan-dan-daftar-kerja).**
- [x] Kapan skala sikap diisi: hanya di P5 setelah cerita penutup, atau juga di P1 sebagai data awal? **Diputuskan (25 Oktober 2026): hanya di P5, di Balai Refleksi.**
- [x] Ambang waktu tidak aktif (usulan 60 detik) dan tebakan cepat (usulan 1,5 detik) ditetapkan setelah uji coba? **Ya: mengikuti tahap 4 peta jalan, yang urutannya dikukuhkan 25 Oktober 2026. Sampai uji coba, ambang sementaranya jeda 3 menit dan tebakan cepat di bawah 3 detik (26 Oktober 2026); keduanya pengaturan yang mudah diubah.**
- [x] Karena sistem baru diubah setelah G2, bagaimana ahli media menilai layar baru (Skenario Digital, *Periksa Unggahan*, token) sebelum uji coba? **Diputuskan (25 Oktober 2026): dua langkah, mock-up di tahap 2 dan layar jadi di instalasi uji sebelum uji coba (G3).**
- [x] Model penelitian dan pengembangan, dan berapa tahap uji dengan siswa? **Diputuskan peneliti (1 November 2026): ADDIE (Branch, 2009), menggantikan Plomp. Uji kelompok kecil (8–12 anak, setelah berpikir lantang) dan uji kelompok menengah (± 80 siswa) di fase *Develop*, di luar data utama, di sekolah berawalan `U`; uji besar = pengambilan data utama, 300 siswa yang datanya bisa dianalisis; versi dikunci di G4; putaran penilaian ulang ahli tanpa batas ([04](04_CATATAN_KEPUTUSAN.md)).**

## Catatan perubahan sistem untuk tahap nanti

Belum ada yang dikerjakan sekarang. Daftar ini jadi bahan dokumen implementasi dan tahap 3, setelah desain instrumen disetujui. Karena semua bagian game boleh dirombak, daftar ini bukan batas; ini yang wajib ada untuk penelitian.

Dokumen implementasi no. 7, 10–15, 23, dan 24 didraf lebih dulu dalam empat bagian (keputusan pengembang 30 Oktober 2026, [04](04_CATATAN_KEPUTUSAN.md)): no. 10 di `27`, no. 11–13 dan 24 di `28`, no. 7, 14, dan 15 di `29`, no. 23 di `30`. Sisanya didraf setelah matriks revisi induk ([20](20_PAKET_VALIDASI.md)). Sistemnya tetap baru diubah di tahap 3. Baris no. 7, 10–15, dan 23 diselaraskan dengan keputusan pengembang di 27–30 (sesi koordinator, 30 Oktober 2026, `asal: pengembang`); rinciannya tetap di dokumen implementasi masing-masing.

| No | Perubahan | Analitik | Catatan untuk pengembang |
| --- | --- | --- | --- |
| 1 | Kode kesalahan pada setiap kata pengecoh, benda pengecoh, pernyataan, dan pilihan skenario | 6, 9 | Kolom baru di workbook bank soal; sistem menurunkan kode ke setiap percobaan salah (`item_tries`); kode tidak pernah dikirim ke browser |
| 2 | Tantangan kelima Skenario Digital di tiga wilayah | 10 | Engine baru dua langkah (tindakan, alasan), simpan `reason_code`; cerita, narasi, gambar, peta; Serpihan Cahaya jadi 15. Cerita penghubung (konteks `scenario_open`), kartu misi, naskah yang berubah, dan daftar rekaman ada di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#yang-perlu-disiapkan-di-tahap-3) |
| 3 | Label level AKM dan dimensi per butir; indikator per dimensi | 2, 3 | Kolom baru di bank soal dan ekspor butir; label Bloom tetap ada |
| 4 | Butir jangkar antarvariasi | 2, 3 | Kartu jangkar di Benar atau Salah, 2 pernyataan per wilayah, sama untuk variasi a dan b; butir berkode `x` (lihat [06](06_KISI_KISI_BANK_SOAL.md#butir-jangkar-usulan)) |
| 5 | Waktu aktif | 5 | Dihitung dari selang layar tersembunyi dan jeda tanpa masukan; ambangnya pengaturan di konfigurasi studi (sementara 3 menit), ditetapkan ulang dari hasil uji coba |
| 6 | Narasi instruksi dengan kode konteks sendiri | 7 | Supaya "mengulang instruksi" terpisah dari "memutar narasi soal" |
| 7 | Pembersihan log | semua | Hapus event dan kolom bertanda "Dihapus" di bagian *Log: dihapus atau dipisah*; pindahkan yang bertanda "Admin" keluar dari ekspor penelitian. `answer_changed` digabung ke `answer_submitted`, `dialogue_advanced` diganti `dialogue_completed`, data lama dihapus di migrasi setelah jumlahnya dicetak, dan halaman *Log teknis* untuk admin ([29 → Pembersihan log](29_IMPLEMENTASI_DATA_PENELITIAN.md#5-pembersihan-log-perubahan-no-7)) |
| 8 | Variabel beban permukaan per tantangan | 2, 5 | Jumlah rumpang, pengecoh, panjang bacaan, benda pengecoh, ukuran kotak di ekspor butir |
| 9 | Lapis data turunan | 1, 4, 5, 6, 9 | Satu tempat menghitung kode kesalahan, waktu aktif, "dilewati", tersensor, tebakan cepat; berversi dan diuji otomatis |
| 10 | Peran `peneliti` terpisah dari `admin` | semua | Hak akses sesuai tabel *Admin dan peneliti*; peneliti hanya melihat data pseudonim dan bisa me-reset kata sandi siswa lewat kode peserta; admin tidak mengunduh data penelitian; peran `guru` dihapus seluruhnya. Dua peran peneliti, `peneliti` dan `ketua`; hanya `ketua` yang mengunduh data dan mengubah studi; peta kemampuan menggantikan pemeriksaan "admin atau bukan" ([27](27_IMPLEMENTASI_PERAN_PENELITI.md)) |
| 11 | Token sesi lapangan | semua | Sesi lapangan di panel peneliti (sekolah, rombel, pertemuan, jam buka–tutup); wilayah yang dibuka diturunkan dari rencana pertemuan, tidak dipilih bebas (`region_opened`); baris dan kursi per kode peserta dimasukkan P1 dari L2 di P1, langsung setelah pendaftaran, lalu panel menghitung buklet dan bentuk tes dan memeriksa keseimbangannya per rombel (peringatan, tidak menahan); sesi susulan dengan token sendiri (kode `-S`, berikutnya `-S2`) hanya untuk siswa yang dicentang P1; token 6 huruf dan angka (tanpa O, 0, I, 1, L), sesi game tersambung ke sesi lapangan; di luar sesi seluruh game tertutup sampai studi ditutup; kemajuan disimpan per siswa per studi ([28](28_IMPLEMENTASI_SESI_LAPANGAN.md), keputusan pengembang 30 Oktober 2026) |
| 12 | Kode peserta baru `S03-A07` dan rombel | semua | Daftar kode sekolah penelitian; sekolah dan rombel dari token; nomor urut per rombel dibuat dengan aman saat banyak siswa mendaftar bersamaan. Kode sekolah di `schools.research_code`, awalan `S`, `U`, atau `V` (instalasi uji validator); pendaftaran P1 dimulai dengan token ([28 → Kode sekolah penelitian dan kode peserta](28_IMPLEMENTASI_SESI_LAPANGAN.md#10-kode-sekolah-penelitian-dan-kode-peserta)) |
| 13 | Panel pemantauan lapangan | 1 | Siapa belum selesai, sesi aktif, event hilang; hanya untuk operasional. Admin melihat angka saja, tanpa kode peserta ([28 → Panel pemantauan](28_IMPLEMENTASI_SESI_LAPANGAN.md#12-panel-pemantauan)) |
| 14 | Kamus data dan ekspor penelitian | semua | Ekspor hanya berisi data bertanda "Peneliti"; kamus data memuat definisi "dilewati", waktu aktif, dimensi, dan kode; event dan kolom mentah baru yang dibutuhkan kamus ada di [11](11_KAMUS_DATA.md#yang-harus-disediakan-sistem-di-tahap-3). Unduhan hanya untuk `ketua`, termasuk lembar `event` dan kunci jawaban; butir risiko daring hanya sebagai angka gabungan ([29 → Unduhan data penelitian](29_IMPLEMENTASI_DATA_PENELITIAN.md#7-unduhan-data-penelitian-perubahan-no-14)) |
| 15 | Templat spreadsheet lembar lapangan L1–L7 | semua | Kolomnya sama dengan [02_LEMBAR_LAPANGAN.md](02_LEMBAR_LAPANGAN.md), kuncinya kode peserta, jam WIB, dan kode sesi. Templat terisi kode diunduh per sesi lapangan dari panel; L1–L6 yang diketik diunggah dan diimpor ke sistem (pratinjau, peringatan nama, ketikan kedua lewat unggah cek); templat L7 hanya dibuat perintah di luar server web ([29 → Templat dan impor lembar lapangan](29_IMPLEMENTASI_DATA_PENELITIAN.md#10-templat-dan-impor-lembar-lapangan-perubahan-no-15), keputusan pengembang 30 Oktober 2026) |
| 16 | Mode penelitian: jam disembunyikan, sesi penelitian di layar kecil ditolak dengan pesan ramah (di luar studi cukup peringatan), pengerjaan ulang baru terbuka setelah kelima tantangan wilayah pertemuan itu tuntas, Kenali wilayah baru terbuka setelah wilayahnya tuntas ([12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#keputusan-pengembang), 26 Oktober 2026) | 5, semua | Sakelar di konfigurasi studi; data pengerjaan ulang disimpan tapi tidak dinilai; waktu tetap direkam server; ambang layar kecil ditetapkan dari ukuran panggung (keputusan media, 25 Oktober 2026) |
| 17 | Pemeriksaan otomatis area ketuk Cari Objek | 2, 6 | Area ketuk tidak boleh bersentuhan (jarak minimal setara 24 dp) di aturan bank soal, untuk babak 1 dan *Periksa Unggahan*; adegan Magelang dan Wonosobo ditata ulang |
| 18 | Aturan narasi | 7 | Bacaan dan pernyataan D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk *Periksa Unggahan* hanya lewat tombol ▶ dan tercatat |
| 19 | Perbaikan media lain dari telaah pendahuluan | semua | Temuan M-04 sampai M-11 di [10_TELAAH_PENDAHULUAN_MEDIA.md](10_TELAAH_PENDAHULUAN_MEDIA.md): nama berkas gambar benda yang acak, antrean event saat keluar, ukuran petunjuk, dua pelanggaran axe, kode penelitian di Profil, data pendaftaran |
| 20 | Bank soal baru dari kisi-kisi | 2, 3, 6, 10 | Cari Objek dua babak, kartu jangkar, variasi dari buklet B1–B4, aturan `DifficultyRules` baru, kolom dan sheet workbook baru; daftar lengkapnya di [06](06_KISI_KISI_BANK_SOAL.md#yang-perlu-disiapkan-sistem-nanti). Isi bank ada di [08](08_DRAF_BANK_SOAL.md). Selama studi, Susun Gambar diacak dari dua variasi yang keterangannya bersih dari kunci, termasuk satu gambar baru Temanggung ([08](08_DRAF_BANK_SOAL.md#susun-gambar-selama-studi)) |
| 21 | Skala pendamping sikap di Balai Refleksi ([17](17_SKALA_SIKAP.md); keputusan 25 dan 26 Oktober 2026) | pendamping | Butir skala dan jawabannya berkode sendiri (`sikap`, `skp-01` … `skp-12`), masuk ekspor peneliti, bukan bagian skor literasi digital. Layar skala di Balai Refleksi setelah cerita penutup, **sebelum** kritik dan saran: petunjuk, satu contoh soal tidak diskor (`skp-ct-01`), lalu satu pernyataan per layar dengan empat pilihan tanpa titik tengah (kata dan titik emas yang makin besar, tanpa wajah), *Lewati* dan *Kembali*, bilah kemajuan, urutan tetap, sekali kirim. Tidak dibacakan dan tanpa rekaman. Jawaban per butir disimpan di server; waktu tampil per butir dan penggantian jawaban dicatat. Pengantar Mbah Kedu yang memuji baru tampil setelah skala. Selama penelitian, kritik dan saran tetap ada sesudah skala dengan semua isian boleh kosong (aturan "minimal dua" tidak dipakai); bintang 1–5-nya masuk ekspor peneliti sebagai data kepraktisan (`feedback_rating`). Siswa yang tidak hadir di P5 mengisi di susulan paling lama 7 hari yang dibuka peneliti (`sikap_makeup`); siswa yang belum tuntas di akhir P5 tidak mendapat jalur khusus (data apa adanya, `sikap_missing_reason` = `belum_tuntas`). Variabelnya di [17](17_SKALA_SIKAP.md#variabel-untuk-kamus-data), masuk `kamus-1.1` |
| 22 | Modul tes awal–akhir dan angket akses digital ([13](13_TES_AWAL_AKHIR.md#modul-tes-di-gelita), [16](16_ANGKET_AKSES_DIGITAL.md); keputusan 26 Oktober 2026) | bukti validitas, kovariat | Tampilan Bootstrap tanpa unsur game; Bootstrap disimpan lokal berversi di `public/assets/vendor/`, tanpa CDN. Dibuka token sesi P1 dan P5; bentuk A/B ditetapkan panel peneliti dari baris dan kursi L2, yang dimasukkan P1 langsung setelah pendaftaran; tes awal menunggu sampai semua siswa punya bentuk. Satu bagian dalam satu waktu, boleh kembali sebelum dikirim; urutan pilihan tetap. Batas waktu per bagian tersembunyi, berupa pengaturan di konfigurasi studi. Jawaban tersimpan per butir di server; kunci dan skor tidak pernah dikirim ke browser. Waktu tampil per butir dan penggantian jawaban dicatat. Angket langsung setelah tes awal; butir risiko daring setelah tes akhir, hanya untuk siswa yang izinnya mencakup butir itu. Susulan dibuka peneliti. Jawaban, skor, dan variabel masuk ekspor peneliti sesuai [11](11_KAMUS_DATA.md) |
| 23 | Perlindungan anak, persetujuan, dan hak atas data di mode penelitian ([22](22_PERMOHONAN_IZIN_ETIK.md), [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md); keputusan 27 Oktober 2026) | semua | Teks centang persetujuan mode penelitian (23 → T4) di `Game.assentLabel`, `assentName`, `assentRequired` (id dan en); layar izin ulang sebelum butir risiko daring (23 → T11) dan pesan dukungan yang sama untuk semua siswa di P5, juga yang tanpa izin butir risiko (23 → T12); penanda tindak lanjut butir risiko dihitung sistem, hanya terlihat akun `ketua`, tidak pernah masuk ekspor; dua status mundur (`data tetap`, `hapus data`) dan hapus sebagian (hanya rekaman atau jawaban butir risiko) di permintaan hapus data; nama di akun diganti kode saat studi ditutup dan L7 dimusnahkan. Dari PP No. 33 Tahun 2026 ([22 → Dasar hukum](22_PERMOHONAN_IZIN_ETIK.md#dasar-hukum)): ambil data satu anak lewat kode untuk permintaan akses dan salinan orang tua, tanpa data anak lain dan tanpa kunci jawaban; pembetulan data pendaftaran (umur, jenis kelamin, kelas) oleh admin dengan jejak audit; penghapusan juga mencakup salinan cadangan server dan ekspor yang masih ada, dengan catatan tanggal untuk berita acara; ekspor daftar izin berkode tanpa nama sebelum nama di akun diganti kode. Dari panduan klirens etik BRIN ([22 → Masa simpan dan pemusnahan](22_PERMOHONAN_IZIN_ETIK.md#masa-simpan-dan-pemusnahan)): masa simpan bawaan studi penelitian GELITA paling singkat 20 tahun (`retention_days` 7305, dihitung sejak studi ditutup; sekarang 1825 dan dihitung sejak mulai sesi). Dari penilaian dampak ([26](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md), keputusan no. 2, langkah P18): pasang `HashedIpSessionHandler` (`app/Libraries/HashedIpSessionHandler.php`) di `Config\Session` dan bersihkan `ci_sessions` kedaluwarsa secara rutin, sebelum kegiatan pertama yang memakai akun GELITA anak (KG2 atau KG3, mana yang lebih dulu); sampai hapus sebagian, ambil data satu anak, dan pembetulan ada, dipakai jalan manual dengan berita acara (26 → P15). Dari keputusan pengembang 30 Oktober 2026 ([30](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md)): permintaan hapus dari `peneliti` dikonfirmasi `ketua` sebelum admin menjalankan; cadangan diganti setelah penghapusan dan saat studi ditutup; P1 memasukkan kolom berkode L7 ke panel (izin berkode), status mundur langsung menonaktifkan akun, dan daftar izin berkode diunduh dari sistem; tenggat per bagian di [30 → Tenggat](30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md#4-tenggat-sebelum-akun-gelita-anak-pertama-kali-dipakai) |
| 24 | Alat bantu `gelita:uji:akun` untuk instalasi uji validator ([20 → Prasyarat no. 8](20_PAKET_VALIDASI.md#prasyarat-no-8-instalasi-uji-dan-akun-uji), langkah 6; usulan sesi koordinator 30 Oktober 2026, diberi nomor oleh pengembang hari yang sama) | — | Perintah spark yang membuat akun siswa uji `uji.*` dan mereset kemajuan satu akun. Berjalan di luar alur token: akun dibuat langsung dengan sekolah `Sekolah Uji Validator` (kode `V01`) dan kode `UJI-01`, `UJI-02`, …, dan `--reset` menghapus data permainan akun itu ([28 → K15](28_IMPLEMENTASI_SESI_LAPANGAN.md#13-alat-bantu-gelitaujiakun-perubahan-no-24), keputusan pengembang 30 Oktober 2026). Didraf bersama no. 11 dan 12 di `28` karena memakai kode sekolah penelitian berawalan `V` dari no. 12 (diselaraskan 31 Oktober 2026; alasan lama "mengikuti alur pendaftaran baru" diganti). Hanya untuk instalasi uji, tidak pernah di server produksi: menolak berjalan bila instalasi berisi siswa yang bukan `uji.*` |

Laporan kelas untuk guru tetap tercatat sebagai gagasan di luar penelitian dan tidak masuk daftar ini.

## Cek fakta dan rujukan

Semua sumber di bawah dibuka atau dicari ulang pada 23 Oktober 2026. Yang belum bisa dipastikan ditandai.

**Cek fakta budaya di contoh**

| Fakta di dokumen | Hasil cek | Sumber |
| --- | --- | --- |
| Bundengan alat musik dari Wonosobo, berasal dari kowangan (tudung bambu) penggembala bebek | Benar. Bundengan juga dipakai mengiringi topeng lengger | [GNFI 2019](https://www.goodnewsfromindonesia.id/2019/10/14/bundenga-alat-musik-tradisional-yang-berawal-dari-pelindung-gembala), [GNFI 2025](https://www.goodnewsfromindonesia.id/2025/07/29/kampung-bundengan-inisiatif-melestarikan-alat-musik-tradisional) |
| Telaga Warna ada di Wonosobo | Benar: Desa Dieng Wetan, Kecamatan Kejajar, Wonosobo | [Indonesia Travel](https://www.indonesia.travel/uae-ar/destination/java/central-java/dieng-plateau---telaga-warna), [Suara.com](https://www.suara.com/lifestyle/2025/04/04/164027/6-rekomendasi-tempat-wisata-di-dieng-nuansa-alam-penuh-history?page=all) |
| Dieng Culture Festival sebagai latar skenario Wonosobo | **Dikoreksi.** Festival ini digelar di kompleks Candi Arjuna, Dieng, Banjarnegara. Contoh Wonosobo diganti ke Telaga Warna | [Jatengprov](https://jatengprov.go.id/publik/ahmad-luthfi-dan-ahy-ikut-mencukur-rambut-gimbal/), [GNFI 2025](https://www.goodnewsfromindonesia.id/2025/08/25/dieng-culture-festival-2025-angkat-tradisi-rambut-gimbal-ke-panggung-internasional) |
| Ruwatan rambut gimbal adalah tradisi Dieng | Benar; dipakai di contoh DIG-ETK sebagai "tradisi Dieng" | [Detik](https://www.detik.com/jateng/budaya/d-6813272/ruwatan-anak-gimbal-di-dieng-7-jenis-tumpeng-dan-aneka-jajanan-disajikan) |
| Lengger/topeng lengger kesenian Wonosobo | Benar | [Wikipedia: Lengger](https://en.wikipedia.org/wiki/Lengger), [jurnal UIN Gus Dur](https://e-journal.uingusdur.ac.id/iqtida/article/view/12940) |
| Angklung dari Jawa Barat; Borobudur di Magelang; Sindoro–Sumbing di Temanggung | Pengetahuan umum dan sudah ada di bank soal; tidak dicek ulang | — |

**Kerangka dan kebijakan (dicek)**

- Asesmen Nasional 2025 tetap digelar untuk sampel siswa kelas 5 SD: [Kompas, 18 Maret 2025](https://www.kompas.com/edu/read/2025/03/18/135650571/pemerintah-pastikan-asesmen-nasional-dan-ujian-nasional-digelar-2025).
- Kepmendikdasmen Nomor 56 Tahun 2026 tentang Pedoman Penyelenggaraan TKA dan AN: [Detik, 11 Maret 2026](https://www.detik.com/edu/sekolah/d-8394778/jadwal-tka-dan-an-terbaru-jenjang-sd-smp-sma-smk-sederajat). TKA kelas 6 SD 2026 memuat Bahasa Indonesia dan literasi membaca: [Medcom](https://www.medcom.id/pendidikan/news-pendidikan/GNGG4eQN-kerjakan-tka-sd-2026-jangan-panik-sebenarnya-ini-diujikan-ke-siswa). **Belum pasti:** apakah AKM untuk kelas 5 masih berjalan. Cek teks resmi di JDIH Kemendikdasmen.
- Level kognitif AKM literasi membaca (menemukan informasi; interpretasi dan integrasi; evaluasi dan refleksi), dirangkum dari kerangka AKM Pusmendik: [ringkasan](https://akupintar.id/info-pintar/-/blogs/komponen-asesmen-kompetensi-minimum). Dokumen kerangka resminya perlu diunduh dari Pusmendik.
- von Davier, M., & Kennedy, A. (Eds.). (2024). *PIRLS 2026 Assessment Frameworks*. TIMSS & PIRLS International Study Center, Boston College: [DOI](https://doi.org/10.6017/lse.tpisc.tr2103.kb4199), [situs resmi](https://timssandpirls.bc.edu/pirls2026/frameworks/index.html). Empat proses pemahaman: fokus dan mengambil informasi tersurat (20%), membuat inferensi langsung (30%), menafsirkan dan memadukan gagasan (30%), mengevaluasi dan mengkritisi isi teks (20%) ([Bab 1](https://timssandpirls.bc.edu/pirls2026/frameworks/downloads/P26_Ch1_Reading-Assessment.pdf)).
- Cosgrove, J., & Cachia, R. (2025). *DigComp 3.0: European Digital Competence Framework* (edisi ke-5). Joint Research Centre, Publications Office of the European Union: [DOI](https://doi.org/10.2760/0001149), [berita JRC, 27 November 2025](https://joint-research-centre.ec.europa.eu/jrc-news-and-updates/european-commission-updates-its-digital-competence-framework-digcomp-2025-11-27_en). Menggantikan DigComp 2.2.
- Empat pilar literasi digital (Cakap, Etis, Aman, Budaya Bermedia Digital) dari modul Kominfo (sekarang Komdigi), Japelidi, dan Siberkreasi: [Komdigi Gaungkan Gerakan Literasi Digital](https://www.metrotvnews.com/read/NP6CpMve-komdigi-gaungkan-gerakan-literasi-digital). Modul resminya perlu diunduh dari situs Siberkreasi.
- Ocumpaugh, J., Baker, R. S., & Rodrigo, M. M. T. (2015). *BROMP 2.0 Technical and Training Manual*. Versi terbaru yang ditemukan masih 2015: [ringkasan](https://en.wikipedia.org/wiki/Baker_Rodrigo_Ocumpaugh_Monitoring_Protocol).
- UU Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi. Per Oktober 2025, peraturan pelaksananya masih menunggu penetapan: [Hukumonline](https://www.hukumonline.com/berita/a/menanti-disahkannya-aturan-turunan-uu-pdp-lt68fae7fbe057d?page=all).

**Rujukan metode (klasik; dicek ulang ke sumber asli pada 26 Oktober 2026, rinciannya di [dokumen pendukung peneliti](dokumen-pendukung-peneliti/README.md))**

- Mislevy, R. J., Steinberg, L. S., & Almond, R. G. (2003). On the structure of educational assessments. *Measurement: Interdisciplinary Research and Perspectives, 1*(1), 3–62.
- Anderson, L. W., & Krathwohl, D. R. (Eds.). (2001). *A Taxonomy for Learning, Teaching, and Assessing*. Longman.
- Biggs, J. B., & Collis, K. F. (1982). *Evaluating the Quality of Learning: The SOLO Taxonomy*. Academic Press.
- Webb, N. L. (1997). *Criteria for Alignment of Expectations and Assessments in Mathematics and Science Education* (Research Monograph No. 6). NISE dan CCSSO: [ERIC ED414305](https://eric.ed.gov/?id=ED414305). Baru memuat kriteria *depth-of-knowledge consistency*, belum level bernomor.
- Webb, N. L. (1999). *Alignment of Science and Mathematics Standards and Assessments in Four States* (Research Monograph No. 18): [ERIC ED440852](https://eric.ed.gov/?id=ED440852). Sumber empat level *Depth of Knowledge* (Recall, Skill/Concept, Strategic Thinking, Extended Thinking), hlm. 2.
- Ericsson, K. A., & Simon, H. A. (1993). *Protocol Analysis: Verbal Reports as Data* (edisi revisi). MIT Press.
- Wise, S. L., & Kong, X. (2005). Response time effort: A new measure of examinee motivation in computer-based tests. *Applied Measurement in Education, 18*(2), 163–183.
- Bond, T. G., & Fox, C. M. (2015). *Applying the Rasch Model* (edisi ke-3). Routledge.
- Cohen, J. (1960). A coefficient of agreement for nominal scales. *Educational and Psychological Measurement, 20*(1), 37–46.

**Dokumen internal**

- Analitik Game Gelita Terbaru (dokumen 10 analitik dari pengembang).
- [`docs/bank-soal/README.md`](../bank-soal/README.md) (bank soal kelas 5) dan [`docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`](../perombakan-kelas5/CATATAN_IMPLEMENTASI.md) (catatan keputusan perombakan kelas 5).
