<?php
/**
 * Slot detail — `/admin/media/{id}` → MediaController::show
 *
 * Everything an admin needs to install one image and check it: preview
 * (with map pins drawn on top), what the picture should show, file and
 * display sizes, format and transparency, the game screens it appears on
 * (public paths to open and check after uploading), the editor that links
 * it, and an upload form that comes back here. Prompts are not repeated: the
 * team's Katalog Aset is searched by asset key.
 *
 * @var array<string, mixed>                  $row      media_assets row
 * @var array<string, mixed>|null             $slot     ImageSlots::describe(), null for keys the game does not read by name
 * @var array{0: int, 1: int}|null            $required enforced file size (upload is rejected otherwise)
 * @var array{0: int, 1: int}|null            $display  size on the 1280 × 720 dp stage
 * @var list<array{label: string, path: string|null, edit: string|null}> $uses
 * @var list<array{label: string, x: float, y: float}> $pins
 * @var array<string, mixed>|null             $prev
 * @var array<string, mixed>|null             $next
 * @var array{0: int, 1: int}|null            $counter
 */
use App\Libraries\ImageSlots;
use App\Libraries\MediaStore;

$key         = (string) $row['asset_key'];
$isActive    = (int) $row['is_active'] === 1;
$isSample    = $isActive && (int) ($row['is_placeholder'] ?? 0) === 1;
$statusCode  = ! $isActive ? 'kosong' : ($isSample ? 'mockup' : 'asli');
$statusText  = ['asli' => 'Gambar asli', 'mockup' => 'Gambar contoh', 'kosong' => 'Kosong'][$statusCode];
$has         = $row['width_px'] === null ? null : [(int) $row['width_px'], (int) $row['height_px']];
$target      = $required ?? ($slot['recommended'] ?? null);
$sizeOk      = $has === null || $target === null || $has === $target;
$label       = trim((string) ($row['label'] ?? '')) !== '' ? (string) $row['label'] : ($slot['label'] ?? $key);
$content     = trim((string) ($row['description'] ?? '')) !== '' ? (string) $row['description'] : ($slot['content'] ?? '');
$screens     = $uses !== [] ? array_map(static fn (array $u): array => [$u['label'], $u['path'], $u['edit']], $uses)
    : array_map(static fn (array $s): array => [$s[0], $s[1], null], $slot['screens'] ?? []);
$ratio       = $target === null ? null : $target[0] . ' / ' . $target[1];
$src         = $isActive && $row['asset_type'] !== 'video'
    ? base_url(MediaStore::versionedPath((string) $row['storage_path'], $row['sha256'] ?? null)) : null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media' . (($slot['group'] ?? 'other') !== 'other' ? '?kelompok=' . $slot['group'] : '')) ?>"><?= icon('left') ?> Kembali ke daftar</a>
<?php if ($prev !== null): ?>
  <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media/' . (int) $prev['id']) ?>" title="<?= esc($prev['asset_key'], 'attr') ?>"><?= icon('left') ?> Sebelumnya</a>
<?php endif ?>
<?php if ($next !== null): ?>
  <a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media/' . (int) $next['id']) ?>" title="<?= esc($next['asset_key'], 'attr') ?>">Berikutnya <?= icon('right') ?></a>
<?php endif ?>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => $label,
    'lead'    => ImageSlots::GROUPS[$slot['group'] ?? 'other'] . ($counter === null ? '' : ' · gambar ' . $counter[0] . ' dari ' . $counter[1]) . ' · kode berkas ' . $key,
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (($slot['priority'] ?? '') === 'unused'): ?>
  <div class="alert alert-info" role="status"><?= icon('info') ?><p>Slot ini belum dibaca permainan. <?= esc($slot['tips'] ?? '') ?></p></div>
<?php elseif ($isSample): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p>Slot ini masih memakai gambar contoh. Ganti dengan gambar asli sebelum pengambilan data.</p></div>
<?php elseif (! $isActive): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p>Belum ada gambar. Sementara permainan menampilkan: <?= esc($slot['fallback'] ?? 'tampilan pengganti') ?>.</p></div>
<?php elseif (! $sizeOk): ?>
  <div class="alert alert-error" role="alert"><?= icon('cross') ?><p>Ukuran gambar sekarang <?= $has[0] ?> × <?= $has[1] ?> piksel, padahal yang dibutuhkan <?= $target[0] ?> × <?= $target[1] ?> piksel. Unggah ulang dengan ukuran yang benar.</p></div>
<?php endif ?>

