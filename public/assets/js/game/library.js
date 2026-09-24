/**
 * Pustaka: buku satu wilayah (`/pustaka/{code}`) dan rak Pustaka Kedu
 * (`/pustaka`).
 *
 * Buku: navigasi halaman (tanpa memuat ulang), media yang gagal dimuat
 * disembunyikan beserta bingkainya, gambar memudar masuk dari skeleton
 * perkamen, gambar halaman berikutnya dipramuat, perbesar gambar (dengan
 * pemutar tunggu), panel kredit ⓘ yang saling menutup, pemutar video
 * tertanam yang baru dimuat saat tombol Putar ditekan, dan event
 * `library_page_viewed` { page, page_id } tiap halaman dibuka. Membuka
 * pustaka tidak memengaruhi skor sama sekali — `library_opened` sudah
 * dicatat server, hanya bila isi buku benar-benar tampil.
 *
 * Rak: sampul yang gagal dimuat disembunyikan sehingga gradiennya tampil.
 */
import { $, $$, el } from '../core/dom.js';
import { emit } from '../core/events.js';
import { initSlides } from './slides.js';

function hideBrokenMedia(root) {
  const hide = (node) => {
    const figure = node.closest('.book-figure');
    if (figure) figure.hidden = true;

    // Halaman yang kehilangan semua medianya menjadi halaman teks saja
    const page = node.closest('.book-spread');
    const media = page?.querySelector('.book-media');
    if (media && !$$('.book-figure:not([hidden])', media).length) {
      media.hidden = true;
      page.classList.add('is-text-only');
    }
  };

  for (const img of $$('.book-figure img', root)) {
    if (img.complete && img.naturalWidth === 0 && img.getAttribute('src')) hide(img);
    else img.addEventListener('error', () => hide(img), { once: true });
  }

  for (const video of $$('.book-figure video', root)) {
    video.addEventListener('error', () => hide(video), { once: true });
    $$('source', video).forEach((source) => source.addEventListener('error', () => hide(video), { once: true }));
  }
}

/**
 * Gambar di bingkai berasio tetap mulai transparan (hanya di bawah
 * `html.js`, lihat game.css) di atas skeleton, lalu memudar masuk saat
 * termuat. Gambar yang sudah ada di cache (`complete`) langsung ditandai.
 * Galat ditangani hideBrokenMedia().
 */
function fadeInImages(root) {
  const loaded = (img) => {
    img.classList.add('is-loaded');
    img.closest('.book-frame')?.classList.add('is-loaded');
  };

  for (const img of $$('.book-frame img', root)) {
    if (img.complete && img.naturalWidth > 0) loaded(img);
    else img.addEventListener('load', () => loaded(img), { once: true });
  }
}

/**
 * Gambar di slide tersembunyi ber-`loading=lazy` baru diminta saat tampil,
 * sehingga halaman berikutnya selalu dibuka dengan skeleton. Saat pindah
 * halaman, gambar halaman sesudahnya dijadikan `eager` agar mulai dimuat.
 */
function preloadPage(slide) {
  if (!slide) return;
  for (const img of $$('img[loading="lazy"]', slide)) img.loading = 'eager';
}

/**
 * Panel kredit (`<details class="media-credit">`): membuka satu menutup yang
 * lain; ketuk di luar panel atau Esc menutup. Tanpa JavaScript `<details>`
 * tetap membuka dan menutup sendiri.
 */
function initCredits(root) {
  const credits = $$('details.media-credit', root);
  if (!credits.length) return;

  const closeAll = (except = null) => {
    for (const details of credits) if (details !== except && details.open) details.open = false;
  };

  // Ketuk/Enter pada ⓘ yang akan membuka: tutup yang lain saat itu juga
  // (`toggle` baru datang setelahnya, secara asinkron)
  root.addEventListener('click', (event) => {
    const summary = event.target instanceof Element ? event.target.closest('details.media-credit > summary') : null;
    if (summary && !summary.parentElement.open) closeAll(summary.parentElement);
  });

  // `toggle` tidak menggelembung; ditangkap di fase capture
  root.addEventListener('toggle', (event) => {
    const details = event.target;
    if (details instanceof HTMLDetailsElement && details.matches('.media-credit') && details.open) closeAll(details);
  }, true);

  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element) || !event.target.closest('details.media-credit')) closeAll();
  });

  document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    const open = credits.find((details) => details.open);
    if (!open) return;
    closeAll();
    $('summary', open)?.focus({ preventScroll: true });
  });
}

/**
 * Pemutar YouTube/Vimeo/Drive: facade (poster + tombol Putar) diganti iframe
 * hanya setelah ditekan. Tanpa JavaScript tautan itu membuka videonya di tab
 * baru.
 */
