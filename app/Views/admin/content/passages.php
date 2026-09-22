<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/bacaan/' . $level->id) ?>" class="form">
  <?= csrf_field() ?>

  <?php $index = 0; ?>
  <?php foreach ($passages as $passage): ?>
    <fieldset class="passage-row">
      <legend><code><?= esc($passage->passage_key) ?></code>
        · dirujuk <?= esc($usage[$passage->id] ?? 0) ?> butir</legend>
      <input type="hidden" name="passages[<?= $index ?>][passage_key]" value="<?= esc($passage->passage_key) ?>">
      <div class="field">
        <label for="p<?= $index ?>-title">Judul (ID)</label>
        <input type="text" id="p<?= $index ?>-title" name="passages[<?= $index ?>][title_id]" value="<?= esc($passage->title_id ?? '') ?>">
      </div>
      <div class="field">
        <label for="p<?= $index ?>-body">Isi (ID)</label>
        <textarea id="p<?= $index ?>-body" name="passages[<?= $index ?>][body_id]" rows="5"><?= esc($passage->body_id ?? '') ?></textarea>
      </div>
      <div class="field">
        <label for="p<?= $index ?>-body-en">Isi (EN)</label>
        <textarea id="p<?= $index ?>-body-en" name="passages[<?= $index ?>][body_en]" rows="5"><?= esc($passage->body_en ?? '') ?></textarea>
      </div>
      <label class="check">
        <input type="checkbox" name="passages[<?= $index ?>][is_active]" value="1" <?= $passage->is_active ? 'checked' : '' ?>> Aktif
      </label>
      <label class="check">
        <input type="checkbox" name="passages[<?= $index ?>][_delete]" value="1"
               <?= ($usage[$passage->id] ?? 0) > 0 ? 'disabled' : '' ?>> Hapus
      </label>
    </fieldset>
    <?php $index++; ?>
  <?php endforeach ?>

  <fieldset class="passage-row">
    <legend>Teks bacaan baru</legend>
    <div class="field">
      <label for="new-key">passage_key</label>
      <input type="text" id="new-key" name="passages[<?= $index ?>][passage_key]">
    </div>
    <div class="field">
      <label for="new-title">Judul (ID)</label>
      <input type="text" id="new-title" name="passages[<?= $index ?>][title_id]">
    </div>
    <div class="field">
      <label for="new-title-en">Judul (EN)</label>
      <input type="text" id="new-title-en" name="passages[<?= $index ?>][title_en]">
    </div>
    <div class="field">
      <label for="new-body">Isi (ID)</label>
      <textarea id="new-body" name="passages[<?= $index ?>][body_id]" rows="5"></textarea>
    </div>
    <div class="field">
      <label for="new-body-en">Isi (EN)</label>
      <textarea id="new-body-en" name="passages[<?= $index ?>][body_en]" rows="5"></textarea>
    </div>
    <label class="check"><input type="checkbox" name="passages[<?= $index ?>][is_active]" value="1" checked> Aktif</label>
  </fieldset>

  <button class="btn btn-primary" type="submit">Simpan teks bacaan</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/konten') ?>">Kembali</a>
<?= $this->endSection() ?>
