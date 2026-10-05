<?php
/**
 * Sunting tantangan — `/admin/konten/node/{id}` → ContentController::node
 *
 * Form node (judul, instruksi, deskripsi ID/EN berdampingan, indikator,
 * config dengan field terpandu, judul + pengecoh per variasi) + bank soal
 * dikelompokkan per variasi dengan status tinjauan berwarna (draf abu, perlu
 * verifikasi kuning, terverifikasi hijau) + tambah soal. Setiap pengerjaan
 * siswa memakai SATU variasi yang dipilih acak (perombakan kelas 5).
 *
 * @var App\Entities\ChallengeNode                         $node
 * @var App\Entities\Level|null                            $level
 * @var list<App\Entities\ChallengeItem>                   $items
 * @var array<string, array<string, mixed>>                $indicators
 * @var list<App\Entities\ReadingPassage>                  $passages
 * @var list<string>                                       $interactions
 * @var bool                                               $locked
 * @var string                                             $ref          kode workbook, mis. tmg-4
 */
$engine      = (string) $node->engine_type;
$variants    = $node->variants();
$groups      = array_values(array_unique(array_merge(
    ['a', 'b', 'c'],
    array_keys($variants),
    array_filter(array_map(static fn ($item): string => (string) $item->variant_group, $items)),
)));
sort($groups);
$reviewClass = ['draft' => 'is-draft', 'needs_verification' => 'is-needs_verification', 'verified' => 'is-verified'];
$texts = [
    'title'       => ['Judul', 1, true],
    'instruction' => ['Perintah untuk siswa', 2, false],
    'description' => ['Cerita di kartu misi', 3, false],
];
// Data bersama form butir (admin/content/items), dioper eksplisit ke component()
$itemFormData = [
    'node'         => $node,
    'interactions' => $interactions,
    'passages'     => $passages,
    'indicators'   => $indicators,
    'difficulty'   => (string) ($level?->difficulty ?? ''),
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $node->text('title', 'id'),
    'eyebrow' => ($level?->text('name', 'id') ?? '') . ' · tantangan ke-' . $node->sequence . ' · kode ' . $ref . ' · ' . engine_label($engine),
]) ?>
<?php if ($level !== null): ?>
  <?= component('partials/content-nav', ['level' => $level, 'active' => 'level']) ?>
<?php endif ?>
<?= $this->include('partials/flash') ?>

