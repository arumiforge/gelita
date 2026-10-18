<?php

namespace App\Libraries;

/**
 * Single catalogue of the image slots the game reads, for the admin panel
 * (Gambar & mockup, the slot detail page, Kelengkapan aset).
 *
 * Each slot says what the picture should show, where it appears (screen
 * name and public path, so the admin can open the page and check it), the
 * file format, whether it needs transparency, and what the game shows while
 * it is empty. File sizes stay in Config\Gelita::$assetSizes (enforced on
 * upload) and $assetDisplaySizes; sizes that are only advice live here as
 * `recommended`.
 *
 * Prompts, reference images, and the visual style guide are not repeated
 * here: they live in the team's Katalog Aset (gelita-katalog/index.html,
 * source gelita-katalog/data.json), searched by asset key. Its images are
 * installed with `php spark gelita:katalog:impor`. Style decisions of
 * 11 October 2026: stylized 3D art, Jaka with a red scarf, WebP as the
 * default raster format (decision log in docs/perombakan-kelas5/).
 *
 * Region slots use `{wilayah}`/`{Wilayah}` in screens, and challenge slots
 * `{node}` and `{n}`; describe() fills them from the asset key. Slot text is
 * admin-facing Indonesian, like the rest of the panel.
 */
final class ImageSlots
{
    /** Group code → panel heading, in panel order. */
    public const GROUPS = [
        'brand'      => 'Logo & tombol',
        'app'        => 'Ikon aplikasi',
        'background' => 'Latar layar',
        'map'        => 'Peta',
        'region'     => 'Wilayah: latar, peta, lencana',
        'character'  => 'Tokoh',
        'avatar'     => 'Avatar',
        'result'     => 'Layar tantangan selesai',
        'challenge'  => 'Gambar tantangan',
        'library'    => 'Pustaka',
        'other'      => 'Lainnya',
    ];

    /** Slot priority: must be uploaded before the study, optional, or not read by the game. */
    public const PRIORITIES = [
        'wajib'    => 'Wajib',
        'opsional' => 'Opsional',
        'unused'   => 'Belum dipakai permainan',
    ];

    /** Challenge kinds by node sequence (bank kelas 5: same order in every region). */
    private const NODE_KINDS = [
        1 => 'Susun Gambar',
        2 => 'Isi Rumpang',
        3 => 'Benar atau Salah',
        4 => 'Cari Objek',
    ];

