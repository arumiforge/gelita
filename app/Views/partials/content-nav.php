<?php
/**
 * Navigasi sub-halaman konten satu wilayah.
 *
 * @var App\Entities\Level $level
 * @var string             $active level | passages | library | dialogues
 */
$links = [
    'level'     => ['admin/konten/level/', 'Wilayah & tantangan', 'map'],
    'passages'  => ['admin/konten/bacaan/', 'Teks bacaan', 'text'],
    'library'   => ['admin/konten/pustaka/', 'Pustaka', 'book'],
    'dialogues' => ['admin/konten/dialog/', 'Dialog', 'message'],
];
?>
<nav aria-label="Konten wilayah <?= esc($level->text('name', 'id'), 'attr') ?>">
  <ul class="sub-nav">
    <li><a href="<?= base_url('admin/konten') ?>"><?= icon('left') ?> Semua konten</a></li>
    <?php foreach ($links as $key => [$path, $label, $iconName]): ?>
      <li><a href="<?= base_url($path . $level->id) ?>" <?= $active === $key ? 'aria-current="page"' : '' ?>><?= icon($iconName) ?> <?= esc($label) ?></a></li>
    <?php endforeach ?>
  </ul>
</nav>
