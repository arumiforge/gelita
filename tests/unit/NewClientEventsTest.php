<?php

use App\Models\GameSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * The new client events of research revision 10, part 3 (29 §6.2–6.3):
 * `empty_tap` and `passage_reopened` arrive through the normal event queue
 * with their references and payload; the clients that send them.
 *
 * @internal
 */
final class NewClientEventsTest extends CIUnitTestCase
{
    use ResetTables;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEmptyTapAndPassageReopenedAreAccepted(): void
    {
        $db = $this->createResetTables(['participants', 'game_sessions', 'game_event_logs']);
        $db->table('participants')->insert(['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x']);
        $db->table('game_sessions')->insert(['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active']);
        $session = model(GameSessionModel::class)->find(1);

        $result = service('eventService')->ingest($session, [
            ['event_type' => 'empty_tap', 'client_event_id' => 'e-1', 'level_id' => 1, 'node_id' => 4, 'attempt_id' => 7, 'item_id' => 31,
             'payload' => ['x_pct' => 42.5, 'y_pct' => 61.5, 'round' => 1, 't_ms' => 12_400]],
            ['event_type' => 'empty_tap', 'client_event_id' => 'e-2', 'node_id' => 4, 'attempt_id' => 7, 'item_id' => 31,
             'payload' => ['round' => 1, 'overflow' => 12, 't_ms' => 30_000]],
            ['event_type' => 'passage_reopened', 'client_event_id' => 'p-1', 'node_id' => 3, 'attempt_id' => 8,
             'payload' => ['via' => 'label', 'passage' => 2, 't_ms' => 9_800]],
        ], ['client_sent_at' => '2026-11-04T01:00:00.000Z']);

        $this->assertSame(3, $result['accepted']);

        $rows = $db->table('game_event_logs')->orderBy('id')->get()->getResultArray();
        $this->assertSame([4, 7, 31], [(int) $rows[0]['challenge_node_id'], (int) $rows[0]['challenge_attempt_id'], (int) $rows[0]['challenge_item_id']]);
        $this->assertSame(['x_pct' => 42.5, 'y_pct' => 61.5, 'round' => 1, 't_ms' => 12_400], json_decode((string) $rows[0]['payload_json'], true));
        $this->assertSame(12, json_decode((string) $rows[1]['payload_json'], true)['overflow']);
        $this->assertSame(['via' => 'label', 'passage' => 2, 't_ms' => 9_800], json_decode((string) $rows[2]['payload_json'], true));
    }

    public function testClientsSendTheNewEvents(): void
    {
        $cari = (string) file_get_contents(FCPATH . 'assets/js/engines/cari.js');
        $this->assertStringContainsString("emit('empty_tap'", $cari);
        $this->assertStringContainsString('CONFIG.emptyTapLimit', $cari);
        $this->assertStringContainsString('ready_ms: readyAt', $cari);
        $this->assertStringNotContainsString('duration_ms', $cari, 'last_input_ms since the challenge opened replaces it');
        $this->assertSame(30, js_config('id')['emptyTapLimit']);

        $challenge = (string) file_get_contents(FCPATH . 'assets/js/game/challenge.js');
        $this->assertStringContainsString("emit('passage_reopened'", $challenge);
        $this->assertStringContainsString('if (!inputSeen) return;', $challenge, 'only after the first input');
        $this->assertStringNotContainsString("emit('answer_selected'", $challenge);

        $boleh = (string) file_get_contents(FCPATH . 'assets/js/engines/boleh.js');
        $this->assertStringContainsString("ctx.passageReopened('label'", $boleh);

        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString("window.addEventListener('pagehide', reportAllListened, true);", $audio);
        $this->assertStringContainsString("if (wasPlaying && this.playIndex > 0 && !this.completed) this.send('pause');", $audio);
    }
}
