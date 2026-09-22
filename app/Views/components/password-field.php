<?php
/**
 * Input kata sandi + daftar syarat yang harus dipenuhi.
 * Nilainya tidak pernah diisi ulang dari old(): siswa selalu mengetik ulang.
 *
 * @var string              $name
 * @var string              $label
 * @var array<string, mixed> $policy
 */
$fieldId = 'pw-' . $name;
?>
<div class="field field-password">
  <label for="<?= esc($fieldId) ?>"><?= esc($label) ?></label>
  <input type="password" id="<?= esc($fieldId) ?>" name="<?= esc($name) ?>"
         autocomplete="new-password" required
         minlength="<?= (int) ($policy['min_length'] ?? 8) ?>"
         maxlength="<?= (int) ($policy['max_length'] ?? 64) ?>"
         data-password-input>
  <button type="button" class="btn btn-quiet" data-password-toggle
          aria-controls="<?= esc($fieldId) ?>"><?= esc(lang('Auth.showPassword')) ?></button>
</div>
