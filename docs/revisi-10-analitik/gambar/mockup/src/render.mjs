#!/usr/bin/env node
/**
 * Renders the static mock-ups in this folder to WebP pictures one level up.
 *
 *   node docs/revisi-10-analitik/gambar/mockup/src/render.mjs [name ...]
 *
 * Every *.html here (except files starting with "_") becomes ../{name}.webp.
 * The pages load the game's real CSS, fonts, and UI pictures from
 * public/assets and gelita-katalog/assets through relative paths, so they
 * open straight from disk (file://). A page may set <meta name="viewport-size"
 * content="844x390"> to render at another window size; the default is the
 * game's 1280 × 720 stage. Uses the pre-installed Playwright Chromium and
 * Pillow (python3) for the WebP step; never run `playwright install`.
 */
import { createRequire } from 'node:module';
import { execFileSync } from 'node:child_process';
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

const browser = await chromium.launch();
for (const file of pages) {
  const html = fs.readFileSync(path.join(here, file), 'utf8');
  const size = /name="viewport-size" content="(\d+)x(\d+)"/.exec(html);
  const [width, height] = size ? [Number(size[1]), Number(size[2])] : [1280, 720];
  const page = await browser.newPage({ viewport: { width, height }, deviceScaleFactor: 1 });
  await page.goto(pathToFileURL(path.join(here, file)).href);
  await page.evaluate(() => document.fonts.ready);
  await page.waitForTimeout(150);
  const png = path.join(out, path.basename(file, '.html') + '.png');
  await page.screenshot({ path: png });
  await page.close();
  const webp = png.replace(/\.png$/, '.webp');
  execFileSync('python3', ['-I', '-c',
    'import sys; from PIL import Image; Image.open(sys.argv[1]).convert("RGB").save(sys.argv[2], "WEBP", quality=78, method=6)',
    png, webp]);
  fs.unlinkSync(png);
  console.log(path.basename(webp), Math.round(fs.statSync(webp).size / 1024) + ' KB');
}
await browser.close();
