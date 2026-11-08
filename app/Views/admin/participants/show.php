<?php
/**
 * Detail siswa — `/admin/peserta/{id}?tab=…` → ParticipantController::show
 * (Dok 3 §8.3)
 *
 * Aksi: reset sandi, unduh data siswa (XLSX/CSV-ZIP, ExportController), cetak PDF. Tab: Profil ·
 * Sesi & perangkat · Progres (12 baris) · Pengerjaan · Jawaban (baris dapat
 * dibuka untuk riwayat percobaan) · Navigasi · Bantuan · Kritik & saran.
 * Tab dirender server satu per satu (`?tab=`), tanpa JavaScript.
 *
 * Two views (27 §9): the admin sees the account card only (no research
 * tabs, no downloads); `peneliti` and `ketua` see codes and the research
 * tabs, never the name, username or school name.
 *
 * @var array<string, mixed>            $participant bentuk aman; without name for researchers
 * @var string                          $tab
 * @var array<string, string>           $tabs
 * @var bool                            $identity    the caller sees names (admin)
 * @var bool                            $research    the caller sees research data
 * @var array<string, mixed>|null       $table      tab data mentah (null = layanan belum tersedia)
 * @var array<string, list<array>>|null $tries      riwayat percobaan per "attempt_id|item_key"
 * @var bool|null                       $readableAnswers jawaban as sentences (false = stored JSON, `?raw=1`)
 * @var array<string, mixed>|null       $sessions
 * @var list<array<string, mixed>>|null $devices
 * @var array<string, mixed>|null       $progress
 * @var array<string, mixed>|null       $feedback
 */
$id       = (int) $participant['id'];
$base     = 'admin/peserta/' . $id;
$criteria = $participant['pw_first_submit_criteria'];
$region   = implode(', ', array_filter([$participant['district_name'], $participant['province_name'], $participant['country_name']]));
$title    = $identity && ! empty($participant['display_name']) ? $participant['display_name'] : $participant['participant_code'];
$school   = $identity ? ($participant['school_name'] ?? null) : ($participant['school_ref'] ?? null);

// Unduh data siswa (lembar §9.2 difilter satu siswa) dan laporan PDF: satu
// form, tujuan dipilih tombolnya. Only `research.export` (ketua), always coded.
$actions = '';

if (staff_can('research.export')) {
    $actions .= '<form method="post" action="' . base_url('admin/ekspor/pdf') . '" class="btn-row">'
        . csrf_field()
        . '<input type="hidden" name="participant_id" value="' . $id . '">'
        . '<button class="btn" type="submit" formaction="' . base_url('admin/ekspor/xlsx') . '">' . icon('download') . ' Unduh XLSX</button>'
        . '<button class="btn" type="submit" formaction="' . base_url('admin/ekspor/csv') . '">' . icon('download') . ' Unduh CSV-ZIP</button>'
        . '<button class="btn" type="submit">' . icon('text') . ' Cetak PDF</button></form>';
}

if (staff_can('participants.reset_by_id')) {
    $actions .= '<a class="btn btn-danger-soft" href="' . base_url($base . '#reset-sandi') . '">' . icon('key') . ' Atur ulang kata sandi</a>';
} elseif (staff_can('participants.reset_by_code')) {
    $actions .= '<a class="btn btn-danger-soft" href="' . base_url('admin/peserta/reset-sandi') . '">' . icon('key') . ' ' . esc(lang('Admin.menuResetByCode')) . '</a>';
}

$rawTable = static function (?array $table, string $empty, array $extra = []) {
    return $table === null
        ? '<div class="empty-state">' . icon('database') . '<p>Data mentah tidak tersedia.</p></div>'
        : component('data-table', $extra + ['table' => $table, 'id' => 'tab-data', 'emptyMessage' => $empty]);
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $title,
    'lead'    => ($identity ? 'Kode ' . $participant['participant_code'] . ' · nama pengguna ' . $participant['username'] . ' · ' : '')
        . 'terdaftar ' . fmt_date($participant['created_at'], false, 'id')
        . ' · ' . ($school ?? 'sekolah tidak tercatat'),
    'actions' => $actions,
]) ?>
<?= $this->include('partials/flash') ?>

<?php if (count($tabs) > 1): ?>
<nav aria-label="Bagian data siswa">
  <ul class="tabs">
    <?php foreach ($tabs as $key => $label): ?>
      <li><a href="<?= base_url($base . ($key === 'profil' ? '' : '?tab=' . $key)) ?>"<?= $key === $tab ? ' aria-current="page"' : '' ?>><?= esc($label) ?></a></li>
    <?php endforeach ?>
  </ul>
</nav>
<?php endif ?>

