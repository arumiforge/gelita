# 05_VIEW_UI.md — View, UI, dan CSS

> **Revisi 2 (21 September 2026).** Ditambahkan: halaman registrasi siswa dengan nama pengguna, kata sandi, ulangi sandi, **indikator kekuatan + daftar syarat + keterangan bila sandi belum kuat**; halaman masuk dan ganti sandi siswa; komponen `password-field`; arena `boleh` dengan tombol Benar/Salah/Pendapat dan panel teks bacaan/dua sumber; halaman admin impor bank soal, teks bacaan, reset sandi siswa. Halaman `/lanjutkan` dihapus. Jumlah halaman dikoreksi.

---

## Tujuan

Membuat seluruh antarmuka GELITA: layout, komponen bersama, setiap halaman permainan, setiap halaman panel admin, dan arsitektur CSS-nya. Setelah tahap ini selesai, aplikasi sudah terlihat utuh dan dapat dinavigasi; perilaku dinamis (AJAX, mesin tantangan) dikerjakan tahap 6.

---

## Konteks

Frontend memakai **CodeIgniter View + HTML5 + CSS3** saja. Tidak ada Vue, React, Vite, atau framework CSS. Halaman dirender server; JavaScript hanya menambah interaksi.

Dua wajah yang berbeda:

* **Game** — layar penuh, latar bergambar, HUD lentera di atas, tombol besar ramah anak SD, dwibahasa ID/EN.
* **Admin** — sidebar kiri, panel, tabel padat, chart. Satu bahasa (Indonesia).

Identitas visual yang dipertahankan: **navy gelap + emas**, judul memakai serif bergaya klasik, isi memakai sans humanis, angka memakai mono.

---

## Yang Harus Dibuat

| Kategori | Jumlah |
|---|---:|
| Layout | 3 |
| Komponen bersama | 17 |
| Halaman game | 17 |
| Halaman admin (berkas view) | 33 |
| Berkas CSS | 6 |

---

## View Structure

```text
app/Views/
├── layouts/
│   ├── game.php
│   ├── admin.php
│   └── auth.php
├── components/
│   ├── hud.php                  bar atas permainan (lentera, bahasa, profil)
│   ├── lantern.php              meter serpihan cahaya
│   ├── lang-switch.php          tombol ID / EN
│   ├── character.php            gambar Jaka / Mbah Kedu
│   ├── narration.php            balon narasi karakter
│   ├── audio-player.php         play / pause / ulangi / transkrip
│   ├── nav-bar.php              tombol navigasi bawah layar game
│   ├── modal.php                kerangka dialog tengah layar
│   ├── toast.php                notifikasi sementara
│   ├── star-rating.php          bintang 0..3
│   ├── stat-tile.php            satu angka + label
│   ├── admin-sidebar.php        menu panel
│   ├── admin-filter-bar.php     filter studi/fase/level/sekolah/tanggal
│   ├── admin-table.php          tabel + pengurutan + kosong-state
│   ├── admin-pagination.php     navigasi halaman
│   ├── admin-chart.php          wadah <div> untuk ECharts
│   └── password-field.php       input sandi + tombol lihat + meter + daftar syarat
├── game/
│   ├── welcome.php
│   ├── start.php
│   ├── consent.php
│   ├── register.php
│   ├── login.php
│   ├── change-password.php
│   ├── intro.php
│   ├── map-kedu.php
│   ├── map-level.php
│   ├── dialogue.php
│   ├── mission-brief.php
│   ├── challenge/
│   │   ├── puzzle.php
│   │   ├── rumpang.php
│   │   ├── boleh.php
│   │   ├── pilihan.php
│   │   └── cari.php
│   ├── challenge-finished.php
│   ├── challenge-result.php
│   ├── library.php
│   ├── profile.php
│   └── reflection.php
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── participants/index.php · show.php · reset-result.php
│   ├── sessions/index.php · show.php · timeline.php
│   ├── analytics/levels.php · nodes.php · node.php · items.php · indicators.php · prepost.php
│   ├── feedback/index.php
│   ├── content/index.php · level.php · node.php · items.php · library.php · dialogues.php · verify.php · passages.php · import.php
│   ├── media/index.php · audio.php
│   ├── study/index.php · releases.php · scoring.php
│   ├── export/index.php
│   ├── governance/index.php · audit.php
│   ├── staff/index.php
│   └── account/password.php        ← ganti sandi sendiri (guru & admin)
├── pdf/
│   ├── report-study.php
│   └── report-participant.php
└── errors/html/error_404.php · error_500.php
```

---

## Implementasi — Layout

### `layouts/game.php`

Sudah dijelaskan kerangkanya di 02_PROJECT_FOUNDATION.md. Tambahan yang wajib ada:

```php
<body class="game"
      data-locale="<?= esc($locale) ?>"
      data-base="<?= esc(base_url()) ?>">
  <?= $this->include('components/hud') ?>

  <div class="scene" aria-hidden="true">
    <img class="scene-img is-visible" id="scene-a" src="<?= esc($background ?? '') ?>" alt="">
    <img class="scene-img" id="scene-b" alt="">
  </div>

  <main id="app" class="app <?= esc($mainClass ?? '') ?>">
    <?= $this->renderSection('content') ?>
  </main>

  <?= $this->include('components/nav-bar') ?>
  <div id="modal-layer" class="modal-layer" hidden></div>
  <div id="toast-layer" class="toast-layer" role="status" aria-live="polite"></div>

  <script type="application/json" id="app-config"><?= json_encode([
      'locale'   => $locale,
      'csrfName' => csrf_token(),
      'csrfHash' => csrf_hash(),
      'apiBase'  => base_url('api'),
  ], JSON_UNESCAPED_SLASHES) ?></script>

  <script type="module" src="<?= asset_url_versioned('js/game.js') ?>"></script>
  <?= $this->renderSection('scripts') ?>
</body>
```

Dua `<img>` latar dipakai agar pergantian latar antar-halaman bisa dilakukan dengan crossfade, bukan berkedip.

Data untuk JavaScript dikirim lewat `<script type="application/json">`, **tidak pernah** dengan menyisipkan variabel PHP ke dalam string JavaScript. Ini mencegah XSS lewat konten yang diketik admin.

### `layouts/admin.php`

```php
<body class="admin">
  <header class="admin-head">
    <img class="admin-logo" src="<?= asset_url_versioned('ui/logo-gelita.png') ?>" alt="GELITA">
    <div class="admin-sub">
      <?= esc($pageTitle ?? '') ?>
      <?php if (!empty($activeStudy)): ?>
        · <?= esc($activeStudy['code']) ?>
      <?php endif ?>
    </div>
    <div class="admin-user">
      <b><?= esc(session('staff_name')) ?></b>
      <small><?= esc(session('staff_role')) ?></small>
      <!-- ≤ 640px: .btn-text tersembunyi secara visual → tombol ikon -->
      <a class="btn btn-quiet" href="<?= base_url('admin/akun/sandi') ?>" title="Ubah sandi"><?= icon('key') ?> <span class="btn-text">Ubah sandi</span></a>
      <a class="btn btn-quiet" href="<?= base_url('admin/logout') ?>" title="Keluar"><?= icon('logout') ?> <span class="btn-text">Keluar</span></a>
    </div>
  </header>

  <div class="admin-body">
    <?= $this->include('components/admin-sidebar') ?>
    <div class="admin-main">
      <?= $this->include('components/toast') ?>
      <?= $this->renderSection('content') ?>
    </div>
  </div>
  <script type="module" src="<?= asset_url_versioned('js/admin.js') ?>"></script>
</body>
```

### `layouts/auth.php`

Satu panel di tengah layar, latar `bg-auth`, tanpa sidebar. Dipakai `admin/login.php`.

---

## Shared UI

### `components/hud.php`

Bar atas permainan, selalu terlihat.

Isi:

* Kiri: tombol suara (aktif/nonaktif), tombol beranda, tombol kembali.
* Tengah-kiri: **lentera** (`components/lantern.php`).
* Kanan: chip peserta (nama + kode), tombol keluar, `components/lang-switch.php`.

