<?php

namespace App\Libraries;

/**
 * Text of the GELITA guide (/admin/panduan-gelita, "Mengenal GELITA"): the
 * whole project and its research from beginning to end, with the reason
 * behind every decision and short references (owner, 17 November 2026).
 * Public like the staff and validator guides, offered on the admin login
 * page.
 *
 * Owner decision, 17 November 2026: public and in full detail. Validators
 * and teachers may read everything here, including item-level and
 * blinding-sensitive detail; this guide deliberately does not follow the
 * validator guide's blinding rule. What must still never be here
 * (GelitaGuideTest refuses it): passwords, secrets, test-account
 * credentials, server or hosting details, and personal data of
 * participants, students or staff (validators appear only under their
 * pseudonyms).
 *
 * Not repeated here, linked instead: pictures, sound and music (the asset
 * catalogue, CATALOGUE; a popover or a page links straight to a challenge's
 * pictures with `#tantangan/{engine}`), the field-day steps and the panel
 * (staff guide), the validators' workflow and accounts (validator guide).
 *
 * Owner, 18 November 2026: written for readers, not developers. No
 * repository documents or file names, no developer terms (they are said in
 * plain words), no dates on the journey page, no technology chapter; every
 * code the text keeps (D1, L3, INF-SUM, C1–C3, SK1–SK4, P1–P5, B1–B4, …)
 * is a glossary term with a popover.
 *
 * Section parts on top of GuideContent's (see StaffGuideContent):
 *   why      list: the reasons behind the decisions on the page
 *   refs     list: works cited for the page, only works the repository's
 *            documents cite (APA style, shortened)
 *   sources  list: `path.md#anchor` under docs/ (GuideSourceCheck::GELITA_DOCS);
 *            never rendered; GelitaGuideTest fails when one changed until
 *            `php spark gelita:panduan:cek --tandai --panduan=gelita`
 */
final class GelitaGuideContent extends GuideContent
{
    public const BASE = 'admin/panduan-gelita';

    /** Sections offered as cards on the first page. */
    public const POPULAR = ['gelita-sekilas', 'tiga-dimensi', 'addie', 'lima-tantangan', 'skenario-digital', 'alur-satu-anak'];

    // Source prefixes (relative to docs/)
    private const R01 = 'revisi-10-analitik/01_REVISI_10_ANALITIK.md#';
    private const R04 = 'revisi-10-analitik/04_CATATAN_KEPUTUSAN.md#';
    private const R22 = 'revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md#';
    private const R26 = 'revisi-10-analitik/26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#';
    private const MODEL = 'revisi-10-analitik/dokumen-pendukung-peneliti/model-penelitian-pengembangan.html';
    private const K1 = 'perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md#';
    private const K2 = 'perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md#';
    private const K3 = 'perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md#';


    // References, cited as the research documents cite them (checked against
    // the originals in the model document, section 13)
    private const BRANCH     = 'Branch, R. M. (2009). Instructional design: The ADDIE approach. Springer.';
    private const PLOMP      = 'Plomp, T. (2013). Educational design research: An introduction. Dalam T. Plomp & N. Nieveen (Ed.), Educational design research – Part A: An introduction (hlm. 10–51). SLO.';
    private const NIEVEEN    = 'Nieveen, N., & Folmer, E. (2013). Formative evaluation in educational design research. Dalam T. Plomp & N. Nieveen (Ed.), Educational design research – Part A (hlm. 152–169). SLO.';
    private const TESSMER    = 'Tessmer, M. (1993). Planning and conducting formative evaluations. Kogan Page.';
    private const MISLEVY    = 'Mislevy, R. J., Steinberg, L. S., & Almond, R. G. (2003). On the structure of educational assessments. Measurement: Interdisciplinary Research and Perspectives, 1(1), 3–62.';
    private const ALMOND     = 'Almond, R. G., Steinberg, L. S., & Mislevy, R. J. (2002). Enhancing the design and delivery of assessment systems: A four-process architecture. Journal of Technology, Learning, and Assessment, 1(5).';
    private const HAERTEL    = 'Mislevy, R. J., & Haertel, G. D. (2006). Implications of evidence-centered design for educational testing. Educational Measurement: Issues and Practice, 25(4), 6–20.';
    private const SHUTE11    = 'Shute, V. J. (2011). Stealth assessment in computer-based games to support learning. Dalam S. Tobias & J. D. Fletcher (Ed.), Computer games and instruction (hlm. 503–524). Information Age Publishing.';
    private const CAMPBELL   = 'Campbell, D. T., & Stanley, J. C. (1963). Experimental and quasi-experimental designs for research. Rand McNally.';
    private const KOMINFO    = 'Kementerian Komunikasi dan Informatika & Katadata Insight Center. (2023). Status literasi digital di Indonesia 2022.';
    private const DIGCOMP    = 'Cosgrove, J., & Cachia, R. (2025). DigComp 3.0: European digital competence framework (5th ed.). Publications Office of the European Union.';
    private const PIRLS      = 'von Davier, M., & Kennedy, A. (Ed.). (2024). PIRLS 2026 assessment frameworks. TIMSS & PIRLS International Study Center, Boston College.';
    private const BLOOM      = 'Anderson, L. W., & Krathwohl, D. R. (Ed.). (2001). A taxonomy for learning, teaching, and assessing. Longman.';
    private const WEBB       = 'Webb, N. L. (1999). Alignment of science and mathematics standards and assessments in four states (Research Monograph No. 18).';
    private const ALWASILAH  = 'Alwasilah, A. C., Suryadi, K., & Karyono, T. (2009). Etnopedagogi: Landasan praktek pendidikan dan pendidikan guru. Kiblat Buku Utama.';
    private const GAY        = 'Gay, G. (2018). Culturally responsive teaching: Theory, research, and practice (3rd ed.). Teachers College Press.';
    private const MALONE     = 'Malone, T. W., & Lepper, M. R. (1987). Making learning fun: A taxonomy of intrinsic motivations for learning. Dalam R. E. Snow & M. J. Farr (Ed.), Aptitude, learning, and instruction: Vol. 3 (hlm. 223–253). Lawrence Erlbaum.';
    private const CSIKS      = 'Csikszentmihalyi, M. (1990). Flow: The psychology of optimal experience. Harper & Row.';
    private const GEE        = 'Gee, J. P. (2003). What video games have to teach us about learning and literacy. Palgrave Macmillan.';
    private const MAYER      = 'Mayer, R. E. (2021). Multimedia learning (3rd ed.). Cambridge University Press.';
    private const SHUTE08    = 'Shute, V. J. (2008). Focus on formative feedback. Review of Educational Research, 78(1), 153–189.';
    private const HATTIE     = 'Hattie, J., & Timperley, H. (2007). The power of feedback. Review of Educational Research, 77(1), 81–112.';
    private const FREIRE     = 'Freire, M., Serrano-Laguna, Á., Manero Iglesias, B., Martínez-Ortiz, I., Moreno-Ger, P., & Fernández-Manjón, B. (2016). Game learning analytics: Learning analytics for serious games. Dalam J. M. Spector dkk. (Ed.), Learning, design, and technology. Springer.';
    private const WILSON     = 'Wilson, M. (2005). Constructing measures: An item response modeling approach. Lawrence Erlbaum.';
    private const BOND       = 'Bond, T. G., & Fox, C. M. (2015). Applying the Rasch model (3rd ed.). Routledge.';
    private const LINACRE    = 'Linacre, J. M. (2002). What do infit and outfit, mean-square and standardized mean? Rasch Measurement Transactions, 16(2), 878.';
    private const ADAMS      = 'Adams, R. J., Wilson, M., & Wang, W.-C. (1997). The multidimensional random coefficients multinomial logit model. Applied Psychological Measurement, 21(1), 1–23.';
    private const MESSICK    = 'Messick, S. (1995). Validity of psychological assessment. American Psychologist, 50(9), 741–749.';
    private const KANE       = 'Kane, M. T. (2013). Validating the interpretations and uses of test scores. Journal of Educational Measurement, 50(1), 1–73.';
    private const AERA       = 'AERA, APA, & NCME. (2014). Standards for educational and psychological testing. American Educational Research Association.';
    private const AIKEN      = 'Aiken, L. R. (1985). Three coefficients for analyzing the reliability and validity of ratings. Educational and Psychological Measurement, 45(1), 131–142.';
    private const LYNN       = 'Lynn, M. R. (1986). Determination and quantification of content validity. Nursing Research, 35(6), 382–385.';
    private const POLIT06    = 'Polit, D. F., & Beck, C. T. (2006). The content validity index: Are you sure you know what\'s being reported? Research in Nursing & Health, 29(5), 489–497.';
    private const POLIT07    = 'Polit, D. F., Beck, C. T., & Owen, S. V. (2007). Is the CVI an acceptable indicator of content validity? Research in Nursing & Health, 30(4), 459–467.';
    private const COHEN60    = 'Cohen, J. (1960). A coefficient of agreement for nominal scales. Educational and Psychological Measurement, 20(1), 37–46.';
    private const COHEN68    = 'Cohen, J. (1968). Weighted kappa: Nominal scale agreement provision for scaled disagreement or partial credit. Psychological Bulletin, 70(4), 213–220.';
    private const NIELSEN    = 'Nielsen, J. (1994). Heuristic evaluation. Dalam J. Nielsen & R. L. Mack (Ed.), Usability inspection methods (hlm. 25–62). John Wiley & Sons.';
    private const ERICSSON   = 'Ericsson, K. A., & Simon, H. A. (1993). Protocol analysis: Verbal reports as data (Rev. ed.). MIT Press.';
    private const BROMP      = 'Ocumpaugh, J., Baker, R. S., & Rodrigo, M. M. T. (2015). Baker Rodrigo Ocumpaugh Monitoring Protocol (BROMP) 2.0 technical and training manual. Teachers College, Columbia University.';
    private const SINGER     = 'Singer, J. D., & Willett, J. B. (2003). Applied longitudinal data analysis. Oxford University Press.';
    private const WISE       = 'Wise, S. L., & Kong, X. (2005). Response time effort: A new measure of examinee motivation in computer-based tests. Applied Measurement in Education, 18(2), 163–183.';
    private const NOSEK      = 'Nosek, B. A., Ebersole, C. R., DeHaven, A. C., & Mellor, D. T. (2018). The preregistration revolution. PNAS, 115(11), 2600–2606.';
    private const UU_PDP     = 'UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi.';
    private const PP_PDP     = 'PP No. 33 Tahun 2026 (pelaksanaan pelindungan data pribadi; berlaku 16 Januari 2027).';
    private const UU_SISNAS  = 'UU No. 11 Tahun 2019, Pasal 40 ayat (5) (masa simpan data penelitian).';
    private const BRIN       = 'Komisi Etik Bidang Sosial Humaniora BRIN. (2026). Panduan klirens etik riset bidang sosial humaniora. Penerbit BRIN.';
    private const PSEPPKN    = 'KEPPKN. (2021). Pedoman dan standar etik penelitian dan pengembangan kesehatan nasional. Lembaga Penerbit Badan Litbangkes.';

