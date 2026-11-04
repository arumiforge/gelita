<?php

use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Encryption;
use Config\Services;
use Tests\Support\Database\BankPlay;

/**
 * `item_tries.ready_ms` (research revision 10, part 3: 29 §6.1): when the
 * item could be answered, in ms since the challenge opened. `/check` keeps it
 * for Benar atau Salah only; `/responses` (Cari Objek) for every tap. Out of
 * range, or later than the last input, it is stored empty.
 *
 * @internal
 */
final class ReadyMsTest extends CIUnitTestCase
{
    use BankPlay;
    use FeatureTestTrait;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $this->originalKey             = (string) config(Encryption::class)->key;
        config(Encryption::class)->key = 'kunci-uji-ready-ms';
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

    public function testCheckKeepsReadyMsForVerdictCardsOverHttp(): void
    {
        $attempt = $this->open('mgl-3');
        $items   = $this->itemsOfType($attempt, 'verdict_card');
        $rows    = [];
        $ready   = [1200, 'x', 4_294_967_296, 9000];

        foreach ($items as $n => $item) {
            $rows[] = [
                'item_id' => $item->id, 'answer' => ['verdict' => $item->verdict()],
                'first_input_ms' => 2000, 'last_input_ms' => 5000, 'ready_ms' => $ready[$n % 4],
            ];
        }

        $this->post('api/attempts/' . $attempt->id . '/check', ['answers' => $rows])->assertOK();

        $stored = array_map(static fn ($v): ?int => $v === null ? null : (int) $v, array_column($this->triesOf($attempt), 'ready_ms'));
        $this->assertSame(1200, $stored[0]);
        $this->assertNull($stored[1], 'not a number');
        $this->assertNull($stored[2], 'beyond INT UNSIGNED');
        $this->assertNull($stored[3], 'later than the last input');
        $this->assertStringNotContainsString('ready_ms', (string) $this->triesOf($attempt)[0]['answer_json'], 'not part of the answer');
    }

    public function testClozeAndPuzzleKeepNoReadyMs(): void
    {
        foreach (['tmg-2' => 'fill_blank_bank', 'tmg-1' => 'puzzle_arrange'] as $ref => $type) {
            $attempt = $this->open($ref);
            $answers = [];

            foreach ($this->itemsOfType($attempt, $type) as $item) {
                $answers[$item->id] = ($type === 'puzzle_arrange'
                    ? ['order' => array_values((array) $item->answerKey('order'))]
                    : ['text' => (string) $item->answerKey('text_id')]) + ['ready_ms' => 100, 'last_input_ms' => 900];
            }

            $this->service->submitCheck($attempt, $answers);

            $this->assertSame([null], array_values(array_unique(array_column($this->triesOf($attempt), 'ready_ms'))), $ref);
        }
    }

    public function testResponsesKeepReadyMsOnRightAndWrongTaps(): void
    {
        $attempt = $this->open('tmg-4');
        [$clue, $other] = $this->itemsOfType($attempt, 'find_object');

        $this->post('api/attempts/' . $attempt->id . '/responses', [
            'item_id' => $clue->id, 'answer' => ['object' => $this->objectRef($attempt, $other->id)],
            'client_event_id' => 'tap-1', 'ready_ms' => 3000, 'last_input_ms' => 4500,
        ])->assertOK();
        $this->post('api/attempts/' . $attempt->id . '/responses', [
            'item_id' => $clue->id, 'answer' => ['object' => $this->objectRef($attempt, $clue->id)],
            'client_event_id' => 'tap-2', 'ready_ms' => 6000, 'last_input_ms' => 5000,
        ])->assertOK();

        $tries = $this->triesOf($attempt);
        $this->assertSame([3000, 4500], [(int) $tries[0]['ready_ms'], (int) $tries[0]['last_input_ms']], 'wrong tap, both since the challenge opened');
        $this->assertNull($tries[1]['ready_ms'], 'ready after the tap: stored empty');
        $this->assertSame(5000, (int) $tries[1]['last_input_ms']);
    }

    /** @param array<string, mixed> $body */
    private function post(string $path, array $body = []): TestResponse
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');

        return $this->withSession(['participant_id' => 1, 'game_session_id' => 1, 'gelita_csrf' => self::CSRF_HASH])
            ->withHeaders(['X-CSRF-TOKEN' => self::CSRF_HASH . str_repeat('0', 32)])
            ->withBodyFormat('json')
            ->call(Method::POST, $path, $body);
    }
}
