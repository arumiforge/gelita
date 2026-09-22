<?php
/**
 * 16. Balai Refleksi — `/refleksi` → ReflectionController::index
 *
 * Ringkasan perjalanan, bar ketepatan per wilayah & per jenis tantangan,
 * catatan "paling sering terlewat", lalu formulir kritik & saran: bintang 1–5
 * (radio bawaan, dapat dipakai dengan keyboard) + empat pertanyaan terbuka,
 * minimal dua terisi — diperiksa ulang server.
 *
 * @var array<string, mixed>       $journey
 * @var array<string, mixed>|null  $existing
 * @var array<string, string>      $errors
 * @var string                     $locale
 */
$questions = ['liked_most', 'hardest_part', 'new_learning', 'suggestion'];
$rating    = (int) old('rating');
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.reflection')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.reflection') ?? media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen screen-medium reflection" data-screen="reflection">
  <header class="reflection-hero">
    <?= component('character', ['character' => 'mbah_kedu', 'pose' => 'happy', 'showName' => true]) ?>
    <div>
      <span class="eyebrow"><?= esc(lang('Game.reflection')) ?></span>
      <h1><?= esc(lang('Game.reflectionTitle')) ?></h1>
      <?= component('narration', ['text' => lang('Game.reflectionLead'), 'speaker' => 'mbah_kedu']) ?>
    </div>
  </header>

  <div class="stat-grid">
    <?= component('stat-tile', ['label' => lang('Game.accuracyFirst'), 'value' => fmt_pct($journey['first_pass'], false, 0), 'icon' => 'target']) ?>
    <?= component('stat-tile', ['label' => lang('Game.challengesDone'), 'value' => (string) $journey['completed'], 'icon' => 'check']) ?>
    <?= component('stat-tile', ['label' => lang('Game.totalTime'), 'value' => ms_to_human($journey['duration_ms']), 'icon' => 'clock']) ?>
    <?= component('stat-tile', ['label' => lang('Game.correctItems'), 'value' => (string) $journey['correct_items'], 'icon' => 'star']) ?>
  </div>

  <div class="reflection-bars">
    <section class="panel">
      <h2><?= esc(lang('Game.perRegion')) ?></h2>
      <ul class="bar-list">
        <?php foreach ($journey['regions'] as $row): ?>
          <li class="bar-row">
            <span><?= esc($row['name']) ?></span>
            <span class="progress" aria-hidden="true"><i style="width: <?= (float) $row['accuracy'] ?>%"></i></span>
            <span class="bar-value"><?= esc(fmt_pct($row['accuracy'], false, 0)) ?></span>
          </li>
        <?php endforeach ?>
      </ul>
    </section>
    <section class="panel">
      <h2><?= esc(lang('Game.perEngine')) ?></h2>
      <ul class="bar-list">
        <?php foreach ($journey['engines'] as $row): ?>
          <li class="bar-row">
            <span><?= esc(lang_or('Game.engine_' . $row['engine'], $row['engine'])) ?></span>
            <span class="progress" aria-hidden="true"><i style="width: <?= (float) $row['accuracy'] ?>%"></i></span>
            <span class="bar-value"><?= esc(fmt_pct($row['accuracy'], false, 0)) ?></span>
          </li>
        <?php endforeach ?>
      </ul>
    </section>
  </div>

  <?php if ($journey['most_missed'] !== null): ?>
    <div class="alert alert-info">
      <?= icon('hint') ?>
      <p><b><?= esc(lang('Game.mostMissed')) ?>:</b>
        <?= esc(lang('Game.mostMissedText', [$journey['most_missed']['title'], fmt_num($journey['most_missed']['accuracy'], 0, $locale)])) ?></p>
    </div>
  <?php endif ?>

  <div class="panel-parchment reflection-form">
    <h2><?= icon('message') ?> <?= esc(lang('Game.feedbackTitle')) ?></h2>

    <?php if ($existing !== null): ?>
      <div class="alert alert-info" role="status"><?= icon('check') ?><p><?= esc(lang('Game.feedbackSent')) ?></p></div>
    <?php endif ?>

    <?= $this->include('partials/form-errors') ?>

    <form method="post" action="<?= base_url('refleksi') ?>" class="form" data-min-answers="2">
      <?= csrf_field() ?>

      <fieldset class="rating<?= isset($errors['rating']) ? ' has-error' : '' ?>">
        <legend class="label"><?= esc(lang('Game.ratingLegend')) ?> <span class="req" aria-hidden="true">*</span></legend>
        <div class="rating-stars">
          <?php for ($star = 1; $star <= 5; $star++): ?>
            <label class="rating-star">
              <input type="radio" name="rating" value="<?= $star ?>" required <?= $rating === $star ? 'checked' : '' ?>>
              <?= icon('star') ?>
              <span class="visually-hidden"><?= esc(lang('Game.ratingStar', [$star])) ?></span>
            </label>
          <?php endfor ?>
        </div>
      </fieldset>

      <?php foreach ($questions as $name): ?>
        <div class="field<?= isset($errors[$name]) ? ' has-error' : '' ?>">
          <label for="<?= esc($name, 'attr') ?>"><?= esc(lang('Game.q_' . $name)) ?></label>
          <textarea id="<?= esc($name, 'attr') ?>" name="<?= esc($name, 'attr') ?>" rows="3" maxlength="2000"><?= esc(old($name)) ?></textarea>
        </div>
      <?php endforeach ?>

      <p class="field-help"><?= icon('info') ?> <?= esc(lang('Game.needTwoHint')) ?></p>

      <div class="form-actions">
        <button class="btn btn-primary btn-lg" type="submit"><?= icon('message') ?> <?= esc(lang('Game.sendFeedback')) ?></button>
      </div>
    </form>
  </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?= component('nav-bar', ['nav' => [
    ['label' => lang('Game.mapKedu'), 'href' => base_url('peta'), 'style' => 'quiet', 'arrow' => 'left'],
    ['label' => lang('Game.profile'), 'href' => base_url('profil'), 'style' => 'quiet', 'icon' => 'user'],
]]) ?>
<?= $this->endSection() ?>
