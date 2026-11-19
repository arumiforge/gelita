<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Layar welcome, pilihan masuk/daftar, dan pemilih bahasa — semuanya dapat
 * dibuka tanpa login. Cerita pembuka dan pilihan pemain lama ada di
 * GateController (grup filter `gameSession`).
 */
class HomeController extends BaseController
{
    /**
     * Start page: logo, invitation, region chips and one Mulai button on the left, Jaka on
     * the right; HUD without the brand (the logo is already large).
     * A signed-in student gets Profil and Keluar in the HUD menu (owner, 20 October 2026).
     */
    public function index(): string
    {
        return view('game/welcome', [
            'locale'      => $this->locale,
            'regions'     => $this->regionChips(),
            'hideBrand'   => true,
            'homePage'    => true,
            'participant' => $this->signedInParticipant(),
        ]);
    }

    /**
     * The three region chips under the invitation (home redesign, owner 19 November 2026).
     * The start page never fails over them: without content (a fresh install, a broken
     * database) it simply shows none.
     *
     * @return list<array{code: string, name: string}>
     */
    private function regionChips(): array
    {
        $regions = [];

        try {
            foreach (service('contentRepository')->levels() as $level) {
                $regions[] = ['code' => (string) $level->code, 'name' => $level->text('name', $this->locale)];
            }
        } catch (\Throwable $e) {
            log_message('warning', 'Start page without region chips: {message}', ['message' => $e->getMessage()]);

            return [];
        }

        return $regions;
    }

    /** The signed-in student in its safe shape (toSafeArray), or null before login. */
    private function signedInParticipant(): ?array
    {
        $participantId = (int) session('participant_id');

        if ($participantId <= 0) {
            return null;
        }

        $participant = model(ParticipantModel::class)->find($participantId);

        return $participant === null ? null : $participant->toSafeArray();
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
     * Tentang GELITA (`/tentang`, owner 21 October 2026): what the game is, its
     * story, how to play, and what is in it, in plain words for children. Open
     * to everyone from the HUD menu; Back returns to the page the student came
     * from when that page is part of GELITA, else to the start page.
     */
    public function about(): string
    {
        $referer = $this->request->getHeaderLine('Referer');
        $base    = rtrim(base_url(), '/') . '/';
        $back    = str_starts_with($referer, $base) && ! str_starts_with($referer, $base . 'tentang') ? $referer : base_url();

        return view('game/about', [
            'locale'      => $this->locale,
            'participant' => $this->signedInParticipant(),
            'back'        => $back,
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
