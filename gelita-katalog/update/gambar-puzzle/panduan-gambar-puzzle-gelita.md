# Panduan Gambar Susun Gambar (Puzzle) GELITA

**Status produksi katalog — 14 Oktober 2026:** 21 gambar tambahan d–j serta empat penggantian yang direkomendasikan di bawah telah dibuat dan dipasang. [Daftar final, prompt dan hasil pemeriksaan](../../docs/PUZZLE-UPDATE-20261014.md). Tinjauan bagian 6 adalah kondisi sebelum pekerjaan ini; label buat baru/wajib ganti pada kartu merupakan brief asal, bukan antrian generasi ulang. Needs_verification dan uji susun oleh manusia tetap perlu diselesaikan oleh peneliti.

Panduan ini untuk siapa pun yang membuat gambar tantangan **Susun Gambar** (`tmg-1`, `mgl-1`, `wnb-1`) dengan ChatGPT. Isinya:

- aturan agar gambar tidak ambigu saat dipotong menjadi keping;
- gaya visual yang **mengikuti arahan aktif katalog aset GELITA** (11 Oktober 2026), disesuaikan untuk puzzle;
- **tinjauan sembilan gambar katalog** yang sekarang dipakai, beserta rekomendasi penggantinya;
- **30 prompt siap tempel** (10 per wilayah, variasi a sampai j) dengan templat prompt katalog, beserta keterangan bilingualnya;
- alur kerja dari prompt sampai unggah, termasuk ekspor tepat 1200 × 1200 px WebP;
- skrip pemeriksa keping otomatis.

Prompt ditulis dalam bahasa Inggris, sama seperti prompt di katalog. Istilah budaya tetap memakai nama aslinya (kuda lumping, kuluk, ongklok) dan selalu disertai uraian bentuk fisiknya, karena model gambar belum tentu mengenalnya.

---

## 0. Ringkasan satu layar

| Hal | Ketentuan |
|---|---|
| Ukuran dan format | Persegi **tepat 1200 × 1200 px**, **WebP**, latar penuh (tidak transparan), sRGB, sesuai katalog |
| Nama berkas | Kode berkas + `.webp`, misalnya `challenge.item.tmg-1-d01.webp` |
| Gaya | Arahan aktif katalog: *premium stylized 3D animated adventure*, bentuk membulat lembut, cahaya sinematik hangat, aksen emas kuningan dan navy. Untuk puzzle: **siang atau pagi keemasan, bukan malam** |
| Kisi | Temanggung 3 × 3 kotak · Magelang 4 × 4 kotak · Wonosobo 4 × 4 jigsaw (dipotong otomatis oleh sistem) |
| Aturan emas | **Satu adegan utuh dengan latar penuh. Setiap keping punya penanda unik. Tidak ada dua keping yang mirip.** |
| Dilarang | Latar putih, polos, transparan, atau studio; langit malam yang gelap rata; latar kabur (bokeh); vinyet; teks, logo, atau watermark; kolase; tokoh Jaka atau Mbah Kedu |
| Kode berkas | Variasi a–c memakai kode lama (`tmg-1-01`, `mgl-1-01`, `wnb-1-05`, dan seterusnya). Variasi baru d–j mengikuti pola kode soal: `challenge.item.{tantangan}-{variasi}01` |
| Gambar katalog | 2 **wajib diganti** (`tmg-1-03`, `mgl-1-01`), 2 disarankan diganti (`tmg-1-01`, `wnb-1-07`), 5 boleh dipakai dengan catatan (bagian 6) |
| Alur | Prompt → buat gambar (mode Thinking) → periksa mata dan `cek_keping.py` → perbaiki → potong dan ekspor 1200 px WebP → unggah → coba mainkan |

---

## 1. Spesifikasi dari bank soal (revisi Oktober 2026)

Bank soal revisi sudah memuat **sepuluh variasi Susun Gambar per wilayah** (a sampai j). Setiap variasi berisi satu gambar dan satu keterangan.

| Wilayah | Tantangan | Kisi | Kode berkas variasi a · b · c (lama) | Kode berkas variasi d … j (baru) |
|---|---|---|---|---|
| Temanggung (mudah) | `tmg-1` | 3 × 3 kotak | `challenge.item.tmg-1-01` · `-03` · `-04` | `challenge.item.tmg-1-d01` … `tmg-1-j01` |
| Magelang (sedang) | `mgl-1` | 4 × 4 kotak | `challenge.item.mgl-1-01` · `-02` · `-03` | `challenge.item.mgl-1-d01` … `mgl-1-j01` |
| Wonosobo (sulit) | `wnb-1` | 4 × 4 jigsaw | `challenge.item.wnb-1-05` · `-06` · `-07` | `challenge.item.wnb-1-d01` … `wnb-1-j01` |

Hal yang perlu diingat dari sheet PETUNJUK, KAMUS_KOLOM, dan DAFTAR_MEDIA:

- Gambar **persegi tepat 1200 × 1200 px, WebP, latar penuh**. Potong dan ubah ukurannya sendiri (bagian 7) agar komposisi tidak dirusak pemotongan otomatis.
- **Keterangan (`prompt_id`/`prompt_en`) tampil setelah gambar tersusun.** Gambar harus memperlihatkan apa yang disebut keterangan. Contohnya, bila keterangan menyebut "stupa berlubang berisi arca Buddha", arca itu harus terlihat.
- Selama gambar asli belum diunggah, sistem memasang gambar contoh. Unggah di **Panel GELITA → Gambar & mockup** dengan kode berkas yang persis sama. Mengunggah ulang dengan kode yang sama berarti mengganti gambar lama.
- Kolom `media_note` dari impor Excel hanya mengisi slot yang catatannya masih kosong. Untuk slot lama (variasi a–c), ubah catatannya lewat panel Gambar & mockup.
- DAFTAR_MEDIA meminta pemilik atau sumber gambar dicatat. Untuk gambar AI, catat alat, tanggal, dan prompt yang dipakai (templat di bagian 10).

---

## 2. Mengapa gambar tanpa latar membuat puzzle ambigu

Pada Susun Gambar, siswa menukar keping sampai setiap keping kembali ke posisinya. Sistem memeriksa **posisi**, bukan tampilan. Bila dua keping tampak sama, ada dua akibat buruk:

1. **Siswa tidak punya petunjuk** keping mana yang ditaruh di mana. Ia terpaksa menebak, sehingga yang diukur adalah keberuntungan, bukan ketelitian.
2. **Gambar sudah tampak utuh, tetapi sistem menyatakan belum selesai**, karena dua keping kembar tertukar. Siswa frustrasi, dan data percobaan pertama menjadi kacau.

Contoh benda di atas latar polos, dipotong 3 × 3:

```text
┌───────┬───────┬───────┐
│ putih │ putih │ putih │   ← K1–K4 dan K6–K9 sama-sama putih:
├───────┼───────┼───────┤      bisa ditukar dengan cara apa pun dan tetap
│ putih │ BENDA │ putih │      "tampak benar", padahal sistem menganggap salah.
├───────┼───────┼───────┤
│ putih │ putih │ putih │
└───────┴───────┴───────┘
```

Ini bukan sekadar teori: gambar katalog `challenge.item.tmg-1-03` (Candi Pringapus berlatar putih) gagal uji karena keping K1 dan K3 polos dan kembar (bagian 6). Masalah yang sama muncul pada langit malam yang gelap rata, langit biru polos, lautan awan, permukaan danau yang tenang, latar kabur dari foto close-up, makanan dipotret dari atas, dan pola berulang (deretan stupa, barisan kebun teh, tumpukan bata). Skrip `cek_keping.py` di bagian 9 sudah diuji pada contoh-contoh ini (lihat 9.3).

---

## 3. Dua belas aturan anti-ambigu

| No. | Aturan | Cara mengecek |
|---|---|---|
| 1 | **Latar penuh (full-bleed).** Adegan memenuhi kanvas sampai ke tepi. Tidak ada latar putih, polos, transparan, atau studio. | Lihat keempat sudut: masing-masing harus berisi benda atau pemandangan. |
| 2 | **Setiap keping punya satu penanda unik**, yaitu benda atau bentuk yang hanya ada di keping itu. | Bayangkan kisi, lalu sebutkan penanda tiap keping. Bila ada keping yang tidak bisa disebutkan penandanya, gambar gagal. |
| 3 | **Tidak ada area polos yang lebih dari kira-kira sepertiga keping** (langit, air, dinding, kabut, lantai). | Area itu harus memuat awan, riak, tekstur, benda, atau perubahan cahaya. |
| 4 | **Fokus dalam.** Latar belakang setajam latar depan. Tidak ada bokeh atau blur. | Perbesar gambar 200%: tepi benda di belakang tetap tajam. |
| 5 | **Tanpa vinyet.** Sudut-sudut sama terang dan sama detailnya dengan bagian tengah. | Bandingkan sudut kiri atas dengan bagian tengah. |
| 6 | **Komposisi asimetris.** Hindari pemandangan yang kiri dan kanannya seperti cermin. | Balik gambar secara horizontal. Bila tampak hampir sama, gambar berisiko. |
| 7 | **Cahaya siang dari satu sisi**, sehingga terang dan warna berubah perlahan dari sisi ke sisi. Bukan malam. | Keping kiri dan kanan berbeda kecerahannya; tidak ada keping yang hampir hitam. |
| 8 | **Pola berulang** (stupa, deret teh, genteng, bilah bambu) tidak boleh memenuhi satu keping tanpa penanda lain. | Setiap keping yang berpola harus juga memuat orang, pohon, atau benda yang khas. |
| 9 | **Subjek utama besar dan menyebar** melewati beberapa keping. Benda kecil yang penting (wajah, arca) utuh di dalam satu keping, jauh dari garis potong. | Garis potong 3 × 3 ada di 1/3 dan 2/3 sisi. Garis potong 4 × 4 ada di 1/4, 1/2, dan 3/4 sisi. |
| 10 | **Jangan bedakan keping hanya lewat warna.** Sekitar 5–8% anak laki-laki buta warna merah-hijau (angkanya berbeda antarpopulasi). | Ubah gambar menjadi hitam-putih: keping tetap bisa dibedakan. Skrip juga menyimulasikan buta warna. |
| 11 | **Tanpa teks, huruf, angka, logo, watermark, atau bingkai.** | Periksa papan, kemasan, dan tembok. Rambu hanya boleh berupa piktogram. |
| 12 | **Garis panjang tidak boleh lurus sempurna di garis potong** (cakrawala, tepi atap, tepi meja). | Cakrawala naik-turun mengikuti bukit atau pohon, sehingga sambungan antarkeping mudah dikenali. |

Khusus kabut di Wonosobo: kabut hanya boleh berupa pita tipis di lembah yang jauh. Kabut tidak boleh lebih dari sekitar 15% luas gambar dan tidak boleh menutupi satu keping penuh. Kabut memang ciri Dieng, tetapi kabut tebal pasti menghasilkan keping kembar.

---

## 4. Gaya visual: arahan aktif katalog GELITA

### 4.1 Blok gaya yang dipakai apa adanya

Setiap prompt dibuka dengan arahan aktif katalog (`GELITA ACTIVE VISUAL DIRECTION — 2026-10-11`), kata per kata sama dengan prompt di `data.json` katalog:

> Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.

Bagian lain prompt juga mengikuti susunan katalog: **SUBJECT AND COMPOSITION**, **DELIVERY**, dan **CONSTRAINTS** (kalimat larangan katalog dipakai utuh). Jadi gambar puzzle akan tampil satu keluarga dengan aset GELITA lain.

### 4.2 Penyesuaian khusus puzzle

Arahan katalog ditulis untuk semua aset. Untuk puzzle, empat hal ditambahkan lewat blok `Puzzle adaptation`:

| Penyesuaian | Alasan |
|---|---|
| **Pagi cerah atau cahaya keemasan siang/sore, bukan malam.** Emas kuningan hadir di sinar matahari dan sorotan; navy hanya di bayangan kecil dan gunung yang jauh. | Lima dari sembilan gambar katalog sekarang berlatar malam atau senja. Langit malam yang gelap rata menghasilkan keping polos dan kembar: `mgl-1-01` gagal uji karena keping K13 polos dan tiga pasang keping kembar. |
| **Detail kaya dan tertata di seluruh persegi, bukan berantakan.** | Katalog melarang *clutter*, sedangkan puzzle butuh penanda di setiap keping. Daftar `Puzzle layout` di prompt menetapkan satu penanda per area agar keduanya terpenuhi. |
| **Bentuk asli tetap sesuai kenyataan; hanya cara render yang distilisasi.** | Katalog meminta *faithfully preserve real architecture, costume, anatomy and proportions*. Candi, kostum, dan makanan harus bisa dikenali dari foto aslinya. |
| **Tanpa Jaka dan Mbah Kedu.** | Katalog: *never add characters to object-only or landscape assets*. Gambar puzzle adalah aset adegan. |

### 4.3 Gambar acuan yang dilampirkan

- **Acuan gaya:** blok katalog menyebut `reference.01` (lembar karakter Jaka) dan `reference.09` (logo GELITA). Keduanya bukan pemandangan, sehingga model bisa ikut menggambar Jaka atau huruf logo. Karena itu, untuk puzzle lampirkan **`reference.06`** (lembah keemasan dengan jalur cahaya ke candi) atau **`reference.07`** (jalan batu dan gerbang tua bercahaya matahari). Keduanya pemandangan dengan hasil akhir GELITA yang sama. Lampirkan `reference.01` hanya bila hasilnya masih kurang "GELITA"; baris `Characters` di prompt menjaga agar tokohnya tidak ikut tergambar.
- **Acuan fakta:** foto berlisensi untuk puzzle bertanda *Wajib* atau *Disarankan* (lihat kartu tiap puzzle).

### 4.4 Ciri tiap wilayah

Ciri ini sudah dimasukkan ke baris `Place` di setiap prompt.

| Wilayah | Cahaya dan suasana | Palet (aksen katalog di dalamnya) | Ciri lokal |
|---|---|---|---|
| Temanggung | Pagi cerah, matahari dari kiri, awan kumulus | Hijau segar dan cokelat tanah vulkanik; emas di sinar pagi; navy hanya di bayangan lereng | Sindoro–Sumbing, ladang bertingkat, kopi, bambu, rumah limasan |
| Magelang | Pagi keemasan yang hangat | Abu-abu andesit berlumut, hijau sawah; emas kuningan dominan di cahaya | Borobudur, Merapi–Merbabu di timur, Menoreh di selatan, kelapa |
| Wonosobo/Dieng | Sejuk dan bening, kabut hanya pita tipis | Hijau kebiruan dan toska yang terkendali, kuning belerang, aksen emas | Teras kentang, cemara, carica, jaket dan kupluk, sarung di bahu |

**Tangga kesulitan visual** (tercantum di baris `Level`): Temanggung memakai bentuk besar dan kontras kuat; Magelang 16 area dengan detail lebih kaya; Wonosobo suasana lebih halus, tetapi 16 penanda tetap unik. Kesulitan datang dari kerumitan gambar, **bukan** dari keping yang kembar.

---

## 5. Kepekaan budaya, agama, dan keselamatan

- **Arca Buddha, stupa, candi, dan Waisak** digambarkan dengan hormat. Arca Buddha harus utuh di dalam satu area, tidak terpotong garis kisi, dan tidak menjadi subjek yang dipotong-potong. Tidak boleh ada pengunjung yang memanjat, duduk di, atau menyentuh stupa dan relief. Pada prosesi Waisak, tampilkan penonton tertib yang memberi jalan, dan tidak ada lampion terbang.
- **Candi Hindu** (Pringapus, Arjuna, arca Nandi) tidak boleh tertukar dengan unsur Buddha, dan sebaliknya.
- **Kuda lumping** ditampilkan hanya sebagai tarian gembira: tanpa adegan kesurupan, cambuk diarahkan ke orang, makan beling, atau asap kemenyan.
- **Tradisi ruwatan rambut gimbal tidak dijadikan puzzle.** Ini upacara sakral yang melibatkan anak-anak nyata, dan gambarnya berisiko menstigma anak berambut gimbal.
- **Tembakau tidak digambarkan.** Bank soal juga tidak memakainya. Kopi hanya tampil sebagai tanaman dan hasil panen; tidak ada anak yang meminum kopi.
- **Keselamatan:** di kawah, semua orang berada di balik pagar. Di Sikunir, semua orang berada di jalur. Anak selalu bersama orang dewasa.
- **Orang nyata:** jangan meminta wajah orang terkenal atau orang tertentu, dan jangan mengunggah foto orang yang bisa dikenali sebagai referensi.
- **Label ilustrasi:** gambar AI adalah ilustrasi, bukan dokumentasi. Bila dipakai di Pustaka, beri keterangan "Ilustrasi".

---


## 6. Gambar katalog yang perlu diganti

Katalog aset (`gelita-katalog`) sudah memuat sembilan gambar Susun Gambar untuk variasi a–c. Semuanya berstatus **"Ilustrasi AI — perlu tinjau"**, dengan catatan isi **"Perlu pemeriksaan isi dan budaya sebelum produksi"**. Status gaya "Sesuai gaya" pada gambar itu ditetapkan atas keputusan pengguna dan, menurut catatan katalog sendiri, *tidak menyatakan gambar lama telah diperbaiki secara visual*.

Kesembilan gambar itu diuji dengan `cek_keping.py` (kisi sesuai wilayah) lalu ditinjau isinya terhadap keterangan di bank soal:

