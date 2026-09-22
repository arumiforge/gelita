# 06_JAVASCRIPT.md — Perilaku Frontend

> **Revisi 2 (21 September 2026).** Ditambahkan: `game/password-meter.js` (meter kekuatan + daftar syarat + keterangan bila sandi belum kuat), cek ketersediaan nama pengguna, `game/login.js`; engine `boleh` memakai verdict Benar/Salah/Pendapat dengan panel teks bacaan dan dua sumber; engine `rumpang` memakai bank kata dari server (jawaban + pengecoh); editor konten mendukung `verdict_*`, `ordering`, teks bacaan, dan pengecoh; `admin/bank-import.js`. `SESSION_CODE` di client dihapus karena identitas siswa kini dipegang session server.

---

## Tujuan

Membuat seluruh JavaScript GELITA: lapisan inti (API, event queue, audio, modal, toast), lima mesin tantangan, perilaku halaman permainan, dan perilaku panel admin termasuk chart.

---

## Konteks

JavaScript ditulis sebagai **ES modules** modern, tanpa bundler dan tanpa build step. Berkas dimuat langsung dengan `<script type="module">`; browser menangani `import`. Target dukungan: Chrome/Edge/Firefox/Safari versi 2 tahun terakhir.

Tidak ada Vue, React, Vite, Angular, atau SPA framework. Halaman tetap dirender server; JavaScript hanya menambah perilaku pada markup yang sudah ada.

Aturan dasar yang berlaku di seluruh berkas:

1. **Server adalah sumber kebenaran.** Client tidak pernah menghitung benar/salah, skor, bintang, atau status unlock. Ia hanya menampilkan apa yang dikembalikan server.
2. **Tidak ada kunci jawaban di client.** Payload soal dari `/api/nodes/{id}/attempts` tidak memuat `answer_key_json`.
3. **Semua event penting dikirim ke server**, dengan `client_event_id` agar pengiriman ulang tidak menggandakan data.
4. **UI hanya diperbarui setelah server menjawab** untuk hal-hal yang authoritative. Umpan balik yang tidak authoritative (animasi klik, sorot kotak aktif) boleh langsung.

---

## Struktur File

```text
public/assets/js/
├── game.js                  titik masuk halaman permainan
├── admin.js                 titik masuk panel admin
├── core/
│   ├── config.js            membaca #app-config
│   ├── api.js               fetch + CSRF + penanganan error seragam
│   ├── events.js            antrean event + pengiriman batch
│   ├── storage.js           antrean offline di localStorage
│   ├── modal.js             dialog tengah layar
│   ├── toast.js             notifikasi sementara
│   ├── audio.js             pemutar narasi + efek suara (Howler)
│   ├── timer.js             jam tantangan
│   ├── confetti.js          efek konfeti ringan (canvas)
│   └── dom.js               pembantu kecil: $, $$, on, html
├── game/
│   ├── hud.js               lentera, tombol suara, konfirmasi keluar
│   ├── register.js          form pendaftaran bertingkat + cek nama pengguna
│   ├── password-meter.js    meter kekuatan & daftar syarat sandi (registrasi + ganti sandi)
│   ├── login.js             form masuk: lihat sandi, Caps Lock, cegah kirim ganda
│   ├── intro.js             slide cerita
│   ├── dialogue.js          dialog karakter
│   ├── map.js               peta Kedu & peta wilayah
│   ├── library.js           Pustaka Kedu
│   ├── reflection.js        formulir kritik & saran
│   └── challenge.js         pemilih mesin + kerangka bersama
├── engines/
│   ├── puzzle.js
│   ├── rumpang.js
│   ├── boleh.js
│   ├── pilihan.js
│   └── cari.js
└── admin/
    ├── filters.js           filter bar + sinkron URL
    ├── charts.js            adapter ECharts
    ├── tables.js            pengurutan, pencarian, pagination client
    ├── content-editor.js    form item dinamis per interaction_type
    ├── bank-import.js       unggah workbook bank soal + pratinjau
    ├── object-picker.js     penempatan koordinat objek `cari`
    ├── media-upload.js      unggah + pemeriksaan ukuran
    └── export.js            polling status export
```

---

## Implementasi — Lapisan Inti

### `core/config.js`

```js
const el = document.getElementById('app-config');
export const CONFIG = el ? JSON.parse(el.textContent) : {};
export const LOCALE = document.body.dataset.locale || 'id';
// Tidak ada SESSION_CODE di client: identitas siswa dipegang session server (cookie HttpOnly CI4).
```

### `core/api.js`

Satu pintu untuk seluruh permintaan ke server.

```js
export async function apiRequest(path, { method = 'GET', body = null } = {}) {
  const headers = { 'Accept': 'application/json' };
  if (body) headers['Content-Type'] = 'application/json';
  if (method !== 'GET') headers['X-CSRF-TOKEN'] = CONFIG.csrfHash;

  const res = await fetch(`${CONFIG.apiBase}${path}`, {
    method,
    headers,
    credentials: 'same-origin',
    body: body ? JSON.stringify(body) : null,
  });

  let json = null;
  try { json = await res.json(); } catch { /* biarkan null */ }

  if (!res.ok || !json || json.success === false) {
    throw new ApiError(json?.code || 'NETWORK', json?.message || 'Gagal menghubungi server.', res.status, json);
  }
  return json.data;
}

export class ApiError extends Error {
  constructor(code, message, status, payload) { … }
}
```

