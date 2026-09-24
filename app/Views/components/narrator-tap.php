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
 * @var string|null $hint
 */
?>
<div class="narrator-tap" data-narrator-tap hidden>
  <div class="narrator-tap-card">
    <div data-narrator-welcome></div>
    <button type="button" class="narrator-tap-btn">
      <span class="narrator-tap-glow" aria-hidden="true"><?= icon('lantern') ?></span>
      <span class="narrator-tap-label"><?= esc(lang('Game.tapToStart')) ?></span>
    </button>
    <p class="narrator-tap-hint"><?= icon('sound') ?> <?= esc($hint ?? lang('Game.tapToStartHint')) ?></p>
  </div>
</div>