<div class="split-grid media-detail">
  <section class="panel">
    <h2 class="panel-title"><?= icon('image') ?> Pratinjau <span class="badge is-<?= $statusCode ?>"><?= esc($statusText) ?></span></h2>
    <div class="media-preview<?= ($slot['transparent'] ?? false) ? ' is-transparent' : '' ?>"<?= $src === null && $ratio !== null ? ' style="aspect-ratio: ' . esc($ratio, 'attr') . '"' : '' ?>>
      <?php if ($src !== null): ?>
        <a href="<?= esc($src, 'attr') ?>" target="_blank" rel="noopener" class="media-preview-frame">
          <img src="<?= esc($src, 'attr') ?>" alt="Pratinjau <?= esc($label, 'attr') ?>" decoding="async">
          <?php foreach ($pins as $i => $pin): ?>
            <span class="media-pin" style="left: <?= $pin['x'] ?>%; top: <?= $pin['y'] ?>%" title="<?= esc($pin['label'], 'attr') ?>"><?= $i + 1 ?></span>
          <?php endforeach ?>
        </a>
      <?php else: ?>
        <div class="media-preview-frame is-empty">
          <span><?= icon($row['asset_type'] === 'video' ? 'video' : 'image') ?> <?= $row['asset_type'] === 'video' ? 'Video tidak dipratinjau di sini.' : 'Belum ada gambar' ?></span>
          <?php foreach ($pins as $i => $pin): ?>
            <span class="media-pin" style="left: <?= $pin['x'] ?>%; top: <?= $pin['y'] ?>%" title="<?= esc($pin['label'], 'attr') ?>"><?= $i + 1 ?></span>
          <?php endforeach ?>
        </div>
      <?php endif ?>
    </div>
    <?php if ($src !== null): ?><p class="muted">Klik gambar untuk membuka ukuran penuh.</p><?php endif ?>

    <?php if ($pins !== []): ?>
      <h3 class="panel-subtitle">Titik pin di atas gambar</h3>
      <p class="muted">Permainan menggambar tombol di titik ini (persen dari kiri dan atas gambar<?= $target !== null ? '; piksel pada ' . $target[0] . ' × ' . $target[1] : '' ?>). Biarkan area di sekitarnya tenang dan jangan gambar tulisan atau penanda sendiri.</p>
      <?= component('components/admin-table', [
          'caption' => 'Titik pin',
          'columns' => ['no' => 'No.', 'label' => 'Tombol', 'pos' => 'Posisi (%)', 'px' => 'Piksel'],
          'rows'    => array_map(static fn (array $pin, int $i): array => [
              'no'    => $i + 1,
              'label' => $pin['label'],
              'pos'   => fmt_num($pin['x'], 0, 'id') . ' %, ' . fmt_num($pin['y'], 0, 'id') . ' %',
              'px'    => $target === null ? '—' : round($pin['x'] / 100 * $target[0]) . ', ' . round($pin['y'] / 100 * $target[1]),
          ], $pins, array_keys($pins)),
      ]) ?>
    <?php endif ?>
  </section>

  <section class="panel">
    <h2 class="panel-title"><?= icon('list') ?> Isi gambar yang dibutuhkan</h2>
    <p><?= esc($content !== '' ? $content : 'Belum ada keterangan untuk gambar ini.') ?></p>
    <?php if (($slot['tips'] ?? '') !== '' && ($slot['priority'] ?? '') !== 'unused'): ?>
      <p class="muted"><?= icon('hint') ?> <?= esc($slot['tips']) ?></p>
    <?php endif ?>
    <?php if (trim((string) ($row['credit'] ?? '')) !== ''): ?>
      <p class="muted">Sumber/kredit: <?= esc($row['credit']) ?></p>
    <?php endif ?>

    <dl class="detail-grid">
      <div><dt>Kode berkas</dt><dd><code><?= esc($key) ?></code></dd></div>
      <div><dt>Ukuran berkas</dt><dd><?= $required !== null ? $required[0] . ' × ' . $required[1] . ' px (gambar lebih besar dengan bentuk sama diperkecil otomatis)' : ($target !== null ? 'disarankan ' . $target[0] . ' × ' . $target[1] . ' px' : 'bebas') ?></dd></div>
      <div><dt>Ukuran tampil di layar</dt><dd><?= $display === null ? '—' : ($display[0] === 0 ? 'selebar posisi benda di adegan' : $display[0] . ' × ' . $display[1] . ' dp (layar 1280 × 720)') ?></dd></div>
      <div><dt>Format</dt><dd><?= esc($slot['format'] ?? 'PNG, JPG, WebP, atau SVG') ?></dd></div>
      <div><dt>Latar transparan</dt><dd><?= ($slot['transparent'] ?? false) ? 'Ya, wajib transparan' : 'Tidak perlu' ?></dd></div>
      <div><dt>Prioritas</dt><dd><?= esc(ImageSlots::PRIORITIES[$slot['priority'] ?? ''] ?? '—') ?></dd></div>
      <div><dt>Bila kosong, tampil</dt><dd><?= esc($slot['fallback'] ?? 'tampilan pengganti') ?></dd></div>
      <div><dt>Berkas sekarang</dt><dd><?= $has === null ? '—' : esc($has[0] . ' × ' . $has[1] . ' px') . ($row['file_size'] === null ? '' : ' · ' . fmt_num(((int) $row['file_size']) / 1024, 0, 'id') . ' KB') ?></dd></div>
      <div><dt>Diperbarui</dt><dd><?= empty($row['updated_at']) ? '—' : esc(fmt_date((string) $row['updated_at'], true, 'id')) ?></dd></div>
    </dl>
    <p class="muted">Prompt dan panduan gaya: buka Katalog Aset tim, lalu cari <code><?= esc($key) ?></code>.</p>
  </section>
