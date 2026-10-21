<?php
/**
 * Tentang GELITA — `/tentang` → HomeController::about (owner, 21 October 2026)
 *
 * The game in plain words for children: what GELITA is, the story, how to
 * play, and what is inside. Opened from the HUD menu on every page, before
 * and after login. Layout as Mulai: the gate picture (`assets/ui/bg-mulai.webp`)
 * under a deep dark layer (`body.is-dim-art`), the logo in the HUD centre, Back
 * on the navy plate; four cards in a 2 × 2 grid, each on one of the owner's
 * round gold buttons.
 *
 * @var string                    $back        where Back leads (the page the student came from)
 * @var array<string, mixed>|null $participant signed-in student for the HUD menu
 */
$cards = [
    ['icon' => 'info', 'title' => 'aboutWhatTitle', 'text' => ['aboutWhatText']],
    ['icon' => 'play', 'title' => 'aboutStoryTitle', 'text' => ['aboutStoryText']],
    ['icon' => 'help', 'title' => 'aboutPlayTitle', 'list' => ['aboutPlay1', 'aboutPlay2', 'aboutPlay3']],
    ['icon' => 'map', 'title' => 'aboutFeaturesTitle', 'list' => ['aboutFeature1', 'aboutFeature2', 'aboutFeature3', 'aboutFeature4']],
];
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('music') ?>welcome<?= $this->endSection() ?>
<?= $this->section('title') ?><?= esc(lang('Game.about')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= asset_url_versioned('ui/bg-mulai.webp') ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-dim-art<?= $this->endSection() ?>

<?php // Escaped by components/hud-menu, like every page's hudBack ?>
<?= $this->section('hudBack') ?><?= $back ?><?= $this->endSection() ?>
<?= $this->section('hudLayout') ?>logo-centre<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="about" aria-labelledby="about-title">
  <div class="about-panel stage-box stage-scroll">
    <header class="about-head">
      <h1 id="about-title"><?= esc(lang('Game.about')) ?></h1>
      <p><?= esc(lang('Game.aboutLead')) ?></p>
    </header>

    <div class="about-cards">
      <?php foreach ($cards as $card): ?>
        <article class="about-card">
          <?= round_icon($card['icon'], 'about-icon') ?>
          <div class="about-body">
            <h2><?= esc(lang('Game.' . $card['title'])) ?></h2>
            <?php foreach ($card['text'] ?? [] as $key): ?>
              <p><?= esc(lang('Game.' . $key)) ?></p>
            <?php endforeach ?>
            <?php if (isset($card['list'])): ?>
              <ul>
                <?php foreach ($card['list'] as $key): ?>
                  <li><?= esc(lang('Game.' . $key)) ?></li>
                <?php endforeach ?>
              </ul>
            <?php endif ?>
          </div>
        </article>
      <?php endforeach ?>
    </div>
  </div>
</section>
<?= $this->endSection() ?>
