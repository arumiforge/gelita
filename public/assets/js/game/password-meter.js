/**
 * Meter kekuatan & daftar syarat kata sandi (registrasi + ganti sandi).
 *
 * Aturan dibaca dari <script type="application/json" id="password-policy">
 * (PasswordPolicy::toClient()) — angka dan syarat TIDAK ditulis ulang di sini.
 * Teks tiap tingkat dibaca dari atribut data-* yang dirender server dari
 * lang('Auth.*'), sehingga ikut bahasa ID/EN.
 *
 * Isi sandi tidak pernah dikirim lewat AJAX, disimpan di browser storage,
 * ditulis ke console, atau masuk antrean event (aturan 12). Satu-satunya
 * jalur keluarnya adalah submit form ke server.
 */
import { $, $$, readJson } from '../core/dom.js';

/** Sama persis dengan PasswordPolicy::check() di server (tampilan saja). */
export function evaluate(password, username, policy) {
  const length = [...password].length;
  const bytes = new TextEncoder().encode(password).length;

  const criteria = {
    length: length >= policy.min_length && length <= policy.max_length && bytes <= (policy.max_bytes ?? Infinity),
  };
  if (policy.require_upper) criteria.upper = /[A-Z]/.test(password);
  if (policy.require_lower) criteria.lower = /[a-z]/.test(password);
  if (policy.require_digit) criteria.digit = /[0-9]/.test(password);
  if (policy.require_symbol) criteria.symbol = /[^A-Za-z0-9]/.test(password);

  const met = Object.values(criteria).filter(Boolean).length;

  let level = 'weak';
  for (const [name, [min, max]] of Object.entries(policy.levels || {})) {
    if (met >= min && met <= max) {
      level = name;
      break;
    }
  }

  const user = (username || '').trim().toLowerCase();
  const containsUsername = Boolean(policy.forbid_username) && user !== '' && password.toLowerCase().includes(user);

  return { criteria, met, level, containsUsername };
}

/** Tombol 👁 lihat/sembunyikan: type, aria-pressed, dan aria-label ikut berubah. */
export function bindToggle(button, input, { show, hide }) {
  if (!button || !input) return;
  button.addEventListener('click', () => {
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.setAttribute('aria-pressed', visible ? 'true' : 'false');
    button.setAttribute('aria-label', visible ? hide : show);
    button.classList.toggle('is-on', visible);
    input.focus();
  });
}

/** Peringatan Caps Lock (event.getModifierState) pada input sandi. */
export function bindCapsLock(inputs, notice) {
  if (!notice) return;
  const check = (event) => {
    if (typeof event.getModifierState !== 'function') return;
    notice.hidden = !event.getModifierState('CapsLock');
  };
  for (const input of inputs) {
    input.addEventListener('keyup', check);
    input.addEventListener('keydown', check);
    input.addEventListener('blur', () => { notice.hidden = true; });
  }
}

export function initPasswordField(root) {
  if (!root) return;

  const password = $('#password', root);
  const confirm = $('#password_confirm', root);
  const toggle = $('.pw-toggle', root);
  const labels = root.dataset;

  bindToggle(toggle, password, { show: labels.showLabel, hide: labels.hideLabel });
  bindCapsLock([password, confirm].filter(Boolean), $('.pw-caps', root));

  const policy = readJson('password-policy');
  // Tanpa kebijakan dari server: komponen diam, daftar syarat tetap tampil statis
  if (!policy || !password) return;

  const meter = $('.pw-meter i', root);
  const levelText = $('.pw-level', root);
  const match = $('.pw-match', root);
  const rules = $$('.pw-rules [data-rule]', root);
  const usernameInput = labels.usernameField ? document.getElementById(labels.usernameField) : null;
  const levelLabels = { weak: labels.levelWeak, medium: labels.levelMedium, strong: labels.levelStrong };

  const currentUsername = () => (usernameInput ? usernameInput.value : labels.username || '');

  const renderStrength = () => {
    const value = password.value;

    if (value === '') {
      rules.forEach((li) => li.classList.remove('is-met'));
      meter?.setAttribute('data-level', 'none');
      if (levelText) {
        levelText.textContent = '';
        levelText.className = 'pw-level';
      }
      return;
    }

    const result = evaluate(value, currentUsername(), policy);
    for (const li of rules) {
      li.classList.toggle('is-met', Boolean(result.criteria[li.dataset.rule]));
    }
    meter?.setAttribute('data-level', result.level);

    if (levelText) {
      // Nama pengguna di dalam sandi tetap diberi tahu walau 5 syarat terpenuhi
      levelText.textContent = result.containsUsername ? labels.containsUsername : (levelLabels[result.level] || '');
      levelText.className = `pw-level is-${result.containsUsername ? 'weak' : result.level}`;
    }
  };

  const renderMatch = () => {
    if (!confirm || !match) return;
    if (confirm.value === '') {
      match.textContent = '';
      match.className = 'pw-match';
      return;
    }
    const same = confirm.value === password.value;
    match.textContent = same ? labels.matchOk : labels.notMatch;
    match.className = `pw-match ${same ? 'is-ok' : 'is-bad'}`;
  };

  password.addEventListener('input', () => {
    renderStrength();
    renderMatch();
  });
  confirm?.addEventListener('input', renderMatch);
  usernameInput?.addEventListener('input', renderStrength);

  renderStrength();
  renderMatch();
}
