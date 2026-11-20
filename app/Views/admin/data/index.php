<?php
/**
 * Data mentah — `/admin/data/{dimensi}` → RawDataController::show (Dok 3 §8.3)
 *
 * Tab = lembar ekspor §9.2; kolom = kolom lembar itu, nilai mentah berkode
 * (sama dengan CSV). Nilai `enum` tampil berlabel (ExportLabels::label())
 * dengan kode aslinya di `title`. Answer columns (Jawaban, Riwayat
 * percobaan) are sentences by default; components/answer-view-switch
 * shows the stored JSON instead (`?raw=1`), and the table CSV follows it. Filter: kode siswa, sekolah, wilayah,
 * tantangan, variasi, rentang tanggal.
 *
 * @var string                    $dimension
 * @var array<string, string>     $tabs
 * @var array<string, mixed>|null $table   RawDataService::page(); null = layanan belum terpasang
 * @var array<string, mixed>      $filters
 * @var bool                      $readable  answer columns as sentences
 * @var string                    $group     tab group (RawDataController::GROUPS)
 */
$labeler = class_exists(App\Libraries\ExportLabels::class) && method_exists(App\Libraries\ExportLabels::class, 'label')
    ? static fn (string $key, $value): string => (string) App\Libraries\ExportLabels::label($dimension, $key, $value)
    : null;
$render = [];

foreach ($table['columns'] ?? [] as $column) {
    if (($column['type'] ?? '') === 'enum' && $labeler !== null) {
        $key          = (string) $column['key'];
        $render[$key] = static fn (array $row): string => ($row[$key] ?? null) === null || $row[$key] === ''
            ? '<span class="is-empty">—</span>'
            : '<span title="' . esc((string) $row[$key], 'attr') . '">' . esc($labeler($key, $row[$key])) . '</span>';
    }
}
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Data mentah',
    'lead'  => 'Setiap baris adalah satu catatan apa adanya dari permainan. Siswa dan sekolah tampil sebagai kode, tanpa nama. Pilih kelompok data di menu kiri, lalu lembarnya di bawah; filter dan Unduh CSV berlaku untuk lembar yang sedang dibuka.',
    'actions' => '<a class="btn" href="' . base_url('admin/ekspor') . '">' . icon('download') . ' Unduh semua lembar</a>',
]) ?>
<?php // Part 9 (35 §10.4): run time, dictionary version, stale, Hitung ulang ?>
<?php if (App\Libraries\DerivedSheets::has($dimension)): ?>
  <?php if (($derivedStudy ?? null) !== null): ?>
    <?= view('admin/study/_derived', ['study' => $derivedStudy, 'back' => 'admin/data/' . $dimension]) ?>
  <?php else: ?>
    <p class="muted"><?= esc(lang('Admin.derivedOffForStudy')) ?></p>
  <?php endif ?>
<?php endif ?>
<?= $this->include('partials/flash') ?>

<?php [$groupLabel, $groupLead, $groupTabs] = App\Controllers\Admin\RawDataController::GROUPS[$group]; ?>
<section class="raw-group" aria-labelledby="raw-group-title">
  <h2 id="raw-group-title" class="raw-group-title"><?= esc(lang($groupLabel)) ?></h2>
  <p class="raw-group-lead"><?= esc(lang($groupLead)) ?></p>
  <nav aria-label="<?= esc(lang($groupLabel), 'attr') ?>">
    <ul class="tabs">
      <?php foreach ($groupTabs as $key): ?>
        <li><a href="<?= base_url('admin/data/' . $key) ?>"<?= $key === $dimension ? ' aria-current="page"' : '' ?>><?= esc(ucfirst(preg_replace('/^Turunan: /', '', $tabs[$key]))) ?></a></li>
      <?php endforeach ?>
    </ul>
  </nav>
  <p class="raw-tab-help"><?= icon('info') ?> <span><?= esc(lang('Admin.rawTabHelp_' . $dimension)) ?></span></p>
</section>

<?= component('admin-filter-bar', [
    'filters' => $filters,
    'only'    => App\Controllers\Admin\RawDataController::FILTERS,
]) ?>

<?php if ($table === null): ?>
  <div class="empty-state">
    <?= icon('database') ?>
    <p><b>Data mentah belum tersedia.</b><br>Hubungi admin bila halaman ini tetap kosong.</p>
  </div>
<?php else: ?>
  <?= component('data-table', [
      'table'        => $table,
      'id'           => 'data-' . $dimension,
      'title'        => $tabs[$dimension],
      'render'       => $render,
      'tools'        => in_array($dimension, ['jawaban', 'percobaan'], true) ? component('answer-view-switch', ['readable' => $readable]) : null,
      'emptyMessage' => $filters !== [] ? 'Tidak ada baris yang cocok dengan filter ini.' : 'Belum ada data untuk lembar ini.',
  ]) ?>
<?php endif ?>
<?= $this->endSection() ?>
