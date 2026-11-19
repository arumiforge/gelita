# Rencana Tampilan Skenario Digital dan HUD Seragam

Per 19 November 2026 · `disetujui pengembang` (mockup disetujui 19 November 2026) · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Keputusan di [04](04_CATATAN_KEPUTUSAN.md) (18 November 2026)

Pengembang membuat mockup baru layar tantangan Skenario Digital (langkah 1 *Tindakan*) dan menyertakan tangkapan layar yang sekarang (langkah 2 *Alasan*). Dokumen ini adalah rencana dari diskusi dan wawancara dua putaran hari itu: apa yang berubah di layar Skenario Digital, apa yang berubah di HUD semua layar, gambar yang dipesan, urutan kerja, dan aturan penelitian yang tidak boleh ikut berubah. Belum ada kode yang diubah. Pada 19 November 2026 pengembang menyetujui mockup tanpa catatan (langkah A), lalu di hari yang sama memberi delapan putaran catatan perbaikan (bagian 1, keputusan 17–43). Pengembang menyetujui hasil putaran kedelapan pada hari yang sama; mockup itu menjadi acuan, dan pesanan gambar di bagian 8 memakai ukurannya.

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

**Catatan pengembang putaran ketiga (19 November 2026, dengan gambar contoh)**

26. **Bagian atas layar cerah:** pita gelap kepala layar (keputusan 20) dibuang; lapisan gelap latar mulai di bawah kepala layar, jadi latar di bagian atas tampak terang. Menggantikan keputusan 20.
27. **Logo GELITA tidak dipakai di kepala layar tantangan** (dicoba pengembang). Nomor tantangan, judul, dan nama kota duduk di satu kapsul gelap supaya tetap terbaca di atas latar yang terang. Kapsul ini dipakai kelima tantangan, jadi gambarnya ditentukan di langkah C (sementara CSS).
28. **Panel kiri menjadi satu kotak berbingkai**, seperti gambar contoh pengembang: bidang biru tua dengan tepi emas tipis dan sudut membulat. Di dalamnya dari atas: baris *Cerita 1 dari 4*, bilah kemajuan, dan *Kesempatan* (di atas gambar, tidak lagi menimpa gambar); gambar situasi **540 × 360** dengan kapsul *Dengarkan cerita* (516 × 52) di bagian bawah gambar; lalu situasi. Situasi terpanjang berakhir 5 dp di atas tepi dalam kotak. Menggantikan keputusan 1 untuk panel kiri dan keputusan 24 (letak penanda cerita dan ukuran gambar). Gambar baru `SD-12` (bingkai panel 600 × 584).
29. **Baris *Dengarkan petunjuk* tanpa gelombang dan tanpa durasi**, dengan tombol ▶ (32 dp) dan tulisan (15 px) lebih kecil; kotak petunjuk kini 584 × 100.

**Catatan pengembang putaran keempat sampai kedelapan (19 November 2026; mockup terakhir disetujui hari itu)**

