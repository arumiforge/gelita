<?php

use App\Entities\ChallengeAttempt;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Services\ChallengeService;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Encryption;
use Config\Services;
use Tests\Support\Database\ResetTables;

/**
 * Data mentah percobaan (perombakan kelas 5, Dok 1 §12.2 dan §12.5): setiap
 * percobaan soal menulis satu baris `item_tries`, menaikkan
 * `item_responses.try_count`, dan mengisi `solved_on_try` saat pertama kali
 * benar — baik lewat "Cek jawabanku" (engine batch) maupun setiap ketuk benda
 * pada `cari`.
 *
 * @internal
 */
final class ItemTryWritesTest extends CIUnitTestCase
{
    use ResetTables;

    private BaseConnection $conn;
    private ChallengeService $service;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-percobaan';

        $this->conn = $this->createResetTables();
        $this->seedWorld();

        foreach (['contentRepository', 'gameContext', 'challengeService', 'eventService', 'scoringService', 'sessionService'] as $name) {
            Services::resetSingle($name);
        }

        $this->service = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        Services::resetSingle('contentRepository');
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testEveryCheckWritesOneTryPerOpenItem(): void
    {
        $attempt = $this->open(1);
        [$a, $b, $c] = $attempt->selectedItemIds();

        $this->assertSame('a', $attempt->variant_group);
        $this->assertSame(3, $attempt->max_tries);

        // Percobaan 1: hanya soal a yang benar
        $first = $this->service->submitCheck($attempt, [
            $a => ['verdict' => 'benar', 'first_input_ms' => 1200, 'last_input_ms' => 3400],
            $b => ['verdict' => 'benar', 'first_input_ms' => 2100, 'last_input_ms' => 2100],
            $c => ['verdict' => 'salah'],
        ]);

        $this->assertSame([1, 2, false], [$first['try_no'], $first['tries_left'], $first['exhausted']]);
        $this->assertSame(1, $first['correct_count']);

        $tries = $this->tries($attempt->id);
        $this->assertCount(3, $tries);
        $this->assertSame([1, 1, 1], array_column($tries, 'try_no'));
        $this->assertSame([1, 1, 1], array_column($tries, 'check_no'));
        $this->assertSame([1200, 3400], [$tries[0]['first_input_ms'], $tries[0]['last_input_ms']]);
        $this->assertSame(['verdict' => 'benar'], json_decode((string) $tries[0]['answer_json'], true), 'waktu klien tidak ikut answer_json');
        $this->assertSame([1, 0, 0], array_column($tries, 'is_correct'));

        $this->assertSame(
            [$a => [1, 1, 1, 3400], $b => [1, null, 0, 2100], $c => [1, null, 0, null]],
            $this->responses($attempt->id),
        );

        // Percobaan 2: soal a terkunci (tanpa baris baru), b benar, c masih salah
        $this->conn->table('challenge_attempts')->where('id', $attempt->id)->update(['hint_count' => 1]);

        $second = $this->service->submitCheck($this->reload($attempt), [
            $a => ['verdict' => 'salah'],
            $b => ['verdict' => 'salah', 'last_input_ms' => 9000],
            $c => ['verdict' => 'salah'],
        ]);

        $this->assertSame([2, 1, false, 2], [$second['try_no'], $second['tries_left'], $second['exhausted'], $second['correct_count']]);
        $this->assertTrue($second['results'][$a], 'soal yang sudah benar tetap benar');

        $tries = $this->tries($attempt->id);
        $this->assertCount(5, $tries, 'soal terkunci tidak menulis percobaan');
        $this->assertSame([$b, 2, 2, 1, 1], [$tries[3]['challenge_item_id'], $tries[3]['try_no'], $tries[3]['check_no'], $tries[3]['is_correct'], $tries[3]['hint_opened_before']]);
        $this->assertSame(0, $tries[0]['hint_opened_before']);

        $this->assertSame(
            [$a => [1, 1, 1, 3400], $b => [2, 2, 0, 9000], $c => [2, null, 0, null]],
            $this->responses($attempt->id),
        );

        // Percobaan 3: c tetap salah → kesempatan habis, pengerjaan ditutup otomatis (Dok 2 §5)
        $third = $this->service->submitCheck($this->reload($attempt), [$c => ['verdict' => 'salah']]);

        $this->assertSame([3, 0, true], [$third['try_no'], $third['tries_left'], $third['exhausted']]);
        $this->assertSame([2, 3], [$third['correct_count'], $third['total']], 'soal terkunci ikut dihitung');
        $this->assertSame([true, 'tries_exhausted', 1], [$third['completed'], $third['completion_reason'], $third['stars']]);
        $this->assertStringEndsWith('selesai/' . $attempt->id, (string) $third['redirect']);
        $this->assertSame([$a => [1, 1, 1, 3400], $b => [2, 2, 0, 9000], $c => [3, null, 0, null]], $this->responses($attempt->id));
        $this->assertCount(6, $this->tries($attempt->id));

        $checked = $this->events('challenge_checked');
        $this->assertCount(3, $checked);
        $this->assertSame(['check_no' => 3, 'try_no' => 3, 'correct_count' => 2, 'total' => 3, 'exhausted' => true], $checked[2]);
        $this->assertSame([['check_count' => 3, 'correct_count' => 2, 'total' => 3]], $this->events('challenge_tries_exhausted'));

        // Ditutup dengan soal yang tetap salah: ★, alasan "kesempatan habis"
        $row = $this->conn->table('challenge_attempts')->where('id', $attempt->id)->get()->getRowArray();
        $this->assertSame(['completed', 'tries_exhausted', 3, 1, 1, 2, 3], [
            $row['status'], $row['completion_reason'], (int) $row['check_count'], (int) $row['stars'],
            (int) $row['first_pass_correct'], (int) $row['final_correct'], (int) $row['scorable_items'],
        ]);
        $this->assertSame('tries_exhausted', $this->events('challenge_completed')[0]['completion_reason']);
    }

