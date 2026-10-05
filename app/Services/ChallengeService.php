<?php

namespace App\Services;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\ItemResponse;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeItemModel;
use App\Models\GameSessionModel;
use App\Models\ItemResponseModel;
use App\Models\ItemTryModel;
use App\Models\ResearchStudyModel;
use App\Models\SessionProgressModel;

/**
 * Inti permainan: membuka node, menerima jawaban, menutup pengerjaan.
 *
 * Setiap pengerjaan memakai satu variasi soal yang dipilih acak (Dok 1 §9.2).
 * Setiap percobaan soal ditulis apa adanya ke `item_tries` (Dok 1 §12.2).
 *
 * Seluruh penilaian benar/salah dilakukan di sini memakai kunci dari database.
 * Field `correct` yang dikirim browser selalu diabaikan, dan `answer_key_json`
 * tidak pernah ikut dalam payload pemain.
 */
class ChallengeService
{
    /**
     * Membuka node: membuat attempt, memilih item, menyiapkan payload pemain.
     * Attempt `in_progress` yang sudah ada dipakai ulang (resume).
     *
     * @return array{attempt: ChallengeAttempt, payload: array<string, mixed>}
     */
    public function openNode(GameSession $session, int $nodeId): array
    {
        $node = service('contentRepository')->node($nodeId);

        if ($node === null) {
            throw new \RuntimeException("Node {$nodeId} tidak ditemukan atau tidak aktif.");
        }

        $this->assertLevelUnlocked($session, $node);

        $attempts = model(ChallengeAttemptModel::class);
        $attempt  = $attempts->currentInProgress($session->id, $nodeId);
        $bank     = service('contentRepository')->itemBank($nodeId);
        $resumed  = $attempt !== null;

        if ($resumed) {
            $items = $this->itemsByIds($bank, $attempt->selectedItemIds());

            // Semua soal percobaan ini sudah diistirahatkan (isi tantangan
            // diperbarui): tutup percobaan lama dan buka yang baru.
            if ($items === []) {
                $this->abandonAttempt($attempt, ['via' => 'content_changed']);
                $attempt = null;
                $resumed = false;
            }
        }

        if (! $resumed) {
            // Satu variasi acak; variasi yang sudah pernah didapat siswa ini
            // (di semua sesinya) dihindari selama masih ada yang lain.
            $seen   = $attempts->variantsSeen($session->participant_id, $nodeId);
            $picked = model(ChallengeItemModel::class)->pickVariant($nodeId, $seen, $bank);
            $items  = $picked['items'];

            if ($items === []) {
                throw new \RuntimeException("Bank soal node {$nodeId} kosong.");
            }

            $db = db_connect();
            $db->transBegin();

            try {
                $attemptId = $attempts->insert([
                    'session_id'             => $session->id,
                    'challenge_node_id'      => $nodeId,
                    'attempt_no'             => $attempts->nextAttemptNo($session->id, $nodeId),
                    'variant_group'          => $picked['group'],
                    'status'                 => 'in_progress',
                    'selected_item_ids_json' => array_map(static fn (ChallengeItem $i): int => $i->id, $items),
                    'scorable_items'         => $this->countScorable($items),
                    'max_tries'              => config('Gelita')->maxTries,
                    'started_at'             => date('Y-m-d H:i:s'),
                ], true);

                if ($attemptId === false) {
                    throw new \RuntimeException('Attempt gagal dibuat: ' . implode(' ', $attempts->errors()));
                }

                $attempt = $attempts->find((int) $attemptId);

                model(ItemResponseModel::class)->createPlaceholders($attempt->id, $items);

                service('eventService')->record($session, 'challenge_opened', [
                    'attempt_no'    => $attempt->attempt_no,
                    'variant_group' => $attempt->variant_group,
                    'item_count'    => count($items),
                ], [
                    'level_id'             => $node->level_id,
                    'challenge_node_id'    => $nodeId,
                    'challenge_attempt_id' => $attempt->id,
                ]);

                $db->transCommit();
            } catch (\Throwable $e) {
                $db->transRollback();

                throw $e;
            }
        }

        model(GameSessionModel::class)->update($session->id, [
            'last_level_id'          => $node->level_id,
            'last_challenge_node_id' => $nodeId,
            'last_active_at'         => date('Y-m-d H:i:s'),
        ]);

        $progress = model(SessionProgressModel::class);
        $progress->ensure($session->id);
        $progress->update($session->id, [
            'current_level_id' => $node->level_id,
            'current_node_id'  => $nodeId,
        ]);

        return [
            'attempt' => $attempt,
            'payload' => $this->buildPayload($session, $node, $attempt, $items, $resumed),
        ];
    }

