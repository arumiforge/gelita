/**
 * Media upload.
 *
 * 1. Image dimensions are read with createImageBitmap() BEFORE uploading.
 * 2. They are compared with the slot size (asset_key patterns from
 *    Config\Gelita::$assetSizes, sent by the server in #asset-sizes) using the
 *    same rule as App\Libraries\ImageFitter: smaller on either side, or a
 *    width-to-height ratio more than 1% off, is refused in the browser with a
 *    message naming both sizes. Larger with the same ratio passes; the server
 *    shrinks it to the slot size.
 *    The file type is checked against the slot's allowed types
 *    (App\Libraries\ImageSlots::types(), sent in #asset-sizes as `types`).
 * 3. Passing files go out as a plain form POST to /admin/media/unggah; the
 *    server checks again with getimagesize() (passing the browser check is
 *    never trusted) and returns to this page with the result message.
 *
 * The upload box accepts dropped files; each asset row's Upload button fills
 * in that row's asset_key. The original file name is ignored by the server.
 */
import { $, $$, el, readJson, scrollToCenter } from '../core/dom.js';

const MAX_BYTES = 64 * 1024 * 1024;
const RATIO_TOLERANCE = 0.01; // ImageFitter::RATIO_TOLERANCE

/** fnmatch sederhana: '*' = sembarang karakter (sama dengan pola di server). */
function matches(pattern, key) {
  const regex = new RegExp(`^${pattern.split('*').map((part) => part.replace(/[.+?^${}()|[\]\\]/g, '\\$&')).join('.*')}$`);
  return regex.test(key);
}

/** Why an image of size has cannot fill a slot of size need, or null (ImageFitter::problem). */
function sizeProblem(has, need, key) {
  if (has[0] < need[0] || has[1] < need[1]) {
    return `Gambar ini ${has[0]} × ${has[1]} piksel, lebih kecil dari ukuran ${key} (${need[0]} × ${need[1]} piksel). Pakai gambar yang paling kecil ${need[0]} × ${need[1]} piksel, lalu unggah lagi.`;
  }
  const slot = need[0] * has[1];
  if (Math.abs(has[0] * need[1] - slot) > RATIO_TOLERANCE * slot) {
    return `Bentuk gambar ini (${has[0]} × ${has[1]} piksel) tidak sama dengan bentuk ${key} (${need[0]} × ${need[1]} piksel). Potong dulu gambarnya dengan perbandingan lebar dan tinggi yang sama, lalu unggah lagi.`;
  }
  return null;
}

const TYPE_MIMES = { png: 'image/png', jpg: 'image/jpeg', webp: 'image/webp', gif: 'image/gif', svg: 'image/svg+xml' };
const typeName = (type) => (type === 'webp' ? 'WebP' : type.toUpperCase());

/** Allowed image types of a key (ImageSlots::types()): exact key first, then the first matching pattern. */
function allowedTypes(rules, key) {
  if (rules?.[key]) return rules[key];
  for (const [pattern, types] of Object.entries(rules || {})) {
    if (pattern.includes('*') && matches(pattern, key)) return types;
  }
  return null;
}

/** "PNG atau WebP", "JPG, PNG, atau WebP" (ImageSlots::typesText()). */
function typesText(types) {
  const names = types.map(typeName);
  const last = names.pop();
  return names.length === 0 ? last : `${names.join(', ')}${names.length > 1 ? ',' : ''} atau ${last}`;
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
  const config = readJson('asset-sizes') ?? {};
  const sizes = config.sizes ?? {};
  const typeRules = config.types ?? {};
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
    info.textContent = need ? `Gambar untuk kode berkas ini berukuran ${need[0]} × ${need[1]} piksel. Gambar yang lebih besar dengan bentuk sama diperkecil otomatis.` : '';
  };

  const check = async () => {
    clear();
    const file = fileInput.files?.[0];
    if (!file) return true;
    if (file.size > MAX_BYTES) {
      fail(`Berkas ini ${(file.size / 1048576).toFixed(1).replace('.', ',')} MB, lebih besar dari batas 64 MB.`);
      return false;
    }
    const key = keyInput.value.trim();
    const types = file.type.startsWith('image/') ? allowedTypes(typeRules, key) : null;
    if (types && !types.some((type) => TYPE_MIMES[type] === file.type)) {
      fail(`Gambar untuk ${key} harus berformat ${typesText(types)}. Simpan ulang gambarnya dengan format itu, lalu unggah lagi.`);
      return false;
    }
    const need = requiredSize(sizes, key);
    if (!need) return true;
    // Same rule as MediaStore::store(): an SVG has no pixel size to check
    if (file.type === 'image/svg+xml') {
      fail(`Gambar untuk ${keyInput.value.trim()} harus PNG, JPG, atau WebP berukuran ${need[0]} × ${need[1]} piksel. Berkas SVG tidak bisa dipakai di sini.`);
      return false;
    }
    const has = await dimensions(file);
    const problem = has && sizeProblem(has, need, keyInput.value.trim());
    if (problem) {
      fail(problem);
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
