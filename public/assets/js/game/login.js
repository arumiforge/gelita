/**
 * Form masuk siswa: lihat/sembunyikan sandi, peringatan Caps Lock, dan
 * tombol Masuk dinonaktifkan setelah diklik agar tidak terkirim dua kali.
 * Form POST biasa; seluruh keputusan (gagal, terkunci, wajib ganti sandi)
 * datang dari server.
 */
import { $ } from '../core/dom.js';
import { bindCapsLock, bindToggle } from './password-meter.js';
import { preventDoubleSubmit } from './register.js';

export function initLogin() {
  const form = $('[data-login-form]');
  if (!form) return;

  const password = $('#password', form);
  const field = password?.closest('.field');

  bindToggle($('.pw-toggle', form), password, {
    show: field?.dataset.showLabel ?? '',
    hide: field?.dataset.hideLabel ?? '',
  });
  bindCapsLock([password].filter(Boolean), $('.pw-caps', form));
  preventDoubleSubmit(form);
}
