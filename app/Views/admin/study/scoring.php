<?php
/**
 * Profil skoring — `/admin/studi/skoring` → StudyController::scoringProfiles
 *
 * Profil tidak pernah diubah di tempat: perubahan bobot atau ambang selalu
 * menjadi versi baru, supaya skor lama tetap dapat dijelaskan dengan versi
 * yang menghasilkannya (scoring_version tercatat di setiap attempt).
 * Form profil baru diisi awal dengan nilai profil aktif.
 *
 * @var list<array<string, mixed>> $profiles
 * @var array<string, mixed>       $active
 */
$num = static fn ($value, int $decimals = 2): string => fmt_num((float) $value, $decimals, 'id');
$pre = static fn (string $key, string $default = '0') => (string) ($active[$key] ?? $default);
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Profil skoring',
    'eyebrow' => 'Pengelolaan · penelitian',
    'lead'    => 'Bobot dan ambang bintang untuk menilai setiap percobaan tantangan. Mengubah skoring berarti membuat versi baru; skor lama tetap tercatat dengan versinya sendiri.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/studi') ?>"><?= icon('flask') ?> Studi & fase</a></li>
  <li><a href="<?= base_url('admin/studi/rilis') ?>"><?= icon('list') ?> Rilis konten</a></li>
  <li><a href="<?= base_url('admin/studi/skoring') ?>" aria-current="page"><?= icon('target') ?> Profil skoring</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<section class="explain">
  <h2><?= icon('info') ?> Cara skor dihitung (profil <?= esc($active['code']) ?> v<?= esc($active['version']) ?>)</h2>
  <p>
    Skor = <b class="num"><?= $num($active['first_pass_weight']) ?></b> × ketepatan percobaan pertama
    + <b class="num"><?= $num($active['final_weight']) ?></b> × ketepatan akhir
    + <b class="num"><?= $num($active['independence_weight']) ?></b> × kemandirian, dibatasi 0–100.
  </p>
  <p>
    Kemandirian = 100 − <b class="num"><?= $num($active['hint_penalty_per_use'], 1) ?></b> per petunjuk
    − <b class="num"><?= $num($active['retry_penalty_per_extra_attempt'], 1) ?></b> per percobaan ulang.
  </p>
  <p>
    ★★★ bila skor ≥ <b class="num"><?= $num($active['three_star_min_score'], 0) ?></b> dan ketepatan percobaan pertama ≥ <b class="num"><?= $num($active['three_star_min_first_pass'], 0) ?></b>%;
    ★★ bila skor ≥ <b class="num"><?= $num($active['two_star_min_score'], 0) ?></b>; ★ cukup dengan menyelesaikan tantangan.
  </p>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('list') ?> Semua versi</h2>
  <?= component('components/admin-table', [
      'caption'  => 'Daftar profil skoring',
      'rows'     => $profiles,
      'rowClass' => static fn (array $row): string => $row['is_active'] ? 'is-good' : '',
      'columns'  => [
          'code'                            => ['label' => 'Profil', 'render' => static fn (array $row): string => '<code>' . esc($row['code']) . '</code> v' . esc($row['version'])],
          'first_pass_weight'               => ['label' => 'Bobot awal', 'format' => 'num', 'decimals' => 2],
          'final_weight'                    => ['label' => 'Bobot akhir', 'format' => 'num', 'decimals' => 2],
          'independence_weight'             => ['label' => 'Bobot mandiri', 'format' => 'num', 'decimals' => 2],
          'hint_penalty_per_use'            => ['label' => 'Penalti petunjuk', 'format' => 'num', 'decimals' => 1],
          'retry_penalty_per_extra_attempt' => ['label' => 'Penalti ulang', 'format' => 'num', 'decimals' => 1],
          'three_star_min_score'            => ['label' => '★★★ skor', 'format' => 'num'],
          'three_star_min_first_pass'       => ['label' => '★★★ awal %', 'format' => 'num'],
          'two_star_min_score'              => ['label' => '★★ skor', 'format' => 'num'],
          'is_active'                       => ['label' => 'Status', 'render' => static fn (array $row): string => $row['is_active'] ? '<span class="badge is-active">' . icon('check') . ' aktif</span>' : '<span class="badge is-muted">arsip</span>'],
      ],
  ]) ?>
