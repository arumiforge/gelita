<?php
/**
 * Kartu "Ketuk untuk mulai" layar bernarasi (game/narrator.js, mode `tap`).
 *
 * Browser (terutama Safari iPad) memblokir audio bersuara sampai pengguna
 * mengetuk halaman ITU, dan GELITA adalah aplikasi multi-halaman. Ketukan
 * kartu ini membuka kunci audio (Sfx.unlock()), lalu narasi diputar
 * otomatis. Tersembunyi tanpa JavaScript: slide tetap dapat dibaca.
 * Kartu sambutan setelah registrasi (bila ada) dipindah JS ke dalam slot
 * [data-narrator-welcome] agar tetap terbaca di atas lapisan ini.
 *
 * Tahap 3: kartu boleh berjudul — kartu bab dialog wilayah ("Bab {n} ·
 * {wilayah}", tagline, "Ketuk untuk mendengar kisahnya", jejak kaki yang
 * melanjutkan tirai wilayah) dan kartu wilayah tuntas ("Serpihan {wilayah}
 * kembali!").
 *
 * @var string|null $hint
 * @var string|null $eyebrow  baris kecil di atas judul, mis. "Bab 2 · Magelang"
 * @var string|null $title    judul kartu, mis. tagline wilayah
 * @var string|null $label    label tombol (bawaan "Ketuk untuk mulai")
 * @var string|null $variant  kelas `is-{variant}`, mis. `chapter`, `done`
 * @var bool|null   $trail    tampilkan jejak kaki (partials/footsteps)
 */
$variant = $variant ?? '';
?>
<div class="narrator-tap<?= $variant !== '' ? ' is-' . esc($variant, 'attr') : '' ?>" data-narrator-tap hidden>
  <div class="narrator-tap-card">
    <div data-narrator-welcome></div>
    <?php if (! empty($trail)): ?>
      <?= component('partials/footsteps', ['steps' => 6, 'class' => 'tap-trail']) ?>
    <?php endif ?>
    <?php if (($eyebrow ?? '') !== ''): ?>
      <span class="narrator-tap-eyebrow"><?= esc($eyebrow) ?></span>
    <?php endif ?>
    <?php if (($title ?? '') !== ''): ?>
      <h2 class="narrator-tap-title"><?= esc($title) ?></h2>
    <?php endif ?>
    <button type="button" class="narrator-tap-btn">
      <span class="narrator-tap-glow" aria-hidden="true"><?= icon('lantern') ?></span>
      <span class="narrator-tap-label"><?= esc($label ?? lang('Game.tapToStart')) ?></span>
    </button>
    <p class="narrator-tap-hint"><?= icon('sound') ?> <?= esc($hint ?? lang('Game.tapToStartHint')) ?></p>
  </div>
</div>
