# Telaah Draf 1 Naskah Cerita & Bank Soal GELITA Kelas 5

Tanggal telaah: 17 Oktober 2026.

**Yang ditelaah** adalah draf 1 di `gelita-katalog/update/`:

- `naskah-cerita/naskah-cerita.md`
- `bank-soal/bank-soal-kelas5.xlsx`
- `catatan-revisi-gelita.md`
- `bank-soal/analisis-naskah-bank-soal-gelita.md`
- `gambar-puzzle/panduan-gambar-puzzle-gelita.md`

**Pembandingnya** adalah dokumen yang dipakai aplikasi sekarang:

- naskah lama: `docs/naskah-cerita.md`, sama persis dengan `app/Database/Seeds/data/story.php`;
- bank soal lama: `docs/bank-soal/gelita-bank-soal-kelas5.xlsx` dari `docs/bank-soal/data/*.php`.

**Lensa telaah:** ahli bahasa (EYD V dan keterbacaan), guru kelas 5, Kurikulum Merdeka (CP Fase C dan profil lulusan), Taksonomi Bloom C1–C6, validitas pengukuran, kesinambungan naskah–bank, budaya Kedu, keramahan dan keselamatan anak, serta terjemahan Inggris.

**Hasil telaah langsung diterapkan sebagai draf 2.** Draf 2 menimpa draf 1 di berkas yang sama; draf 1 tetap tersimpan di riwayat git. Ringkasan perubahannya ada di bagian 5 dan di `catatan-revisi-gelita.md` bagian 7.

---

## 1. Ringkasan dan putusan

**Putusan: draf 1 layak dilanjutkan. Draf 2 memperbaiki satu masalah besar dan sejumlah masalah kecil.** Draf 1 sudah menyelesaikan hampir semua temuan analisis 13 Oktober:

- fakta–pendapat kini benar-benar diukur di Magelang;
- warna Telaga Warna dan pesan keselamatan kawah tidak lagi bertentangan;
- narasi Kenali kini mengenalkan Nandi, uceng, ongklok, Tambi, bundengan, dan Umbul Jumprit;
- kaki Mbah, papan petunjuk, dan pertengkaran di Magelang sudah logis;
- label Bloom lebih jujur;
- tokoh perempuan bertambah.

Isi draf 1 juga cocok dengan catatan revisinya. Diff baris demi baris dan butir demi butir menunjukkan tidak ada perubahan lain di luar yang dicatat.

Masih ada temuan yang perlu ditangani sebelum uji coba ke siswa. Semuanya sudah ditangani di draf 2.

| Prioritas | Temuan | Dampak |
|---|---|---|
| **P1** | **Butir fakta–pendapat bisa dijawab dari pola permukaan.** Di keenam butir, kata "Ucapan …" selalu berarti pendapat dan "Kalimat …" selalu berarti fakta. Setiap pendapat juga memakai templat "Menurut saya, … paling …", dan petunjuk nomor 1 mengajarkan penanda itu. | Siswa bisa benar tanpa memahami konsepnya. Tuntutan nyata C2, bukan C4. Skill baru `membedakan_fakta_pendapat` jadi tidak valid. |
| **P1** | **Pola kunci di Benar atau Salah.** Butir gagasan pokok Magelang selalu BENAR. Kunci wnb-3-b berselang-seling S-B-S-B-S-B. Ketiga pernyataan "sama-sama" di Wonosobo selalu SALAH. | Urutan pernyataan tidak diacak, jadi pola mudah ditemukan siswa. |
| **P1** | **wnb-3-c06 bisa mengajarkan bahwa sikap hati-hati itu salah.** Kunci "keluarga Dinda sebaiknya membatalkan wisata" adalah SALAH. | Padahal di cerita, membatalkan wisata adalah pilihan yang aman. Pesan keselamatannya jadi kabur. |
| **P2** | **mgl-2-c06 bergantung pada pengetahuan awal.** Kegunaan cobek tidak ada di bacaan, dan butirnya memakai stereotip "Ibu" yang memasak. | Yang terukur kosakata rumah tangga, bukan membaca. |
| **P2** | **Kutipan Pak Darto memutus kohesi mgl-3-a.** Kutipan itu berada di antara "dianggap berharga" dan "Karena sangat berharga". Butir mgl-3-a02 ("kurang dari seribu") juga terasa seperti jebakan angka. | Mengganggu keterbacaan dan validitas butir menghubungkan. |
| **P2** | **mgl-1-i01 menyiratkan api Waisak juga diambil dari Umbul Jumprit.** | Fakta budaya keliru di keterangan puzzle. |
| **P2** | **Ketidaksamaan istilah naskah–bank:** "material letusan", tiga nama kuda lumping, nama keranjang ongklok, "ide pokok" vs "gagasan pokok", "Tari Lengger" vs "Topeng Lengger", EN "mie ongklok". | Benang merah naskah–bank melemah, dan siswa bisa bingung. |
| **P2** | **Bahasa naskah.** Tiga baris Kenali terlalu padat (45–57 kata). Logika intro-03 berputar. Legenda "satu malam" sebenarnya milik Prambanan. Petugas "menjamin" kawah tidak meledak. Jaka tidak berperan dalam penyelesaian di Wonosobo. | Beban dengar tinggi, ada risiko miskonsepsi budaya dan sains, dan pesan cerita melemah. |
| **P3** | Label Bloom wnb-4-a01–a03 (C4) terlalu tinggi. Beberapa fakta baru masih perlu diverifikasi. | Laporan HOTS dan ketepatan isi. |

