# Catatan Revisi GELITA — Oktober 2026

Catatan ini merangkum revisi bank soal kelas 5, naskah cerita, dan panduan gambar puzzle berdasarkan keputusan tim. Berkas hasil revisi:

| Berkas | Isi revisi |
|---|---|
| `bank-soal-kelas5.xlsx` | Fakta–pendapat masuk Benar atau Salah Magelang, butir Magelang dan Wonosobo diperbaiki, label Bloom dikoreksi, satu pengecoh di Isi Rumpang Temanggung, 21 variasi Susun Gambar baru, warna Telaga Warna diseragamkan |
| `naskah-cerita.md` | 27 baris disentuh (bahasa, konsistensi dengan bank, pesan keselamatan kawah, pengenalan benda yang dibutuhkan soal); tetap 88 baris dengan kode yang sama |
| `panduan-gambar-puzzle-gelita.md` | Gaya mengikuti katalog, 1200 × 1200 px WebP, kode gambar baru, tinjauan dan pengganti gambar katalog yang ambigu |

Semua butir yang diubah atau ditambahkan di bank berstatus **`needs_verification`** dengan alasan perubahan di kolom `review_note`, supaya peneliti bisa memeriksanya satu per satu sebelum pengambilan data.

---

## 1. Keputusan yang dijalankan

| Keputusan tim | Yang dilakukan |
|---|---|
| Fakta dan pendapat diuji di Magelang, masuk ke tantangan yang ada (Benar atau Salah), menggantikan soal yang lemah, dengan kode skill baru | Kode skill baru **`membedakan_fakta_pendapat`**. Setiap bacaan mgl-3 diberi satu kalimat pendapat tokoh ("Menurut saya, …"). Enam butir lemah diganti menjadi butir fakta–pendapat (dua per variasi). |
| Perbaiki soal dan label Bloom Magelang dan Wonosobo | Ke-11 label yang jelas terlalu tinggi dan ke-16 label yang diperdebatkan di analisis sudah ditangani: diubah labelnya, ditulis ulang, atau diganti butir baru. |
| Warna Telaga Warna memakai "kekuningan" | Bacaan wnb-3-a dan keterangan wnb-1-a01: "hijau, biru, atau kekuningan", sama dengan `kenal-wonosobo-02`. |
| "Kawahnya aman" disesuaikan dengan bank | `tuntas-wonosobo-01`: kawah tidak akan meledak besok pagi, tetapi semua tetap menjaga jarak sesuai rambu. |
| Isi Rumpang Temanggung diberi satu pengecoh | `distractor_count` tmg-2 = 1; pengecoh a *tandus*, b *hujan*, c *kayu*. Perintah dan petunjuk ikut disesuaikan. |
| Pernyataan salah Wonosobo tidak boleh bisa ditebak dari kata ekstrem | Empat pernyataan salah ditulis ulang menjadi miskonsepsi yang masuk akal; petunjuk "waspadai kata pasti" diarahkan ke pesan berantai. Dua pernyataan salah lain ikut diperhalus. |
| 24 baris naskah diperbaiki secara mendalam | 27 baris disentuh (bagian 3). |
| Susun Gambar boleh lebih dari 3 variasi; kode gambar baru mengikuti pola kode soal | Variasi d–j untuk tmg-1, mgl-1, wnb-1; `item_key` `{tantangan}-{variasi}01`, gambar `challenge.item.{tantangan}-{variasi}01`. |
| Gaya gambar mengikuti katalog; ukuran dan format mengikuti panduan katalog | Prompt memakai arahan aktif katalog 2026-10-11 dan templatnya; semua gambar Susun Gambar tepat 1200 × 1200 px WebP. |
| Rekomendasi pengganti gambar katalog yang ambigu, beserta prompt | Panduan bagian 6: 2 wajib ganti, 2 disarankan ganti, 5 boleh dipakai; prompt pengganti P01–P03 tiap wilayah. |

---

## 2. Bank soal

### 2.1 Angka sebelum dan sesudah

| | Sebelum | Sesudah |
|---|---|---|
| Baris soal | 126 | 151 (147 aktif, 4 diistirahatkan) |
| Soal dinilai + benda pengecoh | 108 + 18 | 129 + 18 |
| Variasi | 30 | 51 (Susun Gambar 10 per wilayah) |
| Kata pengecoh Isi Rumpang | 12 | 15 |
| Kode skill Benar atau Salah | 9 | 10 |
| Baris DAFTAR_MEDIA | 45 | 66 |

