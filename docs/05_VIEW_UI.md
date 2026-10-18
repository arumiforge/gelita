# 05_VIEW_UI.md — View, UI, dan CSS

Dokumen ini menggambarkan antarmuka GELITA seperti yang ada di kode: layout, komponen bersama, setiap layar permainan, panel admin, dan susunan CSS. Perilaku JavaScript dijelaskan di [06_JAVASCRIPT.md](06_JAVASCRIPT.md); route dan controller di [04_CONTROLLER_ROUTE.md](04_CONTROLLER_ROUTE.md).

## Prinsip UI

Frontend memakai **CodeIgniter View + HTML5 + CSS3** saja: tanpa Vue, React, Vite, framework CSS, atau build step. Halaman dirender server; JavaScript (modul ES di `public/assets/js/`) hanya menambah perilaku.

Ada dua wajah:

* **Permainan** — layar penuh berlatar gambar, HUD lentera di atas, navigasi bawah, tombol besar ramah anak kelas 5 SD/MI, dwibahasa Indonesia/Inggris.
* **Panel admin** — kepala panel, sidebar kiri, panel dan tabel padat, satu bahasa (Indonesia), tanpa chart.

Identitas visual: **navy gelap + emas**, judul dengan serif klasik (Cinzel), isi dengan sans humanis (Plus Jakarta Sans), angka dengan mono (IBM Plex Mono). Panel perkamen (`.panel-parchment`) dipakai untuk formulir dan teks cerita, panel berukir (`.panel-carved`) untuk arena dan layar selesai.

**Bahasa.** Teks antarmuka permainan selalu lewat `lang()` dari `app/Language/{id,en}/Game.php` dan `Auth.php`; teks yang dipakai JavaScript dari `Js.php` (dikirim lewat `js_config()`). Teks konten memakai `$entity->text('field', $locale)` atau `tr($row, 'field', $locale)`, yang jatuh ke bahasa Indonesia bila versi Inggris kosong. Panel admin memakai `app/Language/id/Admin.php` dan bahasa sehari-hari untuk guru ("soal", bukan "butir"; "kesalahan", bukan "galat"); kode yang tersimpan di database ditampilkan lewat `admin_label()`. Pemilih bahasa adalah form POST biasa (`components/lang-switch`): halaman dimuat ulang pada URL yang sama dan progres tidak berubah karena seluruh state ada di database.

**Aksesibilitas.**

* Kontras teks cukup terhadap latar; latar bergambar selalu diberi lapisan gelap di belakang teks.
* Fokus keyboard selalu terlihat (cincin emas di permainan, cincin indigo di panel); layout `game`, `admin`, dan `auth` punya tautan lompat `.skip-link` ke isi (`#app`, `#admin-main`, `#auth-main`).
* Target sentuh minimal 44 × 44 px.
* Warna tidak pernah menjadi satu-satunya penanda: benar/salah memakai ikon ✓/✗ dan teks, bintang memakai `stars_html()` dengan teks alternatif "2 / 3", status kelengkapan aset memakai ikon + teks.
* Gambar informatif ber-`alt` bermakna; gambar dekoratif `alt=""` atau `aria-hidden`. Ikon SVG dari `icon()` selalu `aria-hidden`; maknanya ada di teks atau `aria-label` induknya.
* Elemen interaktif memakai `<button>`, `<a>`, `<details>`, dan input bawaan, bukan `<div>` berpenangan klik. Pilihan Benar/Salah dan bintang kritik & saran adalah radio bawaan.
* Overlay layar penuh (`components/rotate-gate`, tirai, `partials/region-intro`) membuat sisa halaman `inert` selama tampil.

**Tanpa JavaScript.** Informasi dasar terbaca dan formulir dapat dikirim tanpa JavaScript: pendaftaran 3 langkah, masuk, ganti sandi, peta, slide cerita (jangkar `#slide-n` + CSS `:target`), buku Pustaka, kritik & saran, dan seluruh panel admin. Teknik yang dipakai:

| Kebutuhan | Teknik |
|---|---|
| Slide cerita, dialog, Kenali wilayah, buku Pustaka | jangkar + CSS `:target`; slide aktif menyembunyikan yang lain lewat `:has(.slide:target)` |
| Kartu "Ketuk untuk mulai", kontrol narasi | atribut `hidden` di markup; `game/narrator.js` yang membukanya |
| Tirai pemuatan | `<noscript>` memuat `css/noscript.css` yang menyembunyikan tirai; animasi pengaman CSS memudarkannya bila modul JS gagal |
| Rincian lentera, transkrip audio, kredit media, metadata audit | `<details>` |
| Konfirmasi keluar tantangan, hapus soal, setujui semua narasi, nonaktifkan akun | `<details class="confirm">` |
| Kolom NPSN/nama sekolah, kolom sekolah akun guru, panduan jenis soal | CSS `:has()` pada pilihan `<select>`/radio |
| Toast pesan flash | memudar sendiri lewat animasi CSS |

Pengecualian yang disengaja: **arena tantangan** membutuhkan JavaScript. Tanpa JavaScript, arena tetap merender soal tetapi menampilkan peringatan `Game.needsJs` di dalam `<noscript>`.

**Gerak dikurangi.** Dengan `prefers-reduced-motion: reduce`: konfeti, crossfade latar, Ken Burns, mesin ketik, efek layar, orbit/denyut tirai, jejak kaki, dan visual tirai tantangan dimatikan atau tampil diam dalam keadaan akhirnya; keping Susun Gambar yang disusun otomatis saat kesempatan habis langsung berpindah tanpa animasi.

---

## Struktur view

```text
app/Views/
├── layouts/
│   ├── game.php                 area permainan
│   ├── admin.php                panel guru & admin
│   └── auth.php                 masuk panel staf
├── components/                  dirender lewat component() atau $this->include()
│   ├── hud.php · lantern.php · lang-switch.php · rotate-gate.php
│   ├── nav-bar.php · modal.php · toast.php
│   ├── character.php · narration.php · audio-player.php
│   ├── narrator-controls.php · narrator-tap.php · curtain.php
│   ├── star-rating.php · stat-tile.php · password-field.php
│   ├── admin-sidebar.php · admin-filter-bar.php · data-table.php · admin-table.php · admin-pagination.php
│   └── media-field.php · media-datalist.php · audio-select.php
├── partials/
│   ├── cine-slides.php          slide sinematik layar penuh (intro, penutup)
│   ├── dialogue-scene.php       panggung Jaka & Mbah Kedu (dialog wilayah, wilayah tuntas)
│   ├── region-intro.php         overlay "Mengenal {wilayah}" di Peta Kedu
│   ├── slide-nav.php            Kembali · titik · Lanjut / tombol akhir
│   ├── footsteps.php            jejak kaki (tirai wilayah, kartu bab)
│   ├── challenge-art.php        visual tirai tantangan per engine
│   ├── library-lock.php         progres + penjelasan Pustaka terkunci
│   ├── media-credit.php         ikon ⓘ kredit media Pustaka
│   ├── form-errors.php          ringkasan galat formulir permainan
│   ├── flash.php                galat/catatan panel admin
│   ├── admin-head.php           kepala halaman panel
│   └── content-nav.php          sub-navigasi konten satu wilayah
├── game/
│   ├── welcome.php · start.php · start-choice.php
│   ├── register/_head.php · _stepper.php · step-1.php · step-2.php · step-3.php
│   ├── login.php · change-password.php
│   ├── intro.php · map-kedu.php · dialogue.php · map-level.php · mission-brief.php
│   ├── challenge/_frame.php · puzzle.php · rumpang.php · boleh.php · cari.php
│   ├── challenge-finished.php · challenge-result.php · region-done.php · ending.php
│   ├── library-index.php · library.php · library-locked.php
│   └── profile.php · reflection.php
├── admin/
│   ├── login.php · dashboard.php
│   ├── participants/index.php · show.php · reset-result.php
│   ├── sessions/index.php · show.php
│   ├── data/index.php
│   ├── feedback/index.php · export/index.php
│   ├── content/index.php · level.php · node.php · items.php · passages.php · library.php · dialogues.php · verify.php · import.php
│   ├── narration/index.php · upload.php · approve-all.php
│   ├── media/index.php · show.php · audio.php · checklist.php
│   ├── study/index.php · releases.php
│   ├── governance/index.php · audit.php
│   ├── schools/index.php · staff/index.php
│   └── account/password.php
├── pdf/_style.php · report-participant.php
└── errors/html/error_404.php · error_500.php (+ berkas bawaan CodeIgniter), errors/cli/
```

---

## Layout

### `layouts/game.php`

Section yang diisi halaman:

| Section | Isi |
|---|---|
| `title` | judul tab (ditambah " · GELITA") |
| `background` | URL latar; kosong → gradien CSS |
| `bodyClass` | kelas tambahan `<body>`: `is-welcome`, `is-cinematic` (intro, penutup), `is-challenge` (arena), `is-celebrate` (layar selesai) |
| `overlay` | lapisan selebar panggung di luar `<main>` (tirai, `<template id="tpl-curtain-…">`, overlay Kenali wilayah) |
| `content` | isi halaman di `<main id="app" class="stage-main">` |
| `hudTitle` | teks tengah HUD (mis. Peta Kedu, Pustaka); tanpa ini HUD menampilkan nama wilayah dari `$level` |
| `hudStyle` | `menu` puts the redesigned HUD `components/hud-menu` in place of `components/hud` (result screen only, 18 October 2026) |
| `nav` | `components/nav-bar` (opsional) |
| `head`, `scripts` | tambahan khusus halaman |

`<html class="game-stage">`; di `<head>`, sebelum CSS, skrip kecil `core/viewport.js` (skrip biasa, bukan modul) mengisi `--app-w`/`--app-h` dari ukuran jendela (Dok 3 §2.1). Dulu skrip sebaris; dipindah ke berkas agar lolos CSP produksi.

Urutan `<body class="game …" data-locale data-base data-screen-code>`: `components/rotate-gate` (di **luar** panggung), lalu `.stage-frame` › `#stage.stage` yang berisi `.skip-link`, latar `.scene` (dua `<img class="scene-img">` `#scene-a`/`#scene-b` untuk crossfade), section `overlay`, `components/hud`, `<main id="app" class="stage-main">`, section `nav`, `#modal-layer`, dan `components/toast` (dengan flash `error`). Sesudah panggung: `<template id="tpl-modal">` (isi `components/modal`), `<script type="application/json" id="app-config">` berisi `js_config($locale)`, `vendor/howler.min.js`, dan modul `js/game.js`. Lihat *Panggung 16:9*.

* `<head>` memuat `tokens.css`, `base.css`, `stage.css`, `components.css`, `game.css`, `cinematic.css`, dan `<noscript><link … noscript.css></noscript>`; font Plus Jakarta Sans 400 dan Cinzel 700 di-preload. Layout ini juga menautkan `manifest.json` dan ikon aplikasi terpasang (lihat *Layar putar dan aplikasi terpasang*).
* `data-screen-code` adalah kode layar dari `App\Filters\ScreenViewFilter::resolve()` untuk path saat ini — sama dengan `screen` pada event `screen_viewed` — dan dipakai JavaScript untuk `page_hidden`/`page_visible`. Atribut ini hanya ada pada path yang dikenal filter (lihat tabel di *Layar permainan*).
* Data untuk JavaScript selalu lewat `<script type="application/json">` (`app-config`, `challenge-data`, `password-policy`), tidak pernah disisipkan ke string JavaScript. Selain skrip ukuran panggung di `<head>` (tanpa data), view tidak memuat skrip inline atau atribut `on…`.

### `layouts/admin.php`

Dasbor SaaS netral (Dok 3 §8.1), hanya memuat `admin.css` (CSS permainan tidak ikut). `<body class="admin">`: `.skip-link`, lalu `.admin-shell` = `components/admin-sidebar` + `.admin-column` berisi topbar `header.admin-topbar` (56 px, lengket): tombol laci `.topbar-menu` (layar < 768 px), breadcrumb `nav.breadcrumb` dari `AdminMenu::crumbs()` (+ `$breadcrumb` halaman: `[label => href|null]`), pencarian cepat siswa `form.topbar-search` (GET `/admin/peserta?q=`; teks `Admin.quickSearch` untuk admin, `Admin.quickSearchTeacher` untuk guru), chip penelitian berjalan `.topbar-study`, dan menu akun `<details class="account-menu">` (nama, peran, **Ganti kata sandi**, **Keluar**); lalu `<main class="admin-main" id="admin-main">` selebar layar dengan padding 24 px. Di bawahnya `#modal-layer`, `tpl-modal`, `components/toast` (kanan atas; tanpa flash `error`, galat dirender tetap oleh `partials/flash`), `app-config`, dan modul `js/admin.js`. Section: `title`, `head`, `content`, `scripts`.

### `layouts/auth.php`

Halaman masuk staf bergaya admin (§1.6): `.skip-link` ke `<main id="auth-main">`, satu kartu `.auth-card` di tengah latar netral, merek `.auth-brand` di tengah, hanya `admin.css`. Dipakai `admin/login.php`.

Since 12 October 2026 the brand is the centred GELITA logo: `ui.logo-hero` (`.auth-logo`, up to 280 px wide), else the emblem `ui.logo` above the word GELITA, else the "G" mark above the word GELITA. No "Panel GELITA" caption (removed 12 October 2026). The title and lead line are centred too.

---

## Komponen dan partial

### Merender komponen

`component($name, $data)` (`app/Helpers/gelita_helper.php`) merender dengan renderer terpisah sehingga komponen hanya melihat data yang dioper. Nama tanpa garis miring dicari di `components/`; nama bergaris miring (`partials/…`, `admin/content/items`) dipakai apa adanya. Satu pengecualian: kunci konteks panel `filters` dan `filterOptions` diwariskan dari halaman bila tidak dioper ulang, agar `admin-filter-bar` di halaman ber-filter selalu memakai filter yang sama. `$this->include()` hanya dipakai untuk bagian yang memang berbagi data halaman tanpa data tambahan (HUD, sidebar, `partials/flash`, `partials/form-errors`, `_frame` tantangan, kepala pendaftaran). Perilaku ini dikunci `tests/unit/ViewHelperTest.php`.

### Komponen permainan

