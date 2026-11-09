<?php
/**
 * Pemantauan — `/admin/pemantauan` → MonitoringController::index (research
 * revision 10, part 2: 28 §12). One card per field session open today or
 * closed less than 24 hours ago. admin/monitoring.js refreshes the numbers
 * and code lists every 30 seconds from GET /api/admin/monitoring.
 *
 * @var list<array<string, mixed>> $sessions AnalyticsService::monitoring()
 * @var bool                       $detail   codes and tokens (researchers, head researcher); admin: numbers (K2)
 */
$codes = static fn (array $students, callable $keep): string => implode(', ', array_column(array_filter($students, $keep), 'participant_code')) ?: '—';
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.menuMonitoring'), 'lead' => lang('Admin.monitoringLead')]) ?>
<?= $this->include('partials/flash') ?>

<div data-monitoring data-endpoint="<?= esc(base_url('api/admin/monitoring'), 'attr') ?>">
  <p class="muted" data-monitoring-status data-ok="<?= esc(lang('Admin.monitoringUpdated'), 'attr') ?>" data-stale="<?= esc(lang('Admin.monitoringStale'), 'attr') ?>"><?= esc(lang('Admin.monitoringUpdated')) ?></p>

  <?php if ($sessions === []): ?>
    <p class="muted"><?= esc(lang('Admin.monitoringNone')) ?></p>
  <?php endif ?>

  <?php foreach ($sessions as $row): ?>
    <section class="form-section" data-field-session="<?= (int) $row['id'] ?>">
      <h2><span class="mono"><?= esc($row['code']) ?></span>
        · <?= esc(lang('Admin.fieldSessionState_' . $row['state'])) ?>
        · <?= esc(substr($row['opens_at'], 11, 5)) ?>–<?= esc(substr($row['closes_at'], 11, 5)) ?>
        <?php if ($detail && ! empty($row['token'])): ?> · <?= esc(lang('Admin.fieldSessionToken')) ?> <b class="mono" data-m="token"><?= esc($row['token']) ?></b><?php endif ?>
      </h2>
      <div class="kpi-grid">
        <?php foreach (['entered' => 'monitoringEntered', 'active' => 'monitoringActive', 'not_entered' => 'monitoringNotEntered', 'all_done' => 'monitoringDone', 'dropped' => 'monitoringDropped', 'no_seat' => 'monitoringNoSeat', 'small_screen' => 'monitoringSmallScreen'] as $key => $label): ?>
          <div class="stat-tile"><span class="stat-label"><?= esc(lang('Admin.' . $label)) ?></span> <b class="stat-value" data-m="<?= $key ?>"><?= esc(fmt_num((int) $row[$key], 0, 'id')) ?></b></div>
        <?php endforeach ?>
      </div>

      <?php if ($detail): ?>
        <?php // Part 4 (30 §5.2) ?>
        <?php if ((int) ($row['consent_missing'] ?? 0) > 0): ?>
          <p class="alert alert-warn" data-m="consent-missing"><?= icon('warn') ?> <?= esc(lang('Admin.consentMissing', [$row['consent_missing']])) ?></p>
        <?php endif ?>
        <dl class="detail-list">
          <dt><?= esc(lang('Admin.monitoringActive')) ?></dt><dd class="mono" data-m="list-active"><?= esc($codes($row['students'], static fn ($s) => $s['active'])) ?></dd>
          <dt><?= esc(lang('Admin.monitoringNotEntered')) ?></dt><dd class="mono" data-m="list-not-entered"><?= esc($codes($row['students'], static fn ($s) => ! $s['entered'])) ?></dd>
          <dt><?= esc(lang('Admin.monitoringNoSeat')) ?></dt><dd class="mono" data-m="list-no-seat"><?= esc($codes($row['students'], static fn ($s) => ! $s['has_seat'])) ?></dd>
          <?php // Part 8 (34 §14): test status and "modul selesai" only (§8.2) ?>
          <?php if (isset($row['students'][0]['test_status'])): ?>
            <dt title="<?= esc(lang('Admin.testStatusHelp'), 'attr') ?>"><?= esc(lang('Admin.testStatus')) ?></dt>
            <dd class="mono" data-m="list-test-status"><?= esc(implode(', ', array_map(static fn ($s) => $s['participant_code'] . ': ' . lang('Admin.testStatus' . ucfirst($s['test_status'])) . ($s['module_done'] ? ' · ' . lang('Admin.moduleDone') : ''), $row['students']))) ?></dd>
          <?php endif ?>
        </dl>
        <table class="data-table">
          <thead><tr><th>Kode peserta</th><th><?= esc(lang('Admin.monitoringProgress')) ?></th><th><?= esc(lang('Admin.monitoringLeftovers')) ?></th><th><?= esc(lang('Admin.monitoringDropped')) ?></th></tr></thead>
          <tbody data-m="rows">
            <?php foreach ($row['students'] as $student): ?>
              <tr><td class="mono"><?= esc($student['participant_code']) ?></td><td><?= $student['done'] ?>/<?= $student['total'] ?></td><td><?= $student['leftovers'] ?></td><td><?= $student['dropped'] ?></td></tr>
            <?php endforeach ?>
          </tbody>
        </table>
      <?php endif ?>
    </section>
  <?php endforeach ?>
</div>
<?= $this->endSection() ?>
