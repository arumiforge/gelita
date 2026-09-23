/**
 * Engine `cari` — cari objek budaya di dalam adegan.
 *
 * Payload tidak memakai `items`: `objects` (semua objek, target maupun
 * jebakan, berbentuk identik { ref, x, y, w, media }) dirender server sebagai
 * .object[data-object=ref], dan `clues` ({ item_id, text }) hanya untuk
 * target yang dinilai. Client tidak dapat mencocokkan petunjuk dengan objek:
 * jawabannya selalu `{ item_id: <petunjuk aktif>, answer: { object: <ref> } }`
 * dan server yang menerjemahkan token itu.
 *
 * Benar → objek diberi tanda temuan, dicoret dari daftar sisa, petunjuk
 * berikutnya muncul. Salah → objek bergetar 700 ms + modal penjelasan
 * (umpan balik dari server bila ada). Semua ketemu (`progress` dari server)
 * → konfeti + modal + complete(). Daftar sisa dapat dipakai untuk melompat
 * ke petunjuk tertentu. Tab berpindah antar objek, Enter memilih.
 */
import { $, $$, el, announce } from '../core/dom.js';
import { showModal } from '../core/modal.js';
import { confetti } from '../core/confetti.js';
import { Sfx } from '../core/audio.js';

const SHAKE_MS = 700;

export function mount(ctx) {
  const scene = $('#hunt-scene', ctx.section);
  const clues = Array.isArray(ctx.data.clues) ? ctx.data.clues : [];
  if (!scene || !clues.length) return;

  const clueText = $('#clue-text', ctx.section);
  const clueIndex = $('#clue-index', ctx.section);
  const listItems = $$('#hunt-list .hunt-target', ctx.section);
  const region = ctx.section.dataset.regionName || '';
  const found = new Set();
  let current = 0;
  let shownAt = performance.now();
  let busy = false;

  // Daftar sisa → tombol lompat ke petunjuk
  listItems.forEach((li, i) => {
    const label = li.textContent.trim();
    li.replaceChildren(el('button', { type: 'button', class: 'hunt-jump', dataset: { clue: i } }, label));
  });

  const render = () => {
    const clue = clues[current];
    if (clueText) clueText.textContent = clue?.text || '';
    if (clueIndex) clueIndex.textContent = String(current + 1);
    listItems.forEach((li, i) => {
      li.classList.toggle('is-current', i === current && !found.has(i));
      li.classList.toggle('is-found', found.has(i));
      const jump = $('.hunt-jump', li);
      if (jump) {
        jump.disabled = found.has(i);
        jump.setAttribute('aria-pressed', i === current ? 'true' : 'false');
      }
    });
    shownAt = performance.now();
  };

  const goTo = (i) => {
    if (found.has(i)) return;
    current = i;
    render();
    announce(`${ctx.t('clueLabel', i + 1)}: ${clues[i]?.text ?? ''}`);
  };

  const nextUnfound = () => {
    for (let step = 1; step <= clues.length; step++) {
      const i = (current + step) % clues.length;
      if (!found.has(i)) return i;
    }
    return null;
  };

  ctx.currentItemId = () => clues[current]?.item_id ?? null;

  const shake = (object) => {
    object.classList.remove('is-shaking');
    void object.offsetWidth; // mulai ulang animasi
    object.classList.add('is-shaking');
    setTimeout(() => object.classList.remove('is-shaking'), SHAKE_MS);
  };

  const pick = async (object) => {
    if (busy || object.disabled) return;
    const clue = clues[current];
    if (!clue) return;

    busy = true;
    let result;
    try {
      result = await ctx.submit(clue.item_id, { object: object.dataset.object }, {
        duration_ms: Math.round(performance.now() - shownAt),
      });
    } catch (error) {
      busy = false;
      await ctx.report(error, { onNetwork: () => ctx.networkPause(null) });
      return;
    }
    busy = false;

    if (result.correct) {
      found.add(current);
      object.classList.add('is-found');
      object.disabled = true;
      object.setAttribute('aria-label', `${object.getAttribute('aria-label')} — ${ctx.t('huntFoundMark')}`);
      Sfx.play('correct');
      ctx.tally({ addCorrect: 1 });

      const done = result.progress && result.progress.total > 0 && result.progress.answered >= result.progress.total;
      if (done || found.size >= clues.length) {
        render();
        confetti();
        await showModal({
          type: 'correct',
          title: ctx.t('huntDoneTitle'),
          text: ctx.t('huntDoneText'),
          dismissible: false,
          buttons: [{ label: ctx.t('next'), style: 'primary', value: 'next' }],
        });
        await ctx.complete();
        return;
      }

      const next = nextUnfound();
      if (next !== null) goTo(next);
      return;
    }

    // Klik salah: jebakan (budaya daerah lain) atau target lain (bukan petunjuk kali ini)
    shake(object);
    Sfx.play('wrong');
    ctx.tally({ addWrong: 1 });

    const decoy = Boolean(result.decoy);
    await showModal({
      type: 'wrong',
      title: decoy ? ctx.t('huntDecoyTitle') : ctx.t('huntOtherTitle'),
      text: result.feedback || (decoy ? ctx.t('huntDecoyText') : ctx.t('huntOtherText', region)),
      buttons: [{ label: ctx.t('tryAgain'), style: 'primary', value: 'ok' }],
    });
    object.focus({ preventScroll: true });
  };

  scene.addEventListener('click', (event) => {
    const object = event.target.closest('.object');
    if (object) pick(object);
  });

  ctx.section.addEventListener('click', (event) => {
    const jump = event.target.closest('.hunt-jump');
    if (jump && !jump.disabled) goTo(Number(jump.dataset.clue));
  });

  render();
}
