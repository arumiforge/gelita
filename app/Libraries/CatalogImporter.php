<?php

namespace App\Libraries;

use App\Models\AuditLogModel;
use App\Models\MediaAssetModel;
use CodeIgniter\Database\BaseConnection;
use RuntimeException;

/**
 * Installs the images of the team's Katalog Aset (`gelita-katalog/`) into
 * the game's image slots (`php spark gelita:katalog:impor`).
 *
 * The catalogue's `data.json` lists every entry with its asset key and file.
 * Each usable image goes through MediaStore::storeCopy(), so it gets the
 * same checks as an upload in the panel (type, size, shrinking to the slot
 * size) and lands in public/assets/uploads/ under its official name.
 *
 * - The catalogue keeps lossless WebP (backgrounds about 2 MB). Every image is
 *   re-encoded as lossy WebP (quality WEBP_QUALITY, alpha kept for
 *   transparent slots) before storing, so it fits the size budget of the
 *   loading curtain (a background about 250 KB instead of 2 MB).
 * - Slot keys the game reads by name (logo, buttons, backgrounds, maps,
 *   badges, pins, lanterns, avatars, character frames, challenge images)
 *   are stored under the same key.
 * - App icons are converted to PNG, because the `app.*` slots take PNG only;
 *   the favicon is made from the 192 icon, since the catalogue one is
 *   smaller than the slot.
 * - Story slide backgrounds `bg.cerita-intro-NN` / `bg.cerita-penutup-NN`
 *   become `bg.story.{intro|ending}.{n}` and are linked to their slide.
 * - Library images `library.{wilayah}.p{n}.{i}` are linked to media `i` of
 *   page `n` (a new media row for pages without one); `….poster` becomes the
 *   cover of that page's video. The image of a video entry is not used.
 * - The catalogue's region maps have four clearings for the challenge
 *   buttons. When a map file is the one measured here (same sha256), the
 *   region's challenge buttons are moved onto those clearings.
 *
 * Nothing an admin uploaded is replaced unless `$overwrite` is set: a slot is
 * filled when it is empty, a sample (mockup), a bundled default file, or a
 * Commons link. Rows installed by this class carry CREDIT, so a second run
 * skips them and an admin can find what is still interim art.
 */
final class CatalogImporter
{
    /** media_assets.credit of every image installed from the catalogue. */
    public const CREDIT = 'Katalog Aset GELITA (gambar sementara)';

    /** library_media.credit of library illustrations from the catalogue. */
    public const LIBRARY_CREDIT = 'Ilustrasi GELITA (dibuat dengan bantuan AI, Katalog Aset)';

    /**
     * Challenge button positions (percent of the map) measured on the
     * catalogue's region maps, keyed by the sha256 of the map file, in
     * challenge order 1–4. Owner decision 11 October 2026: buttons sit on the
     * map's clearings.
     */
    public const MAP_STOPS = [
        '44d29140bd864e6ab1244a5bf28a6f9354ff9d2448d005405f194b48fe256823' => ['temanggung', [1 => [29.0, 35.0], 2 => [82.0, 46.0], 3 => [35.0, 65.0], 4 => [80.0, 72.0]]],
        '978611c74fc4e33b26c924ce2070c6c788f43fa9f6623ce3de24432f746c34f7' => ['magelang', [1 => [31.5, 39.0], 2 => [70.0, 45.0], 3 => [78.0, 75.5], 4 => [38.5, 68.0]]],
        '2eac0b7b34e2a570ead3fd41fade25b830578b0b7363177a43380060c9beba07' => ['wonosobo', [1 => [21.5, 35.0], 2 => [61.0, 51.0], 3 => [18.0, 71.0], 4 => [77.0, 81.5]]],
    ];

    /** Catalogue app icon key → [slot key, keep transparency]. */
    private const APP_ICONS = [
        'app.icon-512.png'          => ['app.icon-512', true],
        'app.icon-192.png'          => ['app.icon-192', true],
        'app.icon-maskable-512.png' => ['app.icon-maskable', false],
        'app.apple-touch-icon.png'  => ['app.apple-touch', false],
    ];

    /** Lossy WebP quality for every stored image except the PNG app icons. */
    public const WEBP_QUALITY = 82;

    /** Background of app icons without transparency (--navy-900). */
    private const ICON_BACKGROUND = [0x0B, 0x13, 0x20];

