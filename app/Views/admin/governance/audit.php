<?php
/**
 * Audit log — `/admin/tata-kelola/audit` → GovernanceController::audit
 *
 * Catatan tindakan staf, 50 baris per halaman, terbaru di atas. Filter lewat
 * query string (GET) sehingga tautannya dapat dibagikan. Metadata JSON
 * dibuka per baris dengan <details>. Kata sandi mentah tidak pernah tercatat.
 * Di layar disebut "Riwayat aktivitas"; kode aksi tampil dengan nama
 * Indonesia (admin_label('audit')).
 *
 * @var list<array<string, mixed>>   $rows
 * @var CodeIgniter\Pager\Pager|null $pager
 * @var string                       $action
 * @var string                       $from
 * @var string                       $to
 * @var list<string>                 $actions
 * @var array<int|string, string>    $staff  id → username
 */
$filtered = $action !== '' || $from !== '' || $to !== '';
$targets  = [
    'staff_user' => 'akun', 'workbook' => 'berkas Excel', 'media_asset' => 'gambar/berkas', 'audio_asset' => 'rekaman',
    'data_export' => 'unduhan', 'data_deletion_request' => 'permintaan penghapusan', 'research_study' => 'studi',
    'game_release' => 'versi permainan', 'scoring_profile' => 'aturan penilaian', 'participant' => 'peserta',
    'level' => 'wilayah', 'node' => 'tantangan', 'item' => 'soal', 'options' => 'pilihan jawaban soal', 'passages' => 'bacaan wilayah',
    'library' => 'Pustaka wilayah', 'dialogues' => 'dialog', 'school' => 'sekolah',
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Riwayat aktivitas',
    'eyebrow' => 'Pengelolaan · data penelitian',
    'lead'    => 'Catatan siapa melakukan apa di panel: masuk, mengubah konten, mengunduh data, membuat sandi sementara, dan menghapus data. Alamat internet pengguna disimpan dalam bentuk tersamar.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/tata-kelola') ?>"><?= icon('shield') ?> Hapus data</a></li>
  <li><a href="<?= base_url('admin/tata-kelola/audit') ?>" aria-current="page"><?= icon('list') ?> Riwayat aktivitas</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<form method="get" class="filter-bar" aria-label="Saring riwayat aktivitas">
  <div class="field">
    <label for="f-action">Jenis aktivitas</label>
    <select id="f-action" name="action">
      <option value="">Semua aktivitas</option>
      <?php foreach ($actions as $option): ?>
        <option value="<?= esc($option, 'attr') ?>" <?= $action === $option ? 'selected' : '' ?>><?= esc(admin_label('audit', $option)) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="field">
    <label for="f-from">Dari tanggal</label>
    <input type="date" id="f-from" name="date_from" value="<?= esc($from, 'attr') ?>">
  </div>
  <div class="field">
    <label for="f-to">Sampai tanggal</label>
    <input type="date" id="f-to" name="date_to" value="<?= esc($to, 'attr') ?>">
  </div>
  <div class="filter-actions">
    <button class="btn btn-primary btn-sm" type="submit"><?= icon('search') ?> Terapkan</button>
    <?php if ($filtered): ?>
      <a class="btn btn-quiet btn-sm" href="<?= base_url('admin/tata-kelola/audit') ?>">Atur ulang</a>
    <?php endif ?>
  </div>
</form>

<section class="panel">
  <?= component('components/admin-table', [
      'caption'      => 'Riwayat aktivitas',
      'emptyMessage' => $filtered ? 'Tidak ada aktivitas yang cocok dengan pilihan ini.' : 'Belum ada aktivitas yang tercatat.',
      'rows'         => $rows,
      'rowClass'     => static fn (array $row): string => str_starts_with((string) $row['action'], 'delete_') ? 'is-warn' : '',
      'columns'      => [
          'occurred_at'   => ['label' => 'Waktu', 'render' => static fn (array $row): string => '<span class="timeline-time">' . esc(fmt_date($row['occurred_at'], true, 'id')) . '</span>'],
          'action'        => ['label' => 'Aktivitas', 'render' => static fn (array $row): string => esc(admin_label('audit', (string) $row['action']))],
          'staff_user_id' => ['label' => 'Oleh', 'render' => static fn (array $row): string => $row['staff_user_id'] === null
              ? '<span class="muted">sistem (otomatis)</span>'
              : esc($staff[$row['staff_user_id']] ?? 'akun nomor ' . $row['staff_user_id'])],
          'target'        => ['label' => 'Pada', 'render' => static fn (array $row): string => $row['target_type'] === null
              ? '—'
              : esc($targets[$row['target_type']] ?? $row['target_type']) . ($row['target_id'] !== null ? ' <code>' . esc($row['target_id']) . '</code>' : '')],
          'metadata_json' => ['label' => 'Rincian', 'render' => static function (array $row): string {
              $raw = (string) ($row['metadata_json'] ?? '');

              if ($raw === '' || $raw === '[]' || $raw === '{}') {
                  return '—';
              }

              $decoded = json_decode($raw, true);
              $pretty  = is_array($decoded) ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $raw;

              return '<details class="row-details"><summary>Lihat rincian</summary><pre class="code-block">' . esc((string) $pretty) . '</pre></details>';
          }],
      ],
  ]) ?>
  <?= component('components/admin-pagination', ['pager' => $pager]) ?>
</section>
<?= $this->endSection() ?>
