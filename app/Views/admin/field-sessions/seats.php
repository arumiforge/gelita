<?php
/**
 * Kursi dan buklet (L2) — `/admin/sesi-lapangan/{id}/kursi` (28 §11). The
 * researcher types row and seat; booklet and test forms are computed.
 *
 * @var array<string, mixed>       $fieldSession
 * @var list<array<string, mixed>> $roster  id, participant_code, seat
 * @var array<string, mixed>       $summary counts, balanced, all_have_form, missing
 */
use App\Libraries\SeatRules;

// The seat plan as seen from the board: rows from the front, seats from the left (02 → L2)
$plan = [];
foreach ($roster as $student) {
    if ($student['seat'] !== null) {
        $plan[(int) $student['seat']['row_no']][(int) $student['seat']['seat_no']][] = $student;
    }
}
$planRows  = $plan === [] ? 0 : max(array_keys($plan));
$planSeats = $plan === [] ? 0 : max(array_map(static fn (array $r): int => max(array_keys($r)), $plan));
$unseated  = array_column(array_filter($roster, static fn (array $s): bool => $s['seat'] === null), 'participant_code');
$shared    = [];
foreach ($plan as $r => $row) {
    foreach ($row as $n => $cell) {
        if (count($cell) > 1) {
            $shared[] = implode(' & ', array_column($cell, 'participant_code'));
        }
    }
}
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.fieldSessionSeats') . ' · ' . $fieldSession['code'], 'lead' => lang('Admin.seatsLead')]) ?>
<?= $this->include('partials/flash') ?>

<div class="kpi-grid">
  <?php foreach ($summary['counts'] as $booklet => $count): ?>
    <?= component('stat-tile', ['label' => $booklet, 'value' => fmt_num($count, 0, 'id'), 'icon' => 'clipboard']) ?>
  <?php endforeach ?>
</div>
<?php if (! $summary['balanced']): ?>
  <div class="alert alert-warn" role="status"><?= icon('warn') ?><p><?= esc(lang('Admin.seatBalanceWarn')) ?></p></div>
<?php endif ?>
<?php if ($summary['all_have_form']): ?>
  <p class="alert alert-ok"><?= icon('check') ?> <?= esc(lang('Admin.seatAllHaveForm')) ?></p>
<?php endif ?>

<?php if ($roster !== []): ?>
  <section class="panel seat-plan" data-seat-plan aria-labelledby="seat-plan-title"
           data-label-row="<?= esc(lang('Admin.seatRow'), 'attr') ?>" data-label-seat="<?= esc(lang('Admin.seatNo'), 'attr') ?>"
           data-label-clash="<?= esc(lang('Admin.seatGridClash', ['{codes}']), 'attr') ?>">
    <h2 class="panel-title" id="seat-plan-title"><?= icon('grid') ?> <?= esc(lang('Admin.seatGridTitle')) ?></h2>
    <p class="field-help"><?= esc(lang('Admin.seatGridHelp')) ?></p>
    <div class="seat-plan-scroll">
      <div class="seat-board"><?= esc(lang('Admin.seatGridBoard')) ?></div>
      <div class="seat-grid" data-seat-grid style="--seats: <?= max(1, $planSeats) ?>"<?= $planRows === 0 ? ' hidden' : '' ?>>
        <span class="seat-axis" aria-hidden="true"></span>
        <?php for ($n = 1; $n <= $planSeats; $n++): ?><span class="seat-axis"><?= esc(lang('Admin.seatNo')) ?> <?= $n ?></span><?php endfor ?>
        <?php for ($r = 1; $r <= $planRows; $r++): ?>
          <span class="seat-axis is-row"><?= esc(lang('Admin.seatRow')) ?> <?= $r ?></span>
          <?php for ($n = 1; $n <= $planSeats; $n++): ?>
            <?php $cell = $plan[$r][$n] ?? []; $booklet = $cell === [] ? SeatRules::booklet($r, $n) : (string) $cell[0]['seat']['booklet']; ?>
            <div class="seat-cell is-<?= strtolower($booklet) ?><?= $cell === [] ? ' is-empty' : '' ?><?= count($cell) > 1 ? ' is-shared' : '' ?>">
              <b class="seat-cell-booklet"><?= esc($booklet) ?></b>
              <?php foreach ($cell as $student): ?><span class="mono"><?= esc($student['participant_code']) ?></span><?php endforeach ?>
            </div>
          <?php endfor ?>
        <?php endfor ?>
      </div>
      <p class="muted seat-plan-empty" data-seat-empty<?= $planRows === 0 ? '' : ' hidden' ?>><?= esc(lang('Admin.seatGridEmpty')) ?></p>
    </div>
    <p class="alert alert-warn seat-plan-clash" data-seat-clash<?= $shared === [] ? ' hidden' : '' ?>><?= icon('warn') ?> <span><?= esc(lang('Admin.seatGridClash', [implode(', ', $shared)])) ?></span></p>
    <p class="seat-unseated" data-seat-unseated<?= $unseated === [] ? ' hidden' : '' ?>><b><?= esc(lang('Admin.seatGridNoSeat')) ?>:</b> <span class="mono"><?= esc(implode(', ', $unseated)) ?></span></p>
    <p class="seat-legend"><span><?= esc(lang('Admin.seatGridLegend')) ?>:</span>
      <?php foreach (SeatRules::BOOKLETS as $b): ?><span class="seat-chip is-<?= strtolower($b) ?>"><?= $b ?></span><?php endforeach ?>
    </p>
  </section>
