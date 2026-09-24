<?php

namespace App\Controllers\Game;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Adegan cerita bernarasi dari tabel `dialogues` (Jaka & Mbah Kedu):
 *
 * - `/dialog/{code}`  dialog pembuka wilayah (`level_open`), gerbang wajib
 *                     wilayah yang baru terbuka (dialogueGate());
 * - `/tuntas/{code}`  wilayah tuntas (`level_done`), boleh ditonton ulang;
 * - `/penutup`        penutup cerita (`ending`), setelah seluruh node tuntas.
 *
 * Ketiganya diputar game/narrator.js (kartu ketuk, pose, efek, audio).
 * Perpindahan slide dicatat client lewat `/api/events` (`dialogue_advanced`
 * dengan `context` = konteks dialognya).
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

        // Membuka gerbang halaman di dalam wilayah (lihat dialogueGate())
        $this->markDialogueShown($session, $level);

        $content = service('contentRepository');
        $intro   = $content->dialogues($level->id, 'region_intro');

        return view('game/dialogue', $this->hudData() + [
            'level'   => $level,
            'slides'  => $content->dialogues($level->id, 'level_open'),
            // Kartu bab: tagline = judul slide pertama "Kenali wilayah", sama dengan tirai wilayah
            'tagline' => $intro === [] ? '' : tr($intro[0], 'title', $session->resolvedLocale()),
        ]);
    }

    /**
     * `/tuntas/{code}` — "Serpihan {wilayah} kembali!" (`level_done`). Hanya
     * untuk wilayah yang sudah tuntas pada sesi ini; selain itu ke peta
     * wilayahnya (yang menolak wilayah terkunci dan menjaga dialog pembuka).
     * Slide akhir: Baca Pustaka {wilayah} + Lanjut ke wilayah berikutnya
     * (`entry`, tirai wilayah), atau Lanjut ke `/penutup` di wilayah terakhir.
     */
    public function done(string $code): string|RedirectResponse
    {
        $session = $this->session();
        $level   = $this->requireLevel($code);

        if ($this->regionStatus($session, $level) !== 'completed') {
            return redirect()->to(site_url('wilayah/' . $level->code));
        }

        $content = service('contentRepository');

        return view('game/region-done', $this->hudData() + [
            'level'        => $level,
            'slides'       => $content->dialogues($level->id, 'level_done'),
            'hasLibrary'   => $content->libraryPages($level->id) !== [],
            'nextRegion'   => $this->nextRegion($session, $level->sequence),
            'allCompleted' => $this->allNodesCompleted($session),
        ]);
    }

    /**
     * `/penutup` — penutup cerita sinematik (`ending`), seperti cerita
     * pembuka. Hanya setelah seluruh node tuntas; slide akhir ke Balai
     * Refleksi. Boleh ditonton ulang ("Tonton penutup" di peta).
     */
    public function ending(): string|RedirectResponse
    {
        if (! $this->allNodesCompleted($this->session())) {
            return redirect()->to(site_url('peta'));
        }

        return view('game/ending', $this->hudData() + [
            'slides' => service('contentRepository')->dialogues(null, 'ending'),
        ]);
    }
}
