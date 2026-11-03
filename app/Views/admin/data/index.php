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
    'lead'  => 'Satu baris = satu rekaman apa adanya, tanpa skor atau olahan. Waktu dalam milidetik ada di tab Pengerjaan, Jawaban & ketepatan, dan Riwayat percobaan.'
        . ' Siswa dan sekolah tampil sebagai kode, tanpa nama.',
    'actions' => '<a class="btn" href="' . base_url('admin/ekspor') . '">' . icon('download') . ' Unduh semua lembar</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<nav aria-label="Dimensi data mentah">
  <ul class="tabs">
    <?php foreach ($tabs as $key => $label): ?>
      <li><a href="<?= base_url('admin/data/' . $key) ?>"<?= $key === $dimension ? ' aria-current="page"' : '' ?>><?= esc($label) ?></a></li>
    <?php endforeach ?>
  </ul>
</nav>

<?= component('admin-filter-bar', [
    'filters' => $filters,
    'only'    => App\Controllers\Admin\RawDataController::FILTERS,
]) ?>

<?php if ($table === null): ?>
  <div class="empty-state">
    <?= icon('database') ?>
    <p><b>Layanan data mentah belum terpasang.</b><br>Tabel ini diisi RawDataService (Dok 3 §8.4) setelah bagian data mentah digabung.</p>
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