---

## 2. Perbandingan lama → draf 1 → draf 2

### 2.1 Angka

| | Lama (aplikasi) | Draf 1 | Draf 2 |
|---|---|---|---|
| Baris naskah | 88 | 88 (27 diubah) | 88 (32 berbeda dari lama) |
| Kata naskah ID · rata-rata kata per kalimat | 1.643 · 6,9 | 1.805 · 7,1 | 1.819 · 7,1 |
| Baris naskah terpanjang | 40 kata | 57 kata (`kenal-wonosobo-03`); juga `kenal-temanggung-04` 46 dan `kenal-magelang-03` 45 | 45 kata |
| Baris soal (aktif) | 126 | 151 (147) | 151 (147) |
| Variasi | 30 | 51 | 51 |
| Kode skill Benar atau Salah | 9 | 10 | 10 |
| Pengecoh Isi Rumpang Temanggung | 0 | 1 | 1 |

### 2.2 Sebaran Bloom (99 butir aktif berlabel)

| | C1 | C2 | C3 | C4 | C5 | HOTS (C4–C5) |
|---|---|---|---|---|---|---|
| Lama | 12 | 26 | 23 | 26 | 12 | 38 (38%) |
| Draf 1 | 12 | 25 | 28 | 21 | 13 | 34 (34%) |
| Draf 2 | 12 | 26 | 30 | 18 | 13 | 31 (31%) |

Per wilayah, draf 2 tersebar sebagai berikut:

- **Temanggung:** C1 12 · C2 10.
- **Magelang:** C2 16 · C3 9 · C4 9.
- **Wonosobo:** C3 21 · C4 9 · C5 13.

Angka HOTS turun, tetapi kini lebih jujur. Enam butir C4 fakta–pendapat di draf 1 sebenarnya C2 karena polanya (bagian 3.4). Di draf 2, label C4-nya baru sah karena polanya sudah dipatahkan. Tangga tetap naik rapi: Temanggung C1–C2, Magelang C2–C4, Wonosobo C3–C5.

### 2.3 Keterampilan Benar atau Salah

| Wilayah | Lama | Draf 1 / Draf 2 |
|---|---|---|
| Magelang | gagasan_pokok 3 · menghubungkan_informasi 7 · mengklasifikasikan_tindakan 5 | gagasan_pokok 3 · menghubungkan_informasi 3 · mengklasifikasikan_tindakan 3 · **membedakan_fakta_pendapat 6** |
| Wonosobo | membandingkan 6 · menilai_pilihan 9 · menentukan_solusi 3 | membandingkan 5 · menilai_pilihan 10 · menentukan_solusi 3 |

---

## 3. Telaah per lensa

### 3.1 Ahli bahasa: EYD V dan keterbacaan

**Yang sudah baik di draf 1:**

- Kalimat 29 kata di `intro-02` sudah dipecah.
- Kata kurang akrab sudah diganti: membelit → melilit, terperosok → tercebur, tipu daya → jebakan, ditelan mentah-mentah → dipercaya begitu saja.
- Ejaan baku dipakai, misalnya "memercayai" (bukan "mempercayai") dan "mi" (bukan "mie").
- Tanda petik dan elipsis konsisten.

**Yang diperbaiki di draf 2:**

| Baris | Masalah | Perbaikan |
|---|---|---|
| `intro-03` | Logika berputar: "orang-orang mulai lupa … Karena semua itu terlupakan, naiklah kabut". Lagi pula, membaca terburu-buru bukan sesuatu yang "terlupakan". | "Dari kebiasaan itu, naiklah kabut kelabu yang dingin." |
| `kenal-wonosobo-03` | 57 kata dan 8 istilah baru dalam satu slide audio (Tambi, ongklok, carica, Lengger, bundengan, tudung, gimbal). | Dibagi ke `-02`, `-03`, dan `-04`, masing-masing ≤ 45 kata. |
| `kenal-temanggung-04` | 46 kata; kalimat terakhir bersyarat ("asal kamu membacanya…"). | Dipadatkan; diubah menjadi "Bacalah dengan teliti karena jawabannya sering ada di depan mata." |
| `kenal-magelang-03` | 45 kata, tiga topik. | Dipadatkan tanpa kehilangan benang Umbul Jumprit. |
| `dialog-magelang-15` | "ide pokok", padahal bank memakai "gagasan pokok". | "gagasan pokok"; petunjuk mgl-3 menulis "gagasan pokok (ide pokok)" agar sama dengan istilah buku siswa. |
| `dialog-wonosobo-04` | "meledak" | "meletus", kata yang lazim untuk gunung api. |

