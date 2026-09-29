<?php
/**
 * Form satu butir soal — dipakai content/node.php untuk setiap butir di bank
 * dan untuk "Tambah butir".
 *
 * Form berubah sesuai `interaction_type`: panduan bentuk answer_key_json dan
 * config_json untuk jenis yang dipilih ditampilkan lewat CSS :has() (tanpa
 * JavaScript). admin/content-editor.js menambah editor terpandu — kalimat ___,
 * verdict, dua sumber, potongan urutan, dan pemilih koordinat objek (dengan
 * gambar adegan node dari data-scene) — yang tetap menulis ke dua kolom JSON
 * yang sama; data-verdicts membatasi kunci verdict pada verdict_options node.
 * Selama editor terpandu aktif, contoh JSON (`.json-example`) dan kedua kolom
 * JSON disembunyikan sampai admin membuka "data teknis".
 *
 * Node `cari`: dua pemilih audio narasi petunjuk (ID/EN) untuk butir
 * `find_object`, yang dibacakan di samping teks pertanyaan. Rekaman biasanya
 * diimpor dari halaman Narasi (`petunjuk-{node}-NN.mp3`); objek jebakan tidak
 * punya petunjuk, jadi audionya tidak pernah diputar.
 *
 * @var App\Entities\ChallengeItem|null       $item      null = butir baru
 * @var App\Entities\ChallengeNode            $node
 * @var list<string>                          $interactions
 * @var list<App\Entities\ReadingPassage>     $passages
 * @var array<string, array<string, mixed>>   $indicators kode → baris indikator
 */
