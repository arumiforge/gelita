<?php

namespace App\Services;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\ItemResponse;
use App\Exceptions\ChallengeRuleException;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeItemModel;
use App\Models\GameSessionModel;
use App\Models\ItemResponseModel;
use App\Models\ItemTryModel;
use App\Models\ResearchStudyModel;
use App\Models\SessionProgressModel;
use CodeIgniter\Model;

/**
 * Inti permainan: membuka node, menerima jawaban, menutup pengerjaan.
 *
 * Setiap pengerjaan memakai satu variasi soal yang dipilih acak (Dok 2 §3).
 * Setiap percobaan soal ditulis apa adanya ke `item_tries` (Dok 1 §12.2).
 *
 * Aturan 3 kesempatan (Dok 2 §5) ditegakkan di sini:
 * - Susun Gambar, Isi Rumpang, Benar atau Salah: satu "Cek jawabanku" = satu
 *   percobaan untuk semua soal yang belum benar; soal yang sudah benar
 *   terkunci. Semua benar → selesai (`solved`); percobaan ke-`max_tries`
 *   masih ada yang salah → selesai (`tries_exhausted`), jawaban terakhir
 *   disimpan apa adanya.
 * - Cari Objek: satu ketukan = satu percobaan untuk petunjuk aktif; ketukan
 *   salah ke-`max_tries` menutup petunjuk itu sebagai salah (`clue_failed`).
 *   Semua petunjuk tertutup → selesai.
 * Penutupan berjalan dalam transaction yang sama dengan percobaannya,
 * sehingga hasil tidak bergantung pada klien memanggil `/complete`.
 *
 * Seluruh penilaian benar/salah dilakukan di sini memakai kunci dari database.
 * Field `correct` yang dikirim browser selalu diabaikan, dan `answer_key_json`
 * tidak pernah ikut dalam payload pemain.
 */
class ChallengeService
{
    /** Paling banyak berapa kali bank kata diacak ulang sampai lolos aturan §4. */
    private const BANK_SHUFFLE_TRIES = 50;

