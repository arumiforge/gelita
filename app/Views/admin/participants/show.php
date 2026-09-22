<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<dl class="detail">
  <dt>Kode</dt><dd><?= esc($participant->participant_code) ?></dd>
  <dt>Nama pengguna</dt><dd><?= esc($participant->username) ?></dd>
  <dt>Nama</dt><dd><?= esc($participant->display_name ?? '—') ?></dd>
  <dt>Kelas</dt><dd><?= esc($participant->class_level ?? '—') ?></dd>
  <dt>Sekolah</dt><dd><?= esc($participant->school_name_snapshot ?? '—') ?></dd>
  <dt>Syarat sandi terpenuhi (percobaan pertama)</dt>
  <dd><?= esc($participant->pw_first_submit_criteria ?? '—') ?> / 5</dd>
  <dt>Penolakan sandi lemah</dt><dd><?= esc($participant->pw_weak_submit_count) ?></dd>
</dl>

<h2>Capaian</h2>
<pre class="json-block"><?= esc(json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>

<h2>Reset kata sandi</h2>
<form method="post" action="<?= base_url('admin/peserta/' . $participant->id . '/reset-sandi') ?>" class="form form-danger">
  <?= csrf_field() ?>
  <div class="field">
    <label for="confirm">Ketik RESET untuk mengatur ulang kata sandi siswa ini</label>
    <input type="text" id="confirm" name="confirm" required pattern="RESET" autocomplete="off">
  </div>
  <button class="btn btn-danger" type="submit">Atur ulang kata sandi</button>
</form>

<a class="btn btn-quiet" href="<?= base_url('admin/peserta') ?>">Kembali</a>
<?= $this->endSection() ?>
