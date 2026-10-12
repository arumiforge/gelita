<?php

namespace App\Services;

use App\Models\AssetPackageModel;
use App\Models\GameReleaseModel;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Runtime side of the asset package (docs/rencana-paket-aset.md §5.2, §6.3).
 *
 * Package mode is on when all of these hold:
 *  1. the server kill switch `Config\Gelita::$paket['aktif']` is true
 *     (`gelita.paket.aktif = false` in .env turns it off);
 *  2. the active release's `asset_version` is a valid package code;
 *  3. that package finished building, i.e. its URL map
 *     writable/paket/{package_code}.php exists.
 *
 * A release whose asset_version is still the old free text ('1') therefore
 * simply means "no package". The URL map is written by AssetPackageBuilder
 * at the end of a build and holds media id → package URL, asset_key →
 * package URL, and the source sha256 of every packaged media asset; reading
 * it costs one include per request (opcache keeps it in memory).
 *
 * Staff preview (§6.1 step 3, §9.2): `?paket={package_code}` on any game page
 * makes the URL helpers (urlForId(), urlForKey()) serve that package for the
 * logged-in staff member only, also while package mode is off. The choice is
 * kept in the session (`?paket=mati` ends it). activeCode(), and with it
 * `/api/paket/aktif` and the `X-Gelita-Paket` header, never reflect a preview,
 * so student devices are not affected.
 */
class AssetPackageService
{
    /** Content-addressed media, relative to public/ (§5.2). */
    public const MEDIA_DIR = 'assets/p/';

    /** Code copies per version, relative to public/: assets/k/{code_version}/ (P9). */
    public const CODE_DIR = 'assets/k/';

    /** Manifests, relative to public/: paket/{package_code}.json (§5.3). */
    public const MANIFEST_DIR = 'paket/';

    /** Allowed package code; it ends up in file paths, so nothing else passes. */
    public const CODE_PATTERN = '/^[a-z0-9][a-z0-9.-]{0,29}$/';

    /** Session key of the staff preview package (§6.1 step 3). */
    public const PREVIEW_SESSION_KEY = 'package_preview';

    /** `?paket=` values that end a staff preview. */
    public const PREVIEW_OFF = ['', '0', 'mati'];

    /** Panel settings next to the package switch, in the data folder (§8.7). */
    public const SETTINGS_FILE = 'pengaturan.json';

    private string $publicDir;

    private string $dataDir;

    /** @var array{code: string|null}|null memo for activeCode() */
    private ?array $active = null;

    /** @var array<string, array<string, mixed>|null> loaded URL maps per code */
    private array $maps = [];

    /** @var array{code: string|null}|null memo for previewCode() */
    private ?array $preview = null;

    public function __construct(?string $publicDir = null, ?string $dataDir = null)
    {
        $this->publicDir = rtrim($publicDir ?? FCPATH, '/\\') . '/';
        $this->dataDir   = rtrim($dataDir ?? WRITEPATH . 'paket', '/\\') . '/';
    }

    /** Server kill switch (`gelita.paket.aktif`). */
    public function enabled(): bool
    {
        return (bool) (config('Gelita')->paket['aktif'] ?? true);
    }

    /**
     * Package code the active release names, ignoring the kill switch, or
     * null when it names none (or a package that is not fully built).
     * Used by the build summary and later by the admin panel.
     */
    public function releasePackageCode(): ?string
    {
        if ($this->active !== null) {
            return $this->active['code'];
        }

        $code = null;

        try {
            $code = $this->packageOfRelease(model(GameReleaseModel::class)->activeOrNull());
        } catch (\Throwable) {
            // No release table yet (fresh install, partial test schema): no package.
        }

        $this->active = ['code' => $code];

        return $code;
    }

    /**
     * Package a release row names: its `asset_version` when that is a valid
     * code of a fully built package, else null. The old free text '1' and an
     * empty value both mean "no package" (package mode off).
     *
     * @param array<string, mixed>|null $release
     */
    public function packageOfRelease(?array $release): ?string
    {
        $value = trim((string) ($release['asset_version'] ?? ''));

        return $value !== '' && self::validCode($value) && is_file($this->mapPath($value)) ? $value : null;
    }

    /** Package code that students use right now, or null (package mode off). */
    public function activeCode(): ?string
    {
        return $this->enabled() ? $this->releasePackageCode() : null;
    }

