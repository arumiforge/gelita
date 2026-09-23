<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;

/**
 * Pemeriksaan kelengkapan konten sebelum rilis (FITUR 12 langkah 8).
 *
 * Satu sumber aturan untuk panel `/admin/konten/verifikasi` dan
 * `php spark gelita:content:verify`. Temuan `error` membuat konten belum
 * layak dirilis; `warning` perlu diperiksa tetapi tidak menghalangi permainan.
 */
class ContentVerifier
{
    /**
     * @return list<array{level: string, scope: string, message: string}>
     */
    public function run(): array
    {
        $content  = service('contentRepository');
        $levels   = $content->levels();
        $findings = [];
        $answers  = [];

        if (count($levels) !== 3) {
            $findings[] = $this->finding('error', 'struktur', 'Jumlah wilayah aktif ' . count($levels) . ', seharusnya 3.');
        }

        foreach ($levels as $level) {
            $nodes = $content->nodesForLevel($level->id);
            $scope = 'wilayah ' . $level->code;

            if (count($nodes) !== 5) {
                $findings[] = $this->finding('error', $scope, 'Jumlah tantangan ' . count($nodes) . ', seharusnya 5.');
            }

            foreach (['map_media_id' => 'peta', 'background_media_id' => 'latar', 'badge_media_id' => 'lencana'] as $column => $label) {
                $findings = [...$findings, ...$this->mediaFindings($level->{$column}, $scope, "Media {$label} wilayah")];
            }

            $passageIds = [];

            foreach ($content->passagesForLevel($level->id) as $passage) {
                $passageIds[$passage->id] = true;
                $findings = [...$findings, ...$this->mediaFindings($passage->media_asset_id, $scope . ' · bacaan ' . $passage->id, 'Media bacaan')];
            }

            foreach ($nodes as $node) {
                $nodeScope = $scope . ' · node ' . $node->sequence;
                $bank      = $content->itemBank($node->id);
                $perRound  = $node->itemsPerRound();

                if (! in_array($node->engine_type, config('Gelita')->engineTypes, true)) {
                    $findings[] = $this->finding('error', $nodeScope, "engine_type '{$node->engine_type}' tidak dikenali.");
                }

                if (count($bank) < $perRound) {
                    $findings[] = $this->finding('error', $nodeScope, 'Bank soal ' . count($bank) . " butir, minimal {$perRound}.");
                }

                $distractors = count($node->distractors('id'));
                $needed      = (int) $node->config('distractor_count');

                if ($node->engine_type === 'rumpang' && $needed > 0 && $distractors < $needed) {
                    $findings[] = $this->finding('error', $nodeScope, "Pengecoh rumpang {$distractors}, minimal {$needed}.");
                }

                foreach (['background_media_id' => 'latar', 'scene_media_id' => 'adegan'] as $column => $label) {
                    $findings = [...$findings, ...$this->mediaFindings($node->{$column}, $nodeScope, "Media {$label} tantangan")];
                }

                foreach ($bank as $item) {
                    $itemScope = $nodeScope . ' · ' . $item->item_key;

                    if ($item->scorable && $item->answerKey() === []) {
                        $findings[] = $this->finding('error', $itemScope, 'Butir dinilai tetapi tanpa answer_key_json.');
                    }

                    if (in_array($item->interaction_type, ['single_choice', 'source_trust'], true)) {
                        $correct = 0;

                        foreach ($item->loadedOptions() as $option) {
                            $correct += $option->is_correct ? 1 : 0;
                        }

                        if ($correct !== 1) {
                            $findings[] = $this->finding('error', $itemScope, "Opsi benar {$correct}, seharusnya tepat 1.");
                        }
                    }

                    if (in_array($item->interaction_type, ['verdict_card', 'verdict_reason'], true)
                        && ! in_array((string) $item->verdict(), $node->verdictOptions(), true)) {
                        $findings[] = $this->finding('error', $itemScope, 'Kunci verdict di luar verdict_options node.');
                    }

                    if ($item->passage_id !== null && ! isset($passageIds[$item->passage_id])) {
                        $findings[] = $this->finding('error', $itemScope, 'passage_id berasal dari wilayah lain.');
                    }

                    if ($item->review_status === 'needs_verification') {
                        $findings[] = $this->finding('warning', $itemScope, 'Butir masih berstatus needs_verification.');
                    }

                    $findings = [...$findings, ...$this->mediaFindings($item->media_asset_id, $itemScope, 'Media butir')];

                    $signature = $this->answerSignature($item);

                    if ($signature !== null) {
                        if (isset($answers[$signature]) && $answers[$signature] !== $node->id) {
                            $findings[] = $this->finding(
                                'warning',
                                $itemScope,
                                'Jawaban kembar dengan butir di node lain — analisis butir akan menghitung konsep ganda.',
                            );
                        }

                        $answers[$signature] ??= $node->id;
                    }
                }
            }
        }

        return $findings;
    }

    /** @param list<array{level: string}> $findings */
    public static function errorCount(array $findings): int
    {
        return count(array_filter($findings, static fn (array $f): bool => $f['level'] === 'error'));
    }

    /**
     * Media yang dirujuk tetapi nonaktif/tidak terdaftar, atau berkasnya tidak
     * ada di disk. Peringatan, bukan galat: view punya tampilan pengganti.
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function mediaFindings(?int $mediaId, string $scope, string $label): array
    {
        if ($mediaId === null || $mediaId <= 0) {
            return [];
        }

        $path = service('contentRepository')->mediaMap()[$mediaId] ?? null;

        if ($path === null) {
            return [$this->finding('warning', $scope, "{$label} #{$mediaId} belum ada berkasnya (media nonaktif atau tidak terdaftar).")];
        }

        if (! is_file(FCPATH . ltrim($path, '/'))) {
            return [$this->finding('warning', $scope, "{$label} hilang: berkas {$path} belum diunggah.")];
        }

        return [];
    }

    /** @return array{level: string, scope: string, message: string} */
    private function finding(string $level, string $scope, string $message): array
    {
        return ['level' => $level, 'scope' => $scope, 'message' => $message];
    }

    private function answerSignature(ChallengeItem $item): ?string
    {
        $key = $item->answerKey();

        if ($key === []) {
            return null;
        }

        return $item->interaction_type . '|' . json_encode($key, JSON_UNESCAPED_UNICODE);
    }
}
