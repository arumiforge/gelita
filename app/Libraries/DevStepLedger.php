<?php

namespace App\Libraries;

use Throwable;

/**
 * What a developer install last imported, so `gelita:setelah-pull` can say
 * which spark commands a `git pull` made due (owner, 17 November 2026).
 *
 * For each import step (PostPullCheck::LEDGER_STEPS) the ledger keeps the
 * fingerprint of the step's input files at the moment the step last ran on
 * this install. The import commands record themselves when they finish
 * without errors on their default source; a step whose inputs have a
 * different fingerprint now is due.
 *
 * The file is writable/gelita-langkah.json: per install, never committed
 * (listed in .gitignore). Recording never fails a command: any error here
 * is swallowed, because the ledger is a convenience, not a guard. Under
 * ENVIRONMENT === 'testing' the default ledger records nothing (tests use
 * their own file through the constructor).
 */
final class DevStepLedger
{
    public const FILE = 'gelita-langkah.json';

    /** @var array<string, array{fingerprint: string, at: string}>|null */
    private ?array $data = null;

    public function __construct(
        private readonly string $file,
        private readonly string $root = ROOTPATH,
    ) {
    }

    public static function forInstall(): self
    {
        return new self(WRITEPATH . self::FILE);
    }

    /**
     * Called by an import command after it finished on its default source.
     * Does nothing in tests and never throws.
     */
    public static function recordStep(string $step): void
    {
        if (ENVIRONMENT === 'testing') {
            return;
        }

        try {
            self::forInstall()->record($step);
        } catch (Throwable) {
            // The ledger is a convenience; an unwritable writable/ must not fail an import.
        }
    }

    public function record(string $step): void
    {
        $inputs = PostPullCheck::LEDGER_STEPS[$step]['inputs'] ?? null;

        if ($inputs === null) {
            return;
        }

        $data        = $this->all();
        $data[$step] = ['fingerprint' => $this->fingerprint($inputs), 'at' => date('Y-m-d H:i:s')];
        ksort($data);
        $this->data = $data;

        file_put_contents($this->file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n", LOCK_EX);
    }

    /** @return array{fingerprint: string, at: string}|null */
    public function get(string $step): ?array
    {
        return $this->all()[$step] ?? null;
    }

    /** @return array<string, array{fingerprint: string, at: string}> */
    public function all(): array
    {
        if ($this->data === null) {
            $json       = is_file($this->file) ? json_decode((string) file_get_contents($this->file), true) : [];
            $this->data = is_array($json) ? $json : [];
        }

        return $this->data;
    }

    /**
     * Fingerprint of a step's inputs: every matching file (relative path and
     * content hash), in path order. Missing inputs count as empty, so a
     * folder that appears or disappears changes the fingerprint too.
     *
     * @param array{paths: list<string>, ext?: list<string>, skip?: list<string>} $inputs
     */
    public function fingerprint(array $inputs): string
    {
        $algo  = in_array('xxh128', hash_algos(), true) ? 'xxh128' : 'sha1';
        $files = [];

        foreach ($inputs['paths'] as $path) {
            foreach ($this->files($path, $inputs['ext'] ?? [], $inputs['skip'] ?? []) as $file) {
                $files[$file] = hash_file($algo, $this->root . $file);
            }
        }

        ksort($files);
        $lines = [];

        foreach ($files as $file => $hash) {
            $lines[] = $file . "\t" . $hash;
        }

        return hash($algo, implode("\n", $lines));
    }

    /**
     * Files under one input path, relative to the root, with forward slashes.
     *
     * @param list<string> $ext  lower-case extensions to keep ([] = all)
     * @param list<string> $skip sub-folders (relative to $path) to leave out
     *
     * @return list<string>
     */
    private function files(string $path, array $ext, array $skip): array
    {
        $full = $this->root . $path;

        if (is_file($full)) {
            return [$path];
        }

        if (! is_dir($full)) {
            return [];
        }

        $out  = [];
        $iter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($full, \FilesystemIterator::SKIP_DOTS));

        foreach ($iter as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $rel   = str_replace('\\', '/', substr($file->getPathname(), strlen($this->root)));
            $inner = substr($rel, strlen($path) + 1);

            foreach ($skip as $folder) {
                if (str_starts_with($inner, $folder . '/')) {
                    continue 2;
                }
            }

            if ($ext !== [] && ! in_array(strtolower($file->getExtension()), $ext, true)) {
                continue;
            }

            $out[] = $rel;
        }

        sort($out);

        return $out;
    }
}
