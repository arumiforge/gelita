#!/usr/bin/env node
/**
 * Builds a print-ready A4 PDF of every numbered research-revision document
 * (docs/revisi-10-analitik/NN_*.md) into ../cetak/NN_*.pdf.
 *
 *   node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs               # changed documents only
 *   node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs --all         # every document
 *   node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs 04 22         # by number
 *   node docs/revisi-10-analitik/dokumen-pdf/src/cetak.mjs --allow-dirty # include uncommitted edits
 *
 * Markdown goes through pandoc (GitHub-flavoured, GitHub-style heading ids),
 * then Chromium prints it with _instrumen.css and _cetak.css. Tables too wide
 * for a portrait page move to landscape pages, together with the heading just
 * above them. Links to other files become plain text, because paper cannot
 * follow them; links inside the same document stay clickable.
 *
 * The footer names the commit that last changed the document, so a printed
 * copy shows which version it is. A document with uncommitted changes has no
 * such commit, so it is refused unless --allow-dirty is passed (its footer then
 * says "belum di-commit"). Commit the Markdown first, then build, then commit
 * the PDFs.
 *
 * ../cetak/manifest.json records the source hash, commit, build hash, and page
 * count of each PDF. A document is rebuilt only when one of those changes, so
 * unchanged PDFs keep their bytes. A full run (no document numbers) deletes
 * PDFs whose document no longer exists.
 *
 * Needs pandoc and the pre-installed Playwright Chromium; never run
 * `playwright install`.
 */
import { createRequire } from 'node:module';
import { execFileSync } from 'node:child_process';
import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const here = path.dirname(fileURLToPath(import.meta.url));
const docsDir = path.resolve(here, '..', '..');
const outDir = path.resolve(here, '..', 'cetak');
const manifestPath = path.join(outDir, 'manifest.json');

const args = process.argv.slice(2);
const rebuildAll = args.includes('--all');
const allowDirty = args.includes('--allow-dirty');
const only = args.filter((a) => !a.startsWith('--')).map((n) => n.padStart(2, '0') + '_');

// Content width of the portrait and landscape pages in _instrumen.css, in CSS px (96 per inch).
const PORTRAIT_PX = Math.floor(((210 - 36) / 25.4) * 96);
const LANDSCAPE_PX = Math.floor(((297 - 32) / 25.4) * 96);

const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli',
  'Agustus', 'September', 'Oktober', 'November', 'Desember'];
const idDate = (iso) => {
  const [y, m, d] = iso.split('-').map(Number);
  return `${d} ${MONTHS[m - 1]} ${y}`;
};
const sha = (data) => crypto.createHash('sha256').update(data).digest('hex');
const git = (...a) => execFileSync('git', a, { cwd: docsDir, encoding: 'utf8' }).trim();
const escapeHtml = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
const fileUrl = (p) => pathToFileURL(p).href;

const buildHash = sha(['cetak.mjs', '_cetak.css', '_instrumen.css']
  .map((f) => fs.readFileSync(path.join(here, f), 'utf8')).join('\0'));

const docs = fs.readdirSync(docsDir)
  .filter((f) => /^\d\d_.+\.md$/.test(f))
  .sort()
  .filter((f) => only.length === 0 || only.some((prefix) => f.startsWith(prefix)));

fs.mkdirSync(outDir, { recursive: true });
const manifest = fs.existsSync(manifestPath)
  ? JSON.parse(fs.readFileSync(manifestPath, 'utf8'))
  : { docs: {} };

/** Turns pandoc's HTML fragment into a printable page body. */
function printableBody(html) {
  return html
    // pandoc sizes columns from the dash count of the separator row; let the browser size them.
    .replace(/<colgroup>[\s\S]*?<\/colgroup>\n?/g, '')
    .replace(/ style="width: [\d.]+%;?"/g, '')
    .replace(/<a href="([^"]*)"([^>]*)>([\s\S]*?)<\/a>/g, (all, href, rest, text) => {
      if (href.startsWith('#') || /^(https?:|mailto:)/.test(href)) return all;
      return `<span class="xref">${text}</span>`;
    })
    .replace(/<img src="([^"]+)"/g, (all, src) => (/^[a-z]+:/.test(src)
      ? all
      : `<img src="${fileUrl(path.resolve(docsDir, decodeURI(src)))}"`));
}

