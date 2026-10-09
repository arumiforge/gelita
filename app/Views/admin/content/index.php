<?php
/**
 * Tantangan — `/admin/konten` → ContentController::index (Dok 3 §8.3)
 *
 * Tabel 12 tantangan: wilayah, urutan, jenis, judul, variasi aktif (x/3),
 * soal, gambar contoh tersisa, status verifikasi (mode siap penelitian).
 * Baris membuka editor tantangan. Di bawahnya tabel wilayah dengan tautan
 * ke editor wilayah, bacaan, Pustaka, dan cerita.
 *
 * @var list<array<string, mixed>>  $rows
 * @var list<App\Entities\Level>    $levels
 * @var string                      $version
 */
$static = static fn (array $rows, array $columns): array => [
    'rows' => $rows, 'total' => count($rows), 'page' => 1, 'per_page' => max(1, count($rows)), 'sort' => null,
    'columns' => array_map(static fn (array $c): array => $c + ['sortable' => false], $columns),
];
$verifyBadge = ['ok' => 'is-ok', 'warning' => 'is-warn', 'error' => 'is-bad'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<form method="post" action="<?= base_url('admin/konten/verifikasi') ?>" class="inline-form">
  <?= csrf_field() ?>
  <button class="btn" type="submit"><?= icon('check') ?> Rincian verifikasi</button>
</form>
<a class="btn" href="<?= base_url('admin/konten/dialog/0') ?>"><?= icon('message') ?> Cerita pembuka &amp; penutup</a>
<a class="btn btn-primary" href="<?= base_url('admin/konten/impor-bank') ?>"><?= icon('upload') ?> Impor bank soal</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Tantangan',
    'lead'    => 'Dua belas tantangan, tiga variasi masing-masing. Versi konten ' . $version . '. Soal yang sudah pernah dijawab siswa tidak dapat dihapus, hanya dinonaktifkan.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?= component('data-table', [
    'table'        => $static($rows, [
        ['key' => 'ref', 'label' => 'Kode', 'type' => 'code'],
        ['key' => 'level', 'label' => 'Wilayah', 'type' => 'text'],
        ['key' => 'sequence', 'label' => 'Urutan', 'type' => 'int'],
        ['key' => 'engine', 'label' => 'Jenis', 'type' => 'text'],
        ['key' => 'title', 'label' => 'Judul', 'type' => 'text'],
        ['key' => 'variants', 'label' => 'Variasi aktif', 'type' => 'text'],
        ['key' => 'items', 'label' => 'Soal', 'type' => 'int'],
        ['key' => 'mockups', 'label' => 'Gambar contoh tersisa', 'type' => 'int'],
        ['key' => 'verify', 'label' => 'Verifikasi', 'type' => 'text'],
        ['key' => 'edit', 'label' => '', 'type' => 'text'],
    ]),
    'id'           => 'tantangan',
    'title'        => 'Tantangan',
    'csv'          => false,
    'paginate'     => false,
    'emptyMessage' => 'Belum ada tantangan aktif. Impor bank soal kelas 5 lebih dulu.',
    'render'       => [
        'ref'      => static fn (array $r): string => '<a href="' . base_url('admin/konten/node/' . $r['id']) . '">' . esc($r['ref']) . '</a>',
        'title'    => static fn (array $r): string => '<a href="' . base_url('admin/konten/node/' . $r['id']) . '">' . esc($r['title']) . '</a>',
        'variants' => static fn (array $r): string => '<span class="num' . (str_starts_with($r['variants'], '0/') ? ' size-bad' : '') . '">' . esc($r['variants']) . '</span>',
        'mockups'  => static fn (array $r): string => $r['mockups'] > 0 ? '<span class="badge is-mockup">' . (int) $r['mockups'] . '</span>' : '<span class="num">0</span>',
        'verify'   => static fn (array $r): string => '<span class="badge ' . $verifyBadge[$r['verify']] . '">' . esc($r['verify_text']) . '</span>',
        'edit'     => static fn (array $r): string => '<a class="btn btn-sm btn-quiet" href="' . base_url('admin/konten/node/' . $r['id']) . '">' . icon('edit') . ' Sunting</a>',
    ],
]) ?>

<?= component('data-table', [
    'table'    => $static(array_map(static fn ($level): array => [
        'id' => $level->id, 'sequence' => $level->sequence, 'name' => $level->text('name', 'id'),
        'difficulty' => $level->difficulty, 'status' => $level->is_active ? 'active' : 'inactive', 'links' => '',
    ], $levels), [
        ['key' => 'sequence', 'label' => 'Urutan', 'type' => 'int'],
        ['key' => 'name', 'label' => 'Wilayah', 'type' => 'text'],
        ['key' => 'difficulty', 'label' => 'Tingkat', 'type' => 'text'],
        ['key' => 'status', 'label' => 'Status', 'type' => 'text'],
        ['key' => 'links', 'label' => 'Sunting', 'type' => 'text'],
    ]),
    'id'       => 'wilayah',
    'title'    => 'Wilayah, bacaan, Pustaka, dan cerita',
    'csv'      => false,
    'paginate' => false,
    'render'   => [
        'status' => static fn (array $r): string => '<span class="badge is-' . $r['status'] . '">' . ($r['status'] === 'active' ? 'aktif' : 'nonaktif') . '</span>',
        'links'  => static fn (array $r): string => '<span class="btn-row">'
            . '<a class="btn btn-sm" href="' . base_url('admin/konten/level/' . $r['id']) . '">Wilayah</a>'
            . '<a class="btn btn-sm" href="' . base_url('admin/konten/bacaan/' . $r['id']) . '">Bacaan</a>'
            . '<a class="btn btn-sm" href="' . base_url('admin/konten/pustaka/' . $r['id']) . '">Pustaka &amp; Kenali</a>'
            . '<a class="btn btn-sm" href="' . base_url('admin/konten/dialog/' . $r['id']) . '">Cerita &amp; dialog</a></span>',
    ],
]) ?>
<?= $this->endSection() ?>
