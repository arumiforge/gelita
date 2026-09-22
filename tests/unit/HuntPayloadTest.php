<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Services\ChallengeService;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;

/**
 * Engine `cari`: sumber halaman tidak boleh membedakan jebakan dari target,
 * dan jawaban hanya diterima lewat token objek — bukan id butir.
 *
 * @internal
 */
final class HuntPayloadTest extends CIUnitTestCase
{
    private ChallengeService $service;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-cari';

        $this->service = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        parent::tearDown();
    }

    public function testObjectsHaveIdenticalShapeWithoutIdsPromptsOrDecoyFlag(): void
    {
        $payload = $this->hunt($this->attempt(501), $this->items(), 'id');

        $this->assertCount(3, $payload['objects']);

        foreach ($payload['objects'] as $object) {
            $this->assertSame(['ref', 'x', 'y', 'w', 'media'], array_keys($object));
        }

        $encoded = json_encode($payload);

        $this->assertStringNotContainsString('jebakan', $encoded);
        $this->assertStringNotContainsString('decoy', $encoded);
        $this->assertStringNotContainsString('tmg-4-9', $encoded, 'item_key tidak ikut dikirim');
    }

    public function testObjectsAreOrderedByScreenPositionNotSelectionOrder(): void
    {
        $payload = $this->hunt($this->attempt(501), $this->items(), 'id');

        // pickForAttempt() menaruh jebakan di akhir; payload mengikuti posisi (y, lalu x)
        $this->assertSame([20.0, 40.0, 60.0], array_column($payload['objects'], 'y'));
    }

    public function testCluesOnlyForScorableTargets(): void
    {
        $payload = $this->hunt($this->attempt(501), $this->items(), 'id');

        $this->assertSame([
            ['item_id' => 11, 'text' => 'Temukan tembakau.'],
            ['item_id' => 12, 'text' => 'Temukan candi.'],
        ], $payload['clues']);
    }

    public function testRefsDoNotRevealItemIdsAndChangePerAttempt(): void
    {
        $first  = array_column($this->hunt($this->attempt(501), $this->items(), 'id')['objects'], 'ref');
        $second = array_column($this->hunt($this->attempt(502), $this->items(), 'id')['objects'], 'ref');

        foreach ($first as $ref) {
            $this->assertMatchesRegularExpression('/^[0-9a-f]{20}$/', $ref);
            $this->assertNotContains($ref, ['11', '12', '13']);
        }

        $this->assertSame([], array_intersect($first, $second));
    }

    public function testAnswerResolvesRefToClickedItem(): void
    {
        $attempt = $this->attempt(501);
        $ref     = $this->ref($attempt, 13);

        $this->assertSame(['item_id' => 13], $this->resolve($attempt, ['object' => $ref]));
    }

    public function testRawItemIdFromClientIsNeverTrusted(): void
    {
        $this->expectException(InvalidArgumentException::class);

        // klien tahu id butir target dari `clues`; tanpa token, jawaban ditolak
        $this->resolve($this->attempt(501), ['item_id' => 11]);
    }

    public function testRefFromAnotherAttemptIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->resolve($this->attempt(502), ['object' => $this->ref($this->attempt(501), 11)]);
    }

    /**
     * Body JSON membawa item_id sebagai angka; Validation memanggil aturan
     * dengan strict_types, jadi aturan harus menerima int tanpa TypeError.
     */
    public function testItemRuleAcceptsIntegerItemIdFromJson(): void
    {
        $validation = service('validation', null, false);
        $validation->setRules(['item_id' => 'item_in_attempt[attempt_id]']);

        $this->assertFalse($validation->run(['item_id' => 7, 'attempt_id' => 0]));
        $this->assertArrayHasKey('item_id', $validation->getErrors());
    }

    // ------------------------------------------------------------ bantuan

    /** Urutan meniru pickForAttempt(): target terpilih dulu, jebakan di akhir. */
    private function items(): array
    {
        return [
            $this->item(11, 'tmg-4-91', true, '{"x":30,"y":60,"w":12,"decoy":false}', 'Temukan tembakau.'),
            $this->item(12, 'tmg-4-93', true, '{"x":10,"y":20,"w":12,"decoy":false}', 'Temukan candi.'),
            $this->item(13, 'tmg-4-92', false, '{"x":70,"y":40,"w":12,"decoy":true,"wrong_feedback_id":"Bukan dari Kedu."}', 'Angklung (objek jebakan).'),
        ];
    }

    private function item(int $id, string $key, bool $scorable, string $config, string $prompt): ChallengeItem
    {
        $item = new ChallengeItem();
        $item->injectRawData([
            'id'                => $id,
            'challenge_node_id' => 4,
            'item_key'          => $key,
            'interaction_type'  => 'find_object',
            'prompt_id'         => $prompt,
            'scorable'          => $scorable ? 1 : 0,
            'config_json'       => $config,
            'media_asset_id'    => null,
        ]);

        return $item;
    }

    private function attempt(int $id): ChallengeAttempt
    {
        $attempt = new ChallengeAttempt();
        $attempt->injectRawData([
            'id'                     => $id,
            'challenge_node_id'      => 4,
            'selected_item_ids_json' => '[11,12,13]',
        ]);

        return $attempt;
    }

    private function hunt(ChallengeAttempt $attempt, array $items, string $locale): array
    {
        return $this->call('huntPayload', $attempt, $items, $locale);
    }

    private function ref(ChallengeAttempt $attempt, int $itemId): string
    {
        return $this->call('objectRef', $attempt->id, $itemId);
    }

    private function resolve(ChallengeAttempt $attempt, array $answer): array
    {
        return $this->call('resolveObjectAnswer', $attempt, $answer);
    }

    private function call(string $method, mixed ...$args): mixed
    {
        $reflection = new ReflectionMethod(ChallengeService::class, $method);

        return $reflection->invoke($this->service, ...$args);
    }
}
