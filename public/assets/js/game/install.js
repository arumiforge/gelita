/**
 * Installed app (PWA): register the service worker and offer installation.
 *
 * - The worker is registered at the base URL, so its scope is the whole app.
 *   Registration may fail; the game never depends on it. Normally that is
 *   sw.js (offline page + asset package, docs/rencana-paket-aset.md §4.1).
 *   With the server kill switch `gelita.paket.aktif = false` the boot script
 *   (core/package-boot.js) reports `killed`, and the empty sw-kosong.js is
 *   registered instead, which takes the worker out of every request (§11.1).
 * - Chrome's install offer (`beforeinstallprompt`) is held back. It can fire
 *   before this module loads, so core/install-boot.js catches it first and
 *   leaves it in window.GELITA_INSTALL_PROMPT.
 * - Welcome page (`data-install="modal"`, owner 18 October 2026): on a phone
 *   or tablet that is not already running GELITA as an installed app, a
 *   dialog over the blurred stage offers the install first, once per browser
 *   session ("Nanti saja" or closing it waits until the next session). With
 *   Chrome's offer it has a "Pasang sekarang" button; without it (offer not
 *   given yet, already installed, another browser) it explains the browser
 *   menu; iPhone/iPad get Share → Add to Home Screen. The "Pasang GELITA"
 *   button under Mulai stays and opens the same dialog. On a computer the
 *   button only appears with Chrome's offer, and the dialog never opens by
 *   itself.
 * - Other pages with `[data-install]` (Siapkan GELITA) keep the inline hint:
 *   a button that fires Chrome's offer, or the iPhone/iPad hint.
 * - The installed app is standalone with no fixed orientation (Xiaomi
 *   HyperOS will not start it otherwise); the rotate screen asks for
 *   landscape, and the dialog waits until the device is turned.
 */
import { showModal, closeModal, isModalOpen } from '../core/modal.js';

const INSTALLED = '(display-mode: fullscreen), (display-mode: standalone)';
const ROTATE = '(orientation: portrait) and (pointer: coarse)';
// "Nanti saja" for this browser session only, so the offer comes back next time
const LATER_KEY = 'gelita.install.later';
// How long a phone waits for Chrome's offer before explaining the browser menu
const OFFER_WAIT_MS = 2500;

export function initInstall() {
  const base = document.body.dataset.base || '/';
  if ('serviceWorker' in navigator) {
    const script = window.GELITA_PACKAGE_BOOT?.killed ? 'sw-kosong.js' : 'sw.js';
    navigator.serviceWorker.register(`${base}${script}`, { scope: base }).catch(() => {});
  }

  const box = document.querySelector('[data-install]');
  if (!box) return;

  const installed = window.matchMedia(INSTALLED).matches || navigator.standalone === true;
  if (installed) return;

  const touch = window.matchMedia('(pointer: coarse)').matches;
  if (box.dataset.install === 'modal') {
    initInstallDialog(box, touch);
  } else if (touch) {
    initInstallHint(box);
  }
}

/** Welcome page: the install dialog and the "Pasang GELITA" button that reopens it. */
function initInstallDialog(box, touch) {
  // Safari iOS/iPadOS: navigator.standalone only exists there
  const ios = 'standalone' in navigator;
  const button = box.querySelector('[data-install-button]');
  const copy = box.dataset;
  let deferred = window.GELITA_INSTALL_PROMPT || null;
  let open = false;

  const showButton = () => {
    button.hidden = false;
    box.hidden = false;
  };

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferred = event;
    showButton();
  });
  window.addEventListener('appinstalled', () => {
    deferred = null;
    box.hidden = true;
    if (open) closeModal(null);
  });

  async function offer() {
    if (open) return;
    open = true;
    let options;
    if (deferred) {
      options = {
        text: copy.installText,
        buttons: [
          { label: copy.installLater, style: 'quiet', value: 'later' },
          { label: copy.installNow, style: 'primary', value: 'install' },
        ],
      };
    } else {
      options = {
        text: [copy.installText, ios ? copy.installIos : copy.installMenu, copy.installAlready],
        buttons: [{ label: copy.installOk, style: 'primary', value: 'ok' }],
      };
    }

    const value = await showModal({ type: 'info', icon: 'install', title: copy.installTitle, blur: true, ...options });
    open = false;

    if (value === 'install' && deferred) {
      // Still inside the tap's user activation, which prompt() needs
      const prompt = deferred;
      deferred = null;
      prompt.prompt();
      const choice = await prompt.userChoice.catch(() => null);
      if (choice?.outcome === 'accepted') {
        box.hidden = true;
        return;
      }
    }
    rememberLater();
  }

  button.addEventListener('click', offer);

  // A computer: only Chrome's offer shows the button; nothing opens by itself
  if (!touch) {
    if (deferred) showButton();
    return;
  }

  showButton();
  if (laterThisSession()) return;

  const ready = deferred || ios ? Promise.resolve() : waitForOffer(() => deferred, OFFER_WAIT_MS);
  ready.then(whenLandscape).then(() => {
    if (!box.hidden && !isModalOpen()) offer();
  });
}

/** Other pages: the inline button (Chrome's offer) or the iPhone/iPad hint. */
function initInstallHint(box) {
  if ('standalone' in navigator) {
    box.querySelector('[data-install-ios]').hidden = false;
    box.hidden = false;
    return;
  }

  const button = box.querySelector('[data-install-button]');
  const hint = box.querySelector('[data-install-hint]');
  let deferred = null;

  const show = (event) => {
    deferred = event;
    button.hidden = false;
    hint.hidden = false;
    box.hidden = false;
  };

  if (window.GELITA_INSTALL_PROMPT) show(window.GELITA_INSTALL_PROMPT);
  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    show(event);
  });

  button.addEventListener('click', async () => {
    if (!deferred) return;
    const prompt = deferred;
    deferred = null;
    prompt.prompt();
    await prompt.userChoice.catch(() => null);
    box.hidden = true;
  });

  window.addEventListener('appinstalled', () => { box.hidden = true; });
}

/** Resolves once Chrome's offer is held, or after `ms` without it. */
function waitForOffer(held, ms) {
  return new Promise((resolve) => {
    if (held()) {
      resolve();
      return;
    }
    const done = () => {
      clearTimeout(timer);
      window.removeEventListener('beforeinstallprompt', done);
      resolve();
    };
    const timer = setTimeout(done, ms);
    window.addEventListener('beforeinstallprompt', done);
  });
}

/** Resolves when the rotate screen is not covering the stage. */
function whenLandscape() {
  const mq = window.matchMedia(ROTATE);
  if (!mq.matches) return Promise.resolve();
  return new Promise((resolve) => {
    const change = () => {
      if (mq.matches) return;
      mq.removeEventListener('change', change);
      resolve();
    };
    mq.addEventListener('change', change);
  });
}

function laterThisSession() {
  try {
    return window.sessionStorage.getItem(LATER_KEY) === '1';
  } catch {
    return false;
  }
}

function rememberLater() {
  try {
    window.sessionStorage.setItem(LATER_KEY, '1');
  } catch {
    // Private mode: the dialog may come back on the next page load
  }
}
