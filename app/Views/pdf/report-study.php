<?php
/**
 * Laporan penelitian (PDF) — ReportService::studyData().
 *
 * Pembacanya guru dan peneliti: istilah memakai kata sehari-hari (tahap, tes
 * awal/akhir, benar sejak awal, tingkat kesulitan), bukan istilah teknis.
 *
 * Seluruh nilai yang berasal dari database atau input pengguna (nama sekolah,
 * nama penelitian, judul tantangan) lewat esc(): mPDF memproses HTML, jadi
 * HTML yang tidak bersih adalah lubang injeksi. Catatan aktivitas lengkap
 * tidak pernah masuk laporan ini.
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

/** Kunci sebaran (kode tersimpan) → label ramah; kunci kosong dibiarkan agar tampil "(kosong)". */
$relabel = static function (array $groups, callable $label): array {
    $out = [];

    foreach ($groups as $key => $count) {
        $name       = (string) $key === '' ? '' : $label((string) $key);
        $out[$name] = ($out[$name] ?? 0) + $count;
    }

    return $out;
};

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
  <p class="muted">Ringkasan proses dan capaian belajar dari data permainan yang tercatat oleh sistem.</p>

  <table class="meta">
    <tr><td class="key">Sasaran</td><td>Siswa kelas 5 SD/MI</td></tr>
    <tr><td class="key">Penelitian</td><td><?= esc($meta['study']) ?></td></tr>
    <tr><td class="key">Tahap</td><td><?= esc(admin_label('phase', $meta['phase'])) ?></td></tr>
    <tr><td class="key">Rentang tanggal</td><td><?= esc(($meta['date_from'] ?? 'awal') . ' s.d. ' . ($meta['date_to'] ?? 'sekarang')) ?></td></tr>
    <?php if ($meta['school'] !== null): ?><tr><td class="key">Sekolah</td><td><?= esc($meta['school']) ?></td></tr><?php endif ?>
    <?php if ($meta['level'] !== null): ?><tr><td class="key">Wilayah</td><td><?= esc($meta['level']) ?></td></tr><?php endif ?>
    <?php if ($meta['class_level'] !== null): ?><tr><td class="key">Kelas</td><td><?= esc(class_label($meta['class_level'])) ?></td></tr><?php endif ?>
    <?php if ($meta['locale'] !== null): ?><tr><td class="key">Bahasa</td><td><?= esc(admin_label('locale', $meta['locale'])) ?></td></tr><?php endif ?>
    <tr><td class="key">Dibuat oleh</td><td><?= esc($meta['requester']) ?></td></tr>
    <tr><td class="key">Waktu dibuat</td><td><?= esc(fmt_date($meta['generated_at'], false, 'id')) ?></td></tr>
    <tr><td class="key">Nomor berkas</td><td>#<?= (int) $meta['export_id'] ?></td></tr>
  </table>

  <div class="note small">Laporan ini tidak memuat nama siswa, nama pengguna, kata sandi, atau catatan aktivitas lengkap. Data lengkap untuk diolah lebih lanjut tersedia sebagai berkas Excel di menu Unduh data.</div>
</div>

<pagebreak />

<h2>1. Ringkasan siswa</h2>
<table class="kpi">
  <tr>
    <td><div class="kpi-value"><?= esc($num($summary['participant_count'])) ?></div><div class="kpi-label">siswa</div></td>
    <td><div class="kpi-value"><?= esc($num($summary['session_count'])) ?></div><div class="kpi-label">sesi bermain</div></td>
    <td><div class="kpi-value"><?= esc($pct($summary['completion_rate'], true)) ?></div><div class="kpi-label">sesi yang menuntaskan 15 tantangan</div></td>
  </tr>
</table>

<?php if ((int) $cohort['total'] > 0): ?>
  <h3>Jenis kelamin</h3>
  <?= view('pdf/_bars', ['bars' => $distribution($relabel($cohort['by_gender'], static fn (string $code): string => admin_label('gender', $code)), (int) $cohort['total']), 'caption' => 'Sebaran jenis kelamin'], ['saveData' => false]) ?>
  <h3>Kelas</h3>
  <?= view('pdf/_bars', ['bars' => $distribution($relabel($cohort['by_class_level'], static fn (string $class): string => class_label($class)), (int) $cohort['total']), 'caption' => 'Sebaran kelas', 'alt' => true], ['saveData' => false]) ?>
  <h3>Provinsi</h3>
  <?= view('pdf/_bars', ['bars' => $distribution($cohort['by_province'], (int) $cohort['total']), 'caption' => 'Sebaran provinsi'], ['saveData' => false]) ?>
<?php else: ?>
  <p class="muted">Belum ada siswa dalam cakupan ini.</p>
<?php endif ?>

<h2>2. Ringkasan proses</h2>
<table class="kpi">
  <tr>
    <td><div class="kpi-value"><?= esc($num($summary['avg_score'], 1)) ?></div><div class="kpi-label">rata-rata skor tantangan (0–100)</div></td>
    <td><div class="kpi-value"><?= esc($pct($summary['avg_first_pass_accuracy'])) ?></div><div class="kpi-label">rata-rata benar sejak awal</div></td>
    <td><div class="kpi-value"><?= esc(ms_to_human((int) $summary['avg_duration_ms'])) ?></div><div class="kpi-label">rata-rata lama satu sesi</div></td>
  </tr>
  <tr>
    <td><div class="kpi-value"><?= esc($num($summary['hint_usage'], 2)) ?></div><div class="kpi-label">petunjuk dibuka per tantangan</div></td>
    <td><div class="kpi-value"><?= esc($num($summary['audio_usage']['total_plays'])) ?></div><div class="kpi-label">suara diputar oleh siswa (<?= esc($num($summary['audio_usage']['total_replays'])) ?> diulang, <?= esc($num($summary['audio_usage']['total_autoplays'] ?? 0)) ?> otomatis)</div></td>
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
<h3>Benar sejak awal</h3>
<?= view('pdf/_bars', ['bars' => $passBars, 'caption' => 'Benar sejak awal per wilayah', 'alt' => true], ['saveData' => false]) ?>

