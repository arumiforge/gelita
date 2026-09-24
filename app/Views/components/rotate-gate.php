<?php
/**
 * Layar putar — menutup seluruh layar selama perangkat sentuh dipegang
 * tegak (potret). Aset GELITA hanya dibuat satu versi mendatar (peta
 * 1400×900, adegan 16:9, latar 1920×1080), jadi permainan hanya dilayani
 * dalam posisi mendatar.
 *
 * Tampil/hilangnya murni CSS (layout.css, media query
 * `(orientation: portrait) and (pointer: coarse)`), sehingga berfungsi
 * tanpa JavaScript dan langsung hilang saat perangkat diputar. Web tidak
 * dapat mengunci rotasi dengan andal: screen.orientation.lock() hanya
 * berlaku dalam layar penuh di Android — dan layar penuh lepas setiap kali
 * halaman berganti — sedangkan iOS tidak mendukungnya. Tidak ada tombol
 * lewati.
 *
 * game/rotate-gate.js hanya menjaga aksesibilitas: isi di belakang dibuat
 * `inert` dan fokus dipindah ke sini selama penghalang tampil.
 */
?>
<div class="rotate-gate" role="alertdialog" aria-modal="true" aria-labelledby="rotate-title" aria-describedby="rotate-text" tabindex="-1">
  <div class="rotate-gate-card">
    <svg class="rotate-gate-art" viewBox="0 0 120 120" width="120" height="120" fill="none" stroke="currentColor"
         stroke-width="4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
      <path class="rotate-gate-arrow" d="M22 50a40 40 0 0 1 62-28"/>
      <path class="rotate-gate-arrow" d="M76 14l9 8-10 6"/>
      <g class="rotate-gate-phone">
        <rect x="42" y="36" width="36" height="64" rx="7"/>
        <path d="M56 90h8"/>
      </g>
    </svg>
    <h2 class="rotate-gate-title" id="rotate-title"><?= esc(lang('Game.rotateTitle')) ?></h2>
    <p class="rotate-gate-text" id="rotate-text"><?= esc(lang('Game.rotateText')) ?></p>
    <p class="rotate-gate-stuck"><?= icon('info') ?> <span><?= esc(lang('Game.rotateStuck')) ?></span></p>
  </div>
</div>
