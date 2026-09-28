<?php
/**
 * Linimasa event — `/admin/sesi/{id}/event` → SessionController::timeline
 *
 * Jalur drilldown terdalam: Ringkasan → Fase → Peserta → Level → Node → Item
 * → Event. Waktu ditampilkan hingga milidetik; filter jenis event lewat GET.
 * Jenis event tampil dengan nama Indonesia (admin_label('event')); kodenya
 * tetap tertulis kecil untuk peneliti yang mencocokkan dengan data unduhan.
 *
 * @var App\Entities\GameSession    $session
 * @var list<array<string, mixed>>  $rows
 * @var list<string>                $types
 * @var string                      $type
 * @var array<int, string>          $nodes
 */
$ms = static function ($value): string {
    if ($value === null || $value === '') {
        return '—';
    }
    $time = DateTimeImmutable::createFromFormat('Y-m-d H:i:s.u', (string) $value)
        ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $value);

    return $time === false ? (string) $value : $time->format('d-m-Y H:i:s.v');
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Catatan aktivitas',
    'eyebrow' => 'Sesi ' . substr((string) $session->session_code, 0, 12),
    'lead'    => 'Semua yang dilakukan siswa selama sesi ini, berurutan. "Waktu di perangkat" adalah jam di perangkat siswa; "Diterima sistem" bisa lebih lambat bila catatan sempat tertunda karena internet terputus.',
    'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi/' . $session->id) . '">' . icon('left') . ' Detail sesi</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar" aria-label="Pilih jenis aktivitas">
  <div class="field">
    <label for="f-type">Jenis aktivitas</label>
    <select id="f-type" name="type">
      <option value="">Semua jenis (<?= count($types) ?>)</option>
      <?php foreach ($types as $option): ?>
        <option value="<?= esc($option, 'attr') ?>" <?= $type === $option ? 'selected' : '' ?>><?= esc(admin_label('event', $option)) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="filter-actions">
    <button class="btn btn-primary btn-sm" type="submit"><?= icon('search') ?> Terapkan</button>
    <?php if ($type !== ''): ?>
      <a class="btn btn-quiet btn-sm" href="<?= esc(current_url(), 'attr') ?>">Atur ulang</a>
    <?php endif ?>
  </div>
</form>

<?= component('admin-table', [
    'rows'         => $rows,
    'caption'      => 'Catatan aktivitas sesi',
    'emptyMessage' => $type !== ''
        ? 'Tidak ada aktivitas jenis ini pada sesi ini.'
        : 'Belum ada aktivitas tercatat. Permainan mengirim catatan setiap beberapa detik selama siswa bermain.',
    'columns' => [
        'sequence_no'        => ['label' => 'No.', 'format' => 'num'],
        'occurred_at'        => ['label' => 'Waktu di perangkat', 'render' => static fn (array $r): string => '<span class="timeline-time">' . esc($ms($r['occurred_at'])) . '</span>'],
        'server_received_at' => ['label' => 'Diterima sistem', 'render' => static fn (array $r): string => '<span class="timeline-time">' . esc($ms($r['server_received_at'])) . '</span>'],
        'event_type'         => ['label' => 'Aktivitas', 'render' => static fn (array $r): string => '<span class="timeline-type">' . esc(admin_label('event', (string) $r['event_type'])) . '</span>'
            . '<span class="cell-sub"><code>' . esc($r['event_type']) . '</code></span>'],
        'challenge_node_id'  => ['label' => 'Tantangan / soal', 'render' => static fn (array $r): string => $r['challenge_node_id'] === null ? '—'
            : esc($nodes[$r['challenge_node_id']] ?? '#' . $r['challenge_node_id'])
              . ($r['challenge_item_id'] !== null ? '<span class="cell-sub">soal nomor ' . esc($r['challenge_item_id']) . '</span>' : '')],
        'payload_json'       => ['label' => 'Keterangan', 'format' => 'json'],
    ],
]) ?>
<?= $this->endSection() ?>
