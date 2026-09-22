<?php
/**
 * Kata sandi baru: input + tombol lihat + peringatan Caps Lock + meter
 * kekuatan + daftar 5 syarat + ulangi sandi.
 *
 * Dipakai registrasi dan ganti sandi, sehingga tampilan dan aturannya selalu
 * sama. Aturan dikirim ke JavaScript sebagai JSON dari server
 * (PasswordPolicy::toClient()); password-meter.js (tahap 6) tidak memuat
 * angka atau syarat versinya sendiri. Teks tiap tingkat ada di atribut data-*
 * agar ikut bahasa ID/EN.
 *
 * Tanpa JavaScript, daftar syarat tetap terlihat sebagai panduan statis dan
 * server tetap menolak sandi lemah dengan pesan yang jelas.
 *
 * Kolom sandi TIDAK PERNAH diisi ulang dari old(): siswa selalu mengetik ulang.
 * Sengaja tanpa `minlength`: sandi terlalu pendek harus sampai ke server agar
 * ditolak dengan keterangan syarat dan tercatat sebagai `pw_weak_submit_count`,
 * bukan dihentikan diam-diam oleh validasi browser.
 *
 * @var array<string, mixed>       $policy        PasswordPolicy::toClient()
 * @var array<string, string>|null $errors
 * @var string|null                $label         label kolom sandi
 * @var string|null                $usernameField id input nama pengguna (registrasi)
 * @var string|null                $username      nama pengguna tetap (ganti sandi)
 */
$errors = is_array($errors ?? null) ? $errors : [];
$label  = $label ?? lang('Auth.password');
$max    = (int) ($policy['max_length'] ?? 64);
?>
<div class="password-field"
     data-username-field="<?= esc($usernameField ?? 'username', 'attr') ?>"
     <?= isset($username) ? 'data-username="' . esc($username, 'attr') . '"' : '' ?>
     data-level-weak="<?= esc(lang('Auth.levelWeak'), 'attr') ?>"
     data-level-medium="<?= esc(lang('Auth.levelMedium'), 'attr') ?>"
     data-level-strong="<?= esc(lang('Auth.levelStrong'), 'attr') ?>"
     data-contains-username="<?= esc(lang('Auth.containsUsername'), 'attr') ?>"
     data-not-match="<?= esc(lang('Auth.notMatch'), 'attr') ?>"
     data-match-ok="<?= esc(lang('Auth.matchOk'), 'attr') ?>"
     data-show-label="<?= esc(lang('Auth.showPassword'), 'attr') ?>"
     data-hide-label="<?= esc(lang('Auth.hidePassword'), 'attr') ?>">

  <div class="field<?= isset($errors['password']) ? ' has-error' : '' ?>">
    <label for="password"><?= esc($label) ?> <span class="req" aria-hidden="true">*</span></label>
    <div class="password-input">
      <input id="password" name="password" type="password"
             autocomplete="new-password" maxlength="<?= $max ?>" required
             aria-describedby="pw-rules pw-level<?= isset($errors['password']) ? ' pw-error' : '' ?>"
             <?= isset($errors['password']) ? 'aria-invalid="true"' : '' ?>>
      <button type="button" class="pw-toggle" aria-pressed="false" aria-controls="password"
              aria-label="<?= esc(lang('Auth.showPassword'), 'attr') ?>"><?= icon('eye') ?></button>
    </div>
    <p class="pw-caps" hidden><?= icon('warn') ?> <?= esc(lang('Auth.capsLock')) ?></p>
  </div>

  <div class="pw-meter" aria-hidden="true"><i data-level="none"></i></div>
  <p class="pw-level" id="pw-level" aria-live="polite"></p>

  <p class="pw-rules-title" id="pw-rules-title"><?= esc(lang('Auth.rulesTitle')) ?></p>
  <ul class="pw-rules" id="pw-rules" aria-labelledby="pw-rules-title">
    <li data-rule="length"><?= esc(lang('Auth.ruleLength')) ?></li>
    <li data-rule="upper"><?= esc(lang('Auth.ruleUpper')) ?></li>
    <li data-rule="lower"><?= esc(lang('Auth.ruleLower')) ?></li>
    <li data-rule="digit"><?= esc(lang('Auth.ruleDigit')) ?></li>
    <li data-rule="symbol"><?= esc(lang('Auth.ruleSymbol')) ?></li>
  </ul>
  <?php if (isset($errors['password'])): ?>
    <p class="field-error" id="pw-error"><?= esc($errors['password']) ?></p>
  <?php endif ?>

  <div class="field<?= isset($errors['password_confirm']) ? ' has-error' : '' ?>">
    <label for="password_confirm"><?= esc(lang('Auth.passwordRepeat')) ?> <span class="req" aria-hidden="true">*</span></label>
    <input id="password_confirm" name="password_confirm" type="password"
           autocomplete="new-password" maxlength="<?= $max ?>" required
           <?= isset($errors['password_confirm']) ? 'aria-invalid="true" aria-describedby="pw-confirm-error"' : '' ?>>
    <p class="pw-match" aria-live="polite"></p>
    <?php if (isset($errors['password_confirm'])): ?>
      <p class="field-error" id="pw-confirm-error"><?= esc($errors['password_confirm']) ?></p>
    <?php endif ?>
  </div>
</div>
<script type="application/json" id="password-policy"><?= json_encode(
    $policy,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
