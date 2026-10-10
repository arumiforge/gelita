<?php

namespace App\Services;

use App\Models\AssetPackageModel;
use App\Models\AuditLogModel;

/**
 * Checks and clean-up for built asset packages (docs/rencana-paket-aset.md
 * §5.2, §5.5): `gelita:paket:periksa` and `gelita:paket:bersihkan`.
 */
class AssetPackageMaintenance
{
    /** Builds still `membangun` after this long are treated as abandoned. */
    public const ABANDONED_HOURS = 24;

    private AssetPackageService $packages;

    public function __construct(?AssetPackageService $packages = null)
    {
        $this->packages = $packages ?? service('assetPackages');
    }

    /**
     * Re-hash every file of a package against its manifest, and the manifest
     * against the sha256 recorded at build time.
     *
     * @return list<array{key: string, issue: string}> empty = package is intact
     */
    public function verify(string $code): array
    {
        $row = AssetPackageService::validCode($code) ? model(AssetPackageModel::class)->findByCode($code) : null;

        if ($row === null) {
            return [['key' => $code, 'issue' => 'paket tidak terdaftar']];
        }

        if ($row['status'] === AssetPackageModel::STATUS_BUILDING) {
            return [['key' => $code, 'issue' => 'paket belum selesai dibangun']];
        }

        $path = $this->packages->manifestPath($code);

        if (! is_file($path)) {
            return [['key' => AssetPackageService::manifestUrl($code), 'issue' => 'manifest tidak ada']];
        }

        $issues = [];
        $sha    = hash_file('sha256', $path);
        $map    = $this->packages->map($code);

        if ($sha !== (string) $row['manifest_sha256']) {
            $issues[] = ['key' => AssetPackageService::manifestUrl($code), 'issue' => 'sha256 manifest tidak sama dengan catatan asset_packages'];
        }

        if ($map === null) {
            $issues[] = ['key' => $code, 'issue' => 'peta URL (writable/paket) tidak ada'];
        } elseif ((string) $map['manifest_sha256'] !== $sha) {
            $issues[] = ['key' => $code, 'issue' => 'peta URL dibuat dari manifest lain'];
        }

        $manifest = $this->packages->manifest($code);

        if ($manifest === null || ! is_array($manifest['files'] ?? null)) {
            return array_merge($issues, [['key' => AssetPackageService::manifestUrl($code), 'issue' => 'manifest tidak dapat dibaca']]);
        }

        $total = 0;

        foreach ($manifest['files'] as $file) {
            $url  = (string) ($file['url'] ?? '');
            $key  = (string) ($file['key'] ?? $url);
            $full = $this->packages->publicDir() . $url;

            if (! self::isPackageUrl($url)) {
                $issues[] = ['key' => $key, 'issue' => "alamat di luar assets/p dan assets/k: {$url}"];

                continue;
            }

            if (! is_file($full)) {
                $issues[] = ['key' => $key, 'issue' => "berkas tidak ada: {$url}"];

                continue;
            }

            $total += (int) ($file['bytes'] ?? 0);

            if (filesize($full) !== (int) ($file['bytes'] ?? -1)) {
                $issues[] = ['key' => $key, 'issue' => "ukuran berbeda: {$url}"];
            } elseif (hash_file('sha256', $full) !== (string) ($file['sha256'] ?? '')) {
                $issues[] = ['key' => $key, 'issue' => "isi berkas berubah (sha256): {$url}"];
            }

            if (str_starts_with($url, AssetPackageService::MEDIA_DIR)
                && ! str_starts_with(basename($url), substr((string) ($file['sha256'] ?? ''), 0, 16))) {
                $issues[] = ['key' => $key, 'issue' => "nama berkas tidak sesuai isinya: {$url}"];
            }
        }

        if ($total !== (int) ($manifest['total_bytes'] ?? -1) && $issues === []) {
            $issues[] = ['key' => AssetPackageService::manifestUrl($code), 'issue' => 'total_bytes tidak sama dengan jumlah ukuran berkas'];
        }

        return $issues;
    }

