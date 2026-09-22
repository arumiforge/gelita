<?php
/**
 * Teks bacaan — `/admin/konten/bacaan/{levelId}` → ContentController::passages
 *
 * Bacaan panjang yang dirujuk butir soal lewat passage_id (ditampilkan di
 * panel kiri arena). Indonesia di kiri, English di kanan; isi kedua bahasa
 * wajib. Bacaan yang masih dirujuk butir tidak dapat dihapus.
 *
 * @var App\Entities\Level                 $level
 * @var list<App\Entities\ReadingPassage>  $passages
 * @var array<int, int>                    $usage passage_id → jumlah butir perujuk
 */
$rows   = $passages;
$rows[] = null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Teks bacaan',
    'eyebrow' => 'Wilayah ' . $level->sequence . ' · ' . $level->text('name', 'id'),
    'lead'    => 'Bacaan yang tampil di samping soal. Kunci bacaan (passage_key) tidak dapat diubah setelah disimpan karena dipakai workbook bank soal.',
]) ?>
<?= component('partials/content-nav', ['level' => $level, 'active' => 'passages']) ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/bacaan/' . $level->id) ?>" class="stack">
  <?= csrf_field() ?>

  <?php foreach ($rows as $index => $passage): ?>
    <?php
    $isNew = $passage === null;
    $p     = 'psg' . $index;
    $used  = $isNew ? 0 : ($usage[$passage->id] ?? 0);
    ?>
    <fieldset class="repeat-row<?= $isNew ? ' is-new' : '' ?>">
      <legend>
        <?= icon($isNew ? 'sparkle' : 'text') ?>
        <?php if ($isNew): ?>
          Teks bacaan baru
        <?php else: ?>
          <code><?= esc($passage->passage_key) ?></code>
          <span class="badge <?= $used > 0 ? 'is-open' : 'is-muted' ?>"><?= $used > 0 ? 'dirujuk ' . $used . ' butir' : 'belum dirujuk' ?></span>
        <?php endif ?>
      </legend>

      <?php if ($isNew): ?>
        <div class="form-grid">
          <div class="field">
            <label for="<?= $p ?>-key">Kunci bacaan (passage_key)</label>
            <input type="text" id="<?= $p ?>-key" name="passages[<?= $index ?>][passage_key]" maxlength="60" spellcheck="false" placeholder="psg-tmg-01">
            <p class="field-help">Unik di seluruh konten, mis. psg-tmg-01. Kosongkan bila tidak menambah bacaan.</p>
          </div>
        </div>
      <?php else: ?>
        <input type="hidden" name="passages[<?= $index ?>][passage_key]" value="<?= esc($passage->passage_key, 'attr') ?>">
      <?php endif ?>

      <div class="bilingual">
        <span class="bilingual-label">Judul</span>
        <div class="field">
          <label for="<?= $p ?>-title"><span class="lang-tag">ID</span> Indonesia</label>
          <input type="text" id="<?= $p ?>-title" name="passages[<?= $index ?>][title_id]" maxlength="200" value="<?= esc($isNew ? '' : ($passage->title_id ?? ''), 'attr') ?>">
        </div>
        <div class="field">
          <label for="<?= $p ?>-title-en"><span class="lang-tag">EN</span> English</label>
          <input type="text" id="<?= $p ?>-title-en" name="passages[<?= $index ?>][title_en]" maxlength="200" value="<?= esc($isNew ? '' : ($passage->title_en ?? ''), 'attr') ?>">
        </div>
      </div>

      <div class="bilingual">
        <span class="bilingual-label">Isi bacaan<?php if (! $isNew): ?> <span class="req">*</span><?php endif ?></span>
        <div class="field">
          <label for="<?= $p ?>-body"><span class="lang-tag">ID</span> Indonesia</label>
          <textarea id="<?= $p ?>-body" name="passages[<?= $index ?>][body_id]" rows="7" <?= $isNew ? '' : 'required' ?>><?= esc($isNew ? '' : ($passage->body_id ?? '')) ?></textarea>
        </div>
        <div class="field">
          <label for="<?= $p ?>-body-en"><span class="lang-tag">EN</span> English</label>
          <textarea id="<?= $p ?>-body-en" name="passages[<?= $index ?>][body_en]" rows="7" <?= $isNew ? '' : 'required' ?>><?= esc($isNew ? '' : ($passage->body_en ?? '')) ?></textarea>
        </div>
      </div>

      <div class="form-grid">
        <div class="field span-all">
          <label for="<?= $p ?>-ref">Sumber rujukan</label>
          <input type="text" id="<?= $p ?>-ref" name="passages[<?= $index ?>][reference_source]" maxlength="255" value="<?= esc($isNew ? '' : ($passage->reference_source ?? ''), 'attr') ?>" placeholder="Buku, situs resmi, atau arsip yang menjadi dasar bacaan">
        </div>
      </div>

      <div class="check-row">
        <label class="check"><input type="checkbox" name="passages[<?= $index ?>][is_active]" value="1" <?= $isNew || $passage->is_active ? 'checked' : '' ?>> Aktif</label>
        <?php if (! $isNew): ?>
          <label class="check">
            <input type="checkbox" name="passages[<?= $index ?>][_delete]" value="1" <?= $used > 0 ? 'disabled' : '' ?>>
            Hapus bacaan ini<?php if ($used > 0): ?> <span class="muted">(masih dirujuk butir)</span><?php endif ?>
          </label>
        <?php endif ?>
      </div>
    </fieldset>
  <?php endforeach ?>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan teks bacaan</button>
  </div>
</form>
<?= $this->endSection() ?>
