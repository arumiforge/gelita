#!/usr/bin/env node
/**
 * Builds the blank field sheets L0–L8 as one print-ready A4 PDF:
 * ../lembar-lapangan-kosong.pdf (offered on the staff guide, /admin/panduan-staf).
 *
 *   node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs
 *   node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs --kolom=kolom.json   # a column spec written earlier
 *   node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs --html=check.html    # also keep the page, for checking
 *   node docs/revisi-10-analitik/dokumen-pdf/src/lembar-kosong.mjs --allow-dirty        # uncommitted 02 or spec (check only)
 *
 * Two sources, nothing typed by hand here:
 * - Columns, sheet order and layout come from App\Libraries\FieldSheetSpec,
 *   the one source of the typed template and the upload, through
 *   `php spark gelita:lembar:templat --kolom=FILE` (run with
 *   CI_ENVIRONMENT=development, since the command refuses production). The
 *   printed columns are the spec's `paper` headings: 02 keeps combined
 *   headings on paper (Headset, Pakai bareng, …) that the template splits.
 * - Everything else is read from 02_LEMBAR_LAPANGAN.md: who fills a sheet
 *   and when (Daftar lembar), portrait or landscape and the empty rows per
 *   page (Cara mencetak dan mengisi), the page heads written there, the hint
 *   column of the two field sheets (L1 bagian atas, L8), the code lists, and
 *   the L0 checklist. A part of 02 that is no longer where this script looks
 *   stops the build, so the sheets never drift from 02 quietly.
 *
 * Every sheet is one page (L0 runs over two): a head to fill in by hand
 * (kode sesi lapangan as sekolah–rombel–pertemuan, tanggal, pengisi,
 * Halaman __ dari __; L6 and L7 sekolah and rombel instead), the code list
 * where 02 has one, and empty rows stretched to fill the page. The foot names
 * the spec version and the commit of 02 and FieldSheetSpec, like cetak.mjs;
 * uncommitted changes are refused unless --allow-dirty.
 *
 * Needs PHP with the repository's vendor/ (for spark) and the pre-installed
 * Playwright Chromium; never run `playwright install`.
 */
import { createRequire } from 'node:module';
import { execFileSync } from 'node:child_process';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.resolve(here, '..', '..', '..', '..');
const target = path.resolve(here, '..', 'lembar-lapangan-kosong.pdf');
const SPEC_FILE = 'app/Libraries/FieldSheetSpec.php';

const args = process.argv.slice(2);
const option = (name) => args.find((a) => a.startsWith(`--${name}=`))?.slice(name.length + 3);
const allowDirty = args.includes('--allow-dirty');

/** Rows per page for the two tables 02 gives no count for (this script's choice). */
const DEFAULT_ROWS = { 'L4-pelatihan': 10, 'L6-sesi': 8 };
/** 02 counts "L4 30" and "L6 30": one observation round, and one class of students. */
const ROWS_FOR = { L4: 'L4-bromp', L6: 'L6-tes' };
/** A writing row is never lower than this, in mm (refused below it). */
const ROW_MIN_MM = 4.8;

const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
  'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const idDate = (iso) => {
  const [y, m, d] = iso.split('-').map(Number);
  return `${d} ${MONTHS[m - 1]} ${y}`;
};
const fail = (message) => { throw new Error(`lembar-kosong: ${message}`); };
const git = (...a) => execFileSync('git', ['-c', 'core.abbrev=7', ...a], { cwd: root, encoding: 'utf8' }).trim();
const esc = (s) => String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const unlink = (md) => md.replace(/\[([^\]]+)\]\([^)]*\)/g, '$1');
const plain = (md) => unlink(md).replace(/\*\*|`/g, '').replace(/(^|[\s(])\*([^*]+)\*/g, '$1$2').trim();
/** 02 Markdown to HTML: links become their text (paper cannot follow them). */
const inline = (md) => esc(unlink(md))
  .replace(/\*\*(.+?)\*\*/g, '<b>$1</b>')
  .replace(/(^|[\s(])\*([^*\s][^*]*?)\*(?=[\s).,;:]|$)/g, '$1<i>$2</i>')
  .replace(/`([^`]+)`/g, '<span class="mono">$1</span>');
const cells = (line) => line.trim().replace(/^\|/, '').replace(/\|$/, '').split('|').map((c) => c.trim());
const mm = (n) => `${n}mm`;

// ------------------------------------------------------------------ sources

const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'gelita-lembar-'));
process.on('exit', () => fs.rmSync(tmp, { recursive: true, force: true }));

