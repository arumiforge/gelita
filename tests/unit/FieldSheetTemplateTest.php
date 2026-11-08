<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\StreamFilterTrait;
use Config\Services;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\Support\Database\FieldSheetWorld;

/**
 * The filled template per field session (research revision 10, part 3:
 * 29 §10.3, K4): the sheets of the meeting, filled with codes and booklets;
 * never a name, username, school, NPSN, token, or L7.
 *
 * @internal
 */
final class FieldSheetTemplateTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldSheetWorld;
    use StreamFilterTrait;

    public function testMeetingTwoTemplate(): void
    {
        $fieldSession = $this->createFieldSheetWorld(2);
        $book         = $this->template($fieldSession);

        $this->assertSame(['L1 Kondisi sesi', 'L1 Log penyimpangan', 'L2 Tempat duduk', 'L3 Bantuan dan gangguan', 'L4 Observasi', 'L5 Wawancara', 'Kode'], $book->getSheetNames());

        $l2 = $book->getSheetByName('L2 Tempat duduk')->toArray(null, false, false);
        $this->assertSame(['Kode sesi lapangan', 'S03-A-P2'], array_slice($l2[0], 0, 2));
        $header = $l2[2];
        $rows   = array_map(static fn (array $r): array => array_combine($header, $r), array_slice($l2, 3, 2));
        $this->assertSame(['S03-A01', 'S03-A02'], array_column($rows, 'Kode peserta'));
        $this->assertSame(['B1', 'B2'], array_column($rows, 'Buklet'));
        $this->assertSame([null, null], array_column($rows, 'Kursi'), 'P2–P5: seat left empty, students may sit elsewhere');
        $this->assertNotContains('Kutipan ucapan siswa', $book->getSheetByName('L5 Wawancara')->toArray()[2]);

        $l1 = $book->getSheetByName('L1 Kondisi sesi')->toArray(null, false, false);
        $this->assertSame('S03-A-P2', array_combine($l1[2], $l1[3])['Kode sesi lapangan']);
        $this->assertNotContains('Token', $l1[2]);

        $this->assertContains('S03-A02', array_column($book->getSheetByName('Kode')->toArray(), 10), 'participant codes for the drop-down');
        $this->assertNoPersonalData($book);
    }

    public function testMeetingOneTemplateCarriesSeatsAndTheTestSheet(): void
    {
        $book = $this->template($this->createFieldSheetWorld(1));

        $this->assertSame(['L1 Kondisi sesi', 'L1 Log penyimpangan', 'L2 Tempat duduk', 'L3 Bantuan dan gangguan', 'L6 Catatan sesi tes', 'Kode'], $book->getSheetNames());
        $l2  = $book->getSheetByName('L2 Tempat duduk')->toArray(null, false, false);
        $row = array_combine($l2[2], $l2[3]);
        $this->assertSame(['1', '1', 'A'], [$row['Kursi'], $row['Baris'], $row['Bentuk tes awal']]);
        $this->assertNoPersonalData($book);
    }

    public function testPanelDownloadIsAuditedAndTheUploadPageShowsTheState(): void
    {
        $fieldSession = $this->createFieldSheetWorld(2);
        $session      = ['staff_id' => 2, 'staff_role' => 'peneliti', 'staff_name' => 'Peneliti'];

        Services::resetSingle('security');
        $response = $this->withSession($session)->call(Method::GET, 'admin/sesi-lapangan/' . $fieldSession['id'] . '/lembar');
        $this->assertStringContainsString('lembar-S03-A-P2.xlsx', $response->response()->getHeaderLine('Content-Disposition'));
        $this->assertSame('PK', substr((string) $response->response()->getBody(), 0, 2), 'an XLSX file');
        $this->assertSame(1, db_connect()->table('audit_logs')->where('action', 'field_sheet_template')->countAllResults());

        Services::resetSingle('security');
        $page = (string) $this->withSession($session)->call(Method::GET, 'admin/sesi-lapangan/' . $fieldSession['id'] . '/lembar/unggah')->getBody();
        $this->assertStringContainsString(lang('Admin.fieldSheetStatusMissing'), $page);
        $this->assertStringContainsString('L5 Wawancara', $page);
    }

    public function testCommandWritesTheColumnSpec(): void
    {
        $file              = sys_get_temp_dir() . '/kolom-' . bin2hex(random_bytes(4)) . '.json';
        $this->tempFiles[] = $file;
        $this->createFieldSheetWorld(2);

        command('gelita:lembar:templat --kolom=' . $file);

        $spec = json_decode((string) file_get_contents($file), true);
        $this->assertSame(App\Libraries\FieldSheetSpec::VERSION, $spec['version']);
        $this->assertSame(['try_no'], $spec['sheets']['l5_wawancara']['paper']['Percobaan']);
        $this->assertFalse($spec['sheets']['l7_izin']['imported']);
    }

    private function template(array $fieldSession): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $path              = tempnam(sys_get_temp_dir(), 'lembar-') . '.xlsx';
        $this->tempFiles[] = $path;
        $this->sheets()->writeTemplate($fieldSession, $path);

        return IOFactory::load($path);
    }

    private function assertNoPersonalData(\PhpOffice\PhpSpreadsheet\Spreadsheet $book): void
    {
        $all = '';

        foreach ($book->getAllSheets() as $ws) {
            $all .= json_encode($ws->toArray());
            $this->assertStringNotContainsStringIgnoringCase('izin', $ws->getTitle(), 'no L7');
        }

        foreach (['Jaka', 'Sekar', 'jaka5', 'sekar.ayu', 'SD Lapangan', 'KD7RMA'] as $secret) {
            $this->assertStringNotContainsString($secret, $all, $secret);
        }
    }
}