    /**
     * Delete package files no kept package refers to, plus abandoned builds.
     * $remove: package codes to delete first (refused while a release names one).
     *
     * @param list<string> $remove
     *
     * @return array{removed_packages: list<string>, refused: list<string>, abandoned: list<string>,
     *               media_files: int, code_dirs: list<string>, bytes: int}
     */
    public function prune(array $remove = [], bool $dryRun = false): array
    {
        $model   = model(AssetPackageModel::class);
        $summary = ['removed_packages' => [], 'refused' => [], 'abandoned' => [], 'media_files' => 0, 'code_dirs' => [], 'bytes' => 0];
        $inUse   = $this->releaseCodes();

        foreach ($remove as $code) {
            $row = $model->findByCode($code);

            if ($row === null || isset($inUse[$code])) {
                $summary['refused'][] = $code;

                continue;
            }

            $summary['removed_packages'][] = $code;

            if (! $dryRun) {
                $this->deletePackage($row);
            }
        }

        $cutoff = date('Y-m-d H:i:s', time() - self::ABANDONED_HOURS * 3600);

        foreach ($model->where('status', AssetPackageModel::STATUS_BUILDING)->where('updated_at <', $cutoff)->findAll() as $row) {
            $summary['abandoned'][] = (string) $row['package_code'];

            if (! $dryRun) {
                $this->deletePackage($row);
            }
        }

        [$urls, $codeDirs] = $this->referenced(array_merge($summary['removed_packages'], $dryRun ? $summary['abandoned'] : []));

        $mediaDir = $this->packages->publicDir() . AssetPackageService::MEDIA_DIR;

        foreach (is_dir($mediaDir) ? (scandir($mediaDir) ?: []) : [] as $name) {
            $url = AssetPackageService::MEDIA_DIR . $name;

            if ($name === '.' || $name === '..' || $name === '.gitkeep' || ! is_file($mediaDir . $name) || isset($urls[$url])) {
                continue;
            }

            // Files copied in the last hour may belong to a build batch that
            // has not saved its state yet; they wait for the next run.
            if (filemtime($mediaDir . $name) > time() - 3600) {
                continue;
            }

            $summary['media_files']++;
            $summary['bytes'] += (int) filesize($mediaDir . $name);

            if (! $dryRun) {
                @unlink($mediaDir . $name);
            }
        }

        $codeRoot = $this->packages->publicDir() . AssetPackageService::CODE_DIR;

        foreach (is_dir($codeRoot) ? (scandir($codeRoot) ?: []) : [] as $name) {
            if ($name === '.' || $name === '..' || ! is_dir($codeRoot . $name) || isset($codeDirs[$name])) {
                continue;
            }

            $summary['code_dirs'][] = $name;
            $summary['bytes'] += $this->dirBytes($codeRoot . $name);

            if (! $dryRun) {
                $this->deleteDir($codeRoot . $name);
            }
        }

        if (! $dryRun && ($summary['removed_packages'] !== [] || $summary['media_files'] > 0 || $summary['code_dirs'] !== [] || $summary['abandoned'] !== [])) {
            model(AuditLogModel::class)->record('package_prune', [
                'target_type' => 'asset_package',
                'metadata'    => [
                    'removed'   => $summary['removed_packages'],
                    'abandoned' => $summary['abandoned'],
                    'files'     => $summary['media_files'],
                    'code_dirs' => count($summary['code_dirs']),
                    'bytes'     => $summary['bytes'],
                ],
            ]);
        }

        $this->packages->forget();

        return $summary;
    }

    public static function isPackageUrl(string $url): bool
    {
        return (str_starts_with($url, AssetPackageService::MEDIA_DIR) || str_starts_with($url, AssetPackageService::CODE_DIR))
            && ! str_contains($url, '..') && ! str_contains($url, '\\');
    }

    /**
     * URLs and code folders still referenced by kept packages and by builds
     * in progress.
     *
     * @param list<string> $ignore package codes being removed
     *
     * @return array{0: array<string, true>, 1: array<string, true>}
     */
    private function referenced(array $ignore): array
    {
        $urls = [];
        $dirs = [];

        foreach (model(AssetPackageModel::class)->findAll() as $row) {
            $code = (string) $row['package_code'];

            if (in_array($code, $ignore, true)) {
                continue;
            }

            if ((string) ($row['code_version'] ?? '') !== '') {
                $dirs[(string) $row['code_version']] = true;
            }

            foreach ($this->packages->manifest($code)['files'] ?? [] as $file) {
                $urls[(string) ($file['url'] ?? '')] = true;
            }

            $state = is_file($this->packages->statePath($code)) ? json_decode((string) file_get_contents($this->packages->statePath($code)), true) : null;

            foreach (is_array($state) ? $state['files'] ?? [] : [] as $file) {
                $urls[(string) ($file['url'] ?? '')] = true;
            }
        }

        return [$urls, $dirs];
    }

    /** @return array<string, true> package codes named by any release */
    private function releaseCodes(): array
    {
        $codes = [];

        foreach (db_connect()->table('game_releases')->select('asset_version')->get()->getResultArray() as $row) {
            $codes[trim((string) $row['asset_version'])] = true;
        }

        return $codes;
    }

    /** @param array<string, mixed> $row */
    private function deletePackage(array $row): void
    {
        $code = (string) $row['package_code'];

        if (AssetPackageService::validCode($code)) {
            @unlink($this->packages->manifestPath($code));
            @unlink($this->packages->mapPath($code));
            @unlink($this->packages->statePath($code));
            @file_put_contents($this->packages->tombstonePath($code), date(DATE_ATOM) . "\n");
        }

        model(AssetPackageModel::class)->delete((int) $row['id']);
    }

    private function dirBytes(string $dir): int
    {
        $bytes = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS)) as $file) {
            $bytes += $file->isFile() ? (int) $file->getSize() : 0;
        }

        return $bytes;
    }

    private function deleteDir(string $dir): void
    {
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($items as $item) {
            $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
        }

        @rmdir($dir);
    }
}
