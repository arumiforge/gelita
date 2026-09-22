<?php
/**
 * 7. Peta Kedu — `/peta` → MapController::kedu
 *
 * Tiga titik pada posisi `levels.map_x` / `map_y` (persen). Titik terbuka
 * menuju dialog pembuka bila wilayah belum dijelajahi, selain itu langsung ke
 * peta wilayah. Titik terkunci tetap berupa tautan: server menolaknya dengan
 * toast "Selesaikan wilayah sebelumnya dulu" (map.js tahap 6 menampilkan toast
 * yang sama tanpa berpindah halaman).
 *
 * Di bawah peta ada daftar kartu wilayah yang sama — terbaca di ponsel,
 * pembaca layar, dan saat gambar peta belum diunggah.
 *
 * @var list<array<string, mixed>> $levels
 * @var array<string, mixed>       $progress
 * @var string                     $unlockMode
 * @var string                     $locale
 */
$mapSrc = media_key_src('map.kedu');
$icons  = ['open' => 'lantern', 'in_progress' => 'lantern', 'completed' => 'star', 'locked' => 'lock'];
$href   = static fn (array $l): string => base_url(($l['status'] === 'open' ? 'dialog/' : 'wilayah/') . $l['code']);

// Pustaka: wilayah terakhir yang sudah terbuka
$libraryCode = null;
foreach ($levels as $level) {
    if ($level['status'] !== 'locked') {
        $libraryCode = $level['code'];
    }
}
$allDone = $progress['shards_total'] > 0 && $progress['completed_nodes'] >= $progress['shards_total'];
$points  = implode(' ', array_map(static fn (array $l): string => (float) $l['map_x'] . ',' . (float) $l['map_y'], $levels));
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc(lang('Game.mapKedu')) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_key_src('bg.map') ?? '' ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="screen map-screen map-kedu" data-screen="map-kedu" data-unlock-mode="<?= esc($unlockMode, 'attr') ?>">
  <header class="map-head">
    <h1><?= esc(lang('Game.mapKedu')) ?></h1>
  </header>

  <div class="map-layout">
    <aside class="map-guide">
      <?= component('character', ['character' => 'jaka', 'pose' => 'idle']) ?>
      <?= component('narration', ['text' => lang('Game.mapLead'), 'speaker' => 'jaka', 'tail' => 'down']) ?>
    </aside>

    <div class="map-frame">
      <div class="map-canvas<?= $mapSrc === null ? ' is-drawn' : '' ?>" style="--map-ratio: 1400 / 900">
        <?php if ($mapSrc !== null): ?>
          <img class="map-img" src="<?= esc($mapSrc, 'attr') ?>" alt="<?= esc(lang('Game.mapImageAlt', [lang('Game.mapKedu')]), 'attr') ?>">
        <?php endif ?>
        <svg class="map-route" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
          <polyline points="<?= esc($points, 'attr') ?>"/>
        </svg>
        <ol class="map-points">
          <?php foreach ($levels as $level): ?>
            <li class="map-point is-<?= esc($level['status'], 'attr') ?>"
                style="left: <?= (float) $level['map_x'] ?>%; top: <?= (float) $level['map_y'] ?>%">
              <a class="map-pin" href="<?= esc($href($level), 'attr') ?>"
                 <?= $level['status'] === 'locked' ? 'aria-disabled="true" data-locked-message="' . esc(lang('Game.levelLocked'), 'attr') . '"' : '' ?>>
                <span class="pin-icon" aria-hidden="true"><?= icon($icons[$level['status']] ?? 'lantern') ?></span>
                <span class="pin-label">
                  <b><?= esc($level['name']) ?></b>
                  <small><?= esc(lang('Game.levelStatus_' . $level['status'])) ?> · <span class="num"><?= esc($level['completed_nodes']) ?>/<?= esc($level['total_nodes']) ?></span></small>
                </span>
              </a>
            </li>
          <?php endforeach ?>
        </ol>
      </div>
    </div>
  </div>

  <ol class="region-list">
    <?php foreach ($levels as $level): ?>
      <li class="region-card panel is-<?= esc($level['status'], 'attr') ?>">
        <div class="region-card-head">
          <span class="region-seq num" aria-hidden="true"><?= esc($level['sequence']) ?></span>
          <div>
            <span class="eyebrow"><?= esc(lang('Game.levelOrder', [$level['sequence']])) ?> · <?= esc(lang_or('Game.difficulty_' . $level['difficulty'], $level['difficulty'])) ?></span>
            <h2><?= esc($level['name']) ?></h2>
          </div>
          <span class="badge is-<?= esc($level['status'], 'attr') ?>"><?= icon($icons[$level['status']] ?? 'lantern') ?> <?= esc(lang('Game.levelStatus_' . $level['status'])) ?></span>
        </div>
        <div class="region-card-progress">
          <div class="progress" aria-hidden="true"><i style="width: <?= $level['total_nodes'] > 0 ? (int) round($level['completed_nodes'] / $level['total_nodes'] * 100) : 0 ?>%"></i></div>
          <span><?= esc(lang('Game.nodesProgress', [$level['completed_nodes'], $level['total_nodes']])) ?></span>
          <?= stars_html((int) $level['stars']) ?>
        </div>
        <?php if ($level['status'] !== 'locked'): ?>
          <a class="btn <?= $level['status'] === 'completed' ? 'btn-ghost' : 'btn-primary' ?>" href="<?= esc($href($level), 'attr') ?>">
            <?= esc($level['status'] === 'open' ? lang('Game.start') : lang('Game.continue')) ?> <?= icon('right') ?>
          </a>
        <?php else: ?>
          <p class="region-locked muted"><?= icon('lock') ?> <?= esc(lang('Game.levelLocked')) ?></p>
        <?php endif ?>
      </li>
    <?php endforeach ?>
  </ol>
</section>
<?= $this->endSection() ?>

<?= $this->section('nav') ?>
<?php
$nav = [];
if ($libraryCode !== null) {
    $nav[] = ['label' => lang('Game.library'), 'href' => base_url('pustaka/' . $libraryCode), 'style' => 'ghost', 'arrow' => 'left', 'icon' => 'book'];
}
$nav[] = $allDone
    ? ['label' => lang('Game.reflection'), 'href' => base_url('refleksi'), 'style' => 'primary', 'arrow' => 'right']
    : ['label' => lang('Game.profile'), 'href' => base_url('profil'), 'style' => 'quiet', 'icon' => 'user'];
?>
<?= component('nav-bar', ['nav' => $nav]) ?>
<?= $this->endSection() ?>
