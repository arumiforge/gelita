/**
 * Engine `puzzle` — dua mode pada markup game/challenge/puzzle.php:
 *
 * arrange  Ketuk dua keping untuk menukarnya (wajib, paling andal di tablet);
 *          seret-lepas HTML5 sebagai tambahan; panah untuk berpindah keping,
 *          Enter/Spasi untuk memilih/menukar. Client hanya menukar posisi —
 *          tidak menilai apa pun.
 * ordering Daftar kartu teks (Wonosobo node 1): tombol ▲▼ (wajib untuk
 *          keyboard) dan seret-lepas.
 *
 * "Periksa" → POST /check { answers: [{ item_id, answer: { order } }] }.
 * `detail[item].pieces_correct` (dan `misplaced` untuk gambar, yang susunan
 * benarnya memang publik 1–9) dihitung server.
 */
import { $, $$, announce, reducedMotion } from '../core/dom.js';
import { Sfx } from '../core/audio.js';

function pulse(node) {
  if (reducedMotion() || !node.animate) return;
  node.animate([{ transform: 'scale(.9)' }, { transform: 'scale(1)' }], { duration: 180, easing: 'ease-out' });
}

// ------------------------------------------------------------------ arrange

function mountArrange(ctx, wrap) {
  const board = $('#puzzle-board', wrap);
  const button = $('#btn-check', wrap);
  const itemId = Number(wrap.dataset.item);
  const pieces = $$('.piece', board);
  const grid = Math.round(Math.sqrt(pieces.length)) || 3;
  let selected = null;
  let solved = false;

  const slotOrder = () => pieces
    .slice()
    .sort((a, b) => Number(a.dataset.slot) - Number(b.dataset.slot));

  const select = (piece) => {
    selected?.classList.remove('is-selected');
    selected?.setAttribute('aria-pressed', 'false');
    selected = piece;
    piece?.classList.add('is-selected');
    piece?.setAttribute('aria-pressed', 'true');
  };

  /** Tukar isi dua slot (slot tetap di tempat, fokus tidak berpindah). */
  const swap = (a, b) => {
    if (a === b) return;
    for (const prop of ['--px', '--py']) {
      const tmp = a.style.getPropertyValue(prop);
      a.style.setProperty(prop, b.style.getPropertyValue(prop));
      b.style.setProperty(prop, tmp);
    }
    [a.dataset.piece, b.dataset.piece] = [b.dataset.piece, a.dataset.piece];
    const labelA = a.getAttribute('aria-label');
    a.setAttribute('aria-label', b.getAttribute('aria-label'));
    b.setAttribute('aria-label', labelA);
    const noA = $('.piece-no', a);
    const noB = $('.piece-no', b);
    if (noA && noB) [noA.textContent, noB.textContent] = [noB.textContent, noA.textContent];

    a.classList.remove('is-misplaced');
    b.classList.remove('is-misplaced');
    pulse(a);
    pulse(b);
    Sfx.play('click');
    announce(ctx.t('piecesSwapped', noB?.textContent ?? '', noA?.textContent ?? ''));
  };

  const tap = (piece) => {
    if (solved) return;
    if (!selected) select(piece);
    else if (selected === piece) select(null);
    else {
      swap(selected, piece);
      select(null);
    }
  };

  pieces.forEach((piece) => piece.setAttribute('aria-pressed', 'false'));
  board.addEventListener('click', (event) => {
    const piece = event.target.closest('.piece');
    if (piece) tap(piece);
  });

  // Panah: berpindah fokus di dalam kisi (roving focus)
  board.addEventListener('keydown', (event) => {
    const piece = event.target.closest('.piece');
    if (!piece) return;
    const ordered = slotOrder();
    const index = ordered.indexOf(piece);
    const moves = { ArrowLeft: -1, ArrowRight: 1, ArrowUp: -grid, ArrowDown: grid };
    if (!(event.key in moves)) {
      if (event.key === 'Escape') select(null);
      return;
    }
    event.preventDefault();
    const next = ordered[index + moves[event.key]];
    next?.focus();
  });

  // Seret-lepas sebagai tambahan
  let dragged = null;
  pieces.forEach((piece) => { piece.draggable = true; });
  board.addEventListener('dragstart', (event) => {
    const piece = event.target.closest('.piece');
    if (!piece || solved) return;
    dragged = piece;
    piece.classList.add('is-dragging');
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', piece.dataset.slot);
  });
  board.addEventListener('dragover', (event) => {
    if (dragged && event.target.closest('.piece')) event.preventDefault();
  });
  board.addEventListener('drop', (event) => {
    const target = event.target.closest('.piece');
    if (!dragged || !target) return;
    event.preventDefault();
    swap(dragged, target);
    select(null);
  });
  board.addEventListener('dragend', () => {
    dragged?.classList.remove('is-dragging');
    dragged = null;
  });

  ctx.currentItemId = () => itemId;

  button?.addEventListener('click', async () => {
    if (solved) return;
    select(null);
    const order = slotOrder().map((piece) => Number(piece.dataset.piece));

    ctx.setBusy(button, true);
    let result;
    try {
      result = await ctx.check([{ item_id: itemId, answer: { order } }]);
    } catch (error) {
      ctx.setBusy(button, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(button) });
      return;
    }
    ctx.setBusy(button, false);

    const detail = result.detail?.[itemId] ?? {};
    const ok = Boolean(result.results?.[itemId]);
    ctx.tally({ correct: result.correct_count, wrong: result.total - result.correct_count });

    if (ok) {
      solved = true;
      board.classList.add('is-solved');
      pieces.forEach((piece) => {
        piece.classList.add('is-locked');
        piece.classList.remove('is-misplaced');
        piece.disabled = true;
      });
      button.disabled = true;
    } else {
      const misplaced = new Set((detail.misplaced ?? []).map(Number));
      slotOrder().forEach((piece, slot) => piece.classList.toggle('is-misplaced', misplaced.has(slot)));
    }

    const wrongCount = detail.pieces_correct !== undefined ? pieces.length - Number(detail.pieces_correct) : null;
    const outcome = await ctx.showCheckResult(result, {
      okTitle: ctx.t('puzzleOkTitle'),
      okText: ctx.t('puzzleOkText'),
      partialTitle: ctx.t('puzzleWrongTitle'),
      partialText: wrongCount !== null ? ctx.t('puzzleWrongText', wrongCount) : ctx.t('puzzleWrongPlain'),
    });

    if (outcome === 'fix') ($('.piece.is-misplaced', board) || pieces[0])?.focus();
  });
}

