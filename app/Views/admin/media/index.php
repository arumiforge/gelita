<?php
/**
 * Gambar & mockup — `/admin/media` → MediaController::index (Dok 3 §8.3)
 *
 * Tabel slot: pratinjau, kode, label (link to the slot detail page), isi yang diharapkan (`description`),
 * ukuran tampil & berkas, status (Asli / Gambar contoh / Kosong), dipakai
 * di; filter status dan jenis; tombol **Unggah gambar asli** per slot
 * (admin/media-upload.js mengisi kotak unggah; tanpa JavaScript, tautan
 * `?asset_key=` mengisi kode di kotak unggah). Ukuran wajib per slot
 * diperiksa client lalu dibaca ulang server dengan getimagesize().
 *
 * @var list<array<string, mixed>>        $assets
 * @var array<string, array{0: int, 1: int}> $assetSizes pola asset_key → [lebar, tinggi] berkas (px)
 * @var array<string, array{0: int, 1: int}> $displaySizes pola asset_key → ukuran tampil di panggung 1280 × 720 (dp, Dok 3 §6)
 * @var array<int, list<array{label: string, path: string|null, edit: string|null}>> $usage media_asset_id → uses (MediaUsage)
 * @var array{at: string, findings: list<array{asset_key: string, issue: string}>}|null $scan
 * @var string                                $status     '' | asli | mockup | kosong
 * @var string                                $type       '' | image | video
 * @var string                                $group      '' | ImageSlots::GROUPS code
 * @var string                                $region     '' | region code
 * @var string                                $node       '' | challenge ref (tmg-1), set by the checklist link
 * @var string                                $query      free text on key, label, description
 *
 * Label and "Isi yang diharapkan" fall back to App\Libraries\ImageSlots for
 * official slots (logo, backgrounds, maps, characters) whose rows carry no
 * label; challenge images keep the text from the question bank.
 */
use App\Libraries\ImageSlots;

$sizeFrom = static function (array $sizes, string $key): ?array {
    foreach ($sizes as $pattern => $size) {
        if ($pattern === $key || fnmatch($pattern, $key)) {
            return $size;
        }
    }

    return null;
};
$requiredFor = static fn (string $key): ?array => $sizeFrom($assetSizes, $key);
$displayFor  = static fn (string $key): ?array => $sizeFrom($displaySizes ?? [], $key);
$slotStatus = static fn (array $row): string => ! $row['is_active'] ? 'kosong' : ((int) ($row['is_placeholder'] ?? 0) === 1 ? 'mockup' : 'asli');
$visual     = array_values(array_filter($assets, static fn (array $row): bool => $row['asset_type'] !== 'audio'));
$counts     = ['asli' => 0, 'mockup' => 0, 'kosong' => 0];
$wrong      = 0;

foreach ($visual as $row) {
    $counts[$slotStatus($row)]++;
    $need = $requiredFor((string) $row['asset_key']);

    if ($need !== null && $row['width_px'] !== null && ((int) $row['width_px'] !== $need[0] || (int) $row['height_px'] !== $need[1])) {
        $wrong++;
    }
}

// Official slot text where the row has none
$visual = array_map(static function (array $row): array {
    $slot               = ImageSlots::describe((string) $row['asset_key']);
    $row['slot_group']  = ImageSlots::groupOf((string) $row['asset_key']);
    $row['slot_region'] = ImageSlots::regionOf((string) $row['asset_key']);
    $row['label']       = trim((string) ($row['label'] ?? '')) !== '' ? $row['label'] : ($slot['label'] ?? null);
    $row['description'] = trim((string) ($row['description'] ?? '')) !== '' ? $row['description'] : ($slot['content'] ?? null);
    $row['priority']    = $slot['priority'] ?? null;

    return $row;
}, $visual);
$needle = mb_strtolower($query ?? '');
$shown  = array_values(array_filter($visual, static fn (array $row): bool => ($status === '' || $slotStatus($row) === $status)
    && ($type === '' || $row['asset_type'] === $type)
    && (($group ?? '') === '' || $row['slot_group'] === $group)
    && (($region ?? '') === '' || $row['slot_region'] === $region)
    && (($node ?? '') === '' || preg_match('/^challenge\.(?:item\.)?' . preg_quote($node, '/') . '(?:-|\.)/', (string) $row['asset_key']) === 1)
    && ($needle === '' || str_contains(mb_strtolower($row['asset_key'] . ' ' . $row['label'] . ' ' . $row['description']), $needle))));
