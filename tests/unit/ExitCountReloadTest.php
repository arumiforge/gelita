<?php

use App\Libraries\Derived\AttemptCalculator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Owner, 17 November 2026 (32 §15.3 (d)): `exit_count` leaves out an
 * abandon whose next opening of the attempt is a reload resume in the same
 * game session; the raw `challenge_abandoned` stays in the log.
 *
 * @internal
 */
final class ExitCountReloadTest extends CIUnitTestCase
{
    public function testAReloadResumeInTheSameSessionIsNoExit(): void
    {
        $this->assertSame(0, AttemptCalculator::exitCount([
            $this->event(1, 1, 'challenge_abandoned', 1000),
            $this->event(2, 1, 'challenge_opened', 1500, ['resumed' => true, 'reload' => true]),
        ]));
    }

    public function testOtherReturnsStillCountAsExits(): void
    {
        $this->assertSame(4, AttemptCalculator::exitCount([
            // resumed without reload (back from the map)
            $this->event(1, 1, 'challenge_abandoned', 1000),
            $this->event(2, 1, 'challenge_opened', 2000, ['resumed' => true]),
            // reload flag in another game session
            $this->event(3, 1, 'challenge_abandoned', 3000),
            $this->event(4, 2, 'challenge_opened', 4000, ['resumed' => true, 'reload' => true]),
            // two abandons before one reload: only the last one is the reload's
            $this->event(5, 2, 'challenge_abandoned', 5000),
            $this->event(6, 2, 'challenge_abandoned', 5100),
            $this->event(7, 2, 'challenge_opened', 5200, ['resumed' => true, 'reload' => true]),
            // never reopened
            $this->event(8, 2, 'challenge_abandoned', 6000),
        ]));
    }

    public function testEventsAreOrderedByTimeFirst(): void
    {
        $this->assertSame(0, AttemptCalculator::exitCount([
            $this->event(2, 1, 'challenge_opened', 1500, ['resumed' => true, 'reload' => true]),
            $this->event(1, 1, 'challenge_abandoned', 1000),
        ]));
    }

    public function testTheChallengePageMarksAReload(): void
    {
        $js = (string) file_get_contents(FCPATH . 'assets/js/game/challenge.js');
        $this->assertStringContainsString("performance.getEntriesByType('navigation')[0]?.type === 'reload'", $js);
        $this->assertStringContainsString('...(reload ? { reload: true } : {})', $js);
    }

    /** @param array<string, mixed> $payload */
    private function event(int $id, int $session, string $type, int $at, array $payload = []): array
    {
        return ['id' => $id, 'session_id' => $session, 'type' => $type, 'at' => $at, 'attempt_id' => 1, 'item_id' => null, 'payload' => $payload];
    }
}
