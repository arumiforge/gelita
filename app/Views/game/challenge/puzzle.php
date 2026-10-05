<?php
/**
 * Engine `puzzle` — susun gambar (mode arrange) atau urutkan kartu (ordering).
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
 * Urutan awal diacak deterministik per attempt agar muat ulang tidak
 * mengubah papan. Keterangan gambar (prompt soal) baru tampil setelah gambar
 * utuh.
 *
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeAttempt $attempt
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$item     = $payload['items'][0] ?? [];
$ordering = ($item['interaction_type'] ?? '') === 'ordering' || $node->config('mode') === 'ordering';
$layout   = App\Libraries\PuzzleCut::resolve((array) ($item['config'] ?? []), null, $node->config('grid'), (string) $level->difficulty);
$grid     = $layout['grid'];
$shape    = $layout['shape'];
$count    = $grid * $grid;
$image    = $item['media'] ?? $payload['node']['scene'] ?? media_src(null);
$caption  = trim((string) ($item['prompt'] ?? ''));

$order = seeded_shuffle(range(0, $count - 1), 'puzzle|' . $attempt->id);
if ($order === range(0, $count - 1)) {
    $order[] = array_shift($order);   // jangan pernah mulai dari susunan benar
}
$pieces = $ordering ? [] : App\Libraries\PuzzleCut::pieces($grid, $shape, (string) ($item['item_key'] ?? 'puzzle'));
$board  = $grid * App\Libraries\PuzzleCut::CELL;
$clip   = 'pz' . (int) $attempt->id . '-';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<?php if ($ordering): ?>
  <div class="order-wrap" id="puzzle-wrap" data-mode="ordering" data-item="<?= esc($item['id'] ?? '', 'attr') ?>">
    <?php if (($item['prompt'] ?? '') !== ''): ?>
      <p class="question"><?= esc($item['prompt']) ?></p>
    <?php endif ?>
    <p class="hint-text"><?= esc(lang('Game.orderHint')) ?></p>
    <ol class="order-list" id="order-list">
      <?php foreach ($item['pieces'] ?? [] as $index => $piece): ?>
        <li class="order-card" draggable="true" data-piece="<?= esc($piece['key'], 'attr') ?>">
          <span class="order-no num" aria-hidden="true"><?= $index + 1 ?></span>
          <span class="order-text"><?= esc($piece['text']) ?></span>
          <span class="order-moves">
            <button type="button" class="icon-btn" data-move="up" aria-label="<?= esc(lang('Game.moveUp') . ': ' . $piece['text'], 'attr') ?>"><?= icon('up') ?></button>
            <button type="button" class="icon-btn" data-move="down" aria-label="<?= esc(lang('Game.moveDown') . ': ' . $piece['text'], 'attr') ?>"><?= icon('down') ?></button>
          </span>
        </li>
      <?php endforeach ?>
    </ol>
    <div class="arena-actions">
      <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('check') ?> <?= esc(lang('Game.check')) ?></button>
    </div>
  </div>
<?php else: ?>
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
    <p class="hint-text"><?= esc(lang($shape === 'jigsaw' ? 'Game.puzzleHintJigsaw' : 'Game.puzzleHint')) ?></p>
    <?php if ($caption !== ''): ?>
      <div class="puzzle-caption" id="puzzle-caption" role="note" hidden>
        <b><?= esc(lang('Game.puzzleCaption')) ?></b>
        <p><?= esc($caption) ?></p>
      </div>
    <?php endif ?>
    <div class="arena-actions">
      <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('puzzle') ?> <?= esc(lang('Game.puzzleCheck')) ?></button>
    </div>
  </div>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
