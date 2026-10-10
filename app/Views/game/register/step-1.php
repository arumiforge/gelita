<?php
/**
 * Pendaftaran langkah 1 — Tentang kamu (`/daftar/1`).
 *
 * Nama lengkap, umur (Config\Gelita::$registrationAge), dan jenis kelamin
 * (dua pilihan). Kelas tidak diisi: server memaksa kelas 5.
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

<?= $this->section('title') ?><?= esc(lang('Game.registerTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('mulai') ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="register" data-step="1">
  <?= component('character', ['character' => 'jaka', 'pose' => 'happy', 'class' => 'start-jaka stage-box']) ?>

  <div class="register-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <?= $this->include('game/register/_head') ?>

    <form method="post" action="<?= base_url('daftar/1') ?>" class="form register-form" data-step="1" autocomplete="off">
      <?= csrf_field() ?>
      <div class="register-fields stage-scroll">
      <?= view('partials/form-errors', ['errors' => $errors]) ?>

      <div class="field<?= $invalid('display_name') ?>">
        <label for="display_name"><?= esc(lang('Game.displayName')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="150"
               aria-describedby="display_name-help" value="<?= esc($val('display_name'), 'attr') ?>"<?= $focus('display_name') ?>>
        <p class="field-help" id="display_name-help"><?= esc(lang('Game.displayNameHelp')) ?></p>
        <?php if ($err('display_name')): ?><p class="field-error"><?= esc($err('display_name')) ?></p><?php endif ?>
      </div>

      <div class="field-row">
        <div class="field<?= $invalid('age') ?>">
          <label for="age"><?= esc(lang('Game.age')) ?> <span class="req" aria-hidden="true">*</span></label>
          <div class="input-affix input-affix-lg">
            <input type="number" id="age" name="age" required min="<?= $minAge ?>" max="<?= $maxAge ?>" inputmode="numeric"
                   value="<?= esc($val('age'), 'attr') ?>"<?= $focus('age') ?>>
            <span><?= esc(lang('Game.ageUnit')) ?></span>
          </div>
          <?php if ($err('age')): ?><p class="field-error"><?= esc($err('age')) ?></p><?php endif ?>
        </div>

        <div class="field">
          <span class="label"><?= esc(lang('Game.classLevel')) ?></span>
          <p class="class-fixed"><?= icon('lock') ?> <?= esc(class_label(config('Gelita')->registrationClass, service('request')->getLocale())) ?></p>
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

      <div class="form-actions register-actions">
        <button class="btn btn-primary btn-lg" type="submit"><?= esc(lang('Game.next')) ?> <?= icon('right') ?></button>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
