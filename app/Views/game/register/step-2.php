<?php
/**
 * Pendaftaran langkah 2 — Sekolah dan daerah (`/daftar/2`).
 *
 * Negara (Indonesia / negara lain), provinsi, kabupaten/kota, lalu NPSN
 * (provinsi berdirektori, kartu "Benar ini sekolahmu?") atau nama sekolah.
 * Tanpa JavaScript: blok provinsi/kabupaten dan NPSN/nama bertukar lewat CSS
 * :has(), kabupaten dikelompokkan per provinsi (<optgroup>). register.js
 * menyaring kabupaten dan memeriksa NPSN (GET /api/schools/lookup); server
 * tetap memeriksa NPSN saat Lanjut.
 *
 * @var int                         $step
 * @var list<int>                   $done
 * @var array<string, mixed>        $values isian tersimpan di session wizard
 * @var list<array<string, mixed>>  $provinces
 * @var list<string>                $schoolProvinces
 * @var array<string, ?string>|null $selectedSchool
 * @var bool                        $npsnNotFound
 * @var array<string, string>       $errors
 */
$val     = static fn (string $f): string => (string) (old($f) ?? $values[$f] ?? '');
$err     = static fn (string $f): ?string => $errors[$f] ?? null;
$invalid = static fn (string $f): string => isset($errors[$f]) ? ' has-error' : '';
$country = $val('country_code') ?: 'ID';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.registerTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-auth-register.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-auth-art<?= $this->endSection() ?>
<?= $this->section('hudBack') ?><?= base_url('daftar/1') ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="register" data-step="2" data-package-gate="page" data-package-next="daftar/2">
  <?php /* Jaka waving, one still picture (owner, 18 October 2026), standing on the picture's path */ ?>
  <img class="auth-jaka stage-box" src="<?= esc(asset_url_versioned('ui/jaka-wave.webp'), 'attr') ?>" alt="" width="658" height="1490" decoding="async" draggable="false">

  <div class="register-panel ornate-panel stage-box">
    <?= $this->include('partials/ornate-frame') ?>
    <?= $this->include('game/register/_head') ?>

    <form method="post" action="<?= base_url('daftar/2') ?>" class="form register-form" data-step="2" autocomplete="off">
      <?= csrf_field() ?>
      <div class="register-fields stage-scroll">
      <?= view('partials/form-errors', ['errors' => $errors]) ?>

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
              <option value="<?= esc($province['code'], 'attr') ?>" <?= $val('province_code') === $province['code'] ? 'selected' : '' ?><?= in_array($province['code'], $schoolProvinces, true) ? ' data-directory' : '' ?>>
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
                  <option value="<?= esc($district['code'], 'attr') ?>" <?= $val('district_code') === $district['code'] ? 'selected' : '' ?>><?= esc($district['name']) ?></option>
                <?php endforeach ?>
              </optgroup>
            <?php endforeach ?>
          </select>
          <?php if ($err('district_code')): ?><p class="field-error"><?= esc($err('district_code')) ?></p><?php endif ?>
        </div>
      </div>

      <div class="field region-other<?= $invalid('country_other') ?>">
        <label for="country_other"><?= esc(lang('Game.countryOtherName')) ?></label>
        <input type="text" id="country_other" name="country_other" maxlength="100" enterkeyhint="next"
               value="<?= esc($val('country_other'), 'attr') ?>">
        <?php if ($err('country_other')): ?><p class="field-error"><?= esc($err('country_other')) ?></p><?php endif ?>
      </div>

      <!-- Sekolah: NPSN di provinsi berdirektori, nama bebas di tempat lain -->
      <div class="field school-npsn<?= $invalid('school_npsn') ?>" data-school-npsn
           data-checking="<?= esc(lang('Game.schoolNpsnChecking'), 'attr') ?>"
           data-card-title="<?= esc(lang('Game.schoolCardTitle'), 'attr') ?>"
           data-format="<?= esc(lang('Game.schoolNpsnFormat'), 'attr') ?>"
           data-unknown="<?= esc(lang('Game.schoolNpsnUnknown'), 'attr') ?>"
           data-warn-district="<?= esc(lang('Game.schoolWarnDistrict'), 'attr') ?>"
           data-warn-stage="<?= esc(lang('Game.schoolWarnStage'), 'attr') ?>">
        <label for="school_npsn"><?= esc(lang('Game.schoolNpsn')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="school_npsn" name="school_npsn" maxlength="8" inputmode="numeric"
               autocomplete="off" autocorrect="off" spellcheck="false" enterkeyhint="next" aria-describedby="school_npsn-help school-card"
               value="<?= esc($val('school_npsn'), 'attr') ?>">
        <p class="field-help" id="school_npsn-help"><?= esc(lang('Game.schoolNpsnHelp')) ?></p>
        <div class="school-card" id="school-card" role="status" aria-live="polite" data-school-card>
          <?php if ($selectedSchool !== null): ?>
            <p class="school-card-title"><?= esc(lang('Game.schoolCardTitle')) ?></p>
            <p class="school-card-name"><?= esc($selectedSchool['name']) ?></p>
            <p class="school-card-meta"><?= esc($selectedSchool['meta']) ?></p>
            <?php if (($selectedSchool['allowed'] ?? true) === false): ?>
              <p class="school-card-warn"><?= esc(lang('Game.schoolWarnStage', [$selectedSchool['level'] ?: strtoupper((string) $selectedSchool['stage'])])) ?></p>
            <?php endif ?>
          <?php endif ?>
        </div>
        <?php if ($err('school_npsn')): ?><p class="field-error"><?= esc($err('school_npsn')) ?></p><?php endif ?>
        <div class="school-manual" data-school-manual<?= $npsnNotFound || $val('school_manual') ? '' : ' hidden' ?>>
          <label class="check">
            <input type="checkbox" name="school_manual" value="1" <?= $val('school_manual') ? 'checked' : '' ?>>
            <span><?= esc(lang('Game.schoolManual')) ?></span>
          </label>
          <p class="field-help"><?= esc(lang('Game.schoolManualHelp')) ?></p>
        </div>
      </div>

      <div class="field school-name<?= $invalid('school_name') ?>">
        <label for="school_name"><?= esc(lang('Game.school')) ?> <span class="req" aria-hidden="true">*</span></label>
        <input type="text" id="school_name" name="school_name" minlength="3" maxlength="200"
               autocomplete="off" autocorrect="off" enterkeyhint="next" aria-describedby="school-help"
               value="<?= esc($val('school_name'), 'attr') ?>">
        <p class="field-help" id="school-help"><?= esc(lang('Game.schoolHelp')) ?></p>
        <?php if ($err('school_name')): ?><p class="field-error"><?= esc($err('school_name')) ?></p><?php endif ?>
      </div>

      </div>

      <div class="form-actions register-actions">
        <button class="btn btn-primary btn-lg" type="submit"><?= esc(lang('Game.next')) ?> <?= icon('right') ?></button>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>
