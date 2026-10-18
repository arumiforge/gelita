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
 * - The two flag icons `icon.language-id` / `icon.language-en` become the
 *   language switch icons `ui.lang.id`/`.en` (ICON_SLOTS); other vector
 *   icons and every `reference.*` entry are not game slots.
 * - A few pictures live only in a catalogue mockup package, not in data.json
 *   (MOCKUP_FILES): the Peta Kedu diorama becomes `map.kedu-diorama`.
 * - Some entries also fill a second slot (COPY_SLOTS): `avatar.2` stays an
 *   avatar and is copied to the result screen medal `ui.result-medal`.
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
 * - The Cari Objek scenes have tables and shelves. When a scene file is the
 *   one measured here, objects still at their old bank position are moved
 *   onto them; an object an admin placed elsewhere stays unless overwriting.
 *
 * Nothing an admin uploaded is replaced unless `$overwrite` is set: a slot is
 * filled when it is empty, a sample (mockup), a bundled default file, or a
 * Commons link. Rows installed by this class carry CREDIT, so an admin can
 * find what is still interim art; a later run reinstalls such a row only
 * when its catalogue file changed (same re-encoded sha256 → skipped), so a
 * new catalogue edition (e.g. the 24-frame poses of 12 October 2026) reaches
 * a server that imported an older one.
 *
 * `$only` limits a run to some slots, so `$overwrite` can replace e.g. the
 * old lurik frames of one character without touching other admin uploads.
 * A pose left with frames from two sources (a kept upload among catalogue
 * frames) is reported as a warning, since its loop would switch drawings.
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

    /**
     * Cari Objek object positions (x, y, w in percent of the 16:9 scene, as in
     * the question bank) measured on the catalogue's scenes, keyed by the
     * sha256 of the scene file: object media key → [old bank position, new
     * position]. Owner decision 12 October 2026: objects sit on the tables
     * and shelves drawn in the scene (the bank carries the same new values).
     */
    public const SCENE_OBJECTS = [
        '2e51f64d15fc5567617239aae838949fe90adb8715f78a309cd2e83512f01261' => ['challenge.tmg-4.scene', [
            'challenge.item.tmg-4-02' => [[44.0, 6.0, 12.0], [14.0, 31.0, 10.0]],
            'challenge.item.tmg-4-11' => [[64.0, 6.0, 12.0], [45.0, 31.0, 10.0]],
            'challenge.item.tmg-4-03' => [[84.0, 6.0, 12.0], [76.0, 31.0, 10.0]],
            'challenge.item.tmg-4-04' => [[14.0, 40.0, 12.0], [29.0, 43.0, 11.0]],
            'challenge.item.tmg-4-12' => [[54.0, 40.0, 12.0], [60.0, 43.0, 11.0]],
            'challenge.item.tmg-4-06' => [[4.0, 72.0, 12.0], [12.0, 56.5, 12.0]],
            'challenge.item.tmg-4-10' => [[24.0, 72.0, 12.0], [44.0, 56.5, 12.0]],
            'challenge.item.tmg-4-09' => [[74.0, 40.0, 12.0], [76.0, 56.5, 12.0]],
        ]],
        '0e1696c329f915a3c8c1eb9daa597e218d6335e4c3a438ae5909f002ca092fe2' => ['challenge.mgl-4.scene', [
            'challenge.item.mgl-4-a02' => [[4.0, 56.0, 12.0], [1.0, 54.5, 8.0]],
            'challenge.item.mgl-4-a05' => [[4.0, 17.0, 12.0], [10.5, 54.5, 8.0]],
            'challenge.item.mgl-4-a09' => [[23.5, 56.0, 12.0], [20.0, 54.5, 8.0]],
            'challenge.item.mgl-4-a01' => [[23.5, 17.0, 12.0], [28.5, 48.0, 7.0]],
            'challenge.item.mgl-4-a07' => [[43.0, 17.0, 12.0], [23.5, 45.5, 6.0]],
            'challenge.item.mgl-4-a08' => [[43.0, 56.0, 12.0], [72.0, 54.5, 8.0]],
            'challenge.item.mgl-4-a04' => [[62.5, 56.0, 12.0], [81.5, 54.5, 8.0]],
            'challenge.item.mgl-4-a10' => [[82.0, 56.0, 12.0], [91.0, 54.5, 8.0]],
            'challenge.item.mgl-4-a03' => [[62.5, 17.0, 12.0], [64.5, 48.0, 7.0]],
            'challenge.item.mgl-4-a06' => [[82.0, 17.0, 12.0], [70.5, 45.5, 6.0]],
        ]],
        'e9727ebb0def249783b255a896d1fe3aa39f7194b2bcd7c02228b698cd6738d8' => ['challenge.wnb-4.scene', [
            'challenge.item.wnb-4-a01' => [[9.0, 8.0, 10.0], [31.0, 26.0, 10.0]],
            'challenge.item.wnb-4-a08' => [[33.0, 8.0, 10.0], [47.0, 26.0, 10.0]],
            'challenge.item.wnb-4-a12' => [[81.0, 8.0, 10.0], [63.0, 26.0, 10.0]],
            'challenge.item.wnb-4-a09' => [[9.0, 39.0, 10.0], [15.0, 41.5, 10.0]],
            'challenge.item.wnb-4-a03' => [[33.0, 39.0, 10.0], [35.0, 41.5, 10.0]],
            'challenge.item.wnb-4-a11' => [[57.0, 39.0, 10.0], [55.0, 41.5, 10.0]],
            'challenge.item.wnb-4-a02' => [[81.0, 39.0, 10.0], [75.0, 41.5, 10.0]],
            'challenge.item.wnb-4-a06' => [[9.0, 70.0, 10.0], [7.0, 59.0, 10.0]],
            'challenge.item.wnb-4-a07' => [[33.0, 70.0, 10.0], [25.0, 59.0, 10.0]],
            'challenge.item.wnb-4-a05' => [[57.0, 8.0, 10.0], [45.0, 59.0, 10.0]],
            'challenge.item.wnb-4-a04' => [[57.0, 70.0, 10.0], [65.0, 59.0, 10.0]],
            'challenge.item.wnb-4-a10' => [[81.0, 70.0, 10.0], [83.0, 59.0, 10.0]],
        ]],
    ];

    /** Catalogue app icon key → [slot key, keep transparency]. */
    private const APP_ICONS = [
        'app.icon-512.png'          => ['app.icon-512', true],
        'app.icon-192.png'          => ['app.icon-192', true],
        'app.icon-maskable-512.png' => ['app.icon-maskable', false],
        'app.apple-touch-icon.png'  => ['app.apple-touch', false],
    ];

    /**
     * Catalogue icon entries that fill a game slot: the flag icons of the HUD
     * language switch (catalogue edition of 13–14 October 2026, owner request
     * 18 October 2026). Until then the slots came from `reference.20` /
     * `reference.19`, which the catalogue keeps only as old aliases.
     */
    public const ICON_SLOTS = [
        'icon.language-id' => 'ui.lang.id',
        'icon.language-en' => 'ui.lang.en',
    ];

    /**
     * Catalogue entries installed under their own key and also copied to a
     * second slot (owner decision 18 October 2026): the result screen uses
     * the shard medal `avatar.2`, but from a slot of its own, so changing
     * avatar 2 later does not change the result screen.
     */
    public const COPY_SLOTS = [
        'avatar.2' => 'ui.result-medal',
    ];

    /**
     * Pictures that exist only in a catalogue mockup package (path relative to
     * the catalogue root) → slot (owner decision 18 October 2026: install the
     * Peta Kedu diorama from the mockup until the catalogue lists it).
     */
    public const MOCKUP_FILES = [
        'mockup/gambar-mockup/peta-kedu/revisi-04/aset/diorama.kedu-tiga-wilayah.webp' => 'map.kedu-diorama',
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
        'warning'   => 'perlu dicek',
        'failed'    => 'gagal',
    ];

    private BaseConnection $db;

    private MediaStore $store;

    /** @var list<array{source: string, target: string, status: string, note: string}> */
    private array $report = [];

    /** @var list<string> `$only` patterns of the current run() */
    private array $only = [];

    /** @var list<string> temporary files to remove at the end */
    private array $temp = [];

    /** @var array<string, array{0: string, 1: array<int, array{0: float, 1: float}>}> */
    private array $mapStops;

    /** @var array<string, array{0: string, 1: array<string, array{0: list<float>, 1: list<float>}>}> */
    private array $sceneObjects;

    /**
     * @param array<string, array{0: string, 1: array<int, array{0: float, 1: float}>}>|null $mapStops
     *        measured maps (sha256 → [region, stops]); MAP_STOPS unless a test passes its own
     * @param array<string, array{0: string, 1: array<string, array{0: list<float>, 1: list<float>}>}>|null $sceneObjects
     *        measured Cari Objek scenes (sha256 → [scene key, positions]); SCENE_OBJECTS unless a test passes its own
     */
    public function __construct(private readonly string $root, ?MediaStore $store = null, ?array $mapStops = null, ?array $sceneObjects = null)
    {
        $this->mapStops     = $mapStops ?? self::MAP_STOPS;
        $this->sceneObjects = $sceneObjects ?? self::SCENE_OBJECTS;
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
     * @param bool         $dryRun    report what would happen, change nothing
     * @param bool         $overwrite also replace images uploaded by an admin or installed before
     * @param list<string> $only      fnmatch patterns (e.g. `char.jaka.*`, `ui.btn-start*`); when given,
     *                                only entries whose catalogue key (or, for a reference, the slot it
     *                                fills) matches are handled, so `$overwrite` can be limited to them
     *
     * @return list<array{source: string, target: string, status: string, note: string}>
     */
    public function run(bool $dryRun = false, bool $overwrite = false, ?int $staffId = null, array $only = []): array
    {
        $this->report = [];
        $this->only   = $only;
        $all          = $this->entries();
        $byKey        = array_column($all, null, 'key');
        $entries      = $only === [] ? $all : array_values(array_filter($all, fn (array $entry): bool => $this->matches($entry['key'], $only)));

        try {
            foreach ($entries as $entry) {
                $this->importEntry($entry, $byKey, $dryRun, $overwrite);
            }

            foreach (self::MOCKUP_FILES as $path => $slot) {
                if (isset($byKey[$slot]) || ($only !== [] && ! $this->matches($slot, $only))) {
                    continue; // once data.json lists the slot itself, the catalogue entry wins
                }

                $file = rtrim($this->root, '/\\') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);

                if (is_file($file)) {
                    $this->installSlot(basename($path), $slot, fn (): ?string => $this->webp($file, $slot), $dryRun, $overwrite);
                }
            }
        } finally {
            foreach ($this->temp as $file) {
                @unlink($file);
            }

            $this->temp = [];
        }

        if (! $overwrite) {
            $this->checkPoses($byKey, $only);
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
        $ref = self::ICON_SLOTS[$key] ?? null;

        if (($ref === null && (str_starts_with($key, 'reference.') || str_starts_with($key, 'icon.'))) || in_array($key, ['ui.placeholder', 'app.icon.svg'], true)) {
            return; // references, other icons, and the placeholder are not game slots
        }

        if (! is_file($entry['file'])) {
            $this->add($key, '—', 'failed', 'berkas tidak ada di katalog');

            return;
        }

        if (isset(self::COPY_SLOTS[$key])) {
            $copy = self::COPY_SLOTS[$key];
            $this->installSlot($key, $copy, fn (): ?string => $this->webp($entry['file'], $copy), $dryRun, $overwrite);

            // Matched by --kunci only through the copy's slot: leave the entry's own slot alone
            if ($this->only !== [] && ! $this->matchesOwnKey($key)) {
                return;
            }
        }

        if ($ref !== null) {
            $this->installSlot($key, $ref, fn (): ?string => $this->webp($entry['file'], $ref), $dryRun, $overwrite);

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

        if (str_ends_with($key, '.scene') && ($installed || $planned || $this->isFromCatalog($key))) {
            $this->placeObjects($entry['file'], $dryRun, $overwrite);
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

        $fromCatalog = $row !== null && $this->isCatalogRow($row);
        $path        = ($fromCatalog && ! $overwrite) || ! $dryRun ? $file() : null;

        if ($path === null && ! $dryRun) {
            $this->add($source, $target, 'failed', 'gambar katalog tidak dapat dibuka');

            return false;
        }

        // An earlier catalogue image is replaced only when the catalogue file changed
        // (the re-encode is deterministic, so an unchanged file gives the same sha256)
        if ($fromCatalog && ! $overwrite && $path !== null && hash_file('sha256', $path) === (string) $row['sha256']) {
            $this->add($source, $target, 'skipped', 'sudah dipasang dari katalog, gambarnya sama');

            return false;
        }

        if ($dryRun) {
            $this->add($source, $target, 'planned', $row === null ? 'slot baru' : $this->replaces($row));

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

    /**
     * Move the Cari Objek objects of a measured catalogue scene onto its
     * tables and shelves. Only objects still at their old bank position move
     * (or every object with $overwrite), so an admin's placement is kept.
     */
    private function placeObjects(string $file, bool $dryRun, bool $overwrite): void
    {
        $known = $this->sceneObjects[(string) hash_file('sha256', $file)] ?? null;

        if ($known === null) {
            return; // a scene not measured here: positions stay as they are
        }

        [$scene, $objects] = $known;

        foreach ($objects as $mediaKey => [$from, $to]) {
            $items = $this->db->table('challenge_items i')->select('i.id, i.item_key, i.config_json')
                ->join('media_assets m', 'm.id = i.media_asset_id')
                ->where('m.asset_key', $mediaKey)->where('i.interaction_type', 'find_object')
                ->get()->getResultArray();

            foreach ($items as $item) {
                $config = json_decode((string) $item['config_json'], true) ?: [];
                $label  = 'challenge_items ' . $item['item_key'];

                if ($this->samePosition($config, $to)) {
                    $this->add($scene, $label, 'skipped', 'benda sudah di atas meja/rak');

                    continue;
                }

                if (! $overwrite && ! $this->samePosition($config, $from)) {
                    $this->add($scene, $label, 'skipped', 'posisi sudah diatur admin (pakai --timpa untuk memindah)');

                    continue;
                }

                if (! $dryRun) {
                    [$config['x'], $config['y'], $config['w']] = $to;
                    $this->db->table('challenge_items')->where('id', $item['id'])
                        ->update(['config_json' => json_encode($config, JSON_UNESCAPED_UNICODE), 'updated_at' => date('Y-m-d H:i:s')]);
                }

                $this->add($scene, $label, $dryRun ? 'planned' : 'installed', sprintf('benda dipindah ke x %s %%, y %s %%, lebar %s %%', $to[0], $to[1], $to[2]));
            }
        }
    }

    /**
     * @param array<string, mixed> $config
     * @param list<float>          $position x, y, w
     */
    private function samePosition(array $config, array $position): bool
    {
        foreach (['x', 'y', 'w'] as $i => $axis) {
            if (! is_numeric($config[$axis] ?? null) || abs((float) $config[$axis] - $position[$i]) >= 0.01) {
                return false;
            }
        }

        return true;
    }

    /**
     * A catalogue key, or the slot it fills, matches one of the patterns.
     *
     * @param list<string> $patterns
     */
    private function matchesOwnKey(string $key): bool
    {
        foreach ($this->only as $pattern) {
            if (fnmatch($pattern, $key)) {
                return true;
            }
        }

        return false;
    }

    private function matches(string $key, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $slot = self::ICON_SLOTS[$key] ?? self::COPY_SLOTS[$key] ?? null;

            if (fnmatch($pattern, $key) || ($slot !== null && fnmatch($pattern, $slot))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Warn about character poses whose frames now come from two sources: the
     * catalogue and an upload that was kept (an admin upload without
     * `--timpa`). The loop would switch between two drawings, e.g. old
     * lurik frames among the red-scarf ones.
     *
     * @param array<string, array{key: string, file: string, cat: string}> $byKey
     * @param list<string>                                                 $only
     */
    private function checkPoses(array $byKey, array $only): void
    {
        $poses   = [];
        $planned = array_column(array_filter($this->report, static fn (array $r): bool => $r['status'] === 'planned'), 'target', 'target');

        foreach (array_keys($byKey) as $key) {
            if (preg_match('/^char\.([a-z]+\.[a-z]+)\.(\d+)$/', $key, $m) === 1 && ($only === [] || $this->matches($key, $only))) {
                $poses[$m[1]][] = $key;
            }
        }

        foreach ($poses as $pose => $keys) {
            $kept = [];
            $ours = 0;

            foreach ($keys as $key) {
                $row = model(MediaAssetModel::class)->findByKey($key);

                if (isset($planned[$key])) {
                    $ours++; // dry run: the catalogue frame would be installed

                    continue;
                }

                if ($row === null || (int) $row['is_active'] !== 1 || (int) ($row['is_placeholder'] ?? 0) === 1) {
                    continue;
                }

                if ($this->isCatalogRow($row)) {
                    $ours++;
                } else {
                    $kept[] = (int) substr($key, strrpos($key, '.') + 1);
                }
            }

            if ($kept !== [] && $ours > 0) {
                sort($kept);
                $this->add('char.' . $pose . '.*', 'char.' . $pose, 'warning', sprintf(
                    'gambar gerak %s bukan dari katalog, sisanya dari katalog: gerakannya bisa berganti gambar lain. Ganti dengan --timpa --kunci=char.%s.*',
                    implode(', ', $kept),
                    $pose,
                ));
            }
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

        if ($this->isCatalogRow($row)) {
            return null; // installSlot() compares it with the catalogue file
        }

        if (! str_starts_with((string) $row['storage_path'], MediaStore::uploadDir())) {
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
            $this->isCatalogRow($row)                   => 'gambar katalog berubah, dipasang ulang',
            ! str_starts_with((string) $row['storage_path'], MediaStore::uploadDir()) => 'mengganti berkas bawaan',
            default                                     => 'mengganti gambar unggahan admin',
        };
    }

    /** @param array<string, mixed> $row */
    private function isCatalogRow(array $row): bool
    {
        return (int) $row['is_active'] === 1 && (string) ($row['credit'] ?? '') === self::CREDIT;
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
