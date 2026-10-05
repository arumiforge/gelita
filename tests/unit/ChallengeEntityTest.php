<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Entity konten: casting config, default per engine, pengaturan per variasi,
 * batas percobaan pengerjaan, dan aturan mutlak bahwa answer_key_json tidak
 * pernah ikut ke payload pemain.
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
            'config_json' => '{"use_word_bank":true,"distractor_count":2}',
        ]);

        $this->assertIsArray($node->config_json);
        $this->assertSame(2, $node->config('distractor_count'));
        $this->assertTrue($node->config('use_word_bank'));
    }

    public function testConfigFallsBackToEngineDefault(): void
    {
        $node = new ChallengeNode();
        $node->injectRawData(['id' => 2, 'engine_type' => 'rumpang', 'config_json' => null]);

        $this->assertSame('variant', $node->config('selection'));
        $this->assertTrue($node->isReading(), 'Isi Rumpang kelas 5 selalu satu bacaan');
        $this->assertSame([], $node->variants());
    }

    public function testVerdictOptionsAreAlwaysTrueAndFalse(): void
    {
        $node = new ChallengeNode();
        $node->injectRawData([
            'id'          => 3,
            'engine_type' => 'boleh',
            'config_json' => '{"verdict_options":["benar","salah","pendapat"]}',
        ]);

        $this->assertSame(['benar', 'salah'], $node->verdictOptions(), 'pilihan Pendapat sudah dihapus');
    }

    public function testVariantTitlesAndDistractorsArePerGroup(): void
    {
        $node = new ChallengeNode();
        $node->injectRawData([
            'id'          => 4,
            'engine_type' => 'rumpang',
            'title_id'    => 'Isi Rumpang',
            'title_en'    => 'Fill in the Blanks',
            'config_json' => json_encode([
                'distractors' => ['lama'],
                'variants'    => [
                    'a' => ['title_id' => 'Gunung Kembar', 'title_en' => 'The Twin Mountains', 'distractors' => [['id' => 'salju', 'en' => 'snow'], 'pasir']],
                    'b' => ['title_id' => 'Umbul Jumprit'],
                ],
            ]),
        ]);

        $this->assertSame(['a', 'b'], array_keys($node->variants()));
        $this->assertSame('The Twin Mountains', $node->variantTitle('a', 'en'));
        $this->assertSame('Umbul Jumprit', $node->variantTitle('b', 'en'), 'judul Inggris kosong → judul Indonesia variasi');
        $this->assertSame('Isi Rumpang', $node->variantTitle('c', 'id'), 'variasi tanpa judul → judul tantangan');
        $this->assertSame(['salju', 'pasir'], $node->distractors('id', 'a'));
        $this->assertSame(['snow', 'pasir'], $node->distractors('en', 'a'));
        $this->assertSame([], $node->distractors('id', 'b'));
        $this->assertSame([], $node->distractors('id'), 'pengecoh tingkat node tidak dibaca lagi');
    }

    public function testAttemptCountsTriesLeft(): void
    {
        $attempt = new ChallengeAttempt();
        $attempt->injectRawData(['id' => 1, 'status' => 'in_progress', 'check_count' => 2, 'max_tries' => 3, 'scorable_items' => 4, 'first_pass_correct' => 3]);

        $this->assertSame(3, $attempt->maxTries());
        $this->assertSame(1, $attempt->triesLeft());
        $this->assertFalse($attempt->isExhausted());
        $this->assertSame(75.0, $attempt->firstPassPercent());

        $attempt->check_count = 3;
        $this->assertSame(0, $attempt->triesLeft());
        $this->assertTrue($attempt->isExhausted());

        $legacy = new ChallengeAttempt();
        $legacy->injectRawData(['id' => 2, 'check_count' => 0, 'max_tries' => 0]);
        $this->assertSame(config('Gelita')->maxTries, $legacy->maxTries(), 'baris tanpa max_tries memakai aturan sekarang');
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
            'config_json' => '{"x":30,"y":55,"w":14,"grid":3,"shape":"square","decoy":false,"digital_pillar":"digital_ethics"}',
        ]);

        $this->assertSame(['x' => 30, 'y' => 55, 'w' => 14, 'grid' => 3, 'shape' => 'square'], $item->safeConfig());
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
        $item->injectRawData(['id' => 16, 'item_key' => 'mgl-3-a02', 'answer_key_json' => '{"verdict":"Salah"}']);

        $this->assertSame('salah', $item->verdict());
    }
}