| Komponen | Isi dan aturan |
|---|---|
| `hud` | Pita atas panggung (0, 0, 1280 × 56 dp), tiga kolom. Kiri `.hud-left`: `lantern` + merek kecil `.hud-brand.is-small`. Tengah `.hud-center`: section `hudTitle`, atau nama wilayah dari `$level`. Kanan `.hud-right`: `lang-switch`, tombol suara `#btn-sound` (`aria-pressed`), tombol layar penuh `#btn-fullscreen` (`hidden` sampai `core/stage.js` memastikan Fullscreen API ada), tombol beranda ke `/peta`, chip peserta `.user-chip` (avatar `components/avatar`: gambar `avatar.N` pilihan siswa atau huruf awal, nama, `@username`) ke `/profil`, dan tombol keluar `/keluar`. Lentera, beranda, chip, dan keluar hanya dirender bila controller mengirim data HUD (`BaseGameController::hudData()`: `participant` dari `toSafeArray()`, `progress`, `lantern`); sebelum itu kiri berisi merek teks `.hud-brand` ke `/`, kecuali halaman awal yang mengirim `hideBrand`. Section `hudBack` (URL) mengganti isi kiri dengan tombol **Kembali** `.hud-back` dan menaruh logo `ui.logo` (cadangan merek teks) di tengah `.hud-logo`; dipakai `/mulai`, `/masuk`, dan `/daftar/1…3`. HUD tidak berlatar dan tidak bergaris bawah, dan kontrolnya juga tanpa bingkai: ikon (suara, layar penuh, beranda, keluar, nyala lentera) tanpa latar dan garis, hanya diberi bayangan. Teks diberi latar gelap `--hud-backing` tanpa garis agar tetap terbaca di atas gambar terang: judul tengah, merek, pemilih bahasa, tombol Kembali, nama dan `@username` di chip peserta, serta hitungan serpihan dan bilahnya (`.lantern-meter`). Tautan yang meninggalkan layar diberi kelas `hud-leave` dan disembunyikan selama tantangan (`body.is-challenge`). |
| `hud-menu` | Redesigned HUD (result screen redesign, 18 October 2026), opted in with section `hudStyle` = `menu`. Left `.hud-identity`: logo `ui.logo-hero` (else `ui.logo`, else the text brand) and the region name. Right `.hud-controls`: three `.hud-ctl` controls 88 × 96 dp on the round skin `ui.result-hud-control` — language popover `<details class="hud-pop hud-lang" data-popover>` (flags `ui.lang.*` or text codes, the `form.lang-switch` POST to `/bahasa`), sound `#btn-sound`, and menu `<details class="hud-pop hud-menu" data-popover>` with a scrim: profile (avatar, name, `@username`) to `/profil`, Peta Kedu, fullscreen `#btn-fullscreen`, Keluar. No lantern. `data-in-game` marks a HUD with session data (both HUDs; the heartbeat keys on it). `game/hud.js` keeps one popover open, closes it on Escape, outside click, or its `[data-popover-close]` button, and returns focus to its `summary`. |
| `lantern` | `<details class="lantern">` berisi nyala `.flame-{0..3}` (jumlah wilayah tuntas), "Serpihan Cahaya" `{shards}/{shards_total}`, bar isi, dan rincian `.lantern-detail`: titik `.shard.is-completed/is-open/is-locked` per tantangan, dikelompokkan per wilayah, dengan legenda. `shards_total` selalu dari jumlah tantangan aktif (`GameProgress::progressSummary()`), tidak pernah angka tetap. |
| `lang-switch` | Form POST `/bahasa` dengan `redirect_to` = path saat ini (divalidasi `safe_internal_url()`); satu tombol per `Config\Gelita::$locales`, `aria-pressed` pada bahasa aktif. |
| `rotate-gate` | Penghalang "putar perangkat" (`role="alertdialog"`). Tampil murni lewat CSS (lihat *Layar putar dan aplikasi terpasang*). |
| `nav-bar` | Navigasi bawah dari array `$nav` (`label`, `href`, `style` = `primary`/`ghost`/`quiet`, `arrow`, `icon`, `attrs` untuk `aria-*`/`data-*`). Tombol berpanah kiri di `.nav-bar-start`, sisanya di `.nav-bar-end`. Layar tantangan tidak memakai nav-bar. |
| `modal` | Kerangka dialog (`.modal-card.modal-{type}`, `.modal-title`, `.modal-text`, `.modal-actions`) di dalam `<template id="tpl-modal">`; ikon per jenis (`correct`, `wrong`, `hint`, `confirm`, `info`, `lock`) disediakan sebagai `<template data-modal-icon>` agar JavaScript tidak merakit SVG dari string. `core/modal.js` yang membukanya. |
| `toast` | `#toast-layer`. Game (`withErrors`): flash `message` as `.toast.is-ok.is-flash`, flash `error` as `.toast.is-warn.is-flash` (every game `error` flash is a lock message), each with a round `.toast-badge`, plus `<template data-toast-icon="ok|warn|bad|info">` badges for JS toasts. Admin: only `message`, in the admin look. Flash toasts fade by CSS after about 3.5 s without JavaScript; see *Game-style toasts* below. |
| `character` | `<figure class="character character-{jaka|mbah_kedu}">` dengan frame `char.{jaka|kedu}.{pose}.1` (cadangan pose `idle`); tanpa gambar tampil monogram `.character-fallback` ("J" / "MK"). Opsi `pose`, `showName` (papan nama), `class`. |
| `narration` | Balon `<blockquote class="narration">` dengan `<cite>` nama tokoh; `tail = down` → `.narration-down`. |
| `audio-player` | Tombol putar/jeda/ulangi + bar, transkrip di `<details class="audio-transcript">`, dan `<audio controls>` di dalam `<noscript>`. Bila audio belum disetujui (`audioSrc` kosong) hanya kotak transkrip `.is-text-only` yang dirender. Transkrip wajib ada. |
| `narrator-controls` | Kendali pemutar narasi ◀ · ⏸/▶ · ↻ · ▶ · sakelar **Otomatis** (`data-narrator="…"`), tersembunyi sampai `game/narrator.js` aktif. |
| `narrator-tap` | Kartu "Ketuk untuk mulai" (`data-narrator-tap`, `hidden`). Varian berjudul: `is-chapter` (kartu bab dialog wilayah, dengan jejak kaki) dan `is-done` (wilayah tuntas). Slot `[data-narrator-welcome]` menampung kartu sambutan setelah pendaftaran. |
| `curtain` | Tirai pemuatan layar penuh, lihat *Tirai*. |
| `star-rating` | Bintang 0..3 (atau 0..`max`) lewat `stars_html()`; `size = lg` → `.stars-lg`. |
| `stat-tile` | Satu angka + label (+ ikon, keterangan, `id` untuk diperbarui JavaScript). Nilai berupa kata memakai `.stat-value.is-text`. |
| `password-field` | Kolom sandi baru + tombol lihat `.pw-toggle` + peringatan Caps Lock `.pw-caps` + meter `.pw-meter` + `.pw-level` + daftar 5 syarat `.pw-rules` (`data-rule` = `length`, `upper`, `lower`, `digit`, `symbol`) + ulangi sandi + `.pw-match`. Aturan dikirim sebagai `<script type="application/json" id="password-policy">` dari `PasswordPolicy::toClient()`; teks tingkat ada di atribut `data-level-*`. Kolom sandi tidak pernah diisi ulang dan sengaja tanpa `minlength`, agar sandi lemah sampai ke server dan tercatat sebagai `pw_weak_submit_count`. |

#### Game-style toasts (display plan Phase E)

Applies to `body.game`; the admin panel keeps its own toast look (`admin.css`).

- **Look** (`components.css`). Same stage place (x 856, y 72, width 400 dp). A navy card with a glowing gold frame (`.is-bad` has a red frame), a 56 dp round icon badge `.toast-badge` (✓ ok, ! warn, ✕ bad, i info), and text at body size (20 dp, bold). Enters with a short pop (`toast-pop`: 0.6 → 1.06 → 1); leaves by fading upward (`.is-leaving`, `toast-leave`). With reduced motion it only fades in and out.
- **Layer** (`stage.css`). `pointer-events: none` except on the card; z 60, above the modal veil (z 50), so errors are seen even while a modal is open.
- **Without JavaScript** flash toasts fade after about 3.5 s (`toast-out`). With JavaScript they are removed from the DOM and join the queue of `core/toast.js` (see `06_JAVASCRIPT`).
- **Flash `error` in the game** shows as `warn`, like the lock toast from JavaScript.

### Komponen panel admin

| Komponen | Isi dan aturan |
|---|---|
| `admin-sidebar` | Menu dari `App\Libraries\AdminMenu` (lihat *Panel admin*). Menu khusus admin tidak dirender untuk guru. Menu aktif = awalan path terpanjang. 248 px; di bawah 1280 px kolom ikon 72 px (label tetap terbaca pembaca layar, juga sebagai `title`); di bawah 768 px laci yang dibuka tautan `#admin-menu` (CSS `:target`). |
| `data-table` | **Tabel data server-side (§8.2).** `table` berbentuk `AdminTable::page()` / `RawDataService::page()` (`rows`, `total`, `page`, `per_page`, `sort`, `columns: {key, label, type, sortable?}`). Bilah alat (judul, jumlah baris, `tools`, **Unduh CSV** = URL yang sama + `format=csv`); `.table-wrap` gulir dua arah dengan header lengket; kolom pertama lengket (`stickyFirst`, bawaan ya); judul kolom = tautan urut (`sort=key` / `-key`, `aria-sort`); sel lewat `AdminTable::cell()` (angka rata kanan tabular, kode mono, status = lencana, durasi `m:ss` dengan milidetik di `title`, kosong "—", `clip` = teks dipotong yang dapat dibuka); pagination "x–y dari n baris", pilihan 25/50/100, nomor halaman. Opsi: `render` (closure per kolom), `expand` + `expandLabel` (baris rincian yang dibuka tombol, mis. riwayat percobaan), `paginate` (false = daftar pendek), `csv`, `anchor`, `emptyMessage`. Semua tautan biasa: berfungsi tanpa JavaScript. |
| `admin-filter-bar` | Form `method="get"` `.filter-bar`: kontrol 40 px menurut `only` (urutan render): `q`, `participant_code`, `study_id`, `level_id`, `node_id`, `variant_group`, `school_id` (guru: `.filter-locked`), `district_code`, `province_code`, `class_level`, `gender`, `age`, `progress`, `locale`, `date_from`+`date_to`. `labels` mengganti label, `choices` menambah pilihan, `bare` = hanya kontrol (form POST ekspor). Tombol **Terapkan** dan **Atur ulang**, lalu baris **chip filter aktif** (`.filter-chip`, tiap chip tautan yang melepas satu filter; guru melihat chip sekolah terkunci). `sort` dan `per_page` dibawa sebagai field tersembunyi. |
| `stat-tile` | Kartu KPI: label 13 px, angka 28 px tabular, keterangan; panel hanya memuat hitungan. |
| `admin-table` | Tabel kecil tanpa pagination (ukuran wajib, temuan pindai, riwayat audit lama): `columns` + `rows` dengan `format`/`render`; `admin/tables.js` menambah urut di client (≤ 500 baris) dan kotak saring (≥ 10 baris). |
| `admin-pagination` | Navigasi `paginate()` CodeIgniter (halaman yang belum memakai `data-table`, mis. riwayat aktivitas). |
| `media-field` | Pemilih media editor konten: pratinjau, kotak "kode berkas" (`asset_key`, dengan `media-datalist`), input berkas baru, saran ukuran. Server membacanya lewat `MediaStore::resolveField()`; form induk wajib `multipart/form-data`. |
| `media-datalist` | `<datalist id="media-keys-{jenis}">` berisi seluruh `asset_key` (slot yang belum berberkas bertanda). |
| `audio-select` | Pemilih rekaman `audio_assets` dengan bahasa dan status persetujuan; rekaman bahasa yang diminta didahulukan. |

Gaya seragam lain (§8.2) ada di `admin.css`: kepala halaman (`partials/admin-head`: judul 24 px, deskripsi 14 px, aksi di kanan), tab garis bawah `.tabs` (antarhalaman, atau di satu halaman lewat `data-tabs` + `data-tab-panel`, `admin/tabs.js`), navigasi bagian `.sub-nav` (segmen), lencana `.badge.is-{status}`, tombol `.btn` (utama `.btn-primary`, sekunder bawaan, hantu `.btn-quiet`, bahaya `.btn-danger`/`.btn-danger-soft`), formulir, modal konfirmasi (`form[data-confirm]`, `data-confirm-text`, `data-confirm-ok`, `data-confirm-style` → `core/modal.js`), toast kanan atas, dan keadaan kosong `.empty-state`.

### Partial

| Partial | Dipakai di | Isi |
|---|---|---|
| `cine-slides` | intro, penutup | `ol.slides.cine-slides`; tiap `li.slide.cine-slide` (`id = {prefix}{n}`, `data-character`, `data-pose`, `data-effect`) berisi latar `.cine-bg` (`dialogues.background_media_id`), tokoh di `.cine-stage` (narator tanpa gambar, `.is-narrator`), kotak `.cine-caption` (`data-narrator-advance`: "Bagian n dari N", `.cine-speaker`, `.cine-title`, `.slide-text`, `audio-player`, `slide-nav`). |
| `dialogue-scene` | dialog wilayah, wilayah tuntas | `section.story.dialogue.narrator` (`data-screen`, `data-context`, `data-prefix="line-"`, `data-mode="tap"`), `.dialogue-stage` dengan Jaka (`.stage-left`) dan Mbah Kedu (`.stage-right`), baris `li.dialogue-line` (`data-pose-src` dari `character_frame_src()`), kotak `.dialogue-box.panel-parchment`, `narrator-controls`, `narrator-tap`. Tanpa baris dialog: `Game.dialogueEmpty` dan tombol lanjut. |
| `region-intro` | Peta Kedu | Overlay `section.kenal` (`role="dialog"`, `id="kenal-{code}"`, `data-mode="external"`, `data-keyboard="0"`, `data-hash="0"`): latar wilayah, judul `Game.kenalTitle`, tombol tutup `.kenal-close`, slide `kenal-{code}-{n}`. Slide akhir: **Masuk ke {wilayah}** (tirai wilayah) bila wilayah terbuka, selain itu **Tutup** ke `#region-{code}`. |
| `slide-nav` | semua slide | Kembali · titik `.slide-dot` · Lanjut; slide terakhir menjadi tombol akhir `data-slide-final` (opsional `finalAttrs`, `finalIcon`, tombol sekunder `extra`). |
| `footsteps` | tirai wilayah, kartu bab | Jejak `svg.footstep.is-left/is-right` dengan `--i`; dekoratif. |
| `challenge-art` | tirai tantangan | `.cc-art.cc-{engine}`: keping menyatu (puzzle), huruf jatuh mengisi celah (rumpang), kartu berputar ✓/✗ (boleh), sorot lentera menyapu siluet (cari). |
| `library-lock` | rak Pustaka, Pustaka terkunci | Bar progres `Game.nodesProgress` + kalimat `Game.libraryLockedText`; peta wilayah membaca kalimat yang sama dari atribut tombol Pustaka. |
| `media-credit` | buku Pustaka | `<details class="media-credit">` ikon ⓘ di pojok bingkai, panel kredit + "Lihat sumber ↗" (`target=_blank rel="noopener noreferrer"`). Saudara `.book-zoom`, bukan anaknya. |
| `form-errors` | formulir permainan | Ringkasan galat per field (`.alert.alert-error`). |
| `flash` | panel admin | Flash `error` dan galat field (`.alert-error`), flash `notice` (`.alert-info`). |
| `admin-head` | panel admin | `header.page-head`: eyebrow, `h1.page-title`, `.page-lead`, `.page-actions` (HTML tombol yang sudah di-escape pemanggil). |
| `content-nav` | konten satu wilayah | `ul.sub-nav`: Semua konten · Wilayah & tantangan · Teks bacaan · Pustaka · Cerita & dialog. |

---

## Helper tampilan

Dimuat global lewat `Config\Autoload::$helpers`.

