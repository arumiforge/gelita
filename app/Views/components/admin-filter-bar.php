<?php
/**
 * Bilah filter panel (Dok 3 §8.2): baris kontrol 40 px (pencarian, select,
 * rentang tanggal), tombol "Terapkan" & "Atur ulang", dan chip filter aktif
 * yang masing-masing dapat dilepas.
 *
 * Form `method="get"`, sehingga filter tersimpan di URL dan dapat dibagikan.
 * admin/filters.js memuat isi halaman untuk filter baru di latar tanpa
 * memuat ulang halaman; tanpa JavaScript, tombol Terapkan mengirim form.
 *
 * Schools are listed by name for the admin and by code (`SCH-000123`) for
 * `peneliti` and `ketua` (BaseAdminController::filterOptions(), 27 K5).
 *
 * @var array<string, mixed>          $filters        filter aktif
 * @var Closure|array<string, mixed>  $filterOptions  BaseAdminController::filterOptions()
 * @var list<string>|null             $only           kontrol yang dirender, berurutan
 * @var array<string, string>|null    $extra          ['q' => nilai, 'q_placeholder' => …] (bentuk lama)
 * @var array<string, array<string, string>>|null $choices pilihan tambahan [kunci => [nilai => label]]
 * @var array<string, string>|null    $labels         label kontrol yang diganti [kunci => label]
 * @var bool|null                     $bare           true = hanya kontrol, tanpa <form>, tombol, dan chip
 */
use App\Libraries\AdminTable;

$options = $filterOptions instanceof Closure ? $filterOptions() : ($filterOptions ?? []);
$filters = $filters ?? [];
$bare    = ! empty($bare);
$request = service('request');
$path    = '/' . ltrim($request->getPath(), '/');
$query   = $request->getGet() ?? [];

if (isset($extra['q']) && ! isset($filters['q']) && $extra['q'] !== '') {
    $filters['q'] = $extra['q'];
}

$order = $only ?? ['q', 'study_id', 'level_id', 'school_id', 'class_level', 'province_code', 'date_from', 'locale'];
if (isset($extra['q']) && ! in_array('q', $order, true)) {
    array_unshift($order, 'q');
}

$labelOf = static fn (array $rows, string $key, string $text): array => array_column($rows, $text, $key);
$choices = ($choices ?? []) + [
    'study_id' => array_map(
        static fn (array $s): string => (($s['name'] ?? '') !== '' ? $s['name'] : $s['code']) . ($s['status'] === 'active' ? ' (sedang berjalan)' : ''),
        array_column($options['studies'] ?? [], null, 'id'),
    ),
    'level_id'      => array_map(static fn ($l): string => $l->text('name', 'id'), array_column($options['levels'] ?? [], null, 'id')),
    'node_id'       => $options['nodes'] ?? [],
    'variant_group' => ['a' => 'Variasi A', 'b' => 'Variasi B', 'c' => 'Variasi C'],
    'school_id'     => $labelOf($options['schools'] ?? [], 'id', 'name'),
    'district_code' => $options['districts'] ?? [],
    'province_code' => $options['provinces'] ?? [],
    'class_level'   => array_combine($options['classes'] ?? [], array_map('class_label', $options['classes'] ?? [])) ?: [],
    'gender'        => lang('Admin.gender', [], 'id'),
    'age'           => array_combine($options['ages'] ?? [], array_map(static fn ($a): string => $a . ' tahun', $options['ages'] ?? [])) ?: [],
    'progress'      => ['belum' => 'Belum mulai', 'berjalan' => 'Berjalan', 'selesai' => 'Selesai'],
    'locale'        => array_combine(config('Gelita')->locales, array_map(static fn ($c): string => admin_label('locale', $c), config('Gelita')->locales)),
];
$labels = ($labels ?? []) + [
    'q'                => 'Cari',
    'study_id'         => 'Penelitian',
    'level_id'         => 'Wilayah',
    'node_id'          => 'Tantangan',
    'variant_group'    => 'Variasi',
    'school_id'        => 'Sekolah',
    'district_code'    => 'Kab/Kota',
    'province_code'    => 'Provinsi',
    'class_level'      => 'Kelas',
    'gender'           => 'Jenis kelamin',
    'age'              => 'Umur',
    'progress'         => 'Status progres',
    'locale'           => 'Bahasa',
    'participant_code' => 'Kode siswa',
    'date_from'        => 'Dari tanggal',
    'date_to'          => 'Sampai tanggal',
];
$all = [
    'study_id' => 'Semua penelitian', 'level_id' => 'Semua wilayah', 'node_id' => 'Semua tantangan', 'variant_group' => 'Semua variasi',
    'school_id' => 'Semua sekolah', 'district_code' => 'Semua kab/kota', 'province_code' => 'Semua provinsi', 'class_level' => 'Semua kelas',
    'gender' => 'Semua', 'age' => 'Semua umur', 'progress' => 'Semua status', 'locale' => 'Semua bahasa',
];
$value = static fn (string $key): string => (string) ($filters[$key] ?? '');
$shown = static fn (string $key): bool => in_array($key, $order, true)
    || ($key === 'date_to' && in_array('date_from', $order, true));

