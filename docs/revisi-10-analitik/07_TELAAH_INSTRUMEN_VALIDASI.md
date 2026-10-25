# Telaah Instrumen Validasi Ahli

**Judul penelitian:** *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*

Per 25 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md) · Status: **diterima pemilik proyek (25 Oktober 2026)**; instrumen ditulis ulang setelah kisi-kisi 06 disetujui

Dokumen ini menelaah dua draf instrumen validasi ahli milik pemilik proyek, lalu mengusulkan perbaikannya. Perbaikan yang sudah pasti ditulis sebagai teks pengganti yang siap ditempel. Perbaikan yang masih bergantung pada keputusan atau kisi-kisi ditandai.

| Berkas | Versi | Isi |
| --- | --- | --- |
| `instrumen-validasi-ahli-gelita-r10.pdf` | 2.0 draf, 23 Oktober 2026, 41 halaman | Ahli materi (58 butir + lembar B.5–B.7) dan pengamat budaya (47 butir + matriks 44 objek) |
| `instrumen-validasi-ahli-media-gelita.pdf` | 1.0 draf, 23 Oktober 2026, 24 halaman | Ahli media (63 butir + uji 8 perangkat, catatan temuan, telaah 26 layar) |

Versi 1.0 instrumen ahli materi dan pengamat budaya (`instrumen-validasi-ahli-gelita.pdf`, 33 halaman) sudah digantikan r10 dan tidak ditelaah.

## Ringkasan

**Putusan: kedua instrumen layak dilanjutkan dan landasan teorinya kuat.** Rantai validitas Messick, Evidence-Centered Design, skala 1–4 tanpa titik tengah dengan TD, Aiken's V, I-CVI, k*, κ, aturan veto, dan matriks tindak lanjut sudah lengkap. Rumus dan contoh hitungnya kami periksa ulang dan semuanya benar: Aiken's V, k*, κ = 0,69 pada contoh D.4, batas interval rerata, dan PA Borich. Instrumen media juga menangkap hal yang sering terlewat, yaitu bahwa mutu antarmuka ikut menentukan bersih tidaknya data asesmen.

Ada **satu masalah desain** yang perlu dibetulkan sebelum instrumen dipakai, ditambah serangkaian penyesuaian:

1. **Bukti validitas isi per butir soal masih terlalu tipis** (lihat T1). Lembar B.5 hanya mewajibkan dua penilai untuk seluruh butir; ahli lain cukup menilai sampel. Akibatnya I-CVI kebanyakan butir dihitung dari dua orang. Itu di bawah syarat minimal tiga ahli (Lynn, 1986) yang dikutip instrumen ini sendiri.
2. **Isi r10 tertinggal dari keputusan 25 Oktober 2026**: dua variasi (a/b), model multidimensi *between-item*, D1 bertumpu pada literasi membaca dengan sub-aspek D1a/D1b, jumlah butir D1 tidak dibatasi 29, dan bacaan bergenre digital. Perlu juga koreksi nama area DigComp 3.0 dan angka "129 butir".
3. **Beberapa bagian bergantung pada keputusan yang belum diambil**: letak D3 (Cari Objek babak 2 atau tempat lain), kode BUD-*, skor 0–1–2 Skenario Digital, dan indikator sikap. Juga pada kisi-kisi yang sedang disusun di [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md). Bagian ini sebaiknya ditulis ulang sekali saja, setelah kisi-kisi disetujui.
4. **Ahli media belum tercantum** di daftar bidang validasi dokumen utama (daftar kerja no. 14). Usulan: tambahkan.

## Temuan lintas instrumen

### T1. Validitas isi per butir soal harus dinilai semua ahli materi

**Masalah.** Ada dua lapis penilaian di instrumen ini, dan keduanya berbeda fungsi:
- **Lembar penilaian** (aspek A–J, 58 butir) menilai mutu rancangan secara umum, misalnya "kunci jawaban dapat ditemukan dari bacaan".
- **Lembar B.5** menilai setiap butir soal.

Bukti validitas isi yang dibaca penguji dan reviewer jurnal adalah **I-CVI dan Aiken's V per butir soal**, ditambah S-CVI/Ave untuk tiap dimensi. Sekarang B.5 meminta dua penilai melabeli seluruh butir dan "ahli materi lain cukup menilai sampel", sehingga untuk sebagian besar butir hanya ada dua penilaian.