    public function testAllCorrectClosesAsSolved(): void
    {
        $attempt = $this->open(1);
        [$a, $b, $c] = $attempt->selectedItemIds();

        $done = $this->service->submitCheck($attempt, [$a => ['verdict' => 'benar'], $b => ['verdict' => 'salah'], $c => ['verdict' => 'benar']]);

        // Semua benar: ditutup otomatis pada cek yang sama
        $this->assertSame([true, 'solved', 3], [$done['completed'], $done['completion_reason'], $done['stars']]);
        $this->assertSame('completed', $this->conn->table('challenge_attempts')->where('id', $attempt->id)->get()->getRow('status'));
        $this->assertSame([$a => [1, 1, 1, null], $b => [1, 1, 1, null], $c => [1, 1, 1, null]], $this->responses($attempt->id));
    }

    public function testEveryHuntTapIsATry(): void
    {
        $attempt = $this->open(2);
        $ids     = $attempt->selectedItemIds();
        $targets = $this->conn->table('challenge_items')->select('id')->where('challenge_node_id', 2)->where('scorable', 1)->orderBy('id')->get()->getResultArray();
        [$first, $second] = array_map(static fn (array $row): int => (int) $row['id'], $targets);
        $decoy   = (int) $this->conn->table('challenge_items')->where('item_key', 'tmg-4-a03')->get()->getRow('id');

        $this->assertContains($decoy, $ids);

        // Petunjuk 1: salah ketuk jebakan dulu, lalu benar
        $miss = $this->service->submitAnswer($attempt, $first, ['object' => $this->ref($attempt, $decoy)], ['last_input_ms' => 1500]);
        $this->assertTrue($miss['wrong_click']);
        $this->assertSame(1, $miss['try_no']);

        $hit = $this->service->submitAnswer($this->reload($attempt), $first, ['object' => $this->ref($attempt, $first)], ['last_input_ms' => 2600]);
        $this->assertTrue($hit['correct']);
        $this->assertSame(2, $hit['try_no']);

        // Petunjuk 2: langsung benar
        $this->service->submitAnswer($this->reload($attempt), $second, ['object' => $this->ref($attempt, $second)], ['last_input_ms' => 800]);

        $tries = $this->tries($attempt->id);
        $this->assertSame(
            [[$first, 1, 0, 1500], [$first, 2, 1, 2600], [$second, 1, 1, 800]],
            array_map(static fn (array $t): array => [$t['challenge_item_id'], $t['try_no'], $t['is_correct'], $t['last_input_ms']], $tries),
        );
        $this->assertSame(['item_id' => $decoy], json_decode((string) $tries[0]['answer_json'], true), 'benda yang diketuk, bukan token');
        $this->assertNull($tries[0]['check_no'], 'cari tidak memakai tombol cek');

        $responses = $this->responses($attempt->id);
        $this->assertSame([2, 2, 0, 2600], $responses[$first]);
        $this->assertSame([1, 1, 1, 800], $responses[$second]);
        $this->assertSame([0, null, 0, null], $responses[$decoy], 'jebakan tidak pernah dijawab');

        $wrong = $this->events('wrong_target_clicked');
        $this->assertSame([1, true], [$wrong[0]['try_no'], $wrong[0]['decoy']]);
    }

    // ------------------------------------------------------------ bantu

    private function open(int $nodeId): ChallengeAttempt
    {
        $session = model(GameSessionModel::class)->find(1);
        $this->assertInstanceOf(GameSession::class, $session);

        $this->service->openNode($session, $nodeId);

        $attempt = model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId);
        $this->assertInstanceOf(ChallengeAttempt::class, $attempt);

