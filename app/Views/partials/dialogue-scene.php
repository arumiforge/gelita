<?php
/**
 * Panggung dialog Jaka & Mbah Kedu (Tahap 3), dipakai dialog pembuka wilayah
 * (`level_open`, game/dialogue.php) dan adegan wilayah tuntas (`level_done`,
 * game/region-done.php).
 *
 * - Dua tokoh berhadapan: Jaka di kiri, Mbah Kedu di kanan; yang berbicara
 *   maju dan terang, yang mendengar mundur dan meredup (CSS :target + :has
 *   tanpa JavaScript; game/dialogue.js menambah .is-speaking/.is-listening).
 * - Setiap baris membawa URL frame pose tokohnya (`data-pose-src`,
 *   character_frame_src(): `char.{jaka|kedu}.{pose}.1`, cadangan `idle`,
 *   kosong bila belum ada gambar). dialogue.js menukar gambar tokoh yang
 *   berbicara; tanpa gambar tokoh tetap monogram. A pose with 2 or more
 *   frames also carries `data-pose-frames`/`-fps`/`-loop`, which dialogue.js
 *   hands to core/sprite.js to play the pose.
 * - A line with a background of its own (`data-bg`, slide_bg_attr()) shows
 *   it over the stage: `data-backdrop="stage"` tells game/narrator.js to
 *   crossfade the layout's scene images (core/backdrop.js). A line without
 *   one shows the region background again. Without JavaScript every line
 *   keeps the region background.
 * - Efek baris (`data-effect`) diputar lapisan efek narrator; audio, mesin
 *   ketik, dan maju otomatis oleh game/narrator.js mode `tap`, yang juga
 *   satu-satunya pengirim `dialogue_advanced` (context = $context).
 * - Kartu ketuk berjudul ($tap) menutupi panggung sampai diketuk.
 *
 * `$cinematic` (the region dialogue, owner 19 October 2026; the region-done
 * scene too since 21 October 2026) borrows the
 * intro's pieces (full-size HUD, narrator controls with "Lanjut", the last
 * line's main button centred on the page) but keeps the parchment box
 * (owner follow-up, same day): its first row holds the speaker's name, the
 * page dots (no "Bagian n dari N" text), and, at the right, the narrator
 * controls; the two characters stand at the stage's left and right edges.
 * The box's own Kembali/Lanjut links stay as the no-JavaScript fallback.
 * Without it the box keeps partials/slide-nav (no page uses that since 21
 * October 2026).
 *
 * @var App\Entities\Level         $level
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 * @var string                     $context  `level_open` | `level_done`
 * @var string                     $screen   nilai data-screen
 * @var array<string, mixed>       $tap      eyebrow, title, label, variant, trail
 * @var array<string, mixed>       $final    url, label, attrs?, icon?, extra?, plate? (cinematic: plate_attrs() kind)
 * @var string                     $eyebrow  eyebrow kepala layar
 * @var list<string|null>|null     $nextAssets images of the next screen (next_assets_attr())
 * @var bool|null                  $cinematic intro-like layout (see above)
 */
