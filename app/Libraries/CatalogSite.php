<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Builds the public Katalog Aset site in `gelita-katalog/` (owner,
 * 17 November 2026): one static page of every picture, music track, sound
 * effect and narration the game uses, served at https://katalog.gelita.id/
 * from a repository checkout.
 *
 * The folder holds only the page (index.html, katalog.css, katalog.js) and
 * the generated list data.js. Pictures and sounds are not copied (owner,
 * 17 November 2026: the repository must not grow with asset files): every
 * entry links to the file the live game serves under BASE_URL. A file that
 * is not on that server yet shows a placeholder on the page, and the entry
 * stays listed.
 *
 * Sources (the game leads; nothing here is kept by hand):
 * - pictures: `ai-studio/game-images/data.json` (one still picture per
 *   character pose), the mock-up
 *   pictures of CatalogImporter::MOCKUP_FILES and MOCKUP_DIRS, and the UI art
 *   that ships with the game (`public/assets/ui/`, `public/assets/app/`);
 *   where each picture is used comes from ImageSlots;
 * - review status: the Status and Notes columns of `REVIEW.md`;
 * - music and sound effects: `public/assets/audio/{music,sfx}/` with the
 *   screen and mood columns of `ai-studio/elevenlabs-sound/sounds.csv`;
 * - narration: the lines of `ai-studio/elevenlabs-voice/lines.csv`, each
 *   linked to its take on the server in both languages.
 *
 * Served paths follow the game's own storage rules: slot pictures are
 * stored by CatalogImporter/MediaStore as `assets/uploads/{slot key}.webp`
 * (app icons `.png`, story backgrounds under their `bg.story.*` slide key),
 * shipped UI art stays in `assets/ui/` and `assets/app/`, music and sound
 * effects in `assets/audio/{music,sfx}/`, narration in
 * `assets/audio/narasi/{id,en}/{code}.mp3` (NarrationImporter::FOLDER).
 *
 * Prompt texts are never copied into the site, and the masters in
 * ai-studio/ are never linked. Output is deterministic.
 */
final class CatalogSite
{
    /** Output folder, relative to the repository root. */
    public const OUT_DIR = 'gelita-katalog';

    /** The live game whose files the catalogue links to (`--basis` overrides it). */
    public const BASE_URL = 'https://gelita.id';

    /** Static files of the page, copied from TEMPLATE_DIR. */
    public const STATIC_FILES = ['index.html', 'katalog.css', 'katalog.js'];

    public const DATA_FILE = 'data.js';

    /** Hand-written files of the folder that the build keeps. */
    public const KEPT_FILES = ['README.md'];

    /** Folders of the earlier self-contained edition; the build removes them. */
    public const OLD_DIRS = ['gambar', 'suara', 'huruf'];

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

    /** Region story word of a background key → `dialogues.context_code` (as CatalogImporter::REGION_STORY_CONTEXTS). */
    private const STORY_CONTEXTS = [
        'kenal'  => 'region_intro',
        'dialog' => 'level_open',
        'tuntas' => 'level_done',
        'layar'  => 'scenario_open',
    ];

    /** Catalogue app icon key → slot, stored as PNG (as CatalogImporter::APP_ICONS; the favicon is made from the 192 icon). */
    private const APP_ICON_SLOTS = [
        'app.icon-512.png'          => 'app.icon-512',
        'app.icon-192.png'          => 'app.icon-192',
        'app.icon-maskable-512.png' => 'app.icon-maskable',
        'app.apple-touch-icon.png'  => 'app.apple-touch',
        'app.favicon.ico'           => 'app.favicon',
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
        'challenge-badge'           => 'Lencana tantangan',
        'map-head-plate'            => 'Pelat judul peta',
        'ornate-crest'              => 'Hiasan mahkota emas',
        'select-arrow'              => 'Panah kotak pilihan',
        'kedu-region-'              => 'Penutup wilayah terkunci di Peta Kedu',
        'ui-button-gold'            => 'Tombol tenang emas',
        'ui-button-navy'            => 'Tombol tenang biru tua',
        'ui-button-green'           => 'Tombol tenang hijau',
        'error-'                    => 'Gambar halaman galat',
        'ui-plate-gold'             => 'Pelat emas',
        'ui-plate-navy'             => 'Pelat biru tua',
        'ui-plate-green'            => 'Pelat hijau',
        'ui-frame'                  => 'Bingkai jendela',
        'ui-track'                  => 'Bilah perunggu',
        'node-card-'                => 'Kartu tantangan di peta wilayah',
        'hud-shards-'               => 'Penghitung Serpihan Cahaya',
        'gate-icon-'                => 'Ikon layar gerbang',
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

    private readonly string $base;

    /** @var array<string, true>|null */
    private ?array $videos = null;

    /**
     * @param string      $root repository root
     * @param string|null $out  output folder (OUT_DIR under $root unless a test passes its own)
     * @param string|null $base server the entries link to (BASE_URL unless given)
     */
    public function __construct(?string $root = null, ?string $out = null, ?string $base = null)
    {
        $this->root = rtrim($root ?? ROOTPATH, '/\\');
        $this->out  = rtrim($out ?? $this->root . '/' . self::OUT_DIR, '/\\');
        $this->base = rtrim($base ?? self::BASE_URL, '/');
    }

    public function baseUrl(): string
    {
        return $this->base;
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
            $image['d'] = trim($image['d'] . ' Permainan memakai satu gambar diam per pose.');
        }

        $image['p'] = null;

        foreach ($keys as $k) {
            // A Pustaka video is never installed itself; the game shows only its cover (`….poster`)
            if (! isset($this->videoKeys()[$k])) {
                $image['p'] ??= $this->servedPath($k);
            }
        }

        if ($image['p'] === null && isset($this->videoKeys()[$key])) {
            $image['u'][] = 'Video Pustaka: permainan hanya memakai gambar sampulnya.';
        }

        return $this->withFile($image, $file);
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

        // The game's own ?v= uses the server file's time; a content hash busts a cached copy the same way
        $image['p'] = 'assets/' . substr($path, strlen('public/assets/')) . '?v=' . substr((string) hash_file('sha256', $this->root . '/' . $path), 0, 8);

        return $this->withFile($image, $path);
    }

