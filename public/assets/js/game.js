/**
 * Titik masuk halaman permainan.
 *
 * Lapisan inti selalu aktif (audio, antrean event, HUD); perilaku khusus
 * halaman dimuat sesuai `data-screen` atau form yang ada, lewat dynamic
 * import agar tiap halaman hanya memuat yang dipakainya. Halaman tetap
 * terbaca dan dapat dipakai bila modul ini gagal dimuat (aturan 9 docs/05).
 */
import { CONFIG, setAssetsLocal } from './core/config.js';
import { resendQueued, initVisibilityTracking } from './core/events.js';
import { Sfx, initAudioPlayers } from './core/audio.js';
import { initCurtains } from './core/curtain.js';
import { initHud } from './game/hud.js';
import { initRotateGate } from './game/rotate-gate.js';
import { initInstall } from './game/install.js';
import { initStage } from './core/stage.js';
import { initNextAssets } from './game/next-assets.js';

document.documentElement.classList.add('js');

// Touch basics (display plan Phase F1/F3.5):
// - iOS applies :active press feedback only when a touchstart listener exists.
// - Scene, map, and character images are not draggable (no ghost image on a
//   long press or a mouse drag); images swapped in later are covered by the
//   dragstart guard.
// - iOS zooms the page when a field below 16 px gets focus, and stage inputs
//   are 20 dp (about 11 px on a phone). `maximum-scale=1` stops that focus
//   zoom on iOS while pinch zoom still works there; Android would lose pinch
//   zoom, so it does not get the flag.
const NO_DRAG = '.scene-img, .scene-bg, .map-img, .character-img';
document.addEventListener('touchstart', () => {}, { passive: true });
document.querySelectorAll(NO_DRAG).forEach((img) => { img.draggable = false; });
document.addEventListener('dragstart', (event) => {
  if (event.target instanceof Element && event.target.matches(NO_DRAG)) event.preventDefault();
});
if (isIos()) {
  const meta = document.querySelector('meta[name="viewport"]');
  if (meta && !/maximum-scale/.test(meta.content)) meta.content = `${meta.content}, maximum-scale=1`;
}

function isIos() {
  const ua = navigator.userAgent || '';
  // iPadOS reports itself as a Mac; touch points tell it apart
  return /iPhone|iPad|iPod/.test(ua) || (/Macintosh/.test(ua) && navigator.maxTouchPoints > 1);
}

// Panggung 16:9: ukuran layar, tombol layar penuh, viewport_changed (Dok 3 §7)
initStage({ track: Boolean(CONFIG.sessionTag) });

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
  // Pindah tab/aplikasi (page_hidden/visible)
  initVisibilityTracking();
}

initHud();
initRotateGate();
initInstall();
initCurtains();

// Asset package (docs/rencana-paket-aset.md Stage 2): only pages rendered with a
// package load the package module; with package mode off nothing changes.
if (window.GELITA_PACKAGE_BOOT?.code) {
  import('./core/package.js').then((m) => m.initPackage({ setAssetsLocal })).catch(() => {});
}

const screen = document.querySelector('[data-screen]')?.dataset.screen ?? '';
const attemptId = Number(document.querySelector('[data-screen="challenge"]')?.dataset.attempt) || null;

initAudioPlayers(document, {
  attemptId,
  attention: ['dialogue', 'region-done', 'intro', 'ending', 'mission-brief'].includes(screen),
});

const pages = {
  intro: () => import('./game/intro.js').then((m) => m.initStory('intro')),
  ending: () => import('./game/intro.js').then((m) => m.initStory('ending')),
  dialogue: () => import('./game/dialogue.js').then((m) => m.initDialogue()),
  'region-done': () => import('./game/dialogue.js').then((m) => m.initDialogue()),
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

// On-screen keyboard: keep the focused field visible (Phase F3.4)
if (document.querySelector('#stage form input:not([type="hidden"]), #stage form textarea')) {
  import('./core/keyboard.js').then((m) => m.initKeyboardShift());
}

// Character pose frames (core/sprite.js), only on pages that have them
if (document.querySelector('.character[data-frames]')) {
  import('./core/sprite.js').then((m) => m.initSprites());
}

document.body.dataset.ready = CONFIG.locale;
// Penanda perilaku halaman sudah terpasang (dipakai uji ujung ke ujung)
page.then(() => { document.body.dataset.pageReady = '1'; });

// Decode the next screen's images while idle ([data-next-assets], §4.5)
initNextAssets({ ready: page });