Penanganan galat terpusat:

| `code` | Yang dilakukan UI |
|---|---|
| `INVALID_SESSION` | modal "Sesimu sudah berakhir. Silakan masuk lagi." + tombol ke `/masuk` |
| `PASSWORD_CHANGE_REQUIRED` | langsung ke `/ganti-sandi` |
| `LEVEL_LOCKED` | toast + kembali ke peta |
| `ATTEMPT_CLOSED` | reload halaman tantangan |
| `RATE_LIMITED` | tunda dan coba lagi otomatis (backoff 2s, 4s, 8s) |
| `NETWORK` | masuk antrean offline (lihat `storage.js`), tampilkan penanda "tersimpan sementara" |
| lainnya | toast dengan `message` dari server + `request_id` kecil di pojok |

**Tidak pernah** menampilkan stack trace atau isi respons mentah kepada anak.

### `core/events.js`

Antrean event dengan pengiriman batch dan idempotency.

```js
let queue = [];
let sequence = 0;
let flushTimer = null;

export function emit(type, { levelId, nodeId, attemptId, itemId, payload } = {}) {
  queue.push({
    client_event_id: crypto.randomUUID(),
    event_type: type,
    occurred_at: new Date().toISOString(),
    sequence_no: ++sequence,
    level_id: levelId ?? null,
    node_id: nodeId ?? null,
    attempt_id: attemptId ?? null,
    item_id: itemId ?? null,
    payload: payload ?? null,
  });
  scheduleFlush();
}

export async function flush(force = false) {
  if (!queue.length) return;
  const batch = queue.splice(0, 50);          // maksimum sesuai Config\Gelita
  try {
    await apiRequest('/events', { method: 'POST', body: { events: batch } });
  } catch (e) {
    Storage.queueEvents(batch);               // simpan, kirim ulang nanti
  }
}
```

* `scheduleFlush()` mengirim setiap **3 detik** atau saat antrean mencapai 20 event, mana yang lebih dulu.
* `flush(true)` dipanggil pada `visibilitychange` (tab disembunyikan) dan `pagehide`.
* Untuk pengiriman saat tab ditutup, pakai `navigator.sendBeacon()` dengan `Blob` bertipe `application/json`; bila tidak tersedia, `fetch(..., { keepalive: true })`.

`client_event_id` memakai `crypto.randomUUID()`. Karena id ini dibuat sekali dan ikut disimpan saat masuk antrean offline, pengiriman ulang setelah koneksi pulih tidak akan menggandakan event.

### `core/storage.js`

Antrean offline. Memakai `localStorage`, dibatasi agar tidak menggelembung.

```js
const KEY = 'gelita.eventQueue';
const MAX = 500;

export const Storage = {
  queueEvents(batch) { /* baca, gabung, potong ke MAX terbaru, tulis */ },
  takeAll() { /* ambil semua lalu kosongkan */ },
  size() { … },
};
```

Saat halaman dimuat dan saat `online` menyala kembali, `game.js` memanggil `Storage.takeAll()` dan mengirim ulang isinya. Server membalas `duplicate` untuk yang sudah masuk, jadi aman.

`localStorage` **hanya** untuk antrean sementara dan preferensi tampilan (suara nyala/mati, volume). Ia bukan tempat menyimpan catatan penelitian. Semua yang bernilai sudah ada di server.

### `core/modal.js`

```js
export function showModal({ type, icon, character, title, text, buttons }) { … }
export function closeModal() { … }
export function confirmDialog({ title, text, confirmLabel, cancelLabel }) { /* Promise<boolean> */ }
```

* Fokus dipindahkan ke tombol pertama saat modal terbuka, dan dikembalikan ke pemicu saat ditutup.
* `Escape` menutup modal yang dapat dibatalkan; modal hasil pemeriksaan tetap menyediakan tombol eksplisit.
* Fokus terperangkap di dalam modal selama terbuka.

### `core/audio.js`

Dua peran terpisah.

**Efek & musik (Howler):**

```js
export const Sfx = {
  unlock(),            // dipanggil pada interaksi pertama pengguna
  play(name),          // correct, wrong, click, shard, region-done, page, lock
  music(name),         // map, region, challenge, library
  stopMusic(),
  setVolume(v),
  setEnabled(on),      // tersimpan di localStorage
};
```

Berkas suara opsional: bila gagal dimuat, aplikasi tetap berjalan tanpa bunyi. Tidak ada nada sintetis pengganti — lebih baik diam daripada berbunyi aneh.

**Narasi (elemen `<audio>` + telemetry):**

```js
export function initAudioPlayers(root = document) {
  // untuk tiap .audio-player:
  //  - play / pause / replay / transcript
  //  - lacak playIndex, listenedMs, completed
  //  - emit ke /api/audio-events pada play, pause, replay, complete
}
```

Telemetry audio yang dikirim:

```json
{ "audio_asset_id": 42, "action": "complete", "play_index": 2,
  "listened_ms": 11840, "completed": true,
  "occurred_at": "2026-09-21T09:14:22.481Z",
  "client_event_id": "…", "attempt_id": 118 }
```

