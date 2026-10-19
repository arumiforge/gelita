<?php

namespace App\Libraries;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Wikimedia\Minify\CSSMin;
use Wikimedia\Minify\JavaScriptMinifier;
use Wikimedia\Minify\ParseError;

/**
 * Minified copies of the game and panel code for browsers (owner,
 * 19 October 2026). The sources under public/assets/{js,css} are never
 * touched; `gelita:aset:minify` writes a mirror to public/assets/min/ with
 * the same names and folders, so relative ES-module imports and the CSS
 * `url('../fonts/…')` keep working:
 *
 *   assets/min/{version}/js/…      JavaScriptMinifier (comments, whitespace; no renaming)
 *   assets/min/{version}/css/…     CSSMin
 *   assets/min/{version}/fonts/…   copied as they are (the fonts the CSS asks for)
 *   assets/min/sources.json        current version and the fingerprint of every source
 *
 * `{version}` is a hash of the minified bytes, so every build gets new URLs,
 * including modules imported without `?v=`. A build is written to a
 * temporary folder and becomes current only when sources.json is replaced
 * (one rename); the previous version stays for pages that are still open
 * and may lazy-load a module. Everything happens inside assets/min/, the
 * only folder the web server user needs to write.
 *
 * The mirror is used only while it is fresh: every source still has the
 * fingerprint it had at build time (mtime and size, as asset_file_version())
 * and no source was added or removed. One edited or pulled file without a
 * new run makes the whole set fall back to the originals, so a page never
 * mixes a new game.js with old imports.
 */
class AssetMinifier
{
    /** Mirror folder, relative to public/. */
    public const MIN_DIR = 'assets/min/';

    public const STATE_FILE = 'sources.json';

    /** Source folders relative to public/assets/ and what happens to their files. */
    public const SOURCES = [
        'js'    => ['ext' => 'js', 'mode' => 'js'],
        'css'   => ['ext' => 'css', 'mode' => 'css'],
        'fonts' => ['ext' => 'woff2', 'mode' => 'copy'],
    ];

    private string $publicDir;

    /** @var array{version: string, keys: array<string, true>}|false|null fresh mirror (memo); false when stale */
    private array|false|null $fresh = null;

    public function __construct(?string $publicDir = null)
    {
        $this->publicDir = rtrim($publicDir ?? FCPATH, '/\\') . '/';
    }

    /** Config switch `minifyAssets`. */
    public function enabled(): bool
    {
        return config('Gelita')->minifyAssets;
    }

    /**
     * Path of the minified copy relative to public/assets/
     * (`min/{version}/js/game.js`), or null when the switch is off, the
     * mirror is stale or missing, or the file is not mirrored (images,
     * vendor files).
     */
    public function path(string $key): ?string
    {
        if (! $this->enabled() || ($fresh = $this->fresh()) === false) {
            return null;
        }

        $key = ltrim(str_replace('\\', '/', $key), '/');

        return isset($fresh['keys'][$key]) ? 'min/' . $fresh['version'] . '/' . $key : null;
    }

    /** True when the mirror matches every source right now. */
    public function isFresh(): bool
    {
        return $this->fresh() !== false;
    }

