<?php
/**
 * Kerangka layar tantangan, sama untuk keempat engine. Di-include oleh
 * game/challenge/{engine}.php; isi arena datang dari section `arena`, isi
 * tambahan panel instruksi dari section `aside` (opsional).
 *
 * Panggung 16:9 (Dok 3 §5): tanpa tokoh Jaka, isi selebar panggung. Since the
 * uniform HUD (41 §4, decisions 13, 14, 27, 36; 19 November 2026) the header is
 * the screen's only top row, 32, 16, 1216 × 72: the number medallion
 * (`assets/ui/challenge-badge.webp`) fused with the title capsule (title, and
 * the variant title or, in Skenario Digital, the city beside it), then one
 * capsule for timer | ✓/✗ | Kesempatan, Bantuan, the sound button (moved here
 * from the HUD, which draws nothing on challenge pages: hudLayout `challenge`)
 * and the round Keluar. No logo, no menu, no language switch. The arena
 * starts at y 104.
 * The answer choices (Isi Rumpang words, Benar/Salah) stand on the navy plate
 * (`--plate-navy` from #stage; U-02 since 19 November 2026).
 * `--rumpang-plate` (Isi Rumpang only) is the calm instruction plate of
 * 39 R-26 (16 November 2026): ornaments at both ends, a plain stretchable middle. .arena-panel dan .arena hanya pembungkus
 * (display: contents), jadi panel engine memakai koordinat panggung langsung
 * (stage.css §Tantangan). Panel instruksi (.challenge-aside) diletakkan per
 * engine: panel kiri Susun Gambar, baris pertama arena Isi Rumpang dan Benar
 * atau Salah, panel kanan Cari Objek.
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
$maxTries   = max(1, (int) ($payload['max_tries'] ?? config('Gelita')->maxTries));
$triesLeft  = max(0, $maxTries - (int) ($payload['tries_used'] ?? 0));
// "Kesempatan: {0} dari {1}" — challenge.js mengisi angkanya setiap respons server
$triesAria  = lang('Game.triesLabel') . ': ' . lang('Game.triesOf', ['{0}', '{1}']);
// Part 6 (32 §8.2, K10): instruction narration, only through ▶ and only when approved
$instructionAudioId  = $locale === 'en' ? $node->audio_instruction_en_id : $node->audio_instruction_id;
$instructionAudioSrc = audio_src($instructionAudioId);
// Part 7 (33 §8.4, M-07): one name per challenge kind in the header; the variant title is a small subtitle
$kindName     = lang_or('Game.engine_' . $engine, $node->text('title', $locale));
$variantTitle = trim((string) ($payload['variant']['title'] ?? ''));
// Skenario Digital: the city beside the title instead of the variant title (41 decisions 19, 22)
$subtitle = $engine === 'skenario' ? $region : ($variantTitle !== $kindName ? $variantTitle : '');
// ✗ is hidden in Skenario Digital only (41 decision 9)
$showWrong = $engine !== 'skenario';
?>
<section class="challenge challenge-<?= esc($engine, 'attr') ?>"
         data-screen="challenge"
         data-engine="<?= esc($engine, 'attr') ?>"
         data-variant="<?= esc($node->variant_code ?? '', 'attr') ?>"
         data-attempt="<?= esc($attempt->id, 'attr') ?>"
         data-node="<?= esc($node->id, 'attr') ?>"
         data-level="<?= esc($level->code, 'attr') ?>"
         data-level-id="<?= esc($level->id, 'attr') ?>"
         data-region-name="<?= esc($region, 'attr') ?>"
         data-sequence="<?= esc($sequence, 'attr') ?>"
         data-exit-url="<?= esc(base_url('wilayah/' . $level->code), 'attr') ?>"
         <?php if ($engine === 'rumpang' && is_file(FCPATH . 'assets/ui/rumpang-instruction-plate.webp')): ?>style="--rumpang-plate: url(&quot;<?= esc(asset_url_versioned('ui/rumpang-instruction-plate.webp'), 'attr') ?>&quot;)"<?php endif ?>>

  <div class="arena-panel">
    <?php // One top row (41 §4): medallion fused with the title capsule, stats, Bantuan, sound, Keluar ?>
    <header class="challenge-head stage-box">
      <div class="challenge-title-plate<?= $engine === 'skenario' ? ' is-inline' : '' ?>">
        <div class="badge-pos" aria-hidden="true">
          <img src="<?= esc(asset_url_versioned('ui/challenge-badge.webp'), 'attr') ?>" alt="" width="192" height="192" decoding="async" draggable="false">
          <span class="badge-pos-no"><?= esc($sequence) ?></span>
        </div>
        <div class="challenge-info">
          <?php // The region · position · kind line left the screen (owner); screen readers still hear it ?>
          <span class="visually-hidden"><?= esc($region) ?> · <?= esc(lang('Game.challengeOf', [$sequence, $totalNodes])) ?> · <?= esc(engine_label($engine, $node->variant_code)) ?></span>
          <h1 class="title"><?= esc($kindName) ?></h1>
          <?php if ($subtitle !== ''): ?>
            <p class="challenge-subtitle"><?= esc($subtitle) ?></p>
          <?php endif ?>
        </div>
      </div>
      <div class="challenge-chips">
        <div class="challenge-stats">
          <?php // Part 7 (33 §7.1): no clock in research mode; core/timer.js does nothing without #timer ?>
          <?php if (empty($researchMode)): ?>
          <span class="stat-timer" title="<?= esc(lang('Game.timer'), 'attr') ?>">
            <?= round_icon('clock', 'stat-art', 'clock') ?><span class="visually-hidden"><?= esc(lang('Game.timer')) ?></span> <b id="timer" class="num">0:00</b>
          </span>
          <?php endif ?>
          <span class="chip-tally">
            <span class="tally-ok"><?= round_icon('right', 'stat-art', 'check') ?><span class="visually-hidden"><?= esc(lang('Game.countCorrect')) ?></span> <b id="count-correct" class="num">0</b></span>
            <?php if ($showWrong): ?>
              <span class="tally-sep" aria-hidden="true">/</span>
              <span class="tally-bad"><?= round_icon('wrong', 'stat-art', 'cross') ?><span class="visually-hidden"><?= esc(lang('Game.countWrong')) ?></span> <b id="count-wrong" class="num">0</b></span>
            <?php endif ?>
          </span>
          <?php if (! $node->triesPerItem()): ?>
            <?php // Penanda 3 kesempatan engine batch (Dok 2 §5.3): bulatan terisi = sisa; per-item engines draw their own ?>
            <span class="chip-tries" id="tries" role="img" data-left="<?= $triesLeft ?>"
                  data-label="<?= esc($triesAria, 'attr') ?>"
                  aria-label="<?= esc(lang('Game.triesLabel') . ': ' . lang('Game.triesOf', [$triesLeft, $maxTries]), 'attr') ?>">
              <span class="tries-label" aria-hidden="true"><?= esc(lang('Game.triesLabel')) ?></span>
              <span class="tries-dots" aria-hidden="true"><?php for ($i = 0; $i < $maxTries; $i++): ?><i class="tries-dot<?= $i < $triesLeft ? ' is-left' : '' ?>"></i><?php endfor ?></span>
            </span>
          <?php endif ?>
        </div>
        <button type="button" class="chip chip-btn" id="btn-hint" data-hints="<?= $hints ?>" hidden>
          <?= round_icon('help', 'chip-art', 'hint') ?> <?= esc(lang('Game.hint')) ?>
        </button>
        <?php // The HUD's sound button (game/hud.js finds #btn-sound anywhere), the one HUD control in a challenge (41 decision 16) ?>
        <button type="button" class="hud-ctl challenge-ctl" id="btn-sound" aria-pressed="true"
                aria-label="<?= esc(lang('Game.sound'), 'attr') ?>" data-tip="<?= esc(lang('Game.sound'), 'attr') ?>">
          <?= round_icon('sound', 'hud-ctl-art icon-on', 'sound') ?><?= round_icon('mute', 'hud-ctl-art icon-off', 'sound-off') ?>
        </button>
        <?php // Keluar: the round button only, its word as the tip (41 decision 23); still confirmed ?>
        <details class="confirm confirm-down challenge-exit">
          <summary class="hud-ctl challenge-ctl" aria-label="<?= esc(lang('Game.exitChallenge'), 'attr') ?>" data-tip="<?= esc(lang('Game.exitChallenge'), 'attr') ?>"><?= round_icon('logout', 'hud-ctl-art', 'logout') ?></summary>
          <div class="confirm-box" role="group" aria-labelledby="exit-title">
            <h2 id="exit-title"><?= esc(lang('Game.exitConfirmTitle')) ?></h2>
            <p><?= esc(lang('Game.exitConfirmText')) ?></p>
            <a class="btn btn-danger" href="<?= base_url('wilayah/' . $level->code) ?>" data-exit-confirm><?= esc(lang('Game.exitConfirmYes')) ?></a>
          </div>
        </details>
      </div>
    </header>

    <div class="arena">
      <aside class="challenge-aside stage-box stage-scroll">
        <?php // Skenario Digital draws its instruction in the right side (skenario.php, 41 decision 33) ?>
        <?php if ($engine !== 'skenario' && $node->text('instruction', $locale) !== ''): ?>
          <?php // The round "i" button of the owner's sheet beside the text (owner, 22 October 2026) ?>
          <?php // Part 7 (33 §8.3, M-06): Isi Rumpang and Benar atau Salah show it as a card above the passage ?>
          <p class="instruction<?= in_array($engine, ['rumpang', 'boleh'], true) ? ' is-card' : '' ?>"><?= round_icon('info', 'instruction-art') ?: '<span class="instruction-mark" aria-hidden="true">i</span>' ?> <span class="instruction-text"><?= esc($node->text('instruction', $locale)) ?></span></p>
          <?php if ($instructionAudioSrc !== null): ?>
            <?php // Never plays on its own: core/audio.js mounts it with the attempt id, so each tap is logged (challenge_instruction) ?>
            <div class="audio-player instruction-audio" data-audio-id="<?= (int) $instructionAudioId ?>" data-src="<?= esc($instructionAudioSrc, 'attr') ?>">
              <div class="audio-controls">
                <button type="button" class="audio-btn" data-action="play" aria-label="<?= esc(lang('Game.instructionListen'), 'attr') ?>" title="<?= esc(lang('Game.instructionListen'), 'attr') ?>"><?= icon('play') ?></button>
                <button type="button" class="audio-btn" data-action="pause" aria-label="<?= esc(lang('Game.audioPause'), 'attr') ?>" hidden><?= icon('pause') ?></button>
              </div>
            </div>
          <?php endif ?>
        <?php endif ?>
        <?= $this->renderSection('aside') ?>
      </aside>
      <noscript><div class="alert alert-warn arena-alert stage-box"><?= icon('warn') ?><p><?= esc(lang('Game.needsJs')) ?></p></div></noscript>

      <?php if (($payload['items'] ?? $payload['objects'] ?? $payload['scenarios'] ?? []) === []): ?>
        <div class="empty-state arena-empty stage-box"><?= icon('info') ?><p><?= esc(lang('Game.noItems')) ?></p></div>
      <?php else: ?>
        <div class="arena-body"><?= $this->renderSection('arena') ?></div>
      <?php endif ?>
    </div>
  </div>
</section>
<script type="application/json" id="challenge-data"><?= json_encode(
    $payload,
    JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT,
) ?></script>
