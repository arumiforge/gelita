<?php
/**
 * Attitude scale in Balai Refleksi — `/refleksi/sikap…` → AttitudeController
 * (research revision 10, part 8: 34 §9.2; 17 → Layar, mk-ss-01–04).
 *
 * One navy card on the stage. Four gold dots that grow from Tidak setuju to
 * Sangat setuju, each with its word under it, never a face; the chosen one
 * glows and wears ●. Kembali, a small Lewati link, and Lanjut (active after a
 * choice; without JavaScript always active, Lanjut without a choice keeps the
 * screen empty). The bronze track shows 1/12–12/12. Content Indonesian only (K13).
 *
 * @var string                   $mode     intro, item, review
 * @var array<string, mixed>     $meta     sikap version meta (intro, choices)
 * @var array<string, mixed>|null $item
 * @var int                      $no       0 = example
 * @var string|null              $value
 * @var int                      $answered
 */
$track = is_file(FCPATH . 'assets/ui/loading-track.webp')
    ? ' style="--loading-track: url(&quot;' . esc(asset_url_versioned('ui/loading-track.webp'), 'attr') . '&quot;)"'
    : '';
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.reflection')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.reflection') ?? media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="attitude" data-screen="attitude"<?= $track ?>>
  <div class="attitude-card stage-box">
    <?php if ($mode === 'intro'): ?>
      <h1 class="attitude-title"><?= esc((string) ($meta['intro']['title'] ?? '')) ?></h1>
      <?php foreach ((array) ($meta['intro']['paragraphs'] ?? []) as $paragraph): ?><p><?= esc((string) $paragraph) ?></p><?php endforeach ?>
      <div class="form-actions"><a class="btn btn-primary btn-lg" href="<?= base_url('refleksi/sikap/contoh') ?>"><?= esc(lang('Game.attitudeTry')) ?></a></div>

    <?php elseif ($mode === 'item'): ?>
      <?php if ($no === 0): ?>
        <p class="attitude-example"><?= esc(lang('Game.attitudeExample')) ?></p>
      <?php else: ?>
        <div class="attitude-progress progress has-track" role="progressbar" aria-valuemin="0" aria-valuemax="12" aria-valuenow="<?= $no ?>" aria-label="<?= esc(lang('Game.attitudeProgress', [$no, 12]), 'attr') ?>">
          <span style="width: <?= round($no / 12 * 100, 1) ?>%"></span>
        </div>
        <p class="attitude-count"><?= esc(lang('Game.attitudeProgress', [$no, 12])) ?></p>
      <?php endif ?>
      <form method="post" action="<?= base_url('refleksi/sikap/' . $no) ?>">
        <?= csrf_field() ?>
        <fieldset>
          <legend class="attitude-statement"><?= esc((string) $item['show']['text']) ?></legend>
          <div class="attitude-choices">
            <?php $size = 0 ?>
            <?php foreach ((array) $meta['choices'] as $code => $label): ?>
              <?php $size++ ?>
              <label class="attitude-choice is-size-<?= $size ?>">
                <input type="radio" name="pilihan" value="<?= esc((string) $code, 'attr') ?>"<?= (string) $value === (string) $code ? ' checked' : '' ?>>
                <span class="attitude-dot" aria-hidden="true"></span>
                <span class="attitude-word"><?= esc((string) $label) ?></span>
              </label>
            <?php endforeach ?>
          </div>
        </fieldset>
        <div class="form-actions attitude-actions">
          <button class="btn btn-quiet" type="submit" name="aksi" value="kembali"><?= esc(lang('Game.attitudeBack')) ?></button>
          <button class="btn-link attitude-skip" type="submit" name="aksi" value="lewati"><?= esc(lang('Game.attitudeSkip')) ?></button>
          <button class="btn btn-primary btn-lg" type="submit" name="aksi" value="lanjut"><?= esc(lang('Game.attitudeNext')) ?></button>
        </div>
      </form>

    <?php else: ?>
      <p class="attitude-statement"><?= esc($answered === 12 ? lang('Game.attitudeReviewAll') : lang('Game.attitudeReview', [$answered, 12 - $answered])) ?></p>
      <form method="post" action="<?= base_url('refleksi/sikap/kirim') ?>" class="form-actions attitude-actions">
        <?= csrf_field() ?>
        <a class="btn btn-quiet" href="<?= base_url('refleksi/sikap/12') ?>"><?= esc(lang('Game.attitudeBack')) ?></a>
        <button class="btn btn-primary btn-lg" type="submit"><?= esc(lang('Game.attitudeSend')) ?></button>
      </form>
    <?php endif ?>
  </div>
</section>
<?= $this->endSection() ?>
