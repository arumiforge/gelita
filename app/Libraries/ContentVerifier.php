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
            $findings[] = $this->finding('error', 'Keseluruhan', 'Ada ' . count($levels) . ' wilayah aktif; seharusnya 3.');
        }

        foreach ($levels as $level) {
            $nodes = $content->nodesForLevel($level->id);
            $scope = 'Wilayah ' . $level->text('name', 'id');

            if (count($nodes) !== 5) {
                $findings[] = $this->finding('error', $scope, 'Ada ' . count($nodes) . ' tantangan aktif; seharusnya 5.');
            }

            foreach (['map_media_id' => 'peta', 'background_media_id' => 'latar', 'badge_media_id' => 'lencana'] as $column => $label) {
                $findings = [...$findings, ...$this->mediaFindings($level->{$column}, $scope, "Gambar {$label} wilayah")];
            }

            $passageIds = [];

            foreach ($content->passagesForLevel($level->id) as $passage) {
                $passageIds[$passage->id] = true;
                $findings = [...$findings, ...$this->mediaFindings($passage->media_asset_id, $scope . ' · bacaan ' . $passage->passage_key, 'Gambar bacaan')];
            }

            foreach ($nodes as $node) {
                $nodeScope = $scope . ' · tantangan ' . $node->sequence . ' (' . $node->text('title', 'id') . ')';
                $bank      = $content->itemBank($node->id);
                $perRound  = $node->itemsPerRound();

                if (! in_array($node->engine_type, config('Gelita')->engineTypes, true)) {
                    $findings[] = $this->finding('error', $nodeScope, "Jenis tantangan '{$node->engine_type}' tidak dikenali.");
                }

                if (count($bank) < $perRound) {
                    $findings[] = $this->finding('error', $nodeScope, 'Baru ada ' . count($bank) . " soal aktif; perlu minimal {$perRound} (jumlah soal tiap kali bermain).");
                }

                $distractors = count($node->distractors('id'));
                $needed      = (int) $node->config('distractor_count');

                if ($node->engine_type === 'rumpang' && $needed > 0 && $distractors < $needed) {
                    $findings[] = $this->finding('error', $nodeScope, "Baru ada {$distractors} kata pengecoh; perlu minimal {$needed}.");
                }

                foreach (['background_media_id' => 'latar', 'scene_media_id' => 'adegan'] as $column => $label) {
                    $findings = [...$findings, ...$this->mediaFindings($node->{$column}, $nodeScope, "Gambar {$label} tantangan")];
                }

                foreach ($bank as $item) {
                    $itemScope = $nodeScope . ' · soal ' . $item->item_key;

                    if ($item->scorable && $item->answerKey() === []) {
                        $findings[] = $this->finding('error', $itemScope, 'Soal ini dinilai tetapi belum punya kunci jawaban.');
                    }

                    if (in_array($item->interaction_type, ['single_choice', 'source_trust'], true)) {
                        $correct = 0;

                        foreach ($item->loadedOptions() as $option) {
                            $correct += $option->is_correct ? 1 : 0;
                        }

                        if ($correct !== 1) {
                            $findings[] = $this->finding('error', $itemScope, "Ada {$correct} pilihan yang ditandai benar; seharusnya tepat 1.");
                        }
                    }

                    if (in_array($item->interaction_type, ['verdict_card', 'verdict_reason'], true)
                        && ! in_array((string) $item->verdict(), $node->verdictOptions(), true)) {
                        $findings[] = $this->finding('error', $itemScope, 'Kunci jawabannya tidak termasuk tombol pilihan yang tampil di tantangan ini (benar/salah/pendapat).');
                    }

                    if ($item->passage_id !== null && ! isset($passageIds[$item->passage_id])) {
                        $findings[] = $this->finding('error', $itemScope, 'Soal ini memakai teks bacaan dari wilayah lain.');
                    }

                    if ($item->review_status === 'needs_verification') {
                        $findings[] = $this->finding('warning', $itemScope, 'Fakta di soal ini masih ditandai "perlu dicek".');
                    }

                    $findings = [...$findings, ...$this->mediaFindings($item->media_asset_id, $itemScope, 'Gambar soal')];

                    $signature = $this->answerSignature($item);

                    if ($signature !== null) {
                        if (isset($answers[$signature]) && $answers[$signature] !== $node->id) {
                            $findings[] = $this->finding(
                                'warning',
                                $itemScope,
                                'Jawabannya sama persis dengan soal di tantangan lain — hasil per soal bisa menghitung materi yang sama dua kali.',
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
            return [$this->finding('warning', $scope, "{$label} belum diunggah; permainan memakai tampilan pengganti.")];
        }

        if (! is_file(FCPATH . ltrim($path, '/'))) {
            return [$this->finding('warning', $scope, "{$label}: berkasnya hilang dari server ({$path}). Unggah ulang di menu Gambar & suara.")];
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
