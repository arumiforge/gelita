/**
 * Form masuk siswa: lihat/sembunyikan sandi, peringatan Caps Lock, and the
 * login sent without a page reload (owner request, 12 October 2026).
 *
 * - Empty fields stop in the browser with a modal (game/form-check.js).
 * - The form is sent with fetch (`X-Requested-With: XMLHttpRequest`), and
 *   LoginController::login() answers JSON: `{ ok, redirect }` on success
 *   (also "must change password"), or `{ ok: false, message, field }` when
 *   the name or password is wrong or the account is locked. A failure opens
 *   a modal; the password is cleared and focused, the name stays.
 * - Anything else (CSRF page, rate limit, network error, an old server)
 *   falls back to the plain POST, so the server's own page decides.
 * - Without JavaScript the form is a normal POST, as before.
 */
import { $, $$ } from '../core/dom.js';
import { t } from '../core/config.js';
import { showModal } from '../core/modal.js';
import { bindCapsLock, bindToggle } from './password-meter.js';
import { attachDeviceField } from '../core/device.js';
import { checkBeforeSubmit } from './form-check.js';

function setBusy(form, busy) {
  if (busy) form.dataset.submitting = '1';
  else delete form.dataset.submitting;
  $$('button[type="submit"]', form).forEach((button) => {
    button.disabled = busy;
    if (busy) button.setAttribute('aria-busy', 'true');
    else button.removeAttribute('aria-busy');
  });
}

function plainSubmit(form) {
  form.dataset.plain = '1';
  setBusy(form, true);
  HTMLFormElement.prototype.submit.call(form);
}

async function sendLogin(form) {
  setBusy(form, true);

  let data = null;
  try {
    const response = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
    });
    if ((response.headers.get('content-type') || '').includes('application/json')) data = await response.json();
  } catch {
    data = null;
  }

  if (!data || typeof data.ok !== 'boolean') {
    plainSubmit(form); // let the server's page answer
    return;
  }

  if (data.csrf && data.csrfName) {
    const token = form.querySelector(`input[name="${CSS.escape(data.csrfName)}"]`);
    if (token) token.value = data.csrf;
  }

  if (data.ok) {
    window.location.assign(data.redirect || form.action);
    return;
  }

  setBusy(form, false);
  const password = $('#password', form);
  const username = $('#username', form);
  const field = data.field === 'username' ? username : password;
  if (password) password.value = '';
  field?.closest('.field')?.classList.add('has-error');

  await showModal({
    type: 'wrong',
    title: t('loginFailedTitle'),
    text: data.message || t('errUnknown'),
    buttons: [{ label: t('tryAgain'), style: 'primary', value: 'ok' }],
  });
  field?.focus();
}

export function initLogin() {
  const form = $('[data-login-form]');
  if (!form) return;

  const password = $('#password', form);
  const field = password?.closest('.field');

  // First, so an empty field never reaches device.js or the request
  checkBeforeSubmit(form);

  bindToggle($('.pw-toggle', form), password, {
    show: field?.dataset.showLabel ?? '',
    hide: field?.dataset.hideLabel ?? '',
  });
  bindCapsLock([password].filter(Boolean), $('.pw-caps', form));
  attachDeviceField(form);

  // After device.js: it holds the first submit to fill `device_json`, then submits again
  form.addEventListener('submit', (event) => {
    if (form.dataset.plain) return;
    event.preventDefault();
    if (form.dataset.submitting || typeof window.fetch !== 'function') {
      if (!form.dataset.submitting) plainSubmit(form);
      return;
    }
    sendLogin(form);
  });

  form.addEventListener('input', (event) => event.target.closest('.field')?.classList.remove('has-error'));

  // Back to this page from the bfcache: buttons usable again
  window.addEventListener('pageshow', () => {
    delete form.dataset.plain;
    setBusy(form, false);
  });
}
