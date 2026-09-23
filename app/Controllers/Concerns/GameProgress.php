<?php

namespace App\Controllers\Concerns;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Models\ChallengeAttemptModel;
use App\Models\ResearchStudyModel;
use App\Models\SessionProgressModel;

/**
 * Status buka/kunci level & node untuk layar peta dan payload API.
 *
 * Trait ini hanya menyusun status dari data yang sudah ditulis Service tahap 3
 * (`session_progress` dan `challenge_attempts`). Tidak ada rumus skor,
 * tidak ada penilaian benar/salah, dan tidak ada query analitik di sini.
 */
trait GameProgress
{
    /** `free` bila studi membuka semua level sekaligus, selain itu `sequential`. */
    protected function unlockMode(GameSession $session): string
    {
        $study = model(ResearchStudyModel::class)->find($session->study_id);

        return ($study['unlock_mode'] ?? 'sequential') === 'free' ? 'free' : 'sequential';
    }

    /**
     * Seluruh level beserta status, skor, dan bintang sesi berjalan.
     *
     * @return list<array<string, mixed>>
     */
    protected function levelOverview(GameSession $session): array
    {
        $progress = model(SessionProgressModel::class)->ensure($session->id);
        $unlocked = (int) $progress['unlocked_level_sequence'];
        $free     = $this->unlockMode($session) === 'free';
        $scoring  = service('scoringService');
        $locale   = $session->resolvedLocale();

        $out = [];

        foreach (service('contentRepository')->levels() as $level) {
            $score  = $scoring->levelScore($session->id, $level->id);
            $open   = $free || $level->sequence <= $unlocked;
            $status = $this->levelStatus($score, $open);

            $out[] = [
                'id'              => $level->id,
                'code'            => (string) $level->code,
                'sequence'        => $level->sequence,
                'name'            => $level->text('name', $locale),
                'difficulty'      => (string) $level->difficulty,
                'status'          => $status,
                'entry'           => $this->regionEntryPath((string) $level->code, $status),
                'score'           => $score['score'],
                'stars'           => $score['stars'],
                'completed_nodes' => $score['completed_nodes'],
                'total_nodes'     => $score['total_nodes'],
                'map_x'           => $level->map_x,
                'map_y'           => $level->map_y,
                'map_media'       => media_src($level->map_media_id),
                // latar wilayah untuk dipra-muat peta (map.js); null bila belum diunggah
                'background'      => media_exists($level->background_media_id) ? media_src($level->background_media_id) : null,
            ];
        }

        return $out;
    }

    /**
     * Lima node satu level beserta statusnya.
     *
     * Node ke-n terbuka bila n = 1 atau node ke-(n-1) sudah `completed`.
     * Pada `unlock_mode = free`, semuanya terbuka.
     *
     * @return list<array<string, mixed>>
     */
    protected function nodeOverview(GameSession $session, int $levelId): array
    {
        $done   = model(ChallengeAttemptModel::class)->completedForSession($session->id);
        $free   = $this->unlockMode($session) === 'free';
        $locale = $session->resolvedLocale();

        $out          = [];
        $previousDone = true;

        foreach (service('contentRepository')->nodesForLevel($levelId) as $node) {
            $attempt   = $done[$node->id] ?? null;
            $completed = $attempt !== null;
            $open      = $free || $completed || $previousDone;

            $out[] = [
                'id'          => $node->id,
                'sequence'    => $node->sequence,
                'engine_type' => (string) $node->engine_type,
                'variant'     => (string) ($node->variant_code ?? ''),
                'title'       => $node->text('title', $locale),
                'description' => $node->text('description', $locale),
                'status'      => $completed ? 'completed' : ($open ? 'open' : 'locked'),
                'score'       => $attempt?->score === null ? null : (float) $attempt->score,
                'stars'       => $attempt?->stars === null ? null : (int) $attempt->stars,
                'map_x'       => $node->map_x,
                'map_y'       => $node->map_y,
            ];

            $previousDone = $completed;
        }

        return $out;
    }

