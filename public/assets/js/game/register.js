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
 * - Sekolah: di provinsi berdirektori (option[data-directory], Jawa Tengah)
 *   siswa mengetik NPSN; 8 angka → GET /api/schools/lookup → nama resmi tampil
 *   di kartu, dengan peringatan bila kab/kota atau jenjangnya tidak cocok
 *   (tanda salah ketik). NPSN tak dikenal → muncul centang "Sekolahku tidak
 *   ada di daftar" untuk menulis nama. Pertukaran blok NPSN/nama dilakukan CSS
 *   :has(); di sini hanya `required` yang diselaraskan.
 * - Kolom wajib yang kosong ditandai .has-error dan digulirkan ke tampak;
 *   validasi yang mengikat tetap di server.
 *
 * Form ini POST biasa, bukan AJAX. Tombol Daftar TIDAK dinonaktifkan walau
 * sandi belum kuat: penolakan server adalah bagian dari pembelajaran dan
 * tercatat sebagai pw_weak_submit_count.
 */
import { $, $$, debounce, el, scrollToCenter } from '../core/dom.js';
import { apiRequest } from '../core/api.js';
import { initPasswordField } from './password-meter.js';

const USERNAME_PATTERN = /^[a-z0-9._]{3,30}$/;
const NPSN_PATTERN = /^\d{8}$/;

/** Kelas → jenjang sekolah yang wajar (SLB cocok untuk semua kelas). */
function stageOfGrade(value) {
  const grade = Number(value);
  if (grade >= 1 && grade <= 6) return 'sd';
  if (grade >= 7 && grade <= 9) return 'smp';
  return null;
}

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

function initSchool(form) {
  const block = $('[data-school-npsn]', form);
  const npsn = $('#school_npsn', form);
  const card = $('[data-school-card]', form);
  const manualBox = $('[data-school-manual]', form);
  const manual = $('input[name="school_manual"]', form);
  const name = $('#school_name', form);
  const country = $('[data-region-country]', form);
  const province = $('[data-region-province]', form);
  const district = $('[data-region-district]', form);
  const grade = $('#class_level', form);
  if (!block || !npsn || !card || !name || !province) return;

  const text = block.dataset;
  let school = null; // hasil cek terakhir yang ditemukan
  let lastQuery = '';

  const inDirectory = () => (country?.value ?? 'ID') === 'ID'
    && Boolean(province.selectedOptions[0]?.hasAttribute('data-directory'));

  // blok yang tampak diatur CSS :has(); kolom tersembunyi tidak boleh required
  const syncMode = () => {
    const byNpsn = inDirectory();
    npsn.required = byNpsn && !manual?.checked;
    name.required = !byNpsn || Boolean(manual?.checked);
  };

  const showManual = (visible) => {
    if (!manualBox) return;
    manualBox.hidden = !visible;
    if (!visible && manual) manual.checked = false;
  };

  const render = (state, lines) => {
    card.className = `school-card${state ? ` is-${state}` : ''}`;
    card.replaceChildren(...lines.map(([cls, value]) => el('p', { class: cls, text: value })));
  };

  const warnings = () => {
    const out = [];
    if (district?.value && school.district_code && district.value !== school.district_code) {
      out.push(['school-card-warn', text.warnDistrict.replace('{0}', school.district_name || school.district_code)]);
    }
    const expected = stageOfGrade(grade?.value);
    if (expected && school.stage && school.stage !== 'slb' && school.stage !== expected) {
      out.push(['school-card-warn', text.warnStage.replace('{0}', school.level || school.stage.toUpperCase())]);
    }
    return out;
  };

  const showSchool = () => render('', [
    ['school-card-title', text.cardTitle],
    ['school-card-name', school.name],
    ['school-card-meta', school.meta],
    ...warnings(),
  ]);

  const lookup = debounce(async () => {
    const value = npsn.value;
    if (!NPSN_PATTERN.test(value) || value === lastQuery) return;
    lastQuery = value;
    render('checking', [['school-card-meta', text.checking]]);

    try {
      const data = await apiRequest(`/schools/lookup?npsn=${encodeURIComponent(value)}`, { retry: false });
      if (npsn.value !== value) return; // siswa sudah mengetik lagi
      if (data.found) {
        school = data.school;
        showSchool();
        showManual(false);
      } else {
        school = null;
        render('missing', [['school-card-warn', text.unknown.replace('{value}', value)]]);
        showManual(true);
      }
    } catch {
      lastQuery = '';
      render('', []); // diam: server tetap memeriksa NPSN saat Daftar
    }
    syncMode();
  }, 300);

  npsn.addEventListener('input', () => {
    const digits = npsn.value.replace(/\D+/g, '').slice(0, 8);
    if (digits !== npsn.value) npsn.value = digits;
    if (digits !== lastQuery) {
      school = null;
      lastQuery = '';
      render('', []);
    }
    lookup();
  });

  manual?.addEventListener('change', syncMode);
  country?.addEventListener('change', syncMode);
  province.addEventListener('change', syncMode);
  district?.addEventListener('change', () => { if (school) showSchool(); });
  grade?.addEventListener('change', () => { if (school) showSchool(); });

  syncMode();
  if (NPSN_PATTERN.test(npsn.value)) lookup(); // kembali dari galat: cek ulang + peringatan
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
  initSchool(form);
  initUsername(form);
  initPasswordField($('.password-field', form));
  markInvalidFields(form);
  preventDoubleSubmit(form);
  fillDevice(form);
}
