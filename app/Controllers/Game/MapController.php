<?php

namespace App\Controllers\Game;

use App\Models\GameEventLogModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Peta Kedu (tiga wilayah) dan peta wilayah (empat pos tantangan).
 * Status buka/kunci disusun GameProgress dari `participant_progress` (dan rencana pertemuan dalam mode penelitian).
 * Peta Kedu baru terbuka setelah cerita pembuka ditonton (introGate()).
 * Tahap 3: overlay "Kenali wilayah" (region_intro) dan tirai wilayah.
 */
class MapController extends BaseGameController
{
    /**
     * Flash `curtain=map` (BaseGameController::toMap(): dari gerbang atau
     * akhir cerita pembuka) → tirai "Membuka Peta Kedu" sambil memuat aset
     * peta, lalu narasi `map_intro` diputar otomatis setelah ketukannya.
     * Kunjungan lain: narasi tampil sebagai teks dengan tombol ▶.
     */
    public function kedu(): string|RedirectResponse
    {
        if ($gate = $this->introGate()) {
            return $gate;
        }

        $session = $this->session();
        $locale  = $session->resolvedLocale();
        $levels  = $this->levelOverview($session);
        $curtain = session()->getFlashdata('curtain') === 'map';

        // Once every challenge is done the Mist of Forgetting is gone, so Jaka says the
        // `map_done` lines instead (owner, 21 October 2026); a server without them keeps `map_intro`
        $hud          = $this->hudData();
        $progress     = $hud['progress'] ?? [];
        $storyContext = 'map_intro';
        $story        = [];

        if (($progress['shards_total'] ?? 0) > 0 && ($progress['completed_nodes'] ?? 0) >= $progress['shards_total']) {
            $story        = service('contentRepository')->dialogues(null, 'map_done');
            $storyContext = $story !== [] ? 'map_done' : 'map_intro';
        }

        if ($story === []) {
            $story = service('contentRepository')->dialogues(null, 'map_intro');
        }

        // Tirai wilayah (tpl-curtain-region) pada pin dan kartu wilayah
        foreach ($levels as $index => $row) {
            $levels[$index]['curtain'] = $this->regionCurtain($row, $locale);
        }

        return view('game/map-kedu', $hud + [
            'levels'        => $levels,
            'unlockMode'    => $this->unlockMode($session),
            'story'         => $story,
            'storyContext'  => $storyContext,
            'curtain'       => $curtain,
            'curtainAssets' => $curtain ? $this->mapCurtainAssets($levels, $story, $locale) : [],
            'regionIntros'  => $this->regionIntros($levels, $hud['researchMode']),
            'heard'         => $this->heardRegionIntros(),
            // Part 7 (33 §7.5, K9): region codes whose Kenali waits for the region to be finished
            'kenaliLocked'  => $hud['researchMode'] ? array_values(array_map(static fn (array $l): string => (string) $l['code'], array_filter($levels, static fn (array $l): bool => $l['status'] !== 'completed'))) : [],
        ]);
    }

    /**
     * Narasi "Kenali wilayah" (dialogues `region_intro`, Mbah Kedu) per kode
     * wilayah. Wilayah terkunci ikut: boleh dikenali sebelum dimasuki.
     *
     * Part 7 (33 §7.5, K9): in research mode only finished regions get their
     * slides; the others get an empty list, so neither text nor audio of
     * Kenali is in the page source.
     *
     * @param list<array<string, mixed>> $levels
     *
     * @return array<string, list<array<string, mixed>>>
     */
    private function regionIntros(array $levels, bool $researchMode = false): array
    {
        $intros = [];

        foreach ($levels as $level) {
            $intros[$level['code']] = $researchMode && $level['status'] !== 'completed'
                ? []
                : service('contentRepository')->dialogues((int) $level['id'], 'region_intro');
        }

        return $intros;
    }

    /**
     * level_id wilayah yang narasi Kenali-nya sudah didengar peserta ini sampai
     * slide terakhir (dihitung dari `dialogue_completed`); lencana "Belum
     * didengar" berdenyut pada wilayah lainnya.
     *
     * @return list<int>
     */
    protected function heardRegionIntros(): array
    {
        return model(GameEventLogModel::class)->heardRegionIntros($this->participant()->id);
    }

    /**
     * Aset yang dimuat tirai peta: gambar peta dan latarnya, frame tokoh
     * narasi peta (pose, cadangan idle), latar wilayah, dan audio narasi
     * peta bahasa aktif. Yang belum diunggah/disetujui tidak ikut.
     *
     * @param list<array<string, mixed>> $levels
     * @param list<array<string, mixed>> $story
     *
     * @return list<string>
     */
    private function mapCurtainAssets(array $levels, array $story, string $locale): array
    {
        // The map picture the page shows: the diorama (page redesign 2), else the old map
        $urls = [media_key_src('map.kedu-diorama') ?? media_key_src('map.kedu'), media_key_src('bg.map')];

        foreach ($story as $line) {
            $slug = ($line['character_code'] ?? '') === 'mbah_kedu' ? 'kedu' : (string) ($line['character_code'] ?? '');

            if ($slug !== '' && $slug !== 'narator') {
                $pose   = (string) ($line['pose'] ?? '') ?: 'idle';
                $urls[] = media_key_src("char.{$slug}.{$pose}.1") ?? media_key_src("char.{$slug}.idle.1");
            }

            $urls[] = audio_src((int) ($line[$locale === 'en' ? 'audio_en_asset_id' : 'audio_id_asset_id'] ?? 0) ?: null);
        }

        foreach ($levels as $level) {
            $urls[] = $level['background'] ?? null;
        }

        return array_values(array_unique(array_filter($urls, static fn ($url): bool => is_string($url) && $url !== '')));
    }

    public function level(string $code): string|RedirectResponse
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

        return view('game/map-level', $this->hudData() + [
            'level'      => $level,
            'levelSummary' => service('scoringService')->levelSummary($session->id, $level->id),
            'nodes'      => $this->nodeOverview($session, $level->id),
            'hasLibrary' => service('contentRepository')->libraryPages($level->id) !== [],
            'library'    => $this->libraryAccess($session, $level),
            // Part 6 (32 §7.2): the bridge story may be watched again once it was opened
            'bridgeSeen' => $this->bridgeSeen($session, $level),
        ]);
    }

    /** The region's bridge story was shown in this login, or its Skenario Digital challenge already has an attempt. */
    private function bridgeSeen(\App\Entities\GameSession $session, \App\Entities\Level $level): bool
    {
        if (service('contentRepository')->dialogues($level->id, 'scenario_open') === []) {
            return false;
        }

        foreach (service('contentRepository')->nodesForLevel($level->id) as $node) {
            if ($node->engine_type === 'skenario') {
                return $this->scenarioShown($session, $level) || model(\App\Models\ChallengeAttemptModel::class)->hasAttemptInStudy($session, $node->id);
            }
        }

        return false;
    }
}
