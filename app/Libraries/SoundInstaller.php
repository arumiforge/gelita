<?php

namespace App\Libraries;

use RuntimeException;

/**
 * Installs music and sound effects made with ElevenLabs (owner, 15 November
 * 2026: music and sound effects are produced the same way as the backup
 * narration). Takes of FOLDER/out/{music|sfx}/{name}.mp3 are copied over the
 * game's own files in public/assets/audio/{music|sfx}/ and listed in
 * public/assets/audio/produksi.json with their sha256 and the model named in
 * FOLDER/settings.md, so it stays known which files are synthetic and where
 * they came from. Only names of AssetChecklist::SOUNDS are installed; a take
 * whose name is unknown is reported, never copied.
 *
 * The manifest also gives the files a cache version (`sound_files_version()`),
 * so a browser that holds an earlier copy loads the new one.
 */
final class SoundInstaller
{
    /** Settings sheet of the workspace (model per kind). */
    public const SETTINGS = 'settings.md';

    /** Finished, mastered takes inside the workspace. */
    public const OUT = 'out/';

    /** Manifest file name inside the audio folder. */
    public const MANIFEST = 'produksi.json';

    /** Kind → the line of settings.md that names its model. */
    private const MODEL_LINES = [
        'music' => 'ElevenLabs music model:',
        'sfx'   => 'ElevenLabs sound-effects model:',
    ];

    private string $audioDir;

    /** @param string|null $audioDir the game's audio folder (default public/assets/audio/) */
    public function __construct(?string $audioDir = null)
    {
        $this->audioDir = rtrim($audioDir ?? FCPATH . 'assets/audio', '/\\') . '/';
    }

    public function manifestPath(): string
    {
        return $this->audioDir . self::MANIFEST;
    }

    /**
     * Models per kind from settings.md: the text after "ElevenLabs music
     * model:" and "ElevenLabs sound-effects model:", backticks removed. A
     * line left empty gives no entry.
     *
     * @return array<string, string> kind → model
     */
    public static function parseSettings(string $markdown): array
    {
        $models = [];

        foreach (self::MODEL_LINES as $kind => $label) {
            if (preg_match('/^' . preg_quote($label, '/') . '[ \t]*(.*)$/mi', $markdown, $m) === 1) {
                $model = trim(str_replace('`', '', $m[1]));

                if ($model !== '') {
                    $models[$kind] = $model;
                }
            }
        }

        return $models;
    }

    /**
     * @return array{
     *     installed: list<string>, replaced: list<string>, unchanged: list<string>,
     *     unknown: list<array{file: string, suggestion: string|null}>,
     *     errors: list<string>, files: int, written: bool
     * }
     */
    public function install(string $source, bool $dryRun = false): array
    {
        $source = rtrim($source, '/\\') . '/';
        $report = ['installed' => [], 'replaced' => [], 'unchanged' => [], 'unknown' => [], 'errors' => [], 'files' => 0, 'written' => false];

        if (! is_dir($source . self::OUT)) {
            throw new RuntimeException('Folder ' . $source . self::OUT . ' tidak ada.');
        }

        $takes = $this->takes($source, $report);

        if ($takes === []) {
            return $report;
        }

        $settings = is_file($source . self::SETTINGS) ? self::parseSettings((string) file_get_contents($source . self::SETTINGS)) : [];

        foreach (array_unique(array_map(static fn (string $name): string => explode('/', $name)[0], array_keys($takes))) as $kind) {
            if (! isset($settings[$kind])) {
                $report['errors'][] = self::SETTINGS . ' belum menyebut model untuk ' . ($kind === 'music' ? 'musik' : 'efek suara')
                    . ' (baris "' . self::MODEL_LINES[$kind] . '").';
            }
        }

        foreach ($takes as $name => $path) {
            if (! self::looksLikeMp3($path)) {
                $report['errors'][] = $name . '.mp3 bukan berkas MP3 yang utuh.';
            }
        }

        if ($report['errors'] !== []) {
            return $report;
        }

        $manifest = $this->readManifest();
        $before   = $manifest;

        foreach ($takes as $name => $path) {
            $sha    = (string) hash_file('sha256', $path);
            $target = $this->audioDir . $name . '.mp3';
            $entry  = ['sha256' => $sha, 'model' => $settings[explode('/', $name)[0]]];

            if (is_file($target) && hash_file('sha256', $target) === $sha) {
                $report['unchanged'][] = $name;
            } else {
                $report[isset($manifest[$name]) ? 'replaced' : 'installed'][] = $name;

                if (! $dryRun) {
                    $this->copy($path, $target);
                }
            }

            $manifest[$name] = $entry;
        }

        ksort($manifest);

        if ($manifest !== $before) {
            $report['written'] = true;

            if (! $dryRun) {
                $this->writeManifest($manifest);
            }
        }

        return $report;
    }

