<?php
/**
 * Laporan studi (PDF) — ReportService::studyData().
 *
 * Seluruh nilai yang berasal dari database atau input pengguna (nama sekolah,
 * nama studi, judul node) lewat esc(): mPDF memproses HTML, jadi HTML yang
 * tidak bersih adalah lubang injeksi. Raw event tidak pernah masuk laporan ini.
 *
 * @var string               $title
 * @var array<string, mixed> $meta
 * @var array<string, mixed> $summary     AnalyticsService::summary()
 * @var array<string, mixed> $cohort      ParticipantModel::cohortSummary()
 * @var array<int, array>    $levels      AnalyticsService::levelBreakdown()
 * @var array<int, array>    $nodes       AnalyticsService::nodeDifficulty()
 * @var array<string, array> $indicators  AnalyticsService::indicatorMastery()
 * @var array<string, mixed> $security    AnalyticsService::digitalSecurityLiteracy()
 * @var array<string, mixed> $prePost     AnalyticsService::prePostComparison()
 */
$num = static fn ($value, int $decimals = 0): string => fmt_num($value, $decimals, 'id');
$pct = static fn ($value, bool $ratio = false): string => $value === null ? '—' : fmt_num((float) $value * ($ratio ? 100 : 1), 1, 'id') . '%';
$levelNames = [];

foreach ($levels as $level) {
    $levelNames[$level['level_id']] = $level['name'];
}

$distribution = static function (array $groups, int $total) use ($num): array {
    $bars = [];

    foreach ($groups as $label => $count) {
        $bars[] = [
            'label' => $label === '' ? '(kosong)' : (string) $label,
            'value' => $total > 0 ? $count / $total * 100 : 0,
            'text'  => $num($count),
        ];
    }

    return $bars;
};
?>
<html>
<head>
<meta charset="utf-8">
<?= view('pdf/_style', [], ['saveData' => false]) ?>
</head>
<body>

<div class="cover">
  <div class="eyebrow">GELITA · Game Edukasi Literasi dan Etnopedagogi Kedu</div>
  <h1><?= esc($title) ?></h1>
  <p class="muted">Ringkasan proses dan capaian belajar dari data permainan yang tercatat server.</p>

  <table class="meta">
    <tr><td class="key">Studi</td><td><?= esc($meta['study']) ?></td></tr>
    <tr><td class="key">Fase</td><td><?= esc($meta['phase']) ?></td></tr>
    <tr><td class="key">Rentang tanggal</td><td><?= esc(($meta['date_from'] ?? 'awal') . ' s.d. ' . ($meta['date_to'] ?? 'sekarang')) ?></td></tr>
    <?php if ($meta['school'] !== null): ?><tr><td class="key">Sekolah</td><td><?= esc($meta['school']) ?></td></tr><?php endif ?>
    <?php if ($meta['level'] !== null): ?><tr><td class="key">Wilayah</td><td><?= esc($meta['level']) ?></td></tr><?php endif ?>
    <?php if ($meta['class_level'] !== null): ?><tr><td class="key">Kelas</td><td><?= esc($meta['class_level']) ?></td></tr><?php endif ?>
    <?php if ($meta['locale'] !== null): ?><tr><td class="key">Bahasa</td><td><?= esc($meta['locale']) ?></td></tr><?php endif ?>
    <tr><td class="key">Dibuat oleh</td><td><?= esc($meta['requester']) ?></td></tr>
    <tr><td class="key">Waktu dibuat</td><td><?= esc(fmt_date($meta['generated_at'], false, 'id')) ?></td></tr>
    <tr><td class="key">Nomor export</td><td>#<?= (int) $meta['export_id'] ?></td></tr>
  </table>

  <div class="note small">Laporan ini tidak memuat nama peserta, nama pengguna, kata sandi, atau raw event. Data lengkap untuk analisis lanjutan tersedia sebagai workbook XLSX.</div>
</div>

<pagebreak />

<h2>1. Ringkasan peserta</h2>
<table class="kpi">
  <tr>
    <td><div class="kpi-value"><?= esc($num($summary['participant_count'])) ?></div><div class="kpi-label">peserta</div></td>
    <td><div class="kpi-value"><?= esc($num($summary['session_count'])) ?></div><div class="kpi-label">sesi bermain</div></td>
    <td><div class="kpi-value"><?= esc($pct($summary['completion_rate'], true)) ?></div><div class="kpi-label">sesi tuntas 15 tantangan</div></td>
  </tr>
</table>

