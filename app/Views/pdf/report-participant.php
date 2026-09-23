<?php
/**
 * Laporan satu peserta (PDF) — ReportService::participantData().
 *
 * Dipakai guru untuk laporan individual. Pada mode anonim (selalu untuk guru)
 * hanya kode peserta yang tampil; nama dan nama pengguna tidak pernah dibaca.
 *
 * @var string                    $title
 * @var array<string, mixed>      $meta
 * @var array<string, mixed>      $profile     AnalyticsService::participantProfile()
 * @var array<int, array>         $levels      level_id => {code, name}
 * @var array<string, mixed>|null $hardestNode
 */
$num = static fn ($value, int $decimals = 0): string => fmt_num($value, $decimals, 'id');
$pct = static fn ($value, bool $ratio = false): string => $value === null ? '—' : fmt_num((float) $value * ($ratio ? 100 : 1), 1, 'id') . '%';

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
  <tr><td class="key">Kode peserta</td><td><b><?= esc($code) ?></b></td></tr>
  <?php if (! $meta['anonymized']): ?>
    <?php if (! empty($person['display_name'])): ?><tr><td class="key">Nama</td><td><?= esc($person['display_name']) ?></td></tr><?php endif ?>
    <?php if (! empty($person['school_name'])): ?><tr><td class="key">Sekolah</td><td><?= esc($person['school_name']) ?></td></tr><?php endif ?>
  <?php endif ?>
  <?php if (isset($person['class_level'])): ?><tr><td class="key">Kelas</td><td><?= esc($person['class_level']) ?></td></tr><?php endif ?>
  <tr><td class="key">Studi</td><td><?= esc($meta['study']) ?></td></tr>
  <tr><td class="key">Dibuat oleh</td><td><?= esc($meta['requester']) ?> · <?= esc(fmt_date($meta['generated_at'], false, 'id')) ?></td></tr>
</table>

<h2>Ringkasan</h2>
<table class="kpi">
  <tr>
    <td><div class="kpi-value"><?= esc($num($profile['total_score'], 1)) ?></div><div class="kpi-label">rata-rata skor wilayah</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['completed_nodes'])) ?></div><div class="kpi-label">tantangan selesai</div></td>
    <td><div class="kpi-value"><?= esc(ms_to_human((int) $profile['total_duration_ms'])) ?></div><div class="kpi-label">total waktu mengerjakan</div></td>
  </tr>
  <tr>
    <td><div class="kpi-value"><?= esc($num($profile['hint_uses'])) ?></div><div class="kpi-label">petunjuk dibuka</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['answer_changes'])) ?></div><div class="kpi-label">perubahan jawaban</div></td>
    <td><div class="kpi-value"><?= esc($num($profile['audio']['total_plays'])) ?></div><div class="kpi-label">pemutaran audio</div></td>
  </tr>
</table>

<h2>Skor per wilayah</h2>
<?php if ($profile['level_scores'] === []): ?>
  <p class="muted">Belum ada tantangan yang selesai.</p>
<?php else: ?>
  <?php
  $bars = [];

  foreach ($levels as $levelId => $level) {
      $score  = $profile['level_scores'][$levelId] ?? null;
      $bars[] = ['label' => $level['name'], 'value' => (float) $score, 'text' => $score === null ? 'belum' : $num($score, 1)];
  }
  ?>
  <?= view('pdf/_bars', ['bars' => $bars, 'caption' => 'Skor per wilayah'], ['saveData' => false]) ?>
<?php endif ?>

<?php if ($hardestNode !== null): ?>
  <p class="note">Tantangan yang paling menantang: <b><?= esc($hardestNode['title']) ?></b> (<?= esc($hardestNode['level']) ?>), tepat sejak awal <?= esc($pct($hardestNode['first_pass_accuracy'])) ?>.</p>
<?php endif ?>

<h2>Penguasaan indikator</h2>
<?php if ($profile['indicators'] === []): ?>
  <p class="muted">Belum ada butir terjawab.</p>
<?php else: ?>
  <?php
  $bars = [];

  foreach ($profile['indicators'] as $indicator) {
      $bars[] = [
          'label' => $indicator['name'],
          'value' => $indicator['mastery_ratio'] * 100,
          'text'  => $pct($indicator['mastery_ratio'], true),
          'sub'   => $num($indicator['correct_count']) . ' dari ' . $num($indicator['evidence_count']) . ' butir',
      ];
  }
  ?>
  <?= view('pdf/_bars', ['bars' => $bars, 'caption' => 'Penguasaan indikator', 'alt' => true], ['saveData' => false]) ?>
<?php endif ?>

<?php if ((int) $profile['pre_post']['pairs'] > 0): ?>
  <h2>Pretest dan posttest</h2>
  <p>Skor pretest <b><?= esc($num($profile['pre_post']['pretest'], 1)) ?></b>, posttest <b><?= esc($num($profile['pre_post']['posttest'], 1)) ?></b>, selisih <b><?= esc(($profile['pre_post']['delta'] >= 0 ? '+' : '') . $num($profile['pre_post']['delta'], 1)) ?></b>.</p>
<?php endif ?>

<h2>Cara membaca</h2>
<p class="small">Skor 0–100 dihitung server: 70% tepat sejak awal, 20% ketepatan akhir setelah perbaikan, 10% kemandirian (berkurang oleh petunjuk dan pemeriksaan ulang). Penguasaan indikator adalah rasio butir yang benar sejak awal, bukan label lulus/tidak lulus. Pustaka Kedu dan audio tidak memengaruhi skor.</p>

</body>
</html>
