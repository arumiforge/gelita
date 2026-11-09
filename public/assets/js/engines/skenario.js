/**
 * Engine `skenario` — Skenario Digital (research revision 10, part 6:
 * docs/revisi-10-analitik/32_IMPLEMENTASI_SKENARIO_DIGITAL.md §6).
 *
 * Payload `scenarios[i]` = { id, prompt, media, steps, audio, audio_id,
 * actions: [{ref, text}], reasons: [{ref, text}], state }. Refs are opaque per
 * attempt; the client never knows a score, a code, or which action is safe.
 * One try = one action and one reason, sent together:
 *   submit(id, { action: ref, reason: ref }, { ready_ms, reason_ready_ms,
 *   first_input_ms, action_last_input_ms, reason_last_input_ms, last_input_ms })
 * The server answers `score_band` (safe, partial, risky: only to pick the
 * sentence), `feedback`, `tries_left`, `scenario_closed`, and for a scenario
 * closed after three tries `safe_action` + `explanation`.
 *
 * States (§6.2): action → reason → feedback (Coba lagi or next story) or
 * explained (the safe action after the third try) → the next open scenario.
 * After the last one the server closes the attempt: the closing modal, then
 * Lanjut (#btn-next) from the shared frame. No red, and never the words
 * "salah" or "gagal" (18). A picked choice gets ● and a glow, not colour alone.
 *
 * Times (§6.4), ms since the challenge opened (ctx.sinceOpen()): ready_ms
 * when the actions show for this try, reason_ready_ms when the reasons last
 * show, first_input_ms the first action pick, action_/reason_last_input_ms
 * the last change of each, last_input_ms the tap on Kirim jawaban. Every pick
 * also goes through ctx.selected() with `step` = action or reason.
 *
 * The situation is read out only when ▶ Dengarkan is tapped (K11): moving on,
 * going to the reasons, or picking a choice stops it. A reload shows the
 * first open scenario at step 1 with its remaining tries.
 */
import { $, $$, el, announce } from '../core/dom.js';
import { showModal } from '../core/modal.js';
import { confetti } from '../core/confetti.js';
import { Sfx, narrationPlayer, pauseNarration } from '../core/audio.js';
import { emit } from '../core/events.js';

