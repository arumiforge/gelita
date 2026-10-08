<?php
/**
 * Detail sesi — `/admin/sesi/{id}` → SessionController::show (Dok 3 §8.3)
 *
 * Ringkasan sesi, pengerjaan di sesi ini, dan linimasa event lengkap
 * (urutan, waktu klien, waktu server, event, layar/tantangan/soal, payload
 * JSON) dengan urut, pagination, filter jenis event, dan CSV.
 *
 * @var array<string, mixed>      $session
 * @var array<string, mixed>|null $participant
 * @var array<string, mixed>|null $progress
 * @var array<string, mixed>      $attempts  bentuk tabel
 * @var array<string, mixed>      $timeline  AdminTable::page()
 * @var array<string, string>     $types
 * @var string                    $type
 */
$device = implode(' · ', array_filter([
    (string) $session['device_type'] === '' ? null : admin_label('device', (string) $session['device_type']),
    $session['os_name'], $session['browser_name'], $session['screen_size'],
]));
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Sesi ' . $session['session_code'],
    'lead'    => 'Siswa ' . ($participant['participant_code'] ?? '—') . ' · mulai ' . fmt_date($session['started_at'], true, 'id'),
    'actions' => $participant === null ? null
        : '<a class="btn" href="' . base_url('admin/peserta/' . $participant['id']) . '">' . icon('user') . ' Detail siswa</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <h2 class="panel-title">Ringkasan</h2>
  <dl class="detail-grid">
    <div><dt>Siswa</dt><dd class="mono"><?= esc($participant['participant_code'] ?? '—') ?></dd></div>
    <div><dt>Status</dt><dd><?= App\Libraries\AdminTable::cell($session['status'], 'status') ?></dd></div>
    <div><dt>Bahasa</dt><dd><?= esc(admin_label('locale', (string) $session['locale'])) ?></dd></div>
    <div><dt>Mulai</dt><dd><?= esc(fmt_date($session['started_at'], true, 'id')) ?></dd></div>
    <div><dt>Aktif terakhir</dt><dd><?= esc(fmt_date($session['last_active_at'], true, 'id')) ?></dd></div>
    <div><dt>Berakhir</dt><dd><?= esc(fmt_date($session['ended_at'], true, 'id')) ?></dd></div>
    <div><dt>Durasi</dt><dd class="num"><?= App\Libraries\AdminTable::cell($session['duration_ms'], 'ms') ?></dd></div>
    <div><dt>Tantangan selesai</dt><dd class="num"><?= esc($progress['completed_nodes'] ?? 0) ?></dd></div>
    <div><dt>Bintang</dt><dd class="num"><?= esc($progress['total_stars'] ?? 0) ?></dd></div>
    <div><dt>Perangkat</dt><dd><?= esc($device !== '' ? $device : '—') ?></dd></div>
  </dl>
</section>

<?= component('data-table', [
    'table'        => $attempts,
    'id'           => 'pengerjaan-sesi',
    'title'        => 'Pengerjaan di sesi ini',
    'csv'          => false,
    'paginate'     => false,
    'emptyMessage' => 'Belum ada tantangan yang dikerjakan pada sesi ini.',
]) ?>

<form method="get" class="filter-bar" aria-label="Filter linimasa" id="linimasa">
  <div class="field">
    <label for="f-type">Jenis event</label>
    <select id="f-type" name="type">
      <option value="">Semua jenis (<?= count($types) ?>)</option>
      <?php foreach ($types as $code => $label): ?>
        <option value="<?= esc($code, 'attr') ?>"<?= $type === $code ? ' selected' : '' ?>><?= esc($label) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="filter-actions">
    <?php if ($type !== ''): ?>
      <a class="btn btn-quiet" href="<?= base_url('admin/sesi/' . $session['id']) ?>#linimasa">Atur ulang</a>
    <?php endif ?>
    <button class="btn btn-primary" type="submit"><?= icon('filter') ?> Terapkan</button>
  </div>
</form>

<?= component('data-table', [
    'table'        => $timeline,
    'id'           => 'linimasa-tabel',
    'title'        => 'Linimasa event',
    'anchor'       => 'linimasa',
    'emptyMessage' => $type !== '' ? 'Tidak ada event jenis ini pada sesi ini.' : 'Belum ada event tercatat.',
    'render'       => [
        'event_type' => static fn (array $r): string => '<span title="' . esc(admin_label('event', (string) $r['event_type']), 'attr') . '">' . esc($r['event_type']) . '</span>',
    ],
]) ?>
<?= $this->endSection() ?>
