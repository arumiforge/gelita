<?php
/**
 * Voice-actor script (PDF) — App\Libraries\NarrationScriptPdf.
 *
 * One table for the whole script so it flows without empty page ends:
 * a shaded row names each scene, then one row per line with a tick box,
 * running number, file name, speaker (pose/effect as a delivery cue), and
 * the text. The header row repeats on every page. Narrow columns and a
 * condensed font keep the print short.
 *
 * @var string                     $language id | en | semua
 * @var string                     $title
 * @var list<array<string, mixed>> $groups   NarrationCatalog::groups()
 * @var int                        $lines
 * @var array<string, int>         $speakers speaker name → number of lines
 */
use App\Libraries\NarrationCatalog;

$both    = $language === 'semua';
$locales = $both ? ['id', 'en'] : [$language];
$heading = ['id' => 'Teks bahasa Indonesia', 'en' => 'Teks bahasa Inggris'];
$n       = 0;
$columns = $both ? 6 : 5;
?>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: dejavusanscondensed, sans-serif; font-size: 8.5pt; color: #101828; line-height: 1.3; }
  h1 { font-size: 12pt; margin: 0 0 1mm; }
  p { margin: 0 0 1.5mm; }
  .note { font-size: 7.5pt; color: #344054; }
  table.script { border-collapse: collapse; width: 100%; }
  table.script th, table.script td { border: 0.15mm solid #D0D5DD; padding: 0.8mm 1.2mm; text-align: left; vertical-align: top; }
  table.script th { background: #F2F4F7; font-size: 7.5pt; }
  table.script td.group { background: #EAECF0; font-weight: bold; font-size: 8pt; padding: 1mm 1.2mm; }
  table.script td.tick { width: 4mm; text-align: center; font-size: 9pt; }
  table.script td.num { width: 6mm; text-align: right; color: #475467; }
  table.script td.code { width: 24mm; font-family: dejavusansmono, monospace; font-size: 7.5pt; }
  table.script td.who { width: 23mm; font-size: 7.5pt; }
  .cue { color: #475467; font-style: italic; }
  .missing { color: #B42318; font-style: italic; }
  .pdf-header { font-size: 7pt; color: #667085; text-align: right; border-bottom: 0.15mm solid #E4E7EC; padding-bottom: 0.6mm; }
  table.pdf-footer { width: 100%; }
  table.pdf-footer td { font-size: 7pt; color: #667085; }
  table.pdf-footer td.right { text-align: right; }
</style>
</head>
<body>

<h1><?= esc($title) ?></h1>
<p class="note">
  <?= esc((string) $lines) ?> baris (<?= esc(implode(', ', array_map(static fn (string $name, int $count): string => $name . ' ' . $count, array_keys($speakers), $speakers))) ?>).
  Simpan setiap rekaman dengan nama berkas di kolom <b>Nama berkas</b>, mis. <b>intro-01.mp3</b> (MP3, M4A, OGG, atau WAV), satu berkas untuk satu baris<?= $both ? ', terpisah untuk setiap bahasa' : '' ?>.
  Pose dan efek di bawah nama tokoh adalah petunjuk nada suara. Centang kotak ☐ setelah baris direkam.
</p>

<table class="script">
  <thead>
    <tr>
      <th>✓</th>
      <th>No.</th>
      <th>Nama berkas</th>
      <th>Tokoh</th>
      <?php foreach ($locales as $locale): ?>
        <th><?= esc($heading[$locale]) ?></th>
      <?php endforeach ?>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($groups as $group): ?>
      <tr><td class="group" colspan="<?= (int) $columns ?>"><?= esc($group['label'] . ' · ' . count($group['rows']) . ' baris') ?></td></tr>
      <?php foreach ($group['rows'] as $row): ?>
        <?php
        $notes = array_filter([
            $row['pose'] !== null ? admin_label('pose', (string) $row['pose']) : null,
            $row['effect'] !== null ? admin_label('effect', (string) $row['effect']) : null,
        ]);
        ?>
        <tr>
          <td class="tick">☐</td>
          <td class="num"><?= (int) ++$n ?></td>
          <td class="code"><?= esc($row['code']) ?></td>
          <td class="who">
            <b><?= esc(NarrationCatalog::CHARACTER_LABELS[$row['character_code']] ?? (string) $row['character_code']) ?></b>
            <?php if ($notes !== []): ?><br><span class="cue"><?= esc(implode(', ', $notes)) ?></span><?php endif ?>
          </td>
          <?php foreach ($locales as $locale): ?>
            <?php
            $title = trim((string) ($row['title_' . $locale] ?? ''));
            $body  = trim((string) ($row['text_' . $locale] ?? ''));
            ?>
            <td>
              <?php if ($body === '' && $locale !== 'id'): ?>
                <span class="missing">Teks bahasa Inggris belum ada.</span>
              <?php else: ?>
                <?php if ($title !== ''): ?><b><?= esc($title) ?></b><br><?php endif ?><?= esc($body) ?>
              <?php endif ?>
            </td>
          <?php endforeach ?>
        </tr>
      <?php endforeach ?>
    <?php endforeach ?>
  </tbody>
</table>

</body>
</html>
