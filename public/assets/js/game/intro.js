/**
 * Cerita pembuka (/intro): slide tanpa memuat ulang halaman + event
 * `dialogue_advanced` { index, total, context: 'intro' } tiap perpindahan.
 */
import { $ } from '../core/dom.js';
import { emit } from '../core/events.js';
import { initSlides } from './slides.js';

export function initStory(context = 'intro') {
  const screen = $('[data-screen="intro"]');
  if (!screen) return;

  initSlides({
    list: $('ol.slides', screen),
    prefix: 'slide-',
    onChange: (index, slide, total) => {
      emit('dialogue_advanced', { payload: { index, total, context, character: slide.dataset.character || null } });
    },
  });
}
