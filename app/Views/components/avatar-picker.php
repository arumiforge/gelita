<?php
/**
 * Avatar picker (display plan Phase B): "Huruf awal namaku" first, then every
 * uploaded `avatar.N` (avatar_choices()). Renders nothing while no avatar is
 * uploaded. Works without JavaScript.
 *
 * - `radio` (sign-up `/daftar/1`): radio inputs named `avatar_no`, sent with
 *   the step form; '' = the initial.
 * - `submit` (profile): each tile is a submit button of its own form, so one
 *   tap saves (POST /profil/avatar).
 *
 * The chosen tile has a gold ring and a ✓ plus hidden text, not colour alone.
 *
 * @var string   $mode    radio | submit
 * @var int|null $current chosen avatar number, null = initial
 * @var string   $initial letter shown on the first tile ('' = person icon)
 * @var string   $legend
 * @var bool|null $legendHidden true when a visible heading already names it (profile <summary>)
 * @var string|null $help
 * @var string|null $error
 */
$choices = avatar_choices();

if ($choices === []) {
    return;
}

$help  ??= null;
$legendHidden ??= false;
$error ??= null;
$tiles = ['' => null] + $choices; // PHP keeps numeric keys as int: compare as strings below
?>
<fieldset class="field avatar-picker<?= $error !== null ? ' has-error' : '' ?>">
  <legend class="<?= $legendHidden ? 'visually-hidden' : 'label' ?>"><?= esc($legend) ?></legend>
  <?php if ($help !== null): ?><p class="field-help"><?= esc($help) ?></p><?php endif ?>
  <div class="avatar-grid">
    <?php foreach ($tiles as $value => $src): ?>
      <?php
        $value  = (string) $value;
        $chosen = (string) ($current ?? '') === $value;
        $name   = $value === '' ? lang('Game.avatarInitial') : lang('Game.avatarOption', [$value]);
        $face   = $src !== null
            ? '<img src="' . esc($src, 'attr') . '" alt="" decoding="async" loading="lazy" draggable="false">'
            : ($initial !== '' ? esc($initial) : icon('user'));
      ?>
      <?php if ($mode === 'radio'): ?>
        <label class="avatar-tile">
          <input type="radio" name="avatar_no" value="<?= esc($value, 'attr') ?>" <?= $chosen ? 'checked' : '' ?>>
          <span class="avatar-face<?= $src === null ? ' is-initial' : '' ?>" aria-hidden="true"><?= $face ?></span>
          <span class="visually-hidden"><?= esc($name) ?></span>
        </label>
      <?php else: ?>
        <button type="submit" class="avatar-tile" name="avatar_no" value="<?= esc($value, 'attr') ?>" aria-pressed="<?= $chosen ? 'true' : 'false' ?>">
          <span class="avatar-face<?= $src === null ? ' is-initial' : '' ?>" aria-hidden="true"><?= $face ?></span>
          <span class="visually-hidden"><?= esc($name) ?><?= $chosen ? ' (' . esc(lang('Game.avatarChosen')) . ')' : '' ?></span>
        </button>
      <?php endif ?>
    <?php endforeach ?>
  </div>
  <?php if ($error !== null): ?><p class="field-error"><?= esc($error) ?></p><?php endif ?>
</fieldset>
