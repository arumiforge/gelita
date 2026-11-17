<?php

namespace App\Libraries;

use DOMDocument;
use DOMElement;
use DOMXPath;

/**
 * Status of the research-revision documents for the panel
 * (/admin/status-dokumen, head researcher only; owner, 17 November 2026).
 *
 * The one source is docs/revisi-10-analitik/status-dokumen.html in the
 * server's checkout (deploy.sh pulls the whole repository), so the panel
 * shows whatever the last deploy brought and there is no second copy. Only
 * the page's <main> is taken: its own <h1> and footer are dropped (the
 * panel has its own heading), links become plain text because docs/ is not
 * served to browsers (owner: names only, no links), and scripts or inline
 * handlers are removed even though the file is the repository's own.
 */
final class DocumentStatusPage
{
    public const SOURCE = 'docs/revisi-10-analitik/status-dokumen.html';

    /** The cleaned inner HTML of <main>, or null when the file is missing or unreadable. */
    public static function content(?string $path = null): ?string
    {
        $path ??= ROOTPATH . self::SOURCE;

        if (! is_file($path)) {
            return null;
        }

        $html = (string) file_get_contents($path);

        if ($html === '') {
            return null;
        }

        $dom      = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $loaded   = $dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return null;
        }

        $xpath = new DOMXPath($dom);
        $main  = $xpath->query('//main')->item(0);

        if (! $main instanceof DOMElement) {
            return null;
        }

        // The panel heading replaces the page's own; the footer only speaks about the file
        foreach (['.//h1', './/footer', './/script', './/style', './/iframe', './/form'] as $query) {
            foreach (iterator_to_array($xpath->query($query, $main)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        // Links to .md files lead nowhere on the server: keep their text
        foreach (iterator_to_array($xpath->query('.//a', $main)) as $link) {
            while ($link->firstChild !== null) {
                $link->parentNode?->insertBefore($link->firstChild, $link);
            }
            $link->parentNode?->removeChild($link);
        }

        foreach (iterator_to_array($xpath->query('.//@*[starts-with(name(), "on")]', $main)) as $handler) {
            $handler->ownerElement?->removeAttributeNode($handler);
        }

        $out = '';

        foreach ($main->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }
}
