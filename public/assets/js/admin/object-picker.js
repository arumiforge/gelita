/**
 * Penempatan objek pada adegan `cari`.
 *
 * Admin mengetuk posisi pada gambar adegan → penanda muncul (berpusat di
 * titik ketuk); penanda dapat digeser (pointer) atau dipindah dengan panah
 * (Shift = langkah besar); lebarnya diatur slider. Koordinat disimpan dalam
 * PERSEN, bukan piksel — posisi tetap benar walau gambar adegan diganti
 * dengan resolusi berbeda.
 *
 * Semantik sama dengan layar permainan (.object di game.css): x/y adalah
 * sudut kiri-atas objek dalam persen lebar/tinggi adegan 16:9, w adalah
 * lebar dalam persen lebar adegan, objeknya persegi.
 *
 * Output: onChange({ x, y, w }) → config_json { x, y, w, decoy, … }.
 */
import { el } from '../core/dom.js';

const ASPECT = 16 / 9; // = .hunt-scene aspect-ratio
const round = (v) => Math.round(v * 10) / 10;
const clamp = (v, min, max) => Math.min(max, Math.max(min, v));

export function createObjectPicker({ scene = '', value = {}, onChange }) {
  let x = Number(value.x);
  let y = Number(value.y);
  let w = Number(value.w) || 12;
  let placed = Number.isFinite(x) && Number.isFinite(y);

  const stage = el('div', { class: `picker-stage${scene ? '' : ' is-drawn'}` });
  if (scene) stage.append(el('img', { src: scene, alt: 'Gambar adegan', draggable: 'false' }));

  const marker = el('button', { type: 'button', class: 'picker-marker', 'aria-label': 'Posisi objek — geser dengan panah' });
  const width = el('input', { type: 'range', min: '3', max: '40', step: '0.5', value: String(w), 'aria-label': 'Lebar objek (persen)' });
  const readout = el('output', { class: 'picker-readout num' });
  const help = el('p', { class: 'field-help' }, 'Ketuk gambar untuk menaruh objek, geser penandanya, atur lebar dengan slider. Panah memindahkan 0,5% (Shift: 2%).');

  stage.append(marker);

  const heightPct = () => w * ASPECT; // tinggi objek dalam persen tinggi adegan

  const render = () => {
    marker.hidden = !placed;
    marker.style.left = `${x}%`;
    marker.style.top = `${y}%`;
    marker.style.width = `${w}%`;
    readout.textContent = placed ? `x ${round(x)}% · y ${round(y)}% · lebar ${round(w)}%` : 'Belum ditempatkan';
  };

  const emit = () => {
    render();
    if (placed) onChange?.({ x: round(x), y: round(y), w: round(w) });
  };

  const moveTo = (clientX, clientY, centered = true) => {
    const rect = stage.getBoundingClientRect();
    const px = ((clientX - rect.left) / rect.width) * 100;
    const py = ((clientY - rect.top) / rect.height) * 100;
    x = clamp(centered ? px - w / 2 : px, 0, 100 - w);
    y = clamp(centered ? py - heightPct() / 2 : py, 0, Math.max(0, 100 - heightPct()));
    placed = true;
    emit();
  };

  stage.addEventListener('pointerdown', (event) => {
    if (event.button !== 0) return;
    event.preventDefault();
    moveTo(event.clientX, event.clientY);
    stage.setPointerCapture(event.pointerId);
    marker.focus({ preventScroll: true });
    const move = (e) => moveTo(e.clientX, e.clientY);
    const up = () => {
      stage.removeEventListener('pointermove', move);
      stage.removeEventListener('pointerup', up);
      stage.removeEventListener('pointercancel', up);
    };
    stage.addEventListener('pointermove', move);
    stage.addEventListener('pointerup', up);
    stage.addEventListener('pointercancel', up);
  });

  marker.addEventListener('keydown', (event) => {
    const step = event.shiftKey ? 2 : 0.5;
    const moves = { ArrowLeft: [-step, 0], ArrowRight: [step, 0], ArrowUp: [0, -step], ArrowDown: [0, step] };
    if (!(event.key in moves)) return;
    event.preventDefault();
    const [dx, dy] = moves[event.key];
    x = clamp(x + dx, 0, 100 - w);
    y = clamp(y + dy, 0, Math.max(0, 100 - heightPct()));
    placed = true;
    emit();
  });

  width.addEventListener('input', () => {
    w = Number(width.value);
    x = clamp(x, 0, 100 - w);
    y = clamp(y, 0, Math.max(0, 100 - heightPct()));
    emit();
  });

  render();

  return {
    element: el('div', { class: 'object-picker' }, stage, el('div', { class: 'picker-controls' }, el('label', {}, 'Lebar ', width), readout), help),
    /** Sinkron dari kolom angka manual */
    set(next) {
      if (Number.isFinite(Number(next.x))) x = Number(next.x);
      if (Number.isFinite(Number(next.y))) y = Number(next.y);
      if (Number(next.w) > 0) {
        w = Number(next.w);
        width.value = String(w);
      }
      placed = Number.isFinite(x) && Number.isFinite(y);
      render();
    },
  };
}
