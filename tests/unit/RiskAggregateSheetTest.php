<?php

use App\Services\ExportService;
use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\InstrumentWorld;

/**
 * The `risiko_gabungan` sheet (research revision 10, part 3: 29 §7.6, K9).
 * Its source is the module sittings of kind `risk` (part 8, 34 §8.3): counts
 * per item and choice for the study, then per school code, cells under 5
 * students read `<5`, and no sheet carries a per-student risk answer.
 *
 * @internal
 */
final class RiskAggregateSheetTest extends CIUnitTestCase
{
    use InstrumentWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createInstrumentWorld();
    }

    protected function tearDown(): void
    {
        $this->dropInstrumentWorld();
        parent::tearDown();
    }

    public function testNoRiskAnswersInAnyPerStudentSheet(): void
    {
        $sheets = ExportService::SHEETS;
        $this->assertContains('risiko_gabungan', $sheets);
        $this->assertSame(['kode_pilihan', 'tes_jawaban', 'tes_bagian', 'angket', 'sikap', 'risiko_gabungan', 'izin'], array_slice($sheets, (int) array_search('kode_pilihan', $sheets, true), 7), '29 §7.2 order');

        foreach (array_diff($sheets, ['risiko_gabungan', 'kamus']) as $sheet) {
            foreach ((new ExportService())->columns($sheet, true, true) as $column) {
                $this->assertDoesNotMatchRegularExpression('/^(ang[-_]2[1-3]|risk_(upset|stranger|offered|reconsent)|support_)/', $column, "{$sheet}.{$column}");
            }
        }

        // A risk sitting never shows up in tes_jawaban, tes_bagian, angket, sikap
        $this->riskAnswers($this->conn, 1, '2', '1');
        $scope = StaffScope::of(true, true);

        foreach (['tes_jawaban', 'tes_bagian', 'angket', 'sikap'] as $sheet) {
            $this->assertSame(0, service('rawDataService')->count($sheet, ['study_id' => 1], $scope), $sheet);
        }
    }

    public function testShapeOfTheAggregateSheet(): void
    {
        $ids = [];

        for ($i = 2; $i <= 7; $i++) {
            $ids[$i] = $this->student($this->conn, 'S03-A0' . $i, 'anak' . $i);
        }

        $this->riskAnswers($this->conn, 1, '1');

        foreach ([2, 3, 4, 5, 6] as $i) {
            $this->riskAnswers($this->conn, $ids[$i], '0');
        }

        // Student 7: no risk-items consent → tidak_diberi
        $version = (int) $this->conn->table('instrument_versions')->where('instrument', 'angket')->get()->getRow('id');
        $this->conn->table('instrument_sittings')->insert(['participant_id' => $ids[7], 'study_id' => 1, 'occasion' => 'post', 'kind' => 'risk', 'version_id' => $version, 'risk_offered' => 0, 'started_at' => date('Y-m-d H:i:s'), 'submitted_at' => date('Y-m-d H:i:s')]);

        $rows = iterator_to_array(service('rawDataService')->rows('risiko_gabungan', ['study_id' => 1], StaffScope::of(true, true)), false);
        $this->assertSame(['school_code', 'item_code', 'unit', 'choice', 'students'], array_keys($rows[0]));

        $cell = static function (string $school, string $unit, string $choice) use ($rows): ?string {
            foreach ($rows as $row) {
                if ([$row['school_code'], $row['unit'], $row['choice']] === [$school, $unit, $choice]) {
                    return $row['students'];
                }
            }

            return null;
        };

        $this->assertSame('5', $cell('semua', 'risk_upset', '0'));
        $this->assertSame('<5', $cell('semua', 'risk_upset', '1'), 'one student');
        $this->assertSame('<5', $cell('semua', 'risk_upset', 'tidak_diberi'));
        $this->assertSame('<5', $cell('semua', 'risk_upset', '7'), 'zero is under 5 too');
        $this->assertSame('5', $cell('S03', 'risk_upset', '0'), 'then per school code');

        foreach ($rows as $row) {
            $this->assertStringNotContainsString('S03-A', implode(' ', array_map('strval', $row)), 'never a participant code');
        }
    }
}
