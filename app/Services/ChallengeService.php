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
use App\Models\ResearchStudyModel;
use App\Models\SessionProgressModel;

/**
 * Inti permainan: membuka node, menerima jawaban, menutup attempt.
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
        } else {
            $study = model(ResearchStudyModel::class)->find($session->study_id);
            $seed  = $this->seedFor($session, $nodeId);

            $items = model(ChallengeItemModel::class)->pickForAttempt(
                $nodeId,
                $node->itemsPerRound(),
                (string) ($study['item_selection_mode'] ?? 'fixed'),
                $seed,
                $bank,
            );

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
                    'status'                 => 'in_progress',
                    'selected_item_ids_json' => array_map(static fn (ChallengeItem $i): int => $i->id, $items),
                    'scorable_items'         => $this->countScorable($items),
                    'started_at'             => date('Y-m-d H:i:s'),
                ], true);

                if ($attemptId === false) {
                    throw new \RuntimeException('Attempt gagal dibuat: ' . implode(' ', $attempts->errors()));
                }

                $attempt = $attempts->find((int) $attemptId);

                model(ItemResponseModel::class)->createPlaceholders($attempt->id, $items);

                service('eventService')->record($session, 'challenge_opened', [
                    'attempt_no' => $attempt->attempt_no,
                    'item_count' => count($items),
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
     * Menerima satu jawaban item (engine `pilihan` dan `cari`), atau dipanggil
     * berulang oleh submitCheck() untuk engine batch.
     *
     * @param array<string, mixed> $answer
     * @param array<string, mixed> $meta   duration_ms, reason_text
     *
     * @return array{correct: bool, first_pass: bool, wrong_click: bool, decoy: bool,
     *               already_answered: bool, feedback: ?string, correct_option_key: ?string,
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

        // allow_retry = false (engine `pilihan`): jawaban pertama sekaligus
        // jawaban final. Kiriman ulang untuk butir yang sudah dijawab tidak
        // mengubah apa pun dan dibalas dengan hasil yang tersimpan.
        if (! $node->allowsRetry()) {
            $stored = model(ItemResponseModel::class)->findOne($attempt->id, $itemId);

            if ($stored !== null && $stored->isAnswered()) {
                return [
                    'correct'            => (bool) $stored->is_correct,
                    'first_pass'         => false,
                    'wrong_click'        => false,
                    'decoy'              => false,
                    'already_answered'   => true,
                    'feedback'           => null,
                    'correct_option_key' => $this->revealedOptionKey($node, $item),
                    'progress'           => $this->progressOf($attempt),
                ];
            }
        }

        if ($item->interaction_type === 'find_object') {
            $answer = $this->resolveObjectAnswer($attempt, $answer);
        }

        $db = db_connect();
        $db->transBegin();

        try {
            // Klik objek yang salah pada engine `cari`: dicatat, tetapi tidak
            // mengubah first_pass_correct petunjuk yang sedang dicari.
            if ($item->interaction_type === 'find_object' && $this->isWrongTargetClick($item, $answer, $itemId)) {
                $result = $this->registerWrongClick($session, $attempt, $item, $answer, $locale);
                $db->transCommit();

                return $result;
            }

            $correct  = $this->grade($node, $item, $answer, $locale);
            $applied  = $this->applyAnswer($attempt, $item, $answer, $correct, $meta);
            $feedback = $correct ? null : $this->wrongFeedbackFor($item, $answer, $locale);

            service('eventService')->record(
                $session,
                $applied['changed'] ? 'answer_changed' : 'answer_submitted',
                [
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
            'correct'            => $correct,
            'first_pass'         => $applied['first_pass'],
            'wrong_click'        => false,
            'decoy'              => false,
            'already_answered'   => false,
            'feedback'           => $feedback,
            'correct_option_key' => $this->revealedOptionKey($node, $item),
            'progress'           => $this->progressOf($attempt),
        ];
    }

    /**
     * Tombol "Periksa jawaban" untuk engine batch (rumpang, boleh, puzzle).
     * Pemeriksaan pertama menetapkan first_pass_correct seluruh item.
     *
     * @param array<int, array<string, mixed>> $answers keyed by challenge_item_id
     *
     * @return array{results: array<int, bool>, correct_count: int, total: int, all_correct: bool,
     *               check_count: int, retry_count: int}
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

            $attempts->update($attempt->id, [
                'check_count' => $checkCount,
                'retry_count' => max(0, $checkCount - 1),
            ]);

            $attempt = $attempts->find($attempt->id);

            $results   = [];
            $detail    = [];
            $responses = $node->allowsRetry() ? [] : model(ItemResponseModel::class)->forAttempt($attempt->id);

            foreach ($answers as $itemId => $answer) {
                $itemId = (int) $itemId;

                if (! $attempt->hasItem($itemId) || ! is_array($answer)) {
                    continue;
                }

                $item = $this->itemOf($node->id, $itemId);

                if ($item->isDecoy()) {
                    continue;
                }

                // allow_retry = false: butir yang sudah dijawab tidak dinilai ulang
                if (isset($responses[$itemId]) && $responses[$itemId]->isAnswered()) {
                    $results[$itemId] = (bool) $responses[$itemId]->is_correct;

                    continue;
                }

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

                if (in_array($item->interaction_type, ['puzzle_arrange', 'ordering'], true)) {
                    $detail[$itemId] = $this->orderDetail($item, $answer);
                }

                $this->applyAnswer($attempt, $item, $answer, $correct, [
                    'duration_ms' => $answer['duration_ms'] ?? null,
                    'reason_text' => $answer['reason_text'] ?? null,
                ]);
            }

            $correctCount = count(array_filter($results));

            service('eventService')->record($session, 'challenge_checked', [
                'check_count'   => $checkCount,
                'correct_count' => $correctCount,
                'total'         => count($results),
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
            'all_correct'   => $results !== [] && $correctCount === count($results),
            'check_count'   => $checkCount,
            'retry_count'   => max(0, $checkCount - 1),
            'detail'        => $detail,
        ];
    }

    /**
     * Rincian pemeriksaan butir berurutan, dihitung dari kunci di server:
     * `pieces_correct` = jumlah posisi yang sudah tepat (untuk pesan "masih
     * ada N keping yang keliru"). Untuk puzzle gambar, susunan benarnya
     * memang publik (keping 1–9 dari kiri atas), jadi slot yang keliru ikut
     * dikirim (`misplaced`) agar kepingnya dapat ditandai. Urutan teks
     * (`ordering`) hanya menerima jumlahnya — posisi mana yang benar tidak
     * dibocorkan.
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

        return $item->interaction_type === 'puzzle_arrange'
            ? ['pieces_correct' => $correct, 'misplaced' => $misplaced]
            : ['pieces_correct' => $correct];
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
     * Menutup attempt: skor dihitung ScoringService, progres diperbarui,
     * level berikutnya dibuka bila seluruh node level tuntas.
     *
     * @return array{score: float, stars: int, first_pass_accuracy: float, final_accuracy: float,
     *               level_completed: bool, next_level_unlocked: ?int, shards: int}
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

            $attempts->update($attempt->id, [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'duration_ms'  => max(0, (time() - $startedAt) * 1000),
            ]);

            $attempt = $attempts->find($attempt->id);
            $scored  = service('scoringService')->scoreAttempt($attempt);

            $progress = $this->refreshProgress($session, $node);

            service('eventService')->record($session, 'challenge_completed', [
                'score'               => $scored['score'],
                'stars'               => $scored['stars'],
                'first_pass_accuracy' => $scored['first_pass_accuracy'],
                'attempt_no'          => $attempt->attempt_no,
            ], [
                'level_id'             => $node->level_id,
                'challenge_node_id'    => $node->id,
                'challenge_attempt_id' => $attempt->id,
            ]);

            if ($progress['level_completed']) {
                service('eventService')->record($session, 'level_completed', [
                    'level_score' => $progress['level_score'],
                ], ['level_id' => $node->level_id]);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        service('sessionService')->completeIfFinished($session->id);

        return [
            'score'               => $scored['score'],
            'stars'               => $scored['stars'],
            'first_pass_accuracy' => $scored['first_pass_accuracy'],
            'final_accuracy'      => $scored['final_accuracy'],
            'level_completed'     => $progress['level_completed'],
            'next_level_unlocked' => $progress['next_level_unlocked'],
            'shards'              => $progress['shards'],
        ];
    }

    /** Attempt ditinggalkan (pindah layar / tutup tab). */
    public function abandonAttempt(ChallengeAttempt $attempt): void
    {
        if (! $attempt->isInProgress()) {
            return;
        }

        $session = $this->sessionOf($attempt);
        $node    = $this->nodeOf($attempt);

        model(ChallengeAttemptModel::class)->update($attempt->id, [
            'status'       => 'abandoned',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        service('eventService')->record($session, 'challenge_abandoned', [
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
            'puzzle_arrange', 'ordering' => $this->gradeOrder($item, $answer),
            'fill_blank_bank'            => $this->gradeFillBank($item, $answer, $locale),
            'fill_blank_free'            => $this->gradeFillFree($item, $answer, $locale),
            'verdict_card',
            'verdict_reason'             => $this->gradeVerdict($node, $item, $answer),
            'single_choice',
            'source_trust'               => $this->gradeChoice($item, $answer),
            'find_object'                => $this->gradeFindObject($item, $answer),
            default                      => false,
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

    private function gradeFillFree(ChallengeItem $item, array $answer, string $locale): bool
    {
        $given = $this->normalize((string) ($answer['text'] ?? ''));

        return $given !== '' && in_array($given, $item->acceptedAnswers($locale), true);
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

    private function gradeChoice(ChallengeItem $item, array $answer): bool
    {
        $key = $item->correctOptionKey();

        return $key !== null && (string) ($answer['option_key'] ?? '') === $key;
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
     * Langkah 5–8: mengisi first_answer_json sekali, selalu memperbarui
     * final_answer_json, dan menghitung perubahan jawaban.
     *
     * @param array<string, mixed> $answer
     * @param array<string, mixed> $meta
     *
     * @return array{first_pass: bool, changed: bool, change_count: int}
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

        $update = [
            'final_answer_json' => $answer,
            'is_correct'        => $correct ? 1 : 0,
            'status'            => 'answered',
            'answered_at'       => date('Y-m-d H:i:s'),
        ];

        if (isset($meta['duration_ms'])) {
            $update['duration_ms'] = max(0, (int) $meta['duration_ms']);
        }

        if ($isFirstPass) {
            $update['first_answer_json']  = $answer;
            $update['first_pass_correct'] = $correct ? 1 : 0;
        }

        // Alasan bebas disimpan untuk ditinjau guru, tetapi tidak memengaruhi benar/salah.
        if ($item->interaction_type === 'verdict_reason') {
            $reason = trim((string) ($meta['reason_text'] ?? $answer['reason_text'] ?? ''));

            if ($reason !== '') {
                $update['reason_text']          = $reason;
                $update['reason_review_status'] = 'pending';
            }
        }

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

        return ['first_pass' => $isFirstPass, 'changed' => $changed, 'change_count' => $changeCount];
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
    ): array {
        $responses  = model(ItemResponseModel::class);
        $response   = $responses->findOne($attempt->id, $target->id);
        $firstClick = $response !== null && $response->isFirstPassOpen();

        if ($response !== null) {
            $update = ['wrong_click_count' => $response->wrong_click_count + 1];

            // First-pass `cari` = klik pertama untuk tiap petunjuk: klik pertama
            // yang salah menutup first-pass petunjuk ini sebagai salah, dan
            // klik benar sesudahnya tidak dapat mengubahnya lagi. Butir tetap
            // terbuka (status/final tidak disentuh) sampai objek yang benar ditemukan.
            if ($firstClick) {
                $update['first_answer_json']  = $answer;
                $update['first_pass_correct'] = 0;
            }

            $responses->update($response->id, $update);
        }

        $clickedId = (int) ($answer['item_id'] ?? 0);
        $clicked   = $clickedId > 0 ? $this->findItemInBank($target->challenge_node_id, $clickedId) : null;
        $isDecoy   = $clicked !== null && $clicked->isDecoy();

        service('eventService')->record($session, 'wrong_target_clicked', [
            'clicked_item_id' => $clickedId ?: null,
            'decoy'           => $isDecoy,
            'first_pass'      => $firstClick,
        ], [
            'challenge_node_id'    => $target->challenge_node_id,
            'challenge_attempt_id' => $attempt->id,
            'challenge_item_id'    => $target->id,
        ]);

        return [
            'correct'            => false,
            'first_pass'         => $firstClick,
            'wrong_click'        => true,
            'decoy'              => $isDecoy,
            'already_answered'   => false,
            'feedback'           => $clicked?->wrongFeedback($locale),
            'correct_option_key' => null,
            'progress'           => $this->progressOf($attempt),
        ];
    }

    /**
     * Kunci opsi dikirim hanya SESUDAH butir dijawab dan hanya pada node tanpa
     * pemeriksaan ulang (`allow_retry = false`), agar UI dapat menandai opsi
     * yang benar. Node yang boleh diperiksa ulang tidak pernah menerimanya.
     */
    private function revealedOptionKey(ChallengeNode $node, ChallengeItem $item): ?string
    {
        if ($node->allowsRetry() || ! in_array($item->interaction_type, ['single_choice', 'source_trust'], true)) {
            return null;
        }

        return $item->correctOptionKey();
    }

    /** Umpan balik opsi salah dikirim setelah jawaban dinilai, bukan di payload soal. */
    private function wrongFeedbackFor(ChallengeItem $item, array $answer, string $locale): ?string
    {
        $optionKey = (string) ($answer['option_key'] ?? '');

        if ($optionKey !== '') {
            foreach ($item->loadedOptions() as $option) {
                if ($option->option_key === $optionKey) {
                    $feedback = $option->text('feedback', $locale);

                    return trim($feedback) === '' ? null : $feedback;
                }
            }
        }

        return $item->wrongFeedback($locale);
    }

    // ------------------------------------------------------------- progres

    /**
     * @return array{level_completed: bool, next_level_unlocked: ?int, shards: int, level_score: float}
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
            'total_score'             => $scoring->totalScore($session->id),
            'total_stars'             => $scoring->totalStars($session->id),
        ]);

        return [
            'level_completed'     => $levelFinished,
            'next_level_unlocked' => $nextLevelUnlocked,
            'shards'              => count($done),
            'level_score'         => $scoring->levelScore($session->id, $node->level_id)['score'],
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
            'attempt_id'  => $attempt->id,
            'attempt_no'  => $attempt->attempt_no,
            'resumed'     => $resumed,
            'elapsed_ms'  => max(0, (time() - $startedAt) * 1000),
            'node'        => [
                'id'           => $node->id,
                'level_id'     => $node->level_id,
                'sequence'     => $node->sequence,
                'engine_type'  => $node->engine_type,
                'variant_code' => $node->variant_code,
                'title'        => $node->text('title', $locale),
                'instruction'  => $node->text('instruction', $locale),
                'description'  => $node->text('description', $locale),
                'allow_retry'  => $node->allowsRetry(),
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
            $payload['word_bank'] = $this->wordBank($node, $items, $locale);
        }

        if ($node->engine_type === 'boleh') {
            $payload['verdict_options'] = $node->verdictOptions();
            $payload['require_reason']  = $node->requiresReason();
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
     * `clues` hanya untuk target yang dinilai. `item_id`-nya dikirim klien
     * sebagai butir yang sedang dijawab, tetapi tidak dapat dicocokkan dengan
     * objek mana pun karena `ref` diturunkan dari kunci server (objectRef()).
     *
     * @param list<ChallengeItem> $items
     *
     * @return array{objects: list<array<string, mixed>>, clues: list<array{item_id: int, text: string}>}
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
                $clues[] = ['item_id' => $item->id, 'text' => $item->text('prompt', $locale)];
            }
        }

        usort($objects, static fn (array $a, array $b): int => [$a['y'], $a['x'], $a['ref']] <=> [$b['y'], $b['x'], $b['ref']]);

        return ['objects' => $objects, 'clues' => $clues];
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
     * Bank kata: jawaban item terpilih + pengecoh acak, seluruhnya diacak.
     * Pemetaan kata → rumpang tidak pernah dikirim.
     *
     * @param list<ChallengeItem> $items
     *
     * @return list<string>
     */
    private function wordBank(ChallengeNode $node, array $items, string $locale): array
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

        $distractors = $node->distractors($locale);
        shuffle($distractors);

        $words = array_merge($words, array_slice($distractors, 0, max(0, (int) $node->config('distractor_count'))));
        $words = array_values(array_unique($words));

        shuffle($words);

        return $words;
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

    /** Benih pemilihan item: peserta yang sama mendapat butir sama pada pretest & posttest. */
    private function seedFor(GameSession $session, int $nodeId): string
    {
        $participant = model(\App\Models\ParticipantModel::class)->find($session->participant_id);

        return ($participant?->participant_code ?? (string) $session->participant_id) . '|' . $nodeId;
    }
}
