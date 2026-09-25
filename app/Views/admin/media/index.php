<?php
/**
 * Media — `/admin/media` → MediaController::index
 *
 * Unggah gambar/video per slot `asset_key`. Ukuran wajib per slot dibaca
 * ulang server dengan getimagesize(); di tabel, ukuran yang tidak sesuai
 * ditandai merah (teks + warna). "Pindai berkas" mencocokkan baris database
 * dengan berkas di disk (hilang, sha256, ukuran berkas).
 *
 * @var list<array<string, mixed>>        $assets
 * @var array<string, array{0: int, 1: int}> $assetSizes pola asset_key → [lebar, tinggi]
 * @var array<int, list<string>>              $usage      media_asset_id → tempat pemakaian (MediaUsage)
 * @var array{at: string, findings: list<array{asset_key: string, issue: string}>}|null $scan
 */
$requiredFor = static function (string $key) use ($assetSizes): ?array {
    foreach ($assetSizes as $pattern => $size) {
        if ($pattern === $key || fnmatch($pattern, $key)) {
            return $size;
        }
    }

    return null;
};
$visual  = array_values(array_filter($assets, static fn (array $row): bool => $row['asset_type'] !== 'audio'));
$audios  = count($assets) - count($visual);
$active  = count(array_filter($visual, static fn (array $row): bool => (bool) $row['is_active']));
$empty   = count($visual) - $active;
$unused  = count(array_filter($visual, static fn (array $row): bool => $row['is_active']
    && ! isset($usage[(int) $row['id']]) && App\Libraries\MediaUsage::slotLabel((string) $row['asset_key']) === null));
$wrong   = 0;

foreach ($visual as $row) {
    $need = $requiredFor((string) $row['asset_key']);

    if ($need !== null && $row['width_px'] !== null
        && ((int) $row['width_px'] !== $need[0] || (int) $row['height_px'] !== $need[1])) {
        $wrong++;
    }
}
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/media/pindai') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn btn-ghost btn-sm" type="submit"><?= icon('search') ?> Pindai berkas</button>
</form>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media/kelengkapan') ?>"><?= icon('list') ?> Kelengkapan aset</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media/audio') ?>"><?= icon('sound') ?> Aset audio</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Media',
    'eyebrow' => 'Pengelolaan · gambar & video',
    'lead'    => 'Berkas disimpan dengan nama resmi turunan asset_key, bukan nama asli unggahan. Mengunggah ulang asset_key yang sama mengganti berkasnya.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (is_array($scan)): ?>
  <section class="panel">
    <h2 class="panel-title"><?= icon('search') ?> Hasil pindai <span class="muted"><?= esc(fmt_date($scan['at'], true)) ?></span></h2>
    <?php if ($scan['findings'] === []): ?>
      <p class="alert alert-ok" role="status"><?= icon('check') ?> Semua berkas aset cocok dengan catatan database.</p>
    <?php else: ?>
      <p class="alert alert-error" role="alert"><?= count($scan['findings']) ?> aset bermasalah. Unggah ulang berkasnya atau nonaktifkan asetnya.</p>
      <?= component('components/admin-table', [
          'caption'  => 'Temuan pindai berkas',
          'columns'  => ['asset_key' => ['label' => 'asset_key', 'format' => 'code'], 'issue' => 'Masalah'],
          'rows'     => $scan['findings'],
          'rowClass' => static fn (): string => 'is-error',
      ]) ?>
    <?php endif ?>
  </section>
<?php endif ?>

<div class="kpi-grid">
  <?= component('components/stat-tile', ['label' => 'Gambar & video', 'value' => fmt_num(count($visual)), 'icon' => 'image']) ?>
  <?= component('components/stat-tile', ['label' => 'Aktif', 'value' => fmt_num($active), 'icon' => 'check']) ?>
  <?= component('components/stat-tile', ['label' => 'Slot belum berberkas', 'value' => fmt_num($empty), 'icon' => 'image', 'hint' => 'permainan memakai pengganti']) ?>
  <?= component('components/stat-tile', ['label' => 'Belum dipakai', 'value' => fmt_num($unused), 'icon' => 'info', 'hint' => 'aktif tetapi tidak dirujuk konten']) ?>
  <?= component('components/stat-tile', ['label' => 'Ukuran tidak sesuai', 'value' => fmt_num($wrong), 'icon' => 'warn']) ?>
  <?= component('components/stat-tile', ['label' => 'Berkas audio', 'value' => fmt_num($audios), 'icon' => 'sound', 'hint' => 'dikelola di halaman Audio']) ?>
</div>

