<?php

namespace App\Controllers\Concerns;

use App\Entities\ChallengeAttempt;
use App\Entities\ChallengeNode;
use App\Entities\GameSession;
use App\Entities\Level;
use App\Models\ChallengeAttemptModel;
use App\Models\ResearchStudyModel;
use App\Models\ParticipantProgressModel;

/**
 * Status buka/kunci level & node untuk layar peta dan payload API.
 *
 * Trait ini hanya menyusun status dari data yang sudah ditulis Service tahap 3
 * (`participant_progress` dan `challenge_attempts`). Tidak ada rumus skor,
 * tidak ada penilaian benar/salah, dan tidak ada query analitik di sini.
 *
 * Research revision 10, part 2 (28 §8, §9): progress is the student's across
 * every game session of the study, and in field mode the open regions come
 * from the meeting plan of the session's field session instead of
 * `unlock_mode` (challenges inside a region stay in order).
 */
trait GameProgress
{
    /** `free` bila studi membuka semua level sekaligus, selain itu `sequential`. */
    protected function unlockMode(GameSession $session): string
    {
        // Field mode ignores unlock_mode: regions come from the meeting plan, challenges stay in order (28 §9)
        if ($this->meetingRegions($session) !== null) {
            return 'sequential';
        }

        $study = model(ResearchStudyModel::class)->find($session->study_id);

        return ($study['unlock_mode'] ?? 'sequential') === 'free' ? 'free' : 'sequential';
    }

    /**
     * Region codes the field session of this game session opens, or null
     * when field mode is off (28 §9).
     *
     * @return list<string>|null
     */
    protected function meetingRegions(GameSession $session): ?array
    {
        return service('fieldSessions')->openRegions($session);
    }

    /** One region open? Meeting plan in field mode, else unlock_mode and progress. */
    private function regionOpen(GameSession $session, Level $level, bool $free, int $unlocked): bool
    {
        $regions = $this->meetingRegions($session);

        if ($regions !== null) {
            return in_array((string) $level->code, $regions, true);
        }

        return $free || $level->sequence <= $unlocked;
    }

