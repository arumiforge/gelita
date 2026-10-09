<?php

namespace App\Libraries;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Models\ChallengeItemModel;
use App\Models\MediaAssetModel;

/**
 * Pemeriksaan kelengkapan konten sebelum rilis (FITUR 12 langkah 8).
 *
 * Satu sumber aturan untuk panel `/admin/konten/verifikasi`,
 * `php spark gelita:content:verify`, dan langkah verifikasi `gelita:reset`.
 * Temuan `error` membuat konten belum layak dirilis; `warning` perlu
 * diperiksa tetapi tidak menghalangi permainan.
 *
 * Aturan bank soal kelas 5 (Dok 2 §7.5) dibaca dari App\Libraries\BankRules
 * — aturan yang sama dengan skrip penyusun workbook dan importer: 4
 * tantangan berurutan per wilayah, 3 variasi, jumlah rumpang/pernyataan/benda
 * per tingkat, bacaan, Bloom, keterampilan, posisi benda Cari Objek.
 *
 * Gambar contoh (mockup, `media_assets.is_placeholder = 1`) yang masih
 * dipakai konten tantangan: peringatan, kecuali mode siap penelitian
 * (`--siap-penelitian`), yang menjadikannya galat karena mockup menuliskan
 * nama benda (Dok 2 §10.3).
 */
class ContentVerifier
{
    /**
     * @param bool $researchReady mode siap penelitian: gambar contoh = galat
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    public function run(bool $researchReady = false): array
    {
        $content  = service('contentRepository');
        $state    = new BankState();
        $levels   = $content->levels();
        $findings = [];
        $answers  = [];

        if (count($levels) !== 3) {
            $findings[] = $this->finding('error', 'Keseluruhan', 'Ada ' . count($levels) . ' wilayah aktif; seharusnya 3.');
        }

        $normal  = $state->nodes();
        $regions = $state->regions($normal);
        $keys    = [];

        foreach ($levels as $level) {
            $nodes = $content->nodesForLevel($level->id);
            $scope = 'Wilayah ' . $level->text('name', 'id');

            foreach (BankRules::region($scope, $regions[(string) $level->code] ?? []) as $problem) {
                $findings[] = $this->finding($problem['level'], $scope, $problem['message']);
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
                $ref       = node_ref((string) $level->code, (int) $node->sequence);
                $bank      = $content->itemBank($node->id);

                foreach (BankRules::node($normal[$ref] ?? ['ref' => $ref, 'engine' => $node->engine_type, 'variants' => []], $state->passages()) as $problem) {
                    $findings[] = $this->finding($problem['level'], $this->scope($nodeScope, $problem), $problem['message']);
                }

                if ($node->engine_type === 'puzzle') {
                    $findings = [...$findings, ...$this->puzzleFindings($node, $bank, (string) $level->difficulty, $nodeScope)];
                }

                foreach (['background_media_id' => 'latar', 'scene_media_id' => 'adegan'] as $column => $label) {
                    $findings = [...$findings, ...$this->mediaFindings($node->{$column}, $nodeScope, "Gambar {$label} tantangan", $column === 'scene_media_id')];
                }

                foreach ($bank as $item) {
                    $itemScope = $nodeScope . ' · variasi ' . (trim((string) $item->variant_group) ?: 'a') . ' · soal ' . $item->item_key;
                    $keys[]    = (string) $item->item_key;

                    if (trim((string) $item->variant_group) === '') {
                        $findings[] = $this->finding('warning', $itemScope, 'Soal ini belum punya variasi; dianggap variasi a.');
                    }

                    if ($item->scorable && $item->answerKey() === []) {
                        $findings[] = $this->finding('error', $itemScope, 'Soal ini dinilai tetapi belum punya kunci jawaban.');
                    }

                    if ($item->passage_id !== null && ! isset($passageIds[$item->passage_id])) {
                        $findings[] = $this->finding('error', $itemScope, 'Soal ini memakai teks bacaan dari wilayah lain.');
                    }

                    if ($item->review_status === 'needs_verification') {
                        $findings[] = $this->finding('warning', $itemScope, 'Fakta di soal ini masih ditandai "perlu dicek".');
                    }

                    $findings = [...$findings, ...$this->mediaFindings($item->media_asset_id, $itemScope, 'Gambar soal', true)];

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

        foreach (BankRules::duplicateKeys($keys) as $key) {
            $findings[] = $this->finding('error', 'Keseluruhan', "Kode soal {$key} dipakai lebih dari satu soal aktif.");
        }

        $placeholder = $this->placeholderFinding($researchReady);

        if ($placeholder !== null) {
            $findings[] = $placeholder;
        }

        return $findings;
    }

    /** @param list<array{level: string}> $findings */
    public static function errorCount(array $findings): int
    {
        return count(array_filter($findings, static fn (array $f): bool => $f['level'] === 'error'));
    }

