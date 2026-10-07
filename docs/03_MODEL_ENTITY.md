# 03_MODEL_ENTITY.md — Model, Entity, dan Lapisan Data

## Tujuan

Dokumen ini menjelaskan lapisan data GELITA seperti yang ada di kode: 28 Model CodeIgniter di
`app/Models/` (satu per tabel; tabel `ci_sessions` dipakai session handler, tanpa Model), 11 Entity di
`app/Entities/`, dua trait bersama, dan library/service yang paling erat dengan keduanya
(`ContentRepository`, `ChallengeService`, `ScoringService`, `PuzzleCut`, `DifficultyRules`, `BankRules`).
Nama tabel, tipe kolom, dan relasi ada di [01_DATABASE.md](01_DATABASE.md); alur fitur yang memakai
lapisan ini ada di [07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md).

## Prinsip umum

### Pembagian tanggung jawab

```
Controller  → validasi input, panggil Service, kembalikan response
Service     → aturan bisnis, transaction, penilaian, hitungan lintas tabel
Model       → akses satu tabel, query khusus, validasi tingkat data
Entity      → satu baris: casting, accessor dwibahasa, bentuk aman untuk klien
```

- Model tidak memanggil service GELITA. Satu-satunya pemanggilan `service()` di Model adalah
  `service('request')` di `AuditLogModel` (hash IP). Entity hanya memakai helper (`media_src()`,
  `media_exists()`), `config('Gelita')`, dan locale request.
- Service boleh memanggil Model dan service lain. Penilaian jawaban hanya ada di `ChallengeService`;
  aturan bintang hanya di `ScoringService`.
- Model dipanggil lewat `model(NamaModel::class)` (instance bersama per request); service lewat
  `service('…')` (`app/Config/Services.php`).

### Model CodeIgniter 4

Pola yang dipakai semua Model:

| Properti | Aturan |
|---|---|
| `$table`, `$primaryKey` | satu tabel; PK `id`, kecuali `SessionProgressModel` (`session_id`, `$useAutoIncrement = false`) |
| `$returnType` | Entity bila baris butuh casting/method (11 Model), selain itu `'array'` |
| `$useTimestamps` | `true` hanya untuk tabel ber-`created_at` + `updated_at` (`$dateFormat = 'datetime'`). Tabel lain mengisi waktunya sendiri (`occurred_at`, `started_at`, `submitted_at`, …) atau memakai default `CURRENT_TIMESTAMP(6)` database |
| `$useSoftDeletes` | hanya `ParticipantModel` (`deleted_at`). `game_event_logs.deleted_at` diisi manual oleh layanan penghapusan, bukan oleh Model |
| `$allowedFields` | kolom yang boleh ditulis; `id`, `created_at`, `updated_at` tidak pernah ada di dalamnya |
| `$validationRules` | validasi tingkat data (label Indonesia untuk pesan panel). Nilai status disimpan sebagai VARCHAR, jadi daftar nilai sah ditegakkan lewat `in_list[…]` di sini |

Aturan validasi khusus berasal dari `App\Validation\GelitaRules`:

| Aturan | Arti | Dipakai |
|---|---|---|
| `valid_engine_type` | ada di `Config\Gelita::$engineTypes` (`puzzle`, `rumpang`, `boleh`, `cari`) | `ChallengeNodeModel` |
| `valid_event_type` | ada di `Config\Gelita::$eventTypes` | `GameEventLogModel` |
| `valid_locale` | ada di `Config\Gelita::$locales` (`id`, `en`) | `GameSessionModel`, `AudioAssetModel` |
| `valid_username`, `not_reserved_username` | `[a-z0-9._]{3,30}`, bukan `Config\Gelita::$reservedUsernames` | `ParticipantModel` |
| `valid_dialogue_pose[character_code]` | pose sah untuk tokoh itu (`Config\Gelita::$characterPoses`) | `DialogueModel` |
| `valid_dialogue_effect` | ada di `Config\Gelita::$dialogueEffects` | `DialogueModel` |

Aturan lain di kelas yang sama (`item_in_attempt`, `json_max_bytes`, `known_npsn`,
`allowed_school_stage`, `strong_password`) dipakai controller, bukan Model.

### Entity

Entity turunan `CodeIgniter\Entity\Entity`, dengan `$casts` untuk id (`int`/`?int`), flag
(`boolean`), dan kolom JSON (`json-array` atau `?json-array`). `$dates` berisi kolom waktu yang
dibaca sebagai `Time`. Entity dipakai oleh: `StaffUser`, `Participant`, `GameSession`, `Level`,
`ChallengeNode`, `ChallengeItem`, `ReadingPassage`, `LibraryPage`, `LibraryMedia`,
`ChallengeAttempt`, `ItemResponse`.

### Trait bersama

**`App\Models\Traits\EncodesJson`** — meng-encode kolom JSON sebelum ditulis, sehingga pemanggil
cukup mengirim array. Model pemakainya wajib mendeklarasikan `protected array $jsonFields` sendiri
dan mendaftarkan callback:

```php
use EncodesJson;

protected $beforeInsert      = ['encodeJson'];
protected $beforeUpdate      = ['encodeJson'];        // tidak dipasang di tabel yang hanya ditambah
protected $beforeInsertBatch = ['encodeJsonBatch'];

protected array $jsonFields = ['config_json'];
```

Pemakai: `ChallengeNodeModel` (`config_json`), `ChallengeItemModel` (`answer_key_json`, `config_json`),
`ChallengeAttemptModel` (`selected_item_ids_json`), `ItemResponseModel` (`first_answer_json`,
`final_answer_json`), `ItemTryModel` (`answer_json`), `GameEventLogModel` (`payload_json`),
`DeviceLogModel` (`client_hints_json`), `AuditLogModel` (`metadata_json`), `DataExportModel` dan
`DataDeletionRequestModel` (`scope_json`). Nilai di-encode dengan `JSON_UNESCAPED_UNICODE`.
`GameEventLogModel::appendBatch()` menulis lewat query builder, jadi meng-encode `payload_json`-nya sendiri.

**`App\Entities\Traits\Bilingual`** — teks konten disimpan berpasangan `<field>_id` / `<field>_en`.
`text(string $field, ?string $locale = null)` mengembalikan versi locale yang diminta (bawaan: locale
request), dan jatuh ke versi Indonesia bila kosong. Dipakai `Level`, `ChallengeNode`, `ChallengeItem`,
`ReadingPassage`, `LibraryPage`, `LibraryMedia`. Untuk baris array (mis. `hints`, `dialogues`) padanannya
helper `tr($row, $field, $locale)`.

### Cache konten: `ContentRepository`

`App\Services\ContentRepository` (`service('contentRepository')`) adalah jalur baca konten permainan.
Setiap nilai disimpan di cache CodeIgniter selama 3600 detik dengan kunci
`content.<nama>.v<content_version>`, memakai `content_version` release aktif
(`GameReleaseModel::activeOrNull()`; `'0'` bila belum ada release).

| Method | Isi | Sumber |
|---|---|---|
| `levels()`, `level($code)`, `levelById($id)` | wilayah aktif urut `sequence` | `LevelModel::ordered()` |
| `nodesForLevel($levelId)`, `node($nodeId)` | tantangan aktif satu wilayah | `ChallengeNodeModel::forLevel()` |
| `itemBank($nodeId)` | seluruh soal aktif satu tantangan, urut variasi lalu `sequence` | `ChallengeItemModel::bankForNode()` |
| `hintsFor($nodeId, ?$itemId)` | petunjuk node, atau petunjuk satu soal | `HintModel::forNode()` / `forItem()` |
| `dialogues(?$levelId, $context)` | baris cerita global (`level_id` NULL) atau per wilayah | `DialogueModel::global()` / `forLevel()` |
| `libraryPages($levelId)` | halaman Pustaka beserta medianya (`LibraryPage::setLoadedMedia()`) | `LibraryPageModel` + `LibraryMediaModel::forPages()` |
| `passagesForLevel($levelId)` | bacaan aktif satu wilayah | `ReadingPassageModel::forLevel()` |
| `passagesForIds($ids)` | bacaan per id, satu query, **tanpa** cache | `ReadingPassageModel::forIds()` |
| `mediaMap()`, `mediaKeyMap()` | `id → storage_path` dan `asset_key → storage_path` media aktif (juga disimpan per request) | `MediaAssetModel::map()` / `activeKeyMap()` |
| `version()` | `content_version` yang sedang dipakai | |
| `flush()` | menghapus kunci `content.*` (atau seluruh cache bila handler tidak mendukung `deleteMatching()`) | |

`flush()` dipanggil setiap kali konten, media, atau release berubah: panel konten dan media,
aktivasi release, `ContentImportService` sesudah impor, dan `SystemResetService`. Helper
`media_src()`, `media_exists()`, `media_key_src()` membaca peta media dari sini.

## Kelompok 1 — Peserta dan sesi

Ditulis terutama oleh `SessionService` (pendaftaran, masuk, ganti sandi, sesi) dan `SchoolDirectory`
(sekolah).

