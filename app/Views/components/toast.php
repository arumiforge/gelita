<?php
/**
 * Toast layer (#toast-layer).
 *
 * Server flash messages render here as toasts that fade on their own through
 * a CSS animation, so they work without JavaScript. core/toast.js adopts them
 * into its queue on load and adds later toasts to the same layer.
 *
 * Game (`$withErrors` true, display plan Phase E): game-style cards with a
 * round icon badge. `message` shows as `ok`; `error` shows as `warn`, because
 * every game `error` flash is a "not open yet" lock message, the same as the
 * lock toast from JavaScript. The `<template data-toast-icon>` elements give
 * JavaScript toasts the same badges.
 *
 * Admin panel (`$withErrors` false): only `message`, in the admin look;
 * `error` renders fixed at the top of the page through partials/flash.
 *
 * @var bool|null $withErrors game layer: include the `error` flash and badges
 */
$game    = ! empty($withErrors);
$message = session('message');
$error   = $game ? session('error') : null;

// Badge glyphs (✓ ! ✕ i), drawn to sit inside a round badge
$glyphs = [
    'ok'   => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
    'warn' => '<path d="M12 5v9"/><path d="M12 19h.01"/>',
    'bad'  => '<path d="M6.5 6.5l11 11M17.5 6.5l-11 11"/>',
    'info' => '<path d="M12 11v8"/><path d="M12 5.5h.01"/>',
];
$badge = static fn (string $type): string => '<span class="toast-badge" aria-hidden="true">'
    . '<svg class="icon" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor"'
    . ' stroke-width="3" stroke-linecap="round" stroke-linejoin="round" focusable="false">'
    . $glyphs[$type] . '</svg></span>';
?>
<div id="toast-layer" class="toast-layer" role="status" aria-live="polite">
  <?php if ($game): ?>
    <?php if (is_string($message) && $message !== ''): ?>
      <div class="toast is-ok is-flash" data-toast-type="ok"><?= $badge('ok') ?><span class="toast-text"><?= esc($message) ?></span></div>
    <?php endif ?>
    <?php if (is_string($error) && $error !== ''): ?>
      <div class="toast is-warn is-flash" data-toast-type="warn"><?= $badge('warn') ?><span class="toast-text"><?= esc($error) ?></span></div>
    <?php endif ?>
    <?php foreach (array_keys($glyphs) as $type): ?>
      <template data-toast-icon="<?= $type ?>"><?= $badge($type) ?></template>
    <?php endforeach ?>
  <?php elseif (is_string($message) && $message !== ''): ?>
    <div class="toast is-ok is-flash"><?= icon('check') ?><span><?= esc($message) ?></span></div>
  <?php endif ?>
</div>
