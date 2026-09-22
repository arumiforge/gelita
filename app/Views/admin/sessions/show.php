<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<dl class="detail">
  <dt>Kode sesi</dt><dd><code><?= esc($session->session_code) ?></code></dd>
  <dt>Peserta</dt><dd><?= esc($participant?->participant_code ?? '—') ?></dd>
  <dt>Status</dt><dd><?= esc($session->status) ?></dd>
  <dt>Mulai</dt><dd><?= esc($session->started_at) ?></dd>
  <dt>Selesai</dt><dd><?= esc($session->ended_at ?? '—') ?></dd>
  <dt>Durasi</dt><dd><?= esc(ms_to_human((int) $session->duration_ms)) ?></dd>
</dl>

<h2>Percobaan</h2>
<table class="data-table">
  <thead>
    <tr>
      <th scope="col">Tantangan</th><th scope="col">#</th><th scope="col">Status</th>
      <th scope="col">Skor</th><th scope="col">Bintang</th><th scope="col">Tepat awal</th>
      <th scope="col">Petunjuk</th><th scope="col">Durasi</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($attempts as $attempt): ?>
      <tr>
        <td><?= esc($nodes[$attempt->challenge_node_id] ?? ('#' . $attempt->challenge_node_id)) ?></td>
        <td><?= esc($attempt->attempt_no) ?></td>
        <td><?= esc($attempt->status) ?></td>
        <td><?= esc($attempt->score) ?></td>
        <td><?= esc($attempt->stars) ?></td>
        <td><?= esc($attempt->first_pass_accuracy) ?>%</td>
        <td><?= esc($attempt->hint_count) ?></td>
        <td><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></td>
      </tr>
    <?php endforeach ?>
    <?php if ($attempts === []): ?>
      <tr><td colspan="8"><?= esc(lang('Admin.emptyDefault')) ?></td></tr>
    <?php endif ?>
  </tbody>
</table>

<a class="btn btn-primary" href="<?= base_url('admin/sesi/' . $session->id . '/event') ?>">Linimasa event</a>
<a class="btn btn-quiet" href="<?= base_url('admin/sesi') ?>">Kembali</a>
<?= $this->endSection() ?>
