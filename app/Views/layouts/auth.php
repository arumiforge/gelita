<?php
/**
 * Layout masuk staf — satu kartu di tengah layar bergaya panel admin
 * (Dok 3 §1.6, §8.1): latar netral, tanpa gambar tema permainan.
 *
 * The card opens with the centred GELITA logo (owner, 12 Oct 2026): the large
 * logo `ui.logo-hero`, else the emblem `ui.logo` above the word GELITA, else
 * the plain "G" mark. The logo is the only game artwork on this page.
 *
 * Section `aside` (optional): a second card to the right of the login
 * card (the staff guide card, owner 16 Nov 2026); stacked below it on
 * narrow screens.
 *
 * @var CodeIgniter\View\View $this
 */
$title    = trim($this->renderSection('title'));
$aside    = trim($this->renderSection('aside'));
$logoHero = media_key_src('ui.logo-hero');
$emblem   = $logoHero === null ? media_key_src('ui.logo') : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title !== '' ? $title : lang('Admin.loginTitle')) ?> · GELITA</title>
  <link rel="icon" href="<?= esc(app_icon_src('app.favicon', 'favicon.ico'), 'attr') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
</head>
<body class="auth">
  <a class="skip-link" href="#auth-main"><?= esc(lang('Admin.skipToContent')) ?></a>

  <main class="auth-shell<?= $aside !== '' ? ' has-aside' : '' ?>" id="auth-main" tabindex="-1">
    <section class="auth-card">
      <div class="auth-brand">
        <?php if ($logoHero !== null): ?>
          <img class="auth-logo" src="<?= esc($logoHero, 'attr') ?>" alt="GELITA" width="1600" height="600">
        <?php elseif ($emblem !== null): ?>
          <img class="auth-emblem" src="<?= esc($emblem, 'attr') ?>" alt="" width="512" height="512">
          <span class="auth-wordmark">GELITA</span>
        <?php else: ?>
          <span class="sidebar-mark" aria-hidden="true">G</span>
          <span class="auth-wordmark">GELITA</span>
        <?php endif ?>
      </div>
      <?= $this->renderSection('content') ?>
    </section>
    <?php if ($aside !== ''): ?>
      <?= $aside ?>
    <?php endif ?>
  </main>
</body>
</html>