    /**
     * Membuka node: membuat attempt, memilih item, menyiapkan payload pemain.
     * Attempt `in_progress` yang sudah ada dipakai ulang (resume). Attempt
     * yang ternyata sudah tuntas ditutup di sini: `payload` null dan
     * `redirect` menunjuk layar hasilnya.
     *
     * @return array{attempt: ChallengeAttempt, payload: ?array<string, mixed>, redirect?: string}
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
            // diperbarui): tutup percobaan lama dan buka yang baru. Benda
            // jebakan saja tidak cukup untuk dikerjakan.
            if (array_filter($items, $this->expectsAnswer(...)) === []) {
                $this->abandonAttempt($attempt, ['via' => 'content_changed']);
                $attempt = null;
                $resumed = false;
            } elseif (($reason = $this->closingReason($attempt, $node)) !== null) {
                // Sudah tuntas tetapi belum ditutup, mis. soal/petunjuk terakhir
                // yang belum dijawab dinonaktifkan admin: ditutup sekarang, dan
                // pemain diarahkan ke layar hasil, bukan ke tantangan tanpa
                // soal terbuka (Cari Objek akan menolak setiap ketukan).
                $closed = $this->finishOpen($attempt, $reason, $session, $node);

                if ($closed->isCompleted()) {
                    return ['attempt' => $closed, 'payload' => null, 'redirect' => site_url('selesai/' . $closed->id)];
                }

                // Baru saja ditinggalkan dari tab lain: buka pengerjaan baru
                $attempt = null;
                $resumed = false;
            }
        }

        if (! $resumed) {
            // Satu variasi acak; variasi yang sudah pernah didapat siswa ini
            // (di semua sesinya) dihindari selama masih ada yang lain.
            $served = $attempts->variantsServed($session->participant_id, $nodeId);
            $picked = model(ChallengeItemModel::class)->pickVariant($nodeId, $served, $bank);
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
     * Menerima satu ketukan benda pada Cari Objek (Dok 2 §5.2). Setiap
     * ketukan adalah satu percobaan untuk petunjuk yang sedang dicari (satu
     * baris `item_tries`). Ketukan salah ke-`max_tries` menutup petunjuk itu
     * sebagai salah; bila semua petunjuk sudah tertutup, pengerjaan ditutup
     * dalam transaction yang sama.
     *
     * @param array<string, mixed> $answer `{object: <ref>}` (token objek, bukan id butir)
     * @param array<string, mixed> $meta   duration_ms (sejak petunjuk tampil)
     *
     * @return array{correct: bool, first_pass: bool, wrong_click: bool, decoy: bool, feedback: ?string,
     *               try_no: int, tries_left: int, clue_closed: bool, progress: array{answered: int, total: int},
     *               completed: bool, completion_reason: ?string, redirect: ?string, stars: ?int, shards: ?int}
     *
     * @throws ChallengeRuleException CLUE_CLOSED bila petunjuk sudah ditemukan/dilewati
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

        if ($item->interaction_type !== 'find_object' || ! $this->expectsAnswer($item)) {
            throw new \InvalidArgumentException("Butir {$itemId} bukan petunjuk Cari Objek.");
        }

        $answer   = $this->resolveObjectAnswer($attempt, $answer);
        $response = model(ItemResponseModel::class)->findOne($attempt->id, $itemId);

        if ($response === null) {
            throw new \RuntimeException("Baris jawaban untuk butir {$itemId} tidak ada.");
        }

        if ($response->isAnswered()) {
            throw ChallengeRuleException::clueClosed();
        }

        $completion = null;
        $db         = db_connect();
        $db->transBegin();

        try {
            if ((int) $answer['item_id'] !== $itemId) {
                // Ketukan salah: dicatat sebagai percobaan petunjuk yang sedang dicari
                $result = $this->registerWrongClick($session, $attempt, $item, $answer, $locale, $meta);
            } else {
                $correct = $this->grade($node, $item, $answer, $locale);
                $applied = $this->applyAnswer($attempt, $item, $answer, $correct, [
                    'last_input_ms' => $meta['duration_ms'] ?? null,
                ]);

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

                $result = [
                    'correct'     => $correct,
                    'first_pass'  => $applied['first_pass'],
                    'wrong_click' => false,
                    'decoy'       => false,
                    'feedback'    => null,
                    'try_no'      => $applied['try_no'],
                    'tries_left'  => max(0, $attempt->maxTries() - $applied['try_no']),
                    'clue_closed' => true,
                ];
            }

            if ($this->pendingItemIds($attempt) === []) {
                $completion = $this->closeAttempt($attempt, $this->allCorrect($attempt) ? 'solved' : 'tries_exhausted', $session, $node);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            throw $this->rollback($e, $attempt->id, $itemId);
        }

        if ($completion !== null) {
            service('sessionService')->completeIfFinished($session->id);
        }

        return $result + ['progress' => $this->progressOf($attempt)] + $this->completionFields($attempt, $completion);
    }

    /**
     * Tombol "Cek jawabanku" untuk engine batch (puzzle, rumpang, boleh) —
     * satu percobaan untuk semua soal yang belum benar (Dok 2 §5.2):
     *
     * 1. kesempatan habis (`check_count >= max_tries`) → TRIES_EXHAUSTED, tanpa perubahan;
     * 2. setiap soal yang belum benar wajib diisi → INCOMPLETE bila ada yang kosong;
     * 3. `check_count++`; setiap soal yang belum benar dinilai dan menulis satu
     *    baris `item_tries`; soal yang sudah benar terkunci (tidak dinilai ulang);
     * 4. semua benar → ditutup `solved`; percobaan terakhir masih ada yang salah
     *    → event `challenge_tries_exhausted`, ditutup `tries_exhausted`.
     *
     * $answers[itemId] boleh memuat `first_input_ms` / `last_input_ms` (ms
     * sejak tantangan dibuka, dari klien); keduanya tidak ikut answer_json.
     *
     * $checkNo (opsional) = nomor cek yang dimaksud klien (`tries_used + 1`
     * di layarnya). Bila tidak sama dengan cek berikutnya di server, cek ini
     * berasal dari tampilan usang (tab lain, atau kiriman ulang sesudah
     * respons pertama hilang) → ATTEMPT_CHANGED tanpa perubahan. Dua tab di
     * peramban yang sama tidak pernah benar-benar bersamaan (kunci session
     * PHP), jadi tanpa nomor ini cek dari tab lama dihitung sebagai cek baru.
     *
     * @param array<int, array<string, mixed>> $answers keyed by challenge_item_id
     *
     * @return array{results: array<int, bool>, correct_count: int, total: int, all_correct: bool,
     *               check_count: int, try_no: int, tries_left: int, exhausted: bool,
     *               detail: array<int, array<string, mixed>>, completed: bool, completion_reason: ?string,
     *               redirect: ?string, stars: ?int, shards: ?int}
     *
     * @throws ChallengeRuleException TRIES_EXHAUSTED (409), ATTEMPT_CHANGED (409) atau INCOMPLETE (422)
     */
    public function submitCheck(ChallengeAttempt $attempt, array $answers, ?int $checkNo = null): array
    {
        $max = $attempt->maxTries();

        // Diperiksa sebelum status: percobaan ke-4 pada pengerjaan yang sudah
        // ditutup otomatis tetap dijawab TRIES_EXHAUSTED.
        if ($attempt->check_count >= $max) {
            throw ChallengeRuleException::triesExhausted(
                $attempt->status === 'completed' ? site_url('selesai/' . $attempt->id) : null,
            );
        }

        $this->assertInProgress($attempt);

        if ($checkNo !== null && $checkNo !== $attempt->check_count + 1) {
            throw ChallengeRuleException::attemptChanged();
        }

        $session  = $this->sessionOf($attempt);
        $node     = $this->nodeOf($attempt);
        $locale   = $session->resolvedLocale();
        $attempts = model(ChallengeAttemptModel::class);

        if ($node->engine_type === 'cari') {
            throw new \InvalidArgumentException('Cari Objek dijawab per ketukan benda, bukan lewat Cek jawabanku.');
        }

        $responses = model(ItemResponseModel::class)->forAttempt($attempt->id);
        $expected  = $this->expectedItems($attempt);
        $locked    = [];
        $missing   = [];

        foreach ($expected as $itemId => $item) {
            $response = $responses[$itemId] ?? null;

            if ($response !== null && $response->isAnswered() && $response->is_correct) {
                $locked[$itemId] = true;
            } elseif (! $this->isFilled($node, $item, $answers[$itemId] ?? null)) {
                $missing[] = $itemId;
            }
        }

        if ($missing !== []) {
            throw ChallengeRuleException::incomplete($missing);
        }

        $completion = null;
        $checkCount = $attempt->check_count + 1;
        $db         = db_connect();
        $db->transBegin();

        try {
            // Cek ini hanya berlaku bila check_count belum diubah permintaan
            // lain (tab kedua, atau kiriman ulang saat jaringan lambat). Yang
            // kalah tidak menulis apa pun: tanpa try_no ke-4, tanpa penutupan
            // dan event ganda.
            $this->updateOrStale($attempts, $attempt->id, ['check_count' => $attempt->check_count, 'status' => 'in_progress'], ['check_count' => $checkCount]);

            $attempt = $attempts->find($attempt->id);
            $results = [];
            $detail  = [];
            $tryNo   = 0;

            foreach ($expected as $itemId => $item) {
                // Soal yang sudah benar terkunci: tidak dinilai ulang, tanpa percobaan baru
                if (isset($locked[$itemId])) {
                    $results[$itemId] = true;

                    continue;
                }

                $answer = (array) $answers[$itemId];
                $timing = [
                    'first_input_ms' => $answer['first_input_ms'] ?? null,
                    'last_input_ms'  => $answer['last_input_ms'] ?? null,
                    'check_no'       => $checkCount,
                ];
                unset($answer['first_input_ms'], $answer['last_input_ms'], $answer['duration_ms']);

                $correct          = $this->grade($node, $item, $answer, $locale);
                $results[$itemId] = $correct;

                if ($item->interaction_type === 'puzzle_arrange') {
                    $detail[$itemId] = $this->orderDetail($item, $answer);
                }

                $applied = $this->applyAnswer($attempt, $item, $answer, $correct, $timing);
                $tryNo   = max($tryNo, $applied['try_no']);
            }

            $correctCount = count(array_filter($results));
            $total        = count($results);
            $allCorrect   = $total > 0 && $correctCount === $total;
            $triesLeft    = max(0, $max - $checkCount);
            $exhausted    = ! $allCorrect && $triesLeft === 0;
            $refs         = [
                'level_id'             => $node->level_id,
                'challenge_node_id'    => $node->id,
                'challenge_attempt_id' => $attempt->id,
            ];

            service('eventService')->record($session, 'challenge_checked', [
                'check_no'      => $checkCount,
                'try_no'        => $tryNo,
                'correct_count' => $correctCount,
                'total'         => $total,
                'exhausted'     => $exhausted,
            ], $refs);

            if ($allCorrect) {
                $completion = $this->closeAttempt($attempt, 'solved', $session, $node);
            } elseif ($exhausted) {
                service('eventService')->record($session, 'challenge_tries_exhausted', [
                    'check_count'   => $checkCount,
                    'correct_count' => $correctCount,
                    'total'         => $total,
                ], $refs);

                $completion = $this->closeAttempt($attempt, 'tries_exhausted', $session, $node);
            }

            $db->transCommit();
        } catch (\Throwable $e) {
            throw $this->rollback($e, $attempt->id);
        }

        if ($completion !== null) {
            service('sessionService')->completeIfFinished($session->id);
        }

        return [
            'results'       => $results,
            'correct_count' => $correctCount,
            'total'         => $total,
            'all_correct'   => $allCorrect,
            'check_count'   => $checkCount,
            'try_no'        => $tryNo,
            'tries_left'    => $triesLeft,
            'exhausted'     => $exhausted,
            'detail'        => $detail,
        ] + $this->completionFields($attempt, $completion);
    }

