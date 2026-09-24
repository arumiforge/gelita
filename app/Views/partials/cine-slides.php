<?php
/**
 * Slide sinematik layar penuh: cerita pembuka (game/intro.php) dan penutup
 * (game/ending.php, Tahap 3). Latar per slide (`dialogues.background_media_id`)
 * dengan Ken Burns, tokoh sesuai pose (narator tanpa gambar), kotak teks
 * bergaya subtitle, efek layar, dan audio narasi bahasa aktif. Slide
 * berpindah lewat #{prefix}{n} + :target tanpa JavaScript.
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
    ?>
    <li class="slide cine-slide<?= $character === 'narator' ? ' is-narrator' : '' ?>" id="<?= esc($prefix . $n, 'attr') ?>" data-index="<?= $n ?>"
        data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"
        <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
        aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
      <?php if (media_exists($bgId)): ?>
        <img class="cine-bg" src="<?= esc(media_src($bgId), 'attr') ?>" alt="" <?= $n === 1 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
      <?php endif ?>
      <?php if ($character !== 'narator'): ?>
        <div class="cine-stage">
          <?= component('character', ['character' => $character, 'pose' => $pose, 'class' => 'pose-' . $pose]) ?>
        </div>
      <?php endif ?>
      <div class="cine-caption" data-narrator-advance>
        <div class="cine-caption-head">
          <span class="eyebrow"><?= esc(lang('Game.slideOf', [$n, $total])) ?></span>
          <?php if ($character !== 'narator'): ?>
            <span class="cine-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></span>
          <?php endif ?>
        </div>
        <?php if ($title !== ''): ?>
          <h2 class="cine-title"><?= esc($title) ?></h2>
        <?php endif ?>
        <p class="slide-text"><?= esc($text) ?></p>
        <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
          <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
        <?php endif ?>
        <?= component('partials/slide-nav', [
            'n'        => $n,
            'total'    => $total,
            'prefix'   => $prefix,
            'finalUrl' => $finalUrl,
            'final'    => $final,
        ]) ?>
      </div>
    </li>
  <?php endforeach ?>
</ol>
