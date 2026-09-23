/**
 * Unggah media.
 *
 * 1. Dimensi gambar dibaca dengan createImageBitmap() SEBELUM mengunggah.
 * 2. Dibandingkan dengan "Ukuran wajib" slot (pola asset_key dari
 *    Config\Gelita::$assetSizes, dikirim server di #asset-sizes).
 * 3. Tidak cocok (server selalu mode ketat) → ditolak di client dengan pesan
 *    yang menyebut ukuran yang diminta dan yang diberikan.
 * 4. Lolos → form POST biasa ke /admin/media/unggah; server memeriksa ulang
 *    dengan getimagesize() — unggahan tidak pernah dipercaya karena lolos
 *    pemeriksaan client — lalu kembali ke halaman ini dengan pesan hasil.
 *
 * Kotak unggah menerima berkas yang diseret; tombol Unggah di tiap baris aset
 * mengisi asset_key baris itu. Nama berkas asli diabaikan server.
 */
import { $, $$, el, readJson, scrollToCenter } from '../core/dom.js';

const MAX_BYTES = 64 * 1024 * 1024;

/** fnmatch sederhana: '*' = sembarang karakter (sama dengan pola di server). */
function matches(pattern, key) {
  const regex = new RegExp(`^${pattern.split('*').map((part) => part.replace(/[.+?^${}()|[\]\\]/g, '\\$&')).join('.*')}$`);
  return regex.test(key);
}

function requiredSize(sizes, key) {
  for (const [pattern, size] of Object.entries(sizes || {})) {
    if (pattern === key || matches(pattern, key)) return size;
  }
  return null;
}

async function dimensions(file) {
  if (!file.type.startsWith('image/') || file.type === 'image/svg+xml' || !('createImageBitmap' in window)) return null;
  try {
    const bitmap = await createImageBitmap(file);
    const size = [bitmap.width, bitmap.height];
    bitmap.close?.();
    return size;
  } catch {
    return null; // biar server yang menilai berkas yang tidak terbaca
  }
}

export function initMediaUpload(root = document) {
  const form = $('form.upload-box[action$="media/unggah"]', root);
  if (!form) return;

  const keyInput = $('[name="asset_key"]', form);
  const fileInput = $('[name="file"]', form);
  const submit = $('button[type="submit"]', form);
  const sizes = readJson('asset-sizes')?.sizes ?? {};
  const message = el('p', { class: 'field-error', role: 'alert', hidden: true });
  const info = el('p', { class: 'field-help upload-info', 'aria-live': 'polite' });
  fileInput.after(message, info);

  const fail = (text) => {
    message.textContent = text;
    message.hidden = false;
    fileInput.closest('.field')?.classList.add('has-error');
  };
  const clear = () => {
    message.hidden = true;
    fileInput.closest('.field')?.classList.remove('has-error');
  };

  const describe = () => {
    const need = requiredSize(sizes, keyInput.value.trim());
    info.textContent = need ? `Ukuran wajib untuk slot ini: ${need[0]} × ${need[1]} px.` : '';
  };

  const check = async () => {
    clear();
    const file = fileInput.files?.[0];
    if (!file) return true;
    if (file.size > MAX_BYTES) {
      fail(`Berkas ${(file.size / 1048576).toFixed(1)} MB melebihi batas 64 MB.`);
      return false;
    }
    const need = requiredSize(sizes, keyInput.value.trim());
    if (!need) return true;
    const has = await dimensions(file);
    if (has && (has[0] !== need[0] || has[1] !== need[1])) {
      fail(`Ukuran gambar ${has[0]}×${has[1]} px tidak sesuai ketentuan ${need[0]}×${need[1]} px untuk ${keyInput.value.trim()}.`);
      return false;
    }
    return true;
  };

  keyInput.addEventListener('input', () => { describe(); if (fileInput.files?.length) check(); });
  fileInput.addEventListener('change', check);

  form.addEventListener('submit', async (event) => {
    if (form.dataset.checked) return;
    event.preventDefault();
    if (!(await check())) {
      fileInput.focus();
      return;
    }
    form.dataset.checked = '1';
    if (submit) {
      submit.disabled = true;
      submit.setAttribute('aria-busy', 'true');
    }
    form.submit();
  });

  // Seret-lepas berkas ke kotak unggah
  ['dragenter', 'dragover'].forEach((type) => form.addEventListener(type, (event) => {
    if (!event.dataTransfer?.types?.includes('Files')) return;
    event.preventDefault();
    form.classList.add('is-dragover');
  }));
  ['dragleave', 'drop'].forEach((type) => form.addEventListener(type, () => form.classList.remove('is-dragover')));
  form.addEventListener('drop', (event) => {
    if (!event.dataTransfer?.files?.length) return;
    event.preventDefault();
    const transfer = new DataTransfer();
    transfer.items.add(event.dataTransfer.files[0]);
    fileInput.files = transfer.files;
    check();
  });

  // Tombol Unggah per baris aset
  for (const button of $$('[data-upload-key]', root)) {
    button.hidden = false;
    button.addEventListener('click', () => {
      keyInput.value = button.dataset.uploadKey;
      describe();
      scrollToCenter(form);
      fileInput.focus({ preventScroll: true });
      fileInput.click();
    });
  }

  describe();
}
