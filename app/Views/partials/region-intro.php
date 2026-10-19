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
 * A slide with a background of its own (`data-bg`) crossfades it over the
 * region background (core/backdrop.js, through game/narrator.js); a slide
 * without one shows the region background again.
 *
 * Wilayah terkunci boleh dikenali. Slide akhir: "Masuk ke {wilayah}" (tirai
 * wilayah, ke `entry`) bila wilayah terbuka, selain itu "Tutup".
 *
 * Layout (owner, 19 October 2026), stage coordinates in dp:
 * - `.kenal-head`: a 120 dp band like the HUD. Back (leaves the overlay,
 *   replaces the old cross) at the left, the GELITA logo in the middle, the
 *   narrator controls at the right (cinematic.css);
 * - `.kenal-title`: "Mengenal {wilayah}" centred under the band, not in it;
 * - `.kenal-caption`: speaker and title share one row, with the page dots and
 *   Lanjut at the right corner. No "Wilayah n · tingkat" and no "Bagian n dari
 *   N" text; the slide's aria-label and the dots still say which part it is;
 * - `.kenal-avatar`: the speaker's round avatar (`ui.kedu-avatar` for Mbah
 *   Kedu, `ui.jaka-avatar` for Jaka, else an initial) sits on top of the
 *   caption's left end; the caption starts under its centre, so the avatar's
 *   circle is the visible left edge. Never an animation frame.
 *
 * @var array<string, mixed>       $level  baris levelOverview() + `curtain`
 * @var list<array<string, mixed>> $slides dialogues region_intro wilayah itu
 * @var string                     $locale
 */
$code    = (string) $level['code'];
$region  = (string) $level['name'];
$prefix  = 'kenal-' . $code . '-';
$total   = count($slides);
$open    = $level['status'] !== 'locked';
$close   = '#region-' . $code;
$logo    = media_key_src('ui.logo-hero') ?? media_key_src('ui.logo');
$avatars = ['mbah_kedu' => media_key_src('ui.kedu-avatar'), 'jaka' => media_key_src('ui.jaka-avatar')];
?>
<section class="kenal narrator" id="kenal-<?= esc($code, 'attr') ?>" role="dialog" aria-modal="true"
         aria-labelledby="kenal-<?= esc($code, 'attr') ?>-title"
         data-kenal="<?= esc($code, 'attr') ?>" data-narrator data-context="region_intro"
         data-prefix="<?= esc($prefix, 'attr') ?>" data-mode="external" data-keyboard="0" data-hash="0"
         data-level-id="<?= esc($level['id'], 'attr') ?>"<?= screen_fx_attr() ?>>
  <?php // Two pictures: a slide's own background (data-bg) crossfades over the region one (core/backdrop.js) ?>
  <?php if (! empty($level['background'])): ?>
    <img class="kenal-bg is-visible" src="<?= esc($level['background'], 'attr') ?>" alt="" loading="lazy">
  <?php else: ?>
    <img class="kenal-bg" alt="" loading="lazy">
  <?php endif ?>
  <img class="kenal-bg" alt="" loading="lazy">
  <header class="kenal-head">
    <a class="btn btn-quiet kenal-back" href="<?= esc($close, 'attr') ?>" data-kenal-close><?= icon('left') ?> <span><?= esc(lang('Game.back')) ?></span></a>
    <?php if ($logo !== null): ?>
      <img class="kenal-logo" src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>" decoding="async" draggable="false">
    <?php else: ?>
      <span class="hud-brand kenal-logo">GELITA</span>
    <?php endif ?>
  </header>
  <h2 class="kenal-title" id="kenal-<?= esc($code, 'attr') ?>-title"><?= esc(lang('Game.kenalTitle', [$region])) ?></h2>

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
          <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?><?= slide_bg_attr($slide) ?>
          aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
        <div class="kenal-caption" data-narrator-advance>
          <?php if ($character !== 'narator'): ?>
            <div class="kenal-avatar">
              <?= component('partials/kedu-portrait', ['src' => $avatars[$character] ?? null, 'name' => lang_or('Game.char_' . $character, $character)]) ?>
            </div>
          <?php endif ?>
          <div class="cine-caption-head">
            <?php if ($character !== 'narator'): ?>
              <span class="cine-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></span>
            <?php endif ?>
            <?php if ($title !== ''): ?>
              <h3 class="cine-title"><?= esc($title) ?></h3>
            <?php endif ?>
            <?= component('partials/slide-nav', $open ? [
                'n'          => $n,
                'total'      => $total,
                'prefix'     => $prefix,
                'finalUrl'   => base_url($level['entry']),
                'final'      => lang('Game.enterRegionName', [$region]),
                'finalAttrs' => region_curtain_attrs($level['curtain'] ?? null),
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
          <p class="slide-text"><?= esc($text) ?></p>
          <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
            <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
          <?php endif ?>
        </div>
      </li>
    <?php endforeach ?>
  </ol>

  <?= component('narrator-controls') ?>
</section>
