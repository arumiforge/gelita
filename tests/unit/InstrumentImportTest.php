<?php

use App\Libraries\CatalogImporter;
use App\Libraries\InstrumentImporter;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * Instrument data files and their import (research revision 10, part 8:
 * 34 §6, K2, §15.1 `InstrumentImportTest`).
 *
 * @internal
 */
final class InstrumentImportTest extends CIUnitTestCase
{
    use ResetTables;

    private \CodeIgniter\Database\BaseConnection $conn;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createResetTables();
        $this->dir  = sys_get_temp_dir() . '/gelita-instr-' . bin2hex(random_bytes(4));
        mkdir($this->dir);

        foreach (InstrumentImporter::INSTRUMENTS as $name) {
            copy(InstrumentImporter::defaultDir() . '/' . $name . '.php', $this->dir . '/' . $name . '.php');
        }
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->dir . '/*') ?: []);
        rmdir($this->dir);
        service('cache')->clean();
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testShippedFilesImportWithTheirCounts(): void
    {
        $result = (new InstrumentImporter($this->conn))->import();

        $this->assertSame([], $result['errors']);
        $this->assertSame(['imported', 'imported', 'imported'], array_column($result['files'], 'status'));
        $this->assertSame(69, $result['files']['tes']['items'], '64 items and 5 examples');
        $this->assertSame(20, $result['files']['angket']['items'], 'ang-01–ang-17 and ang-21–ang-23');
        $this->assertSame(13, $result['files']['sikap']['items']);

        $versions = $this->conn->table('instrument_versions')->get()->getResultArray();
        $this->assertSame(['tes-0.1', 'angket-0.1', 'sikap-0.1'], array_column($versions, 'version'));
        $this->assertSame(['needs_verification'], array_values(array_unique(array_column($versions, 'status'))));

        foreach (['mb' => 16, 'ae' => 8, 'bd' => 8] as $part => $count) {
            foreach (['A', 'B'] as $form) {
                $this->assertSame($count, $this->conn->table('instrument_items')->where('part', $part)->where('form', $form)->countAllResults(), "{$part} {$form}");
            }
        }

        $units = 0;

        foreach ($this->conn->table('instrument_items')->where('version_id', 2)->whereIn('part', InstrumentImporter::QUESTIONNAIRE_SECTIONS)->get()->getResultArray() as $item) {
            $units += count(json_decode($item['units_json'], true));
        }

        $this->assertSame(41, $units);
    }

    public function testSameContentWritesNothingAndChangedContentNeedsANewVersion(): void
    {
        $importer = new InstrumentImporter($this->conn, $this->dir);
        $importer->import();
        $this->assertSame('unchanged', $importer->import(['sikap'])['files']['sikap']['status']);
        $this->assertSame(1, $this->conn->table('instrument_versions')->where('instrument', 'sikap')->countAllResults());

        $old = (int) $this->conn->table('instrument_versions')->where('instrument', 'sikap')->get()->getRow('id');

        $this->rewrite('sikap', static function (array $data): array {
            $data['items']['skp-01']['text'] = 'Aku bangga sekali pada budaya daerah Kedu.';

            return $data;
        });
        $this->assertSame(['sikap: Isi berubah, naikkan versinya.'], $importer->import(['sikap'])['errors']);

        $this->rewrite('sikap', static fn (array $data): array => ['version' => 'sikap-0.2'] + $data);
        $this->assertSame('imported', $importer->import(['sikap'])['files']['sikap']['status']);
        $rows = $this->conn->table('instrument_versions')->where('instrument', 'sikap')->orderBy('id')->get()->getResultArray();
        $this->assertSame([[$old, 0], [(int) $rows[1]['id'], 1]], array_map(static fn (array $r): array => [(int) $r['id'], (int) $r['is_active']], $rows), 'the old version stays');
        $this->assertSame(13, $this->conn->table('instrument_items')->where('version_id', $old)->countAllResults(), 'items never change in place');
    }

    public function testChecksRefuseTheWholeFile(): void
    {
        $importer = new InstrumentImporter($this->conn, $this->dir);

        $this->rewrite('tes', static function (array $data): array {
            $data['items']['tes-mb-B07']['type'] = 'pg';                    // A/B equality
            unset($data['items']['tes-ae-A03']['key']['errors']['A']);     // a wrong choice without code
            $data['items']['tes-bd-A01']['stimulus'] = 'tes-bd-A-G9';       // unknown scene

            return $data;
        });
        $this->rewrite('sikap', static function (array $data): array {
            $data['items']['skp-04']['direction'] = '-';

            return $data;
        });
        $this->rewrite('angket', static function (array $data): array {
            unset($data['items']['ang-17']);

            return $data;
        });

        $result = $importer->import();
        $errors = implode("\n", $result['errors']);
        $this->assertSame(['refused', 'refused', 'refused'], array_column($result['files'], 'status'));
        $this->assertStringContainsString('nomor 07: bentuk A dan B', $errors);
        $this->assertStringContainsString('tes-ae-A03: pilihan A belum punya kode kesalahan', $errors);
        $this->assertStringContainsString('tes-bd-A-G9 tidak ada', $errors);
        $this->assertStringContainsString('Butir negatif harus nomor 3, 6, 8, dan 10', $errors);
        $this->assertStringContainsString('41 kolom', $errors);
        $this->assertSame(0, $this->conn->table('instrument_versions')->countAllResults());
    }

    public function testTheCatalogueShipsOnePicturePerImageSlot(): void
    {
        $data  = require InstrumentImporter::defaultDir() . '/tes.php';
        $slots = array_values(array_filter(array_map(static fn (array $s): ?string => $s['image'] ?? null, $data['stimuli'])));
        $dir   = dirname(APPPATH) . '/gelita-katalog/' . array_search('instr.*', CatalogImporter::MOCKUP_DIRS, true);
        $files = array_map(static fn (string $f): string => basename($f, '.webp'), glob($dir . '/instr.*.webp') ?: []);

        $this->assertCount(24, $slots);
        $this->assertEqualsCanonicalizing($slots, $files);
        $this->assertNotNull(\App\Libraries\ImageSlots::label('instr.tes-mb-A-M3'));
    }

    private function rewrite(string $name, callable $change): void
    {
        $file = $this->dir . '/' . $name . '.php';
        $data = $change(require $file);
        file_put_contents($file, '<?php return ' . var_export($data, true) . ';');

        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($file, true);
        }
    }
}
