<?php

namespace App\Libraries;

use Mpdf\Mpdf;
use Mpdf\Output\Destination;

/**
 * Printable script for the voice actors (owner request, 17 October 2026):
 * every narration line (story script and Cari Objek clues) in one table per
 * scene, in game order, with the file name to save each take under, the
 * speaker with pose/effect as a delivery cue, and a tick box.
 *
 * Laid out to save paper: A4 portrait, narrow margins, a condensed 8.5 pt
 * font, group headings as table rows instead of new pages, and one language
 * per print (`id` or `en`); `semua` puts both side by side for the director.
 */
final class NarrationScriptPdf
{
    /** `?bahasa=` values: one language or both side by side. */
    public const LANGUAGES = ['id', 'en', 'semua'];

    private NarrationCatalog $catalog;

    public function __construct(?NarrationCatalog $catalog = null)
    {
        $this->catalog = $catalog ?? new NarrationCatalog();
    }

    public static function normalize(?string $language): string
    {
        return in_array($language, self::LANGUAGES, true) ? (string) $language : 'id';
    }

    /** "gelita-naskah-rekaman-id-20261017.pdf" */
    public static function filename(string $language): string
    {
        return 'gelita-naskah-rekaman-' . self::normalize($language) . '-' . date('Ymd') . '.pdf';
    }

    /**
     * View data for pdf/narration-script.
     *
     * @return array{language: string, title: string, groups: list<array<string, mixed>>, lines: int, speakers: array<string, int>}
     */
    public function data(string $language): array
    {
        $language = self::normalize($language);
        $rows     = $this->catalog->rows();
        $speakers = [];

        foreach ($rows as $row) {
            $name            = NarrationCatalog::CHARACTER_LABELS[$row['character_code']] ?? (string) $row['character_code'];
            $speakers[$name] = ($speakers[$name] ?? 0) + 1;
        }

        return [
            'language' => $language,
            'title'    => self::title($language),
            'groups'   => $this->catalog->groups($rows),
            'lines'    => count($rows),
            'speakers' => $speakers,
        ];
    }

    public function html(string $language): string
    {
        return view('pdf/narration-script', $this->data($language), ['saveData' => false]);
    }

    /** Writes the PDF to $path. */
    public function write(string $language, string $path): void
    {
        $language = self::normalize($language);
        $tempDir  = WRITEPATH . 'cache' . DIRECTORY_SEPARATOR . 'mpdf';

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0775, true);
        }

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'margin_left'       => 7,
            'margin_right'      => 7,
            'margin_top'        => 11,
            'margin_bottom'     => 9,
            'margin_header'     => 4,
            'margin_footer'     => 4,
            'tempDir'           => $tempDir,
            'default_font'      => 'dejavusanscondensed',
            'default_font_size' => 8.5,
        ]);

        // The HTML is escaped in the view; mPDF must not fetch outside sources.
        $mpdf->showImageErrors = false;
        $mpdf->SetTitle(self::title($language));
        $mpdf->SetAuthor('GELITA');
        $mpdf->SetCreator('GELITA');
        $mpdf->SetHTMLHeader('<div class="pdf-header">GELITA · ' . esc(self::title($language)) . '</div>');
        $mpdf->SetHTMLFooter(
            '<table class="pdf-footer"><tr><td>Dicetak ' . esc(date('d-m-Y H:i')) . '</td>'
            . '<td class="right">Halaman {PAGENO} dari {nbpg}</td></tr></table>',
        );
        $mpdf->WriteHTML($this->html($language));
        $mpdf->Output($path, Destination::FILE);
    }

    private static function title(string $language): string
    {
        return 'Naskah rekaman suara · ' . match ($language) {
            'en'    => 'bahasa Inggris',
            'semua' => 'bahasa Indonesia dan Inggris',
            default => 'bahasa Indonesia',
        };
    }
}
