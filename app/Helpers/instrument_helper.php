<?php

/**
 * View helpers of the test module (research revision 10, part 8: 34 §7.1).
 *
 * instrument_text() renders the small Markdown subset of the data files
 * (paragraphs, **bold**, *italic*, `code`, "\" line breaks, "- " lists, and
 * tables) as escaped HTML, so passages and posts read as written in 14 and 15.
 */

if (! function_exists('instrument_inline')) {
    function instrument_inline(string $text): string
    {
        $html = esc($text);
        $html = (string) preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);
        $html = (string) preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);

        return (string) preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/s', '<em>$1</em>', $html);
    }
}

if (! function_exists('instrument_text')) {
    function instrument_text(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        $out = [];

        foreach (preg_split('/\n\s*\n/', trim($markdown)) ?: [] as $block) {
            $lines = explode("\n", trim($block));

            if (str_starts_with($lines[0], '|')) {
                $rows = array_values(array_filter($lines, static fn (string $l): bool => preg_match('/^\|\s*-{3}/', $l) !== 1));
                $html = '<div class="table-responsive"><table class="table table-bordered table-sm">';

                foreach ($rows as $i => $row) {
                    $cells = array_map('trim', explode('|', trim(trim($row), '|')));
                    $tag   = $i === 0 ? 'th' : 'td';
                    $html .= '<tr>' . implode('', array_map(static fn (string $c): string => "<{$tag}>" . instrument_inline($c) . "</{$tag}>", $cells)) . '</tr>';
                }

                $out[] = $html . '</table></div>';

                continue;
            }

            if (str_starts_with($lines[0], '- ')) {
                $out[] = '<ul>' . implode('', array_map(static fn (string $l): string => '<li>' . instrument_inline(substr($l, 2)) . '</li>', $lines)) . '</ul>';

                continue;
            }

            $out[] = '<p>' . implode('<br>', array_map(static fn (string $l): string => instrument_inline(rtrim($l, '\\ ')), $lines)) . '</p>';
        }

        return implode("\n", $out);
    }
}
