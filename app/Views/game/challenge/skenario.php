<?php
/**
 * Engine `skenario` — Skenario Digital (research revision 10, part 6:
 * docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md §6).
 *
 * One scenario at a time: the situation on the left (picture, text, ▶
 * Dengarkan), the two steps on the right (1 Tindakan, 2 Alasan). The actions
 * and reasons come from `payload.scenarios[i].actions` / `.reasons` as
 * `{ref, text}` in the attempt's order; refs are opaque per attempt, and
 * scores, codes, and feedback are not in the page (32 §5.2).
 * engines/skenario.js draws the steps and sends one try = one action and one
 * reason to `/api/attempts/{id}/responses`.
 *
 * The situation is read out only when the student taps ▶ (K11); choices and
 * feedback are never read out. The first situation is rendered here so it
 * reads without JavaScript. Choices sit on navy-edged cards and a picked one
 * is marked with ● and a glow, not by colour alone (32 §6.2).
 *
 * @var App\Entities\ChallengeNode    $node
 * @var App\Entities\Level            $level
 * @var App\Entities\ChallengeAttempt $attempt
 * @var array<string, mixed>          $payload
 * @var string                        $locale
 */
$scenarios = $payload['scenarios'] ?? [];
$first     = $scenarios[0] ?? null;
$voiced    = array_filter(array_column($scenarios, 'audio')) !== [];
$maxTries  = max(1, (int) ($payload['max_tries'] ?? config('Gelita')->maxTries));
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>
<?php // Logo left, the region name in the middle, no shard plate, as on the other challenge screens ?>
<?= $this->section('hudLayout') ?>region-centre<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="scenario" id="scenario"
     data-actions-title="<?= esc(lang('Game.scenarioActions'), 'attr') ?>"
     data-picture-alt="<?= esc(lang('Game.scenarioSituationAlt'), 'attr') ?>">
  <section class="scenario-situation stage-box stage-scroll" aria-labelledby="scenario-progress">
    <header class="scenario-head">
      <span class="eyebrow" id="scenario-progress"><?= esc(lang('Game.scenarioSituation')) ?></span>
      <?php // Tries of the scenario on screen (32 K4); engines/skenario.js fills them from the server ?>
      <span class="clue-tries" id="scenario-tries" role="img" data-label="<?= esc(lang('Game.triesLabel') . ': ' . lang('Game.triesOf', ['{0}', '{1}']), 'attr') ?>" aria-label="<?= esc(lang('Game.triesLabel') . ': ' . lang('Game.triesOf', [$maxTries, $maxTries]), 'attr') ?>">
        <span aria-hidden="true"><?= esc(lang('Game.triesLabel')) ?></span>
        <span class="tries-dots" aria-hidden="true"><?php for ($i = 0; $i < $maxTries; $i++): ?><i class="tries-dot is-left"></i><?php endfor ?></span>
      </span>
    </header>
    <figure class="scenario-figure" id="scenario-figure"<?= empty($first['media']) ? ' hidden' : '' ?>>
      <img id="scenario-img" src="<?= esc((string) ($first['media'] ?? ''), 'attr') ?>" alt="<?= esc(lang('Game.scenarioSituationAlt'), 'attr') ?>" width="1536" height="1024" decoding="async">
    </figure>
    <div class="scenario-voice" id="scenario-voice"></div>
    <p class="scenario-prompt" id="scenario-prompt" aria-live="polite"><?= esc((string) ($first['prompt'] ?? '')) ?></p>
  </section>

  <section class="scenario-steps stage-box stage-scroll" aria-live="polite">
    <ol class="scenario-stepper" id="scenario-stepper" aria-hidden="true">
      <li data-step="action" class="is-current"></li>
      <li data-step="reason"></li>
    </ol>
    <div class="scenario-body" id="scenario-body"></div>
  </section>

  <?php if ($voiced): ?>
    <template id="tpl-scenario-voice">
      <div class="audio-player scenario-audio">
        <div class="audio-controls">
          <button type="button" class="audio-btn" data-action="play"><?= icon('play') ?> <span data-voice-label></span></button>
          <button type="button" class="audio-btn" data-action="pause" aria-label="<?= esc(lang('Game.audioPause'), 'attr') ?>" hidden><?= icon('pause') ?> <span data-voice-playing></span></button>
        </div>
        <span class="clue-voice-off" aria-hidden="true" title="<?= esc(lang('Js.audioNoSound'), 'attr') ?>"><?= icon('sound-off') ?></span>
      </div>
    </template>
  <?php endif ?>
</div>

<?php // The challenge action bar: Kembali (step 2), the main button of the step, and Lanjut once closed ?>
<div class="arena-actions">
  <button class="btn btn-quiet btn-lg btn-action" id="btn-scenario-back" type="button" hidden><?= icon('left') ?> <span></span></button>
  <button class="btn btn-primary btn-lg btn-action" id="btn-scenario-go" type="button" disabled><span></span> <?= icon('right') ?></button>
  <?php // Shown by challenge.js once the attempt is closed: moves on to the result screen ?>
  <button <?= plate_attrs('next', 'btn-action') ?> id="btn-next" type="button" hidden><span><?= esc(lang('Game.continue')) ?></span></button>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
