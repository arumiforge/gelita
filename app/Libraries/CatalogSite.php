<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Builds the public Katalog Aset site in `gelita-katalog/` (owner,
 * 17 November 2026): one static page of every picture, music track, sound
 * effect and narration the game uses, served at https://katalog.gelita.id/
 * from a repository checkout.
 *
 * The site is self-contained: index.html, its script and style, the data
 * file and every preview and audio file live inside the folder and are linked
 * relatively, so it works from any web server and from file://.
 *
 * Sources (the game leads; nothing here is kept by hand):
 * - pictures: `ai-studio/game-images/data.json` (frame 1 of each character
 *   pose only: the game moves to still poses, docs/PROGRESS.md), the mock-up
 *   pictures of CatalogImporter::MOCKUP_FILES and MOCKUP_DIRS, and the UI art
 *   that ships with the game (`public/assets/ui/`, `public/assets/app/`);
 *   where each picture is used comes from ImageSlots;
 * - review status: the Status and Notes columns of `REVIEW.md`;
 * - music and sound effects: `public/assets/audio/{music,sfx}/` with the
 *   screen and mood columns of `ai-studio/elevenlabs-sound/sounds.csv`;
 * - narration: `ai-studio/elevenlabs-voice/lines.csv` and its takes in
 *   `out/{id,en}/`.
 *
 * Prompt texts are never copied into the site. The masters are never served:
 * pictures are re-encoded as web-size lossy WebP (preview and thumbnail);
 * audio is copied byte for byte, so git stores no new blob for it.
 *
 * Output is deterministic. Each picture records a fingerprint of its source
 * and the encoder settings in data.js; a rebuild re-encodes only pictures
 * whose fingerprint changed, and removes files no entry points at.
 */
final class CatalogSite
{
    /** Output folder, relative to the repository root. */
    public const OUT_DIR = 'gelita-katalog';

    /** Longest side and WebP quality of the preview in the detail view. */
    public const PREVIEW_MAX = 1024;

    public const PREVIEW_QUALITY = 72;

    /** Longest side and WebP quality of the grid thumbnail. */
    public const THUMB_MAX = 320;

    public const THUMB_QUALITY = 60;

    /** Part of each picture fingerprint: changing the encoder settings re-encodes everything. */
    public const ENCODER = 'gd-webp/p1024q72/t320q60/v1';

    /** Static files of the page, copied from TEMPLATE_DIR. */
    public const STATIC_FILES = ['index.html', 'katalog.css', 'katalog.js'];

    public const DATA_FILE = 'data.js';

    /** Hand-written files of the folder that the build keeps. */
    public const KEPT_FILES = ['README.md'];

    /** Media folders the build owns completely (stale files are removed). */
    public const MEDIA_DIRS = ['gambar', 'suara', 'huruf'];

    /** The game's own fonts and their licences, copied byte for byte (target → source). */
    public const FONT_FILES = [
        'huruf/marcellus-400.woff2'          => 'public/assets/fonts/marcellus-latin-400-normal.woff2',
        'huruf/plus-jakarta-sans-400.woff2'  => 'public/assets/fonts/plus-jakarta-sans-latin-400-normal.woff2',
        'huruf/plus-jakarta-sans-700.woff2'  => 'public/assets/fonts/plus-jakarta-sans-latin-700-normal.woff2',
        'huruf/OFL-Marcellus.txt'            => 'public/assets/fonts/OFL-Marcellus.txt',
        'huruf/OFL-PlusJakartaSans.txt'      => 'public/assets/fonts/OFL-PlusJakartaSans.txt',
    ];

    private const TEMPLATE_DIR = __DIR__ . '/CatalogSite';

    private const IMAGE_DIR = 'ai-studio/game-images';

    private const SOUND_LIST = 'ai-studio/elevenlabs-sound/sounds.csv';

    private const VOICE_DIR = 'ai-studio/elevenlabs-voice';

    private const AUDIO_DIR = 'public/assets/audio';

    /** Shipped UI art folders → id prefix. */
    private const SHIPPED_DIRS = [
        'public/assets/ui'  => 'ui',
        'public/assets/app' => 'app',
    ];

    /** Category code → page heading, in page order. */
    public const GROUPS = [
        'ui'         => 'Tampilan permainan',
        'brand'      => 'Logo & tombol',
        'app'        => 'Ikon aplikasi',
        'background' => 'Latar layar',
        'story'      => 'Latar cerita',
        'character'  => 'Tokoh',
        'avatar'     => 'Avatar',
        'map'        => 'Peta',
        'region'     => 'Wilayah: latar, peta, lencana',
        'result'     => 'Layar tantangan selesai',
        'challenge'  => 'Gambar tantangan',
        'library'    => 'Pustaka',
        'test'       => 'Gambar tes',
        'reference'  => 'Acuan gaya',
        'other'      => 'Lainnya',
    ];

