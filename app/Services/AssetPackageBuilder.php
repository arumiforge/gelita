<?php

namespace App\Services;

use App\Libraries\CommonsImage;
use App\Models\AssetPackageModel;
use App\Models\AuditLogModel;
use App\Models\GameReleaseModel;
use App\Models\LibraryMediaModel;
use RuntimeException;

/**
 * Builds asset packages (docs/rencana-paket-aset.md §5, §6.1 step 2, §6.3).
 *
 * The build runs in batches so the admin panel (Stage 6) can drive it with
 * one request per batch and a progress bar; `gelita:paket:bangun` runs the
 * same steps in one go through build():
 *
 *   start()   new `asset_packages` row (status `membangun`), plan saved in
 *             writable/paket/{code}.build.json
 *   step()    process the next batch of tasks: first the Commons copies of
 *             library images (P4a), then every packaged file (hash + copy)
 *   finish()  write the manifest public/paket/{code}.json and the URL map
 *             writable/paket/{code}.php, compare with the release's package,
 *             mark the row `dibangun`
 *
 * Files:
 * - media (images, audio) → public/assets/p/{sha256[0:16]}.{ext}; the same
 *   content always gets the same URL, so a new package only adds changed files;
 * - code (game CSS/JS, Howler, fonts, offline.html) → public/assets/k/{code_version}/,
 *   keeping the folder layout so relative ES-module imports and the CSS font
 *   URLs still work (P9). code_version = "{app_version}-{hash of all code
 *   files[0:8]}", so a folder is never rewritten with different content.
 *
 * Building never changes what students see: only a release whose
 * asset_version names the package does (AssetPackageService).
 */
class AssetPackageBuilder
{
    public const FORMAT = 1;

    /** Download order and progress groups (§5.1); regions are inserted between inti and penutup. */
    public const GROUP_CODE = 'kode';
    public const GROUP_CORE = 'inti';
    public const GROUP_END  = 'penutup';

    /**
     * Game code that goes into the `kode` group, relative to public/.
     * Admin code and app icons stay out (§5.1).
     */
    public const CODE_SOURCES = [
        'assets/css'    => ['ext' => ['css'], 'exclude' => ['admin.css']],
        'assets/js'     => ['ext' => ['js'], 'exclude' => ['admin.js', 'admin/']],
        'assets/vendor' => ['ext' => ['js'], 'exclude' => []],
        'assets/fonts'  => ['ext' => ['woff2'], 'exclude' => []],
    ];

    /** Single code files outside assets/ (key = path inside the code folder). */
    public const CODE_FILES = ['offline.html'];

    /** Narration codes per group (`audio.narasi.{locale}.{code}`). */
    private const CORE_NARRATION = ['intro', 'peta'];

    private const REGION_NARRATION = ['kenal', 'dialog', 'tuntas'];

    private const CODE_TYPES = [
        'css'   => 'text/css',
        'js'    => 'text/javascript',
        'woff2' => 'font/woff2',
        'html'  => 'text/html',
    ];

    private AssetPackageService $packages;

    private CommonsImage $commons;

    public function __construct(?AssetPackageService $packages = null, ?CommonsImage $commons = null)
    {
        $this->packages = $packages ?? service('assetPackages');
        $this->commons  = $commons ?? new CommonsImage();
    }

    // ------------------------------------------------------------ public API