**Catatan EYD lain (tidak perlu diubah):**

- "Tapi" di dialog boleh, karena ragam lisan tokoh.
- "Ia" untuk "cahaya" di `intro-02` lazim dalam ragam sastra.
- "mengulek" ada di KBBI, tetapi di draf 2 kalimat itu diganti "menghaluskan bumbu" yang lebih umum.

### 3.2 Guru kelas 5

- **Beban dengar.** Satu baris naskah dibacakan sekitar 15–25 detik. Anak 10–11 tahun nyaman dengan 2–4 kalimat dan paling banyak 3 istilah baru per slide. Baris Kenali di draf 1 melampaui itu, karena satu baris Kenali sekarang mengangkut semua fakta yang dibutuhkan soal. Draf 2 membagi bebannya ke slide sebelah. Pola semua wilayah kini sama: slide `-04` dibuka dengan satu kesenian (kuda lumping, Topeng Ireng, Topeng Lengger/bundengan), lalu pesan Mbah.
- **Istilah ganda.** Siswa mendengar "jaran kepang", membaca "kuda lumping", lalu mencari "kuda kepang". Draf 2 menjelaskannya sekali di naskah: tarian = kuda lumping, yang juga disebut jaran kepang; kuda-kudaannya = kuda kepang dari anyaman bambu. Pemetaan ini sama dengan pemakaian di bank.
- **Contoh konkret.** Contoh fakta dan pendapat di `dialog-magelang-08` sudah sangat baik. Draf 2 menambahkan kata "opini", karena istilah itulah yang dipakai di kelas.
- **Variasi yang adil.** Siswa yang mendapat variasi berbeda harus menghadapi tuntutan yang setara. Di draf 2, setiap variasi mgl-3 berisi tepat satu pernyataan gagasan pokok, satu menghubungkan, satu mengklasifikasikan, dan dua fakta–pendapat. Imbangan kuncinya 3 : 2 di setiap variasi.

### 3.3 Kurikulum Merdeka

- **CP Bahasa Indonesia Fase C, elemen Membaca dan Memirsa** meminta siswa memahami dan menganalisis informasi teks tulis, visual, dan aural. Ketiga bentuk itu sudah ada di GELITA: bacaan, gambar, dan suara Mbah Kedu.
- **Gagasan pokok/ide pokok** adalah materi inti teks informasi di kelas 5, dan bank sudah mengukurnya. Draf 2 menyebut kedua istilah agar siswa yang belajar dengan buku mana pun mengenalinya.
- **Fakta dan opini** lazim diajarkan di kelas 5–6 untuk teks informasi. Di sebagian kerangka literasi, membedakan fakta–opini digolongkan ke level evaluasi. Penempatannya di Magelang tetap dapat dipertahankan, karena yang diukur adalah *membedakan* (menganalisis, C4), sedangkan menilai kredibilitas sumber tetap di Wonosobo (C5). Catat alasan ini di laporan penelitian.
- **Profil lulusan.** GELITA menyentuh beberapa dimensi profil lulusan pada Standar Kompetensi Lulusan 2025 (8 dimensi; perlu dicek ulang rujukan resminya):
  - **penalaran kritis**: memeriksa sumber dan memilah fakta–pendapat;
  - **kewargaan**: menjaga warisan dan menghormati Waisak;
  - **kesehatan**: kopi untuk orang dewasa, air belerang, gas kawah, udara dingin;
  - **komunikasi**: membaca dan mendengar teks.

  Pemetaan ini berguna untuk bab landasan teori laporan.

### 3.4 Taksonomi Bloom: audit ulang

Patokan audit sama dengan analisis 13 Oktober: **butir yang jawabannya bisa dicocokkan langsung dengan satu kalimat, atau dengan satu penanda permukaan, tidak bisa berada di atas C2.**

**1. Fakta–pendapat draf 1: label C4, tuntutan nyata C2.** Tabel kontingensinya di draf 1:

| | Isinya pendapat | Isinya fakta |
|---|---|---|
| Subjek "Ucapan …" | a03, b03, c02 | — |
| Subjek "Kalimat …" | — | a05, b05, c04 |

Aturan "Ucapan = pendapat, Kalimat = fakta" menjawab 6 dari 6 butir dengan benar. Ketiga pendapat juga memakai "Menurut saya" dan "paling", tepat seperti yang diajarkan petunjuk nomor 1. Draf 2 mematahkan pola ini:

