<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Encryption;
use Tests\Support\Database\GradeFiveWorld;
use Tests\Support\Database\NarrationTables;

/**
 * Engine `cari`: sumber halaman tidak boleh membedakan jebakan dari target,
 * dan jawaban hanya diterima lewat token objek — bukan id butir.
 *
 * @internal
 */
final class HuntPayloadTest extends CIUnitTestCase
{
    use GradeFiveWorld;
    use NarrationTables;

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
            $this->assertSame(['ref', 'x', 'y', 'w', 'media', 'found'], array_keys($object));
            $this->assertFalse($object['found']);
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

        $state = ['found' => false, 'closed' => false, 'tries' => 0];

        $this->assertEqualsCanonicalizing([
            ['item_id' => 11, 'text' => 'Temukan tembakau.', 'audio' => null, 'audio_id' => null, 'state' => $state],
            ['item_id' => 12, 'text' => 'Temukan candi.', 'audio' => null, 'audio_id' => null, 'state' => $state],
        ], $payload['clues']);
    }

    /** Dok 1 §9.2: urutan petunjuk diacak per pengerjaan, tetap saat dimuat ulang. */
    public function testClueOrderIsSeededByTheAttempt(): void
    {
        $orders = [];

        foreach (range(501, 540) as $attemptId) {
            $order = array_column($this->hunt($this->attempt($attemptId), $this->items(), 'id')['clues'], 'item_id');

            $this->assertSame($order, array_column($this->hunt($this->attempt($attemptId), $this->items(), 'id')['clues'], 'item_id'), 'muat ulang');
            $orders[implode(',', $order)] = true;
        }

        $this->assertCount(2, $orders, 'kedua urutan muncul di antara 40 pengerjaan');
    }

    /**
     * Dok 2 §3.3: payload Cari Objek ketiga wilayah (bank soal kelas 5
     * sungguhan) tidak membawa penanda pengecoh, kunci, maupun umpan balik
     * pengecoh. Objek target dan pengecoh berbentuk identik.
     */
    public function testGradeFivePayloadsHideDecoysKeysAndFeedback(): void
    {
        $db = $this->createGradeFiveWorld();

        try {
            foreach (['tmg-4' => 4, 'mgl-4' => 6, 'wnb-4' => 8] as $ref => $decoyCount) {
                $nodeId  = $this->nodeId($db, $ref);
                $payload = $this->service->openNode(model(GameSessionModel::class)->find(1), $nodeId)['payload'];
                $bank    = service('contentRepository')->itemBank($nodeId);
                $targets = array_filter($bank, static fn (ChallengeItem $item): bool => ! $item->isDecoy());
                $decoys  = array_filter($bank, static fn (ChallengeItem $item): bool => $item->isDecoy());

                $this->assertArrayNotHasKey('items', $payload, $ref);
                $this->assertCount($decoyCount, $decoys, $ref);
                $this->assertCount(4 + $decoyCount, $payload['objects'], $ref);
                $this->assertCount(4, $payload['clues'], $ref);

                foreach ($payload['objects'] as $object) {
                    $this->assertSame(['ref', 'x', 'y', 'w', 'media', 'found'], array_keys($object), $ref);
                }

                foreach ($payload['clues'] as $clue) {
                    $this->assertSame(['item_id', 'text', 'audio', 'audio_id', 'state'], array_keys($clue), $ref);
                }

                $this->assertEqualsCanonicalizing(
                    array_map(static fn (ChallengeItem $item): int => $item->id, array_values($targets)),
                    array_column($payload['clues'], 'item_id'),
                    $ref . ': petunjuk hanya untuk benda yang dicari',
                );

                $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE);

                foreach (['decoy', 'pengecoh', 'answer_key', 'wrong_feedback', 'scorable', 'target'] as $marker) {
                    $this->assertStringNotContainsStringIgnoringCase($marker, $encoded, "{$ref}: {$marker}");
                }

                foreach ($bank as $item) {
                    $this->assertStringNotContainsString($item->item_key, $encoded, "{$ref}: item_key tidak ikut dikirim");
                }

                foreach ($decoys as $decoy) {
                    $this->assertStringNotContainsString($decoy->text('prompt', 'id'), $encoded, "{$ref}: nama pengecoh");
                    $this->assertStringNotContainsString((string) $decoy->wrongFeedback('id'), $encoded, "{$ref}: umpan balik pengecoh");
                    $this->assertStringNotContainsString('"item_id":' . $decoy->id . ',', $encoded, "{$ref}: id pengecoh");
                }
            }
        } finally {
            $this->dropResetTables();
        }
    }

    /** Urutan petunjuk ketiga wilayah teracak per pengerjaan, tetap saat dimuat ulang. */
    public function testGradeFiveClueOrderIsShuffledButStable(): void
    {
        $db = $this->createGradeFiveWorld();

        try {
            $session = model(GameSessionModel::class)->find(1);

            foreach (['tmg-4', 'mgl-4', 'wnb-4'] as $ref) {
                $nodeId = $this->nodeId($db, $ref);
                $orders = [];

                for ($n = 0; $n < 12; $n++) {
                    $order  = array_column($this->service->openNode($session, $nodeId)['payload']['clues'], 'item_id');
                    $reload = $this->service->openNode($session, $nodeId)['payload'];

                    $this->assertTrue($reload['resumed'], $ref);
                    $this->assertSame($order, array_column($reload['clues'], 'item_id'), "{$ref}: muat ulang");

                    $orders[implode(',', $order)] = true;
                    $this->service->abandonAttempt(model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId));
                }

                // 4 petunjuk = 24 urutan; 12 pengerjaan dengan urutan yang sama hampir mustahil
                $this->assertGreaterThan(1, count($orders), "{$ref}: urutan petunjuk teracak");
            }
        } finally {
            $this->dropResetTables();
        }
    }

    /**
     * Narasi petunjuk: hanya rekaman `approved` dengan media aktif, sesuai
     * bahasa, dan id audio hanya dikirim bersama URL-nya. Objek (termasuk
     * jebakan) tetap tanpa audio, jadi bentuknya tetap identik.
     */
    public function testClueAudioOnlyForApprovedRecordingInThatLocale(): void
    {
        $db = Database::connect('tests');
        $this->createNarrationTables($db);

        try {
            $approved = $this->audioAsset($db, 'audio.narasi.id.petunjuk-tmg-4-01', 'id', 'approved');
            $english  = $this->audioAsset($db, 'audio.narasi.en.petunjuk-tmg-4-01', 'en', 'approved');
            $draft    = $this->audioAsset($db, 'audio.narasi.id.petunjuk-tmg-4-02', 'id', 'draft');
            $decoy    = $this->audioAsset($db, 'audio.narasi.id.jebakan', 'id', 'approved');

            [$tobacco, $temple, $trap] = $this->items();
            $tobacco->audio_prompt_id    = $approved;
            $tobacco->audio_prompt_en_id = $english;
            $temple->audio_prompt_id     = $draft;
            $trap->audio_prompt_id       = $decoy;
            $items                       = [$tobacco, $temple, $trap];

            $id = $this->hunt($this->attempt(501), $items, 'id');
            $this->assertSame(base_url('assets/audio/narasi/id/audio.narasi.id.petunjuk-tmg-4-01.mp3'), $this->clue($id, 11)['audio']);
            $this->assertSame($approved, $this->clue($id, 11)['audio_id']);
            $this->assertNull($this->clue($id, 12)['audio'], 'draft belum terdengar pemain');
            $this->assertNull($this->clue($id, 12)['audio_id'], 'id draft tidak ikut terkirim');

            $en = $this->hunt($this->attempt(501), $items, 'en');
            $this->assertSame($english, $this->clue($en, 11)['audio_id']);
            $this->assertNull($this->clue($en, 12)['audio_id'], 'tidak jatuh ke rekaman bahasa lain');

            foreach ($id['objects'] as $object) {
                $this->assertSame(['ref', 'x', 'y', 'w', 'media', 'found'], array_keys($object));
            }

            $encoded = json_encode($id);
            $this->assertStringNotContainsString('jebakan', $encoded, 'audio jebakan tidak pernah dikirim');
            $this->assertStringNotContainsString((string) $decoy, implode(',', array_column($id['clues'], 'audio_id')));

            // Media nonaktif: tidak diputar walau audionya disetujui
            $db->table('media_assets')->where('asset_key', 'audio.narasi.id.petunjuk-tmg-4-01')->update(['is_active' => 0]);
            $this->assertNull($this->clue($this->hunt($this->attempt(501), $items, 'id'), 11)['audio']);
        } finally {
            $this->dropNarrationTables();
        }
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
     * Satu aturan menentukan petunjuk, progres, dan syarat menutup attempt:
     * objek jebakan (dan objek find_object yang tidak dinilai) tidak pernah
     * diminta dijawab, jadi tidak boleh menahan attempt `cari` tetap terbuka.
     */
    public function testDecoysAreNeverExpectedToBeAnswered(): void
    {
        [$tobacco, $temple, $decoy] = $this->items();

        $this->assertTrue($this->call('expectsAnswer', $tobacco));
        $this->assertTrue($this->call('expectsAnswer', $temple));
        $this->assertFalse($this->call('expectsAnswer', $decoy));

        $unscored = $this->item(14, 'tmg-4-94', false, '{"x":50,"y":50,"w":12,"decoy":false}', 'Tanpa petunjuk.');
        $this->assertFalse($this->call('expectsAnswer', $unscored), 'objek tanpa petunjuk tidak dapat dijawab');
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

    private function audioAsset(BaseConnection $db, string $key, string $locale, string $status): int
    {
        $db->table('media_assets')->insert([
            'asset_key' => $key, 'asset_type' => 'audio', 'mime_type' => 'audio/mpeg',
            'storage_path' => 'assets/audio/narasi/' . $locale . '/' . $key . '.mp3',
        ]);
        $db->table('audio_assets')->insert([
            'media_asset_id' => $db->insertID(), 'locale' => $locale, 'context_code' => 'hunt_clue',
            'transcript' => 'Uji', 'approval_status' => $status,
        ]);

        return (int) $db->insertID();
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

    /** @return array<string, mixed> petunjuk untuk butir $itemId (urutan petunjuk diacak per pengerjaan) */
    private function clue(array $payload, int $itemId): array
    {
        foreach ($payload['clues'] as $clue) {
            if ($clue['item_id'] === $itemId) {
                return $clue;
            }
        }

        $this->fail("Tidak ada petunjuk untuk butir {$itemId}");
    }
}
