/**
 * Rilis → Paket aset: drives a package build batch by batch
 * (docs/rencana-paket-aset.md §6.3). The server has no job queue, so the
 * page sends one POST per batch to `…/paket/bangun/{code}/lanjut` and shows
 * the progress; when the last batch finishes the package, the page reloads to
 * show the build summary.
 *
 * Loaded only by admin/study/package.php while a build is in progress.
 * Without JavaScript the same page offers a "Lanjutkan membangun" button.
 * Network or server errors are retried after 2, 4, and 8 seconds, then a
 * "Coba lagi" button continues from the last finished file (the server keeps
 * the build state, so nothing is processed twice).
 */
const RETRY_DELAYS = [2000, 4000, 8000];

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
}

async function postStep(url) {
  const res = await fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken() },
  });
  let json = null;
  try {
    json = await res.json();
  } catch {
    json = null;
  }
  if (!res.ok || !json || json.success === false) {
    throw new Error(json?.message || '');
  }
  return json.data;
}

function render(section, data) {
  const bar = section.querySelector('.package-progress');
  const fill = bar?.querySelector('i');
  const text = section.querySelector('[data-package-progress-text]');
  const percent = Math.max(0, Math.min(100, Number(data.percent) || 0));
  if (fill) fill.style.width = `${percent}%`;
  if (bar) bar.setAttribute('aria-valuenow', String(percent));
  if (text) {
    const format = (n) => Number(n || 0).toLocaleString('id-ID');
    text.textContent = (section.dataset.textProgress || '{done} / {total}')
      .replace('{done}', format(data.done))
      .replace('{total}', format(data.total));
  }
}

function showError(section, message, onRetry) {
  const text = section.querySelector('[data-package-progress-text]');
  if (text) text.textContent = message || section.dataset.textError || '';
  let retry = section.querySelector('[data-package-retry]');
  if (!retry) {
    retry = document.createElement('button');
    retry.type = 'button';
    retry.className = 'btn btn-primary';
    retry.dataset.packageRetry = '1';
    retry.textContent = section.dataset.textRetry || 'Coba lagi';
    text?.after(retry);
  }
  retry.hidden = false;
  retry.onclick = () => {
    retry.hidden = true;
    onRetry();
  };
}

async function drive(section) {
  const url = section.dataset.stepUrl;
  let failures = 0;

  for (;;) {
    let data;
    try {
      data = await postStep(url);
      failures = 0;
    } catch (error) {
      if (failures < RETRY_DELAYS.length) {
        await sleep(RETRY_DELAYS[failures]);
        failures += 1;
        continue;
      }
      showError(section, error.message || section.dataset.textError, () => drive(section));
      return;
    }

    render(section, data);

    if (data.finished) {
      const text = section.querySelector('[data-package-progress-text]');
      if (text) text.textContent = section.dataset.textDone || '';
      window.location.assign(section.dataset.doneUrl || window.location.href);
      return;
    }
  }
}

export function initPackageBuild(root = document) {
  const section = root.querySelector('[data-package-build]');
  if (!section || section.dataset.driving) return;
  section.dataset.driving = '1';
  // The page drives the build; the no-JavaScript button is not needed
  const fallback = section.querySelector('[data-package-continue]');
  if (fallback) fallback.hidden = true;
  drive(section);
}

initPackageBuild();
