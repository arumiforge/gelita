<?php

namespace App\Services;

use App\Entities\ChallengeAttempt;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeNodeModel;
use App\Models\ItemResponseModel;
use App\Models\ScoringProfileModel;

/**
 * Satu-satunya tempat rumus skor ditulis (01_DATABASE.md — Derived Metrics).
 * Tidak ada perhitungan skor di controller, view, atau JavaScript.
 *
 * Waktu tidak menjadi penalti: durasi disimpan sebagai indikator proses,
 * agar siswa yang lambat tetapi teliti tidak dirugikan.
 */
class ScoringService
{
    /**
     * Menghitung skor satu attempt dan menuliskannya ke baris attempt.
     * scoring_version disalin agar hasil historis tetap dapat direproduksi.
     *
     * @return array{score: float, stars: int, first_pass_accuracy: float, final_accuracy: float,
     *               independence: float, scorable_items: int, scoring_version: string}
     */
    public function scoreAttempt(ChallengeAttempt $attempt): array
    {
        $profile = $this->profileFor($attempt);
        $counts  = model(ItemResponseModel::class)->countCorrect($attempt->id);

        $result = $this->compute($counts, [
            'hint_count'  => $attempt->hint_count,
            'retry_count' => $attempt->retry_count,
            'completed'   => $attempt->status === 'completed',
        ], $profile);

        model(ChallengeAttemptModel::class)->update($attempt->id, [
            'scorable_items'      => $result['scorable_items'],
            'first_pass_correct'  => $counts['first_pass'],
            'final_correct'       => $counts['final'],
            'first_pass_accuracy' => $result['first_pass_accuracy'],
            'final_accuracy'      => $result['final_accuracy'],
            'independence'        => $result['independence'],
            'score'               => $result['score'],
            'stars'               => $result['stars'],
            'scoring_profile_id'  => (int) $profile['id'],
            'scoring_version'     => $profile['version'],
        ]);

        return $result;
    }

    /**
     * Rumus murni — tidak menyentuh database, sehingga dapat diuji langsung.
     *
     * @param array{scorable: int, first_pass: int, final: int}      $counts
     * @param array{hint_count: int, retry_count: int, completed: bool} $metrics
     * @param array<string, mixed>                                   $profile
     *
     * @return array{score: float, stars: int, first_pass_accuracy: float, final_accuracy: float,
     *               independence: float, scorable_items: int, scoring_version: string}
     */
    public function compute(array $counts, array $metrics, array $profile): array
    {
        $scorable = max(0, (int) ($counts['scorable'] ?? 0));

        $firstPassAccuracy = $scorable > 0 ? ((int) $counts['first_pass'] / $scorable) * 100 : 0.0;
        $finalAccuracy     = $scorable > 0 ? ((int) $counts['final'] / $scorable) * 100 : 0.0;

        $hintPenalty  = (int) ($metrics['hint_count'] ?? 0) * (float) $profile['hint_penalty_per_use'];
        $retryPenalty = (int) ($metrics['retry_count'] ?? 0) * (float) $profile['retry_penalty_per_extra_attempt'];
        $independence = clamp(100 - $hintPenalty - $retryPenalty, 0, 100);

        $score = clamp(
            (float) $profile['first_pass_weight'] * $firstPassAccuracy
            + (float) $profile['final_weight'] * $finalAccuracy
            + (float) $profile['independence_weight'] * $independence,
            0,
            100,
        );

        $firstPassAccuracy = round($firstPassAccuracy, 2);
        $finalAccuracy     = round($finalAccuracy, 2);
        $score             = round($score, 2);

        return [
            'scorable_items'      => $scorable,
            'first_pass_accuracy' => $firstPassAccuracy,
            'final_accuracy'      => $finalAccuracy,
            'independence'        => round($independence, 2),
            'score'               => $score,
            'stars'               => $this->stars($score, $firstPassAccuracy, (bool) ($metrics['completed'] ?? false), $profile),
            'scoring_version'     => (string) $profile['version'],
        ];
    }

    /**
     * 3 bintang butuh skor DAN ketepatan percobaan pertama;
     * 1 bintang cukup menyelesaikan; 0 untuk attempt yang belum selesai.
     */
    public function stars(float $score, float $firstPassAccuracy, bool $completed, array $profile): int
    {
        if (! $completed) {
            return 0;
        }

        if ($score >= (float) $profile['three_star_min_score']
            && $firstPassAccuracy >= (float) $profile['three_star_min_first_pass']) {
            return 3;
        }

        if ($score >= (float) $profile['two_star_min_score']) {
            return 2;
        }

        return 1;
    }

