# Mock-up Layar Baru untuk Ahli Media Langkah 1

Per 26 Oktober 2026 · Daftar kerja no. 20 di [01](01_REVISI_10_ANALITIK.md#peta-jalan-dan-daftar-kerja) · Disusun AI, disetujui pengembang 26 Oktober 2026; gambar skala sikap digambar ulang dari 17 di hari yang sama

Dokumen ini menjelaskan 27 gambar mock-up layar baru Revisi 10 di folder [`gambar/mockup/`](gambar/mockup/). Gambar ini bahan ahli media langkah 1 di tahap 2: ahli menilai game yang ada sekarang ditambah rancangan layar baru, karena sistem baru diubah di tahap 3, setelah gerbang G2 (keputusan 25 Oktober 2026). Langkah 2, telaah singkat layar jadi di instalasi uji sebelum uji coba, adalah daftar kerja no. 21 dan bagian gerbang G3.

Setiap gambar diberi label merah **"Mock-up — bukan tampilan akhir"** dan kodenya (`mk-…`). Instrumen media v1.1 ([07](07_TELAAH_INSTRUMEN_VALIDASI.md#langkah-berikutnya)) bisa menyusun bagian mock-up langsung dari tabel di bawah: satu baris per layar, dengan keadaan yang ditampilkan dan pertanyaan untuk ahli.

## Ringkasan

| Layar | Kode gambar | Jumlah | Dari |
| --- | --- | --- | --- |
| Skenario Digital, tantangan kelima | `mk-sd-01` … `mk-sd-05` | 5 | 01, 08 (`tmg-5-a02`), perubahan sistem no. 2 dan 18 |
| Cari Objek babak 2 *Periksa Unggahan* | `mk-pu-01` … `mk-pu-04` | 4 | 08 (`tmg-4-a01`, `tmg-4-a02`), perubahan sistem no. 17, 18, 20 |
| Token sesi ringan, layar di luar sesi, layar kecil, panel peneliti | `mk-tk-01` … `mk-tk-05` | 5 | 01 *Token sesi*, perubahan sistem no. 10, 11, 16 |
| Modul tes dan angket | `mk-mt-01` … `mk-mt-06` | 6 | 13, 14 (`tes-mb-A01`), 16 (ang-01–03, ang-21), perubahan sistem no. 22 |
| Skala sikap di Balai Refleksi, lalu kritik dan saran | `mk-ss-01` … `mk-ss-05` | 5 | [17](17_SKALA_SIKAP.md) (`skp-ct-01`, `skp-03`), perubahan sistem no. 21 |
| Cerita penghubung dan kartu misi tantangan kelima | `mk-cr-01`, `mk-cr-02` | 2 | 12 (`layar-temanggung-02`, kartu misi `tmg-5`) |

Ukuran gambar 27–111 KB (WebP, jumlah ± 1,8 MB). Semua layar game digambar di panggung 1280 × 720 (16:9), kecuali `mk-tk-04` (jendela ponsel 844 × 390) dan beberapa layar modul yang lebih tinggi karena halamannya bergulir.

## Cara mock-up dibuat

- **Tampilan game sungguhan** (keputusan pengembang, 26 Oktober 2026). Sumbernya halaman HTML statis di [`gambar/mockup/src/`](gambar/mockup/src/) yang memakai CSS, huruf, dan gambar UI game yang sebenarnya dari `public/assets/` dan `gelita-katalog/assets/`, dengan susunan markup yang sama dengan view game (`_frame`, `boleh`, `cari`, `login`, `mission-brief`, `reflection`). Bagian yang belum ada di game ditulis di `src/_mock.css`. HUD dan label mock-up dibuat `src/_mock.js`.
- **Isi kata demi kata dari dokumen sumber.** Satu butir nyata per layar diambil dari draf 08, 12, 13, 14, 16, dan 17 tanpa diubah. Teks yang belum ada di dokumen mana pun (pesan galat, pengingat waktu, dan sebagainya) ditandai *draf salinan* di bawah dan didaftar di [Celah yang ditemukan](#celah-yang-ditemukan-di-dokumen-lain).
- **Gambar ulang.** Jalankan dari akar repo: `node docs/revisi-10-analitik/gambar/mockup/src/render.mjs` (semua) atau dengan nama berkas, misalnya `… render.mjs mk-sd-01`. Skrip memakai Playwright Chromium yang sudah terpasang (jangan `playwright install`) dan Pillow untuk WebP. Sumber ini bisa dipakai lagi di tahap 3 sebagai rujukan tata letak.
- **Tanpa CDN.** Modul tes meniru gaya bawaan Bootstrap 5 dengan beberapa aturan di `src/_modul.css`. Repo belum punya salinan Bootstrap; perubahan sistem no. 22 menyimpannya lokal di `public/assets/vendor/` saat modul dibangun ([34 → Tampilan](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#71-tampilan)).
- **Bukan bagian app.** Tidak ada berkas di `app/` atau `public/` yang berubah.

## Keputusan yang dipakai di semua layar

| Keputusan | Asal | Terlihat di |
| --- | --- | --- |
| Jam tidak tampil selama studi | Perubahan sistem no. 16; [10](10_TELAAH_PENDAHULUAN_MEDIA.md) M-01 | Kepala tantangan `mk-sd-*`, `mk-pu-*` tanpa jam; modul tes tanpa jam (`mk-mt-*`) |
| Aturan narasi: D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk *Periksa Unggahan* hanya lewat tombol ▶, tidak otomatis, dan tercatat | Perubahan sistem no. 18; [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#narasi-tantangan-kelima-dan-daftar-rekaman) | Tombol *Dengarkan* di `mk-sd-*`, ▶ di bilah petunjuk `mk-pu-*`; modul tes tanpa tombol suara |
| Area ketuk tidak bersentuhan: tinggi minimal 48 dp, jarak minimal 24 dp | Perubahan sistem no. 17; [10](10_TELAAH_PENDAHULUAN_MEDIA.md#perubahan-media-yang-dibutuhkan-revisi-10) no. 3 | `mk-pu-02` (lapisan pemeriksaan); jarak terkecil di mock-up 24 dp, diperiksa dengan skrip |
| Tidak memakai ponsel untuk pengambilan data; sesi penelitian di layar kecil ditolak dengan pesan ramah | Perubahan sistem no. 16; keputusan media 25 Oktober 2026 | `mk-tk-04` |
| Platform fiktif *Kedugram*, tanpa logo merek nyata | [08](08_DRAF_BANK_SOAL.md#cara-membaca) | `mk-pu-*` (bilah aplikasi bertulisan "aplikasi tiruan") |
| Kode peserta `S03-A07`, tanpa nama lengkap | 01 *Kode peserta baru*; M-10 | `mk-tk-01`–`03`, `mk-mt-*` |

## Layar per layar

### 1. Skenario Digital (`mk-sd-01` … `mk-sd-05`)

**Tujuannya.** Tantangan kelima tiap wilayah (`tmg-5`, `mgl-5`, `wnb-5`), mengukur D2 lewat dua langkah: tindakan, lalu alasan. Menunjukkan perubahan sistem no. 2 (engine dua langkah, `reason_code`) dan no. 18 (narasi lewat ▶).

**Isi.** `tmg-5-a02` *Kata sandi* dari 08, kata demi kata: situasi, tiga tindakan, tiga alasan, dan umpan balik pilihan "kurang tepat". Kalimat tanya alasan tetap: "Yang paling penting kupikirkan di sini adalah …". Instruksi dari kolom instruksi 08. Urutan pilihan diacak per pengerjaan; di gambar, pilihan tepat sengaja tidak di urutan pertama.

**Tata letak.** Kerangka tantangan yang sama dengan Benar atau Salah (`challenge-boleh`): situasi di panel perkamen kiri (32, 140, 596 × 500), instruksi dan panel jawaban di kanan, tombol di bilah tantangan. Pilihan memakai papan biru yang sudah dipakai Isi Rumpang dan Benar atau Salah. Penanda langkah "1 Tindakan — 2 Alasan" di atas pilihan. Gambar situasi masih **gambar sementara** (tablet dengan gelembung pesan); 08 mencatat 24 ilustrasi skenario sebagai opsional, dibuat tim Katalog Aset tanpa layar sungguhan.

| Gambar | Keadaan |
| --- | --- |
| [`mk-sd-01`](gambar/mockup/mk-sd-01.webp) | Situasi tampil, belum ada pilihan; narasi situasi sedang diputar karena siswa menekan *Dengarkan* (tombol berubah jadi ⏸ "Sedang dibacakan…"). Tombol *Lanjut ke alasan* belum aktif |
| [`mk-sd-02`](gambar/mockup/mk-sd-02.webp) | Satu tindakan dipilih (bercahaya dengan tanda ●, tidak hanya warna); *Lanjut ke alasan* aktif |
| [`mk-sd-03`](gambar/mockup/mk-sd-03.webp) | Langkah 2: tindakan pilihan diulang kecil di atas ("Pilihanmu: …"), lalu tiga alasan; satu dipilih; tombol *Kirim jawaban* |
| [`mk-sd-04`](gambar/mockup/mk-sd-04.webp) | Umpan balik setelah pilihan "kurang tepat", kesempatan tersisa 2 dari 3. Tanpa kata "salah" atau "gagal", tanpa warna merah; tombol *Coba lagi* |
| [`mk-sd-05`](gambar/mockup/mk-sd-05.webp) | Kesempatan ketiga habis: tindakan yang aman ditampilkan dan dijelaskan, tombol *Lanjut* ke cerita berikutnya |

*Draf salinan* (belum ada di dokumen sumber): "Kamu masih punya 2 kesempatan. Pilih lagi tindakan dan alasanmu.", "Ini tindakan yang aman", "Kesempatanmu untuk cerita ini sudah habis. Ayo lanjut ke cerita berikutnya.", Penjelasan tindakan aman di `mk-sd-05` bukan draf: itu baris *Penjelasan setelah kesempatan habis* `tmg-5-a02` di 08, kata demi kata (celah no. 1).

**Yang dinilai ahli media.**
- Apakah dua langkah (tindakan, lalu alasan) jelas bagi anak kelas 5, dan langkah alasan tidak terasa seperti soal kedua yang membingungkan?
- Apakah tidak ada isyarat visual pada pilihan tepat (posisi, warna, panjang teks), dan pilihan terpilih tetap terlihat tanpa bergantung pada warna?
- Apakah umpan balik "kurang tepat" ramah, tidak menghukum, dan tetap mendorong mencoba lagi?
- Apakah tombol *Dengarkan* mudah ditemukan, dan jelas bahwa situasi tidak dibacakan otomatis?
- Apakah teks situasi dan pilihan terbaca nyaman di panggung 16:9 pada perangkat sekolah (laptop, Chromebook, tablet)?

### 2. Cari Objek babak 2 *Periksa Unggahan* (`mk-pu-01` … `mk-pu-04`)

**Tujuannya.** Babak 2 Cari Objek mengukur D3: siswa mengetuk bagian unggahan tiruan di aplikasi fiktif *Kedugram* (atribusi, representasi, izin, menghargai tradisi). Menunjukkan perubahan sistem no. 17 (area ketuk tidak bersentuhan), no. 18 (petunjuk lewat ▶), dan no. 20 (Cari Objek dua babak).

**Isi.** `tmg-4` variasi a *Unggahan foto kuda lumping* dari 08: delapan bagian ketuk A1–A8 dengan isinya kata demi kata, petunjuk `tmg-4-a01` (atribusi) dan `tmg-4-a02` (representasi). Instruksi dari 08. Foto unggahan memakai gambar Susun Gambar `tmg-1-04`, seperti saran 08 di *Gambar yang dibutuhkan*. Mbah Kedu berbicara dari potret bundarnya, sama seperti Cari Objek sekarang.

**Tata letak.** Kerangka Cari Objek sekarang: bilah petunjuk (32, 140, 1216 × 60) dengan potret Mbah Kedu, penanda "Petunjuk n dari 4", kesempatan per petunjuk, dan ▶; daftar petunjuk di kiri; adegan 864 × 486 di tengah; instruksi di kanan. Adegannya bukan gambar, tetapi tampilan teks (kartu unggahan, komentar, papan informasi), seperti aturan 08 bahwa teks digital dibangun sebagai tampilan supaya bisa diperbesar. Setiap bagian ketuk berupa blok utuh: nama akun, tulisan pembuat foto di pojok foto, keterangan, dua tombol, dua komentar, dan papan informasi pameran.

| Gambar | Keadaan |
| --- | --- |
| [`mk-pu-01`](gambar/mockup/mk-pu-01.webp) | Petunjuk 1 tampil, belum ada ketukan, tiga kesempatan |
| [`mk-pu-02`](gambar/mockup/mk-pu-02.webp) | Sama dengan `mk-pu-01`, ditambah **lapisan pemeriksaan untuk ahli** (tidak tampil ke siswa): garis putus-putus dan kode A1–A8 di setiap area ketuk. Tinggi tiap area ≥ 48 dp, jarak terkecil antararea 24 dp. Foto dan judul "Komentar" bukan area ketuk |
| [`mk-pu-03`](gambar/mockup/mk-pu-03.webp) | Siswa mengetuk nama akun (A1, pengecoh utama, BUD-ATR): bingkai jingga dan tanda ✗ di bagian itu, pesan ramah, kesempatan untuk petunjuk ini tinggal 2 |
| [`mk-pu-04`](gambar/mockup/mk-pu-04.webp) | A2 ditemukan (bingkai hijau dan ✓), petunjuk 1 bertanda selesai di daftar, petunjuk 2 tampil |

*Draf salinan:* pesan ketukan keliru "Bukan bagian ini. Bagian itu bukan yang dimaksud petunjuk. Baca lagi petunjuknya, ya." Pesan Cari Objek sekarang ("Benda itu bukan yang dimaksud petunjuk…") menyebut *benda*, jadi babak 2 butuh kalimat sendiri (celah no. 2). Ketukan di tempat kosong dekat kunci (NON-SEN) tidak memakai kesempatan dan tidak memunculkan pesan ini.

**Yang dinilai ahli media.**
- Apakah unggahan dikenali anak sebagai unggahan media sosial, tanpa meniru merek nyata (butir A9 usulan di 07)?
- Apakah area ketuk cukup besar dan terpisah, sehingga ketukan keliru mencerminkan pilihan, bukan ketangkasan (butir C10 usulan di 07)? Gambar `mk-pu-02` menunjukkan ukurannya dan ikut dikirim ke ahli (keputusan 26 Oktober 2026).
- Apakah teks unggahan, komentar, dan papan informasi terbaca tanpa perbesar adegan? (Tombol perbesar adegan tetap ada di layar jadi.)
- Apakah tanda ketukan keliru dan benar jelas tanpa bergantung pada warna saja (✗ dan ✓ ikut tampil)?
- Apakah kartu unggahan putih terasa terlalu kontras atau lepas dari gaya game, atau justru membantu anak mengenali "layar di dalam game"?

### 3. Token sesi dan layar penelitian (`mk-tk-01` … `mk-tk-05`)

**Tujuannya.** Token 6 huruf dan angka membuka satu sesi lapangan dan wilayah atau modul tes pertemuan itu; di luar sesi seluruh game tertutup sampai studi selesai (01 *Token sesi*, perubahan sistem no. 11). Siswa mengetik token sekali per sesi setelah masuk atau saat daftar; P1 membuat sesi di panel peneliti (perubahan sistem no. 10 dan 11). Sesi penelitian di layar kecil ditolak (no. 16).

**Tata letak.** Layar siswa memakai halaman Masuk sekarang (`logo-centre`, Jaka melambai, panel kanan). Token diketik di enam kotak besar berhuruf mono, supaya huruf yang mirip mudah dibedakan. Kode penelitian siswa tampil di atas kotak.

| Gambar | Keadaan |
| --- | --- |
| [`mk-tk-01`](gambar/mockup/mk-tk-01.webp) | Siswa sudah masuk dan sedang mengetik token (4 dari 6 huruf dan angka), tombol *Mulai*, tombol *Keluar* di pojok |
| [`mk-tk-02`](gambar/mockup/mk-tk-02.webp) | Token belum cocok: kotak berbingkai jingga dan pesan ramah di atasnya |
| [`mk-tk-03`](gambar/mockup/mk-tk-03.webp) | Di luar sesi: permainan tertutup, dengan kalimat dari 01 kata demi kata: "Permainan dibuka waktu sesi bersama kakak peneliti, ya." |
| [`mk-tk-04`](gambar/mockup/mk-tk-04.webp) | Sesi penelitian dibuka di layar ponsel (844 × 390): pesan ramah bahwa layarnya terlalu kecil |
| [`mk-tk-05`](gambar/mockup/mk-tk-05.webp) | Panel peneliti (tampilan panel admin, bukan game): formulir sesi baru (sekolah, rombel, pertemuan, tanggal, jam buka–tutup) dan sesi berjalan dengan token `KD7RMA` dari 01, jumlah siswa yang sudah masuk, *Buat token baru*, dan *Tutup sesi sekarang*. Tanggalnya contoh |

*Draf salinan:* judul "Token Sesi", petunjuk "Ketik enam huruf token yang ditulis kakak peneliti di papan tulis, ya.", galat "Token belum cocok. Periksa lagi huruf di papan tulis, ya. Kalau masih belum bisa, angkat tangan.", judul "Sampai jumpa di sesi berikutnya!", pesan layar kecil "Sesi penelitian perlu layar yang lebih besar. Pakai tablet atau laptop yang sudah disiapkan kakak peneliti, ya.", dan semua teks panel peneliti. Pesan untuk token yang sudah lewat jamnya belum digambar; usulannya "Sesi ini sudah ditutup. Tanyakan token baru ke kakak peneliti, ya." (celah no. 4).

*Teks awal setelah dokumen implementasi* (sesi koordinator, 30 Oktober 2026, dari [28 → Layar dan teks](28_IMPLEMENTASI_SESI_LAPANGAN.md#16-layar-dan-teks); `asal: pengembang`, `draf pengembang`). Token terdiri dari huruf dan angka (tanpa O, 0, I, 1, L), jadi petunjuknya menjadi "Ketik enam huruf dan angka token yang ditulis kakak peneliti di papan tulis, ya." Tiga pesan penolakan ditambahkan:
- token sesi yang belum dibuka (`tokenNotOpenYet`): "Sesi ini belum dibuka. Tunggu aba-aba kakak peneliti, ya.";
- token kelas lain (`tokenOtherClass`): "Token ini untuk kelas lain. Periksa lagi token di papan tulis kelasmu, ya.";
- token susulan untuk siswa yang bukan anggotanya (`tokenNotMember`): "Token ini untuk sesi susulan teman lain. Tanyakan ke kakak peneliti, ya."

Pesan keempat ditambahkan 3 November 2026 (sesi tindak lanjut dokumen, dari [28](28_IMPLEMENTASI_SESI_LAPANGAN.md) K20; `asal: pengembang`, `draf pengembang`). Satu akun hanya ikut satu studi, jadi token menolak akun yang sudah punya sesi game di studi lain:
- akun yang sudah ikut kegiatan lain (`tokenOtherStudy`): "Akun ini sudah dipakai di kegiatan lain. Panggil kakak peneliti, ya."

Pesan ini belum digambar di mock-up dan tidak mengubah gambar yang sudah ada.

Gambar `mk-tk-01` dan `mk-tk-02` digambar ulang dengan petunjuk baru atas permintaan pengembang (30 Oktober 2026, `asal: pengembang`), sebelum paket ahli media dikirim; galat di `mk-tk-02` tetap mengikuti `tokenUnknown` di 28. Kalimat akhir semua pesan token ditetapkan di 28 setelah telaah ahli media dan uji keterbacaan.

**Yang dinilai ahli media.**
- Apakah layar token mudah dipahami tanpa penjelasan panjang, dan kotak huruf cukup besar untuk anak?
- Apakah papan ketik sentuh di tablet tidak menutupi kotak token (keputusan media no. 7 di 10)? Mock-up belum menggambar papan ketik; ini diperiksa di layar jadi (langkah 2).
- Apakah pesan galat, di luar sesi, dan layar kecil ramah, tidak menyalahkan anak, dan memberi tahu apa yang harus dilakukan?
- Panel peneliti (`mk-tk-05`) hanya untuk peneliti, jadi aspek game tidak berlaku. Ahli cukup menilai kejelasan alur dan keterbacaan token yang akan disalin ke papan tulis.

### 4. Modul tes dan angket (`mk-mt-01` … `mk-mt-06`)

**Tujuannya.** Tes awal–akhir dan angket akses digital dikerjakan di modul GELITA dengan tampilan Bootstrap sederhana, **sengaja tidak seperti game**: latar polos, huruf besar, tanpa HUD, musik, narasi, animasi, bintang, atau Serpihan Cahaya ([13](13_TES_AWAL_AKHIR.md#modul-tes-di-gelita), perubahan sistem no. 22). Jam tidak tampil; lima menit sebelum batas tersembunyi muncul pengingat ramah. Butir risiko daring hanya di P5, hanya untuk siswa yang izin orang tuanya mencakup butir itu ([16](16_ANGKET_AKSES_DIGITAL.md)).

**Isi.** Petunjuk layar pembuka dari 13 kata demi kata; teks `tes-mb-A-M1` *Mengenal Randai* dan butir `tes-mb-A01` dari 14 (urutan pilihan tetap A–D); angket bagian A (ang-01 sampai ang-03) dengan satu penjelasan baku dari 16; pengantar dan butir ang-21 dari 16.

| Gambar | Keadaan |
| --- | --- |
| [`mk-mt-01`](gambar/mockup/mk-mt-01.webp) | Layar pembuka tes dengan petunjuk 1–6 dan kode penelitian di bilah atas |
| [`mk-mt-02`](gambar/mockup/mk-mt-02.webp) | Teks di kiri (bernomor paragraf, bergulir di dalam kartunya), soal di kanan, satu jawaban dipilih, daftar nomor soal 1–16 dengan tanda "belum dijawab", tombol *Sebelumnya* dan *Berikutnya* |
| [`mk-mt-03`](gambar/mockup/mk-mt-03.webp) | Pengingat lima menit sebelum batas, tanpa jam dan tanpa hitung mundur; dua soal masih bertanda |
| [`mk-mt-04`](gambar/mockup/mk-mt-04.webp) | Periksa sebelum mengirim Bagian 1: nomor yang belum dijawab disebut, *Kembali ke soal* atau *Kirim Bagian 1*, dengan peringatan bahwa bagian itu tertutup setelah dikirim |
| [`mk-mt-05`](gambar/mockup/mk-mt-05.webp) | Angket: satu bagian (A) per layar, penanda "Bagian 1 dari 8", pilihan tunggal dan kotak centang besar, penjelasan baku kecil di bawah pertanyaan |
| [`mk-mt-06`](gambar/mockup/mk-mt-06.webp) | P5, butir risiko daring ang-21 dengan pilihan "Tidak mau menjawab"; catatan di bawah bahwa layar ini hanya untuk siswa dengan izin terpisah |

*Draf salinan:* pengingat "Waktunya hampir selesai. Periksa lagi jawabanmu, terutama soal yang masih bertanda, lalu kirim bagian ini.", layar periksa sebelum mengirim, label "sudah dijawab"/"belum dijawab", "Pertanyaan 1 dari 3", dan bilah atas "Tes akhir · pertanyaan tambahan" (celah no. 3).

**Aspek game di instrumen media tidak berlaku untuk modul ini** ([07](07_TELAAH_INSTRUMEN_VALIDASI.md#langkah-berikutnya), poin 4): tidak dinilai daya tarik visual, karakter, cerita, umpan balik permainan, atau musik. Yang tetap berlaku: keterbacaan, navigasi, ukuran target sentuh, kontras, dan aksesibilitas.

**Yang dinilai ahli media.**
- Apakah tampilan polos ini jelas berbeda dari game, sehingga anak tahu sedang mengerjakan tes, tanpa terasa menakutkan?
- Apakah ukuran huruf, panjang baris, dan teks yang bergulir di dalam kartu nyaman untuk membaca 150–200 kata di laptop dan tablet sekolah?
- Apakah daftar nomor soal dan tanda "belum dijawab" mudah dipahami, dan tanda itu tidak bergantung pada warna saja?
- Apakah pengingat waktu tanpa jam terasa menenangkan, bukan membuat panik?
- Apakah layar butir risiko daring cukup privat (tanpa warna mencolok atau ilustrasi yang menarik perhatian teman sebelah) dan pilihan "Tidak mau menjawab" mudah ditemukan?

### 5. Skala sikap di Balai Refleksi (`mk-ss-01` … `mk-ss-05`)

**Tujuannya.** Skala pendamping sikap menjaga warisan diisi sekali di P5, di Balai Refleksi, sebelum kritik dan saran, di luar skor literasi digital (perubahan sistem no. 21). Format dan tampilannya mengikuti keputusan pengembang di [17](17_SKALA_SIKAP.md#format-jawaban-dan-tampilan): empat pilihan tanpa titik tengah (*Tidak setuju · Kurang setuju · Setuju · Sangat setuju*), kata dengan titik emas yang makin besar, tanpa wajah; satu pernyataan per layar; urutan tetap; boleh kembali dan melewati sebelum dikirim; bilah kemajuan perunggu; tidak dibacakan.

**Isi.** Petunjuk layar pertama, contoh soal `skp-ct-01`, dan butir `skp-03` dari 17 kata demi kata. `skp-03` dipilih karena butir negatif (dibalik): di sinilah format tanpa wajah diuji, karena wajah senang di *Setuju* akan memberi isyarat yang keliru.

**Tata letak.** Tampilan game (bukan Bootstrap seperti modul tes), di latar Balai Refleksi, dalam satu kartu biru tua di tengah panggung. Pilihan memakai titik emas berukuran naik di atas katanya; pilihan terpilih bercahaya dan bertanda ●, jadi tidak bergantung pada warna saja. Mbah Kedu dan kalimat pujiannya (`Game.reflectionLead`) **tidak** tampil di layar skala; menurut 17, pujian itu baru tampil setelah skala dikirim. Tidak ada bintang, serpihan, atau umpan balik setelah menjawab.

| Gambar | Keadaan |
| --- | --- |
| [`mk-ss-01`](gambar/mockup/mk-ss-01.webp) | Petunjuk layar pertama (judul "Pendapatmu tentang budaya Kedu" dan tiga paragraf dari 17), tombol *Coba contoh* |
| [`mk-ss-02`](gambar/mockup/mk-ss-02.webp) | Contoh soal "Aku suka makan buah." bertanda *Contoh*, tanpa bilah kemajuan; *Setuju* dipilih; *Lewati* dan *Lanjut* |
| [`mk-ss-03`](gambar/mockup/mk-ss-03.webp) | Pernyataan 3/12 "Belajar tentang candi dan tari itu membosankan." dengan *Kurang setuju* dipilih, bilah kemajuan 3/12, *Kembali*, *Lewati*, *Lanjut* |
| [`mk-ss-04`](gambar/mockup/mk-ss-04.webp) | Layar terakhir: berapa pernyataan sudah dijawab (11 dari 12, satu dilewati), *Kembali* atau *Kirim* |
| [`mk-ss-05`](gambar/mockup/mk-ss-05.webp) | Balai Refleksi setelah skala dikirim (ditambahkan 26 Oktober 2026): kalimat pujian Mbah Kedu baru tampil di sini; ringkasan perjalanan tanpa ubin *Total waktu* (jam disembunyikan selama studi, perubahan sistem no. 16) dan bintang per wilayah dari 15; kritik dan saran dengan bintang 1–5 (data kepraktisan, `feedback_rating`) dan empat pertanyaan terbuka yang semuanya boleh kosong |

*Draf salinan:* pesan "Pendapatmu tentang budaya Kedu sudah terkirim. Terima kasih!" dan petunjuk "Semua pertanyaan boleh dikosongkan." di `mk-ss-05` (menggantikan "Isi minimal dua pertanyaan, ya."); tombol *Coba contoh*, label *Contoh*, dan teks layar kirim ("Kamu sudah menjawab 11 dari 12 kalimat. Satu kalimat kamu lewati. Kalau mau, kamu masih bisa kembali dan menjawabnya. Setelah dikirim, jawabanmu tidak bisa diganti lagi."). 17 hanya menyebut bahwa layar terakhir menunjukkan berapa pernyataan sudah dijawab, lalu *Kirim*.

**Yang dinilai ahli media.**
- Apakah titik yang makin besar dengan kata di bawahnya mudah dipahami anak sebagai "makin setuju", tanpa wajah?
- Apakah empat pilihan cukup besar dan terpisah untuk diketuk, dan pilihan terpilih jelas tanpa bergantung pada warna?
- Apakah *Lewati* cukup terlihat (sesuai prinsip etik) tanpa mengundang siswa melewati semua pernyataan?
- Apakah layar ini terasa bagian dari Balai Refleksi, dan tidak terasa seperti tes?
- Apakah pernyataan berukuran besar terbaca nyaman, termasuk pernyataan terpanjang (skp-11, sepuluh kata)?
- Di `mk-ss-05`: apakah jelas bagi anak bahwa kritik dan saran boleh dikosongkan, tanpa membuatnya terasa tidak penting?

### 6. Cerita penghubung dan kartu misi tantangan kelima (`mk-cr-01`, `mk-cr-02`, pelengkap)

**Tujuannya.** Membantu ahli melihat bagaimana tantangan kelima diperkenalkan: cerita penghubung *Kabut di Layar* (konteks `scenario_open`) diputar sekali saat tantangan 5 pertama dibuka, lalu kartu misi ([12](12_CERITA_PENGHUBUNG_DAN_NARASI.md)).

| Gambar | Isi |
| --- | --- |
| [`mk-cr-01`](gambar/mockup/mk-cr-01.webp) | Baris `layar-temanggung-02` (Mbah Kedu, pose `worried`, efek `fog`) kata demi kata, dengan papan nama, kontrol narator, dan kabut tipis. Tata letaknya disederhanakan dari layar cerita sinematik sekarang |
| [`mk-cr-02`](gambar/mockup/mk-cr-02.webp) | Kartu misi `tmg-5` kata demi kata: judul, cerita kartu misi, instruksi, segel 5/5, tombol *Mulai tantangan* |

**Yang dinilai ahli media.** Apakah cerita penghubung dan kartu misi membuat peralihan ke tantangan bertema layar terasa wajar dalam cerita, dan apakah segel 5/5 jelas menandai tantangan baru.

## Untuk instrumen media v1.1

Usulan bagian mock-up di instrumen media v1.1 (ditulis di 07 → *Langkah berikutnya*, bukan di dokumen ini):

1. Satu baris penilaian per layar di atas (enam layar), dengan gambar yang disebut dan pertanyaan *Yang dinilai ahli media* sebagai petunjuk butir. Layar 5 memakai format jawaban dari 17.
2. Butir A9 dan C10 usulan 07 dinilai pada `mk-pu-01`–`mk-pu-04`.
3. Untuk layar 4 (modul) dan `mk-tk-05` (panel peneliti), aspek game ditandai "tidak berlaku".
4. Catatan di sampul: gambar adalah mock-up statis, jadi gerak, suara, waktu muat, dan papan ketik sentuh dinilai di langkah 2 (daftar kerja no. 21) pada layar jadi.

**Selesai (26 Oktober 2026):** keempat poin masuk [Instrumen Validasi Ahli Media v1.1](dokumen-pdf/instrumen-validasi-ahli-media-v1.1.pdf), lembar B.8 *Telaah mock-up layar baru*. Layar 3 dipecah menjadi 3a (layar siswa) dan 3b (panel peneliti, aspek game tidak berlaku), dan ditambah baris 7 untuk bacaan bergenre digital. Selain A9 dan C10, butir H9 (tanpa jam selama studi) dan I6 (privasi layar butir risiko daring) juga dinilai dari mock-up.

## Celah yang ditemukan di dokumen lain

Ditemukan saat menggambar; tidak diperbaiki di sini karena berkasnya milik dokumen lain.

1. **Penjelasan setelah kesempatan Skenario Digital habis.** 01 menulis "tindakan yang aman ditampilkan dan dijelaskan", tetapi 08 hanya punya umpan balik per pilihan. Umpan balik pilihan tepat diawali "Tepat sekali." atau "Tepat.", yang janggal kalau siswa tidak memilihnya. **Diputuskan (26 Oktober 2026): satu kalimat penjelasan per skenario ditulis di 08** (24 kalimat, kolom atau baris baru), divalidasi bersama bank. **Selesai:** 24 baris *Penjelasan setelah kesempatan habis* ditulis di 08, dengan aturannya di *Cara membaca*, dan `mk-sd-05` digambar ulang.
2. **Pesan ketukan keliru di *Periksa Unggahan*.** Pesan Cari Objek sekarang menyebut *benda*. Babak 2 butuh kalimat sendiri; usulan di layar 2.
3. **Salinan modul tes.** 13 menetapkan pengingat lima menit dan daftar nomor soal, tetapi tidak menulis kalimatnya. Usulan di layar 4 perlu masuk 13 atau dokumen implementasi, lalu ikut uji keterbacaan; draf salinan layar baru diuji di [24](24_UJI_KETERBACAAN.md#bahan-yang-menunggu-uji-keterbacaan). **Dibawa (2 November 2026):** salinan layar 4 dan 5 masuk [34 → Teks antarmuka](34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#13-teks-antarmuka) sebagai teks awal, dengan pasangan bahasa Inggris; kalimat akhir tetap mengikuti uji keterbacaan.
4. **Salinan token dan layar penelitian.** 01 hanya menulis pesan di luar sesi. Pesan token keliru, token lewat jam, dan layar kecil belum ada; usulan di layar 3. **Dibawa (30 Oktober 2026):** pesan token masuk [28 → Layar dan teks](28_IMPLEMENTASI_SESI_LAPANGAN.md#16-layar-dan-teks), ditambah tiga pesan penolakan baru (layar 3). Pesan layar kecil tetap di perubahan no. 16.
5. **Urutan token saat daftar di P1.** 01 menulis "Siswa masuk atau daftar, lalu mengetik token", tetapi juga "Saat siswa mendaftar dengan token, sekolah dan kelasnya terisi sendiri". Artinya di P1 token diketik sebelum akun ada (di awal pendaftaran), sedangkan P2–P5 sesudah masuk. Mock-up menggambar yang kedua. Urutan di P1 perlu ditulis tegas di dokumen implementasi perubahan no. 11 dan 12. **Dijawab (30 Oktober 2026):** pendaftaran di P1 dimulai dengan token ([28 → Pendaftaran dengan token](28_IMPLEMENTASI_SESI_LAPANGAN.md#65-pendaftaran-dengan-token-p1)).
6. **Bantuan (💡) di Skenario Digital.** Tidak ada dokumen yang menyebut apakah tantangan kelima punya tombol Bantuan. Mock-up tidak menampilkannya.
7. **Penghitung ✓/✗ di kepala Skenario Digital.** Kerangka tantangan menghitung butir benar dan salah, tetapi di Skenario Digital belum ditetapkan apakah "kurang tepat" dihitung salah. Mock-up menghitung skenario yang sudah selesai dengan tepat.
8. **Kolom bintang per wilayah di Balai Refleksi terlalu sempit untuk 15 tantangan.** "13 dari 15" bertumpuk dengan bilahnya di lebar kolom sekarang (`.reflection-side .bar-row`, 5,75rem); `mk-ss-05` melebarkannya hanya di halaman mock-up. Perlu dicatat di dokumen implementasi perubahan sistem no. 2 (lima belas tantangan) atau no. 21.
9. **Tombol ▶ instruksi di kepala tantangan.** Sejak [32](32_IMPLEMENTASI_SKENARIO_DIGITAL.md) K10 (2 November 2026) narasi instruksi hanya diputar lewat tombol ▶ di kelima belas tantangan. Mock-up `mk-sd-*` dan `mk-pu-*` belum menggambar tombol itu; perlu ditambahkan bila ahli media menilainya. Catatan ini tidak mengubah keputusan yang sudah ada.

## Keputusan pengembang

Putaran 1 (26 Oktober 2026), sebelum menggambar:

1. **Tingkat kemiripan: tampilan game sungguhan**, memakai CSS, huruf, dan gambar UI game, bukan rangka abu-abu.
2. **Skenario Digital: lima keadaan** (situasi dengan ▶, tindakan dipilih, langkah alasan, umpan balik "kurang tepat" dengan kesempatan tersisa, kesempatan habis).
3. **Layar token: semua** (masukkan token, token keliru, di luar sesi, layar kecil, dan panel peneliti membuat sesi).
4. **Cerita penghubung dan kartu misi ikut digambar**, satu gambar masing-masing.

Putaran 2 (26 Oktober 2026), setelah melihat gambar:

1. **Gambar disetujui apa adanya** untuk paket ahli media langkah 1. `mk-ss-01` diperbarui setelah 17 final (dikerjakan hari yang sama: placeholder diganti empat gambar `mk-ss-01`–`04` dari 17).
2. **Penjelasan setelah kesempatan Skenario Digital habis: satu kalimat per skenario di 08**, bukan umpan balik pilihan tepat yang dipotong. Ke-24 kalimatnya sudah ditulis di 08, dan `mk-sd-05` sudah digambar ulang dengan kalimat `tmg-5-a02`.
3. **Lapisan pemeriksaan area ketuk (`mk-pu-02`) ikut dikirim ke ahli media**, bertanda jelas tidak tampil ke siswa.
4. **Draf salinan di dokumen ini diterima sebagai teks awal.** Kalimat akhirnya ditetapkan di dokumen implementasi, setelah telaah ahli media dan uji keterbacaan dengan anak ([24](24_UJI_KETERBACAAN.md#bahan-yang-menunggu-uji-keterbacaan), 27 Oktober 2026).
