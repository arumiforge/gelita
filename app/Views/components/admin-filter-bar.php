<?php
/**
 * Filter penelitian yang sama untuk seluruh halaman analitik.
 *
 * Form `method="get"`, sehingga filter tersimpan di URL dan dapat dibagikan
 * atau di-bookmark. admin/filters.js memuat isi halaman untuk filter baru (KPI,
 * tabel, dan chart dari /api/admin/*) di latar
 * tanpa memuat ulang halaman; tanpa JavaScript, tombol Terapkan mengirim form.
 *
 * Guru terkunci pada sekolahnya: kontrol sekolah tidak dirender untuk guru,
 * dan server tetap memaksa cakupan lewat StaffScope::scopedFilters().
 *
 * @var array<string, mixed>          $filters  filter aktif
 * @var Closure|array<string, mixed>  $filterOptions BaseAdminController::filterOptions()
 * @var list<string>|null             $only     batasi kontrol yang dirender
 * @var array<string, string>|null    $extra    field tambahan [name => value] (mis. pencarian)
 * @var bool|null                     $bare     true = hanya kontrol, tanpa <form> & tombol;
 *                                              dipakai form POST ekspor dengan nama field yang sama
 */
$options = $filterOptions instanceof Closure ? $filterOptions() : ($filterOptions ?? []);
$filters = $filters ?? [];
$show    = static fn (string $key): bool => ! isset($only) || in_array($key, $only, true);
$value   = static fn (string $key): string => (string) ($filters[$key] ?? '');
$isAdmin = session('staff_role') === 'admin';
$active  = array_diff_key($filters, ['school_id' => true]);
$classes = ['1', '2', '3', '4', '5', '6', '7', '8', '9', 'lainnya'];
$bare    = ! empty($bare);
?>
<?php if ($bare): ?>
<div class="filter-bar">
<?php else: ?>
<form method="get" class="filter-bar" aria-label="Filter data">
<?php endif ?>
  <?php if (isset($extra['q'])): ?>
    <div class="field filter-search">
      <label for="f-q">Cari</label>
      <input type="search" id="f-q" name="q" value="<?= esc($extra['q'], 'attr') ?>"
             placeholder="<?= esc($extra['q_placeholder'] ?? 'kode, nama pengguna, nama', 'attr') ?>">
    </div>
  <?php endif ?>

  <?php if ($show('study_id')): ?>
    <div class="field">
      <label for="f-study">Studi</label>
      <select id="f-study" name="study_id">
        <option value="">Semua studi</option>
        <?php foreach ($options['studies'] ?? [] as $study): ?>
          <option value="<?= esc($study['id'], 'attr') ?>" <?= $value('study_id') === (string) $study['id'] ? 'selected' : '' ?>>
            <?= esc($study['code']) ?><?= $study['status'] === 'active' ? ' (aktif)' : '' ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endif ?>

  <?php if ($show('phase_code')): ?>
    <div class="field">
      <label for="f-phase">Fase</label>
      <select id="f-phase" name="phase_code">
        <option value="">Semua fase</option>
        <?php foreach (config('Gelita')->phases as $phase): ?>
          <option value="<?= esc($phase, 'attr') ?>" <?= $value('phase_code') === $phase ? 'selected' : '' ?>><?= esc(ucfirst($phase)) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endif ?>

  <?php if ($show('level_id')): ?>
    <div class="field">
      <label for="f-level">Wilayah</label>
      <select id="f-level" name="level_id">
        <option value="">Semua wilayah</option>
        <?php foreach ($options['levels'] ?? [] as $level): ?>
          <option value="<?= esc($level->id, 'attr') ?>" <?= $value('level_id') === (string) $level->id ? 'selected' : '' ?>><?= esc($level->text('name', 'id')) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endif ?>

  <?php if ($show('school_id') && $isAdmin): ?>
    <div class="field">
      <label for="f-school">Sekolah</label>
      <select id="f-school" name="school_id">
        <option value="">Semua sekolah</option>
        <?php foreach ($options['schools'] ?? [] as $school): ?>
          <option value="<?= esc($school['id'], 'attr') ?>" <?= $value('school_id') === (string) $school['id'] ? 'selected' : '' ?>><?= esc($school['name']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php elseif ($show('school_id')): ?>
    <div class="field">
      <span class="label">Sekolah</span>
      <span class="filter-locked"><?= icon('lock') ?> <?= esc($options['own_school'] ?? 'Sekolah Anda') ?></span>
    </div>
  <?php endif ?>

  <?php if ($show('class_level')): ?>
    <div class="field">
      <label for="f-class">Kelas</label>
      <select id="f-class" name="class_level">
        <option value="">Semua kelas</option>
        <?php foreach ($classes as $class): ?>
          <option value="<?= esc($class, 'attr') ?>" <?= $value('class_level') === $class ? 'selected' : '' ?>><?= $class === 'lainnya' ? 'Lainnya' : 'Kelas ' . esc($class) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endif ?>

  <?php if ($show('province_code')): ?>
    <div class="field">
      <label for="f-province">Provinsi</label>
      <select id="f-province" name="province_code">
        <option value="">Semua provinsi</option>
        <?php foreach ($options['provinces'] ?? [] as $code => $name): ?>
          <option value="<?= esc($code, 'attr') ?>" <?= $value('province_code') === (string) $code ? 'selected' : '' ?>><?= esc($name) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endif ?>

  <?php if ($show('date_from')): ?>
    <div class="field">
      <label for="f-from">Dari tanggal</label>
      <input type="date" id="f-from" name="date_from" value="<?= esc($value('date_from'), 'attr') ?>">
    </div>
    <div class="field">
      <label for="f-to">Sampai tanggal</label>
      <input type="date" id="f-to" name="date_to" value="<?= esc($value('date_to'), 'attr') ?>">
    </div>
  <?php endif ?>

  <?php if ($show('locale')): ?>
    <div class="field">
      <label for="f-locale">Bahasa</label>
      <select id="f-locale" name="locale">
        <option value="">Semua bahasa</option>
        <?php foreach (config('Gelita')->locales as $code): ?>
          <option value="<?= esc($code, 'attr') ?>" <?= $value('locale') === $code ? 'selected' : '' ?>><?= esc(strtoupper($code)) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  <?php endif ?>

<?php if ($bare): ?>
</div>
<?php else: ?>
  <div class="filter-actions">
    <button class="btn btn-primary btn-sm" type="submit"><?= icon('search') ?> Terapkan</button>
    <?php if ($active !== [] || ! empty($extra['q'])): ?>
      <a class="btn btn-quiet btn-sm" href="<?= esc(current_url(), 'attr') ?>">Atur ulang</a>
    <?php endif ?>
  </div>
</form>
<?php endif ?>
