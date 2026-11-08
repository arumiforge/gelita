<?php

use App\Services\RawDataService;
use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\RawDataWorld;

/**
 * Event times in the download are corrected (research revision 10, part 3:
 * 29 §7.4, K8): `occurred_at_corrected` = occurred_at + clock_offset_ms,
 * `offset_known` 0 when the offset is empty.
 *
 * @internal
 */
final class ExportTimeCorrectionTest extends CIUnitTestCase
{
    use RawDataWorld;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCorrectedTimeAndOffsetKnown(): void
    {
        $this->assertSame(['occurred_at_corrected' => '2026-10-01 08:00:01.500000', 'offset_known' => 1], RawDataService::correctedTime(['occurred_at' => '2026-10-01 08:00:00', 'clock_offset_ms' => 1500]));
        $this->assertSame(['occurred_at_corrected' => '2026-10-01 07:59:58.750000', 'offset_known' => 1], RawDataService::correctedTime(['occurred_at' => '2026-10-01 08:00:00', 'clock_offset_ms' => -1250]));
        $this->assertSame(['occurred_at_corrected' => '2026-10-01 08:00:00', 'offset_known' => 0], RawDataService::correctedTime(['occurred_at' => '2026-10-01 08:00:00', 'clock_offset_ms' => null]));
        $this->assertSame(1, RawDataService::correctedTime(['occurred_at' => '2026-10-01 08:00:00', 'clock_offset_ms' => 0])['offset_known'], 'server events: offset 0, known');
    }

    public function testNavigationRowsCarryTheCorrectedTime(): void
    {
        $db = $this->createRawDataWorld();
        $db->table('game_event_logs')->where('session_id', 2)->where('event_type', 'screen_viewed')->update(['clock_offset_ms' => 300_000]);
        $db->table('game_event_logs')->where('session_id', 1)->where('event_type', 'screen_viewed')->update(['clock_offset_ms' => null]);

        $rows = iterator_to_array(service('rawDataService')->rows('navigasi', ['study_id' => 1], StaffScope::head()), false);
        $byCode = array_column($rows, null, 'session_code');

        $this->assertSame(['2026-10-02T09:07:00.000+07:00', 1], [$byCode['S-BUDI']['occurred_at_corrected'], $byCode['S-BUDI']['offset_known']]);
        $this->assertSame(['2026-10-01T08:02:00.000+07:00', 0], [$byCode['S-ANI']['occurred_at_corrected'], $byCode['S-ANI']['offset_known']]);
        $this->assertArrayNotHasKey('occurred_at', $byCode['S-ANI']);
    }
}