    /**
     * Pattern (exact key or fnmatch pattern) → slot. Exact keys are matched
     * first, then patterns in this order.
     *
     * screens: list of [screen name, public path or null]
     *
     * @var array<string, array{group: string, label: string, content: string, screens: list<array{0: string, 1: string|null}>, format: string, transparent: bool, types?: list<string>, fallback: string, priority: string, tips?: string, recommended?: array{0: int, 1: int}, admin?: array{0: string, 1: string}}>
     */
    private const SLOTS = [
        // ------------------------------------------------------------ brand
        'ui.logo-hero' => [
            'group'       => 'brand',
            'label'       => 'Logo GELITA besar (landscape)',
            'content'     => 'Logo GELITA dari Katalog Aset (reference.09): tulisan GELITA emas timbul dengan aksen biru kehijauan dan lentera kecil, tanpa tagline, sama untuk kedua bahasa, berlatar transparan.',
            'screens'     => [
                ['Halaman awal (logo besar di atas tombol Mulai)', '/'],
                ['Halaman masuk panel staf (logo di tengah, di atas formulir)', '/admin/login'],
            ],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Emblem logo (ui.logo), lalu tulisan GELITA biasa',
            'priority'    => 'wajib',
            'tips'        => 'Sisakan ruang kosong ± 48 px di tepi. Pastikan tetap terbaca saat diperkecil.',
        ],
        'ui.logo' => [
            'group'       => 'brand',
            'label'       => 'Emblem logo (tanpa tulisan)',
            'content'     => 'Emblem lentera emas GELITA tanpa tulisan (Katalog Aset reference.16, lentera dari logo), karena tulisan GELITA sudah tampil di sebelahnya.',
            'screens'     => [
                ['Pita atas layar akun (di tengah, di samping tombol Kembali)', '/mulai'],
                ['Pita atas langkah daftar', '/daftar/1'],
                ['Cadangan logo halaman awal bila logo besar belum ada', '/'],
                ['Cadangan logo halaman masuk panel staf bila logo besar belum ada', '/admin/login'],
            ],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Tulisan GELITA',
            'priority'    => 'wajib',
            'recommended' => [512, 512],
            'tips'        => 'Harus tetap jelas saat tampil kecil (± 36 px).',
        ],
        'ui.btn-start' => [
            'group'       => 'brand',
            'label'       => 'Tombol MULAI bergambar (bahasa Indonesia)',
            'content'     => 'Gambar tombol jadi beserta tulisannya (Katalog Aset reference.10): kapsul berisi enamel biru tua, bingkai emas berukir dengan ornamen Jawa kecil di kedua ujung, dan tulisan MULAI emas timbul berhuruf serif di tengah.',
            'screens'     => [['Halaman awal, tombol utama (bahasa Indonesia)', '/']],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Tombol biasa bertulisan Mulai',
            'priority'    => 'wajib',
            'tips'        => 'Hanya ada dua tombol: ui.btn-start (MULAI) dan ui.btn-start.en (START). Keduanya harus berukuran dan berletak sama; periksa ejaan tulisannya.',
        ],
        'ui.btn-start.*' => [
            'group'       => 'brand',
            'label'       => 'Tombol START bergambar (bahasa Inggris)',
            'content'     => 'Sama persis dengan ui.btn-start (kapsul biru tua berbingkai emas), hanya tulisannya START.',
            'screens'     => [['Halaman awal saat bahasa permainan diganti ke bahasa itu', '/']],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Tombol MULAI bahasa Indonesia (ui.btn-start), lalu tombol biasa',
            'priority'    => 'wajib',
        ],
        'ui.lang.id' => [
            'group'       => 'brand',
            'label'       => 'Ikon bahasa Indonesia (ID)',
            'content'     => 'Ikon bundar Katalog Aset icon.language-id: bendera Indonesia dalam lingkaran berbingkai emas, tanpa tulisan, berlatar transparan.',
            'screens'     => [['Pita atas semua layar, pemilih bahasa di kanan atas', '/'], ['Pita atas layar akun', '/mulai']],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 40 KB',
            'transparent' => true,
            'fallback'    => 'Tombol tulisan ID / EN (dipakai untuk kedua bahasa selama salah satu ikon belum ada)',
            'priority'    => 'opsional',
            'tips'        => 'Pasangan ui.lang.en: ukuran dan bingkai harus sama. Tampil 32 dp; bahasa yang tidak dipilih tampil redup.',
        ],
        'ui.lang.en' => [
            'group'       => 'brand',
            'label'       => 'Ikon bahasa Inggris (EN)',
            'content'     => 'Sama persis dengan ui.lang.id (Katalog Aset icon.language-en), hanya benderanya bendera Inggris (Union Jack).',
            'screens'     => [['Pita atas semua layar, pemilih bahasa di kanan atas', '/'], ['Pita atas layar akun', '/mulai']],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 40 KB',
            'transparent' => true,
            'fallback'    => 'Tombol tulisan ID / EN (dipakai untuk kedua bahasa selama salah satu ikon belum ada)',
            'priority'    => 'opsional',
        ],
        'ui.placeholder' => [
            'group'       => 'brand',
            'label'       => 'Gambar pengganti',
            'content'     => 'Gambar netral yang tampil sementara bila sebuah gambar soal, bacaan, peta wilayah, atau halaman Pustaka belum diunggah. Polos dan tenang, misalnya bingkai perkamen dengan lambang GELITA kecil di tengah, tanpa tulisan.',
            'screens'     => [
                ['Tantangan dengan gambar soal yang belum diunggah', '/tantangan/temanggung/1'],
                ['Peta wilayah yang gambarnya belum diunggah', '/wilayah/temanggung'],
                ['Pustaka', '/pustaka'],
            ],
            'format'      => 'SVG, PNG, atau WebP',
            'transparent' => false,
            'types'       => ['svg', 'png', 'webp'],
            'fallback'    => 'Berkas bawaan assets/ui/placeholder.svg',
            'priority'    => 'opsional',
            'tips'        => 'Hanya cadangan: sebelum penelitian semua gambar asli tetap perlu diunggah.',
        ],

        // ----------------------------------------------------------- result
        // Skin of the "Tantangan selesai" screen (/selesai/{id}), redesign of
        // 18 October 2026 from gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung.
        // Every label stays text: the pictures carry no writing.
        'ui.result-panel' => [
            'group'       => 'result',
            'label'       => 'Panel hasil tantangan',
            'content'     => 'Panel kosong biru tua berbingkai emas ganda dengan ukiran awan Jawa di sudut dan siluet gunung samar di dalamnya, tanpa tulisan, bintang, tombol, atau kartu (Katalog Aset ui.result-panel). Berlatar transparan di luar panel.',
            'screens'     => [['Tantangan selesai, panel tengah', null]],
            'format'      => 'WebP berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Panel biru tua polos berbingkai emas',
            'priority'    => 'opsional',
            'tips'        => 'Tampil 1017 × 495 dp. Bagian dalam harus tenang, karena judul, bintang, kartu, dan tombol tampil di atasnya.',
        ],
        'ui.result-button-primary' => [
            'group'       => 'result',
            'label'       => 'Tombol utama (Berikutnya)',
            'content'     => 'Kapsul emas timbul dengan motif awan halus di kedua ujung, tanpa tulisan dan tanpa panah (Katalog Aset ui.result-button-primary).',
            'screens'     => [['Tantangan selesai, tombol kanan bawah', null]],
            'format'      => 'WebP berlatar transparan, paling besar ± 60 KB',
            'transparent' => true,
            'fallback'    => 'Tombol emas biasa',
            'priority'    => 'opsional',
        ],
        'ui.result-button-secondary' => [
            'group'       => 'result',
            'label'       => 'Tombol peta wilayah',
            'content'     => 'Kapsul biru tua berbingkai emas tipis, tanpa tulisan dan tanpa panah (Katalog Aset ui.result-button-secondary).',
            'screens'     => [['Tantangan selesai, tombol kiri bawah', null]],
            'format'      => 'WebP berlatar transparan, paling besar ± 40 KB',
            'transparent' => true,
            'fallback'    => 'Tombol biru tua berbingkai emas',
            'priority'    => 'opsional',
        ],
        'ui.result-button-detail' => [
            'group'       => 'result',
            'label'       => 'Tombol Detail hasil',
            'content'     => 'Kapsul biru tua berbingkai emas yang lebih pendek, sekeluarga dengan tombol peta wilayah, tanpa tulisan (Katalog Aset ui.result-button-detail).',
            'screens'     => [['Tantangan selesai, tombol tengah bawah', null]],
            'format'      => 'WebP berlatar transparan, paling besar ± 30 KB',
            'transparent' => true,
            'fallback'    => 'Tombol biru tua berbingkai emas',
            'priority'    => 'opsional',
        ],
        'ui.result-hud-control' => [
            'group'       => 'result',
            'label'       => 'Alas tombol bundar HUD',
            'content'     => 'Lingkaran biru tua berbingkai emas, kosong di tengah (ikon bahasa, suara, dan menu digambar permainan di atasnya). Katalog Aset ui.result-hud-control.',
            'screens'     => [['Tantangan selesai, tombol suara dan menu di kanan atas', null]],
            'format'      => 'WebP berlatar transparan, paling besar ± 30 KB',
            'transparent' => true,
            'fallback'    => 'Lingkaran biru tua berbingkai emas',
            'priority'    => 'opsional',
        ],
        'ui.result-star' => [
            'group'       => 'result',
            'label'       => 'Bintang hasil (terisi)',
            'content'     => 'Satu bintang emas timbul yang berkilau, tanpa tulisan (Katalog Aset ui.result-star). Bintang kosong digambar permainan sebagai garis.',
            'screens'     => [['Tantangan selesai, bintang di bawah judul', null]],
            'format'      => 'WebP berlatar transparan, paling besar ± 30 KB',
            'transparent' => true,
            'fallback'    => 'Ikon bintang emas',
            'priority'    => 'opsional',
        ],
        'ui.result-medal' => [
            'group'       => 'result',
            'label'       => 'Medali Serpihan Cahaya',
            'content'     => 'Medali bundar berbingkai biru tua dengan tengah krem dan kristal serpihan cahaya emas. Diisi dari Katalog Aset avatar.2 (keputusan pemilik 18 Oktober 2026) tetapi slotnya sendiri, jadi mengganti avatar 2 tidak mengubah layar ini.',
            'screens'     => [['Tantangan selesai, medali di kiri panel', null]],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 120 KB',
            'transparent' => true,
            'fallback'    => 'Bola cahaya dengan ikon lentera',
            'priority'    => 'opsional',
            'tips'        => 'Tampil 223 dp dengan cahaya tambahan dari permainan.',
        ],

        // ------------------------------------------------------------ app
        'app.favicon' => [
            'group'       => 'app',
            'label'       => 'Ikon tab peramban (favicon)',
            'content'     => 'Lambang GELITA yang sederhana dan tebal, tetap jelas saat tampil sangat kecil di tab peramban (16–48 piksel). Tanpa tulisan.',
            'screens'     => [['Tab peramban di semua halaman permainan dan panel', '/']],
            'format'      => 'PNG persegi, berlatar transparan atau warna gelap',
            'transparent' => true,
            'types'       => ['png'],
            'fallback'    => 'Ikon bawaan favicon.ico',
            'priority'    => 'opsional',
        ],
        'app.apple-touch' => [
            'group'       => 'app',
            'label'       => 'Ikon layar utama iPhone dan iPad',
            'content'     => 'Lambang GELITA di atas latar biru tua penuh (tanpa transparan); iOS membulatkan sudutnya sendiri.',
            'screens'     => [['Layar utama iPhone/iPad sesudah "Tambahkan ke Layar Utama"', '/']],
            'format'      => 'PNG persegi tanpa transparan',
            'transparent' => false,
            'types'       => ['png'],
            'fallback'    => 'Ikon bawaan assets/app/apple-touch-icon.png',
            'priority'    => 'opsional',
        ],
        'app.icon-192' => [
            'group'       => 'app',
            'label'       => 'Ikon aplikasi 192',
            'content'     => 'Lambang GELITA untuk aplikasi terpasang (Android, Chromebook, Windows).',
            'screens'     => [['Layar utama dan daftar aplikasi sesudah GELITA dipasang', '/manifest.json']],
            'format'      => 'PNG persegi',
            'transparent' => true,
            'types'       => ['png'],
            'fallback'    => 'Ikon bawaan assets/app/icon-192.png',
            'priority'    => 'opsional',
        ],
        'app.icon-512' => [
            'group'       => 'app',
            'label'       => 'Ikon aplikasi 512',
            'content'     => 'Lambang GELITA yang sama dengan ikon 192, dalam ukuran besar (layar pembuka aplikasi terpasang).',
            'screens'     => [['Layar pembuka dan daftar aplikasi sesudah GELITA dipasang', '/manifest.json']],
            'format'      => 'PNG persegi',
            'transparent' => true,
            'types'       => ['png'],
            'fallback'    => 'Ikon bawaan assets/app/icon-512.png',
            'priority'    => 'opsional',
        ],
        'app.icon-maskable' => [
            'group'       => 'app',
            'label'       => 'Ikon aplikasi Android (maskable)',
            'content'     => 'Lambang GELITA di tengah latar biru tua penuh. Android memotong ikon menjadi lingkaran atau bentuk lain, jadi lambang harus muat di lingkaran tengah 80%.',
            'screens'     => [['Layar utama Android sesudah GELITA dipasang', '/manifest.json']],
            'format'      => 'PNG persegi tanpa transparan',
            'transparent' => false,
            'types'       => ['png'],
            'fallback'    => 'Ikon bawaan assets/app/icon-maskable-512.png',
            'priority'    => 'opsional',
            'tips'        => 'Zona aman: lingkaran berdiameter 80% kanvas (410 piksel). Bagian di luarnya bisa terpotong.',
        ],

        // ------------------------------------------------------- background
        'bg.welcome' => [
            'group'       => 'background',
            'label'       => 'Latar halaman awal & akun',
            'content'     => 'Lanskap Kedu yang tenang (lembah dan gunung saat senja/fajar), langit bagian tengah-atas kosong untuk logo, bagian tengah-bawah tenang untuk tombol. Tanpa tokoh dan tulisan.',
            'screens'     => [
                ['Halaman awal', '/'],
                ['Pilih masuk atau daftar', '/mulai'],
                ['Gerbang (lanjut atau mulai baru)', '/gerbang'],
                ['Cadangan latar cerita pembuka & penutup', '/intro'],
            ],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar warna gelap polos',
            'priority'    => 'wajib',
            'tips'        => 'Latar ini paling sering dilihat; jaga agar tidak ramai di tengah.',
        ],
        'bg.auth' => [
            'group'       => 'background',
            'label'       => 'Latar masuk, daftar, dan ganti kata sandi',
            'content'     => 'Sejak 18 Oktober 2026 halaman masuk, daftar, dan ganti kata sandi memakai dua gambar tetap yang ikut terpasang bersama permainan (pohon berlentera dan gerbang bercahaya), jadi gambar ini belum tampil di permainan.',
            'screens'     => [],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Tidak ada (belum dipakai)',
            'priority'    => 'unused',
        ],
        'bg.intro' => [
            'group'       => 'background',
            'label'       => 'Latar cerita pembuka & penutup',
            'content'     => 'Suasana sinematik Kedu malam/fajar dengan lentera dan kabut tipis. Sisi kiri dan bagian bawah tenang, karena tokoh berdiri di kiri dan kotak teks ada di bawah.',
            'screens'     => [
                ['Cerita pembuka (slide tanpa latar sendiri)', '/intro'],
                ['Cerita penutup', '/penutup'],
            ],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar halaman awal (bg.welcome), lalu warna polos',
            'priority'    => 'wajib',
            'tips'        => 'Gambar diperbesar pelan (efek Ken Burns), jadi jangan taruh detail penting di tepi.',
        ],
        'bg.map' => [
            'group'       => 'background',
            'label'       => 'Latar Peta Kedu, Profil, dan rak Pustaka',
            'content'     => 'Latar tepi meja/perkamen gelap di sekitar peta. Bagian tengah akan tertutup bingkai peta, jadi detail cukup di tepi.',
            'screens'     => [
                ['Peta Kedu', '/peta'],
                ['Rak Pustaka', '/pustaka'],
                ['Profil siswa', '/profil'],
                ['Cadangan latar Balai Refleksi', '/refleksi'],
            ],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar warna gelap polos',
            'priority'    => 'wajib',
        ],
        'bg.reflection' => [
            'group'       => 'background',
            'label'       => 'Latar Balai Refleksi',
            'content'     => 'Pendapa/balai yang hangat dengan cahaya lentera. Panel Mbah Kedu dan formulir refleksi tampil di atasnya, jadi tengah gambar tidak ramai.',
            'screens'     => [['Balai Refleksi', '/refleksi']],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar Peta Kedu (bg.map), lalu warna polos',
            'priority'    => 'wajib',
        ],
        'bg.loading' => [
            'group'       => 'background',
            'label'       => 'Latar tirai "Membuka Peta Kedu"',
            'content'     => 'Suasana cahaya dan kabut lembut dengan nada sedang (tidak gelap). Bagian tengah kosong tanpa detail, karena lentera, serpihan, dan judul tirai tampil di sana.',
            'screens'     => [['Tirai pemuatan sesudah cerita pembuka, sebelum Peta Kedu', '/peta']],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Gradien cahaya bawaan',
            'priority'    => 'wajib',
        ],

        // -------------------------------------------------------------- map
        'bg.story.*' => [
            'group'       => 'background',
            'label'       => 'Latar slide cerita',
            'content'     => 'Adegan layar penuh untuk satu slide cerita pembuka atau penutup, sesuai isi ucapannya. Tanpa tokoh (Jaka dan Mbah Kedu digambar permainan di atasnya) dan tanpa tulisan. Bagian bawah tenang: kotak narasi menutupinya.',
            'screens'     => [['Cerita pembuka', '/intro'], ['Cerita penutup', '/penutup']],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar cerita umum (bg.intro), lalu latar halaman awal',
            'priority'    => 'opsional',
            'tips'        => 'Dipasang per slide di halaman Konten → Cerita pembuka / Penutup.',
            'admin'       => ['Atur di halaman cerita', 'admin/konten/dialog/0'],
        ],
        'map.kedu' => [
            'group'       => 'map',
            'label'       => 'Peta Karesidenan Kedu',
            'content'     => 'Peta relief 3D dataran Kedu dilihat dari atas (biru tua dan hijau redup, aksen emas) dengan Dieng, Sindoro–Sumbing, Merapi–Merbabu, Borobudur, dan Menoreh. Tanpa tulisan, nama tempat, atau penanda: pin wilayah digambar permainan di atasnya. Area di sekitar titik pin dibiarkan tenang.',
            'screens'     => [['Peta Kedu (tiga pin wilayah di atas gambar)', '/peta']],
            'format'      => 'WebP atau JPG, paling besar ± 600 KB',
            'transparent' => false,
            'fallback'    => 'Peta polos bawaan dengan pin saja',
            'priority'    => 'wajib',
            'recommended' => [1400, 900],
            'tips'        => 'Rasio 14:9; posisi pin wilayah ada di bagian "Titik pin" halaman ini.',
        ],

        'map.kedu-diorama' => [
            'group'       => 'map',
            'label'       => 'Diorama tiga wilayah Peta Kedu',
            'content'     => 'Diorama 3D Dataran Kedu dari atas yang terbagi menjadi tiga pulau: Wonosobo di kiri, Temanggung di kanan atas, Magelang di kanan bawah, dipisahkan celah air. Tanpa tulisan, pin, atau jalur: permainan menggambar pin, nama wilayah, jalur putus-putus, dan meredupkan wilayah yang terkunci.',
            'screens'     => [['Peta Kedu', '/peta']],
            'format'      => 'WebP 1774 × 887 (rasio 2:1), paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Peta Kedu lama (map.kedu) dengan pin di titik masing-masing wilayah',
            'priority'    => 'opsional',
            'tips'        => 'Bentuk potongan tiap wilayah diukur pada gambar Katalog Aset (mockup Peta Kedu kandidat 04). Gambar pengganti harus berkomposisi sama; bila tidak, potongan dan letak pin perlu diukur ulang oleh pengembang.',
        ],
        'ui.pin' => [
            'group'       => 'map',
            'label'       => 'Pin wilayah (semua kota)',
            'content'     => 'Penanda peta untuk wilayah yang belum punya pin sendiri: lentera atau tugu kecil bergaya GELITA, dilihat dari depan, berlatar transparan, tanpa tulisan. Status (terkunci, tuntas) ditambahkan permainan di atasnya.',
            'screens'     => [['Peta Kedu', '/peta']],
            'format'      => 'PNG atau WebP berlatar transparan, paling besar ± 60 KB',
            'transparent' => true,
            'fallback'    => 'Ikon pin bawaan (lingkaran emas)',
            'priority'    => 'opsional',
            'tips'        => 'Tampil 56 dp: bentuk sederhana dan tebal. Wilayah yang terkunci digambar abu-abu oleh permainan.',
        ],
        'ui.lantern' => [
            'group'       => 'map',
            'label'       => 'Lentera (HUD dan tirai peta)',
            'content'     => 'Lentera Jawa berlatar transparan, menghadap depan, tanpa api yang terlalu terang (permainan menambah cahaya sesuai jumlah wilayah tuntas).',
            'screens'     => [['HUD (lentera serpihan)', '/peta'], ['Tirai "Membuka Peta Kedu"', '/peta']],
            'format'      => 'PNG atau WebP berlatar transparan, paling besar ± 60 KB',
            'transparent' => true,
            'fallback'    => 'Ikon lentera bawaan',
            'priority'    => 'opsional',
        ],
        'map.pin.*' => [
            'group'       => 'region',
            'label'       => 'Pin peta {Wilayah}',
            'content'     => 'Penanda khas {Wilayah} di peta Kedu (mis. ikon kecil yang mewakili kota itu), berlatar transparan, tanpa tulisan. Status terkunci dan tuntas ditambahkan permainan.',
            'screens'     => [['Peta Kedu', '/peta']],
            'format'      => 'PNG atau WebP berlatar transparan, paling besar ± 60 KB',
            'transparent' => true,
            'fallback'    => 'Pin wilayah umum (ui.pin), lalu ikon bawaan',
            'priority'    => 'opsional',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],
        'map.lantern.*' => [
            'group'       => 'region',
            'label'       => 'Lentera Kenali {Wilayah}',
            'content'     => 'Lentera kecil khas {Wilayah}, berlatar transparan, tanpa tulisan. Sejak Peta Kedu dirombak (18 Oktober 2026) tombol Kenali memakai ikon buku dan kaca pembesar, jadi gambar ini belum tampil di permainan.',
            'screens'     => [],
            'format'      => 'PNG atau WebP berlatar transparan, paling besar ± 40 KB',
            'transparent' => true,
            'fallback'    => 'Tidak ada (belum dipakai)',
            'priority'    => 'unused',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],
        'bg.result.*' => [
            'group'       => 'region',
            'label'       => 'Latar tantangan selesai {Wilayah}',
            'content'     => 'Lanskap khas {Wilayah} saat senja yang hangat, tanpa tokoh dan tanpa tulisan. Bagian tengah boleh ramai karena tertutup panel hasil; tepi kiri, kanan, dan atas tetap terlihat (Katalog Aset bg.result.temanggung sebagai contoh).',
            'screens'     => [['Tantangan selesai di wilayah ini', null]],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar wilayah (bg.{wilayah}.region)',
            'priority'    => 'opsional',
        ],
        'bg.loading.*' => [
            'group'       => 'region',
            'label'       => 'Latar tirai menuju {Wilayah}',
            'content'     => 'Pemandangan khas {Wilayah} untuk tirai "Menuju {Wilayah}…" yang tampil sebentar saat wilayah dibuka. Tanpa tokoh dan tanpa tulisan; bagian tengah tenang karena judul tirai tampil di sana.',
            'screens'     => [['Tirai menuju wilayah (dari peta Kedu)', '/peta']],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar wilayah (bg.{wilayah}.region), lalu gradien cahaya',
            'priority'    => 'opsional',
            'tips'        => 'Tirai hanya tampil 0,6–2,4 detik: gambar yang sederhana lebih cepat dikenali.',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],

        // ----------------------------------------------------------- region
        'bg.*.region' => [
            'group'       => 'region',
            'label'       => 'Latar wilayah {Wilayah}',
            'content'     => 'Lanskap khas {Wilayah} yang tenang: bentuk besar, kontras sedang, fokus di tengah-atas. Bentuk situs bersejarah mengikuti foto rujukan. Tanpa tokoh dan tulisan.',
            'screens'     => [
                ['Dialog masuk wilayah', '/dialog/{wilayah}'],
                ['Peta wilayah', '/wilayah/{wilayah}'],
                ['Kartu misi & layar tantangan (bila tantangan tanpa latar sendiri)', '/misi/{wilayah}/1'],
                ['Wilayah tuntas', '/tuntas/{wilayah}'],
            ],
            'format'      => 'WebP atau JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar warna gelap polos',
            'priority'    => 'wajib',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],
        'map.region.*' => [
            'group'       => 'region',
            'label'       => 'Peta wilayah {Wilayah}',
            'content'     => 'Peta ilustrasi 3D wilayah {Wilayah} dilihat dari atas, dengan jalan setapak yang melewati empat lapangan kosong untuk tantangan, berurutan. Tanpa tulisan, angka, atau penanda: tombol tantangan digambar permainan di atasnya.',
            'screens'     => [['Peta wilayah (empat tombol tantangan di atas gambar)', '/wilayah/{wilayah}']],
            'format'      => 'WebP atau JPG, paling besar ± 600 KB',
            'transparent' => false,
            'fallback'    => 'Peta polos bawaan dengan tombol tantangan saja',
            'priority'    => 'wajib',
            'tips'        => 'Rasio 14:9; posisi keempat tantangan ada di bagian "Titik pin" halaman ini. Bila gambar baru punya lapangan di tempat lain, pindahkan tombolnya di halaman tantangan agar duduk di lapangan itu.',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],
        'reward.badge.*' => [
            'group'       => 'region',
            'label'       => 'Lencana wilayah {Wilayah}',
            'content'     => 'Medali bundar memenuhi kanvas: tepi emas tebal, isi biru tua, satu simbol besar khas {Wilayah}. Tanpa tulisan. Harus terbaca saat tampil kecil (± 72 px).',
            'screens'     => [['Profil siswa, sesudah wilayah tuntas', '/profil']],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 60 KB',
            'transparent' => true,
            'fallback'    => 'Lencana ikon bawaan',
            'priority'    => 'wajib',
            'tips'        => 'Ketiga lencana satu keluarga: tepi sama, hanya simbolnya berbeda.',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],

        // -------------------------------------------------------- character
        'char.jaka.*' => [
            'group'       => 'character',
            'label'       => 'Jaka · pose {pose} · gambar {frame}',
            'content'     => 'Jaka sesuai lembar tokoh Katalog Aset (reference.01): anak laki-laki ± 11 tahun bergaya 3D, rambut cokelat berjambul, kemeja krem, syal merah, celana cokelat, kain batik di pinggang, sandal, ransel kulit, membawa lentera. Seluruh badan, pose {pose}, menghadap ke kanan layar. Latar transparan, kaki di garis bawah yang sama untuk semua gambar.',
            'screens'     => [
                ['Baris cerita yang memakai pose ini: cerita pembuka, dialog dan wilayah tuntas, penutup (rinciannya di halaman detail)', '/intro'],
            ],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Pose diam (idle), lalu inisial nama tokoh',
            'priority'    => 'wajib',
            'tips'        => 'Sampai 24 gambar per pose, diputar berurutan dan berulang terus. Gambar 1 wajib dan harus terbaca sendiri, karena hanya gambar itu yang tampil bila gerak dikurangi. Semua gambar pose dapat diunggah sekaligus di halaman ini.',
        ],
        'ui.jaka-avatar' => [
            'group'       => 'character',
            'label'       => 'Avatar Jaka (potret cerita Peta Kedu)',
            'content'     => 'Potret dada Jaka sesuai Katalog Aset reference.03: anak laki-laki ± 11 tahun bergaya 3D, rambut cokelat berjambul, kemeja krem, syal merah, tali ransel kulit, wajah ceria. Kepala dan bahu di tengah, berlatar transparan, tanpa tulisan.',
            'screens'     => [['Pita cerita Jaka di bawah Peta Kedu', '/peta']],
            'format'      => 'WebP atau PNG berlatar transparan, persegi, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Huruf pertama nama Jaka dalam lingkaran emas',
            'priority'    => 'opsional',
            'tips'        => 'Tampil dalam lingkaran 120 dp, jadi wajah harus terbaca kecil. Bukan gambar gerak: pita cerita sengaja tidak memakai gambar pose Jaka.',
        ],
        'char.kedu.*' => [
            'group'       => 'character',
            'label'       => 'Mbah Kedu · pose {pose} · gambar {frame}',
            'content'     => 'Mbah Kedu sesuai Katalog Aset (reference.02, gambar gerak 12 Oktober 2026): kakek penjaga lentera bergaya 3D, ikat kepala batik cokelat, kemeja krem berkerah tegak dengan rompi batik cokelat, celana cokelat longgar, sandal, tongkat kayu, tanpa syal merah. Seluruh badan, pose {pose}, menghadap ke kanan layar (permainan mencerminkannya saat berdiri di kanan). Latar transparan, kaki di garis bawah yang sama untuk semua gambar.',
            'screens'     => [
                ['Baris cerita yang memakai pose ini: cerita pembuka, dialog dan wilayah tuntas, penutup (rinciannya di halaman detail)', '/intro'],
            ],
            'format'      => 'WebP atau PNG berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Pose diam (idle), lalu inisial nama tokoh',
            'priority'    => 'wajib',
            'tips'        => 'Sampai 24 gambar per pose, diputar berurutan dan berulang terus. Jangan ada tulisan atau lambang asimetris di pakaian, karena gambar dicerminkan. Semua gambar pose dapat diunggah sekaligus di halaman ini.',
        ],

        // -------------------------------------------------------- challenge
        // ------------------------------------------------------------ avatar
        'avatar.*' => [
            'group'       => 'avatar',
            'label'       => 'Avatar {n}',
            'content'     => 'Medali bundar berbingkai biru tua dengan tengah krem dan satu lambang emas khas Kedu (mis. lentera, stupa, gunung kembar, buah carica), tanpa wajah agar setiap anak dapat memilih tanpa soal rupa atau jenis kelamin. Berlatar transparan, tanpa tulisan. Kesepuluh avatar satu keluarga: bingkai, warna, dan pencahayaan sama.',
            'screens'     => [['Pilihan avatar di pendaftaran', '/daftar/1'], ['Profil (Ganti avatar)', '/profil'], ['Kartu nama di HUD', '/peta']],
            'format'      => 'PNG atau WebP berlatar transparan, paling besar ± 120 KB',
            'transparent' => true,
            'fallback'    => 'Huruf pertama nama siswa; avatar yang kosong tidak ditawarkan',
            'priority'    => 'opsional',
            'tips'        => 'Tampil kecil di HUD (36 dp) dan di profil (72 dp): lambang harus besar dan jelas, isi kanvas sampai tepi lingkaran.',
        ],

        'challenge.*.scene' => [
            'group'       => 'challenge',
            'label'       => 'Adegan Cari Objek {Wilayah}',
            'content'     => 'Adegan pasar/galeri budaya {Wilayah} dengan rak atau meja kosong; benda yang dicari ditempel permainan di atasnya.',
            'screens'     => [['Tantangan Cari Objek {Wilayah}', '/tantangan/{wilayah}/{n}']],
            'format'      => 'WebP atau JPG kualitas ± 85',
            'transparent' => false,
            'fallback'    => 'Gambar contoh bertulisan GAMBAR CONTOH',
            'priority'    => 'wajib',
            'tips'        => 'Jangan gambar benda yang dicari di adegan; benda dipasang terpisah.',
            'admin'       => ['Atur posisi benda di halaman tantangan', 'admin/konten/node/{node_id}'],
        ],
        'challenge.item.*-1-*' => [
            'group'       => 'challenge',
            'label'       => 'Gambar Susun Gambar {Wilayah}',
            'content'     => 'Gambar utuh yang dipotong menjadi keping puzzle.',
            'screens'     => [['Tantangan Susun Gambar {Wilayah}', '/tantangan/{wilayah}/{n}']],
            'format'      => 'WebP atau JPG kualitas ± 85, paling besar ± 350 KB',
            'transparent' => false,
            'fallback'    => 'Gambar contoh bertulisan GAMBAR CONTOH',
            'priority'    => 'wajib',
            'tips'        => 'Persegi; detail tersebar merata agar setiap keping punya ciri.',
            'admin'       => ['Buka soal tantangan', 'admin/konten/node/{node_id}'],
        ],
        'challenge.item.*-3-*' => [
            'group'       => 'challenge',
            'label'       => 'Gambar pernyataan Benar atau Salah {Wilayah}',
            'content'     => 'Gambar pendukung satu kartu pernyataan.',
            'screens'     => [['Tantangan Benar atau Salah {Wilayah}', '/tantangan/{wilayah}/{n}']],
            'format'      => 'WebP atau JPG kualitas ± 85',
            'transparent' => false,
            'fallback'    => 'Gambar contoh bertulisan GAMBAR CONTOH',
            'priority'    => 'wajib',
            'tips'        => 'Kartu memotong gambar; letakkan subjek di pita tengah mendatar.',
            'admin'       => ['Buka soal tantangan', 'admin/konten/node/{node_id}'],
        ],
        'challenge.item.*-4-*' => [
            'group'       => 'challenge',
            'label'       => 'Benda Cari Objek {Wilayah}',
            'content'     => 'Satu benda utuh tanpa latar, dipasang permainan di atas adegan.',
            'screens'     => [['Tantangan Cari Objek {Wilayah}', '/tantangan/{wilayah}/{n}']],
            'format'      => 'WebP atau PNG berlatar transparan',
            'transparent' => true,
            'fallback'    => 'Gambar contoh bertulisan nama benda',
            'priority'    => 'wajib',
            'tips'        => 'Semua benda satu adegan (yang dicari dan pengecoh) satu gaya dan sudut pandang, agar pengecoh tidak mudah dikenali.',
            'admin'       => ['Atur posisi benda di halaman tantangan', 'admin/konten/node/{node_id}'],
        ],
        'challenge.item.*' => [
            'group'       => 'challenge',
            'label'       => 'Gambar soal {Wilayah}',
            'content'     => 'Gambar pendukung soal.',
            'screens'     => [['Tantangan {Wilayah}', '/tantangan/{wilayah}/{n}']],
            'format'      => 'WebP, PNG, atau JPG',
            'transparent' => false,
            'fallback'    => 'Soal tampil tanpa gambar',
            'priority'    => 'opsional',
            'admin'       => ['Buka soal tantangan', 'admin/konten/node/{node_id}'],
        ],

        // ---------------------------------------------------------- library
        'library.*' => [
            'group'       => 'library',
            'label'       => 'Gambar Pustaka',
            'content'     => 'Foto atau ilustrasi halaman Pustaka. Bawaannya memakai tautan Wikimedia Commons; ilustrasi AI dari Katalog Aset dipasang sementara lewat gelita:katalog:impor dan dapat diganti foto asli kapan saja.',
            'screens'     => [['Pustaka Kedu', '/pustaka']],
            'format'      => 'WebP, JPG, atau PNG',
            'transparent' => false,
            'fallback'    => 'Gambar dari tautan bawaan',
            'priority'    => 'opsional',
            'recommended' => [960, 640],
        ],
    ];

