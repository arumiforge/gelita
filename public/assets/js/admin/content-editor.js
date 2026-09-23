/**
 * Editor konten — form butir yang menyesuaikan interaction_type.
 *
 * Editor terpandu ini hanya antarmuka: yang dikirim tetap dua kolom JSON yang
 * sama (answer_key_json, config_json), jadi server memvalidasi persis seperti
 * tanpa JavaScript. Kunci JSON yang tidak dikelola editor dipertahankan apa
 * adanya (sama dengan aturan form node di server). JSON mentah tetap dapat
 * dibuka lewat tombol "JSON mentah".
 *
 *   single_choice / source_trust → opsi A–D di editor opsi (tepat satu kunci)
 *   fill_blank_bank  → kalimat dengan ___ + jawaban ID/EN
 *   fill_blank_free  → kalimat + daftar jawaban diterima (satu per baris)
 *   verdict_card     → kunci verdict (hanya dari verdict_options node) + dua sumber opsional
 *   verdict_reason   → sama + contoh alasan ID/EN (rubrik guru)
 *   find_object      → pemilih koordinat (object-picker) + centang jebakan + umpan balik
 *   puzzle_arrange   → ukuran kisi
 *   ordering         → potongan teks ID/EN; urutan di editor = kunci jawaban
 *
 * Validasi client hanya umpan balik cepat; server selalu memeriksa ulang.
 */
import { $, $$, el, scrollToCenter } from '../core/dom.js';
import { createObjectPicker } from './object-picker.js';