`listened_ms` dihitung dari akumulasi waktu putar sungguhan, bukan dari `duration`. Server tetap memperlakukan angka ini sebagai data client: perbandingan final memakai `duration_ms` aset dari database sebagai pembagi, bukan angka dari browser.

**Kebijakan autoplay:** audio tidak pernah diputar otomatis sebelum ada interaksi pengguna. Pada layar dialog, tombol putar tampil besar dan berkedip halus sekali agar terlihat.

### `core/timer.js`, `core/toast.js`, `core/confetti.js`, `core/dom.js`

* `timer.js` — `startTimer(el, startedAt)` menampilkan `m:ss`, berhenti saat halaman disembunyikan.
* `toast.js` — `toast(message, type)`; menumpuk maksimum 3, hilang setelah 4 detik.
* `confetti.js` — canvas ringan, maksimum 60 partikel, langsung berhenti bila `prefers-reduced-motion`.
* `dom.js` — `$`, `$$`, `on(el, evt, sel, fn)` untuk delegasi, `esc(str)`.

---

## Implementasi — Mesin Tantangan

Semua mesin mengikuti kontrak yang sama, didefinisikan di `game/challenge.js`:

```js
export function bootChallenge() {
  const data = JSON.parse(document.getElementById('challenge-data').textContent);
  const engine = ENGINES[data.engine_type];
  const ctx = {
    attemptId: data.attempt_id,
    nodeId: data.node_id,
    levelId: data.level_id,
    config: data.config,
    items: data.items,
    submit,      // untuk engine per-item
    check,       // untuk engine batch
    complete,
    useHint,
  };
  engine.mount(ctx);
  emit('challenge_opened', { nodeId: data.node_id, attemptId: data.attempt_id });
}
```

Fungsi bersama:

```js
async function submit(itemId, answer, extra = {}) {
  return apiRequest(`/attempts/${attemptId}/responses`, {
    method: 'POST',
    body: { item_id: itemId, answer, client_event_id: crypto.randomUUID(),
            occurred_at: new Date().toISOString(), ...extra },
  });
}

async function check(answers) {
  return apiRequest(`/attempts/${attemptId}/check`, {
    method: 'POST',
    body: { answers, client_event_id: crypto.randomUUID(),
            occurred_at: new Date().toISOString() },
  });
}

async function complete() {
  const r = await apiRequest(`/attempts/${attemptId}/complete`, { method: 'POST', body: {…} });
  await flush(true);
  window.location.href = r.redirect;      // ke /selesai/{attemptId}
}
```

---

### Fitur: Puzzle Gambar

```text
Feature   Puzzle gambar (engine puzzle, mode arrange)
Trigger   Peserta mengetuk dua keping lalu menekan "Periksa gambar"
Input     urutan 9 keping saat ini (array indeks)
Process   client hanya menukar posisi; tidak menilai apa pun
Request   POST /api/attempts/{id}/check
          { answers: [{ item_id, answer: { order: [3,1,0,…] } }] }
Response  { results: { "88": false }, correct_count: 0, total: 1,
            all_correct: false, check_count: 1,
            detail: { "88": { pieces_correct: 6 } } }
UI Update benar  → keping terkunci, gambar utuh ditampilkan, tombol Lanjut
          salah  → keping yang salah posisi diberi kelas .is-misplaced,
                   modal "Gambarnya belum utuh, masih ada N keping yang keliru"
Error     INVALID_SESSION → modal sesi berakhir
          jaringan mati   → tombol Periksa dinonaktifkan sementara + toast
```

Detail interaksi:

* Ketuk keping pertama → `.is-selected`; ketuk keping kedua → tukar, keduanya beranimasi singkat.
* Dukungan drag-and-drop HTML5 sebagai tambahan, bukan pengganti. Ketuk-untuk-tukar wajib ada karena lebih andal di tablet.
* Keyboard: panah untuk berpindah keping, `Enter` untuk memilih/menukar.
* Mode `ordering` (Wonosobo node 1) memakai daftar kartu teks yang dapat digeser naik-turun; tombol ▲▼ wajib tersedia untuk pengguna keyboard.

`pieces_correct` dikirim server di `detail` agar pesan "masih ada N keping" akurat tanpa client mengetahui susunan benarnya.

---

### Fitur: Isi Rumpang

```text
Feature   Isi rumpang (engine rumpang)
Trigger   Peserta mengetuk kotak rumpang lalu memilih kata dari bank,
          atau mengetik langsung bila varian tanpa bank kata
Input     pasangan { item_id, answer: { text: "Magelang" } }
Process   client mengelola kotak aktif, menandai kata terpakai
Request   POST /api/attempts/{id}/check  dengan seluruh isian sekaligus
Response  { results: { "12": true, "13": false, … }, correct_count: 3,
            total: 4, all_correct: false, check_count: 2 }
UI Update kotak benar → .is-correct (hijau + ✓); salah → .is-wrong (merah + ✗)
          semua benar → konfeti + modal "Semua kalimat benar!" + tombol Lanjut
          sebagian    → modal "Belum semuanya tepat, N dari M sudah benar.
                        Ketuk kotak merah untuk menggantinya."
Error     kotak belum terisi → modal lokal, request TIDAK dikirim
```

Detail:

