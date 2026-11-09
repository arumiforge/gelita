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

        $this->assertSame(['L1 Kondisi sesi', 'L1 Log penyimpangan', 'L2 Tempat duduk', 'L3 Bantuan dan gangguan', 'L4 Observasi', 'L5 Wawancara 1', 'L5 Wawancara 2', 'L5 Wawancara 3', 'Kode'], $book->getSheetNames(), 'one L5 sheet per interviewed student (owner, 8 Nov 2026)');

        $l2 = $book->getSheetByName('L2 Tempat duduk')->toArray(null, false, false);
        $this->assertSame(['Kode sesi lapangan', 'S03-A-P2'], array_slice($l2[0], 0, 2));
        $header = $l2[2];
        $rows   = array_map(static fn (array $r): array => array_combine($header, $r), array_slice($l2, 3, 2));
        $this->assertSame(['S03-A01', 'S03-A02'], array_column($rows, 'Kode peserta'));
        $this->assertSame(['B1', 'B2'], array_column($rows, 'Buklet'));
        $this->assertSame([null, null], array_column($rows, 'Kursi'), 'P2–P5: seat left empty, students may sit elsewhere');
        $this->assertNotContains('Kutipan ucapan siswa', $book->getSheetByName('L5 Wawancara 2')->toArray()[2]);

        $l1 = $book->getSheetByName('L1 Kondisi sesi')->toArray(null, false, false);
        $this->assertSame('S03-A-P2', array_combine($l1[2], $l1[3])['Kode sesi lapangan']);
        $this->assertNotContains('Token', $l1[2]);

        $this->assertContains('S03-A02', array_column($book->getSheetByName('Kode')->toArray(), 10), 'participant codes for the drop-down');
        $this->assertNoPersonalData($book);
    }

    public function testMeetingOneTemplateCarriesSeatsAndTheTestSheet(): void
    {
        $fieldSession = $this->createFieldSheetWorld(1);
        $book         = $this->template($fieldSession);

        $this->assertSame(['L1 Kondisi sesi', 'L1 Log penyimpangan', 'L2 Tempat duduk', 'L3 Bantuan dan gangguan', 'L6 Catatan sesi tes', 'L6 Status tes per siswa', 'Kode'], $book->getSheetNames());
        $l2  = $book->getSheetByName('L2 Tempat duduk')->toArray(null, false, false);
        $row = array_combine($l2[2], $l2[3]);
        $this->assertSame(['1', '1', 'A'], [$row['Kursi'], $row['Baris'], $row['Bentuk tes awal']]);

        // Coordinator, wave F: L6 status tes per siswa printed from the test module (29 K12); nothing started yet
        $l6  = $book->getSheetByName('L6 Status tes per siswa')->toArray(null, false, false);
        $row = array_combine($l6[2], $l6[3]);
        $this->assertSame(['S03-A01', 'belum', 'belum', 'belum'], [$row['Kode peserta'], $row['Tes awal (tanggal, bentuk, status)'], $row['Angket'], $row['Butir risiko']]);
        $this->assertNotContains('l6_tes', $this->sheets()->expectedSheets($fieldSession), 'never uploaded');

        $db  = \Config\Database::connect();
        $pid = (int) $db->table('participants')->where('participant_code', 'S03-A01')->get()->getRow('id');
        $db->table('instrument_sittings')->insert([
            'participant_id' => $pid, 'study_id' => (int) $fieldSession['study_id'], 'field_session_id' => (int) $fieldSession['id'], 'occasion' => 'pre',
            'kind' => 'test', 'form' => 'A', 'version_id' => 1, 'is_makeup' => 0, 'is_preview' => 0,
            'started_at' => '2026-11-10 08:05:00.000', 'submitted_at' => '2026-11-10 08:50:00.000', 'updated_at' => '2026-11-10 08:50:00.000',
        ]);
        \Config\Services::resetSingle('instruments'); // the shared service caches sittings
        $l6  = $this->template($fieldSession)->getSheetByName('L6 Status tes per siswa')->toArray(null, false, false);
        $this->assertSame('2026-11-10, A, selesai', array_combine($l6[2], $l6[3])['Tes awal (tanggal, bentuk, status)']);
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

    public function testCommandWritesTheBlankL0ChecklistFromDocument02(): void
    {
        $folder            = sys_get_temp_dir() . '/lembar-' . bin2hex(random_bytes(4));
        $this->tempFiles[] = $folder . '/lembar-kosong.xlsx';
        $this->tempFiles[] = $folder . '/lembar-l0-kosong.xlsx';
        $this->createFieldSheetWorld(2);

        command('gelita:lembar:templat --keluar=' . $folder);

        $blank = IOFactory::load($folder . '/lembar-kosong.xlsx')->getSheetNames();
        $this->assertContains('L5 Wawancara 3', $blank);
        $l0    = IOFactory::load($folder . '/lembar-l0-kosong.xlsx')->getActiveSheet()->toArray(null, false, false);
        $items = array_filter($l0, static fn (array $r): bool => $r[0] === '☐');
        $this->assertGreaterThan(20, count($items), 'every checkbox line of 02 → L0');
        $this->assertStringStartsWith('Kode sesi lapangan:', (string) $l0[1][1]);
        $this->assertContains('H-1, setiap pertemuan', array_column($l0, 1));
        $this->assertSame([], array_filter(array_column($l0, 1), static fn ($t): bool => is_string($t) && preg_match('/\]\(|\*\*|`/', $t) === 1), 'no Markdown left');
        @rmdir($folder);
    }

    public function testTheTemplateRoundTripsWithL1Conditions(): void
    {
        // Found on MariaDB, 9 November 2026: the page head of L1 Kondisi sesi
        // ("Kode sesi lapangan", "Tanggal") was read as the table heading.
        $fieldSession = $this->createFieldSheetWorld(2);
        $path         = tempnam(sys_get_temp_dir(), 'lembar-') . '.xlsx';
        $this->tempFiles[] = $path;
        $this->sheets()->writeTemplate($fieldSession, $path);

        $book = IOFactory::load($path);
        $l1   = $book->getSheetByName('L1 Kondisi sesi');
        $head = $l1->toArray(null, false, false)[2];
        $l1->setCellValue([array_search('Jam mulai (WIB)', $head, true) + 1, 4], '08:00:00');
        $l1->setCellValue([array_search('Jam selesai (WIB)', $head, true) + 1, 4], '09:15:00');
        $l1->setCellValue([array_search('Siswa hadir', $head, true) + 1, 4], '24');
        IOFactory::createWriter($book, 'Xlsx')->save($path);

        $preview = $this->sheets()->preview($fieldSession, $path, 'lembar-S03-A-P2.xlsx', 'main');
        $l1      = $preview['sheets']['l1_kondisi'];

        $this->assertSame([], $l1['errors']);
        $this->assertCount(1, $l1['rows'], 'only the typed row, not the page head or the heading row');
        $this->assertSame(0, $preview['errors']);
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
