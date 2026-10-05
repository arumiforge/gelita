<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Models\ChallengeItemModel;

/**
 * Pemeriksaan kelengkapan konten sebelum rilis (FITUR 12 langkah 8).
 *
 * Satu sumber aturan untuk panel `/admin/konten/verifikasi` dan
 * `php spark gelita:content:verify`. Temuan `error` membuat konten belum
 * layak dirilis; `warning` perlu diperiksa tetapi tidak menghalangi permainan.
 *
 * Aturan kelas 5 per tingkat kesulitan wilayah (DifficultyRules) — ukuran
 * papan Susun Gambar, jumlah rumpang/pengecoh bacaan, tingkat Bloom — hanya
 * menghasilkan peringatan, karena server yang baru diperbarui belum tentu
 * sudah mengimpor workbook barunya (skrip deploy menolak membuka situs bila
 * ada galat).
 *
 * Perombakan kelas 5: 4 tantangan per wilayah (puzzle, rumpang, boleh,
 * cari), soal dikelompokkan per variasi (satu variasi acak per pengerjaan).
 */
class ContentVerifier
{
    /** Kunci config_json node yang tidak dibaca lagi sejak perombakan kelas 5. */
    public const LEGACY_NODE_KEYS = ['items_per_round', 'allow_retry', 'require_reason', 'shuffle_options', 'show_decoys', 'distractors'];

    /** Satu jenis soal per engine. */
    private const ENGINE_INTERACTION = [
        'puzzle'  => 'puzzle_arrange',
        'rumpang' => 'fill_blank_bank',
        'boleh'   => 'verdict_card',
        'cari'    => 'find_object',
    ];

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

