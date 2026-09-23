<?php
/**
 * Akun staf — `/admin/staf` → StaffController::index (juga respons reset sandi)
 *
 * Satu kartu per akun: ringkasan di <summary>, form sunting, reset sandi
 * (ketik RESET), dan nonaktifkan (konfirmasi). Akun sendiri tidak dapat
 * diturunkan role-nya atau dinonaktifkan. Kolom sekolah disembunyikan CSS
 * :has() saat role Admin dipilih — admin tidak terikat sekolah.
 *
 * Sandi sementara hasil reset hanya ada di respons ini (tidak di flash, log,
 * atau audit) dan halaman dikirim dengan Cache-Control: no-store.
 *
 * @var list<array<string, mixed>>                         $rows      toSafeArray() + locked, last_login_at
 * @var list<array<string, mixed>>                         $schools
 * @var array{staff: array<string, mixed>, password: string}|null $temporary
 */
$me          = (int) session('staff_id');
$schoolNames = array_column($schools, 'name', 'id');
$roleNames   = ['admin' => 'Admin', 'guru' => 'Guru'];

/** Kolom akun, dipakai form sunting dan form akun baru. */
$fields = static function (?array $staff, string $p) use ($schools, $roleNames, $me): string {
    $isSelf = $staff !== null && (int) $staff['id'] === $me;
    ob_start(); ?>
    <div class="form-grid">
      <?php if ($staff === null): ?>
        <div class="field">
          <label for="<?= $p ?>-username">Nama pengguna <span class="req">*</span></label>
          <input type="text" id="<?= $p ?>-username" name="username" required minlength="3" maxlength="100" pattern="[A-Za-z0-9_\-]+" autocomplete="off" spellcheck="false">
          <p class="field-help">Huruf, angka, garis bawah, atau tanda hubung. Tidak dapat diubah.</p>
        </div>
      <?php endif ?>
      <div class="field">
        <label for="<?= $p ?>-name">Nama tampilan <span class="req">*</span></label>
        <input type="text" id="<?= $p ?>-name" name="display_name" required maxlength="150" value="<?= esc($staff['display_name'] ?? '', 'attr') ?>">
      </div>
      <div class="field">
        <label for="<?= $p ?>-email">Email</label>
        <input type="email" id="<?= $p ?>-email" name="email" maxlength="150" value="<?= esc($staff['email'] ?? '', 'attr') ?>">
      </div>
      <div class="field">
        <label for="<?= $p ?>-role">Peran <span class="req">*</span></label>
        <?php if ($isSelf): ?>
          <input type="hidden" name="role" value="admin">
          <span class="filter-locked"><?= icon('lock') ?> Admin (akun Anda sendiri)</span>
        <?php else: ?>
          <select id="<?= $p ?>-role" name="role" required>
            <?php foreach ($roleNames as $value => $label): ?>
              <option value="<?= $value ?>" <?= ($staff['role'] ?? 'guru') === $value ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach ?>
          </select>
        <?php endif ?>
      </div>
      <div class="field field-school">
        <label for="<?= $p ?>-school">Sekolah <span class="req">*</span></label>
        <select id="<?= $p ?>-school" name="school_id">
          <option value="">— pilih sekolah —</option>
          <?php foreach ($schools as $school): ?>
            <option value="<?= esc($school['id'], 'attr') ?>" <?= (int) ($staff['school_id'] ?? 0) === (int) $school['id'] ? 'selected' : '' ?>><?= esc($school['name']) ?></option>
          <?php endforeach ?>
        </select>
        <p class="field-help">Guru hanya melihat data siswa dari sekolah ini.</p>
      </div>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Akun staf',
    'eyebrow' => 'Pengelolaan · akses panel',
    'lead'    => 'Admin mengelola seluruh panel. Guru hanya melihat data siswa sekolahnya sendiri dan selalu mengekspor dalam mode anonim.',
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($temporary !== null): ?>
  <section class="panel temp-password-card" aria-labelledby="temp-title">
    <h2 id="temp-title" class="panel-title"><?= icon('key') ?> Sandi sementara untuk <?= esc($temporary['staff']['display_name']) ?></h2>
    <p class="muted">Nama pengguna: <code><?= esc($temporary['staff']['username']) ?></code></p>
    <p class="temp-password" aria-label="Sandi sementara"><?= esc($temporary['password']) ?></p>
    <p><b>Sampaikan langsung kepada yang bersangkutan.</b> Sandi ini hanya ditampilkan sekali — setelah halaman ini ditutup, sandi tidak dapat dilihat lagi. Pemilik akun sebaiknya segera menggantinya lewat <b>Ubah sandi</b> di pojok kanan atas panel.</p>
    <a class="btn btn-primary" href="<?= base_url('admin/staf') ?>"><?= icon('check') ?> Sudah saya catat</a>
  </section>
<?php endif ?>

<div class="item-list">
  <?php foreach ($rows as $staff): ?>
    <?php $sid = (int) $staff['id']; $isSelf = $sid === $me; ?>
    <details class="item-card">
      <summary>
        <span class="node-row-no"><?= icon($staff['role'] === 'admin' ? 'shield' : 'user') ?></span>
        <span class="item-prompt">
          <b><?= esc($staff['display_name']) ?></b><?= $isSelf ? ' <span class="muted">(Anda)</span>' : '' ?>
          <span class="cell-sub"><code><?= esc($staff['username']) ?></code> · <?= $staff['last_login_at'] ? 'masuk terakhir ' . esc(fmt_date($staff['last_login_at'], true, 'id')) : 'belum pernah masuk' ?></span>
        </span>
        <span class="item-meta">
          <span class="badge"><?= esc($roleNames[$staff['role']] ?? $staff['role']) ?></span>
          <?php if ($staff['role'] === 'guru'): ?>
            <span class="badge is-muted"><?= esc($schoolNames[$staff['school_id']] ?? 'tanpa sekolah') ?></span>
          <?php endif ?>
          <?php if ($staff['locked']): ?>
            <span class="badge is-warn"><?= icon('lock') ?> terkunci</span>
          <?php endif ?>
          <span class="badge <?= $staff['is_active'] ? 'is-active' : 'is-inactive' ?>"><?= $staff['is_active'] ? 'aktif' : 'nonaktif' ?></span>
        </span>
      </summary>

      <div class="item-card-body">
        <form method="post" action="<?= base_url('admin/staf/' . $sid) ?>" class="stack staff-form">
          <?= csrf_field() ?>
          <?= $fields($staff, 'st' . $sid) ?>
          <div class="check-row">
            <?php if ($isSelf): ?>
              <input type="hidden" name="is_active" value="1">
            <?php else: ?>
              <label class="check"><input type="checkbox" name="is_active" value="1" <?= $staff['is_active'] ? 'checked' : '' ?>> Akun aktif</label>
            <?php endif ?>
          </div>
          <div class="form-actions">
            <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Simpan akun</button>
          </div>
        </form>

        <div class="form-actions">
          <form method="post" action="<?= base_url('admin/staf/' . $sid . '/sandi') ?>" class="inline-form">
            <?= csrf_field() ?>
            <label for="reset-<?= $sid ?>">Ketik <code>RESET</code></label>
            <input type="text" id="reset-<?= $sid ?>" name="confirm" class="confirm-input" required pattern="RESET" autocomplete="off" spellcheck="false">
            <button class="btn btn-ghost btn-sm" type="submit"><?= icon('key') ?> Buat sandi sementara</button>
          </form>

          <?php if ($staff['is_active'] && ! $isSelf): ?>
            <details class="confirm">
              <summary class="btn btn-quiet btn-sm"><?= icon('lock') ?> Nonaktifkan</summary>
              <div class="confirm-box">
                <h3>Nonaktifkan <?= esc($staff['username']) ?>?</h3>
                <p>Akun tidak dapat masuk lagi sampai diaktifkan kembali. Data dan riwayat audit tetap tersimpan.</p>
                <form method="post" action="<?= base_url('admin/staf/' . $sid . '/nonaktif') ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-danger btn-sm" type="submit">Ya, nonaktifkan</button>
                </form>
              </div>
            </details>
          <?php endif ?>
        </div>
      </div>
    </details>
  <?php endforeach ?>

  <details class="item-card" <?= $rows === [] ? 'open' : '' ?>>
    <summary><span class="node-row-no"><?= icon('sparkle') ?></span> <b>Akun baru</b> <span class="item-meta muted">guru atau admin</span></summary>
    <div class="item-card-body">
      <form method="post" action="<?= base_url('admin/staf') ?>" class="stack staff-form" autocomplete="off">
        <?= csrf_field() ?>
        <?= $fields(null, 'new') ?>
        <div class="field">
          <label for="new-password">Kata sandi awal <span class="req">*</span></label>
          <input type="password" id="new-password" name="password" required minlength="12" maxlength="72" autocomplete="new-password">
          <p class="field-help">Minimal 12 karakter. Sampaikan langsung kepada pemilik akun, bukan lewat pesan grup.</p>
        </div>
        <div class="form-actions">
          <button class="btn btn-primary" type="submit"><?= icon('check') ?> Buat akun</button>
        </div>
      </form>
    </div>
  </details>
</div>
<?= $this->endSection() ?>
