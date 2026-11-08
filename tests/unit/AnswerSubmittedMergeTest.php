<?php

use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\BankPlay;

/**
 * `answer_changed` is merged into `answer_submitted` (research revision 10,
 * part 3: 29 §5.2, K2): a Cari Objek tap that differs from the previous one
 * writes `answer_submitted {changed: true}`; `answer_changed` is never written.
 *
 * @internal
 */
final class AnswerSubmittedMergeTest extends CIUnitTestCase
{
    use BankPlay;

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-gabung';
        $this->conn                    = $this->createGradeFiveWorld();
        $this->service                 = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        Factories::reset('libraries');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testChangedTapIsAnAnswerSubmittedWithChanged(): void
    {
        $attempt = $this->open('tmg-4');
        [$first, $second] = $this->itemsOfType($attempt, 'find_object');

        // Clue 1: right at once. Clue 2: a wrong tap first, then right (differs from before)
        $this->tapObject($attempt, $first, $first);
        $this->tapObject($attempt, $second, $first);
        $this->tapObject($attempt, $second, $second);

        $rows = $this->conn->table('game_event_logs')->where('event_type', 'answer_submitted')->orderBy('id')->get()->getResultArray();
        $this->assertCount(2, $rows);
        $this->assertSame([false, true], array_map(static fn (array $r): bool => json_decode((string) $r['payload_json'], true)['changed'], $rows));
        $this->assertSame((int) $second->id, (int) $rows[1]['challenge_item_id']);
        $this->assertSame(0, $this->conn->table('game_event_logs')->where('event_type', 'answer_changed')->countAllResults());
    }
}
