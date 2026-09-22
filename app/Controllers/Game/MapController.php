<?php

namespace App\Controllers\Game;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Peta Kedu (tiga wilayah) dan peta wilayah (lima pos tantangan).
 * Status buka/kunci disusun GameProgress dari `session_progress`.
 */
class MapController extends BaseGameController
{
    public function kedu(): string
    {
        $session = $this->session();

        return view('game/map-kedu', $this->hudData() + [
            'levels'     => $this->levelOverview($session),
            'unlockMode' => $this->unlockMode($session),
        ]);
    }

    public function level(string $code): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);

        if (! $this->levelUnlocked($session, $level->sequence)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.levelLocked'));
        }

        return view('game/map-level', $this->hudData() + [
            'level'      => $level,
            'levelScore' => service('scoringService')->levelScore($session->id, $level->id),
            'nodes'      => $this->nodeOverview($session, $level->id),
            'hasLibrary' => service('contentRepository')->libraryPages($level->id) !== [],
        ]);
    }
}
