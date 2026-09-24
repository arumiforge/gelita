<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Models\GameSessionModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Layar welcome, pilihan masuk/daftar, dan pemilih bahasa — semuanya dapat
 * dibuka tanpa login. Cerita pembuka dan pilihan pemain lama ada di
 * GateController (grup filter `gameSession`).
 */
class HomeController extends BaseController
{
    /** Halaman awal: logo dan satu tombol Mulai; HUD tanpa merek (logo sudah besar). */
    public function index(): string
    {
        return view('game/welcome', [
            'locale'    => $this->locale,
            'hideBrand' => true,
        ]);
    }

    /**
     * Tujuan tombol Mulai. Belum login → pilihan "Saya baru" / "Sudah punya
     * akun"; sudah login → `/gerbang` (cerita pembuka atau pilihan pemain lama).
     */
    public function start(): string|RedirectResponse
    {
        if ((int) session('participant_id') > 0 && (int) session('game_session_id') > 0) {
            return redirect()->to(site_url('gerbang'));
        }

        return view('game/start', ['locale' => $this->locale]);
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
