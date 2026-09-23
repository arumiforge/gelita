<?php

namespace App\Libraries;

/**
 * Penulis XLSX bertahap untuk export penelitian.
 *
 * Dataset raw event bisa ratusan ribu baris, jadi workbook tidak pernah
 * dibangun utuh di memori. Setiap sheet ditulis sebagai XML SpreadsheetML ke
 * berkas sementara (buffer di-flush ke disk tiap 1000 baris); `finish()`
 * merangkai berkas-berkas itu menjadi paket XLSX dengan ZipArchive.
 *
 * Seluruh teks ditulis sebagai inline string (`t="inlineStr"`), tidak pernah
 * sebagai rumus — nilai yang diawali `=`, `+`, `-`, atau `@` tetap teks biasa
 * saat dibuka di Excel/LibreOffice. Angka PHP (int/float) menjadi sel angka;
 * bool menjadi 0/1; null menjadi sel kosong.
 */
class ExcelWriter
{
    public const FLUSH_EVERY = 1000;

    /** Batas baris satu sheet XLSX (termasuk header). */
    public const MAX_ROWS = 1048576;

    private string $path;

    private string $tmpDir;

    /** @var list<array{name: string, file: string, rows: int, cols: int}> */
    private array $sheets = [];

    /** @var resource|null */
    private $handle;

    private string $buffer = '';

    private int $bufferedRows = 0;

    private int $dataRows = 0;

    private bool $finished = false;

    public function __construct(string $path)
    {
        $dir = dirname($path);

        if (! is_dir($dir) && ! mkdir($dir, 0775, true) && ! is_dir($dir)) {
            throw new \RuntimeException("Folder tujuan export tidak dapat dibuat: {$dir}");
        }

        $this->path   = $path;
        $this->tmpDir = $dir . DIRECTORY_SEPARATOR . '.tmp-' . bin2hex(random_bytes(6));

        if (! mkdir($this->tmpDir, 0700)) {
            throw new \RuntimeException('Folder sementara export tidak dapat dibuat.');
        }
    }

    public function __destruct()
    {
        if (! $this->finished) {
            $this->abort();
        }
    }

    /**
     * Membuka sheet baru; sheet sebelumnya ditutup otomatis.
     *
     * @param list<string> $headers baris pertama, ditebalkan dan dibekukan
     */
    public function startSheet(string $name, array $headers): void
    {
        $this->assertOpen();
        $this->closeSheet();

        $index = count($this->sheets) + 1;
        $file  = $this->tmpDir . DIRECTORY_SEPARATOR . 'sheet' . $index . '.xml';

        $handle = fopen($file, 'wb');

        if ($handle === false) {
            throw new \RuntimeException('Berkas sementara sheet tidak dapat dibuka.');
        }

        $this->handle   = $handle;
        $this->sheets[] = [
            'name' => $this->uniqueSheetName($name),
            'file' => $file,
            'rows' => 0,
            'cols' => max(1, count($headers)),
        ];

        fwrite($handle, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0">'
            . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
            . '</sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>'
            . '<sheetData>');

        $this->writeRow(array_values($headers), 1);
    }

    /**
     * Menulis satu baris data pada sheet aktif; flush ke disk tiap 1000 baris.
     *
     * @param list<mixed> $values
     */
    public function row(array $values): void
    {
        $this->assertOpen();

        if ($this->handle === null) {
            throw new \LogicException('startSheet() harus dipanggil sebelum row().');
        }

        $this->writeRow(array_values($values), 0);
        $this->dataRows++;
    }

    /**
     * Menyuapkan baris dari generator secara bertahap.
     *
     * @param \Closure(): iterable<list<mixed>> $generator
     */
    public function feed(\Closure $generator): void
    {
        foreach ($generator() as $values) {
            $this->row($values);
        }
    }

    /** Jumlah baris data (tanpa header) yang sudah ditulis ke seluruh sheet. */
    public function rowCount(): int
    {
        return $this->dataRows;
    }

