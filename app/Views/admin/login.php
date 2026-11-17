<?php
/**
 * Masuk panel staf — `/admin/login` → AuthController::loginForm
 *
 * Akun tidak ada, sandi salah, dan akun nonaktif dibalas pesan yang sama.
 *
 * @var string                $redirectTo
 * @var array<string, string> $errors
 */
?>
<?= $this->extend('layouts/auth') ?>

<?php // Judul tab memakai bawaan layouts/auth (Admin.loginTitle), yang sudah di-esc() di sana ?>
<?= $this->section('content') ?>
<h1 class="auth-title"><?= esc(lang('Admin.loginTitle')) ?></h1>
<p class="auth-lead">Masuk dengan akun staf GELITA: admin, peneliti, atau ketua peneliti.</p>
<?= $this->include('partials/flash') ?>

<form method="post" action="<?= base_url('admin/login') ?>" class="form">
  <?= csrf_field() ?>
  <input type="hidden" name="redirect_to" value="<?= esc($redirectTo, 'attr') ?>">

  <div class="field<?= isset($errors['username']) ? ' has-error' : '' ?>">
    <label for="username">Nama pengguna</label>
    <input type="text" id="username" name="username" required autocomplete="username"
           autocapitalize="none" spellcheck="false" value="<?= esc(old('username'), 'attr') ?>">
  </div>

  <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
    <label for="password">Kata sandi</label>
    <input type="password" id="password" name="password" required autocomplete="current-password">
  </div>

  <button class="btn btn-primary btn-block" type="submit"><?= icon('key') ?> Masuk</button>
</form>

<p class="auth-foot">Panel ini khusus staf GELITA.<br>
  <a href="<?= base_url() ?>">Kembali ke permainan</a></p>
<?= $this->endSection() ?>

<?php // Right of the login card: the public staff guide (owner, 16 Nov 2026) ?>
<?= $this->section('aside') ?>
<section class="auth-card auth-guide" aria-labelledby="auth-guide-title">
  <span class="auth-guide-icon" aria-hidden="true"><?= icon('book') ?></span>
  <h2 class="auth-title" id="auth-guide-title"><?= esc(lang('Admin.guideCardTitle')) ?></h2>
  <p class="auth-lead"><?= esc(lang('Admin.guideCardText')) ?></p>
  <p class="auth-guide-pick"><?= esc(lang('Admin.guidePickRole')) ?></p>
  <ul class="auth-guide-roles">
    <?php foreach (\App\Libraries\StaffAccess::ROLES as $guideRole): ?>
      <li><a href="<?= esc(base_url('admin/panduan-staf') . '?peran=' . $guideRole, 'attr') ?>"><?= icon('arrow-right') ?> <?= esc(\App\Libraries\StaffAccess::label($guideRole)) ?></a></li>
    <?php endforeach ?>
  </ul>
  <a class="btn btn-block" href="<?= base_url('admin/panduan-staf') ?>"><?= icon('book') ?> <?= esc(lang('Admin.guideCardOpen')) ?></a>
</section>

<?php // Below it: the public validator guide, per panel (owner, 17 Nov 2026) ?>
<section class="auth-card auth-guide" aria-labelledby="auth-vguide-title">
  <span class="auth-guide-icon" aria-hidden="true"><?= icon('book') ?></span>
  <h2 class="auth-title" id="auth-vguide-title"><?= esc(lang('Admin.vguideCardTitle')) ?></h2>
  <p class="auth-lead"><?= esc(lang('Admin.vguideCardText')) ?></p>
  <p class="auth-guide-pick"><?= esc(lang('Admin.vguidePickPanel')) ?></p>
  <ul class="auth-guide-roles">
    <?php foreach (\App\Libraries\ValidatorGuideContent::PANELS as $vguidePanel => $vguideName): ?>
      <li><a href="<?= esc(base_url('admin/panduan-validator') . '?panel=' . $vguidePanel, 'attr') ?>"><?= icon('arrow-right') ?> <?= esc($vguideName) ?></a></li>
    <?php endforeach ?>
  </ul>
  <a class="btn btn-block" href="<?= base_url('admin/panduan-validator') ?>"><?= icon('book') ?> <?= esc(lang('Admin.vguideCardOpen')) ?></a>
</section>

<?php // Under both, across the row: the whole project and its reasons (owner, 17 Nov 2026) ?>
<section class="auth-card auth-guide is-wide" aria-labelledby="auth-gguide-title">
  <span class="auth-guide-icon" aria-hidden="true"><?= icon('compass') ?></span>
  <div>
    <h2 class="auth-title" id="auth-gguide-title"><?= esc(lang('Admin.gguideCardTitle')) ?></h2>
    <p class="auth-lead"><?= esc(lang('Admin.gguideCardText')) ?></p>
  </div>
  <a class="btn btn-block" href="<?= base_url('admin/panduan-gelita') ?>"><?= icon('book') ?> <?= esc(lang('Admin.gguideCardOpen')) ?></a>
</section>
<?= $this->endSection() ?>
