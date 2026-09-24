<?php
/**
 * Kredit satu media Pustaka: ikon ⓘ bulat di pojok kanan atas bingkai yang
 * membuka panel kecil berisi teks kredit dan tautan "Lihat sumber ↗".
 *
 * `<details>` dipilih agar berjalan tanpa JavaScript. library.js menambah:
 * membuka satu menutup yang lain, ketuk di luar atau Esc menutup. Elemen ini
 * saudara `.book-zoom`, bukan anaknya, sehingga mengetuk ⓘ tidak memperbesar
 * gambar dan tidak ada elemen interaktif bersarang di dalam <button>.
 *
 * @var string      $credit teks kredit ('' bila tidak ada)
 * @var string|null $source halaman sumber media pihak lain; null untuk unggahan
 */
?>
<details class="media-credit">
  <summary aria-label="<?= esc(lang('Game.libraryCredit'), 'attr') ?>" title="<?= esc(lang('Game.libraryCredit'), 'attr') ?>"><span aria-hidden="true">i</span></summary>
  <div class="media-credit-panel">
    <?php if ($credit !== ''): ?>
      <p><?= esc($credit) ?></p>
    <?php endif ?>
    <?php if ($source !== null): ?>
      <a href="<?= esc($source, 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc(lang('Game.libraryViewSource')) ?> <span aria-hidden="true">↗</span></a>
    <?php endif ?>
  </div>
</details>