    /**
     * Seluruh level beserta status dan bintang sesi berjalan.
     *
     * @return list<array<string, mixed>>
     */
    protected function levelOverview(GameSession $session): array
    {
        $progress = model(ParticipantProgressModel::class)->forSession($session);
        $unlocked = (int) $progress['unlocked_level_sequence'];
        $free     = $this->unlockMode($session) === 'free';
        $scoring  = service('scoringService');
        $locale   = $session->resolvedLocale();

        $out = [];

        foreach (service('contentRepository')->levels() as $level) {
            $summary  = $scoring->levelSummary($session->id, $level->id);
            $open   = $this->regionOpen($session, $level, $free, $unlocked);
            $status = $this->levelStatus($summary, $open);

            $out[] = [
                'id'              => $level->id,
                'code'            => (string) $level->code,
                'sequence'        => $level->sequence,
                'name'            => $level->text('name', $locale),
                'difficulty'      => (string) $level->difficulty,
                'status'          => $status,
                'entry'           => $this->regionEntryPath((string) $level->code, $status),
                'stars'           => $summary['stars'],
                'completed_nodes' => $summary['completed_nodes'],
                'total_nodes'     => $summary['total_nodes'],
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
     * Empat node satu level beserta statusnya.
     *
     * Node ke-n terbuka bila n = 1 atau node ke-(n-1) sudah `completed`.
     * Pada `unlock_mode = free`, semuanya terbuka.
     *
     * @return list<array<string, mixed>>
     */
    protected function nodeOverview(GameSession $session, int $levelId): array
    {
        $done    = model(ChallengeAttemptModel::class)->completedFor($session);
        $free    = $this->unlockMode($session) === 'free';
        $locale  = $session->resolvedLocale();
        // Part 7 (33 §7.4): research mode keeps completed challenges closed until the retake rule allows them
        $retake  = $this->regionRetakeOpen($session, $levelId, $done);

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
                // Part 7 (33 §7.4; owner, 9 November 2026): research mode, done but not yet open for a retake
                'status'      => $completed ? ($retake ? 'completed' : 'done_locked') : ($open ? 'open' : 'locked'),
                'stars'       => $attempt?->stars === null ? null : (int) $attempt->stars,
                'map_x'       => $node->map_x,
                'map_y'       => $node->map_y,
            ];

            $previousDone = $completed;
        }

        return $out;
    }

    /**
     * Part 7 (33 §7.4): may the completed challenges of a region be played
     * again in this session? Always outside research mode; in research mode
     * ChallengeService::retakeAllowed() decides (same rule for the API).
     *
     * @param array<int, ChallengeAttempt> $done completedFor() of the session
     */
    protected function regionRetakeOpen(GameSession $session, int $levelId, array $done): bool
    {
        if (! service('gameContext')->researchMode($session)) {
            return true;
        }

        $nodes = service('contentRepository')->nodesForLevel($levelId);

        return $nodes !== [] && service('challengeService')->retakeAllowed($session, $nodes[0]);
    }

    /** Part 7 (33 §7.4): a completed challenge the student may not play again yet. */
    protected function retakeLocked(GameSession $session, ChallengeNode $node): bool
    {
        return $this->bestAttempt($session, $node->id) !== null && ! service('challengeService')->retakeAllowed($session, $node);
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
        $done     = model(ChallengeAttemptModel::class)->completedFor($session);
        $free     = $this->unlockMode($session) === 'free';
        $progress = model(ParticipantProgressModel::class)->forSession($session);
        $unlocked = (int) $progress['unlocked_level_sequence'];
        $locale   = $session->resolvedLocale();
        $content  = service('contentRepository');

        $out = [];

        foreach ($content->levels() as $level) {
            $levelOpen    = $this->regionOpen($session, $level, $free, $unlocked);
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
        $regions = $this->meetingRegions($session);

        if ($regions !== null) {
            foreach (service('contentRepository')->levels() as $level) {
                if ($level->sequence === $sequence) {
                    return in_array((string) $level->code, $regions, true);
                }
            }

            return false;
        }

        if ($this->unlockMode($session) === 'free') {
            return true;
        }

        $progress = model(ParticipantProgressModel::class)->forSession($session);

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
        return model(ChallengeAttemptModel::class)->completedFor($session)[$nodeId] ?? null;
    }

    /**
     * Ringkasan progres sesi untuk HUD dan payload API.
     *
     * @return array<string, mixed>
     */
    protected function progressSummary(GameSession $session): array
    {
        $progress = model(ParticipantProgressModel::class)->forSession($session);
        $total    = 0;

        foreach (service('contentRepository')->levels() as $level) {
            $total += count(service('contentRepository')->nodesForLevel($level->id));
        }

        return [
            'completed_nodes'         => (int) $progress['completed_nodes'],
            'completed_levels'        => (int) $progress['completed_levels'],
            'unlocked_level_sequence' => (int) $progress['unlocked_level_sequence'],
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
     * Pustaka sebuah wilayah baru terbuka setelah SEMUA tantangannya selesai
     * pada sesi ini — apa pun `unlock_mode` studinya (mode `free` membuka
     * wilayah dan tantangan, bukan Pustaka). Status Pustaka diturunkan dari
     * status wilayah (levelStatus()):
     *
     * - `open`        wilayah tuntas → isi Pustaka boleh dibaca;
     * - `locked`      wilayah terbuka tetapi belum tuntas → penjelasan + progres;
     * - `unavailable` wilayah belum terbuka → keterangan saja.
     */
    protected function libraryStatus(string $levelStatus): string
    {
        return match ($levelStatus) {
            'completed' => 'open',
            'locked'    => 'unavailable',
            default     => 'locked',
        };
    }

    /**
     * Status Pustaka satu wilayah beserta progres dan jalan untuk melanjutkan
     * tantangannya (`entry`, aturan dialog pembuka yang sama dengan peta).
     *
     * @return array{status: string, level_status: string, entry: string, completed_nodes: int, total_nodes: int}
     */
    protected function libraryAccess(GameSession $session, Level $level): array
    {
        $summary  = service('scoringService')->levelSummary($session->id, $level->id);
        $status = $this->levelStatus($summary, $this->levelUnlocked($session, $level->sequence));

        return [
            'status'          => $this->libraryStatus($status),
            'level_status'    => $status,
            'entry'           => $this->regionEntryPath((string) $level->code, $status),
            'completed_nodes' => (int) $summary['completed_nodes'],
            'total_nodes'     => (int) $summary['total_nodes'],
        ];
    }

    /**
     * Status satu wilayah pada sesi ini (`locked` | `open` | `in_progress` |
     * `completed`), aturan yang sama dengan levelOverview(). Dipakai gerbang
     * adegan wilayah tuntas (`/tuntas/{code}`).
     */
    protected function regionStatus(GameSession $session, Level $level): string
    {
        $summary = service('scoringService')->levelSummary($session->id, $level->id);

        return $this->levelStatus($summary, $this->levelUnlocked($session, $level->sequence));
    }

    /**
     * Isi tirai wilayah `region` untuk satu baris levelOverview(): judul
     * "Menuju {wilayah}…", tagline (judul slide pertama `region_intro`
     * wilayah itu, mis. "Tanah Candi Agung"), chip tingkat kesulitan, dan aset
     * yang dipramuat untuk halaman tujuannya (`entry`): latar wilayah, lalu
     * frame tokoh sesuai pose dialog pembuka (wilayah baru terbuka) atau
     * gambar peta wilayah (selain itu). Aset yang belum diunggah tidak ikut.
     *
     * @param array<string, mixed> $row baris levelOverview()
     *
     * `bg` is the curtain's own picture (display plan Phase C): the city's
     * `bg.loading.{wilayah}`, else the region background; it goes first in
     * `preload`, so it is decoded before the short curtain needs it.
     *
     * @return array{text: array{title: string, tagline: string, chip: string}, preload: list<string>, bg: string|null}
     */
    protected function regionCurtain(array $row, string $locale): array
    {
        $content = service('contentRepository');
        $levelId = (int) $row['id'];
        $intro   = $content->dialogues($levelId, 'region_intro');
        $bg      = region_loading_bg((string) $row['code'], $row['background'] ?? null);
        $urls    = [$bg, $row['background'] ?? null];

        if (str_starts_with((string) ($row['entry'] ?? ''), 'dialog/')) {
            foreach (['jaka', 'mbah_kedu'] as $character) {
                $urls[] = character_frame_src($character, 'idle');
            }

            foreach ($content->dialogues($levelId, 'level_open') as $line) {
                $urls[] = character_frame_src((string) ($line['character_code'] ?? ''), (string) ($line['pose'] ?? ''));
            }
        } elseif (! empty($row['map_media']) && ! media_is_placeholder((string) $row['map_media'])) {
            $urls[] = $row['map_media'];
        }

        $difficulty = (string) ($row['difficulty'] ?? '');

        return [
            'text' => [
                'title'   => lang('Game.curtainRegionTitle', [(string) $row['name']]),
                'tagline' => $intro === [] ? '' : tr($intro[0], 'title', $locale),
                'chip'    => $difficulty === '' ? '' : lang_or('Game.difficulty_' . $difficulty, $difficulty),
                // Stored code: core/curtain.js lights the difficulty meter's bars from it
                'difficulty' => $difficulty,
            ],
            'preload' => array_values(array_unique(array_filter($urls, static fn ($url): bool => is_string($url) && $url !== ''))),
            'bg'      => $bg,
        ];
    }

    /**
     * Baris levelOverview() wilayah sesudah $sequence (beserta `entry` dan
     * `curtain`), atau null untuk wilayah terakhir.
     *
     * @return array<string, mixed>|null
     */
    protected function nextRegion(GameSession $session, int $sequence): ?array
    {
        foreach ($this->levelOverview($session) as $row) {
            if ($row['sequence'] === $sequence + 1) {
                return $row + ['curtain' => $this->regionCurtain($row, $session->resolvedLocale())];
            }
        }

        return null;
    }

    /**
     * Attempt yang menuntaskan wilayah: di antara penyelesaian PERTAMA tiap
     * node wilayah, yang paling akhir. Null bila masih ada node yang belum
     * pernah selesai. Mengulang node yang sudah tuntas tidak pernah menjadi
     * attempt penuntas, jadi momen "Pustaka terbuka" hanya muncul sekali.
     *
     * @param list<ChallengeAttempt> $attempts attempt `completed` sesi ini,
     *                                         urut completed_at lalu id (naik)
     * @param list<int>              $nodeIds  node aktif wilayah itu
     */
    protected static function closingAttemptId(array $attempts, array $nodeIds): ?int
    {
        if ($nodeIds === []) {
            return null;
        }

        $wanted  = array_flip($nodeIds);
        $first   = [];
        $closing = null;

        foreach ($attempts as $attempt) {
            $node = (int) $attempt->challenge_node_id;

            if (isset($wanted[$node]) && ! isset($first[$node])) {
                $first[$node] = true;
                $closing      = (int) $attempt->id;
            }
        }

        return count($first) === count($wanted) ? $closing : null;
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

    /** @param array{completed_nodes: int, total_nodes: int} $summary */
    private function levelStatus(array $summary, bool $open): string
    {
        if (! $open) {
            return 'locked';
        }

        if ($summary['total_nodes'] > 0 && $summary['completed_nodes'] >= $summary['total_nodes']) {
            return 'completed';
        }

        return $summary['completed_nodes'] > 0 ? 'in_progress' : 'open';
    }
}
