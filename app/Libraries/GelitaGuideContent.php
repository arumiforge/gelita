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
 * catalogue, CATALOGUE), the field-day steps and the panel (staff guide),
 * the validators' workflow and accounts (validator guide).
 *
 * Section parts on top of GuideContent's (see StaffGuideContent):
 *   why      list: the reasons behind the decisions on the page
 *   refs     list: works cited for the page, only works the repository's
 *            documents cite (APA style, shortened)
 *   docs     list: repository documents a reader can open for more
 *   sources  list: `path.md#anchor` under docs/ (GuideSourceCheck::GELITA_DOCS);
 *            never rendered; GelitaGuideTest fails when one changed until
 *            `php spark gelita:panduan:cek --tandai --panduan=gelita`
 */
final class GelitaGuideContent extends GuideContent
{
    public const BASE = 'admin/panduan-gelita';

    /** The asset catalogue: pictures, sound and music live there, not here. */
    public const CATALOGUE = 'https://katalog.gelita.id/';

    /** Sections offered as cards on the first page. */
    public const POPULAR = ['gelita-sekilas', 'tiga-dimensi', 'addie', 'lima-tantangan', 'server-kebenaran', 'alur-satu-anak'];

    // Source prefixes (relative to docs/)
    private const R01 = 'revisi-10-analitik/01_REVISI_10_ANALITIK.md#';
    private const R04 = 'revisi-10-analitik/04_CATATAN_KEPUTUSAN.md#';
    private const R22 = 'revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md#';
    private const R26 = 'revisi-10-analitik/26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md#';
    private const MODEL = 'revisi-10-analitik/dokumen-pendukung-peneliti/model-penelitian-pengembangan.html';
    private const K1 = 'perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md#';
    private const K2 = 'perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md#';
    private const K3 = 'perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md#';

    // Repository documents (shown as "Dokumen di repositori")
    private const D01 = '`docs/revisi-10-analitik/01_REVISI_10_ANALITIK.md`';
    private const D04 = '`docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`';
    private const DMODEL = '`docs/revisi-10-analitik/dokumen-pendukung-peneliti/model-penelitian-pengembangan.pdf`';

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