    public const REGIONS = [
        'temanggung' => 'Temanggung',
        'magelang'   => 'Magelang',
        'wonosobo'   => 'Wonosobo',
    ];

    /** Challenge kind code → name, by node sequence 1–5. */
    public const KINDS = [
        'puzzle'   => 'Susun Gambar',
        'rumpang'  => 'Isi Rumpang',
        'boleh'    => 'Benar atau Salah',
        'cari'     => 'Cari Objek',
        'skenario' => 'Skenario Digital',
    ];

    /** Review status code → badge text. */
    public const STATUSES = [
        'keep'       => 'Dipertahankan',
        'fixed'      => 'Sudah diperbaiki',
        'redo'       => 'Akan dibuat ulang',
        'redo-later' => 'Dibuat ulang nanti',
        'drop'       => 'Akan dihapus',
        'none'       => 'Belum ditinjau',
    ];

    /** Narration context (`lines.csv` konteks) → heading, in story order. */
    public const CONTEXTS = [
        'intro'                 => 'Cerita pembuka',
        'map_intro'             => 'Pengantar Peta Kedu',
        'region_intro'          => 'Mengenal wilayah',
        'level_open'            => 'Dialog wilayah',
        'challenge_instruction' => 'Petunjuk tantangan',
        'hunt_clue'             => 'Petunjuk Cari Objek (babak 1)',
        'post_clue'             => 'Petunjuk Periksa Unggahan (babak 2)',
        'scenario_open'         => 'Cerita penghubung',
        'scenario_situation'    => 'Situasi Skenario Digital',
        'level_done'            => 'Wilayah tuntas',
        'map_done'              => 'Peta Kedu tuntas',
        'ending'                => 'Cerita penutup',
    ];

    /** Story background word (`bg.cerita-{word}-…`) → story part. */
    private const STORY_PARTS = [
        'intro'   => 'Cerita pembuka',
        'penutup' => 'Cerita penutup',
        'kenal'   => 'Mengenal {Wilayah}',
        'dialog'  => 'Dialog {Wilayah}',
        'layar'   => 'Cerita penghubung {Wilayah}',
        'tuntas'  => '{Wilayah} tuntas',
    ];

    /** Shipped UI art: file name prefix → what it is (longest prefix first). */
    private const UI_LABELS = [
        'rumpang-instruction-plate' => 'Pelat petunjuk Isi Rumpang',
        'map-progress-track'        => 'Bilah kemajuan di peta',
        'challenge-badge'           => 'Lencana tantangan',
        'map-head-plate'            => 'Pelat judul peta',
        'loading-track'             => 'Bilah memuat',
        'ornate-crest'              => 'Hiasan mahkota emas',
        'select-arrow'              => 'Panah kotak pilihan',
        'kedu-region-'              => 'Penutup wilayah terkunci di Peta Kedu',
        'modal-frame-'              => 'Bingkai jendela',
        'kenal-plate-'              => 'Pelat layar Mengenal wilayah',
        'node-card-'                => 'Kartu tantangan di peta wilayah',
        'hud-shards-'               => 'Penghitung Serpihan Cahaya',
        'gate-icon-'                => 'Ikon layar gerbang',
        'gold-pill-'                => 'Label emas',
        'placeholder'               => 'Gambar pengganti',
        'apple-touch'               => 'Ikon aplikasi',
        'status-'                   => 'Penanda status tantangan',
        'round-'                    => 'Tombol bulat emas',
        'plate-'                    => 'Pelat tombol',
        'icon-'                     => 'Ikon',
        'jaka-'                     => 'Gambar Jaka',
        'star'                      => 'Bintang',
        'btn-'                      => 'Tombol bertulisan',
        'bg-'                       => 'Latar layar',
    ];

    /** Node card / plate names of shipped UI art → challenge kind. */
    private const UI_KINDS = [
        'node-card-puzzle'          => 'puzzle',
        'node-card-rumpang'         => 'rumpang',
        'rumpang-instruction-plate' => 'rumpang',
        'node-card-boleh'           => 'boleh',
        'node-card-cari'            => 'cari',
        'node-card-skenario'        => 'skenario',
    ];

    private readonly string $root;

    private readonly string $out;

    /** @var array<string, array{st: string, text: string, notes: string, no: int}>|null */
    private ?array $review = null;

    /**
     * @param string      $root repository root
     * @param string|null $out  output folder (OUT_DIR under $root unless a test passes its own)
     */
    public function __construct(?string $root = null, ?string $out = null)
    {
        $this->root = rtrim($root ?? ROOTPATH, '/\\');
        $this->out  = rtrim($out ?? $this->root . '/' . self::OUT_DIR, '/\\');
    }

    public function outDir(): string
    {
        return $this->out;
    }

