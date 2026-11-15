// Builds the contact sheets of ../REVIEW.md: one JPEG per "### Sheet NN" section, with
// every picture of that section's table as a numbered thumbnail.
//
//   node ai-studio/game-images/review/build-sheets.mjs          every sheet
//   node ai-studio/game-images/review/build-sheets.mjs 04 12    only these sheets
//
// Reads REVIEW.md only: a table row is `| No | [`key`](path) | …`, with the path relative
// to ai-studio/game-images/. Run it again after a picture was replaced or a row was added.
// Needs the Playwright that cloud sessions have preinstalled; nothing is installed here.
import { createRequire } from 'node:module';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const { chromium } = createRequire(import.meta.url)('playwright');

const here = path.dirname(fileURLToPath(import.meta.url));
const root = path.dirname(here);
const only = process.argv.slice(2);
const sheets = [];
let current = null;

for (const line of fs.readFileSync(path.join(root, 'REVIEW.md'), 'utf8').split('\n')) {
    const head = line.match(/^### Sheet (\d+): (.+)$/);
    if (head) {
        current = { no: head[1], title: head[2].trim(), rows: [] };
        sheets.push(current);
        continue;
    }
    if (/^## /.test(line)) {
        current = null;
        continue;
    }
    const row = line.match(/^\| (\d+) \| \[`([^`]+)`\]\(([^)]+)\) \|/);
    if (row && current) {
        current.rows.push({ no: row[1], key: row[2], file: row[3] });
    }
}

const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/"/g, '&quot;');
const page = (sheet) => `<!doctype html><meta charset="utf-8"><style>
  body { margin: 0; padding: 14px; width: 1572px; background: #1b2230; font: 13px/1.25 system-ui, sans-serif; color: #e9edf3; }
  h1 { font-size: 18px; margin: 0 0 12px; }
  .grid { display: grid; grid-template-columns: repeat(6, 252px); gap: 10px; }
  figure { margin: 0; }
  .pic { width: 252px; height: 168px; background: repeating-conic-gradient(#8b909a 0% 25%, #7a7f89 0% 50%) 0 0 / 24px 24px; display: flex; align-items: center; justify-content: center; }
  img { max-width: 252px; max-height: 168px; display: block; }
  figcaption { padding: 4px 2px 0; word-break: break-all; font-size: 12px; }
  b { color: #ffd166; font-size: 14px; }
</style><h1>Sheet ${sheet.no}: ${esc(sheet.title)}</h1><div class="grid">${sheet.rows.map((r) => {
    const file = path.resolve(root, r.file);
    const src = fs.existsSync(file) ? pathToFileURL(file).href : '';
    return `<figure><div class="pic">${src ? `<img src="${src}">` : 'file missing'}</div><figcaption><b>${r.no}</b> ${esc(r.key)}</figcaption></figure>`;
}).join('')}</div>`;

const browser = await chromium.launch();
const tab = await browser.newPage({ viewport: { width: 1600, height: 900 } });
for (const sheet of sheets) {
    if (only.length && !only.includes(sheet.no)) continue;
    const html = path.join(here, `.sheet-${sheet.no}.html`);
    fs.writeFileSync(html, page(sheet));
    await tab.goto(pathToFileURL(html).href, { waitUntil: 'load' });
    await tab.evaluate(() => Promise.all([...document.images].map((i) => i.decode().catch(() => null))));
    await tab.screenshot({ path: path.join(here, `sheet-${sheet.no}.jpg`), type: 'jpeg', quality: 78, fullPage: true });
    fs.unlinkSync(html);
    const missing = sheet.rows.filter((r) => !fs.existsSync(path.resolve(root, r.file))).map((r) => r.key);
    console.log(`sheet ${sheet.no}: ${sheet.rows.length} pictures${missing.length ? ', MISSING ' + missing.join(', ') : ''}`);
}
await browser.close();
