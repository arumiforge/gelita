<?php
/**
 * Penjelasan Pustaka yang masih terkunci: progres tantangan wilayah (bar +
 * teks x/5) dan kalimat "Selesaikan kelima tantangan di {wilayah}…".
 * Dipakai kartu rak `/pustaka` dan halaman terkunci `/pustaka/{code}`,
 * sehingga keduanya selalu berkata sama. Modal peta wilayah (map.js)
 * membaca kalimat yang sama dari atribut data tombol Pustaka.
 *
 * @var string $region nama wilayah sesuai bahasa sesi
 * @var int    $done   tantangan wilayah yang sudah selesai
 * @var int    $total  jumlah tantangan wilayah
 */
$pct = $total > 0 ? (int) round($done / $total * 100) : 0;
?>
<div class="library-lock">
  <div class="library-lock-progress">
    <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="<?= (int) $total ?>" aria-valuenow="<?= (int) $done ?>"
         aria-label="<?= esc(lang('Game.nodesProgress', [$done, $total]), 'attr') ?>"><i style="width: <?= $pct ?>%"></i></div>
    <span class="num"><?= esc(lang('Game.nodesProgress', [$done, $total])) ?></span>
  </div>
  <p><?= esc(lang('Game.libraryLockedText', [$region, $total])) ?></p>
</div>