```php
<header class="hud">
  <div class="hud-left">
    <div class="hud-icons">
      <button class="icon-btn" id="btn-sound" aria-pressed="true"
              aria-label="<?= esc(lang('Game.sound')) ?>">…</button>
      <a class="icon-btn" href="<?= base_url('peta') ?>"
         aria-label="<?= esc(lang('Game.home')) ?>">…</a>
    </div>
    <?= $this->include('components/lantern') ?>
  </div>
  <div class="hud-right">
    <a class="user-chip" href="<?= base_url('profil') ?>">
      <span class="user-name"><?= esc($participantName ?? '') ?></span>
      <small class="user-code">@<?= esc($participantUsername ?? '') ?></small>
    </a>
    <a class="icon-btn" href="<?= base_url('keluar') ?>"
       aria-label="<?= esc(lang('Game.logout')) ?>">…</a>
    <?= $this->include('components/lang-switch') ?>
  </div>
</header>
```

### `components/lantern.php`

Meter serpihan. Menampilkan `x / 15` dan bar isi. Nyala lentera punya 4 tahap sesuai jumlah wilayah tuntas (0–3).

```php
<div class="lantern" data-shards="<?= (int) $shards ?>" data-total="<?= (int) $shardsTotal ?>">
  <button class="lantern-toggle" aria-expanded="false" aria-controls="lantern-detail">
    <span class="flame flame-<?= (int) $lanternStage ?>"></span>
    <span class="lantern-text">
      <span class="eyebrow"><?= esc(lang('Game.shards')) ?></span>
      <span class="lantern-count"><b><?= (int) $shards ?></b>/<?= (int) $shardsTotal ?></span>
    </span>
  </button>
  <div class="lantern-bar"><i style="width: <?= (int) round($shards / max(1,$shardsTotal) * 100) ?>%"></i></div>
  <div class="lantern-detail" id="lantern-detail" hidden>
    <!-- 15 titik: selesai / terbuka / terkunci, dikelompokkan per wilayah -->
  </div>
</div>
```

`$shardsTotal` **selalu** datang dari `COUNT(challenge_nodes aktif)`, tidak pernah ditulis sebagai angka tetap.

### `components/lang-switch.php`

```php
<form class="lang-switch" method="post" action="<?= base_url('bahasa') ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="redirect_to" value="<?= esc(current_url(true)->getPath()) ?>">
  <button name="locale" value="id" class="lang-opt <?= $locale === 'id' ? 'is-active' : '' ?>">ID</button>
  <button name="locale" value="en" class="lang-opt <?= $locale === 'en' ? 'is-active' : '' ?>">EN</button>
</form>
```

Ini form POST biasa, bukan AJAX. Halaman dimuat ulang pada URL yang sama, server merender ulang dengan locale baru, dan progres tetap utuh karena seluruh state ada di database.

### `components/audio-player.php`

Dipakai di dialog, kartu misi, dan item yang punya audio.

```php
<div class="audio-player" data-audio-id="<?= (int) $audioId ?>"
     data-src="<?= esc($audioSrc) ?>">
  <button class="audio-btn" data-action="play"    aria-label="Putar">▶</button>
  <button class="audio-btn" data-action="pause"   aria-label="Jeda" hidden>⏸</button>
  <button class="audio-btn" data-action="replay"  aria-label="Ulangi">↺</button>
  <button class="audio-btn audio-transcript" data-action="transcript"
          aria-expanded="false">Teks</button>
  <div class="audio-bar"><i></i></div>
  <div class="audio-transcript-box" hidden><?= esc($transcript) ?></div>
</div>
```

Transkrip **wajib ada** untuk setiap audio. Ini bukan tambahan opsional: anak yang kesulitan mendengar, kelas tanpa pengeras suara, dan berkas audio yang gagal dimuat semuanya butuh teks penggantinya.

Bila `$audioSrc` kosong (audio belum `approved`), komponen merender hanya kotak transkrip tanpa tombol.

### `components/nav-bar.php`

Tombol navigasi di bawah layar. Isinya dikirim halaman sebagai array:

```php
$nav = [
  ['label' => lang('Game.back'), 'href' => base_url('wilayah/temanggung'), 'style' => 'quiet', 'arrow' => 'left'],
  ['label' => lang('Game.next'), 'href' => base_url('misi/temanggung/2'), 'style' => 'primary', 'arrow' => 'right'],
];
```

Layar tantangan mengirim `$nav = []` dan sebagai gantinya menampilkan satu tombol keluar yang **harus dikonfirmasi**, agar anak tidak tidak sengaja meninggalkan tantangan yang sedang dikerjakan.

### `components/password-field.php`

Dipakai di registrasi dan ganti sandi. Satu komponen, sehingga tampilan dan aturan selalu sama.

```php
<?php $policy = (new \App\Libraries\PasswordPolicy())->toClient(); ?>
<div class="password-field" data-username-field="<?= esc($usernameField ?? 'username') ?>">
  <label for="password"><?= esc(lang('Auth.password')) ?></label>
  <div class="password-input">
    <input id="password" name="password" type="password"
           autocomplete="new-password" minlength="8" maxlength="64" required
           aria-describedby="pw-rules pw-level">
    <button type="button" class="pw-toggle" aria-pressed="false"
            aria-label="<?= esc(lang('Auth.showPassword')) ?>">👁</button>
  </div>
  <p class="pw-caps" hidden><?= esc(lang('Auth.capsLock')) ?></p>

  <div class="pw-meter" aria-hidden="true"><i data-level="none"></i></div>
  <p class="pw-level" id="pw-level" aria-live="polite"></p>

  <ul class="pw-rules" id="pw-rules">
    <li data-rule="length"><?= esc(lang('Auth.ruleLength')) ?></li>
    <li data-rule="upper"><?= esc(lang('Auth.ruleUpper')) ?></li>
    <li data-rule="lower"><?= esc(lang('Auth.ruleLower')) ?></li>
    <li data-rule="digit"><?= esc(lang('Auth.ruleDigit')) ?></li>
    <li data-rule="symbol"><?= esc(lang('Auth.ruleSymbol')) ?></li>
  </ul>
  <?php if (! empty($errors['password'])): ?>
    <p class="field-error"><?= esc($errors['password']) ?></p>
  <?php endif ?>

  <label for="password_confirm"><?= esc(lang('Auth.passwordRepeat')) ?></label>
  <input id="password_confirm" name="password_confirm" type="password"
         autocomplete="new-password" required>
  <p class="pw-match" aria-live="polite"></p>
</div>
<script type="application/json" id="password-policy"><?= json_encode($policy) ?></script>
```

Aturan tampilan:

* Tiap syarat tampil dengan ikon ✗ abu-abu, berubah menjadi ✓ hijau saat terpenuhi. Warna **tidak pernah** jadi satu-satunya penanda — ikon dan teks ikut berubah.
* Meter tiga tingkat: lemah (merah, 1/3), sedang (oranye, 2/3), kuat (hijau, penuh), dengan teks `Auth.levelWeak/Medium/Strong`.
* Bila server menolak, form kembali dengan pesan di bawah daftar syarat. Kolom sandi **selalu kosong** saat form dikembalikan — tidak diisi ulang dari `old()`.
* Tanpa JavaScript, daftar syarat tetap terlihat sebagai panduan statis, dan server tetap menolak sandi lemah dengan pesan yang jelas.

### `components/modal.php`

Satu kerangka dialog dipakai semua keperluan: benar, salah, konfirmasi keluar, petunjuk.

```php
<div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
  <div class="modal-card modal-{type}">
    <div class="modal-icon">…</div>
    <img class="modal-character" src="…" alt="">
    <h3 class="modal-title" id="modal-title"></h3>
    <p class="modal-text"></p>
    <div class="modal-actions"></div>
  </div>
</div>
```

Modal dibuka lewat JavaScript (tahap 6) tetapi markup-nya didefinisikan di sini agar gaya konsisten.

