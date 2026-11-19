<?php
/**
 * Research-mode notice at the top of every panel page (owner, 19 November
 * 2026): on or off for the running study, and what is recorded differently.
 * Text in Admin.researchMode*; facts in App\Libraries\ResearchModeNotice.
 *
 * @var array{on: bool, study: array<string, mixed>|null, rows: list<array{0: string, 1: string, 2: string}>} $researchMode
 */
$on    = $researchMode['on'];
$study = $researchMode['study'];
?>
<div class="alert <?= $on ? 'alert-warn' : 'alert-info' ?> research-mode-notice" role="status" data-research-mode="<?= $on ? 'on' : 'off' ?>">
  <?= icon($on ? 'warn' : 'info') ?>
  <div class="alert-body">
    <p><b><?= esc($on ? lang('Admin.researchModeOnTitle', [(string) ($study['name'] ?? $study['code'] ?? '')]) : lang('Admin.researchModeOffTitle')) ?></b>
      <?= esc(lang($on ? 'Admin.researchModeOnLead' : ($study === null ? 'Admin.researchModeOffLeadNoStudy' : 'Admin.researchModeOffLead'))) ?></p>
    <details class="research-mode-diff">
      <summary><?= esc(lang('Admin.researchModeDiffSummary')) ?></summary>
      <div class="research-mode-wrap">
        <table class="research-mode-table">
          <thead>
            <tr>
              <th scope="col"><?= esc(lang('Admin.researchModeColAspect')) ?></th>
              <th scope="col"<?= $on ? '' : ' class="is-current"' ?>><?= esc(lang('Admin.researchModeColOff')) ?></th>
              <th scope="col"<?= $on ? ' class="is-current"' : '' ?>><?= esc(lang('Admin.researchModeColOn')) ?></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($researchMode['rows'] as [$aspect, $off, $onText]): ?>
              <tr>
                <th scope="row"><?= esc($aspect) ?></th>
                <td<?= $on ? '' : ' class="is-current"' ?>><?= esc($off) ?></td>
                <td<?= $on ? ' class="is-current"' : '' ?>><?= esc($onText) ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php if (staff_can('studies.manage')): ?>
        <p class="muted"><?= esc(lang('Admin.researchModeWhereSwitch')) ?> <a href="<?= base_url('admin/studi') ?>"><?= esc(lang('Admin.researchModeOpenStudies')) ?></a></p>
      <?php endif ?>
    </details>
  </div>
</div>
