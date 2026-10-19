<?php

namespace App\Libraries;

/**
 * Shrinks a server-rendered HTML page without changing how it renders.
 *
 * - Elements whose whitespace matters are kept byte for byte: `<pre>`,
 *   `<textarea>`, `<script>`, `<style>`, `<code>`, and any element marked
 *   `data-keep-space` (e.g. a `white-space: pre-wrap` cell in AdminTable;
 *   the marked element must not contain another element of the same tag).
 * - Comments are dropped, except IE conditional comments and the debug
 *   toolbar's `DEBUG-VIEW` markers (development only).
 * - Runs of whitespace in text become one space, never zero, so inline
 *   and inline-block neighbours keep their gap. Whitespace around tags that
 *   never render (doctype, html, head, meta, link, title, base, body) is
 *   removed.
 * - Inside a tag only the whitespace between attributes is collapsed;
 *   quoted attribute values stay as they are.
 *
 * A page that the patterns cannot handle (PCRE limit) comes back unchanged.
 */
class HtmlMinifier
{
    /**
     * The source watermark, "Mr. F" in the figlet font ANSI Shadow.
     */
    public const WATERMARK = <<<'TXT'
        ███╗   ███╗██████╗        ███████╗
        ████╗ ████║██╔══██╗       ██╔════╝
        ██╔████╔██║██████╔╝       █████╗
        ██║╚██╔╝██║██╔══██╗       ██╔══╝
        ██║ ╚═╝ ██║██║  ██║██╗    ██║
        ╚═╝     ╚═╝╚═╝  ╚═╝╚═╝    ╚═╝
        TXT;

    private const KEEP_TAGS = 'pre|textarea|script|style|code';

    /** Tags around which whitespace never renders. */
    private const SILENT_TAGS = '!doctype|html|head|meta|link|title|base|body';

    /**
     * Placeholder for a kept block; NUL never occurs in a valid HTML page.
     */
    private const MARK = "\0";

    public static function minify(string $html): string
    {
        $kept = [];
        $keep = static function (array $m) use (&$kept): string {
            $kept[] = $m[0];

            return self::MARK . (count($kept) - 1) . self::MARK;
        };

        $out = preg_replace_callback(
            [
                '~<(' . self::KEEP_TAGS . ')(?=[\s/>])[^>]*>.*?</\1\s*>~is',
                '~<([a-z][a-z0-9-]*)(?=[\s/>])[^>]*\sdata-keep-space(?=[\s=/>])[^>]*>.*?</\1\s*>~is',
                '~<!--\[if\b.*?<!\[endif\]-->~is',
                '~<!-- DEBUG-VIEW .*?-->~s',
            ],
            $keep,
            $html,
        );
        $out = $out === null ? null : preg_replace('~<!--.*?-->~s', '', $out);

        // Tags (quoted values may hold ">") and the text between them
        $parts = $out === null ? false : preg_split(
            '~(<[a-z!/][^>"\']*(?:(?:"[^"]*"|\'[^\']*\')[^>"\']*)*>)~i',
            $out,
            -1,
            PREG_SPLIT_DELIM_CAPTURE,
        );

        if ($parts === false) {
            return $html;
        }

        foreach ($parts as $i => $part) {
            $parts[$i] = $i % 2 === 1
                ? self::collapseTag($part)
                : preg_replace('~[ \t\r\n\f]+~', ' ', $part);
        }

        $out = implode('', $parts);
        $out = preg_replace(
            [
                '~[ \t\r\n\f]+(</?(?:' . self::SILENT_TAGS . ')(?=[\s/>]))~i',
                '~(</?(?:' . self::SILENT_TAGS . ')(?=[\s/>])[^>]*>)[ \t\r\n\f]+~i',
            ],
            '$1',
            $out,
        );

        if ($out === null) {
            return $html;
        }

        // Not plain trim(): it would also strip the NUL of a placeholder at either end
        $out = trim($out, " \t\r\n\f");

        // Newest first: a kept block can hold placeholders of older ones
        for ($i = count($kept) - 1; $i >= 0; $i--) {
            $out = str_replace(self::MARK . $i . self::MARK, $kept[$i], $out);
        }

        return $out;
    }

    /**
     * Prepends the watermark as a comment, before the doctype (allowed by
     * the HTML parser, no quirks mode). The charset comes from the
     * Content-Type header, so the extra bytes before `<meta charset>` are
     * harmless.
     */
    public static function watermark(string $html): string
    {
        return "<!--\n" . self::WATERMARK . "\n-->\n" . $html;
    }

    /**
     * Collapses whitespace between attributes; quoted values are untouched.
     */
    private static function collapseTag(string $tag): string
    {
        $pieces = preg_split('~("[^"]*"|\'[^\']*\')~', $tag, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($pieces === false) {
            return $tag;
        }

        foreach ($pieces as $i => $piece) {
            if ($i % 2 === 0) {
                $piece      = preg_replace('~[ \t\r\n\f]+~', ' ', $piece);
                $pieces[$i] = preg_replace('~ ?(/?>)$~', '$1', $piece);
            }
        }

        return implode('', $pieces);
    }
}
