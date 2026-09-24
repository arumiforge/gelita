<?php

namespace App\Controllers\Api;

use App\Entities\LibraryPage;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Konten permainan untuk mesin di browser.
 * Tidak pernah mengirim kunci jawaban: payload soal hanya keluar lewat
 * ChallengeApiController::open(), yang sudah disaring ChallengeService.
 */
class ContentApiController extends BaseApiController
{
    public function levels(): ResponseInterface
    {
        return $this->ok(['levels' => $this->levelOverview($this->gameSession())]);
    }

    public function nodes(int $levelId): ResponseInterface
    {
        $level = service('contentRepository')->levelById($levelId);

        if ($level === null) {
            return $this->fail('NOT_FOUND', lang('Game.errNotFound'), 404);
        }

        $session = $this->gameSession();

        return $this->ok([
            'level'  => ['id' => $level->id, 'code' => (string) $level->code, 'sequence' => $level->sequence],
            'locked' => ! $this->levelUnlocked($session, $level->sequence),
            'nodes'  => $this->nodeOverview($session, $level->id),
        ]);
    }

    public function node(int $nodeId): ResponseInterface
    {
        $node = service('contentRepository')->node($nodeId);

        if ($node === null) {
            return $this->fail('NOT_FOUND', lang('Game.errNotFound'), 404);
        }

        $session = $this->gameSession();
        $level   = service('contentRepository')->levelById($node->level_id);

        // metadata node tidak boleh bocor dari wilayah yang belum terbuka
        if ($level === null || ! $this->levelUnlocked($session, $level->sequence)) {
            return $this->fail('LEVEL_LOCKED', lang('Game.levelLocked'), 409);
        }

        $locale = $session->resolvedLocale();
        $best   = $this->bestAttempt($session, $node->id);

        return $this->ok([
            'node' => [
                'id'           => $node->id,
                'level_id'     => $node->level_id,
                'sequence'     => $node->sequence,
                'engine_type'  => (string) $node->engine_type,
                'variant_code' => $node->variant_code,
                'title'        => $node->text('title', $locale),
                'instruction'  => $node->text('instruction', $locale),
                'description'  => $node->text('description', $locale),
                'allow_retry'  => $node->allowsRetry(),
                'background'   => media_src($node->background_media_id),
                'scene'        => media_src($node->scene_media_id),
                'hints_count'  => count(service('contentRepository')->hintsFor($node->id)),
            ],
            'best' => $best === null ? null : [
                'attempt_id'          => $best->id,
                'score'               => (float) $best->score,
                'stars'               => (int) $best->stars,
                'first_pass_accuracy' => (float) $best->first_pass_accuracy,
            ],
        ]);
    }

    public function library(int $levelId): ResponseInterface
    {
        $level = service('contentRepository')->levelById($levelId);

        if ($level === null) {
            return $this->fail('NOT_FOUND', lang('Game.errNotFound'), 404);
        }

        $session = $this->gameSession();

        if (! $this->levelUnlocked($session, $level->sequence)) {
            return $this->fail('LEVEL_LOCKED', lang('Game.levelLocked'), 409);
        }

        $locale = $session->resolvedLocale();

        $pages = array_map(static fn (LibraryPage $page): array => [
            'id'       => $page->id,
            'sequence' => $page->sequence,
            'title'    => $page->text('title', $locale),
            'body'     => $page->text('body', $locale),
            // Galeri tanpa batas jumlah (library_media); `embed` hanya dimuat
            // setelah pemain menekan Putar. Empat slot lama sudah disalin ke
            // galeri oleh migration 003500.
            'media'    => $page->gallery($locale),
        ], service('contentRepository')->libraryPages($levelId));

        return $this->ok([
            'level' => ['id' => $level->id, 'code' => (string) $level->code, 'name' => $level->text('name', $locale)],
            'pages' => $pages,
        ]);
    }
}
