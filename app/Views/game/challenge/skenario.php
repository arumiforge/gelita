<?php
/**
 * Engine `skenario` — Skenario Digital (research revision 10, part 6:
 * docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md §6), drawn as
 * the owner-approved mockup of 41 (decisions 17–43, step D, 19 November 2026).
 *
 * Two sides on the region's background, no panel frames (41 decisions 1, 30):
 * - left (32, 104, 600 × 584): "Cerita 1 dari 4" with one bar per story and
 *   the Kesempatan coins, the 3 : 2 situation picture (504 × 336, never cut)
 *   with the Dengarkan cerita pill at its bottom centre, then the situation;
 * - right (664, 104, 584 × 584): the two step tabs, the instruction row
 *   (▶ Dengarkan petunjuk and the instruction text, step 1 only), the
 *   question, the choices and the buttons of the step (y 626).
 * The actions and reasons come from `payload.scenarios[i].actions` /
 * `.reasons` as `{ref, text}` in the attempt's order; refs are opaque per
 * attempt, and scores, codes, and feedback are not in the page (32 §5.2).
 * engines/skenario.js draws the steps and sends one try = one action and one
 * reason to `/api/attempts/{id}/responses`.
 *
 * The situation and the instruction are read out only when their ▶ is tapped
 * (K10, K11); choices and feedback are never read out. Every interface part is
 * a picture of 41 §8 (`SD-01` to `SD-11`, public/assets/ui/sd-*.webp) drawn at
 * its display size, never stretched; the custom properties below pass them to
 * game.css (§Skenario Digital). A picked choice is marked with ● and a glow,
 * not by colour alone (32 §6.2).
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
// The instruction lives in the right side here, not in the frame's aside (41 decision 33)
$instruction        = $node->text('instruction', $locale);
$instructionAudioId = $locale === 'en' ? $node->audio_instruction_en_id : $node->audio_instruction_id;
$instructionAudio   = audio_src($instructionAudioId);
$pictures = [];
foreach (['sd-next', 'sd-next-long', 'sd-back', 'sd-step-1', 'sd-step-1-on', 'sd-step-2', 'sd-step-2-on', 'sd-chosen', 'sd-chosen-2', 'sd-listen', 'sd-listen-wide'] as $file) {
    $src = ui_picture_src($file . '.webp');
    if ($src !== null) {
        $pictures[] = '--' . $file . ': url(&quot;' . esc($src, 'attr') . '&quot;)';
    }
}
// The sound wave: drawn by the game, it moves only while a recording plays (41 decision 6)
$wave = static function (int $bars): string {
    $html = '<span class="sd-wave" aria-hidden="true">';
    for ($i = 0; $i < $bars; $i++) {
        $html .= '<i style="--h:' . (8 + (int) round(20 * abs(sin($i * 1.7)))) . ';--d:' . (($i % 7) * -0.13) . 's"></i>';
    }

    return $html . '</span><span class="sd-dur" aria-hidden="true"></span>';
};
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?php // The region's Skenario Digital background (slot bg.{wilayah}.skenario, 41 SD-12 to SD-14), else the usual one ?>
<?php $scenarioBackground = 'bg.' . $level->code . '.skenario' ?>
<?= $this->section('background') ?><?= media_key_src($scenarioBackground) ?? media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>
<?php // No HUD band: the challenge header is the one top row (41 §4, 19 November 2026) ?>
<?= $this->section('hudLayout') ?>challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="scenario" id="scenario"
     data-actions-title="<?= esc(lang('Game.scenarioActions'), 'attr') ?>"
     data-picture-alt="<?= esc(lang('Game.scenarioSituationAlt'), 'attr') ?>"
     <?php if ($pictures !== []): ?>style="<?= implode('; ', $pictures) ?>"<?php endif ?>>
  <section class="scenario-situation stage-box" aria-labelledby="scenario-progress">
    <header class="scenario-head">
      <span class="scenario-progress" id="scenario-progress"><?= esc(lang('Js.scenarioProgress', [1, max(1, count($scenarios))])) ?></span>
      <?php // One bar per story: where the student is and which stories are closed, never how (41 decision 7) ?>
      <span class="scenario-bars" id="scenario-bars" aria-hidden="true"><?php foreach ($scenarios as $i => $_): ?><i<?= $i === 0 ? ' class="is-current"' : '' ?>></i><?php endforeach ?></span>
      <?php // Tries of the scenario on screen (32 K4); engines/skenario.js fills them from the server ?>
      <span class="scenario-tries" id="scenario-tries" role="img" data-label="<?= esc(lang('Game.triesLabel') . ': ' . lang('Game.triesOf', ['{0}', '{1}']), 'attr') ?>" aria-label="<?= esc(lang('Game.triesLabel') . ': ' . lang('Game.triesOf', [$maxTries, $maxTries]), 'attr') ?>">
        <span aria-hidden="true"><?= esc(lang('Game.triesLabel')) ?></span>
        <span class="tries-dots" aria-hidden="true"><?php for ($i = 0; $i < $maxTries; $i++): ?><i class="tries-dot is-left"></i><?php endfor ?></span>
      </span>
    </header>
    <figure class="scenario-figure" id="scenario-figure"<?= empty($first['media']) ? ' hidden' : '' ?>>
      <img id="scenario-img" src="<?= esc((string) ($first['media'] ?? ''), 'attr') ?>" alt="<?= esc(lang('Game.scenarioSituationAlt'), 'attr') ?>" width="1536" height="1024" decoding="async">
      <div class="scenario-voice" id="scenario-voice"></div>
    </figure>
    <?php // 19 dp for a situation longer than 200 characters (41 §3) ?>
    <p class="scenario-prompt<?= mb_strlen((string) ($first['prompt'] ?? '')) > 200 ? ' is-long' : '' ?>" id="scenario-prompt" aria-live="polite"><?= esc((string) ($first['prompt'] ?? '')) ?></p>
  </section>

  <section class="scenario-steps stage-box" aria-live="polite">
    <?php // The two tabs: the number medallion is in each picture, the word is drawn by the game (41 decision 41) ?>
    <ol class="scenario-stepper" id="scenario-stepper" aria-hidden="true">
      <li data-step="action" class="is-current"><?= esc(lang('Js.scenarioTabAction')) ?></li>
      <li data-step="reason"><?= esc(lang('Js.scenarioTabReason')) ?></li>
    </ol>
    <?php if ($instruction !== ''): ?>
      <?php // Step 1 only: ▶ Dengarkan petunjuk above the instruction text, left-aligned, no box (41 decision 33) ?>
      <div class="scenario-hint" id="scenario-hint">
        <?php if ($instructionAudio !== null): ?>
          <?php // Never plays on its own: game.js mounts it with the attempt id, so each tap is logged (challenge_instruction) ?>
          <div class="audio-player instruction-audio sd-player is-hint" data-audio-id="<?= (int) $instructionAudioId ?>" data-src="<?= esc($instructionAudio, 'attr') ?>">
            <div class="audio-controls">
              <button type="button" class="audio-btn" data-action="play"><?= round_icon('play', 'sd-player-art', 'play') ?> <span><?= esc(lang('Game.scenarioHintListen')) ?></span></button>
              <button type="button" class="audio-btn" data-action="pause" aria-label="<?= esc(lang('Game.audioPause'), 'attr') ?>" hidden><?= round_icon('pause', 'sd-player-art', 'pause') ?> <span><?= esc(lang('Js.scenarioListening')) ?></span></button>
            </div>
            <?= $wave(24) ?>
          </div>
        <?php endif ?>
        <p class="scenario-instruction"><?= esc($instruction) ?></p>
      </div>
    <?php endif ?>
    <div class="scenario-body" id="scenario-body"></div>
    <?php // The buttons of the step, inside the right side (41 decision 5); Lanjut once the attempt is closed ?>
    <div class="scenario-actions">
      <button class="sd-btn is-back" id="btn-scenario-back" type="button" hidden><span></span></button>
      <button class="sd-btn" id="btn-scenario-go" type="button" disabled><span></span></button>
      <?php // Shown by challenge.js once the attempt is closed: moves on to the result screen ?>
      <button class="sd-btn" id="btn-next" type="button" hidden><span><?= esc(lang('Game.continue')) ?></span></button>
    </div>
  </section>

  <?php if ($voiced): ?>
    <template id="tpl-scenario-voice">
      <div class="audio-player sd-player is-story">
        <div class="audio-controls">
          <button type="button" class="audio-btn" data-action="play"><?= round_icon('play', 'sd-player-art', 'play') ?> <span data-voice-label></span></button>
          <button type="button" class="audio-btn" data-action="pause" aria-label="<?= esc(lang('Game.audioPause'), 'attr') ?>" hidden><?= round_icon('pause', 'sd-player-art', 'pause') ?> <span data-voice-playing></span></button>
        </div>
        <?= $wave(20) ?>
        <span class="clue-voice-off" aria-hidden="true" title="<?= esc(lang('Js.audioNoSound'), 'attr') ?>"><?= icon('sound-off') ?></span>
      </div>
    </template>
  <?php endif ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
