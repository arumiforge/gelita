<?php
/**
 * Navigasi slide cerita/dialog: Kembali · titik halaman · Lanjut.
 * Jangkar #{prefix}{n} + CSS :target — berfungsi tanpa JavaScript.
 * Slide terakhir berganti menjadi tombol menuju $finalUrl.
 *
 * @var int    $n
 * @var int    $total
 * @var string $prefix
 * @var string $finalUrl
 * @var string $final
 */
?>
<nav class="slide-nav" aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
  <?php if ($n > 1): ?>
    <a class="btn btn-quiet" href="#<?= esc($prefix . ($n - 1), 'attr') ?>"><?= icon('left') ?> <?= esc(lang('Game.back')) ?></a>
  <?php else: ?>
    <span class="slide-nav-spacer"></span>
  <?php endif ?>

  <ol class="slide-dots">
    <?php for ($i = 1; $i <= $total; $i++): ?>
      <li>
        <a class="slide-dot<?= $i === $n ? ' is-current' : '' ?>" href="#<?= esc($prefix . $i, 'attr') ?>"
           <?= $i === $n ? 'aria-current="step"' : '' ?>><span class="visually-hidden"><?= esc(lang('Game.slideOf', [$i, $total])) ?></span></a>
      </li>
    <?php endfor ?>
  </ol>

  <?php if ($n < $total): ?>
    <a class="btn btn-primary" href="#<?= esc($prefix . ($n + 1), 'attr') ?>"><?= esc(lang('Game.continue')) ?> <?= icon('right') ?></a>
  <?php else: ?>
    <a class="btn btn-primary" href="<?= esc($finalUrl, 'attr') ?>"><?= esc($final) ?> <?= icon('right') ?></a>
  <?php endif ?>
</nav>
