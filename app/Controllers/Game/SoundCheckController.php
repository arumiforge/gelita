<?php

namespace App\Controllers\Game;

use App\Filters\SoundCheckFilter;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * `/cek-suara` — the research-mode sound check (owner, 15 November 2026,
 * decision 4 of 04; built 16 November 2026). The filter `soundCheck` sends
 * the first page of every research-mode game session here.
 *
 * The page plays one sound (game/sound-check.js, which also switches the game
 * sound on) and asks "Kamu mendengar suara ini?". Each answer is the research
 * event `sound_check_answered` (server only) with `heard` and `plays` (how
 * often the sound was played before the answer). "Ya" goes on to the page the
 * student was opening; "Tidak" shows what to do (call the research team, check
 * the headset, play again) and adds "Lanjut tanpa suara", which goes on
 * without another event: the last answer is the result. The check records
 * whether the narration could be heard, never the music.
 */
class SoundCheckController extends BaseGameController
{
    public function index(): string|RedirectResponse
    {
        if (! $this->researchMode()) {
            return redirect()->to($this->target());
        }

        return view('game/sound-check', $this->hudData() + [
            'help' => (bool) session(SoundCheckFilter::HELP),
        ]);
    }

    public function answer(): RedirectResponse
    {
        $session = $this->session();

        if (! $this->researchMode()) {
            return redirect()->to($this->target());
        }

        if ($this->request->getPost('lanjut') !== null) {
            return $this->done((int) $session->id);
        }

        $heard = (string) $this->request->getPost('heard');

        if (! in_array($heard, ['ya', 'tidak'], true)) {
            return redirect()->to(site_url('cek-suara'));
        }

        $plays = filter_var($this->request->getPost('plays'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 99]]);

        service('eventService')->record($session, 'sound_check_answered', [
            'heard' => $heard === 'ya',
            'plays' => $plays === false ? 0 : $plays,
        ]);

        if ($heard === 'tidak') {
            session()->set(SoundCheckFilter::HELP, true);

            return redirect()->to(site_url('cek-suara'));
        }

        return $this->done((int) $session->id);
    }

    private function done(int $gameSessionId): RedirectResponse
    {
        $target = $this->target();
        session()->set(SoundCheckFilter::DONE, $gameSessionId);
        session()->remove([SoundCheckFilter::TARGET, SoundCheckFilter::HELP]);

        return redirect()->to($target);
    }

    /** The page the student was opening; only a path of this site (no open redirect). */
    private function target(): string
    {
        $target = (string) session(SoundCheckFilter::TARGET);

        return preg_match('#^/(?!/)[A-Za-z0-9/_\-?=&%.]*$#', $target) === 1 && ! str_starts_with($target, '/cek-suara')
            ? site_url(ltrim($target, '/'))
            : site_url('peta');
    }

    private function researchMode(): bool
    {
        return $this->context()->researchMode($this->session());
    }
}
