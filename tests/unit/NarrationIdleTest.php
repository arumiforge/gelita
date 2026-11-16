<?php

use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use App\Models\AudioUsageEventModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Narration is never counted as an input pause (data dictionary, active time;
 * 35 §6.7). The browser reports how long a narration was heard on the rows
 * that close a playback (`pause`, `complete`); the row that opens it carries
 * 0 ms. The derived layer has to read the closing rows (sound audit of
 * 15 November 2026).
 *
 * @internal
 */
final class NarrationIdleTest extends CIUnitTestCase
{
    public function testAPauseSpentListeningIsNotIdleTime(): void
    {
        $student  = (require SUPPORTPATH . 'Derived/golden-class.php')[0];
        $baseline = $this->activeMs($student);

        // The golden student has one input pause of 200 s that ends at 08:09:50: 20 s over the 180 s threshold.
        $audio = static fn (string $action, string $at, int $listened): array => [
            'session_id' => 1, 'attempt_id' => null, 'audio_asset_id' => 9, 'action' => $action, 'play_index' => 1,
            'at' => '2026-11-10 ' . $at, 'listened_ms' => $listened, 'context' => 'level_open',
        ];

        $student['audio'] = [...($student['audio'] ?? []), $audio('autoplay', '08:06:30', 0)];
        $this->assertSame($baseline, $this->activeMs($student), 'an opening row alone says nothing about how long the narration ran');

        $this->assertSame(AudioUsageEventModel::ACTIONS, ParticipantRun::AUDIO_ACTIONS, 'the derived layer reads every stored action');

        foreach (['complete', 'pause'] as $closing) {
            $heard          = $student;
            $heard['audio'] = [...$heard['audio'], $audio($closing, '08:09:50', 200000)];
            $this->assertSame($baseline + 20000, $this->activeMs($heard), "a narration closed by `{$closing}` covers the pause");
        }
    }

    private function activeMs(array $student): int
    {
        $rows = ParticipantRun::compute(RunInput::fromArray($student), new Thresholds(180000, 3000, 3000))['derived_field_sessions'];

        return (int) $rows[0]['session_active_ms'];
    }
}
