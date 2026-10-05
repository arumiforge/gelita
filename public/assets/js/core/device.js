/**
 * Sinyal perangkat dan tampilan layar untuk data penelitian (Dok 1 §11–§12).
 *
 *   deviceSignals()      → isi <input name="device_json"> formulir masuk dan
 *                          langkah 3 pendaftaran (ukuran layar, sentuh,
 *                          Client Hints navigator.userAgentData, …). Server
 *                          mengurainya bersama User-Agent (DeviceProfile).
 *   initScreenTracking() → event `viewport_changed` (saat halaman dibuka dan
 *                          setiap ukuran/orientasi berubah; debounce 1 detik,
 *                          paling sering satu event per 5 detik) dan
 *                          `page_hidden` / `page_visible`.
 *   trackReading()       → event `reading_progress` saat panel bacaan
 *                          digulir melewati 25/50/75/100 %.
 *   overlayOpened()      → event `overlay_opened`.
 *
 * Tidak ada sidik jari peramban (canvas, font, audio).
 */
import { $$ } from './dom.js';
import { emit } from './events.js';

const ROTATE_QUERY = '(orientation: portrait) and (pointer: coarse)';
const DEVICE_JSON_MAX = 4000;

export async function deviceSignals() {
  const ua = navigator.userAgentData;
  let uaData = null;
  try {
    uaData = ua ? await ua.getHighEntropyValues(
      ['platform', 'platformVersion', 'model', 'architecture', 'bitness', 'fullVersionList', 'mobile']) : null;
  } catch { /* tidak didukung: biarkan null */ }
  return {
    screen_w: screen.width, screen_h: screen.height,
    viewport_w: innerWidth, viewport_h: innerHeight,
    pixel_ratio: Math.round((devicePixelRatio || 1) * 100) / 100,
    touch_points: navigator.maxTouchPoints || 0,
    orientation: screen.orientation?.type || null,
    is_standalone: matchMedia('(display-mode: standalone)').matches || navigator.standalone === true,
    language: navigator.language || null,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone || null,
    ua_data: uaData,
  };
}

/**
 * Mengisi `device_json` sesaat sebelum formulir dikirim. Formulir ditahan
 * paling lama 800 ms menunggu getHighEntropyValues(); bila gagal, formulir
 * tetap terkirim tanpa sinyal (server memakai User-Agent saja).
 *
 * Pasang SEBELUM penjaga kirim-ganda: kiriman pertama dihentikan di sini
 * (stopImmediatePropagation), lalu dikirim ulang lewat requestSubmit().
 */
export function attachDeviceField(form) {
  const field = form?.querySelector('input[name="device_json"]');
  if (!field) return;
  let ready = false;

  form.addEventListener('submit', async (event) => {
    if (ready) return;
    event.preventDefault();
    event.stopImmediatePropagation();
    try {
      const signals = await Promise.race([
        deviceSignals(),
        new Promise((resolve) => setTimeout(() => resolve(null), 800)),
      ]);
      const json = signals ? JSON.stringify(signals) : '';
      field.value = json.length <= DEVICE_JSON_MAX ? json : JSON.stringify({ ...signals, ua_data: null });
    } catch {
      field.value = '';
    }
    ready = true;
    if (typeof form.requestSubmit === 'function') form.requestSubmit(event.submitter ?? undefined);
    else form.submit();
  });
}

export function overlayOpened(overlay, extra = {}) {
  emit('overlay_opened', { levelId: extra.levelId ?? null, payload: { overlay, ...(extra.level ? { level: extra.level } : {}) } });
}

/**
 * Kode layar Dok 1 §12.4 dari <body data-screen-code> (dirender layout dengan
 * resolver yang sama dengan screen_viewed di server). Pustaka terkunci
 * dikenali dari halamannya, seperti di ScreenViewFilter.
 */
const screenName = () => {
  const code = document.body?.dataset.screenCode || null;
  return code === 'library' && document.querySelector('[data-screen="library-locked"]') ? 'library_locked' : code;
};

export function initScreenTracking() {
  const rotate = window.matchMedia ? window.matchMedia(ROTATE_QUERY) : null;
  let timer = null;
  let lastSent = 0;
  let lastKey = '';

  const send = () => {
    timer = null;
    const payload = {
      w: innerWidth,
      h: innerHeight,
      orientation: screen.orientation?.type || (innerWidth >= innerHeight ? 'landscape' : 'portrait'),
      rotate_gate: Boolean(rotate?.matches),
    };
    const key = JSON.stringify(payload);
    if (key === lastKey) return;
    const wait = 5000 - (Date.now() - lastSent);
    if (lastSent && wait > 0) {
      timer = setTimeout(send, wait);
      return;
    }
    lastKey = key;
    lastSent = Date.now();
    emit('viewport_changed', { payload });
  };

  const schedule = () => {
    if (timer) clearTimeout(timer);
    timer = setTimeout(send, 1000);
  };

  send();
  window.addEventListener('resize', schedule);
  screen.orientation?.addEventListener?.('change', schedule);
  rotate?.addEventListener?.('change', schedule);

  document.addEventListener('visibilitychange', () => {
    emit(document.visibilityState === 'hidden' ? 'page_hidden' : 'page_visible', { payload: { screen: screenName() } });
  });
}

/**
 * Batas gulir panel bacaan (rumpang / benar–salah), masing-masing sekali per
 * pengerjaan. Panel yang tidak dapat digulir dianggap terbaca 100 % saat
 * tampil di layar.
 */
export function trackReading(section, refs = {}) {
  const panels = $$('.rumpang-reading, details.passage .passage-content, .passage-body', section)
    .filter((node, index, list) => !list.some((other) => other !== node && other.contains(node)));
  if (!panels.length) return;

  const marks = [25, 50, 75, 100];

  panels.forEach((panel) => {
    const passageId = Number(panel.closest('[data-passage]')?.dataset.passage) || null;
    const reached = new Set();

    const check = () => {
      const scrollable = panel.scrollHeight - panel.clientHeight > 4;
      let percent;
      if (scrollable) {
        percent = ((panel.scrollTop + panel.clientHeight) / panel.scrollHeight) * 100;
      } else {
        const rect = panel.getBoundingClientRect();
        const visible = Math.min(rect.bottom, innerHeight) - Math.max(rect.top, 0);
        percent = rect.height > 0 ? ((Math.max(0, rect.top < 0 ? -rect.top : 0) + Math.max(0, visible)) / rect.height) * 100 : 0;
      }
      for (const mark of marks) {
        if (percent + 0.5 >= mark && !reached.has(mark)) {
          reached.add(mark);
          emit('reading_progress', { ...refs, payload: { ...(passageId ? { passage_id: passageId } : {}), percent: mark } });
        }
      }
    };

    panel.addEventListener('scroll', check, { passive: true });
    window.addEventListener('scroll', check, { passive: true });
    setTimeout(check, 500);
  });
}