    /** File type code → MIME type, for the per-slot `types` rule. */
    public const TYPE_MIMES = [
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'webp' => 'image/webp',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
    ];

    /** Human pose names for the slot text. */
    private const POSES = [
        'idle'       => 'diam',
        'happy'      => 'senang',
        'bow'        => 'membungkuk hormat',
        'sad'        => 'sedih',
        'afraid'     => 'takut',
        'determined' => 'bertekad',
        'smile'      => 'tersenyum',
        'worried'    => 'cemas',
        'weak'       => 'lemah',
    ];

    /**
     * Slot pattern that matches a key, or null for keys the game does not
     * read by name (scanned files, uploads under a new key).
     */
    public static function patternFor(string $assetKey): ?string
    {
        if (isset(self::SLOTS[$assetKey])) {
            return $assetKey;
        }

        foreach (array_keys(self::SLOTS) as $pattern) {
            if (str_contains($pattern, '*') && fnmatch($pattern, $assetKey)) {
                return $pattern;
            }
        }

        return null;
    }

    /**
     * Image file types an upload to this key may have (enforced by
     * MediaStore::store() and admin/media-upload.js), or null for keys that
     * are not a slot. A slot's own `types` wins; otherwise a transparent slot
     * takes PNG or WebP and any other slot JPG, PNG, or WebP.
     *
     * @return list<string>|null codes from TYPE_MIMES
     */
    public static function types(string $assetKey): ?array
    {
        $pattern = self::patternFor($assetKey);

        if ($pattern === null) {
            return null;
        }

        $slot = self::SLOTS[$pattern];

        return $slot['types'] ?? ($slot['transparent'] ? ['png', 'webp'] : ['jpg', 'png', 'webp']);
    }