| Kode berkas | Butir | Putusan | Hasil uji keping | Alasan | Pengganti |
|---|---|---|---|---|---|
| `challenge.item.tmg-1-01` | `tmg-1-a01` | Disarankan ganti | LULUS DENGAN CATATAN (K2~K3 mirip; K7~K8 pola sama) | Adegan malam berbulan, padahal keterangan dan catatan gambar menyebut langit cerah dan kebun; kebun di kaki gunung nyaris tak terlihat dalam gelap. | TMG-P01 |
| `challenge.item.tmg-1-03` | `tmg-1-b01` | **Wajib ganti** | GAGAL (K1 dan K3 polos 94 %, keduanya kembar; K2~K9 pola sama) | Candi di atas latar putih tanpa adegan, sehingga baris atas hanya berisi putih. Bentuk candinya juga perlu dicocokkan dengan foto Candi Pringapus asli; gambar ini tampak seperti candi bertingkat rekaan. | TMG-P02 |
| `challenge.item.tmg-1-04` | `tmg-1-c01` | Boleh dipakai | LULUS DENGAN CATATAN (K4~K8, K7~K9 tekstur tanah sama) | Penari jelas dan kuda kepangnya terbaca. Adegannya malam hari; buat ulang hanya bila tim ingin seluruh set siang hari. | TMG-P03 |
| `challenge.item.mgl-1-01` | `mgl-1-a01` | **Wajib ganti** | GAGAL (K13 polos; K1~K3, K2~K3, K13~K14 kembar) | Langit malam dan lantai gelap menghasilkan keping kembar. Stupa induk digambar sebagai candi terpisah di seberang lembah, padahal stupa induk ada di puncak Borobudur sendiri. Lubang stupa terdekat juga tidak memperlihatkan arca Buddha yang disebut keterangan. | MGL-P01 |
| `challenge.item.mgl-1-02` | `mgl-1-b01` | Boleh dipakai | LULUS DENGAN CATATAN (K6~K7 tekstur sama) | Kuluk bulu dan lonceng kaki terlihat. Cocokkan rias wajah dan kostum dengan foto Topeng Ireng asli sebelum dipakai. | MGL-P02 |
| `challenge.item.mgl-1-03` | `mgl-1-c01` | Cek bentuk | LULUS | Keping aman. Atap bertingkat penuh stupa kecil perlu dicocokkan dengan foto Candi Mendut asli; bila berbeda jauh, buat ulang dengan prompt MGL-P03. | MGL-P03 |
| `challenge.item.wnb-1-05` | `wnb-1-a01` | Boleh dipakai | LULUS | Siang hari dan keping beragam. Warna air hijau-biru; bagian kekuningan di tepi telaga (keterangan baru) belum tampak jelas, jadi buat ulang bila ingin gambar sama persis dengan keterangan. | WNB-P01 |
| `challenge.item.wnb-1-06` | `wnb-1-b01` | Boleh dipakai | LULUS DENGAN CATATAN (K3~K4 langit mirip) | Lautan awan dan Sindoro sudah benar. Tidak ada pengunjung atau pagar, jadi pesan keselamatan bacaan wnb-3-b tidak terlihat; opsional dibuat ulang. | WNB-P02 |
| `challenge.item.wnb-1-07` | `wnb-1-c01` | Disarankan ganti | LULUS DENGAN CATATAN (K10~K11 pola mi sama) | Tampak atas membuat keping pinggir hanya berisi taplak dan mangkuk. Kuahnya bening kekuningan seperti ramen, padahal ciri mi ongklok adalah kuah kanji kental kecokelatan; keranjang ongklok yang disebut keterangan tidak ada. | WNB-P03 |

**Urutan pengerjaan yang disarankan:**

1. **Wajib, sebelum uji coba ke siswa:** `tmg-1-03` dan `mgl-1-01`. Keduanya gagal uji keping, sehingga siswa bisa menyusun gambar yang tampak benar tetapi dinyatakan salah.
2. **Disarankan:** `tmg-1-01` dan `wnb-1-07`. Keduanya lolos uji keping, tetapi isinya tidak sesuai dengan keterangan (malam vs "langit cerah"; tampak atas dan kuah bening vs kuah kental dengan keranjang ongklok).
3. **Boleh dipakai:** lima gambar lainnya. Bila tim ingin seluruh set seragam (siang hari dan gaya 3D GELITA), buat ulang dengan prompt P01–P03 masing-masing wilayah.

**Cara mengganti:** buat gambar baru dengan prompt pengganti di bagian 8, ekspor `1200 × 1200` WebP, lalu unggah di **Gambar & mockup** dengan **kode berkas yang sama**. Kode berkas, butir soal, dan keterangan tidak berubah, jadi tidak perlu impor ulang. Catat versi lama dan versi baru di lembar produksi (bagian 10), dan perbarui entri katalog (gambar, prompt, dan status tinjauan).

Untuk mengulang uji pada gambar katalog:

```bash
python3 cek_keping.py gelita-katalog/assets/tantangan/challenge.item.tmg-1-03.webp --kisi 3
python3 cek_keping.py gelita-katalog/assets/tantangan/challenge.item.mgl-1-01.webp --kisi 4
```

---

## 7. Alur kerja di ChatGPT

1. **Buka satu percakapan baru untuk setiap wilayah.** Gaya gambar akan lebih seragam di dalam satu percakapan. Bila paket ChatGPT-nya Plus, Pro, atau Business, pilih mode **Thinking** pada pembuatan gambar. Mode ini lebih patuh pada tata letak yang rumit.
2. **Lampirkan acuan gaya** `reference.06` atau `reference.07` dari folder `assets/referensi/` katalog (bagian 4.3).
3. **Lampirkan foto fakta** untuk puzzle bertanda *Wajib* atau *Disarankan*. Pakai foto milik sendiri atau foto berlisensi bebas, misalnya dari Wikimedia Commons, dan catat sumbernya.
4. **Tempel prompt** dari bagian 8, lalu kirim. Untuk mendapat pilihan, tambahkan di akhir: *"Make 2 variations."*
5. **Pilih yang terbaik** dalam 30 detik dengan uji cepat:
   - Perkecil gambar sampai seukuran prangko. Apakah setiap area masih bisa ditunjuk?
   - Apakah keempat sudut berisi dan tidak ada bagian yang hampir hitam?
   - Apakah ada teks, atau tokoh Jaka yang ikut tergambar?
6. **Perbaiki** dengan prompt perbaikan di bagian 8.4. Satu perbaikan untuk satu masalah lebih berhasil daripada banyak perintah sekaligus.
7. **Unduh** hasil dalam resolusi tertinggi (PNG). Ketika panduan ini ditulis, ChatGPT Images 2.0 (`gpt-image-2`, rilis April 2026) disebut mampu menghasilkan gambar hingga sekitar 2K. Bila berkas yang diunduh masih di bawah 1200 px, minta ulang: *"Give me the same image at the highest resolution available, square 1:1."* Bila masih kurang juga, perbesar dengan pembesar AI seperti Upscayl (gratis).
8. **Potong dan ekspor tepat 1200 × 1200 px WebP** (ketentuan katalog). ChatGPT tidak menyimpan WebP, jadi ubah sendiri, misalnya dengan Python dan Pillow:

   ```bash
   python3 -c "from PIL import Image; im=Image.open('hasil.png').convert('RGB'); w,h=im.size; s=min(w,h); im=im.crop(((w-s)//2,(h-s)//2,(w+s)//2,(h+s)//2)).resize((1200,1200),Image.LANCZOS); im.save('challenge.item.tmg-1-d01.webp','WEBP',quality=88,method=6)"
   ```

   Ganti nama keluarannya dengan kode berkas puzzle itu. Hasilnya tidak transparan (RGB), sRGB, dan biasanya berukuran 150–400 KB.
9. **Jalankan `cek_keping.py`** pada berkas WebP itu (bagian 9). Perbaiki semua temuan **GAGAL**. Temuan **PERIKSA** dinilai dengan mata.
10. **Uji coba oleh manusia.** Cetak atau tampilkan gambar `<nama>_acak.png` buatan skrip. Minta dua orang (guru, atau siswa dengan izin) menyusunnya. Catat keping yang membuat mereka ragu.
11. **Unggah** di Panel GELITA → Gambar & mockup dengan kode berkas yang sama. Mainkan sebagai siswa dan pastikan keterangan cocok dengan gambar.
12. **Catat** di lembar produksi (bagian 10). Catatan ini sekaligus menjadi dokumentasi penelitian.

---

## 8. Prompt per wilayah

Setiap prompt memakai templat katalog: kepala `GELITA ACTIVE VISUAL DIRECTION`, blok gaya, **SUBJECT AND COMPOSITION** (adegan, tata letak 9 atau 16 penanda, ketepatan budaya, ciri wilayah, aturan orang), **DELIVERY** (1200 × 1200, WebP, nama berkas), dan **CONSTRAINTS** (larangan katalog, aturan keamanan puzzle, dan larangan khusus). Tempel apa adanya. Bila foto fakta tidak dilampirkan, hapus kalimat yang diawali `Use a verified licensed photograph`.

Kartu tiap puzzle juga memuat keterangan bilingual yang sudah dimasukkan ke bank soal. Keterangan variasi d–j berstatus `needs_verification`: fakta yang ditandai "Catatan fakta" wajib dicek peneliti.

### 8.1 Temanggung: 3 × 3 kotak (mudah)

P01–P03 adalah pengganti gambar katalog variasi a, b, dan c. P04–P10 (variasi d–j) menutup celah cerita dan soal: Situs Liyangan, Prasasti Gondosuli, Umbul Jumprit (benang ke Waisak di Magelang), panen kopi, ikan uceng (target tmg-4), Pasar Papringan, dan perajin kuda kepang.

| No. | Judul | Variasi | Kode berkas | Gambar katalog | Foto referensi |
|---|---|---|---|---|---|
| TMG-P01 | Gunung Sindoro dan Sumbing | Variasi a (sudah ada) | `challenge.item.tmg-1-01` | Disarankan ganti | Disarankan |
| TMG-P02 | Candi Pringapus | Variasi b (sudah ada) | `challenge.item.tmg-1-03` | **Wajib ganti** | **Wajib** |
| TMG-P03 | Kuda Lumping (Jaran Kepang) | Variasi c (sudah ada) | `challenge.item.tmg-1-04` | Boleh dipakai | Disarankan |
| TMG-P04 | Situs Liyangan | Variasi d (baru) | `challenge.item.tmg-1-d01` | buat baru | **Wajib** |
| TMG-P05 | Prasasti Gondosuli | Variasi e (baru) | `challenge.item.tmg-1-e01` | buat baru | **Wajib** |
| TMG-P06 | Umbul Jumprit | Variasi f (baru) | `challenge.item.tmg-1-f01` | buat baru | Disarankan |
| TMG-P07 | Panen Kopi di Lereng Sindoro–Sumbing | Variasi g (baru) | `challenge.item.tmg-1-g01` | buat baru | Tidak perlu |
| TMG-P08 | Ikan Uceng dan Sungai Jernih | Variasi h (baru) | `challenge.item.tmg-1-h01` | buat baru | Disarankan |
| TMG-P09 | Pasar Papringan | Variasi i (baru) | `challenge.item.tmg-1-i01` | buat baru | Disarankan |
| TMG-P10 | Perajin Kuda Kepang | Variasi j (baru) | `challenge.item.tmg-1-j01` | buat baru | Disarankan |

#### TMG-P01 · Gunung Sindoro dan Sumbing

| | |
|---|---|
| Status | Variasi a (sudah ada) · butir `tmg-1-a01` |
| Kode berkas | `challenge.item.tmg-1-01` → simpan sebagai `challenge.item.tmg-1-01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | `kenal-temanggung-01` (kakak dan adik), tmg-2-a (Gunung Kembar), tmg-3-c (kopi lereng) |
| Foto referensi | Disarankan |
| Gambar katalog sekarang | Disarankan ganti · uji keping: LULUS DENGAN CATATAN (K2~K3 mirip; K7~K8 pola sama) |
| `media_note` | Sindoro (kanan) dan Sumbing (kiri) dilihat dari ladang bertingkat di Temanggung; awan khas dan burung di celah puncak; petani dan kebun kopi di latar depan |

**Keterangan (sudah ada di bank soal):**

> **ID:** Gunung Sindoro (3.136 m) dan Gunung Sumbing (3.371 m) disebut gunung kembar. Keduanya mengapit Temanggung dan membuat tanahnya subur.
>
> **EN:** Mount Sindoro (3,136 m) and Mount Sumbing (3,371 m) are called the twin mountains. They stand on either side of Temanggung and make its soil fertile.

*Risiko ambigu:* Catatan gambar lama berbunyi "langit cerah". Hasilnya, tiga keping baris atas bisa berisi langit polos. Isi celah di antara kedua puncak dengan awan yang bentuknya khas dan sekawanan burung.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-01  (TMG-P01 · Gunung Sindoro dan Sumbing)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Morning view from a terraced farming hillside near Temanggung town, looking west at the twin volcanoes standing side by side: Mount Sumbing on the LEFT (broad and rugged, with deep ravines running down its flanks) and Mount Sindoro on the RIGHT (a smoother, more regular cone). Their lower slopes form a patchwork of green fields, small villages and forest; the foreground is a lively farm terrace.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) Sumbing's rugged summit with a small wisp of cloud; (2) the sky gap between the two peaks with one large, distinctive cumulus cloud and a small flock of white egrets; (3) Sindoro's smooth summit wearing a little cloud cap.
• Middle row — (4) Sumbing's forested lower slope with a small village of red roofs; (5) the saddle between the mountains with a winding road and a tall sengon tree; (6) Sindoro's slope with a patchwork of vegetable fields and a red-roofed farmhouse.
• Bottom row — (7) coffee shrubs with clusters of red cherries beside a small bamboo hut; (8) a dirt path with a woman farmer in a caping (bamboo conical hat) carrying a bamboo basket on her back; (9) neat beds of cabbage and carrots with a small irrigation channel and a banana plant.

Accuracy — Sumbing is slightly taller and more eroded than Sindoro. Crops are coffee, cabbage, carrots and spring onions.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — an empty clear sky, snow on the peaks, tobacco fields, text.
```

#### TMG-P02 · Candi Pringapus

| | |
|---|---|
| Status | Variasi b (sudah ada) · butir `tmg-1-b01` |
| Kode berkas | `challenge.item.tmg-1-03` → simpan sebagai `challenge.item.tmg-1-03.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | `kenal-temanggung-03`, tmg-3-b (Pringapus dan Gondosuli), tmg-4-a03 (arca Nandi) |
| Foto referensi | **Wajib** |
| Gambar katalog sekarang | **Wajib ganti** · uji keping: GAGAL (K1 dan K3 polos 94 %, keduanya kembar; K2~K9 pola sama) |

**Keterangan (sudah ada di bank soal):**

> **ID:** Candi Pringapus adalah candi Hindu dari abad ke-9. Di dalam ruang candinya tersimpan arca Nandi, arca lembu yang dihormati umat Hindu.
>
> **EN:** Pringapus Temple is a Hindu temple from the 9th century. Its chamber holds a statue of Nandi, the bull honoured by Hindus.

*Risiko ambigu:* Candi kecil di tengah halaman rumput mudah menyisakan keping yang isinya hanya rumput atau hanya langit. Pastikan setiap keping memuat unsur yang berbeda.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-03  (TMG-P02 · Candi Pringapus)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Front three-quarter view of Candi Pringapus, a small 9th-century Hindu temple of grey andesite standing in a neat grassy yard in a Temanggung village, with Mount Sindoro rising behind it. A stone stairway leads up to the single doorway; carved panels and a kala head decorate the walls; the roof rises in tiers. Match the real temple's proportions and carvings to the reference photo.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) the summit of Mount Sindoro behind a tree, with a cloud; (2) the temple's roof tiers against the sky with one distinctive cloud; (3) the crown of a large banyan tree with a few birds.
• Middle row — (4) a village house with a red-tiled roof behind a bamboo fence; (5) the temple doorway with the kala-head carving above it; (6) the temple's side wall with carved panels and a frangipani tree beside it.
• Bottom row — (7) a flower bed with red and yellow canna flowers and a small stone marker (no writing); (8) the stone steps and the paved path leading to them; (9) the low yard fence and gate with a bicycle leaning on it.

Accuracy — This is a HINDU temple: no Buddhist stupas and no Buddha statues. Keep it small and humble, not like Prambanan.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-03.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — anyone climbing the temple, text on signs, a plain lawn filling a whole area.
```

#### TMG-P03 · Kuda Lumping (Jaran Kepang)

| | |
|---|---|
| Status | Variasi c (sudah ada) · butir `tmg-1-c01` |
| Kode berkas | `challenge.item.tmg-1-04` → simpan sebagai `challenge.item.tmg-1-04.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | `kenal-temanggung-04` (jaran kepang), tmg-2-c, tmg-4-a01 |
| Foto referensi | Disarankan |
| Gambar katalog sekarang | Boleh dipakai · uji keping: LULUS DENGAN CATATAN (K4~K8, K7~K9 tekstur tanah sama) |

**Keterangan (sudah ada di bank soal):**

> **ID:** Kuda lumping atau jaran kepang memakai kuda-kudaan dari anyaman bambu. Di Temanggung ada lebih dari 1.500 kelompok kuda lumping.
>
> **EN:** Kuda lumping, or jaran kepang, uses horses made of woven bamboo. Temanggung has more than 1,500 kuda lumping groups.

*Risiko ambigu:* Halaman tanah yang luas dan langit bisa menjadi keping polos. Sebarkan penonton, gamelan, dan umbul-umbul ke seluruh gambar.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-04  (TMG-P03 · Kuda Lumping (Jaran Kepang))
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A cheerful daytime kuda lumping (jaran kepang) performance in an open village yard in Temanggung. Four dancers in bright costumes (red, gold and black, with headbands and sashes) ride flat hobby horses made of woven bamboo, painted and trimmed with colourful cloth and tassels, caught in a lively mid-dance moment. Gamelan musicians with kendang drums and a hanging gong play on a low bamboo stage; villagers watch from the edge; Sindoro and Sumbing rise behind.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) Mount Sumbing behind tall red-and-white umbul-umbul banner poles; (2) colourful bunting strung between trees, with a distinctive cloud; (3) Mount Sindoro behind a leafy tree.
• Middle row — (4) gamelan players with kendang drums and a hanging gong on a bamboo stage; (5) the lead dancer on a red-and-white hobby horse in mid-step; (6) two dancers on a black horse and a white horse turning toward each other.
• Bottom row — (7) children sitting calmly on a woven mat beside their parents; (8) the dusty yard with footprints and spare hobby horses leaning on a bamboo rack; (9) a food cart (gerobak) selling snacks under a striped umbrella.

Accuracy — Show only the joyful dance: NO trance, NO whips cracked at people, NO eating glass, NO incense smoke.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-04.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — dangerous acts, scary faces, text.
```

