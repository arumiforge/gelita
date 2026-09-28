<?php
/**
 * Sekolah — `/admin/sekolah` → SchoolController::index (admin saja)
 *
 * Siswa di provinsi berdirektori (Jawa Tengah) mendaftar dengan NPSN, jadi
 * satu sekolah = satu baris `schools`. Halaman ini: (1) mencari NPSN untuk
 * sesi kelas; (2) merapikan nama sekolah ketikan siswa — gabungkan ke sekolah
 * lain atau sahkan sebagai sekolah baru ber-NPSN. Entri yang digabung menjadi
 * alias: ketikan yang sama berikutnya langsung tertaut ke sekolah tujuan.
 *
 * @var string                            $query
 * @var string                            $district
 * @var array<string, string>             $districts kode => nama kab/kota berdirektori
 * @var list<array<string, mixed>>|null   $results   null = belum mencari
 * @var int                               $limit
 * @var list<array<string, mixed>>        $unverified + participant_count, region, suggestions
 * @var int                               $clearMatches
 * @var array{official: int, unverified: int, in_use: int} $stats
 * @var array<string, mixed>|null         $dataMeta
 */
$directory = service('schoolDirectory');
$dataDate  = isset($dataMeta['upstream_updated']) ? substr((string) $dataMeta['upstream_updated'], 0, 10) : null;
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Sekolah',
    'eyebrow' => 'Pengelolaan · direktori sekolah',
    'lead'    => 'Siswa di Jawa Tengah mendaftar dengan NPSN, jadi satu sekolah selalu tercatat dengan satu nama yang sama di laporan. Di halaman ini Anda mencari NPSN untuk sesi kelas dan merapikan nama sekolah yang diketik sendiri oleh siswa.',
]) ?>
<?= $this->include('partials/flash') ?>

<?php if ($stats['official'] === 0): ?>
  <div class="alert alert-warn" role="status">
    <?= icon('warn') ?>
    <p><b>Daftar sekolah resmi belum dipasang</b>, jadi siswa Jawa Tengah masih menulis nama sekolahnya sendiri. Minta petugas teknis memasang daftar sekolah (perintah <code>php spark gelita:schools:import</code> di server).</p>
  </div>
<?php endif ?>

<div class="kpi-grid">
  <?= component('stat-tile', [
      'label' => 'Sekolah resmi',
      'value' => fmt_num($stats['official'], 0, 'id'),
      'icon'  => 'building',
      'hint'  => $dataDate !== null ? 'data Kemendikdasmen per ' . $dataDate : null,
  ]) ?>
  <?= component('stat-tile', ['label' => 'Sekolah dengan siswa', 'value' => fmt_num($stats['in_use'], 0, 'id'), 'icon' => 'users']) ?>
  <?= component('stat-tile', ['label' => 'Nama sekolah perlu dirapikan', 'value' => fmt_num($stats['unverified'], 0, 'id'), 'icon' => 'warn', 'hint' => 'diketik sendiri oleh siswa']) ?>
</div>

