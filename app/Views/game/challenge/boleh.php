<?php
/**
 * Engine `boleh` — kartu pernyataan dengan penilaian Benar / Salah atas satu
 * teks bacaan.
 *
 * Pilihan verdict memakai radio bawaan: dapat dipilih dengan keyboard/sentuhan
 * tanpa JavaScript, dan tidak ada kunci jawaban di markup. Kartu diberi nomor
 * "Pernyataan n" (Dok 2 §12.1).
 *
 * @var App\Entities\ChallengeNode $node
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $payload
 * @var string                     $locale
 */
$items    = $payload['items'] ?? [];
$passages = $payload['passages'] ?? [];
$verdicts = ['benar', 'salah'];
$labels   = ['benar' => 'Game.verdictBenar', 'salah' => 'Game.verdictSalah'];
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="boleh-passages stage-box stage-scroll" id="reading-panel">
<?php foreach ($passages as $passage): ?>
  <details class="passage" data-passage="<?= esc($passage['id'], 'attr') ?>" id="passage-<?= esc($passage['id'], 'attr') ?>" open>
    <summary class="passage-title"><?= icon('book') ?> <?= esc($passage['title'] !== '' ? $passage['title'] : lang('Game.readingLabel')) ?></summary>
    <div class="passage-content">
      <?php if (! empty($passage['media'])): ?>
        <img class="passage-img" src="<?= esc($passage['media'], 'attr') ?>" alt="" loading="lazy">
      <?php endif ?>
      <div class="passage-body"><?= nl2br(esc($passage['body'])) ?></div>
    </div>
  </details>
<?php endforeach ?>
</div>

<div class="boleh-cards stage-box stage-scroll">
<h2 class="cards-heading"><?= icon('check') ?> <?= esc(lang('Game.statementsLabel')) ?></h2>
<div class="card-grid<?= count($items) === 1 ? ' is-single' : '' ?>">
  <?php foreach ($items as $index => $item): ?>
    <?php $itemId = (string) $item['id']; ?>
    <article class="verdict-card" data-item="<?= esc($itemId, 'attr') ?>" aria-labelledby="card-<?= esc($itemId, 'attr') ?>-text">
      <header class="card-head">
        <span class="card-no"><?= esc(lang('Game.cardN', [$index + 1])) ?></span>
        <?php if (! empty($item['passage_id']) && isset($passages[$item['passage_id']])): ?>
          <a class="chip card-passage" href="#passage-<?= esc($item['passage_id'], 'attr') ?>">
            <?= icon('book') ?> <?= esc(lang('Game.basedOn', [$passages[$item['passage_id']]['title'] ?: lang('Game.passageLabel')])) ?>
          </a>
        <?php endif ?>
      </header>

      <?php if (! empty($item['media'])): ?>
        <?php // 192 × 144 dp di kartu; ketuk untuk perbesar 640 × 480 dp (Dok 3 §5.2, §6) ?>
        <button type="button" class="card-image" data-zoom="<?= esc($item['media'], 'attr') ?>"
                aria-label="<?= esc(lang('Game.libraryZoom') . ': ' . lang('Game.cardN', [$index + 1]), 'attr') ?>">
          <img src="<?= esc($item['media'], 'attr') ?>" alt="" loading="lazy" width="480" height="360">
        </button>
      <?php endif ?>

      <p class="card-text" id="card-<?= esc($itemId, 'attr') ?>-text"><?= esc($item['prompt']) ?></p>

      <fieldset class="card-choices">
        <legend class="visually-hidden"><?= esc(lang('Game.cardN', [$index + 1])) ?></legend>
        <?php foreach ($verdicts as $verdict): ?>
          <label class="choice choice-<?= esc($verdict, 'attr') ?>">
            <input type="radio" name="verdict-<?= esc($itemId, 'attr') ?>" value="<?= esc($verdict, 'attr') ?>" data-verdict="<?= esc($verdict, 'attr') ?>">
            <span><?= esc(lang_or($labels[$verdict] ?? '', ucfirst($verdict))) ?></span>
          </label>
        <?php endforeach ?>
      </fieldset>

    </article>
  <?php endforeach ?>
</div>
</div>

<div class="arena-actions">
  <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('check') ?> <?= esc(lang('Game.check')) ?></button>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
