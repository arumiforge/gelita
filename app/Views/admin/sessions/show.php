<?php
/**
 * Detail sesi — `/admin/sesi/{id}` → SessionController::show
 *
 * Ringkasan sesi + tabel attempt per node. Setiap baris dapat dibuka ke daftar
 * jawaban per butir (item_responses). Jalur drilldown terdalam berlanjut ke
 * linimasa event.
 *
 * @var App\Entities\GameSession                 $session
 * @var array<string, mixed>|null                $participant bentuk aman
 * @var array<string, mixed>|null                $progress
 * @var string|null                              $phaseCode
 * @var list<App\Entities\ChallengeAttempt>      $attempts
 * @var array<int, list<array<string, mixed>>>   $responses
 * @var array<int, string>                       $nodes
 * @var array<int, string>                       $engines
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Detail sesi',
    'eyebrow' => 'Sesi ' . substr((string) $session->session_code, 0, 12),
    'actions' => '<a class="btn btn-primary btn-sm" href="' . base_url('admin/sesi/' . $session->id . '/event') . '">' . icon('list') . ' Linimasa event</a>'
        . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi') . '">' . icon('left') . ' Daftar sesi</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <dl class="detail-grid">
    <div><dt>Peserta</dt><dd>
      <?php if ($participant !== null): ?>
        <a href="<?= base_url('admin/peserta/' . $participant['id']) ?>"><code><?= esc($participant['participant_code']) ?></code></a>
      <?php else: ?>—<?php endif ?>
    </dd></div>
    <div><dt>Fase</dt><dd><?= esc($phaseCode ?? '—') ?></dd></div>
    <div><dt>Status</dt><dd><span class="badge is-<?= esc($session->status, 'attr') ?>"><?= esc($session->status) ?></span></dd></div>
    <div><dt>Bahasa</dt><dd><?= esc($session->locale) ?></dd></div>
    <div><dt>Mulai</dt><dd><?= esc(fmt_date($session->started_at, true, 'id')) ?></dd></div>
    <div><dt>Selesai</dt><dd><?= esc(fmt_date($session->ended_at, true, 'id')) ?></dd></div>
    <div><dt>Durasi</dt><dd class="num"><?= esc(ms_to_human((int) $session->duration_ms)) ?></dd></div>
    <div><dt>Serpihan</dt><dd class="num"><?= esc($progress['completed_nodes'] ?? 0) ?></dd></div>
    <div><dt>Total skor</dt><dd class="num"><?= esc(fmt_num($progress['total_score'] ?? 0, 1, 'id')) ?></dd></div>
    <div><dt>Perangkat</dt><dd><?= esc(trim(implode(' · ', array_filter([$session->device_type, $session->os_name, $session->browser_name, $session->screen_size]))) ?: '—') ?></dd></div>
  </dl>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('puzzle') ?> Percobaan per tantangan</h2>
  <?php if ($attempts === []): ?>
    <div class="empty-state"><?= icon('info') ?><p>Belum ada percobaan pada sesi ini. Percobaan tercatat saat siswa membuka layar tantangan.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <caption class="visually-hidden">Percobaan per tantangan</caption>
        <thead>
          <tr>
            <th scope="col">Tantangan</th><th scope="col">Jenis</th><th scope="col">#</th><th scope="col">Status</th>
            <th scope="col" class="is-num">Butir</th><th scope="col" class="is-num">Tepat awal</th><th scope="col" class="is-num">Akhir</th>
            <th scope="col" class="is-num">Periksa</th><th scope="col" class="is-num">Petunjuk</th><th scope="col" class="is-num">Ubah</th>
            <th scope="col" class="is-num">Durasi</th><th scope="col" class="is-num">Skor</th><th scope="col">Bintang</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <tr>
              <td>
                <?= esc($nodes[$attempt->challenge_node_id] ?? ('#' . $attempt->challenge_node_id)) ?>
                <?php if (! empty($responses[$attempt->id])): ?>
                  <details class="row-details">
                    <summary>Jawaban per butir (<?= count($responses[$attempt->id]) ?>)</summary>
                    <?= component('admin-table', [
                        'rows'    => $responses[$attempt->id],
                        'columns' => [
                            'display_order'      => ['label' => '#', 'format' => 'num'],
                            'item_key'           => ['label' => 'Butir', 'format' => 'code'],
                            'status'             => ['label' => 'Status', 'format' => 'badge'],
                            'first_pass_correct' => ['label' => 'Tepat awal', 'render' => static fn (array $r): string => $r['first_pass_correct'] === null ? '—' : ((int) $r['first_pass_correct'] === 1 ? '✓ ya' : '✗ tidak')],
                            'is_correct'         => ['label' => 'Akhir', 'render' => static fn (array $r): string => $r['is_correct'] === null ? '—' : ((int) $r['is_correct'] === 1 ? '✓ benar' : '✗ salah')],
                            'change_count'       => ['label' => 'Ubah', 'format' => 'num'],
                            'wrong_click_count'  => ['label' => 'Salah klik', 'format' => 'num'],
                            'duration_ms'        => ['label' => 'Durasi', 'format' => 'ms'],
                            'final_answer_json'  => ['label' => 'Jawaban akhir', 'format' => 'json'],
                            'reason_text'        => 'Alasan',
                        ],
                    ]) ?>
                  </details>
                <?php endif ?>
              </td>
              <td><?= esc($engines[$attempt->challenge_node_id] ?? '—') ?></td>
              <td class="num"><?= esc($attempt->attempt_no) ?></td>
              <td><span class="badge is-<?= esc($attempt->status, 'attr') ?>"><?= esc($attempt->status) ?></span></td>
              <td class="is-num"><?= esc($attempt->scorable_items) ?></td>
              <td class="is-num"><?= esc(fmt_pct($attempt->first_pass_accuracy, false, 0)) ?></td>
              <td class="is-num"><?= esc(fmt_pct($attempt->final_accuracy, false, 0)) ?></td>
              <td class="is-num"><?= esc($attempt->check_count) ?></td>
              <td class="is-num"><?= esc($attempt->hint_count) ?></td>
              <td class="is-num"><?= esc($attempt->answer_change_count) ?></td>
              <td class="is-num"><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></td>
              <td class="is-num"><?= $attempt->isCompleted() ? esc(fmt_num($attempt->score, 1, 'id')) : '—' ?></td>
              <td><?= $attempt->isCompleted() ? stars_html((int) $attempt->stars) : '—' ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
