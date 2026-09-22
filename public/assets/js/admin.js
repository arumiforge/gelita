/**
 * Titik masuk panel admin.
 * Tahap 2: inisialisasi lapisan inti. Filter, tabel, dan chart ditambahkan pada tahap 6.
 */
import { CONFIG } from './core/config.js';
import { toast } from './core/toast.js';

document.documentElement.classList.add('js');

document.addEventListener('click', (event) => {
  const trigger = event.target.closest('[data-demo-toast]');
  if (trigger) toast(`Toast berfungsi · locale ${CONFIG.locale}`, 'ok');
});

document.body.dataset.ready = CONFIG.locale;
