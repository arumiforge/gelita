<?php
/**
 * The one GELITA error card (owner, 19 November 2026), shared by error_404, error_500,
 * error_400 and production.php (every other client error). Self-contained: inline CSS and
 * no layout, so it still shows when the assets or the database fail. The picture
 * (`public/assets/ui/error-{kind}.webp`, document 40 `PL-10` to `PL-12`) shows once
 * installed; without it the card has the code in large gold digits, as before.
 *
 * Never shows the exception, its message or a trace (ui-copy.md); the 500 card shows the
 * request_id, the same as the log line, for the teacher to pass on.
 *
 * @var string      $kind      404 | 500 | 400 (picture and accent)
 * @var string      $code      the status code shown
 * @var string      $title
 * @var string      $text
 * @var string|null $requestId 500 only
 */
$locale  = service('request') instanceof CodeIgniter\HTTP\IncomingRequest ? service('request')->getLocale() : 'id';
$file    = 'ui/error-' . $kind . '.webp';
$picture = is_file(FCPATH . 'assets/' . $file) && function_exists('asset_url_versioned') ? asset_url_versioned($file) : null;
$accent  = $kind === '500' ? '#E08A64' : '#DFC087';
?>
<!DOCTYPE html>
<html lang="<?= esc($locale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($code) ?> · GELITA</title>
  <style>
    :root { color-scheme: dark; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
           background: radial-gradient(circle at 50% 20%, #1B2740, #0B1320 70%);
           color: #EAF0FA; font: 1rem/1.6 'Marcellus', Georgia, serif; }
    .card { width: min(560px, 100%); overflow: hidden; text-align: center; border-radius: 20px;
            background: rgba(14, 24, 40, .94); box-shadow: 0 0 0 1px rgba(223, 192, 135, .35), 0 16px 40px rgba(0, 0, 0, .45); }
    .art { display: block; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; }
    .body { padding: 28px 32px 32px; }
    .code { font: 700 3.5rem/1 'Marcellus', Georgia, serif; color: <?= $accent ?>; letter-spacing: .08em; margin-top: 12px; }
    .has-art .code { font-size: 1rem; letter-spacing: .2em; margin: 0; opacity: .85; }
    h1 { font: 700 1.6rem/1.3 'Marcellus', Georgia, serif; color: #F0DFB8; margin: 8px 0; }
    p { color: rgba(234, 240, 250, .82); margin: 0 0 20px; }
    .rid { display: inline-block; margin-bottom: 20px; padding: 6px 12px; border-radius: 8px;
           background: rgba(255, 255, 255, .08); font: .95rem 'IBM Plex Mono', ui-monospace, Consolas, monospace; }
    a { display: inline-flex; align-items: center; justify-content: center; gap: 10px; min-height: 48px; padding: 10px 22px;
        border-radius: 14px; color: #24170A; font-weight: 700; text-decoration: none;
        background: linear-gradient(180deg, #FFF4D2 0%, #F4D891 34%, #E2BA68 68%, #CB9C48 100%);
        box-shadow: 0 0 0 1px #7E5A1E, inset 0 0 0 1px rgba(255, 249, 228, .9), 0 5px 14px rgba(2, 8, 20, .45); }
    a:focus-visible { outline: 3px solid #F0DFB8; outline-offset: 3px; }
  </style>
</head>
<body>
  <main class="card<?= $picture !== null ? ' has-art' : '' ?>">
    <?php if ($picture !== null): ?>
      <img class="art" src="<?= esc($picture, 'attr') ?>" alt="" width="960" height="720">
    <?php endif ?>
    <div class="body">
      <div class="code"><?= esc($code) ?></div>
      <h1><?= esc($title) ?></h1>
      <p><?= esc($text) ?></p>
      <?php if (! empty($requestId)): ?>
        <div class="rid">request_id: <?= esc($requestId) ?></div><br>
      <?php endif ?>
      <a href="<?= esc(base_url()) ?>"><?= esc(lang('Game.backHome')) ?></a>
    </div>
  </main>
</body>
</html>