    /**
     * Rincian cek Susun Gambar, dihitung dari kunci di server:
     * `pieces_correct` = jumlah posisi yang sudah tepat (untuk pesan "masih
     * ada N keping yang keliru"). Susunan benarnya memang publik (keping 1–N
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
     * Menutup pengerjaan yang masih berjalan, untuk jalur `/complete`
     * (cadangan klien): `solved` bila semua soal benar; `tries_exhausted` bila
     * kesempatannya memang sudah habis (batch: `check_count >= max_tries`;
     * Cari Objek: semua petunjuk tertutup). Selain itu ditolak — tidak ada
     * lagi "Selesaikan dengan jawaban ini" (Dok 2 §5.1).
     *
     * @return array{stars: int, first_pass_correct: int, final_correct: int, scorable_items: int,
     *               completion_reason: string, level_completed: bool, next_level_unlocked: ?int, shards: int}
     *
     * @throws ChallengeRuleException TRIES_REMAINING bila masih ada kesempatan
     */
    public function completeAttempt(ChallengeAttempt $attempt): array
    {
        $this->assertInProgress($attempt);

        $session = $this->sessionOf($attempt);
        $node    = $this->nodeOf($attempt);
        $reason  = $this->closingReason($attempt, $node);

        if ($reason === null) {
            throw ChallengeRuleException::triesRemaining();
        }

        $db = db_connect();
        $db->transBegin();

        try {
            $result = $this->closeAttempt($attempt, $reason, $session, $node);

            $db->transCommit();
        } catch (\Throwable $e) {
            throw $this->rollback($e, $attempt->id);
        }

        service('sessionService')->completeIfFinished($session->id);

        return $result;
    }

