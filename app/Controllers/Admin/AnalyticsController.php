<?php

namespace App\Controllers\Admin;

use App\Models\ChallengeAttemptModel;
use App\Models\ChallengeItemModel;
use App\Models\LearningIndicatorModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Analitik penelitian: level, node, butir, indikator, dan pretest–posttest.
 *
 * Semua method mengikuti pola yang sama: baca filter → tambahkan cakupan
 * sekolah → panggil AnalyticsService → render. Query di sini hanya
 * melengkapi label tampilan (judul butir, indikator, wilayah), bukan angka.
 */
class AnalyticsController extends BaseAdminController
{
    public function levels(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/analytics/levels', 'Analitik level', [
            'filters' => $filters,
            'rows'    => $this->analytics()->levelBreakdown($filters),
        ]);
    }

    public function nodes(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/analytics/nodes', 'Analitik tantangan', [
            'filters'    => $filters,
            'rows'       => $this->analytics()->nodeDifficulty($filters),
            'levels'     => service('contentRepository')->levels(),
            'indicators' => $this->nodeIndicators(),
        ]);
    }

    public function node(int $nodeId): string
    {
        $node = service('contentRepository')->node($nodeId);

        if ($node === null) {
            throw PageNotFoundException::forPageNotFound("Node {$nodeId} tidak ditemukan.");
        }

        $filters            = $this->scopedFilters();
        $filters['node_id'] = $nodeId;
        $analytics          = $this->analytics();

        return $this->panel('admin/analytics/node', 'Drilldown tantangan', [
            'filters'  => $filters,
            'node'     => $node,
            'level'    => service('contentRepository')->levelById($node->level_id),
            'rows'     => $analytics->nodeDifficulty($filters),
            'items'    => $this->describeItems($analytics->itemAnalysis($filters)),
            'attempts' => $this->nodeAttempts($nodeId, $filters),
        ]);
    }

    public function items(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/analytics/items', 'Analisis butir', [
            'filters' => $filters,
            'rows'    => $this->describeItems($this->analytics()->itemAnalysis($filters)),
        ]);
    }

    public function indicators(): string
    {
        $filters   = $this->scopedFilters();
        $analytics = $this->analytics();
        $levels    = service('contentRepository')->levels();
        $perLevel  = [];

        foreach ($levels as $level) {
            $perLevel[$level->id] = $analytics->indicatorMastery(['level_id' => $level->id] + $filters);
        }

        return $this->panel('admin/analytics/indicators', 'Penguasaan indikator', [
            'filters'  => $filters,
            'rows'     => $analytics->indicatorMastery($filters),
            'levels'   => $levels,
            'perLevel' => $perLevel,
        ]);
    }

    public function prePost(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/analytics/prepost', 'Pretest & posttest', [
            'filters' => $filters,
            'result'  => $this->analytics()->prePostComparison($filters),
        ]);
    }

    /**
     * Melengkapi hasil itemAnalysis() dengan label untuk dibaca guru:
     * pertanyaan, jenis interaksi, indikator, wilayah & node, dan kunci.
     * Kunci jawaban hanya tampil di panel staf, tidak pernah di area game.
     *
     * @param array<int, array<string, mixed>> $rows
     *
     * @return list<array<string, mixed>>
     */
    private function describeItems(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $items      = model(ChallengeItemModel::class)->whereIn('id', array_keys($rows))->findAll();
        $indicators = [];

        foreach (model(LearningIndicatorModel::class)->findAll() as $indicator) {
            $indicators[(int) $indicator['id']] = $indicator['code'];
        }

        $content = service('contentRepository');
        $byId    = [];

        foreach ($items as $item) {
            $byId[$item->id] = $item;
        }

        $out = [];

        foreach ($rows as $itemId => $row) {
            $item  = $byId[$itemId] ?? null;
            $node  = $item === null ? null : $content->node($item->challenge_node_id);
            $level = $node === null ? null : $content->levelById($node->level_id);

            $out[] = $row + [
                'prompt'           => $item?->text('prompt', 'id') ?? '',
                'interaction_type' => $item?->interaction_type,
                'indicator'        => $indicators[$item?->indicator_id ?? $node?->indicator_id ?? 0] ?? null,
                'level_name'       => $level?->text('name', 'id'),
                'node_id'          => $node?->id,
                'node_label'       => $node === null ? null : $node->sequence . '. ' . $node->text('title', 'id'),
                'answer_key'       => $item === null ? null : $this->keyLabel($item),
            ];
        }

        return $out;
    }

    /** Kunci jawaban dalam bentuk singkat yang dapat dibaca. */
    private function keyLabel(\App\Entities\ChallengeItem $item): string
    {
        return match ($item->interaction_type) {
            'single_choice', 'source_trust' => (string) ($item->correctOptionKey() ?? '—'),
            'verdict_card', 'verdict_reason' => (string) ($item->verdict() ?? '—'),
            'fill_blank_bank', 'fill_blank_free' => implode(' / ', $item->acceptedAnswers('id')) ?: '—',
            'find_object'  => $item->isDecoy() ? 'jebakan' : 'target',
            default        => json_encode($item->answerKey(), JSON_UNESCAPED_UNICODE) ?: '—',
        };
    }

    /** @return array<int, ?string> id node → kode indikator */
    private function nodeIndicators(): array
    {
        $codes = [];

        foreach (model(LearningIndicatorModel::class)->findAll() as $indicator) {
            $codes[(int) $indicator['id']] = $indicator['code'];
        }

        $out     = [];
        $content = service('contentRepository');

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $out[$node->id] = $codes[$node->indicator_id ?? 0] ?? null;
            }
        }

        return $out;
    }

    /**
     * Attempt selesai pada satu node (skor, ketepatan, durasi) untuk sebaran
     * skor dan scatter durasi vs ketepatan. Dibatasi 500 baris terbaru.
     *
     * @return list<array<string, mixed>>
     */
    private function nodeAttempts(int $nodeId, array $filters): array
    {
        return model(ChallengeAttemptModel::class)->filteredBuilder($filters)
            ->select('challenge_attempts.id, challenge_attempts.score, challenge_attempts.stars', false)
            ->select('challenge_attempts.first_pass_accuracy, challenge_attempts.duration_ms, challenge_attempts.session_id', false)
            ->where('challenge_attempts.challenge_node_id', $nodeId)
            ->where('challenge_attempts.status', 'completed')
            ->orderBy('challenge_attempts.id', 'DESC')
            ->limit(500)
            ->get()
            ->getResultArray();
    }
}
