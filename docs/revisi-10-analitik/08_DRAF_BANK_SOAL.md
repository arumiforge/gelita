# Draf Bank Soal GELITA Kelas 5 (versi kisi-kisi)

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 25 Oktober 2026 (nama fiktif dicek; telaah pendahuluan T6 sudah dijalankan, lihat [09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md)) · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Dasar: [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md) (disetujui pemilik proyek 25 Oktober 2026) · Status semua butir: **`needs_verification`**

Ini isi bank soal baru, ditulis mengikuti kisi-kisi. Dokumen ini masih **draf untuk telaah dan validasi ahli**. Bentuknya dokumen supaya mudah dibaca ahli. Setelah lolos validasi, isinya dipindah ke berkas data [`docs/bank-soal/`](../bank-soal/README.md) dan diterjemahkan ke bahasa Inggris. Belum ada yang diubah di sistem atau di workbook.

## Cara membaca

**Kolom di setiap tabel butir**

| Kolom | Arti |
| --- | --- |
| Kode | Kode butir usulan dari kisi-kisi: `a`/`b` = variasi, `x` = dipakai semua buklet |
| Kunci | Jawaban yang benar. Kunci hanya ada di server, tidak pernah dikirim ke browser |
| Sub | Sub-aspek D1: D1a memahami informasi, D1b menilai informasi dan sumber |
| AKM | L1 menemukan informasi, L2 interpretasi dan integrasi, L3 evaluasi dan refleksi |
| Bloom | Label kedua (C1–C6) |
| Kode salah | Kode kesalahan yang dicatat kalau siswa keliru |

**Aturan kode di Isi Rumpang.** Setiap pasangan rumpang × kata salah punya satu kode. Supaya tabelnya tidak terlalu panjang, dipakai tiga aturan tetap:

1. Kata yang sebenarnya jawaban rumpang lain → **INF-REL** (benar menurut teks, tapi bukan untuk rumpang ini).
2. Kata pengecoh di rumpang sasarannya → kode yang ditulis di tabel (kolom "Pengecoh sasaran").
3. Kata pengecoh di rumpang lain → **INF-LIT**, kecuali ditulis lain di baris "Kode khusus".

