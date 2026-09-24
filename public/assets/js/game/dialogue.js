/**
 * Adegan dialog Jaka & Mbah Kedu: dialog pembuka wilayah (/dialog/{code},
 * `level_open`) dan wilayah tuntas (/tuntas/{code}, `level_done`), markup
 * partials/dialogue-scene.
 *
 * Pemutarnya game/narrator.js mode `tap` (kartu bab / kartu "Serpihan …
 * kembali!", audio otomatis, mesin ketik, efek baris, maju otomatis).
 * narrator.js satu-satunya pengirim `dialogue_advanced` { index, total,
 * context: 'level_open' | 'level_done' }; modul ini hanya memakai `onSlide`:
 *
 * - tokoh yang berbicara diberi .is-speaking (maju dan terang), yang
 *   mendengar .is-listening. Tanpa JavaScript CSS :target sudah menentukan
 *   hal yang sama;
 * - gambar tokoh yang berbicara ditukar ke frame pose baris itu
 *   (`data-pose-src` dari server, sudah jatuh ke `idle`). Baris tanpa frame
 *   sama sekali membiarkan tokoh apa adanya (monogram).
 */
import { $, $$ } from '../core/dom.js';
import { initNarrator } from './narrator.js';

/** Frame pose dipramuat sekali agar pergantian gambar tidak berkedip. */
function preloadPoses(lines) {
  const urls = new Set(lines.map((line) => line.dataset.poseSrc).filter(Boolean));
  const run = () => urls.forEach((src) => { const img = new Image(); img.decoding = 'async'; img.src = src; });
  if ('requestIdleCallback' in window) window.requestIdleCallback(run, { timeout: 1500 });
  else setTimeout(run, 400);
}

function setPose(figure, pose, src) {
  if (!figure) return;
  figure.className = figure.className.replace(/\bpose-[a-z]+\b/g, '').trim();
  figure.classList.add(`pose-${pose || 'idle'}`);
  figure.dataset.pose = pose || 'idle';
  if (!src) return;

  let img = $('.character-img', figure);
  if (!img) {
    // Monogram diganti gambar begitu ada frame pose yang diunggah
    img = document.createElement('img');
    img.className = 'character-img';
    img.alt = '';
    img.width = 700;
    img.height = 900;
    $('.character-fallback', figure)?.replaceWith(img);
    if (!img.isConnected) figure.prepend(img);
  }
  if (img.getAttribute('src') !== src) img.src = src;
}

export function initDialogue() {
  const screen = $('[data-screen="dialogue"], [data-screen="region-done"]');
  if (!screen) return null;

  const figures = $$('.dialogue-stage .character', screen);
  const lines = $$('ol.dialogue-lines > .slide', screen);

  const cast = (line) => {
    if (!line) return;
    const speaker = line.dataset.character;
    for (const figure of figures) {
      const speaking = figure.dataset.character === speaker;
      figure.classList.toggle('is-speaking', speaking);
      figure.classList.toggle('is-listening', !speaking);
      if (speaking) setPose(figure, line.dataset.pose, line.dataset.poseSrc);
    }
    screen.dataset.index = line.dataset.index || '1';
  };

  preloadPoses(lines);

  const story = initNarrator(screen, { onSlide: (index, line) => cast(line) });
  cast(story ? lines[story.current - 1] : lines[0]);

  return story;
}
