<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/pustaka/' . $level->id) ?>" class="form">
  <?= csrf_field() ?>

  <?php $index = 0; ?>
  <?php foreach ($pages as $page): ?>
    <fieldset class="library-row">
      <legend>Halaman <?= esc($page->sequence) ?></legend>
      <input type="hidden" name="pages[<?= $index ?>][id]" value="<?= esc($page->id) ?>">
      <input type="hidden" name="pages[<?= $index ?>][sequence]" value="<?= esc($page->sequence) ?>">
      <div class="field">
        <label for="l<?= $index ?>-title">Judul (ID)</label>
        <input type="text" id="l<?= $index ?>-title" name="pages[<?= $index ?>][title_id]" value="<?= esc($page->title_id ?? '') ?>">
      </div>
      <div class="field">
        <label for="l<?= $index ?>-title-en">Judul (EN)</label>
        <input type="text" id="l<?= $index ?>-title-en" name="pages[<?= $index ?>][title_en]"
               value="<?= esc($page->title_en ?? '') ?>" required>
      </div>
      <div class="field">
        <label for="l<?= $index ?>-body">Isi (ID)</label>
        <textarea id="l<?= $index ?>-body" name="pages[<?= $index ?>][body_id]" rows="4"><?= esc($page->body_id ?? '') ?></textarea>
      </div>
      <div class="field">
        <label for="l<?= $index ?>-body-en">Isi (EN)</label>
        <textarea id="l<?= $index ?>-body-en" name="pages[<?= $index ?>][body_en]" rows="4"><?= esc($page->body_en ?? '') ?></textarea>
      </div>
      <label class="check">
        <input type="checkbox" name="pages[<?= $index ?>][is_active]" value="1" <?= $page->is_active ? 'checked' : '' ?>> Aktif
      </label>
    </fieldset>
    <?php $index++; ?>
  <?php endforeach ?>

  <fieldset class="library-row">
    <legend>Halaman baru</legend>
    <div class="field">
      <label for="new-lib-title">Judul (ID)</label>
      <input type="text" id="new-lib-title" name="pages[<?= $index ?>][title_id]">
    </div>
    <div class="field">
      <label for="new-lib-title-en">Judul (EN)</label>
      <input type="text" id="new-lib-title-en" name="pages[<?= $index ?>][title_en]">
    </div>
    <div class="field">
      <label for="new-lib-body">Isi (ID)</label>
      <textarea id="new-lib-body" name="pages[<?= $index ?>][body_id]" rows="4"></textarea>
    </div>
    <div class="field">
      <label for="new-lib-body-en">Isi (EN)</label>
      <textarea id="new-lib-body-en" name="pages[<?= $index ?>][body_en]" rows="4"></textarea>
    </div>
    <label class="check"><input type="checkbox" name="pages[<?= $index ?>][is_active]" value="1" checked> Aktif</label>
  </fieldset>

  <button class="btn btn-primary" type="submit">Simpan pustaka</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/konten') ?>">Kembali</a>
<?= $this->endSection() ?>
