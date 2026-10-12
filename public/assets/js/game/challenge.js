/**
 * Kerangka bersama layar tantangan: pemilih mesin + fungsi yang dipakai
 * keempat engine.
 *
 * Payload dibaca dari <script type="application/json" id="challenge-data">
 * (ChallengeService::openNode, tanpa kunci jawaban). Client tidak pernah
 * menghitung benar/salah, skor, bintang, atau unlock: semua tampilan hasil
 * datang dari respons server (aturan 1).
 *
 *   submit(itemId, answer, extra)  → POST /api/attempts/{id}/responses  (cari)
 *   check(answers)                 → POST /api/attempts/{id}/check      (puzzle, rumpang, boleh),
 *                                    dengan `check_no` = cek ke-berapa menurut layar ini
 *   selected(itemId, change)       → event `answer_selected` + waktu isian per soal
 *
 * Waktu isian per soal (Dok 1 §12.5): ms sejak tantangan dibuka
 * (performance.now()) saat siswa pertama kali dan terakhir kali mengisi/
 * mengubah soal itu dalam satu percobaan; ikut dikirim pada setiap /check
 * sebagai `first_input_ms` / `last_input_ms` (satu baris item_tries), lalu
 * dihitung ulang untuk percobaan berikutnya. Soal yang tidak disentuh dalam
 * satu percobaan dikirim tanpa waktu. Hanya catatan proses — urutan dan
 * waktu resmi tetap occurred_at server.
 *   finish(result)                 → pengerjaan ditutup server: tetap di layar ini, tombol
 *                                    Lanjut (#btn-next) membawa ke `redirect` dari /check atau /responses
 *   useHint()                      → POST /api/attempts/{id}/hints
 *
 * Aturan 3 kesempatan (Dok 2 §5.3): server yang menghitung dan menutup
 * pengerjaan. /check dan /responses membalas `tries_left`, `completed`,
 * `completion_reason`, dan `redirect`; klien hanya menampilkan penanda
 * kesempatan (●●○) dan modal yang sesuai.
 *
 * After the closing modal the student stays on the finished screen (e.g. the
 * whole picture in Susun Gambar, the marked blanks or cards) and only moves
 * to `redirect` by tapping Lanjut (#btn-next, rendered hidden by each engine
 * view). The check, hint, and exit buttons are hidden then: the attempt is
 * already closed. POST /complete is only a fallback when a closing response
 * has no `redirect` (that endpoint is idempotent). An attempt already changed
 * by another tab or a duplicate request (409 ATTEMPT_CHANGED) → straight to
 * `redirect` when there is one, otherwise reload.
 *
 * Satu-satunya jalan keluar adalah tombol Keluar yang dikonfirmasi; tombol
 * Back browser meminta konfirmasi yang sama. Menutup tab tidak memunculkan
 * dialog bawaan browser — cukup event `challenge_abandoned` lewat beacon.
 */
import { $, $$, readJson } from '../core/dom.js';
import { apiRequest, handleApiError, ApiError } from '../core/api.js';
import { emit, flush, uuid, now } from '../core/events.js';
import { showModal, confirmDialog, isModalOpen } from '../core/modal.js';
import { startTimer } from '../core/timer.js';
import { confetti } from '../core/confetti.js';
import { Sfx } from '../core/audio.js';
import { t } from '../core/config.js';
import { toast } from '../core/toast.js';
import { updateLantern } from './hud.js';
import { trackReading } from '../core/device.js';

