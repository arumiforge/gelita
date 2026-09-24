# 07_FEATURE_INTEGRATION.md — Integrasi Fitur

> **Revisi 2 (21 September 2026).** Alur registrasi dan login siswa ditulis ulang (nama pengguna + kata sandi kuat sebagai literasi keamanan digital); fitur baru: ganti sandi, reset sandi oleh guru, dan **impor workbook bank soal** beserta format sheet-nya (dipakai untuk memindahkan isi Dokumen Bank Soal GELITA ke web). Export dan dashboard menambahkan metrik literasi keamanan digital; pretest–posttest memakai `active_phase_code`.

---

## Tujuan

Menyatukan database, model, controller, view, dan JavaScript menjadi fitur aplikasi yang utuh. Dokumen ini menjelaskan alur setiap fitur utama dari ujung ke ujung, ditambah implementasi dua bagian yang baru muncul di tahap ini: **ExportService** (XLSX/PDF) dan **RetentionService** (penghapusan & retensi).

---

## Konteks

Pola alur yang berlaku untuk seluruh fitur:

```
User
 ↓
View (CodeIgniter)
 ↓
JavaScript
 ↓
Route
 ↓
Controller
 ↓
Validation
 ↓
Service
 ↓
Model
 ↓
Database
 ↓
Response
 ↓
View Update
```

Empat aturan yang tidak pernah dilanggar di seluruh fitur:

1. Skor dan status ditentukan server.
2. Operasi yang menyentuh beberapa tabel dibungkus satu transaction.
3. Setiap tindakan penting menulis raw event atau audit log.
4. Bahasa, fase penelitian, dan versi rilis tercatat pada data hasil.

---

## Yang Harus Dibuat pada Tahap Ini

| Komponen | Lokasi |
|---|---|
| `ExportService` | `app/Services/ExportService.php` |
| `ReportService` (PDF) | `app/Services/ReportService.php` |
| `RetentionService` | `app/Services/RetentionService.php` |
| `ExcelWriter` | `app/Libraries/ExcelWriter.php` |
| `ContentVerifier`, `MediaIntegrity` | `app/Libraries/` — aturan verifikasi konten dan pemeriksa media, dipakai panel dan command |
| Template PDF | `app/Views/pdf/report-study.php`, `report-participant.php` (+ `_style.php`, `_bars.php`) |
| Spark command | `app/Commands/ContentVerify.php`, `RetentionRun.php`, `ScoreRecompute.php`, `MediaScan.php`, `BankImport.php` |
| `ContentImportService` + `gelita:bank:import` | service pembaca workbook (FITUR 12a) **sudah ada sejak tahap 3** dan dipakai panel `/admin/konten/impor-bank` (pratinjau, impor, templat); tahap ini menyambungkan command CLI-nya |

