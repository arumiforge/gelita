<?php

namespace App\Libraries;

use App\Entities\Level;

/**
 * Bentuk data netral untuk adapter chart admin (public/assets/js/admin/charts.js).
 *
 * Query analitik tetap di AnalyticsService; kelas ini hanya menata ulang
 * hasilnya ke bentuk yang dipahami renderChart(), sehingga query tidak pernah
 * terikat pada library chart (ECharts dapat diganti tanpa menyentuh query).
 * Dipakai dua jalur yang sama persis: API admin (`chart` pada respons JSON)
 * dan view yang menanam data chart tanpa endpoint.
 *
 * Bentuk:
 *   bar / line / stacked-bar  { labels: [], series: [{ name, values: [] }], max?, integer? }
 *   heatmap / matrix          { x: [], y: [], values: [[xi, yi, v]], max, labels?: [[xi, yi, teks]], links?: {"xi,yi": url} }
 *   scatter                   { points: [{ x, y, label }], x_name, y_name }
 *   gauge                     { value, max }
 */
final class ChartData
{
    /**
     * Skor rata-rata & tepat sejak awal per wilayah.
     *
     * @param array<int, array<string, mixed>> $rows AnalyticsService::levelBreakdown()
     *
     * @return array<string, mixed>
     */
    public static function levels(array $rows): array
    {
        $rows = array_values($rows);

        if (array_sum(array_map(static fn (array $r): int => (int) $r['completed_attempts'], $rows)) === 0) {
            return self::emptyBar();
        }

        return [
            'labels' => array_map(static fn (array $r): string => (string) $r['name'], $rows),
            'series' => [
                ['name' => 'Rata-rata skor', 'values' => array_map(static fn (array $r): float => round((float) $r['avg_score'], 1), $rows)],
                ['name' => 'Tepat sejak awal (%)', 'values' => array_map(static fn (array $r): float => round((float) $r['avg_first_pass'], 1), $rows)],
            ],
            'max' => 100,
        ];
    }

    /**
     * Heatmap kesulitan: baris = wilayah, kolom = urutan node.
     *
     * @param array<int, array<string, mixed>> $rows   AnalyticsService::nodeDifficulty()
     * @param list<Level>                      $levels
     *
     * @return array<string, mixed>
     */
    public static function nodeHeatmap(array $rows, array $levels): array
    {
        $maxSeq = 0;

        foreach ($rows as $row) {
            $maxSeq = max($maxSeq, (int) $row['sequence']);
        }

        $levelIndex = [];

        foreach (array_values($levels) as $i => $level) {
            $levelIndex[$level->id] = $i;
        }

        $values = [];
        $labels = [];
        $links  = [];
        $any    = false;

        foreach ($rows as $row) {
            $yi = $levelIndex[(int) $row['level_id']] ?? null;

            if ($yi === null) {
                continue;
            }

            $xi       = (int) $row['sequence'] - 1;
            $hasData  = (int) $row['attempts'] > 0;
            $any      = $any || $hasData;
            $values[] = [$xi, $yi, $hasData ? round((float) $row['difficulty_index'], 1) : null];
            $labels[] = [$xi, $yi, (string) $row['title'] . ' — ' . ($hasData ? (int) $row['attempts'] . ' percobaan' : 'belum ada percobaan')];

            $links[$xi . ',' . $yi] = site_url('admin/analitik/node/' . (int) $row['node_id']);
        }

        if (! $any) {
            return ['x' => [], 'y' => [], 'values' => [], 'max' => 100];
        }

        return [
            'x'      => array_map(static fn (int $n): string => 'Node ' . $n, $maxSeq > 0 ? range(1, $maxSeq) : []),
            'y'      => array_map(static fn (Level $l): string => $l->text('name', 'id'), array_values($levels)),
            'values' => $values,
            'labels' => $labels,
            'links'  => (object) $links,
            'max'    => 100,
            'low'    => 'mudah',
            'high'   => 'sulit',
        ];
    }