$groupCounts = array_count_values(array_column($visual, 'slot_group'));
$filtered    = $status !== '' || $type !== '' || ($group ?? '') !== '' || ($region ?? '') !== '' || ($node ?? '') !== '' || $needle !== '';
$withoutFilter = static function (string $name) use ($status, $type, $group, $region, $node, $query): string {
    $params = array_filter(['status' => $status, 'jenis' => $type, 'kelompok' => $group, 'wilayah' => $region, 'tantangan' => $node, 'cari' => $query], static fn ($v): bool => $v !== '' && $v !== null);
    unset($params[$name]);

    return base_url('admin/media') . ($params === [] ? '' : '?' . http_build_query($params));
};
$labels = ['asli' => 'Asli', 'mockup' => 'Gambar contoh', 'kosong' => 'Kosong'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/media/pindai') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn" type="submit"><?= icon('search') ?> Cek berkas hilang/rusak</button>
</form>
<a class="btn" href="<?= base_url('admin/media/audio') ?>"><?= icon('sound') ?> Rekaman suara</a>
<a class="btn btn-primary" href="#unggah"><?= icon('upload') ?> Unggah dengan kode baru</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Gambar & mockup',
    'lead'    => 'Setiap slot punya kode berkas (mis. challenge.tmg-4.scene) yang menentukan tempat gambar tampil. Buka Detail untuk melihat isi gambar yang diharapkan, ukuran, dan halaman permainan tempat gambar itu tampil, lalu unggah dari sana. Prompt pembuatan gambar ada di Katalog Aset tim (cari dengan kode berkasnya). Gambar contoh (mockup) harus diganti gambar asli sebelum pengambilan data.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin/partials/package-notice', ['packageChanged' => $packageChanged ?? null]) ?>

<?php if (is_array($scan)): ?>
  <section class="panel">
    <h2 class="panel-title"><?= icon('search') ?> Hasil pengecekan berkas <span class="muted"><?= esc(fmt_date($scan['at'], true, 'id')) ?></span></h2>
    <?php if ($scan['findings'] === []): ?>
      <div class="alert alert-ok" role="status"><?= icon('check') ?><p>Semua berkas lengkap dan utuh.</p></div>
    <?php else: ?>
      <div class="alert alert-error" role="alert"><?= icon('warn') ?><p><?= count($scan['findings']) ?> berkas bermasalah. Unggah ulang berkasnya, atau nonaktifkan bila tidak dipakai lagi.</p></div>
      <?= component('components/admin-table', [
          'caption'  => 'Berkas yang bermasalah',
          'columns'  => ['asset_key' => ['label' => 'Kode berkas', 'format' => 'code'], 'issue' => 'Masalah'],
          'rows'     => $scan['findings'],
      ]) ?>
    <?php endif ?>
  </section>
<?php endif ?>

<section class="kpi-grid" aria-label="Jumlah slot">
  <?= component('components/stat-tile', ['label' => 'Slot gambar & video', 'value' => fmt_num(count($visual), 0, 'id'), 'icon' => 'image']) ?>
  <?= component('components/stat-tile', ['label' => 'Gambar asli', 'value' => fmt_num($counts['asli'], 0, 'id'), 'icon' => 'check']) ?>
  <?= component('components/stat-tile', ['label' => 'Gambar contoh', 'value' => fmt_num($counts['mockup'], 0, 'id'), 'icon' => 'warn', 'hint' => 'harus 0 sebelum penelitian berjalan']) ?>
  <?= component('components/stat-tile', ['label' => 'Kosong', 'value' => fmt_num($counts['kosong'], 0, 'id'), 'icon' => 'info', 'hint' => 'permainan memakai tampilan pengganti']) ?>
  <?= component('components/stat-tile', ['label' => 'Ukuran tidak sesuai', 'value' => fmt_num($wrong, 0, 'id'), 'icon' => 'cross']) ?>
</section>

