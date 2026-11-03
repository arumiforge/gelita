<?php

use CodeIgniter\Config\BaseService;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\RawDataWorld;

/**
 * Student pages of the panel, for the code issues found in the Dok 3 §11
 * documentation audit (decision log, items 1–3): the Jawaban tab shows the
 * whole try history, the list search does not match hidden identity
 * columns for researchers, and list-table CSV downloads are audited (the
 * admin has none, revision 10 part 1, 27 K17).
 *
 * @internal
 */
final class ParticipantPanelTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use RawDataWorld;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createRawDataWorld();
        Services::resetSingle('contentRepository');
        Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropResetTables();

        parent::tearDown();
    }

    public function testJawabanTabShowsTriesPastTheFirstPage(): void
    {
        // Ani already has 2 tries on this item; 30 more makes 32, past PER_PAGE 25
        for ($i = 3; $i <= 32; $i++) {
            db_connect()->table('item_tries')->insert([
                'challenge_attempt_id' => 1, 'challenge_item_id' => 2, 'try_no' => $i, 'check_no' => $i,
                'answer_json' => '{"verdict":"tanda-' . $i . '"}', 'is_correct' => 0, 'occurred_at' => sprintf('2026-10-01 09:%02d:00', $i),
            ]);
        }

        $html = (string) $this->asStaff('ketua')->call(Method::GET, 'admin/peserta/1', ['tab' => 'jawaban'])->getBody();

        $this->assertStringContainsString('tanda-32', $html, 'newest try');
        $this->assertMatchesRegularExpression('/tanda-3(?!\d)/', $html, 'oldest added try, which page() used to cut off');
    }

    public function testRawDataTablesShowReadableAnswersWithASwitchToJson(): void
    {
        $readable = $this->researchPage('admin/data/jawaban');
        $this->assertMatchesRegularExpression('/aria-current="true">Mudah dibaca</', $readable);
        $this->assertStringContainsString('raw=1', $readable, 'link to the JSON view');
        $this->assertStringNotContainsString('{"verdict', $readable);
        $this->assertMatchesRegularExpression('/<td class="">\s*Salah\s*<\/td>/', $readable);

        $raw = $this->researchPage('admin/data/jawaban', ['raw' => '1']);
        $this->assertMatchesRegularExpression('/aria-current="true">Asli \(JSON\)</', $raw);
        $this->assertStringContainsString('>{"verdict":"salah"}<', $raw);

        // Tabs without answer columns have no switch
        $this->assertStringNotContainsString('Mudah dibaca', $this->researchPage('admin/data/pengerjaan'));

        // The student page's Jawaban tab, try history included
        $student = $this->researchPage('admin/peserta/1', ['tab' => 'jawaban']);
        $this->assertStringContainsString('Mudah dibaca', $student);
        $this->assertStringNotContainsString('{"verdict', $student);
    }

    public function testResearcherSearchDoesNotMatchUsername(): void
    {
        $this->assertSame(0, $this->listTotal('peneliti', 'ani.5'), 'a hidden username must not find the student');
        $this->assertSame(1, $this->listTotal('peneliti', '000001'));
        $this->assertSame(1, $this->listTotal('admin', 'ani.5'), 'admins still search by username');
    }

    public function testListCsvDownloadsAreAudited(): void
    {
        $this->asStaff('peneliti')->call(Method::GET, 'admin/peserta', ['format' => 'csv', 'q' => 'Ani'])->assertOK();
        $this->asStaff('ketua')->call(Method::GET, 'admin/sesi', ['format' => 'csv'])->assertOK();
        $this->asStaff('ketua')->call(Method::GET, 'admin/sesi/1', ['format' => 'csv'])->assertOK();
        $this->asStaff('ketua')->call(Method::GET, 'admin/masukan', ['format' => 'csv'])->assertOK();

        $rows = db_connect()->table('audit_logs')->where('action', 'list_csv')->orderBy('id')->get()->getResultArray();
        $this->assertSame(['siswa', 'sesi', 'linimasa-1', 'kritik-saran'], array_column($rows, 'target_id'));
        $this->assertSame(['admin_table'], array_values(array_unique(array_column($rows, 'target_type'))));

        $meta = json_decode((string) $rows[0]['metadata_json'], true);
        $this->assertArrayNotHasKey('school_id', $meta['filters'], 'no school scope since part 1');
        $this->assertTrue($meta['search']);
        $this->assertArrayNotHasKey('q', $meta['filters'], 'the search text may hold a name');
        $this->assertSame(0, $meta['row_count']);
    }

    public function testAdminHasNoStudentListCsv(): void
    {
        $page = (string) $this->asStaff('admin')->call(Method::GET, 'admin/peserta', ['format' => 'csv'])->getBody();

        $this->assertStringContainsString('<html', $page, 'the admin gets the page, never a file with names');
        $this->assertSame(0, db_connect()->table('audit_logs')->where('action', 'list_csv')->countAllResults());
    }

    /**
     * One page as the head researcher. The shared RawDataService left by an earlier request in
     * the same process has no staff scope, so it is reset first.
     */
    private function researchPage(string $path, array $query = []): string
    {
        Services::resetSingle('rawDataService');

        return (string) $this->asStaff('ketua')->call(Method::GET, $path, $query)->getBody();
    }

    /** Row total the student list reports for search $q. */
    private function listTotal(string $role, string $q): int
    {
        $html = html_entity_decode((string) $this->asStaff($role)->call(Method::GET, 'admin/peserta', ['q' => $q])->getBody());

        $this->assertMatchesRegularExpression('/dari (\d+) baris/', $html);
        preg_match('/dari (\d+) baris/', $html, $match);

        return (int) $match[1];
    }

    private function asStaff(string $role): self
    {
        $this->injectStaff($role);

        return $this->withSession(['staff_id' => 7, 'staff_role' => $role, 'staff_name' => 'Uji']);
    }

    /** null removes the injected staff so later tests read the database again. */
    private function injectStaff(?string $role): void
    {
        $property  = new ReflectionProperty(BaseService::class, 'instances');
        $instances = $property->getValue();

        if ($role === null) {
            unset($instances['staffcontext']);
        } else {
            $instances['staffcontext'] = (object) [
                'id' => 7, 'username' => 'staf_uji', 'email' => null, 'role' => $role, 'display_name' => 'Staf Uji',
                'is_active' => 1, 'must_change_password' => 0, 'last_login_at' => null,
            ];
        }

        $property->setValue(null, $instances);
    }
}