    /**
     * Mastered takes by sound name (`music/map`), unknown names reported.
     *
     * @param array<string, mixed> $report
     *
     * @return array<string, string> name → absolute path
     */
    private function takes(string $source, array &$report): array
    {
        $takes = [];
        $known = array_keys(AssetChecklist::SOUNDS);

        foreach (['music', 'sfx'] as $kind) {
            foreach (glob($source . self::OUT . $kind . '/*') ?: [] as $path) {
                $file = basename($path);

                if (! is_file($path) || str_starts_with($file, '.')) {
                    continue;
                }

                $report['files']++;
                $name = $kind . '/' . preg_replace('/\.mp3$/i', '', $file);

                if (! str_ends_with(strtolower($file), '.mp3') || ! in_array($name, $known, true)) {
                    $report['unknown'][] = ['file' => $kind . '/' . $file, 'suggestion' => self::closest($name, $known)];

                    continue;
                }

                $takes[$name] = $path;
            }
        }

        ksort($takes);

        return $takes;
    }

    /** @param list<string> $known */
    private static function closest(string $name, array $known): ?string
    {
        $best = null;
        $dist = 4;

        foreach ($known as $candidate) {
            $d = levenshtein($name, $candidate);

            if ($d < $dist) {
                [$best, $dist] = [$candidate, $d];
            }
        }

        return $best;
    }

    /** An ID3 tag or an MPEG audio frame at the start, and more than a header's worth of data. */
    private static function looksLikeMp3(string $path): bool
    {
        $head = (string) file_get_contents($path, false, null, 0, 3);

        if (filesize($path) < 1000 || strlen($head) < 3) {
            return false;
        }

        return str_starts_with($head, 'ID3') || (ord($head[0]) === 0xFF && (ord($head[1]) & 0xE0) === 0xE0);
    }

    /** @return array<string, array{sha256: string, model: string}> */
    private function readManifest(): array
    {
        if (! is_file($this->manifestPath())) {
            return [];
        }

        $data = json_decode((string) file_get_contents($this->manifestPath()), true);

        if (! is_array($data) || ! is_array($data['files'] ?? null)) {
            throw new RuntimeException(self::MANIFEST . ' di ' . $this->audioDir . ' tidak bisa dibaca.');
        }

        return $data['files'];
    }

    /** @param array<string, array{sha256: string, model: string}> $files */
    private function writeManifest(array $files): void
    {
        $json = json_encode([
            'about' => 'Music and sound effects installed from the ElevenLabs workspace by php spark gelita:sound:install. Written only by that command.',
            'files' => $files,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";

        $tmp = $this->manifestPath() . '.tmp';

        if (file_put_contents($tmp, $json) === false || ! rename($tmp, $this->manifestPath())) {
            throw new RuntimeException('Tidak bisa menulis ' . $this->manifestPath() . '.');
        }
    }

    private function copy(string $from, string $to): void
    {
        if (! is_dir(dirname($to)) && ! mkdir(dirname($to), 0775, true) && ! is_dir(dirname($to))) {
            throw new RuntimeException('Tidak bisa membuat folder ' . dirname($to) . '.');
        }

        $tmp = $to . '.tmp';

        if (! copy($from, $tmp) || ! rename($tmp, $to)) {
            @unlink($tmp);

            throw new RuntimeException('Tidak bisa menyalin ' . basename($from) . ' ke ' . $to . '.');
        }
    }
}