### `components/admin-filter-bar.php`

Filter yang sama dipakai seluruh halaman analitik:

| Kontrol | Nama field | Isi |
|---|---|---|
| Studi | `study_id` | daftar `research_studies` |
| Fase | `phase_code` | semua / umum / pretest / posttest |
| Wilayah | `level_id` | semua / Temanggung / Magelang / Wonosobo |
| Sekolah | `school_id` | hanya tampil untuk admin; guru terkunci pada sekolahnya |
| Kelas | `class_level` | semua / 1..9 / lainnya |
| Provinsi | `province_code` | daftar dari data peserta |
| Tanggal | `date_from`, `date_to` | rentang |
| Bahasa | `locale` | semua / ID / EN |

Form `method="get"`, sehingga filter tersimpan di URL dan bisa dibagikan atau di-bookmark.

### `components/media-field.php`, `media-datalist.php`, `audio-select.php`

Pemilih media di editor konten admin. `media-field` menampilkan pratinjau aset terpasang, kotak `asset_key` (dengan `media-datalist`: seluruh aset terdaftar, termasuk slot yang belum berberkas), dan input berkas baru; server membacanya lewat `MediaStore::resolveField()` — berkas baru disimpan sebagai kunci yang diketik atau kunci bawaan tempat itu, kotak yang dikosongkan melepas media. `audio-select` memilih audio narasi dari `audio_assets` beserta status persetujuannya. Form induk wajib `enctype="multipart/form-data"`.

### `components/admin-table.php`

Menerima `$columns`, `$rows`, `$emptyMessage`. Wajib punya kondisi kosong yang menjelaskan, bukan tabel kosong:

```php
<?php if (empty($rows)): ?>
  <div class="empty-state"><?= esc($emptyMessage) ?></div>
<?php else: ?>
  <div class="table-wrap"><table class="data-table">…</table></div>
<?php endif ?>
```

---

## Halaman Game

Untuk tiap halaman: URL → controller → view, dan apa yang ditampilkan.

### 1. Welcome — `/` → `HomeController::index` → `game/welcome.php`

* Latar `bg-welcome`, logo GELITA besar, Jaka berdiri memegang lentera.
* Belum login: tombol **Mulai** (→ `/mulai`) dan **Masuk** (→ `/masuk`). Sudah login: tombol **Lanjutkan perjalanan** (→ `/peta`).
* Tautan kecil ke panel admin di pojok.
* Data: `$isLoggedIn`, `$logo`.

### 2. Start — `/mulai` → `game/start.php`

Dua kartu: "Saya baru" → `/persetujuan`; "Saya sudah punya akun" → `/masuk`.

### 3. Persetujuan — `/persetujuan` → `game/consent.php`

* Panel perkamen berisi teks persetujuan versi aktif: data apa yang dicatat, untuk apa, siapa yang dapat melihat, dan bahwa peserta boleh berhenti kapan saja.
* Dua checkbox: persetujuan peserta, persetujuan orang tua/wali.
* Isian nama orang tua/wali.
* Form POST ke `/persetujuan`.
* State: checkbox wajib tercentang sebelum tombol lanjut aktif.

### 4. Daftar — `/daftar` → `game/register.php`

Form (POST `/daftar`):

| Field | Kontrol | Catatan |
|---|---|---|
| `display_name` | text | nama lengkap |
| `age` | number 5–80 | |
| `gender` | select | laki-laki / perempuan / lainnya |
| `class_level` | select | Kelas 1–6 SD, 7–9 SMP, lainnya |
| `school_name` | text + datalist | datalist dari `schools` aktif |
| `country_code` | select | Indonesia / negara lain |
| `province_code` | select | dari `wilayah-id.json`, muncul bila Indonesia |
| `district_code` | select | terisi setelah provinsi dipilih |
| `country_other` | text | muncul bila negara lain |
| `phase` | select | umum / pretest / posttest — **hanya tampil bila `allow_phase_choice = 1`**; selain itu tidak dirender sama sekali |
| `locale` | hidden | locale saat ini |
| `username` | text | `autocomplete="username"`, huruf kecil otomatis, keterangan `Auth.usernameHelp`, status ketersediaan di bawahnya |
| `password` + `password_confirm` | `components/password-field` | meter, daftar 5 syarat, keterangan bila belum kuat |

Formulir dibagi tiga bagian bernomor agar tidak menakutkan anak: **1. Tentang kamu** (nama, umur, jenis kelamin, kelas) · **2. Sekolah dan daerah** · **3. Akun rahasiamu** (nama pengguna + kata sandi).

Bagian 3 dibuka dengan kotak perkamen kecil **"Mengapa kata sandi harus kuat?"** berisi `Auth.whyStrong` dan empat tips (`Auth.tip1`–`tip4`), ditemani ilustrasi Jaka memegang kunci. Ini materi literasi keamanan digital, bukan catatan kaki — beri ruang yang layak.

* Tombol **Daftar** tetap aktif walau sandi belum kuat. Bila ditekan, server menolak dengan keterangan syarat yang belum terpenuhi. Ini disengaja: penolakan itu sendiri adalah umpan balik belajar, dan jumlahnya dicatat sebagai `pw_weak_submit_count`.
* Setelah registrasi berhasil, halaman intro diawali kartu sambutan: "Ingat nama pengguna dan kata sandimu, ya. Jangan beri tahu teman." Kode peserta **tidak** ditampilkan di sini (kode itu untuk penelitian, bukan untuk login).
* State: field kosong ditandai merah; provinsi/kabupaten bertingkat; blok wilayah disembunyikan bila negara bukan Indonesia.

### 5. Masuk — `/masuk` → `game/login.php`

* Panel perkamen: nama pengguna, kata sandi (dengan tombol lihat/sembunyikan dan peringatan Caps Lock), pilihan fase bila `allow_phase_choice = 1`.
* Tombol **Masuk**. Tautan kecil "Belum punya akun? Daftar".
* Galat: satu pesan netral `Auth.loginFailed`, atau `Auth.locked` dengan sisa menit.
* Teks bantuan: "Lupa kata sandi? Minta gurumu mengatur ulang."

### 5a. Ganti Sandi — `/ganti-sandi` → `game/change-password.php`

* Pesan `Auth.mustChange` bila datang dari reset guru.
* Kolom sandi saat ini (sandi sementara dari guru), lalu `components/password-field` untuk sandi baru.
* Setelah berhasil: toast "Kata sandi barumu sudah tersimpan." lalu ke `/peta`.

### 6. Intro — `/intro` → `game/intro.php`

* Slide cerita pembuka dari `dialogues` context `intro`, satu per layar.
* Gambar Jaka intro, judul, paragraf, `components/audio-player`.
* Tombol Kembali / Lanjut, indikator titik halaman.
* Slide terakhir → `/peta`.

### 7. Peta Kedu — `/peta` → `game/map-kedu.php`

* Gambar peta Kedu dengan 3 titik pada posisi `levels.map_x` / `map_y`.
* Tiap titik: ikon status (lampu menyala = terbuka, gembok = terkunci, bintang = tuntas), nama wilayah, urutan, dan progres `n/5`.
* Jaka berdiri di sisi kiri peta dengan balon narasi.
* Klik titik wilayah yang **baru terbuka** (status `open`: terbuka, belum ada tantangan yang selesai) → **selalu** `/dialog/{code}` lebih dulu; wilayah yang sedang dijelajahi atau sudah tuntas → langsung `/wilayah/{code}`. Tujuan ini dihitung satu kali di `GameProgress::regionEntryPath()` dan dikirim sebagai `entry` pada setiap baris `levelOverview()`, sehingga peta, layar selesai, dan API memakai aturan yang sama.
* Klik titik terkunci → toast "Selesaikan wilayah sebelumnya dulu."
* Tombol Pustaka melayang di pojok kiri bawah.

### 8. Dialog — `/dialog/{code}` → `game/dialogue.php`