    /**
     * Package URL (relative to base URL) of a media id in the package this
     * request is served from: the staff preview, else the active package.
     */
    public function urlForId(int $mediaAssetId): ?string
    {
        $code = $this->servingCode();

        return $code === null ? null : ($this->map($code)['ids'][$mediaAssetId] ?? null);
    }

    /** Package URL (relative to base URL) of an asset_key; see urlForId(). */
    public function urlForKey(string $assetKey): ?string
    {
        $code = $this->servingCode();

        return $code === null ? null : ($this->map($code)['keys'][$assetKey] ?? null);
    }

    /** Package the URL helpers use for this request: staff preview first. */
    public function servingCode(): ?string
    {
        return $this->previewCode() ?? $this->activeCode();
    }

    /**
     * Package a staff member previews in this request (§6.1 step 3, §9.2),
     * or null. Checked on the server against the staff session: anyone
     * without an active staff login who adds `?paket=` gets nothing. A valid
     * `?paket={code}` of a built package is stored in the session, so the
     * preview follows the staff member across game pages; `?paket=mati` ends
     * it. Works while package mode is off and regardless of the server kill
     * switch, since it only changes the URLs on this staff member's pages.
     * Never starts a session unless `?paket=` is in the URL.
     */
    public function previewCode(): ?string
    {
        if ($this->preview !== null) {
            return $this->preview['code'];
        }

        $code = null;

        try {
            $request = service('request');
            $asked   = $request instanceof IncomingRequest ? $request->getGet('paket') : null;

            // Only look at the session when the URL asks or a session already runs
            if ((is_string($asked) || session_status() === PHP_SESSION_ACTIVE || isset($_SESSION)) && $this->isStaff()) {
                $session = session();

                if (is_string($asked)) {
                    $asked = strtolower(trim($asked));

                    if (in_array($asked, self::PREVIEW_OFF, true)) {
                        $session->remove(self::PREVIEW_SESSION_KEY);
                    } elseif (self::validCode($asked) && is_file($this->mapPath($asked))) {
                        $session->set(self::PREVIEW_SESSION_KEY, $asked);
                    }
                }

                $stored = $session->get(self::PREVIEW_SESSION_KEY);
                $code   = is_string($stored) && self::validCode($stored) && is_file($this->mapPath($stored)) ? $stored : null;
            }
        } catch (\Throwable) {
            // No session or staff table (CLI, partial test schema): no preview.
            $code = null;
        }

        $this->preview = ['code' => $code];

        return $code;
    }

    /** A logged-in, active staff account (guru or admin) in this session. */
    private function isStaff(): bool
    {
        if ((int) session('staff_id') <= 0) {
            return false;
        }

        $staff = service('staffContext');

        return $staff !== null && (int) $staff->is_active === 1;
    }

    /**
     * URL map of one package, or null when it was never built.
     *
     * @return array{package_code: string, manifest_sha256: string, code_dir: string,
     *               ids: array<int, string>, keys: array<string, string>, sha: array<string, string|null>}|null
     */
    public function map(string $code): ?array
    {
        if (array_key_exists($code, $this->maps)) {
            return $this->maps[$code];
        }

        $path = self::validCode($code) ? $this->mapPath($code) : null;
        $map  = $path !== null && is_file($path) ? include $path : null;

        return $this->maps[$code] = is_array($map) ? $map : null;
    }

    /**
     * Images and audio that changed since the active package (§6.3): media
     * that would enter a new package now, compared by sha256 with what the
     * active package recorded. New, replaced, and removed assets all count.
     * One query plus one map lookup. Zero when no package is active.
     *
     * @return array{count: int, package_code: string|null, changed: list<string>, added: list<string>, removed: list<string>}
     */
    public function changedSinceActive(): array
    {
        $code   = $this->releasePackageCode();
        $result = ['count' => 0, 'package_code' => $code, 'changed' => [], 'added' => [], 'removed' => []];
        $map    = $code === null ? null : $this->map($code);

        if ($map === null) {
            return $result;
        }

        $packaged = $map['sha'];
        $current  = [];

        foreach (self::eligibleMedia() as $row) {
            $current[(string) $row['asset_key']] = $row['sha256'] === null ? null : (string) $row['sha256'];
        }

        foreach ($current as $key => $sha) {
            if (! array_key_exists($key, $packaged)) {
                $result['added'][] = $key;
            } elseif ($packaged[$key] !== $sha) {
                $result['changed'][] = $key;
            }
        }

        foreach (array_keys($packaged) as $key) {
            if (! array_key_exists($key, $current)) {
                $result['removed'][] = (string) $key;
            }
        }

        $result['count'] = count($result['changed']) + count($result['added']) + count($result['removed']);

        return $result;
    }