<section class="form-section" aria-labelledby="school-search-title">
  <h2 id="school-search-title"><?= icon('search') ?> Cari sekolah</h2>
  <p class="muted">Ketik NPSN, nama sekolah, kecamatan, atau desa. Tulis NPSN sekolah Anda di papan tulis saat sesi kelas — siswa mengetik NPSN itu saat mendaftar.</p>
  <form method="get" action="<?= base_url('admin/sekolah') ?>" class="filter-bar" role="search">
    <div class="field">
      <label for="school-q">NPSN atau nama</label>
      <input type="search" id="school-q" name="q" value="<?= esc($query, 'attr') ?>" placeholder="mis. 20318068 atau sd 1 cendono" autocomplete="off">
    </div>
    <div class="field">
      <label for="school-kab">Kabupaten / kota</label>
      <select id="school-kab" name="kab">
        <option value="">Semua kabupaten/kota</option>
        <?php foreach ($districts as $code => $name): ?>
          <option value="<?= esc($code, 'attr') ?>" <?= $district === $code ? 'selected' : '' ?>><?= esc($name) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <button class="btn btn-primary" type="submit"><?= icon('search') ?> Cari</button>
    </div>
  </form>

  <?php if ($results !== null): ?>
    <?php if ($results === []): ?>
      <p class="alert alert-info"><?= icon('info') ?> Tidak ada sekolah yang cocok. Coba kata lain, atau cari NPSN-nya di <a href="https://referensi.data.kemendikdasmen.go.id/pendidikan/dikdas" target="_blank" rel="noopener">Data Referensi Kemendikdasmen</a>.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table class="data-table">
          <caption class="visually-hidden">Hasil pencarian sekolah</caption>
          <thead>
            <tr>
              <th scope="col">NPSN</th>
              <th scope="col">Sekolah</th>
              <th scope="col">Kabupaten/kota</th>
              <th scope="col" class="is-num">Jumlah siswa</th>
              <th scope="col">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($results as $school): ?>
              <tr>
                <td><?= $school['code'] !== null ? '<code>' . esc($school['code']) . '</code>' : '—' ?></td>
                <td>
                  <b><?= esc($school['name']) ?></b>
                  <span class="cell-sub"><?= esc($directory->metaLine(['district_code' => null] + $school)) ?><?= ! empty($school['village_name']) ? ' · Desa ' . esc(mb_convert_case($school['village_name'], MB_CASE_TITLE)) : '' ?></span>
                </td>
                <td><?= esc(\App\Libraries\RegionDirectory::districtName($school['district_code']) ?? '—') ?></td>
                <td class="is-num"><?= esc(fmt_num($school['participant_count'], 0, 'id')) ?></td>
                <td><span class="badge <?= (int) $school['is_verified'] === 1 ? 'is-verified' : 'is-warn' ?>"><?= (int) $school['is_verified'] === 1 ? 'resmi' : 'perlu dirapikan' ?></span></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
      <?php if (count($results) >= $limit): ?>
        <p class="muted">Menampilkan <?= $limit ?> sekolah pertama. Persempit pencarian dengan kata lain atau pilih kabupaten/kota.</p>
      <?php endif ?>
    <?php endif ?>
  <?php endif ?>
</section>