    /**
     * Plan a new package. Nothing is copied yet.
     *
     * @param array{staff_id?: int|null, notes?: string|null, app_version?: string|null, copy_library?: bool|null} $options
     *
     * @return array{package_code: string, done: int, total: int, finished: bool}
     */
    public function start(array $options = []): array
    {
        $this->ensureDir($this->packages->dataDir());

        $release    = $this->activeRelease();
        $appVersion = self::versionSlug((string) ($options['app_version'] ?? $release['app_version'] ?? '0')) ?: '0';
        $codeTasks  = $this->codeTasks();
        $codeHash   = hash('sha256', implode("\n", array_map(static fn (array $t): string => $t['key'] . ':' . $t['sha'], $codeTasks)));
        $copy       = $options['copy_library'] ?? (bool) (config('Gelita')->paket['copyLibraryImages'] ?? true);
        $commons    = $copy ? array_map(static fn ($m): array => ['kind' => 'commons', 'library_media_id' => $m->id], $this->commons->pending()) : [];
        $model      = model(AssetPackageModel::class);
        $code       = $model->nextCode(null, fn (string $code): bool => is_file($this->packages->tombstonePath($code)));

        $state = [
            'package_code'    => $code,
            'release_code'    => $release['release_code'] ?? null,
            'app_version'     => $appVersion,
            'code_version'    => substr($appVersion, 0, 50) . '-' . substr($codeHash, 0, 8),
            'content_version' => $release['content_version'] ?? null,
            'staff_id'        => $options['staff_id'] ?? null,
            'created_at'      => date(DATE_ATOM),
            'commons'         => $commons,
            'code'            => $codeTasks,
            'tasks'           => null,  // filled once the Commons copies are done
            'estimate'        => count($commons) + count(AssetPackageService::eligibleMedia()) + count($codeTasks),
            'cursor'          => 0,
            'files'           => [],
            'sha'             => [],
            'warnings'        => [],
        ];

        $id = $model->insert([
            'package_code'    => $code,
            'app_version'     => mb_substr($appVersion, 0, 30),
            'code_version'    => $state['code_version'],
            'content_version' => $state['content_version'],
            'status'          => AssetPackageModel::STATUS_BUILDING,
            'built_by'        => $state['staff_id'],
            'notes'           => isset($options['notes']) && trim((string) $options['notes']) !== '' ? trim((string) $options['notes']) : null,
        ], true);

        if ($id === false) {
            throw new RuntimeException('Package row could not be created: ' . implode(' ', $model->errors()));
        }

        $this->saveState($state);

        return $this->progressOf($state);
    }

    /**
     * Process the next batch of a package that is being built.
     *
     * @return array{package_code: string, done: int, total: int, finished: bool}
     */
    public function step(string $code, ?int $limit = null): array
    {
        $limit = max(1, $limit ?? (int) (config('Gelita')->paket['batchSize'] ?? 25));

        return $this->withState($code, function (array &$state) use ($limit): void {
            for ($n = 0; $n < $limit; $n++) {
                $commonsCount = count($state['commons']);

                if ($state['cursor'] < $commonsCount) {
                    $this->runCommons($state, $state['commons'][$state['cursor']]);
                    $state['cursor']++;

                    continue;
                }

                if ($state['tasks'] === null) {
                    $state['tasks'] = $this->collect($state);
                }

                $index = $state['cursor'] - $commonsCount;

                if ($index >= count($state['tasks'])) {
                    return;
                }

                $this->runTask($state, $state['tasks'][$index]);
                $state['cursor']++;
            }

            // Collect right away when the last Commons copy closed the batch,
            // so progress() reports the real total.
            if ($state['tasks'] === null && $state['cursor'] >= count($state['commons'])) {
                $state['tasks'] = $this->collect($state);
            }
        });
    }

    /** @return array{package_code: string, done: int, total: int, finished: bool} */
    public function progress(string $code): array
    {
        return $this->progressOf($this->loadState($code));
    }