// Chip filter aktif: hanya kunci yang dirender bilah ini
$chips = [];
foreach (array_keys($labels) as $key) {
    if (! $shown($key) || $value($key) === '') {
        continue;
    }

    $text    = $choices[$key][$value($key)] ?? $value($key);
    $text    = in_array($key, ['date_from', 'date_to'], true) ? fmt_date($value($key) . ' 00:00:00', false, 'id') : $text;
    $chips[] = [
        'label'  => $labels[$key],
        'text'   => preg_replace('/ 00:00$/', '', (string) $text),
        'remove' => base_url(ltrim(AdminTable::url($path, $query, [$key => null]), '/')),
    ];
}
$resetUrl = base_url(ltrim(AdminTable::url($path, array_intersect_key($query, array_flip(['sort', 'per_page'])), []), '/'));
?>
<?php if ($bare): ?>
<div class="filter-bar">
<?php else: ?>
<form method="get" class="filter-bar" aria-label="Filter data">
  <?php foreach (['sort', 'per_page'] as $keep): ?>
    <?php if (isset($query[$keep]) && is_string($query[$keep])): ?>
      <input type="hidden" name="<?= $keep ?>" value="<?= esc($query[$keep], 'attr') ?>">
    <?php endif ?>
  <?php endforeach ?>
<?php endif ?>

  <?php foreach ($order as $key): ?>
    <?php if ($key === 'q'): ?>
      <div class="field filter-search">
        <label for="f-q"><?= esc($labels['q']) ?></label>
        <input type="search" id="f-q" name="q" value="<?= esc($value('q'), 'attr') ?>"
               placeholder="<?= esc($extra['q_placeholder'] ?? 'kode siswa, nama pengguna, atau nama', 'attr') ?>">
      </div>
    <?php elseif ($key === 'participant_code'): ?>
      <div class="field">
        <label for="f-participant_code"><?= esc($labels[$key]) ?></label>
        <input type="text" id="f-participant_code" name="participant_code" value="<?= esc($value($key), 'attr') ?>"
               placeholder="GLT-000123" autocomplete="off" spellcheck="false" class="mono">
      </div>
    <?php elseif ($key === 'date_from'): ?>
      <div class="field field-date">
        <label for="f-from"><?= esc($labels['date_from']) ?></label>
        <input type="date" id="f-from" name="date_from" value="<?= esc($value('date_from'), 'attr') ?>">
      </div>
      <div class="field field-date">
        <label for="f-to"><?= esc($labels['date_to']) ?></label>
        <input type="date" id="f-to" name="date_to" value="<?= esc($value('date_to'), 'attr') ?>">
      </div>
    <?php elseif (isset($choices[$key])): ?>
      <div class="field">
        <label for="f-<?= esc($key, 'attr') ?>"><?= esc($labels[$key] ?? $key) ?></label>
        <select id="f-<?= esc($key, 'attr') ?>" name="<?= esc($key, 'attr') ?>">
          <option value=""><?= esc($all[$key] ?? 'Semua') ?></option>
          <?php foreach ($choices[$key] as $optionValue => $optionLabel): ?>
            <option value="<?= esc((string) $optionValue, 'attr') ?>"<?= $value($key) === (string) $optionValue ? ' selected' : '' ?>><?= esc((string) $optionLabel) ?></option>
          <?php endforeach ?>
        </select>
      </div>
    <?php endif ?>
  <?php endforeach ?>

<?php if ($bare): ?>
</div>
<?php else: ?>
  <div class="filter-actions">
    <?php if ($chips !== []): ?>
      <a class="btn btn-quiet" href="<?= esc($resetUrl, 'attr') ?>">Atur ulang</a>
    <?php endif ?>
    <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Terapkan</button>
  </div>

  <?php if ($chips !== []): ?>
    <div class="filter-chips" aria-label="Filter aktif">
      <span class="filter-chips-label">Filter aktif:</span>
      <?php foreach ($chips as $chip): ?>
        <span class="chip filter-chip">
          <?= esc($chip['label']) ?>: <b><?= esc($chip['text']) ?></b>
          <a href="<?= esc($chip['remove'], 'attr') ?>" aria-label="Lepas filter <?= esc($chip['label'], 'attr') ?>"><?= icon('cross') ?></a>
        </span>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</form>
<?php endif ?>