    /**
     * Menerima satu ketukan benda pada Cari Objek. Setiap ketukan adalah satu
     * percobaan untuk petunjuk yang sedang dicari (satu baris `item_tries`).
     *
     * @param array<string, mixed> $answer
     * @param array<string, mixed> $meta   duration_ms (sejak petunjuk tampil)
     *
     * @return array{correct: bool, first_pass: bool, wrong_click: bool, decoy: bool,
     *               already_answered: bool, feedback: ?string, try_no: int,
     *               progress: array{answered: int, total: int}}
     */
    public function submitAnswer(ChallengeAttempt $attempt, int $itemId, array $answer, array $meta = []): array
    {
        $this->assertInProgress($attempt);

        if (! $attempt->hasItem($itemId)) {
            throw new \InvalidArgumentException("Butir {$itemId} tidak termasuk dalam percobaan ini.");
        }

        $session = $this->sessionOf($attempt);
        $node    = $this->nodeOf($attempt);
        $item    = $this->itemOf($node->id, $itemId);
        $locale  = $session->resolvedLocale();

        if ($item->interaction_type === 'find_object') {
            $answer = $this->resolveObjectAnswer($attempt, $answer);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            // Klik objek yang salah pada engine `cari`: dicatat, tetapi tidak
            // mengubah first_pass_correct petunjuk yang sedang dicari.
            if ($item->interaction_type === 'find_object' && $this->isWrongTargetClick($item, $answer, $itemId)) {
                $result = $this->registerWrongClick($session, $attempt, $item, $answer, $locale, $meta);
                $db->transCommit();

                return $result;
            }

            $correct  = $this->grade($node, $item, $answer, $locale);
            $applied  = $this->applyAnswer($attempt, $item, $answer, $correct, [
                'last_input_ms' => $meta['duration_ms'] ?? null,
            ]);
            $feedback = $correct ? null : $item->wrongFeedback($locale);

            service('eventService')->record(
                $session,
                $applied['changed'] ? 'answer_changed' : 'answer_submitted',
                [
                    'try_no'       => $applied['try_no'],
                    'correct'      => $correct,
                    'first_pass'   => $applied['first_pass'],
                    'change_count' => $applied['change_count'],
                ],
                [
                    'level_id'             => $node->level_id,
                    'challenge_node_id'    => $node->id,
                    'challenge_attempt_id' => $attempt->id,
                    'challenge_item_id'    => $itemId,
                ],
            );

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return [
            'correct'          => $correct,
            'first_pass'       => $applied['first_pass'],
            'wrong_click'      => false,
            'decoy'            => false,
            'already_answered' => false,
            'feedback'         => $feedback,
            'try_no'           => $applied['try_no'],
            'progress'         => $this->progressOf($attempt),
        ];
    }

    /**
     * Tombol "Cek jawabanku" untuk engine batch (rumpang, boleh, puzzle).
     * Percobaan pertama menetapkan first_pass_correct seluruh soal. Setiap
     * soal yang dicek menulis satu baris `item_tries`; soal yang sudah benar
     * terkunci dan tidak dicek ulang.
     *
     * $answers[itemId] boleh memuat `first_input_ms` / `last_input_ms` (ms
     * sejak tantangan dibuka, dari klien); keduanya tidak ikut answer_json.
     *
     * @param array<int, array<string, mixed>> $answers keyed by challenge_item_id
     *
     * @return array{results: array<int, bool>, correct_count: int, total: int, all_correct: bool,
     *               check_count: int, try_no: int, tries_left: int, exhausted: bool,
     *               detail: array<int, array<string, mixed>>}
     */
    public function submitCheck(ChallengeAttempt $attempt, array $answers): array
    {
        $this->assertInProgress($attempt);

        $session  = $this->sessionOf($attempt);
        $node     = $this->nodeOf($attempt);
        $locale   = $session->resolvedLocale();
        $attempts = model(ChallengeAttemptModel::class);

        $db = db_connect();
        $db->transBegin();

        try {
            $checkCount = $attempt->check_count + 1;

            $attempts->update($attempt->id, ['check_count' => $checkCount]);

            $attempt = $attempts->find($attempt->id);

            $results   = [];
            $detail    = [];
            $tryNo     = 0;
            $responses = model(ItemResponseModel::class)->forAttempt($attempt->id);

            foreach ($answers as $itemId => $answer) {
                $itemId = (int) $itemId;

                if (! $attempt->hasItem($itemId) || ! is_array($answer)) {
                    continue;
                }

                $item = $this->itemOf($node->id, $itemId);

                if ($item->isDecoy()) {
                    continue;
                }

                // Soal yang sudah benar terkunci: tidak dicek ulang, tanpa percobaan baru
                if (isset($responses[$itemId]) && $responses[$itemId]->isAnswered() && $responses[$itemId]->is_correct) {
                    $results[$itemId] = true;

                    continue;
                }

                $timing = [
                    'first_input_ms' => $answer['first_input_ms'] ?? null,
                    'last_input_ms'  => $answer['last_input_ms'] ?? null,
                    'check_no'       => $checkCount,
                ];
                unset($answer['first_input_ms'], $answer['last_input_ms'], $answer['duration_ms']);

                // `cari` dinilai lewat token objek, juga di jalur batch: id butir
                // mentah dari klien tidak pernah dipercaya (lihat resolveObjectAnswer())
                if ($item->interaction_type === 'find_object') {
                    try {
                        $answer = $this->resolveObjectAnswer($attempt, $answer);
                    } catch (\InvalidArgumentException) {
                        $answer = ['item_id' => 0];
                    }
                }

                $correct         = $this->grade($node, $item, $answer, $locale);
                $results[$itemId] = $correct;

                if ($item->interaction_type === 'puzzle_arrange') {
                    $detail[$itemId] = $this->orderDetail($item, $answer);
                }

                $applied = $this->applyAnswer($attempt, $item, $answer, $correct, $timing);
                $tryNo   = max($tryNo, $applied['try_no']);
            }

            $correctCount = count(array_filter($results));
            $allCorrect   = $results !== [] && $correctCount === count($results);
            $triesLeft    = max(0, $attempt->maxTries() - $checkCount);
            $exhausted    = ! $allCorrect && $triesLeft === 0;

            service('eventService')->record($session, 'challenge_checked', [
                'check_no'      => $checkCount,
                'try_no'        => $tryNo,
                'correct_count' => $correctCount,
                'total'         => count($results),
                'exhausted'     => $exhausted,
            ], [
                'level_id'             => $node->level_id,
                'challenge_node_id'    => $node->id,
                'challenge_attempt_id' => $attempt->id,
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return [
            'results'       => $results,
            'correct_count' => $correctCount,
            'total'         => count($results),
            'all_correct'   => $allCorrect,
            'check_count'   => $checkCount,
            'try_no'        => $tryNo,
            'tries_left'    => $triesLeft,
            'exhausted'     => $exhausted,
            'detail'        => $detail,
        ];
    }

    /**
     * Rincian cek Susun Gambar, dihitung dari kunci di server:
     * `pieces_correct` = jumlah posisi yang sudah tepat (untuk pesan "masih
     * ada N keping yang keliru"). Susunan benarnya memang publik (keping 1–9
     * dari kiri atas), jadi slot yang keliru ikut dikirim (`misplaced`) agar
     * kepingnya dapat ditandai.
     *
     * @param array<string, mixed> $answer
     *
     * @return array{pieces_correct: int, misplaced?: list<int>}
     */
    private function orderDetail(ChallengeItem $item, array $answer): array
    {
        $key   = $item->answerKey('order');
        $given = isset($answer['order']) && is_array($answer['order']) ? array_values($answer['order']) : [];

        if (! is_array($key)) {
            return ['pieces_correct' => 0];
        }

        $key       = array_values($key);
        $correct   = 0;
        $misplaced = [];

        foreach ($key as $slot => $expected) {
            if (isset($given[$slot]) && (string) $given[$slot] === (string) $expected) {
                $correct++;
            } else {
                $misplaced[] = $slot;
            }
        }

        return ['pieces_correct' => $correct, 'misplaced' => $misplaced];
    }

    /**
     * Mencatat pemakaian petunjuk.
     *
     * @return array{hint_count: int, text: string}
     */
    public function useHint(ChallengeAttempt $attempt, int $hintId, ?int $itemId = null): array
    {
        $this->assertInProgress($attempt);

        $session = $this->sessionOf($attempt);
        $node    = $this->nodeOf($attempt);
        $locale  = $session->resolvedLocale();

        $hints = service('contentRepository')->hintsFor($node->id, $itemId);
        $hint  = null;

        foreach ($hints as $row) {
            if ((int) $row['id'] === $hintId) {
                $hint = $row;
                break;
            }
        }

        if ($hint === null) {
            throw new \InvalidArgumentException("Petunjuk {$hintId} bukan milik node ini.");
        }

        $attempts = model(ChallengeAttemptModel::class);

        $db = db_connect();
        $db->transBegin();

        try {
            $hintCount = $attempt->hint_count + 1;
            $attempts->update($attempt->id, ['hint_count' => $hintCount]);

            if ($itemId !== null && $attempt->hasItem($itemId)) {
                $responses = model(ItemResponseModel::class);
                $response  = $responses->findOne($attempt->id, $itemId);

                if ($response !== null) {
                    $responses->update($response->id, ['hint_used' => 1]);
                }
            }

            service('eventService')->record($session, 'hint_opened', ['hint_id' => $hintId], [
                'level_id'             => $node->level_id,
                'challenge_node_id'    => $node->id,
                'challenge_attempt_id' => $attempt->id,
                'challenge_item_id'    => $itemId,
            ]);

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return ['hint_count' => $hintCount, 'text' => tr($hint, 'text', $locale)];
    }

    /**
     * Menutup pengerjaan: bintang dihitung ScoringService (aturan `bintang-1`),
     * progres diperbarui, wilayah berikutnya dibuka bila seluruh node tuntas.
     * `completion_reason` = `solved` bila semua soal benar di akhir, selain itu
     * `tries_exhausted` (jawaban disimpan apa adanya).
     *
     * @return array{stars: int, first_pass_correct: int, final_correct: int, scorable_items: int,
     *               completion_reason: string, level_completed: bool, next_level_unlocked: ?int, shards: int}
     */
    public function completeAttempt(ChallengeAttempt $attempt): array
    {
        $this->assertInProgress($attempt);

        $session  = $this->sessionOf($attempt);
        $node     = $this->nodeOf($attempt);
        $attempts = model(ChallengeAttemptModel::class);

        $db = db_connect();
        $db->transBegin();

        try {
            $startedAt = strtotime((string) $attempt->started_at) ?: time();

            $counts = model(ItemResponseModel::class)->countCorrect($attempt->id);
            $reason = $counts['final'] >= $counts['scorable'] ? 'solved' : 'tries_exhausted';

            $attempts->update($attempt->id, [
                'status'            => 'completed',
                'completion_reason' => $reason,
                'completed_at'      => date('Y-m-d H:i:s'),
                'duration_ms'       => max(0, (time() - $startedAt) * 1000),
            ]);

            $attempt = $attempts->find($attempt->id);
            $scored  = service('scoringService')->finalize($attempt);

            $progress = $this->refreshProgress($session, $node);

            service('eventService')->record($session, 'challenge_completed', [
                'stars'              => $scored['stars'],
                'first_pass_correct' => $scored['first_pass_correct'],
                'final_correct'      => $scored['final_correct'],
                'scorable_items'     => $scored['scorable_items'],
                'completion_reason'  => $reason,
                'attempt_no'         => $attempt->attempt_no,
            ], [
                'level_id'             => $node->level_id,
                'challenge_node_id'    => $node->id,
                'challenge_attempt_id' => $attempt->id,
            ]);

            if ($progress['level_completed']) {
                service('eventService')->record($session, 'level_completed', [
                    'level_stars' => $progress['level_stars'],
                ], ['level_id' => $node->level_id]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        service('sessionService')->completeIfFinished($session->id);

        return [
            'stars'               => $scored['stars'],
            'first_pass_correct'  => $scored['first_pass_correct'],
            'final_correct'       => $scored['final_correct'],
            'scorable_items'      => $scored['scorable_items'],
            'completion_reason'   => $reason,
            'level_completed'     => $progress['level_completed'],
            'next_level_unlocked' => $progress['next_level_unlocked'],
            'shards'              => $progress['shards'],
        ];
    }

    /**
     * Attempt ditinggalkan: tombol Keluar (`via: user_exit`) atau retensi
     * terjadwal (`via: retention`). Bintang pengerjaan ditinggalkan selalu
     * 0; jawaban dan percobaan yang sudah dicek tetap tersimpan.
     *
     * @param array<string, mixed> $payload tambahan payload event, mis. ['via' => 'retention']
     */
    public function abandonAttempt(ChallengeAttempt $attempt, array $payload = []): void
    {
        if (! $attempt->isInProgress()) {
            return;
        }

        $session = $this->sessionOf($attempt);
        $node    = $this->nodeOf($attempt);

        model(ChallengeAttemptModel::class)->update($attempt->id, [
            'status'       => 'abandoned',
            'stars'        => 0,
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        service('eventService')->record($session, 'challenge_abandoned', $payload + [
            'via'        => 'user_exit',
            'attempt_no' => $attempt->attempt_no,
        ], [
            'level_id'             => $node->level_id,
            'challenge_node_id'    => $node->id,
            'challenge_attempt_id' => $attempt->id,
        ]);
    }

    // ---------------------------------------------------------------- grader

    /**
     * Penilaian per interaction_type. Kunci selalu dari database.
     *
     * @param array<string, mixed> $answer
     */
    private function grade(ChallengeNode $node, ChallengeItem $item, array $answer, string $locale): bool
    {
        return match ($item->interaction_type) {
            'puzzle_arrange'  => $this->gradeOrder($item, $answer),
            'fill_blank_bank' => $this->gradeFillBank($item, $answer, $locale),
            'verdict_card'    => $this->gradeVerdict($node, $item, $answer),
            'find_object'     => $this->gradeFindObject($item, $answer),
            default           => false,
        };
    }

    /** Urutan harus sama persis dengan kunci. */
    private function gradeOrder(ChallengeItem $item, array $answer): bool
    {
        $key = $item->answerKey('order');

        if (! is_array($key) || ! isset($answer['order']) || ! is_array($answer['order'])) {
            return false;
        }

        $given = array_map(static fn ($v): string => (string) $v, array_values($answer['order']));
        $want  = array_map(static fn ($v): string => (string) $v, array_values($key));

        return $given === $want;
    }

    private function gradeFillBank(ChallengeItem $item, array $answer, string $locale): bool
    {
        $expected = (string) ($item->answerKey('text_' . $locale) ?? $item->answerKey('text_id') ?? '');

        if (trim($expected) === '') {
            return false;
        }

        return $this->normalize((string) ($answer['text'] ?? '')) === $this->normalize($expected);
    }

    /** Pilihan penilaian wajib salah satu verdict_options node. */
    private function gradeVerdict(ChallengeNode $node, ChallengeItem $item, array $answer): bool
    {
        $given = mb_strtolower(trim((string) ($answer['verdict'] ?? '')));

        if (! in_array($given, $node->verdictOptions(), true)) {
            return false;
        }

        return $given !== '' && $given === $item->verdict();
    }

    /** Objek yang benar adalah item target itu sendiri. */
    private function gradeFindObject(ChallengeItem $item, array $answer): bool
    {
        if ($item->isDecoy()) {
            return false;
        }

        return (int) ($answer['item_id'] ?? 0) === $item->id;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim($value));
    }

    // ------------------------------------------------------------ penulisan

    /**
     * Satu percobaan satu soal: mengisi first_answer_json sekali, selalu
     * memperbarui final_answer_json, menghitung perubahan jawaban, menambah
     * try_count, mengisi solved_on_try saat pertama kali benar, dan menulis
     * satu baris item_tries.
     *
     * @param array<string, mixed> $answer
     * @param array<string, mixed> $meta   check_no, first_input_ms, last_input_ms
     *
     * @return array{first_pass: bool, changed: bool, change_count: int, try_no: int}
     */
    private function applyAnswer(
        ChallengeAttempt $attempt,
        ChallengeItem $item,
        array $answer,
        bool $correct,
        array $meta,
    ): array {
        $responses = model(ItemResponseModel::class);
        $response  = $responses->findOne($attempt->id, $item->id);

        if ($response === null) {
            throw new \RuntimeException("Baris jawaban untuk butir {$item->id} tidak ada.");
        }

        $isFirstPass = $response->isFirstPassOpen();
        $changed     = ! $isFirstPass && $this->answerDiffers($response, $answer);
        $tryNo       = $response->try_count + 1;
        $lastInput   = self::clientMs($meta['last_input_ms'] ?? null);

        $update = [
            'final_answer_json' => $answer,
            'is_correct'        => $correct ? 1 : 0,
            'status'            => 'answered',
            'answered_at'       => date('Y-m-d H:i:s'),
            'try_count'         => $tryNo,
        ];

        if ($lastInput !== null) {
            $update['duration_ms'] = $lastInput;
        }

        if ($isFirstPass) {
            $update['first_answer_json']  = $answer;
            $update['first_pass_correct'] = $correct ? 1 : 0;
        }

        if ($correct && $response->solved_on_try === null) {
            $update['solved_on_try'] = min(255, $tryNo);
        }

        $this->recordTry($attempt, $item->id, $tryNo, $answer, $correct, $meta);

        $changeCount = $response->change_count;

        if ($changed) {
            $changeCount++;
            $update['change_count'] = $changeCount;
        }

        $responses->update($response->id, $update);

        if ($changed) {
            db_connect()->table('challenge_attempts')
                ->where('id', $attempt->id)
                ->set('answer_change_count', 'answer_change_count + 1', false)
                ->update();
        }

        return ['first_pass' => $isFirstPass, 'changed' => $changed, 'change_count' => $changeCount, 'try_no' => $tryNo];
    }

    /**
     * Satu baris item_tries. `hint_opened_before` dibaca dari hint_count
     * pengerjaan saat ini (petunjuk yang dibuka sebelum percobaan ini).
     *
     * @param array<string, mixed> $answer
     * @param array<string, mixed> $meta
     */
    private function recordTry(ChallengeAttempt $attempt, int $itemId, int $tryNo, array $answer, bool $correct, array $meta): void
    {
        $hintCount = (int) (db_connect()->table('challenge_attempts')
            ->select('hint_count')->where('id', $attempt->id)->get()->getRowArray()['hint_count'] ?? 0);

        model(ItemTryModel::class)->insert([
            'challenge_attempt_id' => $attempt->id,
            'challenge_item_id'    => $itemId,
            'try_no'               => min(255, $tryNo),
            'check_no'             => isset($meta['check_no']) ? (int) $meta['check_no'] : null,
            'answer_json'          => $answer,
            'is_correct'           => $correct ? 1 : 0,
            'hint_opened_before'   => $hintCount > 0 ? 1 : 0,
            'first_input_ms'       => self::clientMs($meta['first_input_ms'] ?? null),
            'last_input_ms'        => self::clientMs($meta['last_input_ms'] ?? null),
            'occurred_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    /** Angka milidetik dari klien: bilangan bulat ≥ 0 dan muat di INT UNSIGNED, selain itu NULL. */
    private static function clientMs(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $ms = (int) round((float) $value);

        return $ms < 0 || $ms > 4_294_967_295 ? null : $ms;
    }

    private function answerDiffers(ItemResponse $response, array $answer): bool
    {
        return json_encode($response->final_answer_json ?? [], JSON_UNESCAPED_UNICODE)
            !== json_encode($answer, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array<string, mixed> $answer
     *
     * @return array{correct: bool, first_pass: bool, wrong_click: bool, decoy: bool,
     *               already_answered: bool, feedback: ?string, correct_option_key: ?string,
     *               progress: array{answered: int, total: int}}
     */
    private function registerWrongClick(
        GameSession $session,
        ChallengeAttempt $attempt,
        ChallengeItem $target,
        array $answer,
        string $locale,
        array $meta = [],
    ): array {
        $responses  = model(ItemResponseModel::class);
        $response   = $responses->findOne($attempt->id, $target->id);
        $firstClick = $response !== null && $response->isFirstPassOpen();
        $tryNo      = 0;

        if ($response !== null) {
            $tryNo  = $response->try_count + 1;
            $update = [
                'wrong_click_count' => $response->wrong_click_count + 1,
                'try_count'         => $tryNo,
            ];

            // First-pass `cari` = klik pertama untuk tiap petunjuk: klik pertama
            // yang salah menutup first-pass petunjuk ini sebagai salah, dan
            // klik benar sesudahnya tidak dapat mengubahnya lagi. Butir tetap
            // terbuka (status/final tidak disentuh) sampai objek yang benar ditemukan.
            if ($firstClick) {
                $update['first_answer_json']  = $answer;
                $update['first_pass_correct'] = 0;
            }

            $responses->update($response->id, $update);

            $this->recordTry($attempt, $target->id, $tryNo, $answer, false, [
                'last_input_ms' => $meta['duration_ms'] ?? null,
            ]);
        }

        $clickedId = (int) ($answer['item_id'] ?? 0);
        $clicked   = $clickedId > 0 ? $this->findItemInBank($target->challenge_node_id, $clickedId) : null;
        $isDecoy   = $clicked !== null && $clicked->isDecoy();

        service('eventService')->record($session, 'wrong_target_clicked', [
            'try_no'          => $tryNo,
            'clicked_item_id' => $clickedId ?: null,
            'decoy'           => $isDecoy,
            'first_pass'      => $firstClick,
        ], [
            'challenge_node_id'    => $target->challenge_node_id,
            'challenge_attempt_id' => $attempt->id,
            'challenge_item_id'    => $target->id,
        ]);

        return [
            'correct'          => false,
            'first_pass'       => $firstClick,
            'wrong_click'      => true,
            'decoy'            => $isDecoy,
            'already_answered' => false,
            'feedback'         => $clicked?->wrongFeedback($locale),
            'try_no'           => $tryNo,
            'progress'         => $this->progressOf($attempt),
        ];
    }

    // ------------------------------------------------------------- progres

    /**
     * @return array{level_completed: bool, next_level_unlocked: ?int, shards: int, level_stars: int}
     */
    private function refreshProgress(GameSession $session, ChallengeNode $node): array
    {
        $scoring  = service('scoringService');
        $content  = service('contentRepository');
        $progress = model(SessionProgressModel::class);
        $done     = model(ChallengeAttemptModel::class)->completedForSession($session->id);

        $levelNodes    = $content->nodesForLevel($node->level_id);
        $levelNodeIds  = array_map(static fn (ChallengeNode $n): int => $n->id, $levelNodes);
        $levelFinished = $levelNodeIds !== [] && array_diff($levelNodeIds, array_keys($done)) === [];

        $completedLevels   = 0;
        $unlockedSequence  = 1;
        $nextLevelUnlocked = null;
        $level             = $content->levelById($node->level_id);
        $study             = model(ResearchStudyModel::class)->find($session->study_id);
        $isFree            = ($study['unlock_mode'] ?? 'sequential') === 'free';

        foreach ($content->levels() as $candidate) {
            $ids = array_map(static fn (ChallengeNode $n): int => $n->id, $content->nodesForLevel($candidate->id));

            if ($ids !== [] && array_diff($ids, array_keys($done)) === []) {
                $completedLevels++;
                $unlockedSequence = max($unlockedSequence, $candidate->sequence + 1);
            }
        }

        if ($isFree) {
            $unlockedSequence = max($unlockedSequence, count($content->levels()));
        }

        if ($levelFinished && $level !== null) {
            $next = $level->sequence + 1;

            foreach ($content->levels() as $candidate) {
                if ($candidate->sequence === $next) {
                    $nextLevelUnlocked = $candidate->id;
                    break;
                }
            }
        }

        $progress->update($session->id, [
            'current_level_id'        => $node->level_id,
            'current_node_id'         => $node->id,
            'completed_nodes'         => count($done),
            'completed_levels'        => $completedLevels,
            'unlocked_level_sequence' => $unlockedSequence,
            'total_stars'             => $scoring->totalStars($session->id),
        ]);

        return [
            'level_completed'     => $levelFinished,
            'next_level_unlocked' => $nextLevelUnlocked,
            'shards'              => count($done),
            'level_stars'         => $scoring->levelSummary($session->id, $node->level_id)['stars'],
        ];
    }

    // -------------------------------------------------------------- payload

    /**
     * Payload pemain: tanpa kunci jawaban, tanpa pemetaan bank kata,
     * tanpa penanda decoy, dan tanpa umpan balik salah-klik.
     * Engine `cari` tidak memakai `items`, melainkan `objects` + `clues`
     * (lihat huntPayload()).
     *
     * @param list<ChallengeItem> $items
     *
     * @return array<string, mixed>
     */
    private function buildPayload(
        GameSession $session,
        ChallengeNode $node,
        ChallengeAttempt $attempt,
        array $items,
        bool $resumed,
    ): array {
        $locale = $session->resolvedLocale();

        // Jam tantangan di layar mulai dari waktu yang dihitung server, jadi
        // tetap benar setelah muat ulang dan tidak bergantung pada jam komputer.
        $startedAt = strtotime((string) $attempt->started_at) ?: time();

        $payload = [
            'attempt_id'    => $attempt->id,
            'attempt_no'    => $attempt->attempt_no,
            'variant_group' => $attempt->variant_group,
            'max_tries'     => $attempt->maxTries(),
            'check_count'   => $attempt->check_count,
            'resumed'       => $resumed,
            'elapsed_ms'    => max(0, (time() - $startedAt) * 1000),
            'node'          => [
                'id'           => $node->id,
                'level_id'     => $node->level_id,
                'sequence'     => $node->sequence,
                'engine_type'  => $node->engine_type,
                'variant_code' => $node->variant_code,
                'title'        => $node->variantTitle($attempt->variant_group, $locale),
                'instruction'  => $node->text('instruction', $locale),
                'description'  => $node->text('description', $locale),
                'background'   => $node->background_media_id ? media_src($node->background_media_id) : null,
                'scene'        => $node->scene_media_id ? media_src($node->scene_media_id) : null,
            ],
            'items' => array_map(static fn (ChallengeItem $i): array => $i->toPlayerArray($locale), $items),
        ];

        // Id petunjuk tanpa teksnya: teks baru dikirim lewat POST /hints,
        // yang sekaligus mencatat pemakaian petunjuk untuk skor kemandirian.
        $payload['hints']       = $this->hintRefs($node, $items);
        $payload['hints_count'] = count($payload['hints']);

        if ($node->engine_type === 'cari') {
            unset($payload['items']);
            $payload += $this->huntPayload($attempt, $items, $locale);
        }

        if ($node->engine_type === 'rumpang' && $node->config('use_word_bank')) {
            $payload['word_bank'] = $this->wordBank($node, $items, $locale, (int) $attempt->id, $attempt->variant_group);
        }

        if ($node->engine_type === 'boleh') {
            $payload['verdict_options'] = $node->verdictOptions();
        }

        $passages = $this->passagesFor($items, $locale);

        if ($passages !== []) {
            $payload['passages'] = $passages;
        }

        return $payload;
    }

    /**
     * Payload engine `cari`.
     *
     * `objects` memuat SEMUA objek di adegan — target maupun jebakan — dengan
     * bentuk yang identik: token buram per attempt (`ref`, bukan id butir),
     * posisi, dan gambar. Urutannya mengikuti posisi di layar, bukan urutan
     * pemilihan (yang menaruh jebakan di akhir). Tidak ada prompt, id butir,
     * `scorable`, atau penanda jebakan.
     *
     * `clues` hanya untuk target yang dinilai, diacak dengan benih id
     * pengerjaan (Dok 1 §9.2): urutannya tetap saat halaman dimuat ulang,
     * tetapi berbeda antarpengerjaan. `item_id`-nya dikirim klien
     * sebagai butir yang sedang dijawab, tetapi tidak dapat dicocokkan dengan
     * objek mana pun karena `ref` diturunkan dari kunci server (objectRef()).
     * `audio` / `audio_id` = narasi petunjuk pada bahasa itu (clueAudio()),
     * keduanya null bila belum ada rekaman yang disetujui.
     *
     * @param list<ChallengeItem> $items
     *
     * @return array{objects: list<array<string, mixed>>, clues: list<array{item_id: int, text: string, audio: string|null, audio_id: int|null}>}
     */
    private function huntPayload(ChallengeAttempt $attempt, array $items, string $locale): array
    {
        $objects = [];
        $clues   = [];

        foreach ($items as $item) {
            if ($item->interaction_type !== 'find_object') {
                continue;
            }

            $objects[] = ['ref' => $this->objectRef($attempt->id, $item->id)] + $item->position() + [
                'media' => $item->media_asset_id ? media_src($item->media_asset_id) : null,
            ];

            if ($this->expectsAnswer($item)) {
                $clues[] = ['item_id' => $item->id, 'text' => $item->text('prompt', $locale)] + $this->clueAudio($item, $locale);
            }
        }

        usort($objects, static fn (array $a, array $b): int => [$a['y'], $a['x'], $a['ref']] <=> [$b['y'], $b['x'], $b['ref']]);

        return ['objects' => $objects, 'clues' => seeded_shuffle($clues, 'cari|' . $attempt->id)];
    }

    /**
     * Narasi satu petunjuk: URL (audio_src(): hanya `approved` dengan media
     * aktif) dan id audio_assets untuk telemetry. Id hanya dikirim bersama
     * URL-nya, jadi rekaman draft tidak terlihat di sumber halaman.
     *
     * @return array{audio: string|null, audio_id: int|null}
     */
    private function clueAudio(ChallengeItem $item, string $locale): array
    {
        $audioId = $item->promptAudioId($locale);
        $src     = audio_src($audioId);

        return ['audio' => $src, 'audio_id' => $src === null ? null : $audioId];
    }

    /**
     * Token objek `cari` untuk satu attempt: HMAC atas (attempt, butir) dengan
     * kunci enkripsi aplikasi. Klien tidak dapat membalik token menjadi id
     * butir, dan token berbeda pada setiap attempt.
     */
    private function objectRef(int $attemptId, int $itemId): string
    {
        $key = (string) config('Encryption')->key;

        if ($key === '') {
            throw new \RuntimeException('encryption.key wajib diisi: token objek engine cari diturunkan darinya.');
        }

        return substr(hash_hmac('sha256', 'cari-object|' . $attemptId . '|' . $itemId, $key), 0, 20);
    }

    /**
     * Jawaban `find_object` dari klien: `{ object: <ref> }`. Diterjemahkan ke
     * id butir yang diklik di server; `item_id` mentah kiriman klien selalu
     * diabaikan, karena klien mengetahui id butir target dari `clues`.
     *
     * @param array<string, mixed> $answer
     *
     * @return array{item_id: int}
     */
    private function resolveObjectAnswer(ChallengeAttempt $attempt, array $answer): array
    {
        $ref = (string) ($answer['object'] ?? '');

        if ($ref !== '') {
            foreach ($attempt->selectedItemIds() as $candidate) {
                if (hash_equals($this->objectRef($attempt->id, $candidate), $ref)) {
                    return ['item_id' => $candidate];
                }
            }
        }

        throw new \InvalidArgumentException('Objek tidak dikenal pada percobaan ini.');
    }

    /**
     * Bank kata: jawaban soal variasi ini + `distractor_count` pengecoh
     * pertama variasi itu, seluruhnya diacak dengan benih id pengerjaan
     * (urutan tetap saat halaman dimuat ulang). Pemetaan kata → rumpang tidak
     * pernah dikirim.
     *
     * @param list<ChallengeItem> $items
     *
     * @return list<string>
     */
    private function wordBank(ChallengeNode $node, array $items, string $locale, int $attemptId = 0, ?string $group = null): array
    {
        $words = [];

        foreach ($items as $item) {
            if ($item->interaction_type !== 'fill_blank_bank') {
                continue;
            }

            $word = (string) ($item->answerKey('text_' . $locale) ?? $item->answerKey('text_id') ?? '');

            if (trim($word) !== '') {
                $words[] = $word;
            }
        }

        $distractors = $node->distractors($locale, $group);

        $words = array_merge($words, array_slice($distractors, 0, max(0, (int) $node->config('distractor_count'))));
        $words = array_values(array_unique($words));

        return seeded_shuffle($words, 'bank|' . $attemptId);
    }

    /**
     * Teks bacaan dikirim sekali per passage, terpisah dari butir soal.
     *
     * @param list<ChallengeItem> $items
     *
     * @return array<int, array<string, mixed>>
     */
    private function passagesFor(array $items, string $locale): array
    {
        $ids = [];

        foreach ($items as $item) {
            if ($item->passage_id !== null) {
                $ids[] = $item->passage_id;
            }
        }

        $out = [];

        foreach (service('contentRepository')->passagesForIds($ids) as $id => $passage) {
            $out[$id] = $passage->toPlayerArray($locale);
        }

        return $out;
    }

    // --------------------------------------------------------------- bantu

    private function assertInProgress(ChallengeAttempt $attempt): void
    {
        if (! $attempt->isInProgress()) {
            throw new \RuntimeException("Attempt {$attempt->id} sudah tidak berstatus in_progress.");
        }
    }

    private function assertLevelUnlocked(GameSession $session, ChallengeNode $node): void
    {
        $study = model(ResearchStudyModel::class)->find($session->study_id);

        if (($study['unlock_mode'] ?? 'sequential') === 'free') {
            return;
        }

        $level = service('contentRepository')->levelById($node->level_id);

        if ($level === null) {
            throw new \RuntimeException("Level node {$node->id} tidak ditemukan.");
        }

        $progress = model(SessionProgressModel::class)->ensure($session->id);

        if ($level->sequence > (int) $progress['unlocked_level_sequence']) {
            throw new \RuntimeException('Level ini belum terbuka.');
        }
    }

    private function sessionOf(ChallengeAttempt $attempt): GameSession
    {
        $session = model(GameSessionModel::class)->find($attempt->session_id);

        if ($session === null) {
            throw new \RuntimeException("Sesi {$attempt->session_id} tidak ditemukan.");
        }

        return $session;
    }

    private function nodeOf(ChallengeAttempt $attempt): ChallengeNode
    {
        $node = service('contentRepository')->node($attempt->challenge_node_id);

        if ($node === null) {
            throw new \RuntimeException("Node {$attempt->challenge_node_id} tidak ditemukan.");
        }

        return $node;
    }

    private function itemOf(int $nodeId, int $itemId): ChallengeItem
    {
        $item = $this->findItemInBank($nodeId, $itemId);

        if ($item === null) {
            throw new \RuntimeException("Butir {$itemId} bukan milik node {$nodeId}.");
        }

        return $item;
    }

    private function findItemInBank(int $nodeId, int $itemId): ?ChallengeItem
    {
        foreach (service('contentRepository')->itemBank($nodeId) as $item) {
            if ($item->id === $itemId) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param list<ChallengeItem> $bank
     * @param list<int>           $ids
     *
     * @return list<ChallengeItem>
     */
    private function itemsByIds(array $bank, array $ids): array
    {
        $byId = [];

        foreach ($bank as $item) {
            $byId[$item->id] = $item;
        }

        $out = [];

        foreach ($ids as $id) {
            if (isset($byId[$id])) {
                $out[] = $byId[$id];
            }
        }

        return $out;
    }

    /** @param list<ChallengeItem> $items */
    private function countScorable(array $items): int
    {
        $count = 0;

        foreach ($items as $item) {
            if ($item->scorable && ! $item->isDecoy()) {
                $count++;
            }
        }

        return $count;
    }

    private function isWrongTargetClick(ChallengeItem $item, array $answer, int $itemId): bool
    {
        return isset($answer['item_id']) && (int) $answer['item_id'] !== $itemId && ! $item->isDecoy();
    }

    /**
     * Butir yang memang diminta dijawab pemain. Objek jebakan `cari` (dan
     * objek `find_object` lain yang tidak dinilai) ikut tampil di adegan dan
     * punya baris item_responses, tetapi tidak punya petunjuk sehingga tidak
     * pernah dijawab. Satu aturan ini dipakai petunjuk `cari`, progres, dan
     * syarat menutup attempt, agar ketiganya tidak pernah berbeda.
     */
    private function expectsAnswer(ChallengeItem $item): bool
    {
        if ($item->isDecoy()) {
            return false;
        }

        return $item->interaction_type !== 'find_object' || $item->scorable;
    }

    /**
     * Baris item_responses attempt yang ikut dihitung progres, keyed by item id.
     * Butir yang sudah tidak ada di bank aktif tidak dapat dijawab lagi,
     * jadi ikut dilewati.
     *
     * @return array<int, ItemResponse>
     */
    private function expectedResponses(ChallengeAttempt $attempt): array
    {
        $expected = [];

        foreach (service('contentRepository')->itemBank($attempt->challenge_node_id) as $bankItem) {
            if ($this->expectsAnswer($bankItem)) {
                $expected[$bankItem->id] = true;
            }
        }

        return array_intersect_key(model(ItemResponseModel::class)->forAttempt($attempt->id), $expected);
    }

    /**
     * Id butir yang belum dijawab atau dilewati — syarat menutup attempt.
     * Objek jebakan tidak pernah menahan attempt tetap terbuka.
     *
     * @return list<int>
     */
    public function pendingItemIds(ChallengeAttempt $attempt): array
    {
        $pending = [];

        foreach ($this->expectedResponses($attempt) as $itemId => $response) {
            if (! $response->isAnswered() && $response->status !== 'skipped') {
                $pending[] = (int) $itemId;
            }
        }

        return $pending;
    }

    /**
     * `total` tidak menghitung objek jebakan — tidak boleh membocorkan jumlah
     * jebakan maupun membuat progres tidak pernah penuh.
     *
     * @return array{answered: int, total: int}
     */
    private function progressOf(ChallengeAttempt $attempt): array
    {
        $responses = $this->expectedResponses($attempt);
        $answered  = 0;

        foreach ($responses as $response) {
            if ($response->isAnswered()) {
                $answered++;
            }
        }

        return ['answered' => $answered, 'total' => count($responses)];
    }

    /**
     * Petunjuk yang tersedia untuk attempt ini: petunjuk node, lalu petunjuk
     * per butir yang diminta dijawab. Hanya id (dan butirnya) — teks dikirim
     * useHint() saat petunjuk benar-benar dibuka. Butir jebakan tidak pernah
     * disebut, agar id-nya tidak bocor.
     *
     * @param list<ChallengeItem> $items
     *
     * @return list<array{id: int, item_id: ?int, sequence: int}>
     */
    private function hintRefs(ChallengeNode $node, array $items): array
    {
        $content = service('contentRepository');
        $refs    = [];

        foreach ($content->hintsFor($node->id) as $hint) {
            $refs[] = ['id' => (int) $hint['id'], 'item_id' => null, 'sequence' => (int) $hint['sequence']];
        }

        foreach ($items as $item) {
            if (! $this->expectsAnswer($item)) {
                continue;
            }

            foreach ($content->hintsFor($node->id, $item->id) as $hint) {
                $refs[] = ['id' => (int) $hint['id'], 'item_id' => $item->id, 'sequence' => (int) $hint['sequence']];
            }
        }

        return $refs;
    }
}
