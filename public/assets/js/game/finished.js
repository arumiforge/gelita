/**
 * Layar selesai (/selesai/{attemptId}): bunyi serpihan dan konfeti canvas.
 * Konfeti CSS bawaan halaman tetap ada untuk tanpa-JavaScript; keduanya mati
 * pada prefers-reduced-motion. Skor dan bintang sudah dirender server.
 */
import { $ } from '../core/dom.js';
import { Sfx } from '../core/audio.js';
import { confetti } from '../core/confetti.js';

export function initFinished() {
  const screen = $('[data-screen="finished"]');
  if (!screen) return;

  Sfx.play(screen.querySelector('.finished-kedu') ? 'region-done' : 'shard');
  if (!document.querySelector('.confetti')) confetti();
}
