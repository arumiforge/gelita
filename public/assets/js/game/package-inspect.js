/**
 * "Periksa paket" (`/periksa-paket`, docs/rencana-paket-aset.md Stage 3,
 * §5.5, §6.3, §8.1, §11.2): the teacher's check of this device before class.
 *
 *   Periksa semua berkas   verify(): every cached file hashed again (sha256);
 *                          missing or broken files are named, broken ones
 *                          removed, and the package is no longer active
 *   Unduh yang kurang      download(): fetches only what is not in the cache
 *                          (also a newer package's changed files)
 *   Simpan permanen        persist() (Firefox asks the teacher)
 *   Hapus paket di perangkat ini   clear(), after a confirmation
 *
 * `data-state` on the section: loading | idle | busy.
 */
import { t } from '../core/config.js';
import { $, $$ } from '../core/dom.js';
import { confirmDialog } from '../core/modal.js';
import * as Package from '../core/package.js';
import { errorText, formatCount, formatDate, formatMb, quotaTooSmall } from './package-ui.js';

const KEYS_SHOWN = 8;

export function initPackageInspect(root = $('[data-screen="package-inspect"]')) {
  if (!root) return null;

  const field = (name) => $(`[data-inspect="${name}"]`, root);
  const buttons = Object.fromEntries($$('[data-inspect-action]', root).map((b) => [b.dataset.inspectAction, b]));
  const bar = $('[data-inspect-bar]', root);
  const status = $('[data-inspect-status]', root);
  const keys = $('[data-inspect-keys]', root);

  let state = null;
  let marker = null;

  const say = (text) => { status.textContent = text; };
  const showKeys = (list) => {
    keys.hidden = list.length === 0;
    const shown = list.slice(0, KEYS_SHOWN).join(', ');
    keys.textContent = list.length ? t('inspectVerifyKeys', list.length > KEYS_SHOWN ? `${shown}, …` : shown) : '';
  };
  const showBar = (done, total) => {
    const pct = total > 0 ? Math.min(100, Math.round((done / total) * 100)) : 0;
    bar.hidden = false;
    bar.querySelector('i').style.width = `${pct}%`;
    bar.setAttribute('aria-valuenow', String(pct));
  };

  function stateText() {
    if (!state) return t('inspectUnknown');
    switch (state.status) {
      case 'off': return t('inspectStateOff');
      case 'unsupported': return t('inspectStateUnsupported');
      case 'online': return t('inspectStateOnline');
      case 'ready': return t('inspectStateReady');
      default:
        if (state.device.complete && !state.support.serviceWorkerReady) return t('inspectStateWorker');
        if (state.device.package_code && state.device.package_code !== state.server.package_code) return t('inspectStateOld');
        return t('inspectStateDownload');
    }
  }

  function paint() {
    field('state').textContent = stateText();
    field('device').textContent = marker
      ? t('inspectDeviceValue', marker.package_code, formatCount(marker.file_count), formatMb(marker.total_bytes), formatDate(marker.activated_at))
      : t('inspectNone');
    const server = state?.server;
    field('server').textContent = !server ? t('inspectUnknown')
      : (server.package_code ? t('inspectServerValue', server.package_code, formatCount(server.file_count), formatMb(server.total_bytes)) : t('inspectServerOff'));
    const storage = state?.storage;
    field('storage').textContent = storage && storage.quota !== null && storage.usage !== null
      ? t('inspectStorageValue', formatMb(storage.usage), formatMb(storage.quota))
      : t('inspectUnknown');
    field('persisted').textContent = storage?.persisted === true ? t('inspectYes') : (storage?.persisted === false ? t('inspectNo') : t('inspectUnknown'));
  }

  function enable() {
    const caps = Package.support();
    buttons.verify.disabled = !marker;
    buttons.repair.disabled = !(caps.download && state?.server?.package_code);
    buttons.persist.disabled = !(typeof navigator.storage?.persist === 'function' && state?.storage?.persisted !== true);
    buttons.clear.disabled = !caps.cacheStorage;
  }

  async function refresh() {
    marker = await Package.activePackage();
    try {
      state = await Package.check();
    } catch (error) {
      state = null;
      say(errorText(error));
    }
    paint();
    enable();
    root.dataset.state = 'idle';
    root.removeAttribute('aria-busy');
  }

  /** Runs one button's task with every button disabled; the result text stays. */
  async function busy(task) {
    root.dataset.state = 'busy';
    root.setAttribute('aria-busy', 'true');
    Object.values(buttons).forEach((button) => { button.disabled = true; });
    showKeys([]);
    try {
      await task();
    } catch (error) {
      say(errorText(error, { private: error?.code === Package.ERRORS.QUOTA && quotaTooSmall(state?.storage, state?.server?.total_bytes) }));
    }
    bar.hidden = true;
    await refresh();
  }

  buttons.verify.addEventListener('click', () => busy(async () => {
    const result = await Package.verify({
      onProgress: ({ doneFiles, totalFiles }) => {
        showBar(doneFiles, totalFiles);
        say(t('inspectVerifying', formatCount(doneFiles), formatCount(totalFiles)));
      },
    });
    if (!result.package_code) say(t('inspectVerifyNone'));
    else if (result.ok) say(t('inspectVerifyOk', formatCount(result.files)));
    else {
      say(t('inspectVerifyBad', formatCount(result.missing.length), formatCount(result.corrupt.length)));
      showKeys([...result.missing, ...result.corrupt]);
    }
  }));

  buttons.repair.addEventListener('click', () => busy(async () => {
    const result = await Package.download({
      server: state?.server,
      onProgress: ({ doneFiles, totalFiles }) => {
        showBar(doneFiles, totalFiles);
        say(t('inspectRepairing', formatCount(doneFiles), formatCount(totalFiles)));
      },
    });
    say(t('inspectRepairDone', formatCount(result.downloadedFiles), formatCount(result.skippedFiles)));
  }));

  buttons.persist.addEventListener('click', () => busy(async () => {
    say((await Package.persist()) === true ? t('inspectPersistOk') : t('inspectPersistNo'));
  }));

  buttons.clear.addEventListener('click', async () => {
    const sure = await confirmDialog({
      title: t('inspectClearTitle'),
      text: t('inspectClearText'),
      confirmLabel: t('inspectClearConfirm'),
      danger: true,
    });
    if (!sure) {
      buttons.clear.focus();
      return;
    }
    await busy(async () => {
      await Package.clear();
      say(t('inspectClearDone'));
    });
  });

  refresh();
  return { refresh };
}
