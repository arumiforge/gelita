/**
 * Pembantu DOM kecil: $, $$, on (delegasi), el (membuat elemen), esc.
 *
 * Aturan sistem 6: data dari server ditulis lewat textContent atau pembuatan
 * elemen, bukan innerHTML dengan string gabungan. `el()` memakai textContent
 * untuk anak berupa string, jadi aman untuk teks yang diketik admin.
 */
export const $ = (selector, root = document) => root.querySelector(selector);
export const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));

/**
 * on(el, 'click', fn) atau on(el, 'click', '.selector', fn) untuk delegasi.
 * Mengembalikan fungsi pelepas.
 */
export function on(target, type, selector, handler, options) {
  if (typeof selector === 'function') {
    target.addEventListener(type, selector, handler);
    return () => target.removeEventListener(type, selector, handler);
  }

  const listener = (event) => {
    const match = event.target instanceof Element ? event.target.closest(selector) : null;
    if (match && target.contains(match)) handler(event, match);
  };
  target.addEventListener(type, listener, options);
  return () => target.removeEventListener(type, listener, options);
}

/** Escape untuk konteks HTML — hanya bila innerHTML tidak terhindarkan. */
export function esc(value) {
  return String(value ?? '').replace(/[&<>"']/g, (ch) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[ch]));
}

/**
 * el('p', { class: 'x', hidden: true, dataset: { item: 3 } }, 'teks', childNode)
 */
export function el(tag, attrs = {}, ...children) {
  const node = document.createElement(tag);

  for (const [name, value] of Object.entries(attrs || {})) {
    if (value === null || value === undefined || value === false) continue;
    if (name === 'class') node.className = value;
    else if (name === 'dataset') Object.assign(node.dataset, value);
    else if (name === 'text') node.textContent = value;
    else if (name.startsWith('on') && typeof value === 'function') node.addEventListener(name.slice(2), value);
    else node.setAttribute(name, value === true ? '' : String(value));
  }

  for (const child of children.flat()) {
    if (child === null || child === undefined || child === false) continue;
    node.append(child instanceof Node ? child : document.createTextNode(String(child)));
  }

  return node;
}

/** Isi <script type="application/json" id="…">; null bila tidak ada/rusak. */
export function readJson(id) {
  const node = document.getElementById(id);
  if (!node) return null;
  try {
    return JSON.parse(node.textContent);
  } catch {
    return null;
  }
}

export const reducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;

export const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/** Gulir elemen ke tengah layar; langsung (tanpa animasi) bila reduced motion. */
export function scrollToCenter(node) {
  node?.scrollIntoView({ block: 'center', behavior: reducedMotion() ? 'auto' : 'smooth' });
}

/** Menyalin ikon SVG dari <template data-icon="…"> yang dirender server. */
export function icon(name) {
  const tpl = document.querySelector(`template[data-icon="${name}"]`);
  return tpl ? tpl.content.firstElementChild.cloneNode(true) : document.createTextNode('');
}

/** Wilayah aria-live bersama untuk pengumuman singkat pembaca layar. */
export function announce(message) {
  let region = document.getElementById('sr-announcer');
  if (!region) {
    region = el('p', { id: 'sr-announcer', class: 'visually-hidden', 'aria-live': 'polite' });
    document.body.append(region);
  }
  region.textContent = '';
  // jeda kecil agar pembaca layar mengumumkan teks yang sama dua kali berturut-turut
  setTimeout(() => { region.textContent = message; }, 30);
}

export function debounce(fn, wait) {
  let timer = null;
  return (...args) => {
    clearTimeout(timer);
    timer = setTimeout(() => fn(...args), wait);
  };
}
