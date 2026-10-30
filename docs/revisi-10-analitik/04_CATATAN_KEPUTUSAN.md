# Catatan Keputusan Revisi 10 Analitik

Per 27 Oktober 2026 · Bagian dari [revisi 10 analitik](01_REVISI_10_ANALITIK.md)

Catatan ini merekam keputusan selama revisi, dari yang terbaru. Keputusan untuk implementasi nanti dicatat di dokumen implementasi dan di catatan keputusan perombakan kelas 5 saat pekerjaannya dimulai.

## 27 Oktober 2026

**Izin etik siap diajukan: komite BRIN dan formulirnya, pengecekan PP No. 33 Tahun 2026, tempat kosong 22 dan 23** (cabang `r10-formulir-etik`, dari `sky/dazzling-planck-6cl9vy`) · `asal: pengembang` · status `draf pengembang` · Prototipe I, belum merevisi produk

Satu putaran wawancara, empat pertanyaan; pengembang memilih usulan pada tiga pertanyaan dan mengisi sendiri pertanyaan server.

Yang diputuskan (22 no. 22–24, 23 no. 22):
- **Komite tujuan: Komisi Etik Bidang Sosial Humaniora BRIN**, lewat Sistem Informasi Klirens Etik Riset, dengan Formulir Klirens Etik Riset BRIN yang diterbitkan (empat bagian, matriks etika 3.1–3.8, checklist lampiran). Nama komite dan alamatnya ditulis di 22 dan di surat 23, karena itu badan publik; *Lembaga Komite Etik* tetap nama rujukan di dokumen lain. PSEPPKN 2021 tetap cadangan. Tidak dipilih: komite etik institusi sendiri, atau belum memilih.
- **Server: IDCloudHost (PT Cloud Hosting Indonesia), pusat data di Jakarta Pusat**, mengisi `[nama dan lokasi penyedia server]` di 22 dan 23 (T1 bagian 3). Perjanjian prosesor memuat sebelas hal di Pasal 14 dan lokasi pusat datanya.
- **Data pribadi orang sungguhan tetap di luar repo.** Nama, kontak, dan data diri ketua peneliti, pembimbing, tim, dan narahubung data tetap tempat kosong, dengan daftar isian di [22 → Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan).

