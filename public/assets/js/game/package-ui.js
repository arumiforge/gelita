/**
 * Shared text for the asset package screens (docs/rencana-paket-aset.md
 * Stage 3): sizes, dates, and the PackageError codes of core/package.js
 * turned into words (Js.package*). Used by game/prepare.js ("Siapkan GELITA")
 * and game/package-inspect.js ("Periksa paket").
 */
import { currentLocale, t } from '../core/config.js';
import { ERRORS } from '../core/package.js';

const MB = 1048576;
const intlLocale = () => (currentLocale() === 'en' ? 'en-GB' : 'id-ID');

/** 12.345.678 bytes → "11,8 MB" (id) / "11.8 MB" (en); at least 0,1 MB for anything above 0. */
export function formatMb(bytes) {
  const value = Math.max(0, Number(bytes) || 0) / MB;
  const shown = value > 0 && value < 0.1 ? 0.1 : value;
  return `${new Intl.NumberFormat(intlLocale(), { minimumFractionDigits: 1, maximumFractionDigits: 1 }).format(shown)} MB`;
}

/** Whole numbers with the local thousands separator: 1.250 / 1,250. */
export function formatCount(value) {
  return new Intl.NumberFormat(intlLocale()).format(Number(value) || 0);
}

/** ISO time → "18 Okt 2026 14:30" in WIB, like fmt_date() on the server. */
export function formatDate(iso) {
  const date = new Date(iso);
  if (Number.isNaN(date.getTime())) return '';
  const parts = Object.fromEntries(new Intl.DateTimeFormat(intlLocale(), {
    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit', hourCycle: 'h23', timeZone: 'Asia/Jakarta',
  }).formatToParts(date).map((part) => [part.type, part.value]));
  return `${parts.day} ${String(parts.month).replace(/\.$/, '')} ${parts.year} ${parts.hour}:${parts.minute}`;
}

/**
 * True when the browser's whole storage quota is smaller than the package
 * (× 1.2): private mode or a browser that keeps very little (§8.3), as
 * opposed to a device whose storage is merely full (§8.4).
 */
export function quotaTooSmall(storage, totalBytes) {
  return storage?.quota !== null && storage?.quota !== undefined && totalBytes > 0 && storage.quota < totalBytes * 1.2;
}

/**
 * Words for a PackageError. `private` picks the private-mode text for a
 * quota error. Errors that are not PackageErrors get the general text.
 */
export function errorText(error, { private: privateMode = false } = {}) {
  const detail = error?.detail ?? {};
  switch (error?.code) {
    case ERRORS.NETWORK: return t('packageErrNetwork');
    case ERRORS.SERVER: return t('packageErrServer');
    case ERRORS.QUOTA:
      if (privateMode) return t('packageErrPrivate');
      return detail.shortBytes > 0 ? t('packageErrQuota', formatMb(detail.shortBytes)) : t('packageErrQuotaFull');
    case ERRORS.CORRUPT: return t('packageErrCorrupt', detail.key ?? '');
    case ERRORS.MISSING: return t('packageErrMissing', detail.key ?? '');
    case ERRORS.MANIFEST: return t('packageErrManifest');
    case ERRORS.INCOMPLETE: return t('packageErrIncomplete');
    default: return t('errUnknown');
  }
}

/** Errors that heal by themselves once the network is back: retried without a tap. */
export function retriesAlone(error) {
  return [ERRORS.NETWORK, ERRORS.SERVER, ERRORS.INCOMPLETE].includes(error?.code);
}
