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

// The anchor card (`{node}-x`, 31 §12.2) is the second text, stacked under
// the variant's text and checked with the same Cek
$isAnchor = static fn (?array $passage): bool => $passage !== null && str_ends_with((string) ($passage['key'] ?? ''), '-x');
$titleOf  = static fn (array $passage): string => $passage['title'] !== '' ? $passage['title'] : lang($isAnchor($passage) ? 'Game.anchorCardTitle' : 'Game.readingLabel');

// Digital text as plain text (31 §6.2): blank line = block; line markers
// `>> ` header, `~~ ` small meta line, `:: ` entry (comment, message, result)
$digital = static function (string $body): string {
    $html = '';

    foreach (preg_split('/\R[ \t]*\R\s*/u', trim($body)) ?: [] as $block) {
        $html .= '<div class="dt-block">';

        foreach (preg_split('/\R/u', $block) ?: [] as $line) {
            $class = match (substr($line, 0, 3)) {
                '>> '   => 'dt-head',
                '~~ '   => 'dt-meta',
                ':: '   => 'dt-entry',
                default => 'dt-line',
            };
            $html .= '<p class="' . $class . '">' . esc($class === 'dt-line' ? $line : substr($line, 3)) . '</p>';
        }

        $html .= '</div>';
    }

    return $html;
};
$verdicts = ['benar', 'salah'];
$labels   = ['benar' => 'Game.verdictBenar', 'salah' => 'Game.verdictSalah'];
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>
<?php // Logo left, the region name in the middle, no shard plate (owner, 21 October 2026) ?>
<?= $this->section('hudLayout') ?>region-centre<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="boleh-passages stage-box stage-scroll" id="reading-panel">
<?php foreach ($passages as $passage): ?>
  <details class="passage" data-passage="<?= esc($passage['id'], 'attr') ?>" id="passage-<?= esc($passage['id'], 'attr') ?>" open>
    <summary class="passage-title"><?= icon('book') ?> <?= esc($titleOf($passage)) ?></summary>
    <div class="passage-content">
      <?php if (! empty($passage['media'])): ?>
        <img class="passage-img" src="<?= esc($passage['media'], 'attr') ?>" alt="" loading="lazy">
      <?php endif ?>
      <?php if (($passage['layout'] ?? 'plain') === 'plain'): ?>
        <div class="passage-body"><?= nl2br(esc($passage['body'])) ?></div>
      <?php else: ?>
        <div class="passage-body dt is-<?= esc($passage['layout'], 'attr') ?>"><?= $digital((string) $passage['body']) ?></div>
      <?php endif ?>
    </div>
  </details>
<?php endforeach ?>
</div>

<div class="boleh-cards stage-box stage-scroll">
<h2 class="cards-heading"><?= icon('check') ?> <?= esc(lang('Game.statementsLabel')) ?></h2>
<div class="card-grid<?= count($items) === 1 ? ' is-single' : '' ?>">
  <?php $anchorShown = false; ?>
  <?php foreach ($items as $index => $item): ?>
    <?php $itemId = (string) $item['id']; ?>
    <?php if (! $anchorShown && $isAnchor($passages[$item['passage_id'] ?? 0] ?? null)): $anchorShown = true; ?>
      <h3 class="cards-subheading"><?= icon('book') ?> <?= esc(lang('Game.anchorCardTitle')) ?></h3>
    <?php endif ?>
    <article class="verdict-card" data-item="<?= esc($itemId, 'attr') ?>" aria-labelledby="card-<?= esc($itemId, 'attr') ?>-text">
      <header class="card-head">
        <span class="card-no"><?= esc(lang('Game.cardN', [$index + 1])) ?></span>
        <?php if (! empty($item['passage_id']) && isset($passages[$item['passage_id']])): ?>
          <a class="chip card-passage" href="#passage-<?= esc($item['passage_id'], 'attr') ?>">
            <?= icon('book') ?> <?= esc(lang('Game.basedOn', [$isAnchor($passages[$item['passage_id']]) ? lang('Game.anchorCardTitle') : ($passages[$item['passage_id']]['title'] ?: lang('Game.passageLabel'))])) ?>
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

<?php // Owner's action bar, 20 October 2026: centred gold button between two gold rules (CSS) ?>
<div class="arena-actions">
  <button class="btn btn-primary btn-lg btn-action" id="btn-check" type="button"><span><?= esc(lang('Game.check')) ?></span> <?= icon('right') ?></button>
  <?php // Shown by challenge.js once the attempt is closed: moves on to the result screen ?>
  <button <?= plate_attrs('next', 'btn-action') ?> id="btn-next" type="button" hidden><span><?= esc(lang('Game.continue')) ?></span></button>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