* Isi bank kata diambil dari `payload.word_bank` (jawaban item terpilih + pengecoh, sudah diacak server). Client tidak tahu kata mana milik rumpang mana.
* Kata di bank diberi id unik, sehingga dua jawaban yang kebetulan sama tidak saling menandai "terpakai" pada chip yang keliru.
* Mengetuk kotak yang sudah terisi mengembalikan katanya ke bank dan menjadikan kotak itu aktif.
* Setelah mengisi satu kotak, fokus pindah otomatis ke kotak kosong berikutnya.
* Varian tanpa bank kata memakai `<input>`; `Enter` memindahkan fokus ke kotak berikutnya, bukan mengirim form.
* Jawaban yang sudah benar **tidak** dihapus saat pemeriksaan ulang.

---

### Fitur: Nilai Pernyataan (Benar / Salah / Pendapat)

```text
Feature   Kartu penilaian pernyataan (engine boleh)
Trigger   Peserta memilih salah satu tombol verdict pada tiap kartu,
          lalu menekan Periksa jawaban
Input     [{ item_id, answer: { verdict: "benar"|"salah"|"pendapat" }, reason_text?: "…" }]
Process   tombol dirender dari payload.verdict_options (["benar","salah"] atau
          ["benar","salah","pendapat"]); client tidak tahu kuncinya
Request   POST /api/attempts/{id}/check
Response  { results: {...}, correct_count, total, all_correct, check_count }
UI Update kartu benar → border hijau + ✓; salah → border merah + ✗
          kartu salah digulirkan ke tengah layar satu per satu saat diperbaiki
Error     ada kartu belum dipilih → kartu itu diberi .has-error selama 2.6 detik,
          digulirkan ke tengah, modal "Ada N kartu yang belum kamu tentukan"
```

Teks bacaan (Temanggung node 3) dirender sekali di atas kartu dari `payload.passages`; setiap kartu yang merujuk passage itu diberi label kecil "Berdasarkan Teks A". Pada layar sempit, teks bacaan dilipat dengan tombol **Baca teks** yang tetap menempel di atas. Dua sumber (Wonosobo node 3) ditampilkan berdampingan di dalam kartu, bertumpuk pada layar sempit.

Varian beralasan (Magelang node 3): textarea alasan ikut dikirim sebagai `reason_text`. Alasan **tidak memengaruhi benar/salah** — server menyimpannya untuk review guru. UI tidak boleh menampilkan alasan sebagai "benar" atau "salah"; cukup tanda "alasanmu tersimpan".

---

### Fitur: Kuis Pilihan

```text
Feature   Kuis pilihan bergambar (engine pilihan)
Trigger   Peserta mengetuk satu opsi
Input     { item_id, answer: { option_key: "tmg3c" } }
Process   seluruh opsi langsung dinonaktifkan agar tidak ada klik ganda
Request   POST /api/attempts/{id}/responses
Response  { correct: false, first_pass: false,
            correct_option_key: "tmg3a", feedback: "…",
            progress: { answered: 2, total: 3 } }
UI Update opsi yang dipilih diberi hijau/merah; bila salah, opsi kunci
          ikut ditandai hijau setelah 400 ms; feedback ditampilkan bila ada
          setelah 1.1 detik → soal berikutnya, indikator langkah maju
          soal terakhir → complete()
Error     jaringan gagal → opsi diaktifkan kembali, toast "Coba ketuk lagi"
```

Penting: `allow_retry = false` pada engine ini, jadi jawaban pertama sekaligus jawaban final. `correct_option_key` dikirim server **setelah** jawaban masuk, bukan sebelumnya.

Soal yang punya `source_text` menampilkan teks sumber di atas pertanyaan, dengan tombol "Baca teks" pada layar sempit.

---

### Fitur: Cari Objek Budaya

```text
Feature   Cari objek budaya (engine cari)
Trigger   Peserta mengetuk objek di dalam adegan
Input     { item_id: <objek yang diklik>, answer: { target_item_id: <petunjuk aktif> } }
Request   POST /api/attempts/{id}/responses
Response  benar  → { correct: true, next_clue: { index: 2, text: "…" }, … }
          salah  → { correct: false, decoy: true, explanation: "…",
                     wrong_click_count: 1 }
UI Update benar  → objek diberi tanda temuan, dicoret dari daftar sisa,
                   petunjuk berikutnya muncul + dibacakan
          salah  → objek bergetar 700 ms, modal penjelasan:
                   jebakan → "Itu bukan dari Kedu. <Nama> memang budaya Indonesia,
                              tetapi berasal dari daerah lain."
                   bukan jebakan → "<Nama> juga warisan <wilayah>, tetapi bukan
                                    yang dimaksud petunjuk kali ini."
          semua ketemu → konfeti + modal + complete()
Error     sama seperti engine lain
```

Detail:

* Objek diposisikan dengan persen (`left`, `top`, `width`) supaya adegan tetap benar pada semua ukuran layar.
* Objek jebakan dirender identik dengan objek asli. Tidak ada kelas CSS, atribut, atau urutan DOM yang membocorkan status jebakan — status itu hanya ada di server.
* Petunjuk aktif dibacakan lewat audio player Mbah Kedu bila asetnya tersedia.
* Keyboard: `Tab` berpindah antar objek, `Enter` memilih. Daftar sisa juga dapat dipakai untuk melompat ke petunjuk tertentu.

---

