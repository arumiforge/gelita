/**
 * Layar selesai (/selesai/{attemptId}): bunyi serpihan dan konfeti canvas.
 * Konfeti CSS bawaan halaman tetap ada untuk tanpa-JavaScript; keduanya mati
 * pada prefers-reduced-motion. Skor dan bintang sudah dirender server.
 *
 * The region-done card (Mbah Kedu, Pustaka) over the panel gets its close
 * button here; without JavaScript it simply stays, the buttons below it
 * remain free. Detail hasil is a popover handled by game/hud.js.
 */
import { $ } from '../core/dom.js';
import { Sfx } from '../core/audio.js';
import { confetti } from '../core/confetti.js';

function initRegionCard(screen) {
  const card = $('.result-kedu', screen);
  const button = card && $('[data-dismiss]', card);
  if (!button) return;

  button.hidden = false;
  button.addEventListener('click', () => {
    card.classList.add('is-dismissed');
    $('.result-btn.is-primary', screen)?.focus();
  });
}

export function initFinished() {
  const screen = $('[data-screen="finished"]');
  if (!screen) return;

  Sfx.play(screen.hasAttribute('data-region-done') ? 'region-done' : 'shard');
  if (!document.querySelector('.confetti')) confetti();
  initRegionCard(screen);
}
