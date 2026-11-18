<?php

namespace App\Libraries;

/**
 * Shared shape of the panel's guides (staff guide /admin/panduan-staf,
 * validator guide /admin/panduan-validator): text markup, page addresses,
 * glossary lookups, search text and menu order. A guide is a data class
 * with BASE, terms() and chapters(); the views (admin/guide/*) take the
 * guide's class name as $guide and call these methods on it.
 *
 * Markup inside strings (everything else is escaped):
 *   **bold**, `code`, [label](#section-id) (a link to another page of the
 *   same guide), [label](/admin/panduan-staf) (another page of this site),
 *   [label](https://…) (an outside page, https only; the GELITA guide
 *   links the asset catalogue this way), [[term key]] or
 *   [[shown text|term key]] (a glossary term).
 */
abstract class GuideContent
{
    /** URL path of the guide; a section page is BASE/{id}. */
    public const BASE = '';

    /** The public asset catalogue: pictures, sound and music live there. */
    public const CATALOGUE = 'https://katalog.gelita.id/';

    /**
     * Glossary: key => term, def, example, more (section id, or null for
     * the glossary page `istilah`). Optional, for a richer popover (owner,
     * 18 November 2026): `kind` (a short label above the term, e.g. "Kode
     * kesalahan"), `picture` (a committed picture under public/assets/ui/
     * as `ui/name.webp`, or a media slot key such as `bg.reflection`; shown
     * only when the file is there) and `catalogue` (a fragment of the asset
     * catalogue, e.g. `tantangan/rumpang` or `gambar/bg.reflection`, linked
     * as "Lihat di Katalog Aset").
     *
     * @return array<string, array{term: string, def: string, example: string, more: string|null, kind?: string, picture?: string, catalogue?: string}>
     */
    abstract public static function terms(): array;

    /**
     * Address of a term's picture, or null when it is not installed here:
     * `ui/…` is a committed picture (versioned URL), anything else a media
     * slot key.
     */
    public static function pictureSrc(string $picture): ?string
    {
        if (str_starts_with($picture, 'ui/')) {
            return is_file(FCPATH . 'assets/' . $picture) ? asset_url_versioned($picture) : null;
        }

        return media_key_src($picture);
    }

    /** @return list<array<string, mixed>> */
    abstract public static function chapters(): array;

    /**
     * Guide text to HTML: escapes everything, then turns **bold**, `code`,
     * [label](#section) and [[term]] into markup. A term key that does not
     * exist stays plain text. $query is appended to guide links (a guest's
     * pick).
     */
    public static function inline(string $text, string $query = ''): string
    {
        $html = esc($text);
        $html = (string) preg_replace('/\*\*(.+?)\*\*/u', '<b>$1</b>', $html);
        $html = (string) preg_replace('/`(.+?)`/u', '<code>$1</code>', $html);
        $html = (string) preg_replace('/\[([^\]]+)\]\((https:\/\/[^\s)"<>]+)\)/u', '<a href="$2" rel="noopener">$1</a>', $html);
        $html = (string) preg_replace_callback(
            '/\[([^\]]+)\]\(\/([a-z0-9\/-]+)\)/u',
            static fn (array $m): string => '<a href="' . esc(base_url($m[2])) . '">' . $m[1] . '</a>',
            $html,
        );
        $html = (string) preg_replace_callback(
            '/\[([^\]]+)\]\(#([a-z0-9-]+)\)/u',
            static fn (array $m): string => '<a href="' . esc(static::url($m[2]) . $query) . '">' . $m[1] . '</a>',
            $html,
        );

        $html = (string) preg_replace_callback(
            '/\[\[(?:([^\]|]+)\|)?([a-z0-9-]+)\]\]/u',
            static function (array $m): string {
                $terms = static::terms();
                $shown = $m[1] !== '' ? $m[1] : ($terms[$m[2]]['term'] ?? $m[2]);

                if (! isset($terms[$m[2]])) {
                    return $shown;
                }

                return '<button type="button" class="term" popovertarget="istilah-pop-' . $m[2] . '">' . $shown . '</button>';
            },
            $html,
        );

        // Keep a bracket or a punctuation mark on the term's line (a lone "("
        // at a line end): both go in a span that does not wrap, while the
        // term's own words still may (.term-glue in admin.css)
        return (string) preg_replace_callback(
            '/(\()?(<button type="button" class="term"[^>]*>.*?<\/button>)([).,;:])?/u',
            static fn (array $m): string => ($m[1] ?? '') === '' && ($m[3] ?? '') === ''
                ? $m[2]
                : '<span class="term-glue">' . $m[1] . $m[2] . ($m[3] ?? '') . '</span>',
            $html,
        );
    }

