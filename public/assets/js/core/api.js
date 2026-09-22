/**
 * Satu pintu untuk seluruh permintaan ke server.
 * Bentuk respons server: { success, data | code + message, request_id }.
 */
import { CONFIG } from './config.js';

const RETRY_DELAYS = [2000, 4000, 8000]; // RATE_LIMITED: backoff 2s, 4s, 8s

export class ApiError extends Error {
  constructor(code, message, status, payload) {
    super(message);
    this.name = 'ApiError';
    this.code = code;
    this.status = status;
    this.payload = payload;
    this.requestId = payload?.request_id ?? null;
  }
}

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

export async function apiRequest(path, { method = 'GET', body = null, keepalive = false } = {}) {
  const headers = { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
  if (body !== null) headers['Content-Type'] = 'application/json';
  if (method !== 'GET') headers['X-CSRF-TOKEN'] = CONFIG.csrfHash;

  for (let attempt = 0; ; attempt++) {
    let res;
    try {
      res = await fetch(`${CONFIG.apiBase}${path}`, {
        method,
        headers,
        credentials: 'same-origin',
        body: body !== null ? JSON.stringify(body) : null,
        keepalive,
      });
    } catch {
      throw new ApiError('NETWORK', 'Gagal menghubungi server.', 0, null);
    }

    let json = null;
    try {
      json = await res.json();
    } catch {
      json = null;
    }

    if (res.status === 429 && attempt < RETRY_DELAYS.length) {
      await sleep(RETRY_DELAYS[attempt]);
      continue;
    }

    if (!res.ok || !json || json.success === false) {
      throw new ApiError(
        json?.code || (res.ok ? 'INVALID_RESPONSE' : 'NETWORK'),
        json?.message || 'Gagal menghubungi server.',
        res.status,
        json,
      );
    }

    return json.data;
  }
}