// ----------------------------------------------------------------- ordering

function mountOrdering(ctx, wrap) {
  const list = $('#order-list', wrap);
  const button = $('#btn-check', wrap);
  const itemId = Number(wrap.dataset.item);
  let solved = false;

  const cards = () => $$('.order-card', list);

  const renumber = () => {
    cards().forEach((card, index) => {
      const no = $('.order-no', card);
      if (no) no.textContent = String(index + 1);
      card.classList.remove('is-correct', 'is-wrong');
    });
  };

  const move = (card, direction, focusSelector) => {
    if (solved) return;
    const sibling = direction === 'up' ? card.previousElementSibling : card.nextElementSibling;
    if (!sibling) return;
    if (direction === 'up') sibling.before(card);
    else sibling.after(card);
    renumber();
    pulse(card);
    Sfx.play('click');
    const position = cards().indexOf(card) + 1;
    announce(ctx.t('cardMoved', position, cards().length));
    // Tetap fokus pada tombol yang sama; bila di ujung, pindah ke tombol arah sebaliknya
    const same = $(focusSelector, card);
    const edge = (direction === 'up' && position === 1) || (direction === 'down' && position === cards().length);
    (edge ? $(`[data-move="${direction === 'up' ? 'down' : 'up'}"]`, card) : same)?.focus();
  };

  list.addEventListener('click', (event) => {
    const control = event.target.closest('[data-move]');
    if (!control) return;
    move(control.closest('.order-card'), control.dataset.move, `[data-move="${control.dataset.move}"]`);
  });

  // Seret-lepas kartu
  let dragged = null;
  list.addEventListener('dragstart', (event) => {
    const card = event.target.closest('.order-card');
    if (!card || solved) return;
    dragged = card;
    card.classList.add('is-dragging');
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', card.dataset.piece);
  });
  list.addEventListener('dragover', (event) => {
    const over = event.target.closest('.order-card');
    if (!dragged || !over || over === dragged) return;
    event.preventDefault();
    const rect = over.getBoundingClientRect();
    if (event.clientY < rect.top + rect.height / 2) over.before(dragged);
    else over.after(dragged);
  });
  list.addEventListener('drop', (event) => event.preventDefault());
  list.addEventListener('dragend', () => {
    if (!dragged) return;
    dragged.classList.remove('is-dragging');
    dragged = null;
    renumber();
  });

  ctx.currentItemId = () => itemId;

  button?.addEventListener('click', async () => {
    if (solved) return;
    const order = cards().map((card) => card.dataset.piece);

    ctx.setBusy(button, true);
    let result;
    try {
      result = await ctx.check([{ item_id: itemId, answer: { order } }]);
    } catch (error) {
      ctx.setBusy(button, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(button) });
      return;
    }
    ctx.setBusy(button, false);

    const ok = Boolean(result.results?.[itemId]);
    const detail = result.detail?.[itemId] ?? {};
    ctx.tally({ correct: result.correct_count, wrong: result.total - result.correct_count });

    if (ok) {
      solved = true;
      cards().forEach((card) => {
        card.classList.add('is-correct');
        card.draggable = false;
        $$('button', card).forEach((b) => { b.disabled = true; });
      });
      button.disabled = true;
    }

    const outcome = await ctx.showCheckResult(result, {
      okTitle: ctx.t('orderOkTitle'),
      okText: ctx.t('orderOkText'),
      partialTitle: ctx.t('orderWrongTitle'),
      partialText: detail.pieces_correct !== undefined
        ? ctx.t('orderWrongText', detail.pieces_correct, cards().length)
        : ctx.t('orderWrongPlain'),
    });

    if (outcome === 'fix') $('[data-move]', list)?.focus();
  });
}

export function mount(ctx) {
  const wrap = $('#puzzle-wrap', ctx.section);
  if (!wrap) return;
  if (wrap.dataset.mode === 'ordering') mountOrdering(ctx, wrap);
  else mountArrange(ctx, wrap);
}
