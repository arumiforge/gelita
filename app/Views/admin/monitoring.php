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
// Counts that need the researcher's attention light up while above zero (admin/monitoring.js keeps it current)
$alerts = ['not_entered', 'dropped', 'no_seat', 'small_screen'];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.menuMonitoring'), 'lead' => lang('Admin.monitoringLead')]) ?>
<?= $this->include('partials/flash') ?>

<div data-monitoring data-endpoint="<?= esc(base_url('api/admin/monitoring'), 'attr') ?>">
  <div class="monitoring-bar">
    <label class="check alert-sound">
      <input type="checkbox" data-alert-sound disabled>
      <span><?= esc(lang('Admin.alertSoundToggle')) ?></span>
    </label>
    <p class="field-help"><?= esc(lang('Admin.alertSoundHelp')) ?></p>
    <p class="visually-hidden" role="status" aria-live="polite" data-alert-live data-text="<?= esc(lang('Admin.alertSoundNew'), 'attr') ?>"></p>
  </div>
  <p class="muted" data-monitoring-status data-ok="<?= esc(lang('Admin.monitoringUpdated'), 'attr') ?>" data-stale="<?= esc(lang('Admin.monitoringStale'), 'attr') ?>" data-updated-at="<?= esc(lang('Admin.monitoringUpdatedAt', ['{time}']), 'attr') ?>"><?= esc(lang('Admin.monitoringUpdated')) ?></p>

  <script type="application/json" data-monitoring-initial><?= json_encode($sessions, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
  <?php if ($sessions === []): ?>
    <p class="muted"><?= esc(lang('Admin.monitoringNone')) ?></p>
  <?php endif ?>

  <?php foreach ($sessions as $row): ?>
    <section class="form-section" data-field-session="<?= (int) $row['id'] ?>">
      <h2><a class="mono" href="<?= base_url('admin/sesi-lapangan/' . (int) $row['id']) ?>"><?= esc($row['code']) ?></a>
        · <?= esc(lang('Admin.fieldSessionState_' . $row['state'])) ?>
        · <?= esc(substr($row['opens_at'], 11, 5)) ?>–<?= esc(substr($row['closes_at'], 11, 5)) ?>
        <?php if ($detail && ! empty($row['token'])): ?> · <?= esc(lang('Admin.fieldSessionToken')) ?> <b class="mono" data-m="token"><?= esc($row['token']) ?></b><?php endif ?>
      </h2>
      <div class="kpi-grid">
        <?php foreach (['entered' => 'monitoringEntered', 'active' => 'monitoringActive', 'not_entered' => 'monitoringNotEntered', 'all_done' => 'monitoringDone', 'dropped' => 'monitoringDropped', 'no_seat' => 'monitoringNoSeat', 'small_screen' => 'monitoringSmallScreen'] as $key => $label): ?>
          <div class="stat-tile<?= in_array($key, $alerts, true) && (int) $row[$key] > 0 ? ' is-alert' : '' ?>"<?= in_array($key, $alerts, true) ? ' data-alert' : '' ?>><span class="stat-label"><?= esc(lang('Admin.' . $label)) ?></span> <b class="stat-value" data-m="<?= $key ?>"><?= esc(fmt_num((int) $row[$key], 0, 'id')) ?></b></div>
        <?php endforeach ?>
      </div>

      <?php if ($detail): ?>
        <?php // Part 4 (30 §5.2) ?>
        <?php if ((int) ($row['consent_missing'] ?? 0) > 0): ?>
          <p class="alert alert-warn" data-m="consent-missing"><?= icon('warn') ?> <?= esc(lang('Admin.consentMissing', [$row['consent_missing']])) ?></p>
        <?php endif ?>
        <?php
          // "Perlu perhatian": the codes behind each amber count (not signed in, lost records, no seat); admin/monitoring.js keeps it current
          $attention = [
              'not-entered' => ['monitoringNotEntered', array_column(array_filter($row['students'], static fn ($s) => ! $s['entered']), 'participant_code')],
              'dropped'     => ['monitoringDropped', array_map(static fn ($s) => $s['participant_code'] . ' (' . fmt_num((int) $s['dropped'], 0, 'id') . ')', array_values(array_filter($row['students'], static fn ($s) => (int) $s['dropped'] > 0)))],
              'no-seat'     => ['monitoringNoSeat', array_column(array_filter($row['students'], static fn ($s) => ! $s['has_seat']), 'participant_code')],
          ];
          $calm = array_filter($attention, static fn ($a) => $a[1] !== []) === [];
        ?>
        <div class="attention<?= $calm ? ' is-calm' : '' ?>" data-attention>
          <h3 class="attention-title"><?= icon('warn', 'attention-warn') ?><?= icon('check', 'attention-ok') ?> <?= esc(lang('Admin.attentionTitle')) ?></h3>
          <p class="attention-none" data-attention-none<?= $calm ? '' : ' hidden' ?>><?= esc(lang('Admin.attentionNone')) ?></p>
          <dl class="attention-list">
            <?php foreach ($attention as $key => [$label, $list]): ?>
              <div data-attention-item="<?= $key ?>"<?= $list === [] ? ' hidden' : '' ?>>
                <dt><?= esc(lang('Admin.' . $label)) ?><?php if ($key !== 'dropped'): ?> <b data-attention-count><?= esc(fmt_num(count($list), 0, 'id')) ?></b><?php endif ?></dt>
                <dd class="mono" data-m="list-<?= $key ?>"><?= esc(implode(', ', $list) ?: '—') ?></dd>
                <?php if ($key === 'dropped'): ?><dd class="field-help"><?= esc(lang('Admin.attentionDroppedHelp')) ?></dd><?php endif ?>
              </div>
            <?php endforeach ?>
          </dl>
        </div>
        <dl class="detail-list">
          <dt><?= esc(lang('Admin.monitoringActive')) ?></dt><dd class="mono" data-m="list-active"><?= esc($codes($row['students'], static fn ($s) => $s['active'])) ?></dd>
          <?php // Part 8 (34 §14): test status and "modul selesai" only (§8.2) ?>
          <?php if (isset($row['students'][0]['test_status'])): ?>
            <dt title="<?= esc(lang('Admin.testStatusHelp'), 'attr') ?>"><?= esc(lang('Admin.testStatus')) ?></dt>
            <dd class="mono" data-m="list-test-status"><?= esc(implode(', ', array_map(static fn ($s) => $s['participant_code'] . ': ' . lang('Admin.testStatus' . ucfirst($s['test_status'])) . ($s['module_done'] ? ' · ' . lang('Admin.moduleDone') : ''), $row['students']))) ?></dd>
          <?php endif ?>
        </dl>
        <div class="table-wrap is-short"><table class="data-table">
          <thead><tr><th><?= esc(lang('Admin.monitoringCode')) ?></th><th><?= esc(lang('Admin.monitoringProgress')) ?></th><th><?= esc(lang('Admin.monitoringLeftovers')) ?></th><th><?= esc(lang('Admin.monitoringDropped')) ?></th></tr></thead>
          <tbody data-m="rows">
            <?php foreach ($row['students'] as $student): ?>
              <tr><td class="mono"><?= esc($student['participant_code']) ?></td><td><?= $student['done'] ?>/<?= $student['total'] ?></td><td><?= $student['leftovers'] ?></td><td><?= $student['dropped'] ?></td></tr>
            <?php endforeach ?>
          </tbody>
        </table></div>
      <?php endif ?>
    </section>
  <?php endforeach ?>
</div>
<?= $this->endSection() ?>