    /**
     * Pustaka keys that have a `.poster` entry in data.json, i.e. videos.
     *
     * @return array<string, true>
     */
    private function videoKeys(): array
    {
        if ($this->videos === null) {
            $this->videos = [];
            $data         = json_decode((string) @file_get_contents($this->root . '/' . self::IMAGE_DIR . '/data.json'), true);

            foreach (is_array($data) ? $data : [] as $entry) {
                if (is_array($entry) && preg_match('/^(library\..+)\.poster$/', (string) ($entry['key'] ?? ''), $m) === 1) {
                    $this->videos[$m[1]] = true;
                }
            }
        }

        return $this->videos;
    }

    /**
     * Adds the original size and the review status.
     *
     * @param array<string, mixed> $image
     *
     * @return array<string, mixed>
     */
    private function withFile(array $image, string $path): array
    {
        $ext    = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $size   = $ext === 'svg' ? false : @getimagesize($this->root . '/' . $path);
        $review = $this->review()[$path] ?? null;

        $image['wh']   = is_array($size) ? [(int) $size[0], (int) $size[1]] : null;
        $image['s']    = $review['st'] ?? 'none';
        $image['sn']   = $review === null ? '' : trim($review['text'] . ($review['text'] !== '' && $review['notes'] !== '' ? ' — ' : '') . $review['notes']);
        $image['no']   = $review['no'] ?? null;
        $image['_src'] = $path;

        return $image;
    }

