<?php
/**
 * Pemutar narasi: putar / jeda / ulangi / transkrip.
 *
 * Transkrip WAJIB ada untuk setiap audio: anak yang kesulitan mendengar,
 * kelas tanpa pengeras suara, dan berkas yang gagal dimuat semuanya butuh
 * teks penggantinya. Bila audio belum `approved` ($audioSrc kosong),
 * komponen hanya merender kotak transkrip tanpa tombol.
 *
 * Tombol putar/jeda/ulangi dan telemetry `/api/audio-events` diberi perilaku
 * pada tahap 6. Transkrip memakai <details>, jadi selalu dapat dibuka —
 * dengan atau tanpa JavaScript; tanpa JavaScript, <audio controls> bawaan
 * browser menggantikan tombol-tombolnya.
 *
 * @var int|null    $audioId
 * @var string|null $audioSrc
 * @var string      $transcript
 * @var bool|null   $open        transkrip langsung terbuka
 */
$audioId    = (int) ($audioId ?? 0);
$audioSrc   = $audioSrc ?? null;
$transcript = trim((string) ($transcript ?? ''));
?>
<?php if ($audioSrc === null || $audioSrc === ''): ?>
  <?php if ($transcript !== ''): ?>
    <div class="audio-player is-text-only" data-audio-id="<?= $audioId ?>">
      <div class="audio-transcript-box"><?= esc($transcript) ?></div>
    </div>
  <?php endif ?>
<?php else: ?>
  <div class="audio-player" data-audio-id="<?= $audioId ?>" data-src="<?= esc($audioSrc, 'attr') ?>">
    <div class="audio-controls">
      <button type="button" class="audio-btn" data-action="play" aria-label="<?= esc(lang('Game.audioPlay'), 'attr') ?>"><?= icon('play') ?></button>
      <button type="button" class="audio-btn" data-action="pause" aria-label="<?= esc(lang('Game.audioPause'), 'attr') ?>" hidden><?= icon('pause') ?></button>
      <button type="button" class="audio-btn" data-action="replay" aria-label="<?= esc(lang('Game.audioReplay'), 'attr') ?>"><?= icon('replay') ?></button>
      <div class="audio-bar" aria-hidden="true"><i></i></div>
    </div>
    <details class="audio-transcript"<?= ! empty($open) ? ' open' : '' ?>>
      <summary class="audio-btn" data-action="transcript"><?= icon('text') ?> <?= esc(lang('Game.audioTranscript')) ?></summary>
      <div class="audio-transcript-box"><?= esc($transcript) ?></div>
    </details>
    <noscript><audio class="audio-native" controls preload="none" src="<?= esc($audioSrc, 'attr') ?>"></audio></noscript>
  </div>
<?php endif ?>