    /**
     * Media that belongs in a package: every active media asset, except
     * audio whose audio_assets row is not approved (draft narration never
     * reaches students, see audio_src()).
     *
     * @return list<array{id: int|string, asset_key: string, asset_type: string, storage_path: string,
     *                    mime_type: string, sha256: string|null, locale: string|null, is_placeholder: int|string}>
     */
    public static function eligibleMedia(): array
    {
        return db_connect()->table('media_assets ma')
            ->select('ma.id, ma.asset_key, ma.asset_type, ma.storage_path, ma.mime_type, ma.sha256, ma.locale, ma.is_placeholder')
            ->join('audio_assets aa', 'aa.media_asset_id = ma.id', 'left')
            ->where('ma.is_active', 1)
            // App icons are read by the browser outside the game (manifest, tab, home screen)
            ->notLike('ma.asset_key', 'app.', 'after')
            ->groupStart()
            ->where('aa.id', null)
            ->orWhere('aa.approval_status', 'approved')
            ->groupEnd()
            ->orderBy('ma.asset_key', 'ASC')
            ->get()
            ->getResultArray();
    }

    /** Forget per-request memos (after a build, a release switch, or in tests). */
    public function forget(): void
    {
        $this->active  = null;
        $this->maps    = [];
        $this->preview = null;
    }

    // ------------------------------------------------------------ panel (Stage 6)

    /**
     * "Izinkan mode daring untuk perangkat lab" (§8.7), off by default.
     *
     * Stored in writable/paket/pengaturan.json next to the URL maps: it only
     * means something together with a package, only `/api/paket/aktif` and
     * the panel read it, the backup scripts already keep that folder, and a
     * restore without the package folder falls back to the safe default
     * (off), just as package mode itself does. A missing or broken file
     * reads as off.
     */
    public function onlineLabAllowed(): bool
    {
        return ($this->settings()['online_lab_allowed'] ?? false) === true;
    }

    public function setOnlineLabAllowed(bool $allowed, ?int $staffId = null): void
    {
        $settings = ['online_lab_allowed' => $allowed, 'changed_at' => date(DATE_ATOM), 'changed_by' => $staffId] + $this->settings();
        $path     = $this->dataDir . self::SETTINGS_FILE;

        if (! is_dir($this->dataDir) && ! @mkdir($this->dataDir, 0775, true) && ! is_dir($this->dataDir)) {
            throw new \RuntimeException("Folder {$this->dataDir} could not be created.");
        }

        $tmp  = $path . '.tmp-' . bin2hex(random_bytes(4));
        $json = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

        if (file_put_contents($tmp, $json, LOCK_EX) !== strlen($json) || ! @rename($tmp, $path)) {
            @unlink($tmp);

            throw new \RuntimeException("{$path} could not be written.");
        }
    }

    /** @return array<string, mixed> */
    private function settings(): array
    {
        $path = $this->dataDir . self::SETTINGS_FILE;
        $data = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;

        return is_array($data) ? $data : [];
    }

