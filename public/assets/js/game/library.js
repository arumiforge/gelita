/**
 * Pustaka Kedu: navigasi halaman buku (tanpa memuat ulang), media yang gagal
 * dimuat disembunyikan beserta bingkainya, perbesar gambar, pemutar video
 * tertanam yang baru dimuat saat tombol Putar ditekan, dan event
 * `library_page_viewed` { page, page_id } tiap halaman dibuka. Membuka
 * pustaka tidak memengaruhi skor sama sekali — `library_opened` sudah
 * dicatat server.
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
    if (img.complete && img.naturalWidth === 0) hide(img);
    else img.addEventListener('error', () => hide(img), { once: true });
  }

  for (const video of $$('.book-figure video', root)) {
    video.addEventListener('error', () => hide(video), { once: true });
    $$('source', video).forEach((source) => source.addEventListener('error', () => hide(video), { once: true }));
  }
}

/**
 * Pemutar YouTube/Vimeo/Drive: tautan "Putar" diganti iframe hanya setelah
 * ditekan. Tanpa JavaScript tautan itu membuka videonya di tab baru.
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
      frame.focus?.();
    });

    // Dipanggil saat pindah halaman: hentikan video dengan mengembalikan tombol Putar
    box.resetEmbed = () => {
      if (!box.classList.contains('is-playing')) return;
      box.innerHTML = facade;
      box.classList.remove('is-playing');
    };
  }
}

/** Perbesar gambar dalam <dialog> bawaan browser (Esc menutup). */
function initZoom(root) {
  const buttons = $$('.book-zoom[data-zoom]', root);
  if (!buttons.length || typeof HTMLDialogElement === 'undefined') return;

  const closeLabel = root.dataset.labelClose || 'Tutup';
  const image = el('img', { alt: '' });
  const caption = el('p', { class: 'lightbox-caption' });
  const close = el('button', { type: 'button', class: 'icon-btn lightbox-close', 'aria-label': closeLabel, title: closeLabel }, '×');
  const dialog = el('dialog', { class: 'lightbox' }, close, image, caption);
  document.body.append(dialog);

  close.addEventListener('click', () => dialog.close());
  dialog.addEventListener('click', (event) => { if (event.target === dialog) dialog.close(); });

  for (const button of buttons) {
    button.addEventListener('click', () => {
      const img = $('img', button);
      image.src = button.dataset.zoom;
      image.alt = img?.alt || '';
      const figcaption = button.closest('figure')?.querySelector('figcaption');
      caption.textContent = figcaption ? figcaption.textContent.replace(/\s+/g, ' ').trim() : '';
      caption.hidden = caption.textContent === '';
      dialog.showModal();
    });
  }
}

export function initLibrary() {
  const screen = $('[data-screen="library"]');
  if (!screen) return;

  const levelId = Number(screen.dataset.levelId) || null;
  hideBrokenMedia(screen);
  initEmbeds(screen);
  initZoom(screen);

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
      report(index, slide, total);
    },
  });

  // Halaman pertama juga dihitung "dilihat"
  if (slides) report(slides.current, $$('ol.book > .slide', screen)[slides.current - 1], slides.total);
}
