<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/media/audio/unggah') ?>" class="form" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="field">
    <label for="asset_key">asset_key</label>
    <input type="text" id="asset_key" name="asset_key" required maxlength="160" placeholder="audio.jaka.intro.id">
  </div>

  <div class="field">
    <label for="locale">Bahasa</label>
    <select id="locale" name="locale" required>
      <?php foreach ($locales as $code): ?>
        <option value="<?= esc($code) ?>"><?= esc($code) ?></option>
      <?php endforeach ?>
    </select>
  </div>

  <div class="field">
    <label for="character_code">Karakter</label>
    <select id="character_code" name="character_code">
      <option value="">— tanpa karakter —</option>
      <?php foreach ($characters as $character): ?>
        <option value="<?= esc($character) ?>"><?= esc($character) ?></option>
      <?php endforeach ?>
    </select>
  </div>

  <div class="field">
    <label for="context_code">Konteks</label>
    <input type="text" id="context_code" name="context_code" required maxlength="80">
  </div>

  <div class="field">
    <label for="transcript">Transkrip (wajib)</label>
    <textarea id="transcript" name="transcript" rows="4" required></textarea>
  </div>

  <div class="field">
    <label for="production_method">Cara produksi</label>
    <select id="production_method" name="production_method">
      <option value="own_recording">Rekaman sendiri</option>
      <option value="tts">Text-to-speech</option>
    </select>
  </div>

  <div class="field">
    <label for="file">Berkas audio</label>
    <input type="file" id="file" name="file" accept="audio/*" required>
  </div>

  <button class="btn btn-primary" type="submit">Unggah audio</button>
</form>

<h2>Aset audio</h2>
<table class="data-table">
  <thead>
    <tr><th scope="col">asset_key</th><th scope="col">Konteks</th><th scope="col">Bahasa</th>
        <th scope="col">Karakter</th><th scope="col">Status</th><th scope="col">Durasi</th><th scope="col"></th></tr>
  </thead>
  <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><code><?= esc($row['asset_key']) ?></code></td>
        <td><?= esc($row['context_code']) ?></td>
        <td><?= esc($row['locale']) ?></td>
        <td><?= esc($row['character_code'] ?? '—') ?></td>
        <td><?= esc($row['approval_status']) ?></td>
        <td><?= $row['duration_ms'] === null ? '—' : esc(ms_to_human((int) $row['duration_ms'])) ?></td>
        <td>
          <?php if ($row['approval_status'] !== 'approved'): ?>
            <form method="post" action="<?= base_url('admin/media/audio/' . $row['id'] . '/setujui') ?>">
              <?= csrf_field() ?>
              <button class="btn btn-primary btn-sm" type="submit">Setujui</button>
            </form>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if ($rows === []): ?>
      <tr><td colspan="7"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<p><small>Hanya audio berstatus <code>approved</code> yang dikirim ke pemain.</small></p>
<a class="btn btn-quiet" href="<?= base_url('admin/media') ?>">Kembali</a>
<?= $this->endSection() ?>
