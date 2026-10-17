<?php
/**
 * Tabel data panel (Dok 3 §8.2) — urut, pagination, dan CSV di server.
 *
 * `$table` berbentuk hasil App\Libraries\AdminTable::page() atau
 * RawDataService::page():
 *   rows, total, page, per_page, sort (`key` / `-key`),
 *   columns: list<{key, label, type, sortable?}>
 *
 * Header lengket, judul kolom kalimat biasa + satuan, baris 44 px, garis
 * horizontal saja, angka rata kanan tabular, kode mono, status sebagai
 * lencana, sel kosong "—", durasi `m:ss` dengan milidetik di `title`.
 * Kolom banyak → gulir horizontal dengan kolom pertama lengket.
 * Pagination 25/50/100 + total baris; tombol **Unduh CSV** memakai filter
 * yang sama (`?format=csv`, nilai mentah). Semua tautan biasa: berfungsi
 * tanpa JavaScript; admin/tables.js hanya menambah kenyamanan.
 *
 * @var array<string, mixed>          $table
 * @var string|null                   $title        judul di bilah alat tabel
 * @var string|null                   $id
 * @var bool|null                     $csv          tombol Unduh CSV (bawaan: ya)
 * @var array<string, Closure>|null   $render       key → fn(array $row): string (HTML sudah di-escape)
 * @var Closure|null                  $expand       fn(array $row): ?string — baris rincian yang dapat dibuka
 * @var string|null                   $expandLabel  teks tombol pembuka baris rincian
 * @var string|null                   $tools        HTML tambahan di bilah alat (sudah di-escape)
 * @var string|null                   $emptyMessage
 * @var bool|null                     $stickyFirst  kolom pertama lengket (bawaan: ya)
 * @var string|null                   $anchor       fragmen yang ditambahkan ke tautan urut/halaman
 * @var bool|null                     $paginate     false = daftar pendek tanpa pagination (Ringkasan)
 * @var string|null                   $note         short muted line under the table (plain text)
 */
use App\Libraries\AdminTable;

