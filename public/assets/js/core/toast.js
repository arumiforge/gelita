/**
 * Notifikasi sementara: maksimum 3 bertumpuk, hilang setelah 4 detik.
 * type: info | ok | bad | warn
 *
 * `requestId` ditampilkan kecil agar guru dapat menyebutnya saat melapor;
 * tidak pernah ada stack trace atau isi respons mentah.
 */
const MAX = 3;
const TTL = 4000;

export function toast(message, type = 'info', requestId = null) {
  const layer = document.getElementById('toast-layer');
  if (!layer || !message) return;

  // Toast flash dari server yang sudah memudar tidak ikut dihitung
  const live = Array.from(layer.children).filter((node) => !node.classList.contains('is-flash'));
  for (let i = 0; i <= live.length - MAX; i++) live[i].remove();

  const item = document.createElement('div');
  item.className = `toast is-${type}`;

  const text = document.createElement('span');
  text.textContent = message;
  item.append(text);

  if (requestId) {
    const small = document.createElement('small');
    small.textContent = `request_id: ${requestId}`;
    text.append(small);
  }

  layer.append(item);
  setTimeout(() => item.remove(), TTL);
}
