<?php

use App\Models\GameEventLogModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * "Belum didengar" badge on the Kedu map: GameEventLogModel::heardRegionIntros()
 * reads the participant's `dialogue_completed` events from the database
 * (sent once at the last slide, revision 10, part 3: 29 K5).
 *
 * MySQL 8 stores a JSON column in binary form and returns it re-serialised
 * with a space after every colon (`{"index": 4, "context": "region_intro"}`);
 * MariaDB keeps the text as written. The query must find both spellings.
 *
 * @internal
 */
final class HeardRegionIntrosTest extends CIUnitTestCase
{
    use ResetTables;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testFindsLastSlideEventsWhateverTheJsonSpacing(): void
    {
        $db = $this->createResetTables(['game_event_logs']);

        $rows = [
            // MariaDB: the text as json_encode() wrote it
            [1, 7, '{"context":"region_intro","total":4}'],
            // MySQL 8: re-serialised with spaces
            [2, 7, '{"context": "region_intro", "total": 4}'],
            // Another story context
            [3, 7, '{"total": 16, "context": "level_open"}'],
            // Another participant
            [3, 8, '{"context":"region_intro","total":4}'],
        ];

        foreach ($rows as $n => [$levelId, $participantId, $payload]) {
            $db->table('game_event_logs')->insert([
                'event_uuid'      => sprintf('00000000-0000-4000-8000-%012d', $n),
                'client_event_id' => 'c' . $n,
                'session_id'      => $participantId,
                'participant_id'  => $participantId,
                'level_id'        => $levelId,
                'event_type'      => 'dialogue_completed',
                'occurred_at'     => '2026-10-12 09:00:00',
                'payload_json'    => $payload,
            ]);
        }

        $this->assertSame([1, 2], model(GameEventLogModel::class, false)->heardRegionIntros(7));
    }
}
