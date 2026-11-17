<?php

namespace App\Libraries;

/**
 * Text of the validator guide (/admin/panduan-validator): what the expert
 * validators need from the validation package (docs 20 and 21 of revision
 * 10), written for them, public like the staff guide and offered beside the
 * admin login card (owner, 17 November 2026).
 *
 * Audiences (`panels` of a chapter, `?panel=` for a reader's pick):
 *   materi  ahli materi (five validators, instrument IM)
 *   b5a     the two blind labelling raters (B.5a), a short page of their own
 *   budaya  pengamat budaya (three, IB)
 *   media   ahli media (three, IMd, step 1)
 *
 * What must never be here (ValidatorGuideTest refuses it): developer labels
 * (dimension, sub-aspect, AKM level, Bloom) or item codes, answer keys, the
 * researchers' key file, real names, passwords, addresses, honorarium
 * amounts, and repository paths or server talk. Validators rate before they
 * see developer work (01 → Peran pengembang, peneliti, dan ahli).
 *
 * Keeping it current:
 * - Facts that change with the game come from ValidatorGuideFacts (a
 *   section's `facts` lists the keys), read when the page opens.
 * - Hand-written text names its sources (`sources`, `doc.md#anchor` in
 *   docs/revisi-10-analitik/). GuideSourceCheck flags a section when one of
 *   its sources changed; check the text, then run
 *   `php spark gelita:panduan:cek --tandai`.
 *
 * Shape and markup: see GuideContent and StaffGuideContent. Sections here
 * carry no panel links (validators have no staff account); steps are plain
 * strings.
 */
final class ValidatorGuideContent extends GuideContent
{
    public const BASE = 'admin/panduan-validator';

    /** Audience keys in menu order, and their names. */
    public const PANELS = [
        'materi' => 'Ahli materi',
        'b5a'    => 'Penilai B.5a',
        'budaya' => 'Pengamat budaya',
        'media'  => 'Ahli media',
    ];

    /** Sections offered as cards on the first page. */
    public const POPULAR = ['alur-kerja', 'masuk', 'instalasi-ini', 'kembali-apa', 'yang-dijaga'];

    private const ALL = ['materi', 'b5a', 'budaya', 'media'];

    private const PLAYERS = ['materi', 'budaya', 'media'];

    private const P20 = '20_PAKET_VALIDASI.md#';

