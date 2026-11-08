<?php

use App\Services\ConsentService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * The coded consent register (research revision 10, part 4: 30 §10.1,
 * §19.1 `ConsentRegisterExportTest`).
 * Also the `izin` sheet of the head researcher's main download (29 §7.2,
 * 30 keputusan no. 9), which never fills `consent_register_exported_at`.
 *
 * @internal
 */
final class ConsentRegisterExportTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->conn->table('participants')->where('id', 1)->update(['assented_at' => '2030-01-02 08:00:00', 'assent_version' => 't4-1']);
        $this->consent($this->conn, 1, ['risk_items_consent' => 1]);
        $other = $this->student($this->conn, 'S03-A02', 'dewi');
        $this->consent($this->conn, $other, ['status' => 'withdrawn_keep', 'status_on' => date('Y-m-d')]);
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testColumnsWithoutIdentitiesAndTheStampIsSet(): void
    {
        $this->asStaff('ketua');
        $response = $this->send(Method::GET, 'admin/ekspor/izin?studi=1')->response();
        $this->assertInstanceOf(CodeIgniter\HTTP\DownloadResponse::class, $response);
        ob_start();
        $response->sendBody();
        $xlsx = $this->xlsxText((string) ob_get_clean());

        foreach (ConsentService::REGISTER_COLUMNS as $column) {
            $this->assertStringContainsString($column, $xlsx);
        }

        $this->assertSame(['participant_code', 'parent_consent_on', 'recording_consent', 'recording_withdrawn_on', 'risk_items_consent', 'risk_items_withdrawn_on', 'assented_at', 'assent_version', 'status', 'status_on'], ConsentService::REGISTER_COLUMNS);
        $this->assertStringContainsString('S03-A01', $xlsx);
        $this->assertStringContainsString('withdrawn_keep', $xlsx);
        $this->assertStringContainsString('t4-1', $xlsx);

        foreach (['Jaka', 'jaka5', 'SD Lapangan', 'DEWI', 'dewi'] as $identity) {
            $this->assertStringNotContainsString($identity, $xlsx, $identity);
        }

        $this->assertNotNull($this->conn->table('research_studies')->where('id', 1)->get()->getRow('consent_register_exported_at'));
        $export = $this->conn->table('data_exports')->get()->getRowArray();
        $this->assertSame('consent_register', json_decode((string) $export['scope_json'], true)['kind']);
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'consent_register_export')->countAllResults());
    }

    public function testIzinSheetOfTheMainDownloadForTheHeadResearcherOnly(): void
    {
        $export  = service('exportService');
        $context = $export->context(['id' => 0, 'requested_by' => 3, 'format' => 'xlsx', 'anonymized' => 1, 'scope_json' => ['filters' => ['study_id' => 1]]]);
        $this->assertContains('izin', $context['sheets']);
        $this->assertSame(ConsentService::REGISTER_COLUMNS, $export->columns('izin', true, $context['full_detail']));

        $rows = iterator_to_array($export->sheetRows('izin', $context), false);
        $this->assertEqualsCanonicalizing(['S03-A01', 'S03-A02'], array_column($rows, 'participant_code'));
        $this->assertSame(array_column(service('consentService')->registerRows(1), 'status', 'participant_code'), array_column($rows, 'status', 'participant_code'));
        $this->assertNull($this->conn->table('research_studies')->where('id', 1)->get()->getRow('consent_register_exported_at'));

        $this->assertNotContains('izin', (new \App\Services\RawDataService())->dimensions(\App\Services\StaffScope::fromRole('peneliti')));
    }

    public function testRowsNarrowToTheStudentsOfADownload(): void
    {
        $rows = service('consentService')->registerRows(1, [1]);

        $this->assertSame(['S03-A01'], array_column($rows, 'participant_code'));
        $this->assertSame([], service('consentService')->registerRows(1, []));
    }

    public function testHeadResearcherOnly(): void
    {
        $this->asStaff('peneliti');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/ekspor/izin');
    }

    private function xlsxText(string $bytes): string
    {
        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        file_put_contents($path, $bytes);
        $zip  = new ZipArchive();
        $zip->open($path);
        $text = '';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $text .= $zip->getFromIndex($i) . "\n";
        }

        $zip->close();
        unlink($path);

        return $text;
    }
}
