<?php
/**
 * Kritik & saran — `/admin/masukan` → FeedbackController::index
 *
 * Ringkasan sebaran bintang + kartu masukan (bintang, empat jawaban, kode
 * peserta, waktu). Nama pengguna siswa tidak ditampilkan di sini.
 *
 * @var list<array<string, mixed>>   $rows
 * @var CodeIgniter\Pager\Pager|null  $pager
 * @var array<string, mixed>          $distribution
 * @var array<string, mixed>          $filters
 */
$questions = [
    'liked_most'   => 'Yang paling disukai',
    'hardest_part' => 'Yang paling sulit',
    'new_learning' => 'Hal baru yang dipelajari',
    'suggestion'   => 'Saran',
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Kritik & saran',
    'eyebrow' => 'Laporan',
    'lead'    => 'Pendapat siswa yang ditulis di Balai Refleksi setelah menyelesaikan semua tantangan.',
]) ?>
<?= $this->include('partials/flash') ?>
<?= component('admin-filter-bar', ['filters' => $filters, 'only' => ['school_id', 'class_level']]) ?>

<div class="split-grid">
  <section class="panel">
    <h2 class="panel-title"><?= icon('star') ?> Sebaran bintang</h2>
    <table class="data-table">
      <caption class="visually-hidden">Jumlah masukan per bintang</caption>
      <thead><tr><th scope="col">Bintang</th><th scope="col" class="is-num">Jumlah masukan</th></tr></thead>
      <tbody>
        <?php foreach (array_reverse($distribution['distribution'], true) as $stars => $total): ?>
          <tr><td><?= esc(str_repeat('★', (int) $stars)) ?></td><td class="is-num"><?= esc((string) $total) ?></td></tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </section>
  <section class="panel">
    <h2 class="panel-title"><?= icon('message') ?> Ringkasan</h2>
    <div class="stat-grid">
      <?= component('stat-tile', ['label' => 'Jumlah masukan', 'value' => fmt_num($distribution['count'], 0, 'id')]) ?>
      <?= component('stat-tile', ['label' => 'Rata-rata bintang', 'value' => $distribution['count'] > 0 ? fmt_num($distribution['mean'], 2, 'id') . ' / 5' : '—']) ?>
    </div>
  </section>
</div>

<?php if ($rows === []): ?>
  <div class="empty-state"><?= icon('message') ?>
    <p>Belum ada masukan. Siswa dapat mengirim kritik & saran setelah menyelesaikan seluruh tantangan di Balai Refleksi.</p>
  </div>
<?php else: ?>
  <div class="feedback-grid">
    <?php foreach ($rows as $row): ?>
      <article class="panel feedback-card">
        <header class="feedback-card-head">
          <?= stars_html((int) $row['rating'], 5) ?>
          <span class="muted"><code><?= esc($row['participant_code'] ?? '#' . $row['participant_id']) ?></code>
            · <?= esc(fmt_date($row['submitted_at'], false, 'id')) ?></span>
        </header>
        <dl>
          <?php foreach ($questions as $field => $label): ?>
            <?php if (trim((string) ($row[$field] ?? '')) !== ''): ?>
              <div><dt><?= esc($label) ?></dt><dd><?= nl2br(esc($row[$field])) ?></dd></div>
            <?php endif ?>
          <?php endforeach ?>
        </dl>
      </article>
    <?php endforeach ?>
  </div>
  <?= component('admin-pagination', ['pager' => $pager]) ?>
<?php endif ?>
<?= $this->endSection() ?>
