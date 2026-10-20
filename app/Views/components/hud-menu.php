<?php
/**
 * The game HUD (result screen redesign, 18 October 2026; mockup in
 * gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung). Every
 * page uses it (owner, 18 October 2026: the same logo, language, sound, and
 * menu everywhere). Three sizes:
 * - full (120 dp): pages laid out for it set section `hudStyle` = `menu`
 *   (Peta Kedu, the result screen);
 * - compact (56 dp, `.is-compact`): every other page, laid out for the old
 *   56 dp HUD strip;
 * - tall (84 dp, `.is-compact.is-tall`, section `hudStyle` = `tall`): the
 *   region map, whose content sits lower (owner, 20 October 2026).
 *
 * Left : section `hudBack` (a URL) first puts a Back button (account screens
 *        before login); then the GELITA logo (`ui.logo-hero`, else `ui.logo`,
 *        else the text brand; hidden with `$hideBrand` on the welcome page,
 *        whose logo is already large) and the section `hudTitle` (e.g. Peta
 *        Kedu), else the region name. Section `hudLayout` = `title-centre`
 *        drops the logo and centres the title (Peta Kedu, 20 October 2026).
 * Middle: the shard plate on in-game pages (the owner's carved frame
 *        `assets/ui/hud-shards-frame.webp`, the bar filled by the page with
 *        `hud-shards-fill.webp`; on the left with `title-centre`); a page
 *        sets section `hudShards` = `off` to leave it out (the result screen
 *        shows shards in its panel).
 * Right: section `hudBadge` first (e.g. the region dialogue's difficulty
 *        badge, partials/region-difficulty), then three round controls on
 *        the `ui.result-hud-control` skin:
 *        - language: popover with the flag icons (`ui.lang.*`), a POST form
 *          with class `lang-switch` (game/hud.js, the challenge frame);
 *          hidden during a challenge;
 *        - sound (#btn-sound, game/hud.js);
 *        - menu: profile, Peta Kedu, fullscreen (#btn-fullscreen,
 *          core/stage.js; hidden when unsupported), and logout. Signed in
 *          without progress data (the opening story): Kembali ke awal (`/`)
 *          instead of Peta Kedu. Before login only fullscreen; the menu
 *          hides itself when it has no row left.
 *
 * Both popovers are <details data-popover>: they open without JavaScript;
 * game/hud.js adds Escape, outside click, one-open-at-a-time, the close
 * button, and focus back to the trigger.
 *
 * @var array<string, mixed>|null $participant safe shape (toSafeArray)
 * @var array<string, mixed>|null $progress
 * @var \App\Entities\Level|null   $level
 * @var bool|null                 $hideBrand
 */
$inGame   = isset($participant, $progress) && is_array($participant) && is_array($progress);
// Signed in without progress data (the opening story): the menu still has the account rows
$signedIn = isset($participant) && is_array($participant);
$hudStyle = trim($this->renderSection('hudStyle'));
$compact  = $hudStyle !== 'menu';
// `tall` (region map, owner 20 October 2026): the compact parts, larger, in an 84 dp band
$tall     = $hudStyle === 'tall';
// Section `hudLayout` = `title-centre` (Peta Kedu, owner 20 October 2026): no logo, the shard
// plate on the left, the title in the middle
$titleCentre = trim($this->renderSection('hudLayout')) === 'title-centre';
$locale   = service('request')->getLocale();
$locales  = config('Gelita')->locales;
$hudTitle = trim($this->renderSection('hudTitle'));
$region   = $hudTitle !== '' ? $hudTitle : (isset($level) && $level instanceof \App\Entities\Level ? $level->text('name', $locale) : null);
$back     = trim($this->renderSection('hudBack'));
$badge    = trim($this->renderSection('hudBadge'));
$shards   = $inGame && trim($this->renderSection('hudShards')) !== 'off';
$logo     = empty($hideBrand) && ! $titleCentre ? (media_key_src('ui.logo-hero') ?? media_key_src('ui.logo')) : null;
$flags    = ['id' => media_key_src('ui.lang.id'), 'en' => media_key_src('ui.lang.en')];

