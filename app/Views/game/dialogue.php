<?php
/**
 * 8. Dialog — `/dialog/{code}` → DialogueController::show
 *
 * Dua tokoh berhadapan: yang berbicara maju dan terang, yang mendengar mundur
 * dan meredup. Tokoh yang berbicara ditentukan CSS dari baris yang sedang
 * ditampilkan (:target + :has), jadi tanpa JavaScript pun tetap benar;
 * dialogue.js (tahap 6) menambah kelas .is-speaking / .is-listening, pintasan
 * Spasi/panah kanan, dan event `dialogue_advanced`.
 *
 * @var App\Entities\Level         $level
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 */
$total  = count($slides);
$first  = (string) ($slides[0]['character_code'] ?? 'jaka');
$bg     = media_exists($level->background_media_id) ? media_src($level->background_media_id) : '';
$region = $level->text('name', $locale);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($region) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $bg ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium story dialogue" data-screen="dialogue"
         data-level="<?= esc($level->code, 'attr') ?>" data-level-id="<?= esc($level->id, 'attr') ?>"
         data-first="<?= esc($first, 'attr') ?>" data-index="1">
  <header class="screen-head">
    <span class="eyebrow"><?= esc(lang('Game.levelOrder', [$level->sequence])) ?></span>
    <h1><?= esc($region) ?></h1>
  </header>

  <div class="dialogue-stage" aria-hidden="true">
    <?= component('character', ['character' => 'jaka', 'showName' => true, 'class' => 'stage-left']) ?>
    <?= component('character', ['character' => 'mbah_kedu', 'showName' => true, 'class' => 'stage-right']) ?>
  </div>

  <?php if ($slides === []): ?>
    <div class="panel-parchment story-empty">
      <p><?= esc(lang('Game.dialogueEmpty')) ?></p>
      <a class="btn btn-primary btn-lg" href="<?= base_url('wilayah/' . $level->code) ?>"><?= esc(lang('Game.enterRegion')) ?> <?= icon('right') ?></a>
    </div>
  <?php else: ?>
    <ol class="slides dialogue-lines">
      <?php foreach ($slides as $index => $slide): ?>
        <?php
        $n         = $index + 1;
        $character = (string) ($slide['character_code'] ?? 'jaka');
        $text      = tr($slide, 'text', $locale);
        $audioId   = (int) ($locale === 'en' ? ($slide['audio_en_asset_id'] ?? 0) : ($slide['audio_id_asset_id'] ?? 0));
        ?>
        <li class="slide dialogue-line" id="line-<?= $n ?>" data-index="<?= $n ?>" data-character="<?= esc($character, 'attr') ?>">
          <div class="dialogue-box panel-parchment">
            <cite class="dialogue-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></cite>
            <p class="dialogue-text"><?= esc($text) ?></p>
            <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
              <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
            <?php endif ?>
            <?= component('partials/slide-nav', [
                'n'        => $n,
                'total'    => $total,
                'prefix'   => 'line-',
                'finalUrl' => base_url('wilayah/' . $level->code),
                'final'    => lang('Game.enterRegion'),
            ]) ?>
          </div>
        </li>
      <?php endforeach ?>
    </ol>
  <?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left'],
    ['label' => lang('Game.skip'), 'href' => base_url('wilayah/' . $level->code), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
