/**
 * Konfigurasi dari server: <script type="application/json" id="app-config">.
 * Tidak ada SESSION_CODE di client — identitas dipegang session server (cookie HttpOnly).
 */
const el = document.getElementById('app-config');

let parsed = {};
try {
  parsed = el ? JSON.parse(el.textContent) : {};
} catch {
  parsed = {};
}

export const CONFIG = Object.freeze({
  locale: parsed.locale || document.body.dataset.locale || 'id',
  csrfName: parsed.csrfName || document.querySelector('meta[name="csrf-name"]')?.content || '',
  csrfHash: parsed.csrfHash || document.querySelector('meta[name="csrf-token"]')?.content || '',
  apiBase: (parsed.apiBase || `${document.body.dataset.base || '/'}api`).replace(/\/+$/, ''),
});

export const LOCALE = CONFIG.locale;
