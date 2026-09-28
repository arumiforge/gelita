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
$language = 'bahasa ' . admin_label('locale', $locale);
?>
<?php if ($count === 0): ?>
  <span class="muted">Tidak ada rekaman <?= esc($language) ?> yang menunggu persetujuan.</span>
<?php else: ?>
  <details class="confirm">
    <summary class="btn btn-ghost btn-sm"><?= icon('check') ?> Setujui semua rekaman <?= esc($language) ?> (<?= esc($count) ?>)</summary>
    <div class="confirm-box">
      <h3>Setujui <?= esc($count) ?> rekaman <?= esc($language) ?>?</h3>
      <p>Semua rekaman narasi <?= esc($language) ?> yang menunggu persetujuan langsung terdengar oleh siswa. Pastikan sudah didengarkan dan sesuai teksnya. Rekaman lain (mis. narasi kartu misi) tidak ikut.</p>
      <form method="post" action="<?= base_url('admin/konten/narasi/setujui') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($locale, 'attr') ?>">
        <input type="hidden" name="back" value="<?= esc($back, 'attr') ?>">
        <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Ya, setujui <?= esc($count) ?> rekaman</button>
      </form>
    </div>
  </details>
<?php endif ?>
