/**
 * Form pendaftaran bertingkat.
 *
 * - Negara/provinsi/kabupaten: server sudah merender semua kabupaten sebagai
 *   <optgroup data-province> (dari wilayah-id.json yang dibaca server), jadi
 *   form tetap lengkap tanpa JavaScript. Di sini daftar kabupaten disaring
 *   menurut provinsi terpilih — dengan melepas/memasang optgroup, karena
 *   menyembunyikan <option> tidak berlaku di semua browser.
 * - Cek nama pengguna: huruf kecil & tanpa spasi saat mengetik, pola dicek
 *   di client dulu, lalu GET /api/auth/username-available (debounce 500 ms).
 *   429/jaringan gagal → diam; server tetap memeriksa saat Daftar.
 * - Kolom wajib yang kosong ditandai .has-error dan digulirkan ke tampak;
 *   validasi yang mengikat tetap di server.
 *
 * Form ini POST biasa, bukan AJAX. Tombol Daftar TIDAK dinonaktifkan walau
 * sandi belum kuat: penolakan server adalah bagian dari pembelajaran dan
 * tercatat sebagai pw_weak_submit_count.
 */
import { $, $$, debounce, scrollToCenter } from '../core/dom.js';
import { apiRequest } from '../core/api.js';
import { initPasswordField } from './password-meter.js';

const USERNAME_PATTERN = /^[a-z0-9._]{3,30}$/;

function initRegion(form) {
  const country = $('[data-region-country]', form);
  const province = $('[data-region-province]', form);
  const district = $('[data-region-district]', form);
  if (!country || !province || !district) return;

  const groups = $$('optgroup[data-province]', district);
  const placeholder = district.querySelector('option[value=""]');
  const placeholderText = placeholder?.textContent ?? '';
  const chooseText = province.querySelector('option[value=""]')?.textContent ?? placeholderText;

  const syncDistricts = () => {
    const selected = district.value;
    const code = province.value;
    groups.forEach((group) => group.remove());

    const match = groups.find((group) => group.dataset.province === code);
    if (match) {
      district.append(match);
      district.disabled = false;
      if (placeholder) placeholder.textContent = chooseText;
    } else {
      district.disabled = code === '';
      if (placeholder) placeholder.textContent = placeholderText;
    }

    district.value = match && match.querySelector(`option[value="${CSS.escape(selected)}"]`) ? selected : '';
  };

  const syncCountry = () => {
    const indonesia = country.value === 'ID';
    province.required = indonesia;
    district.required = indonesia;
    if (!indonesia) {
      province.value = '';
      syncDistricts();
    }
  };

  province.addEventListener('change', syncDistricts);
  country.addEventListener('change', syncCountry);
  syncDistricts();
  syncCountry();
}

function initUsername(form) {
  const input = $('[data-username-check]', form);
  const status = $('#username-status', form);
  if (!input || !status) return;

  const show = (text, state) => {
    status.textContent = text;
    status.className = `username-status${state ? ` is-${state}` : ''}`;
  };

  let lastChecked = '';

  const check = debounce(async () => {
    const value = input.value;
    if (value.length < 3) {
      show('', null);
      return;
    }
    if (!USERNAME_PATTERN.test(value)) {
      show(input.dataset.format, 'bad');
      return;
    }
    if (value === lastChecked) return;

    try {
      const data = await apiRequest(`/auth/username-available?u=${encodeURIComponent(value)}`, { retry: false });
      if (input.value !== value) return; // pengguna sudah mengetik lagi
      lastChecked = value;
      if (data.available) show(input.dataset.available, 'ok');
      else show(data.reason === 'format' ? input.dataset.format : input.dataset.unavailable, 'bad');
    } catch {
      show('', null); // diam: server tetap memeriksa saat Daftar
    }
  }, 500);

  input.addEventListener('input', () => {
    const normalized = input.value.toLowerCase().replace(/\s+/g, '');
    if (normalized !== input.value) {
      const caret = input.selectionStart - (input.value.length - normalized.length);
      input.value = normalized;
      input.setSelectionRange(Math.max(0, caret), Math.max(0, caret));
    }
    lastChecked = lastChecked === input.value ? lastChecked : '';
    check();
  });

  if (input.value) check();
}

/** Kolom wajib kosong → .has-error + gulir ke kolom pertama. */
export function markInvalidFields(form) {
  let first = true;

  form.addEventListener('invalid', (event) => {
    const field = event.target.closest('.field, fieldset, .check') || event.target.parentElement;
    field?.classList.add('has-error');
    if (first) {
      first = false;
      scrollToCenter(field || event.target);
      setTimeout(() => { first = true; }, 300);
    }
  }, true);

  const clear = (event) => {
    if (event.target.validity?.valid) {
      event.target.closest('.field, fieldset, .check')?.classList.remove('has-error');
    }
  };
  form.addEventListener('input', clear);
  form.addEventListener('change', clear);
}

/** Cegah kirim ganda: tombol submit dinonaktifkan setelah form benar-benar terkirim. */
export function preventDoubleSubmit(form) {
  form.addEventListener('submit', (event) => {
    if (form.dataset.submitting) {
      event.preventDefault();
      return;
    }
    form.dataset.submitting = '1';
    $$('button[type="submit"], button:not([type])', form).forEach((button) => {
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
    });
  });
  // kembali lewat tombol Back (bfcache): aktifkan lagi
  window.addEventListener('pageshow', () => {
    delete form.dataset.submitting;
    $$('button[type="submit"], button:not([type])', form).forEach((button) => {
      button.disabled = false;
      button.removeAttribute('aria-busy');
    });
  });
}

function fillDevice(form) {
  const screenInput = $('[data-device-screen]', form);
  const touchInput = $('[data-device-touch]', form);
  if (screenInput) screenInput.value = `${window.screen.width}x${window.screen.height}`;
  if (touchInput) touchInput.value = window.matchMedia('(pointer: coarse)').matches || navigator.maxTouchPoints > 0 ? '1' : '0';
}

export function initRegister() {
  const form = $('form.register-form');
  if (!form) return;

  initRegion(form);
  initUsername(form);
  initPasswordField($('.password-field', form));
  markInvalidFields(form);
  preventDoubleSubmit(form);
  fillDevice(form);
}
