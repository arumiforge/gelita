/**
 * Layar sinematik layar penuh: cerita pembuka (/intro, context `intro`) dan
 * penutup (/penutup, context `ending`, Tahap 3). Pemutar narasi mode `tap`
 * (game/narrator.js): kartu "Ketuk untuk mulai" membuka kunci audio, lalu
 * narasi diputar otomatis; event `dialogue_completed` { context, total }
 * at the last slide.
 *
 * Right after sign-up the intro carries the welcome card (flash `welcome`).
 * It opens as a modal over the tap card (owner, 19 October 2026): the
 * student reads it and presses Lanjut, then the intro starts as usual.
 * Without JavaScript the card stays in the page above the first slide.
 */
import { $, $$ } from '../core/dom.js';
import { t } from '../core/config.js';
import { showModal } from '../core/modal.js';
import { confetti } from '../core/confetti.js';
import { initNarrator } from './narrator.js';

/** The server's welcome card, lifted into the shared game modal. */
function showWelcome(screen) {
  const card = $('.welcome-card', screen);
  if (!card) return;

  const title = $('h2', card)?.textContent.trim() || '';
  const text = $$('p', card).map((p) => p.textContent.trim()).filter(Boolean);
  card.remove();

  showModal({
    type: 'info',
    icon: 'key',
    title,
    text,
    blur: true,
    buttons: [{ label: t('next'), style: 'primary', value: 'ok' }],
  });
  confetti(40);
}

/** @param {'intro'|'ending'} [screenName] nilai data-screen */
export function initStory(screenName = 'intro') {
  const screen = $(`[data-screen="${screenName}"][data-narrator]`);
  if (!screen) return null;

  const story = initNarrator(screen);
  showWelcome(screen);
  return story;
}
