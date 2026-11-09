<?php

use App\Models\GameSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Clock offset on narration rows (research revision 10, part 7: 33 §5.3,
 * K10, §13.1 `AudioOffsetTest`): `audio_usage_events.clock_offset_ms` is the
 * batch's offset, the same as on the raw events, also for `pause` (which
 * has no raw event), and the `bantuan` sheet corrects its times with it.
 *
 * @internal
 */
final class AudioOffsetTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    private int $asset;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
        $this->conn = $this->createGradeFiveWorld();
        $this->conn->table('audio_assets')->insert(['media_asset_id' => 900, 'locale' => 'id', 'context_code' => 'uji', 'transcript' => 'Halo.']);
        $this->asset = (int) $this->conn->insertID();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEveryRowOfTheBatchCarriesTheBatchOffset(): void
    {
        // The device clock is 90 s behind the server
        $sent = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('-90 seconds')->format('Y-m-d\TH:i:s.v\Z');
        $at   = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->modify('-100 seconds')->format('Y-m-d\TH:i:s.v\Z');

        $result = service('eventService')->recordAudioBatch(model(GameSessionModel::class)->find(1), [
            ['client_event_id' => 'a-1', 'audio_asset_id' => $this->asset, 'action' => 'play', 'occurred_at' => $at],
            ['client_event_id' => 'a-2', 'audio_asset_id' => $this->asset, 'action' => 'pause', 'listened_ms' => 4000, 'occurred_at' => $at],
        ], $sent);

        $this->assertSame(2, $result['accepted']);

        $raw  = (int) $this->conn->table('game_event_logs')->where('client_event_id', 'a-1')->get()->getRow('clock_offset_ms');
        $rows = array_map('intval', array_column($this->conn->table('audio_usage_events')->orderBy('id')->get()->getResultArray(), 'clock_offset_ms'));

        $this->assertEqualsWithDelta(90000, $raw, 5000);
        $this->assertSame([$raw, $raw], $rows, 'play and pause carry the raw event offset');

        // The bantuan sheet corrects the playback time with it (no longer NULL)
        $row = (new App\Services\RawDataService(App\Services\StaffScope::head()))->page('bantuan', [], 1, 25, null)['rows'][0] ?? null;
        $this->assertNotNull($row);
        $this->assertNotNull($row['occurred_at_corrected']);
    }

    public function testNoClientTimeLeavesTheOffsetEmpty(): void
    {
        service('eventService')->recordAudioBatch(model(GameSessionModel::class)->find(1), [
            ['client_event_id' => 'a-1', 'audio_asset_id' => $this->asset, 'action' => 'play'],
        ]);

        $this->assertNull($this->conn->table('audio_usage_events')->get()->getRow('clock_offset_ms'));
    }
}
