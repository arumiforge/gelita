<?php
/**
 * Penelitian — `/admin/studi` → StudyController::index
 *
 * Permainan memakai studi berstatus `active` dengan id terbesar. Form hanya:
 * kode, nama, deskripsi, tahun ajaran, status, retensi, bahasa bawaan, dan
 * mode buka wilayah (perombakan kelas 5: satu tahap bermain, soal selalu
 * diacak per variasi, persetujuan siswa di pendaftaran). Semua kolom dikirim
 * ulang saat menyimpan, agar tidak ada nilai yang terhapus diam-diam.
 *
 * @var list<array<string, mixed>>               $studies
 * @var list<string>                             $locales
 * @var array<string, mixed>                     $readiness ResearchReadiness::check()
 * @var string|null                              $packageCode package the active release names
 *
 * Daftar siap penelitian (Dok 3 §8.3): 0 gambar contoh, narasi disetujui,
 * verifikasi siap penelitian lolos. Mengubah status ke *berjalan* saat daftar
 * belum hijau meminta centang konfirmasi (diperiksa ulang server).
 *
 * Rights (revision 10, part 1, 27 K12): `ketua` changes studies
 * (`studies.manage`); the admin sees the same page with the forms locked.
 *
 * Study package lock (docs/rencana-paket-aset.md §7.2): a running study shows
 * the asset package it was locked to when it became *berjalan*, and a warning
 * when students now get another package.
 */
$used = null;

foreach ($studies as $study) {
    if ($study['status'] === 'active' && ($used === null || (int) $study['id'] > (int) $used)) {
        $used = (int) $study['id'];
    }
}

$choices = [
    'status'              => ['draft' => 'Draf (belum dipakai)', 'active' => 'Berjalan (dipakai permainan)', 'closed' => 'Ditutup (selesai)'],
    'unlock_mode'         => ['sequential' => 'Berurutan — wilayah & tantangan terbuka satu per satu', 'free' => 'Bebas — semua langsung terbuka (untuk uji coba)'],
];