| Helper | Berkas | Fungsi |
|---|---|---|
| `icon($name, $class)` | `ui_helper.php` | SVG garis 24×24 `currentColor`, `aria-hidden`; nama tak dikenal → `info`. Tanpa font ikon atau CDN. |
| `curtain_attrs($kind, $text, $preload, $bg)` | `ui_helper.php` | Atribut tautan bertirai: `data-curtain`, `data-curtain-text` (JSON slot → teks), `data-curtain-preload` (JSON URL), `data-curtain-bg` (gambar tirai). `region_curtain_attrs($curtain)` membentuknya dari `GameProgress::regionCurtain()`. |
| `region_art_src($kind, $code)`, `region_loading_bg($code, $bg)`, `art_or_icon($src, $icon)` | `content_helper.php` | Gambar kota dari panel (display plan Phase C): pin/lentera kota → umum → null; latar tirai kota → latar wilayah → null; `<img>` atau ikon SVG bawaan. |
| `lang_or($key, $fallback, $args)` | `ui_helper.php` | `lang()` dengan cadangan bila kunci belum ada. |
| `engine_label($engine, $variant)` | `ui_helper.php` | Nama jenis tantangan: `Game.variant_{kode}` bila ada, selain itu `Game.engine_{engine}`. |
| `admin_label($group, $code)` | `ui_helper.php` | Label Indonesia untuk kode database dari kelompok di `Admin.php` (`status`, `completion`, `locale`, `gender`, `pose`, `effect`, `device`, `interaction`, `review`, `approval`, `exportSheet`, `event`, `audit`, …); kosong → "—". |
| `class_label($code, $locale)` | `ui_helper.php` | `5` → `Game.classSd` ("Kelas 5 SD/MI"). |
| `fmt_num`, `fmt_pct`, `fmt_date` | `ui_helper.php` | Angka, persen, dan tanggal-waktu gaya Indonesia/Inggris; kosong → "—". |
| `ms_to_human($ms)` | `gelita_helper.php` | Durasi untuk layar: `125000` → "2:05", `3725000` → "1:02:05". |
| `asset_url_versioned($path)` | `gelita_helper.php` | URL `assets/{path}?v={Config\Gelita::$assetVersion}` untuk melewati cache. |
| `js_config($locale)` | `gelita_helper.php` | Isi `#app-config`: `locale`, `csrfName`, `csrfHash`, `apiBase`, `baseUrl`, `sessionTag`, `maxTries`, `stage` (1280 × 720), dan `text` = semua kunci `Js.*` dalam bahasa aktif. Tanpa identitas siswa. |
| `component($name, $data)` | `gelita_helper.php` | Lihat *Merender komponen*. |
| `tr($row, $field, $locale)` | `content_helper.php` | Teks dwibahasa dari baris array (`{field}_id`/`{field}_en`) dengan cadangan Indonesia. |
| `rich_text($text)` | `content_helper.php` | Format ringan teks Pustaka: paragraf, `## ` → `h3`, `- ` → daftar, `> ` → `aside.book-fact`, `Sumber:` → `p.book-source`, `**tebal**`, `*miring*`. Teks di-escape lebih dulu. |
| `media_src($id)`, `media_exists($id)`, `media_first(...$ids)` | `content_helper.php` | URL media aktif; `media_src()` jatuh ke `assets/ui/placeholder.svg`, `media_first()` ke string kosong (layout lalu memakai gradien). |
| `media_key_src($key)`, `media_key_src_locale($key, $locale)` | `content_helper.php` | URL menurut `asset_key` resmi, `null` bila belum diunggah. Varian bahasa: `{kunci}.{locale}` dicoba lebih dulu (mis. `ui.btn-start.en`). |
| `character_frame_src($character, $pose)` | `content_helper.php` | Frame `char.{jaka|kedu}.{pose}.1`, cadangan `idle`; `null` untuk narator atau bila belum ada gambar. |
| `audio_src($id)` | `content_helper.php` | URL audio hanya bila `approval_status = approved` dan berkasnya aktif. |
| `media_catalog()`, `audio_catalog()` | `content_helper.php` | Daftar aset untuk pemilih di panel admin. |
| `stars_html($stars, $max)` | `content_helper.php` | Bintang penuh/kosong + teks alternatif. |
| `first_pass_text($attempt, $locale, $engine)` | `content_helper.php` | "Berhasil pada Percobaan Pertama": "{0} dari {1} soal" (`Game.firstTryCount`); Susun Gambar satu gambar: `Game.firstTryYes`/`Game.firstTryNo`. |

## Layar permainan

### Ringkasan layar

`data-screen` adalah atribut pada `<section>` utama halaman (dipakai CSS dan JavaScript). `data-screen-code` dirender layout pada `<body>` dari `ScreenViewFilter::resolve()` dan sama dengan kode `screen_viewed`. Halaman sebelum login (`/`, `/mulai`, `/daftar/*`, `/masuk`) tidak punya keduanya.

| Layar | URL | Controller | View | `data-screen` | `data-screen-code` |
|---|---|---|---|---|---|
| Halaman awal | `/` | `HomeController::index` | `game/welcome` | — | — |
| Pilihan akun | `/mulai` | `HomeController::start` | `game/start` | — | — |
| Pendaftaran | `/daftar/1..3` | `RegisterController::step` | `game/register/step-{n}` | — (`data-step`) | — |
| Masuk | `/masuk` | `LoginController::form` | `game/login` | — | — |
| Ganti sandi | `/ganti-sandi` | `LoginController::changePasswordForm` | `game/change-password` | — | `change_password` |
| Gerbang | `/gerbang` | `GateController::index` | `game/start-choice` | — | `gate` |
| Cerita pembuka | `/intro` | `GateController::intro` | `game/intro` | `intro` | `intro` |
| Peta Kedu | `/peta` | `MapController::kedu` | `game/map-kedu` | `map-kedu` | `map` |
| Dialog wilayah | `/dialog/{code}` | `DialogueController::show` | `game/dialogue` | `dialogue` | `dialogue` |
| Peta wilayah | `/wilayah/{code}` | `MapController::level` | `game/map-level` | `map-level` | `region` |
| Kartu misi | `/misi/{code}/{seq}` | `ChallengeController::brief` | `game/mission-brief` | `mission-brief` | `brief` |
| Tantangan | `/tantangan/{code}/{seq}` | `ChallengeController::play` | `game/challenge/{engine}` | `challenge` | `challenge` |
| Selesai | `/selesai/{attemptId}` | `ChallengeController::finished` | `game/challenge-finished` | `finished` | `finished` |
| Hasil tantangan | `/hasil/{code}/{seq}` | `ChallengeController::result` | `game/challenge-result` | `result` | `result` |
| Wilayah tuntas | `/tuntas/{code}` | `DialogueController::done` | `game/region-done` | `region-done` | `region_done` |
| Penutup | `/penutup` | `DialogueController::ending` | `game/ending` | `ending` | `ending` |
| Pustaka Kedu (rak) | `/pustaka` | `LibraryController::index` | `game/library-index` | `library-index` | `library_index` |
| Pustaka {wilayah} | `/pustaka/{code}` | `LibraryController::show` | `game/library` | `library` | `library` |
| Pustaka terkunci | `/pustaka/{code}` | `LibraryController::show` | `game/library-locked` | `library-locked` | `library` (event `screen_viewed` mencatat `library_locked`) |
| Profil | `/profil` | `ProfileController::index` | `game/profile` | `profile` | `profile` |
| Balai Refleksi | `/refleksi` | `ReflectionController::index` | `game/reflection` | `reflection` | `reflection` |

Semua layar sesudah login menerima data HUD (`hudData()`), kecuali cerita pembuka dan ganti sandi, yang HUD-nya hanya berisi merek dan pemilih bahasa.

### Halaman awal — `game/welcome`

* Latar `bg.welcome`, `body.is-welcome`, HUD tanpa merek (`hideBrand`).
* Logo: slot `ui.logo-hero` (1600 × 600), cadangan `ui.logo`, lalu judul teks `Game.appName` + `Game.tagline`. Bila logo berupa gambar, tagline tetap ada sebagai teks `.visually-hidden`.
* **Satu** tombol Mulai `.btn-start` → `/mulai`, sama untuk siswa yang sudah maupun belum login. Gambarnya slot `ui.btn-start` (720 × 240, bertulisan, jadi memakai `media_key_src_locale()`: `ui.btn-start.en` untuk bahasa Inggris); tanpa gambar menjadi `btn btn-primary btn-xl` bertuliskan `Game.start`.
* Tawaran memasang aplikasi `.welcome-install` (`data-install`, tersembunyi sampai `game/install.js` memutuskan): tombol `Game.installApp` atau petunjuk iPhone/iPad `Game.installIos`.
* Staf masuk lewat `/admin/login`; halaman awal tidak menautkannya.

### Pilihan akun — `game/start`

Jaka (pose `happy`, gambar terpisah dari latar) di kiri dan satu panel gelap berornamen emas `.start-panel.ornate-panel` (`partials/ornate-frame`: sudut, kompas di tepi atas, garis pemisah) berisi judul dan dua kartu baris `.start-card`: **Saya baru** (`Game.startNew`, ikon `user-plus`, kartu utama `.is-primary`) → `/daftar` (dialihkan ke langkah pertama yang belum lengkap) dan **Saya sudah punya akun** (`Game.startReturning`, ikon `login`) → `/masuk`. Siswa yang sudah login dan bersesi dialihkan ke `/gerbang`. Kembali ke `/` ada di HUD kiri atas (`hudBack`); tidak ada nav bar.

### Pendaftaran tiga langkah — `game/register/step-{1,2,3}`

Kembali ada di HUD kiri atas (`hudBack`: langkah 1 → `/mulai`, 2 → `/daftar/1`, 3 → `/daftar/2`) dengan logo GELITA di tengah HUD; bilah tombol panel hanya berisi Lanjut/Daftar di kanan. Tata letak sama dengan `/mulai` dan `/masuk` (10 Okt 2026): Jaka di kiri (`.start-jaka`; pose `happy` di langkah 1–2, `bow` di langkah 3) dan panel gelap berornamen `.register-panel.ornate-panel` di `section.register[data-step]`, dengan kepala `register/_head` (eyebrow `Game.regStepOf`, judul `Game.regStep{n}`, stepper `register/_stepper`, lalu `partials/form-errors`). Stepper `.reg-stepper`: langkah aktif `.is-active` (`aria-current="step"`), langkah yang sudah tersimpan `.is-done` bertanda ✓ dan berupa tautan GET kembali ke langkahnya. Isian langkah 1–2 disimpan di session PHP (`reg_wizard`), bukan di database; membuka langkah yang lebih jauh dari langkah pertama yang belum lengkap dialihkan ke langkah itu. Ketiga formulir POST ke `/daftar/{n}` dan berjalan tanpa JavaScript; `game/register.js` menambah perilaku menurut `data-step`. Langkah 1 diakhiri pemilih avatar opsional (`components/avatar-picker` mode `radio`, nama `avatar_no`, bawaan huruf awal) bila ada avatar yang diunggah; pilihan disimpan bersama isian langkah 1 di `reg_wizard` dan ditulis saat akun dibuat.

| Langkah | Judul | Isian |
|---|---|---|
| 1 `/daftar/1` | Tentang kamu | `display_name` (nama lengkap, 2–150), `age` (number, batas `Config\Gelita::$registrationAge`, satuan `Game.ageUnit`), kelas tetap `.class-fixed` bergembok "Kelas 5 SD/MI" (tidak diisi siswa), `gender` radio `.choice-pill` dari `Config\Gelita::$genders` (Laki-laki / Perempuan). Kembali → `/mulai`. |
| 2 `/daftar/2` | Sekolah dan daerah | `country_code` (Indonesia `ID` / negara lain `XX`), `province_code` + `district_code` (`.region-id`; kabupaten dikelompokkan `<optgroup data-province>`), `country_other` (`.region-other`), NPSN `school_npsn` (`.school-npsn`, 8 angka, hanya untuk provinsi berdirektori bertanda `data-directory`), kartu `.school-card` "Benar ini sekolahmu?" (nama resmi + jenjang dan wilayah, peringatan `.school-card-warn` bila jenjangnya tidak diizinkan), centang `school_manual` "Sekolahku tidak ada di daftar" (`.school-manual`, muncul setelah NPSN tidak ditemukan), dan `school_name` (`.school-name`) untuk provinsi lain, negara lain, atau setelah `school_manual`. Blok provinsi/NPSN/nama bertukar lewat CSS `:has()` tanpa JavaScript; `register.js` menyaring kabupaten dan memeriksa NPSN lewat `GET /api/schools/lookup`, sedangkan server tetap memeriksa NPSN saat Lanjut. |
| 3 `/daftar/3` | Akun rahasiamu | Kotak ringkas `.why-strong` (`Game.whyStrongTitle`, `Auth.whyStrong`, tips `Auth.tip1`–`tip4`), `username` (`data-username-check`, status ketersediaan `.username-status` lewat `GET /api/auth/username-available`, `Auth.usernameHelp`), `components/password-field`, centang persetujuan siswa `assent` (`Game.assentLabel`, wajib), `locale` dan `device_json` tersembunyi (diisi `core/device.js` saat dikirim). Tombol **Daftar** (`Game.registerSubmit`) tetap aktif walau sandi belum kuat: penolakan server adalah umpan balik belajar dan dicatat sebagai `pw_weak_submit_count`. |

Setelah berhasil, siswa dibawa ke `/intro` dengan flash `welcome` (`Game.registerWelcome`, menyebut nama pengguna; kata sandi dan kode peserta tidak pernah ditampilkan).

### Masuk — `game/login`

Latar halaman akun (`/masuk`, `/daftar/1–3`, `/ganti-sandi`): slot `bg.auth`, cadangan `bg.welcome`.

Tata letak sama dengan `/mulai`: Jaka pose `bow` di kiri dan panel gelap berornamen `.login-panel.ornate-panel`, isinya bergulir di `.ornate-body.stage-scroll` bila panjang (mis. dengan pesan galat); Kembali ke `/mulai` di HUD kiri atas (`hudBack`), logo di tengah HUD, tanpa nav bar. Isi panel: judul `Game.loginTitle`, `Game.loginLead`, nama pengguna, kata sandi (tombol lihat `.pw-toggle`, peringatan Caps Lock `.pw-caps`), `device_json` tersembunyi, tombol **Masuk**. Galat datang dari server sebagai satu pesan netral `Auth.loginFailed` atau `Auth.locked` dengan sisa menit. Kaki panel: "Belum punya akun? Daftar" dan `Game.forgotPassword` (minta guru mengatur ulang). Setelah berhasil: halaman yang sempat diminta (`redirect_after_login`), selain itu `/mulai`; akun bersandi sementara ke `/ganti-sandi`.

### Ganti sandi — `game/change-password`

Pemberitahuan `Auth.mustChange` bila sandi berasal dari reset guru (`$mustChange`; nav-bar tidak dirender), kolom sandi saat ini, lalu `components/password-field` dengan label `Auth.newPassword` dan `data-username` untuk memeriksa "memuat nama pengguna". Setelah berhasil server mengarahkan ke `/gerbang` (atau `/intro` bila cerita pembuka belum ditonton) dengan toast `Auth.passwordChanged`.

### Gerbang — `game/start-choice`

Pemain yang belum pernah menonton cerita pembuka sampai selesai (`participants.intro_seen_at` kosong) dialihkan ke `/intro`. Selain itu: tata letak sama dengan `/mulai` (Jaka + panel berornamen), sapaan `Game.welcomeBack` (nama lengkap atau nama pengguna) dan dua kartu baris (kartu utama: ke peta): **Lihat cerita pembuka** → `/intro`, **Langsung ke peta** → `/gerbang/peta`, yang men-set flash `curtain=map` lalu membuka `/peta` (flash hanya dapat diset server, jadi kartu tidak menautkan `/peta` langsung).

### Cerita pembuka — `game/intro`

Layar sinematik (`body.is-cinematic`, `section.intro.cine.narrator`, `data-context="intro"`, `data-prefix="slide-"`, `data-mode="tap"`): sembilan slide `dialogues` konteks `intro` lewat `partials/cine-slides`, latar per slide dengan Ken Burns pelan (cadangan latar layout `bg.intro`, lalu `bg.welcome`), tokoh sesuai pose, kotak teks bergaya subtitle, efek layar, audio narasi bahasa aktif, `narrator-controls`, dan `narrator-tap`.

* Slide terakhir **Mulai petualangan** → `/intro/selesai` (mengisi `intro_seen_at` sekali, mencatat `intro_completed`, lalu `/peta` dengan tirai peta).
* **Pemain baru wajib menonton:** nav **Lewati** (→ `/gerbang/peta`) hanya dirender bila `canSkip` (sudah pernah menonton).
* Setelah pendaftaran, kartu sambutan `.welcome-card` (`Game.welcomeCardTitle`, pesan flash, `Game.welcomeCardTip`) tampil di atas; dengan JavaScript kartu itu dipindah ke kartu ketuk.
* Tanpa slide: `Game.introEmpty` + tombol ke `/intro/selesai`.

