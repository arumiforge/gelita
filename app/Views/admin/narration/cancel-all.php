<?php
/**
 * "Batalkan semua rekaman" button of one language, with a
 * `<details class="confirm">` confirmation (works without JavaScript).
 * NarrationCatalog::cancelAll(): lines lose their recording, imported
 * narration files are deleted.
 *
 * @var string $locale
 * @var int    $count  recordings of this language that would be cancelled
 */
$language = 'bahasa ' . admin_label('locale', $locale);
?>
<?php if ($count > 0): ?>
  <details class="confirm">
    <summary class="btn btn-danger-soft btn-sm"><?= icon('trash') ?> Batalkan semua rekaman <?= esc($language) ?> (<?= esc($count) ?>)</summary>
    <div class="confirm-box">
      <h3>Batalkan <?= esc($count) ?> rekaman <?= esc($language) ?>?</h3>
      <p>Semua baris naskah dan petunjuk Cari Objek <?= esc($language) ?> kembali berstatus <b>belum ada</b>, dan berkas rekaman lamanya dihapus dari server. Siswa tidak lagi mendengar narasi <?= esc($language) ?> sampai rekaman baru diunggah dan disetujui. Langkah ini tidak dapat dibatalkan.</p>
      <p>Rekaman baru cukup diunggah dengan nama berkas yang sama (mis. intro-01.mp3).</p>
      <form method="post" action="<?= base_url('admin/konten/narasi/batalkan') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="locale" value="<?= esc($locale, 'attr') ?>">
        <button class="btn btn-danger btn-sm" type="submit"><?= icon('trash') ?> Ya, batalkan <?= esc($count) ?> rekaman</button>
      </form>
    </div>
  </details>
<?php endif ?>
