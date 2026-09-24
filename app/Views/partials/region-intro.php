<?php
/**
 * Overlay layar penuh "Mengenal {wilayah}" di Peta Kedu (Tahap 3): narasi
 * Mbah Kedu `region_intro` (4 slide) dengan pose, efek, dan audio.
 *
 * Dibuka dari tombol lentera "Kenali {wilayah}" pada pin dan kartu wilayah.
 * Dengan JavaScript (game/map.js): narrator mode `external`, dimulai
 * `start({ userInitiated: true })` di dalam ketukan tombol itu; slide tanpa
 * hash URL (`data-hash="0"`) karena beberapa pemutar berbagi halaman peta.
 * Tanpa JavaScript: target `:target` #kenal-{code}, slide lewat
 * #kenal-{code}-{n}, dan tautan tutup menuju kartu wilayahnya.
 *
 * Wilayah terkunci boleh dikenali. Slide akhir: "Masuk ke {wilayah}" (tirai
 * wilayah, ke `entry`) bila wilayah terbuka, selain itu "Tutup".
 *
 * @var array<string, mixed>       $level  baris levelOverview() + `curtain`
 * @var list<array<string, mixed>> $slides dialogues region_intro wilayah itu
 * @var string                     $locale
 */
$code   = (string) $level['code'];
$region = (string) $level['name'];
$prefix = 'kenal-' . $code . '-';
$total  = count($slides);
$open   = $level['status'] !== 'locked';
$close  = '#region-' . $code;
?>
<section class="kenal narrator" id="kenal-<?= esc($code, 'attr') ?>" role="dialog" aria-modal="true"
         aria-labelledby="kenal-<?= esc($code, 'attr') ?>-title"
         data-kenal="<?= esc($code, 'attr') ?>" data-narrator data-context="region_intro"
         data-prefix="<?= esc($prefix, 'attr') ?>" data-mode="external" data-keyboard="0" data-hash="0"
         data-level-id="<?= esc($level['id'], 'attr') ?>">
  <?php if (! empty($level['background'])): ?>
    <img class="kenal-bg" src="<?= esc($level['background'], 'attr') ?>" alt="" loading="lazy">
  <?php endif ?>
  <header class="kenal-head">
    <div>
      <span class="eyebrow"><?= esc(lang('Game.levelOrder', [$level['sequence']])) ?> · <?= esc(lang_or('Game.difficulty_' . $level['difficulty'], (string) $level['difficulty'])) ?></span>
      <h2 id="kenal-<?= esc($code, 'attr') ?>-title"><?= esc(lang('Game.kenalTitle', [$region])) ?></h2>
    </div>
    <a class="icon-btn kenal-close" href="<?= esc($close, 'attr') ?>" data-kenal-close
       aria-label="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></a>
  </header>

  <div class="narrator-fx" data-narrator-fx aria-hidden="true"></div>

  <ol class="slides kenal-slides">
    <?php foreach ($slides as $index => $slide): ?>
      <?php
      $n         = $index + 1;
      $character = (string) ($slide['character_code'] ?? 'mbah_kedu');
      $pose      = (string) ($slide['pose'] ?? '') ?: 'idle';
      $effect    = (string) ($slide['effect'] ?? '');
      $title     = tr($slide, 'title', $locale);
      $text      = tr($slide, 'text', $locale);
      $audioId   = (int) ($locale === 'en' ? ($slide['audio_en_asset_id'] ?? 0) : ($slide['audio_id_asset_id'] ?? 0));
      ?>
      <li class="slide kenal-slide<?= $character === 'narator' ? ' is-narrator' : '' ?>" id="<?= esc($prefix . $n, 'attr') ?>" data-index="<?= $n ?>"
          data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"
          <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
          aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
        <?php if ($character !== 'narator'): ?>
          <div class="kenal-stage">
            <?= component('character', ['character' => $character, 'pose' => $pose, 'class' => 'pose-' . $pose]) ?>
          </div>
        <?php endif ?>
        <div class="kenal-caption" data-narrator-advance>
          <div class="cine-caption-head">
            <span class="eyebrow"><?= esc(lang('Game.slideOf', [$n, $total])) ?></span>
            <?php if ($character !== 'narator'): ?>
              <span class="cine-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></span>
            <?php endif ?>
          </div>
          <?php if ($title !== ''): ?>
            <h3 class="cine-title"><?= esc($title) ?></h3>
          <?php endif ?>
          <p class="slide-text"><?= esc($text) ?></p>
          <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
            <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
          <?php endif ?>
          <?= component('partials/slide-nav', $open ? [
              'n'          => $n,
              'total'      => $total,
              'prefix'     => $prefix,
              'finalUrl'   => base_url($level['entry']),
              'final'      => lang('Game.enterRegionName', [$region]),
              'finalAttrs' => isset($level['curtain']) ? curtain_attrs('region', $level['curtain']['text'], $level['curtain']['preload']) : '',
          ] : [
              'n'          => $n,
              'total'      => $total,
              'prefix'     => $prefix,
              'finalUrl'   => $close,
              'final'      => lang('Game.close'),
              'finalIcon'  => 'cross',
              'finalAttrs' => ' data-kenal-close',
          ]) ?>
        </div>
      </li>
    <?php endforeach ?>
  </ol>

  <?= component('narrator-controls') ?>
</section>