<?php if ((int) $cohort['total'] > 0): ?>
  <h3>Jenis kelamin</h3>
  <?= view('pdf/_bars', ['bars' => $distribution($cohort['by_gender'], (int) $cohort['total']), 'caption' => 'Sebaran jenis kelamin'], ['saveData' => false]) ?>
  <h3>Kelas</h3>
  <?= view('pdf/_bars', ['bars' => $distribution($cohort['by_class_level'], (int) $cohort['total']), 'caption' => 'Sebaran kelas', 'alt' => true], ['saveData' => false]) ?>
  <h3>Provinsi</h3>
  <?= view('pdf/_bars', ['bars' => $distribution($cohort['by_province'], (int) $cohort['total']), 'caption' => 'Sebaran provinsi'], ['saveData' => false]) ?>
<?php else: ?>
  <p class="muted">Belum ada peserta dalam cakupan ini.</p>
<?php endif ?>

<h2>2. Ringkasan proses</h2>
<table class="kpi">
  <tr>
    <td><div class="kpi-value"><?= esc($num($summary['avg_score'], 1)) ?></div><div class="kpi-label">rata-rata skor tantangan (0–100)</div></td>
    <td><div class="kpi-value"><?= esc($pct($summary['avg_first_pass_accuracy'])) ?></div><div class="kpi-label">rata-rata tepat sejak awal</div></td>
    <td><div class="kpi-value"><?= esc(ms_to_human((int) $summary['avg_duration_ms'])) ?></div><div class="kpi-label">rata-rata durasi sesi</div></td>
  </tr>
  <tr>
    <td><div class="kpi-value"><?= esc($num($summary['hint_usage'], 2)) ?></div><div class="kpi-label">petunjuk per tantangan</div></td>
    <td><div class="kpi-value"><?= esc($num($summary['audio_usage']['total_plays'])) ?></div><div class="kpi-label">pemutaran audio (<?= esc($num($summary['audio_usage']['total_replays'])) ?> diulang)</div></td>
    <td><div class="kpi-value"><?= esc($pct($security['password']['participants'] > 0 ? $security['password']['strong_first_try'] / $security['password']['participants'] : 0, true)) ?></div><div class="kpi-label">langsung membuat sandi kuat</div></td>
  </tr>
</table>

<h2>3. Capaian per wilayah</h2>
<?php
$levelBars = [];
$passBars  = [];

foreach ($levels as $level) {
    $levelBars[] = ['label' => $level['name'], 'value' => $level['avg_score'], 'text' => $num($level['avg_score'], 1), 'sub' => $num($level['completed_attempts']) . ' tantangan selesai'];
    $passBars[]  = ['label' => $level['name'], 'value' => $level['avg_first_pass'], 'text' => $pct($level['avg_first_pass'])];
}
?>
<h3>Rata-rata skor</h3>
<?= view('pdf/_bars', ['bars' => $levelBars, 'caption' => 'Rata-rata skor per wilayah'], ['saveData' => false]) ?>
<h3>Tepat sejak awal</h3>
<?= view('pdf/_bars', ['bars' => $passBars, 'caption' => 'Tepat sejak awal per wilayah', 'alt' => true], ['saveData' => false]) ?>

<h2>4. Kesulitan tantangan</h2>
<p class="muted small">Indeks kesulitan 0–100: 40% ketidaktepatan awal, 20% pengulangan, 20% petunjuk, 10% durasi, 10% tantangan ditinggalkan. Makin tinggi makin sulit.</p>
<table class="data compact">
  <thead>
    <tr><th style="width: 13%">Wilayah</th><th style="width: 29%">Tantangan</th><th class="num" style="width: 11%">Percobaan</th><th class="num" style="width: 9%">Tepat awal</th><th class="num" style="width: 9%">Tepat akhir</th><th class="num" style="width: 11%">Petunjuk</th><th class="num" style="width: 9%">Median durasi</th><th class="num" style="width: 9%">Indeks</th></tr>
  </thead>
  <tbody>
    <?php foreach ($nodes as $node): ?>
      <tr>
        <td><?= esc($levelNames[$node['level_id']] ?? '') ?></td>
        <td><?= (int) $node['sequence'] ?>. <?= esc($node['title']) ?><br><span class="muted small"><?= esc($node['engine_type']) ?></span></td>
        <td class="num"><?= esc($num($node['attempts'])) ?></td>
        <td class="num"><?= esc($pct($node['mean_first_pass'])) ?></td>
        <td class="num"><?= esc($pct($node['mean_final'])) ?></td>
        <td class="num"><?= esc($num($node['mean_hint'], 2)) ?></td>
        <td class="num"><?= esc(ms_to_human((int) $node['median_duration_ms'])) ?></td>
        <td class="num"><b><?= esc($num($node['difficulty_index'], 1)) ?></b></td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<h2>5. Penguasaan indikator</h2>