| Model | Tabel | returnType | Timestamps |
|---|---|---|---|
| `SchoolModel` | `schools` | array | ya |
| `ParticipantModel` | `participants` | `Participant` | ya, + soft delete |
| `GameSessionModel` | `game_sessions` | `GameSession` | tidak |
| `SessionProgressModel` | `session_progress` | array | tidak |
| `DeviceLogModel` | `device_logs` | array | tidak (`created_at` dari database) |

### `SchoolModel`

- allowedFields: `code` (NPSN), `name`, `level`, `stage`, `status`, `country_code`, `province_code`,
  `district_code`, `subdistrict_name`, `village_name`, `is_active`, `is_verified`, `match_key`,
  `merged_into_id`.
- Validasi: `name` wajib, maks. 200.
- Baris resmi (`is_verified = 1`, NPSN di `code`) berasal dari `gelita:schools:import`; baris
  `is_verified = 0` adalah nama sekolah ketikan siswa tanpa NPSN yang cocok. Pencocokan, pencarian,
  penggabungan, dan verifikasi ada di `App\Services\SchoolDirectory`, bukan di Model.
- `inUseList()` — sekolah aktif yang dipakai peserta (belum dihapus) atau staf, urut nama; untuk
  pilihan filter panel, ekspor, dan akun guru. Tabel berisi puluhan ribu sekolah resmi, jadi tidak ada
  method "semua sekolah".

### `ParticipantModel` dan Entity `Participant`

allowedFields: `participant_code`, `username`, `password_hash`, `must_change_password`,
`display_name`, `age`, `gender`, `class_level`, `school_id`, `school_name_snapshot`, `country_code`,
`country_name_snapshot`, `province_code`, `province_name_snapshot`, `district_code`,
`district_name_snapshot`, `pw_first_submit_criteria`, `pw_weak_submit_count`, `failed_login_count`,
`locked_until`, `last_login_at`, `password_changed_at`, `assented_at`, `intro_seen_at`.

Validasi:

```
participant_code : required|max_length[40]|is_unique[participants.participant_code,id,{id}]
username         : required|valid_username|not_reserved_username|is_unique[participants.username,id,{id}]
age              : permit_empty|integer|greater_than[4]|less_than[81]
gender           : permit_empty|in_list[laki-laki,perempuan]
class_level      : permit_empty|max_length[20]
display_name     : permit_empty|max_length[150]
```

Batas sasaran pendaftaran (kelas 5, umur `Config\Gelita::$registrationAge`, jenjang sekolah) diperiksa
controller pendaftaran; Model hanya menjaga bentuk data.

| Method | Perilaku |
|---|---|
| `generateCode()` | kode berurutan `GLT-000001` (helper `participant_code()`), dari `MAX(id)` + 1; bila sudah terpakai dicoba sampai 5 kali, lalu exception. Kode selalu dibuat server |
| `findByCode($code)` | cari menurut `participant_code` |
| `findByUsername($username)` | cari menurut nama pengguna yang dinormalkan |
| `normalizeUsername($username)` | `mb_strtolower(trim())` |
| `usernameAvailable($username)` | belum dipakai, **termasuk** akun yang sudah dihapus lunak |
| `setPassword($id, $plain, $mustChange = false)` | satu-satunya jalur menulis `password_hash` sesudah pendaftaran; mengisi `password_changed_at` dan `must_change_password` |
| `markIntroSeen($id)` | mengisi `intro_seen_at` sekali (idempoten); `true` bila baru saja ditandai |
| `registerFailedLogin($id)` | menambah `failed_login_count`; mencapai `loginThrottle['participant']['max_fail']` (8) → `locked_until` = sekarang + `lock_minutes` (5) dan pencacah kembali 0 |
| `clearFailedLogin($id)` | pencacah 0, `locked_until` NULL, `last_login_at` sekarang |
| `scopedForStaff(?$schoolId)` | query builder; NULL (admin) tanpa filter, terisi (guru) hanya sekolah itu |
| `cohortSummary($filters)` | `total`, `by_gender`, `by_class_level`, `by_province` (dasbor lama; tampilan dasbor Dok 3 tidak memakainya) |