    /** Report status codes → words for the command output. */
    public const STATUSES = [
        'installed' => 'dipasang',
        'planned'   => 'akan dipasang',
        'skipped'   => 'dilewati',
        'failed'    => 'gagal',
    ];

    private BaseConnection $db;

    private MediaStore $store;

    /** @var list<array{source: string, target: string, status: string, note: string}> */
    private array $report = [];

    /** @var list<string> temporary files to remove at the end */
    private array $temp = [];

    /** @var array<string, array{0: string, 1: array<int, array{0: float, 1: float}>}> */
    private array $mapStops;

    /**
     * @param array<string, array{0: string, 1: array<int, array{0: float, 1: float}>}>|null $mapStops
     *        measured maps (sha256 → [region, stops]); MAP_STOPS unless a test passes its own
     */
    public function __construct(private readonly string $root, ?MediaStore $store = null, ?array $mapStops = null)
    {
        $this->mapStops = $mapStops ?? self::MAP_STOPS;
        $this->db    = db_connect();
        $this->store = $store ?? new MediaStore();
    }

    /**
     * Visual entries of the catalogue: asset key, absolute file path, group.
     *
     * @return list<array{key: string, file: string, cat: string}>
     */
    public function entries(): array
    {
        $path = rtrim($this->root, '/\\') . DIRECTORY_SEPARATOR . 'data.json';

        if (! is_file($path)) {
            throw new RuntimeException('Katalog Aset tidak ditemukan: ' . $path . ' tidak ada.');
        }

        $data = json_decode((string) file_get_contents($path), true);

        if (! is_array($data)) {
            throw new RuntimeException('data.json Katalog Aset tidak dapat dibaca.');
        }

        $entries = [];

        foreach ($data as $entry) {
            if (! is_array($entry) || ($entry['size'] ?? '') === 'Audio' || ! isset($entry['key'], $entry['file'])) {
                continue;
            }

            $entries[] = [
                'key'  => (string) $entry['key'],
                'file' => rtrim($this->root, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $entry['file']),
                'cat'  => (string) ($entry['cat'] ?? ''),
            ];
        }

        return $entries;
    }

    /**
     * Install every usable catalogue image.
     *
     * @param bool $dryRun    report what would happen, change nothing
     * @param bool $overwrite also replace images uploaded by an admin or installed before
     *
     * @return list<array{source: string, target: string, status: string, note: string}>
     */
    public function run(bool $dryRun = false, bool $overwrite = false, ?int $staffId = null): array
    {
        $this->report = [];
        $entries      = $this->entries();
        $byKey        = array_column($entries, null, 'key');

        try {
            foreach ($entries as $entry) {
                $this->importEntry($entry, $byKey, $dryRun, $overwrite);
            }
        } finally {
            foreach ($this->temp as $file) {
                @unlink($file);
            }

            $this->temp = [];
        }

        $installed = count(array_filter($this->report, static fn (array $r): bool => $r['status'] === 'installed'));

        if (! $dryRun && $installed > 0) {
            service('contentRepository')->flush();

            model(AuditLogModel::class)->record('catalog_import', [
                'staff_user_id' => $staffId,
                'metadata'      => ['installed' => $installed, 'overwrite' => $overwrite, 'source' => basename(rtrim($this->root, '/\\'))],
            ]);
        }

        return $this->report;
    }

