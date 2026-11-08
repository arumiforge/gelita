<?php

use App\Services\ExportService;
use App\Services\ParticipantCopyService;
use App\Services\RawDataService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Follow-up flags are never stored and never leave the head researcher's page
 * (research revision 10, part 4: 30 §6, K7, §19.1 `RiskFlagsNeverExportedTest`).
 *
 * @internal
 */
final class RiskFlagsNeverExportedTest extends CIUnitTestCase
{
    public function testNoFlagSheetColumnOrDimensionAnywhere(): void
    {
        $pattern = '/flag|tindak|follow|priority|didahulukan/i';

        foreach ([ExportService::SHEETS, RawDataService::DIMENSIONS, ParticipantCopyService::SHEETS] as $list) {
            foreach ($list as $name) {
                $this->assertDoesNotMatchRegularExpression($pattern, $name);
            }
        }

        $migration = (string) file_get_contents(APPPATH . 'Database/Migrations/2026-01-01-003800_AddConsentAndDataRights.php');
        $this->assertDoesNotMatchRegularExpression("/'[a-z_]*(?:flag|priority)[a-z_]*'\\s*=>/", $migration, 'no stored flag column');

        $monitoring = (string) file_get_contents(APPPATH . 'Views/admin/monitoring.php') . file_get_contents(APPPATH . 'Services/AnalyticsService.php');
        $this->assertStringNotContainsString('riskFollowUp', $monitoring);
        $this->assertStringNotContainsString('risk_upset', $monitoring);
    }
}
