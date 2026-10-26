/*
 * Shared parts of the GELITA mock-ups (stage 1 of research revision 10).
 * Builds the game HUD (components/hud-menu, compact) and the
 * "Mock-up — bukan tampilan akhir" label from data attributes on <body>,
 * so each page only holds its own screen:
 *   data-hud     region-centre | logo-centre | none
 *   data-region  region name in the HUD middle (region-centre)
 *   data-back    label of the navy back plate (logo-centre), optional
 *   data-code    mock-up code printed on the label, e.g. mk-sd-01
 * A classic script (not a module) so the pages open from disk.
 */
(function () {
  var A = '../../../../../public/assets/';
  var K = '../../../../../gelita-katalog/assets/';
  var body = document.body;
  var stage = document.getElementById('stage');
  document.documentElement.style.setProperty('--app-w', window.innerWidth + 'px');
  document.documentElement.style.setProperty('--app-h', window.innerHeight + 'px');

  if (stage) {
    var pics = {
      'gold-pill-1': 'ui/gold-pill-1.webp', 'gold-pill-2': 'ui/gold-pill-2.webp',
      'gold-pill-3': 'ui/gold-pill-3.webp', 'gold-pill-4': 'ui/gold-pill-4.webp',
      'modal-frame-compact': 'ui/modal-frame-4x3.webp', 'modal-frame-wide': 'ui/modal-frame-16x9.webp',
      'select-arrow': 'ui/select-arrow.webp', 'modal-plate-navy': 'ui/kenal-plate-navy.webp',
      'round-close': 'ui/round-close.webp', 'round-blank': 'ui/round-blank.webp',
      'round-neutral': 'ui/round-neutral.webp', 'plate-navy': 'ui/kenal-plate-navy.webp',
      'plate-green': 'ui/plate-green.webp'
    };
    Object.keys(pics).forEach(function (k) {
      // Absolute URLs: a relative url() in a custom property resolves against the stylesheet that uses it
      stage.style.setProperty('--' + k, 'url("' + new URL(A + pics[k], location.href).href + '")');
    });
  }

  var hud = body.getAttribute('data-hud') || 'none';
  if (stage && hud !== 'none') {
    var h = document.createElement('header');
    h.className = 'hud hud-menu-style is-compact' + (hud === 'region-centre' ? ' is-region-centre' : '') + (hud === 'logo-centre' ? ' is-logo-centre' : '');
    var logo = '<div class="hud-left hud-identity"><img class="hud-identity-logo" src="' + K + 'logo/ui.logo-hero.webp" alt="GELITA"></div>';
    var region = body.getAttribute('data-region');
    var mid = hud === 'region-centre' && region ? '<span class="hud-identity-region hud-region-centre">' + region + '</span>' : '';
    var back = body.getAttribute('data-back');
    var plate = hud === 'logo-centre' && back
      ? '<a class="hud-back-plate" href="#" style="--plate-navy: url(&quot;' + new URL(A + 'ui/kenal-plate-navy.webp', location.href).href + '&quot;)"><span>' + back + '</span></a>' : '';
    var ctl = function (name) {
      return '<span class="hud-pop"><span class="hud-ctl"><img class="hud-ctl-art" src="' + A + 'ui/round-' + name + '.webp" alt=""></span></span>';
    };
    var controls = '<div class="hud-right hud-controls">' + (hud === 'region-centre' ? '' : ctl('lang-id')) + ctl('sound') + ctl('menu') + '</div>';
    h.innerHTML = logo + mid + plate + controls;
    stage.insertBefore(h, stage.querySelector('main'));
  }

  // Inline url()s in custom properties: make them absolute so they load from any stylesheet
  Array.prototype.forEach.call(document.querySelectorAll('[style*="url("]'), function (el) {
    el.setAttribute('style', el.getAttribute('style').replace(/url\((["']?)(\.\.\/[^"')]+)\1\)/g, function (m, q, u) {
      return 'url("' + new URL(u, location.href).href + '")';
    }));
  });

  var code = body.getAttribute('data-code') || '';
  var label = document.createElement('div');
  label.className = 'mock-label';
  label.innerHTML = '<b>Mock-up — bukan tampilan akhir</b>' + (code ? '<span>' + code + '</span>' : '');
  (stage || body).appendChild(label);
})();