const VERDICT_LABELS = { benar: 'Benar', salah: 'Salah', pendapat: 'Pendapat' };
const SOURCE_KINDS = { official: 'Resmi (ikon gedung)', anonymous: 'Tanpa nama (ikon tanda tanya)', unknown: 'Lainnya' };

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
    this.verdicts = readJsonAttr(form.dataset.verdicts) || ['benar', 'salah'];
    this.scene = form.dataset.scene || '';
    if (!this.type || !this.answerTa || !this.configTa) return;

    form.classList.add('is-guided');
    this.errors = el('div', { class: 'alert alert-error editor-errors', role: 'alert', hidden: true });
    this.host = el('div', { class: 'guided-editor', 'aria-live': 'off' });
    this.preview = el('div', { class: 'item-preview', 'aria-label': 'Pratinjau untuk siswa' });
    const toggle = el('button', { type: 'button', class: 'btn btn-quiet btn-sm json-toggle', 'aria-expanded': 'false' }, 'JSON mentah');

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
      this.host.append(el('p', { class: 'alert alert-warn' }, 'JSON mentah tidak valid. Perbaiki di kolom JSON — editor terpandu aktif lagi setelah JSON terbaca.'));
      this.form.classList.add('show-json');
      this.renderPreview();
      return;
    }

    const builders = {
      single_choice: () => this.choice(),
      source_trust: () => this.choice(),
      fill_blank_bank: () => this.fillBank(answer),
      fill_blank_free: () => this.fillFree(answer),
      verdict_card: () => this.verdict(answer, config, false),
      verdict_reason: () => this.verdict(answer, config, true),
      find_object: () => this.findObject(answer, config),
      puzzle_arrange: () => this.puzzle(config),
      ordering: () => this.ordering(answer, config),
    };
    (builders[this.type.value] || (() => {}))();
    this.renderPreview();
  }

  // ---------------------------------------------------------- per jenis

  choice() {
    const saved = this.form.closest('.item-card-body')?.querySelector('form.option-editor');
    this.host.append(el('p', { class: 'field-help' }, saved
      ? 'Opsi A–D dan kuncinya diatur di "Opsi jawaban" di bawah form ini — tepat satu opsi benar.'
      : 'Simpan butir ini dulu; editor opsi A–D muncul setelah butir tersimpan.'));
  }

  fillBank(answer) {
    const id = input(answer.text_id, { required: true });
    const en = input(answer.text_en);
    const sync = () => this.write({ text_id: id.value.trim(), text_en: en.value.trim() });
    [id, en].forEach((n) => n.addEventListener('input', sync));
    this.host.append(el('div', { class: 'bilingual' },
      field('Jawaban (ID)', id, 'Kata ini ikut masuk bank kata bersama pengecoh node.'),
      field('Jawaban (EN)', en, 'Boleh kosong — permainan memakai jawaban Indonesia.')));
  }

  fillFree(answer) {
    const lines = (list) => (Array.isArray(list) ? list.join('\n') : '');
    const id = area(lines(answer.accept_id), 3);
    const en = area(lines(answer.accept_en), 3);
    const caseSensitive = el('input', { type: 'checkbox', checked: Boolean(answer.case_sensitive) });
    const split = (text) => text.split('\n').map((s) => s.trim()).filter(Boolean);
    const sync = () => this.write({ accept_id: split(id.value), accept_en: split(en.value), case_sensitive: caseSensitive.checked ? true : null });
    [id, en].forEach((n) => n.addEventListener('input', sync));
    caseSensitive.addEventListener('change', sync);
    this.host.append(
      el('div', { class: 'bilingual' },
        field('Jawaban diterima (ID) — satu per baris', id),
        field('Jawaban diterima (EN) — satu per baris', en)),
      el('label', { class: 'check' }, caseSensitive, ' Bedakan huruf besar/kecil'),
    );
  }

  verdict(answer, config, withReason) {
    const name = `verdict-${Math.random().toString(36).slice(2, 8)}`;
    const group = el('fieldset', { class: 'field' }, el('legend', { class: 'label' }, 'Kunci penilaian'));
    const row = el('div', { class: 'check-row' });
    for (const verdict of this.verdicts) {
      const radio = el('input', { type: 'radio', name, value: verdict, checked: answer.verdict === verdict, 'data-verdict-key': '' });
      radio.addEventListener('change', () => this.write({ verdict }));
      row.append(el('label', { class: 'check' }, radio, ` ${VERDICT_LABELS[verdict] || verdict}`));
    }
    group.append(row, el('p', { class: 'field-help' }, `Hanya pilihan dari verdict_options node: ${this.verdicts.join(' / ')}.`));
    this.host.append(group);

    if (withReason) {
      const id = area(answer.sample_reason_id, 2);
      const en = area(answer.sample_reason_en, 2);
      const sync = () => this.write({ sample_reason_id: id.value.trim(), sample_reason_en: en.value.trim() });
      [id, en].forEach((n) => n.addEventListener('input', sync));
      this.host.append(el('div', { class: 'bilingual' },
        field('Contoh alasan (ID) — untuk rubrik guru', id),
        field('Contoh alasan (EN)', en)));
    }

    // Dua sumber berdampingan (opsional)
    const sources = Array.isArray(config.sources) ? config.sources : [];
    const enable = el('input', { type: 'checkbox', checked: sources.length > 0 });
    const box = el('div', { class: 'source-editor', hidden: sources.length === 0 });
    const rows = [0, 1].map((i) => {
      const src = sources[i] || { kind: i === 0 ? 'official' : 'anonymous' };
      const kind = el('select', {}, Object.entries(SOURCE_KINDS).map(([value, label]) => el('option', { value, selected: src.kind === value }, label)));
      const parts = {
        label_id: input(src.label_id), label_en: input(src.label_en), kind,
        text_id: area(src.text_id, 2), text_en: area(src.text_en, 2),
      };
      box.append(el('fieldset', { class: 'repeat-row' }, el('legend', {}, `Sumber ${i === 0 ? 'A' : 'B'}`),
        el('div', { class: 'bilingual' }, field('Label (ID)', parts.label_id), field('Label (EN)', parts.label_en)),
        field('Jenis sumber', kind),
        el('div', { class: 'bilingual' }, field('Isi (ID)', parts.text_id), field('Isi (EN)', parts.text_en))));
      return parts;
    });
    const sync = () => {
      if (!enable.checked) {
        this.write({}, { sources: null });
        return;
      }
      this.write({}, {
        sources: rows.map((p) => ({
          label_id: p.label_id.value.trim(), label_en: p.label_en.value.trim(), kind: p.kind.value,
          text_id: p.text_id.value.trim(), text_en: p.text_en.value.trim(),
        })),
      });
    };
    enable.addEventListener('change', () => {
      box.hidden = !enable.checked;
      sync();
    });
    box.addEventListener('input', sync);
    box.addEventListener('change', sync);
    this.host.append(el('label', { class: 'check' }, enable, ' Tampilkan dua sumber berdampingan'), box);
  }

  findObject(answer, config) {
    const decoy = el('input', { type: 'checkbox', checked: Boolean(config.decoy) });
    const fbId = area(config.wrong_feedback_id, 2);
    const fbEn = area(config.wrong_feedback_en, 2);
    const feedback = el('div', { class: 'bilingual' },
      field('Umpan balik saat diklik (ID)', fbId, 'Wajib untuk objek jebakan: jelaskan dari daerah mana benda ini berasal.'),
      field('Umpan balik saat diklik (EN)', fbEn));

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
      this.host.append(el('p', { class: 'field-help' }, 'Gambar adegan node belum diunggah — posisi tetap dapat diatur pada kanvas kosong 16:9.'));
    }
    this.host.append(picker.element, el('label', { class: 'check' }, decoy, ' Objek jebakan (budaya daerah lain — tidak dinilai)'), feedback);
    if (answer.target === undefined) syncFlags();
  }

  puzzle(config) {
    const grid = el('input', { type: 'number', min: '2', max: '5', value: String(config.grid || 3) });
    const sync = () => {
      const n = Math.min(5, Math.max(2, Number(grid.value) || 3));
      this.write({ order: Array.from({ length: n * n }, (_, i) => i) }, { grid: n });
    };
    grid.addEventListener('input', sync);
    this.host.append(field('Ukuran kisi (2–5)', grid, 'Kunci jawaban otomatis: keping 1 sampai n² dari kiri atas. Gambar puzzle diambil dari media butir atau gambar adegan node.'));
    if (!Array.isArray(this.answer?.order)) sync();
  }

  ordering(answer, config) {
    const pieces = Array.isArray(config.pieces) ? config.pieces.slice() : [];
    const order = Array.isArray(answer.order) ? answer.order.map(String) : [];
    pieces.sort((a, b) => {
      const ia = order.indexOf(String(a.key));
      const ib = order.indexOf(String(b.key));
      return (ia < 0 ? 999 : ia) - (ib < 0 ? 999 : ib);
    });

    const list = el('ol', { class: 'piece-editor' });
    const nextKey = () => {
      const used = new Set($$('li', list).map((li) => li.dataset.key));
      for (const ch of 'abcdefghijklmnopqrstuvwxyz') if (!used.has(ch)) return ch;
      return `p${used.size + 1}`;
    };

    const sync = () => {
      const rows = $$('li', list).map((li) => ({
        key: li.dataset.key,
        text_id: $('[data-part="id"]', li).value.trim(),
        text_en: $('[data-part="en"]', li).value.trim(),
      }));
      this.write({ order: rows.map((r) => r.key) }, { pieces: rows });
    };

    const addRow = (piece = {}) => {
      const key = String(piece.key ?? nextKey());
      const id = input(piece.text_id, { 'data-part': 'id', 'aria-label': `Potongan ${key} (ID)`, placeholder: 'Teks Indonesia' });
      const en = input(piece.text_en, { 'data-part': 'en', 'aria-label': `Potongan ${key} (EN)`, placeholder: 'English' });
      const up = el('button', { type: 'button', class: 'btn btn-quiet btn-sm', 'aria-label': `Naikkan potongan ${key}` }, '▲');
      const down = el('button', { type: 'button', class: 'btn btn-quiet btn-sm', 'aria-label': `Turunkan potongan ${key}` }, '▼');
      const remove = el('button', { type: 'button', class: 'btn btn-quiet btn-sm', 'aria-label': `Hapus potongan ${key}` }, '✕');
      const li = el('li', { dataset: { key } }, el('code', {}, key), id, en, el('span', { class: 'piece-moves' }, up, down, remove));
      up.addEventListener('click', () => { li.previousElementSibling?.before(li); sync(); up.focus(); });
      down.addEventListener('click', () => { li.nextElementSibling?.after(li); sync(); down.focus(); });
      remove.addEventListener('click', () => { li.remove(); sync(); });
      [id, en].forEach((n) => n.addEventListener('input', sync));
      list.append(li);
      return li;
    };

    pieces.forEach((piece) => addRow(piece));
    const add = el('button', { type: 'button', class: 'btn btn-ghost btn-sm' }, '+ Tambah potongan');
    add.addEventListener('click', () => {
      const li = addRow();
      sync();
      $('input', li).focus();
    });

    this.host.append(
      el('p', { class: 'label' }, 'Potongan urutan — susun dari langkah pertama sampai terakhir (urutan di sini = kunci jawaban; server mengacaknya untuk siswa)'),
      list,
      add,
    );
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

    if (type.startsWith('verdict')) {
      if (Array.isArray(config.sources) && config.sources.length) {
        body.push(el('div', { class: 'preview-sources' }, config.sources.map((s) => el('blockquote', {}, el('cite', {}, s.label_id || s.kind), s.text_id || ''))));
      }
      body.push(el('div', { class: 'preview-choices' }, this.verdicts.map((v) => el('span', { class: `chip${answer.verdict === v ? ' is-key' : ''}` }, VERDICT_LABELS[v] || v))));
      if (type === 'verdict_reason') body.push(el('p', { class: 'muted' }, '+ kolom "Alasanmu" (tidak dinilai otomatis)'));
    } else if (type === 'ordering' && Array.isArray(config.pieces)) {
      body.push(el('ol', { class: 'preview-pieces' }, config.pieces.map((p) => el('li', {}, p.text_id || '—'))));
      body.push(el('p', { class: 'muted' }, 'Siswa melihat potongan ini dalam urutan acak.'));
    } else if (type === 'puzzle_arrange') {
      body.push(el('p', { class: 'muted' }, `Puzzle ${config.grid || 3}×${config.grid || 3} keping.`));
    } else if (type === 'find_object') {
      body.push(el('p', { class: 'muted' }, config.decoy ? 'Objek jebakan: tampil sama persis dengan objek lain, tanpa petunjuk.' : 'Petunjuk di atas dibacakan kepada siswa; objeknya dicari di adegan.'));
    } else if (type === 'fill_blank_free') {
      body.push(el('p', { class: 'muted' }, 'Siswa mengetik jawabannya di kotak rumpang.'));
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

    if (answer === null) errors.push('answer_key_json bukan JSON yang valid.');
    if (config === null) errors.push('config_json bukan JSON yang valid.');

    if (answer && config) {
      if (type.startsWith('fill_blank')) {
        if (!prompts[0]?.value.includes('___')) errors.push('Kalimat rumpang (ID) harus memuat penanda ___.');
        if (prompts[1]?.value.trim() && !prompts[1].value.includes('___')) errors.push('Kalimat rumpang (EN) harus memuat penanda ___ atau dikosongkan.');
      }
      if (type === 'fill_blank_bank' && !String(answer.text_id || '').trim()) errors.push('Jawaban (ID) wajib diisi.');
      if (type === 'fill_blank_free' && !(Array.isArray(answer.accept_id) && answer.accept_id.length)) errors.push('Isi minimal satu jawaban yang diterima (ID).');
      if (type.startsWith('verdict') && !this.verdicts.includes(answer.verdict)) errors.push(`Kunci verdict harus salah satu dari: ${this.verdicts.join(' / ')}.`);
      if (type === 'find_object') {
        if (![config.x, config.y, config.w].every((v) => Number.isFinite(Number(v)) && v !== '')) errors.push('Tempatkan objek pada gambar adegan (koordinat x, y, dan lebar).');
        if (config.decoy && !String(config.wrong_feedback_id || '').trim()) errors.push('Objek jebakan wajib punya umpan balik (ID).');
      }
      if (type === 'ordering') {
        const pieces = Array.isArray(config.pieces) ? config.pieces : [];
        if (pieces.length < 2) errors.push('Urutan butuh minimal dua potongan.');
        if (pieces.some((p) => !String(p.text_id || '').trim())) errors.push('Setiap potongan wajib punya teks Indonesia.');
      }
      if (type === 'puzzle_arrange' && !(Number(config.grid) >= 2 && Number(config.grid) <= 5)) errors.push('Ukuran kisi puzzle harus 2–5.');
    }

    if (!errors.length) {
      this.errors.hidden = true;
      return;
    }

    event.preventDefault();
    this.errors.replaceChildren(el('p', {}, el('b', {}, 'Butir belum dapat disimpan:')), el('ul', {}, errors.map((e) => el('li', {}, e))));
    this.errors.hidden = false;
    scrollToCenter(this.errors);
  }
}

