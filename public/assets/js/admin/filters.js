/**
 * Filter bar panel (form GET).
 *
 * Perubahan filter memperbarui URL lewat history.replaceState (tetap dapat
 * dibagikan/di-bookmark) lalu memuat isi halaman untuk filter baru di latar,
 * tanpa memuat ulang halaman: KPI dan tabel dirender ulang server, chart
 * mengambil ulang datanya dari /api/admin/* dengan filter yang sama. Form
 * filter sendiri tidak diganti, jadi fokus keyboard tidak hilang.
 *
 * "Atur ulang" mengembalikan ke filter bawaan. Tanpa JavaScript tombol
 * Terapkan tetap mengirim form seperti biasa.
 */
import { $, $$, debounce } from '../core/dom.js';
import { toast } from '../core/toast.js';

let controller = null;

function queryOf(form) {
  const params = new URLSearchParams();
  for (const [name, value] of new FormData(form)) {
    if (typeof value === 'string' && value.trim() !== '') params.append(name, value.trim());
  }
  return params.toString();
}

async function apply(form, onSwap, query = queryOf(form)) {
  const main = $('#admin-main');
  const url = `${window.location.pathname}${query ? `?${query}` : ''}`;

  // Form yang bukan anak langsung <main> tidak dapat ditukar aman: navigasi biasa
  if (!main || form.parentElement !== main) {
    window.location.assign(url);
    return;
  }

  history.replaceState(history.state, '', url);
  controller?.abort();
  controller = new AbortController();
  main.setAttribute('aria-busy', 'true');
  main.classList.add('is-refreshing');

  try {
    const res = await fetch(url, { credentials: 'same-origin', signal: controller.signal, headers: { Accept: 'text/html' } });
    if (!res.ok) throw new Error(String(res.status));
    const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
    const fresh = doc.getElementById('admin-main');
    const freshForm = fresh ? $('form.filter-bar', fresh) : null;
    if (!fresh || !freshForm || freshForm.parentElement !== fresh) throw new Error('layout');

    // Ganti semua saudara form filter; form-nya sendiri tetap (fokus terjaga)
    const before = [];
    const after = [];
    let seen = false;
    for (const node of Array.from(fresh.childNodes)) {
      if (node === freshForm) seen = true;
      else (seen ? after : before).push(node);
    }

    onSwap?.('dispose', main);
    let node = form.previousSibling;
    while (node) {
      const prev = node.previousSibling;
      node.remove();
      node = prev;
    }
    node = form.nextSibling;
    while (node) {
      const next = node.nextSibling;
      node.remove();
      node = next;
    }
    form.before(...before.map((n) => document.importNode(n, true)));
    form.after(...after.map((n) => document.importNode(n, true)));

    // Tombol "Atur ulang" hanya ada bila filter aktif: ikuti versi server
    const actions = $('.filter-actions', form);
    const freshActions = $('.filter-actions', freshForm);
    if (actions && freshActions) actions.replaceWith(document.importNode(freshActions, true));

    document.title = doc.title || document.title;
    onSwap?.('init', main);
  } catch (error) {
    if (error.name === 'AbortError') return;
    toast('Data untuk filter ini gagal dimuat. Halaman dimuat ulang.', 'bad');
    window.location.assign(url);
  } finally {
    main.removeAttribute('aria-busy');
    main.classList.remove('is-refreshing');
  }
}

function clear(form) {
  for (const field of $$('select, input', form)) {
    if (field.type === 'hidden' || field.type === 'submit') continue;
    if (field.tagName === 'SELECT') field.value = '';
    else field.value = '';
  }
}

/**
 * @param {(phase: 'dispose'|'init', root: HTMLElement) => void} onSwap
 */
export function initFilters(onSwap) {
  const forms = $$('form.filter-bar[method="get"], form.filter-bar:not([method])');

  for (const form of forms) {
    const run = () => apply(form, onSwap);
    const runLater = debounce(run, 450);

    form.addEventListener('change', (event) => {
      if (event.target.matches('input[type="search"], input[type="text"]')) return;
      run();
    });
    form.addEventListener('input', (event) => {
      if (event.target.matches('input[type="search"], input[type="text"]')) runLater();
    });
    form.addEventListener('submit', (event) => {
      event.preventDefault();
      run();
    });
    form.addEventListener('click', (event) => {
      const reset = event.target.closest('.filter-actions a');
      if (!reset) return;
      event.preventDefault();
      clear(form);
      apply(form, onSwap, '');
    });
  }
}