    /**
     * Write the manifest and URL map and mark the package built.
     *
     * @return array<string, mixed> summary(), see there
     */
    public function finish(string $code): array
    {
        $state = $this->loadState($code);

        if (! $this->progressOf($state)['finished']) {
            throw new RuntimeException("Package {$code} still has files to process; call step() first.");
        }

        // Several keys with the same content share one file entry (aliases).
        $byUrl = [];

        foreach ($state['files'] as $file) {
            if (isset($byUrl[$file['url']])) {
                $byUrl[$file['url']]['aliases'][] = $file['key'];
                $byUrl[$file['url']]['media_ids'] = array_merge($byUrl[$file['url']]['media_ids'], $file['media_ids']);

                continue;
            }

            $byUrl[$file['url']] = $file;
        }

        $order = array_flip(self::groupOrder());
        $files = array_values($byUrl);

        usort($files, static fn (array $a, array $b): int => [$order[$a['group']] ?? 99, $a['key']] <=> [$order[$b['group']] ?? 99, $b['key']]);

        $groups = array_fill_keys(self::groupOrder(), 0);
        $total  = 0;
        $ids    = [];
        $keys   = [];

        foreach ($files as $i => $file) {
            $groups[$file['group']] = ($groups[$file['group']] ?? 0) + $file['bytes'];
            $total += $file['bytes'];

            foreach (array_merge([$file['key']], $file['aliases'] ?? []) as $key) {
                $keys[$key] = $file['url'];
            }

            foreach ($file['media_ids'] as $mediaId) {
                $ids[$mediaId] = $file['url'];
            }

            $files[$i] = self::manifestEntry($file);
        }

        $manifest = [
            'format'          => self::FORMAT,
            'package_code'    => $state['package_code'],
            'release_code'    => $state['release_code'],
            'app_version'     => $state['app_version'],
            'code_version'    => $state['code_version'],
            'code_dir'        => AssetPackageService::CODE_DIR . $state['code_version'] . '/',
            'content_version' => $state['content_version'],
            'created_at'      => date(DATE_ATOM),
            'total_bytes'     => $total,
            'file_count'      => count($files),
            'groups'          => $groups,
            'files'           => $files,
        ];

        $json         = json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
        $manifestPath = $this->packages->manifestPath($code);
        $this->ensureDir(dirname($manifestPath));
        $this->writeAtomic($manifestPath, $json);
        $manifestSha = hash('sha256', $json);

        $map = [
            'package_code'    => $code,
            'manifest_sha256' => $manifestSha,
            'code_dir'        => $manifest['code_dir'],
            'ids'             => $ids,
            'keys'            => $keys,
            'sha'             => $state['sha'],
        ];
        $this->writeAtomic($this->packages->mapPath($code), "<?php\n\n// Generated by gelita:paket:bangun; do not edit.\nreturn " . var_export($map, true) . ";\n");

        if (function_exists('opcache_invalidate')) {
            @opcache_invalidate($this->packages->mapPath($code), true);
        }

        $this->packages->forget();
        $diff     = $this->diffWith($this->packages->releasePackageCode(), $files);
        $warnings = $this->finalWarnings($state);

        model(AssetPackageModel::class)->where('package_code', $code)->set([
            'manifest_sha256' => $manifestSha,
            'total_bytes'     => $total,
            'file_count'      => count($files),
            'groups_json'     => json_encode($groups),
            'warnings_json'   => json_encode($warnings, JSON_UNESCAPED_UNICODE),
            'status'          => AssetPackageModel::STATUS_BUILT,
            'built_at'        => date('Y-m-d H:i:s'),
        ])->update();

        @unlink($this->packages->statePath($code));
        $this->packages->forget();

        model(AuditLogModel::class)->record('package_build', [
            'staff_user_id' => $state['staff_id'],
            'target_type'   => 'asset_package',
            'target_id'     => $code,
            'metadata'      => ['files' => count($files), 'bytes' => $total, 'new_files' => $diff['new_files'], 'warnings' => count($warnings)],
        ]);

        return [
            'package_code'    => $code,
            'manifest_url'    => AssetPackageService::manifestUrl($code),
            'manifest_sha256' => $manifestSha,
            'total_bytes'     => $total,
            'file_count'      => count($files),
            'groups'          => $groups,
            'compared_with'   => $diff['compared_with'],
            'new_files'       => $diff['new_files'],
            'new_bytes'       => $diff['new_bytes'],
            'warnings'        => $warnings,
        ];
    }

    /**
     * Whole build in one call (CLI). $onProgress receives progress() after
     * every batch.
     *
     * @param array{staff_id?: int|null, notes?: string|null, app_version?: string|null, copy_library?: bool|null} $options
     *
     * @return array<string, mixed> finish() summary
     */
    public function build(array $options = [], ?callable $onProgress = null): array
    {
        $progress = $this->start($options);

        while (! $progress['finished']) {
            $progress = $this->step($progress['package_code']);

            if ($onProgress !== null) {
                $onProgress($progress);
            }
        }

        return $this->finish($progress['package_code']);
    }

    /**
     * Group names in download order: kode, inti, wilayah-{region}…, penutup.
     *
     * @return list<string>
     */
    public static function groupOrder(): array
    {
        $regions = array_map(static fn (string $level): string => 'wilayah-' . $level, array_values(config('Gelita')->levelPrefixes));

        return array_merge([self::GROUP_CODE, self::GROUP_CORE], $regions, [self::GROUP_END]);
    }