#### TMG-P04 · Situs Liyangan

| | |
|---|---|
| Status | Variasi d (baru) · butir `tmg-1-d01` |
| Kode berkas | `challenge.item.tmg-1-d01` → simpan sebagai `challenge.item.tmg-1-d01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | `kenal-temanggung-03`, tmg-3-a (bacaan Situs Liyangan) |
| Foto referensi | **Wajib** |
| `media_note` | situs ekskavasi di lereng Sindoro: talud batu bertingkat, kaki candi, jalan batu kuno, dinding galian berlapis, peneliti dewasa bekerja |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Situs Liyangan di Desa Purbosari, Ngadirejo, ditemukan pada tahun 2008 saat warga menambang pasir. Para peneliti menemukan sisa rumah kayu, bangunan candi, jalan batu, dan butir padi hangus yang tertimbun material letusan Gunung Sindoro.
>
> **EN:** The Liyangan Site in Purbosari Village, Ngadirejo, was found in 2008 while villagers were digging for sand. Researchers found the remains of wooden houses, temple buildings, stone paths and burnt grains of rice, all buried by material from an eruption of Mount Sindoro.

*Risiko ambigu:* Dinding galian pasir yang luas bisa tampak seragam. Tampilkan lapisan tanah yang warnanya berbeda dan akar pohon di bagian atasnya.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-d01  (TMG-P04 · Situs Liyangan)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
An archaeological excavation site on the lower slope of Mount Sindoro. Stepped retaining walls of fitted andesite stone (talud) climb the slope; the stone base of a small temple with stairs stands in the middle; an ancient stone-paved path crosses the site. Tall excavation walls show clear horizontal layers of volcanic sand and ash in different colours. Two adult archaeologists in field hats work carefully with brushes and a measuring-string grid. A simple roof shelter protects part of the site. Mount Sindoro looms close behind.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) Sindoro's summit with a cloud; (2) the top edge of the tall excavation wall with tree roots and grass hanging over it; (3) a simple zinc-roofed shelter on wooden posts.
• Middle row — (4) stepped stone terrace walls climbing to the left; (5) the stone temple base with its small stairway; (6) an archaeologist kneeling with a soft brush beside a sieve.
• Bottom row — (7) the ancient stone-paved path; (8) a square excavation pit with a string grid and dark charred wooden remains; (9) a wheelbarrow, buckets and a pile of sand.

Accuracy — The layers look like real volcanic deposits (grey, brown and yellowish bands). The site is calm and scientific — no treasure, no gold.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-d01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — children digging, text on labels, a plain sand wall filling a whole area.
```

#### TMG-P05 · Prasasti Gondosuli

| | |
|---|---|
| Status | Variasi e (baru) · butir `tmg-1-e01` |
| Kode berkas | `challenge.item.tmg-1-e01` → simpan sebagai `challenge.item.tmg-1-e01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | `kenal-temanggung-03` (832 M), tmg-3-b |
| Foto referensi | **Wajib** |
| `media_note` | situs Prasasti Gondosuli: batu andesit besar beraksara kuno di halaman situs, susunan pelindung dan pagar sesuai aslinya, batu-batu candi kuno di sekitarnya |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Prasasti Gondosuli dibuat pada tahun 832 Masehi. Prasasti adalah tulisan pada batu atau logam yang mencatat peristiwa penting.
>
> **EN:** The Gondosuli Inscription was made in the year 832 CE. An inscription is writing on stone or metal that records an important event.

*Risiko ambigu:* Batu prasasti yang gelap tampak seragam. Pakai cahaya samping yang rendah agar goresan aksara dan tekstur batu terlihat, lalu taruh benda yang berbeda-beda di sekelilingnya.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-e01  (TMG-P05 · Prasasti Gondosuli)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A small, well-kept heritage site in Gondosuli village, Temanggung. A large, dark andesite stone with several lines of shallow, worn ancient script rests on a low stone platform. Arrange the protective roof (if any), the fence and the surroundings exactly as in the attached reference photo of the real site. Old carved temple stones lie neatly nearby; village trees and a mountain slope fill the background.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) a mountain slope with terraced fields under a cloud; (2) the top of the protective shelter, or a large shade tree if the site has no roof; (3) a coconut palm and the roof of a neighbouring house.
• Middle row — (4) neatly lined old temple stones with carvings; (5) the upper half of the inscription stone, its carved lines lit by low side light; (6) an adult guide explaining to two children who listen with hands behind their backs.
• Bottom row — (7) a garden bed with ferns and red ginger flowers; (8) the low stone platform and the lower half of the inscription stone; (9) the low fence and the entrance path with fallen leaves.

Accuracy — The script must look ancient and worn (Old Javanese-style characters), never modern Latin letters.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-e01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — readable modern letters, text on signs, anyone touching the stone.
```

#### TMG-P06 · Umbul Jumprit

| | |
|---|---|
| Status | Variasi f (baru) · butir `tmg-1-f01` |
| Kode berkas | `challenge.item.tmg-1-f01` → simpan sebagai `challenge.item.tmg-1-f01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | tmg-2-b (Umbul Jumprit), mgl-3-b (air suci Waisak): benang dari Temanggung ke Magelang |
| Foto referensi | Disarankan |
| `media_note` | mata air keluar dari bawah batu berlumut dan pohon besar, kolam jernih berkerikil, aliran awal Sungai Progo, pinus, monyet ekor panjang, gapura batu |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Umbul Jumprit adalah mata air di lereng Gunung Sindoro. Airnya jernih dan tidak pernah kering, lalu mengalir menjadi hulu Sungai Progo. Setiap Waisak, air suci untuk perayaan di Candi Borobudur diambil dari sini.
>
> **EN:** Umbul Jumprit is a spring on the slopes of Mount Sindoro. Its clear water never dries up and flows on to become the source of the Progo River. Every Vesak, holy water for the celebration at Borobudur Temple is taken from here.

*Risiko ambigu:* Air jernih bisa menjadi area yang seragam. Tampilkan kerikil warna-warni, pantulan, riak, dan daun yang hanyut.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-f01  (TMG-P06 · Umbul Jumprit)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A cool forest spring on the slope of Mount Sindoro. Crystal-clear water wells up from beneath a mossy rock and an old cave mouth at the foot of a huge shady tree, fills a stone-edged pool and flows out as a small rocky stream — the very beginning of the Progo River. Tall pine trees stand around; long-tailed macaques sit calmly on rocks and roots; an old Javanese-style stone gate appears among the trees. Morning sunbeams fall through the leaves.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) tall pine trunks with slanting sunbeams; (2) the giant tree's canopy with hanging aerial roots; (3) an old Javanese-style stone gate (gapura) among the trees.
• Middle row — (4) the mossy cave mouth where the water comes out; (5) the clear pool with colourful pebbles visible under gentle ripples and reflections; (6) a macaque sitting on a rock eating a fruit.
• Bottom row — (7) ferns and mossy stone steps; (8) the stream flowing out over rocks beneath a small wooden footbridge; (9) a mother macaque with her baby on a big tree root among fallen leaves.

Accuracy — Keep it natural and calm. The monkeys are wild long-tailed macaques, not cartoon monkeys.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-f01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — people bathing, litter, text.
```

#### TMG-P07 · Panen Kopi di Lereng Sindoro–Sumbing

| | |
|---|---|
| Status | Variasi g (baru) · butir `tmg-1-g01` |
| Kode berkas | `challenge.item.tmg-1-g01` → simpan sebagai `challenge.item.tmg-1-g01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | `kenal-temanggung-02`, tmg-3-c (Kopi dari Lereng Gunung), tmg-4-a02 (biji kopi) |
| Foto referensi | Tidak perlu |
| `media_note` | panen kopi arabika di lereng bertingkat: petani dewasa memetik buah merah, pohon peneduh, buah dijemur di depan pondok, Sindoro di latar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Petani di lereng Gunung Sindoro dan Sumbing menanam kopi arabika di ketinggian lebih dari 900 meter. Buah kopi yang sudah merah dipetik, diolah, lalu dikeringkan.
>
> **EN:** Farmers on the slopes of Mount Sindoro and Mount Sumbing grow arabica coffee at more than 900 metres. The ripe red coffee cherries are picked, processed and then dried.

*Risiko ambigu:* Deretan semak kopi bisa tampak berulang. Bedakan setiap keping dengan petani, pohon peneduh, keranjang, dan jalan setapak.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-g01  (TMG-P07 · Panen Kopi di Lereng Sindoro–Sumbing)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Harvest time in a coffee garden on a terraced slope of Mount Sindoro. Adult farmers pick ripe red coffee cherries into bamboo baskets; the shrubs have glossy dark-green leaves with clusters of red and green cherries; tall shade trees stand between the rows. Near a small farm hut, picked cherries are spread on mats to dry. Mount Sindoro rises behind.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) Sindoro's summit with a cloud; (2) the canopy of a tall shade tree with a perched bird; (3) terraced slopes stepping down into a valley.
• Middle row — (4) a woman farmer in a caping picking cherries; (5) a coffee branch heavy with red cherries, seen close; (6) a man carrying a full sack on his shoulder along the terrace.
• Bottom row — (7) a bamboo basket overflowing with red cherries; (8) red and brown cherries drying on woven mats in front of the hut; (9) young coffee seedlings in black polybags beside a path.

Accuracy — Coffee cherries grow in clusters along the branches. Coffee is shown only as a crop — nobody drinks it.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-g01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — tobacco plants, children with coffee, text.
```

#### TMG-P08 · Ikan Uceng dan Sungai Jernih

| | |
|---|---|
| Status | Variasi h (baru) · butir `tmg-1-h01` |
| Kode berkas | `challenge.item.tmg-1-h01` → simpan sebagai `challenge.item.tmg-1-h01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | tmg-4-a04 (target ikan uceng): mengisi celah karena uceng belum dikenalkan di cerita |
| Foto referensi | Disarankan |
| `media_note` | sungai pegunungan jernih berbatu, pencari ikan dengan serok kecil, ikan uceng kecil di atas kerikil, warung tepi sungai dengan uceng goreng |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Ikan uceng adalah ikan sungai kecil yang menjadi makanan khas Temanggung. Ikan ini hidup di sungai yang airnya jernih dan biasanya digoreng kering.
>
> **EN:** Uceng is a small river fish and a special food of Temanggung. It lives in clear rivers and is usually fried until crispy.

*Catatan fakta:* "hidup di sungai yang airnya jernih" dan "biasanya digoreng kering"

*Risiko ambigu:* Air sungai dan batu bisa tampak seragam. Pastikan setiap keping punya jangkar yang berbeda.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-h01  (TMG-P08 · Ikan Uceng dan Sungai Jernih)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A clear, rocky mountain river in Temanggung with shallow riffles. An adult fisherman stands knee-deep with a small hand net; tiny slender fish dart over the pebbles. On the riverbank, a simple bamboo warung table holds a plate of crispy fried uceng (small, slender, golden-brown fish) with rice, sambal and fresh vegetables. Green hills and a mountain rise behind.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) a mountain peak and hills under clouds; (2) a leafy tree canopy over the river; (3) a bamboo footbridge crossing the river.
• Middle row — (4) the fisherman with his small hand net; (5) clear riffles over colourful pebbles with tiny slender fish; (6) a big mossy boulder with water splashing.
• Bottom row — (7) the warung table edge with the plate of crispy fried uceng; (8) a small bowl of red sambal and green lalapan on a banana leaf; (9) a bamboo bench with a kettle and a glass of tea.

Accuracy — Uceng are small (about a finger long) and slender, with dark bars or spots — not big fish.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-h01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — pollution, dangerously fast water, text.
```

#### TMG-P09 · Pasar Papringan

| | |
|---|---|
| Status | Variasi i (baru) · butir `tmg-1-i01` |
| Kode berkas | `challenge.item.tmg-1-i01` → simpan sebagai `challenge.item.tmg-1-i01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | motif bambu lintas wilayah (kuda kepang, ongklok, bundengan); pesan peduli lingkungan |
| Foto referensi | Disarankan |
| `media_note` | pasar pagi di bawah rumpun bambu: lapak bambu, jajanan di tampah daun pisang, mainan kayu, pembeli membayar dengan keping bambu, tanpa plastik |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Pasar Papringan di Dusun Ngadiprono, Desa Ngadimulyo, Kecamatan Kedu, digelar di bawah rumpun bambu setiap Minggu Wage dan Minggu Pon. Pembeli membayar dengan pring, alat tukar dari bambu, dan pasar ini tidak memakai plastik.
>
> **EN:** Papringan Market in Ngadiprono Hamlet, Ngadimulyo Village, Kedu District, is held under a bamboo grove on the Javanese market days Minggu Wage and Minggu Pon. Shoppers pay with pring, tokens made of bamboo, and no plastic is used.

*Catatan fakta:* semua isi keterangan (sumber: GNFI 2022)

*Risiko ambigu:* Batang bambu yang berderet bisa menjadi pola berulang. Selingi dengan lapak, orang, dan benda yang berbeda.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-i01  (TMG-P09 · Pasar Papringan)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A lively morning market under a tall, shady bamboo grove in a Temanggung village. Simple stalls are built from bamboo; vendors sell traditional Javanese snacks on banana leaves and round woven trays (tampah), fresh vegetables and wooden toys. Shoppers pay with small bamboo tokens. Everything is natural — baskets, leaves, wood — and there is no plastic anywhere. Sunlight filters through the bamboo leaves onto the ground.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) the bamboo canopy with bright sunbeams breaking through; (2) tall bamboo stems with a hanging woven bamboo lamp (unlit); (3) the thatched roof of a bamboo stall with hanging bunches of bananas.
• Middle row — (4) a vendor in batik with a tampah of colourful traditional snacks; (5) hands exchanging a small bamboo token; (6) a stall of wooden toys and spinning tops.
• Bottom row — (7) stacked woven baskets and a clay water jar; (8) the path covered in fallen bamboo leaves and footprints; (9) a grandmother and a child choosing vegetables from a bamboo basket.

Accuracy — No plastic bags, bottles or banners. The tokens are small pieces of bamboo with no visible writing.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-i01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — plastic, banknotes, text.
```

#### TMG-P10 · Perajin Kuda Kepang

| | |
|---|---|
| Status | Variasi j (baru) · butir `tmg-1-j01` |
| Kode berkas | `challenge.item.tmg-1-j01` → simpan sebagai `challenge.item.tmg-1-j01.webp` |
| Kisi | 3 × 3 kotak |
| Kaitan naskah/soal | tmg-2-c01 (dicat dan dihias kain), tmg-2-c03 (anak muda berlatih), tmg-4-a01, `kenal-temanggung-04` |
| Foto referensi | Disarankan |
| `media_note` | halaman bengkel perajin: menganyam dan mengecat kuda kepang, kuda jadi digantung, tumpukan bilah bambu, remaja siap berlatih, Sumbing di latar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Kuda kepang untuk tari kuda lumping dibuat dari anyaman bambu, lalu dicat dan dihias kain warna-warni. Agar kesenian ini tidak hilang, anak-anak muda diajak berlatih menari kuda lumping.
>
> **EN:** The hobby horses for the kuda lumping dance are made of woven bamboo, then painted and decorated with colourful cloth. So that this art does not disappear, young people are invited to practise the dance.

*Risiko ambigu:* Tumpukan bilah bambu bisa tampak berulang. Tempatkan kuda jadi dengan warna berbeda di beberapa keping.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.tmg-1-j01  (TMG-P10 · Perajin Kuda Kepang)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A craftsman's open workshop yard in a Temanggung village. Adult craftsmen weave thin split-bamboo strips into flat horse shapes, paint them in bold red, white, black and gold patterns, and add manes of black fibre and colourful cloth tassels. Finished hobby horses hang on a bamboo rack; bundles of bamboo strips and poles lean against the wall; Mount Sumbing appears beyond the yard.

Puzzle layout — imagine the square divided into a 3×3 grid (do NOT draw the grid). Put one clear landmark in each area:
• Top row — (1) Mount Sumbing seen over the yard wall, with a cloud; (2) three finished horses (red, white and black) hanging from a beam; (3) the workshop's clay-tile roof with a bird on it.
• Middle row — (4) tall bamboo poles leaning against a plastered wall; (5) a craftsman weaving a bamboo horse shape on his lap; (6) a craftsman painting gold patterns with a brush, plain paint pots beside him.
• Bottom row — (7) bundles of thin bamboo strips tied with string; (8) an unpainted woven horse panel lying on a mat; (9) a teenager in practice clothes holding a finished horse, ready to practise.

