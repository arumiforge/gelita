<?php
/**
 * Engine `rumpang` — isi bagian rumpang.
 *
 * Tiap soal memuat penanda `___` yang diganti kotak rumpang. Kotak berupa
 * tombol, diisi dengan mengetuk kata dari bank (payload.word_bank — jawaban +
 * pengecoh yang sudah diacak server; client tidak tahu kata mana milik
 * rumpang mana). Tidak ada lagi isian ketik sendiri (perombakan kelas 5).
 * engines/rumpang.js memasang perilakunya.
 *
 * Tantangan bacaan (config as_reading, kelas 5): semua soal adalah
 * potongan satu bacaan, disambung menjadi paragraf utuh dengan rumpang
 * bernomor di dalam kalimat (App\Libraries\ClozeReading). Selain itu,
 * soal tampil sebagai daftar kalimat lepas. Judul variasi (`variant.title`)
 * tampil di atas bacaan.
 *
 * Muat ulang di tengah tantangan: rumpang yang sudah benar
 * (`items[].state.locked`) dipulihkan engines/rumpang.js dengan jawaban
 * siswa sendiri dan terkunci.
 *
 * @var App\Entities\ChallengeNode $node
 * @var App\Entities\Level         $level
 * @var array<string, mixed>       $payload
 * @var string                     $locale
 */
$items       = $payload['items'] ?? [];
$payload['word_bank'] ??= [];
$media       = $payload['node']['scene'] ?? ($items[0]['media'] ?? null);
$reading     = $node->isReading();
// Judul variasi di atas bacaan (Dok 2 §6.2); judul node sudah tampil di kepala tantangan
$variantTitle = trim((string) ($payload['variant']['title'] ?? ''));
if ($variantTitle === $node->text('title', $locale)) {
    $variantTitle = ''; // variasi tanpa judul sendiri: judul node sudah tampil di kepala
}

// Satu rumpang (tombol bank kata). Dirakit sebagai string agar tidak ada
// spasi templat di antara rumpang dan tanda baca sesudahnya.
$blankHtml = static function (int|string $itemId, int $number): string {
    $label = lang('Game.blank', [$number]);
    $field = '<button type="button" class="blank" data-item="' . esc((string) $itemId, 'attr') . '" aria-label="' . esc($label, 'attr') . '">?</button>';

    return '<span class="blank-wrap"><sup class="blank-no num" aria-hidden="true">' . $number . '</sup>' . $field . '</span>';
};
?>
<?= $this->extend('layouts/game') ?>

<?= $this->section('title') ?><?= esc($node->text('title', $locale)) ?><?= $this->endSection() ?>
<?= $this->section('background') ?><?= media_first($node->background_media_id, $level->background_media_id) ?><?= $this->endSection() ?>
<?= $this->section('bodyClass') ?>is-challenge<?= $this->endSection() ?>

<?= $this->section('arena') ?>
<div class="rumpang<?= $media ? ' has-media' : '' ?>" data-word-bank="1">
  <?php if ($media): ?>
    <figure class="rumpang-media">
      <img src="<?= esc($media, 'attr') ?>" alt="" loading="lazy">
    </figure>
  <?php endif ?>

  <div class="rumpang-main">
    <?php if ($reading): ?>
    <div class="rumpang-reading stage-scroll" id="reading-panel">
      <?php if ($variantTitle !== ''): ?>
        <h2 class="rumpang-title"><?= esc($variantTitle) ?></h2>
      <?php endif ?>
      <?php foreach (App\Libraries\ClozeReading::paragraphs(array_map(static fn (array $item): array => ['id' => $item['id'], 'prompt' => (string) ($item['prompt'] ?? '')], $items)) as $paragraph): ?>
        <p><?php foreach ($paragraph as $token): ?><?= $token['type'] === 'text' ? esc($token['text']) : $blankHtml($token['id'], (int) $token['index']) ?><?php endforeach ?></p>
      <?php endforeach ?>
    </div>
    <?php else: ?>
    <ol class="sentences stage-scroll" id="reading-panel">
      <?php foreach ($items as $index => $item): ?>
        <?php
        $prompt = (string) ($item['prompt'] ?? '');
        $parts  = explode('___', $prompt, 2);
        $label  = lang('Game.blank', [$index + 1]);
        ?>
        <li class="sentence" data-item="<?= esc($item['id'], 'attr') ?>">
          <?php if (($item['source_text'] ?? '') !== ''): ?>
            <span class="sentence-source"><?= esc($item['source_text']) ?></span>
          <?php endif ?>
          <span class="sentence-text">
            <?= esc($parts[0]) ?>
            <button type="button" class="blank" data-item="<?= esc($item['id'], 'attr') ?>" aria-label="<?= esc($label, 'attr') ?>">?</button>
            <?= esc($parts[1] ?? '') ?>
          </span>
        </li>
      <?php endforeach ?>
    </ol>
    <?php endif ?>

    <div class="word-bank" id="word-bank" role="group" aria-label="<?= esc(lang('Game.wordBank'), 'attr') ?>">
      <span class="word-bank-title"><?= esc(lang('Game.wordBank')) ?></span>
      <?php foreach ($payload['word_bank'] as $index => $word): ?>
        <button type="button" class="word" data-word-id="k<?= $index ?>"><?= esc($word) ?></button>
      <?php endforeach ?>
    </div>

    <div class="arena-actions">
      <button class="btn btn-primary btn-lg" id="btn-check" type="button"><?= icon('check') ?> <?= esc(lang('Game.check')) ?></button>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?= $this->include('game/challenge/_frame') ?>
<?= $this->endSection() ?>
