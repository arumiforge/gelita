<?php

use App\Entities\ChallengeAttempt;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Pemilihan variasi (Dok 2 §3) pada bank soal kelas 5 sungguhan: satu
 * variasi acak per pengerjaan, pengerjaan ulang menghindari variasi yang
 * sudah pernah didapat siswa (di semua sesinya), muat ulang tidak mengganti
 * variasi, dan variasi yang dinonaktifkan seluruhnya membuka pengerjaan baru.
 *
 * @internal
 */
final class VariantSelectionTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private BaseConnection $conn;
    private ChallengeService $service;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-variasi';

        $this->conn    = $this->createGradeFiveWorld();
        $this->service = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    /** Setiap pengerjaan memakai semua soal aktif satu variasi, urut `sequence`. */
    public function testAttemptHoldsOneWholeVariant(): void
    {
        $refs = [];

        foreach (['tmg', 'mgl', 'wnb'] as $region) {
            foreach (range(1, 4) as $sequence) {
                $refs[] = $region . '-' . $sequence;
            }
        }

        foreach ($refs as $ref) {
            $nodeId  = $this->nodeId($this->conn, $ref);
            $payload = $this->service->openNode($this->session(), $nodeId)['payload'];
            $attempt = $this->current($nodeId);
            $group   = (string) $attempt->variant_group;

            $expected = array_map('intval', array_column($this->conn->table('challenge_items')->select('id')
                ->where('challenge_node_id', $nodeId)->where('variant_group', $group)->where('is_active', 1)
                ->orderBy('sequence')->get()->getResultArray(), 'id'));

            // Cari Objek: satu adegan tetap per wilayah (Dok 2 §3.1)
            $this->assertContains($group, str_ends_with($ref, '-4') ? ['a'] : ['a', 'b', 'c'], $ref);
            $this->assertSame($expected, $attempt->selectedItemIds(), $ref . ': satu variasi utuh, urut sequence');
            $this->assertSame(['group' => $group, 'title' => $payload['variant']['title']], $payload['variant'], $ref);
            $this->assertNotSame('', $payload['variant']['title'], $ref);
            $this->assertSame([3, 0], [$payload['max_tries'], $payload['tries_used']], $ref);
        }
    }

    /** Judul variasi: config_json.variants (puzzle, rumpang, cari) atau judul bacaannya (boleh). */
    public function testPayloadNamesTheVariant(): void
    {
        foreach (['tmg-2', 'mgl-1', 'wnb-4'] as $ref) {
            $nodeId   = $this->nodeId($this->conn, $ref);
            $payload  = $this->service->openNode($this->session(), $nodeId)['payload'];
            $variants = json_decode((string) $this->conn->table('challenge_nodes')->where('id', $nodeId)->get()->getRow('config_json'), true)['variants'];

            $this->assertSame($variants[$payload['variant']['group']]['title_id'], $payload['variant']['title'], $ref);
        }

        $nodeId  = $this->nodeId($this->conn, 'mgl-3');
        $payload = $this->service->openNode($this->session(), $nodeId)['payload'];
        $passage = $this->conn->table('reading_passages')->where('passage_key', 'mgl-3-' . $payload['variant']['group'])->get()->getRowArray();

        $this->assertSame($passage['title_id'], $payload['variant']['title'], 'Benar atau Salah: judul bacaan variasi itu');
        $this->assertSame([(int) $passage['id']], array_keys($payload['passages']), 'satu bacaan per pengerjaan');
    }

    /** Siswa yang berbeda mendapat variasi acak: ketiga variasi muncul. */
    public function testVariantIsPickedAtRandom(): void
    {
        $nodeId = $this->nodeId($this->conn, 'wnb-2');
        $seen   = [];

        for ($n = 2; $n <= 41; $n++) {
            $session = $this->newStudent($n);

            $this->service->openNode($session, $nodeId);
            $seen[] = (string) $this->current($nodeId, $session->id)->variant_group;
        }

        // Peluang satu variasi tidak pernah muncul dalam 40 siswa: 3 × (2/3)^40 ≈ 3e-7
        $this->assertSame(['a', 'b', 'c'], array_values(array_unique(array_merge(['a', 'b', 'c'], $seen))));
        $this->assertCount(3, array_unique($seen), 'ketiga variasi pernah terpilih');
    }

    /**
     * Pengerjaan ulang memilih variasi yang belum pernah didapat siswa itu,
     * juga dari sesi lain; setelah ketiganya pernah, semua variasi boleh lagi.
     */
    public function testRetryAvoidsVariantsAlreadyServed(): void
    {
        $nodeId  = $this->nodeId($this->conn, 'mgl-2');
        $session = $this->session();
        $groups  = [];

        $this->service->openNode($session, $nodeId);
        $first    = $this->current($nodeId);
        $groups[] = $first->variant_group;
        $this->service->abandonAttempt($first);

        $this->service->openNode($session, $nodeId);
        $second   = $this->current($nodeId);
        $groups[] = $second->variant_group;

        $this->assertSame(2, $second->attempt_no);
        $this->assertNotSame($first->variant_group, $second->variant_group);
        $this->service->abandonAttempt($second);

        // Sesi lain milik siswa yang sama: variasi ketiga yang tersisa
        $this->conn->table('game_sessions')->where('id', 1)->update(['status' => 'completed']);
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-KELAS5-2', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);
        $other = model(GameSessionModel::class)->find(2);

        $this->service->openNode($other, $nodeId);
        $third    = $this->current($nodeId, 2);
        $groups[] = $third->variant_group;

        $this->assertSame(['a', 'b', 'c'], $this->sorted($groups), 'tiga pengerjaan, tiga variasi berbeda');
        $this->service->abandonAttempt($third);

        // Ketiganya sudah pernah: pengerjaan berikutnya memilih dari semua variasi
        $this->service->openNode($other, $nodeId);
        $this->assertContains($this->current($nodeId, 2)->variant_group, ['a', 'b', 'c']);
    }

    /** Siswa lain tidak memengaruhi variasi yang dihindari. */
    public function testServedVariantsArePerStudent(): void
    {
        $nodeId = $this->nodeId($this->conn, 'tmg-3');

        $this->service->openNode($this->session(), $nodeId);
        $mine = $this->current($nodeId);

        $this->assertSame([$mine->variant_group], model(ChallengeAttemptModel::class)->variantsServed(1, $nodeId));
        $this->assertSame([], model(ChallengeAttemptModel::class)->variantsServed(2, $nodeId));
    }

    /** Muat ulang memakai pengerjaan yang sama: variasi, soal, dan urutan bank kata tetap. */
    public function testResumeKeepsTheVariant(): void
    {
        $nodeId = $this->nodeId($this->conn, 'wnb-2');

        $opened  = $this->service->openNode($this->session(), $nodeId);
        $resumed = $this->service->openNode($this->session(), $nodeId);

        $this->assertFalse($opened['payload']['resumed']);
        $this->assertTrue($resumed['payload']['resumed']);
        $this->assertSame($opened['attempt']->id, $resumed['attempt']->id);

        foreach (['variant', 'word_bank', 'passages'] as $key) {
            $this->assertSame($opened['payload'][$key] ?? null, $resumed['payload'][$key] ?? null, $key);
        }

        $this->assertSame(array_column($opened['payload']['items'], 'id'), array_column($resumed['payload']['items'], 'id'));
        $this->assertSame(1, $this->conn->table('challenge_attempts')->where('challenge_node_id', $nodeId)->countAllResults());
    }

    /**
     * Semua soal variasi pengerjaan yang berjalan dinonaktifkan admin:
     * pengerjaan lama ditinggalkan (`via: content_changed`) dan variasi lain
     * dipilih pada pengerjaan baru.
     */
    public function testDeactivatedVariantStartsANewAttempt(): void
    {
        $nodeId = $this->nodeId($this->conn, 'tmg-1');

        $this->service->openNode($this->session(), $nodeId);
        $old = $this->current($nodeId);

        $this->conn->table('challenge_items')
            ->where('challenge_node_id', $nodeId)->where('variant_group', $old->variant_group)
            ->update(['is_active' => 0]);
        // MockCache menyimpan kunci tanpa prefix, jadi flush() cukup dibantu clean()
        service('contentRepository')->flush();
        service('cache')->clean();

        $reopened = $this->service->openNode($this->session(), $nodeId);
        $new      = $this->current($nodeId);

        $this->assertFalse($reopened['payload']['resumed']);
        $this->assertNotSame($old->id, $new->id);
        $this->assertSame(2, $new->attempt_no);
        $this->assertNotSame($old->variant_group, $new->variant_group);
        $this->assertSame('abandoned', $this->conn->table('challenge_attempts')->where('id', $old->id)->get()->getRow('status'));

        $abandoned = $this->conn->table('game_event_logs')->where('event_type', 'challenge_abandoned')->get()->getRowArray();
        $this->assertSame('content_changed', json_decode((string) $abandoned['payload_json'], true)['via']);
    }

    // ------------------------------------------------------------ bantu

    private function session(): GameSession
    {
        return model(GameSessionModel::class)->find(1);
    }

    private function current(int $nodeId, int $sessionId = 1): ChallengeAttempt
    {
        $attempt = model(ChallengeAttemptModel::class)->currentInProgress($sessionId, $nodeId);
        $this->assertInstanceOf(ChallengeAttempt::class, $attempt);

        return $attempt;
    }

    /** Siswa baru ke-$n dengan satu sesi aktif. */
    private function newStudent(int $n): GameSession
    {
        $this->conn->table('participants')->insert([
            'participant_code' => sprintf('GLT-%06d', $n), 'username' => 'siswa' . $n, 'password_hash' => 'x', 'display_name' => 'Siswa ' . $n,
        ]);
        $participantId = (int) $this->conn->insertID();

        $this->conn->table('game_sessions')->insert([
            'session_code' => 'S-' . $n, 'participant_id' => $participantId, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);

        return model(GameSessionModel::class)->find((int) $this->conn->insertID());
    }

    /** @param list<string|null> $values */
    private function sorted(array $values): array
    {
        sort($values);

        return $values;
    }
}