### Peta Kedu — `game/map-kedu`

`section.map-screen.map-kedu` (`data-unlock-mode`). Peserta yang belum menonton cerita pembuka dialihkan ke `/intro`.

* **Panduan peta** `.map-guide.map-story.narrator`: tiga slide `map_intro` (Jaka) dengan tokoh sesuai pose, balon `.map-story-line`, audio, navigasi kecil `.map-story-nav`, efek layar, dan `narrator-controls` (`data-keyboard="0"`), tampil sebagai bilah `.map-balloon` selebar peta di bagian bawah peta: Jaka (pose slide, 124 × 168) berdiri di ujung kiri dan menjulang ke sudut kiri bawah peta yang kosong, lalu balon, lalu kontrol dalam satu baris. Datang dengan flash `curtain=map` → tirai "Membuka Peta Kedu" lalu narasi diputar setelah ketukannya (`data-mode="external"`); kunjungan lain → teks utuh dengan tombol ▶ (`data-mode="manual"`). Tanpa slide: balon `Game.mapLead`.
* **Kanvas peta** `.map-canvas` (`--map-ratio: 1400 / 900`, gambar `map.kedu`; tanpa gambar `.is-drawn`), garis rute `svg.map-route`, dan pin `li.map-point.is-{status}` pada `levels.map_x`/`map_y` (persen). Ikon pin: lentera (terbuka/sedang dijelajahi), bintang (tuntas), gembok (terkunci). Bila ada gambar pin dari panel (`region_art_src('pin', $code)`: `map.pin.{code}`, lalu `ui.pin`), `.pin-icon.has-art` menampilkan gambar itu tanpa piringan emas; status ditambah otomatis: terkunci abu-abu + tanda gembok `.pin-mark`, tuntas tanda bintang, terbuka berdenyut; label berisi nama, `Game.levelStatus_{status}`, dan `{selesai}/{total}`. Tautan pin menuju `entry` dari `GameProgress::regionEntryPath()`: wilayah yang baru terbuka → `/dialog/{code}`, selain itu → `/wilayah/{code}`. Pin terkunci tetap tautan ber-`aria-disabled="true"` + `data-locked-message` (`Game.levelLocked`); server menolaknya dengan toast yang sama.
* **Kartu wilayah** `ol.region-list.map-side.stage-scroll` › `li.region-card#region-{code}` di panel kanan peta (terbaca pembaca layar dan saat gambar peta belum ada): urutan + tingkat kesulitan, lencana status, progres + bintang, tombol Mulai/Lanjut, atau keterangan terkunci.
* **Kenali wilayah**: tombol lentera `.kenal-btn` pada pin (area sentuh 48 dp mulai dari tepi kanan pin, jadi ketukan di pin tidak membuka Kenali; gambar `map.lantern.{code}` → `ui.lantern` bila ada) dan `.kenal-card-btn` pada kartu (juga untuk wilayah terkunci) membuka overlay `partials/region-intro` (4 slide `region_intro` Mbah Kedu). Lencana **Belum didengar** (`.kenal-dot` di pin, `.kenal-badge` di kartu, `data-kenal-badge`) tampil sampai narasi wilayah itu pernah didengar sampai slide terakhir oleh peserta ini (dihitung server dari `dialogue_advanced`, `GameEventLogModel::heardRegionIntros()`).
* **Tirai wilayah**: tautan ke wilayah yang terbuka membawa `region_curtain_attrs($level['curtain'])`; template `<template id="tpl-curtain-region">` ada di section `overlay`. Gambar tirai (`data-curtain-bg`): `bg.loading.{code}`, lalu latar wilayah (`region_loading_bg()`); dipramuat pertama dan dihangatkan di peta (`data-preload` pada pin bila berbeda dari latar wilayah). Tanpa gambar, tirai tetap gradien. Lentera HUD dan tirai "Membuka Peta Kedu" memakai `ui.lantern` bila ada (tahap nyala menjadi cahaya `drop-shadow`).
* Nav: **Pustaka Kedu** → `/pustaka` (kiri). Bila semua tantangan tuntas: **Tonton penutup** → `/penutup` dan **Balai Refleksi** → `/refleksi`; selain itu **Profil**.

### Dialog wilayah — `game/dialogue`

Dialog pembuka wilayah (`level_open`, 15–16 baris) lewat `partials/dialogue-scene` (`data-screen="dialogue"`), latar wilayah. Kartu ketuk varian `chapter`: `Game.chapterOf` ("Bab {n} · {wilayah}"), tagline (judul slide pertama `region_intro`, sama dengan tirai wilayah), tombol `Game.chapterTap`, dan jejak kaki. Panggung dua tokoh: yang berbicara maju dan terang (`.is-speaking`), yang mendengar mundur dan meredup (`.is-listening`); tanpa JavaScript lewat `:target`/`:has()`. Gambar tokoh yang berbicara berganti menurut `data-pose-src` baris itu. Slide akhir **Masuk ke {wilayah}** (`Game.enterRegionName`) → `/wilayah/{code}`. Nav: Peta Kedu dan **Lewati** → `/wilayah/{code}`.

Wilayah yang baru terbuka wajib lewat dialog ini: `/wilayah/{code}`, `/misi/…`, dan `/tantangan/…` dialihkan ke `/dialog/{code}` (`BaseGameController::dialogueGate()`) sampai dialog tampil pada sesi login itu.

### Peta wilayah — `game/map-level`

`section.map-screen.map-level` (`data-level`, `data-level-id`). Kepala: urutan + tingkat kesulitan, nama wilayah, fokus literasi (`levels.focus_*`). Kanvas `map.region.{code}` (`levels.map_media_id`) dengan empat pin `li.map-point.node-point.is-{completed|open|locked}` pada `challenge_nodes.map_x`/`map_y`: nomor (terbuka), ✓ (selesai, dengan bintang), atau gembok; label "{n}. {jenis tantangan}". Pin selesai → `/hasil/{code}/{seq}`, pin terbuka → `/misi/{code}/{seq}`, pin terkunci ber-`aria-disabled` + `Game.nodeLocked`. Di bawahnya bar progres `Game.nodesDoneOf` dan daftar `ol.node-list` › `.node-card` (eyebrow `Game.challengeOf` + jenis, judul, bintang, tombol Lihat hasil / Mulai / lencana terkunci).

Nav: Peta Kedu (kiri) dan, bila wilayah punya halaman Pustaka, **Pustaka {wilayah}** (`Game.libraryRegion`). Selama keempat tantangan belum selesai, tombol itu bergembok dan membawa `aria-disabled="true"`, `data-library-locked`, `data-locked-title`, `data-locked-message`, `data-locked-progress`, `data-locked-ok`, `data-index-label`, `data-index-href`; `game/map.js` membuka modal penjelasan dari atribut itu. Tanpa JavaScript tautannya membuka halaman Pustaka terkunci.

### Kartu misi — `game/mission-brief`

Panel perkamen `.mission-card`: segel nomor, eyebrow `Game.challengeOf` + wilayah, judul tantangan, chip jenis tantangan (`engine_label()`), deskripsi (`challenge_nodes.description_*`), instruksi, audio narasi kartu misi (`audio_intro_id`/`audio_intro_en_id`, hanya yang disetujui), bintang terbaik (`Game.bestResult`), dan tombol besar **Mulai tantangan** → `/tantangan/{code}/{seq}`. Tombol membawa `curtain_attrs('challenge', …)`: tirai tantangan diputar di halaman ini **sebelum** pindah, karena pengerjaan baru dibuka saat halaman tantangan dirender (`ChallengeService::openNode()`), sehingga tirai tidak menambah waktu pengerjaan. Isi tirai (`<template id="tpl-curtain-challenge">`): label jenis, judul, ajakan `Game.challengeLead_{engine}`, visual `partials/challenge-art`. Latar: latar tantangan, cadangan latar wilayah. Nav: kembali ke peta wilayah.

### Layar tantangan — `game/challenge/{engine}` + `_frame`

Keempat engine merender section `arena` (dan opsional `aside`) lalu meng-include `game/challenge/_frame` (`body.is-challenge`). Tata letak lebar penuh **tanpa Jaka** (Dok 3 §5); koordinat di *Panggung 16:9*. Kerangka:

* `section.challenge.challenge-{engine}` dengan `data-screen="challenge"`, `data-engine`, `data-variant`, `data-attempt`, `data-node`, `data-level`, `data-level-id`, `data-region-name`, `data-sequence`, `data-exit-url`.
* Bingkai ukiran digambar `.challenge::before` (16, 60, 1248 × 652 dp). `.arena-panel`, `.arena`, dan `.arena-body` hanya pembungkus (`display: contents`), sehingga panel engine memakai koordinat panggung langsung.
* Kepala `.challenge-head.stage-box` (32, 64, 1216 × 56): nomor `.badge-pos`, eyebrow wilayah · `Game.challengeOf` · jenis, judul `h1`, chip `.challenge-chips`: waktu `#timer`, hitungan ✓ `#count-correct` / ✗ `#count-wrong`, penanda kesempatan `#tries.chip-tries` (engine selain `cari`; bulatan `.tries-dot.is-left` = sisa, `aria-label` "Kesempatan: x dari 3"), tombol petunjuk `#btn-hint` (tersembunyi, `data-hints`), dan **Keluar** berkonfirmasi `<details class="confirm confirm-down challenge-exit">` (`Game.exitConfirmTitle`, `Game.exitConfirmText`, tombol `data-exit-confirm` ke peta wilayah). Tautan HUD yang meninggalkan layar disembunyikan dan nav-bar tidak ada.
* `.arena`: panel instruksi `aside.challenge-aside.stage-scroll` (instruksi + section `aside`; tempatnya per engine: panel kiri Susun Gambar, baris pertama arena Isi Rumpang dan Benar atau Salah, panel kanan Cari Objek), `<noscript>` `Game.needsJs`, lalu `.arena-body` berisi section `arena`; tanpa soal `Game.noItems`.
* Payload `ChallengeService::openNode()` ditanam sebagai `<script type="application/json" id="challenge-data">` — **tanpa kunci jawaban** dan tanpa penanda pengecoh. `game/challenge.js` dan `engines/*.js` memasang perilaku (lihat [06_JAVASCRIPT.md](06_JAVASCRIPT.md)).

Tiap wilayah berisi empat tantangan berurutan: 1 Susun Gambar (`puzzle`), 2 Isi Rumpang (`rumpang`), 3 Benar atau Salah (`boleh`), 4 Cari Objek (`cari`). Setiap pengerjaan memakai satu variasi (a/b/c) yang dipilih acak.

**Susun Gambar — `puzzle.php`**

* `.puzzle-wrap#puzzle-wrap` (`data-mode="arrange"`, `data-item`) › `.puzzle-board.is-{square|jigsaw}#puzzle-board` (`role="group"`, `data-grid`, `data-shape`, variabel `--grid`, `--inset`, `--overhang`) › `svg.puzzle-defs` (satu `<clipPath>` per keping) + `.puzzle-grid` berisi `button.piece` (`data-slot`, `data-piece`, `data-pos`, `aria-label` `Game.piece` = letak slot) dengan `svg.piece-art` yang memotong **satu gambar utuh** (`preserveAspectRatio="xMidYMid slice"`) dan garis tepi `.piece-edge`.
* Papan mengikuti soal (`App\Libraries\PuzzleCut::resolve()`), yang ditetapkan dari tingkat kesulitan wilayah: Temanggung 3 × 3 kotak, Magelang 4 × 4 kotak, Wonosobo 4 × 4 jigsaw. Keping tidak bernomor. Urutan awal diacak tetap per pengerjaan tanpa satu keping pun di tempatnya (`seeded_derangement()`).
* Section `aside`: tiga tips `ul.challenge-tips` (`Game.puzzleHint`/`Game.puzzleHintJigsaw`, `Game.puzzleTipMark`, `Game.puzzleTipHint`) di panel kiri. Keterangan gambar `.puzzle-caption.stage-scroll` di panel kanan (tersembunyi sampai gambar utuh atau kesempatan habis), tombol `#btn-check` **Cek gambarku** (`Game.puzzleCheck`).

**Isi Rumpang — `rumpang.php`**

* `.rumpang` (`data-word-bank="1"`, `.has-media` bila ada gambar `.rumpang-media`, di kiri bacaan) › `.rumpang-main`; keduanya pembungkus `display: contents`.
* Tantangan bacaan (`config_json.as_reading`, `ChallengeNode::isReading()`): `.rumpang-reading.stage-scroll#reading-panel` (huruf `--fs-reading`) berisi judul variasi `h2.rumpang-title` (disembunyikan bila sama dengan judul tantangan) dan paragraf dari `App\Libraries\ClozeReading::paragraphs()`; setiap rumpang adalah `.blank-wrap` › `sup.blank-no` + `button.blank` (`data-item`, `aria-label` `Game.blank`). Selain itu soal tampil sebagai `ol.sentences` › `li.sentence` (teks sumber `.sentence-source` opsional).
* Bank kata `#word-bank.word-bank` (`role="group"`) berisi `button.word` (`data-word-id`) dari `payload.word_bank`: jawaban + pengecoh variasi, diacak server. Tidak ada isian ketik.
* Tombol `#btn-check` **Cek jawabanku** (`Game.check`).

**Benar atau Salah — `boleh.php`**

* Kolom kiri `.boleh-passages.stage-scroll#reading-panel` berisi bacaan variasi sebagai `<details class="passage" open>` (`#passage-{id}`): judul (cadangan `Game.readingLabel`), gambar `.passage-img`, `.passage-body`. Tidak ada lagi pelipatan bacaan di layar sempit.
* Kolom kanan `.boleh-cards.stage-scroll`: judul `Game.statementsLabel`, lalu `.card-grid` satu kolom berisi `article.verdict-card` (`data-item`): `.card-no` "Pernyataan n" (`Game.cardN`), chip `.card-passage` ke bacaannya, gambar `button.card-image[data-zoom]` 192 × 144 dp (utuh, `object-fit: contain`; ketuk = lightbox 640 × 480 dp), `.card-text`, dan `fieldset.card-choices` dengan dua radio bawaan `label.choice.choice-benar` / `.choice-salah` (`Game.verdictBenar` / `Game.verdictSalah`).
* Tombol `#btn-check` **Cek jawabanku**.

**Cari Objek — `cari.php`**

* `.hunt` (pembungkus) › bilah petunjuk `.hunt-clue` 1216 × 56: avatar bulat Mbah Kedu 48 dp (`.hunt-guide`, tetap ada), `.hunt-clue-body` dengan "Petunjuk x dari y" (`Game.clueOf`, `#clue-index`, `#clue-total`), teks petunjuk `#clue-text` (`aria-live`, petunjuk pertama dirender server) dan penanda kesempatan per petunjuk `#clue-tries`.
* `#hunt-scene.hunt-scene` (16:9; tanpa gambar `.is-drawn`) berisi gambar adegan `img.scene-bg` dan `button.object` (`data-object` = token buram per pengerjaan, posisi `left`/`top`/`width` dalam persen, `aria-label` netral "Objek n"). **Benda pengecoh dirender sama persis dengan benda target**, tanpa kelas, atribut, label, atau urutan DOM yang membedakan.
* `.hunt-side`: `Game.huntList` + `ol#hunt-list` › `li.hunt-target` (`.is-current` untuk petunjuk aktif); `engines/cari.js` mengganti isinya dengan tombol lompat `.hunt-jump` dan menandai petunjuk yang dilewati `.is-skipped` (✗).
* Narasi petunjuk: bila ada petunjuk dengan rekaman yang disetujui (`clues[i].audio`), `<template id="tpl-clue-voice">` berisi tombol ▶/⏸ (`Game.clueListen`, `Game.audioPause`) yang dipasang `engines/cari.js` di samping teks petunjuk. Template inert: tanpa JavaScript atau tanpa rekaman, tampilannya sama dengan petunjuk teks saja.

