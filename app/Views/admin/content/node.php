<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/node/' . $node->id) ?>" class="form">
  <?= csrf_field() ?>

  <?php foreach (['title' => 'Judul', 'instruction' => 'Instruksi', 'description' => 'Deskripsi'] as $field => $label): ?>
    <div class="field">
      <label for="<?= esc($field) ?>_id"><?= esc($label) ?> (ID)</label>
      <textarea id="<?= esc($field) ?>_id" name="<?= esc($field) ?>_id" rows="2"><?= esc($node->{$field . '_id'} ?? '') ?></textarea>
    </div>
    <div class="field">
      <label for="<?= esc($field) ?>_en"><?= esc($label) ?> (EN)</label>
      <textarea id="<?= esc($field) ?>_en" name="<?= esc($field) ?>_en" rows="2"><?= esc($node->{$field . '_en'} ?? '') ?></textarea>
    </div>
  <?php endforeach ?>

  <div class="field">
    <label for="engine_type">Engine</label>
    <select id="engine_type" name="engine_type" <?= $locked ? 'disabled' : '' ?>>
      <?php foreach (config('Gelita')->engineTypes as $option): ?>
        <option value="<?= esc($option) ?>" <?= $node->engine_type === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
      <?php endforeach ?>
    </select>
    <?php if ($locked): ?>
      <input type="hidden" name="engine_type" value="<?= esc($node->engine_type) ?>">
      <small>Node ini sudah punya percobaan, jadi engine tidak dapat diubah.</small>
    <?php endif ?>
  </div>

  <div class="field">
    <label for="variant_code">Varian</label>
    <input type="text" id="variant_code" name="variant_code" value="<?= esc($node->variant_code ?? '') ?>">
  </div>

  <div class="field">
    <label for="config_json">config_json</label>
    <textarea id="config_json" name="config_json" rows="6"><?= esc(json_encode($node->config_json ?: [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></textarea>
  </div>

  <label class="check">
    <input type="checkbox" name="is_active" value="1" <?= $node->is_active ? 'checked' : '' ?>> Aktif
  </label>

  <button class="btn btn-primary" type="submit">Simpan tantangan</button>
</form>

<h2>Bank soal (<?= count($items) ?> butir)</h2>
<?php foreach ($items as $item): ?>
  <section class="item-editor">
    <h3><code><?= esc($item->item_key) ?></code> — <?= esc($item->interaction_type) ?></h3>

    <form method="post" action="<?= base_url('admin/konten/item/' . $item->id) ?>" class="form">
      <?= csrf_field() ?>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-type">Jenis interaksi</label>
        <select id="it<?= esc($item->id) ?>-type" name="interaction_type" required>
          <?php foreach ($interactions as $option): ?>
            <option value="<?= esc($option) ?>" <?= $item->interaction_type === $option ? 'selected' : '' ?>>
              <?= esc($option) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-prompt">Pertanyaan (ID)</label>
        <textarea id="it<?= esc($item->id) ?>-prompt" name="prompt_id" rows="2"><?= esc($item->prompt_id ?? '') ?></textarea>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-prompt-en">Pertanyaan (EN)</label>
        <textarea id="it<?= esc($item->id) ?>-prompt-en" name="prompt_en" rows="2"><?= esc($item->prompt_en ?? '') ?></textarea>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-seq">Urutan</label>
        <input type="number" id="it<?= esc($item->id) ?>-seq" name="sequence" min="0"
               value="<?= esc($item->sequence) ?>">
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-source">Teks sumber (ID)</label>
        <textarea id="it<?= esc($item->id) ?>-source" name="source_text_id" rows="2"><?= esc($item->source_text_id ?? '') ?></textarea>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-source-en">Teks sumber (EN)</label>
        <textarea id="it<?= esc($item->id) ?>-source-en" name="source_text_en" rows="2"><?= esc($item->source_text_en ?? '') ?></textarea>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-config">config_json</label>
        <textarea id="it<?= esc($item->id) ?>-config" name="config_json" rows="3"><?= esc(json_encode($item->config_json ?: [], JSON_UNESCAPED_UNICODE)) ?></textarea>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-ref">Sumber rujukan</label>
        <input type="text" id="it<?= esc($item->id) ?>-ref" name="reference_source"
               value="<?= esc($item->reference_source ?? '') ?>">
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-note">Catatan tinjauan</label>
        <input type="text" id="it<?= esc($item->id) ?>-note" name="review_note"
               value="<?= esc($item->review_note ?? '') ?>">
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-key">answer_key_json</label>
        <textarea id="it<?= esc($item->id) ?>-key" name="answer_key_json" rows="3"><?= esc(json_encode($item->answerKey(), JSON_UNESCAPED_UNICODE)) ?></textarea>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-indicator">Indikator</label>
        <select id="it<?= esc($item->id) ?>-indicator" name="indicator_id">
          <option value="">— tanpa indikator —</option>
          <?php foreach ($indicators as $code => $indicator): ?>
            <option value="<?= esc($indicator['id']) ?>"
                    <?= (int) $item->indicator_id === (int) $indicator['id'] ? 'selected' : '' ?>>
              <?= esc($code) ?> — <?= esc($indicator['name_id']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-passage">Teks bacaan</label>
        <select id="it<?= esc($item->id) ?>-passage" name="passage_id">
          <option value="">— tanpa bacaan —</option>
          <?php foreach ($passages as $passage): ?>
            <option value="<?= esc($passage->id) ?>" <?= (int) $item->passage_id === $passage->id ? 'selected' : '' ?>>
              <?= esc($passage->passage_key) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>

      <div class="field">
        <label for="it<?= esc($item->id) ?>-review">Status tinjauan</label>
        <select id="it<?= esc($item->id) ?>-review" name="review_status">
          <?php foreach (['draft', 'needs_verification', 'verified'] as $option): ?>
            <option value="<?= esc($option) ?>" <?= $item->review_status === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
          <?php endforeach ?>
        </select>
      </div>

      <label class="check"><input type="checkbox" name="scorable" value="1" <?= $item->scorable ? 'checked' : '' ?>> Dinilai</label>
      <label class="check"><input type="checkbox" name="is_active" value="1" <?= $item->is_active ? 'checked' : '' ?>> Aktif</label>

      <button class="btn btn-primary btn-sm" type="submit">Simpan butir</button>
    </form>

    <?php if (in_array($item->interaction_type, ['single_choice', 'source_trust'], true)): ?>
      <form method="post" action="<?= base_url('admin/konten/item/' . $item->id . '/opsi') ?>" class="form">
        <?= csrf_field() ?>
        <h4>Opsi jawaban — tepat satu harus benar</h4>

        <?php $optionRows = $options[$item->id] ?? []; ?>
        <?php foreach (array_values($optionRows) as $index => $option): ?>
          <fieldset class="option-row">
            <legend><code><?= esc($option->option_key) ?></code></legend>
            <input type="hidden" name="options[<?= $index ?>][option_key]" value="<?= esc($option->option_key) ?>">
            <input type="hidden" name="options[<?= $index ?>][display_order]" value="<?= esc($option->display_order) ?>">
            <div class="field">
              <label for="op<?= esc($option->id) ?>-label">Label (ID)</label>
              <input type="text" id="op<?= esc($option->id) ?>-label" name="options[<?= $index ?>][label_id]"
                     value="<?= esc($option->label_id ?? '') ?>">
            </div>
            <div class="field">
              <label for="op<?= esc($option->id) ?>-label-en">Label (EN)</label>
              <input type="text" id="op<?= esc($option->id) ?>-label-en" name="options[<?= $index ?>][label_en]"
                     value="<?= esc($option->label_en ?? '') ?>" required>
            </div>
            <div class="field">
              <label for="op<?= esc($option->id) ?>-feedback">Umpan balik (ID)</label>
              <input type="text" id="op<?= esc($option->id) ?>-feedback" name="options[<?= $index ?>][feedback_id]"
                     value="<?= esc($option->feedback_id ?? '') ?>">
            </div>
            <div class="field">
              <label for="op<?= esc($option->id) ?>-feedback-en">Umpan balik (EN)</label>
              <input type="text" id="op<?= esc($option->id) ?>-feedback-en" name="options[<?= $index ?>][feedback_en]"
                     value="<?= esc($option->feedback_en ?? '') ?>">
            </div>
            <label class="check">
              <input type="radio" name="correct_option" value="<?= esc($option->option_key) ?>"
                     <?= $option->is_correct ? 'checked' : '' ?>>
              Jawaban benar
            </label>
          </fieldset>
        <?php endforeach ?>

        <?php if ($optionRows === []): ?>
          <p><?= esc(lang('Admin.emptyDefault')) ?></p>
        <?php endif ?>

        <button class="btn btn-primary btn-sm" type="submit">Simpan opsi</button>
      </form>
    <?php endif ?>

    <form method="post" action="<?= base_url('admin/konten/item/' . $item->id . '/hapus') ?>"
          onsubmit="return confirm('Hapus butir ini?')">
      <?= csrf_field() ?>
      <button class="btn btn-danger btn-sm" type="submit">Hapus butir</button>
    </form>
  </section>
<?php endforeach ?>

<?php if ($items === []): ?>
  <p><?= esc(lang('Admin.emptyDefault')) ?></p>
<?php endif ?>

<h2>Tambah butir</h2>
<form method="post" action="<?= base_url('admin/konten/node/' . $node->id . '/item') ?>" class="form">
  <?= csrf_field() ?>

  <div class="field">
    <label for="item_key">item_key</label>
    <input type="text" id="item_key" name="item_key" required maxlength="60">
  </div>

  <div class="field">
    <label for="interaction_type">Jenis interaksi</label>
    <select id="interaction_type" name="interaction_type" required>
      <?php foreach ($interactions as $option): ?>
        <option value="<?= esc($option) ?>"><?= esc($option) ?></option>
      <?php endforeach ?>
    </select>
  </div>

  <div class="field">
    <label for="prompt_id">Pertanyaan (ID)</label>
    <textarea id="prompt_id" name="prompt_id" rows="2"></textarea>
  </div>

  <div class="field">
    <label for="prompt_en">Pertanyaan (EN)</label>
    <textarea id="prompt_en" name="prompt_en" rows="2"></textarea>
  </div>

  <div class="field">
    <label for="sequence">Urutan</label>
    <input type="number" id="sequence" name="sequence" min="0" value="0">
  </div>

  <div class="field">
    <label for="answer_key_json">answer_key_json</label>
    <textarea id="answer_key_json" name="answer_key_json" rows="3"></textarea>
  </div>

  <div class="field">
    <label for="passage_id">Teks bacaan</label>
    <select id="passage_id" name="passage_id">
      <option value="">— tanpa bacaan —</option>
      <?php foreach ($passages as $passage): ?>
        <option value="<?= esc($passage->id) ?>"><?= esc($passage->passage_key) ?></option>
      <?php endforeach ?>
    </select>
  </div>

  <label class="check"><input type="checkbox" name="scorable" value="1" checked> Dinilai</label>
  <label class="check"><input type="checkbox" name="is_active" value="1" checked> Aktif</label>

  <button class="btn btn-primary" type="submit">Tambah butir</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/konten') ?>">Kembali</a>
<?= $this->endSection() ?>
