/**
 * Titik masuk halaman permainan.
 * Tahap 2: inisialisasi lapisan inti. Perilaku halaman (peta, dialog, tantangan)
 * dimuat per halaman pada tahap 6.
 */
import { CONFIG } from './core/config.js';
import { resendQueued } from './core/events.js';
import { Storage } from './core/storage.js';
import { Sfx } from './core/audio.js';

document.documentElement.classList.add('js');

// Audio baru boleh berbunyi setelah interaksi pertama
const unlock = () => {
  Sfx.unlock();
  window.removeEventListener('pointerdown', unlock);
  window.removeEventListener('keydown', unlock);
};
window.addEventListener('pointerdown', unlock);
window.addEventListener('keydown', unlock);

// Kirim ulang event yang tertunda saat offline
if (Storage.size() > 0) resendQueued();
window.addEventListener('online', () => resendQueued());

document.body.dataset.ready = CONFIG.locale;
