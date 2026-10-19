<?php
/**
 * 16a. Penutup — `/penutup` → DialogueController::ending
 *
 * Penutup cerita sinematik (Tahap 3), seperti cerita pembuka: lima slide
 * `ending` (naskah §6) satu per layar penuh (partials/cine-slides), tokoh
 * sesuai pose, efek layar, audio narasi, dan kartu "Ketuk untuk mulai"
 * (game/narrator.js mode `tap`, event `dialogue_advanced` context `ending`).
 * Hanya terbuka setelah seluruh node tuntas; boleh ditonton ulang lewat
 * "Tonton penutup" di Peta Kedu. Slide akhir → Balai Refleksi.
 * Tanpa JavaScript slide berpindah lewat #slide-n + :target.
 *
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 */
// The page starts on slide 1's own picture, so nothing else shows before it;
// the story background is the default for slides without one (core/backdrop.js)
$defaultBg = media_key_src('bg.intro') ?? media_key_src('bg.welcome') ?? '';
$firstBg   = $slides === [] ? null : slide_bg_src($slides[0]);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.endingTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $firstBg ?? $defaultBg ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic<?= $this->endSection() ?>
<?php /* Full-size HUD (big logo and controls); its centre holds the slide progress, so no shard pill */ ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('hudShards') ?>off<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="story ending cine narrator" data-screen="ending"
         data-narrator data-context="ending" data-prefix="slide-" data-mode="tap"<?= screen_fx_attr() ?>
         data-backdrop="stage" data-backdrop-mode="dip" data-bg-default="<?= esc($defaultBg, 'attr') ?>">
  <h1 class="visually-hidden"><?= esc(lang('Game.endingTitle')) ?></h1>

  <?php if ($slides === []): ?>
    <div class="panel-parchment story-empty">
      <p><?= esc(lang('Game.endingEmpty')) ?></p>
      <a class="btn btn-primary btn-lg" href="<?= base_url('refleksi') ?>"><?= icon('sparkle') ?> <?= esc(lang('Game.reflection')) ?></a>
    </div>
  <?php else: ?>
    <div class="narrator-fx" data-narrator-fx aria-hidden="true"></div>

    <?= component('partials/cine-slides', [
        'slides'   => $slides,
        'locale'   => $locale,
        'prefix'   => 'slide-',
        'finalUrl' => base_url('refleksi'),
        'final'    => lang('Game.reflection'),
    ]) ?>

    <?= component('narrator-controls', ['nextLabel' => true]) ?>
    <?= component('narrator-tap') ?>
  <?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.skip'), 'href' => base_url('refleksi'), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
