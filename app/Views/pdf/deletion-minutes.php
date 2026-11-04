<?php
/**
 * Minutes of a parent's data request (PDF, part 4: 30 §7.6) —
 * DataRequestService::minutesHtml(). Codes, dates, row counts, and the
 * checklist; never a child's or parent's name. Staff appear by account name.
 *
 * @var array<string, mixed>                       $request   data_deletion_requests row
 * @var array<string, int>                         $counts    rows per table
 * @var array<string, array{at: string, by: ?int}> $checklist
 * @var list<string>                               $items     checklist items of the kind
 * @var array<int, string>                         $staff     id => username
 */
$time = static fn ($value): string => $value === null || $value === '' ? '—' : fmt_date((string) $value, true, 'id');
$who  = static fn ($id): string => $id === null ? '—' : ($staff[(int) $id] ?? '#' . $id);
?>
<html>
<head>
<meta charset="utf-8">
<?= view('pdf/_style', [], ['saveData' => false]) ?>
</head>
<body>
<div class="eyebrow muted small">GELITA · Berita acara</div>
<h1>Berita acara permintaan hapus data no. <?= esc((string) $request['id']) ?></h1>

<table class="meta">
  <tr><td class="key">Kode peserta</td><td><b><?= esc((string) $request['participant_code']) ?></b></td></tr>
  <tr><td class="key">Jenis</td><td><?= esc(lang('Admin.requestKind_' . $request['kind'])) ?></td></tr>
  <tr><td class="key">Diterima</td><td><?= esc($time($request['received_at'])) ?> (<?= esc(lang('Admin.requestVia_' . $request['received_via'])) ?>)</td></tr>
  <tr><td class="key">Identitas pemohon dicocokkan</td><td><?= esc($time($request['verified_at'])) ?></td></tr>
  <tr><td class="key">Diajukan oleh</td><td><?= esc($who($request['requested_by'])) ?></td></tr>
  <tr><td class="key">Dikonfirmasi oleh</td><td><?= esc($who($request['confirmed_by'])) ?>, <?= esc($time($request['confirmed_at'])) ?></td></tr>
  <tr><td class="key">Dijalankan</td><td><?= esc($who($request['approved_by'])) ?>, <?= esc($time($request['executed_at'])) ?></td></tr>
  <tr><td class="key">Orang tua diberi tahu</td><td><?= esc($time($checklist['parent_notified']['at'] ?? null)) ?></td></tr>
</table>

<h2>Jumlah baris yang dihapus</h2>
<table class="data">
  <tr><th>Tabel</th><th>Baris</th></tr>
  <?php foreach ($counts as $table => $count): ?>
    <tr><td><?= esc((string) $table) ?></td><td><?= esc((string) $count) ?></td></tr>
  <?php endforeach ?>
  <?php if ($counts === []): ?><tr><td colspan="2">Tidak ada data di sistem untuk jenis ini.</td></tr><?php endif ?>
</table>

<h2>Daftar periksa</h2>
<table class="data">
  <tr><th>Butir</th><th>Tanggal</th><th>Oleh</th></tr>
  <?php foreach ($items as $item): ?>
    <tr><td><?= esc(lang('Admin.requestItem_' . $item)) ?></td><td><?= esc($time($checklist[$item]['at'] ?? null)) ?></td><td><?= esc($who($checklist[$item]['by'] ?? null)) ?></td></tr>
  <?php endforeach ?>
</table>

<table class="meta" style="margin-top: 24mm">
  <tr><td>Ketua peneliti<br><br><br>(..............................)</td><td>Admin<br><br><br>(..............................)</td></tr>
</table>
</body>
</html>