<?php if ($tab === 'profil'): ?>
  <div class="split-grid">
    <section class="panel">
      <h2 class="panel-title">Data diri</h2>
      <dl class="detail-grid">
        <div><dt>Kode siswa</dt><dd class="mono"><?= esc($participant['participant_code']) ?></dd></div>
        <?php if ($identity): ?>
          <div><dt>Nama pengguna</dt><dd class="mono"><?= esc($participant['username']) ?></dd></div>
          <div><dt>Nama</dt><dd><?= esc($participant['display_name'] ?? '—') ?></dd></div>
        <?php endif ?>
        <div><dt>Umur</dt><dd class="num"><?= esc($participant['age'] ?? '—') ?><?= $participant['age'] !== null ? ' tahun' : '' ?></dd></div>
        <div><dt>Jenis kelamin</dt><dd><?= esc(admin_label('gender', $participant['gender'] ?? null)) ?></dd></div>
        <div><dt>Kelas</dt><dd><?= esc(class_label($participant['class_level'] ?? null)) ?></dd></div>
        <div><dt><?= $identity ? 'Sekolah' : 'Kode sekolah' ?></dt><dd<?= $identity ? '' : ' class="mono"' ?>><?= esc($school ?? '—') ?></dd></div>
        <div><dt>Daerah</dt><dd><?= esc($region !== '' ? $region : '—') ?></dd></div>
      </dl>
    </section>

    <section class="panel">
      <h2 class="panel-title">Pendaftaran & akun</h2>
      <dl class="detail-grid">
        <div><dt>Terdaftar</dt><dd><?= esc(fmt_date($participant['created_at'], true, 'id')) ?></dd></div>
        <?php if ($research): ?>
          <div><dt>Setuju ikut bermain</dt><dd><?= empty($participant['assented_at']) ? 'tidak tercatat' : esc(fmt_date($participant['assented_at'], true, 'id')) ?></dd></div>
          <div><dt>Pengantar dilihat</dt><dd><?= esc(fmt_date($participant['intro_seen_at'], true, 'id')) ?></dd></div>
          <div><dt>Syarat sandi dipenuhi pada kiriman pertama</dt><dd class="num"><?= $criteria === null ? '—' : esc($criteria) . ' dari 5' ?></dd></div>
          <div><dt>Mencoba sandi terlalu lemah</dt><dd class="num"><?= esc($participant['pw_weak_submit_count']) ?> kali</dd></div>
        <?php endif ?>
        <?php if ($identity): ?>
          <div><dt>Masuk terakhir</dt><dd><?= esc(fmt_date($participant['last_login_at'], true, 'id')) ?></dd></div>
          <div><dt>Terakhir ganti sandi</dt><dd><?= esc(fmt_date($participant['password_changed_at'], false, 'id')) ?></dd></div>
          <div><dt>Harus membuat sandi baru</dt><dd><?= $participant['must_change_password'] ? 'ya' : 'tidak' ?></dd></div>
        <?php endif ?>
      </dl>
    </section>
  </div>

  <?php if (staff_can('participants.reset_by_id')): ?>
  <section class="form-section danger-zone" id="reset-sandi">
    <h2><?= icon('key') ?> Siswa lupa kata sandi?</h2>
    <p class="muted">Buat sandi sementara untuk siswa ini. Sandi hanya ditampilkan sekali, dan siswa harus membuat sandi baru saat masuk berikutnya.</p>
    <form method="post" action="<?= base_url($base . '/reset-sandi') ?>" class="form"
          data-confirm="Buat sandi sementara?" data-confirm-text="Sandi lama siswa <?= esc($participant['participant_code'], 'attr') ?> langsung tidak berlaku." data-confirm-ok="Buat sandi" data-confirm-style="danger">
      <?= csrf_field() ?>
      <div class="field">
        <label for="confirm">Ketik <code>RESET</code> (huruf besar) untuk memastikan</label>
        <input type="text" id="confirm" name="confirm" class="confirm-input" required pattern="RESET" autocomplete="off" spellcheck="false">
      </div>
      <div class="form-actions">
        <button class="btn btn-danger" type="submit"><?= icon('key') ?> Buat sandi sementara</button>
      </div>
    </form>
    <?php if (staff_can('deletion.execute')): ?>
      <p class="muted">
        Siswa atau orang tua/wali meminta datanya dihapus?
        <a href="<?= base_url('admin/tata-kelola?participant_id=' . $id) ?>#hapus">Hapus data siswa ini di Tata kelola</a>
      </p>
    <?php endif ?>
  </section>
  <?php endif ?>

