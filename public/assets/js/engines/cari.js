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
 * Narasi petunjuk (`clues[i].audio` / `audio_id`, hanya rekaman yang
 * disetujui): tombol ▶/⏸ dari <template id="tpl-clue-voice"> dipasang di
 * samping teks petunjuk, satu pemutar per petunjuk (core/audio.js, elemen
 * <audio> bersama agar Safari iPad mengizinkan putar otomatis). Petunjuk
 * pertama hanya diputar lewat tombol (action `play`): sebelum halaman
 * disentuh, browser menolak audio bersuara. Setelah jawaban benar, petunjuk
 * berikutnya diputar sendiri (action `autoplay`) bila suara aktif — setelah
 * jeda singkat agar tidak menimpa bunyi "benar". Berganti petunjuk
 * (maju atau lompat) selalu menghentikan narasi sebelumnya; lompat tidak
 * memutar otomatis. Telemetry membawa attempt_id, jadi setiap event narasi
 * petunjuk ikut menambah challenge_attempts.audio_use_count. Petunjuk tanpa
 * rekaman: tanpa tombol, sama seperti sebelumnya.
 *
 * "Perbesar adegan" (display plan F3.2): see initZoom() below.
 */
import { $, $$, el, announce } from '../core/dom.js';
import { showModal, isModalOpen } from '../core/modal.js';
import { overlayOpened } from '../core/device.js';
import { confetti } from '../core/confetti.js';
import { Sfx, narrationPlayer, pauseNarration } from '../core/audio.js';

const SHAKE_MS = 700;
const VOICE_DELAY_MS = 600; // bunyi "benar" selesai dulu sebelum petunjuk berikutnya dibacakan

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

export function mount(ctx) {
  const scene = $('#hunt-scene', ctx.section);
  const clues = Array.isArray(ctx.data.clues) ? ctx.data.clues : [];
  if (!scene || !clues.length) return;

  const clueText = $('#clue-text', ctx.section);
  const clueIndex = $('#clue-index', ctx.section);
  const listItems = $$('#hunt-list .hunt-target', ctx.section);
  const region = ctx.section.dataset.regionName || '';
  const found = new Set();
  const skipped = new Set(); // petunjuk yang kesempatannya habis
  const triesUsed = clues.map((clue) => Number(clue?.state?.tries) || 0);
  const triesNode = $('#clue-tries', ctx.section);
  const closed = (i) => found.has(i) || skipped.has(i);
  let current = 0;
  let shownAt = performance.now();
  let busy = false;
  let done = false; // attempt closed: the scene stays visible but takes no more taps
  let voiceTimer = null;

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

  const showVoice = (i, { autoplay = false } = {}) => {
    clearTimeout(voiceTimer);
    pauseNarration();
    voices.forEach((player, index) => { player.root.hidden = index !== i; });
    const player = voiceOf(i);
    if (!player) return;
    player.root.hidden = false;
    if (autoplay) {
      voiceTimer = setTimeout(() => {
        if (current === i && !closed(i)) player.autoplay();
      }, VOICE_DELAY_MS);
    }
  };

  // Daftar sisa → tombol lompat ke petunjuk
  listItems.forEach((li, i) => {
    const label = li.textContent.trim();
    li.replaceChildren(el('button', { type: 'button', class: 'hunt-jump', dataset: { clue: i } }, label));
  });

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
    shownAt = performance.now();
  };

  /** autoplay: hanya setelah jawaban benar, bukan saat pemain melompat. */
  const goTo = (i, { autoplay = false } = {}) => {
    if (closed(i)) return;
    current = i;
    render();
    showVoice(i, { autoplay });
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

    if (Number.isFinite(Number(result.try_no))) triesUsed[index] = Number(result.try_no);

    if (result.correct) {
      found.add(index);
      object.classList.add('is-found');
      object.disabled = true;
      object.setAttribute('aria-label', `${object.getAttribute('aria-label')} — ${ctx.t('huntFoundMark')}`);
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
      clearTimeout(voiceTimer);
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
      const next = nextUnfound();
      if (next !== null) goTo(next, { autoplay: true });
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
      const next = nextUnfound();
      if (next !== null) goTo(next);
      return;
    }

    render();
    const decoy = Boolean(result.decoy);
    await showModal({
      type: 'wrong',
      title: decoy ? ctx.t('huntDecoyTitle') : ctx.t('huntOtherTitle'),
      text: [
        result.feedback || (decoy ? ctx.t('huntDecoyText') : ctx.t('huntOtherText', region)),
        ctx.triesText(result.tries_left),
      ],
      buttons: [{ label: ctx.t('tryAgain'), style: 'primary', value: 'ok' }],
    });
    object.focus({ preventScroll: true });
  };

  const zoom = initZoom(ctx, scene);

  scene.addEventListener('click', (event) => {
    const object = event.target.closest('.object');
    if (object) pick(object);
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
  if (closed(current)) current = nextUnfound() ?? current;

  render();
  showVoice(current); // petunjuk pertama: tombol ▶ saja, tanpa putar otomatis
}
