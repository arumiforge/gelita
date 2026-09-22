<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>Profil · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="profile" data-screen="profile">
  <h1>Profil <?= esc($participant->display_name ?? $participant->username) ?></h1>
  <?= $this->include('partials/flash') ?>

  <dl class="profile-stats">
    <dt>Kode peserta</dt><dd><?= esc($participant->participant_code) ?></dd>
    <dt><?= esc(lang('Game.shards')) ?></dt><dd><?= esc($progress['shards']) ?> / <?= esc($progress['shards_total']) ?></dd>
    <dt>Total bintang</dt><dd><?= esc($progress['total_stars']) ?></dd>
    <dt><?= esc(lang('Game.score')) ?></dt><dd><?= esc($progress['total_score']) ?></dd>
    <dt><?= esc(lang('Game.accuracyFirst')) ?></dt><dd><?= esc($meanFirstPass) ?>%</dd>
    <dt>Ketepatan akhir</dt><dd><?= esc($meanFinal) ?>%</dd>
    <dt><?= esc(lang('Game.timeSpent')) ?></dt><dd><?= esc(ms_to_human($durationMs)) ?></dd>
  </dl>

  <h2>Riwayat tantangan</h2>
  <table class="data-table">
    <thead>
      <tr>
        <th scope="col">Tantangan</th><th scope="col">Percobaan</th><th scope="col">Status</th>
        <th scope="col"><?= esc(lang('Game.score')) ?></th><th scope="col">Bintang</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($attempts as $attempt): ?>
        <tr>
          <td><?= esc($nodes[$attempt->challenge_node_id] ?? ('#' . $attempt->challenge_node_id)) ?></td>
          <td>#<?= esc($attempt->attempt_no) ?></td>
          <td><?= esc($attempt->status) ?></td>
          <td><?= esc($attempt->score) ?></td>
          <td><?= stars_html((int) $attempt->stars) ?></td>
        </tr>
      <?php endforeach ?>
      <?php if ($attempts === []): ?>
        <tr><td colspan="5">Belum ada tantangan yang dikerjakan.</td></tr>
      <?php endif ?>
    </tbody>
  </table>

  <?php if ($canReflect): ?>
    <a class="btn btn-primary" href="<?= base_url('refleksi') ?>"><?= esc(lang('Game.reflection')) ?></a>
  <?php endif ?>
  <a class="btn btn-quiet" href="<?= base_url('peta') ?>"><?= esc(lang('Game.back')) ?></a>
</section>
<?= $this->endSection() ?>
