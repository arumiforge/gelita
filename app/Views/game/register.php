<?php
/**
 * 4. Daftar — `/daftar` → RegisterController::form
 *
 * Tiga bagian bernomor agar tidak menakutkan anak: 1. Tentang kamu ·
 * 2. Sekolah dan daerah · 3. Akun rahasiamu.
 *
 * Tombol Daftar TIDAK dinonaktifkan walau sandi belum kuat, dan kolom sandi
 * sengaja tanpa `minlength`: penolakan dari server adalah umpan balik belajar
 * dan dicatat sebagai `pw_weak_submit_count`. Validasi browser yang
 * menghentikan kiriman lebih dulu akan membuat metrik itu tidak tercatat.
 *
 * Tanpa JavaScript: blok provinsi/kabupaten disembunyikan dengan CSS :has()
 * bila negara lain dipilih, dan daftar kabupaten dikelompokkan per provinsi
 * (<optgroup>). register.js (tahap 6) menyaring kabupaten sesuai provinsi dan
 * memeriksa ketersediaan nama pengguna.
 *
 * @var string                     $locale
 * @var bool                       $allowPhase
 * @var list<string>               $phases
 * @var list<array<string, mixed>> $provinces
 * @var list<string>               $schools
 * @var array<string, mixed>       $passwordPolicy
 * @var array<string, string>      $errors
 */
