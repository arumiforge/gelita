<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<p>Versi konten aktif: <code><?= esc($version) ?></code></p>

<?php foreach ($levels as $entry): ?>
  <?php $level = $entry['level']; ?>
  <section class="content-level">
    <h2><?= esc($level->text('name', 'id')) ?> (<?= esc($level->code) ?>)</h2>
    <p>
      <a class="btn btn-quiet" href="<?= base_url('admin/konten/level/' . $level->id) ?>">Sunting wilayah</a>
      <a class="btn btn-quiet" href="<?= base_url('admin/konten/bacaan/' . $level->id) ?>">Teks bacaan</a>
      <a class="btn btn-quiet" href="<?= base_url('admin/konten/pustaka/' . $level->id) ?>">Pustaka</a>
      <a class="btn btn-quiet" href="<?= base_url('admin/konten/dialog/' . $level->id) ?>">Dialog</a>
    </p>
    <table class="data-table">
      <thead>
        <tr><th scope="col">#</th><th scope="col">Judul</th><th scope="col">Engine</th>
            <th scope="col">Bank soal</th><th scope="col"></th></tr>
      </thead>
      <tbody>
        <?php foreach ($entry['nodes'] as $node): ?>
          <tr>
            <td><?= esc($node->sequence) ?></td>
            <td><?= esc($node->text('title', 'id')) ?></td>
            <td><?= esc($node->engine_type) ?></td>
            <td><?= esc($entry['bank'][$node->id] ?? 0) ?> butir</td>
            <td><a class="btn btn-quiet" href="<?= base_url('admin/konten/node/' . $node->id) ?>">Sunting</a></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </section>
<?php endforeach ?>

<form method="post" action="<?= base_url('admin/konten/verifikasi') ?>">
  <?= csrf_field() ?>
  <button class="btn btn-primary" type="submit">Verifikasi konten</button>
</form>

<a class="btn btn-ghost" href="<?= base_url('admin/konten/impor-bank') ?>">Impor bank soal</a>
<?= $this->endSection() ?>
