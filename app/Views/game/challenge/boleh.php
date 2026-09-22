<?php
/**
 * Engine `boleh` — kartu pernyataan dengan penilaian Benar / Salah
 * (/ Pendapat pada Magelang 3), teks bacaan bersama (Temanggung 3), dua sumber
 * berdampingan (Wonosobo 3), dan kolom alasan (varian beralasan).
 *
 * Pilihan verdict memakai radio bawaan: dapat dipilih dengan keyboard/sentuhan
 * tanpa JavaScript, dan tidak ada kunci jawaban di markup. Tombol dirender dari
 * payload.verdict_options. Sumber `official` diberi ikon gedung, `anonymous`
 * ikon tanda tanya — tanpa menyebut mana yang benar.
 *
 * @var App\Entities\ChallengeNode $node
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $payload
 * @var string                     $locale
 */
$items    = $payload['items'] ?? [];
$passages = $payload['passages'] ?? [];
$verdicts = $payload['verdict_options'] ?? ['benar', 'salah'];
$reason   = ! empty($payload['require_reason']);
$labels   = ['benar' => 'Game.verdictBenar', 'salah' => 'Game.verdictSalah', 'pendapat' => 'Game.verdictPendapat'];
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<?php foreach ($passages as $passage): ?>
  <details class="passage" data-passage="<?= esc($passage['id'], 'attr') ?>" id="passage-<?= esc($passage['id'], 'attr') ?>" open>
    <summary class="passage-title"><?= icon('book') ?> <?= esc($passage['title'] !== '' ? $passage['title'] : lang('Game.passageLabel')) ?></summary>
    <div class="passage-content">
      <?php if (! empty($passage['media'])): ?>
        <img class="passage-img" src="<?= esc($passage['media'], 'attr') ?>" alt="" loading="lazy">
      <?php endif ?>
      <div class="passage-body"><?= nl2br(esc($passage['body'])) ?></div>
    </div>
  </details>
<?php endforeach ?>

<?php if (in_array('pendapat', $verdicts, true)): ?>
  <p class="fact-opinion"><?= icon('info') ?>
    <span><b><?= esc(lang('Game.factWord')) ?></b> · <b><?= esc(lang('Game.opinionWord')) ?></b> — <?= esc(lang('Game.factVsOpinion')) ?></span>
  </p>
<?php endif ?>

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
        <div class="card-image"><img src="<?= esc($item['media'], 'attr') ?>" alt="" loading="lazy"></div>
      <?php endif ?>

      <?php if (! empty($item['sources'])): ?>
        <div class="sources">
          <?php foreach ($item['sources'] as $source): ?>
            <?php $official = $source['kind'] === 'official'; ?>
            <blockquote class="source source-<?= esc($source['kind'], 'attr') ?>">
              <cite><?= icon($official ? 'building' : 'question') ?>
                <?= esc($source['label'] !== '' ? $source['label'] : lang($official ? 'Game.sourceOfficial' : 'Game.sourceAnonymous')) ?></cite>
              <p><?= esc($source['text']) ?></p>
            </blockquote>
          <?php endforeach ?>
        </div>
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

      <?php if ($reason): ?>
        <div class="field card-reason-field">
          <label for="reason-<?= esc($itemId, 'attr') ?>"><?= esc(lang('Game.reasonLabel')) ?></label>
          <textarea class="card-reason" id="reason-<?= esc($itemId, 'attr') ?>" data-item="<?= esc($itemId, 'attr') ?>"
                    rows="2" maxlength="1000" placeholder="<?= esc(lang('Game.reasonPlaceholder'), 'attr') ?>"></textarea>
        </div>
      <?php endif ?>
    </article>
  <?php endforeach ?>
</div>

<div class="arena-actions">
  <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('check') ?> <?= esc(lang('Game.check')) ?></button>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
