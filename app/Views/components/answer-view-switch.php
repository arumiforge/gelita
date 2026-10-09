<?php
/**
 * Switch for the answer columns of a raw data table: readable sentences
 * (default) or the stored JSON (`?raw=1`). Plain links, so it works without
 * JavaScript and keeps every other query parameter (filters, sort, size).
 * Placed in the `tools` slot of components/data-table.
 *
 * @var bool $readable current choice (BaseAdminController::readableAnswers())
 */
use App\Libraries\AdminTable;

$request = service('request');
$path    = '/' . ltrim($request->getPath(), '/');
$query   = $request->getGet() ?? [];
unset($query['format']);
$options = [
    lang('Admin.answerViewReadable') => ['raw' => null, 'page' => $query['page'] ?? null],
    lang('Admin.answerViewRaw')      => ['raw' => '1', 'page' => $query['page'] ?? null],
];
$current = $readable ? lang('Admin.answerViewReadable') : lang('Admin.answerViewRaw');
?>
<nav aria-label="<?= esc(lang('Admin.answerView'), 'attr') ?>" title="<?= esc(lang('Admin.answerViewHint'), 'attr') ?>">
  <ul class="sub-nav">
    <?php foreach ($options as $label => $change): ?>
      <li><a href="<?= esc(base_url(ltrim(AdminTable::url($path, $query, $change), '/')), 'attr') ?>"<?= $label === $current ? ' aria-current="true"' : '' ?>><?= esc($label) ?></a></li>
    <?php endforeach ?>
  </ul>
</nav>
