<?php
/**
 * Engine `cari` — cari objek budaya di dalam adegan.
 *
 * Objek jebakan dirender SAMA PERSIS dengan objek asli: tanpa kelas, atribut,
 * label, atau urutan DOM yang membedakannya — justru itulah yang diuji.
 * Payload (ChallengeService::huntPayload) sudah menjamin hal yang sama di
 * sumber halaman: `objects` hanya berisi token buram per attempt + posisi,
 * diurutkan menurut posisi di layar; teks petunjuk (`clues`) hanya untuk
 * target dan tidak terhubung ke objek mana pun. Label tombol netral
 * ("Objek 3"). Posisi memakai persen (left/top/width) agar tetap tepat pada
 * semua ukuran layar.
 *
 * Jawaban dikirim engines/cari.js (tahap 6) sebagai
 * `{ item_id: <clues[i].item_id>, answer: { object: <data-object> } }`.
 * Petunjuk pertama dirender server agar terbaca tanpa JavaScript.
 *
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeAttempt $attempt
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$objects = $payload['objects'] ?? [];
$clues   = $payload['clues'] ?? [];
$scene   = $payload['node']['scene'] ?? null;
$total   = max(1, count($clues));
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="hunt">
  <div class="hunt-clue">
    <?= component('character', ['character' => 'mbah_kedu', 'class' => 'hunt-guide']) ?>
    <div class="hunt-clue-body">
      <span class="eyebrow"><?= str_replace(
          ['{0}', '{1}'],
          ['<b id="clue-index">1</b>', '<span id="clue-total">' . $total . '</span>'],
          esc(lang('Game.clueOf', ['{0}', '{1}'])),
      ) ?></span>
      <p class="clue-text" id="clue-text" aria-live="polite"><?= esc(($clues[0]['text'] ?? '') !== '' ? $clues[0]['text'] : lang('Game.clueWaiting')) ?></p>
    </div>
  </div>

  <div class="hunt-scene<?= $scene ? '' : ' is-drawn' ?>" id="hunt-scene">
    <?php if ($scene): ?>
      <img class="scene-bg" src="<?= esc($scene, 'attr') ?>" alt="<?= esc(lang('Game.huntSceneAlt'), 'attr') ?>">
    <?php endif ?>
    <?php foreach ($objects as $index => $object): ?>
      <button type="button" class="object" data-object="<?= esc($object['ref'], 'attr') ?>"
              style="left: <?= (float) $object['x'] ?>%; top: <?= (float) $object['y'] ?>%; width: <?= (float) $object['w'] ?>%"
              aria-label="<?= esc(lang('Game.objectN', [$index + 1]), 'attr') ?>">
        <?php if (! empty($object['media'])): ?>
          <img src="<?= esc($object['media'], 'attr') ?>" alt="" loading="lazy">
        <?php endif ?>
      </button>
    <?php endforeach ?>
  </div>

  <div class="hunt-side">
    <p class="hunt-list-title"><?= esc(lang('Game.huntList')) ?></p>
    <ol class="hunt-list" id="hunt-list">
      <?php for ($i = 1; $i <= $total; $i++): ?>
        <li class="hunt-target<?= $i === 1 ? ' is-current' : '' ?>"><?= esc(lang('Game.huntPending', [$i])) ?></li>
      <?php endfor ?>
    </ol>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
