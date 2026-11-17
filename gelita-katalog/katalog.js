/* Katalog Aset GELITA: renders window.KATALOG (data.js). A classic script, so the page also works from file://. */
(function () {
  'use strict';

  var K = window.KATALOG;
  var $ = function (id) { return document.getElementById(id); };

  if (!K) {
    document.querySelector('main').insertAdjacentHTML('afterbegin', '<p class="note">Daftar aset belum bisa dimuat. Muat ulang halaman sebentar lagi, ya.</p>');
    return;
  }

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (name) {
      if (name === 'text') { node.textContent = attrs[name]; }
      else if (name === 'class') { node.className = attrs[name]; }
      else { node.setAttribute(name, attrs[name]); }
    });
    (children || []).forEach(function (child) { if (child) { node.appendChild(child); } });
    return node;
  }

  function fill(select, options) {
    Object.keys(options).forEach(function (code) { select.appendChild(el('option', { value: code, text: options[code] })); });
  }

  function norm(text) {
    return String(text || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
  }

  function fmtNum(n) { return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, '.'); }

  function fmtDur(seconds) {
    if (seconds === null || seconds === undefined) { return ''; }
    if (seconds < 60) { return String(seconds).replace('.', ',') + ' detik'; }
    var s = Math.round(seconds);
    return Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0') + ' menit';
  }

  function badge(code) { return el('span', { class: 'badge st-' + code, text: K.statuses[code] }); }

  function tags(item) {
    var list = [badge(item.s)];
    if (item.r) { list.push(el('span', { class: 'badge tag', text: K.regions[item.r] })); }
    if (item.c) { list.push(el('span', { class: 'badge tag', text: K.kinds[item.c] })); }
    if (item.x) { list.push(el('span', { class: 'badge unused', text: 'Belum dipakai' })); }
    return el('p', { class: 'badges' }, list);
  }

  $('summary').textContent = fmtNum(K.images.length) + ' gambar · ' + K.music.length + ' musik · ' + K.sfx.length
    + ' efek suara · ' + fmtNum(K.narration.length) + ' baris narasi';

  // ---------------------------------------------------------------- pictures
  var usedGroups = {};
  K.images.forEach(function (i) { usedGroups[i.g] = true; });
  var groups = {};
  Object.keys(K.groups).forEach(function (code) { if (usedGroups[code]) { groups[code] = K.groups[code]; } });
  fill($('img-g'), groups);
  fill($('img-r'), K.regions);
  fill($('img-c'), K.kinds);
  fill($('img-s'), K.statuses);

  K.images.forEach(function (i) {
    i.hay = norm([i.t, i.d, i.k.join(' '), i.u.join(' '), i.id].join(' '));
  });

  var shown = [];

  function renderImages() {
    var q = norm($('img-q').value).trim();
    var g = $('img-g').value, r = $('img-r').value, c = $('img-c').value, s = $('img-s').value;
    var words = q ? q.split(/\s+/) : [];

    shown = K.images.filter(function (i) {
      return (!g || i.g === g)
        && (!r || (r === 'umum' ? !i.r : i.r === r))
        && (!c || i.c === c)
        && (!s || i.s === s)
        && words.every(function (w) { return i.hay.indexOf(w) !== -1; });
    });

    var list = $('img-list');
    list.textContent = '';
    $('img-count').textContent = shown.length === K.images.length
      ? 'Menampilkan semua ' + fmtNum(shown.length) + ' gambar.'
      : 'Menampilkan ' + fmtNum(shown.length) + ' dari ' + fmtNum(K.images.length) + ' gambar.';

    if (!shown.length) {
      list.appendChild(el('p', { class: 'empty', text: 'Tidak ada gambar yang cocok. Coba kata lain atau ubah pilihan filternya.' }));
      return;
    }

    Object.keys(K.groups).forEach(function (code) {
      var items = shown.filter(function (i) { return i.g === code; });
      if (!items.length) { return; }

      list.appendChild(el('div', { class: 'group' }, [el('h2', { text: K.groups[code] }), el('span', { text: items.length + ' gambar' })]));
      var grid = el('div', { class: 'grid' });
      items.forEach(function (i) {
        var img = el('img', { src: i.th, alt: '', loading: 'lazy', decoding: 'async' });
        var card = el('button', { type: 'button', class: 'card', 'data-id': i.id }, [
          el('span', { class: 'thumb' }, [img]),
          el('span', { class: 'body' }, [el('span', { class: 'name', text: i.t }), tags(i)])
        ]);
        card.addEventListener('click', function () { openDetail(i.id); });
        grid.appendChild(card);
      });
      list.appendChild(grid);
    });
  }

  ['img-q', 'img-g', 'img-r', 'img-c', 'img-s'].forEach(function (id) {
    $(id).addEventListener(id === 'img-q' ? 'input' : 'change', renderImages);
  });

  // ------------------------------------------------------------ detail view
  var dialog = $('detail');
  var current = null;

  function fact(term, value) {
    if (value === null || value === undefined || value === '' || (Array.isArray(value) && !value.length)) { return []; }
    var dd = el('dd');
    if (Array.isArray(value)) {
      if (value.length === 1) { dd.textContent = value[0]; }
      else { dd.appendChild(el('ul', {}, value.map(function (v) { return el('li', { text: v }); }))); }
    } else if (value instanceof Node) { dd.appendChild(value); }
    else { dd.textContent = value; }
    return [el('dt', { text: term }), dd];
  }

  function codes(keys) {
    var span = el('span');
    keys.forEach(function (k, n) {
      if (n) { span.appendChild(document.createTextNode(', ')); }
      span.appendChild(el('code', { text: k }));
    });
    return span;
  }

  function openDetail(id) {
    var item = K.images.filter(function (i) { return i.id === id; })[0];
    if (!item) { return; }
    current = item;

    $('detail-img').src = item.img;
    $('detail-img').alt = item.t;
    $('detail-open').href = item.img;
    $('detail-title').textContent = item.t;
    var badges = $('detail-badges');
    badges.textContent = '';
    Array.prototype.slice.call(tags(item).childNodes).forEach(function (node) { badges.appendChild(node); });
    $('detail-desc').textContent = item.d;

    var facts = $('detail-facts');
    facts.textContent = '';
    []
      .concat(fact('Kategori', K.groups[item.g]))
      .concat(fact('Dipakai di', item.u))
      .concat(fact('Kunci', codes(item.k)))
      .concat(fact('Ukuran asli', item.wh ? fmtNum(item.wh[0]) + ' × ' + fmtNum(item.wh[1]) + ' piksel' : 'Gambar vektor (SVG)'))
      .concat(fact('Status tinjauan', K.statuses[item.s] + (item.no ? ' (baris ' + item.no + ' daftar tinjauan)' : '')))
      .concat(fact('Catatan tinjauan', item.sn ? item.sn + ' (catatan pengembang, berbahasa Inggris)' : ''))
      .forEach(function (node) { facts.appendChild(node); });

    var pos = shown.indexOf(item);
    $('detail-prev').disabled = pos <= 0;
    $('detail-next').disabled = pos === -1 || pos >= shown.length - 1;

    if (!dialog.open) { dialog.showModal(); }
    if (location.hash !== '#gambar/' + item.id) { history.replaceState(null, '', '#gambar/' + item.id); }
  }

  function step(delta) {
    var pos = shown.indexOf(current);
    if (pos !== -1 && shown[pos + delta]) { openDetail(shown[pos + delta].id); }
  }

  $('detail-prev').addEventListener('click', function () { step(-1); });
  $('detail-next').addEventListener('click', function () { step(1); });
  $('detail-close').addEventListener('click', function () { dialog.close(); });
  dialog.addEventListener('click', function (e) { if (e.target === dialog) { dialog.close(); } });
  dialog.addEventListener('keydown', function (e) {
    if (e.key === 'ArrowLeft') { step(-1); }
    if (e.key === 'ArrowRight') { step(1); }
  });
  dialog.addEventListener('close', function () {
    history.replaceState(null, '', '#gambar');
    var card = document.querySelector('.card[data-id="' + (current ? current.id : '') + '"]');
    if (card) { card.focus(); }
  });

  // ------------------------------------------------------- music and sounds
  function player(src) { return el('audio', { controls: '', preload: 'none', src: src }); }

  function renderSounds(listId, sounds) {
    var list = $(listId);
    sounds.forEach(function (s) {
      var meta = [s.d, fmtDur(s.dur), s.loop ? 'diputar berulang' : ''].filter(Boolean).join(' · ');
      list.appendChild(el('article', { class: 'sound' }, [
        el('h3', { text: s.t }),
        meta ? el('p', { text: meta }) : null,
        player(s.f),
        el('p', {}, [el('code', { text: s.id + '.mp3' })])
      ]));
    });
  }

  renderSounds('music-list', K.music);
  renderSounds('sfx-list', K.sfx);

  // -------------------------------------------------------------- narration
  var speakers = {};
  K.narration.forEach(function (l) { speakers[l.sp] = l.sp; l.hay = norm([l.id, l.ti, l.te, l.sp].join(' ')); });
  fill($('nar-ctx'), K.contexts);
  fill($('nar-r'), K.regions);
  fill($('nar-sp'), speakers);

  function langBlock(label, text, file) {
    return el('div', { class: 'lang' }, [
      el('span', { class: 'label', text: label }),
      el('p', { text: text }),
      file ? player(file) : el('span', { class: 'missing', text: 'Rekaman belum ada.' })
    ]);
  }

  function renderNarration() {
    var q = norm($('nar-q').value).trim();
    var ctx = $('nar-ctx').value, r = $('nar-r').value, sp = $('nar-sp').value;
    var words = q ? q.split(/\s+/) : [];
    var lines = K.narration.filter(function (l) {
      return (!ctx || l.ctx === ctx)
        && (!r || (r === 'umum' ? !l.r : l.r === r))
        && (!sp || l.sp === sp)
        && words.every(function (w) { return l.hay.indexOf(w) !== -1; });
    });

    var list = $('nar-list');
    list.textContent = '';
    $('nar-count').textContent = 'Menampilkan ' + fmtNum(lines.length) + ' dari ' + fmtNum(K.narration.length) + ' baris narasi.';

    if (!lines.length) {
      list.appendChild(el('p', { class: 'empty', text: 'Tidak ada baris yang cocok. Coba kata lain atau ubah pilihan filternya.' }));
      return;
    }

    Object.keys(K.contexts).concat(['']).forEach(function (code) {
      var part = lines.filter(function (l) { return code ? l.ctx === code : !K.contexts[l.ctx]; });
      if (!part.length) { return; }

      list.appendChild(el('div', { class: 'group' }, [el('h2', { text: code ? K.contexts[code] : 'Lainnya' }), el('span', { text: part.length + ' baris' })]));
      var box = el('div', { class: 'lines' });
      part.forEach(function (l) {
        box.appendChild(el('article', { class: 'line' }, [
          el('header', {}, [
            el('strong', { text: l.sp }),
            l.r ? el('span', { class: 'badge tag', text: K.regions[l.r] }) : null,
            l.c ? el('span', { class: 'badge tag', text: K.kinds[l.c] }) : null,
            el('code', { text: l.id })
          ]),
          el('div', { class: 'pair' }, [
            langBlock('Bahasa Indonesia', l.ti, l.fi),
            langBlock('English', l.te, l.fe)
          ])
        ]));
      });
      list.appendChild(box);
    });
  }

  ['nar-q', 'nar-ctx', 'nar-r', 'nar-sp'].forEach(function (id) {
    $(id).addEventListener(id === 'nar-q' ? 'input' : 'change', renderNarration);
  });

  // ------------------------------------------------------------------- tabs
  var rendered = {};

  function showTab() {
    var hash = decodeURIComponent(location.hash.replace(/^#/, ''));
    var tab = hash.split('/')[0];
    if (['gambar', 'musik', 'efek', 'narasi'].indexOf(tab) === -1) { tab = 'gambar'; }

    document.querySelectorAll('.tab').forEach(function (section) { section.hidden = section.id !== 'tab-' + tab; });
    document.querySelectorAll('.tabs a').forEach(function (a) {
      if (a.getAttribute('data-tab') === tab) { a.setAttribute('aria-current', 'page'); } else { a.removeAttribute('aria-current'); }
    });

    if (tab === 'gambar' && !rendered.gambar) { rendered.gambar = true; renderImages(); }
    if (tab === 'narasi' && !rendered.narasi) { rendered.narasi = true; renderNarration(); }

    var id = hash.split('/')[1];
    if (tab === 'gambar' && id) { openDetail(id); }
  }

  window.addEventListener('hashchange', function () {
    if (!/^#gambar\//.test(location.hash) || !dialog.open) { showTab(); }
  });
  showTab();
})();