**Sebaran label Bloom (butir aktif berlabel):**

| Level | Sebelum | Sesudah |
|---|---|---|
| C1 | 12 | 12 |
| C2 | 26 | 25 |
| C3 | 23 | 28 |
| C4 | 26 | 21 |
| C5 | 12 | 13 |
| **HOTS (C4–C5)** | 38 dari 99 (38%) | 34 dari 99 (34%) |

Persentase HOTS turun di atas kertas, tetapi sekarang jujur: di analisis, label lama setelah dikoreksi hanya setara sekitar 20% HOTS. Revisi menaikkan tuntutan butir yang sebelumnya terlalu mudah (misalnya wnb-2-b02, wnb-3-b06, wnb-3-c04), menurunkan label yang tidak didukung isinya (misalnya konjungsi dalam satu kalimat C4 → C3), dan menambah enam butir C4 fakta–pendapat. Semua label tetap berada di rentang PETUNJUK per wilayah.

### 2.2 Benar atau Salah Magelang: fakta dan pendapat

Bacaan yang ditambah:

- **mgl-3-a:** *Seorang pemandu, Pak Darto, berkata, "Menurut saya, Borobudur adalah candi paling indah di dunia."*
- **mgl-3-b:** *Bu Ratna, warga Borobudur, berkata, "Menurut saya, suasana perjalanan Waisak adalah yang paling damai."*
- **mgl-3-c:** *Pendaki diminta tetap berjalan di jalur tangga dan membawa pulang sampahnya. Seorang pendaki, Kak Dewi, berkata, "Menurut saya, pemandangan dari Gunung Tidar paling indah saat pagi."* Kalimat "taruna … perwira TNI Angkatan Darat" disederhanakan menjadi "calon perwira tentara".

Komposisi setiap variasi sekarang: 1 gagasan pokok (C2), 1 menghubungkan informasi (C4), 1 mengklasifikasikan tindakan (C3), 2 fakta–pendapat (C4). Butir fakta–pendapat disusun agar tidak bisa dijawab dengan pola: kata "fakta" di pernyataan muncul 2 kali benar dan 2 kali salah, "pendapat" 1 benar dan 1 salah; subjek "Ucapan …" (kutipan pendapat tokoh) dan "Kalimat …" (kutipan fakta dari bacaan) masing-masing punya kunci benar dan salah. Setiap variasi tetap 3 benar : 2 salah. Empat kunci berubah (a02, a05, b03, b05); butir diubah di tempat karena pengambilan data belum dimulai, dan alasannya dicatat di `review_note`.

| Butir | Sebelum | Sesudah | Kunci | Bloom · skill |
|---|---|---|---|---|
| `mgl-3-a01` | (teks sama) | (teks sama) | benar | C2 · gagasan_pokok |
| `mgl-3-a02` | Borobudur harus dijaga bersama karena dianggap berharga bagi seluruh umat manusia. | Panel relief yang tidak boleh disentuh pengunjung jumlahnya kurang dari seribu. | benar → salah | C4 · menghubungkan_informasi |
| `mgl-3-a03` | Menyentuh relief dengan tangan termasuk tindakan menjaga Borobudur. | Ucapan Pak Darto bahwa Borobudur adalah candi paling indah di dunia merupakan fakta. | salah | C3 · mengklasifikasikan_tindakan → **C4 · membedakan_fakta_pendapat** |
| `mgl-3-a04` | (teks sama) | (teks sama) | benar | C3 · mengklasifikasikan_tindakan |
| `mgl-3-a05` | Batu-batu Borobudur direkatkan dengan semen. | Kalimat "Pada tahun 1991, UNESCO menetapkan Borobudur sebagai Warisan Dunia" adalah fakta. | salah → benar | C2 · menghubungkan_informasi → **C4 · membedakan_fakta_pendapat** |
| `mgl-3-b01` | (teks sama) | (teks sama) | benar | C2 · gagasan_pokok |
| `mgl-3-b02` | Dalam perjalanan Waisak, umat Buddha melewati Candi Pawon sebelum tiba di Candi Borobudur. | Menurut bacaan, candi tempat perjalanan Waisak dimulai diperkirakan lebih tua daripada candi tempat perjalanan itu berakhir. | benar | C4 · menghubungkan_informasi |
| `mgl-3-b03` | Air suci untuk perayaan Waisak di Magelang diambil dari wilayah Wonosobo. | Ucapan Bu Ratna tentang suasana perjalanan Waisak adalah pendapat. | salah → benar | C4 · menghubungkan_informasi → **C4 · membedakan_fakta_pendapat** |
| `mgl-3-b04` | (teks sama) | (teks sama) | salah | C3 · mengklasifikasikan_tindakan |
| `mgl-3-b05` | Berbicara pelan saat melihat umat beribadah termasuk sikap menghormati perayaan agama lain. | Kalimat "Air suci itu diambil dari Umbul Jumprit di Temanggung" adalah pendapat. | benar → salah | C3 · mengklasifikasikan_tindakan → **C4 · membedakan_fakta_pendapat** |
| `mgl-3-c01` | (teks sama) | (teks sama) | benar | C2 · gagasan_pokok |
| `mgl-3-c02` | Cerita bahwa Pulau Jawa dipaku di Gunung Tidar adalah penjelasan ilmiah. | Ucapan Kak Dewi tentang pemandangan dari Gunung Tidar adalah fakta. | salah | C2 · menghubungkan_informasi → **C4 · membedakan_fakta_pendapat** |
| `mgl-3-c03` | Mendaki tangga Gunung Tidar dapat digolongkan sebagai kegiatan berolahraga. | Turun lewat jalur tangga sambil membawa pulang bungkus makanan termasuk tindakan yang sesuai aturan pendakian. | benar | C2 · mengklasifikasikan_tindakan → **C3 · mengklasifikasikan_tindakan** |
| `mgl-3-c04` | Hari ulang tahun Kota Magelang berasal dari tanggal Prasasti Mantyasih. | Kalimat "Kota Magelang berulang tahun setiap tanggal 11 April" adalah fakta. | benar | C4 · menghubungkan_informasi → **C4 · membedakan_fakta_pendapat** |
| `mgl-3-c05` | (teks sama) | (teks sama) | salah | C4 · menghubungkan_informasi |

