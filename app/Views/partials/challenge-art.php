<?php
/**
 * Visual tirai tantangan per engine (Tahap 3, components/curtain `challenge`).
 * Dekoratif; gerakannya di cinematic.css `.cc-*`, dimatikan bila gerak
 * dikurangi (tampil sebagai keadaan akhirnya).
 *
 * - puzzle   keping-keping menyatu
 * - rumpang  huruf jatuh mengisi celah kalimat
 * - boleh    kartu berputar antara ✓ dan ✗
 * - pilihan  tiga kartu mengipas lalu satu bersinar
 * - cari     sorot lentera menyapu siluet gelap
 *
 * @var string $engine
 */
?>
<div class="cc-art cc-<?= esc($engine, 'attr') ?>" aria-hidden="true">
  <?php if ($engine === 'puzzle'): ?>
    <?php for ($i = 0; $i < 9; $i++): ?><i style="--i: <?= $i ?>"></i><?php endfor ?>
  <?php elseif ($engine === 'rumpang'): ?>
    <span class="cc-line"></span>
    <span class="cc-gap"><?php foreach (['K', 'E', 'D', 'U'] as $i => $letter): ?><b style="--i: <?= $i ?>"><?= $letter ?></b><?php endforeach ?></span>
    <span class="cc-line is-short"></span>
  <?php elseif ($engine === 'boleh'): ?>
    <span class="cc-card">
      <b class="cc-face is-ok"><?= icon('check') ?></b>
      <b class="cc-face is-bad"><?= icon('cross') ?></b>
    </span>
  <?php elseif ($engine === 'pilihan'): ?>
    <?php for ($i = 0; $i < 3; $i++): ?><i style="--i: <?= $i ?>"></i><?php endfor ?>
  <?php else: ?>
    <span class="cc-shape is-temple"></span><span class="cc-shape is-mask"></span><span class="cc-shape is-drum"></span>
    <span class="cc-light"></span>
  <?php endif ?>
</div>