Accuracy — The hobby horses are flat cut-outs of woven bamboo, painted, with fibre manes — not 3D toy horses.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Temanggung Regency, Central Java, Indonesia — cool tropical highland between the twin volcanoes Mount Sindoro and Mount Sumbing; dark, fertile volcanic soil; terraced vegetable and coffee fields; bamboo groves, banana plants, tall sengon trees and ferns; Javanese village houses with red clay-tile roofs and plastered walls; a bright, clear morning with scattered white cumulus clouds and sunlight from the left. Do NOT show tobacco plants, cigarettes or smoking.
Level — EASY (3×3 grid): bold, simple shapes, strong colour and brightness contrast, one clear main subject.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.tmg-1-j01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 3×3 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 9 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — sharp tools near children, text on paint pots.
```

### 8.2 Magelang: 4 × 4 kotak (sedang)

Tema Magelang adalah "urutan relief" dan "menghubungkan informasi". Karena itu ada relief Borobudur (dibaca searah jarum jam), tiga candi segaris, perjalanan Waisak yang membawa air dari Jumprit (benang ke Temanggung), dan Borobudur di antara dua pasang gunung kembar (benang ke Sindoro–Sumbing).

| No. | Judul | Variasi | Kode berkas | Gambar katalog | Foto referensi |
|---|---|---|---|---|---|
| MGL-P01 | Stupa Borobudur | Variasi a (sudah ada) | `challenge.item.mgl-1-01` | **Wajib ganti** | Disarankan |
| MGL-P02 | Penari Topeng Ireng | Variasi b (sudah ada) | `challenge.item.mgl-1-02` | Boleh dipakai | **Wajib** |
| MGL-P03 | Candi Mendut | Variasi c (sudah ada) | `challenge.item.mgl-1-03` | Cek bentuk | **Wajib** |
| MGL-P04 | Relief Borobudur | Variasi d (baru) | `challenge.item.mgl-1-d01` | buat baru | **Wajib** |
| MGL-P05 | Candi Pawon | Variasi e (baru) | `challenge.item.mgl-1-e01` | buat baru | **Wajib** |
| MGL-P06 | Gunung Tidar | Variasi f (baru) | `challenge.item.mgl-1-f01` | buat baru | Disarankan |
| MGL-P07 | Getuk Magelang | Variasi g (baru) | `challenge.item.mgl-1-g01` | buat baru | Tidak perlu |
| MGL-P08 | Gerabah Klipoh | Variasi h (baru) | `challenge.item.mgl-1-h01` | buat baru | Disarankan |
| MGL-P09 | Perjalanan Waisak | Variasi i (baru) | `challenge.item.mgl-1-i01` | buat baru | Disarankan |
| MGL-P10 | Borobudur di Antara Dua Pasang Gunung Kembar | Variasi j (baru) | `challenge.item.mgl-1-j01` | buat baru | Disarankan |

#### MGL-P01 · Stupa Borobudur

| | |
|---|---|
| Status | Variasi a (sudah ada) · butir `mgl-1-a01` |
| Kode berkas | `challenge.item.mgl-1-01` → simpan sebagai `challenge.item.mgl-1-01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-magelang-01`, mgl-2-a (stupa, teras, 72 arca), mgl-4-a01 (stupa berlubang) |
| Foto referensi | Disarankan |
| Gambar katalog sekarang | **Wajib ganti** · uji keping: GAGAL (K13 polos; K1~K3, K2~K3, K13~K14 kembar) |
| `media_note` | teras bundar Borobudur saat fajar: stupa berlubang pada berbagai jarak, arca Buddha terlihat dari lubang stupa terdekat, stupa induk, Merapi dan Merbabu di cakrawala |

**Keterangan (sudah ada di bank soal):**

> **ID:** Candi Borobudur dibangun pada abad ke-8 dan ke-9. Di teras bundarnya berdiri 72 stupa berlubang, masing-masing berisi arca Buddha.
>
> **EN:** Borobudur Temple was built in the 8th and 9th centuries. On its round terraces stand 72 perforated stupas, each holding a Buddha statue.

*Risiko ambigu:* Deretan stupa yang mirip adalah sumber ambigu terbesar di seluruh set. Bedakan jarak, ukuran, cahaya, lumut, dan latar di setiap keping.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-01  (MGL-P01 · Stupa Borobudur)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Early morning on the round upper terraces of Borobudur Temple. Bell-shaped perforated stupas with diamond- and square-shaped openings stand in curving rows at different distances; through the openings of the nearest stupa a seated Buddha statue can be seen. The large main stupa rises at the top on the right. Beyond the temple, the green Kedu plain stretches to Mount Merbabu and Mount Merapi under a golden sky.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) Mount Merbabu's peak with a soft cloud; (2) Mount Merapi with a thin white plume; (3) the rising sun with rays between clouds; (4) high wispy clouds and a small flock of birds.
• Row 2 — (5) distant palm groves and rice fields far below; (6) the spire of the main stupa; (7) a row of distant stupas silhouetted against the light; (8) the curve of the terrace edge with trees beyond.
• Row 3 — (9) the sunlit lattice of the nearest stupa with patches of moss; (10) the openings of that stupa showing the seated Buddha statue, whole and calm; (11) mid-distance stupas in partial shade; (12) the body of the main stupa with its base ring.
• Row 4 (bottom) — (13) the stone floor with a drainage spout and lichen; (14) the lotus-cushion base of the nearest stupa; (15) a patch of sunlight and long shadows on the stone floor; (16) the stone stairway leading down to the lower terrace.

Accuracy — Show the stupas respectfully and accurately (Borobudur's perforated stupas, not Thai or Tibetan stupas). The Buddha statue stays whole inside one area, not on a grid line.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — tourists climbing, touching or sitting on stupas; text; snow-capped mountains.
```

#### MGL-P02 · Penari Topeng Ireng

| | |
|---|---|
| Status | Variasi b (sudah ada) · butir `mgl-1-b01` |
| Kode berkas | `challenge.item.mgl-1-02` → simpan sebagai `challenge.item.mgl-1-02.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-magelang-04`, mgl-2-b, mgl-4-a02 (kuluk), pengecoh mgl-4-a07 (dadak merak) |
| Foto referensi | **Wajib** |
| Gambar katalog sekarang | Boleh dipakai · uji keping: LULUS DENGAN CATATAN (K6~K7 tekstur sama) |

**Keterangan (sudah ada di bank soal):**

> **ID:** Penari Topeng Ireng memakai hiasan kepala dari bulu berwarna-warni dan lonceng kecil di kaki. Tarian ini tumbuh di Desa Tuksongo, Borobudur.
>
> **EN:** Topeng Ireng dancers wear colourful feather headdresses and small bells on their ankles. The dance grew in Tuksongo Village, Borobudur.

*Risiko ambigu:* Penari yang kostumnya sama bisa menghasilkan keping kembar. Beri warna kuluk dan pose yang berbeda untuk setiap penari.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-02  (MGL-P02 · Penari Topeng Ireng)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A group of adult Topeng Ireng dancers performs with energy in a village yard near Borobudur. Each wears a tall headdress (kuluk) of brightly coloured feathers fanning upward — every dancer's feathers have a different main colour — with sequins and beads, a sparkling vest, a fringed costume and rows of small bells around the ankles. Their painted faces look bold but friendly. They stamp in rhythm and dust rises. Drummers play at the side; villagers watch; the Menoreh Hills and coconut palms rise behind.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the Menoreh hill line with a cloud; (2) the tip of a red-and-yellow feather headdress; (3) coconut palms against the sky with a tiny Borobudur silhouette far away; (4) the tip of a blue-and-green feather headdress.
• Row 2 — (5) a village roof and banana leaves; (6) dancer A's painted face under the headdress; (7) dancer B's raised arm with fringes swinging; (8) dancer C's purple-and-white headdress seen from the side.
• Row 3 — (9) a drummer with a kendang and a small gong; (10) dancer A's sparkling vest and sash; (11) dancer B's fringed skirt mid-swing; (12) adult villagers clapping.
• Row 4 (bottom) — (13) dust clouds and stamping feet with ankle bells; (14) the packed-earth ground with a dropped colourful feather; (15) dancer C's feet and bells on a woven mat; (16) children sitting with their parents, watching.

Accuracy — Topeng Ireng headdresses are tall fans of dyed feathers worn on the head — NOT the huge peacock-feather mask (dadak merak) of Reog Ponorogo.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-02.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — scary faces, dancers looking at the camera, text.
```

#### MGL-P03 · Candi Mendut

| | |
|---|---|
| Status | Variasi c (sudah ada) · butir `mgl-1-c01` |
| Kode berkas | `challenge.item.mgl-1-03` → simpan sebagai `challenge.item.mgl-1-03.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-magelang-03`, mgl-3-b (Waisak dimulai dari Mendut) |
| Foto referensi | **Wajib** |
| Gambar katalog sekarang | Cek bentuk · uji keping: LULUS |

**Keterangan (sudah ada di bank soal):**

> **ID:** Candi Mendut menyimpan arca Buddha setinggi sekitar tiga meter. Setiap Waisak, prosesi umat Buddha dimulai dari candi ini.
>
> **EN:** Mendut Temple holds a Buddha statue about three metres tall. Every Vesak, the Buddhist procession starts from this temple.

*Risiko ambigu:* Dinding candi yang abu-abu dan halaman rumput bisa tampak seragam. Isi setiap keping dengan relief, pohon, dan benda yang berbeda.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-03  (MGL-P03 · Candi Mendut)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Three-quarter front view of Candi Mendut in morning light: a tall Buddhist temple on a high square base, with a long stairway flanked by carved balustrades leading up to the entrance porch. Carved relief panels of graceful figures decorate the outer walls; the roof rises in tiers topped with small stupas. A huge old banyan tree grows beside the temple yard; the grass is neat; village houses and palms surround it. Match the real temple to the reference photo.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the banyan tree's wide canopy; (2) the top of the temple roof with small stupas; (3) the roof tiers with carved edges; (4) the sky with a distinctive cloud and birds.
• Row 2 — (5) the banyan's hanging aerial roots; (6) an upper wall relief panel; (7) the top of the entrance porch with a carved kala head; (8) the side wall with a relief panel and a palm frond.
• Row 3 — (9) a flower bed with bougainvillea; (10) the middle of the stairway with carved balustrades; (11) the carved base platform; (12) a stone path with a small heritage marker (no writing).
• Row 4 (bottom) — (13) a bench and a garden lamp post; (14) the bottom steps of the stairway; (15) neat grass with fallen banyan leaves; (16) the low fence with the village street and a becak beyond.

Accuracy — This is a Buddhist temple; the big Buddha statue is inside and does not need to be shown. Keep the carvings true to the reference.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-03.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — people climbing the walls, crowds, text.
```

#### MGL-P04 · Relief Borobudur

| | |
|---|---|
| Status | Variasi d (baru) · butir `mgl-1-d01` |
| Kode berkas | `challenge.item.mgl-1-d01` → simpan sebagai `challenge.item.mgl-1-d01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-magelang-02` (buku dari batu), `dialog-magelang-04` (relief teracak), mgl-2-a03 (relief), mgl-3-a (tidak menyentuh relief) |
| Foto referensi | **Wajib** |
| `media_note` | lorong galeri Borobudur: panel relief (kapal bercadik, orang di pantai, pohon, hewan) dengan cahaya samping, pemandu menunjuk tanpa menyentuh, dua anak mengamati |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Dinding Candi Borobudur dihiasi 2.672 panel relief. Relief dibaca berurutan sambil berjalan searah jarum jam, dimulai dari sisi timur, supaya ceritanya utuh. Pengunjung boleh melihat, tetapi tidak boleh menyentuhnya.
>
> **EN:** Borobudur Temple's walls are decorated with 2,672 relief panels. The reliefs are read in order while walking clockwise, starting from the east side, so the story stays whole. Visitors may look, but they must not touch them.

*Catatan fakta:* "searah jarum jam, dimulai dari sisi timur" (sumber: Wikipedia Borobudur)

*Risiko ambigu:* Batu relief berwarna sama di semua bagian. Pakai cahaya samping yang kuat, lumut yang berbeda-beda, langit di atas pagar langkan, dan orang di lorong.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-d01  (MGL-P04 · Relief Borobudur)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A gallery corridor of Borobudur Temple in warm morning side light. Along the wall runs a carved stone relief panel that tells a story — a sailing ship with an outrigger and sails, people on the shore with baskets, trees and animals — with crisp shadows that make every figure readable. On the other side, the balustrade has small niches; above it are a strip of sky and distant hills. A local guide points at the relief while two children look closely, hands behind their backs.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) a strip of sky and distant hills above the balustrade; (2) a balustrade niche with a small seated Buddha statue, whole and calm; (3) the carved garland frieze along the top of the relief wall; (4) the corridor turning a corner in bright sunlight.
• Row 2 — (5) relief: a tree with birds; (6) relief: the ship's sails and mast; (7) relief: the ship's hull with its outrigger and waves; (8) relief: people on the shore carrying baskets.
• Row 3 — (9) relief: animals (a deer and an elephant); (10) the decorative lower border of the relief; (11) the guide's hand pointing — not touching; (12) two children watching with hands behind their backs.
• Row 4 (bottom) — (13) the stone floor with patches of lichen; (14) the balustrade's shadow across the floor; (15) a low rope barrier on short posts; (16) stone steps leading to the next level.

Accuracy — The carvings must look like real Borobudur low reliefs (Javanese style, 8th–9th century): worn grey andesite with moss in the cracks.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-d01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — anyone touching or leaning on the relief, modern graffiti, text.
```

#### MGL-P05 · Candi Pawon

| | |
|---|---|
| Status | Variasi e (baru) · butir `mgl-1-e01` |
| Kode berkas | `challenge.item.mgl-1-e01` → simpan sebagai `challenge.item.mgl-1-e01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-magelang-03` (tiga candi segaris), mgl-3-b |
| Foto referensi | **Wajib** |
| `media_note` | Candi Pawon di taman desa di antara sawah: tangga, panel berukir, stupa kecil di atap, rumah beratap merah, Perbukitan Menoreh di latar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Candi Pawon berdiri di antara Candi Mendut dan Candi Borobudur. Ketiga candi Buddha itu berada hampir pada satu garis lurus.
>
> **EN:** Pawon Temple stands between Mendut Temple and Borobudur Temple. These three Buddhist temples lie almost in one straight line.

*Risiko ambigu:* Candi kecil di tengah dikelilingi langit dan rumput yang luas. Isi sudut-sudut gambar dengan unsur yang berbeda.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-e01  (MGL-P05 · Candi Pawon)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Candi Pawon, a small, elegant Buddhist temple of grey andesite on a raised base, standing in a neat village garden between rice fields. A short stairway leads to its doorway; carved panels decorate its walls; small stupas crown its roof. Village houses with red roofs, frangipani and palms surround it; the Menoreh Hills stretch across the background. Match the real temple to the reference photo.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the Menoreh Hills with a cloud; (2) the top of the temple roof with small stupas; (3) a coconut palm crown; (4) the sky with birds and a distinctive cloud.
• Row 2 — (5) a red-roofed village house; (6) the upper temple wall with carved panels; (7) the doorway with its carved frame; (8) a frangipani tree in bloom.
• Row 3 — (9) the edge of a rice field with a farmer walking; (10) the stairway; (11) the temple base with carved panels; (12) a bamboo fence and banana plants.
• Row 4 (bottom) — (13) a garden path of stepping stones; (14) a flower bed with red canna; (15) neat grass with a garden tap and a bucket; (16) the village lane with a parked bicycle.

Accuracy — A small Buddhist temple, smaller than Mendut. No Hindu elements.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-e01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — people climbing, text.
```

#### MGL-P06 · Gunung Tidar

| | |
|---|---|
| Status | Variasi f (baru) · butir `mgl-1-f01` |
| Kode berkas | `challenge.item.mgl-1-f01` → simpan sebagai `challenge.item.mgl-1-f01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-magelang-03` (paku tanah Jawa), mgl-3-c (Gunung Tidar) |
| Foto referensi | Disarankan |
| `media_note` | tangga batu mendaki di hutan Gunung Tidar, keluarga dan orang berolahraga, celah pohon memperlihatkan atap kota Magelang dan Gunung Sumbing |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Gunung Tidar adalah bukit hijau di tengah Kota Magelang, tingginya sekitar 503 meter di atas permukaan laut. Banyak orang mendaki tangganya untuk berolahraga. Dalam legenda, Gunung Tidar disebut paku tanah Jawa.
>
> **EN:** Mount Tidar is a green hill in the middle of Magelang City, about 503 metres above sea level. Many people climb its steps to exercise. In legend, Mount Tidar is called the nail of Java.

*Risiko ambigu:* Hutan lebat dan tangga panjang bisa tampak berulang. Selingi dengan orang, tiang penunjuk arah (tanpa tulisan), bangku, dan pemandangan kota.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-f01  (MGL-P06 · Gunung Tidar)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A long stone stairway climbs through tall trees on the forested slopes of Mount Tidar, a green hill in the middle of Magelang City. Families and adults in sports clothes climb for exercise; a gap in the trees reveals the red roofs of the city below and Mount Sumbing on the horizon. Morning light streams through the leaves.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) Mount Sumbing on the horizon; (2) a leafy canopy with sunlight; (3) a hawk circling in a patch of sky; (4) the far ridge of Mount Merbabu.
• Row 2 — (5) red rooftops of Magelang city below; (6) the upper bend of the stairway with a handrail; (7) tall pine trunks; (8) a squirrel on a branch.
• Row 3 — (9) two adults jogging up the steps; (10) the middle of the stairway with mossy edges; (11) a resting bench with a water bottle; (12) a direction post with an arrow (no writing) and a rubbish bin.
• Row 4 (bottom) — (13) ferns and wild flowers; (14) the bottom of the stairway with a family starting the climb; (15) an old stone retaining wall with moss; (16) a flowering shrub and fallen leaves.

