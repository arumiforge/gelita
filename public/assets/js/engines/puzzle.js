/**
 * Engine `puzzle` — Susun Gambar pada markup game/challenge/puzzle.php.
 *
 * Papan n × n keping SVG (kotak atau jigsaw). Ketuk dua keping untuk
 * menukarnya (wajib, paling andal di tablet); seret-lepas HTML5 sebagai
 * tambahan; panah untuk berpindah keping, Enter/Spasi untuk memilih/menukar.
 * Slot (tombol) tetap di tempat; yang berpindah adalah gambar keping
 * (.piece-art) beserta data-piece. Keping tidak bernomor; label pembaca layar
 * menyebut letak slot. Client hanya menukar posisi — tidak menilai apa pun.
 * Setiap tukar keping dicatat sebagai event `answer_selected` (action swap).
 *
 * "Cek jawabanku" → POST /check { answers: [{ item_id, answer: { order } }] }.
 * `detail[item].pieces_correct` (dan `misplaced` untuk gambar) dihitung
 * server. Setelah gambar utuh, keterangannya (#puzzle-caption) ditampilkan.
 *
 * Kesempatan ketiga habis (`completed` + `tries_exhausted`, Dok 2 §5.3):
 * keping disusun ke posisi utuh dengan animasi 600 ms (susunan benar memang
 * publik: keping 0..n−1 dari kiri atas), keterangan gambar tampil, baru
 * kemudian modal "Kesempatanmu sudah habis".
 */
import { $, $$, announce, reducedMotion } from '../core/dom.js';
import { Sfx } from '../core/audio.js';

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

    a.classList.remove('is-misplaced');
    b.classList.remove('is-misplaced');
    pulse(a);
    pulse(b);
    Sfx.play('click');
    announce(ctx.t('piecesSwapped', a.dataset.pos ?? '', b.dataset.pos ?? ''));
    ctx.selected(itemId, { action: 'swap', value: null, from: Number(a.dataset.slot), to: Number(b.dataset.slot) });
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

  const lock = () => {
    pieces.forEach((piece) => {
      piece.classList.add('is-locked');
      piece.classList.remove('is-misplaced', 'is-selected');
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
      lock();
      if (caption) caption.hidden = false;
    } else if (result.completed) {
      lock(); // kesempatan habis: animasi gambar utuh dijalankan sebelum modal (beforeExhausted)
    } else {
      const misplaced = new Set((detail.misplaced ?? []).map(Number));
      slotOrder().forEach((piece, slot) => piece.classList.toggle('is-misplaced', misplaced.has(slot)));
    }

    const wrongCount = detail.pieces_correct !== undefined ? pieces.length - Number(detail.pieces_correct) : null;
    const captionText = caption ? $('p', caption)?.textContent ?? '' : '';
    const outcome = await ctx.showCheckResult(result, {
      okTitle: ctx.t('puzzleOkTitle'),
      okText: captionText ? [ctx.t('puzzleOkText'), captionText] : ctx.t('puzzleOkText'),
      partialTitle: ctx.t('puzzleWrongTitle'),
      partialText: wrongCount !== null ? ctx.t('puzzleWrongText', wrongCount) : ctx.t('puzzleWrongPlain'),
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
