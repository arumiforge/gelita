<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/level/' . $level->id) ?>" class="form">
  <?= csrf_field() ?>

  <?php foreach (['name' => 'Nama', 'focus' => 'Fokus', 'cp' => 'Capaian pembelajaran', 'tp' => 'Tujuan pembelajaran', 'intro' => 'Pengantar'] as $field => $label): ?>
    <div class="field">
      <label for="<?= esc($field) ?>_id"><?= esc($label) ?> (ID)</label>
      <textarea id="<?= esc($field) ?>_id" name="<?= esc($field) ?>_id" rows="2"><?= esc($level->{$field . '_id'} ?? '') ?></textarea>
    </div>
    <div class="field">
      <label for="<?= esc($field) ?>_en"><?= esc($label) ?> (EN)</label>
      <textarea id="<?= esc($field) ?>_en" name="<?= esc($field) ?>_en" rows="2"><?= esc($level->{$field . '_en'} ?? '') ?></textarea>
    </div>
  <?php endforeach ?>

  <div class="field">
    <label for="difficulty">Tingkat kesulitan</label>
    <select id="difficulty" name="difficulty">
      <?php foreach (['mudah', 'sedang', 'sulit'] as $option): ?>
        <option value="<?= esc($option) ?>" <?= $level->difficulty === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
      <?php endforeach ?>
    </select>
  </div>

  <label class="check">
    <input type="checkbox" name="is_active" value="1" <?= $level->is_active ? 'checked' : '' ?>> Aktif
  </label>

  <button class="btn btn-primary" type="submit">Simpan</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/konten') ?>">Kembali</a>
<?= $this->endSection() ?>
