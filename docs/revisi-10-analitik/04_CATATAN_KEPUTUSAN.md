# Catatan Keputusan Revisi 10 Analitik

Per 26 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Catatan ini merekam keputusan selama revisi, dari yang terbaru. Keputusan untuk implementasi nanti dicatat di dokumen implementasi dan di catatan keputusan perombakan kelas 5 saat pekerjaannya dimulai.

## 26 Oktober 2026

**Keputusan pemilik proyek: pertanyaan terbuka skala sikap** (sesi `r10-skala-sikap`, menjawab pertanyaan terbuka di [17](17_SKALA_SIKAP.md#pertanyaan-yang-masih-terbuka))

1. **Butir bahasa daerah (skp-10) adil** untuk siswa yang bahasa ibunya bukan bahasa Jawa, karena menyebut "bahasa daerah" umum. Butir tetap dipakai.
2. **skp-12 menyebut contoh acara setempat:** "Aku mau ikut acara budaya seperti merti desa dan kirab." Ahli Budaya Kedu memastikan keduanya dikenal di tiga wilayah.
3. **Bintang 1–5 di kritik dan saran menjadi data kepraktisan** (`feedback_rating`, masuk `kamus-1.1`).
4. **Siswa yang belum tuntas di akhir P5: data apa adanya.** Tidak ada jalur khusus ke Balai Refleksi; skalanya kosong dengan alasan `belum_tuntas`, dan data tantangannya dipakai seperti adanya. Susulan 7 hari hanya untuk siswa yang tidak hadir di P5.
5. **Korelasi sikap dengan PBK dan D3 diharapkan sedang: cukup kuat, tidak berlebihan** (usulan r ≈ 0,30–0,50; r ≥ 0,70 atau r < 0,10 dicatat sebagai temuan). Batas pastinya ditulis di pra-registrasi OSF di G4.

Akibatnya: [17](17_SKALA_SIKAP.md) diperbarui (butir, pelaksanaan, variabel `sikap_missing_reason` dan `feedback_rating`, akibat ke 01 dan dokumen pendukung peneliti).

**Keputusan pemilik proyek: skala sikap menjaga warisan** (sesi `r10-skala-sikap`, dua putaran wawancara; semua usulan disetujui)

1. **Format jawaban: empat pilihan tanpa titik tengah** (*Tidak setuju · Kurang setuju · Setuju · Sangat setuju*), kata dengan titik emas yang makin besar, tanpa wajah.
2. **12 butir, empat indikator × tiga:** menghargai (SK1), ingin tahu (SK2), mau ikut merawat (SK3), bangga (SK4). Satu butir per indikator berarah negatif dan dibalik skornya, ditulis tanpa "tidak" atau "bukan". Semua butir berlatar dunia nyata, tanpa ruang digital, supaya terpisah dari D3 dan PBK.
3. **Tidak dibacakan:** hanya di layar, tanpa rekaman. P3 boleh menjelaskan satu kata, dicatat di L3.
4. **Kritik dan saran yang ada sekarang tetap**, sesudah skala; selama penelitian semua isiannya boleh kosong.
5. **Penilai ahli:** Ahli Budaya Kedu, Ahli Pengukuran, Ahli Bahasa Anak 1 dan 2 menilai relevansi (I-CVI, aturan Lynn: keempatnya harus memberi 3 atau 4); Ahli Bahasa Anak juga menelaah bahasa; Ahli Literasi Digital 1 dan 2 hanya memberi catatan tumpang tindih D3.
6. **Tidak hadir di P5:** susulan paling lama 7 hari, ditandai; sesudahnya kosong, tanpa perkiraan.
7. **Tampilan:** satu pernyataan per layar, boleh kembali sebelum kirim, urutan tetap, bilah kemajuan.

Akibatnya: [17](17_SKALA_SIKAP.md) ditulis; kode skala masuk 03. Penyesuaian 01 (perubahan sistem no. 21), 02 (L1, L3, L7), 06 (baris P5), 07 (lembar penilaian v2.1), 11 (`kamus-1.1`), 12, 13 (waktu P5), dan mock-up 18 tercatat di [17](17_SKALA_SIKAP.md#akibat-ke-dokumen-lain).

**Keputusan pemilik proyek: lembar lapangan final, putaran 1** (sesi `r10-lembar-lapangan`, daftar kerja no. 11)

1. **Siswa tanpa izin orang tua tidak punya akun GELITA.** Mereka ikut kegiatan pengganti yang setara di ruang yang sama, disiapkan guru (misalnya membaca buku), jadi tidak ada data tentang mereka. Pilihan bermain lalu datanya dihapus, atau mode tamu tanpa penyimpanan, tidak dipakai.
2. **Di P1, baris dan kursi dimasukkan langsung setelah pendaftaran.** Siswa mendaftar lalu menunggu di layar pembuka modul tes. P1 mengetik kode peserta → baris dan kursi untuk seluruh kelas di panel peneliti (± 5 menit), bersama buklet. P3 mencocokkan kode dengan L2. Setelah itu tes awal dimulai bersama. Buklet tidak lagi dimasukkan "sesudah pertemuan P1".
3. **Wawancara retrospektif L5 di P2–P4, tiga siswa per pertemuan**, satu dari setiap kelompok nilai tes awal (rendah, sedang, tinggi). Setiap siswa diwawancarai paling banyak sekali, jadi ± 9 siswa per kelas. Wawancara di P5 tidak dijadwalkan.
4. **Siswa yang absen di pertemuan wilayah ikut sesi susulan dalam tiga hari**, seperti susulan tes. P1 membuka sesi lapangan susulan untuk wilayah itu dengan token sendiri (kode sesi berakhiran `-S`, misalnya `S03-A-P3-S`), diamati sedikitnya satu peneliti yang mengisi L1–L3. Sisa tantangan tetap dilanjutkan di susulan P5.

**Persetujuan:** pemilik proyek menyetujui lembar L0–L7 final di [02](02_LEMBAR_LAPANGAN.md) apa adanya, termasuk pilihan yang menyertainya:
- L3 juga dipakai saat tes dan angket, menggantikan kolom catatan L6 di 13.
- L4 hanya selama waktu bermain.
- Kode penyimpangan baru DEV-TIM (peneliti kurang dari tiga).
- L2 satu baris per siswa, dengan kolom baris dan kursi di baris.
- Kelompok nilai tes awal untuk L5 adalah sepertiga terbawah, tengah, dan teratas di rombel.
- Contoh tanggal fiktif, sesudah perkiraan persetujuan etik.

Akibatnya: 02 final; 03 mendapat kode sesi susulan, DEV-TIM, dan arti L5 yang baru. Sesudah persetujuan, 01 (*Kerja lapangan*, perubahan sistem no. 11 dan no. 22), 06 (pembagian pertemuan, buklet), dan 13 (waktu P1 menjadi ± 124 menit, urutan P5, bantuan saat tes di L3) disesuaikan. Urutan P5 di catatan keputusan 12 dibiarkan sebagai riwayat. Yang tersisa: 11 di `kamus-1.1`. Yang perlu disesuaikan di dokumen lain dicatat di [02](02_LEMBAR_LAPANGAN.md#perubahan-dari-draf).

**Keputusan pemilik proyek: label Bloom pengembang** (sesi `r10-label-bloom`, daftar kerja no. 4, telaah di [19](19_TELAAH_LABEL_BLOOM.md))

1. **Petunjuk Cari Objek diberi label menurut proses yang dilakukan siswa.** PBK: C1 kalau petunjuk menyebut nama bendanya (Temanggung), C2 kalau hanya mendeskripsikannya (Magelang, Wonosobo). Periksa Unggahan per jenis petunjuk: atribusi C2, izin C3, representasi C2 di Temanggung dan C4 di Magelang dan Wonosobo, menghargai tradisi C2 di Temanggung dan Magelang dan C4 di Wonosobo. Tidak ada C5 di Cari Objek.
2. **Benar atau Salah paling tinggi C4, kecuali dua butir C5.** Membandingkan isi dua teks dan menggolongkan fakta atau pendapat menjadi C2 (mengikuti Anderson & Krathwohl, bukan daftar kata kerja operasional yang menaruh "membedakan" di C4). Menilai kepercayaan sumber dari penanda yang diberikan, atau menolak alasan popularitas dan posisi, menjadi C4. C5 hanya untuk `wnb-3-a06` dan `wnb-3-b06`, yang memeriksa apakah simpulan didukung bukti.
3. **Skenario Digital diberi label per butir di 08.** Temanggung dan Magelang C3, Wonosobo C4, kecuali `wnb-5-a03` dan `wnb-5-b03` C5 (menimbang privasi teman melawan tekanan grup).
4. **Rentang Bloom wilayah direvisi, butir tidak ditulis ulang:** Temanggung C1–C3, Magelang C2–C4, Wonosobo C2–C5. Aturan no. 14 di 06 kini menyebut bahwa Bloom menggambarkan proses berpikir, bukan tingkat kesulitan; kesulitan ditunjukkan level AKM dan data Rasch.
5. **Lima perubahan label Isi Rumpang dipertahankan** (ditanyakan sesudah wawancara): `mgl-2-a04`, `mgl-2-b05`, dan `wnb-2-b05` menjadi C2 (perbandingan angka atau sebab–akibat langsung, sama dengan butir serupa di wilayah lain); `wnb-2-a04` dan `wnb-2-a05` menjadi C3 (menerapkan keterangan rentang ketinggian).
6. **`wnb-3-b02` ditulis ulang, level AKM tetap L2** (ditanyakan setelah PR #247 digabung). Jawabannya tersurat, jadi lebih dekat ke L1; daripada memasang L1 di Wonosobo, pernyataannya ditulis ulang di sesi bank berikutnya supaya perlu inferensi. Catatannya ada di 08 (*Yang harus dicek sebelum validasi ahli*).

Akibatnya: 39 dari 92 label lama di 08 berubah, dan 36 label baru ditulis (12 PBK, 24 skenario). Rentang per tantangan dan per wilayah di 06 serta tabel pelevelan wilayah di 01 disesuaikan. Ini label pengembang; label ahli dari pelabelan buta B.5a di tahap 2 menggantikannya.

**Keputusan pemilik proyek: penyelarasan dokumen bersama setelah keputusan tes** (sesi `pbb/magical-hamilton-4f6l98`, wawancara lanjutan)

1. **Variabel tes dan angket masuk kamus data di `kamus-1.1`, setelah uji coba.** `kamus-1.0` di [11](11_KAMUS_DATA.md) tidak diubah sekarang. Sampai saat itu, daftar variabelnya ada di [13](13_TES_AWAL_AKHIR.md#penskoran-dan-pemasukan-data) dan [16](16_ANGKET_AKSES_DIGITAL.md#variabel-untuk-kamus-data).
2. **Pertemuan wilayah (P2–P4) juga tidak terikat 2 JP: waktu bermain ± 90 menit.** Tantangan yang belum selesai tetap dilanjutkan di susulan P5 (paling lama 20 menit). Dengan perkiraan 29–52 menit per wilayah di [06](06_KISI_KISI_BANK_SOAL.md#waktu-bermain), hampir semua siswa selesai di pertemuannya.
3. **Butir risiko daring di P5 memakai izin orang tua terpisah.** Surat izin orang tua dan L7 punya centang sendiri untuk butir ini. Tanpa centang itu, siswa tidak melihat butir tersebut, tetapi tetap ikut kegiatan lain.

Akibatnya: 02 (L6 dan L7), 06 (waktu bermain dan pembagian pertemuan), 03 (kode tes dan angket), 07 (paket validasi), 13, dan 16 disesuaikan.

**Keputusan pemilik proyek: tes awal–akhir dan angket akses digital** (sesi `pbb/magical-hamilton-4f6l98`, menjawab empat pertanyaan di [13](13_TES_AWAL_AKHIR.md#keputusan-pemilik-proyek) dan satu putaran wawancara lanjutan)

1. **Lama tes mengikuti kebutuhan, tidak terikat jam pelajaran sekolah.** Penelitian sudah mendapat izin memakai seluruh JP, bahkan sampai setelah jam sekolah. Jadi lama P1 dan P5 mengikuti kebutuhan tes dan kegiatan lain; pilihan 3 JP dan 4 JP di 13 tidak dipakai lagi.
   - Setiap bagian tes diberi batas waktu longgar di atas perkiraan: Bagian 1 ± 40 menit, Bagian 2 dan 3 ± 35 menit. Jam tidak tampil di layar, sama seperti di game.
   - Waktu selesai setiap siswa dicatat, dan batas akhirnya ditetapkan dari uji coba.
2. **Media tes: modul tes di GELITA**, dengan tampilan Bootstrap yang sederhana dan tidak dibuat seperti game. Tes tidak memakai kertas, dan jawaban tidak diketik ulang. Modul ini menjadi perubahan sistem no. 22 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti) dan dibangun di tahap 3, setelah G2.
3. **Topik teks dan adegan tes: budaya Nusantara di luar Kedu**, seperti draf [14](14_TES_BAGIAN_MEMBACA.md) dan [15](15_TES_BAGIAN_DIGITAL.md).
4. **Angket akses digital masuk modul yang sama**, langsung setelah tes awal di P1. Pertanyaannya hanya tampil di layar dan tidak dibacakan koordinator sesi.
5. **Pengalaman risiko daring: 2–3 butir di angket, diisi di P5 setelah tes akhir**, sendiri di layar (ang-21 sampai ang-23 di [16](16_ANGKET_AKSES_DIGITAL.md)). Syaratnya: butir ini disebut di permohonan izin etik dan surat izin orang tua, dan ada prosedur rujukan tertulis untuk anak yang menjawab pernah mengalami hal yang mengganggu.

Akibatnya: 13, 14, 15, dan 16 disesuaikan dengan modul layar (petunjuk menjawab, waktu, pemasukan data otomatis, kode butir tetap). 02 (L6), 06 (isi P1 dan P5), 11 (variabel tes dan angket), 03 (pola kode), dan 07 (paket validasi) diselaraskan sesudahnya.

**Keputusan pemilik proyek: model penelitian dan pengembangan, dan aturan I-CVI** (sesi `sky/peaceful-curie-68ufr5`)

1. **Model penelitian dan pengembangan: penelitian desain pendidikan model Plomp (2013), jenis studi pengembangan.**
   - ECD menjadi metode merancang asesmen di dalamnya.
   - Lapis evaluasi formatif Tessmer (1993) dan tabel metode Nieveen & Folmer (2013) menentukan cara menguji setiap prototipe.
   - Empat kriteria mutu (relevansi, konsistensi, kepraktisan, keefektifan) menjadi ukuran mutu produk.
   - Peta jalan enam tahap dan lima gerbang di 01 tidak berubah. Tahap 1–4 adalah iterasi fase prototipe, dan tahap 5–6 adalah fase penilaian.
   - Alasannya:
     - GELITA adalah alat ukur, jadi keefektifan diartikan sebagai mutu pengukuran, bukan peningkatan hasil belajar.
     - Prototipe awal boleh berupa kertas, sesuai aturan "sistem diubah setelah G2".
     - Model ini menghasilkan prinsip desain sebagai sumbangan teoretis.
   - ADDIE, 4D, Borg & Gall, dan Sugiyono dibandingkan dan tidak dipilih.
   - Rinciannya, dengan setiap rujukan dicek ke sumber asli, ada di [dokumen pendukung peneliti](dokumen-pendukung-peneliti/README.md). Folder itu adalah bacaan untuk peneliti dan tim, bukan dokumen implementasi.
2. **Aturan I-CVI: aturan Lynn (1986) tetap dipakai.**
   - Dengan lima ahli atau kurang, butir hanya lolos kalau semua ahli memberi skor 3 atau 4 (I-CVI = 1,00).
   - Aturan Polit, Beck, & Owen (2007) yang lebih longgar tidak dipakai.
   - Dicatat di 07 (*Koreksi kecil*) dan ditulis di D.6 instrumen v2.1.
3. **Rujukan dibetulkan setelah dicek ke sumber asli:**
   - PIRLS 2026: editornya "Kennedy, A." (bukan "A. M."), ditambah DOI, di 01 dan 06.
   - Judul DigComp 3.0 tanpa "The", ditambah DOI, di 01, 05, dan 06.
   - Laporan Kominfo & Katadata terbit 2023, dengan istilah pilarnya sendiri (Kecakapan, Etika, Keamanan, Budaya Digital), di 05, 06, dan 07.
   - Webb (1999) ditambahkan di 01 sebagai sumber level DOK.
   - Judul Cohen (1968) dibetulkan di 07.
   - Judul "Rujukan metode (klasik, tidak dicek ulang)" di 01 diganti, karena semuanya sudah dicek.

Usulan dokumen itu yang belum diputuskan:
- uji beda tes awal–akhir dilaporkan sebagai temuan eksploratif, karena desain satu kelompok tanpa kontrol tidak bisa menunjukkan sebab-akibat;
- kepraktisan diukur dari log dan lembar L1, L3, dan L4, karena guru tidak ikut;
- penelitian pendahuluan ditulis sebagai fase tersendiri;
- prototipe diberi nomor I–III.

**Konfirmasi pemilik proyek: fakta dan gambar Susun Gambar** (sesi `sky/festive-hopper-cl6p65`)

- **Wayang Kedu Temanggungan** adalah varian tradisi wayang kulit asli dari wilayah Kedu, Jawa Tengah, yang berkembang khusus di Kabupaten Temanggung. Keterangan gambar baru Temanggung ditulis dari fakta ini. Kalimat tentang Warisan Budaya Takbenda dihapus dari draf, karena surat penetapannya belum ditemukan.
- **Topeng Lengger berasal dari Wonosobo**, jadi syarat Lengger sebagai variasi `b` Wonosobo terpenuhi. Bukit Sikunir tidak dipakai di pengerjaan pertama. Tahun penetapan 2020 di keterangannya tetap dicek peneliti.
- **Gambar baru Wayang Kedu dibuat tim katalog dan dicek ulang nanti.** Yang diperlukan sekarang hanya konsepnya, dan arahannya sudah ada di 08.
- **Gambar Candi Pawon diganti**, karena gambar sekarang menonjolkan stupa kecil di atap yang mirip sasaran PBK `mgl-4-x01`. Arahan gambar baru ada di 08, dan gambarnya dicek ulang setelah dibuat.

**Keputusan pemilik proyek: variasi Susun Gambar selama studi** (sesi `sky/festive-hopper-cl6p65`, dua pertanyaan di [08](08_DRAF_BANK_SOAL.md#keputusan-pemilik-proyek-26-oktober-2026) dan empat pertanyaan lanjutan)

1. **Cara memperbaiki: dua variasi bersih diacak**, bukan satu variasi tetap per wilayah (usulan awal). Kedua variasi menjadi `a` dan `b`, sesuai `FIRST_ATTEMPT_GROUPS` di 06, dan Susun Gambar tidak mengikuti buklet. Variasi lain hanya untuk pengerjaan ulang.
2. **Pasangan per wilayah:**
   - Temanggung: Pasar Papringan, ditambah gambar dan keterangan baru tentang **Wayang Kedu** (topik yang tidak dipakai butir mana pun). Menulis ulang `tmg-1-a` atau `tmg-1-e` tidak dipilih, dan Posong ditolak karena topik gunungnya sama dengan `tmg-2-a`.
   - Magelang: Candi Mendut dan Candi Pawon.
   - Wonosobo: Kompleks Candi Arjuna dan Tari Topeng Lengger. Lengger dipakai hanya kalau ahli budaya membenarkan asalnya dari Wonosobo; kalau tidak, pemilik proyek memilih ulang, dan Bukit Sikunir yang dipangkas menjadi satu-satunya sisa.
3. **Yang menyusul:** draf keterangan Wayang Kedu dan arahan gambar (usulan prompt TMG-P11) ada di 08, menunggu cek fakta dan gambar dari tim katalog. Fakta Papringan, Pawon, Arjuna, dan Lengger dicek peneliti sebelum validasi ahli.

Disesuaikan di dokumen lain: 06 (perubahan sistem no. 4, tabel kode, tabel "Yang harus ditulis") dan 01 (perubahan sistem no. 20). Rujukan "perubahan sistem no. 4 di 01" di entri sebelumnya keliru dan sudah dibetulkan: pemilihan variasi ada di no. 20 di 01, yang merujuk ke no. 4 di 06.
**Keputusan pemilik proyek: cerita penghubung dan narasi tantangan kelima** (sesi `sky/optimistic-bardeen-4zx8ob`)

Pemilik proyek menyetujui seluruh draf [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) beserta semua usulannya:

1. **Cerita penghubung diputar sekali**, saat tantangan kelima pertama kali dibuka, sebelum kartu misinya.
2. **Selama studi, Kenali wilayah baru terbuka setelah wilayahnya tuntas**, seperti Pustaka. Di luar studi tetap seperti sekarang. Masuk perubahan sistem no. 16 di 01.
3. **Kabar berantai Wonosobo menjadi ramalan gempa**, bukan kawah akan meletus.
4. **Di Skenario Digital hanya situasi yang dibacakan**, lewat tombol ▶.
5. **Urutan P5:** tantangan susulan, tes akhir, cerita penutup, skala sikap di Balai Refleksi, wawancara L5. Tes akhir kini sebelum cerita penutup.

Penyesuaian di dokumen lain: 06 (urutan P5; baris waktu "Cerita wilayah: pembuka, penghubung, tuntas" 4–5 menit, sehingga waktu bermain menjadi ± 29–36, 36–44, dan 43–52 menit), 02 (aturan tes akhir), 01 (daftar kerja no. 7 selesai; perubahan sistem no. 16), dan 00. Pemetaan narasi `petunjuk-*` ke butir `x01`–`x04` dibetulkan di tahap 3, karena pemetaan sekarang masih benar untuk bank yang terpasang.

Keterangan Susun Gambar diperiksa dan diputuskan di sesi lain (dua entri di atas). Karena asal Topeng Lengger dari Wonosobo sudah dikonfirmasi pemilik proyek, `kenal-wonosobo-04` di 12 kembali ke teks lama dan tetap menyebut Lengger; yang dibetulkan di Kenali tinggal jumlah relief yang bercerita (`kenal-magelang-02`). Naskah menjadi 21 baris baru dan 31 baris diubah. Pemetaan narasi `petunjuk-*` ke butir `x01`–`x04` dikerjakan di tahap 3 (12, daftar tahap 3 butir 3).

**Pemeriksaan keterangan Susun Gambar terhadap draf bank soal** (sesi `sky/festive-hopper-cl6p65`, menindaklanjuti *Di luar dokumen ini* di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#di-luar-dokumen-ini))

- Ketiga puluh keterangan Susun Gambar (`*-1`, variasi a–j) dicocokkan dengan semua butir D1, kunci D3, dan petunjuk PBK di [08](08_DRAF_BANK_SOAL.md#susun-gambar-selama-studi). Hasilnya: 22 keterangan bocor atau memberi isyarat. Kedua belas petunjuk PBK dan keenam butir representasi D3 masing-masing punya keterangan yang sepadan. Skenario D2 tidak terkena.
- **Koreksi atas 12:** variasi d–j hanya muncul di pengerjaan ulang, yang selama studi baru terbuka setelah wilayahnya tuntas. Jadi Situs Liyangan, Prasasti Gondosuli, dan Umbul Jumprit tidak bocor selama studi, dan tidak ada keterangan yang bocor ke wilayah sesudahnya. Yang berlaku adalah variasi a–c di pengerjaan pertama: 7 dari 9 bocor (`tmg-1-a`, `b`, `c`; `mgl-1-a`, `b`; `wnb-1-a`, `c`). Tiga gambarnya juga foto unggahan *Periksa Unggahan* di wilayah yang sama.
- **Usulan** (menunggu dua jawaban pemilik proyek di 08):
  - Selama studi, pengerjaan pertama Susun Gambar memakai satu variasi tetap per wilayah dari keterangan yang bersih: Pasar Papringan, Candi Mendut, dan Kompleks Candi Arjuna.
  - Ke-27 variasi lain tetap untuk pengerjaan ulang. Tidak ada keterangan atau gambar yang diubah.
  - Setelah diputuskan, pemilihan variasi pengerjaan pertama (perubahan sistem no. 20 di 01, no. 4 di daftar 06) dan kode Susun Gambar di 06 disesuaikan.
- Petunjuk PBK dihitung sebagai kebocoran, sejalan dengan usulan Kenali di 12 (pertanyaan 2).
- Dua fakta keterangan dicatat untuk peneliti: asal Topeng Lengger di `wnb-1-i` (sengaja tidak dipakai di 08), dan tahun Tambi di `wnb-1-h`.
- Pemetaan narasi `petunjuk-*` yang usang (temuan kedua di 12) belum dikerjakan di sesi ini.

**Draf cerita penghubung dan narasi tantangan kelima** (sesi `sky/optimistic-bardeen-4zx8ob`, daftar kerja no. 7)

- Cerita penghubung, kartu misi, baris lama yang berubah, dan daftar rekaman disusun di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md). Statusnya draf sampai pemilik proyek menjawab empat pertanyaan di bagian akhir dokumen itu:
  1. kapan cerita penghubung diputar
  2. Kenali wilayah selama studi (usulan: baru terbuka setelah wilayahnya tuntas, karena fakta budayanya adalah kunci butir *Periksa Unggahan* dan Temukan Benda)
  3. kabar berantai Wonosobo: tetap kawah atau ganti ramalan gempa
  4. yang dibacakan di Skenario Digital
- **Usulan yang ikut draf** (bisa dikoreksi pemilik proyek):
  - Serpihan kelima setiap wilayah disembunyikan Kabut Lupa di dalam layar (*Kabut di Layar*). Konteks dialog baru `scenario_open`, berkas `layar-{wilayah}-NN`, 6 baris per wilayah.
  - Sebelas aturan menulis cerita untuk penelitian. Intinya: cerita sebelum tantangan tidak menyebut jawaban, tindakan kunci, atau ciri jebakan, dan tidak mendorong ke arah sebaliknya; pelajaran datang di cerita tuntas dan hanya mengulang umpan balik; cerita tuntas tidak menyebut jumlah serpihan seluruhnya, karena ada siswa susulan.
  - Kode berkas baru `skenario-{kode butir}` (Narator) dan `unggahan-{kode butir}` (Mbah Kedu), dengan arahan suara datar supaya nada bicara tidak membocorkan jawaban.
  - Kartu misi tantangan kelima, dan tujuh kartu lama disesuaikan dengan bentuk teks bank baru, sama untuk variasi a dan b.
  - Naskah menjadi 112 baris per bahasa (21 baru, 32 diubah); dengan situasi, petunjuk *Periksa Unggahan*, dan instruksi, rekaman menjadi ± 187 per bahasa. Waktu cerita per pertemuan wilayah naik sekitar satu menit.
- **Temuan pemeriksaan naskah lama terhadap 08** (subagen, dicek ulang): 9 baris menyebut jawaban butir D1 atau tindakan kunci D2, terutama di Kenali dan dialog masuk Wonosobo; dua fakta Kenali bertentangan dengan 08; 12 baris masih menyebut 12 serpihan atau 4 tantangan. Draf 12 lalu ditelaah ulang secara terpisah; temuannya (antara lain kata "katanya" sebagai penanda jebakan, desakan waktu, dan "putuskan sendiri" sebelum skenario yang kuncinya melibatkan orang dewasa) sudah dimasukkan. Naskah aktif (`docs/naskah-cerita.md`) belum diubah; isinya dipasang di tahap 3.
- **Di luar dokumen itu:** keterangan Susun Gambar (tantangan 1) memuat jawaban butir D1 Temanggung, dan pemetaan narasi `petunjuk-*` ke butir `a01`–`a04` sudah usang; keduanya perlu dibereskan di bank sebelum validasi ahli. Diusulkan juga tes akhir dikerjakan sebelum cerita penutup di P5, yang mengubah urutan di 06 dan menunggu persetujuan pemilik proyek.

**Keputusan pemilik proyek: kamus data** (sesi `sky/optimistic-carson-kkpbcv`)

Empat pertanyaan di draf [11](11_KAMUS_DATA.md#keputusan-pemilik-proyek) diputuskan, dan kamus menjadi `kamus-1.0`:

1. **Kode bagian lain di *Periksa Unggahan*: sesuai usulan.** Bagian ketuk yang bukan kunci dan bukan pengecoh utama diberi kode jenis petunjuknya (BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK), bukan INF-REL. Ketukan di tempat kosong dekat kunci = NON-SEN dan tidak memakai kesempatan. Aturan di 08 disesuaikan.
2. **Ambang jeda tanpa masukan: 3 menit, sementara.** Hanya bagian jeda yang melebihi ambang yang dipotong dari waktu aktif. Pemilik proyek meminta durasinya mudah diubah, jadi ambang disimpan sebagai pengaturan di konfigurasi studi, dan lapis turunan dihitung ulang dari data mentah tanpa mengubah sistem.
3. **Data siswa yang bisa dianalisis: sesuai usulan.** Izin lengkap, bukan sekolah uji coba, dan percobaan pertama yang bukan dari perangkat bersama pada sedikitnya 80% butir literasi digital (49 dari 61).
4. **Tebakan cepat dipakai apa adanya di analisis utama**; analisis kedua memperlakukannya sebagai data hilang. Ambang sementaranya di bawah 3 detik (usulan awal 1,5 detik), juga berupa pengaturan.

Ambang 3 menit dan 3 detik menggantikan angka usulan lama (60 detik dan 1,5 detik) di 01 dan 03, dan tetap ditetapkan ulang setelah uji coba (`kamus-1.1`). Penegasan yang menyertai draf tidak dikoreksi, jadi ikut berlaku.

## 25 Oktober 2026

**Draf kamus data** (sesi `sky/optimistic-carson-kkpbcv`, daftar kerja no. 2)

- Kamus data disusun di [11_KAMUS_DATA.md](11_KAMUS_DATA.md): peta bukti, butir yang dinilai, pengerjaan pertama, data hilang, penanda analisis, definisi beku 10 analitik, variabel butir dan peserta, aturan gabung lembar, dan syarat data untuk tahap 3. Statusnya draf sampai pemilik proyek memutuskan empat pertanyaan di bagian akhir dokumen itu:
  1. kode bagian ketuk lain di *Periksa Unggahan*
  2. cara memotong jeda di waktu aktif
  3. definisi data siswa yang bisa dianalisis
  4. perlakuan tebakan cepat di analisis utama
- **Penegasan yang diusulkan bersama draf** (tafsiran dokumen sebelumnya, bisa dikoreksi pemilik proyek):
  - Pengerjaan pertama yang terputus dilanjutkan, bukan dibuat baru. Syarat ini ditambahkan ke mode penelitian (perubahan sistem no. 16).
  - "Dilewati" jenis (a) juga mencakup skenario yang ditutup setelah kesempatan ketiga.
  - "Dibantu" di analisis utama hanya bantuan isi L3 (ISI, STR, BHS).
  - Ketukan di tempat kosong tidak memakai kesempatan; ketukan dekat kunci dihitung sebagai NON-SEN di luar percobaan.
  - Benda sasaran petunjuk lain di Temukan Benda diberi INF-LIT.
  - NON-TEB tidak dihitung untuk Isi Rumpang.
  - Token yang membuka wilayah sebuah pertemuan melewati aturan buka berurutan antarwilayah; tantangan yang tertinggal dikerjakan di pertemuan 5.
  - Perpindahan setelah umpan balik di Skenario Digital dinilai dari pilihan tindakan, bukan kodenya, karena dua pilihan bisa berbagi kode.
  - Kode kesalahan bank tidak pernah ditimpa: NON-TEB dan NON-HABIS menjadi penanda tersendiri, dan `*-LAIN` dari pengodean manual disimpan di kolom lain.
- **Perbaikan tanpa keputusan baru:**
  - Di 03, DIG-HOX hanya D2, analitik 8 berlaku untuk D1–D3, dan analitik 10 hanya D2, karena dimensi ikut butir (model *between-item*).
  - Gambar peta bukti disesuaikan dengan kisi-kisi yang disetujui: nama D3, Isi Rumpang dan Benar atau Salah hanya D1, Cari Objek babak 2 D3 dengan babak 1 PBK, Skenario Digital hanya D2, dan kode BUD di pola kesalahan.
  - Contoh data mentah di 01 mendapat kolom `rapid_guess` (kosong untuk Isi Rumpang). Baris tebakan cepat kini memakai kode bank butir itu (INF-HUB), dan `wnb-3-a01` memakai INF-LIT sesuai 08.
  - Analitik 3 di 01 tidak lagi menyebut Susun Gambar (tidak dinilai sejak kisi-kisi 06), dan catatan perubahan sistem no. 14 menunjuk ke syarat data di 11.
  - Di 06, siswa yang belum selesai melanjutkan di pertemuan 5 (susulan), sesuai keputusan token.

**Pemeriksaan sebelum tahap dokumen 1** (sesi `pbb/eloquent-sagan-j40sg8`)

- **Keputusan pemilik proyek: pelabelan buta hanya sekali, oleh dua ahli di tahap 2** (B.5a di 07). Kesepakatan κ ≥ 0,70 pindah dari gerbang G1 ke G2. G1 kini berarti konstruk disetujui dan semua dokumen tahap 1 selesai dan disetujui pemilik proyek. Daftar kerja no. 3 pindah ke tahap 2. Alasannya: pelabelan ganda memakan waktu, dan anggota tim yang sudah melihat label pengembang di 08 tidak bisa melabel secara buta.
- **Perbaikan 01 tanpa keputusan baru**, supaya sejalan dengan kisi-kisi 06 yang sudah disetujui sebelum kamus data disusun:
  - Tabel topik Skenario Digital: izin atas karya budaya pindah ke D3, topik izin D2 cukup foto dan obrolan diri sendiri atau teman.
  - Skema kesalahan mendapat bagian kode BUD (bagian C; NON jadi D, alasan R jadi E). Kalimat "INF-BUD juga D3" dan "DIG-IZN dan DIG-ETK juga D3" diganti pembagian yang disetujui.
  - Cara kode menempel kini memisahkan Cari Objek babak 1 (PBK) dan babak 2 (*Periksa Unggahan*, D3).
  - Contoh data mentah memakai kode `x` untuk butir Temukan Benda.
  - Dua kalimat "kalau token sesi dipakai" dihapus.
- Gambar peta jalan dan 00 disesuaikan.

**Keputusan pemilik proyek: wawancara pertanyaan terbuka** (sesi `pbb/eloquent-sagan-j40sg8`, bertahap)

Tahap 1, urutan kerja dan akses:

- **Urutan kerja tetap seperti peta jalan di 01.** Dokumen implementasi dan perubahan sistem baru dimulai di tahap 3, setelah validasi ahli dan izin etik (gerbang G2). Akibatnya, layar baru (Skenario Digital, *Periksa Unggahan*, token) belum ada saat ahli media menilai di tahap 2; cara menilainya masuk pertanyaan terbuka di 01.
- **Token sesi dipakai, versi ringan, dan sekaligus mengatur wilayah yang dibuka.** P1 pendaftaran dan cerita pembuka, P2 Temanggung, P3 Magelang, P4 Wonosobo, P5 tantangan yang tertinggal dan cerita penutup.
- **Di luar sesi lapangan, seluruh game tertutup sampai penelitian selesai**, termasuk Pustaka dan Profil. Siswa melihat pesan ramah. Setelah status studi ditutup, game terbuka seperti biasa.
- **Kode peserta `S03-A07` disetujui** dengan aturannya di 01: kode sekolah dari daftar peneliti, awalan `U` untuk sekolah uji coba, tanpa `GLT-`, dibuat sistem saat daftar dari token, tidak pernah diubah setelah ada data.
- **Perbaikan dokumen tanpa keputusan baru:** kalimat "kode butir tidak bergeser" di 01 diselaraskan dengan keputusan bank baru (kode yang sudah punya data siswa tidak dipakai ulang); perubahan sistem no. 4 di 01 kini menyebut butir jangkar antarvariasi; L1 di 02 tidak lagi menyebut HP sebagai perangkat.

Tahap 2, aturan game selama penelitian:

- **Skenario Digital memakai tiga kesempatan**, sama seperti tantangan lain. Setiap kesempatan berisi pilihan tindakan dan alasan; yang dinilai tindakan dan alasan pertama. Tafsiran yang dipakai: skenario selesai saat tindakan yang tepat dipilih, pilihan "kurang tepat" (skor 1) memakai satu kesempatan, dan setelah kesempatan ketiga tindakan yang aman ditampilkan dan dijelaskan.
- **Buklet dimasukkan P1 dari L2.** Sesudah pertemuan P1, koordinator memasukkan buklet per kode peserta di panel peneliti; panel memeriksa keseimbangan B1–B4 per rombel. Siswa duduk di kursi yang sama di P2–P4; buklet melekat pada siswa. L2 di 02 mendapat kolom Buklet.
- **Layar terlalu kecil saat studi berjalan: sesi ditolak dengan pesan ramah.** Di luar studi cukup peringatan. Ini memperjelas keputusan media sebelumnya ("ditolak atau diberi peringatan").
- **Pengerjaan ulang selama studi** baru terbuka setelah kelima tantangan wilayah pertemuan itu tuntas, hanya di dalam sesi. Datanya disimpan tapi tidak dinilai.

Tahap 3, akun dan data:

- **Peran `guru` dihapus seluruhnya**, termasuk halaman dan hak aksesnya, bukan hanya dinonaktifkan. Kalau laporan guru dibuat nanti, perannya dibangun ulang dengan hak baru. Kolom Guru di tabel peran 01 dihapus.
- **Lupa kata sandi di lapangan: P1 me-reset lewat kode peserta** dari panel peneliti (kode dicari di L2). Sistem memberi kata sandi sementara dan mencatatnya di log audit; peneliti tetap tidak melihat nama, dan admin tidak perlu hadir.
- **Nama lengkap siswa tetap disimpan di akun, hanya terlihat oleh admin**, sesuai rencana di 01 (usulan nama panggilan saja tidak dipilih).
- **Indikator `sikap` dipakai sebagai skala pendamping sikap menjaga warisan lewat Balai Refleksi**, di luar skor literasi digital, seperti PBK. Saat ini Balai Refleksi hanya berisi ringkasan perjalanan dan kritik dan saran, jadi skala ini instrumen baru: disusun di tahap 1 (daftar kerja no. 19), divalidasi di tahap 2 (G3 pengamat budaya), dan menjadi perubahan sistem no. 21. Waktu pengisiannya masuk pertanyaan terbuka.
- **Ambang waktu tidak aktif dan tebakan cepat ditetapkan setelah uji coba**, mengikuti tahap 4 peta jalan yang urutannya sudah dikukuhkan di tahap 1 wawancara.

Tahap 4, operasional penelitian:

- **Tiga peneliti per kelas** (P1, P2, P3) di pengambilan data utama. Kalau pada hari itu satu berhalangan, P1 juga mengisi L3, L5 dipindah, dan penyimpangannya dicatat di L1.
- **Tes awal dan akhir:** bagian literasi membaca diadaptasi dari contoh soal AKM/ANBK yang terbuka untuk umum; bagian D2 dan D3 disusun sendiri, sejajar dengan kisi-kisi tapi tidak sama dengan butir game; divalidasi bersama bank di tahap 2.
- **Pra-registrasi di OSF** untuk skema kesalahan dan rencana analisis, di gerbang G4 (setelah uji coba, sebelum pengambilan data utama).
- **Ahli media menilai dalam dua langkah:** tahap 2 menilai game sekarang ditambah mock-up layar baru (daftar kerja no. 20); setelah tahap 3, telaah singkat layar jadi di instalasi uji sebelum uji coba (no. 21, bagian gerbang G3). Gambar peta jalan disesuaikan, sekaligus angka lamanya ("129 butir", "15 perubahan") diganti rumusan tanpa angka.

Tahap 5, rencana dokumen:

- **Cerita penghubung dan narasi tantangan kelima didraf Claude** dari skenario di 08, disetujui pemilik proyek, dan direkam pengisi suara yang sama dengan narasi lain di sesi rekaman yang sama.
- **Skala sikap diisi sekali, di P5**, di Balai Refleksi setelah cerita penutup.
- **Dokumen berikutnya: kamus data** (`11_KAMUS_DATA.md`, daftar kerja no. 2), yang membekukan definisi 10 analitik, "dilewati", waktu aktif, tersensor, dan kode. Dokumen tahap 1 lain merujuk ke sana.
- **Aturan menjaga konteks** dicatat di `.claude/rules/revisi-10.md` (dimuat saat berkas di folder ini disentuh), dan [00_INDEKS.md](00_INDEKS.md) memuat status setiap dokumen, urutan baca, dan tahap dokumen yang tersisa.
- **Status lama dibetulkan:** daftar kerja no. 6 (Skenario Digital) sudah selesai di draf 08.

Validator dan komisi etik belum bisa ditetapkan pemilik proyek. Sementara:

- **Tujuh validator dengan nama samaran**, sesuai komposisi panel di 07 yang sudah diterima: Ahli Bahasa Anak 1 dan 2, Ahli Literasi Digital 1 dan 2, Ahli Pengukuran (panel ahli materi lima orang), Ahli Budaya Kedu (pengamat budaya), dan Ahli Media Pembelajaran. Nama samaran dicatat di 03 dan dipakai di instrumen dan paket validasi sampai orangnya ditetapkan.
- **Komite etik memakai nama samaran *Lembaga Komite Etik*.** Izin etik diajukan pada minggu kedua sampai keempat November 2026. Persetujuan diperoleh sebelum kegiatan yang mensyaratkannya dimulai, paling lambat antara akhir September dan minggu pertama November 2027.
- **Gerbang G2 tetap menunggu persetujuan etik.** Dengan jadwal di atas, dokumen implementasi dan perubahan sistem (tahap 3) bisa baru dimulai sekitar akhir 2027. Usulan memulai tahap 3 setelah izin diajukan tidak dipilih; pemilik proyek tetap pada urutan ketat dari tahap 1 wawancara. Uji keterbacaan dengan anak (tahap 2), uji coba, dan pengambilan data juga menunggu persetujuan.

**Keputusan pemilik proyek: kisi-kisi, draf bank soal, dan telaah pendahuluan T6** (sesi `kisi-kisi-bank-soal`)

- **Kisi-kisi bank soal ([06](06_KISI_KISI_BANK_SOAL.md)) disetujui seluruhnya**, termasuk semua bagian yang semula bertanda "usulan". Dengan begitu rumusan keputusan di [05](05_TELAAH_BUTIR_DAN_DIMENSI.md) ikut berlaku:
  - D3 = *Partisipasi dan Representasi Budaya Kedu di Ruang Digital*, diukur lewat Cari Objek babak 2 *Periksa Unggahan* (12 butir per siswa). Cari Objek babak 1 jadi skala pendamping *Pengetahuan Budaya Kedu* (PBK), di luar skor literasi digital.
  - D2: tindakan Skenario Digital diskor 0–1–2; alasan hanya data diagnosis dan tidak masuk skor utama di versi pertama.
  - Kode BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK, BUD-LAIN untuk D3. INF-BUD hanya untuk PBK; DIG-IZN dan DIG-ETK hanya untuk D2.
  - Isi Rumpang Temanggung memakai dua kata pengecoh.
  - Per siswa di pengerjaan pertama: 61 butir literasi digital (37 D1 = 26 D1a + 11 D1b; 12 D2; 12 D3) ditambah 12 butir PBK. Isi Rumpang 4/5/6 rumpang, Benar atau Salah 4/6/6 pernyataan.
  - Desain **empat buklet (B1–B4)** per kursi untuk pengerjaan pertama, menggantikan pengacakan variasi; pengerjaan ulang tetap bebas.
  - **Butir jangkar antarvariasi**: 6 pernyataan D1 (2 per wilayah) di kartu jangkar yang sama untuk variasi a dan b, bukan butir yang diulang di tiga wilayah.
  - **Sampel**: target 300 siswa yang datanya bisa dianalisis, merekrut sekitar 360 (± 13 kelas).
  - **Lima pertemuan per kelas**: tes awal; Temanggung; Magelang; Wonosobo; susulan, cerita penutup, dan tes akhir.
  - Opsi tiga pilihan ("tidak bisa dipastikan dari teks") untuk D1b Wonosobo ditunda sampai setelah uji coba.
- **Penyimpangan dari kisi-kisi yang disetujui** (06 sudah disesuaikan): sebaran kode kesalahan dihitung per wilayah, bukan per variasi; panjang teks `wnb-3` 90–150 kata dan kartu jangkar Wonosobo 25–50 kata.
- **Draf bank soal baru** disusun di [08](08_DRAF_BANK_SOAL.md): 116 butir literasi digital dalam variasi a/b ditambah 6 butir jangkar dan 12 butir PBK, semua berstatus `needs_verification`. Terjemahan bahasa Inggris dikerjakan pemilik proyek nanti.
- **Nama fiktif dicek di web.** "Sanggar Turangga Muda" diganti "Sanggar Kuda Pelangi" karena mirip nama kelompok kuda lumping nyata. Semua unggahan tampil di aplikasi media sosial fiktif *Kedugram*, dan komentator ditulis dengan nama depan saja. Pencarian manual nama akun di Instagram dan TikTok masih perlu dilakukan satu orang tim.
- **Telaah pendahuluan T6** dijalankan ([09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md)); 2 temuan sistematis (pilihan tepat dan alasan yang diharapkan selalu paling panjang) dan 13 temuan per butir dibetulkan di 08. Sisa temuan R1–R4 disetujui sesuai usulan: rumpang yang hanya punya satu calon dibiarkan dan dipantau di uji coba (R1); label D1b Temanggung diputuskan ahli (R2); pola kalimat pilihan "kurang tepat" dinilai ahli bahasa anak (R3); kunci Periksa Unggahan Wonosobo dipertahankan dan dipastikan ahli (R4).
- **Cek fakta C.5**: semua fakta bank lama didukung sumber; tahun perkebunan teh Tambi (1865 atau 1885) diserahkan ke ahli budaya.

**Keputusan pemilik proyek: media**

Keempat usulan dari telaah pendahuluan media ([10](10_TELAAH_PENDAHULUAN_MEDIA.md)) disetujui:

- **Tanpa ponsel.** Pengambilan data hanya memakai laptop, Chromebook, atau tablet mendatar berlayar 10 inci atau lebih. Di ponsel, sebagian besar teks tampil di bawah 12 px, sehingga skor membaca ikut bergantung pada perangkat. Aturan ini masuk daftar periksa L0, dan game menolak atau memberi peringatan pada sesi penelitian di layar kecil.
- **Area ketuk Cari Objek tidak boleh bertumpuk.** Adegan Magelang dan Wonosobo ditata ulang supaya area ketuk tidak bersentuhan (jarak minimal setara 24 dp). Aturan bank soal mendapat pemeriksaan otomatis yang sama, berlaku untuk babak 1 dan babak 2 (*Periksa Unggahan*).
- **Jam disembunyikan selama penelitian.** Waktu tetap direkam server. Hitungan benar/salah boleh tampil setelah Cek pertama sebagai umpan balik. Diatur lewat mode penelitian di konfigurasi studi.
- **Aturan narasi.** Bacaan dan pernyataan D1 tidak pernah dibacakan. Situasi Skenario Digital (D2) dan petunjuk *Periksa Unggahan* (D3) boleh dibacakan lewat tombol ▶, tidak diputar otomatis, dan pemutarannya tercatat. Kalimat kisi-kisi "teks di layar game tetap dibacakan narasi seperti sekarang" dibaca dengan aturan ini.

**Telaah pendahuluan media (Claude, bukan validasi ahli)**

- Media game ditelaah di instalasi uji lokal: [10_TELAAH_PENDAHULUAN_MEDIA.md](10_TELAAH_PENDAHULUAN_MEDIA.md). Tiga temuan berkode V dan keparahan 3 perlu keputusan pemilik proyek sebelum uji coba:
  - pengambilan data tidak memakai ponsel
  - area ketuk Cari Objek yang bertumpuk ditata ulang dan diperiksa otomatis
  - jam disembunyikan selama penelitian
- Usulan aturan narasi: bacaan dan pernyataan D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk *Periksa Unggahan* boleh dibacakan lewat tombol ▶.

**Keputusan pemilik proyek**

- Media game yang ada sekarang (tampilan, layar, interaksi, audio, gambar) juga terbuka untuk diubah, ditambah, dan dihapus sesuai kebutuhan Revisi 10.
- Telaah instrumen validasi ([07](07_TELAAH_INSTRUMEN_VALIDASI.md)) diterima seluruhnya:
  - B.5 dipecah menjadi B.5a (pelabelan buta, dua ahli) dan B.5b (relevansi dan kejelasan, semua ahli menilai semua butir, minimal tiga penilaian non-TD per butir).
  - Panel ahli materi lima orang, dengan matriks penugasan aspek.
  - κ berbobot untuk level AKM.
  - Perkiraan beban kerja dicantumkan di surat permohonan.
- Ahli media masuk daftar bidang validasi (daftar kerja no. 14).
- Claude berperan sebagai penelaah pendahuluan berbantuan AI, bukan validator: skornya tidak dihitung dan Claude tidak melakukan pelabelan buta. Telaah pendahuluan media dikerjakan di instalasi uji lokal.
- Belum ada data siswa asli di server. Bank soal baru boleh memakai susunan kode butir yang baru.
- Setiap tantangan yang dinilai cukup punya **dua variasi (a/b)**, bukan tiga.
- Analisis memakai **model multidimensi *between-item***: setiap butir punya tepat satu dimensi utama.
- **D1 bertumpu pada literasi membaca**, dengan dua label sub-aspek: D1a memahami informasi dan D1b menilai informasi dan sumber. Jumlah butir D1 **tidak dibatasi 29**; lebih banyak tidak masalah.
- Beberapa bacaan diubah menjadi **teks bergenre digital**.
- Kisi-kisi bank soal baru disusun di sesi terpisah, di [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md) (branch `kisi-kisi-bank-soal`). Disetujui pada hari yang sama (lihat di atas).
- Draf instrumen validasi ahli (r10 untuk ahli materi dan pengamat budaya, serta instrumen ahli media) ditelaah di [07_TELAAH_INSTRUMEN_VALIDASI.md](07_TELAAH_INSTRUMEN_VALIDASI.md). Versi 1.0 instrumen ahli materi sudah digantikan r10.
- Seluruh isi bank soal yang ada terbuka untuk diubah, diperbaiki, dihapus, dan ditambah sesuai kebutuhan penelitian. Bank soal baru disusun bersama Claude. Batas yang tetap berlaku hanya batas teknis penelitian: kunci jawaban tetap di server, butir baru atau yang diubah berstatus `needs_verification` sampai divalidasi, dan kode butir yang sudah punya data siswa tidak dipakai ulang untuk isi yang berbeda.

**Telaah butir dan dimensi** (rumusan keputusannya berlaku sejak kisi-kisi 06 disetujui, lihat di atas)

- Pemilik proyek mengusulkan rumusan keputusan dimensi dan penskoran: satu dimensi utama per butir, D1 sekitar 29 butir, D2 sekitar 12 butir, D3 diubah menjadi *Partisipasi dan Representasi Budaya Kedu di Ruang Digital*, skor utama dari percobaan pertama.
- Telaah butir dan dimensi disusun di [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md): 99 butir dinilai ditelaah satu per satu, ada rumusan keputusan yang sudah disesuaikan, dan daftar perbaikan butir. Rumusan itu berlaku sejak kisi-kisi 06 disetujui.

## 23 Oktober 2026

**Keputusan pemilik proyek**

- Judul penelitian ditetapkan: *Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar*.
- Admin dan peneliti adalah dua entitas yang berbeda.
- Log lama yang tidak dibutuhkan dihapus; yang masih perlu dipisah untuk admin atau untuk peneliti.
- Skenario Digital jadi tantangan kelima di tiap wilayah (15 tantangan, 15 Serpihan Cahaya).
- Semua yang ada di game boleh dirombak total.
- Literasi digital wajib ada dalam penelitian.
- Semua rekomendasi telaah diterima.
- Guru tidak ikut dalam penelitian; tugasnya hanya menyiapkan perangkat dan internet. Laporan untuk guru cuma ide tambahan di luar penelitian.
- Belum ada implementasi di sistem. Dokumentasi revisi diselesaikan dulu; dokumen implementasi yang lengkap disusun setelahnya.
- Dokumentasi revisi disimpan di `docs/revisi-10-analitik/`.

**Usulan yang menunggu persetujuan tim** (rinciannya di dokumen utama)

- Literasi digital sebagai konstruk utama dengan tiga dimensi: D1 literasi informasi, D2 aman dan etis, D3 budaya digital Kedu. Usulan ini turunan langsung dari judul penelitian.
- Token sesi versi ringan, dibuat peneliti per pertemuan.
- Kode peserta `S03-A07` (sekolah, rombel, nomor urut).
- Pembagian log: dihapus, admin, atau peneliti, sesuai tabel di dokumen utama.
- Peran `peneliti` baru dan peran `guru` dinonaktifkan selama penelitian.
- Olah data dua lapis di sistem (mentah dan turunan), analisis statistik di luar sistem.

**Hasil cek fakta**

- Dieng Culture Festival digelar di Banjarnegara, bukan Wonosobo. Contoh skenario Wonosobo diganti ke Telaga Warna (Dieng Wetan, Kejajar, Wonosobo).
- Sampai 2025, AKM diikuti sampel siswa kelas 5. Mulai 2026 aturannya mengikuti Kepmendikdasmen Nomor 56 Tahun 2026; beberapa sumber menyebut literasi membaca kini masuk TKA Bahasa Indonesia kelas 6. Teks resminya masih perlu dicek.
- Rujukan diperbarui ke PIRLS 2026, DigComp 3.0 (2025), dan nama Komdigi.

## Titik awal: dokumen 10 analitik dari pemilik proyek

Revisi ini berangkat dari tabel berikut (dokumen *Analitik Game Gelita Terbaru*).

| No | Analitik | Data yang direkam dalam game | Informasi bagi guru (kolom asal) |
| --- | --- | --- | --- |
| 1 | Penyelesaian aktivitas | Soal yang dikerjakan, dilewati, serta level yang selesai | Sejauh mana siswa menyelesaikan kegiatan |
| 2 | Ketepatan jawaban | Jawaban siswa dan status benar–salah pada setiap soal | Indikator yang sudah atau belum dikuasai |
| 3 | Ketepatan pada percobaan pertama | Jawaban pertama sebelum mengulang atau menerima umpan balik | Gambaran kemampuan awal siswa pada tugas tersebut |
| 4 | Jumlah percobaan | Banyaknya percobaan sampai berhasil | Tugas yang membutuhkan pengulangan |
| 5 | Waktu pengerjaan | Waktu mulai dan selesai setiap soal; waktu tidak aktif dipisahkan jika memungkinkan | Tugas yang memerlukan waktu lebih lama untuk diselesaikan |
| 6 | Pola kesalahan | Pilihan jawaban salah berdasarkan kategori kesalahan yang telah ditentukan | Jenis kesulitan, misalnya salah memahami informasi atau memilih tindakan digital |
| 7 | Penggunaan bantuan | Pemutaran audio, pembukaan petunjuk, dan pengulangan instruksi | Dukungan yang digunakan siswa selama mengerjakan |
| 8 | Navigasi permainan | Perpindahan halaman, kembali ke materi, dan soal yang dilewati | Alur siswa ketika mencari informasi dan menyelesaikan tugas |
| 9 | Perubahan setelah umpan balik | Jawaban sebelum dan sesudah umpan balik | Apakah siswa berhasil memperbaiki jawabannya |
| 10 | Pilihan dan alasan pada skenario digital | Pilihan tindakan dan alasan tentang data pribadi, kata sandi, izin berbagi, dan pesan orang asing | Pemahaman siswa mengenai keamanan dan etika digital |

Karena guru tidak ikut dalam penelitian, kolom terakhir diganti "Makna bagi penelitian" di dokumen utama.
