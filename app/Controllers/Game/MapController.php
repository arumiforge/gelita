<?php

namespace App\Controllers\Game;

use CodeIgniter\HTTP\RedirectResponse;

/**
 * Peta Kedu (tiga wilayah) dan peta wilayah (lima pos tantangan).
 * Status buka/kunci disusun GameProgress dari `session_progress`.
 * Peta Kedu baru terbuka setelah cerita pembuka ditonton (introGate()).
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
        $levels  = $this->levelOverview($session);
        $story   = service('contentRepository')->dialogues(null, 'map_intro');
        $curtain = session()->getFlashdata('curtain') === 'map';

        return view('game/map-kedu', $this->hudData() + [
            'levels'        => $levels,
            'unlockMode'    => $this->unlockMode($session),
            'story'         => $story,
            'curtain'       => $curtain,
            'curtainAssets' => $curtain ? $this->mapCurtainAssets($levels, $story, $session->resolvedLocale()) : [],
        ]);
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
        $urls = [media_key_src('map.kedu'), media_key_src('bg.map')];

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
            'levelScore' => service('scoringService')->levelScore($session->id, $level->id),
            'nodes'      => $this->nodeOverview($session, $level->id),
            'hasLibrary' => service('contentRepository')->libraryPages($level->id) !== [],
        ]);
    }
}