Petunjuk mgl-3 nomor 1 sekarang menjelaskan fakta dan pendapat; perintah dan kartu misi mgl-3 menyebut fakta dan pendapat.

### 2.3 Isi Rumpang Magelang

| Butir | Sebelum | Sesudah | Kunci | Bloom · skill |
|---|---|---|---|---|
| `mgl-2-a02` | Di teras bundar berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng. Bangunan berbentuk lonceng yang paling besar ada di puncak candi dan disebut ___ induk. ¶ Dinding candi dihiasi ribuan pahatan gambar yang menceritakan sebuah kisah. | Di teras bundar berdiri 72 stupa berlubang. Stupa adalah bangunan berbentuk lonceng. Bangunan berbentuk lonceng yang paling besar ada di puncak candi dan disebut ___ induk. | stupa | C3 → **C2** |
| `mgl-2-a04` | (teks sama) | **diistirahatkan** (`is_active` = 0) | dinding | C3 |
| `mgl-2-a06` | —(baru) | Di dalam setiap stupa berlubang itu duduk sebuah arca Buddha. Jadi, di teras bundar ada ___ arca Buddha di dalam stupa. ¶ Dinding candi dihiasi ribuan pahatan gambar yang menceritakan sebuah kisah. | 72 | C3 |
| `mgl-2-b03` | Karena itu, para penari harus berlatih bersama supaya ___. | Kalau ada satu penari yang bergerak lebih cepat daripada teman-temannya, tarian mereka menjadi tidak ___. | kompak | C3 |
| `mgl-2-c02` | (teks sama) | (teks sama) | pengganti | C3 → **C2** |
| `mgl-2-c04` | (teks sama) | **diistirahatkan** (`is_active` = 0) | dapur | C3 |
| `mgl-2-c06` | —(baru) | Ketiga benda itu dipakai di dapur. Kalau Ibu ingin mengulek sambal, ia memakai ___. | cobek | C3 |

Pengecoh mgl-2-c *sawah* diganti *kuali* (lawan jawaban baru *cobek*). Rujukan mgl-2-a01 ditambah hitungan 6 teras persegi + 3 teras bundar.

### 2.4 Isi Rumpang Wonosobo

Sembilan butir konjungsi di dalam satu kalimat (a01, a02, a04, b03, b04, b06, c01, c02, c04) diubah labelnya C4 → C3. Butir yang ditulis ulang atau diganti:

