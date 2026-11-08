<?php
/**
 * Tindak lanjut — `/admin/tindak-lanjut` (30 §6). Code, priority, and the
 * time of the last answered item only; never the answers.
 *
 * @var list<array{field_session: string, rows: list<array<string, mixed>>}> $groups
 * @var bool $closed
 */
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', ['title' => lang('Admin.menuRiskFollowUp'), 'lead' => lang('Admin.riskIntro')]) ?>

<?php if ($closed): ?>
  <p class="muted"><?= esc(lang('Admin.riskStudyClosed')) ?></p>
<?php elseif ($groups === []): ?>
  <p class="muted"><?= esc(lang('Admin.riskNone')) ?></p>
<?php else: ?>
  <?php foreach ($groups as $group): ?>
    <section class="form-section">
      <h2 class="mono"><?= esc($group['field_session']) ?></h2>
      <table class="data-table">
        <thead><tr><th>Kode peserta</th><th><?= esc(lang('Admin.riskPriority')) ?></th><th><?= esc(lang('Admin.riskLastAnswered')) ?></th></tr></thead>
        <tbody>
          <?php foreach ($group['rows'] as $row): ?>
            <tr><td class="mono"><?= esc($row['participant_code']) ?></td><td><?= $row['priority'] ? esc(lang('Admin.yes')) : '' ?></td><td><?= esc($row['last_answered_at'] === null ? '—' : fmt_date((string) $row['last_answered_at'], true, 'id')) ?></td></tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </section>
  <?php endforeach ?>
<?php endif ?>
<?= $this->endSection() ?>