const ENGINES = {
  puzzle: () => import('../engines/puzzle.js'),
  rumpang: () => import('../engines/rumpang.js'),
  boleh: () => import('../engines/boleh.js'),
  cari: () => import('../engines/cari.js'),
};

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
  const levelCode = section.dataset.level || null; // payload overlay_opened `level` (kode, seperti "kenal")
  const exitUrl = section.dataset.exitUrl;
  const state = { leaving: false, completed: false, correct: 0, wrong: 0 };

  const refs = { levelId, nodeId, attemptId };
  const timer = startTimer($('#timer'), data.elapsed_ms);

  // ------------------------------------------------------ waktu isian

  const openedAt = performance.now();
  const inputTimes = new Map(); // itemId → { first, last } (ms sejak tantangan dibuka)
  const sinceOpen = () => Math.max(0, Math.round(performance.now() - openedAt));

  /** Siswa mengisi/mengubah satu soal sebelum mengecek (event answer_selected). */
  function selected(itemId, change = {}) {
    const ms = sinceOpen();
    const times = inputTimes.get(itemId) ?? { first: ms, last: ms };
    times.last = ms;
    inputTimes.set(itemId, times);
    emit('answer_selected', { ...refs, itemId, payload: { ...change, t_ms: ms } });
  }

  // ---------------------------------------------------------- permintaan

  const post = (path, body) => apiRequest(`/attempts/${attemptId}/${path}`, { method: 'POST', body });

  const report = async (error, options = {}) => {
    if (await ruleError(error)) return error.code;
    return handleApiError(error, { mapUrl: exitUrl, ...options });
  };

  /**
   * Server sudah menutup pengerjaan: menutup tab saat modal hasil akhir masih
   * tampil bukan meninggalkan tantangan (tanpa `challenge_abandoned`).
   */
  const closed = (result) => {
    if (result?.completed) state.leaving = true;
    return result;
  };

  async function submit(itemId, answer, extra = {}) {
    return closed(await post('responses', {
      item_id: itemId,
      answer,
      client_event_id: uuid(),
      occurred_at: now(),
      ...extra,
    }));
  }

  // Cek yang sudah dipakai menurut layar ini; dikirim sebagai `check_no` agar
  // cek dari tab lama (tampilan usang) ditolak ATTEMPT_CHANGED, bukan dihitung cek baru
  let checksUsed = Number(data.tries_used) || 0;

  async function check(answers) {
    // Waktu isian per percobaan: diambil sebelum request, jadi isian selama
    // request berjalan sudah masuk hitungan percobaan berikutnya
    const sent = new Map();
    const rows = answers.map((row) => {
      const times = inputTimes.get(row.item_id);
      if (!times) return row;
      sent.set(row.item_id, times);
      inputTimes.delete(row.item_id);
      return { ...row, first_input_ms: times.first, last_input_ms: times.last };
    });
    try {
      const result = await post('check', { answers: rows, check_no: checksUsed + 1, client_event_id: uuid(), occurred_at: now() });
      checksUsed = Number(result.check_count) || checksUsed + 1;
      return closed(result);
    } catch (error) {
      // Gagal: waktunya dikembalikan untuk pengiriman berikutnya (first lama, last terbaru)
      for (const [itemId, times] of sent) {
        const newer = inputTimes.get(itemId);
        inputTimes.set(itemId, { first: times.first, last: newer ? newer.last : times.last });
      }
      throw error;
    }
  }

  /** The server has closed the attempt: stop the clock, update the lantern. */
  function markClosed(shards = null) {
    state.completed = true;
    state.leaving = true;
    timer.stop();
    if (shards !== null && shards !== undefined) updateLantern(shards);
  }

  /** Pindah ke layar hasil; pengerjaan sudah ditutup server. */
  async function leaveTo(redirect, shards = null) {
    markClosed(shards);
    await flush();
    window.location.href = redirect;
  }

  /** Cadangan: respons selesai tanpa `redirect` → POST /complete (idempoten). */
  let completing = false;
  async function complete() {
    if (completing) return;
    completing = true;
    try {
      const result = await post('complete', { client_event_id: uuid(), occurred_at: now() });
      await leaveTo(result.redirect, result.shards);
    } catch (error) {
      completing = false;
      if (error?.code === 'TRIES_REMAINING') toast(t('itemsPending'), 'warn');
      else await report(error);
    }
  }

  const nextButton = $('#btn-next', section);
  // Button of the closing modal: "Oke" when Lanjut waits on the screen
  const closingLabel = nextButton ? t('ok') : t('next');

  /** Go on to the result screen of a closed attempt. */
  async function proceed(result) {
    if (result?.redirect) await leaveTo(result.redirect, result.shards);
    else await complete();
  }

  /**
   * Stay on the finished screen: the check, hint, and exit buttons give way
   * to Lanjut, which moves on to the result screen.
   */
  function hold(result) {
    markClosed(result?.shards);
    for (const node of [$('#btn-check', section), hintButton, $('details.challenge-exit', section)]) {
      if (node) node.hidden = true;
    }
    nextButton.hidden = false;
    nextButton.addEventListener('click', async () => {
      setBusy(nextButton, true);
      await proceed(result);
      setBusy(nextButton, false); // still here only when the fallback failed
    });
    nextButton.focus({ preventScroll: true });
  }

  /** Ikuti penutupan dari respons /check atau /responses. */
  async function finish(result) {
    if (state.completed) return;
    if (nextButton) hold(result);
    else await proceed(result);
  }

  /**
   * Galat aturan kesempatan dari server (mis. tab lain sudah memakai
   * kesempatan terakhir — TRIES_EXHAUSTED — atau sudah mengubah pengerjaan
   * ini lebih dulu, termasuk request ganda/ulang — ATTEMPT_CHANGED):
   * pengerjaan yang sudah selesai (`redirect`) → layar hasilnya; selain itu,
   * juga ATTEMPT_CLOSED dan petunjuk yang sudah tertutup (CLUE_CLOSED) →
   * muat ulang agar tampilan ikut payload. Muat ulang ini bukan meninggalkan
   * tantangan: tanpa event `challenge_abandoned`.
   * @returns {Promise<boolean>} true bila galatnya sudah ditangani
   */
  async function ruleError(error) {
    if (!(error instanceof ApiError)) return false;
    if (error.code === 'TRIES_EXHAUSTED' || error.code === 'ATTEMPT_CHANGED') {
      if (error.payload?.redirect) {
        await leaveTo(error.payload.redirect);
      } else {
        state.leaving = true;
        window.location.reload();
      }
      return true;
    }
    if (error.code === 'CLUE_CLOSED' || error.code === 'ATTEMPT_CLOSED') {
      state.leaving = true;
      window.location.reload();
      return true;
    }
    return false;
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

  // ------------------------------------------------- penanda kesempatan

  const maxTries = Math.max(1, Number(data.max_tries) || 3);

  /**
   * Penanda 3 bulatan (●●○; terisi = sisa) — markup dari server
   * (_frame.php untuk engine batch, cari.php per petunjuk). Nilainya selalu
   * dari server: `max_tries − tries_used` saat dimuat, lalu `tries_left`.
   */
  function setTries(node, left, label = null) {
    if (!node) return;
    const remaining = Math.max(0, Math.min(maxTries, Number(left) || 0));
    node.dataset.left = String(remaining);
    $$('.tries-dot', node).forEach((dot, index) => dot.classList.toggle('is-left', index < remaining));
    // data-label = "Kesempatan: {0} dari {1}" (dirender server dari Game.triesLabel + Game.triesOf)
    const template = label ?? node.dataset.label ?? '';
    if (template) node.setAttribute('aria-label', template.replace('{0}', String(remaining)).replace('{1}', String(maxTries)));
  }

  const triesChip = $('#tries', section);
  if (triesChip) setTries(triesChip, maxTries - (Number(data.tries_used) || 0));

  /** Kalimat sisa kesempatan untuk modal "Belum semuanya tepat". */
  const triesText = (left) => (Number(left) === 1 ? t('triesLast') : t('triesLeft', left));

  /**
   * Hasil pemeriksaan batch (puzzle, rumpang, boleh).
   *
   * - `completed` + `solved` → konfeti + modal sukses → finish() (Lanjut);
   * - `completed` + `tries_exhausted` → `beforeExhausted()` (Susun Gambar:
   *   gambar utuh), modal "Kesempatanmu sudah habis" → finish() (Lanjut);
   *   kunci jawaban tidak ditampilkan;
   * - selain itu → modal "Belum semuanya tepat" + sisa kesempatan + Perbaiki.
   *
   * @returns {Promise<'complete'|'fix'>}
   */
  async function showCheckResult(result, {
    okTitle, okText, partialTitle, partialText, exhaustedText = t('triesExhaustedText'), beforeExhausted = null,
  }) {
    if (result.tries_left !== undefined) setTries(triesChip, result.tries_left);

    if (result.completed && result.completion_reason !== 'tries_exhausted') {
      Sfx.play('correct');
      confetti();
      await showModal({
        type: 'correct',
        title: okTitle,
        text: okText,
        dismissible: false,
        buttons: [{ label: closingLabel, style: 'primary', value: 'next' }],
      });
      await finish(result);
      return 'complete';
    }

    Sfx.play('wrong');

    if (result.completed) {
      if (beforeExhausted) await beforeExhausted();
      await showModal({
        type: 'info',
        title: t('triesExhaustedTitle'),
        text: exhaustedText,
        dismissible: false,
        buttons: [{ label: closingLabel, style: 'primary', value: 'next' }],
      });
      await finish(result);
      return 'complete';
    }

    await showModal({
      type: 'wrong',
      title: partialTitle,
      text: [partialText, triesText(result.tries_left)],
      buttons: [{ label: t('fixAnswers'), style: 'primary', value: 'fix' }],
    });
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
    levelCode,
    items: Array.isArray(data.items) ? data.items : [],
    refs,
    submit,
    check,
    selected,
    finish,
    closingLabel,
    tally,
    setTries,
    triesText,
    maxTries,
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

    emit('overlay_opened', { ...refs, payload: { overlay: 'hint', ...(levelCode ? { level: levelCode } : {}) } });
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
    emit('overlay_opened', { ...refs, payload: { overlay: 'exit', ...(levelCode ? { level: levelCode } : {}) } });
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

  // Bacaan (rumpang / benar–salah): batas gulir 25/50/75/100 % → reading_progress
  trackReading(section, refs);

  const engine = await load();
  engine.mount(ctx);
}