<div class="split-grid">
  <form method="post" action="<?= base_url('admin/media/unggah') ?>" class="upload-box" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <h2 class="panel-title"><?= icon('upload') ?> Unggah aset</h2>
    <div class="field">
      <label for="asset_key">asset_key <span class="req">*</span></label>
      <input type="text" id="asset_key" name="asset_key" required maxlength="160" placeholder="bg.temanggung" spellcheck="false" list="asset-keys" value="<?= esc((string) (service('request')->getGet('asset_key') ?? ''), 'attr') ?>">
      <datalist id="asset-keys">
        <?php foreach ($assets as $asset): ?>
          <option value="<?= esc($asset['asset_key'], 'attr') ?>"></option>
        <?php endforeach ?>
      </datalist>
      <p class="field-help">Pilih asset_key yang ada untuk mengganti berkasnya, atau tulis yang baru. Gambar tantangan, opsi, bacaan, dan Pustaka juga dapat diunggah langsung dari editor kontennya.</p>
    </div>
    <div class="field">
      <label for="file">Berkas <span class="req">*</span></label>
      <input type="file" id="file" name="file" required accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,video/mp4,video/webm,video/ogg">
      <p class="field-help">PNG, JPEG, WebP, GIF, SVG, MP4, WebM, atau OGV; maksimal 64 MB.</p>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah</button>
    </div>
  </form>

  <section class="panel">
    <h2 class="panel-title"><?= icon('grid') ?> Ukuran wajib per slot</h2>
    <p class="muted">Gambar dengan ukuran lain ditolak saat diunggah. Tanda * berarti semua kunci dengan awalan itu.</p>
    <div class="table-wrap">
      <table class="data-table">
        <caption class="visually-hidden">Ukuran wajib aset gambar</caption>
        <thead><tr><th scope="col">Pola asset_key</th><th scope="col" class="is-num">Lebar × tinggi (px)</th></tr></thead>
        <tbody>
          <?php foreach ($assetSizes as $pattern => $size): ?>
            <tr><td><code><?= esc($pattern) ?></code></td><td class="is-num"><?= esc($size[0]) ?> × <?= esc($size[1]) ?></td></tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </section>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('image') ?> Aset gambar & video</h2>
  <?= component('components/admin-table', [
      'caption'      => 'Daftar aset gambar dan video',
      'emptyMessage' => 'Belum ada aset gambar. Permainan memakai latar gradien bawaan sampai aset diunggah.',
      'rows'         => $visual,
      'rowClass'     => static fn (array $row): string => $row['is_active'] ? '' : 'is-muted',
      'columns'      => [
          'thumb' => ['label' => 'Pratinjau', 'render' => static function (array $row): string {
              // Slot nonaktif biasanya berarti berkasnya belum diunggah: jangan minta ke server
              if ($row['asset_type'] !== 'image' || ! $row['is_active']) {
                  return '<span class="media-thumb is-empty">' . icon($row['asset_type'] === 'image' ? 'image' : 'play') . '</span>';
              }

              return '<span class="media-thumb"><img src="' . esc(base_url($row['storage_path']), 'attr') . '" alt="" loading="lazy" decoding="async"></span>';
          }],
          'asset_key' => ['label' => 'asset_key', 'render' => static fn (array $row): string => '<code>' . esc($row['asset_key']) . '</code><span class="cell-sub">' . esc($row['mime_type'] ?? $row['asset_type']) . '</span>'],
          'usage' => ['label' => 'Dipakai di', 'render' => static function (array $row) use ($usage): string {
              $slot  = App\Libraries\MediaUsage::slotLabel((string) $row['asset_key']);
              $where = $usage[(int) $row['id']] ?? [];

              if ($slot === null && $where === []) {
                  return '<span class="muted">belum dipakai</span>';
              }

              $html = $slot === null ? '' : '<span class="cell-sub">slot: ' . esc($slot) . '</span>';

              foreach (array_slice($where, 0, 4) as $label) {
                  $html .= '<span class="cell-sub">' . esc($label) . '</span>';
              }

              return $html . (count($where) > 4 ? '<span class="cell-sub">+' . (count($where) - 4) . ' lainnya</span>' : '');
          }],
          'size' => ['label' => 'Ukuran', 'render' => static function (array $row) use ($requiredFor): string {
              $need = $requiredFor((string) $row['asset_key']);
              $has  = $row['width_px'] === null ? null : [(int) $row['width_px'], (int) $row['height_px']];
              $text = $has === null ? '—' : $has[0] . '×' . $has[1];

              if ($need === null || $has === null) {
                  return '<span class="num">' . esc($text) . '</span>' . ($need !== null ? '<span class="cell-sub">wajib ' . $need[0] . '×' . $need[1] . '</span>' : '');
              }

              $ok = $has === $need;

              return '<span class="num ' . ($ok ? 'size-ok' : 'size-bad') . '">' . icon($ok ? 'check' : 'cross') . ' ' . esc($text) . '</span>'
                  . ($ok ? '' : '<span class="cell-sub">wajib ' . $need[0] . '×' . $need[1] . '</span>');
          }],
          'file_size' => ['label' => 'Berkas', 'render' => static fn (array $row): string => $row['file_size'] === null ? '—' : '<span class="num">' . esc(fmt_num(((int) $row['file_size']) / 1024, 0, 'id')) . ' KB</span>'],
          'is_active' => ['label' => 'Status', 'render' => static fn (array $row): string => '<span class="badge ' . ($row['is_active'] ? 'is-active">aktif' : 'is-inactive">nonaktif') . '</span>'],
          'actions' => ['label' => '', 'render' => static function (array $row): string {
              // Tombol Unggah per baris: diberi perilaku admin/media-upload.js (tersembunyi tanpa JavaScript)
              $upload = '<button type="button" class="btn btn-quiet btn-sm" data-upload-key="' . esc($row['asset_key'], 'attr') . '" hidden>'
                  . icon('upload') . ' Unggah</button>';

              if (! $row['is_active']) {
                  return $upload;
              }

              return $upload . '<form method="post" action="' . esc(base_url('admin/media/' . $row['id'] . '/nonaktif'), 'attr') . '" class="inline-form">'
                  . csrf_field()
                  . '<button class="btn btn-quiet btn-sm" type="submit">Nonaktifkan</button></form>';
          }],
      ],
  ]) ?>
</section>
<script type="application/json" id="asset-sizes"><?= json_encode(
    ['sizes' => $assetSizes, 'strict' => true],
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
<?= $this->endSection() ?>