**Perbaikan.**
- Pisahkan B.5 menjadi dua lembar:
  - **B.5a Pelabelan buta.** Dua ahli, tanpa melihat label pengembang: dimensi, sub-aspek D1, level AKM, Bloom. Datanya untuk κ.
  - **B.5b Relevansi dan kejelasan per butir.** **Semua** ahli materi menilai **semua** butir, termasuk Skenario Digital dan butir D3. Datanya untuk I-CVI, Aiken's V, dan S-CVI/Ave per dimensi.
- Tetapkan aturan: setiap butir harus punya **minimal tiga penilaian non-TD**. Kalau kurang, peneliti menambah penilai untuk butir itu.
- Panel ahli materi idealnya **lima orang**. Dengan tiga orang, satu saja skor 2 sudah membuat I-CVI = 0,67 dan butir gagal kriteria Lynn. Dengan lima orang, I-CVI tetap harus 1,00 menurut Lynn (1986), tapi V Aiken lebih stabil dan TD satu orang tidak langsung merusak perhitungan.

### T2. Skala relevansi per butir perlu jangkar sendiri

**Masalah.** B.5 menyebut "Relevansi, kejelasan: skala 1–4 seperti Tabel A1". Tapi Tabel A1 berbunyi *sesuai/tidak sesuai*, padahal CVI menuntut penilaian *relevan/tidak relevan terhadap konstruk yang didefinisikan* (Lynn, 1986; Polit & Beck, 2006).

**Perbaikan.** Beri B.5b jangkar relevansi sendiri. Pakai jangkar yang sama dengan [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md), supaya telaah internal dan penilaian ahli bisa dibandingkan:

| Skor | Relevansi terhadap dimensi utama dan sub-aspeknya |
| --- | --- |
| 4 | Sangat relevan: tugas langsung menuntut kompetensi inti dimensi |
| 3 | Relevan: tugas menuntut proses yang menjadi dasar dimensi |
| 2 | Kurang relevan: butir bisa dijawab tanpa teks atau tanpa kompetensi itu (pengetahuan umum, norma, bentuk kata) |
| 1 | Tidak relevan untuk dimensi ini |

Tambahkan dua kolom cepat di B.5b, karena keduanya menangkap cacat yang paling sering ditemukan di telaah internal:
- **Bisa dijawab tanpa membaca teks?** (Y/T)
- **Jawaban bocor dari judul, gambar, atau butir lain?** (Y/T)

### T3. Level AKM adalah data ordinal: pakai κ berbobot

Level AKM (MI < II < ER) berurutan. Tidak sepakat antara MI dan ER jauh lebih serius daripada antara II dan ER. Untuk level AKM, laporkan **κ berbobot kuadratik** (Cohen, 1968) di samping κ biasa. Untuk dimensi dan kode kesalahan (nominal), tetap pakai κ biasa atau Fleiss' κ. Target 0,70 tetap berlaku.

### T4. TD dan kecocokan keahlian

Instrumen ahli materi mencakup empat bidang sekaligus: literasi membaca, literasi digital anak, bahasa anak, dan pengukuran. Dengan tiga ahli, hampir pasti banyak butir diisi TD, lalu n per butir turun di bawah tiga. Usulan:
- Tambahkan **matriks penugasan** di A.3: aspek mana yang wajib diisi oleh ahli bidang apa. Contohnya: aspek C dan I wajib diisi ahli pengukuran; aspek E dan F wajib diisi ahli literasi digital; aspek G wajib diisi ahli bahasa.
- Komposisi panel ahli materi yang disarankan (lima orang): 2 literasi membaca/bahasa Indonesia SD, 2 literasi digital anak, 1 evaluasi atau pengukuran.
- Sampai orangnya ditetapkan, validator memakai nama samaran (keputusan 25 Oktober 2026): Ahli Bahasa Anak 1 dan 2, Ahli Literasi Digital 1 dan 2, Ahli Pengukuran, ditambah Ahli Budaya Kedu (pengamat budaya) dan Ahli Media Pembelajaran. Di matriks penugasan: aspek C dan I wajib Ahli Pengukuran, aspek E dan F wajib Ahli Literasi Digital 1 dan 2, aspek G wajib Ahli Bahasa Anak 1 dan 2.

### T5. Beban kerja validator

