<?php
/**
 * Form satu butir soal — dipakai content/node.php untuk setiap butir di bank
 * dan untuk "Tambah butir".
 *
 * Form berubah sesuai `interaction_type`: panduan bentuk answer_key_json dan
 * config_json untuk jenis yang dipilih ditampilkan lewat CSS :has() (tanpa
 * JavaScript). content-editor.js (tahap 6) menggantinya dengan editor terpandu
 * — editor opsi, kalimat ___, verdict, dua sumber, potongan urutan, dan
 * pemilih koordinat objek — yang tetap menulis ke dua kolom JSON yang sama.
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
    'single_choice source_trust' => ['Pilihan tunggal', 'Opsi A–D disimpan di editor opsi di bawah form ini (tepat satu benar). answer_key_json boleh berisi kunci opsi.', '{"option_key": "a"}', ''],
    'fill_blank_bank'            => ['Rumpang dengan bank kata', 'Tulis kalimat dengan penanda ___ di kolom pertanyaan. Kata jawaban ikut masuk bank kata bersama pengecoh node.', '{"text_id": "kopi", "text_en": "coffee"}', ''],
    'fill_blank_free'            => ['Rumpang isian bebas', 'Tulis kalimat dengan penanda ___. Semua jawaban yang diterima, per bahasa.', '{"accept_id": ["Magelang"], "accept_en": ["Magelang"], "case_sensitive": false}', ''],
    'verdict_card'               => ['Kartu pernyataan', 'Kunci verdict harus salah satu dari verdict_options node: ' . $verdicts . '. Dua sumber (opsional) ditulis di config_json.', '{"verdict": "benar"}', '{"sources": [{"label_id": "Sumber A", "label_en": "Source A", "kind": "official", "text_id": "…", "text_en": "…"}, {"label_id": "Sumber B", "label_en": "Source B", "kind": "anonymous", "text_id": "…", "text_en": "…"}]}'],
    'verdict_reason'             => ['Kartu pernyataan beralasan', 'Seperti kartu pernyataan; siswa juga menulis alasan. Alasan tidak dinilai otomatis — contoh alasan untuk rubrik guru.', '{"verdict": "pendapat", "reason_example_id": "…", "reason_example_en": "…"}', ''],
    'find_object'                => ['Cari objek', 'Posisi dalam persen terhadap gambar adegan. Objek jebakan (decoy: true) wajib punya wrong_feedback dan tidak dinilai.', '{"target": true}', '{"x": 18, "y": 62, "w": 13, "decoy": false, "wrong_feedback_id": "…", "wrong_feedback_en": "…"}'],
    'puzzle_arrange'             => ['Puzzle gambar', 'Pilih gambar lewat media butir; ukuran kisi di config.', '{"order": [0,1,2,3,4,5,6,7,8]}', '{"grid": 3}'],
    'ordering'                   => ['Urutkan potongan', 'Urutan di answer_key_json adalah kunci; server mengacak ulang saat dikirim ke siswa.', '{"order": ["c", "a", "d", "b"]}', '{"pieces": [{"key": "a", "text_id": "…", "text_en": "…"}]}'],
];
?>
<form method="post" action="<?= esc($action, 'attr') ?>" class="form item-form">
  <?= csrf_field() ?>

  <div class="form-grid">
    <?php if ($isNew): ?>
      <div class="field">
        <label for="<?= $prefix ?>-key">Kunci butir (item_key) <span class="req">*</span></label>
        <input type="text" id="<?= $prefix ?>-key" name="item_key" required maxlength="60" placeholder="tmg-1-99" spellcheck="false">
      </div>
    <?php endif ?>
    <div class="field">
      <label for="<?= $prefix ?>-type">Jenis interaksi <span class="req">*</span></label>
      <select id="<?= $prefix ?>-type" name="interaction_type" required>
        <?php foreach ($interactions as $option): ?>
          <option value="<?= esc($option, 'attr') ?>" <?= ! $isNew && $item->interaction_type === $option ? 'selected' : '' ?>><?= esc($option) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-seq">Urutan</label>
      <input type="number" id="<?= $prefix ?>-seq" name="sequence" min="0" value="<?= esc($isNew ? 0 : $item->sequence, 'attr') ?>">
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-review">Status tinjauan</label>
      <select id="<?= $prefix ?>-review" name="review_status">
        <?php foreach (['draft' => 'Draf', 'needs_verification' => 'Perlu verifikasi', 'verified' => 'Terverifikasi'] as $value => $label): ?>
          <option value="<?= esc($value, 'attr') ?>" <?= ($isNew ? 'draft' : $item->review_status) === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-passage">Teks bacaan</label>
      <select id="<?= $prefix ?>-passage" name="passage_id">
        <option value="">— tanpa bacaan —</option>
        <?php foreach ($passages as $passage): ?>
          <option value="<?= esc($passage->id, 'attr') ?>" <?= ! $isNew && (int) $item->passage_id === $passage->id ? 'selected' : '' ?>><?= esc($passage->passage_key) ?></option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-indicator">Indikator</label>
      <select id="<?= $prefix ?>-indicator" name="indicator_id">
        <option value="">— ikut indikator node —</option>
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
      <label for="<?= $prefix ?>-prompt-en"><span class="lang-tag">EN</span> English</label>
      <textarea id="<?= $prefix ?>-prompt-en" name="prompt_en" rows="2"><?= esc($isNew ? '' : ($item->prompt_en ?? '')) ?></textarea>
    </div>
    <p class="field-help">Rumpang: tandai bagian kosong dengan <code>___</code>. English boleh dikosongkan — permainan memakai teks Indonesia.</p>
  </div>

  <div class="bilingual">
    <span class="bilingual-label">Teks sumber (opsional)</span>
    <div class="field">
      <label for="<?= $prefix ?>-source"><span class="lang-tag">ID</span> Indonesia</label>
      <textarea id="<?= $prefix ?>-source" name="source_text_id" rows="2"><?= esc($isNew ? '' : ($item->source_text_id ?? '')) ?></textarea>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-source-en"><span class="lang-tag">EN</span> English</label>
      <textarea id="<?= $prefix ?>-source-en" name="source_text_en" rows="2"><?= esc($isNew ? '' : ($item->source_text_en ?? '')) ?></textarea>
    </div>
  </div>

  <?php foreach ($guides as $for => [$title, $text, $keyExample, $configExample]): ?>
    <div class="type-guide" data-for="<?= esc($for, 'attr') ?>">
      <b><?= icon('info') ?> <?= esc($title) ?></b>
      <p><?= esc($text) ?></p>
      <p>answer_key_json: <code><?= esc($keyExample) ?></code></p>
      <?php if ($configExample !== ''): ?>
        <p>config_json: <code><?= esc($configExample) ?></code></p>
      <?php endif ?>
    </div>
  <?php endforeach ?>

  <div class="form-grid">
    <div class="field json-field">
      <label for="<?= $prefix ?>-answer">answer_key_json</label>
      <textarea id="<?= $prefix ?>-answer" name="answer_key_json" rows="4" spellcheck="false"><?= esc($isNew ? '' : $json($item->answerKey())) ?></textarea>
    </div>
    <div class="field json-field">
      <label for="<?= $prefix ?>-config">config_json</label>
      <textarea id="<?= $prefix ?>-config" name="config_json" rows="4" spellcheck="false"><?= esc($isNew ? '' : $json($item->config_json ?: null)) ?></textarea>
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-ref">Sumber rujukan</label>
      <input type="text" id="<?= $prefix ?>-ref" name="reference_source" value="<?= esc($isNew ? '' : ($item->reference_source ?? ''), 'attr') ?>">
    </div>
    <div class="field">
      <label for="<?= $prefix ?>-note">Catatan tinjauan</label>
      <input type="text" id="<?= $prefix ?>-note" name="review_note" value="<?= esc($isNew ? '' : ($item->review_note ?? ''), 'attr') ?>">
    </div>
  </div>

  <div class="check-row">
    <label class="check"><input type="checkbox" name="scorable" value="1" <?= $isNew || $item->scorable ? 'checked' : '' ?>> Dinilai</label>
    <label class="check"><input type="checkbox" name="is_active" value="1" <?= $isNew || $item->is_active ? 'checked' : '' ?>> Aktif</label>
  </div>

  <div class="form-actions">
    <button class="btn btn-primary btn-sm" type="submit"><?= icon($isNew ? 'check' : 'edit') ?> <?= $isNew ? 'Tambah butir' : 'Simpan butir' ?></button>
  </div>
</form>