`cohortSummary()` membuang peserta yang dihapus lunak dan menerima `school_id`, `class_level`,
`province_code`, `date_from`, `date_to`; filter tanggal berlaku pada `participants.created_at`, berbeda
dengan [filter penelitian baku](#filter-penelitian-baku). Sebaran umur dan metrik sandi
(`ageDistribution()`, `digitalSecurityStats()`) dihapus pada Dok 3: nilainya ada mentah di lembar
Peserta (`age`, `pw_first_submit_criteria`, `pw_weak_submit_count`).

Entity `Participant`:

- casts: `id` int, `age` ?int, `school_id` ?int, `must_change_password` boolean, `failed_login_count`
  int, `pw_first_submit_criteria` ?int, `pw_weak_submit_count` int; `$dates` termasuk `deleted_at`,
  `intro_seen_at`, `assented_at`.
- `anonLabel()` (kode peserta, untuk tampilan anonim), `label()` (`display_name`, atau `username`).
- `verifyPassword($plain)`, `isLocked()`, `lockMinutesLeft()`, `mustChangePassword()`.
- `hasSeenIntro()` (cerita pembuka sudah ditonton), `isActive()` (`deleted_at` kosong).
- `toSafeArray()` — satu-satunya bentuk yang boleh dikirim ke view/API: `id`, `participant_code`,
  `username`, `display_name`, `age`, `gender`, `class_level`, `school_name`. Jangan memakai
  `toArray()` untuk respons, karena ikut membawa `password_hash` dan kolom throttle.

`assented_at` diisi saat siswa mencentang persetujuan di langkah terakhir pendaftaran
(`SessionService::registerAndStart()` menolak pendaftaran tanpa centang).

### `GameSessionModel` dan Entity `GameSession`

Satu sesi = satu masa bermain seorang siswa pada studi aktif, dengan release yang dikunci saat sesi dibuat.

- allowedFields: `session_code`, `participant_id`, `study_id`, `release_id`, `locale`, `status`,
  `started_at`, `last_active_at`, `ended_at`, `duration_ms`, `last_level_id`, `last_challenge_node_id`,
  `device_type`, `os_name`, `browser_name`, `screen_size`, `is_touch`, `app_client_version`, `ip_hash`.
- Validasi: `session_code` wajib & unik (maks. 64), `participant_id`/`study_id`/`release_id` wajib,
  `locale` `valid_locale`, `status` `in_list[active,paused,completed,abandoned]`.
- `session_code` = `random_code(16)` (32 heksadesimal), pengenal internal untuk ekspor dan log; identitas
  login siswa dipegang session CI4 (`participant_id`, `game_session_id`). Kolom perangkat ringkas diisi
  dari `DeviceProfile::sessionSummary()`; rincian perangkat ada di `device_logs`. IP hanya disimpan
  sebagai `hash_ip()`.

| Method | Perilaku |
|---|---|
| `findByCode($sessionCode)` | cari menurut `session_code` |
| `resumable($participantId, $studyId)` | sesi `active`/`paused` terbaru yang dapat dilanjutkan (dipakai saat masuk) |
| `latestCompleted($participantId, $studyId)` | sesi `completed` terakhir |
| `touch($sessionId)` | hanya memperbarui `last_active_at` |
| `markStale($idleMinutes)` | sesi `active` yang menganggur → `paused`; mengembalikan jumlah baris (dipakai `gelita:retention:run` dengan `Config\Gelita::$sessionIdleMinutes`) |

Entity `GameSession`: casts `id`, `participant_id`, `study_id`, `release_id`, `duration_ms` int;
`last_level_id`, `last_challenge_node_id` ?int; `is_touch` ?boolean. Method `isActive()`,
`isFinished()` (`completed` atau `abandoned`), `resolvedLocale()` (locale sesi bila didukung, selain
itu `id`). Semua teks permainan memakai `resolvedLocale()`.

### `SessionProgressModel`

- PK `session_id` (`$useAutoIncrement = false`); allowedFields `session_id`, `current_level_id`,
  `current_node_id`, `unlocked_level_sequence`, `completed_nodes`, `completed_levels`, `total_stars`.
- `ensure($sessionId)` — mengembalikan baris progres, membuatnya bila belum ada
  (`unlocked_level_sequence = 1`, hitungan 0).
- Baris ini ringkasan yang dihitung ulang `ChallengeService` setiap pengerjaan ditutup (lihat
  [Kelompok 4](#kelompok-4--data-penelitian)): `completed_nodes` = jumlah Serpihan Cahaya (paling
  banyak 12), `total_stars` = jumlah bintang terbaik per tantangan.

### `DeviceLogModel`

- Satu baris setiap siswa mendaftar, masuk, atau melanjutkan sesi; hanya ditambah.
- `VIA = ['register', 'login', 'resume']`; validasi `session_id`, `participant_id` wajib dan `via`
  `in_list[register,login,resume]`.
- allowedFields: `session_id`, `participant_id`, `via`, `user_agent`, `device_type`, `device_brand`,
  `device_model`, `os_name`, `os_version`, `os_platform`, `browser_name`, `browser_version`,
  `browser_engine`, `is_bot`, `client_hints_json`, `screen_w`, `screen_h`, `viewport_w`, `viewport_h`,
  `pixel_ratio`, `touch_points`, `orientation`, `is_standalone`, `language`, `timezone`, `corrected`,
  `detector`, `ip_hash`.
- `record($sessionId, $participantId, $via, $profile)` — menulis hasil
  `App\Libraries\DeviceProfile::capture()` (hanya kunci yang ada di allowedFields; `via` tak dikenal
  menjadi `login`). `forParticipant($participantId)` — urut waktu, untuk profil siswa di panel.

## Kelompok 2 — Konten permainan

Konten dibaca permainan lewat `ContentRepository` dan ditulis oleh panel konten, `ContentImportService`
(workbook bank soal, `gelita:bank:import`), `ContentSeeder`, dan `SystemResetService`.

| Model | Tabel | returnType | Timestamps |
|---|---|---|---|
| `LevelModel` | `levels` | `Level` | ya |
| `ChallengeNodeModel` | `challenge_nodes` | `ChallengeNode` | ya |
| `ChallengeItemModel` | `challenge_items` | `ChallengeItem` | ya |
| `ReadingPassageModel` | `reading_passages` | `ReadingPassage` | ya |
| `HintModel` | `hints` | array | tidak |
| `DialogueModel` | `dialogues` | array | tidak |
| `LibraryPageModel` | `library_pages` | `LibraryPage` | ya |
| `LibraryMediaModel` | `library_media` | `LibraryMedia` | ya |
| `LearningIndicatorModel` | `learning_indicators` | array | tidak |

### `LevelModel` dan Entity `Level`

Tiga wilayah berurutan: Temanggung (`mudah`), Magelang (`sedang`), Wonosobo (`sulit`).

- allowedFields: `sequence`, `code`, `name_id`, `name_en`, `difficulty`, `focus_id`, `focus_en`,
  `cp_id`, `cp_en`, `tp_id`, `tp_en`, `intro_id`, `intro_en`, `map_media_id`, `background_media_id`,
  `badge_media_id`, `map_x`, `map_y`, `is_active`.
- Validasi: `sequence` dan `code` wajib & unik, `name_id`/`name_en` wajib (maks. 100), `difficulty`
  `in_list[mudah,sedang,sulit]`. `difficulty` menentukan aturan `DifficultyRules` untuk semua
  tantangan wilayah itu.
- `ordered()` (aktif, urut `sequence`), `findByCode($code)`, `findBySequence($sequence)`.
- Entity `Level`: `use Bilingual`; casts `id`, `sequence` int, `map_media_id`, `background_media_id`,
  `badge_media_id` ?int, `map_x`, `map_y` float, `is_active` boolean.

### `ChallengeNodeModel` dan Entity `ChallengeNode`

Satu baris = satu tantangan. Setiap wilayah punya tepat 4 tantangan aktif berurutan:
1 Susun Gambar (`puzzle`), 2 Isi Rumpang (`rumpang`), 3 Benar atau Salah (`boleh`),
4 Cari Objek (`cari`) — 12 tantangan, 12 Serpihan Cahaya.

- allowedFields: `level_id`, `sequence`, `engine_type`, `variant_code`, `title_id`, `title_en`,
  `instruction_id`, `instruction_en`, `description_id`, `description_en`, `indicator_id`,
  `background_media_id`, `scene_media_id`, `audio_intro_id`, `audio_intro_en_id`, `config_json`,
  `map_x`, `map_y`, `content_version`, `is_active`.
- Validasi: `level_id` wajib, `sequence` `greater_than[0]|less_than[5]` (1–4), `engine_type`
  `valid_engine_type`, `title_id`/`title_en` wajib (maks. 250).
- `forLevel($levelId)` (aktif, urut `sequence`), `findByLevelSequence($levelId, $sequence)`,
  `allActive()` (semua tantangan aktif, urut wilayah lalu `sequence`).
- Kode tantangan di workbook dan editor (`tmg-1` … `wnb-4`) dibentuk helper `node_ref()` dari
  `Config\Gelita::$levelPrefixes`; kode ini tidak disimpan sebagai kolom.

`config_json` tantangan:

| Kunci | Engine | Arti |
|---|---|---|
| `selection` | semua | selalu `variant`: setiap pengerjaan memakai satu variasi soal |
| `variants` | semua | pengaturan per variasi, keyed by `variant_group`: `title_id`, `title_en`, dan untuk Isi Rumpang `distractors` |
| `as_reading` | `rumpang` | soal satu variasi tampil sebagai satu bacaan berurutan (bawaan `true`) |
| `use_word_bank` | `rumpang` | jawaban dipilih dari bank kata (bawaan `true`) |
| `distractor_count` | `rumpang` | jumlah kata pengecoh yang ikut di bank kata (mudah 0, sedang 2, sulit 2) |
| `verdict_options` | `boleh` | bawaan `['benar', 'salah']` |

Entity `ChallengeNode` (`use Bilingual`):

- casts: `id`, `level_id`, `sequence` int; `indicator_id`, `background_media_id`, `scene_media_id`,
  `audio_intro_id`, `audio_intro_en_id` ?int; `config_json` ?json-array; `map_x`, `map_y` float;
  `is_active` boolean.
- `config($key, $default = null)` — nilai `config_json`, lalu `$default`, lalu bawaan engine dari
  `defaultConfig($engine)` (static).
- `isReading()` — `rumpang` yang tampil sebagai bacaan.
- `verdictOptions()` — selalu `['benar', 'salah']`; dipakai penilai dan payload `boleh`.
- `variants()` — `config_json['variants']` yang sah, keyed by `variant_group`.
- `variantTitle(?$group, $locale)` — judul variasi pada bahasa itu, jatuh ke `title_id` variasi lalu
  judul tantangan.
- `distractors($locale, ?$group)` — kata pengecoh Isi Rumpang satu variasi
  (`variants[$group]['distractors']`; tiap entri teks biasa atau `{id, en}` / `{text_id, text_en}`),
  kosong bila `$group` NULL.

### `ChallengeItemModel` dan Entity `ChallengeItem`

Satu baris = satu soal. Soal dikelompokkan per variasi (`variant_group` `a`, `b`, `c`): tiga variasi
untuk Susun Gambar, Isi Rumpang, dan Benar atau Salah; satu variasi (satu adegan) untuk Cari Objek.

- allowedFields: `challenge_node_id`, `item_key`, `sequence`, `variant_group`, `interaction_type`,
  `prompt_id`, `prompt_en`, `source_text_id`, `source_text_en`, `passage_id`, `answer_key_json`,
  `media_asset_id`, `audio_prompt_id`, `audio_prompt_en_id`, `indicator_id`, `bloom_level`,
  `config_json`, `reference_source`, `review_status`, `review_note`, `scorable`, `is_active`.
- Validasi: `challenge_node_id` wajib; `item_key` wajib (maks. 100); `interaction_type`
  `in_list[puzzle_arrange,fill_blank_bank,verdict_card,find_object]`; `variant_group`
  `permit_empty|alpha_numeric|max_length[10]`; `review_status` `in_list[draft,needs_verification,verified]`;
  `bloom_level` `in_list[C1,C2,C3,C4,C5,C6]`.

Jenis soal per tantangan (`BankRules::INTERACTION`) dan bentuk kunci jawaban:

| Engine | `interaction_type` | `answer_key_json` | Kunci `config_json` |
|---|---|---|---|
| `puzzle` | `puzzle_arrange` | `{"order": [0, 1, …]}` — nomor keping urut posisi benar | `grid`, `shape` |
| `rumpang` | `fill_blank_bank` | `{"text_id": "…", "text_en": "…"}` | — |
| `boleh` | `verdict_card` | `{"verdict": "benar" \| "salah"}` | `skill` (kode `DifficultyRules::SKILLS`) |
| `cari` | `find_object` | `{"target": true \| false}` | `x`, `y`, `w` (persen adegan), `decoy`, `wrong_feedback_id`, `wrong_feedback_en` |

Soal Benar atau Salah merujuk bacaannya lewat `passage_id`; potongan Isi Rumpang memuat satu `___`
di `prompt_*`. Objek pengecoh Cari Objek (`config_json.decoy = true`) ikut tampil di adegan tetapi
tidak pernah ditanyakan.

| Method | Perilaku |
|---|---|
| `bankForNode($nodeId)` | soal aktif satu tantangan, urut `variant_group`, `sequence`, `id` |
| `groupByVariant($bank)` (static) | kelompokkan per variasi (kosong → `a`), urut nama variasi lalu `sequence` |
| `pickVariant($nodeId, $excludeGroups, ?$bank)` | satu variasi untuk pengerjaan baru: variasi yang sudah pernah didapat siswa dibuang selama masih ada yang lain, lalu dipilih acak (`random_int()`, tanpa benih). Mengembalikan `{group, items}`; objek pengecoh `cari` diletakkan di akhir |
| `hasResponses($itemId)` | soal sudah pernah dijawab; panel dan impor menolak mengubah kunci jawabannya |
| `findByKey($itemKey)` | cari menurut `item_key` |

Entity `ChallengeItem` (`use Bilingual`):

- casts: `id`, `challenge_node_id`, `sequence` int; `variant_group` ?string; `passage_id`,
  `media_asset_id`, `audio_prompt_id`, `audio_prompt_en_id`, `indicator_id` ?int; `answer_key_json`,
  `config_json` ?json-array; `scorable`, `is_active` boolean.
- `answerKey(?$key)` — isi `answer_key_json` (seluruhnya, atau satu kunci). Hanya untuk server.
- `conf($key, $default)` — satu kunci `config_json`.
- `isDecoy()` — `config_json.decoy === true`.
- `position()` — `{x, y, w}` objek Cari Objek dalam persen (bawaan 50, 50, 12).
- `promptAudioId($locale)` — id `audio_assets` narasi petunjuk Cari Objek pada bahasa itu
  (`audio_prompt_en_id` untuk `en`, selain itu `audio_prompt_id`); tidak jatuh ke bahasa lain karena
  suaranya harus sama dengan teks yang tampil.
- `verdict()` — `benar`/`salah` dari kunci, huruf kecil.
- `puzzleLayout(?$difficulty)` — `{grid, shape}` Susun Gambar lewat `PuzzleCut::resolve()`.
- `wrongFeedback($locale)` — umpan balik saat objek itu diketuk keliru (jatuh ke `wrong_feedback_id`).
- `safeConfig()` — hanya kunci tampilan `x`, `y`, `w`, `grid`, `shape`.
- `toPlayerArray($locale)` — bentuk soal untuk klien: `id`, `item_key`, `interaction_type`, `prompt`,
  `source_text`, `passage_id`, `media`, `config` (Susun Gambar: `puzzleLayout()` + `safeConfig()`).
  **Tidak** memuat `answer_key_json`, `decoy`, `wrong_feedback_*`, `skill`, `scorable`, Bloom, atau status
  pemeriksaan.

### `ReadingPassageModel` dan Entity `ReadingPassage`

Bacaan bersama untuk Benar atau Salah (satu bacaan per variasi, 9 bacaan).

- allowedFields: `level_id`, `passage_key`, `title_id`, `title_en`, `body_id`, `body_en`,
  `media_asset_id`, `reference_source`, `is_active`.
- Validasi: `level_id` wajib, `passage_key` wajib & unik (maks. 60), `body_id`/`body_en` wajib.
  Paragraf dipisah baris kosong; jumlah paragraf diperiksa `BankRules`.
- `forLevel($levelId)` (urut `passage_key`), `findByKey($key)`, `forIds($ids)` (satu query, keyed by id).
- Entity: `use Bilingual`; casts `id`, `level_id` int, `media_asset_id` ?int, `is_active` boolean;
  `toPlayerArray($locale)` → `id`, `key`, `title`, `body`, `media`.

### `HintModel`

- allowedFields: `challenge_node_id`, `challenge_item_id`, `sequence`, `text_id`, `text_en`, `is_active`;
  validasi `sequence` wajib, `text_id`/`text_en` wajib.
- `forNode($nodeId)` — petunjuk tingkat tantangan (`challenge_item_id` NULL); `forItem($itemId)` —
  petunjuk satu soal. Teks petunjuk baru dikirim ke klien saat dibuka (`ChallengeService::useHint()`).

### `DialogueModel`

Baris cerita dan narasi dari `app/Database/Seeds/data/story.php` (salinan `docs/naskah-cerita.md`).

- allowedFields: `level_id`, `context_code`, `sequence`, `character_code`, `pose`, `effect`,
  `title_id`, `title_en`, `text_id`, `text_en`, `audio_id_asset_id`, `audio_en_asset_id`,
  `background_media_id`, `is_active`.
- Validasi: `context_code` wajib, `sequence` wajib, `character_code` wajib, `pose`
  `valid_dialogue_pose[character_code]` (NULL = pose `idle`), `effect` `valid_dialogue_effect`,
  `text_id`/`text_en` wajib.
- Konteks (`Config\Gelita::$dialogueContexts`): global (`level_id` NULL) `intro`, `map_intro`,
  `ending`; per wilayah `region_intro`, `level_open`, `level_done`.
- `global($context)` (urut `sequence` lalu `id`), `forLevel($levelId, $context = 'level_open')`.

### `LibraryPageModel` / `LibraryMediaModel` dan Entity-nya

Pustaka Kedu: halaman per wilayah, masing-masing dengan galeri gambar/video.

`LibraryPageModel`:

- allowedFields `level_id`, `sequence`, `title_id`, `title_en`, `body_id`, `body_en`, `is_active`;
  validasi `level_id`, `sequence`, `title_id`/`title_en` (maks. 250) wajib.
- `forLevel($levelId)` — halaman aktif urut `sequence`.

`LibraryMediaModel`:

- allowedFields `library_page_id`, `sequence`, `media_kind`, `media_asset_id`, `external_url`,
  `poster_media_id`, `caption_id`, `caption_en`, `credit`, `is_active`.
- Validasi: `library_page_id`, `sequence` wajib; `media_kind` `in_list[image,video]`; `external_url`
  maks. 1000; `caption_*` maks. 500; `credit` maks. 300.
- `forPages($pageIds, $activeOnly = true)` — media banyak halaman sekaligus (satu query), keyed by
  `library_page_id`, urut `sequence`.

Entity `LibraryMedia` (`use Bilingual`; casts `id`, `library_page_id`, `sequence` int,
`media_asset_id`, `poster_media_id` ?int, `is_active` boolean):

- `resolve()` — bentuk siap-render `{kind, provider, src, embed, href, label, poster}`: berkas unggahan
  (`provider = upload`) bila `media_asset_id` terisi dan medianya aktif, selain itu tautan luar yang
  diurai `App\Libraries\MediaLink::parse()`. `null` bila berkas belum ada/nonaktif atau tautan tidak sah.

Entity `LibraryPage` (`use Bilingual`; casts `id`, `level_id`, `sequence` int, `is_active` boolean):

- `setLoadedMedia($media)` / `loadedMedia()` — media yang dipasang `ContentRepository::libraryPages()`.
- `gallery($locale)` — galeri siap-render urut `sequence`: hasil `resolve()` + `id`, `caption`, `credit`;
  media yang tidak dapat dirender dibuang.

### `LearningIndicatorModel`

- allowedFields `code`, `name_id`, `name_en`, `description_id`, `description_en`, `domain`, `is_active`;
  validasi `code` wajib & unik, `name_id`/`name_en` wajib.
- `map()` — indikator aktif keyed by `code` (editor konten dan impor workbook).

### Aturan tingkat kesulitan: `DifficultyRules`

`App\Libraries\DifficultyRules` adalah satu-satunya sumber aturan per `levels.difficulty`. Kelasnya murni
PHP (tanpa framework) karena juga dibaca `docs/bank-soal/build-workbook.php`.

| Aturan | mudah | sedang | sulit |
|---|---|---|---|
| Susun Gambar (`puzzle()`) | 3 × 3 `square` | 4 × 4 `square` | 4 × 4 `jigsaw` |
| Isi Rumpang (`reading()`): rumpang / pengecoh | 3 / 0 | 5 / 2 | 7 / 2 |
| Isi Rumpang: Bloom | C1–C2 | C2–C3 | C3–C4 |
| Benar atau Salah (`trueFalse()`): paragraf / pernyataan | 1 / 3 | 2 / 5 | 3 / 6 |
| Benar atau Salah: Bloom | C1–C2 | C2–C4 | C4–C5 |
| Cari Objek (`hunt()`): dicari / pengecoh | 4 / 4 | 4 / 6 | 4 / 8 |
| Cari Objek: Bloom petunjuk | C1–C2 | C2–C3 | C3–C4 |

Konstanta lain: `BLOOM` (C1–C6 → nama), `SHAPES` (`square`, `jigsaw`), `SKILLS` (keterampilan Benar
atau Salah, tiga per tingkat), `DEFAULT = 'mudah'` (tingkat tak dikenal dianggap mudah). Method bantu:
`for()`, `normalize()`, `bloomRange()`, `isBloom()`, `bloomInRange()`, `bloomLabel()`,
`bloomRangeLabel()`, `isSkill()`, `skillLabel()`, `layoutLabel()`.

### Potongan Susun Gambar: `PuzzleCut`

`App\Libraries\PuzzleCut` (murni) memotong satu gambar utuh menjadi n × n keping tanpa membuat berkas
baru; browser menggambar tiap keping sebagai SVG dengan clip-path.

- `resolve($itemConfig, ?$difficulty)` — `{grid, shape}` dari `config_json.grid/shape` soal, atau aturan
  tingkat bila belum diisi; ukuran di luar aturan tingkat tanpa bentuk tercatat menjadi `square`; grid
  dibatasi 2–6. `ContentImportService` menetapkan `grid` dan `shape` sekali saat soal dibuat, sehingga
  soal yang sudah ada tetap memakai ukuran dan kuncinya sendiri.
- `pieces($grid, $shape, $seed)` — path dan `viewBox` tiap keping, urut baris dari kiri atas (indeks
  keping = posisi benarnya). Bentuk jigsaw diacak dengan benih `item_key`, jadi semua siswa melihat
  potongan yang sama untuk gambar yang sama.
- `overhang($shape)`, `boardInset($grid, $shape)` — ukuran tonjolan jigsaw untuk CSS.
- Konstanta `CELL = 100`, `MARGIN = 35`.

### Pemeriksaan bank soal: `BankRules`

`App\Libraries\BankRules` (murni) memeriksa bank soal kelas 5 dalam satu bentuk node bersama
(`ref`, `engine`, `difficulty`, `distractor_count`, `variants` → `distractors`, `items`). Pemakainya:
`docs/bank-soal/build-workbook.php`, `ContentImportService`, `App\Libraries\ContentVerifier`
(`gelita:content:verify` dan panel), serta uji.

- `node($node, $passages)` — semua pemeriksaan satu tantangan; `region($scope, $nodes)` — wilayah wajib
  punya 4 tantangan aktif berurutan `ENGINES` (`puzzle`, `rumpang`, `boleh`, `cari`);
  `duplicateKeys($keys)` — `item_key` ganda.
- Isi aturan: jumlah variasi aktif (`VARIANTS`: 3, 3, 3, dan paling sedikit 1 untuk `cari`); jenis soal
  sesuai `INTERACTION`; Bloom wajib untuk `fill_blank_bank`, `verdict_card`, dan target `find_object`
  (di luar rentang tingkat = peringatan); Susun Gambar tepat satu gambar per variasi; Isi Rumpang jumlah
  rumpang dan pengecoh tepat, satu `___` per potongan, jawaban dan pengecoh tidak kembar; Benar atau
  Salah jumlah pernyataan tepat, satu bacaan per variasi dengan jumlah paragraf sesuai, kunci `benar`/`salah`,
  `skill` sah; Cari Objek jumlah benda dan pengecoh tepat, posisi di dalam adegan, tumpang tindih paling
  banyak `MAX_OVERLAP` (20 %, tinggi benda = lebar × `OBJECT_ASPECT` 16/9), pengecoh wajib punya
  `wrong_feedback_id`.
- Hasil: daftar `{level: error|warning, node, variant, item, message}`; galat menolak impor dan
  menggagalkan verifikasi. `format($problem)` membentuk satu baris teks untuk CLI.

Dengan aturan ini bank soal final berisi 126 soal (108 dinilai + 18 pengecoh) dalam 30 variasi,
9 bacaan, dan 12 kata pengecoh; sumbernya `docs/bank-soal/`.

## Kelompok 3 — Media dan audio

| Model | Tabel | returnType | Timestamps |
|---|---|---|---|
| `MediaAssetModel` | `media_assets` | array | ya |
| `AudioAssetModel` | `audio_assets` | array | tidak |

### `MediaAssetModel`

Satu baris = satu slot berkas (gambar, audio, video, frame karakter). Slot dibuat `MediaAssetSeeder`
dan impor; berkasnya diunggah lewat panel (Media / Gambar & suara) atau dipasang command.

- allowedFields: `asset_key`, `asset_type`, `storage_path`, `mime_type`, `file_size`, `sha256`,
  `width_px`, `height_px`, `locale`, `credit`, `label`, `description`, `version`, `is_active`,
  `is_placeholder`.
- Validasi: `asset_key` wajib & unik (maks. 160), `asset_type` `in_list[image,audio,video,sprite_frame]`,
  `storage_path` wajib (maks. 500), `mime_type` wajib.
- `is_active = 1` berarti berkasnya ada dan boleh ditampilkan; media nonaktif tidak masuk peta media,
  sehingga `media_src()` jatuh ke gambar pengganti dan `LibraryMedia::resolve()` mengembalikan `null`.
- `is_placeholder = 1` menandai gambar contoh (mockup) buatan `App\Libraries\MockupGenerator`
  (`php spark gelita:mockups`) untuk 45 slot gambar tantangan. Setiap berkas yang disimpan lewat
  `App\Libraries\MediaStore` (mis. unggahan panel) dianggap gambar asli dan mengembalikannya ke 0; `gelita:content:verify --siap-penelitian` gagal selama masih ada gambar contoh.

| Method | Perilaku |
|---|---|
| `map()` | `id → storage_path` media aktif (dibungkus cache oleh `ContentRepository::mediaMap()`) |
| `activeKeyMap()` | `asset_key → storage_path` media aktif, untuk aset yang dirujuk lewat kunci resmi (latar, karakter, peta, logo) |
| `findByKey($key)` | cari menurut `asset_key` |
| `keyMap()` | `asset_key → id` semua baris, untuk impor workbook |
| `placeholders()` | semua baris gambar contoh, urut `asset_key` |
| `placeholderCount()` | jumlah gambar contoh yang masih aktif |

Ukuran piksel wajib per pola `asset_key` ada di `Config\Gelita::$assetSizes`.

### `AudioAssetModel`

Rekaman narasi dan suara, masing-masing menunjuk satu berkas `media_assets` (100 rekaman narasi
per bahasa: 88 baris cerita + 12 petunjuk Cari Objek).

- allowedFields: `media_asset_id`, `locale`, `character_code`, `context_code`, `transcript`,
  `production_method`, `voice_profile`, `duration_ms`, `approval_status`, `approved_by`, `approved_at`,
  `version`.
- Validasi: `media_asset_id` wajib, `locale` `valid_locale`, `context_code` wajib (maks. 80),
  `transcript` wajib, `approval_status` `in_list[draft,review,approved,rejected]`.
- Tabel ini tidak punya `is_active`. Audio hanya diputar bila `approval_status = approved` **dan**
  media induknya aktif; aturan itu ditegakkan helper `audio_src()`, yang mengembalikan NULL untuk
  rekaman lain. Rekaman hasil `gelita:narration:import` masuk sebagai draft dan disetujui di panel
  (menu Rekaman narasi).
- `approvedFor($context, $locale)` — rekaman disetujui terbaru untuk satu konteks;
  `approvedForCharacter($characterCode, $locale)` — semua rekaman disetujui milik satu tokoh.
- Rujukan ke rekaman: `dialogues.audio_id_asset_id` / `audio_en_asset_id`, `challenge_nodes.audio_intro_id` /
  `audio_intro_en_id`, `challenge_items.audio_prompt_id` / `audio_prompt_en_id`
  (`ChallengeItem::promptAudioId()`). Telemetry pemutarannya ada di `audio_usage_events`
  ([Kelompok 4](#kelompok-4--data-penelitian)).

## Kelompok 4 — Data penelitian

Data mentah permainan. Tidak ada skor, akurasi, atau indeks yang dihitung dan disimpan; satu-satunya
nilai olahan adalah bintang (hadiah di layar siswa). Pengolahan dilakukan peneliti dari ekspor.

| Model | Tabel | returnType | Ditulis oleh |
|---|---|---|---|
| `ChallengeAttemptModel` | `challenge_attempts` | `ChallengeAttempt` | `ChallengeService`, `ScoringService`, `RetentionService` |
| `ItemResponseModel` | `item_responses` | `ItemResponse` | `ChallengeService` |
| `ItemTryModel` | `item_tries` | array | `ChallengeService` (hanya ditambah) |
| `GameEventLogModel` | `game_event_logs` | array | `EventService` (hanya ditambah) |
| `AudioUsageEventModel` | `audio_usage_events` | array | `EventService::recordAudio()` |
| `ParticipantFeedbackModel` | `participant_feedback` | array | Balai Refleksi (`ReflectionController`) |

Hanya `item_responses` yang memakai `$useTimestamps`; tabel lain mengisi waktunya sendiri.

### `ChallengeAttemptModel` dan Entity `ChallengeAttempt`

Satu baris = satu pengerjaan satu tantangan dengan satu variasi soal, dari dibuka sampai selesai atau
ditinggalkan.

- allowedFields: `session_id`, `challenge_node_id`, `attempt_no`, `variant_group`, `status`,
  `completion_reason`, `selected_item_ids_json`, `scorable_items`, `first_pass_correct`,
  `final_correct`, `check_count`, `max_tries`, `hint_count`, `answer_change_count`, `audio_use_count`,
  `stars`, `started_at`, `completed_at`, `duration_ms`.
- Validasi: `session_id`, `challenge_node_id` wajib; `status` `in_list[in_progress,completed,abandoned]`;
  `completion_reason` `in_list[solved,tries_exhausted]`.
- `max_tries` disalin dari `Config\Gelita::$maxTries` (3) saat pengerjaan dibuat, sehingga batas yang
  berlaku tetap tercatat. `selected_item_ids_json` = id soal variasi itu (target dulu, pengecoh `cari`
  di akhir); `scorable_items` = soal dinilai, tanpa pengecoh.

| Method | Perilaku |
|---|---|
| `nextAttemptNo($sessionId, $nodeId)` | nomor pengerjaan berikutnya untuk tantangan itu dalam sesi |
| `currentInProgress($sessionId, $nodeId)` | pengerjaan `in_progress` terakhir (dilanjutkan saat tantangan dibuka lagi) |
| `completedForSession($sessionId)` | pengerjaan `completed` terbaik per tantangan (bintang terbanyak, lalu yang paling awal), keyed by `challenge_node_id` |
| `variantsServed($participantId, $nodeId)` | variasi yang sudah pernah didapat siswa pada tantangan itu di semua sesinya, termasuk yang ditinggalkan |
| `filteredBuilder($filters)` | builder pengerjaan + join `game_sessions`, `participants`, `challenge_nodes`, dengan [filter penelitian baku](#filter-penelitian-baku) |

Entity `ChallengeAttempt`:

- casts: `id`, `session_id`, `challenge_node_id`, `attempt_no`, `scorable_items`,
  `first_pass_correct`, `final_correct`, `check_count`, `max_tries`, `hint_count`,
  `answer_change_count`, `audio_use_count`, `stars` int; `variant_group` ?string;
  `selected_item_ids_json` json-array; `duration_ms` ?int.
- `isCompleted()`, `isInProgress()`, `selectedItemIds()` (list int), `hasItem($itemId)`.
- `maxTries()` — `max_tries` baris ini (cadangan `Config\Gelita::$maxTries`).
- `triesLeft()` — `maxTries() - check_count`, paling kecil 0: sisa kesempatan "Cek jawabanku" untuk
  Susun Gambar, Isi Rumpang, dan Benar atau Salah.
- `isExhausted()` — `triesLeft() === 0`.
- `firstPassPercent()` — persentase soal Berhasil pada Percobaan Pertama, hanya untuk tampilan siswa.

### `ItemResponseModel` dan Entity `ItemResponse`

Satu baris per soal dalam satu pengerjaan: keadaan terakhir soal itu.

- allowedFields: `challenge_attempt_id`, `challenge_item_id`, `display_order`, `status`,
  `first_answer_json`, `final_answer_json`, `first_pass_correct`, `is_correct`, `change_count`,
  `try_count`, `solved_on_try`, `hint_used`, `wrong_click_count`, `started_at`, `answered_at`,
  `duration_ms`.
- Validasi: `challenge_attempt_id`, `challenge_item_id` wajib; `status` `in_list[pending,answered,skipped]`.
- `createPlaceholders($attemptId, $items)` — satu baris `pending` per soal terpilih (`display_order`
  berurutan), satu `insertBatch`.
- `forAttempt($attemptId)` — keyed by `challenge_item_id`; `findOne($attemptId, $itemId)`.
- `countCorrect($attemptId)` — `{scorable, first_pass, final}`, hanya soal `scorable = 1`.

Entity `ItemResponse`: casts `id`, `challenge_attempt_id`, `challenge_item_id`, `display_order`,
`change_count`, `try_count`, `wrong_click_count` int; `first_answer_json`, `final_answer_json`
?json-array; `first_pass_correct`, `is_correct` ?boolean; `solved_on_try`, `duration_ms` ?int;
`hint_used` boolean. Method `isFirstPassOpen()` (`first_answer_json` masih NULL) dan `isAnswered()`.

### `ItemTryModel`

Satu baris = satu percobaan satu soal; hanya ditambah, tidak pernah diubah (tanpa callback `beforeUpdate`).

- allowedFields: `challenge_attempt_id`, `challenge_item_id`, `try_no`, `check_no`, `answer_json`,
  `is_correct`, `hint_opened_before`, `first_input_ms`, `last_input_ms`, `occurred_at`.
- Validasi: `challenge_attempt_id`, `challenge_item_id`, `try_no` wajib.
- `forAttempt($attemptId)` — urut soal lalu `try_no`.
- `check_no` = nomor tekan "Cek jawabanku" (NULL untuk ketukan Cari Objek); `hint_opened_before` = sudah ada
  petunjuk dibuka pada pengerjaan itu sebelum percobaan ini; `first_input_ms`/`last_input_ms` = waktu dari
  klien (ms sejak tantangan dibuka, atau sejak petunjuk tampil untuk Cari Objek), NULL bila tidak sah.

### Cara `ChallengeService` menulis kelompok ini

`App\Services\ChallengeService` (`service('challengeService')`) adalah satu-satunya penulis pengerjaan,
jawaban, dan percobaan. Setiap langkah berjalan dalam satu transaction bersama event-nya.

1. **`openNode($session, $nodeId)`** — memastikan wilayah sudah terbuka (`research_studies.unlock_mode`
   `sequential`, kecuali `free`), melanjutkan pengerjaan `in_progress` bila ada, atau memilih variasi
   lewat `variantsServed()` + `pickVariant()`, membuat baris `challenge_attempts`, placeholder
   `item_responses`, dan event `challenge_opened`. Mengembalikan `{attempt, payload}`.
2. **`submitCheck($attempt, $answers)`** — Susun Gambar, Isi Rumpang, Benar atau Salah. Satu tekan
   "Cek jawabanku" = satu percobaan untuk semua soal yang belum benar: ditolak `TRIES_EXHAUSTED` bila
   `check_count >= max_tries`, `INCOMPLETE` bila ada soal belum diisi; lalu `check_count++`, setiap soal
   yang belum benar dinilai dan menulis satu `item_tries`, soal yang sudah benar terkunci. Semua benar →
   ditutup `solved`; percobaan terakhir masih ada yang salah → event `challenge_tries_exhausted` dan
   ditutup `tries_exhausted`. Respons Susun Gambar memuat `pieces_correct` dan `misplaced`.
3. **`submitAnswer($attempt, $itemId, $answer, $meta)`** — Cari Objek. Klien mengirim token objek
   (`answer.object`); `item_id` mentah dari klien diabaikan. Satu ketukan = satu percobaan untuk
   petunjuk aktif: ketukan salah menambah `wrong_click_count` dan event `wrong_target_clicked`
   (ketukan salah pertama menutup first-pass sebagai salah); ketukan salah ke-`max_tries` menutup
   petunjuk itu sebagai salah (event `clue_failed`). Semua petunjuk tertutup → pengerjaan ditutup.
4. **`useHint($attempt, $hintId, ?$itemId)`** — `hint_count++`, `item_responses.hint_used = 1`, event
   `hint_opened`; mengembalikan teks petunjuk.
5. **`completeAttempt($attempt)`** — penutupan lewat endpoint `/complete`, hanya bila
   `closingReason()` mengizinkan (semua benar, atau kesempatan habis); selain itu `TRIES_REMAINING`.
6. **`abandonAttempt($attempt, $payload)`** — status `abandoned`, bintang 0, event
   `challenge_abandoned` (`via`: `user_exit`, `retention`, atau `content_changed`).

Aturan penulisan satu percobaan (`applyAnswer()`): `first_answer_json` dan `first_pass_correct` diisi
sekali pada percobaan pertama; `final_answer_json`, `is_correct`, `try_count`, `status = answered`
selalu diperbarui; `solved_on_try` diisi saat pertama kali benar; jawaban yang berbeda dari sebelumnya
menambah `change_count` dan `challenge_attempts.answer_change_count`.

Penutupan (`closeAttempt()`) mengisi `status = completed`, `completion_reason`, `completed_at`,
`duration_ms`, memanggil `ScoringService::finalize()`, menghitung ulang `session_progress`, menulis
`challenge_completed` (dan `level_completed` bila wilayah tuntas); sesudah commit
`SessionService::completeIfFinished()` menandai sesi selesai bila ketiga wilayah tuntas.

Penilaian benar/salah (`grade()`) selalu memakai kunci dari database:

| `interaction_type` | Benar bila |
|---|---|
| `puzzle_arrange` | `answer.order` sama persis dengan `answer_key_json.order` |
| `fill_blank_bank` | `mb_strtolower(trim(answer.text))` sama dengan `text_{locale}` (cadangan `text_id`) |
| `verdict_card` | `answer.verdict` salah satu `verdictOptions()` dan sama dengan `verdict()` |
| `find_object` | objek yang diketuk adalah soal target itu sendiri (bukan pengecoh) |

### Bintang: `ScoringService`

`App\Services\ScoringService` (`service('scoringService')`) memegang aturan `bintang-1`
(`ScoringService::VERSION`, disimpan di `game_releases.scoring_version`):

| Bintang | Syarat (hanya soal `scorable`) |
|---|---|
| 3 | pengerjaan selesai dan semua soal Berhasil pada Percobaan Pertama |
| 2 | semua soal benar di akhir |
| 1 | selesai, tetapi ada soal yang tetap salah |
| 0 | belum selesai atau ditinggalkan |

Petunjuk, suara, dan waktu tidak mengurangi bintang.

- `starsFromCounts($completed, $scorable, $firstPass, $final)` (static) — aturan murni di atas.
- `starsFor($attempt)` — bintang dari `ItemResponseModel::countCorrect()`.
- `finalize($attempt)` — menulis `first_pass_correct`, `final_correct`, `scorable_items`, `stars` ke
  pengerjaan.
- `totalStars($sessionId)` — jumlah bintang terbaik per tantangan; `levelSummary($sessionId, $levelId)` —
  `{completed_nodes, total_nodes, stars}` satu wilayah.

### Payload pemain

Payload `openNode()` dibentuk dari `ChallengeItem::toPlayerArray()` dan
`ReadingPassage::toPlayerArray()`, ditambah:

- `attempt_id`, `attempt_no`, `variant` (`group`, `title`), `max_tries`, `tries_used`, `resumed`,
  `elapsed_ms` (dari `started_at` server), `node` (judul, instruksi, latar).
- `items[].state` untuk memulihkan tampilan: soal yang sudah benar membawa `locked` dan jawaban siswa
  sendiri; soal lain hanya jumlah percobaannya. Jawaban salah tidak pernah dikembalikan.
- `hints` — hanya `{id, item_id, sequence}`; teks dikirim `useHint()`.
- `rumpang`: `word_bank` — jawaban variasi + `distractor_count` pengecoh pertama dari
  `ChallengeNode::distractors()`, diacak dengan benih id pengerjaan sampai urutannya tidak sama dengan
  urutan rumpang (tanpa pengecoh: tidak ada kata di posisinya sendiri). Pemetaan kata → rumpang tidak dikirim.
- `boleh`: `verdict_options`; `passages` — bacaan sekali per `passage_id`.
- `cari`: tanpa `items`; `objects` memuat semua benda (target dan pengecoh) dalam bentuk identik —
  `ref` (HMAC-SHA256 atas pengerjaan + soal dengan `encryption.key`, berbeda per pengerjaan), posisi,
  gambar, `found` — diurutkan menurut posisi layar. `clues` hanya untuk target dinilai: `item_id`,
  teks, `audio`/`audio_id` (narasi disetujui, `promptAudioId()`), `state`; diacak dengan benih id
  pengerjaan.

### `GameEventLogModel`

Log event mentah, hanya ditambah. Waktu dikelola manual: `occurred_at` dari klien (dikonversi ke zona
aplikasi), `server_received_at` dari server.

- allowedFields: `event_uuid`, `client_event_id`, `session_id`, `participant_id`, `level_id`,
  `challenge_node_id`, `challenge_attempt_id`, `challenge_item_id`, `event_type`, `sequence_no`,
  `occurred_at`, `server_received_at`, `payload_json`, `deleted_at`, `deleted_by`, `delete_reason`.
- Validasi: `event_uuid` (maks. 36), `client_event_id` (maks. 120), `session_id`, `participant_id` wajib;
  `event_type` `valid_event_type`.

| Method | Perilaku |
|---|---|
| `appendBatch($events)` | `INSERT IGNORE` batch; duplikat `(session_id, client_event_id)` diabaikan. Mengembalikan `{accepted, duplicate}` |
| `nextSequenceNo($sessionId)` | `MAX(sequence_no) + 1` |
| `timeline($sessionId, $limit = 500)` | event yang belum dihapus, urut `occurred_at`, `sequence_no`, `id` (`sequence_no` klien dimulai ulang setiap halaman dimuat) |
| `heardRegionIntros($participantId)` | `level_id` wilayah yang narasi "Kenali wilayah"-nya didengar sampai slide terakhir (event `dialogue_advanced`, `context = region_intro`, `index >= total`) |
| `reachedLastSlide($rows, $context)` (static) | penyaring bersama untuk method di atas |
| `existsClientEvent($sessionId, $clientEventId)` | dipakai `EventService::recordAudio()` |
| `softDeleteScope($scope, $staffId, $reason)` | menandai `deleted_at`/`deleted_by`/`delete_reason` untuk cakupan `session_id`, `participant_id`, atau `study_id` (cakupan kosong ditolak) |

`App\Services\EventService` (`service('eventService')`) adalah penulisnya: `ingest()` menerima batch
klien (paling banyak `Config\Gelita::$maxEventsPerBatch`; tipe harus terdaftar dan bukan
`EventService::SERVER_ONLY` — `screen_viewed`, `challenge_tries_exhausted`, `clue_failed`),
`record()` menulis satu event server tanpa membuka transaction sendiri (agar ikut transaction pemanggil),
`recordAudio()` menulis `audio_usage_events` + event `audio_*` dalam satu transaction.

### `AudioUsageEventModel`

- `ACTIONS = ['play', 'autoplay', 'pause', 'replay', 'complete']`. `play` = pemain menekan putar,
  `autoplay` = layar bernarasi memutar sendiri; keduanya dihitung terpisah.
- allowedFields: `session_id`, `challenge_attempt_id`, `audio_asset_id`, `action`, `play_index`,
  `listened_ms`, `completed`, `occurred_at`, `server_received_at`; validasi `session_id`,
  `audio_asset_id` wajib, `action` `in_list[play,autoplay,pause,replay,complete]`.
- `nextPlayIndex($sessionId, $audioAssetId, $action)` — nomor pemutaran ke-n aset itu dalam sesi,
  dihitung server (`play`, `autoplay`, `replay` membuka pemutaran baru).
- `usageForSession($sessionId)`. Statistik olahan (`usageStats()`: rasio selesai, rata-rata lama
  didengar) dihapus pada Dok 3; baris mentahnya ada di lembar Bantuan.
- Setiap event audio yang terkait pengerjaan menambah `challenge_attempts.audio_use_count`.

### `ParticipantFeedbackModel`

- allowedFields `participant_id`, `session_id`, `rating`, `liked_most`, `hardest_part`,
  `new_learning`, `suggestion`, `submitted_at`; validasi `participant_id` wajib, `rating` 1–5.
- `forStudy($studyId)` (dengan `participant_code`), `ratingDistribution($filters)` —
  `{count, mean, distribution}`.

Data mentah kelompok ini dibaca lewat [`RawDataService`](#data-mentah-rawdataservice-dan-ekspor),
satu tempat query untuk tabel panel dan ekspor. `AnalyticsService` tinggal KPI hitungan, profil satu
siswa, dan linimasa event.

### Data mentah: `RawDataService` dan ekspor

`App\Services\RawDataService` (Dok 3 §8.4) memegang query setiap dimensi = kode lembar ekspor:

| Dimensi | Satu baris | Sumber |
|---|---|---|
| `peserta` | siswa | `participants` (+ `schools.code` hanya beridentitas) |
| `sesi` | sesi bermain | `game_sessions` + studi + rilis |
| `perangkat` | catatan perangkat | `device_logs` |
| `progres` | keadaan terbaru sesi | `session_progress` |
| `pengerjaan` | pengerjaan tantangan | `challenge_attempts` |
| `jawaban` | keadaan akhir soal | `item_responses` + `challenge_items` |
| `percobaan` | satu percobaan | `item_tries` |
| `navigasi` | event navigasi | `game_event_logs` (`NAVIGATION_EVENTS`) |
| `bantuan` | petunjuk dibuka / aksi suara | UNION `game_event_logs` `hint_opened` + `audio_usage_events` |
| `selesai` | event penyelesaian | `game_event_logs` (`COMPLETION_EVENTS`) |
| `event` | semua event (admin saja) | `game_event_logs` tanpa baris bertanda hapus |
| `masukan` | kritik & saran | `participant_feedback` |
| `butir` | soal bank | `challenge_items` |

- `query($dimension, $filters, StaffScope $scope)` — builder terfilter berisi `_key` (kunci unik) dan
  kolom mentah; `shape($dimension, $row, $scope, $labeled)` mengubahnya ke kolom §9.2.
- `page($dimension, $filters, $page, $perPage, $sort)` — `{rows, total, page, per_page, sort,
  columns: [{key, label, type}]}` untuk tabel admin; `streamCsv($dimension, $filters, $handle)` — CSV
  (BOM, koma, judul = kode kolom) dengan baris yang sama. Keduanya memakai cakupan yang diikat
  `withScope()` / `service('rawDataService', $scope)`.
- `rows($dimension, $filters, $scope, $labeled)` — generator keyset per 2.000 baris untuk ekspor.
- Nilai berkode: waktu ISO 8601 WIB dengan milidetik, durasi milidetik, boolean 1/0, JSON sebagai
  teks. Berlabel: kode → label (`ExportLabels::label()`), wilayah → nama, jawaban/kunci → kalimat
  (`AnswerText`).
- Filter `RawDataService::FILTER_KEYS`; rentang tanggal memakai waktu baris itu sendiri.

`App\Services\StaffScope` adalah objek nilai hak baca (`admin($anonymous)`, `teacher($schoolId)`,
`fromStaff($row)`): guru → sekolahnya, anonim, tanpa dimensi `event` dan kolom `answer_key`. Kolom
identitas (`display_name`, `username`, `school_name`, `school_npsn`) pada mode anonim tidak pernah
dibaca dari database.

`App\Libraries\ExportLabels` menyimpan judul (Indonesia, bersatuan), jenis, arti, dan daftar nilai
setiap kolom; dari sana dibangun Kamus Data. `App\Services\ExportService` menulis berkas XLSX atau
CSV-ZIP dari `RawDataService::rows()` dan meneruskan laporan PDF satu siswa ke `ReportService`; lihat
[07_FEATURE_INTEGRATION.md](07_FEATURE_INTEGRATION.md).

## Kelompok 5 — Admin dan tata kelola

| Model | Tabel | returnType | Timestamps |
|---|---|---|---|
| `StaffUserModel` | `staff_users` | `StaffUser` | ya |
| `ResearchStudyModel` | `research_studies` | array | ya |
| `GameReleaseModel` | `game_releases` | array | tidak |
| `AuditLogModel` | `audit_logs` | array | tidak (`occurred_at`) |
| `DataExportModel` | `data_exports` | array | tidak (`created_at` dari database) |
| `DataDeletionRequestModel` | `data_deletion_requests` | array | tidak (`created_at` dari database) |

### `StaffUserModel` dan Entity `StaffUser`

Akun panel: `admin` (seluruh data) dan `guru` (hanya sekolahnya).

- allowedFields: `username`, `email`, `password_hash`, `must_change_password`, `role`, `display_name`,
  `school_id`, `is_active`, `failed_login_count`, `locked_until`, `last_login_at`.
- Validasi: `username` `required|alpha_dash|min_length[3]|max_length[100]|is_unique[…]`, `role`
  `in_list[admin,guru]`, `display_name` wajib (maks. 150).

| Method | Perilaku |
|---|---|
| `findByUsername($username)` | cari menurut nama pengguna (di-`trim`) |
| `registerFailedLogin($id)` / `clearFailedLogin($id)` | sama dengan siswa, memakai `loginThrottle['staff']` (5 kali gagal → terkunci 15 menit) |
| `activeStaff()` | staf aktif urut nama |
| `setPassword($id, $plain, $mustChange = false)` | satu-satunya jalur menulis `password_hash` |
| `resetToTemporary($id)` | sandi sementara wajib-ganti + buka kunci login; dipakai panel `/admin/staf` dan `gelita:staff:password`. Sandi dikembalikan sekali untuk ditampilkan |
| `temporaryPassword()` (static) | 16 karakter acak yang lolos `PasswordPolicy` |

Entity `StaffUser`: casts `id` int, `school_id` ?int, `is_active`, `must_change_password` boolean,
`failed_login_count` int. Method `isAdmin()`, `mustChangePassword()`, `verifyPassword($plain)`,
`isLocked()`, `schoolScope()` (NULL untuk admin, `school_id` untuk guru — batas data yang diteruskan ke
`AnalyticsService`), `toSafeArray()` (tanpa `password_hash` dan kolom throttle).

### `ResearchStudyModel`

- allowedFields: `code`, `name`, `description`, `year_label`, `status`, `retention_days`,
  `default_locale`, `unlock_mode`.
- Validasi: `code` wajib & unik (maks. 50), `name` wajib, `status` `in_list[draft,active,closed]`,
  `unlock_mode` `in_list[sequential,free]`.
- `activeStudy()` — studi `active` terbaru, atau NULL; `requireActiveStudy()` — sama, tetapi
  exception bila tidak ada (pendaftaran dan sesi tidak dapat berjalan tanpanya).
- `unlock_mode` dibaca `ChallengeService` (wilayah terbuka berurutan atau bebas), `default_locale` dipakai
  bila locale sesi tidak sah, `retention_days` dibaca retensi terjadwal: data yang lewat masa simpan tidak dihapus otomatis,
  melainkan dibuatkan pratinjau penghapusan `hard` yang menunggu persetujuan admin.

### `GameReleaseModel`

- allowedFields: `release_code`, `app_version`, `content_version`, `asset_version`, `scoring_version`,
  `published_at`, `notes`, `is_active`.
- Validasi: `release_code` wajib & unik (maks. 80), `app_version`, `content_version`,
  `scoring_version` wajib (maks. 30).
- `active()` — release aktif; exception bila tidak ada (sesi tidak boleh dibuat tanpanya).
  `activeOrNull()` — tanpa exception, untuk kunci cache `ContentRepository`.
  `activate($id)` — menonaktifkan release lain dan mengaktifkan satu, dalam satu transaction.
- `game_sessions.release_id` mengunci release yang berlaku untuk setiap sesi.

### `AuditLogModel`

- allowedFields `staff_user_id`, `action`, `target_type`, `target_id`, `metadata_json`, `ip_hash`,
  `occurred_at`; validasi `action` wajib (maks. 50).
- `record($action, $opts)` — mencatat tindakan staf. `staff_user_id` diambil dari `$opts` atau
  `session('staff_id')`; `ip_hash` dari request web (NULL untuk command CLI). Kata sandi mentah tidak
  pernah masuk `metadata`.
- `recent($limit = 100)` — catatan terbaru.

### `DataExportModel`

- allowedFields: `requested_by`, `study_id`, `format`, `scope_json`, `anonymized`, `status`,
  `row_count`, `file_path`, `file_sha256`, `error_message`, `completed_at`, `expires_at`.
- Validasi: `requested_by` wajib, `format` `in_list[xlsx,csv,pdf]`, `status`
  `in_list[queued,running,done,failed]`. `ExportService` membangun `xlsx` (workbook data mentah),
  `csv` (CSV-ZIP, berkas `.zip`), dan `pdf` (laporan satu siswa lewat `ReportService`).
- `markDone($id, $path, $sha, $rows)` — status `done`, `expires_at` = sekarang +
  `Config\Gelita::$exportRetentionDays` hari; `markFailed($id, $message)`; `expired()` — berkas `done`
  yang sudah lewat masa simpan (dihapus `ExportService::purgeExpired()`).

### `DataDeletionRequestModel`

- allowedFields: `requested_by`, `scope_json`, `reason`, `mode`, `status`, `affected_count`,
  `approved_by`, `executed_at`.
- Validasi: `requested_by` wajib, `mode` `in_list[soft,hard]`, `status`
  `in_list[preview,approved,executed,cancelled]`.
- `pendingApproval()` — permintaan berstatus `preview` yang menunggu persetujuan admin kedua.
- Pratinjau, persetujuan, eksekusi (soft: menandai `game_event_logs` dan `participants.deleted_at`;
  hard: menghapus baris), dan retensi terjadwal ada di `App\Services\RetentionService`.

## Filter penelitian baku

Query statistik dan ekspor menerima array `$filters` yang sama. Helper
`apply_research_filters($builder, $filters, $skip = [])` menerapkannya pada builder yang sudah men-join
`game_sessions`, `participants`, dan `challenge_nodes`; kunci yang tabelnya tidak di-join disebut di
`$skip` (mis. `AudioUsageEventModel` dan `ParticipantFeedbackModel` melewatkan `level_id` dan `node_id`).

| Kunci | Kolom |
|---|---|
| `study_id` | `game_sessions.study_id` |
| `participant_id` | `game_sessions.participant_id` |
| `level_id` | `challenge_nodes.level_id` |
| `node_id` | `challenge_nodes.id` |
| `school_id` | `participants.school_id` — wajib diisi untuk guru |
| `class_level` | `participants.class_level` |
| `province_code` | `participants.province_code` |
| `locale` | `game_sessions.locale` |
| `date_from` / `date_to` | `game_sessions.started_at` (awal/akhir hari, format `Y-m-d`) |

Nilai kosong atau NULL diabaikan. Peserta yang dihapus lunak selalu dibuang oleh query pemanggil
(`participants.deleted_at IS NULL`). Helper ini dipakai `AnalyticsService` dan halaman panel lama;
`RawDataService` menerapkan filternya sendiri per dimensi (`FILTER_KEYS`), dan formulir ekspor hanya
menerima `ExportService::FILTER_KEYS`.

Cakupan sekolah ditegakkan di service, bukan hanya di filter route: `AnalyticsService`,
`RawDataService`, dan `ExportService` menambahkan `school_id` guru ke setiap query, berapa pun nilai
yang dikirim formulir.

## Aturan keamanan data

1. **Kunci jawaban tidak pernah dikirim ke klien.** `answer_key_json` hanya dibaca server lewat
   `ChallengeItem::answerKey()`; soal dikirim lewat `ChallengeItem::toPlayerArray()`, yang hanya
   meneruskan kunci tampilan `config_json` (`safeConfig()`). `decoy`, `wrong_feedback_*`, `skill`,
   `scorable`, dan pemetaan bank kata tetap di server; umpan balik objek salah dikirim sesudah diketuk.
2. **Penilaian hanya di server.** `ChallengeService` menilai memakai kunci dari database; field `correct`
   kiriman browser diabaikan. Jawaban hanya diterima untuk soal yang ada di `selected_item_ids_json`
   (`ChallengeAttempt::hasItem()`, aturan `item_in_attempt`).
3. **Objek Cari Objek tidak dapat dicocokkan dari klien.** Objek target dan pengecoh dikirim dalam bentuk
   identik dengan token `ref` per pengerjaan; `encryption.key` wajib terisi.
4. **Batas kesempatan ditegakkan server.** `max_tries` disalin ke pengerjaan; `submitCheck()` menolak
   percobaan sesudah kesempatan habis, dan event `challenge_tries_exhausted`, `clue_failed`,
   `screen_viewed` tidak dapat dikirim klien.
5. **Kata sandi.** `password_hash` hanya ditulis lewat `setPassword()` (atau saat pendaftaran) dan tidak
   pernah keluar ke view, API, ekspor, atau log; pakai `Participant::toSafeArray()` /
   `StaffUser::toSafeArray()`. Statistik sandi hanya membaca `pw_first_submit_criteria` dan
   `pw_weak_submit_count`. Kekuatan sandi diputuskan `App\Libraries\PasswordPolicy`.
6. **IP tidak disimpan mentah.** Kolom `ip_hash` (`game_sessions`, `device_logs`, `audit_logs`) diisi
   `hash_ip()` dengan garam `gelita.ipSalt`.
7. **Data mentah hanya ditambah.** `item_tries`, `game_event_logs`, `device_logs`, dan
   `audio_usage_events` tidak diubah sesudah ditulis; penghapusan event bersifat lunak (`deleted_at`,
   `deleted_by`, `delete_reason`) kecuali lewat permintaan penghapusan `hard` yang disetujui.
8. **Atomik.** "Simpan jawaban + tulis `item_tries` + tulis event + perbarui progres" berjalan dalam satu
   transaction; `EventService::record()` sengaja tidak membuka transaction sendiri.
9. **Kunci jawaban soal yang sudah dijawab tidak diubah.** Panel dan impor memeriksa
   `ChallengeItemModel::hasResponses()` agar data lama tetap sebanding.