<?php if ($indicators === []): ?>
  <p class="muted">Belum ada butir terjawab dalam cakupan ini.</p>
<?php else: ?>
  <?php
  $indicatorBars = [];

  foreach ($indicators as $indicator) {
      $indicatorBars[] = [
          'label' => $indicator['name'],
          'value' => $indicator['mastery_ratio'] * 100,
          'text'  => $pct($indicator['mastery_ratio'], true),
          'sub'   => $num($indicator['correct_count']) . ' dari ' . $num($indicator['evidence_count']) . ' bukti',
      ];
  }
  ?>
  <?= view('pdf/_bars', ['bars' => $indicatorBars, 'caption' => 'Rasio penguasaan per indikator'], ['saveData' => false]) ?>
<?php endif ?>

<?php if ($security['pillars'] !== []): ?>
  <h3>Pilar literasi digital</h3>
  <?php
  $pillarBars = [];

  foreach ($security['pillars'] as $pillar => $stats) {
      $pillarBars[] = ['label' => (string) $pillar, 'value' => $stats['accuracy'] * 100, 'text' => $pct($stats['accuracy'], true), 'sub' => $num($stats['appeared']) . ' jawaban'];
  }
  ?>
  <?= view('pdf/_bars', ['bars' => $pillarBars, 'caption' => 'Ketepatan per pilar literasi digital', 'alt' => true], ['saveData' => false]) ?>
<?php endif ?>

<h2>6. Pretest dan posttest</h2>
<?php if ((int) $prePost['pairs'] === 0 && (int) $prePost['incompatible_pairs'] === 0): ?>
  <p class="muted">Belum ada peserta yang menuntaskan sesi pretest dan posttest dalam cakupan ini.</p>
<?php else: ?>
  <table class="kpi">
    <tr>
      <td><div class="kpi-value"><?= esc($num($prePost['pretest'], 1)) ?></div><div class="kpi-label">rata-rata skor pretest</div></td>
      <td><div class="kpi-value"><?= esc($num($prePost['posttest'], 1)) ?></div><div class="kpi-label">rata-rata skor posttest</div></td>
      <td><div class="kpi-value"><?= $prePost['delta'] === null ? '—' : esc(($prePost['delta'] >= 0 ? '+' : '') . $num($prePost['delta'], 1)) ?></div><div class="kpi-label">selisih, dari <?= esc($num($prePost['pairs'])) ?> pasangan</div></td>
    </tr>
  </table>
  <?php if ((int) $prePost['incompatible_pairs'] > 0): ?>
    <p class="note small"><?= esc($num($prePost['incompatible_pairs'])) ?> pasangan tidak ikut dihitung karena pretest dan posttest-nya memakai versi rilis yang berbeda.</p>
  <?php endif ?>
<?php endif ?>

<h2>7. Catatan metodologis</h2>
<p><b>Tepat sejak awal</b> (first-pass accuracy) adalah persentase butir yang dijawab benar pada pemeriksaan pertama, sebelum peserta memperbaiki jawabannya. Ukuran ini paling dekat dengan pemahaman awal dan menjadi bobot terbesar skor (70%).</p>
<p><b>Ketepatan akhir</b> adalah persentase butir yang benar setelah perbaikan; <b>kemandirian</b> berkurang oleh penggunaan petunjuk dan pengulangan pemeriksaan. Skor = 70% tepat sejak awal + 20% ketepatan akhir + 10% kemandirian, dihitung server dengan profil skor bernomor versi.</p>
<p><b>p</b> (indeks kesukaran butir) = proporsi jawaban benar di antara peserta yang menjawab butir itu; makin kecil makin sulit. <b>D</b> (daya beda) = p kelompok 27% teratas dikurangi p kelompok 27% terbawah berdasarkan total ketepatan: &lt;0 buruk, &lt;0,20 lemah, 0,20–0,40 cukup, &gt;0,40 baik. Nilai D negatif biasanya menandakan kunci jawaban keliru atau kalimat yang membingungkan.</p>
<p><b>Penguasaan indikator</b> adalah rasio bukti — butir benar sejak awal dibagi butir terjawab — bukan label lulus/tidak lulus. Pustaka Kedu dan audio tidak memengaruhi skor; keduanya dianalisis sebagai perilaku belajar.</p>
<p class="muted small">Sesi berfase "umum" tidak pernah dicampur ke perbandingan pretest–posttest. Perbandingan hanya dilakukan pada sesi dengan versi rilis yang sama.</p>

</body>
</html>