    /**
     * Every source reference used by the guide, for GuideSourceCheck.
     *
     * @return list<string>
     */
    public static function sources(): array
    {
        $refs = [];

        foreach (static::chapters() as $chapter) {
            foreach ($chapter['sections'] as $section) {
                array_push($refs, ...($section['sources'] ?? []));
            }
        }

        return array_values(array_unique($refs));
    }

    /** Address of a guide page; '' is the first page. */
    public static function url(string $section = ''): string
    {
        return base_url($section === '' ? static::BASE : static::BASE . '/' . $section);
    }

    /**
     * Term keys used in a section's text, in first-use order.
     *
     * @param array<string, mixed> $section
     *
     * @return list<string>
     */
    public static function termsIn(array $section): array
    {
        preg_match_all('/\[\[(?:[^\]|]+\|)?([a-z0-9-]+)\]\]/u', (string) json_encode($section, JSON_UNESCAPED_UNICODE), $m);

        return array_values(array_intersect(array_unique($m[1]), array_keys(static::terms())));
    }

    /**
     * Plain text of a section, for the guide's search box.
     *
     * @param array<string, mixed> $section
     */
    public static function plainText(array $section): string
    {
        $parts = [];
        array_walk_recursive($section, static function ($value, $key) use (&$parts): void {
            if (is_string($value) && ! in_array($key, ['id', 'facts', 'sources', 'docs'], true) && ! str_starts_with($value, 'admin/')) {
                $parts[] = $value;
            }
        });
        $text = preg_replace(['/\[\[(?:([^\]|]+)\|)?([a-z0-9-]+)\]\]/u', '/\[([^\]]+)\]\((?:#[a-z0-9-]+|https:\/\/[^\s)]+|\/[a-z0-9\/-]+)\)/u', '/\*\*|`/u'], ['$1$2', '$1', ''], implode(' ', $parts));

        return mb_strtolower(trim((string) preg_replace('/\s+/u', ' ', (string) $text)));
    }

    /**
     * Menu order for one reader. Chapters written for every audience keep
     * their place at the start and end; the chapters for the reader's own
     * audience come right after the first one, then the other audiences'
     * chapters, folded in the menu. Adds `open` and `mine` to each chapter.
     *
     * @param list<array<string, mixed>> $chapters each with the list $key
     * @param list<string>               $all      every audience key
     *
     * @return list<array<string, mixed>>
     */
    public static function orderFor(array $chapters, string $reader, array $all, string $key): array
    {
        $first = [];
        $own   = [];
        $other = [];
        $last  = [];

        foreach ($chapters as $index => $chapter) {
            $forAll          = array_diff($all, $chapter[$key]) === [];
            $chapter['open'] = $forAll || in_array($reader, $chapter[$key], true);
            $chapter['mine'] = $chapter['open'] && ! $forAll;

            if ($forAll) {
                $index === 0 ? $first[] = $chapter : $last[] = $chapter;
            } elseif ($chapter['open']) {
                $own[] = $chapter;
            } else {
                $other[] = $chapter;
            }
        }

        // The narrower chapter first (the ketua's own before shared field work)
        usort($own, static fn (array $a, array $b): int => count($a[$key]) <=> count($b[$key]));

        return [...$first, ...$own, ...$other, ...$last];
    }

    /**
     * Sections in reading order, each with its chapter's id and title.
     *
     * @param list<array<string, mixed>> $chapters
     *
     * @return array<string, array<string, mixed>>
     */
    public static function sectionsById(array $chapters): array
    {
        $out = [];

        foreach ($chapters as $chapter) {
            foreach ($chapter['sections'] as $section) {
                $out[$section['id']] = $section + ['chapter' => $chapter['id'], 'chapterTitle' => $chapter['title']];
            }
        }

        return $out;
    }
}
