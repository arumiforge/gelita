<?php
/**
 * Tabel data panel dengan kosong-state.
 *
 * @var array<string, string>            $columns kunci kolom => label
 * @var iterable<array<string, mixed>>   $rows
 */
$value = static function ($row, string $key) {
    $data = is_object($row) ? (method_exists($row, 'toArray') ? $row->toArray() : (array) $row) : (array) $row;
    $item = $data[$key] ?? null;

    if (is_bool($item)) {
        return $item ? 'ya' : 'tidak';
    }

    return is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : (string) ($item ?? '—');
};
?>
<table class="data-table">
  <thead>
    <tr><?php foreach ($columns as $label): ?><th scope="col"><?= esc($label) ?></th><?php endforeach ?></tr>
  </thead>
  <tbody>
    <?php $empty = true; ?>
    <?php foreach ($rows as $row): $empty = false; ?>
      <tr>
        <?php foreach (array_keys($columns) as $key): ?>
          <td><?= esc($value($row, $key)) ?></td>
        <?php endforeach ?>
      </tr>
    <?php endforeach ?>
    <?php if ($empty): ?>
      <tr><td colspan="<?= count($columns) ?>"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>
