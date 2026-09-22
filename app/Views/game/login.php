<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.login')) ?> · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="login">
  <h1><?= esc(lang('Game.login')) ?></h1>
  <?= $this->include('partials/flash') ?>

  <form method="post" action="<?= base_url('masuk') ?>" class="form">
    <?= csrf_field() ?>

    <div class="field">
      <label for="username"><?= esc(lang('Auth.username')) ?></label>
      <input type="text" id="username" name="username" required autocomplete="username"
             value="<?= esc(old('username')) ?>">
    </div>

    <div class="field">
      <label for="password"><?= esc(lang('Auth.password')) ?></label>
      <input type="password" id="password" name="password" required autocomplete="current-password" data-password-input>
      <button type="button" class="btn btn-quiet" data-password-toggle aria-controls="password">
        <?= esc(lang('Auth.showPassword')) ?>
      </button>
    </div>

    <?php if ($allowPhase): ?>
      <div class="field">
        <label for="phase">Fase</label>
        <select id="phase" name="phase">
          <?php foreach ($phases as $phase): ?>
            <option value="<?= esc($phase) ?>"><?= esc($phase) ?></option>
          <?php endforeach ?>
        </select>
      </div>
    <?php endif ?>

    <button class="btn btn-primary btn-lg" type="submit"><?= esc(lang('Game.login')) ?></button>
  </form>

  <p><a href="<?= base_url('persetujuan') ?>">Belum punya akun? Daftar dulu.</a></p>
  <p><small>Lupa kata sandi? Mintalah gurumu mengatur ulang dari panel.</small></p>
</section>
<?= $this->endSection() ?>
