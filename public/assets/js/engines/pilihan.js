/**
 * Engine `pilihan` — kuis pilihan bergambar, satu soal per layar.
 *
 * allow_retry = false: jawaban pertama sekaligus jawaban final. Mengetuk satu
 * opsi langsung menonaktifkan seluruh opsi (tidak ada klik ganda), lalu
 * POST /responses. Opsi terpilih diberi hijau/merah dari `correct`; bila
 * salah, opsi kunci (`correct_option_key`, dikirim server SESUDAH jawaban
 * diterima) ikut ditandai hijau setelah 400 ms. Umpan balik ditampilkan bila
 * ada; setelah 1,1 detik soal berikutnya, soal terakhir → complete().
 *
 * Kiriman ulang untuk butir yang sudah dijawab (mis. setelah muat ulang)
 * dibalas server dengan hasil tersimpan (`already_answered`), tidak dinilai
 * ulang.
 */
import { $, $$, el, announce, sleep } from '../core/dom.js';
import { Sfx } from '../core/audio.js';

const REVEAL_MS = 400;
const ADVANCE_MS = 1100;
const FEEDBACK_EXTRA_MS = 1400;

export function mount(ctx) {
  const questions = $$('.question-card', ctx.section);
  const steps = $$('#steps .step', ctx.section);
  if (!questions.length) return;

  let index = Math.max(0, questions.findIndex((q) => q.classList.contains('is-current')));
  let shownAt = performance.now();
  let busy = false;

  const idOf = (card) => Number(card.dataset.item);

  const show = (next, { focus = true } = {}) => {
    questions.forEach((card, i) => card.classList.toggle('is-current', i === next));
    steps.forEach((step, i) => {
      step.classList.toggle('is-current', i === next);
      step.classList.toggle('is-done', i < next);
      if (i === next) step.setAttribute('aria-current', 'step');
      else step.removeAttribute('aria-current');
    });
    index = next;
    shownAt = performance.now();
    if (!focus) return;
    const heading = $('.question', questions[next]);
    heading?.setAttribute('tabindex', '-1');
    heading?.focus();
  };

  ctx.currentItemId = () => idOf(questions[index]);

  const answer = async (option) => {
    if (busy) return;
    const card = option.closest('.question-card');
    if (card !== questions[index]) return;

    busy = true;
    const options = $$('.option', card);
    options.forEach((node) => { node.disabled = true; });
    option.setAttribute('aria-pressed', 'true');

    let result;
    try {
      result = await ctx.submit(idOf(card), { option_key: option.dataset.option }, {
        duration_ms: Math.round(performance.now() - shownAt),
      });
    } catch (error) {
      options.forEach((node) => { node.disabled = false; });
      option.removeAttribute('aria-pressed');
      busy = false;
      await ctx.report(error, { onNetwork: () => ctx.networkPause(null) });
      return;
    }

    const ok = Boolean(result.correct);
    option.classList.add(ok ? 'is-correct' : 'is-wrong');
    Sfx.play(ok ? 'correct' : 'wrong');
    announce(ok ? ctx.t('markCorrect') : ctx.t('markWrong'));
    if (!result.already_answered) ctx.tally({ addCorrect: ok ? 1 : 0, addWrong: ok ? 0 : 1 });

    let wait = ADVANCE_MS;
    if (!ok && result.correct_option_key) {
      await sleep(REVEAL_MS);
      const key = options.find((node) => node.dataset.option === result.correct_option_key);
      key?.classList.add('is-correct');
      wait -= REVEAL_MS;
    }
    if (result.feedback) {
      $('.option-feedback', card)?.remove();
      $('.option-grid', card)?.after(el('p', { class: 'option-feedback', role: 'status' }, result.feedback));
      wait += FEEDBACK_EXTRA_MS;
    }

    await sleep(Math.max(0, wait));
    busy = false;

    const done = result.progress && result.progress.total > 0 && result.progress.answered >= result.progress.total;
    if (index < questions.length - 1 && !done) {
      show(index + 1);
    } else {
      steps.forEach((step) => step.classList.add('is-done'));
      await ctx.complete();
    }
  };

  ctx.section.addEventListener('click', (event) => {
    const option = event.target.closest('.option');
    if (option && !option.disabled) answer(option);
  });

  // Pintasan huruf A–D / angka 1–4 untuk opsi soal yang sedang tampil
  document.addEventListener('keydown', (event) => {
    if (event.altKey || event.ctrlKey || event.metaKey || busy) return;
    if (document.body.classList.contains('has-modal')) return;
    if (event.target instanceof Element && event.target.closest('input, textarea, select')) return;
    const letters = 'abcdefgh';
    const key = event.key.toLowerCase();
    const position = letters.includes(key) ? letters.indexOf(key) : Number(key) - 1;
    if (!Number.isInteger(position) || position < 0) return;
    const option = $$('.option', questions[index])[position];
    if (option && !option.disabled) {
      event.preventDefault();
      option.focus();
      answer(option);
    }
  });

  show(index, { focus: false });
}
