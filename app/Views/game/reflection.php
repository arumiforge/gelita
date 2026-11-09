<?php
/**
 * 16. Balai Refleksi — `/refleksi` → ReflectionController::index
 *
 * Ringkasan perjalanan, bar ketepatan per wilayah & per jenis tantangan,
 * catatan "paling sering terlewat", lalu formulir kritik & saran: bintang 1–5
 * (radio bawaan, dapat dipakai dengan keyboard) + empat pertanyaan terbuka,
 * minimal dua terisi — diperiksa ulang server.
 *
 * Redesign (owner, 21 October 2026): the left column is a navy card with a
 * thin gold edge (as the Pustaka Kedu cards). Its heading is the owner's
 * lettered Balai Refleksi plate; Mbah Kedu speaks from his round avatar
 * (`ui.kedu-avatar`, never a pose frame) with plain words and no name line,
 * since it is clearly him; the per-region and per-challenge bars run on the
 * bronze track (`loading-track.webp`).
 *
 * @var array<string, mixed>       $journey
 * @var array<string, mixed>|null  $existing
 * @var array<string, string>      $errors
 * @var string                     $locale
 */
$questions = ['liked_most', 'hardest_part', 'new_learning', 'suggestion'];
$rating    = (int) old('rating');
// Part 8 (34 §9.4): during a study every feedback field may stay empty
$studyFeedback = ! empty($studyFeedback);
$plate     = 'ui/btn-reflection' . ($locale === 'en' ? '-en' : '') . '.webp';
$plateSize = is_file(FCPATH . 'assets/' . $plate) ? @getimagesize(FCPATH . 'assets/' . $plate) : false;
$track     = is_file(FCPATH . 'assets/ui/loading-track.webp')
    ? ' style="--loading-track: url(&quot;' . esc(asset_url_versioned('ui/loading-track.webp'), 'attr') . '&quot;)"'
    : '';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.reflection')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.reflection') ?? media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="reflection" data-screen="reflection"<?= $track ?>>
  <?php // Kiri 32, 72, 520 × 576: ringkasan + Mbah Kedu; kanan 576, 72, 672 × 576: formulir, Kirim tetap di bawah (Dok 3 §4) ?>
  <aside class="reflection-side stage-box stage-scroll">
  <header class="reflection-hero">
    <h1 class="reflection-plate">
      <?php if ($plateSize !== false): ?>
        <img src="<?= esc(asset_url_versioned($plate), 'attr') ?>" alt="<?= esc(lang('Game.reflection'), 'attr') ?>" width="<?= $plateSize[0] ?>" height="<?= $plateSize[1] ?>" decoding="async" draggable="false">
      <?php else: ?>
        <?= esc(lang('Game.reflection')) ?>
      <?php endif ?>
    </h1>
    <p class="reflection-title"><?= esc(lang('Game.reflectionTitle')) ?></p>
    <div class="reflection-speaker">
      <span class="reflection-avatar"><?= component('partials/kedu-portrait', ['src' => media_key_src('ui.kedu-avatar'), 'name' => lang('Game.char_mbah_kedu')]) ?></span>
      <p class="reflection-words"><?= esc(lang('Game.reflectionLead')) ?></p>
      <?php if ($studyFeedback): ?><p class="reflection-words"><?= esc(lang('Game.attitudeThanks')) ?></p><?php endif ?>
    </div>
  </header>

  <div class="stat-grid">
    <?= component('stat-tile', ['label' => lang('Game.firstPass'), 'value' => lang('Game.firstTryCount', [$journey['first_pass'], $journey['scorable']]), 'icon' => 'target']) ?>
    <?= component('stat-tile', ['label' => lang('Game.challengesDone'), 'value' => (string) $journey['completed'], 'icon' => 'check']) ?>
    <?= component('stat-tile', ['label' => lang('Game.totalTime'), 'value' => ms_to_human($journey['duration_ms']), 'icon' => 'clock']) ?>
    <?= component('stat-tile', ['label' => lang('Game.correctItems'), 'value' => (string) $journey['correct_items'], 'icon' => 'star']) ?>
  </div>

  <div class="reflection-bars">
    <section>
      <h2><?= esc(lang('Game.perRegion')) ?></h2>
      <ul class="bar-list">
        <?php foreach ($journey['regions'] as $row): ?>
          <li class="bar-row">
            <span><?= esc($row['name']) ?></span>
            <span class="progress" aria-hidden="true"><i style="width: <?= $row['stars_max'] > 0 ? round(100 * $row['stars'] / $row['stars_max'], 1) : 0 ?>%"></i></span>
            <span class="bar-value"><?= star_img() ?> <?= esc(lang('Game.triesOf', [$row['stars'], $row['stars_max']])) ?></span>
          </li>
        <?php endforeach ?>
      </ul>
    </section>
    <section>
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
        <?= esc(lang('Game.mostMissedText', [$journey['most_missed']['title'], $journey['most_missed']['first'], $journey['most_missed']['scorable']])) ?></p>
    </div>
  <?php endif ?>

  </aside>

  <div class="reflection-form stage-box">
    <h2><?= icon('message') ?> <?= esc(lang('Game.feedbackTitle')) ?></h2>

    <?php if ($existing !== null): ?>
      <div class="alert alert-info" role="status"><?= icon('check') ?><p><?= esc(lang('Game.feedbackSent')) ?></p></div>
    <?php endif ?>

    <?= $this->include('partials/form-errors') ?>

    <form method="post" action="<?= base_url('refleksi') ?>" class="form reflection-fields-form" data-min-answers="<?= $studyFeedback ? 0 : 2 ?>">
      <?= csrf_field() ?>
      <div class="reflection-fields stage-scroll">

      <fieldset class="rating<?= isset($errors['rating']) ? ' has-error' : '' ?>">
        <legend class="label"><?= esc(lang('Game.ratingLegend')) ?> <?php if (! $studyFeedback): ?><span class="req" aria-hidden="true">*</span><?php endif ?></legend>
        <div class="rating-stars">
          <?php for ($star = 1; $star <= 5; $star++): ?>
            <label class="rating-star">
              <input type="radio" name="rating" value="<?= $star ?>"<?= $studyFeedback ? '' : ' required' ?> <?= $rating === $star ? 'checked' : '' ?>>
              <?= star_img(false) ?>
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

      <p class="field-help"><?= icon('info') ?> <?= esc(lang($studyFeedback ? 'Game.reflectionAllOptional' : 'Game.needTwoHint')) ?></p>
      </div>

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