    /**
     * Status seluruh node per wilayah untuk rincian lentera di HUD.
     *
     * Aturan buka/kunci sama dengan levelOverview() + nodeOverview(), tetapi
     * attempt selesai dibaca SEKALI untuk semua wilayah karena HUD ada di
     * setiap layar permainan.
     *
     * @return list<array{code: string, name: string, nodes: list<array{sequence: int, title: string, status: string}>}>
     */
    protected function lanternMap(GameSession $session): array
    {
        $done     = model(ChallengeAttemptModel::class)->completedForSession($session->id);
        $free     = $this->unlockMode($session) === 'free';
        $progress = model(SessionProgressModel::class)->ensure($session->id);
        $unlocked = (int) $progress['unlocked_level_sequence'];
        $locale   = $session->resolvedLocale();
        $content  = service('contentRepository');

        $out = [];

        foreach ($content->levels() as $level) {
            $levelOpen    = $free || $level->sequence <= $unlocked;
            $previousDone = true;
            $nodes        = [];

            foreach ($content->nodesForLevel($level->id) as $node) {
                $completed = isset($done[$node->id]);
                $open      = $levelOpen && ($free || $completed || $previousDone);

                $nodes[] = [
                    'sequence' => $node->sequence,
                    'title'    => $node->text('title', $locale),
                    'status'   => $completed ? 'completed' : ($open ? 'open' : 'locked'),
                ];

                $previousDone = $completed;
            }

            $out[] = [
                'code'  => (string) $level->code,
                'name'  => $level->text('name', $locale),
                'nodes' => $nodes,
            ];
        }

        return $out;
    }

    /** Level terbuka untuk sesi ini? */
    protected function levelUnlocked(GameSession $session, int $sequence): bool
    {
        if ($this->unlockMode($session) === 'free') {
            return true;
        }

        $progress = model(SessionProgressModel::class)->ensure($session->id);

        return $sequence <= (int) $progress['unlocked_level_sequence'];
    }

    /** Node terbuka untuk sesi ini? */
    protected function nodeUnlocked(GameSession $session, ChallengeNode $node): bool
    {
        foreach ($this->nodeOverview($session, $node->level_id) as $row) {
            if ($row['id'] === $node->id) {
                return $row['status'] !== 'locked';
            }
        }

        return false;
    }

    /**
     * Attempt `completed` terbaik pada node, atau null.
     */
    protected function bestAttempt(GameSession $session, int $nodeId): ?ChallengeAttempt
    {
        return model(ChallengeAttemptModel::class)->completedForSession($session->id)[$nodeId] ?? null;
    }

    /**
     * Ringkasan progres sesi untuk HUD dan payload API.
     *
     * @return array<string, mixed>
     */
    protected function progressSummary(GameSession $session): array
    {
        $progress = model(SessionProgressModel::class)->ensure($session->id);
        $total    = 0;

        foreach (service('contentRepository')->levels() as $level) {
            $total += count(service('contentRepository')->nodesForLevel($level->id));
        }

        return [
            'completed_nodes'         => (int) $progress['completed_nodes'],
            'completed_levels'        => (int) $progress['completed_levels'],
            'unlocked_level_sequence' => (int) $progress['unlocked_level_sequence'],
            'total_score'             => (float) $progress['total_score'],
            'total_stars'             => (int) $progress['total_stars'],
            'shards'                  => (int) $progress['completed_nodes'],
            'shards_total'            => $total,
            'current_level_id'        => $progress['current_level_id'] === null ? null : (int) $progress['current_level_id'],
            'current_node_id'         => $progress['current_node_id'] === null ? null : (int) $progress['current_node_id'],
        ];
    }

    /** Seluruh node aktif sudah tuntas pada sesi ini? Syarat masuk Balai Refleksi. */
    protected function allNodesCompleted(GameSession $session): bool
    {
        $summary = $this->progressSummary($session);

        return $summary['shards_total'] > 0 && $summary['completed_nodes'] >= $summary['shards_total'];
    }

    /**
     * Jalan masuk ke wilayah dari peta dan dari layar selesai.
     *
     * Wilayah yang baru terbuka SELALU lewat dialog pembuka Jaka & Mbah Kedu
     * lebih dulu; tombol di akhir dialog membawa ke peta wilayah. Wilayah yang
     * sedang dijelajahi atau sudah tuntas langsung ke peta wilayahnya. Wilayah
     * terkunci tetap menuju peta wilayah, yang menolaknya dengan pesan
     * "selesaikan wilayah sebelumnya". URL yang diketik langsung dijaga
     * BaseGameController::dialogueGate() dengan aturan yang sama.
     */
    private function regionEntryPath(string $code, string $status): string
    {
        return ($this->isNewRegion($status) ? 'dialog/' : 'wilayah/') . $code;
    }

    /** Wilayah baru terbuka: status `open` — terbuka, belum ada tantangan yang selesai. */
    private function isNewRegion(string $status): bool
    {
        return $status === 'open';
    }

    /** @param array{completed_nodes: int, total_nodes: int} $score */
    private function levelStatus(array $score, bool $open): string
    {
        if (! $open) {
            return 'locked';
        }

        if ($score['total_nodes'] > 0 && $score['completed_nodes'] >= $score['total_nodes']) {
            return 'completed';
        }

        return $score['completed_nodes'] > 0 ? 'in_progress' : 'open';
    }
}
