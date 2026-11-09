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
 * Left : the GELITA logo (`ui.logo-hero`, else `ui.logo`, else the text
 *        brand; hidden with `$hideBrand` on the welcome page, whose logo is
 *        already large) and the section `hudTitle` (e.g. Peta Kedu), else the
 *        region name (section `hudRegion` = `off` leaves the region name out:
 *        the region map shows it on its own head plate, owner 21 October 2026).
 *        Section `hudLayout` = `title-centre` drops the logo and
 *        centres the title (Peta Kedu, 20 October 2026); `logo-centre` puts
 *        the logo alone in the middle and leaves out the shard plate
 *        (Mulai, Masuk, Daftar, Gerbang, the mission brief, the region-done
 *        scene; owner, 20 and 21 October 2026); `region-centre` keeps the
 *        logo on the left and puts the region name alone in the middle, in
 *        the shard plate's place (the challenge screens; owner, 21 October
 *        2026).
 * Middle: the shard plate on in-game pages (the owner's carved frame
 *        `assets/ui/hud-shards-frame.webp`, the bar filled by the page with
 *        `hud-shards-fill.webp`; on the left with `title-centre`); a page
 *        sets section `hudShards` = `off` to leave it out (the result screen
 *        shows shards in its panel).
 * Right: section `hudBadge` first (e.g. the region dialogue's difficulty
 *        badge, partials/region-difficulty), then three round controls, each
 *        drawn as the owner's round gold button (`assets/ui/round-*.webp`,
 *        round_icon(); 21 October 2026), else a line icon on the
 *        `ui.result-hud-control` skin:
 *        - language: popover with the round flags (`round-lang-{code}`, else
 *          the `ui.lang.*` slot icons), a POST form
 *          with class `lang-switch` (game/hud.js, the challenge frame);
 *          hidden during a challenge;
 *        - sound (#btn-sound, game/hud.js): `round-sound` / `round-mute`;
 *        - menu (`round-menu`): profile, Peta Kedu, Tentang GELITA
 *          (`/tentang`), and logout; each row on its round picture (map,
 *          info, logout). Signed in without progress data (the opening
 *          story): Kembali ke awal (`/`) instead of Peta Kedu; on the start
 *          page itself (`$homePage`) neither. Before login: Tentang GELITA
 *          and Masuk staf (`/admin/login`, the old welcome-page corner link).
 *          No fullscreen row since 21 October 2026 (owner).
 *        Section `hudBack` (a URL) adds a Back button in the top-right
 *        corner, after the controls (owner, 20 October 2026; the round
 *        `round-back` button since 21 October 2026); the controls
 *        and Back then share the `.hud-end` row, so the popover sheets stay
 *        anchored to the controls. With `logo-centre` Back sits alone in the
 *        top-left corner on the owner's navy plate instead
 *        (`assets/ui/kenal-plate-navy.webp`, as Kembali on Kenali; owner,
 *        20 October 2026). Section `hudBackLabel` replaces its "Kembali"
 *        (the region-done scene: "Peta wilayah").
 *
 * Both popovers are <details data-popover>: they open without JavaScript;
 * game/hud.js adds Escape, outside click, one-open-at-a-time, the close
 * button, and focus back to the trigger.
 *
 * @var array<string, mixed>|null $participant safe shape (toSafeArray)
 * @var array<string, mixed>|null $progress
 * @var \App\Entities\Level|null   $level
 * @var bool|null                 $hideBrand
 * @var bool|null                 $homePage  the start page `/`: no Kembali ke awal row
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
$hudLayout   = trim($this->renderSection('hudLayout'));
$titleCentre = $hudLayout === 'title-centre';
// Section `hudLayout` = `logo-centre` (account pages and the gate, owner 20 October 2026): the logo
// alone in the middle, no shard plate
$logoCentre = $hudLayout === 'logo-centre';
// Section `hudLayout` = `region-centre` (challenge screens, owner 21 October 2026): logo left, the
// region name in the middle where the shard plate was, no shard plate
$regionCentre = $hudLayout === 'region-centre';
$locale   = service('request')->getLocale();
$locales  = config('Gelita')->locales;
$hudTitle = trim($this->renderSection('hudTitle'));
$region   = $hudTitle !== '' ? $hudTitle : (isset($level) && $level instanceof \App\Entities\Level ? $level->text('name', $locale) : null);
if (trim($this->renderSection('hudRegion')) === 'off') {
    $region = null;
}
$back     = trim($this->renderSection('hudBack'));
// Section `hudBackLabel`: the plate's text, escaped by the page like every section
$backText = trim($this->renderSection('hudBackLabel'));
$backText = $backText !== '' ? $backText : esc(lang('Game.back'));
// logo-centre: Back on the navy plate in the free top-left corner, not beside the controls
$backPlate = $back !== '' && $logoCentre;
$backEnd   = $back !== '' && ! $logoCentre;
$badge    = trim($this->renderSection('hudBadge'));
$shards   = $inGame && ! $logoCentre && ! $regionCentre && trim($this->renderSection('hudShards')) !== 'off';
$logo     = empty($hideBrand) && ! $titleCentre ? (media_key_src('ui.logo-hero') ?? media_key_src('ui.logo')) : null;
// The owner's round flag buttons (21 October 2026) first, else the uploaded slot icons
$roundFlag = ['id' => round_icon_src('lang-id'), 'en' => round_icon_src('lang-en')];
$flags     = [
    'id' => $roundFlag['id'] ?? media_key_src('ui.lang.id'),
    'en' => $roundFlag['en'] ?? media_key_src('ui.lang.en'),
];
$backArt  = round_icon_src('back');

$langName = static fn (string $code): string => lang_or('Game.lang_' . $code, strtoupper($code));
$userName = $signedIn ? (string) ($participant['display_name'] ?: $participant['username']) : '';
?>
<header class="hud hud-menu-style<?= $compact ? ' is-compact' : '' ?><?= $tall ? ' is-tall' : '' ?><?= $titleCentre ? ' is-title-centre' : '' ?><?= $logoCentre ? ' is-logo-centre' : '' ?><?= $regionCentre ? ' is-region-centre' : '' ?>"<?= $inGame ? ' data-in-game' : '' ?><?= skin_style_attr(['skin-hud' => 'ui.result-hud-control']) ?>>
  <div class="hud-left hud-identity">
    <?php if ($logo !== null): ?>
      <img class="hud-identity-logo" src="<?= esc($logo, 'attr') ?>" alt="<?= esc(lang('Game.appName'), 'attr') ?>" decoding="async" draggable="false">
    <?php elseif (empty($hideBrand) && ! $titleCentre): ?>
      <span class="hud-brand"><?= esc(lang('Game.appName')) ?></span>
    <?php endif ?>
    <?php if ($region !== null && ! $logoCentre && ! $regionCentre): ?>
      <span class="hud-identity-region"><?= esc($region) ?></span>
    <?php endif ?>
  </div>

  <?php if ($region !== null && $regionCentre): ?>
    <span class="hud-identity-region hud-region-centre"><?= esc($region) ?></span>
  <?php endif ?>

  <?php if ($shards): ?>
    <?php // The owner's shard plate (20 October 2026); absolute URLs, as for Kenali's plates ?>
    <div class="hud-shards" style="--shards-frame: url(&quot;<?= esc(asset_url_versioned('ui/hud-shards-frame.webp'), 'attr') ?>&quot;); --shards-fill: url(&quot;<?= esc(asset_url_versioned('ui/hud-shards-fill.webp'), 'attr') ?>&quot;)"><?= $this->include('components/lantern') ?></div>
  <?php endif ?>

  <?php if ($backPlate): ?>
    <a class="hud-back-plate" href="<?= esc($back, 'attr') ?>" style="--plate-navy: url(&quot;<?= esc(asset_url_versioned('ui/kenal-plate-navy.webp'), 'attr') ?>&quot;)"><?= icon('left') ?><span><?= $backText ?></span></a>
  <?php endif ?>

  <?php if ($backEnd): ?><div class="hud-end"><?php endif ?>
  <div class="hud-right hud-controls">
    <?= $badge ?>
    <details class="hud-pop hud-lang" data-popover>
      <summary class="hud-ctl" aria-label="<?= esc(lang('Game.language') . ': ' . $langName($locale), 'attr') ?>"
               title="<?= esc(lang('Game.language'), 'attr') ?>">
        <?php if ($roundFlag[$locale] ?? null): ?>
          <img class="hud-ctl-art" src="<?= esc($roundFlag[$locale], 'attr') ?>" alt="" draggable="false">
        <?php elseif ($flags[$locale] ?? null): ?>
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
                <img class="hud-sheet-flag<?= ($roundFlag[$code] ?? null) ? ' is-round' : '' ?>" src="<?= esc($flags[$code], 'attr') ?>" alt="" draggable="false">
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
      <?= round_icon('sound', 'hud-ctl-art icon-on', 'sound') ?><?= round_icon('mute', 'hud-ctl-art icon-off', 'sound-off') ?>
    </button>

    <details class="hud-pop hud-menu" data-popover>
      <summary class="hud-ctl" aria-label="<?= esc(lang('Game.resultMenu'), 'attr') ?>" title="<?= esc(lang('Game.resultMenu'), 'attr') ?>">
        <?= round_icon('menu', 'hud-ctl-art', 'menu') ?>
      </summary>
      <div class="hud-sheet hud-menu-sheet" role="group" aria-labelledby="hud-menu-title">
        <div class="hud-sheet-head">
          <h2 id="hud-menu-title"><?= esc(lang('Game.resultMenu')) ?></h2>
          <button type="button" class="hud-sheet-close" data-popover-close hidden
                  aria-label="<?= esc(lang('Game.close'), 'attr') ?>"><?= round_icon('close', 'hud-sheet-close-art', 'cross') ?></button>
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
              <?= round_icon('map', 'hud-sheet-art', 'home') ?><span class="hud-sheet-label"><?= esc(lang('Game.mapKedu')) ?></span><?= icon('right', 'hud-sheet-go') ?>
            </a>
          <?php elseif (empty($homePage)): ?>
            <a class="hud-sheet-row hud-leave" href="<?= base_url() ?>">
              <?= round_icon('back', 'hud-sheet-art', 'home') ?><span class="hud-sheet-label"><?= esc(lang('Game.backHome')) ?></span><?= icon('right', 'hud-sheet-go') ?>
            </a>
          <?php endif ?>
        <?php endif ?>
        <?php // Part 8 (34 §5.5, K4): the validators' preview of the test module (uji.* accounts, switch on) ?>
        <?php if ($signedIn && App\Services\InstrumentGate::previewFor((string) ($participant['username'] ?? ''))): ?>
          <a class="hud-sheet-row hud-leave hud-instrument" href="<?= base_url('tes') ?>">
            <?= round_icon('play', 'hud-sheet-art', 'play') ?><span class="hud-sheet-label"><?= esc(lang('Game.instrumentPreviewLink')) ?></span><?= icon('right', 'hud-sheet-go') ?>
          </a>
        <?php endif ?>
        <a class="hud-sheet-row hud-leave hud-about" href="<?= base_url('tentang') ?>">
          <?= round_icon('info', 'hud-sheet-art', 'info') ?><span class="hud-sheet-label"><?= esc(lang('Game.about')) ?></span><?= icon('right', 'hud-sheet-go') ?>
        </a>
        <?php if (! $signedIn): ?>
          <a class="hud-sheet-row hud-leave hud-staff" href="<?= base_url('admin/login') ?>">
            <?= round_icon('profile', 'hud-sheet-art', 'key') ?><span class="hud-sheet-label"><?= esc(lang('Game.staffLogin')) ?></span><?= icon('right', 'hud-sheet-go') ?>
          </a>
        <?php endif ?>
        <?php if ($signedIn): ?>
          <a class="hud-sheet-row hud-leave" href="<?= base_url('keluar') ?>">
            <?= round_icon('logout', 'hud-sheet-art', 'logout') ?><span class="hud-sheet-label"><?= esc(lang('Game.logout')) ?></span><?= icon('right', 'hud-sheet-go') ?>
          </a>
        <?php endif ?>
      </div>
    </details>
  </div>
  <?php if ($backEnd && $backArt !== null): ?>
    <a class="hud-ctl hud-back" href="<?= esc($back, 'attr') ?>" aria-label="<?= esc(lang('Game.back'), 'attr') ?>" title="<?= esc(lang('Game.back'), 'attr') ?>">
      <img class="hud-ctl-art" src="<?= esc($backArt, 'attr') ?>" alt="" draggable="false">
    </a>
  </div>
  <?php elseif ($backEnd): ?>
    <a class="btn btn-quiet hud-back" href="<?= esc($back, 'attr') ?>"><?= icon('left') ?> <span><?= esc(lang('Game.back')) ?></span></a>
  </div>
  <?php endif ?>
</header>
