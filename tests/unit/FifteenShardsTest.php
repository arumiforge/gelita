<?php

use App\Controllers\Game\BaseGameController;
use App\Database\Seeds\ContentSeeder;
use App\Entities\GameSession;
use App\Models\GameSessionModel;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Five challenges per region (32 §7.3, §15.1): 15 Serpihan Cahaya counted
 * from the active nodes, the region-done story after the fifth challenge (not
 * the fourth), the closing story after all fifteen, and a bank without
 * Skenario Digital keeps 12 (32 K8).
 *
 * @internal
 */
final class FifteenShardsTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['url', 'gelita', 'content', 'ui']);

        $this->conn = $this->createGradeFiveWorld();
        (new ContentSeeder(config('Database'), $this->conn))->seedDialogues();
        $this->conn->table('participants')->update(['intro_seen_at' => date('Y-m-d H:i:s')]);
        service('contentRepository')->flush();
        service('cache')->clean();
        $_SESSION = ['dialogues_shown' => ['1:1' => true, '1:2' => true, '1:3' => true]];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testShardTotalIsTheNumberOfActiveNodes(): void
    {
        $this->assertSame(15, $this->summary()['shards_total']);

        // A bank without Skenario Digital: the fifth nodes are inactive, 12 shards (32 K8)
        $this->conn->table('challenge_nodes')->where('engine_type', 'skenario')->update(['is_active' => 0]);
        service('contentRepository')->flush();
        service('cache')->clean();
        $this->assertSame(12, $this->summary()['shards_total']);
    }

    public function testRegionDoneAfterTheFifthChallengeAndEndingAfterFifteen(): void
    {
        foreach ([1, 2, 3, 4] as $n) {
            $this->complete('tmg-' . $n);
        }

        $this->assertStringEndsWith('/wilayah/temanggung', $this->send('tuntas/temanggung')->getRedirectUrl(), 'four of five: not done yet');

        $this->complete('tmg-5');
        $done = $this->send('tuntas/temanggung');
        $done->assertOK();
        $this->assertCount(5, service('contentRepository')->dialogues(1, 'level_done'));
        $this->assertStringContainsString('kata sandi hanya untuk pemilik akun', (string) $done->response()->getBody());

        $this->assertStringEndsWith('/peta', $this->send('penutup')->getRedirectUrl());

        foreach (['mgl', 'wnb'] as $prefix) {
            foreach (range(1, 5) as $n) {
                $this->complete($prefix . '-' . $n);
            }
        }

        $this->conn->table('participant_progress')->where('participant_id', 1)->update(['completed_nodes' => 15]);
        $this->send('penutup')->assertOK();
        $this->assertSame(15, $this->summary()['shards']);
    }

    /** One completed attempt of a node, as closeAttempt() leaves it. */
    private function complete(string $ref): void
    {
        $this->conn->table('challenge_attempts')->insert([
            'session_id' => 1, 'challenge_node_id' => $this->nodeId($this->conn, $ref), 'attempt_no' => 1, 'status' => 'completed',
            'completion_reason' => 'solved', 'selected_item_ids_json' => '[]', 'stars' => 3, 'completed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return array<string, mixed> GameProgress::progressSummary() of game session 1 */
    private function summary(): array
    {
        $probe = new class () extends BaseGameController {
            public function summary(GameSession $session): array
            {
                return $this->progressSummary($session);
            }
        };

        return $probe->summary(model(GameSessionModel::class)->find(1));
    }

    private function send(string $path): TestResponse
    {
        foreach (['security', 'gameContext', 'sessionService', 'eventService', 'scoringService'] as $name) {
            Services::resetSingle($name);
        }

        return $this->withSession(['gelita_csrf' => self::CSRF_HASH, 'participant_id' => 1, 'game_session_id' => 1] + $_SESSION)->call(Method::GET, $path);
    }
}