**Aturan tiga kesempatan di layar.** Nilai penanda selalu dari server (`max_tries` − `tries_used`, lalu `tries_left` tiap respons). Percobaan salah membuka modal per engine dengan sisa kesempatan; kesempatan habis membuka satu modal "Kesempatanmu sudah habis" dengan tombol Lanjut ke layar Selesai. Rinciannya di [06_JAVASCRIPT.md](06_JAVASCRIPT.md).

### Selesai — `game/challenge-finished`

Redesigned 18 October 2026 from catalogue mockup 01 (`gelita-katalog/mockup/gambar-mockup/tantangan-selesai-temanggung/`); one layout for every region and challenge kind. Decisions in the decision log, *Page redesign 1*.

`body.is-celebrate`, CSS confetti `.confetti` (24 pieces, off with reduced motion), HUD variant `components/hud-menu` (section `hudStyle` = `menu`), background `bg.result.{code}` or else the region background, no nav bar. Panel `.result-card` 133, 145, 1017 × 495 dp (skin `ui.result-panel`, plain navy and gold while empty):

* `.result-kind` (`Game.engine_*`), `h1` `Game.finishedTitle`, `.result-stars` (`role="img"`, `Game.resultStarsOf`; filled stars `ui.result-star`, else the star icon);
* `.result-shards`: medal `ui.result-medal` (else the light orb) with the glow of catalogue `ui.result-shard-effects` (inline in `game.css`), `Game.resultShards`, "{shards} / {total}", one bar segment per shard;
* `.result-summary`: cards Waktu and Percobaan (`Game.resultTries`, "{cek} dari {max_tries}"), or Ketukan salah for Cari Objek; badge `.result-badge.is-solved` / `.is-saved`; one `.result-message`: `Game.exhaustedNote`, `Game.multiCheckNote`, or `Game.resultFirstTry`;
* `.result-actions`: `Game.resultRegionMap` to `/wilayah/{code}` (skin `ui.result-button-secondary`), **Detail hasil** `<details class="result-detail" data-popover>` with Berhasil pada Percobaan Pertama (`first_pass_text()`, skin `ui.result-button-detail`), and the main button (skin `ui.result-button-primary`) in this order: this attempt completes the region → **Lanjut** to `/tuntas/{code}`; every challenge done → **Balai Refleksi**; a next challenge → **Berikutnya** to its mission card; replay in a completed region → **Lanjut ke {wilayah berikutnya}** (`entry`, region curtain); region completed → Peta Kedu; else **Berikutnya** to the region map.

When the region is complete, `aside.result-kedu` (290, 168, 700 × 352) covers the upper panel: Mbah Kedu `.finished-kedu` (`Game.regionDone` + `Game.nextRegionOpen` or `Game.allShardsDone`) and, for the attempt that opened it, `.library-unlocked` with Baca Pustaka. `game/finished.js` shows its close button and plays `region-done` instead of `shard` (`data-region-done`).

### Hasil tantangan — `game/challenge-result`

Panel `.result-panel` 160, 72, 960 × 576: pengerjaan terbaik `.result-best` (bintang, Berhasil pada Percobaan Pertama, waktu, jumlah cek), tabel semua pengerjaan selesai `.result-table` di `.table-wrap.stage-scroll` (Pengerjaan, Mulai, Durasi, Percobaan, Berhasil pada Percobaan Pertama, Bintang; baris terbaik `.is-best`), catatan `Game.retryNote` (mengulang tidak menghapus catatan lama), dan tombol **Ulangi** (`Game.retry`) ke kartu misi — sehingga tirai tantangan ikut tampil.

### Wilayah tuntas — `game/region-done`

Adegan `level_done` (4 baris) lewat `partials/dialogue-scene` (`data-screen="region-done"`), kartu ketuk varian `done`: eyebrow "Wilayah n · Tuntas", judul `Game.doneTitle` ("Serpihan {wilayah} kembali!"). Slide akhir: **Baca Pustaka {wilayah}** (`Game.readLibraryRegion`, bila ada Pustaka) dan **Lanjut ke {wilayah berikutnya}** (`entry`, tirai wilayah). Wilayah terakhir: **Lanjut** ke `/penutup` bila semua tantangan tuntas, selain itu Peta Kedu. Boleh ditonton ulang; wilayah yang belum tuntas dialihkan ke peta wilayahnya. Nav: kembali ke wilayah (`Game.backToRegion`), Peta Kedu.

### Penutup — `game/ending`

Seperti cerita pembuka (`partials/cine-slides`, `body.is-cinematic`, `data-context="ending"`): lima slide `ending`, kartu ketuk, efek, audio. Slide akhir dan nav **Lewati** → `/refleksi`. Hanya terbuka bila seluruh tantangan tuntas; selain itu dialihkan ke `/peta`. Dibuka dari "Lanjut" di wilayah tuntas terakhir dan dari "Tonton penutup" di peta.

### Pustaka Kedu (rak) — `game/library-index`

Satu kartu `li.shelf-card.is-{open|locked|unavailable}` per wilayah: sampul `.shelf-cover` (gambar pertama galeri halaman pertama → latar wilayah → gradien; sampul yang gagal dimuat disembunyikan `library.js`), lencana `.shelf-badge` (`Game.libraryStatus_*`), eyebrow urutan + jumlah halaman, judul "Pustaka {wilayah}".

* **Terbuka** (wilayah tuntas) → tombol **Baca** (`Game.libraryRead`).
* **Terkunci** (wilayah terbuka, belum tuntas) → `partials/library-lock` + **Lanjutkan tantangan** (`Game.libraryContinue`) ke `entry` wilayah.
* **Belum terbuka** → keterangan `Game.libraryUnavailable` saja.

Status dihitung `GameProgress::libraryStatus()`: Pustaka sebuah wilayah terbuka setelah **semua** tantangan wilayah itu selesai pada sesi pemain, apa pun mode buka wilayah studinya. Di peta dan rak namanya "Pustaka Kedu" (`Game.library`); di dalam wilayah "Pustaka {wilayah}" (`Game.libraryRegion`).

### Pustaka {wilayah} — `game/library`

Dirender hanya bila Pustaka wilayah terbuka; event `library_opened` dicatat. Buku `ol.slides.book` › `li.slide.book-spread#page-{n}` (`.is-text-only` bila tanpa media):

* Kiri `.book-media` › `.book-gallery`: media sebanyak apa pun dari `library_media` (`LibraryPage::gallery()`), masing-masing `figure.book-figure.is-{kind}.via-{provider}` dalam bingkai berasio tetap `.book-frame` dengan skeleton perkamen selama dimuat. Gambar berupa `button.book-zoom` (`data-zoom`) yang dibuka `library.js` di lightbox `<dialog>`; video berkas `<video controls preload="none">`; video YouTube/Vimeo/Google Drive sebagai *facade* `.book-embed` (poster thumbnail yang sudah diunduh server, lencana penyedia, tombol Putar, `Game.libraryEmbedNote`). Iframe baru dibuat setelah Putar ditekan, jadi membuka Pustaka tidak menghubungi domain luar; tanpa JavaScript facade adalah tautan ke halaman aslinya. Kredit lewat `partials/media-credit`; `figcaption` hanya berisi keterangan. Media yang berkasnya tidak ada disembunyikan beserta bingkainya.
* Kanan `.book-text`: `Game.pageOf`, judul, `.book-body` dari `rich_text()`, dan `slide-nav` (halaman terakhir: Tutup ke peta wilayah).
* Kepala `.library-head` dengan tombol tutup `.library-close`. Tanpa halaman: `Game.libraryEmpty`. Nav: peta wilayah, Pustaka Kedu.

### Pustaka terkunci — `game/library-locked`

Dirender (bukan redirect) bila wilayah sudah terbuka tetapi tantangannya belum semua selesai, agar penjelasannya terbaca tanpa JavaScript: ikon gembok, `Game.libraryLockedTitle`, `partials/library-lock`, tombol **Lanjutkan tantangan** (`entry`) dan **Pustaka Kedu**. `library_opened` tidak dicatat. Wilayah yang belum terbuka dialihkan ke `/peta` dengan toast `Game.levelLocked`.

### Profil — `game/profile`

Kartu `.profile-card`: avatar (`components/avatar`, 72 dp: gambar pilihan atau huruf awal), nama, `@username`, kode peserta kecil dengan keterangan `Game.researchCode` (kode penelitian, bukan untuk masuk), tombol **Ganti kata sandi** → `/ganti-sandi`. Grid `stat-tile`: umur, jenis kelamin, kelas, sekolah, serpihan, Berhasil pada Percobaan Pertama ("x dari y soal"), total waktu, bintang, tantangan selesai. Rak lencana `.badge-shelf` per wilayah (gambar `reward.badge.{code}` bila tuntas dan ada, selain itu ikon), lalu tabel riwayat pengerjaan. Nav: Peta Kedu, dan Balai Refleksi bila semua tantangan tuntas.

**Ganti avatar** (display plan Phase B): `<details class="profile-avatar-change">` di bawah kartu identitas, hanya bila ada avatar yang diunggah. Isinya `components/avatar-picker` mode `submit`: setiap ubin bulat 64 dp adalah tombol kirim ke POST `/profil/avatar` (satu ketukan langsung menyimpan); ubin pertama "huruf awal namaku". Pilihan aktif bercincin emas dan bertanda ✓ (`aria-pressed`). Sesudah menyimpan, `<details>` terbuka lagi dan toast `Game.avatarSaved` tampil.

### Balai Refleksi — `game/reflection`

Hanya terbuka setelah semua tantangan tuntas (selain itu ke `/peta` dengan `Game.reflectionLocked`). Latar `bg.reflection`, cadangan `bg.map`.

* Kepala: Mbah Kedu pose `smile`, `Game.reflectionTitle`, balon `Game.reflectionLead`.
* Empat `stat-tile` dari ringkasan perjalanan (`journey`, pengerjaan terbaik tiap tantangan): Berhasil pada Percobaan Pertama, tantangan selesai, total waktu, soal benar.
* `.reflection-bars`: **Bintang per wilayah** (`Game.perRegion`, "x dari 12" untuk empat tantangan) dan ketepatan per jenis tantangan (`Game.perEngine`, persen).
* Catatan "paling sering terlewat" (`Game.mostMissed`, `Game.mostMissedText`: "{1} dari {2} soal").
* Formulir kritik & saran (POST `/refleksi`): bintang 1–5 sebagai radio bawaan, empat `textarea` (`liked_most`, `hardest_part`, `new_learning`, `suggestion`, label `Game.q_*`), minimal dua terisi (`Game.needTwoHint`, diperiksa ulang server). Bila sudah pernah mengirim: `Game.feedbackSent`.

### Tirai — `components/curtain` + `core/curtain.js`

Layar pemuatan layar penuh `.curtain.curtain-{kind}` (`data-curtain-layer`, `data-preload`, `data-statuses`, `data-tap`, opsional `data-min-ms`/`data-max-ms`/`data-skip`, `aria-busy`), dirender di section `overlay`. Selama tirai tampil, HUD, `<main>`, dan nav bar `inert`.

| Jenis | Isi | Durasi | Ketukan | Dipasang di |
|---|---|---|---|---|
| `map` | latar `bg.loading`, lentera `.curtain-lantern` dengan cahaya yang membesar mengikuti progres, tiga serpihan mengorbit, judul `Game.curtainMapTitle`, status bergilir `Game.curtainMapStatus1..3`, berkas cahaya `.curtain-beam` (`role="progressbar"`), tombol `Game.curtainMapTap` | progres nyata, ±2,5–8 detik | penutup, membuka kunci audio (`Sfx.unlock()`) | `/peta` dengan flash `curtain=map` |
| `region` | jejak kaki `partials/footsteps`, judul "Menuju {wilayah}…", tagline, chip tingkat kesulitan (slot `data-curtain-text`) | 1,8–2,4 detik | tidak ada | pin dan kartu wilayah, slide akhir Kenali, "Lanjut ke {wilayah berikutnya}" di layar Selesai dan wilayah tuntas |
| `challenge` | `partials/challenge-art` (`data-engine`), label jenis, judul, ajakan, `Game.curtainSkip` | 1,2–1,5 detik | melewati | **Mulai tantangan** di kartu misi |

* Tautan `a[data-curtain="{kind}"]` memutar tirai dari `<template id="tpl-curtain-{kind}">` sebelum pindah halaman; tanpa template atau tanpa JavaScript tautan berjalan biasa. Satu template `region` melayani semua wilayah; teks dan aset pramuatnya dari atribut tautan.
* Tanpa JavaScript tirai tidak pernah menutupi halaman (`noscript.css`). Bila modul JavaScript gagal dimuat, animasi pengaman `curtain-failsafe` memudarkan tirai sendiri; `curtain.js` membatalkannya dengan kelas `is-live`.

**Short curtain when assets are local (asset package Stage 4, P8).** `region` and `challenge` carry `data-local-min-ms="600"`; when `assetsLocal()` is on (Stage 2), they last at least 600 ms instead of 1.8 s / 1.2 s. The `map` curtain that opens the game keeps 2.5 s. See `docs/06_JAVASCRIPT.md` §`core/curtain.js`.

### Screen transitions and next-screen images (asset package Stage 4)

* **Cross-document View Transitions** (`stage.css`): `@view-transition { navigation: auto; }` plus `view-transition-name` on `#stage` (`gelita-stage`), the background `#stage > .scene` (`gelita-scene`), and the HUD `#stage > .hud` (`gelita-hud`). Every game screen is still a new document; the browser keeps the old screen up until the new one renders, then cross-fades in 260 ms, with the HUD staying in place. Chrome ≥ 126 and Safari ≥ 18.2 animate; other browsers ignore the at-rule and navigate as before. `prefers-reduced-motion: reduce` sets `navigation: none`. Only `layouts/game.php` loads `stage.css`, so the panel is unchanged. Reloads (F5) never animate (browser rule).
* **`data-next-assets`** on the screen element names the next screen's images: `next_assets_attr(list $urls, int $limit = 6)` (`gelita_helper.php`) drops empty values and duplicates, keeps at most 6, and returns ` data-next-assets="[…]"` or an empty string. `game/next-assets.js` decodes them while idle (table in `docs/06_JAVASCRIPT.md`). `partials/dialogue-scene` takes `nextAssets`.
* **Language button during a challenge** is hidden like the HUD leave links (`body.is-challenge .lang-switch { display: none; }`, owner decision P5), because the reload would drop unchecked answers.

### Pola ketuk-untuk-mulai

Browser (terutama Safari iPad) memblokir audio bersuara sampai pengguna mengetuk halaman itu sendiri, dan GELITA adalah aplikasi multi-halaman. Karena itu setiap layar bernarasi (cerita pembuka, dialog wilayah, wilayah tuntas, penutup) dibuka kartu `components/narrator-tap`; di Peta Kedu perannya diambil ketukan penutup tirai, dan overlay Kenali dimulai oleh ketukan tombolnya sendiri.

