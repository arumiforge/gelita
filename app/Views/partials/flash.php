<?php
/**
 * Pesan flash bersama: berhasil, galat umum, dan galat per field.
 *
 * @var array<string, string> $errors
 */
$flashOk    = session('message');
$flashError = session('error');
$fieldErrors = $errors ?? (session('errors') ?? []);
?>
<?php if ($flashOk): ?>
  <p class="alert alert-ok" role="status"><?= esc($flashOk) ?></p>
<?php endif ?>
<?php if ($flashError): ?>
  <p class="alert alert-error" role="alert"><?= esc($flashError) ?></p>
<?php endif ?>
<?php if ($fieldErrors): ?>
  <ul class="alert alert-error" role="alert">
    <?php foreach ($fieldErrors as $message): ?>
      <li><?= esc($message) ?></li>
    <?php endforeach ?>
  </ul>
<?php endif ?>