<form method="get" class="filter-bar" aria-label="Filter slot">
  <div class="field">
    <label for="f-cari">Cari</label>
    <input type="search" id="f-cari" name="cari" value="<?= esc($query ?? '', 'attr') ?>" maxlength="80" placeholder="kode, nama, atau isi gambar">
  </div>
  <div class="field">
    <label for="f-kelompok">Kelompok</label>
    <select id="f-kelompok" name="kelompok">
      <option value="">Semua kelompok</option>
      <?php foreach (ImageSlots::GROUPS as $code => $label): ?>
        <?php if (isset($groupCounts[$code])): ?>
          <option value="<?= $code ?>"<?= ($group ?? '') === $code ? ' selected' : '' ?>><?= esc($label) ?> (<?= $groupCounts[$code] ?>)</option>
        <?php endif ?>
      <?php endforeach ?>
    </select>
  </div>
  <div class="field">
    <label for="f-wilayah">Wilayah</label>
    <select id="f-wilayah" name="wilayah">
      <option value="">Semua wilayah</option>
      <?php foreach (config('Gelita')->levelPrefixes as $code): ?>
        <option value="<?= $code ?>"<?= ($region ?? '') === $code ? ' selected' : '' ?>><?= esc(ucfirst($code)) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <?php if (($node ?? '') !== ''): ?><input type="hidden" name="tantangan" value="<?= esc($node, 'attr') ?>"><?php endif ?>
  <div class="field">
    <label for="f-status">Status</label>
    <select id="f-status" name="status">
      <option value="">Semua status</option>
      <?php foreach ($labels as $code => $label): ?>
        <option value="<?= $code ?>"<?= $status === $code ? ' selected' : '' ?>><?= esc($label) ?> (<?= $counts[$code] ?>)</option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="field">
    <label for="f-jenis">Jenis</label>
    <select id="f-jenis" name="jenis">
      <option value="">Gambar & video</option>
      <option value="image"<?= $type === 'image' ? ' selected' : '' ?>>Gambar</option>
      <option value="video"<?= $type === 'video' ? ' selected' : '' ?>>Video</option>
    </select>
  </div>
  <div class="filter-actions">
    <?php if ($filtered): ?>
      <a class="btn btn-quiet" href="<?= base_url('admin/media') ?>">Atur ulang</a>
    <?php endif ?>
    <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Terapkan</button>
  </div>
  <?php if ($filtered): ?>
    <div class="filter-chips" aria-label="Filter aktif">
      <span class="filter-chips-label">Filter aktif:</span>
      <?php foreach ([
          'cari'      => ['Cari', $query ?? ''],
          'kelompok'  => ['Kelompok', ImageSlots::GROUPS[$group ?? ''] ?? ''],
          'wilayah'   => ['Wilayah', ucfirst($region ?? '')],
          'tantangan' => ['Tantangan', $node ?? ''],
          'status'    => ['Status', $labels[$status] ?? ''],
          'jenis'     => ['Jenis', $type === 'image' ? 'Gambar' : ($type === 'video' ? 'Video' : '')],
      ] as $name => [$title, $value]): ?>
        <?php if ($value !== ''): ?>
          <span class="chip filter-chip"><?= esc($title) ?>: <b><?= esc($value) ?></b> <a href="<?= esc($withoutFilter($name), 'attr') ?>" aria-label="Lepas filter <?= esc($title, 'attr') ?>"><?= icon('cross') ?></a></span>
        <?php endif ?>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</form>