    /** @param array<string, array{key: string, file: string, cat: string}> $byKey */
    private function importEntry(array $entry, array $byKey, bool $dryRun, bool $overwrite): void
    {
        $key = $entry['key'];

        if (str_starts_with($key, 'reference.') || str_starts_with($key, 'icon.') || in_array($key, ['ui.placeholder', 'app.icon.svg'], true)) {
            return; // references, vector icons, and the placeholder are not game slots
        }

        if (! is_file($entry['file'])) {
            $this->add($key, '—', 'failed', 'berkas tidak ada di katalog');

            return;
        }

        if (isset(self::APP_ICONS[$key])) {
            [$slot, $transparent] = self::APP_ICONS[$key];
            $this->installSlot($key, $slot, fn (): ?string => $this->png($entry['file'], null, $transparent), $dryRun, $overwrite);

            return;
        }

        if ($key === 'app.favicon.ico') {
            // The catalogue favicon is 32 px; the slot needs 48 px, so it is made from the 192 icon
            $source = $byKey['app.icon-192.png']['file'] ?? $entry['file'];
            $this->installSlot($key, 'app.favicon', fn (): ?string => $this->png($source, [48, 48], true), $dryRun, $overwrite);

            return;
        }

        if (preg_match('/^bg\.cerita-(intro|penutup)-(\d+)$/', $key, $m) === 1) {
            $this->installStory($key, $entry['file'], $m[1] === 'intro' ? 'intro' : 'ending', (int) $m[2], $dryRun, $overwrite);

            return;
        }

        if (preg_match('/^library\.([a-z]+)\.p(\d+)\.(\d+)(\.poster)?$/', $key, $m) === 1) {
            $this->installLibrary($key, $entry['file'], $m[1], (int) $m[2], (int) $m[3], ($m[4] ?? '') !== '', $dryRun, $overwrite);

            return;
        }

        if (ImageSlots::patternFor($key) === null) {
            $this->add($key, $key, 'skipped', 'bukan slot gambar permainan');

            return;
        }

        $installed = $this->installSlot($key, $key, fn (): ?string => $this->webp($entry['file'], $key), $dryRun, $overwrite);
        $planned   = $dryRun && end($this->report)['status'] === 'planned';

        if (str_starts_with($key, 'map.region.') && ($installed || $planned || $this->isFromCatalog($key))) {
            $this->placeStops($entry['file'], $dryRun);
        }

        if ($installed && ! $dryRun) {
            $this->linkLevel($key);
        }
    }

    /**
     * Store one file under a slot key when the slot may be filled.
     *
     * @param callable(): ?string $source path of the file to store (may convert first)
     */
    private function installSlot(string $source, string $target, callable $file, bool $dryRun, bool $overwrite): bool
    {
        $row    = model(MediaAssetModel::class)->findByKey($target);
        $reason = $this->blocked($row, $overwrite);

        if ($reason !== null) {
            $this->add($source, $target, 'skipped', $reason);

            return false;
        }

        if ($dryRun) {
            $this->add($source, $target, 'planned', $row === null ? 'slot baru' : $this->replaces($row));

            return false;
        }

        $path = $file();

        if ($path === null) {
            $this->add($source, $target, 'failed', 'gambar katalog tidak dapat dibuka');

            return false;
        }

        $result = $this->store->storeCopy($path, $target, ['image'], null, true);

        if ($result['error'] !== null) {
            $this->add($source, $target, 'failed', $result['error']);

            return false;
        }

        model(MediaAssetModel::class)->update((int) $result['id'], ['credit' => self::CREDIT]);
        $this->add($source, $target, 'installed', $row === null ? 'slot baru' : $this->replaces($row));

        return true;
    }

    /** Story slide background: stored as bg.story.{context}.{n} and linked to that slide. */
    private function installStory(string $source, string $file, string $context, int $sequence, bool $dryRun, bool $overwrite): void
    {
        $target = "bg.story.{$context}.{$sequence}";
        $slide  = $this->db->table('dialogues')->select('id, background_media_id')
            ->where('level_id', null)->where('context_code', $context)->where('sequence', $sequence)
            ->get()->getRowArray();

        if ($slide === null) {
            $this->add($source, $target, 'skipped', 'slide cerita ' . $context . ' ' . $sequence . ' tidak ada');

            return;
        }

        $current = $slide['background_media_id'] === null ? null : model(MediaAssetModel::class)->find((int) $slide['background_media_id']);

        if ($current !== null && $current['asset_key'] !== $target && ($reason = $this->blocked($current, $overwrite)) !== null) {
            $this->add($source, $target, 'skipped', 'slide sudah memakai ' . $current['asset_key'] . ': ' . $reason);

            return;
        }

        if ($this->installSlot($source, $target, fn (): ?string => $this->webp($file, $target), $dryRun, $overwrite)) {
            $mediaId = model(MediaAssetModel::class)->findByKey($target)['id'] ?? null;
            $this->db->table('dialogues')->where('id', $slide['id'])->update(['background_media_id' => $mediaId]);
        }
    }

