/**
 * Jam tantangan `m:ss`.
 *
 * Titik awal adalah `elapsed_ms` dari server (waktu sejak attempt dibuka,
 * dihitung server), jadi jam tetap benar setelah muat ulang/lanjut dan tidak
 * terpengaruh jam komputer yang salah. Selama halaman tersembunyi jam berhenti
 * berdetak (tidak ada kerja di latar); saat terlihat lagi, angkanya menyusul
 * waktu sebenarnya — sama dengan durasi yang dihitung server.
 */
export function formatClock(ms) {
  const total = Math.max(0, Math.floor(ms / 1000));
  const hours = Math.floor(total / 3600);
  const minutes = Math.floor((total % 3600) / 60);
  const seconds = String(total % 60).padStart(2, '0');
  return hours > 0 ? `${hours}:${String(minutes).padStart(2, '0')}:${seconds}` : `${minutes}:${seconds}`;
}

export function startTimer(el, elapsedMs = 0) {
  if (!el) return { stop() {}, elapsed: () => 0 };

  const origin = performance.now() - Math.max(0, Number(elapsedMs) || 0);
  let handle = null;

  const elapsed = () => performance.now() - origin;
  const render = () => { el.textContent = formatClock(elapsed()); };

  const run = () => {
    clearInterval(handle);
    handle = null;
    if (document.visibilityState === 'visible') {
      render();
      handle = setInterval(render, 1000);
    }
  };

  document.addEventListener('visibilitychange', run);
  run();

  return {
    stop() {
      clearInterval(handle);
      document.removeEventListener('visibilitychange', run);
    },
    elapsed,
  };
}
