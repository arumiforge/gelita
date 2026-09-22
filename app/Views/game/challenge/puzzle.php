<?php
/**
 * Engine `puzzle` — susun gambar (mode arrange) atau urutkan kartu (ordering,
 * Wonosobo node 1). Perilaku tukar keping, seret, keyboard, dan Periksa
 * dipasang engines/puzzle.js (tahap 6); client hanya menukar posisi, tidak
 * menilai apa pun.
 *
 * Susunan benar keping gambar memang publik (1–9 dari kiri atas), jadi urutan
 * awal yang diacak di sini — deterministik per attempt, agar muat ulang tidak
 * mengubah papan — tidak membocorkan kunci apa pun.
 *
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeAttempt $attempt
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$item     = $payload['items'][0] ?? [];
$ordering = ($item['interaction_type'] ?? '') === 'ordering' || $node->config('mode') === 'ordering';
$grid     = max(2, min(5, (int) ($item['config']['grid'] ?? $node->config('grid') ?? 3)));
$count    = $grid * $grid;
$image    = $item['media'] ?? $payload['node']['scene'] ?? media_src(null);

$order = seeded_shuffle(range(0, $count - 1), 'puzzle|' . $attempt->id);
if ($order === range(0, $count - 1)) {
    $order[] = array_shift($order);   // jangan pernah mulai dari susunan benar
}
$step = 100 / ($grid - 1);
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
    <div class="puzzle-board" id="puzzle-board" role="group" aria-label="<?= esc(lang('Game.puzzleImageAlt'), 'attr') ?>"
         style="--grid: <?= $grid ?>; --puzzle-img: url('<?= esc($image, 'css') ?>')">
      <?php foreach ($order as $slot => $piece): ?>
        <button type="button" class="piece" data-slot="<?= $slot ?>" data-piece="<?= $piece ?>"
                style="--px: <?= round(($piece % $grid) * $step, 4) ?>%; --py: <?= round(intdiv($piece, $grid) * $step, 4) ?>%"
                aria-label="<?= esc(lang('Game.piece', [$piece + 1]), 'attr') ?>">
          <span class="piece-no num" aria-hidden="true"><?= $piece + 1 ?></span>
        </button>
      <?php endforeach ?>
    </div>
    <p class="hint-text"><?= esc(lang('Game.puzzleHint')) ?></p>
    <div class="arena-actions">
      <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('puzzle') ?> <?= esc(lang('Game.puzzleCheck')) ?></button>
    </div>
  </div>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