/** Moves tables that overflow a portrait page onto landscape pages. Runs in the page. */
function placeWideTables({ portrait, landscape }) {
  let wide = 0;
  for (const table of document.querySelectorAll('table')) {
    if (table.offsetWidth <= portrait + 1) continue;
    wide++;
    const prev = table.previousElementSibling;
    const group = prev?.classList.contains('wide') ? prev : document.createElement('div');
    if (group !== prev) {
      group.className = 'wide';
      table.before(group);
      if (prev && /^H[2-4]$/.test(prev.tagName)) group.append(prev);
    }
    group.append(table);
    group.style.width = `${landscape}px`;
    if (table.offsetWidth > landscape + 1) table.classList.add('squeeze');
    group.style.width = '';
  }
  return wide;
}

const tmp = fs.mkdtempSync(path.join(os.tmpdir(), 'gelita-cetak-'));
const today = new Date().toISOString().slice(0, 10);
const refused = [];
let built = 0;
let unchanged = 0;

const browser = await chromium.launch();
for (const file of docs) {
  const name = file.replace(/\.md$/, '');
  const target = path.join(outDir, `${name}.pdf`);
  const source = fs.readFileSync(path.join(docsDir, file));

  const dirty = git('status', '--porcelain', '--', file) !== '';
  if (dirty && !allowDirty) {
    refused.push(file);
    continue;
  }
  const [commit, date] = dirty ? ['belum di-commit', today] : git('log', '-1', '--format=%h %cs', '--', file).split(' ');

  const entry = { source: sha(source), commit, build: buildHash };
  const prev = manifest.docs[file];
  if (!rebuildAll && prev && fs.existsSync(target)
    && prev.source === entry.source && prev.commit === entry.commit && prev.build === entry.build) {
    unchanged++;
    continue;
  }

  const fragment = execFileSync('pandoc', ['-f', 'gfm', '-t', 'html5', '--wrap=none'],
    { input: source, encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 });
  const title = /<h1[^>]*>([\s\S]*?)<\/h1>/.exec(fragment)?.[1].replace(/<[^>]+>/g, '') ?? name;
  const page = `<!doctype html>
<html lang="id"><head><meta charset="utf-8"><title>${title}</title>
<link rel="stylesheet" href="${fileUrl(path.join(here, '_instrumen.css'))}">
<link rel="stylesheet" href="${fileUrl(path.join(here, '_cetak.css'))}">
</head><body class="cetak">
${printableBody(fragment)}
</body></html>`;
  const htmlPath = path.join(tmp, `${name}.html`);
  fs.writeFileSync(htmlPath, page);

  const tab = await browser.newPage({ viewport: { width: PORTRAIT_PX, height: 1000 } });
  await tab.goto(fileUrl(htmlPath), { waitUntil: 'load' });
  await tab.evaluate(() => document.fonts.ready);
  const wide = await tab.evaluate(placeWideTables, { portrait: PORTRAIT_PX, landscape: LANDSCAPE_PX });

  const label = escapeHtml(`${name} · GELITA Revisi 10 Analitik · versi ${commit}, ${idDate(date)}`);
  const pdf = await tab.pdf({
    path: target,
    preferCSSPageSize: true,
    printBackground: true,
    displayHeaderFooter: true,
    headerTemplate: '<span></span>',
    footerTemplate: `<div style="width:100%;margin:0 18mm;display:flex;justify-content:space-between;
      font-family:Inter,sans-serif;font-size:7.5px;color:#4a5568;letter-spacing:.02em">
      <span>${label}</span>
      <span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>`,
  });
  await tab.close();

  const pages = (pdf.toString('latin1').match(/\/Type\s*\/Page[^s]/g) ?? []).length;
  manifest.docs[file] = { ...entry, date, pages, landscapeTables: wide };
  built++;
  console.log(`${name}.pdf  ${pages} pages${wide ? `, ${wide} landscape tables` : ''}`);
}
await browser.close();
fs.rmSync(tmp, { recursive: true, force: true });

// A full run also removes PDFs whose document is gone.
if (only.length === 0) {
  for (const key of Object.keys(manifest.docs)) {
    if (docs.includes(key)) continue;
    fs.rmSync(path.join(outDir, key.replace(/\.md$/, '.pdf')), { force: true });
    delete manifest.docs[key];
    console.log(`removed: ${key.replace(/\.md$/, '.pdf')}`);
  }
}

manifest.docs = Object.fromEntries(Object.entries(manifest.docs).sort(([a], [b]) => a.localeCompare(b)));
fs.writeFileSync(manifestPath, `${JSON.stringify(manifest, null, 2)}\n`);

console.log(`${built} built, ${unchanged} unchanged${refused.length ? `, ${refused.length} refused` : ''}`);
if (refused.length) {
  console.error(`Uncommitted changes, not built: ${refused.join(', ')}. Commit them first, or pass --allow-dirty.`);
  process.exitCode = 1;
}