function readJsonAttr(value) {
  if (!value) return null;
  try {
    const parsed = JSON.parse(value);
    return Array.isArray(parsed) && parsed.length ? parsed : null;
  } catch {
    return null;
  }
}

/** Editor opsi A–D: tepat satu opsi benar, dan opsi itu harus berlabel. */
function initOptionEditors(root) {
  for (const form of $$('form.option-editor', root)) {
    form.addEventListener('submit', (event) => {
      const checked = $('input[name="correct_option"]:checked', form);
      let message = null;
      if (!checked) message = 'Pilih tepat satu opsi sebagai jawaban benar.';
      else {
        const label = $('input[name$="[label_id]"]', checked.closest('.option-row'));
        if (label && !label.value.trim()) message = 'Opsi yang ditandai benar belum punya label Indonesia.';
      }
      $('.editor-errors', form)?.remove();
      if (!message) return;
      event.preventDefault();
      const box = el('p', { class: 'alert alert-error editor-errors', role: 'alert' }, message);
      form.prepend(box);
      scrollToCenter(box);
    });
  }
}

/** Form node: pengecoh rumpang (tambah/hapus baris) + minimal dua verdict_options. */
function initNodeForm(root) {
  const form = $$('form.form-section', root).find((f) => $('[name="engine_type"]', f));
  if (!form) return;

  const rows = $$('.bilingual', form).filter((row) => $('input[name^="cfg[distractors]"]', row));
  if (rows.length) {
    const container = rows[0].parentElement;
    let index = rows.length;

    const decorate = (row) => {
      const remove = el('button', { type: 'button', class: 'btn btn-quiet btn-sm', 'aria-label': 'Hapus pengecoh ini' }, '✕');
      remove.addEventListener('click', () => {
        if ($$('input[name^="cfg[distractors]"]', container).length <= 2) {
          $$('input', row).forEach((i) => { i.value = ''; });
        } else {
          row.remove();
        }
      });
      row.append(remove);
      row.classList.add('has-remove');
    };
    rows.forEach(decorate);

    const add = el('button', { type: 'button', class: 'btn btn-ghost btn-sm' }, '+ Tambah pengecoh');
    add.addEventListener('click', () => {
      const row = el('div', { class: 'bilingual' },
        el('input', { type: 'text', name: `cfg[distractors][${index}][id]`, placeholder: 'ID', 'aria-label': `Pengecoh ${index + 1} (Indonesia)` }),
        el('input', { type: 'text', name: `cfg[distractors][${index}][en]`, placeholder: 'EN', 'aria-label': `Pengecoh ${index + 1} (English)` }));
      index++;
      decorate(row);
      add.before(row);
      $('input', row).focus();
    });
    container.append(add);
  }

  form.addEventListener('submit', (event) => {
    const verdicts = $$('input[name="cfg[verdict_options][]"]', form);
    if (!verdicts.length || verdicts.filter((v) => v.checked).length >= 2) return;
    event.preventDefault();
    const box = verdicts[0].closest('fieldset');
    box.classList.add('has-error');
    $('.editor-errors', box)?.remove();
    box.append(el('p', { class: 'field-error editor-errors', role: 'alert' }, 'Pilih minimal dua pilihan penilaian.'));
    scrollToCenter(box);
  });
}

export function initContentEditor(root = document) {
  for (const form of $$('form.item-form', root)) {
    if (form.dataset.guided) continue;
    form.dataset.guided = '1';
    new ItemEditor(form);
  }
  initOptionEditors(root);
  initNodeForm(root);
}