Accuracy — Show only the hill, the stairs, nature and the city view.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-f01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — graves, military buildings or soldiers, text.
```

#### MGL-P07 · Getuk Magelang

| | |
|---|---|
| Status | Variasi g (baru) · butir `mgl-1-g01` |
| Kode berkas | `challenge.item.mgl-1-g01` → simpan sebagai `challenge.item.mgl-1-g01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | mgl-2-c (Getuk), mgl-4-a03 (getuk), pengecoh mgl-4-a09 (bakpia) |
| Foto referensi | Tidak perlu |
| `media_note` | lapak pasar dari sudut agak tinggi (bukan tampak atas): getuk tiga warna di tampah, singkong, lumpang dan alu, dandang beruap, latar pasar tajam |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Magelang dijuluki Kota Getuk. Getuk dibuat dari singkong yang dikukus, ditumbuk halus, lalu diberi gula.
>
> **EN:** Magelang is nicknamed the Getuk Town. Getuk is made from cassava that is steamed, pounded smooth and then sweetened.

*Risiko ambigu:* Foto makanan close-up dengan latar kabur adalah penyebab ambigu yang paling sering. Wajib memakai fokus dalam dan latar pasar yang ramai tetapi tajam.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-g01  (MGL-P07 · Getuk Magelang)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A traditional market stall in Magelang in the morning, seen from a slightly high angle (NOT top-down). On a round woven bamboo tray (tampah) lined with banana leaves lie neat slices of three-coloured getuk (white, pink and brown layers) sprinkled with grated coconut. Around it are raw cassava roots with brown skin, a wooden mortar and pestle for pounding, a steamer pot with steam and blocks of palm sugar. The seller's hands wrap getuk in banana leaf. Behind, the market alley is busy but sharp: other stalls, hanging bananas, a striped awning.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) wooden market roof beams with a hanging lamp; (2) a striped awning; (3) hanging bunches of bananas; (4) a vegetable seller's stall in the background.
• Row 2 — (5) a stack of fresh banana leaves; (6) the seller's hands folding a banana-leaf parcel; (7) a steamer pot (dandang) with rising steam; (8) an old brass hanging scale with weights.
• Row 3 — (9) a pile of raw cassava roots; (10) the three-coloured getuk slices seen close; (11) a bowl of grated coconut; (12) the wooden mortar and pestle (lumpang dan alu).
• Row 4 (bottom) — (13) a woven basket (bakul); (14) the rim of the tampah and the banana-leaf edge; (15) brown blocks of palm sugar on a leaf; (16) the wooden table edge with a knife resting on a leaf.

Accuracy — Getuk is a soft, dense cassava cake cut into blocks or layers — not bread, and not a cake with frosting.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-g01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — plastic packaging, brand names, blur, text.
```

#### MGL-P08 · Gerabah Klipoh

| | |
|---|---|
| Status | Variasi h (baru) · butir `mgl-1-h01` |
| Kode berkas | `challenge.item.mgl-1-h01` → simpan sebagai `challenge.item.mgl-1-h01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | mgl-2-c (gerabah Klipoh), mgl-4-a04 (kendil), pengecoh mgl-4-a10 (panci logam) |
| Foto referensi | Disarankan |
| `media_note` | halaman perajin gerabah Klipoh: membentuk tanah liat di alat putar tangan, kendil/cobek/kuali dijemur, pembakaran terbuka kecil, anggota keluarga belajar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Warga Dusun Klipoh, sekitar tiga kilometer dari Candi Borobudur, membuat kendil, cobek, dan kuali dari tanah liat. Tanah liat dibentuk, lalu dibakar agar keras. Keahlian ini diwariskan turun-temurun.
>
> **EN:** The people of Klipoh Hamlet, about three kilometres from Borobudur Temple, make cooking pots, grinding bowls and woks from clay. The clay is shaped, then fired to make it hard. This skill is passed down from parents to children.

*Risiko ambigu:* Deretan gerabah yang sama bisa tampak berulang. Variasikan bentuk (kendil, cobek, kuali), warna (abu-abu basah dan merah bata), dan kegiatannya.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-h01  (MGL-P08 · Gerabah Klipoh)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A pottery yard in Klipoh Hamlet near Borobudur. An adult potter sits on a low stool shaping a wet clay pot on a simple hand-turned wooden wheel. Around the yard, freshly shaped grey clay pots (kendil), grinding bowls (cobek) and woks (kuali) dry in the sun on bamboo racks; fired pots in warm reddish-brown are stacked nearby. In a corner, a small, careful open firing of straw and wood smoulders with a little smoke. The green Menoreh Hills rise behind the village.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the Menoreh Hills with a cloud; (2) a coconut palm and a tiled roof; (3) a thin column of smoke rising and fading; (4) the sky with birds.
• Row 2 — (5) bamboo racks with drying grey kendil; (6) the potter's hands shaping clay on the wheel; (7) the small open firing of straw and wood; (8) stacked reddish-brown fired kuali.
• Row 3 — (9) a lump of wet clay on a board; (10) the potter on a low stool with a water bowl; (11) a younger family member watching and learning; (12) cobek grinding bowls lined up.
• Row 4 (bottom) — (13) a heap of straw; (14) clay-stained wooden tools on a mat; (15) a basket of small finished kendil; (16) a broom and a bucket beside the yard wall.

Accuracy — Use earthenware shapes typical of Central Java. The fire stays small and controlled, away from children.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-h01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — metal pots, plastic, text.
```

#### MGL-P09 · Perjalanan Waisak

| | |
|---|---|
| Status | Variasi i (baru) · butir `mgl-1-i01` |
| Kode berkas | `challenge.item.mgl-1-i01` → simpan sebagai `challenge.item.mgl-1-i01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | mgl-3-b (Perjalanan Waisak), mgl-1-c (Mendut), tmg-2-b (Umbul Jumprit): benang Temanggung–Magelang |
| Foto referensi | Disarankan |
| `media_note` | prosesi Waisak yang tenang di jalan desa: biksu membawa air suci dan api, umat berbaju putih dengan bunga teratai, bendera Buddhis, penonton tertib di balik tali, Borobudur di kejauhan |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Setiap Waisak, umat Buddha berjalan kaki dari Candi Mendut ke Candi Pawon, lalu berakhir di Candi Borobudur. Mereka membawa api dan air suci dari Umbul Jumprit, Temanggung. Kita menghormatinya dengan menjaga ketenangan dan memberi jalan.
>
> **EN:** Every Vesak, Buddhists walk from Mendut Temple to Pawon Temple and end at Borobudur Temple. They carry fire and holy water from Umbul Jumprit in Temanggung. We show respect by keeping quiet and making way.

*Risiko ambigu:* Barisan orang berjubah dengan warna yang sama bisa menjadi pola berulang. Bedakan setiap keping dengan benda yang dibawa, payung, bendera, dan latar.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-i01  (MGL-P09 · Perjalanan Waisak)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A calm, respectful Vesak (Waisak) procession on a village road between the temples near Borobudur, in soft daylight. Buddhist monks in saffron and maroon robes walk in two orderly lines; one carries a covered vessel of holy water, another carries a flame in a lantern. Devotees in white clothes follow, holding lotus flowers. Buddhist flags (blue, yellow, red, white and orange stripes) line the road. Onlookers stand quietly behind a rope at the roadside, making way. Borobudur stands on its hill in the distance.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) Borobudur on its hill in the distance; (2) a row of Buddhist flags on poles; (3) the sky with soft clouds; (4) the Menoreh Hills.
• Row 2 — (5) roadside trees hung with small flags; (6) a monk carrying the covered vessel of holy water; (7) a monk carrying a flame in a lantern; (8) a large ceremonial umbrella held above the procession.
• Row 3 — (9) onlookers behind the rope with hands folded; (10) the line of monks walking; (11) devotees in white holding lotus flowers; (12) a village house with potted plants.
• Row 4 (bottom) — (13) the rope and a small post; (14) the road with scattered flower petals; (15) the hems of robes and simple sandals; (16) a child standing quietly, holding a parent's hand.

Accuracy — Respectful and calm. Faces are simple and generic (no real person). No sky lanterns.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-i01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — tourists blocking the path, flash photography, pushing, text.
```

#### MGL-P10 · Borobudur di Antara Dua Pasang Gunung Kembar

| | |
|---|---|
| Status | Variasi j (baru) · butir `mgl-1-j01` |
| Kode berkas | `challenge.item.mgl-1-j01` → simpan sebagai `challenge.item.mgl-1-j01.webp` |
| Kisi | 4 × 4 kotak |
| Kaitan naskah/soal | `kenal-temanggung-01` (gunung kembar), `kenal-magelang-01`, tmg-2-b (Progo), `penutup-02` (kabut tersibak) |
| Foto referensi | Disarankan |
| `media_note` | fajar dari bukit di barat Borobudur: candi terlihat jelas di tengah, kabut tipis hanya di cekungan, Merbabu dan Merapi di cakrawala timur, dek pandang dengan pengunjung |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Candi Borobudur berdiri di antara dua pasang gunung kembar, Sindoro–Sumbing dan Merbabu–Merapi, serta dua sungai, Progo dan Elo. Saat kabut pagi tersibak, candi batu itu terlihat jelas dari bukit di sekitarnya.
>
> **EN:** Borobudur Temple stands between two pairs of twin volcanoes, Sindoro–Sumbing and Merbabu–Merapi, and two rivers, the Progo and the Elo. When the morning mist clears, the stone temple can be seen clearly from the hills around it.

*Catatan fakta:* "dua pasang gunung kembar … dua sungai, Progo dan Elo" (sumber: Wikipedia Borobudur)

*Risiko ambigu:* Kabut di lembah dan langit fajar bisa menjadi area polos. Batasi kabut hanya di lembah yang rendah, dan isi langit dengan matahari, awan, serta puncak gunung.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.mgl-1-j01  (MGL-P10 · Borobudur di Antara Dua Pasang Gunung Kembar)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Sunrise seen from a wooded hill west of Borobudur. The temple sits on its low hill in the middle distance, surrounded by coconut palms and trees, clearly visible. Thin morning mist lies only in the lowest hollows of the valley and is already lifting. On the eastern horizon, Mount Merbabu and Mount Merapi (with a thin plume) stand side by side as the sun rises near them. In the foreground: a wooden viewing deck with a railing, a few adult visitors in light jackets, a parent and child pointing, and a frangipani branch.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) Mount Merbabu's peak; (2) the sun rising with rays; (3) Mount Merapi with a thin plume; (4) clouds with pink and gold edges.
• Row 2 — (5) a distant ridge of layered hills; (6) the top of Borobudur with the main stupa; (7) the temple's stepped terraces lit by the sun; (8) a palm forest catching the light.
• Row 3 — (9) a thin patch of mist with treetops poking through; (10) terraced rice fields; (11) village roofs among trees; (12) tall coconut palms.
• Row 4 (bottom) — (13) the wooden deck railing; (14) an adult visitor with a camera; (15) a parent and child pointing at the temple; (16) a frangipani branch with flowers and leaves.

Accuracy — From this viewpoint, Merapi and Merbabu stand behind the temple on the eastern horizon. Mist covers only small, low areas.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Magelang, Central Java, Indonesia — the green Kedu plain around Borobudur; Mount Merapi (an active volcano, sometimes with a thin white plume) and Mount Merbabu on the eastern horizon, the long Menoreh Hills to the south; rice paddies, coconut palms, banana and kapok trees; grey-black andesite temple stone with patches of lichen and moss; red-tiled village roofs; warm golden morning light from the left.
Level — MEDIUM (4×4 grid): richer detail, medium contrast, sixteen clearly different areas.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.mgl-1-j01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 tile-swap puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — thick mist hiding the temple, drones, text.
```

### 8.3 Wonosobo: 4 × 4 jigsaw (sulit)

Wonosobo adalah wilayah paling berisiko ambigu karena kabut, lautan awan, dan permukaan telaga. Ikuti batas kabut dengan ketat. Kawah dengan pagar dan rambu meneguhkan pesan keselamatan yang sejalan dengan bacaan wnb-3-c dan baris naskah `tuntas-wonosobo-01` yang sudah direvisi. Candi Arjuna dan kebanyakan kawah Dieng berada di wilayah Kabupaten Banjarnegara, jadi tulis "di Dataran Tinggi Dieng".

| No. | Judul | Variasi | Kode berkas | Gambar katalog | Foto referensi |
|---|---|---|---|---|---|
| WNB-P01 | Telaga Warna | Variasi a (sudah ada) | `challenge.item.wnb-1-05` | Boleh dipakai | Disarankan |
| WNB-P02 | Matahari Terbit di Sikunir | Variasi b (sudah ada) | `challenge.item.wnb-1-06` | Boleh dipakai | Disarankan |
| WNB-P03 | Mi Ongklok | Variasi c (sudah ada) | `challenge.item.wnb-1-07` | Disarankan ganti | Disarankan |
| WNB-P04 | Kompleks Candi Arjuna | Variasi d (baru) | `challenge.item.wnb-1-d01` | buat baru | **Wajib** |
| WNB-P05 | Kawah di Dieng: Tetap di Balik Pagar | Variasi e (baru) | `challenge.item.wnb-1-e01` | buat baru | Disarankan |
| WNB-P06 | Carica, Pepaya Gunung | Variasi f (baru) | `challenge.item.wnb-1-f01` | buat baru | Disarankan |
| WNB-P07 | Bundengan | Variasi g (baru) | `challenge.item.wnb-1-g01` | buat baru | **Wajib** |
| WNB-P08 | Kebun Teh Tambi | Variasi h (baru) | `challenge.item.wnb-1-h01` | buat baru | Disarankan |
| WNB-P09 | Tari Topeng Lengger | Variasi i (baru) | `challenge.item.wnb-1-i01` | buat baru | **Wajib** |
| WNB-P10 | Embun Upas | Variasi j (baru) | `challenge.item.wnb-1-j01` | buat baru | Disarankan |

#### WNB-P01 · Telaga Warna

| | |
|---|---|
| Status | Variasi a (sudah ada) · butir `wnb-1-a01` |
| Kode berkas | `challenge.item.wnb-1-05` → simpan sebagai `challenge.item.wnb-1-05.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | `kenal-wonosobo-02`, `dialog-wonosobo-09/16` (Mbah menunggu di Telaga Warna), wnb-3-a (Dua Kabar tentang Telaga Warna) |
| Foto referensi | Disarankan |
| Gambar katalog sekarang | Boleh dipakai · uji keping: LULUS |
| `media_note` | Telaga Warna dari titik pandang tinggi: air hijau, biru, dan kekuningan berbelerang, Telaga Pengilon yang lebih gelap, hutan cemara, jalan papan dengan pengunjung kecil |

**Keterangan (sudah ada di bank soal):**

> **ID:** Air Telaga Warna di Dieng mengandung belerang. Saat terkena cahaya matahari, warnanya tampak berganti: hijau, biru, atau kekuningan. Airnya tidak untuk diminum.
>
> **EN:** The water of Telaga Warna in Dieng contains sulphur. In sunlight its colour seems to change: green, blue, or yellowish. The water is not for drinking.

*Catatan fakta:* Rumusan warna diselaraskan dengan naskah (`kenal-wonosobo-02`): hijau, biru, atau kekuningan.

*Risiko ambigu:* Permukaan danau yang luas adalah risiko terbesar di Wonosobo. Wajib ada gradasi warna air (toska ke hijau ke putih susu di tepi), riak, pantulan pohon, dan endapan belerang di tepi.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-05  (WNB-P01 · Telaga Warna)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A high viewpoint over Telaga Warna on the Dieng Plateau on a clear morning. The lake shows several colours at once — green, blue-turquoise, and yellowish shallows near the shore where sulphur deposits lie — beside the smaller, darker Telaga Pengilon, separated from it by a strip of forest. Forested hills with cypress and pine surround the lakes; a thin band of mist lingers only over the farthest valley. A small wooden boardwalk with tiny visitors runs along one shore.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) far hills with a thin band of mist; (2) a ridge with pine trees and a cloud; (3) clear sky with one distinctive cloud; (4) a hillside with potato terraces.
• Row 2 — (5) a forested slope; (6) Telaga Pengilon's darker water with tree reflections; (7) the forest strip between the two lakes; (8) the far side of Telaga Warna in turquoise, with reflections.
• Row 3 — (9) a shoreline with white-yellow sulphur deposits; (10) the middle of Telaga Warna: emerald ripples and sun glints; (11) yellowish shallows over pale sulphur sand; (12) the wooden boardwalk with a few tiny visitors.
• Row 4 (bottom) — (13) a mossy rock outcrop in the foreground; (14) a cypress branch; (15) ferns and small wildflowers; (16) the viewpoint's wooden railing.

Accuracy — The lake's colours come from sulphur; the water is not for drinking — nobody drinks or swims.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-05.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — boats, swimmers, thick mist, text.
```

#### WNB-P02 · Matahari Terbit di Sikunir

