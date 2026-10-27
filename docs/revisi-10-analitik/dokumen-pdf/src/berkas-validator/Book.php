<?php

declare(strict_types=1);

namespace BerkasValidator;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * One validator workbook: a guide sheet, rating sheets with the header block
 * (pseudonym, version, date) and drop-down lists, and a hidden list sheet.
 */
final class Book
{
    public const HEADER_ROW = 7;

    /** Fixed document date (27 Oct 2026 UTC) so a rebuild without source changes gives the same properties. */
    private const STAMP = 1793059200;

    private Spreadsheet $book;
    private int $listCol = 0;
    private ?Worksheet $lists = null;

    public function __construct(private readonly string $title, private readonly string $version)
    {
        $this->book = new Spreadsheet();
        $this->book->removeSheetByIndex(0);
        $p = $this->book->getProperties();
        $p->setCreator('GELITA revisi 10')->setLastModifiedBy('GELITA revisi 10')->setTitle($title)
            ->setCreated(self::STAMP)->setModified(self::STAMP);
    }

    /** Guide sheet: one paragraph or table row per line; a list value becomes a row of cells. */
    public function guide(string $name, array $lines): void
    {
        $s = $this->book->createSheet();
        $s->setTitle($name);
        $s->getColumnDimension('A')->setWidth(28);
        $s->getColumnDimension('B')->setWidth(110);
        $s->setCellValue('A1', $this->title);
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $s->setCellValue('A2', 'Versi berkas: ' . $this->version);
        $r = 4;
        foreach ($lines as $line) {
            if (is_array($line)) {
                foreach (array_values($line) as $i => $v) {
                    $s->setCellValue([$i + 1, $r], $v);
                }
                $s->getStyle("A{$r}")->getFont()->setBold(true);
            } elseif (str_starts_with($line, '## ')) {
                $s->setCellValue("A{$r}", substr($line, 3));
                $s->getStyle("A{$r}")->getFont()->setBold(true)->setSize(12);
            } else {
                $s->setCellValue("A{$r}", $line);
                $s->mergeCells("A{$r}:B{$r}");
            }
            $s->getStyle("A{$r}:B{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $r++;
        }
    }

    /**
     * Rating sheet.
     *
     * @param list<array{head: string, width?: int, list?: list<string>, ref?: bool}> $cols
     *        ref = reference column (grey, filled by the generator); list = drop-down values
     * @param list<list<mixed>|array{group: string}> $rows
     */
    public function sheet(string $name, string $title, string $instrument, array $pseudonyms, array $cols, array $rows): Worksheet
    {
        $s = $this->book->createSheet();
        $s->setTitle($name);
        $s->setCellValue('A1', $title);
        $s->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $s->setCellValue('A2', 'Instrumen');
        $s->setCellValue('B2', $instrument);
        $s->setCellValue('A3', 'Nama samaran validator');
        $s->setCellValue('A4', 'Versi berkas');
        $s->setCellValue('B4', $this->version);
        $s->setCellValue('A5', 'Tanggal pengisian');
        $s->getStyle('A2:A5')->getFont()->setBold(true);
        $s->getStyle('B3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF5D6');
        $s->getStyle('B5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF5D6');
        $this->validate($s, 'B3', $pseudonyms);

        $h = self::HEADER_ROW;
        foreach ($cols as $i => $c) {
            $col = Coordinate::stringFromColumnIndex($i + 1);
            $s->setCellValue("{$col}{$h}", $c['head']);
            $s->getColumnDimension($col)->setWidth($c['width'] ?? 14);
        }
        $last = Coordinate::stringFromColumnIndex(count($cols));
        $hs = $s->getStyle("A{$h}:{$last}{$h}");
        $hs->getFont()->setBold(true);
        $hs->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $hs->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E2EC');

        $r = $h + 1;
        foreach ($rows as $row) {
            if (isset($row['group'])) {
                $s->setCellValue("A{$r}", $row['group']);
                $s->mergeCells("A{$r}:{$last}{$r}");
                $s->getStyle("A{$r}")->getFont()->setBold(true);
                $s->getStyle("A{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EEF2F6');
                $r++;
                continue;
            }
            foreach ($cols as $i => $c) {
                $v = $row[$i] ?? '';
                $s->setCellValueExplicit([$i + 1, $r], (string) $v, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $col = Coordinate::stringFromColumnIndex($i + 1);
                if (! empty($c['ref'])) {
                    $s->getStyle("{$col}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F2F2F2');
                } elseif ($v === '' && isset($c['list'])) {
                    $this->validate($s, "{$col}{$r}", $c['list']);
                } elseif ($v !== '' && empty($c['ref']) && $i > 1) {
                    $s->getStyle("{$col}{$r}")->getFont()->getColor()->setRGB('7B8794');
                }
            }
            $r++;
        }
        $end = max($r - 1, $h);
        $s->getStyle("A{$h}:{$last}{$end}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $s->getStyle("A{$h}:{$last}{$end}")->getBorders()->getAllBorders()->setBorderStyle('thin')->getColor()->setRGB('BCCCDC');
        $s->freezePane('A' . ($h + 1));
        $s->setAutoFilter("A{$h}:{$last}{$end}");
        $s->getPageSetup()->setOrientation('landscape')->setFitToWidth(1)->setFitToHeight(0);
        $s->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($h, $h);

        return $s;
    }

    private function validate(Worksheet $s, string $cell, array $values): void
    {
        $v = $s->getCell($cell)->getDataValidation();
        $v->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)->setShowDropDown(true)->setShowErrorMessage(true)
            ->setErrorTitle('Pilihan tidak tersedia')->setError('Pilih salah satu nilai dari daftar.');
        $inline = '"' . implode(',', $values) . '"';
        if (strlen($inline) <= 250 && ! array_filter($values, fn ($x) => str_contains($x, ','))) {
            $v->setFormula1($inline);

            return;
        }
        $v->setFormula1($this->listRange($values));
    }

    /** Long lists go to the hidden sheet "Daftar". */
    private function listRange(array $values): string
    {
        if ($this->lists === null) {
            $this->lists = $this->book->createSheet();
            $this->lists->setTitle('Daftar');
            $this->lists->setSheetState(Worksheet::SHEETSTATE_HIDDEN);
        }
        $key = implode("\n", $values);
        static $cache = [];
        $id = spl_object_id($this) . ':' . $key;
        if (! isset($cache[$id])) {
            $col = Coordinate::stringFromColumnIndex(++$this->listCol);
            foreach (array_values($values) as $i => $val) {
                $this->lists->setCellValueExplicit($col . ($i + 1), $val, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
            $cache[$id] = "Daftar!\${$col}\$1:\${$col}\$" . count($values);
        }

        return $cache[$id];
    }

    public function save(string $path): void
    {
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }
        $this->book->setActiveSheetIndex(0);
        (new Xlsx($this->book))->save($path);

        // Pin the zip entry times as well, so unchanged sources give an identical file.
        $zip = new \ZipArchive();
        if ($zip->open($path) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $zip->setMtimeIndex($i, self::STAMP);
            }
            $zip->close();
        }
    }
}