function loadSpec() {
  let file = option('kolom');
  if (!file) {
    file = path.join(tmp, 'kolom.json');
    execFileSync('php', ['spark', 'gelita:lembar:templat', `--kolom=${file}`], {
      cwd: root,
      env: { ...process.env, CI_ENVIRONMENT: 'development' },
      stdio: ['ignore', 'ignore', 'inherit'],
    });
  }
  return JSON.parse(fs.readFileSync(path.resolve(file), 'utf8'));
}

const spec = loadSpec();
const docText = fs.readFileSync(path.join(root, spec.source), 'utf8');

const sources = [spec.source, SPEC_FILE];
const dirty = git('status', '--porcelain', '--', ...sources) !== '';
if (dirty && !allowDirty) {
  console.error(`Uncommitted changes in ${sources.join(' or ')}. Commit them first, or pass --allow-dirty (check only, do not commit the PDF).`);
  process.exit(1);
}
const today = new Date().toISOString().slice(0, 10);
const [commit, date] = dirty ? ['belum di-commit', today] : git('log', '-1', '--format=%h %cs', '--', ...sources).split(' ');

/** 02 split at its `## ` headings: sheet sections by id ('L0': {id, title, text}), the others by heading. */
const sections = {};
const named = {};
for (const part of docText.split(/^## /m).slice(1)) {
  const [heading, ...rest] = part.split('\n');
  const m = /^(L\d) (.+)$/.exec(heading.trim());
  if (m) sections[m[1]] = { id: m[1], title: m[2].trim(), text: rest.join('\n') };
  else named[heading.trim()] = rest.join('\n');
}
const namedSection = (heading) => named[heading] ?? fail(`02 has no section "## ${heading}"`);

/** The Markdown table whose first row is lines[start]: head cells and body rows. */
function tableAt(lines, start) {
  const rows = [];
  for (let i = start; i < lines.length && lines[i].trim().startsWith('|'); i++) rows.push(cells(lines[i]));
  if (rows.length < 2) fail(`no table at line ${start + 1} of a section`);
  return { head: rows[0], body: rows.slice(2) };
}

// Daftar lembar: who fills in a sheet, when, and per what
const sheetInfo = {};
for (const row of tableAt(namedSection('Daftar lembar').split('\n'), namedSection('Daftar lembar').split('\n').findIndex((l) => l.startsWith('| Lembar'))).body) {
  const id = /^L\d/.exec(row[0])?.[0] ?? fail(`unexpected row in Daftar lembar: ${row[0]}`);
  sheetInfo[id] = { when: plain(row[2]), by: plain(row[3]), per: plain(row[4]) };
}

// Cara mencetak dan mengisi: orientation and empty rows per page
const howTo = namedSection('Cara mencetak dan mengisi');
const orient = /\*\*Kertas A4\.\*\*\s*(.+?) dicetak tegak; (.+?) mendatar/.exec(howTo) ?? fail('orientation line not found in Cara mencetak dan mengisi');
const portrait = orient[1].match(/L\d/g);
const landscape = orient[2].match(/L\d/g);
const rowsLine = /\*\*Jumlah baris kosong per halaman:\*\*\s*(.+)/.exec(howTo) ?? fail('rows line not found in Cara mencetak dan mengisi');
const rowCount = { ...DEFAULT_ROWS };
for (const m of rowsLine[1].matchAll(/(L\d)(?: ([a-z]+))? (\d+)/g)) {
  const marker = m[2] ? `${m[1]}-${m[2]}` : ROWS_FOR[m[1]]
    ?? Object.values(spec.sheets).find((s) => s.marker.startsWith(`${m[1]}-`) && s.layout === 'columns')?.marker;
  if (!marker || !Object.values(spec.sheets).some((s) => s.marker === marker)) fail(`rows for "${m[0]}" match no sheet`);
  rowCount[marker] = Number(m[3]);
}
const howToBullet = (prefix) => {
  const line = howTo.split('\n').find((l) => l.startsWith(`- **${prefix}`)) ?? fail(`bullet "${prefix}" not found in Cara mencetak dan mengisi`);
  return line.slice(2);
};

// ------------------------------------------------------------------ heads

/** One `Label: ____ · Label: a / b · Halaman __ dari __` line of 02 as fields. */
function headFields(line) {
  return plain(line).split(' · ').map((raw) => {
    const part = raw.replace(/\s*\([^)]*\)/g, '').trim();
    if (/^Halaman\b/.test(part)) return { kind: 'page' };
    const m = /^(.+?):\s*(.*)$/.exec(part) ?? fail(`head field "${part}" has no label`);
    const [, label, value] = m;
    if (/^_+$/.test(value)) {
      return label === 'Kode sesi lapangan' ? { kind: 'session' } : { kind: 'line', label, size: Math.max(14, value.length * 2.6) };
    }
    if (value.includes(' / ')) return { kind: 'choice', label, options: value.split(' / ') };
    return fail(`head field "${part}" is neither a blank nor a choice`);
  });
}