<?= component('data-table', [
    'table' => [
        'rows' => array_map(static fn (array $row): array => $row + ['slot_status' => $slotStatus($row)], $shown),
        'total' => count($shown), 'page' => 1, 'per_page' => max(1, count($shown)), 'sort' => null,
        'columns' => array_map(static fn (array $c): array => $c + ['sortable' => false], [
            ['key' => 'thumb', 'label' => 'Pratinjau', 'type' => 'text'],
            ['key' => 'asset_key', 'label' => 'Kode berkas', 'type' => 'code'],
            ['key' => 'label', 'label' => 'Label', 'type' => 'text'],
            ['key' => 'description', 'label' => 'Isi yang diharapkan', 'type' => 'clip'],
            ['key' => 'size', 'label' => 'Ukuran tampil & berkas', 'type' => 'text'],
            ['key' => 'slot_status', 'label' => 'Status', 'type' => 'text'],
            ['key' => 'usage', 'label' => 'Dipakai di', 'type' => 'text'],
            ['key' => 'actions', 'label' => '', 'type' => 'text'],
        ]),
    ],
    'id'           => 'slot-media',
    'title'        => 'Slot gambar & video',
    'csv'          => false,
    'paginate'     => false,
    'emptyMessage' => 'Tidak ada slot dengan status ini.',
    'render'       => [
        // The label opens the slot detail page (the action column can sit off-screen in the wide table)
        'label'     => static fn (array $row): string => '<a href="' . esc(base_url('admin/media/' . (int) $row['id']), 'attr') . '">' . esc((string) ($row['label'] ?? '')) . '</a>',
        'thumb' => static function (array $row): string {
            // Slot kosong: jangan minta berkas ke server
            // Character frames are stored as sprite_frame, still images to preview
            if (! in_array($row['asset_type'], ['image', 'sprite_frame'], true) || ! $row['is_active']) {
                return '<span class="media-thumb is-empty">' . icon($row['asset_type'] === 'video' ? 'video' : 'image') . '</span>';
            }

            $src = esc(base_url(App\Libraries\MediaStore::versionedPath((string) $row['storage_path'], $row['sha256'] ?? null)), 'attr');

            // Opens full size in the panel lightbox (admin.js), or in a new tab without JavaScript
            return '<a class="media-thumb" href="' . $src . '" target="_blank" rel="noopener" data-zoom="' . $src . '" data-zoom-caption="' . esc((string) $row['asset_key'], 'attr') . '" aria-label="Perbesar ' . esc((string) $row['asset_key'], 'attr') . '"><img src="' . $src . '" alt="" loading="lazy" decoding="async"></a>';
        },
        'size' => static function (array $row) use ($requiredFor, $displayFor): string {
            $need = $requiredFor((string) $row['asset_key']);
            $show = $displayFor((string) $row['asset_key']);
            $dp   = $show === null ? '' : '<span class="cell-sub">tampil ' . ($show[0] === 0 ? 'selebar w% adegan' : $show[0] . ' × ' . $show[1] . ' dp') . '</span>';
            $has  = $row['width_px'] === null ? null : [(int) $row['width_px'], (int) $row['height_px']];
            $kb   = $row['file_size'] === null ? '' : ' · ' . fmt_num(((int) $row['file_size']) / 1024, 0, 'id') . ' KB';
            $html = $need === null ? '<span class="cell-sub">ukuran bebas</span>' : '<span class="cell-sub">wajib ' . $need[0] . ' × ' . $need[1] . '</span>';

            if ($has === null) {
                return '<span class="is-empty">—</span>' . $html . $dp;
            }

            $ok = $need === null || $has === $need;

            return '<span class="num ' . ($ok ? '' : 'size-bad') . '">' . esc($has[0] . ' × ' . $has[1] . $kb) . '</span>' . $html . $dp;
        },
        'slot_status' => static fn (array $row): string => '<span class="badge is-' . $row['slot_status'] . '">' . esc($labels[$row['slot_status']]) . '</span>',
        'usage' => static function (array $row) use ($usage): string {
            $slot  = ImageSlots::describe((string) $row['asset_key']);
            $where = $usage[(int) $row['id']] ?? [];

            if ($slot !== null && $slot['priority'] === 'unused') {
                return '<span class="is-empty">belum dipakai permainan</span>';
            }

            // Official slots list their screens; content rows list their uses
            $lines = $where !== [] ? array_map(static fn (array $u): array => [$u['label'], $u['path']], $where) : ($slot['screens'] ?? []);

            if ($lines === []) {
                return '<span class="is-empty">belum dipakai</span>';
            }

            $html = '';

            foreach (array_slice($lines, 0, 3) as [$label, $path]) {
                $html .= '<span class="cell-sub">' . esc($label) . ($path === null ? '' : ' <code>' . esc($path) . '</code>') . '</span>';
            }

            return $html . (count($lines) > 3 ? '<span class="cell-sub">dan ' . (count($lines) - 3) . ' tempat lain (lihat Detail)</span>' : '');
        },
        'actions' => static function (array $row): string {
            $key    = (string) $row['asset_key'];
            $detail = '<a class="btn btn-sm" href="' . esc(base_url('admin/media/' . (int) $row['id']), 'attr') . '">' . icon('info') . ' Detail</a>';
            $upload = $detail . '<button type="button" class="btn btn-sm' . ($row['is_active'] && (int) ($row['is_placeholder'] ?? 0) === 0 ? '' : ' btn-primary') . '" data-upload-key="' . esc($key, 'attr') . '" hidden>'
                . icon('upload') . ' Unggah gambar asli</button>'
                . '<a class="btn btn-sm no-js-only" href="' . esc(base_url('admin/media?asset_key=' . rawurlencode($key)) . '#unggah', 'attr') . '">' . icon('upload') . ' Unggah</a>';

            if (! $row['is_active']) {
                return '<span class="actions-cell">' . $upload . '</span>';
            }

            return '<span class="actions-cell">' . $upload . '<form method="post" action="' . esc(base_url('admin/media/' . $row['id'] . '/nonaktif'), 'attr') . '" class="inline-form"'
                . ' data-confirm="Nonaktifkan ' . esc($key, 'attr') . '?" data-confirm-text="Permainan memakai tampilan pengganti sampai berkas baru diunggah." data-confirm-ok="Nonaktifkan" data-confirm-style="danger">'
                . csrf_field()
                . '<button class="btn btn-quiet btn-sm" type="submit">Nonaktifkan</button></form></span>';
        },
    ],
]) ?>

