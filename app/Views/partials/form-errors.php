<?php
/**
 * Ringkasan galat per field untuk form di area game. Flash `error` tampil
 * sebagai toast dari layout; pesan per field juga dirender di dekat kolomnya.
 *
 * @var array<string, string>|null $errors
 */
$fieldErrors = $errors ?? (session('errors') ?? []);
$fieldErrors = is_array($fieldErrors) ? array_filter($fieldErrors, 'is_string') : [];
?>
<?php if ($fieldErrors !== []): ?>
  <div class="alert alert-error" role="alert">
    <?= icon('warn') ?>
    <?php if (count($fieldErrors) === 1): ?>
      <p><?= esc(reset($fieldErrors)) ?></p>
    <?php else: ?>
      <ul>
        <?php foreach ($fieldErrors as $message): ?>
          <li><?= esc($message) ?></li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </div>
<?php endif ?>
