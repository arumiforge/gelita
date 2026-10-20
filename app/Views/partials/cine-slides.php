<?php
/**
 * Slide sinematik layar penuh: cerita pembuka (game/intro.php) dan penutup
 * (game/ending.php, Tahap 3). Latar per slide (`dialogues.background_media_id`)
 * dengan Ken Burns, tokoh sesuai pose (narator tanpa gambar), kotak teks
 * bergaya subtitle, efek layar, dan audio narasi bahasa aktif. Slide
 * berpindah lewat #{prefix}{n} + :target tanpa JavaScript.
 *
 * Layout (intro redesign, 18 October 2026):
 * - `.cine-top`: the page dots, centred in the HUD band ("Bagian n dari N"
 *   only as their label for screen readers, owner 20 October 2026);
 * - `.cine-caption`: full-width subtitle along the bottom edge, straight on
 *   the picture (no band, owner 20 October 2026); the speaker and title share
 *   their row with the narrator controls (right);
 * - `.cine-final`: the last slide's main button, centred on the page, in
 *   without an arrow (owner, 18 October 2026; proper case since 20 October);
 * - `.cine-stage`: on a slide with a character, the figure stands at the left
 *   of the caption text, drawn over the caption.
 * The caption's own Kembali/Lanjut links are the no-JavaScript fallback; once
 * narrator.js runs they are hidden (swipe still clicks them) and the ▶ button
 * in the narrator controls reads "Lanjut".
 *
 * Backgrounds (owner, 19 October 2026): with JavaScript the slide's own
 * `.cine-bg` is hidden and its picture (`data-bg`) is shown by the layout's
 * scene images instead, with a dip to black between slides (core/backdrop.js,
 * `data-backdrop-mode="dip"` on the root), so no other picture shows between
 * two slides. The caption and the characters stay in place. Without
 * JavaScript each slide still shows its own `.cine-bg`.
 *
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 * @var string                     $prefix
 * @var string                     $finalUrl
 * @var string                     $final
 */
$total = count($slides);
?>
<ol class="slides cine-slides">
  <?php foreach ($slides as $index => $slide): ?>
    <?php
    $n         = $index + 1;
    $character = (string) ($slide['character_code'] ?? 'narator');
    $pose      = (string) ($slide['pose'] ?? '') ?: 'idle';
    $effect    = (string) ($slide['effect'] ?? '');
    $title     = tr($slide, 'title', $locale);
    $text      = tr($slide, 'text', $locale);
    $audioId   = (int) ($locale === 'en' ? ($slide['audio_en_asset_id'] ?? 0) : ($slide['audio_id_asset_id'] ?? 0));
    $bgId      = (int) ($slide['background_media_id'] ?? 0);
    $progress  = lang('Game.slideOf', [$n, $total]);
    ?>
    <li class="slide cine-slide<?= $character === 'narator' ? ' is-narrator' : '' ?>" id="<?= esc($prefix . $n, 'attr') ?>" data-index="<?= $n ?>"
        data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"
        <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?><?= slide_bg_attr($slide) ?>
        aria-label="<?= esc($progress, 'attr') ?>">
      <?php if (media_exists($bgId)): ?>
        <img class="cine-bg" src="<?= esc(media_src($bgId), 'attr') ?>" alt="" <?= $n === 1 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
      <?php endif ?>
      <?php if ($character !== 'narator'): ?>
        <div class="cine-stage">
          <?= component('character', ['character' => $character, 'pose' => $pose, 'class' => 'pose-' . $pose]) ?>
        </div>
      <?php endif ?>

      <nav class="cine-top" aria-label="<?= esc($progress, 'attr') ?>">
        <ol class="slide-dots">
          <?php for ($i = 1; $i <= $total; $i++): ?>
            <li>
              <a class="slide-dot<?= $i === $n ? ' is-current' : '' ?>" href="#<?= esc($prefix . $i, 'attr') ?>"
                 <?= $i === $n ? 'aria-current="step"' : '' ?>><span class="visually-hidden"><?= esc(lang('Game.slideOf', [$i, $total])) ?></span></a>
            </li>
          <?php endfor ?>
        </ol>
      </nav>

      <div class="cine-caption" data-narrator-advance>
        <div class="cine-caption-head">
          <?php if ($character !== 'narator'): ?>
            <span class="cine-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></span>
          <?php endif ?>
          <?php if ($title !== ''): ?>
            <h2 class="cine-title"><?= esc($title) ?></h2>
          <?php endif ?>
        </div>
        <div class="cine-caption-body stage-scroll">
          <p class="slide-text"><?= esc($text) ?></p>
          <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
            <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
          <?php endif ?>
        </div>
        <?php if ($total > 1): ?>
          <div class="slide-nav cine-step">
            <?php if ($n > 1): ?>
              <a class="btn btn-quiet" href="#<?= esc($prefix . ($n - 1), 'attr') ?>"><?= icon('left') ?> <?= esc(lang('Game.back')) ?></a>
            <?php endif ?>
            <?php if ($n < $total): ?>
              <a class="btn btn-primary" href="#<?= esc($prefix . ($n + 1), 'attr') ?>"><?= esc(lang('Game.continue')) ?> <?= icon('right') ?></a>
            <?php endif ?>
          </div>
        <?php endif ?>
      </div>

      <?php if ($n === $total): ?>
        <div class="cine-final">
          <a class="btn btn-primary btn-lg" href="<?= esc($finalUrl, 'attr') ?>" data-slide-final><?= esc($final) ?></a>
        </div>
      <?php endif ?>
    </li>
  <?php endforeach ?>
</ol>