    /** @return array<string, array{term: string, def: string, example: string, more: string|null}> */
    public static function terms(): array
    {
        return [
            'literasi-digital' => [
                'term'    => 'Literasi digital',
                'def'     => 'Konstruk utama yang diukur GELITA: kemampuan menemukan dan menilai informasi, bertindak aman dan etis, dan ikut menjaga representasi budaya di ruang digital. Di GELITA dipecah menjadi tiga dimensi, D1–D3.',
                'example' => 'Memilih untuk tidak mengirim kode OTP kepada teman, lalu memberi tahu orang tua.',
                'more'    => 'tiga-dimensi',
            ],
            'percobaan-pertama' => [
                'term'    => 'Percobaan pertama',
                'def'     => 'Jawaban siswa sebelum mendapat umpan balik apa pun. Inilah data inti analisis; kesempatan kedua dan ketiga disediakan untuk belajar.',
                'example' => 'Pilihan tindakan pertama di sebuah Skenario Digital.',
                'more'    => 'tiga-kesempatan',
            ],
            'ecd' => [
                'term'    => 'Evidence-Centered Design (ECD)',
                'def'     => 'Cara merancang asesmen: tentukan dulu klaim tentang siswa, lalu bukti yang mendukungnya, lalu tugas yang menghasilkan bukti itu.',
                'example' => 'Klaim "siswa bisa menilai sumber" didukung bukti "memilih pengumuman resmi", yang dihasilkan pernyataan di Benar atau Salah.',
                'more'    => 'ecd',
            ],
            'addie' => [
                'term'    => 'ADDIE',
                'def'     => 'Model penelitian dan pengembangan yang dipakai GELITA: Analyze, Design, Develop, Implement, Evaluate. Setiap fase diuji, dan temuan dikembalikan ke fase Develop sampai produknya final.',
                'example' => 'Revisi setelah uji kelompok kecil masuk ke Prototipe III sebelum uji kelompok menengah.',
                'more'    => 'addie',
            ],
            'gerbang' => [
                'term'    => 'Gerbang (G1–G5)',
                'def'     => 'Titik keputusan di peta jalan. Tahap berikutnya baru dimulai setelah syarat gerbangnya terpenuhi.',
                'example' => 'G2: validasi ahli selesai dan izin etik disetujui (dilalui 4 November 2026).',
                'more'    => 'tahap-gerbang',
            ],
            'kode-kesalahan' => [
                'term'    => 'Kode kesalahan',
                'def'     => 'Kode jenis kekeliruan yang dipasang pada setiap pilihan salah sebelum data diambil, lalu ikut tercatat di setiap percobaan salah.',
                'example' => '`INF-SUM` (salah menilai sumber), `DIG-PRI` (membagikan data pribadi berlebihan).',
                'more'    => 'kode-kesalahan',
            ],
            'buklet' => [
                'term'    => 'Buklet',
                'def'     => 'Susunan variasi soal (B1–B4) yang menentukan variasi a atau b di pengerjaan pertama setiap siswa, dihitung dari baris dan kursinya.',
                'example' => 'Dua siswa yang duduk bersebelahan mendapat buklet berbeda.',
                'more'    => 'variasi-buklet',
            ],
            'mode-penelitian' => [
                'term'    => 'Mode penelitian',
                'def'     => 'Sakelar per studi yang menyalakan semua aturan pengambilan data: token sesi, jam tersembunyi, penolakan layar kecil, buklet, dan lain-lain. Mati secara bawaan.',
                'example' => 'Saat mode penelitian menyala, game hanya bisa dimainkan di dalam sesi lapangan.',
                'more'    => 'mode-penelitian',
            ],
            'data-turunan' => [
                'term'    => 'Data turunan',
                'def'     => 'Nilai analisis yang dihitung sistem dari data mentah dengan aturan tetap: waktu aktif, tebakan cepat, "dilewati", tersensor, skor, dan indeks.',
                'example' => 'Waktu aktif sebuah butir: waktu total dikurangi layar tersembunyi dan jeda panjang.',
                'more'    => 'lapis-turunan',
            ],
            'kamus-data' => [
                'term'    => 'Kamus data',
                'def'     => 'Definisi beku semua variabel dan aturan hitung, berversi, dan ikut setiap unduhan data penelitian.',
                'example' => 'Unduhan saat ini menulis versi `kamus-1.1`.',
                'more'    => 'kamus-data',
            ],
            'prototipe' => [
                'term'    => 'Prototipe I–III',
                'def'     => 'Nama versi produk di sepanjang pengembangan: I berupa dokumen dan mock-up, II sistem di instalasi uji, III versi lengkap yang direvisi setelah setiap uji dengan siswa.',
                'example' => 'Validator menilai Prototipe I; uji kelompok kecil memakai Prototipe III.',
                'more'    => 'tahap-gerbang',
            ],
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
            self::technology(),
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
                    'lead'  => 'Panduan ini menceritakan GELITA dari awal sampai akhir: masalah yang dijawab, model penelitiannya, apa yang diukur, desain permainan, teknologinya, dan bagaimana semuanya menyatu. Setiap halaman menjelaskan **mengapa** sebuah keputusan diambil, lalu menyebut rujukan dan dokumen yang bisa dibuka.',
                    'list'  => [
                        '**Untuk siapa.** Tim peneliti, validator ahli, guru, kepala sekolah, dan siapa pun yang ingin memahami GELITA lebih dalam. Panduan ini terbuka dan dapat dibaca tanpa masuk.',
                        '**Isi lengkap.** Pengembang memutuskan panduan ini memuat semua rincian, termasuk rincian butir dan kode kesalahan. Bagi validator yang sedang mengerjakan pelabelan buta (B.5a), sebaiknya halaman bab [Apa yang diukur](#tiga-dimensi) dan [Revisi 10](#bank-soal) dibaca **setelah** lembar B.5a dikirim.',
                        '**Cara membaca.** Setiap halaman punya bagian **Mengapa begini** (alasan keputusan), **Rujukan** (karya yang dikutip dokumen penelitian), dan **Dokumen di repositori** (tempat rinciannya).',
                        '**Yang tidak dibahas di sini.** Gambar, suara, dan musik ada di [Katalog Aset GELITA](' . self::CATALOGUE . '). Langkah kerja di panel dan hari lapangan ada di [Panduan staf](/admin/panduan-staf). Bahan dan akun validator ada di [Panduan validator](/admin/panduan-validator).',
                    ],
                    'note'    => 'Panduan ini merangkum dokumen di repositori. Bila ada yang berbeda, dokumen penelitian dan catatan keputusan yang berlaku.',
                    'why'     => [
                        'Keputusan GELITA tersebar di puluhan dokumen dan dua catatan keputusan yang besar. Satu halaman yang menyambungkannya memudahkan orang baru memahami alasannya tanpa membaca semuanya.',
                        'Rincian yang sensitif bagi validator sengaja dibuka (keputusan pengembang, 17 November 2026): pembaca panduan ini sudah menyelesaikan validasi atau tidak sedang menilai.',
                    ],
                    'docs'    => ['`docs/revisi-10-analitik/00_INDEKS.md` (urutan baca dan status dokumen)', self::D04, '`docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`'],
                    'sources' => ['revisi-10-analitik/00_INDEKS.md#mulai-sesi-baru-di-sini'],
                ],
                [
                    'id'    => 'gelita-sekilas',
                    'title' => 'GELITA dalam satu halaman',
                    'lead'  => 'GELITA (Game Edukasi Literasi dan Etnopedagogi Kedu) adalah game web untuk siswa kelas 5 SD/MI yang sekaligus menjadi instrumen penelitian: **model asesmen literasi digital** dengan konteks budaya Kedu.',
                    'table' => [['Hal', 'Isinya'], [
                        ['Judul penelitian', 'Pengembangan Game Edukasi Etnopedagogi Kedu Berbasis Analitik sebagai Model Asesmen Literasi Digital Peserta Didik Sekolah Dasar'],
                        ['Yang diukur', 'Literasi digital dalam tiga dimensi: D1 literasi informasi, D2 aman dan etis, D3 partisipasi dan representasi budaya Kedu di ruang digital'],
                        ['Permainan', 'Jaka, dibimbing Mbah Kedu, memulihkan 15 Serpihan Cahaya di Temanggung (mudah), Magelang (sedang), dan Wonosobo (sulit); lima tantangan per wilayah'],
                        ['Model penelitian', 'ADDIE (Branch, 2009), dengan ECD untuk merancang asesmen dan empat kriteria mutu'],
                        ['Data', 'Data mentah setiap percobaan dan event, lapis data turunan, lembar lapangan L0–L8; analisis Rasch di luar sistem'],
                        ['Peserta rencana', 'Target 300 siswa yang datanya bisa dianalisis (sekitar 360 direkrut), lima pertemuan per kelas, setelah uji coba bertahap di sekolah uji coba'],
                        ['Teknologi', 'CodeIgniter 4, halaman dirender server, JavaScript tanpa bundler, panggung 16:9 tetap; server satu-satunya sumber kebenaran'],
                    ]],
                    'why' => [
                        'Game dipilih karena merekam **tindakan** anak, percobaan demi percobaan, bukan penilaian diri seperti angket. Dari situ terlihat mengapa anak keliru, bukan hanya skornya.',
                        'Budaya Kedu menjadi konteks agar anak belajar mengenali representasi budaya daerahnya sendiri di ruang digital (etnopedagogi).',
                    ],
                    'refs'    => [self::BRANCH, self::MISLEVY, self::ALWASILAH],
                    'docs'    => [self::D01 . ' → Judul penelitian dan konstruk', '`docs/revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md` → Latar belakang dan nilai sosial'],
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
                        'GELITA merekam tindakan anak: jawaban pertama, kesalahan berkode, alasan, bantuan yang dibuka, dan waktu. Dari situ peneliti melihat **mengapa** anak keliru.',
                        'Kerangkanya empat pilar literasi digital Komdigi dan Siberkreasi, dengan DigComp 3.0 sebagai padanan internasional; level membaca mengikuti AKM dan PIRLS 2026.',
                        'Hasil yang diharapkan: alat asesmen yang divalidasi, rekomendasi pengembangan asesmen berbasis game untuk anak, dan gambaran kesalahan literasi digital yang sering terjadi pada siswa kelas 5. Penelitian ini **tidak menilai** siswa, guru, atau sekolah.',
                    ],
                    'why' => [
                        'Asesmen yang dijalin ke dalam permainan mengumpulkan data kinerja terus-menerus selama bermain, tanpa terasa sebagai ujian (stealth assessment).',
                        'Analitik pembelajaran untuk game memberi cara menelusuri jejak percobaan menjadi informasi tentang proses berpikir.',
                    ],
                    'refs'    => [self::SHUTE11, self::FREIRE, self::KOMINFO, self::DIGCOMP],
                    'docs'    => ['`docs/revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md` → Latar belakang dan nilai sosial'],
                    'sources' => [self::R22 . 'latar-belakang-dan-nilai-sosial'],
                ],
                [
                    'id'    => 'perjalanan',
                    'title' => 'Perjalanan GELITA',
                    'lead'  => 'GELITA berawal sebagai game literasi membaca berbasis budaya Kedu. Dua perombakan besar menjadikannya model asesmen literasi digital.',
                    'table' => [['Waktu', 'Yang terjadi', 'Mengapa'], [
                        ['Sebelum Oktober 2026', 'Game literasi membaca dengan cerita Jaka dan Mbah Kedu, Pustaka Kedu, dan beberapa jenis kuis', 'Titik awal: membaca dalam konteks budaya Kedu'],
                        ['6–12 Oktober 2026', '[Perombakan kelas 5](#dok-1-3): fokus kelas 5, reset total, empat tantangan, aturan tiga kesempatan, panggung 16:9, data mentah', 'Peneliti meminta game yang fokus dan data yang bersih sebelum penelitian dimulai'],
                        ['23 Oktober 2026', 'Revisi 10 analitik dimulai: judul penelitian ditetapkan, literasi digital menjadi konstruk utama, Skenario Digital tantangan kelima', 'Judul penelitian menyebut GELITA sebagai model asesmen literasi digital'],
                        ['25–27 Oktober 2026', 'Kisi-kisi, draf bank soal, kamus data, instrumen validasi, izin etik, dan pelindungan data disusun', 'Desain instrumen harus selesai sebelum validasi (gerbang G1)'],
                        ['1 November 2026', 'Model diganti ADDIE; semua perubahan sistem dibangun sekarang di balik sakelar studi', 'Peneliti ingin setiap fase diuji dan direvisi sampai final; sistem perlu siap saat izin etik terbit'],
                        ['4 November 2026', 'Gerbang G2 dilalui: validasi ahli selesai dan izin etik disetujui', 'Kegiatan dengan anak yang tercakup protokol boleh dimulai sesuai jadwal peneliti'],
                        ['3–17 November 2026', 'Bagian implementasi 1–9 dibangun: peran, sesi lapangan, data, persetujuan, bank baru, Skenario Digital, mode penelitian, modul tes, data turunan', 'Prototipe II harus memuat semua kebutuhan penelitian sebelum gerbang G3'],
                    ]],
                    'why' => [
                        'Setiap perubahan besar dicatat dengan tanggal, asal, dan alasannya, supaya penguji dan peneliti lain bisa menelusuri mengapa produk menjadi seperti sekarang (refleksi dan dokumentasi sistematis, Plomp, 2013, hlm. 19).',
                    ],
                    'refs'    => [self::PLOMP],
                    'docs'    => [self::D04 . ' (terbaru di atas)', '`docs/arsip/CATATAN_IMPLEMENTASI-2026-10.md` (catatan perombakan kelas 5)', '`docs/PROGRESS.md`'],
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
                        'Menggambarkan pola kesalahan, penggunaan bantuan, dan alasan anak di Skenario Digital.',
                    ],
                    'why' => [
                        'Klaim utamanya soal **mutu pengukuran**, bukan peningkatan hasil belajar. Tidak ada kelompok kontrol, jadi penelitian ini tidak mengklaim game meningkatkan literasi.',
                        'Bukti validitas disusun mengikuti lima sumber bukti Standards: isi, proses respons, struktur internal, hubungan dengan variabel lain, dan akibat pemakaian.',
                    ],
                    'refs'    => [self::AERA, self::MESSICK, self::KANE],
                    'docs'    => ['`docs/revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md` → Tujuan', self::D01 . ' → Rencana analisis singkat'],
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
                    'lead'  => 'Sejak 1 November 2026 GELITA memakai model [[ADDIE|addie]] (Branch, 2009), keputusan peneliti. Sebelumnya, 26 Oktober 2026, pengembang memilih model Plomp (2013).',
                    'list'  => [
                        'Branch menyebut ADDIE "a product development paradigm and not a model per se", iteratif dan nonlinier, dengan uji rintis di fase Develop.',
                        'Metode di dalam fase tidak berubah: ECD untuk merancang asesmen, lapis evaluasi formatif Tessmer (telaah ahli, satu-satu, kelompok kecil, uji lapangan) dan tabel metode Nieveen & Folmer untuk menguji prototipe.',
                        'Uji kelompok kecil dan menengah ditaruh di fase Develop; uji besar (pengambilan data utama) di fase Implement pada versi yang dikunci di gerbang G4.',
                        'Keluaran di luar alat ukur ditulis sebagai **rekomendasi pengembangan** asesmen berbasis game untuk anak.',
                    ],
                    'why' => [
                        'Peneliti memilih ADDIE karena setiap fase diuji, dan perubahan dari saran ahli atau hasil uji masuk kembali ke fase pengembangan sampai produknya benar-benar final.',
                        'Kekurangan ADDIE yang tetap benar, tidak ada kriteria mutu khusus untuk alat ukur, ditutup dengan empat kriteria mutu Plomp dan ECD.',
                        'Kritik bahwa fase Implement mencampur uji coba dengan pengambilan data utama diatasi dengan memisahkan keduanya: uji coba di Develop, data utama di Implement.',
                        'Model lain ditimbang dan tidak dipilih: 4D (difusi dan adopsi di luar rencana), Borg & Gall (uji lapangan berulang yang tidak dipakai), Sugiyono (berakhir produksi masal), MDLC dan GDLC (siklus produksi perangkat lunak, bukan desain penelitian).',
                    ],
                    'refs'    => [self::BRANCH, self::PLOMP, self::TESSMER, self::NIEVEEN],
                    'docs'    => [self::DMODEL . ' → Pembaruan 1 November 2026', self::D04 . ' → 1 November 2026'],
                    'sources' => [self::MODEL],
                ],
                [
                    'id'    => 'tahap-gerbang',
                    'title' => 'Enam tahap, lima gerbang, tiga prototipe',
                    'lead'  => 'Pekerjaan berjalan dalam enam tahap. Tahap berikutnya baru dimulai setelah [[gerbang]] terpenuhi.',
                    'table' => [['Fase ADDIE', 'Tahap GELITA', 'Prototipe', 'Metode evaluasi'], [
                        ['Analyze', 'Sebelum tahap 1', '—', 'Kajian pustaka, telaah butir dan media lama, profil sekolah (L8)'],
                        ['Design', '1, gerbang G1', 'Prototipe I (dokumen, mock-up)', 'Telaah pendahuluan oleh pengembang (screening)'],
                        ['Develop', '2, gerbang G2', 'Prototipe I, direvisi', 'Validasi sebelas ahli; uji keterbacaan dengan 4 + 1 anak'],
                        ['Develop', '3, gerbang G3', 'Prototipe II (instalasi uji)', 'Ahli media langkah 2 (walkthrough), uji perangkat, tes otomatis'],
                        ['Develop', '4, gerbang G4', 'Prototipe III', 'Berpikir lantang (5–8 anak), uji kelompok kecil (8–12), uji kelompok menengah (± 80); versi dikunci dan pra-registrasi OSF'],
                        ['Implement', '5, gerbang G5', 'Versi final', 'Pengambilan data utama: target 300 siswa'],
                        ['Evaluate', 'Setiap fase; 6', '—', 'Analisis Rasch, DIF, pola kesalahan, validitas kriteria'],
                    ]],
                    'why' => [
                        'Gerbang mencegah pekerjaan mahal dimulai di atas dasar yang belum disetujui: kegiatan dengan anak menunggu izin etik (G2), uji coba menunggu sistem jadi (G3), data utama menunggu versi yang dikunci (G4).',
                        'Nama [[prototipe]] I–III dipakai supaya setiap revisi tercatat pada versi yang jelas.',
                        'Pra-registrasi skema kesalahan dan rencana analisis di OSF sebelum data utama mencegah analisis disesuaikan dengan hasil.',
                        'Uji coba di sekolah berawalan `U` di wilayah Kedu, di luar sekolah utama, dan satu anak hanya ikut satu uji, supaya data uji tidak tercampur dengan data utama.',
                    ],
                    'refs'    => [self::TESSMER, self::NIEVEEN, self::NOSEK],
                    'docs'    => [self::D01 . ' → Peta jalan dan daftar kerja', '`docs/revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md` → Desain penelitian'],
                    'sources' => [self::R22 . 'desain-penelitian', self::MODEL],
                ],
                [
                    'id'    => 'kriteria-mutu',
                    'title' => 'Empat kriteria mutu',
                    'lead'  => 'Mutu produk dinilai dengan relevansi, konsistensi, kepraktisan, dan keefektifan (Plomp, 2013, hlm. 29), tidak bergantung pada model pengembangannya.',
                    'table' => [['Kriteria', 'Artinya untuk GELITA', 'Bukti yang direncanakan'], [
                        ['Relevansi', 'Butir mewakili konstruk D1–D3 dan konteks budaya Kedu', 'I-CVI, V Aiken, S-CVI/Ave dari ahli; kisi-kisi'],
                        ['Konsistensi', 'Rantai klaim–bukti–tugas ECD utuh; label disepakati ahli', 'Peta bukti, kamus data, κ berbobot pelabelan buta ≥ 0,70'],
                        ['Kepraktisan', 'Game berjalan di perangkat sekolah, sesi muat dalam waktu, siswa bermain tanpa bantuan isi soal', 'Uji perangkat, ahli media, log penyelesaian dan waktu aktif, lembar L1, L3, L4, bintang kritik dan saran'],
                        ['Keefektifan', 'Skor game mengukur dengan baik', 'Kecocokan Rasch (MNSQ 0,5–1,5), reliabilitas, peta Wright, DIF, korelasi dengan tes awal–akhir'],
                    ]],
                    'why' => [
                        'Keefektifan diartikan sebagai **mutu pengukuran**, bukan kenaikan hasil belajar, karena GELITA adalah alat ukur.',
                        'Uji beda tes awal–akhir dilaporkan sebagai temuan **eksploratif**: desain satu kelompok tanpa kontrol tidak bisa menunjukkan bahwa game menyebabkan perubahan (sejarah, kematangan, efek tes, regresi).',
                        'Karena guru tidak ikut penelitian, kepraktisan diambil dari log, lembar pengamatan, dan pendapat siswa.',
                    ],
                    'refs'    => [self::PLOMP, self::LINACRE, self::CAMPBELL],
                    'docs'    => [self::DMODEL . ' → bagian 8 dan 9'],
                    'sources' => [self::MODEL],
                ],
                [
                    'id'    => 'ecd',
                    'title' => 'Evidence-Centered Design',
                    'lead'  => 'Asesmen GELITA dirancang dengan [[ecd]]: klaim tentang literasi digital siswa → bukti yang mendukungnya → tugas yang menghasilkan bukti itu. Setiap analitik harus bisa ditelusuri ke minimal satu dimensi.',
                    'table' => [['Unsur ECD', 'Padanan di GELITA'], [
                        ['Model siswa', 'Literasi digital tiga dimensi D1–D3'],
                        ['Model bukti', 'Sepuluh analitik dan kamus data; model Rasch multidimensi between-item'],
                        ['Model tugas', 'Lima jenis tantangan'],
                        ['Model perakitan', 'Variasi a/b, buklet, butir jangkar, lima pertemuan'],
                        ['Model penyampaian', 'Layar game, token sesi, mode penelitian'],
                        ['Empat proses', 'Benar/salah dan bintang dihitung di server; kunci jawaban tidak pernah dikirim ke peramban'],
                    ]],
                    'why' => [
                        'ECD bukan model penelitian dan pengembangan, jadi ia butuh wadah; ADDIE memberi wadah itu tanpa bertabrakan.',
                        'Data yang tidak tersambung ke dimensi mana pun tidak dikumpulkan (masuk daftar log yang dihapus), sejalan dengan minimalisasi data anak.',
                        'Pemakaian ECD di dalam game punya preseden: stealth assessment menjadikan ECD salah satu unsur kuncinya.',
                    ],
                    'refs'    => [self::MISLEVY, self::ALMOND, self::HAERTEL, self::SHUTE11],
                    'docs'    => [self::D01 . ' → Kerangka: peta bukti', self::DMODEL . ' → bagian 5.4'],
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
                        'Label asal: `asal: pengembang`, `asal: peneliti`, `asal: peneliti + ahli`, `asal: komite etik`.',
                        'Status dokumen: draf pengembang → disetujui pengembang → disetujui peneliti → tervalidasi ahli → final.',
                    ],
                    'why' => [
                        'Kerja pengembang **bukan** validasi ahli: skor dan label pengembang tidak ikut dihitung dalam V Aiken, I-CVI, atau κ, supaya ahli menilai tanpa terpengaruh.',
                        '"Dibekukan" berarti versinya dicatat, bukan tidak boleh diubah. Perubahan sesudahnya tetap boleh asal dicatat dengan akibatnya: dinilai ulang ahli, amandemen ke komite etik, atau tidak perlu apa-apa.',
                        'Kerja pengembang yang lengkap mempercepat peneliti: ia menyetujui, menolak, atau merevisi bahan yang sudah siap, dan tetap bertanggung jawab atas setiap keputusan final.',
                    ],
                    'docs'    => [self::D01 . ' → Peran pengembang, peneliti, dan ahli', '`docs/revisi-10-analitik/00_INDEKS.md` → Label asal dan status'],
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
                    'lead'  => 'Yang diukur adalah [[literasi digital|literasi-digital]] siswa; Kedu adalah konteks budayanya. Literasi membaca dan pengetahuan budaya tidak lagi berdiri sebagai konstruk sendiri.',
                    'table' => [['Dimensi', 'Artinya', 'Diukur lewat', 'Acuan level'], [
                        ['D1 Literasi informasi', 'Menemukan, menafsirkan, dan menilai informasi dari teks dan gambar di layar, termasuk teks bergenre digital (D1a memahami, D1b menilai informasi dan sumber)', 'Isi Rumpang, Benar atau Salah, kartu jangkar', 'Level AKM, padanan PIRLS 2026'],
                        ['D2 Aman dan etis', 'Menjaga data pribadi, kata sandi, dan OTP; izin berbagi foto dan obrolan; menghadapi orang asing dan kabar bohong', 'Skenario Digital (tindakan diskor 0–1–2); metrik kata sandi saat daftar', 'Kerumitan skenario per wilayah'],
                        ['D3 Partisipasi dan representasi budaya Kedu di ruang digital', 'Atribusi, representasi akurat, izin pelaku dan pemilik karya, sikap menghargai tradisi', 'Cari Objek babak 2 Periksa Unggahan', 'Kerumitan adegan per wilayah'],
                    ]],
                    'why' => [
                        'Judul penelitian menyebut GELITA "model asesmen literasi digital", jadi literasi digital menjadi konstruk utama dan yang lain menjadi dimensinya (disetujui 25 Oktober 2026).',
                        'Empat pilar Komdigi dan Siberkreasi dipakai sebagai payung karena dikenal di Indonesia; DigComp 3.0 memberi padanan internasional.',
                        'Setiap butir punya tepat satu dimensi utama (model multidimensi between-item), supaya keterpisahan tiga dimensi bisa diuji dengan model Rasch.',
                    ],
                    'refs'    => [self::KOMINFO, self::DIGCOMP, self::PIRLS, self::ADAMS],
                    'docs'    => [self::D01 . ' → Judul penelitian dan konstruk', '`docs/revisi-10-analitik/05_TELAAH_BUTIR_DAN_DIMENSI.md`', '`docs/revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md`'],
                    'sources' => [self::R01 . 'judul-penelitian-dan-konstruk'],
                ],
                [
                    'id'    => 'sepuluh-analitik',
                    'title' => 'Sepuluh analitik',
                    'lead'  => 'Sepuluh analitik berasal dari peneliti. Revisi 10 memberi setiap analitik definisi yang bisa dihitung dari log atau lembar lapangan.',
                    'table' => [['No', 'Analitik', 'Dihitung dari'], [
                        ['1', 'Penyelesaian aktivitas, dengan tiga jenis "dilewati"', 'Log game, L1'],
                        ['2', 'Ketepatan jawaban per dimensi dan level AKM', 'Log game'],
                        ['3', 'Ketepatan [[percobaan pertama|percobaan-pertama]], data inti uji Rasch', 'Log game'],
                        ['4', 'Jumlah percobaan; "belum berhasil setelah 3" diperlakukan sebagai tersensor', 'Log game'],
                        ['5', 'Waktu aktif: waktu total dikurangi layar tersembunyi dan jeda panjang', 'Log game, L3'],
                        ['6', 'Pola kesalahan dari kode kesalahan', 'Log game, pengodean manual'],
                        ['7', 'Penggunaan bantuan: Bantuan, narasi, instruksi; bantuan orang lewat L3', 'Log game, L1, L3'],
                        ['8', 'Navigasi: layar, Pustaka, kembali ke bacaan', 'Log game'],
                        ['9', 'Perubahan setelah umpan balik: diperbaiki, kesalahan sama, kesalahan lain', 'Log game'],
                        ['10', 'Pilihan dan alasan di Skenario Digital', 'Log game, L5, metrik kata sandi'],
                    ]],
                    'why' => [
                        'Kolom asal "Informasi bagi guru" diganti "Makna bagi penelitian", karena guru tidak ikut dalam penelitian.',
                        'Butir yang belum berhasil setelah tiga kesempatan bukan angka 3 melainkan data tersensor; model kejadian waktu-diskret menangani sensor itu dengan benar.',
                        'Tebakan cepat ditandai dari waktu respons, karena jawaban yang terlalu cepat tidak mencerminkan kemampuan.',
                    ],
                    'refs'    => [self::SINGER, self::WISE, self::FREIRE],
                    'docs'    => [self::D01 . ' → 10 analitik versi revisi', '`docs/revisi-10-analitik/11_KAMUS_DATA.md` → Sepuluh analitik'],
                    'sources' => [self::R01 . '10-analitik-versi-revisi', self::R04 . 'titik-awal-dokumen-10-analitik-dari-pengembang'],
                ],
                [
                    'id'    => 'kode-kesalahan',
                    'title' => 'Skema kode kesalahan',
                    'lead'  => 'Setiap pilihan salah diberi satu [[kode kesalahan|kode-kesalahan]] **sebelum** data diambil, dan kode itu ikut tercatat di setiap percobaan yang salah.',
                    'table' => [['Kelompok', 'Dimensi', 'Contoh'], [
                        ['INF: salah memilih informasi', 'D1 (INF-BUD hanya untuk skala PBK)', '`INF-LIT` salah membaca informasi tersurat; `INF-SUM` salah menilai sumber; `INF-FOP` mencampur fakta dan pendapat'],
                        ['DIG: salah memilih tindakan digital', 'D2', '`DIG-PRI` membagikan data pribadi berlebihan; `DIG-ASG` menanggapi orang asing'],
                        ['BUD: salah dalam representasi budaya', 'D3', '`BUD-ATR` atribusi, `BUD-REP` representasi, `BUD-IZN` izin, `BUD-ETK` etika'],
                        ['NON: penanda tambahan', 'Semua', 'Tebakan cepat, jawaban terakhir saat kesempatan habis; dihitung sistem'],
                        ['R: alasan di Skenario Digital', 'D2 (diagnosis)', '`R-RISK`, `R-ETIK`, `R-ATUR`, `R-SOS`, `R-ACAK`'],
                    ]],
                    'why' => [
                        'Kode dipasang sebelum data diambil dan dibekukan setelah uji coba, supaya kategori tidak disesuaikan dengan hasil; skemanya ikut pra-registrasi di G4.',
                        'Kode diturunkan server dari pilihan siswa, sehingga tidak ada pengodean ulang yang bisa berbeda antarpeneliti; pengodean manual hanya untuk "LAIN" di kolom terpisah.',
                        'Ruang hasil (outcome space) berkode adalah salah satu blok bangunan pengukuran: dari kode itulah pola kesalahan dan skor 0–1–2 disusun.',
                    ],
                    'refs'    => [self::WILSON, self::NOSEK],
                    'docs'    => [self::D01 . ' → Skema kategori kesalahan', '`docs/revisi-10-analitik/03_KODE_DAN_ARTI.md` (arti setiap kode)'],
                    'sources' => [self::R01 . 'skema-kategori-kesalahan'],
                ],
                [
                    'id'    => 'pelevelan',
                    'title' => 'Pelevelan wilayah',
                    'lead'  => 'Urutan Temanggung (mudah), Magelang (sedang), Wonosobo (sulit) tetap. Bedanya, tingkat kesulitan sekarang harus dibuktikan dengan data, bukan hanya label Bloom.',
                    'table' => [['Wilayah', 'Level AKM (D1)', 'Padanan PIRLS 2026', 'Bloom (label kedua)', 'Skenario Digital (D2)'], [
                        ['Temanggung', 'Menemukan informasi', 'Mengambil informasi tersurat', 'C1–C3', 'Tanda bahaya jelas, 3 pilihan'],
                        ['Magelang', 'Interpretasi dan integrasi', 'Inferensi langsung; menafsirkan dan memadukan', 'C2–C4', 'Tanda bahaya samar, 4 pilihan'],
                        ['Wonosobo', 'Evaluasi dan refleksi', 'Mengevaluasi dan mengkritisi isi teks', 'C2–C5', 'Nilai bertentangan, dua langkah'],
                    ]],
                    'list' => [
                        'Dua ahli memberi label level AKM pada butir secara terpisah (pelabelan buta), dengan kesepakatan κ berbobot ≥ 0,70.',
                        'Butir jangkar menyambungkan variasi a dan b di satu skala.',
                        'Beban permukaan (jumlah rumpang, kata pengecoh, panjang bacaan, benda pengecoh) dicatat sebagai variabel sendiri.',
                        'Setelah data terkumpul, model Rasch memeriksa apakah urutan kesulitan butir mengikuti wilayah dan level.',
                    ],
                    'why' => [
                        'AKM adalah kerangka nasional literasi membaca, jadi hasilnya mudah dibaca pemangku kepentingan di Indonesia; PIRLS 2026 memberi padanan internasional untuk jurnal.',
                        'Bloom tetap ditulis sebagai label kedua; DOK Webb dan SOLO menjadi pembanding tingkat kognitif.',
                        'Urutan wilayah tidak bisa diacak karena mengikuti cerita. Ini ditulis sebagai keterbatasan, dengan waktu aktif kumulatif dan nomor pertemuan sebagai kovariat.',
                    ],
                    'refs'    => [self::PIRLS, self::BLOOM, self::WEBB, self::COHEN68, self::BOND],
                    'docs'    => [self::D01 . ' → Pelevelan wilayah', '`docs/revisi-10-analitik/19_TELAAH_LABEL_BLOOM.md`'],
                    'sources' => [self::R01 . 'pelevelan-wilayah'],
                ],
                [
                    'id'    => 'skala-pendamping',
                    'title' => 'Dua skala pendamping',
                    'lead'  => 'Dua hal diukur di samping literasi digital, tetapi **di luar** skornya.',
                    'table' => [['Skala', 'Isinya', 'Diukur lewat'], [
                        ['Pengetahuan Budaya Kedu (PBK)', 'Mengenali benda dan tradisi budaya Kedu', 'Cari Objek babak 1 Temukan Benda (butir bersama `x`)'],
                        ['Skala sikap menjaga warisan', 'Menghargai, ingin tahu, mau merawat, dan bangga pada warisan budaya Kedu (SK1–SK4)', '12 pernyataan dengan empat pilihan tanpa titik tengah, sekali di Balai Refleksi pada pertemuan 5'],
                    ]],
                    'why' => [
                        'Pengetahuan tentang benda budaya bukan literasi digital; memasukkannya ke skor akan mencampur konstruk. Karena itu PBK berdiri sendiri.',
                        'Sikap bukan pengetahuan dan bukan perilaku di ruang digital, jadi diukur dengan skala tersendiri, hanya di layar dan tidak dibacakan.',
                        'Skala sikap divalidasi dengan I-CVI aturan Lynn oleh empat ahli dan uji keterbacaan dengan anak; statusnya `tervalidasi ahli` sejak 4 November 2026.',
                    ],
                    'refs'    => [self::LYNN, self::GAY],
                    'docs'    => ['`docs/revisi-10-analitik/17_SKALA_SIKAP.md`', '`docs/revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md`'],
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
                    'lead'  => 'Pemain berperan sebagai Jaka, dibimbing Mbah Kedu, memulihkan 15 Serpihan Cahaya di tiga wilayah eks-Karesidenan Kedu.',
                    'steps' => [
                        'Halaman awal → pendaftaran → cerita pembuka → Peta Kedu.',
                        'Per wilayah: Kenali wilayah → dialog pembuka → lima tantangan berurutan → wilayah tuntas → Pustaka wilayah terbuka.',
                        'Setelah ketiga wilayah: cerita penutup → Balai Refleksi (skala sikap, kritik dan saran).',
                        'Sejak revisi 10, cerita penghubung membuka tantangan kelima (Skenario Digital) di setiap wilayah.',
                    ],
                    'why' => [
                        'Cerita memberi fantasi dan rasa ingin tahu, dua sumber motivasi intrinsik; tantangan yang naik bertahap menjaga anak di wilayah "flow".',
                        'Cerita tidak boleh membocorkan jawaban. Naskah lama diperiksa terhadap bank soal, dan cerita penghubung ditulis tanpa petunjuk isi.',
                        'Wilayah terbuka berurutan karena cerita mengikuti perjalanan Jaka; dalam mode penelitian token mengatur wilayah per pertemuan.',
                    ],
                    'refs'    => [self::MALONE, self::CSIKS, self::GEE],
                    'docs'    => ['`docs/naskah-cerita.md`', '`docs/revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md`'],
                    'sources' => ['revisi-10-analitik/12_CERITA_PENGHUBUNG_DAN_NARASI.md#aturan-menulis-cerita-untuk-penelitian'],
                ],
                [
                    'id'    => 'lima-tantangan',
                    'title' => 'Lima tantangan per wilayah',
                    'lead'  => 'Urutannya sama di ketiga wilayah; jumlahnya naik per tingkat kesulitan.',
                    'table' => [['No', 'Tantangan', 'Dimensi', 'Temanggung · Magelang · Wonosobo'], [
                        ['1', 'Susun Gambar', 'Tidak dinilai (pemanasan)', '3 × 3 · 4 × 4 · jigsaw 4 × 4'],
                        ['2', 'Isi Rumpang', 'D1', '4 · 5 · 6 rumpang, masing-masing + 2 pengecoh'],
                        ['3', 'Benar atau Salah', 'D1 (+ kartu jangkar)', '4 · 6 · 6 pernyataan + 2 jangkar'],
                        ['4', 'Cari Objek', 'Babak 1 PBK, babak 2 D3', 'Babak 1: 4 dicari + 4/6/8 pengecoh; babak 2 Periksa Unggahan: 4 petunjuk, 8/10/12 bagian ketuk'],
                        ['5', 'Skenario Digital', 'D2', '4 skenario; 3/4/4 tindakan; 3/4/5 alasan'],
                    ]],
                    'why' => [
                        'Setiap tantangan adalah satu "model tugas" ECD yang menghasilkan bukti untuk satu dimensi.',
                        'Susun Gambar tidak dinilai karena tidak mengukur literasi digital; ia menjadi pemanasan sekaligus pengenalan wilayah.',
                        'Area ketuk Cari Objek tidak boleh bertumpuk (jarak minimal setara 24 dp) dan diperiksa otomatis, supaya ketepatan jari tidak ikut terukur.',
                        'Kuis pilihan, rumpang ketik sendiri, dan benar–salah beralasan dihapus sejak perombakan kelas 5 atas permintaan peneliti.',
                    ],
                    'refs'    => [self::MISLEVY],
                    'docs'    => ['`docs/revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md` → Kisi-kisi per wilayah dan tantangan', '`docs/bank-soal/README.md`'],
                    'sources' => ['revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md#kisi-kisi-per-wilayah-dan-tantangan'],
                ],
                [
                    'id'    => 'tiga-kesempatan',
                    'title' => 'Tiga kesempatan, percobaan pertama yang dinilai',
                    'lead'  => 'Setiap soal paling banyak tiga percobaan, ditegakkan server.',
                    'list'  => [
                        'Susun Gambar, Isi Rumpang, Benar atau Salah: satu tekan **Cek jawabanku** = satu percobaan untuk semua soal yang belum benar.',
                        'Cari Objek: satu ketukan = satu percobaan untuk petunjuk yang aktif; ketukan salah ke-3 menutup petunjuk itu.',
                        'Skenario Digital: tiga kesempatan per skenario; setiap kesempatan berisi tindakan dan alasan; setelah kesempatan ketiga tindakan yang aman ditampilkan dan dijelaskan.',
                        'Bila kesempatan habis, jawaban terakhir disimpan apa adanya dan siswa tetap mendapat serpihan.',
                    ],
                    'why' => [
                        'Yang dinilai adalah [[percobaan pertama|percobaan-pertama]], karena hanya itu yang belum dipengaruhi umpan balik. Kesempatan berikutnya untuk belajar (analitik 9).',
                        'Umpan balik setelah percobaan salah adalah umpan balik formatif: memberi tahu apa yang perlu diperbaiki tanpa memberi jawabannya.',
                        'Batas tiga menjaga sesi tetap muat dalam waktu dan mencegah anak terjebak di satu soal.',
                        'Batas ditegakkan server dengan pembaruan bersyarat, sehingga dua tab atau kiriman ulang tidak bisa memberi kesempatan keempat.',
                    ],
                    'refs'    => [self::SHUTE08, self::HATTIE],
                    'docs'    => ['`docs/perombakan-kelas5/02_TANTANGAN_DAN_BANK_SOAL.md` → 5. Aturan 3 kesempatan', self::D01 . ' → Skenario Digital: tantangan kelima'],
                    'sources' => [self::K2 . '5-aturan-3-kesempatan'],
                ],
                [
                    'id'    => 'bintang-serpihan',
                    'title' => 'Bintang dan Serpihan Cahaya',
                    'lead'  => 'Setiap tantangan yang selesai memberi satu Serpihan Cahaya (15 seluruhnya) dan satu sampai tiga bintang.',
                    'list'  => [
                        'Bintang dihitung server dari jumlah percobaan (aturan `bintang-1`).',
                        'Serpihan tetap didapat walaupun kesempatan habis, supaya anak bisa melanjutkan cerita.',
                    ],
                    'why' => [
                        'Bintang dan serpihan adalah **hadiah**, bukan prestasi. Keduanya tidak dianalisis, karena dihitung dari jumlah percobaan yang sudah tercatat sendiri.',
                        'Skor 0–100 dan profil skor dihapus sejak perombakan kelas 5: angka di layar akan dibaca sebagai nilai, padahal pengukuran dilakukan di luar game.',
                    ],
                    'refs'    => [self::MALONE],
                    'docs'    => [self::D01 . ' → Log: dihapus atau dipisah', '`docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md` → 3. Keputusan yang dikunci (K6)'],
                    'sources' => [self::R01 . 'log-dihapus-atau-dipisah', self::K1 . '3-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'bantuan-narasi',
                    'title' => 'Bantuan, petunjuk, dan narasi',
                    'lead'  => 'Tombol 💡 **Bantuan** ada di setiap tantangan; **petunjuk** adalah kalimat yang dicari di Cari Objek. Narasi diatur ketat.',
                    'list'  => [
                        'Bacaan dan pernyataan D1 **tidak pernah dibacakan**.',
                        'Situasi Skenario Digital (D2) dan petunjuk Periksa Unggahan (D3) boleh dibacakan lewat tombol ▶, tidak diputar otomatis, dan pemutarannya tercatat.',
                        'Narasi instruksi punya kode konteks sendiri, supaya pemakaiannya bisa dipisahkan dari narasi cerita.',
                        'Setiap pembukaan Bantuan dan pemutaran narasi tercatat (analitik 7).',
                    ],
                    'why' => [
                        'D1 bertumpu pada kemampuan membaca. Bila bacaan dibacakan, yang terukur menjadi kemampuan menyimak.',
                        'D2 dan D3 tidak mengukur membaca, jadi narasi membantu anak yang lambat membaca tanpa mengubah konstruk; narasi tanpa putar otomatis menjaga beban kognitif tetap terkendali.',
                        'Nama "Bantuan" untuk tombol 💡 dan "petunjuk" untuk Cari Objek dipisah (21 Oktober 2026) supaya satu kata hanya punya satu arti.',
                    ],
                    'refs'    => [self::MAYER],
                    'docs'    => [self::D04 . ' → 25 Oktober 2026 (Keputusan pengembang: media)', '`docs/revisi-10-analitik/10_TELAAH_PENDAHULUAN_MEDIA.md`'],
                    'sources' => [self::R04 . '25-oktober-2026'],
                ],
                [
                    'id'    => 'pustaka-bahasa',
                    'title' => 'Pustaka Kedu, dua bahasa, dan suara',
                    'lead'  => 'Pustaka Kedu berisi 30 halaman bacaan dwibahasa, terbuka per wilayah setelah wilayahnya tuntas. Seluruh area game tersedia dalam bahasa Indonesia dan Inggris; panel staf hanya berbahasa Indonesia.',
                    'list'  => [
                        'Isi Pustaka tidak diubah oleh perombakan kelas 5 maupun reset, atas permintaan peneliti.',
                        'Ganti bahasa dicatat sebagai kovariat (`locale_changed`).',
                        'Narasi: pengisi suara tetap pilihan pertama; suara sintetis dipakai sebagai cadangan sejak 15 November 2026 karena pengisi suara belum tersedia, disetujui peneliti, dan tercatat sebagai suara sintetis di data.',
                        'Gambar, suara, dan musik dijelaskan di [Katalog Aset GELITA](' . self::CATALOGUE . ').',
                    ],
                    'why' => [
                        'Pustaka dibuka **setelah** wilayah tuntas supaya bacaan tambahan tidak menjadi sumber jawaban saat tantangan berlangsung.',
                        'Rekaman dicatat menurut cara pembuatannya, karena suara asli dan sintetis bisa memengaruhi cara anak mendengar; kekeliruan pencatatan akan menjadi kekeliruan di data penelitian.',
                        'Musik latar hanya hiasan dan bukan bagian penelitian.',
                    ],
                    'docs'    => ['`docs/pustaka/README.md`', self::D04 . ' → 15 November 2026', '`docs/revisi-10-analitik/36_TERJEMAHAN_BANK_SOAL.md`'],
                    'sources' => [self::R04 . '15-november-2026', self::K1 . '2-yang-tidak-boleh-berubah'],
                ],
                [
                    'id'    => 'variasi-buklet',
                    'title' => 'Variasi, butir jangkar, dan buklet',
                    'lead'  => 'Setiap tantangan yang dinilai punya dua variasi, `a` dan `b`, ditambah butir bersama `x`. Pengerjaan pertama mengikuti [[buklet]] siswa (B1–B4).',
                    'list'  => [
                        'Butir jangkar: 6 pernyataan D1 (2 per wilayah) di kartu jangkar yang sama untuk variasi a dan b.',
                        'Cari Objek babak 1 memakai butir bersama `x` untuk semua siswa.',
                        'Buklet dihitung panel dari baris dan kursi di denah kelas, dan melekat pada siswa sampai pertemuan terakhir.',
                        'Di luar mode penelitian satu variasi dipilih acak; pengerjaan ulang memakai variasi lain bila ada.',
                        'Per siswa di pengerjaan pertama: 61 butir literasi digital (37 D1, 12 D2, 12 D3) ditambah 12 butir PBK.',
                    ],
                    'why' => [
                        'Dua variasi mengurangi saling mencontek antartetangga; buklet per kursi menjamin tetangga mendapat susunan berbeda.',
                        'Butir jangkar menyambungkan kedua variasi ke satu skala Rasch. Rencana awal (butir yang diulang di tiga wilayah) diganti karena siswa yang sama memainkan ketiga wilayah.',
                        'Dua variasi (bukan tiga) sudah cukup untuk tujuan itu dan mengurangi butir yang harus ditulis dan divalidasi.',
                        'Kesetaraan variasi a dan b diuji dengan DIF setelah data terkumpul.',
                    ],
                    'refs'    => [self::BOND, self::ADAMS],
                    'docs'    => ['`docs/revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md` → Jumlah per siswa dan jumlah yang harus ditulis', '`docs/revisi-10-analitik/31_IMPLEMENTASI_BANK_SOAL_BARU.md`'],
                    'sources' => ['revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md#jumlah-per-siswa-dan-jumlah-yang-harus-ditulis'],
                ],
                [
                    'id'    => 'skenario-digital',
                    'title' => 'Skenario Digital',
                    'lead'  => 'Tantangan kelima di setiap wilayah, untuk dimensi D2: situasi pendek berlatar Kedu dengan sudut pandang "kamu", lalu **pilih tindakan**, lalu **pilih alasan**.',
                    'table' => [['Topik (pilar)', 'Temanggung', 'Magelang', 'Wonosobo'], [
                        ['Data pribadi (Aman)', 'Tanda bahaya jelas', 'Tanda bahaya samar', 'Ada hadiah yang menggoda'],
                        ['Kata sandi dan OTP (Aman)', 'Orang asing yang minta', 'Teman dekat yang minta', 'Permintaan tampak resmi'],
                        ['Izin membagikan foto atau obrolan (Etis)', 'Foto diri sendiri', 'Foto teman', 'Tangkapan layar obrolan teman'],
                        ['Pesan orang asing dan kabar bohong (Aman, Cakap)', 'Jelas mencurigakan', 'Pesan berantai dari grup', 'Berkembang dua langkah'],
                    ]],
                    'list' => [
                        'Satu tindakan tepat (skor 2), satu kurang tepat (1), sisanya berisiko (0); setiap pilihan punya kode kesalahan.',
                        'Alasan ditanyakan apa pun tindakannya; alasan hanya data diagnosis dan tidak masuk skor utama di versi pertama.',
                        'Umpan balik ramah tentang akibat pilihan, tanpa kata "salah" atau "gagal". Skenario tidak menakut-nakuti dan tidak memuat tautan sungguhan.',
                        'Semua unggahan tampil di aplikasi media sosial fiktif **Kedugram**; nama akun dicek tidak sama dengan akun nyata.',
                    ],
                    'why' => [
                        '**Topik sama di tiap wilayah, kerumitannya yang naik**, supaya topik tidak tercampur dengan tingkat kesulitan.',
                        'Alasan ditanyakan terpisah supaya pola "tindakannya benar, alasannya keliru" kelihatan.',
                        'Izin atas karya budaya dipindah ke D3 (Periksa Unggahan), jadi topik izin di D2 cukup foto dan obrolan diri sendiri atau teman.',
                        'Latar dicek fakta: contoh awal memakai Dieng Culture Festival, padahal festival itu di Banjarnegara, jadi diganti Telaga Warna di Wonosobo.',
                    ],
                    'refs'    => [self::KOMINFO, self::DIGCOMP, self::WILSON],
                    'docs'    => [self::D01 . ' → Skenario Digital: tantangan kelima', '`docs/revisi-10-analitik/08_DRAF_BANK_SOAL.md`', '`docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md`'],
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
            'lead'     => 'Tiga dokumen yang membersihkan dan menyusun ulang GELITA pada Oktober 2026, dan apa yang kemudian diubah lagi oleh revisi 10.',
            'sections' => [
                [
                    'id'    => 'dok-1-3',
                    'title' => 'Tiga dokumen perombakan',
                    'lead'  => 'Perombakan dikerjakan berurutan Dok 1 → Dok 2 → Dok 3, dan aplikasi tetap bisa dijalankan di akhir setiap dokumen.',
                    'table' => [['Dokumen', 'Isinya'], [
                        ['Dok 1 Fondasi data dan reset', 'Skema baru, perintah reset total, satu tahap bermain, pendaftaran tiga langkah, catatan perangkat, data mentah'],
                        ['Dok 2 Tantangan dan bank soal', 'Empat tantangan, aturan tiga kesempatan, variasi acak, bank soal kelas 5, gambar contoh'],
                        ['Dok 3 Tampilan 16:9 dan admin', 'Panggung 16:9 tetap, tantangan lebar penuh, panel admin bergaya dasbor, ekspor data mentah, rilis'],
                    ]],
                    'list' => [
                        'Fokus hanya kelas 5; sisa uji coba dibersihkan dengan `gelita:reset`.',
                        'Pustaka, Kenali wilayah, dan sebagian besar cerita tidak boleh berubah.',
                        '"Tepat sejak awal" menjadi istilah **Berhasil pada Percobaan Pertama**.',
                        'Analitik di panel menjadi data mentah: halaman indeks kesulitan, daya pembeda, dan grafik dihapus.',
                    ],
                    'why' => [
                        'Penelitian belum mulai dan tidak ada data siswa asli, jadi skema boleh diubah tanpa membawa data lama.',
                        'Analitik olahan di panel dihapus karena pengolahan data dikerjakan peneliti dengan perangkat lunak statistik, bukan di game.',
                        'Satu dokumen per bagian dengan daftar uji dan kriteria selesai menjaga aplikasi tetap berjalan sepanjang perombakan.',
                    ],
                    'docs'    => ['`docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md` → 1. Permintaan → keputusan → tempat pengerjaan', '`docs/arsip/CATATAN_IMPLEMENTASI-2026-10.md`'],
                    'sources' => [self::K1 . '1-permintaan--keputusan--tempat-pengerjaan', self::K1 . '0-cara-memakai-ketiga-dokumen'],
                ],
                [
                    'id'    => 'keputusan-kelas5',
                    'title' => 'Keputusan yang kemudian berubah',
                    'lead'  => 'Beberapa keputusan perombakan kelas 5 diubah lagi oleh revisi 10. Riwayat ini menjelaskan mengapa dokumen lama kadang berbeda dengan game sekarang.',
                    'table' => [['Perombakan kelas 5 (Oktober)', 'Revisi 10 (sejak 23 Oktober)', 'Mengapa berubah'], [
                        ['Empat tantangan, 12 serpihan', 'Lima tantangan, 15 serpihan', 'Literasi digital wajib ada; Skenario Digital mengukur D2'],
                        ['Tiga variasi a/b/c, dipilih acak', 'Dua variasi a/b + butir bersama x, dari buklet', 'Desain buklet dan butir jangkar untuk skala Rasch'],
                        ['Tanpa tes awal–akhir, satu tahap', 'Modul tes awal–akhir terpisah dari game', 'Bukti validitas kriteria'],
                        ['Izin orang tua diurus guru luring; satu centang siswa', 'Izin berkode per siswa, status mundur, permintaan hapus', 'Protokol izin etik dan pelindungan data pribadi'],
                        ['Peran `guru` melihat sekolahnya', 'Peran `guru` dihapus; `peneliti` dan `ketua`', 'Guru tidak ikut penelitian; pemisahan identitas dan data'],
                        ['Pemilihan soal selalu acak', 'Pengerjaan pertama dari buklet; acak hanya di luar mode penelitian', 'Tetangga mendapat susunan berbeda dan seimbang per rombel'],
                    ]],
                    'why' => [
                        'Tidak ada fitur yang wajib dipertahankan (keputusan 23 Oktober 2026). Batasnya hanya kebutuhan penelitian: kunci jawaban di server, kode butir yang sudah punya data siswa tidak dipakai ulang, data mentah tetap lengkap.',
                    ],
                    'docs'    => ['`docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md` → 3. Keputusan yang dikunci', self::D01 . ' → Keputusan yang sudah diambil'],
                    'sources' => [self::K1 . '3-keputusan-yang-dikunci', self::R01 . 'keputusan-yang-sudah-diambil'],
                ],
                [
                    'id'    => 'panggung-16x9',
                    'title' => 'Panggung 16:9 untuk Chromebook',
                    'lead'  => 'Seluruh layar game digambar di satu panggung logis 1280 × 720 dp yang diperbesar atau diperkecil utuh mengikuti layar, dengan bingkai gelap di sisanya.',
                    'list'  => [
                        'Tidak ada tata letak responsif di dalam panggung; posisi gambar, benda Cari Objek, dan panel sama di semua perangkat.',
                        'Halaman tidak pernah bergulir; isi panjang bergulir di dalam panelnya.',
                        'Perangkat sentuh yang dipegang tegak melihat layar "Putar perangkatmu".',
                        'Pada Chromebook 1280 × 800, panggung tampil tepat 1280 × 720 dengan 1 rem = 16 px.',
                        'Panel staf tidak memakai panggung: tabel data butuh lebar layar penuh.',
                    ],
                    'why' => [
                        'Posisi visual yang tetap berarti semua anak melihat soal yang sama dengan tata letak yang sama; perbedaan tata letak antarperangkat akan menjadi sumber variasi skor.',
                        'Chromebook dan laptop sekolah adalah perangkat sasaran, jadi ukuran dan letak diatur untuk siswa kelas 5 di layar itu.',
                        'Pengambilan data tidak memakai ponsel: di ponsel sebagian besar teks tampil di bawah 12 px, sehingga skor membaca ikut bergantung pada perangkat. Dalam mode penelitian layar di bawah 960 px ditolak.',
                    ],
                    'refs'    => [self::NIELSEN],
                    'docs'    => ['`docs/perombakan-kelas5/03_TAMPILAN_16x9_DAN_ADMIN.md` → 1. Prinsip panggung 16:9', '`docs/05_VIEW_UI.md`', '`docs/revisi-10-analitik/10_TELAAH_PENDAHULUAN_MEDIA.md`'],
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
            'lead'     => 'Dokumen revisi, bank soal, tes dan angket, validasi ahli, izin etik, dan pelindungan data pribadi.',
            'sections' => [
                [
                    'id'    => 'dokumen-revisi',
                    'title' => 'Cara dokumen revisi bekerja',
                    'lead'  => 'Revisi 10 berupa dokumen bernomor di `docs/revisi-10-analitik/`, dengan indeks sebagai pintu masuk.',
                    'table' => [['Nomor', 'Kelompok'], [
                        ['00–04', 'Indeks, dokumen utama, lembar lapangan, arti kode, catatan keputusan'],
                        ['05–19', 'Desain instrumen: telaah butir, kisi-kisi, instrumen validasi, bank soal, kamus data, cerita, tes, angket, skala sikap, mock-up, label Bloom'],
                        ['20–26', 'Validasi dan etik: paket validasi, berkas validator, izin etik, surat persetujuan, uji keterbacaan, cek fakta budaya, penilaian dampak pelindungan data'],
                        ['27–35', 'Dokumen implementasi bagian 1–9'],
                        ['36–39', 'Terjemahan bank soal dan prompt gambar'],
                    ]],
                    'why' => [
                        'Satu dokumen per keputusan besar memudahkan validator, komite, dan sesi kerja membaca hanya bagian yang mereka perlukan.',
                        'Catatan keputusan ditulis terbaru di atas dengan asal dan status, supaya setiap perubahan bisa ditelusuri.',
                        'Setiap dokumen punya versi PDF siap cetak untuk diserahkan ke peneliti dan komite.',
                    ],
                    'docs'    => ['`docs/revisi-10-analitik/00_INDEKS.md` → Daftar dokumen', '`docs/revisi-10-analitik/status-dokumen.html`'],
                    'sources' => ['revisi-10-analitik/00_INDEKS.md#mulai-sesi-baru-di-sini'],
                ],
                [
                    'id'    => 'bank-soal',
                    'title' => 'Kisi-kisi dan bank soal',
                    'lead'  => 'Kisi-kisi (06) disetujui 25 Oktober 2026; bank soal (08) berisi 116 butir literasi digital dalam variasi a/b, 6 butir jangkar, dan 12 butir PBK, `disetujui peneliti` sejak 16 November 2026.',
                    'list'  => [
                        'Beberapa bacaan diubah menjadi **teks bergenre digital** (unggahan, pengumuman, obrolan).',
                        'Setiap butir punya label dimensi, sub-aspek, level AKM, dan Bloom, dengan asal labelnya (pengembang atau ahli).',
                        'Telaah pendahuluan pengembang menemukan dua pola sistematis (pilihan tepat dan alasan yang diharapkan selalu paling panjang) dan membetulkannya sebelum validasi.',
                        'Fakta budaya diperiksa: 135 klaim dicek dengan sumbernya, dan pengamat budaya menilai 44 fakta.',
                        'Isi bank disimpan di workbook dan diimpor ke database, lalu diterjemahkan ke bahasa Inggris.',
                    ],
                    'why' => [
                        'Kisi-kisi ditetapkan sebelum butir ditulis, supaya jumlah butir per dimensi dan level sesuai rencana analisis Rasch.',
                        'Pola "jawaban benar paling panjang" bisa ditebak tanpa membaca; membetulkannya menjaga butir mengukur kemampuan, bukan kecerdikan menebak.',
                        'Nama fiktif dicek di web dan diganti bila mirip kelompok nyata, supaya soal tidak menyinggung atau mengiklankan pihak tertentu.',
                    ],
                    'refs'    => [self::WILSON, self::BOND],
                    'docs'    => ['`docs/revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md`', '`docs/revisi-10-analitik/08_DRAF_BANK_SOAL.md`', '`docs/revisi-10-analitik/09_TELAAH_PENDAHULUAN_BANK_SOAL.md`', '`docs/revisi-10-analitik/25_CEK_FAKTA_BUDAYA.md`', '`docs/bank-soal/gelita-bank-soal-kelas5.xlsx`'],
                    'sources' => ['revisi-10-analitik/06_KISI_KISI_BANK_SOAL.md#ringkasan', self::R04 . '25-oktober-2026'],
                ],
                [
                    'id'    => 'tes-angket',
                    'title' => 'Tes awal–akhir dan angket',
                    'lead'  => 'Tes awal (pertemuan 1) dan tes akhir (pertemuan 5) punya tiga bagian sejajar dengan tiga dimensi: Membaca (16 butir), Aman dan etis (8 skenario), Budaya digital (8 butir). Bentuk A dan B setara butir demi butir.',
                    'list'  => [
                        'Bagian membaca diadaptasi dari contoh soal resmi AKM; bagian D2 dan D3 ditulis sendiri.',
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
                    'docs'    => ['`docs/revisi-10-analitik/13_TES_AWAL_AKHIR.md`', '`docs/revisi-10-analitik/14_TES_BAGIAN_MEMBACA.md`', '`docs/revisi-10-analitik/15_TES_BAGIAN_DIGITAL.md`', '`docs/revisi-10-analitik/16_ANGKET_AKSES_DIGITAL.md`'],
                    'sources' => ['revisi-10-analitik/13_TES_AWAL_AKHIR.md#ringkasan'],
                ],
                [
                    'id'    => 'validasi',
                    'title' => 'Validasi ahli dan uji keterbacaan',
                    'lead'  => 'Sebelas validator dengan nama samaran: lima ahli materi (Ahli Bahasa Anak 1–2, Ahli Literasi Digital 1–2, Ahli Pengukuran), tiga pengamat budaya (Ahli Budaya Kedu 1–3), dan tiga ahli media (Ahli Media Pembelajaran 1–3).',
                    'table' => [['Tujuan', 'Cara', 'Kriteria'], [
                        ['Validitas isi', 'Penilaian relevansi skala 1–4', 'V Aiken; I-CVI aturan Lynn; S-CVI/Ave ≥ 0,90'],
                        ['Kesepakatan label', 'Pelabelan buta B.5a oleh dua ahli', 'κ berbobot ≥ 0,70'],
                        ['Mutu media', 'Heuristik dengan skala keparahan 0–4, dua langkah', 'Rerata skor dan aturan veto'],
                        ['Keterbacaan', 'Empat anak kelas 5 ditambah satu di putaran kedua', 'Kode temuan KB-* dan aturan revisi'],
                    ]],
                    'why' => [
                        '**Aturan Lynn**: dengan lima ahli atau kurang, butir lolos hanya bila semua setuju (I-CVI = 1,00). Aturan Polit dkk. (2007) yang lebih longgar tidak dipakai, supaya butir yang meragukan direvisi.',
                        'Pelabelan buta dikerjakan lebih dulu, sebelum ahli melihat label pengembang, supaya kesepakatannya jujur. Pelabelan hanya sekali, oleh dua ahli: anggota tim yang sudah melihat label tidak bisa melabel secara buta.',
                        'Penilaian ulang tanpa batas putaran sampai butir lolos, mengikuti sifat iteratif ADDIE.',
                        'Ahli media menilai dua kali: langkah 1 pada game lama dan mock-up layar baru, langkah 2 pada layar jadi di instalasi uji sebelum uji coba.',
                        'Uji keterbacaan dengan anak di luar sampel memastikan kalimat soal dipahami anak kelas 5 sebelum dipakai di kelas.',
                    ],
                    'refs'    => [self::AIKEN, self::LYNN, self::POLIT06, self::POLIT07, self::COHEN60, self::COHEN68, self::NIELSEN],
                    'docs'    => ['`docs/revisi-10-analitik/07_TELAAH_INSTRUMEN_VALIDASI.md`', '`docs/revisi-10-analitik/20_PAKET_VALIDASI.md`', '`docs/revisi-10-analitik/24_UJI_KETERBACAAN.md`', 'Langkah kerja validator: [Panduan validator](/admin/panduan-validator)'],
                    'sources' => ['revisi-10-analitik/20_PAKET_VALIDASI.md#ringkasan', 'revisi-10-analitik/24_UJI_KETERBACAAN.md#ringkasan', self::MODEL],
                ],
                [
                    'id'    => 'etik',
                    'title' => 'Izin etik',
                    'lead'  => 'Permohonan izin etik memakai panduan klirens etik bidang sosial humaniora BRIN sebagai pedoman utama, dengan PSEPPKN 2021 sebagai cadangan. Izin disetujui 4 November 2026 (gerbang G2).',
                    'table' => [['Kegiatan', 'Dengan anak', 'Kapan'], [
                        ['KG1 Uji keterbacaan', '4 + 1 anak di luar sampel', 'Tahap 2'],
                        ['KG2 Berpikir lantang', '5–8 anak sekolah uji coba', 'Tahap 4'],
                        ['KG3a Uji kelompok kecil', '8–12 anak, lima pertemuan', 'Tahap 4'],
                        ['KG3b Uji kelompok menengah', '± 80 siswa', 'Tahap 4, sebelum G4'],
                        ['KG4 Wawancara retrospektif', '3 siswa per pertemuan wilayah', 'Bersama KG3a, KG3b, KG5'],
                        ['KG5 Pengambilan data utama', '± 360 direkrut, target 300', 'Setelah G4'],
                    ]],
                    'list' => [
                        'Lapis persetujuan: surat dan formulir orang tua dengan centang terpisah untuk rekaman suara dan butir risiko daring, persetujuan lisan anak di pertemuan 1, centang di halaman daftar, pengingat di setiap pertemuan.',
                        'Anak tanpa izin tidak dibuatkan akun dan ikut kegiatan pengganti di ruang yang sama.',
                        'Anak bisa mundur kapan saja dengan tiga pilihan: data tetap dipakai, sebagian dihapus, atau semua dihapus.',
                    ],
                    'why' => [
                        'Penelitian ini penelitian pendidikan dan sosial, bukan kesehatan, jadi panduan BRIN bidang sosial humaniora yang paling cocok.',
                        'Pesertanya anak (subjek rentan), sehingga izin orang tua, persetujuan anak, sesi berjeda, dan prosedur rujukan butir risiko daring dirancang tersendiri.',
                        'Satu anak hanya ikut satu kegiatan, supaya bebannya terbagi dan data uji tidak tercampur dengan data utama.',
                    ],
                    'refs'    => [self::BRIN, self::PSEPPKN],
                    'docs'    => ['`docs/revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md`', '`docs/revisi-10-analitik/23_SURAT_INFORMASI_DAN_PERSETUJUAN.md`'],
                    'sources' => [self::R22 . 'pedoman-yang-dirujuk', self::R22 . 'kegiatan-yang-dimintakan-izin'],
                ],
                [
                    'id'    => 'pelindungan-data',
                    'title' => 'Pelindungan data pribadi',
                    'lead'  => 'Penilaian dampak pelindungan data pribadi (lampiran izin etik) menilai 14 risiko dan menetapkan 18 langkah perlindungan.',
                    'list'  => [
                        '**Kode peserta** menggantikan nama di semua data penelitian; nama hanya di akun (dikelola admin) dan di lembar izin bernama.',
                        '**Peran dipisah**: admin memegang identitas, peneliti melihat data satu siswa lewat kodenya, hanya ketua peneliti yang mengunduh data penelitian.',
                        'Server, cadangan, dan penyimpanan di Indonesia; salinan dienkripsi; kunci jawaban tidak dikirim ke peramban.',
                        'Butir risiko daring hanya angka gabungan; penanda tindak lanjut hanya terlihat ketua peneliti dan tidak pernah diunduh.',
                        'Permintaan orang tua dan pemberitahuan kebocoran dalam 3 × 24 jam.',
                        'Saat studi ditutup nama dan sekolah di akun diganti kode; data berkode disimpan paling singkat 20 tahun.',
                        'Log dan rincian perangkat yang tidak dipakai sepuluh analitik **dihapus**, bukan hanya disembunyikan.',
                    ],
                    'why' => [
                        'Data anak termasuk data pribadi spesifik, dan tes serta skor permainan adalah evaluasi sistematis: dua pemicu yang mewajibkan penilaian dampak.',
                        'Minimalisasi data: yang tidak menjawab pertanyaan penelitian tidak boleh dikumpulkan, karena setiap data tambahan menambah risiko identifikasi.',
                        'Risiko sisa terbesar (kebocoran nama bersama jawaban, pengenalan kembali dari gabungan data) tetap sedang, karena tidak ada sistem daring yang sepenuhnya bebas kebocoran; dinilai dapat diterima dengan tinjauan tahunan.',
                    ],
                    'refs'    => [self::UU_PDP, self::PP_PDP, self::UU_SISNAS],
                    'docs'    => ['`docs/revisi-10-analitik/26_PENILAIAN_DAMPAK_PELINDUNGAN_DATA.md`', '`docs/revisi-10-analitik/30_IMPLEMENTASI_PERSETUJUAN_DAN_HAK_DATA.md`'],
                    'sources' => [self::R26 . 'ringkasan-untuk-komite', self::R26 . 'langkah-perlindungan'],
                ],
                [
                    'id'    => 'peran-data',
                    'title' => 'Admin, peneliti, dan ketua peneliti',
                    'lead'  => 'Tiga peran staf dengan hak yang berbeda. Peran `guru` dihapus seluruhnya.',
                    'table' => [['Peran', 'Memegang', 'Tidak boleh'], [
                        ['Admin', 'Sistem, akun, identitas, konten, rilis, log teknis', 'Melihat atau mengunduh data penelitian'],
                        ['Peneliti (P1–P3)', 'Sesi lapangan, token, pemantauan, data satu siswa lewat kodenya, atur ulang kata sandi lewat kode', 'Melihat nama siswa; mengunduh data'],
                        ['Ketua peneliti', 'Semua hak peneliti, unduhan data, data turunan, pengaturan studi, konfirmasi permintaan data', 'Melihat nama siswa di sistem'],
                    ]],
                    'why' => [
                        'Admin menjaga sistem dan identitas, peneliti memegang data yang sudah disamarkan. Pemisahan ini membuat tidak ada satu akun pun yang melihat nama bersama jawaban.',
                        'Satu orang boleh memegang dua peran, tetapi lewat dua akun terpisah, supaya jejak audit jelas.',
                        'Laporan untuk guru di luar lingkup penelitian; bila dibuat nanti, perannya dibangun ulang dengan hak baru.',
                    ],
                    'docs'    => [self::D01 . ' → Admin dan peneliti: dua peran berbeda', '`docs/revisi-10-analitik/27_IMPLEMENTASI_PERAN_PENELITI.md`', 'Langkah per peran: [Panduan staf](/admin/panduan-staf)'],
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
            'lead'     => 'Sesi lapangan dan token, mode penelitian, modul tes, log, lapis data turunan, dan kamus data.',
            'sections' => [
                [
                    'id'    => 'sesi-lapangan',
                    'title' => 'Sesi lapangan, token, dan kode peserta',
                    'lead'  => 'Dalam mode penelitian, siswa hanya bermain di sesi yang diamati peneliti, dengan token enam huruf dan angka yang ditulis di papan tulis.',
                    'table' => [['Pertemuan', 'Isinya'], [
                        ['P1', 'Pendaftaran, tes awal, cerita pembuka'],
                        ['P2', 'Temanggung'],
                        ['P3', 'Magelang'],
                        ['P4', 'Wonosobo'],
                        ['P5', 'Tantangan yang tertinggal, cerita penutup, tes akhir, skala sikap'],
                    ]],
                    'list' => [
                        'Kode peserta seperti `S03-A07`: kode sekolah penelitian, huruf rombel, nomor urut. Sekolah uji coba berawalan `U`.',
                        'Di luar sesi, seluruh game tertutup sampai penelitian selesai, dengan pesan ramah.',
                        'Lembar lapangan L0–L8 diisi peneliti, dengan kunci yang sama dengan log: kode peserta dan jam WIB.',
                        'Tiga peneliti per kelas: koordinator sesi (P1), pengamat keterlibatan (P2), pengamat bantuan (P3).',
                        'Langkah hari lapangan ada di [Panduan staf](/admin/panduan-staf).',
                    ],
                    'why' => [
                        'Data hanya bersih bila siswa bermain di sesi yang diamati: tanpa bantuan orang tua atau kakak di rumah yang tidak tercatat.',
                        'Token membawa sekolah dan rombel, sehingga siswa tidak perlu memilih sekolah, dan setiap sesi game tersambung otomatis ke lembar lapangannya.',
                        'Token mengatur wilayah per pertemuan supaya laju bermain antarkelas setara. Versi ringan, bukan sistem ujian seperti ANBK: tidak ada aplikasi khusus atau token yang berganti tiap 15 menit.',
                        'Kode sekolah dibuat peneliti, bukan NPSN, supaya kode tetap samaran dan pendek. Rombel juga menjadi satu tingkat dalam model campuran.',
                        'Lembar lapangan mengisi yang tidak terlihat log: siapa yang dibantu, perangkat yang dipakai bersama, kondisi kelas. Pengamatan keterlibatan mengadaptasi BROMP.',
                    ],
                    'refs'    => [self::BROMP, self::ERICSSON],
                    'docs'    => [self::D01 . ' → Token sesi: perlu atau tidak?', self::D01 . ' → Kerja lapangan: cara kerjanya', '`docs/revisi-10-analitik/02_LEMBAR_LAPANGAN.md`', '`docs/revisi-10-analitik/28_IMPLEMENTASI_SESI_LAPANGAN.md`'],
                    'sources' => [self::R01 . 'token-sesi-perlu-atau-tidak', self::R01 . 'kode-peserta-baru', self::R01 . 'kerja-lapangan-cara-kerjanya'],
                ],
                [
                    'id'    => 'mode-penelitian',
                    'title' => 'Mode penelitian',
                    'lead'  => 'Semua aturan pengambilan data ada di balik [[mode penelitian|mode-penelitian]], sakelar per studi yang mati secara bawaan.',
                    'table' => [['Aturan', 'Mengapa'], [
                        ['Jam disembunyikan; waktu tetap direkam server', 'Jam di layar membuat anak terburu-buru dan mengubah perilaku yang diukur'],
                        ['Layar di bawah 960 px ditolak dengan pesan ramah', 'Teks terlalu kecil membuat skor membaca bergantung pada perangkat'],
                        ['Pengerjaan pertama yang terputus dilanjutkan di sesi berikutnya, dengan potongan pengerjaan', 'Pengerjaan pertama harus satu; membuat yang baru akan menghapus percobaan pertama'],
                        ['Pengerjaan ulang baru terbuka setelah kelima tantangan wilayah tuntas', 'Mencegah anak mengulang sebelum percobaan pertamanya lengkap'],
                        ['Jeda masukan ≥ 20 detik dicatat', 'Bahan mentah untuk menghitung waktu aktif'],
                        ['Cek suara sekali per sesi game', 'Memastikan headset berfungsi sebelum narasi dipakai'],
                    ]],
                    'why' => [
                        'Sakelar per studi membuat kode baru bisa dibangun sekarang tanpa mengubah perilaku game biasa, dan studi uji coba bisa dibedakan dari studi utama.',
                        'Ambang (jeda, tebakan cepat, lebar layar minimum) disimpan sebagai kolom studi, supaya bisa ditetapkan ulang setelah uji coba tanpa mengubah kode, lalu dikunci di G4.',
                    ],
                    'docs'    => ['`docs/revisi-10-analitik/33_IMPLEMENTASI_MODE_PENELITIAN.md`', self::D04 . ' → 25 Oktober 2026'],
                    'sources' => ['revisi-10-analitik/33_IMPLEMENTASI_MODE_PENELITIAN.md#2-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'modul-tes',
                    'title' => 'Modul tes',
                    'lead'  => 'Tes awal, tes akhir, angket, dan butir risiko daring dikerjakan di modul tes GELITA (`/tes`), dan skala sikap di Balai Refleksi.',
                    'list'  => [
                        'Tampilan Bootstrap sederhana, tidak seperti game, tanpa hiasan permainan.',
                        'Jawaban tersimpan langsung di server per butir; skor dihitung server dari kunci yang tidak pernah sampai ke halaman.',
                        'Batas waktu tersembunyi per blok, dengan tambahan menit dari halaman sesi lapangan.',
                        'Skor disimpan mentah; nilai olahan dihitung lapis data turunan.',
                    ],
                    'why' => [
                        'Tes harus terasa sebagai tes, terpisah dari game, supaya tidak tercampur dengan pengalaman bermain dan hadiah.',
                        'Di modul, tidak ada kertas dan tidak ada pengetikan ulang, sehingga tidak ada salah ketik di data tes.',
                    ],
                    'docs'    => ['`docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md`', '`docs/revisi-10-analitik/13_TES_AWAL_AKHIR.md`'],
                    'sources' => ['revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#2-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'log-event',
                    'title' => 'Log dan event',
                    'lead'  => 'Setiap log masuk salah satu dari tiga: dihapus, hanya untuk admin, atau untuk peneliti. Daftar jenis event ada di satu katalog.',
                    'table' => [['Contoh', 'Keputusan', 'Alasan'], [
                        ['Awal, jeda, akhir sesi; alur tantangan; jawaban', 'Peneliti', 'Analitik 1, 3, 4, 5'],
                        ['Bantuan dan narasi', 'Peneliti', 'Analitik 7'],
                        ['Layar, Pustaka, gulir bacaan', 'Peneliti', 'Analitik 8'],
                        ['Setiap ganti pilihan sebelum Cek', 'Dihapus', 'Tidak dipakai; cukup hitungan per butir'],
                        ['Merek, model perangkat, user agent, zona waktu', 'Dihapus', 'Tidak dipakai dan menambah risiko identifikasi'],
                        ['Ukuran layar berubah, selisih jam perangkat, event hilang', 'Admin', 'Teknis; jumlah event hilang tampil di panel pemantauan'],
                    ]],
                    'why' => [
                        'Minimalisasi data anak sesuai UU No. 27 Tahun 2022: log yang tidak menjawab sepuluh analitik dihapus, bukan disembunyikan.',
                        'Satu katalog event mencegah jenis event baru muncul tanpa alasan; event tertentu hanya boleh ditulis server supaya tidak bisa dipalsukan dari peramban.',
                    ],
                    'refs'    => [self::UU_PDP],
                    'docs'    => [self::D01 . ' → Log: dihapus atau dipisah', '`docs/revisi-10-analitik/29_IMPLEMENTASI_DATA_PENELITIAN.md`'],
                    'sources' => [self::R01 . 'log-dihapus-atau-dipisah'],
                ],
                [
                    'id'    => 'lapis-turunan',
                    'title' => 'Tiga lapis data',
                    'lead'  => 'Data mentah dan [[data turunan|data-turunan]] dihitung di sistem; analisis statistik dikerjakan peneliti di luar sistem.',
                    'table' => [['Lapis', 'Di mana', 'Isinya', 'Mengapa di situ'], [
                        ['1. Data mentah', 'Sistem GELITA', 'Setiap ketukan, jawaban, percobaan, dan event; hanya ditambah, tidak diubah', 'Sumber kebenaran yang bisa ditelusuri ulang'],
                        ['2. Data turunan', 'Sistem GELITA', 'Kode kesalahan per percobaan, waktu aktif, "dilewati", tersensor, tebakan cepat, skor, indeks', 'Aturannya bergantung logika game; dihitung sekali, sama untuk semua peneliti, diuji otomatis'],
                        ['3. Analisis', 'R, jamovi, Winsteps, atau Python', 'Rasch, model campuran, κ, grafik, tabel', 'Reviewer jurnal mengharapkan perangkat lunak statistik standar dan skrip yang bisa diulang'],
                    ]],
                    'list' => [
                        'Data turunan dihitung kalkulator murni per putaran hitung berversi, tidak di dalam game.',
                        'Unduhan menghitung ulang bila data mentah berubah sejak putaran terakhir.',
                        'Data siswa yang mundur dihitung sampai saat mundur saja.',
                    ],
                    'why' => [
                        'Membangun statistik di dalam game mahal dan rawan salah; membiarkan setiap peneliti menghitung sendiri membuat hasil berbeda-beda.',
                        'Putaran hitung berversi mencatat ambang yang dipakai, sehingga setiap angka di unduhan bisa dijelaskan ulang.',
                    ],
                    'refs'    => [self::WISE, self::SINGER, self::BOND],
                    'docs'    => [self::D01 . ' → Olah data: di sistem atau di alat lain?', '`docs/revisi-10-analitik/35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md`'],
                    'sources' => [self::R01 . 'olah-data-di-sistem-atau-di-alat-lain', 'revisi-10-analitik/35_IMPLEMENTASI_LAPIS_DATA_TURUNAN.md#2-keputusan-yang-dikunci'],
                ],
                [
                    'id'    => 'kamus-data',
                    'title' => 'Kamus data',
                    'lead'  => 'Kamus data membekukan definisi sepuluh analitik dan semua variabel. Versinya ditulis di setiap unduhan: `kamus-1.0` (26 Oktober 2026), `kamus-1.1` (disetujui 9 November 2026); ambang yang ditetapkan ulang setelah uji coba akan menjadi `kamus-1.2`.',
                    'list'  => [
                        'Data inti adalah percobaan pertama di pengerjaan pertama.',
                        'Butir tanpa percobaan adalah data hilang, bukan salah; butir yang belum berhasil setelah tiga kesempatan adalah data tersensor.',
                        '"Dilewati" dihitung dalam tiga jenis terpisah.',
                        'Ambang sementara: jeda 3 menit untuk waktu aktif dan tebakan cepat di bawah 3 detik.',
                    ],
                    'why' => [
                        'Definisi dibekukan sebelum data diambil, supaya aturan hitung tidak berubah mengikuti hasil.',
                        'Versi baru menambah sumber data dan variabel tanpa mengubah definisi lama, sehingga unduhan lama tetap bisa dibaca.',
                    ],
                    'docs'    => ['`docs/revisi-10-analitik/11_KAMUS_DATA.md`'],
                    'sources' => ['revisi-10-analitik/11_KAMUS_DATA.md#ringkasan', 'revisi-10-analitik/11_KAMUS_DATA.md#versi-dan-cara-berubah'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function technology(): array
    {
        return [
            'id'       => 'teknologi',
            'title'    => 'Teknologi dan arsitektur',
            'lead'     => 'Pilihan teknik dan alasannya: server sebagai sumber kebenaran, tanpa bundler, pembaruan bersyarat, isi di berkas data, dan sakelar studi.',
            'sections' => [
                [
                    'id'    => 'server-kebenaran',
                    'title' => 'Server sumber kebenaran',
                    'lead'  => 'Benar atau salah, bintang, serpihan, sisa kesempatan, dan buka-kunci dihitung server. Kunci jawaban tidak pernah dikirim ke peramban.',
                    'list'  => [
                        'Data soal yang dikirim ke halaman tidak memuat kunci; tampilan hasil selalu dari jawaban server.',
                        'Benda Cari Objek memakai token bertanda tangan, sehingga posisi jawaban tidak bisa dibaca dari halaman.',
                        'Modul tes memakai aturan yang sama dan diuji otomatis.',
                    ],
                    'why' => [
                        'Asesmen hanya sahih bila jawaban tidak bisa diintip: anak yang membuka kode halaman tidak boleh menemukan kuncinya.',
                        'Dalam arsitektur empat proses ECD, pemrosesan respons dan skor ringkasan terpisah dari presentasi.',
                        'Satu tempat perhitungan berarti satu aturan untuk semua perangkat, termasuk perangkat yang memakai paket aset luring.',
                    ],
                    'refs'    => [self::ALMOND],
                    'docs'    => ['`docs/06_JAVASCRIPT.md` → Prinsip', '`docs/perombakan-kelas5/01_FONDASI_DATA_DAN_RESET.md` → 0. Cara memakai ketiga dokumen'],
                    'sources' => ['06_JAVASCRIPT.md#prinsip', self::K1 . '0-cara-memakai-ketiga-dokumen'],
                ],
                [
                    'id'    => 'tanpa-bundler',
                    'title' => 'Halaman dari server, JavaScript tanpa bundler',
                    'lead'  => 'GELITA dibangun dengan CodeIgniter 4. Halaman dirender server; JavaScript berupa ES module tanpa bundler, transpiler, atau langkah build, dan hanya menambah perilaku.',
                    'list'  => [
                        'Halaman tetap terbaca dan dipakai tanpa JavaScript sejauh mungkin; pendaftaran tiga langkah berjalan juga tanpa JavaScript.',
                        'Tanpa CDN: semua pustaka dan aset di-host sendiri.',
                        'Versi ringkas JS/CSS dibuat oleh perintah khusus, sumbernya tetap tidak diringkas.',
                        'Bisa dipasang di layar utama, dengan paket aset yang diunduh sekali untuk lab berinternet lemah.',
                    ],
                    'why' => [
                        'Tanpa langkah build, kode yang berjalan di sekolah sama dengan kode di repositori, dan pemasangan di server sekolah cukup dengan PHP.',
                        'Tanpa CDN, game tetap berjalan di jaringan sekolah yang membatasi situs luar, dan tidak ada data yang bocor ke pihak ketiga.',
                        'Progressive enhancement membuat perangkat lama tetap bisa dipakai.',
                    ],
                    'docs'    => ['`docs/02_PROJECT_FOUNDATION.md` → 2. Tumpukan teknologi', '`docs/06_JAVASCRIPT.md` → Prinsip'],
                    'sources' => ['02_PROJECT_FOUNDATION.md#2-tumpukan-teknologi', '06_JAVASCRIPT.md#prinsip'],
                ],
                [
                    'id'    => 'pembaruan-bersyarat',
                    'title' => 'Pembaruan bersyarat',
                    'lead'  => 'Penulisan yang bisa berebut (pengerjaan, jawaban, kemajuan siswa) memakai pembaruan bersyarat: baris hanya diubah bila masih bernilai lama. Permintaan yang kalah dibatalkan dan dijawab dari keadaan terbaru.',
                    'why'   => [
                        'Tinjauan 7 Oktober 2026 menemukan batas tiga kesempatan bisa terlewati oleh dua permintaan bersamaan (dua tab, atau kiriman ulang saat Wi-Fi lambat), sampai tercatat percobaan ke-4.',
                        'Cara ini berlaku sama di MySQL/MariaDB dan SQLite (dipakai tes otomatis), tanpa kunci baris yang bisa menahan permintaan lain.',
                        'Nomor cek dari klien membuat cek dari tab lama atau kiriman ulang ditolak tanpa memakan kesempatan.',
                    ],
                    'docs'    => ['`docs/arsip/CATATAN_IMPLEMENTASI-2026-10.md` → Perbaikan sebelum Dok 3 — temuan tinjauan Dok 2'],
                    'sources' => ['arsip/CATATAN_IMPLEMENTASI-2026-10.md#perbaikan-sebelum-dok-3--temuan-tinjauan-dok-2'],
                ],
                [
                    'id'    => 'isi-di-data',
                    'title' => 'Isi di berkas data, sakelar studi',
                    'lead'  => 'Bank soal, cerita, teks, dan isi tes disimpan di berkas data (workbook, berkas cerita, berkas bahasa, berkas instrumen) dan diimpor ke database. Perilaku penelitian ada di balik sakelar studi.',
                    'list'  => [
                        'Revisi dari validasi cukup diubah di data lalu diimpor ulang, tanpa mengubah kode.',
                        'Teks persetujuan diambil dari dokumen surat persetujuan apa adanya; bila komite mengubahnya, dokumen diubah dulu, baru berkas bahasa.',
                        'Kode boleh dibangun sekarang di cabang pendek dari `main`; `main` tetap bisa dirilis karena sakelar mati secara bawaan.',
                        'Instalasi uji validator memakai versi yang dibekukan, bukan `main`, supaya validator menilai satu versi yang tetap.',
                        'Migrasi yang menghapus data baru dijalankan di server produksi bila pengembang memutuskannya.',
                    ],
                    'why' => [
                        'Sistem perlu siap saat izin etik terbit, tetapi isinya masih bisa berubah dari validasi; memisahkan isi dari kode membuat keduanya bisa berjalan bersamaan (keputusan 1 November 2026).',
                        'Tidak ada kegiatan dengan anak dan sistem baru tidak dipakai dengan anak sebelum persetujuan etik terbit.',
                    ],
                    'docs'    => [self::D04 . ' → 1 November 2026 (Aturan baru untuk pekerjaan sistem)', '`docs/bank-soal/README.md`'],
                    'sources' => [self::R04 . '1-november-2026'],
                ],
                [
                    'id'    => 'uji-otomatis',
                    'title' => 'Tes otomatis dan catatan kerja',
                    'lead'  => 'Lebih dari 1.250 tes PHPUnit menjaga aturan game dan penelitian; setiap berkas tes dijalankan terpisah di setiap usulan perubahan.',
                    'list'  => [
                        'Tes memeriksa antara lain batas tiga kesempatan, kebocoran kunci jawaban, dan kelengkapan berkas bahasa.',
                        'Panduan validator dan panduan ini punya kunci sumber: tes gagal bila bagian dokumen sumbernya berubah sampai teksnya diperiksa ulang.',
                        'Setiap keputusan atau penyimpangan dari rencana dicatat di catatan implementasi; setiap langkah kerja ditutup dengan commit dan catatan kemajuan.',
                    ],
                    'why' => [
                        'Tes otomatis adalah bagian dari evaluasi Prototipe II (screening): aturan penelitian yang tidak diuji mudah rusak tanpa ketahuan.',
                        'Pekerjaan dikerjakan banyak sesi; catatan yang lengkap di repositori membuat sesi berikutnya bisa melanjutkan tanpa kehilangan alasan.',
                    ],
                    'refs'    => [self::NIEVEEN],
                    'docs'    => ['`docs/PROGRESS.md`', '`docs/perombakan-kelas5/CATATAN_IMPLEMENTASI.md`', '`docs/02_PROJECT_FOUNDATION.md` → 10. Pengujian'],
                    'sources' => [self::K1 . '0-cara-memakai-ketiga-dokumen'],
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
            'lead'     => 'Perjalanan satu anak dari izin sampai analisis, peta dokumen, rujukan, dan istilah.',
            'sections' => [
                [
                    'id'    => 'alur-satu-anak',
                    'title' => 'Perjalanan satu anak',
                    'lead'  => 'Dari surat izin sampai angka di analisis, setiap langkah memakai keputusan dari bab-bab sebelumnya.',
                    'steps' => [
                        '**Izin.** Orang tua menerima surat dan formulir; anak tanpa izin tidak dibuatkan akun. Lihat [Izin etik](#etik).',
                        '**Pertemuan 1.** Anak memberi persetujuan lisan, mendaftar dengan token, mendapat kode peserta, lalu mengerjakan tes awal di modul tes. Lihat [Sesi lapangan](#sesi-lapangan) dan [Modul tes](#modul-tes).',
                        '**Kursi dan buklet.** Koordinator memasukkan baris dan kursi; panel menghitung buklet dan bentuk tes. Lihat [Variasi dan buklet](#variasi-buklet).',
                        '**Pertemuan 2–4.** Satu wilayah per pertemuan: lima tantangan, tiga kesempatan per soal, percobaan pertama dinilai, setiap percobaan dan kode kesalahan tercatat. Peneliti mengisi lembar pengamatan. Lihat [Lima tantangan](#lima-tantangan).',
                        '**Pertemuan 5.** Tantangan yang tertinggal, cerita penutup, tes akhir, skala sikap di Balai Refleksi.',
                        '**Data.** Data mentah dan lembar lapangan bertemu lewat kode peserta dan jam WIB; data turunan dihitung dengan kamus data berversi. Lihat [Tiga lapis data](#lapis-turunan).',
                        '**Analisis.** Ketua peneliti mengunduh data berkode; Rasch per dimensi, DIF, pola kesalahan, dan korelasi dengan tes dikerjakan di perangkat lunak statistik, sesuai pra-registrasi.',
                        '**Penutupan.** Nama dimusnahkan dan diganti kode; data berkode disimpan sesuai aturan. Lihat [Pelindungan data](#pelindungan-data).',
                    ],
                    'why' => [
                        'Setiap mata rantai menjaga satu hal: izin menjaga hak anak, token menjaga data bersih, buklet menjaga kejujuran jawaban, server menjaga kunci, kamus data menjaga aturan hitung, pemisahan peran menjaga identitas.',
                    ],
                    'refs'    => [self::KANE, self::AERA],
                    'docs'    => [self::D01 . ' → Kerja lapangan: cara kerjanya', '`docs/revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md` → 4. Satu siswa dari P1 sampai P5'],
                    'sources' => [self::R01 . 'kerja-lapangan-cara-kerjanya', 'revisi-10-analitik/34_IMPLEMENTASI_MODUL_TES_DAN_SKALA_SIKAP.md#4-satu-siswa-dari-p1-sampai-p5'],
                ],
                [
                    'id'    => 'peta-dokumen',
                    'title' => 'Peta dokumen',
                    'lead'  => 'Dokumen di repositori yang bisa dibuka untuk rincian.',
                    'table' => [['Ingin tahu', 'Buka'], [
                        ['Gambaran aplikasi dan perintahnya', '`README.md`, `docs/01`–`docs/08`'],
                        ['Perombakan kelas 5', '`docs/perombakan-kelas5/01`–`03` dan catatan implementasinya'],
                        ['Revisi 10, urutan baca dan status', '`docs/revisi-10-analitik/00_INDEKS.md`'],
                        ['Keputusan penelitian terbaru', '`docs/revisi-10-analitik/04_CATATAN_KEPUTUSAN.md`'],
                        ['Model penelitian dan rujukannya', '`docs/revisi-10-analitik/dokumen-pendukung-peneliti/`'],
                        ['Arti kode', '`docs/revisi-10-analitik/03_KODE_DAN_ARTI.md`'],
                        ['Bank soal', '`docs/bank-soal/`'],
                        ['Cerita', '`docs/naskah-cerita.md`'],
                        ['Kemajuan pekerjaan', '`docs/PROGRESS.md`'],
                        ['Gambar, suara, musik', '[Katalog Aset GELITA](' . self::CATALOGUE . ')'],
                    ]],
                    'docs' => ['`docs/revisi-10-analitik/00_INDEKS.md` → Daftar dokumen', '`README.md` → Dokumentasi'],
                    'why' => [
                        'Dokumen penelitian berbahasa Indonesia dan memakai label asal dan status, sehingga pembaca tahu mana yang masih usulan dan mana yang sudah ditetapkan.',
                    ],
                    'sources' => ['revisi-10-analitik/00_INDEKS.md#mulai-sesi-baru-di-sini'],
                ],
                [
                    'id'    => 'rujukan',
                    'title' => 'Daftar rujukan',
                    'lead'  => 'Semua karya yang dirujuk panduan ini, seperti dikutip dokumen penelitian GELITA. Setiap rujukan dicek ke sumber aslinya di dokumen model penelitian (bagian 13, dengan tanda tingkat pengecekan).',
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
                    'note'    => 'Rujukan peraturan dicek ke naskahnya pada 27 Oktober 2026 (dokumen izin etik dan penilaian dampak). Rincian halaman, DOI, dan tanda pengecekan ada di dokumen model penelitian.',
                    'docs'    => [self::DMODEL . ' → 13. Daftar pustaka', '`docs/revisi-10-analitik/22_PERMOHONAN_IZIN_ETIK.md` → Dasar hukum'],
                    'sources' => [self::MODEL, self::R26 . 'dasar-hukum'],
                ],
                [
                    'id'       => 'istilah',
                    'title'    => 'Istilah',
                    'lead'     => 'Istilah yang sering muncul di panduan ini, dengan contoh.',
                    'glossary' => true,
                ],
            ],
        ];
    }
}