<?php elseif ($tab === 'sesi'): ?>
  <?= component('data-table', [
      'table'        => $sessions,
      'id'           => 'sesi-siswa',
      'title'        => 'Sesi bermain',
      'csv'          => false,
      'paginate'     => false,
      'emptyMessage' => 'Siswa ini belum pernah bermain.',
      'render'       => [
          'session_code' => static fn (array $r): string => staff_can('research.browse')
              ? '<a href="' . base_url('admin/sesi/' . $r['id']) . '">' . esc($r['session_code']) . '</a>'
              : esc($r['session_code']),
      ],
  ]) ?>
  <?php if (staff_can('tech_logs.view')): // device details are admin data (29 §5.5) ?>
  <?= component('data-table', [
      'table' => [
          'rows' => $devices, 'total' => count($devices), 'page' => 1, 'per_page' => max(1, count($devices)), 'sort' => null,
          'columns' => array_map(static fn (array $c): array => $c + ['sortable' => false], [
              ['key' => 'created_at', 'label' => 'Waktu', 'type' => 'datetime'],
              ['key' => 'via', 'label' => 'Tercatat lewat', 'type' => 'text'],
              ['key' => 'device_type', 'label' => 'Jenis', 'type' => 'label:device'],
              ['key' => 'os_name', 'label' => 'Sistem operasi', 'type' => 'text'],
              ['key' => 'os_version', 'label' => 'Versi SO', 'type' => 'code'],
              ['key' => 'browser_name', 'label' => 'Peramban', 'type' => 'text'],
              ['key' => 'browser_version', 'label' => 'Versi peramban', 'type' => 'code'],
              ['key' => 'screen', 'label' => 'Layar (px)', 'type' => 'code'],
              ['key' => 'viewport', 'label' => 'Jendela (px)', 'type' => 'code'],
              ['key' => 'corrected', 'label' => 'Dikoreksi', 'type' => 'bool'],
          ]),
      ],
      'id'           => 'perangkat-siswa',
      'title'        => 'Perangkat',
      'csv'          => false,
      'paginate'     => false,
      'emptyMessage' => 'Belum ada catatan perangkat.',
      'render'       => [
          'screen'   => static fn (array $r): string => ($r['screen_w'] ?? null) === null ? '<span class="is-empty">—</span>' : esc($r['screen_w'] . ' × ' . $r['screen_h']),
          'viewport' => static fn (array $r): string => ($r['viewport_w'] ?? null) === null ? '<span class="is-empty">—</span>' : esc($r['viewport_w'] . ' × ' . $r['viewport_h']),
      ],
  ]) ?>
  <?php endif ?>

<?php elseif ($tab === 'progres'): ?>
  <?= component('data-table', [
      'table'    => $progress,
      'id'       => 'progres-siswa',
      'title'    => 'Progres 12 tantangan',
      'csv'      => false,
      'paginate' => false,
  ]) ?>

<?php elseif ($tab === 'jawaban'): ?>
  <?php
    $tries      = $tries ?? [];
    $tryColumns = $tries['_columns'] ?? [];
    $expand     = static function (array $row) use ($tries, $tryColumns): ?string {
        $history = $tries[($row['attempt_id'] ?? '') . '|' . ($row['item_key'] ?? '')] ?? [];

        if ($history === []) {
            return null;
        }

        return component('data-table', [
            'table'       => ['rows' => $history, 'total' => count($history), 'page' => 1, 'per_page' => count($history), 'sort' => null,
                'columns' => array_map(static fn (array $c): array => $c + ['sortable' => false], $tryColumns)],
            'title'       => 'Riwayat percobaan · ' . ($row['item_key'] ?? ''),
            'csv'         => false,
            'paginate'    => false,
            'stickyFirst' => false,
        ]);
    };
  ?>
  <?= $rawTable($table ?? null, 'Siswa ini belum menjawab soal.', [
      'expand'      => $expand,
      'expandLabel' => 'Riwayat',
      'tools'       => component('answer-view-switch', ['readable' => $readableAnswers ?? true]),
  ]) ?>

<?php elseif ($tab === 'masukan'): ?>
  <?= component('data-table', [
      'table'        => $feedback,
      'id'           => 'masukan-siswa',
      'csv'          => false,
      'paginate'     => false,
      'emptyMessage' => 'Siswa ini belum mengirim kritik & saran.',
  ]) ?>

<?php else: ?>
  <?= $rawTable($table ?? null, [
      'pengerjaan' => 'Siswa ini belum mengerjakan tantangan.',
      'navigasi'   => 'Belum ada catatan navigasi.',
      'bantuan'    => 'Siswa ini belum membuka petunjuk atau memutar suara.',
  ][$tab] ?? 'Belum ada data.') ?>
<?php endif ?>
<?= $this->endSection() ?>
