<?php
/**
 * Navigasi halaman untuk hasil paginate() CodeIgniter.
 *
 * Tautan dibangun dengan Pager::getPageURI(), sehingga filter pada query
 * string ikut terbawa ke halaman berikutnya.
 *
 * @var CodeIgniter\Pager\Pager|null $pager
 */
if (! isset($pager) || ! $pager instanceof CodeIgniter\Pager\Pager) {
    return;
}

$current = $pager->getCurrentPage();
$count   = max(1, $pager->getPageCount());
$total   = $pager->getTotal();
$perPage = max(1, $pager->getPerPage());

if ($total === 0) {
    return;
}

$from = ($current - 1) * $perPage + 1;
$to   = min($total, $current * $perPage);

// Jendela 2 halaman di kiri & kanan halaman aktif, plus halaman pertama/terakhir
$pages = array_unique(array_merge([1], range(max(1, $current - 2), min($count, $current + 2)), [$count]));
sort($pages);
?>
<nav class="pagination" aria-label="Halaman data">
  <span class="pagination-info">
    <?= esc(fmt_num($from, 0, 'id')) ?>–<?= esc(fmt_num($to, 0, 'id')) ?> dari <?= esc(fmt_num($total, 0, 'id')) ?> baris
  </span>
  <?php if ($count > 1): ?>
    <ul>
      <?php if ($current > 1): ?>
        <li><a href="<?= esc($pager->getPageURI($current - 1), 'attr') ?>" rel="prev" aria-label="Halaman sebelumnya"><?= icon('left') ?></a></li>
      <?php endif ?>
      <?php $previous = 0; ?>
      <?php foreach ($pages as $page): ?>
        <?php if ($page - $previous > 1): ?>
          <li><span class="page" aria-hidden="true">…</span></li>
        <?php endif ?>
        <li>
          <?php if ($page === $current): ?>
            <span class="page is-active" aria-current="page"><?= $page ?></span>
          <?php else: ?>
            <a href="<?= esc($pager->getPageURI($page), 'attr') ?>" aria-label="Halaman <?= $page ?>"><?= $page ?></a>
          <?php endif ?>
        </li>
        <?php $previous = $page; ?>
      <?php endforeach ?>
      <?php if ($current < $count): ?>
        <li><a href="<?= esc($pager->getPageURI($current + 1), 'attr') ?>" rel="next" aria-label="Halaman berikutnya"><?= icon('right') ?></a></li>
      <?php endif ?>
    </ul>
  <?php endif ?>
</nav>
