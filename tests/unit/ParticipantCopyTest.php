<?php

use App\Services\ParticipantCopyService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * One child's data copy (research revision 10, part 4: 30 §8, K2, K14,
 * §19.1 `ParticipantCopyTest`).
 *
 * @internal
 */
final class ParticipantCopyTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ConsentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createConsentWorld();
        $this->consent($this->conn, 1);
        $this->playData($this->conn, 1, 1);
        $other = $this->student($this->conn, 'S03-A02', 'dewi');
        $this->playData($this->conn, $other, (int) $this->conn->table('game_sessions')->where('participant_id', $other)->get()->getRow('id'));
    }

    protected function tearDown(): void
    {
        $this->dropRiskTables();
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testTheZipHoldsThisChildOnlyAndIsNotKept(): void
    {
        $before = glob(WRITEPATH . 'cache/salinan-*') ?: [];
        $files  = $this->download(['participant_code' => 's03-a01', 'purpose' => 'copy']);

        $this->assertSame(['data-S03-A01.xlsx', 'ringkasan-S03-A01.pdf'], array_keys($files));
        $this->assertStringStartsWith('%PDF', $files['ringkasan-S03-A01.pdf']);
        $xlsx = $this->xlsxText($files['data-S03-A01.xlsx']);
        $this->assertStringContainsString('S03-A01', $xlsx);
        $this->assertStringNotContainsString('S03-A02', $xlsx, 'no other child');
        $this->assertStringNotContainsString('Jaka', $xlsx);
        $this->assertSame($before, glob(WRITEPATH . 'cache/salinan-*') ?: [], 'nothing stays on the server');

        $audit = json_decode((string) $this->conn->table('audit_logs')->where('action', 'participant_copy')->get()->getRow('metadata_json'), true);
        $this->assertSame(['participant_code' => 'S03-A01', 'purpose' => 'copy', 'risk_answers' => 'none'], $audit);
    }

    public function testColumnsWithoutKeysTextsIdsOrDevices(): void
    {
        $kept = ParticipantCopyService::columns(['attempt_id', 'participant_code', 'answer_key', 'prompt', 'first_answer', 'is_correct', 'device_type', 'os_name', 'hint_id', 'session_code']);

        $this->assertSame(['participant_code', 'first_answer', 'is_correct', 'session_code'], $kept);
        $this->assertNotContains('perangkat', ParticipantCopyService::SHEETS);
        $this->assertNotContains('butir', ParticipantCopyService::SHEETS);
        $this->assertNotContains('event', ParticipantCopyService::SHEETS);
    }

    public function testRiskAnswersOnlyWithTheChildsOrParentsChoice(): void
    {
        $this->createRiskTables($this->conn);
        $this->riskAnswers($this->conn, 1, '1');

        $this->asStaff('ketua');
        $this->assertStringContainsString(esc(lang('Admin.copyRiskChildAgreed')), (string) $this->send(Method::GET, 'admin/peserta/salinan?kode=S03-A01')->getBody());

        $this->assertStringContainsString(esc(lang('Admin.copyRiskLeftOut')), service('participantCopy')->summaryHtml(1, true));
        $this->assertStringNotContainsString(esc(lang('Admin.copyRiskLeftOut')), service('participantCopy')->summaryHtml(1, false));

        $none = $this->xlsxText($this->download(['participant_code' => 'S03-A01', 'purpose' => 'copy', 'risk_answers' => 'none'])['data-S03-A01.xlsx']);
        $this->assertStringNotContainsString('risk_upset', $none);

        $with = $this->xlsxText($this->download(['participant_code' => 'S03-A01', 'purpose' => 'copy', 'risk_answers' => 'child_agreed'])['data-S03-A01.xlsx']);
        $this->assertStringContainsString('risk_upset', $with);

        $choices = array_map(static fn (array $r): string => json_decode((string) $r['metadata_json'], true)['risk_answers'], $this->conn->table('audit_logs')->where('action', 'participant_copy')->orderBy('id')->get()->getResultArray());
        $this->assertSame(['none', 'child_agreed'], $choices);
    }

    public function testHeadResearcherOnly(): void
    {
        $this->asStaff('admin');
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/peserta/salinan');
    }

    /** @return array<string, string> file name => bytes */
    private function download(array $post): array
    {
        $this->asStaff('ketua');
        $response = $this->send(Method::POST, 'admin/peserta/salinan', $post)->response();
        $this->assertInstanceOf(CodeIgniter\HTTP\DownloadResponse::class, $response);
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        ob_start();
        $response->sendBody();

        return $this->unzip((string) ob_get_clean());
    }

    /** @return array<string, string> */
    private function unzip(string $bytes): array
    {
        $path = tempnam(sys_get_temp_dir(), 'zip');
        file_put_contents($path, $bytes);
        $zip = new ZipArchive();
        $zip->open($path);
        $out = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $out[$zip->getNameIndex($i)] = (string) $zip->getFromIndex($i);
        }

        $zip->close();
        unlink($path);
        ksort($out);

        return $out;
    }

    private function xlsxText(string $bytes): string
    {
        return implode("\n", $this->unzip($bytes));
    }
}
