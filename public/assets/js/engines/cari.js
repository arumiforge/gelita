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
 * Aturan 3 kesempatan per petunjuk (Dok 2 §5): setiap ketukan benda adalah
 * satu percobaan untuk petunjuk yang sedang aktif; #clue-tries menampilkan
 * sisa kesempatan petunjuk itu (`tries_left` dari server).
 *
 * Benar → objek diberi tanda temuan, dicoret dari daftar sisa, petunjuk
 * berikutnya muncul. Salah → objek bergetar 700 ms + modal penjelasan
 * (umpan balik dari server bila ada) + sisa kesempatan. Salah ke-3
 * (`clue_closed`) → modal "Petunjuk ini dilewati", petunjuk diberi ✗ di
 * daftar, bendanya TIDAK ditandai/diungkap, petunjuk berikutnya muncul.
 * Semua petunjuk tertutup → server menutup pengerjaan (`completed`,
 * `redirect`): konfeti + modal bila semua ketemu, modal "Kesempatanmu sudah
 * habis" bila ada yang dilewati; the scene stays (objects locked) until the
 * student taps Lanjut in the side panel. Daftar sisa dapat dipakai
 * untuk melompat ke petunjuk yang masih terbuka (tidak menghitung
 * percobaan). Tab berpindah antar objek, Enter memilih.
 *
 * Muat ulang: `clues[i].state` ({found, closed, tries}) dan `objects[].found`
 * memulihkan temuan, petunjuk yang dilewati, dan sisa kesempatan.
 *
 * Clue narration (`clues[i].audio` / `audio_id`, approved recordings only):
 * the ▶/⏸ button from <template id="tpl-clue-voice"> sits next to the clue
 * text, one player per clue (core/audio.js, shared <audio> element for
 * Safari iPad). A clue is only ever read out when the student taps ▶
 * (action `play`): nothing plays on its own, not on load and not after a
 * correct answer (owner request, 12 October 2026). Switching clues (moving on
 * or jumping) always stops the previous narration. Telemetry carries
 * attempt_id, so every clue narration event also counts toward
 * challenge_attempts.audio_use_count. Clues without a recording get no button.
 *
 * "Perbesar adegan" (display plan F3.2): see initZoom() below.
 *
 * Two rounds (research revision 10, 31 §12.1). Round 1 is the scene above.
 * `ctx.data.round2` (if any) is *Periksa Unggahan*: once every round-1 clue
 * is closed a short interlude modal names round 2, then the scene is redrawn
 * as a Kedugram post: the photo (decorative) and one absolutely positioned
 * <button class="post-part"> per tap part, plain text that zooms with the
 * scene. Parts carry opaque per-attempt refs like objects, and the answer has
 * the same shape `{ object: <ref> }`. Tries stay per clue; a wrong tap shows
 * the round-2 sentence (data-part-wrong). A reload after round 1 starts
 * straight in round 2.
 *
 * Research data (revision 10, part 3: 29 §6.1–6.2). Each tap sends
 * `ready_ms` (the clue shown or the previous tap's feedback closed) and
 * `last_input_ms` (the tap), both in ms since the challenge opened. A tap on
 * an empty spot of the scene sends `empty_tap` { x_pct, y_pct, round, t_ms }
 * through the event queue, at most CONFIG.emptyTapLimit per clue per
 * attempt; the rest leave as one `empty_tap` with `overflow`. The game stays
 * silent on such a tap, and the client never measures a distance (it does
 * not know where the key is).
 */
import { $, $$, el, announce } from '../core/dom.js';
import { showModal, isModalOpen } from '../core/modal.js';
import { overlayOpened } from '../core/device.js';
import { confetti } from '../core/confetti.js';
import { Sfx, narrationPlayer, pauseNarration } from '../core/audio.js';
import { emit } from '../core/events.js';
import { CONFIG } from '../core/config.js';

const SHAKE_MS = 700;

/**
 * "Perbesar adegan" (display plan F3.2): the button after the scene toggles
 * `.hunt.is-zoomed` and `body.has-hunt-zoom`; the CSS grows the scene to the
 * whole stage with the clue on a ribbon at the top. Objects are placed in
 * percent, so taps keep working while zoomed. Opening logs `overlay_opened`
 * (`hunt_zoom`); closing (button or Esc) logs nothing.
 *
 * Esc belongs to an open modal, native dialog, or open lantern panel first;
 * otherwise it closes the zoom and stops there (no toast or other Esc
 * handler reacts to the same key press).
 *
 * @returns {{ close: () => void }}
 */
function initZoom(ctx, scene) {
  const button = $('#hunt-zoom', ctx.section);
  const hunt = scene.closest('.hunt');
  if (!button || !hunt) return { close: () => {} };

  const label = $('[data-hunt-zoom-label]', button);
  let zoomed = false;

  const set = (on) => {
    zoomed = on;
    hunt.classList.toggle('is-zoomed', on);
    document.body.classList.toggle('has-hunt-zoom', on);
    button.setAttribute('aria-pressed', on ? 'true' : 'false');
    const text = (on ? button.dataset.labelClose : button.dataset.labelOpen) || '';
    if (label) label.textContent = text;
    button.title = text;
  };

  button.hidden = false;
  button.addEventListener('click', () => {
    if (zoomed) {
      set(false);
      return;
    }
    set(true);
    overlayOpened('hunt_zoom', { levelId: ctx.levelId ?? null, level: ctx.levelCode ?? null });
  });

  // Capture on window runs before the document-level Esc handlers (toast, HUD)
  window.addEventListener('keydown', (event) => {
    if (!zoomed || event.key !== 'Escape' || event.defaultPrevented) return;
    if (isModalOpen() || document.querySelector('dialog[open], details.lantern[open]')) return;
    event.preventDefault();
    event.stopPropagation();
    set(false);
  }, true);

  return { close: () => { if (zoomed) set(false); } };
}

/** Round-2 post drawn into the scene: photo + one text button per tap part. */
function drawPost(scene, post, texts) {
  $$('.object, .scene-bg', scene).forEach((node) => node.remove());
  scene.classList.remove('is-drawn');
  scene.classList.add('is-post');
  if (post.media) {
    scene.append(el('img', { class: 'post-photo', src: post.media, alt: texts.photoAlt || '', loading: 'lazy' }));
  }
  (post.parts ?? []).forEach((part, i) => {
    const button = el('button', {
      type: 'button',
      class: `post-part is-${part.kind}`,
      dataset: { object: part.ref },
      style: `left: ${Number(part.x)}%; top: ${Number(part.y)}%; width: ${Number(part.w)}%; height: ${Number(part.h)}%`,
      'aria-label': `${(texts.partLabel || '{0}').replace('{0}', String(i + 1))}: ${part.label ? `${part.label}. ` : ''}${part.text}`,
    }, el('span', { class: 'post-part-text' }, part.text));
    scene.append(button);
  });
}

export function mount(ctx) {
  const scene = $('#hunt-scene', ctx.section);
  let clues = Array.isArray(ctx.data.clues) ? ctx.data.clues : [];
  if (!scene || !clues.length) return;

  const hunt = scene.closest('.hunt');
  const texts = {
    title: hunt?.dataset.round2Title || '',
    intro: hunt?.dataset.round2Intro || '',
    start: hunt?.dataset.round2Start || '',
    partWrong: hunt?.dataset.partWrong || '',
    partLabel: hunt?.dataset.partLabel || '',
    photoAlt: hunt?.dataset.photoAlt || '',
    pending: hunt?.dataset.pendingLabel || '{0}',
  };
  const round2 = ctx.data.round2 && Array.isArray(ctx.data.round2.clues) && ctx.data.round2.clues.length ? ctx.data.round2 : null;
  let round = 1;

  const clueText = $('#clue-text', ctx.section);
  const clueIndex = $('#clue-index', ctx.section);
  let listItems = $$('#hunt-list .hunt-target', ctx.section);
  const region = ctx.section.dataset.regionName || '';
  const found = new Set();
  const skipped = new Set(); // petunjuk yang kesempatannya habis
  let triesUsed = clues.map((clue) => Number(clue?.state?.tries) || 0);
  const triesNode = $('#clue-tries', ctx.section);
  const closed = (i) => found.has(i) || skipped.has(i);
  let current = 0;
  let readyAt = ctx.sinceOpen(); // clue shown or previous feedback done (29 §6.1)
  let busy = false;
  let done = false; // attempt closed: the scene stays visible but takes no more taps

  // Narasi petunjuk: slot di samping teks, pemutar dibuat saat petunjuknya tampil
  const voiceTemplate = $('#tpl-clue-voice', ctx.section);
  const voiceSlot = voiceTemplate && clueText && clues.some((clue) => clue?.audio)
    ? el('div', { class: 'clue-voice' })
    : null;
  const voices = new Map(); // indeks petunjuk → NarrationPlayer

  if (voiceSlot) {
    clueText.parentElement.classList.add('has-voice');
    clueText.before(voiceSlot);
  }

  const voiceOf = (i) => {
    const clue = clues[i];
    if (!voiceSlot || !clue?.audio) return null;
    if (!voices.has(i)) {
      const node = voiceTemplate.content.firstElementChild.cloneNode(true);
      node.dataset.src = clue.audio;
      node.dataset.audioId = String(clue.audio_id ?? '');
      node.hidden = true;
      voiceSlot.append(node);
      // Berkas gagal dimuat: tombol disembunyikan (NarrationPlayer#fail), teks tetap ada
      voices.set(i, narrationPlayer(node, { attemptId: ctx.attemptId, shared: true }));
    }
    return voices.get(i);
  };

  // Shows the ▶ button of clue i; playback only starts when the student taps it
  const showVoice = (i) => {
    pauseNarration();
    voices.forEach((player, index) => { player.root.hidden = index !== i; });
    const player = voiceOf(i);
    if (player) player.root.hidden = false;
  };

  // Daftar sisa → tombol lompat ke petunjuk
  const makeJumps = () => listItems.forEach((li, i) => {
    const label = li.textContent.trim();
    li.replaceChildren(el('button', { type: 'button', class: 'hunt-jump', dataset: { clue: i } }, label));
  });
  makeJumps();

  const render = () => {
    const clue = clues[current];
    if (clueText) clueText.textContent = clue?.text || '';
    if (clueIndex) clueIndex.textContent = String(current + 1);
    ctx.setTries(triesNode, ctx.maxTries - (triesUsed[current] ?? 0), ctx.t('clueTriesLeft', '{0}'));
    listItems.forEach((li, i) => {
      li.classList.toggle('is-current', i === current && !closed(i));
      li.classList.toggle('is-found', found.has(i));
      li.classList.toggle('is-skipped', skipped.has(i));
      const jump = $('.hunt-jump', li);
      if (jump) {
        jump.disabled = closed(i);
        jump.setAttribute('aria-pressed', i === current ? 'true' : 'false');
      }
    });
    readyAt = ctx.sinceOpen();
  };

  // ------------------------------------------------- empty taps (29 §6.2)

  const tapLimit = Math.max(0, Number(CONFIG.emptyTapLimit) || 30);
  const tapCounts = new Map(); // `${round}:${item_id}` → empty taps sent
  let overflow = null;         // { key, refs, round, count } held back over the limit

  const sendOverflow = () => {
    if (!overflow) return;
    emit('empty_tap', { ...overflow.refs, payload: { round: overflow.round, overflow: overflow.count, t_ms: ctx.sinceOpen() } });
    overflow = null;
  };

  const emptyTap = (event) => {
    const clue = clues[current];
    if (!clue || done) return;
    const box = scene.getBoundingClientRect();
    if (!box.width || !box.height) return;
    const key = `${round}:${clue.item_id}`;
    const refs = { levelId: ctx.levelId, nodeId: ctx.nodeId, attemptId: ctx.attemptId, itemId: clue.item_id };
    if (overflow && overflow.key !== key) sendOverflow();
    const sent = tapCounts.get(key) ?? 0;
    if (sent >= tapLimit) {
      overflow = overflow ?? { key, refs, round, count: 0 };
      overflow.count += 1;
      return;
    }
    tapCounts.set(key, sent + 1);
    const pct = (value) => Math.round(Math.min(100, Math.max(0, value)) * 10) / 10;
    emit('empty_tap', {
      ...refs,
      payload: {
        x_pct: pct(((event.clientX - box.left) / box.width) * 100),
        y_pct: pct(((event.clientY - box.top) / box.height) * 100),
        round,
        t_ms: ctx.sinceOpen(),
      },
    });
  };
  window.addEventListener('pagehide', sendOverflow, true);

  const goTo = (i) => {
    if (closed(i)) return;
    current = i;
    render();
    showVoice(i);
    announce(`${ctx.t('clueLabel', i + 1)}: ${clues[i]?.text ?? ''}`);
  };

  const nextUnfound = () => {
    for (let step = 1; step <= clues.length; step++) {
      const i = (current + step) % clues.length;
      if (!closed(i)) return i;
    }
    return null;
  };

  ctx.currentItemId = () => clues[current]?.item_id ?? null;

  // Round 2: new clues, list, and scene; tries and marks start afresh per clue
  const startRoundTwo = () => {
    round = 2;
    clues = round2.clues;
    found.clear();
    skipped.clear();
    triesUsed = clues.map((clue) => Number(clue?.state?.tries) || 0);
    clues.forEach((clue, i) => {
      if (clue?.state?.found) found.add(i);
      else if (clue?.state?.closed) skipped.add(i);
    });
    voices.forEach((player) => player.root.remove());
    voices.clear();
    drawPost(scene, round2, texts);
    const list = $('#hunt-list', ctx.section);
    if (list) {
      list.replaceChildren(...clues.map((_, i) => el('li', { class: 'hunt-target' }, texts.pending.replace('{0}', String(i + 1)))));
      listItems = $$('.hunt-target', list);
      makeJumps();
    }
    const totalNode = $('#clue-total', ctx.section);
    if (totalNode) totalNode.textContent = String(clues.length);
    current = 0;
    if (closed(current)) current = nextUnfound() ?? current;
    render();
    showVoice(current);
    announce(`${texts.title}: ${clues[current]?.text ?? ''}`);
  };

  // Next open clue; at the end of round 1 the interlude and round 2
  const advance = async () => {
    const next = nextUnfound();
    if (next !== null) {
      goTo(next);
      return;
    }
    if (round === 1 && round2) {
      pauseNarration();
      await showModal({
        type: 'info',
        title: texts.title,
        text: texts.intro,
        dismissible: false,
        buttons: [{ label: texts.start || ctx.t('next'), style: 'primary', value: 'next' }],
      });
      startRoundTwo();
    }
  };

  const shake = (object) => {
    object.classList.remove('is-shaking');
    void object.offsetWidth; // mulai ulang animasi
    object.classList.add('is-shaking');
    setTimeout(() => object.classList.remove('is-shaking'), SHAKE_MS);
  };

  const pick = async (object) => {
    if (busy || done || object.disabled) return;
    const index = current; // hasil request selalu milik petunjuk yang aktif saat diketuk
    const clue = clues[index];
    if (!clue) return;

    busy = true;
    sendOverflow();
    let result;
    try {
      result = await ctx.submit(clue.item_id, { object: object.dataset.object }, {
        ready_ms: readyAt,
        last_input_ms: ctx.sinceOpen(),
      });
    } catch (error) {
      busy = false;
      await ctx.report(error, { onNetwork: () => ctx.networkPause(null) });
      return;
    }
    busy = false;

    if (Number.isFinite(Number(result.try_no))) triesUsed[index] = Number(result.try_no);

    if (result.correct) {
      found.add(index);
      // Round 2: a key part stays tappable, it is a lure for the other clues (31 §6.3)
      if (round === 1) {
        object.classList.add('is-found');
        object.disabled = true;
        object.setAttribute('aria-label', `${object.getAttribute('aria-label')} — ${ctx.t('huntFoundMark')}`);
      }
      Sfx.play('correct');
      ctx.tally({ addCorrect: 1 });
    } else {
      // Klik salah: jebakan (budaya daerah lain) atau target lain (bukan petunjuk kali ini)
      shake(object);
      Sfx.play('wrong');
      ctx.tally({ addWrong: 1 });
      if (result.clue_closed) skipped.add(index);
    }

    // Semua petunjuk tertutup: server sudah menutup pengerjaan
    if (result.completed) {
      done = true;
      zoom.close(); // the closing modal and the Lanjut button belong to the normal layout
      pauseNarration();
      render();
      const solved = result.completion_reason !== 'tries_exhausted';
      if (solved) confetti();
      await showModal({
        type: solved ? 'correct' : 'info',
        title: solved ? ctx.t('huntDoneTitle') : ctx.t('triesExhaustedTitle'),
        text: solved ? ctx.t('huntDoneText') : ctx.t('triesExhaustedText'),
        dismissible: false,
        buttons: [{ label: ctx.closingLabel, style: 'primary', value: 'next' }],
      });
      await ctx.finish(result);
      return;
    }

    if (result.correct) {
      await advance();
      return;
    }

    if (result.clue_closed) {
      // Kesempatan petunjuk ini habis: dilewati tanpa mengungkap bendanya
      render();
      await showModal({
        type: 'info',
        title: ctx.t('clueFailedTitle'),
        text: ctx.t('clueFailedText'),
        dismissible: false,
        buttons: [{ label: ctx.t('next'), style: 'primary', value: 'next' }],
      });
      await advance();
      return;
    }

    render();
    const decoy = Boolean(result.decoy);
    await showModal({
      type: 'wrong',
      title: decoy ? ctx.t('huntDecoyTitle') : ctx.t('huntOtherTitle'),
      text: [
        round === 2
          ? texts.partWrong
          : (result.feedback || (decoy ? ctx.t('huntDecoyText') : ctx.t('huntOtherText', region))),
        ctx.triesText(result.tries_left),
      ],
      buttons: [{ label: ctx.t('tryAgain'), style: 'primary', value: 'ok' }],
    });
    readyAt = ctx.sinceOpen();
    object.focus({ preventScroll: true });
  };

  const zoom = initZoom(ctx, scene);

  scene.addEventListener('click', (event) => {
    const object = event.target.closest('.object, .post-part');
    if (object) pick(object);
    else if (!busy) emptyTap(event);
  });

  // Lompat diabaikan selama ketukan masih menunggu server
  ctx.section.addEventListener('click', (event) => {
    const jump = event.target.closest('.hunt-jump');
    if (jump && !jump.disabled && !busy) goTo(Number(jump.dataset.clue));
  });

  // Muat ulang di tengah tantangan: pulihkan temuan dan petunjuk yang dilewati
  clues.forEach((clue, i) => {
    if (clue?.state?.found) found.add(i);
    else if (clue?.state?.closed) skipped.add(i);
  });
  const foundRefs = new Set((ctx.data.objects ?? []).filter((o) => o.found).map((o) => String(o.ref)));
  $$('.object', scene).forEach((object) => {
    if (!foundRefs.has(object.dataset.object)) return;
    object.classList.add('is-found');
    object.disabled = true;
    object.setAttribute('aria-label', `${object.getAttribute('aria-label')} — ${ctx.t('huntFoundMark')}`);
  });
  if (found.size || skipped.size) ctx.tally({ correct: found.size, wrong: 0 });

  // Reload after round 1: straight on to round 2
  if (round2 && clues.every((_, i) => closed(i))) {
    startRoundTwo();
    return;
  }
  if (closed(current)) current = nextUnfound() ?? current;

  render();
  showVoice(current);
}
