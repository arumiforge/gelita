<?php
/**
 * Detail sesi — `/admin/sesi/{id}` → SessionController::show
 *
 * Ringkasan sesi + tabel pengerjaan per node (data mentah, tanpa skor).
 * Setiap baris dapat dibuka ke jawaban per soal (item_responses) dan setiap
 * percobaan (item_tries). Perangkat dari device_logs. Jalur drilldown
 * terdalam berlanjut ke linimasa event.
 *
 * @var App\Entities\GameSession                 $session
 * @var array<string, mixed>|null                $participant bentuk aman
 * @var array<string, mixed>|null                $progress
 * @var list<App\Entities\ChallengeAttempt>      $attempts
 * @var array<int, list<array<string, mixed>>>   $responses
 * @var array<int, list<array<string, mixed>>>   $tries
 * @var list<array<string, mixed>>               $devices
 * @var array<int, string>                       $nodes
 * @var array<int, string>                       $engines
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Detail sesi',
    'eyebrow' => 'Sesi ' . substr((string) $session->session_code, 0, 12),
    'actions' => '<a class="btn btn-primary btn-sm" href="' . base_url('admin/sesi/' . $session->id . '/event') . '">' . icon('list') . ' Catatan aktivitas</a>'
        . '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/sesi') . '">' . icon('left') . ' Daftar sesi</a>',
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <dl class="detail-grid">
    <div><dt>Siswa</dt><dd>
      <?php if ($participant !== null): ?>
        <a href="<?= base_url('admin/peserta/' . $participant['id']) ?>"><code><?= esc($participant['participant_code']) ?></code></a>
      <?php else: ?>—<?php endif ?>
    </dd></div>
    <div><dt>Status</dt><dd><span class="badge is-<?= esc($session->status, 'attr') ?>"><?= esc(admin_label('status', (string) $session->status)) ?></span></dd></div>
    <div><dt>Bahasa</dt><dd><?= esc(admin_label('locale', (string) $session->locale)) ?></dd></div>
    <div><dt>Mulai</dt><dd><?= esc(fmt_date($session->started_at, true, 'id')) ?></dd></div>
    <div><dt>Selesai</dt><dd><?= esc(fmt_date($session->ended_at, true, 'id')) ?></dd></div>
    <div><dt>Lama bermain</dt><dd class="num"><?= esc(ms_to_human((int) $session->duration_ms)) ?></dd></div>
    <div><dt>Tantangan selesai</dt><dd class="num"><?= esc($progress['completed_nodes'] ?? 0) ?></dd></div>
    <div><dt>Total bintang</dt><dd class="num"><?= esc($progress['total_stars'] ?? 0) ?></dd></div>
    <div><dt>Perangkat</dt><dd><?= esc(trim(implode(' · ', array_filter([(string) $session->device_type === '' ? null : admin_label('device', (string) $session->device_type), $session->os_name, $session->browser_name, $session->screen_size]))) ?: '—') ?></dd></div>
  </dl>
</section>

<?php if ($devices !== []): ?>
<section class="panel">
  <h2 class="panel-title"><?= icon('grid') ?> Perangkat (masuk/daftar)</h2>
  <?= component('admin-table', [
      'rows'    => $devices,
      'columns' => [
          'created_at'   => ['label' => 'Waktu', 'format' => 'datetime'],
          'via'          => 'Lewat',
          'device_type'  => 'Jenis',
          'device_brand' => 'Merek',
          'device_model' => 'Model',
          'os_name'      => 'Sistem operasi',
          'os_version'   => 'Versi OS',
          'browser_name' => 'Peramban',
          'browser_version' => 'Versi peramban',
          'screen_w'     => ['label' => 'Layar', 'render' => static fn (array $r): string => $r['screen_w'] === null ? '—' : $r['screen_w'] . ' × ' . $r['screen_h']],
          'viewport_w'   => ['label' => 'Area tampil', 'render' => static fn (array $r): string => $r['viewport_w'] === null ? '—' : $r['viewport_w'] . ' × ' . $r['viewport_h']],
          'touch_points' => ['label' => 'Titik sentuh', 'format' => 'num'],
          'corrected'    => 'Koreksi',
      ],
  ]) ?>
</section>
<?php endif ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('puzzle') ?> Hasil tiap tantangan</h2>
  <?php if ($attempts === []): ?>
    <div class="empty-state"><?= icon('info') ?><p>Belum ada tantangan yang dikerjakan pada sesi ini. Hasil tercatat begitu siswa membuka layar tantangan.</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="data-table">
        <caption class="visually-hidden">Hasil tiap tantangan</caption>
        <thead>
          <tr>
            <th scope="col">Tantangan</th><th scope="col">Jenis</th><th scope="col">Pengerjaan ke-</th><th scope="col">Variasi</th><th scope="col">Status</th>
            <th scope="col">Alasan selesai</th><th scope="col" class="is-num">Soal dinilai</th><th scope="col" class="is-num">Berhasil pada Percobaan Pertama</th><th scope="col" class="is-num">Benar di akhir</th>
            <th scope="col" class="is-num">Menekan Cek jawabanku</th><th scope="col" class="is-num">Petunjuk</th><th scope="col" class="is-num">Ubah jawaban</th>
            <th scope="col" class="is-num">Lama</th><th scope="col">Bintang</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($attempts as $attempt): ?>
            <tr>
              <td>
                <?= esc($nodes[$attempt->challenge_node_id] ?? ('#' . $attempt->challenge_node_id)) ?>
                <?php if (! empty($responses[$attempt->id])): ?>
                  <details class="row-details">
                    <summary>Jawaban tiap soal (<?= count($responses[$attempt->id]) ?>)</summary>
                    <?= component('admin-table', [
                        'rows'    => $responses[$attempt->id],
                        'columns' => [
                            'display_order'      => ['label' => 'No.', 'format' => 'num'],
                            'item_key'           => ['label' => 'Kode soal', 'format' => 'code'],
                            'status'             => ['label' => 'Status', 'format' => 'badge'],
                            'first_pass_correct' => ['label' => 'Berhasil pada Percobaan Pertama', 'render' => static fn (array $r): string => $r['first_pass_correct'] === null ? '—' : ((int) $r['first_pass_correct'] === 1 ? '✓ ya' : '✗ tidak')],
                            'is_correct'         => ['label' => 'Benar di akhir', 'render' => static fn (array $r): string => $r['is_correct'] === null ? '—' : ((int) $r['is_correct'] === 1 ? '✓ benar' : '✗ salah')],
                            'try_count'          => ['label' => 'Jumlah percobaan', 'format' => 'num'],
                            'solved_on_try'      => ['label' => 'Benar pada percobaan ke-', 'format' => 'num'],
                            'change_count'       => ['label' => 'Ubah jawaban', 'format' => 'num'],
                            'wrong_click_count'  => ['label' => 'Salah pilih', 'format' => 'num'],
                            'duration_ms'        => ['label' => 'Lama', 'format' => 'ms'],
                            'final_answer_json'  => ['label' => 'Jawaban terakhir', 'format' => 'json'],
                        ],
                    ]) ?>
                  </details>
                <?php endif ?>
                <?php if (! empty($tries[$attempt->id])): ?>
                  <details class="row-details">
                    <summary>Setiap percobaan (<?= count($tries[$attempt->id]) ?>)</summary>
                    <?= component('admin-table', [
                        'rows'    => $tries[$attempt->id],
                        'columns' => [
                            'item_key'           => ['label' => 'Kode soal', 'format' => 'code'],
                            'try_no'             => ['label' => 'Percobaan ke-', 'format' => 'num'],
                            'check_no'           => ['label' => 'Cek ke-', 'format' => 'num'],
                            'answer_json'        => ['label' => 'Jawaban', 'format' => 'json'],
                            'is_correct'         => ['label' => 'Benar', 'render' => static fn (array $r): string => (int) $r['is_correct'] === 1 ? '✓' : '✗'],
                            'hint_opened_before' => ['label' => 'Petunjuk sudah dibuka', 'render' => static fn (array $r): string => (int) $r['hint_opened_before'] === 1 ? 'ya' : 'tidak'],
                            'first_input_ms'     => ['label' => 'Isian pertama', 'format' => 'ms'],
                            'last_input_ms'      => ['label' => 'Isian terakhir', 'format' => 'ms'],
                            'occurred_at'        => ['label' => 'Waktu server', 'format' => 'datetime'],
                        ],
                    ]) ?>
                  </details>
                <?php endif ?>
              </td>
              <td><?= esc(isset($engines[$attempt->challenge_node_id]) ? engine_label($engines[$attempt->challenge_node_id]) : '—') ?></td>
              <td class="num"><?= esc($attempt->attempt_no) ?></td>
              <td><code><?= esc($attempt->variant_group ?? '—') ?></code></td>
              <td><span class="badge is-<?= esc($attempt->status, 'attr') ?>"><?= esc(admin_label('status', (string) $attempt->status)) ?></span></td>
              <td><?= esc(admin_label('completion', (string) ($attempt->completion_reason ?? ''))) ?></td>
              <td class="is-num"><?= esc($attempt->scorable_items) ?></td>
              <td class="is-num"><?= esc($attempt->first_pass_correct) ?></td>
              <td class="is-num"><?= esc($attempt->final_correct) ?></td>
              <td class="is-num"><?= esc($attempt->check_count) ?></td>
              <td class="is-num"><?= esc($attempt->hint_count) ?></td>
              <td class="is-num"><?= esc($attempt->answer_change_count) ?></td>
              <td class="is-num"><?= esc(ms_to_human((int) $attempt->duration_ms)) ?></td>
              <td><?= $attempt->isCompleted() ? stars_html((int) $attempt->stars) : '—' ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>
</section>
<?= $this->endSection() ?>