<?= component('components/media-datalist', ['types' => ['image']]) ?>
<form method="post" action="<?= base_url('admin/konten/node/' . $node->id) ?>" class="form-section" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <h2>Tantangan</h2>

  <?php foreach ($texts as $field => [$label, $rows, $required]): ?>
    <div class="bilingual">
      <span class="bilingual-label"><?= esc($label) ?><?php if ($required): ?> <span class="req">*</span><?php endif ?></span>
      <div class="field">
        <label for="<?= $field ?>_id"><span class="lang-tag">ID</span> Indonesia</label>
        <?php if ($rows === 1): ?>
          <input type="text" id="<?= $field ?>_id" name="<?= $field ?>_id" <?= $required ? 'required' : '' ?> maxlength="150" value="<?= esc($node->{$field . '_id'} ?? '', 'attr') ?>">
        <?php else: ?>
          <textarea id="<?= $field ?>_id" name="<?= $field ?>_id" rows="<?= $rows ?>"><?= esc($node->{$field . '_id'} ?? '') ?></textarea>
        <?php endif ?>
      </div>
      <div class="field">
        <label for="<?= $field ?>_en"><span class="lang-tag">EN</span> Inggris</label>
        <?php if ($rows === 1): ?>
          <input type="text" id="<?= $field ?>_en" name="<?= $field ?>_en" <?= $required ? 'required' : '' ?> maxlength="150" value="<?= esc($node->{$field . '_en'} ?? '', 'attr') ?>">
        <?php else: ?>
          <textarea id="<?= $field ?>_en" name="<?= $field ?>_en" rows="<?= $rows ?>"><?= esc($node->{$field . '_en'} ?? '') ?></textarea>
        <?php endif ?>
      </div>
    </div>
  <?php endforeach ?>

  <div class="form-grid">
    <div class="field">
      <label for="engine_type">Jenis tantangan</label>
      <select id="engine_type" name="engine_type" <?= $locked ? 'disabled' : '' ?>>
        <?php foreach (config('Gelita')->engineTypes as $option): ?>
          <option value="<?= esc($option, 'attr') ?>" <?= $engine === $option ? 'selected' : '' ?>><?= esc(engine_label($option)) ?></option>
        <?php endforeach ?>
      </select>
      <?php if ($locked): ?>
        <input type="hidden" name="engine_type" value="<?= esc($engine, 'attr') ?>">
        <p class="field-help"><?= icon('lock') ?> Tidak dapat diubah karena tantangan ini sudah pernah dimainkan siswa.</p>
      <?php endif ?>
    </div>
    <div class="field">
      <label for="variant_code">Kode variasi tampilan (tidak wajib)</label>
      <input type="text" id="variant_code" name="variant_code" maxlength="60" value="<?= esc($node->variant_code ?? '', 'attr') ?>">
      <p class="field-help">Hanya untuk tantangan yang memakai tampilan khusus. Biarkan seperti adanya bila ragu.</p>
    </div>
    <div class="field">
      <label for="indicator_id">Indikator pembelajaran</label>
      <select id="indicator_id" name="indicator_id">
        <option value="">— tanpa indikator —</option>
        <?php foreach ($indicators as $code => $indicator): ?>
          <option value="<?= esc($indicator['id'], 'attr') ?>" <?= (int) $node->indicator_id === (int) $indicator['id'] ? 'selected' : '' ?>><?= esc($code) ?> — <?= esc($indicator['name_id']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  </div>

  <fieldset class="form-section">
    <legend class="panel-subtitle">Pengaturan tantangan</legend>
    <div class="form-grid">
      <p class="field-help span-all">Setiap kali bermain, siswa mendapat SATU variasi (a, b, atau c) yang dipilih acak — variasi yang sudah pernah didapat siswa itu dihindari selama masih ada yang lain. Semua soal aktif variasi itu dipakai, urut nomor urutnya. Siswa boleh mencoba sampai <?= (int) config('Gelita')->maxTries ?> kali per soal.</p>
      <?php if ($engine === 'puzzle'): ?>
        <?php $puzzleRule = App\Libraries\DifficultyRules::puzzle($level?->difficulty); ?>
        <div class="field">
          <span class="label">Ukuran papan</span>
          <p><b><?= esc(App\Libraries\DifficultyRules::layoutLabel($puzzleRule['grid'], $puzzleRule['shape'])) ?></b> — mengikuti tingkat kesulitan wilayah (<?= esc((string) ($level?->difficulty ?? 'mudah')) ?>).</p>
          <p class="field-help">Setiap variasi berisi SATU gambar (sediakan 3 variasi: a, b, c). Admin cukup mengunggah gambar utuh; sistem memotongnya otomatis. Soal yang sudah pernah dijawab tetap memakai ukuran saat soal itu dibuat.</p>
        </div>
      <?php elseif ($engine === 'rumpang'): ?>
        <input type="hidden" name="cfg[use_word_bank]" value="1">
        <input type="hidden" name="cfg[as_reading]" value="0">
        <label class="check"><input type="checkbox" name="cfg[as_reading]" value="1" <?= $node->isReading() ? 'checked' : '' ?>> Tampilkan soal satu variasi sebagai satu bacaan berurutan (setiap soal = satu potongan bacaan dengan satu ___)</label>
        <div class="field">
          <label for="cfg-distractor-count">Jumlah kata pengecoh yang ikut ditampilkan</label>
          <input type="number" id="cfg-distractor-count" name="cfg[distractor_count]" min="0" value="<?= esc($node->config('distractor_count') ?? 0, 'attr') ?>">
        </div>
      <?php elseif ($engine === 'boleh'): ?>
        <p class="field-help">Tombol jawaban siswa selalu <b>Benar</b> dan <b>Salah</b>. Setiap variasi berisi satu bacaan beserta pernyataannya.</p>
      <?php endif ?>
    </div>

    <div class="variant-list">
      <span class="label">Variasi soal — judul tampil<?= $engine === 'rumpang' ? ' dan kata pengecoh (kata yang salah tetapi masuk akal)' : '' ?>. Kosongkan judul untuk memakai judul tantangan.</span>
      <?php foreach ($groups as $group): ?>
        <?php $entry = $variants[$group] ?? []; ?>
        <fieldset class="variant-row">
          <legend>Variasi <code><?= esc($group) ?></code></legend>
          <div class="bilingual">
            <input type="text" name="cfg[variants][<?= esc($group, 'attr') ?>][title_id]" maxlength="150" value="<?= esc((string) ($entry['title_id'] ?? ''), 'attr') ?>" aria-label="Judul variasi <?= esc($group, 'attr') ?> (Indonesia)" placeholder="Judul (Indonesia)">
            <input type="text" name="cfg[variants][<?= esc($group, 'attr') ?>][title_en]" maxlength="150" value="<?= esc((string) ($entry['title_en'] ?? ''), 'attr') ?>" aria-label="Judul variasi <?= esc($group, 'attr') ?> (Inggris)" placeholder="Judul (Inggris)">
          </div>
          <?php if ($engine === 'rumpang'): ?>
            <?php $rows = array_merge(array_values((array) ($entry['distractors'] ?? [])), array_fill(0, 3, ['id' => '', 'en' => ''])); ?>
            <?php foreach ($rows as $index => $row): ?>
              <?php $rowId = is_array($row) ? ($row['id'] ?? $row['text_id'] ?? '') : (string) $row; ?>
              <?php $rowEn = is_array($row) ? ($row['en'] ?? $row['text_en'] ?? '') : ''; ?>
              <div class="bilingual">
                <input type="text" name="cfg[variants][<?= esc($group, 'attr') ?>][distractors][<?= $index ?>][id]" value="<?= esc($rowId, 'attr') ?>" aria-label="Pengecoh <?= $index + 1 ?> variasi <?= esc($group, 'attr') ?> (Indonesia)" placeholder="Pengecoh (Indonesia)">
                <input type="text" name="cfg[variants][<?= esc($group, 'attr') ?>][distractors][<?= $index ?>][en]" value="<?= esc($rowEn, 'attr') ?>" aria-label="Pengecoh <?= $index + 1 ?> variasi <?= esc($group, 'attr') ?> (Inggris)" placeholder="Pengecoh (Inggris)">
              </div>
            <?php endforeach ?>
          <?php endif ?>
        </fieldset>
      <?php endforeach ?>
    </div>

    <details class="disclosure">
      <summary>Pengaturan teknis (biasanya tidak perlu diubah)</summary>
      <div class="field json-field">
        <label for="config_json">Untuk petugas teknis. Isian di atas selalu menggantikan pengaturan yang sama di kotak ini.</label>
        <textarea id="config_json" name="config_json" rows="8" spellcheck="false"><?= esc(json_encode($node->config_json ?: [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></textarea>
      </div>
    </details>
  </fieldset>

  <fieldset class="form-section">
    <legend class="panel-subtitle">Gambar & suara tantangan</legend>
    <div class="media-grid">
      <?= component('components/media-field', [
          'id' => 'node-scene', 'label' => $engine === 'cari' ? 'Gambar adegan (wajib untuk Cari Objek)' : 'Gambar adegan / gambar utama',
          'keyName' => 'media_scene_key', 'fileName' => 'media_scene_file', 'mediaId' => $node->scene_media_id,
          'defaultKey' => 'challenge.' . $ref . '.scene', 'size' => '1280 × 720 px (16:9)',
          'help' => match ($engine) {
              'cari'    => 'Siswa mencari benda di gambar ini; letak tiap benda diatur di masing-masing soal.',
              'puzzle'  => 'Dipakai bila soal puzzle tidak punya gambar sendiri.',
              'rumpang' => 'Tampil di samping kalimat rumpang.',
              default   => 'Tidak wajib.',
          },
      ]) ?>
      <?= component('components/media-field', [
          'id' => 'node-bg', 'label' => 'Latar tantangan (tidak wajib)', 'keyName' => 'media_bg_key', 'fileName' => 'media_bg_file',
          'mediaId' => $node->background_media_id, 'defaultKey' => 'challenge.' . $ref . '.bg', 'size' => '1920 × 1080 px',
          'help' => 'Bila kosong, memakai latar wilayah.',
      ]) ?>
    </div>
    <div class="form-grid">
      <?= component('components/audio-select', ['id' => 'node-audio-id', 'name' => 'audio_intro_id', 'label' => 'Narasi kartu misi (Indonesia)', 'audioId' => $node->audio_intro_id, 'locale' => 'id']) ?>
      <?= component('components/audio-select', ['id' => 'node-audio-en', 'name' => 'audio_intro_en_id', 'label' => 'Narasi kartu misi (Inggris)', 'audioId' => $node->audio_intro_en_id, 'locale' => 'en']) ?>
    </div>
  </fieldset>

  <label class="check"><input type="checkbox" name="is_active" value="1" <?= $node->is_active ? 'checked' : '' ?>> Tantangan aktif (tampil di permainan)</label>

  <div class="form-actions">
    <button class="btn btn-primary" type="submit"><?= icon('check') ?> Simpan tantangan</button>
  </div>
</form>

<section class="stack">
  <h2 class="panel-title"><?= icon('list') ?> Kumpulan soal <span class="chip num"><?= count($items) ?> soal · satu variasi tiap bermain</span></h2>

  <?php if ($items === []): ?>
    <div class="empty-state"><?= icon('info') ?><p>Belum ada soal. Tambahkan soal di bawah, atau impor soal dari Excel.</p></div>
  <?php endif ?>

  <div class="item-list">
    <?php foreach ($items as $item): ?>
      <details class="item-card" id="item-<?= esc($item->id, 'attr') ?>">
        <summary>
          <code><?= esc($item->item_key) ?></code>
          <span class="badge is-muted">variasi <?= esc($item->variant_group ?? '—') ?></span>
          <span class="item-prompt"><?= esc($item->text('prompt', 'id') ?: '—') ?></span>
          <span class="item-meta">
            <span class="badge is-muted"><?= esc(admin_label('interaction', (string) $item->interaction_type)) ?></span>
            <span class="badge <?= $reviewClass[$item->review_status] ?? 'is-muted' ?>"><?= esc(admin_label('review', (string) $item->review_status)) ?></span>
            <?php if (! $item->scorable): ?><span class="badge is-muted">tidak dinilai</span><?php endif ?>
            <?php if (! $item->is_active): ?><span class="badge is-inactive">nonaktif</span><?php endif ?>
          </span>
        </summary>
        <div class="item-card-body">
          <?= component('admin/content/items', ['item' => $item] + $itemFormData) ?>

          <details class="confirm">
            <summary class="btn btn-quiet btn-sm"><?= icon('trash') ?> Hapus soal</summary>
            <div class="confirm-box">
              <h3>Hapus soal <?= esc($item->item_key) ?>?</h3>
              <p>Soal yang sudah pernah dijawab siswa tidak dihapus, hanya dinonaktifkan, supaya data penelitian tetap utuh.</p>
              <form method="post" action="<?= base_url('admin/konten/item/' . $item->id . '/hapus') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-danger btn-sm" type="submit">Ya, hapus</button>
              </form>
            </div>
          </details>
        </div>
      </details>
    <?php endforeach ?>
  </div>

  <details class="item-card" <?= $items === [] ? 'open' : '' ?>>
    <summary><?= icon('check') ?> <b>Tambah soal</b> <span class="item-meta muted">soal baru berstatus draf</span></summary>
    <div class="item-card-body">
      <?= component('admin/content/items', ['item' => null] + $itemFormData) ?>
    </div>
  </details>
</section>
<?= $this->endSection() ?>