    /**
     * Build summary of a finished package for the panel (§6.1 step 2): size
     * per group, the files and bytes a device that holds the release's
     * current package still has to download, and the build warnings.
     *
     * @return array{package_code: string, status: string, total_bytes: int, file_count: int, built_at: string|null,
     *               published_at: string|null, notes: string|null, groups: array<string, int>,
     *               warnings: list<array{code: string, key: string, detail: string|null}>,
     *               compared_with: string|null, new_files: int, new_bytes: int}|null
     */
    public function summary(string $code): ?array
    {
        $row = self::validCode($code) ? model(AssetPackageModel::class)->findByCode($code) : null;

        if ($row === null || $row['status'] === AssetPackageModel::STATUS_BUILDING) {
            return null;
        }

        $current  = $this->releasePackageCode();
        $old      = $current === null || $current === $code ? null : $this->manifest($current);
        $have     = $old === null ? [] : array_flip(array_column($old['files'] ?? [], 'url'));
        $newFiles = 0;
        $newBytes = 0;

        if ($current !== $code) {
            foreach ($this->manifest($code)['files'] ?? [] as $file) {
                if (! isset($have[$file['url'] ?? ''])) {
                    $newFiles++;
                    $newBytes += (int) ($file['bytes'] ?? 0);
                }
            }
        }

        return [
            'package_code'  => $code,
            'status'        => (string) $row['status'],
            'total_bytes'   => (int) $row['total_bytes'],
            'file_count'    => (int) $row['file_count'],
            'built_at'      => $row['built_at'],
            'published_at'  => $row['published_at'],
            'notes'         => $row['notes'],
            'groups'        => array_map('intval', (array) json_decode((string) ($row['groups_json'] ?? '[]'), true)),
            'warnings'      => array_values((array) json_decode((string) ($row['warnings_json'] ?? '[]'), true)),
            'compared_with' => $old === null ? null : $current,
            'new_files'     => $newFiles,
            'new_bytes'     => $newBytes,
        ];
    }

    /**
     * Result of the `gelita:paket:periksa` check (§5.5) for one package, for
     * the research-ready checklist. Re-hashing a full package takes a while,
     * so the result is cached per package and manifest for an hour; $fresh
     * (the panel's Periksa button) checks again now.
     *
     * @return array{ok: bool, issues: list<array{key: string, issue: string}>, checked_at: string}
     */
    public function verification(string $code, bool $fresh = false): array
    {
        $map   = $this->map($code);
        $key   = 'gelita_paket_periksa_' . preg_replace('/[^a-z0-9]/', '_', $code) . '_' . substr((string) ($map['manifest_sha256'] ?? 'none'), 0, 16);
        $cache = service('cache');

        if (! $fresh) {
            $hit = $cache->get($key);

            if (is_array($hit) && isset($hit['ok'], $hit['checked_at'])) {
                return $hit;
            }
        }

        $issues = (new AssetPackageMaintenance($this))->verify($code);
        $result = ['ok' => $issues === [], 'issues' => $issues, 'checked_at' => date('Y-m-d H:i:s')];
        $cache->save($key, $result, 3600);

        return $result;
    }

    /**
     * Newest build still in progress (status `membangun` with its state file,
     * not abandoned), or null. The panel resumes driving it instead of
     * starting a second build.
     *
     * @return array<string, mixed>|null asset_packages row
     */
    public function runningBuild(): ?array
    {
        $cutoff = date('Y-m-d H:i:s', time() - AssetPackageMaintenance::ABANDONED_HOURS * 3600);
        $row    = model(AssetPackageModel::class)
            ->where('status', AssetPackageModel::STATUS_BUILDING)
            ->where('created_at >=', $cutoff)
            ->orderBy('id', 'DESC')
            ->first();

        return $row !== null && is_file($this->statePath((string) $row['package_code'])) ? $row : null;
    }

    public static function validCode(string $code): bool
    {
        return preg_match(self::CODE_PATTERN, $code) === 1;
    }

    public function publicDir(): string
    {
        return $this->publicDir;
    }

    public function dataDir(): string
    {
        return $this->dataDir;
    }

    public function mapPath(string $code): string
    {
        return $this->dataDir . $code . '.php';
    }

    /** Marker left by gelita:paket:bersihkan so a deleted code is never reused. */
    public function tombstonePath(string $code): string
    {
        return $this->dataDir . $code . '.deleted';
    }

    public function statePath(string $code): string
    {
        return $this->dataDir . $code . '.build.json';
    }

    /** Manifest path relative to public/ (and to the base URL). */
    public static function manifestUrl(string $code): string
    {
        return self::MANIFEST_DIR . $code . '.json';
    }

    public function manifestPath(string $code): string
    {
        return $this->publicDir . self::manifestUrl($code);
    }

    /**
     * Decoded manifest of a package, or null when the file is missing or broken.
     *
     * @return array<string, mixed>|null
     */
    public function manifest(string $code): ?array
    {
        if (! self::validCode($code) || ! is_file($this->manifestPath($code))) {
            return null;
        }

        $data = json_decode((string) file_get_contents($this->manifestPath($code)), true);

        return is_array($data) ? $data : null;
    }
}
