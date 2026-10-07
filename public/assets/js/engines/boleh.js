/**
 * Engine `boleh` — kartu pernyataan dengan verdict Benar / Salah. Tombol
 * verdict dirender server sebagai radio bawaan; client tidak tahu kuncinya.
 * Setiap pilihan dicatat sebagai event `answer_selected` (action set).
 *
 * Cek jawabanku → POST /check [{ item_id, answer: { verdict } }].
 * Kartu belum dipilih → .has-error selama 2,6 detik, digulirkan ke tengah,
 * modal "Ada N kartu yang belum kamu tentukan" — request tidak dikirim.
 * Kartu salah digulirkan ke tengah satu per satu saat diperbaiki. Setelah
 * kesempatan ketiga habis, kartu yang salah tetap bertanda merah dan kuncinya
 * tidak ditampilkan (Dok 2 §5.3).
 *
 * Muat ulang: pernyataan dengan `items[].state.locked` dipulihkan dengan
 * pilihan siswa sendiri, bertanda benar, dan terkunci.
 *
 * Teks bacaan: pada layar sempit dilipat; judulnya menjadi tombol
 * "Baca teks" yang menempel di atas.
 */
import { $, $$, el, scrollToCenter } from '../core/dom.js';
import { showModal } from '../core/modal.js';
import { Sfx } from '../core/audio.js';

const ERROR_MS = 2600;

function initPassages(section, t) {
  const passages = $$('details.passage', section);
  if (!passages.length) return;

  if (window.matchMedia('(max-width: 640px)').matches) {
    passages.forEach((passage) => {
      passage.open = false;
      passage.classList.add('is-folded');
      const title = $('.passage-title', passage);
      if (title && !$('.passage-read', title)) title.append(el('span', { class: 'passage-read' }, t('readText')));
    });
  }

  // Label "Berdasarkan Teks A" membuka dan menggulir ke teks bacaannya
  section.addEventListener('click', (event) => {
    const link = event.target.closest('a.card-passage[href^="#passage-"]');
    if (!link) return;
    const target = document.getElementById(link.getAttribute('href').slice(1));
    if (!target) return;
    event.preventDefault();
    target.open = true;
    scrollToCenter(target);
    $('summary', target)?.focus({ preventScroll: true });
  });
}

export function mount(ctx) {
  const section = ctx.section;
  const cards = $$('.verdict-card', section);
  const button = $('#btn-check', section);
  if (!cards.length || !button) return;

  initPassages(section, ctx.t);

  const idOf = (card) => Number(card.dataset.item);
  const verdictOf = (card) => $('input[type="radio"]:checked', card)?.value ?? null;
  let lastFocused = null;

  const setMark = (card, ok) => {
    card.classList.toggle('is-correct', ok === true);
    card.classList.toggle('is-wrong', ok === false);
    let mark = $('.card-mark', card);
    if (ok === null) {
      mark?.remove();
      return;
    }
    if (!mark) {
      mark = el('span', { class: 'card-mark', role: 'status' });
      $('.card-head', card)?.append(mark);
    }
    mark.className = `card-mark ${ok ? 'is-ok' : 'is-bad'}`;
    mark.textContent = ok ? `✓ ${ctx.t('markCorrect')}` : `✗ ${ctx.t('markWrong')}`;
  };

  const nextWrong = (after = null) => {
    const wrong = cards.filter((card) => card.classList.contains('is-wrong'));
    if (!wrong.length) return null;
    const index = after ? cards.indexOf(after) : -1;
    return wrong.find((card) => cards.indexOf(card) > index) ?? wrong[0];
  };

  // Memilih verdict baru pada kartu salah: tanda salah dilepas, kartu salah berikutnya digulirkan
  section.addEventListener('change', (event) => {
    const input = event.target.closest('input[type="radio"]');
    if (!input) return;
    const card = input.closest('.verdict-card');
    card.classList.remove('has-error');
    ctx.selected(idOf(card), { action: 'set', value: input.value });
    lastFocused = card;
    Sfx.play('click');
    if (card.classList.contains('is-wrong')) {
      setMark(card, null);
      const next = nextWrong(card);
      if (next) setTimeout(() => scrollToCenter(next), 250);
    }
  });

  // Muat ulang di tengah tantangan: pulihkan pernyataan yang sudah benar
  const states = new Map(ctx.items.map((item) => [Number(item.id), item.state ?? {}]));
  let restored = 0;
  for (const card of cards) {
    const st = states.get(idOf(card));
    const verdict = st?.locked ? String(st.answer?.verdict ?? '') : '';
    const input = verdict ? $$('input[type="radio"]', card).find((radio) => radio.value === verdict) : null;
    if (!input) continue;
    input.checked = true;
    setMark(card, true);
    $$('input[type="radio"]', card).forEach((radio) => { radio.disabled = true; });
    restored++;
  }
  if (restored) ctx.tally({ correct: restored, wrong: Number(ctx.data.tries_used) > 0 ? cards.length - restored : 0 });

  section.addEventListener('focusin', (event) => {
    const card = event.target.closest?.('.verdict-card');
    if (card) lastFocused = card;
  });

  ctx.currentItemId = () => (lastFocused ? idOf(lastFocused) : null);

  button.addEventListener('click', async () => {
    const missing = cards.filter((card) => verdictOf(card) === null);
    if (missing.length) {
      missing.forEach((card) => {
        card.classList.add('has-error');
        setTimeout(() => card.classList.remove('has-error'), ERROR_MS);
      });
      scrollToCenter(missing[0]);
      await showModal({
        type: 'info',
        title: ctx.t('cardsEmptyTitle'),
        text: ctx.t('cardsEmptyText', missing.length),
      });
      $('input[type="radio"]', missing[0])?.focus();
      return;
    }

    const answers = cards.map((card) => ({ item_id: idOf(card), answer: { verdict: verdictOf(card) } }));

    // Pilihan dibekukan selama /check berjalan (radio juga berubah lewat tombol panah)
    const frozen = $$('input[type="radio"]:not(:disabled)', section);
    const unfreeze = () => frozen.forEach((input) => { input.disabled = false; });
    frozen.forEach((input) => { input.disabled = true; });

    ctx.setBusy(button, true);
    let result;
    try {
      result = await ctx.check(answers);
    } catch (error) {
      unfreeze();
      ctx.setBusy(button, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(button) });
      return;
    }
    unfreeze();
    ctx.setBusy(button, false);

    for (const card of cards) {
      const ok = Boolean(result.results?.[idOf(card)]);
      setMark(card, ok);
      if (ok) $$('input[type="radio"]', card).forEach((input) => { input.disabled = true; });
    }
    ctx.tally({ correct: result.correct_count, wrong: result.total - result.correct_count });

    if (result.completed) {
      $$('input[type="radio"]', section).forEach((input) => { input.disabled = true; });
      button.disabled = true;
    }

    const outcome = await ctx.showCheckResult(result, {
      okTitle: ctx.t('cardsOkTitle'),
      okText: ctx.t('cardsOkText'),
      partialTitle: ctx.t('cardsWrongTitle'),
      partialText: ctx.t('cardsWrongText', result.correct_count, result.total),
    });

    if (outcome === 'fix') {
      const first = nextWrong();
      if (first) {
        scrollToCenter(first);
        $('input[type="radio"]:not(:disabled)', first)?.focus({ preventScroll: true });
      }
    }
  });
}