    // ------------------------------------------------------------------ model

    /**
     * The whole catalogue as it would be written to data.js.
     *
     * @return array{images: list<array<string, mixed>>, music: list<array<string, mixed>>, sfx: list<array<string, mixed>>, narration: list<array<string, mixed>>}
     */
    public function model(): array
    {
        return [
            'images'    => $this->images(),
            'music'     => $this->sounds('music'),
            'sfx'       => $this->sounds('sfx'),
            'narration' => $this->narration(),
        ];
    }

    /**
     * One entry per picture file. Internal fields (source path, how to make
     * the preview) start with an underscore and stay out of data.js.
     *
     * @return list<array<string, mixed>>
     */
    public function images(): array
    {
        $byFile = [];

        // 1. The list of the picture masters, one entry per file
        $data = json_decode((string) @file_get_contents($this->root . '/' . self::IMAGE_DIR . '/data.json'), true);

        if (! is_array($data)) {
            throw new RuntimeException(self::IMAGE_DIR . '/data.json tidak dapat dibaca.');
        }

        foreach ($data as $entry) {
            if (! is_array($entry) || ! isset($entry['key'], $entry['file']) || ($entry['size'] ?? '') === 'Audio') {
                continue;
            }

            $key = (string) $entry['key'];

            // Still poses only: frame 1 stands for its pose
            if (preg_match('/^char\.(?:jaka|kedu)\.[a-z]+\.(\d+)$/', $key, $m) && $m[1] !== '1') {
                continue;
            }

            $file = self::IMAGE_DIR . '/' . ltrim((string) $entry['file'], '/');
            $byFile[$file] ??= ['keys' => [], 'entries' => []];
            $byFile[$file]['keys'][]    = $key;
            $byFile[$file]['entries'][] = $entry;
        }

        // 2. Mock-up pictures that fill a slot without a list entry
        foreach (CatalogImporter::MOCKUP_FILES as $path => $slot) {
            if (is_file($this->root . '/' . self::IMAGE_DIR . '/' . $path)) {
                $byFile[self::IMAGE_DIR . '/' . $path] ??= ['keys' => [$slot], 'entries' => []];
            }
        }

        foreach (array_keys(CatalogImporter::MOCKUP_DIRS) as $dir) {
            foreach ($this->listFiles(self::IMAGE_DIR . '/' . $dir, ['webp', 'png', 'jpg']) as $path) {
                $byFile[$path] ??= ['keys' => [pathinfo($path, PATHINFO_FILENAME)], 'entries' => []];
            }
        }

        $images = [];

        foreach ($byFile as $file => $found) {
            $images[] = $this->listedImage($file, $found['keys'], $found['entries']);
        }

        // 3. UI art that ships with the game
        foreach (self::SHIPPED_DIRS as $dir => $prefix) {
            foreach ($this->listFiles($dir, ['webp', 'png', 'svg', 'jpg']) as $path) {
                $images[] = $this->shippedImage($path, $prefix);
            }
        }

        $seen = [];

        foreach ($images as $image) {
            if (isset($seen[$image['id']])) {
                throw new RuntimeException('Dua gambar memakai nama yang sama di katalog: ' . $image['id'] . ' (' . $seen[$image['id']] . ', ' . $image['_src'] . ').');
            }

            $seen[$image['id']] = $image['_src'];
        }

        $groupOrder = array_flip(array_keys(self::GROUPS));
        usort($images, static fn (array $a, array $b): int => [$groupOrder[$a['g']], $a['id']] <=> [$groupOrder[$b['g']], $b['id']]);

        return $images;
    }

