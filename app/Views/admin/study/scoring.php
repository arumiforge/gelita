<?php
/**
 * Profil skoring — `/admin/studi/skoring` → StudyController::scoringProfiles
 *
 * Profil tidak pernah diubah di tempat: perubahan bobot atau ambang selalu
 * menjadi versi baru, supaya skor lama tetap dapat dijelaskan dengan versi
 * yang menghasilkannya (scoring_version tercatat di setiap attempt).
 * Form profil baru diisi awal dengan nilai profil aktif.
 * Di layar, "profil skoring" disebut aturan penilaian.
 *
 * @var list<array<string, mixed>> $profiles
 * @var array<string, mixed>       $active
 */
$num = static fn ($value, int $decimals = 2): string => fmt_num((float) $value, $decimals, 'id');
$pct = static fn ($value): string => fmt_num((float) $value * 100, 0, 'id') . '%';
$pre = static fn (string $key, string $default = '0') => (string) ($active[$key] ?? $default);
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => 'Aturan penilaian',
    'eyebrow' => 'Pengelolaan · pengaturan penelitian',
    'lead'    => 'Cara skor dan bintang setiap tantangan dihitung. Aturan yang sudah dipakai tidak pernah diubah langsung: perubahan selalu disimpan sebagai versi baru, sehingga skor lama tetap dapat dijelaskan dengan aturan yang menghasilkannya.',
]) ?>
<ul class="sub-nav">
  <li><a href="<?= base_url('admin/studi') ?>"><?= icon('flask') ?> Studi & fase</a></li>
  <li><a href="<?= base_url('admin/studi/rilis') ?>"><?= icon('list') ?> Versi permainan</a></li>
  <li><a href="<?= base_url('admin/studi/skoring') ?>" aria-current="page"><?= icon('target') ?> Aturan penilaian</a></li>
</ul>
<?= $this->include('partials/flash') ?>

<section class="explain">
  <h2><?= icon('info') ?> Cara skor dihitung sekarang (aturan <?= esc($active['code']) ?> versi <?= esc($active['version']) ?>)</h2>
  <p>
    Skor (0–100) = <b class="num"><?= $pct($active['first_pass_weight']) ?></b> dari jawaban yang <b>benar sejak percobaan pertama</b>
    + <b class="num"><?= $pct($active['final_weight']) ?></b> dari jawaban yang <b>benar di akhir</b>
    + <b class="num"><?= $pct($active['independence_weight']) ?></b> dari <b>kemandirian</b>.
  </p>
  <p>
    Kemandirian dimulai dari 100, lalu dikurangi <b class="num"><?= $num($active['hint_penalty_per_use'], 1) ?></b> untuk setiap petunjuk yang dibuka
    dan <b class="num"><?= $num($active['retry_penalty_per_extra_attempt'], 1) ?></b> untuk setiap kali mengulang.
  </p>
  <p>
    ★★★ bila skor minimal <b class="num"><?= $num($active['three_star_min_score'], 0) ?></b> dan jawaban benar sejak awal minimal <b class="num"><?= $num($active['three_star_min_first_pass'], 0) ?></b>%;
    ★★ bila skor minimal <b class="num"><?= $num($active['two_star_min_score'], 0) ?></b>; ★ cukup dengan menyelesaikan tantangan.
  </p>
</section>

<section class="panel">
  <h2 class="panel-title"><?= icon('list') ?> Semua versi aturan</h2>
  <?= component('components/admin-table', [
      'caption'  => 'Daftar versi aturan penilaian',
      'rows'     => $profiles,
      'rowClass' => static fn (array $row): string => $row['is_active'] ? 'is-good' : '',
      'columns'  => [
          'code'                            => ['label' => 'Aturan', 'render' => static fn (array $row): string => '<code>' . esc($row['code']) . '</code> versi ' . esc($row['version'])],
          'first_pass_weight'               => ['label' => 'Bobot benar sejak awal', 'format' => 'num', 'decimals' => 2],
          'final_weight'                    => ['label' => 'Bobot benar di akhir', 'format' => 'num', 'decimals' => 2],
          'independence_weight'             => ['label' => 'Bobot kemandirian', 'format' => 'num', 'decimals' => 2],
          'hint_penalty_per_use'            => ['label' => 'Potongan per petunjuk', 'format' => 'num', 'decimals' => 1],
          'retry_penalty_per_extra_attempt' => ['label' => 'Potongan per ulangan', 'format' => 'num', 'decimals' => 1],
          'three_star_min_score'            => ['label' => '★★★ skor minimal', 'format' => 'num'],
          'three_star_min_first_pass'       => ['label' => '★★★ benar sejak awal minimal (%)', 'format' => 'num'],
          'two_star_min_score'              => ['label' => '★★ skor minimal', 'format' => 'num'],
          'is_active'                       => ['label' => 'Status', 'render' => static fn (array $row): string => $row['is_active'] ? '<span class="badge is-active">' . icon('check') . ' aktif</span>' : '<span class="badge is-muted">arsip</span>'],
      ],
  ]) ?>
