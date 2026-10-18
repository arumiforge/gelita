/*
 * Catches Chrome's install offer (`beforeinstallprompt`) from the first
 * moment of the page. The event can fire before the game.js module graph has
 * loaded on a slow phone, and a missed event means no "Pasang GELITA" button.
 * game/install.js picks the held event up from window.GELITA_INSTALL_PROMPT.
 * A classic script in <head> before the manifest link, so the listener is in
 * place before Chrome can make its offer; a file, not inline, so it passes a
 * CSP of script-src 'self'.
 */
(function () {
  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    window.GELITA_INSTALL_PROMPT = event;
  });
  window.addEventListener('appinstalled', function () {
    window.GELITA_INSTALL_PROMPT = null;
  });
})();