    /**
     * @param list<string>               $keys
     * @param list<array<string, mixed>> $entries
     *
     * @return array<string, mixed>
     */
    private function listedImage(string $file, array $keys, array $entries): array
    {
        $first = $entries[0] ?? [];
        $key   = $keys[0];
        $slot  = ImageSlots::describe($key);
        $story = $this->storyUse($key);
        $group = match (true) {
            $story !== null                                 => 'story',
            str_starts_with($key, 'instr.')                 => 'test',
            str_starts_with($key, 'reference.') && $slot === null => 'reference',
            str_starts_with($key, 'char.')                  => 'character',
            default                                         => ImageSlots::groupOf($key),
        };

        $region = null;

        foreach ($keys as $k) {
            $region ??= ImageSlots::regionOf($k) ?? $this->storyRegion($k);
        }

        $listRegion = strtolower((string) ($first['region'] ?? ''));
        $region ??= isset(self::REGIONS[$listRegion]) ? $listRegion : null;

        $title = trim((string) ($first['title'] ?? ''));
        $desc  = trim((string) ($first['caption']['id'] ?? $first['desc'] ?? ''));

        if (str_starts_with($key, 'char.') && preg_match('/^char\.(jaka|kedu)\.([a-z]+)\.1$/', $key, $m)) {
            $title = ($m[1] === 'jaka' ? 'Jaka' : 'Mbah Kedu') . ', pose ' . ImageSlots::poseLabel($m[2]);
        }

        if ($title === '' || $title === $key) {
            $title = $slot['label'] ?? $story ?? $key;
        }

        if ($desc === '') {
            $desc = $slot['content'] ?? '';
        }

        $uses   = [];
        $unused = $slot === null && $story === null;

        foreach ($keys as $k) {
            $use = $this->storyUse($k);

            if ($use !== null) {
                $uses[] = $use;

                continue;
            }

            $d = ImageSlots::describe($k);

            if ($d === null) {
                continue;
            }

            if ($d['priority'] === 'unused') {
                $unused = true;
            }

            $screens = array_map(static fn (array $s): string => $s[0], $d['screens']);
            $uses[]  = $d['label'] . ($screens === [] ? '' : ': ' . implode('; ', $screens));
        }

        if ($unused && $uses === []) {
            $uses[] = 'Tidak dipasang ke permainan (acuan gaya untuk gambar lain).';
        }

        $image = [
            'id' => pathinfo($file, PATHINFO_FILENAME),
            'g'  => $group,
            't'  => $title,
            'd'  => $desc,
            'k'  => $keys,
            'u'  => array_values(array_unique($uses)),
            'r'  => $region,
            'c'  => $this->kindOf($key),
            'x'  => $unused,
        ];

        if (str_starts_with($key, 'char.')) {
            $image['d'] = trim($image['d'] . ' Gambar 1 dari pose ini; permainan memakai satu gambar diam per pose.');
        }

        return $this->withFile($image, $file, false);
    }

    /** @return array<string, mixed> */
    private function shippedImage(string $path, string $prefix): array
    {
        $name  = pathinfo($path, PATHINFO_FILENAME);
        $label = 'Gambar tampilan';

        foreach (self::UI_LABELS as $start => $text) {
            if (str_starts_with($name, $start)) {
                $label = $text;

                break;
            }
        }

        $region = null;

        foreach (array_keys(self::REGIONS) as $code) {
            if (str_ends_with($name, '-' . $code)) {
                $region = $code;
            }
        }

        $image = [
            'id' => $prefix . '-' . $name,
            'g'  => $prefix === 'app' ? 'app' : 'ui',
            't'  => $label . ' (' . $name . ')',
            'd'  => $prefix === 'app'
                ? 'Ikon yang dipakai saat GELITA dipasang di layar utama perangkat.'
                : 'Bagian tampilan berlapis emas yang ikut terpasang bersama permainan.',
            'k'  => [$prefix . '/' . basename($path)],
            'u'  => [$prefix === 'app' ? 'Ikon aplikasi dan tab peramban' : 'Terpasang bersama permainan (folder tampilan)'],
            'r'  => $region,
            'c'  => self::UI_KINDS[$name] ?? null,
            'x'  => false,
        ];

        return $this->withFile($image, $path, true);
    }

    /**
     * Adds size, review status, output paths and the source fingerprint.
     *
     * @param array<string, mixed> $image
     *
     * @return array<string, mixed>
     */
    private function withFile(array $image, string $path, bool $webReady): array
    {
        $abs    = $this->root . '/' . $path;
        $ext    = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $size   = $ext === 'svg' ? false : @getimagesize($abs);
        $review = $this->review()[$path] ?? null;
        $raster = in_array($ext, ['webp', 'png', 'jpg', 'jpeg'], true);

        $image['wh'] = is_array($size) ? [(int) $size[0], (int) $size[1]] : null;
        $image['s']  = $review['st'] ?? 'none';
        $image['sn'] = $review === null ? '' : trim($review['text'] . ($review['text'] !== '' && $review['notes'] !== '' ? ' — ' : '') . $review['notes']);
        $image['no'] = $review['no'] ?? null;

        if ($raster) {
            $image['img'] = 'gambar/' . $image['id'] . '.webp';
            $image['th']  = 'gambar/kecil/' . $image['id'] . '.webp';
        } else {
            $image['img'] = 'gambar/' . $image['id'] . '.' . $ext;
            $image['th']  = $image['img'];
        }

        $copyPreview = ! $raster || ($webReady && $ext === 'webp' && is_array($size) && max($size[0], $size[1]) <= self::PREVIEW_MAX);

        $image['src'] = substr(hash('sha256', self::ENCODER . "\0" . ($copyPreview ? 'copy' : 'encode') . "\0" . (string) @hash_file('sha256', $abs)), 0, 16);

        $image['_src']     = $path;
        $image['_copy']    = $copyPreview;
        $image['_raster']  = $raster;

        return $image;
    }

