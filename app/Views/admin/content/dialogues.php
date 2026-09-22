<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/dialog/' . $level->id) ?>" class="form">
  <?= csrf_field() ?>

  <?php $index = 0; ?>
  <?php foreach ($dialogues as $row): ?>
    <fieldset class="dialogue-row">
      <legend>Slide <?= esc($row['sequence']) ?></legend>
      <input type="hidden" name="dialogues[<?= $index ?>][id]" value="<?= esc($row['id']) ?>">
      <input type="hidden" name="dialogues[<?= $index ?>][sequence]" value="<?= esc($row['sequence']) ?>">
      <input type="hidden" name="dialogues[<?= $index ?>][context_code]" value="<?= esc($row['context_code']) ?>">
      <div class="field">
        <label for="d<?= $index ?>-char">Karakter</label>
        <select id="d<?= $index ?>-char" name="dialogues[<?= $index ?>][character_code]">
          <?php foreach ($characters as $character): ?>
            <option value="<?= esc($character) ?>" <?= ($row['character_code'] ?? '') === $character ? 'selected' : '' ?>>
              <?= esc($character) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="d<?= $index ?>-text">Teks (ID)</label>
        <textarea id="d<?= $index ?>-text" name="dialogues[<?= $index ?>][text_id]" rows="3"><?= esc($row['text_id'] ?? '') ?></textarea>
      </div>
      <div class="field">
        <label for="d<?= $index ?>-text-en">Teks (EN)</label>
        <textarea id="d<?= $index ?>-text-en" name="dialogues[<?= $index ?>][text_en]" rows="3"><?= esc($row['text_en'] ?? '') ?></textarea>
      </div>
      <label class="check">
        <input type="checkbox" name="dialogues[<?= $index ?>][is_active]" value="1"
               <?= ! empty($row['is_active']) ? 'checked' : '' ?>> Aktif
      </label>
    </fieldset>
    <?php $index++; ?>
  <?php endforeach ?>

  <fieldset class="dialogue-row">
    <legend>Slide baru</legend>
    <input type="hidden" name="dialogues[<?= $index ?>][context_code]" value="level_open">
    <input type="hidden" name="dialogues[<?= $index ?>][sequence]" value="<?= $index + 1 ?>">
    <div class="field">
      <label for="new-dlg-char">Karakter</label>
      <select id="new-dlg-char" name="dialogues[<?= $index ?>][character_code]">
        <?php foreach ($characters as $character): ?>
          <option value="<?= esc($character) ?>"><?= esc($character) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="new-dlg-text">Teks (ID)</label>
      <textarea id="new-dlg-text" name="dialogues[<?= $index ?>][text_id]" rows="3"></textarea>
    </div>
    <div class="field">
      <label for="new-dlg-text-en">Teks (EN)</label>
      <textarea id="new-dlg-text-en" name="dialogues[<?= $index ?>][text_en]" rows="3"></textarea>
    </div>
    <label class="check"><input type="checkbox" name="dialogues[<?= $index ?>][is_active]" value="1" checked> Aktif</label>
  </fieldset>

  <button class="btn btn-primary" type="submit">Simpan dialog</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/konten') ?>">Kembali</a>
<?= $this->endSection() ?>
