<?php

use App\Libraries\Derived\ParticipantRun;
use App\Libraries\Derived\RunInput;
use App\Libraries\Derived\Thresholds;
use App\Libraries\EventCatalog;
use App\Services\EventService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * `item_shown` (owner, 9 November 2026; 35 §6.2): a Cari Objek clue or a
 * scenario came on screen. With these events the derived item time counts
 * only the stretches where the item was shown; without them the old span
 * (first try's ready time to the closing try) stays.
 *
 * @internal
 */
final class ItemShownTimingTest extends CIUnitTestCase
{
    public function testItemTimeCountsOnlyWhileTheClueWasOnScreen(): void
    {
        $student = (require SUPPORTPATH . 'Derived/golden-class.php')[0];
        $this->assertSame(178000, $this->itemTotal($student, 41), 'golden span without item_shown: 08:10:02 → 08:13:00');

        // Item 41 shown at 08:10:02, the student jumps to item 42 at 08:11:00 and back to 41 at 08:12:30; 41 closes at 08:13:00
        $shown = static fn (int $id, string $at, int $item, string $via): array => [
            'id' => $id, 'session_id' => 1, 'type' => 'item_shown', 'at' => '2026-11-10 ' . $at, 'offset_known' => 1,
            'attempt_id' => 4, 'item_id' => $item, 'payload' => ['round' => 1, 'via' => $via],
        ];
        $student['events'] = [...$student['events'], $shown(50, '08:10:02', 41, 'start'), $shown(51, '08:11:00', 42, 'jump'), $shown(52, '08:12:30', 41, 'jump')];

        $this->assertSame(58000 + 30000, $this->itemTotal($student, 41));
    }

    public function testItemShownIsAClientResearchEventWithAClampedPayload(): void
    {
        $this->assertContains('item_shown', EventCatalog::RESEARCH);
        $this->assertNotContains('item_shown', EventCatalog::SERVER_ONLY);
        $this->assertSame(['round' => 2, 'via' => 'jump', 't_ms' => 1500], EventService::clampPayload('item_shown', ['round' => '2', 'via' => 'jump', 't_ms' => 1499.6, 'extra' => 'x']));
        $this->assertSame(['round' => null, 'via' => null, 't_ms' => null], EventService::clampPayload('item_shown', ['round' => 7, 'via' => '<b>', 't_ms' => 'soon']));
    }

    private function itemTotal(array $student, int $itemId): ?int
    {
        foreach (ParticipantRun::compute(RunInput::fromArray($student), new Thresholds(180000, 3000, 3000))['derived_items'] as $row) {
            if ((int) $row['challenge_item_id'] === $itemId) {
                return $row['item_total_ms'];
            }
        }

        return null;
    }
}
