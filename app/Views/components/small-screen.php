<?php
/**
 * Small screens (research revision 10, part 7: 33 §7.2, K7). Two parts,
 * both hidden until game/screen-check.js measures the 16:9 stage:
 *
 * - `.small-screen-gate`: research mode and a stage narrower than the
 *   study's `min_stage_px` (960 by default). Covers the page like the rotate
 *   gate (the rest of <body> is `inert`, focus moves here) and goes away by
 *   itself once the window is wide enough. No skip button.
 * - `.small-screen-warn`: outside research mode, a warning that can be
 *   closed, once per browser session (`sessionStorage`).
 *
 * The server refuses small screens at /token and /daftar step 0 too
 * (`stage_w`, audit `small_screen_refused`); without JavaScript nothing
 * shows here and the server accepts an empty `stage_w`.
 */
?>
<div class="small-screen-gate" role="alertdialog" aria-modal="true" aria-labelledby="small-screen-title" aria-describedby="small-screen-text" tabindex="-1" hidden data-small-screen-gate>
  <div class="small-screen-card">
    <h2 id="small-screen-title"><?= esc(lang('Game.smallScreenTitle')) ?></h2>
    <p id="small-screen-text"><?= esc(lang('Game.smallScreenBody')) ?></p>
  </div>
</div>
<div class="small-screen-warn" role="status" hidden data-small-screen-warn>
  <p><?= esc(lang('Game.smallScreenWarn')) ?></p>
  <button type="button" class="btn btn-ghost btn-sm" data-small-screen-dismiss><?= esc(lang('Game.smallScreenDismiss')) ?></button>
</div>
