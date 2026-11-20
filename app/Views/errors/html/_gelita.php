<?php
/**
 * The one GELITA error page (owner, 19 November 2026), shared by error_404, error_500,
 * error_400 and production.php (every other client error). Self-contained: inline CSS and
 * no layout, so it still shows when the assets or the database fail.
 *
 * Owner, same day: the error picture is the page's own background
 * (`public/assets/ui/error-{kind}.webp`, 1920 × 1080, document 40 `PL-10` to `PL-12`), and
 * the text stands straight on it in the calm left part, with no box or border; a soft dark
 * wash from the left keeps it readable. Without the picture the navy gradient shows.
 *
 * Kembali ke awal wears the light carved gold body of the game's text buttons (document 40,
 * `PL-13`, 21 November 2026; `public/assets/ui/ui-button-carved-gold.webp`, drawn as a border
 * image like cinematic.css → *QUIET BUTTONS*); without it the CSS gold face shows.
 *
 * Never shows the exception, its message or a trace (ui-copy.md); the 500 page shows the
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
$body    = is_file(FCPATH . 'assets/ui/ui-button-carved-gold.webp') && function_exists('asset_url_versioned') ? asset_url_versioned('ui/ui-button-carved-gold.webp') : null;
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
    html, body { height: 100%; }
    body { margin: 0; min-height: 100vh; display: flex; align-items: center; padding: 6vh 7vw;
           background: radial-gradient(circle at 70% 30%, #1B2740, #0B1320 70%) #0B1320;
           color: #EAF0FA; font: 1rem/1.6 'Marcellus', Georgia, serif; }
    body.has-art { background: #0B1320 var(--art) center / cover no-repeat; }
    /* A soft wash from the left, so the text reads on the picture without a box */
    body::before { content: ""; position: fixed; inset: 0; pointer-events: none;
                   background: linear-gradient(90deg, rgba(8, 14, 26, .82) 0%, rgba(8, 14, 26, .55) 40%, rgba(8, 14, 26, 0) 70%); }
    main { position: relative; max-width: 520px; text-shadow: 0 2px 10px rgba(0, 0, 0, .8); }
    .code { font: 700 4.5rem/1 'Marcellus', Georgia, serif; color: <?= $accent ?>; letter-spacing: .08em; }
    .has-art .code { font-size: 1.125rem; letter-spacing: .25em; opacity: .9; }
    h1 { font: 700 2.25rem/1.2 'Marcellus', Georgia, serif; color: #F7E7BE; margin: 10px 0 12px; }
    p { font-size: 1.125rem; color: rgba(240, 244, 250, .9); margin: 0 0 24px; }
    .rid { display: inline-block; margin-bottom: 24px; color: rgba(240, 244, 250, .75); font: .95rem 'IBM Plex Mono', ui-monospace, Consolas, monospace; }
    a { display: inline-flex; align-items: center; justify-content: center; min-height: 52px; padding: 10px 26px;
        border-radius: 14px; color: #24170A; font-weight: 700; text-decoration: none; text-shadow: none;
        background: linear-gradient(180deg, #FFF4D2 0%, #F4D891 34%, #E2BA68 68%, #CB9C48 100%);
        box-shadow: 0 0 0 1px #7E5A1E, inset 0 0 0 1px rgba(255, 249, 228, .9), 0 5px 14px rgba(2, 8, 20, .45); }
    /* The carved body (1200 × 200): the button from y 12 to 187, ends 60 px = 0.341 × height.
       The gold gradient stays under it, clipped inside the carved rim (transparent border,
       padding-box), so the link still reads when the picture cannot be loaded. */
    a.has-body { height: 56px; border: solid transparent; border-width: 3px 3px 4px; border-radius: 28px;
                 background-clip: padding-box; box-shadow: none;
                 border-image: var(--body) 12 60 12 fill / 0 19px stretch; filter: drop-shadow(0 4px 10px rgba(2, 8, 20, .4)); }
    a:focus-visible { outline: 3px solid #F0DFB8; outline-offset: 3px; }
    @media (max-width: 640px) { body { align-items: flex-end; } body::before { background: linear-gradient(0deg, rgba(8, 14, 26, .9), rgba(8, 14, 26, .2) 70%); } }
  </style>
</head>
<body<?= $picture !== null ? ' class="has-art" style="--art: url(&quot;' . esc($picture, 'attr') . '&quot;)"' : '' ?>>
  <main>
    <div class="code"><?= esc($code) ?></div>
    <h1><?= esc($title) ?></h1>
    <p><?= esc($text) ?></p>
    <?php if (! empty($requestId)): ?>
      <div class="rid">request_id: <?= esc($requestId) ?></div><br>
    <?php endif ?>
    <a href="<?= esc(base_url()) ?>"<?= $body !== null ? ' class="has-body" style="--body: url(&quot;' . esc($body, 'attr') . '&quot;)"' : '' ?>><?= esc(lang('Game.backHome')) ?></a>
  </main>
</body>
</html>
