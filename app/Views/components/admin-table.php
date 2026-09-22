<?php
/**
 * Tabel data panel + kondisi kosong yang menjelaskan.
 *
 * `$columns` berisi kunci kolom → label, atau kunci → definisi:
 *   ['label' => 'Skor', 'format' => 'num', 'decimals' => 2]
 * Format: text (bawaan) | num | pct (0–100) | ratio (0–1 → %) | date | datetime
 *         | ms (durasi) | bool | badge | code | json
 * Kolom `render` menerima closure fn(array $row): string yang mengembalikan
 * HTML yang SUDAH di-escape pemanggil (dipakai untuk tautan & tombol aksi).
 *
 * `table-sort` (tahap 6) menambah pengurutan client untuk tabel ≤ 500 baris.
 *
 * @var array<string, string|array<string, mixed>> $columns
 * @var iterable<array<string, mixed>|object>      $rows
 * @var string|null                                $emptyMessage
 * @var string|null                                $caption      judul tabel untuk pembaca layar
 * @var Closure|null                               $rowClass     fn(array $row): string
 * @var string|null                                $id
 */
$toArray = static function ($row): array {
    if (is_array($row)) {
        return $row;
    }

    if (is_object($row) && method_exists($row, 'toArray')) {
        return $row->toArray();
    }

    return (array) $row;
};

$format = static function ($value, array $def): string {
    if ($value === null || $value === '') {
        return '—';
    }

    return match ($def['format'] ?? 'text') {
        'num'      => fmt_num($value, (int) ($def['decimals'] ?? 0), 'id'),
        'pct'      => fmt_pct($value, false, (int) ($def['decimals'] ?? 1)),
        'ratio'    => fmt_pct($value, true, (int) ($def['decimals'] ?? 1)),
        'date'     => fmt_date($value, false, 'id'),
        'datetime' => fmt_date($value, true, 'id'),
        'ms'       => ms_to_human((int) $value),
        'bool'     => $value ? 'ya' : 'tidak',
        'badge'    => '<span class="badge is-' . esc((string) $value, 'attr') . '">' . esc((string) $value) . '</span>',
        'code'     => '<code>' . esc((string) $value) . '</code>',
        'json'     => '<code class="json-inline">' . esc(is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) . '</code>',
        default    => esc(is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value),
    };
};

$defs = [];
foreach ($columns as $key => $def) {
    $defs[$key] = is_array($def) ? $def : ['label' => $def];
}

$prepared = [];
foreach ($rows ?? [] as $row) {
    $prepared[] = $toArray($row);
}
?>
<?php if ($prepared === []): ?>
  <div class="empty-state">
    <?= icon('info') ?>
    <p><?= esc($emptyMessage ?? lang('Admin.emptyDefault')) ?></p>
  </div>
<?php else: ?>
  <div class="table-wrap"<?= isset($id) ? ' id="' . esc($id, 'attr') . '"' : '' ?>>
    <table class="data-table" data-sortable="<?= count($prepared) <= 500 ? 'client' : 'server' ?>">
      <?php if (! empty($caption)): ?>
        <caption class="visually-hidden"><?= esc($caption) ?></caption>
      <?php endif ?>
      <thead>
        <tr>
          <?php foreach ($defs as $def): ?>
            <th scope="col" class="<?= in_array($def['format'] ?? '', ['num', 'pct', 'ratio', 'ms'], true) ? 'is-num' : '' ?><?= isset($def['render']) && ($def['label'] ?? '') === '' ? ' is-actions' : '' ?>">
              <?= esc($def['label'] ?? '') ?>
            </th>
          <?php endforeach ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($prepared as $row): ?>
          <tr class="<?= isset($rowClass) && $rowClass instanceof Closure ? esc($rowClass($row), 'attr') : '' ?>">
            <?php foreach ($defs as $key => $def): ?>
              <?php $numeric = in_array($def['format'] ?? '', ['num', 'pct', 'ratio', 'ms'], true); ?>
              <td class="<?= $numeric ? 'is-num' : '' ?><?= isset($def['render']) && ($def['label'] ?? '') === '' ? ' is-actions' : '' ?>">
                <?= isset($def['render']) && $def['render'] instanceof Closure ? $def['render']($row) : $format($row[$key] ?? null, $def) ?>
              </td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
<?php endif ?>