Selebihnya tahap ini adalah **merangkai** yang sudah dibuat pada tahap 1–6. Status: **selesai** — keputusan implementasinya dicatat di [*Catatan Implementasi Tahap 7*](#catatan-implementasi-tahap-7).

---

## FITUR 1: Registrasi Siswa dengan Kata Sandi Kuat

Registrasi sekaligus materi literasi keamanan digital: anak belajar membuat kata sandi kuat, dan prosesnya tercatat sebagai data.

```text
 1. Siswa membuka / → "Mulai" → /mulai → "Saya baru" → /persetujuan
 2. Membaca teks persetujuan, mencentang persetujuan peserta dan orang tua/wali,
    mengisi nama wali, menekan Lanjut
 3. POST /persetujuan → RegisterController::storeConsent
      validasi: kedua centang wajib bila study.require_consent = 1
      simpan ke session + stempel waktu; belum menulis database
 4. Redirect /daftar → formulir tiga bagian:
      1. Tentang kamu  2. Sekolah dan daerah  3. Akun rahasiamu
 5. register.js memuat wilayah-id.json (provinsi → kabupaten bertingkat)
 6. Mengetik nama pengguna → GET /api/auth/username-available (debounce 500 ms)
      ✓ bisa dipakai / ✗ sudah dipakai, coba tambahkan angka
 7. Mengetik kata sandi → password-meter.js (lokal, tanpa request):
      5 syarat berubah ✗→✓ satu per satu, meter lemah/sedang/kuat,
      keterangan bila belum kuat, peringatan bila memuat nama pengguna,
      cek ulangan sandi, peringatan Caps Lock
 8. POST /daftar → RegisterController::store
 9. PasswordPolicy::check() dihitung lebih dulu:
      percobaan kirim pertama → session('reg_first_criteria') = jumlah syarat (0–5)
      sandi belum acceptable   → session('reg_weak_count') + 1
10. Validation seluruh field (termasuk strong_password[username], matches[password])
      gagal → kembali ke /daftar, field lain terisi old(),
              kolom sandi KOSONG, keterangan syarat yang belum terpenuhi
              → kembali ke langkah 7
11. Lolos → SessionService::registerAndStart() — SATU TRANSACTION:
      a. PasswordPolicy::check() diulang di service (pertahanan kedua)
      b. SchoolModel::findOrCreateByName() → school_id
      c. ParticipantModel::generateCode() → GLT-000123
      d. insert participants: username (huruf kecil), password_hash,
         snapshot sekolah/wilayah, pw_first_submit_criteria, pw_weak_submit_count,
         password_changed_at
      e. insert participant_consents (versi teks + stempel waktu + nama wali)
      f. release_id = game_releases aktif (dikunci untuk sesi ini)
      g. fase = study.allow_phase_choice ? pilihan siswa : study.active_phase_code
      h. insert game_sessions (session_code 32 hex, locale, device, ip_hash)
      i. insert session_progress (nol)
      j. EventService::record('session_started', {via:'register'})
12. Hapus reg_first_criteria & reg_weak_count dari session
13. session()->regenerate(true); set participant_id, game_session_id
14. Redirect /intro dengan kartu sambutan:
      "Ingat nama pengguna dan kata sandimu, ya. Jangan beri tahu teman."
15. View intro merender slide dari dialogues context 'intro', tanpa tombol
    "Lewati" (intro_seen_at masih kosong)
16. Slide terakhir → /intro/selesai → ParticipantModel::markIntroSeen()
    (isi intro_seen_at sekali), EventService::record('intro_completed', {first})
    → redirect /peta dengan flash curtain=map
```

Bila consent ditolak atau tidak lengkap, registrasi dibatalkan seluruhnya — tidak ada baris `participants` yang tertinggal. Kata sandi tidak pernah ditulis ke session, flash, `old()`, log, atau event; yang disimpan hanya jumlah syarat terpenuhi dan jumlah penolakan.

---

## FITUR 2: Masuk dan Melanjutkan Perjalanan

```text
 1. Siswa membuka / → "Mulai" → /mulai → "Saya sudah punya akun" → /masuk
    (komputer mana pun di kelas)
 2. Mengisi nama pengguna + kata sandi (+ fase bila allow_phase_choice = 1)
 3. POST /masuk → LoginController::login → SessionService::login()
 4. findByUsername (huruf kecil)
      tidak ada / sandi salah → registerFailedLogin (bila akun ada)
                               → pesan identik "Nama pengguna atau kata sandi salah."
      locked_until > now       → "Terlalu banyak percobaan. Tunggu N menit."
                                 (8 kali gagal → kunci 5 menit)
 5. Berhasil → clearFailedLogin (last_login_at = now)
 6. must_change_password = 1 → session diisi, redirect /ganti-sandi (FITUR 2a)
 7. fase = allow_phase_choice ? pilihan : study.active_phase_code
    cari game_sessions active/paused untuk (participant, study, fase)
      ada   → status 'active', EventService::record('session_resumed', {via:'login'})
      tidak → SessionService::startNewSession(), record('session_started', {via:'login'})
 8. session()->regenerate(true); set participant_id, game_session_id
 9. Redirect ke URL tujuan tersimpan atau /mulai → /gerbang
      intro_seen_at kosong → /intro (cerita pembuka wajib, tanpa "Lewati")
      selain itu           → pilihan "Lihat cerita pembuka" / "Langsung ke peta"
                             (/gerbang/peta → /peta + flash curtain=map)
10. Peta dirender dari session_progress: lentera, status level, dan status node
    langsung sesuai keadaan terakhir; attempt in_progress dilanjutkan saat node dibuka.
    /peta sendiri juga mengalihkan ke /intro selama intro_seen_at kosong
```

**Keluar** (`/keluar`): event `session_paused`, attempt `in_progress` tidak ditutup, `session()->destroy()`, kembali ke `/`. Komputer kelas yang ditinggal tanpa keluar tetap aman karena session berakhir sendiri setelah `session.expiration` (4 jam).

---

## FITUR 2a: Ganti Kata Sandi & Reset oleh Guru

```text
RESET OLEH GURU
 1. Siswa lupa sandi → memberi tahu guru
 2. Guru membuka /admin/peserta/{id} (hanya siswa di sekolahnya)
 3. Menekan "Reset kata sandi", mengetik RESET, kirim
 4. SessionService::resetPasswordByStaff():
      sandi sementara acak yang lolos kebijakan (mis. "Kedu-7342-Lentera")
      password_hash baru, must_change_password = 1,
      failed_login_count = 0, locked_until = NULL
      audit 'participant_password_reset' (tanpa sandi di metadata)
 5. /admin/peserta/{id}/reset-sandi menampilkan sandi sementara SATU KALI
    (no-store). Guru menyampaikannya langsung kepada siswa.

GANTI SANDI OLEH SISWA
 6. Siswa masuk dengan sandi sementara → dialihkan ke /ganti-sandi
    (semua route game lain ikut mengalihkan ke sini, API membalas 403
     PASSWORD_CHANGE_REQUIRED)
 7. Mengisi sandi saat ini + sandi baru + ulangi (komponen password-field yang sama)
 8. POST /ganti-sandi → SessionService::changePassword():
      verifikasi sandi saat ini; sandi baru lolos PasswordPolicy dan ≠ sandi lama
      password_hash baru, must_change_password = 0, password_changed_at = now
      EventService::record('password_changed')
 9. Redirect /mulai + toast "Kata sandi barumu sudah tersimpan."
```

Siswa juga dapat mengganti sandi sukarela dari halaman Profil lewat alur yang sama (langkah 7–9).

---

## FITUR 3: Pergantian Bahasa

```text
 1. Peserta menekan ID atau EN pada HUD
 2. POST /bahasa (form biasa, bukan AJAX) → HomeController::setLocale
 3. Validation: locale required|valid_locale; redirect_to wajib path internal
 4. SessionService::setLocale():
      update game_sessions.locale
      EventService::record('locale_changed', payload {from, to})
      session()->set('locale', $locale)
 5. Redirect kembali ke path yang sama
 6. LocaleFilter menetapkan locale request
 7. View merender ulang: lang() untuk UI, $entity->text() untuk konten
 8. Gambar bertulisan memakai varian media_assets.locale = 'en' bila ada,
    selain itu varian Indonesia
```

Yang **tidak** berubah: `session_id`, `current_level`, `current_node`, seluruh `item_responses`, skor, bintang, dan status penyelesaian. Halaman dimuat ulang, tetapi state ada di database sehingga tidak ada yang hilang.

---

## FITUR 4: Navigasi Peta dan Unlock Level

```text
 1. Peserta membuka /peta
 2. MapController::kedu
      ContentRepository::levels() → 3 level urut sequence
      SessionProgressModel → unlocked_level_sequence
      ChallengeAttemptModel::completedForSession() → jumlah node selesai per level
 3. Status tiap level:
      completed  bila 5 node level itu selesai
      open       bila sequence <= unlocked_level_sequence
      locked     selain itu (hanya bila study.unlock_mode = 'sequential')
 4. Klik level terbuka:
      wilayah baru terbuka (status open: belum ada node selesai) → SELALU /dialog/{code}
      sedang dijelajahi (in_progress) atau tuntas (completed)      → /wilayah/{code}
      tujuan dihitung GameProgress::regionEntryPath() dan dikirim sebagai `entry`;
      URL /wilayah, /misi, /tantangan yang diketik langsung untuk wilayah baru
      dialihkan ke dialognya sampai dialog itu tampil pada sesi login ini
      (tanda disimpan di sesi PHP, bukan database)
 5. Klik level terkunci → toast, tidak ada navigasi
 6. MapController::level
      ContentRepository::nodesForLevel() → 5 node
      status node: node 1 selalu terbuka; node n terbuka bila node n-1 completed
 7. emit('level_opened')
```

Unlock level berikutnya terjadi di `ChallengeService::completeAttempt()`, bukan di controller peta. Peta hanya membaca.

---

## FITUR 5: Mengerjakan Tantangan — Alur Lengkap

Ini fitur inti. Alur untuk engine batch (`puzzle`, `rumpang`, `boleh`):

```text
 1. Peserta di /wilayah/temanggung menekan pos 2 → /misi/temanggung/2
 2. ChallengeController::brief menampilkan kartu misi + audio
 3. Menekan "Mulai tantangan" → /tantangan/temanggung/2
 4. ChallengeController::play → ChallengeService::openNode()
      a. validasi level unlock dan node aktif
      b. ada attempt in_progress untuk node ini? → pakai ulang (resume)
      c. bila tidak:
           attempt_no = nextAttemptNo(session, node)
           mode = research_studies.item_selection_mode
           seed = participant_code . '|' . node_id
           items = ChallengeItemModel::pickForAttempt(node, itemsPerRound, mode, seed)
           insert challenge_attempts (selected_item_ids_json, scorable_items)
           ItemResponseModel::createPlaceholders() → satu baris status 'pending' per item
           EventService::record('challenge_opened')
      d. bentuk payload pemain: item->toPlayerArray() — TANPA answer_key_json
 5. View game/challenge/rumpang.php merender arena + <script id="challenge-data">
 6. challenge.js memuat engines/rumpang.js, memasang handler
 7. Peserta mengisi 4 kotak rumpang, menekan "Periksa jawaban"
 8. POST /api/attempts/118/check
      { answers: [{item_id, answer:{text}}, …], client_event_id, occurred_at }
 9. ChallengeApiController::check
      validasi: attempt milik sesi, status in_progress,
                setiap item_id ada di selected_item_ids_json
10. ChallengeService::submitCheck() — SATU TRANSACTION:
      attempt.check_count++
      attempt.retry_count = max(0, check_count - 1)
      untuk tiap jawaban:
        ambil challenge_items.answer_key_json DARI DATABASE
        nilai dengan grader sesuai interaction_type
        bila item_responses.first_answer_json NULL:
          isi first_answer_json, set first_pass_correct
        selalu: final_answer_json, is_correct, status='answered',
                answered_at, duration_ms
        bila jawaban berubah: change_count++, attempt.answer_change_count++
      hitung ulang attempt.first_pass_correct dan final_correct
      EventService::record('challenge_checked', payload {correct_count, total})
      EventService::record('answer_submitted'/'answer_changed') per item
11. Response { results, correct_count, total, all_correct, check_count }
12. rumpang.js menandai kotak hijau/merah
      belum semua benar → modal "Belum semuanya tepat" → peserta memperbaiki
                          → kembali ke langkah 7 (check_count bertambah)
      semua benar        → konfeti + modal + tombol Lanjut
13. Tombol Lanjut → POST /api/attempts/118/complete
14. ChallengeService::completeAttempt() — SATU TRANSACTION:
      a. validasi semua item sudah 'answered' atau 'skipped'
      b. attempt.completed_at, duration_ms, status='completed'
      c. ScoringService::scoreAttempt():
           profile = node.scoring_profile_id ?? profil aktif
           first_pass_accuracy = first_pass_correct / scorable_items * 100
           final_accuracy      = final_correct / scorable_items * 100
           independence = clamp(100 - hint_count*hintPenalty
                                    - retry_count*retryPenalty, 0, 100)
           score = clamp(.70*fp + .20*fa + .10*ind, 0, 100)
           stars = 3 bila score>=85 dan fp>=80; 2 bila score>=65;
                   1 bila completed; 0 selain itu
           simpan score, stars, independence, scoring_profile_id, scoring_version
      d. session_progress.completed_nodes++ (jumlah node completed unik)
      e. bila 5 node level selesai:
           completed_levels++
           unlocked_level_sequence = level.sequence + 1 (maks 3)
           EventService::record('level_completed')
      f. total_score = ScoringService::totalScore(session)
      g. total_stars = SUM(stars) attempt completed
      h. EventService::record('challenge_completed')
      i. bila 15 node selesai → SessionService::completeIfFinished()
           game_sessions.status='completed', ended_at, event session_completed
15. Response { score, stars, first_pass_accuracy, final_accuracy, duration_ms,
               shards, level_completed, next_level_unlocked,
               redirect: "/selesai/118" }
16. challenge.js memanggil flush(true) lalu navigasi ke /selesai/118
17. ChallengeController::finished menampilkan bintang, tiga statistik,
    pesan Mbah Kedu bila wilayah tuntas, tombol Balai Refleksi bila 15 tuntas
```

### Perbedaan untuk engine per-item (`pilihan`, `cari`)

```text
 7'. Peserta mengetuk satu opsi / satu objek
 8'. POST /api/attempts/{id}/responses { item_id, answer, client_event_id }
10'. ChallengeService::submitAnswer() — transaction:
       pilihan: allow_retry = false, jadi jawaban pertama = final;
                first_pass_correct = is_correct
       cari   : bila objek yang diklik = target → benar, lanjut petunjuk berikut
                bila bukan target → wrong_click_count++,
                                    event 'wrong_target_clicked',
                                    bila klik pertama untuk petunjuk ini:
                                      first_pass_correct target = 0 dan TETAP 0,
                                    tidak menutup item
                objek jebakan tidak pernah diminta dijawab dan tidak
                menahan complete()
11'. Response menyertakan correct_option_key (pilihan) / feedback + decoy (cari) SETELAH dijawab
13'. Item terakhir dijawab → client langsung memanggil complete()
```

### Bila peserta meninggalkan tantangan

```text
A. Menekan tombol keluar
     confirmDialog → POST /api/attempts/{id}/abandon { reason:'user_exit' }
     ChallengeService::abandonAttempt(): status='abandoned', stars=0, score=0,
     event 'challenge_abandoned'
     Jawaban yang sudah diperiksa TETAP tersimpan di item_responses.

B. Menutup tab / memuat ulang / koneksi putus
     pagehide → event 'challenge_abandoned' { via:'pagehide', attempt_open:true }
     lalu flush antrean lewat sendBeacon (tanpa dialog bawaan browser)
     attempt tetap 'in_progress'
     Saat peserta kembali ke node itu, openNode() menemukan attempt in_progress
     dan melanjutkannya, bukan membuat attempt baru; client mencatat
     'challenge_opened' { resumed:true }.
     RetentionService menandai attempt in_progress yang tidak tersentuh
     lebih dari 24 jam menjadi 'abandoned'.
```

`challenge_abandoned` punya tiga sumber: tombol Keluar (A, server), RetentionService (server), dan `pagehide` dari browser (B) — yang terakhir hanya berarti halaman ditinggalkan, attempt-nya masih dapat dilanjutkan. Karena itu **tingkat ditinggalkan dihitung dari `challenge_attempts.status = 'abandoned'`**, bukan dari jumlah event; di sheet Raw Events ketiganya dibedakan lewat `payload.via` dan `client_event_id` (event server berawalan `srv-`).

---

## FITUR 6: Petunjuk

```text
1. Tombol 💡 tampil bila payload attempt menyertakan hints_count > 0; id petunjuk ada di payload.hints
2. Peserta menekan → POST /api/attempts/{id}/hints { hint_id, item_id? }
3. Validasi: hint milik node attempt (atau item di dalamnya)
4. ChallengeService::useHint() — transaction:
     attempt.hint_count++
     item_responses.hint_used = 1 bila hint terikat item
     EventService::record('hint_opened')
5. Response { text, hint_count }
6. Modal menampilkan teks petunjuk; penghitung bar diperbarui
7. Pengaruh ke skor terjadi saat completeAttempt(), lewat independence,
   dengan besaran dari scoring_profiles — bukan dari JavaScript
```

---

## FITUR 7: Audio & Telemetry

```text
1. View merender components/audio-player untuk dialog, kartu misi, atau item
     audio_src() mengembalikan path hanya bila approval_status = 'approved'
     bila NULL → hanya kotak transkrip yang dirender
2. Peserta menekan Putar (tidak ada autoplay sebelum ada interaksi)
3. audio.js mengakumulasi listenedMs dari waktu putar sungguhan
4. Pada play (mulai dari awal) / pause / replay / ended:
     POST /api/audio-events { events: [{ audio_asset_id, action, play_index,
                              listened_ms, completed, occurred_at,
                              client_event_id, attempt_id? }] }
     Melanjutkan dari jeda tidak dikirim sebagai 'play' baru (masih pemutaran
     yang sama); waktu dengarnya terbawa ke pause/complete berikutnya.
     play_index yang disimpan dihitung server (nextPlayIndex): play/replay
     membuka pemutaran ke-n berikutnya, pause/complete memakai pemutaran
     berjalan — play_index dari client diabaikan.
5. AudioApiController::ingest → EventService::recordAudio() — satu transaction,
   idempotent terhadap client_event_id (kiriman ulang → duplicate):
     insert audio_usage_events
     insert game_event_logs (audio_play / audio_pause / audio_replay / audio_completed)
     bila terkait attempt → attempt.audio_use_count++
6. Analitik audio memakai audio_usage_events, dengan durasi pembanding
   diambil dari audio_assets.duration_ms (data server), bukan dari browser
```

---

## FITUR 8: Pustaka Kedu

```text
1. Tombol "Pustaka Kedu" di peta Kedu → /pustaka (rak semua wilayah,
   LibraryController::index). Tombol "Pustaka {wilayah}" di peta wilayah
   → /pustaka/{level_code}; bergembok selama wilayah belum tuntas (modal
   penjelasan di map.js, tanpa JS → halaman terkunci).
2. Kunci per wilayah (GameProgress::libraryStatus/libraryAccess):
     wilayah belum terbuka              → /peta + toast (seperti sebelumnya)
     wilayah terbuka, belum semua node  → game/library-locked: progres x/5,
       selesai (levelScore)               penjelasan, "Lanjutkan tantangan" →
                                          `entry` wilayah. TIDAK dicatat
                                          library_opened. Berlaku juga pada
                                          unlock_mode = free.
     semua node wilayah selesai         → buku (langkah 3–5)
   /api/library/{levelId} memakai aturan yang sama → 409 LIBRARY_LOCKED.
3. LibraryController::show → ContentRepository::libraryPages()
     halaman aktif + baris library_media aktifnya (LibraryMediaModel::forPages)
4. View merender halaman buku "Pustaka {wilayah}": kiri galeri media (berapa
   pun gambar/video, tiap media di bingkai berasio tetap dengan skeleton),
   kanan judul + teks rich_text(). Media tanpa berkas aktif atau dengan
   tautan yang tidak dikenali MediaLink tidak dirender.
     gambar      : unggahan, Wikimedia Commons, Google Drive, berkas https
                   langsung (tanpa referrer, loading="lazy"); klik → perbesar
     video       : unggahan (<video> berposter) atau YouTube/Vimeo/Drive —
                   facade berposter (thumbnail yang diunduh SERVER, di bawah);
                   iframe baru dipasang setelah siswa menekan Putar;
                   YouTube lewat youtube-nocookie.com dengan
                   referrerpolicy="strict-origin-when-cross-origin"
     media luar  : ikon ⓘ di pojok bingkai → kredit + "Lihat sumber ↗"
5. Server mencatat library_opened (hanya bila isi buku tampil), lalu
   emit('library_page_viewed') tiap ganti halaman
6. Attempt yang menuntaskan wilayah menampilkan "Pustaka {wilayah} terbuka!"
   + tombol Baca Pustaka di layar selesai.
7. Pustaka TIDAK memengaruhi skor, bintang, atau status node.
   Pemakaiannya dianalisis sebagai perilaku belajar, bukan sebagai penilaian.
```

**Catatan untuk peneliti — `library_opened` sejak Tahap 4.** Event `library_opened` kini **hanya** tercatat setelah seluruh tantangan wilayah itu selesai pada sesi tersebut; membuka `/pustaka/{code}` sebelum itu menampilkan halaman terkunci tanpa event. Akibatnya:

* data sebelum Tahap 4 dan sesudahnya tidak sebanding langsung: sebelumnya siswa dapat membaca Pustaka *di tengah* mengerjakan wilayah (mis. sebagai rujukan jawaban), sekarang Pustaka menjadi bacaan *sesudah* wilayah tuntas. Pisahkan analisis menurut tanggal rilis atau `game_releases`;
* setiap `library_opened` dan `library_page_viewed` pada wilayah X selalu terjadi setelah kelima tantangan wilayah X selesai pada sesi yang sama — pemakaian Pustaka tidak lagi dapat memengaruhi attempt pertama tantangan wilayah itu (mengulang tantangan tetap boleh);
* upaya membuka Pustaka yang masih terkunci tidak tercatat sebagai event.

**Thumbnail video (`App\Libraries\VideoThumbnail`).** Poster kartu video YouTube/Vimeo/Drive diunduh **server** satu kali, bukan oleh perangkat siswa: YouTube `i.ytimg.com/vi/{id}/maxresdefault.jpg` (cadangan `hqdefault.jpg`, juga bila maxres berupa gambar abu-abu 120×90), Vimeo lewat oEmbed `vimeo.com/api/oembed.json` → `thumbnail_url` (hanya https di `*.vimeocdn.com`), Drive `drive.google.com/thumbnail?id={id}&sz=w1280`. Respons diterima hanya bila status 200, `Content-Type: image/*` bukan SVG, ≤ 4 MB, dan terbaca `getimagesizefromstring()` — Drive privat mengembalikan halaman HTML masuk Google dan ditolak. Timeout 6 detik (sambung 3 detik), maksimal 3 pengalihan https. Gambar disimpan `MediaStore::storeBytes()` sebagai aset gambar `library.thumb.{penyedia}.{id}-{sha1}` (satu berkas per video, dipakai bersama bila video sama muncul di beberapa halaman) lalu dipasang di `library_media.poster_media_id`.

* Dipanggil setelah commit oleh `Admin\ContentController::saveLibrary()` dan `ContentImportService::import()` untuk video luar yang posternya masih kosong. Kegagalan tidak pernah menggagalkan penyimpanan: dicatat `log_message()`, admin melihat catatan (flash `notice`), impor menambah peringatan di sheet `library_media`.
* `php spark gelita:library:thumbnails [--force]` mengisi baris yang sudah ada (atau yang gagal karena server sedang tanpa internet) dan mencetak ringkasan terisi/dilewati/gagal; `--force` mengunduh ulang juga yang sudah berposter. Kode keluar 1 bila ada yang gagal.
* Poster yang diunggah admin tidak pernah ditimpa kecuali dengan `--force`.

Admin mengelola halaman dan medianya di `/admin/konten/pustaka/{level_id}`: tambah/hapus/urutkan halaman, dan per halaman tambah media dari **Berkas** (asset_key atau unggah langsung) atau **Tautan**. Seluruh halaman satu wilayah disimpan dalam satu transaction. Referrer-Policy situs `same-origin` tidak dipakai untuk iframe YouTube karena pemutarnya menolak diputar tanpa referrer (galat 153).

Format teks halaman (ditulis tanpa HTML): baris kosong = paragraf, `## ` subjudul, `- ` daftar, `> ` kotak "Tahukah kamu?", baris `Sumber:` catatan rujukan, `**tebal**`, `*miring*`. `rich_text()` meng-escape seluruh teks lebih dulu.

---

## FITUR 9: Balai Refleksi & Kritik-Saran

```text
1. Setelah 15 node selesai, tombol Balai Refleksi muncul di layar selesai
2. /refleksi → ReflectionController::index
     guard: completed_nodes harus sama dengan jumlah node aktif;
            bila belum → redirect /peta
3. Menampilkan ringkasan literasi peserta:
     rata-rata tepat sejak awal, tantangan selesai, total waktu belajar,
     benar/total butir, bar ketepatan per wilayah dan per jenis tantangan,
     daftar "paling sering terlewat"
4. Formulir: bintang 1–5 + empat pertanyaan terbuka
5. POST /refleksi
     validasi rating 1–5; minimal dua textarea terisi
     insert participant_feedback
     EventService::record('feedback_submitted')
6. Masukan muncul di /admin/masukan
```

---

## FITUR 10: Login & Dashboard Guru

```text
1. Guru membuka /admin/login
2. POST → AuthController::login
     findByUsername → isLocked? → verifyPassword → is_active?
     gagal   : registerFailedLogin, audit 'login_failed',
               pesan identik untuk semua jenis kegagalan
     berhasil: session()->regenerate(true), set staff_id/role/school_id/name,
               clearFailedLogin, audit 'login'
3. Redirect /admin/dashboard
4. DashboardController::index
     filters = readFilters()  (query string)
     scope   = schoolScope()  (null untuk admin, school_id untuk guru)
     AnalyticsService::summary(filters + school_id)
5. View merender 9 KPI card (termasuk % siswa yang langsung membuat sandi kuat)
   + wadah chart kosong
6. admin.js memanggil /api/admin/summary, /levels, /nodes, /prepost
7. charts.js menggambar dengan ECharts
8. Mengubah filter → filters.js memperbarui URL + memuat ulang data,
   tanpa memuat ulang halaman
```

Scope sekolah diterapkan di tiga tempat: filter route, `schoolScope()` di controller, dan di dalam setiap query `AnalyticsService`. Bila `AnalyticsService` dipanggil tanpa parameter scope oleh pemanggil non-admin, ia melempar exception.

---

## FITUR 11: Drilldown Analitik

Jalur navigasi: `Ringkasan → Fase → Peserta → Level → Node → Item → Event`.

```text
1. /admin/dashboard  → KPI + chart per wilayah
2. Klik wilayah      → /admin/analitik/level?level_id=1
3. Klik node         → /admin/analitik/node/7
     AnalyticsService::nodeDifficulty + itemAnalysis untuk node itu
     sebaran skor, scatter durasi vs ketepatan, daftar item dengan p dan D
4. Klik item         → daftar item_responses: jawaban peserta, kunci,
                       benar/salah, durasi, jumlah perubahan
5. Klik satu respons → /admin/sesi/{id}/event?item_id=…
     linimasa game_event_logs dengan presisi milidetik
6. Dari mana pun, klik kode peserta → /admin/peserta/{id}
```

**Analisis butir** (`/admin/analitik/butir`) dihitung oleh `AnalyticsService::itemAnalysis()`:

```sql
-- inti perhitungan
muncul       = COUNT(item_responses yang status='answered')
benar        = COUNT(is_correct = 1)
p            = benar / muncul
rata_detik   = AVG(duration_ms) / 1000
distraktor   = jawaban salah tersering dari final_answer_json
```

Daya beda `D`:

```text
1. Hitung total ketepatan tiap peserta pada (study, phase) yang difilter
2. Urutkan peserta, ambil 27% teratas dan 27% terbawah
3. D = (benar_atas / n_atas) - (benar_bawah / n_bawah)
4. Tafsir: <0 buruk, <0.20 lemah, 0.20–0.40 cukup, >0.40 baik
```

Nilai `D` negatif ditandai merah dan diberi catatan: biasanya menunjukkan kunci jawaban keliru atau kalimat yang membingungkan.

---

## FITUR 12: Kelola Konten

```text
1. Admin membuka /admin/konten → 3 kartu wilayah, tiap kartu 5 node
2. Klik node → /admin/konten/node/7
     form node (judul/instruksi/deskripsi ID+EN berdampingan, indikator,
     profil skoring, config terpandu) + tabel bank item
3. Tambah item → content-editor.js menampilkan field sesuai interaction_type
4. POST /admin/konten/node/7/item
     validasi: interaction_type cocok dengan engine_type node;
               answer_key_json wajib bila scorable = 1;
               single_choice wajib punya tepat satu opsi is_correct
5. Insert challenge_items (+ challenge_options bila perlu)
     media langsung dari formulir (komponen media-field, App\Libraries\MediaStore):
       node   : gambar adegan (arena cari) + latar tantangan + narasi pembuka ID/EN
       butir  : gambar keping puzzle / objek cari / gambar pendamping
       opsi   : gambar per opsi;  bacaan: gambar pendamping
       wilayah: peta, latar, lencana;  dialog: audio per baris
     pilih asset_key yang ada ATAU unggah berkas baru di baris itu; berkas
     baru disimpan dengan asset_key bawaan tempat itu (mis. challenge.item.tmg-4-03)
6. ContentRepository::flush(); audit 'content_create'
7. Impor massal seluruh bank soal: lihat FITUR 12a
8. Verifikasi: POST /admin/konten/verifikasi atau `php spark gelita:content:verify`
     memeriksa 3 level, 5 node per level, engine valid,
     bank item >= items_per_round, kunci lengkap, verdict di dalam verdict_options,
     passage satu level dengan node, pengecoh rumpang cukup,
     jawaban kembar antar node, media hilang, item needs_verification yang aktif
```

`engine_type` tidak dapat diubah bila node sudah punya attempt — mengubahnya akan membuat data lama tidak sebanding.

---

## FITUR 12a: Impor Workbook Bank Soal

Isi bank soal (119 butir untuk 15 node, beserta teks bacaan, opsi, pengecoh, dan petunjuk) disusun di **Dokumen Bank Soal GELITA**. Admin memindahkannya ke satu workbook XLSX mengikuti templat di bawah, lalu mengimpornya sekaligus.

Workbook produksi yang sudah terisi — 15 tantangan, 131 butir, 30 halaman Pustaka dengan 48 media — ada di [`docs/bank-soal/`](bank-soal/README.md) dan dapat langsung diimpor.

### Alur

```text
1. Admin membuka /admin/konten/impor-bank → unduh templat (importTemplate)
2. Menyalin isi Dokumen Bank Soal ke sheet templat (pemetaan di bawah)
3. Unggah → POST /admin/konten/impor-bank/pratinjau
     ContentImportService::preview():
       baca 10 sheet data (sheet lain, mis. PETUNJUK, diabaikan);
       validasi setiap baris; TIDAK menulis database
       ringkasan per node: item, passage, opsi, hint, bank vs minimum
       galat (merah)      : sheet + nomor baris + alasan
       peringatan (kuning): review_status needs_verification, bank = minimum persis,
                            asset_key belum ada di media_assets (akan dibuat slot)
4. Galat = 0 → tombol Impor aktif
5. POST /admin/konten/impor-bank/jalankan → ContentImportService::import()
     SATU TRANSACTION, urutan: slot media → nodes → distractors → passages
                               → items → options → pieces → sources → hints
                               → library → library_media
     asset_key yang belum terdaftar → baris media_assets is_active = 0
       (slot kosong; view memakai pengganti sampai berkasnya diunggah)
     upsert berdasarkan node_ref / passage_key / item_key / option_key /
       (level_code, sequence) halaman Pustaka
     library_media: seluruh media halaman yang disebut diganti baris workbook
     kolom media kosong pada baris yang sudah ada → media lama dipertahankan
     item yang sudah punya item_responses: answer_key TIDAK boleh berubah
       → baris ditolak, seluruh impor dibatalkan, admin diberi tahu item mana
6. ContentRepository::flush(); audit 'content_import' {file_sha256, jumlah per tabel}
7. Jalankan verifikasi konten (FITUR 12 langkah 8)
```

CLI setara untuk instalasi awal: `php spark gelita:bank:import writable/uploads/bank-soal.xlsx --dry-run` lalu tanpa `--dry-run`.

### Format workbook (10 sheet data)

Baris pertama tiap sheet = header persis seperti di bawah (`ContentImportService::SHEETS`). Sel kosong = NULL. Nilai `node_ref` = `tmg-1`…`tmg-5`, `mgl-1`…`mgl-5`, `wnb-1`…`wnb-5` (awalan dari `Config\Gelita::$levelPrefixes`). Sheet `library` dan `library_media` boleh tidak ada.

**Templat berpanduan** (`App\Libraries\BankWorkbookGuide`, dipakai templat unduhan dan workbook produksi). Header tetap berbahasa Inggris karena dibaca sistem; panduannya berbahasa Indonesia:

- sheet **PETUNJUK** — langkah mengisi & mengimpor, arti warna header, fungsi setiap sheet beserta jumlah barisnya, format teks Pustaka, dan tabel jenis soal;
- sheet **KAMUS_KOLOM** — setiap kolom: nama Indonesia, status (Wajib/Bersyarat/Opsional), arti, isian yang boleh, contoh;
- header sheet data berwarna menurut status, dengan catatan (segitiga merah) dan kotak bantuan yang muncul saat sel dipilih;
- dropdown untuk kolom berkode (`node_ref`, `interaction_type`, `kind`, 0/1, …) dan batas angka (`x`/`y` 0–100, `items_per_round` ≥ 1) pada 1.000 baris pertama; kolom teks diformat Teks agar `01` atau `1,2,3` tidak diubah Excel.

`tests/unit/BankWorkbookGuideTest.php` menjaga agar setiap kolom importer punya penjelasan, dan workbook produksi ikut dibangun ulang bila kolom berubah.

**1. `nodes`** — satu baris per node (15 baris)

| Kolom | Isi |
|---|---|
| node_ref | `tmg-1` … `wnb-5` (wajib; menentukan level & sequence) |
| title_id, title_en | judul node |
| instruction_id, instruction_en | instruksi |
| description_id, description_en | teks kartu misi |
| items_per_round | angka |
| verdict_options | khusus node `boleh`: `benar,salah` atau `benar,salah,pendapat` |
| require_reason | 0/1 |
| use_word_bank, distractor_count | khusus rumpang |
| scene_media_key | gambar adegan arena `cari` (wajib untuk tmg-4) |
| background_media_key | latar layar tantangan; kosong = latar wilayah |

**2. `distractors`** — pengecoh rumpang: `node_ref`, `text_id`, `text_en`

**3. `passages`** — `passage_key` (mis. `tmg-teks-a`), `level_code`, `title_id`, `title_en`, `body_id`, `body_en`, `media_asset_key`, `reference_source`

**4. `items`** — satu baris per butir

| Kolom | Isi |
|---|---|
| item_key | `tmg-2-01` (wajib, unik) |
| node_ref | node pemilik |
| sequence | urutan dalam bank |
| interaction_type | salah satu dari 9 nilai di 01_DATABASE.md |
| indicator | `literasi` \| `budaya` \| `sikap` |
| prompt_id, prompt_en | kalimat soal / pernyataan / petunjuk objek / deskripsi puzzle |
| source_text_id, source_text_en | teks sumber khusus item (bila tidak memakai passage) |
| passage_key | rujukan ke sheet `passages` |
| answer_id, answer_en | kunci ringkas, lihat tabel pemetaan kunci |
| sample_reason_id, sample_reason_en | contoh alasan `verdict_reason` |
| x, y, w, decoy, wrong_feedback_id, wrong_feedback_en | khusus `find_object` (posisi & lebar dalam persen adegan 16:9) |
| digital_pillar | khusus Wonosobo node 5 |
| media_asset_key | gambar item |
| scorable | 1 (default) / 0 |
| review_status, review_note | `draft` \| `needs_verification` \| `verified` |
| reference_source | lembaga/judul + URL |

**Pemetaan kunci ringkas → `answer_key_json`:**

| interaction_type | Isi `answer_id` / `answer_en` | Disimpan sebagai |
|---|---|---|
| puzzle_arrange | kosong (atau `1..9`) | `{"order":[0,1,2,3,4,5,6,7,8]}` |
| ordering | kunci potongan berurutan, mis. `1,2,3,4,5` | `{"order":["1","2","3","4","5"]}` |
| fill_blank_bank | satu kata/frasa, mis. `Sumbing` / `Sumbing` | `{"text_id":…,"text_en":…}` |
| fill_blank_free | jawaban diterima dipisah `\|`, mis. `Syailendra\|Sailendra` | `{"accept_id":[…],"accept_en":[…]}` |
| verdict_card / verdict_reason | `benar` \| `salah` \| `pendapat` (huruf besar diterima, disimpan kecil) | `{"verdict":…}` (+ sample_reason) |
| single_choice / source_trust | kosong — kunci diambil dari opsi `is_correct = 1` | `{"option_key":…}` |
| find_object | `target` atau `decoy` | `{"target":true\|false}` + `config_json.decoy` |

**5. `options`** — `option_key` (mis. `mgl-4-03-b`), `item_key`, `label_id`, `label_en`, `is_correct` (0/1), `feedback_id`, `feedback_en`, `media_asset_key`, `display_order`

**6. `pieces`** — potongan `ordering`: `item_key`, `piece_key`, `text_id`, `text_en`

**7. `sources`** — dua sumber Wonosobo node 3: `item_key`, `label_id`, `label_en`, `kind` (`official` \| `anonymous` \| `chain_message` \| `blog`), `text_id`, `text_en`

**8. `hints`** — `node_ref` **atau** `item_key` (salah satu), `sequence`, `text_id`, `text_en`

**9. `library`** — halaman Pustaka Kedu: `level_code`, `sequence` (nomor halaman), `title_id`, `title_en`, `body_id`, `body_en` (format teks FITUR 8), `is_active`

**10. `library_media`** — gambar/video halaman: `level_code`, `page_sequence`, `sequence`, `media_kind` (`image` \| `video`), `media_asset_key` **atau** `external_url` (YouTube, Google Drive, Vimeo, Wikimedia Commons, berkas `https`), `poster_media_key` (khusus video unggahan), `caption_id`, `caption_en`, `credit` (wajib untuk media milik pihak lain)

### Pemetaan dari Dokumen Bank Soal GELITA

| Di dokumen bank soal | Masuk ke |
|---|---|
| Tabel metadata node (title, instruction, description, items_per_round, petunjuk node) | sheet `nodes` + `hints` (baris dengan `node_ref`) |
| "Kata pengecoh" / "Pengecoh (frasa)" | sheet `distractors` |
| "source_text (Teks A/B/C)" | sheet `passages`; item yang memakainya diberi `passage_key` |
| Tabel item (prompt, kunci, indikator) | sheet `items` |
| Kolom "opsi (kunci ✓)" A–D | sheet `options`, kunci ✓ → `is_correct = 1` |
| Kolom "feedback" / "penjelasan salah-klik" | `options.feedback_*` / `items.wrong_feedback_*` |
| Potongan tahapan bernomor (Wonosobo node 1) | sheet `pieces`; `answer_id` = urutan nomor |
| "Sumber A … / Sumber B …" (Wonosobo node 3) | sheet `sources` |
| "petunjuk item" | sheet `hints` (baris dengan `item_key`) |
| "media (ilustrator)" | daftarkan dulu sebagai `media_assets`, lalu isi `media_asset_key` |
| Klaim di lampiran "[PERLU VERIFIKASI]" | `review_status = needs_verification` + `review_note` |
| Jawaban rumpang bank kata dengan dua alternatif (mis. "Wonosobo/Dieng") | pilih satu untuk `answer_id` — kata di bank kata harus tunggal |

Nomor potongan puzzle gambar di dokumen bank soal ditulis mulai 1 (`[1..9]`); importer menormalkannya menjadi indeks 0–8.

---

## FITUR 13: Media & Audio

```text
1. /admin/media → tabel aset: kunci, jenis, ukuran wajib vs sebenarnya, status,
     dan kolom "Dipakai di" (App\Libraries\MediaUsage): tantangan, butir, opsi,
     bacaan, wilayah, halaman Pustaka, atau slot tampilan (bg.welcome, logo,
     karakter, lencana …). Jumlah slot tanpa berkas tampil di kartu "Slot belum berberkas".
2. Seret berkas ke baris aset → media-upload.js membaca dimensi di client
3. Ukuran tidak cocok + mode ketat → ditolak di client dengan pesan spesifik
4. POST /admin/media/unggah (multipart)
     MediaController::upload:
       validasi MIME + ekstensi + ukuran berkas
       getimagesize() → verifikasi ulang dimensi (client tidak dipercaya)
       simpan ke public/assets/uploads/{asset_key}.{ext} dengan NAMA RESMI
       hitung sha256, file_size, dimensi
       upsert media_assets
       audit 'media_upload'
5. Audio: POST /admin/media/audio/unggah dengan locale, character_code,
   context_code, transcript (WAJIB), production_method
     status awal 'draft' → tidak dikirim ke pemain
     unggah ulang asset_key yang sama → baris audio_assets yang ada diperbarui
     dan kembali 'draft' (berkas baru wajib didengar ulang sebelum tayang)
6. Admin memutar pratinjau, menekan Setujui
     approval_status='approved', approved_by, approved_at, audit 'audio_approve'
7. Baru setelah itu audio_src() mengembalikan path dan pemain mendengarnya
```

Unggahan dari editor konten (FITUR 12) memakai jalur yang sama (`App\Libraries\MediaStore`): validasi MIME, dimensi wajib dari `Config\Gelita::$assetSizes`, nama berkas resmi, sha256, dan audit. Audio dialog dan narasi pembuka tantangan dipilih dari audio yang sudah diunggah di `/admin/media/audio`.

---

## FITUR 14: Export XLSX

### Alur

```text
1. Guru/admin membuka /admin/ekspor
2. Memilih filter (studi, fase, wilayah, sekolah, kelas, rentang tanggal),
   memilih sheet yang disertakan, dan mode anonim
     guru → toggle anonim TERKUNCI menyala; sheet Raw Events tidak tersedia
3. POST /admin/ekspor/xlsx
4. ExportController::xlsx
     insert data_exports (status 'running', scope_json {filters, sheets}, anonymized)
     ExportService::build($exportId) — sinkron di request yang sama
       hak pemohon DIBACA ULANG dari staff_users (lapis ketiga):
       guru → school_id sekolahnya, anonim, tanpa Raw Events, tanpa kunci jawaban
       Raw Events diminta → COUNT(*) dulu; > gelita.exportMaxRawEvents → 'failed'
5. ExportService menulis berkas ke writable/exports/{id}-{Ymd-His}.xlsx
6. Selesai: file_sha256, row_count, status 'done',
   expires_at = now + gelita.exportRetentionDays; audit 'export' {status, row_count, sha}
   Gagal: status 'failed' + error_message, berkas setengah jadi dihapus
7. Redirect ke /admin/ekspor dengan pesan "Ekspor #n siap diunduh" atau alasan gagal;
   export.js tetap mem-polling /api/admin/exports/{id}/status untuk baris queued/running
8. Status 'done' → tombol Unduh aktif, SHA-256 ditampilkan
9. GET /admin/ekspor/unduh/{id}
     validasi pemilik atau admin, validasi belum kedaluwarsa
     kirim berkas dari writable/exports/ (di luar public/), audit
```

### Isi workbook

| # | Sheet | Kolom utama |
|---:|---|---|
| 1 | Participants | kode, [nama, nama pengguna, sekolah]*, umur, kelas, jenis kelamin, negara, provinsi, kabupaten, terdaftar, consent, waktu consent, **syarat sandi terpenuhi pada percobaan pertama (0–5), jumlah penolakan sandi lemah** |
| 2 | Sessions | kode sesi, kode peserta, studi, fase, rilis, bahasa, status, mulai, selesai, durasi, perangkat, OS, peramban, serpihan, total skor |
| 3 | Levels | kode sesi, wilayah, node selesai, level score, bintang, durasi |
| 4 | Challenge Summary | kode sesi, wilayah, node, jenis, varian, attempt, status, item, benar awal, benar akhir, tepat sejak awal %, ketepatan akhir %, pemeriksaan, petunjuk, perubahan jawaban, audio, independence, skor, bintang, scoring_version |
| 5 | Item Responses | kode sesi, node, item_key, interaction_type, indikator, jawaban pertama, jawaban akhir, kunci**, benar awal, benar akhir, perubahan, petunjuk, klik salah, durasi |
| 6 | Raw Events | event_uuid, kode sesi, kode peserta, wilayah, node, attempt, item, jenis event, urutan, waktu client, waktu server, payload |
| 7 | Audio Usage | kode sesi, aset audio, konteks, karakter, bahasa, aksi, indeks putar (pemutaran ke-n, dihitung server), listened_ms, selesai, waktu |
| 8 | Indicators | kode peserta, indikator, bukti, benar, rasio penguasaan, rata durasi |
| 9 | Demographic Summary | agregat per jenis kelamin, kelas, sekolah, provinsi, fase |
| 10 | Feedback | kode peserta, fase, bintang, empat jawaban terbuka, waktu |

\* Kolom nama, nama sekolah, dan identitas lain **dihilangkan sepenuhnya** bila `anonymized = 1`. Bukan dikosongkan — kolomnya tidak dibuat, sehingga tidak ada kolom kosong yang menggoda untuk diisi manual. Sebagai gantinya ada `school_ref` (`SCH-000012`, kode samaran stabil) di sheet Participants dan Demographic Summary agar analisis per sekolah tetap mungkin. Nama wali (`guardian_name`) tidak pernah diekspor.

\** Kolom kunci jawaban hanya disertakan untuk role admin.

`password_hash`, `failed_login_count`, dan `locked_until` **tidak pernah** masuk export dalam mode apa pun.

### `ExcelWriter` — menulis bertahap

Dataset raw event bisa ratusan ribu baris. Jangan membangun seluruh workbook di memori.

> **Implementasi tahap 7:** `ExcelWriter` tidak memakai `PhpSpreadsheet\Writer\Xlsx` untuk menulis — writer itu tetap menyimpan seluruh sel di memori, dan cache sel ke disk memperlambatnya berkali lipat. Sebagai gantinya setiap sheet ditulis langsung sebagai XML SpreadsheetML ke berkas sementara (buffer di-flush tiap 1000 baris) lalu dirangkai menjadi paket XLSX dengan `ZipArchive`. Antarmukanya tetap seperti di bawah. Uji 150.000 baris Raw Events: puncak memori 18 MB. PhpSpreadsheet masih dipakai untuk membaca workbook impor bank soal dan di test untuk membaca ulang hasil export. Teks ditulis sebagai inline string, sehingga nilai berawalan `=`/`+`/`-`/`@` tidak pernah menjadi rumus.

```php
class ExcelWriter
{
    public function __construct(string $path);

    public function startSheet(string $name, array $headers): void;

    /** Menulis satu baris; flush ke disk tiap 1000 baris */
    public function row(array $values): void;

    /** Menyuapkan hasil query secara bertahap */
    public function feed(\Closure $generator): void;

    public function finish(): string;   // return sha256
}
```

Query dibaca per potongan 2.000 baris dengan keyset (`WHERE id > :terakhir ORDER BY id LIMIT 2000`), bukan hasil unbuffered — portabel antardriver dan tidak menahan koneksi selama sheet ditulis:

```php
foreach ($this->chunked(fn () => $this->eventBuilder($filters)->select('game_event_logs.id AS _id, …'), 'game_event_logs.id') as $row) {
    $writer->row([...]);
}
```

Raw Events dibatasi `Config\Gelita::$exportMaxRawEvents` (200.000). Estimasi dihitung dengan `COUNT(*)` sebelum penulisan dimulai; bila melebihi ambang, export ditolak dengan pesan yang meminta rentang dipersempit.

---

## FITUR 15: Export PDF

```text
1. POST /admin/ekspor/pdf dengan filter (laporan studi) atau participant_id
   (laporan satu peserta, tombol di /admin/peserta/{id})
2. ExportService::build($exportId) → ReportService::render($path, $context):
     a. AnalyticsService mengumpulkan snapshot: ringkasan, per level,
        per node, indikator, demographic, pre/post
     b. render app/Views/pdf/report-study.php menjadi HTML
        - seluruh nilai lewat esc()
        - chart disertakan sebagai gambar SVG/PNG yang dibuat server-side
          sederhana (bar horizontal dengan <div> berwarna), bukan hasil
          screenshot browser
     c. mPDF: \Mpdf\Mpdf dengan mode 'utf-8', A4, margin 20mm
        setHeader / setFooter dengan nomor halaman dan tanggal cetak
     d. Output ke writable/exports/
3. Isi laporan default:
     sampul (judul, studi, fase, rentang tanggal, dibuat oleh, waktu)
     ringkasan demographic
     ringkasan proses keseluruhan
     grafik per wilayah
     tabel kesulitan node
     penguasaan indikator
     perbandingan pretest/posttest bila ada
     catatan metodologis singkat: arti tepat sejak awal, p, dan D
4. Raw event TIDAK masuk PDF default. Bila admin memerlukannya,
   ia mengekspor XLSX dengan cakupan eksplisit.
```

`report-participant.php` adalah varian satu peserta, dipakai guru untuk laporan individual.

Semua konten yang berasal dari input pengguna (nama sekolah, kritik & saran) di-escape sebelum masuk mPDF. mPDF memproses HTML, jadi HTML yang tidak bersih adalah lubang injeksi.

---

## FITUR 16: Penghapusan Data

```text
1. Admin membuka /admin/tata-kelola
2. Menentukan cakupan: tepat satu dari peserta, sesi, atau studi;
   cakupan studi dapat dipersempit ke satu fase dan rentang tanggal mulai sesi
3. POST /admin/tata-kelola/hapus/pratinjau
     RetentionService::normalizeScope() → RetentionService::preview($scope, $mode, $reason, $staffId):
       menghitung jumlah baris terdampak PER TABEL tanpa menghapus apa pun
       insert data_deletion_requests (status 'preview', affected_count)
       audit 'delete_preview'
4. UI menampilkan tabel jumlah terdampak:
     participants 12 · game_sessions 31 · challenge_attempts 402
     item_responses 2.118 · game_event_logs 47.930 · audio_usage_events 1.204
     participant_feedback 9
5. Admin mengetik HAPUS pada kotak konfirmasi, memilih mode soft/hard
6. POST /admin/tata-kelola/hapus/{id}/jalankan
     RetentionService::execute($requestId) — SATU TRANSACTION:
       mode soft: participants.deleted_at, game_event_logs.deleted_at +
                  deleted_by + delete_reason
       mode hard: hapus baris turunan lebih dulu, lalu induknya
       update data_deletion_requests: status 'executed', executed_at,
              affected_count final
       audit 'delete_execute' dengan rincian per tabel
7. Bila jumlah terdampak saat eksekusi berbeda jauh dari pratinjau
   (data bertambah di antaranya), eksekusi DIBATALKAN dan admin diminta
   membuat pratinjau baru. "Jauh" = |sekarang − pratinjau| >
   max(gelita.deletionDriftMinRows = 10, gelita.deletionDriftFraction = 10% × pratinjau)
```

Mode *soft* menandai `game_event_logs` (deleted_at/by/reason) seluruh sesi dalam cakupan, dan `participants.deleted_at` bila cakupannya satu peserta. Mode *hard* menghapus dari anak ke induk: event, audio, jawaban, attempt, progres, refleksi (per sesi, atau seluruh refleksi peserta pada cakupan peserta), sesi, lalu — hanya pada cakupan peserta — persetujuan dan baris peserta.

Tidak ada endpoint lain di seluruh aplikasi yang menghapus data penelitian.

---

## FITUR 17: Retensi Terjadwal

```bash
php spark gelita:retention:run
```

Dijalankan cron harian (tombol "Jalankan retensi sekarang" di `/admin/tata-kelola` menjalankan `RetentionService::run()` yang sama). Langkah:

```text
1. Sesi 'active' yang last_active_at lebih lama dari sessionIdleMinutes
     → status 'paused' (peserta masih dapat melanjutkan)
2. Attempt 'in_progress' yang tidak tersentuh > attemptAbandonHours (24 jam)
     sentuhan terakhir = MAX(started_at, jawaban terakhir, event terakhir attempt itu)
     → status 'abandoned', stars 0, score 0,
       event 'challenge_abandoned' {via:'retention', idle_hours, last_touch}
3. Sesi 'paused' yang tidak tersentuh > sessionAbandonDays (30 hari)
     → status 'abandoned', event 'session_abandoned' {via:'retention'}
4. data_exports dengan expires_at < now
     → hapus berkas fisik, kosongkan file_path, audit
5. Data penelitian melewati research_studies.retention_days
     → TIDAK dihapus otomatis. Sistem membuat data_deletion_requests
       berstatus 'preview' dan menampilkan peringatan di dashboard admin.
       Penghapusan sungguhan tetap memerlukan konfirmasi manusia.
       Satu pratinjau per studi: scope {study_id, date_to = hari ini − retention_days − 1},
       mode hard, scope_json.origin = 'retention'. Run berikutnya MEMPERBARUI
       pratinjau yang masih menunggu, bukan membuat baru. Pemiliknya admin
       yang menjalankan tombol, atau admin aktif pertama bila dijalankan cron.
6. Menulis ringkasan ke audit_logs
```

Langkah 5 disengaja. Retensi yang menghapus sendiri data penelitian tanpa seorang pun menyetujuinya adalah cara paling cepat kehilangan data skripsi yang tidak dapat diulang. Sistem mengingatkan; manusia memutuskan.

---

## FITUR 18: Pretest → Posttest

```text
1. Guru menyetel research_studies.item_selection_mode = 'fixed' sebelum kelas
2. Peserta mengerjakan sesi fase 'pretest'
     pickForAttempt memakai seed participant_code|node_id
     → butir yang dipilih deterministik
3. Pembelajaran berlangsung di luar aplikasi
4. Guru menyetel research_studies.active_phase_code = 'posttest' di /admin/studi
   Siswa masuk di /masuk dengan akun yang sama
     tidak ada sesi posttest → SessionService::startNewSession() membuat sesi BARU
     seed sama (participant_code|node_id) → butir yang sama persis
5. /admin/analitik/prepost
     AnalyticsService::prePostComparison():
       memasangkan sesi completed terakhir tiap peserta per fase
       memeriksa kompatibilitas: release_id, content_version, scoring_version
       kompatibel     → hitung delta skor, delta tepat sejak awal,
                        delta durasi, delta penguasaan indikator
       tidak kompatibel → ditampilkan di bagian terpisah dengan penjelasan,
                        TIDAK ikut dihitung dalam delta
6. Chart garis pretest → posttest per kelompok dan per peserta
7. Export XLSX menyertakan kolom fase pada seluruh sheet gameplay,
   sehingga analisis lanjutan di SPSS/R dapat memisahkannya sendiri
```

Sesi berfase `umum` tidak pernah dicampur ke dalam perhitungan efek pretest–posttest.

---

## FITUR 19: Ketahanan Offline

```text
1. Koneksi putus di tengah tantangan
2. api.js gagal → ApiError code 'NETWORK'
3. Untuk event: events.js menyerahkan batch ke Storage.queueEvents()
     UI menampilkan penanda kecil "Tersimpan sementara"
4. Untuk jawaban (check/respond): TIDAK diantrekan.
     Peserta melihat toast "Sambungan terputus. Ketuk Periksa lagi."
     Jawaban harus divalidasi server; menyimpannya diam-diam di client
     akan membuat UI menampilkan hasil yang belum tentu disetujui server.
5. Koneksi pulih (event 'online' atau permintaan berikutnya berhasil)
     Storage.takeAll() → kirim ulang batch
     server membalas duplicate untuk yang sudah masuk → tidak ada data ganda
6. Bila peserta menutup tab sebelum koneksi pulih:
     event yang belum terkirim tetap ada di localStorage perangkat itu
     dan dikirim saat aplikasi dibuka kembali di perangkat yang sama
```

Pembedaan ini penting: event bersifat tambahan dan boleh terlambat; jawaban bersifat authoritative dan tidak boleh ditampilkan sebagai diterima sebelum server menerimanya.

---

## Ringkasan Transaction Boundary

| Operasi | Yang dibungkus satu transaction |
|---|---|
| Registrasi siswa | schools + participants (akun + metrik sandi) + consents + sessions + progress + event |
| Login siswa | participants (throttle) + game_sessions (lanjut/baru) + event |
| Ganti / reset sandi | participants + event `password_changed` atau audit `participant_password_reset` |
| Impor bank soal | nodes + distractors + passages + items + options + hints + audit |
| Buka node | attempt + item_responses placeholder + event |
| Kirim jawaban | item_responses + attempt counters + event |
| Periksa batch | seluruh item_responses + attempt counters + event |
| Pakai petunjuk | attempt.hint_count + item_responses.hint_used + event |
| Tutup attempt | attempt + scoring + session_progress + unlock + event |
| Sesi selesai | game_sessions + session_progress + event |
| Ingest event batch | insert batch (idempotent) |
| Eksekusi penghapusan | seluruh tabel terdampak + deletion request + audit |

Semua memakai `$db->transStart()` / `$db->transComplete()`. Bila gagal, tidak ada yang setengah tersimpan.

---

## Aturan Sistem

1. Skor, bintang, status unlock, dan jumlah serpihan ditentukan server tanpa kecuali.
2. Kunci jawaban tidak pernah meninggalkan server sebelum item dijawab.
3. Setiap operasi lintas tabel dibungkus satu transaction.
4. Setiap tindakan peserta yang bermakna menulis `game_event_logs`; setiap tindakan staff yang bermakna menulis `audit_logs`.
5. Guru hanya melihat dan mengekspor data sekolahnya, selalu dalam mode anonim.
6. Raw event tidak dihapus karena agregatnya sudah dibuat.
7. Penghapusan selalu dua langkah dengan konfirmasi teks, dan selalu tercatat.
8. Berkas export berada di luar `public/`, punya SHA-256 dan masa berlaku.
9. Perbandingan pretest–posttest hanya dilakukan pada versi rilis dan scoring yang kompatibel.
10. Mengulang tantangan tidak menghapus catatan lama — setiap attempt tersimpan sebagai data penelitian tersendiri.
11. Pustaka dan audio tidak pernah memengaruhi skor; keduanya dianalisis sebagai perilaku belajar.
12. Kata sandi siswa tidak pernah disimpan, dicatat, atau diekspor dalam bentuk apa pun selain `password_hash`. Metrik literasi keamanan digital hanya berupa angka jumlah syarat dan jumlah penolakan.
13. Isi bank soal hanya masuk lewat impor workbook atau editor konten; keduanya memvalidasi kunci jawaban dan tidak mengizinkan perubahan kunci pada item yang sudah dijawab.

---

## Catatan Implementasi Tahap 7

Tahap 7 menyelesaikan ekspor, laporan PDF, retensi, dan kelima command. Semua diverifikasi di atas MariaDB 10.11 dengan data permainan dari service asli (6 peserta, 79 attempt, lima engine) lalu lewat HTTP (`php spark serve`) sebagai admin dan guru.

### Komponen

| Komponen | Peran |
|---|---|
| `ExportService` | `build($exportId)` untuk XLSX dan PDF; `context()` membaca ulang hak pemohon dari `staff_users`; `columns()`, `allowedSheets()`, `sanitizeFilters()` adalah aturan murni yang diuji tanpa database; `purgeExpired()` dipakai retensi |
| `ReportService` | `render($path, $context)` — templat `study` (default) atau `participant`; data dari `AnalyticsService` yang terikat cakupan pemohon, **selalu anonim** untuk laporan studi |
| `RetentionService` | `normalizeScope`, `countAffected`, `preview`, `execute`, `cancel`, `driftExceeded`, `run`, `pendingRetentionRequests`; logika penghapusan yang sebelumnya ada di `GovernanceController` pindah ke sini |
| `ExcelWriter` | penulis XLSX streaming (lihat FITUR 14) |
| `ContentVerifier` | aturan verifikasi konten, dipindah dari `ContentController`; ditambah pemeriksaan media (level, bacaan, node, butir) yang belum ada berkasnya → *peringatan* |
| `MediaIntegrity` | pemeriksa aset aktif ↔ berkas (hilang, SHA-256, ukuran) + berkas unggahan tanpa baris; dipakai tombol Pindai dan `gelita:media:scan`. Slot nonaktif (memang belum diunggah) tidak lagi dilaporkan |

### Keputusan

- **Export sinkron.** Berkas dibangun di request yang sama (batas waktu dinaikkan ke 300 detik). Kolom `status` tetap melewati `running` → `done`/`failed`, jadi polling `export.js` tetap berlaku bila kelak dipindah ke antrean.
- **Audit `export` ditulis setelah build**, berisi status, `row_count`, dan SHA-256 — satu baris per permintaan, termasuk yang gagal. Unduhan tetap diaudit `export_download`; pembuangan berkas kedaluwarsa diaudit `export_purge`.
- **Unduhan** hanya dari `writable/exports/` (path di-`realpath` dan dicek berada di folder itu) dengan MIME XLSX/PDF yang benar.
- **Sheet Levels** memakai aturan yang sama dengan `ScoringService::levelScore()` (attempt selesai terbaik per node, rata-rata berbobot `scorable_items`), dihitung per potongan sesi.
- **Filter wilayah** berlaku pada sheet berbasis tantangan (Levels, Challenge Summary, Item Responses, Indicators) dan Raw Events (`game_event_logs.level_id`); tidak pada Participants, Sessions, Audio Usage, Demographic Summary, Feedback.
- **Feedback** diekspor bila terikat sesi dalam cakupan (`participant_feedback.session_id`).
- **PDF**: mPDF (`dejavusans`, A4, margin 20 mm, header/footer bernomor halaman). Bar chart digambar sebagai tabel bersarang — mPDF mengabaikan lebar persen pada `<div>` di dalam sel. `Stage7WiringTest` memastikan setiap `<?= … ?>` di `app/Views/pdf/` lewat `esc()`.
- **`ChallengeService::abandonAttempt($attempt, $payload = [])`** kini menegakkan skor dan bintang 0 dan menulis `via` di payload event (`user_exit` untuk tombol Keluar, `retention` untuk retensi).
- **`AuditLogModel::record()`** aman dipanggil dari CLI: `ip_hash` NULL bila request bukan request web.
- **`writable/exports/`** di-ignore Git (kecuali `index.html`).

### Command

| Command | Perilaku | Kode keluar |
|---|---|---|
| `gelita:content:verify [--strict]` | tabel temuan `ContentVerifier`; cache konten di-flush dulu | 1 bila ada galat (atau peringatan dengan `--strict`) |
| `gelita:retention:run` | `RetentionService::run(null)`; ringkasan per langkah + pratinjau masa simpan | 1 hanya bila retensi melempar galat |
| `gelita:score:recompute --profile CODE [--version V] [--study ID] [--node ID] [--dry-run]` | `ScoringService::recompute()` untuk attempt `completed`; `session_progress.total_score/total_stars` diperbarui; satu transaction per sesi; audit `score_recompute` | 1 bila ada sesi gagal |
| `gelita:media:scan [--check-only]` | `MediaAssetSeeder` (idempoten) lalu `MediaIntegrity`; audit `media_scan` | 1 bila ada temuan |
| `gelita:bank:import FILE [--dry-run] [--staff USERNAME]` | `ContentImportService::preview()`/`import()`; ringkasan per node, galat & peringatan per baris; pelaku audit = admin `--staff` atau admin aktif pertama | 1 bila ada galat |
| `gelita:staff:password USERNAME` | `StaffUserModel::resetToTemporary()`, jalur yang sama dengan reset di `/admin/staf`: sandi sementara 16 karakter dicetak sekali, `must_change_password = 1`, kunci login dibuka; audit `staff_password_reset` dengan `via: cli`. Untuk admin yang lupa sandi tanpa admin lain. Sandi tidak diketik karena `CLI::prompt()` menampilkan input | 1 bila nama pengguna kosong atau tidak ditemukan |
| `gelita:story:update [--force] [--dry-run]` | `App\Libraries\StorySync`: menyelaraskan `dialogues` dengan `app/Database/Seeds/data/story.php` (salinan `docs/naskah-cerita.md`). Sisipkan baris baru, perbarui baris berteks seeder lama, lewati & laporkan suntingan admin (kecuali `--force`), nonaktifkan baris di luar jumlah naskah; audio dan latar tidak disentuh. Satu transaction; cache konten di-flush; audit `content_update` (`target_type: dialogues`, `via: cli`). Jalankan setelah `php spark migrate` (docs/01 §8) | 1 bila transaction gagal (tidak ada yang diubah) |

### Pengujian

Unit test baru (tanpa database): `ExcelWriterTest` (paket terbaca PhpSpreadsheet, teks tidak jadi rumus, nama sheet, pembersihan berkas sementara), `ExportRulesTest` (kolom identitas & rahasia, kunci jawaban admin, kolom fase, sheet guru, cakupan sekolah yang dipaksa, cakupan penghapusan, penjaga drift), `Stage7WiringTest` (command aktif, service terdaftar, escape templat PDF). Diverifikasi manual di MariaDB: guru tidak dapat mengambil Raw Events, kunci jawaban, data/laporan sekolah lain, atau ekspor milik staf lain (403 di API status); eksekusi penghapusan dibatalkan saat baris bertambah 103 → 143; retensi dua kali berturut-turut tidak menggandakan pratinjau; ekspor 150.000 Raw Events selesai dengan memori 18 MB.

---

## Dependency

Dari **01_DATABASE.md**: seluruh tabel, aturan integritas, rumus metrik.

Dari **02_PROJECT_FOUNDATION.md**: konfigurasi, filter, helper, `Config\Gelita`, folder `writable/exports/`.

Dari **03_MODEL_ENTITY.md**: seluruh Model, Entity, delapan Service, dan library `PasswordPolicy`.

Dari **Dokumen Bank Soal GELITA**: isi 15 node yang dipindahkan ke workbook impor (FITUR 12a).

Dari **04_CONTROLLER_ROUTE.md**: route, controller, otorisasi, bentuk request/response.

Dari **05_VIEW_UI.md**: seluruh view dan komponen.

Dari **06_JAVASCRIPT.md**: mesin tantangan, antrean event, adapter chart, editor konten.

---

## Hasil Akhir

Setelah tahap ini selesai, aplikasi berfungsi penuh dari ujung ke ujung:

* Seorang anak dapat menyetujui, mendaftar dengan kata sandi kuat (dibimbing daftar syarat dan keterangan bila belum kuat), masuk kembali dari komputer mana pun, memainkan 15 tantangan di tiga wilayah berurutan, membuka Balai Refleksi, dan mengirim kritik & saran.
* Guru dapat mereset sandi siswa di sekolahnya; siswa wajib membuat sandi baru saat masuk.
* Admin dapat memuat seluruh bank soal dari satu workbook dengan pratinjau galat per baris sebelum data ditulis.
* Seluruh jawaban, perubahan jawaban, petunjuk, audio, dan perpindahan layar tercatat sebagai raw event dengan stempel waktu presisi milidetik.
* Skor, bintang, dan penguasaan indikator dihitung server dengan formula bernomor versi.
* Guru dapat masuk, melihat dashboard sekolahnya, menelusuri dari ringkasan sampai satu event, dan mengunduh XLSX anonim.
* Admin dapat mengelola konten, media, audio, studi, dan rilis; mengekspor data lengkap; serta menghapus data dengan pratinjau, konfirmasi, dan jejak audit.
* Desain pretest–posttest bekerja: butir yang sama, fase yang terpisah, perbandingan yang hanya dilakukan pada versi yang kompatibel.
* Koneksi yang sempat putus tidak menghilangkan event dan tidak menggandakan data.