<h2>4. Kesulitan tantangan</h2>
<p class="muted small">Tingkat kesulitan 0–100 dihitung dari: salah pada jawaban pertama (40%), mengulang (20%), membuka petunjuk (20%), lama mengerjakan (10%), dan tantangan yang ditinggalkan (10%). Makin tinggi, makin sulit.</p>
<table class="data compact">
  <thead>
    <tr><th style="width: 12%">Wilayah</th><th style="width: 25%">Tantangan</th><th class="num" style="width: 13%">Dikerjakan (kali)</th><th class="num" style="width: 10%">Benar sejak awal</th><th class="num" style="width: 10%">Benar di akhir</th><th class="num" style="width: 10%">Petunjuk per kali</th><th class="num" style="width: 10%">Lama (nilai tengah)</th><th class="num" style="width: 10%">Tingkat kesulitan</th></tr>
  </thead>
  <tbody>
    <?php foreach ($nodes as $node): ?>
      <tr>
        <td><?= esc($levelNames[$node['level_id']] ?? '') ?></td>
        <td><?= (int) $node['sequence'] ?>. <?= esc($node['title']) ?><br><span class="muted small"><?= esc(engine_label((string) $node['engine_type'])) ?></span></td>
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

<h2>5. Capaian indikator</h2>
<?php if ($indicators === []): ?>
  <p class="muted">Belum ada soal yang dijawab dalam cakupan ini.</p>
<?php else: ?>
  <?php
  $indicatorBars = [];

  foreach ($indicators as $indicator) {
      $indicatorBars[] = [
          'label' => $indicator['name'],
          'value' => $indicator['mastery_ratio'] * 100,
          'text'  => $pct($indicator['mastery_ratio'], true),
          'sub'   => $num($indicator['correct_count']) . ' dari ' . $num($indicator['evidence_count']) . ' jawaban benar sejak awal',
      ];
  }
  ?>
  <?= view('pdf/_bars', ['bars' => $indicatorBars, 'caption' => 'Capaian per indikator'], ['saveData' => false]) ?>
<?php endif ?>

<?php if ($security['pillars'] !== []): ?>
  <h3>Pilar literasi digital</h3>
  <?php
  $pillarBars = [];

  foreach ($security['pillars'] as $pillar => $stats) {
      $pillarBars[] = ['label' => admin_label('pillar', (string) $pillar), 'value' => $stats['accuracy'] * 100, 'text' => $pct($stats['accuracy'], true), 'sub' => $num($stats['appeared']) . ' jawaban'];
  }
  ?>
  <?= view('pdf/_bars', ['bars' => $pillarBars, 'caption' => 'Jawaban benar per pilar literasi digital', 'alt' => true], ['saveData' => false]) ?>
<?php endif ?>

<h2>6. Tes awal dan tes akhir</h2>
<?php if ((int) $prePost['pairs'] === 0 && (int) $prePost['incompatible_pairs'] === 0): ?>
  <p class="muted">Belum ada siswa yang menuntaskan tes awal dan tes akhir dalam cakupan ini.</p>
<?php else: ?>
  <table class="kpi">
    <tr>
      <td><div class="kpi-value"><?= esc($num($prePost['pretest'], 1)) ?></div><div class="kpi-label">rata-rata skor tes awal</div></td>
      <td><div class="kpi-value"><?= esc($num($prePost['posttest'], 1)) ?></div><div class="kpi-label">rata-rata skor tes akhir</div></td>
      <td><div class="kpi-value"><?= $prePost['delta'] === null ? '—' : esc(($prePost['delta'] >= 0 ? '+' : '') . $num($prePost['delta'], 1)) ?></div><div class="kpi-label">selisih, dari <?= esc($num($prePost['pairs'])) ?> siswa yang mengikuti kedua tes</div></td>
    </tr>
  </table>
  <?php if ((int) $prePost['incompatible_pairs'] > 0): ?>
    <p class="note small"><?= esc($num($prePost['incompatible_pairs'])) ?> siswa tidak ikut dihitung karena tes awal dan tes akhirnya memakai versi permainan yang berbeda.</p>
  <?php endif ?>
<?php endif ?>

<h2>7. Cara membaca angka</h2>
<p><b>Benar sejak awal</b> adalah persentase soal yang langsung benar saat siswa pertama kali menekan Periksa, sebelum memperbaiki jawabannya. Angka ini paling dekat dengan pemahaman awal siswa, jadi bobotnya paling besar dalam skor (70%).</p>
<p><b>Benar di akhir</b> adalah persentase soal yang benar setelah diperbaiki. <b>Kemandirian</b> berkurang setiap kali siswa membuka petunjuk atau mengulang. Skor tantangan = 70% benar sejak awal + 20% benar di akhir + 10% kemandirian, dihitung otomatis oleh sistem.</p>
<p><b>Capaian indikator</b> adalah persentase jawaban yang benar sejak awal untuk soal-soal indikator itu. Angka ini bukan nilai lulus/tidak lulus. Membaca Pustaka Kedu dan memutar suara tidak memengaruhi skor; keduanya dicatat sebagai kebiasaan belajar.</p>
<p class="muted small">Sesi bermain biasa tidak pernah dicampur ke perbandingan tes awal dan tes akhir. Perbandingan hanya dilakukan bila kedua tes memakai versi permainan yang sama.</p>

</body>
</html>
