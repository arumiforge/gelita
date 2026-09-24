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
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.endingTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.intro') ?? media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen story ending cine narrator" data-screen="ending"
         data-narrator data-context="ending" data-prefix="slide-" data-mode="tap">
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

    <?= component('narrator-controls') ?>
    <?= component('narrator-tap') ?>
  <?php endif ?>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.skip'), 'href' => base_url('refleksi'), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
