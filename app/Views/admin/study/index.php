<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<h2>Studi</h2>
<?php foreach ($studies as $study): ?>
  <form method="post" action="<?= base_url('admin/studi/' . $study['id']) ?>" class="form study-row">
    <?= csrf_field() ?>
    <h3><?= esc($study['code']) ?> — <?= esc($study['name']) ?></h3>

    <input type="hidden" name="code" value="<?= esc($study['code']) ?>">

    <div class="field">
      <label for="name-<?= esc($study['id']) ?>">Nama</label>
      <input type="text" id="name-<?= esc($study['id']) ?>" name="name" value="<?= esc($study['name']) ?>" required>
    </div>

    <div class="field">
      <label for="status-<?= esc($study['id']) ?>">Status</label>
      <select id="status-<?= esc($study['id']) ?>" name="status">
        <?php foreach (['draft', 'active', 'closed'] as $option): ?>
          <option value="<?= esc($option) ?>" <?= $study['status'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="field">
      <label for="phase-<?= esc($study['id']) ?>">Fase aktif</label>
      <select id="phase-<?= esc($study['id']) ?>" name="active_phase_code">
        <?php foreach ($phaseCodes as $code): ?>
          <option value="<?= esc($code) ?>" <?= $study['active_phase_code'] === $code ? 'selected' : '' ?>><?= esc($code) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="field">
      <label for="locale-<?= esc($study['id']) ?>">Bahasa bawaan</label>
      <select id="locale-<?= esc($study['id']) ?>" name="default_locale">
        <?php foreach ($locales as $code): ?>
          <option value="<?= esc($code) ?>" <?= $study['default_locale'] === $code ? 'selected' : '' ?>><?= esc($code) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="field">
      <label for="unlock-<?= esc($study['id']) ?>">Mode buka level</label>
      <select id="unlock-<?= esc($study['id']) ?>" name="unlock_mode">
        <?php foreach (['sequential', 'free'] as $option): ?>
          <option value="<?= esc($option) ?>" <?= $study['unlock_mode'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="field">
      <label for="selection-<?= esc($study['id']) ?>">Pemilihan butir</label>
      <select id="selection-<?= esc($study['id']) ?>" name="item_selection_mode">
        <?php foreach (['fixed', 'random'] as $option): ?>
          <option value="<?= esc($option) ?>" <?= $study['item_selection_mode'] === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="field">
      <label for="retention-<?= esc($study['id']) ?>">Retensi (hari)</label>
      <input type="number" id="retention-<?= esc($study['id']) ?>" name="retention_days" value="<?= esc($study['retention_days']) ?>">
    </div>

    <label class="check">
      <input type="checkbox" name="require_consent" value="1" <?= $study['require_consent'] ? 'checked' : '' ?>>
      Wajib persetujuan orang tua/wali
    </label>
    <label class="check">
      <input type="checkbox" name="allow_phase_choice" value="1" <?= $study['allow_phase_choice'] ? 'checked' : '' ?>>
      Siswa boleh memilih fase
    </label>

    <p><small>Fase: <?php foreach ($phases[(int) $study['id']] ?? [] as $phase): ?>
      <span class="chip"><?= esc($phase['code']) ?></span>
    <?php endforeach ?></small></p>

    <button class="btn btn-primary" type="submit">Simpan</button>
  </form>
<?php endforeach ?>

<h2>Studi baru</h2>
<form method="post" action="<?= base_url('admin/studi') ?>" class="form">
  <?= csrf_field() ?>
  <div class="field"><label for="new-code">Kode</label><input type="text" id="new-code" name="code" required></div>
  <div class="field"><label for="new-name">Nama</label><input type="text" id="new-name" name="name" required></div>
  <input type="hidden" name="status" value="draft">
  <input type="hidden" name="default_locale" value="id">
  <input type="hidden" name="unlock_mode" value="sequential">
  <input type="hidden" name="item_selection_mode" value="fixed">
  <input type="hidden" name="active_phase_code" value="umum">
  <button class="btn btn-primary" type="submit">Buat studi</button>
</form>

<p>
  <a class="btn btn-quiet" href="<?= base_url('admin/studi/rilis') ?>">Rilis konten</a>
  <a class="btn btn-quiet" href="<?= base_url('admin/studi/skoring') ?>">Profil skoring</a>
</p>
<?= $this->endSection() ?>
