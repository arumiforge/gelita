<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/ekspor/xlsx') ?>" class="form">
  <?= csrf_field() ?>

  <div class="field">
    <label for="study_id">Studi</label>
    <select id="study_id" name="study_id">
      <option value="">— semua —</option>
      <?php foreach ($studies as $study): ?>
        <option value="<?= esc($study['id']) ?>"><?= esc($study['code']) ?></option>
      <?php endforeach ?>
    </select>
  </div>

  <div class="field"><label for="phase_code">Fase</label><input type="text" id="phase_code" name="phase_code"></div>

  <div class="field">
    <label for="school_id">Sekolah</label>
    <select id="school_id" name="school_id" <?= $isAdmin ? '' : 'disabled' ?>>
      <option value="">— semua yang boleh saya lihat —</option>
      <?php foreach ($schools as $school): ?>
        <option value="<?= esc($school['id']) ?>"><?= esc($school['name']) ?></option>
      <?php endforeach ?>
    </select>
    <?php if (! $isAdmin): ?>
      <small>Guru selalu dibatasi pada sekolahnya sendiri.</small>
    <?php endif ?>
  </div>
  <div class="field"><label for="class_level">Kelas</label><input type="text" id="class_level" name="class_level"></div>
  <div class="field"><label for="date_from">Dari</label><input type="date" id="date_from" name="date_from"></div>
  <div class="field"><label for="date_to">Sampai</label><input type="date" id="date_to" name="date_to"></div>

  <fieldset class="field">
    <legend>Sheet yang disertakan</legend>
    <?php foreach ($sheets as $sheet): ?>
      <?php $adminOnlySheet = in_array($sheet, $adminOnly, true); ?>
      <?php if ($adminOnlySheet && ! $isAdmin) {
          continue;
      } ?>
      <label class="check">
        <input type="checkbox" name="sheets[]" value="<?= esc($sheet) ?>" checked>
        <?= esc($sheet) ?><?= $adminOnlySheet ? ' (admin saja)' : '' ?>
      </label>
    <?php endforeach ?>
  </fieldset>

  <label class="check">
    <input type="checkbox" name="anonymized" value="1" <?= $isAdmin ? '' : 'checked disabled' ?>>
    Mode anonim
  </label>
  <?php if (! $isAdmin): ?>
    <p><small>Guru selalu mengekspor dalam mode anonim.</small></p>
  <?php endif ?>

  <button class="btn btn-primary" type="submit" formaction="<?= base_url('admin/ekspor/xlsx') ?>">Ekspor XLSX</button>
  <button class="btn btn-ghost" type="submit" formaction="<?= base_url('admin/ekspor/pdf') ?>">Ekspor PDF</button>
</form>

<h2>Ekspor terakhir</h2>
<p><small>Berkas ekspor dihapus otomatis setelah <?= esc($retention) ?> hari.</small></p>
<table class="data-table">
  <thead>
    <tr><th scope="col">#</th><th scope="col">Format</th><th scope="col">Anonim</th>
        <th scope="col">Status</th><th scope="col">Dibuat</th><th scope="col"></th></tr>
  </thead>
  <tbody>
    <?php foreach ($recent as $export): ?>
      <tr data-export="<?= esc($export['id']) ?>">
        <td><?= esc($export['id']) ?></td>
        <td><?= esc($export['format']) ?></td>
        <td><?= $export['anonymized'] ? 'ya' : 'tidak' ?></td>
        <td><?= esc($export['status']) ?><?= $export['error_message'] ? ' — ' . esc($export['error_message']) : '' ?></td>
        <td><?= esc($export['created_at']) ?></td>
        <td>
          <?php if ($export['status'] === 'done'): ?>
            <a class="btn btn-quiet" href="<?= base_url('admin/ekspor/unduh/' . $export['id']) ?>">Unduh</a>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
    <?php if ($recent === []): ?>
      <tr><td colspan="6"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>
<?= $this->endSection() ?>