    /** Merangkai paket XLSX dan mengembalikan SHA-256 berkasnya. */
    public function finish(): string
    {
        $this->assertOpen();
        $this->closeSheet();

        if ($this->sheets === []) {
            throw new \LogicException('Workbook tanpa sheet tidak dapat ditulis.');
        }

        if (is_file($this->path)) {
            unlink($this->path);
        }

        $zip = new \ZipArchive();

        if ($zip->open($this->path, \ZipArchive::CREATE | \ZipArchive::EXCL) !== true) {
            throw new \RuntimeException('Berkas XLSX tidak dapat dibuat.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('docProps/app.xml', $this->appProps());
        $zip->addFromString('docProps/core.xml', $this->coreProps());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());

        foreach ($this->sheets as $i => $sheet) {
            $zip->addFile($sheet['file'], 'xl/worksheets/sheet' . ($i + 1) . '.xml');
        }

        if (! $zip->close()) {
            throw new \RuntimeException('Berkas XLSX gagal ditutup.');
        }

        $this->finished = true;
        $this->cleanup();

        $sha = hash_file('sha256', $this->path);

        if ($sha === false) {
            throw new \RuntimeException('SHA-256 berkas export tidak dapat dihitung.');
        }

        return $sha;
    }

    /** Membatalkan penulisan: berkas sementara dan berkas tujuan dihapus. */
    public function abort(): void
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }

        $this->finished = true;
        $this->cleanup();

        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    // ---------------------------------------------------------------- sel

    /** @param list<mixed> $values */
    private function writeRow(array $values, int $style): void
    {
        $current = count($this->sheets) - 1;
        $rowNo   = $this->sheets[$current]['rows'] + 1;

        if ($rowNo > self::MAX_ROWS) {
            throw new \OverflowException('Sheet ' . $this->sheets[$current]['name'] . ' melebihi batas baris XLSX.');
        }

        $xml = '<row r="' . $rowNo . '">';

        foreach ($values as $col => $value) {
            $xml .= $this->cell(self::columnName($col) . $rowNo, $value, $style);
        }

        $this->buffer .= $xml . '</row>';
        $this->sheets[$current]['rows'] = $rowNo;
        $this->sheets[$current]['cols'] = max($this->sheets[$current]['cols'], count($values));

        if (++$this->bufferedRows >= self::FLUSH_EVERY) {
            $this->flush();
        }
    }

