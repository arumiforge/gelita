<?php

namespace App\Controllers\Game;

use App\Models\ParticipantModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Gerbang masuk permainan bagi siswa yang sudah login: cerita pembuka dan
 * pilihan pemain lama.
 *
 * Pemain baru wajib menonton cerita pembuka sampai selesai (tanpa "Lewati");
 * penandanya `participants.intro_seen_at`, diisi `/intro/selesai`. Pemain lama
 * memilih di `/gerbang`: lihat cerita pembuka lagi, atau langsung ke peta.
 * Seluruh route di sini berada di grup filter `gameSession`.
 */
class GateController extends BaseGameController
{
    /** `/gerbang` — tujuan tombol Mulai (lewat `/mulai`) bagi siswa yang sudah login. */
    public function index(): string|RedirectResponse
    {
        if ($gate = $this->introGate()) {
            return $gate;
        }

        $participant = $this->participant();

        return view('game/start-choice', $this->hudData() + [
            'name' => (string) ($participant->display_name ?: $participant->username),
        ]);
    }

    /** `/gerbang/peta` — kartu "Langsung ke peta"; flash tirai hanya dapat diset server. */
    public function map(): RedirectResponse
    {
        return $this->toMap();
    }

    /** `/intro` — "Lewati" hanya untuk yang sudah pernah menonton sampai selesai. */
    public function intro(): string
    {
        return view('game/intro', [
            'locale'  => $this->session()->resolvedLocale(),
            'slides'  => service('contentRepository')->dialogues(null, 'intro'),
            'canSkip' => $this->participant()->hasSeenIntro(),
        ]);
    }

    /**
     * `/intro/selesai` — slide terakhir cerita pembuka. `intro_seen_at` hanya
     * diisi sekali (idempoten); event `intro_completed` dicatat setiap kali,
     * dengan `first` = true pada tontonan pertama.
     */
    public function finishIntro(): RedirectResponse
    {
        $first = model(ParticipantModel::class)->markIntroSeen($this->participant()->id);

        service('eventService')->record($this->session(), 'intro_completed', ['first' => $first]);

        return $this->toMap();
    }
}
