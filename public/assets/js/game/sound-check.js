/**
 * Cek suara (`/cek-suara`, research mode; owner, 15 November 2026, decision 4
 * of 04). Putar suara switches the game sound on (a muted device from an
 * earlier student would otherwise stay silent), opens audio in the tap, and
 * plays one sound. The answers wait until it has played once; the number of
 * plays goes with the answer (`plays`), which the server records as
 * `sound_check_answered`. The sound is not narration, so it writes no
 * narration row.
 */
import { $, $$ } from '../core/dom.js';
import { Sfx } from '../core/audio.js';

const SOUND = 'shard';

export function initSoundCheck() {
  const root = $('[data-screen="sound-check"]');
  if (!root) return;

  const play = $('[data-sound-play]', root);
  const plays = $('[data-sound-plays]', root);
  const answers = $$('[data-sound-answer]', root);
  const first = $('[data-sound-first]', root);
  let count = 0;

  const ready = (on) => {
    for (const button of answers) button.disabled = !on;
    if (first) first.hidden = on;
  };
  ready(false);

  play?.addEventListener('click', () => {
    if (!Sfx.isEnabled()) Sfx.setEnabled(true);
    Sfx.unlock();
    Sfx.play(SOUND);
    count += 1;
    if (plays) plays.value = String(count);
    ready(true);
  });
}
