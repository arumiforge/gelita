<?php
/**
 * Laporan satu siswa (PDF) — ReportService::participantData().
 *
 * Dipakai guru untuk laporan individual. Pada mode anonim (selalu untuk guru)
 * hanya kode siswa yang tampil; nama dan nama pengguna tidak pernah dibaca.
 *
 * @var string                    $title
 * @var array<string, mixed>      $meta
 * @var array<string, mixed>      $profile     AnalyticsService::participantProfile()
 */
$num = static fn ($value, int $decimals = 0): string => fmt_num($value, $decimals, 'id');

$person = $profile['participant'];
$code   = (string) ($person['participant_code'] ?? $person['code'] ?? '');
?>
<html>
<head>
<meta charset="utf-8">
<?= view('pdf/_style', [], ['saveData' => false]) ?>
</head>
<body>

<div class="eyebrow muted small">GELITA · Laporan individual</div>
<h1><?= esc($title) ?></h1>

<table class="meta">
  <tr><td class="key">Kode siswa</td><td><b><?= esc($code) ?></b></td></tr>
  <?php if (! $meta['anonymized']): ?>
    <?php if (! empty($person['display_name'])): ?><tr><td class="key">Nama</td><td><?= esc($person['display_name']) ?></td></tr><?php endif ?>
    <?php if (! empty($person['school_name'])): ?><tr><td class="key">Sekolah</td><td><?= esc($person['school_name']) ?></td></tr><?php endif ?>
  <?php endif ?>
  <?php if (isset($person['class_level'])): ?><tr><td class="key">Kelas</td><td><?= esc(class_label($person['class_level'])) ?></td></tr><?php endif ?>
  <tr><td class="key">Penelitian</td><td><?= esc($meta['study']) ?></td></tr>
  <tr><td class="key">Dibuat oleh</td><td><?= esc($meta['requester']) ?> · <?= esc(fmt_date($meta['generated_at'], false, 'id')) ?></td></tr>
</table>

<h2>Ringkasan data mentah</h2>
<table class="kpi">
  <tr>
    <td><div class="kpi-value"><?= esc($num($profile['completed_attempts'])) ?></div><div class="kpi-label">pengerjaan selesai</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['first_pass_correct'])) ?> / <?= esc($num($profile['scorable_items'])) ?></div><div class="kpi-label">soal Berhasil pada Percobaan Pertama</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['final_correct'])) ?> / <?= esc($num($profile['scorable_items'])) ?></div><div class="kpi-label">soal benar di akhir</div></td>
  </tr>
  <tr>
    <td><div class="kpi-value"><?= esc(ms_to_human((int) $profile['total_duration_ms'])) ?></div><div class="kpi-label">total waktu mengerjakan</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['hint_uses'])) ?></div><div class="kpi-label">petunjuk dibuka</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['audio']['total_plays'])) ?></div><div class="kpi-label">suara diputar oleh siswa (<?= esc($num($profile['audio']['total_autoplays'] ?? 0)) ?> otomatis)</div></td>
  </tr>
</table>

<h2>Cara membaca</h2>
<p class="small">Angka di atas adalah hitungan apa adanya dari catatan permainan, tanpa skor atau persentase olahan. "Berhasil pada Percobaan Pertama" berarti soal sudah benar saat siswa pertama kali menekan Cek jawabanku (atau mengetuk benda pertama). Siswa boleh mencoba sampai 3 kali per soal; jawaban terakhir disimpan apa adanya.</p>

</body>
</html>