    /**
     * Rata-rata skor pretest → posttest dari pasangan yang kompatibel.
     *
     * @param array{pretest: float, posttest: float, delta: ?float, pairs: int, incompatible_pairs: int} $result
     *
     * @return array<string, mixed>
     */
    public static function prePost(array $result): array
    {
        if ((int) $result['pairs'] === 0) {
            return ['labels' => [], 'series' => []];
        }

        return [
            'labels' => ['Pretest', 'Posttest'],
            'series' => [
                ['name' => 'Rata-rata skor (' . (int) $result['pairs'] . ' pasangan)', 'values' => [(float) $result['pretest'], (float) $result['posttest']]],
            ],
            'max' => 100,
        ];
    }

    /**
     * Matriks penguasaan indikator × wilayah (+ kolom keseluruhan).
     *
     * @param array<string, array<string, mixed>>             $overall  AnalyticsService::indicatorMastery()
     * @param array<int, array<string, array<string, mixed>>> $perLevel level_id → indicatorMastery()
     * @param list<Level>                                     $levels
     *
     * @return array<string, mixed>
     */
    public static function indicatorMatrix(array $overall, array $perLevel, array $levels): array
    {
        if ($overall === []) {
            return ['x' => [], 'y' => [], 'values' => [], 'max' => 1];
        }

        $levels = array_values($levels);
        $x      = array_map(static fn (Level $l): string => $l->text('name', 'id'), $levels);
        $x[]    = 'Keseluruhan';
        $codes  = array_keys($overall);

        $values = [];
        $labels = [];

        foreach ($codes as $yi => $code) {
            $cells = array_map(static fn (Level $l) => $perLevel[$l->id][$code] ?? null, $levels);
            $cells[] = $overall[$code];

            foreach ($cells as $xi => $cell) {
                $evidence = (int) ($cell['evidence_count'] ?? 0);
                $values[] = [$xi, $yi, $evidence > 0 ? round((float) $cell['mastery_ratio'], 3) : null];
                $labels[] = [$xi, $yi, $evidence > 0
                    ? round((float) $cell['mastery_ratio'] * 100) . '% · ' . (int) $cell['correct_count'] . '/' . $evidence . ' bukti'
                    : '0 bukti'];
            }
        }

        return [
            'x'      => $x,
            'y'      => array_map('strval', $codes),
            'values' => $values,
            'labels' => $labels,
            'max'    => 1,
            'low'    => 'rendah',
            'high'   => 'tinggi',
            'ratio'  => true,
        ];
    }

    /**
     * Sebaran sederhana: label → jumlah.
     *
     * @param array<int|string, int|float> $counts
     *
     * @return array<string, mixed>
     */
    public static function distribution(array $counts, string $name, string $suffix = ''): array
    {
        if ($counts === [] || (float) array_sum($counts) === 0.0) {
            return self::emptyBar();
        }

        return [
            'labels'  => array_map(static fn ($key): string => $key . $suffix, array_keys($counts)),
            'series'  => [['name' => $name, 'values' => array_values($counts)]],
            'integer' => true, // jumlah orang/percobaan: sumbu tanpa pecahan
        ];
    }

    /**
     * Batang dari daftar label → nilai (mis. skor per wilayah satu peserta).
     *
     * @param list<array{label: string, value: float|int|null}> $rows
     *
     * @return array<string, mixed>
     */
    public static function bars(array $rows, string $name, ?float $max = null): array
    {
        if ($rows === [] || array_filter($rows, static fn (array $r): bool => $r['value'] !== null) === []) {
            return self::emptyBar();
        }

        $chart = [
            'labels' => array_map(static fn (array $r): string => $r['label'], $rows),
            'series' => [['name' => $name, 'values' => array_map(static fn (array $r) => $r['value'] === null ? null : round((float) $r['value'], 3), $rows)]],
        ];

        if ($max !== null) {
            $chart['max'] = $max;
        }

        return $chart;
    }

    /**
     * @param list<array{x: float|int, y: float|int, label?: string}> $points
     *
     * @return array<string, mixed>
     */
    public static function scatter(array $points, string $xName, string $yName): array
    {
        return ['points' => array_values($points), 'x_name' => $xName, 'y_name' => $yName];
    }

    /** @return array{labels: list<string>, series: list<array<string, mixed>>} */
    private static function emptyBar(): array
    {
        return ['labels' => [], 'series' => []];
    }
}
