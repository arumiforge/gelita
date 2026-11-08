<?php

use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\FieldSheetWorld;

/**
 * The second typing (research revision 10, part 3: 29 §10.5): rows match the
 * main import by the sheet's row key, differences are listed, and a check
 * import never reaches the download.
 *
 * @internal
 */
final class FieldSheetCheckTest extends CIUnitTestCase
{
    use FieldSheetWorld;

    public function testSecondTypingIsComparedAndNeverDownloaded(): void
    {
        $fieldSession = $this->createFieldSheetWorld(2);
        $head         = ['participant_code', 'seat_no', 'row_no', 'seat_in_row', 'device_no', 'booklet', 'entry_time', 'note'];
        $main         = $this->csv('l2_kursi.csv', [
            $head,
            ['S03-A01', '1', '1', '1', 'P01', 'B1', '07:40:40', '—'],
            ['S03-A02', '2', '1', '2', 'P02', 'B2', '07:41:12', '—'],
        ]);
        $this->sheets()->save($fieldSession, $this->sheets()->preview($fieldSession, $main, 'l2_kursi.csv', 'main'), 2, false);

        // Part of the rows, typed again by someone else, one cell different
        $check   = $this->csv('l2_kursi.csv', [$head, ['S03-A02', '2', '1', '2', 'P12', 'B2', '07:41:12', '—']]);
        $preview = $this->sheets()->preview($fieldSession, $check, 'l2_kursi.csv', 'check');
        $compare = $preview['sheets']['l2_kursi']['compare'];

        $this->assertSame(['checked' => 1, 'total' => 2, 'missing_main' => false], array_diff_key($compare, ['diffs' => 1]));
        $this->assertSame([[2, 'Perangkat', 'P02', 'P12']], $compare['diffs']);

        $this->sheets()->save($fieldSession, $preview, 3, false);
        $this->assertSame(['main', 'check'], array_column($this->conn->table('field_sheet_imports')->orderBy('id')->get()->getResultArray(), 'purpose'));
        $this->assertSame('checked', $this->sheets()->status($fieldSession)[2]['state']);
        $this->assertSame(50, $this->sheets()->status($fieldSession)[2]['share']);

        $rows = iterator_to_array(service('rawDataService')->rows('l2_kursi', ['study_id' => 1], StaffScope::head()), false);
        $this->assertSame(['P01', 'P02'], array_column($rows, 'device_no'), 'only the main typing is downloaded');
    }
}