    /** @return array<string, array{term: string, def: string, example: string, more: string|null}> */
    public static function terms(): array
    {
        return [
            'instalasi-uji' => [
                'term'    => 'Instalasi uji',
                'def'     => 'Salinan GELITA yang dipasang khusus untuk validasi, terpisah dari server yang dipakai sekolah. Tidak berisi data siswa.',
                'example' => 'Alamatnya dikirim tim peneliti lewat surel, terpisah dari kata sandi.',
                'more'    => 'masuk',
            ],
            'akun-uji' => [
                'term'    => 'Akun uji',
                'def'     => 'Akun siswa untuk validator, dengan nama samaran per peran. Bukan akun staf: validator tidak membuka panel dan tidak melihat data siswa.',
                'example' => '`uji.budaya2` untuk Ahli Budaya Kedu 2.',
                'more'    => 'masuk',
            ],
            'nama-samaran' => [
                'term'    => 'Nama samaran',
                'def'     => 'Nama pengganti validator di semua dokumen penelitian. Nama asli hanya ada di surat pernyataan dan arsip ketua peneliti.',
                'example' => 'Ahli Media Pembelajaran 3, disingkat `AMP3` di nama berkas.',
                'more'    => 'kembali-cara',
            ],
            'kiriman' => [
                'term'    => 'Kiriman',
                'def'     => 'Satu pengiriman bahan ke validator. Sebagian panel menerima bahannya dalam dua kiriman.',
                'example' => 'Kiriman awal di minggu 1, kiriman setelah B.5a di minggu 3.',
                'more'    => 'alur-kerja',
            ],
            'putaran' => [
                'term'    => 'Putaran',
                'def'     => 'Satu kali penilaian. Putaran 1 adalah penilaian pertama; putaran 2 dan seterusnya hanya menilai ulang butir yang direvisi.',
                'example' => 'Putaran 2 sekitar minggu 10–11, dengan rubrik yang sama dan waktu jauh lebih singkat.',
                'more'    => 'putaran-ulang',
            ],
            'td' => [
                'term'    => 'TD (tidak dapat dinilai)',
                'def'     => 'Pilihan untuk butir di luar keahlian Anda, disertai alasan singkat. Hanya boleh pada lembar bertanda ○, tidak pada lembar wajib (W).',
                'example' => 'Ahli media selain Ahli Media Pembelajaran 3 mengisi TD pada butir teknis H3.',
                'more'    => null,
            ],
            'lembar-kerja' => [
                'term'    => 'Lembar kerja',
                'def'     => 'Berkas spreadsheet yang sudah berisi daftar butir dan kolom penilaian. Baris dan kolomnya jangan ditambah, dihapus, atau dipindah.',
                'example' => 'Lembar kerja B.8 untuk ahli media berisi gambar rancangan layar yang dinilai.',
                'more'    => 'kembali-cara',
            ],
            'versi-berkas' => [
                'term'    => 'Versi berkas',
                'def'     => 'Tanda versi di kepala setiap lembar kerja. Tim mencocokkannya dengan versi yang dikirim kepada Anda.',
                'example' => '`berkas-1.0 · sumber 3a48dbfe` di baris paling atas lembar.',
                'more'    => 'kembali-cara',
            ],
            'id-netral' => [
                'term'    => 'ID netral',
                'def'     => 'Nomor pengganti kode butir pada pelabelan buta, supaya nomor itu tidak menunjukkan wilayah, tantangan, atau bagian tes.',
                'example' => 'Butir `BT-001` sampai `BT-180`, bahan bacaan `BH-01` sampai `BH-73`.',
                'more'    => 'b5a-urutan',
            ],
            'mockup' => [
                'term'    => 'Gambar rancangan layar (mock-up)',
                'def'     => 'Gambar layar yang belum dibuat di game. Dinilai dari gambarnya; layar jadinya dinilai lagi di langkah 2.',
                'example' => 'Layar Skenario Digital dan layar token sebelum dibuat.',
                'more'    => 'media-kiriman',
            ],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function chapters(): array
    {
        return [
            self::start(),
            self::blind(),
            self::content(),
            self::culture(),
            self::media(),
            self::play(),
            self::returning(),
            self::rules(),
        ];
    }

    /**
     * Every source reference used by the guide, for GuideSourceCheck.
     *
     * @return list<string>
     */
    public static function sources(): array
    {
        $refs = [];

        foreach (self::chapters() as $chapter) {
            foreach ($chapter['sections'] as $section) {
                array_push($refs, ...($section['sources'] ?? []));
            }
        }

        return array_values(array_unique($refs));
    }

    /** @return array<string, mixed> */
    private static function start(): array
    {
        return [
            'id'       => 'mulai',
            'title'    => 'Mulai di sini',
            'panels'   => self::ALL,
            'lead'     => 'Untuk siapa panduan ini, dan urutan kerja validasi dari surat sampai putaran ulang.',
            'sections' => [
                [
                    'id'      => 'selamat-datang',
                    'title'   => 'Selamat datang',
                    'lead'    => 'Terima kasih telah bersedia menjadi validator GELITA. Panduan ini merangkum hal praktis dari paket validasi: apa yang Anda terima, cara masuk ke [[instalasi uji|instalasi-uji]], dan cara mengembalikan hasil.',
                    'table'   => [['Panel', 'Validator', 'Instrumen'], [
                        ['Ahli materi', 'Ahli Bahasa Anak 1, 2; Ahli Literasi Digital 1, 2; Ahli Pengukuran', 'Instrumen Validasi Ahli Materi'],
                        ['Pengamat budaya', 'Ahli Budaya Kedu 1, 2, 3', 'Instrumen Validasi Pengamat Budaya'],
                        ['Ahli media', 'Ahli Media Pembelajaran 1, 2, 3', 'Instrumen Validasi Ahli Media (langkah 1)'],
                    ]],
                    'list'    => [
                        'Pilih panel Anda di menu kiri, agar bab untuk panel Anda ada paling atas.',
                        'Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1 juga menjadi **penilai pelabelan buta (B.5a)**. Bacalah [Penilai B.5a](#b5a-urutan) lebih dulu, sebelum bab lain.',
                        'Semua validator memakai [[nama samaran|nama-samaran]]. Nama asli Anda tidak pernah muncul di panduan ini atau di dokumen penelitian.',
                    ],
                    'note'    => 'Panduan ini tidak menggantikan instrumen. Bila ada yang berbeda, ikuti instrumen dan surat dari ketua peneliti, lalu beri tahu tim agar panduan ini diperbaiki.',
                    'sources' => [self::P20 . 'ringkasan'],
                ],
                [
                    'id'      => 'alur-kerja',
                    'title'   => 'Alur kerja validasi',
                    'lead'    => 'Urutan dari kesediaan sampai penilaian ulang. Minggu dihitung dari [[kiriman]] awal; tanggalnya ada di surat Anda.',
                    'steps'   => [
                        '**Minggu 0, kesediaan.** Ketua peneliti menanyakan kesediaan, waktu, dan perangkat Anda. Surat resmi dikirim setelah Anda bersedia.',
                        '**Minggu 1, kiriman awal.** Surat permohonan, instrumen, berkas bahan, dan lembar kerja panel Anda. Pengarahan daring per panel: ahli materi sekitar 45 menit, pengamat budaya 30–45 menit, ahli media 30 menit. Dua penilai B.5a menerima paket B.5a saja dan pengarahan tersendiri sekitar 15 menit.',
                        '**Minggu 1–5, mengisi.** Pengisian boleh dicicil. Batas: lembar B.5a akhir minggu 2, lembar B.6 ahli media akhir minggu 3, semua lembar ahli media dan pengamat budaya akhir minggu 4, semua lembar ahli materi akhir minggu 5.',
                        '**Minggu 3, kiriman lanjutan.** Ahli materi menerima bahan berlabel setelah kedua lembar B.5a kembali. Ahli media yang sudah mengumpulkan B.6 menerima daftar temuan telaah pendahuluan.',
                        '**Minggu 10–11, [[putaran]] 2.** Hanya butir yang direvisi, dikirim ke penilainya bersama matriks tindak lanjut. Bila masih belum memenuhi aturan, ada putaran berikutnya dengan pola dua minggu yang sama.',
                        '**Sesudahnya.** Butir yang direvisi dari uji keterbacaan dengan siswa bisa kembali untuk dinilai ulang. Ahli media diminta telaah singkat langkah 2 pada layar jadi, dengan surat tersendiri.',
                    ],
                    'note'    => 'Pengingat dikirim di minggu 2, minggu 4, dan dua hari sebelum batas. Bila Anda butuh waktu lebih, kabari ketua peneliti sebelum batasnya.',
                    'sources' => [self::P20 . 'kiriman-dan-putaran', self::P20 . 'jadwal-relatif'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function blind(): array
    {
        return [
            'id'       => 'b5a',
            'title'    => 'Penilai B.5a',
            'panels'   => ['b5a'],
            'lead'     => 'Untuk Ahli Bahasa Anak 1 dan Ahli Literasi Digital 1: pelabelan buta dikerjakan lebih dulu, tanpa membuka bahan lain.',
            'sections' => [
                [
                    'id'      => 'b5a-urutan',
                    'title'   => 'Urutan kerja Anda',
                    'lead'    => 'Lembar B.5a menguji apakah dua ahli memberi label yang sama tanpa melihat label dari tim. Karena itu B.5a dikerjakan lebih dulu, sebelum Anda membaca instrumen lengkap atau bahan lain.',
                    'steps'   => [
                        '**Minggu 1–2: B.5a saja (sekitar 6–8 jam).** Kiriman pertama Anda hanya berisi paket B.5a: lembar kerja daftar butir buta, buklet yang sama untuk diisi di kertas bila lebih nyaman, dan halaman petunjuk B.5a.',
                        'Setiap butir memakai [[ID netral|id-netral]] `BT-…` dan setiap bahan bacaan `BH-…`. Urutan bahan sudah diacak.',
                        'Kembalikan lembar B.5a paling lambat **akhir minggu 2**. Bila selesai lebih cepat, kiriman berikutnya juga datang lebih cepat.',
                        '**Minggu 3–5: lembar lain.** Begitu kedua lembar B.5a diterima, Anda menerima instrumen lengkap, bahan berlabel, lembar kerja lain, dan akses [[instalasi uji|instalasi-uji]], lalu ikut pengarahan ahli materi sekitar 45 menit.',
                    ],
                    'table'   => [['Validator', 'B.5a (minggu 1–2)', 'Lembar lain (minggu 3–5)'], [
                        ['Ahli Bahasa Anak 1', '6–8 jam', 'sekitar 9–14 jam (sekitar 3–6 jam per minggu)'],
                        ['Ahli Literasi Digital 1', '6–8 jam', 'sekitar 12–18 jam, termasuk B.7 (sekitar 4–6 jam per minggu)'],
                    ]],
                    'note'    => 'Bila beban minggu 3–5 terlalu berat, bicarakan dengan ketua peneliti. Batas Anda bisa diundur sampai akhir minggu 6.',
                    'sources' => [self::P20 . 'panel-ahli-materi-im', self::P20 . 'beban-dua-penilai-b5a-di-minggu-35', self::P20 . 'surat-1-ahli-materi'],
                ],
                [
                    'id'      => 'b5a-buta',
                    'title'   => 'Menjaga pelabelan tetap buta',
                    'lead'    => 'Selama mengerjakan B.5a, hindari semua yang bisa menunjukkan label dari tim.',
                    'list'    => [
                        'Jangan membuka game GELITA atau bab [Bermain di instalasi uji](#masuk) di panduan ini sampai lembar B.5a Anda dikirim. Game memperlihatkan wilayah setiap butir. Alamat dan akun uji memang baru dikirim sesudahnya.',
                        'Jangan berdiskusi dengan penilai B.5a lain atau validator lain tentang butir sampai lembar Anda selesai.',
                        'Kolom dimensi, sub-aspek, level, dan Bloom di lembar kerja memang kosong. Isi menurut pedoman di halaman petunjuk B.5a.',
                        'Kunci jawaban tetap dicantumkan, karena label menilai proses yang dilakukan siswa untuk sampai ke jawaban benar.',
                        'Nama tempat dan bentuk tugas di butir masih memberi isyarat. Itu wajar dan sudah dicatat sebagai keterbatasan. Nilailah dari isi butir.',
                        'Daftar butir B.5a mohon tidak disebarkan.',
                    ],
                    'sources' => ['21_BERKAS_BUTIR_VALIDATOR.md#cara-menjaga-kebutaan-b5a', self::P20 . 'akses-untuk-validator'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function content(): array
    {
        return [
            'id'       => 'materi',
            'title'    => 'Ahli materi',
            'panels'   => ['materi'],
            'lead'     => 'Lima validator menilai kesesuaian isi dengan konstruk literasi digital, ketepatan kunci, kebahasaan, dan relevansi setiap butir.',
            'sections' => [
                [
                    'id'      => 'materi-kiriman',
                    'title'   => 'Yang Anda terima, dan kapan',
                    'lead'    => 'Bahan ahli materi datang dalam dua [[kiriman]], karena label dari tim baru boleh dibuka setelah pelabelan buta selesai.',
                    'table'   => [['Kiriman', 'Ahli Bahasa Anak 2, Ahli Literasi Digital 2, Ahli Pengukuran', 'Dua penilai B.5a'], [
                        ['Awal (minggu 1)', 'Surat, instrumen lengkap, bahan bank soal **tanpa** label, lembar kerja B.6–B.9, bahan lain menurut tabel D.3 instrumen', 'Paket B.5a saja'],
                        ['Setelah B.5a (minggu 3)', 'Bahan dengan label dari tim, kisi-kisi, lembar kerja B.5b relevansi dan kejelasan', 'Instrumen lengkap, semua bahan (berlabel), lembar kerja B.5b dan B.6–B.9, akses instalasi uji'],
                    ]],
                    'list'    => [
                        'Lembar yang menjadi tugas Anda ditandai di Tabel A1 instrumen: **W** wajib, **○** diisi bila sesuai keahlian ([[TD|td]] boleh).',
                        'Semua ahli materi juga mengisi lembar identitas (B.1), kesimpulan (B.10), dan surat pernyataan (D.1).',
                        'Lembar B.6–B.9 dan sebagian B.3 boleh dikerjakan sejak kiriman awal. B.5b dan sisa B.3 dikerjakan setelah kiriman kedua.',
                        'Bila sebuah butir punya kurang dari tiga penilaian selain TD, ketua peneliti bisa meminta Anda mengisi lembar bertanda ○.',
                    ],
                    'sources' => [self::P20 . 'panel-ahli-materi-im', self::P20 . 'berkas-dari-21-per-penerima', self::P20 . 'surat-1-ahli-materi'],
                ],
                [
                    'id'      => 'materi-lembar',
                    'title'   => 'Lembar kerja dan perkiraan waktu',
                    'lead'    => 'Lembar per butir tersedia sebagai [[lembar kerja|lembar-kerja]] yang sudah berisi daftar butirnya. Lembar lain boleh diisi di berkas instrumen atau di kertas.',
                    'table'   => [['Lembar kerja', 'Untuk'], [
                        ['`materi-b5b-relevansi-kejelasan.xlsx`', 'Kelima ahli materi (B.5b)'],
                        ['`materi-b6-b9-lembar-kerja.xlsx`', 'Kelima ahli materi (B.6, B.7, B.8a–b, B.9a–b). B.7 wajib untuk Ahli Literasi Digital 1 dan 2'],
                        ['`materi-b7-kode-kesalahan-buta.xlsx`', 'Ahli Literasi Digital 1 dan 2, hanya bila ketua peneliti meminta pengodean B.7 tanpa kolom asal kode'],
                    ]],
                    'list'    => [
                        'Perkiraan seluruh pengisian: Ahli Bahasa Anak 2 sekitar 9–14 jam, Ahli Literasi Digital 2 sekitar 12–18 jam, Ahli Pengukuran sekitar 7,5–12 jam. Belum termasuk pengarahan dan bermain satu wilayah.',
                        'Lembar B.7 mencakup semua sel kode, sekitar 3–5 jam. Sel dari aturan tetap bisa disaring lewat kolom **Asal kode** dan dinilai per aturan.',
                        'Mainkan minimal satu wilayah di [[instalasi uji|instalasi-uji]] untuk mengenal konteks butir (lihat [Masuk dengan akun uji](#masuk)).',
                    ],
                    'sources' => [self::P20 . 'siapa-menerima-apa', self::P20 . 'panel-ahli-materi-im', self::P20 . 'berkas-dari-21-per-penerima'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function culture(): array
    {
        return [
            'id'       => 'budaya',
            'title'    => 'Pengamat budaya',
            'panels'   => ['budaya'],
            'lead'     => 'Tiga pengamat budaya menilai ketepatan dan kepantasan muatan budaya Kedu di game, bacaan, dan gambar.',
            'sections' => [
                [
                    'id'      => 'budaya-tugas',
                    'title'   => 'Tugas dan bahan Anda',
                    'lead'    => 'Semua bahan dikirim di kiriman awal (minggu 1). Ketiga pengamat budaya menilai semua butir; yang berbeda hanya lembar B.8.',
                    'table'   => [['Lembar', 'Isi'], [
                        ['B.3', 'Semua butir'],
                        ['B.5', 'Matriks objek budaya. Nilailah objek yang Anda kuasai'],
                        ['B.6a, B.6b', 'Babak Periksa Unggahan dan butir pemahaman budaya'],
                        ['B.7', 'Konfirmasi cek fakta: setiap fakta yang sudah dicek tim, dengan kode sumbernya'],
                        ['B.8', 'Ahli Budaya Kedu 1: relevansi, indikator, contoh budaya, dan B.8b. Ahli Budaya Kedu 2 dan 3: contoh budaya untuk wilayahnya (wajib), B.8b (boleh)'],
                    ]],
                    'list'    => [
                        'Lembar B.5–B.8 ada di [[lembar kerja|lembar-kerja]] `budaya-b5-b8-lembar-kerja.xlsx`. Lembar B.1, B.3, dan B.9 diisi di berkas instrumen atau di kertas.',
                        'Bahan bank soal yang Anda terima tidak memuat label level dan Bloom. Label itu memang tidak dikirim ke panel ini.',
                        'Idealnya setiap pengamat paling mengenal satu wilayah: Temanggung, Magelang, atau Wonosobo.',
                        'Gambar baru Wayang Kedu Temanggungan dan Candi Pawon menyusul setelah kiriman awal.',
                        'Perkiraan waktu: sekitar 9–13,5 jam untuk Ahli Budaya Kedu 1, sekitar 8,5–12,5 jam untuk Ahli Budaya Kedu 2 dan 3, termasuk membaca bahan dan bermain.',
                    ],
                    'sources' => [self::P20 . 'panel-pengamat-budaya-ib', self::P20 . 'berkas-dari-21-per-penerima'],
                ],
                [
                    'id'      => 'budaya-pustaka',
                    'title'   => 'Bermain dan membaca Pustaka',
                    'lead'    => 'Mainkan minimal satu wilayah, sebaiknya wilayah yang paling Anda kenal, lalu baca Pustaka wilayah itu beserta sumbernya.',
                    'steps'   => [
                        'Masuk ke [[instalasi uji|instalasi-uji]] dengan [[akun uji|akun-uji]] Anda (lihat [Masuk dengan akun uji](#masuk)).',
                        'Selesaikan semua tantangan satu wilayah. Pustaka wilayah itu baru terbuka sesudahnya.',
                        'Buka Pustaka dari peta wilayah, baca setiap halaman, dan catat temuan di lembar yang sesuai.',
                    ],
                    'facts'   => ['pustaka', 'wilayah'],
                    'sources' => [self::P20 . 'akses-untuk-validator', self::P20 . 'prasyarat-no-8-instalasi-uji-dan-akun-uji'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function media(): array
    {
        return [
            'id'       => 'media',
            'title'    => 'Ahli media',
            'panels'   => ['media'],
            'lead'     => 'Tiga ahli media menilai tampilan, audio, interaksi, alur permainan, dan keandalan GELITA di beberapa perangkat, serta gambar rancangan layar baru.',
            'sections' => [
                [
                    'id'      => 'media-kiriman',
                    'title'   => 'Tugas dan bahan Anda',
                    'lead'    => 'Penilaian sekarang adalah **langkah 1**: game di [[instalasi uji|instalasi-uji]] dan [[gambar rancangan layar|mockup]]. Langkah 2, telaah singkat pada layar jadi, diminta dengan surat tersendiri sebelum uji coba.',
                    'table'   => [['Lembar', 'Isi', 'Cara mengisi'], [
                        ['B.3', 'Mutu media. Butir teknis H3 hanya diisi Ahli Media Pembelajaran 3, sebagai daftar periksa lulus/tidak; yang lain mengisi [[TD|td]]', 'Berkas instrumen atau kertas'],
                        ['B.5', 'Uji perangkat', 'Berkas instrumen atau kertas'],
                        ['B.6', 'Temuan, dengan tangkapan layar', 'Berkas instrumen atau kertas'],
                        ['B.7', 'Penilaian per layar', 'Berkas instrumen atau kertas'],
                        ['B.8', 'Gambar rancangan layar', '[[Lembar kerja|lembar-kerja]] `media-b8-mockup.xlsx`'],
                    ]],
                    'list'    => [
                        'Semua bahan dikirim di kiriman awal. Setelah lembar B.6 Anda kembali, Anda menerima daftar temuan telaah pendahuluan media.',
                        'Batas B.6: akhir minggu 3. Batas semua lembar: akhir minggu 4.',
                        'Perkiraan waktu: sekitar 4–5,5 jam (bermain 2–3 jam, gambar rancangan 1–1,5 jam, B.3 sekitar 1 jam), ditambah pengarahan 30 menit.',
                    ],
                    'sources' => [self::P20 . 'panel-ahli-media-imd', self::P20 . 'surat-3-ahli-media'],
                ],
                [
                    'id'      => 'media-perangkat',
                    'title'   => 'Perangkat dan cara menguji',
                    'lead'    => 'Ujilah minimal tiga perangkat. Bila Anda tidak punya salah satunya, tim dapat meminjamkannya.',
                    'steps'   => [
                        'Siapkan laptop atau Chromebook, tablet mendatar 10 inci atau lebih, dan satu perangkat seperti milik sekolah.',
                        'Di setiap perangkat, mainkan satu wilayah penuh.',
                        'Daftarkan satu akun baru sendiri ([Mendaftar akun sendiri](#media-daftar)), buka Pustaka, lalu keluar akun.',
                        'Pasang GELITA sebagai aplikasi bila perangkat menawarkannya, lalu catat hasilnya di lembar B.5.',
                        'Ambil tangkapan layar untuk setiap temuan B.6, dan catat perangkat yang dipakai di surat pernyataan (D.1).',
                    ],
                    'facts'   => ['layar', 'versi'],
                    'warn'    => 'Pakai instalasi uji saja. Jangan masuk ke server yang dipakai sekolah, dan jangan menjalankan uji beban, uji penetrasi, atau percobaan masuk ke akun lain.',
                    'sources' => [self::P20 . 'panel-ahli-media-imd', self::P20 . 'akses-untuk-validator', self::P20 . 'surat-3-ahli-media'],
                ],
                [
                    'id'      => 'media-daftar',
                    'title'   => 'Mendaftar akun sendiri',
                    'lead'    => 'Pendaftaran termasuk yang dinilai, jadi ahli media boleh mendaftar akun baru sendiri selain memakai [[akun uji|akun-uji]] dari tim.',
                    'steps'   => [
                        'Buka halaman pendaftaran dari layar pertama game.',
                        'Identitas: nama samaran tanpa angka dan tanpa titik, misalnya `Uji Media Satu`.',
                        'Sekolah: pilih **Sekolahku tidak ada di daftar**, lalu tulis `Sekolah Uji Validator`. Dengan begitu akun tidak tertaut ke sekolah sungguhan.',
                        'Akun: nama pengguna diawali `uji.media`, misalnya `uji.media1b`, lalu kata sandi yang kuat.',
                    ],
                    'facts'   => ['nama-pengguna', 'kata-sandi', 'umur', 'mode-penelitian'],
                    'sources' => [self::P20 . 'usulan-akun-uji', self::P20 . 'prasyarat-no-8-instalasi-uji-dan-akun-uji'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function play(): array
    {
        return [
            'id'       => 'bermain',
            'title'    => 'Bermain di instalasi uji',
            'panels'   => self::PLAYERS,
            'lead'     => 'Cara masuk, apa saja isi permainan di instalasi ini, dan bila akun bermasalah.',
            'sections' => [
                [
                    'id'      => 'masuk',
                    'title'   => 'Masuk dengan akun uji',
                    'lead'    => 'Anda bermain sebagai siswa dengan [[akun uji|akun-uji]], bukan sebagai staf. Alamat [[instalasi uji|instalasi-uji]] dan kata sandi dikirim lewat dua saluran berbeda, misalnya alamat lewat surel dan kata sandi lewat pesan.',
                    'steps'   => [
                        'Buka alamat instalasi uji di peramban, sebaiknya di jendela pribadi.',
                        'Pilih **Masuk**, lalu ketik nama pengguna dan kata sandi dari tim.',
                        'Bila diminta mengganti kata sandi, buat yang baru dan simpan sendiri.',
                        'Bila mode penelitian menyala, ketik token sesi dari tim peneliti.',
                        'Selesai bermain, keluar akun dari menu.',
                    ],
                    'table'   => [['Akun', 'Untuk'], [
                        ['`uji.bahasa1`, `uji.bahasa2`', 'Ahli Bahasa Anak 1, 2'],
                        ['`uji.digital1`, `uji.digital2`', 'Ahli Literasi Digital 1, 2'],
                        ['`uji.ukur`', 'Ahli Pengukuran'],
                        ['`uji.budaya1` – `uji.budaya3`', 'Ahli Budaya Kedu 1–3'],
                        ['`uji.media1` – `uji.media3`', 'Ahli Media Pembelajaran 1–3'],
                    ]],
                    'facts'   => ['mode-penelitian'],
                    'note'    => 'Pakai akun ini hanya untuk validasi, dan jangan dipinjamkan. Penilai B.5a menerima alamat dan akunnya setelah lembar B.5a kembali.',
                    'sources' => [self::P20 . 'akses-untuk-validator', self::P20 . 'usulan-akun-uji'],
                ],
                [
                    'id'      => 'instalasi-ini',
                    'title'   => 'Isi permainan di instalasi ini',
                    'lead'    => 'Tabel di bawah dibaca langsung dari instalasi ini saat halaman dibuka, jadi selalu sesuai dengan game yang Anda mainkan.',
                    'facts'   => ['versi', 'instrumen', 'wilayah', 'jenis-tantangan', 'kesempatan', 'wilayah-terbuka', 'mode-penelitian', 'pustaka'],
                    'list'    => [
                        'Ahli materi dan pengamat budaya memainkan minimal satu wilayah. Ahli media memainkan satu wilayah penuh di setiap perangkat.',
                        'Jawablah seperti siswa kelas 5. Tidak ada jawaban Anda yang ikut dihitung sebagai data penelitian.',
                        'Catat temuan di lembar Anda saat bermain, termasuk halaman tempat temuan itu muncul.',
                    ],
                    'note'    => 'Bila tabel ini dibuka di alamat lain selain instalasi uji, isinya menggambarkan instalasi itu, bukan instalasi uji. Pakai alamat dari tim peneliti.',
                    'sources' => [self::P20 . 'akses-untuk-validator', self::P20 . 'prasyarat-no-8-instalasi-uji-dan-akun-uji'],
                ],
                [
                    'id'      => 'masalah-akun',
                    'title'   => 'Bila akun bermasalah',
                    'lead'    => 'Hubungi ketua peneliti. Jangan membuat akun dengan nama lain selain yang diminta.',
                    'table'   => [['Keadaan', 'Yang terjadi'], [
                        ['Lupa kata sandi atau akun terkunci', 'Tim memberi kata sandi sementara. Anda menggantinya saat masuk berikutnya.'],
                        ['Perlu mulai dari awal', 'Tim memberi akun cadangan, atau mengosongkan kemajuan bermain akun Anda.'],
                        ['Semua wilayah sudah tuntas', 'Masuk berikutnya membuka permainan baru dari awal. Ini bukan galat.'],
                        ['Validator diganti', 'Validator pengganti memakai akun cadangan.'],
                    ]],
                    'facts'   => ['jeda-lama'],
                    'sources' => [self::P20 . 'prasyarat-no-8-instalasi-uji-dan-akun-uji', self::P20 . 'usulan-akun-uji'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function returning(): array
    {
        return [
            'id'       => 'kembali',
            'title'    => 'Mengembalikan hasil',
            'panels'   => self::ALL,
            'lead'     => 'Apa yang dikirim kembali, cara menamai dan memeriksanya, dan kapan butir kembali untuk dinilai ulang.',
            'sections' => [
                [
                    'id'      => 'kembali-apa',
                    'title'   => 'Apa yang dikirim kembali',
                    'table'   => [['Dari', 'Yang dikirim kembali'], [
                        ['Penilai B.5a', 'Lembar kerja B.5a, atau buklet yang diisi di kertas, paling lambat akhir minggu 2'],
                        ['Ahli materi', 'Lembar kerja B.5b dan B.6–B.9 (B.7 dari lembar pengodean buta bila diminta); lembar B.1, B.3, B.10; surat pernyataan D.1 bertanda tangan'],
                        ['Pengamat budaya', 'Lembar kerja B.5–B.8; lembar B.1, B.3, B.9; surat pernyataan D.1'],
                        ['Ahli media', 'Lembar kerja B.8; lembar B.3 dan B.5–B.7; tangkapan layar temuan; kesimpulan B.9; surat pernyataan D.1 dengan daftar perangkat yang dipakai'],
                        ['Putaran 2 dan seterusnya', 'Lembar nilai ulang dan matriks tindak lanjut yang diparaf'],
                    ]],
                    'note'    => 'Kirim ke ketua peneliti lewat saluran yang disebut di surat. Instalasi uji tidak dipakai untuk mengirim lembar.',
                    'sources' => [self::P20 . 'apa-yang-kembali'],
                ],
                [
                    'id'      => 'kembali-cara',
                    'title'   => 'Menamai dan memeriksa berkas',
                    'steps'   => [
                        'Isi [[lembar kerja|lembar-kerja]] tanpa menambah, menghapus, atau memindah baris dan kolom. Tim menyalin baris lembar kerja ke rekap tanpa mengetik ulang.',
                        'Biarkan [[versi berkas|versi-berkas]] di kepala lembar apa adanya. Bila versinya berbeda dari surat, tanyakan sebelum mengisi.',
                        'Setiap skor 1 atau 2 diberi catatan, dan setiap [[TD|td]] diberi alasan. Lembar yang belum lengkap akan ditanyakan sekali.',
                        'Bila bisa, namai berkas dengan [[nama samaran|nama-samaran]], lembar, putaran, dan tanggal, misalnya `AMP3_B8_p1_2026-12-10.xlsx`.',
                        'Di surat pernyataan (D.1), tulis versi atau tanggal bahan yang Anda nilai. Versi game ada di halaman [Isi permainan di instalasi ini](#instalasi-ini).',
                    ],
                    'note'    => 'Surat pernyataan memuat nama asli dan tanda tangan, jadi hanya disimpan di arsip ketua peneliti. Di semua dokumen lain hanya nama samaran yang dipakai.',
                    'sources' => [self::P20 . 'apa-yang-kembali', self::P20 . 'di-mana-disimpan'],
                ],
                [
                    'id'      => 'putaran-ulang',
                    'title'   => 'Bila butir kembali untuk dinilai ulang',
                    'lead'    => 'Butir yang belum memenuhi aturan direvisi, lalu dinilai ulang oleh semua penilainya di [[putaran]] berikutnya, dengan rubrik yang sama.',
                    'list'    => [
                        'Butir yang direvisi kembali ke semua penilai butir itu, sampai memenuhi aturan. Jumlah putaran tidak dibatasi.',
                        'Catatan kritis (veto) kembali ke validator yang memberinya dan ke semua penilai butir itu.',
                        'Butir dengan kurang dari tiga penilaian selain TD dikirim ke penilai tambahan dari panel yang sama.',
                        'Revisi kecil (skor 3, catatan kecil) tidak dikirim ulang; cukup tercatat di matriks.',
                        'Temuan media yang berat dan skor 1–2 pada gambar rancangan layar dinilai lagi di putaran 2 atau di langkah 2, pada layar jadi.',
                        'Setiap putaran memberi waktu dua minggu dan dikirim bersama matriks tindak lanjut yang hanya berisi baris untuk Anda.',
                    ],
                    'sources' => [self::P20 . 'kapan-butir-yang-direvisi-kembali-ke-validator', self::P20 . 'kiriman-dan-putaran'],
                ],
            ],
        ];
    }

    /** @return array<string, mixed> */
    private static function rules(): array
    {
        return [
            'id'       => 'rambu',
            'title'    => 'Rambu validator',
            'panels'   => self::ALL,
            'lead'     => 'Hal yang dijaga bersama selama validasi, dan daftar istilah.',
            'sections' => [
                [
                    'id'      => 'yang-dijaga',
                    'title'   => 'Yang dijaga bersama',
                    'list'    => [
                        '**Mandiri.** Jangan berdiskusi dengan validator lain sampai lembar Anda selesai. Pertanyaan tentang bahan diajukan ke ketua peneliti, yang menjawab dengan informasi faktual.',
                        '**Rahasia.** Bank soal, kunci jawaban, butir tes, skema kode, isi game, gambar rancangan, dan tangkapan layar jangan disebarkan. Kunci yang bocor ke calon peserta merusak data.',
                        '**Hanya instalasi uji.** Pakai alamat dan akun dari tim. Jangan masuk ke server yang dipakai sekolah.',
                        '**Tanpa data siswa.** Validator tidak pernah menerima atau melihat data siswa. Akun uji adalah akun siswa tanpa akses panel.',
                        '**Nama samaran.** Nama asli Anda hanya ada di surat pernyataan dan arsip ketua peneliti, dan disebut di laporan hanya dengan izin Anda.',
                        '**Honorarium** sesuai ketentuan yang berlaku, dibayarkan setelah lembar dan surat pernyataan diterima. Rinciannya ada di surat Anda.',
                    ],
                    'sources' => [self::P20 . 'surat-1-ahli-materi', self::P20 . 'surat-3-ahli-media', self::P20 . 'akses-untuk-validator'],
                ],
                [
                    'id'       => 'istilah',
                    'title'    => 'Daftar istilah',
                    'lead'     => 'Istilah yang dipakai di panduan ini. Istilah bergaris titik di teks bisa diketuk untuk melihat artinya.',
                    'glossary' => true,
                ],
            ],
        ];
    }
}