30. **Tanpa kotak di kedua sisi dan tanpa garis pemisah.** Panel kiri tidak lagi berbingkai (menggantikan keputusan 28 untuk bingkainya; bingkai panel lama batal), sisi kanan juga tanpa kotak, dan garis tipis di antara keduanya dihapus. Penanda cerita, gambar, dan situasi berdiri langsung di atas latar.
31. **Latar layar baru khusus Skenario Digital**, satu per wilayah (`SD-12` sampai `SD-14`): langit terang di bagian atas untuk kepala layar, bagian tengah dan bawah tenang, agak gelap, dan sedikit detail supaya teks di kedua sisi terbaca. Menggantikan "latar utama tidak diganti" di keputusan 1.
32. **Situasi menjorok** sejajar tepi gambar (jarak 14 dp kiri dan kanan).
33. **Petunjuk di atas pertanyaan, seperti *Pilihanmu*, tanpa latar:** baris atas ▶ dan tulisan *Dengarkan petunjuk*; selama dibacakan tulisan menjadi *Sedang dibacakan…* dan gelombang serta durasi tampil, lalu hilang lagi saat berhenti. Di bawahnya teks instruksi rata kiri, lalu garis tipis. ▶ dekat dengan teks yang dibacakannya; bila teks dua baris, ▶ sejajar baris pertama. Menggantikan keputusan 25 dan 29 (kotak petunjuk; kotak petunjuk lama batal).
34. ***↳ Pilihanmu · …* tanpa ▶ dan tanpa garis warna di kiri**, dengan garis tipis di bawahnya. Pilihan jawaban tidak pernah dibacakan (bagian 2).
35. **Kedua pertanyaan rata kiri** dan **semua teks di sisi kanan berukuran sama dengan teks situasi (20 px)**: *Dengarkan petunjuk*, instruksi, *Pilihanmu*, pertanyaan (tebal, emas muda), dan pilihan jawaban.
36. **Judul tebal, nama kota di tengah tinggi judul, kapsul judul menyatu dengan medali nomor tantangan** (kapsul mulai dari tengah medali). Gambarnya tetap di langkah C (keputusan 27).
37. **Pemutar *Dengarkan cerita* berupa pil kecil di tengah bawah gambar** (252 × 52) tanpa gelombang dan durasi; selama dibacakan pil melebar (480 × 52) dan menampilkan gelombang serta durasi. Gambar situasi **504 × 336** supaya situasi punya ruang lebih (menggantikan 540 × 360).
38. **Tombol maju memakai pelat dengan bulatan panah ke kanan di ujung kanan** (seperti `public/assets/ui/plate-next.webp`), tulisan tanpa panah, ujung kiri polos: *Lanjut ke alasan*, *Kirim jawaban*, *Coba lagi* (284 × 58) dan *Lanjut ke cerita berikutnya* (392 × 58). Menggantikan tombol emas `SD-01`/`SD-02` lama dan catatan tulisan putih/hitam di putaran sebelumnya.
39. **Tombol *Kembali* baru: bulatan panah ke kiri di ujung kiri** (seperti `plate-back.webp`), tulisan *Kembali* tanpa panah, ujung kanan polos, 248 × 58.
40. **Tulisan tombol tepat di tengah bidang polos pelat**, mendatar dan tegak, tanpa menghitung bulatan panah dan ujung hiasan (diukur di mockup: selisih di bawah 1 dp).
41. **Sakelar langkah baru: dua tab dengan medali nomor 1 dan 2 di dalam gambarnya**, 144 × 40 masing-masing, tab aktif emas terang dengan tulisan gelap, tab lain gelap. Menggantikan rel dan bagian aktif (`SD-08`, `SD-10` lama).
42. **Pilihan jawaban tanpa latar dan tanpa garis tepi; hanya jawaban yang dipilih** mendapat bidang dan tepi emas (584 × 54, dua baris 584 × 70). Menggantikan kartu biasa lama.
43. **Kartu dan pesanan lama batal:** `SD-01` sampai `SD-12` versi sebelumnya diganti daftar baru di bagian 8 (19 November 2026); belum ada yang dibuat.

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

**Ukuran teks dari bank (18 November 2026).** 24 skenario: situasi sampai 250 karakter (rata-rata 173); 3 atau 4 tindakan, sampai 59 karakter (rata-rata 45, bisa dua baris); 3, 4, atau 5 alasan, sampai 48 karakter (rata-rata 41).

**Tata letak yang disetujui (mockup 19 November 2026, putaran kedelapan).** Gambar situasi 3 : 2 utuh **504 × 336** di x 80, y 150, dengan pil *Dengarkan cerita* di tengah bawahnya; baris *Cerita 1 dari 4* dan *Kesempatan* di atas gambar (y 110); situasi di bawah gambar, menjorok 14 dp, 20 px (19 px untuk situasi lebih dari 200 karakter). Sisi kanan mulai x 664, lebar 584: sakelar dua tab di tengah atas, petunjuk atau *Pilihanmu*, pertanyaan, pilihan jawaban, tombol di bawah (y 626). Situasi terpanjang (`wnb-5-a04`) dan lima alasan muat di keempat keadaan tanpa panel bergulir; tangkapan di `gambar/41/`.

| Keadaan | Sisi kiri | Sisi kanan |
| --- | --- | --- |
| `action` | Baris penanda cerita dan *Kesempatan*; gambar dengan pil *Dengarkan cerita*; situasi | Tab **1 Tindakan** · 2 Alasan; ▶ *Dengarkan petunjuk* dan teks instruksi; "Apa yang akan kamu lakukan?"; pilihan tindakan; *Lanjut ke alasan* (belum aktif sampai siswa memilih) |
| `reason` | sama | Tab 1 Tindakan · **2 Alasan**; *↳ Pilihanmu · …*; "Yang paling penting kupikirkan di sini adalah …"; pilihan alasan; *Kembali* dan *Kirim jawaban* berdampingan |
| `feedback` | sama | Judul umpan balik "Pilihan yang aman!" setelah tindakan aman, selain itu "Ayo pikirkan lagi" (teks mockup, diputuskan pengembang 19 November 2026; bahasa Inggris "A safe choice!" / "Let's think again"); teks umpan balik dari bank; "Kamu masih punya {n} kesempatan …" bila belum tertutup; *Coba lagi* atau *Lanjut ke cerita berikutnya* |
| `explained` | sama | "Kesempatanmu untuk cerita ini sudah habis …"; "Ini tindakan yang aman"; tindakan aman; penjelasan; *Lanjut ke cerita berikutnya* |
| selesai | Modal hasil dari kerangka, lalu *Lanjut* | |

## 4. HUD seragam: pekerjaan

