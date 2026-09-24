<?php
/**
 * 7. Peta Kedu — `/peta` → MapController::kedu
 *
 * Tiga titik pada posisi `levels.map_x` / `map_y` (persen). Tujuan tautan
 * datang dari `entry` (GameProgress::regionEntryPath()): wilayah yang baru
 * terbuka selalu menuju dialog pembuka, wilayah yang sedang dijelajahi atau
 * tuntas langsung ke peta wilayah. Titik terkunci tetap berupa tautan: server
 * menolaknya dengan toast "Selesaikan wilayah sebelumnya dulu" (map.js tahap 6
 * menampilkan toast yang sama tanpa berpindah halaman).
 *
 * Di bawah peta ada daftar kartu wilayah yang sama — terbaca di ponsel,
 * pembaca layar, dan saat gambar peta belum diunggah.
 *
 * Panduan peta berisi narasi Jaka (dialogues `map_intro`, game/narrator.js).
 * Datang dari gerbang atau akhir cerita pembuka (flash `curtain=map`): tirai
 * "Membuka Peta Kedu" memuat aset peta, lalu ketukannya memulai musik peta
 * dan narasi otomatis (mode `external`). Kunjungan lain: teks utuh dengan
 * tombol ▶ (mode `manual`). Tanpa slide `map_intro`: balon Game.mapLead.
 *
 * @var list<array<string, mixed>> $levels
 * @var array<string, mixed>       $progress
 * @var string                     $unlockMode
 * @var string                     $locale
 * @var list<array<string, mixed>> $story          dialogues map_intro
 * @var bool                       $curtain        tampilkan tirai peta
 * @var list<string>               $curtainAssets  URL yang dimuat tirai
 */
$mapSrc = media_key_src('map.kedu');
$icons  = ['open' => 'lantern', 'in_progress' => 'lantern', 'completed' => 'star', 'locked' => 'lock'];
$href   = static fn (array $l): string => base_url($l['entry']);

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

<?php if (! empty($curtain)): ?>
<?= $this->section('overlay') ?>
<?= component('curtain', ['kind' => 'map', 'preload' => $curtainAssets ?? []]) ?>
<?= $this->endSection() ?>
<?php endif ?>

<?= $this->section('content') ?>
<section class="screen map-screen map-kedu" data-screen="map-kedu" data-unlock-mode="<?= esc($unlockMode, 'attr') ?>">
  <header class="map-head">
    <h1><?= esc(lang('Game.mapKedu')) ?></h1>
  </header>

  <div class="map-layout">
    <?php if (($story ?? []) === []): ?>
      <aside class="map-guide">
        <?= component('character', ['character' => 'jaka', 'pose' => 'idle']) ?>
        <?= component('narration', ['text' => lang('Game.mapLead'), 'speaker' => 'jaka', 'tail' => 'down']) ?>
      </aside>
    <?php else: ?>
      <?php $storyTotal = count($story); ?>
      <aside class="map-guide map-story narrator" aria-labelledby="map-story-title"
             data-narrator data-context="map_intro" data-prefix="peta-" data-keyboard="0"
             data-mode="<?= ! empty($curtain) ? 'external' : 'manual' ?>">
        <h2 class="visually-hidden" id="map-story-title"><?= esc(lang('Game.mapStoryTitle')) ?></h2>
        <div class="narrator-fx" data-narrator-fx aria-hidden="true"></div>
        <ol class="slides map-story-slides">
          <?php foreach ($story as $index => $line): ?>
            <?php
            $n         = $index + 1;
            $character = (string) ($line['character_code'] ?? 'jaka');
            $pose      = (string) ($line['pose'] ?? '') ?: 'idle';
            $effect    = (string) ($line['effect'] ?? '');
            $title     = tr($line, 'title', $locale);
            $text      = tr($line, 'text', $locale);
            $audioId   = (int) ($locale === 'en' ? ($line['audio_en_asset_id'] ?? 0) : ($line['audio_id_asset_id'] ?? 0));
            ?>
            <li class="slide map-story-slide" id="peta-<?= $n ?>" data-index="<?= $n ?>"
                data-character="<?= esc($character, 'attr') ?>" data-pose="<?= esc($pose, 'attr') ?>"
                <?= $effect !== '' ? 'data-effect="' . esc($effect, 'attr') . '"' : '' ?>
                aria-label="<?= esc(lang('Game.slideOf', [$n, $storyTotal]), 'attr') ?>">
              <?php if ($character !== 'narator'): ?>
                <?= component('character', ['character' => $character, 'pose' => $pose, 'class' => 'pose-' . $pose]) ?>
              <?php endif ?>
              <blockquote class="narration narration-down map-story-line" data-narrator-advance>
                <cite><?= esc(lang_or('Game.char_' . $character, $character)) ?><?php if ($title !== ''): ?><span class="map-story-title">· <?= esc($title) ?></span><?php endif ?></cite>
                <p class="slide-text"><?= esc($text) ?></p>
              </blockquote>
              <?php if (($audioSrc = audio_src($audioId ?: null)) !== null): ?>
                <?= component('audio-player', ['audioId' => $audioId, 'audioSrc' => $audioSrc, 'transcript' => $text]) ?>
              <?php endif ?>
              <nav class="map-story-nav" aria-label="<?= esc(lang('Game.slideOf', [$n, $storyTotal]), 'attr') ?>">
                <?php if ($n > 1): ?>
                  <a class="icon-btn" href="#peta-<?= $n - 1 ?>" aria-label="<?= esc(lang('Game.narratorPrev'), 'attr') ?>"><?= icon('left') ?></a>
                <?php endif ?>
                <span class="num"><?= $n ?>/<?= $storyTotal ?></span>
                <?php if ($n < $storyTotal): ?>
                  <a class="icon-btn" href="#peta-<?= $n + 1 ?>" aria-label="<?= esc(lang('Game.narratorNext'), 'attr') ?>"><?= icon('right') ?></a>
                <?php endif ?>
              </nav>
            </li>
          <?php endforeach ?>
        </ol>
        <?= component('narrator-controls') ?>
      </aside>
    <?php endif ?>

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
                style="left: <?= (float) $level['map_x'] ?>%; top: <?= (float) $level['map_y'] ?>%"
                <?= $level['status'] !== 'locked' && ! empty($level['background']) ? 'data-preload="' . esc($level['background'], 'attr') . '"' : '' ?>>
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