B.5a (sekitar 100 butir atau lebih) dan B.7 (kode pada setiap pengecoh, ratusan baris) memakan waktu jauh lebih lama daripada "1–2 minggu" yang tersirat untuk mengisi lembar.
- Tulis perkiraan waktu di surat permohonan (misalnya 4–6 jam untuk B.5a, 2–3 jam untuk B.7).
- Izinkan pengisian dalam lembar kerja (spreadsheet) yang sudah berisi kode butir, bukan di kertas.
- Pertimbangkan honorarium yang sepadan; ini lazim dicantumkan di proposal.

### T6. Penelaah pendahuluan berbantuan AI

Pemilik proyek meminta Claude ikut "menjadi ahli". Aturan yang kami usulkan:
- **Claude bukan validator.** Skornya tidak ikut dihitung dalam Aiken's V, CVI, κ, atau ICC, dan tidak dilaporkan sebagai validasi ahli.
- **Claude tidak bisa melakukan pelabelan buta**, karena Claude sendiri ikut menyusun label pengembang (05, kisi-kisi). Labelnya pasti tidak buta.
- Yang bisa dan berguna dikerjakan Claude **sebelum** berkas dikirim ke ahli:
  - Menjalankan B.5b dan B.7 pada draf bank soal sebagai penyaringan awal, supaya cacat yang jelas (kunci ganda, jawaban bocor, butir tanpa teks) sudah dibetulkan dan waktu ahli dipakai untuk hal yang substansial.
  - Cek fakta untuk matriks C.5, dengan sumber.
  - Evaluasi heuristik dan uji perangkat pada instalasi uji untuk instrumen media (B.5–B.7). Claude bisa memainkan game di Chromium pada beberapa ukuran layar dan mencatat temuan dengan tangkapan layar.
- Di naskah penelitian, ini ditulis terbuka sebagai *telaah pendahuluan berbantuan AI oleh tim pengembang*, bagian dari tahap pengembangan, bukan bagian dari validasi ahli.

## Instrumen ahli materi dan pengamat budaya (r10): perubahan yang sudah pasti

Perubahan berikut mengikuti keputusan pemilik proyek per 25 Oktober 2026 dan temuan telaah butir. Kolom "Ganti dengan" siap ditempel.

