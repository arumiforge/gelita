<?php

use App\Models\GameEventLogModel;
use App\Models\GameSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * `dialogue_advanced` per slide is replaced by one `dialogue_completed` at
 * the last slide (research revision 10, part 3: 29 §5.3, K5); the
 * "Belum didengar" badge reads it.
 *
 * @internal
 */
final class DialogueCompletedTest extends CIUnitTestCase
{
    use ResetTables;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testNarratorSendsOneEventAtTheLastSlideOnly(): void
    {
        $narrator = (string) file_get_contents(FCPATH . 'assets/js/game/narrator.js');

        $this->assertSame(1, substr_count($narrator, "emit('dialogue_completed'"));
        $this->assertMatchesRegularExpression("~if \\(index >= total\\) \\{\\s*emit\\('dialogue_completed', \\{ levelId, payload: \\{ context, total \\} \\}\\);\\s*flush\\(\\);~", $narrator, 'other slides send nothing');
        $this->assertStringNotContainsString('dialogue_advanced', $narrator);
    }

    public function testTheBadgeGoesOnceTheLastSlideIsReached(): void
    {
        $db = $this->createResetTables(['participants', 'game_sessions', 'game_event_logs']);
        $db->table('participants')->insert(['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x']);
        $db->table('game_sessions')->insert(['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active']);
        $session = model(GameSessionModel::class)->find(1);
        $logs    = model(GameEventLogModel::class, false);

        $this->assertSame([], $logs->heardRegionIntros(1));

        $result = service('eventService')->ingest($session, [
            ['event_type' => 'dialogue_completed', 'client_event_id' => 'd-1', 'level_id' => 2, 'payload' => ['context' => 'region_intro', 'total' => 4]],
            ['event_type' => 'dialogue_completed', 'client_event_id' => 'd-2', 'level_id' => 3, 'payload' => ['context' => 'level_open', 'total' => 16]],
            ['event_type' => 'dialogue_advanced', 'client_event_id' => 'd-3', 'level_id' => 1, 'payload' => ['index' => 4, 'total' => 4, 'context' => 'region_intro']],
        ]);

        $this->assertSame(2, $result['accepted'], 'the old per-slide event is refused');
        $this->assertSame([2], $logs->heardRegionIntros(1));
    }
}