$err     = static fn (string $field): ?string => $errors[$field] ?? null;
$invalid = static fn (string $field): string => isset($errors[$field]) ? ' has-error' : '';
$country = (string) (old('country_code') ?: 'ID');
// Kunci numerik ('1'…'9') menjadi int di array PHP — bandingkan sebagai string.
$classes = [];
for ($grade = 1; $grade <= 6; $grade++) {
    $classes[(string) $grade] = lang('Game.classSd', [$grade]);
}
for ($grade = 7; $grade <= 9; $grade++) {
    $classes[(string) $grade] = lang('Game.classSmp', [$grade]);
}
$classes['lainnya'] = lang('Game.classOther');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.registerTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium register">
  <div class="panel-parchment">
    <header class="register-head">
      <span class="eyebrow"><?= esc(lang('Game.appName')) ?></span>
      <h1><?= esc(lang('Game.registerTitle')) ?></h1>
      <p class="muted"><?= esc(lang('Game.registerLead')) ?></p>
    </header>

    <?= $this->include('partials/form-errors') ?>

    <form method="post" action="<?= base_url('daftar') ?>" class="form register-form" autocomplete="off">
      <?= csrf_field() ?>

      <!-- 1. Tentang kamu -->
      <fieldset class="reg-step">
        <legend class="reg-step-title"><span class="reg-step-no">1</span> <?= esc(lang('Game.regStep1')) ?></legend>

        <div class="field<?= $invalid('display_name') ?>">
          <label for="display_name"><?= esc(lang('Game.displayName')) ?> <span class="req" aria-hidden="true">*</span></label>
          <input type="text" id="display_name" name="display_name" required minlength="2" maxlength="150"
                 aria-describedby="display_name-help" value="<?= esc(old('display_name'), 'attr') ?>">
          <p class="field-help" id="display_name-help"><?= esc(lang('Game.displayNameHelp')) ?></p>
          <?php if ($err('display_name')): ?><p class="field-error"><?= esc($err('display_name')) ?></p><?php endif ?>
        </div>

        <div class="field-row">
          <div class="field<?= $invalid('age') ?>">
            <label for="age"><?= esc(lang('Game.age')) ?> <span class="req" aria-hidden="true">*</span></label>
            <div class="input-affix">
              <input type="number" id="age" name="age" required min="5" max="80" inputmode="numeric"
                     value="<?= esc(old('age'), 'attr') ?>">
              <span><?= esc(lang('Game.ageUnit')) ?></span>
            </div>
            <?php if ($err('age')): ?><p class="field-error"><?= esc($err('age')) ?></p><?php endif ?>
          </div>

          <div class="field<?= $invalid('class_level') ?>">
            <label for="class_level"><?= esc(lang('Game.classLevel')) ?> <span class="req" aria-hidden="true">*</span></label>
            <select id="class_level" name="class_level" required>
              <option value=""><?= esc(lang('Game.classChoose')) ?></option>
              <?php foreach ($classes as $value => $label): ?>
                <option value="<?= esc((string) $value, 'attr') ?>" <?= (string) old('class_level') === (string) $value ? 'selected' : '' ?>><?= esc($label) ?></option>
              <?php endforeach ?>
            </select>
            <?php if ($err('class_level')): ?><p class="field-error"><?= esc($err('class_level')) ?></p><?php endif ?>
          </div>
        </div>

        <fieldset class="field<?= $invalid('gender') ?>">
          <legend class="label"><?= esc(lang('Game.gender')) ?> <span class="req" aria-hidden="true">*</span></legend>
          <div class="choice-row">
            <?php foreach (['laki-laki', 'perempuan', 'lainnya'] as $value): ?>
              <label class="check choice-pill">
                <input type="radio" name="gender" value="<?= esc($value, 'attr') ?>" required <?= old('gender') === $value ? 'checked' : '' ?>>
                <span><?= esc(lang('Game.gender_' . $value)) ?></span>
              </label>
            <?php endforeach ?>
          </div>
          <?php if ($err('gender')): ?><p class="field-error"><?= esc($err('gender')) ?></p><?php endif ?>
        </fieldset>
      </fieldset>

      <!-- 2. Sekolah dan daerah -->
      <fieldset class="reg-step">
        <legend class="reg-step-title"><span class="reg-step-no">2</span> <?= esc(lang('Game.regStep2')) ?></legend>

        <div class="field<?= $invalid('school_name') ?>">
          <label for="school_name"><?= esc(lang('Game.school')) ?> <span class="req" aria-hidden="true">*</span></label>
          <input type="text" id="school_name" name="school_name" required minlength="3" maxlength="200"
                 list="school-list" autocomplete="off" aria-describedby="school-help"
                 value="<?= esc(old('school_name'), 'attr') ?>">
          <datalist id="school-list">
            <?php foreach ($schools as $school): ?>
              <option value="<?= esc($school, 'attr') ?>"></option>
            <?php endforeach ?>
          </datalist>
          <p class="field-help" id="school-help"><?= esc(lang('Game.schoolHelp')) ?></p>
          <?php if ($err('school_name')): ?><p class="field-error"><?= esc($err('school_name')) ?></p><?php endif ?>
        </div>

        <div class="field<?= $invalid('country_code') ?>">
          <label for="country_code"><?= esc(lang('Game.country')) ?> <span class="req" aria-hidden="true">*</span></label>
          <select id="country_code" name="country_code" required data-region-country>
            <option value="ID" <?= $country === 'ID' ? 'selected' : '' ?>>Indonesia</option>
            <option value="XX" <?= $country !== 'ID' ? 'selected' : '' ?>><?= esc(lang('Game.countryOther')) ?></option>
          </select>
        </div>

        <div class="field-row region-id">
          <div class="field<?= $invalid('province_code') ?>">
            <label for="province_code"><?= esc(lang('Game.province')) ?> <span class="req" aria-hidden="true">*</span></label>
            <select id="province_code" name="province_code" data-region-province>
              <option value=""><?= esc(lang('Game.choose')) ?></option>
              <?php foreach ($provinces as $province): ?>
                <option value="<?= esc($province['code'], 'attr') ?>" <?= old('province_code') === $province['code'] ? 'selected' : '' ?>>
                  <?= esc($province['name']) ?>
                </option>
              <?php endforeach ?>
            </select>
            <?php if ($err('province_code')): ?><p class="field-error"><?= esc($err('province_code')) ?></p><?php endif ?>
          </div>

          <div class="field<?= $invalid('district_code') ?>">
            <label for="district_code"><?= esc(lang('Game.district')) ?> <span class="req" aria-hidden="true">*</span></label>
            <select id="district_code" name="district_code" data-region-district>
              <option value=""><?= esc(lang('Game.chooseProvince')) ?></option>
              <?php foreach ($provinces as $province): ?>
                <optgroup label="<?= esc($province['name'], 'attr') ?>" data-province="<?= esc($province['code'], 'attr') ?>">
                  <?php foreach ($province['districts'] ?? [] as $district): ?>
                    <option value="<?= esc($district['code'], 'attr') ?>" <?= old('district_code') === $district['code'] ? 'selected' : '' ?>><?= esc($district['name']) ?></option>
                  <?php endforeach ?>
                </optgroup>
              <?php endforeach ?>
            </select>
            <?php if ($err('district_code')): ?><p class="field-error"><?= esc($err('district_code')) ?></p><?php endif ?>
          </div>
        </div>

        <div class="field region-other<?= $invalid('country_other') ?>">
          <label for="country_other"><?= esc(lang('Game.countryOtherName')) ?></label>
          <input type="text" id="country_other" name="country_other" maxlength="100"
                 value="<?= esc(old('country_other'), 'attr') ?>">
          <?php if ($err('country_other')): ?><p class="field-error"><?= esc($err('country_other')) ?></p><?php endif ?>
        </div>

        <?php if ($allowPhase): ?>
          <div class="field<?= $invalid('phase') ?>">
            <label for="phase"><?= esc(lang('Game.phase')) ?></label>
            <select id="phase" name="phase">
              <?php foreach ($phases as $phase): ?>
                <option value="<?= esc($phase, 'attr') ?>" <?= old('phase') === $phase ? 'selected' : '' ?>><?= esc(lang_or('Game.phase_' . $phase, $phase)) ?></option>
              <?php endforeach ?>
            </select>
          </div>
        <?php endif ?>
      </fieldset>

      <!-- 3. Akun rahasiamu -->
      <fieldset class="reg-step">
        <legend class="reg-step-title"><span class="reg-step-no">3</span> <?= esc(lang('Game.regStep3')) ?></legend>

        <aside class="why-strong" aria-labelledby="why-strong-title">
          <?= component('character', ['character' => 'jaka', 'pose' => 'bow', 'class' => 'why-strong-jaka']) ?>
          <div class="why-strong-body">
            <h2 class="why-strong-title" id="why-strong-title"><?= icon('key') ?> <?= esc(lang('Game.whyStrongTitle')) ?></h2>
            <p><?= esc(lang('Auth.whyStrong')) ?></p>
            <ul class="why-strong-tips">
              <li><?= esc(lang('Auth.tip1')) ?></li>
              <li><?= esc(lang('Auth.tip2')) ?></li>
              <li><?= esc(lang('Auth.tip3')) ?></li>
              <li><?= esc(lang('Auth.tip4')) ?></li>
            </ul>
          </div>
        </aside>

        <div class="field<?= $invalid('username') ?>">
          <label for="username"><?= esc(lang('Auth.username')) ?> <span class="req" aria-hidden="true">*</span></label>
          <input type="text" id="username" name="username" required maxlength="30"
                 autocomplete="username" autocapitalize="none" spellcheck="false"
                 aria-describedby="username-help username-status" data-username-check
                 data-available="<?= esc(lang('Auth.usernameAvailable'), 'attr') ?>"
                 data-unavailable="<?= esc(lang('Auth.usernameUnavailable'), 'attr') ?>"
                 data-format="<?= esc(lang('Auth.usernameFormat'), 'attr') ?>"
                 value="<?= esc(old('username'), 'attr') ?>">
          <p class="field-help" id="username-help"><?= esc(lang('Auth.usernameHelp')) ?></p>
          <p class="username-status" id="username-status" role="status" aria-live="polite"></p>
          <?php if ($err('username')): ?><p class="field-error"><?= esc($err('username')) ?></p><?php endif ?>
        </div>

        <?= component('password-field', [
            'policy'        => $passwordPolicy,
            'errors'        => $errors,
            'usernameField' => 'username',
        ]) ?>
      </fieldset>

      <input type="hidden" name="locale" value="<?= esc($locale, 'attr') ?>">
      <input type="hidden" name="screen_size" data-device-screen>
      <input type="hidden" name="is_touch" data-device-touch>

      <div class="form-actions register-actions">
        <button class="btn btn-primary btn-xl" type="submit"><?= esc(lang('Game.registerSubmit')) ?> <?= icon('right') ?></button>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.back'), 'href' => base_url('persetujuan'), 'style' => 'quiet', 'arrow' => 'left'],
]]) ?>
<?= $this->endSection() ?>