$isNew  = $item === null;
$prefix = $isNew ? 'new' : 'it' . $item->id;
$action = $isNew ? base_url('admin/konten/node/' . $node->id . '/item') : base_url('admin/konten/item/' . $item->id);
$json   = static fn ($value): string => $value === null || $value === [] ? '' : (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$verdicts = implode(' / ', $node->verdictOptions());

$guides = [
    'single_choice source_trust' => ['Pilihan ganda', 'Pilihan jawaban A–D diisi di bagian “Pilihan jawaban” di bawah formulir ini (muncul setelah soal disimpan). Tandai tepat satu yang benar.', '{"option_key": "a"}', ''],
    'fill_blank_bank'            => ['Isi rumpang — pilih kata', 'Tulis kalimat dengan ___ (tiga garis bawah) di bagian yang kosong. Kata jawabannya ikut masuk daftar pilihan kata bersama kata pengecoh tantangan ini.', '{"text_id": "kopi", "text_en": "coffee"}', ''],
    'fill_blank_free'            => ['Isi rumpang — ketik jawaban', 'Tulis kalimat dengan ___ (tiga garis bawah) di bagian yang kosong, lalu tulis semua jawaban yang boleh diterima.', '{"accept_id": ["Magelang"], "accept_en": ["Magelang"], "case_sensitive": false}', ''],
    'verdict_card'               => ['Kartu benar/salah', 'Siswa menilai sebuah pernyataan. Pilih kunci jawabannya dari tombol yang tampil di tantangan ini: ' . $verdicts . '. Boleh ditambah dua kartu sumber untuk dibandingkan.', '{"verdict": "benar"}', '{"sources": [{"label_id": "Sumber A", "label_en": "Source A", "kind": "official", "text_id": "…", "text_en": "…"}, {"label_id": "Sumber B", "label_en": "Source B", "kind": "anonymous", "text_id": "…", "text_en": "…"}]}'],
    'verdict_reason'             => ['Kartu benar/salah/pendapat + alasan', 'Seperti kartu benar/salah, tetapi siswa juga menulis alasannya. Alasan tidak dinilai otomatis; isi contoh alasan yang baik sebagai pegangan guru.', '{"verdict": "pendapat", "sample_reason_id": "…", "sample_reason_en": "…"}', ''],
    'find_object'                => ['Cari benda di gambar', 'Letakkan benda pada gambar adegan tantangan. Benda jebakan (dari daerah lain) tidak dinilai dan wajib diberi penjelasan yang muncul saat siswa salah memilihnya.', '{"target": true}', '{"x": 18, "y": 62, "w": 13, "decoy": false, "wrong_feedback_id": "…", "wrong_feedback_en": "…"}'],
    'puzzle_arrange'             => ['Susun kepingan gambar', 'Pilih gambar soal di bawah (persegi). Gambar dipotong menjadi keping-keping sesuai pengaturan tantangan, lalu siswa menyusunnya kembali.', '{"order": [0,1,2,3,4,5,6,7,8]}', '{"grid": 3}'],
    'ordering'                   => ['Urutkan kartu', 'Tulis kartu-kartunya dalam urutan yang benar; permainan mengacak urutannya untuk siswa.', '{"order": ["c", "a", "d", "b"]}', '{"pieces": [{"key": "a", "text_id": "…", "text_en": "…"}]}'],
];
?>
<form method="post" action="<?= esc($action, 'attr') ?>" class="form item-form" enctype="multipart/form-data"
      data-verdicts="<?= esc(json_encode($node->verdictOptions()), 'attr') ?>"
      <?= $node->scene_media_id && media_exists($node->scene_media_id) ? 'data-scene="' . esc(media_src($node->scene_media_id), 'attr') . '"' : '' ?>>
  <?= csrf_field() ?>

  <div class="form-grid">
    <?php if ($isNew): ?>
      <div class="field">
        <label for="<?= $prefix ?>-key">Kode soal <span class="req">*</span></label>
        <input type="text" id="<?= $prefix ?>-key" name="item_key" required maxlength="60" placeholder="tmg-1-99" spellcheck="false">
        <p class="field-help">Kode unik: kode tantangan + nomor, mis. tmg-1-12. Tidak dapat diubah setelah disimpan.</p>
      </div>
    <?php endif ?>
    <div class="field">
      <label for="<?= $prefix ?>-type">Jenis soal <span class="req">*</span></label>
      <select id="<?= $prefix ?>-type" name="interaction_type" required>
        <?php foreach ($interactions as $option): ?>
          <option value="<?= esc($option, 'attr') ?>" <?= ! $isNew && $item->interaction_type === $option ? 'selected' : '' ?>><?= esc(admin_label('interaction', $option)) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-seq">Urutan di daftar</label>
      <input type="number" id="<?= $prefix ?>-seq" name="sequence" min="0" value="<?= esc($isNew ? 0 : $item->sequence, 'attr') ?>">
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-review">Status pemeriksaan isi</label>
      <select id="<?= $prefix ?>-review" name="review_status">
        <?php foreach (['draft' => 'Draf', 'needs_verification' => 'Fakta perlu dicek', 'verified' => 'Sudah dicek guru/ahli'] as $value => $label): ?>
          <option value="<?= esc($value, 'attr') ?>" <?= ($isNew ? 'draft' : $item->review_status) === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-passage">Teks bacaan yang dipakai</label>
      <select id="<?= $prefix ?>-passage" name="passage_id">
        <option value="">— tanpa bacaan —</option>
        <?php foreach ($passages as $passage): ?>
          <option value="<?= esc($passage->id, 'attr') ?>" <?= ! $isNew && (int) $item->passage_id === $passage->id ? 'selected' : '' ?>><?= esc($passage->passage_key . (($passage->title_id ?? '') !== '' ? ' — ' . $passage->title_id : '')) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-indicator">Indikator pembelajaran</label>
      <select id="<?= $prefix ?>-indicator" name="indicator_id">
        <option value="">— ikut indikator tantangan —</option>
        <?php foreach ($indicators as $code => $indicator): ?>
          <option value="<?= esc($indicator['id'], 'attr') ?>" <?= ! $isNew && (int) $item->indicator_id === (int) $indicator['id'] ? 'selected' : '' ?>><?= esc($code) ?> — <?= esc($indicator['name_id']) ?></option>
        <?php endforeach ?>
      </select>
    </div>
  </div>

  <div class="bilingual">
    <span class="bilingual-label">Pertanyaan / pernyataan</span>
    <div class="field">
      <label for="<?= $prefix ?>-prompt"><span class="lang-tag">ID</span> Indonesia</label>
      <textarea id="<?= $prefix ?>-prompt" name="prompt_id" rows="2"><?= esc($isNew ? '' : ($item->prompt_id ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-prompt-en"><span class="lang-tag">EN</span> Inggris</label>
      <textarea id="<?= $prefix ?>-prompt-en" name="prompt_en" rows="2"><?= esc($isNew ? '' : ($item->prompt_en ?? '')) ?></textarea>
    </div>
    <p class="field-help">Untuk soal rumpang, tandai bagian yang kosong dengan tiga garis bawah <code>___</code>. Kolom Inggris boleh dikosongkan — pemain berbahasa Inggris akan membaca teks Indonesia.</p>
  </div>

  <?php if ($node->engine_type === 'cari'): ?>
    <div class="bilingual">
      <span class="bilingual-label">Rekaman petunjuk (dibacakan Mbah Kedu)</span>
      <?= component('components/audio-select', ['id' => $prefix . '-audio-id', 'name' => 'audio_prompt_id', 'label' => 'Rekaman petunjuk (Indonesia)', 'audioId' => $isNew ? null : $item->audio_prompt_id, 'locale' => 'id']) ?>
      <?= component('components/audio-select', ['id' => $prefix . '-audio-en', 'name' => 'audio_prompt_en_id', 'label' => 'Rekaman petunjuk (Inggris)', 'audioId' => $isNew ? null : $item->audio_prompt_en_id, 'locale' => 'en']) ?>
      <p class="field-help">Isi rekamannya sama dengan teks pertanyaan di atas. Unggah rekaman bernama <code>petunjuk-{node}-NN.mp3</code> (mis. <code>petunjuk-tmg-4-01.mp3</code>) di halaman <a href="<?= base_url('admin/konten/narasi') ?>">Rekaman narasi</a>; siswa baru mendengarnya setelah rekaman disetujui. Benda jebakan tidak perlu rekaman.</p>
    </div>
  <?php endif ?>

  <?= component('components/media-field', [
      'id'         => $prefix . '-media',
      'label'      => match ($node->engine_type) {
          'puzzle' => 'Gambar puzzle',
          'cari'   => 'Gambar benda',
          default  => 'Gambar soal (tidak wajib)',
      },
      'keyName'    => 'media_item_key',
      'fileName'   => 'media_item_file',
      'mediaId'    => $isNew ? null : $item->media_asset_id,
      'defaultKey' => $isNew ? 'challenge.item.{item_key}' : 'challenge.item.' . App\Libraries\MediaStore::slug((string) $item->item_key),
      'size'       => match ($node->engine_type) {
          'puzzle' => '900 × 900 px (persegi)',
          'cari'   => '240 × 240 px, latar transparan (PNG/WebP)',
          default  => '960 × 640 px',
      },
  ]) ?>

  <div class="bilingual">
    <span class="bilingual-label">Teks sumber di atas soal (tidak wajib)</span>
    <div class="field">
      <label for="<?= $prefix ?>-source"><span class="lang-tag">ID</span> Indonesia</label>
      <textarea id="<?= $prefix ?>-source" name="source_text_id" rows="2"><?= esc($isNew ? '' : ($item->source_text_id ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-source-en"><span class="lang-tag">EN</span> Inggris</label>
      <textarea id="<?= $prefix ?>-source-en" name="source_text_en" rows="2"><?= esc($isNew ? '' : ($item->source_text_en ?? '')) ?></textarea>
    </div>
  </div>

  <?php foreach ($guides as $for => [$title, $text, $keyExample, $configExample]): ?>
    <div class="type-guide" data-for="<?= esc($for, 'attr') ?>">
      <b><?= icon('info') ?> <?= esc($title) ?></b>
      <p><?= esc($text) ?></p>
      <p class="json-example">Contoh isian kunci jawaban: <code><?= esc($keyExample) ?></code></p>
      <?php if ($configExample !== ''): ?>
        <p class="json-example">Contoh isian pengaturan soal: <code><?= esc($configExample) ?></code></p>
      <?php endif ?>
    </div>
  <?php endforeach ?>

  <div class="form-grid">
    <div class="field json-field">
      <label for="<?= $prefix ?>-answer">Kunci jawaban (format teknis)</label>
      <textarea id="<?= $prefix ?>-answer" name="answer_key_json" rows="4" spellcheck="false"><?= esc($isNew ? '' : $json($item->answerKey())) ?></textarea>
    </div>
    <div class="field json-field">
      <label for="<?= $prefix ?>-config">Pengaturan soal (format teknis)</label>
      <textarea id="<?= $prefix ?>-config" name="config_json" rows="4" spellcheck="false"><?= esc($isNew ? '' : $json($item->config_json ?: null)) ?></textarea>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-ref">Sumber rujukan</label>
      <input type="text" id="<?= $prefix ?>-ref" name="reference_source" value="<?= esc($isNew ? '' : ($item->reference_source ?? ''), 'attr') ?>" placeholder="Buku, situs resmi, atau sumber lain untuk fakta di soal ini">
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-note">Catatan pemeriksaan</label>
      <input type="text" id="<?= $prefix ?>-note" name="review_note" value="<?= esc($isNew ? '' : ($item->review_note ?? ''), 'attr') ?>" placeholder="Apa yang perlu dicek atau sudah dicek">
    </div>
  </div>

  <div class="check-row">
    <label class="check"><input type="checkbox" name="scorable" value="1" <?= $isNew || $item->scorable ? 'checked' : '' ?>> Dinilai (masuk skor)</label>
    <label class="check"><input type="checkbox" name="is_active" value="1" <?= $isNew || $item->is_active ? 'checked' : '' ?>> Aktif (dipakai di permainan)</label>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary btn-sm" type="submit"><?= icon($isNew ? 'check' : 'edit') ?> <?= $isNew ? 'Tambah soal' : 'Simpan soal' ?></button>
  </div>
</form>