/** Kolom studi, dipakai form sunting dan form studi baru. */
$fields = static function (?array $study, string $p) use ($choices, $locales, $readiness): string {
    $value = static fn (string $key, $default = '') => $study[$key] ?? $default;
    ob_start(); ?>
    <div class="form-grid">
      <?php if ($study === null): ?>
        <div class="field">
          <label for="<?= $p ?>-code">Kode singkat penelitian <span class="req">*</span></label>
          <input type="text" id="<?= $p ?>-code" name="code" required maxlength="50" spellcheck="false" placeholder="gelita-2026">
          <p class="field-help">Tanpa spasi, mis. gelita-2026. Tidak dapat diubah setelah dibuat.</p>
        </div>
      <?php else: ?>
        <input type="hidden" name="code" value="<?= esc($study['code'], 'attr') ?>">
      <?php endif ?>
      <div class="field">
        <label for="<?= $p ?>-name">Nama penelitian <span class="req">*</span></label>
        <input type="text" id="<?= $p ?>-name" name="name" required maxlength="200" value="<?= esc($value('name'), 'attr') ?>">
      </div>
      <div class="field">
        <label for="<?= $p ?>-year">Tahun ajaran</label>
        <input type="text" id="<?= $p ?>-year" name="year_label" maxlength="20" placeholder="2026/2027" value="<?= esc($value('year_label'), 'attr') ?>">
      </div>
      <div class="field">
        <label for="<?= $p ?>-status">Status</label>
        <select id="<?= $p ?>-status" name="status">
          <?php foreach ($choices['status'] as $option => $label): ?>
            <option value="<?= $option ?>" <?= $value('status', 'draft') === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-locale">Bahasa awal permainan</label>
        <select id="<?= $p ?>-locale" name="default_locale">
          <?php foreach ($locales as $code): ?>
            <option value="<?= esc($code, 'attr') ?>" <?= $value('default_locale', 'id') === $code ? 'selected' : '' ?>><?= esc(admin_label('locale', $code)) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-unlock">Cara wilayah terbuka</label>
        <select id="<?= $p ?>-unlock" name="unlock_mode">
          <?php foreach ($choices['unlock_mode'] as $option => $label): ?>
            <option value="<?= $option ?>" <?= $value('unlock_mode', 'sequential') === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-retention">Lama penyimpanan data (hari)</label>
        <input type="number" id="<?= $p ?>-retention" name="retention_days" min="1" max="3650" value="<?= esc($value('retention_days', 1825), 'attr') ?>">
        <p class="field-help">Setelah lewat, sistem mengingatkan admin untuk menghapus data siswa penelitian ini (tidak dihapus otomatis). 1825 hari = 5 tahun.</p>
      </div>
      <div class="field span-all">
        <label for="<?= $p ?>-desc">Keterangan</label>
        <textarea id="<?= $p ?>-desc" name="description" rows="2"><?= esc($value('description')) ?></textarea>
      </div>
      <?php if (! $readiness['ready'] && $value('status') !== 'active'): ?>
        <div class="alert alert-warn span-all confirm-not-ready">
          <?= icon('warn') ?>
          <label class="check"><input type="checkbox" name="confirm_not_ready" value="1">
            <span>Daftar siap penelitian belum hijau. <b>Tetap jalankan</b> penelitian ini sekarang.</span></label>
        </div>
      <?php endif ?>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title' => 'Penelitian',
    'lead'  => 'Permainan memakai penelitian berstatus Berjalan yang paling baru dibuat. Perubahan berlaku untuk sesi bermain berikutnya; sesi yang sedang berjalan tidak berubah.',
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($used === null): ?>
  <div class="alert alert-error" role="alert"><?= icon('warn') ?><p>Belum ada penelitian berstatus Berjalan. Siswa belum dapat mulai bermain sampai satu penelitian dijalankan.</p></div>
<?php endif ?>

<section class="panel">
  <div class="panel-head">
    <h2 class="panel-title"><?= icon('clipboard') ?> Daftar siap penelitian</h2>
    <span class="badge <?= $readiness['ready'] ? 'is-ok' : 'is-warn' ?>"><?= $readiness['ready'] ? 'siap' : 'belum siap' ?></span>
  </div>
  <ul class="ready-list">
    <?php foreach ($readiness['items'] as $item): ?>
      <li class="ready-item <?= $item['ok'] ? 'is-ok' : 'is-bad' ?>">
        <span class="ready-mark"><?= icon($item['ok'] ? 'check' : 'cross') ?></span>
        <div><b><?= esc($item['label']) ?></b><p class="muted"><?= esc($item['detail']) ?></p></div>
        <a class="btn btn-sm" href="<?= base_url($item['link']) ?>">Buka</a>
      </li>
    <?php endforeach ?>
  </ul>
</section>

<?php foreach ($studies as $study): ?>
  <?php $sid = (int) $study['id']; ?>
  <form method="post" action="<?= base_url('admin/studi/' . $sid) ?>" class="form-section">
    <?= csrf_field() ?>
    <header class="level-card-head">
      <div>
        <span class="eyebrow"><?= esc($study['code']) ?><?= $study['year_label'] ? ' · ' . esc($study['year_label']) : '' ?></span>
        <h2><?= esc($study['name']) ?></h2>
      </div>
      <?php if ($sid === $used): ?>
        <span class="badge is-active"><?= icon('play') ?> sedang dipakai permainan</span>
      <?php else: ?>
        <span class="badge is-<?= esc($study['status'], 'attr') ?>"><?= esc(admin_label('studyStatus', (string) $study['status'])) ?></span>
      <?php endif ?>
    </header>

    <?php if ($study['status'] === 'active'): ?>
      <?php $locked = $study['package_code'] ?? null; ?>
      <p class="muted" data-study-package>
        <?= icon('download') ?> <?= esc(lang('Admin.studyPackage')) ?>:
        <?= $locked === null ? esc(lang('Admin.studyPackageNone')) : '<code>' . esc($locked) . '</code>' ?>
      </p>
      <?php if ($locked !== $packageCode): ?>
        <div class="alert alert-warn"><?= icon('warn') ?><p><?= esc(lang('Admin.studyPackageDiffers', [$packageCode ?? lang('Admin.releaseNoPackage')])) ?></p></div>
      <?php endif ?>
    <?php endif ?>

    <?php if (staff_can('studies.manage')): ?>
      <?= $fields($study, 's' . $sid) ?>

      <div class="form-actions">
        <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan penelitian</button>
      </div>
    <?php else: ?>
      <fieldset class="form-readonly" disabled><?= $fields($study, 's' . $sid) ?></fieldset>
      <p class="filter-locked"><?= icon('lock') ?> Hanya ketua peneliti yang dapat mengubah penelitian.</p>
    <?php endif ?>
  </form>
<?php endforeach ?>

<?php if (staff_can('studies.manage')): ?>
<details class="form-section" <?= $studies === [] ? 'open' : '' ?>>
  <summary class="panel-title"><?= icon('sparkle') ?> Buat penelitian baru</summary>
  <form method="post" action="<?= base_url('admin/studi') ?>" class="stack">
    <?= csrf_field() ?>
    <?= $fields(null, 'new') ?>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Buat penelitian</button>
    </div>
  </form>
</details>
<?php endif ?>
<?= $this->endSection() ?>
