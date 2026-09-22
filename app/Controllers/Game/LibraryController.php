<?php

namespace App\Controllers\Game;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Pustaka Kedu — bacaan pendamping tiap wilayah.
 * Membuka pustaka tidak memengaruhi skor, bintang, atau status node sama sekali.
 */
class LibraryController extends BaseGameController
{
    public function show(string $code): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);

        if (! $this->levelUnlocked($session, $level->sequence)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.levelLocked'));
        }

        service('eventService')->record($session, 'library_opened', [], ['level_id' => $level->id]);

        return view('game/library', $this->hudData() + [
            'level' => $level,
            'pages' => service('contentRepository')->libraryPages($level->id),
        ]);
    }
}
