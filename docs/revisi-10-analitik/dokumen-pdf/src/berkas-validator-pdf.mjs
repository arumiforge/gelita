#!/usr/bin/env node
/**
 * Prints the given HTML files to PDF next to them (A4, size from @page).
 *
 *   node docs/revisi-10-analitik/dokumen-pdf/src/berkas-validator-pdf.mjs file.html [...]
 *
 * Called by berkas-validator.php for the B.5a booklet. Follows render.mjs:
 * pre-installed Playwright Chromium (never run `playwright install`), page
 * number in the footer.
 */
import { createRequire } from 'node:module';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const require = createRequire(import.meta.url);
const { chromium } = require('playwright');

const browser = await chromium.launch();
for (const file of process.argv.slice(2)) {
  const page = await browser.newPage();
  await page.goto(pathToFileURL(path.resolve(file)).href);
  await page.evaluate(() => document.fonts.ready);
  const target = file.replace(/\.html$/, '.pdf');
  await page.pdf({
    path: target,
    preferCSSPageSize: true,
    printBackground: true,
    displayHeaderFooter: true,
    headerTemplate: '<span></span>',
    footerTemplate: `<div style="width:100%;margin:0 12mm;display:flex;justify-content:space-between;
      font-family:Inter,sans-serif;font-size:7.5px;color:#4a5568">
      <span>GELITA · Buklet pelabelan buta B.5a · rahasia sampai pelabelan selesai</span>
      <span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>`,
  });
  await page.close();
  console.log(path.relative(process.cwd(), target));
}
await browser.close();
