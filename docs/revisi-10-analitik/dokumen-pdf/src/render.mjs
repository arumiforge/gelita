#!/usr/bin/env node
/**
 * Renders the instrument pages in this folder to A4 PDFs one level up.
 *
 *   node docs/revisi-10-analitik/dokumen-pdf/src/render.mjs [name ...]
 *
 * Every *.html here (except files starting with "_") becomes ../{name}.pdf.
 * Page size and margins come from @page in _instrumen.css (A4 portrait, with
 * landscape pages for wide rating sheets marked class="wide").
 * The running footer comes from <meta name="pdf-footer" content="...">;
 * Chromium adds the page number and total. Uses the pre-installed
 * Playwright Chromium; never run `playwright install`.
 */
import { createRequire } from 'node:module';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');
const here = path.dirname(fileURLToPath(import.meta.url));
const out = path.resolve(here, '..');
const only = process.argv.slice(2);

const pages = fs.readdirSync(here)
  .filter((f) => f.endsWith('.html') && !f.startsWith('_'))
  .filter((f) => only.length === 0 || only.includes(path.basename(f, '.html')));

const escapeHtml = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

const browser = await chromium.launch();
for (const file of pages) {
  const html = fs.readFileSync(path.join(here, file), 'utf8');
  const footer = /name="pdf-footer" content="([^"]*)"/.exec(html);
  const label = footer ? escapeHtml(footer[1]) : '';
  const page = await browser.newPage();
  await page.goto(pathToFileURL(path.join(here, file)).href);
  await page.evaluate(() => document.fonts.ready);
  const target = path.join(out, path.basename(file, '.html') + '.pdf');
  await page.pdf({
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
  await page.close();
  console.log(`${path.relative(process.cwd(), target)}`);
}
await browser.close();
