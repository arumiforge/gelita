/**
 * Installed app (PWA): register the service worker and offer installation.
 *
 * - The worker is registered at the base URL, so its scope is the whole app.
 *   Registration may fail; the game never depends on it. Normally that is
 *   sw.js (offline page + asset package, docs/rencana-paket-aset.md §4.1).
 *   With the server kill switch `gelita.paket.aktif = false` the boot script
 *   (core/package-boot.js) reports `killed`, and the empty sw-kosong.js is
 *   registered instead, which takes the worker out of every request (§11.1).
 * - Chrome/Android: `beforeinstallprompt` is held back, and the "Pasang GELITA"
 *   button on the welcome page fires it. The button only shows on touch
 *   devices that are not already running GELITA as an installed app. The
 *   installed app is standalone with no fixed orientation (Xiaomi HyperOS
 *   will not start it otherwise); the rotate screen asks for landscape.
 * - iPhone/iPad have no such event, so they get the hint
 *   Share → Add to Home Screen.
 */
const INSTALLED = '(display-mode: fullscreen), (display-mode: standalone)';

export function initInstall() {
  const base = document.body.dataset.base || '/';
  if ('serviceWorker' in navigator) {
    const script = window.GELITA_PACKAGE_BOOT?.killed ? 'sw-kosong.js' : 'sw.js';
    navigator.serviceWorker.register(`${base}${script}`, { scope: base }).catch(() => {});
  }

  const box = document.querySelector('[data-install]');
  if (!box) return;

  const installed = window.matchMedia(INSTALLED).matches || navigator.standalone === true;
  const touch = window.matchMedia('(pointer: coarse)').matches;
  if (installed || !touch) return;

  // Safari iOS/iPadOS: navigator.standalone only exists there
  if ('standalone' in navigator) {
    box.querySelector('[data-install-ios]').hidden = false;
    box.hidden = false;
    return;
  }

  const button = box.querySelector('[data-install-button]');
  const hint = box.querySelector('[data-install-hint]');
  let deferred = null;

  window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferred = event;
    button.hidden = false;
    hint.hidden = false;
    box.hidden = false;
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