**Aturan kode di Periksa Unggahan (babak 2 Cari Objek).** Setiap petunjuk punya beberapa *pengecoh utama*, bagian adegan yang sengaja dibuat menggoda; kodenya ditulis di tabel. Mengetuk bagian ketuk lain, termasuk kunci petunjuk lain → **kode jenis petunjuk itu**: atribusi BUD-ATR, representasi BUD-REP, izin BUD-IZN, menghargai tradisi BUD-ETK. Mengetuk tempat kosong di dekat kunci → **NON-SEN**, bukan percobaan dan tidak memakai kesempatan (keputusan 26 Oktober 2026, [kamus data](11_KAMUS_DATA.md#analitik-6-pola-kesalahan)).

**Skenario Digital.** Skor tindakan: tepat = 2, kurang tepat = 1, berisiko = 0. Alasan tidak diskor, hanya dikode. Kalimat tanya alasan selalu sama: **"Yang paling penting kupikirkan di sini adalah …"**. Pilihan tindakan dan alasan diacak per pengerjaan. Umpan balik tidak memakai kata "salah" atau "gagal".

**Nama dan alamat tiruan.** Semua akun, sanggar, warung, situs, dan tokoh di bacaan digital adalah fiktif. Unggahan tampil di aplikasi media sosial fiktif **Kedugram**, jadi nama akun di game tidak merujuk ke akun di platform sungguhan. Komentator ditulis dengan nama depan saja, tanpa @. Alamat situs memakai domain `.example`. Sebelum dipakai, setiap nama dicek bahwa tidak sama dengan akun, sanggar, atau usaha nyata (lihat *Yang harus dicek sebelum validasi*).

**Gambar.** Bacaan digital tampil sebagai tangkapan layar tiruan (papan informasi, unggahan, grup percakapan, hasil pencarian). Teksnya tetap teks biasa supaya bisa dibacakan narasi dan diperbesar. Daftar gambar baru ada di bagian akhir.

## Jumlah

| | Temanggung | Magelang | Wonosobo | Jumlah |
| --- | --- | --- | --- | --- |
| Isi Rumpang (per variasi × 2) | 4 × 2 | 5 × 2 | 6 × 2 | 30 |
| Benar atau Salah (per variasi × 2) | 4 × 2 | 6 × 2 | 6 × 2 | 32 |
| Kartu jangkar | 2 | 2 | 2 | 6 |
| Periksa Unggahan (D3) | 4 × 2 | 4 × 2 | 4 × 2 | 24 |
| Skenario Digital (D2) | 4 × 2 | 4 × 2 | 4 × 2 | 24 |
| **Butir literasi digital** | **34** | **40** | **42** | **116** |
| Temukan Benda (PBK, dari bank lama) | 4 | 4 | 4 | 12 |

---

# Temanggung (mudah)

Level utama: menemukan informasi (L1) · Bloom C1–C2 · Skenario: tanda bahaya jelas

## `tmg-2` Isi Rumpang

**Instruksi:** "Baca bacaannya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Ada dua kata yang tidak terpakai."
**Genre:** teks informasi (artikel Pustaka) · 4 rumpang · bank kata 6 (4 jawaban + 2 pengecoh)

### Variasi a: Dua Gunung Temanggung

> Kabupaten Temanggung di Jawa Tengah diapit dua gunung api, yaitu Gunung Sindoro dan Gunung Sumbing. Orang menyebut keduanya gunung kembar karena bentuknya mirip. Tinggi Gunung Sindoro 3.136 meter, sedangkan Gunung **(1)** tingginya 3.371 meter. Dahulu kedua gunung itu pernah meletus dan menyebarkan **(2)**. Abu itu membuat tanah di lerengnya subur. Di tanah yang **(3)** itu, petani menanam kopi arabika. Kopi itu tumbuh di ketinggian lebih dari 900 meter. Jadi, kebun kopinya berada di tempat yang **(4)**.

Panjang ± 75 kata · Pengecoh bank kata: *Sindoro*, *pasir*

| Kode | Rumpang | Kunci | Sub | AKM | Bloom | Pengecoh sasaran | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| tmg-2-a01 | (1) | Sumbing | D1a | L1 | C1 | *Sindoro* → INF-LIT | Nama sudah disebut di kalimat pertama; perbaikan tmg-2-a01 lama |
| tmg-2-a02 | (2) | abu | D1a | L1 | C1 | *pasir* → INF-HUB | Jawaban ada di kalimat berikutnya ("Abu itu …"); *pasir* menangkap siswa yang tidak menghubungkan dua kalimat |
| tmg-2-a03 | (3) | subur | D1a | L1 | C1 | — | Tersurat di kalimat sebelumnya; *tinggi* tetap bersaing |
| tmg-2-a04 | (4) | tinggi | D1a | L2 | C2 | — | Menyimpulkan dari "lebih dari 900 meter" |

Judul diganti dari "Gunung Kembar" supaya jawaban tidak bocor (05, daftar perbaikan).

### Variasi b: Mata Air di Lereng Sindoro

> Di lereng Gunung Sindoro ada mata air bernama Umbul Jumprit. Airnya jernih dan tidak pernah kering, bahkan pada musim **(1)**. Musim kemarau adalah musim ketika hujan jarang turun. Air dari Umbul **(2)** mengalir menjadi hulu Sungai Progo. Mata air ini juga penting bagi umat Buddha. Setiap Waisak, mereka mengambil air suci dari sini. Air **(3)** itu lalu dibawa ke Candi Borobudur. Warga menjaga pepohonan di sekitar mata air, karena akar pohon membantu menyimpan air. Kalau pohon-pohon itu ditebang, mata air bisa **(4)**.

Panjang ± 80 kata · Pengecoh bank kata: *hujan*, *penuh*

| Kode | Rumpang | Kunci | Sub | AKM | Bloom | Pengecoh sasaran | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| tmg-2-b01 | (1) | kemarau | D1a | L1 | C1 | *hujan* → INF-HUB | Kata "kemarau" ada di kalimat berikutnya; *hujan* menangkap siswa yang mengabaikan "bahkan" |
| tmg-2-b02 | (2) | Jumprit | D1a | L1 | C1 | — | Nama sudah disebut |
| tmg-2-b03 | (3) | suci | D1a | L1 | C1 | — | Tersurat di kalimat sebelumnya; mengganti tmg-2-b03 lama ("sampah") yang terjawab dari norma |
| tmg-2-b04 | (4) | kering | D1a | L2 | C2 | *penuh* → INF-HUB | Sebab–akibat: pohon menyimpan air → tanpa pohon, mata air kering |

**Rujukan:** variasi a: Pemkab Temanggung (profil daerah); data ketinggian gunung PVMBG; Dinas Pertanian Kab. Temanggung dan MPIG Kopi Java Arabika Sindoro Sumbing (kopi di atas 900 m). Variasi b: Wikipedia *Umbul Jumprit*; Mongabay (2018) *Berkah Air dari Umbul Jumprit*; Liputan6 (air suci Waisak dari Umbul Jumprit). Kalimat "akar pohon membantu menyimpan air" adalah pengetahuan IPA umum.

## `tmg-3` Benar atau Salah

**Instruksi:** "Baca layar informasi, lalu pilih BENAR atau SALAH untuk setiap pernyataan."
**Genre:** papan informasi digital (layar di pameran) · 1 paragraf · 4 pernyataan (1 bergambar) · kunci 2 : 2

### Variasi a: Situs Liyangan

> **LAYAR INFORMASI · Pameran Budaya Temanggung**
> Dipasang oleh: Panitia Pameran Budaya SD Lereng Sindoro · Diperbarui Sabtu, 12 Juli
>
> Situs Liyangan berada di Desa Purbosari, Kecamatan Ngadirejo, Temanggung. Situs ini ditemukan pada tahun 2008 ketika warga sedang menambang pasir. Di dalam tanah, para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi yang hangus. Semua itu tertimbun pasir dan batu dari letusan Gunung Sindoro pada masa lalu.

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| tmg-3-a01 | Situs Liyangan ditemukan pada tahun 2008. | benar | D1a | L1 | C1 | INF-LIT |
| tmg-3-a02 | Situs Liyangan tertimbun pasir dan batu dari letusan Gunung Merapi. | salah | D1a | L1 | C1 | INF-LIT |
| tmg-3-a03 | Layar ini tidak menyebutkan siapa pemasangnya, jadi kita tidak tahu siapa yang bisa ditanya kalau isinya keliru. | salah | D1b | L1 | C2 | INF-SUM |
| tmg-3-a04 | [Gambar: butir padi hangus] Gambar ini menunjukkan salah satu benda yang ditemukan di Situs Liyangan. | benar | D1a | L1 | C2 | INF-LIT |

### Variasi b: Candi Pringapus dan Prasasti Gondosuli

> **LAYAR INFORMASI · Pameran Budaya Temanggung**
> Dipasang oleh: Panitia Pameran Budaya SD Lereng Sindoro · Diperbarui Sabtu, 12 Juli
>
> Candi Pringapus adalah candi Hindu peninggalan abad ke-9 di Temanggung. Di dalam ruang candinya ada arca Nandi, yaitu arca lembu yang dihormati umat Hindu. Temanggung juga punya Prasasti Gondosuli yang dibuat pada tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| tmg-3-b01 | Candi Pringapus adalah candi Buddha. | salah | D1a | L1 | C1 | INF-LIT |
| tmg-3-b02 | Prasasti Gondosuli dibuat pada tahun 832 Masehi. | benar | D1a | L1 | C1 | INF-LIT |
| tmg-3-b03 | Layar ini dipasang tanpa nama pemasang, jadi isinya tidak bisa ditanyakan kepada siapa pun. | salah | D1b | L1 | C2 | INF-SUM |
| tmg-3-b04 | [Gambar: arca lembu duduk dari batu] Gambar ini menunjukkan arca yang ada di dalam ruang Candi Pringapus. | benar | D1a | L1 | C2 | INF-LIT |

Kedua variasi punya struktur yang sama: dua butir tersurat, satu butir penanda sumber yang kuncinya *salah*, satu butir gambar yang kuncinya *benar*. Urutan kunci: a = B S S B, b = S B S B.

**Rujukan:** Pemkab Temanggung (*Sejarah Temanggung*); Kompas.com (2023) *Situs Liyangan*; BPK Wilayah X Jawa Tengah. Panitia dan sekolah di layar informasi fiktif.

### Kartu jangkar Temanggung (sama di variasi a dan b)

Muncul sesudah keempat pernyataan, sebagai kartu kedua di tantangan yang sama.

> **Grup Kelas 5A** · Bu Sari (wali kelas) · 07.15
> Anak-anak, besok Kamis kita berkunjung ke Pameran Budaya di balai desa. Kumpul di sekolah pukul 07.30, ya. Bawa topi dan botol minum sendiri. Pameran ditutup pukul 11.00.

Panjang ± 35 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| tmg-3-x01 | Menurut pesan itu, siswa berkumpul di balai desa pukul 07.30. | salah | D1a | L1 | C1 | INF-KAT |
| tmg-3-x02 | Siswa diminta membawa botol minum sendiri. | benar | D1a | L1 | C1 | INF-LIT |

## `tmg-4` Cari Objek

### Babak 1: Temukan Benda (PBK, satu adegan untuk semua)

Diambil dari bank lama tanpa perubahan isi: adegan *Pasar Budaya Temanggung*, 4 petunjuk, 4 pengecoh.

| Kode | Petunjuk | Kunci |
| --- | --- | --- |
| tmg-4-x01 | Temukan kuda-kudaan dari anyaman bambu yang dipakai untuk menari kuda lumping. | kuda kepang bambu |
| tmg-4-x02 | Temukan biji kopi yang tumbuh di lereng Gunung Sindoro dan Sumbing. | biji kopi |
| tmg-4-x03 | Temukan arca lembu Nandi, seperti yang ada di dalam Candi Pringapus. | arca Nandi |
| tmg-4-x04 | Temukan ikan uceng, ikan sungai kecil yang menjadi makanan khas Temanggung. | ikan uceng |

Pengecoh: angklung, ondel-ondel, perahu pinisi, sasando, semuanya **INF-BUD**.

### Babak 2: Periksa Unggahan (D3)

**Instruksi:** "Ini unggahan tentang budaya Temanggung. Baca petunjuknya, lalu ketuk bagian unggahan yang tepat."
**Tingkat:** tanda keliru jelas · 8 bagian ketuk · 2 pengecoh utama per petunjuk

#### Variasi a: Unggahan foto kuda lumping

**Adegan.** Layar unggahan media sosial tiruan. Di sebelahnya ada papan informasi pameran.

| No | Bagian ketuk | Isi |
| --- | --- | --- |
| A1 | Nama akun pengunggah | `@jalan2_bima` |
| A2 | Tulisan kecil di pojok foto | "Foto: Sanggar Kuda Pelangi" |
| A3 | Keterangan unggahan | "Tari kuda lumping di lapangan desa! Kuda-kudaannya dari kayu jati 😍" |
| A4 | Papan informasi pameran | "Kuda lumping memakai kuda-kudaan dari anyaman bambu yang dicat warna-warni." |
| A5 | Komentar 1 | **Nadia**: "Keren! Terima kasih sudah berbagi foto kesenian Temanggung 👏" |
| A6 | Komentar 2 | **Doni**: "Tarian kuno, nggak zaman! 😝" |
| A7 | Tombol | "Kirim pesan ke sanggar" |
| A8 | Tombol | "Bagikan" |

| Kode | Jenis | Petunjuk | Kunci | Pengecoh utama | Bloom |
| --- | --- | --- | --- | --- | --- |
| tmg-4-a01 | Atribusi | Foto ini bukan buatan pemilik akun. Ketuk tulisan yang menunjukkan siapa pembuat fotonya. | A2 | A1 → BUD-ATR; A4 → BUD-ATR | C2 |
| tmg-4-a02 | Representasi | Bacalah papan informasi. Ketuk bagian unggahan yang isinya tidak sesuai dengan papan itu. | A3 | A4 → BUD-REP; A6 → BUD-ETK | C2 |
| tmg-4-a03 | Izin | Kamu ingin memasang foto ini di majalah dinding kelas. Ketuk langkah pertama yang tepat. | A7 | A8 → BUD-IZN; A1 → BUD-IZN | C2 |
| tmg-4-a04 | Menghargai tradisi | Ketuk komentar yang menghargai kesenian ini. | A5 | A6 → BUD-ETK; A3 → BUD-ETK | C2 |

#### Variasi b: Unggahan video ikan uceng

| No | Bagian ketuk | Isi |
| --- | --- | --- |
| B1 | Nama akun pengunggah | `@sekar.kuliner` |
| B2 | Tulisan kecil di pojok video | "Video: Warung Tepi Kali Bu Tini" |
| B3 | Keterangan unggahan | "Uceng goreng, ikan laut khas Temanggung. Kriuk! 😋" |
| B4 | Papan informasi pameran | "Ikan uceng adalah ikan sungai kecil, makanan khas Temanggung." |
| B5 | Komentar 1 | **Raka**: "Wah, jadi pengin coba. Terima kasih infonya 🙏" |
| B6 | Komentar 2 | **Tio**: "Ih, ikan kecil gitu, jijik! 🤢" |
| B7 | Tombol | "Kirim pesan ke warung" |
| B8 | Tombol | "Bagikan" |

| Kode | Jenis | Petunjuk | Kunci | Pengecoh utama | Bloom |
| --- | --- | --- | --- | --- | --- |
| tmg-4-b01 | Atribusi | Video ini bukan buatan pemilik akun. Ketuk tulisan yang menunjukkan siapa pembuat videonya. | B2 | B1 → BUD-ATR; B4 → BUD-ATR | C2 |
| tmg-4-b02 | Representasi | Bacalah papan informasi. Ketuk bagian unggahan yang isinya tidak sesuai dengan papan itu. | B3 | B4 → BUD-REP; B6 → BUD-ETK | C2 |
| tmg-4-b03 | Izin | Kamu ingin memakai video ini untuk tugas kelas. Ketuk langkah pertama yang tepat. | B7 | B8 → BUD-IZN; B1 → BUD-IZN | C2 |
| tmg-4-b04 | Menghargai tradisi | Ketuk komentar yang menghargai makanan khas ini. | B5 | B6 → BUD-ETK; B3 → BUD-ETK | C2 |

**Rujukan:** kuda kepang dari anyaman bambu: DPRD Jateng (*Kuda Lumping, Kesenian Khas Temanggung*); uceng ikan sungai dan makanan khas Temanggung: DJKI (indikasi geografis), Antara Jateng (dicek di 05). Akun, sanggar, dan warung fiktif.

## `tmg-5` Skenario Digital

**Instruksi:** "Bacalah ceritanya. Pilih apa yang akan kamu lakukan, lalu pilih alasanmu."
**Tingkat:** tanda bahaya jelas · 4 skenario per variasi · 3 tindakan · 3 alasan

### Variasi a

**tmg-5-a01 · Data pribadi.** Kamu menemukan situs kuis berhadiah. Tulisannya: "Isi nama lengkap, alamat rumah, dan nama sekolahmu, lalu ikut undian sepeda!" Kamu tidak tahu siapa pembuat situs itu.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Isi nama, alamat, dan sekolah supaya bisa ikut undian | 0 | DIG-PRI | Alamat rumah dan nama sekolah bisa dipakai orang yang tidak kita kenal untuk menemukanmu. Data seperti itu sebaiknya disimpan. |
| Tutup situsnya dan cerita ke orang tua | 2 | — | Pilihan yang aman. Orang tua bisa membantu memeriksa apakah kuis itu sungguhan. |
| Tutup situsnya dan lanjut bermain yang lain | 1 | DIG-PSF | Kamu sudah aman. Akan lebih baik lagi kalau kamu cerita ke orang tua, supaya mereka tahu ada situs seperti itu. |

| Alasan | Kode |
| --- | --- |
| Alamat dan sekolahku bisa dipakai orang asing | R-RISK |
| Pokoknya tidak boleh mengisi apa pun di internet | R-ATUR |
| Teman-temanku sudah ikut semua undian itu | R-SOS |

**tmg-5-a02 · Kata sandi.** Kamu sedang bermain game di tablet. Seseorang yang tidak kamu kenal mengirim pesan: "Aku bisa bikin levelmu naik cepat. Kirim kata sandimu, ya!"

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Kirim kata sandinya supaya levelmu cepat naik | 0 | DIG-SAN | Orang yang tahu kata sandimu bisa masuk dan memakai akunmu. Kata sandi hanya untuk pemilik akun. |
| Tunjukkan pesan itu ke orang tua, lalu laporkan | 2 | — | Tepat sekali. Kata sandimu aman, dan orang dewasa bisa membantu melapor. |
| Tidak membalas, lalu keluar dari game itu | 1 | DIG-PSF | Kata sandimu aman. Ceritakan juga ke orang tua supaya akun itu bisa dilaporkan. |

| Alasan | Kode |
| --- | --- |
| Orang yang tahu sandiku bisa memakai akunku | R-RISK |
| Kata guru, kata sandi tidak boleh diberikan | R-ATUR |
| Aku sedang malas bermain game hari ini | R-ACAK |

**tmg-5-a03 · Izin membagikan foto diri.** Kamu punya foto dirimu saat menari kuda lumping. Kamu ingin mengunggahnya ke akun media sosial milik ibumu. Ibumu sedang memasak di dapur.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Unggah sendiri lewat HP ibu, nanti baru bilang | 0 | DIG-IZN | Akun itu milik ibumu. Sebelum mengunggah apa pun di akun orang lain, kita perlu izin pemiliknya. |
| Tanya kakakmu dulu, boleh diunggah atau tidak | 1 | DIG-IZN | Bertanya itu bagus. Tapi yang punya akun adalah ibumu, jadi izinnya perlu dari ibu. |
| Tunggu ibu selesai memasak, lalu tanya ibu | 2 | — | Tepat. Ibu bisa memutuskan, dan bisa membantu memilih siapa saja yang boleh melihat foto itu. |

| Alasan | Kode |
| --- | --- |
| Ibu pemilik akunnya, jadi ibu yang memutuskan | R-ETIK |
| Kalau ketahuan tanpa izin, nanti aku dimarahi | R-ATUR |
| Biar teman-temanku cepat melihat aku menari | R-SOS |

**tmg-5-a04 · Kabar bohong.** Di grup keluarga muncul pesan: "Mulai besok semua sekolah di Temanggung libur seminggu! Sebarkan ke semua grup!" Pesan itu tidak menyebut siapa pembuatnya.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Teruskan ke grup kelas supaya teman-teman tahu | 0 | DIG-HOX | Kabar tanpa sumber belum tentu benar. Kalau diteruskan, banyak teman bisa ikut bingung. |
| Tanyakan dulu ke wali kelas apakah itu benar | 2 | — | Tepat. Guru dan orang tua bisa memeriksa ke sumber yang jelas, misalnya pengumuman sekolah. |
| Tidak meneruskan dan tidak bertanya ke siapa pun | 1 | DIG-PSF | Kamu tidak ikut menyebarkan, bagus. Bertanya ke guru atau orang tua akan lebih membantu. |

| Alasan | Kode |
| --- | --- |
| Kabar tanpa sumber belum tentu benar | R-RISK |
| Ayah melarangku membalas pesan di grup | R-ATUR |
| Biar teman-teman tahu duluan dari aku | R-SOS |

### Variasi b

**tmg-5-b01 · Data pribadi.** Kamu ingin ikut lomba menggambar daring. Formulirnya meminta nama, kelas, nomor HP orang tua, dan alamat rumah lengkap. Di halaman itu tidak ada nama penyelenggara.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Isi semua data supaya bisa langsung ikut lomba | 0 | DIG-PRI | Nomor HP orang tua dan alamat rumah adalah data pribadi. Jangan diberikan ke penyelenggara yang tidak jelas. |
| Berhenti mengisi dan tanya orang tua dulu | 2 | — | Pilihan yang aman. Orang tua bisa mencari tahu siapa penyelenggaranya. |
| Berhenti mengisi, lalu cari lomba yang lain | 1 | DIG-PSF | Datamu aman. Akan lebih baik kalau orang tua juga tahu. |

| Alasan | Kode |
| --- | --- |
| Data pribadi bisa dipakai orang asing | R-RISK |
| Pokoknya tidak boleh mengisi formulir apa pun | R-ATUR |
| Aku memang tidak terlalu suka menggambar | R-ACAK |

**tmg-5-b02 · Kata sandi.** Di ruang obrolan game, akun yang tidak kamu kenal menulis: "Halo, aku bisa kasih koin gratis. Tulis kata sandimu di sini, ya!"

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Tulis kata sandinya supaya dapat koin gratis | 0 | DIG-SAN | Kata sandi yang ditulis di ruang obrolan bisa dibaca orang lain. Akunmu bisa diambil alih. |
| Tunjukkan ke orang tua, lalu laporkan akunnya | 2 | — | Tepat. Akunmu aman dan akun itu bisa dilaporkan. |
| Tidak menjawab, lalu keluar dari ruang obrolan | 1 | DIG-PSF | Akunmu aman. Ceritakan juga ke orang tua, ya. |

| Alasan | Kode |
| --- | --- |
| Orang yang tahu sandiku bisa mengambil akunku | R-RISK |
| Kata guru, kata sandi itu harus dirahasiakan | R-ATUR |
| Teman-temanku juga tidak menulis kata sandi | R-SOS |

**tmg-5-b03 · Izin membagikan foto diri.** Kamu punya video dirimu sedang belajar menari. Kamu ingin mengunggahnya ke akun media sosial milik ayahmu. Ayahmu sedang di kebun kopi.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Unggah sendiri lewat HP ayah, nanti baru bilang | 0 | DIG-IZN | Akun itu milik ayahmu. Perlu izin pemiliknya dulu sebelum mengunggah. |
| Tanya temanmu dulu, boleh diunggah atau tidak | 1 | DIG-IZN | Bertanya itu bagus. Tapi yang berhak memberi izin adalah pemilik akun, yaitu ayahmu. |
| Tunggu ayah pulang, lalu tanya ayah | 2 | — | Tepat. Ayah bisa memutuskan dan membantu mengatur siapa yang boleh menonton. |

| Alasan | Kode |
| --- | --- |
| Ayah pemilik akunnya, jadi ayah yang memutuskan | R-ETIK |
| Kalau ketahuan tanpa izin, nanti aku dimarahi | R-ATUR |
| Biar banyak orang menyukai video menariku | R-SOS |

**tmg-5-b04 · Kabar bohong.** Di grup kelas ada pesan: "Minum kopi bikin anak-anak jadi pintar! Sebarkan ke semua teman!" Pesan itu tidak menyebut siapa pembuatnya.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Teruskan ke grup lain supaya banyak yang tahu | 0 | DIG-HOX | Kabar tanpa sumber belum tentu benar. Kopi mengandung kafein dan bukan minuman untuk anak-anak. |
| Tanyakan dulu ke guru apakah itu benar | 2 | — | Tepat. Guru dan orang tua bisa membantu mencari sumber yang dapat dipercaya. |
| Tidak meneruskan dan tidak bertanya ke siapa pun | 1 | DIG-PSF | Kamu tidak ikut menyebarkan, bagus. Bertanya akan lebih membantu teman-temanmu. |

| Alasan | Kode |
| --- | --- |
| Kabar tanpa sumber belum tentu benar | R-RISK |
| Ibu melarangku membuka pesan di grup kelas | R-ATUR |
| Aku memang tidak suka minum kopi | R-ACAK |

Kopi untuk orang dewasa, bukan anak-anak: dari bacaan lama `tmg-3-c` (rujukan Dinas Pertanian Kab. Temanggung).

---

# Magelang (sedang)

Level utama: interpretasi dan integrasi (L2) · Bloom C2–C4 · Skenario: tanda bahaya samar

## `mgl-2` Isi Rumpang

**Instruksi:** "Baca bacaannya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Ada dua kata yang tidak terpakai."
**Genre:** teks informasi (artikel majalah anak) · 5 rumpang · bank kata 7 (5 jawaban + 2 pengecoh)

### Variasi a: Candi Borobudur

> Candi Borobudur berdiri di Kabupaten Magelang. Candi Buddha ini dibangun pada abad ke-8 dan ke-9. Di bagian bawah ada enam teras persegi, lalu di atasnya ada tiga teras bundar. Jadi, Borobudur memiliki **(1)** teras. Di teras bundar berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng. Bangunan berbentuk lonceng yang paling besar ada di puncak candi dan disebut **(2)** induk. Di dalam setiap stupa berlubang duduk sebuah arca Buddha. Jadi, di teras bundar ada **(3)** arca Buddha di dalam stupa. Dinding candi dihiasi 2.672 panel relief, yaitu pahatan gambar. Sebanyak 1.460 panel bercerita, sedangkan 1.212 panel hanya hiasan. Jadi, relief yang bercerita lebih **(4)** daripada relief hiasan. Batu-batu candi bisa rusak bila diinjak dan dipanjat. Oleh karena itu, pengunjung **(5)** memanjat stupa.

Panjang ± 120 kata · Pengecoh bank kata: *enam*, *sedikit*

| Kode | Rumpang | Kunci | Sub | AKM | Bloom | Pengecoh sasaran | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| mgl-2-a01 | (1) | sembilan | D1a | L2 | C2 | *enam* → INF-KAT | Memadukan dua angka; *enam* menangkap siswa yang hanya mencocokkan kata di kalimat sebelumnya |
| mgl-2-a02 | (2) | stupa | D1a | L1 | C2 | — | Istilah didefinisikan di kalimat sebelumnya |
| mgl-2-a03 | (3) | 72 | D1a | L2 | C2 | — | Satu arca di setiap stupa |
| mgl-2-a04 | (4) | banyak | D1a | L2 | C3 | *sedikit* → INF-HUB | Membandingkan 1.460 dan 1.212; mengganti mgl-2-a03 lama ("relief") yang butuh kosakata awal |
| mgl-2-a05 | (5) | dilarang | D1a | L2 | C2 | — | Sebab–akibat |

Fakta relief sudah dibetulkan sesuai 05: 2.672 panel, 1.460 bercerita, 1.212 hiasan.

### Variasi b: Tari Topeng Ireng

> Topeng Ireng adalah tari rakyat dari Kabupaten Magelang. Tarian ini mulai tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur. Penarinya memakai kuluk, yaitu hiasan kepala dari bulu yang berwarna-warni. Saat penari melompat, bulu-bulu pada **(1)** itu ikut bergoyang. Di pergelangan kaki penari terpasang lonceng kecil. Setiap kali kaki dihentakkan, lonceng itu **(2)**. Satu kelompok bisa berisi puluhan penari yang bergerak serempak. Kalau ada satu penari yang bergerak lebih cepat daripada teman-temannya, tarian mereka menjadi tidak **(3)**. Tarian ini lahir di desa lereng gunung yang warganya banyak bertani. Gerakannya yang tegas menggambarkan semangat para **(4)** itu. Dalam pertunjukan di lapangan desa, panitia memasang tali pembatas karena penari melompat ke segala arah. Penonton yang duduk di belakang tali tetap **(5)** dari lompatan penari.

Panjang ± 120 kata · Pengecoh bank kata: *diam*, *nelayan*

| Kode | Rumpang | Kunci | Sub | AKM | Bloom | Pengecoh sasaran | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| mgl-2-b01 | (1) | kuluk | D1a | L1 | C2 | — | Istilah didefinisikan di kalimat sebelumnya |
| mgl-2-b02 | (2) | berbunyi | D1a | L2 | C2 | *diam* → INF-HUB | Sebab–akibat |
| mgl-2-b03 | (3) | kompak | D1a | L2 | C2 | — | Menyimpulkan dari "bergerak serempak" |
| mgl-2-b04 | (4) | petani | D1a | L2 | C2 | *nelayan* → INF-LIT | Dari "warganya banyak bertani" |
| mgl-2-b05 | (5) | aman | D1a | L2 | C3 | — | Sebab–akibat dari tali pembatas; *diam* dan *kompak* tetap bersaing secara bentuk kata. Mengganti mgl-2-b05 lama ("mengganggu") yang terjawab dari norma |

Pertunjukan di lapangan desa adalah contoh situasi, bukan klaim tentang setiap pertunjukan Topeng Ireng.

**Rujukan:** variasi a: UNESCO World Heritage Centre (No. 592); Wikipedia *Borobudur* (jumlah relief, dicek di 05). Variasi b: detikEdu (2022) *Tari Topeng Ireng*; GNFI (2024) (Tuksongo, 1950-an; dicek di 05).

## `mgl-3` Benar atau Salah

**Instruksi:** "Baca unggahan dan komentarnya, lalu pilih BENAR atau SALAH untuk setiap pernyataan."
**Genre:** unggahan akun resmi dengan kolom komentar · 2 paragraf + 3 komentar · 6 pernyataan (3 D1a, 3 D1b) · kunci 3 : 3

### Variasi a: Menjaga Candi Borobudur

> **@pengelola.candi** ✓ akun resmi · 5 Mei
>
> Candi Borobudur adalah candi Buddha terbesar di dunia. Candi ini dibangun pada abad ke-8 dan ke-9 tanpa semen; batu-batunya disusun saling mengunci. Dindingnya dihiasi 2.672 panel relief. Sebanyak 1.460 panel bercerita, sedangkan sisanya hiasan. Pada tahun 1991, UNESCO menetapkan Borobudur sebagai Warisan Dunia.
>
> Mari jaga Borobudur bersama. Ikuti rute dan aturan dari pengelola. Jangan memanjat stupa dan jangan menyentuh relief, sebab minyak dan keringat tangan dapat merusak batu. Jangan mencoret dinding dan jangan membuang sampah sembarangan.
>
> 💬 **Dimas:** Borobudur candi paling indah di dunia! 😍
> 💬 **Bu Rina:** Borobudur ditetapkan sebagai Warisan Dunia pada tahun 1991.
> 💬 **Nanda:** Katanya boleh kok pegang relief, asal pelan-pelan.

Panjang ± 125 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| mgl-3-a01 | Paragraf kedua unggahan terutama menjelaskan cara menjaga Candi Borobudur. | benar | D1a | L2 | C2 | INF-REL |
| mgl-3-a02 | Menurut unggahan, semua relief Borobudur bercerita. | salah | D1a | L2 | C3 | INF-GEN |
| mgl-3-a03 | Pengunjung boleh menyentuh relief asalkan tangannya sudah dicuci bersih. | salah | D1a | L2 | C3 | INF-HUB |
| mgl-3-a04 | Komentar Dimas berisi pendapat. | benar | D1b | L3 | C4 | INF-FOP |
| mgl-3-a05 | Komentar Bu Rina berisi pendapat. | salah | D1b | L3 | C4 | INF-FOP |
| mgl-3-a06 | Tentang boleh tidaknya menyentuh relief, unggahan akun resmi lebih dapat dipercaya daripada komentar Nanda. | benar | D1b | L3 | C4 | INF-SUM |

`mgl-3-a03` menggantikan butir lama "membawa pulang sampah" (terjawab dari norma). Kuncinya *salah* karena larangan di unggahan berlaku tanpa syarat ("jangan menyentuh relief"), dan alasannya minyak serta keringat tangan, bukan kotoran. Siswa yang menambahkan syarat sendiri terjebak.

### Variasi b: Gunung Tidar

> **@infowisata.kotamagelang** ✓ akun resmi · 11 April
>
> Di tengah Kota Magelang ada bukit hijau bernama Gunung Tidar. Tingginya sekitar 503 meter di atas permukaan laut. Banyak orang mendaki tangganya untuk berolahraga. Pendaki diminta tetap berjalan di jalur tangga. Dalam cerita rakyat, Gunung Tidar disebut paku tanah Jawa. Konon, Pulau Jawa dulu bergoyang-goyang, lalu dipaku di Gunung Tidar agar tenang. Cerita seperti ini disebut legenda, bukan penjelasan ilmiah.
>
> Di lembah Gunung Tidar berdiri Akademi Militer sejak tahun 1957. Kota Magelang berulang tahun setiap tanggal 11 April. Tanggal itu diambil dari Prasasti Mantyasih yang dibuat pada tahun 907 Masehi.
>
> 💬 **Kak Dewi:** Pemandangan dari Gunung Tidar paling indah saat pagi! 🌄
> 💬 **Pak Anton:** Prasasti Mantyasih dibuat pada tahun 907 Masehi.
> 💬 **Raffi:** Katanya boleh lewat jalan pintas di hutan biar cepat sampai atas.

Panjang ± 125 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| mgl-3-b01 | Paragraf pertama unggahan terutama menceritakan Gunung Tidar. | benar | D1a | L2 | C2 | INF-REL |
| mgl-3-b02 | Prasasti Mantyasih dibuat sesudah Akademi Militer berdiri. | salah | D1a | L2 | C4 | INF-HUB |
| mgl-3-b03 | Menurut unggahan, Pulau Jawa benar-benar pernah bergoyang lalu dipaku di Gunung Tidar. | salah | D1a | L2 | C2 | INF-LIT |
| mgl-3-b04 | Komentar Kak Dewi berisi fakta. | salah | D1b | L3 | C4 | INF-FOP |
| mgl-3-b05 | Komentar Pak Anton berisi fakta. | benar | D1b | L3 | C4 | INF-FOP |
| mgl-3-b06 | Tentang jalur mendaki Gunung Tidar, unggahan akun resmi lebih dapat dipercaya daripada komentar Raffi. | benar | D1b | L3 | C4 | INF-SUM |

Kunci `mgl-3-b01` dirumuskan ulang sesuai 05 ("terutama menceritakan Gunung Tidar", tanpa "dan legendanya"). Pernyataan "berulang tahun 11 April = fakta" yang terlalu mudah diganti dengan komentar berisi fakta.

Kedua variasi punya struktur yang sama: gagasan pokok (benar), memadukan informasi (salah), satu butir aturan atau legenda (salah), komentar pendapat, komentar fakta, dan perbandingan sumber (benar). Urutan kunci: a = B S S B S B, b = B S S S B B.

**Rujukan:** variasi a: UNESCO World Heritage Centre (No. 592); Wikipedia *Borobudur*; Kompas Travel (2025) (aturan pengunjung). Variasi b: Tirto.id (*Sejarah Gunung Tidar*); Kompas Regional (2024); Pemkot Magelang (*Prasasti Mantyasih*). Kedua akun fiktif.

### Kartu jangkar Magelang (sama di variasi a dan b)

> 🔍 **asal tari Topeng Ireng**
>
> **[Iklan]** Sewa Kostum Tari Murah! · `kostum-tari.example`
> Kostum Topeng Ireng lengkap mulai Rp50.000. Pesan sekarang!
>
> **Catatan Jalan-jalan Rani** · `rani-jalan.example`
> Aku nonton Topeng Ireng kemarin, seru banget! Katanya tarian ini dari Bali, ya?
>
> **Ensiklopedia Budaya Jawa Tengah** · `budaya-jateng.example`
> Topeng Ireng adalah tari rakyat yang tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur, Magelang.

Panjang ± 55 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| mgl-3-x01 | Dari tiga hasil pencarian itu, hanya satu yang menjawab dengan jelas dari mana Topeng Ireng berasal. | benar | D1a | L2 | C3 | INF-REL |
| mgl-3-x02 | Hasil pertama paling dapat dipercaya karena letaknya paling atas. | salah | D1b | L3 | C4 | INF-SUM |

## `mgl-4` Cari Objek

### Babak 1: Temukan Benda (PBK)

Diambil dari bank lama tanpa perubahan isi: adegan *Galeri Budaya Magelang*.

| Kode | Petunjuk | Kunci |
| --- | --- | --- |
| mgl-4-x01 | Temukan bangunan kecil berbentuk lonceng yang berlubang-lubang. Di teras bundar Borobudur ada 72 bangunan seperti ini. | stupa berlubang |
| mgl-4-x02 | Temukan hiasan kepala dari bulu warna-warni yang dipakai penari tari rakyat dari Desa Tuksongo, Magelang. | kuluk Topeng Ireng |
| mgl-4-x03 | Temukan jajanan dari singkong yang dikukus, ditumbuk, lalu diberi gula. Jajanan ini membuat Magelang punya julukan khusus. | getuk |
| mgl-4-x04 | Temukan benda dari tanah liat yang dibentuk lalu dibakar, buatan warga Dusun Klipoh. | kendil gerabah |

Pengecoh: kuda kepang bambu, manisan carica, dadak merak Reog, angklung, bakpia, panci logam, semuanya **INF-BUD**.

### Babak 2: Periksa Unggahan (D3)

**Instruksi:** "Ini unggahan tentang budaya Magelang. Baca petunjuknya dengan teliti, lalu ketuk bagian unggahan yang tepat."
**Tingkat:** tanda keliru samar · 10 bagian ketuk · 3 pengecoh utama per petunjuk

#### Variasi a: Unggahan video Topeng Ireng

| No | Bagian ketuk | Isi |
| --- | --- | --- |
| C1 | Nama akun pengunggah | `@vlog.arka` |
| C2 | Tulisan kecil di pojok video | "Video: Sanggar Cahya Kuluk" |
| C3 | Keterangan unggahan | "Tari Topeng Ireng, kesenian khas Temanggung! 🔥" |
| C4 | Kartu informasi | "Topeng Ireng: tari rakyat yang tumbuh sekitar tahun 1950-an di Desa Tuksongo, Kecamatan Borobudur, Magelang." |
| C5 | Jumlah suka | "540 suka" |
| C6 | Komentar 1 | **Salsa**: "Bukan dari Temanggung, Kak. Di kartu info tertulis dari Tuksongo, Magelang 🙏" |
| C7 | Komentar 2 | **Bayu**: "Haha, kostumnya kayak ayam jago 🐔" |
| C8 | Komentar 3 | **Lina**: "Keren! Temanggung memang hebat 👍" |
| C9 | Tombol | "Unduh" |
| C10 | Tombol | "Kirim pesan ke sanggar" |

| Kode | Jenis | Petunjuk | Kunci | Pengecoh utama | Bloom |
| --- | --- | --- | --- | --- | --- |
| mgl-4-a01 | Atribusi | Ketuk tulisan yang menunjukkan siapa pembuat video ini. | C2 | C1 → BUD-ATR; C3 → BUD-ATR; C4 → BUD-ATR | C3 |
| mgl-4-a02 | Representasi | Bandingkan keterangan video dengan kartu informasi. Ketuk komentar yang membetulkan keterangan itu dengan benar dan sopan. | C6 | C8 → BUD-REP; C3 → BUD-REP; C7 → BUD-ETK | C4 |
| mgl-4-a03 | Izin | Kamu ingin memakai video ini untuk tugas kelompok. Ketuk langkah pertama yang tepat. | C10 | C9 → BUD-IZN; C1 → BUD-IZN; C5 → BUD-IZN | C3 |
| mgl-4-a04 | Menghargai tradisi | Ketuk komentar yang kurang menghargai para penari. | C7 | C8 → BUD-ETK; C6 → BUD-ETK; C3 → BUD-REP | C4 |

#### Variasi b: Unggahan foto gerabah Klipoh

| No | Bagian ketuk | Isi |
| --- | --- | --- |
| D1 | Nama akun pengunggah | `@dapur.nayla` |
| D2 | Tulisan kecil di pojok foto | "Foto: Rumah Gerabah Sekar Lempung" |
| D3 | Keterangan unggahan | "Gerabah cantik dari Dusun Klipoh, Wonosobo! 🏺" |
| D4 | Kartu informasi | "Dusun Klipoh berada sekitar 3 km dari Candi Borobudur, Magelang. Warganya membuat kendil, cobek, dan kuali dari tanah liat." |
| D5 | Jumlah suka | "610 suka" |
| D6 | Komentar 1 | **Tegar**: "Klipoh itu di Magelang, Kak, dekat Borobudur. Ada di kartu info 🙏" |
| D7 | Komentar 2 | **Rani**: "Wkwk kendil jadul, siapa yang masih pakai 😂" |
| D8 | Komentar 3 | **Ayu**: "Cantik! Wonosobo memang keren 👍" |
| D9 | Tombol | "Unduh" |
| D10 | Tombol | "Kirim pesan ke rumah gerabah" |

| Kode | Jenis | Petunjuk | Kunci | Pengecoh utama | Bloom |
| --- | --- | --- | --- | --- | --- |
| mgl-4-b01 | Atribusi | Ketuk tulisan yang menunjukkan siapa pembuat foto ini. | D2 | D1 → BUD-ATR; D3 → BUD-ATR; D4 → BUD-ATR | C3 |
| mgl-4-b02 | Representasi | Bandingkan keterangan foto dengan kartu informasi. Ketuk komentar yang membetulkan keterangan itu dengan benar dan sopan. | D6 | D8 → BUD-REP; D3 → BUD-REP; D7 → BUD-ETK | C4 |
| mgl-4-b03 | Izin | Kamu ingin memakai foto ini untuk tugas kelompok. Ketuk langkah pertama yang tepat. | D10 | D9 → BUD-IZN; D1 → BUD-IZN; D5 → BUD-IZN | C3 |
| mgl-4-b04 | Menghargai tradisi | Ketuk komentar yang kurang menghargai kerajinan ini. | D7 | D8 → BUD-ETK; D6 → BUD-ETK; D3 → BUD-REP | C4 |

**Rujukan:** Topeng Ireng dari Tuksongo: GNFI (2024), dicek di 05. Dusun Klipoh dekat Borobudur dan hasil gerabahnya: Kompas Travel (2022) *Desa Klipoh*; Badan Otorita Borobudur. Akun, sanggar, dan rumah gerabah fiktif.

## `mgl-5` Skenario Digital

**Tingkat:** tanda bahaya samar · 4 skenario per variasi · 4 tindakan (1 tepat, 1 kurang tepat, 2 berisiko) · 4 alasan

### Variasi a

**mgl-5-a01 · Data pribadi.** Kamu mencoba kuis "Kamu Mirip Tokoh Wayang Siapa?" di sebuah aplikasi. Sebelum hasilnya keluar, aplikasi meminta tanggal lahir, nama lengkap ibumu, dan nama sekolahmu.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Isi semuanya supaya hasil kuisnya cepat keluar | 0 | DIG-PRI | Tanggal lahir dan nama ibu sering dipakai untuk membuka akun. Data itu bisa disalahgunakan. |
| Isi tanggal lahir saja, yang lain dikosongkan | 0 | DIG-PRI | Tanggal lahir juga data pribadi. Kuis untuk hiburan tidak perlu data seperti itu. |
| Berhenti, lalu tanya orang tua apakah aman | 2 | — | Tepat. Kuis yang meminta data pribadi patut dicurigai, dan orang tua bisa membantu memeriksanya. |
| Berhenti main tanpa cerita ke siapa pun | 1 | DIG-PSF | Datamu aman. Cerita ke orang tua akan membantu kalau aplikasi itu nanti muncul lagi. |

| Alasan | Kode |
| --- | --- |
| Data itu bisa dipakai orang untuk membobol akun | R-RISK |
| Ibu melarangku bermain kuis di aplikasi apa pun | R-ATUR |
| Teman-temanku sudah membagikan hasil kuisnya | R-SOS |
| Aku memang tidak terlalu suka cerita wayang | R-ACAK |

**mgl-5-a02 · Kata sandi.** Sahabatmu, Laras, berkata, "Pinjam akun game-mu, dong. Kasih tahu kata sandinya, aku cuma mau lihat-lihat."

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Beri tahu kata sandinya karena Laras sahabatmu | 0 | DIG-SAN | Walaupun sahabat, kata sandi tetap untuk pemilik akun. Kata sandi yang sudah dibagikan bisa tersebar tanpa sengaja. |
| Beri tahu sekarang, nanti kata sandinya diganti | 0 | DIG-SAN | Selama belum diganti, akunmu bisa dipakai orang lain. Lebih aman kalau tidak dibagikan sama sekali. |
| Tolak dengan ramah, lalu ajak main bersama | 2 | — | Tepat. Akunmu aman dan persahabatan kalian tetap terjaga. |
| Tolak tanpa menjelaskan apa pun ke Laras | 1 | DIG-PSF | Menolak memberi kata sandi itu benar. Menjelaskan alasannya dengan ramah membuat Laras mengerti dan tidak salah paham. |

| Alasan | Kode |
| --- | --- |
| Kata sandi bisa tersebar walau tidak sengaja | R-RISK |
| Kata guru, kata sandi itu harus dirahasiakan | R-ATUR |
| Nanti Laras marah kalau tidak kuberi tahu | R-SOS |
| Level akunku memang masih rendah sekali | R-ACAK |

**mgl-5-a03 · Izin membagikan foto teman.** Saat lomba tari di sekolah, kamu memotret Dimas yang terpeleset dan jatuh terduduk di panggung. Fotonya lucu. Kamu ingin mengirimnya ke grup kelas.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Kirim ke grup kelas supaya semua ikut tertawa | 0 | DIG-IZN | Foto itu tentang Dimas. Membagikannya tanpa izin bisa membuatnya malu. |
| Tunjukkan ke teman-teman sambil tertawa | 0 | DIG-ETK | Menertawakan teman yang jatuh bisa menyakiti hatinya, walaupun fotonya tidak dikirim. |
| Tanya Dimas dulu, boleh dibagikan atau tidak | 2 | — | Tepat. Foto orang lain hanya dibagikan kalau orangnya setuju. |
| Simpan fotonya di HP tanpa bertanya ke Dimas | 1 | DIG-PSF | Kamu tidak membagikannya, bagus. Lebih baik lagi kalau kamu bertanya ke Dimas, karena itu fotonya. |

| Alasan | Kode |
| --- | --- |
| Dimas mungkin malu kalau fotonya disebar | R-ETIK |
| Di sekolah ada aturan tidak boleh memotret | R-ATUR |
| Teman-teman pasti suka melihat foto lucu | R-SOS |
| Memori HP-ku sebentar lagi hampir penuh | R-ACAK |

**mgl-5-a04 · Pesan berantai.** Di grup kelas, seorang teman meneruskan pesan: "Info dari teman ibuku: tiket masuk Candi Borobudur gratis untuk anak SD selama bulan ini! Sebarkan!" Pesan itu tidak menyebut lembaga atau sumber resmi.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Teruskan ke grup keluarga supaya semua kebagian | 0 | DIG-HOX | Kabar ini belum jelas sumbernya. Kalau ternyata tidak benar, banyak keluarga bisa kecewa. |
| Ajak teman-teman langsung berangkat besok | 0 | DIG-HOX | Sebaiknya kabar dicek dulu sebelum dipakai untuk membuat rencana. |
| Cek dulu di situs resmi candi bersama orang tua | 2 | — | Tepat. Sumber resmi bisa memastikan benar atau tidaknya kabar itu. |
| Tidak meneruskan dan tidak menanggapi apa pun | 1 | DIG-PSF | Kamu tidak ikut menyebarkan, bagus. Mengecek ke sumber resmi akan membantu teman-temanmu juga. |

| Alasan | Kode |
| --- | --- |
| Kabar tanpa sumber jelas belum tentu benar | R-RISK |
| Ayah melarangku membuka pesan di grup kelas | R-ATUR |
| Biar aku dianggap tahu banyak informasi | R-SOS |
| Aku sudah pernah berkunjung ke Borobudur | R-ACAK |

### Variasi b

**mgl-5-b01 · Data pribadi.** Ada lomba video "Jajanan Khas Magelang". Peserta diminta menyebut nama lengkap dan alamat rumah di awal video. Semua video akan diunggah ke akun yang bisa dilihat siapa saja.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Sebut nama lengkap dan alamat rumah sesuai aturan | 0 | DIG-PRI | Video di akun umum bisa ditonton siapa saja. Alamat rumah sebaiknya tidak disebutkan. |
| Sebut nama lengkap dan nama sekolah saja | 0 | DIG-PRI | Nama lengkap dan sekolah juga bisa dipakai orang asing untuk menemukanmu. |
| Tanya orang tua; sebut nama panggilan saja | 2 | — | Tepat. Kamu tetap bisa ikut lomba tanpa membuka data pribadi. |
| Tidak jadi ikut tanpa cerita ke siapa pun | 1 | DIG-PSF | Datamu aman. Orang tua mungkin bisa membantumu ikut lomba dengan cara yang aman. |

| Alasan | Kode |
| --- | --- |
| Orang asing bisa tahu alamat rumahku | R-RISK |
| Pokoknya tidak boleh ikut lomba di internet | R-ATUR |
| Peserta lain juga menyebut alamat rumahnya | R-SOS |
| Aku lebih suka getuk daripada jajanan lain | R-ACAK |

**mgl-5-b02 · Kata sandi.** Teman sekelompokmu, Arif, berkata, "Kasih tahu kata sandi akun belajarmu, ya. Biar aku yang mengerjakan tugas kelompok dari rumahku."

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Beri tahu kata sandinya supaya tugas cepat selesai | 0 | DIG-SAN | Akun belajarmu berisi data dan nilaimu. Kata sandi tetap untuk pemilik akun. |
| Beri tahu sekarang, nanti kata sandinya diganti | 0 | DIG-SAN | Selama belum diganti, akunmu bisa dipakai orang lain. Lebih aman tidak dibagikan. |
| Tolak dengan ramah, lalu ajak kerja bersama | 2 | — | Tepat. Akunmu aman dan tugas kelompok tetap jalan. |
| Tolak tanpa menjelaskan apa pun ke Arif | 1 | DIG-PSF | Menolak memberi kata sandi itu benar. Menjelaskan alasannya dengan ramah membuat Arif mengerti dan tugas tetap jalan. |

| Alasan | Kode |
| --- | --- |
| Kata sandi bisa tersebar walau tidak sengaja | R-RISK |
| Kata guru, kata sandi itu harus dirahasiakan | R-ATUR |
| Nanti Arif tidak mau berteman lagi denganku | R-SOS |
| Tugas kelompok kali ini memang sulit | R-ACAK |

**mgl-5-b03 · Izin membagikan foto teman.** Kamu memotret teman-teman saat latihan Topeng Ireng. Di foto itu, Sekar belum memakai kuluk dan rambutnya berantakan. Kamu ingin mengunggah foto itu ke akun kelas.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Unggah ke akun kelas karena fotonya ramai | 0 | DIG-IZN | Sekar ada di foto itu. Sebelum diunggah, ia perlu ditanya dulu. |
| Kirim ke beberapa teman sambil mengejek rambut Sekar | 0 | DIG-ETK | Mengejek penampilan teman bisa menyakiti hatinya. |
| Tanya Sekar dulu, atau pilih foto yang lain | 2 | — | Tepat. Setiap orang di foto berhak setuju atau tidak. |
| Simpan fotonya di HP tanpa bertanya ke Sekar | 1 | DIG-PSF | Kamu tidak membagikannya, bagus. Bertanya ke Sekar akan lebih baik. |

| Alasan | Kode |
| --- | --- |
| Sekar mungkin malu kalau fotonya diunggah | R-ETIK |
| Di sekolah ada aturan tidak boleh mengunggah | R-ATUR |
| Akun kelas jadi ramai kalau ada foto lucu | R-SOS |
| Kamera HP-ku baru dan hasilnya bagus | R-ACAK |

**mgl-5-b04 · Pesan berantai.** Di grup kelas ada pesan: "Info dari teman pamanku: getuk yang dijual di pasar minggu ini bikin sakit perut! Jangan beli, sebarkan!" Pesan itu tidak menyebut sumber atau bukti.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Teruskan ke grup keluarga supaya semua waspada | 0 | DIG-HOX | Kabar tanpa bukti bisa merugikan para penjual getuk yang tidak bersalah. |
| Unggah tulisan "Jangan beli getuk!" di media sosial | 0 | DIG-HOX | Sebelum menyebarkan peringatan, kabarnya perlu dicek dulu. |
| Cek dulu di berita tepercaya bersama orang tua | 2 | — | Tepat. Mengecek ke sumber yang jelas mencegah kabar bohong menyebar. |
| Tidak meneruskan dan tidak menanggapi apa pun | 1 | DIG-PSF | Kamu tidak ikut menyebarkan, bagus. Mengecek dan memberi tahu teman akan lebih membantu. |

| Alasan | Kode |
| --- | --- |
| Kabar tanpa bukti bisa merugikan penjual | R-RISK |
| Ibu melarangku membuka pesan di grup kelas | R-ATUR |
| Biar aku yang pertama memberi tahu teman | R-SOS |
| Aku jarang sekali pergi ke pasar | R-ACAK |

---

# Wonosobo (sulit)

Level utama: evaluasi dan refleksi (L3) untuk Benar atau Salah, kartu jangkar, dan Periksa Unggahan; Isi Rumpang L2 · Bloom C3–C5 · Skenario: situasi berkembang dua langkah

## `wnb-2` Isi Rumpang

**Instruksi:** "Baca artikelnya. Ketuk kotak kosong, lalu ketuk kata yang tepat. Ada dua kata yang tidak terpakai."
**Genre:** artikel situs wisata (judul, penulis, tanggal) · 6 rumpang · bank kata 8 (6 jawaban + 2 pengecoh)

### Variasi a: Negeri di Atas Awan

> 🌐 `dieng-wisata.example` › Artikel
> **Negeri di Atas Awan**
> Ditulis oleh Tim Redaksi · 3 Agustus
>
> Sebagian Dataran Tinggi Dieng berada di Kabupaten Wonosobo. Dataran tinggi ini terletak lebih dari 2.000 meter di atas permukaan laut. Dieng sering tertutup kabut **(1)** dijuluki negeri di atas awan. Pada musim kemarau, embun pagi bisa membeku menjadi es **(2)** suhunya turun di bawah 0 °C. Wisatawan yang datang pada musim itu sebaiknya membawa pakaian hangat.
>
> Dieng juga cocok untuk carica, yaitu pepaya gunung. Carica tumbuh baik di ketinggian 1.400–2.400 meter, **(3)** sulit tumbuh di dataran rendah yang panas. Desa Sembungan berada di ketinggian sekitar 2.300 meter, jadi carica **(4)** tumbuh di desa itu. Daerah pantai termasuk dataran rendah yang panas, jadi carica **(5)** tumbuh di sana. Dari semua keterangan itu, kita tahu bahwa carica membutuhkan udara yang **(6)**.

Panjang ± 125 kata · Pengecoh bank kata: *agar*, *panas* · Kode khusus: *agar* di rumpang (2) dan (3) → INF-HUB

| Kode | Rumpang | Kunci | Sub | AKM | Bloom | Pengecoh sasaran | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| wnb-2-a01 | (1) | sehingga | D1a | L2 | C3 | *agar* → INF-HUB | Akibat, bukan tujuan |
| wnb-2-a02 | (2) | karena | D1a | L2 | C3 | — | Sebab |
| wnb-2-a03 | (3) | tetapi | D1a | L2 | C3 | — | Pertentangan |
| wnb-2-a04 | (4) | dapat | D1a | L2 | C4 | — | Membandingkan 2.300 dengan rentang 1.400–2.400 |
| wnb-2-a05 | (5) | sulit | D1a | L2 | C4 | — | Menerapkan keterangan dataran rendah |
| wnb-2-a06 | (6) | sejuk | D1a | L2 | C4 | *panas* → INF-LIT | Simpulan dari seluruh teks |

Rumpang "hangat" di bank lama tidak dipakai (7 → 6 rumpang); kalimatnya tetap ada sebagai isi bacaan.

### Variasi b: Embun Upas

> 🌐 `jelajah-dieng.example` › Cerita Pemandu
> **Embun Upas di Dieng**
> Ditulis oleh Kak Laras, pemandu wisata · 20 Juli
>
> Pada musim kemarau, langit Dataran Tinggi Dieng sering sangat cerah. Pada malam hari, panas bumi cepat lepas ke angkasa **(1)** tidak ada awan yang menahannya. Udara dingin **(2)** turun dan terkumpul di cekungan dataran tinggi. Suhu di dekat tanah bisa turun di bawah 0 °C, **(3)** embun pagi membeku menjadi es tipis. Warga Dieng menyebut es tipis itu embun upas.
>
> Bagi wisatawan, embun upas tampak indah, **(4)** bagi petani es itu bisa merusak daun kentang. Wisatawan yang ingin melihat embun upas harus datang pada **(5)** hari, karena es itu cepat mencair saat matahari mulai terik. Dari semua keterangan itu, embun upas paling mudah terbentuk setelah malam yang **(6)**.

Panjang ± 120 kata · Pengecoh bank kata: *siang*, *berawan*

| Kode | Rumpang | Kunci | Sub | AKM | Bloom | Pengecoh sasaran | Catatan |
| --- | --- | --- | --- | --- | --- | --- | --- |
| wnb-2-b01 | (1) | karena | D1a | L2 | C3 | — | Sebab |
| wnb-2-b02 | (2) | lalu | D1a | L2 | C3 | — | Urutan |
| wnb-2-b03 | (3) | sehingga | D1a | L2 | C3 | — | Akibat. Mengganti rumpang "membeku", satu-satunya kata kerja di bank kata, yang bisa diisi tanpa membaca |
| wnb-2-b04 | (4) | tetapi | D1a | L2 | C3 | — | Pertentangan |
| wnb-2-b05 | (5) | pagi | D1a | L2 | C4 | *siang* → INF-LIT | Dari "cepat mencair saat matahari mulai terik" |
| wnb-2-b06 | (6) | cerah | D1a | L2 | C4 | *berawan* → INF-HUB | Simpulan sebab–akibat dari paragraf pertama |

Rumpang "layu" di bank lama tidak dipakai, karena sebagian bergantung pada pengetahuan tanaman (05).

Kedua variasi setara dalam level dan sub-aspek. Variasi a punya tiga kata penghubung dan tiga simpulan; variasi b punya empat kata penghubung (sebab, urutan, akibat, pertentangan) dan dua simpulan.

**Rujukan:** variasi a: Wikipedia *Dataran Tinggi Dieng*; Tempo dan Kompas Travel (Desa Sembungan). Variasi b: BMKG melalui Kompas.com (Juli 2026) dan detikEdu (*embun upas*). Situs dan penulis fiktif.

## `wnb-3` Benar atau Salah

**Instruksi:** "Baca kedua kabar dan ceritanya, lalu pilih BENAR atau SALAH untuk setiap pernyataan."
**Genre:** dua sumber digital yang bertentangan (pesan berantai di grup dan unggahan akun resmi) · 3 bagian · 6 pernyataan (2 D1a, 4 D1b) · kunci 3 : 3

### Variasi a: Dua Kabar tentang Telaga Warna

> 💬 **Grup Keluarga Besar Raka** · ↪ *Diteruskan berkali-kali*
> "Minum air Telaga Warna bikin awet muda! Sebarkan ke semua grup!"
> *(tanpa nama pembuat dan tanggal)*
>
> 📢 **@pengelola.telagawarna** ✓ akun resmi · 2 Agustus
> "Air Telaga Warna mengandung belerang, sehingga warnanya bisa tampak hijau, biru, atau kekuningan. Air telaga tidak untuk diminum. Bawalah air minum sendiri saat berkunjung."
>
> Raka ingin tahu kabar mana yang dapat dipercaya. Ia memeriksa siapa pembuat setiap kabar, lalu bertanya kepada gurunya. Gurunya menjelaskan bahwa air yang mengandung belerang tidak aman untuk diminum. Dito, sepupu Raka, juga hanya membaca pesan berantai itu. Ia berkata, "Kayaknya benar, deh."

Panjang ± 95 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| wnb-3-a01 | Kedua kabar sama-sama membicarakan air Telaga Warna, tetapi isinya bertentangan. | benar | D1a | L2 | C4 | INF-LIT |
| wnb-3-a02 | Pesan berantai itu pasti benar karena sudah diteruskan berkali-kali. | salah | D1b | L3 | C5 | INF-SUM |
| wnb-3-a03 | Hanya satu dari dua kabar itu yang menyarankan orang meminum air telaga. | benar | D1a | L2 | C4 | INF-LIT |
| wnb-3-a04 | Unggahan pengelola lebih dapat dipercaya daripada pesan berantai karena pembuat dan tanggalnya jelas. | benar | D1b | L3 | C5 | INF-SUM |
| wnb-3-a05 | Bertanya kepada Dito sama membantunya dengan bertanya kepada guru. | salah | D1b | L3 | C5 | INF-SUM |
| wnb-3-a06 | Berdasarkan bukti dalam bacaan, pengunjung yang haus boleh meminum air Telaga Warna asalkan airnya tampak jernih. | salah | D1b | L3 | C5 | INF-GEN |

`wnb-3-a05` dirumuskan ulang sesuai 05: yang dibandingkan sekarang guru dengan sepupu yang **juga hanya membaca pesan berantai itu**, jadi kuncinya tidak bisa diperdebatkan.

### Variasi b: Kabar tentang Kawah

> Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api. Karena itu, di sana ada beberapa kawah. Di sekitar kawah dapat keluar gas yang berbahaya bagi kesehatan. Petugas memasang rambu dan pagar pembatas agar pengunjung tetap berada pada jarak aman.
>
> 💬 **Grup Wisata Keluarga Dinda** · ↪ *Diteruskan berkali-kali*
> "Kawah aman kok, dekati saja biar fotonya bagus!"
> *(tanpa nama pembuat)*
>
> 📢 **Akun resmi badan pemantau gunung api** ✓ · 2 Agustus
> "Pengunjung kawah di Dieng harus mengikuti rambu dan menjaga jarak aman dari kawah."
>
> Keluarga Dinda akhirnya tetap berwisata, tetapi mereka berdiri di belakang pagar pembatas saat berfoto.

Panjang ± 90 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| wnb-3-b01 | Pesan berantai dan pengumuman resmi memberi saran yang berbeda tentang mendekati kawah. | benar | D1a | L2 | C4 | INF-LIT |
| wnb-3-b02 | Menurut bacaan, gas di sekitar kawah aman bagi kesehatan. | salah | D1a | L2 | C4 | INF-KAT |
| wnb-3-b03 | Pesan berantai itu dapat dipercaya karena sudah banyak beredar. | salah | D1b | L3 | C5 | INF-SUM |
| wnb-3-b04 | Pengumuman resmi dapat dipercaya karena lembaga pembuatnya jelas. | benar | D1b | L3 | C5 | INF-SUM |
| wnb-3-b05 | Rambu dan pagar dari petugas lebih pantas diikuti daripada pesan berantai, karena petugas mengetahui bahaya gas di kawah. | benar | D1b | L3 | C5 | INF-SUM |
| wnb-3-b06 | Berdasarkan bukti dalam bacaan, keluarga Dinda harus membatalkan wisata karena pengumuman resmi melarang pengunjung datang ke kawah. | salah | D1b | L3 | C5 | INF-GEN |

Variasi `b` lama (Bukit Sikunir, sumber lisan) tidak dipakai; dua variasi yang tersisa sama-sama memakai dua sumber digital (05, prioritas tinggi). Urutan kunci: a = B S B B S S, b = B S S B B S.

**Rujukan:** Telaga Warna dan belerang: Indonesia Travel, Suara.com (dicek di 01); kawah dan gas di Dieng: laman PVMBG/MAGMA Indonesia; panduan cek fakta Siberkreasi dan Mafindo. Akun pengelola fiktif; lembaga pemantau gunung api ditulis tanpa nama dan logo.

### Kartu jangkar Wonosobo (sama di variasi a dan b)

> **@carica.mantap** · *Bersponsor*
> "Manisan carica TERENAK se-Dieng! Dijamin bikin pintar dan tidak pernah sakit! Pesan sekarang, diskon 50% hari ini saja!"
> ❤️ 2.300 suka

Panjang ± 25 kata

| Kode | Pernyataan | Kunci | Sub | AKM | Bloom | Kode salah |
| --- | --- | --- | --- | --- | --- | --- |
| wnb-3-x01 | Unggahan ini adalah iklan, terlihat dari tanda "Bersponsor". | benar | D1b | L3 | C4 | INF-SUM |
| wnb-3-x02 | Karena disukai 2.300 orang, isi unggahan itu pasti benar. | salah | D1b | L3 | C5 | INF-SUM |

## `wnb-4` Cari Objek

### Babak 1: Temukan Benda (PBK)

Diambil dari bank lama tanpa perubahan isi: adegan *Pasar Oleh-oleh Dieng*.

| Kode | Petunjuk | Kunci |
| --- | --- | --- |
| wnb-4-x01 | Temukan alat musik berbentuk tudung lengkung dari anyaman bambu. Dulu tudung itu melindungi penggembala bebek dari panas dan hujan, lalu diberi senar untuk dipetik. | bundengan |
| wnb-4-x02 | Temukan buah pegunungan yang mirip pepaya, tetapi ukurannya kecil. Buah ini tumbuh baik di udara sejuk dan sering dibuat manisan. | carica |
| wnb-4-x03 | Temukan keranjang bambu kecil yang dicelup-celupkan ke air mendidih. Nama alat ini dipakai untuk menamai mi khas Wonosobo. | ongklok |
| wnb-4-x04 | Temukan daun yang diseduh menjadi minuman hangat. Daun ini dipetik di perkebunan Tambi yang sudah ada sejak tahun 1865. | daun teh |

Pengecoh: pepaya biasa, rigen, semangkuk mi ongklok, angklung, sasando, biji kopi, kuluk Topeng Ireng, getuk, semuanya **INF-BUD**. Fakta "Tambi sejak 1865" tetap perlu dicek ahli (05).

### Babak 2: Periksa Unggahan (D3)

**Instruksi:** "Ini unggahan tentang budaya Wonosobo. Baca petunjuknya dengan teliti, lalu ketuk bagian unggahan yang tepat."
**Tingkat:** nilai yang bertentangan (keliru tapi populer; benar tapi kasar) · 12 bagian ketuk · 3 pengecoh utama per petunjuk

#### Variasi a: Unggahan video bundengan

| No | Bagian ketuk | Isi |
| --- | --- | --- |
| E1 | Nama akun pengunggah | `@jelajah.awan` |
| E2 | Tulisan kecil di pojok video | "Video: Sanggar Sekar Kowangan" |
| E3 | Keterangan unggahan | "Angklung khas Jawa Barat, keren banget! 🎶 Video bikinan aku sendiri, lho." |
| E4 | Papan informasi Rumah Budaya | "Bundengan adalah alat musik dari Wonosobo. Bentuknya berasal dari kowangan, tudung bambu para penggembala bebek." |
| E5 | Jumlah suka | "8.000 suka" |
| E6 | Komentar 1 | **Wulan**: "Itu bundengan dari Wonosobo, Kak. Ada di papan Rumah Budaya 🙏" |
| E7 | Komentar 2 | **Galih**: "SALAH! Gitu aja nggak tahu, malu-maluin! 😡" |
| E8 | Komentar 3 | **Putra**: "Wkwk sama aja lah, sama-sama dari bambu." |
| E9 | Komentar 4 | **Intan**: "Aku juga kira angklung 😅" |
| E10 | Tombol | "Bagikan" |
| E11 | Tombol | "Unduh" |
| E12 | Tombol | "Kirim pesan ke sanggar" |

| Kode | Jenis | Petunjuk | Kunci | Pengecoh utama | Bloom |
| --- | --- | --- | --- | --- | --- |
| wnb-4-a01 | Atribusi | Pengunggah menulis bahwa ia membuat video ini sendiri. Ketuk tanda yang menunjukkan siapa pembuat video yang sebenarnya. | E2 | E1 → BUD-ATR; E3 → BUD-ATR; E4 → BUD-ATR | C4 |
| wnb-4-a02 | Representasi | Unggahan ini disukai 8.000 orang, tapi ada yang keliru. Bandingkan dengan papan informasi, lalu ketuk bagian yang paling perlu dibetulkan supaya orang lain tidak ikut salah paham. | E3 | E5 → BUD-REP; E8 → BUD-REP; E9 → BUD-REP | C5 |
| wnb-4-a03 | Izin | Kamu ingin memakai video ini untuk tugas sekolah yang dikumpulkan besok. Ketuk langkah pertama yang tepat. | E12 | E11 → BUD-IZN; E10 → BUD-IZN; E1 → BUD-IZN | C4 |
| wnb-4-a04 | Menghargai tradisi | Ketuk komentar yang membetulkan keterangan dengan benar, sopan, dan menyebut sumbernya. | E6 | E7 → BUD-ETK; E8 → BUD-REP; E9 → BUD-REP | C5 |

#### Variasi b: Unggahan video mi ongklok

| No | Bagian ketuk | Isi |
| --- | --- | --- |
| F1 | Nama akun pengunggah | `@makan.terus` |
| F2 | Tulisan kecil di pojok video | "Video: Warung Kabut Pagi" |
| F3 | Keterangan unggahan | "Mi ongklok, kuliner khas Magelang! 😋 Video bikinan aku sendiri, lho." |
| F4 | Papan informasi Rumah Budaya | "Mi ongklok adalah makanan khas Wonosobo. Namanya berasal dari ongklok, keranjang bambu kecil untuk merebus mi." |
| F5 | Jumlah suka | "7.500 suka" |
| F6 | Komentar 1 | **Dewi**: "Mi ongklok itu dari Wonosobo, Kak. Ada di papan Rumah Budaya 🙏" |
| F7 | Komentar 2 | **Aldi**: "NGACO! Makanya belajar dulu! 😡" |
| F8 | Komentar 3 | **Yoga**: "Wkwk sama aja lah, sama-sama mi." |
| F9 | Komentar 4 | **Nisa**: "Aku juga kira dari Magelang 😅" |
| F10 | Tombol | "Bagikan" |
| F11 | Tombol | "Unduh" |
| F12 | Tombol | "Kirim pesan ke warung" |

| Kode | Jenis | Petunjuk | Kunci | Pengecoh utama | Bloom |
| --- | --- | --- | --- | --- | --- |
| wnb-4-b01 | Atribusi | Pengunggah menulis bahwa ia membuat video ini sendiri. Ketuk tanda yang menunjukkan siapa pembuat video yang sebenarnya. | F2 | F1 → BUD-ATR; F3 → BUD-ATR; F4 → BUD-ATR | C4 |
| wnb-4-b02 | Representasi | Unggahan ini disukai 7.500 orang, tapi ada yang keliru. Bandingkan dengan papan informasi, lalu ketuk bagian yang paling perlu dibetulkan supaya orang lain tidak ikut salah paham. | F3 | F5 → BUD-REP; F8 → BUD-REP; F9 → BUD-REP | C5 |
| wnb-4-b03 | Izin | Kamu ingin memakai video ini untuk tugas sekolah yang dikumpulkan besok. Ketuk langkah pertama yang tepat. | F12 | F11 → BUD-IZN; F10 → BUD-IZN; F1 → BUD-IZN | C4 |
| wnb-4-b04 | Menghargai tradisi | Ketuk komentar yang membetulkan keterangan dengan benar, sopan, dan menyebut sumbernya. | F6 | F7 → BUD-ETK; F8 → BUD-REP; F9 → BUD-REP | C5 |

**Rujukan:** bundengan dari kowangan, Wonosobo: GNFI (2019, 2025), dicek di 01. Mi ongklok dan keranjang ongklok: Indonesia Kaya; Pemkab Wonosobo (kuliner). Akun, sanggar, warung, dan Rumah Budaya fiktif. Telaah 05 memakai contoh sanggar dan museum yang namanya perlu diganti; di sini keduanya sudah diganti nama fiktif.

## `wnb-5` Skenario Digital

**Tingkat:** situasi berkembang dua langkah · 4 skenario per variasi · 4 tindakan (1 tepat, 1 kurang tepat, 2 berisiko) · 5 alasan (semua kode R muncul)

### Variasi a

**wnb-5-a01 · Data pribadi.** Akun yang tidak kamu kenal mengirim pesan: "Selamat! Kamu menang wisata gratis ke Telaga Warna untuk sekeluarga!" Beberapa menit kemudian, ia menulis lagi: "Kirim foto kartu pelajar dan nomor HP orang tuamu sekarang, kuotanya tinggal 3!"

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Langsung kirim fotonya sebelum kuotanya habis | 0 | DIG-PRI | Hadiah dari orang tak dikenal bisa jadi tipuan untuk mengambil data. Desakan "tinggal 3" adalah tanda bahaya. |
| Balas dulu untuk menanyakan dia itu siapa | 0 | DIG-ASG | Membalas membuat orang asing tahu akunmu aktif, dan ia bisa terus membujukmu. |
| Tunjukkan ke orang tua, lalu blokir akunnya | 2 | — | Tepat. Datamu aman, dan akun penipu bisa dihentikan. |
| Diamkan saja tanpa cerita ke siapa pun | 1 | DIG-PSF | Datamu aman. Cerita ke orang tua akan membantu melindungi orang lain juga. |

| Alasan | Kode |
| --- | --- |
| Hadiah dari orang asing bisa jadi tipuan | R-RISK |
| Data orang tua bukan milikku untuk dibagikan | R-ETIK |
| Kata guru tidak boleh membalas orang asing | R-ATUR |
| Teman-temanku juga ikut mengirim datanya | R-SOS |
| Aku memang tidak terlalu suka jalan-jalan | R-ACAK |

Skenario ini diambil dari contoh di 01; kalimat tanya alasannya dibuat netral.

**wnb-5-a02 · Kata sandi dan kode OTP.** Kamu mendapat pesan dari akun bernama "Tim Resmi Game Bintang ✓": "Akunmu akan dihapus hari ini! Kirim kode yang baru masuk ke HP ibumu untuk menyelamatkannya." Tak lama kemudian, HP ibumu menerima SMS berisi kode enam angka.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Cepat kirim kodenya supaya akun tidak dihapus | 0 | DIG-SAN | Kode seperti itu adalah kunci akun. Tim yang asli tidak pernah memintanya, walaupun akunnya bertanda centang. |
| Balas dulu, tanya apakah mereka benar tim resmi | 0 | DIG-ASG | Penipu akan selalu menjawab "benar". Membalas membuatmu terus dibujuk. |
| Tunjukkan pesan dan SMS-nya ke ibu | 2 | — | Tepat. Kode tetap aman, ibu tahu ada yang mencoba masuk ke akunnya, dan kalian bisa melaporkan akun itu bersama. |
| Hapus pesan itu dan tidak cerita ke ibu | 1 | DIG-PSF | Kodenya aman. Ibu perlu tahu, karena kode itu masuk ke HP-nya. |

| Alasan | Kode |
| --- | --- |
| Tim resmi tidak pernah meminta kode itu | R-RISK |
| Kode itu masuk ke HP ibu, bukan milikku | R-ETIK |
| Aku dilarang membalas pesan orang tak dikenal | R-ATUR |
| Temanku pernah mengirim kode dan aman saja | R-SOS |
| Aku sebenarnya jarang main game itu | R-ACAK |

"Game Bintang" fiktif. Sengaja tidak memakai nama GELITA, supaya siswa tidak mengira tim GELITA bisa menghubungi mereka lewat pesan.

**wnb-5-a03 · Izin membagikan obrolan.** Bima bercerita lewat pesan pribadi bahwa ia sedih karena kalah lomba bundengan. Sore harinya, teman-teman di grup kelas bertanya, "Bima kenapa sih? Kok diam terus?" Kamu masih menyimpan obrolanmu dengan Bima.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Teruskan tangkapan layar obrolan Bima ke grup | 0 | DIG-IZN | Obrolan pribadi Bima hanya boleh dibagikan kalau ia setuju. Tangkapan layar bisa tersebar ke mana-mana. |
| Ceritakan isi obrolannya di grup dengan kata-katamu sendiri | 0 | DIG-ETK | Walaupun tanpa tangkapan layar, cerita pribadi Bima tetap tersebar tanpa izinnya. |
| Minta grup bertanya langsung ke Bima, lalu sapa Bima | 2 | — | Tepat. Rahasia Bima terjaga, dan kamu tetap peduli padanya. |
| Tidak menjawab grup dan tidak menyapa Bima | 1 | DIG-PSF | Rahasia Bima aman. Menyapa Bima secara pribadi akan membuatnya merasa ditemani. |

| Alasan | Kode |
| --- | --- |
| Bima belum tentu mau ceritanya disebar | R-ETIK |
| Tangkapan layar bisa tersebar ke mana-mana | R-RISK |
| Ada aturan kelas tidak boleh bergosip di grup | R-ATUR |
| Teman-teman di grup sudah sangat penasaran | R-SOS |
| Aku juga suka mendengar bunyi bundengan | R-ACAK |

**wnb-5-a04 · Kabar bohong, dua langkah.** Di grup kelas beredar pesan dengan foto jalan rusak: "Jalan ke Telaga Warna putus karena longsor hari ini! Sebarkan!" Temanmu, Fikri, membalas: "Sudah kusebar ke grup keluarga." Lalu kamu melihat foto yang sama di sebuah berita lama dari daerah lain.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Ikut menyebarkan ke grup lain supaya orang berhati-hati | 0 | DIG-HOX | Fotonya ternyata foto lama dari tempat lain. Menyebarkannya membuat lebih banyak orang salah paham. |
| Balas di grup: "Fikri bodoh, itu hoaks!" | 0 | DIG-ETK | Kamu benar soal fotonya, tapi kata-kata kasar membuat Fikri malu dan teman lain enggan mendengar. |
| Beri tahu Fikri dengan sopan bahwa itu foto lama | 2 | — | Tepat. Kabar bohong bisa berhenti, dan Fikri tetap dihargai. Ajak juga mengecek akun resmi pengelola. |
| Tidak menyebarkan dan tidak menanggapi apa pun | 1 | DIG-PSF | Kamu tidak ikut menyebarkan, bagus. Memberi tahu dengan sopan bisa menghentikan kabar bohong itu. |

| Alasan | Kode |
| --- | --- |
| Foto lama bisa membuat orang salah paham | R-RISK |
| Kalau ditegur dengan sopan, Fikri tidak malu | R-ETIK |
| Guru bilang tidak boleh menyebarkan berita | R-ATUR |
| Semua orang di grup sudah percaya kabar itu | R-SOS |
| Aku belum pernah pergi ke Telaga Warna | R-ACAK |

### Variasi b

**wnb-5-b01 · Data pribadi.** Akun "Undian Carica Dieng" mengumumkan: "Ikuti undian, hadiahnya sepeda!" Untuk ikut, peserta harus mengirim foto Kartu Keluarga dan nomor HP orang tua. Temanmu, Sinta, berkata, "Aku sudah ikut, kok. Katanya aman."

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Kirim foto Kartu Keluarga supaya bisa ikut | 0 | DIG-PRI | Kartu Keluarga memuat data seluruh keluargamu. Undian sungguhan tidak memintanya. |
| Kirim nomor HP orang tua dulu, foto KK menyusul | 0 | DIG-PRI | Nomor HP orang tua juga data pribadi yang bisa dipakai untuk menipu. |
| Tunjukkan ke orang tua, dan ingatkan Sinta juga | 2 | — | Tepat. Datamu aman, dan kamu ikut menolong Sinta. |
| Tidak ikut, dan tidak membahasnya dengan Sinta | 1 | DIG-PSF | Datamu aman. Sinta mungkin perlu bantuan karena sudah mengirim datanya. |

| Alasan | Kode |
| --- | --- |
| Data keluarga bisa disalahgunakan orang asing | R-RISK |
| Kartu Keluarga memuat data seluruh keluargaku | R-ETIK |
| Kata guru tidak boleh ikut undian di internet | R-ATUR |
| Sinta sudah ikut, jadi pasti aman-aman saja | R-SOS |
| Aku sudah punya sepeda di rumah | R-ACAK |

**wnb-5-b02 · Kata sandi.** Kamu mendapat pesan dari "Admin Sekolah": "Akun belajarmu akan diblokir. Masuk ulang lewat `sekolah-kita.example/masuk` dan tulis kata sandimu." Kamu melihat alamat itu berbeda dari alamat situs sekolah yang biasa kamu pakai.

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Buka tautannya dan tulis kata sandimu di sana | 0 | DIG-SAN | Alamat yang berbeda adalah tanda bahaya. Situs palsu bisa mencuri kata sandimu. |
| Balas pesannya, tanya apakah benar admin sekolah | 0 | DIG-ASG | Pengirim palsu akan menjawab "benar". Lebih aman bertanya langsung ke guru. |
| Tunjukkan pesan itu ke guru tanpa membukanya | 2 | — | Tepat. Guru bisa memastikan, dan teman-teman lain bisa diperingatkan. |
| Hapus pesannya dan tidak cerita ke siapa pun | 1 | DIG-PSF | Kata sandimu aman. Memberi tahu guru akan melindungi teman-temanmu juga. |

| Alasan | Kode |
| --- | --- |
| Alamatnya berbeda, bisa jadi situs palsu | R-RISK |
| Data teman sekelas bisa ikut terlihat | R-ETIK |
| Aku dilarang membuka tautan apa pun di HP | R-ATUR |
| Teman-teman lain sepertinya sudah masuk ulang | R-SOS |
| Aku sedang tidak ada tugas sekolah | R-ACAK |

**wnb-5-b03 · Izin membagikan foto teman.** Saat studi wisata ke Dieng, kamu memotret Nadia yang tertidur di bus dengan mulut terbuka. Teman-teman di grup kelas tahu, lalu menulis: "Kirim dong fotonya, pasti lucu!"

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Kirim fotonya ke grup karena teman-teman meminta | 0 | DIG-IZN | Nadia tidak tahu dirinya difoto. Foto itu hanya boleh dibagikan kalau ia setuju. |
| Jadikan stiker lucu, lalu kirim ke grup | 0 | DIG-ETK | Stiker dari foto orang yang sedang tidur bisa membuatnya malu dan terus beredar. |
| Bilang di grup bahwa kamu perlu izin Nadia dulu | 2 | — | Tepat. Kamu menjaga perasaan Nadia walaupun teman-teman mendesak. |
| Tidak menjawab grup dan tetap menyimpan fotonya | 1 | DIG-PSF | Fotonya tidak tersebar, bagus. Menjelaskan alasanmu bisa membuat teman lain ikut berpikir. |

| Alasan | Kode |
| --- | --- |
| Nadia belum tentu mau fotonya disebar | R-ETIK |
| Foto yang terkirim bisa tersebar ke mana-mana | R-RISK |
| Guru melarang memotret teman di dalam bus | R-ATUR |
| Teman-teman di grup sudah memintanya | R-SOS |
| Perjalanan ke Dieng memang jauh sekali | R-ACAK |

**wnb-5-b04 · Kabar bohong, dua langkah.** Di grup kelas ada video berjudul "Embun upas bisa membuat orang sakit, jangan keluar rumah pagi-pagi!" Video itu tidak menyebut sumber. Temanmu, Fikri, membalas: "Sudah kukirim ke grup keluarga."

| Tindakan | Skor | Kode | Umpan balik |
| --- | --- | --- | --- |
| Ikut mengirim ke grup lain supaya orang berhati-hati | 0 | DIG-HOX | Video tanpa sumber belum tentu benar. Menyebarkannya bisa membuat banyak orang cemas. |
| Balas di grup: "Fikri bodoh, mana ada begitu!" | 0 | DIG-ETK | Mengingatkan itu bagus, tapi kata-kata kasar membuat Fikri malu. |
| Ingatkan Fikri dengan sopan untuk cek sumbernya | 2 | — | Tepat. Kabar dicek dulu, dan Fikri tetap dihargai. |
| Tidak mengirim dan tidak menanggapi apa pun | 1 | DIG-PSF | Kamu tidak ikut menyebarkan, bagus. Memberi tahu dengan sopan bisa menghentikan kabar itu. |

| Alasan | Kode |
| --- | --- |
| Video tanpa sumber bisa membuat orang cemas | R-RISK |
| Kalau ditegur dengan sopan, Fikri tidak malu | R-ETIK |
| Guru bilang tidak boleh menonton video di grup | R-ATUR |
| Banyak orang di grup sudah percaya video itu | R-SOS |
| Aku belum pernah melihat embun upas | R-ACAK |

---

# Susun Gambar selama studi

Ditambahkan 26 Oktober 2026 · **usulan, menunggu keputusan pemilik proyek**

Susun Gambar (tantangan 1) adalah pemanasan dan tidak dinilai, jadi tidak ada di dokumen ini. Tapi keterangan gambarnya dibaca siswa tepat sebelum Isi Rumpang dan Benar atau Salah di pertemuan yang sama. Bank yang terpasang punya 10 variasi gambar per wilayah (a–j), masing-masing dengan keterangan ([`docs/bank-soal/data/`](../bank-soal/data/)). Semua keterangan diperiksa terhadap butir yang dinilai dan butir PBK di dokumen ini, memakai aturan menulis butir no. 2 di [06](06_KISI_KISI_BANK_SOAL.md#aturan-menulis-butir) dan aturan cerita di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#aturan-menulis-cerita-untuk-penelitian). Pemeriksaan ini ditemukan saat menyusun 12.

**Temanggung**

| Variasi | Gambar | Yang dibocorkan keterangan | Butir |
| --- | --- | --- | --- |
| a | Gunung Sindoro dan Sumbing | Tinggi Sumbing 3.371 m; tanahnya subur | `tmg-2-a01`, `a03` |
| b | Candi Pringapus | Candi Hindu; arca Nandi di dalamnya | `tmg-3-b01`, `b04`; PBK `tmg-4-x03` |
| c | Kuda Lumping | Kuda-kudaan dari anyaman bambu | `tmg-4-a02` (D3); PBK `tmg-4-x01` |
| d | Situs Liyangan | Tahun 2008, padi hangus, letusan Sindoro | `tmg-3-a01`, `a02`, `a04` |
| e | Prasasti Gondosuli | Tahun 832; arti prasasti | `tmg-3-b02` |
| f | Umbul Jumprit | Nama mata air, hulu Sungai Progo, air suci, "tidak pernah kering" | `tmg-2-b01`–`b04` |
| g | Panen Kopi di Lereng Sindoro–Sumbing | Kopi di ketinggian lebih dari 900 m | `tmg-2-a04`; PBK `tmg-4-x02` |
| h | Ikan Uceng dan Sungai Jernih | Ikan sungai kecil, makanan khas Temanggung | `tmg-4-b02` (D3); PBK `tmg-4-x04` |
| i | Pasar Papringan | Tidak ada | — |
| j | Perajin Kuda Kepang | Kuda kepang dari anyaman bambu | `tmg-4-a02` (D3); PBK `tmg-4-x01` |

**Magelang**

| Variasi | Gambar | Yang dibocorkan keterangan | Butir |
| --- | --- | --- | --- |
| a | Stupa Borobudur | 72 stupa berlubang, masing-masing berisi arca Buddha | `mgl-2-a02`, `a03`; PBK `mgl-4-x01` |
| b | Penari Topeng Ireng | Hiasan kepala dari bulu, lonceng di kaki, asal Desa Tuksongo | `mgl-2-b01`, `b02`; `mgl-3-x01`; `mgl-4-a02` (D3); PBK `mgl-4-x02` |
| c | Candi Mendut | Tidak ada | — |
| d | Relief Borobudur | Relief tidak boleh disentuh | `mgl-3-a03`; dekat dengan `mgl-2-a05` |
| e | Candi Pawon | Tidak ada | — |
| f | Gunung Tidar | Bukit di tengah kota, tangga untuk olahraga, legenda paku tanah Jawa | `mgl-3-b01`, `b03` |
| g | Getuk Magelang | Kota Getuk; singkong dikukus, ditumbuk, diberi gula | PBK `mgl-4-x03` |
| h | Gerabah Klipoh | Dusun Klipoh dekat Borobudur; tanah liat dibentuk lalu dibakar | `mgl-4-b02` (D3); PBK `mgl-4-x04` |
| i | Perjalanan Waisak | Air suci dari Umbul Jumprit | `tmg-2-b03`, hanya untuk siswa susulan Temanggung |
| j | Borobudur di Antara Dua Pasang Gunung Kembar | Sindoro–Sumbing disebut gunung kembar | `tmg-2-a`, hanya untuk siswa susulan Temanggung |

**Wonosobo**

| Variasi | Gambar | Yang dibocorkan keterangan | Butir |
| --- | --- | --- | --- |
| a | Telaga Warna | Air mengandung belerang dan tidak untuk diminum | `wnb-3-a01`–`a06` |
| b | Matahari Terbit di Sikunir | Desa Sembungan sekitar 2.300 m, angka yang dipakai bernalar di butir | `wnb-2-a04` |
| c | Mi Ongklok | Keranjang ongklok; makanan khas Wonosobo | `wnb-4-b02` (D3); PBK `wnb-4-x03` |
| d | Kompleks Candi Arjuna | Tidak ada | — |
| e | Kawah di Dieng: Tetap di Balik Pagar | Gas di sekitar kawah berbahaya; ikuti rambu dan pagar | `wnb-3-b02`, `b05`, `b06` |
| f | Carica, Pepaya Gunung | Tumbuh di 1.400–2.400 m dan di udara sejuk | `wnb-2-a04`–`a06`; PBK `wnb-4-x02` |
| g | Bundengan | Alat musik khas Wonosobo dari tudung penggembala bebek | `wnb-4-a02` (D3); PBK `wnb-4-x01` |
| h | Kebun Teh Tambi | Kebun teh sejak 1865 | PBK `wnb-4-x04` |
| i | Tari Topeng Lengger | Tidak ada, tapi keterangan menyebut lengger "tari khas Wonosobo", yang asalnya diperdebatkan (lihat *Yang harus dicek sebelum validasi ahli*) | — |
| j | Embun Upas | Suhu pagi di bawah 0 °C sehingga embun membeku | `wnb-2-b03`, `b05` |

**Usulan:** selama studi, Susun Gambar hanya memakai variasi yang tidak membocorkan apa pun: **Temanggung i** (Pasar Papringan), **Magelang c dan e** (Candi Mendut, Candi Pawon), dan **Wonosobo d** (Kompleks Candi Arjuna). Di luar studi, semua variasi tetap dipakai seperti sekarang. Keterangan variasi Wonosobo i (lengger) dicek ahli budaya, terlepas dari keputusan ini.

---

# Pemeriksaan terhadap kisi-kisi

## Jumlah dan sebaran

| Pemeriksaan | Hasil |
| --- | --- |
| Butir per variasi sesuai kisi-kisi (rumpang 4/5/6, pernyataan 4/6/6, jangkar 2, Periksa Unggahan 4, skenario 4) | Sesuai |
| D1 per siswa (buklet mana pun) | 37 butir: 26 D1a, 11 D1b (Temanggung 9 + 1, Magelang 9 + 4, Wonosobo 8 + 6) |
| Kunci Benar atau Salah per variasi | Temanggung 2 : 2, Magelang dan Wonosobo 3 : 3, jangkar 1 : 1; tidak ada pola selang-seling penuh |
| Skenario Digital | Setiap skenario punya tepat satu tindakan tepat (2) dan satu kurang tepat (1); tindakan 3/4/4 dan alasan 3/4/5 sesuai wilayah |
| Alasan | Setiap set memuat R-RISK atau R-ETIK, R-ATUR, dan R-SOS atau R-ACAK; di Wonosobo kelima kode muncul |
| Periksa Unggahan | Setiap variasi memuat empat jenis (atribusi, representasi, izin, menghargai tradisi); bagian ketuk 8/10/12 |
| Bloom | Semua butir di dalam rentang wilayah |

## Kode kesalahan yang dipakai

| Wilayah | Kode INF | Kode BUD | Kode DIG |
| --- | --- | --- | --- |
| Temanggung | LIT, KAT, REL, HUB, SUM | ATR, REP, IZN, ETK | PRI, SAN, IZN, HOX, PSF |
| Magelang | LIT, KAT, REL, HUB, GEN, FOP, SUM | ATR, REP, IZN, ETK | PRI, SAN, IZN, ETK, HOX, PSF |
| Wonosobo | LIT, KAT, REL, HUB, GEN, SUM | ATR, REP, IZN, ETK | PRI, SAN, IZN, ASG, ETK, HOX, PSF |

**Penyesuaian dari kisi-kisi.** Kisi-kisi meminta setiap kode INF muncul minimal dua kali per wilayah. Itu tidak masuk akal untuk semua kode: INF-FOP (fakta dan pendapat) memang bukan tugas Temanggung, dan INF-GEN jarang cocok di level menemukan informasi. Aturan yang dipakai di draf ini (disetujui pemilik proyek 25 Oktober 2026, dan kisi-kisi 06 sudah disesuaikan): setiap kode yang cocok dengan level wilayah muncul paling sedikit sekali di wilayah itu, dan setiap kode INF muncul di minimal dua wilayah, kecuali INF-FOP yang memang khusus Magelang. DIG-ASG hanya muncul di Wonosobo, tempat situasi orang asing paling rumit.

## Panjang teks

| Teks | Kisi-kisi | Draf | Catatan |
| --- | --- | --- | --- |
| `tmg-2` | 55–70 kata | ± 75–80 | Sedikit di atas; selisih a dan b < 10% |
| `tmg-3` | 50–70 | ± 50–55 (isi), ± 65 dengan kepala layar | Sesuai |
| Jangkar TMG | 30–40 | ± 35 | Sesuai |
| `mgl-2` | 100–120 | ± 120 | Sesuai |
| `mgl-3` | 110–140 | ± 110–125 | Sesuai |
| Jangkar MGL | 40–50 | ± 55 | Sedikit di atas |
| `wnb-2` | 110–130 | ± 120–125 (isi) | Sesuai |
| `wnb-3` | 90–150 | ± 90–95 | Di bawah rentang. Enam pernyataan tetap bisa dijawab dari teks, dan beban baca turun. Rentang kisi-kisi diubah jadi 90–150 (disetujui) |
| Jangkar WNB | 25–50 | ± 25 | Unggahan iklan memang pendek. Rentang diubah jadi 25–50 (disetujui) |

# Yang harus dicek sebelum validasi ahli

**Nama fiktif (dicek 25 Oktober 2026).** Setiap nama dicari di web dengan tanda kutip, ditambah kata kunci daerah atau jenis usahanya.

| Nama | Hasil | Tindakan |
| --- | --- | --- |
| Sanggar Turangga Muda | Tidak ada yang persis sama, tapi nama yang hampir sama dipakai beberapa kelompok kuda lumping nyata (Paguyuban Turonggo Mudo di Dharmasraya, Sekar Turonggo Mudo di Kampar, Sanggar Turonggo Satrio Mudo di Blitar) | **Diganti** menjadi Sanggar Kuda Pelangi (tidak ditemukan) |
| Sanggar Kuda Pelangi, Sanggar Cahya Kuluk, Sanggar Sekar Kowangan, Rumah Gerabah Sekar Lempung | Tidak ditemukan | Dipakai |
| Warung Tepi Kali Bu Tini, Warung Kabut Pagi | Tidak ditemukan | Dipakai |
| SD Lereng Sindoro, Undian Carica Dieng | Tidak ditemukan | Dipakai |
| Game Bintang | Tidak ada game dengan nama ini; yang muncul hanya acara televisi "Liga Bintang Juara" | Dipakai |
| Kedugram (nama aplikasi media sosial fiktif) | Tidak ditemukan | Dipakai |
| Rumah Budaya | Kata benda umum, bukan nama lembaga tertentu | Dipakai |
| Akun pengunggah (`@jalan2_bima`, `@sekar.kuliner`, `@vlog.arka`, `@dapur.nayla`, `@jelajah.awan`, `@makan.terus`) dan akun resmi tiruan (`@pengelola.candi`, `@infowisata.kotamagelang`, `@pengelola.telagawarna`, `@carica.mantap`) | Tidak ditemukan di web. **Catatan:** mesin pencari tidak mengindeks Instagram dan TikTok dengan lengkap, jadi hasil ini belum membuktikan nama itu kosong | Dipakai, karena semua unggahan tampil di aplikasi fiktif *Kedugram* dan tidak merujuk ke platform nyata |
| Nama komentator | Nama depan umum tidak menunjuk orang tertentu | `@nama` diganti nama depan saja (misalnya **Nadia**, **Pak Anton**) |
| Situs | Semua memakai domain `.example` (dicadangkan, RFC 2606) | Dipakai |

Sebelum pengambilan data, satu orang tim tetap disarankan mengetik setiap nama akun di kolom cari Instagram dan TikTok. Kalau ada akun nyata yang aktif dengan nama sama persis, namanya diganti.

**Fakta yang dipakai ulang dari bank lama** sudah dicek di telaah pendahuluan T6 ([09_TELAAH_PENDAHULUAN_BANK_SOAL.md](09_TELAAH_PENDAHULUAN_BANK_SOAL.md), bagian C.5): semuanya didukung sumber. Satu catatan: tahun berdirinya perkebunan teh Tambi umumnya ditulis 1865, tapi ada sumber yang menulis 1885; ahli budaya memutuskan apakah angka tahun tetap dipakai di petunjuk `wnb-4-x04`.

**Fakta yang sengaja tidak dipakai** karena sumbernya belum meyakinkan: apakah penari Topeng Ireng memakai topeng (sumber tidak menyebutnya), dan asal tari lengger (sebagian sumber menyebut Wonosobo, sebagian Banyumas). Karena itu Periksa Unggahan Wonosobo memakai bundengan dan mi ongklok, bukan lengger.

**Skenario.** Setiap skenario dibaca ulang oleh ahli bahasa anak dan ahli literasi digital: apakah tindakan "tepat" memang paling tepat, apakah tindakan "kurang tepat" memang aman tapi belum lengkap, dan apakah umpan baliknya tidak menakut-nakuti.

# Gambar yang dibutuhkan

Teks digital (layar informasi, unggahan, grup, hasil pencarian) dibangun sebagai tampilan di game, bukan gambar, supaya teksnya bisa dibacakan dan diperbesar. Yang perlu gambar:

| Untuk | Isi | Bisa memakai |
| --- | --- | --- |
| `tmg-3-a04` | Butir padi hangus | Gambar lama `challenge.item.tmg-3-a03` |
| `tmg-3-b04` | Arca lembu duduk dari batu | Baru (gambar lama sengaja arca Buddha) |
| `tmg-4` babak 2 a | Foto penari kuda lumping | Gambar Susun Gambar `tmg-1-04` atau `tmg-1-j01` |
| `tmg-4` babak 2 b | Uceng goreng di piring | Baru, atau potongan `tmg-1-h01` |
| `mgl-4` babak 2 a | Penari Topeng Ireng | `mgl-1-02` |
| `mgl-4` babak 2 b | Gerabah Klipoh | `mgl-1-h01` |
| `wnb-4` babak 2 a | Pemain bundengan | `wnb-1-g01` |
| `wnb-4` babak 2 b | Semangkuk mi ongklok | `wnb-1-07` |
| `tmg-5`, `mgl-5`, `wnb-5` | Satu ilustrasi per skenario (24), opsional | Jaka dan teman-teman dengan perangkat; tanpa layar sungguhan |

# Langkah berikutnya

1. Pemilik proyek menelaah draf ini.
2. Cek fakta di bagian *Yang harus dicek* (nama fiktif sudah dicek; tinggal pencarian manual akun di Instagram dan TikTok).
3. Validasi ahli (I-CVI atau V Aiken) dengan instrumen yang sedang disiapkan sesi koordinator.
4. Terjemahan bahasa Inggris untuk setiap teks yang tampil di game.
5. Pindahkan isi ke berkas data `docs/bank-soal/` dengan kolom baru dari kisi-kisi. Ini pekerjaan tahap implementasi.

# Untuk dicatat di catatan keputusan

- Draf bank soal baru disusun di `08_DRAF_BANK_SOAL.md`: 116 butir literasi digital dan 12 butir PBK, semua berstatus `needs_verification`.
- Dari bank lama, variasi yang dipakai ulang (dengan perbaikan): `tmg-2` a (Sindoro–Sumbing) dan b (Umbul Jumprit); `tmg-3` a (Liyangan) dan b (Pringapus–Gondosuli); `mgl-2` a (Borobudur) dan b (Topeng Ireng); `mgl-3` a (Borobudur) dan c (Gunung Tidar, sekarang b); `wnb-2` a (Dieng) dan c (embun upas, sekarang b); `wnb-3` a (Telaga Warna) dan c (kawah, sekarang b). Tidak dipakai: `tmg-2-c`, `tmg-3-c`, `mgl-2-c`, `mgl-3-b`, `wnb-2-b`, `wnb-3-b`.
- Semua perbaikan butir di 05 sudah dimasukkan, kecuali `mgl-3-b04` (memotret Waisak), yang tidak dipakai karena variasinya tidak dipilih.
- **Disetujui pemilik proyek (25 Oktober 2026):** aturan sebaran kode di kisi-kisi diubah: setiap kode INF yang cocok dengan level wilayah muncul paling sedikit sekali di wilayah itu, dan setiap kode INF muncul di minimal dua wilayah (kecuali INF-FOP, khusus Magelang).
- **Disetujui pemilik proyek (25 Oktober 2026):** rentang panjang teks `wnb-3` jadi 90–150 kata dan kartu jangkar Wonosobo 25–50 kata. Kisi-kisi 06 sudah disesuaikan.
- Nama fiktif sudah dicek di web (25 Oktober 2026). "Sanggar Turangga Muda" diganti "Sanggar Kuda Pelangi" karena mirip nama kelompok kuda lumping nyata. Semua unggahan tampil di aplikasi fiktif *Kedugram*, dan komentator ditulis dengan nama depan saja.
- Penari Topeng Ireng (memakai topeng atau tidak) dan asal tari lengger tidak dipakai sebagai fakta di bank, karena sumbernya belum meyakinkan.
- Skenario OTP memakai nama game fiktif "Game Bintang", bukan GELITA.
- Lembaga resmi di bacaan ditulis tanpa nama dan logo (misalnya "akun resmi badan pemantau gunung api").
- Telaah pendahuluan T6 sudah dijalankan ([09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md)); sisa temuan R1–R4 disetujui pemilik proyek sesuai usulan pada 25 Oktober 2026. Berkas untuk ahli memuat empat pertanyaan khusus: rumpang `tmg-2-b02`, `mgl-2-a02`, `mgl-2-a05` (R1), label D1b `tmg-3-a03`/`tmg-3-b03` (R2), pola pilihan "kurang tepat" (R3), dan kunci `wnb-4-a02`/`wnb-4-b02` (R4).

# Rujukan tambahan (dibuka 25 Oktober 2026)

- Bundengan mengiringi lengger, dan asal lengger yang diperdebatkan: [Wikipedia: Lengger](https://en.wikipedia.org/wiki/Lengger); [GNFI 2019](https://www.goodnewsfromindonesia.id/2019/10/14/bundenga-alat-musik-tradisional-yang-berawal-dari-pelindung-gembala); [Katadata](https://katadata.co.id/amp/berita/daerah/631075f2df535/memahami-sejarah-dan-makna-tari-tradisional-lengger/).
- Topeng Ireng: asal nama dan kostum, tanpa keterangan tentang topeng: [VOI](https://voinews.id/index.php/component/k2/item/3189-topeng-ireng-dance).
- Rujukan isi bacaan lainnya sama dengan bank lama ([`docs/bank-soal/data/`](../bank-soal/data/)) dan cek fakta di [01](01_REVISI_10_ANALITIK.md#cek-fakta-dan-rujukan) dan [05](05_TELAAH_BUTIR_DAN_DIMENSI.md#rujukan).
- Cek nama fiktif (25 Oktober 2026): kelompok kuda lumping bernama mirip "Turonggo Mudo": [Avant-Garde UNP](https://avant-garde.ppj.unp.ac.id/index.php/avant-garde/article/view/213), [Jurnal Tata Rias Unesa](https://ejournal.unesa.ac.id/index.php/jurnal-tata-rias/article/view/33731), [Repository UIR](https://repository.uir.ac.id/25977/).
