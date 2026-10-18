/**
 * Mulai gate for the asset package (docs/rencana-paket-aset.md P11, §8.7):
 * with package mode on, a device that can hold the package goes through
 * "Siapkan GELITA" (`/siapkan`) before a game session starts. The server
 * cannot tell (the package lives in the browser), so the pages before a
 * session carry `data-package-gate` and this module decides:
 *
 *   data-package-gate="link"  welcome screen: Mulai ([data-package-start])
 *                             leads to /siapkan instead of /mulai
 *   data-package-gate="page"  /mulai, /masuk, /daftar/{n}: the page is
 *                             replaced by /siapkan?lanjut={data-package-next}
 *
 * Nothing happens with package mode off (the page names no package), during a
 * running game session (S8), after online mode was chosen in this browser
 * session, or in a browser that cannot hold a package (§8.6, plays online).
 * When the boot mirror says the device does not hold the page's package the
 * decision is immediate; otherwise check() confirms it (Cache Storage and the
 * service worker).
 */
import { CONFIG, url } from '../core/config.js';
import * as Package from '../core/package.js';

export async function initPackageGate(root = document.querySelector('[data-package-gate]')) {
  const boot = window.GELITA_PACKAGE_BOOT || {};
  if (!root || !boot.code || CONFIG.sessionTag || boot.online) return 'skip';
  if (!Package.support().download) return 'skip';

  const next = root.dataset.packageNext || '';
  const target = url(next ? `siapkan?lanjut=${encodeURIComponent(next)}` : 'siapkan');
  const hold = () => {
    if (root.dataset.packageGate === 'page') {
      window.location.replace(target);
    } else {
      for (const link of document.querySelectorAll('[data-package-start]')) link.href = target;
    }
    return 'hold';
  };

  if (!boot.holds) return hold();
  try {
    const state = await Package.check();
    return state.canStart ? 'pass' : hold();
  } catch {
    // Server unreachable: the device holds the page's package, so let it be
    return 'pass';
  }
}
