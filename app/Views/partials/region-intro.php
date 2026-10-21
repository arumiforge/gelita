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
 * Music: the region's Kenali track (`data-music`, game/map.js) while the
 * overlay is open, Peta Kedu's track again when it closes.
 *
 * Wilayah terkunci boleh dikenali. Slide akhir: "Masuk ke {wilayah}" (tirai
 * wilayah, ke `entry`) bila wilayah terbuka, selain itu "Tutup".
 *
 * Layout (owner, 19 October 2026), stage coordinates in dp:
 * - `.kenal-head`: a 120 dp band like the HUD. Back (leaves the overlay,
 *   replaces the old cross) at the left, the title in the middle, the
 *   narrator controls at the right, each on the HUD's round
 *   `ui.result-hud-control` skin with no band behind them (cinematic.css);
 * - `.kenal-heading`: "Mengenal {wilayah}" in gold Marcellus letters over a
 *   gold rule with an ornament in its middle. Since 21 October 2026 (owner)
 *   it sits in the band where the GELITA logo was; the logo is gone;
 * - `.kenal-caption`: the title shares one row with the page dots and Lanjut
 *   at the right corner; caption and avatar sit at the bottom of the stage.
 *   No "Wilayah n · tingkat" and no "Bagian n dari N" text; the slide's
 *   aria-label and the dots still say which part it is;
 * - `.kenal-avatar`: the speaker's round avatar (`ui.kedu-avatar` for Mbah
 *   Kedu, `ui.jaka-avatar` for Jaka, both in a gold ring, else an initial) on
 *   the caption's left end, with the speaker's name on a navy nameplate over
 *   the ring's lower part. Never an animation frame;
 * - Back and the previous-slide Kembali sit on the owner's navy plate, Lanjut
 *   and the final button on the gold plate (`assets/ui/kenal-plate-*.webp`,
 *   Kenali only; other pages keep `ui.result-button-primary`).
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
$avatars = ['mbah_kedu' => media_key_src('ui.kedu-avatar'), 'jaka' => media_key_src('ui.jaka-avatar')];
// Skins as custom properties (absolute URLs, so the minified mirror and the asset package keep them)
$skins = [
    'kenal-plate-gold' => asset_url_versioned('ui/kenal-plate-gold.webp'),
    'kenal-plate-navy' => asset_url_versioned('ui/kenal-plate-navy.webp'),
    'skin-hud'         => media_key_src('ui.result-hud-control'),
];
$style = [];

foreach ($skins as $name => $src) {
    if ($src !== null) {
        $style[] = '--' . $name . ': url("' . str_replace(['"', '\\', "\n"], ['%22', '%5C', ''], $src) . '")';
    }
}
?>
<section class="kenal narrator" id="kenal-<?= esc($code, 'attr') ?>" role="dialog" aria-modal="true"
         aria-labelledby="kenal-<?= esc($code, 'attr') ?>-title"
         data-kenal="<?= esc($code, 'attr') ?>" data-narrator data-context="region_intro"
         data-prefix="<?= esc($prefix, 'attr') ?>" data-mode="external" data-keyboard="0" data-hash="0"
         data-level-id="<?= esc($level['id'], 'attr') ?>" data-music="<?= esc('kenal-' . $code, 'attr') ?>" style="<?= esc(implode('; ', $style), 'attr') ?>"<?= screen_fx_attr() ?>>
  <?php // Two pictures: a slide's own background (data-bg) crossfades over the region one (core/backdrop.js) ?>
  <?php if (! empty($level['background'])): ?>
    <img class="kenal-bg is-visible" src="<?= esc($level['background'], 'attr') ?>" alt="" loading="lazy">
  <?php else: ?>
    <img class="kenal-bg" alt="" loading="lazy">
  <?php endif ?>
  <img class="kenal-bg" alt="" loading="lazy">
  <header class="kenal-head">
    <a class="btn btn-quiet kenal-back" href="<?= esc($close, 'attr') ?>" data-kenal-close><?= icon('left') ?> <span><?= esc(lang('Game.back')) ?></span></a>
  <div class="kenal-heading">
    <h2 class="kenal-title" id="kenal-<?= esc($code, 'attr') ?>-title"><?= esc(lang('Game.kenalTitle', [$region])) ?></h2>
    <span class="kenal-rule" aria-hidden="true">
      <svg class="kenal-ornament" viewBox="0 0 64 30" focusable="false">
        <path class="kenal-ornament-fill" d="M32 1C36.5 7 37.5 13 32 20 26.5 13 27.5 7 32 1ZM30.5 19.5C24 18 19 13 17.5 6.5 23.5 8.5 28.5 12.5 30.5 19.5ZM33.5 19.5C40 18 45 13 46.5 6.5 40.5 8.5 35.5 12.5 33.5 19.5ZM19 20.5Q32 26 45 20.5 32 23 19 20.5ZM32 23.5 34.5 26.5 32 29.5 29.5 26.5Z"/>
        <path class="kenal-ornament-line" d="M18 21C11 22 7 17 10.5 13.5 13 11 16.5 13 15 15.5M46 21C53 22 57 17 53.5 13.5 51 11 47.5 13 49 15.5"/>
      </svg>
    </span>
  </div>
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
          <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?><?= slide_bg_attr($slide) ?>
          aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
        <div class="kenal-caption" data-narrator-advance>
          <?php if ($character !== 'narator'): ?>
            <div class="kenal-avatar">
              <?= component('partials/kedu-portrait', ['src' => $avatars[$character] ?? null, 'name' => lang_or('Game.char_' . $character, $character)]) ?>
              <span class="kenal-nameplate"><?= esc(lang_or('Game.char_' . $character, $character)) ?></span>
            </div>
          <?php endif ?>
          <div class="cine-caption-head">
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