1. **Inventaris.** Untuk setiap layar permainan (sekitar 35 tampilan di `app/Views/game/`): tinggi pita (`hudStyle`: 120, 84, atau 56 dp), tata letak (`hudLayout`: kiri, `logo-centre`, `title-centre`, `region-centre`), dan tombol yang tampil (logo, judul/wilayah, Serpihan Cahaya, bahasa, suara, menu, Kembali). Diukur di Chromebook 1366 × 768 dan 1366 × 657 dengan peramban otomatis, sama seperti [40 → Ukuran yang diukur](40_PROMPT_GAMBAR_ANTARMUKA.md).
2. **Satu ukuran tombol bundar dan satu ukuran logo** untuk semua pita, satu jarak tepi kanan, satu urutan (bahasa · suara · menu · Kembali/Keluar).
3. **Aturan tampil** keputusan 16 diterapkan di `components/hud-menu.php` dari satu tempat, bukan per halaman, dan diuji per jenis layar.
4. **Tantangan:** `hudLayout` `region-centre` diganti kepala satu baris (keputusan 13); kerangka `_frame.php` dan `stage.css §Tantangan` diatur ulang untuk kelima engine. Koordinat isi engine lain ikut naik; masing-masing diperiksa ulang.

## 5. Gambar yang dipesan (baris `SD-` di `ai-studio/chatgpt-images/TASKS.md`)

