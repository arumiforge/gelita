<?php
/**
 * 500 bergaya GELITA (production). Tidak menampilkan pesan exception atau trace;
 * hanya request_id yang sama dengan baris di writable/logs/.
 */
$locale    = service('request') instanceof CodeIgniter\HTTP\IncomingRequest ? service('request')->getLocale() : 'id';
$requestId = (string) service('gelitaRequestId');
?>
<!DOCTYPE html>
<html lang="<?= esc($locale) ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title>500 · GELITA</title>
  <style>
    :root { color-scheme: dark; }
    * { box-sizing: border-box; }
    body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
           background: radial-gradient(circle at 50% 20%, #1B2740, #0B1320 70%);
           color: #EAF0FA; font: 1rem/1.6 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; }
    .card { max-width: 520px; text-align: center; padding: 40px 32px; border-radius: 20px;
            background: rgba(18, 28, 46, .9); border: 1px solid rgba(217, 112, 74, .45);
            box-shadow: 0 12px 32px rgba(0, 0, 0, .4); }
    .code { font: 700 3.5rem/1 'Cinzel', Georgia, serif; color: #D9704A; letter-spacing: .08em; }
    h1 { font: 700 1.6rem/1.3 'Cinzel', Georgia, serif; color: #F0DFB8; margin: 16px 0 8px; }
    p { color: rgba(234, 240, 250, .8); margin: 0 0 16px; }
    .rid { display: inline-block; margin-bottom: 24px; padding: 6px 12px; border-radius: 8px;
           background: rgba(255, 255, 255, .08); font: .95rem 'IBM Plex Mono', ui-monospace, Consolas, monospace; }
    a { display: inline-block; min-height: 44px; padding: 12px 24px; border-radius: 999px;
        background: #DFC087; color: #0B1320; font-weight: 700; text-decoration: none; }
    a:focus-visible { outline: 3px solid #F0DFB8; outline-offset: 3px; }
  </style>
</head>
<body>
  <main class="card">
    <div class="code">500</div>
    <h1><?= esc(lang('Game.serverErrorTitle')) ?></h1>
    <p><?= esc(lang('Game.serverErrorText')) ?></p>
    <div class="rid">request_id: <?= esc($requestId) ?></div><br>
    <a href="<?= esc(base_url()) ?>"><?= esc(lang('Game.backHome')) ?></a>
  </main>
</body>
</html>