    /**
     * Group of one media asset (§5.1). $levelHint is the region the asset is
     * linked to in the database (levels, nodes, items, library, dialogues).
     */
    public static function groupFor(string $assetKey, ?string $levelHint = null): string
    {
        $key      = strtolower($assetKey);
        $prefixes = config('Gelita')->levelPrefixes;
        $regions  = array_values($prefixes);
        $region   = static fn (string $level): string => 'wilayah-' . $level;

        // Shared by every screen
        if (preg_match('/^(char\.|ui\.|audio\.sfx\.)/', $key) || in_array($key, ['map.kedu', 'audio.music.map'], true)
            || in_array($key, ['bg.welcome', 'bg.auth', 'bg.intro', 'bg.map', 'bg.reflection', 'bg.loading'], true)) {
            return self::GROUP_CORE;
        }

        if (preg_match('/^audio\.narasi\.[a-z]{2}\.([a-z]+)-(.+)$/', $key, $m)) {
            if (in_array($m[1], self::CORE_NARRATION, true)) {
                return self::GROUP_CORE;
            }

            if ($m[1] === 'penutup') {
                return self::GROUP_END;
            }

            foreach ($regions as $level) {
                if (in_array($m[1], self::REGION_NARRATION, true) && str_starts_with($m[2], $level)) {
                    return $region($level);
                }
            }

            if ($m[1] === 'petunjuk' && preg_match('/^([a-z]+)-\d/', $m[2], $p) && isset($prefixes[$p[1]])) {
                return $region($prefixes[$p[1]]);
            }
        }

        if (str_starts_with($key, 'audio.music.')) {
            return self::GROUP_END;
        }

        foreach ($regions as $level) {
            if (in_array($key, ["bg.{$level}.region", "map.region.{$level}", "reward.badge.{$level}", "map.pin.{$level}", "map.lantern.{$level}", "bg.loading.{$level}"], true)) {
                return $region($level);
            }
        }

        // Challenge slots carry the node ref: challenge.tmg-1.scene, challenge.item.mgl-4-a01
        if (preg_match('/(?:^|[.])(' . implode('|', array_map('preg_quote', array_keys($prefixes))) . ')-\d/', $key, $m)) {
            return $region($prefixes[$m[1]]);
        }

        if ($levelHint !== null && in_array($levelHint, $regions, true)) {
            return $region($levelHint);
        }

        return self::GROUP_CORE;
    }

    // ------------------------------------------------------------ tasks

    /**
     * Every code file with its sha256 at planning time.
     *
     * @return list<array{kind: string, key: string, source: string, sha: string}>
     */
    private function codeTasks(): array
    {
        $public = $this->packages->publicDir();
        $tasks  = [];

        foreach (self::CODE_SOURCES as $dir => $rule) {
            if (! is_dir($public . $dir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($public . $dir, \FilesystemIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($public . $dir) + 1));

                if (! $file->isFile() || ! in_array(strtolower($file->getExtension()), $rule['ext'], true)) {
                    continue;
                }

                foreach ($rule['exclude'] as $exclude) {
                    if ($relative === $exclude || (str_ends_with($exclude, '/') && str_starts_with($relative, $exclude))) {
                        continue 2;
                    }
                }

                $source  = $dir . '/' . $relative;
                $tasks[] = ['kind' => 'code', 'key' => substr($source, strlen('assets/')), 'source' => $source, 'sha' => hash_file('sha256', $file->getPathname())];
            }
        }

        foreach (self::CODE_FILES as $path) {
            if (is_file($public . $path)) {
                $tasks[] = ['kind' => 'code', 'key' => $path, 'source' => $path, 'sha' => hash_file('sha256', $public . $path)];
            }
        }

        usort($tasks, static fn (array $a, array $b): int => strcmp($a['key'], $b['key']));

        return $tasks;
    }