    /** "Cerita pembuka, slide 3" for a story background key, else null. */
    private function storyUse(string $key): ?string
    {
        if (! preg_match('/^bg\.cerita-([a-z]+)(?:-([a-z]+))?-(\d+)$/', $key, $m) || ! isset(self::STORY_PARTS[$m[1]])) {
            return null;
        }

        $region = self::REGIONS[$m[2]] ?? '';

        return trim(str_replace('{Wilayah}', $region, self::STORY_PARTS[$m[1]])) . ', slide ' . (int) $m[3];
    }

    private function storyRegion(string $key): ?string
    {
        return preg_match('/^bg\.cerita-[a-z]+-([a-z]+)-\d+$/', $key, $m) && isset(self::REGIONS[$m[1]]) ? $m[1] : null;
    }

    /** Challenge kind code of a picture or narration key, or null. */
    private function kindOf(string $key): ?string
    {
        $kinds = array_keys(self::KINDS);

        if (preg_match('/^(?:challenge\.item\.|instruksi-)[a-z]+-(\d)(?:-|$)/', $key, $m)) {
            return $kinds[(int) $m[1] - 1] ?? null;
        }

        return match (true) {
            str_starts_with($key, 'challenge.post.'), str_ends_with($key, '.scene') && str_starts_with($key, 'challenge.') => 'cari',
            str_starts_with($key, 'item.skenario.')                                                                          => 'skenario',
            default                                                                                                          => null,
        };
    }

    /**
     * Music or sound effects, in the order of sounds.csv, then any file it does not list.
     *
     * @return list<array<string, mixed>>
     */
    public function sounds(string $kind): array
    {
        $listed = [];

        foreach ($this->readCsv(self::SOUND_LIST) as $row) {
            if (($row['jenis'] ?? '') === $kind && str_starts_with((string) ($row['kode'] ?? ''), $kind . '/')) {
                $listed[substr($row['kode'], strlen($kind) + 1)] = $row;
            }
        }

        $folder = $kind === 'music' ? 'musik' : 'efek';
        $files  = [];

        foreach ($this->listFiles(self::AUDIO_DIR . '/' . $kind, ['mp3']) as $path) {
            $files[pathinfo($path, PATHINFO_FILENAME)] = $path;
        }

        $sounds = [];

        foreach (array_merge(array_keys(array_intersect_key($listed, $files)), array_keys(array_diff_key($files, $listed))) as $name) {
            $row      = $listed[$name] ?? [];
            $sounds[] = [
                'id'   => $name,
                't'    => trim((string) ($row['layar'] ?? '')) ?: $name,
                'd'    => trim((string) ($row['suasana'] ?? '')),
                'dur'  => isset($row['durasi_detik']) && is_numeric($row['durasi_detik']) ? (float) $row['durasi_detik'] : null,
                'loop' => ($row['putar_ulang'] ?? '') === 'ya',
                'f'    => 'suara/' . $folder . '/' . $name . '.mp3',
                '_src' => $files[$name],
            ];
        }

        return $sounds;
    }

    /**
     * Narration lines in story order, with the take per language where it exists.
     *
     * @return list<array<string, mixed>>
     */
    public function narration(): array
    {
        $order = array_flip(array_keys(self::CONTEXTS));
        $lines = [];

        foreach ($this->readCsv(self::VOICE_DIR . '/lines.csv') as $index => $row) {
            $code = trim((string) ($row['kode_berkas'] ?? ''));

            if ($code === '' || preg_match('/^[a-z0-9-]+$/', $code) !== 1) {
                continue;
            }

            $line = [
                'id'  => $code,
                'ctx' => (string) ($row['konteks'] ?? ''),
                'r'   => isset(self::REGIONS[$row['wilayah'] ?? '']) ? $row['wilayah'] : null,
                'c'   => match ($row['konteks'] ?? '') {
                    'hunt_clue', 'post_clue' => 'cari',
                    'scenario_situation'     => 'skenario',
                    default                  => $this->kindOf($code),
                },
                'sp'  => (string) ($row['tokoh'] ?? ''),
                'ti'  => trim((string) ($row['teks_id'] ?? '')),
                'te'  => trim((string) ($row['teks_en'] ?? '')),
                '_i'  => $index,
            ];

            foreach (['id' => 'fi', 'en' => 'fe'] as $locale => $field) {
                $take = self::VOICE_DIR . '/out/' . $locale . '/' . $code . '.mp3';

                if (is_file($this->root . '/' . $take)) {
                    $line[$field]          = 'suara/narasi/' . $locale . '/' . $code . '.mp3';
                    $line['_' . $field]    = $take;
                }
            }

            $lines[] = $line;
        }

        usort($lines, static fn (array $a, array $b): int => [$order[$a['ctx']] ?? 99, $a['_i']] <=> [$order[$b['ctx']] ?? 99, $b['_i']]);

        return array_map(static function (array $line): array {
            unset($line['_i']);

            return $line;
        }, $lines);
    }

