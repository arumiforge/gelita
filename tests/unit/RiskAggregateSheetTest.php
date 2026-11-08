<?php

use App\Services\ExportService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The `risiko_gabungan` sheet (research revision 10, part 3: 29 §7.6, K9).
 * Its source, the module sittings of kind `risk`, comes with change no. 22
 * (part 8, 34 §8.3); part 3 only fixes the place of the sheet. Until then
 * no risk answer can reach any sheet.
 *
 * @internal
 */
final class RiskAggregateSheetTest extends CIUnitTestCase
{
    public function testNoRiskAnswersBeforeTheTestModule(): void
    {
        $this->assertNotContains('risiko_gabungan', ExportService::SHEETS, 'added by part 8 after kode_pilihan and the turunan_/tes_ sheets');

        foreach (ExportService::SHEETS as $sheet) {
            foreach ((new ExportService())->columns($sheet, true, true) as $column) {
                $this->assertDoesNotMatchRegularExpression('/^ang[-_]2[1-3]/', $column, "{$sheet}.{$column}");
            }
        }
    }

    public function testShapeOfTheAggregateSheet(): void
    {
        $this->markTestSkipped('Activated with the test module (change no. 22, part 8): cells under 5 students read "<5".');
    }
}
