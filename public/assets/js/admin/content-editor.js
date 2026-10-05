/**
 * Editor konten — form butir yang menyesuaikan interaction_type.
 *
 * Editor terpandu ini hanya antarmuka: yang dikirim tetap dua kolom JSON yang
 * sama (answer_key_json, config_json), jadi server memvalidasi persis seperti
 * tanpa JavaScript. Kunci JSON yang tidak dikelola editor dipertahankan apa
 * adanya (sama dengan aturan form node di server). JSON mentah tetap dapat
 * dibuka lewat tombol "Lihat format teknis".
 *
 * Teks untuk guru memakai bahasa sehari-hari: "soal" (bukan butir), "benda"
 * (bukan objek), "format teknis" (bukan JSON).
 *
 *   fill_blank_bank  → kalimat dengan ___ + jawaban ID/EN
 *   verdict_card     → kunci Benar / Salah
 *   find_object      → pemilih koordinat (object-picker) + centang jebakan + umpan balik
 *   puzzle_arrange   → keterangan ukuran papan (ditetapkan server dari tingkat wilayah)
 *
 * Validasi client hanya umpan balik cepat; server selalu memeriksa ulang.
 */
import { $, $$, el, scrollToCenter } from '../core/dom.js';
import { createObjectPicker } from './object-picker.js';

const VERDICT_LABELS = { benar: 'Benar', salah: 'Salah' };
const verdictList = (verdicts) => verdicts.map((v) => VERDICT_LABELS[v] || v).join(' / ');

function parse(text) {
  const value = text.trim();
  if (value === '') return {};
  try {
    const parsed = JSON.parse(value);
    return parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : null;
  } catch {
    return null;
  }
}

const stringify = (obj) => (Object.keys(obj).length ? JSON.stringify(obj, null, 2) : '');

function field(label, control, help = null) {
  const id = control.id || `ce-${Math.random().toString(36).slice(2, 9)}`;
  control.id = id;
  return el('div', { class: 'field' }, el('label', { for: id }, label), control, help ? el('p', { class: 'field-help' }, help) : null);
}

const input = (value = '', attrs = {}) => el('input', { type: 'text', value: value ?? '', ...attrs });
const area = (value = '', rows = 2, attrs = {}) => {
  const node = el('textarea', { rows, ...attrs });
  node.value = value ?? '';
  return node;
};

