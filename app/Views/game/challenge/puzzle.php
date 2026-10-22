<?php
/**
 * Engine `puzzle` — Susun Gambar.
 * Perilaku tukar keping, seret, keyboard, dan Periksa dipasang
 * engines/puzzle.js; client hanya menukar posisi, tidak menilai apa pun.
 *
 * Satu gambar utuh dipotong di browser menjadi n × n keping SVG
 * (App\Libraries\PuzzleCut): ukuran papan dan bentuk keping (kotak atau
 * jigsaw) mengikuti soal, yang ditetapkan dari tingkat kesulitan wilayah saat
 * soal dibuat — Temanggung 3 × 3 kotak, Magelang 4 × 4 kotak, Wonosobo 4 × 4
 * jigsaw. `preserveAspectRatio="xMidYMid slice"` memotong bagian tengah
 * gambar yang tidak persegi. Keping tidak bernomor; label pembaca layar
 * menyebut letak slot (baris, kolom), bukan letak benarnya.
 *
 * Urutan awal diacak deterministik per pengerjaan agar muat ulang tidak
 * mengubah papan, dan tidak ada keping yang sudah di tempatnya. Keterangan
 * gambar (prompt soal) baru tampil setelah gambar utuh atau setelah
 * kesempatan habis.
 *
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeAttempt $attempt
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$item     = $payload['items'][0] ?? [];
$layout   = App\Libraries\PuzzleCut::resolve((array) ($item['config'] ?? []), (string) $level->difficulty);
$grid     = $layout['grid'];
$shape    = $layout['shape'];
$count    = $grid * $grid;
$image    = $item['media'] ?? $payload['node']['scene'] ?? media_src(null);
$caption  = trim((string) ($item['prompt'] ?? ''));

// Acak tetap per pengerjaan, tanpa satu keping pun di tempatnya sendiri (Dok 2 §4)
$order = seeded_derangement($count, 'puzzle|' . $attempt->id);
$pieces = App\Libraries\PuzzleCut::pieces($grid, $shape, (string) ($item['item_key'] ?? 'puzzle'));
$board  = $grid * App\Libraries\PuzzleCut::CELL;
$clip   = 'pz' . (int) $attempt->id . '-';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>
<?php // Logo left, the region name in the middle, no shard plate (owner, 21 October 2026) ?>
<?= $this->section('hudLayout') ?>region-centre<?= $this->endSection() ?>

<?= $this->section('aside') ?>
  <ul class="challenge-tips">
    <li><?= esc(lang($shape === 'jigsaw' ? 'Game.puzzleHintJigsaw' : 'Game.puzzleHint')) ?></li>
    <li><?= esc(lang('Game.puzzleTipMark')) ?></li>
    <li><?= esc(lang('Game.puzzleTipHint')) ?></li>
  </ul>
<?= $this->endSection() ?>

<?= $this->section('arena') ?>
  <div class="puzzle-wrap" id="puzzle-wrap" data-mode="arrange" data-item="<?= esc($item['id'] ?? '', 'attr') ?>">
    <div class="puzzle-board is-<?= esc($shape, 'attr') ?>" id="puzzle-board" role="group" aria-label="<?= esc(lang('Game.puzzleImageAlt'), 'attr') ?>"
         data-grid="<?= $grid ?>" data-shape="<?= esc($shape, 'attr') ?>"
         style="--grid: <?= $grid ?>; --inset: <?= App\Libraries\PuzzleCut::boardInset($grid, $shape) ?>%; --overhang: <?= App\Libraries\PuzzleCut::overhang($shape) ?>%">
      <svg class="puzzle-defs" width="0" height="0" aria-hidden="true" focusable="false">
        <defs>
          <?php foreach ($pieces as $piece): ?>
            <clipPath id="<?= $clip . $piece['index'] ?>"><path d="<?= esc($piece['path'], 'attr') ?>"/></clipPath>
          <?php endforeach ?>
        </defs>
      </svg>
      <div class="puzzle-grid">
        <?php foreach ($order as $slot => $index): ?>
          <?php $piece = $pieces[$index]; $row = intdiv($slot, $grid) + 1; $col = $slot % $grid + 1; ?>
          <button type="button" class="piece" data-slot="<?= $slot ?>" data-piece="<?= $index ?>"
                  data-pos="<?= esc(lang('Game.piecePos', [$row, $col]), 'attr') ?>"
                  aria-label="<?= esc(lang('Game.piece', [$row, $col]), 'attr') ?>">
            <svg class="piece-art" viewBox="<?= esc($piece['viewBox'], 'attr') ?>" aria-hidden="true" focusable="false"
                 xmlns:xlink="http://www.w3.org/1999/xlink">
              <image href="<?= esc($image, 'attr') ?>" xlink:href="<?= esc($image, 'attr') ?>" x="0" y="0"
                     width="<?= $board ?>" height="<?= $board ?>" preserveAspectRatio="xMidYMid slice"
                     clip-path="url(#<?= $clip . $index ?>)"/>
              <path class="piece-edge" d="<?= esc($piece['path'], 'attr') ?>"/>
            </svg>
          </button>
        <?php endforeach ?>
      </div>
    </div>
    <?php if ($caption !== ''): ?>
      <?php // Owner's panel, 20 October 2026: "i" medallion, gold title and rule, white text, mountains (CSS) ?>
      <div class="puzzle-caption stage-scroll" id="puzzle-caption" role="note" hidden>
        <div class="puzzle-caption-head"><?= round_icon('info', 'puzzle-caption-art') ?: '<span class="puzzle-caption-icon" aria-hidden="true">i</span>' ?> <b><?= esc(lang('Game.puzzleCaption')) ?></b></div>
        <p><?= esc($caption) ?></p>
      </div>
    <?php endif ?>
    <?php // Owner's action bar, 20 October 2026: centred gold button between two gold rules (CSS) ?>
    <div class="arena-actions">
      <button class="btn btn-primary btn-lg btn-action" id="btn-check" type="button"><span><?= esc(lang('Game.puzzleCheck')) ?></span> <?= icon('right') ?></button>
      <?php // Shown by challenge.js once the attempt is closed: moves on to the result screen ?>
      <button <?= plate_attrs('next', 'btn-action') ?> id="btn-next" type="button" hidden><span><?= esc(lang('Game.continue')) ?></span></button>
    </div>
  </div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