    /**
     * Media and code tasks, collected after the Commons copies so the copies
     * are packaged too. Also records the source sha256 per asset_key that
     * AssetPackageService::changedSinceActive() compares against.
     *
     * @param array<string, mixed> $state
     *
     * @return list<array<string, mixed>>
     */
    private function collect(array &$state): array
    {
        $hints = $this->levelHints();
        $tasks = [];

        foreach (AssetPackageService::eligibleMedia() as $row) {
            $key                = (string) $row['asset_key'];
            $state['sha'][$key] = $row['sha256'] === null ? null : (string) $row['sha256'];

            $tasks[] = [
                'kind'        => 'media',
                'media_id'    => (int) $row['id'],
                'key'         => $key,
                'source'      => ltrim(str_replace('\\', '/', (string) $row['storage_path']), '/'),
                'type'        => (string) $row['mime_type'],
                'locale'      => $row['locale'] === null || $row['locale'] === '' ? null : (string) $row['locale'],
                'group'       => self::groupFor($key, $hints[(int) $row['id']] ?? null),
                'sha'         => $row['sha256'] === null ? null : (string) $row['sha256'],
                'placeholder' => (int) $row['is_placeholder'] === 1,
            ];
        }

        return array_merge($tasks, $state['code']);
    }

    /** @param array<string, mixed> $state */
    private function runCommons(array &$state, array $task): void
    {
        $media = model(LibraryMediaModel::class)->find((int) $task['library_media_id']);

        if ($media === null || $media->media_asset_id !== null) {
            return;
        }

        $result = $this->commons->copy($media, $state['staff_id']);

        if ($result['error'] !== null) {
            $state['warnings'][] = ['code' => 'library_copy_failed', 'key' => 'library_media#' . $media->id, 'detail' => $result['error']];
        }
    }

    /**
     * Hash and copy one file, append its manifest entry.
     *
     * @param array<string, mixed> $state
     * @param array<string, mixed> $task
     */
    private function runTask(array &$state, array $task): void
    {
        $public = $this->packages->publicDir();
        $source = $public . $task['source'];

        if (! is_file($source)) {
            $state['warnings'][] = ['code' => 'file_missing', 'key' => $task['key'], 'detail' => $task['source']];

            return;
        }

        $sha   = hash_file('sha256', $source);
        $bytes = (int) filesize($source);

        if ($task['kind'] === 'code') {
            if ($sha !== $task['sha']) {
                throw new RuntimeException("Code file {$task['source']} changed during the build; start a new build.");
            }

            $url = AssetPackageService::CODE_DIR . $state['code_version'] . '/' . $task['key'];
            $this->copyInto($source, $public . $url, $sha);

            $state['files'][] = [
                'url' => $url, 'sha256' => $sha, 'bytes' => $bytes,
                'type' => self::CODE_TYPES[strtolower(pathinfo($source, PATHINFO_EXTENSION))] ?? 'application/octet-stream',
                'group' => self::GROUP_CODE, 'key' => $task['key'], 'locale' => null, 'media_ids' => [],
            ];

            return;
        }

        if ($task['sha'] !== null && $task['sha'] !== $sha) {
            $state['warnings'][] = ['code' => 'hash_mismatch', 'key' => $task['key'], 'detail' => 'isi berkas tidak sama dengan sha256 di media_assets'];
        }

        if ($task['placeholder']) {
            $state['warnings'][] = ['code' => 'placeholder', 'key' => $task['key'], 'detail' => null];
        }

        $budget = self::budgetFor($task['key']);

        if ($budget !== null && $bytes > $budget) {
            $state['warnings'][] = ['code' => 'over_budget', 'key' => $task['key'], 'detail' => sprintf('%d > %d byte', $bytes, $budget)];
        }

        $extension = strtolower((string) pathinfo($task['source'], PATHINFO_EXTENSION));
        $extension = preg_match('/^[a-z0-9]{1,5}$/', $extension) ? $extension : 'bin';
        $url       = AssetPackageService::MEDIA_DIR . substr($sha, 0, 16) . '.' . $extension;
        $this->copyInto($source, $public . $url, $sha);

        $state['files'][] = [
            'url' => $url, 'sha256' => $sha, 'bytes' => $bytes, 'type' => $task['type'],
            'group' => $task['group'], 'key' => $task['key'], 'locale' => $task['locale'], 'media_ids' => [$task['media_id']],
        ];
    }

