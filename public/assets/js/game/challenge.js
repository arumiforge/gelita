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
 *   check(answers)                 → POST /api/attempts/{id}/check      (puzzle, rumpang, boleh)
 *   selected(itemId, change)       → event `answer_selected` + waktu isian per soal
 *
 * Waktu isian per soal (Dok 1 §12.5): ms sejak tantangan dibuka
 * (performance.now()) saat siswa pertama kali dan terakhir kali mengisi/
 * mengubah soal itu dalam satu percobaan; ikut dikirim pada setiap /check
 * sebagai `first_input_ms` / `last_input_ms` (satu baris item_tries), lalu
 * dihitung ulang untuk percobaan berikutnya. Soal yang tidak disentuh dalam
 * satu percobaan dikirim tanpa waktu. Hanya catatan proses — urutan dan
 * waktu resmi tetap occurred_at server.
 *   finish(result)                 → ikuti `completed` / `redirect` dari /check atau /responses
 *   useHint()                      → POST /api/attempts/{id}/hints
 *
 * Aturan 3 kesempatan (Dok 2 §5.3): server yang menghitung dan menutup
 * pengerjaan. /check dan /responses membalas `tries_left`, `completed`,
 * `completion_reason`, dan `redirect`; klien hanya menampilkan penanda
 * kesempatan (●●○), modal yang sesuai, lalu pindah ke `redirect`.
 * POST /complete hanya cadangan bila respons selesai tanpa `redirect`
 * (endpoint itu idempoten).
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

  function submit(itemId, answer, extra = {}) {
    return post('responses', {
      item_id: itemId,
      answer,
      client_event_id: uuid(),
      occurred_at: now(),
      ...extra,
    });
  }

  async function check(answers) {
    const rows = answers.map((row) => {
      const times = inputTimes.get(row.item_id);
      return times ? { ...row, first_input_ms: times.first, last_input_ms: times.last } : row;
    });
    const result = await post('check', { answers: rows, client_event_id: uuid(), occurred_at: now() });
    // Waktu isian per percobaan: percobaan berikutnya dihitung dari isian berikutnya
    for (const row of answers) inputTimes.delete(row.item_id);
    return result;
  }

  /** Pindah ke layar hasil; pengerjaan sudah ditutup server. */
  async function leaveTo(redirect, shards = null) {
    state.completed = true;
    state.leaving = true;
    timer.stop();
    if (shards !== null && shards !== undefined) updateLantern(shards);
    await flush();
    window.location.href = redirect;
  }

  /** Cadangan: respons selesai tanpa `redirect` → POST /complete (idempoten). */
  async function complete() {
    if (state.completed) return;
    try {
      const result = await post('complete', { client_event_id: uuid(), occurred_at: now() });
      await leaveTo(result.redirect, result.shards);
    } catch (error) {
      if (error?.code === 'TRIES_REMAINING') toast(t('itemsPending'), 'warn');
      else await report(error);
    }
  }

  /** Ikuti penutupan dari respons /check atau /responses. */
  async function finish(result) {
    if (state.completed) return;
    if (result?.redirect) await leaveTo(result.redirect, result.shards);
    else await complete();
  }

  /**
   * Galat aturan kesempatan dari server (mis. tab lain sudah memakai
   * kesempatan terakhir): pengerjaan yang sudah selesai → layar hasilnya;
   * petunjuk yang sudah tertutup → muat ulang agar tampilan ikut payload.
   * @returns {Promise<boolean>} true bila galatnya sudah ditangani
   */
  async function ruleError(error) {
    if (!(error instanceof ApiError)) return false;
    if (error.code === 'TRIES_EXHAUSTED') {
      if (error.payload?.redirect) await leaveTo(error.payload.redirect);
      else window.location.reload();
      return true;
    }
    if (error.code === 'CLUE_CLOSED') {
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
   * - `completed` + `solved` → konfeti + modal sukses → redirect;
   * - `completed` + `tries_exhausted` → `beforeExhausted()` (Susun Gambar:
   *   gambar utuh), modal "Kesempatanmu sudah habis" dengan satu tombol
   *   Lanjut → redirect; kunci jawaban tidak ditampilkan;
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
        buttons: [{ label: t('next'), style: 'primary', value: 'next' }],
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
        buttons: [{ label: t('next'), style: 'primary', value: 'next' }],
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
    items: Array.isArray(data.items) ? data.items : [],
    refs,
    submit,
    check,
    selected,
    finish,
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