export function mount(ctx) {
  const scenarios = Array.isArray(ctx.data.scenarios) ? ctx.data.scenarios : [];
  const root = $('#scenario', ctx.section);
  if (!root || !scenarios.length) return;

  const { t } = ctx;
  const nodes = {
    progress: $('#scenario-progress', root),
    tries: $('#scenario-tries', root),
    figure: $('#scenario-figure', root),
    img: $('#scenario-img', root),
    prompt: $('#scenario-prompt', root),
    voice: $('#scenario-voice', root),
    stepper: $('#scenario-stepper', root),
    body: $('#scenario-body', root),
    go: $('#btn-scenario-go', ctx.section),
    back: $('#btn-scenario-back', ctx.section),
  };
  const voiceTemplate = $('#tpl-scenario-voice', root);
  const players = new Map(); // scenario index → NarrationPlayer
  const used = scenarios.map((s) => Number(s?.state?.tries) || 0);
  const closed = scenarios.map((s) => Boolean(s?.state?.closed));

  let index = 0;
  let step = 'action';
  let choice = { action: null, reason: null };
  let times = {};
  let busy = false;
  let done = false;
  let onGo = null;
  let onBack = null;

  $$('li', nodes.stepper).forEach((li) => {
    li.textContent = t(li.dataset.step === 'action' ? 'scenarioStepAction' : 'scenarioStepReason');
  });

  const label = (button, text) => { const span = $('span', button); if (span) span.textContent = text; };

  const setGo = (text, handler, enabled = true) => {
    label(nodes.go, text);
    nodes.go.disabled = !enabled;
    nodes.go.hidden = false;
    onGo = handler;
  };

  const setBack = (handler) => {
    nodes.back.hidden = handler === null;
    if (handler) label(nodes.back, t('scenarioBack'));
    onBack = handler;
  };

  nodes.go?.addEventListener('click', () => { if (!busy && onGo) onGo(); });
  nodes.back?.addEventListener('click', () => { if (!busy && onBack) onBack(); });

  const markStep = (name) => {
    $$('li', nodes.stepper).forEach((li) => li.classList.toggle('is-current', li.dataset.step === name));
    nodes.stepper.hidden = name !== 'action' && name !== 'reason';
  };

  // ---------------------------------------------------------- narration

  const playerOf = (i) => {
    const scenario = scenarios[i];
    if (!voiceTemplate || !nodes.voice || !scenario?.audio) return null;
    if (!players.has(i)) {
      const node = voiceTemplate.content.firstElementChild.cloneNode(true);
      node.dataset.src = scenario.audio;
      node.dataset.audioId = String(scenario.audio_id ?? '');
      const play = $('[data-voice-label]', node);
      const playing = $('[data-voice-playing]', node);
      if (play) play.textContent = t('scenarioListen');
      if (playing) playing.textContent = t('scenarioListening');
      node.hidden = true;
      nodes.voice.append(node);
      players.set(i, narrationPlayer(node, { attemptId: ctx.attemptId, shared: true }));
    }
    return players.get(i);
  };

  const showVoice = (i) => {
    pauseNarration();
    players.forEach((player, n) => { if (player) player.root.hidden = n !== i; });
    const player = playerOf(i);
    if (player) player.root.hidden = false;
  };

  // ------------------------------------------------------------ choices

  /** One radio list of {ref, text}; `name` = action or reason. */
  const choiceList = (name, options, chosen) => {
    const list = el('div', { class: 'scenario-options', role: 'radiogroup' });
    options.forEach((option) => {
      const input = el('input', { type: 'radio', name: `scenario-${name}`, value: option.ref });
      input.checked = option.ref === chosen;
      list.append(el('label', { class: 'scenario-option' }, input, el('span', { class: 'scenario-option-text' }, option.text)));
    });
    list.addEventListener('change', (event) => {
      const input = event.target.closest('input[type="radio"]');
      if (!input) return;
      const ms = ctx.sinceOpen();
      choice[name] = input.value;
      if (name === 'action') {
        times.first ??= ms;
        times.actionLast = ms;
      } else {
        times.reasonLast = ms;
      }
      pauseNarration();
      Sfx.play('click');
      ctx.selected(scenarios[index].id, { step: name, value: input.value });
      nodes.go.disabled = false;
    });
    return list;
  };

  const textOf = (list, ref) => (list.find((option) => option.ref === ref)?.text ?? '');

  // ------------------------------------------------------------- states

  const showAction = () => {
    step = 'action';
    markStep('action');
    const scenario = scenarios[index];
    times.ready ??= ctx.sinceOpen();
    nodes.body.replaceChildren(
      el('h2', { class: 'scenario-question' }, root.dataset.actionsTitle || ''),
      choiceList('action', scenario.actions ?? [], choice.action),
    );
    setBack(null);
    setGo(t('scenarioToReason'), showReason, choice.action !== null);
  };

  const showReason = () => {
    step = 'reason';
    markStep('reason');
    pauseNarration();
    const scenario = scenarios[index];
    times.reasonReady = ctx.sinceOpen();
    nodes.body.replaceChildren(
      el('p', { class: 'scenario-chosen' }, t('scenarioYourChoice', textOf(scenario.actions ?? [], choice.action))),
      el('h2', { class: 'scenario-question' }, t('scenarioReasonQuestion')),
      choiceList('reason', scenario.reasons ?? [], choice.reason),
    );
    setBack(showAction);
    setGo(t('scenarioSend'), send, choice.reason !== null);
    $('input', nodes.body)?.focus({ preventScroll: true });
  };

  const showFeedback = (result) => {
    step = 'feedback';
    markStep('feedback');
    const parts = [el('p', { class: `scenario-feedback is-${result.score_band ?? 'partial'}` }, result.feedback || '')];
    if (!result.scenario_closed) parts.push(el('p', { class: 'scenario-tries-left' }, t('scenarioTriesLeft', result.tries_left)));
    nodes.body.replaceChildren(...parts);
    setBack(null);
    if (result.scenario_closed) setGo(result.completed ? t('next') : t('scenarioNext'), () => nextScenario(result));
    else setGo(t('scenarioRetry'), retry);
    nodes.go.focus({ preventScroll: true });
  };

  const showExplained = (result) => {
    step = 'explained';
    markStep('explained');
    nodes.body.replaceChildren(
      el('p', { class: 'scenario-feedback is-closed' }, t('scenarioClosed')),
      el('h2', { class: 'scenario-question' }, t('scenarioSafeAction')),
      el('p', { class: 'scenario-safe' }, result.safe_action || ''),
      el('p', { class: 'scenario-explanation' }, result.explanation || ''),
    );
    setBack(null);
    setGo(result.completed ? t('next') : t('scenarioNext'), () => nextScenario(result));
    nodes.go.focus({ preventScroll: true });
  };

  const retry = () => {
    choice = { action: null, reason: null };
    times = {};
    showAction();
  };

  let shownVia = 'start';
  const render = (i) => {
    index = i;
    const scenario = scenarios[i];
    choice = { action: null, reason: null };
    times = {};
    if (nodes.progress) nodes.progress.textContent = t('scenarioProgress', i + 1, scenarios.length);
    if (nodes.prompt) nodes.prompt.textContent = scenario.prompt || '';
    if (nodes.figure && nodes.img) {
      nodes.figure.hidden = !scenario.media;
      if (scenario.media) nodes.img.src = scenario.media;
    }
    ctx.setTries(nodes.tries, ctx.maxTries - used[i]);
    showVoice(i);
    showAction();
    // Coordinator follow-up (owner, 9 November 2026; 35 §6.2): `item_shown` when a scenario comes on screen
    emit('item_shown', { levelId: ctx.levelId, nodeId: ctx.nodeId, attemptId: ctx.attemptId, itemId: scenario.id, payload: { via: shownVia, t_ms: ctx.sinceOpen() } });
    shownVia = 'next';
    announce(`${t('scenarioProgress', i + 1, scenarios.length)}: ${scenario.prompt || ''}`);
  };

  const nextOpen = () => {
    for (let step = 1; step <= scenarios.length; step++) {
      const i = (index + step) % scenarios.length;
      if (!closed[i]) return i;
    }
    return null;
  };

  const finishAll = async (result) => {
    done = true;
    pauseNarration();
    nodes.go.hidden = true;
    nodes.back.hidden = true;
    const solved = result.completion_reason !== 'tries_exhausted';
    if (solved) confetti();
    await showModal({
      type: solved ? 'correct' : 'info',
      title: solved ? t('scenarioDoneTitle') : t('triesExhaustedTitle'),
      text: solved ? t('scenarioDoneText') : t('triesExhaustedText'),
      dismissible: false,
      buttons: [{ label: ctx.closingLabel, style: 'primary', value: 'next' }],
    });
    await ctx.finish(result);
  };

  const nextScenario = (result) => {
    if (result?.completed) {
      finishAll(result);
      return;
    }
    const next = nextOpen();
    if (next !== null) render(next);
  };

  // --------------------------------------------------------------- send

  async function send() {
    if (busy || done || !choice.action || !choice.reason) return;
    const scenario = scenarios[index];
    const sentAt = ctx.sinceOpen();
    busy = true;
    ctx.setBusy(nodes.go, true);
    let result;
    try {
      result = await ctx.submit(scenario.id, { action: choice.action, reason: choice.reason }, {
        ready_ms: times.ready ?? null,
        reason_ready_ms: times.reasonReady ?? null,
        first_input_ms: times.first ?? null,
        action_last_input_ms: times.actionLast ?? null,
        reason_last_input_ms: times.reasonLast ?? null,
        last_input_ms: sentAt,
      });
    } catch (error) {
      busy = false;
      ctx.setBusy(nodes.go, false);
      await ctx.report(error, { onNetwork: () => ctx.networkPause(nodes.go) });
      return;
    }
    busy = false;
    ctx.setBusy(nodes.go, false);
    pauseNarration();

    if (Number.isFinite(Number(result.try_no))) used[index] = Number(result.try_no);
    ctx.setTries(nodes.tries, result.tries_left);

    if (result.scenario_closed) {
      closed[index] = true;
      if (result.score_band === 'safe') {
        Sfx.play('correct');
        ctx.tally({ addCorrect: 1 });
      } else {
        ctx.tally({ addWrong: 1 });
      }
    }

    if (result.scenario_closed && result.score_band !== 'safe') showExplained(result);
    else showFeedback(result);
  }

  // Reload: the closed scenarios count on the tally; start at the first open one
  const restored = scenarios.filter((s) => s?.state?.closed);
  if (restored.length) {
    ctx.tally({
      correct: restored.filter((s) => s.state.outcome === 'safe').length,
      wrong: restored.filter((s) => s.state.outcome !== 'safe').length,
    });
  }
  index = scenarios.length - 1;
  const first = nextOpen();
  if (first === null) return; // every scenario is closed: the server redirects on the next open
  render(first);
  ctx.currentItemId = () => scenarios[index]?.id ?? null;
}
