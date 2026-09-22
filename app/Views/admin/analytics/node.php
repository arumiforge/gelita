<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<p><?= esc($level?->text('name', 'id') ?? '') ?> · <?= esc($node->text('title', 'id')) ?>
  (<?= esc($node->engine_type) ?>)</p>

<h2>Kesulitan</h2>
<pre class="json-block"><?= esc(json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>

<h2>Butir</h2>
<pre class="json-block"><?= esc(json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>

<a class="btn btn-quiet" href="<?= base_url('admin/analitik/node') ?>">Kembali</a>
<?= $this->include('partials/stage-note') ?>
<?= $this->endSection() ?>