1. Ketukan memanggil `Sfx.unlock()` dan membuka satu elemen `<audio>` bersama untuk semua slide layar itu.
2. Narasi slide diputar otomatis; teks tampil dengan mesin ketik (ketukan pertama menampilkan teks penuh, ketukan kedua lanjut).
3. Sakelar **Otomatis** (bawaan menyala, disimpan sebagai preferensi perangkat) memajukan slide setelah audio selesai; slide terakhir tidak pindah halaman sendiri.
4. Semua narasi punya audio **dan** teks. Tanpa audio bahasa aktif yang disetujui, teks tetap tampil dan slide dilanjutkan manual.
5. Efek layar dari `dialogues.effect` diputar lapisan `.narrator-fx` (`fx-fog`, `fx-fog-lift`, `fx-glow`, `fx-flash`, `fx-shake`, `fx-dim`).

### Kondisi aset hilang

Aplikasi tetap utuh selama gambar belum diunggah:

* Latar: `media_first()`/`media_key_src()` mengembalikan string kosong/`null`, lalu layout memakai gradien CSS.
* Tokoh: monogram `.character-fallback`; pose yang belum ada jatuh ke `idle`.
* Logo dan tombol Mulai: judul teks dan tombol CSS emas-navy.
* Peta tanpa gambar: kanvas `.is-drawn`; adegan Cari Objek tanpa gambar: `.hunt-scene.is-drawn`.
* `media_src()` untuk gambar soal jatuh ke `assets/ui/placeholder.svg`.
* Gambar bertulisan punya varian bahasa lewat akhiran kunci (`ui.btn-start` dan `ui.btn-start.en`), dibaca `media_key_src_locale()`.
* Gambar contoh (mockup) yang dibuat `php spark gelita:mockups` untuk 45 slot gambar tantangan adalah berkas SVG biasa (`media_assets.is_placeholder = 1`) dan tampil seperti gambar lain sampai diganti gambar asli di panel.

## Panel admin

Dasbor SaaS tenang dan formal (Dok 3 §8), **tidak bertema GELITA**: token di `admin.css` (latar `#F5F6F8`, permukaan putih, garis `#E4E7EC`, teks `#101828`, aksen indigo `#4338CA`, sidebar `#0F172A`, Plus Jakarta Sans 14/20, kode IBM Plex Mono 13 px, radius 8/12 px). Panel mengikuti lebar layar (bukan panggung 16:9) dan tidak memuat chart. Setiap halaman dibuka `partials/admin-head` lalu `partials/flash`. Tabel data memakai `components/data-table` (urut, halaman, dan CSV di server); filter memakai `components/admin-filter-bar` (GET, tersimpan di URL, chip filter aktif). Guru selalu terkunci pada sekolahnya: kontrol sekolah tidak dirender, kolom identitas tidak diambil, dan server memaksa cakupan (`StaffScope::scopedFilters()`, `RawDataService`).

### Menu (`App\Libraries\AdminMenu`)

| Grup | Menu (`Admin.menu*`) | URL | Ikon | Peran |
|---|---|---|---|---|
| — | Ringkasan | `/admin` | `grid` | guru, admin |
| Data penelitian | Siswa | `/admin/peserta` | `users` | guru, admin |
| Data penelitian | Sesi bermain | `/admin/sesi` | `clock` | guru, admin |
| Data penelitian | Data mentah | `/admin/data` | `database` | guru, admin |
| Data penelitian | Kritik & saran | `/admin/masukan` | `message` | guru, admin |
| Data penelitian | Unduh data | `/admin/ekspor` | `download` | guru, admin |
| Konten | Tantangan | `/admin/konten` | `puzzle` | admin |
| Konten | Impor bank soal | `/admin/konten/impor-bank` | `upload` | admin |
| Konten | Rekaman narasi | `/admin/konten/narasi` | `sound` | admin |
| Konten | Gambar & mockup | `/admin/media` | `image` | admin |
| Konten | Kelengkapan aset | `/admin/media/kelengkapan` | `clipboard` | admin |
| Pengaturan | Penelitian | `/admin/studi` | `flask` | admin |
| Pengaturan | Rilis | `/admin/studi/rilis` | `tag` | admin |
| Pengaturan | Sekolah | `/admin/sekolah` | `building` | admin |
| Pengaturan | Staf | `/admin/staf` | `key` | admin |
| Pengaturan | Tata kelola | `/admin/tata-kelola` | `shield` | admin |

**Ganti kata sandi** dan **Keluar** ada di menu akun topbar. Rute yang tidak berhak tetap dibalas 404 untuk guru.

### Halaman

| View | URL | Isi |
|---|---|---|
| `admin/login` (layout `auth`) | `/admin/login` | Kartu masuk bergaya admin: nama pengguna, kata sandi, `redirect_to` tersembunyi. Akun tidak ada, sandi salah, dan akun nonaktif dibalas pesan yang sama. |
| `admin/dashboard` | `/admin` | **Ringkasan:** empat KPI hitungan (siswa terdaftar, sesi aktif sekarang ≤ 10 menit, pengerjaan selesai hari ini, kritik & saran); admin: peringatan retensi, gambar contoh tersisa, narasi belum disetujui, verifikasi konten (`ResearchReadiness`); tabel **Aktivitas terbaru** (20 event: waktu, kode siswa, kejadian, layar/tantangan) dan **Progres siswa** (10 terbaru: kode, sekolah, wilayah saat ini, tantangan selesai x/12, bintang, aktif terakhir). |
| `admin/participants/index` | `/admin/peserta` | Filter cari, sekolah, kab/kota, jenis kelamin, umur, status progres, tanggal daftar; tabel Kode, Nama*, Nama pengguna*, Sekolah, Kab/Kota, JK, Umur, Terdaftar, Setuju ikut, Tantangan selesai, Wilayah terakhir, Bintang, Aktif terakhir (*admin saja); CSV. |
| `admin/participants/show` | `/admin/peserta/{id}` | Aksi: Unduh XLSX / CSV-ZIP dan Cetak PDF (admin: centang Anonim), Reset sandi. Tab: **Profil** (data diri, pendaftaran & akun, literasi keamanan digital, `.danger-zone` reset sandi ketik `RESET` + modal konfirmasi), **Sesi & perangkat**, **Progres** (12 tantangan: status, variasi, pengerjaan, Berhasil pada Percobaan Pertama x dari y, bintang, durasi), **Pengerjaan**, **Jawaban** (tombol **Riwayat** membuka percobaan per soal), **Navigasi**, **Bantuan** (data mentah satu siswa), **Kritik & saran**. Guru tidak melihat nama dan nama pengguna. |
| `admin/participants/reset-result` | respons POST `/admin/peserta/{id}/reset-sandi` | Sandi sementara **satu kali** (`.temp-password`, mono besar, `user-select: all`), tombol Salin (`data-copy`), peringatan bahwa siswa wajib membuat sandi baru. Dikirim dengan `Cache-Control: no-store`; tidak lewat flash atau log. |
| `admin/sessions/index` | `/admin/sesi` | Filter kode siswa, sekolah, penelitian, bahasa, tanggal; tabel kode sesi, kode siswa, sekolah, penelitian, bahasa, status, mulai, aktif terakhir, durasi, perangkat, tantangan selesai, bintang; CSV. |
| `admin/sessions/show` | `/admin/sesi/{id}` (`/admin/sesi/{id}/event` dialihkan ke `#linimasa` halaman ini) | Ringkasan (`.detail-grid`), tabel pengerjaan di sesi ini, lalu **Linimasa event** `#linimasa`: filter jenis event, tabel urutan, waktu klien, waktu server, event (kode, label di `title`), layar/tantangan/soal, payload JSON; urut, halaman, CSV. |
| `admin/data/index` | `/admin/data/{dimensi}` | **Data mentah:** tab Jawaban & ketepatan · Riwayat percobaan · Pengerjaan · Progres · Penyelesaian · Navigasi · Bantuan · Perangkat; filter kode siswa, sekolah, wilayah, tantangan, variasi, tanggal; tabel `RawDataService::page()` (nilai `enum` berlabel `ExportLabels::label()`, kode di `title`); CSV per tabel. |
| `admin/feedback/index` | `/admin/masukan` | Filter sekolah dan tanggal; tabel kode siswa, sekolah, bintang (1–5), empat jawaban (dipotong, klik untuk lengkap), waktu; CSV. |
| `admin/export/index` | `/admin/ekspor` | Formulir: filter sekolah/wilayah/tanggal (`bare`), isi nilai (bawaan/berlabel/berkode), anonim (guru: selalu), pilihan lembar di `<details>`; tombol **Buat XLSX** dan **Buat CSV-ZIP**. Tabel **Riwayat unduhan**: format, status (`data-export-id`, diperbarui `admin/export.js`), baris, ukuran, SHA-256, kedaluwarsa, Unduh. |
| `admin/content/index` | `/admin/konten` | **Tantangan:** aksi Rincian verifikasi, Cerita pembuka & penutup, Impor bank soal; tabel 12 tantangan (kode, wilayah, urutan, jenis, judul, variasi aktif x/3, soal, gambar contoh tersisa, status verifikasi) dan tabel wilayah dengan tautan Wilayah · Bacaan · Pustaka & Kenali · Cerita & dialog. |
| `admin/content/level` | `/admin/konten/level/{id}` | `partials/content-nav`; teks wilayah berpasangan ID/EN (`.bilingual`: nama, fokus literasi, capaian pembelajaran, tujuan pembelajaran, pengantar), tingkat kesulitan, aktif, gambar wilayah lewat `media-field` (peta `map.region.{code}` 1400 × 900, latar `bg.{code}.region` 1920 × 1080, lencana `reward.badge.{code}` 320 × 320); bagian **Pin, lentera, dan tirai di peta Kedu** dengan `components/slot-field` (kode tetap, hanya unggah): `map.pin.{code}`, `map.lantern.{code}` (256 × 256), `bg.loading.{code}` (1920 × 1080); daftar tantangan. |
| `admin/content/node` | `/admin/konten/node/{id}` | Editor bertab (`data-tabs`): **Info** (judul, perintah, cerita kartu misi ID/EN, jenis — terkunci bila sudah dimainkan, kode variasi tampilan, indikator, gambar adegan/latar, narasi kartu misi, aktif); **Variasi** (judul ID/EN dan pengecoh rumpang per variasi a/b/c, `config_json` di `<details>`, tabel **Soal per variasi**: kode, variasi, urutan, jenis, Bloom, keterampilan, kunci, tinjauan, dinilai, aktif; lalu kartu sunting soal `.item-card` dan **Tambah soal**); **Posisi benda** (Cari Objek: kartu benda dengan pemilih koordinat). Satu form node terbagi ke dua tab; tanpa JavaScript semua bagian tampil berurutan. |
| `admin/content/items` | (di dalam node) | Form satu soal: kode (baru), jenis (`puzzle_arrange`, `fill_blank_bank`, `verdict_card`, `find_object`), variasi, urutan, status pemeriksaan, bacaan, indikator, tingkat Bloom (`DifficultyRules::BLOOM`), pertanyaan ID/EN, rekaman petunjuk ID/EN (hanya tantangan `cari`), gambar soal (`media-field`), teks sumber, panduan per jenis `.type-guide[data-for]` (ditampilkan lewat `:has()`), kunci jawaban dan pengaturan soal JSON, sumber rujukan, catatan pemeriksaan, Dinilai, Aktif. `admin/content-editor.js` menambah editor terpandu (kalimat `___`, Benar/Salah, pemilih koordinat benda di atas `data-scene`) yang menulis ke kolom JSON yang sama. |
| `admin/content/passages` | `/admin/konten/bacaan/{levelId}` | Satu `fieldset.repeat-row` per bacaan (kode, judul dan isi ID/EN, gambar, sumber rujukan, aktif, hapus — dinonaktifkan bila masih dipakai soal) + baris bacaan baru. |
| `admin/content/library` | `/admin/konten/pustaka/{levelId}` | Semua halaman Pustaka wilayah dalam satu form; tiap halaman punya media sebanyak apa pun (berkas terdaftar/unggahan, atau tautan YouTube, Google Drive, Vimeo, Wikimedia Commons, atau berkas https langsung). Halaman dan media nonaktif tetap tampil bertanda. |
| `admin/content/dialogues` | `/admin/konten/dialog/{levelId}?konteks=…` | Satu konteks naskah per halaman: `levelId = 0` untuk `intro`, `map_intro`, `ending`; selain itu `region_intro`, `level_open`, `level_done`. Per slide (`fieldset id="slide-{n}"`): tokoh, pose, efek, judul, teks ID/EN, audio narasi ID/EN; khusus `intro` dan `ending` juga latar slide (`media-field`, form multipart, kode bawaan `bg.story.{konteks}.{urutan}`, 1920 × 1080; kosong = `bg.intro`, lalu `bg.welcome`). |
| `admin/content/verify` | POST `/admin/konten/verifikasi` | Temuan pemeriksaan kelengkapan (3 wilayah × 4 tantangan, tiga variasi per tantangan, pengecoh rumpang per variasi, kunci jawaban, verdict, bacaan, soal yang belum diverifikasi, gambar contoh), dikelompokkan **Harus diperbaiki** dan **Perlu dicek**. |
| `admin/content/import` | `/admin/konten/impor-bank` | Langkah impor: unggah workbook → **pemeriksaan** (POST `…/pratinjau`, tidak menulis apa pun; ringkasan per tantangan, kesalahan per sheet + nomor baris, peringatan) → **Simpan soal ke permainan** (POST `…/jalankan`, hanya bila tanpa kesalahan). Tautan unduh templat, hasil impor terakhir, riwayat impor dari `audit_logs`. |
| `admin/narration/index` | `/admin/konten/narasi` | Aksi: Unggah rekaman, Unduh daftar rekaman (Excel), Kelengkapan gambar & suara. Panel kemajuan per bahasa (disetujui / menunggu persetujuan / belum ada, batang `.progress-stack` ber-`role="img"`) dengan `admin/narration/approve-all`. Lalu satu tabel per konteks dan wilayah (urut naskah): kode berkas, tokoh + pose + efek, cuplikan teks, status rekaman ID dan EN (lencana + pemutar kecil `.audio-preview-sm`), dan **Sunting** ke editor dialog (`#slide-{n}`) atau, untuk petunjuk Cari Objek `petunjuk-{node}-NN`, ke soalnya (`#item-{id}`). Total 100 rekaman per bahasa (88 baris naskah + 12 petunjuk). |
| `admin/narration/upload` | `/admin/konten/narasi/unggah` | `files[]` (banyak berkas audio) + bahasa + "periksa nama saja"; panel batas sekali unggah (per berkas, jumlah berkas, total) yang juga diperiksa `admin/narration-upload.js` sebelum dikirim; laporan hasil (baru, diganti, sama, nama tidak dikenal dengan saran, gagal, baris yang belum punya rekaman); impor dari folder server `public/assets/audio/narasi/{id,en}/`. |
| `admin/narration/approve-all` | (komponen) | Tombol "Setujui semua rekaman {bahasa} (n)" dengan `<details class="confirm">`; hanya narasi naskah dan petunjuk. Dipakai halaman Narasi dan Audio. |
| `admin/media/index` | `/admin/media` | **Gambar & mockup:** KPI slot, asli, gambar contoh, kosong, ukuran tidak sesuai; filter status (Asli / Gambar contoh / Kosong) dan jenis dengan chip; tabel slot (pratinjau, kode, label, isi yang diharapkan, ukuran berkas (wajib dan sebenarnya) & ukuran tampil dp (`$assetDisplaySizes`), status, dipakai di, **Unggah gambar asli**, Nonaktifkan berkonfirmasi); form unggah `#unggah` dan tabel ukuran wajib; hasil Pindai berkas. Pratinjau (juga frame tokoh) dibuka di lightbox panel. Halaman detail `/admin/media/{id}` (`admin/media/show`): pratinjau ber-lightbox; untuk frame tokoh, semua gambar pose itu berdampingan dan gerakannya diputar seperti di permainan (`core/sprite.js`, tombol Putar ulang untuk pose sekali-putar); form unggah hanya menerima jenis berkas slot (`ImageSlots::types()`). |
| `admin/media/audio` | `/admin/media/audio` | Panel narasi cerita (tautan ke Narasi, tombol setujui semua per bahasa), form unggah audio, tabel rekaman (konteks, tokoh, bahasa, transkrip, status persetujuan, pemakaian, pemutar, tombol Setujui). |
| `admin/media/checklist` | `/admin/media/kelengkapan` | Kelengkapan aset (`App\Libraries\AssetChecklist`): jumlah belum ada / perlu diperiksa / lengkap, lalu tabel per kelompok dengan status ikon + teks, ukuran wajib, dan tautan ke tempat mengunggahnya. |
| `admin/study/index` | `/admin/studi` | **Daftar siap penelitian** (0 gambar contoh, narasi disetujui, verifikasi siap penelitian lolos; tiap butir bertautan), lalu satu kartu per penelitian + form baru (kode, nama, deskripsi, tahun ajaran, status draf/berjalan/ditutup, retensi, bahasa bawaan, mode buka wilayah). Memilih status Berjalan saat daftar belum hijau memunculkan centang **Tetap jalankan** (`confirm_not_ready`, diperiksa server). *(Stage 6, English:)* the checklist has a fourth item, **Paket terbit, lengkap, dan terverifikasi** (link to Paket aset), and a running study shows **Paket aset terkunci** with a warning when students now get another package. |
| `admin/study/releases` | `/admin/studi/rilis` | **Rilis** (`game_releases`): versi aktif, daftar dengan Aktifkan (modal konfirmasi), form versi baru (selalu nonaktif). *(Stage 6, English:)* a **Mode paket** panel (Nyala/Mati, active package, link **Buka Paket aset**), the changed-asset notice, the "Gambar & suara" column shows the package code or "tanpa paket", and during a running study rows whose package differs link to the confirmation page (**Aktifkan…**). |
| `admin/governance/index` | `/admin/tata-kelola` | **Tata kelola**, tab Penghapusan data & retensi · Riwayat aktivitas. Hapus data dua langkah: hitung data terdampak tanpa menghapus, lalu jalankan setelah mengetik `HAPUS` atau batalkan. `?participant_id=` mengisi cakupan dari detail siswa. Riwayat permintaan dan panel retensi. |
| `admin/governance/audit` | `/admin/tata-kelola/audit` | Riwayat aktivitas staf (`audit_logs`), filter aksi dan tanggal (GET), metadata per baris di `<details>`, 50 baris per halaman. |
| `admin/schools/index` | `/admin/sekolah` | **Cari sekolah** (NPSN/nama, per kab/kota berdirektori) dan **Rapikan nama sekolah**: nama ketikan siswa digabung ke sekolah lain (menjadi alias) atau disahkan sebagai sekolah ber-NPSN; gabung otomatis untuk kecocokan yang jelas. |
| `admin/staff/index` | `/admin/staf` | Satu `<details>` per akun (nama, peran, sekolah, wajib ganti sandi, terkunci, terakhir masuk) berisi form sunting, reset sandi (ketik `RESET`), nonaktifkan berkonfirmasi; form akun baru. Kolom sekolah disembunyikan CSS `:has()` saat peran Admin dipilih. Sandi sementara hasil reset hanya tampil di respons itu (`Cache-Control: no-store`). |
| `admin/study/package` | `/admin/studi/rilis/paket` | *(Asset package Stage 6, English.)* **Paket aset** (breadcrumb Rilis › Paket aset; no sidebar entry of its own). Error alert "Mode paket dimatikan dari server" when `gelita.paket.aktif = false`. Changed-asset notice. Panel **Mode paket** with badge Nyala/Mati (`data-package-mode`), active package KPIs (code, download size, files, build date), verification line + **Periksa sekarang**, and **Nyalakan** (starts a build) or **Matikan** (modal confirmation). While a build runs: panel with `.progress` bar (`role="progressbar"`), "{n} dari {m} berkas", and a no-JavaScript **Lanjutkan membangun** button, driven by `js/admin/package-build.js` (loaded through the `scripts` section, one POST per batch, retries after 2/4/8 s, then **Coba lagi**; reloads the page when finished). **Ringkasan paket baru**: KPIs (size, files, new or changed files for devices with the active package, build date), table of sizes per part (`admin_label('packageGroup')`, regions as "Wilayah …"), warnings grouped by code (`admin_label('packageWarning')`, up to 8 keys each), **Pratinjau** (new tab, `/?paket={code}`) and **Terbitkan dan nyalakan** / **Terbitkan paket ini**. Panel **Izinkan mode daring untuk perangkat lab** (checkbox + hidden `0`). **Riwayat paket**: staff preview explanation, "Anda sedang melihat pratinjau paket …" with **Hentikan pratinjau** (`/?paket=mati`), table of the last 10 packages (status via `admin_label('packageStatus')`) with **Pratinjau**. During a running study the publish and switch-off forms include `admin/partials/study-confirm-fields` (tick `confirm_running_study` + `reason`, both `required`). |
| `admin/study/confirm-activation` | GET `/admin/studi/rilis/{id}/aktifkan` | *(English.)* Activating a release with another asset package during a running study: package now → package of this release (`.detail-grid`), `study-confirm-fields`, **Aktifkan versi ini** / Batal. |
| `admin/partials/package-notice` | (partial) | *(English.)* "Ada {n} gambar/suara yang berubah sejak paket terakhir. Siswa belum melihatnya." with **Bangun ulang paket** (POST `/admin/studi/rilis/paket/bangun`, then the package page drives the build). Rendered on Paket aset, Rilis, Gambar & mockup, and Rekaman narasi from `AssetPackageService::changedSinceActive()`; nothing while package mode is off or nothing changed. |
| `admin/partials/study-confirm-fields` | (partial) | *(English.)* Running-study warning with the confirmation tick and the reason field, rendered inside a form (`$study`, `$idPrefix`). |
| `admin/account/password` | `/admin/akun/sandi` | Ganti sandi sendiri (guru & admin): sandi saat ini, sandi baru (12–72 karakter), ulangi. Bila `$mustChange`, pemberitahuan `.alert-info` bahwa halaman panel lain baru terbuka setelah sandi disimpan. Kolom nama pengguna tersembunyi tanpa `name` membantu pengelola sandi browser. |