    /**
     * Copy into the content-addressed target unless an identical file is
     * already there. A different file under the same name is a broken
     * earlier copy and is replaced; code folders are protected by the sha
     * check in runTask() instead.
     */
    private function copyInto(string $source, string $target, string $sha): void
    {
        if (is_file($target) && hash_file('sha256', $target) === $sha) {
            return;
        }

        $this->ensureDir(dirname($target));
        $tmp = $target . '.tmp-' . bin2hex(random_bytes(4));

        if (! @copy($source, $tmp) || hash_file('sha256', $tmp) !== $sha || ! @rename($tmp, $target)) {
            @unlink($tmp);

            throw new RuntimeException("File could not be copied to {$target}.");
        }
    }

    // ------------------------------------------------------------ helpers

    /**
     * media id → region code from the database links (first link wins).
     *
     * @return array<int, string>
     */
    private function levelHints(): array
    {
        $db     = db_connect();
        $levels = [];
        $hints  = [];

        foreach ($db->table('levels')->select('id, code, map_media_id, background_media_id, badge_media_id')->get()->getResultArray() as $level) {
            $levels[(int) $level['id']] = (string) $level['code'];

            foreach (['map_media_id', 'background_media_id', 'badge_media_id'] as $column) {
                if ($level[$column] !== null) {
                    $hints[(int) $level[$column]] ??= (string) $level['code'];
                }
            }
        }

        $add = static function (array $rows, string $levelColumn, array $mediaColumns) use (&$hints, $levels): void {
            foreach ($rows as $row) {
                $level = $levels[(int) ($row[$levelColumn] ?? 0)] ?? null;

                if ($level === null) {
                    continue;
                }

                foreach ($mediaColumns as $column) {
                    if (($row[$column] ?? null) !== null) {
                        $hints[(int) $row[$column]] ??= $level;
                    }
                }
            }
        };

        $tables = [
            ['challenge_nodes', 'SELECT level_id, background_media_id, scene_media_id FROM ' . $db->prefixTable('challenge_nodes'), ['background_media_id', 'scene_media_id']],
            ['challenge_items', 'SELECT n.level_id, i.media_asset_id FROM ' . $db->prefixTable('challenge_items') . ' i JOIN ' . $db->prefixTable('challenge_nodes') . ' n ON n.id = i.challenge_node_id', ['media_asset_id']],
            ['library_media', 'SELECT p.level_id, m.media_asset_id, m.poster_media_id FROM ' . $db->prefixTable('library_media') . ' m JOIN ' . $db->prefixTable('library_pages') . ' p ON p.id = m.library_page_id', ['media_asset_id', 'poster_media_id']],
            ['reading_passages', 'SELECT level_id, media_asset_id FROM ' . $db->prefixTable('reading_passages'), ['media_asset_id']],
            ['dialogues', 'SELECT d.level_id, d.background_media_id, a1.media_asset_id AS audio_id, a2.media_asset_id AS audio_en FROM ' . $db->prefixTable('dialogues') . ' d LEFT JOIN '
                . $db->prefixTable('audio_assets') . ' a1 ON a1.id = d.audio_id_asset_id LEFT JOIN ' . $db->prefixTable('audio_assets') . ' a2 ON a2.id = d.audio_en_asset_id', ['background_media_id', 'audio_id', 'audio_en']],
        ];

        foreach ($tables as [$table, $sql, $columns]) {
            if ($db->tableExists($table)) {
                $add($db->query($sql)->getResultArray(), 'level_id', $columns);
            }
        }

        return $hints;
    }

    /** Size budget for an asset_key (Config\Gelita::$paket['budgets']), or null. */
    public static function budgetFor(string $assetKey): ?int
    {
        foreach (config('Gelita')->paket['budgets'] ?? [] as $pattern => $bytes) {
            if ($pattern === $assetKey || fnmatch((string) $pattern, $assetKey)) {
                return (int) $bytes;
            }
        }

        return null;
    }

    /**
     * Files of the new package that devices with the release's current
     * package do not have yet (§6.1: "berkas baru/berubah").
     *
     * @param list<array<string, mixed>> $files
     *
     * @return array{compared_with: string|null, new_files: int, new_bytes: int}
     */
    private function diffWith(?string $previous, array $files): array
    {
        $old  = $previous === null ? null : $this->packages->manifest($previous);
        $have = $old === null ? [] : array_flip(array_column($old['files'] ?? [], 'url'));
        $new  = array_filter($files, static fn (array $file): bool => ! isset($have[$file['url']]));

        return [
            'compared_with' => $old === null ? null : $previous,
            'new_files'     => count($new),
            'new_bytes'     => array_sum(array_column($new, 'bytes')),
        ];
    }

