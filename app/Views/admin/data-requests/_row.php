<?php
/**
 * One parent's request in a list (Permintaan data, Tata kelola).
 *
 * @var array<string, mixed> $row request + `deadline` (DataRequestService::deadlines())
 */
$d = $row['deadline'];
?>
<tr>
  <td><a href="<?= base_url('admin/permintaan-data/' . $row['id']) ?>">#<?= (int) $row['id'] ?></a></td>
  <td class="mono"><?= esc((string) $row['participant_code']) ?></td>
  <td><?= esc(lang('Admin.requestKind_' . $row['kind'])) ?></td>
  <td><?= esc(lang('Admin.requestStatus_' . $row['status'])) ?></td>
  <td><?= esc(fmt_date((string) $row['received_at'], true, 'id')) ?><?php if ($d['received_hours'] !== null): ?> · <?= esc(lang('Admin.requestReceivedAgo', [$d['received_hours']])) ?><?php endif ?></td>
  <td class="deadline is-<?= esc($d['level'], 'attr') ?>">
    <?php if ($d['notify_left'] !== null): ?>
      <?= esc($d['notify_left'] >= 0 ? lang('Admin.requestNotifyLeft', [$d['notify_left']]) : lang('Admin.requestNotifyLate', [-$d['notify_left']])) ?>
    <?php else: ?>—<?php endif ?>
  </td>
</tr>
