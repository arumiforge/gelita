<?php
/**
 * Kerangka arena tantangan, sama untuk kelima engine.
 *
 * Payload soal ditanam sebagai <script type="application/json" id="challenge-data">
 * dan sudah disaring ChallengeService: tanpa kunci jawaban, tanpa penanda
 * decoy, dan tanpa pemetaan bank kata.
 *
 * @var array<string, mixed> $payload
 */
?>
<section class="challenge challenge-<?= esc($node->engine_type) ?>"
         data-screen="challenge"
         data-engine="<?= esc($node->engine_type) ?>"
         data-variant="<?= esc($node->variant_code ?? '') ?>"
         data-attempt="<?= esc($attempt->id) ?>"
         data-node="<?= esc($node->id) ?>"
         data-level="<?= esc($level->code) ?>"
         data-sequence="<?= esc($sequence) ?>">

  <h1><?= esc($node->text('title', $locale)) ?></h1>
  <p class="challenge-instruction"><?= esc($node->text('instruction', $locale)) ?></p>
  <?= $this->include('partials/flash') ?>

  <div id="challenge-arena" class="challenge-arena" data-items="<?= count($payload['items'] ?? []) ?>">
    <noscript>
      <p class="alert alert-error">Tantangan ini membutuhkan JavaScript aktif.</p>
    </noscript>
  </div>

  <div class="challenge-actions">
    <button type="button" class="btn btn-primary" data-action="check"><?= esc(lang('Game.check')) ?></button>
    <?php if (($payload['hints_count'] ?? 0) > 0): ?>
      <button type="button" class="btn btn-ghost" data-action="hint"><?= esc(lang('Game.hint')) ?></button>
    <?php endif ?>
    <a class="btn btn-quiet" href="<?= base_url('wilayah/' . $level->code) ?>"><?= esc(lang('Game.back')) ?></a>
  </div>

  <script type="application/json" id="challenge-data"><?= json_encode(
      $payload,
      JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
  ) ?></script>

  <?= $this->include('partials/stage-note') ?>
</section>
