<?php
/**
 * Pustaka Kedu — `/admin/konten/pustaka/{levelId}` → ContentController::library
 *
 * Halaman buku yang dibaca siswa di menu Pustaka. Satu form menyimpan semua
 * halaman; baris "Halaman baru" diabaikan bila judulnya kosong.
 * Halaman yang dinonaktifkan hilang dari daftar ini dan dari permainan.
 *
 * @var App\Entities\Level              $level
 * @var list<App\Entities\LibraryPage>  $pages
 */
$rows   = $pages;
$rows[] = null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Pustaka Kedu',
    'eyebrow' => 'Wilayah ' . $level->sequence . ' · ' . $level->text('name', 'id'),
    'lead'    => 'Halaman buku yang terbuka untuk siswa di menu Pustaka. Judul wajib dwibahasa; isi English yang kosong diganti teks Indonesia saat permainan.',
]) ?>
<?= component('partials/content-nav', ['level' => $level, 'active' => 'library']) ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/pustaka/' . $level->id) ?>" class="stack">
  <?= csrf_field() ?>

  <?php foreach ($rows as $index => $page): ?>
    <?php $isNew = $page === null; $p = 'lib' . $index; ?>
    <fieldset class="repeat-row<?= $isNew ? ' is-new' : '' ?>">
      <legend><?= icon($isNew ? 'sparkle' : 'book') ?> <?= $isNew ? 'Halaman baru' : 'Halaman ' . esc($page->sequence) ?></legend>
      <?php if (! $isNew): ?>
        <input type="hidden" name="pages[<?= $index ?>][id]" value="<?= esc($page->id, 'attr') ?>">
      <?php endif ?>

      <div class="form-grid">
        <div class="field">
          <label for="<?= $p ?>-seq">Urutan halaman</label>
          <input type="number" id="<?= $p ?>-seq" name="pages[<?= $index ?>][sequence]" min="1" value="<?= esc($isNew ? count($pages) + 1 : $page->sequence, 'attr') ?>">
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Judul<?php if (! $isNew): ?> <span class="req">*</span><?php endif ?></span>
        <div class="field">
          <label for="<?= $p ?>-title"><span class="lang-tag">ID</span> Indonesia</label>
          <input type="text" id="<?= $p ?>-title" name="pages[<?= $index ?>][title_id]" maxlength="150" <?= $isNew ? '' : 'required' ?> value="<?= esc($isNew ? '' : ($page->title_id ?? ''), 'attr') ?>">
        </div>
        <div class="field">
          <label for="<?= $p ?>-title-en"><span class="lang-tag">EN</span> English</label>
          <input type="text" id="<?= $p ?>-title-en" name="pages[<?= $index ?>][title_en]" maxlength="150" <?= $isNew ? '' : 'required' ?> value="<?= esc($isNew ? '' : ($page->title_en ?? ''), 'attr') ?>">
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Isi halaman</span>
        <div class="field">
          <label for="<?= $p ?>-body"><span class="lang-tag">ID</span> Indonesia</label>
          <textarea id="<?= $p ?>-body" name="pages[<?= $index ?>][body_id]" rows="6"><?= esc($isNew ? '' : ($page->body_id ?? '')) ?></textarea>
        </div>
        <div class="field">
          <label for="<?= $p ?>-body-en"><span class="lang-tag">EN</span> English</label>
          <textarea id="<?= $p ?>-body-en" name="pages[<?= $index ?>][body_en]" rows="6"><?= esc($isNew ? '' : ($page->body_en ?? '')) ?></textarea>
        </div>
        <?php if ($isNew): ?>
          <p class="field-help">Biarkan judul Indonesia kosong bila tidak menambah halaman. Bila diisi, judul English juga wajib.</p>
        <?php endif ?>
      </div>

      <div class="check-row">
        <label class="check"><input type="checkbox" name="pages[<?= $index ?>][is_active]" value="1" <?= $isNew || $page->is_active ? 'checked' : '' ?>> Tampil di permainan</label>
      </div>
    </fieldset>
  <?php endforeach ?>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan pustaka</button>
  </div>
</form>
<?= $this->endSection() ?>
