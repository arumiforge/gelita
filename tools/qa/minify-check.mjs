#!/usr/bin/env node
/**
 * Minified JS check (gelita:aset:minify, App\Libraries\AssetMinifier).
 *
 * Parses every source file under public/assets/js/ and its minified copy
 * under public/assets/min/{version}/js/ with acorn and compares the syntax trees,
 * ignoring only positions and raw spellings. Equal trees mean the minifier
 * removed comments and whitespace and nothing else. That covers what a
 * syntax check misses, such as spaces lost inside a nested template literal,
 * which would change the text students read.
 *
 * Usage:
 *   php spark gelita:aset:minify
 *   node tools/qa/minify-check.mjs [--src=public/assets/js] [--min=public/assets/min/{version}/js]
 *
 * acorn comes from `npm root -g`, the local node_modules, or
 * /opt/node-tools/node_modules (cloud sessions). Set ACORN_PATH to point
 * elsewhere. Exit code 0 when every file matches, 1 otherwise, 2 when acorn
 * is missing.
 */
import { createRequire } from 'node:module';
import { execSync } from 'node:child_process';
import { existsSync, readdirSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath, pathToFileURL } from 'node:url';

const ROOT = join(dirname(fileURLToPath(import.meta.url)), '..', '..');
const arg = (name, fallback) => (process.argv.find(a => a.startsWith(`--${name}=`)) ?? '').split('=')[1] || fallback;
const SRC = arg('src', join(ROOT, 'public/assets/js'));
// Default: the current build named in public/assets/min/sources.json
const currentBuild = () => {
  try { return JSON.parse(readFileSync(join(ROOT, 'public/assets/min/sources.json'), 'utf8')).version; } catch { return 'missing'; }
};
const MIN = arg('min', null) ?? join(ROOT, 'public/assets/min', currentBuild(), 'js');

async function loadAcorn() {
  const candidates = [process.env.ACORN_PATH, '/opt/node-tools/node_modules/acorn'];
  try { candidates.push(join(execSync('npm root -g', { stdio: ['ignore', 'pipe', 'ignore'] }).toString().trim(), 'acorn')); } catch { /* no npm */ }
  try { return createRequire(import.meta.url)('acorn'); } catch { /* not local */ }
  for (const dir of candidates.filter(Boolean)) {
    const entry = join(dir, 'dist', 'acorn.mjs');
    if (existsSync(entry)) return import(pathToFileURL(entry).href);
  }
  return null;
}

/** Tree without positions; `!0`/`!1` read as true/false in case a minifier shortens them. */
function normalize(node) {
  if (Array.isArray(node)) return node.map(normalize);
  if (!node || typeof node !== 'object') return node;
  if (node.type === 'UnaryExpression' && node.operator === '!' && node.argument.type === 'Literal' && (node.argument.value === 0 || node.argument.value === 1)) {
    return { type: 'Literal', value: node.argument.value === 0 };
  }
  const out = {};
  for (const [key, value] of Object.entries(node)) {
    if (!['start', 'end', 'loc', 'range', 'raw'].includes(key)) out[key] = normalize(value);
  }
  if (out.type === 'TemplateElement') out.value = { cooked: out.value.cooked };
  if (out.type === 'Literal' && out.regex) out.value = null;
  return out;
}

const acorn = await loadAcorn();
if (!acorn) {
  console.error('acorn not found; set ACORN_PATH to its package folder.');
  process.exit(2);
}

const parse = text => {
  try { return acorn.parse(text, { ecmaVersion: 'latest', sourceType: 'module' }); } catch {
    return acorn.parse(text, { ecmaVersion: 'latest', sourceType: 'script' });
  }
};

const files = readdirSync(SRC, { recursive: true }).filter(f => f.endsWith('.js')).sort();
let failures = 0;

for (const file of files) {
  const minPath = join(MIN, file);
  if (!existsSync(minPath)) { console.log(`MISSING  ${file}`); failures++; continue; }
  let a; let b;
  try { a = JSON.stringify(normalize(parse(readFileSync(join(SRC, file), 'utf8')))); } catch (e) { console.log(`SOURCE   ${file}: ${e.message}`); failures++; continue; }
  try { b = JSON.stringify(normalize(parse(readFileSync(minPath, 'utf8')))); } catch (e) { console.log(`PARSE    ${file}: ${e.message}`); failures++; continue; }
  if (a !== b) {
    let i = 0;
    while (a[i] === b[i]) i++;
    console.log(`CHANGED  ${file}\n  source:   …${a.slice(Math.max(0, i - 80), i + 80)}…\n  minified: …${b.slice(Math.max(0, i - 80), i + 80)}…`);
    failures++;
  }
}

console.log(`${files.length} files, ${failures} problem(s).`);
process.exit(failures === 0 ? 0 : 1);
