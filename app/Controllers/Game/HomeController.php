<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Models\GameSessionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Layar welcome, pilihan masuk/daftar, cerita pembuka, dan pemilih bahasa.
 *
 * index(), start(), dan setLocale() dapat dibuka tanpa login; intro() berada
 * di grup filter `gameSession`, jadi GameContext sudah dimuat saat dipanggil.
 */
class HomeController extends BaseController
{
    public function index(): string
    {
        $levels = service('contentRepository')->levels();

        return view('game/welcome', [
            'locale'     => $this->locale,
            'isLoggedIn' => (int) session('participant_id') > 0,
            'levels'     => $levels,
        ]);
    }

    public function start(): string
    {
        return view('game/start', ['locale' => $this->locale]);
    }

    public function intro(): string
    {
        return view('game/intro', [
            'locale' => $this->locale,
            'slides' => service('contentRepository')->dialogues(null, 'intro'),
        ]);
    }

    /**
     * Ganti bahasa tanpa membuat sesi baru dan tanpa menyentuh progres.
     * Tujuan redirect berasal dari input pengguna, jadi selalu divalidasi
     * sebagai path internal.
     */
    public function setLocale(): RedirectResponse
    {
        $target = safe_internal_url($this->request->getPost('redirect_to'), '/');

        if (! $this->validate(['locale' => 'required|valid_locale'])) {
            return redirect()->to($target)->with('error', lang('Validation.valid_locale'));
        }

        $locale = (string) $this->request->getPost('locale');

        session()->set('locale', $locale);

        $participantId = (int) session('participant_id');
        $gameSessionId = (int) session('game_session_id');

        if ($participantId > 0 && $gameSessionId > 0) {
            $owned = model(GameSessionModel::class)
                ->where('id', $gameSessionId)
                ->where('participant_id', $participantId)
                ->first();

            if ($owned !== null) {
                service('sessionService')->setLocale($gameSessionId, $locale);
            }
        }

        return redirect()->to($target);
    }
}