| | Isinya pendapat | Isinya fakta |
|---|---|---|
| Subjek "Ucapan …" | a03, b03, c02 (c02 **tanpa** "menurut saya") | **b05** (biksu: "Perjalanan kami selalu dimulai dari Candi Mendut.") |
| Subjek "Kalimat …" | **a05** ("Menjaga Borobudur adalah tugas yang sangat mulia", tanpa penanda) | c04 |

Butir a05 sengaja memakai pendapat yang *disetujui semua orang*. Miskonsepsi yang paling umum pada anak adalah "kalau semua setuju, berarti fakta". Petunjuk nomor 1 kini berbunyi: *"Fakta dapat dibuktikan kebenarannya… Pendapat (opini) adalah penilaian atau perasaan seseorang… Hati-hati: ucapan seseorang bisa berisi fakta, dan pendapat tidak selalu diawali 'menurut saya'."* Imbangan kunci fakta–pendapat tetap 3 benar : 3 salah.

**2. Label lain yang dikoreksi di draf 2:**

| Butir | Draf 1 | Draf 2 | Alasan |
|---|---|---|---|
| mgl-2-a06 | C3 | C2 | Satu stupa berisi satu arca, jadi 72 stupa berisi 72 arca. Cukup memahami dua kalimat berurutan. |
| mgl-2-c06 | C3, tetapi bergantung pada pengetahuan awal | C3 sungguhan | Kegunaan kendil dan cobek kini tertulis, lalu diterapkan pada situasi baru (Dina membuat sambal). |
| wnb-4-a01–a03 | C4 | C3 | Teka-teki ini mencocokkan ciri (bahan, bentuk, kegunaan), dan cirinya kini juga dikenalkan narasi Kenali Wonosobo. Tetap di rentang C3–C4. |

**3. Yang sudah tepat di draf 1 dan dipertahankan:**

- mgl-3-b02, butir menghubungkan C4 yang baik: candi awal perjalanan lebih tua daripada candi akhir.
- wnb-2-c09: menyimpulkan "malam yang cerah" dari dua kalimat berjauhan.
- wnb-3-b06 menjadi C5 sungguhan setelah solusi sampah dihapus dari bacaan.
- Pernyataan salah Wonosobo kini berupa miskonsepsi yang masuk akal.

**4. C6 (mencipta)** belum ada, dan itu wajar untuk format pilihan. Saran untuk Balai Refleksi (tidak masuk draf 2, perlu perubahan aplikasi): satu pertanyaan terbuka, misalnya *"Tulis satu pesan untuk temanmu: bagaimana cara memeriksa kabar sebelum membagikannya?"* Jawaban dinilai dengan rubrik sederhana tiga tingkat.

### 3.5 Validitas pengukuran (psikometri)

Pernyataan Benar atau Salah **tidak diacak** (README bank soal). Karena itu, pola letak kunci ikut menjadi petunjuk.

| Variasi | Kunci draf 1 | Masalah | Kunci draf 2 |
|---|---|---|---|
| mgl-3-a | B S S B B | — | B B S B S |
| mgl-3-b | B B B S S | Gagasan pokok selalu B | **S** B B S B (gagasan pokok b01 kini SALAH) |
| mgl-3-c | B S B B S | — | B S B B S |
| wnb-3-a | B S S B S B | "sama-sama" selalu S | B **B** S B S **S** |
| wnb-3-b | S B S B S B | **Berselang-seling penuh** | S B B S S B (isi b03 dan b04 ditukar) |
| wnb-3-c | B S B B S S | — | B S B B S S |

Di draf 2:

- Pernyataan "sama-sama" kini 1 benar (wnb-3-a02) dan 2 salah.
- Butir gagasan pokok kini 2 benar dan 1 salah.
- Ada butir salah baru wnb-3-a06, berupa miskonsepsi nyata: "boleh meminum air Telaga Warna asalkan airnya tampak jernih". Butir ini memperkuat pesan kesehatan dan menjaga imbangan 3 : 3.

**Catatan lain:**

- Isi Rumpang Temanggung dengan satu pengecoh sudah baik. Peluang menebak rumpang terakhir kini 50%, bukan 100%. Pengecoh *hujan* menuntut pemahaman kata "bahkan", dan masih wajar untuk C2.
- Analisis Benar atau Salah tetap harus memakai **jawaban percobaan pertama**, sesuai catatan draf 1.
- **Susun Gambar a–j (putusan pemilik 17 Oktober 2026):** variasi a–c adalah set penelitian dan dinilai pada pengerjaan pertama; variasi d–j hanya muncul pada pengerjaan ulang. Sepuluh gambar berbeda kerumitan visualnya, jadi bila semuanya diacak sejak awal, skor puzzle antarsiswa tidak setara. Aturan ini tertulis di PETUNJUK, KAMUS_KOLOM, dan `review_note` d–j. Penerapannya perlu perubahan aplikasi (bagian 7).

### 3.6 Kesinambungan naskah dan bank

