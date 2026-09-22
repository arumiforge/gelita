<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($pageTitle) ?> · Panel GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1><?= esc($pageTitle) ?></h1>
<?= $this->include('partials/flash') ?>

<p>Sesi <code><?= esc($session->session_code) ?></code></p>

<?= $this->include('components/admin-table', [
    'columns' => [
        'sequence_no'        => '#',
        'event_type'         => 'Jenis event',
        'occurred_at'        => 'Waktu client',
        'server_received_at' => 'Waktu server',
        'payload_json'       => 'Payload',
    ],
    'rows' => $rows,
]) ?>

<a class="btn btn-quiet" href="<?= base_url('admin/sesi/' . $session->id) ?>">Kembali</a>
<?= $this->endSection() ?>
