<?php

namespace App\Libraries;

/**
 * Keeps a hand-written guide in step with the research documents it was
 * written from (owner, 17 November 2026).
 *
 * Each guide section names its sources as `doc.md#anchor` (a heading of a
 * document in docs/revisi-10-analitik/, anchor as GitHub makes it). A lock
 * file holds the fingerprint each source section had when a developer last
 * checked the guide against it. When a source section's text changes, its
 * fingerprint no longer matches: the guide page shows signed-in staff a
 * "check this page" note, ValidatorGuideTest fails, and
 * `php spark gelita:panduan:cek` lists it. After the guide text is checked
 * (and changed where needed), `gelita:panduan:cek --tandai` records the new
 * fingerprints.
 *
 * Why not copy the documents into the page: they carry developer notes,
 * blinded label information and internal paths that validators must not
 * see. The guide is written for validators; this check only tells the team
 * when it may have fallen behind.
 */
final class GuideSourceCheck
{
    /** Folder the source references are relative to. */
    public const DOCS = 'docs/revisi-10-analitik/';

    /** Lock file of the validator guide, relative to ROOTPATH. */
    public const VALIDATOR_LOCK = 'app/Libraries/GuideSources/validator.json';

    public const OK      = 'ok';
    public const CHANGED = 'berubah';
    public const NEW     = 'baru';
    public const GONE    = 'hilang';
    public const NO_DOCS = 'tanpa-dokumen';

    /** @var array<string, string>|null */
    private ?array $locked = null;

    public function __construct(
        private readonly string $lockFile,
        private readonly string $root = ROOTPATH,
    ) {
    }

    public static function forValidatorGuide(): self
    {
        return new self(ROOTPATH . self::VALIDATOR_LOCK);
    }

    /** Whether the research documents are on this install at all (a server may ship without them). */
    public function docsPresent(): bool
    {
        return is_dir($this->root . self::DOCS);
    }

    /**
     * GitHub-style anchor of a Markdown heading: lower case, punctuation
     * dropped (an en dash too), spaces to hyphens.
     */
    public static function anchor(string $heading): string
    {
        $text = mb_strtolower(trim($heading));
        $text = (string) preg_replace('/[^\p{L}\p{N} _-]/u', '', $text);

        return str_replace(' ', '-', $text);
    }

    /**
     * Text of one section: from its heading to the next heading of the same
     * or a higher level (sub-sections included). Null when the document or
     * the heading is missing. Duplicate headings get GitHub's -1, -2 suffixes.
     */
    public function sectionText(string $ref): ?string
    {
        [$doc, $anchor] = array_pad(explode('#', $ref, 2), 2, '');
        $path           = $this->root . self::DOCS . $doc;

        if ($doc === '' || ! is_file($path)) {
            return null;
        }

        $lines   = preg_split('/\R/u', (string) file_get_contents($path)) ?: [];
        $seen    = [];
        $start   = null;
        $level   = 0;
        $inFence = false;
        $out     = [];

        foreach ($lines as $line) {
            if (str_starts_with(ltrim($line), '```')) {
                $inFence = ! $inFence;
            }

            $isHeading = ! $inFence && preg_match('/^(#{1,6})\s+(.+?)\s*#*\s*$/u', $line, $m) === 1;

            if ($isHeading) {
                $slug = self::anchor($m[2]);
                $seen[$slug] = isset($seen[$slug]) ? $seen[$slug] + 1 : 0;
                $slug .= $seen[$slug] > 0 ? '-' . $seen[$slug] : '';

                if ($start !== null && strlen($m[1]) <= $level) {
                    break;
                }

                if ($start === null && ($anchor === '' || $slug === $anchor)) {
                    $start = true;
                    $level = strlen($m[1]);
                }
            }

            if ($start !== null) {
                $out[] = $line;
            }
        }

        // No anchor: the whole document
        if ($anchor === '') {
            return implode("\n", $lines);
        }

        return $start === null ? null : implode("\n", $out);
    }

    /** Fingerprint of a source section, whitespace-insensitive; null when it is missing. */
    public function fingerprint(string $ref): ?string
    {
        $text = $this->sectionText($ref);

        return $text === null ? null : substr(sha1(trim((string) preg_replace('/\s+/u', ' ', $text))), 0, 12);
    }

    /** @return array<string, string> ref => recorded fingerprint */
    public function locked(): array
    {
        if ($this->locked === null) {
            $data         = is_file($this->lockFile) ? json_decode((string) file_get_contents($this->lockFile), true) : [];
            $this->locked = is_array($data) ? array_map('strval', $data['sources'] ?? []) : [];
        }

        return $this->locked;
    }

    /**
     * State of every reference: ok, berubah (text changed), baru (never
     * recorded), hilang (heading or document gone), tanpa-dokumen (this
     * install has no research documents; nothing can be said).
     *
     * @param list<string> $refs
     *
     * @return array<string, string> ref => state
     */
    public function states(array $refs): array
    {
        $out = [];

        foreach (array_unique($refs) as $ref) {
            if (! $this->docsPresent()) {
                $out[$ref] = self::NO_DOCS;

                continue;
            }

            $now    = $this->fingerprint($ref);
            $before = $this->locked()[$ref] ?? null;

            $out[$ref] = match (true) {
                $now === null    => self::GONE,
                $before === null => self::NEW,
                $now !== $before => self::CHANGED,
                default          => self::OK,
            };
        }

        return $out;
    }

    /**
     * References whose state is not ok (an install without the documents
     * reports nothing).
     *
     * @param list<string> $refs
     *
     * @return array<string, string>
     */
    public function problems(array $refs): array
    {
        return array_filter($this->states($refs), static fn (string $s): bool => ! in_array($s, [self::OK, self::NO_DOCS], true));
    }

    /**
     * Records the current fingerprint of every reference (and drops those no
     * longer used). Refuses while a reference points at a missing heading.
     *
     * @param list<string> $refs
     *
     * @return list<string> references that could not be recorded (missing)
     */
    public function mark(array $refs): array
    {
        $sources = [];
        $missing = [];

        foreach (array_unique($refs) as $ref) {
            $print = $this->fingerprint($ref);

            if ($print === null) {
                $missing[] = $ref;

                continue;
            }
            $sources[$ref] = $print;
        }

        if ($missing !== []) {
            return $missing;
        }

        ksort($sources);

        if (! is_dir(dirname($this->lockFile))) {
            mkdir(dirname($this->lockFile), 0775, true);
        }

        $json = [
            'about'   => 'Fingerprints of the research-document sections the validator guide was last checked against. Update with: php spark gelita:panduan:cek --tandai (after checking the guide text).',
            'sources' => $sources,
        ];
        file_put_contents($this->lockFile, json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
        $this->locked = $sources;

        return [];
    }
}
