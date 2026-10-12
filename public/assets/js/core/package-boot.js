/*
 * Asset package boot (docs/rencana-paket-aset.md §5.6, §7.1, §8.7; contract in
 * docs/06_JAVASCRIPT.md §Asset package — service worker and downloader).
 *
 * A classic blocking script loaded from <head> before game.js, so the
 * "assets are local" hook (core/config.js assetsLocal()) is set before the
 * page's own curtain starts. A file, not inline, so it passes a CSP of
 * script-src 'self'.
 *
 * Its <script> tag carries, from the server:
 *   data-package="pkt-…"  package the page's URLs come from (absent: package mode off)
 *   data-sw="off"         server kill switch `gelita.paket.aktif = false`
 *                         (game/install.js then registers sw-kosong.js)
 *
 * It reads what core/package.js left on this device: localStorage
 * `gelita.paket` (mirror of the active package marker) and sessionStorage
 * `gelita.paket.daring` (online mode chosen for this browser session).
 *
 * Sets:
 *   <html data-assets-local="1">  only when the page's package is the one
 *       complete on this device, a service worker controls the page, and
 *       online mode was not chosen;
 *   window.GELITA_PACKAGE_BOOT    what was found, for core/package.js and game/install.js;
 *   window.GELITA_PACKAGE         { package_code, package_mode } for core/device.js
 *       (core/package.js replaces it with an async check against Cache Storage).
 */
(function () {
  var script = document.currentScript;
  var code = (script && script.getAttribute('data-package')) || null;
  var killed = !!script && script.getAttribute('data-sw') === 'off';
  var held = null;
  var online = null;

  try {
    held = JSON.parse(window.localStorage.getItem('gelita.paket') || 'null');
  } catch (e) {
    held = null;
  }
  try {
    online = window.sessionStorage.getItem('gelita.paket.daring');
  } catch (e) {
    online = null;
  }

  var controlled = 'serviceWorker' in navigator && !!navigator.serviceWorker.controller;
  var holds = !!code && !!held && held.package_code === code;
  var local = holds && controlled && !online && !killed;

  if (local) document.documentElement.setAttribute('data-assets-local', '1');

  window.GELITA_PACKAGE_BOOT = {
    code: code,
    killed: killed,
    holds: holds,
    controlled: controlled,
    online: online,
    local: local
  };
  window.GELITA_PACKAGE = {
    package_code: code,
    package_mode: code ? (local ? 'lengkap' : 'daring') : 'nonaktif'
  };
})();