    /**
     * Rebuilds the mirror unless it is already fresh. All or nothing: a
     * failed file leaves the current mirror (fresh or stale) as it was.
     *
     * @return array{rebuilt: bool, version: string, files: int, source_bytes: int, min_bytes: int}
     *
     * @throws RuntimeException naming the file that could not be minified
     */
    public function refresh(bool $force = false): array
    {
        $sources = $this->scan();
        $fresh   = $this->fresh();

        if (! $force && $fresh !== false) {
            return ['rebuilt' => false] + $this->sizes($sources, $fresh['version']);
        }

        $root = $this->publicDir . self::MIN_DIR;
        $temp = $root . '.tmp-' . bin2hex(random_bytes(4)) . '/';

        try {
            $hashes = [];

            foreach ($sources as $key => $info) {
                $out = $temp . $key;

                if (! is_dir(dirname($out)) && ! @mkdir(dirname($out), 0775, true) && ! is_dir(dirname($out))) {
                    throw new RuntimeException("Cannot create a folder in " . self::MIN_DIR . " for {$key}.");
                }

                $bytes = $this->minifyFile($key, $info['mode']);

                if (@file_put_contents($out, $bytes) === false) {
                    throw new RuntimeException("Cannot write the minified copy of {$key}.");
                }

                $hashes[] = $key . ':' . hash('sha256', $bytes);
            }

            $version = substr(hash('sha256', implode("\n", $hashes)), 0, 12);

            if (! is_dir($root . $version) && ! @rename($temp, $root . $version)) {
                throw new RuntimeException('Cannot move the new build into ' . self::MIN_DIR . '.');
            }

            $previous = $this->state()['version'] ?? null;
            $state    = [
                'format'     => 1,
                'version'    => $version,
                'previous'   => $previous !== $version ? $previous : ($this->state()['previous'] ?? null),
                'created_at' => date(DATE_ATOM),
                'files'      => array_map(static fn (array $i): string => $i['fingerprint'], $sources),
            ];
            $stateTemp = $root . '.state-' . bin2hex(random_bytes(4));

            if (@file_put_contents($stateTemp, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n") === false
                || ! @rename($stateTemp, $root . self::STATE_FILE)) {
                @unlink($stateTemp);

                throw new RuntimeException('Cannot write ' . self::MIN_DIR . self::STATE_FILE . '.');
            }
        } finally {
            self::removeDir($temp);
        }

        // Keep the current and the previous version; older builds go
        foreach (glob($root . '*', GLOB_ONLYDIR) ?: [] as $dir) {
            if (! in_array(basename($dir), [$state['version'], $state['previous']], true)) {
                self::removeDir($dir);
            }
        }

        $this->fresh = null;

        return ['rebuilt' => true] + $this->sizes($sources, $version);
    }

    /**
     * Mirror bytes of one source: minified JS/CSS, fonts as they are.
     *
     * @throws RuntimeException when the file cannot be read or minified
     */
    protected function minifyFile(string $key, string $mode): string
    {
        $source = @file_get_contents($this->publicDir . 'assets/' . $key);

        if ($source === false) {
            throw new RuntimeException("Cannot read {$key}.");
        }

        return $mode === 'copy' ? $source : self::minify($source, $mode, $key);
    }

    /**
     * Minified text of one JS or CSS file. The JavaScript tokenizer is
     * lenient: it reports only some errors and passes the rest through, so
     * tools/qa/minify-check.mjs (run by AssetMinifierTest) compares the
     * syntax trees of every real file and its copy.
     *
     * @throws RuntimeException when the JavaScript tokenizer reports a parse error
     */
    public static function minify(string $source, string $mode, string $name = 'input'): string
    {
        if ($mode === 'css') {
            return CSSMin::minify($source);
        }

        $error = null;
        $out   = JavaScriptMinifier::minify($source, static function (ParseError $e) use (&$error): void {
            $error ??= $e;
        });

        if ($error !== null) {
            throw new RuntimeException("Cannot minify {$name}: {$error->getMessage()} (offset {$error->getOffset()}).");
        }

        return $out;
    }

    /**
     * Every mirrored source with its fingerprint, sorted by key.
     *
     * @return array<string, array{mode: string, fingerprint: string}>
     */
    private function scan(): array
    {
        $files = [];

        foreach (self::SOURCES as $dir => $rule) {
            $root = $this->publicDir . 'assets/' . $dir;

            if (! is_dir($root)) {
                continue;
            }

            foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)) as $file) {
                if (! $file->isFile() || strtolower($file->getExtension()) !== $rule['ext']) {
                    continue;
                }

                $key         = $dir . '/' . str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
                $files[$key] = ['mode' => $rule['mode'], 'fingerprint' => $file->getMTime() . ':' . $file->getSize()];
            }
        }

        ksort($files, SORT_STRING);

        return $files;
    }

    /** @return array<string, mixed> contents of sources.json, [] when missing or unreadable */
    private function state(): array
    {
        $json  = @file_get_contents($this->publicDir . self::MIN_DIR . self::STATE_FILE);
        $state = is_string($json) ? json_decode($json, true) : null;

        return is_array($state) ? $state : [];
    }

    /**
     * Current version and keys while the mirror is fresh, false otherwise (memoised).
     *
     * @return array{version: string, keys: array<string, true>}|false
     */
    private function fresh(): array|false
    {
        if ($this->fresh !== null) {
            return $this->fresh;
        }

        $state    = $this->state();
        $version  = $state['version'] ?? null;
        $recorded = $state['files'] ?? null;
        $current  = array_map(static fn (array $i): string => $i['fingerprint'], $this->scan());

        if (! is_string($version) || preg_match('/^[0-9a-f]{12}$/', $version) !== 1 || ! is_array($recorded) || $current === [] || $recorded != $current) {
            return $this->fresh = false;
        }

        foreach (array_keys($current) as $key) {
            if (! is_file($this->publicDir . self::MIN_DIR . $version . '/' . $key)) {
                return $this->fresh = false;
            }
        }

        return $this->fresh = ['version' => $version, 'keys' => array_fill_keys(array_keys($current), true)];
    }

    /**
     * @param array<string, array{mode: string, fingerprint: string}> $sources
     *
     * @return array{version: string, files: int, source_bytes: int, min_bytes: int}
     */
    private function sizes(array $sources, string $version): array
    {
        $source = $min = 0;

        foreach (array_keys($sources) as $key) {
            $source += (int) @filesize($this->publicDir . 'assets/' . $key);
            $min    += (int) @filesize($this->publicDir . self::MIN_DIR . $version . '/' . $key);
        }

        return ['version' => $version, 'files' => count($sources), 'source_bytes' => $source, 'min_bytes' => $min];
    }

    private static function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }

        rmdir($dir);
    }
}