    /**
     * Path (under the server root) where the game stores the picture of a
     * catalogue key once `gelita:katalog:impor` installed it, or null for
     * keys it does not install (CatalogImporter::importEntry()).
     */
    public function servedPath(string $key): ?string
    {
        $slot = CatalogImporter::ICON_SLOTS[$key] ?? CatalogImporter::REFERENCE_SLOTS[$key] ?? null;

        if ($slot !== null) {
            return 'assets/uploads/' . $slot . '.webp';
        }

        if (str_starts_with($key, 'reference.') || str_starts_with($key, 'icon.') || in_array($key, ['ui.placeholder', 'app.icon.svg'], true)) {
            return null;
        }

        if (isset(self::APP_ICON_SLOTS[$key])) {
            return 'assets/uploads/' . self::APP_ICON_SLOTS[$key] . '.png';
        }

        if (preg_match('/^bg\.cerita-(intro|penutup)-(\d+)$/', $key, $m) === 1) {
            return 'assets/uploads/bg.story.' . ($m[1] === 'intro' ? 'intro' : 'ending') . '.' . (int) $m[2] . '.webp';
        }

        if (preg_match('/^bg\.cerita-([a-z]+)-([a-z]+)-(\d+)$/', $key, $m) === 1) {
            return isset(self::STORY_CONTEXTS[$m[1]]) ? 'assets/uploads/bg.story.' . self::STORY_CONTEXTS[$m[1]] . '.' . $m[2] . '.' . (int) $m[3] . '.webp' : null;
        }

        if (str_starts_with($key, 'library.') || ImageSlots::patternFor($key) !== null) {
            return 'assets/uploads/' . $key . '.webp';
        }

        return null;
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
                'f'    => 'assets/audio/' . $kind . '/' . $name . '.mp3',
            ];
        }

        return $sounds;
    }

    /**
     * Narration lines in story order, with the served path of the take per language.
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

            // Every line links to where the game serves its take (NarrationImporter::FOLDER); a missing one shows a placeholder
            $line['fi'] = 'assets/audio/narasi/id/' . $code . '.mp3';
            $line['fe'] = 'assets/audio/narasi/en/' . $code . '.mp3';

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
    public function dataScript(array $model): string
    {
        $json  = static fn ($value): string => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $clean = static fn (array $rows): array => array_map(
            static fn (array $row): array => array_filter($row, static fn ($k): bool => ! str_starts_with((string) $k, '_'), ARRAY_FILTER_USE_KEY),
            $rows,
        );

        $out = "/* Dibuat oleh php spark gelita:katalog:halaman. Jangan diubah dengan tangan. */\n";
        $out .= "window.KATALOG = {\n";
        $out .= '"base": ' . $json($this->base . '/') . ",\n";
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
     * Every file the build writes → its text.
     *
     * @return array<string, string>
     */
    private function files(array $model): array
    {
        $texts = [self::DATA_FILE => $this->dataScript($model)];

        foreach (self::STATIC_FILES as $file) {
            $texts[$file] = (string) file_get_contents(self::TEMPLATE_DIR . '/' . $file);
        }

        return $texts;
    }

    // ----------------------------------------------------------------- build

    /**
     * Writes the page. Returns what was (or, in a dry run, would be) done.
     *
     * @return array{written: list<string>, removed: list<string>, unchanged: int, model: array}
     */
    public function build(bool $dryRun = false): array
    {
        $model  = $this->model();
        $report = ['written' => [], 'removed' => [], 'unchanged' => 0, 'model' => $model];

        if (! $dryRun && ! is_dir($this->out) && ! mkdir($this->out, 0775, true) && ! is_dir($this->out)) {
            throw new RuntimeException('Folder ' . $this->out . ' tidak dapat dibuat.');
        }

        foreach ($this->files($model) as $file => $text) {
            if (@file_get_contents($this->out . '/' . $file) === $text) {
                $report['unchanged']++;

                continue;
            }

            if (! $dryRun) {
                file_put_contents($this->out . '/' . $file, $text);
            }

            $report['written'][] = $file;
        }

        foreach ($this->staleEntries() as $stale) {
            if (! $dryRun) {
                $this->delete($this->out . '/' . $stale);
            }

            $report['removed'][] = $stale;
        }

        return $report;
    }

    /**
     * Why the folder does not match the sources (empty when it is current).
     *
     * @return list<string>
     */
    public function check(): array
    {
        $problems = [];

        foreach ($this->files($this->model()) as $file => $text) {
            if (@file_get_contents($this->out . '/' . $file) !== $text) {
                $problems[] = $file === self::DATA_FILE
                    ? self::DATA_FILE . ' tidak sesuai dengan sumbernya (gambar, daftar tinjauan, suara, atau narasi berubah).'
                    : $file . ' tidak sama dengan templatnya di app/Libraries/CatalogSite/.';
            }
        }

        foreach ($this->staleEntries() as $stale) {
            $problems[] = 'Berkas atau folder yang tidak dipakai lagi: ' . $stale;
        }

        return $problems;
    }

    /**
     * Every picture and sound URL of the model, for `--cek-tautan`.
     *
     * @return list<string>
     */
    public function urls(): array
    {
        $model = $this->model();
        $paths = [];

        foreach ($model['images'] as $image) {
            if ($image['p'] !== null) {
                $paths[] = $image['p'];
            }
        }

        foreach (['music', 'sfx'] as $name) {
            foreach ($model[$name] as $sound) {
                $paths[] = $sound['f'];
            }
        }

        foreach ($model['narration'] as $line) {
            $paths[] = $line['fi'];
            $paths[] = $line['fe'];
        }

        return array_map(fn (string $path): string => $this->base . '/' . $path, array_values(array_unique($paths)));
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
     * Top-level files the build neither writes nor keeps, and the media
     * folders of the earlier self-contained edition. Other folders are left alone.
     *
     * @return list<string>
     */
    private function staleEntries(): array
    {
        if (! is_dir($this->out)) {
            return [];
        }

        $known = array_flip(array_merge(self::STATIC_FILES, [self::DATA_FILE], self::KEPT_FILES));
        $stale = [];

        foreach (scandir($this->out) ?: [] as $name) {
            if ($name === '.' || $name === '..' || isset($known[$name])) {
                continue;
            }

            if (is_file($this->out . '/' . $name) || in_array($name, self::OLD_DIRS, true)) {
                $stale[] = $name;
            }
        }

        sort($stale);

        return $stale;
    }

    private function delete(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);

            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $file) {
            $file->isDir() ? @rmdir($file->getPathname()) : @unlink($file->getPathname());
        }

        @rmdir($path);
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
