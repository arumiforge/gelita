/**
 * Layar selesai (/selesai/{attemptId}): bunyi serpihan dan konfeti canvas.
 * Konfeti CSS bawaan halaman tetap ada untuk tanpa-JavaScript; keduanya mati
 * pada prefers-reduced-motion. Skor dan bintang sudah dirender server.
 *
 * The region-done card (Mbah Kedu, Pustaka) becomes a modal here: the panel
 * under it is inert behind a blurred, dimmed layer, focus starts on its close
 * button, and the close button, Escape, or a tap on the layer closes it and
 * moves focus to the main button. Without JavaScript the card simply stays and
 * the buttons below it remain free. Detail hasil is a popover handled by
 * game/hud.js.
 */
import { $ } from '../core/dom.js';
import { Sfx } from '../core/audio.js';
import { confetti } from '../core/confetti.js';

function initRegionCard(screen) {
  const card = $('.result-kedu', screen);
  const button = card && $('[data-dismiss]', card);
  if (!button) return;

  const panel = $('.result-card', screen);
  const close = () => {
    if (screen.classList.contains('is-kedu-closed')) return;
    screen.classList.add('is-kedu-closed');
    card.removeAttribute('aria-modal');
    if (panel) panel.inert = false;
    $('.result-btn.is-primary', screen)?.focus();
  };

  card.setAttribute('aria-modal', 'true');
  if (panel) panel.inert = true;
  button.hidden = false;
  button.focus({ preventScroll: true });

  button.addEventListener('click', close);
  $('.result-scrim', screen)?.addEventListener('click', close);
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') close();
  });
}

export function initFinished() {
  const screen = $('[data-screen="finished"]');
  if (!screen) return;

  Sfx.play(screen.hasAttribute('data-region-done') ? 'region-done' : 'shard');
  if (!document.querySelector('.confetti')) confetti();
  initRegionCard(screen);
}
