# Rencana Tampilan Skenario Digital dan HUD Seragam

Per 19 November 2026 · `disetujui pengembang` (mockup disetujui 19 November 2026) · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Keputusan di [04](04_CATATAN_KEPUTUSAN.md) (18 November 2026)

Pengembang membuat mockup baru layar tantangan Skenario Digital (langkah 1 *Tindakan*) dan menyertakan tangkapan layar yang sekarang (langkah 2 *Alasan*). Dokumen ini adalah rencana dari diskusi dan wawancara dua putaran hari itu: apa yang berubah di layar Skenario Digital, apa yang berubah di HUD semua layar, gambar yang dipesan, urutan kerja, dan aturan penelitian yang tidak boleh ikut berubah. Belum ada kode yang diubah. Pada 19 November 2026 pengembang menyetujui mockup tanpa catatan (langkah A), lalu di hari yang sama memberi dua putaran catatan perbaikan (bagian 1, keputusan 17–25) yang sudah masuk ke mockup dan ke prompt gambar `SD-` di bagian 8.

Rujukan yang tetap berlaku: [32 §6](32_IMPLEMENTASI_SKENARIO_DIGITAL.md#6-engine-skenario-di-peramban) (keadaan layar, umpan balik, waktu), [32 §8.2](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) (narasi instruksi hanya lewat ▶, K10), [33 §7.1](33_IMPLEMENTASI_MODE_PENELITIAN.md) (tanpa jam di mode penelitian), [18](18_MOCKUP_LAYAR_BARU.md) (tanpa merah, tanpa kata "salah"/"gagal"), dan arah tampilan pengembang (11 Oktober dan 15 November 2026: satu tampilan emas berukir, tidak ada bagian yang ditarik ke ukuran yang bukan ukuran gambarnya, ukuran untuk siswa kelas 5 di Chromebook).

## 1. Keputusan pengembang (18 November 2026)

**Layar Skenario Digital**

1. **Dua panel tanpa bingkai.** Bingkai, latar tambahan, dan garis tepi kedua panel dihapus supaya lebih lega; yang tersisa hanya latar utama wilayah, yang selalu gelap sehingga teks tetap terbaca. Tidak ada hiasan batik di sudut panel, dan latar utama tidak diganti.
2. **Gambar situasi tidak dipotong.** Gambar tampil utuh dengan perbandingan aslinya (3 : 2, 1536 × 1024), tanpa bingkai.
3. **Pita instruksi dihapus.** Teks instruksi tantangan menjadi baris kecil di bawah pertanyaan besar ("Apa yang akan kamu lakukan?"), dengan tombol bundar ▶ kecil di sampingnya. Narasi instruksi tetap hanya diputar lewat ▶ (32 §8.2, K10). *Diubah keputusan 17.*
4. **Baris ⓘ ("Kamu bisa memilih alasan setelah ini.") dan judul kecil "TENTUKAN PILIHANMU" tidak dipakai.** Ruangnya dipakai untuk pilihan jawaban.
5. **Tombol utama pindah ke dalam panel kanan**, di bawah pilihan. Bilah tombol di bawah layar dihapus.
6. **Pemutar *Dengarkan cerita*:** label "Dengarkan cerita", gelombang suara yang **bergerak hanya selama cerita dibacakan** (diam saat berhenti, supaya siswa tahu apakah suara sedang diputar), dan durasi dari berkas suaranya. Cerita yang belum punya narasi tidak menampilkan pemutar, dan panel kiri tetap rapi tanpanya. *Letaknya diubah keputusan 21.*
7. **Penanda cerita:** "Cerita 1 dari 4" dengan bilah kemajuan per cerita dan bulatan *Kesempatan* di baris yang sama, di atas panel kiri (*sejak keputusan 24: di dalam gambar, bagian atas*). Bilah menandai posisi dan cerita yang sudah ditutup saja, tidak pernah hasilnya (tanpa warna aman/berisiko).
8. **Subjudul kepala tetap judul varian.** Judul varian sudah memuat nama wilayah (misalnya *Skenario Digital Temanggung*), jadi nama wilayah di tengah atas dihapus. *Diubah keputusan 19.*
9. **✗ disembunyikan di Skenario Digital**; ✓ (jumlah cerita yang ditutup dengan tindakan aman) tetap. Empat tantangan lain tetap menampilkan ✓ dan ✗.
10. **Kepala layar tetap rapi tanpa jam.** Di mode penelitian jam tidak tampil (33 §7.1), dan kapsul ✓ tidak boleh menyisakan celah.
11. **Pelat khusus Skenario Digital dipesan ke alat pembuat gambar** pada ukuran tampilnya (bagian 5), bukan pelat umum `U-01`/`U-02` dari [40](40_PROMPT_GAMBAR_ANTARMUKA.md). Tidak ada tombol atau kartu yang ditarik.
12. **Mockup yang bisa diklik dulu.** Sebelum pesanan gambar dan kode, pengembang memeriksa satu halaman HTML 16 : 9 berisi keempat keadaan (Tindakan, Alasan, umpan balik, penjelasan tindakan aman) dengan teks asli dari bank soal.

**HUD semua layar**

13. **Kepala layar satu baris di kelima tantangan**, seperti mockup: logo · nomor tantangan · judul dan subjudul varian · jam · ✓/✗ · suara · Keluar. Pita HUD dan kepala tantangan yang sekarang terpisah digabung (sekitar 60 dp lebih lega untuk isi).
14. **Tombol menu (☰) disembunyikan di kelima tantangan.** Keluar tetap satu-satunya jalan keluar dari tantangan. Akibatnya sakelar musik latar (ada di menu) tidak bisa dicapai selama tantangan; tombol suara tetap ada. Musik latar hanya hiasan, bukan bagian penelitian.
15. **Seragam = tombol yang sama di sudut yang sama.** Tombol bundar HUD satu ukuran di semua layar, selalu di baris kanan atas dengan urutan yang sama, dan logo selalu berukuran dan berletak sama. Tinggi pita HUD boleh berbeda bila halamannya membutuhkan (Peta Kedu, layar hasil).
16. **Aturan tampil tombol HUD:**

| Tombol | Tampil di |
| --- | --- |
| Suara | Semua layar |
| Menu | Semua layar kecuali kelima tantangan |
| Bahasa | Sebelum masuk dan di luar tantangan (seperti sekarang) |
| Kembali / Keluar | Bila halaman punya; selalu tombol terakhir di kanan |

**Catatan pengembang atas mockup (19 November 2026)**

17. **Petunjuk punya baris pemutar sendiri.** Di langkah 1, tepat di bawah "Apa yang akan kamu lakukan?", ada pemutar *Dengarkan petunjuk* yang sama bentuknya dengan *Dengarkan cerita* (▶, label, gelombang yang bergerak hanya selama dibacakan, durasi), selebar panel kanan. Teks instruksi ada di bawahnya, **rata kiri**, dan kini muat satu baris. Bila instruksi belum punya rekaman, pemutarnya tidak tampil dan teksnya tetap ada. Menggantikan ▶ kecil di samping teks (keputusan 3); narasi tetap hanya lewat ▶ (K10).
18. **Tombol Keluar sementara tanpa pelat gambar:** tombol bundar `round-logout` dengan tulisan "Keluar" di sampingnya. Pelat untuk Keluar ditentukan lagi saat HUD seragam (langkah C).
19. **Subjudul Skenario Digital = nama kota/kabupaten saja** (*Temanggung*, *Magelang*, *Wonosobo*), di bawah judul *Skenario Digital*. Menggantikan keputusan 8 untuk Skenario Digital.
20. **Pita kepala layar digelapkan:** gradasi gelap di belakang kepala layar (dari atas sampai sedikit di bawahnya, sekitar 128 dp) supaya HUD terbaca di latar apa pun dan bagian atas latar tampak lebih gelap. Ikut kepala satu baris (keputusan 13) di kelima tantangan.
21. **Pemutar *Dengarkan cerita* menempel tepat di bawah gambar**, selebar gambar, sebagai satu blok (sudut atas gambar dan sudut bawah pemutar membulat). Gambar dilebarkan dari 456 × 304 menjadi **540 × 360** untuk semua cerita; situasi terpanjang (250 karakter, 19 px) tetap muat, tepat sampai tepi bawah panel kiri. *Diganti keputusan 24.*

**Catatan pengembang putaran kedua (19 November 2026)**

22. **Nama kota di kanan judul, satu baris:** *Skenario Digital │ Temanggung* (nama kota lebih kecil, dipisah garis tipis emas); baris subjudul hilang, kepala layar lebih ramping. Menggantikan keputusan 19.
23. **Keluar hanya tombol bundar `round-logout`**, tanpa tulisan; keterangan "Keluar" muncul saat disorot, difokus, atau ditekan lama, seperti tombol ikon lain (04, keterangan tombol ikon). Menggantikan keputusan 18.
24. **Penanda cerita dan pemutar di dalam gambar.** Gambar selebar panel kiri, **600 × 400**. Di dalamnya, di bagian atas: *Cerita 1 dari 4*, bilah kemajuan, dan bulatan *Kesempatan* di atas gradasi gelap; di bagian bawah: pemutar *Dengarkan cerita* sebagai kapsul 576 × 52 dengan jarak 12 dp dari tepi gambar. Situasi di bawah gambar; situasi terpanjang kini menyisakan sekitar 60 dp. Menggantikan keputusan 7 (letak) dan 21. Akibatnya pita atas dan bawah gambar situasi tertutup; isi penting gambar ada di tengah.
25. **Pemutar dan teks petunjuk dalam satu kotak, satu baris masing-masing:** baris atas ▶ *Dengarkan petunjuk*, gelombang, durasi; garis tipis; baris bawah teks instruksi rata kiri. Kotak 584 × 110 (teks Indonesia dan Inggris sama-sama satu baris). Bila instruksi belum punya rekaman, kotak tidak tampil dan teks berdiri sendiri, rata kiri. Melengkapi keputusan 17.

## 2. Yang tidak berubah (aturan penelitian)

- Kunci jawaban, skor, kode kesalahan, dan tindakan aman tidak pernah ada di halaman sebelum server menjawab (32 §5.2).
- Satu percobaan = satu tindakan + satu alasan, dikirim bersama. Alasan ditanyakan apa pun tindakannya. Kalimat tanya alasan "Yang paling penting kupikirkan di sini adalah …" adalah teks instrumen dan tidak diubah tanpa peneliti.
- Pilihan yang dipilih ditandai bentuk (●) dan cahaya, tidak hanya warna. Umpan balik tanpa merah dan tanpa kata "salah"/"gagal".
- Situasi dan petunjuk hanya dibacakan bila ▶-nya diketuk; pilihan dan umpan balik tidak dibacakan. Hanya satu suara pada satu waktu: memutar yang satu menghentikan yang lain, dan memilih, pindah langkah, atau pindah cerita menghentikan suara.
- Keenam waktu (`ready_ms`, `reason_ready_ms`, `first_input_ms`, `action_last_input_ms`, `reason_last_input_ms`, `last_input_ms`), `selected()` per langkah, dan event `item_shown` tetap seperti 32 §6.4. Tombol utama yang pindah ke panel kanan tidak mengubah artinya.
- *Kembali* di langkah 2, *Coba lagi*, dan *Lanjut ke cerita berikutnya* tetap seperti 32 §6.2.

## 3. Isi layar per keadaan

Panggung 1280 × 720 dp. Angka di bawah adalah titik awal mockup; ukuran akhir diukur di mockup dengan teks terpanjang dari bank, lalu di Chromebook 1366 × 768 dan 1366 × 657.

| Bagian | Titik awal |
| --- | --- |
| Kepala satu baris | y 16, tinggi 72 |
| Panel kiri (situasi) | x 32, y 104, lebar 600, tinggi 584 |
| Panel kanan (langkah) | x 664, y 104, lebar 584, tinggi 584 |

**Ukuran teks dari bank (18 November 2026).** 24 skenario: situasi sampai 250 karakter (rata-rata 173); 3 atau 4 tindakan, sampai 59 karakter (rata-rata 45, bisa dua baris); 3, 4, atau 5 alasan, sampai 48 karakter (rata-rata 41). Panel kanan harus memuat lima kartu alasan ditambah baris *Pilihanmu*, dan empat kartu tindakan dua baris. Gambar 3 : 2 utuh 600 × 400, dengan penanda cerita di bagian atas dan pemutar di bagian bawah di dalam gambar (keputusan 24; semula 456 × 304, lalu 540 × 360); situasi panjang (lebih dari 200 karakter) memakai satu ukuran huruf lebih kecil (19 px, biasa 20 px). Di mockup 19 November 2026 situasi terpanjang berakhir sekitar 60 dp di atas tepi bawah panel kiri. Panel bergulir hanya sebagai cadangan terakhir.

| Keadaan | Panel kiri | Panel kanan |
| --- | --- | --- |
| `action` | Gambar dengan penanda cerita dan *Kesempatan* di bagian atas dan pemutar *Dengarkan cerita* di bagian bawah; situasi | Sakelar **1 Tindakan** · 2 Alasan; "Apa yang akan kamu lakukan?"; kotak *Dengarkan petunjuk* + teks instruksi rata kiri; kartu tindakan; tombol *Lanjut ke alasan* (belum aktif sampai siswa memilih) |
| `reason` | sama | Sakelar 1 Tindakan · **2 Alasan**; kartu kecil *Pilihanmu: …*; "Yang paling penting kupikirkan di sini adalah …"; kartu alasan; *Kembali* dan *Kirim jawaban* berdampingan |
| `feedback` | sama | Teks umpan balik dari bank; "Kamu masih punya {n} kesempatan …" bila belum tertutup; *Coba lagi* atau *Lanjut ke cerita berikutnya* |
| `explained` | sama | "Kesempatanmu untuk cerita ini sudah habis …"; "Ini tindakan yang aman"; tindakan aman; penjelasan; *Lanjut ke cerita berikutnya* |
| selesai | Modal hasil dari kerangka, lalu *Lanjut* | |

## 4. HUD seragam: pekerjaan

1. **Inventaris.** Untuk setiap layar permainan (sekitar 35 tampilan di `app/Views/game/`): tinggi pita (`hudStyle`: 120, 84, atau 56 dp), tata letak (`hudLayout`: kiri, `logo-centre`, `title-centre`, `region-centre`), dan tombol yang tampil (logo, judul/wilayah, Serpihan Cahaya, bahasa, suara, menu, Kembali). Diukur di Chromebook 1366 × 768 dan 1366 × 657 dengan peramban otomatis, sama seperti [40 → Ukuran yang diukur](40_PROMPT_GAMBAR_ANTARMUKA.md).
2. **Satu ukuran tombol bundar dan satu ukuran logo** untuk semua pita, satu jarak tepi kanan, satu urutan (bahasa · suara · menu · Kembali/Keluar).
3. **Aturan tampil** keputusan 16 diterapkan di `components/hud-menu.php` dari satu tempat, bukan per halaman, dan diuji per jenis layar.
4. **Tantangan:** `hudLayout` `region-centre` diganti kepala satu baris (keputusan 13); kerangka `_frame.php` dan `stage.css §Tantangan` diatur ulang untuk kelima engine. Koordinat isi engine lain ikut naik; masing-masing diperiksa ulang.

## 5. Gambar yang dipesan (baris `SD-` di `ai-studio/chatgpt-images/TASKS.md`)

Dipesan **sesudah mockup disetujui**, dengan ukuran tampil yang diukur di sana; kanvas dua sampai tiga kali ukuran tampil terbesar, gaya `GELITA CARVED GOLD UI — 2026-11-18` dan papan acuan `reference.27` ([40 → Aturan bersama](40_PROMPT_GAMBAR_ANTARMUKA.md#aturan-bersama)). Prompt siap tempel ada di [bagian 8](#8-prompt-gambar-sd-) (19 November 2026), dengan ukuran akhir dan dua gambar tambahan: `SD-10` (bagian aktif sakelar langkah, dipisah dari relnya supaya bisa bergeser) dan `SD-11` (kotak *Dengarkan petunjuk*, keputusan 17 dan 25). Daftar semula:

| Kode | Bagian | Catatan |
| --- | --- | --- |
| `SD-01` | Tombol emas utama, lebar pendek | *Lanjut ke alasan*, *Kirim jawaban*, *Coba lagi* |
| `SD-02` | Tombol emas utama, lebar panjang | *Lanjut ke cerita berikutnya* |
| `SD-03` | Tombol biru tua *Kembali* | Berdampingan dengan `SD-01` di langkah 2 |
| `SD-04`, `SD-05` | Kartu pilihan biru tua, satu baris dan dua baris | Keadaan biasa |
| `SD-06`, `SD-07` | Kartu pilihan dipilih, satu baris dan dua baris | Tepi emas menyala; ● tetap digambar permainan |
| `SD-08` | Sakelar langkah 1 · 2 | Rel biru tua; bagian aktif emas |
| `SD-09` | Kapsul pemutar *Dengarkan cerita* | Tombol ▶ memakai `round-play` yang ada; gelombang digambar permainan |

Tombol yang belum aktif memakai gambar yang sama dengan tampilan diredupkan (CSS), bukan gambar tersendiri. Penanda ○/● dan bilah kemajuan cerita digambar permainan dengan warna emas yang sama, kecuali pengembang meminta gambar tersendiri setelah melihat mockup.

## 6. Urutan kerja

| Langkah | Isi | Selesai bila |
| --- | --- | --- |
| A | Mockup HTML keempat keadaan (keputusan 12); dibuat 18 November 2026 dengan cerita `tmg-5-b01` dan cerita terpanjang `wnb-5-a04` (4 tindakan, 5 alasan): semua keadaan muat di 1366 × 768 tanpa panel bergulir. Sumbernya `tools/mockup/skenario-41/` (`python3 tools/mockup/skenario-41/build.py hasil.html`); berkas HTML-nya tidak disimpan di repo, tangkapan keempat keadaan di `gambar/41/` | **Selesai 19 November 2026:** disetujui pengembang, lalu diperbaiki dengan dua putaran catatannya (keputusan 17–25) |
| B | Prompt `SD-` (bagian 8) dan baris `TASKS.md` | **Selesai 19 November 2026:** baris `SD-01` sampai `SD-11` berstatus `todo` |
| C | HUD seragam (bagian 4), satu cabang, bersama pemasangan pelat emas, biru tua, dan hijau, bingkai jendela, dan bilah perunggu dari [40](40_PROMPT_GAMBAR_ANTARMUKA.md) (`U-01` sampai `U-05`, keputusan pengembang 18 November 2026), karena keduanya menata ulang semua layar | Inventaris sebelum/sesudah, uji per jenis layar, cek `tools/qa/stage-check.mjs` |
| D | Tampilan baru Skenario Digital di `skenario.php`, `engines/skenario.js`, `stage.css`; teks baru di berkas bahasa (id dan en) | Uji Skenario Digital hijau; keempat keadaan dicek di Chromebook dua ukuran; aturan bagian 2 utuh |
| E | Pasang gambar `SD-` yang diterima pengembang | Pelat sementara diganti tanpa mengubah tata letak |

Langkah D boleh mulai dengan pelat sementara sebelum gambar `SD-` datang, asal ukurannya sudah ukuran akhir.

## 7. Pertanyaan yang masih terbuka

- ~~Apakah ✓ di Skenario Digital tetap berbentuk angka saja (tanpa "/"), atau digabung dengan bilah kemajuan cerita?~~ **Angka saja**, seperti mockup; bilah *Cerita 1 dari 4* tetap terpisah dan hanya menandai posisi (pengembang, 19 November 2026).
- ~~Teks instruksi tantangan Skenario Digital di bank perlu dipendekkan agar satu baris?~~ **Tidak**: teks dan rekamannya tetap (pengembang, 19 November 2026). Sejak keputusan 17 dan 25 teksnya rata kiri di dalam kotak petunjuk dan muat satu baris.
- **`U-07` (bingkai Serpihan Cahaya) ditahan** sampai kristalnya (`U-36`, prompt di [40](40_PROMPT_GAMBAR_ANTARMUKA.md) sejak 19 November 2026) dibuat dan keduanya dipasang bersama. Bingkainya sudah di `ai-studio/chatgpt-images/out/hud-shards-frame.webp` (dari PR #420, yang ditutup). Bila HUD seragam (langkah C) mengubah ukuran penghitung Serpihan Cahaya, ukuran kristal tidak berubah: kanvasnya sama persis dengan lubang bingkai, jadi ikut skala bingkai.

## 8. Prompt gambar `SD-`

Ditambahkan 19 November 2026, sesudah mockup disetujui, dan disesuaikan hari itu juga dengan dua putaran catatan pengembang (keputusan 17–25: `SD-09` menjadi kapsul di dalam gambar, `SD-11` baru untuk kotak *Dengarkan petunjuk*). Ukuran tampil diukur di mockup (panggung 1280 × 720 dp) dengan semua teks tindakan dan alasan dari bank soal, dalam bahasa Indonesia dan Inggris: kartu satu baris 54 dp (87 dari 88 tindakan, semua alasan), kartu dua baris 67 dp (dibulatkan 68), dan setiap label tombol berbahasa Inggris lebih pendek dari label Indonesianya, jadi setiap tombol punya **satu ukuran tetap**. Kanvas tiga kali ukuran tampil. Tangkapan mockup yang disetujui ada di `gambar/41/` (`mockup-skenario-1-tindakan.webp` sampai `-4-tindakan-aman.webp`) dan dilampirkan supaya letak dan besar bagian terlihat.

Semua gambar `SD-` berukuran tetap: dipasang **tanpa ditarik** (`background-size: 100% 100%` pada ukuran tampilnya, bukan `border-image`), jadi hiasan boleh ada di mana pun yang disebut prompt, asal bagian tempat teks tetap tenang. Setiap prompt dimulai dengan blok `GELITA CARVED GOLD UI — 2026-11-18` dari [40 → Aturan bersama](40_PROMPT_GAMBAR_ANTARMUKA.md#aturan-bersama), kata per kata. Urutan: `SD-01` lebih dulu; `SD-02`, `SD-03` dari `SD-01`; `SD-04` sendiri; `SD-05` sampai `SD-07` dan `SD-09` dari `SD-04`; `SD-08` dari `SD-04`; `SD-10` dari `SD-01` dan `SD-08`; `SD-11` dari `SD-04`.

| Kode | Berkas di `public/assets/ui/` | Tampil (dp) | Kanvas | Dipakai untuk |
| --- | --- | --- | --- | --- |
| `SD-01` | `sd-button-gold.webp` | 312 × 58 | 936 × 174 | *Lanjut ke alasan*, *Kirim jawaban*, *Coba lagi* |
| `SD-02` | `sd-button-gold-long.webp` | 420 × 58 | 1260 × 174 | *Lanjut ke cerita berikutnya* |
| `SD-03` | `sd-button-navy.webp` | 236 × 58 | 708 × 174 | *Kembali* di langkah 2 |
| `SD-04` | `sd-card.webp` | 584 × 54 | 1752 × 162 | Kartu tindakan/alasan, satu baris |
| `SD-05` | `sd-card-2.webp` | 584 × 68 | 1752 × 204 | Kartu dua baris |
| `SD-06` | `sd-card-on.webp` | 584 × 54 | 1752 × 162 | Kartu satu baris yang dipilih |
| `SD-07` | `sd-card-on-2.webp` | 584 × 68 | 1752 × 204 | Kartu dua baris yang dipilih |
| `SD-08` | `sd-step-rail.webp` | 256 × 44 | 768 × 132 | Rel sakelar *1 Tindakan · 2 Alasan* |
| `SD-09` | `sd-player.webp` | 576 × 52 | 1728 × 156 | Kapsul pemutar *Dengarkan cerita* di dalam gambar situasi (600 × 400), bagian bawah |
| `SD-10` | `sd-step-on.webp` | 125 × 38 | 375 × 114 | Bagian aktif sakelar, di atas rel (kiri atau kanan) |
| `SD-11` | `sd-hint-box.webp` | 584 × 110 | 1752 × 330 | Kotak petunjuk: pemutar *Dengarkan petunjuk* dan teks instruksi |

Pemasangan (langkah E): berkas dipindahkan dari `ai-studio/chatgpt-images/out/` ke `public/assets/ui/` dan dipanggil lewat `asset_url_versioned('ui/…')`; tombol yang belum aktif memakai gambar yang sama dengan `filter` CSS, dan cahaya lembut di sekitar kartu yang dipilih tetap dari CSS (`box-shadow`), seperti di mockup. Teks, ○/●, gelombang suara, ▶ (`round-play`), dan durasi tetap digambar permainan.

### SD-01 · Tombol emas Skenario Digital

| | |
|---|---|
| Berkas | baru: `sd-button-gold.webp` |
| Kanvas | 936 × 174, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/ui-plate-gold.webp` (`U-01`: emas dan ujung yang sama), `docs/revisi-10-analitik/gambar/41/mockup-skenario-2-alasan.webp` (letak dan besar tombol, kanan bawah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-button-gold (SD-01 · main gold button, fixed size)
A short horizontal button plate with a warm polished GOLD face and a thin raised carved gold border, in the same gold, bevel and end caps as the attached gold plate (U-01). Dark brown text such as "Kirim jawaban ›" is written on it later by the game, centred.
Canvas exactly 936 × 174 pixels, transparent outside the plate; the plate fills the canvas with an even 6-pixel margin. It is shown at exactly 312 × 58, never stretched, so draw it at exactly this shape.
Each end carries one compact carved end cap (a rounded pointed cap with one scroll curl and one small diamond stud), each no wider than 120 pixels. Between the end caps the face is calm and even, with no ornament, gem or strong reflection, so that text stays readable. WebP, named sd-button-gold.webp.
```

### SD-02 · Tombol emas panjang

| | |
|---|---|
| Berkas | baru: `sd-button-gold-long.webp` |
| Kanvas | 1260 × 174, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-button-gold.webp` (`SD-01`), `docs/revisi-10-analitik/gambar/41/mockup-skenario-4-tindakan-aman.webp` (letak dan besar tombol) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-button-gold-long (SD-02 · long gold button, fixed size)
Exactly the same button as the attached gold button (SD-01): the same gold face, border, bevel and end caps at the same size. Only the calm face between the end caps is longer, for a longer label ("Lanjut ke cerita berikutnya ›") written later by the game.
Canvas exactly 1260 × 174 pixels, transparent outside the plate; even 6-pixel margin. It is shown at exactly 420 × 58, never stretched, so draw it at exactly this shape. Each end cap no wider than 120 pixels; the face between them calm and even, no ornament, gem or strong reflection. WebP, named sd-button-gold-long.webp.
```

### SD-03 · Tombol biru tua *Kembali*

| | |
|---|---|
| Berkas | baru: `sd-button-navy.webp` |
| Kanvas | 708 × 174, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-button-gold.webp` (`SD-01`), `ai-studio/chatgpt-images/out/ui-plate-navy.webp` (`U-02`: bidang biru tua), `docs/revisi-10-analitik/gambar/41/mockup-skenario-2-alasan.webp` (tombol kiri bawah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-button-navy (SD-03 · navy back button, fixed size)
The same plate shape, carved gold border and end caps as the attached gold button (SD-01), but with a dark NAVY stone face with a very faint even cloud grain, like the attached navy plate (U-02). Light cream text ("‹ Kembali") is written on it later by the game, centred. It stands next to the gold button and must look like its quieter partner.
Canvas exactly 708 × 174 pixels, transparent outside the plate; even 6-pixel margin. It is shown at exactly 236 × 58, never stretched, so draw it at exactly this shape. Each end cap no wider than 120 pixels; the face between them calm and even. WebP, named sd-button-navy.webp.
```

### SD-04 · Kartu pilihan, satu baris

| | |
|---|---|
| Berkas | baru: `sd-card.webp` |
| Kanvas | 1752 × 162, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/ui-plate-navy.webp` (`U-02`: bidang biru tua), `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (tiga kartu di kanan) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-card (SD-04 · answer card, one line, fixed size)
A wide, low answer card: a dark NAVY stone face with a very faint even cloud grain (like the attached navy plate) inside a THIN raised gold border with softly rounded corners. Up to five of these cards stand stacked in a column, so the card is quiet: the border is a slim gold bead line, not a heavy frame, and the only ornaments are four tiny carved gold corner accents, each inside the 60 × 60 pixels of its corner.
The game later draws a small round gold choice mark near the left end (centred about 90 pixels from the left edge) and one line of cream text to its right, so the whole face stays plain and evenly dark: no ornament, gem, highlight spot or gradient along the length.
Canvas exactly 1752 × 162 pixels, transparent outside the card; the card fills the canvas with an even 4-pixel margin. It is shown at exactly 584 × 54, never stretched, so draw it at exactly this shape. WebP, named sd-card.webp.
```

### SD-05 · Kartu pilihan, dua baris

| | |
|---|---|
| Berkas | baru: `sd-card-2.webp` |
| Kanvas | 1752 × 204, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-card.webp` (`SD-04`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-card-2 (SD-05 · answer card, two lines, fixed size)
Exactly the same card as the attached one-line answer card (SD-04): the same navy face, the same thin gold bead border, the same rounded corners and the same four tiny corner accents at the same size. Only the card is taller, for two lines of text.
Canvas exactly 1752 × 204 pixels, transparent outside the card; even 4-pixel margin. It is shown at exactly 584 × 68, never stretched, so draw it at exactly this shape. The face stays plain and evenly dark. WebP, named sd-card-2.webp.
```

### SD-06 · Kartu pilihan yang dipilih, satu baris

| | |
|---|---|
| Berkas | baru: `sd-card-on.webp` |
| Kanvas | 1752 × 162, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-card.webp` (`SD-04`), `docs/revisi-10-analitik/gambar/41/mockup-skenario-2-alasan.webp` (kartu ketiga: yang dipilih) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-card-on (SD-06 · answer card, selected, one line, fixed size)
The attached one-line answer card (SD-04) in its SELECTED state: exactly the same shape, size, corners and corner accents, but the gold border is a little thicker and brighter (#F0DFB8 highlights), and the face is a slightly lighter deep blue-teal (about #142E3A) so the chosen card stands out from the others even in black and white.
The game draws the filled choice mark and the text, so the face stays plain: no ornament, symbol, check mark or highlight spot. No red and no green, and no glow outside the border (the game adds its own soft light). Canvas exactly 1752 × 162 pixels, transparent outside the card, with the same even 4-pixel margin as SD-04 so both line up exactly when swapped. Shown at exactly 584 × 54. WebP, named sd-card-on.webp.
```

### SD-07 · Kartu pilihan yang dipilih, dua baris

| | |
|---|---|
| Berkas | baru: `sd-card-on-2.webp` |
| Kanvas | 1752 × 204, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-card-on.webp` (`SD-06`), `ai-studio/chatgpt-images/out/sd-card-2.webp` (`SD-05`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-card-on-2 (SD-07 · answer card, selected, two lines, fixed size)
The attached two-line answer card (SD-05) in the same SELECTED state as the attached selected one-line card (SD-06): the same brighter, slightly thicker gold border and the same lighter deep blue-teal face. Exactly the shape and size of SD-05.
Canvas exactly 1752 × 204 pixels, transparent outside the card, no glow outside the border; even 4-pixel margin as SD-05. Shown at exactly 584 × 68. Plain face, no symbol, no red, no green. WebP, named sd-card-on-2.webp.
```

### SD-08 · Rel sakelar langkah

| | |
|---|---|
| Berkas | baru: `sd-step-rail.webp` |
| Kanvas | 768 × 132, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-card.webp` (`SD-04`: bidang dan tepi yang sama), `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (sakelar di atas panel kanan) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-step-rail (SD-08 · two-step switch rail, fixed size)
A small pill-shaped rail for a two-step switch: a dark NAVY stone face with fully rounded ends inside the same thin gold bead border as the attached answer card. The game lays a separate gold part over one half of the rail and writes "1 Tindakan" on the left half and "2 Alasan" on the right half, so the whole face is plain and evenly dark, with no divider, ornament or highlight spot.
Canvas exactly 768 × 132 pixels, transparent outside the rail; the rail fills the canvas with an even 4-pixel margin. It is shown at exactly 256 × 44, never stretched. WebP, named sd-step-rail.webp.
```

### SD-09 · Kapsul pemutar *Dengarkan cerita*

| | |
|---|---|
| Berkas | baru: `sd-player.webp` |
| Kanvas | 1728 × 156, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-card.webp` (`SD-04`), `public/assets/ui/round-play.webp` (tombol yang duduk di ujung kirinya), `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (kapsul di bagian bawah gambar, kiri) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-player (SD-09 · "listen to the story" player capsule, fixed size)
A long capsule with fully rounded ends that floats over the lower edge of a colourful story picture: a dark NAVY stone face with a very faint even cloud grain inside the same thin gold bead border as the attached answer card (SD-04). It must stand out clearly from any bright picture behind it, so the face is deep and even and the border crisp, with a soft contact shadow under the capsule.
The game places the attached round gold play button over the left end (a circle about 126 pixels across, its centre about 81 pixels from the left edge and on the middle line), then writes a short label, draws a moving gold sound wave in the middle, and writes the duration at the right end. So the face is completely plain and evenly dark from end to end: no ornament, no wave, no button, no divider and no highlight spot.
Canvas exactly 1728 × 156 pixels, transparent outside the capsule and its soft shadow; even 4-pixel margin. It is shown at exactly 576 × 52, never stretched. WebP, named sd-player.webp.
```

### SD-10 · Bagian aktif sakelar langkah

| | |
|---|---|
| Berkas | baru: `sd-step-on.webp` |
| Kanvas | 375 × 114, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-button-gold.webp` (`SD-01`: emasnya), `ai-studio/chatgpt-images/out/sd-step-rail.webp` (`SD-08`: rel tempat bagian ini duduk) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-step-on (SD-10 · active half of the two-step switch, fixed size)
A small pill with fully rounded ends and a warm polished GOLD face with one soft even bevel, the same gold as the attached gold button (SD-01), with a slim raised gold rim. It sits inside one half of the attached navy rail (SD-08) to show which step is active; dark brown text ("1 Tindakan" or "2 Alasan") is written on it later by the game, so the face is calm: no end caps, no ornament, no gem, no strong reflection. It slides between the left and the right half, so it must look right on either side: light from the upper left, but no lighting that only fits one side.
Canvas exactly 375 × 114 pixels, transparent outside the pill; even 4-pixel margin. It is shown at exactly 125 × 38, never stretched. WebP, named sd-step-on.webp.
```

### SD-11 · Kotak petunjuk

| | |
|---|---|
| Berkas | baru: `sd-hint-box.webp` |
| Kanvas | 1752 × 330, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-card.webp` (`SD-04`: bidang dan tepi yang sama), `public/assets/ui/round-play.webp`, `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (kotak di bawah pertanyaan, kanan) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-hint-box (SD-11 · instruction box with a listen row, fixed size)
A wide box with softly rounded corners: a dark NAVY stone face with a very faint even cloud grain inside the same thin gold bead border as the attached answer card (SD-04), with the same four tiny carved gold corner accents. It holds two rows that the game fills: the upper row (the top 156 pixels) gets the attached round gold play button near the left end, a short label, a moving gold sound wave and a duration; the lower row gets one line of small cream text. The game also draws the thin line between the two rows.
So the face is completely plain and evenly dark: no divider, no ornament in the middle, no wave, no button and no highlight spot.
Canvas exactly 1752 × 330 pixels, transparent outside the box; even 4-pixel margin. It is shown at exactly 584 × 110, never stretched. WebP, named sd-hint-box.webp.
```

**Daftar periksa hasil `SD-`.** Satu keluarga dengan `reference.27` dan `U-01`/`U-02`; ukuran kanvas tepat; tanpa tulisan, angka, centang, atau simbol; bidang tempat teks tenang (perkecil ke ukuran tampil dan taruh teks terpanjang di atasnya: *Lanjut ke cerita berikutnya ›*, tindakan 59 karakter); kartu dan kartu dipilih tetap bisa dibedakan dalam hitam-putih; tanpa merah dan hijau; `SD-04`/`SD-06` dan `SD-05`/`SD-07` berimpit tepat bila ditukar.
