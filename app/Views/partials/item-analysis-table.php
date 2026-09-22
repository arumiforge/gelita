<?php
/**
 * Tabel analisis butir: kesukaran p dan daya beda D beserta tafsirnya.
 * Baris merah bila benar < 50%, hijau bila > 85%. Tafsir selalu tertulis,
 * warna hanya penegas.
 *
 * @var list<array<string, mixed>> $rows          AnalyticsController::describeItems()
 * @var bool|null                  $showLocation  tampilkan kolom wilayah/node
 */
$pLabel = static fn (float $p): array => $p < 0.30 ? ['sukar', 'is-bad'] : ($p <= 0.70 ? ['sedang', 'is-warn'] : ['mudah', 'is-ok']);
$dLabel = static function (?float $d): array {
    if ($d === null) {
        return ['belum cukup data', 'is-muted'];
    }

    return match (true) {
        $d < 0     => ['buruk', 'is-bad'],
        $d < 0.20  => ['lemah', 'is-warn'],
        $d < 0.40  => ['cukup', 'is-open'],
        default    => ['baik', 'is-ok'],
    };
};
$columns = [
    'item_key' => ['label' => 'Soal', 'render' => static fn (array $r): string => '<code>' . esc($r['item_key']) . '</code>'
        . '<span class="cell-sub cell-clip" title="' . esc($r['prompt'], 'attr') . '">' . esc($r['prompt'] !== '' ? $r['prompt'] : '—') . '</span>'],
    'indicator'        => 'Indikator',
    'interaction_type' => ['label' => 'Jenis', 'format' => 'code'],
];
if (! empty($showLocation)) {
    $columns['level_name'] = ['label' => 'Wilayah', 'render' => static fn (array $r): string => esc($r['level_name'] ?? '—')
        . ($r['node_id'] ? '<span class="cell-sub"><a href="' . base_url('admin/analitik/node/' . $r['node_id']) . '">' . esc($r['node_label']) . '</a></span>' : '')];
}
$columns += [
    'answer_key'       => ['label' => 'Kunci', 'render' => static fn (array $r): string => '<span class="cell-clip">' . esc($r['answer_key'] ?? '—') . '</span>'],
    'appeared'         => ['label' => 'Muncul', 'format' => 'num'],
    'correct'          => ['label' => 'Benar', 'format' => 'num'],
    'p'                => ['label' => 'Kesukaran p', 'render' => static function (array $r) use ($pLabel): string {
        [$text, $class] = $pLabel((float) $r['p']);

        return '<span class="num">' . esc(fmt_num($r['p'], 2, 'id')) . '</span> <span class="badge ' . $class . '">' . $text . '</span>';
    }],
    'd'                => ['label' => 'Daya beda D', 'render' => static function (array $r) use ($dLabel): string {
        [$text, $class] = $dLabel($r['d'] === null ? null : (float) $r['d']);

        return '<span class="num">' . ($r['d'] === null ? '—' : esc(fmt_num($r['d'], 2, 'id'))) . '</span> <span class="badge ' . $class . '">' . $text . '</span>';
    }],
    'mean_duration_ms' => ['label' => 'Rata-rata detik', 'render' => static fn (array $r): string => '<span class="num">' . esc(fmt_num($r['mean_duration_ms'] / 1000, 1, 'id')) . '</span>'],
    'top_wrong'        => ['label' => 'Salah tersering', 'format' => 'json'],
];
?>
<?= component('admin-table', [
    'rows'         => $rows,
    'caption'      => 'Analisis butir soal',
    'emptyMessage' => 'Belum ada jawaban untuk dianalisis pada filter ini. Butir muncul setelah siswa menjawabnya.',
    'rowClass'     => static fn (array $r): string => (float) $r['p'] < 0.5 ? 'is-bad' : ((float) $r['p'] > 0.85 ? 'is-good' : ''),
    'columns'      => $columns,
]) ?>
