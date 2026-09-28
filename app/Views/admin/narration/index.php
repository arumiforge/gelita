<?php
/**
 * Narasi — `/admin/konten/narasi` → NarrationController::index
 *
 * Seluruh baris naskah cerita (docs/naskah-cerita.md) dengan status rekaman
 * ID dan EN: belum ada, draft (belum terdengar pemain), atau disetujui.
 * Dikelompokkan per konteks dan wilayah, urut naskah. Pemutar kecil memutar
 * rekaman apa pun statusnya, jadi admin dapat mendengar draft sebelum
 * menyetujuinya. "Sunting" membuka editor dialog pada baris itu; untuk
 * petunjuk arena `cari` (`petunjuk-{node}-NN`, teks dari bank soal) membuka
 * butirnya di halaman tantangan.
 *
 * Di layar, status `draft` disebut "menunggu persetujuan".
 *
 * @var list<array{context: string, level_code: string|null, label: string, rows: list<array<string, mixed>>}> $groups
 * @var array<string, array{total: int, approved: int, draft: int, none: int}> $progress
 * @var array<string, int> $drafts  locale → jumlah narasi draft yang ikut "Setujui semua"
 * @var list<string>       $locales
 */
use App\Libraries\NarrationCatalog;

$editUrl     = static fn (array $row): string => ($row['table'] ?? 'dialogues') === 'challenge_items'
    ? base_url('admin/konten/node/' . (int) $row['node_id']) . '#item-' . (int) $row['id']
    : base_url('admin/konten/dialog/' . ($row['level_id'] === null ? 0 : (int) $row['level_id'])) . '?konteks=' . rawurlencode((string) $row['context_code']) . '#slide-' . (int) $row['sequence'];
