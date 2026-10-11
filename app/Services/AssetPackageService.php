<?php

namespace App\Services;

use App\Models\GameReleaseModel;

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

    private string $publicDir;

    private string $dataDir;

    /** @var array{code: string|null}|null memo for activeCode() */
    private ?array $active = null;

    /** @var array<string, array<string, mixed>|null> loaded URL maps per code */
    private array $maps = [];

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
            $release = model(GameReleaseModel::class)->activeOrNull();
            $value   = trim((string) ($release['asset_version'] ?? ''));

            if ($value !== '' && self::validCode($value) && is_file($this->mapPath($value))) {
                $code = $value;
            }
        } catch (\Throwable) {
            // No release table yet (fresh install, partial test schema): no package.
        }

        $this->active = ['code' => $code];

        return $code;
    }

    /** Package code that students use right now, or null (package mode off). */
    public function activeCode(): ?string
    {
        return $this->enabled() ? $this->releasePackageCode() : null;
    }

    /** Package URL (relative to base URL) of a media id in the active package. */
    public function urlForId(int $mediaAssetId): ?string
    {
        $code = $this->activeCode();

        return $code === null ? null : ($this->map($code)['ids'][$mediaAssetId] ?? null);
    }

    /** Package URL (relative to base URL) of an asset_key in the active package. */
    public function urlForKey(string $assetKey): ?string
    {
        $code = $this->activeCode();

        return $code === null ? null : ($this->map($code)['keys'][$assetKey] ?? null);
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
        $this->active = null;
        $this->maps   = [];
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