## Laporan PDF dan halaman galat

**`pdf/report-participant.php`** — laporan satu siswa (mPDF), dibuat dari form di profil siswa (POST `/admin/ekspor/pdf`). Berisi tabel identitas (kode siswa; nama dan sekolah hanya bila tidak anonim — laporan dari akun guru selalu anonim), kelas, penelitian, pembuat dan tanggal, lalu "Ringkasan data mentah" (pengerjaan selesai, Berhasil pada Percobaan Pertama, benar di akhir, total waktu, petunjuk dibuka, suara diputar) dan "Cara membaca". Tanpa skor olahan atau persentase. Gaya bersama di `pdf/_style.php` (hanya CSS yang dipahami mPDF: tabel, warna, border; tanpa flex/grid atau variabel CSS).

**Halaman galat.** `errors/html/error_404.php` dan `error_500.php` bergaya GELITA dan mandiri (CSS di dalam berkas, agar tetap tampil walau aset gagal dimuat), dwibahasa lewat `Game.notFoundTitle`/`Game.notFoundText` dan `Game.serverErrorTitle`/`Game.serverErrorText`, dengan tombol `Game.backHome`. Halaman 500 tidak menampilkan pesan exception atau trace, hanya `request_id` yang sama dengan baris di `writable/logs/`. Berkas galat lain (`error_400.php`, `error_exception.php`, `production.php`, `errors/cli/`) adalah bawaan CodeIgniter.

## CSS

### Berkas

Delapan berkas di `public/assets/css/`, dimuat berurutan lewat `asset_url_versioned()`. Tidak ada framework CSS atau build step. CSS game (`tokens`, `base`, `stage`, `components`, `game`, `cinematic`) ditulis dalam `rem` panggung (lihat *Panggung 16:9*).

| Berkas | Isi | Dimuat oleh |
|---|---|---|
| `tokens.css` | warna, lapisan, tipografi, bayangan (bersama); ukuran game dalam rem (`--fs-*`, `--gap-*`, `--tap`, `--hud-h`, `--nav-h`, `--gutter`); ukuran panggung `:root.game-stage` | game |
| `base.css` | `@font-face` lokal, reset, elemen dasar, tipografi, `:focus-visible`, `.skip-link`, `.visually-hidden`, `[hidden]`, aturan gerak dikurangi global | game |
| `stage.css` | bingkai `.stage-frame`, panggung `#stage`, `.stage-box`, `.stage-scroll`, latar `.scene`, HUD, `.stage-main`, nav bar, lapisan modal & toast, layar putar, dan **semua koordinat tata letak layar** | game |
| `components.css` | tombol (`.btn`, `.btn-primary`, `.btn-ghost`, `.btn-quiet`, `.btn-danger`, `.btn-sm`/`.btn-lg`/`.btn-xl`, `.btn-block`), panel (`.panel`, `.panel-parchment`, `.panel-carved`), form, `.alert-*`, `.chip`, `.badge`, `.confirm`/`.confirm-box`, toast, modal, tabel, `.empty-state`, bintang, `stat-tile`, komponen kata sandi, pemutar audio, karakter, balon narasi, paginasi | game |
| `game.css` | rupa layar permainan: lentera & chip peserta, halaman awal, akun & pendaftaran, cerita & dialog, peta, kartu misi, empat arena, selesai & hasil, Pustaka, profil, Balai Refleksi | game |
| `cinematic.css` | tirai, pemutar narasi (kartu ketuk, kontrol, mesin ketik, efek `.fx-*`), cerita pembuka/penutup, narasi peta, overlay Kenali, tirai wilayah & tantangan (`.cc-*`), kartu bab, panggung dialog, wilayah tuntas | game (sesudah `game.css`) |
| `noscript.css` | `.curtain { display: none !important; }` | game, hanya di dalam `<noscript>` |
| `admin.css` | **berkas mandiri panel admin dan halaman masuk staf** (Dok 3 §8): font, token §8.1, dasar, sidebar/topbar, kepala halaman, tab, tombol, formulir, lencana, peringatan, bilah filter + chip, KPI, tabel data, pagination, modal, toast, halaman khusus (konten, media, studi, …), responsif (ikon < 1280 px, laci < 768 px) | admin, auth |


### Token (`tokens.css`)

| Kelompok | Token |
|---|---|
| Warna | `--navy-900` `#0B1320`, `--navy-800`, `--navy-700`, `--navy-600`; `--gold-500` `#DFC087`, `--gold-600`, `--gold-300`; `--parchment`, `--parchment-dark`; `--brown-700`, `--brown-500`; `--ok`, `--bad`, `--warn` dan versi teksnya `--ok-ink`, `--bad-ink`, `--warn-ink`; `--ink`, `--ink-dim` |
| Lapisan & garis | `--veil` (lapisan gelap di belakang teks di atas gambar), `--veil-strong`, `--line`, `--line-strong`, `--line-ink`, `--surface`, `--surface-2`, `--glow` |
| Tipografi | `--font-display` (Cinzel), `--font-body` (Plus Jakarta Sans), `--font-mono` (IBM Plex Mono); ukuran `--fs-caption` 16, `--fs-body` 20, `--fs-reading` 22 (bacaan), `--fs-h3` 24, `--fs-h2` 32, `--fs-h1` 40, `--fs-display` 56 dp |
| Ruang & bentuk | `--gap-1` … `--gap-7` (4, 8, 12, 16, 24, 32, 48 dp), `--gutter` 32 dp, `--radius` 12, `--radius-lg` 20, `--radius-pill`, `--shadow-1`, `--shadow-2`, `--shadow-glow`, `--line-w` |
| Sentuh & layout | `--tap` 48 dp, `--hud-h` 56 dp, `--nav-h` 64 dp; panggung `--app-w`/`--app-h`, `--stage-w`/`--stage-h`, `--letterbox` (`:root.game-stage`) |

Panel admin memakai token sendiri di `admin.css` (§8.1: `--bg`, `--surface`, `--line`, `--text`, `--accent`, `--side-*`, `--radius` 8 px, `--control-h` 40 px, `--row-h` 44 px, jarak `--sp-1` … `--sp-7` dalam px) dan tidak memuat `tokens.css`, `base.css`, atau `components.css`.

Font dimuat lokal dari `public/assets/fonts/` (berkas `.woff2` + lisensi OFL) dengan `font-display: swap`, bukan dari CDN: koneksi sekolah tidak dapat diandalkan, dan font pihak ketiga mengirim alamat IP anak ke luar.

### Aturan penulisan

* Kelas berbahasa Inggris, dipisah tanda hubung (`.challenge-head`, `.word-bank`); state memakai awalan `is-`/`has-` (`.is-active`, `.is-locked`, `.is-current`, `.has-error`).
* Warna, ukuran teks, dan jarak lewat token; berkas lain tidak menulis nilai warna identitas baru.
* `!important` hanya pada utilitas `[hidden]` (`base.css`, `admin.css`), `noscript.css`, dan aturan gerak dikurangi global `admin.css`.
* Tata letak di dalam panel dengan Grid dan Flexbox; letak panel di panggung hanya lewat `stage.css`. Posisi pin peta dan benda Cari Objek ditulis inline dalam persen; variabel per elemen (`--grid`, `--i`, `--map-ratio`, `--steps`) juga lewat atribut `style`.
* CSS game: satuan `rem` (1rem = 16 dp); `px` hanya untuk garis 1px; tanpa `vw`/`vh` dan tanpa `@media` lebar/tinggi (`tests/unit/StageCssTest.php`).

### Panggung 16:9

Semua layar game digambar di **satu panggung tetap 1280 × 720 dp** (dp = *design pixel*), yang diperbesar/diperkecil seragam agar pas di layar lalu diletakkan di tengah; sisa layar menjadi bingkai gelap (`--letterbox`). Tidak ada tata letak responsif: tampilan di laptop, proyektor, tablet, dan ponsel mendatar sama, hanya skalanya yang berbeda (Dok 3 §1–§6).

**Satuan.** `:root.game-stage { font-size: calc(var(--stage-w) / 80) }`, jadi `1rem = 16 dp` dan `#stage` berukuran `80rem × 45rem`. `--stage-w = min(--app-w, --app-h × 16/9)`; `--app-w`/`--app-h` diisi `core/viewport.js` di `<head>` lalu `core/stage.js` dari `innerWidth`/`innerHeight` (cadangan tanpa JavaScript: `100vw`/`100dvh`). Contoh: 1280 × 800 → panggung 1280 × 720, 1 rem = 16 px; 1024 × 768 → 1024 × 576, 12,8 px; 1920 × 1080 → 24 px; ponsel 844 × 390 → 693 × 390, ± 8,7 px.

**Kerangka dan pita tetap.**

| Pita | dp (x, y, w, h) | Isi |
|---|---|---|
| HUD `.hud` | 0, 0, 1280, 56 | kiri: lentera serpihan + merek (lentera tetap kiri atas); tengah: section `hudTitle` atau nama wilayah; kanan: bahasa, suara, layar penuh (`#btn-fullscreen`, tersembunyi bila Fullscreen API tidak ada), beranda, profil, keluar. Saat tantangan tautan yang meninggalkan layar disembunyikan |
| Area isi `main#app.stage-main` | 0, 0, 1280, 720 | `<main>` selebar panggung tanpa stacking context, jadi koordinat isi = koordinat panggung; isi tidak diletakkan di bawah HUD (y < 56) |
| Nav bar `.nav-bar` | 0, 656, 1280, 64 | kembali di kiri, tombol utama di kanan. Layar sinematik: "Lewati" di kanan atas (y 72). Layar tantangan tanpa nav bar |
| Modal `#modal-layer` | 0, 0, 1280, 720 | kotak 480–720 × maks 560 dp di tengah, isi bergulir bila panjang |
| Toast `#toast-layer` | 856, 72, 400 | kanan atas di bawah HUD; z 60, di atas tirai modal (z 50) |

