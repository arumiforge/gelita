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
 * Prompts and the visual style guide are not repeated here: they live in the
 * internal Katalog Aset (static page shared by the team, see
 * docs/rencana-katalog-aset.md). The admin searches it by asset key.
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
        'background' => 'Latar layar',
        'map'        => 'Peta',
        'region'     => 'Wilayah: latar, peta, lencana',
        'character'  => 'Tokoh',
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
     * @var array<string, array{group: string, label: string, content: string, screens: list<array{0: string, 1: string|null}>, format: string, transparent: bool, fallback: string, priority: string, tips?: string, recommended?: array{0: int, 1: int}, admin?: array{0: string, 1: string}}>
     */
    private const SLOTS = [
        // ------------------------------------------------------------ brand
        'ui.logo-hero' => [
            'group'       => 'brand',
            'label'       => 'Logo GELITA besar (landscape)',
            'content'     => 'Tulisan GELITA saja, tanpa tagline, sama untuk kedua bahasa. Huruf emas di atas latar transparan; huruf I boleh diganti siluet lentera bernyala.',
            'screens'     => [['Halaman awal (logo besar di atas tombol Mulai)', '/']],
            'format'      => 'PNG berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Emblem logo (ui.logo), lalu tulisan GELITA biasa',
            'priority'    => 'wajib',
            'tips'        => 'Sisakan ruang kosong ± 48 px di tepi. Pastikan tetap terbaca saat diperkecil.',
        ],
        'ui.logo' => [
            'group'       => 'brand',
            'label'       => 'Emblem logo (tanpa tulisan)',
            'content'     => 'Emblem/lambang GELITA tanpa tulisan (mis. lentera emas), karena tulisan GELITA sudah tampil di sebelahnya.',
            'screens'     => [
                ['Pita atas layar akun (di tengah, di samping tombol Kembali)', '/mulai'],
                ['Pita atas langkah daftar', '/daftar/1'],
                ['Cadangan logo halaman awal bila logo besar belum ada', '/'],
            ],
            'format'      => 'PNG berlatar transparan, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Tulisan GELITA',
            'priority'    => 'wajib',
            'recommended' => [512, 512],
            'tips'        => 'Harus tetap jelas saat tampil kecil (± 36 px).',
        ],
        'ui.btn-start' => [
            'group'       => 'brand',
            'label'       => 'Tombol MULAI bergambar (bahasa Indonesia)',
            'content'     => 'Badan tombol emas dengan tulisan MULAI berwarna gelap dan siluet lentera kecil di kiri tulisan. Badan tombol ± 688 × 208 px di tengah, sisa tepi untuk kilau.',
            'screens'     => [['Halaman awal, tombol utama (bahasa Indonesia)', '/']],
            'format'      => 'PNG berlatar transparan, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Tombol biasa bertulisan Mulai',
            'priority'    => 'wajib',
            'tips'        => 'Versi Inggris (ui.btn-start.en) harus berukuran dan berletak sama.',
        ],
        'ui.btn-start.*' => [
            'group'       => 'brand',
            'label'       => 'Tombol MULAI bergambar, versi bahasa lain',
            'content'     => 'Sama persis dengan ui.btn-start, hanya tulisannya dalam bahasa itu (ui.btn-start.en: START).',
            'screens'     => [['Halaman awal saat bahasa permainan diganti ke bahasa itu', '/']],
            'format'      => 'PNG berlatar transparan, paling besar ± 80 KB',
            'transparent' => true,
            'fallback'    => 'Tombol MULAI bahasa Indonesia (ui.btn-start), lalu tombol biasa',
            'priority'    => 'wajib',
        ],
        'ui.placeholder' => [
            'group'       => 'brand',
            'label'       => 'Gambar pengganti',
            'content'     => 'Saat ini permainan memakai berkas bawaan assets/ui/placeholder.svg langsung; slot ini tidak dibaca.',
            'screens'     => [],
            'format'      => 'SVG',
            'transparent' => false,
            'fallback'    => 'Berkas bawaan placeholder.svg',
            'priority'    => 'unused',
            'tips'        => 'Tidak perlu diunggah.',
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
                ['Masuk siswa', '/masuk'],
                ['Daftar langkah 1–3', '/daftar/1'],
                ['Ganti kata sandi', '/ganti-sandi'],
                ['Cadangan latar cerita pembuka & penutup', '/intro'],
            ],
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar warna gelap polos',
            'priority'    => 'wajib',
            'tips'        => 'Latar ini paling sering dilihat; jaga agar tidak ramai di tengah.',
        ],
        'bg.auth' => [
            'group'       => 'background',
            'label'       => 'Latar masuk & daftar (cadangan, belum dipakai)',
            'content'     => 'Saat ini halaman masuk dan daftar siswa memakai latar halaman awal (bg.welcome). Slot ini belum dibaca permainan.',
            'screens'     => [],
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => '—',
            'priority'    => 'unused',
            'tips'        => 'Tidak perlu diunggah sekarang.',
        ],
        'bg.intro' => [
            'group'       => 'background',
            'label'       => 'Latar cerita pembuka & penutup',
            'content'     => 'Suasana sinematik Kedu malam/fajar dengan lentera dan kabut tipis. Sisi kiri dan bagian bawah tenang, karena tokoh berdiri di kiri dan kotak teks ada di bawah.',
            'screens'     => [
                ['Cerita pembuka (slide tanpa latar sendiri)', '/intro'],
                ['Cerita penutup', '/penutup'],
            ],
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
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
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar warna gelap polos',
            'priority'    => 'wajib',
        ],
        'bg.reflection' => [
            'group'       => 'background',
            'label'       => 'Latar Balai Refleksi',
            'content'     => 'Pendapa/balai yang hangat dengan cahaya lentera. Panel Mbah Kedu dan formulir refleksi tampil di atasnya, jadi tengah gambar tidak ramai.',
            'screens'     => [['Balai Refleksi', '/refleksi']],
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar Peta Kedu (bg.map), lalu warna polos',
            'priority'    => 'wajib',
        ],
        'bg.loading' => [
            'group'       => 'background',
            'label'       => 'Latar tirai "Membuka Peta Kedu"',
            'content'     => 'Suasana cahaya dan kabut lembut dengan nada sedang (tidak gelap). Bagian tengah kosong tanpa detail, karena lentera, serpihan, dan judul tirai tampil di sana.',
            'screens'     => [['Tirai pemuatan sesudah cerita pembuka, sebelum Peta Kedu', '/peta']],
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Gradien cahaya bawaan',
            'priority'    => 'wajib',
        ],

        // -------------------------------------------------------------- map
        'map.kedu' => [
            'group'       => 'map',
            'label'       => 'Peta Karesidenan Kedu',
            'content'     => 'Peta ilustrasi dataran Kedu (gaya perkamen gelap, garis emas) dengan Sindoro–Sumbing, Merapi–Merbabu, dan Menoreh. Tanpa tulisan, nama tempat, atau penanda: pin wilayah digambar permainan di atasnya. Area di sekitar titik pin dibiarkan tenang.',
            'screens'     => [['Peta Kedu (tiga pin wilayah di atas gambar)', '/peta']],
            'format'      => 'PNG atau JPG, paling besar ± 600 KB',
            'transparent' => false,
            'fallback'    => 'Peta polos bawaan dengan pin saja',
            'priority'    => 'wajib',
            'recommended' => [1400, 900],
            'tips'        => 'Rasio 14:9; posisi pin wilayah ada di bagian "Titik pin" halaman ini.',
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
            'format'      => 'JPG kualitas ± 80, paling besar ± 450 KB',
            'transparent' => false,
            'fallback'    => 'Latar warna gelap polos',
            'priority'    => 'wajib',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],
        'map.region.*' => [
            'group'       => 'region',
            'label'       => 'Peta wilayah {Wilayah}',
            'content'     => 'Peta ilustrasi wilayah {Wilayah} dilihat dari atas (perkamen gelap, garis emas), dengan jalan setapak yang melewati empat titik tantangan berurutan. Tanpa tulisan, angka, atau penanda: tombol tantangan digambar permainan di atasnya.',
            'screens'     => [['Peta wilayah (empat tombol tantangan di atas gambar)', '/wilayah/{wilayah}']],
            'format'      => 'PNG atau JPG, paling besar ± 600 KB',
            'transparent' => false,
            'fallback'    => 'Peta polos bawaan dengan tombol tantangan saja',
            'priority'    => 'wajib',
            'recommended' => [1400, 900],
            'tips'        => 'Rasio 14:9; posisi keempat tantangan ada di bagian "Titik pin" halaman ini. Jangan taruh gambar penting di bawah titik itu.',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],
        'reward.badge.*' => [
            'group'       => 'region',
            'label'       => 'Lencana wilayah {Wilayah}',
            'content'     => 'Medali bundar memenuhi kanvas: tepi emas tebal, isi biru tua, satu simbol besar khas {Wilayah}. Tanpa tulisan. Harus terbaca saat tampil kecil (± 72 px).',
            'screens'     => [['Profil siswa, sesudah wilayah tuntas', '/profil']],
            'format'      => 'PNG berlatar transparan, paling besar ± 60 KB',
            'transparent' => true,
            'fallback'    => 'Lencana ikon bawaan',
            'priority'    => 'wajib',
            'recommended' => [320, 320],
            'tips'        => 'Ketiga lencana satu keluarga: tepi sama, hanya simbolnya berbeda.',
            'admin'       => ['Atur di halaman wilayah', 'admin/konten/level/{level_id}'],
        ],

        // -------------------------------------------------------- character
        'char.jaka.*' => [
            'group'       => 'character',
            'label'       => 'Jaka · pose {pose} · gambar {frame}',
            'content'     => 'Jaka (anak laki-laki ± 11 tahun, baju lurik, iket, membawa lentera) seluruh badan, pose {pose}, menghadap ke kanan layar. Latar transparan, kaki di garis bawah yang sama untuk semua gambar.',
            'screens'     => [
                ['Baris cerita yang memakai pose ini: cerita pembuka, dialog dan wilayah tuntas, penutup (rinciannya di halaman detail)', '/intro'],
            ],
            'format'      => 'PNG berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Pose diam (idle), lalu inisial nama tokoh',
            'priority'    => 'wajib',
            'tips'        => 'Saat ini hanya gambar 1 setiap pose yang tampil; gambar 2 dan 3 untuk animasi berikutnya. Gambar 1 harus terbaca sendiri.',
        ],
        'char.kedu.*' => [
            'group'       => 'character',
            'label'       => 'Mbah Kedu · pose {pose} · gambar {frame}',
            'content'     => 'Mbah Kedu (kakek penjaga lentera, surjan dan jarik, blangkon) seluruh badan, pose {pose}, menghadap ke kanan layar (permainan mencerminkannya saat berdiri di kanan). Latar transparan, kaki di garis bawah yang sama untuk semua gambar.',
            'screens'     => [
                ['Baris cerita yang memakai pose ini: cerita pembuka, dialog dan wilayah tuntas, penutup (rinciannya di halaman detail)', '/intro'],
            ],
            'format'      => 'PNG berlatar transparan, paling besar ± 250 KB',
            'transparent' => true,
            'fallback'    => 'Pose diam (idle), lalu inisial nama tokoh',
            'priority'    => 'wajib',
            'tips'        => 'Saat ini hanya gambar 1 setiap pose yang tampil; gambar 2 dan 3 untuk animasi berikutnya. Jangan ada tulisan atau lambang asimetris di pakaian, karena gambar dicerminkan.',
        ],

        // -------------------------------------------------------- challenge
        'challenge.*.scene' => [
            'group'       => 'challenge',
            'label'       => 'Adegan Cari Objek {Wilayah}',
            'content'     => 'Adegan pasar/galeri budaya {Wilayah} dengan rak atau meja kosong; benda yang dicari ditempel permainan di atasnya.',
            'screens'     => [['Tantangan Cari Objek {Wilayah}', '/tantangan/{wilayah}/{n}']],
            'format'      => 'JPG kualitas ± 85',
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
            'format'      => 'JPG kualitas ± 85, paling besar ± 350 KB',
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
            'format'      => 'JPG kualitas ± 85',
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
            'format'      => 'PNG berlatar transparan',
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
            'format'      => 'PNG atau JPG',
            'transparent' => false,
            'fallback'    => 'Soal tampil tanpa gambar',
            'priority'    => 'opsional',
            'admin'       => ['Buka soal tantangan', 'admin/konten/node/{node_id}'],
        ],

        // ---------------------------------------------------------- library
        'library.*' => [
            'group'       => 'library',
            'label'       => 'Gambar Pustaka',
            'content'     => 'Foto atau ilustrasi halaman Pustaka. Bawaannya memakai tautan Wikimedia Commons, jadi unggahan hanya bila ingin memakai berkas sendiri.',
            'screens'     => [['Pustaka Kedu', '/pustaka']],
            'format'      => 'JPG atau PNG',
            'transparent' => false,
            'fallback'    => 'Gambar dari tautan bawaan',
            'priority'    => 'opsional',
            'recommended' => [960, 640],
        ],
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
            if (in_array($assetKey, ["bg.{$code}.region", "map.region.{$code}", "reward.badge.{$code}"], true)
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
