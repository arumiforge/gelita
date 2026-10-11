/*
 * Sets --app-w/--app-h from the window size before the stylesheets apply
 * (Dok 3 §2.1); core/stage.js takes over once the modules load.
 * A classic blocking script loaded from <head>, not a module, so it runs before
 * the first paint. Kept as a file rather than inline so it passes a CSP of
 * script-src 'self'.
 */
(function () {
  var d = document.documentElement;
  function f() {
    d.style.setProperty('--app-w', window.innerWidth + 'px');
    d.style.setProperty('--app-h', window.innerHeight + 'px');
  }
  f();
  window.addEventListener('resize', f);
  window.addEventListener('orientationchange', f);
  window.addEventListener('load', f);
})();