$request  = service('request');
$path     = '/' . ltrim($request->getPath(), '/');
$query    = $request->getGet() ?? [];
unset($query['format']);
$columns  = $table['columns'] ?? [];
$rows     = $table['rows'] ?? [];
$total    = (int) ($table['total'] ?? 0);
$page     = max(1, (int) ($table['page'] ?? 1));
$perPage  = max(1, (int) ($table['per_page'] ?? AdminTable::PER_PAGE[0]));
$sort     = (string) ($table['sort'] ?? '');
$pages    = max(1, (int) ceil($total / $perPage));
$render   = $render ?? [];
$csv      = $csv ?? true;
$sticky   = $stickyFirst ?? true;
$fragment = ! empty($anchor) ? '#' . $anchor : '';
$link     = static fn (array $change): string => esc(base_url(ltrim(AdminTable::url($path, $query, $change), '/')) . $fragment, 'attr');
$from     = $total === 0 ? 0 : ($page - 1) * $perPage + 1;
$to       = min($total, $page * $perPage);
$window   = array_unique(array_merge([1], range(max(1, $page - 2), min($pages, $page + 2)), [$pages]));
sort($window);
?>
<section class="table-card"<?= ! empty($id) ? ' id="' . esc($id, 'attr') . '"' : '' ?> data-table-card>
  <div class="table-toolbar">
    <div class="table-title">
      <?php if (! empty($title)): ?><h2 class="table-title"><?= esc($title) ?></h2><?php endif ?>
      <?php if ($paginate ?? true): ?><span class="table-count"><?= esc(fmt_num($total, 0, 'id')) ?> baris</span><?php endif ?>
    </div>
    <div class="table-tools">
      <?= $tools ?? '' ?>
      <?php if ($csv): ?>
        <a class="btn btn-sm" href="<?= esc(base_url(ltrim(AdminTable::url($path, $query, ['format' => 'csv', 'page' => null, 'per_page' => null]), '/')), 'attr') ?>" download>
          <?= icon('download') ?> Unduh CSV
        </a>
      <?php endif ?>
    </div>
  </div>

  <?php if ($rows === []): ?>
    <div class="empty-state">
      <?= icon('database') ?>
      <p><?= esc($emptyMessage ?? lang('Admin.emptyDefault')) ?></p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table<?= $sticky ? ' has-sticky-first' : '' ?>" data-sortable="server">
        <?php if (! empty($title)): ?><caption class="visually-hidden"><?= esc($title) ?></caption><?php endif ?>
        <thead>
          <tr>
            <?php foreach ($columns as $column): ?>
              <?php
                $key       = (string) $column['key'];
                $type      = (string) ($column['type'] ?? 'text');
                $sortable  = (bool) ($column['sortable'] ?? true);
                $direction = $sort === $key ? 'ascending' : ($sort === '-' . $key ? 'descending' : null);
                $next      = $direction === 'ascending' ? '-' . $key : $key;
              ?>
              <th scope="col" class="<?= AdminTable::cellClass($type) ?>"<?= $direction !== null ? ' aria-sort="' . $direction . '"' : '' ?> data-key="<?= esc($key, 'attr') ?>">
                <?php if ($sortable && $key !== '' && ($column['label'] ?? '') !== ''): ?>
                  <a class="th-sort" href="<?= $link(['sort' => $next]) ?>"><?= esc($column['label']) ?><span class="th-sort-icon" aria-hidden="true"></span></a>
                <?php else: ?>
                  <?= esc($column['label'] ?? '') ?>
                <?php endif ?>
              </th>
            <?php endforeach ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $index => $row): ?>
            <?php $row = is_array($row) ? $row : (array) $row; ?>
            <?php $detail = isset($expand) && $expand instanceof Closure ? $expand($row) : null; ?>
            <tr<?= $detail !== null ? ' data-expandable' : '' ?>>
              <?php foreach ($columns as $column): ?>
                <?php $key = (string) $column['key']; $type = (string) ($column['type'] ?? 'text'); ?>
                <td class="<?= AdminTable::cellClass($type) ?>">
                  <?= isset($render[$key]) && $render[$key] instanceof Closure ? $render[$key]($row) : AdminTable::cell($row[$key] ?? null, $type) ?>
                </td>
              <?php endforeach ?>
            </tr>
            <?php if ($detail !== null): ?>
              <tr class="row-expand" id="<?= esc(($id ?? 'tabel') . '-rinci-' . $index, 'attr') ?>" data-label="<?= esc($expandLabel ?? 'Rincian', 'attr') ?>">
                <td colspan="<?= count($columns) ?>"><?= $detail ?></td>
              </tr>
            <?php endif ?>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>

  <?php if (! empty($note)): ?>
    <p class="table-note muted"><?= esc($note) ?></p>
  <?php endif ?>

  <?php if ($paginate ?? true): ?>
  <nav class="pagination" aria-label="Halaman tabel">
    <span class="pagination-info"><?= esc(fmt_num($from, 0, 'id')) ?>–<?= esc(fmt_num($to, 0, 'id')) ?> dari <?= esc(fmt_num($total, 0, 'id')) ?> baris</span>

    <form class="pagination-size" method="get" action="<?= esc(base_url(ltrim($path, '/')), 'attr') ?>">
      <?php foreach ($query as $name => $value): ?>
        <?php if ($name === 'per_page' || $name === 'page' || is_array($value)) continue; ?>
        <input type="hidden" name="<?= esc((string) $name, 'attr') ?>" value="<?= esc((string) $value, 'attr') ?>">
      <?php endforeach ?>
      <label for="<?= esc(($id ?? 'tabel') . '-per-page', 'attr') ?>">Baris per halaman</label>
      <select id="<?= esc(($id ?? 'tabel') . '-per-page', 'attr') ?>" name="per_page" data-auto-submit>
        <?php foreach (AdminTable::PER_PAGE as $size): ?>
          <option value="<?= $size ?>"<?= $size === $perPage ? ' selected' : '' ?>><?= $size ?></option>
        <?php endforeach ?>
      </select>
      <button class="btn btn-sm no-js-only" type="submit">Tampilkan</button>
    </form>

    <?php if ($pages > 1): ?>
      <ul>
        <li>
          <?php if ($page > 1): ?>
            <a href="<?= $link(['page' => $page - 1]) ?>" rel="prev" aria-label="Halaman sebelumnya"><?= icon('left') ?></a>
          <?php else: ?>
            <span class="page is-disabled" aria-hidden="true"><?= icon('left') ?></span>
          <?php endif ?>
        </li>
        <?php $previous = 0; ?>
        <?php foreach ($window as $number): ?>
          <?php if ($number - $previous > 1): ?>
            <li><span class="page" aria-hidden="true">…</span></li>
          <?php endif ?>
          <li>
            <?php if ($number === $page): ?>
              <span class="page is-active" aria-current="page"><?= $number ?></span>
            <?php else: ?>
              <a href="<?= $link(['page' => $number]) ?>" aria-label="Halaman <?= $number ?>"><?= $number ?></a>
            <?php endif ?>
          </li>
          <?php $previous = $number; ?>
        <?php endforeach ?>
        <li>
          <?php if ($page < $pages): ?>
            <a href="<?= $link(['page' => $page + 1]) ?>" rel="next" aria-label="Halaman berikutnya"><?= icon('right') ?></a>
          <?php else: ?>
            <span class="page is-disabled" aria-hidden="true"><?= icon('right') ?></span>
          <?php endif ?>
        </li>
      </ul>
    <?php endif ?>
  </nav>
  <?php endif ?>
</section>