| | |
|---|---|
| Status | Variasi b (sudah ada) · butir `wnb-1-b01` |
| Kode berkas | `challenge.item.wnb-1-06` → simpan sebagai `challenge.item.wnb-1-06.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | wnb-3-b (Berangkat ke Bukit Sikunir: jaket, senter, sampah), `dialog-wonosobo-02` (negeri di atas awan) |
| Foto referensi | Disarankan |
| Gambar katalog sekarang | Boleh dipakai · uji keping: LULUS DENGAN CATATAN (K3~K4 langit mirip) |
| `media_note` | fajar dari puncak Sikunir: lautan awan bertekstur (emas dekat matahari, ungu-biru menjauh), kerucut Sindoro menembus awan, pengunjung berjaket di balik pagar, anak memegang senter |

**Keterangan (sudah ada di bank soal):**

> **ID:** Bukit Sikunir ada di Desa Sembungan, desa tertinggi di Pulau Jawa (sekitar 2.300 meter). Wisatawan mendaki sebelum subuh untuk melihat matahari terbit di atas awan.
>
> **EN:** Sikunir Hill is in Sembungan Village, the highest village in Java (about 2,300 metres). Visitors climb before dawn to watch the sun rise above the clouds.

*Risiko ambigu:* Lautan awan dan langit fajar adalah area yang seragam. Wajib ada tekstur awan yang bervariasi, gradasi warna yang kuat, puncak gunung yang menembus awan, dan latar depan berisi orang serta benda.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-06  (WNB-P02 · Matahari Terbit di Sikunir)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Golden sunrise seen from the top of Sikunir Hill above Sembungan, the highest village in Java. Below, a sea of clouds fills the valleys; its billows glow gold near the sun and turn blue-violet farther away, with gaps showing dark forest. The cone of Mount Sindoro rises out of the clouds near the rising sun, with more distant peaks faint on the horizon. In the foreground: a grassy hilltop with a wooden railing, a cypress tree, and adult visitors and a family in thick jackets, beanies and gloves, one of them holding a torch.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) deep blue sky with the last faint stars; (2) pink-orange high clouds; (3) the sun with rays; (4) violet sky with a thin cloud.
• Row 2 — (5) faint distant volcano peaks on the horizon; (6) Mount Sindoro's cone with its sunlit edge; (7) the golden cloud sea near the sun; (8) the blue-violet cloud sea with a dark ridge.
• Row 3 — (9) a gap in the clouds showing dark forest and a tiny village light; (10) Sindoro's lower slope sinking into the clouds; (11) a cypress tree on the hilltop; (12) silhouettes of adult visitors in jackets and beanies.
• Row 4 (bottom) — (13) grass with sparkling dew; (14) the wooden railing; (15) rocks and the trail; (16) a child with a parent, holding a torch.

Accuracy — Visitors are dressed warmly; everyone stays on the path behind the railing.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-06.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — people at the cliff edge, litter, text.
```

#### WNB-P03 · Mi Ongklok

| | |
|---|---|
| Status | Variasi c (sudah ada) · butir `wnb-1-c01` |
| Kode berkas | `challenge.item.wnb-1-07` → simpan sebagai `challenge.item.wnb-1-07.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | `kenal-wonosobo-03`, wnb-2-b (Mi Ongklok), wnb-4-a03 (keranjang ongklok), pengecoh wnb-4-a07 |
| Foto referensi | Disarankan |
| Gambar katalog sekarang | Disarankan ganti · uji keping: LULUS DENGAN CATATAN (K10~K11 pola mi sama) |
| `media_note` | warung mi ongklok dari sudut 3/4 (bukan tampak atas): mangkuk mi ongklok, sate sapi, tempe kemul, juru masak mencelup keranjang ongklok, jendela ke perbukitan berkabut |

**Keterangan (sudah ada di bank soal):**

> **ID:** Mi ongklok adalah makanan khas Wonosobo. Mi dan sayur direbus di keranjang bambu kecil bernama ongklok, lalu disiram kuah kental dan disajikan bersama sate sapi.
>
> **EN:** Ongklok noodles are Wonosobo's special dish. The noodles and vegetables are boiled in a small bamboo basket called an ongklok, then covered with a thick sauce and served with beef satay.

*Risiko ambigu:* Catatan gambar lama berbunyi "tampak atas". Hasilnya, keping pinggir hanya berisi permukaan meja. Ganti menjadi sudut 3/4 dengan suasana warung.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-07  (WNB-P03 · Mi Ongklok)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A warm, cosy warung kitchen in Wonosobo on a chilly morning, seen from a three-quarter angle (NOT top-down). On a wooden table: a bowl of mi ongklok — yellow noodles, cabbage and chives under a thick, glossy brown sauce with a drizzle of peanut sauce — next to a plate of beef satay with peanut sauce, golden tempe kemul fritters, and cups of hot tea with steam. Behind, the cook dips a small bamboo basket with a long handle (the ongklok) into a pot of boiling water as steam rises. A window shows misty green hills with potato terraces.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the window with misty hills and terraces; (2) small bamboo ongklok baskets hanging on hooks; (3) the cook's upper body and the rising steam (face kept simple); (4) a shelf with glass jars and enamel plates.
• Row 2 — (5) a wooden wall with a round wall clock; (6) the boiling pot with the ongklok basket being dipped; (7) the stove flame under the pot; (8) noodle nests and cabbage on a tray.
• Row 3 — (9) an enamel cup of tea with steam; (10) the bowl of mi ongklok with chives on top; (11) the plate of beef satay with peanut sauce; (12) tempe kemul fritters on a plate.
• Row 4 (bottom) — (13) the table edge with a spoon and fork; (14) a small dish of chillies and lime; (15) a banana leaf and a napkin holder; (16) a bench with a thick jacket draped over it.

Accuracy — The ongklok is a small cone-shaped bamboo basket on a handle, dipped again and again into boiling water.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-07.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — a top-down view, text on walls or menus, brand logos.
```

#### WNB-P04 · Kompleks Candi Arjuna

| | |
|---|---|
| Status | Variasi d (baru) · butir `wnb-1-d01` |
| Kode berkas | `challenge.item.wnb-1-d01` → simpan sebagai `challenge.item.wnb-1-d01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | `kenal-wonosobo-02` (salah satu candi Hindu tertua di Jawa) |
| Foto referensi | **Wajib** |
| `media_note` | deretan candi Hindu kecil dari andesit gelap di lapangan rumput Dieng, Candi Semar menghadap Candi Arjuna, bukit berteras kentang, pengunjung berjaket |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Kompleks Candi Arjuna di Dataran Tinggi Dieng adalah salah satu kompleks candi Hindu tertua di Jawa. Candi-candinya diberi nama tokoh wayang, seperti Arjuna, Semar, Srikandi, dan Puntadewa.
>
> **EN:** The Arjuna Temple complex on the Dieng Plateau is one of the oldest Hindu temple complexes in Java. Its temples are named after wayang characters such as Arjuna, Semar, Srikandi and Puntadewa.

*Catatan fakta:* "diberi nama tokoh wayang". Lokasi administratifnya Kabupaten Banjarnegara, jadi tulis "di Dataran Tinggi Dieng", jangan "di Wonosobo".

*Risiko ambigu:* Lapangan rumput yang luas di depan candi. Isi dengan jalan setapak, pengunjung, bunga, dan genangan air.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-d01  (WNB-P04 · Kompleks Candi Arjuna)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
The Arjuna temple complex on the Dieng Plateau on a crisp morning: a row of small, dark-grey andesite Hindu temples standing on a wide grassy field, with the small rectangular Candi Semar facing Candi Arjuna. Hills with steep potato terraces and cypress trees surround the field; a thin band of mist lies only in a far valley. A few visitors in jackets walk along a stone path.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) a hillside with potato terraces; (2) cypress trees with a cloud; (3) a far ridge with a thin mist band; (4) clear sky with a bird.
• Row 2 — (5) the roof of Candi Puntadewa; (6) the roof of Candi Arjuna; (7) Candi Srikandi; (8) Candi Sembadra at the end of the row.
• Row 3 — (9) the small rectangular Candi Semar; (10) Candi Arjuna's doorway and stairs; (11) the stone path with visitors in jackets; (12) grass with small yellow wildflowers.
• Row 4 (bottom) — (13) a low stone border; (14) a small heritage marker (no writing); (15) a puddle reflecting the sky; (16) a wooden bench and a rubbish bin.

Accuracy — The Dieng temples are small and simple — no Buddhist stupas. Match their order and shapes to the reference photo.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-d01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — climbing, thick fog, text.
```

#### WNB-P05 · Kawah di Dieng: Tetap di Balik Pagar

| | |
|---|---|
| Status | Variasi e (baru) · butir `wnb-1-e01` |
| Kode berkas | `challenge.item.wnb-1-e01` → simpan sebagai `challenge.item.wnb-1-e01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | `dialog-wonosobo-03/04` (pesan berantai kawah), `tuntas-wonosobo-01`, wnb-3-c (Kabar tentang Kawah) |
| Foto referensi | Disarankan |
| `media_note` | kawah beruap di Dieng: kolam lumpur abu-abu, endapan belerang kuning, pagar pengaman dan jalan papan, rambu bergambar tanpa tulisan, petugas, pengunjung di balik pagar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Dataran Tinggi Dieng terbentuk oleh kegiatan gunung api, sehingga di sana ada beberapa kawah. Gas di sekitar kawah dapat berbahaya, jadi pengunjung harus mengikuti rambu dan tetap berdiri di belakang pagar pembatas.
>
> **EN:** The Dieng Plateau was formed by volcanic activity, so it has several craters. Gas around a crater can be harmful, so visitors must follow the signs and stay behind the safety fence.

*Risiko ambigu:* Uap putih dan tanah gundul bisa tampak seragam. Batasi uap, lalu tampilkan endapan kuning-oranye, pagar, rambu bergambar, dan orang.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-e01  (WNB-P05 · Kawah di Dieng: Tetap di Balik Pagar)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A steaming volcanic crater on the Dieng Plateau on a clear day. A grey mud pool bubbles; small vents release white steam; the ground is crusted with yellow sulphur and orange-brown mineral stains. A wooden safety fence and boardwalk keep visitors at a safe distance; red-and-white warning signs show only pictograms (a triangle with an exclamation mark, a gas-mask symbol). An officer in a uniform vest signals visitors to stay back; adults and a family in jackets take photos from behind the fence. Hills with potato terraces rise behind.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) hills with potato terraces; (2) blue sky with a cloud; (3) the wispy top of a steam plume, thin and partly see-through; (4) a ridge with pine trees.
• Row 2 — (5) a small, distant steaming vent; (6) the crater rim with a yellow sulphur crust; (7) the bubbling grey mud pool; (8) an orange-brown stained slope.
• Row 3 — (9) the wooden boardwalk; (10) the safety fence with a pictogram warning sign; (11) the officer in a vest signalling to stay back; (12) visitors taking photos from behind the fence.
• Row 4 (bottom) — (13) a fence post with a rope; (14) a gravel path with small puddles; (15) a child holding a parent's hand behind the fence; (16) a small stall hanging face masks for sale.

Accuracy — Everyone stays behind the fence. Steam covers only small areas.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-e01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — anyone near the mud pool, thick steam hiding the scene, text.
```

#### WNB-P06 · Carica, Pepaya Gunung

| | |
|---|---|
| Status | Variasi f (baru) · butir `wnb-1-f01` |
| Kode berkas | `challenge.item.wnb-1-f01` → simpan sebagai `challenge.item.wnb-1-f01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | `kenal-wonosobo-03` (manisan carica), wnb-2-a (carica dan ketinggian), wnb-4-a02, pengecoh wnb-4-a05 (pepaya biasa) |
| Foto referensi | Disarankan |
| `media_note` | kebun carica di teras Dieng: pohon carica berbuah kuning kecil bergerigi, petani berjaket memetik, teras rumah dengan buah dikupas dan stoples manisan carica |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Carica adalah pepaya gunung yang buahnya kecil. Carica tumbuh baik di udara sejuk pada ketinggian 1.400–2.400 meter, dan buahnya sering dibuat manisan.
>
> **EN:** Carica is a mountain papaya with small fruit. It grows well in cool air at 1,400–2,400 metres, and its fruit is often made into sweets.

*Risiko ambigu:* Daun dan buah bisa tampak berulang. Bedakan setiap keping dengan petani, keranjang, teras rumah, dan stoples.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-f01  (WNB-P06 · Carica, Pepaya Gunung)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A carica garden on a terraced slope of the Dieng Plateau on a cool morning. Carica trees look like small papaya trees with lobed leaves and clusters of small, yellow, ridged fruits growing along the trunk. An adult farmer in a jacket and beanie picks ripe fruit into a bamboo basket. On the porch of a nearby house, fruit is peeled and cut, and glass jars of manisan carica (yellow pieces in clear syrup) stand in a row. Potato terraces and misty hills lie beyond.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) a far hill with thin mist; (2) the crown of a carica tree with lobed leaves; (3) a pine tree with a cloud; (4) a house roof with a little chimney smoke.
• Row 2 — (5) a stone terrace wall; (6) a cluster of yellow and green carica fruits on the trunk; (7) the farmer reaching up to pick; (8) a second carica tree against the slope.
• Row 3 — (9) rows of potato plants; (10) a bamboo basket full of yellow carica; (11) the porch table with peeled, cut fruit; (12) glass jars of manisan carica in a row.
• Row 4 (bottom) — (13) the ground with fallen leaves; (14) stone steps up to the porch; (15) a cat sleeping on a mat; (16) a wooden bench with a thermos.

Accuracy — Carica fruits are small (about fist-sized), yellow and five-ridged — much smaller than lowland papaya.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-f01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — big lowland papayas, brand labels on jars, text.
```

#### WNB-P07 · Bundengan

| | |
|---|---|
| Status | Variasi g (baru) · butir `wnb-1-g01` |
| Kode berkas | `challenge.item.wnb-1-g01` → simpan sebagai `challenge.item.wnb-1-g01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | wnb-4-a01 (target bundengan), pengecoh angklung dan sasando: mengisi celah karena bundengan belum dikenalkan di cerita |
| Foto referensi | **Wajib** |
| `media_note` | pemusik dewasa memetik bundengan di teras rumah, dua anak mendengarkan; di sawah, penggembala bebek memakai kowangan (tudung bambu) di punggung |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Bundengan adalah alat musik khas Wonosobo. Dulu benda ini berupa kowangan, tudung anyaman bambu yang melindungi penggembala bebek dari panas dan hujan. Tudung itu kemudian diberi senar dan dipetik; suaranya mirip gamelan.
>
> **EN:** The bundengan is a musical instrument from Wonosobo. It began as a kowangan, a woven bamboo hood that protected duck herders from the sun and rain. Strings were later added to pluck, and it sounds like a gamelan.

*Catatan fakta:* "suaranya mirip gamelan" (sumber: GNFI 2019)

*Risiko ambigu:* Bundengan yang berwarna cokelat bambu bisa menyatu dengan latar. Beri kontras, dan tambahkan unsur sawah serta bebek.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-g01  (WNB-P07 · Bundengan)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
On the porch of a village house in Wonosobo, an adult musician sits on a low bamboo bench playing a bundengan: a large curved hood about as tall as a seated person, shaped like a shield that narrows at the top, woven from bamboo and covered outside with sheets of bamboo sheath. Inside, strings and thin bamboo strips are stretched across, and the musician plucks them with both hands. Two children sit and listen. Beyond the porch, in a harvested rice field, a duck herder wearing the same kind of bamboo hood on his back walks behind a flock of ducks; misty hills rise in the distance. Match the instrument to the reference photo.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) misty hills with thin mist; (2) the duck herder wearing a bamboo hood in the field; (3) a flock of white and brown ducks in the paddy; (4) the sky with a cloud.
• Row 2 — (5) the porch roof beam with hanging corn cobs; (6) the narrow top of the bundengan; (7) the musician's face and hands plucking; (8) a window with a wooden frame.
• Row 3 — (9) two children sitting and listening; (10) the woven body of the bundengan; (11) the strings and bamboo strips inside the hood; (12) the end of the bench with a kettle.
• Row 4 (bottom) — (13) a woven floor mat; (14) the musician's feet in sandals; (15) the base of the bundengan resting on the floor; (16) a potted plant and a cat.

Accuracy — The bundengan is NOT an angklung (shaken bamboo tubes) and NOT a sasando (a palm-leaf harp from Rote). It is a curved woven hood with strings inside.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-g01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — guitar or harp shapes, text.
```

#### WNB-P08 · Kebun Teh Tambi

| | |
|---|---|
| Status | Variasi h (baru) · butir `wnb-1-h01` |
| Kode berkas | `challenge.item.wnb-1-h01` → simpan sebagai `challenge.item.wnb-1-h01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | wnb-4-a04 (daun teh Tambi), pengecoh wnb-4-a10 (biji kopi); Sindoro menghubungkan Temanggung dan Wonosobo |
| Foto referensi | Disarankan |
| `media_note` | kebun teh Tambi: deretan teh di lereng, pemetik dewasa bercaping dengan keranjang, pohon peneduh, truk kecil di jalan berkelok, bangunan pabrik lama, Sindoro di latar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Kebun teh Tambi di Wonosobo sudah ada sejak tahun 1865. Pucuk daun teh dipetik, diolah, lalu diseduh menjadi minuman hangat.
>
> **EN:** The Tambi tea estate in Wonosobo has been there since 1865. The young tea leaves are picked, processed and then brewed into a warm drink.

*Risiko ambigu:* Deretan semak teh adalah pola berulang yang klasik. Setiap keping harus punya pemetik, pohon, jalan, bangunan, atau gunung.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-h01  (WNB-P08 · Kebun Teh Tambi)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Rolling rows of tea bushes on the slopes of the Tambi tea estate in Wonosobo on a cool, bright morning. Adult tea pickers in conical hats, with baskets on their backs, pick young shoots; tall shade trees stand among the rows; a narrow road winds through with a small truck carrying sacks of leaves; an old estate building with a red roof stands in the distance. Mount Sindoro rises behind under drifting clouds; a thin band of mist lies in a far valley.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) Mount Sindoro's summit; (2) a large cloud; (3) a far ridge with a thin mist band; (4) the sky with a flock of birds.
• Row 2 — (5) the old estate building with a red roof; (6) a tall shade tree; (7) the winding road with a small truck; (8) tea rows curving over a far slope.
• Row 3 — (9) a picker with a basket on her back; (10) a tea bush with fresh light-green shoots; (11) a picker in a conical hat bending over; (12) the path between rows with small flowers.
• Row 4 (bottom) — (13) a basket full of tea shoots; (14) tea leaves close up in the foreground; (15) a stone marker and an irrigation ditch; (16) the trunk of a shade tree with ferns.

Accuracy — The tea pickers are adults; the part they pick is the bud with the top two leaves.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-h01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — text on the building, children working, brand logos.
```

