/**
 * Engine `puzzle` (Susun Gambar) on the markup of game/challenge/puzzle.php.
 *
 * An n × n board of SVG pieces (square or jigsaw). Three ways to swap two
 * pieces, all through the same swap():
 * - tap two pieces (the main path, the most reliable one on tablets);
 * - drag a piece onto another (core/drag.js, pointer events for mouse, touch,
 *   and pen alike; display plan Phase F3.1). The drag starts after 8 px, the
 *   piece gets `.is-dragging` and its `.piece-art` follows the pointer with an
 *   inline transform, the piece under the pointer gets `.is-drop-target`.
 *   Dropping on the same piece or outside the board cancels; a press that
 *   never moves 8 px stays a tap;
 * - keyboard: arrows move between pieces, Enter/Space select and swap.
 * Nothing moves while a check is running or after the picture is solved.
 * Slots (buttons) stay in place; the piece art (.piece-art) and data-piece
 * move. Pieces carry no numbers; the screen reader label names the slot
 * position. The client only swaps positions and judges nothing. Every swap
 * updates the input times (`ctx.selected`, no event since 29 §5.1), whichever way it
 * was made.
 *
 * "Cek jawabanku" → POST /check { answers: [{ item_id, answer: { order } }] }.
 * `detail[item].pieces_correct` (and `misplaced` for the picture) come from
 * the server. Once the picture is whole, its caption (#puzzle-caption) shows.
 *
 * Third try used up (`completed` + `tries_exhausted`, Dok 2 §5.3): the pieces
 * move to the solved layout in a 600 ms animation (the solved order is
 * public anyway: pieces 0..n−1 from the top left), the caption shows, and
 * only then the "Kesempatanmu sudah habis" modal.
 *
 * Pieces marked ✗ (`.is-misplaced`) are announced to screen readers too: the
 * slot label gains "belum di tempatnya" until the piece is swapped. A reload
 * after a check that was not whole restores the ✓/✗ tally (0 / 1) from
 * `tries_used`, the way the other engines restore it from `state`.
 */
import { $, $$, announce, reducedMotion } from '../core/dom.js';
import { Sfx } from '../core/audio.js';
import { initDrag, dragLocalScale } from '../core/drag.js';

const ASSEMBLE_MS = 600;

function pulse(node) {
  if (reducedMotion() || !node.animate) return;
  node.animate([{ transform: 'scale(.9)' }, { transform: 'scale(1)' }], { duration: 180, easing: 'ease-out' });
}

// ------------------------------------------------------------------ arrange

