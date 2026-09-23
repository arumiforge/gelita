/**
 * Kerangka bersama layar tantangan: pemilih mesin + fungsi yang dipakai
 * kelima engine.
 *
 * Payload dibaca dari <script type="application/json" id="challenge-data">
 * (ChallengeService::openNode, tanpa kunci jawaban). Client tidak pernah
 * menghitung benar/salah, skor, bintang, atau unlock: semua tampilan hasil
 * datang dari respons server (aturan 1).
 *
 *   submit(itemId, answer, extra)  → POST /api/attempts/{id}/responses  (pilihan, cari)
 *   check(answers)                 → POST /api/attempts/{id}/check      (puzzle, rumpang, boleh)
 *   complete()                     → POST /api/attempts/{id}/complete → /selesai/{id}
 *   useHint()                      → POST /api/attempts/{id}/hints
 *
 * Satu-satunya jalan keluar adalah tombol Keluar yang dikonfirmasi; tombol
 * Back browser meminta konfirmasi yang sama. Menutup tab tidak memunculkan
 * dialog bawaan browser — cukup event `challenge_abandoned` lewat beacon.
 */
import { $, readJson } from '../core/dom.js';
import { apiRequest, handleApiError } from '../core/api.js';
import { emit, flush, uuid, now } from '../core/events.js';
import { showModal, confirmDialog, isModalOpen } from '../core/modal.js';
import { startTimer } from '../core/timer.js';
import { confetti } from '../core/confetti.js';
import { Sfx } from '../core/audio.js';
import { t } from '../core/config.js';
import { toast } from '../core/toast.js';
import { updateLantern } from './hud.js';

const ENGINES = {
  puzzle: () => import('../engines/puzzle.js'),
  rumpang: () => import('../engines/rumpang.js'),
  boleh: () => import('../engines/boleh.js'),
  pilihan: () => import('../engines/pilihan.js'),
  cari: () => import('../engines/cari.js'),
};

/** Batas pemeriksaan sebelum tombol "Selesaikan dengan jawaban ini" ditawarkan. */
const FINISH_OFFER_AFTER = 3;

