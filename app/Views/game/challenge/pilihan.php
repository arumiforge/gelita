<?php
/**
 * Engine `pilihan` — kuis pilihan bergambar, satu soal per layar.
 *
 * Tidak ada tombol Periksa: `allow_retry = false`, jawaban pertama sekaligus
 * jawaban final. Soal pertama diberi .is-current; engines/pilihan.js (tahap 6)
 * memajukannya setelah server menjawab. Kunci opsi yang benar baru dikirim
 * server SESUDAH jawaban diterima — tidak ada di markup maupun payload.
 *
 * @var App\Entities\ChallengeNode $node
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $payload
 * @var string                     $locale
 */
$items    = $payload['items'] ?? [];
$passages = $payload['passages'] ?? [];
$total    = count($items);
$letters  = range('A', 'Z');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<ol class="steps" id="steps" aria-label="<?= esc(lang('Game.questionOf', [1, $total]), 'attr') ?>">
  <?php foreach ($items as $index => $item): ?>
    <li class="step<?= $index === 0 ? ' is-current' : '' ?>" data-item="<?= esc($item['id'], 'attr') ?>"
        <?= $index === 0 ? 'aria-current="step"' : '' ?>><span class="visually-hidden"><?= esc(lang('Game.questionOf', [$index + 1, $total])) ?></span></li>
  <?php endforeach ?>
</ol>

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

<div class="questions" id="questions">
  <?php foreach ($items as $index => $item): ?>
    <section class="question-card<?= $index === 0 ? ' is-current' : '' ?>" data-item="<?= esc($item['id'], 'attr') ?>"
             aria-labelledby="q-<?= esc($item['id'], 'attr') ?>">
      <span class="eyebrow"><?= esc(lang('Game.questionOf', [$index + 1, $total])) ?></span>

      <?php if (($item['source_text'] ?? '') !== ''): ?>
        <details class="source-text" open>
          <summary><?= icon('text') ?> <?= esc(lang('Game.sourceText')) ?></summary>
          <p><?= nl2br(esc($item['source_text'])) ?></p>
        </details>
      <?php endif ?>

      <?php if (! empty($item['media'])): ?>
        <img class="question-img" src="<?= esc($item['media'], 'attr') ?>" alt="" loading="lazy">
      <?php endif ?>

      <p class="question" id="q-<?= esc($item['id'], 'attr') ?>"><?= esc($item['prompt']) ?></p>

      <div class="option-grid<?= array_filter(array_column($item['options'] ?? [], 'media')) !== [] ? ' has-media' : '' ?>">
        <?php foreach ($item['options'] ?? [] as $optionIndex => $option): ?>
          <button type="button" class="option" data-option="<?= esc($option['option_key'], 'attr') ?>">
            <?php if (! empty($option['media'])): ?>
              <img src="<?= esc($option['media'], 'attr') ?>" alt="" loading="lazy">
            <?php endif ?>
            <span class="option-letter" aria-hidden="true"><?= esc($letters[$optionIndex] ?? '') ?></span>
            <span class="label"><?= esc($option['label']) ?></span>
          </button>
        <?php endforeach ?>
      </div>
    </section>
  <?php endforeach ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
