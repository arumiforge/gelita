<?php
/**
 * Kursi dan buklet (L2) — `/admin/sesi-lapangan/{id}/kursi` (28 §11). The
 * researcher types row and seat; booklet and test forms are computed.
 *
 * @var array<string, mixed>       $fieldSession
 * @var list<array<string, mixed>> $roster  id, participant_code, seat
 * @var array<string, mixed>       $summary counts, balanced, all_have_form, missing
 */
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

<?php if ($roster === []): ?>
  <p class="muted"><?= esc(lang('Admin.seatsNone')) ?></p>
<?php else: ?>
  <form method="post" action="<?= base_url('admin/sesi-lapangan/' . $fieldSession['id'] . '/kursi') ?>" class="form">
    <?= csrf_field() ?>
    <table class="data-table">
      <thead><tr>
        <th>Kode peserta</th><th><?= esc(lang('Admin.seatRow')) ?></th><th><?= esc(lang('Admin.seatNo')) ?></th>
        <th><?= esc(lang('Admin.seatBooklet')) ?></th><th><?= esc(lang('Admin.seatPreForm')) ?></th><th><?= esc(lang('Admin.seatPostForm')) ?></th>
      </tr></thead>
      <tbody>
        <?php foreach ($roster as $student): ?>
          <?php $seat = $student['seat']; $locked = $seat !== null && $seat['locked_at'] !== null; ?>
          <tr>
            <td class="mono"><?= esc($student['participant_code']) ?></td>
            <td><input type="number" min="1" max="<?= \App\Libraries\SeatRules::MAX_NO ?>" name="seats[<?= (int) $student['id'] ?>][row]" value="<?= esc((string) ($seat['row_no'] ?? ''), 'attr') ?>" <?= $locked ? 'readonly' : '' ?> aria-label="<?= esc(lang('Admin.seatRow') . ' ' . $student['participant_code'], 'attr') ?>"></td>
            <td><input type="number" min="1" max="<?= \App\Libraries\SeatRules::MAX_NO ?>" name="seats[<?= (int) $student['id'] ?>][seat]" value="<?= esc((string) ($seat['seat_no'] ?? ''), 'attr') ?>" <?= $locked ? 'readonly' : '' ?> aria-label="<?= esc(lang('Admin.seatNo') . ' ' . $student['participant_code'], 'attr') ?>"></td>
            <td><?= esc((string) ($seat['booklet'] ?? '—')) ?></td>
            <td><?= esc((string) ($seat['pre_form'] ?? '—')) ?></td>
            <td><?= esc((string) ($seat['post_form'] ?? '—')) ?><?= $locked ? ' · ' . esc(lang('Admin.seatLocked')) : '' ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <div class="form-actions"><button class="btn btn-primary" type="submit"><?= icon('check') ?> <?= esc(lang('Admin.save')) ?></button></div>
  </form>
<?php endif ?>
<?= $this->endSection() ?>