    /**
     * Gambar contoh yang masih dipakai konten tantangan (adegan, latar, gambar
     * soal, gambar bacaan).
     *
     * @return list<array<string, mixed>> baris media_assets, urut asset_key
     */
    public function placeholdersInUse(): array
    {
        $db   = db_connect();
        $used = [];

        foreach ([
            'challenge_nodes'  => ['scene_media_id', 'background_media_id'],
            'challenge_items'  => ['media_asset_id'],
            'reading_passages' => ['media_asset_id'],
        ] as $table => $columns) {
            foreach ($columns as $column) {
                foreach ($db->table($table)->select($column)->where('is_active', 1)->where($column . ' IS NOT NULL', null, false)->get()->getResultArray() as $row) {
                    $used[(int) $row[$column]] = true;
                }
            }
        }

        return array_values(array_filter(
            model(MediaAssetModel::class)->placeholders(),
            static fn (array $row): bool => isset($used[(int) $row['id']]) && (int) $row['is_active'] === 1,
        ));
    }

    /** @return array{level: string, scope: string, message: string}|null */
    private function placeholderFinding(bool $researchReady): ?array
    {
        $rows = $this->placeholdersInUse();

        if ($rows === []) {
            return null;
        }

        $keys = array_map(static fn (array $row): string => (string) $row['asset_key'], $rows);
        $list = implode(', ', array_slice($keys, 0, 5)) . (count($keys) > 5 ? ', …' : '');

        return $researchReady
            ? $this->finding('error', 'Gambar tantangan', count($rows) . " gambar masih gambar contoh (mockup): {$list}. Gambar contoh menuliskan nama benda, jadi belum layak untuk pengambilan data. Unggah gambar asli di menu Gambar & mockup.")
            : $this->finding('warning', 'Gambar tantangan', count($rows) . " gambar masih gambar contoh (mockup): {$list}. Ganti dengan gambar asli sebelum pengambilan data penelitian.");
    }

    /**
     * Susun Gambar: kunci sesuai ukuran papan, dan ukuran papan sesuai tingkat
     * wilayahnya (jumlah gambar per variasi diperiksa BankRules).
     *
     * @param list<ChallengeItem> $bank
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function puzzleFindings(ChallengeNode $node, array $bank, string $difficulty, string $scope): array
    {
        $findings = [];
        $rule     = DifficultyRules::puzzle($difficulty);

        foreach ($bank as $item) {
            if ($item->interaction_type !== 'puzzle_arrange') {
                continue;
            }

            $layout = $item->puzzleLayout($difficulty);
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
     * Media yang dirujuk tetapi nonaktif/tidak terdaftar, atau berkasnya tidak
     * ada di disk. Peringatan, bukan galat: view punya tampilan pengganti.
     * `$mockupSlot` = slot yang diisi `gelita:mockups` (adegan dan gambar
     * soal, MockupGenerator); slot lain hanya dapat diunggah di panel.
     *
     * @return list<array{level: string, scope: string, message: string}>
     */
    private function mediaFindings(?int $mediaId, string $scope, string $label, bool $mockupSlot = false): array
    {
        if ($mediaId === null || $mediaId <= 0) {
            return [];
        }

        $path = service('contentRepository')->mediaMap()[$mediaId] ?? null;

        if ($path === null) {
            return [$this->finding('warning', $scope, "{$label} belum diunggah; permainan memakai tampilan pengganti. " . ($mockupSlot
                ? 'Jalankan php spark gelita:mockups untuk memasang gambar contoh, atau unggah di menu Gambar & mockup.'
                : 'Unggah di menu Gambar & mockup.'))];
        }

        if (! is_file(FCPATH . ltrim($path, '/'))) {
            return [$this->finding('warning', $scope, "{$label}: berkasnya hilang dari server ({$path}). Unggah ulang di menu Gambar & mockup.")];
        }

        return [];
    }

    /** "Wilayah … · tantangan 2 (…) · variasi a · soal tmg-2-a01" */
    private function scope(string $nodeScope, array $problem): string
    {
        return $nodeScope
            . (($problem['variant'] ?? null) !== null ? ' · variasi ' . $problem['variant'] : '')
            . (($problem['item'] ?? null) !== null ? ' · soal ' . $problem['item'] : '');
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