* Latar `bg-dialog-{level}`.
* Dua karakter berhadapan: yang berbicara maju dan terang, yang mendengar mundur dan meredup.
* Kotak teks dengan nama tokoh, teks dialog, dan audio player.
* Tombol Lanjut; dialog terakhir → `/wilayah/{code}`.
* State: `data-index` menunjuk baris dialog ke-n.

### 9. Peta Wilayah — `/wilayah/{code}` → `game/map-level.php`

* Gambar peta wilayah dengan 5 pos pada `challenge_nodes.map_x/map_y`.
* Tiap pos: nomor (atau ✓ bila selesai, 🔒 bila terkunci), nama jenis tantangan, dan bintang yang sudah diperoleh.
* Pos selesai → `/hasil/{code}/{seq}`; pos terbuka → `/misi/{code}/{seq}`.
* Teks bawah: "3 dari 5 tantangan selesai".
* Tombol Pustaka wilayah dan tombol kembali ke peta Kedu.

### 10. Kartu Misi — `/misi/{code}/{seq}` → `game/mission-brief.php`

* Panel perkamen di tengah: eyebrow "Tantangan 2 dari 5 · Temanggung", judul node, deskripsi, audio player.
* Tombol besar **Mulai tantangan**.

### 11. Layar Tantangan — `/tantangan/{code}/{seq}` → `game/challenge/{engine}.php`

Kerangka bersama (`game/challenge/_frame.php` di-include tiap engine):

```php
<div class="challenge">
  <div class="challenge-character"><?= $this->include('components/character') ?></div>
  <div class="panel panel-carved arena-panel">
    <div class="challenge-head">
      <div class="badge-pos">…</div>
      <div class="challenge-info">
        <div class="eyebrow"><?= esc($levelName) ?> · <?= esc(lang('Game.challengeOf', [$seq, 5])) ?> · <?= esc($engineName) ?></div>
        <h3 class="title"><?= esc($node->text('title')) ?></h3>
      </div>
      <span class="chip"><span class="chip-icon">⏱</span> <b id="timer">0:00</b></span>
      <span class="chip"><span class="chip-icon ok">✓</span> <b id="count-correct">0</b>
            <span class="chip-icon bad">✗</span> <b id="count-wrong">0</b></span>
      <button class="chip chip-btn" id="btn-hint" hidden>💡 <?= esc(lang('Game.hint')) ?></button>
    </div>
    <div class="arena">
      <p class="instruction"><?= esc($node->text('instruction')) ?></p>
      <?= $this->renderSection('arena') ?>
    </div>
  </div>
</div>
<script type="application/json" id="challenge-data"><?= $challengeJson ?></script>
```

`$challengeJson` adalah payload dari `ChallengeService::openNode()` — **tanpa kunci jawaban**. Di implementasi, `_frame.php` meng-encode `$payload` sendiri (`JSON_HEX_*`), dan tombol 💡 dirender tersembunyi dengan `data-hints` = `hints_count`; daftar id petunjuk ada di `payload.hints` (tanpa teks — teks baru dikirim `POST /api/attempts/{id}/hints`).

Markup arena per engine:

**`puzzle.php`**

```html
<div id="puzzle-wrap">
  <div class="puzzle-board" id="puzzle-board">
    <!-- 9 <button class="piece" data-slot="0..8"> -->
  </div>
  <p class="hint-text">Susunan yang benar adalah 1 sampai 9 dari kiri atas ke kanan bawah.</p>
  <button class="btn btn-primary" id="btn-check">Periksa gambar</button>
</div>
```

Mode `ordering` (Wonosobo node 1) memakai markup berbeda di file yang sama: daftar `<li class="order-card" draggable="true">` yang dapat ditukar urutannya, bukan grid.

**`rumpang.php`**

```html
<div class="rumpang">
  <figure class="rumpang-media">…</figure>
  <div class="sentences">
    <!-- tiap kalimat: teks + <button class="blank" data-item="{id}">?</button> -->
  </div>
  <div class="word-bank" id="word-bank" <?= $useWordBank ? '' : 'hidden' ?>>
    <!-- <button class="word" data-word-id="k0"> -->
  </div>
  <div class="free-inputs" <?= $useWordBank ? 'hidden' : '' ?>>
    <!-- <input class="blank-input" data-item="{id}"> bila tanpa bank kata -->
  </div>
  <button class="btn btn-primary" id="btn-check">Periksa jawaban</button>
</div>
```

**`boleh.php`** — kartu pernyataan dengan tombol penilaian

```html
<!-- Teks bacaan bersama (Temanggung node 3), dirender sekali per passage -->
<section class="passage" data-passage="{id}">
  <h4 class="passage-title">Teks A</h4>
  <div class="passage-body">…</div>
</section>

<div class="card-grid">
  <div class="verdict-card" data-item="{id}">
    <div class="card-image">…</div>

    <!-- Wonosobo node 3: dua sumber berdampingan -->
    <div class="sources">
      <blockquote class="source source-official"><cite>Sumber A · situs resmi</cite>…</blockquote>
      <blockquote class="source source-anonymous"><cite>Sumber B · akun anonim</cite>…</blockquote>
    </div>

    <p class="card-text">…pernyataan…</p>
    <div class="card-choices" role="radiogroup">
      <!-- dirender dari verdict_options node -->
      <button class="choice" role="radio" data-verdict="benar">Benar</button>
      <button class="choice" role="radio" data-verdict="salah">Salah</button>
      <button class="choice" role="radio" data-verdict="pendapat">Pendapat</button>  <!-- hanya Magelang 3 -->
    </div>
    <!-- varian beralasan (Magelang 3) -->
    <textarea class="card-reason" data-item="{id}" rows="2"
              placeholder="Tulis alasanmu"></textarea>
  </div>
</div>
<button class="btn btn-primary" id="btn-check">Periksa jawaban</button>
```

Label tombol dari `lang('Game.verdictBenar')`, `verdictSalah`, `verdictPendapat` (EN: True, False, Opinion). Pada Magelang 3 tampilkan kotak kecil di atas kartu: "**Fakta** bisa dibuktikan. **Pendapat** adalah perasaan atau selera seseorang." Sumber berlabel `official` diberi ikon gedung, `anonymous` diberi ikon tanda tanya — tanpa menyebut mana yang benar.

**`pilihan.php`**

```html
<div class="steps" id="steps"><!-- satu <span> per soal --></div>
<div class="source-text" id="source-text" hidden></div>
<p class="question" id="question"></p>
<div class="option-grid" id="option-grid">
  <!-- <button class="option" data-option="key"><img><span class="label"></span></button> -->
</div>
```

Satu soal per layar, maju otomatis setelah dijawab. Tidak ada tombol Periksa karena `allow_retry = false`.

**`cari.php`**

```html
<div class="hunt">
  <div class="panel hunt-clue">
    <div class="eyebrow">Petunjuk <b id="clue-index">1</b> dari <span id="clue-total">4</span></div>
    <p class="clue-text" id="clue-text"></p>
  </div>
  <div class="hunt-scene" id="hunt-scene">
    <img class="scene-bg" src="…" alt="Adegan pencarian">
    <!-- tiap objek: <button class="object" data-item="{id}"
                       style="left:18%;top:62%;width:13%"> -->
  </div>
  <ol class="hunt-list" id="hunt-list"><!-- daftar target --></ol>
</div>
```

Objek jebakan dirender sama persis dengan objek asli — tidak ada penanda visual apa pun yang membedakannya, karena justru itulah yang diuji.

### 12. Selesai — `/selesai/{attemptId}` → `game/challenge-finished.php`

* Orb cahaya, bintang 0–3, judul "Tantangan Selesai!".
* Tiga statistik: Waktu, Tepat sejak awal (%), Skor.
* Catatan kecil bila lebih dari satu pemeriksaan.
* Bila wilayah tuntas: panel Mbah Kedu bangga + "Wilayah berikutnya kini terbuka", dan tombol utama "Lanjut ke {wilayah berikutnya}" yang menuju `entry` wilayah itu — untuk wilayah yang baru terbuka berarti dialog pembukanya.
* Bila 15 node tuntas: tombol **Balai Refleksi**.
* Jaka senang, konfeti.

