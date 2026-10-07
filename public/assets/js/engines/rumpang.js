/**
 * Engine `rumpang` — isi bagian rumpang dengan bank kata.
 *
 * Ketuk kotak rumpang lalu kata di bank. Bank kata dari
 * payload.word_bank (jawaban + pengecoh, diacak server); client tidak tahu
 * kata mana milik rumpang mana. Tiap chip punya id unik, jadi dua kata yang
 * kebetulan sama tidak saling menandai "terpakai" pada chip yang keliru.
 * Mengetuk kotak yang sudah terisi mengembalikan katanya ke bank dan
 * menjadikan kotak itu aktif. Setelah mengisi, fokus pindah ke kotak kosong
 * berikutnya.
 *
 * Setiap kata yang dipasang atau dilepas dicatat sebagai event
 * `answer_selected` (action set / clear).
 *
 * Cek jawabanku → POST /check dengan seluruh isian sekaligus. Kotak kosong →
 * modal lokal, request TIDAK dikirim (tidak dihitung percobaan). Jawaban yang
 * sudah benar tidak dihapus dan terkunci saat pemeriksaan ulang. Setelah
 * kesempatan ketiga habis, kotak yang salah tetap bertanda merah dan kuncinya
 * tidak ditampilkan (Dok 2 §5.3).
 *
 * Muat ulang: rumpang dengan `items[].state.locked` diisi lagi dengan
 * jawaban siswa sendiri (chip pertama yang belum terpakai dengan teks sama)
 * dan terkunci.
 */
import { $, $$, announce, scrollToCenter } from '../core/dom.js';
import { showModal } from '../core/modal.js';
import { Sfx } from '../core/audio.js';

export function mount(ctx) {
  const root = $('.rumpang', ctx.section);
  if (!root) return;

  const button = $('#btn-check', root);
  const blanks = $$('.blank', root);
  const words = $$('.word', root);
  const placeholder = '?';
  const filled = new Map(); // itemId → chip kata
  let active = null;
  let checking = false; // isian dibekukan selama /check berjalan

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

  const release = (blank, record = true) => {
    const chip = filled.get(idOf(blank));
    if (!chip) return;
    if (record) ctx.selected(idOf(blank), { action: 'clear', value: chip.textContent });
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
    if (checking) return;
    const target = active && !locked(active) ? active : nextEmpty(blanks[blanks.length - 1]);
    if (!target) return;

    const previous = filled.get(idOf(target))?.textContent ?? null;
    release(target, false);
    filled.set(idOf(target), chip);
    ctx.selected(idOf(target), { action: 'set', value: chip.textContent, ...(previous !== null ? { from: previous } : {}) });
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

  for (const blank of blanks) {
    blank.dataset.label = blank.getAttribute('aria-label') || '';
    blank.setAttribute('aria-pressed', 'false');
    blank.addEventListener('click', () => {
      if (locked(blank) || checking) return;
      if (filled.has(idOf(blank))) release(blank);
      activate(blank);
      (words.find((chip) => !chip.disabled) || blank).focus();
    });
  }
  words.forEach((chip) => chip.addEventListener('click', () => place(chip)));

  // Muat ulang di tengah tantangan: pulihkan rumpang yang sudah benar
  const states = new Map(ctx.items.map((item) => [Number(item.id), item.state ?? {}]));
  let restored = 0;
  for (const blank of blanks) {
    const st = states.get(idOf(blank));
    const text = st?.locked ? String(st.answer?.text ?? '') : '';
    if (!text) continue;
    const chip = words.find((word) => !word.disabled && word.textContent.trim() === text)
      ?? words.find((word) => !word.disabled && word.textContent.trim().toLowerCase() === text.toLowerCase());
    if (!chip) continue;
    filled.set(idOf(blank), chip);
    chip.classList.add('is-used');
    chip.disabled = true;
    blank.textContent = chip.textContent;
    blank.classList.add('is-filled', 'is-correct');
    blank.disabled = true;
    blank.setAttribute('aria-label', `${blank.dataset.label}: ${chip.textContent} — ${ctx.t('markCorrect')}`);
    restored++;
  }
  if (restored) ctx.tally({ correct: restored, wrong: Number(ctx.data.tries_used) > 0 ? blanks.length - restored : 0 });
  activate(blanks.find((blank) => !locked(blank)) ?? null);

  ctx.currentItemId = () => (active ? idOf(active) : null);

  // --------------------------------------------------------------- periksa

  const answerOf = (blank) => filled.get(idOf(blank))?.textContent ?? '';

  button?.addEventListener('click', async () => {
    const empty = blanks.filter((blank) => answerOf(blank) === '');
    if (empty.length) {
      await showModal({
        type: 'info',
        title: ctx.t('blankEmptyTitle'),
        text: ctx.t('blankEmptyText', empty.length),
      });
      scrollToCenter(empty[0]);
      activate(empty[0]);
      empty[0].focus();
      return;
    }

    const answers = blanks.map((blank) => ({ item_id: idOf(blank), answer: { text: answerOf(blank) } }));

    ctx.setBusy(button, true);
    checking = true;
    let result;
    try {
      result = await ctx.check(answers);
    } catch (error) {
      checking = false;
      ctx.setBusy(button, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(button) });
      return;
    }
    checking = false;
    ctx.setBusy(button, false);

    for (const blank of blanks) {
      const ok = Boolean(result.results?.[idOf(blank)]);
      blank.classList.toggle('is-correct', ok);
      blank.classList.toggle('is-wrong', !ok);
      if (ok) blank.disabled = true;
      const state = ok ? ctx.t('markCorrect') : ctx.t('markWrong');
      const label = blank.dataset.label;
      blank.setAttribute('aria-label', `${label}: ${answerOf(blank)} — ${state}`);
    }
    ctx.tally({ correct: result.correct_count, wrong: result.total - result.correct_count });

    if (result.completed) {
      blanks.forEach((blank) => { blank.disabled = true; });
      words.forEach((chip) => { chip.disabled = true; });
      button.disabled = true;
      activate(null);
    }

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
        activate(wrong);
        wrong.focus();
      }
    }
  });
}
