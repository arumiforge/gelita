/**
 * Notifikasi sementara: maksimum 3 bertumpuk, hilang setelah 4 detik.
 * type: info | ok | bad | warn
 */
const MAX = 3;
const TTL = 4000;

export function toast(message, type = 'info', requestId = null) {
  const layer = document.getElementById('toast-layer');
  if (!layer) return;

  while (layer.children.length >= MAX) layer.firstElementChild.remove();

  const item = document.createElement('div');
  item.className = `toast is-${type}`;
  item.textContent = message;

  if (requestId) {
    const small = document.createElement('small');
    small.textContent = `request_id: ${requestId}`;
    item.append(small);
  }

  layer.append(item);
  setTimeout(() => item.remove(), TTL);
}
