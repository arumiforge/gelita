/**
 * Tab di dalam satu halaman (Dok 3 §8.2), mis. editor tantangan:
 * `<ul class="tabs" data-tabs>` berisi tautan `#tab-{kunci}`; isinya adalah
 * elemen `[data-tab-panel~="{kunci}"]` di mana pun letaknya di halaman (satu
 * form boleh terbagi ke beberapa tab; field yang tersembunyi tetap terkirim).
 *
 * Tanpa JavaScript semua bagian tampil berurutan. Tab yang dipilih tersimpan
 * di hash URL, jadi pemuatan ulang sesudah menyimpan kembali ke tab yang sama.
 */
import { $$ } from '../core/dom.js';

export function initTabs(root = document) {
  for (const nav of $$('[data-tabs]', root)) {
    if (nav.dataset.ready) continue;
    nav.dataset.ready = '1';

    const links = $$('a[href^="#tab-"]', nav);
    const keys = links.map((link) => link.getAttribute('href').slice(5));
    const panels = $$('[data-tab-panel]', root);

    const show = (key, push) => {
      if (!keys.includes(key)) key = keys[0];
      for (const panel of panels) panel.hidden = !panel.dataset.tabPanel.split(/\s+/).includes(key);
      links.forEach((link, index) => {
        if (keys[index] === key) link.setAttribute('aria-current', 'page');
        else link.removeAttribute('aria-current');
      });
      if (push) history.replaceState(history.state, '', `#tab-${key}`);
    };

    nav.addEventListener('click', (event) => {
      const link = event.target.closest('a[href^="#tab-"]');
      if (!link) return;
      event.preventDefault();
      show(link.getAttribute('href').slice(5), true);
    });

    // Tautan ke soal (#item-…) membuka tab yang memuat kartu soalnya
    root.addEventListener('click', (event) => {
      const link = event.target.closest('a[data-open-item]');
      if (!link) return;
      const target = document.querySelector(link.getAttribute('href'));
      const panel = target?.closest('[data-tab-panel]');
      if (!target || !panel) return;
      event.preventDefault();
      show(panel.dataset.tabPanel.split(/\s+/)[0], true);
      target.open = true;
      target.scrollIntoView({ block: 'start' });
    });

    // Hash #tab-x, atau #item-… (kembali sesudah menyimpan soal): buka tab pemuatnya
    const hash = window.location.hash;
    const anchored = hash && !hash.startsWith('#tab-') ? document.getElementById(hash.slice(1))?.closest('[data-tab-panel]') : null;
    show(anchored ? anchored.dataset.tabPanel.split(/\s+/)[0] : (hash.startsWith('#tab-') ? hash.slice(5) : keys[0]), false);
  }
}