<div class="split-grid" id="unggah">
  <form method="post" action="<?= base_url('admin/media/unggah') ?>" class="upload-box" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <h2 class="panel-title"><?= icon('upload') ?> Unggah gambar atau video</h2>
    <div class="field">
      <label for="asset_key">Kode berkas <span class="req">*</span></label>
      <input type="text" id="asset_key" name="asset_key" required maxlength="160" placeholder="bg.temanggung" spellcheck="false" list="asset-keys" class="mono" value="<?= esc((string) (service('request')->getGet('asset_key') ?? ''), 'attr') ?>">
      <datalist id="asset-keys">
        <?php foreach ($assets as $asset): ?>
          <option value="<?= esc($asset['asset_key'], 'attr') ?>"></option>
        <?php endforeach ?>
      </datalist>
      <p class="field-help">Pilih kode dari daftar untuk mengganti gambarnya, atau tulis kode baru. Unggahan selalu tercatat sebagai gambar asli.</p>
    </div>
    <div class="field">
      <label for="file">Berkas <span class="req">*</span></label>
      <input type="file" id="file" name="file" required accept="image/png,image/jpeg,image/webp,image/gif,image/svg+xml,video/mp4,video/webm,video/ogg">
      <p class="field-help">Gambar PNG, JPG, WebP, GIF, atau SVG; video MP4, WebM, atau OGV. Paling besar 64 MB.</p>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('upload') ?> Unggah</button>
    </div>
  </form>

  <section class="panel">
    <h2 class="panel-title"><?= icon('grid') ?> Ukuran gambar per slot</h2>
    <p class="muted">Ukuran berkas tiap slot dan ukuran tampilnya di panggung 1280 × 720 dp. Gambar yang lebih kecil, atau yang bentuknya (perbandingan lebar dan tinggi) berbeda, ditolak saat diunggah. Gambar yang lebih besar dengan bentuk sama diperkecil otomatis ke ukuran slot. Tanda * berarti semua kode yang diawali tulisan itu.</p>
    <?= component('components/admin-table', [
        'caption' => 'Ukuran gambar per slot',
        'columns' => ['pattern' => ['label' => 'Kode berkas', 'format' => 'code'], 'size' => 'Berkas (px)', 'display' => 'Tampil di panggung (dp)'],
        'rows'    => array_map(static function (string $pattern, array $size) use ($displayFor): array {
            $show = $displayFor($pattern);

            return ['pattern' => $pattern, 'size' => $size[0] . ' × ' . $size[1],
                'display' => $show === null ? null : ($show[0] === 0 ? 'selebar w% adegan' : $show[0] . ' × ' . $show[1])];
        }, array_keys($assetSizes), $assetSizes),
    ]) ?>
  </section>
</div>
<script type="application/json" id="asset-sizes"><?= json_encode(
    ['sizes' => $assetSizes, 'strict' => true, 'types' => ImageSlots::typeRules()],
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
<?= $this->endSection() ?>