function initEmbeds(root) {
  for (const box of $$('.book-embed[data-embed]', root)) {
    const facade = box.innerHTML;
    box.dataset.facade = '1';

    box.addEventListener('click', (event) => {
      const play = event.target.closest('.embed-play');
      if (!play) return;
      event.preventDefault();

      const provider = box.dataset.provider;
      const autoplay = provider === 'youtube' || provider === 'vimeo' ? '&autoplay=1' : '';
      const frame = el('iframe', {
        src: box.dataset.embed + autoplay,
        title: box.dataset.title || '',
        loading: 'lazy',
        allow: 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
        allowfullscreen: '',
        // Kebijakan situs `same-origin` tidak mengirim referrer lintas domain;
        // pemutar YouTube menolak diputar tanpa referrer (galat 153).
        referrerpolicy: 'strict-origin-when-cross-origin',
      });
      box.replaceChildren(frame);
      box.classList.add('is-playing');
      box.closest('.book-frame')?.classList.add('is-playing');
      frame.focus?.();
    });

    // Dipanggil saat pindah halaman: hentikan video dengan mengembalikan facade
    box.resetEmbed = () => {
      if (!box.classList.contains('is-playing')) return;
      box.innerHTML = facade;
      box.classList.remove('is-playing');
      box.closest('.book-frame')?.classList.remove('is-playing');
      fadeInImages(box);
    };
  }
}

/** Perbesar gambar dalam <dialog> bawaan browser (Esc menutup). */
function initZoom(root) {
  const buttons = $$('.book-zoom[data-zoom]', root);
  if (!buttons.length || typeof HTMLDialogElement === 'undefined') return;

  const closeLabel = root.dataset.labelClose || 'Tutup';
  const image = el('img', { alt: '' });
  const spinner = el('span', { class: 'lightbox-spinner', role: 'status', 'aria-label': root.dataset.labelLoading || '' });
  const stage = el('div', { class: 'lightbox-stage' }, image, spinner);
  const caption = el('p', { class: 'lightbox-caption' });
  const close = el('button', { type: 'button', class: 'icon-btn lightbox-close', 'aria-label': closeLabel, title: closeLabel }, '×');
  const dialog = el('dialog', { class: 'lightbox' }, close, stage, caption);
  document.body.append(dialog);

  const ready = () => dialog.classList.remove('is-loading');
  image.addEventListener('load', ready);
  image.addEventListener('error', ready);

  close.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

  for (const button of buttons) {
    button.addEventListener('click', () => {
      const img = $('img', button);
      dialog.classList.add('is-loading');
      image.src = button.dataset.zoom;
      image.alt = img?.alt || '';
      // Gambar yang sama dengan sebelumnya tidak memicu `load` lagi
      if (image.complete && image.naturalWidth > 0) ready();
      const figcaption = button.closest('figure')?.querySelector('figcaption');
      caption.textContent = figcaption ? figcaption.textContent.replace(/\s+/g, ' ').trim() : '';
      caption.hidden = caption.textContent === '';
      dialog.showModal();
    });
  }
}

/** Rak `/pustaka`: sampul rusak disembunyikan, gradien kartu yang tampil. */
export function initLibraryIndex() {
  const screen = $('[data-screen="library-index"]');
  if (!screen) return;

  for (const img of $$('.shelf-cover img', screen)) {
    const hide = () => { img.hidden = true; };
    if (img.complete && img.naturalWidth === 0) hide();
    else img.addEventListener('error', hide, { once: true });
  }
}

export function initLibrary() {
  const screen = $('[data-screen="library"]');
  if (!screen) return;

  const levelId = Number(screen.dataset.levelId) || null;
  hideBrokenMedia(screen);
  fadeInImages(screen);
  initCredits(screen);
  initEmbeds(screen);
  initZoom(screen);

  const pages = $$('ol.book > .slide', screen);

  const report = (index, slide, total) => {
    emit('library_page_viewed', {
      levelId,
      payload: { page: index, total, page_id: Number(slide.dataset.page) || null },
    });
  };

  const slides = initSlides({
    list: $('ol.book', screen),
    prefix: 'page-',
    onChange: (index, slide, total) => {
      $$('video', screen).forEach((video) => video.pause());
      $$('.book-embed', screen).forEach((box) => box.resetEmbed?.());
      $$('details.media-credit[open]', screen).forEach((details) => { details.open = false; });
      preloadPage(pages[index]);
      report(index, slide, total);
    },
  });

  // Halaman pertama juga dihitung "dilihat"; halaman keduanya mulai dimuat
  if (slides) {
    preloadPage(pages[slides.current]);
    report(slides.current, pages[slides.current - 1], slides.total);
  }
}