### 13. Hasil Node — `/hasil/{code}/{seq}` → `game/challenge-result.php`

Statistik terbaik + tabel seluruh percobaan (waktu, durasi, tepat sejak awal, pemeriksaan, skor, bintang). Tombol Ulangi. Catatan: "Mengulang tidak menghapus catatan lama, setiap percobaan tetap tersimpan sebagai data penelitian."

### 14. Pustaka Kedu — `/pustaka/{code}` → `game/library.php`

* Tata letak buku dua halaman: kiri galeri media (berapa pun gambar/video per halaman, dari `library_media`), kanan judul + teks.
* Gambar dapat diklik untuk diperbesar (`<dialog>`, `game/library.js`). Video YouTube/Vimeo/Drive tampil sebagai kartu **Putar**; iframe baru dipasang setelah tombol ditekan dan dilepas lagi saat halaman berganti. Tanpa JavaScript, kartu itu tautan ke halaman aslinya.
* Keterangan gambar dwibahasa di bawah media; media pihak lain menampilkan kredit yang menaut ke sumbernya.
* Teks memakai `rich_text()`: paragraf, subjudul `h3`, daftar, kotak "Tahukah kamu?" (`aside.book-fact`), catatan sumber (`p.book-source`), tebal, miring.
* Navigasi halaman kiri/kanan + nomor halaman.
* Media yang berkasnya tidak ada disembunyikan, bukan menampilkan kotak rusak. Gambar tautan yang gagal dimuat (mis. kelas tanpa internet) juga disembunyikan.
* Tombol tutup kembali ke layar sebelumnya.

### 15. Profil — `/profil` → `game/profile.php`

Avatar, nama, nama pengguna, kode peserta (kecil, dengan keterangan "kode penelitianmu"), tombol **Ganti kata sandi**, lalu grid data: umur, jenis kelamin, sekolah, serpihan, rata-rata tepat sejak awal, total waktu, total skor, tantangan selesai. Lencana per wilayah. Tabel riwayat attempt.

### 16. Balai Refleksi — `/refleksi` → `game/reflection.php`

* Panel Mbah Kedu + judul "Perjalananmu Sudah Lengkap".
* Ringkasan: tepat sejak awal, tantangan selesai, total waktu belajar, soal benar.
* Bar ketepatan per wilayah dan per jenis tantangan.
* Catatan "paling sering terlewat".
* Formulir kritik & saran: bintang 1–5 + empat textarea (paling disukai, paling sulit, hal baru, saran). Minimal dua textarea terisi.

---

## Halaman Admin

### Sidebar

| Menu | URL | Role |
|---|---|---|
| Beranda | `/admin/dashboard` | guru, admin |
| Peserta | `/admin/peserta` | guru, admin |
| Sesi | `/admin/sesi` | guru, admin |
| Analitik ▸ Level | `/admin/analitik/level` | guru, admin |
| Analitik ▸ Tantangan | `/admin/analitik/node` | guru, admin |
| Analitik ▸ Butir soal | `/admin/analitik/butir` | guru, admin |
| Analitik ▸ Indikator | `/admin/analitik/indikator` | guru, admin |
| Analitik ▸ Pretest/Posttest | `/admin/analitik/prepost` | guru, admin |
| Kritik & saran | `/admin/masukan` | guru, admin |
| Ekspor | `/admin/ekspor` | guru, admin |
| Konten | `/admin/konten` | admin |
| Konten ▸ Impor bank soal | `/admin/konten/impor-bank` | admin |
| Media & audio | `/admin/media` | admin |
| Studi & rilis | `/admin/studi` | admin |
| Tata kelola | `/admin/tata-kelola` | admin |
| Akun staf | `/admin/staf` | admin |

Menu yang tidak berhak diakses **tidak dirender** untuk guru. **Ubah sandi** (`/admin/akun/sandi`, guru & admin) tidak ada di sidebar; tautannya ada di kepala panel, di samping Keluar.

### Isi tiap halaman

**`dashboard.php`** — filter bar; 9 KPI card: jumlah peserta, sesi, tingkat penyelesaian, rata-rata skor, rata-rata tepat sejak awal, rata-rata durasi, pemakaian petunjuk, pemakaian audio, **% siswa yang langsung membuat sandi kuat** pada percobaan pertama. Lalu 4 chart: garis pretest→posttest, batang skor per wilayah, heatmap kesulitan node, sebaran umur. Di bawahnya: 5 node tersulit dan 10 sesi terakhir.

**`participants/index.php`** — tabel: kode, nama pengguna, nama, umur, jenis kelamin, kelas, sekolah, provinsi, jumlah sesi, serpihan, rata-rata tepat sejak awal, terakhir aktif. Pencarian + filter + pagination. Untuk guru, kolom sekolah terkunci pada sekolahnya.

**`participants/show.php`** — profil peserta: demographic, nama pengguna, consent (versi + waktu + orang tua), **literasi keamanan digital** (syarat sandi terpenuhi pada percobaan pertama x/5, jumlah penolakan sandi lemah, terakhir ganti sandi), daftar sesi per fase, skor per wilayah, radar/bar indikator, node tersulit, dan tautan ke linimasa event tiap sesi. Tombol **Reset kata sandi** dengan kotak konfirmasi ketik `RESET`.

**`participants/reset-result.php`** — menampilkan sandi sementara **satu kali** dalam huruf besar ber-font mono, tombol salin, dan pesan "Berikan kepada siswa. Siswa wajib membuat sandi baru saat masuk." Halaman ini tidak boleh di-cache (`Cache-Control: no-store`).

**`sessions/index.php`** — tabel sesi: kode, peserta, studi, fase, bahasa, status, mulai, durasi, serpihan, skor, perangkat.

**`sessions/show.php`** — ringkasan sesi + tabel attempt per node: jenis, status, item, tepat sejak awal, akhir, pemeriksaan, petunjuk, perubahan jawaban, durasi, skor, bintang. Setiap baris dapat dibuka ke daftar `item_responses`.

**`sessions/timeline.php`** — linimasa `game_event_logs`: waktu (presisi milidetik), jenis event, node/item, dan payload ringkas. Filter jenis event. Ini jalur drilldown terdalam: Ringkasan → Fase → Peserta → Level → Node → Item → Event.

**`analytics/levels.php`** — per wilayah: peserta, penyelesaian, rata-rata skor, tepat sejak awal, durasi, bintang. Chart batang.

**`analytics/nodes.php`** — heatmap 3×5 kesulitan node. Tabel: node, jenis, indikator, tepat sejak awal, akhir, pemeriksaan, petunjuk, durasi median, tingkat ditinggalkan, indeks kesulitan. Diurutkan dari tersulit.

**`analytics/node.php`** — drilldown satu node: sebaran skor, scatter durasi vs ketepatan, daftar item dengan p dan D, tautan ke event.

**`analytics/items.php`** — analisis butir. Kolom: soal, indikator, jenis, wilayah, kunci, muncul, benar, **kesukaran p** dengan tafsir (sukar/sedang/mudah), **daya beda D** dengan tafsir (buruk/lemah/cukup/baik), rata-rata detik, jawaban salah tersering. Baris merah bila benar < 50%, hijau bila > 85%. Disertai kotak penjelasan cara membaca p dan D, karena angka ini yang biasanya diminta penguji.

**`analytics/indicators.php`** — matriks penguasaan indikator × wilayah, dengan jumlah bukti (bukan label biner lulus/tidak).

**`analytics/prepost.php`** — tabel dan chart garis perbandingan pretest vs posttest per peserta dan per kelompok. Pasangan sesi yang `release_id` atau `scoring_version`-nya tidak kompatibel ditampilkan di bagian terpisah dengan penjelasan, bukan ikut dihitung.

**`feedback/index.php`** — kartu masukan: bintang, empat jawaban, kode peserta, fase, waktu. Ringkasan sebaran bintang.

