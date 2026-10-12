/**
 * Konfigurasi dari server: <script type="application/json" id="app-config">.
 *
 * Tidak ada SESSION_CODE di client — identitas dipegang session server
 * (cookie HttpOnly). `sessionTag` hanya penanda buram sesi permainan (HMAC di
 * server) agar antrean event offline tidak pernah dikirim ke sesi siswa lain
 * yang kebetulan masuk di komputer yang sama.
 *
 * Teks UI untuk JavaScript (`text`) dirender server dari lang('Js.*').
 *
 * Asset package Stage 4 (docs/rencana-paket-aset.md §4.4): the server sends the
 * texts in both languages (`text.id`, `text.en`), so setLocale() switches the
 * texts of JS-built toasts and modals without another request. An older flat
 * `text` map (one language) is still accepted.
 *
 * assetsLocal() is the hook Stage 2 (service worker package) switches on; see
 * docs/06_JAVASCRIPT.md §Asset package hooks.
 */
const el = document.getElementById('app-config');

let parsed = {};
try {
  parsed = el ? JSON.parse(el.textContent) : {};
} catch {
  parsed = {};
}

const base = (parsed.baseUrl || document.body.dataset.base || '/').replace(/\/?$/, '/');

export const CONFIG = Object.freeze({
  locale: parsed.locale || document.body.dataset.locale || 'id',
  csrfName: parsed.csrfName || document.querySelector('meta[name="csrf-name"]')?.content || '',
  csrfHash: parsed.csrfHash || document.querySelector('meta[name="csrf-token"]')?.content || '',
  apiBase: (parsed.apiBase || `${base}api`).replace(/\/+$/, ''),
  baseUrl: base,
  sessionTag: parsed.sessionTag || null,
  // lowest sequence_no for this page's client events (core/events.js)
  sequenceFloor: Number(parsed.sequenceFloor) || 0,
  // music and sound effects of the active asset package: { 'music/map': url, 'sfx/click': url } (core/audio.js)
  audioUrls: parsed.audioUrls && typeof parsed.audioUrls === 'object' && !Array.isArray(parsed.audioUrls) ? Object.freeze({ ...parsed.audioUrls }) : Object.freeze({}),
  text: parsed.text && typeof parsed.text === 'object' ? parsed.text : {},
});

export const LOCALE = CONFIG.locale;

const isMap = (value) => Boolean(value) && typeof value === 'object' && !Array.isArray(value);

/** Texts per locale: { id: {...}, en: {...} }; a flat map counts as the active locale. */
const textsByLocale = (() => {
  const text = CONFIG.text;
  const keys = Object.keys(text);
  if (keys.length && keys.every((key) => isMap(text[key]))) return text;
  return { [CONFIG.locale]: text };
})();

let activeLocale = Object.hasOwn(textsByLocale, CONFIG.locale) ? CONFIG.locale : Object.keys(textsByLocale)[0] ?? CONFIG.locale;

/** Locale whose texts t() returns; changes with setLocale(). */
export const currentLocale = () => activeLocale;

/** Locales with texts in this page (normally `id` and `en`). */
export const textLocales = () => Object.keys(textsByLocale);

/**
 * Switch t() to another locale already in the page, with no request.
 * Sets <html lang> and fires `gelita:locale` ({ detail: { from, to } }) on
 * document. Returns false when the page has no texts for that locale.
 */
export function setLocale(code) {
  if (!Object.hasOwn(textsByLocale, code)) return false;
  if (code === activeLocale) return true;
  const from = activeLocale;
  activeLocale = code;
  document.documentElement.lang = code;
  document.dispatchEvent(new CustomEvent('gelita:locale', { detail: { from, to: code } }));
  return true;
}

/*
 * "Assets are local" hook (Stage 4 → Stage 2 contract).
 *
 * true when the page's images, audio, and modules come from the on-device
 * asset package, so curtains between screens can be short (§4.5, P8).
 * False by default. Stage 2 switches it on in one of two ways:
 *   - <html data-assets-local="1"> set before game.js runs (inline script), or
 *   - setAssetsLocal(true) from its module once the service worker confirms
 *     the active package is complete. Curtains that already started keep
 *     their minimum time.
 */
let assetsLocalFlag = null;

export function assetsLocal() {
  if (assetsLocalFlag !== null) return assetsLocalFlag;
  return document.documentElement.dataset.assetsLocal === '1';
}

export function setAssetsLocal(on) {
  assetsLocalFlag = Boolean(on);
}

/** URL internal aplikasi: url('masuk') → https://…/masuk */
export function url(path = '') {
  return `${CONFIG.baseUrl}${String(path).replace(/^\/+/, '')}`;
}

/**
 * Teks UI: t('blankEmptyText', 3) mengganti {0} dengan 3.
 * Kunci yang tidak ada dikembalikan apa adanya agar kekurangan mudah terlihat.
 */
export function t(key, ...args) {
  const text = textsByLocale[activeLocale] ?? {};
  const template = Object.hasOwn(text, key) ? String(text[key]) : key;
  return template.replace(/\{(\d+)\}/g, (match, index) => (args[index] !== undefined ? String(args[index]) : match));
}
