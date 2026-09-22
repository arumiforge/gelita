<?php
/**
 * Bintang 0..3 (atau 0..$max). Ikon + teks alternatif: warna tidak pernah
 * menjadi satu-satunya penanda.
 *
 * @var int|null    $stars
 * @var int|null    $max
 * @var string|null $size   `lg` untuk layar selesai
 * @var bool|null   $label  tampilkan teks "2 / 3" di samping bintang
 */
$max   = max(1, (int) ($max ?? 3));
$stars = max(0, min($max, (int) ($stars ?? 0)));
?>
<span class="star-rating<?= ($size ?? '') === 'lg' ? ' stars-lg' : '' ?>">
  <?= stars_html($stars, $max) ?>
  <?php if (! empty($label)): ?>
    <span class="star-rating-text num" aria-hidden="true"><?= $stars ?>/<?= $max ?></span>
  <?php endif ?>
</span>
