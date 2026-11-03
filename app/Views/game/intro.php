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
 * Layout (owner, 18 October 2026): full-size HUD (big logo and controls)
 * without the shard pill; the page dots centred in the HUD; Lewati under the
 * menu button; a full-width caption along the bottom whose title row holds
 * the narrator controls (▶ reads "Lanjut"); "Mulai petualangan" centred on
 * the page. See partials/cine-slides. Owner, 20 October 2026: no "Bagian n
 * dari N" text, no band behind the caption or the controls, and the slide
 * picture stays dark until the tap card is tapped (cinematic.css).
 *
 * Right after sign-up the page carries the welcome card (flash `welcome`);
 * game/intro.js opens it as a modal over the tap card (owner, 19 October
 * 2026), and Lanjut leads into the intro. Without JavaScript it stays above
 * the first slide. Kode peserta TIDAK ditampilkan di sini — kode itu untuk
 * penelitian.
 *
 * @var list<array<string, mixed>> $slides
 * @var string                     $locale
 * @var bool                       $canSkip sudah pernah menonton (intro_seen_at terisi)
 */
$welcome = session('welcome');
// The page starts on slide 1's own picture, so nothing else shows before it;
// the story background is the default for slides without one (core/backdrop.js)
$defaultBg = media_key_src('bg.intro') ?? media_key_src('bg.welcome') ?? '';
$firstBg   = $slides === [] ? null : slide_bg_src($slides[0]);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>opening<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.introTitle')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= $firstBg ?? $defaultBg ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-cinematic<?= $this->endSection() ?>
<?php /* Full-size HUD (big logo and controls); its centre holds the slide progress, so no shard pill */ ?>
<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>
<?= $this->section('hudShards') ?>off<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php /* Next screen: Peta Kedu behind the opening map curtain */ ?>
<section class="story intro cine narrator" data-screen="intro"<?= next_assets_attr([media_key_src('bg.loading'), media_key_src('map.kedu'), media_key_src('bg.map')]) ?>
         data-narrator data-context="intro" data-prefix="slide-" data-mode="tap"<?= screen_fx_attr() ?>
         data-backdrop="stage" data-backdrop-mode="dip" data-bg-default="<?= esc($defaultBg, 'attr') ?>">
  <?php if (is_string($welcome) && $welcome !== ''): ?>
    <div class="welcome-card" role="status">
      <span class="welcome-card-icon" aria-hidden="true"><?= icon('key') ?></span>
      <div>
        <h2><?= esc(lang('Game.welcomeCardTitle')) ?></h2>
        <p><?= esc($welcome) ?></p>
        <p class="muted"><?= esc(lang('Game.welcomeCardTip')) ?></p>
        <?php $researchCode = session('research_code') ?>
        <?php if (is_string($researchCode) && $researchCode !== ''): ?>
          <?php /* Field mode (28 §6.5, M-11): the big research code card */ ?>
          <p class="welcome-research-code"><?= esc(lang('Game.researchCodeLabel')) ?>
            <strong><?= esc($researchCode) ?></strong>
            <small><?= esc(lang('Game.researchCodeHelp')) ?></small></p>
        <?php endif ?>
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
        // The owner's mountain plate (21 October 2026)
        'finalPlate' => 'enter',
    ]) ?>

    <?= component('narrator-controls', ['nextLabel' => true]) ?>
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
