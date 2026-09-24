<?php
/**
 * Jejak kaki kiri–kanan yang muncul bergantian (Tahap 3): tirai wilayah
 * `region` dan kartu bab dialog wilayah, yang melanjutkan langkah tirainya.
 * Dekoratif (aria-hidden); cinematic.css `.footsteps` mengatur urutan
 * munculnya lewat `--i`. Gerak dikurangi: jejak tampil diam.
 *
 * @var int|null    $steps  jumlah jejak (bawaan 8)
 * @var string|null $class  kelas tambahan
 */
$steps = max(2, (int) ($steps ?? 8));
?>
<div class="footsteps <?= esc($class ?? '', 'attr') ?>" aria-hidden="true" style="--steps: <?= $steps ?>">
  <?php for ($i = 0; $i < $steps; $i++): ?>
    <svg class="footstep <?= $i % 2 === 0 ? 'is-left' : 'is-right' ?>" style="--i: <?= $i ?>" viewBox="0 0 24 40" width="24" height="40" focusable="false">
      <path d="M12 13c4.6 0 7.4 3.6 7.4 9.2 0 4-1.5 6.6-2.6 9.4-1 2.7-2.4 5.4-4.8 5.4s-3.8-2.7-4.8-5.4C6.1 28.8 4.6 26.2 4.6 22.2 4.6 16.6 7.4 13 12 13z"/>
      <circle cx="5.2" cy="8.6" r="2"/><circle cx="8.6" cy="5.4" r="2.2"/><circle cx="12.6" cy="4.2" r="2.4"/><circle cx="16.8" cy="5.2" r="2.2"/><circle cx="19.8" cy="8.4" r="1.9"/>
    </svg>
  <?php endfor ?>
</div>