### Fitur: Petunjuk (Hint)

```text
Feature   Petunjuk
Trigger   Peserta menekan tombol 💡 pada bar tantangan
Request   POST /api/attempts/{id}/hints  { hint_id, item_id? }
Response  { text: "…", hint_count: 1 }
UI Update modal berisi teks petunjuk; penghitung petunjuk pada bar bertambah
Catatan   Penalti petunjuk diatur scoring_profile di server, bukan JavaScript.
          UI hanya menampilkan pesan netral: "Memakai petunjuk akan sedikit
          menurunkan nilai kemandirianmu." Tanpa angka, karena angkanya
          dapat diubah admin.
```

Tombol petunjuk hanya tampil bila `hints_available > 0` pada payload attempt.

---

### Fitur: Keluar dari Tantangan

```text
Feature   Konfirmasi keluar
Trigger   Peserta menekan tombol keluar, atau menekan tombol Back browser
Process   confirmDialog("Tinggalkan tantangan ini? Jawabanmu yang sudah
          diperiksa tetap tersimpan, tetapi tantangan ini belum selesai.")
Request   bila ya → POST /api/attempts/{id}/abandon { reason: "user_exit" }
UI Update navigasi ke /wilayah/{code}
```

Selain itu, `beforeunload` memanggil `flush(true)` dan mengirim event `challenge_abandoned` lewat `sendBeacon`. Jangan memasang `event.preventDefault()` pada `beforeunload` — memaksa dialog bawaan browser pada anak SD hanya membingungkan.

---

## Implementasi — Perilaku Halaman Game

### `game/hud.js`

* Tombol suara → `Sfx.setEnabled()`, status tersimpan di `localStorage`.
* Lentera dapat dibuka untuk melihat 15 titik serpihan per wilayah.
* Jumlah serpihan diperbarui dari respons `complete()`, bukan dihitung sendiri.
* Heartbeat: `POST /api/session/heartbeat` tiap 60 detik selama tab terlihat.

### `game/register.js`

```text
Feature   Form pendaftaran bertingkat
Trigger   Perubahan pada select Negara dan Provinsi
Process   memuat public/assets/data/wilayah-id.json sekali (fetch + cache di memori)
          Negara = Indonesia  → tampilkan provinsi & kabupaten
          Negara = lainnya    → sembunyikan, tampilkan input nama negara
          Provinsi dipilih    → isi ulang select kabupaten
Validasi  client memberi tanda merah pada kolom kosong sebelum submit,
          tetapi validasi yang mengikat tetap di server
UI Update kolom bermasalah diberi .has-error dan digulirkan ke tampak
```

```text
Feature   Cek nama pengguna
Trigger   Mengetik di kolom username (debounce 500 ms, minimal 3 karakter)
Process   ubah ke huruf kecil & buang spasi saat mengetik;
          periksa pola ^[a-z0-9._]{3,30}$ di client dulu
Request   GET /api/auth/username-available?u=jaka.kedu
Response  { available: true } | { available: false, reason: "taken"|"reserved"|"format" }
UI Update ✓ "Nama pengguna bisa dipakai" / ✗ "Sudah dipakai, coba tambahkan angka"
Error     429 atau jaringan gagal → diam saja; server tetap memeriksa saat Daftar
```

Form ini POST biasa, bukan AJAX — lebih tahan terhadap koneksi sekolah yang putus-putus. Tombol **Daftar** tidak dinonaktifkan walau sandi belum kuat: penolakan dari server adalah bagian dari pembelajaran dan tercatat sebagai metrik.

### `game/password-meter.js`

Dipakai `register.js` dan halaman ganti sandi. Aturan dibaca dari `<script type="application/json" id="password-policy">` yang dikirim server — **angka dan syarat tidak ditulis ulang di JavaScript**.

```text
Feature   Meter kekuatan & daftar syarat kata sandi
Trigger   input pada #password dan #password_confirm; keyup untuk Caps Lock
Input     isi kolom sandi, isi kolom username (untuk syarat tambahan)
Process   criteria = {
            length: 8 ≤ panjang ≤ 64,
            upper : /[A-Z]/, lower: /[a-z]/, digit: /[0-9]/, symbol: /[^A-Za-z0-9]/
          }
          met   = jumlah true (0–5)
          level = met ≤ 2 ? weak : met ≤ 4 ? medium : strong
          containsUsername = username ≠ '' && sandi (huruf kecil) memuat username
Request   tidak ada — seluruhnya lokal, isi sandi tidak pernah dikirim selain saat submit form
UI Update tiap <li data-rule> diberi .is-met bila terpenuhi (ikon ✗ → ✓)
          meter: data-level = weak | medium | strong
          #pw-level (aria-live): Auth.levelWeak / levelMedium / levelStrong
          containsUsername → tampilkan Auth.containsUsername walau 5 syarat terpenuhi
          #password_confirm tidak sama → .pw-match berisi Auth.notMatch; sama → ✓
          Caps Lock menyala (event.getModifierState('CapsLock')) → tampilkan .pw-caps
          tombol 👁 → ganti type password/text, aria-pressed & aria-label ikut berubah
Error     tidak ada kondisi galat; bila JSON kebijakan tidak ada, komponen diam
          dan daftar syarat tetap tampil statis
```

