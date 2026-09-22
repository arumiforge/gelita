<?php
/**
 * Daftar syarat kata sandi. Aturan yang sama dipakai server (PasswordPolicy)
 * dan JavaScript, yang membacanya dari <script id="password-policy">.
 *
 * @var array<string, mixed> $policy
 */
?>
<section class="password-help">
  <p><?= esc(lang('Auth.whyStrong')) ?></p>
  <ul class="password-rules" data-password-rules>
    <li data-rule="length"><?= esc(lang('Auth.ruleLength')) ?></li>
    <li data-rule="upper"><?= esc(lang('Auth.ruleUpper')) ?></li>
    <li data-rule="lower"><?= esc(lang('Auth.ruleLower')) ?></li>
    <li data-rule="digit"><?= esc(lang('Auth.ruleDigit')) ?></li>
    <li data-rule="symbol"><?= esc(lang('Auth.ruleSymbol')) ?></li>
  </ul>
  <ul class="password-tips">
    <li><?= esc(lang('Auth.tip1')) ?></li>
    <li><?= esc(lang('Auth.tip2')) ?></li>
    <li><?= esc(lang('Auth.tip3')) ?></li>
    <li><?= esc(lang('Auth.tip4')) ?></li>
  </ul>
</section>
<script type="application/json" id="password-policy"><?= json_encode(
    $policy,
    JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