const headLineOf = (section) => section.text.split('\n').find((l) => /_{3,}/.test(l) && l.includes(' · ') && /^[A-Z*]/.test(l));
const initials = (by) => /P\d/.exec(by)?.[0] ?? 'pengisi';

/** Head fields of one sheet: its 02 page head, else the rule of 02 → Kepala setiap halaman. */
function headFor(sheet, section) {
  const by = initials(sheetInfo[section.id]?.by ?? '');
  const school = [{ kind: 'line', label: 'Sekolah', size: 26 }, { kind: 'line', label: 'Rombel', size: 12 },
    { kind: 'line', label: 'Tanggal', size: 30 }, { kind: 'line', label: `Inisial ${by}`, size: 16 }];
  let fields;
  if (sheet.marker === 'L4-pelatihan') fields = [];               // once per observer, outside a session
  else if (section.id === 'L6') fields = school;                   // 02: "L7 dan L6: sekolah dan rombel"
  else if (headLineOf(section)) fields = headFields(headLineOf(section));
  else fields = [{ kind: 'session' }, { kind: 'line', label: 'Tanggal', size: 30 }, { kind: 'line', label: `Inisial ${by}`, size: 16 }];
  return fields.some((f) => f.kind === 'page') ? fields : [...fields, { kind: 'page' }];
}

function fieldHtml(f) {
  switch (f.kind) {
    case 'page':
      return '<span class="f"><span class="f-label">Halaman</span><span class="f-line" style="width:9mm"></span><span>dari</span><span class="f-line" style="width:9mm"></span></span>';
    case 'session':
      return `<span class="f"><span class="f-label">Kode sesi lapangan</span><span class="f-seg">
        <span class="f-box"><b style="width:13mm"></b><small>sekolah</small></span><i>–</i>
        <span class="f-box"><b style="width:7mm"></b><small>rombel</small></span><i>–</i>
        <span class="f-box"><b style="width:15mm"></b><small>pertemuan</small></span></span></span>`;
    case 'choice':
      return `<span class="f f-choice"><span class="f-label">${esc(f.label)}</span>${f.options.map((o) => `<span class="opt">${esc(o)}</span>`).join('')}<em>(lingkari)</em></span>`;
    default:
      return `<span class="f"><span class="f-label">${esc(f.label)}</span><span class="f-line" style="width:${mm(f.size)}"></span></span>`;
  }
}

// ------------------------------------------------------------------ legends