Teks untuk setiap tingkat dibaca dari atribut `data-*` yang dirender server dari `lang('Auth.*')`, sehingga ikut bahasa ID/EN.

Yang **tidak** dilakukan skrip ini: menyimpan sandi ke `localStorage`, mengirimnya lewat AJAX, menuliskannya ke `console`, atau memasukkannya ke antrean event.

### `game/login.js`

* Tombol lihat/sembunyikan sandi dan peringatan Caps Lock (fungsi dari `password-meter.js`, tanpa meter).
* Tombol **Masuk** dinonaktifkan setelah diklik agar tidak terkirim dua kali.
* Form POST biasa; seluruh keputusan (gagal, terkunci, wajib ganti sandi) datang dari server.

### `game/intro.js` dan `game/dialogue.js`

* Maju/mundur slide tanpa memuat ulang halaman; `history.replaceState` menjaga nomor slide di URL hash.
* `emit('dialogue_advanced', { payload: { index } })` tiap perpindahan.
* Karakter yang berbicara diberi kelas `.is-speaking`, yang mendengar `.is-listening`.
* `Space` dan panah kanan = lanjut; ini cara tercepat di papan tulis interaktif.

### `game/map.js`

* Titik terkunci → toast, bukan navigasi.
* Pra-muat gambar latar wilayah berikutnya saat peta dibuka, agar perpindahan mulus.
* `emit('level_opened', { levelId })` saat peta wilayah dibuka.

### `game/library.js`

* Navigasi halaman, video dimuat `preload="none"`.
* Gambar/video yang gagal dimuat disembunyikan beserta bingkainya.
* `emit('library_page_viewed', { levelId, payload: { page } })`.

### `game/reflection.js`

* Bintang 1–5 dapat diklik dan dinavigasi keyboard.
* Client memeriksa minimal dua textarea terisi sebelum submit, server memeriksa ulang.

---

## Implementasi — Panel Admin

### `admin/charts.js` — adapter ECharts

Seluruh chart dibuat lewat satu adapter, sehingga query analitik tidak pernah terikat pada library chart.

```js
export function renderChart(el, type, data, options = {}) { … }
```

`type` yang didukung dan penggunaannya:

| type | Dipakai di | Bentuk data |
|---|---|---|
| `line` | perubahan pretest → posttest | `{ labels: [], series: [{ name, values: [] }] }` |
| `bar` | skor per wilayah, sebaran umur | idem |
| `stacked-bar` | pemeriksaan & petunjuk per node | `{ labels, series: [{name, values}] }` |
| `heatmap` | kesulitan 15 node (3 baris × 5 kolom) | `{ x: [], y: [], values: [[x,y,v]] }` |
| `scatter` | durasi vs ketepatan per peserta | `{ points: [{x,y,label}] }` |
| `matrix` | penguasaan indikator × wilayah | seperti heatmap dengan label teks |
| `gauge` | tingkat penyelesaian | `{ value }` |

Adapter menetapkan tema GELITA sekali (warna dari token CSS dibaca lewat `getComputedStyle`), sehingga setiap chart konsisten tanpa mengulang konfigurasi.

Data diambil dari `/api/admin/*`, bukan ditanam di HTML, agar filter dapat diubah tanpa memuat ulang halaman:

```js
const data = await apiRequest(`/admin/nodes?${new URLSearchParams(filters)}`);
renderChart(document.getElementById('chart-difficulty'), 'heatmap', data);
```

Chart wajib punya:

* kondisi kosong ("Belum ada data untuk filter ini"), bukan kanvas kosong;
* `aria-label` dan tabel data alternatif yang tersembunyi secara visual tetapi terbaca pembaca layar;
* `resize` handler yang memanggil `chart.resize()`.

### `admin/filters.js`

* Perubahan filter memperbarui `URLSearchParams` dan memuat ulang data chart lewat AJAX, tanpa memuat ulang halaman.
* `history.replaceState` menjaga URL tetap dapat dibagikan.
* Tombol "Atur ulang" mengembalikan ke filter bawaan.

### `admin/tables.js`

* Pengurutan kolom di sisi client untuk tabel ≤ 500 baris; di atas itu pengurutan dilakukan server lewat query string.
* Pencarian menyaring baris yang tampak.
* Kolom `p` dan `D` pada tabel butir soal diberi kelas warna sesuai tafsirnya.

### `admin/content-editor.js`

```text
Feature   Form item yang menyesuaikan interaction_type
Trigger   Admin memilih interaction_type pada form item
Process   menampilkan blok field yang sesuai dan menyembunyikan sisanya:
            single_choice    → editor 4 opsi + radio kunci + unggah gambar opsi
            fill_blank_bank  → kalimat dengan penanda ___ + jawaban ID/EN
            fill_blank_free  → kalimat + daftar jawaban yang diterima (satu per baris)
            verdict_card     → pernyataan + pilihan kunci (hanya opsi dari verdict_options node)
                               + gambar + pemilih teks bacaan + editor dua sumber (opsional)
            verdict_reason   → sama + kolom contoh alasan ID/EN (rubrik guru)
            find_object      → pemilih koordinat (object-picker.js) + centang jebakan
            puzzle_arrange   → pilih gambar + ukuran grid
            ordering         → daftar potongan teks ID/EN; urutan di editor = kunci jawaban
                               (server mengacak ulang saat dikirim ke siswa)
            semua jenis      → review_status, review_note, reference_source
Validasi  client memeriksa: kalimat rumpang harus memuat ___,
          single_choice harus punya tepat satu kunci,
          kunci verdict harus termasuk verdict_options node,
          find_object harus punya koordinat, objek jebakan wajib punya wrong_feedback
UI Update pratinjau tampilan item seperti yang akan dilihat anak
```

