<?php

namespace App\Services;

use App\Libraries\ErrorCodes;
use App\Libraries\FieldSheetSpec;
use App\Models\AuditLogModel;
use App\Models\FieldSessionMemberModel;
use App\Models\FieldSheetImportModel;
use App\Models\FieldSheetRowModel;
use CodeIgniter\Database\BaseConnection;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Field sheets L1–L6 (research revision 10, part 3: 29 §10, K4, K11–K13):
 * the filled template per field session, the preview and save of a typed
 * upload, and the second typing (check).
 *
 * Only codes ever reach a file or the server: the template holds
 * participant codes and booklets, no name, username, school, NPSN, or token;
 * L7 is never made here (only `gelita:lembar:templat`, off the web server).
 * The uploaded file is not kept, only its SHA-256.
 *
 * Saving supersedes the previous main import of the same sheet with a
 * conditional UPDATE (`id < new id AND superseded_at IS NULL`), so of two
 * uploads at once the newer counts. L5 `manual_code` goes to the try it
 * names in the same transaction; the superseded import's codes are cleared first.
 */
class FieldSheetService
{
    /** L5 worksheets per template: one per interviewed student, three per meeting (02 → L5). */
    public const L5_WORKSHEETS = 3;

    /** Row-number block per extra worksheet of one sheet (rows of worksheet k are k·1000 + row). */
    public const WORKSHEET_ROWS = 1000;

    /** Preview lifetime in the staff session (29 §13). */
    public const PREVIEW_TTL = 1800;

    public const SESSION_KEY = 'field_sheet_preview';

    /** Upload more than this after the session closed: a warning (02). */
    public const LATE_HOURS = 24;

    /** Words of a name shorter than this are not compared (29 §10.4). */
    private const NAME_MIN = 3;

    /** Sheet columns that become `started_at` / `ended_at`. */
    private const START_COLUMNS = ['start_time', 'obs_time', 'entry_time'];

    // ------------------------------------------------------------ sheets per meeting

    /**
     * Sheets a field session expects (29 §10.3): by meeting, makeups fewer.
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<string>
     */
    public function expectedSheets(array $fieldSession): array
    {
        $meeting = (int) $fieldSession['meeting_no'];
        $base    = ['l1_kondisi', 'l1_penyimpangan', 'l2_kursi', 'l3_bantuan'];
        $test    = in_array($meeting, [1, 5], true);

        if ((int) ($fieldSession['makeup_no'] ?? 0) > 0) {
            return $test ? [...$base, 'l6_sesi'] : $base;
        }

        return match (true) {
            $meeting === 1 => [...$base, 'l6_sesi'],
            $meeting === 5 => [...$base, 'l4_bromp', 'l6_sesi'],
            default        => [...$base, 'l4_bromp', 'l5_wawancara'],
        };
    }

    /**
     * Upload state per expected sheet: missing, uploaded (date, uploader),
     * checked (share of rows typed twice), overdue after 24 h (29 §10.4).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return list<array<string, mixed>>
     */
    public function status(array $fieldSession): array
    {
        $imports = model(FieldSheetImportModel::class);
        $closed  = $this->closedAt($fieldSession);
        $late    = $closed !== null && time() - strtotime($closed) > self::LATE_HOURS * 3600;
        $out     = [];

        foreach ($this->expectedSheets($fieldSession) as $sheet) {
            $main  = $imports->current((int) $fieldSession['id'], $sheet);
            $check = $main === null ? null : $imports->where('field_session_id', (int) $fieldSession['id'])->where('sheet', $sheet)
                ->where('purpose', 'check')->where('created_at >=', $main['created_at'])->orderBy('id', 'DESC')->first();

            $out[] = [
                'sheet'    => $sheet,
                'title'    => FieldSheetSpec::title($sheet),
                'state'    => $main === null ? 'missing' : ($check === null ? 'uploaded' : 'checked'),
                'overdue'  => $main === null && $late,
                'main'     => $main,
                'share'    => $main === null || $check === null || (int) $main['row_count'] === 0 ? null
                    : (int) round(100 * min(1, (int) $check['row_count'] / (int) $main['row_count'])),
            ];
        }

        return $out;
    }

    // ------------------------------------------------------------ template

    /**
     * The filled template of one field session (29 §10.3), written to $path.
     *
     * @param array<string, mixed> $fieldSession field_sessions row
     * @param list<string>|null    $sheets       null = the sheets of the meeting
     */
    public function writeTemplate(array $fieldSession, string $path, ?array $sheets = null): void
    {
        $book   = new Spreadsheet();
        $roster = $this->roster($fieldSession);
        $book->removeSheetByIndex(0);

        foreach ($sheets ?? $this->expectedSheets($fieldSession) as $sheet) {
            $this->templateSheet($book, $sheet, $fieldSession, $roster);
        }

        // Coordinator, wave F (29 §10.3, K12): on a test meeting, L6 status tes per siswa
        // printed from the test module (part 8); never uploaded, so not in expectedSheets()
        if ($sheets === null && ($testRows = (new FieldTestService())->testSheetRows($fieldSession)) !== []) {
            $this->testStatusSheet($book, $fieldSession, $testRows);
        }

        $this->codeSheet($book, array_column($roster, 'participant_code'));
        $book->setActiveSheetIndex(0);

        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
    }

    /**
     * Blank print templates, L7 among them, for `gelita:lembar:templat`
     * (29 §10.6). Never called by the web server.
     */
    public function writeBlankTemplate(string $path, array $sheets): void
    {
        $book = new Spreadsheet();
        $book->removeSheetByIndex(0);

        foreach ($sheets as $sheet) {
            $spec     = FieldSheetSpec::sheet($sheet);
            $headings = $spec['imported'] ? array_column(FieldSheetSpec::columns($sheet), 'label') : array_keys($spec['paper']);

            foreach (self::worksheetTitles($sheet) as $title) {
                $ws = $book->createSheet()->setTitle($title);
                $ws->setCellValue('A1', 'Kode sesi lapangan');
                $ws->setCellValue('C1', 'Tanggal');
                $ws->setCellValue('E1', 'Inisial pengisi');

                foreach ($headings as $i => $heading) {
                    $ws->setCellValue([$i + 1, 3], $heading);
                    $ws->getColumnDimensionByColumn($i + 1)->setWidth(max(12, min(40, mb_strlen($heading) + 2)));
                }
            }
        }

        $this->codeSheet($book, []);
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
    }

