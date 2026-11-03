<?php

use App\Libraries\AdminTable;
use CodeIgniter\Config\BaseService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Tabel data panel admin di tingkat tampilan (Dok 3 §8.2, §12.1
 * `AdminTableTest`): komponen components/data-table merender kolom, urut
 * server-side, pagination 25/50/100 + total, dan tombol CSV; CSV mentah
 * sama dengan isi tabel; researchers never see identity columns, and the
 * admin has no list CSV (revision 10 part 1, 27 K17). Kontrak
 * `RawDataService::page()` diuji sesi data mentah di `AdminTableTest`.
 *
 * Basis data SQLite dari trait ResetTables; staf disuntikkan ke service
 * staffContext seperti NarrationPagesTest.
 *
 * @internal
 */
final class AdminTableViewTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use ResetTables;

    private BaseConnection $sqlite;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = $this->createResetTables();
        $this->sqlite->table('schools')->insertBatch([
            ['id' => 1, 'code' => 'S1', 'name' => 'SD Satu'],
            ['id' => 2, 'code' => 'S2', 'name' => 'SD Dua'],
        ]);

        for ($i = 1; $i <= 60; $i++) {
            $this->sqlite->table('participants')->insert([
                'participant_code' => sprintf('GLT-%06d', $i),
                'username'         => 'siswa' . $i,
                'password_hash'    => 'x',
                'display_name'     => 'Nama Rahasia ' . $i,
                'age'              => 10 + $i % 3,
                'gender'           => $i % 2 === 0 ? 'perempuan' : 'laki-laki',
                'school_id'        => $i <= 40 ? 1 : 2,
                'created_at'       => sprintf('2026-10-%02d 08:00:00', 1 + $i % 28),
            ]);
        }

        Services::resetSingle('contentRepository');
        Services::resetSingle('security');
    }

    protected function tearDown(): void
    {
        $this->injectStaff(null);
        $this->dropResetTables();

        parent::tearDown();
    }

    public function testPageSortsAndPaginatesOnTheServer(): void
    {
        $columns = [
            ['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code', 'sort' => 'participant_code'],
            ['key' => 'age', 'label' => 'Umur', 'type' => 'int', 'sort' => 'age'],
        ];
        $builder = fn () => $this->sqlite->table('participants')->select('id, participant_code, age');

        $first = AdminTable::page($builder(), $columns, 1, 25, '-participant_code', 'id');
        $this->assertSame(60, $first['total']);
        $this->assertCount(25, $first['rows']);
        $this->assertSame('GLT-000060', $first['rows'][0]['participant_code']);
        $this->assertSame(['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code', 'sortable' => true], $first['columns'][0]);

        $last = AdminTable::page($builder(), $columns, 9, 25, 'participant_code', 'id');
        $this->assertSame(3, $last['page'], 'halaman dijepit ke halaman terakhir');
        $this->assertCount(10, $last['rows']);
        $this->assertSame('GLT-000060', end($last['rows'])['participant_code']);

        $request = service('incomingrequest')->setGlobal('get', ['per_page' => '40', 'sort' => 'password_hash', 'page' => '2']);
        $params  = AdminTable::params($request, ['participant_code', 'age'], '-age');
        $this->assertSame(['page' => 2, 'per_page' => 25, 'sort' => '-age'], $params, 'per_page di luar 25/50/100 dan urutan tak dikenal ditolak');
    }

    public function testComponentRendersColumnsSortLinksPaginationAndCsv(): void
    {
        $table = [
            'rows'     => [['participant_code' => 'GLT-000001', 'age' => 11, 'duration_ms' => 125000, 'status' => 'completed', 'note' => null]],
            'total'    => 60,
            'page'     => 2,
            'per_page' => 25,
            'sort'     => '-age',
            'columns'  => [
                ['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code'],
                ['key' => 'age', 'label' => 'Umur (tahun)', 'type' => 'int'],
                ['key' => 'duration_ms', 'label' => 'Durasi', 'type' => 'ms', 'sortable' => false],
                ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ['key' => 'note', 'label' => 'Catatan', 'type' => 'text'],
            ],
        ];

        $html = html_entity_decode(component('data-table', ['table' => $table, 'id' => 'uji']), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('has-sticky-first', $html);
        $this->assertMatchesRegularExpression('/<th scope="col" class="is-num" aria-sort="descending" data-key="age">/', $html);
        $this->assertStringContainsString('sort=age', $html, 'klik kedua membalik urutan');
        $this->assertStringContainsString('sort=participant_code', $html);
        $this->assertStringNotContainsString('sort=duration_ms', $html, 'kolom tak dapat diurutkan tanpa tautan');
        $this->assertStringContainsString('<span title="125.000 mdtk">2:05</span>', $html);
        $this->assertStringContainsString('<span class="badge is-completed">selesai</span>', $html);
        $this->assertStringContainsString('<span class="is-empty">—</span>', $html);
        $this->assertStringContainsString('26–50 dari 60 baris', $html);
        $this->assertMatchesRegularExpression('/<option value="50"\s*>50<\/option>/', $html);
        $this->assertStringContainsString('page=3', $html);
        $this->assertStringContainsString('format=csv', $html);
        $this->assertStringContainsString('Unduh CSV', $html);
    }

    public function testCsvIsTheRawContentOfTheTable(): void
    {
        $columns = [
            ['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code', 'sort' => 'participant_code'],
            ['key' => 'gender', 'label' => 'JK', 'type' => 'label:gender', 'sort' => 'gender'],
            ['key' => 'age', 'label' => 'Umur', 'type' => 'int', 'sort' => 'age'],
        ];
        $builder = fn () => $this->sqlite->table('participants')->select('id, participant_code, gender, age');
        $page    = AdminTable::page($builder(), $columns, 1, 100, 'participant_code', 'id');

        $handle = fopen('php://memory', 'w+b');
        $count  = AdminTable::streamCsv($builder(), $columns, $handle, 'participant_code', 'id');
        rewind($handle);
        $this->assertSame("\xEF\xBB\xBF", fread($handle, 3), 'UTF-8 dengan BOM');
        $csv = [];

        while (($line = fgetcsv($handle, null, ',', '"', '')) !== false) {
            $csv[] = $line;
        }
        fclose($handle);

        $this->assertSame(60, $count);
        $this->assertSame(['participant_code', 'gender', 'age'], $csv[0]);
        $this->assertSame(
            array_map(static fn (array $row): array => [$row['participant_code'], $row['gender'], (string) $row['age']], $page['rows']),
            array_slice($csv, 1),
            'CSV memuat nilai berkode yang sama dengan tabel (gender mentah, bukan label)',
        );
    }

    public function testParticipantListForAdminIncludesIdentityWithoutCsv(): void
    {
        $result = $this->asStaff('admin')->call(Method::GET, 'admin/peserta', ['sort' => 'participant_code']);
        $result->assertOK();
        $html = (string) $result->getBody();

        $this->assertStringContainsString('>Nama pengguna<', $html);
        $this->assertMatchesRegularExpression('/Nama Rahasia 1\s*</', $html);
        $this->assertStringContainsString('1–25 dari 60 baris', html_entity_decode($html));

        // No CSV button, and ?format=csv gives the page, not a file (27 K17)
        $this->assertStringNotContainsString('format=csv', $html);
        $csv = $this->asStaff('admin')->call(Method::GET, 'admin/peserta', ['format' => 'csv']);
        $this->assertStringNotContainsString('text/csv', $csv->response()->getHeaderLine('Content-Type'));
    }

    public function testResearcherSeesCodesWithoutIdentityColumns(): void
    {
        $result = $this->asStaff('peneliti')->call(Method::GET, 'admin/peserta', ['school_id' => 2]);
        $result->assertOK();
        $html = html_entity_decode((string) $result->getBody(), ENT_QUOTES | ENT_HTML5);

        $this->assertStringContainsString('1–20 dari 20 baris', $html, 'the school filter narrows the list');
        $this->assertStringNotContainsString('Nama pengguna</a>', $html);
        $this->assertStringNotContainsString('Nama Rahasia', $html);
        $this->assertStringNotContainsString('siswa45', $html);
        $this->assertStringContainsString('GLT-000045', $html);
        $this->assertStringContainsString('SCH-000002', $html, 'the school as its code');

        $csv = (string) $this->asStaff('peneliti')->call(Method::GET, 'admin/peserta', ['format' => 'csv'])->response()->getBody();
        $this->assertStringNotContainsString('display_name', $csv);
        $this->assertStringNotContainsString('username', $csv);
        $this->assertStringContainsString('school_ref', $csv);
        $this->assertStringContainsString('SCH-000001', $csv);
        $this->assertSame(61, substr_count(trim($csv), "\n") + 1, 'judul + 60 baris, every school');

        // Quick search names only what a researcher can search (Dok 3 §11 audit, item 6)
        $this->assertStringContainsString('placeholder="Cari kode siswa"', $html);

        // Content is read-only, research data for all students is ketua's: menu items by capability
        $this->assertStringContainsString('admin/konten', $html);
        $this->assertStringNotContainsString('admin/konten/impor-bank', $html);
        $this->assertStringNotContainsString('href="' . base_url('admin/data') . '"', $html);
        $this->assertStringContainsString('admin/peserta/reset-sandi', $html);
    }

    public function testLoginLayoutHasASkipLink(): void
    {
        $html = (string) $this->call(Method::GET, 'admin/login')->getBody();

        $this->assertStringContainsString('<a class="skip-link" href="#auth-main">Langsung ke isi halaman</a>', $html);
        $this->assertStringContainsString('id="auth-main"', $html);
    }

    private function asStaff(string $role): self
    {
        $this->injectStaff($role);

        return $this->withSession(['staff_id' => 7, 'staff_role' => $role, 'staff_name' => 'Uji']);
    }

    /** null = hapus suntikan agar test lain kembali membaca database. */
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
