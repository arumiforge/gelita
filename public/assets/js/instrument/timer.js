/*
 * Hidden time limit of the test module (research revision 10, part 8: 34 §7.4, K6).
 * The page carries `data-remind-in` and `data-close-in` (seconds, from the
 * server). No clock or countdown is shown: at `remind-in` a friendly reminder
 * appears, at `close-in` the page reloads and the server closes the part.
 * Without JavaScript the server still enforces the limit on the next request.
 */
(() => {
  const root = document.querySelector('[data-close-in]');

  if (!root) {
    return;
  }

  const remindIn = Number(root.dataset.remindIn);
  const closeIn = Number(root.dataset.closeIn);
  const reminder = document.querySelector('.instrument-reminder');

  if (reminder && Number.isFinite(remindIn)) {
    window.setTimeout(() => { reminder.hidden = false; }, Math.max(0, remindIn) * 1000);
    reminder.querySelector('button')?.addEventListener('click', () => { reminder.hidden = true; });
  }

  if (Number.isFinite(closeIn)) {
    window.setTimeout(() => { window.location.reload(); }, (Math.max(0, closeIn) + 1) * 1000);
  }
})();