/** Code lists and short rules 02 prints with a sheet, as HTML paragraphs. */
function legendFor(sheet, section, first) {
  const lines = section.text.split('\n');
  const out = [];
  if (first) {
    // "Kode: …", "**Kode perilaku:** …", and the bullets under a "**Kode**" line
    lines.forEach((line, i) => {
      if (/^Kode: /.test(line) || /^\*\*Kode [a-z]+:\*\*/.test(line)) out.push(inline(line));
      if (line.trim() === '**Kode**') {
        for (let j = i + 1; j < lines.length && (lines[j].startsWith('- ') || lines[j].trim() === ''); j++) {
          if (lines[j].startsWith('- ')) out.push(inline(lines[j].slice(2)));
        }
      }
    });
  }
  if (sheet.marker === 'L2-kursi') {
    // The check table of step 3 (buklet and test form from row and seat)
    const start = lines.findIndex((l) => /^\s+\| Baris \| Kursi \| Buklet/.test(l));
    if (start < 0) fail('L2 check table (Baris | Kursi | Buklet) not found');
    const t = tableAt(lines, start);
    const after = /\(([^)]+)\)/.exec(t.head[3])?.[1];
    out.push(`<b>Buklet dan bentuk dari panel.</b> Cek (baris/kursi → buklet, bentuk): ${t.body.map((r) => esc(`${r[0]}/${r[1]} → ${r[2]}, ${r[3]}`)).join(' · ')}${after ? `; ${esc(after)}` : ''}.`);
  }
  if (sheet.marker === 'L6-tes') {
    const m = /Nilai status: [^.]+\./.exec(section.text) ?? fail('L6 "Nilai status" sentence not found');
    out.push(inline(m[0]));
  }
  if (sheet.marker === 'L7-izin') {
    const bullet = lines.find((l) => l.startsWith('- Status: ')) ?? fail('L7 status bullet not found');
    const values = [...new Set([...bullet.matchAll(/`([^`]+)`/g)].map((m) => m[1]))];
    out.push(`<b>Status:</b> ${values.map(esc).join(' · ')}. Mundur dan keluar ditulis dengan tanggalnya.`);
  }
  if (sheet.marker === 'L5-wawancara') {
    const start = lines.findIndex((l) => l.startsWith('**Pertanyaan'));
    if (start < 0) fail('L5 questions not found');
    const items = [];
    for (let j = start + 1; j < lines.length && !lines[j].startsWith('**'); j++) {
      const m = /^\d+\. (.+)$/.exec(lines[j]);
      if (m) items.push(`<li>${inline(m[1])}</li>`);
      else if (lines[j].trim() !== '' && items.length) out.push(inline(lines[j]));
    }
    out.unshift(`<b>${inline(lines[start].replace(/\*\*/g, ''))}</b><ol>${items.join('')}</ol>`);
  }
  return out;
}

// ------------------------------------------------------------------ columns

const TYPE_WEIGHT = { session: 2.2, time: 1.7, date: 1.9, int: 1.1, code: 1.3, yesno: 1.15, participant: 1.8,
  seat: 1, seats: 1.4, item: 1.9, try: 1.2, manual: 1.7, text: 2.4, free: 4 };
const WIDE = /catatan|uraian|kejadian|tindakan|kutipan|jawaban|pertanyaan|nama siswa|^status$|^tes (awal|akhir) \(/i;

/** Relative width of a paper heading: from the typed columns it holds, else from its words. */
function weight(sheet, heading) {
  const codes = sheet.paper[heading] ?? [];
  let w = codes.length
    ? codes.reduce((sum, code) => sum + (TYPE_WEIGHT[sheet.columns[code]?.type] ?? 2), 0)
    : Math.min(3, Math.max(1, heading.length * 0.11));
  return WIDE.test(heading) ? Math.max(w, 4) : w;
}

/**
 * Narrowest width of a column in mm: its heading fits in `lines` lines (two
 * on a landscape page, where every millimetre goes to the rows; three on a
 * portrait one) and no word is broken.
 */
function minWidth(heading, lines) {
  const words = heading.split(/\s+/);
  const longestWord = Math.max(...words.map((w) => w.length));
  let best = heading.length;
  for (let width = longestWord; width <= heading.length; width++) {
    let used = 1;
    let line = 0;
    for (const w of words) {
      if (line === 0) line = w.length;
      else if (line + 1 + w.length <= width) line += 1 + w.length;
      else { used++; line = w.length; }
    }
    if (used <= lines) { best = width; break; }
  }
  return best * 2 + 3.4;
}

/** Column widths in mm: by weight, but never under a column's minimum. */
function widths(headings, weights, total, lines) {
  const mins = headings.map((h) => minWidth(h, lines));
  const fixed = new Set();
  for (;;) {
    const freeWeight = weights.reduce((sum, w, i) => (fixed.has(i) ? sum : sum + w), 0);
    const freeMm = total - [...fixed].reduce((sum, i) => sum + mins[i], 0);
    const out = weights.map((w, i) => (fixed.has(i) ? mins[i] : (freeMm * w) / freeWeight));
    const under = out.map((v, i) => (!fixed.has(i) && v < mins[i] ? i : -1)).filter((i) => i >= 0);
    if (under.length === 0) return out;
    under.forEach((i) => fixed.add(i));
  }
}

/** Page content width in mm (_lembar.css), and heading lines allowed. */
const CONTENT_MM = { tegak: 188, datar: 277 };
const HEAD_LINES = { tegak: 3, datar: 2 };

// ------------------------------------------------------------------ pages

const sectionOf = (marker) => {
  const id = marker.slice(0, 2);
  const section = sections[id] ?? fail(`02 has no section for ${id}`);
  if (!section.text.includes(`<!-- lembar: ${marker} -->`)) fail(`02 section ${id} has no table tagged ${marker}`);
  return section;
};
const orientationOf = (id) => (portrait.includes(id) ? 'tegak' : landscape.includes(id) ? 'datar' : fail(`02 does not say how to print ${id}`));
const foot = (code) => `<footer class="sheet-foot"><span>GELITA · Lembar lapangan kosong · ${esc(spec.version)} · ${esc(code)}</span><span>Dibuat dari 02 Lembar Lapangan, versi ${esc(commit)}, ${esc(idDate(date))}</span></footer>`;

/** The line under a sheet's title: who fills it in and when (02 → Daftar lembar). */
function subLine(sheet, section) {
  if (sheet.marker === 'L4-pelatihan') {
    // A record per observer, not per session: its own line from the training paragraph of 02
    const kappa = /κ ≥ [\d,]+/.exec(section.text)?.[0] ?? fail('L4 training threshold (κ ≥ …) not found');
    return `Diisi sekali per pengamat setelah pelatihan · lulus bila ${kappa} untuk perilaku dan untuk emosi`;
  }
  const info = sheetInfo[section.id];
  return info ? `Diisi ${info.by} · ${info.when} · satu lembar untuk ${info.per}` : '';
}

function head(id, title, part, fields, sub) {
  return `<header class="sheet-head">
    <div class="sheet-title"><span class="sheet-badge">${esc(id)}</span><div>
      <h1>${esc(title)}${part ? ` <span class="sheet-part">· ${esc(part)}</span>` : ''}</h1>
      ${sub ? `<p class="sheet-sub">${esc(sub)}</p>` : ''}</div></div>
    <div class="sheet-fields">${fields.map(fieldHtml).join('')}</div>
  </header>`;
}

const pages = [];
const toc = [];

// L0: the checklist of its section in 02 (as FieldSheetService::writeL0Template reads it)
{
  const section = sections.L0 ?? fail('02 has no L0 section');
  const lines = section.text.split('\n');
  const lead = lines.find((l) => l.startsWith('L0 ')) ?? '';
  const groups = [];
  let group = null;
  for (const line of lines) {
    if (/^\*\*[^*]+\*\*$/.test(line.trim())) {
      group = { title: plain(line), parts: [] };
      groups.push(group);
    } else if (group && /^- [^[].*:$/.test(line)) {
      group.parts.push(`<h3>${inline(line.slice(2, -1))}</h3>`);
    } else if (group && /^\s*- \[ \] /.test(line)) {
      group.parts.push(`<li${line.startsWith(' ') ? ' class="sub"' : ''}>${inline(line.replace(/^\s*- \[ \] /, ''))}</li>`);
    }
  }
  if (groups.length === 0) fail('L0 checklist not found');
  // A block is a group, or one meeting inside "tambahan per pertemuan"; a block moves to the next page whole
  const blocks = [];
  for (const g of groups) {
    let block = { h2: g.title, h3: '', items: [] };
    for (const p of g.parts) {
      if (p.startsWith('<h3>')) {
        if (block.items.length) blocks.push(block);
        block = { h2: block.items.length ? '' : block.h2, h3: p, items: [] };
      } else {
        block.items.push(p);
      }
    }
    blocks.push(block);
  }
  const blockHtml = blocks.map((b) => `<div class="group">${b.h2 ? `<h2>${esc(b.h2)}</h2>` : ''}${b.h3}<ul class="checks-l0">${b.items.join('')}</ul></div>`);
  pages.push({ id: 'L0', html: `<section class="sheet ${orientationOf('L0')} flow" data-sheet="L0">
    ${head('L0', section.title, '', headFor({ marker: 'L0' }, section), subLine({ marker: 'L0' }, section))}
    <div class="flow-body">${lead ? `<p class="flow-lead">${inline(lead)}</p>` : ''}${blockHtml.join('')}</div>
    ${foot('L0')}
  </section>` });
  toc.push({ id: 'L0', marker: 'L0', title: section.title, part: '' });
}

// L1–L8: one page per tagged table of 02, in the spec's order of 02 sections
const markers = Object.values(spec.sheets).sort((a, b) => docText.indexOf(`<!-- lembar: ${a.marker} -->`) - docText.indexOf(`<!-- lembar: ${b.marker} -->`));
const firstInSection = new Set();
for (const sheet of markers) {
  const id = sheet.marker.slice(0, 2);
  const section = sectionOf(sheet.marker);
  const many = markers.filter((s) => s.marker.startsWith(`${id}-`)).length > 1;
  const part = many ? sheet.title.replace(/^L\d\s+/, '') : '';
  const isFirstColumns = sheet.layout === 'columns' && sheet.imported && !firstInSection.has(id);
  if (isFirstColumns) firstInSection.add(id);
  const legend = legendFor(sheet, section, isFirstColumns);
  const lines = section.text.split('\n');
  const table = tableAt(lines, lines.findIndex((l) => l.trim() === `<!-- lembar: ${sheet.marker} -->`) + 1);
  const headings = Object.keys(sheet.paper);
  let grid;

  if (sheet.layout === 'fields') {
    const first = table.body.map((r) => plain(r[0]));
    if (JSON.stringify(first) !== JSON.stringify(headings)) {
      fail(`${sheet.marker}: 02 rows (${first.join(', ')}) differ from the spec (${headings.join(', ')}); rebuild the column spec`);
    }
    grid = `<table class="grid fields" data-rows="${table.body.length}">
      <colgroup><col style="width:21%"><col style="width:41%"><col style="width:38%"></colgroup>
      <thead><tr><th scope="col">${esc(table.head[0])}</th><th scope="col">${esc(table.head[1])}</th><th scope="col">Isi di sini</th></tr></thead>
      <tbody>${table.body.map((r) => `<tr><th scope="row">${esc(plain(r[0]))}</th><td class="howto">${inline(r[1])}</td><td class="write"></td></tr>`).join('')}</tbody>
    </table>`;
  } else {
    const paperHead = table.head.map(plain);
    if (JSON.stringify(paperHead) !== JSON.stringify(headings)) {
      fail(`${sheet.marker}: 02 columns (${paperHead.join(', ')}) differ from the spec (${headings.join(', ')}); rebuild the column spec`);
    }
    const rows = rowCount[sheet.marker] ?? fail(`no row count for ${sheet.marker}`);
    const cols = widths(headings, headings.map((h) => weight(sheet, h)), CONTENT_MM[orientationOf(id)], HEAD_LINES[orientationOf(id)]);
    const total = cols.reduce((a, b) => a + b, 0);
    grid = `<table class="grid" data-rows="${rows}">
      <colgroup>${cols.map((w) => `<col style="width:${((100 * w) / total).toFixed(2)}%">`).join('')}</colgroup>
      <thead><tr>${headings.map((h) => `<th scope="col">${esc(h)}</th>`).join('')}</tr></thead>
      <tbody>${`<tr>${headings.map(() => '<td></td>').join('')}</tr>`.repeat(rows)}</tbody>
    </table>`;
  }

  pages.push({ id, html: `<section class="sheet ${orientationOf(id)}" data-sheet="${esc(sheet.marker)}">
    ${head(id, section.title, part, headFor(sheet, section), subLine(sheet, section))}
    ${legend.length ? `<div class="legend">${legend.map((p) => (p.startsWith('<b>') && p.includes('<ol>') ? `<div>${p}</div>` : `<p>${p}</p>`)).join('')}</div>` : ''}
    <div class="grid-wrap">${grid}</div>
    ${foot(sheet.marker)}
  </section>` });
  toc.push({ id, marker: sheet.marker, title: section.title, part });
}

// First page: what is in the file, what to print per meeting, how to write
const meetings = (() => {
  const text = namedSection('Pertemuan dan lembar yang dipakai').split('\n');
  return tableAt(text, text.findIndex((l) => l.startsWith('| Pertemuan'))).body.map((r) => [plain(r[0]), plain(r[4])]);
})();
const cover = `<section class="sheet tegak isi" data-sheet="isi">
  <div>
    <p class="kicker">GELITA · Revisi 10 Analitik</p>
    <h1>Lembar Lapangan L0–L8, lembar kosong</h1>
    <p class="lead">Lembar untuk diisi tangan oleh peneliti di sekolah. Kolomnya sama dengan templat yang diketik dan diunggah ke panel (${esc(spec.version)}). Cetak halaman yang dibutuhkan pertemuan itu; setiap lembar boleh difotokopi sebanyak yang perlu.</p>
    <h2>Isi berkas</h2>
    <table><thead><tr><th>Lembar</th><th>Diisi oleh</th><th>Kapan</th><th class="n">Halaman</th></tr></thead><tbody>
      ${toc.map((t) => `<tr data-toc="${esc(t.marker)}"><td><b>${esc(t.id)}</b> ${esc(t.part || t.title)}</td><td>${esc(sheetInfo[t.id]?.by ?? '')}</td><td>${esc(sheetInfo[t.id]?.when ?? '')}</td><td class="n"></td></tr>`).join('')}
    </tbody></table>
    <h2>Yang dicetak per pertemuan</h2>
    <table><thead><tr><th>Pertemuan</th><th>Lembar</th></tr></thead><tbody>
      ${meetings.map(([m, l]) => `<tr><td>${esc(m)}</td><td>${esc(l)}</td></tr>`).join('')}
    </tbody></table>
    <h2>Cara mengisi</h2>
    <ul>${['Kertas A4.', 'Kepala setiap halaman:', 'Menulis:', 'Nama siswa hanya di L7.'].map((p) => `<li>${inline(howToBullet(p))}</li>`).join('')}</ul>
  </div>
  <div style="flex:1"></div>
  ${foot('isi')}
</section>`;

const html = `<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>Lembar Lapangan L0–L8 kosong</title>
<link rel="stylesheet" href="${pathToFileURL(path.join(here, '_instrumen.css')).href}">
<link rel="stylesheet" href="${pathToFileURL(path.join(here, '_lembar.css')).href}">
</head><body class="lembar">
${cover}
${pages.map((p) => p.html).join('\n')}
</body></html>`;

// ------------------------------------------------------------------ print

/** Runs in the page: splits the checklist over pages, stretches the rows, numbers the contents. */
function layout({ rowMinMm, pxPerMm }) {
  const problems = [];
  for (const first of [...document.querySelectorAll('section.sheet.flow')]) {
    let cur = first;
    while (cur.scrollHeight > cur.clientHeight + 1) {
      const next = cur.cloneNode(false);
      const head = cur.querySelector(':scope > .sheet-head').cloneNode(true);
      head.querySelector('h1').insertAdjacentHTML('beforeend', ' <span class="sheet-part">· lanjutan</span>');
      const body = document.createElement('div');
      body.className = 'flow-body';
      next.append(head, body, cur.querySelector(':scope > .sheet-foot').cloneNode(true));
      cur.after(next);
      while (cur.scrollHeight > cur.clientHeight + 1) {
        const groups = cur.querySelectorAll(':scope > .flow-body > .group');
        if (groups.length < 2) { problems.push(`${cur.dataset.sheet}: one checklist group is taller than a page`); break; }
        body.prepend(groups[groups.length - 1]);
      }
      cur = next;
    }
  }
  for (const section of document.querySelectorAll('section.sheet')) {
    const table = section.querySelector('table.grid');
    if (table) {
      const wrap = table.parentElement;
      const rows = [...table.tBodies[0].rows];
      const free = wrap.clientHeight - table.tHead.offsetHeight - 2;
      let h = Math.floor((free / rows.length) * 10) / 10;
      if (h < rowMinMm * pxPerMm) problems.push(`${section.dataset.sheet}: rows only ${(h / pxPerMm).toFixed(1)} mm high`);
      for (const row of rows) row.style.height = `${h}px`;
      if (table.offsetHeight > wrap.clientHeight + 1) problems.push(`${section.dataset.sheet}: the table is taller than its space`);
      section.dataset.rowMm = (h / pxPerMm).toFixed(1);
    }
    if (section.scrollHeight > section.clientHeight + 1) problems.push(`${section.dataset.sheet}: content overflows the page`);
  }
  const sections = [...document.querySelectorAll('section.sheet')];
  for (const row of document.querySelectorAll('tr[data-toc]')) {
    const marker = row.dataset.toc;
    const at = sections.map((s, i) => (s.dataset.sheet === marker ? i + 1 : 0)).filter(Boolean);
    row.lastElementChild.textContent = at.length > 1 ? `${at[0]}–${at[at.length - 1]}` : String(at[0] ?? '');
  }
  return { problems, pages: sections.length, rows: sections.filter((s) => s.dataset.rowMm).map((s) => `${s.dataset.sheet} ${s.dataset.rowMm}`) };
}

const htmlPath = path.join(tmp, 'lembar-kosong.html');
fs.writeFileSync(htmlPath, html);

const browser = await chromium.launch();
try {
  const tab = await browser.newPage();
  await tab.emulateMedia({ media: 'print' });
  await tab.goto(pathToFileURL(htmlPath).href, { waitUntil: 'load' });
  await tab.evaluate(() => document.fonts.ready);
  const result = await tab.evaluate(layout, { rowMinMm: ROW_MIN_MM, pxPerMm: 96 / 25.4 });
  if (option('html')) fs.writeFileSync(path.resolve(option('html')), await tab.content());
  if (result.problems.length) fail(result.problems.join('; '));
  const pdf = await tab.pdf({ path: target, preferCSSPageSize: true, printBackground: true });
  const printed = (pdf.toString('latin1').match(/\/Type\s*\/Page[^s]/g) ?? []).length;
  if (printed !== result.pages) fail(`${printed} pages printed for ${result.pages} sheets: a sheet runs over its page`);
  console.log(`${path.relative(process.cwd(), target)}  ${printed} pages (02 ${commit}, ${spec.version}); row height in mm: ${result.rows.join(', ')}`);
} finally {
  await browser.close();
}