**`content/index.php`** — 3 kartu wilayah, tiap kartu memuat 5 node dengan jenis, jumlah item di bank, dan status aktif. Tombol "Verifikasi konten".

**`content/node.php`** — form node (judul, instruksi, deskripsi ID/EN berdampingan, indikator, profil skoring, config dengan field terpandu: `items_per_round`, `verdict_options`, daftar pengecoh rumpang ID/EN) + tabel item bank dengan kolom `review_status` berwarna (draft abu, perlu verifikasi kuning, terverifikasi hijau) + tombol tambah item.

**`content/passages.php`** — daftar teks bacaan satu level (kunci, judul, isi ID/EN berdampingan, gambar, sumber) + jumlah item yang merujuknya.

**`content/import.php`** — unggah workbook bank soal → tombol **Pratinjau** → tabel ringkasan 15 node (passage, item, opsi, hint, bank vs minimum) → daftar galat per sheet + nomor baris (merah) → daftar peringatan (kuning, mis. item `needs_verification`) → tombol **Impor** hanya aktif bila tanpa galat. Tautan unduh templat. Riwayat impor dari `audit_logs`.

**`content/items.php`** — form item yang **berubah sesuai `interaction_type`**: untuk `single_choice` muncul editor opsi A–D dengan radio kunci dan unggah gambar; untuk `fill_blank_*` muncul kolom kalimat dengan penanda `___` dan kolom jawaban; untuk `verdict_card`/`verdict_reason` muncul teks pernyataan + pilihan kunci Benar/Salah/Pendapat (sesuai `verdict_options` node) + kolom contoh alasan + editor dua sumber; untuk `ordering` muncul daftar potongan kalimat yang dapat diurutkan; setiap item punya pemilih teks bacaan, `review_status`, `review_note`, dan `reference_source`; untuk `find_object` muncul pemilih koordinat di atas gambar adegan dengan penanda yang dapat digeser.

**`content/verify.php`** — daftar temuan: node kurang/lebih, bank item kurang dari `items_per_round`, item tanpa kunci, `single_choice` tanpa opsi benar, kunci verdict di luar `verdict_options`, pengecoh rumpang kurang, jawaban kembar antar node, media hilang, dan item `needs_verification` yang masih aktif.

**`media/index.php`** — tabel media: kunci aset, jenis, path, ukuran wajib vs ukuran sebenarnya, status ada/hilang, pratinjau. Unggah per baris; nama berkas diperbaiki otomatis menjadi nama resmi aset.

**`media/audio.php`** — tabel audio: konteks, karakter, bahasa, durasi, metode produksi, transkrip, status persetujuan, tombol setujui. Pemutar pratinjau.

**`study/index.php`**, **`releases.php`**, **`scoring.php`** — form studi (retensi, mode unlock, mode pemilihan item, wajib consent), daftar rilis dengan tombol aktifkan, daftar profil skoring dengan bobot dan ambang bintang.

**`export/index.php`** — form filter + pilihan sheet + toggle anonim (terkunci menyala untuk guru) + daftar export sebelumnya dengan status, jumlah baris, SHA-256, dan masa berlaku.

**`governance/index.php`** — form cakupan penghapusan → tombol Pratinjau → tabel jumlah terdampak per tabel → kotak konfirmasi ketik `HAPUS` → tombol Jalankan. Di bawahnya daftar request sebelumnya.

**`governance/audit.php`** — tabel `audit_logs`.

**`staff/index.php`** — daftar akun, tambah, ubah role/sekolah, reset kata sandi, nonaktifkan. Lencana *wajib ganti sandi* muncul pada akun yang sandinya masih sementara (akun baru atau hasil reset); kartu sandi sementara menjelaskan bahwa pemilik akun wajib menggantinya saat masuk.

**`account/password.php`** — ganti sandi sendiri: sandi saat ini, sandi baru (12–72 karakter), ulangi. Bila `$mustChange` (sandi sementara dari admin), pemberitahuan `alert-info` menjelaskan bahwa halaman panel lain baru terbuka setelah sandi disimpan. Kolom sandi tidak pernah diisi ulang; kolom nama pengguna tersembunyi tanpa `name` membantu pengelola sandi browser.

---

## CSS

### Arsitektur

Enam berkas, dimuat berurutan. Tidak ada framework CSS, tidak ada build step.

```text
public/assets/css/
├── tokens.css        custom property: warna, tipografi, spasi, radius, bayangan
├── base.css          reset, elemen dasar, tipografi, fokus
├── layout.css        hud, app, scene, sidebar admin, grid halaman
├── components.css    tombol, panel, kartu, tabel, modal, toast, chip, form
├── game.css          layar permainan dan lima arena tantangan
└── admin.css         panel, tabel data, filter bar, chart, form konten
```

Halaman game memuat: `tokens, base, layout, components, game`.
Halaman admin memuat: `tokens, base, layout, components, admin`.

### `tokens.css`

```css
:root {
  /* Warna */
  --navy-900:#0B1320;  --navy-800:#121C2E;  --navy-700:#1B2740;
  --gold-500:#DFC087;  --gold-600:#C7A265;  --gold-300:#F0DFB8;
  --parchment:#F4E7CB; --brown-700:#4A3218; --brown-500:#8A6A32;
  --ok:#8FD14F;        --bad:#D9704A;       --warn:#F0A868;
  --ink:#EAF0FA;       --ink-dim:rgba(234,240,250,.72);

  /* Tipografi */
  --font-display:'Cinzel', Georgia, 'Times New Roman', serif;
  --font-body:'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
  --font-mono:'IBM Plex Mono', ui-monospace, 'SF Mono', Consolas, monospace;

  --step--1:clamp(.82rem, .78rem + .2vw, .92rem);
  --step-0: clamp(.95rem, .9rem + .25vw, 1.05rem);
  --step-1: clamp(1.15rem, 1.05rem + .5vw, 1.4rem);
  --step-2: clamp(1.5rem, 1.3rem + 1vw, 2.1rem);
  --step-3: clamp(2rem, 1.6rem + 2vw, 3rem);

  /* Ruang & bentuk */
  --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px;
  --sp-5:24px; --sp-6:32px; --sp-7:48px;
  --radius:12px; --radius-lg:20px;
  --shadow-1:0 2px 8px rgba(0,0,0,.25);
  --shadow-2:0 12px 32px rgba(0,0,0,.4);

  /* Layout */
  --hud-h:72px; --nav-h:76px; --sidebar-w:248px;
  --content-max:1180px;
}
```

Font dimuat **lokal** dari `public/assets/fonts/`, bukan dari Google Fonts CDN. Alasan: aplikasi dipakai di sekolah yang koneksinya tidak dapat diandalkan, dan memuat font dari pihak ketiga mengirimkan permintaan berisi alamat IP anak ke luar. Sediakan `@font-face` dengan `font-display: swap` dan fallback sistem yang wajar.

### Aturan penulisan CSS

* Kelas memakai bahasa Inggris, satu kata atau dipisah tanda hubung: `.challenge-head`, `.option-grid`.
* State memakai awalan `is-` atau `has-`: `.is-active`, `.is-correct`, `.is-locked`, `.has-error`.
* Tidak ada `!important` kecuali pada utilitas `[hidden]`.
* Tidak ada selector lebih dalam dari tiga tingkat.
* Layout memakai Grid dan Flexbox; tidak ada float.
* Semua ukuran teks lewat token `--step-*`.

### Responsif

| Breakpoint | Perilaku |
|---|---|
| `< 640px` | HUD menjadi dua baris; grid opsi 1 kolom; kartu perilaku 1 kolom; sidebar admin menjadi drawer; tabel admin menggulir horizontal di dalam `.table-wrap` |
| `640–1024px` | grid opsi 2 kolom; puzzle maksimum 420px; sidebar admin menyempit menjadi ikon |
| `> 1024px` | tata letak penuh; arena tantangan maksimum 900px; konten admin maksimum `--content-max` |

