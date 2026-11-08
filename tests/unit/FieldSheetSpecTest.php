<?php

use App\Libraries\FieldSheetSpec;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The field-sheet spec follows the tables tagged `<!-- lembar: … -->` in 02
 * (research revision 10, part 3: 29 §10.1, K11): if 02 changes without the
 * spec, or the spec without 02, this fails.
 *
 * @internal
 */
final class FieldSheetSpecTest extends CIUnitTestCase
{
    public function testHeadingsMatchTheTaggedTablesOf02(): void
    {
        $doc     = (string) file_get_contents(ROOTPATH . FieldSheetSpec::DOC);
        $markers = [];

        preg_match_all('/<!-- lembar: ([A-Za-z0-9-]+) -->/', $doc, $found);

        foreach (FieldSheetSpec::sheets() as $sheet) {
            $spec      = FieldSheetSpec::sheet($sheet);
            $markers[] = $spec['marker'];

            $this->assertSame(array_keys($spec['paper']), $this->headings($doc, $spec['marker'], $spec['layout']), $sheet);
        }

        $this->assertEqualsCanonicalizing($found[1], $markers, 'one spec sheet per tagged table');
    }

    public function testImportedSheetsAndTheirColumns(): void
    {
        $this->assertSame(['l1_kondisi', 'l1_penyimpangan', 'l2_kursi', 'l3_bantuan', 'l4_bromp', 'l5_wawancara', 'l6_sesi'], FieldSheetSpec::imported());
        $this->assertFalse(FieldSheetSpec::sheet('l7_izin')['imported'], 'L7 never reaches the server');

        foreach (FieldSheetSpec::imported() as $sheet) {
            $paper   = array_merge(...array_values(FieldSheetSpec::paper($sheet)));
            $typed   = [...$paper, ...(FieldSheetSpec::sheet($sheet)['extra'] ?? [])];
            $columns = array_keys(FieldSheetSpec::columns($sheet));

            $this->assertEqualsCanonicalizing($typed, $columns, $sheet . ': every typed code has a column, and the reverse');

            foreach (FieldSheetSpec::key($sheet) as $key) {
                $this->assertContains($key, $columns, $sheet);
            }
        }

        // 29 §10.7: L5 has Percobaan (try_no) and Kode manual (manual_code), not the quote
        $this->assertSame(['try_no'], FieldSheetSpec::paper('l5_wawancara')['Percobaan']);
        $this->assertSame(['manual_code'], FieldSheetSpec::paper('l5_wawancara')['Kode manual']);
        $this->assertTrue(FieldSheetSpec::isDropped('l5_wawancara', 'Kutipan ucapan siswa'));
        $this->assertTrue(FieldSheetSpec::isDropped('l1_kondisi', 'token'));
        $this->assertSame('headset_count', FieldSheetSpec::codeFor('l1_kondisi', 'Headset dipakai'));
        $this->assertSame('seat_no', FieldSheetSpec::codeFor('l2_kursi', 'kursi'));
        $this->assertSame('seat_no', FieldSheetSpec::codeFor('l2_kursi', 'seat_no'));
        $this->assertNull(FieldSheetSpec::codeFor('l1_kondisi', 'Headset'), 'a heading of two columns is split by the import');
    }

    /** @return list<string> */
    private function headings(string $doc, string $marker, string $layout): array
    {
        $start = strpos($doc, '<!-- lembar: ' . $marker . ' -->');
        $this->assertNotFalse($start, $marker);

        $lines = [];

        foreach (array_slice(explode("\n", substr($doc, $start)), 1) as $line) {
            if (! str_starts_with(trim($line), '|')) {
                break;
            }

            $lines[] = array_map('trim', explode('|', trim(trim($line), '|')));
        }

        if ($layout === 'columns') {
            return $lines[0];
        }

        return array_map(static fn (array $cells): string => $cells[0], array_slice($lines, 2));
    }
}
