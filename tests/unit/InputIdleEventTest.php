<?php

use App\Libraries\EventCatalog;
use App\Models\GameSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * Input pauses (research revision 10, part 7: 33 §5.2, K4, §13.1
 * `InputIdleEventTest` and the client part of `activity.test.mjs`):
 * `input_idle` is a research event the client sends, its payload is clamped,
 * and core/idle-tracker.js logs one event per pause of 20 s or more, cut by a
 * hidden page.
 *
 * @internal
 */
final class InputIdleEventTest extends CIUnitTestCase
{
    use ResetTables;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCatalogAndConfig(): void
    {
        $this->assertTrue(EventCatalog::isResearch('input_idle'));
        $this->assertFalse(EventCatalog::isServerOnly('input_idle'));
        $this->assertContains('input_idle', EventCatalog::NAVIGATION);
        $this->assertSame(20000, config('Gelita')->inputIdleLogMs);
        $this->assertNotSame('input_idle', admin_label('event', 'input_idle'), 'the panel has a label');
    }

    public function testIngestClampsThePayload(): void
    {
        helper(['gelita', 'ui']);
        $db = $this->createResetTables(['participants', 'game_sessions', 'game_event_logs']);
        $db->table('participants')->insert(['participant_code' => 'GLT-000001', 'username' => 'a', 'password_hash' => 'x']);
        $db->table('game_sessions')->insert(['session_code' => 'S1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active']);

        $result = service('eventService')->ingest(model(GameSessionModel::class)->find(1), [
            ['event_type' => 'input_idle', 'client_event_id' => 'i-1', 'occurred_at' => '2026-11-04T08:00:40.000Z',
                'payload' => ['idle_ms' => 25400.7, 'started_at' => '2026-11-04T08:00:15.000Z', 'end' => 'hidden', 'screen' => 'challenge', 'extra' => 'x']],
            ['event_type' => 'input_idle', 'client_event_id' => 'i-2', 'occurred_at' => '2026-11-04T08:01:40.000Z',
                'payload' => ['idle_ms' => -5, 'started_at' => 'tomorrow', 'end' => 'nap', 'screen' => '<b>']],
        ]);

        $this->assertSame(2, $result['accepted']);
        $payloads = array_map(static fn (array $r): array => json_decode((string) $r['payload_json'], true), $db->table('game_event_logs')->orderBy('id')->get()->getResultArray());

        $this->assertSame(['idle_ms' => 25401, 'started_at' => '2026-11-04T08:00:15.000Z', 'end' => 'hidden', 'screen' => 'challenge'], $payloads[0]);
        $this->assertSame(['idle_ms' => 0, 'started_at' => null, 'end' => null, 'screen' => null], $payloads[1]);
    }

    public function testClientListensToInputButNotToMouseMovesWithoutAButton(): void
    {
        $source = (string) file_get_contents(FCPATH . 'assets/js/core/activity.js');

        $this->assertStringContainsString("['pointerdown', 'keydown', 'wheel', 'scroll']", $source);
        $this->assertStringContainsString('if (event.buttons) onInput();', $source);
        $this->assertStringNotContainsString("'mousemove'", $source);
        $this->assertStringContainsString("import('./core/activity.js')", (string) file_get_contents(FCPATH . 'assets/js/game.js'));
    }

    public function testTrackerLogsOnePausePerGapAndHiddenCutsIt(): void
    {
        $node = trim((string) shell_exec('command -v node 2>/dev/null'));

        if ($node === '') {
            $this->markTestSkipped('Node is not installed.');
        }

        $module = 'file://' . FCPATH . 'assets/js/core/idle-tracker.js';
        $script = <<<JS
            const { createIdleTracker } = await import('{$module}');
            const t = createIdleTracker(20000, 0);
            const out = [];
            out.push(t.input(5000));      // 5 s: nothing
            out.push(t.input(30000));     // 25 s pause: one event
            out.push(t.input(31000));     // 1 s: nothing
            out.push(t.hide(60000));      // 29 s, cut by hidden
            out.push(t.input(70000));     // input while hidden: nothing
            out.push(t.show(400000));     // visible again: clock restarts
            out.push(t.input(410000));    // 10 s after showing: nothing
            out.push(t.leave(440000));    // 30 s, page left
            console.log(JSON.stringify(out));
            JS;

        $output = shell_exec(escapeshellarg($node) . ' --input-type=module -e ' . escapeshellarg($script) . ' 2>&1');
        $events = json_decode(trim((string) $output), true);

        $this->assertIsArray($events, (string) $output);
        $this->assertSame([
            null,
            ['idle_ms' => 25000, 'started_ms' => 5000, 'end' => 'input'],
            null,
            ['idle_ms' => 29000, 'started_ms' => 31000, 'end' => 'hidden'],
            null,
            null,
            null,
            ['idle_ms' => 30000, 'started_ms' => 410000, 'end' => 'pagehide'],
        ], $events);
    }
}
