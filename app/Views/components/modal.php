<?php
/**
 * Kerangka dialog tengah layar — satu markup untuk semua keperluan: benar,
 * salah, konfirmasi keluar, petunjuk, sesi berakhir.
 *
 * Layout menaruh markup ini di dalam <template id="tpl-modal">; core/modal.js
 * (tahap 6) menyalinnya ke #modal-layer, mengganti kelas `modal-{type}`, dan
 * mengisi judul/teks/tombol dengan textContent. Ikon tersedia sebagai
 * <template> per jenis agar JavaScript tidak merakit SVG dari string.
 *
 * The round icon sits before the card, not inside it: the card scrolls when
 * its text is long, and a scroll box clips anything that sticks out of it.
 * The game stage shows the icon half above the frame; the panel places it in
 * the card's top-left corner (admin.css). `data-type` on the dialog carries
 * the kind for the icon colour.
 *
 * Game pages pass `$roundArt` (owner, 22 October 2026): the icons are the
 * owner's round gold buttons (round_icon(), sheets 1 and 2) instead of line
 * icons; a kind with no round picture of its own (install, key) puts its line
 * icon on the plain dark face. The panel keeps the line icons.
 *
 * @var bool|null $roundArt
 */
$roundArt = ! empty($roundArt);

$art = static function (string $kind) use ($roundArt): string {
    // kind => [round picture or null, line icon]
    $map = [
        'correct' => ['right', 'check'],
        'wrong'   => ['wrong', 'cross'],
        'hint'    => ['help', 'hint'],
        'confirm' => ['help', 'question'],
        'info'    => ['info', 'info'],
        'lock'    => ['lock', 'lock'],
        'install' => [null, 'download'],
        'key'     => [null, 'key'],
    ];
    [$round, $line] = $map[$kind];

    if (! $roundArt) {
        return icon($line);
    }
    if ($round !== null && round_icon_src($round) !== null) {
        return round_icon($round, 'modal-icon-art');
    }
    if (round_icon_src('neutral') !== null) {
        return '<span class="modal-icon-stack">' . round_icon('neutral', 'modal-icon-art') . icon($line) . '</span>';
    }

    return icon($line);
};
?>
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-text" data-type="info">
  <?php // Outside the card: the card scrolls (overflow) and would clip the icon that rises above it ?>
  <div class="modal-icon<?= $roundArt ? ' is-round' : '' ?>" aria-hidden="true"><?= $art('info') ?></div>
  <div class="modal-card modal-info">
    <img class="modal-character" alt="" hidden>
    <h3 class="modal-title" id="modal-title"></h3>
    <p class="modal-text" id="modal-text"></p>
    <div class="modal-actions"></div>
  </div>
  <?php foreach (['correct', 'wrong', 'hint', 'confirm', 'info', 'lock', 'install', 'key'] as $kind): ?>
  <template data-modal-icon="<?= $kind ?>"><?= $art($kind) ?></template>
  <?php endforeach ?>
</div>
