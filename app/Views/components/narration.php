<?php
/**
 * Balon narasi tokoh.
 *
 * @var string      $text
 * @var string|null $speaker    `jaka` | `mbah_kedu` — nama tokoh di atas teks
 * @var string|null $tail       `left` (bawaan) | `down`
 * @var string|null $id
 */
$speakerName = isset($speaker) && $speaker !== '' ? lang_or('Game.char_' . $speaker, $speaker) : null;
?>
<blockquote class="narration<?= ($tail ?? 'left') === 'down' ? ' narration-down' : '' ?>"
            <?= isset($id) ? 'id="' . esc($id, 'attr') . '"' : '' ?>>
  <?php if ($speakerName !== null): ?>
    <cite><?= esc($speakerName) ?></cite>
  <?php endif ?>
  <?= esc($text ?? '') ?>
</blockquote>