$statusBadge = ['none' => 'is-muted', 'draft' => 'is-draft', 'review' => 'is-warn', 'rejected' => 'is-rejected', 'inactive' => 'is-warn', 'approved' => 'is-approved'];
$audioCell   = static function (array $audio, string $code, string $locale) use ($statusBadge): string {
    $html = '<span class="badge ' . ($statusBadge[$audio['status']] ?? '') . '">' . esc(NarrationCatalog::STATUS_LABELS[$audio['status']] ?? $audio['status']) . '</span>';

    if ($audio['src'] !== null) {
        $html .= '<audio class="audio-preview audio-preview-sm" controls preload="none" src="' . esc(base_url($audio['src']), 'attr') . '" aria-label="' . esc('Dengar ' . $code . ' bahasa ' . admin_label('locale', $locale), 'attr') . '"></audio>';
    }

    return $html;
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php ob_start() ?>
<a class="btn btn-primary btn-sm" href="<?= base_url('admin/konten/narasi/unggah') ?>"><?= icon('upload') ?> Unggah rekaman</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/konten/narasi/daftar-rekaman') ?>"><?= icon('download') ?> Unduh daftar rekaman (Excel)</a>
<a class="btn btn-ghost btn-sm" href="<?= base_url('admin/media/kelengkapan') ?>"><?= icon('list') ?> Kelengkapan gambar &amp; suara</a>
<?php $actions = ob_get_clean() ?>
<?= component('partials/admin-head', [
    'title'   => 'Rekaman narasi',
    'eyebrow' => 'Konten · suara cerita',
    'lead'    => 'Setiap baris cerita dan setiap petunjuk tantangan Cari Objek punya satu rekaman untuk tiap bahasa. Nama berkas rekaman harus sama dengan kodenya (mis. intro-01.mp3, petunjuk-tmg-4-01.mp3). Rekaman yang baru diunggah menunggu persetujuan, dan baru terdengar oleh siswa setelah disetujui.',
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<section class="panel">
  <h2 class="panel-title"><?= icon('sound') ?> Kemajuan rekaman</h2>
  <div class="narration-progress">
    <?php foreach ($locales as $locale): ?>
      <?php $p = $progress[$locale]; $total = max(1, $p['total']); ?>
      <div class="narration-progress-row">
        <p class="narration-progress-text">
          <b>Bahasa <?= esc(admin_label('locale', $locale)) ?> (<?= esc(strtoupper($locale)) ?>):</b>
          <?= esc($p['approved']) ?>/<?= esc($p['total']) ?> disetujui, <?= esc($p['draft']) ?> menunggu persetujuan, <?= esc($p['none']) ?> belum ada
        </p>
        <div class="progress-stack" role="img" aria-label="<?= esc(sprintf('Bahasa %s: %d disetujui, %d menunggu persetujuan, %d belum ada dari %d baris', admin_label('locale', $locale), $p['approved'], $p['draft'], $p['none'], $p['total']), 'attr') ?>">
          <span class="progress-seg is-approved" style="width: <?= round($p['approved'] / $total * 100, 2) ?>%"></span>
          <span class="progress-seg is-draft" style="width: <?= round($p['draft'] / $total * 100, 2) ?>%"></span>
        </div>
        <?= component('admin/narration/approve-all', ['locale' => $locale, 'count' => $drafts[$locale] ?? 0, 'back' => 'narasi']) ?>
      </div>
    <?php endforeach ?>
  </div>
  <p class="field-help">“Menunggu persetujuan” menghitung semua rekaman yang sudah dipasang tetapi belum terdengar oleh siswa (belum disetujui, sedang ditinjau, ditolak, atau berkasnya dinonaktifkan).</p>
</section>

<?php if ($groups === []): ?>
  <div class="empty-state"><?= icon('message') ?><p>Naskah cerita belum dipasang. Minta petugas teknis memasangnya (perintah <code>php spark gelita:story:update</code> di server).</p></div>
<?php endif ?>

<?php foreach ($groups as $group): ?>
  <?php
  $first   = $group['rows'][0];
  $levelId = $first['level_id'] === null ? 0 : (int) $first['level_id'];
  ?>
  <section class="panel narration-group">
    <h2 class="panel-title">
      <?= icon('message') ?> <?= esc($group['label']) ?>
      <span class="muted"><?= count($group['rows']) ?> baris</span>
    </h2>
    <?= component('components/admin-table', [
        'caption'  => 'Narasi ' . $group['label'],
        'rows'     => $group['rows'],
        'rowClass' => static fn (array $row): string => $row['audio']['id']['status'] === 'approved' ? '' : 'is-warn',
        'columns'  => [
            'code' => ['label' => 'Nama berkas', 'render' => static fn (array $row): string => '<code>' . esc($row['code']) . '</code>'],
            'who'  => ['label' => 'Tokoh', 'render' => static function (array $row): string {
                $name = NarrationCatalog::CHARACTER_LABELS[$row['character_code']] ?? $row['character_code'];

                return esc($name)
                    . ($row['pose'] !== null ? '<span class="cell-sub">pose ' . esc(admin_label('pose', (string) $row['pose'])) . '</span>' : '')
                    . ($row['effect'] !== null ? '<span class="cell-sub">efek ' . esc(admin_label('effect', (string) $row['effect'])) . '</span>' : '');
            }],
            'text' => ['label' => 'Teks (Indonesia)', 'render' => static fn (array $row): string => ($row['title_id'] !== null ? '<b>' . esc($row['title_id']) . '</b><br>' : '')
                . '<span class="cell-clip" title="' . esc($row['text_id'], 'attr') . '">' . esc(mb_strimwidth((string) $row['text_id'], 0, 90, '…')) . '</span>'],
            'audio_id' => ['label' => 'Rekaman Indonesia', 'render' => static fn (array $row): string => $audioCell($row['audio']['id'], $row['code'], 'id')],
            'audio_en' => ['label' => 'Rekaman Inggris', 'render' => static fn (array $row): string => $audioCell($row['audio']['en'], $row['code'], 'en')],
            'edit' => ['label' => '', 'render' => static fn (array $row): string => '<a class="btn btn-quiet btn-sm" href="'
                . esc($editUrl($row), 'attr')
                . '">' . icon('edit') . ' Sunting</a>'],
        ],
    ]) ?>
    <?php if (($first['table'] ?? 'dialogues') === 'challenge_items'): ?>
      <p class="field-help">Teks petunjuk berasal dari bank soal (kolom pertanyaan pada soal). <a href="<?= base_url('admin/konten/node/' . (int) $first['node_id']) ?>">Buka tantangan <?= esc($first['node_ref']) ?></a></p>
    <?php else: ?>
      <p class="field-help"><a href="<?= base_url('admin/konten/dialog/' . $levelId) ?>?konteks=<?= esc($group['context'], 'url') ?>">Ubah teks <?= esc($group['label']) ?></a></p>
    <?php endif ?>
  </section>
<?php endforeach ?>
<?= $this->endSection() ?>
