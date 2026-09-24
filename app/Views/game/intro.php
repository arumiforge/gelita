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
$total   = count($slides);
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

    <ol class="slides cine-slides">
      <?php foreach ($slides as $index => $slide): ?>
        <?php
        $n         = $index + 1;
        $character = (string) ($slide['character_code'] ?? 'narator');
        $pose      = (string) ($slide['pose'] ?? '') ?: 'idle';
        $effect    = (string) ($slide['effect'] ?? '');
        $title     = tr($slide, 'title', $locale);
        $text      = tr($slide, 'text', $locale);
        $audioId   = (int) ($locale === 'en' ? ($slide['audio_en_asset_id'] ?? 0) : ($slide['audio_id_asset_id'] ?? 0));
        $bgId      = (int) ($slide['background_media_id'] ?? 0);
        ?>
        <li class="slide cine-slide<?= $character === 'narator' ? ' is-narrator' : '' ?>" id="slide-<?= $n ?>" data-index="<?= $n ?>"
            data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"
            <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
            aria-label="<?= esc(lang('Game.slideOf', [$n, $total]), 'attr') ?>">
          <?php if (media_exists($bgId)): ?>
            <img class="cine-bg" src="<?= esc(media_src($bgId), 'attr') ?>" alt="" <?= $n === 1 ? 'fetchpriority="high"' : 'loading="lazy"' ?>>
          <?php endif ?>
          <?php if ($character !== 'narator'): ?>
            <div class="cine-stage">
              <?= component('character', ['character' => $character, 'pose' => $pose, 'class' => 'pose-' . $pose]) ?>
            </div>
          <?php endif ?>
          <div class="cine-caption" data-narrator-advance>
            <div class="cine-caption-head">
              <span class="eyebrow"><?= esc(lang('Game.slideOf', [$n, $total])) ?></span>
              <?php if ($character !== 'narator'): ?>
                <span class="cine-speaker"><?= esc(lang_or('Game.char_' . $character, $character)) ?></span>
              <?php endif ?>
            </div>
            <?php if ($title !== ''): ?>
              <h2 class="cine-title"><?= esc($title) ?></h2>
            <?php endif ?>
            <p class="slide-text"><?= esc($text) ?></p>
            <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
              <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
            <?php endif ?>
            <?= component('partials/slide-nav', [
                'n'        => $n,
                'total'    => $total,
                'prefix'   => 'slide-',
                'finalUrl' => base_url('intro/selesai'),
                'final'    => lang('Game.startJourney'),
            ]) ?>
          </div>
        </li>
      <?php endforeach ?>
    </ol>

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