| Benang | Naskah draf 2 | Bank | Status |
|---|---|---|---|
| Umbul Jumprit → air suci Waisak | `kenal-temanggung-01`, `kenal-magelang-03` | tmg-1-f, tmg-2-b, mgl-3-b, mgl-1-i | Utuh. mgl-1-i01 kini memisahkan sumber air (Jumprit) dan api (Mrapen). |
| Borobudur tanpa semen | `dialog-magelang-06` (rumor "dibangun dengan semen"), `tuntas-magelang-01` (papan: "batu saling mengunci, tanpa semen") | mgl-3-a ("dibangun … tanpa semen. Batu-batunya disusun saling mengunci") | **Baru di draf 2.** Pertengkaran di cerita kini dituntaskan dengan fakta yang sama persis dengan bacaan soal. |
| Fakta vs pendapat | `intro-08`, `kenal-magelang-04`, `dialog-magelang-08` (+ "opini"), `-15` | 6 butir mgl-3, petunjuk nomor 1 | Selaras. |
| Gagasan pokok | `dialog-magelang-15` (kini "gagasan pokok") | mgl-3 a01/b01/c01, petunjuk nomor 2 | Selaras. |
| Kuda lumping / jaran kepang / kuda kepang | `kenal-temanggung-04` menjelaskan ketiganya | tmg-1-c/j, tmg-2-c, tmg-4-a01, mgl-4-a05 | Selaras. |
| Liyangan "pasir dan batu dari letusan" | `kenal-temanggung-03` | tmg-3-a, tmg-3-a02, tmg-1-d01 (kini sama) | Selaras. |
| Kuluk Topeng Ireng | `kenal-magelang-04` (baru) | mgl-2-b02, mgl-4-a02, wnb-4-a11 | Selaras. |
| Keranjang bernama ongklok | `kenal-wonosobo-03` ("direbus di ongklok, keranjang bambu kecil…") | wnb-2-b01/b02, wnb-4-a03 | Selaras. Draf 1 hanya menyebut "keranjang bambu kecil", padahal petunjuk wnb-4-a03 meminta nama alat itu. |
| Topeng Lengger dan bundengan | `kenal-wonosobo-04` | wnb-1-g, wnb-1-i, wnb-4-a01 | Selaras ("Tari Lengger" → "Topeng Lengger"). |
| Kawah: aman bila menjaga jarak | `dialog-wonosobo-04` ("meletus"), `tuntas-wonosobo-01` ("keadaan kawah normal… tetap menjaga jarak") | wnb-1-e, wnb-3-c | Selaras. |

**Catatan desain:** narasi Kenali wilayah bersifat pilihan. Petunjuk Cari Objek tetap memuat ciri fisik benda, jadi semua soal masih bisa dijawab tanpa narasi. Pertahankan prinsip ini.

### 3.7 Budaya Kedu

- **Legenda "dibangun dalam satu malam"** di draf 1 melekat pada kisah Roro Jonggrang (Prambanan dan Candi Sewu), bukan Borobudur. Meski disampaikan sebagai rumor yang keliru, anak bisa mengingatnya sebagai "legenda Borobudur". Draf 2 menggantinya dengan rumor "dibangun dengan semen", yang sekaligus menjadi jembatan ke bacaan mgl-3-a.
- **Topeng Ireng** tidak memakai topeng; nama itu sering dijelaskan sebagai singkatan dan merujuk pada rias wajah. Di game ada juga Topeng Lengger yang *memakai* topeng. Bila kelak ada butir tentang Topeng Ireng, hindari pernyataan yang menyiratkan topeng.
- **Waisak:** prosesi Mendut → Pawon → Borobudur, air suci dari Umbul Jumprit, dan api dari Mrapen (Grobogan). Bahasa penghormatan (menjaga ketenangan, memberi jalan) sudah tepat. Panduan gambar sudah melarang lampion terbang dan pengunjung yang menghalangi.
- **Ruwatan rambut gimbal** disebut dengan hormat sebagai "tradisi memotong rambut gimbal", tanpa gambar. Ini sejalan dengan panduan puzzle bagian 5.
- **Tembakau** (putusan pemilik 17 Oktober 2026) tetap disebut di `kenal-temanggung-02` sebagai fakta wilayah, tanpa butir dan tanpa gambar. Keputusan ini tercatat di log keputusan.
- **Candi Arjuna** tetap ditulis "di Dataran Tinggi Dieng", karena letaknya di Banjarnegara. Telaga Warna dan Sikunir berada di sisi Wonosobo (perlu dicek ulang untuk Telaga Warna).

### 3.8 Keramahan dan keselamatan anak

