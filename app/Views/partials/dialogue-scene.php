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
 *   berbicara; tanpa gambar tokoh tetap monogram.
 * - Efek baris (`data-effect`) diputar lapisan efek narrator; audio, mesin
 *   ketik, dan maju otomatis oleh game/narrator.js mode `tap`, yang juga
 *   satu-satunya pengirim `dialogue_advanced` (context = $context).
 * - Kartu ketuk berjudul ($tap) menutupi panggung sampai diketuk.
 *
 * @var App\Entities\Level         $level
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 * @var string                     $context  `level_open` | `level_done`
 * @var string                     $screen   nilai data-screen
 * @var array<string, mixed>       $tap      eyebrow, title, label, variant, trail
 * @var array<string, mixed>       $final    url, label, attrs?, icon?, extra?
 * @var string                     $eyebrow  eyebrow kepala layar
 */
$total  = count($slides);
$first  = (string) ($slides[0]['character_code'] ?? 'jaka');
$region = $level->text('name', $locale);
?>
<section class="screen screen-medium story dialogue narrator" data-screen="<?= esc($screen, 'attr') ?>"
         data-narrator data-context="<?= esc($context, 'attr') ?>" data-prefix="line-" data-mode="tap"
         data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>"
         data-first="<?= esc($first, 'attr') ?>" data-index="1">
  <header class="screen-head">
    <span class="eyebrow"><?= esc($eyebrow) ?></span>
    <h1><?= esc($region) ?></h1>
  </header>

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

    <div class="dialogue-stage" aria-hidden="true">
      <?= component('character', ['character' => 'jaka', 'showName' => true, 'class' => 'stage-left']) ?>
      <?= component('character', ['character' => 'mbah_kedu', 'showName' => true, 'class' => 'stage-right']) ?>
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
            data-pose="<?= esc($pose, 'attr') ?>" data-pose-src="<?= esc(character_frame_src($character, $pose) ?? '', 'attr') ?>"
            <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
            aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
          <div class="dialogue-box panel-parchment" data-narrator-advance>
            <cite class="dialogue-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></cite>
            <p class="dialogue-text slide-text"><?= esc($text) ?></p>
            <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
              <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
            <?php endif ?>
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
        </li>
      <?php endforeach ?>
    </ol>

    <?= component('narrator-controls') ?>
    <?= component('narrator-tap', $tap) ?>
  <?php endif ?>
</section>
