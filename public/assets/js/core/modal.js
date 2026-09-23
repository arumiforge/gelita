/**
 * Dialog tengah layar — satu markup (components/modal.php di <template
 * id="tpl-modal">) untuk benar, salah, petunjuk, konfirmasi, dan sesi berakhir.
 *
 * - Fokus pindah ke tombol pertama saat terbuka, kembali ke pemicu saat ditutup.
 * - Fokus terperangkap di dalam modal selama terbuka.
 * - Escape menutup modal yang dapat dibatalkan (`dismissible`); modal hasil
 *   pemeriksaan tetap menyediakan tombol eksplisit.
 * - Seluruh teks ditulis dengan textContent.
 */
import { el } from './dom.js';
import { t } from './config.js';

let current = null;

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function layer() {
  return document.getElementById('modal-layer');
}

function buildFromTemplate() {
  const tpl = document.getElementById('tpl-modal');
  if (tpl) return tpl.content.firstElementChild.cloneNode(true);

  // Cadangan bila layout tidak menyertakan template (mis. halaman galat)
  return el('div', { class: 'modal', role: 'dialog', 'aria-modal': 'true', 'aria-labelledby': 'modal-title' },
    el('div', { class: 'modal-card modal-info' },
      el('div', { class: 'modal-icon', 'aria-hidden': 'true' }),
      el('h3', { class: 'modal-title', id: 'modal-title' }),
      el('p', { class: 'modal-text', id: 'modal-text' }),
      el('div', { class: 'modal-actions' })));
}

/**
 * @param {{
 *   type?: 'correct'|'wrong'|'hint'|'confirm'|'info',
 *   icon?: string,
 *   character?: string,
 *   title?: string,
 *   text?: string|string[],
 *   buttons?: Array<{ label: string, style?: string, value?: string, href?: string }>,
 *   dismissible?: boolean,
 * }} options
 * @returns {Promise<string|null>} `value` tombol yang ditekan; null bila dibatalkan
 */
export function showModal({ type = 'info', icon = null, character = null, title = '', text = '', buttons = null, dismissible = true } = {}) {
  closeModal(null);

  const host = layer();
  if (!host) return Promise.resolve(null);

  const trigger = document.activeElement instanceof HTMLElement ? document.activeElement : null;
  const modal = buildFromTemplate();
  const card = modal.querySelector('.modal-card');
  card.className = `modal-card modal-${type}`;

  const iconHost = modal.querySelector('.modal-icon');
  const iconTpl = modal.querySelector(`template[data-modal-icon="${icon || type}"]`);
  if (iconHost) {
    iconHost.replaceChildren();
    if (iconTpl) iconHost.append(iconTpl.content.cloneNode(true));
  }
  modal.querySelectorAll('template[data-modal-icon]').forEach((node) => node.remove());

  const img = modal.querySelector('.modal-character');
  if (img && character) {
    img.src = character;
    img.hidden = false;
  }

  modal.querySelector('.modal-title').textContent = title;

  const textHost = modal.querySelector('.modal-text');
  const paragraphs = Array.isArray(text) ? text.filter(Boolean) : [text].filter(Boolean);
  textHost.textContent = paragraphs.shift() || '';
  let anchor = textHost;
  for (const extra of paragraphs) {
    const p = el('p', { class: 'modal-text modal-extra' }, extra);
    anchor.after(p);
    anchor = p;
  }
  if (!textHost.textContent) textHost.hidden = true;

  const actions = modal.querySelector('.modal-actions');
  const list = buttons && buttons.length ? buttons : [{ label: t('ok'), style: 'primary', value: 'ok' }];

  return new Promise((resolve) => {
    const finish = (value) => closeModal(value);

    for (const button of list) {
      const node = button.href
        ? el('a', { class: `btn btn-${button.style || 'quiet'}`, href: button.href }, button.label)
        : el('button', { type: 'button', class: `btn btn-${button.style || 'quiet'}` }, button.label);
      node.addEventListener('click', () => finish(button.value ?? button.label));
      actions.append(node);
    }

    const onKey = (event) => {
      if (event.key === 'Escape' && dismissible) {
        event.preventDefault();
        finish(null);
        return;
      }
      if (event.key !== 'Tab') return;

      const nodes = Array.from(modal.querySelectorAll(FOCUSABLE)).filter((n) => !n.hidden);
      if (!nodes.length) return;
      const first = nodes[0];
      const last = nodes[nodes.length - 1];
      if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
      }
    };

    const onBackdrop = (event) => {
      if (event.target === host && dismissible) finish(null);
    };

    host.replaceChildren(modal);
    host.hidden = false;
    document.body.classList.add('has-modal');
    document.addEventListener('keydown', onKey, true);
    host.addEventListener('click', onBackdrop);

    current = { resolve, trigger, onKey, onBackdrop };
    (actions.querySelector('button, a') || modal).focus();
  });
}

export function closeModal(value = null) {
  if (!current) return;
  const { resolve, trigger, onKey, onBackdrop } = current;
  current = null;

  const host = layer();
  document.removeEventListener('keydown', onKey, true);
  host?.removeEventListener('click', onBackdrop);
  if (host) {
    host.replaceChildren();
    host.hidden = true;
  }
  document.body.classList.remove('has-modal');

  if (trigger && document.contains(trigger)) trigger.focus({ preventScroll: true });
  resolve(value);
}

export const isModalOpen = () => current !== null;

/** Konfirmasi ya/tidak → Promise<boolean>. */
export async function confirmDialog({ title, text, confirmLabel = t('yes'), cancelLabel = t('cancel'), danger = false } = {}) {
  const value = await showModal({
    type: 'confirm',
    title,
    text,
    buttons: [
      { label: cancelLabel, style: 'quiet', value: 'cancel' },
      { label: confirmLabel, style: danger ? 'danger' : 'primary', value: 'confirm' },
    ],
  });
  return value === 'confirm';
}
