/**
 * Aplikasi terpasang (PWA): daftarkan service worker dan tawarkan pemasangan.
 *
 * - sw.js didaftarkan di base URL, jadi lingkupnya seluruh aplikasi. Kalau
 *   gagal didaftarkan tidak masalah, karena permainan tidak bergantung padanya.
 * - Chrome/Android: `beforeinstallprompt` ditahan, lalu tombol "Pasang GELITA"
 *   di halaman welcome yang memicunya. Tombol hanya muncul di perangkat sentuh
 *   yang belum menjalankan GELITA sebagai aplikasi terpasang. Setelah
 *   terpasang, manifest mengunci posisi mendatar dan layar penuh.
 * - iPhone/iPad tidak punya event itu, jadi yang tampil petunjuk
 *   Bagikan → Tambah ke Layar Utama. iOS tidak mengunci rotasi, sehingga layar
 *   putar tetap berlaku di sana.
 */
const INSTALLED = '(display-mode: fullscreen), (display-mode: standalone)';

export function initInstall() {
  const base = document.body.dataset.base || '/';
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register(`${base}sw.js`, { scope: base }).catch(() => {});
  }

  const box = document.querySelector('[data-install]');
  if (!box) return;

  const installed = window.matchMedia(INSTALLED).matches || navigator.standalone === true;
  const touch = window.matchMedia('(pointer: coarse)').matches;
  if (installed || !touch) return;

  // Safari iOS/iPadOS: navigator.standalone hanya ada di sana
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