Papan tulis interaktif dan proyektor kelas sering berjalan pada 1366×768 — pastikan layar tantangan muat tanpa menggulir pada tinggi 768px.

### Gaya komponen kata sandi (`components.css`)

```css
.pw-meter { height: 8px; border-radius: 4px; background: rgba(255,255,255,.15); overflow: hidden; }
.pw-meter i { display:block; height:100%; width:0; transition: width .2s; }
.pw-meter i[data-level="weak"]   { width:33%;  background: var(--bad); }
.pw-meter i[data-level="medium"] { width:66%;  background: var(--warn); }
.pw-meter i[data-level="strong"] { width:100%; background: var(--ok); }
.pw-rules li::before { content: "✗"; margin-right: var(--sp-2); color: var(--ink-dim); }
.pw-rules li.is-met { color: var(--ok); }
.pw-rules li.is-met::before { content: "✓"; color: var(--ok); }
```

Teks aturan tetap berukuran `--step-0` — ini materi yang harus dibaca anak, bukan catatan kecil.

### Aksesibilitas

* Kontras teks minimal 4.5:1 terhadap latarnya. Latar bergambar selalu diberi lapisan gelap `rgba(11,19,32,.55)` di belakang teks.
* Fokus keyboard selalu terlihat: `outline: 3px solid var(--gold-500); outline-offset: 2px`. Jangan pernah `outline: none` tanpa pengganti.
* Target sentuh minimal 44×44px — anak SD memakai tablet dan jari mereka kecil tetapi kurang presisi.
* `prefers-reduced-motion: reduce` → matikan konfeti, crossfade, dan animasi karakter; ganti dengan pergantian langsung.
* Setiap gambar informatif punya `alt` bermakna; gambar dekoratif memakai `alt=""`.
* Warna tidak pernah menjadi satu-satunya penanda benar/salah: selalu disertai ikon ✓/✗ dan teks.
* Elemen interaktif memakai `<button>` dan `<a>`, bukan `<div>` dengan handler klik.

### Kondisi aset hilang

Aplikasi harus tetap terpakai saat berkas gambar belum diunggah. Aturan:

* `media_src()` mengembalikan `assets/ui/placeholder.svg` bila aset tidak aktif/ada.
* Tombol yang punya versi gambar (`btn-*.png`) jatuh ke gaya CSS emas-navy bila gambarnya tidak ada. Ini default: gaya CSS dipakai kecuali admin memilih sebaliknya.
* Gambar dengan tulisan punya varian bahasa: `media_assets.locale`. Bila varian `en` tidak ada, varian `id` dipakai.

---

## External CSS/JS Library

Hanya dua library frontend, keduanya di-host sendiri di `public/assets/vendor/`.

| Library | Versi | Tujuan | Halaman | Sumber |
|---|---|---|---|---|
| **Apache ECharts** | 6.x, build `echarts.min.js` | seluruh chart admin: garis, batang, heatmap, scatter, matriks | halaman admin yang memuat chart | unduh dari rilis resmi, simpan ke repo |
| **Howler.js** | 2.2.x | efek suara dan musik latar; menangani kebijakan autoplay browser secara konsisten | halaman game | idem |

**Chart.js tidak dipakai.** Memakai dua library chart berarti dua API, dua tema, dan dua set bug untuk pekerjaan yang sama. ECharts sendiri sudah menangani seluruh jenis visual yang dibutuhkan, termasuk heatmap dan matriks yang tidak dimiliki Chart.js secara bawaan.

Narasi audio (dialog Jaka dan Mbah Kedu) memakai elemen `<audio>` bawaan, bukan Howler, karena butuh kontrol posisi dan telemetry per-aset yang lebih mudah dibaca langsung dari elemen.

Tidak ada CDN. Semua berkas vendor ada di repositori dan dimuat dari domain sendiri.

> **Status:** sudah ada di repo sejak tahap ini — ECharts 6.1.0 dan Howler.js 2.2.4, lengkap dengan lisensinya. `layouts/admin.php` memuat ECharts hanya bila halaman mengisi section `charts`; `layouts/game.php` memuat Howler. Lihat [02_PROJECT_FOUNDATION.md → *Status `public/assets/vendor/`*](02_PROJECT_FOUNDATION.md) dan [`public/assets/vendor/README.md`](../public/assets/vendor/README.md).

---

## Aturan Sistem

1. Semua output dinamis melewati `esc()`. Data untuk JavaScript dikirim lewat `<script type="application/json">`, tidak pernah diinterpolasi ke dalam string JS.
2. Kunci jawaban tidak pernah muncul di HTML, atribut data, maupun payload JSON halaman.
3. Teks UI memakai `lang()`. Teks konten memakai `$entity->text('field')` yang otomatis jatuh ke bahasa Indonesia bila versi Inggris kosong.
4. Jumlah serpihan dan jumlah node selalu dihitung dari database.
5. Panel admin satu bahasa (Indonesia). Hanya area game yang dwibahasa.
6. Setiap tabel admin punya kondisi kosong yang menjelaskan apa yang harus dilakukan, bukan tabel kosong tanpa keterangan.
7. Filter analitik memakai `method="get"` agar tersimpan di URL.
8. Layar tantangan tidak menyediakan jalan keluar tak sengaja: satu tombol keluar, dengan konfirmasi.
9. Halaman tidak boleh bergantung pada JavaScript untuk menampilkan informasi dasar. Navigasi dan isi tetap terbaca meski modul JS gagal dimuat.
10. Aset vendor di-host sendiri; tidak ada permintaan ke domain pihak ketiga dari halaman yang diakses anak.
11. Kolom kata sandi tidak pernah diisi ulang oleh server, dan `password_hash` tidak pernah dikirim ke view mana pun.
12. Nama pengguna siswa tidak tampil di export anonim maupun di layar publik (papan skor, dsb.); di HUD hanya terlihat oleh siswa itu sendiri.

---

## Catatan Implementasi Tahap 5

Keputusan dan temuan selama tahap ini, supaya tahap 6–7 tidak mengulang penelusurannya.

### Merender komponen: `component()`, bukan `$this->include()`

* `$this->include($view, $options)` milik CodeIgniter menerima **options** (cache) sebagai argumen kedua, bukan data — array yang dioper ke sana diabaikan diam-diam. Beberapa view tahap 4 memakai pola itu sehingga datanya tidak pernah sampai.
* Helper `component($name, $data)` (`gelita_helper.php`) merender dengan renderer **terpisah**: komponen hanya melihat `$data` yang dioper. Data halaman tidak ikut, karena variabel halaman bernama sama pernah menimpa variabel opsional komponen (contoh nyata: `$actions` berisi daftar aksi audit menimpa `$actions` HTML tombol di `partials/admin-head` → galat 500).
* Satu pengecualian yang disengaja: kunci konteks panel `filters` dan `filterOptions` diwariskan bila tidak dioper ulang, agar `admin-filter-bar` dan `admin-chart` di halaman ber-filter selalu memakai filter yang sama.
* `$this->include()` tetap dipakai untuk bagian yang memang berbagi data halaman tanpa data tambahan (HUD, sidebar, `partials/flash`).
* `tests/unit/ViewHelperTest.php` mengunci perilaku ini.

### Pola tanpa JavaScript

Semua informasi dasar terbaca dan semua form dapat dikirim tanpa JS; tahap 6 hanya menambah perilaku.

| Kebutuhan | Teknik |
|---|---|
| Slide intro, dialog, dan buku Pustaka | anchor + CSS `:target`; slide aktif disembunyikan yang lain lewat `:has(.slide:target)` |
| Konfirmasi keluar tantangan, hapus butir, nonaktifkan akun, aktifkan rilis | `<details class="confirm">`; di dalam sel tabel memakai `.confirm-inline` agar tidak terpotong `overflow` |
| Panduan bentuk JSON per `interaction_type`, kolom sekolah hanya untuk guru, pembicara dialog | CSS `:has()` pada pilihan `<select>`/radio |
| Tombol kirim persetujuan redup sampai semua wajib tercentang | `form:invalid` |
| Transkrip audio, detail lentera, metadata audit | `<details>` |
| Toast | memudar otomatis lewat animasi CSS |
| Chart admin | `admin-chart` berisi visual cadangan dari server (bar-list, heatmap, scatter) yang diganti ECharts oleh `admin/charts.js` (tahap 6); chart tanpa endpoint membawa datanya lewat parameter `data` |

