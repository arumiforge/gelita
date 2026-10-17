<?php

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Bank kata Isi Rumpang (Dok 2 §4) pada bank soal kelas 5 sungguhan, untuk
 * 1 000 pengerjaan per variasi dan kedua bahasa:
 *
 * - every region (Temanggung 3 words + 1 distractor since draft 2, Magelang
 *   and Wonosobo + 2): the answers never appear in blank order inside the bank;
 * - isi bank = jawaban variasi itu + `distractor_count` pengecoh variasi itu;
 * - urutannya tetap untuk pengerjaan yang sama (muat ulang).
 *
 * @internal
 */
final class WordBankShuffleTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private const SEEDS = 1000;

    private BaseConnection $conn;
    private ChallengeService $service;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-bank-kata';

        $this->conn    = $this->createGradeFiveWorld();
        $this->service = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAnswerOrderIsNeverTheBlankOrder(): void
    {
        foreach (['tmg-2' => [3, 1], 'mgl-2' => [5, 2], 'wnb-2' => [7, 2]] as $ref => [$blanks, $decoys]) {
            foreach ($this->variants($ref) as $group => [$node, $items]) {
                foreach (['id', 'en'] as $locale) {
                    $answers     = $this->answers($items, $locale);
                    $distractors = $node->distractors($locale, $group);
                    $orders      = [];

                    $this->assertCount($blanks, $answers, "{$ref} {$group}");
                    $this->assertCount($decoys, $distractors, "{$ref} {$group}: distractors of the variant");

                    for ($attemptId = 1; $attemptId <= self::SEEDS; $attemptId++) {
                        $bank = $this->bank($node, $items, $locale, $attemptId, $group);

                        $this->assertEqualsCanonicalizing(array_merge($answers, $distractors), $bank, "{$ref} {$group} {$locale} #{$attemptId}");
                        $this->assertNotSame(
                            $answers,
                            array_values(array_filter($bank, static fn (string $word): bool => in_array($word, $answers, true))),
                            "{$ref} {$group} {$locale} #{$attemptId}: urutan jawaban sama dengan urutan rumpang",
                        );

                        $orders[implode('|', $bank)] = true;
                    }

                    // Temanggung: 4 words = 24 orders, 20 of them allowed; the others vary far more
                    $this->assertGreaterThan(min(self::SEEDS * 0.9, 19), count($orders), "{$ref} {$group} {$locale}: urutan beragam");
                }
            }
        }
    }

    /**
     * Pengecoh hanya dari variasi pengerjaan itu, sebanyak `distractor_count`.
     * (Variasi a dan b `wnb-2` memang berbagi pengecoh *panas*, *agar* — §9.3.)
     */
    public function testDistractorsComeFromTheAttemptVariant(): void
    {
        foreach (['mgl-2', 'wnb-2'] as $ref) {
            $variants = $this->variants($ref);

            foreach ($variants as $group => [$node, $items]) {
                $own    = $node->distractors('id', $group);
                $extras = array_values(array_diff($this->bank($node, $items, 'id', 7, $group), $this->answers($items, 'id')));

                $this->assertEqualsCanonicalizing(array_slice($own, 0, (int) $node->config('distractor_count')), $extras, "{$ref} {$group}");

                foreach (array_keys($variants) as $other) {
                    $foreign = array_diff($node->distractors('id', $other), $own);

                    $this->assertSame([], array_values(array_intersect($foreign, $extras)), "{$ref} {$group}: pengecoh variasi {$other}");
                }
            }
        }
    }

    /** Muat ulang: bank kata pengerjaan yang sama tetap; pengerjaan lain teracak sendiri. */
    public function testBankIsStablePerAttempt(): void
    {
        $nodeId  = $this->nodeId($this->conn, 'tmg-2');
        $session = model(GameSessionModel::class)->find(1);

        $first   = $this->service->openNode($session, $nodeId)['payload'];
        $reload  = $this->service->openNode($session, $nodeId)['payload'];
        $attempt = model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId);

        $this->assertTrue($reload['resumed']);
        $this->assertSame($first['word_bank'], $reload['word_bank']);

        [$node, $items] = $this->variants('tmg-2')[$attempt->variant_group];
        $this->assertSame($this->bank($node, $items, 'id', $attempt->id, $attempt->variant_group), $first['word_bank']);

        for ($attemptId = 1; $attemptId <= 50; $attemptId++) {
            $this->assertSame(
                $this->bank($node, $items, 'id', $attemptId, $attempt->variant_group),
                $this->bank($node, $items, 'id', $attemptId, $attempt->variant_group),
            );
        }

        // Pemetaan kata → rumpang tidak pernah dikirim
        foreach ($first['items'] as $item) {
            $this->assertArrayNotHasKey('answer_key_json', $item);
            $this->assertArrayNotHasKey('answer', $item);
        }
    }

    // ------------------------------------------------------------ bantu

    /**
     * Node dan soal aktif per variasi, dibaca seperti saat pengerjaan dibuka.
     *
     * @return array<string, array{0: ChallengeNode, 1: list<ChallengeItem>}>
     */
    private function variants(string $ref): array
    {
        $nodeId = $this->nodeId($this->conn, $ref);
        $node   = service('contentRepository')->node($nodeId);
        $out    = [];

        foreach (service('contentRepository')->itemBank($nodeId) as $item) {
            $out[(string) $item->variant_group][1][] = $item;
            $out[(string) $item->variant_group][0]   = $node;
        }

        $this->assertSame(['a', 'b', 'c'], array_keys($out), $ref);

        return array_map(static fn (array $pair): array => [$pair[0], $pair[1]], $out);
    }

    /**
     * @param list<ChallengeItem> $items
     *
     * @return list<string> jawaban urut rumpang
     */
    private function answers(array $items, string $locale): array
    {
        return array_map(static fn (ChallengeItem $item): string => (string) $item->answerKey('text_' . $locale), $items);
    }

    /**
     * @param list<ChallengeItem> $items
     *
     * @return list<string>
     */
    private function bank(ChallengeNode $node, array $items, string $locale, int $attemptId, string $group): array
    {
        return (new ReflectionMethod(ChallengeService::class, 'wordBank'))
            ->invoke($this->service, $node, $items, $locale, $attemptId, $group);
    }
}
