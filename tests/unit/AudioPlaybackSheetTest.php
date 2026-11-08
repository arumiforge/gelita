<?php

use App\Models\GameSessionModel;
use App\Services\StaffScope;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\RawDataWorld;

/**
 * `pause` writes its audio row but no raw event, and the `bantuan` sheet has
 * one row per playback with the largest listened_ms (research revision 10,
 * part 3: 29 §5.4, K6).
 *
 * @internal
 */
final class AudioPlaybackSheetTest extends CIUnitTestCase
{
    use RawDataWorld;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testOneRowPerPlaybackWithTheLargestListenedTime(): void
    {
        $db      = $this->createRawDataWorld();
        $session = model(GameSessionModel::class)->find(2);
        $audio   = static fn (string $id, string $action, ?int $ms, string $at): array => [
            'client_event_id' => $id, 'audio_asset_id' => 1, 'action' => $action, 'listened_ms' => $ms, 'completed' => $action === 'complete', 'occurred_at' => $at,
        ];

        $result = service('eventService')->recordAudioBatch($session, [
            $audio('a-1', 'autoplay', null, '2026-10-02T02:03:00.000Z'),
            $audio('a-2', 'pause', 1200, '2026-10-02T02:03:01.200Z'),
            $audio('a-3', 'pause', 2600, '2026-10-02T02:03:05.000Z'),
            $audio('a-4', 'replay', null, '2026-10-02T02:03:06.000Z'),
            $audio('a-5', 'complete', 4100, '2026-10-02T02:03:10.100Z'),
        ]);

        $this->assertSame(5, $result['accepted']);
        $this->assertSame(0, $db->table('game_event_logs')->where('session_id', 2)->where('event_type', 'audio_pause')->countAllResults(), 'no raw pause event');
        $this->assertSame(2, $db->table('audio_usage_events')->where('session_id', 2)->where('action', 'pause')->countAllResults(), 'but the audio rows');
        $this->assertSame(3, $db->table('game_event_logs')->where('session_id', 2)->like('event_type', 'audio_', 'after')->countAllResults());

        // A re-sent pause (lost answer) is a duplicate, not a second row
        $again = service('eventService')->recordAudioBatch($session, [$audio('a-2', 'pause', 1200, '2026-10-02T02:03:01.200Z')]);
        $this->assertSame(['accepted' => 0, 'duplicate' => 1], array_intersect_key($again, ['accepted' => 1, 'duplicate' => 1]));

        $rows = iterator_to_array(service('rawDataService')->rows('bantuan', ['study_id' => 1, 'participant_id' => 2], StaffScope::head()), false);
        $this->assertSame(
            [['autoplay', 1, 2600, 0], ['replay', 2, 4100, 1]],
            array_map(static fn (array $r): array => [$r['action'], $r['play_index'], $r['listened_ms'], $r['completed']], $rows),
        );
    }
}
