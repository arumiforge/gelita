<?php
/**
 * Laporan satu siswa (PDF, Dok 3 §10) — ReportService::participantData().
 *
 * Tabel formal data mentah: identitas (sesuai hak), sesi & perangkat,
 * progres per tantangan aktif, kritik & saran. Tanpa skor, indikator,
 * atau kalimat penilaian. Pada mode anonim (selalu untuk guru) hanya kode
 * siswa dan kode sekolah samaran yang tampil.
 *
 * @var string               $title
 * @var array<string, mixed> $meta
 * @var array<string, mixed> $profile AnalyticsService::participantProfile()
 */
$person = $profile['participant'];
$dash   = static fn ($value): string => $value === null || $value === '' ? '—' : (string) $value;
$time   = static fn ($value): string => $value === null || $value === '' ? '—' : fmt_date((string) $value, true, 'id');
$ms     = static fn ($value): string => $value === null ? '—' : fmt_num((int) $value, 0, 'id');
$status = [
    'not_started' => 'belum dimulai',
    'in_progress' => 'sedang dikerjakan',
    'completed'   => 'selesai',
    'abandoned'   => 'ditinggalkan',
];
?>
<html>
<head>
<meta charset="utf-8">
<?= view('pdf/_style', [], ['saveData' => false]) ?>
</head>
<body>

<div class="eyebrow muted small">GELITA · Laporan satu siswa</div>
<h1><?= esc($title) ?></h1>

<table class="meta">
  <tr><td class="key">Kode siswa</td><td><b><?= esc($person['participant_code']) ?></b></td></tr>
  <?php if (! $meta['anonymized']): ?>
    <tr><td class="key">Nama</td><td><?= esc($dash($person['display_name'] ?? null)) ?></td></tr>
    <tr><td class="key">Nama pengguna</td><td><?= esc($dash($person['username'] ?? null)) ?></td></tr>
    <tr><td class="key">Sekolah</td><td><?= esc($dash($person['school_name'] ?? null)) ?></td></tr>
  <?php endif ?>
  <tr><td class="key">Kode sekolah (samaran)</td><td><?= esc($dash($person['school_ref'])) ?></td></tr>
  <tr><td class="key">Umur · jenis kelamin · kelas</td><td><?= esc($dash($person['age'])) ?> · <?= esc(admin_label('gender', $person['gender'])) ?> · <?= esc(class_label($person['class_level'])) ?></td></tr>
  <tr><td class="key">Terdaftar</td><td><?= esc($time($person['registered_at'])) ?></td></tr>
  <tr><td class="key">Penelitian</td><td><?= esc($meta['study']) ?></td></tr>
  <tr><td class="key">Dibuat oleh</td><td><?= esc($meta['requester']) ?> · <?= esc(fmt_date($meta['generated_at'], true, 'id')) ?></td></tr>
</table>

<h2>Sesi bermain</h2>
<table class="data compact">
  <tr><th>Kode sesi</th><th>Bahasa</th><th>Status</th><th>Mulai</th><th>Terakhir aktif</th><th class="num">Durasi aktif (mdtk)</th><th>Perangkat</th></tr>
  <?php foreach ($profile['sessions'] as $s): ?>
    <tr>
      <td><?= esc(substr((string) $s['session_code'], 0, 8)) ?>…</td>
      <td><?= esc(admin_label('locale', $s['locale'])) ?></td>
      <td><?= esc(admin_label('status', $s['status'])) ?></td>
      <td><?= esc($time($s['started_at'])) ?></td>
      <td><?= esc($time($s['last_active_at'])) ?></td>
      <td class="num"><?= esc($ms($s['duration_ms'])) ?></td>
      <td><?= esc(implode(' · ', array_filter([$s['device_type'], $s['os_name'], $s['browser_name'], $s['screen_size']]))) ?: '—' ?></td>
    </tr>
  <?php endforeach ?>
  <?php if ($profile['sessions'] === []): ?><tr><td colspan="7">—</td></tr><?php endif ?>
</table>

<h2>Perangkat</h2>
<table class="data compact">
  <tr><th>Dicatat</th><th>Asal</th><th>Perangkat</th><th>Sistem</th><th>Peramban</th><th>Layar (px)</th><th>Jendela (px)</th></tr>
  <?php foreach ($profile['devices'] as $d): ?>
    <tr>
      <td><?= esc($time($d['created_at'])) ?></td>
      <td><?= esc($d['via']) ?></td>
      <td><?= esc((string) ($d['device_type'] ?? '')) ?: '—' ?></td>
      <td><?= esc(trim($d['os_name'] . ' ' . $d['os_version'])) ?: '—' ?></td>
      <td><?= esc(trim($d['browser_name'] . ' ' . $d['browser_version'])) ?: '—' ?></td>
      <td><?= esc($d['screen_w'] === null ? '—' : $d['screen_w'] . ' × ' . $d['screen_h']) ?></td>
      <td><?= esc($d['viewport_w'] === null ? '—' : $d['viewport_w'] . ' × ' . $d['viewport_h']) ?></td>
    </tr>
  <?php endforeach ?>
  <?php if ($profile['devices'] === []): ?><tr><td colspan="7">—</td></tr><?php endif ?>
</table>

<h2>Progres tantangan</h2>
<table class="data compact">
  <tr><th>Kode</th><th>Tantangan</th><th>Status</th><th>Variasi</th><th class="num">Pengerjaan</th><th class="num">Cek jawaban</th><th class="num">Berhasil pada Percobaan Pertama</th><th class="num">Bintang</th><th class="num">Durasi (mdtk)</th></tr>
  <?php foreach ($profile['progress'] as $p): ?>
    <tr>
      <td><?= esc($p['node_ref']) ?></td>
      <td><?= esc($p['title']) ?></td>
      <td><?= esc($status[$p['status']] ?? $p['status']) ?></td>
      <td><?= esc($dash($p['variant_group'])) ?></td>
      <td class="num"><?= esc($p['attempts']) ?></td>
      <td class="num"><?= esc($dash($p['check_count'])) ?></td>
      <td class="num"><?= esc($p['first_pass_correct'] === null ? '—' : $p['first_pass_correct'] . ' dari ' . $p['scorable_items']) ?></td>
      <td class="num"><?= esc($dash($p['stars'])) ?></td>
      <td class="num"><?= esc($ms($p['duration_ms'])) ?></td>
    </tr>
  <?php endforeach ?>
</table>
<p class="small muted">Baris yang ditampilkan per tantangan: pengerjaan selesai dengan bintang terbanyak (yang paling awal bila sama), selain itu pengerjaan terakhir.</p>

<h2>Kritik &amp; saran</h2>
<table class="data compact">
  <tr><th>Dikirim</th><th class="num">Bintang (1–5)</th><th>Paling disukai</th><th>Paling sulit</th><th>Hal baru</th><th>Saran</th></tr>
  <?php foreach ($profile['feedback'] as $f): ?>
    <tr>
      <td><?= esc($time($f['submitted_at'])) ?></td>
      <td class="num"><?= esc($f['rating']) ?></td>
      <td><?= esc($dash($f['liked_most'])) ?></td>
      <td><?= esc($dash($f['hardest_part'])) ?></td>
      <td><?= esc($dash($f['new_learning'])) ?></td>
      <td><?= esc($dash($f['suggestion'])) ?></td>
    </tr>
  <?php endforeach ?>
  <?php if ($profile['feedback'] === []): ?><tr><td colspan="6">—</td></tr><?php endif ?>
</table>

</body>
</html>