    /**
     * Status and notes per picture path (relative to the repository root),
     * read from the table rows of REVIEW.md.
     *
     * @return array<string, array{st: string, text: string, notes: string, no: int}>
     */
    public function review(): array
    {
        if ($this->review !== null) {
            return $this->review;
        }

        $this->review = [];
        $text         = (string) @file_get_contents($this->root . '/' . self::IMAGE_DIR . '/REVIEW.md');

        foreach (preg_split('/\R/', $text) ?: [] as $row) {
            if (! preg_match('/^\| (\d+) \| \[`[^`]+`\]\(([^)]+)\) \| .*? \| .*? \| (.*?) \| (.*?) \|\s*$/', $row, $m)) {
                continue;
            }

            $path   = $this->normalise(self::IMAGE_DIR . '/' . $m[2]);
            $status = self::plain($m[3]);
            $code   = strtolower(trim((string) preg_replace('/:.*$/s', '', $status)));
            $code   = match ($code) {
                'keep', 'fixed', 'redo', 'drop' => $code,
                'redo later'                    => 'redo-later',
                default                         => 'none',
            };

            $this->review[$path] = [
                'st'    => $code,
                'text'  => str_contains($status, ':') ? trim(substr($status, strpos($status, ':') + 1)) : '',
                'notes' => self::plain($m[4]),
                'no'    => (int) $m[1],
            ];
        }

        return $this->review;
    }

    /** Markdown cell → plain text (no backticks, links as their text). */
    private static function plain(string $cell): string
    {
        $cell = (string) preg_replace('/\[([^\]]*)\]\([^)]*\)/', '$1', $cell);

        return trim(str_replace(['`', '\\|', '**'], ['', '|', ''], $cell));
    }

    private function normalise(string $path): string
    {
        $parts = [];

        foreach (explode('/', str_replace('\\', '/', $path)) as $part) {
            if ($part === '..') {
                array_pop($parts);
            } elseif ($part !== '.' && $part !== '') {
                $parts[] = $part;
            }
        }

        return implode('/', $parts);
    }

    // ------------------------------------------------------------- rendering

    /** The data.js text for a model: one entry per line, so a rebuild diff stays small. */
    public static function dataScript(array $model): string
    {
        $json  = static fn ($value): string => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $clean = static fn (array $rows): array => array_map(
            static fn (array $row): array => array_filter($row, static fn ($k): bool => ! str_starts_with((string) $k, '_'), ARRAY_FILTER_USE_KEY),
            $rows,
        );

        $out = "/* Dibuat oleh php spark gelita:katalog:halaman. Jangan diubah dengan tangan. */\n";
        $out .= "window.KATALOG = {\n";
        $out .= '"groups": ' . $json(self::GROUPS) . ",\n";
        $out .= '"regions": ' . $json(self::REGIONS) . ",\n";
        $out .= '"kinds": ' . $json(self::KINDS) . ",\n";
        $out .= '"statuses": ' . $json(self::STATUSES) . ",\n";
        $out .= '"contexts": ' . $json(self::CONTEXTS) . ",\n";

        $sections = ['images', 'music', 'sfx', 'narration'];

        foreach ($sections as $i => $name) {
            $rows = $clean($model[$name]);
            $out .= '"' . $name . "\": [\n" . implode(",\n", array_map($json, $rows)) . "\n]" . ($i < count($sections) - 1 ? ',' : '') . "\n";
        }

        return $out . "};\n";
    }

    /**
     * Picture fingerprints of the data.js now in the folder: id → src.
     *
     * @return array<string, string>
     */
    public function builtFingerprints(): array
    {
        $text = (string) @file_get_contents($this->out . '/' . self::DATA_FILE);
        $map  = [];

        if (preg_match('/"images": \[\n(.*?)\n\]/s', $text, $m)) {
            foreach (explode("\n", $m[1]) as $line) {
                $row = json_decode(rtrim($line, ','), true);

                if (is_array($row) && isset($row['id'], $row['src'])) {
                    $map[(string) $row['id']] = (string) $row['src'];
                }
            }
        }

        return $map;
    }

    // ----------------------------------------------------------------- build

