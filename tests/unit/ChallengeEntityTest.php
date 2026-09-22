<?php

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Entity konten: casting config, default per engine, dan aturan mutlak
 * bahwa answer_key_json tidak pernah ikut ke payload pemain.
 *
 * @internal
 */
final class ChallengeEntityTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);
    }

    public function testConfigJsonIsCastToArray(): void
    {
        $node = new ChallengeNode();
        $node->injectRawData([
            'id'          => 1,
            'engine_type' => 'rumpang',
            'config_json' => '{"items_per_round":4,"use_word_bank":true,"distractor_count":2}',
        ]);

        $this->assertIsArray($node->config_json);
        $this->assertSame(4, $node->itemsPerRound());
        $this->assertTrue($node->config('use_word_bank'));
    }

    public function testConfigFallsBackToEngineDefault(): void
    {
        $node = new ChallengeNode();
        $node->injectRawData(['id' => 2, 'engine_type' => 'pilihan', 'config_json' => null]);

        $this->assertSame(3, $node->itemsPerRound());
        $this->assertFalse($node->allowsRetry());
    }

    public function testVerdictOptionsComeFromNodeConfig(): void
    {
        $node = new ChallengeNode();
        $node->injectRawData([
            'id'          => 3,
            'engine_type' => 'boleh',
            'config_json' => '{"verdict_options":["benar","salah","pendapat"],"require_reason":true}',
        ]);

        $this->assertSame(['benar', 'salah', 'pendapat'], $node->verdictOptions());
        $this->assertTrue($node->requiresReason());
    }

    public function testPlayerArrayNeverCarriesTheAnswerKey(): void
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'                => 10,
            'challenge_node_id' => 1,
            'item_key'          => 'tmg-2-91',
            'interaction_type'  => 'fill_blank_bank',
            'prompt_id'         => 'Gunung ___ ada di Temanggung.',
            'prompt_en'         => 'Mount ___ is in Temanggung.',
            'answer_key_json'   => '{"text_id":"Sumbing","text_en":"Sumbing"}',
            'config_json'       => '{"decoy":true,"wrong_feedback_id":"Bukan dari Temanggung.","digital_pillar":"digital_safety","x":30,"y":55,"w":14}',
            'scorable'          => 1,
            'is_active'         => 1,
        ]);

        $payload = $item->toPlayerArray('id');
        $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

        $this->assertArrayNotHasKey('answer_key_json', $payload);
        $this->assertStringNotContainsString('Sumbing', $encoded);
        $this->assertStringNotContainsString('decoy', $encoded);
        $this->assertStringNotContainsString('wrong_feedback', $encoded);
        $this->assertStringNotContainsString('digital_pillar', $encoded);
    }

    public function testSafeConfigKeepsOnlyDisplayKeys(): void
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'          => 11,
            'item_key'    => 'tmg-4-91',
            'config_json' => '{"x":30,"y":55,"w":14,"grid":3,"decoy":false,"digital_pillar":"digital_ethics"}',
        ]);

        $this->assertSame(['x' => 30, 'y' => 55, 'w' => 14, 'grid' => 3], $item->safeConfig());
        $this->assertSame('digital_ethics', $item->digitalPillar());
    }

    public function testAcceptedAnswersAreNormalisedAndIncludeIndonesian(): void
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'              => 12,
            'item_key'        => 'mgl-2-91',
            'answer_key_json' => '{"accept_id":[" Syailendra ","Sailendra"],"accept_en":["Shailendra"]}',
        ]);

        $accepted = $item->acceptedAnswers('en');

        $this->assertContains('shailendra', $accepted);
        $this->assertContains('syailendra', $accepted);
        $this->assertContains('sailendra', $accepted);
    }

    public function testOrderingPiecesAreShuffledWithoutRevealingTheKey(): void
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'                => 13,
            'item_key'          => 'wnb-1-91',
            'interaction_type'  => 'ordering',
            'answer_key_json'   => '{"order":["c","a","d","b"]}',
            'config_json'       => '{"pieces":[{"key":"a","text_id":"Rebus mie.","text_en":"Boil."},'
                . '{"key":"b","text_id":"Sajikan.","text_en":"Serve."},'
                . '{"key":"c","text_id":"Siapkan bahan.","text_en":"Prepare."},'
                . '{"key":"d","text_id":"Tuang kuah.","text_en":"Pour."}]}',
        ]);

        $payload = $item->toPlayerArray('id');

        $this->assertCount(4, $payload['pieces']);
        $this->assertEqualsCanonicalizing(
            ['a', 'b', 'c', 'd'],
            array_column($payload['pieces'], 'key'),
        );
        $this->assertArrayNotHasKey('order', $payload);
    }

    public function testDecoyItemIsRecognised(): void
    {
        $decoy = new ChallengeItem();
        $decoy->injectRawData(['id' => 14, 'item_key' => 'tmg-4-92', 'config_json' => '{"decoy":true}']);

        $target = new ChallengeItem();
        $target->injectRawData(['id' => 15, 'item_key' => 'tmg-4-91', 'config_json' => '{"decoy":false}']);

        $this->assertTrue($decoy->isDecoy());
        $this->assertFalse($target->isDecoy());
    }

    public function testVerdictIsLowercased(): void
    {
        $item = new ChallengeItem();
        $item->injectRawData(['id' => 16, 'item_key' => 'mgl-3-92', 'answer_key_json' => '{"verdict":"Pendapat"}']);

        $this->assertSame('pendapat', $item->verdict());
    }
}