| Butir | Sebelum | Sesudah | Kunci | Bloom · skill |
|---|---|---|---|---|
| `wnb-2-b01` | Mi ongklok adalah makanan khas Wonosobo. Mi kuning, kol, dan daun kucai dimasukkan ke dalam keranjang bambu kecil, ___ keranjang itu dicelupkan berulang kali ke air mendidih. | Mi ongklok adalah makanan khas Wonosobo. Mi kuning, kol, dan daun kucai dimasukkan ke dalam ongklok, yaitu keranjang bambu kecil, ___ keranjang itu dicelupkan berulang kali ke air mendidih. | lalu | C3 |
| `wnb-2-b02` | Keranjang itu bernama ongklok. Jadi, nama mi ongklok berasal dari nama ___ untuk merebusnya. | Sekarang kamu tahu bahwa nama mi ongklok berasal dari nama ___ untuk merebusnya. | alat | C4 |
| `wnb-2-b05` | Jika ingin membuat mi ongklok sendiri, kamu perlu menyiapkan mi kuning, kol, dan daun ___. | Rina ingin memasak mi ongklok. Di rumahnya hanya ada mi kuning dan kol, jadi ia masih harus membeli daun ___. | kucai | C3 |
| `wnb-2-c05` | (teks sama) | **diistirahatkan** (`is_active` = 0) | racun | C3 |
| `wnb-2-c07` | (teks sama) | **diistirahatkan** (`is_active` = 0) | dingin | C3 |
| `wnb-2-c08` | —(baru) | Dalam bahasa Jawa, upas berarti racun. Es itu dijuluki begitu karena bisa membuat tanaman kentang ___ lalu mati. | layu | C3 |
| `wnb-2-c09` | —(baru) | Dari semua keterangan itu, embun upas paling mudah terbentuk setelah malam yang ___. | cerah | C4 |

Pengecoh wnb-2-c *mencair* diganti *berawan* (lawan jawaban baru *cerah*). Pengecoh "agar" yang diterjemahkan "unless" dibiarkan; catat sebagai adaptasi, bukan terjemahan.

### 2.5 Benar atau Salah Wonosobo

Bacaan wnb-3-b tidak lagi menulis solusi sampah (diganti fakta "Di puncak bukit tidak ada tempat sampah"), sehingga wnb-3-b06 sungguh menuntut C5. Tokoh wnb-3-c diganti dari Dimas menjadi **Dinda** agar ada tokoh perempuan selain Sinta.

| Butir | Sebelum | Sesudah | Kunci | Bloom · skill |
|---|---|---|---|---|
| `wnb-3-a05` | Raka sebaiknya langsung menyebarkan pesan berantai itu agar teman-temannya ikut awet muda. | Bertanya kepada teman sekelas akan lebih membantu Raka daripada bertanya kepada guru. | salah | C5 · menilai_pilihan |
| `wnb-3-b05` | Membuat jalan pintas lewat ladang warga agar cepat sampai di puncak adalah pilihan yang baik. | Karena wisatawan di Sembungan sangat banyak, keluar sebentar dari jalur untuk berfoto bukanlah masalah. | salah | C5 · menilai_pilihan |
| `wnb-3-b06` | (teks sama) | (teks sama) | benar | C5 · menentukan_solusi |
| `wnb-3-c02` | Pesan berantai lebih dapat dipercaya karena isinya membuat foto lebih bagus. | Pesan berantai itu dapat dipercaya karena sudah banyak beredar pagi itu. | salah | C5 · menilai_pilihan |
| `wnb-3-c04` | Rambu dan pagar pembatas dipasang karena gas di sekitar kawah dapat berbahaya. | Rambu dan pagar dari petugas lebih pantas diikuti daripada pesan berantai, karena petugas mengetahui bahaya gas di kawah. | benar | C4 · membandingkan_informasi → **C5 · menilai_pilihan** |
| `wnb-3-c05` | Berfoto di belakang pagar pembatas adalah pilihan yang tidak aman. | Keluarga Dinda boleh mendekati kawah asalkan cuaca pagi itu cerah. | salah | C5 · menilai_pilihan |
| `wnb-3-c06` | Berdasarkan bukti dalam bacaan, keluarga Dimas sebaiknya membatalkan wisata karena semua kawah pasti berbahaya. | Berdasarkan bukti dalam bacaan, keluarga Dinda sebaiknya membatalkan wisata ke Dieng. | salah | C5 · menentukan_solusi |

Imbangan benar/salah tetap 3 : 3 di setiap variasi.

### 2.6 Isi Rumpang Temanggung

