/**
 * Satu pintu untuk seluruh permintaan ke server.
 * Bentuk respons server: { success, data | code + message, request_id }.
 *
 * RATE_LIMITED dicoba ulang otomatis (backoff 2s, 4s, 8s). Galat lain
 * dilempar sebagai ApiError; handleApiError() menerjemahkannya menjadi
 * tindakan UI yang seragam — tanpa pernah menampilkan stack trace atau isi
 * respons mentah kepada anak (aturan 8).
 */
import { CONFIG, t, url } from './config.js';
import { toast } from './toast.js';

const RETRY_DELAYS = [2000, 4000, 8000];

export class ApiError extends Error {
  constructor(code, message, status, payload) {
    super(message);
    this.name = 'ApiError';
    this.code = code;
    this.status = status;
    this.payload = payload;
    this.requestId = payload?.request_id ?? null;
  }

  /** Galat jaringan: server tidak terjangkau (bukan penolakan dari server). */
  get isNetwork() {
    return this.code === 'NETWORK';
  }
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

/** Path relatif API (`/events`) atau URL lengkap (data-endpoint chart admin). */
function resolve(path) {
  return /^https?:\/\//.test(path) ? path : `${CONFIG.apiBase}${path}`;
}

export async function apiRequest(path, { method = 'GET', body = null, keepalive = false, retry = true } = {}) {
  const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
  if (body !== null) headers['Content-Type'] = 'application/json';
  if (method !== 'GET') headers['X-CSRF-TOKEN'] = CONFIG.csrfHash;

  for (let attempt = 0; ; attempt++) {
    let res;
    try {
      res = await fetch(resolve(path), {
        method,
        headers,
        credentials: 'same-origin',
        body: body !== null ? JSON.stringify(body) : null,
        keepalive,
      });
    } catch {
      throw new ApiError('NETWORK', t('errNetwork'), 0, null);
    }

    let json = null;
    try {
      json = await res.json();
    } catch {
      json = null;
    }

    if (res.status === 429 && retry && attempt < RETRY_DELAYS.length) {
      await sleep(RETRY_DELAYS[attempt]);
      continue;
    }

    if (!res.ok || !json || json.success === false) {
      // Tanpa JSON: gateway/proxy mati atau halaman HTML galat — perlakukan
      // sebagai gangguan jaringan, bukan sebagai jawaban server.
      const code = json?.code || (res.status >= 500 || !json ? 'NETWORK' : 'INVALID_RESPONSE');
      throw new ApiError(code, json?.message || t('errNetwork'), res.status, json);
    }

    return json.data;
  }
}

let sessionModalOpen = false;

/**
 * Tindakan UI seragam untuk galat API.
 *
 * @param {unknown} error
 * @param {{ mapUrl?: string, onNetwork?: () => void, quiet?: boolean }} options
 * @returns {Promise<string>} kode galat yang ditangani
 */
export async function handleApiError(error, { mapUrl = url('peta'), onNetwork = null, quiet = false } = {}) {
  if (!(error instanceof ApiError)) {
    if (!quiet) toast(t('errUnknown'), 'bad');
    console.error(error); // galat pemrograman (bukan data anak), untuk konsol pengembang
    return 'UNKNOWN';
  }

  switch (error.code) {
    case 'INVALID_SESSION': {
      if (sessionModalOpen) break;
      sessionModalOpen = true;
      const { showModal } = await import('./modal.js');
      await showModal({
        type: 'info',
        title: t('sessionTitle'),
        text: t('sessionExpired'),
        dismissible: false,
        buttons: [{ label: t('loginAgain'), style: 'primary', value: 'login' }],
      });
      window.location.href = url('masuk');
      break;
    }

    case 'CSRF_EXPIRED': {
      // Masih login, tetapi token halaman ini sudah tidak berlaku (mis. masuk ulang di tab lain)
      if (sessionModalOpen) break;
      sessionModalOpen = true;
      const { showModal } = await import('./modal.js');
      await showModal({
        type: 'info',
        title: t('pageExpiredTitle'),
        text: error.message,
        dismissible: false,
        buttons: [{ label: t('reloadPage'), style: 'primary', value: 'reload' }],
      });
      window.location.reload();
      break;
    }

    case 'PASSWORD_CHANGE_REQUIRED':
      window.location.href = url('ganti-sandi');
      break;

    case 'LEVEL_LOCKED':
      toast(error.message, 'warn');
      setTimeout(() => { window.location.href = mapUrl; }, 1200);
      break;

    case 'ATTEMPT_CLOSED':
      window.location.reload();
      break;

    case 'RATE_LIMITED':
      if (!quiet) toast(t('errRateLimited'), 'warn');
      break;

    case 'NETWORK':
      if (onNetwork) onNetwork();
      else if (!quiet) toast(t('errNetwork'), 'bad');
      break;

    default:
      if (!quiet) toast(error.message, 'bad', error.requestId);
  }

  return error.code;
}
