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
    'status'              => ['draft' => 'Draf', 'active' => 'Aktif', 'closed' => 'Ditutup'],
    'unlock_mode'         => ['sequential' => 'Berurutan — wilayah & tantangan terbuka satu per satu', 'free' => 'Bebas — semua terbuka (uji coba)'],
    'item_selection_mode' => ['fixed' => 'Tetap — urutan bank soal', 'random' => 'Acak — ditarik dari bank per percobaan'],
];
$phaseNames = ['umum' => 'Umum', 'pretest' => 'Pretest', 'posttest' => 'Posttest'];

/** Kolom studi, dipakai form sunting dan form studi baru. */
$fields = static function (?array $study, string $p) use ($choices, $phaseNames, $locales, $phaseCodes): string {
    $value = static fn (string $key, $default = '') => $study[$key] ?? $default;
    ob_start(); ?>
    <div class="form-grid">
      <?php if ($study === null): ?>
        <div class="field">
          <label for="<?= $p ?>-code">Kode studi <span class="req">*</span></label>
          <input type="text" id="<?= $p ?>-code" name="code" required maxlength="50" spellcheck="false" placeholder="gelita-2026">
          <p class="field-help">Tidak dapat diubah setelah dibuat.</p>
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
        <label for="<?= $p ?>-phase">Fase aktif</label>
        <select id="<?= $p ?>-phase" name="active_phase_code">
          <?php foreach ($phaseCodes as $code): ?>
            <option value="<?= esc($code, 'attr') ?>" <?= $value('active_phase_code', 'umum') === $code ? 'selected' : '' ?>><?= esc($phaseNames[$code] ?? $code) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-locale">Bahasa bawaan</label>
        <select id="<?= $p ?>-locale" name="default_locale">
          <?php foreach ($locales as $code): ?>
            <option value="<?= esc($code, 'attr') ?>" <?= $value('default_locale', 'id') === $code ? 'selected' : '' ?>><?= $code === 'id' ? 'Indonesia' : ($code === 'en' ? 'English' : esc($code)) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-unlock">Pembukaan wilayah</label>
        <select id="<?= $p ?>-unlock" name="unlock_mode">
          <?php foreach ($choices['unlock_mode'] as $option => $label): ?>
            <option value="<?= $option ?>" <?= $value('unlock_mode', 'sequential') === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-selection">Pemilihan butir</label>
        <select id="<?= $p ?>-selection" name="item_selection_mode">
          <?php foreach ($choices['item_selection_mode'] as $option => $label): ?>
            <option value="<?= $option ?>" <?= $value('item_selection_mode', 'fixed') === $option ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="field">
        <label for="<?= $p ?>-retention">Retensi data (hari)</label>
        <input type="number" id="<?= $p ?>-retention" name="retention_days" min="1" max="3650" value="<?= esc($value('retention_days', 1825), 'attr') ?>">
        <p class="field-help">Batas lama penyimpanan data siswa untuk studi ini.</p>
      </div>
      <div class="field span-all">
        <label for="<?= $p ?>-desc">Deskripsi</label>
        <textarea id="<?= $p ?>-desc" name="description" rows="2"><?= esc($value('description')) ?></textarea>
      </div>
    </div>
    <div class="check-row">
      <label class="check"><input type="checkbox" name="require_consent" value="1" <?= (int) $value('require_consent', 1) === 1 ? 'checked' : '' ?>> Wajib persetujuan orang tua/wali sebelum bermain</label>
      <label class="check"><input type="checkbox" name="allow_phase_choice" value="1" <?= (int) $value('allow_phase_choice', 0) === 1 ? 'checked' : '' ?>> Siswa boleh memilih fase sendiri</label>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Studi & fase',
    'eyebrow' => 'Pengelolaan · penelitian',
    'lead'    => 'Permainan memakai studi berstatus Aktif yang paling baru. Perubahan di sini berlaku untuk sesi berikutnya; sesi yang sedang berjalan tidak berubah.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/studi') ?>" aria-current="page"><?= icon('flask') ?> Studi & fase</a></li>
  <li><a href="<?= base_url('admin/studi/rilis') ?>"><?= icon('list') ?> Rilis konten</a></li>
  <li><a href="<?= base_url('admin/studi/skoring') ?>"><?= icon('target') ?> Profil skoring</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<?php if ($used === null): ?>
  <p class="alert alert-error" role="alert"><?= icon('warn') ?> Belum ada studi berstatus Aktif. Siswa tidak dapat memulai permainan sampai satu studi diaktifkan.</p>
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
        <span class="badge is-active"><?= icon('play') ?> dipakai permainan</span>
      <?php else: ?>
        <span class="badge is-<?= esc($study['status'], 'attr') ?>"><?= esc($choices['status'][$study['status']] ?? $study['status']) ?></span>
      <?php endif ?>
    </header>

    <?= $fields($study, 's' . $sid) ?>

    <div>
      <span class="field-help">Fase penelitian studi ini:</span>
      <ul class="chip-list">
        <?php foreach ($phases[$sid] ?? [] as $phase): ?>
          <li class="chip<?= $phase['code'] === $study['active_phase_code'] ? ' is-on' : '' ?>">
            <?= esc($phase['label_id'] ?: ($phaseNames[$phase['code']] ?? $phase['code'])) ?>
            <?php if ($phase['code'] === $study['active_phase_code']): ?><span class="visually-hidden">(aktif)</span><?= icon('check') ?><?php endif ?>
          </li>
        <?php endforeach ?>
        <?php if (($phases[$sid] ?? []) === []): ?>
          <li class="muted">Belum ada baris fase untuk studi ini.</li>
        <?php endif ?>
      </ul>
    </div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan studi</button>
    </div>
  </form>
<?php endforeach ?>

<details class="form-section" <?= $studies === [] ? 'open' : '' ?>>
  <summary class="panel-title"><?= icon('sparkle') ?> Studi baru</summary>
  <form method="post" action="<?= base_url('admin/studi') ?>" class="stack">
    <?= csrf_field() ?>
    <?= $fields(null, 'new') ?>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Buat studi</button>
    </div>
  </form>
</details>
<?= $this->endSection() ?>