    /**
     * Blank L0 checklist (owner, 8 November 2026), read from its section in
     * 02 so the printed list never drifts from the document. Rows: the page
     * head, bold group headings, meeting sub-headings, and one ☐ per item.
     * Only for `gelita:lembar:templat` on a machine that has the docs.
     */
    public function writeL0Template(string $path, string $doc): void
    {
        $text = is_file($doc) ? (string) file_get_contents($doc) : '';

        if (preg_match('/^## L0 [^\n]*\n(.*?)^## /ms', $text, $m) !== 1) {
            throw new \RuntimeException('Bagian L0 tidak ditemukan di ' . $doc);
        }

        $book = new Spreadsheet();
        $ws   = $book->getActiveSheet()->setTitle('L0 Daftar periksa');
        $ws->getColumnDimension('A')->setWidth(5);
        $ws->getColumnDimension('B')->setWidth(110);
        $ws->setCellValue('A1', 'L0 Daftar periksa persiapan sesi');
        $ws->getStyle('A1')->getFont()->setBold(true);
        $r = 2;

        foreach (preg_split('/\R/', $m[1]) ?: [] as $line) {
            $plain = trim((string) preg_replace(['/\[([^\]]+)\]\([^)]*\)/', '/\*\*|`/'], ['$1', ''], $line));

            if (str_starts_with($plain, 'Kode sesi lapangan:')) {
                $ws->setCellValue([2, $r++], $plain);
            } elseif (preg_match('/^- \[ \] (.+)$/', $plain, $item) === 1) {
                $ws->setCellValue([1, $r], '☐');
                $ws->setCellValue([2, $r++], (str_starts_with($line, ' ') ? '    ' : '') . $item[1]);
            } elseif (preg_match('/^- ([^\[].*):$/', $plain, $sub) === 1 || (str_starts_with($line, '**') && ($sub = [1 => $plain]))) {
                $ws->setCellValue([2, ++$r], $sub[1]);
                $ws->getStyle('B' . $r++)->getFont()->setBold(true);
            }
        }

        $ws->getStyle('B1:B' . $r)->getAlignment()->setWrapText(true);
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
    }

