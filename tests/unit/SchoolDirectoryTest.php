<?php

use App\Libraries\SchoolImporter;
use App\Services\SchoolDirectory;
use App\Validation\GelitaRules;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Services;
use Tests\Support\Database\SchoolTables;

/**
 * Direktori sekolah: impor daftar resmi (SchoolImporter), cek NPSN,
 * pencocokan nama ketikan, entri belum terverifikasi, dan penggabungan.
 *
 * Memakai grup basis data `tests` (SQLite di memori) dengan tabel ringkas
 * (SchoolTables). Sekolah uji meniru data resmi Jawa Tengah, termasuk dua
 * "SD NEGERI 1 KARANGGEDANG" di kab/kota yang sama — nama saja tidak cukup.
 *
 * @internal
 */
final class SchoolDirectoryTest extends CIUnitTestCase
{
    use SchoolTables;

    private const OFFICIAL = [
        ['20318068', 'SD 1 CENDONO', 'SD', 'sd', 'NEGERI', '33.19', 'DAWE', 'CENDONO'],
        ['20337844', 'SD 4 CENDONO', 'SD', 'sd', 'NEGERI', '33.19', 'DAWE', 'CENDONO'],
        ['60712310', 'MIS NU MIFTAHUL FALAH', 'MI', 'sd', 'SWASTA', '33.19', 'DAWE', 'CENDONO'],
        ['20364140', 'MTS NU MIFTAHUL FALAH', 'MTS', 'smp', 'SWASTA', '33.19', 'DAWE', 'CENDONO'],
        ['20302001', 'SD NEGERI 1 KARANGGEDANG', 'SD', 'sd', 'NEGERI', '33.03', 'BUKATEJA', 'KARANGGEDANG'],
        ['20302002', 'SD NEGERI 1 KARANGGEDANG', 'SD', 'sd', 'NEGERI', '33.03', 'KARANGANYAR', 'KARANGGEDANG'],
        ['20301003', 'SEKOLAH DASAR NEGERI 3 TUMIYANG KECAMATAN PEKUNCEN', 'SD', 'sd', 'NEGERI', '33.02', 'PEKUNCEN', 'TUMIYANG'],
        ['20301004', 'SD NEGERI 1 PEKUNCEN', 'SD', 'sd', 'NEGERI', '33.02', 'PEKUNCEN', 'PEKUNCEN'],
    ];

    private const KUDUS = ['country_code' => 'ID', 'province_code' => '33', 'district_code' => '33.19'];

    private BaseConnection $sqlite;

    /** @var list<string> */
    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->sqlite = Database::connect('tests');
        $this->createSchoolTables($this->sqlite);
        Services::resetSingle('schoolDirectory');
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        $this->dropSchoolTables();
        Services::resetSingle('schoolDirectory');

