<?php

use App\Models\ChallengeAttemptModel;
use App\Services\ScoringService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\ResetTables;

/**
 * Aturan bintang `bintang-1` (perombakan kelas 5, Dok 1 §13.1). Bintang hanya
 * dibaca dari jumlah soal dinilai yang Berhasil pada Percobaan Pertama dan
 * yang benar di akhir; petunjuk, suara, dan waktu tidak mengurangi bintang.
 *
 * @internal
 */
final class StarsTest extends CIUnitTestCase
{
    use ResetTables;

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    /** @return iterable<string, array{bool, int, int, int, int}> selesai, dinilai, percobaan pertama, akhir, bintang */
    public static function rules(): iterable
    {
        yield 'semua berhasil pada percobaan pertama'           => [true, 4, 4, 4, 3];
        yield 'semua benar di akhir, satu butuh percobaan ke-2' => [true, 4, 3, 4, 2];
        yield 'semua benar di akhir, tak satu pun langsung'     => [true, 4, 0, 4, 2];
        yield 'selesai, satu soal tetap salah'                  => [true, 4, 3, 3, 1];
        yield 'selesai, semua tetap salah'                      => [true, 4, 0, 0, 1];
        yield 'belum selesai walau semua benar'                 => [false, 4, 4, 4, 0];
        yield 'ditinggalkan tanpa jawaban'                      => [false, 4, 0, 0, 0];
        yield 'puzzle utuh pada percobaan pertama'              => [true, 1, 1, 1, 3];
        yield 'puzzle utuh pada percobaan ke-3'                 => [true, 1, 0, 1, 2];
        yield 'puzzle, kesempatan habis'                        => [true, 1, 0, 0, 1];
        yield 'tanpa soal dinilai: tidak ada yang salah'        => [true, 0, 0, 0, 3];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('rules')]
    public function testStarRule(bool $completed, int $scorable, int $firstPass, int $final, int $stars): void
    {
        $this->assertSame($stars, ScoringService::starsFromCounts($completed, $scorable, $firstPass, $final));
    }

    public function testRuleVersionIsStoredOnTheRelease(): void
    {
        $this->assertSame('bintang-1', ScoringService::VERSION);
        $this->assertFalse(method_exists(ScoringService::class, 'compute'), 'rumus berbobot 70/20/10 sudah dihapus');
    }

    public function testFinalizeCountsOnlyScorableItemsAndIgnoresHelp(): void
    {
        $db        = $this->createResetTables(['challenge_nodes', 'challenge_items', 'challenge_attempts', 'item_responses']);
        $attemptId = $this->attempt($db, 1, 1, 1, 'completed', ['hint_count' => 5, 'audio_use_count' => 9]);

        // dua target langsung benar, satu benar pada percobaan ke-2, satu jebakan (tidak dinilai)
        $this->respond($db, $attemptId, 'tmg-4-a01', true, true);
        $this->respond($db, $attemptId, 'tmg-4-a02', false, true);
        $this->respond($db, $attemptId, 'tmg-4-a03', true, true);
        $this->respond($db, $attemptId, 'tmg-4-a04', false, false, false);

        $service = new ScoringService();
        $attempt = model(ChallengeAttemptModel::class)->find($attemptId);

        $this->assertSame(
            ['first_pass_correct' => 2, 'final_correct' => 3, 'scorable_items' => 3, 'stars' => 2],
            $service->finalize($attempt),
        );

        $saved = $db->table('challenge_attempts')->where('id', $attemptId)->get()->getRowArray();
        $this->assertSame([2, 3, 3, 2], [(int) $saved['first_pass_correct'], (int) $saved['final_correct'], (int) $saved['scorable_items'], (int) $saved['stars']]);

        // 5 petunjuk dan 9 kali suara tidak menurunkan ★★★
        $db->table('item_responses')->where('challenge_attempt_id', $attemptId)->update(['first_pass_correct' => 1, 'is_correct' => 1]);
        $this->assertSame(3, $service->starsFor(model(ChallengeAttemptModel::class)->find($attemptId)));

        $db->table('challenge_attempts')->where('id', $attemptId)->update(['status' => 'abandoned']);
        $this->assertSame(0, $service->starsFor(model(ChallengeAttemptModel::class)->find($attemptId)), 'ditinggalkan = 0');
    }

