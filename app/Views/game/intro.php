<?php
/**
 * 6. Intro — `/intro` → HomeController::intro
 *
 * Slide cerita pembuka (dialogues context `intro`), satu per layar.
 * Perpindahan slide memakai jangkar #slide-n dan CSS :target, sehingga tetap
 * berjalan tanpa JavaScript; game/intro.js memakai hash yang sama lewat
 * location.replace() (riwayat tidak bertambah, :target tetap berlaku) dan
 * mengirim event `dialogue_advanced`.
 *
 * Setelah registrasi, halaman diawali kartu sambutan (flash `welcome`).
 * Kode peserta TIDAK ditampilkan di sini — kode itu untuk penelitian.
 *
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 */
$welcome = session('welcome');
$total   = count($slides);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.introTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.intro') ?? media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium story intro" data-screen="intro">
  <?php if (is_string($welcome) && $welcome !== ''): ?>
    <div class="welcome-card" role="status">
      <span class="welcome-card-icon" aria-hidden="true"><?= icon('key') ?></span>
      <div>
        <h2><?= esc(lang('Game.welcomeCardTitle')) ?></h2>
        <p><?= esc($welcome) ?></p>
        <p class="muted"><?= esc(lang('Game.welcomeCardTip')) ?></p>
      </div>
    </div>
  <?php endif ?>

  <h1 class="visually-hidden"><?= esc(lang('Game.introTitle')) ?></h1>

  <?php if ($slides === []): ?>
    <div class="panel-parchment story-empty">
      <p><?= esc(lang('Game.introEmpty')) ?></p>
      <a class="btn btn-primary btn-lg" href="<?= base_url('peta') ?>"><?= esc(lang('Game.startJourney')) ?> <?= icon('right') ?></a>
    </div>
  <?php else: ?>
    <ol class="slides">
      <?php foreach ($slides as $index => $slide): ?>
        <?php
        $n         = $index + 1;
        $character = (string) ($slide['character_code'] ?? 'jaka');
        $title     = tr($slide, 'title', $locale);
        $text      = tr($slide, 'text', $locale);
        $audioId   = (int) ($locale === 'en' ? ($slide['audio_en_asset_id'] ?? 0) : ($slide['audio_id_asset_id'] ?? 0));
        ?>
        <li class="slide" id="slide-<?= $n ?>" data-index="<?= $n ?>" data-character="<?= esc($character, 'attr') ?>"
            aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
          <div class="slide-art">
            <?= component('character', ['character' => $character, 'showName' => true]) ?>
          </div>
          <div class="slide-body panel-parchment">
            <span class="eyebrow"><?= esc(lang('Game.slideOf', [$n, $total])) ?></span>
            <?php if ($title !== ''): ?>
              <h2><?= esc($title) ?></h2>
            <?php endif ?>
            <p class="slide-text"><?= esc($text) ?></p>
            <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
              <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
            <?php endif ?>
            <?= component('partials/slide-nav', [
                'n'        => $n,
                'total'    => $total,
                'prefix'   => 'slide-',
                'finalUrl' => base_url('peta'),
                'final'    => lang('Game.startJourney'),
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
    ['label' => lang('Game.skip'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
