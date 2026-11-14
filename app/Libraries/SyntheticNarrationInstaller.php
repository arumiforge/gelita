<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * Installs backup narration made with ElevenLabs (ai-studio/elevenlabs-voice/)
 * into the narration folder, and records every installed file in the
 * production manifest (produksi.json) so `gelita:narration:import` stores it
 * as synthetic: `production_method = tts` plus a `voice_profile` naming the
 * model and the voice. Without the manifest the same files would be stored as
 * real voices, which would be false in the research data.
 *
 * Source folder layout:
 *
 *   voices.md               model and voice per speaker and language
 *   out/{id|en}/{code}.mp3  one take per script line, named after its file code
 *
 * Rules:
 * - Nothing is copied while the model or the voice of a speaker with takes is
 *   missing in voices.md: a take without a voice profile is not installed.
 * - Voice actors come first (owner, 14 November 2026). A line that already has
 *   a real recording, in the narration folder or uploaded in the panel, keeps
 *   it; the take is reported as kept unless $overwrite is set.
 * - A take replaces an earlier synthetic take of the same line (a folder file
 *   whose sha256 is in the manifest).
 * - This class writes files only. Registering them in the database stays with
 *   NarrationImporter, which is run afterwards.
 */
final class SyntheticNarrationInstaller
{
    /** Voice sheet in the source folder. */
    public const VOICES = 'voices.md';

    /** Takes in the source folder: out/{locale}/. */
    public const OUT = 'out/';

    /** Heading keyword of each language's voice column in voices.md. */
    private const VOICE_COLUMNS = ['id' => 'indonesian', 'en' => 'english'];

    private BaseConnection $db;