$langName = static fn (string $code): string => lang_or('Game.lang_' . $code, strtoupper($code));
$userName = $signedIn ? (string) ($participant['display_name'] ?: $participant['username']) : '';
?>
<header class="hud hud-menu-style<?= $compact ? ' is-compact' : '' ?><?= $tall ? ' is-tall' : '' ?><?= $titleCentre ? ' is-title-centre' : '' ?>"<?= $inGame ? ' data-in-game' : '' ?><?= skin_style_attr(['skin-hud' => 'ui.result-hud-control']) ?>>
  <div class="hud-left hud-identity">
    <?php if ($back !== ''): ?>
      <a class="btn btn-quiet hud-back" href="<?= esc($back, 'attr') ?>"><?= icon('left') ?> <span><?= esc(lang('Game.back')) ?></span></a>
    <?php endif ?>
    <?php if ($logo !== null): ?>
      <img class="hud-identity-logo" src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>" decoding="async" draggable="false">
    <?php elseif (empty($hideBrand) && ! $titleCentre): ?>
      <span class="hud-brand"><?= esc(lang('Game.appName')) ?></span>
    <?php endif ?>
    <?php if ($region !== null): ?>
      <span class="hud-identity-region"><?= esc($region) ?></span>
    <?php endif ?>
  </div>

  <?php if ($shards): ?>
    <?php // The owner's shard plate (20 October 2026); absolute URLs, as for Kenali's plates ?>
    <div class="hud-shards" style="--shards-frame: url(&quot;<?= esc(asset_url_versioned('ui/hud-shards-frame.webp'), 'attr') ?>&quot;); --shards-fill: url(&quot;<?= esc(asset_url_versioned('ui/hud-shards-fill.webp'), 'attr') ?>&quot;)"><?= $this->include('components/lantern') ?></div>
  <?php endif ?>

  <div class="hud-right hud-controls">
    <?= $badge ?>
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
        <?php if ($signedIn): ?>
          <a class="hud-sheet-row hud-leave hud-profile" href="<?= base_url('profil') ?>"
             aria-label="<?= esc(lang('Game.profile') . ': ' . $userName, 'attr') ?>">
            <?= component('components/avatar', ['participant' => $participant, 'class' => 'user-avatar']) ?>
            <span class="hud-sheet-label">
              <span class="hud-profile-name"><?= esc($userName) ?></span>
              <small class="hud-profile-code">@<?= esc($participant['username']) ?></small>
            </span>
            <?= icon('right', 'hud-sheet-go') ?>
          </a>
          <?php if ($inGame): ?>
            <a class="hud-sheet-row hud-leave" href="<?= base_url('peta') ?>">
              <?= icon('home') ?><span class="hud-sheet-label"><?= esc(lang('Game.mapKedu')) ?></span><?= icon('right', 'hud-sheet-go') ?>
            </a>
          <?php else: ?>
            <a class="hud-sheet-row hud-leave" href="<?= base_url() ?>">
              <?= icon('home') ?><span class="hud-sheet-label"><?= esc(lang('Game.backHome')) ?></span><?= icon('right', 'hud-sheet-go') ?>
            </a>
          <?php endif ?>
        <?php endif ?>
        <button type="button" class="hud-sheet-row hud-fullscreen" id="btn-fullscreen" aria-pressed="false" hidden>
          <?= icon('fullscreen', 'icon-enter') ?><?= icon('fullscreen-exit', 'icon-exit') ?>
          <span class="hud-sheet-label"><?= esc(lang('Game.fullscreen')) ?></span><?= icon('right', 'hud-sheet-go') ?>
        </button>
        <?php if ($signedIn): ?>
          <a class="hud-sheet-row hud-leave" href="<?= base_url('keluar') ?>">
            <?= icon('logout') ?><span class="hud-sheet-label"><?= esc(lang('Game.logout')) ?></span><?= icon('right', 'hud-sheet-go') ?>
          </a>
        <?php endif ?>
      </div>
    </details>
  </div>
</header>
