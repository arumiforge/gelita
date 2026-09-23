/**
 * Dialog pembuka wilayah (/dialog/{code}).
 *
 * Tokoh yang berbicara diberi .is-speaking (maju dan terang), yang mendengar
 * .is-listening. Tanpa JavaScript CSS :target sudah menentukan hal yang sama;
 * kelas ini memastikan keadaannya benar juga saat slide diganti lewat
 * keyboard. Event `dialogue_advanced` { index, total, context: 'region' }.
 */
import { $, $$ } from '../core/dom.js';
import { emit } from '../core/events.js';
import { initSlides } from './slides.js';

export function initDialogue() {
  const screen = $('[data-screen="dialogue"]');
  if (!screen) return;

  const levelId = Number(screen.dataset.levelId) || null;
  const characters = $$('.dialogue-stage .character', screen);

  const cast = (speaker) => {
    for (const figure of characters) {
      const speaking = figure.dataset.character === speaker;
      figure.classList.toggle('is-speaking', speaking);
      figure.classList.toggle('is-listening', !speaking);
    }
  };

  const slides = initSlides({
    list: $('ol.dialogue-lines', screen),
    prefix: 'line-',
    onChange: (index, slide, total) => {
      cast(slide.dataset.character);
      screen.dataset.index = String(index);
      emit('dialogue_advanced', {
        levelId,
        payload: { index, total, context: 'region', character: slide.dataset.character || null },
      });
    },
  });

  const first = slides ? $$('ol.dialogue-lines > .slide', screen)[slides.current - 1] : null;
  cast(first?.dataset.character || screen.dataset.first);
}