    /** @param list<array<string, string>> $rows from FieldTestService::testSheetRows() */
    private function testStatusSheet(Spreadsheet $book, array $fieldSession, array $rows): void
    {
        $ws = $book->createSheet()->setTitle(mb_substr(FieldSheetSpec::title('l6_tes'), 0, 31));
        $ws->setCellValue('A1', 'Kode sesi lapangan');
        $ws->setCellValue('B1', (string) $fieldSession['code']);
        $ws->setCellValue('C1', 'Tanggal');
        $ws->setCellValueExplicit('D1', (string) $fieldSession['session_date'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $ws->setCellValue('A2', FieldSheetSpec::VERSION . ' · l6_tes (dari modul tes, tidak diunggah)');

        foreach (array_keys(FieldSheetSpec::sheet('l6_tes')['paper']) as $i => $heading) {
            $ws->setCellValue([$i + 1, 3], $heading);
            $ws->getColumnDimensionByColumn($i + 1)->setWidth(max(12, min(40, mb_strlen($heading) + 2)));

            foreach ($rows as $r => $row) {
                $ws->setCellValueExplicit([$i + 1, $r + 4], $row[$heading] ?? '', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }
        }

        $ws->freezePane('A4');
    }

    private function templateSheet(Spreadsheet $book, string $sheet, array $fieldSession, array $roster): void
    {
        foreach (self::worksheetTitles($sheet) as $title) {
            $this->templateWorksheet($book->createSheet()->setTitle($title), $sheet, $fieldSession, $roster);
        }
    }

    /**
     * Worksheet titles of one sheet in a template: L5 has one worksheet per
     * interviewed student, three per meeting (02 → L5, 29 §10.3; owner,
     * 8 November 2026); every other sheet has one.
     *
     * @return list<string>
     */
    public static function worksheetTitles(string $sheet): array
    {
        $title = mb_substr(FieldSheetSpec::title($sheet), 0, 29);

        return $sheet === 'l5_wawancara'
            ? array_map(static fn (int $n): string => $title . ' ' . $n, range(1, self::L5_WORKSHEETS))
            : [mb_substr(FieldSheetSpec::title($sheet), 0, 31)];
    }

    /** Row number as the panel shows it: rows of L5 worksheet 2 and 3 are stored from 2000 and 3000 (worksheetRow()). */
    public static function rowLabel(int|string $rowNo): string
    {
        $rowNo = (int) $rowNo;

        return $rowNo >= self::WORKSHEET_ROWS
            ? sprintf('%d (lembar %d)', $rowNo % self::WORKSHEET_ROWS, intdiv($rowNo, self::WORKSHEET_ROWS))
            : (string) $rowNo;
    }

    private function templateWorksheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, string $sheet, array $fieldSession, array $roster): void
    {
        $columns = FieldSheetSpec::columns($sheet);
        $codes   = array_keys($columns);

        // Page head as in 02: session code, date, initials of the one who fills it in
        $ws->setCellValue('A1', 'Kode sesi lapangan');
        $ws->setCellValue('B1', (string) $fieldSession['code']);
        $ws->setCellValue('C1', 'Tanggal');
        $ws->setCellValueExplicit('D1', (string) $fieldSession['session_date'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $ws->setCellValue('E1', 'Inisial pengisi');
        $ws->setCellValue('A2', FieldSheetSpec::VERSION . ' · ' . $sheet);

        foreach ($codes as $i => $code) {
            $ws->setCellValue([$i + 1, 3], $columns[$code]['label']);
            $ws->getColumnDimensionByColumn($i + 1)->setWidth(max(12, min(40, mb_strlen($columns[$code]['label']) + 2)));
            // Typed as text, so 08:01:10 stays a time and 05–08 a seat range
            $ws->getStyle(Coordinate::stringFromColumnIndex($i + 1) . '4:' . Coordinate::stringFromColumnIndex($i + 1) . '400')
                ->getNumberFormat()->setFormatCode('@');
            $this->validation($ws, $i + 1, $columns[$code]);
        }

        foreach ($this->prefill($sheet, $fieldSession, $roster) as $r => $values) {
            foreach ($values as $code => $value) {
                $c = array_search($code, $codes, true);

                if ($c !== false && $value !== null && $value !== '') {
                    $ws->setCellValueExplicit([$c + 1, $r + 4], (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                }
            }
        }

        $ws->freezePane('A4');
    }

    /**
     * Prefilled rows (29 §10.3): L1 the session itself; L2 one row per
     * student with code and booklet (P1 also row, seat, and pretest form).
     *
     * @return list<array<string, mixed>>
     */
    private function prefill(string $sheet, array $fieldSession, array $roster): array
    {
        if ($sheet === 'l1_kondisi') {
            $plan = service('fieldSessions')->planEntry($fieldSession);

            return [[
                'field_session_code' => $fieldSession['code'],
                'meeting_content'    => implode(', ', $plan['regions']) ?: ($plan['test'] ?? ''),
                'session_date'       => $fieldSession['session_date'],
                'start_time'         => substr((string) $fieldSession['opens_at'], 11, 8),
            ]];
        }

        if ($sheet === 'l6_sesi') {
            return [['field_session_code' => $fieldSession['code']]];
        }

        if ($sheet !== 'l2_kursi') {
            return [];
        }

        $first = (int) $fieldSession['meeting_no'] === 1 && (int) ($fieldSession['makeup_no'] ?? 0) === 0;
        $rows  = [];

        foreach ($roster as $student) {
            $rows[] = ['participant_code' => $student['participant_code'], 'booklet' => $student['booklet']] + ($first ? [
                'seat_no' => $student['seat_no'], 'row_no' => $student['row_no'], 'pre_form' => $student['pre_form'],
            ] : []);
        }

        return $rows;
    }

    /** The `Kode` sheet: allowed codes and the class group's participant codes. */
    private function codeSheet(Spreadsheet $book, array $participants): void
    {
        $ws    = $book->createSheet()->setTitle('Kode');
        $lists = FieldSheetSpec::CODES + ['participant' => $participants];
        $c     = 1;

        foreach ($lists as $name => $values) {
            $ws->setCellValue([$c, 1], $name);

            foreach (array_values($values) as $r => $value) {
                $ws->setCellValueExplicit([$c, $r + 2], (string) $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            }

            $c++;
        }
    }

    /** List validation from the `Kode` sheet for coded columns. */
    private function validation(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, int $column, array $spec): void
    {
        $list = match ($spec['type']) {
            'code'        => $spec['list'],
            'yesno'       => 'yesno',
            'participant' => 'participant',
            default       => null,
        };

        if ($list === null) {
            return;
        }

        $names  = [...array_keys(FieldSheetSpec::CODES), 'participant'];
        $letter = Coordinate::stringFromColumnIndex((int) array_search($list, $names, true) + 1);
        $rule   = (new DataValidation())->setType(DataValidation::TYPE_LIST)->setAllowBlank(true)
            ->setShowDropDown(true)->setFormula1("Kode!\${$letter}\$2:\${$letter}\$200");

        $ws->setDataValidation(Coordinate::stringFromColumnIndex($column) . '4:' . Coordinate::stringFromColumnIndex($column) . '400', $rule);
    }

    // ------------------------------------------------------------ preview

    /**
     * Reads an uploaded XLSX (the template) or CSV (one sheet, named after
     * its code, from the static site) and checks every row (29 §10.4).
     *
     * @param array<string, mixed> $fieldSession
     *
     * @return array{purpose: string, sha: string, sheets: array<string, array<string, mixed>>, errors: int, warnings: int, names: int}
     */
    public function preview(array $fieldSession, string $path, string $name, string $purpose): array
    {
        $purpose = $purpose === 'check' ? 'check' : 'main';
        $read    = $this->read($path, $name);

        if ($read === []) {
            throw new \DomainException(lang('Admin.fieldSheetNoSheets'));
        }

        $context = $this->context($fieldSession, $read);
        $sheets  = [];
        $totals  = ['errors' => 0, 'warnings' => 0, 'names' => 0];

        foreach ($read as $sheet => $data) {
            $result = $this->check($sheet, $data, $context);

            if ($purpose === 'check') {
                $result['compare'] = $this->compare($fieldSession, $sheet, $result['rows']);
            }

            $sheets[$sheet] = $result;
            $totals['errors']   += count($result['errors']);
            $totals['warnings'] += count($result['warnings']) + count($result['names']);
            $totals['names']    += count($result['names']);
        }

        return ['purpose' => $purpose, 'sha' => (string) hash_file('sha256', $path), 'sheets' => $sheets] + $totals;
    }

    /**
     * Rows of every known sheet in the file: heading → value, with the file's
     * session code when it has one.
     *
     * @return array<string, array{session: ?string, rows: array<int, array<string, string>>}>
     */
    public function read(string $path, string $name): array
    {
        $isCsv = str_ends_with(strtolower($name), '.csv');

        try {
            $reader = $isCsv ? new \PhpOffice\PhpSpreadsheet\Reader\Csv() : IOFactory::createReaderForFile($path);

            if ($isCsv) {
                $reader->setInputEncoding('UTF-8')->setDelimiter(str_contains((string) file_get_contents($path, false, null, 0, 2048), ';') ? ';' : ',');
            }

            $book = $reader->load($path);
        } catch (\Throwable) {
            throw new \DomainException(lang('Admin.fieldSheetFileInvalid'));
        }

        $out = [];

        foreach ($book->getAllSheets() as $ws) {
            $sheet = $isCsv ? $this->sheetFromName($name) : $this->sheetFromTitle($ws->getTitle());

            if ($sheet === null || ! in_array($sheet, FieldSheetSpec::imported(), true)) {
                continue;
            }

            $cells = $ws->toArray(null, false, true, false);
            $data  = $this->rowsOf($sheet, array_map(fn (array $r): array => array_map(fn ($v): string => $this->cellText($v), $r), $cells));

            // L5 worksheet 2 and 3 (one per student): rows kept apart as 2000 + row, 3000 + row
            $copy = $sheet === 'l5_wawancara' && preg_match('/\s(\d)$/', $ws->getTitle(), $m) === 1 ? (int) $m[1] : 1;

            if ($copy > 1) {
                $data['rows'] = array_combine(array_map(static fn (int $n): int => $copy * self::WORKSHEET_ROWS + $n, array_keys($data['rows'])), $data['rows']) ?: [];
            }

            $out[$sheet] = isset($out[$sheet])
                ? ['session' => $out[$sheet]['session'] ?? $data['session'], 'rows' => $out[$sheet]['rows'] + $data['rows']]
                : $data;
        }

        $book->disconnectWorksheets();

        return $out;
    }

    /** @param array<string, mixed> $fieldSession */
    private function context(array $fieldSession, array $read): array
    {
        $roster = $this->roster($fieldSession);
        $seats  = [];

        // Seats of this session's L2: from the upload, else the counting L2 import
        foreach ($read['l2_kursi']['rows'] ?? [] as $row) {
            $seat = $this->seatNumber($this->cell($row, 'l2_kursi', 'seat_no'));
            if ($seat !== null) {
                $seats[$seat] = true;
            }
        }

        if ($seats === [] && ($l2 = model(FieldSheetImportModel::class)->current((int) $fieldSession['id'], 'l2_kursi')) !== null) {
            foreach (model(FieldSheetRowModel::class)->forImport((int) $l2['id']) as $row) {
                if ($row['seat_no'] !== null) {
                    $seats[(int) $row['seat_no']] = true;
                }
            }
        }

        return [
            'session'  => $fieldSession,
            'roster'   => array_column($roster, null, 'participant_code'),
            'names'    => self::nameWords($roster),
            'seats'    => $seats,
            'late'     => ($closed = $this->closedAt($fieldSession)) !== null && time() - strtotime($closed) > self::LATE_HOURS * 3600,
        ];
    }

    /**
     * Checks one sheet. Errors hold the save back; warnings do not; name
     * warnings must be confirmed (K13).
     *
     * @param array{session: ?string, rows: array<int, array<string, string>>} $data
     *
     * @return array{rows: list<array<string, mixed>>, errors: list<array{0: int, 1: string, 2: string}>, warnings: list<string>, names: list<array{0: int, 1: string}>}
     */
    private function check(string $sheet, array $data, array $context): array
    {
        $session  = $context['session'];
        $columns  = FieldSheetSpec::columns($sheet);
        $errors   = [];
        $warnings = [];
        $names    = [];
        $rows     = [];

        if ($data['session'] !== null && $data['session'] !== (string) $session['code']) {
            $errors[] = [0, 'field_session_code', lang('Admin.fieldSheetWrongSession', [$data['session']])];
        }

        foreach ($data['rows'] as $rowNo => $raw) {
            $values = [];

            foreach ($columns as $code => $spec) {
                $value = $raw[$code] ?? '';
                $value = in_array($value, ['—', '-', '–'], true) ? '' : $value;

                if ($value === '') {
                    $values[$code] = null;

                    continue;
                }

                [$value, $error] = $this->value($code, $spec, $value, $context);

                if ($error !== null) {
                    $errors[] = [$rowNo, $spec['label'], $error];
                }

                if ($spec['type'] === 'free' && $this->hasName((string) $value, $context['names'])) {
                    $names[] = [$rowNo, $spec['label']];
                }

                $values[$code] = $value;
            }

            if ($values === array_fill_keys(array_keys($columns), null)) {
                continue;   // an empty row of the template
            }

            foreach ([['start_time', 'end_time']] as [$from, $to]) {
                if (($values[$from] ?? null) !== null && ($values[$to] ?? null) !== null && $values[$to] < $values[$from]) {
                    $errors[] = [$rowNo, $columns[$to]['label'], lang('Admin.fieldSheetErrTimeOrder')];
                }
            }

            $row = ['row_no' => $rowNo, 'values' => $values] + $this->keys($sheet, $values, $context);

            if ($sheet === 'l5_wawancara' && $values['manual_code'] !== null) {
                [$row, $error, $warning] = $this->manualCode($row, $context);

                if ($error !== null) {
                    $errors[] = [$rowNo, $columns['manual_code']['label'], $error];
                }

                if ($warning !== null) {
                    $warnings[] = $warning;
                }
            }

            if ($sheet === 'l2_kursi') {
                $warnings = [...$warnings, ...$this->seatWarnings($row, $context)];
            }

            $rows[] = $row;
        }

        if ($sheet === 'l2_kursi') {
            $listed = array_filter(array_column(array_column($rows, 'values'), 'participant_code'));

            foreach (array_diff(array_keys($context['roster']), $listed) as $missing) {
                $warnings[] = lang('Admin.fieldSheetWarnMissing', [$missing]);
            }
        }

        if ($context['late']) {
            $warnings[] = lang('Admin.fieldSheetLate');
        }

        return ['rows' => $rows, 'errors' => $errors, 'warnings' => $warnings, 'names' => $names];
    }

    /**
     * One typed value, normalized; error message or null.
     *
     * @return array{0: mixed, 1: ?string}
     */
    private function value(string $code, array $spec, string $value, array $context): array
    {
        switch ($spec['type']) {
            case 'session':
                return [$value, $value === (string) $context['session']['code'] ? null : lang('Admin.fieldSheetWrongSession', [$value])];

            case 'time':
                if (preg_match('/^(\d{1,2}):(\d{2}):(\d{2})$/', $value, $m) === 1 && (int) $m[1] < 24 && (int) $m[2] < 60 && (int) $m[3] < 60) {
                    return [sprintf('%02d:%02d:%02d', $m[1], $m[2], $m[3]), null];
                }

                return [$value, lang('Admin.fieldSheetErrTime')];

            case 'date':
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 && strtotime($value) !== false) {
                    return [$value, null];
                }

                return [$value, lang('Admin.fieldSheetErrDate')];

            case 'int':
            case 'try':
                if (! ctype_digit($value)) {
                    return [$value, lang($spec['type'] === 'try' ? 'Admin.fieldSheetErrTry' : 'Admin.fieldSheetErrInt')];
                }

                return $spec['type'] === 'try' && ((int) $value < 1 || (int) $value > 3) ? [$value, lang('Admin.fieldSheetErrTry')] : [(int) $value, null];

            case 'code':
                $allowed = FieldSheetSpec::CODES[$spec['list']];
                $match   = array_values(array_filter($allowed, static fn (string $c): bool => strcasecmp($c, $value) === 0));

                return $match === [] ? [$value, lang('Admin.fieldSheetErrCode', [implode(', ', $allowed)])] : [$match[0], null];

            case 'yesno':
                $word = strtolower(trim(explode(',', $value)[0]));

                return in_array($word, ['ya', 'tidak'], true) ? [$word === 'ya' ? 1 : 0, null] : [$value, lang('Admin.fieldSheetErrYesNo')];

            case 'participant':
                if (strcasecmp($value, '(lihat L2)') === 0) {
                    return [null, null];
                }

                $value = strtoupper($value);

                return [$value, isset($context['roster'][$value]) ? null : lang('Admin.fieldSheetCodeOtherClass', [$value])];

            case 'seat':
                $seat = $this->seatNumber($value);

                if ($seat === null) {
                    return [$value, lang('Admin.fieldSheetErrInt')];
                }

                return [$seat, $context['seats'] === [] || isset($context['seats'][$seat]) ? null : lang('Admin.fieldSheetErrSeat', [$value])];

            case 'seats':
                return [$value, $this->seatsError($value, $context)];

            case 'item':
                $exists = db_connect()->table('challenge_items')->where('item_key', $value)->countAllResults() > 0;

                return [$value, $exists ? null : lang('Admin.fieldSheetErrItem')];

            default:
                return [$value, null];
        }
    }

    /** "semua", a seat, a range 05–08, or a list 03, 05 (29 §10.4). */
    private function seatsError(string $value, array $context): ?string
    {
        if (strcasecmp($value, 'semua') === 0) {
            return null;
        }

        $seats = [];

        foreach (preg_split('/\s*,\s*/', $value) as $part) {
            if (preg_match('/^(\d{1,3})\s*[–—-]\s*(\d{1,3})$/u', $part, $m) === 1 && (int) $m[1] <= (int) $m[2]) {
                $seats = [...$seats, ...range((int) $m[1], (int) $m[2])];
            } elseif (ctype_digit($part)) {
                $seats[] = (int) $part;
            } else {
                return lang('Admin.fieldSheetErrSeats');
            }
        }

        foreach ($seats as $seat) {
            if ($context['seats'] !== [] && ! isset($context['seats'][$seat])) {
                return lang('Admin.fieldSheetErrSeat', [(string) $seat]);
            }
        }

        return null;
    }

    /** Join keys as columns (29 §10.4): participant, seat, time from the session date. */
    private function keys(string $sheet, array $values, array $context): array
    {
        $code  = $values['participant_code'] ?? null;
        $date  = (string) ($values['session_date'] ?? $context['session']['session_date']);
        $start = null;

        foreach (self::START_COLUMNS as $column) {
            $start ??= $values[$column] ?? null;
        }

        $end  = $values['end_time'] ?? null;
        $seat = $values['seat_no'] ?? ($sheet === 'l3_bantuan' && ctype_digit((string) ($values['seats'] ?? '')) ? (int) $values['seats'] : null);

        return [
            'participant_id' => $code === null ? null : ($context['roster'][$code]['id'] ?? null),
            'seat_no'        => is_int($seat) && $seat >= 0 && $seat <= 255 ? $seat : null,
            'started_at'     => $start === null ? null : $date . ' ' . $start,
            'ended_at'       => $end === null || ! is_string($end) || preg_match('/^\d{2}:\d{2}:\d{2}$/', $end) !== 1 ? null : $date . ' ' . $end,
        ];
    }

    /**
     * L5 `manual_code` (29 §10.4, §19 no. 14–17): one of the item's bank codes
     * or the `*-LAIN` code of its dimension, for a wrong try of the student's
     * first attempt. The same code as the game's is a warning.
     *
     * @return array{0: array<string, mixed>, 1: ?string, 2: ?string}
     */
    private function manualCode(array $row, array $context): array
    {
        $values = $row['values'];
        $code   = strtoupper((string) $values['manual_code']);
        $row['values']['manual_code'] = $code;

        if ($values['item_key'] === null || $row['participant_id'] === null) {
            return [$row, lang('Admin.fieldSheetErrItem'), null];
        }

        $db   = db_connect();
        $item = $db->table('challenge_items')->select('id, dimension, error_code')->where('item_key', $values['item_key'])->get()->getRowArray();

        if ($item === null) {
            return [$row, lang('Admin.fieldSheetErrItem'), null];
        }

        $other   = ['D1' => 'INF-LAIN', 'D2' => 'DIG-LAIN', 'D3' => 'BUD-LAIN'][(string) $item['dimension']] ?? null;
        $allowed = array_values(array_unique(array_filter([
            $item['error_code'],
            ...array_column($db->table('item_error_codes')->select('error_code')->where('challenge_item_id', $item['id'])->get()->getResultArray(), 'error_code'),
        ])));

        if (! in_array($code, $allowed, true) && $code !== $other) {
            return [$row, lang('Admin.fieldSheetErrManual', [implode(', ', $allowed) ?: '—', $other ?? implode(', ', ErrorCodes::MANUAL)]), null];
        }

        $tryNo = (int) ($values['try_no'] ?? 1) ?: 1;
        $try   = $this->firstAttemptTry((int) $row['participant_id'], (int) $item['id'], $tryNo);

        if ($try === null) {
            return [$row, lang('Admin.fieldSheetErrTryMissing', [$tryNo, $values['item_key']]), null];
        }

        if ((int) $try['is_correct'] === 1) {
            return [$row, lang('Admin.fieldSheetErrTryCorrect'), null];
        }

        $row['values']['_try_id'] = (int) $try['id'];

        return [$row, null, $try['error_code'] === $code ? lang('Admin.fieldSheetWarnSameCode', [self::rowLabel($row['row_no'])]) : null];
    }

    /** The try of an item in the student's first attempt of its challenge in this study. */
    private function firstAttemptTry(int $participantId, int $itemId, int $tryNo): ?array
    {
        $db      = db_connect();
        $attempt = $db->table('challenge_attempts')
            ->select('challenge_attempts.id')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->join('challenge_items', 'challenge_items.challenge_node_id = challenge_attempts.challenge_node_id')
            ->where('game_sessions.participant_id', $participantId)
            ->where('challenge_items.id', $itemId)
            ->orderBy('challenge_attempts.started_at', 'ASC')->orderBy('challenge_attempts.id', 'ASC')
            ->get(1)->getRowArray();

        if ($attempt === null) {
            return null;
        }

        return $db->table('item_tries')->select('id, is_correct, error_code')
            ->where('challenge_attempt_id', $attempt['id'])->where('challenge_item_id', $itemId)->where('try_no', $tryNo)
            ->get()->getRowArray();
    }

    /** L2 booklet or form different from the panel: the panel counts (warning). */
    private function seatWarnings(array $row, array $context): array
    {
        $student = $context['roster'][$row['values']['participant_code'] ?? ''] ?? null;

        if ($student === null) {
            return [];
        }

        $out = [];

        foreach (['booklet', 'pre_form'] as $column) {
            $typed = $row['values'][$column] ?? null;

            if ($typed !== null && $student[$column] !== null && $typed !== $student[$column]) {
                $out[] = lang('Admin.fieldSheetWarnBooklet', [$row['row_no'], $typed, $student[$column]]);
            }
        }

        return $out;
    }

    // ------------------------------------------------------------ second typing

    /**
     * Matches second-typing rows with the main import by the sheet's row key
     * (29 §10.5): rows checked, rows in the main import, and differing cells.
     *
     * @param list<array<string, mixed>> $rows
     *
     * @return array{checked: int, total: int, diffs: list<array{0: int, 1: string, 2: string, 3: string}>, missing_main: bool}
     */
    public function compare(array $fieldSession, string $sheet, array $rows): array
    {
        $main = model(FieldSheetImportModel::class)->current((int) $fieldSession['id'], $sheet);

        if ($main === null) {
            return ['checked' => 0, 'total' => 0, 'diffs' => [], 'missing_main' => true];
        }

        $key    = FieldSheetSpec::key($sheet);
        $byKey  = [];
        $first  = model(FieldSheetRowModel::class)->forImport((int) $main['id']);
        $labels = FieldSheetSpec::columns($sheet);

        foreach ($first as $stored) {
            $values = is_array($stored['values_json']) ? $stored['values_json'] : (json_decode((string) $stored['values_json'], true) ?: []);
            $byKey[$this->rowKey($key, $values)] = $values;
        }

        $checked = 0;
        $diffs   = [];

        foreach ($rows as $row) {
            $theirs = $byKey[$this->rowKey($key, $row['values'])] ?? null;

            if ($theirs === null) {
                continue;
            }

            $checked++;

            foreach ($labels as $code => $spec) {
                $a = (string) ($theirs[$code] ?? '');
                $b = (string) ($row['values'][$code] ?? '');

                if ($a !== $b) {
                    $diffs[] = [$row['row_no'], $spec['label'], $a, $b];
                }
            }
        }

        return ['checked' => $checked, 'total' => count($first), 'diffs' => $diffs, 'missing_main' => false];
    }

    /** @param list<string> $key */
    private function rowKey(array $key, array $values): string
    {
        return implode('|', array_map(static fn (string $k): string => (string) ($values[$k] ?? ''), $key ?: ['field_session_code']));
    }

    // ------------------------------------------------------------ save

    /**
     * Saves a preview: one import per sheet with its rows; a main import
     * supersedes the previous one and writes L5 manual codes (29 §10.4).
     *
     * @param array<string, mixed> $fieldSession
     * @param array<string, mixed> $preview      preview()
     *
     * @return list<array{sheet: string, import_id: int, rows: int, replaced: bool}>
     */
    public function save(array $fieldSession, array $preview, ?int $staffId, bool $namesConfirmed): array
    {
        if ((int) $preview['errors'] > 0) {
            throw new \DomainException(lang('Admin.fieldSheetHasErrors'));
        }

        if ((int) $preview['names'] > 0 && ! $namesConfirmed) {
            throw new \DomainException(lang('Admin.fieldSheetConfirmNames'));
        }

        $db      = $this->db();
        $purpose = (string) $preview['purpose'];
        $saved   = [];

        $db->transBegin();

        try {
            foreach ($preview['sheets'] as $sheet => $result) {
                $imports = model(FieldSheetImportModel::class);
                $id      = (int) $imports->insert([
                    'field_session_id' => (int) $fieldSession['id'],
                    'sheet'            => $sheet,
                    'spec_version'     => FieldSheetSpec::VERSION,
                    'purpose'          => $purpose,
                    'file_sha256'      => (string) $preview['sha'],
                    'row_count'        => count($result['rows']),
                    'warning_count'    => count($result['warnings']) + count($result['names']),
                    'names_confirmed'  => $namesConfirmed ? 1 : 0,
                    'uploaded_by'      => $staffId,
                    'created_at'       => date('Y-m-d H:i:s'),
                ], true);

                $rows = array_map(static fn (array $row): array => [
                    'import_id'      => $id,
                    'row_no'         => (int) $row['row_no'],
                    'participant_id' => $row['participant_id'],
                    'seat_no'        => $row['seat_no'],
                    'started_at'     => $row['started_at'],
                    'ended_at'       => $row['ended_at'],
                    'values_json'    => $row['values'],
                ], $result['rows']);

                if ($rows !== []) {
                    model(FieldSheetRowModel::class)->insertBatch($rows);
                }

                $replaced = false;

                if ($purpose === 'main') {
                    $replaced = $this->supersede((int) $fieldSession['id'], $sheet, $id);

                    if ($sheet === 'l5_wawancara') {
                        $this->writeManualCodes($result['rows']);
                    }
                }

                model(AuditLogModel::class)->record('field_sheet_import', [
                    'staff_user_id' => $staffId,
                    'target_type'   => 'field_sheet_import',
                    'target_id'     => (string) $id,
                    'metadata'      => [
                        'field_session_code' => $fieldSession['code'], 'sheet' => $sheet, 'purpose' => $purpose,
                        'row_count' => count($rows), 'warning_count' => count($result['warnings']) + count($result['names']),
                    ],
                ]);

                $saved[] = ['sheet' => $sheet, 'import_id' => $id, 'rows' => count($rows), 'replaced' => $replaced];
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return $saved;
    }

    /**
     * Older main imports of the sheet become history: one conditional UPDATE
     * (`id < new AND superseded_at IS NULL`), so the newest upload counts even
     * when two arrive at once. Their L5 manual codes are cleared first.
     */
    private function supersede(int $fieldSessionId, string $sheet, int $newId): bool
    {
        $db  = $this->db();
        $old = array_map('intval', array_column($db->table('field_sheet_imports')->select('id')
            ->where('field_session_id', $fieldSessionId)->where('sheet', $sheet)->where('purpose', 'main')
            ->where('superseded_at', null)->where('id <', $newId)->get()->getResultArray(), 'id'));

        if ($old === []) {
            return false;
        }

        if ($sheet === 'l5_wawancara') {
            foreach (model(FieldSheetRowModel::class)->whereIn('import_id', $old)->findAll() as $row) {
                $values = is_array($row['values_json']) ? $row['values_json'] : (json_decode((string) $row['values_json'], true) ?: []);

                if (isset($values['_try_id'], $values['manual_code'])) {
                    $db->table('item_tries')->where('id', (int) $values['_try_id'])->where('manual_code', $values['manual_code'])->update(['manual_code' => null]);
                }
            }
        }

        $db->table('field_sheet_imports')->whereIn('id', $old)->where('superseded_at', null)
            ->update(['superseded_at' => date('Y-m-d H:i:s'), 'superseded_by' => $newId]);

        return $db->affectedRows() > 0;
    }

    /** @param list<array<string, mixed>> $rows */
    private function writeManualCodes(array $rows): void
    {
        foreach ($rows as $row) {
            if (isset($row['values']['_try_id'], $row['values']['manual_code'])) {
                $this->db()->table('item_tries')->where('id', (int) $row['values']['_try_id'])->update(['manual_code' => $row['values']['manual_code']]);
            }
        }
    }

    // ------------------------------------------------------------ helpers

    /**
     * Students of the session (the class group, or a makeup's members), by
     * code, with their seat of the study.
     *
     * @return list<array{id: int, participant_code: string, booklet: ?string, pre_form: ?string, seat_no: ?int, row_no: ?int, display_name: ?string, username: ?string}>
     */
    private function roster(array $fieldSession): array
    {
        $db      = $this->db();
        $members = (int) ($fieldSession['makeup_no'] ?? 0) > 0 ? model(FieldSessionMemberModel::class)->memberIds((int) $fieldSession['id']) : null;
        $builder = $db->table('participants p')
            ->select('p.id, p.participant_code, p.display_name, p.username, s.booklet, s.pre_form, s.seat_no, s.row_no')
            ->join('participant_seats s', 's.participant_id = p.id AND s.study_id = ' . (int) $fieldSession['study_id'], 'left')
            ->where('p.deleted_at', null)
            ->orderBy('p.participant_code', 'ASC');

        if ($members !== null) {
            $builder->whereIn('p.id', $members ?: [0]);
        } else {
            $builder->where('p.school_id', (int) $fieldSession['school_id'])->where('p.rombel', (string) $fieldSession['rombel']);
        }

        return array_map(static fn (array $r): array => [
            'id' => (int) $r['id'], 'participant_code' => (string) $r['participant_code'],
            'booklet' => $r['booklet'], 'pre_form' => $r['pre_form'],
            'seat_no' => $r['seat_no'] === null ? null : (int) $r['seat_no'], 'row_no' => $r['row_no'] === null ? null : (int) $r['row_no'],
            'display_name' => $r['display_name'], 'username' => $r['username'],
        ], $builder->get()->getResultArray());
    }

    /**
     * Lower-case words of the students' names and usernames (≥ 3 letters, 29 K13);
     * also used by StudyClosureService to mask feedback (30 §10.2).
     *
     * @param iterable<array{display_name: mixed, username: mixed}> $roster
     *
     * @return array<string, true>
     */
    public static function nameWords(iterable $roster): array
    {
        $words = [];

        foreach ($roster as $student) {
            foreach ([(string) $student['display_name'], (string) $student['username']] as $name) {
                foreach (preg_split('/[^\p{L}]+/u', mb_strtolower($name), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                    if (mb_strlen($word) >= self::NAME_MIN) {
                        $words[$word] = true;
                    }
                }
            }
        }

        return $words;
    }

    private function hasName(string $text, array $names): bool
    {
        foreach (preg_split('/[^\p{L}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) as $word) {
            if (isset($names[$word])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sheet rows by spec code, from a grid: the heading row is the first row
     * whose cells name at least two columns; a `fields` sheet may also be
     * typed vertically (heading in column A, value beside it). The page head
     * ("Kode sesi lapangan", code, "Tanggal", …) is never the heading row,
     * even where those words are also column headings (L1 Kondisi sesi).
     *
     * @param list<list<string>> $grid
     *
     * @return array{session: ?string, rows: array<int, array<string, string>>}
     */
    private function rowsOf(string $sheet, array $grid): array
    {
        $session = null;
        $headRow = null;

        foreach ($grid as $r => $cells) {
            $value = trim($cells[1] ?? '');

            // A column heading beside it means the table's own heading row, not the page head
            if (strcasecmp(trim($cells[0] ?? ''), 'Kode sesi lapangan') === 0 && $value !== '' && ! $this->isHeading($sheet, $value)) {
                $session = $value;
                $headRow = $r;

                break;
            }
        }

        foreach ($grid as $r => $cells) {
            if ($r === $headRow) {
                continue;
            }

            $map = [];

            foreach ($cells as $c => $heading) {
                if ($heading === '' || FieldSheetSpec::isDropped($sheet, $heading)) {
                    continue;
                }

                $codes = ($code = FieldSheetSpec::codeFor($sheet, $heading)) !== null ? [$code] : (FieldSheetSpec::paper($sheet)[$this->paperHeading($sheet, $heading)] ?? []);

                if ($codes !== []) {
                    $map[$c] = $codes;
                }
            }

            if (count($map) < 2) {
                continue;
            }

            $rows = [];

            foreach (array_slice($grid, $r + 1, null, true) as $n => $line) {
                $row = [];

                foreach ($map as $c => $codes) {
                    $row = $this->split($codes, trim($line[$c] ?? '')) + $row;
                }

                if (implode('', $row) !== '') {
                    $rows[$n + 1] = $row;
                }
            }

            return ['session' => $session, 'rows' => $rows];
        }

        // Vertical: heading in the first column, value in the last filled one
        $row = [];

        foreach ($grid as $cells) {
            $heading = trim($cells[0] ?? '');
            $codes   = ($code = FieldSheetSpec::codeFor($sheet, $heading)) !== null ? [$code] : (FieldSheetSpec::paper($sheet)[$this->paperHeading($sheet, $heading)] ?? []);

            if ($codes !== [] && ! FieldSheetSpec::isDropped($sheet, $heading)) {
                $value = trim((string) (count($cells) >= 4 ? $cells[3] : ($cells[1] ?? '')));
                $row   = $this->split($codes, $value) + $row;
            }
        }

        return ['session' => $session, 'rows' => implode('', $row) === '' ? [] : [1 => $row]];
    }

    private function isHeading(string $sheet, string $text): bool
    {
        return FieldSheetSpec::codeFor($sheet, $text) !== null || $this->paperHeading($sheet, $text) !== null;
    }

    private function paperHeading(string $sheet, string $heading): ?string
    {
        foreach (array_keys(FieldSheetSpec::paper($sheet)) as $paper) {
            if (mb_strtolower(trim($paper)) === mb_strtolower(trim($heading))) {
                return $paper;
            }
        }

        return null;
    }

    /**
     * Combined paper entries split into their columns (29 §10.7): "24 dari 26",
     * "28 / 26 / 26", "90 / 84", "Ya, S03-A16", "08:40:00 ke P15 (baterai
     * habis)", "2027-11-15; 07:32:15–09:28:40".
     *
     * @param list<string> $codes
     *
     * @return array<string, string>
     */
    private function split(array $codes, string $value): array
    {
        if (count($codes) === 1) {
            return [$codes[0] => $value];
        }

        $parts = match ($codes[0]) {
            'shared_device' => preg_match('/^(ya|tidak)\s*,?\s*(.*)$/iu', $value, $m) === 1 ? [$m[1], $m[2]] : [$value, ''],
            'switch_time'   => preg_match('/^(\d{1,2}:\d{2}:\d{2})\s+ke\s+(\S+)\s*(.*)$/iu', $value, $m) === 1 ? [$m[1], $m[2], trim($m[3], ' ()')] : ['', '', $value],
            'session_date'  => preg_match('/^(\S+)\s*;\s*(\d{1,2}:\d{2}:\d{2})\s*[–—-]\s*(\d{1,2}:\d{2}:\d{2})$/u', $value, $m) === 1 ? [$m[1], $m[2], $m[3]] : [$value, '', ''],
            default         => preg_split('/\s*(?:dari|\/)\s*/u', $value),
        };

        $out = [];

        foreach ($codes as $i => $code) {
            $out[$code] = trim((string) ($parts[$i] ?? ''));
        }

        // "Pindah perangkat" keeps the reason in the note
        if ($codes[0] === 'switch_time' && ($parts[2] ?? '') !== '') {
            $out['note'] = (string) $parts[2];
        }

        return array_intersect_key($out, array_flip([...$codes, 'note']));
    }

    private function cell(array $row, string $sheet, string $code): string
    {
        return (string) ($row[$code] ?? '');
    }

    private function seatNumber(string $value): ?int
    {
        return ctype_digit(trim($value)) ? (int) trim($value) : null;
    }

    /** Excel numbers that are times or dates back to text. */
    private function cellText(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_float($value) || is_int($value)) {
            if ($value > 0 && $value < 1) {
                return ExcelDate::excelToDateTimeObject($value)->format('H:i:s');
            }

            return (string) $value;
        }

        return trim((string) $value);
    }

    private function sheetFromTitle(string $title): ?string
    {
        $title = (string) preg_replace('/^(' . preg_quote(mb_substr(FieldSheetSpec::title('l5_wawancara'), 0, 29), '/') . ') [1-' . self::L5_WORKSHEETS . ']$/i', '$1', $title);

        foreach (FieldSheetSpec::imported() as $sheet) {
            if (strcasecmp($title, $sheet) === 0 || strcasecmp($title, mb_substr(FieldSheetSpec::title($sheet), 0, 31)) === 0) {
                return $sheet;
            }
        }

        return null;
    }

    private function sheetFromName(string $name): ?string
    {
        $name = strtolower(basename($name));

        foreach (FieldSheetSpec::imported() as $sheet) {
            if (str_contains($name, $sheet)) {
                return $sheet;
            }
        }

        return null;
    }

    private function closedAt(array $fieldSession): ?string
    {
        $closed = $fieldSession['closed_at'] ?? null;

        if ($closed !== null && $closed !== '') {
            return (string) $closed;
        }

        return strtotime((string) $fieldSession['closes_at']) < time() ? (string) $fieldSession['closes_at'] : null;
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