    /**
     * Contextual glossary (owner, 18 November 2026): every code and research
     * term the text uses opens a rich popover (kind, picture, meaning,
     * example, the page that explains it, and the asset catalogue when there
     * is something to look at). Developer terms are not glossed; the text
     * says them in plain words instead.
     *
     * @return array<string, array{term: string, def: string, example: string, more: string|null, kind?: string, picture?: string, catalogue?: string}>
     */
    public static function terms(): array
    {
        return [
            // ---- Research model -------------------------------------------
            'literasi-digital' => [
                'kind'    => 'Konstruk',
                'term'    => 'Literasi digital',
                'def'     => 'Hal utama yang diukur GELITA: kemampuan menemukan dan menilai informasi, bertindak aman dan etis, dan ikut menjaga representasi budaya di ruang digital. Di GELITA dipecah menjadi tiga dimensi: D1 literasi informasi, D2 aman dan etis, dan D3 partisipasi dan representasi budaya.',
                'example' => 'Memilih untuk tidak mengirim kode OTP kepada teman, lalu memberi tahu orang tua.',
                'more'    => 'tiga-dimensi',
            ],
            'addie' => [
                'kind'    => 'Model penelitian',
                'term'    => 'ADDIE',
                'def'     => 'Model penelitian dan pengembangan yang dipakai GELITA: Analyze (analisis), Design (rancangan), Develop (pengembangan), Implement (penerapan), Evaluate (evaluasi). Setiap fase diuji, dan temuannya dikembalikan ke fase pengembangan sampai produknya final.',
                'example' => 'Revisi setelah uji kelompok kecil masuk ke Prototipe III sebelum uji kelompok menengah.',
                'more'    => 'addie',
            ],
            'ecd' => [
                'kind'    => 'Model penelitian',
                'term'    => 'Evidence-Centered Design (ECD)',
                'def'     => 'Cara merancang asesmen: tentukan dulu klaim tentang siswa, lalu bukti yang mendukungnya, lalu tugas yang menghasilkan bukti itu.',
                'example' => 'Klaim "siswa bisa menilai sumber" didukung bukti "memilih pengumuman resmi", yang dihasilkan pernyataan di Benar atau Salah.',
                'more'    => 'ecd',
            ],
            'gerbang' => [
                'kind'    => 'Peta jalan',
                'term'    => 'Gerbang (G1–G5)',
                'def'     => 'Titik keputusan di peta jalan penelitian. Tahap berikutnya baru dimulai setelah syarat gerbangnya terpenuhi: G1 desain instrumen selesai, G2 validasi ahli dan izin etik, G3 sistem siap diuji coba, G4 versi dikunci dan rencana analisis didaftarkan, G5 pengambilan data utama selesai.',
                'example' => 'Kegiatan dengan anak baru boleh dimulai setelah G2 dilalui.',
                'more'    => 'tahap-gerbang',
            ],
            'prototipe' => [
                'kind'    => 'Peta jalan',
                'term'    => 'Prototipe I–III',
                'def'     => 'Nama versi produk di sepanjang pengembangan: I berupa dokumen dan contoh tampilan, II sistem yang sudah bisa dicoba di versi uji, III versi lengkap yang direvisi setelah setiap uji dengan siswa.',
                'example' => 'Validator menilai Prototipe I; uji kelompok kecil memakai Prototipe III.',
                'more'    => 'tahap-gerbang',
            ],
            'kegiatan-anak' => [
                'kind'    => 'Kegiatan dengan anak',
                'term'    => 'KG1–KG5',
                'def'     => 'Kegiatan dengan anak yang dimintakan izin etik: KG1 uji keterbacaan, KG2 berpikir lantang, KG3a uji kelompok kecil, KG3b uji kelompok menengah, KG4 wawancara singkat sesudah bermain, KG5 pengambilan data utama.',
                'example' => 'Satu anak hanya ikut satu kegiatan, misalnya KG2 saja.',
                'more'    => 'etik',
            ],
            'pra-registrasi' => [
                'kind'    => 'Statistik',
                'term'    => 'Pra-registrasi (OSF)',
                'def'     => 'Rencana analisis dan skema kode kesalahan didaftarkan di Open Science Framework sebelum data utama diambil, dengan tanggal yang tidak bisa diubah.',
                'example' => 'Batas kecocokan butir ditulis lebih dulu, bukan dipilih setelah melihat hasil.',
                'more'    => 'tahap-gerbang',
            ],

            // ---- What is measured -----------------------------------------
            'd1' => [
                'kind'    => 'Dimensi',
                'term'    => 'D1 Literasi informasi',
                'def'     => 'Menemukan, menafsirkan, dan menilai informasi dari teks dan gambar di layar. Ada dua sub-aspek: D1a memahami informasi, dan D1b menilai informasi dan sumbernya (fakta atau pendapat, sumber yang bisa dipercaya, kabar yang bertentangan).',
                'example' => 'Memilih pengumuman sekolah yang resmi, bukan pesan berantai, sebagai sumber jadwal libur.',
                'more'    => 'tiga-dimensi',
            ],
            'd2' => [
                'kind'    => 'Dimensi',
                'term'    => 'D2 Aman dan etis',
                'def'     => 'Menjaga data pribadi, kata sandi, dan kode OTP; meminta izin sebelum membagikan foto atau obrolan; menghadapi orang asing dan kabar bohong. Diukur lewat Skenario Digital.',
                'example' => 'Tidak membalas pesan orang asing yang menawarkan hadiah, lalu memberi tahu orang tua.',
                'more'    => 'tiga-dimensi',
            ],
            'd3' => [
                'kind'    => 'Dimensi',
                'term'    => 'D3 Partisipasi dan representasi budaya',
                'def'     => 'Bersikap tepat saat budaya Kedu tampil di ruang digital: menyebut pembuat dan sumber karya, memastikan keterangannya benar, meminta izin pelaku dan pemilik karya, dan menghargai tradisi. Diukur lewat Periksa Unggahan.',
                'example' => 'Menandai unggahan video tari yang tidak menyebut kelompok penarinya.',
                'more'    => 'tiga-dimensi',
            ],
            'pbk' => [
                'kind'    => 'Skala pendamping',
                'term'    => 'PBK (Pengetahuan Budaya Kedu)',
                'def'     => 'Skala pendamping tentang mengenali benda dan tradisi budaya Kedu, diukur di Cari Objek babak 1 (Temukan Benda). Dilaporkan terpisah, di luar skor literasi digital.',
                'example' => 'Menemukan gambar kuda lumping di antara benda-benda lain di adegan.',
                'more'    => 'skala-pendamping',
                'catalogue' => 'tantangan/cari',
            ],
            'skala-sikap' => [
                'kind'    => 'Skala pendamping',
                'term'    => 'Skala sikap menjaga warisan',
                'def'     => '12 pernyataan dengan empat pilihan (Tidak setuju sampai Sangat setuju, tanpa pilihan tengah), diisi sekali di Balai Refleksi pada pertemuan 5. Dilaporkan terpisah, di luar skor literasi digital.',
                'example' => '"Candi di daerah Kedu adalah warisan yang berharga." Setuju atau tidak?',
                'more'    => 'skala-pendamping',
            ],
            'aspek-sikap' => [
                'kind'    => 'Skala pendamping',
                'term'    => 'SK1–SK4',
                'def'     => 'Empat indikator skala sikap menjaga warisan: SK1 menghargai, SK2 ingin tahu, SK3 mau ikut merawat, SK4 bangga. Setiap indikator diwakili tiga pernyataan.',
                'example' => '"Kesenian dari daerah Kedu penting untuk dijaga." termasuk SK1 (menghargai).',
                'more'    => 'skala-pendamping',
            ],
            'level-akm' => [
                'kind'    => 'Pelevelan',
                'term'    => 'Level AKM',
                'def'     => 'Tiga tingkat proses membaca dari Asesmen Kompetensi Minimum: menemukan informasi, interpretasi dan integrasi, evaluasi dan refleksi. Di bank soal ditulis singkat L1–L3 (berbeda dari lembar lapangan L0–L8).',
                'example' => 'Butir Temanggung umumnya meminta menemukan informasi yang tertulis jelas di bacaan.',
                'more'    => 'pelevelan',
            ],
            'pirls' => [
                'kind'    => 'Pelevelan',
                'term'    => 'Padanan PIRLS 2026',
                'def'     => 'Proses membaca dalam kerangka studi internasional PIRLS 2026, dipakai sebagai padanan level AKM supaya hasilnya bisa dibandingkan di jurnal internasional.',
                'example' => '"Interpretasi dan integrasi" AKM sepadan dengan "inferensi langsung" dan "menafsirkan dan memadukan" di PIRLS.',
                'more'    => 'pelevelan',
            ],
            'bloom' => [
                'kind'    => 'Pelevelan',
                'term'    => 'Level Bloom (C1–C6)',
                'def'     => 'Tingkat berpikir menurut taksonomi Bloom revisi: C1 mengingat, C2 memahami, C3 menerapkan, C4 menganalisis, C5 mengevaluasi, C6 mencipta. Di GELITA hanya label kedua; acuan utamanya level AKM.',
                'example' => 'Wonosobo memakai butir C2–C5, sampai menilai isi bacaan.',
                'more'    => 'pelevelan',
            ],
            'kerumitan-skenario' => [
                'kind'    => 'Pelevelan',
                'term'    => 'Kerumitan skenario per wilayah',
                'def'     => 'Cara Skenario Digital (D2) naik tingkat: topiknya sama di ketiga wilayah, tetapi tanda bahayanya makin samar dan pilihannya makin banyak.',
                'example' => 'Kata sandi diminta orang asing (Temanggung), teman dekat (Magelang), lalu pesan yang tampak resmi (Wonosobo).',
                'more'    => 'skenario-digital',
            ],
            'kerumitan-adegan' => [
                'kind'    => 'Pelevelan',
                'term'    => 'Kerumitan adegan per wilayah',
                'def'     => 'Cara Periksa Unggahan (D3) naik tingkat: makin banyak bagian unggahan yang bisa diketuk dan makin halus masalah budayanya.',
                'example' => '8 bagian yang bisa diketuk di Temanggung, 10 di Magelang, 12 di Wonosobo.',
                'more'    => 'lima-tantangan',
                'catalogue' => 'tantangan/cari',
            ],
            'skor-tindakan' => [
                'kind'    => 'Penskoran',
                'term'    => 'Skor tindakan 0–1–2',
                'def'     => 'Setiap pilihan tindakan di Skenario Digital punya skor: 2 tindakan yang aman, 1 kurang tepat, 0 berisiko. Skor diambil dari percobaan pertama.',
                'example' => 'Menolak memberi OTP dan memberi tahu orang tua (2); diam saja tanpa melapor (1); mengirim OTP (0).',
                'more'    => 'skenario-digital',
            ],
            'metrik-sandi' => [
                'kind'    => 'Bukti tambahan',
                'term'    => 'Metrik kata sandi saat daftar',
                'def'     => 'Saat mendaftar, sistem mencatat berapa syarat kata sandi yang dipenuhi siswa dan berapa kali kata sandinya ditolak. Kata sandinya sendiri tidak pernah dilihat. Angka ini menjadi bukti tambahan D2, tidak masuk skor.',
                'example' => 'Siswa yang langsung memenuhi semua syarat tercatat tanpa penolakan.',
                'more'    => 'tiga-dimensi',
            ],
            'beban-permukaan' => [
                'kind'    => 'Desain soal',
                'term'    => 'Beban permukaan',
                'def'     => 'Hal yang membuat soal terasa sulit tanpa mengubah kemampuan yang diukur: jumlah rumpang, jumlah kata pengecoh, panjang bacaan, jumlah benda pengecoh. Dicatat per butir supaya pengaruhnya bisa dipisahkan saat analisis.',
                'example' => 'Bacaan Wonosobo lebih panjang; panjangnya ikut dicatat.',
                'more'    => 'pelevelan',
            ],
            'teks-digital' => [
                'kind'    => 'Desain soal',
                'term'    => 'Teks bergenre digital',
                'def'     => 'Bacaan yang tampil seperti teks yang biasa ditemui anak di layar: unggahan media sosial, pengumuman, obrolan grup, hasil pencarian.',
                'example' => 'Pengumuman lomba yang tampil sebagai unggahan di Kedugram.',
                'more'    => 'bank-soal',
            ],

            // ---- Error and reason codes -------------------------------------
            'kode-kesalahan' => [
                'kind'    => 'Kode kesalahan',
                'term'    => 'Kode kesalahan',
                'def'     => 'Kode jenis kekeliruan yang dipasang pada setiap pilihan salah sebelum data diambil, lalu ikut tercatat di setiap percobaan salah. Ada empat kelompok: INF (informasi), DIG (tindakan digital), BUD (budaya digital), dan NON (penanda prosedur).',
                'example' => 'Memilih pesan berantai sebagai sumber yang dipercaya tercatat INF-SUM.',
                'more'    => 'kode-kesalahan',
            ],
            'kode-inf' => ['kind' => 'Kode kesalahan', 'term' => 'INF: salah memilih informasi', 'def' => 'Kelompok kode untuk kekeliruan membaca dan menilai informasi, dimensi D1 (INF-BUD khusus skala PBK).', 'example' => 'INF-LIT, INF-SUM, INF-FOP.', 'more' => 'kode-kesalahan'],
            'inf-lit' => ['kind' => 'Kode kesalahan · D1', 'term' => 'INF-LIT', 'def' => 'Salah membaca informasi yang tertulis jelas di bacaan.', 'example' => 'Bacaan menyebut festival hari Sabtu, siswa memilih Minggu.', 'more' => 'kode-kesalahan'],
            'inf-sum' => ['kind' => 'Kode kesalahan · D1', 'term' => 'INF-SUM', 'def' => 'Salah menilai sumber: memercayai sumber yang tidak jelas atau mengabaikan sumber resmi.', 'example' => 'Memilih pesan berantai daripada pengumuman dinas.', 'more' => 'kode-kesalahan'],
            'inf-fop' => ['kind' => 'Kode kesalahan · D1', 'term' => 'INF-FOP', 'def' => 'Mencampur fakta dan pendapat.', 'example' => 'Menganggap "telaga paling indah" sebagai fakta.', 'more' => 'kode-kesalahan'],
            'inf-bud' => ['kind' => 'Kode kesalahan · PBK', 'term' => 'INF-BUD', 'def' => 'Tertukar benda budaya yang mirip. Hanya dipakai di Cari Objek babak 1, untuk skala PBK, tidak untuk skor literasi digital.', 'example' => 'Mengetuk topeng lain saat yang dicari topeng lengger.', 'more' => 'kode-kesalahan'],
            'kode-dig' => ['kind' => 'Kode kesalahan', 'term' => 'DIG: salah memilih tindakan digital', 'def' => 'Kelompok kode untuk tindakan yang berisiko atau kurang tepat di Skenario Digital, dimensi D2.', 'example' => 'DIG-PRI, DIG-SAN, DIG-ASG.', 'more' => 'kode-kesalahan'],
            'dig-pri' => ['kind' => 'Kode kesalahan · D2', 'term' => 'DIG-PRI', 'def' => 'Membagikan data pribadi berlebihan (berisiko).', 'example' => 'Mengisi alamat rumah dan nama sekolah demi hadiah undian.', 'more' => 'kode-kesalahan'],
            'dig-asg' => ['kind' => 'Kode kesalahan · D2', 'term' => 'DIG-ASG', 'def' => 'Menanggapi orang asing (berisiko).', 'example' => 'Membalas pesan dari akun tak dikenal yang mengaku kakak kelas.', 'more' => 'kode-kesalahan'],
            'kode-bud' => ['kind' => 'Kode kesalahan', 'term' => 'BUD: salah dalam budaya digital', 'def' => 'Kelompok kode untuk kekeliruan saat budaya tampil di ruang digital, dimensi D3, di Periksa Unggahan.', 'example' => 'BUD-ATR, BUD-REP, BUD-IZN, BUD-ETK.', 'more' => 'kode-kesalahan'],
            'bud-atr' => ['kind' => 'Kode kesalahan · D3', 'term' => 'BUD-ATR', 'def' => 'Tidak menyebut, atau salah menyebut, pembuat dan sumber karya budaya.', 'example' => 'Menganggap wajar foto batik tanpa nama perajinnya.', 'more' => 'kode-kesalahan'],
            'bud-rep' => ['kind' => 'Kode kesalahan · D3', 'term' => 'BUD-REP', 'def' => 'Menerima atau menyebarkan keterangan budaya yang keliru.', 'example' => 'Tidak menandai keterangan yang menyebut candi di daerah yang salah.', 'more' => 'kode-kesalahan'],
            'bud-izn' => ['kind' => 'Kode kesalahan · D3', 'term' => 'BUD-IZN', 'def' => 'Mengunggah ulang penampilan atau karya budaya tanpa izin pelaku atau pemiliknya.', 'example' => 'Menganggap wajar video pentas yang diunggah ulang tanpa izin kelompoknya.', 'more' => 'kode-kesalahan'],
            'bud-etk' => ['kind' => 'Kode kesalahan · D3', 'term' => 'BUD-ETK', 'def' => 'Menanggapi tradisi dengan cara yang tidak menghormati.', 'example' => 'Tidak menandai komentar yang mengejek upacara adat.', 'more' => 'kode-kesalahan'],
            'kode-non' => ['kind' => 'Kode kesalahan', 'term' => 'NON: penanda prosedur', 'def' => 'Penanda yang dihitung sistem, bukan jenis salah paham: menebak cepat (menjawab kurang dari 3 detik), salah ketuk di dekat sasaran, dan jawaban terakhir saat kesempatan habis.', 'example' => 'Jawaban dalam 1 detik tanpa membaca diberi penanda tebakan cepat.', 'more' => 'kode-kesalahan'],
            'kode-r' => ['kind' => 'Kode alasan', 'term' => 'R: alasan di Skenario Digital', 'def' => 'Kode alasan yang dipilih siswa setelah memilih tindakan. Dipakai untuk diagnosis, tidak masuk skor utama.', 'example' => 'R-RISK, R-ETIK, R-ATUR, R-SOS, R-ACAK.', 'more' => 'kode-kesalahan'],
            'r-risk' => ['kind' => 'Kode alasan · D2', 'term' => 'R-RISK', 'def' => 'Paham risikonya. Alasan yang diharapkan.', 'example' => '"Kalau kodenya kuberikan, akunku bisa diambil orang."', 'more' => 'kode-kesalahan'],
            'r-etik' => ['kind' => 'Kode alasan · D2', 'term' => 'R-ETIK', 'def' => 'Empati atau izin. Alasan yang diharapkan.', 'example' => '"Temanku belum tentu mau fotonya dibagikan."', 'more' => 'kode-kesalahan'],
            'r-atur' => ['kind' => 'Kode alasan · D2', 'term' => 'R-ATUR', 'def' => 'Karena disuruh atau dilarang. Tindakannya bisa benar, tetapi alasannya dangkal.', 'example' => '"Soalnya dilarang Ibu."', 'more' => 'kode-kesalahan'],
            'r-sos' => ['kind' => 'Kode alasan · D2', 'term' => 'R-SOS', 'def' => 'Tekanan teman atau ingin disukai.', 'example' => '"Biar teman-teman tidak marah."', 'more' => 'kode-kesalahan'],
            'r-acak' => ['kind' => 'Kode alasan · D2', 'term' => 'R-ACAK', 'def' => 'Alasannya tidak berhubungan dengan situasi.', 'example' => 'Memilih alasan tentang cuaca di skenario kata sandi.', 'more' => 'kode-kesalahan'],
            'pengodean-manual' => [
                'kind'    => 'Data',
                'term'    => 'Pengodean manual',
                'def'     => 'Kode yang diberi peneliti dengan tangan untuk kekeliruan yang tidak cocok dengan kode mana pun (INF-LAIN, DIG-LAIN, BUD-LAIN) dan untuk alasan di wawancara. Disimpan terpisah dari kode yang diberi sistem.',
                'example' => 'Jawaban wawancara "aku asal pilih yang warnanya bagus" dikode manual dari lembar L5.',
                'more'    => 'kode-kesalahan',
            ],

            // ---- The game -------------------------------------------------
            'susun-gambar' => [
                'kind' => 'Tantangan 1', 'term' => 'Susun Gambar',
                'def' => 'Menyusun keping gambar khas wilayah menjadi utuh. Pemanasan dan pengenalan wilayah; tidak dinilai.',
                'example' => 'Gambar 3 × 3 keping di Temanggung, jigsaw 4 × 4 di Wonosobo.',
                'more' => 'lima-tantangan', 'picture' => 'ui/node-card-puzzle.webp', 'catalogue' => 'tantangan/puzzle',
            ],
            'isi-rumpang' => [
                'kind' => 'Tantangan 2 · D1', 'term' => 'Isi Rumpang',
                'def' => 'Melengkapi bacaan yang rumpang dengan menyeret kata yang tepat. Setiap rumpang punya dua kata pengecoh.',
                'example' => 'Bacaan Temanggung dengan 4 rumpang dan 8 kata pengecoh.',
                'more' => 'lima-tantangan', 'picture' => 'ui/node-card-rumpang.webp', 'catalogue' => 'tantangan/rumpang',
            ],
            'benar-salah' => [
                'kind' => 'Tantangan 3 · D1', 'term' => 'Benar atau Salah',
                'def' => 'Menandai setiap pernyataan tentang bacaan sebagai benar atau salah, ditambah dua pernyataan di kartu jangkar.',
                'example' => '"Pengumuman ini dibuat oleh kantor desa." Benar atau salah?',
                'more' => 'lima-tantangan', 'picture' => 'ui/node-card-boleh.webp', 'catalogue' => 'tantangan/boleh',
            ],
            'cari-objek' => [
                'kind' => 'Tantangan 4', 'term' => 'Cari Objek',
                'def' => 'Dua babak dalam satu tantangan: babak 1 Temukan Benda (benda budaya di adegan, untuk skala PBK) dan babak 2 Periksa Unggahan (bagian unggahan yang bermasalah, untuk D3).',
                'example' => 'Mengetuk gamelan di adegan pendopo, lalu mengetuk keterangan foto yang keliru.',
                'more' => 'lima-tantangan', 'picture' => 'ui/node-card-cari.webp', 'catalogue' => 'tantangan/cari',
            ],
            'temukan-benda' => [
                'kind' => 'Cari Objek babak 1 · PBK', 'term' => 'Temukan Benda',
                'def' => 'Babak pertama Cari Objek: siswa mengetuk benda budaya yang disebut petunjuk di sebuah adegan, di antara benda pengecoh. Butirnya sama untuk semua siswa dan dihitung untuk skala PBK.',
                'example' => '4 benda yang dicari di antara 4, 6, atau 8 pengecoh.',
                'more' => 'skala-pendamping', 'picture' => 'ui/node-card-cari.webp', 'catalogue' => 'tantangan/cari',
            ],
            'periksa-unggahan' => [
                'kind' => 'Cari Objek babak 2 · D3', 'term' => 'Periksa Unggahan',
                'def' => 'Babak kedua Cari Objek: siswa melihat unggahan tentang budaya Kedu di Kedugram dan mengetuk bagian yang bermasalah (sumber tidak disebut, keterangan keliru, tanpa izin, atau tidak menghormati).',
                'example' => 'Mengetuk keterangan foto yang menyebut Candi Borobudur ada di Wonosobo.',
                'more' => 'lima-tantangan', 'picture' => 'challenge.post.tmg-4-b', 'catalogue' => 'tantangan/cari',
            ],
            'skenario-digital' => [
                'kind' => 'Tantangan 5 · D2', 'term' => 'Skenario Digital',
                'def' => 'Situasi pendek berlatar Kedu dengan sudut pandang "kamu": siswa memilih tindakan, lalu memilih alasannya. Ada empat skenario per wilayah.',
                'example' => 'Seseorang di grup kelas meminta kode OTP. Apa yang kamu lakukan, dan mengapa?',
                'more' => 'skenario-digital', 'picture' => 'ui/node-card-skenario.webp', 'catalogue' => 'tantangan/skenario',
            ],
            'kedugram' => [
                'kind' => 'Isi permainan', 'term' => 'Kedugram',
                'def' => 'Aplikasi media sosial fiktif tempat semua unggahan di GELITA tampil, supaya tidak ada merek atau akun nyata. Nama akunnya dicek tidak sama dengan akun sungguhan.',
                'example' => 'Unggahan pentas kuda lumping di Periksa Unggahan tampil sebagai kiriman Kedugram.',
                'more' => 'skenario-digital', 'catalogue' => 'tantangan/cari',
            ],
            'kartu-jangkar' => [
                'kind' => 'Desain soal', 'term' => 'Kartu jangkar',
                'def' => 'Bacaan kedua di Benar atau Salah yang sama untuk semua siswa, dengan dua pernyataan. Di layar disebut "Bacaan kedua".',
                'example' => 'Siswa dengan variasi a dan siswa dengan variasi b membaca kartu jangkar yang sama.',
                'more' => 'variasi-buklet', 'picture' => 'ui/node-card-boleh.webp', 'catalogue' => 'tantangan/boleh',
            ],
            'butir-jangkar' => [
                'kind' => 'Desain soal', 'term' => 'Butir jangkar',
                'def' => 'Pernyataan di kartu jangkar (6 seluruhnya, 2 per wilayah) yang dijawab semua siswa. Karena sama untuk semua, butir ini menyambungkan variasi a dan b ke satu skala.',
                'example' => 'Kalau siswa variasi a lebih sering benar di butir jangkar, perbedaan kemampuan itu ikut diperhitungkan.',
                'more' => 'variasi-buklet',
            ],
            'variasi' => [
                'kind' => 'Desain soal', 'term' => 'Variasi a dan b',
                'def' => 'Setiap tantangan yang dinilai punya dua susunan soal setara, a dan b, dengan bacaan berbeda tetapi kisi-kisi sama. Variasi untuk pengerjaan pertama ditentukan buklet siswa.',
                'example' => 'Isi Rumpang Temanggung variasi a dan b memakai bacaan berbeda dengan jumlah rumpang dan level yang sama.',
                'more' => 'variasi-buklet',
            ],
            'butir-bersama' => [
                'kind' => 'Desain soal', 'term' => 'Butir bersama (x)',
                'def' => 'Butir yang sama untuk semua siswa, apa pun bukletnya: kartu jangkar di Benar atau Salah dan Temukan Benda di Cari Objek babak 1. Di bank soal ditandai huruf x.',
                'example' => 'Semua siswa mencari benda yang sama di babak 1 Cari Objek Wonosobo.',
                'more' => 'variasi-buklet',
            ],
            'buklet' => [
                'kind' => 'Desain soal', 'term' => 'Buklet (B1–B4)',
                'def' => 'Gabungan variasi untuk ketiga wilayah: B1 a-a-a, B2 b-b-b, B3 a-b-a, B4 b-a-b. Ditentukan dari baris dan kursi siswa di denah kelas, dan dipakai sampai pertemuan terakhir.',
                'example' => 'Dua siswa yang duduk bersebelahan mendapat buklet berbeda.',
                'more' => 'variasi-buklet',
            ],
            'percobaan-pertama' => [
                'kind' => 'Data', 'term' => 'Percobaan pertama',
                'def' => 'Jawaban siswa sebelum mendapat umpan balik apa pun. Inilah data inti analisis; kesempatan kedua dan ketiga disediakan untuk belajar.',
                'example' => 'Pilihan tindakan pertama di sebuah Skenario Digital.',
                'more' => 'tiga-kesempatan',
            ],
            'serpihan' => [
                'kind' => 'Isi permainan', 'term' => 'Serpihan Cahaya',
                'def' => 'Hadiah di setiap tantangan yang selesai, 15 seluruhnya, untuk memulihkan cahaya Lentera Kedu dalam cerita. Tidak dianalisis.',
                'example' => 'Siswa tetap mendapat serpihan walaupun kesempatannya habis.',
                'more' => 'bintang-serpihan', 'picture' => 'ui.lantern', 'catalogue' => 'gambar/ui.lantern',
            ],
            'tokoh' => [
                'kind' => 'Isi permainan', 'term' => 'Jaka dan Mbah Kedu',
                'def' => 'Dua tokoh cerita: Jaka, anak kelas 5 berselendang merah yang dimainkan siswa, dan Mbah Kedu, penjaga Lentera Kedu yang membimbingnya.',
                'example' => 'Mbah Kedu membuka setiap wilayah dengan dialog pembuka.',
                'more' => 'cerita-wilayah', 'picture' => 'ui.jaka-avatar', 'catalogue' => 'kelompok/character',
            ],
            'balai-refleksi' => [
                'kind' => 'Tempat di game', 'term' => 'Balai Refleksi',
                'def' => 'Pendopo tempat siswa singgah setelah ketiga wilayah tuntas: mengisi skala sikap menjaga warisan, lalu memberi kritik dan saran.',
                'example' => 'Pada pertemuan 5, setelah cerita penutup, siswa masuk ke Balai Refleksi.',
                'more' => 'cerita-wilayah', 'picture' => 'bg.reflection', 'catalogue' => 'gambar/bg.reflection',
            ],
            'pustaka-kedu' => [
                'kind' => 'Tempat di game', 'term' => 'Pustaka Kedu',
                'def' => '30 halaman bacaan dwibahasa tentang Temanggung, Magelang, dan Wonosobo, terbuka per wilayah setelah wilayahnya tuntas.',
                'example' => 'Halaman tentang Telaga Warna terbuka setelah Wonosobo tuntas.',
                'more' => 'pustaka-bahasa', 'picture' => 'ui/icon-pustaka.webp', 'catalogue' => 'kelompok/library',
            ],
            'bantuan' => [
                'kind' => 'Isi permainan', 'term' => 'Bantuan (tombol 💡)',
                'def' => 'Tombol di setiap tantangan yang menjelaskan cara mengerjakan, tanpa memberi jawaban. Setiap pembukaannya tercatat.',
                'example' => 'Di Isi Rumpang, Bantuan menjelaskan cara menyeret kata ke rumpang.',
                'more' => 'bantuan-narasi',
            ],
            'narasi' => [
                'kind' => 'Isi permainan', 'term' => 'Narasi',
                'def' => 'Rekaman suara cerita, instruksi, dan sebagian soal. Bacaan D1 tidak pernah dibacakan; situasi Skenario Digital dan petunjuk Periksa Unggahan bisa diputar lewat tombol ▶.',
                'example' => 'Siswa yang lambat membaca bisa mendengarkan situasi skenario.',
                'more' => 'bantuan-narasi', 'catalogue' => 'narasi',
            ],
            'panggung' => [
                'kind' => 'Tampilan', 'term' => 'Layar game (panggung 16:9)',
                'def' => 'Cara soal sampai ke siswa: semua layar game digambar di satu bidang berukuran tetap 16:9 yang diperbesar atau diperkecil utuh, jadi letak setiap bagian sama di semua perangkat.',
                'example' => 'Di Chromebook dan laptop, benda Cari Objek ada di tempat yang sama.',
                'more' => 'panggung-16x9',
            ],

            // ---- Field work -----------------------------------------------
            'mode-penelitian' => [
                'kind' => 'Lapangan', 'term' => 'Mode penelitian',
                'def' => 'Sakelar per studi yang menyalakan semua aturan pengambilan data: token sesi, jam tersembunyi, penolakan layar kecil, buklet, dan lain-lain. Mati kecuali untuk studi yang sedang berjalan.',
                'example' => 'Saat mode penelitian menyala, game hanya bisa dimainkan di dalam sesi lapangan.',
                'more' => 'mode-penelitian',
            ],
            'sesi-lapangan' => [
                'kind' => 'Lapangan', 'term' => 'Sesi lapangan',
                'def' => 'Satu pertemuan di satu kelas yang diamati peneliti, dengan token sendiri dan lembar lapangan sendiri.',
                'example' => 'Pertemuan 3 kelas 5A di sebuah sekolah adalah satu sesi lapangan.',
                'more' => 'sesi-lapangan',
            ],
            'token-sesi' => [
                'kind' => 'Lapangan', 'term' => 'Token sesi',
                'def' => 'Enam huruf dan angka yang ditulis peneliti di papan tulis. Siswa memasukkannya untuk masuk ke sesi; token membawa sekolah, kelas, dan wilayah pertemuan itu.',
                'example' => 'KD7RMA. Huruf yang mudah tertukar (O, 0, I, 1, L) tidak dipakai.',
                'more' => 'sesi-lapangan',
            ],
            'kode-peserta' => [
                'kind' => 'Lapangan', 'term' => 'Kode peserta',
                'def' => 'Pengganti nama siswa di semua data penelitian: kode sekolah penelitian, huruf rombel, dan nomor urut. Sekolah uji coba berawalan U.',
                'example' => 'S03-A07: sekolah S03, rombel A, siswa ke-7.',
                'more' => 'sesi-lapangan',
            ],
            'pertemuan' => [
                'kind' => 'Lapangan', 'term' => 'Pertemuan P1–P5',
                'def' => 'Lima pertemuan per kelas: P1 pendaftaran dan tes awal, P2 Temanggung, P3 Magelang, P4 Wonosobo, P5 penutup, tes akhir, dan skala sikap.',
                'example' => 'Siswa yang absen di P3 mengikuti sesi susulan dalam tiga hari.',
                'more' => 'sesi-lapangan',
            ],
            'peneliti-lapangan' => [
                'kind' => 'Lapangan', 'term' => 'Peneliti P1–P3',
                'def' => 'Tiga peran peneliti di setiap kelas: P1 koordinator sesi, P2 pengamat keterlibatan, P3 pengamat bantuan. Berbeda dari pertemuan P1–P5.',
                'example' => 'P3 mencatat setiap kali siswa dibantu di lembar L3.',
                'more' => 'sesi-lapangan',
            ],
            'lembar-lapangan' => [
                'kind' => 'Lembar lapangan', 'term' => 'Lembar lapangan L0–L8',
                'def' => 'Sembilan lembar yang diisi peneliti untuk hal yang tidak terlihat di catatan game: persiapan, kondisi kelas, tempat duduk, bantuan, keterlibatan, wawancara, tes, izin, dan profil sekolah. Kuncinya sama dengan catatan game: kode peserta dan jam.',
                'example' => 'L3 mencatat jam 09.12, siswa S03-A07 dibantu memakai headset.',
                'more' => 'sesi-lapangan',
            ],
            'l0' => ['kind' => 'Lembar lapangan', 'term' => 'L0', 'def' => 'Daftar periksa persiapan sesi, diisi koordinator (P1).', 'example' => 'Perangkat terisi daya, token siap, headset dicek.', 'more' => 'sesi-lapangan'],
            'l1' => ['kind' => 'Lembar lapangan', 'term' => 'L1', 'def' => 'Kondisi sesi dan penyimpangan dari protokol, diisi koordinator (P1).', 'example' => 'Listrik padam 10 menit di tengah pertemuan.', 'more' => 'sesi-lapangan'],
            'l2' => ['kind' => 'Lembar lapangan', 'term' => 'L2', 'def' => 'Peta tempat duduk, perangkat, dan kode peserta; dari sini buklet dan bentuk tes ditentukan.', 'example' => 'Baris 2 kursi 3: S03-A07, Chromebook nomor 12.', 'more' => 'variasi-buklet'],
            'l3' => ['kind' => 'Lembar lapangan', 'term' => 'L3', 'def' => 'Catatan bantuan dan gangguan, diisi pengamat bantuan (P3).', 'example' => 'Siswa bertanya cara menyeret kata; dijawab tanpa menyebut isi soal.', 'more' => 'sesi-lapangan'],
            'l4' => ['kind' => 'Lembar lapangan', 'term' => 'L4', 'def' => 'Pengamatan keterlibatan dan emosi siswa, mengadaptasi protokol BROMP, diisi pengamat keterlibatan (P2).', 'example' => 'Siswa tampak bingung di Periksa Unggahan, lalu kembali fokus.', 'more' => 'sesi-lapangan'],
            'l5' => ['kind' => 'Lembar lapangan', 'term' => 'L5', 'def' => 'Wawancara singkat sesudah bermain (tiga siswa per pertemuan wilayah) dan catatan berpikir lantang di uji coba.', 'example' => '"Kenapa kamu memilih membalas pesan itu?"', 'more' => 'sesi-lapangan'],
            'l6' => ['kind' => 'Lembar lapangan', 'term' => 'L6', 'def' => 'Catatan pelaksanaan tes awal–akhir dan angket. Jawabannya sendiri tersimpan di modul tes.', 'example' => 'Dua siswa mendapat tambahan waktu 5 menit.', 'more' => 'modul-tes'],
            'l7' => ['kind' => 'Lembar lapangan', 'term' => 'L7', 'def' => 'Izin orang tua dan status setiap peserta.', 'example' => 'Izin rekaman suara: tidak; butir risiko daring: ya.', 'more' => 'etik'],
            'l8' => ['kind' => 'Lembar lapangan', 'term' => 'L8', 'def' => 'Profil sekolah: letak, jumlah kelas 5, perangkat, internet, listrik, dan pengalaman asesmen digital. Diisi sekali per sekolah.', 'example' => 'Sekolah memiliki 20 Chromebook dan internet yang sering lambat.', 'more' => 'tahap-gerbang'],
            'log-game' => [
                'kind' => 'Data', 'term' => 'Log game',
                'def' => 'Catatan otomatis dari permainan: setiap jawaban, percobaan, bantuan yang dibuka, narasi yang diputar, perpindahan layar, dan waktunya. Hanya catatan yang dipakai sepuluh analitik yang disimpan.',
                'example' => 'Pukul 09.14:05 siswa S03-A07 menekan Cek jawabanku di Isi Rumpang Temanggung.',
                'more' => 'log-event',
            ],

            // ---- Data and statistics --------------------------------------
            'data-turunan' => [
                'kind' => 'Data', 'term' => 'Data turunan',
                'def' => 'Nilai analisis yang dihitung sistem dari data mentah dengan aturan tetap: waktu aktif, tebakan cepat, "dilewati", tersensor, skor, dan indeks.',
                'example' => 'Waktu aktif sebuah butir: waktu total dikurangi layar tersembunyi dan jeda panjang.',
                'more' => 'lapis-turunan',
            ],
            'kamus-data' => [
                'kind' => 'Data', 'term' => 'Kamus data',
                'def' => 'Definisi tetap semua variabel dan aturan hitung, bernomor versi, yang ikut di setiap unduhan data penelitian.',
                'example' => 'Unduhan saat ini memakai kamus versi 1.1.',
                'more' => 'kamus-data',
            ],
            'waktu-aktif' => ['kind' => 'Data', 'term' => 'Waktu aktif', 'def' => 'Waktu mengerjakan setelah dikurangi saat layar tersembunyi dan jeda tanpa gerakan yang panjang (sementara: lebih dari 3 menit).', 'example' => 'Siswa membuka butir 5 menit, tetapi 2 menit ditinggal: waktu aktifnya 3 menit.', 'more' => 'lapis-turunan'],
            'tebakan-cepat' => ['kind' => 'Data', 'term' => 'Tebakan cepat', 'def' => 'Jawaban yang diberikan terlalu cepat untuk sempat membaca (sementara: kurang dari 3 detik), jadi tidak mencerminkan kemampuan.', 'example' => 'Pernyataan Benar atau Salah dijawab dalam 1 detik.', 'more' => 'sepuluh-analitik'],
            'tersensor' => ['kind' => 'Data', 'term' => 'Tersensor', 'def' => 'Butir yang belum berhasil setelah tiga kesempatan. Tidak dihitung sebagai "3 percobaan", karena siswa mungkin butuh lebih dari tiga; analisis memperlakukannya sebagai data yang terpotong.', 'example' => 'Skenario yang tetap berisiko di kesempatan ketiga.', 'more' => 'sepuluh-analitik'],
            'dilewati' => ['kind' => 'Data', 'term' => '"Dilewati"', 'def' => 'Butir yang tidak dikerjakan, dibedakan dalam tiga jenis supaya penyebabnya terlihat (misalnya waktu habis, ditinggal, atau tidak sampai).', 'example' => 'Butir yang tidak sempat dibuka karena pertemuan selesai.', 'more' => 'kamus-data'],
            'rasch' => ['kind' => 'Statistik', 'term' => 'Model Rasch', 'def' => 'Model statistik yang menaruh kemampuan siswa dan kesulitan butir di satu penggaris yang sama. Dipakai untuk memeriksa apakah butir mengukur hal yang sama dan apakah urutan kesulitannya masuk akal.', 'example' => 'Butir Wonosobo diharapkan berada lebih tinggi di penggaris daripada butir Temanggung.', 'more' => 'kriteria-mutu'],
            'mnsq' => ['kind' => 'Statistik', 'term' => 'MNSQ (infit dan outfit)', 'def' => 'Angka kecocokan butir dalam model Rasch. Butir dianggap cocok bila angkanya antara 0,5 dan 1,5.', 'example' => 'Butir dengan outfit 2,1 diperiksa ulang: mungkin kalimatnya membingungkan.', 'more' => 'kriteria-mutu'],
            'dif' => ['kind' => 'Statistik', 'term' => 'DIF', 'def' => 'Pemeriksaan apakah sebuah butir lebih sulit bagi satu kelompok daripada kelompok lain yang kemampuannya sama, misalnya variasi a dan b, atau anak laki-laki dan perempuan.', 'example' => 'Bila butir variasi b jauh lebih sulit pada kemampuan yang sama, kesetaraannya diperiksa.', 'more' => 'variasi-buklet'],
            'v-aiken' => ['kind' => 'Statistik', 'term' => 'V Aiken', 'def' => 'Angka 0–1 yang merangkum penilaian relevansi beberapa ahli terhadap satu butir. Makin dekat ke 1, makin sepakat ahli bahwa butir itu relevan.', 'example' => 'Lima ahli memberi nilai 4, 4, 4, 3, 4 pada skala 1–4.', 'more' => 'validasi'],
            'icvi' => ['kind' => 'Statistik', 'term' => 'I-CVI dan S-CVI/Ave', 'def' => 'Indeks validitas isi: I-CVI per butir (bagian ahli yang menilai relevan), S-CVI/Ave rata-rata I-CVI seluruh butir. Dengan lima ahli atau kurang, butir lolos hanya bila semua ahli setuju.', 'example' => 'I-CVI 1,00: kelima ahli menilai butir itu relevan.', 'more' => 'validasi'],
            'kappa' => ['kind' => 'Statistik', 'term' => 'κ berbobot (kappa)', 'def' => 'Ukuran kesepakatan dua penilai yang sudah memperhitungkan kebetulan. Versi berbobot memberi nilai sebagian bila beda labelnya hanya satu tingkat. GELITA memakai batas 0,70.', 'example' => 'Dua ahli memberi level AKM yang sama pada sebagian besar butir.', 'more' => 'validasi'],
            'pelabelan-buta' => ['kind' => 'Validasi', 'term' => 'Pelabelan buta (B.5a)', 'def' => 'Dua ahli memberi label level pada butir tanpa melihat label pengembang dan tanpa kode yang menyebut wilayahnya, supaya kesepakatannya jujur. B.5a adalah nama lembarnya di paket validasi.', 'example' => 'Butir hanya diberi nomor netral, bukan kode yang menyebut wilayah.', 'more' => 'validasi'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function chapters(): array
    {
        $chapters = [
            self::start(),
            self::origin(),
            self::model(),
            self::construct(),
            self::game(),
            self::overhaul(),
            self::revision(),
            self::field(),
            self::together(),
        ];

        // No audiences: every chapter is for every reader
        foreach ($chapters as &$chapter) {
            $chapter += ['open' => true, 'mine' => false, 'for' => ''];
        }

        return $chapters;
    }

    /** @return array<string, mixed> */
    private static function start(): array
    {
        return [
            'id'       => 'mulai',
            'title'    => 'Mulai di sini',
            'lead'     => 'Apa isi panduan ini, untuk siapa, dan GELITA dalam satu halaman.',
            'sections' => [
                [
                    'id'    => 'tentang-panduan',
                    'title' => 'Tentang panduan ini',
                    'lead'  => 'Panduan ini menceritakan GELITA dari awal sampai akhir: masalah yang dijawab, model penelitiannya, apa yang diukur, desain permainan, dan pengambilan datanya. Setiap halaman menjelaskan **mengapa** sebuah keputusan diambil, lalu menyebut rujukannya.',
                    'list'  => [
                        '**Untuk siapa.** Tim peneliti, validator ahli, guru, kepala sekolah, dan siapa pun yang ingin memahami GELITA lebih dalam. Panduan ini terbuka dan dapat dibaca tanpa masuk.',
                        '**Isi lengkap.** Pengembang memutuskan panduan ini memuat semua rincian, termasuk rincian butir dan [[kode kesalahan|kode-kesalahan]]. Bagi validator yang sedang mengerjakan [[pelabelan buta|pelabelan-buta]], sebaiknya halaman bab [Apa yang diukur](#tiga-dimensi) dan [Revisi 10](#bank-soal) dibaca **setelah** lembar pelabelannya dikirim.',
                        '**Cara membaca.** Setiap halaman punya bagian **Mengapa begini** (alasan keputusan) dan **Rujukan** (karya yang dikutip dokumen penelitian). Istilah bergaris titik-titik dapat disentuh atau ditunjuk untuk melihat arti, contoh, dan halaman penjelasnya.',
                        '**Yang tidak dibahas di sini.** Gambar, suara, dan musik ada di [Katalog Aset GELITA](' . self::CATALOGUE . '). Langkah kerja di panel dan hari lapangan ada di [Panduan staf](/admin/panduan-staf). Bahan dan akun validator ada di [Panduan validator](/admin/panduan-validator).',
                    ],
                    'note'    => 'Panduan ini merangkum dokumen penelitian GELITA. Bila ada yang berbeda, dokumen penelitian dan catatan keputusan yang berlaku.',
                    'why'     => [
                        'Keputusan GELITA tersebar di puluhan dokumen penelitian. Satu panduan yang menyambungkannya memudahkan orang baru memahami alasannya tanpa membaca semuanya.',
                        'Rincian yang sensitif bagi validator sengaja dibuka (keputusan pengembang): pembaca panduan ini sudah menyelesaikan validasi atau tidak sedang menilai.',
                    ],
                    'refs'    => [self::BRANCH],
                    'sources' => [self::R01 . 'judul-penelitian-dan-konstruk'],
                ],
                [
                    'id'    => 'gelita-sekilas',
                    'title' => 'GELITA dalam satu halaman',
                    'lead'  => 'GELITA (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game web untuk siswa kelas 5 SD/MI yang sekaligus menjadi alat penelitian: **model asesmen literasi digital** dengan konteks budaya Kedu.',
                    'table' => [['Hal', 'Isinya'], [
                        ['Judul penelitian', 'Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar'],
                        ['Yang diukur', '[[Literasi digital|literasi-digital]] dalam tiga dimensi: [[D1|d1]] literasi informasi, [[D2|d2]] aman dan etis, [[D3|d3]] partisipasi dan representasi budaya Kedu di ruang digital'],
                        ['Permainan', '[[Jaka, dibimbing Mbah Kedu|tokoh]], memulihkan 15 [[Serpihan Cahaya|serpihan]] di Temanggung (mudah), Magelang (sedang), dan Wonosobo (sulit); lima tantangan per wilayah'],
                        ['Model penelitian', '[[ADDIE|addie]] (Branch, 2009), dengan [[ECD|ecd]] untuk merancang asesmen dan empat kriteria mutu'],
                        ['Data', 'Catatan setiap percobaan di game, [[data turunan|data-turunan]] yang dihitung sistem, dan [[lembar lapangan L0–L8|lembar-lapangan]] dari peneliti; analisis statistik ([[model Rasch|rasch]]) dikerjakan di luar sistem'],
                        ['Peserta rencana', 'Target 300 siswa yang datanya bisa dianalisis (sekitar 360 direkrut), [[lima pertemuan|pertemuan]] per kelas, setelah uji coba bertahap di sekolah uji coba'],
                        ['Cara kerja', 'Game dibuka di peramban Chromebook atau laptop sekolah. Benar atau salah, bintang, dan kunci jawaban diurus server GELITA, bukan perangkat siswa'],
                    ]],
                    'why' => [
                        'Game dipilih karena merekam **tindakan** anak, percobaan demi percobaan, bukan penilaian diri seperti angket. Dari situ terlihat mengapa anak keliru, bukan hanya skornya.',
                        'Budaya Kedu menjadi konteks agar anak belajar mengenali representasi budaya daerahnya sendiri di ruang digital (etnopedagogi).',
                    ],
                    'refs'    => [self::BRANCH, self::MISLEVY, self::ALWASILAH],
                    'sources' => [self::R01 . 'judul-penelitian-dan-konstruk', self::R22 . 'latar-belakang-dan-nilai-sosial'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function origin(): array
    {
        return [
            'id'       => 'asal',
            'title'    => 'Asal-usul dan tujuan',
            'lead'     => 'Masalah yang dijawab, perjalanan GELITA dari game literasi membaca ke model asesmen, dan tujuan penelitiannya.',
            'sections' => [
                [
                    'id'    => 'masalah',
                    'title' => 'Masalah yang dijawab',
                    'lead'  => 'Anak SD di Indonesia makin awal memakai HP dan internet, tetapi alat ukur literasi digital untuk usia ini masih sedikit.',
                    'list'  => [
                        'Alat yang ada umumnya **angket penilaian diri**. Angket tidak memperlihatkan apa yang benar-benar dilakukan anak saat bertemu informasi, permintaan data pribadi, atau unggahan tentang budaya.',
                        'GELITA merekam tindakan anak: [[jawaban pertama|percobaan-pertama]], [[kesalahan berkode|kode-kesalahan]], alasan, [[bantuan|bantuan]] yang dibuka, dan waktu. Dari situ peneliti melihat **mengapa** anak keliru.',
                        'Kerangkanya empat pilar literasi digital Komdigi dan Siberkreasi, dengan DigComp 3.0 sebagai padanan internasional; level membaca mengikuti [[AKM|level-akm]] dan [[PIRLS 2026|pirls]].',
                        'Hasil yang diharapkan: alat asesmen yang divalidasi, rekomendasi pengembangan asesmen berbasis game untuk anak, dan gambaran kesalahan literasi digital yang sering terjadi pada siswa kelas 5. Penelitian ini **tidak menilai** siswa, guru, atau sekolah.',
                    ],
                    'why' => [
                        'Asesmen yang dijalin ke dalam permainan mengumpulkan data kinerja terus-menerus selama bermain, tanpa terasa sebagai ujian (stealth assessment).',
                        'Analitik pembelajaran untuk game memberi cara menelusuri jejak percobaan menjadi informasi tentang proses berpikir.',
                    ],
                    'refs'    => [self::SHUTE11, self::FREIRE, self::KOMINFO, self::DIGCOMP],
                    'sources' => [self::R22 . 'latar-belakang-dan-nilai-sosial'],
                ],
                [
                    'id'    => 'perjalanan',
                    'title' => 'Perjalanan GELITA',
                    'lead'  => 'GELITA berawal sebagai game literasi membaca berbasis budaya Kedu. Dua perombakan besar menjadikannya model asesmen literasi digital.',
                    'table' => [['Langkah', 'Yang terjadi', 'Mengapa'], [
                        ['1. Titik awal', 'Game literasi membaca dengan cerita Jaka dan Mbah Kedu, [[Pustaka Kedu|pustaka-kedu]], dan beberapa jenis kuis', 'Membaca dalam konteks budaya Kedu'],
                        ['2. Perombakan kelas 5', '[Perombakan kelas 5](#dok-1-3): fokus kelas 5, dimulai bersih, empat tantangan, aturan tiga kesempatan, [[layar 16:9|panggung]], data mentah', 'Peneliti meminta game yang fokus dan data yang bersih sebelum penelitian dimulai'],
                        ['3. Revisi 10 dimulai', 'Judul penelitian ditetapkan, literasi digital menjadi hal utama yang diukur, [[Skenario Digital|skenario-digital]] menjadi tantangan kelima', 'Judul penelitian menyebut GELITA sebagai model asesmen literasi digital'],
                        ['4. Desain instrumen', 'Kisi-kisi, draf bank soal, [[kamus data|kamus-data]], instrumen validasi, izin etik, dan pelindungan data disusun', 'Desain instrumen harus selesai sebelum validasi ([[gerbang G1|gerbang]])'],
                        ['5. Model ADDIE', 'Model penelitian diganti [[ADDIE|addie]]; semua kebutuhan penelitian disiapkan di sistem, tetapi baru menyala lewat [[mode penelitian|mode-penelitian]]', 'Peneliti ingin setiap fase diuji dan direvisi sampai final; sistem perlu siap saat izin etik terbit'],
                        ['6. Gerbang G2', 'Validasi ahli selesai dan izin etik disetujui', 'Kegiatan dengan anak yang tercakup protokol boleh dimulai sesuai jadwal peneliti'],
                        ['7. Sistem penelitian lengkap', 'Peran staf, [[sesi lapangan|sesi-lapangan]], data, persetujuan, bank soal baru, Skenario Digital, mode penelitian, modul tes, dan data turunan selesai dibangun', '[[Prototipe II|prototipe]] harus memuat semua kebutuhan penelitian sebelum gerbang G3'],
                    ]],
                    'why' => [
                        'Setiap perubahan besar dicatat dengan asal dan alasannya, supaya penguji dan peneliti lain bisa menelusuri mengapa produk menjadi seperti sekarang (refleksi dan dokumentasi sistematis, Plomp, 2013, hlm. 19).',
                    ],
                    'refs'    => [self::PLOMP],
                    'sources' => [self::R04 . '23-oktober-2026', self::R04 . '4-november-2026'],
                ],
                [
                    'id'    => 'tujuan',
                    'title' => 'Tujuan penelitian',
                    'lead'  => 'Lima tujuan dari protokol izin etik.',
                    'steps' => [
                        'Mengembangkan GELITA sebagai model asesmen literasi digital tiga dimensi untuk siswa kelas 5 SD, dengan konteks budaya Kedu.',
                        'Menguji validitas isi bank soal, tes, angket, dan skala sikap lewat validasi ahli dan uji keterbacaan.',
                        'Menguji kepraktisan dan keterbacaan GELITA di kelas lewat berpikir lantang, uji kelompok kecil, dan uji kelompok menengah, dan merevisinya setelah setiap uji.',
                        'Menguji bukti validitas dan reliabilitas skor GELITA pada data utama: urutan kesulitan wilayah, keterpisahan tiga dimensi, kecocokan butir, dan hubungan skor game dengan tes awal–akhir.',
                        'Menggambarkan pola kesalahan, penggunaan bantuan, dan alasan anak di [[Skenario Digital|skenario-digital]].',
                    ],
                    'why' => [
                        'Klaim utamanya soal **mutu pengukuran**, bukan peningkatan hasil belajar. Tidak ada kelompok kontrol, jadi penelitian ini tidak mengklaim game meningkatkan literasi.',
                        'Bukti validitas disusun mengikuti lima sumber bukti Standards: isi, proses respons, struktur internal, hubungan dengan variabel lain, dan akibat pemakaian.',
                    ],
                    'refs'    => [self::AERA, self::MESSICK, self::KANE],
                    'sources' => [self::R22 . 'tujuan', self::R01 . 'rencana-analisis-singkat'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function model(): array
    {
        return [
            'id'       => 'model',
            'title'    => 'Model penelitian',
            'lead'     => 'ADDIE sebagai model pengembangan, ECD untuk merancang asesmen, empat kriteria mutu, dan pembagian peran pengembang, peneliti, dan ahli.',
            'sections' => [
                [
                    'id'    => 'addie',
                    'title' => 'ADDIE dan alasannya',
                    'lead'  => 'GELITA memakai model [[ADDIE|addie]] (Branch, 2009), keputusan peneliti. Sebelumnya pengembang memilih model Plomp (2013).',
                    'list'  => [
                        'Branch menyebut ADDIE "a product development paradigm and not a model per se", berulang dan tidak harus lurus, dengan uji rintis di fase Develop.',
                        'Metode di dalam fase tidak berubah: [[ECD|ecd]] untuk merancang asesmen, lapis evaluasi formatif Tessmer (telaah ahli, satu-satu, kelompok kecil, uji lapangan) dan tabel metode Nieveen & Folmer untuk menguji prototipe.',
                        'Uji kelompok kecil dan menengah ditaruh di fase Develop; uji besar (pengambilan data utama) di fase Implement pada versi yang dikunci di [[gerbang G4|gerbang]].',
                        'Keluaran di luar alat ukur ditulis sebagai **rekomendasi pengembangan** asesmen berbasis game untuk anak.',
                    ],
                    'why' => [
                        'Peneliti memilih ADDIE karena setiap fase diuji, dan perubahan dari saran ahli atau hasil uji masuk kembali ke fase pengembangan sampai produknya benar-benar final.',
                        'Kekurangan ADDIE yang tetap benar, tidak ada kriteria mutu khusus untuk alat ukur, ditutup dengan empat kriteria mutu Plomp dan ECD.',
                        'Kritik bahwa fase Implement mencampur uji coba dengan pengambilan data utama diatasi dengan memisahkan keduanya: uji coba di Develop, data utama di Implement.',
                        'Model lain ditimbang dan tidak dipilih: 4D (difusi dan adopsi di luar rencana), Borg & Gall (uji lapangan berulang yang tidak dipakai), Sugiyono (berakhir produksi massal), MDLC dan GDLC (siklus pembuatan perangkat lunak, bukan desain penelitian).',
                    ],
                    'refs'    => [self::BRANCH, self::PLOMP, self::TESSMER, self::NIEVEEN],
                    'sources' => [self::MODEL],
                ],
                [
                    'id'    => 'tahap-gerbang',
                    'title' => 'Enam tahap, lima gerbang, tiga prototipe',
                    'lead'  => 'Pekerjaan berjalan dalam enam tahap. Tahap berikutnya baru dimulai setelah [[gerbang]] terpenuhi.',
                    'table' => [['Fase ADDIE', 'Tahap GELITA', 'Prototipe', 'Metode evaluasi'], [
                        ['Analyze', 'Sebelum tahap 1', '—', 'Kajian pustaka, telaah butir dan media lama, profil sekolah ([[L8|l8]])'],
                        ['Design', '1, gerbang G1', '[[Prototipe I|prototipe]] (dokumen, contoh tampilan)', 'Telaah pendahuluan oleh pengembang'],
                        ['Develop', '2, gerbang G2', 'Prototipe I, direvisi', 'Validasi sebelas ahli; uji keterbacaan dengan 4 + 1 anak'],
                        ['Develop', '3, gerbang G3', 'Prototipe II (versi uji)', 'Ahli media menilai layar jadi, uji perangkat sekolah, pemeriksaan otomatis aturan game'],
                        ['Develop', '4, gerbang G4', 'Prototipe III', 'Berpikir lantang (5–8 anak), uji kelompok kecil (8–12), uji kelompok menengah (± 80); versi dikunci dan [[pra-registrasi OSF|pra-registrasi]]'],
                        ['Implement', '5, gerbang G5', 'Versi final', 'Pengambilan data utama: target 300 siswa'],
                        ['Evaluate', 'Setiap fase; 6', '—', 'Analisis [[Rasch|rasch]], [[DIF|dif]], pola kesalahan, validitas kriteria'],
                    ]],
                    'why' => [
                        'Gerbang mencegah pekerjaan mahal dimulai di atas dasar yang belum disetujui: kegiatan dengan anak menunggu izin etik (G2), uji coba menunggu sistem jadi (G3), data utama menunggu versi yang dikunci (G4).',
                        'Nama [[prototipe]] I–III dipakai supaya setiap revisi tercatat pada versi yang jelas.',
                        'Pra-registrasi skema kesalahan dan rencana analisis di OSF sebelum data utama mencegah analisis disesuaikan dengan hasil.',
                        'Uji coba dilakukan di sekolah uji coba di wilayah Kedu, di luar sekolah utama, dan satu anak hanya ikut satu uji, supaya data uji tidak tercampur dengan data utama.',
                    ],
                    'refs'    => [self::TESSMER, self::NIEVEEN, self::NOSEK],
                    'sources' => [self::R22 . 'desain-penelitian', self::MODEL],
                ],
                [
                    'id'    => 'kriteria-mutu',
                    'title' => 'Empat kriteria mutu',
                    'lead'  => 'Mutu produk dinilai dengan relevansi, konsistensi, kepraktisan, dan keefektifan (Plomp, 2013, hlm. 29), tidak bergantung pada model pengembangannya.',
                    'table' => [['Kriteria', 'Artinya untuk GELITA', 'Bukti yang direncanakan'], [
                        ['Relevansi', 'Butir mewakili tiga dimensi literasi digital dan konteks budaya Kedu', '[[I-CVI, S-CVI/Ave|icvi]], [[V Aiken|v-aiken]] dari ahli; kisi-kisi'],
                        ['Konsistensi', 'Rantai klaim–bukti–tugas ECD utuh; label disepakati ahli', 'Peta bukti, [[kamus data|kamus-data]], [[κ berbobot|kappa]] [[pelabelan buta|pelabelan-buta]] ≥ 0,70'],
                        ['Kepraktisan', 'Game berjalan di perangkat sekolah, sesi muat dalam waktu, siswa bermain tanpa dibantu isi soal', 'Uji perangkat, ahli media, catatan penyelesaian dan [[waktu aktif|waktu-aktif]], lembar [[L1|l1]], [[L3|l3]], [[L4|l4]], bintang kritik dan saran'],
                        ['Keefektifan', 'Skor game mengukur dengan baik', 'Kecocokan [[Rasch|rasch]] ([[MNSQ|mnsq]] 0,5–1,5), reliabilitas, peta Wright, [[DIF|dif]], korelasi dengan tes awal–akhir'],
                    ]],
                    'why' => [
                        'Keefektifan diartikan sebagai **mutu pengukuran**, bukan kenaikan hasil belajar, karena GELITA adalah alat ukur.',
                        'Uji beda tes awal–akhir dilaporkan sebagai temuan **eksploratif**: desain satu kelompok tanpa kontrol tidak bisa menunjukkan bahwa game menyebabkan perubahan (sejarah, kematangan, efek tes, regresi).',
                        'Karena guru tidak ikut penelitian, kepraktisan diambil dari catatan game, lembar pengamatan, dan pendapat siswa.',
                    ],
                    'refs'    => [self::PLOMP, self::LINACRE, self::CAMPBELL],
                    'sources' => [self::MODEL],
                ],
                [
                    'id'    => 'ecd',
                    'title' => 'Evidence-Centered Design',
                    'lead'  => 'Asesmen GELITA dirancang dengan [[ecd]]: klaim tentang literasi digital siswa → bukti yang mendukungnya → tugas yang menghasilkan bukti itu. Setiap analitik harus bisa ditelusuri ke minimal satu dimensi.',
                    'table' => [['Unsur ECD', 'Padanan di GELITA'], [
                        ['Model siswa (apa yang ingin diketahui)', 'Literasi digital tiga dimensi: [[D1|d1]], [[D2|d2]], [[D3|d3]]'],
                        ['Model bukti (bagaimana jawaban menjadi skor)', 'Sepuluh analitik dan [[kamus data|kamus-data]]; [[model Rasch|rasch]] tiga dimensi, setiap butir masuk tepat satu dimensi'],
                        ['Model tugas (apa yang dikerjakan siswa)', 'Lima jenis tantangan: [[Susun Gambar|susun-gambar]], [[Isi Rumpang|isi-rumpang]], [[Benar atau Salah|benar-salah]], [[Cari Objek|cari-objek]], [[Skenario Digital|skenario-digital]]'],
                        ['Model perakitan (soal mana untuk siapa)', '[[Variasi a/b|variasi]], [[buklet]], [[butir jangkar|butir-jangkar]], [[lima pertemuan|pertemuan]]'],
                        ['Model penyampaian (bagaimana soal sampai ke siswa)', '[[Layar game|panggung]], [[token sesi|token-sesi]], [[mode penelitian|mode-penelitian]]'],
                        ['Empat proses (siapa yang menilai)', 'Benar atau salah dan bintang dihitung server GELITA; kunci jawaban tidak pernah dikirim ke perangkat siswa'],
                    ]],
                    'why' => [
                        'ECD bukan model penelitian dan pengembangan, jadi ia butuh wadah; ADDIE memberi wadah itu tanpa bertabrakan.',
                        'Data yang tidak tersambung ke dimensi mana pun tidak dikumpulkan, sejalan dengan prinsip mengumpulkan data anak seperlunya saja.',
                        'Pemakaian ECD di dalam game punya preseden: stealth assessment menjadikan ECD salah satu unsur kuncinya.',
                        'Asesmen hanya sahih bila jawaban tidak bisa diintip. Karena itu penilaian dipisah dari tampilan: perangkat siswa hanya menampilkan soal, sedangkan kunci dan penilaiannya ada di server.',
                    ],
                    'refs'    => [self::MISLEVY, self::ALMOND, self::HAERTEL, self::SHUTE11],
                    'sources' => [self::R01 . 'kerangka-peta-bukti', self::MODEL],
                ],
                [
                    'id'    => 'peran-jalur',
                    'title' => 'Pengembang, peneliti, dan ahli',
                    'lead'  => 'Revisi dikerjakan dalam dua jalur. Setiap isi, keputusan, dan perubahan menyebut asalnya.',
                    'table' => [['Jalur', 'Yang dikerjakan', 'Kedudukannya'], [
                        ['Pengembang', 'Draf, label, skor, telaah, cek fakta, usulan revisi, berkas siap kirim', 'Sementara, ditandai sebagai kerja pengembang'],
                        ['Peneliti', 'Menerima, menindaklanjuti, menyerahkan ke ahli dan komite, lalu menetapkan', 'Sah setelah disetujui peneliti'],
                        ['Peneliti bersama ahli dan komite', 'Validasi ahli, izin etik, izin sekolah', 'Final'],
                    ]],
                    'list' => [
                        'Setiap isi diberi label asal: pengembang, peneliti, peneliti bersama ahli, atau komite etik.',
                        'Status dokumen: draf pengembang → disetujui pengembang → disetujui peneliti → tervalidasi ahli → final.',
                    ],
                    'why' => [
                        'Kerja pengembang **bukan** validasi ahli: skor dan label pengembang tidak ikut dihitung dalam [[V Aiken|v-aiken]], [[I-CVI|icvi]], atau [[κ|kappa]], supaya ahli menilai tanpa terpengaruh.',
                        '"Dibekukan" berarti versinya dicatat, bukan tidak boleh diubah. Perubahan sesudahnya tetap boleh asal dicatat dengan akibatnya: dinilai ulang ahli, diajukan kembali ke komite etik, atau tidak perlu apa-apa.',
                        'Kerja pengembang yang lengkap mempercepat peneliti: ia menyetujui, menolak, atau merevisi bahan yang sudah siap, dan tetap bertanggung jawab atas setiap keputusan final.',
                    ],
                    'refs'    => [self::AIKEN, self::COHEN68],
                    'sources' => [self::R01 . 'peran-pengembang-peneliti-dan-ahli'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function construct(): array
    {
        return [
            'id'       => 'konstruk',
            'title'    => 'Apa yang diukur',
            'lead'     => 'Tiga dimensi literasi digital, sepuluh analitik, skema kode kesalahan, pelevelan wilayah, dan dua skala pendamping.',
            'sections' => [
                [
                    'id'    => 'tiga-dimensi',
                    'title' => 'Tiga dimensi literasi digital',
                    'lead'  => 'Yang diukur adalah [[literasi digital|literasi-digital]] siswa; Kedu adalah konteks budayanya. Literasi membaca dan pengetahuan budaya tidak lagi berdiri sebagai hal yang diukur tersendiri.',
                    'table' => [['Dimensi', 'Artinya', 'Diukur lewat', 'Acuan tingkat kesulitan'], [
                        ['[[D1 Literasi informasi|d1]]', 'Menemukan, menafsirkan, dan menilai informasi dari teks dan gambar di layar, termasuk [[teks bergenre digital|teks-digital]] (D1a memahami, D1b menilai informasi dan sumber)', '[[Isi Rumpang|isi-rumpang]], [[Benar atau Salah|benar-salah]], [[kartu jangkar|kartu-jangkar]]', '[[Level AKM|level-akm]], [[padanan PIRLS 2026|pirls]]'],
                        ['[[D2 Aman dan etis|d2]]', 'Menjaga data pribadi, kata sandi, dan OTP; izin berbagi foto dan obrolan; menghadapi orang asing dan kabar bohong', '[[Skenario Digital|skenario-digital]] ([[tindakan diskor 0–1–2|skor-tindakan]]); [[metrik kata sandi saat daftar|metrik-sandi]]', '[[Kerumitan skenario per wilayah|kerumitan-skenario]]'],
                        ['[[D3 Partisipasi dan representasi budaya Kedu di ruang digital|d3]]', 'Menyebut pembuat dan sumber, keterangan yang akurat, izin pelaku dan pemilik karya, sikap menghargai tradisi', '[[Cari Objek|cari-objek]] babak 2 [[Periksa Unggahan|periksa-unggahan]]', '[[Kerumitan adegan per wilayah|kerumitan-adegan]]'],
                    ]],
                    'why' => [
                        'Judul penelitian menyebut GELITA "model asesmen literasi digital", jadi literasi digital menjadi hal utama yang diukur dan yang lain menjadi dimensinya.',
                        'Empat pilar Komdigi dan Siberkreasi dipakai sebagai payung karena dikenal di Indonesia; DigComp 3.0 memberi padanan internasional.',
                        'Setiap butir punya tepat satu dimensi utama, supaya keterpisahan tiga dimensi bisa diuji dengan [[model Rasch|rasch]].',
                    ],
                    'refs'    => [self::KOMINFO, self::DIGCOMP, self::PIRLS, self::ADAMS],
                    'sources' => [self::R01 . 'judul-penelitian-dan-konstruk'],
                ],
                [
                    'id'    => 'sepuluh-analitik',
                    'title' => 'Sepuluh analitik',
                    'lead'  => 'Sepuluh analitik berasal dari peneliti. Revisi 10 memberi setiap analitik definisi yang bisa dihitung dari catatan game atau lembar lapangan.',
                    'table' => [['No', 'Analitik', 'Dihitung dari'], [
                        ['1', 'Penyelesaian aktivitas, dengan tiga jenis [["dilewati"|dilewati]]', '[[Log game|log-game]], [[L1|l1]]'],
                        ['2', 'Ketepatan jawaban per dimensi dan [[level AKM|level-akm]]', '[[Log game|log-game]]'],
                        ['3', 'Ketepatan [[percobaan pertama|percobaan-pertama]], data inti uji [[Rasch|rasch]]', '[[Log game|log-game]]'],
                        ['4', 'Jumlah percobaan; "belum berhasil setelah 3" diperlakukan sebagai [[tersensor]]', '[[Log game|log-game]]'],
                        ['5', '[[Waktu aktif|waktu-aktif]]: waktu total dikurangi layar tersembunyi dan jeda panjang', '[[Log game|log-game]], [[L3|l3]]'],
                        ['6', 'Pola kesalahan dari [[kode kesalahan|kode-kesalahan]]', '[[Log game|log-game]], [[pengodean manual|pengodean-manual]]'],
                        ['7', 'Penggunaan bantuan: [[Bantuan|bantuan]], [[narasi]], instruksi; bantuan orang lewat L3', '[[Log game|log-game]], [[L1|l1]], [[L3|l3]]'],
                        ['8', 'Navigasi: perpindahan layar, [[Pustaka|pustaka-kedu]], kembali ke bacaan', '[[Log game|log-game]]'],
                        ['9', 'Perubahan setelah umpan balik: diperbaiki, kesalahan sama, kesalahan lain', '[[Log game|log-game]]'],
                        ['10', 'Pilihan dan alasan di [[Skenario Digital|skenario-digital]]', '[[Log game|log-game]], [[L5|l5]], [[metrik kata sandi|metrik-sandi]]'],
                    ]],
                    'why' => [
                        'Kolom asal "Informasi bagi guru" diganti "Makna bagi penelitian", karena guru tidak ikut dalam penelitian.',
                        'Butir yang belum berhasil setelah tiga kesempatan bukan angka 3 melainkan data [[tersensor]]; model kejadian waktu-diskret menangani data seperti itu dengan benar.',
                        '[[Tebakan cepat|tebakan-cepat]] ditandai dari waktu respons, karena jawaban yang terlalu cepat tidak mencerminkan kemampuan.',
                    ],
                    'refs'    => [self::SINGER, self::WISE, self::FREIRE],
                    'sources' => [self::R01 . '10-analitik-versi-revisi', self::R04 . 'titik-awal-dokumen-10-analitik-dari-pengembang'],
                ],
                [
                    'id'    => 'kode-kesalahan',
                    'title' => 'Skema kode kesalahan',
                    'lead'  => 'Setiap pilihan salah diberi satu [[kode kesalahan|kode-kesalahan]] **sebelum** data diambil, dan kode itu ikut tercatat di setiap percobaan yang salah.',
                    'table' => [['Kelompok', 'Dimensi', 'Contoh'], [
                        ['[[INF: salah memilih informasi|kode-inf]]', '[[D1|d1]] ([[INF-BUD|inf-bud]] hanya untuk skala [[PBK|pbk]])', '[[INF-LIT|inf-lit]] salah membaca informasi tersurat; [[INF-SUM|inf-sum]] salah menilai sumber; [[INF-FOP|inf-fop]] mencampur fakta dan pendapat'],
                        ['[[DIG: salah memilih tindakan digital|kode-dig]]', '[[D2|d2]]', '[[DIG-PRI|dig-pri]] membagikan data pribadi berlebihan; [[DIG-ASG|dig-asg]] menanggapi orang asing'],
                        ['[[BUD: salah dalam representasi budaya|kode-bud]]', '[[D3|d3]]', '[[BUD-ATR|bud-atr]] sumber dan pembuat, [[BUD-REP|bud-rep]] keterangan keliru, [[BUD-IZN|bud-izn]] izin, [[BUD-ETK|bud-etk]] sikap terhadap tradisi'],
                        ['[[NON: penanda tambahan|kode-non]]', 'Semua', '[[Tebakan cepat|tebakan-cepat]], jawaban terakhir saat kesempatan habis; dihitung sistem'],
                        ['[[R: alasan di Skenario Digital|kode-r]]', '[[D2|d2]] (diagnosis)', '[[R-RISK|r-risk]] paham risiko, [[R-ETIK|r-etik]] empati atau izin, [[R-ATUR|r-atur]] karena aturan, [[R-SOS|r-sos]] tekanan teman, [[R-ACAK|r-acak]] tidak nyambung'],
                    ]],
                    'why' => [
                        'Kode dipasang sebelum data diambil dan dibekukan setelah uji coba, supaya kategori tidak disesuaikan dengan hasil; skemanya ikut [[pra-registrasi]] di [[G4|gerbang]].',
                        'Kode diberikan sistem langsung dari pilihan siswa, sehingga tidak ada pengodean ulang yang bisa berbeda antarpeneliti; [[pengodean manual|pengodean-manual]] hanya untuk kode "LAIN" dan disimpan terpisah.',
                        'Ruang hasil (outcome space) berkode adalah salah satu blok bangunan pengukuran: dari kode itulah pola kesalahan dan [[skor 0–1–2|skor-tindakan]] disusun.',
                    ],
                    'refs'    => [self::WILSON, self::NOSEK],
                    'sources' => [self::R01 . 'skema-kategori-kesalahan'],
                ],
                [
                    'id'    => 'pelevelan',
                    'title' => 'Pelevelan wilayah',
                    'lead'  => 'Urutan Temanggung (mudah), Magelang (sedang), Wonosobo (sulit) tetap. Bedanya, tingkat kesulitan sekarang harus dibuktikan dengan data, bukan hanya label Bloom.',
                    'table' => [['Wilayah', '[[Level AKM|level-akm]] ([[D1|d1]])', '[[Padanan PIRLS 2026|pirls]]', '[[Bloom|bloom]] (label kedua)', '[[Skenario Digital|kerumitan-skenario]] ([[D2|d2]])'], [
                        ['Temanggung', 'Menemukan informasi', 'Mengambil informasi tersurat', '[[C1–C3|bloom]]', 'Tanda bahaya jelas, 3 pilihan'],
                        ['Magelang', 'Interpretasi dan integrasi', 'Inferensi langsung; menafsirkan dan memadukan', '[[C2–C4|bloom]]', 'Tanda bahaya samar, 4 pilihan'],
                        ['Wonosobo', 'Evaluasi dan refleksi', 'Mengevaluasi dan mengkritisi isi teks', '[[C2–C5|bloom]]', 'Nilai bertentangan, dua langkah'],
                    ]],
                    'list' => [
                        'Dua ahli memberi label level AKM pada butir secara terpisah ([[pelabelan buta|pelabelan-buta]]), dengan kesepakatan [[κ berbobot|kappa]] ≥ 0,70.',
                        '[[Butir jangkar|butir-jangkar]] menyambungkan [[variasi a dan b|variasi]] di satu skala.',
                        '[[Beban permukaan|beban-permukaan]] (jumlah rumpang, kata pengecoh, panjang bacaan, benda pengecoh) dicatat sebagai variabel sendiri.',
                        'Setelah data terkumpul, [[model Rasch|rasch]] memeriksa apakah urutan kesulitan butir mengikuti wilayah dan level.',
                    ],
                    'why' => [
                        'AKM adalah kerangka nasional literasi membaca, jadi hasilnya mudah dibaca pemangku kepentingan di Indonesia; PIRLS 2026 memberi padanan internasional untuk jurnal.',
                        'Bloom tetap ditulis sebagai label kedua; DOK Webb dan SOLO menjadi pembanding tingkat berpikir.',
                        'Urutan wilayah tidak bisa diacak karena mengikuti cerita. Ini ditulis sebagai keterbatasan, dengan [[waktu aktif|waktu-aktif]] kumulatif dan nomor pertemuan ikut diperhitungkan dalam analisis.',
                    ],
                    'refs'    => [self::PIRLS, self::BLOOM, self::WEBB, self::COHEN68, self::BOND],
                    'sources' => [self::R01 . 'pelevelan-wilayah'],
                ],
                [
                    'id'    => 'skala-pendamping',
                    'title' => 'Dua skala pendamping',
                    'lead'  => 'Dua hal diukur di samping literasi digital, tetapi **di luar** skornya.',
                    'table' => [['Skala', 'Isinya', 'Diukur lewat'], [
                        ['[[Pengetahuan Budaya Kedu (PBK)|pbk]]', 'Mengenali benda dan tradisi budaya Kedu', '[[Cari Objek|cari-objek]] babak 1 [[Temukan Benda|temukan-benda]] ([[butir bersama x|butir-bersama]])'],
                        ['[[Skala sikap menjaga warisan|skala-sikap]]', 'Menghargai, ingin tahu, mau merawat, dan bangga pada warisan budaya Kedu ([[SK1–SK4|aspek-sikap]])', '12 pernyataan dengan empat pilihan tanpa titik tengah, sekali di [[Balai Refleksi|balai-refleksi]] pada pertemuan 5'],
                    ]],
                    'why' => [
                        'Pengetahuan tentang benda budaya bukan literasi digital; memasukkannya ke skor akan mencampur hal yang diukur. Karena itu PBK berdiri sendiri.',
                        'Sikap bukan pengetahuan dan bukan perilaku di ruang digital, jadi diukur dengan skala tersendiri, hanya di layar dan tidak dibacakan.',
                        'Skala sikap divalidasi dengan [[I-CVI|icvi]] aturan Lynn oleh empat ahli dan uji keterbacaan dengan anak; statusnya sudah tervalidasi ahli.',
                    ],
                    'refs'    => [self::LYNN, self::GAY],
                    'sources' => ['revisi-10-analitik/17_SKALA_SIKAP.md#ringkasan'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function game(): array
    {
        return [
            'id'       => 'permainan',
            'title'    => 'Desain permainan',
            'lead'     => 'Cerita dan wilayah, lima tantangan, tiga kesempatan, hadiah, bantuan, Pustaka, bahasa, buklet, dan Skenario Digital.',
            'sections' => [
                [
                    'id'    => 'cerita-wilayah',
                    'title' => 'Cerita dan tiga wilayah',
                    'lead'  => 'Pemain berperan sebagai [[Jaka, dibimbing Mbah Kedu|tokoh]], memulihkan 15 [[Serpihan Cahaya|serpihan]] di tiga wilayah eks-Karesidenan Kedu.',
                    'steps' => [
                        'Halaman awal → pendaftaran → cerita pembuka → Peta Kedu.',
                        'Per wilayah: Kenali wilayah → dialog pembuka → lima tantangan berurutan → wilayah tuntas → [[Pustaka|pustaka-kedu]] wilayah terbuka.',
                        'Setelah ketiga wilayah: cerita penutup → [[Balai Refleksi|balai-refleksi]] (skala sikap, kritik dan saran).',
                        'Sejak revisi 10, cerita penghubung membuka tantangan kelima ([[Skenario Digital|skenario-digital]]) di setiap wilayah.',
                        'Gambar latar, tokoh, dan narasi cerita dapat dilihat di [Katalog Aset GELITA](' . self::CATALOGUE . '#narasi).',
                    ],
                    'why' => [
                        'Cerita memberi fantasi dan rasa ingin tahu, dua sumber motivasi intrinsik; tantangan yang naik bertahap menjaga anak di wilayah "flow".',
                        'Cerita tidak boleh membocorkan jawaban. Naskah lama diperiksa terhadap bank soal, dan cerita penghubung ditulis tanpa petunjuk isi.',
                        'Wilayah terbuka berurutan karena cerita mengikuti perjalanan Jaka; dalam [[mode penelitian|mode-penelitian]] [[token sesi|token-sesi]] mengatur wilayah per pertemuan.',
                    ],
                    'refs'    => [self::MALONE, self::CSIKS, self::GEE],
                    'sources' => ['revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md#aturan-menulis-cerita-untuk-penelitian'],
                ],
                [
                    'id'    => 'lima-tantangan',
                    'title' => 'Lima tantangan per wilayah',
                    'lead'  => 'Urutannya sama di ketiga wilayah; jumlahnya naik per tingkat kesulitan. Contoh gambar setiap tantangan ada di [Katalog Aset GELITA](' . self::CATALOGUE . '#gambar).',
                    'table' => [['No', 'Tantangan', 'Dimensi', 'Temanggung · Magelang · Wonosobo'], [
                        ['1', '[[Susun Gambar|susun-gambar]]', 'Tidak dinilai (pemanasan)', '3 × 3 · 4 × 4 · jigsaw 4 × 4'],
                        ['2', '[[Isi Rumpang|isi-rumpang]]', '[[D1|d1]]', '4 · 5 · 6 rumpang, masing-masing + 2 pengecoh'],
                        ['3', '[[Benar atau Salah|benar-salah]]', '[[D1|d1]] (+ [[kartu jangkar|kartu-jangkar]])', '4 · 6 · 6 pernyataan + 2 jangkar'],
                        ['4', '[[Cari Objek|cari-objek]]', 'Babak 1 [[PBK|pbk]], babak 2 [[D3|d3]]', 'Babak 1 [[Temukan Benda|temukan-benda]]: 4 dicari + 4/6/8 pengecoh; babak 2 [[Periksa Unggahan|periksa-unggahan]]: 4 petunjuk, 8/10/12 bagian yang bisa diketuk'],
                        ['5', '[[Skenario Digital|skenario-digital]]', '[[D2|d2]]', '4 skenario; 3/4/4 tindakan; 3/4/5 alasan'],
                    ]],
                    'why' => [
                        'Setiap tantangan adalah satu "model tugas" [[ECD|ecd]] yang menghasilkan bukti untuk satu dimensi.',
                        'Susun Gambar tidak dinilai karena tidak mengukur literasi digital; ia menjadi pemanasan sekaligus pengenalan wilayah.',
                        'Area ketuk Cari Objek tidak boleh bertumpuk dan diberi jarak kira-kira selebar ujung jari, dan diperiksa otomatis, supaya ketepatan jari tidak ikut terukur.',
                        'Kuis pilihan, rumpang ketik sendiri, dan benar–salah beralasan dihapus sejak perombakan kelas 5 atas permintaan peneliti.',
                    ],
                    'refs'    => [self::MISLEVY],
                    'sources' => ['revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md#kisi-kisi-per-wilayah-dan-tantangan'],
                ],
                [
                    'id'    => 'tiga-kesempatan',
                    'title' => 'Tiga kesempatan, percobaan pertama yang dinilai',
                    'lead'  => 'Setiap soal paling banyak tiga percobaan. Batas itu, benar atau salahnya jawaban, dan kunci jawaban diurus server GELITA, bukan perangkat siswa.',
                    'list'  => [
                        '[[Susun Gambar|susun-gambar]], [[Isi Rumpang|isi-rumpang]], [[Benar atau Salah|benar-salah]]: satu tekan **Cek jawabanku** = satu percobaan untuk semua soal yang belum benar.',
                        '[[Cari Objek|cari-objek]]: satu ketukan = satu percobaan untuk petunjuk yang aktif; ketukan salah ke-3 menutup petunjuk itu.',
                        '[[Skenario Digital|skenario-digital]]: tiga kesempatan per skenario; setiap kesempatan berisi tindakan dan alasan; setelah kesempatan ketiga tindakan yang aman ditampilkan dan dijelaskan.',
                        'Bila kesempatan habis, jawaban terakhir disimpan apa adanya dan siswa tetap mendapat [[serpihan]].',
                        'Kunci jawaban tidak pernah dikirim ke perangkat siswa: soal yang tampil tidak memuat kunci, dan hasil "benar" atau "belum tepat" selalu datang dari server.',
                    ],
                    'why' => [
                        'Yang dinilai adalah [[percobaan pertama|percobaan-pertama]], karena hanya itu yang belum dipengaruhi umpan balik. Kesempatan berikutnya untuk belajar (analitik 9).',
                        'Umpan balik setelah percobaan salah adalah umpan balik formatif: memberi tahu apa yang perlu diperbaiki tanpa memberi jawabannya.',
                        'Batas tiga menjaga sesi tetap muat dalam waktu dan mencegah anak terjebak di satu soal.',
                        'Server memeriksa sisa kesempatan setiap kali jawaban masuk, jadi dua jendela yang terbuka bersamaan atau kiriman ulang saat Wi-Fi lambat tidak bisa memberi kesempatan keempat.',
                        'Asesmen hanya sahih bila jawaban tidak bisa diintip: anak yang membuka isi halaman tidak boleh menemukan kuncinya.',
                    ],
                    'refs'    => [self::SHUTE08, self::HATTIE, self::ALMOND],
                    'sources' => [self::K2 . '5-aturan-3-kesempatan', self::K1 . '0-cara-memakai-ketiga-dokumen'],
                ],
                [
                    'id'    => 'bintang-serpihan',
                    'title' => 'Bintang dan Serpihan Cahaya',
                    'lead'  => 'Setiap tantangan yang selesai memberi satu [[Serpihan Cahaya|serpihan]] (15 seluruhnya) dan satu sampai tiga bintang.',
                    'list'  => [
                        'Bintang dihitung server dari jumlah percobaan, dengan aturan yang sama untuk semua siswa.',
                        'Serpihan tetap didapat walaupun kesempatan habis, supaya anak bisa melanjutkan cerita.',
                        'Gambar lentera, bintang, dan layar hasil dapat dilihat di [Katalog Aset GELITA](' . self::CATALOGUE . '#kelompok/result).',
                    ],
                    'why' => [
                        'Bintang dan serpihan adalah **hadiah**, bukan prestasi. Keduanya tidak dianalisis, karena dihitung dari jumlah percobaan yang sudah tercatat sendiri.',
                        'Skor 0–100 dan profil skor dihapus sejak perombakan kelas 5: angka di layar akan dibaca sebagai nilai, padahal pengukuran dilakukan di luar game.',
                    ],
                    'refs'    => [self::MALONE],
                    'sources' => [self::R01 . 'log-dihapus-atau-dipisah', self::K1 . '3-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'bantuan-narasi',
                    'title' => 'Bantuan, petunjuk, dan narasi',
                    'lead'  => 'Tombol 💡 [[Bantuan|bantuan]] ada di setiap tantangan; **petunjuk** adalah kalimat yang dicari di Cari Objek. [[Narasi|narasi]] diatur ketat.',
                    'list'  => [
                        'Bacaan dan pernyataan [[D1|d1]] **tidak pernah dibacakan**.',
                        'Situasi [[Skenario Digital|skenario-digital]] ([[D2|d2]]) dan petunjuk [[Periksa Unggahan|periksa-unggahan]] ([[D3|d3]]) boleh dibacakan lewat tombol ▶, tidak diputar otomatis, dan pemutarannya tercatat.',
                        'Narasi instruksi dicatat terpisah dari narasi cerita, supaya pemakaiannya bisa dibedakan.',
                        'Setiap pembukaan Bantuan dan pemutaran narasi tercatat (analitik 7).',
                        'Semua baris narasi beserta rekamannya dapat didengar di [Katalog Aset GELITA](' . self::CATALOGUE . '#narasi).',
                    ],
                    'why' => [
                        'D1 bertumpu pada kemampuan membaca. Bila bacaan dibacakan, yang terukur menjadi kemampuan menyimak.',
                        'D2 dan D3 tidak mengukur membaca, jadi narasi membantu anak yang lambat membaca tanpa mengubah hal yang diukur; narasi tanpa putar otomatis menjaga beban berpikir tetap terkendali.',
                        'Nama "Bantuan" untuk tombol 💡 dan "petunjuk" untuk Cari Objek dipisah supaya satu kata hanya punya satu arti.',
                    ],
                    'refs'    => [self::MAYER],
                    'sources' => [self::R04 . '25-oktober-2026'],
                ],
                [
                    'id'    => 'pustaka-bahasa',
                    'title' => 'Pustaka Kedu, dua bahasa, dan suara',
                    'lead'  => '[[Pustaka Kedu|pustaka-kedu]] berisi 30 halaman bacaan dwibahasa, terbuka per wilayah setelah wilayahnya tuntas. Seluruh area game tersedia dalam bahasa Indonesia dan Inggris; panel staf hanya berbahasa Indonesia.',
                    'list'  => [
                        'Isi Pustaka tidak diubah oleh perombakan kelas 5, atas permintaan peneliti.',
                        'Pergantian bahasa di tengah permainan dicatat, supaya pengaruhnya bisa diperhitungkan dalam analisis.',
                        'Narasi: suara sintetis menjadi cadangan pengisi suara (disetujui peneliti) dan ditandai sebagai suara sintetis di data penelitian.',
                        'Gambar, musik, dan narasi tidak dijelaskan di panduan ini: lihat [Katalog Aset GELITA](' . self::CATALOGUE . '), bagian [musik](' . self::CATALOGUE . '#musik), [narasi](' . self::CATALOGUE . '#narasi), dan [gambar Pustaka](' . self::CATALOGUE . '#kelompok/library).',
                    ],
                    'why' => [
                        'Pustaka dibuka **setelah** wilayah tuntas supaya bacaan tambahan tidak menjadi sumber jawaban saat tantangan berlangsung.',
                        'Cara pembuatan rekaman dicatat karena data penelitian harus menyebut dengan benar apakah anak mendengar suara asli atau sintetis.',
                    ],
                    'refs'    => [self::MAYER],
                    'sources' => [self::R04 . '15-november-2026', self::K1 . '2-yang-tidak-boleh-berubah'],
                ],
                [
                    'id'    => 'variasi-buklet',
                    'title' => 'Variasi, butir jangkar, dan buklet',
                    'lead'  => 'Setiap tantangan yang dinilai punya dua [[variasi|variasi]], a dan b, ditambah [[butir bersama x|butir-bersama]]. Pengerjaan pertama mengikuti [[buklet]] siswa (B1–B4).',
                    'list'  => [
                        '[[Butir jangkar|butir-jangkar]]: 6 pernyataan [[D1|d1]] (2 per wilayah) di [[kartu jangkar|kartu-jangkar]] yang sama untuk variasi a dan b.',
                        '[[Cari Objek|cari-objek]] babak 1 memakai butir bersama untuk semua siswa.',
                        'Buklet ditentukan dari baris dan kursi di denah kelas ([[L2|l2]]), dan melekat pada siswa sampai pertemuan terakhir.',
                        'Di luar [[mode penelitian|mode-penelitian]] satu variasi dipilih acak; pengerjaan ulang memakai variasi lain bila ada.',
                        'Per siswa di pengerjaan pertama: 61 butir literasi digital (37 [[D1|d1]], 12 [[D2|d2]], 12 [[D3|d3]]) ditambah 12 butir [[PBK|pbk]].',
                    ],
                    'why' => [
                        'Dua variasi mengurangi saling mencontek antartetangga; buklet per kursi menjamin tetangga mendapat susunan berbeda.',
                        'Butir jangkar menyambungkan kedua variasi ke satu skala [[Rasch|rasch]]. Rencana awal (butir yang diulang di tiga wilayah) diganti karena siswa yang sama memainkan ketiga wilayah.',
                        'Dua variasi (bukan tiga) sudah cukup untuk tujuan itu dan mengurangi butir yang harus ditulis dan divalidasi.',
                        'Kesetaraan variasi a dan b diuji dengan [[DIF|dif]] setelah data terkumpul.',
                    ],
                    'refs'    => [self::BOND, self::ADAMS],
                    'sources' => ['revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md#jumlah-per-siswa-dan-jumlah-yang-harus-ditulis'],
                ],
                [
                    'id'    => 'skenario-digital',
                    'title' => 'Skenario Digital',
                    'lead'  => 'Tantangan kelima di setiap wilayah, untuk dimensi [[D2|d2]]: situasi pendek berlatar Kedu dengan sudut pandang "kamu", lalu **pilih tindakan**, lalu **pilih alasan**. Gambar situasinya dapat dilihat di [Katalog Aset GELITA](' . self::CATALOGUE . '#tantangan/skenario).',
                    'table' => [['Topik (pilar)', 'Temanggung', 'Magelang', 'Wonosobo'], [
                        ['Data pribadi (Aman)', 'Tanda bahaya jelas', 'Tanda bahaya samar', 'Ada hadiah yang menggoda'],
                        ['Kata sandi dan OTP (Aman)', 'Orang asing yang minta', 'Teman dekat yang minta', 'Permintaan tampak resmi'],
                        ['Izin membagikan foto atau obrolan (Etis)', 'Foto diri sendiri', 'Foto teman', 'Tangkapan layar obrolan teman'],
                        ['Pesan orang asing dan kabar bohong (Aman, Cakap)', 'Jelas mencurigakan', 'Pesan berantai dari grup', 'Berkembang dua langkah'],
                    ]],
                    'list' => [
                        'Satu tindakan tepat (skor 2), satu kurang tepat (1), sisanya berisiko (0); lihat [[skor tindakan 0–1–2|skor-tindakan]]. Setiap pilihan punya [[kode kesalahan|kode-kesalahan]].',
                        'Alasan ([[kode R|kode-r]]) ditanyakan apa pun tindakannya; alasan hanya data diagnosis dan tidak masuk skor utama di versi pertama.',
                        'Umpan balik ramah tentang akibat pilihan, tanpa kata "salah" atau "gagal". Skenario tidak menakut-nakuti dan tidak memuat tautan sungguhan.',
                        'Semua unggahan tampil di aplikasi media sosial fiktif [[Kedugram|kedugram]]; nama akun dicek tidak sama dengan akun nyata.',
                    ],
                    'why' => [
                        '**Topik sama di tiap wilayah, kerumitannya yang naik** ([[kerumitan skenario|kerumitan-skenario]]), supaya topik tidak tercampur dengan tingkat kesulitan.',
                        'Alasan ditanyakan terpisah supaya pola "tindakannya benar, alasannya keliru" kelihatan.',
                        'Izin atas karya budaya dipindah ke [[D3|d3]] ([[Periksa Unggahan|periksa-unggahan]]), jadi topik izin di D2 cukup foto dan obrolan diri sendiri atau teman.',
                        'Latar dicek fakta: contoh awal memakai Dieng Culture Festival, padahal festival itu di Banjarnegara, jadi diganti Telaga Warna di Wonosobo.',
                    ],
                    'refs'    => [self::KOMINFO, self::DIGCOMP, self::WILSON],
                    'sources' => [self::R01 . 'skenario-digital-tantangan-kelima'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function overhaul(): array
    {
        return [
            'id'       => 'kelas5',
            'title'    => 'Perombakan kelas 5',
            'lead'     => 'Tiga tahap yang membersihkan dan menyusun ulang GELITA sebelum revisi penelitian, dan apa yang kemudian diubah lagi oleh revisi 10.',
            'sections' => [
                [
                    'id'    => 'dok-1-3',
                    'title' => 'Tiga tahap perombakan',
                    'lead'  => 'Perombakan dikerjakan dalam tiga tahap berurutan, dan game tetap bisa dimainkan di akhir setiap tahap.',
                    'table' => [['Tahap', 'Isinya'], [
                        ['1. Fondasi data dan mulai bersih', 'Susunan data baru, sisa uji coba lama dibersihkan, satu tahap bermain, pendaftaran tiga langkah, catatan perangkat, data mentah'],
                        ['2. Tantangan dan bank soal', 'Empat tantangan, aturan tiga kesempatan, variasi acak, bank soal kelas 5, gambar contoh'],
                        ['3. Tampilan 16:9 dan panel', '[[Layar 16:9|panggung]] berukuran tetap, tantangan selebar layar, panel staf yang lebih rapi, unduhan data mentah, rilis'],
                    ]],
                    'list' => [
                        'Fokus hanya kelas 5; data uji coba lama dihapus seluruhnya.',
                        '[[Pustaka|pustaka-kedu]], Kenali wilayah, dan sebagian besar cerita tidak boleh berubah.',
                        '"Tepat sejak awal" menjadi istilah **Berhasil pada Percobaan Pertama**.',
                        'Analitik di panel menjadi data mentah: halaman indeks kesulitan, daya pembeda, dan grafik dihapus.',
                    ],
                    'why' => [
                        'Penelitian belum mulai dan tidak ada data siswa asli, jadi susunan data boleh diubah tanpa membawa data lama.',
                        'Analitik olahan di panel dihapus karena pengolahan data dikerjakan peneliti dengan perangkat lunak statistik, bukan di game.',
                        'Satu tahap per bagian dengan daftar uji dan kriteria selesai menjaga game tetap berjalan sepanjang perombakan.',
                    ],
                    'refs'    => [self::PLOMP],
                    'sources' => [self::K1 . '1-permintaan--keputusan--tempat-pengerjaan', self::K1 . '0-cara-memakai-ketiga-dokumen'],
                ],
                [
                    'id'    => 'keputusan-kelas5',
                    'title' => 'Keputusan yang kemudian berubah',
                    'lead'  => 'Beberapa keputusan perombakan kelas 5 diubah lagi oleh revisi 10. Riwayat ini menjelaskan mengapa dokumen lama kadang berbeda dengan game sekarang.',
                    'table' => [['Perombakan kelas 5', 'Revisi 10', 'Mengapa berubah'], [
                        ['Empat tantangan, 12 serpihan', 'Lima tantangan, 15 serpihan', 'Literasi digital wajib ada; [[Skenario Digital|skenario-digital]] mengukur [[D2|d2]]'],
                        ['Tiga variasi a/b/c, dipilih acak', 'Dua [[variasi a/b|variasi]] + [[butir bersama x|butir-bersama]], dari [[buklet]]', 'Desain buklet dan [[butir jangkar|butir-jangkar]] untuk skala [[Rasch|rasch]]'],
                        ['Tanpa tes awal–akhir, satu tahap', 'Modul tes awal–akhir terpisah dari game', 'Bukti validitas kriteria'],
                        ['Izin orang tua diurus guru di luar sistem; satu centang siswa', 'Izin berkode per siswa, status mundur, permintaan hapus', 'Protokol izin etik dan pelindungan data pribadi'],
                        ['Peran guru melihat sekolahnya', 'Peran guru dihapus; ada peneliti dan ketua peneliti', 'Guru tidak ikut penelitian; identitas dipisah dari data'],
                        ['Pemilihan soal selalu acak', 'Pengerjaan pertama dari buklet; acak hanya di luar [[mode penelitian|mode-penelitian]]', 'Tetangga mendapat susunan berbeda dan seimbang per rombel'],
                    ]],
                    'why' => [
                        'Tidak ada fitur yang wajib dipertahankan. Batasnya hanya kebutuhan penelitian: kunci jawaban tetap di server, kode butir yang sudah punya data siswa tidak dipakai ulang, data mentah tetap lengkap.',
                    ],
                    'refs'    => [self::PLOMP],
                    'sources' => [self::K1 . '3-keputusan-yang-dikunci', self::R01 . 'keputusan-yang-sudah-diambil'],
                ],
                [
                    'id'    => 'panggung-16x9',
                    'title' => 'Layar 16:9 untuk Chromebook',
                    'lead'  => 'Seluruh layar game digambar di satu [[bidang berukuran tetap 16:9|panggung]] yang diperbesar atau diperkecil utuh mengikuti layar, dengan bingkai gelap di sisanya.',
                    'list'  => [
                        'Letak gambar, benda Cari Objek, dan panel sama di semua perangkat; tidak ada bagian yang berpindah tempat mengikuti ukuran layar.',
                        'Halaman tidak pernah bergulir; isi panjang bergulir di dalam panelnya.',
                        'Perangkat sentuh yang dipegang tegak melihat layar "Putar perangkatmu".',
                        'Di Chromebook berlayar 1280 × 800, game tampil dengan ukuran aslinya, tanpa diperkecil.',
                        'Panel staf tidak memakai bidang tetap ini: tabel data butuh lebar layar penuh.',
                    ],
                    'why' => [
                        'Letak yang tetap berarti semua anak melihat soal yang sama dengan tata letak yang sama; perbedaan tata letak antarperangkat akan menjadi sumber perbedaan skor.',
                        'Chromebook dan laptop sekolah adalah perangkat sasaran, jadi ukuran dan letak diatur untuk siswa kelas 5 di layar itu.',
                        'Pengambilan data tidak memakai ponsel: di ponsel sebagian besar teks tampil terlalu kecil, sehingga skor membaca ikut bergantung pada perangkat. Dalam [[mode penelitian|mode-penelitian]] layar yang terlalu kecil ditolak dengan pesan ramah.',
                    ],
                    'refs'    => [self::NIELSEN],
                    'sources' => [self::K3 . '1-prinsip-panggung-169'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function revision(): array
    {
        return [
            'id'       => 'revisi10',
            'title'    => 'Revisi 10: instrumen, validasi, dan etika',
            'lead'     => 'Bank soal, tes dan angket, validasi ahli, izin etik, pelindungan data pribadi, dan peran staf.',
            'sections' => [
                [
                    'id'    => 'bank-soal',
                    'title' => 'Kisi-kisi dan bank soal',
                    'lead'  => 'Kisi-kisi ditetapkan lebih dulu; bank soal berisi 116 butir literasi digital dalam [[variasi a/b|variasi]], 6 [[butir jangkar|butir-jangkar]], dan 12 butir [[PBK|pbk]], dan sudah disetujui peneliti.',
                    'list'  => [
                        'Beberapa bacaan diubah menjadi [[teks bergenre digital|teks-digital]] (unggahan, pengumuman, obrolan).',
                        'Setiap butir punya label dimensi, sub-aspek, [[level AKM|level-akm]], dan [[Bloom|bloom]], dengan asal labelnya (pengembang atau ahli).',
                        'Telaah pendahuluan pengembang menemukan dua pola sistematis (pilihan tepat dan alasan yang diharapkan selalu paling panjang) dan membetulkannya sebelum validasi.',
                        'Fakta budaya diperiksa: 135 klaim dicek dengan sumbernya, dan pengamat budaya menilai 44 fakta.',
                        'Bank soal disusun di satu lembar kerja yang dimasukkan ke game apa adanya, lalu diterjemahkan ke bahasa Inggris. Revisi dari ahli cukup diubah di lembar kerja itu.',
                    ],
                    'why' => [
                        'Kisi-kisi ditetapkan sebelum butir ditulis, supaya jumlah butir per dimensi dan level sesuai rencana analisis [[Rasch|rasch]].',
                        'Pola "jawaban benar paling panjang" bisa ditebak tanpa membaca; membetulkannya menjaga butir mengukur kemampuan, bukan kecerdikan menebak.',
                        'Nama fiktif dicek di web dan diganti bila mirip kelompok nyata, supaya soal tidak menyinggung atau mengiklankan pihak tertentu.',
                    ],
                    'refs'    => [self::WILSON, self::BOND],
                    'sources' => ['revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md#ringkasan', self::R04 . '25-oktober-2026'],
                ],
                [
                    'id'    => 'tes-angket',
                    'title' => 'Tes awal–akhir dan angket',
                    'lead'  => 'Tes awal (pertemuan 1) dan tes akhir (pertemuan 5) punya tiga bagian sejajar dengan tiga dimensi: Membaca (16 butir), Aman dan etis (8 skenario), Budaya digital (8 butir). Bentuk A dan B setara butir demi butir.',
                    'list'  => [
                        'Bagian membaca diadaptasi dari contoh soal resmi AKM; bagian [[D2|d2]] dan [[D3|d3]] ditulis sendiri.',
                        'Topik budaya tes sengaja **di luar Kedu**.',
                        'Bentuk dibagi seperti papan catur dari nomor baris dan kursi: tetangga selalu berbeda bentuk.',
                        'Angket akses digital dan butir risiko daring diisi di modul tes, tidak dibacakan.',
                        'Waktu ± 45–58 menit dengan batas longgar dan tanpa jam di layar.',
                    ],
                    'why' => [
                        'Tes berguna untuk dua hal: memeriksa apakah skor game sejalan dengan kemampuan di luar game (validitas kriteria), dan menggambarkan perubahan dari tes awal ke akhir secara eksploratif.',
                        'Topik di luar Kedu supaya kenaikan di tes akhir tidak datang dari hafalan isi game.',
                        'Butir risiko daring ditanyakan dengan izin terpisah dan hanya dilaporkan sebagai angka gabungan, karena jawabannya peka.',
                    ],
                    'refs'    => [self::CAMPBELL, self::AERA],
                    'sources' => ['revisi-10-analitik/13_TES_AWAL_AKHIR.md#ringkasan'],
                ],
                [
                    'id'    => 'validasi',
                    'title' => 'Validasi ahli dan uji keterbacaan',
                    'lead'  => 'Sebelas validator dengan nama samaran: lima ahli materi (Ahli Bahasa Anak 1–2, Ahli Literasi Digital 1–2, Ahli Pengukuran), tiga pengamat budaya (Ahli Budaya Kedu 1–3), dan tiga ahli media (Ahli Media Pembelajaran 1–3).',
                    'table' => [['Tujuan', 'Cara', 'Kriteria'], [
                        ['Validitas isi', 'Penilaian relevansi skala 1–4', '[[V Aiken|v-aiken]]; [[I-CVI|icvi]] aturan Lynn; S-CVI/Ave ≥ 0,90'],
                        ['Kesepakatan label', '[[Pelabelan buta|pelabelan-buta]] oleh dua ahli', '[[κ berbobot|kappa]] ≥ 0,70'],
                        ['Mutu media', 'Heuristik dengan skala keparahan 0–4, dua langkah', 'Rerata skor dan aturan veto'],
                        ['Keterbacaan', 'Empat anak kelas 5 ditambah satu di putaran kedua', 'Daftar temuan keterbacaan dan aturan revisi'],
                    ]],
                    'why' => [
                        '**Aturan Lynn**: dengan lima ahli atau kurang, butir lolos hanya bila semua setuju (I-CVI = 1,00). Aturan Polit dkk. (2007) yang lebih longgar tidak dipakai, supaya butir yang meragukan direvisi.',
                        'Pelabelan buta dikerjakan lebih dulu, sebelum ahli melihat label pengembang, supaya kesepakatannya jujur. Pelabelan hanya sekali, oleh dua ahli: anggota tim yang sudah melihat label tidak bisa melabel secara buta.',
                        'Penilaian ulang tanpa batas putaran sampai butir lolos, mengikuti sifat berulang ADDIE.',
                        'Ahli media menilai dua kali: langkah 1 pada game lama dan contoh layar baru, langkah 2 pada layar jadi di versi uji sebelum uji coba.',
                        'Uji keterbacaan dengan anak di luar sampel memastikan kalimat soal dipahami anak kelas 5 sebelum dipakai di kelas. Langkah kerja validator ada di [Panduan validator](/admin/panduan-validator).',
                    ],
                    'refs'    => [self::AIKEN, self::LYNN, self::POLIT06, self::POLIT07, self::COHEN60, self::COHEN68, self::NIELSEN],
                    'sources' => ['revisi-10-analitik/20_PAKET_VALIDASI.md#ringkasan', 'revisi-10-analitik/24_UJI_KETERBACAAN.md#ringkasan', self::MODEL],
                ],
                [
                    'id'    => 'etik',
                    'title' => 'Izin etik',
                    'lead'  => 'Permohonan izin etik memakai panduan klirens etik bidang sosial humaniora BRIN sebagai pedoman utama, dengan PSEPPKN 2021 sebagai cadangan. Izin sudah disetujui ([[gerbang G2|gerbang]]).',
                    'table' => [['Kegiatan', 'Dengan anak', 'Kapan'], [
                        ['[[KG1|kegiatan-anak]] Uji keterbacaan', '4 + 1 anak di luar sampel', 'Tahap 2'],
                        ['[[KG2|kegiatan-anak]] Berpikir lantang', '5–8 anak sekolah uji coba', 'Tahap 4'],
                        ['[[KG3a|kegiatan-anak]] Uji kelompok kecil', '8–12 anak, lima pertemuan', 'Tahap 4'],
                        ['[[KG3b|kegiatan-anak]] Uji kelompok menengah', '± 80 siswa', 'Tahap 4, sebelum G4'],
                        ['[[KG4|kegiatan-anak]] Wawancara sesudah bermain', '3 siswa per pertemuan wilayah', 'Bersama KG3a, KG3b, KG5'],
                        ['[[KG5|kegiatan-anak]] Pengambilan data utama', '± 360 direkrut, target 300', 'Setelah G4'],
                    ]],
                    'list' => [
                        'Lapis persetujuan: surat dan formulir orang tua dengan centang terpisah untuk rekaman suara dan butir risiko daring, persetujuan lisan anak di pertemuan 1, centang di halaman daftar, pengingat di setiap pertemuan. Status izin dicatat di [[L7|l7]].',
                        'Anak tanpa izin tidak dibuatkan akun dan ikut kegiatan pengganti di ruang yang sama.',
                        'Anak bisa mundur kapan saja dengan tiga pilihan: data tetap dipakai, sebagian dihapus, atau semua dihapus.',
                    ],
                    'why' => [
                        'Penelitian ini penelitian pendidikan dan sosial, bukan kesehatan, jadi panduan BRIN bidang sosial humaniora yang paling cocok.',
                        'Pesertanya anak (subjek rentan), sehingga izin orang tua, persetujuan anak, sesi berjeda, dan prosedur rujukan butir risiko daring dirancang tersendiri.',
                        'Satu anak hanya ikut satu kegiatan, supaya bebannya terbagi dan data uji tidak tercampur dengan data utama.',
                    ],
                    'refs'    => [self::BRIN, self::PSEPPKN],
                    'sources' => [self::R22 . 'pedoman-yang-dirujuk', self::R22 . 'kegiatan-yang-dimintakan-izin'],
                ],
                [
                    'id'    => 'pelindungan-data',
                    'title' => 'Pelindungan data pribadi',
                    'lead'  => 'Penilaian dampak pelindungan data pribadi (lampiran izin etik) menilai 14 risiko dan menetapkan 18 langkah perlindungan.',
                    'list'  => [
                        '[[Kode peserta|kode-peserta]] menggantikan nama di semua data penelitian; nama hanya di akun (dikelola admin) dan di lembar izin bernama.',
                        '**Peran dipisah**: admin memegang identitas, peneliti melihat data satu siswa lewat kodenya, hanya ketua peneliti yang mengunduh data penelitian.',
                        'Server, cadangan, dan penyimpanan di Indonesia; salinan cadangan dikunci dengan sandi; kunci jawaban tidak dikirim ke perangkat siswa.',
                        'Butir risiko daring hanya angka gabungan; penanda tindak lanjut hanya terlihat ketua peneliti dan tidak pernah diunduh.',
                        'Permintaan orang tua dan pemberitahuan kebocoran dalam 3 × 24 jam.',
                        'Saat studi ditutup nama dan sekolah di akun diganti kode; data berkode disimpan paling singkat 20 tahun.',
                        'Catatan dan rincian perangkat yang tidak dipakai sepuluh analitik **dihapus**, bukan hanya disembunyikan.',
                    ],
                    'why' => [
                        'Data anak termasuk data pribadi spesifik, dan tes serta skor permainan adalah evaluasi sistematis: dua pemicu yang mewajibkan penilaian dampak.',
                        'Minimalisasi data: yang tidak menjawab pertanyaan penelitian tidak boleh dikumpulkan, karena setiap data tambahan menambah risiko identifikasi.',
                        'Risiko sisa terbesar (kebocoran nama bersama jawaban, pengenalan kembali dari gabungan data) tetap sedang, karena tidak ada sistem daring yang sepenuhnya bebas kebocoran; dinilai dapat diterima dengan tinjauan tahunan.',
                    ],
                    'refs'    => [self::UU_PDP, self::PP_PDP, self::UU_SISNAS],
                    'sources' => [self::R26 . 'ringkasan-untuk-komite', self::R26 . 'langkah-perlindungan'],
                ],
                [
                    'id'    => 'peran-data',
                    'title' => 'Admin, peneliti, dan ketua peneliti',
                    'lead'  => 'Tiga peran staf dengan hak yang berbeda. Peran guru dihapus seluruhnya.',
                    'table' => [['Peran', 'Memegang', 'Tidak boleh'], [
                        ['Admin', 'Sistem, akun, identitas, isi game, rilis, catatan teknis', 'Melihat atau mengunduh data penelitian'],
                        ['Peneliti ([[P1–P3|peneliti-lapangan]])', 'Sesi lapangan, [[token|token-sesi]], pemantauan, data satu siswa lewat kodenya, atur ulang kata sandi lewat kode', 'Melihat nama siswa; mengunduh data'],
                        ['Ketua peneliti', 'Semua hak peneliti, unduhan data, [[data turunan|data-turunan]], pengaturan studi, konfirmasi permintaan data', 'Melihat nama siswa di sistem'],
                    ]],
                    'why' => [
                        'Admin menjaga sistem dan identitas, peneliti memegang data yang sudah disamarkan. Pemisahan ini membuat tidak ada satu akun pun yang melihat nama bersama jawaban.',
                        'Satu orang boleh memegang dua peran, tetapi lewat dua akun terpisah, supaya jejak pemeriksaannya jelas.',
                        'Laporan untuk guru di luar lingkup penelitian; bila dibuat nanti, perannya dibangun ulang dengan hak baru. Langkah per peran ada di [Panduan staf](/admin/panduan-staf).',
                    ],
                    'refs'    => [self::UU_PDP],
                    'sources' => [self::R01 . 'admin-dan-peneliti-dua-peran-berbeda'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function field(): array
    {
        return [
            'id'       => 'lapangan',
            'title'    => 'Lapangan dan data',
            'lead'     => 'Sesi lapangan dan token, mode penelitian, modul tes, catatan game, lapis data turunan, dan kamus data.',
            'sections' => [
                [
                    'id'    => 'sesi-lapangan',
                    'title' => 'Sesi lapangan, token, dan kode peserta',
                    'lead'  => 'Dalam [[mode penelitian|mode-penelitian]], siswa hanya bermain di [[sesi lapangan|sesi-lapangan]] yang diamati peneliti, dengan [[token sesi|token-sesi]] enam huruf dan angka yang ditulis di papan tulis.',
                    'table' => [['[[Pertemuan|pertemuan]]', 'Isinya'], [
                        ['P1', 'Pendaftaran, tes awal, cerita pembuka'],
                        ['P2', 'Temanggung'],
                        ['P3', 'Magelang'],
                        ['P4', 'Wonosobo'],
                        ['P5', 'Tantangan yang tertinggal, cerita penutup, tes akhir, skala sikap'],
                    ]],
                    'list' => [
                        '[[Kode peserta|kode-peserta]] seperti S03-A07: kode sekolah penelitian, huruf rombel, nomor urut. Sekolah uji coba berawalan U.',
                        'Di luar sesi, seluruh game tertutup sampai penelitian selesai, dengan pesan ramah.',
                        '[[Lembar lapangan L0–L8|lembar-lapangan]] diisi peneliti, dengan kunci yang sama dengan catatan game: kode peserta dan jam WIB.',
                        'Tiga peneliti per kelas ([[P1–P3|peneliti-lapangan]]): koordinator sesi, pengamat keterlibatan, pengamat bantuan.',
                        'Langkah hari lapangan ada di [Panduan staf](/admin/panduan-staf).',
                    ],
                    'why' => [
                        'Data hanya bersih bila siswa bermain di sesi yang diamati: tanpa bantuan orang tua atau kakak di rumah yang tidak tercatat.',
                        'Token membawa sekolah dan rombel, sehingga siswa tidak perlu memilih sekolah, dan setiap sesi game tersambung otomatis ke lembar lapangannya.',
                        'Token mengatur wilayah per pertemuan supaya laju bermain antarkelas setara. Versi ringan, bukan sistem ujian seperti ANBK: tidak ada aplikasi khusus atau token yang berganti tiap 15 menit.',
                        'Kode sekolah dibuat peneliti, bukan nomor resmi sekolah, supaya kode tetap samaran dan pendek. Rombel juga menjadi satu tingkat dalam analisis.',
                        'Lembar lapangan mengisi yang tidak terlihat di catatan game: siapa yang dibantu ([[L3|l3]]), perangkat yang dipakai bersama, kondisi kelas ([[L1|l1]]). Pengamatan keterlibatan ([[L4|l4]]) mengadaptasi BROMP.',
                    ],
                    'refs'    => [self::BROMP, self::ERICSSON],
                    'sources' => [self::R01 . 'token-sesi-perlu-atau-tidak', self::R01 . 'kode-peserta-baru', self::R01 . 'kerja-lapangan-cara-kerjanya'],
                ],
                [
                    'id'    => 'mode-penelitian',
                    'title' => 'Mode penelitian',
                    'lead'  => 'Semua aturan pengambilan data ada di balik [[mode penelitian|mode-penelitian]], sakelar per studi yang hanya menyala untuk studi yang sedang berjalan.',
                    'table' => [['Aturan', 'Mengapa'], [
                        ['Jam disembunyikan; waktu tetap dicatat server', 'Jam di layar membuat anak terburu-buru dan mengubah perilaku yang diukur'],
                        ['Layar yang terlalu kecil ditolak dengan pesan ramah', 'Teks terlalu kecil membuat skor membaca bergantung pada perangkat'],
                        ['Pengerjaan pertama yang terputus dilanjutkan di sesi berikutnya', '[[Percobaan pertama|percobaan-pertama]] harus satu; membuat pengerjaan baru akan menghapusnya'],
                        ['Pengerjaan ulang baru terbuka setelah kelima tantangan wilayah tuntas', 'Mencegah anak mengulang sebelum percobaan pertamanya lengkap'],
                        ['Jeda tanpa gerakan ≥ 20 detik dicatat', 'Bahan untuk menghitung [[waktu aktif|waktu-aktif]]'],
                        ['Cek suara sekali per sesi game', 'Memastikan headset berfungsi sebelum [[narasi]] dipakai'],
                    ]],
                    'why' => [
                        'Sakelar per studi membuat aturan penelitian bisa disiapkan lebih awal tanpa mengubah permainan biasa, dan studi uji coba bisa dibedakan dari studi utama.',
                        'Ambang (jeda, [[tebakan cepat|tebakan-cepat]], lebar layar minimum) disimpan sebagai pengaturan studi, supaya bisa ditetapkan ulang setelah uji coba tanpa mengubah game, lalu dikunci di [[G4|gerbang]].',
                    ],
                    'refs'    => [self::WISE],
                    'sources' => ['revisi-10-analitik/33_IMPLEMENTASI_MODE_PENELITIAN.md#2-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'modul-tes',
                    'title' => 'Modul tes',
                    'lead'  => 'Tes awal, tes akhir, angket, dan butir risiko daring dikerjakan di modul tes GELITA, dan [[skala sikap|skala-sikap]] di [[Balai Refleksi|balai-refleksi]].',
                    'list'  => [
                        'Tampilannya sederhana seperti lembar soal, tanpa hiasan permainan.',
                        'Jawaban langsung tersimpan per butir; skor dihitung server dari kunci yang tidak pernah sampai ke halaman.',
                        'Batas waktu tidak tampil di layar, dan peneliti bisa menambah menit dari halaman sesi lapangan. Pelaksanaannya dicatat di [[L6|l6]].',
                        'Skor disimpan mentah; nilai olahan dihitung di [[data turunan|data-turunan]].',
                    ],
                    'why' => [
                        'Tes harus terasa sebagai tes, terpisah dari game, supaya tidak tercampur dengan pengalaman bermain dan hadiah.',
                        'Di modul, tidak ada kertas dan tidak ada pengetikan ulang, sehingga tidak ada salah ketik di data tes.',
                    ],
                    'refs'    => [self::AERA],
                    'sources' => ['revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#2-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'log-event',
                    'title' => 'Catatan game',
                    'lead'  => 'Setiap jenis [[catatan game|log-game]] masuk salah satu dari tiga: dihapus, hanya untuk admin, atau untuk peneliti. Semua jenis catatan terdaftar di satu daftar tetap.',
                    'table' => [['Contoh', 'Keputusan', 'Alasan'], [
                        ['Awal, jeda, akhir sesi; alur tantangan; jawaban', 'Peneliti', 'Analitik 1, 3, 4, 5'],
                        ['Bantuan dan narasi', 'Peneliti', 'Analitik 7'],
                        ['Perpindahan layar, Pustaka, gulir bacaan', 'Peneliti', 'Analitik 8'],
                        ['Setiap ganti pilihan sebelum Cek', 'Dihapus', 'Tidak dipakai; cukup hitungan per butir'],
                        ['Merek dan model perangkat, identitas peramban, zona waktu', 'Dihapus', 'Tidak dipakai dan menambah risiko identifikasi'],
                        ['Ukuran layar berubah, selisih jam perangkat, catatan yang hilang', 'Admin', 'Teknis; jumlah catatan yang hilang tampil di halaman pemantauan'],
                    ]],
                    'why' => [
                        'Minimalisasi data anak sesuai UU No. 27 Tahun 2022: catatan yang tidak menjawab sepuluh analitik dihapus, bukan disembunyikan.',
                        'Satu daftar tetap mencegah jenis catatan baru muncul tanpa alasan; catatan tertentu (misalnya kesempatan habis) hanya boleh ditulis server, supaya tidak bisa dipalsukan dari perangkat siswa.',
                    ],
                    'refs'    => [self::UU_PDP],
                    'sources' => [self::R01 . 'log-dihapus-atau-dipisah'],
                ],
                [
                    'id'    => 'lapis-turunan',
                    'title' => 'Tiga lapis data',
                    'lead'  => 'Data mentah dan [[data turunan|data-turunan]] dihitung di sistem; analisis statistik dikerjakan peneliti di luar sistem.',
                    'table' => [['Lapis', 'Di mana', 'Isinya', 'Mengapa di situ'], [
                        ['1. Data mentah', 'Sistem GELITA', 'Setiap ketukan, jawaban, percobaan, dan catatan game; hanya ditambah, tidak diubah', 'Sumber kebenaran yang bisa ditelusuri ulang'],
                        ['2. Data turunan', 'Sistem GELITA', '[[Kode kesalahan|kode-kesalahan]] per percobaan, [[waktu aktif|waktu-aktif]], [["dilewati"|dilewati]], [[tersensor]], [[tebakan cepat|tebakan-cepat]], skor, indeks', 'Aturannya bergantung pada jalannya game; dihitung sekali, sama untuk semua peneliti, dan diperiksa otomatis'],
                        ['3. Analisis', 'Perangkat lunak statistik (R, jamovi, Winsteps, atau Python)', '[[Rasch|rasch]], model campuran, [[κ|kappa]], grafik, tabel', 'Reviewer jurnal mengharapkan perangkat lunak statistik standar dan langkah yang bisa diulang'],
                    ]],
                    'list' => [
                        'Data turunan dihitung terpisah dari permainan; setiap penghitungan diberi nomor versi dan mencatat ambang yang dipakai.',
                        'Unduhan menghitung ulang bila data mentah berubah sejak penghitungan terakhir.',
                        'Data siswa yang mundur dihitung sampai saat mundur saja.',
                    ],
                    'why' => [
                        'Membangun statistik di dalam game mahal dan rawan salah; membiarkan setiap peneliti menghitung sendiri membuat hasil berbeda-beda.',
                        'Penghitungan bernomor versi mencatat ambang yang dipakai, sehingga setiap angka di unduhan bisa dijelaskan ulang.',
                    ],
                    'refs'    => [self::WISE, self::SINGER, self::BOND],
                    'sources' => [self::R01 . 'olah-data-di-sistem-atau-di-alat-lain', 'revisi-10-analitik/35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#2-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'kamus-data',
                    'title' => 'Kamus data',
                    'lead'  => '[[Kamus data|kamus-data]] membekukan definisi sepuluh analitik dan semua variabel. Nomor versinya ditulis di setiap unduhan: versi 1.0, lalu versi 1.1 yang sudah disetujui; ambang yang ditetapkan ulang setelah uji coba akan menjadi versi 1.2.',
                    'list'  => [
                        'Data inti adalah [[percobaan pertama|percobaan-pertama]] di pengerjaan pertama.',
                        'Butir tanpa percobaan adalah data hilang, bukan salah; butir yang belum berhasil setelah tiga kesempatan adalah data [[tersensor]].',
                        '[["Dilewati"|dilewati]] dihitung dalam tiga jenis terpisah.',
                        'Ambang sementara: jeda 3 menit untuk [[waktu aktif|waktu-aktif]] dan [[tebakan cepat|tebakan-cepat]] di bawah 3 detik.',
                    ],
                    'why' => [
                        'Definisi dibekukan sebelum data diambil, supaya aturan hitung tidak berubah mengikuti hasil.',
                        'Versi baru menambah sumber data dan variabel tanpa mengubah definisi lama, sehingga unduhan lama tetap bisa dibaca.',
                    ],
                    'refs'    => [self::NOSEK],
                    'sources' => ['revisi-10-analitik/11_KAMUS_DATA.md#ringkasan', 'revisi-10-analitik/11_KAMUS_DATA.md#versi-dan-cara-berubah'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function together(): array
    {
        return [
            'id'       => 'menyatu',
            'title'    => 'Bagaimana semuanya menyatu',
            'lead'     => 'Perjalanan satu anak dari izin sampai analisis, rujukan, dan istilah.',
            'sections' => [
                [
                    'id'    => 'alur-satu-anak',
                    'title' => 'Perjalanan satu anak',
                    'lead'  => 'Dari surat izin sampai angka di analisis, setiap langkah memakai keputusan dari bab-bab sebelumnya.',
                    'steps' => [
                        '**Izin.** Orang tua menerima surat dan formulir; anak tanpa izin tidak dibuatkan akun. Lihat [Izin etik](#etik).',
                        '**Pertemuan 1.** Anak memberi persetujuan lisan, mendaftar dengan [[token sesi|token-sesi]], mendapat [[kode peserta|kode-peserta]], lalu mengerjakan tes awal di modul tes. Lihat [Sesi lapangan](#sesi-lapangan) dan [Modul tes](#modul-tes).',
                        '**Kursi dan buklet.** Koordinator memasukkan baris dan kursi dari denah [[L2|l2]]; panel menentukan [[buklet]] dan bentuk tes. Lihat [Variasi dan buklet](#variasi-buklet).',
                        '**Pertemuan 2–4.** Satu wilayah per pertemuan: lima tantangan, tiga kesempatan per soal, [[percobaan pertama|percobaan-pertama]] dinilai, setiap percobaan dan [[kode kesalahan|kode-kesalahan]] tercatat. Peneliti mengisi [[lembar pengamatan|lembar-lapangan]]. Lihat [Lima tantangan](#lima-tantangan).',
                        '**Pertemuan 5.** Tantangan yang tertinggal, cerita penutup, tes akhir, [[skala sikap|skala-sikap]] di [[Balai Refleksi|balai-refleksi]].',
                        '**Data.** Data mentah dan lembar lapangan bertemu lewat kode peserta dan jam WIB; [[data turunan|data-turunan]] dihitung dengan [[kamus data|kamus-data]] bernomor versi. Lihat [Tiga lapis data](#lapis-turunan).',
                        '**Analisis.** Ketua peneliti mengunduh data berkode; [[Rasch|rasch]] per dimensi, [[DIF|dif]], pola kesalahan, dan korelasi dengan tes dikerjakan di perangkat lunak statistik, sesuai [[pra-registrasi]].',
                        '**Penutupan.** Nama dimusnahkan dan diganti kode; data berkode disimpan sesuai aturan. Lihat [Pelindungan data](#pelindungan-data).',
                    ],
                    'why' => [
                        'Setiap mata rantai menjaga satu hal: izin menjaga hak anak, token menjaga data bersih, buklet menjaga kejujuran jawaban, server menjaga kunci, kamus data menjaga aturan hitung, pemisahan peran menjaga identitas.',
                    ],
                    'refs'    => [self::KANE, self::AERA],
                    'sources' => [self::R01 . 'kerja-lapangan-cara-kerjanya', 'revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#4-satu-siswa-dari-p1-sampai-p5'],
                ],
                [
                    'id'    => 'rujukan',
                    'title' => 'Daftar rujukan',
                    'lead'  => 'Semua karya yang dirujuk panduan ini, seperti dikutip dokumen penelitian GELITA. Setiap rujukan dicek ke sumber aslinya di dokumen model penelitian, dengan tanda tingkat pengecekannya.',
                    'refs'  => [
                        self::ADAMS, self::AERA, self::AIKEN, self::ALMOND, self::ALWASILAH, self::BLOOM, self::BOND, self::BRANCH,
                        self::CAMPBELL, self::COHEN60, self::COHEN68, self::DIGCOMP, self::CSIKS, self::ERICSSON, self::FREIRE, self::GAY,
                        self::GEE, self::HATTIE, self::KANE, self::KOMINFO, self::LINACRE, self::LYNN, self::MALONE, self::MAYER,
                        self::MESSICK, self::HAERTEL, self::MISLEVY, self::NIELSEN, self::NIEVEEN, self::NOSEK, self::BROMP, self::PLOMP,
                        self::POLIT06, self::POLIT07, self::SHUTE08, self::SHUTE11, self::SINGER, self::TESSMER, self::PIRLS, self::WEBB,
                        self::WILSON, self::WISE, self::BRIN, self::PSEPPKN, self::UU_PDP, self::PP_PDP, self::UU_SISNAS,
                    ],
                    'why'     => [
                        'Hanya karya yang dikutip dokumen penelitian yang dicantumkan, dan setiap rujukan sudah dicek ke sumber aslinya, bukan ditulis dari ingatan. Dokumen model penelitian mencatat 14 rincian yang mudah keliru dikutip, misalnya batas infit dan outfit 0,5–1,5 yang tepatnya dari Linacre (2002), bukan Bond & Fox (2015).',
                    ],
                    'note'    => 'Rujukan peraturan dicek ke naskahnya saat dokumen izin etik dan penilaian dampak disusun. Rincian halaman, DOI, dan tanda pengecekan ada di dokumen model penelitian.',
                    'sources' => [self::MODEL, self::R26 . 'dasar-hukum'],
                ],
                [
                    'id'       => 'istilah',
                    'title'    => 'Istilah',
                    'lead'     => 'Istilah dan kode yang muncul di panduan ini, dengan contoh. Istilah yang ada gambarnya di Katalog Aset diberi tautan ke sana.',
                    'glossary' => true,
                ],
            ],
        ];
    }
}
