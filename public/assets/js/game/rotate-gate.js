/**
 * Layar putar: aksesibilitas penghalang potret.
 *
 * Tampil/hilangnya penghalang sepenuhnya diatur CSS (layout.css) dengan
 * media query yang sama seperti di bawah. Modul ini hanya memastikan
 * fokus keyboard dan pembaca layar tidak tertinggal di belakangnya:
 * selama penghalang tampil, seluruh isi <body> lainnya `inert` dan fokus
 * dipindah ke penghalang; saat perangkat diputar mendatar, keduanya
 * dikembalikan.
 */
const QUERY = '(orientation: portrait) and (pointer: coarse)';

export function initRotateGate() {
  const gate = document.querySelector('.rotate-gate');
  if (!gate || !window.matchMedia) return;

  const mq = window.matchMedia(QUERY);
  let returnFocus = null;

  const apply = () => {
    const blocked = mq.matches;
    for (const el of document.body.children) {
      if (el !== gate && !['SCRIPT', 'TEMPLATE'].includes(el.tagName)) el.inert = blocked;
    }
    if (blocked) {
      const active = document.activeElement;
      returnFocus = active && active !== document.body && active !== gate ? active : null;
      gate.focus({ preventScroll: true });
    } else if (returnFocus?.isConnected) {
      returnFocus.focus({ preventScroll: true });
      returnFocus = null;
    }
  };

  apply();
  mq.addEventListener('change', apply);
}
