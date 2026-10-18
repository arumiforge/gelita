/**
 * "Siapkan GELITA" (`/siapkan`, docs/rencana-paket-aset.md Stage 3, §5.6,
 * §8.1–§8.7, P7, P11): downloads the full asset package (every part, both
 * languages) and enables Mulai only once it is verified on this device.
 * There is no skip button.
 *
 *   - Progress per part (§5.1 order) and in total, from download() onProgress.
 *   - Network or server trouble: finished files are kept; the screen tries
 *     again by itself every 10 s and as soon as the browser is back online,
 *     and offers Coba lagi.
 *   - Too little storage (§8.3, §8.4): the private-mode or "kurang ±n MB"
 *     text, Coba lagi, and "Main daring di perangkat ini" (reason storage).
 *   - Lab device wiped on logout (§8.2): "Main daring di perangkat ini" under
 *     "Untuk guru", only when the server allows online mode for lab devices.
 *   - The online choice lives in sessionStorage (core/package.js), so every
 *     page of this browser session records package_mode `daring`.
 *
 * `data-state` on the section: checking | downloading | error | ready | online.
 */
import { CONFIG, t } from '../core/config.js';
import { $, $$, sleep } from '../core/dom.js';
import * as Package from '../core/package.js';
import { errorText, formatMb, quotaTooSmall, retriesAlone } from './package-ui.js';

const AUTO_RETRY_MS = 10000;
const WORKER_WAIT_MS = 10000;