### Penyesuaian controller yang dibutuhkan view

View tahap ini membutuhkan data yang belum dikirim controller tahap 4. Perubahannya menambah data, bukan mengubah perilaku route:

* **Game:** HUD memakai `Participant::toSafeArray()` + peta lentera (`GameProgress::lanternMap()`); `nodeOverview` menyertakan `variant`; registrasi mengirim daftar nama sekolah (datalist) dan sapaan selamat datang lewat flash `welcome`; ganti sandi mengirim `username`; refleksi mengirim ringkasan perjalanan (`journey`).
* **Admin:** `BaseAdminController::panel()` menambah `activeStudy` dan `filterOptions` (closure, dievaluasi hanya bila dirender). Controller peserta, sesi, dashboard, analitik, masukan, dan staf mengirim baris yang sudah aman untuk view — `password_hash` tidak pernah ikut (`toSafeArray()`).
* **Konten:** form node kini menyimpan `indicator_id`, `scoring_profile_id`, dan field konfigurasi terpandu `cfg[...]` yang menimpa kunci yang sama di JSON mentah tanpa menghapus kunci lain. `node()` mengelompokkan opsi per butir (`$options[$itemId]`); sebelumnya daftar datar sehingga editor opsi selalu kosong dan menyimpan ulang ditolak "tepat satu opsi benar".
* **Perbaikan bug yang ditemukan saat merender halaman nyata:** `StudyController::releases()` mengurutkan `game_releases` menurut `created_at` yang tidak ada di tabel itu (500); `saveLibrary()` menulis `NULL` ke `library_pages.body_en` yang `NOT NULL` (500) — kini `''` dan permainan jatuh ke teks Indonesia; reset sandi staf dikirim dengan `Cache-Control: no-store` seperti reset sandi siswa.
* Form teks bacaan dan dialog tahap 4 tidak mengirim `title_en`, `reference_source`, atau judul slide sehingga nilai itu terhapus setiap kali disimpan; form baru mengirim semua kolom yang dikelola controller.

### Keputusan yang sudah diambil

* **Arena Cari objek — payload tanpa pembeda jebakan.** Payload `cari` tidak memakai `items`. `objects` memuat semua objek (target maupun jebakan) dengan bentuk identik `{ ref, x, y, w, media }`, diurutkan menurut posisi di layar; `ref` adalah token HMAC per attempt dari `encryption.key`. `clues` hanya berisi petunjuk target (`{ item_id, text }`). Server hanya menerima `answer.object` dan menerjemahkannya sendiri ke id butir — id mentah kiriman klien diabaikan, karena id butir target memang terlihat di `clues`. Jalur `/check` memakai aturan yang sama, dan `progress.total` tidak menghitung jebakan. Kontrak API lengkap di [06_JAVASCRIPT.md → *Fitur: Cari Objek Budaya*](06_JAVASCRIPT.md). Dikunci oleh `tests/unit/HuntPayloadTest.php`.
* **Dialog selalu muncul saat wilayah baru terbuka.** Ini aturan permanen: wilayah berstatus `open` selalu masuk lewat `/dialog/{code}`. Tautan peta memakai `entry`, dan layar selesai yang menuntaskan satu wilayah menawarkan "Lanjut ke {wilayah berikutnya}" menuju dialog pembuka wilayah itu. URL yang diketik langsung juga dijaga: `/wilayah/{code}`, `/misi/{code}/{n}`, dan `/tantangan/{code}/{n}` untuk wilayah yang baru terbuka dialihkan ke dialognya (`BaseGameController::dialogueGate()`) sampai dialog itu tampil. Tanda "sudah tampil" disimpan di sesi PHP per sesi permainan dan per wilayah, bukan di database, sehingga setelah keluar-masuk lagi dialog wilayah yang masih baru terbuka tampil kembali. Pustaka sengaja tidak dijaga. Dikunci oleh `tests/unit/RegionEntryTest.php` dan `tests/unit/DialogueGateTest.php`.
* **Registrasi memakai nama lengkap.** Label kolom `display_name` adalah "Nama lengkap" / "Full name". Teks persetujuan (`Game.consentBody`) kini menyebut seluruh data profil yang dicatat — termasuk nama lengkap — dan bahwa nama hanya dapat dilihat guru dan tim peneliti. Versi teks persetujuan naik menjadi `2` (`RegisterController::CONSENT_VERSION`); persetujuan yang sudah tersimpan tetap bertanda versi `1`.
* **Kolom sandi** sengaja tanpa atribut `minlength`: sandi lemah harus sampai ke server agar metrik `pw_weak_submit_count` tercatat.
* **Audit pra-tahap 6 (23 September 2026).** Penelusuran alur nyata di MariaDB menemukan beberapa cacat di lapisan server yang menopang view ini; semuanya diperbaiki tanpa mengubah markup:
  * attempt `cari` tidak dapat ditutup karena baris objek jebakan dihitung "belum dijawab" — kini petunjuk, progres, dan syarat `/complete` memakai satu aturan (`ChallengeService::expectsAnswer()`);
  * first-pass `cari` kini ditutup salah oleh klik pertama yang keliru, dan `allow_retry = false` (arena `pilihan`) ditegakkan server;
  * payload menambahkan `hints` (id petunjuk) sehingga tombol 💡 dapat dipakai tahap 6; `hints_count` kini menghitung petunjuk node dan petunjuk butir yang diminta dijawab;
  * Debug Toolbar development tidak lagi menyimpan kata sandi dari form registrasi, masuk, ganti sandi, dan login staf (`App\Filters\DebugToolbar`).
  Rinciannya di 03_MODEL_ENTITY.md dan 04_CONTROLLER_ROUTE.md.

### Aset

Selama berkas gambar belum diunggah, baris slot di `media_assets` berstatus nonaktif. `media_key_src()`/`media_first()` lalu mengembalikan `null`/string kosong dan view memakai penggantinya sendiri (gradien latar, monogram tokoh, logo teks) — tidak ada permintaan gambar yang berakhir 404. Halaman Media juga tidak meminta pratinjau untuk slot nonaktif.

---

## Dependency

Dari **02_PROJECT_FOUNDATION.md**: layout, helper `media_src()`/`audio_src()`/`tr()`, berkas bahasa, `Config\Gelita`.

Dari **03_MODEL_ENTITY.md**: Entity dengan trait `Bilingual`, `ContentRepository`, `AnalyticsService`.

Dari **04_CONTROLLER_ROUTE.md**: nama view yang dipanggil tiap controller, variabel yang dikirim, URL untuk `href` dan `action`.

---

## Hasil Akhir

Setelah tahap ini selesai:

* Seluruh 17 halaman game dan 34 view admin (33 dari tahap 5, ditambah ubah sandi staf) merender lengkap dengan data sungguhan dari database.
* Halaman registrasi menampilkan bagian "Akun rahasiamu" dengan daftar 5 syarat, meter kekuatan, dan tips; halaman masuk dan ganti sandi tersedia.
* Alur peserta dapat ditelusuri penuh dengan klik, dari welcome sampai Balai Refleksi, meski tombol Periksa belum berfungsi (menunggu tahap 6).
* Lima arena tantangan sudah memiliki markup dan gaya yang benar, tinggal diberi perilaku.
* Pergantian ID/EN mengubah seluruh teks UI dan konten, tanpa mengubah progres.
* Panel admin menampilkan tabel dan filter yang berfungsi; wadah chart sudah ada dan tinggal diisi tahap 6.
* Tampilan tetap rapi pada 1366×768, tablet, dan ponsel, serta tetap terpakai saat sebagian gambar belum diunggah.
