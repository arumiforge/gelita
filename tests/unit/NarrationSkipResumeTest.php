<?php

use App\Libraries\Derived\AttemptCalculator;
use App\Models\AudioUsageEventModel;
use App\Models\GameSessionModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\RawDataWorld;

/**
 * Sound and narration decisions of the owner, 16 November 2026 (04, decisions
 * 1, 2, 3 and 5; proposed `kamus-1.2`):
 * - narration actions `skip` (Lewati) and `resume` (a paused narration goes
 *   on) are accepted and stored as audio_usage_events rows without a raw
 *   event, like `pause`;
 * - play count = `play` + `replay` + `resume`;
 * - background music has its own switch, off by default; correct/wrong
 *   effects are silent in research mode (source checks, as the browser code
 *   has no unit tests).
 *
 * @internal
 */
final class NarrationSkipResumeTest extends CIUnitTestCase
{
    use RawDataWorld;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testSkipAndResumeAreStoredAsNarrationRows(): void
    {
        $this->assertContains('skip', AudioUsageEventModel::ACTIONS);
        $this->assertContains('resume', AudioUsageEventModel::ACTIONS);
        $this->assertNotContains('resume', AudioUsageEventModel::OPENING_ACTIONS, 'a resume goes on with the same playback');
        $this->assertSame(['play', 'replay', 'resume'], AudioUsageEventModel::PLAY_COUNT_ACTIONS);

        $db      = $this->createRawDataWorld();
        $session = model(GameSessionModel::class)->find(2);
        $audio   = static fn (string $id, string $action, ?int $ms, string $at): array => [
            'client_event_id' => $id, 'audio_asset_id' => 1, 'action' => $action, 'listened_ms' => $ms, 'completed' => false, 'occurred_at' => $at,
        ];
        $before = $db->table('game_event_logs')->where('session_id', 2)->countAllResults();

        $result = service('eventService')->recordAudioBatch($session, [
            $audio('s-1', 'play', null, '2026-11-16T02:03:00.000Z'),
            $audio('s-2', 'pause', 1500, '2026-11-16T02:03:01.500Z'),
            $audio('s-3', 'resume', 1500, '2026-11-16T02:03:04.000Z'),
            $audio('s-4', 'skip', 2500, '2026-11-16T02:03:05.000Z'),
            $audio('s-5', 'rewind', null, '2026-11-16T02:03:06.000Z'),
        ]);

        $this->assertSame(4, $result['accepted']);
        $this->assertSame([['index' => 4, 'reason' => 'audio_event_invalid']], $result['rejected'], 'unknown actions are still refused');

        $rows = $db->table('audio_usage_events')->select('action, play_index, listened_ms')->where('session_id', 2)
            ->whereIn('action', ['play', 'pause', 'resume', 'skip'])->where('occurred_at >=', '2026-11-16')->orderBy('id')->get()->getResultArray();
        $this->assertSame(
            [['play', 1], ['pause', 1], ['resume', 1], ['skip', 1]],
            array_map(static fn (array $r): array => [$r['action'], (int) $r['play_index'] - (int) $rows[0]['play_index'] + 1], $rows),
            'resume and skip belong to the running playback',
        );
        $this->assertSame(2500, (int) $rows[3]['listened_ms']);
        $this->assertSame($before + 1, $db->table('game_event_logs')->where('session_id', 2)->countAllResults(), 'only `play` writes a raw event');

        // A re-sent resume or skip (lost answer, offline queue) is a duplicate, not a second row
        $again = service('eventService')->recordAudioBatch($session, [
            $audio('s-3', 'resume', 1500, '2026-11-16T02:03:04.000Z'),
            $audio('s-4', 'skip', 2500, '2026-11-16T02:03:05.000Z'),
        ]);
        $this->assertSame(['accepted' => 0, 'duplicate' => 2], array_intersect_key($again, ['accepted' => 1, 'duplicate' => 1]));

        $model = model(AudioUsageEventModel::class);
        $this->assertTrue($model->validate(['session_id' => 2, 'audio_asset_id' => 1, 'action' => 'skip']));
        $this->assertTrue($model->validate(['session_id' => 2, 'audio_asset_id' => 1, 'action' => 'resume']));
        $this->assertFalse($model->validate(['session_id' => 2, 'audio_asset_id' => 1, 'action' => 'rewind']));
    }