function mountArrange(ctx, wrap) {
  const board = $('#puzzle-board', wrap);
  const button = $('#btn-check', wrap);
  const caption = $('#puzzle-caption', wrap);
  const itemId = Number(wrap.dataset.item);
  const pieces = $$('.piece', board);
  const grid = Number(board.dataset.grid) || Math.round(Math.sqrt(pieces.length)) || 3;
  let selected = null;
  let solved = false;
  let checking = false; // susunan dibekukan selama /check berjalan

  const slotOrder = () => pieces
    .slice()
    .sort((a, b) => Number(a.dataset.slot) - Number(b.dataset.slot));

  // Label asli tiap slot ("Keping baris 1, kolom 2"); tanda ✗ menambah keterangannya
  pieces.forEach((piece) => { piece.dataset.label = piece.getAttribute('aria-label') || ''; });
  const markMisplaced = (piece, on) => {
    piece.classList.toggle('is-misplaced', on);
    piece.setAttribute('aria-label', on ? `${piece.dataset.label} — ${ctx.t('pieceMisplaced')}` : piece.dataset.label);
  };

  const select = (piece) => {
    selected?.classList.remove('is-selected');
    selected?.setAttribute('aria-pressed', 'false');
    selected = piece;
    piece?.classList.add('is-selected');
    piece?.setAttribute('aria-pressed', 'true');
  };

  /**
   * Tukar isi dua slot: gambar keping dan nomor keping (data-piece) pindah,
   * slot dan labelnya (letak baris/kolom) tetap; fokus tidak berpindah.
   */
  const swap = (a, b) => {
    if (a === b) return;
    const artA = $('.piece-art', a);
    const artB = $('.piece-art', b);
    if (artA && artB) {
      a.append(artB);
      b.append(artA);
    }
    [a.dataset.piece, b.dataset.piece] = [b.dataset.piece, a.dataset.piece];

    markMisplaced(a, false);
    markMisplaced(b, false);
    pulse(a);
    pulse(b);
    Sfx.play('click');
    announce(ctx.t('piecesSwapped', a.dataset.pos ?? '', b.dataset.pos ?? ''));
    ctx.selected(itemId, { action: 'swap', value: null, from: Number(a.dataset.slot), to: Number(b.dataset.slot) });
  };

  const tap = (piece) => {
    if (solved || checking) return;
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

  // Drag a piece onto another to swap them (core/drag.js: mouse, touch, and
  // pen alike). The piece's art follows the pointer; the piece under it is
  // highlighted. Dropping on the same piece or outside the board cancels.
  let dropTarget = null;
  const setDropTarget = (piece) => {
    if (piece === dropTarget) return;
    dropTarget?.classList.remove('is-drop-target');
    dropTarget = piece;
    dropTarget?.classList.add('is-drop-target');
  };
  const endDrag = (piece) => {
    piece.classList.remove('is-dragging');
    const art = $('.piece-art', piece);
    if (art) art.style.transform = '';
    setDropTarget(null);
  };
  let artScale = 1;
  initDrag(board, {
    selector: '.piece',
    canStart: (piece) => !solved && !checking && !piece.disabled,
    onStart: (piece) => {
      select(null);
      artScale = dragLocalScale(piece);
      piece.classList.add('is-dragging');
    },
    onMove: (piece, { dx, dy, target }) => {
      const art = $('.piece-art', piece);
      if (art) art.style.transform = `translate(${dx / artScale}px, ${dy / artScale}px)`;
      setDropTarget(target);
    },
    onDrop: (piece, target) => {
      endDrag(piece);
      if (!target || target === piece || solved || checking) return;
      swap(piece, target);
    },
    onCancel: endDrag,
  });

  const lock = () => {
    pieces.forEach((piece) => {
      piece.classList.add('is-locked');
      markMisplaced(piece, false);
      piece.classList.remove('is-selected');
      piece.disabled = true;
    });
    button.disabled = true;
  };

  /** Susun semua keping ke posisi utuh (FLIP, 600 ms), lalu tampilkan keterangan. */
  const assemble = async () => {
    solved = true;
    select(null);
    const ordered = slotOrder();
    const arts = new Map(); // nomor keping → .piece-art
    const before = new Map();
    ordered.forEach((piece) => {
      const art = $('.piece-art', piece);
      if (!art) return;
      arts.set(Number(piece.dataset.piece), art);
      before.set(art, art.getBoundingClientRect());
    });
    ordered.forEach((piece, slot) => {
      const art = arts.get(slot);
      if (art) piece.append(art);
      piece.dataset.piece = String(slot);
    });
    lock();

    if (!reducedMotion()) {
      const runs = [];
      for (const [art, from] of before) {
        const to = art.getBoundingClientRect();
        const dx = from.left - to.left;
        const dy = from.top - to.top;
        if (!art.animate || (dx === 0 && dy === 0)) continue;
        runs.push(art.animate(
          [{ transform: `translate(${dx}px, ${dy}px)` }, { transform: 'translate(0, 0)' }],
          { duration: ASSEMBLE_MS, easing: 'ease-in-out' },
        ).finished.catch(() => {}));
      }
      await Promise.all(runs);
    }

    board.classList.add('is-solved');
    if (caption) caption.hidden = false;
  };

  ctx.currentItemId = () => itemId;

  // Muat ulang sesudah cek yang belum utuh: satu gambar, jadi ✓ 0 / ✗ 1
  if (Number(ctx.data.tries_used) > 0) ctx.tally({ correct: 0, wrong: 1 });

  button?.addEventListener('click', async () => {
    if (solved) return;
    select(null);
    const order = slotOrder().map((piece) => Number(piece.dataset.piece));

    ctx.setBusy(button, true);
    checking = true;
    let result;
    try {
      result = await ctx.check([{ item_id: itemId, answer: { order } }]);
    } catch (error) {
      checking = false;
      ctx.setBusy(button, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(button) });
      return;
    }
    checking = false;
    ctx.setBusy(button, false);

    const detail = result.detail?.[itemId] ?? {};
    const ok = Boolean(result.results?.[itemId]);
    ctx.tally({ correct: result.correct_count, wrong: result.total - result.correct_count });

    if (ok) {
      solved = true;
      board.classList.add('is-solved');
      lock();
      if (caption) caption.hidden = false;
    } else if (result.completed) {
      lock(); // kesempatan habis: animasi gambar utuh dijalankan sebelum modal (beforeExhausted)
    } else {
      const misplaced = new Set((detail.misplaced ?? []).map(Number));
      slotOrder().forEach((piece, slot) => markMisplaced(piece, misplaced.has(slot)));
    }

    const wrongCount = detail.pieces_correct !== undefined ? pieces.length - Number(detail.pieces_correct) : null;
    const captionText = caption ? $('p', caption)?.textContent ?? '' : '';
    const outcome = await ctx.showCheckResult(result, {
      okTitle: ctx.t('puzzleOkTitle'),
      okText: captionText ? [ctx.t('puzzleOkText'), captionText] : ctx.t('puzzleOkText'),
      partialTitle: ctx.t('puzzleWrongTitle'),
      partialText: wrongCount !== null ? ctx.t(wrongCount === 1 ? 'puzzleWrongTextOne' : 'puzzleWrongText', wrongCount) : ctx.t('puzzleWrongPlain'),
      exhaustedText: captionText ? [ctx.t('triesExhaustedPuzzle'), captionText] : ctx.t('triesExhaustedPuzzle'),
      beforeExhausted: assemble,
    });

    if (outcome === 'fix') ($('.piece.is-misplaced', board) || pieces[0])?.focus();
  });
}

export function mount(ctx) {
  const wrap = $('#puzzle-wrap', ctx.section);
  if (!wrap) return;
  mountArrange(ctx, wrap);
}
