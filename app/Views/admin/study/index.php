<?php
/**
 * Studi & fase — `/admin/studi` → StudyController::index
 *
 * Permainan memakai studi berstatus `active` dengan id terbesar. Pengaturan
 * di sini langsung memengaruhi siswa berikutnya: fase aktif, mode buka
 * wilayah, pemilihan butir, dan kewajiban persetujuan orang tua/wali.
 * Semua kolom dikirim ulang saat menyimpan, termasuk deskripsi & tahun ajaran,
 * agar tidak ada nilai yang terhapus diam-diam.
 *
 * @var list<array<string, mixed>>               $studies
 * @var array<int, list<array<string, mixed>>>   $phases     study_id → fase
 * @var list<string>                             $locales
 * @var list<string>                             $phaseCodes
 */
$used = null;

foreach ($studies as $study) {
    if ($study['status'] === 'active' && ($used === null || (int) $study['id'] > (int) $used)) {
        $used = (int) $study['id'];
    }
}

$choices = [
    'status'              => ['draft' => 'Draf (belum dipakai)', 'active' => 'Aktif (dipakai permainan)', 'closed' => 'Ditutup (selesai)'],
    'unlock_mode'         => ['sequential' => 'Berurutan — wilayah & tantangan terbuka satu per satu', 'free' => 'Bebas — semua langsung terbuka (untuk uji coba)'],
    'item_selection_mode' => ['fixed' => 'Tetap — soal selalu sama, sesuai urutan', 'random' => 'Acak — soal diambil acak dari kumpulan soal setiap kali bermain'],
];

/** Kolom studi, dipakai form sunting dan form studi baru. */
$fields = static function (?array $study, string $p) use ($choices, $locales, $phaseCodes): string {
    $value = static fn (string $key, $default = '') => $study[$key] ?? $default;
    ob_start(); ?>
    <div class="form-grid">
      <?php if ($study === null): ?>
        <div class="field">
          <label for="<?= $p ?>-code">Kode singkat studi <span class="req">*</span></label>
          <input type="text" id="<?= $p ?>-code" name="code" required maxlength="50" spellcheck="false" placeholder="gelita-2026">
          <p class="field-help">Tanpa spasi, mis. gelita-2026. Tidak dapat diubah setelah dibuat.</p>
        </div>
      <?php else: ?>
        <input type="hidden" name="code" value="<?= esc($study['code'], 'attr') ?>">
      <?php endif ?>
      <div class="field">
        <label for="<?= $p ?>-name">Nama studi <span class="req">*</span></label>
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
        <label for="<?= $p ?>-phase">Fase yang sedang berjalan</label>
        <select id="<?= $p ?>-phase" name="active_phase_code">
          <?php foreach ($phaseCodes as $code): ?>
            <option value="<?= esc($code, 'attr') ?>" <?= $value('active_phase_code', 'umum') === $code ? 'selected' : '' ?>><?= esc(admin_label('phase', $code)) ?></option>
          <?php endforeach ?>
        </select>
        <p class="field-help">Sesi bermain baru dicatat pada fase ini. Ganti ke Posttest setelah pretest selesai.</p>
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
        <label for="<?= $p ?>-selection">Pemilihan soal</label>
        <select id="<?= $p ?>-selection" name="item_selection_mode">
          <?php foreach ($choices['item_selection_mode'] as $option => $label): ?>
            <option value="<?= $option ?>" <?= $value('item_selection_mode', 'fixed') === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-retention">Lama penyimpanan data (hari)</label>
        <input type="number" id="<?= $p ?>-retention" name="retention_days" min="1" max="3650" value="<?= esc($value('retention_days', 1825), 'attr') ?>">
        <p class="field-help">Setelah lewat, sistem mengingatkan admin untuk menghapus data siswa studi ini (tidak dihapus otomatis). 1825 hari = 5 tahun.</p>
      </div>
      <div class="field span-all">
        <label for="<?= $p ?>-desc">Keterangan</label>
        <textarea id="<?= $p ?>-desc" name="description" rows="2"><?= esc($value('description')) ?></textarea>
      </div>
    </div>
    <div class="check-row">
      <label class="check"><input type="checkbox" name="require_consent" value="1" <?= (int) $value('require_consent', 1) === 1 ? 'checked' : '' ?>> Siswa wajib mendapat persetujuan orang tua/wali sebelum bermain</label>
      <label class="check"><input type="checkbox" name="allow_phase_choice" value="1" <?= (int) $value('allow_phase_choice', 0) === 1 ? 'checked' : '' ?>> Siswa boleh memilih fase sendiri</label>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Studi & fase',
    'eyebrow' => 'Pengelolaan · pengaturan penelitian',
    'lead'    => 'Permainan memakai studi berstatus Aktif yang paling baru dibuat. Perubahan di sini berlaku untuk sesi bermain berikutnya; sesi yang sedang berjalan tidak berubah.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/studi') ?>" aria-current="page"><?= icon('flask') ?> Studi & fase</a></li>
  <li><a href="<?= base_url('admin/studi/rilis') ?>"><?= icon('list') ?> Versi permainan</a></li>
  <li><a href="<?= base_url('admin/studi/skoring') ?>"><?= icon('target') ?> Aturan penilaian</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<?php if ($used === null): ?>
  <p class="alert alert-error" role="alert"><?= icon('warn') ?> Belum ada studi berstatus Aktif. Siswa belum dapat mulai bermain sampai satu studi diaktifkan.</p>
<?php endif ?>

<?php foreach ($studies as $study): ?>
  <?php $sid = (int) $study['id']; ?>
  <form method="post" action="<?= base_url('admin/studi/' . $sid) ?>" class="form-section">
    <?= csrf_field() ?>
    <header class="level-card-head">
      <div>
        <span class="eyebrow"><code><?= esc($study['code']) ?></code><?= $study['year_label'] ? ' · ' . esc($study['year_label']) : '' ?></span>
        <h2><?= esc($study['name']) ?></h2>
      </div>
      <?php if ($sid === $used): ?>
        <span class="badge is-active"><?= icon('play') ?> sedang dipakai permainan</span>
      <?php else: ?>
        <span class="badge is-<?= esc($study['status'], 'attr') ?>"><?= esc(admin_label('studyStatus', (string) $study['status'])) ?></span>
      <?php endif ?>
    </header>

    <?= $fields($study, 's' . $sid) ?>

    <div>
      <span class="field-help">Fase penelitian studi ini:</span>
      <ul class="chip-list">
        <?php foreach ($phases[$sid] ?? [] as $phase): ?>
          <li class="chip<?= $phase['code'] === $study['active_phase_code'] ? ' is-on' : '' ?>">
            <?= esc($phase['label_id'] ?: admin_label('phase', $phase['code'])) ?>
            <?php if ($phase['code'] === $study['active_phase_code']): ?><span class="visually-hidden">(sedang berjalan)</span><?= icon('check') ?><?php endif ?>
          </li>
        <?php endforeach ?>
        <?php if (($phases[$sid] ?? []) === []): ?>
          <li class="muted">Studi ini belum punya daftar fase.</li>
        <?php endif ?>
      </ul>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan studi</button>
    </div>
  </form>
<?php endforeach ?>

<details class="form-section" <?= $studies === [] ? 'open' : '' ?>>
  <summary class="panel-title"><?= icon('sparkle') ?> Buat studi baru</summary>
  <form method="post" action="<?= base_url('admin/studi') ?>" class="stack">
    <?= csrf_field() ?>
    <?= $fields(null, 'new') ?>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Buat studi</button>
    </div>
  </form>
</details>
<?= $this->endSection() ?>