`distractor_count` = 1. Pengecoh: a *tandus*/*barren* (lawan *subur*), b *hujan*/*rainy* (lawan *kemarau*), c *kayu*/*wooden* (lawan *bambu*). Perintah: "Ada satu kata yang tidak terpakai." Petunjuk nomor 3: "Ada satu kata pengecoh. Setiap kata hanya dipakai satu kali."

### 2.7 Susun Gambar

- 21 variasi baru (d–j untuk setiap wilayah) dengan judul ID/EN di sheet `variants`, keterangan ID/EN di `items`, catatan gambar di `media_note`, dan rujukan di `reference_source`.
- Keterangan wnb-1-a01 memakai warna "hijau, biru, atau kekuningan".
- Catatan gambar (`media_note`) tmg-1-a01, mgl-1-a01, wnb-1-a01, wnb-1-b01, dan wnb-1-c01 diperbarui. Impor hanya mengisi slot yang catatannya kosong, jadi untuk slot lama ubah juga di panel Gambar & mockup.
- DAFTAR_MEDIA: format Susun Gambar menjadi "persegi tepat 1200 × 1200 px, WebP, latar penuh (tidak transparan)"; 21 baris baru; kolom catatan memuat putusan tinjauan untuk sembilan gambar katalog.

### 2.8 Sheet petunjuk dan validasi

- PETUNJUK: ringkasan jumlah (B3), jumlah baris per sheet, aturan variasi (Susun Gambar boleh lebih dari tiga), aturan Temanggung (1 pengecoh), keterampilan Magelang (termasuk `membedakan_fakta_pendapat`), dan format gambar.
- KAMUS_KOLOM: `distractor_count` Temanggung 1, variasi a … j untuk Susun Gambar, daftar skill, ukuran gambar puzzle, dan arti `needs_verification`.
- Daftar pilihan (validasi data): variasi `a`–`j` di sheet `variants` dan `items`; skill `membedakan_fakta_pendapat` di `items`. Catatan judul kolom dan kotak bantuan ikut diperbarui; kotak bantuan tetap di bawah batas 250 huruf Excel.
- Pemeriksaan otomatis atas aturan bank (jumlah rumpang dan pernyataan per variasi, jumlah pengecoh, jawaban unik, pengecoh ≠ jawaban, satu `___` yang tidak di awal, rentang Bloom dan skill per wilayah, kutipan di pernyataan ada di bacaan, imbangan benar/salah, kode media terdaftar): **0 kesalahan**.

---

## 3. Naskah cerita

Kode baris, tokoh, pose, efek, dan jumlah baris (88) tidak berubah. Rata-rata panjang kalimat ID tetap pendek (sekitar 7 kata), dan kalimat 29 kata di `intro-02` sudah dipecah. Di bawah ini daftar baris yang diubah; teks lama ada di berkas asli.

| Baris | Alasan | Teks ID baru | EN |
|---|---|---|---|
| `intro-01` | Lima nama gunung muncul sebelum subjek; "terbentang" kurang akrab | Dahulu kala, ada dataran luas bernama Dataran Kedu. Di sekelilingnya berdiri Gunung Sindoro, Sumbing, Merapi, Merbabu, dan Perbukitan Menoreh. Setiap malam, desa-desanya diterangi cahaya lembut yang disebut Cahaya Kedu. | diubah |
| `intro-02` | Satu kalimat 29 kata dipecah | Cahaya itu lahir dari ilmu dan budaya. Ia datang dari dongeng para kakek dan nenek, dari relief Candi Borobudur, dan dari tembang para petani. Ia juga menyala setiap kali seseorang membaca dengan teliti. Cahaya itu disimpan di dalam sebuah lentera tua bernama Lentera Kedu. | diubah |
| `intro-03` | "Kelupaan" kata benda abstrak | Namun perlahan, orang-orang mulai lupa. Mereka membaca dengan terburu-buru, memercayai kabar tanpa memeriksanya, dan meninggalkan cerita para leluhur. Karena semua itu terlupakan, naiklah kabut kelabu yang dingin. Namanya Kabut Lupa. | diubah |
| `intro-04` | "Membelit" kurang umum | Pada suatu malam, Kabut Lupa melilit Lentera Kedu. Kacanya retak, apinya bergetar, lalu cahayanya pecah! Dua belas Serpihan Cahaya melesat ke langit dan jatuh di Temanggung, Magelang, dan Wonosobo. | tetap |
| `intro-05` | Bertentangan dengan Mbah yang kemudian ikut ke tiga wilayah | Aku Mbah Kedu, penjaga lentera ini. Kakiku sudah tua, jadi aku hanya bisa menemanimu pelan-pelan. Setiap kali cahayanya meredup, tubuhku ikut melemah. Aku butuh anak yang berani dan mau membaca dengan sungguh-sungguh. | diubah |
| `intro-08` | "Nilailah" bisa disangka angka/skor | Ingat tiga kunci ini, Le. Di Temanggung, temukan informasi yang tertulis jelas. Di Magelang, hubungkan informasi dan bedakan fakta dari pendapat. Di Wonosobo, periksa dan timbang setiap kabar sebelum kamu memercayainya. | diubah |
| `peta-01` | "Lembah" dan "dataran" untuk tempat yang sama | Inilah Dataran Kedu. Dulu dataran ini menyimpan cahaya ilmu dan budaya. Sekarang lihatlah... Kabut Lupa menutupi hampir semuanya. | diubah |
| `kenal-temanggung-01` | Benang antarwilayah: Umbul Jumprit, hulu Sungai Progo (dipakai tmg-2-b dan mgl-3-b) | Inilah Temanggung, Le. Tanahnya diapit dua gunung, Sindoro dan Sumbing. Kalau pagi cerah, kedua puncaknya tampak berdiri berdampingan, seperti kakak dan adik yang saling menjaga. Di lereng Sindoro ada mata air Umbul Jumprit, hulu Sungai Progo. | diubah |
| `kenal-temanggung-02` | Ikan uceng dikenalkan (target tmg-4-a04; petunjuk tmg-4 #2 merujuk Kenali Temanggung) | Tanah di lereng gunung ini sangat subur. Para petani menanam tembakau, kopi, dan sayuran. Kopi dari lereng Sindoro dan Sumbing terkenal harum sampai ke luar daerah. Di sungainya yang jernih hidup ikan uceng, ikan kecil yang menjadi makanan khas Temanggung. | diubah |
| `kenal-temanggung-03` | "Material letusan" istilah teknis; arca Nandi dikenalkan (target tmg-4-a03) | Temanggung juga menyimpan jejak masa lalu. Ada Situs Liyangan, permukiman kuno yang pernah tertimbun pasir dan batu dari letusan Gunung Sindoro. Ada pula Candi Pringapus dengan arca lembu Nandi di dalamnya, dan Prasasti Gondosuli yang dibuat pada tahun 832 Masehi. | diubah |
| `kenal-temanggung-04` | Menjembatani tiga nama (jaran kepang, kuda lumping, kuda kepang) dan bahan bambunya | Saat ada perayaan, warga menari jaran kepang atau kuda lumping dengan kuda-kudaan dari anyaman bambu, diiringi kendang dan gamelan. Di sini kamu akan belajar menemukan informasi yang tertulis jelas dari gambar, teks pendek, dan suara. Jawabannya sering ada di depan mata, asal kamu membacanya dengan teliti. | diubah |
| `kenal-magelang-03` | Benang antarwilayah: air suci Waisak dari Umbul Jumprit (dipakai mgl-3-b) | Tak jauh dari Borobudur ada Candi Mendut dan Candi Pawon. Ketiganya berdiri hampir segaris. Setiap Waisak, umat Buddha berjalan dari Mendut ke Borobudur sambil membawa air suci dari Umbul Jumprit, Temanggung. Di tengah kota ada Gunung Tidar, yang dalam cerita rakyat dijuluki paku tanah Jawa. | diubah |
| `kenal-wonosobo-03` | "Ruwatan" dan "gimbal" dijelaskan; keranjang ongklok, teh Tambi, dan bundengan dikenalkan (target wnb-4; petunjuk wnb-4 #3 merujuk Kenali Wonosobo) | Udara di sini dingin sekali. Warga menghangatkan badan dengan teh dari kebun Tambi dan mi ongklok berkuah kental, yang direbus di keranjang bambu kecil. Ada juga manisan carica, si pepaya gunung. Tari Lengger diiringi bundengan, alat musik dari tudung bambu penggembala bebek. Ada pula upacara memotong rambut gimbal, yaitu rambut yang menggumpal dengan sendirinya, pada anak-anak Dieng. | diubah |
| `dialog-temanggung-05` | Pola kalimat terasa seperti terjemahan | Ada. Tapi kabut membuat tulisannya samar. Orang hanya melirik sekilas, lalu menebak arah. Kalau tebakannya keliru, mereka bisa berputar-putar di ladang sampai senja. | diubah |
| `dialog-temanggung-07` | Panah dan tulisan tampak bertentangan; panah kini menunjuk ke jembatan | Lihat, ada papan petunjuk! Panahnya ke kanan, ke arah jembatan... Ayo, Mbah, lewat sini! | diubah |
| `dialog-temanggung-09` | "Terperosok" kurang akrab; tulisan papan diperjelas | "Jembatan rusak! Silakan lewat jalan kiri." Aduh... Kalau tadi aku langsung berlari ke kanan, kita bisa tercebur ke sungai. | diubah |
| `dialog-temanggung-10` | Melabeli pribadi anak, bukan perilakunya | Nah. Mata yang terburu-buru melewatkan jawaban yang tertulis jelas. Kabut Lupa paling senang kalau kita terburu-buru. | diubah |
| `dialog-magelang-02` | "Tipu daya" kurang akrab | Jangan cepat merasa hebat, Le. Kabut di Magelang punya jebakan yang lain. | diubah |
| `dialog-magelang-03` | Menyesuaikan kata "jebakan" di baris sebelumnya | Jebakan apa, Mbah? Itu Candi Borobudur, kan? Megah sekali... Tapi kenapa relief-reliefnya tampak aneh? | diubah |
| `dialog-magelang-06` | 27 kata; "semua itu cuma dongeng" ambigu (bapak kedua justru bisa benar) | Bapak yang satu berteriak, "Katanya candi ini dibangun dalam satu malam!" Bapak yang lain membalas, "Katanya candi ini baru dibangun seratus tahun lalu!" Keduanya hanya berkata "katanya". Tak seorang pun mau memeriksa. | diubah |
| `dialog-magelang-08` | Definisi fakta/pendapat abstrak; diberi contoh konkret (selaras soal mgl-3 baru). Kalimat kunci tetap. | Itulah tugasmu, Le. Pendapat boleh berbeda, tapi fakta bisa dibuktikan. "Candi ini indah sekali" itu pendapat. "Candi ini dibangun lebih dari seribu tahun lalu" itu fakta. Hubungkan informasi yang satu dengan yang lain, maka kebenaran akan terlihat. | diubah |
| `dialog-wonosobo-03` | Kentongan terkesan tanda bahaya resmi | Mbah, dengar! Ada warga yang panik memukul kentongan. Orang-orang berlarian sambil memegang ponsel! | diubah |
| `dialog-wonosobo-12` | "Orang yang tepat" terlalu abstrak | Dengarkan pesan Mbah. Sebelum percaya, periksa sumbernya. Sebelum membagikan, pikirkan akibatnya. Kalau ragu, tanyakan kepada orang yang tepat, seperti guru, orang tua, atau petugas resmi. | diubah |
| `tuntas-temanggung-03` | Kiasan "buah" | Itu hasil ketelitianmu. Pustaka Temanggung kini terbuka untukmu. Bacalah sepuasnya. | tetap |
| `tuntas-magelang-01` | Pertengkaran dituntaskan dengan fakta | Dengar, Le... Kedua bapak itu berhenti bertengkar. Mereka membaca papan informasi bersama: Borobudur dibangun pada abad ke-8 dan ke-9, batu demi batu. Jadi, kedua kabar "katanya" tadi sama-sama keliru. Sekarang mereka membaca relief bersama-sama, dari awal sampai akhir. | diubah |
| `tuntas-wonosobo-01` | "Kawahnya aman" bertentangan dengan pesan keselamatan di bank (wnb-3-c) | Mbah! Aku berhasil! Warga sudah bertanya kepada petugas resmi. Kawah tidak akan meledak besok pagi, jadi tidak ada yang perlu mengungsi. Semua tetap menjaga jarak sesuai rambu. | diubah |
| `penutup-02` | Idiom "ditelan mentah-mentah" | Kabut Lupa tersibak dari Temanggung, Magelang, sampai Wonosobo. Papan petunjuk terbaca jelas, relief bercerita dengan urutan yang benar, dan pesan berantai tidak lagi langsung dipercaya begitu saja. | diubah |

Selain 24 baris dari analisis (`kenal-wonosobo-02` tidak perlu diubah karena sudah memakai "kekuningan"), ada baris tambahan: `dialog-magelang-03` (mengikuti kata "jebakan"), serta `kenal-temanggung-01/02` dan `kenal-magelang-03` yang mengenalkan Umbul Jumprit, ikan uceng, dan air suci Waisak. Dengan begitu petunjuk Cari Objek "Ingat cerita Mbah Kedu di Kenali …" kini benar-benar didukung narasi (Nandi, uceng, keranjang ongklok, teh Tambi, bundengan), dan benang Temanggung–Magelang lewat Umbul Jumprit muncul di cerita.

---

## 4. Panduan gambar puzzle

- Gaya: arahan aktif katalog 2026-10-11 dipakai kata per kata, ditambah penyesuaian puzzle (siang/pagi keemasan, bukan malam; detail tertata; bentuk asli tetap; tanpa Jaka dan Mbah Kedu). Acuan gaya yang dilampirkan untuk adegan: `reference.06` atau `reference.07`.
- Templat prompt mengikuti katalog: kepala arahan aktif, SUBJECT AND COMPOSITION, DELIVERY (1200 × 1200, WebP, nama berkas), CONSTRAINTS (larangan katalog + aturan keamanan puzzle).
- Tinjauan sembilan gambar katalog (uji keping + isi): **wajib ganti** `tmg-1-03` (latar putih, GAGAL) dan `mgl-1-01` (malam, GAGAL, stupa induk salah letak); **disarankan ganti** `tmg-1-01` (malam, padahal keterangan "langit cerah") dan `wnb-1-07` (tampak atas, kuah tidak sesuai, tanpa keranjang ongklok); lima lainnya boleh dipakai dengan catatan. Gantinya diunggah dengan kode berkas yang sama.
- Alur ekspor tepat 1200 × 1200 px WebP dengan perintah Pillow satu baris.

---

## 5. Yang perlu ditangani di sisi sistem dan produksi

| No. | Hal | Keterangan |
|---|---|---|
| 1 | Validasi impor: jumlah variasi | Susun Gambar kini a–j. Pemeriksaan "tepat tiga variasi" perlu dilonggarkan khusus Susun Gambar. |
| 2 | Validasi impor: skill per wilayah | `membedakan_fakta_pendapat` perlu ditambahkan ke daftar skill yang sah untuk Magelang (aturan kelas 5 di aplikasi). |
| 3 | Validasi impor: pengecoh Temanggung | Aturan kelas 5 yang mewajibkan 0 pengecoh di Temanggung perlu diubah menjadi 1. |
| 4 | Laporan dan analisis | Skill baru perlu muncul di laporan per keterampilan. Variasi Susun Gambar yang didapat siswa perlu ikut diekspor dan diperlakukan sebagai faktor. |
| 5 | Naskah ke aplikasi | Salin 27 baris ke `app/Database/Seeds/data/story.php` agar `StoryDataTest` lulus, lalu perbarui baris di panel editor dialog bila basis data sudah terpasang. |
| 6 | Rekaman | Rekam ulang 27 baris ID dan 25 baris EN (TTS bawaan atau pengisi suara). Rekaman yang berubah kembali ke draft dan perlu disetujui ulang. |
| 7 | Katalog aset | Perbarui brief narasi audio untuk baris yang berubah (misalnya `intro-02`, `dialog-magelang-08`, `tuntas-wonosobo-01`), entri gambar `tmg-1-03` dan `mgl-1-01` setelah diganti, serta 21 entri gambar baru. |
| 8 | Media lama | Ubah `media_note` slot lama lewat panel Gambar & mockup (impor tidak menimpa catatan yang sudah terisi). |
| 9 | Verifikasi isi | Periksa semua butir `needs_verification`, terutama fakta baru: aturan pendakian Gunung Tidar, arah membaca relief, Pasar Papringan, Bundengan, Topeng Lengger (WBTb 2020), dan Candi Arjuna (tulis "di Dataran Tinggi Dieng"). |

## 6. Hal yang masih terbuka

- **Tembakau** di `kenal-temanggung-02` dibiarkan sebagai fakta wilayah; tim perlu memutuskan secara sadar apakah tetap disebut.
- **Jawaban angka "72"** di mgl-2-a06 tampil sebagai angka di bank kata, sedangkan jawaban hitungan lain (*sembilan*) berupa kata. Bila tampilannya terasa janggal, ganti kalimatnya agar jawabannya kata.
- **Susun Gambar dan indikator budaya.** Keterangan dibaca setelah puzzle selesai dan tidak diperiksa. Bila skor Susun Gambar dipakai sebagai ukuran budaya, pertimbangkan satu pertanyaan singkat tentang keterangannya.
- **Pola kata "sama" di Wonosobo.** Tiga pernyataan pembanding yang memakai "sama-sama" atau "keterangan yang sama" (wnb-3-a02, a03, b01) semuanya salah. Ini bukan kata ekstrem, tetapi bisa menjadi pola. Pertimbangkan satu pernyataan "sama" yang benar, misalnya "Kedua kabar sama-sama menyebut Telaga Warna."
- **Skor percobaan pertama.** Untuk Benar atau Salah, analisis sebaiknya memakai jawaban pada percobaan pertama.
