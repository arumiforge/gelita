<?php
/**
 * Redesigned HUD (result screen redesign, 18 October 2026; mockup in
 * gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung). A page
 * opts in with `<?= $this->section('hudStyle') ?>menu<?= $this->endSection() ?>`;
 * every other page keeps components/hud.
 *
 * Left : GELITA logo (`ui.logo-hero`, else the text brand) and the region name.
 * Right: three round controls on the `ui.result-hud-control` skin:
 *        - language: popover with the flag icons (`ui.lang.*`), the same
 *          POST form as components/lang-switch (class `lang-switch`, so
 *          game/hud.js and the challenge frame keep working);
 *        - sound (#btn-sound, game/hud.js);
 *        - menu: profile, Peta Kedu, fullscreen (#btn-fullscreen,
 *          core/stage.js; hidden when unsupported), and logout.
 * No shard lantern here: the result panel shows the shard count itself.
 *
 * Both popovers are <details data-popover>: they open without JavaScript;
 * game/hud.js adds Escape, outside click, one-open-at-a-time, the close
 * button, and focus back to the trigger.
 *
 * @var array<string, mixed>|null $participant safe shape (toSafeArray)
 * @var array<string, mixed>|null $progress
 * @var \App\Entities\Level|null   $level
 */
$inGame  = isset($participant, $progress) && is_array($participant) && is_array($progress);
$locale  = service('request')->getLocale();
$locales = config('Gelita')->locales;
$region  = isset($level) && $level instanceof \App\Entities\Level ? $level->text('name', $locale) : null;
$logo    = media_key_src('ui.logo-hero') ?? media_key_src('ui.logo');
$flags   = ['id' => media_key_src('ui.lang.id'), 'en' => media_key_src('ui.lang.en')];

$langName = static fn (string $code): string => lang_or('Game.lang_' . $code, strtoupper($code));
$userName = $inGame ? (string) ($participant['display_name'] ?: $participant['username']) : '';
?>
<header class="hud hud-menu-style"<?= $inGame ? ' data-in-game' : '' ?><?= skin_style_attr(['skin-hud' => 'ui.result-hud-control']) ?>>
  <div class="hud-left hud-identity">
    <?php if ($logo !== null): ?>
      <img class="hud-identity-logo" src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>" decoding="async" draggable="false">
    <?php else: ?>
      <span class="hud-brand"><?= esc(lang('Game.appName')) ?></span>
    <?php endif ?>
    <?php if ($region !== null): ?>
      <span class="hud-identity-region"><?= esc($region) ?></span>
    <?php endif ?>
  </div>

  <div class="hud-right hud-controls">
    <details class="hud-pop hud-lang" data-popover>
      <summary class="hud-ctl" aria-label="<?= esc(lang('Game.language') . ': ' . $langName($locale), 'attr') ?>"
               title="<?= esc(lang('Game.language'), 'attr') ?>">
        <?php if ($flags[$locale] ?? null): ?>
          <img class="hud-ctl-flag" src="<?= esc($flags[$locale], 'attr') ?>" alt="" draggable="false">
        <?php else: ?>
          <span class="hud-ctl-code" aria-hidden="true"><?= esc(strtoupper($locale)) ?></span>
        <?php endif ?>
      </summary>
      <div class="hud-sheet hud-lang-sheet">
        <form class="lang-switch hud-lang-list" method="post" action="<?= base_url('bahasa') ?>"
              aria-label="<?= esc(lang('Game.resultLanguage'), 'attr') ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="redirect_to" value="<?= esc('/' . uri_string(), 'attr') ?>">
          <?php foreach ($locales as $code): ?>
            <?php $active = $code === $locale; ?>
            <button type="submit" name="locale" value="<?= esc($code, 'attr') ?>" lang="<?= esc($code, 'attr') ?>"
                    class="hud-sheet-row hud-lang-row<?= $active ? ' is-active' : '' ?>" aria-pressed="<?= $active ? 'true' : 'false' ?>">
              <?php if ($flags[$code] ?? null): ?>
                <img class="hud-sheet-flag" src="<?= esc($flags[$code], 'attr') ?>" alt="" draggable="false">
              <?php else: ?>
                <span class="hud-sheet-code" aria-hidden="true"><?= esc(strtoupper($code)) ?></span>
              <?php endif ?>
              <span class="hud-sheet-label"><?= esc($langName($code)) ?></span>
              <?= icon('check', 'hud-sheet-mark') ?>
            </button>
          <?php endforeach ?>
        </form>
      </div>
    </details>

    <button type="button" class="hud-ctl" id="btn-sound" aria-pressed="true"
            aria-label="<?= esc(lang('Game.sound'), 'attr') ?>">
      <?= icon('sound', 'icon-on') ?><?= icon('sound-off', 'icon-off') ?>
    </button>

    <details class="hud-pop hud-menu" data-popover>
      <summary class="hud-ctl" aria-label="<?= esc(lang('Game.resultMenu'), 'attr') ?>" title="<?= esc(lang('Game.resultMenu'), 'attr') ?>">
        <?= icon('menu') ?>
      </summary>
      <div class="hud-sheet hud-menu-sheet" role="group" aria-labelledby="hud-menu-title">
        <div class="hud-sheet-head">
          <h2 id="hud-menu-title"><?= esc(lang('Game.resultMenu')) ?></h2>
          <button type="button" class="hud-sheet-close" data-popover-close hidden
                  aria-label="<?= esc(lang('Game.close'), 'attr') ?>"><?= icon('cross') ?></button>
        </div>
        <?php if ($inGame): ?>
          <a class="hud-sheet-row hud-leave hud-profile" href="<?= base_url('profil') ?>"
             aria-label="<?= esc(lang('Game.profile') . ': ' . $userName, 'attr') ?>">
            <?= component('components/avatar', ['participant' => $participant, 'class' => 'user-avatar']) ?>
            <span class="hud-sheet-label">
              <span class="hud-profile-name"><?= esc($userName) ?></span>
              <small class="hud-profile-code">@<?= esc($participant['username']) ?></small>
            </span>
            <?= icon('right', 'hud-sheet-go') ?>
          </a>
          <a class="hud-sheet-row hud-leave" href="<?= base_url('peta') ?>">
            <?= icon('home') ?><span class="hud-sheet-label"><?= esc(lang('Game.mapKedu')) ?></span><?= icon('right', 'hud-sheet-go') ?>
          </a>
        <?php endif ?>
        <button type="button" class="hud-sheet-row hud-fullscreen" id="btn-fullscreen" aria-pressed="false" hidden>
          <?= icon('fullscreen', 'icon-enter') ?><?= icon('fullscreen-exit', 'icon-exit') ?>
          <span class="hud-sheet-label"><?= esc(lang('Game.fullscreen')) ?></span><?= icon('right', 'hud-sheet-go') ?>
        </button>
        <?php if ($inGame): ?>
          <a class="hud-sheet-row hud-leave" href="<?= base_url('keluar') ?>">
            <?= icon('logout') ?><span class="hud-sheet-label"><?= esc(lang('Game.logout')) ?></span><?= icon('right', 'hud-sheet-go') ?>
          </a>
        <?php endif ?>
      </div>
    </details>
  </div>
</header>