Dipesan **sesudah mockup disetujui**, dengan ukuran tampil yang diukur di sana; kanvas dua sampai tiga kali ukuran tampil terbesar, gaya `GELITA CARVED GOLD UI — 2026-11-18` dan papan acuan `reference.27` ([40 → Aturan bersama](40_PROMPT_GAMBAR_ANTARMUKA.md#aturan-bersama)). Daftar akhir dan prompt siap tempelnya ada di [bagian 8](#8-prompt-gambar-sd-) (19 November 2026, dari mockup yang disetujui); daftar di bawah ini hanya perkiraan awal 18 November 2026:

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
| A | Mockup HTML keempat keadaan (keputusan 12); dibuat 18 November 2026 dengan cerita `tmg-5-b01` dan cerita terpanjang `wnb-5-a04` (4 tindakan, 5 alasan): semua keadaan muat di 1366 × 768 tanpa panel bergulir. Sumbernya `tools/mockup/skenario-41/` (`python3 tools/mockup/skenario-41/build.py hasil.html`); berkas HTML-nya tidak disimpan di repo, tangkapan keempat keadaan di `gambar/41/` | **Selesai 19 November 2026:** disetujui pengembang, lalu diperbaiki dengan delapan putaran catatannya (keputusan 17–43) dan disetujui lagi |
| B | Prompt `SD-` (bagian 8) dan baris `TASKS.md` | **Selesai 19 November 2026:** ditulis ulang dari mockup yang disetujui; baris `SD-01` sampai `SD-14` (11 bagian antarmuka dan 3 latar layar) berstatus `todo` |
| C | **Selesai 19 November 2026** (kepala tantangan satu baris, bagian 4 no. 4; satu ukuran tombol bundar dan logo, satu sudut dan urutan, no. 1–3; pelat, bilah, dan bingkai `U-01` sampai `U-05`; [04](04_CATATAN_KEPUTUSAN.md)). HUD seragam (bagian 4), satu cabang, bersama pemasangan pelat emas, biru tua, dan hijau, bingkai jendela, dan bilah perunggu dari [40](40_PROMPT_GAMBAR_ANTARMUKA.md) (`U-01` sampai `U-05`, keputusan pengembang 18 November 2026), karena keduanya menata ulang semua layar | Inventaris sebelum/sesudah, uji per jenis layar, cek `tools/qa/stage-check.mjs` |
| D | Tampilan baru Skenario Digital di `skenario.php`, `engines/skenario.js`, `stage.css`; teks baru di berkas bahasa (id dan en) | Uji Skenario Digital hijau; keempat keadaan dicek di Chromebook dua ukuran; aturan bagian 2 utuh |
| E | Pasang gambar `SD-` yang diterima pengembang (bagian 8 → *Pemasangan*), termasuk tiga latar layar sebagai slot baru `bg.{wilayah}.skenario` | Pelat sementara diganti tanpa mengubah tata letak |

Langkah D boleh mulai dengan pelat sementara sebelum gambar `SD-` datang, asal ukurannya sudah ukuran akhir.

## 7. Pertanyaan yang masih terbuka

- ~~Apakah ✓ di Skenario Digital tetap berbentuk angka saja (tanpa "/"), atau digabung dengan bilah kemajuan cerita?~~ **Angka saja**, seperti mockup; bilah *Cerita 1 dari 4* tetap terpisah dan hanya menandai posisi (pengembang, 19 November 2026).
- ~~Teks instruksi tantangan Skenario Digital di bank perlu dipendekkan agar satu baris?~~ **Tidak**: teks dan rekamannya tetap (pengembang, 19 November 2026). Sejak keputusan 33 teksnya ada di bawah ▶ *Dengarkan petunjuk*, rata kiri, 20 px, dan boleh dua baris.
- **`U-07` (bingkai Serpihan Cahaya) ditahan** sampai kristalnya (`U-36`, prompt di [40](40_PROMPT_GAMBAR_ANTARMUKA.md) sejak 19 November 2026) dibuat dan keduanya dipasang bersama. Bingkainya sudah di `ai-studio/chatgpt-images/out/hud-shards-frame.webp` (dari PR #420, yang ditutup). Bila HUD seragam (langkah C) mengubah ukuran penghitung Serpihan Cahaya, ukuran kristal tidak berubah: kanvasnya sama persis dengan lubang bingkai, jadi ikut skala bingkai.

## 8. Prompt gambar `SD-`

Ditulis ulang 19 November 2026 dari mockup yang disetujui pengembang (putaran kedelapan, keputusan 30–43); daftar `SD-01` sampai `SD-12` yang ditulis pagi itu batal dan belum ada yang dibuat. Ukuran tampil diukur di mockup (panggung 1280 × 720 dp) dengan semua teks dari bank dan berkas bahasa, Indonesia dan Inggris; setiap label Inggris lebih pendek dari label Indonesianya, jadi setiap bagian punya **satu ukuran tetap**. Kanvas tiga kali ukuran tampil. Tangkapan mockup yang disetujui ada di `gambar/41/` dan dilampirkan supaya letak dan besar bagian terlihat; bagian di mockup itu gambar sementara, bukan tampilan yang ditiru.

Bagian antarmuka (`SD-01` sampai `SD-11`) dimulai dengan blok `GELITA CARVED GOLD UI — 2026-11-18` dari [40 → Aturan bersama](40_PROMPT_GAMBAR_ANTARMUKA.md#aturan-bersama), kata per kata, dan dipasang **tanpa ditarik** pada ukuran tampilnya. Latar layar (`SD-12` sampai `SD-14`) dimulai dengan blok `GELITA ACTIVE VISUAL DIRECTION — 2026-10-11`, seperti `S-W` di 40. Yang tetap digambar permainan, bukan gambar: tulisan semua tombol dan tab, ○/●, gelombang suara dan durasi, ▶ (`round-play`), baris penanda cerita dan *Kesempatan*, garis tipis di bawah petunjuk dan *Pilihanmu*, dan cahaya lembut di sekitar jawaban yang dipilih. Pilihan jawaban yang belum dipilih tidak punya gambar (keputusan 42), begitu juga kapsul judul (langkah C, keputusan 27).

| Kode | Berkas | Tampil (dp) | Kanvas | Dipakai untuk |
| --- | --- | --- | --- | --- |
| `SD-01` | `public/assets/ui/sd-next.webp` | 284 × 58 | 852 × 174 | *Lanjut ke alasan*, *Kirim jawaban*, *Coba lagi* |
| `SD-02` | `public/assets/ui/sd-next-long.webp` | 392 × 58 | 1176 × 174 | *Lanjut ke cerita berikutnya* |
| `SD-03` | `public/assets/ui/sd-back.webp` | 248 × 58 | 744 × 174 | *Kembali* di langkah 2 |
| `SD-04` | `public/assets/ui/sd-step-1.webp` | 148 × 44 | 444 × 132 | Tab langkah 1, tidak aktif |
| `SD-05` | `public/assets/ui/sd-step-1-on.webp` | 148 × 44 | 444 × 132 | Tab langkah 1, aktif |
| `SD-06` | `public/assets/ui/sd-step-2.webp` | 148 × 44 | 444 × 132 | Tab langkah 2, tidak aktif |
| `SD-07` | `public/assets/ui/sd-step-2-on.webp` | 148 × 44 | 444 × 132 | Tab langkah 2, aktif |
| `SD-08` | `public/assets/ui/sd-chosen.webp` | 584 × 54 | 1752 × 162 | Jawaban yang dipilih, satu baris |
| `SD-09` | `public/assets/ui/sd-chosen-2.webp` | 584 × 70 | 1752 × 210 | Jawaban yang dipilih, dua baris |
| `SD-10` | `public/assets/ui/sd-listen.webp` | 252 × 52 | 756 × 156 | Pil *Dengarkan cerita* (diam) |
| `SD-11` | `public/assets/ui/sd-listen-wide.webp` | 480 × 52 | 1440 × 156 | Pil *Dengarkan cerita* selama dibacakan |
| `SD-12` | `ai-studio/game-images/assets/latar/bg.temanggung.skenario.webp` | layar penuh | 1920 × 1080 | Latar Skenario Digital Temanggung |
| `SD-13` | `ai-studio/game-images/assets/latar/bg.magelang.skenario.webp` | layar penuh | 1920 × 1080 | Latar Skenario Digital Magelang |
| `SD-14` | `ai-studio/game-images/assets/latar/bg.wonosobo.skenario.webp` | layar penuh | 1920 × 1080 | Latar Skenario Digital Wonosobo |

Tab 148 × 44: badan pil 144 × 40 dengan medali bundar 44 dp di ujung kiri yang sedikit keluar dari badan pil, seperti mockup. Urutan: `SD-01` lebih dulu; `SD-02` dan `SD-03` dari `SD-01`; `SD-04` sendiri; `SD-05` sampai `SD-07` dari `SD-04`; `SD-08` sendiri, `SD-09` dari `SD-08`; `SD-10` sendiri, `SD-11` dari `SD-10`; `SD-12` sampai `SD-14` sendiri-sendiri.

**Pemasangan (langkah E).** Bagian antarmuka dipindahkan dari `ai-studio/chatgpt-images/out/` ke `public/assets/ui/` dan dipanggil lewat `asset_url_versioned('ui/…')`. Untuk `SD-01` sampai `SD-03`, tepi bidang polos tiap gambar diukur dulu (seperti mockup mengukur `plate-next` dan `plate-back`), lalu tulisan diletakkan tepat di tengah bidang itu (keputusan 40). Tombol yang belum aktif memakai gambar yang sama dengan `filter` CSS. Latar `SD-12` sampai `SD-14` masuk Katalog Aset (`ai-studio/game-images/`, entri `data.json` dan baris `REVIEW.md`) sebagai slot baru `bg.{wilayah}.skenario` di `ImageSlots`, lalu `php spark gelita:katalog:impor`; halaman katalog dibangun ulang (`gelita:katalog:halaman`).

### SD-01 · Tombol maju

| | |
|---|---|
| Berkas | baru: `sd-next.webp` |
| Kanvas | 852 × 174, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `public/assets/ui/plate-next.webp` (bentuk yang diminta), `public/assets/ui/round-next.webp` (bulatan panah), `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (tombol kanan bawah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-next (SD-01 · "go on" button with an arrow disc, fixed size)
A horizontal button plate with the same layout as the attached plate with the arrow disc on its right end, redrawn in the carved-gold family of the attached style board: a dark NAVY stone face with a very faint even cloud grain inside a carved gold rim; at the RIGHT end a round carved gold disc that overlaps the end of the plate, with one embossed gold arrow pointing RIGHT on a dark face, like the attached round arrow button; at the LEFT end only a small carved gold end cap with one scroll curl. Light cream text (for example "Lanjut ke alasan") is written later by the game, centred on the plain face, so the face between the left cap and the disc is calm and even: no ornament, gem, cloud motif or highlight spot in it.
Canvas exactly 852 × 174 pixels, transparent outside the plate and the disc; the disc is about 174 pixels across and touches the right edge; the plate body is about 150 pixels high, centred on the same middle line. It is shown at exactly 284 × 58, never stretched, so draw it at exactly this shape. WebP, named sd-next.webp.
```

### SD-02 · Tombol maju panjang

| | |
|---|---|
| Berkas | baru: `sd-next-long.webp` |
| Kanvas | 1176 × 174, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-next.webp` (`SD-01`), `docs/revisi-10-analitik/gambar/41/mockup-skenario-4-tindakan-aman.webp` (tombol bawah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-next-long (SD-02 · long "go on" button, fixed size)
Exactly the same button as the attached one (SD-01): the same navy face, carved gold rim, left end cap and right arrow disc at the same size. Only the calm face between the left cap and the disc is longer, for the label "Lanjut ke cerita berikutnya" written later by the game.
Canvas exactly 1176 × 174 pixels, transparent outside; the disc touches the right edge, the plate body about 150 pixels high on the same middle line. It is shown at exactly 392 × 58, never stretched. The face stays plain: no ornament, gem, cloud motif or highlight spot. WebP, named sd-next-long.webp.
```

### SD-03 · Tombol *Kembali*

| | |
|---|---|
| Berkas | baru: `sd-back.webp` |
| Kanvas | 744 × 174, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-next.webp` (`SD-01`: rim dan bidang yang sama), `public/assets/ui/plate-back.webp` (bentuk yang diminta), `docs/revisi-10-analitik/gambar/41/mockup-skenario-2-alasan.webp` (tombol kiri bawah) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-back (SD-03 · back button with an arrow disc, fixed size)
The mirror partner of the attached "go on" button (SD-01), with the layout of the attached back plate: the same navy face and carved gold rim, but the round carved gold disc is at the LEFT end with one embossed gold arrow pointing LEFT, and the RIGHT end has only the small carved end cap. Light from the upper left as on every part (do not mirror the lighting). Light cream text "Kembali" is written later by the game, centred on the plain face, so the face between the disc and the right cap is calm and even.
Canvas exactly 744 × 174 pixels, transparent outside; the disc about 174 pixels across touches the left edge, the plate body about 150 pixels high on the same middle line. It is shown at exactly 248 × 58, never stretched. WebP, named sd-back.webp.
```

### SD-04 · Tab langkah 1, tidak aktif

| | |
|---|---|
| Berkas | baru: `sd-step-1.webp` |
| Kanvas | 444 × 132, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `public/assets/ui/challenge-badge.webp` (medali nomor), `docs/revisi-10-analitik/gambar/41/mockup-skenario-2-alasan.webp` (dua tab di atas sisi kanan; tab 1 di sini tidak aktif) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-step-1 (SD-04 · step tab "1", not active, fixed size)
A small pill-shaped tab with fully rounded ends and a dark NAVY stone face inside a thin carved gold rim. Its LEFT end is a round medallion, slightly taller than the pill, in the style of the attached number medallion: a carved gold ring around a face with the numeral "1" embossed in it. In this NOT ACTIVE state the medallion is dimmed antique bronze and the numeral muted, so the tab reads as "not here now". A short word ("Tindakan") is written later by the game in muted cream on the plain face to the right of the medallion, so that face is calm and even.
The numeral "1" is the only character in the picture. Canvas exactly 444 × 132 pixels, transparent outside: the medallion is a circle 132 pixels across at the left edge; the pill body is 120 pixels high on the same middle line and runs to the right edge. It is shown at exactly 148 × 44, never stretched. WebP, named sd-step-1.webp.
```

### SD-05 · Tab langkah 1, aktif

| | |
|---|---|
| Berkas | baru: `sd-step-1-on.webp` |
| Kanvas | 444 × 132, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-step-1.webp` (`SD-04`), `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (tab 1 aktif) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-step-1-on (SD-05 · step tab "1", active, fixed size)
Exactly the shape, size and medallion of the attached not-active tab (SD-04), in the ACTIVE state: the pill face is warm, bright polished gold (light #FBEBC0 to #E3C07C) with one soft even bevel, and the medallion is full bright gold with the numeral "1" crisply embossed. Dark brown text ("Tindakan") is written later by the game on the plain gold face, so it stays calm and even. Active and not active must differ clearly in black and white.
Canvas exactly 444 × 132 pixels, transparent outside, the same outline as SD-04 so the two line up when swapped. Shown at exactly 148 × 44. WebP, named sd-step-1-on.webp.
```

### SD-06 · Tab langkah 2, tidak aktif

| | |
|---|---|
| Berkas | baru: `sd-step-2.webp` |
| Kanvas | 444 × 132, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-step-1.webp` (`SD-04`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-step-2 (SD-06 · step tab "2", not active, fixed size)
Exactly the attached not-active tab (SD-04), with the numeral "2" embossed in the medallion instead of "1". Everything else is identical: shape, size, dimmed bronze medallion, navy face, rim. The numeral "2" is the only character. Canvas exactly 444 × 132 pixels, transparent outside, the same outline as SD-04. Shown at exactly 148 × 44. WebP, named sd-step-2.webp.
```

### SD-07 · Tab langkah 2, aktif

| | |
|---|---|
| Berkas | baru: `sd-step-2-on.webp` |
| Kanvas | 444 × 132, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-step-1-on.webp` (`SD-05`), `ai-studio/chatgpt-images/out/sd-step-2.webp` (`SD-06`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-step-2-on (SD-07 · step tab "2", active, fixed size)
Exactly the attached active tab (SD-05), with the numeral "2" embossed in the medallion instead of "1", the same as the attached not-active tab "2" (SD-06). Bright gold face and medallion, calm face for the game's dark text ("Alasan"). The numeral "2" is the only character. Canvas exactly 444 × 132 pixels, transparent outside, the same outline as SD-04. Shown at exactly 148 × 44. WebP, named sd-step-2-on.webp.
```

### SD-08 · Jawaban yang dipilih, satu baris

| | |
|---|---|
| Berkas | baru: `sd-chosen.webp` |
| Kanvas | 1752 × 162, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `public/assets/ui/ui-plate-navy.webp` (`U-02`: bidang biru tua), `docs/revisi-10-analitik/gambar/41/mockup-skenario-2-alasan.webp` (jawaban ketiga: yang dipilih) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-chosen (SD-08 · chosen answer, one line, fixed size)
A wide, low answer bar shown only behind the answer a child has chosen; the other answers have no bar at all. A deep blue-teal stone face (about #142E3A, a little lighter than navy) with a very faint even grain inside a bright, slightly thick gold bead border (#F0DFB8 highlights) with softly rounded corners and four tiny carved gold corner accents, each inside the 60 × 60 pixels of its corner. The game draws a filled round gold choice mark near the left end (centred about 90 pixels from the left edge) and one line of cream text to its right, and adds its own soft light around the bar, so the face stays plain: no symbol, check mark, highlight spot or glow outside the border. No red and no green.
Canvas exactly 1752 × 162 pixels, transparent outside the bar; even 4-pixel margin. It is shown at exactly 584 × 54, never stretched. WebP, named sd-chosen.webp.
```

### SD-09 · Jawaban yang dipilih, dua baris

| | |
|---|---|
| Berkas | baru: `sd-chosen-2.webp` |
| Kanvas | 1752 × 210, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-chosen.webp` (`SD-08`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-chosen-2 (SD-09 · chosen answer, two lines, fixed size)
Exactly the attached chosen-answer bar (SD-08): the same face, border, corners and corner accents at the same size. Only the bar is taller, for two lines of text. Plain face, no symbol, no glow outside the border, no red, no green.
Canvas exactly 1752 × 210 pixels, transparent outside; even 4-pixel margin. It is shown at exactly 584 × 70, never stretched. WebP, named sd-chosen-2.webp.
```

### SD-10 · Pil *Dengarkan cerita*

| | |
|---|---|
| Berkas | baru: `sd-listen.webp` |
| Kanvas | 756 × 156, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `public/assets/ui/round-play.webp` (tombol yang duduk di ujung kirinya), `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (pil di tengah bawah gambar) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-listen (SD-10 · "listen to the story" pill, fixed size)
A short capsule with fully rounded ends that floats over the lower middle of a colourful story picture: a dark NAVY stone face with a very faint even cloud grain inside a thin gold bead border, with a soft contact shadow under it. It must stand out clearly from any bright picture behind it, so the face is deep and even and the border crisp.
The game places the attached round gold play button over the left end (a circle about 126 pixels across, its centre about 81 pixels from the left edge and on the middle line) and writes a short label to its right, so the face is completely plain: no ornament, no wave, no button and no highlight spot.
Canvas exactly 756 × 156 pixels, transparent outside the capsule and its soft shadow; even 4-pixel margin. It is shown at exactly 252 × 52, never stretched. WebP, named sd-listen.webp.
```

### SD-11 · Pil *Dengarkan cerita* selama dibacakan

| | |
|---|---|
| Berkas | baru: `sd-listen-wide.webp` |
| Kanvas | 1440 × 156, latar transparan |
| Lampiran | `ai-studio/game-images/assets/referensi/reference.27.webp`, `ai-studio/chatgpt-images/out/sd-listen.webp` (`SD-10`) |

```text
GELITA CARVED GOLD UI — 2026-11-18
Asset: sd-listen-wide (SD-11 · "listen to the story" pill while reading, fixed size)
Exactly the attached pill (SD-10): the same face, border, rounded ends and shadow, only longer. While the story is read aloud the game shows this wider pill with its play button at the left end, a label, a moving gold sound wave in the middle and the duration at the right end, so the face is completely plain from end to end: no wave, no divider, no ornament and no highlight spot.
Canvas exactly 1440 × 156 pixels, transparent outside the capsule and its soft shadow; even 4-pixel margin. It is shown at exactly 480 × 52, never stretched. WebP, named sd-listen-wide.webp.
```

### SD-12 · Latar Skenario Digital Temanggung

| | |
|---|---|
| Kode berkas | `bg.temanggung.skenario` → simpan sebagai `bg.temanggung.skenario.webp` (slot baru) |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.temanggung.region.webp` (tempat dan suasana wilayah); foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Sindoro-Sumbing-sunrise-02.jpg`; `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` (letak kepala layar, gambar, teks, dan pilihan di atas latar) |
| Dipakai | Tantangan kelima Temanggung: kepala layar di atas, gambar situasi dan teks di kiri, pertanyaan dan pilihan di kanan, semuanya tanpa kotak |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.temanggung.skenario  (SD-12 · Skenario Digital screen, Temanggung)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all. The attached photo is a factual reference for the mountains only; do not copy it.

SUBJECT AND COMPOSITION
Wide 16:9 background behind a quiz screen that has NO boxes: a title bar and round buttons in the top 12%, a story picture and story text on the left half, a question and answer rows on the right half, with cream and gold text standing directly on this background.
Top 12%: bright, clear morning sky, soft and even, so the title bar reads as bright and fresh.
Horizon band between 12% and 30% of the height: Mount Sumbing (broader, left) and Mount Sindoro (a clean cone, right) side by side, smooth slopes, no smoke.
Everything below 30% of the height: calm terraced tobacco and coffee fields and a few far tiled roofs in gentle shade, as if under a soft passing cloud, low in contrast and low in detail, getting gently darker towards the bottom, so that cream text and gold lines on top read clearly without a box. No bright spots, no strong edges, no small busy detail below 30%. The left and right thirds below 30% are the calmest.
Accuracy — No people, animals, signs, screens, phones, paths with signposts, temples, mist, light rays or water reflections.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.temanggung.skenario.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### SD-13 · Latar Skenario Digital Magelang

| | |
|---|---|
| Kode berkas | `bg.magelang.skenario` → simpan sebagai `bg.magelang.skenario.webp` (slot baru) |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.magelang.region.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Borobudur-aerial-plain-volcano-01.jpg` (dataran dan gunung; candinya tidak digambar); `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` |
| Dipakai | Tantangan kelima Magelang, seperti `SD-12` |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.magelang.skenario  (SD-13 · Skenario Digital screen, Magelang)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all. The attached photo is a factual reference for the plain and the mountains only; do not copy it and do not draw its temple.

SUBJECT AND COMPOSITION
Wide 16:9 background behind a quiz screen that has NO boxes: a title bar and round buttons in the top 12%, a story picture and story text on the left half, a question and answer rows on the right half, with cream and gold text standing directly on this background.
Top 12%: bright, clear morning sky, soft and even.
Horizon band between 12% and 30% of the height: the Kedu plain seen from the west, with Mount Merbabu (broad, several low summits) and Mount Merapi (a steeper cone) far away on the horizon and the soft line of the Menoreh hills; smooth slopes, no smoke, no eruption.
Everything below 30% of the height: calm rice fields and palm groves of the plain in gentle shade, as if under a soft passing cloud, low in contrast and low in detail, getting gently darker towards the bottom, so that cream text and gold lines on top read clearly without a box. No bright spots, no strong edges, no small busy detail below 30%. The left and right thirds below 30% are the calmest.
Accuracy — No temples, stupas or reliefs, no people, animals, signs, screens, phones, mist, light rays or water reflections.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.magelang.skenario.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

### SD-14 · Latar Skenario Digital Wonosobo

| | |
|---|---|
| Kode berkas | `bg.wonosobo.skenario` → simpan sebagai `bg.wonosobo.skenario.webp` (slot baru) |
| Kanvas | 1920 × 1080 |
| Lampiran | Acuan gaya `ai-studio/game-images/assets/referensi/reference.06.webp`; `ai-studio/game-images/assets/latar/bg.wonosobo.region.webp`; foto `ai-studio/chatgpt-images/inbox/reference-Kedu-researcher-Telaga-Warna-Dieng-05.jpg` (dataran tinggi Dieng); `docs/revisi-10-analitik/gambar/41/mockup-skenario-1-tindakan.webp` |
| Dipakai | Tantangan kelima Wonosobo, seperti `SD-12` |

```text
GELITA ACTIVE VISUAL DIRECTION — 2026-10-11
Asset: bg.wonosobo.skenario  (SD-14 · Skenario Digital screen, Wonosobo)
Premium stylized 3D animated adventure game for Indonesian children aged 8–14. Softly rounded dimensional forms, warm cinematic light, brass gold (#DFC087, #F0DFB8), deep navy (#0B1320, #1B2740), restrained turquoise, earthy browns and cream. Match GELITA reference.01 and reference.09 in finish and mood; keep the exact subject specified. No painted brush or paper texture.
Characters: no people at all. The attached photo is a factual reference for the plateau only; do not copy it.

SUBJECT AND COMPOSITION
Wide 16:9 background behind a quiz screen that has NO boxes: a title bar and round buttons in the top 12%, a story picture and story text on the left half, a question and answer rows on the right half, with cream and gold text standing directly on this background.
Top 12%: bright, clear morning sky, soft and even.
Horizon band between 12% and 30% of the height: the green ridges of the Dieng plateau around a small lake far away, smooth rounded hills.
Everything below 30% of the height: calm, steep terraced vegetable fields of the plateau in gentle shade, as if under a soft passing cloud, low in contrast and low in detail, getting gently darker towards the bottom, so that cream text and gold lines on top read clearly without a box. No bright spots, no strong edges, no small busy detail below 30%. The left and right thirds below 30% are the calmest.
Accuracy — No steam, smoke or craters, no temples, no people, animals, signs, screens, phones, mist, light rays or water reflections in the lower part.

DELIVERY
Target canvas 1920 × 1080 pixels; aspect 16:9. Opaque, full-bleed. Deliver WebP, named bg.wonosobo.skenario.webp.

CONSTRAINTS
No watermarks, signatures, text, letters, numbers, logos, frames, neon colours, clutter, blurry edges. No painterly 2D, gouache or paper grain.
```

**Daftar periksa hasil `SD-`.** Bagian antarmuka: satu keluarga dengan `reference.27`; kanvas tepat; tanpa tulisan, angka, centang, atau simbol selain angka 1 dan 2 di tab; bidang tempat tulisan tenang (perkecil ke ukuran tampil dan taruh label terpanjang di atasnya); tab aktif dan tidak aktif, dan jawaban dipilih, terbedakan dalam hitam-putih; tanpa merah dan hijau; pasangan tab (`SD-04`/`SD-05`, `SD-06`/`SD-07`) berimpit tepat bila ditukar. Latar: langit atas terang; di bawah 30% tinggi tenang, agak gelap, tanpa titik terang; uji dengan menaruh tangkapan mockup di atasnya (teks krem 20 px terbaca tanpa kotak); tanpa orang, layar, papan, atau kabut.
