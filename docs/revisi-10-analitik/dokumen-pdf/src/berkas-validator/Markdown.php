<?php

declare(strict_types=1);

namespace BerkasValidator;

use RuntimeException;

/**
 * Minimal Markdown reader for the revision documents: splits a file into
 * headings, block quotes, tables, and paragraphs, in document order.
 */
final class Markdown
{
    /**
     * @return list<array{type: string, level?: int, text?: string, head?: list<string>, rows?: list<list<string>>, line: int}>
     */
    public static function blocks(string $path, ?string $from = null, ?string $until = null): array
    {
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException("Cannot read {$path}");
        }
        $lines  = preg_split('/\R/u', $raw);
        $blocks = [];
        $n      = count($lines);
        $active = $from === null;

        for ($i = 0; $i < $n; $i++) {
            $line = $lines[$i];
            if (preg_match('/^(#{1,6}) (.*)$/u', $line, $m)) {
                $title = trim($m[2]);
                if (! $active && str_starts_with($title, $from)) {
                    $active = true;
                }
                if ($active && $until !== null && str_starts_with($title, $until)) {
                    break;
                }
                if ($active) {
                    $blocks[] = ['type' => 'h', 'level' => strlen($m[1]), 'text' => $title, 'line' => $i + 1];
                }
                continue;
            }
            if (! $active || trim($line) === '') {
                continue;
            }
            $start = $i;
            if (str_starts_with($line, '|')) {
                $rowsRaw = [];
                while ($i < $n && str_starts_with($lines[$i], '|')) {
                    $rowsRaw[] = $lines[$i++];
                }
                $i--;
                $head = self::cells($rowsRaw[0]);
                $rows = [];
                foreach (array_slice($rowsRaw, 2) as $r) {
                    $rows[] = self::cells($r);
                }
                $blocks[] = ['type' => 'table', 'head' => $head, 'rows' => $rows, 'line' => $start + 1];
                continue;
            }
            if (str_starts_with($line, '>')) {
                $buf = [];
                while ($i < $n && str_starts_with($lines[$i], '>')) {
                    $buf[] = preg_replace('/^> ?/u', '', $lines[$i++]);
                }
                $i--;
                $blocks[] = ['type' => 'quote', 'text' => implode("\n", $buf), 'line' => $start + 1];
                continue;
            }
            $buf = [];
            while ($i < $n && trim($lines[$i]) !== '' && ! preg_match('/^(#{1,6} |\||>)/u', $lines[$i])) {
                $buf[] = $lines[$i++];
            }
            $i--;
            $blocks[] = ['type' => 'p', 'text' => implode("\n", $buf), 'line' => $start + 1];
        }

        return $blocks;
    }

    /** @return list<string> */
    public static function cells(string $row): array
    {
        $row   = trim($row);
        $row   = preg_replace('/^\||\|$/u', '', $row);
        $parts = preg_split('/(?<!\\\\)\|/u', $row);

        return array_map(static fn ($c) => trim(str_replace('\|', '|', $c)), $parts);
    }

    /** Markdown inline markup to plain text (keeps line breaks). */
    public static function plain(string $s): string
    {
        $s = preg_replace('/\[([^\]]*)\]\([^)]*\)/u', '$1', $s);      // links
        $s = str_replace(['**', '`'], '', $s);
        $s = preg_replace('/(?<![\w*])\*(?!\s)([^*\n]+?)(?<!\s)\*(?![\w*])/u', '$1', $s); // italics
        $s = preg_replace('/\\\\$/mu', '', $s);                        // hard breaks
        $s = preg_replace('/^- /mu', '• ', $s);

        return trim($s);
    }

    /**
     * A block quote that may hold a Markdown table, as plain text: table
     * rows become "a · b · c", separator rows disappear.
     */
    public static function quoteText(string $quote): string
    {
        $out = [];
        foreach (preg_split('/\n/u', $quote) as $line) {
            if (preg_match('/^\|\s*-{3}/u', $line)) {
                continue;
            }
            if (str_starts_with($line, '|')) {
                $line = implode(' · ', self::cells($line));
            }
            $out[] = $line;
        }
        $text = self::plain(implode("\n", $out));

        return preg_replace("/\n{3,}/u", "\n\n", $text);
    }

    /** Column index by header name (exact, else prefix). */
    public static function col(array $head, string $name): int
    {
        foreach ($head as $i => $h) {
            if ($h === $name) {
                return $i;
            }
        }
        foreach ($head as $i => $h) {
            if (str_starts_with($h, $name)) {
                return $i;
            }
        }
        throw new RuntimeException("Column '{$name}' not in [" . implode(' | ', $head) . ']');
    }
}
