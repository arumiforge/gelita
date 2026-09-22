<?php

namespace App\Controllers\Game;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Dialog pembuka wilayah (Jaka & Mbah Kedu).
 * Perpindahan slide dicatat client lewat `/api/events` (`dialogue_advanced`).
 */
class DialogueController extends BaseGameController
{
    public function show(string $code): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);

        if (! $this->levelUnlocked($session, $level->sequence)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.levelLocked'));
        }

        return view('game/dialogue', $this->hudData() + [
            'level'  => $level,
            'slides' => service('contentRepository')->dialogues($level->id, 'level_open'),
        ]);
    }
}
