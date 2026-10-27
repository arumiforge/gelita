<?php

declare(strict_types=1);

namespace BerkasValidator;

use DOMDocument;
use DOMElement;
use DOMXPath;
use RuntimeException;

/**
 * Read-only access to the tables of one instrument HTML file.
 *
 * Tables are found by the heading (h2–h4) that precedes them, so the
 * workbooks follow the instrument's own column headers and fixed rows.
 */
final class Instrument
{
    private DOMXPath $xpath;
    private string $text;

    public function __construct(public readonly string $path)
    {
        $html = file_get_contents($path);
        if ($html === false) {
            throw new RuntimeException("Cannot read {$path}");
        }
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();
        $this->xpath = new DOMXPath($dom);
        $this->text  = self::clean($dom->textContent);
    }

    /** Whole document as plain text, for count checks. */
    public function text(): string
    {
        return $this->text;
    }

    /**
     * The $nth table after the last heading whose text starts with $heading
     * (the first match is usually the table of contents).
     *
     * @return array{head: list<string>, rows: list<list<string>>}
     */
    public function table(string $heading, int $nth = 0): array
    {
        $found = null;
        foreach ($this->xpath->query('//h2|//h3|//h4') as $h) {
            if (str_starts_with(self::clean($h->textContent), $heading)) {
                $found = $h;
            }
        }
        if ($found === null) {
            throw new RuntimeException("Heading '{$heading}' not found in " . basename($this->path));
        }
        $tables = $this->xpath->query('following::table', $found);
        $table  = $tables->item($nth);
        if (! $table instanceof DOMElement) {
            throw new RuntimeException("No table {$nth} after '{$heading}' in " . basename($this->path));
        }

        $head = [];
        foreach ($this->xpath->query('.//thead//th', $table) as $th) {
            $head[] = self::clean($th->textContent);
        }
        $rows = [];
        foreach ($this->xpath->query('.//tbody/tr|./tr', $table) as $tr) {
            $cells = [];
            foreach ($this->xpath->query('./td|./th', $tr) as $td) {
                $cells[] = self::clean($td->textContent);
            }
            $rows[] = $cells;
        }

        return ['head' => $head, 'rows' => $rows];
    }

    public static function clean(string $s): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($s, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }
}
