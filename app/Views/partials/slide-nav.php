<?php
/**
 * Navigasi slide cerita/dialog: Kembali · titik halaman · Lanjut.
 * Jangkar #{prefix}{n} + CSS :target — berfungsi tanpa JavaScript.
 * Slide terakhir berganti menjadi tombol menuju $finalUrl.
 *
 * Tahap 3: tombol akhir boleh membawa atribut tambahan ($finalAttrs, mis.
 * tirai wilayah dari curtain_attrs(), sudah di-escape pemanggil) dan ikon
 * lain ($finalIcon); $extra menambah tombol sekunder di depannya (mis.
 * "Baca Pustaka {wilayah}" di adegan wilayah tuntas). Tombol akhir ditandai
 * `data-slide-final` agar pemutar narasi dapat mengikutinya.
 *
 * @var int                                                                     $n
 * @var int                                                                     $total
 * @var string                                                                  $prefix
 * @var string                                                                  $finalUrl
 * @var string                                                                  $final
 * @var string|null                                                             $finalAttrs
 * @var string|null                                                             $finalIcon
 * @var list<array{label: string, href: string, icon?: string, attrs?: string}>|null $extra
 */
$finalIcon ??= 'right';
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
    <?php if (! empty($extra)): ?>
      <span class="slide-nav-final">
        <?php foreach ($extra as $button): ?>
          <a class="btn btn-ghost" href="<?= esc($button['href'], 'attr') ?>"<?= $button['attrs'] ?? '' ?>><?php if (! empty($button['icon'])): ?><?= icon($button['icon']) ?> <?php endif ?><?= esc($button['label']) ?></a>
        <?php endforeach ?>
        <a class="btn btn-primary" href="<?= esc($finalUrl, 'attr') ?>" data-slide-final<?= $finalAttrs ?? '' ?>><?= esc($final) ?> <?= icon($finalIcon) ?></a>
      </span>
    <?php else: ?>
      <a class="btn btn-primary" href="<?= esc($finalUrl, 'attr') ?>" data-slide-final<?= $finalAttrs ?? '' ?>><?= esc($final) ?> <?= icon($finalIcon) ?></a>
    <?php endif ?>
  <?php endif ?>
</nav>
