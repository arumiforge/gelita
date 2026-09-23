/**
 * Engine `rumpang` — isi bagian rumpang.
 *
 * Varian bank kata: ketuk kotak rumpang lalu kata di bank. Bank kata dari
 * payload.word_bank (jawaban + pengecoh, diacak server); client tidak tahu
 * kata mana milik rumpang mana. Tiap chip punya id unik, jadi dua kata yang
 * kebetulan sama tidak saling menandai "terpakai" pada chip yang keliru.
 * Mengetuk kotak yang sudah terisi mengembalikan katanya ke bank dan
 * menjadikan kotak itu aktif. Setelah mengisi, fokus pindah ke kotak kosong
 * berikutnya.
 *
 * Varian tanpa bank: <input>; Enter memindahkan fokus ke kotak berikutnya,
 * bukan mengirim form.
 *
 * Periksa → POST /check dengan seluruh isian sekaligus. Kotak kosong →
 * modal lokal, request TIDAK dikirim. Jawaban yang sudah benar tidak dihapus
 * dan terkunci saat pemeriksaan ulang.
 */
import { $, $$, announce, scrollToCenter } from '../core/dom.js';
import { showModal } from '../core/modal.js';
import { Sfx } from '../core/audio.js';

export function mount(ctx) {
  const root = $('.rumpang', ctx.section);
  if (!root) return;

  const button = $('#btn-check', root);
  const useBank = root.dataset.wordBank === '1';
  const blanks = useBank ? $$('.blank', root) : $$('.blank-input', root);
  const words = $$('.word', root);
  const placeholder = '?';
  const filled = new Map(); // itemId → chip kata (varian bank)
  let active = null;

  const idOf = (node) => Number(node.dataset.item);
  const locked = (blank) => blank.classList.contains('is-correct');

  // ------------------------------------------------------------ bank kata

  const activate = (blank) => {
    active?.classList.remove('is-active');
    active?.setAttribute('aria-pressed', 'false');
    active = blank;
    if (blank) {
      blank.classList.add('is-active');
      blank.setAttribute('aria-pressed', 'true');
    }
  };

  const release = (blank) => {
    const chip = filled.get(idOf(blank));
    if (!chip) return;
    filled.delete(idOf(blank));
    chip.classList.remove('is-used');
    chip.disabled = false;
    chip.removeAttribute('aria-disabled');
    blank.textContent = placeholder;
    blank.classList.remove('is-filled', 'is-wrong');
    blank.setAttribute('aria-label', blank.dataset.label);
  };

  const nextEmpty = (from) => {
    const start = blanks.indexOf(from);
    const ordered = blanks.slice(start + 1).concat(blanks.slice(0, start + 1));
    return ordered.find((blank) => !filled.has(idOf(blank)) && !locked(blank)) ?? null;
  };

  const place = (chip) => {
    const target = active && !locked(active) ? active : nextEmpty(blanks[blanks.length - 1]);
    if (!target) return;

    release(target);
    filled.set(idOf(target), chip);
    chip.classList.add('is-used');
    chip.disabled = true;
    target.textContent = chip.textContent;
    target.classList.add('is-filled');
    target.classList.remove('is-wrong');
    target.setAttribute('aria-label', `${target.dataset.label}: ${chip.textContent}`);
    Sfx.play('click');
    announce(ctx.t('blankFilled', target.dataset.label, chip.textContent));

    const next = nextEmpty(target);
    activate(next);
    (next || button)?.focus();
  };

  if (useBank) {
    for (const blank of blanks) {
      blank.dataset.label = blank.getAttribute('aria-label') || '';
      blank.setAttribute('aria-pressed', 'false');
      blank.addEventListener('click', () => {
        if (locked(blank)) return;
        if (filled.has(idOf(blank))) release(blank);
        activate(blank);
        (words.find((chip) => !chip.disabled) || blank).focus();
      });
    }
    words.forEach((chip) => chip.addEventListener('click', () => place(chip)));
    activate(blanks.find((blank) => !locked(blank)) ?? null);
  } else {
    blanks.forEach((input, index) => {
      input.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const next = blanks.slice(index + 1).find((b) => !b.readOnly) || blanks.find((b) => !b.readOnly && b.value.trim() === '');
        (next && next !== input ? next : button)?.focus();
      });
      input.addEventListener('input', () => input.classList.remove('is-wrong'));
    });
  }

  ctx.currentItemId = () => {
    if (useBank) return active ? idOf(active) : null;
    const focused = blanks.find((input) => input === document.activeElement);
    return focused ? idOf(focused) : null;
  };

  // --------------------------------------------------------------- periksa

  const answerOf = (blank) => {
    if (useBank) return filled.get(idOf(blank))?.textContent ?? '';
    return blank.value.trim();
  };

  button?.addEventListener('click', async () => {
    const empty = blanks.filter((blank) => answerOf(blank) === '');
    if (empty.length) {
      await showModal({
        type: 'info',
        title: ctx.t('blankEmptyTitle'),
        text: ctx.t('blankEmptyText', empty.length),
      });
      scrollToCenter(empty[0]);
      if (useBank) activate(empty[0]);
      empty[0].focus();
      return;
    }

    const answers = blanks.map((blank) => ({ item_id: idOf(blank), answer: { text: answerOf(blank) } }));

    ctx.setBusy(button, true);
    let result;
    try {
      result = await ctx.check(answers);
    } catch (error) {
      ctx.setBusy(button, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(button) });
      return;
    }
    ctx.setBusy(button, false);

    for (const blank of blanks) {
      const ok = Boolean(result.results?.[idOf(blank)]);
      blank.classList.toggle('is-correct', ok);
      blank.classList.toggle('is-wrong', !ok);
      if (ok) {
        if (useBank) blank.disabled = true;
        else blank.readOnly = true;
      }
      const state = ok ? ctx.t('markCorrect') : ctx.t('markWrong');
      const label = useBank ? blank.dataset.label : blank.getAttribute('aria-label');
      blank.setAttribute('aria-label', `${label}: ${answerOf(blank)} — ${state}`);
    }
    ctx.tally({ correct: result.correct_count, wrong: result.total - result.correct_count });

    const outcome = await ctx.showCheckResult(result, {
      okTitle: ctx.t('blankOkTitle'),
      okText: ctx.t('blankOkText'),
      partialTitle: ctx.t('blankWrongTitle'),
      partialText: ctx.t('blankWrongText', result.correct_count, result.total),
    });

    if (outcome === 'fix') {
      const wrong = blanks.find((blank) => blank.classList.contains('is-wrong'));
      if (wrong) {
        scrollToCenter(wrong);
        if (useBank) activate(wrong);
        wrong.focus();
      }
    }
  });
}