    /**
     * Writes the site. Returns what was (or, in a dry run, would be) done.
     *
     * @return array{encoded: list<string>, copied: list<string>, written: list<string>, removed: list<string>, unchanged: int, failed: list<string>, model: array}
     */
    public function build(bool $dryRun = false): array
    {
        $model  = $this->model();
        $built  = $this->builtFingerprints();
        $report = ['encoded' => [], 'copied' => [], 'written' => [], 'removed' => [], 'unchanged' => 0, 'failed' => [], 'model' => $model];
        $keep   = [];

        if (! $dryRun && ! is_dir($this->out) && ! mkdir($this->out, 0775, true) && ! is_dir($this->out)) {
            throw new RuntimeException('Folder ' . $this->out . ' tidak dapat dibuat.');
        }

        foreach ($model['images'] as $image) {
            $keep[$image['img']] = true;
            $keep[$image['th']]  = true;
            $current = ($built[$image['id']] ?? null) === $image['src']
                && is_file($this->out . '/' . $image['img']) && is_file($this->out . '/' . $image['th']);

            if ($current) {
                $report['unchanged']++;

                continue;
            }

            if ($dryRun) {
                $report[$image['_copy'] ? 'copied' : 'encoded'][] = $image['img'];

                continue;
            }

            $source = $this->root . '/' . $image['_src'];
            $ok     = $image['_copy']
                ? $this->copyIfChanged($source, $image['img'])
                : $this->encode($source, $this->out . '/' . $image['img'], self::PREVIEW_MAX, self::PREVIEW_QUALITY);

            if ($ok && $image['_raster']) {
                $ok = $this->encode($source, $this->out . '/' . $image['th'], self::THUMB_MAX, self::THUMB_QUALITY);
            }

            if ($ok) {
                $report[$image['_copy'] ? 'copied' : 'encoded'][] = $image['img'];
            } else {
                $report['failed'][] = $image['_src'];
            }
        }

        $audio = self::FONT_FILES;

        foreach (['music', 'sfx'] as $name) {
            foreach ($model[$name] as $sound) {
                $audio[$sound['f']] = $sound['_src'];
            }
        }

        foreach ($model['narration'] as $line) {
            foreach (['fi', 'fe'] as $field) {
                if (isset($line[$field])) {
                    $audio[$line[$field]] = $line['_' . $field];
                }
            }
        }

        // Audio and fonts: byte-for-byte copies
        foreach ($audio as $target => $source) {
            $keep[$target] = true;

            if ($this->sameFile($this->root . '/' . $source, $this->out . '/' . $target)) {
                $report['unchanged']++;
            } elseif ($dryRun || $this->copyIfChanged($this->root . '/' . $source, $target)) {
                $report['copied'][] = $target;
            } else {
                $report['failed'][] = $source;
            }
        }

        $texts = [self::DATA_FILE => self::dataScript($model)];

        foreach (self::STATIC_FILES as $file) {
            $texts[$file] = (string) file_get_contents(self::TEMPLATE_DIR . '/' . $file);
        }

        foreach ($texts as $file => $text) {
            if (@file_get_contents($this->out . '/' . $file) === $text) {
                continue;
            }

            if (! $dryRun) {
                file_put_contents($this->out . '/' . $file, $text);
            }

            $report['written'][] = $file;
        }

        foreach ($this->staleFiles($keep) as $stale) {
            if (! $dryRun) {
                @unlink($this->out . '/' . $stale);
            }

            $report['removed'][] = $stale;
        }

        if (! $dryRun) {
            $this->removeEmptyDirs();
        }

        return $report;
    }

    /**
     * Why the folder does not match the sources (empty when it is current).
     * Reads and hashes, never encodes or writes.
     *
     * @return list<string>
     */
    public function check(): array
    {
        $problems = [];
        $model    = $this->model();

        if (@file_get_contents($this->out . '/' . self::DATA_FILE) !== self::dataScript($model)) {
            $problems[] = self::DATA_FILE . ' tidak sesuai dengan sumbernya (gambar, daftar tinjauan, suara, atau narasi berubah).';
        }

        foreach (self::STATIC_FILES as $file) {
            if (@file_get_contents($this->out . '/' . $file) !== @file_get_contents(self::TEMPLATE_DIR . '/' . $file)) {
                $problems[] = $file . ' tidak sama dengan templatnya di app/Libraries/CatalogSite/.';
            }
        }

        $keep = [];

        foreach ($model['images'] as $image) {
            foreach (['img', 'th'] as $field) {
                $keep[$image[$field]] = true;

                if (! is_file($this->out . '/' . $image[$field])) {
                    $problems[] = 'Berkas belum ada: ' . $image[$field];
                }
            }
        }

        foreach (self::FONT_FILES as $target => $source) {
            $keep[$target] = true;

            if (! $this->sameFile($this->root . '/' . $source, $this->out . '/' . $target)) {
                $problems[] = 'Berkas huruf belum ada atau berbeda: ' . $target;
            }
        }

        foreach (['music', 'sfx'] as $name) {
            foreach ($model[$name] as $sound) {
                $keep[$sound['f']] = true;

                if (! $this->sameFile($this->root . '/' . $sound['_src'], $this->out . '/' . $sound['f'])) {
                    $problems[] = 'Berkas suara belum ada atau berbeda: ' . $sound['f'];
                }
            }
        }

        foreach ($model['narration'] as $line) {
            foreach (['fi', 'fe'] as $field) {
                if (isset($line[$field])) {
                    $keep[$line[$field]] = true;

                    if (! $this->sameFile($this->root . '/' . $line['_' . $field], $this->out . '/' . $line[$field])) {
                        $problems[] = 'Rekaman belum ada atau berbeda: ' . $line[$field];
                    }
                }
            }
        }

        foreach ($this->staleFiles($keep) as $stale) {
            $problems[] = 'Berkas lama yang tidak dipakai lagi: ' . $stale;
        }

        return array_values(array_unique($problems));
    }

