/**
 * Cerita pembuka (/intro): pemutar narasi sinematik mode `tap`
 * (game/narrator.js). Kartu "Ketuk untuk mulai" membuka kunci audio, lalu
 * narasi diputar otomatis; event `dialogue_advanced` { index, total,
 * context: 'intro' } tiap perpindahan slide.
 */
import { $ } from '../core/dom.js';
import { initNarrator } from './narrator.js';

export function initStory() {
  const screen = $('[data-screen="intro"][data-narrator]');
  if (!screen) return null;

  return initNarrator(screen);
}
