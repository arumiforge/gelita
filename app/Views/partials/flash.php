<?php
/**
 * Galat yang harus tetap terlihat: flash `error` dan galat per field.
 * Pesan berhasil (`message`) tampil sebagai toast dari layout. Flash
 * `notice` untuk catatan yang perlu dibaca tetapi bukan kegagalan, mis.
 * thumbnail video Pustaka yang belum dapat diunduh server.
 *
 * @var array<string, string>|null $errors
 */
$flashError  = session('error');
$flashNotice = session('notice');
$fieldErrors = $errors ?? (session('errors') ?? []);
$fieldErrors = is_array($fieldErrors) ? $fieldErrors : [];
?>
<?php if (is_string($flashError) && $flashError !== ''): ?>
  <div class="alert alert-error" role="alert"><?= icon('warn') ?><p><?= esc($flashError) ?></p></div>
<?php endif ?>
<?php if (is_string($flashNotice) && $flashNotice !== ''): ?>
  <div class="alert alert-info" role="status"><?= icon('info') ?><p><?= esc($flashNotice) ?></p></div>
<?php endif ?>
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
