<?php

use App\Libraries\AdminMenu;
use App\Libraries\DocumentStatusPage;
use App\Libraries\StaffAccess;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * `/admin/status-dokumen` (owner, 17 November 2026): the head researcher
 * reads docs/revisi-10-analitik/status-dokumen.html inside the panel, with
 * plain document names instead of links; admin and peneliti get 404 and no
 * menu item.
 *
 * @internal
 */
final class DocumentStatusPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    private const STAFF = ['admin' => 1, 'ketua' => 2, 'peneliti' => 3];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createRawDataWorld();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testHeadResearcherSeesTheRepositoryPageWithoutLinks(): void
    {
        $body = $this->get('ketua', 'admin/status-dokumen');

        $this->assertStringContainsString(lang('Admin.docStatusLead'), $body);
        $this->assertStringContainsString('class="doc-status"', $body);
        $this->assertStringContainsString('tervalidasi ahli', $body);
        $this->assertStringContainsString('Yang masih ditunggu', $body);
        $this->assertDoesNotMatchRegularExpression('/href="[^"]*\.md"/', $body, 'document names, no links to .md files');
        $this->assertStringNotContainsString('<h1>Status dokumen revisi', $body, 'the panel heading replaces the page heading');
        $this->assertStringContainsString('admin/status-dokumen', $this->get('ketua', 'admin'), 'menu item');
    }

    public function testOnlyTheHeadResearcherHasTheCapability(): void
    {
        $this->assertTrue(StaffAccess::allows('ketua', 'research_docs.view'));

        foreach (['admin', 'peneliti'] as $role) {
            $this->assertFalse(StaffAccess::allows($role, 'research_docs.view'), $role);
            $can = static fn (string $capability): bool => StaffAccess::allows($role, $capability);
            $this->assertNotContains('admin/status-dokumen', array_column(AdminMenu::items($can, 'admin'), 'href'), $role);
        }

        $routes = service('routes');
        $routes->loadRoutes();
        $routes->setHTTPVerb('GET');
        $this->assertSame(['staffAuth', 'staffCan:research_docs.view'], $routes->getFiltersForRoute('admin/status-dokumen'));

        $_SESSION['staff_role'] = 'peneliti';

        try {
            $this->expectException(PageNotFoundException::class);
            (new App\Filters\StaffCanFilter())->before(service('request'), ['research_docs.view']);
        } finally {
            unset($_SESSION['staff_role']);
        }
    }

    public function testCleaningKeepsTextAndDropsLinksScriptsAndHandlers(): void
    {
        $file = WRITEPATH . 'status-dokumen-test.html';
        file_put_contents($file, '<!doctype html><html><head><title>x</title><style>body{}</style></head><body><main>'
            . '<h1>Judul</h1><p class="lead">Lihat <a href="00_INDEKS.md">00_INDEKS.md</a>.</p>'
            . '<div class="card" onclick="alert(1)"><h3>08 <span class="badge b2">disetujui peneliti</span></h3></div>'
            . '<script>alert(2)</script><footer>kaki</footer></main></body></html>');

        try {
            $html = (string) DocumentStatusPage::content($file);
        } finally {
            @unlink($file);
        }

        $this->assertStringContainsString('Lihat 00_INDEKS.md.', $html);
        $this->assertStringContainsString('<span class="badge b2">disetujui peneliti</span>', $html);
        foreach (['<a ', '<h1', '<script', 'onclick', '<footer', 'body{}'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, $gone);
        }
        $this->assertNull(DocumentStatusPage::content(WRITEPATH . 'tidak-ada.html'), 'missing file');
    }

    public function testMissingFileShowsAFriendlyMessage(): void
    {
        $this->assertStringContainsString(lang('Admin.docStatusMissing'), view('admin/document-status', ['content' => null, 'pageTitle' => 'x']));
    }

    private function get(string $role, string $path): string
    {
        foreach (['security', 'renderer'] as $name) {
            Services::resetSingle($name);
        }

        return (string) $this->withSession(['staff_id' => self::STAFF[$role], 'staff_role' => $role, 'staff_name' => ucfirst($role)])
            ->call(Method::GET, $path)->getBody();
    }
}
