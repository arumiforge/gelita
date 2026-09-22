<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?>Daftar · GELITA<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="register">
  <h1>Buat akunmu</h1>
  <?= $this->include('partials/flash') ?>

  <form method="post" action="<?= base_url('daftar') ?>" class="form" autocomplete="off">
    <?= csrf_field() ?>

    <div class="field">
      <label for="display_name">Nama panggilan</label>
      <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="150"
             value="<?= esc(old('display_name')) ?>">
    </div>

    <div class="field">
      <label for="age">Umur</label>
      <input type="number" id="age" name="age" required min="5" max="80" value="<?= esc(old('age')) ?>">
    </div>

    <fieldset class="field">
      <legend>Jenis kelamin</legend>
      <?php foreach (['laki-laki' => 'Laki-laki', 'perempuan' => 'Perempuan', 'lainnya' => 'Lainnya'] as $value => $label): ?>
        <label class="check">
          <input type="radio" name="gender" value="<?= esc($value) ?>" required
                 <?= old('gender') === $value ? 'checked' : '' ?>>
          <?= esc($label) ?>
        </label>
      <?php endforeach ?>
    </fieldset>

    <div class="field">
      <label for="class_level">Kelas</label>
      <input type="text" id="class_level" name="class_level" required maxlength="20" value="<?= esc(old('class_level')) ?>">
    </div>

    <div class="field">
      <label for="school_name">Nama sekolah</label>
      <input type="text" id="school_name" name="school_name" required minlength="3" maxlength="200"
             value="<?= esc(old('school_name')) ?>">
    </div>

    <input type="hidden" name="country_code" value="ID">

    <div class="field">
      <label for="province_code">Provinsi</label>
      <select id="province_code" name="province_code" data-region-province>
        <option value="">— pilih —</option>
        <?php foreach ($provinces as $province): ?>
          <option value="<?= esc($province['code']) ?>" <?= old('province_code') === $province['code'] ? 'selected' : '' ?>>
            <?= esc($province['name']) ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>

    <div class="field">
      <label for="district_code">Kabupaten / kota</label>
      <select id="district_code" name="district_code" data-region-district>
        <option value="">— pilih provinsi dulu —</option>
        <?php foreach ($provinces as $province): ?>
          <?php if (old('province_code') !== $province['code']) {
              continue;
          } ?>
          <?php foreach ($province['districts'] ?? [] as $district): ?>
            <option value="<?= esc($district['code']) ?>" <?= old('district_code') === $district['code'] ? 'selected' : '' ?>>
              <?= esc($district['name']) ?>
            </option>
          <?php endforeach ?>
        <?php endforeach ?>
      </select>
    </div>

    <?php if ($allowPhase): ?>
      <div class="field">
        <label for="phase">Fase</label>
        <select id="phase" name="phase">
          <?php foreach ($phases as $phase): ?>
            <option value="<?= esc($phase) ?>" <?= old('phase') === $phase ? 'selected' : '' ?>><?= esc($phase) ?></option>
          <?php endforeach ?>
        </select>
      </div>
    <?php endif ?>

    <div class="field">
      <label for="username"><?= esc(lang('Auth.username')) ?></label>
      <input type="text" id="username" name="username" required pattern="[a-z0-9._]{3,30}"
             autocomplete="username" data-username-check value="<?= esc(old('username')) ?>">
      <small><?= esc(lang('Auth.usernameHelp')) ?></small>
      <p class="field-hint" data-username-status role="status"></p>
    </div>

    <?= $this->include('components/password-field', ['name' => 'password', 'label' => lang('Auth.password'), 'policy' => $passwordPolicy]) ?>
    <?= $this->include('components/password-field', ['name' => 'password_confirm', 'label' => lang('Auth.passwordRepeat'), 'policy' => $passwordPolicy]) ?>
    <?= $this->include('components/password-rules', ['policy' => $passwordPolicy]) ?>

    <input type="hidden" name="locale" value="<?= esc($locale) ?>">
    <input type="hidden" name="screen_size" data-device-screen>
    <input type="hidden" name="is_touch" data-device-touch>

    <button class="btn btn-primary btn-lg" type="submit">Mulai bermain</button>
  </form>
</section>
<?= $this->endSection() ?>
