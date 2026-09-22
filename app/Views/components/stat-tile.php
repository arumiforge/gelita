<?php
/**
 * Satu angka + label (KPI panel, statistik layar selesai & profil).
 *
 * @var string      $label
 * @var string|int|float $value  sudah diformat pemanggil
 * @var string|null $hint   keterangan kecil di bawah angka
 * @var string|null $icon   nama ikon (ui_helper)
 * @var string|null $id     id elemen angka, untuk diperbarui JavaScript
 */
$value = (string) ($value ?? '—');
// Nilai berupa kata (jenis kelamin, nama sekolah) memakai font isi, bukan mono besar
$isText = preg_match('/\p{L}{3,}/u', $value) === 1;
?>
<div class="stat-tile">
  <span class="stat-label"><?php if (! empty($icon)): ?><?= icon($icon) ?> <?php endif ?><?= esc($label ?? '') ?></span>
  <p class="stat-value<?= $isText ? ' is-text' : '' ?>"<?= ! empty($id) ? ' id="' . esc($id, 'attr') . '"' : '' ?>><?= esc($value) ?></p>
  <?php if (! empty($hint)): ?>
    <span class="stat-hint"><?= esc($hint) ?></span>
  <?php endif ?>
</div>