</section>

<details class="form-section">
  <summary class="panel-title"><?= icon('sparkle') ?> Versi profil baru</summary>
  <form method="post" action="<?= base_url('admin/studi/skoring') ?>" class="stack">
    <?= csrf_field() ?>
    <p class="muted">Diisi awal dengan nilai profil aktif. Ubah yang perlu, beri versi baru, lalu simpan.</p>

    <div class="form-grid">
      <div class="field">
        <label for="code">Kode profil <span class="req">*</span></label>
        <input type="text" id="code" name="code" required maxlength="50" spellcheck="false" value="<?= esc($pre('code', ''), 'attr') ?>">
      </div>
      <div class="field">
        <label for="version">Versi baru <span class="req">*</span></label>
        <input type="text" id="version" name="version" required maxlength="20" spellcheck="false" placeholder="mis. 2.1">
        <p class="field-help">Kombinasi kode + versi harus belum ada.</p>
      </div>
    </div>

    <fieldset class="repeat-row">
      <legend><?= icon('target') ?> Bobot skor</legend>
      <p class="field-help">Jumlah ketiga bobot sebaiknya 1,00 agar skor maksimum 100.</p>
      <div class="form-grid">
        <div class="field">
          <label for="first_pass_weight">Ketepatan percobaan pertama <span class="req">*</span></label>
          <input type="number" id="first_pass_weight" name="first_pass_weight" required step="0.01" min="0" max="1" value="<?= esc($pre('first_pass_weight'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="final_weight">Ketepatan akhir <span class="req">*</span></label>
          <input type="number" id="final_weight" name="final_weight" required step="0.01" min="0" max="1" value="<?= esc($pre('final_weight'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="independence_weight">Kemandirian <span class="req">*</span></label>
          <input type="number" id="independence_weight" name="independence_weight" required step="0.01" min="0" max="1" value="<?= esc($pre('independence_weight'), 'attr') ?>">
        </div>
      </div>
    </fieldset>

    <fieldset class="repeat-row">
      <legend><?= icon('hint') ?> Penalti kemandirian (poin dari 100)</legend>
      <div class="form-grid">
        <div class="field">
          <label for="hint_penalty_per_use">Per petunjuk yang dipakai</label>
          <input type="number" id="hint_penalty_per_use" name="hint_penalty_per_use" step="0.5" min="0" max="100" value="<?= esc($pre('hint_penalty_per_use'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="retry_penalty_per_extra_attempt">Per percobaan ulang</label>
          <input type="number" id="retry_penalty_per_extra_attempt" name="retry_penalty_per_extra_attempt" step="0.5" min="0" max="100" value="<?= esc($pre('retry_penalty_per_extra_attempt'), 'attr') ?>">
        </div>
      </div>
    </fieldset>

    <fieldset class="repeat-row">
      <legend><?= icon('star') ?> Ambang bintang</legend>
      <div class="form-grid">
        <div class="field">
          <label for="three_star_min_score">★★★ skor minimum</label>
          <input type="number" id="three_star_min_score" name="three_star_min_score" step="1" min="0" max="100" value="<?= esc($pre('three_star_min_score'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="three_star_min_first_pass">★★★ ketepatan pertama minimum (%)</label>
          <input type="number" id="three_star_min_first_pass" name="three_star_min_first_pass" step="1" min="0" max="100" value="<?= esc($pre('three_star_min_first_pass'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="two_star_min_score">★★ skor minimum</label>
          <input type="number" id="two_star_min_score" name="two_star_min_score" step="1" min="0" max="100" value="<?= esc($pre('two_star_min_score'), 'attr') ?>">
        </div>
      </div>
    </fieldset>

    <div class="check-row">
      <label class="check"><input type="checkbox" name="activate" value="1"> Langsung aktifkan untuk percobaan berikutnya</label>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan versi</button>
    </div>
  </form>
</details>
<?= $this->endSection() ?>