$total     = count($slides);
$first     = (string) ($slides[0]['character_code'] ?? 'jaka');
$region    = $level->text('name', $locale);
$cinematic = ! empty($cinematic);
?>
<section class="story dialogue narrator<?= $cinematic ? ' cine dialogue-cine' : '' ?>" data-screen="<?= esc($screen, 'attr') ?>"
         data-narrator data-context="<?= esc($context, 'attr') ?>" data-prefix="line-" data-mode="tap"
         data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>"
         data-first="<?= esc($first, 'attr') ?>" data-index="1" data-backdrop="stage"<?= screen_fx_attr() ?><?= next_assets_attr($nextAssets ?? []) ?>>
  <?php if ($cinematic): ?>
    <h1 class="visually-hidden"><?= esc($region) ?></h1>
  <?php else: ?>
    <header class="screen-head dialogue-head stage-box">
      <span class="eyebrow"><?= esc($eyebrow) ?></span>
      <h1><?= esc($region) ?></h1>
    </header>
  <?php endif ?>

  <?php if ($slides === []): ?>
    <div class="dialogue-stage" aria-hidden="true">
      <?= component('character', ['character' => 'jaka', 'showName' => true, 'class' => 'stage-left']) ?>
      <?= component('character', ['character' => 'mbah_kedu', 'showName' => true, 'class' => 'stage-right']) ?>
    </div>
    <div class="panel-parchment story-empty">
      <p><?= esc(lang('Game.dialogueEmpty')) ?></p>
      <div class="btn-row btn-row-center">
        <?php foreach ($final['extra'] ?? [] as $button): ?>
          <a class="btn btn-ghost btn-lg" href="<?= esc($button['href'], 'attr') ?>"<?= $button['attrs'] ?? '' ?>><?php if (! empty($button['icon'])): ?><?= icon($button['icon']) ?> <?php endif ?><?= esc($button['label']) ?></a>
        <?php endforeach ?>
        <a class="btn btn-primary btn-lg" href="<?= esc($final['url'], 'attr') ?>"<?= $final['attrs'] ?? '' ?>><?= esc($final['label']) ?> <?= icon('right') ?></a>
      </div>
    </div>
  <?php else: ?>
    <div class="narrator-fx" data-narrator-fx aria-hidden="true"></div>
    <?php if ($cinematic): ?>
      <div class="dialogue-ambience" aria-hidden="true"></div>
    <?php endif ?>

    <?php // No nameplates under the figures: the box names the speaker (owner, 21 October 2026) ?>
    <div class="dialogue-stage" aria-hidden="true">
      <?= component('character', ['character' => 'jaka', 'class' => 'stage-left']) ?>
      <?= component('character', ['character' => 'mbah_kedu', 'class' => 'stage-right']) ?>
    </div>

    <ol class="slides dialogue-lines">
      <?php foreach ($slides as $index => $slide): ?>
        <?php
        $n         = $index + 1;
        $character = (string) ($slide['character_code'] ?? 'jaka');
        $pose      = (string) ($slide['pose'] ?? '') ?: 'idle';
        $effect    = (string) ($slide['effect'] ?? '');
        $text      = tr($slide, 'text', $locale);
        $audioId   = (int) ($locale === 'en' ? ($slide['audio_en_asset_id'] ?? 0) : ($slide['audio_id_asset_id'] ?? 0));
        ?>
        <li class="slide dialogue-line" id="line-<?= $n ?>" data-index="<?= $n ?>" data-character="<?= esc($character, 'attr') ?>"
            data-pose="<?= esc($pose, 'attr') ?>" data-pose-src="<?= esc(character_frame_src($character, $pose) ?? '', 'attr') ?>"<?= character_frames_attr(character_frames($character, $pose), 'data-pose-') ?><?= slide_bg_attr($slide) ?>
            <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
            aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
          <?php if ($cinematic): ?>
            <div class="dialogue-box panel-parchment dialogue-caption" data-narrator-advance>
              <div class="dialogue-caption-head">
                <cite class="dialogue-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></cite>
                <nav class="dialogue-progress" aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
                  <ol class="slide-dots">
                    <?php for ($i = 1; $i <= $total; $i++): ?>
                      <li>
                        <a class="slide-dot<?= $i === $n ? ' is-current' : '' ?>" href="#line-<?= $i ?>"
                           <?= $i === $n ? 'aria-current="step"' : '' ?>><span class="visually-hidden"><?= esc(lang('Game.slideOf', [$i, $total])) ?></span></a>
                      </li>
                    <?php endfor ?>
                  </ol>
                </nav>
                <?php if ($total > 1): ?>
                  <div class="slide-nav dialogue-step">
                    <?php if ($n > 1): ?>
                      <a class="btn btn-quiet" href="#line-<?= $n - 1 ?>"><?= icon('left') ?> <?= esc(lang('Game.back')) ?></a>
                    <?php endif ?>
                    <?php if ($n < $total): ?>
                      <a class="btn btn-primary" href="#line-<?= $n + 1 ?>"><?= esc(lang('Game.continue')) ?> <?= icon('right') ?></a>
                    <?php endif ?>
                  </div>
                <?php endif ?>
              </div>
              <div class="dialogue-body stage-scroll">
                <p class="dialogue-text slide-text"><?= esc($text) ?></p>
                <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
                  <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
                <?php endif ?>
              </div>
            </div>

            <?php if ($n === $total): ?>
              <div class="cine-final">
                <?php foreach ($final['extra'] ?? [] as $button): ?>
                  <a class="btn btn-ghost btn-lg" href="<?= esc($button['href'], 'attr') ?>"<?= $button['attrs'] ?? '' ?>><?php if (! empty($button['icon'])): ?><?= icon($button['icon']) ?> <?php endif ?><?= esc($button['label']) ?></a>
                <?php endforeach ?>
                <?php if (! empty($final['plate'])): ?>
                  <a <?= plate_attrs($final['plate'], 'is-final') ?> href="<?= esc($final['url'], 'attr') ?>" data-slide-final<?= $final['attrs'] ?? '' ?>><span><?= esc($final['label']) ?></span></a>
                <?php else: ?>
                  <a class="btn btn-primary btn-lg" href="<?= esc($final['url'], 'attr') ?>" data-slide-final<?= $final['attrs'] ?? '' ?>><?= esc($final['label']) ?></a>
                <?php endif ?>
              </div>
            <?php endif ?>
          <?php else: ?>
          <div class="dialogue-box panel-parchment" data-narrator-advance>
            <cite class="dialogue-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></cite>
            <div class="dialogue-body stage-scroll">
              <p class="dialogue-text slide-text"><?= esc($text) ?></p>
              <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
                <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
              <?php endif ?>
            </div>
            <?= component('partials/slide-nav', [
                'n'          => $n,
                'total'      => $total,
                'prefix'     => 'line-',
                'finalUrl'   => $final['url'],
                'final'      => $final['label'],
                'finalAttrs' => $final['attrs'] ?? '',
                'finalIcon'  => $final['icon'] ?? 'right',
                'extra'      => $final['extra'] ?? [],
            ]) ?>
          </div>
          <?php endif ?>
        </li>
      <?php endforeach ?>
    </ol>

    <?= component('narrator-controls', ['nextLabel' => $cinematic]) ?>
    <?= component('narrator-tap', $tap) ?>
  <?php endif ?>
</section>
