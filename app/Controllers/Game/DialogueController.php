<?php

namespace App\Controllers\Game;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Adegan cerita bernarasi dari tabel `dialogues` (Jaka & Mbah Kedu):
 *
 * - `/dialog/{code}`  dialog pembuka wilayah (`level_open`), gerbang wajib
 *                     wilayah yang baru terbuka (dialogueGate());
 * - `/penghubung/{code}` the bridge story before the fifth challenge
 *                     (`scenario_open`, part 6), gate of scenarioGate();
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
        // Cerita pembuka tetap wajib bila URL wilayah diketik langsung
        if ($gate = $this->introGate()) {
            return $gate;
        }

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
     * `/penghubung/{code}` — the bridge story *Kabut di Layar* (`scenario_open`,
     * 32 §7.2): the same narrated scene as `/dialog/{code}`, with the region's
     * `dialog-` music; its last slide leads to the mission brief of the
     * Skenario Digital challenge. Marks the gate as passed, and may be watched
     * again from the region map.
     */
    public function bridge(string $code): string|RedirectResponse
    {
        if ($gate = $this->introGate()) {
            return $gate;
        }

        $session = $this->session();
        $level   = $this->requireLevel($code);

        if (! $this->levelUnlocked($session, $level->sequence)) {
            return redirect()->to(site_url('peta'))->with('error', lang('Game.levelLocked'));
        }

        if ($gate = $this->dialogueGate($session, $level)) {
            return $gate;
        }

        $node   = null;
        $slides = service('contentRepository')->dialogues($level->id, 'scenario_open');

        foreach (service('contentRepository')->nodesForLevel($level->id) as $candidate) {
            if ($candidate->engine_type === 'skenario') {
                $node = $candidate;
            }
        }

        if ($node === null || $slides === [] || ! $this->nodeUnlocked($session, $node)) {
            return redirect()->to(site_url('wilayah/' . $level->code));
        }

        $this->markScenarioShown($session, $level);

        return view('game/bridge', $this->hudData() + [
            'level'    => $level,
            'node'     => $node,
            'slides'   => $slides,
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
