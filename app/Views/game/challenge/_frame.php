<?php
/**
 * Kerangka layar tantangan, sama untuk kelima engine. Di-include oleh
 * game/challenge/{engine}.php; isi arena datang dari section `arena`.
 *
 * Payload soal ditanam sebagai <script type="application/json" id="challenge-data">
 * dan sudah disaring ChallengeService: tanpa kunci jawaban, tanpa penanda
 * decoy, dan tanpa pemetaan bank kata (engine `cari` memakai `objects` +
 * `clues`, bukan `items`). challenge.js (tahap 6) membacanya dan
 * memasang mesin tantangan pada markup arena.
 *
 * Satu-satunya jalan keluar adalah tombol Keluar yang harus dikonfirmasi
 * (<details>, tanpa JavaScript); tautan beranda/profil/keluar di HUD
 * disembunyikan selama tantangan (body.is-challenge).
 *
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\ChallengeAttempt $attempt
 * @var int                           $sequence
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$region     = $level->text('name', $locale);
$totalNodes = count(service('contentRepository')->nodesForLevel($level->id));
$engine     = (string) $node->engine_type;
$hints      = (int) ($payload['hints_count'] ?? 0);
?>
<section class="challenge challenge-<?= esc($engine, 'attr') ?>"
         data-screen="challenge"
         data-engine="<?= esc($engine, 'attr') ?>"
         data-variant="<?= esc($node->variant_code ?? '', 'attr') ?>"
         data-attempt="<?= esc($attempt->id, 'attr') ?>"
         data-node="<?= esc($node->id, 'attr') ?>"
         data-level="<?= esc($level->code, 'attr') ?>"
         data-level-id="<?= esc($level->id, 'attr') ?>"
         data-sequence="<?= esc($sequence, 'attr') ?>"
         data-exit-url="<?= esc(base_url('wilayah/' . $level->code), 'attr') ?>">

  <div class="challenge-character" aria-hidden="true">
    <?= component('character', ['character' => 'jaka']) ?>
  </div>

  <div class="panel-carved arena-panel">
    <header class="challenge-head">
      <div class="badge-pos num" aria-hidden="true"><?= esc($sequence) ?></div>
      <div class="challenge-info">
        <span class="eyebrow"><?= esc($region) ?> · <?= esc(lang('Game.challengeOf', [$sequence, $totalNodes])) ?> · <?= esc(engine_label($engine, $node->variant_code)) ?></span>
        <h1 class="title"><?= esc($node->text('title', $locale)) ?></h1>
      </div>
      <div class="challenge-chips">
        <span class="chip" title="<?= esc(lang('Game.timer'), 'attr') ?>">
          <?= icon('clock') ?><span class="visually-hidden"><?= esc(lang('Game.timer')) ?></span> <b id="timer" class="num">0:00</b>
        </span>
        <span class="chip chip-tally">
          <span class="tally-ok"><?= icon('check') ?><span class="visually-hidden"><?= esc(lang('Game.countCorrect')) ?></span> <b id="count-correct" class="num">0</b></span>
          <span class="tally-bad"><?= icon('cross') ?><span class="visually-hidden"><?= esc(lang('Game.countWrong')) ?></span> <b id="count-wrong" class="num">0</b></span>
        </span>
        <button type="button" class="chip chip-btn" id="btn-hint" data-hints="<?= $hints ?>" hidden>
          <?= icon('hint') ?> <?= esc(lang('Game.hint')) ?>
        </button>
        <details class="confirm confirm-down challenge-exit">
          <summary class="btn btn-quiet btn-sm"><?= icon('logout') ?> <?= esc(lang('Game.exitChallenge')) ?></summary>
          <div class="confirm-box" role="group" aria-labelledby="exit-title">
            <h2 id="exit-title"><?= esc(lang('Game.exitConfirmTitle')) ?></h2>
            <p><?= esc(lang('Game.exitConfirmText')) ?></p>
            <a class="btn btn-danger" href="<?= base_url('wilayah/' . $level->code) ?>" data-exit-confirm><?= esc(lang('Game.exitConfirmYes')) ?></a>
          </div>
        </details>
      </div>
    </header>

    <div class="arena">
      <?php if ($node->text('instruction', $locale) !== ''): ?>
        <p class="instruction"><?= icon('info') ?> <span><?= esc($node->text('instruction', $locale)) ?></span></p>
      <?php endif ?>
      <noscript><div class="alert alert-warn"><?= icon('warn') ?><p><?= esc(lang('Game.needsJs')) ?></p></div></noscript>

      <?php if (($payload['items'] ?? $payload['objects'] ?? []) === []): ?>
        <div class="empty-state"><?= icon('info') ?><p><?= esc(lang('Game.noItems')) ?></p></div>
      <?php else: ?>
        <?= $this->renderSection('arena') ?>
      <?php endif ?>
    </div>
  </div>
</section>
<script type="application/json" id="challenge-data"><?= json_encode(
    $payload,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
