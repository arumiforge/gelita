<?php
/**
 * Tombol "Setujui semua narasi draft" satu bahasa, dengan konfirmasi
 * `<details class="confirm">` (berjalan tanpa JavaScript). Hanya audio
 * narasi naskah yang ikut (NarrationCatalog::draftIds()), bukan audio lain.
 *
 * @var string $locale
 * @var int    $count  jumlah narasi draft bahasa ini
 * @var string $back   narasi | audio — halaman tujuan setelah menyetujui
 */
$label = strtoupper($locale);
?>
<?php if ($count === 0): ?>
  <span class="muted">Tidak ada narasi <?= esc($label) ?> berstatus draft.</span>
<?php else: ?>
  <details class="confirm">
    <summary class="btn btn-ghost btn-sm"><?= icon('check') ?> Setujui semua narasi draft <?= esc($label) ?> (<?= esc($count) ?>)</summary>
    <div class="confirm-box">
      <h3>Setujui <?= esc($count) ?> narasi <?= esc($label) ?>?</h3>
      <p>Seluruh rekaman narasi <?= esc($label) ?> berstatus draft langsung terdengar pemain. Pastikan sudah didengarkan dan sesuai teksnya. Audio lain (mis. narasi kartu misi) tidak ikut.</p>
      <form method="post" action="<?= base_url('admin/konten/narasi/setujui') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($locale, 'attr') ?>">
        <input type="hidden" name="back" value="<?= esc($back, 'attr') ?>">
        <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Ya, setujui <?= esc($count) ?> narasi</button>
      </form>
    </div>
  </details>
<?php endif ?>
