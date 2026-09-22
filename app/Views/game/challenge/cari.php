<?php
/**
 * Engine `cari` — cari objek budaya di dalam adegan.
 *
 * Objek jebakan dirender SAMA PERSIS dengan objek asli: tanpa kelas, atribut,
 * label, atau urutan DOM yang membedakannya — justru itulah yang diuji. Karena
 * itu teks `prompt` objek tidak dirender di sini, label tombol netral
 * ("Objek 3"), dan urutan DOM mengikuti posisi di layar (atas → bawah,
 * kiri → kanan), bukan urutan payload. Posisi memakai persen
 * (left/top/width) agar tetap tepat pada semua ukuran layar.
 *
 * Teks petunjuk diisi engines/cari.js (tahap 6); jumlah petunjuk = jumlah
 * butir yang dinilai pada attempt ini.
 *
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeAttempt $attempt
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$items   = $payload['items'] ?? [];
$scene   = $payload['node']['scene'] ?? null;
$clues   = max(1, (int) ($attempt->scorable_items ?? 0));
$objects = array_values(array_filter($items, static fn (array $i): bool => isset($i['config']['x'], $i['config']['y'])));

usort($objects, static fn (array $a, array $b): int => [(float) $a['config']['y'], (float) $a['config']['x']]
    <=> [(float) $b['config']['y'], (float) $b['config']['x']]);
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
          ['<b id="clue-index">1</b>', '<span id="clue-total">' . $clues . '</span>'],
          esc(lang('Game.clueOf', ['{0}', '{1}'])),
      ) ?></span>
      <p class="clue-text" id="clue-text" aria-live="polite"><?= esc(lang('Game.clueWaiting')) ?></p>
    </div>
  </div>

  <div class="hunt-scene<?= $scene ? '' : ' is-drawn' ?>" id="hunt-scene">
    <?php if ($scene): ?>
      <img class="scene-bg" src="<?= esc($scene, 'attr') ?>" alt="<?= esc(lang('Game.huntSceneAlt'), 'attr') ?>">
    <?php endif ?>
    <?php foreach ($objects as $index => $object): ?>
      <button type="button" class="object" data-item="<?= esc($object['id'], 'attr') ?>"
              style="left: <?= (float) $object['config']['x'] ?>%; top: <?= (float) $object['config']['y'] ?>%; width: <?= (float) ($object['config']['w'] ?? 12) ?>%"
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
      <?php for ($i = 1; $i <= $clues; $i++): ?>
        <li class="hunt-target<?= $i === 1 ? ' is-current' : '' ?>"><?= esc(lang('Game.huntPending', [$i])) ?></li>
      <?php endfor ?>
    </ol>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
