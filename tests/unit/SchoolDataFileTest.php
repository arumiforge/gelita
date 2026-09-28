<?php

use App\Libraries\SchoolImporter;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Daftar sekolah resmi Jawa Tengah yang ikut dikirim (docs/sekolah/):
 * format, NPSN, kode wilayah, dan contoh nyata dari penelitian.
 *
 * @internal
 */
final class SchoolDataFileTest extends CIUnitTestCase
{
    /** @var array<string, array<string, mixed>>|null */
    private static ?array $rows = null;

    /** @return array<string, array<string, mixed>> NPSN => kolom `schools` */
    private function rows(): array
    {
        // read() memeriksa header, NPSN 8 angka & unik, kode kab, jenjang
        return self::$rows ??= (new SchoolImporter())->read(SchoolImporter::DEFAULT_FILE);
    }

    public function testFileCoversCentralJavaPrimaryAndJuniorSchools(): void
    {
        $rows   = $this->rows();
        $stages = array_count_values(array_column($rows, 'stage'));
        $levels = array_count_values(array_column($rows, 'level'));

        $this->assertGreaterThan(25000, count($rows));
        $this->assertGreaterThan(20000, $stages['sd']);
        $this->assertGreaterThan(5000, $stages['smp']);
        $this->assertGreaterThan(100, $stages['slb']);

        foreach (['SD', 'MI', 'SMP', 'MTS', 'SLB'] as $level) {
            $this->assertArrayHasKey($level, $levels, $level);
        }

        $this->assertSame(['33'], array_values(array_unique(array_column($rows, 'province_code'))));
        $this->assertSame([], array_diff(array_unique(array_column($rows, 'status')), ['NEGERI', 'SWASTA']));
    }

    public function testEveryDistrictExistsInTheRegionFile(): void
    {
        $regions   = json_decode((string) file_get_contents(FCPATH . 'assets/data/wilayah-id.json'), true);
        $districts = [];

        foreach ($regions['provinces'] as $province) {
            if ($province['code'] === '33') {
                $districts = array_column($province['districts'], 'code');
            }
        }

        $used = array_values(array_unique(array_column($this->rows(), 'district_code')));
        sort($used);
        sort($districts);

        $this->assertSame($districts, $used, 'ke-35 kab/kota Jawa Tengah terisi, tanpa kode asing');
    }

    public function testResearcherExampleIsPresent(): void
    {
        $school = $this->rows()['20318068'] ?? null;

        $this->assertNotNull($school);
        $this->assertSame(
            ['SD 1 CENDONO', 'SD', 'NEGERI', '33.19', 'DAWE', 'CENDONO', 'SD 1 CENDONO'],
            [$school['name'], $school['level'], $school['status'], $school['district_code'], $school['subdistrict_name'], $school['village_name'], $school['match_key']],
        );
    }

    public function testMetaDescribesTheFile(): void
    {
        $meta = json_decode((string) file_get_contents(dirname(SchoolImporter::DEFAULT_FILE) . '/sekolah-jateng.meta.json'), true);

        $stages = array_count_values(array_column($this->rows(), 'stage'));
        $meta['by_stage'] = array_map('intval', $meta['by_stage']);
        ksort($stages);
        ksort($meta['by_stage']);

        $this->assertSame(count($this->rows()), $meta['total']);
        $this->assertSame($stages, $meta['by_stage']);
        $this->assertStringContainsString('bahrye/api-sekolah', $meta['source']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', (string) $meta['upstream_updated']);
    }
}