    private function cell(string $ref, mixed $value, int $style): string
    {
        $s = $style > 0 ? ' s="' . $style . '"' : '';

        if ($value === null || $value === '') {
            return $style > 0 ? '<c r="' . $ref . '"' . $s . '/>' : '';
        }

        if (is_bool($value)) {
            $value = $value ? 1 : 0;
        }

        if (is_int($value) || (is_float($value) && is_finite($value))) {
            return '<c r="' . $ref . '"' . $s . '><v>' . $this->number($value) . '</v></c>';
        }

        if (is_array($value) || is_object($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $text = $this->xmlText((string) $value);

        return '<c r="' . $ref . '"' . $s . ' t="inlineStr"><is><t xml:space="preserve">' . $text . '</t></is></c>';
    }

    private function number(int|float $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        // tanpa notasi ilmiah dan tanpa pemisah ribuan lokal
        $text = rtrim(rtrim(sprintf('%.10F', $value), '0'), '.');

        return $text === '-0' ? '0' : $text;
    }

    /** Escape XML + buang karakter kontrol yang tidak sah di XML 1.0; maks 32.767 karakter per sel. */
    private function xmlText(string $value): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        $value = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        if (mb_strlen($value) > 32767) {
            $value = mb_substr($value, 0, 32767);
        }

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** 0 → A, 25 → Z, 26 → AA */
    public static function columnName(int $index): string
    {
        $name = '';

        for ($n = $index + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $name = chr(65 + (($n - 1) % 26)) . $name;
        }

        return $name;
    }

    // -------------------------------------------------------------- sheet

    private function flush(): void
    {
        if ($this->handle !== null && $this->buffer !== '') {
            if (fwrite($this->handle, $this->buffer) === false) {
                throw new \RuntimeException('Gagal menulis sheet export ke disk.');
            }
        }

        $this->buffer       = '';
        $this->bufferedRows = 0;
    }

    private function closeSheet(): void
    {
        if ($this->handle === null) {
            return;
        }

        $this->flush();

        $current = $this->sheets[count($this->sheets) - 1];
        $last    = self::columnName($current['cols'] - 1);

        fwrite($this->handle, '</sheetData>'
            . '<autoFilter ref="A1:' . $last . max(1, $current['rows']) . '"/>'
            . '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>'
            . '</worksheet>');
        fclose($this->handle);

        $this->handle = null;
    }

    /** Nama sheet XLSX: maks 31 karakter, tanpa []:*?/\, unik tanpa membedakan huruf besar. */
    private function uniqueSheetName(string $name): string
    {
        $base = trim((string) preg_replace('/[\[\]:*?\/\\\\]/', ' ', $name));
        $base = mb_substr($base === '' ? 'Sheet' : $base, 0, 31);

        $taken = array_map(static fn (array $s): string => mb_strtolower($s['name']), $this->sheets);
        $final = $base;

        for ($i = 2; in_array(mb_strtolower($final), $taken, true); $i++) {
            $suffix = ' (' . $i . ')';
            $final  = mb_substr($base, 0, 31 - strlen($suffix)) . $suffix;
        }

        return $final;
    }

    private function assertOpen(): void
    {
        if ($this->finished) {
            throw new \LogicException('Workbook sudah ditutup.');
        }
    }

    private function cleanup(): void
    {
        foreach ($this->sheets as $sheet) {
            if (is_file($sheet['file'])) {
                unlink($sheet['file']);
            }
        }

        if (is_dir($this->tmpDir)) {
            rmdir($this->tmpDir);
        }
    }

    // ------------------------------------------------------------ paket

    private function contentTypes(): string
    {
        $sheets = '';

        foreach (array_keys($this->sheets) as $i) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet' . ($i + 1) . '.xml"'
                . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . $sheets
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>';
    }

    private function appProps(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            . '<Application>GELITA</Application></Properties>';
    }

    private function coreProps(): string
    {
        $now = gmdate('Y-m-d\TH:i:s\Z');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"'
            . ' xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/"'
            . ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>GELITA — export data penelitian</dc:title><dc:creator>GELITA</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified>'
            . '</cp:coreProperties>';
    }

    private function workbook(): string
    {
        $sheets = '';

        foreach ($this->sheets as $i => $sheet) {
            $sheets .= '<sheet name="' . htmlspecialchars($sheet['name'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '"'
                . ' sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }

        $names = '';

        foreach ($this->sheets as $i => $sheet) {
            $quoted = "'" . str_replace("'", "''", $sheet['name']) . "'";
            $names .= '<definedName name="_xlnm._FilterDatabase" localSheetId="' . $i . '" hidden="1">'
                . htmlspecialchars($quoted, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                . '!$A$1:$' . self::columnName($sheet['cols'] - 1) . '$' . max(1, $sheet['rows'])
                . '</definedName>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView/></bookViews>'
            . '<sheets>' . $sheets . '</sheets>'
            . '<definedNames>' . $names . '</definedNames>'
            . '</workbook>';
    }

    private function workbookRels(): string
    {
        $rels = '';

        foreach (array_keys($this->sheets) as $i) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                . ' Target="worksheets/sheet' . ($i + 1) . '.xml"/>';
        }

        $rels .= '<Relationship Id="rId' . (count($this->sheets) + 1) . '"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels . '</Relationships>';
    }

    /** Gaya 0 = biasa, 1 = header tebal berlatar abu. */
    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="3"><fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8E2D4"/><bgColor indexed="64"/></patternFill></fill></fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
