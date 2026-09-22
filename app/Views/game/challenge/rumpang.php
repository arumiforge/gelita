<?php
/**
 * Engine `rumpang` — isi bagian rumpang.
 *
 * Tiap kalimat memuat penanda `___` yang diganti kotak rumpang. Varian bank
 * kata: kotak berupa tombol, diisi dengan mengetuk kata dari bank
 * (payload.word_bank — jawaban + pengecoh yang sudah diacak server; client
 * tidak tahu kata mana milik rumpang mana). Varian tanpa bank kata: kotak
 * berupa input teks. engines/rumpang.js (tahap 6) memasang perilakunya.
 *
 * @var App\Entities\ChallengeNode $node
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $payload
 * @var string                     $locale
 */
$items       = $payload['items'] ?? [];
$useWordBank = isset($payload['word_bank']);
$media       = $payload['node']['scene'] ?? ($items[0]['media'] ?? null);
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="rumpang<?= $media ? ' has-media' : '' ?>" data-word-bank="<?= $useWordBank ? '1' : '0' ?>">
  <?php if ($media): ?>
    <figure class="rumpang-media">
      <img src="<?= esc($media, 'attr') ?>" alt="" loading="lazy">
    </figure>
  <?php endif ?>

  <div class="rumpang-main">
    <ol class="sentences">
      <?php foreach ($items as $index => $item): ?>
        <?php
        $prompt = (string) ($item['prompt'] ?? '');
        $parts  = explode('___', $prompt, 2);
        $label  = lang('Game.blank', [$index + 1]);
        ?>
        <li class="sentence" data-item="<?= esc($item['id'], 'attr') ?>">
          <?php if (($item['source_text'] ?? '') !== ''): ?>
            <span class="sentence-source"><?= esc($item['source_text']) ?></span>
          <?php endif ?>
          <span class="sentence-text">
            <?= esc($parts[0]) ?>
            <?php if ($useWordBank): ?>
              <button type="button" class="blank" data-item="<?= esc($item['id'], 'attr') ?>" aria-label="<?= esc($label, 'attr') ?>">?</button>
            <?php else: ?>
              <input type="text" class="blank-input" data-item="<?= esc($item['id'], 'attr') ?>" aria-label="<?= esc($label, 'attr') ?>"
                     placeholder="<?= esc(lang('Game.typeAnswer'), 'attr') ?>" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="100">
            <?php endif ?>
            <?= esc($parts[1] ?? '') ?>
          </span>
        </li>
      <?php endforeach ?>
    </ol>

    <?php if ($useWordBank): ?>
      <div class="word-bank" id="word-bank" role="group" aria-label="<?= esc(lang('Game.wordBank'), 'attr') ?>">
        <span class="word-bank-title"><?= esc(lang('Game.wordBank')) ?></span>
        <?php foreach ($payload['word_bank'] as $index => $word): ?>
          <button type="button" class="word" data-word-id="k<?= $index ?>"><?= esc($word) ?></button>
        <?php endforeach ?>
      </div>
    <?php endif ?>

    <div class="arena-actions">
      <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('check') ?> <?= esc(lang('Game.check')) ?></button>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