<?php endif ?>

<?php if ($roster === []): ?>
  <p class="muted"><?= esc(lang('Admin.seatsNone')) ?></p>
<?php else: ?>
  <form method="post" action="<?= base_url('admin/sesi-lapangan/' . $fieldSession['id'] . '/kursi') ?>" class="form">
    <?= csrf_field() ?>
    <div class="table-wrap"><table class="data-table">
      <thead><tr>
        <th>Kode peserta</th><th><?= esc(lang('Admin.seatRow')) ?></th><th><?= esc(lang('Admin.seatNo')) ?></th>
        <th><?= esc(lang('Admin.seatBooklet')) ?></th><th><?= esc(lang('Admin.seatPreForm')) ?></th><th><?= esc(lang('Admin.seatPostForm')) ?></th>
      </tr></thead>
      <tbody>
        <?php foreach ($roster as $student): ?>
          <?php $seat = $student['seat']; $locked = $seat !== null && $seat['locked_at'] !== null; ?>
          <tr data-seat-row data-code="<?= esc($student['participant_code'], 'attr') ?>"<?= $locked ? ' data-booklet="' . esc((string) $seat['booklet'], 'attr') . '"' : '' ?>>
            <td class="mono"><?= esc($student['participant_code']) ?></td>
            <td><input type="number" min="1" max="<?= \App\Libraries\SeatRules::MAX_NO ?>" name="seats[<?= (int) $student['id'] ?>][row]" data-seat-input="row" value="<?= esc((string) ($seat['row_no'] ?? ''), 'attr') ?>" <?= $locked ? 'readonly' : '' ?> aria-label="<?= esc(lang('Admin.seatRow') . ' ' . $student['participant_code'], 'attr') ?>"></td>
            <td><input type="number" min="1" max="<?= \App\Libraries\SeatRules::MAX_NO ?>" name="seats[<?= (int) $student['id'] ?>][seat]" data-seat-input="seat" value="<?= esc((string) ($seat['seat_no'] ?? ''), 'attr') ?>" <?= $locked ? 'readonly' : '' ?> aria-label="<?= esc(lang('Admin.seatNo') . ' ' . $student['participant_code'], 'attr') ?>"></td>
            <td><?= esc((string) ($seat['booklet'] ?? '—')) ?></td>
            <td><?= esc((string) ($seat['pre_form'] ?? '—')) ?></td>
            <td><?= esc((string) ($seat['post_form'] ?? '—')) ?><?= $locked ? ' · ' . esc(lang('Admin.seatLocked')) : '' ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table></div>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.save')) ?></button></div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