    /**
     * Menutup pengerjaan yang sudah tuntas saat dibuka lagi (openNode()).
     * Bila permintaan lain lebih dulu menutupnya, hasilnya dipakai apa adanya.
     */
    private function finishOpen(ChallengeAttempt $attempt, string $reason, GameSession $session, ChallengeNode $node): ChallengeAttempt
    {
        $db = db_connect();
        $db->transBegin();

        try {
            $this->closeAttempt($attempt, $reason, $session, $node);

            $db->transCommit();
            service('sessionService')->completeIfFinished($session->id);
        } catch (ChallengeRuleException $e) {
            $db->transRollback();

            if ($e->errorCode !== 'ATTEMPT_CHANGED') {
                throw $e;
            }
        } catch (\Throwable $e) {
            $db->transRollback();

            throw $e;
        }

        return model(ChallengeAttemptModel::class)->find($attempt->id);
    }

    /**
     * Alasan penutupan yang sah saat ini, atau null bila pengerjaan belum
     * boleh ditutup.
     */
    public function closingReason(ChallengeAttempt $attempt, ?ChallengeNode $node = null): ?string
    {
        $node ??= $this->nodeOf($attempt);

        if ($this->allCorrect($attempt)) {
            return 'solved';
        }

        $exhausted = $node->engine_type === 'cari'
            ? $this->pendingItemIds($attempt) === []
            : $attempt->check_count >= $attempt->maxTries();

        return $exhausted ? 'tries_exhausted' : null;
    }

