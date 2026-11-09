<?php
/**
 * One child's data copy for the parents (PDF, part 4: 30 §8) —
 * ParticipantCopyService::summaryHtml(). Plain sentences, coded: no name, no
 * school name, no answer keys or item texts.
 *
 * @var array<string, mixed>       $participant code, age, gender, class_level, school_ref, rombel
 * @var list<array<string, mixed>> $regions     region, done, best
 * @var int                        $stars
 * @var int                        $minutes
 * @var bool                       $riskLeftOut the ang-21–ang-23 answers exist and are not included
 * @var list<string>               $instruments test, questionnaire, and attitude-scale parts done (part 8)
 */
$dash = static fn ($value): string => $value === null || $value === '' ? '—' : (string) $value;
?>
<html>
<head>
<meta charset="utf-8">
<?= view('pdf/_style', [], ['saveData' => false]) ?>
</head>
<body>
<div class="eyebrow muted small">GELITA · Salinan data anak</div>
<h1>Salinan data anak <?= esc($participant['participant_code']) ?></h1>

<p>Salinan ini berisi data yang dicatat GELITA dengan kode anak, bukan dengan namanya. Rincian lengkap ada di berkas Excel yang menyertainya.</p>

<h2>Data pendaftaran</h2>
<table class="meta">
  <tr><td class="key">Kode anak</td><td><b><?= esc($participant['participant_code']) ?></b></td></tr>
  <tr><td class="key">Umur</td><td><?= esc($dash($participant['age'])) ?> tahun</td></tr>
  <tr><td class="key">Jenis kelamin</td><td><?= esc($dash($participant['gender'])) ?></td></tr>
  <tr><td class="key">Kelas</td><td><?= esc($dash($participant['class_level'])) ?></td></tr>
  <tr><td class="key">Kode sekolah</td><td><?= esc($dash($participant['school_ref'])) ?></td></tr>
  <tr><td class="key">Rombel</td><td><?= esc($dash($participant['rombel'])) ?></td></tr>
</table>

<h2>Bermain</h2>
<?php if ($regions === []): ?>
  <p>Anak belum menyelesaikan tantangan.</p>
<?php else: ?>
  <?php foreach ($regions as $region): ?>
    <p>Di wilayah <?= esc($region['region']) ?>, anak menyelesaikan <?= esc((string) $region['done']) ?> tantangan.</p>
  <?php endforeach ?>
<?php endif ?>
<p>Jumlah bintang yang dikumpulkan: <?= esc((string) $stars) ?>. Lama bermain seluruhnya sekitar <?= esc((string) $minutes) ?> menit.</p>
<?php // Part 8 (34 §11.4, 30 §8): parts of the tests, the questionnaire, and the attitude scale done ?>
<h2>Tes dan angket</h2>
<?php if (($instruments ?? []) === []): ?>
  <p>Anak belum mengerjakan tes atau angket.</p>
<?php else: ?>
  <?php foreach ($instruments as $line): ?><p><?= esc($line) ?></p><?php endforeach ?>
<?php endif ?>

<h2>Yang tidak disertakan</h2>
<p>Kunci jawaban dan teks soal tidak disertakan, supaya soalnya tetap bisa dipakai. Apakah jawaban anak benar atau belum tetap ada di berkas Excel.</p>
<?php if ($riskLeftOut): ?>
  <p><?= esc(lang('Admin.copyRiskLeftOut')) ?></p>
<?php endif ?>
</body>
</html>
