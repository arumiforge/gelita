<?php

namespace App\Controllers\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Analitik penelitian: level, node, butir, indikator, dan pretest–posttest.
 *
 * Semua method mengikuti pola yang sama: baca filter → tambahkan cakupan
 * sekolah → panggil AnalyticsService → render. Tidak ada query di sini.
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
            'filters' => $filters,
            'rows'    => $this->analytics()->nodeDifficulty($filters),
        ]);
    }

    public function node(int $nodeId): string
    {
        $node = service('contentRepository')->node($nodeId);

        if ($node === null) {
            throw PageNotFoundException::forPageNotFound("Node {$nodeId} tidak ditemukan.");
        }

        $filters               = $this->scopedFilters();
        $filters['node_id']    = $nodeId;
        $analytics             = $this->analytics();

        return $this->panel('admin/analytics/node', 'Drilldown tantangan', [
            'filters' => $filters,
            'node'    => $node,
            'level'   => service('contentRepository')->levelById($node->level_id),
            'rows'    => $analytics->nodeDifficulty($filters),
            'items'   => $analytics->itemAnalysis($filters),
        ]);
    }

    public function items(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/analytics/items', 'Analisis butir', [
            'filters' => $filters,
            'rows'    => $this->analytics()->itemAnalysis($filters),
        ]);
    }

    public function indicators(): string
    {
        $filters = $this->scopedFilters();

        return $this->panel('admin/analytics/indicators', 'Penguasaan indikator', [
            'filters' => $filters,
            'rows'    => $this->analytics()->indicatorMastery($filters),
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
}
