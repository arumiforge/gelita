/**
 * Konfigurasi dari server: <script type="application/json" id="app-config">.
 *
 * Tidak ada SESSION_CODE di client — identitas dipegang session server
 * (cookie HttpOnly). `sessionTag` hanya penanda buram sesi permainan (HMAC di
 * server) agar antrean event offline tidak pernah dikirim ke sesi siswa lain
 * yang kebetulan masuk di komputer yang sama.
 *
 * Teks UI untuk JavaScript (`text`) dirender server dari lang('Js.*'), sehingga
 * ikut bahasa ID/EN tanpa kamus kedua di JavaScript.
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
  text: parsed.text && typeof parsed.text === 'object' ? parsed.text : {},
});

export const LOCALE = CONFIG.locale;

/** URL internal aplikasi: url('masuk') → https://…/masuk */
export function url(path = '') {
  return `${CONFIG.baseUrl}${String(path).replace(/^\/+/, '')}`;
}

/**
 * Teks UI: t('blankEmptyText', 3) mengganti {0} dengan 3.
 * Kunci yang tidak ada dikembalikan apa adanya agar kekurangan mudah terlihat.
 */
export function t(key, ...args) {
  const template = Object.hasOwn(CONFIG.text, key) ? String(CONFIG.text[key]) : key;
  return template.replace(/\{(\d+)\}/g, (match, index) => (args[index] !== undefined ? String(args[index]) : match));
}
