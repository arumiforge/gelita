/**
 * Pustaka Kedu: navigasi halaman buku (tanpa memuat ulang), media yang gagal
 * dimuat disembunyikan beserta bingkainya, dan event `library_page_viewed`
 * { page, page_id } tiap halaman dibuka. Membuka pustaka tidak memengaruhi
 * skor sama sekali — `library_opened` sudah dicatat server.
 */
import { $, $$ } from '../core/dom.js';
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

export function initLibrary() {
  const screen = $('[data-screen="library"]');
  if (!screen) return;

  const levelId = Number(screen.dataset.levelId) || null;
  hideBrokenMedia(screen);

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
      report(index, slide, total);
    },
  });

  // Halaman pertama juga dihitung "dilihat"
  if (slides) report(slides.current, $$('ol.book > .slide', screen)[slides.current - 1], slides.total);
}