- **wnb-3-c06.** Draf 1 menulis "Berdasarkan bukti dalam bacaan, keluarga Dinda sebaiknya membatalkan wisata ke Dieng" dengan kunci SALAH. Anak yang memilih hati-hati jadi disalahkan. Draf 2 menulis "…harus membatalkan wisata **karena pengumuman resmi melarang pengunjung datang ke kawah**" (SALAH). Yang keliru kini premisnya, sebab pengumuman meminta menjaga jarak, bukan melarang datang. Tuntutan C5 tetap, dan pesan keselamatan tidak terbalik.
- **`tuntas-wonosobo-01`.** Lembaga gunung api memberi *status* aktivitas (Normal, Waspada, Siaga, Awas), bukan jaminan. Draf 2 menulis "Kata petugas, keadaan kawah normal, jadi tidak ada yang perlu mengungsi. Semua tetap menjaga jarak sesuai rambu." Jaka juga kini *berbuat*: "Aku mengajak warga bertanya kepada petugas resmi." Pelajaran Wonosobo jadi diperagakan tokohnya, bukan hanya diceritakan.
- **Kesetaraan gender.** Dina menggantikan "Ibu" di mgl-2-c06, sehingga tidak ada lagi pola "Ibu yang memasak". Tokoh bacaan kini perempuan: Sinta, Dinda, Rina, Dina, Bu Ratna, Kak Dewi; laki-laki: Raka, Bayu, Pak Darto, seorang biksu.
- **Kesehatan.** Pesan kopi untuk orang dewasa tetap ada. Air belerang tidak untuk diminum kini diuji dua arah (wnb-3-a06 baru). Penjelasan kentongan yang dipukul warga yang panik sudah baik.

### 3.9 Terjemahan Inggris

- Bank memakai "ongklok noodles", sedangkan naskah draf 1 memakai "mie ongklok". Draf 2 menyeragamkannya menjadi "ongklok noodles".
- Kutipan baru diterjemahkan dengan pola yang sama ("A monk explained, …", "Looking after Borobudur is a very noble duty").
- Kata pengecoh "agar" → "unless" tetap dicatat sebagai adaptasi, bukan terjemahan (sudah di catatan draf 1).

---

## 4. Daftar temuan dengan bukti

Semua temuan di bawah ini sudah ditangani di draf 2.

| No. | Kode | Temuan | Bukti |
|---|---|---|---|
| 1 | mgl-3 (6 butir) | Fakta–pendapat dapat dijawab dari pola "Ucapan/Kalimat" dan penanda "Menurut saya" | Tabel kontingensi 3.4 |
| 2 | mgl-3 a01/b01/c01 | Gagasan pokok selalu benar | Kunci 3.5 |
| 3 | wnb-3-b | Kunci berselang-seling penuh | Kunci 3.5 |
| 4 | wnb-3 a02/a03/b01 | "sama-sama" selalu salah | Catatan draf 1 bagian 6 |
| 5 | wnb-3-c06 | Hati-hati dianggap salah | 3.8 |
| 6 | mgl-3-a (bacaan) | Kutipan memutus "dianggap berharga" → "Karena sangat berharga" | Teks bacaan draf 1 |
| 7 | mgl-3-a02 | "kurang dari seribu": hubungan antarparagraf dibuat-buat | — |
| 8 | mgl-2-c06 | Kegunaan cobek tidak ada di bacaan; stereotip "Ibu" | — |
| 9 | mgl-2-a06 | Label C3, tuntutan C2 | 3.4 |
| 10 | mgl-1-i01 | "membawa api dan air suci dari Umbul Jumprit" | Bandingkan mgl-3-b yang sudah tepat |
| 11 | tmg-3-a, tmg-1-d01 | Masih "material letusan" | Naskah sudah "pasir dan batu" |
| 12 | wnb-4-a01–a03 | Label C4 untuk mencocokkan ciri | 3.4 |
| 13 | `intro-03` | Logika berputar | 3.1 |
| 14 | `kenal-*` | Tiga baris 45–57 kata | 2.1 |
| 15 | `kenal-temanggung-04` | Tiga nama kuda lumping belum dipetakan | 3.2 |
| 16 | `kenal-wonosobo-03` | Nama "ongklok" untuk keranjang tidak disebut | 3.6 |
| 17 | `dialog-temanggung-07` | Panah menunjuk jembatan rusak masih membingungkan | Draf 2: Jaka hanya membaca kata "Jembatan" dan panahnya, lalu Mbah memintanya membaca seluruh tulisan |
| 18 | `dialog-magelang-06` | Legenda Prambanan dipakai untuk Borobudur | 3.7 |
| 19 | `dialog-magelang-08/15` | Belum ada "opini"; "ide pokok" ≠ "gagasan pokok" | 3.3 |
| 20 | `dialog-wonosobo-04`, `tuntas-wonosobo-01` | "meledak"; jaminan petugas; Jaka pasif | 3.8 |
| 21 | Naskah EN | "mie ongklok" | 3.9 |

---

## 5. Perubahan di draf 2

### 5.1 Naskah: 15 baris disentuh

