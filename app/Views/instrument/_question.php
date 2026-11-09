<?php
/**
 * One item's inputs (34 §7.1, §7.3): only `show` of the data file reaches
 * the page; the key never does. Choices keep the order of the data file.
 *
 * @var array<string, mixed>       $item    decoded item (show, units, type)
 * @var array<string, string|null> $values  unit → stored raw response
 * @var array<string, mixed>       $meta    version meta (scales, type hints)
 * @var bool                       $hint    show the type hint above the prompt
 */
$show  = $item['show'];
$unit  = $item['units'][0];
$value = $values[$unit] ?? null;
$id    = static fn (string ...$parts): string => 'q-' . preg_replace('/[^a-z0-9]+/i', '-', implode('-', $parts));
$hints = (array) ($meta['type_hints'] ?? []);
$hintKey = $item['type'] === 'pgk' ? 'pgk_' . ($show['scale'] ?? 'bs') : $item['type'];
?>
<fieldset class="mb-4">
  <?php if (! empty($show['lead'])): ?><p><?= esc($show['lead']) ?></p><?php endif ?>
  <?php if (($hint ?? true) && ! empty($hints[$hintKey])): ?><p class="text-body-secondary small mb-1"><?= esc($hints[$hintKey]) ?></p><?php endif ?>
  <?php if (! empty($show['prompt'])): ?>
    <legend class="fs-5 fw-bold"><?= instrument_inline((string) $show['prompt']) ?><?php if (! empty($show['hint'])): ?> <em class="fw-normal"><?= esc($show['hint']) ?></em><?php endif ?></legend>
  <?php endif ?>

  <?php if (in_array($item['type'], ['pg', 'd2'], true) || ($item['type'] === 'single' && count($item['units']) === 1)): ?>
    <?php foreach ((array) $show['options'] as $key => $text): ?>
      <label class="choice" for="<?= $id($unit, (string) $key) ?>">
        <input class="form-check-input" type="radio" name="a[<?= esc($unit, 'attr') ?>]" id="<?= $id($unit, (string) $key) ?>" value="<?= esc((string) $key, 'attr') ?>"<?= (string) $value === (string) $key ? ' checked' : '' ?>>
        <?php if ($item['type'] !== 'single'): ?><span class="choice-letter"><?= esc((string) $key) ?>.</span><?php endif ?>
        <span><?= instrument_inline((string) $text) ?></span>
      </label>
    <?php endforeach ?>

  <?php elseif ($item['type'] === 'single'): ?>
    <?php foreach ((array) ($show['rows'] ?? $show['columns']) as $rowUnit => $label): ?>
      <div class="mb-3">
        <div class="fw-semibold mb-1"><?= esc((string) $label) ?></div>
        <?php foreach ((array) $show['options'] as $key => $text): ?>
          <label class="choice" for="<?= $id($rowUnit, (string) $key) ?>">
            <input class="form-check-input" type="radio" name="a[<?= esc($rowUnit, 'attr') ?>]" id="<?= $id($rowUnit, (string) $key) ?>" value="<?= esc((string) $key, 'attr') ?>"<?= (string) ($values[$rowUnit] ?? '') === (string) $key && ($values[$rowUnit] ?? null) !== null ? ' checked' : '' ?>>
            <span><?= esc((string) $text) ?></span>
          </label>
        <?php endforeach ?>
      </div>
    <?php endforeach ?>

  <?php elseif ($item['type'] === 'multi'): ?>
    <input type="hidden" name="m[<?= esc($item['code'], 'attr') ?>]" value="1">
    <?php foreach ((array) $show['boxes'] as $boxUnit => $label): ?>
      <label class="choice" for="<?= $id($boxUnit) ?>">
        <input class="form-check-input" type="checkbox" name="a[<?= esc($boxUnit, 'attr') ?>]" id="<?= $id($boxUnit) ?>" value="1"<?= ($values[$boxUnit] ?? null) === '1' ? ' checked' : '' ?>>
        <span><?= esc((string) $label) ?></span>
      </label>
    <?php endforeach ?>
    <?php if (! empty($show['decline'])): ?>
      <label class="choice" for="<?= $id($item['code'], 'decline') ?>">
        <input class="form-check-input" type="checkbox" name="d[<?= esc($item['code'], 'attr') ?>]" id="<?= $id($item['code'], 'decline') ?>" value="1"<?= ($values[$item['units'][0]] ?? null) === '7' ? ' checked' : '' ?>>
        <span><?= esc((string) $show['decline']) ?></span>
      </label>
    <?php endif ?>

  <?php elseif ($item['type'] === 'pgk'): ?>
    <?php $scale = (array) ($meta['scales'][$show['scale'] ?? 'bs'] ?? []) ?>
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th scope="col"></th><?php foreach ($scale as $label): ?><th scope="col" class="text-center"><?= esc((string) $label) ?></th><?php endforeach ?></tr></thead>
        <tbody>
        <?php foreach ((array) $show['statements'] as $no => $statement): ?>
          <tr>
            <th scope="row" class="fw-normal">(<?= esc((string) $no) ?>) <?= instrument_inline((string) $statement) ?></th>
            <?php foreach ($scale as $letter => $label): ?>
              <td class="text-center">
                <input class="form-check-input" type="radio" name="a[<?= esc($unit, 'attr') ?>][<?= esc((string) $no, 'attr') ?>]" value="<?= esc((string) $letter, 'attr') ?>"
                       aria-label="<?= esc('(' . $no . ') ' . $label, 'attr') ?>"<?= $value !== null && ($value[(int) $no - 1] ?? '') === (string) $letter ? ' checked' : '' ?>>
              </td>
            <?php endforeach ?>
          </tr>
        <?php endforeach ?>
        </tbody>
      </table>
    </div>

  <?php elseif ($item['type'] === 'match'): ?>
    <?php $pairs = $value === null ? [] : App\Services\InstrumentScorer::pairs((string) $value) ?>
    <div class="row g-3">
      <div class="col-md-6">
        <?php foreach ((array) $show['right'] as $letter => $text): ?>
          <p class="mb-1"><span class="choice-letter"><?= esc((string) $letter) ?>.</span> <?= instrument_inline((string) $text) ?></p>
        <?php endforeach ?>
      </div>
      <div class="col-md-6">
        <?php foreach ((array) $show['left'] as $no => $text): ?>
          <div class="mb-2">
            <label class="form-label" for="<?= $id($unit, (string) $no) ?>"><?= esc((string) $no) ?>. <?= instrument_inline((string) $text) ?></label>
            <select class="form-select" name="a[<?= esc($unit, 'attr') ?>][<?= esc((string) $no, 'attr') ?>]" id="<?= $id($unit, (string) $no) ?>">
              <option value=""><?= esc(lang('Instrument.matchPick')) ?></option>
              <?php foreach (array_keys((array) $show['right']) as $letter): ?>
                <option value="<?= esc((string) $letter, 'attr') ?>"<?= ($pairs[(string) $no] ?? '') === (string) $letter ? ' selected' : '' ?>><?= esc((string) $letter) ?></option>
              <?php endforeach ?>
            </select>
          </div>
        <?php endforeach ?>
      </div>
    </div>
  <?php endif ?>

  <?php foreach ((array) ($show['explain'] ?? []) as $explain): ?>
    <p class="small text-body-secondary mt-2 mb-0"><?= esc((string) $explain) ?></p>
  <?php endforeach ?>
</fieldset>