`#stage` memakai `contain: layout paint`, sehingga `position: fixed` di dalamnya (tirai, overlay Kenali, konfeti) berlabuh ke panggung, dan apa pun yang keluar dari panggung terpotong. Lightbox `<dialog>` dirender peramban di top layer, tetapi ukurannya dalam rem sehingga tetap di dalam panggung.

**Koordinat.** Kelas `.stage-box` meletakkan elemen dari variabel dp tanpa satuan `--x --y --w --h`; elemen bagian (partial) yang dipakai ulang mendapat aturan yang sama lewat daftar selektor di `stage.css`. Semua angka ada di `stage.css` §Koordinat:

| Layar | Elemen (x, y, w, h dp) |
|---|---|
| Halaman awal | logo 240, 120, 800 × 300; Mulai 460, 456, 360 × 120; tawaran pasang 896, 576, 352 × 40; staff login link bottom-right (right 24, bottom 16) |
| Mulai, Gerbang | judul 0, 88, 1280 × 96; kartu 212, 200, 400 × 300 dan 668, 200 |
| Masuk / Ganti sandi | panel 360, 88, 560 × 544 / 360, 72, 560 × 576 (gulir) |
| Daftar 1–3 | panel 200, 72, 880 × 576: kepala + stepper 0–64, isian gulir, bilah tombol 80 dp di bawah; langkah 3 kotak "Mengapa sandi harus kuat?" dengan Jaka 220 dp |
| Cerita pembuka, penutup | tokoh 64, 136, 300 × 520 (gambar 300 × 386); kotak teks 160, 520, 960 × 168 (judul + teks bergulir, navigasi slide di baris kepala); kontrol narasi + Lewati di kanan atas y 72 |
| Dialog wilayah, wilayah tuntas | judul 0, 64, 1280 × 64; Jaka 64, 136 dan Mbah Kedu 916, 136 (300 × 520); kotak teks 160, 472, 960 × 168 (naik dari 520 karena ada nav bar) |
| Peta Kedu | peta 32, 72, 880 × 566; panel wilayah kanan 936, 72, 312 × 576 (gulir); bilah narasi Jaka 40, 536, 864 × 98 (Jaka 124 × 168 menjulang ke atas) |
| Kenali wilayah | overlay penuh 0, 0, 1280 × 720, isi dan komposisi slide sama |
| Peta wilayah | peta 32, 72, 880 × 566; panel kanan 936, 72, 312 × 576: judul, fokus, progres, daftar tantangan (gulir) |
| Kartu misi | 280, 96, 720 × 528; deskripsi bergulir; hasil terbaik: bintang + "Berhasil pada Percobaan Pertama x dari y" |
| Tantangan | kepala 32, 64, 1216 × 56; arena 32, 128, 1216 × 576 — per engine lihat tabel berikut |
| Selesai | kartu 280, 72, 720 × 576, tanpa Jaka |
| Hasil | panel 160, 72, 960 × 576, tabel bergulir |
| Rak Pustaka | keterangan 88, 64, 1104 × 48; kartu 88/464/840, 120, 352 × 440 |
| Pustaka {wilayah} | buku 64, 80, 1152 × 568: galeri kiri 576 (gulir), teks kanan 576 (isi bergulir, navigasi halaman di bawah); lightbox selebar panggung |
| Pustaka terkunci | kartu 320, 140, 640 × 420 |
| Profil | kiri 32, 72, 400 × 576 (identitas, empat ubin); kanan 456, 72, 792 × 576 (lencana + riwayat bergulir) |
| Balai Refleksi | kiri 32, 72, 520 × 576 (ringkasan, gulir); kanan 576, 72, 672 × 576 (formulir bergulir, Kirim tetap di bawah) |

| Engine | Panel (x, y, w, h dp) |
|---|---|
| Susun Gambar | instruksi + 3 tips 32, 128, 332 × 512; papan 388, 136, 504 × 504; bilah aksi 388, 648, 504 × 48; keterangan gambar 916, 128, 332 × 512 |
| Isi Rumpang | instruksi 96, 128, 1088 × 40; bacaan 96, 176, 1088 × 384 (gulir, `--fs-reading`; dengan gambar `.has-media`: gambar 96, 176, 304 × 384 dan bacaan 416, 176, 768 × 384); bank kata 96, 568, 1088 × 72; bilah aksi 96, 648, 1088 × 56 |
| Benar atau Salah | bacaan 32, 128, 596 × 576 (gulir); instruksi 652, 128, 596 × 64; pernyataan 652, 200, 596 × 440 (gulir; gambar 192 × 144); bilah aksi 652, 648, 596 × 56 |
| Cari Objek | bilah petunjuk 32, 128, 1216 × 56 (avatar Mbah Kedu 48); daftar benda 32, 192, 164 × 486; adegan 208, 192, 864 × 486; instruksi 1084, 192, 164 × 486 |

**Gulir.** Panggung dan halaman tidak pernah bergulir. Panel yang isinya bisa panjang memakai `.stage-scroll` (tinggi tetap, `overflow-y: auto`); huruf tidak pernah dikecilkan untuk memuatkan teks. `core/dom.js` `scrollToCenter()` menggulir panel `.stage-scroll` terdekat.

**Aset** (Dok 3 §6, `Config\Gelita::$assetDisplaySizes` di samping `$assetSizes`): latar 1920 × 1080 → 1280 × 720 dp; peta 1400 × 900 → 880 × 566; adegan Cari Objek 1280 × 720 → 864 × 486; gambar Susun Gambar ≥ 1200² → 504 × 504; gambar pernyataan 480 × 360 → 192 × 144 (lightbox 640 × 480); tokoh 700 × 900 → 300 × 386 (cerita), 220 × 283 (pendaftaran); logo hero 1600 × 600 → 800 × 300; tombol Mulai 720 × 240 → 360 × 120. Pin peta dan benda Cari Objek dalam persen gambar, jadi posisinya sama di semua ukuran.

**Media query yang boleh** di CSS game: `(orientation: portrait) and (pointer: coarse)` (layar putar), `prefers-reduced-motion`, `hover: hover`. `tests/unit/StageCssTest.php` menjaga aturan satuan dan media query; `tools/qa/stage-check.mjs` memeriksa hasil tampilannya di peramban.

**Uji tampilan otomatis (`tools/qa/stage-check.mjs`).** Playwright + Chromium (sudah terpasang; jangan `playwright install`) terhadap `php spark serve` dengan database uji hasil instalasi baru atau `gelita:reset`, studi berjalan dengan *Cara wilayah terbuka* = bebas. `node tools/qa/stage-check.mjs [--base=…] [--user=… --pass=…] [--sizes=1280x720,…] [--only=peta,…]`: mendaftarkan siswa uji (tanpa `--user`), mengunjungi semua layar dan keempat tantangan di ketiga wilayah pada 1280 × 720, 1920 × 1080, 1366 × 657, 1024 × 768, 2560 × 1080, dan 844 × 390, membuka modal hasil, Kenali, dan lightbox Pustaka, lalu menghabiskan kesempatan setiap tantangan (jawaban asal) untuk layar selesai, hasil, wilayah tuntas, Pustaka, penutup, dan refleksi; terakhir 390 × 844 sentuh harus menampilkan layar putar. Yang diperiksa: rasio `#stage` 16:9 (±1 px) dan di tengah, halaman tidak bergulir, tidak ada elemen keluar dari panggung, isi yang melebihi wadahnya hanya di `.stage-scroll`, dan posisi relatif penanda (pin, benda, panel) sama di semua ukuran (selisih < 0,5 %). Tangkapan layar di `writable/qa/stage/` (diabaikan git). Kode keluar 1 bila ada yang gagal.

### Touch (display plan Phase F)

The stage cannot enlarge controls on phones only (a landscape phone at 844 × 390 shows the stage at about 0.54), so every target uses its full area and touch annoyances are removed. All rules stay inside `StageCssTest` (dp only; queries `hover: hover`, `prefers-reduced-motion`, and the portrait-touch rotate gate).

- **Basics.** `body.game` has no tap highlight. Controls and the stage use `touch-action: manipulation` (no double-tap zoom, pinch zoom kept); puzzle pieces use `touch-action: none` for the finger drag. Play surfaces (pieces, Cari scene, words and blanks, map, characters, cinematic stages) are not selectable and show no long-press callout; reading text stays selectable. Scene, map, and character images are not draggable.
- **Press feedback.** `:active` on buttons (`scale(.96)`), icon buttons, options, words, blanks, pins, objects, cards, and dots; pieces brighten instead of scaling (a scale would also scale the drag translate). No transforms under reduced motion.
- **No sticky hover.** Hovers that transform or highlight a border/outline sit inside `@media (hover: hover)`; colour-only hovers stay as they were.
- **Hit areas of at least 48 dp** (`--tap`). Small controls keep their look and get a centred `::before` of `max(100%, var(--tap))`: `.btn` (so `.btn-sm` and the 40 dp story/dialogue Lanjut/Kembali), `.icon-btn` (HUD, narrator), `.narrator-auto`, `.chip-btn`, `.lang-opt`, `.word`, `.blank`. Slide dots get a 48 dp tall area as wide as dot plus gap, so neighbours never overlap. Checkboxes and radios count through their `.check` label (full row, 48 dp). Exceptions: slide dots are 48 dp tall but only as wide as dot plus gap, and the map story's stacked narrator buttons keep their 36 dp height (a taller area would cover the next button); both repeat full-size Lanjut/Kembali. `.hud-brand` and `.card-passage` get their own 48 dp tall `::before`. `tools/qa/stage-check.mjs` reports any interactive element in `#stage` whose box and `::before`/`::after` are both under 48 dp (links inside running text and the two exceptions above are exempt; 1 px tolerance for rounding).
- **Cari Objek zoom.** `.hunt-zoom` (48 dp) sits in the scene's top-right corner. `.hunt.is-zoomed` turns the scene into 0, 0, 1280 × 720 (z 40), the clue into a ribbon 16, 16, 1184 × 56 (z 41, taps pass through it), and the button to 1216, 20 (z 42); modals (z 50) and toasts (z 60) stay above.
- **Form fields.** See `06_JAVASCRIPT` (*Touch*) for the on-screen keyboard shift and the iOS focus-zoom flag; inputs carry `autocorrect="off"` (usernames, school fields) and `enterkeyhint`.

### Layar putar dan aplikasi terpasang

Area permainan hanya dilayani **mendatar** di perangkat sentuh, karena semua aset dibuat satu versi mendatar.

* `components/rotate-gate` ada di setiap halaman `layouts/game.php`, **di luar** panggung (`position: fixed`, seluruh layar). Tampil dan hilangnya murni CSS (`stage.css`, media query `(orientation: portrait) and (pointer: coarse)`), jadi bekerja tanpa JavaScript; `pointer: coarse` membuat laptop berjendela sempit tidak ikut dihalangi. Selama tampil, panggung disembunyikan dan 1 rem diukur dari lebar layar (`calc(var(--app-w) / 24)`) agar teksnya terbaca. Tidak ada tombol lewati; `Game.rotateStuck` menjelaskan cara menyalakan putar otomatis.
* `game/rotate-gate.js` hanya menjaga aksesibilitas: selama penghalang tampil, sisa `<body>` dibuat `inert` dan fokus dipindah ke penghalang. Media query di CSS dan JavaScript wajib sama (`tests/unit/RotateGateTest.php`).
* Tombol **Layar penuh** di HUD (Fullscreen API) disembunyikan bila tidak didukung (mis. Safari iPhone). Layar penuh lepas setiap kali halaman berganti; karena itu memasang GELITA sebagai aplikasi tetap dianjurkan.

**Aplikasi terpasang.** Hanya `layouts/game.php` yang menautkan `/manifest.json` (`display: fullscreen`, `orientation: landscape`, URL relatif). Manifest dibuat `Game\AppController::manifest()` (bukan berkas statis), sehingga ikonnya dapat diganti di panel: slot `app.icon-192`, `app.icon-512`, `app.icon-maskable`, plus `app.favicon` (ketiga layout) dan `app.apple-touch` lewat helper `app_icon_src()`. Slot kosong memakai berkas bawaan di `public/assets/app/` (`icon-192.png`, `icon-512.png`, `icon-maskable-512.png`, `apple-touch-icon.png`) dan `public/favicon.ico`. Ikon tidak ikut paket aset. `public/sw.js` adalah service worker minimal yang hanya menangani navigasi `GET` (jaringan dulu, `public/offline.html` bila putus); API, event permainan, formulir, dan aset tidak pernah melewatinya, sehingga tidak ada data anak yang tersimpan di cache perangkat kelas (`tests/unit/InstallableAppTest.php`). `game/install.js` mendaftarkan service worker dan menampilkan tawaran pasang di halaman awal. Di Android aplikasi terpasang terkunci mendatar dan layar penuh; iPhone/iPad mengabaikan `orientation`, jadi layar putar tetap berlaku di sana.

### Pustaka pihak ketiga

Satu-satunya pustaka frontend adalah **Howler.js** (`public/assets/vendor/howler.min.js`, lisensi `LICENSE-howler.md`) untuk efek suara dan musik latar di area permainan. Narasi memakai elemen `<audio>` bawaan. Semua berkas dimuat dari domain sendiri; tidak ada CDN.

---

## Aturan sistem

1. Semua output dinamis melewati `esc()`. Data untuk JavaScript dikirim lewat `<script type="application/json">`, tidak pernah diinterpolasi ke string JavaScript; view tidak memuat skrip inline selain skrip ukuran panggung di `layouts/game.php` (tanpa data).
2. Kunci jawaban dan penanda pengecoh tidak pernah muncul di HTML, atribut data, maupun payload halaman.
3. Teks antarmuka memakai `lang()`; teks konten memakai `text()`/`tr()` yang jatuh ke bahasa Indonesia bila versi Inggris kosong. Area permainan dwibahasa; panel admin hanya Indonesia.
4. Jumlah Serpihan Cahaya dan jumlah tantangan selalu dihitung dari database, tidak ditulis sebagai angka tetap di view.
5. Informasi dasar tidak bergantung pada JavaScript; hanya arena tantangan yang membutuhkannya, dan arena memberi tahu bila JavaScript mati.
6. Layar tantangan tidak punya jalan keluar tak sengaja: satu tombol Keluar dengan konfirmasi, tanpa nav-bar dan tanpa tautan HUD yang meninggalkan layar.
7. Setiap tabel admin punya kondisi kosong yang menjelaskan; filter panel memakai `method="get"`.
8. Kolom kata sandi tidak pernah diisi ulang server, dan `password_hash` tidak pernah dikirim ke view mana pun (peserta dan staf lewat `toSafeArray()`). Sandi sementara hanya tampil sekali dengan `Cache-Control: no-store`.
9. Nama pengguna siswa hanya terlihat oleh siswa itu sendiri di area permainan; ekspor dan laporan dari akun guru selalu tanpa nama.
10. Tidak ada permintaan ke domain pihak ketiga dari halaman yang dibuka anak sebelum anak sendiri menekan Putar pada video Pustaka; font, ikon, dan pustaka di-host sendiri.
11. Perangkat sentuh hanya dilayani mendatar; aset cukup satu versi mendatar.
12. Semua layar game digambar di panggung 16:9 1280 × 720 dp yang diskalakan seragam; halaman tidak pernah bergulir, hanya panel `.stage-scroll`.
