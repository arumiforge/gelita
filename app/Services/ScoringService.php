<?php

namespace App\Services;

use App\Entities\ChallengeAttempt;
use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeNodeModel;
use App\Models\ItemResponseModel;

/**
 * Aturan bintang `bintang-1` (perombakan kelas 5, Dok 1 §13). Skor 0–100,
 * bobot, dan profil skor sudah dihapus: bintang hanya hadiah di layar siswa.
 *
 *   ★★★  semua soal berhasil pada percobaan pertama
 *   ★★   semua soal benar di akhir (sebagian butuh percobaan ke-2/ke-3)
 *   ★    pengerjaan selesai, tetapi ada soal yang tetap salah
 *   0    pengerjaan belum selesai atau ditinggalkan
 *
 * Petunjuk, suara, dan waktu tidak mengurangi bintang; semuanya tetap
 * tercatat sebagai data mentah.
 */
class ScoringService
{
    /** Kode aturan, disimpan di game_releases.scoring_version */
    public const VERSION = 'bintang-1';

    /** Aturan bintang murni dari hitungan soal yang dinilai. */
    public static function starsFromCounts(bool $completed, int $scorable, int $firstPass, int $final): int
    {
        if (! $completed) {
            return 0;
        }

        if ($firstPass >= $scorable) {
            return 3;
        }

        return $final >= $scorable ? 2 : 1;
    }

    /** Bintang satu pengerjaan, dibaca dari item_responses soal yang dinilai. */
    public function starsFor(ChallengeAttempt $attempt): int
    {
        $counts = model(ItemResponseModel::class)->countCorrect($attempt->id);

        return self::starsFromCounts($attempt->isCompleted(), $counts['scorable'], $counts['first_pass'], $counts['final']);
    }

    /**
     * Menulis hitungan akhir ke baris pengerjaan: jumlah Berhasil pada
     * Percobaan Pertama, benar di akhir, soal dinilai, dan bintang.
     *
     * @return array{first_pass_correct: int, final_correct: int, scorable_items: int, stars: int}
     */
    public function finalize(ChallengeAttempt $attempt): array
    {
        $counts = model(ItemResponseModel::class)->countCorrect($attempt->id);

        $row = [
            'first_pass_correct' => $counts['first_pass'],
            'final_correct'      => $counts['final'],
            'scorable_items'     => $counts['scorable'],
            'stars'              => self::starsFromCounts($attempt->isCompleted(), $counts['scorable'], $counts['first_pass'], $counts['final']),
        ];

        model(ChallengeAttemptModel::class)->update($attempt->id, $row);

        return $row;
    }

    /** Jumlah bintang terbaik per node yang sudah tuntas pada satu sesi. */
    public function totalStars(int $sessionId): int
    {
        $stars = 0;

        foreach (model(ChallengeAttemptModel::class)->completedForSession($sessionId) as $attempt) {
            $stars += $attempt->stars;
        }

        return $stars;
    }

    /**
     * Ringkasan satu wilayah untuk layar siswa.
     *
     * @return array{completed_nodes: int, total_nodes: int, stars: int}
     */
    public function levelSummary(int $sessionId, int $levelId): array
    {
        $nodeIds = array_map(
            static fn ($node): int => (int) $node->id,
            model(ChallengeNodeModel::class)->forLevel($levelId),
        );

        $attempts = $nodeIds === [] ? [] : model(ChallengeAttemptModel::class)->completedForSession($sessionId);
        $stars    = 0;
        $done     = 0;

        foreach ($nodeIds as $nodeId) {
            if (isset($attempts[$nodeId])) {
                $stars += $attempts[$nodeId]->stars;
                $done++;
            }
        }

        return ['completed_nodes' => $done, 'total_nodes' => count($nodeIds), 'stars' => $stars];
    }
}