    /** Folder size in bytes and file count. @return array{0: int, 1: int} */
    public function folderSize(): array
    {
        $bytes = $count = 0;

        if (is_dir($this->out)) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->out, \FilesystemIterator::SKIP_DOTS)) as $file) {
                $bytes += $file->getSize();
                $count++;
            }
        }

        return [$bytes, $count];
    }

    /**
     * Files under the media folders that no entry points at, plus top-level
     * files the build does not write or keep.
     *
     * @param array<string, true> $keep
     *
     * @return list<string>
     */
    private function staleFiles(array $keep): array
    {
        $stale = [];

        if (! is_dir($this->out)) {
            return [];
        }

        $top = array_flip(array_merge(self::STATIC_FILES, [self::DATA_FILE], self::KEPT_FILES));

        foreach (scandir($this->out) ?: [] as $name) {
            if ($name === '.' || $name === '..') {
                continue;
            }

            if (is_dir($this->out . '/' . $name)) {
                if (! in_array($name, self::MEDIA_DIRS, true)) {
                    continue;
                }

                $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->out . '/' . $name, \FilesystemIterator::SKIP_DOTS));

                foreach ($iterator as $file) {
                    $relative = substr(str_replace('\\', '/', $file->getPathname()), strlen($this->out) + 1);

                    if (! isset($keep[$relative])) {
                        $stale[] = $relative;
                    }
                }
            } elseif (! isset($top[$name])) {
                $stale[] = $name;
            }
        }

        sort($stale);

        return $stale;
    }

    private function removeEmptyDirs(): void
    {
        foreach (self::MEDIA_DIRS as $dir) {
            if (! is_dir($this->out . '/' . $dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($this->out . '/' . $dir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    @rmdir($file->getPathname());
                }
            }
        }
    }

    private function sameFile(string $a, string $b): bool
    {
        return is_file($a) && is_file($b) && filesize($a) === filesize($b) && hash_file('sha256', $a) === hash_file('sha256', $b);
    }

    private function copyIfChanged(string $source, string $target): bool
    {
        $path = $this->out . '/' . $target;

        if ($this->sameFile($source, $path)) {
            return true;
        }

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        return @copy($source, $path);
    }

    /**
     * Lossy WebP no larger than $max on its longest side; transparency kept.
     */
    private function encode(string $source, string $target, int $max, int $quality): bool
    {
        $data  = @file_get_contents($source);
        $image = $data === false ? false : @imagecreatefromstring($data);

        if ($image === false) {
            return false;
        }

        imagepalettetotruecolor($image);
        [$width, $height] = [imagesx($image), imagesy($image)];
        $scale            = min(1, $max / max($width, $height));
        [$outW, $outH]    = [max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale))];

        if ($scale < 1) {
            $canvas = imagecreatetruecolor($outW, $outH);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
            imagecopyresampled($canvas, $image, 0, 0, 0, 0, $outW, $outH, $width, $height);
            imagedestroy($image);
            $image = $canvas;
        } else {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }

        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0775, true);
        }

        $ok = imagewebp($image, $target, $quality);
        imagedestroy($image);

        return $ok;
    }

    /**
     * Files with one of the extensions directly in a folder (relative to the root), sorted.
     *
     * @param list<string> $extensions
     *
     * @return list<string>
     */
    private function listFiles(string $dir, array $extensions): array
    {
        $files = [];

        foreach (@scandir($this->root . '/' . $dir) ?: [] as $name) {
            if (is_file($this->root . '/' . $dir . '/' . $name) && in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), $extensions, true)) {
                $files[] = $dir . '/' . $name;
            }
        }

        sort($files);

        return $files;
    }

    /** @return list<array<string, string>> */
    private function readCsv(string $path): array
    {
        $handle = @fopen($this->root . '/' . $path, 'rb');

        if ($handle === false) {
            throw new RuntimeException($path . ' tidak dapat dibaca.');
        }

        $header = fgetcsv($handle, null, ',', '"', '');
        $rows   = [];

        while (is_array($header) && ($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
            if (count($row) === count($header)) {
                $rows[] = array_combine($header, $row);
            }
        }

        fclose($handle);

        return $rows;
    }
}
