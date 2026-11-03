<?php

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\GameSession;
use App\Entities\ItemResponse;
use App\Exceptions\ChallengeRuleException;
use App\Models\ChallengeAttemptModel;
use App\Models\GameSessionModel;
use App\Models\ItemResponseModel;
use App\Services\ChallengeService;
use CodeIgniter\Config\Factories;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\Test\TestResponse;
use Config\Encryption;
use Config\Services;
use Tests\Support\Database\GradeFiveWorld;

/**
 * Aturan 3 kesempatan (Dok 2 §5) di keempat tantangan, pada bank soal kelas 5
 * sungguhan:
 *
 * - engine batch: cek ke-3 yang masih salah menutup pengerjaan
 *   `tries_exhausted` (★) di transaction yang sama; cek ke-4 ditolak 409
 *   TRIES_EXHAUSTED tanpa perubahan; soal yang sudah benar terkunci; cek
 *   dengan soal kosong ditolak 422 INCOMPLETE tanpa memakai kesempatan;
 * - Cari Objek: ketukan salah ke-3 menutup petunjuk (`clue_failed`), dan
 *   pengerjaan selesai setelah keempat petunjuk tertutup;
 * - setiap percobaan tercatat di `item_tries`.
 *
 * @internal
 */
final class TryLimitTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use GradeFiveWorld;

    /** Hash CSRF (mode session); token = hash . kunci nol (tokenRandomize). */
    private const CSRF_HASH = '0123456789abcdef0123456789abcdef';

    /** Susun Gambar, Isi Rumpang, Benar atau Salah di ketiga wilayah. */
    private const BATCH = ['tmg-1', 'tmg-2', 'tmg-3', 'mgl-1', 'mgl-2', 'mgl-3', 'wnb-1', 'wnb-2', 'wnb-3'];

    private BaseConnection $conn;
    private ChallengeService $service;
    private string $originalKey;

    protected function setUp(): void
    {
        parent::setUp();
        helper(['gelita', 'content']);

        $encryption        = config(Encryption::class);
        $this->originalKey = (string) $encryption->key;
        $encryption->key   = 'kunci-uji-kesempatan';

        $this->conn    = $this->createGradeFiveWorld();
        $this->service = new ChallengeService();
    }

    protected function tearDown(): void
    {
        config(Encryption::class)->key = $this->originalKey;
        $this->dropResetTables();
        parent::tearDown();
    }

    /** Cek ke-3 yang masih salah menutup pengerjaan; jawaban terakhir disimpan apa adanya. */
    public function testThirdWrongCheckClosesEveryBatchChallenge(): void
    {
        foreach (self::BATCH as $ref) {
            $attempt = $this->open($ref);
            $items   = $this->expected($attempt);
            $count   = count($items);

            foreach ([1, 2, 3] as $check) {
                $result = $this->service->submitCheck($this->reload($attempt), $this->wrongAnswers($items));

                $this->assertSame([$check, 3 - $check, 0, $count], [$result['check_count'], $result['tries_left'], $result['correct_count'], $result['total']], "{$ref} cek {$check}");
                $this->assertSame($check === 3, $result['completed'], "{$ref} cek {$check}");
                $this->assertSame($check === 3, $result['exhausted'], "{$ref} cek {$check}");
            }

            $this->assertSame(['tries_exhausted', 1], [$result['completion_reason'], $result['stars']], $ref);
            $this->assertStringEndsWith('selesai/' . $attempt->id, (string) $result['redirect'], $ref);

            $row = $this->conn->table('challenge_attempts')->where('id', $attempt->id)->get()->getRowArray();
            $this->assertSame(['completed', 'tries_exhausted', 3, 1, 0, 0, $count], [
                $row['status'], $row['completion_reason'], (int) $row['check_count'], (int) $row['stars'],
                (int) $row['first_pass_correct'], (int) $row['final_correct'], (int) $row['scorable_items'],
            ], $ref);

            // item_tries lengkap: satu baris per soal per cek, try_no = check_no = 1..3
            $tries = $this->tries($attempt->id);
            $this->assertCount(3 * $count, $tries, $ref);

            foreach ($items as $itemId => $item) {
                $own = array_values(array_filter($tries, static fn (array $t): bool => (int) $t['challenge_item_id'] === $itemId));

                $this->assertSame([1, 2, 3], array_map('intval', array_column($own, 'try_no')), "{$ref} {$item->item_key}");
                $this->assertSame([1, 2, 3], array_map('intval', array_column($own, 'check_no')), "{$ref} {$item->item_key}");
                $this->assertSame(['0', '0', '0'], array_map('strval', array_column($own, 'is_correct')), "{$ref} {$item->item_key}");
            }

            foreach ($this->responses($attempt->id) as $itemId => $response) {
                $this->assertSame(['answered', 0, 3, null, 0], [
                    $response['status'], (int) $response['is_correct'], (int) $response['try_count'],
                    $response['solved_on_try'], (int) $response['first_pass_correct'],
                ], "{$ref} butir {$itemId}");
                $this->assertSame($this->wrongAnswers($items)[$itemId], json_decode((string) $response['final_answer_json'], true), "{$ref}: jawaban terakhir apa adanya");
            }
        }

        $this->assertCount(27, $this->events('challenge_checked'));
        $this->assertCount(9, $this->events('challenge_tries_exhausted'));
        $this->assertSame(array_fill(0, 9, 'tries_exhausted'), array_column($this->events('challenge_completed'), 'completion_reason'));
        $this->assertSame(9, (int) $this->conn->table('session_progress')->where('session_id', 1)->get()->getRow('completed_nodes'), 'serpihan tetap didapat');
    }

    /** Cek ke-4 ditolak TRIES_EXHAUSTED (409) tanpa perubahan apa pun. */
    public function testFourthCheckIsRejectedWithoutChanges(): void
    {
        $attempt = $this->open('mgl-2');
        $items   = $this->expected($attempt);

        foreach ([1, 2, 3] as $check) {
            $this->service->submitCheck($this->reload($attempt), $this->wrongAnswers($items));
        }

        $before = [$this->tries($attempt->id), $this->responses($attempt->id), $this->conn->table('game_event_logs')->countAllResults()];

        try {
            $this->service->submitCheck($this->reload($attempt), $this->rightAnswers($items));
            $this->fail('Cek ke-4 seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['TRIES_EXHAUSTED', 409], [$e->errorCode, $e->status]);
            $this->assertStringEndsWith('selesai/' . $attempt->id, (string) $e->extra['redirect']);
        }

        $this->assertSame($before, [$this->tries($attempt->id), $this->responses($attempt->id), $this->conn->table('game_event_logs')->countAllResults()]);
        $this->assertSame(3, (int) $this->reload($attempt)->check_count);
    }

    /** Lewat HTTP: cek ke-3 melaporkan penutupan, cek ke-4 dibalas 409 TRIES_EXHAUSTED. */
    public function testChecksOverHttp(): void
    {
        $attempt = $this->open('tmg-3');
        $items   = $this->expected($attempt);

        $first = $this->check($attempt->id, $this->wrongAnswers($items));
        $first->assertStatus(200);
        $data = $this->json($first)['data'];
        $this->assertSame([false, null, null, 2, 1], [$data['completed'], $data['completion_reason'], $data['redirect'], $data['tries_left'], $data['check_count']]);

        $this->check($attempt->id, $this->wrongAnswers($items))->assertStatus(200);

        $third = $this->json($this->check($attempt->id, $this->wrongAnswers($items)))['data'];
        $this->assertSame([true, 'tries_exhausted', 0, true], [$third['completed'], $third['completion_reason'], $third['tries_left'], $third['exhausted']]);
        $this->assertSame(site_url('selesai/' . $attempt->id), $third['redirect']);

        $fourth = $this->check($attempt->id, $this->rightAnswers($items));
        $fourth->assertStatus(409);
        $this->assertSame(['TRIES_EXHAUSTED', site_url('selesai/' . $attempt->id)], [$this->json($fourth)['code'], $this->json($fourth)['redirect']]);

        // /complete tetap ada dan idempoten: hasil dan redirect yang sama
        $complete = $this->post('api/attempts/' . $attempt->id . '/complete');
        $complete->assertStatus(200);
        $this->assertSame(['tries_exhausted', 1, site_url('selesai/' . $attempt->id)], [
            $this->json($complete)['data']['completion_reason'], $this->json($complete)['data']['stars'], $this->json($complete)['data']['redirect'],
        ]);
        $this->assertCount(1, $this->events('challenge_completed'), 'tidak ditutup dua kali');
    }

    /**
     * Tab lama di peramban yang sama: kunci session PHP membuat cek-nya tiba
     * SESUDAH cek tab lain, jadi tanpa `check_no` ia dihitung sebagai cek
     * baru. Dengan `check_no` (nomor cek menurut layar klien) cek dari
     * tampilan usang ditolak ATTEMPT_CHANGED tanpa tulisan apa pun.
     */
    public function testStaleCheckNumberIsRejected(): void
    {
        $attempt = $this->open('mgl-2');
        $items   = $this->expected($attempt);
        $rows    = static fn (array $answers): array => array_map(
            static fn (int $id, array $answer): array => ['item_id' => $id, 'answer' => $answer],
            array_keys($answers),
            $answers,
        );

        $first = $this->post('api/attempts/' . $attempt->id . '/check', ['answers' => $rows($this->wrongAnswers($items)), 'check_no' => 1]);
        $first->assertStatus(200);
        $this->assertSame(1, $this->json($first)['data']['check_count']);

        // Tab kedua masih menampilkan keadaan sebelum cek ke-1
        $before = [$this->tries($attempt->id), $this->responses($attempt->id), $this->conn->table('game_event_logs')->countAllResults()];
        $stale  = $this->post('api/attempts/' . $attempt->id . '/check', ['answers' => $rows($this->rightAnswers($items)), 'check_no' => 1]);
        $stale->assertStatus(409);
        $this->assertSame('ATTEMPT_CHANGED', $this->json($stale)['code']);
        $this->assertSame($before, [$this->tries($attempt->id), $this->responses($attempt->id), $this->conn->table('game_event_logs')->countAllResults()]);
        $this->assertSame(1, (int) $this->reload($attempt)->check_count);

        // Nomor yang benar diterima; tanpa nomor tetap seperti sebelumnya
        $this->post('api/attempts/' . $attempt->id . '/check', ['answers' => $rows($this->wrongAnswers($items)), 'check_no' => 2])->assertStatus(200);
        $this->post('api/attempts/' . $attempt->id . '/check', ['answers' => $rows($this->rightAnswers($items))])->assertStatus(200);
        $this->assertSame(['completed', 3], [$this->reload($attempt)->status, (int) $this->reload($attempt)->check_count]);
    }

    /** Selama masih ada kesempatan, /complete tidak dapat menutup pengerjaan. */
    public function testCompleteIsRefusedWhileTriesRemain(): void
    {
        $attempt = $this->open('wnb-3');

        $this->check($attempt->id, $this->wrongAnswers($this->expected($attempt)))->assertStatus(200);

        $response = $this->post('api/attempts/' . $attempt->id . '/complete');
        $response->assertStatus(409);
        $this->assertSame('TRIES_REMAINING', $this->json($response)['code']);
        $this->assertSame('in_progress', $this->reload($attempt)->status);
    }

    /** Soal yang sudah benar terkunci: tidak dinilai ulang dan tidak menambah try_count. */
    public function testCorrectItemsAreLocked(): void
    {
        $attempt = $this->open('mgl-3');
        $items   = $this->expected($attempt);
        $ids     = array_keys($items);
        $right   = $this->rightAnswers($items);
        $wrong   = $this->wrongAnswers($items);

        // Cek 1: dua pernyataan pertama benar, sisanya salah
        $first = $this->service->submitCheck($attempt, array_replace($wrong, array_intersect_key($right, array_flip(array_slice($ids, 0, 2)))));
        $this->assertSame(2, $first['correct_count']);

        // Muat ulang: soal benar membawa jawabannya sendiri, soal lain hanya jumlah percobaan
        $payload = $this->service->openNode($this->session(), $this->nodeId($this->conn, 'mgl-3'))['payload'];
        $states  = array_column(array_map(static fn (array $i): array => ['id' => $i['id'], 'state' => $i['state']], $payload['items']), 'state', 'id');

        $this->assertSame(['locked' => true, 'answer' => $right[$ids[0]], 'tries' => 1], $states[$ids[0]]);
        $this->assertSame(['tries' => 1], $states[$ids[4]]);

        // Cek 2: soal terkunci tidak dikirim (tidak INCOMPLETE), satu dikirim salah tetap diabaikan
        $answers          = array_diff_key($right, array_flip(array_slice($ids, 0, 2)));
        $answers[$ids[0]] = $wrong[$ids[0]];
        $second           = $this->service->submitCheck($this->reload($attempt), $answers);

        $this->assertTrue($second['results'][$ids[0]], 'soal terkunci tetap benar');
        // 6 statements + 2 anchor statements (31 §12.2): one Cek checks both cards
        $this->assertSame([8, 8, true, 'solved', 2], [$second['correct_count'], $second['total'], $second['completed'], $second['completion_reason'], $second['stars']]);

        $responses = $this->responses($attempt->id);
        $this->assertSame([1, 1, 1], [(int) $responses[$ids[0]]['try_count'], (int) $responses[$ids[0]]['solved_on_try'], (int) $responses[$ids[0]]['first_pass_correct']]);
        $this->assertSame($right[$ids[0]], json_decode((string) $responses[$ids[0]]['final_answer_json'], true), 'jawaban benar tidak tertimpa');
        $this->assertSame([2, 2, 0], [(int) $responses[$ids[4]]['try_count'], (int) $responses[$ids[4]]['solved_on_try'], (int) $responses[$ids[4]]['first_pass_correct']]);
        $this->assertCount(8 + 6, $this->tries($attempt->id), 'soal terkunci tidak menulis percobaan');
    }

    /** Soal yang belum benar wajib diisi: cek ditolak INCOMPLETE tanpa memakai kesempatan. */
    public function testIncompleteCheckDoesNotUseATry(): void
    {
        $attempt = $this->open('tmg-2');
        $items   = $this->expected($attempt);
        $ids     = array_keys($items);
        $answers = $this->rightAnswers($items);

        unset($answers[$ids[1]]);
        $answers[$ids[2]] = ['text' => '  '];

        try {
            $this->service->submitCheck($attempt, $answers);
            $this->fail('Cek dengan rumpang kosong seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['INCOMPLETE', 422, ['items' => [$ids[1], $ids[2]]]], [$e->errorCode, $e->status, $e->extra]);
        }

        $this->assertSame([0, []], [(int) $this->reload($attempt)->check_count, $this->tries($attempt->id)]);

        $response = $this->check($attempt->id, array_diff_key($answers, [$ids[1] => true]));
        $response->assertStatus(422);
        $this->assertSame(['INCOMPLETE', [$ids[1], $ids[2]]], [$this->json($response)['code'], $this->json($response)['items']]);
    }

    /**
     * Cari Objek: satu ketukan = satu percobaan petunjuk aktif. Ketukan salah
     * ke-3 menutup petunjuk (`clue_failed`); petunjuk tertutup menolak
     * ketukan berikutnya (CLUE_CLOSED); setelah keempat petunjuk tertutup
     * pengerjaan selesai `tries_exhausted`.
     */
    public function testHuntClosesCluesAfterThreeWrongTaps(): void
    {
        $attempt = $this->open('wnb-4');
        $targets = array_keys($this->roundOne($attempt));
        $decoys  = $this->decoys($attempt);

        $this->assertCount(4, $targets);
        $this->assertCount(8, $decoys);

        [$t1, $t2, $t3, $t4] = $targets;

        // Petunjuk 1: tiga ketukan salah (pengecoh, target lain, pengecoh) → ditutup
        $taps = [$decoys[0], $t2, $decoys[1]];

        foreach ($taps as $n => $tapped) {
            $result = $this->tap($attempt, $t1, $tapped);

            $this->assertSame([false, $n + 1, 2 - $n, $n === 2, false], [
                $result['correct'], $result['try_no'], $result['tries_left'], $result['clue_closed'], $result['completed'],
            ], 'ketukan ' . ($n + 1));

            // Pengecoh membawa umpan baliknya sendiri, dikirim sesudah diketuk (Dok 2 §6.4)
            $this->assertSame($tapped !== $t2, $result['decoy']);
            $this->assertSame($tapped !== $t2, $result['feedback'] !== null && $result['feedback'] !== '');
        }

        try {
            $this->tap($attempt, $t1, $t1);
            $this->fail('Petunjuk yang sudah ditutup seharusnya menolak ketukan.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['CLUE_CLOSED', 409], [$e->errorCode, $e->status]);
        }

        $closed = $this->responses($attempt->id)[$t1];
        $this->assertSame(['answered', 0, 3, 3, null], [
            $closed['status'], (int) $closed['is_correct'], (int) $closed['try_count'], (int) $closed['wrong_click_count'], $closed['solved_on_try'],
        ]);
        $this->assertSame(['item_id' => $decoys[1]], json_decode((string) $closed['final_answer_json'], true), 'ketukan terakhir apa adanya');
        $this->assertSame([['try_count' => 3]], $this->events('clue_failed'));

        // Petunjuk 2: benar pada ketukan pertama; 3: benar pada ketukan ke-3; 4: tiga kali salah
        $this->assertTrue($this->tap($attempt, $t2, $t2)['correct']);
        $this->tap($attempt, $t3, $decoys[2]);
        $this->tap($attempt, $t3, $decoys[3]);
        $found = $this->tap($attempt, $t3, $t3);
        $this->assertSame([true, 3, true, false], [$found['correct'], $found['try_no'], $found['clue_closed'], $found['completed']]);

        $this->tap($attempt, $t4, $decoys[4]);
        $this->tap($attempt, $t4, $decoys[5]);
        $closing = $this->tap($attempt, $t4, $decoys[6]);
        $this->assertSame([true, false], [$closing['clue_closed'], $closing['completed']], 'round 2 is still open');

        // Round 2 (31 §12.1): every clue found on the first tap closes the attempt
        $last = $this->finishRoundTwo($attempt);

        $this->assertSame([true, true, 'tries_exhausted', 1], [$last['clue_closed'], $last['completed'], $last['completion_reason'], $last['stars']]);
        $this->assertSame(site_url('selesai/' . $attempt->id), $last['redirect']);
        $this->assertSame(['completed', 'tries_exhausted', 1 + 4, 2 + 4], [
            $this->reload($attempt)->status, $this->reload($attempt)->completion_reason, (int) $this->reload($attempt)->first_pass_correct, (int) $this->reload($attempt)->final_correct,
        ]);
        $this->assertCount(2, $this->events('clue_failed'));

        // item_tries: 3 + 1 + 3 + 3 taps in round 1, then 4 in round 2, no check_no
        $tries = array_slice($this->tries($attempt->id), 0, 10);
        $this->assertSame(
            [[$t1, 1], [$t1, 2], [$t1, 3], [$t2, 1], [$t3, 1], [$t3, 2], [$t3, 3], [$t4, 1], [$t4, 2], [$t4, 3]],
            array_map(static fn (array $t): array => [(int) $t['challenge_item_id'], (int) $t['try_no']], $tries),
        );
        $this->assertSame([null], array_values(array_unique(array_column($tries, 'check_no'))));

        // Pengecoh tidak pernah menjadi soal yang dijawab
        foreach ($decoys as $decoy) {
            $this->assertSame('pending', $this->responses($attempt->id)[$decoy]['status']);
        }
    }

    /** Keempat benda ditemukan pada ketukan pertama: selesai `solved` ★★★. */
    public function testHuntSolvedOnFirstTaps(): void
    {
        $attempt = $this->open('tmg-4');
        $targets = array_keys($this->roundOne($attempt));

        foreach ($targets as $target) {
            $result = $this->tap($attempt, $target, $target);

            $this->assertSame([true, 1, false], [$result['correct'], $result['try_no'], $result['completed']]);
        }

        $result = $this->finishRoundTwo($attempt);
        $this->assertTrue($result['completed']);

        $this->assertSame(['solved', 3], [$result['completion_reason'], $result['stars']]);
        $this->assertSame([], $this->events('clue_failed'));
    }

    /** Ketukan lewat HTTP: respons membawa tries_left, clue_closed, dan penutupan. */
    public function testHuntTapsOverHttp(): void
    {
        $attempt = $this->open('mgl-4');
        $targets = array_keys($this->roundOne($attempt));
        $decoy   = $this->decoys($attempt)[0];

        foreach ([1, 2, 3] as $n) {
            $data = $this->json($this->respond($attempt, $targets[0], $decoy, 'tap-' . $n))['data'];

            $this->assertSame([$n, 3 - $n, $n === 3, true], [$data['try_no'], $data['tries_left'], $data['clue_closed'], $data['decoy']]);
        }

        $closed = $this->respond($attempt, $targets[0], $targets[0], 'tap-4');
        $closed->assertStatus(409);
        $this->assertSame('CLUE_CLOSED', $this->json($closed)['code']);

        foreach (array_slice($targets, 1) as $n => $target) {
            $data = $this->json($this->respond($attempt, $target, $target, 'hit-' . $n))['data'];
        }

        $this->assertFalse($data['completed'], 'round 2 is still open');

        // Round 2 over HTTP: the same answer shape, a part ref instead of an object ref
        foreach ($this->roundTwo($attempt) as $n => $clue) {
            $data = $this->json($this->post('api/attempts/' . $attempt->id . '/responses', [
                'item_id' => $clue->id, 'answer' => ['object' => $this->keyPartRef($attempt, $clue)], 'client_event_id' => 'part-' . $n, 'duration_ms' => 700,
            ]))['data'];
        }

        $this->assertSame([true, 'tries_exhausted', site_url('selesai/' . $attempt->id)], [$data['completed'], $data['completion_reason'], $data['redirect']]);
    }

    /**
     * Dua cek bersamaan (tab kedua, atau kiriman ulang saat Wi-Fi lambat)
     * membawa check_count yang sama: hanya yang pertama berlaku, yang kedua
     * ditolak tanpa menulis apa pun.
     */
    public function testDuplicateCheckIsRejectedWithoutWrites(): void
    {
        $attempt = $this->open('tmg-2');
        $items   = $this->expected($attempt);
        $stale   = $this->reload($attempt);

        $this->service->submitCheck($stale, $this->wrongAnswers($items));
        $before = [$this->tries($attempt->id), $this->responses($attempt->id), $this->conn->table('game_event_logs')->countAllResults()];

        try {
            $this->service->submitCheck($stale, $this->wrongAnswers($items));
            $this->fail('Cek ganda seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['ATTEMPT_CHANGED', 409, []], [$e->errorCode, $e->status, $e->extra], 'masih berjalan: klien memuat ulang');
        }

        $this->assertSame($before, [$this->tries($attempt->id), $this->responses($attempt->id), $this->conn->table('game_event_logs')->countAllResults()]);
        $this->assertSame(1, (int) $this->reload($attempt)->check_count);
    }

    /** Cek ke-3 ganda: tanpa try_no 4, pengerjaan ditutup dan dicatat sekali. */
    public function testDuplicateThirdCheckClosesOnce(): void
    {
        $attempt = $this->open('wnb-1');
        $items   = $this->expected($attempt);

        foreach ([1, 2] as $check) {
            $this->service->submitCheck($this->reload($attempt), $this->wrongAnswers($items));
        }

        $stale = $this->reload($attempt);
        $this->assertTrue($this->service->submitCheck($stale, $this->wrongAnswers($items))['completed']);

        try {
            $this->service->submitCheck($stale, $this->rightAnswers($items));
            $this->fail('Cek ke-3 ganda seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['TRIES_EXHAUSTED', site_url('selesai/' . $attempt->id)], [$e->errorCode, $e->extra['redirect'] ?? null]);
        }

        $this->assertSame(3, max(array_map('intval', array_column($this->tries($attempt->id), 'try_no'))));
        $this->assertCount(3, $this->events('challenge_checked'));
        $this->assertCount(1, $this->events('challenge_tries_exhausted'));
        $this->assertCount(1, $this->events('challenge_completed'));
        $this->assertSame([3, 1], [(int) $this->reload($attempt)->check_count, (int) $this->reload($attempt)->stars]);
    }

    /** Cek ganda sesudah pengerjaan selesai benar: diarahkan ke layar hasil, bukan memuat ulang tantangan. */
    public function testDuplicateSolvingCheckPointsToTheResult(): void
    {
        $attempt = $this->open('mgl-1');
        $items   = $this->expected($attempt);
        $stale   = $this->reload($attempt);

        $this->assertSame('solved', $this->service->submitCheck($stale, $this->rightAnswers($items))['completion_reason']);

        try {
            $this->service->submitCheck($stale, $this->rightAnswers($items));
            $this->fail('Cek ganda seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['ATTEMPT_CHANGED', site_url('selesai/' . $attempt->id)], [$e->errorCode, $e->extra['redirect'] ?? null]);
        }

        $this->assertCount(1, $this->events('challenge_completed'));
        $this->assertSame([1, 3], [(int) $this->reload($attempt)->check_count, (int) $this->reload($attempt)->stars]);
    }

    /**
     * Dua ketukan Cari Objek bersamaan: yang kedua membaca baris jawaban
     * sebelum yang pertama tersimpan. Ditolak 409, bukan galat kunci unik
     * item_tries (500), dan tidak menulis percobaan ganda.
     */
    public function testConcurrentHuntTapIsRejected(): void
    {
        $attempt = $this->open('tmg-4');
        [$t1, $t2] = array_keys($this->expected($attempt));
        $decoy     = $this->decoys($attempt)[0];

        // Ketukan salah yang belum menutup petunjuk → ATTEMPT_CHANGED
        $stale = $this->staleResponses($attempt->id, $t1);
        $this->tap($attempt, $t1, $decoy);
        Factories::injectMock('models', ItemResponseModel::class, $stale);

        try {
            $this->tap($attempt, $t1, $t1);
            $this->fail('Ketukan bersamaan seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame(['ATTEMPT_CHANGED', 409], [$e->errorCode, $e->status]);
        }

        Factories::reset('models');
        $this->assertSame([1], array_map('intval', array_column($this->tries($attempt->id), 'try_no')));

        // Ketukan yang sudah menutup petunjuk → CLUE_CLOSED
        $stale = $this->staleResponses($attempt->id, $t2);
        $this->assertTrue($this->tap($attempt, $t2, $t2)['correct']);
        Factories::injectMock('models', ItemResponseModel::class, $stale);

        try {
            $this->tap($attempt, $t2, $decoy);
            $this->fail('Ketukan pada petunjuk yang baru tertutup seharusnya ditolak.');
        } catch (ChallengeRuleException $e) {
            $this->assertSame('CLUE_CLOSED', $e->errorCode);
        }

        Factories::reset('models');
        $this->assertSame(['answered', 1, 1], [$this->responses($attempt->id)[$t2]['status'], (int) $this->responses($attempt->id)[$t2]['is_correct'], (int) $this->responses($attempt->id)[$t2]['try_count']]);
        $this->assertCount(2, $this->tries($attempt->id));
    }

    /**
     * Soal yang belum benar dinonaktifkan admin di tengah pengerjaan: sisa
     * soalnya sudah benar semua, jadi pengerjaan ditutup saat dibuka lagi,
     * dan bintangnya hanya menghitung soal yang masih dinilai.
     */
    public function testDeactivatedItemIsLeftOutOfStars(): void
    {
        $attempt = $this->open('mgl-3');
        $items   = $this->expected($attempt);
        $ids     = array_keys($items);

        $this->service->submitCheck($attempt, array_replace($this->rightAnswers($items), [$ids[4] => $this->wrongAnswers($items)[$ids[4]]]));
        $this->deactivate($ids[4]);

        $opened = $this->service->openNode($this->session(), $this->nodeId($this->conn, 'mgl-3'));

        $this->assertSame([null, site_url('selesai/' . $attempt->id)], [$opened['payload'], $opened['redirect'] ?? null]);
        $row = $this->reload($attempt);
        $this->assertSame(['completed', 'solved', 3, 7, 7, 7], [
            $row->status, $row->completion_reason, (int) $row->stars, (int) $row->first_pass_correct, (int) $row->final_correct, (int) $row->scorable_items,
        ]);
        $this->assertCount(1, $this->events('challenge_completed'));
    }

    /**
     * Cari Objek: petunjuk terakhir yang belum ditemukan dinonaktifkan. Dulu
     * setiap ketukan dijawab CLUE_CLOSED dan halaman dimuat ulang terus;
     * kini pengerjaan ditutup saat dibuka dan pemain menuju layar hasil.
     */
    public function testHuntWithDeactivatedLastClueIsClosedOnOpen(): void
    {
        $attempt = $this->open('wnb-4');

        foreach (array_keys($this->roundOne($attempt)) as $target) {
            $this->assertFalse($this->tap($attempt, $target, $target)['completed']);
        }

        $clues = $this->roundTwo($attempt);

        foreach (array_slice($clues, 0, 3) as $clue) {
            $this->assertFalse($this->service->submitAnswer($this->reload($attempt), $clue->id, ['object' => $this->keyPartRef($attempt, $clue)])['completed']);
        }

        $this->deactivate($clues[3]->id);

        $opened = $this->service->openNode($this->session(), $this->nodeId($this->conn, 'wnb-4'));

        $this->assertSame(site_url('selesai/' . $attempt->id), $opened['redirect'] ?? null);
        $this->assertSame(['completed', 'solved', 3], [$this->reload($attempt)->status, $this->reload($attempt)->completion_reason, (int) $this->reload($attempt)->stars]);

        // Dibuka lagi sesudahnya: pengerjaan baru, bukan pengalihan berulang
        $again = $this->service->openNode($this->session(), $this->nodeId($this->conn, 'wnb-4'));
        $this->assertArrayNotHasKey('redirect', $again);
        $this->assertNotSame($attempt->id, $again['attempt']->id);
    }

    /** Keluar dari tab lama sesudah pengerjaan ditutup tab lain: tetap `completed`, tanpa event ditinggalkan. */
    public function testAbandonDoesNotOverrideACompletedAttempt(): void
    {
        $attempt = $this->open('wnb-2');
        $stale   = $this->reload($attempt);

        $this->service->submitCheck($this->reload($attempt), $this->rightAnswers($this->expected($attempt)));
        $this->service->abandonAttempt($stale);

        $this->assertSame('completed', $this->reload($attempt)->status);
        $this->assertSame([], $this->events('challenge_abandoned'));
    }

    // ------------------------------------------------------------ bantu

    /**
     * ItemResponseModel yang dua kali pertama mengembalikan baris jawaban
     * $itemId seperti saat ini (sebelum ketukan berikutnya tersimpan):
     * pembacaan di submitAnswer() dan di registerWrongClick()/applyAnswer().
     */
    private function staleResponses(int $attemptId, int $itemId): ItemResponseModel
    {
        $model = new class () extends ItemResponseModel {
            public ?ItemResponse $stale = null;
            public int $reads           = 2;

            public function findOne(int $attemptId, int $itemId): ?ItemResponse
            {
                if ($this->stale !== null && $this->reads > 0 && $this->stale->challenge_item_id === $itemId) {
                    $this->reads--;

                    return clone $this->stale;
                }

                return parent::findOne($attemptId, $itemId);
            }
        };
        $model->stale = $model->findOne($attemptId, $itemId);

        return $model;
    }

    private function deactivate(int $itemId): void
    {
        $this->conn->table('challenge_items')->where('id', $itemId)->update(['is_active' => 0]);
        // MockCache menyimpan kunci tanpa prefix, jadi flush() cukup dibantu clean()
        service('contentRepository')->flush();
        service('cache')->clean();
    }


    private function session(): GameSession
    {
        return model(GameSessionModel::class)->find(1);
    }

    private function open(string $ref): ChallengeAttempt
    {
        $nodeId = $this->nodeId($this->conn, $ref);

        $this->service->openNode($this->session(), $nodeId);

        $attempt = model(ChallengeAttemptModel::class)->currentInProgress(1, $nodeId);
        $this->assertInstanceOf(ChallengeAttempt::class, $attempt, $ref);

        return $attempt;
    }

    private function reload(ChallengeAttempt $attempt): ChallengeAttempt
    {
        return model(ChallengeAttemptModel::class)->find($attempt->id);
    }

    /**
     * Soal yang diminta dijawab pada pengerjaan ini (tanpa pengecoh), urut pilihan.
     *
     * @return array<int, ChallengeItem>
     */
    private function expected(ChallengeAttempt $attempt): array
    {
        $bank = [];

        foreach (service('contentRepository')->itemBank($attempt->challenge_node_id) as $item) {
            $bank[$item->id] = $item;
        }

        $out = [];

        foreach ($attempt->selectedItemIds() as $id) {
            if (isset($bank[$id]) && ! $bank[$id]->isDecoy() && $bank[$id]->scorable) {
                $out[$id] = $bank[$id];
            }
        }

        return $out;
    }

    /** @return list<int> id pengecoh Cari Objek pada pengerjaan ini */
    private function decoys(ChallengeAttempt $attempt): array
    {
        $out = [];

        foreach (service('contentRepository')->itemBank($attempt->challenge_node_id) as $item) {
            if ($item->isDecoy() && $attempt->hasItem($item->id)) {
                $out[] = $item->id;
            }
        }

        return $out;
    }

    /**
     * @param array<int, ChallengeItem> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function rightAnswers(array $items): array
    {
        return array_map(static fn (ChallengeItem $item): array => match ($item->interaction_type) {
            'puzzle_arrange'  => ['order' => array_values((array) $item->answerKey('order'))],
            'fill_blank_bank' => ['text' => (string) $item->answerKey('text_id')],
            'verdict_card'    => ['verdict' => $item->verdict()],
        }, $items);
    }

    /**
     * Jawaban salah yang wajar: keping terbalik, kata rumpang berikutnya,
     * penilaian sebaliknya.
     *
     * @param array<int, ChallengeItem> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function wrongAnswers(array $items): array
    {
        $words = array_values(array_map(static fn (ChallengeItem $item): string => (string) $item->answerKey('text_id'), $items));
        $out   = [];
        $n     = 0;

        foreach ($items as $id => $item) {
            $out[$id] = match ($item->interaction_type) {
                'puzzle_arrange'  => ['order' => array_reverse(array_values((array) $item->answerKey('order')))],
                'fill_blank_bank' => ['text' => $words[($n + 1) % count($words)]],
                'verdict_card'    => ['verdict' => $item->verdict() === 'benar' ? 'salah' : 'benar'],
            };
            $n++;
        }

        return $out;
    }

    /**
     * Satu ketukan Cari Objek lewat layanan: benda $tapped untuk petunjuk $clue.
     *
     * @return array<string, mixed>
     */
    /** @return array<int, ChallengeItem> round-1 clues of the attempt */
    private function roundOne(ChallengeAttempt $attempt): array
    {
        return array_filter($this->expected($attempt), static fn (ChallengeItem $i): bool => $i->interaction_type === 'find_object');
    }

    /** @return list<ChallengeItem> round-2 clues of the attempt */
    private function roundTwo(ChallengeAttempt $attempt): array
    {
        return array_values(array_filter($this->expected($attempt), static fn (ChallengeItem $i): bool => $i->interaction_type === 'find_part'));
    }

    /** Opaque ref of the key part of one round-2 clue, as the server derives it. */
    private function keyPartRef(ChallengeAttempt $attempt, ChallengeItem $clue): string
    {
        $post = service('contentRepository')->postScene($attempt->challenge_node_id, (string) $attempt->variant_group);

        foreach ($post['parts'] as $part) {
            if ($part['part_key'] === $clue->answerKey('part')) {
                return (string) (new \ReflectionMethod(ChallengeService::class, 'partRef'))->invoke($this->service, $attempt->id, (int) $part['id']);
            }
        }

        $this->fail('Key part not found.');
    }

    /** Every round-2 clue found on the first tap; returns the last result. */
    private function finishRoundTwo(ChallengeAttempt $attempt): array
    {
        foreach ($this->roundTwo($attempt) as $clue) {
            $result = $this->service->submitAnswer($this->reload($attempt), $clue->id, ['object' => $this->keyPartRef($attempt, $clue)], ['duration_ms' => 900]);
        }

        return $result;
    }

    private function tap(ChallengeAttempt $attempt, int $clue, int $tapped): array
    {
        return $this->service->submitAnswer($this->reload($attempt), $clue, ['object' => $this->ref($attempt, $tapped)], ['duration_ms' => 900]);
    }

    private function ref(ChallengeAttempt $attempt, int $itemId): string
    {
        return (new ReflectionMethod(ChallengeService::class, 'objectRef'))->invoke($this->service, $attempt->id, $itemId);
    }

    /** @param array<int, array<string, mixed>> $answers */
    private function check(int $attemptId, array $answers): TestResponse
    {
        $rows = [];

        foreach ($answers as $itemId => $answer) {
            $rows[] = ['item_id' => $itemId, 'answer' => $answer, 'first_input_ms' => 500, 'last_input_ms' => 1500];
        }

        return $this->post('api/attempts/' . $attemptId . '/check', ['answers' => $rows]);
    }

    private function respond(ChallengeAttempt $attempt, int $clue, int $tapped, string $eventId): TestResponse
    {
        return $this->post('api/attempts/' . $attempt->id . '/responses', [
            'item_id' => $clue, 'answer' => ['object' => $this->ref($attempt, $tapped)], 'client_event_id' => $eventId, 'duration_ms' => 700,
        ]);
    }

    /**
     * POST JSON sebagai siswa yang sedang bermain (sesi 1), dengan token CSRF
     * di header seperti fetch() permainan.
     *
     * @param array<string, mixed> $body
     */
    private function post(string $path, array $body = []): TestResponse
    {
        Services::resetSingle('security');
        Services::resetSingle('gameContext');

        return $this->withSession(['participant_id' => 1, 'game_session_id' => 1, 'gelita_csrf' => self::CSRF_HASH])
            ->withHeaders(['X-CSRF-TOKEN' => self::CSRF_HASH . str_repeat('0', 32)])
            ->withBodyFormat('json')
            ->call(Method::POST, $path, $body);
    }

    /** @return array<string, mixed> */
    private function json(TestResponse $response): array
    {
        return json_decode((string) $response->getJSON(), true) ?? [];
    }

    /** @return list<array<string, mixed>> */
    private function tries(int $attemptId): array
    {
        return $this->conn->table('item_tries')->where('challenge_attempt_id', $attemptId)->orderBy('id')->get()->getResultArray();
    }

    /** @return array<int, array<string, mixed>> keyed by challenge_item_id */
    private function responses(int $attemptId): array
    {
        $out = [];

        foreach ($this->conn->table('item_responses')->where('challenge_attempt_id', $attemptId)->get()->getResultArray() as $row) {
            $out[(int) $row['challenge_item_id']] = $row;
        }

        return $out;
    }

    /** @return list<array<string, mixed>> payload event bertipe $type, urut waktu */
    private function events(string $type): array
    {
        $rows = $this->conn->table('game_event_logs')->where('event_type', $type)->orderBy('id')->get()->getResultArray();

        return array_map(static fn (array $row): array => json_decode((string) $row['payload_json'], true), $rows);
    }
}