        parent::tearDown();
    }

    // ------------------------------------------------------------------ impor

    public function testImportWritesOnlyChangesAndNeverDeletes(): void
    {
        $importer = new SchoolImporter($this->sqlite);

        $first = $importer->import($this->csv(self::OFFICIAL));
        $this->assertSame([8, 8, 0, 0], [$first['total'], $first['added'], $first['updated'], $first['unchanged']]);

        $again = $importer->import($this->csv(self::OFFICIAL));
        $this->assertSame([0, 0, 8], [$again['added'], $again['updated'], $again['unchanged']], 'impor ulang berkas yang sama = 0 perubahan');

        $renamed       = self::OFFICIAL;
        $renamed[1][1] = 'SD 4 CENDONO DAWE';
        unset($renamed[3]);

        $third = $importer->import($this->csv(array_values($renamed)));
        $this->assertSame([0, 1, 6], [$third['added'], $third['updated'], $third['unchanged']]);
        $this->assertSame(['20364140'], $third['missing']);
        $this->assertSame('SD 4 CENDONO DAWE', $this->school('20337844')['name']);
        $this->assertNotNull($this->school('20364140'), 'sekolah yang hilang dari berkas tidak dihapus');

        $row = $this->school('20318068');
        $this->assertSame(['1', '33', '33.19', 'sd', 'SD 1 CENDONO'], [(string) $row['is_verified'], $row['province_code'], $row['district_code'], $row['stage'], $row['match_key']]);
    }

    public function testDryRunWritesNothing(): void
    {
        $report = (new SchoolImporter($this->sqlite))->import($this->csv(self::OFFICIAL), true);

        $this->assertSame(8, $report['added']);
        $this->assertSame(0, $this->sqlite->table('schools')->countAllResults());
    }

    public function testBrokenFileIsRejectedWhole(): void
    {
        $rows      = self::OFFICIAL;
        $rows[2][0] = '123';
        $rows[]    = self::OFFICIAL[0];

        try {
            (new SchoolImporter($this->sqlite))->import($this->csv($rows));
            $this->fail('berkas rusak harus ditolak');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString("NPSN '123' bukan 8 angka", $e->getMessage());
            $this->assertStringContainsString('NPSN 20318068 ganda', $e->getMessage());
        }

        $this->assertSame(0, $this->sqlite->table('schools')->countAllResults());
    }

    public function testImportRefreshesStaleKeysOfTypedSchools(): void
    {
        $this->sqlite->table('schools')->insert(['name' => 'sdn 01 cendono', 'is_verified' => 0, 'match_key' => 'KUNCI LAMA']);

        $report = $this->import();

        $this->assertSame(1, $report['rekeyed']);
        $this->assertSame('SD 1 CENDONO', $this->sqlite->table('schools')->where('code', null)->get()->getRow('match_key'));
    }

    public function testShippedDataFileImports(): void
    {
        $report = (new SchoolImporter($this->sqlite))->import(SchoolImporter::DEFAULT_FILE, true);

        $this->assertGreaterThan(25000, $report['total']);
        $this->assertSame($report['total'], $report['added']);
    }

    // ------------------------------------------------------------------ NPSN

    public function testFindByNpsn(): void
    {
        $this->import();
        $directory = $this->directory();

        $this->assertSame('SD 1 CENDONO', $directory->findByNpsn('20318068')['name']);
        $this->assertSame('SD 1 CENDONO', $directory->findByNpsn(' 20318068 ')['name']);
        $this->assertNull($directory->findByNpsn('12345678'));
        $this->assertNull($directory->findByNpsn('2031806'));
        $this->assertNull($directory->findByNpsn('2031806x'));

        $card = $directory->describe($directory->findByNpsn('20318068'));
        $this->assertSame(['20318068', 'DAWE', 'Kabupaten Kudus', 'sd'], [$card['npsn'], $card['subdistrict'], $card['district_name'], $card['stage']]);
    }

    public function testKnownNpsnValidationRule(): void
    {
        $this->import();
        $rules = new GelitaRules();

        $this->assertTrue($rules->known_npsn('20318068'));
        $this->assertFalse($rules->known_npsn('12345678'));
        $this->assertFalse($rules->known_npsn(''));
    }

    public function testRequiresNpsnOnlyForDirectoryProvinces(): void
    {
        $this->assertFalse($this->directory()->requiresNpsn('ID', '33'), 'daftar resmi belum dipasang: siswa menulis nama');
        $this->assertSame([], $this->directory()->directoryProvinces());

        $this->import();
        $directory = $this->directory();

        $this->assertSame(['33'], $directory->directoryProvinces());
        $this->assertTrue($directory->requiresNpsn('ID', '33'));
        $this->assertFalse($directory->requiresNpsn('ID', '34'));
        $this->assertFalse($directory->requiresNpsn('XX', '33'));
        $this->assertFalse($directory->requiresNpsn('ID', null));
    }

    // ------------------------------------------------------------------ nama ketikan

    public function testMatchByNameFindsTheOfficialSchool(): void
    {
        $this->import();
        $directory = $this->directory();

        foreach (['SD NEGERI 1 CENDONO', 'sdn 01 cendono', 'SD 1 Cendono', 'SD Negeri Cendono 1'] as $typed) {
            $this->assertSame('20318068', $directory->matchByName($typed, '33.19', '33')['code'] ?? null, $typed);
        }

        $this->assertSame('60712310', $directory->matchByName('MI NU Miftahul Falah', '33.19', '33')['code'] ?? null);
        // kecamatan di nama resmi, tidak diketik siswa
        $this->assertSame('20301003', $directory->matchByName('SD N 3 Tumiyang', '33.02', '33')['code'] ?? null);
        // kab/kota yang dipilih berbeda, tetapi namanya satu-satunya se-provinsi
        $this->assertSame('20318068', $directory->matchByName('SD Negeri 1 Cendono', '33.08', '33')['code'] ?? null);
    }

    public function testMatchByNameRefusesToGuess(): void
    {
        $this->import();
        $directory = $this->directory();

        $this->assertNull($directory->matchByName('SD Negeri 1 Karanggedang', '33.03', '33'), 'dua sekolah resmi bernama sama');
        $this->assertNull($directory->matchByName('SD 2 Cendono', '33.19', '33'), 'SD 2 bukan SD 1 atau SD 4');
        $this->assertNull($directory->matchByName('SD Cendono', '33.19', '33'));
        $this->assertNull($directory->matchByName('SMP 1 Cendono', '33.19', '33'));
        $this->assertNull($directory->matchByName('SD 1', '33.02', '33'), 'jenjang + angka saja tidak ditautkan lewat aturan kecamatan');
        $this->assertSame('20301004', $directory->matchByName('SDN 1 Pekuncen', '33.02', '33')['code'] ?? null, 'nama lengkap tetap cocok persis');
    }

    public function testResolveByNpsnUsesOfficialName(): void
    {
        $this->import();

        $result = $this->directory()->resolve(['npsn' => '20318068', 'name' => 'terserah'] + self::KUDUS);

        $this->assertTrue($result['verified']);
        $this->assertSame('SD 1 CENDONO', $result['name']);
        $this->assertSame((int) $this->school('20318068')['id'], $result['id']);
    }

    public function testResolveLinksClearTypedNames(): void
    {
        $this->import();

        $result = $this->directory()->resolve(['npsn' => '', 'name' => 'SD NEGERI 1 CENDONO'] + self::KUDUS);

        $this->assertTrue($result['verified']);
        $this->assertSame('SD 1 CENDONO', $result['name']);
        $this->assertSame(8, $this->sqlite->table('schools')->countAllResults(), 'tidak ada baris baru');
    }

    public function testResolveKeepsOneUnverifiedRowPerVariant(): void
    {
        $this->import();
        $directory = $this->directory();

        $first  = $directory->resolve(['name' => 'SD Tunas Harapan'] + self::KUDUS);
        $second = $directory->resolve(['name' => '  sd  tunas HARAPAN '] + self::KUDUS);
        $other  = $directory->resolve(['name' => 'SD Tunas Harapan', 'district_code' => '33.20'] + self::KUDUS);
        $abroad = $directory->resolve(['name' => 'Sekolah Indonesia Kuala Lumpur', 'country_code' => 'XX', 'province_code' => '', 'district_code' => '']);

        $this->assertFalse($first['verified']);
        $this->assertSame('SD Tunas Harapan', $first['name']);
        $this->assertSame($first['id'], $second['id']);
        $this->assertNotSame($first['id'], $other['id'], 'kab/kota berbeda = entri berbeda');

        $row = $this->sqlite->table('schools')->where('id', $abroad['id'])->get()->getRowArray();
        $this->assertSame(['XX', null, '0'], [$row['country_code'], $row['province_code'], (string) $row['is_verified']]);
        $this->assertSame(['id' => null, 'name' => null, 'verified' => false], $directory->resolve(['name' => '   '] + self::KUDUS));
    }

    // ------------------------------------------------------------------ admin

    public function testSearch(): void
    {
        $this->import();
        $directory = $this->directory();
        $sd1       = (int) $this->school('20318068')['id'];
        $this->sqlite->table('participants')->insertBatch([
            ['username' => 'a', 'school_id' => $sd1, 'deleted_at' => null],
            ['username' => 'b', 'school_id' => $sd1, 'deleted_at' => null],
            ['username' => 'c', 'school_id' => $sd1, 'deleted_at' => '2026-01-01 00:00:00'],
        ]);

        $this->assertSame(['SD 1 CENDONO'], array_column($directory->search('20318068'), 'name'));
        $this->assertSame(['SD 1 CENDONO'], array_column($directory->search('sdn 1 cendono'), 'name'), '1 tidak menemukan SD 4 / SD 11');
        $this->assertCount(4, $directory->search('dawe'), 'kecamatan');
        $this->assertCount(4, $directory->search('cendono', ['district_code' => '33.19']), 'nama atau desa');
        $this->assertSame([], $directory->search('cendono', ['district_code' => '33.03']));
        $this->assertSame(2, $directory->search('20318068')[0]['participant_count'], 'peserta terhapus tidak dihitung');
    }

    public function testMergeMovesParticipantsAndStaff(): void
    {
        $this->import();
        $directory = $this->directory();
        $typed     = $directory->resolve(['name' => 'SD Cendono Satu'] + self::KUDUS);
        $official  = (int) $this->school('20318068')['id'];

        $this->sqlite->table('participants')->insertBatch([
            ['username' => 'a', 'school_id' => $typed['id'], 'school_name_snapshot' => 'SD Cendono Satu'],
            ['username' => 'b', 'school_id' => $typed['id'], 'school_name_snapshot' => 'sd cendono satu'],
        ]);
        $this->sqlite->table('staff_users')->insert(['username' => 'guru', 'school_id' => $typed['id']]);

        $moved = $directory->merge($typed['id'], $official);

        $this->assertSame(['participants' => 2, 'staff' => 1], $moved);
        $this->assertSame(['SD 1 CENDONO'], array_unique(array_column(
            $this->sqlite->table('participants')->where('school_id', $official)->get()->getResultArray(),
            'school_name_snapshot',
        )));
        $this->assertSame((string) $official, (string) $this->sqlite->table('staff_users')->get()->getRow('school_id'));

        $source = $this->sqlite->table('schools')->where('id', $typed['id'])->get()->getRowArray();
        $this->assertSame(['0', (string) $official], [(string) $source['is_active'], (string) $source['merged_into_id']]);
        $this->assertSame(1, $this->sqlite->table('audit_logs')->where('action', 'school_merge')->countAllResults());

        // entri yang sudah digabung menjadi alias: ketikan yang sama langsung ke tujuan
        $again = $directory->resolve(['name' => 'sd cendono SATU'] + self::KUDUS);
        $this->assertSame(['id' => $official, 'name' => 'SD 1 CENDONO', 'verified' => true], $again);
    }

    public function testMergeNeverMovesAnOfficialSchool(): void
    {
        $this->import();

        $this->expectException(InvalidArgumentException::class);
        $this->directory()->merge((int) $this->school('20337844')['id'], (int) $this->school('20318068')['id']);
    }

    public function testVerifyNeedsAnUnusedNpsn(): void
    {
        $this->import();
        $directory = $this->directory();
        $typed     = $directory->resolve(['name' => 'SD Tunas Harapan'] + self::KUDUS);

        try {
            $directory->verify($typed['id'], '20318068');
            $this->fail('NPSN milik sekolah lain harus ditolak');
        } catch (InvalidArgumentException $e) {
            $this->assertStringContainsString('SD 1 CENDONO', $e->getMessage());
        }

        $verified = $directory->verify($typed['id'], '69990001', 'SD TUNAS HARAPAN');

        $this->assertSame(['69990001', 'SD TUNAS HARAPAN', '1'], [$verified['code'], $verified['name'], (string) $verified['is_verified']]);
        $this->assertSame($typed['id'], (int) $directory->findByNpsn('69990001')['id'], 'siswa berikutnya bisa memakai NPSN ini');
        $this->assertSame(1, $this->sqlite->table('audit_logs')->where('action', 'school_verify')->countAllResults());
    }

    public function testAutoMergeLinksOnlyClearMatches(): void
    {
        $directory = $this->directory();
        // diketik sebelum daftar resmi dipasang
        $clear     = $directory->resolve(['name' => 'SDN 01 Cendono'] + self::KUDUS);
        $ambiguous = $directory->resolve(['name' => 'SD N 1 Karanggedang', 'district_code' => '33.03'] + self::KUDUS);
        $this->sqlite->table('participants')->insert(['username' => 'a', 'school_id' => $clear['id']]);

        $this->import();
        $pairs = $this->directory()->autoMerge(true);
        $this->assertSame([['SDN 01 Cendono', 'SD 1 CENDONO']], array_map(static fn (array $pair): array => [$pair['from']['name'], $pair['to']['name']], $pairs));
        $this->assertSame((string) $clear['id'], (string) $this->sqlite->table('participants')->get()->getRow('school_id'), 'uji coba tidak menulis');

        $this->directory()->autoMerge();

        $this->assertSame((string) $this->school('20318068')['id'], (string) $this->sqlite->table('participants')->get()->getRow('school_id'));
        $this->assertSame(1, (int) $this->sqlite->table('schools')->where('id', $ambiguous['id'])->get()->getRow('is_active'));
    }

    public function testSuggestionsPreferOfficialSchoolsSharingNameWords(): void
    {
        $this->import();
        $directory = $this->directory();
        $typed     = $directory->resolve(['name' => 'SD Karanggedang 1', 'district_code' => '33.03'] + self::KUDUS);
        $row       = $this->sqlite->table('schools')->where('id', $typed['id'])->get()->getRowArray();

        $this->assertSame(['20302001', '20302002'], array_column($directory->suggestions($row), 'code'));
    }

    // ------------------------------------------------------------------ bantu

    private function directory(): SchoolDirectory
    {
        return new SchoolDirectory($this->sqlite);
    }

    /** @return array<string, int|list<string>> */
    private function import(): array
    {
        return (new SchoolImporter($this->sqlite))->import($this->csv(self::OFFICIAL));
    }

    /** @param list<list<string>> $rows */
    private function csv(array $rows): string
    {
        return $this->files[] = $this->writeSchoolCsv($rows);
    }

    /** @return array<string, mixed>|null */
    private function school(string $npsn): ?array
    {
        return $this->sqlite->table('schools')->where('code', $npsn)->get()->getRowArray();
    }
}
