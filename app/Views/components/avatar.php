<?php
/**
 * Student avatar (display plan Phase B): the chosen `avatar.N` picture, or
 * the first letter of the name when none is chosen or the slot is empty.
 * Decorative (aria-hidden): the name is always written next to it.
 *
 * @var array<string, mixed> $participant Participant::toSafeArray()
 * @var string               $class       user-avatar (HUD) | profile-avatar (profile)
 */
$name = (string) (($participant['display_name'] ?? '') ?: ($participant['username'] ?? ''));
$src  = avatar_src(isset($participant['avatar_no']) ? (int) $participant['avatar_no'] : null);
?>
<span class="<?= esc($class, 'attr') ?><?= $src !== null ? ' has-art' : '' ?>" aria-hidden="true"><?php if ($src !== null): ?><img src="<?= esc($src, 'attr') ?>" alt="" decoding="async" draggable="false"><?php else: ?><?= esc(mb_strtoupper(mb_substr($name, 0, 1))) ?><?php endif ?></span>
