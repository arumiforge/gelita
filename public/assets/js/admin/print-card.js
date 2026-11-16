/**
 * Printable field session card (/admin/sesi-lapangan/{id}/cetak): shows the
 * Print button, which opens the browser's print dialog. Without JavaScript
 * the page prints with Ctrl+P as usual.
 */
const button = document.querySelector('[data-print]');
if (button) {
  button.hidden = false;
  button.addEventListener('click', () => window.print());
}