class ItemEditor {
  constructor(form) {
    this.form = form;
    this.type = $('[name="interaction_type"]', form);
    this.answerTa = $('[name="answer_key_json"]', form);
    this.configTa = $('[name="config_json"]', form);
    this.verdicts = ['benar', 'salah'];
    this.scene = form.dataset.scene || '';
    if (!this.type || !this.answerTa || !this.configTa) return;

    form.classList.add('is-guided');
    this.errors = el('div', { class: 'alert alert-error editor-errors', role: 'alert', hidden: true });
    this.host = el('div', { class: 'guided-editor', 'aria-live': 'off' });
    this.preview = el('div', { class: 'item-preview', 'aria-label': 'Pratinjau untuk siswa' });
    const toggle = el('button', { type: 'button', class: 'btn btn-quiet btn-sm json-toggle', 'aria-expanded': 'false' }, 'Lihat format teknis');

    const jsonGrid = this.answerTa.closest('.form-grid');
    jsonGrid.before(this.host, this.preview, el('div', { class: 'json-toggle-row' }, toggle));
    form.prepend(this.errors);

    toggle.addEventListener('click', () => {
      const open = form.classList.toggle('show-json');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    this.type.addEventListener('change', () => this.render());
    // JSON diketik manual → editor terpandu mengikuti
    [this.answerTa, this.configTa].forEach((ta) => ta.addEventListener('change', () => this.render()));
    $$('[name="prompt_id"], [name="prompt_en"]', form).forEach((ta) => ta.addEventListener('input', () => this.renderPreview()));
    form.addEventListener('submit', (event) => this.validate(event));

    this.render();
  }

  get answer() { return parse(this.answerTa.value); }

  get config() { return parse(this.configTa.value); }

  /** Gabungkan kunci terkelola ke JSON yang ada; null/'' menghapus kunci. */
  write(answerPatch = {}, configPatch = {}) {
    const answer = this.answer ?? {};
    const config = this.config ?? {};
    for (const [key, value] of Object.entries(answerPatch)) {
      if (value === null || value === '' || (Array.isArray(value) && !value.length)) delete answer[key];
      else answer[key] = value;
    }
    for (const [key, value] of Object.entries(configPatch)) {
      if (value === null || value === '' || (Array.isArray(value) && !value.length)) delete config[key];
      else config[key] = value;
    }
    this.answerTa.value = stringify(answer);
    this.configTa.value = stringify(config);
    this.renderPreview();
  }

  render() {
    const answer = this.answer;
    const config = this.config;
    this.host.replaceChildren();

    if (answer === null || config === null) {
      this.host.append(el('p', { class: 'alert alert-warn' }, 'Isian format teknis tidak dapat dibaca. Perbaiki di kotak "Kunci jawaban (format teknis)" atau "Pengaturan soal (format teknis)" di bawah — formulir berpanduan muncul lagi setelah isiannya terbaca.'));
      this.form.classList.add('show-json');
      this.renderPreview();
      return;
    }

    const builders = {
      fill_blank_bank: () => this.fillBank(answer),
      verdict_card: () => this.verdict(answer),
      find_object: () => this.findObject(answer, config),
      puzzle_arrange: () => this.puzzle(config),
    };
    (builders[this.type.value] || (() => {}))();
    this.renderPreview();
  }

  // ---------------------------------------------------------- per jenis

  fillBank(answer) {
    const id = input(answer.text_id, { required: true });
    const en = input(answer.text_en);
    const sync = () => this.write({ text_id: id.value.trim(), text_en: en.value.trim() });
    [id, en].forEach((n) => n.addEventListener('input', sync));
    this.host.append(el('div', { class: 'bilingual' },
      field('Jawaban (Indonesia)', id, 'Kata ini ikut masuk pilihan kata bersama kata pengecoh variasi ini.'),
      field('Jawaban (Inggris)', en, 'Boleh kosong — permainan memakai jawaban Indonesia.')));
  }

  verdict(answer) {
    const name = `verdict-${Math.random().toString(36).slice(2, 8)}`;
    const group = el('fieldset', { class: 'field' }, el('legend', { class: 'label' }, 'Kunci jawaban'));
    const row = el('div', { class: 'check-row' });
    for (const verdict of this.verdicts) {
      const radio = el('input', { type: 'radio', name, value: verdict, checked: answer.verdict === verdict, 'data-verdict-key': '' });
      radio.addEventListener('change', () => this.write({ verdict }));
      row.append(el('label', { class: 'check' }, radio, ` ${VERDICT_LABELS[verdict] || verdict}`));
    }
    group.append(row, el('p', { class: 'field-help' }, `Tombol jawaban siswa: ${verdictList(this.verdicts)}.`));
    this.host.append(group);
  }

  findObject(answer, config) {
    const decoy = el('input', { type: 'checkbox', checked: Boolean(config.decoy) });
    const fbId = area(config.wrong_feedback_id, 2);
    const fbEn = area(config.wrong_feedback_en, 2);
    const feedback = el('div', { class: 'bilingual' },
      field('Penjelasan saat diklik (Indonesia)', fbId, 'Wajib untuk benda jebakan: jelaskan dari daerah mana benda ini berasal.'),
      field('Penjelasan saat diklik (Inggris)', fbEn));

    const picker = createObjectPicker({
      scene: this.scene,
      value: { x: config.x, y: config.y, w: config.w },
      onChange: ({ x, y, w }) => this.write({}, { x, y, w }),
    });

    const syncFlags = () => this.write(
      { target: !decoy.checked },
      { decoy: decoy.checked, wrong_feedback_id: fbId.value.trim(), wrong_feedback_en: fbEn.value.trim() },
    );
    decoy.addEventListener('change', syncFlags);
    [fbId, fbEn].forEach((n) => n.addEventListener('input', syncFlags));

    if (!this.scene) {
      this.host.append(el('p', { class: 'field-help' }, 'Gambar adegan tantangan ini belum diunggah — posisi benda tetap dapat diatur pada bidang kosong 16:9.'));
    }
    this.host.append(picker.element, el('label', { class: 'check' }, decoy, ' Benda jebakan (budaya daerah lain — tidak dinilai)'), feedback);
    if (answer.target === undefined) syncFlags();
  }

  /**
   * Ukuran papan dan bentuk keping tidak diisi admin: server menetapkannya dari
   * tingkat kesulitan wilayah saat soal dibuat (data-puzzle-grid/shape).
   */
  puzzle(config) {
    const n = Number(this.form.dataset.puzzleGrid) || Number(config.grid) || 3;
    const shape = this.form.dataset.puzzleShape || config.shape || 'square';
    const label = `${n} × ${n} keping ${shape === 'jigsaw' ? 'jigsaw (bertonjolan)' : 'kotak'}`;
    this.host.append(el('p', { class: 'field-help' }, el('b', {}, `Ukuran papan: ${label}. `),
      'Ukuran ini mengikuti tingkat kesulitan wilayah dan dibuat otomatis bersama kunci jawabannya. Gambarnya diambil dari gambar soal, atau dari gambar adegan tantangan bila soal tidak punya gambar.'));
    if (!Array.isArray(this.answer?.order)) {
      this.write({ order: Array.from({ length: n * n }, (_, i) => i) }, { grid: n, shape });
    }
  }

  // --------------------------------------------------------- pratinjau

  renderPreview() {
    const type = this.type.value;
    const prompt = $('[name="prompt_id"]', this.form)?.value.trim() || '';
    const answer = this.answer || {};
    const config = this.config || {};
    const body = [];

    if (prompt) {
      const text = el('p', { class: 'preview-prompt' });
      prompt.split('___').forEach((part, i, all) => {
        text.append(part);
        if (i < all.length - 1) text.append(el('span', { class: 'preview-blank' }, type === 'fill_blank_bank' && answer.text_id ? answer.text_id : '?'));
      });
      body.push(text);
    }

    if (type === 'verdict_card') {
      body.push(el('div', { class: 'preview-choices' }, this.verdicts.map((v) => el('span', { class: `chip${answer.verdict === v ? ' is-key' : ''}` }, VERDICT_LABELS[v] || v))));
    } else if (type === 'puzzle_arrange') {
      const n = Number(this.form.dataset.puzzleGrid) || Number(config.grid) || 3;
      const jigsaw = (this.form.dataset.puzzleShape || config.shape) === 'jigsaw';
      body.push(el('p', { class: 'muted' }, `Susun gambar ${n} × ${n} keping ${jigsaw ? 'jigsaw' : 'kotak'}, tanpa nomor. Keterangan di atas tampil setelah gambar utuh.`));
    } else if (type === 'find_object') {
      body.push(el('p', { class: 'muted' }, config.decoy ? 'Benda jebakan: tampil sama persis dengan benda lain, tanpa petunjuk.' : 'Petunjuk di atas dibacakan kepada siswa; bendanya dicari di gambar adegan.'));
    }

    this.preview.replaceChildren(el('span', { class: 'eyebrow' }, 'Pratinjau untuk siswa'), ...(body.length ? body : [el('p', { class: 'muted' }, 'Isi pertanyaan untuk melihat pratinjau.')]));
  }

  // ---------------------------------------------------------- validasi

  validate(event) {
    const type = this.type.value;
    const answer = this.answer;
    const config = this.config;
    const errors = [];
    const prompts = [$('[name="prompt_id"]', this.form), $('[name="prompt_en"]', this.form)];

    if (answer === null) errors.push('Isian "Kunci jawaban (format teknis)" tidak dapat dibaca.');
    if (config === null) errors.push('Isian "Pengaturan soal (format teknis)" tidak dapat dibaca.');

    if (answer && config) {
      if (type === 'fill_blank_bank') {
        if (!prompts[0]?.value.includes('___')) errors.push('Kalimat soal (Indonesia) harus memuat tanda ___ (tiga garis bawah) sebagai tempat kosong.');
        if (prompts[1]?.value.trim() && !prompts[1].value.includes('___')) errors.push('Kalimat soal (Inggris) harus memuat tanda ___ atau dikosongkan.');
      }
      if (type === 'fill_blank_bank' && !String(answer.text_id || '').trim()) errors.push('Jawaban (Indonesia) wajib diisi.');
      if (type === 'verdict_card' && !this.verdicts.includes(answer.verdict)) errors.push(`Pilih kunci jawaban: ${verdictList(this.verdicts)}.`);
      if (type === 'find_object') {
        if (![config.x, config.y, config.w].every((v) => Number.isFinite(Number(v)) && v !== '')) errors.push('Tempatkan benda pada gambar adegan (ketuk gambarnya atau geser penandanya).');
        if (config.decoy && !String(config.wrong_feedback_id || '').trim()) errors.push('Benda jebakan wajib punya penjelasan saat diklik (Indonesia).');
      }
      if (type === 'puzzle_arrange' && !Array.isArray(answer.order)) errors.push('Kunci susun gambar belum terbentuk. Muat ulang halaman, lalu coba lagi.');
    }

    if (!errors.length) {
      this.errors.hidden = true;
      return;
    }

    event.preventDefault();
    this.errors.replaceChildren(el('p', {}, el('b', {}, 'Soal belum dapat disimpan:')), el('ul', {}, errors.map((e) => el('li', {}, e))));
    this.errors.hidden = false;
    scrollToCenter(this.errors);
  }
}

/** Form node: tambah/hapus baris kata pengecoh per variasi (Isi Rumpang). */
function initNodeForm(root) {
  const form = $$('form.form-section', root).find((f) => $('[name="engine_type"]', f));
  if (!form) return;

  for (const fieldset of $$('fieldset.variant-row', form)) {
    const rows = $$('.bilingual', fieldset).filter((row) => $('input[name*="[distractors]"]', row));
    if (!rows.length) continue;

    const prefix = $('input[name*="[distractors]"]', rows[0]).name.replace(/\[distractors\].*$/, '[distractors]');
    let index = rows.length;

    const decorate = (row) => {
      const remove = el('button', { type: 'button', class: 'btn btn-quiet btn-sm', 'aria-label': 'Hapus kata pengecoh ini' }, '✕');
      remove.addEventListener('click', () => {
        if ($$('input[name*="[distractors]"]', fieldset).length <= 2) {
          $$('input', row).forEach((i) => { i.value = ''; });
        } else {
          row.remove();
        }
      });
      row.append(remove);
      row.classList.add('has-remove');
    };
    rows.forEach(decorate);

    const add = el('button', { type: 'button', class: 'btn btn-ghost btn-sm' }, '+ Tambah kata pengecoh');
    add.addEventListener('click', () => {
      const row = el('div', { class: 'bilingual' },
        el('input', { type: 'text', name: `${prefix}[${index}][id]`, placeholder: 'Pengecoh (Indonesia)', 'aria-label': `Pengecoh ${index + 1} (Indonesia)` }),
        el('input', { type: 'text', name: `${prefix}[${index}][en]`, placeholder: 'Pengecoh (Inggris)', 'aria-label': `Pengecoh ${index + 1} (Inggris)` }));
      index++;
      decorate(row);
      add.before(row);
      $('input', row).focus();
    });
    fieldset.append(add);
  }
}

export function initContentEditor(root = document) {
  for (const form of $$('form.item-form', root)) {
    if (form.dataset.guided) continue;
    form.dataset.guided = '1';
    new ItemEditor(form);
  }
  initNodeForm(root);
}