export async function bootChallenge() {
  const section = $('[data-screen="challenge"]');
  const data = readJson('challenge-data');
  if (!section || !data || !data.attempt_id) return;

  const engineType = data.node?.engine_type || section.dataset.engine;
  const load = ENGINES[engineType];
  if (!load) return;

  const attemptId = data.attempt_id;
  const nodeId = data.node?.id ?? (Number(section.dataset.node) || null);
  const levelId = data.node?.level_id ?? (Number(section.dataset.levelId) || null);
  const exitUrl = section.dataset.exitUrl;
  const state = { leaving: false, completed: false, correct: 0, wrong: 0 };

  const refs = { levelId, nodeId, attemptId };
  const timer = startTimer($('#timer'), data.elapsed_ms);

  // ---------------------------------------------------------- permintaan

  const post = (path, body) => apiRequest(`/attempts/${attemptId}/${path}`, { method: 'POST', body });

  const report = (error, options = {}) => handleApiError(error, { mapUrl: exitUrl, ...options });

  function submit(itemId, answer, extra = {}) {
    return post('responses', {
      item_id: itemId,
      answer,
      client_event_id: uuid(),
      occurred_at: now(),
      ...extra,
    });
  }

  function check(answers) {
    return post('check', { answers, client_event_id: uuid(), occurred_at: now() });
  }

  async function complete() {
    if (state.completed) return;
    try {
      const result = await post('complete', { client_event_id: uuid(), occurred_at: now() });
      state.completed = true;
      state.leaving = true;
      timer.stop();
      updateLantern(result.shards);
      await flush();
      window.location.href = result.redirect;
    } catch (error) {
      // Masih ada butir yang belum dijawab: satu pesan yang dimengerti anak
      if (error?.code === 'INVALID_PAYLOAD') toast(t('itemsPending'), 'warn');
      else await report(error);
    }
  }

  // ------------------------------------------------------------ tampilan

  const countCorrect = $('#count-correct');
  const countWrong = $('#count-wrong');

  /** Angka ✓/✗ pada bar: dari respons server, bukan hitungan client atas kunci. */
  function tally({ correct = null, wrong = null, addCorrect = 0, addWrong = 0 } = {}) {
    state.correct = correct ?? state.correct + addCorrect;
    state.wrong = wrong ?? state.wrong + addWrong;
    if (countCorrect) countCorrect.textContent = String(state.correct);
    if (countWrong) countWrong.textContent = String(state.wrong);
  }

  /** Tombol pengirim dinonaktifkan selama request berjalan (aturan 7). */
  function setBusy(button, busy) {
    if (!button) return;
    button.disabled = busy;
    button.toggleAttribute('aria-busy', busy);
  }

  /** Galat jaringan saat Periksa: tombol istirahat sebentar + toast. */
  function networkPause(button) {
    toast(t('errNetworkRetry'), 'bad');
    setBusy(button, true);
    setTimeout(() => setBusy(button, false), 3000);
  }

  /**
   * Hasil pemeriksaan batch (puzzle, rumpang, boleh).
   * @returns {Promise<'complete'|'fix'>}
   */
  async function showCheckResult(result, { okTitle, okText, partialTitle, partialText }) {
    if (result.all_correct) {
      Sfx.play('correct');
      confetti();
      await showModal({
        type: 'correct',
        title: okTitle,
        text: okText,
        dismissible: false,
        buttons: [{ label: t('next'), style: 'primary', value: 'next' }],
      });
      await complete();
      return 'complete';
    }

    Sfx.play('wrong');

    // allow_retry = false: jawaban pertama sudah final — tidak ada perbaikan
    if (data.node?.allow_retry === false) {
      await showModal({
        type: 'info',
        title: partialTitle,
        text: [partialText, t('noRetry')],
        dismissible: false,
        buttons: [{ label: t('next'), style: 'primary', value: 'next' }],
      });
      await complete();
      return 'complete';
    }

    const buttons = [{ label: t('fixAnswers'), style: 'primary', value: 'fix' }];
    if ((result.check_count ?? 0) >= FINISH_OFFER_AFTER) {
      buttons.unshift({ label: t('finishAnyway'), style: 'quiet', value: 'finish' });
    }

    const choice = await showModal({ type: 'wrong', title: partialTitle, text: partialText, buttons });
    if (choice === 'finish') {
      await complete();
      return 'complete';
    }
    return 'fix';
  }

  // ------------------------------------------------------------- petunjuk

  const hintButton = $('#btn-hint');
  const hintRefs = Array.isArray(data.hints) ? data.hints.slice() : [];
  const openedHints = new Map(); // hint_id → teks (tidak diminta ulang: tiap permintaan dihitung server)
  let hintCounter = null;

  const ctx = {
    data,
    section,
    attemptId,
    nodeId,
    levelId,
    items: Array.isArray(data.items) ? data.items : [],
    allowRetry: data.node?.allow_retry !== false,
    refs,
    submit,
    check,
    complete,
    tally,
    setBusy,
    networkPause,
    report,
    showCheckResult,
    /** Engine boleh menimpa: id butir yang sedang dikerjakan (untuk petunjuk butir). */
    currentItemId: () => null,
    t,
  };

  function nextHint() {
    const current = ctx.currentItemId();
    const unused = hintRefs.filter((hint) => !openedHints.has(hint.id));
    const bySequence = (a, b) => (a.sequence ?? 0) - (b.sequence ?? 0);
    return unused.filter((hint) => current !== null && hint.item_id === current).sort(bySequence)[0]
      ?? unused.filter((hint) => hint.item_id === null).sort(bySequence)[0]
      ?? null;
  }

  async function useHint() {
    const hint = nextHint();

    if (!hint) {
      const texts = Array.from(openedHints.values());
      await showModal({
        type: 'hint',
        title: t('hintTitle'),
        text: texts.length ? [t('hintAllOpened'), ...texts] : t('hintNone'),
      });
      return;
    }

    const ok = await confirmDialog({
      title: t('hintTitle'),
      text: t('hintPenalty'),
      confirmLabel: t('hintOpen'),
      cancelLabel: t('cancel'),
    });
    if (!ok) return;

    setBusy(hintButton, true);
    try {
      const result = await post('hints', {
        hint_id: hint.id,
        ...(hint.item_id ? { item_id: hint.item_id } : {}),
        client_event_id: uuid(),
      });
      openedHints.set(hint.id, result.text);
      if (hintCounter) hintCounter.textContent = String(result.hint_count);
      await showModal({ type: 'hint', title: t('hintTitle'), text: result.text });
    } catch (error) {
      await report(error);
    } finally {
      setBusy(hintButton, false);
    }
  }

  // Tombol 💡 hanya tampil bila attempt ini punya petunjuk (hints_count > 0)
  if (hintButton && hintRefs.length > 0) {
    hintButton.hidden = false;
    hintCounter = document.createElement('b');
    hintCounter.className = 'num hint-count';
    hintCounter.setAttribute('aria-label', t('hintUsed'));
    hintCounter.textContent = '0';
    hintButton.append(' ', hintCounter);
    hintButton.addEventListener('click', useHint);
  }

  // -------------------------------------------------------------- keluar

  async function confirmExit() {
    if (isModalOpen()) return false;
    const ok = await confirmDialog({
      title: t('exitTitle'),
      text: t('exitText'),
      confirmLabel: t('exitYes'),
      cancelLabel: t('exitNo'),
      danger: true,
    });
    if (!ok) return false;

    state.leaving = true;
    timer.stop();
    try {
      await apiRequest(`/attempts/${attemptId}/abandon`, { method: 'POST', body: { reason: 'user_exit' } });
    } catch {
      /* tetap keluar: attempt yang masih terbuka dapat dilanjutkan nanti */
    }
    await flush();
    window.location.href = exitUrl;
    return true;
  }

  const exit = $('details.challenge-exit', section);
  if (exit) {
    $('summary', exit)?.addEventListener('click', (event) => {
      event.preventDefault();
      confirmExit();
    });
    $('[data-exit-confirm]', exit)?.addEventListener('click', (event) => {
      event.preventDefault();
      confirmExit();
    });
  }

  // Tombol Back browser: satu entri penjaga di riwayat
  history.pushState({ gelitaChallenge: attemptId }, '', window.location.href);
  window.addEventListener('popstate', async () => {
    if (state.leaving) return;
    const left = await confirmExit();
    if (!left) history.pushState({ gelitaChallenge: attemptId }, '', window.location.href);
  });

  // Ganti bahasa memuat ulang halaman yang sama: bukan meninggalkan tantangan
  document.querySelector('form.lang-switch')?.addEventListener('submit', () => { state.leaving = true; });

  window.addEventListener('pagehide', () => {
    if (state.leaving || state.completed) return;
    emit('challenge_abandoned', { ...refs, payload: { via: 'pagehide', attempt_open: true } });
    flush(true);
  });

  // ------------------------------------------------------------- mesin

  if (data.resumed) {
    // Pembukaan pertama sudah dicatat server; yang dicatat di sini melanjutkan attempt
    emit('challenge_opened', { ...refs, payload: { resumed: true, attempt_no: data.attempt_no ?? null } });
  }

  Sfx.music('challenge');

  const engine = await load();
  engine.mount(ctx);
}