    /**
     * Menutup pengerjaan di dalam transaction pemanggil: bintang dihitung
     * ScoringService (aturan `bintang-1`), progres diperbarui, wilayah
     * berikutnya dibuka bila seluruh node tuntas. Pemanggil menjalankan
     * SessionService::completeIfFinished() sesudah commit.
     *
     * @return array{stars: int, first_pass_correct: int, final_correct: int, scorable_items: int,
     *               completion_reason: string, level_completed: bool, next_level_unlocked: ?int, shards: int}
     */
    private function closeAttempt(ChallengeAttempt $attempt, string $reason, GameSession $session, ChallengeNode $node): array
    {
        $attempts  = model(ChallengeAttemptModel::class);
        $startedAt = strtotime((string) $attempt->started_at) ?: time();

        // Hanya sekali: penutupan kedua (permintaan yang balapan) tidak
        // menghitung ulang bintang dan tidak mencatat event ganda.
        $this->updateOrStale($attempts, $attempt->id, ['status' => 'in_progress'], [
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
     * Kolom penutupan di respons /check dan /responses (Dok 2 §5.2).
     *
     * @param array<string, mixed>|null $completion hasil closeAttempt()
     *
     * @return array{completed: bool, completion_reason: ?string, redirect: ?string, stars: ?int, shards: ?int}
     */
    private function completionFields(ChallengeAttempt $attempt, ?array $completion): array
    {
        return [
            'completed'         => $completion !== null,
            'completion_reason' => $completion['completion_reason'] ?? null,
            'redirect'          => $completion === null ? null : site_url('selesai/' . $attempt->id),
            'stars'             => $completion['stars'] ?? null,
            'shards'            => $completion['shards'] ?? null,
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

        // Pengerjaan yang baru saja ditutup permintaan lain tidak diubah menjadi ditinggalkan
        if (! $this->updateIf(model(ChallengeAttemptModel::class), $attempt->id, ['status' => 'in_progress'], [
            'status'       => 'abandoned',
            'stars'        => 0,
            'completed_at' => date('Y-m-d H:i:s'),
        ])) {
            return;
        }

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

        $changeCount = $response->change_count;

        if ($changed) {
            $changeCount++;
            $update['change_count'] = $changeCount;
        }

        // try_count sebagai versi baris: dua ketukan bersamaan tidak sama-sama menulis try_no yang sama
        $this->updateOrStale($responses, $response->id, ['try_count' => $response->try_count], $update);
        $this->recordTry($attempt, $item->id, $tryNo, $answer, $correct, $meta);

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
     * Ketukan salah pada Cari Objek: percobaan untuk petunjuk yang sedang
     * dicari. Ketukan salah pertama menutup first-pass petunjuk ini sebagai
     * salah; ketukan salah ke-`max_tries` menutup petunjuknya sebagai salah
     * ("dilewati": status answered, is_correct 0, final_answer_json = ketukan
     * terakhir) dan menulis event `clue_failed`.
     *
     * @param array<string, mixed> $answer
     *
     * @return array{correct: bool, first_pass: bool, wrong_click: bool, decoy: bool, feedback: ?string,
     *               try_no: int, tries_left: int, clue_closed: bool}
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
        $closed     = false;

        if ($response !== null) {
            $tryNo     = $response->try_count + 1;
            $closed    = $tryNo >= $attempt->maxTries();
            $lastInput = self::clientMs($meta['duration_ms'] ?? null);
            $update    = [
                'wrong_click_count' => $response->wrong_click_count + 1,
                'try_count'         => $tryNo,
            ];

            // First-pass `cari` = klik pertama untuk tiap petunjuk: klik pertama
            // yang salah menutup first-pass petunjuk ini sebagai salah, dan
            // klik benar sesudahnya tidak dapat mengubahnya lagi.
            if ($firstClick) {
                $update['first_answer_json']  = $answer;
                $update['first_pass_correct'] = 0;
            }

            // Kesempatan petunjuk ini habis: ditutup salah, ketukan terakhir disimpan apa adanya
            if ($closed) {
                $update += [
                    'final_answer_json' => $answer,
                    'is_correct'        => 0,
                    'status'            => 'answered',
                    'answered_at'       => date('Y-m-d H:i:s'),
                ] + ($lastInput !== null ? ['duration_ms' => $lastInput] : []);
            }

            $this->updateOrStale($responses, $response->id, ['try_count' => $response->try_count], $update);

            $this->recordTry($attempt, $target->id, $tryNo, $answer, false, [
                'last_input_ms' => $meta['duration_ms'] ?? null,
            ]);
        }

        $clickedId = (int) ($answer['item_id'] ?? 0);
        $clicked   = $clickedId > 0 ? $this->findItemInBank($target->challenge_node_id, $clickedId) : null;
        $isDecoy   = $clicked !== null && $clicked->isDecoy();
        $refs      = [
            'level_id'             => $this->nodeOf($attempt)->level_id,
            'challenge_node_id'    => $target->challenge_node_id,
            'challenge_attempt_id' => $attempt->id,
            'challenge_item_id'    => $target->id,
        ];

        service('eventService')->record($session, 'wrong_target_clicked', [
            'try_no'          => $tryNo,
            'clicked_item_id' => $clickedId ?: null,
            'decoy'           => $isDecoy,
            'first_pass'      => $firstClick,
        ], $refs);

        if ($closed) {
            service('eventService')->record($session, 'clue_failed', ['try_count' => $tryNo], $refs);
        }

        return [
            'correct'     => false,
            'first_pass'  => $firstClick,
            'wrong_click' => true,
            'decoy'       => $isDecoy,
            'feedback'    => $clicked?->wrongFeedback($locale),
            'try_no'      => $tryNo,
            'tries_left'  => max(0, $attempt->maxTries() - $tryNo),
            'clue_closed' => $closed,
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
     * `variant` = variasi pengerjaan ini (Dok 2 §3.3); `max_tries` /
     * `tries_used` = kesempatan engine batch. `items[].state` memulihkan
     * tampilan saat dimuat ulang: soal yang sudah benar membawa `locked` +
     * jawaban siswa sendiri; soal lain hanya jumlah percobaannya — jawaban
     * yang salah tidak pernah dikembalikan.
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
        $locale    = $session->resolvedLocale();
        $responses = model(ItemResponseModel::class)->forAttempt($attempt->id);
        $passages  = $this->passagesFor($items, $locale);

        // Jam tantangan di layar mulai dari waktu yang dihitung server, jadi
        // tetap benar setelah muat ulang dan tidak bergantung pada jam komputer.
        $startedAt = strtotime((string) $attempt->started_at) ?: time();

        $payload = [
            'attempt_id'    => $attempt->id,
            'attempt_no'    => $attempt->attempt_no,
            'variant_group' => $attempt->variant_group,
            'variant'       => [
                'group' => $attempt->variant_group,
                'title' => $this->variantTitle($node, $attempt->variant_group, $passages, $locale),
            ],
            'max_tries'     => $attempt->maxTries(),
            'tries_used'    => $attempt->check_count,
            'check_count'   => $attempt->check_count,
            'resumed'       => $resumed,
            'elapsed_ms'    => max(0, (time() - $startedAt) * 1000),
            'node'          => [
                'id'           => $node->id,
                'level_id'     => $node->level_id,
                'sequence'     => $node->sequence,
                'engine_type'  => $node->engine_type,
                'variant_code' => $node->variant_code,
                'title'        => $node->text('title', $locale),
                'instruction'  => $node->text('instruction', $locale),
                'description'  => $node->text('description', $locale),
                'background'   => $node->background_media_id ? media_src($node->background_media_id) : null,
                'scene'        => $node->scene_media_id ? media_src($node->scene_media_id) : null,
            ],
            'items' => array_map(
                fn (ChallengeItem $i): array => $i->toPlayerArray($locale) + ['state' => $this->itemState($responses[$i->id] ?? null)],
                $items,
            ),
        ];

        // Id petunjuk tanpa teksnya: teks baru dikirim lewat POST /hints,
        // yang sekaligus mencatat pemakaian petunjuk sebagai data bantuan.
        $payload['hints']       = $this->hintRefs($node, $items);
        $payload['hints_count'] = count($payload['hints']);

        if ($node->engine_type === 'cari') {
            unset($payload['items']);
            $payload += $this->huntPayload($attempt, $items, $locale, $responses);
        }

        if ($node->engine_type === 'rumpang' && $node->config('use_word_bank')) {
            $payload['word_bank'] = $this->wordBank($node, $items, $locale, (int) $attempt->id, $attempt->variant_group);
        }

        if ($node->engine_type === 'boleh') {
            $payload['verdict_options'] = $node->verdictOptions();
        }

        if ($passages !== []) {
            $payload['passages'] = $passages;
        }

        return $payload;
    }

    /**
     * Status satu soal untuk payload (Dok 2 §3.3).
     *
     * @return array{locked: true, answer: array<string, mixed>, tries: int}|array{tries: int}
     */
    private function itemState(?ItemResponse $response): array
    {
        if ($response === null) {
            return ['tries' => 0];
        }

        if ($response->isAnswered() && $response->is_correct) {
            return ['locked' => true, 'answer' => (array) ($response->final_answer_json ?? []), 'tries' => $response->try_count];
        }

        return ['tries' => $response->try_count];
    }

    /**
     * Judul variasi: config_json.variants (Susun Gambar, Isi Rumpang, Cari
     * Objek), atau judul bacaannya (Benar atau Salah); terakhir judul node.
     *
     * @param array<int, array<string, mixed>> $passages
     */
    private function variantTitle(ChallengeNode $node, ?string $group, array $passages, string $locale): string
    {
        $entry = $group === null ? [] : ($node->variants()[$group] ?? []);
        $title = trim((string) ($entry['title_' . $locale] ?? ''));

        if ($title === '') {
            $title = trim((string) ($entry['title_id'] ?? ''));
        }

        if ($title === '' && $node->engine_type === 'boleh' && $passages !== []) {
            $title = trim((string) (reset($passages)['title'] ?? ''));
        }

        return $title !== '' ? $title : $node->text('title', $locale);
    }

    /**
     * Payload engine `cari`.
     *
     * `objects` memuat SEMUA objek di adegan — target maupun jebakan — dengan
     * bentuk yang identik: token buram per attempt (`ref`, bukan id butir),
     * posisi, gambar, dan `found` (benda yang sudah ditemukan siswa sendiri).
     * Urutannya mengikuti posisi di layar, bukan urutan pemilihan (yang
     * menaruh jebakan di akhir). Tidak ada prompt, id butir, `scorable`, atau
     * penanda jebakan.
     *
     * `clues` hanya untuk target yang dinilai, diacak dengan benih id
     * pengerjaan (Dok 2 §4): urutannya tetap saat halaman dimuat ulang,
     * tetapi berbeda antarpengerjaan. `item_id`-nya dikirim klien
     * sebagai butir yang sedang dijawab, tetapi tidak dapat dicocokkan dengan
     * objek mana pun karena `ref` diturunkan dari kunci server (objectRef()).
     * `audio` / `audio_id` = narasi petunjuk pada bahasa itu (clueAudio()),
     * keduanya null bila belum ada rekaman yang disetujui. `state` =
     * {found, closed, tries} untuk memulihkan tampilan saat dimuat ulang.
     *
     * @param list<ChallengeItem>       $items
     * @param array<int, ItemResponse> $responses
     *
     * @return array{objects: list<array<string, mixed>>, clues: list<array<string, mixed>>}
     */
    private function huntPayload(ChallengeAttempt $attempt, array $items, string $locale, array $responses = []): array
    {
        $objects = [];
        $clues   = [];

        foreach ($items as $item) {
            if ($item->interaction_type !== 'find_object') {
                continue;
            }

            $response = $responses[$item->id] ?? null;
            $found    = $response !== null && $response->isAnswered() && (bool) $response->is_correct && $this->expectsAnswer($item);

            $objects[] = ['ref' => $this->objectRef($attempt->id, $item->id)] + $item->position() + [
                'media' => $item->media_asset_id ? media_src($item->media_asset_id) : null,
                'found' => $found,
            ];

            if ($this->expectsAnswer($item)) {
                $clues[] = ['item_id' => $item->id, 'text' => $item->text('prompt', $locale)] + $this->clueAudio($item, $locale) + [
                    'state' => [
                        'found'  => $found,
                        'closed' => $response !== null && $response->isAnswered(),
                        'tries'  => $response?->try_count ?? 0,
                    ],
                ];
            }
        }

        usort($objects, static fn (array $a, array $b): int => [$a['y'], $a['x'], $a['ref']] <=> [$b['y'], $b['x'], $b['ref']]);

        return ['objects' => $objects, 'clues' => seeded_shuffle($clues, 'clue|' . $attempt->id)];
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
     * Bank kata Isi Rumpang (Dok 2 §4): jawaban soal variasi ini (A, urut
     * rumpang) + `distractor_count` pengecoh pertama variasi itu, diacak
     * dengan benih `bank|{pengerjaan}|{k}` untuk k = 0, 1, 2, … sampai lolos:
     *
     * - urutan jawaban di dalam bank tidak sama dengan A;
     * - tanpa pengecoh (Temanggung), tidak ada kata di posisi rumpangnya
     *   sendiri (derangement), sehingga bank kata selalu tampak teracak.
     *
     * Paling banyak BANK_SHUFFLE_TRIES kali; urutannya tetap saat halaman
     * dimuat ulang. Pemetaan kata → rumpang tidak pernah dikirim.
     *
     * @param list<ChallengeItem> $items
     *
     * @return list<string>
     */
    private function wordBank(ChallengeNode $node, array $items, string $locale, int $attemptId = 0, ?string $group = null): array
    {
        $answers = [];

        foreach ($items as $item) {
            if ($item->interaction_type !== 'fill_blank_bank') {
                continue;
            }

            $word = (string) ($item->answerKey('text_' . $locale) ?? $item->answerKey('text_id') ?? '');

            if (trim($word) !== '') {
                $answers[] = $word;
            }
        }

        $distractors = array_slice($node->distractors($locale, $group), 0, max(0, (int) $node->config('distractor_count')));
        $words       = array_values(array_unique(array_merge($answers, $distractors)));

        if (count($words) < 2) {
            return $words;
        }

        $bank = $words;

        for ($k = 0; $k < self::BANK_SHUFFLE_TRIES; $k++) {
            $bank = seeded_shuffle($words, 'bank|' . $attemptId . '|' . $k);

            if (self::bankShuffled($bank, $answers, $distractors === [])) {
                return $bank;
            }
        }

        return $bank;
    }

    /**
     * Aturan bank kata teracak (Dok 2 §4).
     *
     * @param list<string> $bank    hasil acak
     * @param list<string> $answers jawaban urut rumpang
     * @param bool         $strict  tanpa pengecoh: tidak ada kata di posisi rumpangnya sendiri
     */
    public static function bankShuffled(array $bank, array $answers, bool $strict): bool
    {
        if ($strict) {
            foreach ($answers as $position => $word) {
                if (($bank[$position] ?? null) === $word) {
                    return false;
                }
            }

            return true;
        }

        return array_values(array_filter($bank, static fn (string $word): bool => in_array($word, $answers, true))) !== $answers;
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

    /**
     * UPDATE bersyarat (penguncian optimistis): baris $id hanya diubah bila
     * kolom $expected masih bernilai sama. false = permintaan lain lebih dulu
     * mengubahnya. Di MySQL UPDATE menunggu kunci baris transaction lain lalu
     * membaca nilai terbarunya; di SQLite (uji) penulis memang berurutan.
     * $data selalu mengubah salah satu kolom $expected, jadi baris yang cocok
     * selalu terhitung berubah.
     *
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $data
     */
    private function updateIf(Model $model, int $id, array $expected, array $data): bool
    {
        $model->where($expected)->update($id, $data);

        return db_connect()->affectedRows() === 1;
    }

    /**
     * updateIf() di dalam transaction: kalah balapan → ATTEMPT_CHANGED, yang
     * oleh rollback() diganti galat sesuai keadaan terbaru.
     *
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $data
     */
    private function updateOrStale(Model $model, int $id, array $expected, array $data): void
    {
        if (! $this->updateIf($model, $id, $expected, $data)) {
            throw ChallengeRuleException::attemptChanged();
        }
    }

    /**
     * Membatalkan transaction yang gagal. Permintaan yang kalah balapan
     * dijawab menurut keadaan sesudah rollback (dibaca di luar transaction
     * agar tidak memakai snapshot lama): pengerjaan sudah ditutup → menuju
     * layar hasil; petunjuk Cari Objek sudah tertutup → CLUE_CLOSED;
     * kesempatan batch habis → TRIES_EXHAUSTED; selain itu ATTEMPT_CHANGED
     * (klien memuat ulang).
     */
    private function rollback(\Throwable $e, int $attemptId, ?int $itemId = null): \Throwable
    {
        db_connect()->transRollback();

        if (! $e instanceof ChallengeRuleException || $e->errorCode !== 'ATTEMPT_CHANGED') {
            return $e;
        }

        $fresh    = model(ChallengeAttemptModel::class)->find($attemptId);
        $redirect = $fresh?->isCompleted() ? site_url('selesai/' . $attemptId) : null;

        if ($redirect === null && $itemId !== null && model(ItemResponseModel::class)->findOne($attemptId, $itemId)?->isAnswered()) {
            return ChallengeRuleException::clueClosed();
        }

        if ($fresh !== null && $fresh->isExhausted()) {
            return ChallengeRuleException::triesExhausted($redirect);
        }

        return ChallengeRuleException::attemptChanged($redirect);
    }

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
     * Soal yang diminta dijawab pada pengerjaan ini (urut pilihan), keyed by
     * id. Butir yang sudah tidak ada di bank aktif dilewati.
     *
     * @return array<int, ChallengeItem>
     */
    private function expectedItems(ChallengeAttempt $attempt): array
    {
        $out = [];

        foreach ($this->itemsByIds(service('contentRepository')->itemBank($attempt->challenge_node_id), $attempt->selectedItemIds()) as $item) {
            if ($this->expectsAnswer($item)) {
                $out[$item->id] = $item;
            }
        }

        return $out;
    }

    /**
     * Jawaban satu soal batch terisi (Dok 2 §5.2 langkah 2): kata rumpang tidak
     * kosong, pilihan Benar/Salah sah, susunan keping lengkap.
     *
     * @param array<string, mixed>|null $answer
     */
    private function isFilled(ChallengeNode $node, ChallengeItem $item, mixed $answer): bool
    {
        if (! is_array($answer)) {
            return false;
        }

        return match ($item->interaction_type) {
            'fill_blank_bank' => trim((string) ($answer['text'] ?? '')) !== '',
            'verdict_card'    => in_array(mb_strtolower(trim((string) ($answer['verdict'] ?? ''))), $node->verdictOptions(), true),
            'puzzle_arrange'  => is_array($answer['order'] ?? null)
                && count($answer['order']) === count((array) $item->answerKey('order')),
            default => false,
        };
    }

    /** Semua soal yang diminta sudah dijawab benar. */
    private function allCorrect(ChallengeAttempt $attempt): bool
    {
        $responses = $this->expectedResponses($attempt);

        if ($responses === []) {
            return false;
        }

        foreach ($responses as $response) {
            if (! $response->isAnswered() || ! $response->is_correct) {
                return false;
            }
        }

        return true;
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