    public function __construct(private NarrationImporter $importer, ?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Reads voices.md: the line "ElevenLabs model: …" and the first table whose
     * heading starts with "Speaker". A value written in italics (the
     * "_not chosen yet_" placeholder) counts as empty.
     *
     * @return array{model: string, voices: array<string, array{id: string, en: string, settings: string}>} voices keyed by lowercase speaker label
     */
    public static function parseVoices(string $markdown): array
    {
        $clean = static function (string $cell): string {
            $cell = trim(str_replace('`', '', $cell));

            return preg_match('/^_.*_$/s', $cell) === 1 ? '' : $cell;
        };

        $model   = preg_match('/^ElevenLabs model:(.*)$/mi', $markdown, $m) === 1 ? $clean($m[1]) : '';
        $voices  = [];
        $columns = null;

        foreach (preg_split('/\R/', $markdown) ?: [] as $line) {
            $line = trim($line);

            if (! str_starts_with($line, '|')) {
                if ($columns !== null) {
                    break;   // the voice table ended; the pronunciation table below is not voices
                }

                continue;
            }

            $cells = array_map($clean, explode('|', trim($line, '|')));

            if ($columns === null) {
                if (strtolower(substr($cells[0], 0, 7)) !== 'speaker') {
                    continue;
                }

                $columns = ['settings' => null] + array_fill_keys(array_keys(self::VOICE_COLUMNS), null);

                foreach ($cells as $index => $heading) {
                    $heading = strtolower($heading);

                    foreach (self::VOICE_COLUMNS as $locale => $keyword) {
                        $columns[$locale] ??= str_starts_with($heading, $keyword) ? $index : null;
                    }

                    $columns['settings'] ??= str_starts_with($heading, 'settings') ? $index : null;
                }

                continue;
            }

            if ($cells[0] === '' || preg_match('/^[-: ]+$/', $cells[0]) === 1) {
                continue;   // the |---| rule under the heading
            }

            $voices[mb_strtolower($cells[0])] = [
                'id'       => $columns['id'] === null ? '' : ($cells[$columns['id']] ?? ''),
                'en'       => $columns['en'] === null ? '' : ($cells[$columns['en']] ?? ''),
                'settings' => $columns['settings'] === null ? '' : ($cells[$columns['settings']] ?? ''),
            ];
        }

        return ['model' => $model, 'voices' => $voices];
    }

    /**
     * Text stored in `audio_assets.voice_profile` (at most 200 characters).
     *
     * @param array{id: string, en: string, settings: string} $voice
     */
    public static function voiceProfile(string $model, array $voice, string $locale): string
    {
        return mb_substr(implode(' · ', array_filter(['ElevenLabs ' . $model, $voice[$locale] ?? '', $voice['settings']], static fn (string $part): bool => $part !== '')), 0, 200);
    }

    /**
     * @param string $source the workspace folder (absolute), holding voices.md and out/
     *
     * @return array{
     *     dry_run: bool, files: int, written: bool, errors: list<string>,
     *     installed: list<string>, replaced: list<string>, unchanged: list<string>,
     *     kept: list<array{file: string, reason: string}>,
     *     unknown: list<array{file: string, reason: string, suggestion: string|null}>
     * } codes are reported as "{locale}/{code}"
     */
    public function install(string $source, bool $dryRun = false, bool $overwrite = false): array
    {
        $source = rtrim(str_replace('\\', '/', $source), '/') . '/';
        $report = ['dry_run' => $dryRun, 'files' => 0, 'written' => false, 'errors' => [], 'installed' => [], 'replaced' => [], 'unchanged' => [], 'kept' => [], 'unknown' => []];

        if (! is_dir($source . self::OUT)) {
            $report['errors'][] = 'Folder ' . $source . self::OUT . ' tidak ada.';

            return $report;
        }

        if (! is_file($source . self::VOICES)) {
            $report['errors'][] = 'Berkas ' . $source . self::VOICES . ' tidak ada.';

            return $report;
        }

        $sheet = self::parseVoices((string) file_get_contents($source . self::VOICES));
        $takes = $this->takes($source, $report);

        if ($takes !== [] && $sheet['model'] === '') {
            $report['errors'][] = 'Model ElevenLabs belum diisi di ' . self::VOICES . ' (baris "ElevenLabs model:").';
        }

        $missing = [];

        foreach ($takes as $index => $take) {
            $voice = $sheet['voices'][mb_strtolower($take['speaker'])] ?? null;

            if ($voice === null || $voice[$take['locale']] === '') {
                $missing[$take['speaker'] . ' (' . strtoupper($take['locale']) . ')'] = true;

                continue;
            }

            $takes[$index]['profile'] = self::voiceProfile($sheet['model'], $voice, $take['locale']);
        }

        foreach (array_keys($missing) as $who) {
            $report['errors'][] = 'Suara ' . $who . ' belum diisi di ' . self::VOICES . '.';
        }

        if ($report['errors'] !== []) {
            return $report;   // all or nothing: no take is installed without its voice profile
        }

        $path     = $this->importer->manifestPath();
        $manifest = is_file($path) ? json_decode((string) file_get_contents($path), true) : null;
        $manifest = is_array($manifest) ? $manifest : [];
        $files    = is_array($manifest['files'] ?? null) ? $manifest['files'] : [];
        $changed  = false;
        $copies   = [];

        foreach ($takes as $take) {
            ['locale' => $locale, 'code' => $code, 'sha' => $sha] = $take;

            $label  = $locale . '/' . $code;
            $dir    = FCPATH . $this->importer->folderFor($locale);
            $target = $dir . $code . '.' . $take['extension'];
            $known  = strtolower((string) ($files[$locale][$code]['sha256'] ?? ''));
            $same   = false;
            $reason = $this->uploadedVoice($locale, $code, $sha);
            $stale  = [];

            foreach (NarrationImporter::EXTENSIONS as $extension) {
                $existing = $dir . $code . '.' . $extension;

                if (! is_file($existing)) {
                    continue;
                }

                $current = hash_file('sha256', $existing);

                if ($existing === $target && $current === $sha) {
                    $same = true;

                    continue;
                }

                // Anything but this take or an earlier synthetic take of the line is a real recording
                if ($current !== $sha && $current !== $known) {
                    $reason ??= 'sudah ada rekaman lain di folder narasi (' . basename($existing) . ')';
                }

                $stale[] = $existing;
            }

            if ($reason !== null && ! $overwrite) {
                $report['kept'][] = ['file' => $label, 'reason' => $reason];

                continue;
            }

            // Copied again over a real recording too, so the import sees a file newer than the one it replaces
            $state = $stale !== [] || $reason !== null ? 'replaced' : ($same ? 'unchanged' : 'installed');

            $entry = ['sha256' => $sha, 'voice_profile' => $take['profile']];

            if (($files[$locale][$code] ?? null) != $entry) {
                $files[$locale][$code] = $entry;
                $changed               = true;
            }

            $report[$state][] = $label;

            if ($state !== 'unchanged') {
                $copies[] = ['label' => $label, 'from' => $take['path'], 'to' => $target, 'stale' => $stale];
            }
        }

        if ($dryRun) {
            return $report;
        }

        // The manifest goes first: a listed file that is not there yet is harmless,
        // a copied file that is not listed would be imported as a real voice.
        if ($changed) {
            foreach ($files as &$codes) {
                ksort($codes);
            }

            unset($codes);

            $manifest['note']  = 'Synthetic narration (ElevenLabs), written by php spark gelita:narration:install. A file whose sha256 is listed here is stored as production_method = tts.';
            $manifest['files'] = $files;

            if (! $this->writeManifest($path, $manifest)) {
                $report['errors'][] = 'Manifest ' . $path . ' tidak dapat ditulis. Tidak ada rekaman yang disalin.';

                return $report;
            }

            $report['written'] = true;
        }

        foreach ($copies as $copy) {
            $dir = dirname($copy['to']);

            if ((! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) || ! copy($copy['from'], $copy['to'])) {
                $report['errors'][] = 'Rekaman ' . $copy['label'] . ' tidak dapat disalin ke ' . $copy['to'] . '.';

                continue;
            }

            foreach ($copy['stale'] as $old) {
                if ($old !== $copy['to']) {
                    @unlink($old);
                }
            }

            $report['written'] = true;
        }

        return $report;
    }

    /**
     * The takes in out/{locale}/ that belong to a script line, one per line and language.
     *
     * @param array<string, mixed> $report
     *
     * @return list<array{locale: string, code: string, path: string, extension: string, sha: string, speaker: string, profile?: string}>
     */
    private function takes(string $source, array &$report): array
    {
        $lines = $this->importer->lines();
        $takes = [];

        foreach (config('Gelita')->locales as $locale) {
            $dir    = $source . self::OUT . $locale . '/';
            $byCode = [];

            foreach (is_dir($dir) ? (scandir($dir) ?: []) : [] as $name) {
                if (str_starts_with($name, '.') || ! is_file($dir . $name)) {
                    continue;
                }

                $report['files']++;
                $match = $this->importer->match($name);

                if ($match['code'] === null) {
                    $report['unknown'][] = ['file' => $locale . '/' . $name, 'reason' => $match['error'], 'suggestion' => $match['suggestion']];

                    continue;
                }

                $byCode[$match['code']][] = $name;
            }

            ksort($byCode);

            foreach ($byCode as $code => $names) {
                $extension = static fn (string $name): string => strtolower(pathinfo($name, PATHINFO_EXTENSION));

                usort($names, static fn (string $a, string $b): int => array_search($extension($a), NarrationImporter::EXTENSIONS, true) <=> array_search($extension($b), NarrationImporter::EXTENSIONS, true));

                foreach (array_slice($names, 1) as $duplicate) {
                    $report['unknown'][] = ['file' => $locale . '/' . $duplicate, 'reason' => 'berkas ganda: ' . $code . ' sudah diambil dari ' . $names[0], 'suggestion' => null];
                }

                $character = (string) ($lines[$code]['character_code'] ?? '');

                $takes[] = [
                    'locale'    => $locale,
                    'code'      => (string) $code,
                    'path'      => $dir . $names[0],
                    'extension' => $extension($names[0]),
                    'sha'       => (string) hash_file('sha256', $dir . $names[0]),
                    'speaker'   => NarrationCatalog::CHARACTER_LABELS[$character] ?? $character,
                ];
            }
        }

        return $takes;
    }

    /**
     * A real recording registered for this line (uploaded in the panel or
     * imported from the folder) with other content than the take; null when
     * there is none. Cancelled recordings (inactive file) do not count.
     */
    private function uploadedVoice(string $locale, string $code, string $sha): ?string
    {
        $row = $this->db->table('media_assets ma')
            ->select('ma.sha256, aa.production_method')
            ->join('audio_assets aa', 'aa.media_asset_id = ma.id')
            ->where('ma.asset_key', NarrationImporter::assetKey($locale, $code))
            ->where('ma.is_active', 1)
            ->get()
            ->getRowArray();

        if ($row === null || $row['production_method'] === 'tts' || (string) $row['sha256'] === $sha) {
            return null;
        }

        return 'sudah ada rekaman suara asli yang terpasang di permainan';
    }

    /** @param array<string, mixed> $manifest */
    private function writeManifest(string $path, array $manifest): bool
    {
        $json = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
        $temp = $path . '.tmp';
        $dir  = dirname($path);

        if ((! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) || file_put_contents($temp, $json) === false || ! rename($temp, $path)) {
            @unlink($temp);

            return false;
        }

        $this->importer->reloadManifest();

        return true;
    }
}
