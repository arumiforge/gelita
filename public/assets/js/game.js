/**
 * Titik masuk halaman permainan.
 *
 * Lapisan inti selalu aktif (audio, antrean event, HUD); perilaku khusus
 * halaman dimuat sesuai `data-screen` atau form yang ada, lewat dynamic
 * import agar tiap halaman hanya memuat yang dipakainya. Halaman tetap
 * terbaca dan dapat dipakai bila modul ini gagal dimuat (aturan 9 docs/05).
 */
import { CONFIG } from './core/config.js';
import { resendQueued } from './core/events.js';
import { Sfx, initAudioPlayers } from './core/audio.js';
import { initHud } from './game/hud.js';
import { initRotateGate } from './game/rotate-gate.js';
import { initInstall } from './game/install.js';

document.documentElement.classList.add('js');

// Audio baru boleh berbunyi setelah interaksi pertama
const unlock = () => {
  Sfx.unlock();
  window.removeEventListener('pointerdown', unlock);
  window.removeEventListener('keydown', unlock);
};
window.addEventListener('pointerdown', unlock);
window.addEventListener('keydown', unlock);

// Kirim ulang event milik sesi ini yang tertunda saat offline
if (CONFIG.sessionTag) {
  resendQueued();
  window.addEventListener('online', () => resendQueued());
}

initHud();
initRotateGate();
initInstall();

const screen = document.querySelector('[data-screen]')?.dataset.screen ?? '';
const attemptId = Number(document.querySelector('[data-screen="challenge"]')?.dataset.attempt) || null;

initAudioPlayers(document, {
  attemptId,
  attention: ['dialogue', 'intro', 'mission-brief'].includes(screen),
});

const pages = {
  intro: () => import('./game/intro.js').then((m) => m.initStory('intro')),
  dialogue: () => import('./game/dialogue.js').then((m) => m.initDialogue()),
  'map-kedu': () => import('./game/map.js').then((m) => m.initMap()),
  'map-level': () => import('./game/map.js').then((m) => m.initMap()),
  library: () => import('./game/library.js').then((m) => m.initLibrary()),
  'library-index': () => import('./game/library.js').then((m) => m.initLibraryIndex()),
  reflection: () => import('./game/reflection.js').then((m) => m.initReflection()),
  challenge: () => import('./game/challenge.js').then((m) => m.bootChallenge()),
  finished: () => import('./game/finished.js').then((m) => m.initFinished()),
};

let page = Promise.resolve();
if (pages[screen]) {
  page = pages[screen]();
} else if (document.querySelector('form.register-form')) {
  page = import('./game/register.js').then((m) => m.initRegister());
} else if (document.querySelector('[data-login-form]')) {
  page = import('./game/login.js').then((m) => m.initLogin());
} else if (document.querySelector('.password-field')) {
  page = import('./game/password-meter.js').then((m) => m.initPasswordField(document.querySelector('.password-field')));
}

document.body.dataset.ready = CONFIG.locale;
// Penanda perilaku halaman sudah terpasang (dipakai uji ujung ke ujung)
page.then(() => { document.body.dataset.pageReady = '1'; });