    public function testInstructionPlayCountIsPlayReplayAndResume(): void
    {
        $audio = static fn (string $action, string $context = 'challenge_instruction'): array => ['action' => $action, 'context' => $context, 'at' => 1];
        $row   = AttemptCalculator::row(
            ['id' => 7, 'status' => 'started', 'completed_at' => null, 'is_retake' => 0, 'hint_count' => 0],
            [
                'is_scored_attempt' => 1, 'windows' => [], 'cumulative_active_ms' => 0, 'events' => [], 'meeting_no' => null, 'field_session_id' => null,
                'timeline'          => ['total_ms' => 0, 'hidden_ms' => 0, 'idle_excess_ms' => 0, 'active_ms' => 0],
                'audio'             => [
                    $audio('play'), $audio('pause'), $audio('resume'), $audio('complete'), $audio('replay'),
                    $audio('autoplay'), $audio('skip'), $audio('play', 'challenge_hint'),
                ],
            ],
        );

        $this->assertSame(3, $row['instruction_replays']);
    }

    public function testDashboardPlayCountFollowsTheSameRule(): void
    {
        $service = (string) file_get_contents(APPPATH . 'Services/AnalyticsService.php');
        $this->assertSame(2, substr_count($service, 'AudioUsageEventModel::PLAY_COUNT_ACTIONS'));
    }

    public function testBrowserSendsResumeAndSkip(): void
    {
        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString("if (!fresh) return this.start(this.opened ? 'resume' : 'play');", $audio);
        $this->assertStringContainsString("this.send('skip');", $audio);

        $narrator = (string) file_get_contents(FCPATH . 'assets/js/game/narrator.js');
        $this->assertStringContainsString("target?.closest('[data-narration-skip]')", $narrator);
        $this->assertStringContainsString('player.skip();', $narrator);

        foreach (['bridge', 'dialogue', 'ending', 'intro'] as $view) {
            $this->assertStringContainsString("'data-narration-skip' => '1'", (string) file_get_contents(APPPATH . 'Views/game/' . $view . '.php'), "Lewati on {$view}");
        }
    }

    public function testMusicHasItsOwnSwitchOffByDefault(): void
    {
        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString("let musicEnabled = Storage.getPref('music', false) === true;", $audio);
        $this->assertStringContainsString("Storage.setPref('music', musicEnabled);", $audio);
        $this->assertStringContainsString('if (!unlocked || !enabled || !musicEnabled || !wantedMusic) return;', $audio);

        $hud = (string) file_get_contents(FCPATH . 'assets/js/game/hud.js');
        $this->assertStringContainsString('Sfx.setMusicEnabled(!Sfx.isMusicEnabled());', $hud);

        $menu = (string) file_get_contents(APPPATH . 'Views/components/hud-menu.php');
        $this->assertStringContainsString('id="btn-music" aria-pressed="false" hidden', $menu);
        $this->assertStringContainsString("lang('Game.music')", $menu);
        $this->assertSame('Musik latar', lang('Game.music', [], 'id'));
        $this->assertSame('Background music', lang('Game.music', [], 'en'));
    }

    public function testCorrectAndWrongEffectsAreSilentInResearchMode(): void
    {
        $audio = (string) file_get_contents(FCPATH . 'assets/js/core/audio.js');
        $this->assertStringContainsString("export const RESEARCH_SILENT_EFFECTS = Object.freeze(['correct', 'wrong']);", $audio);
        $this->assertStringContainsString('if (CONFIG.researchMode && RESEARCH_SILENT_EFFECTS.includes(name)) return;', $audio);
    }
}