| Lokasi | Sekarang | Ganti dengan |
| --- | --- | --- |
| Sampul, A.3, B.5, E.3 | "129 butir yang dinilai" | "semua butir yang dinilai (jumlahnya mengikuti kisi-kisi; Susun Gambar tidak termasuk)" |
| A.4 langkah 3, B.5 | Satu lembar pelabelan buta untuk dua penilai, ahli lain menilai sampel | B.5a pelabelan buta (dua ahli) dan B.5b relevansi dan kejelasan (semua ahli, semua butir; minimal tiga penilaian non-TD per butir). Lihat T1 |
| B.5, kolom "Dimensi" | "D1, D2, atau D3. Bila lebih dari satu, tulis yang utama lebih dulu (mis. D1/D3)" | "Satu dimensi utama saja: D1, D2, atau D3 (model multidimensi *between-item*). Untuk D1, tulis juga sub-aspeknya: D1a memahami informasi atau D1b menilai informasi dan sumber" |
| B.5, kolom relevansi | "Skala 1–4 seperti Tabel A1" | Jangkar relevansi T2, ditambah kolom "tanpa teks?" dan "jawaban bocor?" |
| Tabel B1, kolom "Area DigComp 3.0" | D1 "Literasi informasi dan data"; D2 "Keamanan; komunikasi dan kolaborasi"; D3 "Hak, pilihan, dan tanggung jawab digital" | D1 "1 *Information search, evaluation and management* (1.1, 1.2)"; D2 "4 *Safety, wellbeing and responsible use* (4.2, 4.3); 2.2 *Sharing*"; D3 "2 *Communication and collaboration* (2.2, 2.3, 2.5); 3.3 *Copyright and licences*". Nama lama D1 berasal dari DigComp 2.x; "hak, pilihan, dan tanggung jawab" bukan nama area di DigComp 3.0 |
| Tabel B1, baris D1 | "Menemukan, menafsirkan, dan menilai informasi dari teks dan gambar di layar" | "Bertumpu pada literasi membaca: menemukan, menafsirkan, dan menilai informasi dari teks dan gambar di layar, termasuk teks bergenre digital. Dua sub-aspek: D1a memahami informasi (menemukan informasi; interpretasi dan integrasi) dan D1b menilai informasi dan sumber (evaluasi dan refleksi)" |
| A.2.2, tabel ECD, "Tugas" | "15 tantangan, bacaan, gambar, 12 Skenario Digital, butir jangkar" | Tambahkan: "dua variasi (a/b) per tantangan; sebagian bacaan bergenre digital" |
| C4 (aspek C) | "Variasi a, b, dan c dalam satu tantangan setara ... Siswa mendapat satu variasi acak" | "Variasi a dan b dalam satu tantangan setara dalam panjang, genre teks, level AKM, sub-aspek D1, dan kesulitan. Setiap siswa mendapat satu variasi; variasi yang tidak setara merugikan separuh siswa" |
| Tabel B2 dan C1 | Satu level AKM per wilayah (Temanggung MI, Magelang II, Wonosobo ER) | "Proporsi butir level tinggi naik dari wilayah ke wilayah. Temanggung didominasi MI, Magelang II, Wonosobo memuat ER terbanyak." Alasan: telaah butir menemukan Isi Rumpang Wonosobo (kata penghubung) masuk II, bukan ER; klaim satu level per wilayah akan langsung terbantah oleh pelabelan buta |
| I6 | "Jumlah butir per dimensi dan per level AKM memadai" | Tambahkan petunjuk: "D1 tidak dibatasi 29 butir. Ingat bahwa butir dalam satu bacaan saling bergantung (testlet), jadi jumlah bacaan sama pentingnya dengan jumlah butir" |
| E.3, prasyarat | "Daftar 129 butir ... tanpa label" | "Daftar semua butir yang dinilai tanpa label dimensi, sub-aspek, level AKM, dan Bloom (untuk B.5a), dan daftar yang sama dengan definisi dimensi (untuk B.5b)" |
| E.3, keputusan terbuka | Persetujuan konstruk tiga dimensi | Hapus: sudah diputuskan. Ganti dengan "letak dan definisi akhir D3" (lihat bagian berikut) |
| Tabel C3 (cek fakta) | Lima fakta 23 Oktober | Tambahkan hasil cek 25 Oktober: relief Borobudur 2.672 panel, 1.460 bercerita (bacaan dikoreksi); Topeng Ireng muncul sekitar 1950-an di Desa Tuksongo; olahan uceng Temanggung terdaftar Indikasi Geografis; Mendut umumnya disebut yang tertua dari tiga candi |
| A6 pengamat budaya, petunjuk | Contoh Dieng Culture Festival | Tambahkan: "Kawah wisata seperti Sikidang dan kompleks Candi Arjuna ada di sisi Banjarnegara; pastikan latar yang disebut 'Wonosobo' benar-benar di Wonosobo" |
| Tabel A2.4 | κ ≥ 0,70 untuk label nominal | Tambahkan baris "κ berbobot kuadratik untuk level AKM (ordinal)" (T3) |
| A.3 | Komposisi panel ahli materi ≥ 3 | Lima orang disarankan, dengan matriks penugasan aspek (T4) |

### Butir baru yang perlu ditambahkan (ahli materi)

| Aspek | Butir usulan | Petunjuk telaah |
| --- | --- | --- |
| D (D1) | **D7** Teks bergenre digital (unggahan, pesan grup, papan informasi digital, hasil pencarian) otentik, sesuai usia, dan tidak meniru merek atau akun nyata | Anak harus mengenali bentuknya sebagai unggahan atau pesan, tapi platform dan akunnya fiktif |
| D (D1) | **D8** Butir D1b benar-benar menuntut penilaian sumber, fakta–pendapat, atau kabar yang bertentangan, bukan sekadar menemukan informasi di teks bergenre digital | Bentuk teks digital saja belum menjadikan butir D1b |
| H | **H5** Isi Rumpang: bank kata tidak membuat rumpang terakhir terjawab dengan sendirinya (pilihan tersisa terlalu sedikit) | Lihat temuan Temanggung di 05: tiga rumpang, empat kata |
| I | **I7** Variasi a dan b setara dalam genre teks dan sub-aspek D1 (misalnya keduanya memuat sumber digital di Benar atau Salah Wonosobo) | Variasi tanpa sumber digital membuat separuh siswa tidak mendapat butir D1b |

