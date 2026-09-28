<?php
/**
 * Sunting tantangan — `/admin/konten/node/{id}` → ContentController::node
 *
 * Form node (judul, instruksi, deskripsi ID/EN berdampingan, indikator,
 * profil skoring, config dengan field terpandu) + bank butir dengan status
 * tinjauan berwarna (draf abu, perlu verifikasi kuning, terverifikasi hijau)
 * + tambah butir.
 *
 * @var App\Entities\ChallengeNode                         $node
 * @var App\Entities\Level|null                            $level
 * @var list<App\Entities\ChallengeItem>                   $items
 * @var array<int, list<App\Entities\ChallengeOption>>     $options
 * @var array<string, array<string, mixed>>                $indicators
 * @var list<App\Entities\ReadingPassage>                  $passages
 * @var list<array<string, mixed>>                         $profiles
 * @var list<string>                                       $interactions
 * @var bool                                               $locked
 * @var string                                             $ref          kode workbook, mis. tmg-4
 */
$engine      = (string) $node->engine_type;
$verdicts    = $node->verdictOptions();
$distractors = (array) $node->config('distractors', []);
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
];
?>
<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?= component('partials/admin-head', [
    'title'   => $node->text('title', 'id'),
    'eyebrow' => ($level?->text('name', 'id') ?? '') . ' · tantangan ke-' . $node->sequence . ' · kode ' . $ref . ' · ' . engine_label($engine),
    'actions' => '<a class="btn btn-quiet btn-sm" href="' . base_url('admin/analitik/node/' . $node->id) . '">' . icon('chart') . ' Lihat hasil siswa</a>',
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
    <div class="field">
      <label for="scoring_profile_id">Aturan penilaian</label>
      <select id="scoring_profile_id" name="scoring_profile_id">
        <option value="">— ikut aturan yang sedang aktif (disarankan) —</option>
        <?php foreach ($profiles as $profile): ?>
          <option value="<?= esc($profile['id'], 'attr') ?>" <?= (int) $node->scoring_profile_id === (int) $profile['id'] ? 'selected' : '' ?>><?= esc($profile['code'] . ' versi ' . $profile['version']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  </div>

  <fieldset class="form-section">
    <legend class="panel-subtitle">Pengaturan tantangan</legend>
    <div class="form-grid">
      <div class="field">
        <label for="cfg-items">Jumlah soal tiap kali bermain</label>
        <input type="number" id="cfg-items" name="cfg[items_per_round]" min="1" value="<?= esc($node->itemsPerRound(), 'attr') ?>">
        <p class="field-help">Diambil dari kumpulan soal di bawah. Sediakan soal 1,5–2 kali lebih banyak dari angka ini.</p>
      </div>
      <input type="hidden" name="cfg[allow_retry]" value="0">
      <label class="check"><input type="checkbox" name="cfg[allow_retry]" value="1" <?= $node->allowsRetry() ? 'checked' : '' ?>> Siswa boleh memperbaiki jawaban setelah menekan Periksa</label>

      <?php if ($engine === 'puzzle'): ?>
        <div class="field">
          <label for="cfg-grid">Jumlah keping per sisi (3 = 3 × 3 keping)</label>
          <input type="number" id="cfg-grid" name="cfg[grid]" min="2" max="5" value="<?= esc($node->config('grid') ?? 3, 'attr') ?>">
        </div>
      <?php elseif ($engine === 'rumpang'): ?>
        <input type="hidden" name="cfg[use_word_bank]" value="0">
        <label class="check"><input type="checkbox" name="cfg[use_word_bank]" value="1" <?= $node->config('use_word_bank') ? 'checked' : '' ?>> Siswa memilih jawaban dari daftar kata (bank kata)</label>
        <div class="field">
          <label for="cfg-distractor-count">Jumlah kata pengecoh yang ikut ditampilkan</label>
          <input type="number" id="cfg-distractor-count" name="cfg[distractor_count]" min="0" value="<?= esc($node->config('distractor_count') ?? 0, 'attr') ?>">
        </div>
      <?php elseif ($engine === 'boleh'): ?>
        <fieldset class="field">
          <legend class="label">Tombol jawaban yang tampil untuk siswa</legend>
          <div class="check-row">
            <?php foreach (['benar' => 'Benar', 'salah' => 'Salah', 'pendapat' => 'Pendapat'] as $value => $label): ?>
              <label class="check"><input type="checkbox" name="cfg[verdict_options][]" value="<?= $value ?>" <?= in_array($value, $verdicts, true) ? 'checked' : '' ?>> <?= $label ?></label>
            <?php endforeach ?>
          </div>
        </fieldset>
        <input type="hidden" name="cfg[require_reason]" value="0">
        <label class="check"><input type="checkbox" name="cfg[require_reason]" value="1" <?= $node->requiresReason() ? 'checked' : '' ?>> Siswa juga menulis alasan</label>
      <?php elseif ($engine === 'pilihan'): ?>
        <input type="hidden" name="cfg[shuffle_options]" value="0">
        <label class="check"><input type="checkbox" name="cfg[shuffle_options]" value="1" <?= $node->config('shuffle_options') ? 'checked' : '' ?>> Acak urutan pilihan jawaban</label>
      <?php elseif ($engine === 'cari'): ?>
        <input type="hidden" name="cfg[show_decoys]" value="0">
        <label class="check"><input type="checkbox" name="cfg[show_decoys]" value="1" <?= $node->config('show_decoys') ? 'checked' : '' ?>> Tampilkan benda jebakan (benda dari daerah lain)</label>
      <?php endif ?>
    </div>

    <?php if ($engine === 'rumpang'): ?>
      <div class="field">
        <span class="label">Kata pengecoh — kata yang salah tetapi masuk akal (Indonesia / Inggris). Baris kosong diabaikan.</span>
        <?php $rows = array_merge(array_values($distractors), array_fill(0, 3, ['id' => '', 'en' => ''])); ?>
        <?php foreach ($rows as $index => $entry): ?>
          <?php $entryId = is_array($entry) ? ($entry['id'] ?? $entry['text_id'] ?? '') : (string) $entry; ?>
          <?php $entryEn = is_array($entry) ? ($entry['en'] ?? $entry['text_en'] ?? '') : ''; ?>
          <div class="bilingual">
            <input type="text" name="cfg[distractors][<?= $index ?>][id]" value="<?= esc($entryId, 'attr') ?>" aria-label="Pengecoh <?= $index + 1 ?> (Indonesia)" placeholder="Indonesia">
            <input type="text" name="cfg[distractors][<?= $index ?>][en]" value="<?= esc($entryEn, 'attr') ?>" aria-label="Pengecoh <?= $index + 1 ?> (Inggris)" placeholder="Inggris">
          </div>
        <?php endforeach ?>
      </div>
    <?php endif ?>

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
  <h2 class="panel-title"><?= icon('list') ?> Kumpulan soal <span class="chip num"><?= count($items) ?> soal · <?= $node->itemsPerRound() ?> dipakai tiap bermain</span></h2>

  <?php if ($items === []): ?>
    <div class="empty-state"><?= icon('info') ?><p>Belum ada soal. Tambahkan soal di bawah, atau impor soal dari Excel.</p></div>
  <?php endif ?>

  <div class="item-list">
    <?php foreach ($items as $item): ?>
      <details class="item-card" id="item-<?= esc($item->id, 'attr') ?>">
        <summary>
          <code><?= esc($item->item_key) ?></code>
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

          <?php if (in_array($item->interaction_type, ['single_choice', 'source_trust'], true)): ?>
            <?php
            $optionRows = array_values($options[$item->id] ?? []);
            $keys       = array_map(static fn ($o): string => (string) $o->option_key, $optionRows);
            foreach (['a', 'b', 'c', 'd'] as $key) {
                if (count($optionRows) >= 4) {
                    break;
                }
                if (! in_array($key, $keys, true)) {
                    $optionRows[] = null;
                    $keys[]       = $key;
                }
            }
            ?>
            <form method="post" action="<?= base_url('admin/konten/item/' . $item->id . '/opsi') ?>" class="form-section option-editor" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <h3 class="panel-subtitle">Pilihan jawaban — tandai tepat satu yang benar</h3>
              <?php foreach ($optionRows as $index => $option): ?>
                <?php $key = $option?->option_key ?? $keys[$index]; ?>
                <fieldset class="option-row">
                  <legend class="visually-hidden">Pilihan <?= esc($key) ?></legend>
                  <span class="option-key"><?= esc(strtoupper((string) $key)) ?></span>
                  <div class="stack">
                    <input type="hidden" name="options[<?= $index ?>][option_key]" value="<?= esc($key, 'attr') ?>">
                    <input type="hidden" name="options[<?= $index ?>][display_order]" value="<?= esc($option?->display_order ?? $index + 1, 'attr') ?>">
                    <div class="bilingual">
                      <input type="text" name="options[<?= $index ?>][label_id]" value="<?= esc($option?->label_id ?? '', 'attr') ?>" aria-label="Teks pilihan <?= esc($key, 'attr') ?> (Indonesia)" placeholder="Teks pilihan (Indonesia)">
                      <input type="text" name="options[<?= $index ?>][label_en]" value="<?= esc($option?->label_en ?? '', 'attr') ?>" aria-label="Teks pilihan <?= esc($key, 'attr') ?> (Inggris)" placeholder="Teks pilihan (Inggris)">
                      <input type="text" name="options[<?= $index ?>][feedback_id]" value="<?= esc($option?->feedback_id ?? '', 'attr') ?>" aria-label="Penjelasan pilihan <?= esc($key, 'attr') ?> (Indonesia)" placeholder="Penjelasan setelah dipilih (Indonesia, tidak wajib)">
                      <input type="text" name="options[<?= $index ?>][feedback_en]" value="<?= esc($option?->feedback_en ?? '', 'attr') ?>" aria-label="Penjelasan pilihan <?= esc($key, 'attr') ?> (Inggris)" placeholder="Penjelasan setelah dipilih (Inggris, tidak wajib)">
                    </div>
                    <?= component('components/media-field', [
                        'id'         => 'opt' . $item->id . '-' . $index,
                        'label'      => 'Gambar pilihan ' . strtoupper((string) $key) . ' (tidak wajib)',
                        'keyName'    => 'options[' . $index . '][media_key]',
                        'fileName'   => 'option_media[' . $index . ']',
                        'mediaId'    => $option?->media_asset_id,
                        'defaultKey' => 'challenge.option.' . App\Libraries\MediaStore::slug(str_starts_with((string) $key, (string) $item->item_key) ? (string) $key : $item->item_key . '-' . $key),
                        'size'       => '400 × 400 px',
                    ]) ?>
                    <label class="check"><input type="radio" name="correct_option" value="<?= esc($key, 'attr') ?>" <?= $option?->is_correct ? 'checked' : '' ?>> Ini jawaban yang benar</label>
                  </div>
                </fieldset>
              <?php endforeach ?>
              <p class="field-help">Pilihan yang teks Indonesianya kosong tidak disimpan. Setiap pilihan yang disimpan wajib punya teks Inggris juga.</p>
              <div class="form-actions">
                <button class="btn btn-primary btn-sm" type="submit"><?= icon('check') ?> Simpan pilihan jawaban</button>
              </div>
            </form>
          <?php endif ?>

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
