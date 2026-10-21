<?php
/**
 * Pendaftaran langkah 1 — Tentang kamu (`/daftar/1`).
 *
 * Nama lengkap, umur (Config\Gelita::$registrationAge), dan jenis kelamin
 * (dua pilihan). Kelas tidak diisi: server memaksa kelas 5. An optional
 * avatar picker follows once avatars are uploaded (display plan Phase B).
 * Owner, 21 October 2026: name, age, class, and gender in the first column;
 * the second holds only the avatar picker, with a hint to tap Berikutnya.
 *
 * @var int                   $step
 * @var list<int>             $done
 * @var array<string, mixed>  $values isian tersimpan di session wizard
 * @var array<string, string> $errors
 */
$val     = static fn (string $f): string => (string) (old($f) ?? $values[$f] ?? '');
$err     = static fn (string $f): ?string => $errors[$f] ?? null;
$invalid = static fn (string $f): string => isset($errors[$f]) ? ' has-error' : '';
$first   = array_key_first($errors);
$focus   = static fn (string $f): string => $first === $f ? ' autofocus' : '';
[$minAge, $maxAge] = config('Gelita')->registrationAge;
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.registerTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-mulai.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-dim-art<?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('mulai') ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="register" data-step="1" data-package-gate="page" data-package-next="daftar/1" data-cine="account">
  <div class="register-panel ornate-panel is-bare stage-box">
    <?php // No fill, border, shadow, or ornaments (owner, 21 October 2026): the form stands on the darkened picture ?>
    <?= $this->include('game/register/_head') ?>

    <form method="post" action="<?= base_url('daftar/1') ?>" class="form register-form" data-step="1" autocomplete="off"<?= round_style_attr(['loading']) ?>>
      <?= csrf_field() ?>
      <div class="register-fields stage-scroll">
      <?= view('partials/form-errors', ['errors' => $errors]) ?>

      <?php // Two columns, so the whole step fits the card without scrolling (owner, 20 October 2026) ?>
      <div class="register-grid">
      <div class="register-col">
      <div class="field<?= $invalid('display_name') ?>">
        <label for="display_name"><?= esc(lang('Game.displayName')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="150"
               aria-describedby="display_name-help" enterkeyhint="next" value="<?= esc($val('display_name'), 'attr') ?>"<?= $focus('display_name') ?>>
        <p class="field-help" id="display_name-help"><?= esc(lang('Game.displayNameHelp')) ?></p>
        <?php if ($err('display_name')): ?><p class="field-error"><?= esc($err('display_name')) ?></p><?php endif ?>
      </div>

      <div class="field-row">
        <div class="field<?= $invalid('age') ?>">
          <label for="age"><?= esc(lang('Game.age')) ?> <span class="req" aria-hidden="true">*</span></label>
          <div class="input-affix input-affix-lg">
            <input type="number" id="age" name="age" required min="<?= $minAge ?>" max="<?= $maxAge ?>" inputmode="numeric" enterkeyhint="next"
                   value="<?= esc($val('age'), 'attr') ?>"<?= $focus('age') ?>>
            <span><?= esc(lang('Game.ageUnit')) ?></span>
          </div>
          <?php if ($err('age')): ?><p class="field-error"><?= esc($err('age')) ?></p><?php endif ?>
        </div>

        <div class="field">
          <span class="label"><?= esc(lang('Game.classLevel')) ?></span>
          <p class="class-fixed"><?= round_icon('lock', 'class-fixed-art', 'lock') ?> <?= esc(class_label(config('Gelita')->registrationClass, service('request')->getLocale())) ?></p>
        </div>
      </div>

      <fieldset class="field<?= $invalid('gender') ?>">
        <legend class="label"><?= esc(lang('Game.gender')) ?> <span class="req" aria-hidden="true">*</span></legend>
        <div class="choice-row choice-row-lg">
          <?php foreach (config('Gelita')->genders as $index => $value): ?>
            <label class="check choice-pill choice-pill-lg">
              <input type="radio" name="gender" value="<?= esc($value, 'attr') ?>" required <?= $val('gender') === $value ? 'checked' : '' ?><?= $index === 0 ? $focus('gender') : '' ?>>
              <span><?= esc(lang('Game.gender_' . $value)) ?></span>
            </label>
          <?php endforeach ?>
        </div>
        <?php if ($err('gender')): ?><p class="field-error"><?= esc($err('gender')) ?></p><?php endif ?>
      </fieldset>
      </div>

      <div class="register-col">
      <?= component('components/avatar-picker', [
          'mode'    => 'radio',
          'current' => $val('avatar_no') === '' ? null : (int) $val('avatar_no'),
          'initial' => mb_strtoupper(mb_substr(trim($val('display_name')), 0, 1)),
          'legend'  => lang('Game.avatarOptional'),
          'help'    => lang('Game.avatarHelp'),
          'error'   => $err('avatar_no'),
      ]) ?>
      <p class="register-next-hint"><?= round_icon('info', 'hint-art') ?><span><?= esc(lang('Game.regNextHint')) ?></span></p>
      </div>
      </div>

      </div>

      <div class="form-actions register-actions">
        <?= gold_plate_button(lang('Game.next'), 'medium') ?>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