Yang dikerjakan:
- **22 disesuaikan dengan formulir BRIN** tanpa mengubah isi atau keputusan: bagian baru [Pengajuan lewat sistem klirens etik BRIN](22_PERMOHONAN_IZIN_ETIK.md#pengajuan-lewat-sistem-klirens-etik-brin) (delapan langkah dari akun sampai jawaban atas catatan komisi, dan lampiran mana yang diunggah di langkah mana), dan [Daftar isian sebelum diajukan](22_PERMOHONAN_IZIN_ETIK.md#daftar-isian-sebelum-diajukan) (yang sudah diisi, lalu kelompok A–F menurut siapa yang mengisi). Masa berlaku dan perpanjangan kini ditulis menurut BRIN (12 bulan, formulir amandemen); bagian PSEPPKN ditandai cadangan. Garis waktu mendapat baris perkiraan keputusan Januari–Februari 2027 (patokan BRIN ± 1–2 bulan sejak berkas lengkap); target persetujuan di 01 tidak diubah dan menunggu tinjauan pengembang.
- **PP No. 33 Tahun 2026 dicek ke transkripsi salinan naskahnya** (Meridian Hukum, memuat blok "Salinan sesuai dengan aslinya" Kementerian Sekretariat Negara; PDF resmi di peraturan.go.id, JDIH Setneg, JDIH Komdigi, dan BPK tidak bisa dibuka 27 Oktober 2026). Ditetapkan dan diundangkan 16 Juli 2026, LN 2026 No. 88, TLN No. 7190, 225 pasal. Dibetulkan: Pasal 82 ayat (4) → ayat (3) (penghapusan melibatkan prosesor). Dipertajam: 3 × 24 jam dihitung sejak permintaan diterima (Pasal 92 ayat (2)), juga di surat T1 bagian 6 dan T10; pembetulan Pasal 70–71 (bukan 69–71); akses Pasal 77 ayat (1), salinan Pasal 78; prosesor lain Pasal 15 ayat (2); PPDP Pasal 142–147; definisi anak "dan belum menikah"; Pasal 115 juga untuk dampak serius terhadap kepentingan masyarakat; Pasal 38 ayat (7); batas 3 × 24 jam ditulis dalam jam, jadi definisi "Hari" tidak berlaku dan tim menghitung jam kalender. Nomor pasal UU 27/2022 cocok dengan naskah anotasi Mahkamah Konstitusi. `needs_verification` yang tersisa: satu pencocokan lagi dengan PDF resmi sebelum berkas diunggah.
- **Peraturan Lembaga belum terbit**, dan Badan Pelindungan Data Pribadi belum dibentuk (berita Bisnis 13 September dan CNBC Indonesia 16 September 2026). Selama masa peralihan, pemberitahuan kebocoran dan aduan ke "lembaga" dikirim ke Kementerian Komunikasi dan Digital ([22 → Dasar hukum](22_PERMOHONAN_IZIN_ETIK.md#dasar-hukum)).
- **Tempat kosong yang diisi:** nama dan kontak komite (laman klirensetik.brin.go.id, dibuka 27 Oktober 2026), pedoman di komitmen etik, penyedia server, nomor WA SAPA 129 (08111-129-129, pernyataan Kementerian PPPA 23 Februari 2026 dikutip Kompas TV), dan lampiran 18 ditautkan ke [26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md](26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md), yang ditulis sesi lain dengan tempat kosong yang sama.

Akibat ke dokumen lain (dicatat di 22 → *Akibat ke dokumen lain*, tidak diubah di cabang ini): target persetujuan di 01 untuk ditinjau pengembang; 26 memakai isian server dan Kementerian Komunikasi dan Digital yang sama; 24 dan 02 → L7 tidak perlu diubah.

**Cek fakta budaya tim: 135 klaim dicek, B.7 IB diperbarui, perbaikan 08 dan 12 diputuskan** (cabang `r10-cek-fakta-budaya`, bergantung pada PR #275) · `asal: pengembang` · `draf pengembang`, keputusan perbaikan `disetujui pengembang` · Prototipe I, belum merevisi produk

Prasyarat no. 6 di [20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan) dipenuhi di [25_CEK_FAKTA_BUDAYA.md](25_CEK_FAKTA_BUDAYA.md). Klaim budaya dikumpulkan dari:
- matriks 46 objek IB;
- isi budaya 08;
- keterangan Susun Gambar;
- cerita 12.

Setiap klaim dicek ke sumber dengan bantuan AI, dan hanya sumber yang benar-benar dibuka yang dikutip. Hasilnya: 103 dikonfirmasi, 20 varian, 2 keliru, 10 `needs_verification`. Ini kerja pengembang, bukan validasi ahli; pengamat budaya mengonfirmasinya di B.7.

Yang diputuskan pengembang (dua putaran, empat pertanyaan per putaran):
1. **Topeng Ireng `mgl-2-b04`.** Bacaan lama keliru: menurut dua kajian, gerak tari ini menggambarkan prajurit, bukan petani. Bacaan ditulis ulang ke makna keprajuritan dengan kunci *prajurit* dan pengecoh *penjajah* (INF-LIT); AKM L2 dan Bloom C2 tetap. Pengembang memilih ini, bukan usulan yang direkomendasikan (kunci *petani* tetap dengan kalimat baru).
2. **Kompleks Candi Arjuna** ada di Desa Dieng Kulon, Kecamatan Batur, Banjarnegara. Gambarnya tetap menjadi variasi `a` Susun Gambar Wonosobo, tetapi keterangannya menyebut letak itu, dan deskripsi tantangan menjadi "lukisan Wonosobo dan Dieng".
3. **Prasasti Gondosuli** ditulis "diperkirakan dibuat sekitar tahun 832 Masehi". Prasastinya tidak bertanggal, dan 832 adalah tafsiran de Casparis. Kunci `tmg-3-b02` tetap *benar*.
4. **Petunjuk teh Tambi** ditulis "sejak zaman Belanda": kebunnya sudah ada sejak 1838, dan baru disewa swasta pada 1865. Pertanyaan terbuka 1865 atau 1885 ditutup.
5. **Enam rumusan kecil di 08**, tanpa mengubah kunci:
   - tinggi Sindoro dan Sumbing ditulis "sekitar";
   - alasan larangan menyentuh relief menjadi "aus";
   - keterangan keliru `mgl-4-a` menjadi "tarian asli dari Temanggung";
   - getuk "dimasak";
   - embun upas "suhu di dekat tanah";
   - "panas dari permukaan bumi".

   "Puluhan penari" juga menjadi "banyak penari".
6. **"Dataran Kedu" menjadi "tanah Kedu"** di 12, pada baris yang mencakup Wonosobo (*Benang merah baru*, `penutup-01`).
7. **Perbaikan di berkas data** (keterangan Susun Gambar dan baris Kenali) dicatat di 25 → *Akibat ke dokumen lain* dan diterapkan di tahap 3.

Yang diubah:
- 25 baru;
- 08 dan 12 sesuai keputusan;
- IB: B.7 menjadi 44 fakta berkode, nama objek B.5 no. 6, 15, 26, 31, 42, dan 46, catatan di C.3, D.3, dan D.4;
- PDF IB dan lembar kerja validator, dibuat ulang;
- baris 25 di 00.

Yang dibawa sesi lain: di 20, prasyarat no. 6 dan daftar periksa pengamat budaya; di 21, "19 fakta" menjadi "44 fakta" (daftar di 25).

**Peran pengembang, peneliti, dan ahli; "pemilik proyek" menjadi "pengembang", nama asisten menjadi "AI"** (cabang `sky/dazzling-planck-6cl9vy`) · `asal: pengembang` · Prototipe I, belum merevisi produk

Pengembang menetapkan cara kerja revisi ini. Pengembang, dibantu AI, boleh mengerjakan hal yang nantinya diserahkan ke ahli, komite, atau pihak lain: memberi label, skor, telaah, dan validasi latihan, misalnya memberi skor pada butir instrumen validasi. Kerja itu ditandai jelas sebagai kerja pengembang. Peneliti menerima semuanya, menindaklanjutinya, menetapkannya, dan menyerahkannya ke ahli, komite, dan pihak terkait. Judul penelitian dan 10 analitik tetap berasal dari peneliti. Sebelum diputuskan final, tidak ada yang beku penuh: perubahan mendadak atau nanti tetap boleh, asalkan jelas berasal dari pengembang atau dari peneliti bersama ahli.

Yang diputuskan:
- **Prinsip baru di [01 → Peran pengembang, peneliti, dan ahli](01_REVISI_10_ANALITIK.md#peran-pengembang-peneliti-dan-ahli).** Ada dua jalur. Kerja pengembang bukan validasi ahli: skornya tidak masuk V Aiken, I-CVI, S-CVI, κ, atau ICC, mengikuti T6 di 07. Ahli menilai tanpa melihat skor atau label pengembang. "Dibekukan" berarti versinya dicatat, bukan tidak boleh diubah. Versi yang mengikat pihak lain ada empat: paket validasi, lampiran izin etik, skema kode kesalahan setelah uji coba, dan kamus data sebelum pengambilan data. Satu orang yang memegang dua peran menandai keputusannya per peran. Bantuan AI ditulis terbuka di naskah penelitian.
- **Peran baru di [03](03_KODE_DAN_ARTI.md#peran):** *Pengembang (berbantuan AI)*. Baris *Peneliti* ditambah perannya di dokumen revisi.
- **Label asal dan status di [00](00_INDEKS.md#label-asal-dan-status).** Asal: `pengembang`, `peneliti`, `peneliti + ahli`, `komite etik`. Status: `draf pengembang` → `disetujui pengembang` → `disetujui peneliti` → `tervalidasi ahli` → `final`. Status `disetujui pengembang` ditambahkan di antara draf dan persetujuan peneliti, karena semua yang dicatat "disetujui" sejauh ini ada di tingkat itu. `needs_verification` tetap penanda terpisah.
- **"Pemilik proyek" diganti "pengembang"** di semua berkas folder ini, kecuali draf asli di `dokumen-pdf/arsip/` yang tidak diubah. Penggantian mencakup Markdown, sumber HTML instrumen, dokumen pendukung peneliti, gambar SVG, skrip `berkas-validator.php`, dan `LAPORAN.md`. Jangkar ikut berubah, misalnya `#keputusan-pemilik-proyek` menjadi `#keputusan-pengembang`. Keputusan lama di catatan ini ikut ditulis ulang dengan kata "pengembang". Kedudukannya `disetujui pengembang`, dan peneliti mengukuhkannya saat menindaklanjuti.
- **Nama asisten diganti "AI".** Contohnya: kolom penyusun "AI (draf), pengembang"; T6 di 07 "AI bukan validator"; "Disusun: AI" di dokumen pendukung peneliti. Yang tetap hanya jalur berkas aturan `.claude/rules/`, karena itu nama folder di repo.
- PDF ketiga instrumen dan dokumen model penelitian dibuat ulang dari sumbernya; jumlah halamannya sama (43, 31, 29, 12). Berkas validator dibuat ulang dari instrumen yang diperbarui.

Pekerjaan sebelum validasi dan izin etik dilanjutkan di sesi paralel. Masing-masing memakai prinsip ini dan mencatat keputusannya sendiri di sini.

**Akibat sesi PP No. 33 Tahun 2026 dan sesi BRIN dibawa ke 01, 02, 22, 23, dan 24** (cabang `sky/vigilant-wozniak-12kyni`, sesi koordinator) · Prototipe I, belum merevisi produk

Kedua sesi berjalan bersamaan, dan masing-masing menyerahkan sebagian akibatnya kepada sesi yang lain. Setelah keduanya digabung (#272, #273), akibat itu tidak dipegang siapa pun, dan dokumen yang akan dikirim saling bertentangan: keputusan no. 19 di 22 menetapkan data berkode disimpan paling singkat 20 tahun, sedangkan tabel masa simpan 22, surat orang tua T1 dan T8a, dan 24 masih menulis 5 tahun. Atas permintaan pengembang, akibat itu dibawa sekarang, sebelum versi bahan dibekukan untuk validasi ([20 → Ringkasan](20_PAKET_VALIDASI.md#ringkasan)). Tidak ada keputusan baru; semuanya menjalankan keputusan no. 10–21 di 22.

Yang diubah:
- **Masa simpan data berkode paling singkat 20 tahun** (22 no. 19) di 22 (*Isi surat orang tua*, tabel *Masa simpan dan pemusnahan*, butir 3.5), 23 (T1 bagian 3, T8a *Kerahasiaan*, *Dasar yang sudah diputuskan*, *Tabel pembagian*, tabel masa simpan), dan 24 (*Dasar yang sudah diputuskan*, *Pengelolaan data*, *Yang dibutuhkan dari dokumen lain*). Kalimat untuk orang tua: data berkode disimpan paling sedikit 20 tahun karena undang-undang mewajibkannya, tanpa nama, dan setelah penelitian ditutup tidak bisa lagi dihubungkan dengan anak. Keputusan lama yang menyebut 5 tahun (22 no. 1, 23 no. 1, 24 no. 6) diberi catatan "diganti no. 19", tidak ditulis ulang.
- **UU No. 11 Tahun 2019 Pasal 40 ayat (5)** ditambahkan ke 22 → *Dasar hukum*.
- **22** juga mendapat:
  - status di kepala yang menyebut PP No. 33 Tahun 2026 dan panduan BRIN sebagai pedoman utama;
  - catatan patokan telaah BRIN di pembuka;
  - catatan formulir BRIN bagian 1 di *Identitas dan tim*;
  - butir pernyataan versi tidak ada potensi konflik di *Konflik kepentingan*;
  - lampiran 18 (penilaian dampak lengkap, belum ditulis) dan daftar dokumen pengendali yang diperlihatkan kalau diminta;
  - rujukan PP 33/2026 di standar 6 daftar tilik.
- **02 → L5** dan 22 → *Prosedur per pertemuan*: wawancara tidak terdengar guru atau teman (panduan BRIN butir 3.1). **02 → L7** mendapat butir surat dari panduan BRIN.
- **23 → *Cek isi L7*** mendapat baris tanpa foto dan video, lima baris PP No. 33 Tahun 2026, dan dua baris panduan BRIN; tabel masa simpan mendapat baris daftar izin berkode.
- **01:**
  - *Kerja lapangan* langkah 1 memakai waktu surat H-14 sampai H-10, pengingat H-5, batas kembali H-3 (22 no. 7), menggantikan "H-7 sampai H-1".
  - Daftar kerja no. 15 menyebut pedoman BRIN dan patokan telaahnya.
  - Perubahan sistem no. 23 mendapat empat perubahan dari PP No. 33 Tahun 2026 dan `retention_days` ± 7300 hari. Digabung ke no. 23, bukan nomor baru, supaya jumlah perubahan sistem tetap 23.

Yang sengaja tidak dibawa:
- 11 (`consent_ok` untuk `data tetap`, daftar izin berkode sebagai sumber `consent_ok`) menunggu `kamus-1.1`, karena `kamus-1.0` tidak diubah.
- Kontak PPDP tidak dipindah ke T1 bagian 8, karena tidak wajib.
- Target persetujuan etik di 01 tetap, dan ditinjau pengembang setelah komite dipilih.

Masih harus dikerjakan sebelum permohonan diajukan (minggu kedua–keempat November 2026):
- menulis penilaian dampak lengkap (lampiran 18);
- mengecek nomor pasal PP 33/2026 dan UU 27/2022 ke naskah resmi, dan Peraturan Lembaga yang sudah terbit;
- mengisi tempat kosong seperti `[nama dan lokasi penyedia server]` dan `[kontak PPDP atau narahubung]`;
- menyesuaikan 22 dan 23 dengan formulir komite yang dipilih.

**Keputusan pengembang: panduan klirens etik BRIN menjadi pedoman utama 22 dan 23** (cabang `r10-klirens-brin`) · Prototipe I, belum merevisi produk

*Panduan Klirens Etik Riset Bidang Sosial Humaniora* (Komisi Etik Bidang Sosial Humaniora BRIN, kata pengantar 12 November 2025, Penerbit BRIN Maret 2026, DOI 10.55981/brin.2462) dibaca utuh, termasuk bagan alir dan contoh lampiran yang berupa gambar, lalu dibandingkan dengan 22 dan 23.

Temuan pokok:

1. **Cakupan.** Riset sosial dan humaniora oleh periset di dalam dan di luar BRIN, lewat Sistem Informasi Klirens Etik Riset, tanpa biaya. Anak disebut kelompok rentan, dan penelitian di SD dipakai sebagai contoh, jadi cocok untuk GELITA. Komite di lembaga lain bisa memakai formulirnya sendiri.
2. **Prinsip:** tiga prinsip CIOMS 2016, ditambah keselamatan peneliti dan konflik kepentingan. Bahaya yang dicegah: fisik, psikis, sosial, dan hukum.
3. **Telaah:** tanpa tingkat dikecualikan, dipercepat, atau lengkap. Setiap usulan dibaca sedikitnya tiga anggota, lalu diputuskan di sidang pleno (Rabu pekan pertama dan ketiga). Patokan bagan alir ± 1–2 bulan sejak berkas lengkap. Keputusannya disetujui, disetujui dengan catatan, revisi (10 hari kerja), atau ditolak (kalau data sudah diambil).
4. **Masa berlaku 12 bulan;** perpanjangan dan perubahan lewat formulir amandemen. Periset melapor setelah pengumpulan data lapangan selesai.
5. **Formulir empat bagian:** data diri (termasuk jenis kelamin, pendidikan, disiplin, kepakaran, dan alamat), deskripsi riset 2.1–2.11 (termasuk tautan instrumen daring dan lama dibuka, serta anggaran dan independensi), matriks etika 3.1–3.8, dan checklist lampiran.
6. **Lampiran wajib:** proposal, instrumen, formulir, surat pengantar lembaga, pernyataan konflik kepentingan, dan *informed consent*; surat izin orang tua untuk peserta di bawah 18 tahun.
7. **Isi lembar persetujuan** mencakup judul dan alamat komite.
8. **Butir 3.5 merujuk UU No. 11 Tahun 2019 Pasal 40 ayat (5):** data primer dan keluaran penelitian wajib disimpan paling singkat 20 tahun. Teks pasalnya dicek 27 Oktober 2026.

Yang sudah sesuai: persetujuan berlapis dan persetujuan anak, sukarela, tanpa penipuan, data berkode, mitigasi kelompok rentan, butir sensitif dengan prosedur rujukan, risiko bagi peneliti, hasil kembali ke sekolah, tidak ada kegiatan sebelum persetujuan, amandemen, dan isi pokok surat T1.

Keputusan pengembang (27 Oktober 2026):

1. **Panduan BRIN menjadi pedoman utama** (pilihan B). PSEPPKN 2021 disimpan sebagai pedoman cadangan. Jawaban awal pengembang adalah pilihan A (berdampingan), lalu diganti B dalam sesi yang sama. Tidak dipilih: berdampingan sampai komite dipilih (usulan), atau catatan saja.
2. **Data penelitian berkode disimpan paling singkat 20 tahun** (sesuai usulan). Nama, L7, dan kunci kode tetap dimusnahkan saat studi ditutup, dan rekaman tetap dihapus setelah transkrip diperiksa. Ini menggantikan "5 tahun setelah studi ditutup" untuk data berkode. `needs_verification`: apakah data berkode tanpa kunci memenuhi kewajiban simpan "data mentah autentik". Tidak dipilih: tetap 5 tahun dengan alasan, atau 20 tahun hanya untuk komite BRIN.
3. **Lokasi ditulis lengkap sampai desa/kelurahan dan dusun** di formulir butir 2.5 dan di usulan penelitian, mengikuti tabel panduan. Nama sekolah tetap tidak ditulis di judul, laporan, atau publikasi. Tidak dipilih: sampai kabupaten/kota atau kecamatan (usulan).
4. **Pernyataan konflik kepentingan versi "tidak ada potensi konflik".** Pengembangan GELITA oleh tim sendiri dan pengamannya tetap diuraikan di 22 → *Konflik kepentingan* dan di isian 3.8. Tidak dipilih: versi "ada potensi konflik" (usulan).

Akibatnya:
- **22** mendapat:
  - *Pedoman yang dirujuk* dengan BRIN sebagai pedoman utama dan PSEPPKN 2021 sebagai cadangan;
  - *Pemetaan ke formulir klirens etik BRIN* (bagian 1–4, dengan tempat kosong untuk data tim, anggaran, tautan GELITA, dan lokasi) dan *Telaah dan masa berlaku menurut panduan BRIN*;
  - catatan pedoman cadangan di format protokol dan daftar tilik;
  - komitmen etik yang menyebut pedoman komite yang dipilih;
  - kolom *BRIN* di lampiran, lampiran 15–17 (proposal, pernyataan tanpa potensi konflik dengan teksnya, formulir terisi), dan daftar lampiran BRIN yang tidak berlaku;
  - keputusan no. 18–21 dan baris baru di *Akibat ke dokumen lain*.
- **23** mendapat: baris judul penelitian di T1, T6, dan T8a; tempat kosong `[judul penelitian]`; `[kontak komite etik]` berisi alamat, situs, dan surel komite; keputusan no. 20–21.
- **00** dan `docs/PROGRESS.md` diperbarui.

Belum diubah karena dipegang sesi PP No. 33 Tahun 2026 (dicatat di 22 → *Akibat ke dokumen lain*):
- masa simpan 20 tahun di 22 → *Masa simpan dan pemusnahan* dan *Isi surat orang tua*;
- UU No. 11 Tahun 2019 di 22 → *Dasar hukum*;
- masa simpan di 23 → T1 bagian 3 dan T8a *Kerahasiaan*; bagian 23 lain yang memuat "5 tahun" (*Dasar yang sudah diputuskan*, *Cek isi L7*) diubah bersamaan supaya 23 tidak saling bertentangan;
- 02 → L5: wawancara tidak terdengar guru atau teman (panduan butir 3.1).

Akibat lain yang belum dibawa:
- masa simpan di 24;
- target persetujuan di 01 ditinjau karena telaah BRIN jauh lebih cepat;
- *Konflik kepentingan* dan status di kepala 22;
- `retention_days` bawaan studi sebagai perubahan sistem tahap 3.

**Keputusan pengembang: 22 dan 23 dibandingkan dengan PP No. 33 Tahun 2026** (cabang `r10-pp-pdp`) · Prototipe I, belum merevisi produk

PP No. 33 Tahun 2026 adalah peraturan pelaksana UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi: diundangkan 16 Juli 2026 (LN 2026 No. 88), berlaku 16 Januari 2027 (Pasal 225), jadi berlaku penuh untuk KG1–KG5. Naskah resmi belum bisa diperoleh (peraturan.go.id dan JDIH belum memuat berkasnya). Yang dibaca utuh adalah naskah terkonsolidasi tidak resmi di datahukum.com (12 bab, berakhir di Pasal 225), dicocokkan dengan ringkasan beberapa kantor hukum. Ada sumber yang menyebut 15 Juli 2026 dan 224 pasal. **Semua nomor pasal `needs_verification`.** Ini pemeriksaan dokumen, bukan nasihat hukum.

Temuan pokok:

1. **Data anak** (di bawah 18 tahun) adalah data pribadi spesifik (Pasal 6). Pengolahannya wajib dengan persetujuan orang tua atau wali, yang diverifikasi "dengan mempertimbangkan teknologi yang tersedia" (Pasal 38). Bukti persetujuan wajib disimpan (Pasal 36).
2. **Pengecualian untuk penelitian ilmiah** (Pasal 148 ayat (1) huruf e, Pasal 154) hanya membatasi hak hapus, tarik persetujuan, keberatan atas keputusan otomatis, penundaan, dan portabilitas; tidak membatasi akses dan pembetulan, dan tidak membebaskan kewajiban pengendali (Pasal 149).
3. **Batas 3 × 24 jam** untuk akses, pembetulan, penghentian setelah tarik persetujuan, dan pemberitahuan setelah penghapusan (Pasal 71, 77, 87, 92). Persetujuan yang ditarik mewajibkan penghapusan (Pasal 82 ayat (1) huruf b), termasuk salinan cadangan.
4. **Kebocoran:** pemberitahuan tertulis paling lambat 3 × 24 jam kepada subjek data dan lembaga pelindungan data pribadi, dengan isi minimum dan kontak PPDP atau narahubung (Pasal 114–118). Sebelumnya 22 hanya menyebut sekolah, orang tua, dan komite.
5. **Penilaian dampak** wajib sebelum pengolahan (data spesifik; evaluasi dan penskoran sistematis; Pasal 120–121). Juga perjanjian prosesor dengan penyedia server (Pasal 14), PPDP atau narahubung (Pasal 142), catatan kegiatan pengolahan (Pasal 74), dan kebijakan masa simpan (Pasal 75). Server atau penyimpanan di luar negeri memicu aturan transfer Bab V.
6. **Informasi sebelum persetujuan** (Pasal 62): identitas pengendali dan kontak PPDP, dasar pengolahan, prosesor, dan hak akses, salinan, dan pembetulan belum ada di surat.

Yang sudah sesuai: centang terpisah per izin, surat sebelum formulir, mundur tanpa akibat, minimisasi data, kode dengan kunci terpisah, enkripsi, kalimat batas pengamanan daring, kegiatan pengganti bagi anak tanpa izin, dan masa simpan.

Keputusan pengembang (27 Oktober 2026), keempatnya pilihan yang diusulkan:

1. **Server, salinan cadangan, dan penyimpanan terenkripsi peneliti di Indonesia**; tidak ada transfer ke luar negeri. Tidak dipilih: boleh di luar negeri dengan penilaian transfer Bab V.
2. **Bukti persetujuan setelah studi ditutup: daftar izin berkode tanpa nama**, disimpan selama data berkode disimpan. Tidak dipilih: formulir bertanda tangan disimpan 5 tahun, atau tanpa perubahan.
3. **Ringkasan penilaian dampak ditulis di 22**, penilaian lengkap dilampirkan. Tidak dipilih: dokumen terpisah nanti.
4. **Pengecualian untuk penelitian tidak dipakai**: semua hak dijalankan sampai studi ditutup, pilihan A di T10 berarti persetujuan untuk data yang sudah ada tetap berlaku, dan batas 3 × 24 jam dipakai. Tidak dipilih: memakai Pasal 148 ayat (1) huruf e.

Diterapkan: 22 → *Isi surat orang tua*, *Mengundurkan diri dan hapus data*, *Kerahasiaan dan pengelolaan data* (subbagian baru *Pengendali, prosesor, dan narahubung data* dan *Penilaian dampak pelindungan data*, tempat data di Indonesia, daftar izin berkode, *Dasar hukum* ditulis ulang), *Pelaporan kejadian tak diinginkan* (langkah kebocoran), *Keputusan pengembang* no. 14–17; 23 → T1 bagian 3 dan 6, T2, T8a, T10 (pilihan D), tempat kosong (`[kontak PPDP atau narahubung]`, `[nama dan lokasi penyedia server]`; `[jumlah hari kerja]` dihapus), *Keputusan pengembang* no. 16–19; 02 → L7 (butir batas pengamanan daring yang sudah disetujui sebelumnya, isi surat dari PP 33/2026, verifikasi izin, daftar izin berkode).

Akibat yang belum dibawa (bagian sesi BRIN `r10-klirens-brin` atau dokumen lain; tercatat di [22 → Akibat ke dokumen lain](22_PERMOHONAN_IZIN_ETIK.md#akibat-ke-dokumen-lain)):
- 22 → *Lampiran*: penilaian dampak lengkap sebagai lampiran; 22 → *Daftar tilik tujuh standar*, standar 6: rujukan ke subbagian baru.
- 23 → *Cek isi L7 dan jalur persetujuan*: baris untuk butir L7 yang baru; 23 → T1 bagian 8: kontak PPDP boleh dipindah ke sana (tidak wajib).
- 01 → *Catatan perubahan sistem*: ambil data satu anak untuk permintaan akses, pembetulan data pendaftaran, penghapusan termasuk salinan cadangan, ekspor daftar izin berkode.
- 11 → *L7*: daftar izin berkode menjadi sumber `consent_ok` setelah L7 dimusnahkan.

Yang masih harus dicek sebelum permohonan diajukan: nomor pasal terhadap naskah resmi PP 33/2026 dan UU 27/2022, Peraturan Lembaga yang sudah terbit (PPDP, penilaian dampak, kebocoran, penghapusan), apakah lembaga pelindungan data pribadi sudah berjalan, dan apakah 3 × 24 jam dihitung dengan hari libur.

**Keputusan pengembang: 22 dan 23 diselaraskan dengan pedoman KEPPKN 2021** (cabang `sky/friendly-newton-jjuvj0`) · Prototipe I, belum merevisi produk

Pengembang memberi tautan *Pedoman dan Standar Etik Penelitian dan Pengembangan Kesehatan Nasional* (PSEPPKN) di situs KEPPKN. Isinya **edisi 2021** (KEPPKN periode 2021–2024, prakata 20 Juni 2021; berkasnya diunggah ulang Agustus 2025), sedangkan 22 masih merujuk edisi 2017. Naskah lengkapnya dibaca dan dibandingkan dengan 22, 23, dan 24.

Temuan pokok:

1. **Edisi dan nama.** Edisi 2021 ditulis *Komite* (bukan *Komisi*) Etik Penelitian dan Pengembangan Kesehatan Nasional. Tanda `needs_verification` tentang versi pedoman di 22 ditutup.
2. **Delapan kriteria di teks, tujuh di penilaian.** Bab III Standar 7 menambah *8. Pelindungan subjek rentan*. Daftar tilik (Lampiran 3) dan format protokol (Lampiran 1) masih memakai tujuh standar, dan Lampiran 1 masih menyebut "Pedoman KEPPKN 2017".
3. **Tingkat telaah.** Protokol dengan anak dibahas lengkap (*full board*). Data berkode yang bisa dihubungkan ke nama membuat penelitian ini tidak bisa dikecualikan.
4. **Masa berlaku satu tahun.** Rentang KG1–KG5 lebih dari setahun, jadi perpanjangan diajukan setiap tahun bersama laporan kemajuan.
5. **Kewajiban laporan:** laporan kemajuan paling sedikit sekali setahun, laporan akhir, KTD, dan penyimpangan; notifikasi cukup untuk perubahan yang tidak menambah risiko.
6. **Format protokol resmi dan daftar tilik.** Dibandingkan dengan Lampiran 1, 22 belum memuat aturan penghentian, rencana pelibatan masyarakat, hak atas data, komitmen etik, risiko bagi peneliti dan kelompok, dan simpulan risiko dan manfaat. Daftar tilik Lampiran 3 diisi peneliti sebelum protokol diajukan.
7. **Peneliti mahasiswa:** pembimbing yang mengirim permohonan dan mendampingi saat telaah lengkap.
8. **Surat orang tua** (Lampiran 2 dan indikator 7.3):
   - penelitian dengan alat daring yang melibatkan kelompok rentan harus menyebut upaya pengamanan dan batasnya (Lampiran 2 huruf i, CIOMS pedoman 22);
   - jenis pemberian harus disebut;
   - jumlah peserta, kontak cadangan, sumber dana, dan saksi juga diminta.

Yang sudah sesuai dan tidak diubah:
- persetujuan berlapis dan persetujuan ulang di P2–P5;
- tanpa penipuan, dan mundur tanpa sanksi;
- data berkode dengan kunci terpisah, serta penyimpanan terkunci dan terenkripsi;
- tanpa uang sebagai bujukan;
- dua orang dewasa dan tempat yang terlihat;
- prosedur rujukan dengan batas kerahasiaan;
- hasil kembali ke sekolah;
- protokol uji keterbacaan (24).

Pedoman tidak menetapkan masa simpan minimum dan menyebut komite tidak mengharuskan data dimusnahkan, jadi masa simpan di 22 tetap.

Keputusan pengembang (27 Oktober 2026):

1. **Semua usulan diterapkan:** sebelas perubahan di 22 dan empat di 23, dengan T8a dan T8b ikut di bagian yang berlaku.
2. **Tanda terima kasih disebut dalam satu kalimat netral** di 23 → T1 bagian 7 dan T8a: tanpa uang, tanpa biaya, dan sama untuk semua anak di kelas, ikut atau tidak, ditambah kudapan di P1 dan P5. Tidak dipilih: tetap tidak disebut di surat.
3. **Surat bisa dibacakan guru kelas atau peneliti.** Formulir T2 dan T8b mendapat baris saksi yang diisi hanya dalam keadaan itu, oleh orang di luar tim peneliti, dan tanda tangan boleh diganti cap jempol. Tidak dipilih: tanpa perubahan.
4. **Ketua peneliti mahasiswa (S2/S3).** 22 mendapat baris pembimbing/promotor; pembimbing mengirim permohonan dan mendampingi ketua peneliti saat telaah lengkap.
5. **Putaran tersendiri nanti:** panduan klirens etik bidang sosial humaniora BRIN (November 2025), kalau komite yang dipilih menelaah penelitian sosial atau pendidikan, dan PP No. 33 Tahun 2026 (peraturan pelaksana UU PDP, diundangkan 16 Juli 2026, berlaku 16 Januari 2027) untuk *Dasar hukum* dan batas pemberitahuan kebocoran di 22.

Aturan pelaksanaan yang ditulis sesi ini, bukan dari pedoman (bisa diubah pengembang):
- perpanjangan diajukan paling lambat satu bulan sebelum masa berlaku habis;
- sesi kelas ditunda kalau kurang dari dua peneliti hadir;
- setiap pembukaan kode oleh ketua peneliti dicatat tanggal dan alasannya;
- kalau sekolah menarik izin, data yang sudah terkumpul mengikuti pilihan tiap orang tua di 23 → T10.

Akibatnya:
- **22** mendapat:
  - rujukan edisi 2021 dan baris standar 8;
  - format protokol resmi;
  - *Tingkat telaah dan masa berlaku persetujuan*, *Laporan berkala dan laporan akhir*, dan *Penghentian kegiatan dan penelitian*;
  - notifikasi di *Perubahan protokol*;
  - risiko bagi kelompok dan peneliti, dengan simpulan;
  - pelibatan sekolah dan masyarakat, dan alasan melibatkan anak;
  - aturan membuka kode;
  - hasil diterbitkan apa pun hasilnya, dan hak atas data;
  - *Daftar tilik tujuh standar* dan *Komitmen etik* (lampiran 13 dan 14);
  - baris pembimbing;
  - nomor standar dan pedoman CIOMS di isu etik.
- **23** mendapat:
  - tambahan di T1, T2, T8, T8a, T8b, dan T9;
  - tempat kosong `[nomor cadangan]`, `[sumber dana]`, dan `[jenis tanda terima kasih]`;
  - baris baru di *Cek isi L7*;
  - hitung ulang keterbacaan: T1 13,0 kata per kalimat dan ± 11 menit; T2 13,0; T8a 10,9; T8b 12,2.
- **00** dan `docs/PROGRESS.md` diperbarui.
- **02 → L7** perlu butir isi surat *batas pengamanan daring*; ini dicatat di *Akibat ke dokumen lain* di 22 dan belum diubah.

**Keputusan pengembang: ketiga instrumen validasi ahli disetujui** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator) · Prototipe I, belum merevisi produk

1. **Instrumen ahli materi v2.1, pengamat budaya v2.1, dan ahli media v1.1 disetujui** apa adanya, dalam versi yang sudah diselaraskan dengan 20 dan 21 (entri di bawah).
2. Kotak status draf di sampul ketiganya dihapus (langkah 2 di `dokumen-pdf/README.md`), perubahannya dicatat di D.4 masing-masing, PDF dibuat ulang (43, 31, dan 29 halaman), dan berkas validator dibuat ulang karena HTML instrumen termasuk sumbernya (69 pemeriksaan lolos).

Akibatnya: prasyarat no. 1 dan 2 di 20 terpenuhi; 07, 00, README `dokumen-pdf/`, dan `docs/PROGRESS.md` diperbarui. 00 mendapat *Langkah berikutnya di tahap 2* sebagai pintu masuk sesi baru. Gerbang G1 tinggal menunggu daftar kode sekolah (no. 12), yang tidak menahan validasi maupun izin etik.

**Keputusan pengembang: satu level pengembang per butir tes D2 dan D3** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator; temuan no. 2 di [21](21_BERKAS_BUTIR_VALIDATOR.md)) · Prototipe I, belum merevisi butir

1. **Usulan sesi koordinator disetujui apa adanya.** Setiap nomor tes D2 dan D3 punya satu level pengembang, sama di bentuk A dan B, dengan arti level B.5a (L1 tanda jelas, L2 tanda samar, L3 nilai bertentangan atau dua langkah), sama seperti kata *Tingkat* di 08:
   - **D2:** nomor 1, 3, 5, 7 L1; nomor 2 dan 4 L2; nomor 6 (niat baik berhadapan dengan izin teman) dan 8 (pesan berantai dua langkah) L3.
   - **D3:** butir 1–4 (adegan G1) L1; butir 5 (atribusi) dan 7 (izin) L2; butir 6 (keliru tapi populer) dan 8 (benar tapi kasar) L3.
   - Sebarannya L1 empat, L2 dua, L3 dua di setiap bagian.
2. Tidak dipilih: nomor 4 D2 dinaikkan ke L3 (kesetiakawanan sebagai nilai yang bertentangan), atau level dibiarkan usulan sampai label ahli kembali.

Akibatnya: 15 mendapat bagian *Level pengembang* (alasan per nomor); 13 (tabel D2 dan D3) memakai level ini; skrip `berkas-validator.php` membaca level dari tabel itu dan memeriksanya (69 pemeriksaan lolos); berkas kunci B.5a tidak lagi berisi rentang; 21 (temuan no. 2) dan 00 diperbarui. Kelima sesi paralel tahap 2 diarsipkan atas permintaan pengembang.

**Keputusan pengembang atas lima hal yang tersisa dari penyelarasan, dan pembaruan instrumen** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator, PR #268) · Prototipe I, belum merevisi produk

Keputusan pengembang (27 Oktober 2026):

1. **Perkiraan waktu B.7 3–5 jam** (622 sel kode) disetujui.
2. **T1 tidak diuji baca oleh orang tua** di luar sampel sebelum permohonan etik dikirim.
3. **Pesan dukungan (23 → T12) untuk semua siswa di P5**, juga yang tanpa izin butir risiko daring, di titik yang sama.
4. **Selebaran tempat bercerita (23 → T13) dibagikan kepada semua anak di kelas di akhir P5**, bersama tanda terima kasih.
5. **Pengodean buta B.7 oleh Ahli Literasi Digital 2 diterima apa adanya**: bank berkode tetap ada di kiriman awalnya, jadi versi buta B.7 tidak sepenuhnya buta bagi validator itu. Kiriman tidak diubah.

Akibatnya: 16, 20, 22, 23, 24, dan 01 (perubahan sistem no. 23) diperbarui.

Pembaruan instrumen (atas permintaan pengembang, dari *Akibat ke dokumen lain* di 20 dan 21): instrumen ahli materi v2.1 (A.3 dan A.4: penilai B.5a, paket B.5a lebih dulu, pengarahan B.5a ± 15 menit, B.7 3–5 jam; B.5a: ID butir dan ID bahan netral yang diacak; D.3: nama berkas lembar kerja dan urutan kirim), pengamat budaya v2.1 (D.3 no. 11), dan ahli media v1.1 (D.3 no. 9: lembar kerja hanya B.8). Ketiganya memakai kalimat honorarium "sesuai ketentuan yang berlaku" dan menyebut uji keterbacaan empat anak ditambah satu. Nama topik B.6 dan benda sasaran B.6b di instrumen dipertahankan karena isinya sama dengan 08 (pilihan sesi koordinator; dicatat di D.4 pengamat budaya). Perubahan dicatat di D.4 setiap instrumen, PDF dibuat ulang (ahli materi kini 43 halaman), dan lembar kerja validator dibuat ulang (68 pemeriksaan lolos). Status ketiga instrumen tetap draf, menunggu persetujuan pengembang.

Masih terbuka: satu level pengembang untuk setiap butir tes D2 dan D3 di 13 dan 15 (sekarang rentang L1–L2 atau L2–L3), yang dibutuhkan analisis C.4 ahli materi.

**Penyelarasan dokumen tahap 2 (20–24) dan keputusan pengembang** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator, PR #268) · Prototipe I, belum merevisi produk

Kelima sesi paralel selesai pada hari yang sama, masing-masing dengan jawaban pengembang sendiri. Sesi koordinator memeriksa kelima dokumen bersama-sama dan menemukan satu keputusan yang bertentangan, beberapa keputusan yang belum terbawa ke dokumen lain, dan kode yang bertabrakan.

Keputusan pengembang (27 Oktober 2026):

1. **Batas permintaan hapus data: sampai studi ditutup**, mengikuti 22. Nama, L7, dan formulir dimusnahkan saat studi ditutup; hasil gabungan yang sudah terbit tidak bisa ditarik. Aturan di 23 ("sampai analisis utama dimulai") diganti. Tidak dipilih: gabungan dua aturan (permintaan sesudah analisis utama dimulai tetap menghapus data mentah), dan aturan 23.
2. **Formulir mundur T10 tetap tiga pilihan:** A berhenti dan data tetap dipakai, B berhenti dan data dihapus, C tetap ikut tetapi menarik rekaman atau jawaban butir risiko. L7 di 02 mendapat dua status, `Mengundurkan diri (data tetap)` dan `Mengundurkan diri (hapus data)`; mulai `kamus-1.1`, status `data tetap` dihitung `consent_ok = 1` untuk data sampai tanggal mundur. Hapus sebagian menjadi perubahan sistem no. 23. Tidak dipilih: hanya B dan C (seperti L7 lama), atau hanya A dan B.
3. **Anak uji keterbacaan mendapat tanda terima kasih kecil yang sama** dengan anak di kelas, setelah sesi terakhirnya, tidak disebut sebagai hadiah. Tidak dipilih: ditambah kudapan setiap sesi, atau tanpa hadiah.
4. **Akun `pengamat`, `pewawancara`, dan `ketua` disetujui** (sudah dicatat di entri koordinator di bawah).
5. **Keempat PR dokumen 21–24 digabung lewat satu PR koordinator** (#268), supaya konflik di 00, 04, dan PROGRESS diselesaikan sekali dan dokumen diselaraskan sebelum masuk `main`. Tidak dipilih: menggabung satu per satu.

Diselaraskan sesi koordinator tanpa keputusan baru:

- **Kode:** kegiatan dengan anak di 22 menjadi `KG1`–`KG5` (dulu `K1`–`K5`, bertabrakan dengan kode anak `K01`–`K05` di 24); kode sesi uji keterbacaan menjadi `K01-1` (dulu `K01-S1`, terbaca seperti kode sekolah). Label `T1`–`T13` di 23 dan `PB-*` di 24 tetap label di dalam dokumennya.
- **Keputusan 22 dibawa ke 23:** berbagi data berkode atas persetujuan komite, masa simpan, batas kerahasiaan, kontak komite, dan tiga teks baru untuk anak (T11 layar izin ulang sebelum butir risiko, T12 pesan dukungan, T13 selebaran tempat bercerita).
- **Keputusan 24 dibawa ke 22 dan 23:** empat anak ditambah satu, empat sesi ± 60 menit di sekolah anak, butir risiko daring diparafrasekan saja, dan sekolah uji keterbacaan tidak ikut sampel nanti. Nama dan tingkat membaca anak baru sampai ke peneliti setelah izin orang tua kembali.
- **Keputusan 21 dibawa ke 20:** dua penilai B.5a menerima paket B.5a saja lebih dulu; nama berkas dari 21; perkiraan waktu B.7 dinaikkan.
- **Keputusan 23 dibawa ke 22:** amplop H-14 sampai H-10, pengingat H-5, batas kembali H-3; ringkasan hasil lewat sekolah; pengingat persetujuan di P2–P5.
- **Janji "nama hanya di dua tempat"** di 22 dan 23 diganti aturan yang benar: nama tidak pernah ada di data penelitian; nama hanya di akun (admin) dan di berkas izin, mundur, dan rujukan yang disimpan terkunci terpisah.
- **Dokumen bersama:** 01 (daftar kerja no. 3, 14, 15, 23, pertanyaan izin etik, perubahan sistem no. 23 baru), 02 (L0, L5, L7; bagian *Perubahan setelah persetujuan*), 03 (ketua peneliti, *Kegiatan dengan anak*, ID netral B.5a, *Uji keterbacaan*), 06, 13–18 (tautan ke 24 dan 22; kalimat 16 "namamu tidak ditulis di mana pun" diganti "Jawabanmu disimpan dengan kode, bukan dengan namamu"), dan README `dokumen-pdf/`.

Yang menyusul di PR koordinator berikutnya: perubahan HTML ketiga instrumen dari 21 (ID netral di B.5a, nama berkas dan urutan kirim di D.3, waktu B.7), level tunggal untuk butir tes D2 dan D3 di 13 dan 15, dan nama topik B.6 dan benda B.6b yang berbeda dengan 08.

Menunggu konfirmasi pengembang: perkiraan waktu B.7 3–5 jam (dulu 2–3 jam) di 20; cara menjaga pengodean buta B.7 oleh Ahli Literasi Digital 2, yang menerima bank berkode di kiriman awal; uji baca T1 oleh dua–tiga orang tua di luar sampel (usulan 23); dan dua usulan di 23, yaitu T12 juga untuk anak tanpa izin butir risiko, dan T13 dibagikan ke semua anak di akhir P5 supaya tidak ada yang tampak berbeda. Kamus data `kamus-1.0` tidak diubah; `consent_ok` untuk `data tetap` dan aturan letak nama masuk `kamus-1.1`.

**Draf protokol uji keterbacaan, daftar kerja no. 23** (cabang `r10-uji-keterbacaan`, sesi paralel tahap 2; Prototipe I)

Status: **draf; keempat pertanyaan diputuskan pengembang di hari yang sama** (lihat di bawah). Dokumen baru [24_UJI_KETERBACAAN.md](24_UJI_KETERBACAAN.md) berisi protokol uji keterbacaan dengan anak kelas 5, yang dijalankan setelah izin etik terbit dan setelah bahan direvisi dari validasi ahli. Entri ini tidak merevisi prototipe; revisi dari hasil uji nanti dicatat sebagai "Prototipe I, revisi dari uji keterbacaan".

Yang ditulis: tujuan dan arti "terbaca"; syarat dan cara memilih anak (guru hanya mengelompokkan kelancaran membaca, tanpa nilai); daftar bahan dari 08 dan 12–18, dengan alasan bahan yang tidak diuji (Temukan Benda, keterangan Susun Gambar, cerita penghubung yang dibacakan, panel peneliti); pembagian bahan; perangkat (laptop, salinan layar statis dari sumber mock-up 18, tanpa ponsel, tanpa suara); empat sesi per anak dengan langkah baca sendiri, parafrase, pancingan baku `PB-1`–`PB-9`, menandai kata, dan kartu kata; peran (P3 pemandu, P2 pencatat, P1 persiapan, ketua peneliti penanggung jawab); lembar LK1–LK3 dan kode kesulitan `KB-*`; aturan keputusan dan yang kembali ke ahli; cara waktu yang diukur dibandingkan dengan 06, 13, dan 15; pengelolaan data; yang dibutuhkan dari 20, 22, dan 23.

Pilihan yang diambil saat menulis:
1. **Empat anak di putaran pertama, satu anak lagi untuk putaran kedua** bahan yang direvisi.
2. **Inti untuk semua anak** (satu bentuk tes lengkap, angket, skala sikap, kartu jangkar, kartu kata, salinan layar), **bank dibagi enam blok** wilayah × variasi, setiap blok dibaca dua anak.
3. **Parafrase sesudah membaca, bukan berpikir lantang selama membaca**, supaya waktu baca bisa dipakai; berpikir lantang tetap di uji coba tahap 4 (L5).
4. **Ambang keputusan dibedakan:** bacaan D1 hanya direvisi untuk kata di luar sasaran butir, pokok soal, pilihan, dan petunjuk; petunjuk direvisi kalau satu anak saja tidak paham; butir negatif skala direvisi kalau satu anak menjawab berlawanan.
5. **Batas waktu tes tidak diubah dari uji ini**; angka waktunya hanya memberi catatan ke 06, 13, dan 15 sampai uji coba.
6. **Butir risiko daring diuji dengan parafrase saja**, tanpa jawaban tentang diri sendiri, dan hanya dengan izin terpisah.
7. Ambang berhenti lama (± 10 detik) dan lama membaca sebelum jeda ditandai `needs_verification`.

**Keputusan pengembang (27 Oktober 2026, satu putaran, semuanya usulan):** (1) inti untuk semua anak dan bank dibagi enam blok, empat sesi per anak; (2) empat anak, ditambah satu anak untuk putaran kedua bahan yang direvisi; (3) anak dari sekolah di wilayah Kedu yang tidak masuk sampel dan bukan sekolah uji coba; (4) butir risiko daring diuji dengan parafrase saja, dengan izin terpisah. Dicatat di [24, Keputusan pengembang](24_UJI_KETERBACAAN.md#keputusan-pengembang). Perubahan yang dibutuhkan di 01, 03, 06, 13–18, 20, 22, dan 23 didaftar di [24, Akibat ke dokumen lain](24_UJI_KETERBACAAN.md#akibat-ke-dokumen-lain) dan dibawa sesi koordinator.

**Surat informasi dan persetujuan ditulis (draf)** (cabang `r10-surat-izin`, daftar kerja no. 15, [23](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md); Prototipe I, dokumen tahap 2)

Status: draf; empat pertanyaan pengembang dijawab (di bawah). Yang ditulis, semua sebagai blok siap cetak dengan tempat kosong bertanda:
- T1–T2 surat informasi dan formulir orang tua, dengan kotak Ya/Tidak terpisah untuk ikut, rekaman suara, dan butir risiko daring. Semua isi wajib surat di 02 → L7 ada di T1–T2 (tabel cek di 23).
- T3 naskah persetujuan anak di pembukaan P1, T4 usulan teks centang persetujuan di halaman daftar untuk mode penelitian (file bahasa tidak diubah), T5 pengingat singkat di P2–P5 dan sebelum wawancara L5.
- T6 surat kepala sekolah dengan permintaan data L8, T7 lembar guru kelas.
- T8 varian uji keterbacaan, T9 varian kelas uji coba (awalan `U`, berpikir lantang), T10 formulir pengunduran diri dan hapus data.
- Tabel pembagian (siapa, kapan, disimpan di mana, mengikuti aturan simpan L7), catatan keterbacaan per teks.

Pilihan yang diambil saat menulis (rinciannya di [23 → Pilihan yang diambil saat menulis](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#pilihan-yang-diambil-saat-menulis)): formulir yang tidak kembali dibaca tidak mengizinkan; kotak tambahan yang kosong dibaca Tidak; amplop tertutup H-14 sampai H-10 dengan batas kembali H-3; tanpa foto dan video; persetujuan anak diulang singkat di P2–P5; anak yang menolak di P1 dicatat `Tanpa izin`; T10 memberi pilihan berhenti tanpa menghapus data, yang butuh penyesuaian `consent_ok`.

Keputusan pengembang (wawancara singkat, 27 Oktober 2026; semuanya usulan, rinciannya di [23 → Keputusan pengembang](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#keputusan-pengembang)):
1. **Permintaan hapus data boleh diajukan sampai analisis utama dimulai.** *(Diganti pada hari yang sama: sampai studi ditutup, lihat entri penyelarasan di atas.)* Sesudahnya data anak tetap dihapus, tetapi angka gabungan yang sudah dihitung atau terbit tidak berubah. Tidak dipilih: kapan saja tanpa batas.
2. **Anak uji keterbacaan ikut membaca butir risiko daring, dengan centang terpisah**, dan hanya menilai kalimatnya. Tidak dipilih: hanya telaah ahli.
3. **Tanpa centang ringkasan hasil untuk orang tua;** ringkasan angka gabungan ke sekolah, dan orang tua memintanya lewat sekolah. Tidak dipilih: centang keempat.
4. **Kenang-kenangan kecil yang sama untuk seluruh kelas**, termasuk yang tidak ikut, di akhir P5, tidak disebut sebagai imbalan. Tidak dipilih: tanpa kenang-kenangan, atau hanya untuk peserta.

Akibat ke 01, 02, 11, 22, 24, dan 03 dicatat di [23 → Akibat ke dokumen lain](23_SURAT_INFORMASI_DAN_PERSETUJUAN.md#akibat-ke-dokumen-lain) dan dibawa sesi koordinator setelah PR digabung. Entri ini tidak merevisi bank, instrumen, atau sistem.

**Draf permohonan izin etik: [22_PERMOHONAN_IZIN_ETIK.md](22_PERMOHONAN_IZIN_ETIK.md)** (cabang `r10-izin-etik`, sesi paralel tahap 2; daftar kerja no. 15) · **Status: draf; keputusan pengembang atas empat pertanyaannya diambil hari yang sama**

Prototipe I: dokumen ini tidak merevisi bank, instrumen, atau mock-up; ia menjelaskan bagaimana Prototipe I sampai III dan data utama akan dijalankan dengan anak.

1. **Yang ditulis.** Protokol lengkap untuk *Lembaga Komite Etik*: identitas dan tim (nama samaran dan tempat kosong), ringkasan, nilai sosial, tujuan, desain Plomp dengan Prototipe I–III, lima kegiatan dengan anak (K1 uji keterbacaan, K2 berpikir lantang, K3 uji coba satu kelas `U`, K4 wawancara retrospektif, K5 data utama ± 360 siswa, ± 13 kelas, target 300), peserta, rekrutmen, lapis persetujuan, prosedur per pertemuan dan beban anak (paling lama ± 10 jam dalam lima pertemuan, dari 06 dan 13), risiko dan mitigasi, prosedur rujukan butir risiko daring, manfaat, kerahasiaan dan pengelolaan data, kompensasi, konflik kepentingan, KTD, perubahan protokol, lampiran, dan pemetaan ke tujuh standar KEPPKN dan ke bagian formulir umum.
2. **Pilihan yang dibuat saat menulis** (usulan, bisa diubah pengembang):
   - Penanda rujukan mengikuti keputusan 26 Oktober 2026: setiap anak yang menjawab *Pernah* di ang-21. Sapaan pribadi oleh ketua peneliti paling lambat hari sekolah berikutnya, didahulukan untuk *Beberapa kali atau lebih* atau yang tidak bercerita kepada siapa pun. Orang tua hanya dihubungi dengan persetujuan anak, kecuali ada bahaya serius. Penanda dan catatan rujukan di luar data penelitian, hanya terlihat ketua peneliti.
   - Batas kerahasiaan disebut jujur di surat orang tua dan kalimat anak sebelum butir risiko (akibat ke 02, 16, dan 23).
   - Penolakan anak didahulukan dari izin orang tua; persetujuan ulang singkat sebelum wawancara dan butir risiko; wawancara di tempat yang terlihat.
   - Usulan satu pertemuan per hari; data penelitian disimpan 5 tahun sesuai `retention_days` bawaan sistem.
   - Rujukan luar yang belum dicek ditandai `needs_verification`: rumusan dan versi pedoman KEPPKN, UU No. 27 Tahun 2022 (data anak, pemberitahuan kebocoran), layanan SAPA 129 dan UPTD PPA, batas waktu laporan KTD dan aturan amandemen komite.
3. **Keputusan pengembang atas empat pertanyaan draf (27 Oktober 2026, wawancara satu putaran):**
   - **Nama dan L7 dimusnahkan saat studi ditutup** (usulan draf: 12 bulan setelah P5 terakhir). Permintaan hapus diterima sampai studi ditutup; data berkode disimpan 5 tahun.
   - **Data per anak berkode dibagikan atas permintaan, dengan persetujuan komite** (usulan draf: tidak dibagikan). Tidak ada unggahan publik; sebelum dibagikan kode diganti kode acak baru, kode sekolah dan rombel dihapus, dan jawaban butir risiko serta transkrip tidak ikut. Surat orang tua (23) harus menyebutnya.
   - **Tanda terima kasih kecil yang sama untuk semua anak di kelas**, termasuk yang tidak ikut, plus kudapan di P1 dan P5; tanpa uang (sesuai usulan).
   - **Satu permohonan untuk K1–K5**, revisi instrumen dilaporkan sebagai perubahan protokol (sesuai usulan).

   Status 22 tetap draf: diajukan setelah dokumen tahap 1 yang dilampirkan disetujui dan formulir komite sebenarnya diketahui.
4. **Disesuaikan dengan 20 (PR #263, catatan sesi koordinator):** bahan untuk anak yang dilampirkan adalah versi yang dibekukan untuk validasi ahli, dan setiap revisi Prototipe I (atau sesudahnya) yang mengubah bahan anak diajukan ke *Lembaga Komite Etik* sebagai amandemen protokol. Nama samaran akun staf `pengamat`, `pewawancara`, dan `ketua` tidak lagi ditandai usulan.

Akibat ke dokumen lain (01, 02, 03, 16, 17, 23, 24) dicatat di 22, bagian *Akibat ke dokumen lain*, dan dibawa sesi koordinator setelah digabung.

**Berkas butir untuk validator: draf 21 dan skrip pembuatnya** (cabang `r10-berkas-validator`, sesi paralel tahap 2; Prototipe I, belum merevisi produk) · **Status: draf; keputusan pengembang diambil 27 Oktober 2026**

1. **Yang ditulis.** [21_BERKAS_BUTIR_VALIDATOR.md](21_BERKAS_BUTIR_VALIDATOR.md) dan skrip `dokumen-pdf/src/berkas-validator.php`. Skrip membaca 03, 08, 14–18 dan tabel ketiga instrumen (tanpa mengubahnya), lalu menulis berkas di `dokumen-pdf/berkas-validator/`:
   - daftar buta B.5a (lembar kerja dan buklet PDF) dan kuncinya di `khusus-peneliti/`;
   - daftar B.5b;
   - lembar kerja B.6–B.9 ahli materi, dan B.7 versi pengodean buta;
   - lembar kerja B.5–B.8 pengamat budaya;
   - lembar kerja B.8 ahli media;
   - laporan pemeriksaan `LAPORAN.md`.

   Jumlahnya: 180 butir B.5a dan B.5b (116 bank, 64 tes), 24 skenario, 622 sel kode, 12 pernyataan sikap, 38 pasangan A–B, 20 butir angket, 46 objek budaya, 24 petunjuk, 12 butir PBK, 19 fakta, dan 27 gambar mock-up. Semua 68 pemeriksaan lolos.
2. **Pilihan saat menulis**, semuanya pilihan yang disarankan di pertanyaan 21:
   - ID netral `BT-…` dan `BH-…`, dengan bahan diacak dengan benih tetap;
   - kunci jawaban dicantumkan di daftar buta, kode kesalahan dan label tidak;
   - bagian ketuk dinamai "Bagian 1, 2, …";
   - B.7 berisi semua 622 sel dengan kolom "Asal kode";
   - level pengembang *Periksa Unggahan* dan Skenario Digital diturunkan dari baris *Tingkat* 08 per wilayah.
3. **Temuan untuk pengembang** (rinciannya di 21):
   - paket awal di D.3 ahli materi membocorkan dimensi dan sub-aspek ke dua penilai B.5a lewat kode kesalahan dan beberapa bagian instrumen;
   - level pengembang butir tes D2 dan D3 berupa rentang;
   - B.7 lebih panjang dari perkiraan 2–3 jam;
   - nama topik B.6 dan nama benda B.6b berbeda kata dengan 08;
   - lembar kerja B.3–B.7 ahli media belum dibuat.
4. **Keputusan pengembang (27 Oktober 2026), keempatnya pilihan yang disarankan:**
   - daftar B.5a memakai ID netral yang diacak, dengan berkas kunci khusus peneliti;
   - dua penilai B.5a menerima paket B.5a saja dulu; instrumen lengkap, bank berkode, kisi-kisi, B.5b, dan lembar B.6–B.9 dikirim setelah B.5a kembali;
   - kunci jawaban dicantumkan di daftar buta;
   - B.7 mencakup semua 622 sel, dan perkiraan waktunya dinaikkan.

   Berkas sudah memakai pilihan ini, jadi tidak dibuat ulang. Urutan kirim dan waktu B.7 masuk ke 20 dan instrumen ahli materi lewat sesi koordinator.

Akibatnya: 00 (baris 21), `dokumen-pdf/README.md` (tabel *Isi* dan bagian *Membuat ulang berkas validator*), dan `docs/PROGRESS.md` diperbarui. Perubahan untuk 01, 03, 08, 13, 15, 20, dan ketiga instrumen dicatat di 21 → *Akibat ke dokumen lain* untuk sesi koordinator.

**Draf paket validasi ahli: [20_PAKET_VALIDASI.md](20_PAKET_VALIDASI.md)** (cabang `r10-paket-validasi`, sesi paralel tahap 2; daftar kerja no. 3 dan 14) · Prototipe I · Status: draf; empat pertanyaan dijawab pengembang pada hari yang sama (lihat akhir entri)

Yang ditulis: prasyarat kirim, tabel siapa menerima apa per nama samaran (lembar dari matriks penugasan, nomor berkas dari D.3 ketiga instrumen, perkiraan jam, putaran kirim), jadwal relatif dua belas minggu dengan usulan tanggal mulai 9 November 2026, tiga surat permohonan, surat lanjutan setelah B.5a, pengingat dan surat putaran 2, usulan akun siswa uji validator, cara pengembalian dan pencatatan, templat matriks revisi induk, aturan kapan butir kembali ke validator, cara mencatat revisi di 04, dan daftar periksa validasi untuk G2.

Pilihan yang diambil saat menulis (semua bisa diubah pengembang):

1. **Penilai B.5a dalam draf: Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1**, mengikuti saran IM A.3. Ditanyakan ke pengembang.
2. **Label pengembang dan kisi-kisi ditahan untuk kelima ahli materi** sampai B.5a dikumpulkan, bukan hanya untuk dua penilainya, sesuai IM D.3 no. 4–6 dan supaya tidak ada salinan berlabel di panel. Lembar yang sudah selesai di putaran awal diminta tidak diubah setelah label dibuka.
3. **Akun validator adalah akun siswa uji**, bukan akun staf: `uji.bahasa1`–`2`, `uji.digital1`–`2`, `uji.ukur`, `uji.budaya1`–`3`, `uji.media1`–`3`, dan dua cadangan, dibuat `admin`. Titik dipakai karena aturan nama pengguna siswa tidak mengizinkan tanda hubung.
4. **Daftar padanan nama samaran ↔ nama asli dan surat pernyataan disimpan di luar repo**, dipegang ketua peneliti; lembar kertas diketik dua kali oleh dua orang, lembar kerja digital dicek 10% baris acak.
5. **Satu matriks revisi induk** untuk ketiga instrumen; D.2 tiap instrumen adalah potongannya untuk putaran 2. Matriks induk menjadi dokumen baru saat hasil pertama kembali.
6. **Media langkah 2 belum punya instrumen**; disusun di tahap 3 untuk G3.

**Keputusan pengembang atas pertanyaan di 20** (27 Oktober 2026):

1. **Penilai B.5a: Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1** (sesuai usulan).
2. **Honorarium di surat ditulis "sesuai ketentuan yang berlaku", tanpa angka** (bukan usulan draf, yang menyebut besaran per panel). Besarannya dicatat ketua peneliti di luar repo.
3. **Paket dikirim digital**: folder bersama yang dibatasi per validator, surat pernyataan dipindai, kertas hanya atas permintaan (sesuai usulan).
4. **Ahli media langkah 1 tidak menunggu L8**: dikirim bersama panel lain begitu instrumen disetujui, dengan daftar jenis perangkat sebagai pengganti L8 (sesuai usulan).

20 diperbarui: bagian *Pertanyaan untuk pengembang* menjadi *Keputusan pengembang*, dan teks surat memakai kalimat honorarium yang baru. Perubahan yang dibutuhkan di 01, 03, 07, README `dokumen-pdf/`, 22, dan 24 ada di bagian *Akibat ke dokumen lain* di 20, dibawa sesi koordinator setelah digabung. Entri ini tidak merevisi isi prototipe; ia menyiapkan validasi Prototipe I.

**Keputusan pengembang: daftar kerja no. 12 dan 13, dan dimulainya dokumen tahap 2** (cabang `sky/inspiring-ritchie-nex4qi`, sesi koordinator)

1. **Daftar kode sekolah penelitian (no. 12) belum diputuskan; tahap 2 tetap dimulai.** Kode `S01`, `S02`, … di semua dokumen tetap contoh, dan tempat kosongnya di 02 tetap ditandai. Daftar ini baru dibutuhkan sebelum sekolah direkrut dan L8 diisi, jadi tidak menahan dokumen validasi dan izin etik.
2. **Pemegang akun admin dan akun peneliti (no. 13) sementara memakai nama samaran sesuai perannya:** akun admin `admin`, akun peneliti untuk koordinator sesi (P1) `koordinator`, dan seterusnya. `pengamat` (P2), `pewawancara` (P3), dan `ketua` (ketua peneliti) diusulkan sesi koordinator untuk "dan seterusnya" dan **disetujui pengembang pada hari yang sama**; akunnya dibuat kalau perannya butuh akun ([03 → Akun staf](03_KODE_DAN_ARTI.md#akun-staf)). Siapa orangnya ditetapkan nanti dan dicatat ketua peneliti di luar repo, sama seperti nama asli validator.
3. **Dokumen tahap 2 ditulis paralel di beberapa sesi.** Sesi koordinator membaginya menjadi lima dokumen, satu sesi dan satu cabang masing-masing:

   | Dokumen | Cabang | Isi |
   | --- | --- | --- |
   | `20_PAKET_VALIDASI.md` | `r10-paket-validasi` | Siapa menerima apa dan kapan, surat permohonan, jadwal, pengembalian, matriks revisi, daftar periksa validasi di G2 |
   | `21_BERKAS_BUTIR_VALIDATOR.md` | `r10-berkas-validator` | Daftar butir buta B.5a, daftar B.5b, lembar kerja per lembar instrumen |
   | `22_PERMOHONAN_IZIN_ETIK.md` | `r10-izin-etik` | Protokol dan permohonan izin etik |
   | `23_SURAT_INFORMASI_DAN_PERSETUJUAN.md` | `r10-surat-izin` | Surat informasi dan persetujuan untuk orang tua, anak, sekolah, dan guru |
   | `24_UJI_KETERBACAAN.md` | `r10-uji-keterbacaan` | Protokol uji keterbacaan dengan 3–5 anak kelas 5 |

   Uji keterbacaan menjadi daftar kerja baru no. 23 di 01, karena sudah ada di peta jalan (tahap 2, Prototipe I) tetapi belum punya baris sendiri.

Gerbang G1 belum resmi terpenuhi: no. 12 masih terbuka, dan instrumen ahli materi v2.1, pengamat budaya v2.1, dan ahli media v1.1 masih menunggu persetujuan. Pengembang memilih tetap memulai tahap 2. Jadi dokumen tahap 2 boleh ditulis sekarang, tetapi yang dikirim ke luar (paket ke validator, permohonan ke *Lembaga Komite Etik*) tetap menunggu persetujuan dokumen yang dibutuhkannya.

Akibatnya: 00 (tahap dokumen 2, baris 02, baris dokumen 20–24, "sebelas validator" menggantikan "tujuh validator"), 01 (daftar kerja no. 3, 12, 13, 14, 15, dan no. 23 baru), 02 (tempat kosong akun P1), dan 03 (bagian *Akun staf*) diperbarui. Entri ini tidak merevisi prototipe.

## 26 Oktober 2026

**Instrumen validasi ahli ditulis ulang: ahli materi v2.1, pengamat budaya v2.1, ahli media v1.1** (cabang `pbb/dazzling-feynman-r7tljf`, [07 → Langkah berikutnya](07_TELAAH_INSTRUMEN_VALIDASI.md#langkah-berikutnya) no. 2 dan 4)

Pengembang menyerahkan draf r10 v2.0 dan media v1.0 (23 Oktober 2026) dan meminta:

1. **r10 dipecah menjadi dua dokumen**, satu per bidang validator: ahli materi dan pengamat budaya. Masing-masing punya panduan, analisis, dan lampiran sendiri.
2. **PDF penting disimpan di subfolder khusus** [dokumen-pdf/](dokumen-pdf/README.md), dengan sumber HTML di `src/` dan draf asli di `arsip/`.

Keputusan pengembang saat penulisan (wawancara singkat, 26 Oktober 2026):

3. **Panel pengamat budaya tiga orang** (Ahli Budaya Kedu 1–3), idealnya satu orang per wilayah. Sebelumnya hanya ada satu nama samaran, sehingga I-CVI dan Aiken's V tidak bisa dihitung untuk instrumen budaya.
4. **Panel ahli media tiga orang** (Ahli Media Pembelajaran 1–3): teknologi pendidikan atau media pembelajaran, desain visual atau UX, dan rekayasa perangkat lunak atau pengembangan game.

Jadi validatornya sebelas orang. Akibatnya, dicatat di instrumen dan di 03, 01, 13, 16, dan 17:

- Aturan Lynn berlaku untuk panel tiga orang: butir lolos hanya bila ketiganya memberi 3 atau 4.
- Butir teknis H3 di instrumen media menjadi daftar periksa lulus/tidak, karena hanya satu validator berlatar rekayasa perangkat lunak.
- Relevansi skala sikap (B.8) tetap dinilai empat orang seperti keputusan sebelumnya, dengan Ahli Budaya Kedu 1 sebagai pengamat budaya; Ahli Budaya Kedu 2 dan 3 hanya mengisi kolom "contoh budaya tepat" untuk wilayahnya.

Isi instrumen mengikuti semua perubahan di 07. Yang berbeda dari tulisan di 07, dan pilihan yang diambil saat menulis, dicatat di [07 → Instrumen versi 2.1 dan 1.1](07_TELAAH_INSTRUMEN_VALIDASI.md#instrumen-versi-21-dan-11). Yang terpenting:

- Telaah *Periksa Unggahan* mencakup 24 petunjuk, bukan 12.
- INF-BUD hanya untuk PBK.
- Butir PBK dinilai relevansinya oleh pengamat budaya.
- Ponsel dihapus dari matriks perangkat ahli media.
- Letak kompleks Candi Arjuna dan tahun kebun teh Tambi ditanyakan ke pengamat budaya.

Instrumen ini dipakai untuk menilai prototipe I (tahap 1–2, berbasis kertas dan mock-up), sesuai penomoran prototipe di entri berikutnya.

Sekalian dikerjakan dari entri *empat usulan dokumen model penelitian* di bawah, karena berkasnya dipegang cabang ini:
- "L0–L7" di 00 diganti "L0–L8", dan baris 02 di 00 ditambah "L8 draf".
- L8 Profil sekolah (bagian perangkat dan internet) masuk paket berkas ahli media (D.3 media v1.1).

`gambar/peta-jalan.svg`, `gambar/lapis-data.svg`, dan variabel L8 di `kamus-1.1` masih menyusul.

Status: draf, menunggu persetujuan pengembang. Kotak status di sampul dihapus sebelum dicetak untuk validator.

**Keputusan pengembang: empat usulan dokumen model penelitian** (cabang `r10-usulan-model-penelitian`, usulan yang belum diputuskan di entri *model penelitian dan pengembangan, dan aturan I-CVI* di bawah; alasannya di [dokumen pendukung peneliti](dokumen-pendukung-peneliti/README.md), bagian 7–9)

1. **Tes awal–akhir terutama bukti validitas kriteria; uji bedanya eksploratif.**
   - Kegunaan utama tes adalah korelasi skor tes per bagian dengan kemampuan Rasch game per dimensi.
   - Uji berpasangan, ukuran efek, dan model campuran tetap dijalankan dan dilaporkan lengkap, sebagai "perubahan" yang **eksploratif**, bukan pengaruh game dan bukan jawaban pertanyaan penelitian utama.
   - Alasannya: desain satu kelompok dengan tes awal dan akhir adalah desain pra-eksperimen (Campbell & Stanley, 1963).
   - Tidak dipilih: uji beda sebagai temuan utama dengan paragraf keterbatasan, dan menghapus uji beda.
2. **Kepraktisan: log, L1, L3, L4, dan bintang 1–5 di kritik dan saran sudah cukup untuk sekarang.**
   - Tidak ada butir baru untuk siswa sekarang, jadi 17 dan 11 tidak berubah.
   - Perlu tidaknya pertanyaan kepraktisan tambahan diputuskan setelah uji coba tahap 4. Bahan kualitatifnya dari berpikir lantang (L5).
   - Tidak dipilih: 2–3 butir kepraktisan sekarang, karena perlu validasi ahli di tahap 2 dan menambah beban P5.
3. **Penelitian pendahuluan ditulis sebagai fase tersendiri, ditambah lembar konteks sekolah.**
   - Bahannya dari 01, 05, 10, dan game kelas 5 yang ada sekarang (daftar kerja no. 22 baru di 01).
   - Data konteks sekolah sasaran dari lembar baru **L8 Profil sekolah** di [02](02_LEMBAR_LAPANGAN.md#l8-profil-sekolah), satu per sekolah, diisi P1 saat sekolah setuju ikut. Isinya fakta sekolah (letak, rombel kelas 5, perangkat, internet, listrik, pengalaman ANBK, pelajaran digital), tanpa nama guru atau siswa.
   - L0 tetap daftar periksa persiapan, dan angket akses digital (16) tetap sumber data akses tiap siswa. Keduanya tidak diubah.
   - Pengembang memilih pilihan ini, bukan usulan awal (fase tersendiri tanpa lembar baru, konteks dari data sekolah terbuka dan ringkasan angket). Melebur ke tahap 1 tidak dipilih.
4. **Prototipe diberi nomor I–III, mengikuti tabel bagian 7 dokumen pendukung.**
   - Prototipe I: tahap 1–2, berbasis kertas dan mock-up.
   - Prototipe II: tahap 3, instalasi uji.
   - Prototipe III: tahap 4, uji coba.
   - Produk final: tahap 5.
   - Mulai sekarang, setiap entri di catatan ini yang merevisi produk menyebut prototipe yang direvisinya.
   - Tidak dipilih: hanya nama tahap.

Akibatnya:
- **13** (*Apa yang diukur*, *Analisis*, keputusan pengembang, rujukan Campbell & Stanley) diperbarui.
- **01** diperbarui:
  - tabel fase dan prototipe di *Peta jalan*;
  - daftar kerja no. 22;
  - empat pertanyaan terbuka yang dicentang;
  - baris uji beda di *Rencana analisis*;
  - "L0–L8" di pembuka.
- **02** mendapat L8 (draf).
- **Dokumen pendukung peneliti** diperbarui: HTML, PDF, dan README.

Yang menyusul, karena berkasnya sedang dipakai sesi lain atau belum waktunya:
- "L0–L7" di 00, `gambar/peta-jalan.svg`, dan `gambar/lapis-data.svg` diganti "L0–L8". Baris 02 di 00 ditambah "L8 draf".
- Paket validasi di 07 menyertakan L8 untuk dibaca Ahli Media Pembelajaran (bagian perangkat dan internet).
- Variabel L8 masuk `kamus-1.1`.

**Keputusan pengembang: baris penutup sebelum skala sikap** (sesi `r10-skala-sikap`, pemeriksaan cerita penutup di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#penutup-ending) terhadap [17](17_SKALA_SIKAP.md))

1. **`penutup-04` diubah.** "…saat membaca, saat memakai layar, dan saat bercerita tentang budaya Kedu" menjadi "…saat membaca, saat memakai layar, dan saat budaya Kedu tampil di layar" (EN: "…and when Kedu's culture appears on a screen"). Kalimat lama memperagakan butir `skp-07` ("Aku senang menceritakan budaya Kedu kepada teman") beberapa menit sebelum siswa menjawabnya; kalimat baru tetap mencakup tiga dimensi, dengan bagian ketiga merujuk D3. Tidak ada baris penutup lain yang memuji siswa; pujian Mbah Kedu di Balai Refleksi sudah dipindah ke sesudah skala.

Akibatnya: 12 diperbarui (baris dan keputusan no. 6). Pada hari yang sama, akibat skala sikap ke dokumen lain diselesaikan: 02 (susulan skala di L1 dan tabel pertemuan, aturan bantuan kata di L3, kalimat skala di surat izin L7), 18 (`mk-ss-05`, Balai Refleksi dengan kritik dan saran), dan 17 (teks siap tempel untuk permohonan izin etik).

**Keputusan pengembang: mock-up layar baru untuk ahli media langkah 1** (cabang `r10-mockup-media`, daftar kerja no. 20, [18](18_MOCKUP_LAYAR_BARU.md))

Putaran 1, sebelum menggambar:

1. **Mock-up memakai tampilan game sungguhan**: halaman HTML statis dengan CSS, huruf, dan gambar UI game, digambar ke WebP 1280 × 720. Sumbernya di `gambar/mockup/src/` supaya bisa dipakai lagi di tahap 3.
2. **Skenario Digital digambar dalam lima keadaan**: situasi dengan ▶, tindakan dipilih, langkah alasan, umpan balik "kurang tepat" dengan kesempatan tersisa, dan kesempatan habis.
3. **Layar token digambar semua**: masukkan token, token keliru, di luar sesi, layar kecil ditolak, dan panel peneliti membuat sesi.
4. **Cerita penghubung dan kartu misi tantangan kelima ikut digambar**, satu gambar masing-masing.

Putaran 2, setelah melihat 23 gambar:

1. **Gambar disetujui apa adanya** untuk paket ahli media langkah 1. Gambar skala sikap (`mk-ss-01`) masih placeholder dan diperbarui setelah `17_SKALA_SIKAP.md` final.
2. **Penjelasan setelah kesempatan Skenario Digital habis ditulis sebagai satu kalimat tersendiri per skenario di 08** (24 kalimat), tidak memakai umpan balik pilihan tepat yang dipotong. Dikerjakan di sisi bank sebelum validasi ahli.
3. **Lapisan pemeriksaan area ketuk (`mk-pu-02`) ikut dikirim ke ahli media**, bertanda tidak tampil ke siswa.
4. **Draf salinan di 18** (token keliru, layar kecil, ketukan keliru di *Periksa Unggahan*, pengingat lima menit, kalimat kesempatan habis) **diterima sebagai teks awal**; kalimat akhirnya ditetapkan di dokumen implementasi setelah telaah ahli dan uji keterbacaan.

Akibatnya: 18 ditulis; 00, 01 (daftar kerja no. 20), dan 03 (kode `mk-*`) diperbarui. Atas permintaan pengembang di sesi yang sama, 24 kalimat *Penjelasan setelah kesempatan habis* ditulis di 08 (satu per skenario, dengan aturannya di *Cara membaca*: menyebut risikonya lalu mengapa tindakan tepat aman, tanpa kata "tepat", "salah", atau "gagal", tidak dibacakan, tidak diskor), dan `mk-sd-05` digambar ulang. Kalimatnya ikut validasi ahli bersama bank. Setelah 17 masuk `main` (PR #253), placeholder `mk-ss-01` diganti empat gambar `mk-ss-01`–`04` (petunjuk, contoh soal, `skp-03`, layar kirim) dengan format jawaban dari 17, jadi 18 kini memuat 26 gambar. Celah lain yang ditemukan dicatat di 18 → *Celah yang ditemukan di dokumen lain*.

**Keputusan pengembang: pertanyaan terbuka skala sikap** (sesi `r10-skala-sikap`, menjawab pertanyaan terbuka di [17](17_SKALA_SIKAP.md#pertanyaan-yang-masih-terbuka))

1. **Butir bahasa daerah (skp-10) adil** untuk siswa yang bahasa ibunya bukan bahasa Jawa, karena menyebut "bahasa daerah" umum. Butir tetap dipakai.
2. **skp-12 menyebut contoh acara setempat:** "Aku mau ikut acara budaya seperti merti desa dan kirab." Ahli Budaya Kedu memastikan keduanya dikenal di tiga wilayah.
3. **Bintang 1–5 di kritik dan saran menjadi data kepraktisan** (`feedback_rating`, masuk `kamus-1.1`).
4. **Siswa yang belum tuntas di akhir P5: data apa adanya.** Tidak ada jalur khusus ke Balai Refleksi; skalanya kosong dengan alasan `belum_tuntas`, dan data tantangannya dipakai seperti adanya. Susulan 7 hari hanya untuk siswa yang tidak hadir di P5.
5. **Korelasi sikap dengan PBK dan D3 diharapkan sedang: cukup kuat, tidak berlebihan** (usulan r ≈ 0,30–0,50; r ≥ 0,70 atau r < 0,10 dicatat sebagai temuan). Batas pastinya ditulis di pra-registrasi OSF di G4.

Akibatnya: [17](17_SKALA_SIKAP.md) diperbarui (butir, pelaksanaan, variabel `sikap_missing_reason` dan `feedback_rating`, akibat ke 01 dan dokumen pendukung peneliti).

**Keputusan pengembang: skala sikap menjaga warisan** (sesi `r10-skala-sikap`, dua putaran wawancara; semua usulan disetujui)

1. **Format jawaban: empat pilihan tanpa titik tengah** (*Tidak setuju · Kurang setuju · Setuju · Sangat setuju*), kata dengan titik emas yang makin besar, tanpa wajah.
2. **12 butir, empat indikator × tiga:** menghargai (SK1), ingin tahu (SK2), mau ikut merawat (SK3), bangga (SK4). Satu butir per indikator berarah negatif dan dibalik skornya, ditulis tanpa "tidak" atau "bukan". Semua butir berlatar dunia nyata, tanpa ruang digital, supaya terpisah dari D3 dan PBK.
3. **Tidak dibacakan:** hanya di layar, tanpa rekaman. P3 boleh menjelaskan satu kata, dicatat di L3.
4. **Kritik dan saran yang ada sekarang tetap**, sesudah skala; selama penelitian semua isiannya boleh kosong.
5. **Penilai ahli:** Ahli Budaya Kedu, Ahli Pengukuran, Ahli Bahasa Anak 1 dan 2 menilai relevansi (I-CVI, aturan Lynn: keempatnya harus memberi 3 atau 4); Ahli Bahasa Anak juga menelaah bahasa; Ahli Literasi Digital 1 dan 2 hanya memberi catatan tumpang tindih D3.
6. **Tidak hadir di P5:** susulan paling lama 7 hari, ditandai; sesudahnya kosong, tanpa perkiraan.
7. **Tampilan:** satu pernyataan per layar, boleh kembali sebelum kirim, urutan tetap, bilah kemajuan.

Akibatnya: [17](17_SKALA_SIKAP.md) ditulis; kode skala masuk 03. Penyesuaian 01 (perubahan sistem no. 21), 02 (L1, L3, L7), 06 (baris P5), 07 (lembar penilaian v2.1), 11 (`kamus-1.1`), 12, 13 (waktu P5), dan mock-up 18 tercatat di [17](17_SKALA_SIKAP.md#akibat-ke-dokumen-lain).

**Keputusan pengembang: lembar lapangan final, putaran 1** (sesi `r10-lembar-lapangan`, daftar kerja no. 11)

1. **Siswa tanpa izin orang tua tidak punya akun GELITA.** Mereka ikut kegiatan pengganti yang setara di ruang yang sama, disiapkan guru (misalnya membaca buku), jadi tidak ada data tentang mereka. Pilihan bermain lalu datanya dihapus, atau mode tamu tanpa penyimpanan, tidak dipakai.
2. **Di P1, baris dan kursi dimasukkan langsung setelah pendaftaran.** Siswa mendaftar lalu menunggu di layar pembuka modul tes. P1 mengetik kode peserta → baris dan kursi untuk seluruh kelas di panel peneliti (± 5 menit), bersama buklet. P3 mencocokkan kode dengan L2. Setelah itu tes awal dimulai bersama. Buklet tidak lagi dimasukkan "sesudah pertemuan P1".
3. **Wawancara retrospektif L5 di P2–P4, tiga siswa per pertemuan**, satu dari setiap kelompok nilai tes awal (rendah, sedang, tinggi). Setiap siswa diwawancarai paling banyak sekali, jadi ± 9 siswa per kelas. Wawancara di P5 tidak dijadwalkan.
4. **Siswa yang absen di pertemuan wilayah ikut sesi susulan dalam tiga hari**, seperti susulan tes. P1 membuka sesi lapangan susulan untuk wilayah itu dengan token sendiri (kode sesi berakhiran `-S`, misalnya `S03-A-P3-S`), diamati sedikitnya satu peneliti yang mengisi L1–L3. Sisa tantangan tetap dilanjutkan di susulan P5.

**Persetujuan:** pengembang menyetujui lembar L0–L7 final di [02](02_LEMBAR_LAPANGAN.md) apa adanya, termasuk pilihan yang menyertainya:
- L3 juga dipakai saat tes dan angket, menggantikan kolom catatan L6 di 13.
- L4 hanya selama waktu bermain.
- Kode penyimpangan baru DEV-TIM (peneliti kurang dari tiga).
- L2 satu baris per siswa, dengan kolom baris dan kursi di baris.
- Kelompok nilai tes awal untuk L5 adalah sepertiga terbawah, tengah, dan teratas di rombel.
- Contoh tanggal fiktif, sesudah perkiraan persetujuan etik.

Akibatnya: 02 final; 03 mendapat kode sesi susulan, DEV-TIM, dan arti L5 yang baru. Sesudah persetujuan, 01 (*Kerja lapangan*, perubahan sistem no. 11 dan no. 22), 06 (pembagian pertemuan, buklet), dan 13 (waktu P1 menjadi ± 124 menit, urutan P5, bantuan saat tes di L3) disesuaikan. Urutan P5 di catatan keputusan 12 dibiarkan sebagai riwayat. Yang tersisa: 11 di `kamus-1.1`. Yang perlu disesuaikan di dokumen lain dicatat di [02](02_LEMBAR_LAPANGAN.md#perubahan-dari-draf).

**Keputusan pengembang: label Bloom pengembang** (sesi `r10-label-bloom`, daftar kerja no. 4, telaah di [19](19_TELAAH_LABEL_BLOOM.md))

1. **Petunjuk Cari Objek diberi label menurut proses yang dilakukan siswa.** PBK: C1 kalau petunjuk menyebut nama bendanya (Temanggung), C2 kalau hanya mendeskripsikannya (Magelang, Wonosobo). Periksa Unggahan per jenis petunjuk: atribusi C2, izin C3, representasi C2 di Temanggung dan C4 di Magelang dan Wonosobo, menghargai tradisi C2 di Temanggung dan Magelang dan C4 di Wonosobo. Tidak ada C5 di Cari Objek.
2. **Benar atau Salah paling tinggi C4, kecuali dua butir C5.** Membandingkan isi dua teks dan menggolongkan fakta atau pendapat menjadi C2 (mengikuti Anderson & Krathwohl, bukan daftar kata kerja operasional yang menaruh "membedakan" di C4). Menilai kepercayaan sumber dari penanda yang diberikan, atau menolak alasan popularitas dan posisi, menjadi C4. C5 hanya untuk `wnb-3-a06` dan `wnb-3-b06`, yang memeriksa apakah simpulan didukung bukti.
3. **Skenario Digital diberi label per butir di 08.** Temanggung dan Magelang C3, Wonosobo C4, kecuali `wnb-5-a03` dan `wnb-5-b03` C5 (menimbang privasi teman melawan tekanan grup).
4. **Rentang Bloom wilayah direvisi, butir tidak ditulis ulang:** Temanggung C1–C3, Magelang C2–C4, Wonosobo C2–C5. Aturan no. 14 di 06 kini menyebut bahwa Bloom menggambarkan proses berpikir, bukan tingkat kesulitan; kesulitan ditunjukkan level AKM dan data Rasch.
5. **Lima perubahan label Isi Rumpang dipertahankan** (ditanyakan sesudah wawancara): `mgl-2-a04`, `mgl-2-b05`, dan `wnb-2-b05` menjadi C2 (perbandingan angka atau sebab–akibat langsung, sama dengan butir serupa di wilayah lain); `wnb-2-a04` dan `wnb-2-a05` menjadi C3 (menerapkan keterangan rentang ketinggian).
6. **`wnb-3-b02` ditulis ulang, level AKM tetap L2** (ditanyakan setelah PR #247 digabung). Jawabannya tersurat, jadi lebih dekat ke L1; daripada memasang L1 di Wonosobo, pernyataannya ditulis ulang di sesi bank berikutnya supaya perlu inferensi. Catatannya ada di 08 (*Yang harus dicek sebelum validasi ahli*). Atas permintaan pengembang, butir itu langsung ditulis ulang di hari yang sama: "Menurut bacaan, petugas memasang pagar pembatas karena kawah di Dieng terlalu panas untuk didekati." Kunci tetap *salah*, kode salah menjadi INF-HUB (sebab yang keliru), Bloom tetap C2.

Akibatnya: 39 dari 92 label lama di 08 berubah, dan 36 label baru ditulis (12 PBK, 24 skenario). Rentang per tantangan dan per wilayah di 06 serta tabel pelevelan wilayah di 01 disesuaikan. Ini label pengembang; label ahli dari pelabelan buta B.5a di tahap 2 menggantikannya.

**Keputusan pengembang: penyelarasan dokumen bersama setelah keputusan tes** (sesi `pbb/magical-hamilton-4f6l98`, wawancara lanjutan)

1. **Variabel tes dan angket masuk kamus data di `kamus-1.1`, setelah uji coba.** `kamus-1.0` di [11](11_KAMUS_DATA.md) tidak diubah sekarang. Sampai saat itu, daftar variabelnya ada di [13](13_TES_AWAL_AKHIR.md#penskoran-dan-pemasukan-data) dan [16](16_ANGKET_AKSES_DIGITAL.md#variabel-untuk-kamus-data).
2. **Pertemuan wilayah (P2–P4) juga tidak terikat 2 JP: waktu bermain ± 90 menit.** Tantangan yang belum selesai tetap dilanjutkan di susulan P5 (paling lama 20 menit). Dengan perkiraan 29–52 menit per wilayah di [06](06_KISI_KISI_BANK_SOAL.md#waktu-bermain), hampir semua siswa selesai di pertemuannya.
3. **Butir risiko daring di P5 memakai izin orang tua terpisah.** Surat izin orang tua dan L7 punya centang sendiri untuk butir ini. Tanpa centang itu, siswa tidak melihat butir tersebut, tetapi tetap ikut kegiatan lain.

Akibatnya: 02 (L6 dan L7), 06 (waktu bermain dan pembagian pertemuan), 03 (kode tes dan angket), 07 (paket validasi), 13, dan 16 disesuaikan.

**Keputusan pengembang: tes awal–akhir dan angket akses digital** (sesi `pbb/magical-hamilton-4f6l98`, menjawab empat pertanyaan di [13](13_TES_AWAL_AKHIR.md#keputusan-pengembang) dan satu putaran wawancara lanjutan)

1. **Lama tes mengikuti kebutuhan, tidak terikat jam pelajaran sekolah.** Penelitian sudah mendapat izin memakai seluruh JP, bahkan sampai setelah jam sekolah. Jadi lama P1 dan P5 mengikuti kebutuhan tes dan kegiatan lain; pilihan 3 JP dan 4 JP di 13 tidak dipakai lagi.
   - Setiap bagian tes diberi batas waktu longgar di atas perkiraan: Bagian 1 ± 40 menit, Bagian 2 dan 3 ± 35 menit. Jam tidak tampil di layar, sama seperti di game.
   - Waktu selesai setiap siswa dicatat, dan batas akhirnya ditetapkan dari uji coba.
2. **Media tes: modul tes di GELITA**, dengan tampilan Bootstrap yang sederhana dan tidak dibuat seperti game. Tes tidak memakai kertas, dan jawaban tidak diketik ulang. Modul ini menjadi perubahan sistem no. 22 di [01](01_REVISI_10_ANALITIK.md#catatan-perubahan-sistem-untuk-tahap-nanti) dan dibangun di tahap 3, setelah G2.
3. **Topik teks dan adegan tes: budaya Nusantara di luar Kedu**, seperti draf [14](14_TES_BAGIAN_MEMBACA.md) dan [15](15_TES_BAGIAN_DIGITAL.md).
4. **Angket akses digital masuk modul yang sama**, langsung setelah tes awal di P1. Pertanyaannya hanya tampil di layar dan tidak dibacakan koordinator sesi.
5. **Pengalaman risiko daring: 2–3 butir di angket, diisi di P5 setelah tes akhir**, sendiri di layar (ang-21 sampai ang-23 di [16](16_ANGKET_AKSES_DIGITAL.md)). Syaratnya: butir ini disebut di permohonan izin etik dan surat izin orang tua, dan ada prosedur rujukan tertulis untuk anak yang menjawab pernah mengalami hal yang mengganggu.

Akibatnya: 13, 14, 15, dan 16 disesuaikan dengan modul layar (petunjuk menjawab, waktu, pemasukan data otomatis, kode butir tetap). 02 (L6), 06 (isi P1 dan P5), 11 (variabel tes dan angket), 03 (pola kode), dan 07 (paket validasi) diselaraskan sesudahnya.

**Keputusan pengembang: model penelitian dan pengembangan, dan aturan I-CVI** (sesi `sky/peaceful-curie-68ufr5`)

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

Usulan dokumen itu yang belum diputuskan (semuanya **diputuskan 26 Oktober 2026**, lihat entri *empat usulan dokumen model penelitian* di atas):
- uji beda tes awal–akhir dilaporkan sebagai temuan eksploratif, karena desain satu kelompok tanpa kontrol tidak bisa menunjukkan sebab-akibat; **diputuskan: ya, dan tes terutama menjadi bukti validitas kriteria**;
- kepraktisan diukur dari log dan lembar L1, L3, dan L4, karena guru tidak ikut (ditambah bintang 1–5 di kritik dan saran sebagai pendapat siswa, sesuai keputusan skala sikap 26 Oktober 2026); **diputuskan: cukup untuk sekarang, ditinjau lagi setelah uji coba**;
- penelitian pendahuluan ditulis sebagai fase tersendiri; **diputuskan: ya, ditambah lembar L8 Profil sekolah**;
- prototipe diberi nomor I–III; **diputuskan: ya**.

**Konfirmasi pengembang: fakta dan gambar Susun Gambar** (sesi `sky/festive-hopper-cl6p65`)

- **Wayang Kedu Temanggungan** adalah varian tradisi wayang kulit asli dari wilayah Kedu, Jawa Tengah, yang berkembang khusus di Kabupaten Temanggung. Keterangan gambar baru Temanggung ditulis dari fakta ini. Kalimat tentang Warisan Budaya Takbenda dihapus dari draf, karena surat penetapannya belum ditemukan.
- **Topeng Lengger berasal dari Wonosobo**, jadi syarat Lengger sebagai variasi `b` Wonosobo terpenuhi. Bukit Sikunir tidak dipakai di pengerjaan pertama. Tahun penetapan 2020 di keterangannya tetap dicek peneliti.
- **Gambar baru Wayang Kedu dibuat tim katalog dan dicek ulang nanti.** Yang diperlukan sekarang hanya konsepnya, dan arahannya sudah ada di 08.
- **Gambar Candi Pawon diganti**, karena gambar sekarang menonjolkan stupa kecil di atap yang mirip sasaran PBK `mgl-4-x01`. Arahan gambar baru ada di 08, dan gambarnya dicek ulang setelah dibuat.

**Keputusan pengembang: variasi Susun Gambar selama studi** (sesi `sky/festive-hopper-cl6p65`, dua pertanyaan di [08](08_DRAF_BANK_SOAL.md#keputusan-pengembang-26-oktober-2026) dan empat pertanyaan lanjutan)

1. **Cara memperbaiki: dua variasi bersih diacak**, bukan satu variasi tetap per wilayah (usulan awal). Kedua variasi menjadi `a` dan `b`, sesuai `FIRST_ATTEMPT_GROUPS` di 06, dan Susun Gambar tidak mengikuti buklet. Variasi lain hanya untuk pengerjaan ulang.
2. **Pasangan per wilayah:**
   - Temanggung: Pasar Papringan, ditambah gambar dan keterangan baru tentang **Wayang Kedu** (topik yang tidak dipakai butir mana pun). Menulis ulang `tmg-1-a` atau `tmg-1-e` tidak dipilih, dan Posong ditolak karena topik gunungnya sama dengan `tmg-2-a`.
   - Magelang: Candi Mendut dan Candi Pawon.
   - Wonosobo: Kompleks Candi Arjuna dan Tari Topeng Lengger. Lengger dipakai hanya kalau ahli budaya membenarkan asalnya dari Wonosobo; kalau tidak, pengembang memilih ulang, dan Bukit Sikunir yang dipangkas menjadi satu-satunya sisa.
3. **Yang menyusul:** draf keterangan Wayang Kedu dan arahan gambar (usulan prompt TMG-P11) ada di 08, menunggu cek fakta dan gambar dari tim katalog. Fakta Papringan, Pawon, Arjuna, dan Lengger dicek peneliti sebelum validasi ahli.

Disesuaikan di dokumen lain: 06 (perubahan sistem no. 4, tabel kode, tabel "Yang harus ditulis") dan 01 (perubahan sistem no. 20). Rujukan "perubahan sistem no. 4 di 01" di entri sebelumnya keliru dan sudah dibetulkan: pemilihan variasi ada di no. 20 di 01, yang merujuk ke no. 4 di 06.
**Keputusan pengembang: cerita penghubung dan narasi tantangan kelima** (sesi `sky/optimistic-bardeen-4zx8ob`)

Pengembang menyetujui seluruh draf [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md) beserta semua usulannya:

1. **Cerita penghubung diputar sekali**, saat tantangan kelima pertama kali dibuka, sebelum kartu misinya.
2. **Selama studi, Kenali wilayah baru terbuka setelah wilayahnya tuntas**, seperti Pustaka. Di luar studi tetap seperti sekarang. Masuk perubahan sistem no. 16 di 01.
3. **Kabar berantai Wonosobo menjadi ramalan gempa**, bukan kawah akan meletus.
4. **Di Skenario Digital hanya situasi yang dibacakan**, lewat tombol ▶.
5. **Urutan P5:** tantangan susulan, tes akhir, cerita penutup, skala sikap di Balai Refleksi, wawancara L5. Tes akhir kini sebelum cerita penutup.

Penyesuaian di dokumen lain: 06 (urutan P5; baris waktu "Cerita wilayah: pembuka, penghubung, tuntas" 4–5 menit, sehingga waktu bermain menjadi ± 29–36, 36–44, dan 43–52 menit), 02 (aturan tes akhir), 01 (daftar kerja no. 7 selesai; perubahan sistem no. 16), dan 00. Pemetaan narasi `petunjuk-*` ke butir `x01`–`x04` dibetulkan di tahap 3, karena pemetaan sekarang masih benar untuk bank yang terpasang.

Keterangan Susun Gambar diperiksa dan diputuskan di sesi lain (dua entri di atas). Karena asal Topeng Lengger dari Wonosobo sudah dikonfirmasi pengembang, `kenal-wonosobo-04` di 12 kembali ke teks lama dan tetap menyebut Lengger; yang dibetulkan di Kenali tinggal jumlah relief yang bercerita (`kenal-magelang-02`). Naskah menjadi 21 baris baru dan 31 baris diubah. Pemetaan narasi `petunjuk-*` ke butir `x01`–`x04` dikerjakan di tahap 3 (12, daftar tahap 3 butir 3).

**Pemeriksaan keterangan Susun Gambar terhadap draf bank soal** (sesi `sky/festive-hopper-cl6p65`, menindaklanjuti *Di luar dokumen ini* di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md#di-luar-dokumen-ini))

- Ketiga puluh keterangan Susun Gambar (`*-1`, variasi a–j) dicocokkan dengan semua butir D1, kunci D3, dan petunjuk PBK di [08](08_DRAF_BANK_SOAL.md#susun-gambar-selama-studi). Hasilnya: 22 keterangan bocor atau memberi isyarat. Kedua belas petunjuk PBK dan keenam butir representasi D3 masing-masing punya keterangan yang sepadan. Skenario D2 tidak terkena.
- **Koreksi atas 12:** variasi d–j hanya muncul di pengerjaan ulang, yang selama studi baru terbuka setelah wilayahnya tuntas. Jadi Situs Liyangan, Prasasti Gondosuli, dan Umbul Jumprit tidak bocor selama studi, dan tidak ada keterangan yang bocor ke wilayah sesudahnya. Yang berlaku adalah variasi a–c di pengerjaan pertama: 7 dari 9 bocor (`tmg-1-a`, `b`, `c`; `mgl-1-a`, `b`; `wnb-1-a`, `c`). Tiga gambarnya juga foto unggahan *Periksa Unggahan* di wilayah yang sama.
- **Usulan** (menunggu dua jawaban pengembang di 08):
  - Selama studi, pengerjaan pertama Susun Gambar memakai satu variasi tetap per wilayah dari keterangan yang bersih: Pasar Papringan, Candi Mendut, dan Kompleks Candi Arjuna.
  - Ke-27 variasi lain tetap untuk pengerjaan ulang. Tidak ada keterangan atau gambar yang diubah.
  - Setelah diputuskan, pemilihan variasi pengerjaan pertama (perubahan sistem no. 20 di 01, no. 4 di daftar 06) dan kode Susun Gambar di 06 disesuaikan.
- Petunjuk PBK dihitung sebagai kebocoran, sejalan dengan usulan Kenali di 12 (pertanyaan 2).
- Dua fakta keterangan dicatat untuk peneliti: asal Topeng Lengger di `wnb-1-i` (sengaja tidak dipakai di 08), dan tahun Tambi di `wnb-1-h`.
- Pemetaan narasi `petunjuk-*` yang usang (temuan kedua di 12) belum dikerjakan di sesi ini.

**Draf cerita penghubung dan narasi tantangan kelima** (sesi `sky/optimistic-bardeen-4zx8ob`, daftar kerja no. 7)

- Cerita penghubung, kartu misi, baris lama yang berubah, dan daftar rekaman disusun di [12](12_CERITA_PENGHUBUNG_DAN_NARASI.md). Statusnya draf sampai pengembang menjawab empat pertanyaan di bagian akhir dokumen itu:
  1. kapan cerita penghubung diputar
  2. Kenali wilayah selama studi (usulan: baru terbuka setelah wilayahnya tuntas, karena fakta budayanya adalah kunci butir *Periksa Unggahan* dan Temukan Benda)
  3. kabar berantai Wonosobo: tetap kawah atau ganti ramalan gempa
  4. yang dibacakan di Skenario Digital
- **Usulan yang ikut draf** (bisa dikoreksi pengembang):
  - Serpihan kelima setiap wilayah disembunyikan Kabut Lupa di dalam layar (*Kabut di Layar*). Konteks dialog baru `scenario_open`, berkas `layar-{wilayah}-NN`, 6 baris per wilayah.
  - Sebelas aturan menulis cerita untuk penelitian. Intinya: cerita sebelum tantangan tidak menyebut jawaban, tindakan kunci, atau ciri jebakan, dan tidak mendorong ke arah sebaliknya; pelajaran datang di cerita tuntas dan hanya mengulang umpan balik; cerita tuntas tidak menyebut jumlah serpihan seluruhnya, karena ada siswa susulan.
  - Kode berkas baru `skenario-{kode butir}` (Narator) dan `unggahan-{kode butir}` (Mbah Kedu), dengan arahan suara datar supaya nada bicara tidak membocorkan jawaban.
  - Kartu misi tantangan kelima, dan tujuh kartu lama disesuaikan dengan bentuk teks bank baru, sama untuk variasi a dan b.
  - Naskah menjadi 112 baris per bahasa (21 baru, 32 diubah); dengan situasi, petunjuk *Periksa Unggahan*, dan instruksi, rekaman menjadi ± 187 per bahasa. Waktu cerita per pertemuan wilayah naik sekitar satu menit.
- **Temuan pemeriksaan naskah lama terhadap 08** (subagen, dicek ulang): 9 baris menyebut jawaban butir D1 atau tindakan kunci D2, terutama di Kenali dan dialog masuk Wonosobo; dua fakta Kenali bertentangan dengan 08; 12 baris masih menyebut 12 serpihan atau 4 tantangan. Draf 12 lalu ditelaah ulang secara terpisah; temuannya (antara lain kata "katanya" sebagai penanda jebakan, desakan waktu, dan "putuskan sendiri" sebelum skenario yang kuncinya melibatkan orang dewasa) sudah dimasukkan. Naskah aktif (`docs/naskah-cerita.md`) belum diubah; isinya dipasang di tahap 3.
- **Di luar dokumen itu:** keterangan Susun Gambar (tantangan 1) memuat jawaban butir D1 Temanggung, dan pemetaan narasi `petunjuk-*` ke butir `a01`–`a04` sudah usang; keduanya perlu dibereskan di bank sebelum validasi ahli. Diusulkan juga tes akhir dikerjakan sebelum cerita penutup di P5, yang mengubah urutan di 06 dan menunggu persetujuan pengembang.

**Keputusan pengembang: kamus data** (sesi `sky/optimistic-carson-kkpbcv`)

Empat pertanyaan di draf [11](11_KAMUS_DATA.md#keputusan-pengembang) diputuskan, dan kamus menjadi `kamus-1.0`:

1. **Kode bagian lain di *Periksa Unggahan*: sesuai usulan.** Bagian ketuk yang bukan kunci dan bukan pengecoh utama diberi kode jenis petunjuknya (BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK), bukan INF-REL. Ketukan di tempat kosong dekat kunci = NON-SEN dan tidak memakai kesempatan. Aturan di 08 disesuaikan.
2. **Ambang jeda tanpa masukan: 3 menit, sementara.** Hanya bagian jeda yang melebihi ambang yang dipotong dari waktu aktif. Pengembang meminta durasinya mudah diubah, jadi ambang disimpan sebagai pengaturan di konfigurasi studi, dan lapis turunan dihitung ulang dari data mentah tanpa mengubah sistem.
3. **Data siswa yang bisa dianalisis: sesuai usulan.** Izin lengkap, bukan sekolah uji coba, dan percobaan pertama yang bukan dari perangkat bersama pada sedikitnya 80% butir literasi digital (49 dari 61).
4. **Tebakan cepat dipakai apa adanya di analisis utama**; analisis kedua memperlakukannya sebagai data hilang. Ambang sementaranya di bawah 3 detik (usulan awal 1,5 detik), juga berupa pengaturan.

Ambang 3 menit dan 3 detik menggantikan angka usulan lama (60 detik dan 1,5 detik) di 01 dan 03, dan tetap ditetapkan ulang setelah uji coba (`kamus-1.1`). Penegasan yang menyertai draf tidak dikoreksi, jadi ikut berlaku.

## 25 Oktober 2026

**Draf kamus data** (sesi `sky/optimistic-carson-kkpbcv`, daftar kerja no. 2)

- Kamus data disusun di [11_KAMUS_DATA.md](11_KAMUS_DATA.md): peta bukti, butir yang dinilai, pengerjaan pertama, data hilang, penanda analisis, definisi beku 10 analitik, variabel butir dan peserta, aturan gabung lembar, dan syarat data untuk tahap 3. Statusnya draf sampai pengembang memutuskan empat pertanyaan di bagian akhir dokumen itu:
  1. kode bagian ketuk lain di *Periksa Unggahan*
  2. cara memotong jeda di waktu aktif
  3. definisi data siswa yang bisa dianalisis
  4. perlakuan tebakan cepat di analisis utama
- **Penegasan yang diusulkan bersama draf** (tafsiran dokumen sebelumnya, bisa dikoreksi pengembang):
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

- **Keputusan pengembang: pelabelan buta hanya sekali, oleh dua ahli di tahap 2** (B.5a di 07). Kesepakatan κ ≥ 0,70 pindah dari gerbang G1 ke G2. G1 kini berarti konstruk disetujui dan semua dokumen tahap 1 selesai dan disetujui pengembang. Daftar kerja no. 3 pindah ke tahap 2. Alasannya: pelabelan ganda memakan waktu, dan anggota tim yang sudah melihat label pengembang di 08 tidak bisa melabel secara buta.
- **Perbaikan 01 tanpa keputusan baru**, supaya sejalan dengan kisi-kisi 06 yang sudah disetujui sebelum kamus data disusun:
  - Tabel topik Skenario Digital: izin atas karya budaya pindah ke D3, topik izin D2 cukup foto dan obrolan diri sendiri atau teman.
  - Skema kesalahan mendapat bagian kode BUD (bagian C; NON jadi D, alasan R jadi E). Kalimat "INF-BUD juga D3" dan "DIG-IZN dan DIG-ETK juga D3" diganti pembagian yang disetujui.
  - Cara kode menempel kini memisahkan Cari Objek babak 1 (PBK) dan babak 2 (*Periksa Unggahan*, D3).
  - Contoh data mentah memakai kode `x` untuk butir Temukan Benda.
  - Dua kalimat "kalau token sesi dipakai" dihapus.
- Gambar peta jalan dan 00 disesuaikan.

**Keputusan pengembang: wawancara pertanyaan terbuka** (sesi `pbb/eloquent-sagan-j40sg8`, bertahap)

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

- **Cerita penghubung dan narasi tantangan kelima didraf AI** dari skenario di 08, disetujui pengembang, dan direkam pengisi suara yang sama dengan narasi lain di sesi rekaman yang sama.
- **Skala sikap diisi sekali, di P5**, di Balai Refleksi setelah cerita penutup.
- **Dokumen berikutnya: kamus data** (`11_KAMUS_DATA.md`, daftar kerja no. 2), yang membekukan definisi 10 analitik, "dilewati", waktu aktif, tersensor, dan kode. Dokumen tahap 1 lain merujuk ke sana.
- **Aturan menjaga konteks** dicatat di `.claude/rules/revisi-10.md` (dimuat saat berkas di folder ini disentuh), dan [00_INDEKS.md](00_INDEKS.md) memuat status setiap dokumen, urutan baca, dan tahap dokumen yang tersisa.
- **Status lama dibetulkan:** daftar kerja no. 6 (Skenario Digital) sudah selesai di draf 08.

Validator dan komisi etik belum bisa ditetapkan pengembang. Sementara:

- **Tujuh validator dengan nama samaran**, sesuai komposisi panel di 07 yang sudah diterima: Ahli Bahasa Anak 1 dan 2, Ahli Literasi Digital 1 dan 2, Ahli Pengukuran (panel ahli materi lima orang), Ahli Budaya Kedu (pengamat budaya), dan Ahli Media Pembelajaran. Nama samaran dicatat di 03 dan dipakai di instrumen dan paket validasi sampai orangnya ditetapkan.
- **Komite etik memakai nama samaran *Lembaga Komite Etik*.** Izin etik diajukan pada minggu kedua sampai keempat November 2026. Persetujuan diperoleh sebelum kegiatan yang mensyaratkannya dimulai, paling lambat antara akhir September dan minggu pertama November 2027.
- **Gerbang G2 tetap menunggu persetujuan etik.** Dengan jadwal di atas, dokumen implementasi dan perubahan sistem (tahap 3) bisa baru dimulai sekitar akhir 2027. Usulan memulai tahap 3 setelah izin diajukan tidak dipilih; pengembang tetap pada urutan ketat dari tahap 1 wawancara. Uji keterbacaan dengan anak (tahap 2), uji coba, dan pengambilan data juga menunggu persetujuan.

**Keputusan pengembang: kisi-kisi, draf bank soal, dan telaah pendahuluan T6** (sesi `kisi-kisi-bank-soal`)

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
- **Draf bank soal baru** disusun di [08](08_DRAF_BANK_SOAL.md): 116 butir literasi digital dalam variasi a/b ditambah 6 butir jangkar dan 12 butir PBK, semua berstatus `needs_verification`. Terjemahan bahasa Inggris dikerjakan pengembang nanti.
- **Nama fiktif dicek di web.** "Sanggar Turangga Muda" diganti "Sanggar Kuda Pelangi" karena mirip nama kelompok kuda lumping nyata. Semua unggahan tampil di aplikasi media sosial fiktif *Kedugram*, dan komentator ditulis dengan nama depan saja. Pencarian manual nama akun di Instagram dan TikTok masih perlu dilakukan satu orang tim.
- **Telaah pendahuluan T6** dijalankan ([09](09_TELAAH_PENDAHULUAN_BANK_SOAL.md)); 2 temuan sistematis (pilihan tepat dan alasan yang diharapkan selalu paling panjang) dan 13 temuan per butir dibetulkan di 08. Sisa temuan R1–R4 disetujui sesuai usulan: rumpang yang hanya punya satu calon dibiarkan dan dipantau di uji coba (R1); label D1b Temanggung diputuskan ahli (R2); pola kalimat pilihan "kurang tepat" dinilai ahli bahasa anak (R3); kunci Periksa Unggahan Wonosobo dipertahankan dan dipastikan ahli (R4).
- **Cek fakta C.5**: semua fakta bank lama didukung sumber; tahun perkebunan teh Tambi (1865 atau 1885) diserahkan ke ahli budaya.

**Keputusan pengembang: media**

Keempat usulan dari telaah pendahuluan media ([10](10_TELAAH_PENDAHULUAN_MEDIA.md)) disetujui:

- **Tanpa ponsel.** Pengambilan data hanya memakai laptop, Chromebook, atau tablet mendatar berlayar 10 inci atau lebih. Di ponsel, sebagian besar teks tampil di bawah 12 px, sehingga skor membaca ikut bergantung pada perangkat. Aturan ini masuk daftar periksa L0, dan game menolak atau memberi peringatan pada sesi penelitian di layar kecil.
- **Area ketuk Cari Objek tidak boleh bertumpuk.** Adegan Magelang dan Wonosobo ditata ulang supaya area ketuk tidak bersentuhan (jarak minimal setara 24 dp). Aturan bank soal mendapat pemeriksaan otomatis yang sama, berlaku untuk babak 1 dan babak 2 (*Periksa Unggahan*).
- **Jam disembunyikan selama penelitian.** Waktu tetap direkam server. Hitungan benar/salah boleh tampil setelah Cek pertama sebagai umpan balik. Diatur lewat mode penelitian di konfigurasi studi.
- **Aturan narasi.** Bacaan dan pernyataan D1 tidak pernah dibacakan. Situasi Skenario Digital (D2) dan petunjuk *Periksa Unggahan* (D3) boleh dibacakan lewat tombol ▶, tidak diputar otomatis, dan pemutarannya tercatat. Kalimat kisi-kisi "teks di layar game tetap dibacakan narasi seperti sekarang" dibaca dengan aturan ini.

**Telaah pendahuluan media (AI, bukan validasi ahli)**

- Media game ditelaah di instalasi uji lokal: [10_TELAAH_PENDAHULUAN_MEDIA.md](10_TELAAH_PENDAHULUAN_MEDIA.md). Tiga temuan berkode V dan keparahan 3 perlu keputusan pengembang sebelum uji coba:
  - pengambilan data tidak memakai ponsel
  - area ketuk Cari Objek yang bertumpuk ditata ulang dan diperiksa otomatis
  - jam disembunyikan selama penelitian
- Usulan aturan narasi: bacaan dan pernyataan D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk *Periksa Unggahan* boleh dibacakan lewat tombol ▶.

**Keputusan pengembang**

- Media game yang ada sekarang (tampilan, layar, interaksi, audio, gambar) juga terbuka untuk diubah, ditambah, dan dihapus sesuai kebutuhan Revisi 10.
- Telaah instrumen validasi ([07](07_TELAAH_INSTRUMEN_VALIDASI.md)) diterima seluruhnya:
  - B.5 dipecah menjadi B.5a (pelabelan buta, dua ahli) dan B.5b (relevansi dan kejelasan, semua ahli menilai semua butir, minimal tiga penilaian non-TD per butir).
  - Panel ahli materi lima orang, dengan matriks penugasan aspek.
  - κ berbobot untuk level AKM.
  - Perkiraan beban kerja dicantumkan di surat permohonan.
- Ahli media masuk daftar bidang validasi (daftar kerja no. 14).
- AI berperan sebagai penelaah pendahuluan, bukan validator: skornya tidak dihitung dan AI tidak melakukan pelabelan buta. Telaah pendahuluan media dikerjakan di instalasi uji lokal.
- Belum ada data siswa asli di server. Bank soal baru boleh memakai susunan kode butir yang baru.
- Setiap tantangan yang dinilai cukup punya **dua variasi (a/b)**, bukan tiga.
- Analisis memakai **model multidimensi *between-item***: setiap butir punya tepat satu dimensi utama.
- **D1 bertumpu pada literasi membaca**, dengan dua label sub-aspek: D1a memahami informasi dan D1b menilai informasi dan sumber. Jumlah butir D1 **tidak dibatasi 29**; lebih banyak tidak masalah.
- Beberapa bacaan diubah menjadi **teks bergenre digital**.
- Kisi-kisi bank soal baru disusun di sesi terpisah, di [06_KISI_KISI_BANK_SOAL.md](06_KISI_KISI_BANK_SOAL.md) (branch `kisi-kisi-bank-soal`). Disetujui pada hari yang sama (lihat di atas).
- Draf instrumen validasi ahli (r10 untuk ahli materi dan pengamat budaya, serta instrumen ahli media) ditelaah di [07_TELAAH_INSTRUMEN_VALIDASI.md](07_TELAAH_INSTRUMEN_VALIDASI.md). Versi 1.0 instrumen ahli materi sudah digantikan r10.
- Seluruh isi bank soal yang ada terbuka untuk diubah, diperbaiki, dihapus, dan ditambah sesuai kebutuhan penelitian. Bank soal baru disusun bersama AI. Batas yang tetap berlaku hanya batas teknis penelitian: kunci jawaban tetap di server, butir baru atau yang diubah berstatus `needs_verification` sampai divalidasi, dan kode butir yang sudah punya data siswa tidak dipakai ulang untuk isi yang berbeda.

**Telaah butir dan dimensi** (rumusan keputusannya berlaku sejak kisi-kisi 06 disetujui, lihat di atas)

- Pengembang mengusulkan rumusan keputusan dimensi dan penskoran: satu dimensi utama per butir, D1 sekitar 29 butir, D2 sekitar 12 butir, D3 diubah menjadi *Partisipasi dan Representasi Budaya Kedu di Ruang Digital*, skor utama dari percobaan pertama.
- Telaah butir dan dimensi disusun di [05_TELAAH_BUTIR_DAN_DIMENSI.md](05_TELAAH_BUTIR_DAN_DIMENSI.md): 99 butir dinilai ditelaah satu per satu, ada rumusan keputusan yang sudah disesuaikan, dan daftar perbaikan butir. Rumusan itu berlaku sejak kisi-kisi 06 disetujui.

## 23 Oktober 2026

**Keputusan pengembang**

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

## Titik awal: dokumen 10 analitik dari pengembang

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