Baris yang disentuh: `intro-03`, `kenal-temanggung-02`, `kenal-temanggung-04`, `kenal-magelang-03`, `kenal-magelang-04`, `kenal-wonosobo-02` (judul ikut berubah menjadi *Candi, Telaga, dan Tradisi*), `kenal-wonosobo-03`, `kenal-wonosobo-04`, `dialog-temanggung-07`, `dialog-magelang-06`, `dialog-magelang-08`, `dialog-magelang-15`, `dialog-wonosobo-04`, `tuntas-magelang-01`, `tuntas-wonosobo-01`.

Dibanding naskah aplikasi, kini **32 baris** berbeda, dan EN berbeda pada 28 di antaranya. Teks lengkapnya ada di `naskah-cerita/naskah-cerita.md`; daftar lama → baru ada di catatan revisi bagian 7.

### 5.2 Bank soal

| Butir | Perubahan | Kunci |
|---|---|---|
| Bacaan mgl-3-a | Kutipan Pak Darto dipindah sebelum kalimat UNESCO; ditambah kalimat pendapat tanpa penanda di akhir paragraf 2 | — |
| mgl-3-a02 | "Relief yang tidak boleh disentuh pengunjung berjumlah lebih dari dua ribu panel." | salah → **benar** |
| mgl-3-a05 | Kalimat "Menjaga Borobudur adalah tugas yang sangat mulia" adalah fakta. | benar → **salah** |
| Bacaan mgl-3-b | Ditambah: Seorang biksu menjelaskan, "Perjalanan kami selalu dimulai dari Candi Mendut." | — |
| mgl-3-b01 | "Paragraf pertama terutama menjelaskan perjalanan umat Buddha saat Waisak." | benar → **salah** |
| mgl-3-b05 | "Ucapan biksu bahwa perjalanan mereka dimulai dari Candi Mendut adalah fakta." | salah → **benar** |
| Bacaan mgl-3-c | Kak Dewi tanpa "Menurut saya" | — |
| Petunjuk mgl-3 nomor 1 dan 2 | Definisi fakta/pendapat (opini) dengan peringatan pola; "gagasan pokok (ide pokok)" | — |
| mgl-2-c06 | Kegunaan kendil dan cobek tertulis; tokoh Dina | cobek |
| mgl-2-a06 | C3 → C2 | 72 |
| mgl-1-i01 | Air dari Umbul Jumprit, api dari Mrapen | — |
| Bacaan tmg-3-a, tmg-3-a02, tmg-1-d01 | "pasir dan batu dari letusan" | tetap |
| wnb-3-a02 | "Kedua kabar sama-sama membicarakan air Telaga Warna, tetapi isinya bertentangan." | salah → **benar** |
| wnb-3-a06 | "…pengunjung yang haus boleh meminum air Telaga Warna asalkan airnya tampak jernih." | benar → **salah** |
| wnb-3-b03 ↔ b04 | Isi ditukar | tetap per isi |
| wnb-3-c06 | "…harus membatalkan wisata karena pengumuman resmi melarang pengunjung datang ke kawah." | salah |
| wnb-4-a01–a03 | C4 → C3 | — |
| PETUNJUK, KAMUS_KOLOM, d–j | Aturan Susun Gambar: a–c dinilai, d–j untuk pengerjaan ulang | — |

Semua butir yang disentuh berstatus `needs_verification`, dengan `review_note` diawali "Revisi v2 (17 Okt 2026)". Catatan lama tetap tersimpan setelah "Sebelumnya:".

**Pemeriksaan otomatis draf 2:** imbangan kunci per variasi (Temanggung 3 butir, Magelang 3 : 2, Wonosobo 3 : 3), tidak ada variasi berselang-seling penuh, tabel kontingensi fakta–pendapat, satu `___` per rumpang yang tidak di awal, jawaban unik, pengecoh ≠ jawaban, Bloom di rentang `DifficultyRules`, dan semua kutipan di pernyataan ada di bacaan. Hasilnya **0 kesalahan**. Daftar pilihan (validasi data) dan catatan judul kolom tetap utuh.

---

## 6. Fakta yang wajib diverifikasi peneliti

| Fakta | Dipakai di | Sumber yang disarankan |
|---|---|---|
| Arca Nandi berada di dalam ruang Candi Pringapus | tmg-1-b01, tmg-3-b, tmg-4-a03, `kenal-temanggung-03` | BPK Wilayah X; data Cagar Budaya Kemendikbud |
| Api Waisak diambil dari Mrapen, Grobogan | mgl-1-i01 | Walubi/Permabudhi; liputan Waisak nasional |
| Jadwal Pasar Papringan (Minggu Wage dan Minggu Pon) dan lokasinya | tmg-1-i01 | Pengelola Pasar Papringan (Spedagi) |
| Tahun penetapan Topeng Lengger sebagai WBTb | wnb-1-i01 | Laman resmi Warisan Budaya Takbenda Kemendikbud |
| Kebun teh Tambi berdiri sejak 1865 | wnb-1-h01, wnb-4-a04 | PT Tambi; Pemkab Wonosobo |
| Telaga Warna berada di sisi Kabupaten Wonosobo | wnb-3-a, `dialog-wonosobo-09` | BPS/Pemkab Wonosobo |
| Arah membaca relief (searah jarum jam, mulai sisi timur) | mgl-1-d01 | Balai Konservasi Borobudur |
| Aturan pendakian Gunung Tidar (jalur tangga, bawa pulang sampah) | mgl-3-c | Pengelola kawasan Gunung Tidar |
| Ikan uceng sebagai makanan khas Temanggung | `kenal-temanggung-02`, tmg-1-h01, tmg-4-a04 | Pemkab Temanggung |