Setiap kolom teks punya sepasang kotak berdampingan: Indonesia di kiri, English di kanan. Kotak English yang dikosongkan akan memakai teks Indonesianya saat permainan, jadi aman ditinggal kosong.

Form node menyediakan editor daftar pengecoh rumpang (pasangan ID/EN, tambah/hapus baris) dan kotak centang `verdict_options` untuk node `boleh`.

### `admin/bank-import.js`

```text
Feature   Impor workbook bank soal
Trigger   Admin memilih berkas .xlsx lalu menekan Pratinjau
Process   periksa ekstensi .xlsx dan ukuran ≤ 20 MB di client (umpan balik cepat saja)
Request   POST /admin/konten/impor-bank/pratinjau (form multipart biasa)
Response  halaman dirender ulang oleh server dengan ringkasan, galat, peringatan
UI Update tabel galat dapat difilter per sheet; klik baris galat → sorot nomor baris;
          tombol Impor aktif hanya bila jumlah galat = 0;
          klik Impor → konfirmasi "Impor 119 butir ke 15 node?" → POST jalankan
Error     berkas ditolak server → pesan di atas form
```

### `admin/object-picker.js`

```text
Feature   Penempatan objek pada adegan `cari`
Trigger   Admin membuka form item find_object
Process   menampilkan gambar adegan; admin mengetuk posisi → penanda muncul
          penanda dapat digeser; lebar objek diatur slider (persen)
          koordinat disimpan sebagai persen, bukan piksel
Output    config_json { x: 18, y: 62, w: 13, decoy: false }
```

Menyimpan dalam persen membuat posisi tetap benar meski gambar adegan diganti dengan resolusi berbeda.

### `admin/media-upload.js`

```text
Feature   Unggah media
Trigger   Admin menyeret berkas ke kotak, atau menekan tombol Unggah pada baris aset
Process   1. baca dimensi dengan createImageBitmap() sebelum mengunggah
          2. bandingkan dengan "Ukuran wajib" pada baris itu
          3. bila tidak cocok dan mode ketat menyala → tolak di client
             dengan pesan yang menyebut ukuran yang diminta dan yang diberikan
          4. bila lolos → FormData ke POST /admin/media/unggah
Request   multipart/form-data { asset_key, file }
Response  { media_id, storage_path, width, height, sha256 }
UI Update baris tabel diperbarui, pratinjau disegarkan dengan ?v=timestamp
Error     ukuran salah, MIME salah, berkas terlalu besar → pesan spesifik
```

Pemeriksaan di client hanya untuk memberi umpan balik cepat. Server **selalu** memeriksa ulang; unggahan tidak pernah dipercaya karena lolos pemeriksaan client.

Nama berkas asli diabaikan. Berkas disimpan dengan nama resmi dari `asset_key`, sehingga `WhatsApp Image 2026.jpg` tetap menjadi `assets/uploads/library.temanggung.3a.jpg`.

### `admin/export.js`

```text
Feature   Status export
Trigger   Admin menekan "Buat XLSX" / "Buat PDF"
Request   POST /admin/ekspor/xlsx (form biasa) → mengembalikan export_id
Process   polling GET /api/admin/exports/{id}/status tiap 2 detik,
          maksimum 5 menit, dengan backoff bila gagal
Response  { status: "running"|"done"|"failed", row_count, file_sha256 }
UI Update bar kemajuan tak tentu → saat "done", tombol Unduh aktif dan
          SHA-256 ditampilkan agar berkas dapat diverifikasi
Error     "failed" → pesan galat dari server + tombol coba lagi
```

---

## Library JavaScript

| Nama | Tujuan | Versi / strategi | Sumber aset | Lokasi | Halaman |
|---|---|---|---|---|---|
| **Apache ECharts** | seluruh chart panel admin: garis, batang, stacked bar, heatmap, scatter, matrix, gauge | 6.x, versi dikunci di repo, diperbarui manual | unduhan rilis resmi | `public/assets/vendor/echarts.min.js` | `/admin/dashboard`, `/admin/analitik/*`, `/admin/peserta/{id}` |
| **Howler.js** | efek suara dan musik latar; menangani kebijakan autoplay dan pemutaran tumpang tindih secara konsisten lintas browser | 2.2.x, dikunci di repo | unduhan rilis resmi | `public/assets/vendor/howler.min.js` | seluruh halaman game |

Itu saja. Alasan library lain **tidak** dipakai:

* **Chart.js** — ECharts sudah mencakup semua jenis chart yang dibutuhkan termasuk heatmap dan matrix. Dua library chart berarti dua tema dan dua API untuk pekerjaan yang sama.
* **SheetJS/XLSX** — seluruh export dibuat server dengan PhpSpreadsheet. Membangun workbook di browser membatasi ukuran data dan melewati pemeriksaan otorisasi.
* **localForage** — antrean offline hanya menyimpan event sementara, bukan basis data. `localStorage` dengan batas 500 entri sudah cukup dan tidak menambah 30 KB.
* **Panzoom** — zoom peta dapat dilakukan dengan `transform` CSS dan dua event pointer.
* **anime.js** — animasi yang dibutuhkan (crossfade latar, getar objek, keping bertukar, karakter berbicara) semuanya dapat ditulis dengan CSS transition dan Web Animations API.
* **Alpine/htmx** — halaman sudah dirender server; interaksi yang tersisa spesifik dan lebih jelas ditulis langsung.

Tidak ada CDN. Kedua berkas vendor ada di repositori dan dimuat dari domain sendiri.

> **Status:** `public/assets/vendor/` masih kosong — kedua berkas di atas ditambahkan pada tahap ini, belum ada di repo. Lihat [02_PROJECT_FOUNDATION.md → *Status `public/assets/vendor/`*](02_PROJECT_FOUNDATION.md).

---

## Aturan Sistem

1. Client tidak pernah menentukan benar/salah, skor, bintang, unlock, atau jumlah serpihan. Semuanya datang dari respons server.
2. Payload soal tidak memuat kunci jawaban. Bila suatu tampilan butuh kunci (misalnya menyorot opsi benar setelah dijawab), kunci itu dikirim server **sesudah** jawaban diterima.
3. Setiap request yang menulis membawa `client_event_id` unik, sehingga pengiriman ulang tidak menggandakan data.
4. Event dikirim berkelompok maksimum 50 per request, otomatis tiap 3 detik, dan dipaksa saat tab disembunyikan atau ditutup.
5. `localStorage` hanya untuk antrean event sementara dan preferensi tampilan. Tidak ada catatan penelitian yang bergantung padanya.
6. Semua penulisan ke DOM dari data server memakai `textContent` atau pembuatan elemen, bukan `innerHTML` dengan string gabungan. Bila `innerHTML` tidak terhindarkan untuk markup statis, nilai dinamis tetap di-escape lebih dulu.
7. Tombol yang mengirim request dinonaktifkan selama request berjalan, agar tidak ada klik ganda.
8. Galat jaringan tidak pernah menampilkan pesan teknis kepada anak. Pesan yang muncul berupa kalimat biasa dan langkah berikutnya.
9. `prefers-reduced-motion` dihormati: konfeti, getar, dan crossfade dimatikan.
10. Seluruh interaksi tantangan dapat diselesaikan dengan keyboard.
11. Tidak ada permintaan ke domain pihak ketiga dari halaman permainan.
12. Isi kata sandi tidak pernah dikirim lewat AJAX, disimpan di browser storage, ditulis ke `console`, atau masuk antrean event. Satu-satunya jalur keluarnya adalah submit form ke server.
13. Aturan kekuatan sandi dibaca dari JSON kebijakan yang dikirim server; JavaScript tidak memuat angka atau syarat versinya sendiri.

---

## Dependency

Dari **04_CONTROLLER_ROUTE.md**: seluruh endpoint API, bentuk request dan response, daftar kode galat.

Dari **05_VIEW_UI.md**: id dan kelas elemen (`#challenge-data`, `#app-config`, `#password-policy`, `.password-field`, `.pw-rules`, `#puzzle-board`, `#option-grid`, `#hunt-scene`, `.blank`, `.verdict-card`, `.passage`, `.sources`, `.audio-player`, wadah chart admin), token CSS untuk tema chart.

Dari **02_PROJECT_FOUNDATION.md**: `Config\Gelita::$passwordPolicy` (disalin ke `#password-policy`) dan kunci bahasa `Auth.*`.

Dari **01_DATABASE.md**: daftar `event_type` yang valid dan bentuk `interaction_type`.

---

## Hasil Akhir

Setelah tahap ini selesai:

* Kelima mesin tantangan berfungsi penuh: puzzle, rumpang, boleh, pilihan, dan cari, termasuk varian ordering dan varian beralasan.
* Menyelesaikan satu node menghasilkan skor dan bintang dari server, lentera bertambah, dan peserta diarahkan ke layar selesai.
* Menyelesaikan 5 node membuka wilayah berikutnya; menyelesaikan 15 node membuka Balai Refleksi.
* Seluruh event gameplay tercatat di `game_event_logs`, termasuk saat koneksi sempat putus di tengah sesi.
* Telemetry audio tercatat di `audio_usage_events`.
* Panel admin menampilkan seluruh chart dengan data sungguhan, dan filter mengubahnya tanpa memuat ulang halaman.
* Halaman registrasi menandai syarat sandi satu per satu saat anak mengetik, menampilkan tingkat lemah/sedang/kuat, memberi keterangan bila sandi belum kuat atau memuat nama pengguna, dan memeriksa ketersediaan nama pengguna.
* Engine `boleh` menampilkan Benar/Salah (atau Benar/Salah/Pendapat pada Magelang 3), teks bacaan bersama, dan dua sumber.
* Editor konten dapat membuat item untuk kesembilan `interaction_type`, termasuk penempatan koordinat objek `cari`, urutan potongan `ordering`, dan kunci verdict.
* Admin dapat mengunggah workbook bank soal, melihat pratinjau galat per baris, lalu mengimpornya.
* Export dapat dibuat dan statusnya terpantau sampai berkasnya siap diunduh.
