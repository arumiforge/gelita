<?php
/**
 * Round portrait of Jaka in the Peta Kedu story bar: the bust avatar
 * `ui.jaka-avatar` (catalogue reference.03), else the first letter of his
 * name. Never a pose frame (owner, 18 October 2026). Decorative: the
 * speaker's name is written next to it.
 *
 * @var string|null $src  URL of ui.jaka-avatar
 * @var string      $name speaker name
 */
?>
<span class="kedu-portrait<?= $src !== null ? ' has-art' : '' ?>" aria-hidden="true"><?php if ($src !== null): ?><img class="kedu-avatar" src="<?= esc($src, 'attr') ?>" alt="" decoding="async" draggable="false"><?php else: ?><span class="kedu-portrait-initial"><?= esc(mb_strtoupper(mb_substr($name, 0, 1))) ?></span><?php endif ?></span>