<section class="form-section" aria-labelledby="school-tidy-title">
  <h2 id="school-tidy-title"><?= icon('edit') ?> Rapikan nama sekolah</h2>
  <p class="muted">
    Nama-nama di bawah diketik sendiri oleh siswa, karena NPSN-nya tidak ada di daftar atau sekolahnya di luar Jawa Tengah.
    <b>Gabungkan</b> bila nama itu sebenarnya sekolah yang sudah ada: siswanya (dan akun guru) dipindahkan ke sekolah itu, dan ketikan yang sama berikutnya langsung ikut ke sana.
    <b>Sahkan</b> bila nama itu memang sekolah baru: sekolah itu didaftarkan dengan NPSN-nya sehingga bisa dipilih siswa berikutnya.
  </p>

  <?php if ($unverified === []): ?>
    <p class="alert alert-ok"><?= icon('check') ?> Tidak ada nama sekolah yang perlu dirapikan.</p>
  <?php else: ?>
    <form method="post" action="<?= base_url('admin/sekolah/gabung-otomatis') ?>" class="form-actions">
      <?= csrf_field() ?>
      <button class="btn btn-primary btn-sm" type="submit" <?= $clearMatches === 0 ? 'disabled' : '' ?>><?= icon('check') ?> Gabungkan otomatis yang sudah jelas (<?= $clearMatches ?>)</button>
      <span class="muted">Hanya nama yang pasti menunjuk satu sekolah resmi, mis. "SDN 01 Cendono" → SD 1 CENDONO.</span>
    </form>

    <div class="item-list">
      <?php foreach ($unverified as $school): ?>
        <?php $sid = (int) $school['id']; ?>
        <details class="item-card">
          <summary>
            <span class="node-row-no"><?= icon('building') ?></span>
            <span class="item-prompt">
              <b><?= esc($school['name']) ?></b>
              <span class="cell-sub"><?= esc($school['region']) ?></span>
            </span>
            <span class="item-meta">
              <span class="badge<?= $school['participant_count'] > 0 ? '' : ' is-muted' ?>"><?= icon('users') ?> <?= esc(fmt_num($school['participant_count'], 0, 'id')) ?> siswa</span>
              <?php if ($school['suggestions'] !== []): ?>
                <span class="badge is-warn"><?= count($school['suggestions']) ?> saran</span>
              <?php endif ?>
            </span>
          </summary>

          <div class="item-card-body">
            <?php if ($school['suggestions'] !== []): ?>
              <div class="stack">
                <p class="muted">Mungkin yang dimaksud:</p>
                <?php foreach ($school['suggestions'] as $candidate): ?>
                  <form method="post" action="<?= base_url('admin/sekolah/' . $sid . '/gabung') ?>" class="inline-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="target_id" value="<?= (int) $candidate['id'] ?>">
                    <button class="btn btn-quiet btn-sm" type="submit"><?= icon('right') ?> Gabungkan ke <b><?= esc($candidate['name']) ?></b></button>
                    <span class="cell-sub">
                      <?= $candidate['code'] !== null ? 'NPSN ' . esc($candidate['code']) . ' · ' : 'belum resmi · ' ?>
                      <?= ! empty($candidate['subdistrict_name']) ? 'Kec. ' . esc(mb_convert_case($candidate['subdistrict_name'], MB_CASE_TITLE)) : '' ?>
                      <?= ! empty($candidate['village_name']) ? ' · Desa ' . esc(mb_convert_case($candidate['village_name'], MB_CASE_TITLE)) : '' ?>
                    </span>
                  </form>
                <?php endforeach ?>
              </div>
            <?php endif ?>

            <form method="post" action="<?= base_url('admin/sekolah/' . $sid . '/gabung') ?>" class="inline-form">
              <?= csrf_field() ?>
              <label for="merge-<?= $sid ?>">Gabungkan ke sekolah dengan NPSN</label>
              <input type="text" id="merge-<?= $sid ?>" name="target_npsn" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" required autocomplete="off">
              <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Gabungkan</button>
            </form>

            <form method="post" action="<?= base_url('admin/sekolah/' . $sid . '/sahkan') ?>" class="form-grid">
              <?= csrf_field() ?>
              <div class="field">
                <label for="verify-npsn-<?= $sid ?>">NPSN sekolah ini <span class="req">*</span></label>
                <input type="text" id="verify-npsn-<?= $sid ?>" name="npsn" inputmode="numeric" maxlength="8" pattern="[0-9]{8}" required autocomplete="off">
              </div>
              <div class="field">
                <label for="verify-name-<?= $sid ?>">Nama resmi sekolah</label>
                <input type="text" id="verify-name-<?= $sid ?>" name="name" maxlength="200" value="<?= esc($school['name'], 'attr') ?>">
              </div>
              <div class="field">
                <button class="btn btn-quiet btn-sm" type="submit"><?= icon('shield') ?> Sahkan sebagai sekolah baru</button>
              </div>
            </form>
          </div>
        </details>
      <?php endforeach ?>
    </div>
  <?php endif ?>
</section>

<?php if ($dataMeta !== null): ?>
  <p class="muted">
    Sumber daftar sekolah resmi: <?= esc((string) ($dataMeta['source'] ?? '')) ?><?= $dataDate !== null ? ', data per ' . esc($dataDate) : '' ?>.
  </p>
  <details class="row-details muted">
    <summary>Catatan untuk petugas teknis</summary>
    <p>Memperbarui daftar sekolah: jalankan <code>php docs/sekolah/build-sekolah-jateng.php</code> lalu <code>php spark gelita:schools:import</code> di server.</p>
  </details>
<?php endif ?>
<?= $this->endSection() ?>
