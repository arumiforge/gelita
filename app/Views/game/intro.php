<?php
/**
 * 6. Intro — `/intro` → GateController::intro
 *
 * Cerita pembuka sinematik (dialogues context `intro`), satu slide per
 * layar penuh: latar per slide dengan Ken Burns pelan
 * (`dialogues.background_media_id`, cadangan `bg.intro` lalu `bg.welcome`
 * lewat latar layout), tokoh sesuai pose (narator tanpa gambar), kotak teks
 * bergaya subtitle berisi judul slide, dan efek layar (`dialogues.effect`).
 *
 * Pemutar: game/narrator.js mode `tap`. Kartu "Ketuk untuk mulai" membuka
 * kunci audio, lalu narasi tiap slide (`audio_id_asset_id` /
 * `audio_en_asset_id`, hanya yang disetujui — audio_src()) diputar otomatis
 * dan slide maju sendiri bila sakelar Otomatis menyala. Tanpa audio yang
 * tersedia, teks tetap tampil dan slide dilanjutkan manual.
 *
 * Slide terakhir → `/intro/selesai` (isi `intro_seen_at`, lalu peta dengan
 * tirai). Pemain yang belum pernah menonton sampai selesai wajib menonton:
 * "Lewati" (lewat `/gerbang/peta`, agar tirai peta ikut tampil) hanya
 * dirender bila `canSkip`; tiap slide tetap dapat dilanjut.
 * Perpindahan slide memakai jangkar #slide-n dan CSS :target, sehingga tetap
 * berjalan tanpa JavaScript (kartu ketuk dan kontrol narasi tersembunyi).
 *
 * Setelah registrasi, halaman diawali kartu sambutan (flash `welcome`); JS
 * memindahkannya ke kartu ketuk. Kode peserta TIDAK ditampilkan di sini —
 * kode itu untuk penelitian.
 *
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 * @var bool                       $canSkip sudah pernah menonton (intro_seen_at terisi)
 */
$welcome = session('welcome');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.introTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.intro') ?? media_key_src('bg.welcome') ?? '' ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen story intro cine narrator" data-screen="intro"
         data-narrator data-context="intro" data-prefix="slide-" data-mode="tap">
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
      <a class="btn btn-primary btn-lg" href="<?= base_url('intro/selesai') ?>"><?= esc(lang('Game.startJourney')) ?> <?= icon('right') ?></a>
    </div>
  <?php else: ?>
    <div class="narrator-fx" data-narrator-fx aria-hidden="true"></div>

    <?= component('partials/cine-slides', [
        'slides'   => $slides,
        'locale'   => $locale,
        'prefix'   => 'slide-',
        'finalUrl' => base_url('intro/selesai'),
        'final'    => lang('Game.startJourney'),
    ]) ?>

    <?= component('narrator-controls') ?>
    <?= component('narrator-tap') ?>
  <?php endif ?>
</section>
<?= $this->endSection() ?>

<?php if ($canSkip): ?>
<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.skip'), 'href' => base_url('gerbang/peta'), 'style' => 'quiet', 'arrow' => 'right'],
]]) ?>
<?= $this->endSection() ?>
<?php endif ?>