    /**
     * Slot pattern → allowed types, in lookup order (exact keys first, as in
     * patternFor()), for the browser check in admin/media-upload.js.
     *
     * @return array<string, list<string>>
     */
    public static function typeRules(): array
    {
        $exact = $wild = [];

        foreach (array_keys(self::SLOTS) as $pattern) {
            if (str_contains($pattern, '*')) {
                $wild[$pattern] = self::types($pattern);
            } else {
                $exact[$pattern] = self::types($pattern);
            }
        }

        return $exact + $wild;
    }

    /** "PNG atau WebP", "JPG, PNG, atau WebP" for messages. */
    public static function typesText(array $types): string
    {
        $names = array_map(static fn (string $type): string => $type === 'webp' ? 'WebP' : strtoupper($type), $types);
        $last  = array_pop($names);

        return $names === [] ? $last : implode(', ', $names) . (count($names) > 1 ? ',' : '') . ' atau ' . $last;
    }

    /** Short label of the slot (placeholders filled), or null when not a slot. */
    public static function label(string $assetKey): ?string
    {
        return self::describe($assetKey)['label'] ?? null;
    }

    /** Group code of any asset key; `other` when it is not a known slot. */
    public static function groupOf(string $assetKey): string
    {
        $pattern = self::patternFor($assetKey);

        return $pattern === null ? 'other' : self::SLOTS[$pattern]['group'];
    }

