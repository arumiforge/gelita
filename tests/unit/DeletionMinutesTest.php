<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\ConsentWorld;

/**
 * The minutes of a parent's request (research revision 10, part 4: 30 §7.6,
 * §19.1 `DeletionMinutesTest`).
 *
 * @internal
 */
final class DeletionMinutesTest extends CIUnitTestCase
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
    }

    protected function tearDown(): void
    {
        $this->dropConsentWorld();
        parent::tearDown();
    }

    public function testMinutesHoldCodesDatesCountsAndTicksWithoutNames(): void
    {
        $id = $this->fileRequest('all');
        service('dataRequests')->confirm($id, 3);
        service('retentionService')->execute($id, 1);
        service('dataRequests')->tick($id, 'backups', 1, 'admin');

        $html = service('dataRequests')->minutesHtml($this->requestRow($id));

        $this->assertStringContainsString('S03-A01', $html);
        $this->assertStringContainsString(esc(lang('Admin.requestKind_all')), $html);
        $this->assertStringContainsString('participant_consents', $html);
        $this->assertStringContainsString(esc(lang('Admin.requestItem_backups')), $html);
        $this->assertStringContainsString('peneliti_uji', $html, 'staff by account name');
        $this->assertStringContainsString('ketua_uji', $html);
        $this->assertStringNotContainsString('Jaka', $html);
        $this->assertStringNotContainsString('jaka5', $html);

        $this->asStaff('ketua');
        $response = $this->send(Method::GET, 'admin/permintaan-data/' . $id . '/berita-acara')->response();
        $this->assertInstanceOf(CodeIgniter\HTTP\DownloadResponse::class, $response);
        $this->assertStringContainsString('no-store', $response->getHeaderLine('Cache-Control'));
        ob_start();
        $response->sendBody();
        $this->assertStringStartsWith('%PDF', (string) ob_get_clean());
    }
}
