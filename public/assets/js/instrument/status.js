/*
 * Opening screen of the test module (research revision 10, part 8: 34 §5.3):
 * asks `GET /api/instrument/status` every 10 seconds and reloads once the
 * researcher started the test and the student has a form. Without
 * JavaScript the student presses "Muat ulang".
 */
(() => {
  const root = document.querySelector('[data-status-url]');

  if (!root) {
    return;
  }

  const ask = () => {
    fetch(root.dataset.statusUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
      .then((response) => (response.ok ? response.json() : null))
      .then((data) => {
        if (data && data.started && data.form_ready) {
          window.location.reload();
        }
      })
      .catch(() => {});
  };

  window.setInterval(ask, 10000);
})();