    /**
     * Rata-rata berbobot 5 node satu level: SUM(score * scorable_items) / SUM(scorable_items).
     *
     * @return array{score: float, stars: int, completed_nodes: int, total_nodes: int}
     */
    public function levelScore(int $sessionId, int $levelId): array
    {
        $nodeIds = array_map(
            static fn ($node): int => (int) $node->id,
            model(ChallengeNodeModel::class)->forLevel($levelId),
        );

        if ($nodeIds === []) {
            return ['score' => 0.0, 'stars' => 0, 'completed_nodes' => 0, 'total_nodes' => 0];
        }

        $attempts = model(ChallengeAttemptModel::class)->completedForSession($sessionId);

        $weighted = 0.0;
        $weight   = 0;
        $stars    = 0;
        $done     = 0;

        foreach ($nodeIds as $nodeId) {
            if (! isset($attempts[$nodeId])) {
                continue;
            }

            $attempt = $attempts[$nodeId];
            $items   = max(1, $attempt->scorable_items);

            $weighted += $attempt->score * $items;
            $weight += $items;
            $stars += $attempt->stars;
            $done++;
        }

        return [
            'score'           => $weight > 0 ? round($weighted / $weight, 2) : 0.0,
            'stars'           => $stars,
            'completed_nodes' => $done,
            'total_nodes'     => count($nodeIds),
        ];
    }

    /** Rata-rata 3 level; level yang belum dikerjakan dihitung 0. */
    public function totalScore(int $sessionId): float
    {
        $levels = service('contentRepository')->levels();

        if ($levels === []) {
            return 0.0;
        }

        $sum = 0.0;

        foreach ($levels as $level) {
            $sum += $this->levelScore($sessionId, $level->id)['score'];
        }

        return round($sum / count($levels), 2);
    }

    /** Total bintang seluruh node yang sudah tuntas pada satu sesi. */
    public function totalStars(int $sessionId): int
    {
        $stars = 0;

        foreach (model(ChallengeAttemptModel::class)->completedForSession($sessionId) as $attempt) {
            $stars += $attempt->stars;
        }

        return $stars;
    }

    /**
     * Menghitung ulang satu attempt dengan profil tertentu.
     * Dipakai `php spark gelita:score:recompute`.
     */
    public function recompute(int $attemptId, string $profileCode, string $version): array
    {
        $attempt = model(ChallengeAttemptModel::class)->find($attemptId);

        if ($attempt === null) {
            throw new \RuntimeException("Attempt {$attemptId} tidak ditemukan.");
        }

        $profile = model(ScoringProfileModel::class)->findVersion($profileCode, $version);

        if ($profile === null) {
            throw new \RuntimeException("Scoring profile {$profileCode} versi {$version} tidak ditemukan.");
        }

        $counts = model(ItemResponseModel::class)->countCorrect($attempt->id);

        $result = $this->compute($counts, [
            'hint_count'  => $attempt->hint_count,
            'retry_count' => $attempt->retry_count,
            'completed'   => $attempt->status === 'completed',
        ], $profile);

        model(ChallengeAttemptModel::class)->update($attempt->id, [
            'scorable_items'      => $result['scorable_items'],
            'first_pass_correct'  => $counts['first_pass'],
            'final_correct'       => $counts['final'],
            'first_pass_accuracy' => $result['first_pass_accuracy'],
            'final_accuracy'      => $result['final_accuracy'],
            'independence'        => $result['independence'],
            'score'               => $result['score'],
            'stars'               => $result['stars'],
            'scoring_profile_id'  => (int) $profile['id'],
            'scoring_version'     => $profile['version'],
        ]);

        return $result;
    }

    /** Profil node bila ada, selain itu profil aktif. */
    public function profileFor(ChallengeAttempt $attempt): array
    {
        $model = model(ScoringProfileModel::class);
        $node  = service('contentRepository')->node($attempt->challenge_node_id);

        if ($node !== null && $node->scoring_profile_id !== null) {
            $profile = $model->find($node->scoring_profile_id);

            if ($profile !== null) {
                return $profile;
            }
        }

        return $model->active();
    }
}