    public function testLevelSummaryUsesTheBestCompletedAttemptPerNode(): void
    {
        $db = $this->createResetTables(['challenge_nodes', 'challenge_attempts', 'game_sessions']);

        // Sessions 1 and 3 belong to one student in study 1 (progress follows the student, 28 §8); session 2 to another
        foreach ([1 => 1, 2 => 2, 3 => 1] as $sessionId => $participantId) {
            $db->table('game_sessions')->insert([
                'session_code' => 'S-' . $sessionId, 'participant_id' => $participantId, 'study_id' => 1, 'release_id' => 1,
            ]);
        }

        foreach ([[1, 1], [1, 2], [1, 3], [1, 4], [2, 1]] as [$level, $sequence]) {
            $db->table('challenge_nodes')->insert([
                'level_id' => $level, 'sequence' => $sequence, 'engine_type' => 'puzzle',
                'title_id' => "Node {$level}-{$sequence}", 'title_en' => "Node {$level}-{$sequence}",
            ]);
        }

        // sesi 1: node 1 dua kali (★ lalu ★★★), node 2 ★★, node 3 masih dikerjakan, node 4 ditinggalkan
        $this->attempt($db, 1, 1, 1, 'completed', ['stars' => 1]);
        $this->attempt($db, 1, 1, 2, 'completed', ['stars' => 3]);
        $this->attempt($db, 1, 2, 1, 'completed', ['stars' => 2]);
        $this->attempt($db, 1, 3, 1, 'in_progress');
        $this->attempt($db, 1, 4, 1, 'abandoned');
        $this->attempt($db, 1, 5, 1, 'completed', ['stars' => 1]);   // wilayah lain
        $this->attempt($db, 2, 2, 1, 'completed', ['stars' => 3]);   // another student
        $this->attempt($db, 3, 4, 1, 'completed', ['stars' => 1]);   // the same student's later session

        $service = new ScoringService();

        $this->assertSame(['completed_nodes' => 3, 'total_nodes' => 4, 'stars' => 6], $service->levelSummary(1, 1));
        $this->assertSame(['completed_nodes' => 3, 'total_nodes' => 4, 'stars' => 6], $service->levelSummary(3, 1));
        $this->assertSame(['completed_nodes' => 1, 'total_nodes' => 1, 'stars' => 1], $service->levelSummary(1, 2));
        $this->assertSame(7, $service->totalStars(1));
        $this->assertSame(['completed_nodes' => 0, 'total_nodes' => 0, 'stars' => 0], $service->levelSummary(1, 9));
    }

    /** @param array<string, mixed> $extra */
    private function attempt(BaseConnection $db, int $sessionId, int $nodeId, int $attemptNo, string $status, array $extra = []): int
    {
        $db->table('challenge_attempts')->insert([
            'session_id'             => $sessionId,
            'challenge_node_id'      => $nodeId,
            'attempt_no'             => $attemptNo,
            'variant_group'          => 'a',
            'status'                 => $status,
            'selected_item_ids_json' => '[]',
        ] + $extra);

        return (int) $db->insertID();
    }

    private function respond(BaseConnection $db, int $attemptId, string $itemKey, bool $firstPass, bool $final, bool $scorable = true): void
    {
        $db->table('challenge_items')->insert([
            'challenge_node_id' => 1,
            'item_key'          => $itemKey,
            'variant_group'     => 'a',
            'interaction_type'  => 'find_object',
            'scorable'          => $scorable ? 1 : 0,
        ]);

        $db->table('item_responses')->insert([
            'challenge_attempt_id' => $attemptId,
            'challenge_item_id'    => (int) $db->insertID(),
            'first_pass_correct'   => $firstPass ? 1 : 0,
            'is_correct'           => $final ? 1 : 0,
            'try_count'            => $firstPass ? 1 : 2,
            'solved_on_try'        => $final ? ($firstPass ? 1 : 2) : null,
        ]);
    }
}
