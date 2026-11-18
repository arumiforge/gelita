# Rencana Tampilan Skenario Digital dan HUD Seragam

Per 18 November 2026 · `draf pengembang` · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Keputusan di [04](04_CATATAN_KEPUTUSAN.md) (18 November 2026)

Pengembang membuat mockup baru layar tantangan Skenario Digital (langkah 1 *Tindakan*) dan menyertakan tangkapan layar yang sekarang (langkah 2 *Alasan*). Dokumen ini adalah rencana dari diskusi dan wawancara dua putaran hari itu: apa yang berubah di layar Skenario Digital, apa yang berubah di HUD semua layar, gambar yang dipesan, urutan kerja, dan aturan penelitian yang tidak boleh ikut berubah. Belum ada kode yang diubah.

Rujukan yang tetap berlaku: [32 §6](32_IMPLEMENTASI_SKENARIO_DIGITAL.md#6-engine-skenario-di-peramban) (keadaan layar, umpan balik, waktu), [32 §8.2](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) (narasi instruksi hanya lewat ▶, K10), [33 §7.1](33_IMPLEMENTASI_MODE_PENELITIAN.md) (tanpa jam di mode penelitian), [18](18_MOCKUP_LAYAR_BARU.md) (tanpa merah, tanpa kata "salah"/"gagal"), dan arah tampilan pengembang (11 Oktober dan 15 November 2026: satu tampilan emas berukir, tidak ada bagian yang ditarik ke ukuran yang bukan ukuran gambarnya, ukuran untuk siswa kelas 5 di Chromebook).

## 1. Keputusan pengembang (18 November 2026)

**Layar Skenario Digital**

1. **Dua panel tanpa bingkai.** Bingkai, latar tambahan, dan garis tepi kedua panel dihapus supaya lebih lega; yang tersisa hanya latar utama wilayah, yang selalu gelap sehingga teks tetap terbaca. Tidak ada hiasan batik di sudut panel, dan latar utama tidak diganti.
2. **Gambar situasi tidak dipotong.** Gambar tampil utuh dengan perbandingan aslinya (3 : 2, 1536 × 1024), tanpa bingkai.
3. **Pita instruksi dihapus.** Teks instruksi tantangan menjadi baris kecil di bawah pertanyaan besar ("Apa yang akan kamu lakukan?"), dengan tombol bundar ▶ kecil di sampingnya. Narasi instruksi tetap hanya diputar lewat ▶ (32 §8.2, K10).
4. **Baris ⓘ ("Kamu bisa memilih alasan setelah ini.") dan judul kecil "TENTUKAN PILIHANMU" tidak dipakai.** Ruangnya dipakai untuk pilihan jawaban.
5. **Tombol utama pindah ke dalam panel kanan**, di bawah pilihan. Bilah tombol di bawah layar dihapus.
6. **Pemutar *Dengarkan cerita*:** label "Dengarkan cerita", gelombang suara yang **bergerak hanya selama cerita dibacakan** (diam saat berhenti, supaya siswa tahu apakah suara sedang diputar), dan durasi dari berkas suaranya. Cerita yang belum punya narasi tidak menampilkan pemutar, dan panel kiri tetap rapi tanpanya.
7. **Penanda cerita:** "Cerita 1 dari 4" dengan bilah kemajuan per cerita dan bulatan *Kesempatan* di baris yang sama, di atas panel kiri. Bilah menandai posisi dan cerita yang sudah ditutup saja, tidak pernah hasilnya (tanpa warna aman/berisiko).
8. **Subjudul kepala tetap judul varian.** Judul varian sudah memuat nama wilayah (misalnya *Skenario Digital Temanggung*), jadi nama wilayah di tengah atas dihapus.
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

## 2. Yang tidak berubah (aturan penelitian)

- Kunci jawaban, skor, kode kesalahan, dan tindakan aman tidak pernah ada di halaman sebelum server menjawab (32 §5.2).
- Satu percobaan = satu tindakan + satu alasan, dikirim bersama. Alasan ditanyakan apa pun tindakannya. Kalimat tanya alasan "Yang paling penting kupikirkan di sini adalah …" adalah teks instrumen dan tidak diubah tanpa peneliti.
- Pilihan yang dipilih ditandai bentuk (●) dan cahaya, tidak hanya warna. Umpan balik tanpa merah dan tanpa kata "salah"/"gagal".
- Situasi hanya dibacakan bila ▶ diketuk; pilihan dan umpan balik tidak dibacakan. Memilih, pindah langkah, atau pindah cerita menghentikan suara.
- Keenam waktu (`ready_ms`, `reason_ready_ms`, `first_input_ms`, `action_last_input_ms`, `reason_last_input_ms`, `last_input_ms`), `selected()` per langkah, dan event `item_shown` tetap seperti 32 §6.4. Tombol utama yang pindah ke panel kanan tidak mengubah artinya.
- *Kembali* di langkah 2, *Coba lagi*, dan *Lanjut ke cerita berikutnya* tetap seperti 32 §6.2.

## 3. Isi layar per keadaan

Panggung 1280 × 720 dp. Angka di bawah adalah titik awal mockup; ukuran akhir diukur di mockup dengan teks terpanjang dari bank, lalu di Chromebook 1366 × 768 dan 1366 × 657.

| Bagian | Titik awal |
| --- | --- |
| Kepala satu baris | y 16, tinggi 72 |
| Panel kiri (situasi) | x 32, y 104, lebar 600, tinggi 584 |
| Panel kanan (langkah) | x 664, y 104, lebar 584, tinggi 584 |

**Ukuran teks dari bank (18 November 2026).** 24 skenario: situasi sampai 250 karakter (rata-rata 173); 3 atau 4 tindakan, sampai 59 karakter (rata-rata 45, bisa dua baris); 3, 4, atau 5 alasan, sampai 48 karakter (rata-rata 41). Panel kanan harus memuat lima kartu alasan ditambah baris *Pilihanmu*, dan empat kartu tindakan dua baris. Gambar 3 : 2 utuh mengecil (perkiraan 456 × 304) supaya situasi enam baris tetap muat; bila tetap tidak muat, situasi panjang memakai satu ukuran huruf lebih kecil. Panel bergulir hanya sebagai cadangan terakhir.

| Keadaan | Panel kiri | Panel kanan |
| --- | --- | --- |
| `action` | Penanda cerita, gambar, pemutar, situasi | Sakelar **1 Tindakan** · 2 Alasan; "Apa yang akan kamu lakukan?"; baris instruksi + ▶; kartu tindakan; tombol *Lanjut ke alasan* (belum aktif sampai siswa memilih) |
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

Dipesan **sesudah mockup disetujui**, dengan ukuran tampil yang diukur di sana; kanvas dua sampai tiga kali ukuran tampil terbesar, gaya `GELITA CARVED GOLD UI — 2026-11-18` dan papan acuan `reference.27` ([40 → Aturan bersama](40_PROMPT_GAMBAR_ANTARMUKA.md#aturan-bersama)). Prompt siap tempel ditambahkan ke dokumen ini sebagai bagian 8 pada saat itu. Perkiraan daftar:

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
| A | Mockup HTML keempat keadaan (keputusan 12); dibuat 18 November 2026 dengan cerita `tmg-5-b01` dan cerita terpanjang `wnb-5-a04` (4 tindakan, 5 alasan): semua keadaan muat di 1366 × 768 tanpa panel bergulir. Sumbernya `tools/mockup/skenario-41/` (`python3 tools/mockup/skenario-41/build.py hasil.html`); berkas HTML-nya tidak disimpan di repo | Pengembang menyetujui atau memberi catatan |
| B | Prompt `SD-` (bagian 8) dan baris `TASKS.md` | Baris berstatus `todo` |
| C | HUD seragam (bagian 4), satu cabang, bersama pemasangan pelat emas, biru tua, dan hijau, bingkai jendela, dan bilah perunggu dari [40](40_PROMPT_GAMBAR_ANTARMUKA.md) (`U-01` sampai `U-05`, keputusan pengembang 18 November 2026), karena keduanya menata ulang semua layar | Inventaris sebelum/sesudah, uji per jenis layar, cek `tools/qa/stage-check.mjs` |
| D | Tampilan baru Skenario Digital di `skenario.php`, `engines/skenario.js`, `stage.css`; teks baru di berkas bahasa (id dan en) | Uji Skenario Digital hijau; keempat keadaan dicek di Chromebook dua ukuran; aturan bagian 2 utuh |
| E | Pasang gambar `SD-` yang diterima pengembang | Pelat sementara diganti tanpa mengubah tata letak |

Langkah D boleh mulai dengan pelat sementara sebelum gambar `SD-` datang, asal ukurannya sudah ukuran akhir.

## 7. Pertanyaan yang masih terbuka

- Apakah ✓ di Skenario Digital tetap berbentuk angka saja (tanpa "/"), atau digabung dengan bilah kemajuan cerita? Diputuskan di mockup.
- Teks instruksi tantangan Skenario Digital di bank ("Bacalah ceritanya. Pilih apa yang akan kamu lakukan, lalu pilih alasanmu.") panjangnya dua baris di lebar panel kanan; mungkin perlu dipendekkan di bank (`nodes`) agar menjadi satu baris.
- **`U-07` (bingkai Serpihan Cahaya) ditahan** sampai kristalnya dipesan dan dipasang bersama (40 → `U-07`, catatan 18 November 2026). Bila HUD seragam (langkah C) mengubah ukuran penghitung Serpihan Cahaya, ukuran kristal ikut diukur ulang.