</section>

<details class="form-section">
  <summary class="panel-title"><?= icon('sparkle') ?> Buat versi aturan baru</summary>
  <form method="post" action="<?= base_url('admin/studi/skoring') ?>" class="stack">
    <?= csrf_field() ?>
    <p class="muted">Isian sudah diisi dengan aturan yang aktif sekarang. Ubah yang perlu, beri nomor versi baru, lalu simpan. Ubah aturan penilaian hanya bila penelitian memerlukannya, dan jangan di tengah pengambilan data.</p>

    <div class="form-grid">
      <div class="field">
        <label for="code">Nama aturan <span class="req">*</span></label>
        <input type="text" id="code" name="code" required maxlength="50" spellcheck="false" value="<?= esc($pre('code', ''), 'attr') ?>">
      </div>
      <div class="field">
        <label for="version">Nomor versi baru <span class="req">*</span></label>
        <input type="text" id="version" name="version" required maxlength="20" spellcheck="false" placeholder="mis. 2.1">
        <p class="field-help">Pasangan nama aturan + nomor versi harus belum pernah dipakai.</p>
      </div>
    </div>

    <fieldset class="repeat-row">
      <legend><?= icon('target') ?> Bobot skor</legend>
      <p class="field-help">Tulis sebagai pecahan 0–1 (mis. 0.7 = 70%). Jumlah ketiga bobot sebaiknya 1 agar skor tertinggi 100.</p>
      <div class="form-grid">
        <div class="field">
          <label for="first_pass_weight">Benar sejak percobaan pertama <span class="req">*</span></label>
          <input type="number" id="first_pass_weight" name="first_pass_weight" required step="0.01" min="0" max="1" value="<?= esc($pre('first_pass_weight'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="final_weight">Benar di akhir <span class="req">*</span></label>
          <input type="number" id="final_weight" name="final_weight" required step="0.01" min="0" max="1" value="<?= esc($pre('final_weight'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="independence_weight">Kemandirian <span class="req">*</span></label>
          <input type="number" id="independence_weight" name="independence_weight" required step="0.01" min="0" max="1" value="<?= esc($pre('independence_weight'), 'attr') ?>">
        </div>
      </div>
    </fieldset>

    <fieldset class="repeat-row">
      <legend><?= icon('hint') ?> Potongan nilai kemandirian (dari 100)</legend>
      <div class="form-grid">
        <div class="field">
          <label for="hint_penalty_per_use">Untuk setiap petunjuk yang dibuka</label>
          <input type="number" id="hint_penalty_per_use" name="hint_penalty_per_use" step="0.5" min="0" max="100" value="<?= esc($pre('hint_penalty_per_use'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="retry_penalty_per_extra_attempt">Untuk setiap kali mengulang</label>
          <input type="number" id="retry_penalty_per_extra_attempt" name="retry_penalty_per_extra_attempt" step="0.5" min="0" max="100" value="<?= esc($pre('retry_penalty_per_extra_attempt'), 'attr') ?>">
        </div>
      </div>
    </fieldset>

    <fieldset class="repeat-row">
      <legend><?= icon('star') ?> Syarat bintang</legend>
      <div class="form-grid">
        <div class="field">
          <label for="three_star_min_score">★★★ skor minimal</label>
          <input type="number" id="three_star_min_score" name="three_star_min_score" step="1" min="0" max="100" value="<?= esc($pre('three_star_min_score'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="three_star_min_first_pass">★★★ benar sejak awal minimal (%)</label>
          <input type="number" id="three_star_min_first_pass" name="three_star_min_first_pass" step="1" min="0" max="100" value="<?= esc($pre('three_star_min_first_pass'), 'attr') ?>">
        </div>
        <div class="field">
          <label for="two_star_min_score">★★ skor minimal</label>
          <input type="number" id="two_star_min_score" name="two_star_min_score" step="1" min="0" max="100" value="<?= esc($pre('two_star_min_score'), 'attr') ?>">
        </div>
      </div>
    </fieldset>

    <div class="check-row">
      <label class="check"><input type="checkbox" name="activate" value="1"> Langsung pakai aturan ini untuk tantangan berikutnya</label>
    </div>
    <div class="form-actions">
      <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan versi aturan</button>
    </div>
  </form>
</details>
<?= $this->endSection() ?>