export function initPrepare(root = $('[data-screen="prepare"]')) {
  if (!root) return null;

  const ui = {
    status: $('[data-prepare-status]', root),
    bar: $('[data-prepare-bar]', root),
    size: $('[data-prepare-size]', root),
    start: $('[data-prepare-start]', root),
    retry: $('[data-prepare-retry]', root),
    storage: $('[data-prepare-online="storage"]', root),
    lab: $('[data-prepare-lab]', root),
  };
  const rows = new Map($$('[data-group]', root).map((li) => [li.dataset.group, {
    li,
    bar: $('.prepare-group-bar > i', li),
    state: $('[data-group-state]', li),
    bytes: Number(li.dataset.bytes) || 0,
  }]));
  const next = root.dataset.next || `${CONFIG.baseUrl}mulai`;

  let state = null;
  let controller = null;
  let running = false;
  let retryTimer = null;
  let progress = null;
  let frame = 0;

  const setState = (name) => { root.dataset.state = name; };
  const say = (text) => { if (ui.status.textContent !== text) ui.status.textContent = text; };

  // ------------------------------------------------------------ progress

  function paint() {
    frame = 0;
    if (!progress) return;
    const { totalBytes, doneBytes, groups } = progress;
    const pct = totalBytes > 0 ? Math.min(100, Math.round((doneBytes / totalBytes) * 100)) : 0;
    const sizeText = t('packageSizeOf', formatMb(doneBytes), formatMb(totalBytes));
    ui.bar.querySelector('i').style.width = `${pct}%`;
    ui.bar.setAttribute('aria-valuenow', String(pct));
    ui.bar.setAttribute('aria-valuetext', sizeText);
    ui.size.textContent = sizeText;

    for (const group of groups) {
      const row = rows.get(group.name);
      if (!row) continue;
      const share = group.totalBytes > 0 ? Math.round((group.doneBytes / group.totalBytes) * 100) : 100;
      row.bar.style.width = `${group.complete ? 100 : share}%`;
      row.li.classList.toggle('is-done', group.complete);
      row.state.textContent = group.complete
        ? t('packageGroupDone')
        : (group.doneBytes > 0
          ? t('packageSizeOf', formatMb(group.doneBytes), formatMb(group.totalBytes))
          : `${t('packageGroupWaiting')} · ${formatMb(group.totalBytes)}`);
    }
  }

  function onProgress(p) {
    progress = p;
    if (p.phase === 'activate') say(t('packageActivating'));
    if (!frame) frame = requestAnimationFrame(paint);
  }

  /** Every part complete (the device already holds the package). */
  function paintComplete() {
    const total = state?.server?.total_bytes || [...rows.values()].reduce((sum, row) => sum + row.bytes, 0);
    progress = {
      totalBytes: total,
      doneBytes: total,
      groups: [...rows.entries()].map(([name, row]) => ({ name, totalBytes: row.bytes, doneBytes: row.bytes, complete: true })),
    };
    paint();
  }

  // ------------------------------------------------------------ buttons

  function showActions({ retry = false, storage = false } = {}) {
    ui.retry.hidden = !retry;
    ui.storage.hidden = !(storage && Package.allowedOnlineReasons(state).includes(Package.ONLINE_REASONS.STORAGE));
    ui.lab.hidden = !(root.dataset.state !== 'ready' && root.dataset.state !== 'online'
      && Package.allowedOnlineReasons(state).includes(Package.ONLINE_REASONS.LAB));
  }

  function allowStart(text, name) {
    setState(name);
    say(text);
    showActions();
    ui.lab.hidden = true;
    ui.start.disabled = false;
    ui.start.focus({ preventScroll: true });
  }

  ui.start.addEventListener('click', () => {
    if (!ui.start.disabled) window.location.assign(next);
  });

  ui.retry.addEventListener('click', () => {
    // A worker that never became ready only recovers with a fresh page
    if (root.dataset.workerStuck === '1') window.location.reload();
    else run();
  });

  for (const button of $$('[data-prepare-online]', root)) {
    button.addEventListener('click', () => {
      if (!Package.chooseOnline(button.dataset.prepareOnline, state)) return;
      stopRetry();
      controller?.abort();
      allowStart(t('packageOnline'), 'online');
    });
  }

  // ------------------------------------------------------------ retry

  function stopRetry() {
    clearTimeout(retryTimer);
    retryTimer = null;
  }

  function scheduleRetry() {
    stopRetry();
    retryTimer = setTimeout(() => { retryTimer = null; run(); }, AUTO_RETRY_MS);
  }

  window.addEventListener('online', () => {
    if (root.dataset.state === 'error' && retryTimer) run();
  });

  function fail(error) {
    setState('error');
    const tooSmall = error?.code === Package.ERRORS.QUOTA && quotaTooSmall(state?.storage, state?.server?.total_bytes);
    say(errorText(error, { private: tooSmall }));
    showActions({ retry: true, storage: error?.code === Package.ERRORS.QUOTA });
    if (retriesAlone(error)) scheduleRetry();
  }

  // ------------------------------------------------------------ run

  /** After a download: the package is active, but is the worker that serves it ready? */
  async function confirmReady() {
    let after = await Package.check({ server: state.server });
    if (after.status === 'download' && after.device.complete && !after.support.serviceWorkerReady) {
      await Promise.race([navigator.serviceWorker.ready, sleep(WORKER_WAIT_MS)]).catch(() => null);
      after = await Package.check({ server: state.server });
    }
    return after;
  }

  async function run() {
    if (running) return;
    running = true;
    stopRetry();
    showActions();
    ui.start.disabled = true;
    if (root.dataset.state === 'error') {
      setState('checking');
      say(t('packageRetrying'));
    }

    try {
      state = await Package.check();
      showActions();

      // A running game session keeps its package mode (S8); off/unsupported/online/ready may start
      if (state.canStart || CONFIG.sessionTag) {
        if (state.status === 'ready') paintComplete();
        allowStart(state.status === 'online' ? t('packageOnline') : t('packageReady'), state.status === 'online' ? 'online' : 'ready');
        return;
      }

      setState('downloading');
      say(t('packageDownloading'));
      controller = new AbortController();
      await Package.download({ server: state.server, signal: controller.signal, onProgress });
      controller = null;

      const after = await confirmReady();
      state = after;
      if (after.canStart) {
        paintComplete();
        allowStart(t('packageReady'), 'ready');
        return;
      }
      root.dataset.workerStuck = '1';
      setState('error');
      say(t('packageErrWorker'));
      showActions({ retry: true });
    } catch (error) {
      controller = null;
      // Aborted because online mode was chosen: that path already updated the screen
      if (error?.code === Package.ERRORS.ABORTED) return;
      fail(error);
    } finally {
      running = false;
    }
  }

  run();
  return { run };
}
