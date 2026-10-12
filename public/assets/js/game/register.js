/**
 * Pendaftaran tiga langkah (/daftar/1, /2, /3 — form POST biasa per
 * langkah; server menyimpan isian langkah 1–2 dan menjaga urutannya).
 * Perilaku dipasang menurut `data-step` formulir:
 *
 *   langkah 1  tandai kolom wajib kosong
 *   langkah 2  filter wilayah + NPSN
 *   langkah 3  cek nama pengguna, pengukur sandi, `device_json` (core/device.js)
 *
 * - Negara/provinsi/kabupaten: server sudah merender semua kabupaten sebagai
 *   <optgroup data-province> (dari wilayah-id.json yang dibaca server), jadi
 *   form tetap lengkap tanpa JavaScript. Di sini daftar kabupaten disaring
 *   menurut provinsi terpilih — dengan melepas/memasang optgroup, karena
 *   menyembunyikan <option> tidak berlaku di semua browser.
 * - Cek nama pengguna: huruf kecil & tanpa spasi saat mengetik, pola dicek
 *   di client dulu, lalu GET /api/auth/username-available (debounce 500 ms).
 *   429/jaringan gagal → diam; server tetap memeriksa saat Daftar. No check
 *   is sent once the form is submitting (see check()).
 * - Sekolah: di provinsi berdirektori (option[data-directory], Jawa Tengah)
 *   siswa mengetik NPSN; 8 angka → GET /api/schools/lookup → nama resmi tampil
 *   di kartu, dengan peringatan bila kab/kota atau jenjangnya tidak cocok
 *   (tanda salah ketik). NPSN tak dikenal → muncul centang "Sekolahku tidak
 *   ada di daftar" untuk menulis nama. Pertukaran blok NPSN/nama dilakukan CSS
 *   :has(); di sini hanya `required` yang diselaraskan.
 * - Empty or invalid fields stop the submit with one modal that names them
 *   (game/form-check.js), without a reload; the field gets .has-error and
 *   focus. Validasi yang mengikat tetap di server.
 *
 * Setiap langkah POST biasa, bukan AJAX. Tombol Daftar TIDAK dinonaktifkan walau
 * sandi belum kuat: penolakan server adalah bagian dari pembelajaran dan
 * tercatat sebagai pw_weak_submit_count.
 */
import { $, $$, debounce, el, reducedMotion } from '../core/dom.js';
import { apiRequest } from '../core/api.js';
import { initPasswordField } from './password-meter.js';
import { attachDeviceField } from '../core/device.js';
import { checkBeforeSubmit } from './form-check.js';

const USERNAME_PATTERN = /^[a-z0-9._]{3,30}$/;
const NPSN_PATTERN = /^\d{8}$/;

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
    // Once the form is on its way (preventDoubleSubmit), never ask again: the
    // server regenerates the session when the account is created, and a late
    // check carrying the old session cookie would answer with that cookie and
    // log the new student out. View transitions keep this page alive a little
    // longer during the submit, which made the race easy to hit.
    if (form.dataset.submitting) return;
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

  // The card and the "not on the list" box appear below the NPSN input, often
  // under the panel's visible area: scroll down just enough to show them.
  const reveal = (node) => {
    const panel = node?.closest('.stage-scroll');
    if (!panel || node.hidden) return;
    const below = node.getBoundingClientRect().bottom - panel.getBoundingClientRect().bottom;
    if (below > 0) panel.scrollBy({ top: below + 8, behavior: reducedMotion() ? 'auto' : 'smooth' });
  };

  const warnings = () => {
    const out = [];
    if (district?.value && school.district_code && district.value !== school.district_code) {
      out.push(['school-card-warn', text.warnDistrict.replace('{0}', school.district_name || school.district_code)]);
    }
    // GELITA khusus kelas 5 SD/MI: server menandai sekolah SMP/MTs `allowed: false`
    // dan menolak pendaftarannya; kartu ini hanya memberi tahu lebih awal.
    if (school.allowed === false) {
      out.push(['school-card-warn', text.warnStage.replace('{0}', school.level || String(school.stage || '').toUpperCase())]);
    }
    return out;
  };

  const showSchool = () => render(school.allowed === false ? 'missing' : '', [
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
        reveal(card);
      } else {
        school = null;
        render('missing', [['school-card-warn', text.unknown.replace('{value}', value)]]);
        showManual(true);
        reveal(manualBox);
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

  syncMode();
  if (NPSN_PATTERN.test(npsn.value)) lookup(); // kembali dari galat: cek ulang + peringatan
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

export function initRegister() {
  const form = $('form.register-form');
  if (!form) return;

  const step = form.dataset.step ?? '';

  // First: an empty or invalid field stops the submit with a modal, before
  // device.js or the double-submit guard run (no reload just to say "empty")
  checkBeforeSubmit(form);

  if (step === '2') {
    initRegion(form);
    initSchool(form);
  }

  if (step === '3') {
    initUsername(form);
    initPasswordField($('.password-field', form));
    attachDeviceField(form); // sebelum penjaga kirim-ganda
  }

  preventDoubleSubmit(form);
}
