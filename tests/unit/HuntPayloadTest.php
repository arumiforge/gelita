<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Encryption;
use Tests\Support\Database\NarrationTables;

/**
 * Engine `cari`: sumber halaman tidak boleh membedakan jebakan dari target,
 * dan jawaban hanya diterima lewat token objek — bukan id butir.
 *
 * @internal
 */
final class HuntPayloadTest extends CIUnitTestCase
{
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
            ['item_id' => 11, 'text' => 'Temukan tembakau.', 'audio' => null, 'audio_id' => null],
            ['item_id' => 12, 'text' => 'Temukan candi.', 'audio' => null, 'audio_id' => null],
        ], $payload['clues']);
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
            $this->assertSame(base_url('assets/audio/narasi/id/audio.narasi.id.petunjuk-tmg-4-01.mp3'), $id['clues'][0]['audio']);
            $this->assertSame($approved, $id['clues'][0]['audio_id']);
            $this->assertNull($id['clues'][1]['audio'], 'draft belum terdengar pemain');
            $this->assertNull($id['clues'][1]['audio_id'], 'id draft tidak ikut terkirim');

            $en = $this->hunt($this->attempt(501), $items, 'en');
            $this->assertSame($english, $en['clues'][0]['audio_id']);
            $this->assertNull($en['clues'][1]['audio_id'], 'tidak jatuh ke rekaman bahasa lain');

            foreach ($id['objects'] as $object) {
                $this->assertSame(['ref', 'x', 'y', 'w', 'media'], array_keys($object));
            }

            $encoded = json_encode($id);
            $this->assertStringNotContainsString('jebakan', $encoded, 'audio jebakan tidak pernah dikirim');
            $this->assertStringNotContainsString((string) $decoy, implode(',', array_column($id['clues'], 'audio_id')));

            // Media nonaktif: tidak diputar walau audionya disetujui
            $db->table('media_assets')->where('asset_key', 'audio.narasi.id.petunjuk-tmg-4-01')->update(['is_active' => 0]);
            $this->assertNull($this->hunt($this->attempt(501), $items, 'id')['clues'][0]['audio']);
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
}
