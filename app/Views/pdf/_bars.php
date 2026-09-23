<?php
/**
 * Chart bar horizontal untuk PDF.
 *
 * mPDF mengabaikan lebar persen pada <div> di dalam sel tabel, jadi bar
 * digambar sebagai tabel bersarang: sel berwarna selebar nilainya + sel
 * latar untuk sisanya.
 *
 * @var list<array{label: string, value: float, text: string, sub?: string}> $bars nilai 0–100
 * @var bool                                                                 $alt  warna kedua
 * @var string                                                               $caption
 */
$alt ??= false;
$fill = $alt ? '#4f7a6a' : '#b0703a';
?>
<table class="bars" summary="<?= esc($caption, 'attr') ?>">
  <?php foreach ($bars as $bar): ?>
    <?php $width = round(max(0, min(100, (float) $bar['value'])), 1); ?>
    <tr>
      <td class="label"><?= esc($bar['label']) ?><?php if (! empty($bar['sub'])): ?><br><span class="muted small"><?= esc($bar['sub']) ?></span><?php endif ?></td>
      <td>
        <table class="bar" width="100%" cellpadding="0" cellspacing="0"><tr>
          <?php if ($width > 0): ?><td width="<?= number_format($width, 1, '.', '') ?>%" style="background-color: <?= $fill ?>; height: 3.4mm;"></td><?php endif ?>
          <?php if ($width < 100): ?><td width="<?= number_format(100 - $width, 1, '.', '') ?>%" style="background-color: #efe7da; height: 3.4mm;"></td><?php endif ?>
        </tr></table>
      </td>
      <td class="value"><?= esc($bar['text']) ?></td>
    </tr>
  <?php endforeach ?>
</table>