#### WNB-P09 · Tari Topeng Lengger

| | |
|---|---|
| Status | Variasi i (baru) · butir `wnb-1-i01` |
| Kode berkas | `challenge.item.wnb-1-i01` → simpan sebagai `challenge.item.wnb-1-i01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | `kenal-wonosobo-03` (tari Lengger); bisa dihubungkan dengan bundengan, yang dipakai mengiringi lagu-lagu lenggeran |
| Foto referensi | **Wajib** |
| `media_note` | pentas Topeng Lengger di pendapa desa pada sore hari: penari bertopeng kayu dan penari berkebaya dengan sampur, pemain gamelan, penonton berjaket, bukit berkabut |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Tari Topeng Lengger adalah tari khas Wonosobo. Penarinya memakai topeng yang menggambarkan tokoh-tokoh dalam cerita. Pada tahun 2020, tarian ini ditetapkan sebagai Warisan Budaya Takbenda Indonesia.
>
> **EN:** The Topeng Lengger dance is a traditional dance of Wonosobo. The dancers wear masks that show the characters in a story. In 2020, the dance was named part of Indonesia's Intangible Cultural Heritage.

*Catatan fakta:* "topeng menggambarkan tokoh" dan "Warisan Budaya Takbenda 2020" (sumber: Visit Jawa Tengah)

*Risiko ambigu:* Panggung dan kostum bisa tampak berulang. Bedakan setiap keping dengan alat musik, penari, penonton, dan hiasan.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-i01  (WNB-P09 · Tari Topeng Lengger)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
A Topeng Lengger performance in warm late-afternoon light on the open stage (pendopo) of a Wonosobo village. A dancer wearing a carved, painted wooden mask moves gracefully; another dancer in a kebaya, batik jarik and a flowing sampur scarf dances beside. Gamelan musicians play kendang, saron and a hanging gong at the side. Villagers in jackets watch. Misty hills glow behind the carved wooden roof beams. Match the masks and costumes to the reference photo.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) the carved wooden roof beams of the pendopo; (2) hills with one thin mist band in golden late-afternoon light; (3) hanging cloth decorations; (4) a tall cypress tree lit from the side, with a few birds.
• Row 2 — (5) a gong hanging on a carved frame; (6) the masked dancer's mask up close; (7) the second dancer's face with a flower hair ornament; (8) a musician playing the kendang.
• Row 3 — (9) saron players; (10) the masked dancer's costume and sash; (11) the flowing sampur scarf in mid-movement; (12) adult villagers in jackets watching.
• Row 4 (bottom) — (13) the wooden stage floorboards; (14) the dancers' feet and the hem of the jarik; (15) the carved stage edge with a small oil lamp; (16) children in jackets sitting with a parent.

Accuracy — The masks are Javanese carved wooden masks, not horror masks.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-i01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — scary faces, modern stage lights, text.
```

#### WNB-P10 · Embun Upas

| | |
|---|---|
| Status | Variasi j (baru) · butir `wnb-1-j01` |
| Kode berkas | `challenge.item.wnb-1-j01` → simpan sebagai `challenge.item.wnb-1-j01.webp` |
| Kisi | 4 × 4 jigsaw |
| Kaitan naskah/soal | wnb-2-c (Embun Upas), wnb-2-a02 (embun membeku) |
| Foto referensi | Disarankan |
| `media_note` | pagi berembun beku di Dieng: es tipis putih menutupi sebagian rumput dan daun kentang, petani berjaket memeriksa tanaman, pengunjung memotret, siluet candi kecil di latar |

**Keterangan (sudah dimasukkan ke bank soal, status `needs_verification`):**

> **ID:** Pada musim kemarau, suhu pagi di Dataran Tinggi Dieng bisa turun di bawah 0 °C, sehingga embun membeku menjadi es tipis. Warga menyebutnya embun upas. Upas berarti racun, sebab es ini bisa merusak tanaman kentang.
>
> **EN:** In the dry season, early-morning temperatures on the Dieng Plateau can drop below 0 °C, so the dew freezes into thin ice. People call it embun upas. Upas means poison, because the ice can damage potato plants.

*Risiko ambigu:* Es putih bisa menutupi seluruh gambar. Es harus berselang-seling dengan hijau, cokelat, dan cahaya matahari yang rendah.

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: challenge.item.wnb-1-j01  (WNB-P10 · Embun Upas)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Puzzle adaptation of the GELITA direction: a clear morning or warm golden-hour daytime scene (never night). Brass gold lives in the sunlight and highlights; deep navy appears only in small shadow accents and distant mountain shade, never as a large dark sky. Rich, organised detail across the whole square — not clutter. Real places, buildings, costumes and objects keep their true shapes and proportions; only the rendering is stylized.
Characters: do NOT include Jaka or Mbah Kedu — this is a scene asset. Attached GELITA references are for render finish and mood only; never copy their characters, text or logo into the picture.

SUBJECT AND COMPOSITION
Sunrise on a frosty dry-season morning on the Dieng Plateau. White ice crystals (embun upas) cover patches of grass and potato leaves, while other parts of the field still show green and brown; where the low sun touches, the frost melts into sparkling droplets. Adult farmers in thick jackets, beanies and sarongs over their shoulders inspect the frosted potato plants; visitors in jackets photograph the frost; their breath shows in the cold air. Silhouettes of the small Dieng temples and the hills stand in the background; a thin band of mist lies over the far edge of the field.

Puzzle layout — imagine the square divided into a 4×4 grid (do NOT draw the grid). Put one clear landmark in each area:
• Row 1 (top) — (1) hills with the sun just above the ridge; (2) silhouettes of the small Dieng temples; (3) a clear sky gradient with a pale moon; (4) cypress trees.
• Row 2 — (5) a thin mist band over the far field; (6) a farmer inspecting potato leaves; (7) visitors photographing the frost; (8) a stone field wall.
• Row 3 — (9) frosted potato leaves close up; (10) half-melted frost glittering in the sunlight; (11) frosted grass with footprints; (12) a wooden bench covered in ice crystals.
• Row 4 (bottom) — (13) a frozen puddle with cracked ice; (14) a farmer's boots and a hoe; (15) frosted cabbage; (16) an enamel mug of hot tea steaming on the bench.

Accuracy — The frost is thin and patchy, not deep snow.
Use a verified licensed photograph wherever possible. If illustrating, attach a verified photo and faithfully preserve real architecture, costume, anatomy and proportions. Never invent historical detail. Use the photo only as a factual reference; compose a new picture as described here, do not copy the photo.
Place — Wonosobo and the Dieng Plateau, Central Java, Indonesia — cool highland at about 2,000 m; steep terraced potato and cabbage fields, cypress (cemara) and pine trees, carica (mountain papaya) trees; houses with zinc or clay-tile roofs; people in thick jackets, knitted beanies and sarongs over their shoulders; crisp, cool, clear light from the left. Mist is allowed ONLY as thin, partial bands in distant valleys and must never hide more than a small part of the picture.
Level — HARD (4×4 jigsaw): atmospheric, with subtler colour shifts, but each of the sixteen areas still has its own unique landmark.
People — Stylized like the GELITA references but anatomically correct: Javanese Indonesians of mixed ages in modest local clothing, calm and friendly, never looking at the camera; no real or famous person; no brand logos; children only with adults and only doing safe things.
Compose distinctive features across the whole square for puzzle tiles.

DELIVERY
Target canvas 1200 × 1200 pixels; aspect 1:1 (generate at the highest square resolution available; it will be resized to exactly 1200 × 1200). Complete subject within canvas, proportional scaling only. Opaque, full-bleed background matching the scene brief. Deliver WebP for raster assets, named challenge.item.wnb-1-j01.webp.

CONSTRAINTS
No watermarks, signatures, unintended text, extra limbs, duplicate subjects, cropped key features, blurry edges, neon colours, clutter, or fake checkerboard transparency. No painterly 2D, gouache, paper grain, or conflicting Jaka costume.
Puzzle safety — This image will be cut into a 4×4 jigsaw puzzle for children:
• ONE continuous natural scene — no collage, no panels, no grid lines.
• Full-bleed to every edge; NO plain, white, transparent, studio or empty background.
• All 16 areas have different landmarks; no two areas look alike.
• No large uniform patches: sky, water, walls, mist and ground carry clouds, ripples, texture, objects or changing light.
• Deep focus everywhere: no bokeh, blur, vignette, dark corners or lens flare.
• Asymmetric; light from one side; the horizon rises and falls naturally, never one straight line across.
• Faces and statues sit fully inside one area, away from the grid lines.
• No text, letters, numbers, logos, watermarks, frames or borders.
Avoid — snow drifts, snowmen, text.
```

### 8.4 Prompt perbaikan (tempel di percakapan yang sama)

| Masalah | Prompt perbaikan |
|---|---|
| Adegan malam atau terlalu gelap | `Keep the same composition and subject, but re-light it as a clear morning with warm golden sunlight from the left. No night sky; deep navy only in small shadows.` |
| Ada area polos (misalnya kiri atas berisi langit kosong) | `Keep the picture exactly the same, but the top-left area is almost empty sky. Add a distinctive cloud, shaped differently from the others, and a small flock of birds there.` |
| Jaka, Mbah Kedu, atau huruf logo ikut tergambar | `Remove the character and any lettering. This is a scene asset: no Jaka, no Mbah Kedu, no logo. Keep everything else the same.` |
| Latar kabur | `Keep everything the same, but make the background as sharp and detailed as the foreground. Remove all blur and bokeh.` |
| Sudut gelap (vinyet) | `Remove the dark vignette. The corners must be as bright and detailed as the centre.` |
| Hasilnya kolase atau ada garis kisi | `This looks like a collage or grid. Redraw it as ONE continuous natural scene with no panels and no lines.` |
| Muncul tulisan atau logo | `Remove every letter, number and logo. Signs may only show simple pictograms.` |
| Kabut terlalu tebal | `There is too much mist. Keep mist only as thin bands in the distant valleys; everything else must be clear and detailed.` |
| Pola terlalu seragam (stupa, deret teh) | `The [stupas] look too similar. Give each one a different distance, size, light and moss pattern, and add a unique nearby object next to each.` |
| Benda penting terpotong garis tengah | `Move the [statue] slightly to the left so it is not in the exact centre of the picture.` |
| Bentuk benda budaya keliru | `The [bundengan] is not accurate. Use the attached reference photo for its shape: [uraikan ciri yang benar]. Keep the rest of the picture the same.` |
| Gaya belum seperti aset GELITA (terlalu foto, datar 2D, atau bertekstur kuas) | `Match the GELITA finish of the attached reference: premium stylized 3D, softly rounded dimensional forms, warm cinematic light. Not photographic, not flat 2D, no painted brush or paper texture. Keep the same composition and real proportions.` |
| Terlalu ramai | `Keep every listed landmark, but simplify the small background clutter so each area reads clearly.` |
| Resolusi kurang | `Give me the same image at the highest resolution available, square 1:1.` |

---

## 9. Pemeriksaan kualitas

### 9.1 Daftar periksa mata

- [ ] Berkas WebP, persegi **tepat 1200 × 1200 px**, tidak transparan, nama berkas = kode berkas
- [ ] Satu adegan utuh, bukan kolase, tanpa garis kisi
- [ ] Tidak ada latar putih, polos, transparan, atau studio; keempat sudut berisi
- [ ] Siang atau pagi keemasan; tidak ada keping yang hampir hitam
- [ ] Setiap keping punya penanda unik (cocokkan dengan daftar `Puzzle layout` di prompt)
- [ ] Gambar diperkecil 25%: setiap keping masih bisa ditunjuk
- [ ] Tidak ada blur, bokeh, atau vinyet
- [ ] Tidak ada teks, angka, logo, watermark, Jaka, atau Mbah Kedu
- [ ] Gaya selaras dengan aset GELITA (3D stilisasi, bukan foto, bukan lukisan 2D)
- [ ] Gambar memperlihatkan apa yang disebut keterangan (`prompt_id`)
- [ ] Detail budaya akurat (dibandingkan dengan foto referensi)
- [ ] Aman dan sopan: tidak ada perilaku berbahaya, pakaian sopan, agama dihormati
- [ ] Lolos `cek_keping.py` (tidak ada GAGAL; semua PERIKSA sudah dinilai)
- [ ] Dua orang berhasil menyusunnya tanpa kebingungan

### 9.2 Skrip `cek_keping.py`

Skrip ini memotong gambar sesuai kisi, lalu mencari:

- keping **POLOS** (hampir tanpa detail);
- keping **KEMBAR** (bisa tertukar);
- keping yang **mirip** atau berpola sama;
- keping yang hanya berbeda warna, berdasarkan simulasi buta warna merah-hijau (deuteranopia, Machado dkk., 2009).

Skrip membaca JPG, PNG, dan WebP, lalu menghasilkan dua gambar bantu: `_kisi.png`, berisi nomor keping dengan tanda merah untuk GAGAL dan kuning untuk PERIKSA, serta `_acak.png`, berisi keping yang diacak untuk uji coba manusia.

```bash
pip install pillow numpy
python3 cek_keping.py challenge.item.tmg-1-d01.webp --kisi 3
python3 cek_keping.py folder_magelang/ --kisi 4 --keluaran hasil_magelang
```

Salin kode berikut ke berkas bernama `cek_keping.py`:

```python
#!/usr/bin/env python3
"""
cek_keping.py — pemeriksa gambar Susun Gambar GELITA.

Memotong gambar menjadi kisi N x N (3 = Temanggung, 4 = Magelang/Wonosobo),
lalu mencari dua masalah yang membuat puzzle ambigu:
  1. keping POLOS  : keping yang hampir tidak punya detail (langit/dinding/latar polos)
  2. keping KEMBAR : dua keping yang tampak sama sehingga bisa tertukar
Juga memberi peringatan bila dua keping hanya berbeda warna (sulit bagi
siswa buta warna) dan bila ukuran gambar belum memenuhi syarat.

Hasil: laporan di layar + dua gambar bantu
  <nama>_kisi.png  : gambar dengan garis kisi, nomor keping, dan tanda merah/kuning
  <nama>_acak.png  : keping diacak, untuk uji coba menyusun di layar/kertas

Pakai : python3 cek_keping.py gambar.jpg --kisi 3
        python3 cek_keping.py folder_gambar/ --kisi 4
Butuh : Python 3.9+, Pillow, numpy  (pip install pillow numpy)
"""
import argparse
import itertools
import os
import random
import sys

import numpy as np
from PIL import Image, ImageDraw, ImageFont, ImageOps

UKURAN_KERJA = 1200          # gambar besar diperkecil ke 1200 x 1200 sebelum dianalisis
MIN_SISI = 1200              # syarat bank soal: persegi, minimal 1200 px

# Ambang hasil kalibrasi (lihat panduan). Ubah hanya bila perlu.
POLOS_FRAKSI = 0.70          # >70 % luas keping berupa blok rata  -> POLOS (gagal)
LUAS_FRAKSI = 0.45           # 45-70 % luas keping rata            -> AREA POLOS LUAS (periksa)
POLOS_STD = 12.0             # simpangan baku luminans < 12 ...
POLOS_TEPI = 0.04            # ... dan kepadatan tepi < 4 %       -> POLOS (gagal)
KEMBAR_PIKSEL = 0.055        # beda piksel rata-rata < 5,5 %       -> KEMBAR
MIRIP_PIKSEL = 0.085         # beda piksel rata-rata < 8,5 %       -> MIRIP
POLA_HIST = 0.84             # histogram warna hampir sama ...
POLA_ARAH = 0.93             # ... dan arah tekstur hampir sama     -> POLA SAMA
WARNA_SIM = 0.055            # dalam simulasi buta warna merah-hijau nyaris sama ...
WARNA_RGB = 0.085            # ... padahal bagi mata normal beda    -> BEDA WARNA SAJA

# Simulasi deuteranopia (Machado, Oliveira & Fernandes 2009, keparahan 1.0), ruang RGB linear
DEUTAN = np.array([[0.367322, 0.860646, -0.227968],
                   [0.280085, 0.672501, 0.047413],
                   [-0.011820, 0.042940, 0.968881]], dtype=np.float32)


def muat(path):
    im = Image.open(path)
    im = ImageOps.exif_transpose(im).convert("RGB")
    return im


def potong_persegi_tengah(im):
    w, h = im.size
    s = min(w, h)
    kiri, atas = (w - s) // 2, (h - s) // 2
    return im.crop((kiri, atas, kiri + s, atas + s))


def luminans(a):
    return 0.299 * a[..., 0] + 0.587 * a[..., 1] + 0.114 * a[..., 2]


def gradien(g):
    gx = np.zeros_like(g)
    gy = np.zeros_like(g)
    gx[:, 1:-1] = (g[:, 2:] - g[:, :-2]) / 2.0
    gy[1:-1, :] = (g[2:, :] - g[:-2, :]) / 2.0
    return gx, gy


def kecilkan(a, s=24):
    return np.asarray(
        Image.fromarray(np.clip(a, 0, 255).astype(np.uint8)).resize((s, s), Image.BILINEAR)
    ).astype(np.float32)


def simulasi_deutan(t):
    lin = (np.clip(t, 0, 255) / 255.0) ** 2.2
    sim = np.clip(lin @ DEUTAN.T, 0, 1) ** (1 / 2.2)
    return sim * 255.0