    /** Region code of a key (`temanggung`) or null. */
    public static function regionOf(string $assetKey): ?string
    {
        $prefixes = config('Gelita')->levelPrefixes;

        if (preg_match('/^challenge\.(?:item\.)?(' . implode('|', array_keys($prefixes)) . ')-/', $assetKey, $m)) {
            return $prefixes[$m[1]];
        }

        foreach ($prefixes as $code) {
            if (in_array($assetKey, ["bg.{$code}.region", "map.region.{$code}", "reward.badge.{$code}", "map.pin.{$code}", "map.lantern.{$code}", "bg.loading.{$code}", "bg.result.{$code}"], true)
                || str_starts_with($assetKey, "library.{$code}.")) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Full slot description for one key, placeholders filled in.
     *
     * @param array<string, int> $ids optional ids for admin links: level_id, node_id
     *
     * @return array{pattern: string, group: string, label: string, content: string, screens: list<array{0: string, 1: string|null}>, format: string, transparent: bool, fallback: string, priority: string, tips: string, recommended: array{0: int, 1: int}|null, admin: array{0: string, 1: string}|null, region: string|null}|null
     */
    public static function describe(string $assetKey, array $ids = []): ?array
    {
        $pattern = self::patternFor($assetKey);

        if ($pattern === null) {
            return null;
        }

        $slot   = self::SLOTS[$pattern];
        $region = self::regionOf($assetKey);
        $vars   = [
            '{wilayah}' => $region ?? 'kode-wilayah',
            '{Wilayah}' => $region === null ? '' : ucfirst($region),
            '{n}'       => '1',
            '{pose}'    => '',
            '{frame}'   => '',
        ];

        if (preg_match('/^challenge\.(?:item\.)?[a-z]+-(\d+)/', $assetKey, $m)) {
            $vars['{n}'] = $m[1];
        }

        if (preg_match('/^avatar\.(\d+)$/', $assetKey, $m)) {
            $vars['{n}'] = $m[1];
        }

        if (preg_match('/^char\.(?:jaka|kedu)\.([a-z]+)\.(\d+)$/', $assetKey, $m)) {
            $vars['{pose}']  = self::POSES[$m[1]] ?? $m[1];
            $vars['{frame}'] = $m[2];
        }

        foreach ($ids as $name => $id) {
            $vars['{' . $name . '}'] = (string) $id;
        }

        $fill = static fn (string $text): string => trim(preg_replace('/ {2,}/', ' ', strtr($text, $vars)) ?? '');

        $label = $fill($slot['label']);

        // Challenge items: name the challenge kind from the node sequence
        if ($slot['group'] === 'challenge' && isset(self::NODE_KINDS[(int) $vars['{n}']]) && $pattern === 'challenge.item.*') {
            $label = 'Gambar ' . self::NODE_KINDS[(int) $vars['{n}']] . ' ' . $vars['{Wilayah}'];
        }

        $admin = $slot['admin'] ?? null;

        if ($admin !== null) {
            $admin = str_contains($fill($admin[1]), '{') ? null : [$admin[0], $fill($admin[1])];
        }

        return [
            'pattern'     => $pattern,
            'group'       => $slot['group'],
            'label'       => $label,
            'content'     => $fill($slot['content']),
            'screens'     => array_map(static fn (array $s): array => [$fill($s[0]), $s[1] === null ? null : strtr($s[1], $vars)], $slot['screens']),
            'format'      => $slot['format'],
            'transparent' => $slot['transparent'],
            'fallback'    => $slot['fallback'],
            'priority'    => $slot['priority'],
            'tips'        => $slot['tips'] ?? '',
            'recommended' => $slot['recommended'] ?? null,
            'admin'       => $admin,
            'region'      => $region,
        ];
    }

    /**
     * Fixed keys the game reads by name (not FK-linked or bank content), for
     * tests and the checklist.
     *
     * @return list<string>
     */
    public static function namedKeys(): array
    {
        return array_values(array_filter(array_keys(self::SLOTS), static fn (string $k): bool => ! str_contains($k, '*')));
    }

    /** Human pose name (`idle` → `diam`). */
    public static function poseLabel(string $pose): string
    {
        return self::POSES[$pose] ?? $pose;
    }
}