        return $attempt;
    }

    private function reload(ChallengeAttempt $attempt): ChallengeAttempt
    {
        return model(ChallengeAttemptModel::class)->find($attempt->id);
    }

    private function ref(ChallengeAttempt $attempt, int $itemId): string
    {
        return (new ReflectionMethod(ChallengeService::class, 'objectRef'))->invoke($this->service, $attempt->id, $itemId);
    }

    /** @return list<array<string, mixed>> */
    private function tries(int $attemptId): array
    {
        $rows = $this->conn->table('item_tries')->where('challenge_attempt_id', $attemptId)->orderBy('id')->get()->getResultArray();

        return array_map(static function (array $row): array {
            foreach (['challenge_item_id', 'try_no', 'check_no', 'is_correct', 'hint_opened_before', 'first_input_ms', 'last_input_ms'] as $column) {
                $row[$column] = $row[$column] === null ? null : (int) $row[$column];
            }

            return $row;
        }, $rows);
    }

    /** @return array<int, array{0: int, 1: ?int, 2: int, 3: ?int}> item id → [try_count, solved_on_try, first_pass_correct, duration_ms] */
    private function responses(int $attemptId): array
    {
        $out = [];

        foreach ($this->conn->table('item_responses')->where('challenge_attempt_id', $attemptId)->orderBy('challenge_item_id')->get()->getResultArray() as $row) {
            $out[(int) $row['challenge_item_id']] = [
                (int) $row['try_count'],
                $row['solved_on_try'] === null ? null : (int) $row['solved_on_try'],
                (int) $row['first_pass_correct'],
                $row['duration_ms'] === null ? null : (int) $row['duration_ms'],
            ];
        }

        return $out;
    }

    /** @return list<array<string, mixed>> payload event bertipe $type, urut waktu */
    private function events(string $type): array
    {
        $rows = $this->conn->table('game_event_logs')->where('event_type', $type)->orderBy('id')->get()->getResultArray();

        return array_map(static fn (array $row): array => json_decode((string) $row['payload_json'], true), $rows);
    }

    /** Satu wilayah, node boleh (3 kartu variasi a) dan node cari (2 target + 1 jebakan). */
    private function seedWorld(): void
    {
        $db = $this->conn;

        $db->table('levels')->insert([
            'sequence' => 1, 'code' => 'temanggung', 'name_id' => 'Temanggung', 'name_en' => 'Temanggung',
            'difficulty' => 'mudah', 'focus_id' => 'Fokus', 'focus_en' => 'Focus',
        ]);
        $db->table('research_studies')->insert([
            'code' => 'STUDI-UJI', 'name' => 'Studi uji', 'status' => 'active', 'unlock_mode' => 'free', 'default_locale' => 'id',
        ]);
        $db->table('participants')->insert([
            'participant_code' => 'GLT-000001', 'username' => 'jaka', 'password_hash' => 'x', 'display_name' => 'Jaka',
        ]);
        $db->table('game_sessions')->insert([
            'session_code' => 'S-UJI', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'locale' => 'id', 'status' => 'active',
        ]);

        $db->table('challenge_nodes')->insertBatch([
            ['level_id' => 1, 'sequence' => 3, 'engine_type' => 'boleh', 'variant_code' => 'benar_salah_bacaan', 'title_id' => 'Boleh', 'title_en' => 'Boleh', 'config_json' => '{"selection":"variant"}'],
            ['level_id' => 1, 'sequence' => 4, 'engine_type' => 'cari', 'variant_code' => 'cari_objek', 'title_id' => 'Cari', 'title_en' => 'Find', 'config_json' => '{"selection":"variant"}'],
        ]);

        $items = [];

        foreach ([['tmg-3-a01', 'benar'], ['tmg-3-a02', 'salah'], ['tmg-3-a03', 'benar']] as $i => [$key, $verdict]) {
            $items[] = [
                'challenge_node_id' => 1, 'item_key' => $key, 'sequence' => $i + 1, 'variant_group' => 'a',
                'interaction_type' => 'verdict_card', 'prompt_id' => "Pernyataan {$key}", 'prompt_en' => "Statement {$key}",
                'answer_key_json' => json_encode(['verdict' => $verdict]), 'config_json' => null, 'scorable' => 1,
            ];
        }

        foreach ([['tmg-4-a01', 20, false], ['tmg-4-a02', 60, false], ['tmg-4-a03', 40, true]] as $i => [$key, $x, $decoy]) {
            $items[] = [
                'challenge_node_id' => 2, 'item_key' => $key, 'sequence' => $i + 1, 'variant_group' => 'a',
                'interaction_type' => 'find_object', 'prompt_id' => "Temukan {$key}", 'prompt_en' => "Find {$key}",
                'answer_key_json' => json_encode(['target' => ! $decoy]),
                'config_json' => json_encode(['x' => $x, 'y' => 50, 'w' => 10, 'decoy' => $decoy]),
                'scorable' => $decoy ? 0 : 1,
            ];
        }

        $db->table('challenge_items')->insertBatch($items);
    }
}