</div>

<section class="panel">
  <h2 class="panel-title"><?= icon('eye') ?> Tampil di halaman</h2>
  <?php if ($screens === []): ?>
    <p class="muted">Gambar ini belum tampil di halaman permainan mana pun.</p>
  <?php else: ?>
    <p class="muted">Sesudah mengunggah, buka halaman permainan ini (masuk sebagai siswa uji) untuk memeriksa hasilnya.</p>
    <?= component('components/admin-table', [
        'caption' => 'Tempat gambar tampil',
        'columns' => [
            'label' => 'Tempat',
            'path'  => ['label' => 'Alamat halaman', 'render' => static fn (array $r): string => $r['path'] === null ? '<span class="muted">—</span>' : '<code>' . esc($r['path']) . '</code>'],
            'edit'  => ['label' => '', 'render' => static fn (array $r): string => $r['edit'] === null ? '' : '<a class="btn btn-quiet btn-sm" href="' . esc(base_url($r['edit']), 'attr') . '">' . icon('edit') . ' Buka di editor</a>'],
        ],
        'rows'    => array_map(static fn (array $s): array => ['label' => $s[0], 'path' => $s[1], 'edit' => $s[2]], $screens),
    ]) ?>
  <?php endif ?>
  <?php if (($slot['admin'] ?? null) !== null && $uses === []): ?>
    <p><a class="btn btn-quiet btn-sm" href="<?= esc(base_url($slot['admin'][1]), 'attr') ?>"><?= icon('edit') ?> <?= esc($slot['admin'][0]) ?></a></p>
  <?php endif ?>
</section>

<?php if (($slot['priority'] ?? '') !== 'unused'): ?>
  <div class="split-grid">
    <form method="post" action="<?= base_url('admin/media/unggah') ?>" class="upload-box" enctype="multipart/form-data" id="unggah">
      <?= csrf_field() ?>
      <input type="hidden" name="asset_key" value="<?= esc($key, 'attr') ?>">
      <input type="hidden" name="back" value="admin/media/<?= (int) $row['id'] ?>">
      <h2 class="panel-title"><?= icon('upload') ?> <?= $isActive && ! $isSample ? 'Ganti gambar' : 'Unggah gambar asli' ?></h2>
      <div class="field">
        <label for="file">Berkas untuk <code><?= esc($key) ?></code> <span class="req">*</span></label>
        <input type="file" id="file" name="file" required accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,video/mp4,video/webm,video/ogg">
        <p class="field-help"><?= $required !== null ? 'Gambar PNG, JPG, atau WebP paling kecil ' . $required[0] . ' × ' . $required[1] . ' piksel dengan bentuk yang sama; yang lebih besar diperkecil otomatis.' : 'Gambar PNG, JPG, WebP, GIF, atau SVG.' ?> Paling besar 64 MB. Gambar lama diganti dan langsung dipakai permainan.</p>
      </div>
      <div class="form-actions">
        <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah</button>
      </div>
    </form>

    <?php if ($isActive): ?>
      <section class="panel">
        <h2 class="panel-title"><?= icon('cross') ?> Nonaktifkan gambar</h2>
        <p class="muted">Permainan kembali menampilkan: <?= esc($slot['fallback'] ?? 'tampilan pengganti') ?>.</p>
        <form method="post" action="<?= base_url('admin/media/' . (int) $row['id'] . '/nonaktif') ?>" class="inline-form"
              data-confirm="Nonaktifkan <?= esc($key, 'attr') ?>?" data-confirm-text="Permainan memakai tampilan pengganti sampai berkas baru diunggah." data-confirm-ok="Nonaktifkan" data-confirm-style="danger">
          <?= csrf_field() ?>
          <input type="hidden" name="back" value="admin/media/<?= (int) $row['id'] ?>">
          <button class="btn btn-quiet" type="submit">Nonaktifkan</button>
        </form>
      </section>
    <?php endif ?>
  </div>
<?php endif ?>
<script type="application/json" id="asset-sizes"><?= json_encode(
    ['sizes' => config('Gelita')->assetSizes, 'strict' => true],
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
<?= $this->endSection() ?>