---

## 7. Kebutuhan sistem untuk integrasi (cabang terpisah)

Draf 2 **belum** dipasang di aplikasi. Uji `gelita:bank:import --dry-run` pada basis data SQLite lokal (bukan server produksi) memberi hasil berikut.

- **Draf 1 dan draf 2** sama-sama ditolak dengan 6 galat: skill `membedakan_fakta_pendapat` tidak dikenal.
- **Bila skill itu sementara diganti**, muncul 6 galat berikutnya: Susun Gambar `tmg-1`, `mgl-1`, dan `wnb-1` punya 10 variasi (aturan: tepat 3), dan `tmg-2` punya 1 pengecoh (aturan: 0).
- **Tidak ada galat lain.** Draf 2 hanya menambah peringatan `needs_verification` untuk butir yang disentuh.

Yang perlu diubah:

| No. | Hal | Lokasi |
|---|---|---|
| 1 | Susun Gambar boleh lebih dari 3 variasi (a–j) | `app/Libraries/BankRules.php` (`VARIANTS`, pemeriksaan "tepat 3 variasi"); `app/Libraries/BankWorkbookGuide.php` (`VARIANT_GROUPS`) |
| 2 | Skill `membedakan_fakta_pendapat` sah untuk tingkat sedang, beserta labelnya | `app/Libraries/DifficultyRules.php` (`SKILLS`, aturan `sedang`) |
| 3 | Pengecoh Isi Rumpang Temanggung 1 | `DifficultyRules` (`mudah.reading.distractors`) |
| 4 | Kode soal eksplisit: a06, c08, dan c09 disisipkan di tengah variasi; a04/c04/c05/c07 diistirahatkan | `app/Libraries/BankSource.php` membuat kode dari urutan (`sprintf('%s-%s%02d')`); perlu dukungan kunci eksplisit dan `is_active = 0` di `docs/bank-soal/data/*.php` |
| 5 | Pengerjaan pertama Susun Gambar hanya dari a–c; d–j untuk pengerjaan ulang | `app/Models/ChallengeItemModel.php::pickVariant()` |
| 6 | Laporan dan ekspor: label skill baru; variasi a–j | `app/Libraries/ExportLabels.php` (teks "a, b, atau c"), laporan per keterampilan |
| 7 | Naskah 32 baris → `app/Database/Seeds/data/story.php`; perbarui juga lewat editor dialog di basis data yang sudah terpasang | `StoryDataTest`, `RegionStoryTest` |
| 8 | Tes yang mengunci jumlah dan isi bank | `BankKelas5DataTest`, `BankWorkbookGuideTest`, `BankImportCommandTest`, `SystemResetTest`, `tests/_support/Database/GradeFiveWorld.php` |
| 9 | Rekaman ulang 32 baris ID dan 28 baris EN; rekaman yang berubah kembali ke draft | Panel Rekaman narasi. Audio katalog dibekukan (`gelita-katalog/AGENTS.md`), jadi brief audio diperbarui atas permintaan terpisah. |

Bank kata Isi Rumpang sudah menangani pengecoh secara umum (`ChallengeService::wordBank`), jadi tidak perlu diubah. Pengambilan data penelitian belum dimulai (`docs/PROGRESS.md`), sehingga perubahan kunci di tempat masih aman.

---

## 8. Hal yang masih terbuka

- **Jawaban "72" di mgl-2-a06** tampil sebagai angka di bank kata, sedangkan jawaban hitungan lain ("sembilan") berupa kata. Dibiarkan karena tetap jelas bagi siswa. Bila uji coba menunjukkan janggal, ganti kalimatnya agar jawabannya berupa kata.
- **Pola tmg-3** (B-S-B / S-B-S) tidak bisa dihindari dengan tiga pernyataan. Risikonya kecil.
- **Keterangan Susun Gambar tidak diperiksa.** Bila skor puzzle dipakai sebagai indikator budaya, pertimbangkan satu pertanyaan singkat tentang keterangannya (catatan draf 1 bagian 6).
- **C6 di Balai Refleksi** (bagian 3.4) perlu perubahan aplikasi.
- **Uji keterbacaan dengan 3–5 siswa kelas 5** sebelum pengambilan data, terutama untuk baris Kenali dan butir fakta–pendapat: minta mereka menjelaskan alasan jawabannya (*think-aloud*).