## Instrumen ahli materi dan pengamat budaya (r10): perubahan yang menunggu keputusan

Bagian-bagian ini sebaiknya ditulis ulang **sekali**, setelah pemilik proyek memutuskan dan kisi-kisi (06) disetujui.

| Keputusan atau masukan | Bagian instrumen yang terdampak | Arah perubahan bila usulan 05 disetujui |
| --- | --- | --- |
| Letak dan definisi D3 | Tabel B1 baris D3, C.2, aspek F pengamat budaya (F1–F7), butir F5 ahli materi | D3 = "Partisipasi dan Representasi Budaya Kedu di Ruang Digital". Aspek F pengamat budaya menilai tugas *Periksa Unggahan* (atribusi, representasi akurat, izin pelaku dan pemilik karya, menghargai tradisi). F2 lama (pengenalan ciri khas di Cari Objek) pindah ke aspek baru "Skala pendamping Pengetahuan Budaya Kedu" |
| Kode BUD-* dan label tunggal | Tabel B4, F5 ahli materi, C.2, C8–C9 pengamat budaya | INF-BUD hanya D1 dan skala pendamping; DIG-IZN dan DIG-ETK hanya D2; BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK untuk D3 |
| Skor tindakan Skenario Digital 0–1–2, alasan sebagai diagnosis | Aspek E, B.6, H4 | Tambahkan butir: "Pilihan 'kurang tepat' sungguh berada di antara 'tepat' dan 'berisiko', sehingga skor 0–1–2 masuk akal". Ubah E4: R-ATUR disebut "penalaran berbasis aturan" (wajar dan aman pada usia 10–11 tahun), bukan "kepatuhan semata". Tambahkan di B.6 kolom "Alasan netral terhadap tindakan (Y/T)": pilihan alasan masuk akal apa pun tindakan yang dipilih |
| Butir D3 baru | B.6 (telaah skenario), C.5 (matriks objek) | Tambahkan lembar telaah *Periksa Unggahan* (12 butir), dan baris matriks untuk setiap unggahan tiruan dan sanggar atau pelaku budaya yang disebut |
| Indikator sikap | G3 pengamat budaya | Diputuskan 25 Oktober 2026: dipakai sebagai skala pendamping sikap lewat Balai Refleksi. G3 menilai butir skala itu setelah disusun |
| Aturan kesempatan Skenario Digital | Aspek E, B.6 | Diputuskan 25 Oktober 2026: tiga kesempatan seperti tantangan lain; yang dinilai tindakan dan alasan pertama; "kurang tepat" memakai satu kesempatan |
| Butir jangkar, tes L6 | C5, aspek J ahli materi | Diisi TD sampai tersedia (sudah tertulis) |
| Jumlah butir, bacaan, dan pengecoh per wilayah | Tabel B2–B3, C3 ahli materi, B.6 baris | Disalin dari kisi-kisi 06 |

## Instrumen ahli media: telaah

**Putusan: layak dipakai dengan revisi kecil.** Struktur aspek A–I (Wahono, 2006; LORI), heuristik Nielsen ditambah heuristik permainan, skala keparahan 0–4, aturan veto untuk temuan berkode V dan R, dan uji delapan perangkat sudah tepat. Klaim tentang game kami cocokkan dengan dokumen sistem:
- target sentuh 48 dp (`docs/05_VIEW_UI.md`, `--tap`)
- layar "putar perangkatmu" (`components/rotate-gate`)
- mode paket dan Chromebook sesi tamu
- larangan memakai server produksi (sesuai aturan proyek)

Semuanya cocok.

**Perubahan:**