def ciri_keping(t, b=12):
    """Hitung ciri satu keping (array RGB float). b = sisi blok uji area rata (piksel)."""
    g = luminans(t)
    gx, gy = gradien(g)
    mag = np.hypot(gx, gy)
    # blok b x b: blok "rata" bila simpangan bakunya < 2.5
    h, w = g.shape
    gb = g[: h - h % b, : w - w % b].reshape(h // b, b, w // b, b)
    blok_std = gb.std(axis=(1, 3))
    fraksi_rata = float((blok_std < 2.5).mean())
    # histogram warna 4x4x4
    q = (np.clip(t, 0, 255) // 64).astype(int)
    idx = q[..., 0] * 16 + q[..., 1] * 4 + q[..., 2]
    hist = np.bincount(idx.ravel(), minlength=64).astype(np.float64)
    hist /= hist.sum()
    # histogram arah tepi (8 arah, berbobot kekuatan tepi)
    sudut = (np.arctan2(gy, gx) % np.pi) / np.pi * 8
    arah = np.bincount(np.minimum(sudut.astype(int), 7).ravel(), weights=mag.ravel(), minlength=8)
    arah = arah / arah.sum() if arah.sum() > 0 else np.full(8, 1 / 8)
    return {
        "std": float(g.std()),
        "tepi": float((mag > 12).mean()),
        "rata": fraksi_rata,
        "kecil_rgb": kecilkan(t),
        "kecil_sim": kecilkan(simulasi_deutan(t)),
        "hist": hist,
        "arah": arah,
        "rerata_tepi": float(mag.mean()),
    }


def periksa(path, n, outdir=None, seed=7):
    im0 = muat(path)
    w0, h0 = im0.size
    catatan = []
    if w0 != h0:
        catatan.append(f"Gambar tidak persegi ({w0}x{h0}); sistem akan memotong bagian tengah. Potong sendiri agar komposisi tidak rusak.")
    if min(w0, h0) < MIN_SISI:
        catatan.append(f"Sisi terpendek {min(w0, h0)} px < {MIN_SISI} px. Perbesar (upscale) dulu sebelum diunggah.")
    sq = potong_persegi_tengah(im0)
    kerja = min(sq.size[0], UKURAN_KERJA)          # gambar kecil tidak diperbesar (agar tidak kabur)
    kerja -= kerja % n
    im = sq.resize((kerja, kerja), Image.LANCZOS)
    a = np.asarray(im).astype(np.float32)
    s = kerja // n
    blok = max(4, round(kerja / 100))
    keping = [a[r * s:(r + 1) * s, c * s:(c + 1) * s] for r in range(n) for c in range(n)]
    ciri = [ciri_keping(t, blok) for t in keping]

    polos, luas = [], []
    for i, c in enumerate(ciri):
        if c["rata"] > POLOS_FRAKSI or (c["std"] < POLOS_STD and c["tepi"] < POLOS_TEPI):
            polos.append(i)
        elif c["rata"] > LUAS_FRAKSI:
            luas.append(i)

    kembar, mirip, pola, warna = [], [], [], []
    for i, j in itertools.combinations(range(n * n), 2):
        ci, cj = ciri[i], ciri[j]
        d_rgb = float(np.abs(ci["kecil_rgb"] - cj["kecil_rgb"]).mean() / 255)
        d_sim = float(np.abs(ci["kecil_sim"] - cj["kecil_sim"]).mean() / 255)
        hist_sama = float(np.minimum(ci["hist"], cj["hist"]).sum())
        arah_sama = float(1 - np.abs(ci["arah"] - cj["arah"]).sum() / 2)
        tepi_rasio = min(ci["rerata_tepi"], cj["rerata_tepi"]) / max(ci["rerata_tepi"], cj["rerata_tepi"], 1e-6)
        if d_rgb < KEMBAR_PIKSEL:
            kembar.append((i, j, d_rgb))
        elif d_rgb < MIRIP_PIKSEL:
            mirip.append((i, j, d_rgb))
        elif hist_sama > POLA_HIST and arah_sama > POLA_ARAH and tepi_rasio > 0.8:
            pola.append((i, j, hist_sama))
        if d_sim < WARNA_SIM and d_rgb >= WARNA_RGB:
            warna.append((i, j, d_sim))

    nama = lambda k: f"K{k + 1}(b{k // n + 1},k{k % n + 1})"
    lulus = not polos and not kembar
    print("=" * 70)
    print(f"{os.path.basename(path)}  |  kisi {n}x{n}  |  ukuran asli {w0}x{h0}")
    for t in catatan:
        print("  ! " + t)
    print("  Keping (std = keragaman terang-gelap, tepi = kepadatan garis, rata = porsi area polos):")
    for r in range(n):
        baris = []
        for c in range(n):
            k = r * n + c
            tanda = " POLOS" if k in polos else (" LUAS" if k in luas else "")
            baris.append(f"K{k + 1:>2} std{ciri[k]['std']:5.1f} tepi{ciri[k]['tepi'] * 100:4.0f}% rata{ciri[k]['rata'] * 100:4.0f}%{tanda}")
        print("   " + " | ".join(baris))
    if polos:
        print(f"  [GAGAL] Keping polos: {', '.join(nama(k) for k in polos)}")
    if luas:
        print(f"  [PERIKSA] Area polos luas (pastikan ada penanda unik): {', '.join(nama(k) for k in luas)}")
    if kembar:
        print("  [GAGAL] Keping kembar (bisa tertukar): " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in kembar))
    if mirip:
        print("  [PERIKSA] Keping mirip: " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in mirip))
    if pola:
        print("  [PERIKSA] Pola/tekstur sama: " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in pola))
    if warna:
        print("  [PERIKSA] Beda hanya warna (sulit bagi siswa buta warna): " + ", ".join(f"{nama(i)}~{nama(j)}" for i, j, _ in warna))
    putusan = "LULUS" if lulus and not (mirip or pola or warna or luas) else ("LULUS DENGAN CATATAN" if lulus else "PERLU DIPERBAIKI")
    if catatan and putusan == "LULUS":
        putusan = "LULUS (perbaiki ukuran)"
    print(f"  Putusan: {putusan}")

    if outdir is not None:
        os.makedirs(outdir, exist_ok=True)
        dasar = os.path.splitext(os.path.basename(path))[0]
        kisi = im.copy().convert("RGBA")
        lapis = Image.new("RGBA", kisi.size, (0, 0, 0, 0))
        dr = ImageDraw.Draw(lapis)
        try:
            huruf = ImageFont.load_default(size=max(14, s // 9))
        except TypeError:  # Pillow lama
            huruf = ImageFont.load_default()
        bermasalah_kuning = set(luas) | {k for p in (mirip + pola + warna) for k in p[:2]}
        bermasalah_merah = set(polos) | {k for p in kembar for k in p[:2]}
        for k in range(n * n):
            r, c = divmod(k, n)
            kotak = (c * s, r * s, (c + 1) * s - 1, (r + 1) * s - 1)
            if k in bermasalah_merah:
                dr.rectangle(kotak, fill=(220, 30, 30, 90))
            elif k in bermasalah_kuning:
                dr.rectangle(kotak, fill=(240, 200, 0, 70))
            dr.rectangle(kotak, outline=(255, 255, 255, 230), width=3)
            label = f"K{k + 1}"
            kotak_teks = dr.textbbox((0, 0), label, font=huruf)
            lebar, tinggi = kotak_teks[2] - kotak_teks[0], kotak_teks[3] - kotak_teks[1]
            dr.rectangle((c * s + 6, r * s + 6, c * s + 18 + lebar, r * s + 18 + tinggi), fill=(0, 0, 0, 160))
            dr.text((c * s + 12, r * s + 10), label, fill=(255, 255, 255, 255), font=huruf)
        Image.alpha_composite(kisi, lapis).convert("RGB").save(os.path.join(outdir, f"{dasar}_kisi.png"))
        urutan = list(range(n * n))
        random.Random(seed).shuffle(urutan)
        acak = Image.new("RGB", (kerja + (n - 1) * 8, kerja + (n - 1) * 8), (40, 40, 40))
        for pos, k in enumerate(urutan):
            r, c = divmod(k, n)
            pr, pc = divmod(pos, n)
            acak.paste(im.crop((c * s, r * s, (c + 1) * s, (r + 1) * s)), (pc * (s + 8), pr * (s + 8)))
        acak.save(os.path.join(outdir, f"{dasar}_acak.png"))
    return {"putusan": putusan, "polos": polos, "luas": luas, "kembar": kembar, "mirip": mirip, "pola": pola, "warna": warna}


def main():
    ap = argparse.ArgumentParser(description="Pemeriksa keping Susun Gambar GELITA")
    ap.add_argument("sumber", help="berkas gambar atau folder berisi gambar")
    ap.add_argument("--kisi", type=int, choices=[3, 4], required=True, help="3 = Temanggung, 4 = Magelang/Wonosobo")
    ap.add_argument("--keluaran", default="hasil_cek", help="folder untuk gambar bantu (bawaan: hasil_cek)")
    arg = ap.parse_args()
    if os.path.isdir(arg.sumber):
        daftar = sorted(
            os.path.join(arg.sumber, f) for f in os.listdir(arg.sumber)
            if f.lower().endswith((".jpg", ".jpeg", ".png", ".webp"))
        )
    else:
        daftar = [arg.sumber]
    if not daftar:
        sys.exit("Tidak ada gambar yang ditemukan.")
    hasil = [periksa(p, arg.kisi, arg.keluaran) for p in daftar]
    gagal = sum(1 for h in hasil if h["putusan"] == "PERLU DIPERBAIKI")
    print("=" * 70)
    print(f"Selesai: {len(hasil)} gambar, {gagal} perlu diperbaiki. Gambar bantu ada di folder '{arg.keluaran}'.")


if __name__ == "__main__":
    main()
```

### 9.3 Hasil uji skrip

Skrip sudah dicoba pada foto contoh, gambar sintetis, dan sembilan gambar katalog (bagian 6). Hasilnya sesuai dengan masalah yang dibahas di bagian 2:

| Gambar uji | Kisi 3 × 3 | Kisi 4 × 4 | Arti |
|---|---|---|---|
| Benda di latar putih polos | GAGAL: 9 keping polos, puluhan pasangan kembar | GAGAL: 16 keping polos | Kasus "tanpa latar" |
| Langit biru polos di atas bukit | GAGAL: 3 keping polos, 6 pasangan kembar | GAGAL: 8 keping polos | Kasus "langit cerah" |
| Pagoda dengan langit biru (foto) | GAGAL: 3 keping polos | GAGAL: 6 polos dan 5 pasangan kembar | Langit kosong di atas bangunan |
| Bunga close-up dengan latar kabur (foto) | GAGAL: 4 keping polos | GAGAL: 9 keping polos | Kasus "bokeh" |
| Secangkir kopi tampak atas di meja (foto) | PERIKSA: 4 area polos luas | GAGAL: 1 polos dan 6 area polos luas | Kasus "makanan tampak atas" |
| Tekstur dinding bata | GAGAL: pasangan kembar, banyak keping mirip | GAGAL | Kasus pola berulang |
| Dua keping hanya beda merah dan hijau | PERIKSA: beda hanya warna | PERIKSA | Uji simulasi buta warna |
| Adegan kaya detail di seluruh bidang | **LULUS** | LULUS dengan catatan | Target yang diinginkan |
| Katalog `tmg-1-03` (candi di latar putih) | GAGAL: K1, K3 polos dan kembar | — | Kasus "tanpa latar" pada aset nyata |
| Katalog `mgl-1-01` (stupa, malam) | — | GAGAL: K13 polos, 3 pasangan kembar | Kasus "langit malam" pada aset nyata |

Ambang batas skrip ada di bagian atas kode. Bila tim menemukan temuan yang keliru pada gambar nyata, sesuaikan ambangnya sedikit demi sedikit dan catat perubahannya.

### 9.4 Uji coba oleh manusia

Ambil `<nama>_acak.png`. Minta dua orang menyusunnya di layar atau di kertas yang digunting. Catat waktunya dan keping yang membuat mereka ragu. Bila dua orang ragu pada keping yang sama, perbaiki area itu meskipun skrip menyatakan lulus.

---

## 10. Lembar catatan produksi

Salin tabel ini ke spreadsheet tim. Catatan ini memenuhi permintaan DAFTAR_MEDIA untuk mencatat pemilik atau sumber gambar, sekaligus menjadi jejak penelitian.

| Kode puzzle | Kode berkas | Versi | Tanggal | Alat dan mode (mis. ChatGPT Images, Thinking) | Prompt (kode panduan dan ubahan) | Acuan gaya dan foto fakta (lisensi) | Prompt perbaikan yang dipakai | Hasil `cek_keping.py` | Uji manusia | Disetujui oleh |
|---|---|---|---|---|---|---|---|---|---|---|
| TMG-P02 | `challenge.item.tmg-1-03` | v2 (ganti katalog) | | | | | | | | |
| TMG-P04 | `challenge.item.tmg-1-d01` | v1 | | | | | | | | |

---

## 11. Sepuluh variasi per wilayah di bank soal

Sistem Susun Gambar dapat menerima lebih dari tiga variasi. Bank soal revisi sudah memakai sepuluh variasi per wilayah:

| Puzzle | Variasi | `item_key` | Kode berkas gambar | `sequence` |
|---|---|---|---|---|
| P01–P03 | a, b, c | `{tantangan}-a01` … `-c01` | kode lama (`tmg-1-01`, `-03`, `-04`; `mgl-1-01`, `-02`, `-03`; `wnb-1-05`, `-06`, `-07`) | 1–3 |
| P04–P10 | d sampai j | `{tantangan}-d01` … `-j01` | `challenge.item.{tantangan}-d01` … `-j01` | 4–10 |

- **Pola kode baru** sama dengan kode soal lain: kode tantangan + variasi + nomor dua angka (`tmg-1-d01`). Kode berkas gambarnya memakai `item_key` yang sama, seperti pola `challenge.item.mgl-4-a01` di Cari Objek. Kode lama variasi a–c tidak diubah karena gambarnya sudah terunggah.
- Sheet `variants` sudah berisi judul ID/EN untuk variasi d–j, dan daftar pilihan kolom variasi di sheet `variants` dan `items` sudah diperluas menjadi `a` sampai `j`.
- Keterangan variasi d–j berstatus `needs_verification`. Sebagian besar faktanya diambil dari bank soal; fakta baru ditandai "Catatan fakta" di kartu puzzle dan di kolom `review_note`.
- **Untuk analisis data:** setiap pengerjaan mendapat satu dari sepuluh gambar secara acak, dan tingkat kesulitan gambar bisa berbeda. Catat variasi yang didapat siswa dan perlakukan sebagai faktor dalam analisis. Bila tim ingin membatasi ragam selama satu periode pengambilan data, istirahatkan sebagian variasi dengan `is_active = 0` tanpa menghapus barisnya.
- Validasi impor yang masih mewajibkan tepat tiga variasi perlu disesuaikan di sisi sistem (dicatat di catatan revisi).

---

## 12. Catatan untuk gambar Cari Objek

Aturan "latar penuh" **khusus berlaku untuk Susun Gambar**. Gambar benda Cari Objek justru **wajib PNG berlatar transparan, 240 × 240 px**, karena akan ditempel ke gambar adegan berukuran 1280 × 720 px. Untuk benda-benda itu:

- beri bayangan kontak yang lembut di bawah benda agar tampak diletakkan, bukan melayang;
- buat siluet yang jelas berbeda untuk pasangan yang memang sengaja mirip: kuluk Topeng Ireng dan dadak merak, carica dan pepaya biasa, keranjang ongklok dan rigen, bundengan dan angklung atau sasando;
- uji pada ukuran sebenarnya (240 px) di atas gambar adegan.

---

## Sumber

- Katalog aset GELITA: `data.json` (arahan aktif 2026-10-11, status dan prompt tiap gambar), `assets/tantangan/`, dan `assets/referensi/` — repositori `akubapakmu/gelita-katalog`
- [ChatGPT Images 2.0 (gpt-image-2): resolusi 2K dan mode Thinking — NYU Shanghai RITS](https://rits.shanghai.nyu.edu/ai/openai-launches-chatgpt-images-2-0-with-2k-output-and-reasoning-mode/)
- [Spesifikasi GPT Image 2 — Neurohive](https://neurohive.io/en/news/chatgpt-images-2-0-openai-launches-image-generation-model-with-reasoning-2k-resolution-and-multilingual-text/)
- [Borobudur: dua pasang gunung kembar, dua sungai, arah membaca relief — Wikipedia](https://en.wikipedia.org/wiki/Borobudur)
- [Borobudur Temple Compounds — UNESCO](https://whc.unesco.org/en/list/592)
- [Pasar Papringan, Temanggung — GNFI](https://www.goodnewsfromindonesia.id/2022/12/14/mengunjungi-pasar-papringan-temanggung-yang-kental-akan-nuansa-jawa-kuno-dan-ramah-ling)
- [Bundengan, alat musik dari pelindung penggembala — GNFI](https://www.goodnewsfromindonesia.id/2019/10/14/bundenga-alat-musik-tradisional-yang-berawal-dari-pelindung-gembala)
- [Tari Topeng Lengger — Visit Jawa Tengah](https://visitjawatengah.jatengprov.go.id/en/art-and-culture/tari-topeng-lengger)
- [Umbul Jumprit — Ensiklopedia](https://p2k.stekom.ac.id/ensiklopedia/Umbul_Jumprit)
- [Situs Liyangan — Tirto](https://tirto.id/kisah-beras-kuno-berusia-1000-tahun-di-situs-liyangan-hvim)
- [Embun beku di Kompleks Candi Arjuna (Dieng Kulon, Banjarnegara) — Antara](https://www.antaranews.com/berita/4196871/embun-beku-kembali-selimuti-kompleks-candi-arjuna-dieng)
- [Telaga Warna — Wikipedia bahasa Indonesia](https://id.wikipedia.org/wiki/Telaga_Warna_(Dieng))
