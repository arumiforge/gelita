<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Auth.changeTitle')) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="change-password">
  <h1><?= esc(lang('Auth.changeTitle')) ?></h1>
  <?php if ($mustChange): ?>
    <p class="alert alert-info" role="status"><?= esc(lang('Auth.mustChange')) ?></p>
  <?php endif ?>
  <?= $this->include('partials/flash') ?>

  <form method="post" action="<?= base_url('ganti-sandi') ?>" class="form" autocomplete="off">
    <?= csrf_field() ?>

    <div class="field">
      <label for="current_password"><?= esc(lang('Auth.currentPassword')) ?></label>
      <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
    </div>

    <?= $this->include('components/password-field', ['name' => 'password', 'label' => lang('Auth.password'), 'policy' => $passwordPolicy]) ?>
    <?= $this->include('components/password-field', ['name' => 'password_confirm', 'label' => lang('Auth.passwordRepeat'), 'policy' => $passwordPolicy]) ?>
    <?= $this->include('components/password-rules', ['policy' => $passwordPolicy]) ?>

    <button class="btn btn-primary" type="submit">Simpan kata sandi</button>
  </form>
</section>
<?= $this->endSection() ?>
