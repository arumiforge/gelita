<?php
/**
 * Sunting wilayah — `/admin/konten/level/{id}` → ContentController::level
 *
 * Kolom teks berpasangan: Indonesia di kiri, English di kanan. Kotak English
 * yang dikosongkan akan memakai teks Indonesianya saat permainan (kecuali
 * nama wilayah, yang wajib diisi keduanya).
 *
 * @var App\Entities\Level                  $level
 * @var list<App\Entities\ChallengeNode>    $nodes
 * @var array<string, string>               $errors
 */
$fields = [
    'name'  => ['Nama', 1, true],
    'focus' => ['Fokus literasi', 2, false],
    'cp'    => ['Capaian pembelajaran', 3, false],
    'tp'    => ['Tujuan pembelajaran', 3, false],
    'intro' => ['Pengantar wilayah', 3, false],
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $level->text('name', 'id'),
    'eyebrow' => 'Sunting wilayah ' . $level->sequence,
]) ?>
<?= component('partials/content-nav', ['level' => $level, 'active' => 'level']) ?>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/konten/level/' . $level->id) ?>" class="form-section">
  <?= csrf_field() ?>
  <h2>Teks wilayah</h2>

  <?php foreach ($fields as $field => [$label, $rows, $required]): ?>
    <div class="bilingual">
      <span class="bilingual-label"><?= esc($label) ?><?php if ($required): ?> <span class="req">*</span><?php endif ?></span>
      <div class="field<?= isset($errors[$field . '_id']) ? ' has-error' : '' ?>">
        <label for="<?= esc($field) ?>_id"><span class="lang-tag">ID</span> Indonesia</label>
        <?php if ($rows === 1): ?>
          <input type="text" id="<?= esc($field) ?>_id" name="<?= esc($field) ?>_id" <?= $required ? 'required' : '' ?> value="<?= esc($level->{$field . '_id'} ?? '', 'attr') ?>">
        <?php else: ?>
          <textarea id="<?= esc($field) ?>_id" name="<?= esc($field) ?>_id" rows="<?= $rows ?>"><?= esc($level->{$field . '_id'} ?? '') ?></textarea>
        <?php endif ?>
      </div>
      <div class="field<?= isset($errors[$field . '_en']) ? ' has-error' : '' ?>">
        <label for="<?= esc($field) ?>_en"><span class="lang-tag">EN</span> English</label>
        <?php if ($rows === 1): ?>
          <input type="text" id="<?= esc($field) ?>_en" name="<?= esc($field) ?>_en" <?= $required ? 'required' : '' ?> value="<?= esc($level->{$field . '_en'} ?? '', 'attr') ?>">
        <?php else: ?>
          <textarea id="<?= esc($field) ?>_en" name="<?= esc($field) ?>_en" rows="<?= $rows ?>"><?= esc($level->{$field . '_en'} ?? '') ?></textarea>
        <?php endif ?>
      </div>
    </div>
  <?php endforeach ?>

  <div class="form-grid">
    <div class="field">
      <label for="difficulty">Tingkat kesulitan</label>
      <select id="difficulty" name="difficulty">
        <?php foreach (['mudah', 'sedang', 'sulit'] as $option): ?>
          <option value="<?= esc($option, 'attr') ?>" <?= $level->difficulty === $option ? 'selected' : '' ?>><?= esc(ucfirst($option)) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <label class="check"><input type="checkbox" name="is_active" value="1" <?= $level->is_active ? 'checked' : '' ?>> Wilayah aktif</label>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan wilayah</button>
  </div>
</form>

<section class="panel">
  <h2 class="panel-title"><?= icon('puzzle') ?> Tantangan di wilayah ini</h2>
  <ul class="node-rows">
    <?php foreach ($nodes as $node): ?>
      <li>
        <a class="node-row" href="<?= base_url('admin/konten/node/' . $node->id) ?>">
          <span class="node-row-no"><?= esc($node->sequence) ?></span>
          <span><?= esc($node->text('title', 'id')) ?><span class="node-row-meta"><?= esc($node->engine_type) ?> · <?= esc($node->variant_code ?? '—') ?></span></span>
          <?= icon('edit') ?>
        </a>
      </li>
    <?php endforeach ?>
  </ul>
</section>
<?= $this->endSection() ?>