    /**
     * Build warnings plus draft narration that was left out.
     *
     * @param array<string, mixed> $state
     *
     * @return list<array{code: string, key: string, detail: string|null}>
     */
    private function finalWarnings(array $state): array
    {
        $warnings = $state['warnings'];
        $drafts   = db_connect()->table('audio_assets aa')
            ->select('ma.asset_key, aa.approval_status')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id')
            ->where('ma.is_active', 1)
            ->where('aa.approval_status !=', 'approved')
            ->orderBy('ma.asset_key', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($drafts as $row) {
            $warnings[] = ['code' => 'narration_not_approved', 'key' => (string) $row['asset_key'], 'detail' => (string) $row['approval_status']];
        }

        return $warnings;
    }

    /** @param array<string, mixed> $file */
    private static function manifestEntry(array $file): array
    {
        $entry = [
            'url'    => $file['url'],
            'sha256' => $file['sha256'],
            'bytes'  => $file['bytes'],
            'type'   => $file['type'],
            'group'  => $file['group'],
            'key'    => $file['key'],
            'locale' => $file['locale'],
        ];

        if (! empty($file['aliases'])) {
            $entry['aliases'] = array_values(array_unique($file['aliases']));
        }

        return $entry;
    }

    /** @return array<string, mixed> */
    private function activeRelease(): array
    {
        try {
            return model(GameReleaseModel::class)->activeOrNull() ?? [];
        } catch (\Throwable) {
            return [];
        }
    }

    /** Folder-safe version text: `2.0.0`, `1ba4502`. */
    public static function versionSlug(string $value): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9._-]+/', '-', trim($value)), '-.');
    }

    /** @param array<string, mixed> $state */
    private function progressOf(array $state): array
    {
        $total = $state['tasks'] === null ? $state['estimate'] : count($state['commons']) + count($state['tasks']);

        return [
            'package_code' => $state['package_code'],
            'done'         => $state['cursor'],
            'total'        => $total,
            'finished'     => $state['tasks'] !== null && $state['cursor'] >= $total,
        ];
    }

    /**
     * Run $change on the saved state under an exclusive lock, so two batch
     * requests for the same package never process the same files.
     *
     * @param callable(array<string, mixed>&): void $change
     */
    private function withState(string $code, callable $change): array
    {
        $path = $this->statePathOrFail($code);
        $fh   = fopen($path, 'c+');

        if ($fh === false || ! flock($fh, LOCK_EX)) {
            throw new RuntimeException("Build state of {$code} could not be locked.");
        }

        try {
            $state = json_decode((string) stream_get_contents($fh), true);

            if (! is_array($state)) {
                throw new RuntimeException("Build state of {$code} is unreadable.");
            }

            $change($state);

            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, (string) json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            fflush($fh);

            return $this->progressOf($state);
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }

    /** @return array<string, mixed> */
    private function loadState(string $code): array
    {
        $state = json_decode((string) file_get_contents($this->statePathOrFail($code)), true);

        if (! is_array($state)) {
            throw new RuntimeException("Build state of {$code} is unreadable.");
        }

        return $state;
    }

    private function statePathOrFail(string $code): string
    {
        if (! AssetPackageService::validCode($code) || ! is_file($this->packages->statePath($code))) {
            throw new RuntimeException("No build in progress for package {$code}.");
        }

        return $this->packages->statePath($code);
    }

    /** @param array<string, mixed> $state */
    private function saveState(array $state): void
    {
        $this->writeAtomic($this->packages->statePath($state['package_code']), (string) json_encode($state, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function writeAtomic(string $path, string $content): void
    {
        $tmp = $path . '.tmp-' . bin2hex(random_bytes(4));

        if (file_put_contents($tmp, $content, LOCK_EX) !== strlen($content) || ! @rename($tmp, $path)) {
            @unlink($tmp);

            throw new RuntimeException("{$path} could not be written.");
        }
    }

    private function ensureDir(string $dir): void
    {
        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new RuntimeException("Folder {$dir} could not be created.");
        }
    }
}
