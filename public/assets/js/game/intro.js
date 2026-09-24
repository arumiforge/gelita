/**
 * Layar sinematik layar penuh: cerita pembuka (/intro, context `intro`) dan
 * penutup (/penutup, context `ending`, Tahap 3). Pemutar narasi mode `tap`
 * (game/narrator.js): kartu "Ketuk untuk mulai" membuka kunci audio, lalu
 * narasi diputar otomatis; event `dialogue_advanced` { index, total,
 * context } tiap perpindahan slide.
 */
import { $ } from '../core/dom.js';
import { initNarrator } from './narrator.js';

/** @param {'intro'|'ending'} [screenName] nilai data-screen */
export function initStory(screenName = 'intro') {
  const screen = $(`[data-screen="${screenName}"][data-narrator]`);
  if (!screen) return null;

  return initNarrator(screen);
}