| Lokasi | Perubahan |
| --- | --- |
| Sampul, catatan status | Hapus "Ahli media belum tercantum ... tambahkan bila disetujui tim" setelah pemilik proyek menyetujui ahli media masuk daftar kerja no. 14 (usulan kami: setujui) |
| B.2, tabel perubahan Revisi 10 | Tambahkan baris: "Dua variasi (a/b) per tantangan"; "Bacaan bergenre digital (unggahan, pesan grup, papan informasi) di dalam tantangan"; dan, bila D3 disetujui, "Cari Objek babak 2 *Periksa Unggahan*: mengetuk bagian unggahan tiruan" |
| Aspek A, butir baru **A9** | "Teks bergenre digital (unggahan, pesan, papan informasi) terbaca jelas di ukuran layar sebenarnya, dikenali anak sebagai unggahan atau pesan, dan tidak memakai logo atau tampilan merek platform nyata." Petunjuk: tiruan merek nyata berisiko secara hukum dan bisa membingungkan anak |
| Aspek C, butir baru **C10** | "Target ketuk di *Periksa Unggahan* (nama akun, keterangan, komentar, tombol) cukup besar dan terpisah, sehingga ketukan salah mencerminkan pilihan, bukan ketangkasan." Tandai RANCANGAN REVISI 10 |
| F2 (pembaca layar) | Tambahkan petunjuk: "Untuk penelitian, catat bila pembaca layar membacakan bacaan soal; yang terukur bergeser dari membaca ke menyimak, sama seperti E5" |
| H4 | Tambahkan petunjuk: "Coba tekan Cek sebelum semua rumpang atau kartu terisi, dan tekan Cek dua kali dengan cepat: keduanya tidak boleh memakai kesempatan." Sistem sekarang sudah menolak Cek yang belum lengkap sebelum kesempatan dihitung (aturan `INCOMPLETE`) dan menolak kiriman ganda (aturan `ATTEMPT_CHANGED`); validator memastikan hal itu juga terasa jelas di layar |
| B.7 telaah per layar | Tambahkan baris "Cari Objek babak 2 *Periksa Unggahan*" bila D3 disetujui, dan "Bacaan bergenre digital" sebagai baris tersendiri |
| C.2 | Tambahkan aturan minimal tiga penilaian non-TD per butir. Butir teknis seperti H3 hanya diisi validator berlatar rekayasa perangkat lunak, jadi panel media perlu sedikitnya dua orang dengan latar itu, atau H3 diperiksa sebagai daftar periksa lulus/tidak, bukan skor 1–4 |

## Koreksi kecil

- r10 butir I5: di versi teks, ikon 💡 lepas dari kalimat "Bantuan (tombol )". Periksa di PDF sebelum dicetak.
- r10 Tabel A2.4 menulis kriteria I-CVI "3–5 ahli: I-CVI = 1,00". Itu benar menurut Lynn (1986), tapi akibatnya satu skor 2 dari satu ahli langsung menggugurkan butir. Tulis ini terang-terangan di D.6, supaya peneliti tidak kaget, dan supaya alasan memilih lima ahli (T1) jelas.
- Kepmendikdasmen Nomor 56 Tahun 2026 masih bertanda "teks resmi perlu dicek". Biarkan tanda itu sampai teksnya dibaca langsung.
- Modul literasi digital Komdigi masih "t.t.". Untuk definisi pilar, laporan *Status Literasi Digital di Indonesia 2022* (Kominfo & Katadata) sudah memuat definisi empat pilar dan indikatornya, dan bisa ditambahkan ke daftar pustaka.

## Langkah berikutnya

1. ~~Pemilik proyek memutuskan T1–T6 dan ahli media.~~ Diterima seluruhnya pada 25 Oktober 2026.
2. Setelah kisi-kisi 06 dan keputusan D3 selesai, instrumen r10 dan media ditulis ulang sekali sebagai versi 2.1 dan 1.1, dengan semua perubahan di dokumen ini.
3. Setelah draf bank soal baru ada, Claude menjalankan telaah pendahuluan (T6) sebelum berkas dikirim ke ahli.
4. Ahli media menilai dalam dua langkah (keputusan 25 Oktober 2026): di tahap 2, game sekarang ditambah mock-up layar baru; setelah tahap 3, telaah singkat layar jadi di instalasi uji sebelum uji coba. Instrumen media v1.1 perlu memuat bagian untuk mock-up.

## Rujukan tambahan

- Cohen, J. (1968). Weighted kappa: Nominal scale agreement with provision for scaled disagreement or partial credit. *Psychological Bulletin, 70*(4), 213–220.
- Kementerian Kominfo & Katadata Insight Center. (2022). *Status Literasi Digital di Indonesia 2022*: [PDF](https://cdn1.katadata.co.id/media/microsites/litdik/ReportSurveiStatusLiterasiDigitalIndonesia2022.pdf).
- Rujukan lain sama dengan daftar pustaka kedua instrumen dan [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md).
