<?php
/**
 * Kartu sesi lapangan — `/admin/sesi-lapangan/{id}/cetak` →
 * FieldSessionController::printCard. One A4 page, landscape: the session's
 * codes, date and hours, the token in large type, an empty seating plan for
 * L1/L2 (02 → L2; 28 §11), and the class group's participant codes with
 * their booklets. Codes only, never names (28 §6.1). A standalone page
 * without the panel layout, so nothing but the card is printed.
 *
 * @var array<string, mixed>       $fieldSession + research_code
 * @var string                     $state  pending | open | closed
 * @var string|null                $token
 * @var array<string, mixed>       $plan   FieldSessionService::planEntry()
 * @var list<array<string, mixed>> $roster SeatService::roster()
 * @var int                        $rows   rows of the seating plan
 * @var int                        $seats  seats per row
 * @var int                        $max    largest rows/seats value
 */
use App\Libraries\SeatRules;

$id       = (int) $fieldSession['id'];
$base     = 'admin/sesi-lapangan/' . $id;
$p1       = (int) $fieldSession['meeting_no'] === 1;
$meeting  = 'P' . (int) $fieldSession['meeting_no'] . ((int) $fieldSession['makeup_no'] > 0 ? ' · ' . lang('Admin.printCardMakeup', [(int) $fieldSession['makeup_no']]) : '');
// What the meeting holds (02 → Pertemuan dan lembar): registration, regions, test
$content  = implode(', ', array_filter([
    $plan['register'] ? lang('Admin.printCardRegister') : null,
    ...array_map(static fn (string $code): string => service('contentRepository')->level($code)?->text('name', 'id') ?? ucfirst($code), $plan['regions']),
    $plan['test'] !== null ? lang('Admin.printCardTest_' . $plan['test']) : null,
])) ?: '—';
$title    = lang('Admin.printCardTitle') . ' · ' . $fieldSession['code'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex">
  <title><?= esc($title) ?> · <?= esc(lang('Admin.panelTitle')) ?></title>
  <link rel="icon" href="<?= esc(app_icon_src('app.favicon', 'favicon.ico'), 'attr') ?>">
  <link rel="stylesheet" href="<?= asset_url_versioned('css/admin.css') ?>">
</head>
<body class="admin print-page">
  <a class="skip-link" href="#kartu-sesi"><?= esc(lang('Admin.skipToContent')) ?></a>

  <div class="print-toolbar">
    <a class="btn" href="<?= base_url($base) ?>"><?= icon('left') ?> <?= esc(lang('Admin.printCardBack')) ?></a>
    <form method="get" action="<?= base_url($base . '/cetak') ?>" class="form-inline">
      <div class="field">
        <label for="pc-rows"><?= esc(lang('Admin.printCardRows')) ?></label>
        <input type="number" id="pc-rows" name="baris" min="1" max="<?= $max ?>" value="<?= $rows ?>">
      </div>
      <div class="field">
        <label for="pc-seats"><?= esc(lang('Admin.printCardSeats')) ?></label>
        <input type="number" id="pc-seats" name="kursi" min="1" max="<?= $max ?>" value="<?= $seats ?>">
      </div>
      <button class="btn" type="submit"><?= esc(lang('Admin.printCardApply')) ?></button>
    </form>
    <button class="btn btn-primary" type="button" data-print hidden><?= icon('printer') ?> <?= esc(lang('Admin.printCardPrint')) ?></button>
    <p class="field-help"><?= esc(lang('Admin.printCardHelp')) ?></p>
  </div>

  <main class="print-card" id="kartu-sesi" tabindex="-1">
    <header class="print-card-head">
      <div class="print-card-ids">
        <p class="print-card-kicker"><?= esc(lang('Admin.printCardTitle')) ?></p>
        <h1 class="print-card-code mono"><?= esc((string) $fieldSession['code']) ?></h1>
        <dl class="print-card-facts">
          <div><dt><?= esc(lang('Admin.printCardSchool')) ?></dt><dd class="mono"><?= esc((string) ($fieldSession['research_code'] ?? '—')) ?></dd></div>
          <div><dt><?= esc(lang('Admin.fieldSessionRombel')) ?></dt><dd class="mono"><?= esc((string) $fieldSession['rombel']) ?></dd></div>
          <div><dt><?= esc(lang('Admin.fieldSessionMeeting')) ?></dt><dd><?= esc($meeting) ?></dd></div>
          <div><dt><?= esc(lang('Admin.printCardContent')) ?></dt><dd><?= esc($content) ?></dd></div>
          <div><dt><?= esc(lang('Admin.fieldSessionDate')) ?></dt><dd><?= esc(fmt_date((string) $fieldSession['session_date'])) ?></dd></div>
          <div><dt><?= esc(lang('Admin.fieldSessionHours')) ?></dt><dd class="mono"><?= esc(substr((string) $fieldSession['opens_at'], 11, 5)) ?>–<?= esc(substr((string) ($fieldSession['closed_at'] ?? $fieldSession['closes_at']), 11, 5)) ?></dd></div>
          <div><dt><?= esc(lang('Admin.printCardFiller')) ?></dt><dd class="print-blank"></dd></div>
          <div><dt><?= esc(lang('Admin.printCardPage')) ?></dt><dd></dd></div>
        </dl>
      </div>
      <div class="print-card-token">
        <p class="print-card-kicker"><?= esc(lang('Admin.fieldSessionToken')) ?></p>
        <p class="print-token mono<?= $token === null ? ' is-none' : '' ?>"><?= esc($token ?? lang('Admin.fieldSessionTokenNone')) ?></p>
        <p class="print-card-note"><?= esc(lang('Admin.printCardTokenNote')) ?></p>
      </div>
    </header>

    <section class="print-plan" aria-labelledby="pc-plan-title">
      <h2 id="pc-plan-title"><?= esc(lang('Admin.printCardGridTitle')) ?></h2>
      <p class="print-card-note"><?= esc(lang($p1 ? 'Admin.printCardGridHelp' : 'Admin.printCardGridHelpLater')) ?></p>
      <div class="print-board"><span><?= esc(lang('Admin.printCardBoard')) ?></span><span class="print-door"><?= esc(lang('Admin.printCardDoor')) ?></span></div>
      <div class="print-grid" style="--rows: <?= $rows ?>; --seats: <?= $seats ?>">
        <?php for ($r = 1; $r <= $rows; $r++): ?>
          <?php for ($s = 1; $s <= $seats; $s++): ?>
            <div class="print-seat">
              <span class="print-seat-pos"><?= esc(lang('Admin.printCardSeatLabel', [$r, $s])) ?></span>
              <?php if ($p1): ?><b class="print-seat-booklet"><?= esc(SeatRules::booklet($r, $s)) ?></b><?php endif ?>
              <span class="print-seat-line"><?= esc(lang('Admin.printCardCode')) ?></span>
              <?php if (! $p1): ?><span class="print-seat-line"><?= esc(lang('Admin.printCardBooklet')) ?></span><?php endif ?>
              <span class="print-seat-line"><?= esc(lang('Admin.printCardDevice')) ?></span>
            </div>
          <?php endfor ?>
        <?php endfor ?>
      </div>
    </section>

    <section class="print-roster" aria-labelledby="pc-roster-title">
      <h2 id="pc-roster-title"><?= esc(lang('Admin.printCardRosterTitle')) ?> <small><?= esc(lang('Admin.printCardRosterHelp')) ?></small></h2>
      <?php if ($roster === []): ?>
        <p class="print-card-note"><?= esc(lang('Admin.printCardRosterNone')) ?></p>
      <?php else: ?>
        <ol class="print-roster-list">
          <?php foreach ($roster as $student): ?>
            <li><span class="mono"><?= esc($student['participant_code']) ?></span> <b><?= esc((string) ($student['seat']['booklet'] ?? '—')) ?></b></li>
          <?php endforeach ?>
        </ol>
      <?php endif ?>
    </section>
  </main>

  <script type="module" src="<?= asset_url_versioned('js/admin/print-card.js') ?>"></script>
</body>
</html>