    /** Library image or video cover, linked to media `sequence` of page `page`. */
    private function installLibrary(string $source, string $file, string $region, int $page, int $sequence, bool $poster, bool $dryRun, bool $overwrite): void
    {
        $pageId = $this->db->table('library_pages lp')->select('lp.id')
            ->join('levels l', 'l.id = lp.level_id')
            ->where('l.code', $region)->where('lp.sequence', $page)
            ->get()->getRow('id');

        if ($pageId === null) {
            $this->add($source, $source, 'skipped', 'halaman Pustaka tidak ada');

            return;
        }

        $media = $this->db->table('library_media')->where('library_page_id', $pageId)->where('sequence', $sequence)->get()->getRowArray();
        $kind  = $media['media_kind'] ?? 'image';

        if ($poster) {
            if ($media === null || $kind !== 'video') {
                $this->add($source, $source, 'skipped', 'media ini bukan video');

                return;
            }

            $current = $media['poster_media_id'] === null ? null : model(MediaAssetModel::class)->find((int) $media['poster_media_id']);

            if ($current !== null && ($reason = $this->blocked($current, $overwrite)) !== null) {
                $this->add($source, $source, 'skipped', 'sampul video sudah ada: ' . $reason);

                return;
            }

            if ($this->installSlot($source, $source, fn (): ?string => $this->webp($file, $source), $dryRun, $overwrite)) {
                $mediaId = model(MediaAssetModel::class)->findByKey($source)['id'] ?? null;
                $this->db->table('library_media')->where('id', $media['id'])->update(['poster_media_id' => $mediaId]);
            }

            return;
        }

        if ($kind === 'video') {
            $this->add($source, $source, 'skipped', 'media ini video; yang dipakai hanya gambar sampulnya');

            return;
        }

        $current = $media === null || $media['media_asset_id'] === null ? null : model(MediaAssetModel::class)->find((int) $media['media_asset_id']);

        if ($current !== null && ($reason = $this->blocked($current, $overwrite)) !== null) {
            $this->add($source, $source, 'skipped', 'gambar Pustaka sudah ada: ' . $reason);

            return;
        }

        if (! $this->installSlot($source, $source, fn (): ?string => $this->webp($file, $source), $dryRun, $overwrite)) {
            return;
        }

        $mediaId = model(MediaAssetModel::class)->findByKey($source)['id'] ?? null;
        $payload = ['media_kind' => 'image', 'media_asset_id' => $mediaId, 'external_url' => null, 'credit' => self::LIBRARY_CREDIT];

        if ($media === null) {
            $now = date('Y-m-d H:i:s');
            $this->db->table('library_media')->insert($payload + [
                'library_page_id' => $pageId, 'sequence' => $sequence, 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
            ]);
        } else {
            $this->db->table('library_media')->where('id', $media['id'])->update($payload + ['updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    /** Move a region's challenge buttons onto the clearings of a measured catalogue map. */
    private function placeStops(string $file, bool $dryRun): void
    {
        $known = $this->mapStops[(string) hash_file('sha256', $file)] ?? null;

        if ($known === null) {
            return; // a map not measured here: positions stay as they are
        }

        [$region, $stops] = $known;
        $levelId          = $this->db->table('levels')->select('id')->where('code', $region)->get()->getRow('id');

        if ($levelId === null) {
            return;
        }

        foreach ($stops as $sequence => [$x, $y]) {
            $node = $this->db->table('challenge_nodes')->select('id, map_x, map_y')
                ->where('level_id', $levelId)->where('sequence', $sequence)->get()->getRowArray();

            if ($node === null) {
                continue;
            }

            $label = "challenge_nodes {$region} {$sequence}";

            if (abs((float) $node['map_x'] - $x) < 0.01 && abs((float) $node['map_y'] - $y) < 0.01) {
                $this->add('map.region.' . $region, $label, 'skipped', 'tombol sudah di lapangan peta');

                continue;
            }

            if (! $dryRun) {
                $this->db->table('challenge_nodes')->where('id', $node['id'])->update(['map_x' => number_format($x, 2, '.', ''), 'map_y' => number_format($y, 2, '.', '')]);
            }

            $this->add('map.region.' . $region, $label, $dryRun ? 'planned' : 'installed', sprintf('tombol dipindah ke %s %%, %s %%', $x, $y));
        }
    }

    /** Fill the region's map/background/badge column when it is still empty. */
    private function linkLevel(string $key): void
    {
        $columns = ['map.region.' => 'map_media_id', 'reward.badge.' => 'badge_media_id'];

        foreach ($columns as $prefix => $column) {
            if (str_starts_with($key, $prefix)) {
                $this->linkLevelColumn(substr($key, strlen($prefix)), $column, $key);
            }
        }

        if (preg_match('/^bg\.([a-z]+)\.region$/', $key, $m) === 1) {
            $this->linkLevelColumn($m[1], 'background_media_id', $key);
        }
    }

    private function linkLevelColumn(string $region, string $column, string $key): void
    {
        $mediaId = model(MediaAssetModel::class)->findByKey($key)['id'] ?? null;

        if ($mediaId !== null) {
            $this->db->table('levels')->where('code', $region)->where($column, null)->update([$column => $mediaId]);
        }
    }

    /**
     * Why a slot must not be filled, or null when it may be: empty, a sample,
     * a bundled default file, or (with $overwrite) anything.
     *
     * @param array<string, mixed>|null $row
     */
    private function blocked(?array $row, bool $overwrite): ?string
    {
        if ($row === null || $overwrite || (int) $row['is_active'] !== 1 || (int) ($row['is_placeholder'] ?? 0) === 1) {
            return null;
        }

        if ((string) ($row['credit'] ?? '') === self::CREDIT) {
            return 'sudah dipasang dari katalog (pakai --timpa untuk memasang ulang)';
        }

        if (! str_starts_with((string) $row['storage_path'], MediaStore::UPLOAD_DIR)) {
            return null; // bundled default file under public/assets/{ui,bg,…}
        }

        return 'sudah ada gambar unggahan admin (pakai --timpa untuk mengganti)';
    }

    /** @param array<string, mixed> $row */
    private function replaces(array $row): string
    {
        return match (true) {
            (int) $row['is_active'] !== 1               => 'mengisi slot kosong',
            (int) ($row['is_placeholder'] ?? 0) === 1   => 'mengganti gambar contoh',
            (string) ($row['credit'] ?? '') === self::CREDIT => 'memasang ulang dari katalog',
            ! str_starts_with((string) $row['storage_path'], MediaStore::UPLOAD_DIR) => 'mengganti berkas bawaan',
            default                                     => 'mengganti gambar unggahan admin',
        };
    }

    private function isFromCatalog(string $key): bool
    {
        $row = model(MediaAssetModel::class)->findByKey($key);

        return $row !== null && (int) $row['is_active'] === 1 && (string) ($row['credit'] ?? '') === self::CREDIT;
    }

    /**
     * Re-encode a catalogue image as a temporary lossy WebP; the alpha
     * channel is kept for transparent slots. SVG and unreadable files are
     * passed on unchanged for MediaStore to judge.
     */
    private function webp(string $source, string $key): ?string
    {
        $data  = @file_get_contents($source);
        $image = $data === false ? false : @imagecreatefromstring($data);

        if ($image === false) {
            return $source;
        }

        $transparent = (bool) (ImageSlots::describe($key)['transparent'] ?? true);
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, $transparent);

        $path = $this->tempPath('.webp');

        return $path !== null && imagewebp($image, $path, self::WEBP_QUALITY) ? $path : null;
    }

    private function tempPath(string $extension): ?string
    {
        $dir = WRITEPATH . 'tmp';

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $path = tempnam($dir, 'katalog-');

        if ($path === false) {
            return null;
        }

        @unlink($path);
        $this->temp[] = $path . $extension;

        return $path . $extension;
    }

    /**
     * Convert an image to a temporary PNG (app icons take PNG only),
     * optionally resized and flattened onto the navy app background.
     *
     * @param array{0: int, 1: int}|null $size
     */
    private function png(string $source, ?array $size, bool $transparent): ?string
    {
        $data  = @file_get_contents($source);
        $image = $data === false ? false : @imagecreatefromstring($data);

        if ($image === false) {
            return null;
        }

        [$width, $height] = [imagesx($image), imagesy($image)];
        [$outW, $outH]    = $size ?? [$width, $height];
        $canvas           = imagecreatetruecolor($outW, $outH);

        if ($transparent) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        } else {
            imagefill($canvas, 0, 0, imagecolorallocate($canvas, ...self::ICON_BACKGROUND));
            imagealphablending($canvas, true);
        }

        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $outW, $outH, $width, $height);

        $path = $this->tempPath('.png');

        return $path !== null && imagepng($canvas, $path) ? $path : null;
    }

    private function add(string $source, string $target, string $status, string $note): void
    {
        $this->report[] = ['source' => $source, 'target' => $target, 'status' => $status, 'note' => $note];
    }
}