            if (count($nodes) !== 4) {
                $findings[] = $this->finding('error', $scope, 'Ada ' . count($nodes) . ' tantangan aktif; seharusnya 4.');
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
                $groups    = ChallengeItemModel::groupByVariant($bank);

                if (! in_array($node->engine_type, config('Gelita')->engineTypes, true)) {
                    $findings[] = $this->finding('error', $nodeScope, "Jenis tantangan '{$node->engine_type}' tidak dikenali.");
                }

                if ($bank === []) {
                    $findings[] = $this->finding('error', $nodeScope, 'Belum ada soal aktif.');
                } elseif (count($groups) !== 3) {
                    $findings[] = $this->finding('warning', $nodeScope, 'Ada ' . count($groups) . ' variasi soal (' . implode(', ', array_keys($groups)) . '); aturan kelas 5 memakai 3 variasi (a, b, c) yang tampil acak.');
                }

                $legacy = array_values(array_intersect(self::LEGACY_NODE_KEYS, array_keys((array) ($node->config_json ?: []))));

                if ($legacy !== []) {
                    $findings[] = $this->finding('warning', $nodeScope, 'Pengaturan lama yang tidak dipakai lagi masih tersimpan: ' . implode(', ', $legacy) . '.');
                }

                foreach ($groups as $group => $items) {
                    $groupScope = $nodeScope . ' · variasi ' . $group;

                    if ($node->engine_type === 'puzzle') {
                        $findings = [...$findings, ...$this->puzzleFindings($node, $items, (string) $level->difficulty, $groupScope)];
                    }

                    if ($node->isReading()) {
                        $findings = [...$findings, ...$this->readingFindings($node, $items, (string) $level->difficulty, $groupScope, $group)];
                    }

                    $distractors = count($node->distractors('id', $group));
                    $needed      = (int) $node->config('distractor_count');

                    if ($node->engine_type === 'rumpang' && $needed > 0 && $distractors < $needed) {
                        $findings[] = $this->finding('error', $groupScope, "Baru ada {$distractors} kata pengecoh; perlu minimal {$needed}.");
                    }
                }

                foreach (['background_media_id' => 'latar', 'scene_media_id' => 'adegan'] as $column => $label) {
                    $findings = [...$findings, ...$this->mediaFindings($node->{$column}, $nodeScope, "Gambar {$label} tantangan")];
                }

                foreach ($bank as $item) {
                    $itemScope = $nodeScope . ' · soal ' . $item->item_key;

                    if (! in_array($item->interaction_type, config('Gelita')->interactionTypes, true)) {
                        $findings[] = $this->finding('error', $itemScope, "Jenis soal '{$item->interaction_type}' sudah tidak dipakai; ganti atau istirahatkan soal ini.");
                    } elseif (isset(self::ENGINE_INTERACTION[$node->engine_type]) && $item->interaction_type !== self::ENGINE_INTERACTION[$node->engine_type]) {
                        $findings[] = $this->finding('error', $itemScope, 'Jenis soal tidak cocok dengan jenis tantangannya.');
                    }

                    if (trim((string) $item->variant_group) === '') {
                        $findings[] = $this->finding('warning', $itemScope, 'Soal ini belum punya variasi; dianggap variasi a.');
                    }

                    if ($item->scorable && $item->answerKey() === []) {
                        $findings[] = $this->finding('error', $itemScope, 'Soal ini dinilai tetapi belum punya kunci jawaban.');
                    }

                    if ($item->interaction_type === 'verdict_card'
                        && ! in_array((string) $item->verdict(), $node->verdictOptions(), true)) {
                        $findings[] = $this->finding('error', $itemScope, 'Kunci jawabannya harus benar atau salah.');
                    }

                    if ($item->passage_id !== null && ! isset($passageIds[$item->passage_id])) {
                        $findings[] = $this->finding('error', $itemScope, 'Soal ini memakai teks bacaan dari wilayah lain.');
                    }

                    if ($item->review_status === 'needs_verification') {
                        $findings[] = $this->finding('warning', $itemScope, 'Fakta di soal ini masih ditandai "perlu dicek".');
                    }

                    $findings = [...$findings, ...$this->mediaFindings($item->media_asset_id, $itemScope, 'Gambar soal')];

                    // Kunci susun gambar dan benar/salah memang sama di banyak soal
                    $signature = $item->interaction_type === 'fill_blank_bank' ? $this->answerSignature($item) : null;

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

    /**
     * Susun Gambar satu variasi: tepat satu gambar (tampilan hanya merender
     * soal pertama) dan ukuran papan sesuai tingkat wilayahnya.
     *
     * @param list<ChallengeItem> $bank
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function puzzleFindings(ChallengeNode $node, array $bank, string $difficulty, string $scope): array
    {
        $findings = [];
        $rule     = DifficultyRules::puzzle($difficulty);
        $images   = array_filter($bank, static fn (ChallengeItem $item): bool => $item->interaction_type === 'puzzle_arrange');

        if (count($images) > 1) {
            $findings[] = $this->finding('error', $scope, 'Ada ' . count($images) . ' gambar dalam satu variasi; Susun Gambar hanya menampilkan satu gambar per pengerjaan.');
        }

        foreach ($images as $item) {
            $layout = $item->puzzleLayout($difficulty, $node->config('grid'));
            $order  = $item->answerKey('order');

            if (! is_array($order) || array_map('intval', $order) !== range(0, $layout['grid'] * $layout['grid'] - 1)) {
                $findings[] = $this->finding('error', $scope . ' · soal ' . $item->item_key, 'Kunci susun gambar tidak cocok dengan ukuran papannya; buat ulang soal ini.');
            }

            if ($layout !== $rule) {
                $findings[] = $this->finding(
                    'warning',
                    $scope . ' · soal ' . $item->item_key,
                    'Papan soal ini ' . DifficultyRules::layoutLabel($layout['grid'], $layout['shape']) . ', padahal tingkat wilayahnya memakai '
                    . DifficultyRules::layoutLabel($rule['grid'], $rule['shape']) . '. Buat soal baru (ukurannya otomatis) dan istirahatkan soal ini.',
                );
            }
        }

        return $findings;
    }

    /**
     * Isi Rumpang bacaan satu variasi: tepat satu ___ per potongan, jawaban unik, pengecoh
     * tidak sama dengan jawaban (galat), serta jumlah rumpang/pengecoh dan
     * tingkat Bloom sesuai aturan tingkat wilayah (peringatan).
     *
     * @param list<ChallengeItem> $bank
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function readingFindings(ChallengeNode $node, array $bank, string $difficulty, string $scope, string $group): array
    {
        $blanks = [];

        foreach ($bank as $item) {
            if ($item->interaction_type !== 'fill_blank_bank') {
                continue;
            }

            $blanks[] = [
                'key'       => (string) $item->item_key,
                'prompt_id' => (string) ($item->prompt_id ?? ''),
                'prompt_en' => (string) ($item->prompt_en ?? ''),
                'answer_id' => (string) ($item->answerKey('text_id') ?? ''),
                'answer_en' => (string) ($item->answerKey('text_en') ?? ''),
                'bloom'     => $item->bloom_level,
            ];
        }

        $distractors = array_map(
            static fn (string $id, string $en): array => ['id' => $id, 'en' => $en],
            $node->distractors('id', $group),
            array_pad($node->distractors('en', $group), count($node->distractors('id', $group)), ''),
        );

        $problems = ClozeReading::problems(
            $blanks,
            $distractors,
            (int) $node->config('distractor_count'),
            (bool) $node->config('use_word_bank'),
            $difficulty,
        );

        return [
            ...array_map(fn (string $message): array => $this->finding('error', $scope, $message), $problems['errors']),
            ...array_map(fn (string $message): array => $this->finding('warning', $scope, $message), $problems['warnings']),
        ];
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
