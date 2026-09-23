# 03_MODEL_ENTITY.md — Data Layer (Model, Entity, Service)

> **Revisi 2 (21 September 2026).** Perubahan: akun siswa (`ParticipantModel`/`Participant` kini punya login, throttle, reset sandi oleh guru); `SessionService` menangani registrasi berkata sandi, login, ganti sandi; aturan validasi `strong_password`/`valid_username`; model & entity baru `ReadingPassage`; grader `verdict_*` (Benar/Salah/Pendapat) menggantikan `boolean_*`; pengecoh rumpang dari `config_json.distractors`; service baru `ContentImportService` untuk memuat workbook bank soal. Jumlah hitungan komponen juga dikoreksi (Model 29).

---

## Tujuan

Membuat seluruh data layer GELITA: Model CodeIgniter untuk 30 tabel, Entity untuk tabel yang butuh casting/accessor, dan Service untuk logika yang tidak boleh berada di controller (scoring, session, event, konten, analitik).

---

## Konteks

Aturan pembagian tanggung jawab yang dipakai proyek ini:

```
Controller  → validasi input, panggil Service, kembalikan response
Service     → business rule, transaction, perhitungan
Model       → akses tabel, query khusus, validasi tingkat data
Entity      → representasi satu baris, casting, accessor dwibahasa
```

**Service dibuat hanya di tempat yang memang membutuhkannya** — enam service bisnis (sesi & akun siswa, tantangan, skor, event, analitik, impor bank soal) ditambah dua pembantu (`ContentRepository` untuk cache konten, `GameContext` untuk state satu request). Kriterianya: menyentuh lebih dari satu tabel dalam satu transaction, atau berisi perhitungan yang tidak boleh berbeda antar pemanggil. Sisanya cukup Model.

Tabel yang **tidak** memerlukan Entity: tabel referensi sederhana (`schools`, `research_phases`, `learning_indicators`, `scoring_profiles`, `audit_logs`, `hints`). Model-nya memakai `returnType = 'array'`.

---

## Yang Harus Dibuat

| Kategori | Jumlah | Folder |
|---|---:|---|
| Model | 29 | `app/Models/` |
| Entity | 11 | `app/Entities/` |
| Service | 8 | `app/Services/` |
| Validation ruleset | 1 | `app/Validation/GelitaRules.php` |

---

## Struktur File

```text
app/Models/
├── StaffUserModel.php
├── SchoolModel.php
├── ResearchStudyModel.php
├── ResearchPhaseModel.php
├── ParticipantModel.php
├── ParticipantConsentModel.php
├── GameReleaseModel.php
├── LevelModel.php
├── LearningIndicatorModel.php
├── ScoringProfileModel.php
├── MediaAssetModel.php
├── AudioAssetModel.php
├── ChallengeNodeModel.php
├── ChallengeItemModel.php
├── ChallengeOptionModel.php
├── HintModel.php
├── DialogueModel.php
├── LibraryPageModel.php
├── LibraryMediaModel.php
├── ReadingPassageModel.php
├── GameSessionModel.php
├── SessionProgressModel.php
├── ChallengeAttemptModel.php
├── ItemResponseModel.php
├── AudioUsageEventModel.php
├── GameEventLogModel.php
├── ParticipantFeedbackModel.php
├── AuditLogModel.php
├── DataExportModel.php
└── DataDeletionRequestModel.php

app/Entities/
├── StaffUser.php
├── Participant.php
├── Level.php
├── ChallengeNode.php
├── ChallengeItem.php
├── ChallengeOption.php
├── LibraryPage.php
├── LibraryMedia.php
├── ReadingPassage.php
├── GameSession.php
├── ChallengeAttempt.php
└── ItemResponse.php

app/Services/
├── ContentRepository.php
├── GameContext.php
├── SessionService.php
├── ChallengeService.php
├── ScoringService.php
├── EventService.php
├── AnalyticsService.php
└── ContentImportService.php

app/Validation/
└── GelitaRules.php
```

---

## Implementasi — Entity

### Pola dasar: trait `Bilingual`

Buat `app/Entities/Traits/Bilingual.php`:

```php
<?php
namespace App\Entities\Traits;

trait Bilingual
{
    /**
     * Mengambil nilai dwibahasa. Bila versi locale kosong, jatuh ke Indonesia.
     * text('title') → title_en bila locale 'en' dan terisi, selain itu title_id
     */
    public function text(string $field, ?string $locale = null): string
    {
        $locale = $locale ?: (service('request')->getLocale() ?: 'id');
        $value  = (string) ($this->attributes[$field . '_' . $locale] ?? '');
        if (trim($value) === '') {
            $value = (string) ($this->attributes[$field . '_id'] ?? '');
        }
        return $value;
    }
}
```

Semua Entity konten memakai trait ini. Di view cukup `$node->text('title')`.

### `App\Entities\ChallengeNode`

```php
<?php
namespace App\Entities;

use CodeIgniter\Entity\Entity;
use App\Entities\Traits\Bilingual;

class ChallengeNode extends Entity
{
    use Bilingual;

    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'                 => 'int',
        'level_id'           => 'int',
        'sequence'           => 'int',
        'indicator_id'       => '?int',
        'scoring_profile_id' => '?int',
        'background_media_id'=> '?int',
        'scene_media_id'     => '?int',
        'audio_intro_id'     => '?int',
        'audio_intro_en_id'  => '?int',
        'config_json'        => 'json-array',
        'map_x'              => 'float',
        'map_y'              => 'float',
        'is_active'          => 'boolean',
    ];

    /** Nilai config dengan default per engine */
    public function config(string $key, $default = null)
    {
        $cfg = $this->config_json ?: [];
        if (array_key_exists($key, $cfg)) {
            return $cfg[$key];
        }
        return $default ?? self::defaultConfig($this->engine_type)[$key] ?? null;
    }

    public static function defaultConfig(string $engine): array
    {
        return match ($engine) {
            'puzzle'  => ['items_per_round' => 1, 'grid' => 3, 'allow_retry' => true, 'mode' => 'arrange'],
            'rumpang' => ['items_per_round' => 4, 'use_word_bank' => true, 'distractor_count' => 2,
                          'allow_retry' => true, 'distractors' => []],
            'boleh'   => ['items_per_round' => 8, 'allow_retry' => true,
                          'verdict_options' => ['benar', 'salah'], 'require_reason' => false],
            'pilihan' => ['items_per_round' => 3, 'allow_retry' => false, 'shuffle_options' => true],
            'cari'    => ['items_per_round' => 4, 'allow_retry' => true, 'show_decoys' => true],
            default   => ['items_per_round' => 1, 'allow_retry' => true],
        };
    }

    public function itemsPerRound(): int
    {
        return max(1, (int) $this->config('items_per_round'));
    }

    public function allowsRetry(): bool
    {
        return (bool) $this->config('allow_retry');
    }
}
```

### `App\Entities\ChallengeItem`

```php
protected $casts = [
    'id' => 'int', 'challenge_node_id' => 'int', 'sequence' => 'int',
    'answer_key_json' => 'json-array',
    'config_json'     => 'json-array',
    'media_asset_id'  => '?int',
    'indicator_id'    => '?int',
    'scorable'        => 'boolean',
    'is_active'       => 'boolean',
];
```

Method:

```php
answerKey(?string $key = null)           // akses answer_key_json
isDecoy(): bool                          // config_json['decoy'] === true
position(): array                        // ['x'=>..,'y'=>..,'w'=>..] untuk engine cari
acceptedAnswers(string $locale): array   // untuk fill_blank_free, normalisasi lowercase+trim
verdict(): ?string                       // answer_key_json['verdict'] untuk verdict_card/verdict_reason
pieces(string $locale): array            // config_json['pieces'] untuk ordering: [['key'=>'a','text'=>'…'],…]
sources(string $locale): array           // config_json['sources'] untuk Wonosobo node 3
```

**Penting:** Entity ini pernah dikirim ke browser sebagai payload soal. Karena itu tambahkan:

```php
/** Bentuk aman untuk dikirim ke browser: TANPA answer_key_json */
public function toPlayerArray(string $locale): array
{
    return [
        'id'               => $this->id,
        'item_key'         => $this->item_key,
        'interaction_type' => $this->interaction_type,
        'prompt'           => $this->text('prompt', $locale),
        'source_text'      => $this->text('source_text', $locale),
        'passage_id'       => $this->passage_id,          // teks bacaan dikirim terpisah, sekali per passage
        'media'            => media_src($this->media_asset_id),
        'pieces'           => $this->interaction_type === 'ordering'
                                ? $this->shuffledPieces($locale) : null,   // diacak, kunci urutan TIDAK dikirim
        'sources'          => $this->sources($locale) ?: null,
        'config'           => $this->safeConfig(),   // buang 'decoy', 'wrong_feedback_*', 'digital_pillar'
    ];
}
```

`safeConfig()` hanya meneruskan kunci tampilan (`x`, `y`, `w`, `grid`). `decoy`, `wrong_feedback_*`, dan `digital_pillar` tetap di server. `wrong_feedback_*` dikirim **setelah** objek diklik, lewat respons `submitAnswer()`.

`answer_key_json` **tidak boleh** pernah dikirim ke browser. Ini aturan mutlak — seluruh penilaian di server.

### `App\Entities\ChallengeAttempt`

```php
protected $casts = [
    'id' => 'int', 'session_id' => 'int', 'challenge_node_id' => 'int',
    'attempt_no' => 'int',
    'selected_item_ids_json' => 'json-array',
    'scorable_items' => 'int', 'first_pass_correct' => 'int', 'final_correct' => 'int',
    'first_pass_accuracy' => 'float', 'final_accuracy' => 'float',
    'check_count' => 'int', 'hint_count' => 'int', 'retry_count' => 'int',
    'answer_change_count' => 'int', 'audio_use_count' => 'int',
    'independence' => 'float', 'score' => 'float', 'stars' => 'int',
    'duration_ms' => '?int',
];

public function isCompleted(): bool   { return $this->status === 'completed'; }
public function selectedItemIds(): array { return $this->selected_item_ids_json ?: []; }
```

### `App\Entities\ItemResponse`

```php
protected $casts = [
    'first_answer_json' => 'json-array',
    'final_answer_json' => 'json-array',
    'first_pass_correct' => '?boolean',
    'is_correct' => '?boolean',
    'hint_used' => 'boolean',
    'change_count' => 'int',
    'wrong_click_count' => 'int',
    'duration_ms' => '?int',
];
```

### `App\Entities\GameSession`

```php
protected $casts = ['id'=>'int','participant_id'=>'int','study_id'=>'int',
                    'phase_id'=>'int','release_id'=>'int','duration_ms'=>'int',
                    'is_touch'=>'?boolean'];

public function isActive(): bool { return $this->status === 'active'; }
public function isFinished(): bool { return in_array($this->status, ['completed','abandoned'], true); }
```

### `App\Entities\Participant`

```php
protected $casts = ['id'=>'int','age'=>'?int','school_id'=>'?int',
                    'must_change_password'=>'boolean','failed_login_count'=>'int',
                    'pw_first_submit_criteria'=>'?int','pw_weak_submit_count'=>'int'];

/** Nama untuk export anonim */
public function anonLabel(): string { return $this->participant_code; }

/** Nama untuk tampilan guru */
public function label(): string
{
    return $this->display_name ?: $this->username;
}

public function verifyPassword(string $plain): bool
{
    return password_verify($plain, $this->password_hash);
}

public function isLocked(): bool
{
    return $this->locked_until !== null && strtotime($this->locked_until) > time();
}

public function mustChangePassword(): bool { return (bool) $this->must_change_password; }

/** Satu-satunya bentuk yang boleh dikirim ke view/API: tanpa password_hash & kolom throttle */
public function toSafeArray(): array
{
    return [
        'id' => $this->id, 'participant_code' => $this->participant_code,
        'username' => $this->username, 'display_name' => $this->display_name,
        'age' => $this->age, 'gender' => $this->gender, 'class_level' => $this->class_level,
        'school_name' => $this->school_name_snapshot,
    ];
}
```

Jangan pernah memanggil `$participant->toArray()` untuk respons atau view — metode itu ikut membawa `password_hash`. Pakai `toSafeArray()`.

### `App\Entities\StaffUser`

```php
public function isAdmin(): bool { return $this->role === 'admin'; }
public function mustChangePassword(): bool { return (bool) $this->must_change_password; }
public function verifyPassword(string $plain): bool
{
    return password_verify($plain, $this->password_hash);
}
public function isLocked(): bool
{
    return $this->locked_until !== null && strtotime($this->locked_until) > time();
}
```

### `Level`, `ChallengeOption`, `LibraryPage`, `ReadingPassage`

Hanya `use Bilingual` + casts int/bool. Tidak ada logika tambahan. `ReadingPassage` memakai `text('title')` dan `text('body')`.

---

## Implementasi — Model

### Pola umum

```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class ChallengeNodeModel extends Model
{
    protected $table            = 'challenge_nodes';
    protected $primaryKey       = 'id';
    protected $returnType       = \App\Entities\ChallengeNode::class;
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $dateFormat       = 'datetime';
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';
    protected $allowedFields    = [
        'level_id','sequence','engine_type','variant_code',
        'title_id','title_en','instruction_id','instruction_en',
        'description_id','description_en','indicator_id','scoring_profile_id',
        'background_media_id','scene_media_id','audio_intro_id','audio_intro_en_id',
        'config_json','map_x','map_y','content_version','is_active',
    ];
    protected $validationRules = [
        'level_id'    => 'required|is_natural_no_zero',
        'sequence'    => 'required|greater_than[0]|less_than[6]',
        'engine_type' => 'required|in_list[puzzle,rumpang,boleh,pilihan,cari]',
        'title_id'    => 'required|max_length[250]',
        'title_en'    => 'required|max_length[250]',
    ];
    protected $beforeInsert = ['encodeJson'];
    protected $beforeUpdate = ['encodeJson'];

    protected function encodeJson(array $data): array
    {
        if (isset($data['data']['config_json']) && is_array($data['data']['config_json'])) {
            $data['data']['config_json'] = json_encode($data['data']['config_json'], JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
}
```

### Spesifikasi per Model

Kolom `allowedFields` = semua kolom tabel kecuali `id`, `created_at`, `updated_at` (dikelola `useTimestamps`), kecuali disebutkan lain.

---

#### `StaffUserModel`

| Properti | Nilai |
|---|---|
| table | `staff_users` |
| returnType | `App\Entities\StaffUser` |
| useTimestamps | true |
| allowedFields | username, email, password_hash, role, display_name, school_id, is_active, failed_login_count, locked_until, last_login_at |
| validationRules | `username: required|alpha_dash|min_length[3]|max_length[100]|is_unique[staff_users.username,id,{id}]`<br>`role: required|in_list[admin,guru]`<br>`display_name: required|max_length[150]` |

Method khusus:

```php
findByUsername(string $username): ?StaffUser
registerFailedLogin(int $id): void        // increment; >=5 → locked_until = NOW + 15 menit
clearFailedLogin(int $id): void           // reset counter, set last_login_at
activeStaff(): array
```

`password_hash` tidak pernah diisi langsung dari input. Selalu lewat:

```php
public function setPassword(int $id, string $plain, bool $mustChange = false): bool
{
    return $this->update($id, [
        'password_hash'        => password_hash($plain, PASSWORD_DEFAULT),
        'must_change_password' => $mustChange ? 1 : 0,
    ]);
}
```

---

#### `ParticipantModel`

| Properti | Nilai |
|---|---|
| table | `participants` |
| returnType | `App\Entities\Participant` |
| useSoftDeletes | **true**, `deletedField = 'deleted_at'` |
| useTimestamps | true |

allowedFields: semua kolom tabel kecuali `id`, `created_at`, `updated_at`, `deleted_at`. `password_hash` ada di daftar tetapi hanya diisi lewat `setPassword()`.

validationRules:

```
participant_code : required|max_length[40]|is_unique[participants.participant_code,id,{id}]
username         : required|valid_username|not_reserved_username|is_unique[participants.username,id,{id}]
age              : permit_empty|integer|greater_than[4]|less_than[81]
gender           : permit_empty|in_list[laki-laki,perempuan,lainnya]
class_level      : permit_empty|max_length[20]
display_name     : permit_empty|max_length[150]
```

Method khusus:

```php
generateCode(): string
    // Transaction: SELECT MAX(id)+1 → participant_code('GLT-%06d')
    // Bila bentrok (race), ulangi hingga 5 kali.

findByCode(string $code): ?Participant

findByUsername(string $username): ?Participant
    // strtolower + trim sebelum mencari

usernameAvailable(string $username): bool

setPassword(int $id, string $plain, bool $mustChange = false): bool
    // password_hash(PASSWORD_DEFAULT), password_changed_at = NOW(6), must_change_password = $mustChange

registerFailedLogin(int $id): void
    // failed_login_count++; bila >= loginThrottle.participant.max_fail
    // → locked_until = NOW + lock_minutes, counter di-reset

clearFailedLogin(int $id): void
    // failed_login_count = 0, locked_until = NULL, last_login_at = NOW(6)

digitalSecurityStats(array $filters): array
    // distribusi pw_first_submit_criteria (0..5), rata-rata & sebaran pw_weak_submit_count

scopedForStaff(?int $schoolId): \CodeIgniter\Database\BaseBuilder
    // Bila $schoolId NULL (admin) → builder tanpa filter
    // Bila terisi (guru)         → where('school_id', $schoolId)

cohortSummary(array $filters): array
    // COUNT per gender, per class_level, per province untuk dashboard
```

---

#### `ChallengeItemModel`

Method khusus:

```php
bankForNode(int $nodeId): array
    // where challenge_node_id, is_active=1, orderBy sequence

pickForAttempt(int $nodeId, int $count, string $mode, string $seed): array
    // mode 'fixed'  → seeded_shuffle(bank, seed) lalu ambil $count pertama
    // mode 'random' → shuffle acak lalu ambil $count
    // Untuk engine 'cari': item decoy TIDAK ikut dihitung sebagai target,
    //   tetapi selalu disertakan di payload tampilan.
    // Bila bank lebih sedikit dari $count, ambil semuanya.

withOptions(array $itemIds): array
    // 1 query ke challenge_options untuk semua item sekaligus (hindari N+1)
```

---

#### `GameSessionModel`

Method khusus:

```php
findByCode(string $sessionCode): ?GameSession
latestCompleted(int $participantId, int $studyId, int $phaseId): ?GameSession
touch(int $sessionId): void                  // update last_active_at
markStale(int $idleMinutes): int             // status active → paused bila last_active_at terlalu lama
```

---

#### `ChallengeAttemptModel`

```php
nextAttemptNo(int $sessionId, int $nodeId): int
currentInProgress(int $sessionId, int $nodeId): ?ChallengeAttempt
completedForSession(int $sessionId): array   // keyed by challenge_node_id
statsForNode(int $nodeId, array $filters): array
    // AVG(first_pass_accuracy), AVG(retry_count), AVG(hint_count),
    // median duration (via percentile query), skip_rate
```

---

#### `ItemResponseModel`

```php
createPlaceholders(int $attemptId, array $items): void
    // insertBatch satu baris per item: status 'pending', display_order berurutan

forAttempt(int $attemptId): array          // keyed by challenge_item_id
countCorrect(int $attemptId): array        // ['first_pass'=>n, 'final'=>n]
itemAnalysis(array $filters): array
    // per challenge_item_id: muncul, benar, rata durasi, jawaban salah tersering
```

---

#### `GameEventLogModel`

```php
protected $useTimestamps = false;   // waktu dikelola manual (occurred_at / server_received_at)
protected $useSoftDeletes = false;  // deleted_at diisi manual oleh deletion service
```

Method khusus:

```php
appendBatch(array $events): array
    // insertBatch dengan ignore duplikat (session_id, client_event_id).
    // Mengembalikan ['accepted'=>n, 'duplicate'=>n]
    // Implementasi: INSERT IGNORE lewat $this->db->table()->ignore(true)->insertBatch()

nextSequenceNo(int $sessionId): int
timeline(int $sessionId, int $limit = 500): array
existsClientEvent(int $sessionId, string $clientEventId): bool
softDeleteScope(array $scope, int $staffId, string $reason): int
```

---

#### Model lain (ringkas)

| Model | returnType | Method khusus |
|---|---|---|
| `SchoolModel` | array | `activeList()`, `findOrCreateByName(string $name, array $region): int` |
| `ResearchStudyModel` | array | `activeStudy(): ?array`, `requireActiveStudy(): array`; validasi `unlock_mode in_list[sequential,free]` (D13) |
| `ResearchPhaseModel` | array | `forStudy(int $studyId): array`, `findByCode(int $studyId, string $code): ?array` |
| `ParticipantConsentModel` | array | `latestFor(int $participantId): ?array` |
| `GameReleaseModel` | array | `active(): array` (throw bila tidak ada) |
| `LevelModel` | `Level` | `ordered(): array` (orderBy sequence), `findByCode(string $code): ?Level` |
| `LearningIndicatorModel` | array | `map(): array` (keyed by code) |
| `ScoringProfileModel` | array | `active(): array`, `findVersion(string $code, string $version): ?array` |
| `MediaAssetModel` | array | `map(): array` (keyed by id, di-cache), `activeKeyMap(): array` (asset_key → path), `findByKey(string $key): ?array`; `asset_type in_list[image,audio,video,sprite_frame]` sesuai §11 skema |
| `AudioAssetModel` | array | `approvedFor(string $context, string $locale): ?array` |
| `ChallengeOptionModel` | `ChallengeOption` | `forItems(array $itemIds): array` |
| `HintModel` | array | `forNode(int $nodeId): array`, `forItem(int $itemId): array` |
| `DialogueModel` | array | `intro(): array`, `forLevel(int $levelId, string $context): array` |
| `ReadingPassageModel` | `ReadingPassage` | `forLevel(int $levelId): array`, `findByKey(string $key): ?ReadingPassage`, `forIds(array $ids): array` |
| `LibraryPageModel` | `LibraryPage` | `forLevel(int $levelId): array` |
| `LibraryMediaModel` | `LibraryMedia` | `forPages(array $pageIds, bool $activeOnly = true): array` (keyed by `library_page_id`, urut `sequence`). `LibraryMedia::resolve()` → bentuk tampil (berkas aktif, atau tautan lewat `App\Libraries\MediaLink`), `null` bila tidak dapat dirender. `ContentRepository::libraryPages()` memasang media ke setiap `LibraryPage` (`loadedMedia()`, `gallery($locale)`) |
| `SessionProgressModel` | array | `primaryKey = 'session_id'`, `useAutoIncrement = false`, `ensure(int $sessionId): array` |
| `AudioUsageEventModel` | array | `usageForSession(int $sessionId): array`, `usageStats(array $filters): array` |
| `ParticipantFeedbackModel` | array | `forStudy(int $studyId): array`, `ratingDistribution(array $filters): array` |
| `AuditLogModel` | array | `record(string $action, array $opts = []): void` |
| `DataExportModel` | array | `expired(): array`, `markDone(int $id, string $path, string $sha, int $rows): void` |
| `DataDeletionRequestModel` | array | `pendingApproval(): array` |

---

## Implementasi — Validation Ruleset

`app/Validation/GelitaRules.php`:

```php
<?php
namespace App\Validation;

class GelitaRules
{
    /** Engine type yang dikenali */
    public function valid_engine_type(string $str): bool
    {
        return in_array($str, config('Gelita')->engineTypes, true);
    }

    /** Event type yang boleh ditulis */
    public function valid_event_type(string $str): bool
    {
        return in_array($str, config('Gelita')->eventTypes, true);
    }

    /** Locale yang didukung */
    public function valid_locale(string $str): bool
    {
        return in_array($str, config('Gelita')->locales, true);
    }

    /** Item harus milik attempt yang disebut */
    public function item_in_attempt(string $itemId, string $params, array $data): bool
    {
        $attemptId = (int) ($data['attempt_id'] ?? 0);
        $attempt   = model(\App\Models\ChallengeAttemptModel::class)->find($attemptId);
        return $attempt && in_array((int) $itemId, $attempt->selectedItemIds(), true);
    }

    /** Payload JSON tidak melebihi batas */
    public function json_max_bytes(?string $str, string $max): bool
    {
        return strlen((string) $str) <= (int) $max;
    }

    /** Nama pengguna siswa: huruf kecil, angka, titik, garis bawah; 3–30 karakter */
    public function valid_username(string $str): bool
    {
        return (bool) preg_match('/^[a-z0-9._]{3,30}$/', strtolower(trim($str)));
    }

    public function not_reserved_username(string $str): bool
    {
        return ! in_array(strtolower(trim($str)), config('Gelita')->reservedUsernames, true);
    }

    /**
     * Kata sandi siswa kuat. Parameter = nama field username, mis. strong_password[username]
     * Memakai PasswordPolicy — aturan yang sama dengan yang dikirim ke JavaScript.
     */
    public function strong_password(string $str, string $usernameField, array $data): bool
    {
        $r = (new \App\Libraries\PasswordPolicy())->check($str, $data[$usernameField] ?? null);
        return $r['acceptable'];
    }
}
```

Pesan di `app/Language/id/Validation.php`:

```php
'valid_engine_type' => 'Jenis tantangan tidak dikenali.',
'valid_event_type'  => 'Jenis event tidak dikenali.',
'valid_locale'      => 'Bahasa tidak didukung.',
'item_in_attempt'   => 'Butir soal tidak termasuk dalam percobaan ini.',
'json_max_bytes'    => 'Data yang dikirim terlalu besar.',
'valid_username'    => 'Nama pengguna hanya boleh huruf kecil, angka, titik, atau garis bawah (3–30 karakter).',
'not_reserved_username' => 'Nama pengguna itu tidak boleh dipakai. Pilih yang lain.',
'strong_password'   => 'Kata sandi belum kuat. Penuhi kelima syarat dan jangan memuat nama penggunamu.',
```

Validasi ulangan sandi memakai rule bawaan CI4: `password_confirm: required|matches[password]`.

---

## Implementasi — Service

### 1. `ContentRepository`

**Tanggung jawab:** membaca konten permainan dengan cache, agar setiap render halaman tidak memicu belasan query.

```php
namespace App\Services;

class ContentRepository
{
    private const TTL = 3600;

    public function levels(): array;
        // 3 Level entity, urut sequence. Cache key: content.levels.v{content_version}

    public function level(string $code): ?Level;

    public function nodesForLevel(int $levelId): array;
        // 5 ChallengeNode entity, urut sequence

    public function node(int $nodeId): ?ChallengeNode;

    public function itemBank(int $nodeId): array;
        // ChallengeItem entity + options ter-eager-load

    public function hintsFor(int $nodeId, ?int $itemId = null): array;

    public function dialogues(?int $levelId, string $context): array;

    public function libraryPages(int $levelId): array;

    public function mediaMap(): array;
        // id => storage_path, sekali query untuk seluruh media aktif

    public function flush(): void;
        // Dipanggil setelah admin menyimpan konten
}
```

**Aturan:** cache key menyertakan `game_releases.content_version` yang aktif. Saat konten diubah, admin menaikkan `content_version` atau memanggil `flush()`; sesi yang sedang berjalan tetap memakai release lamanya.

### 2. `GameContext`

**Tanggung jawab:** wadah state satu request. Tidak menyentuh business rule.

```php
class GameContext
{
    public ?GameSession $session = null;
    public ?array $progress = null;
    public ?Participant $participant = null;

    public function load(int $participantId, int $gameSessionId): bool;
    public function locale(): string;
    public function isReady(): bool;
    public function refreshProgress(): void;
}
```

### 3. `SessionService`

**Tanggung jawab:** siklus hidup peserta dan sesi. Semua operasi menulis memakai transaction.

```php
class SessionService
{
    /**
     * Mendaftarkan siswa + akun + consent + membuat sesi pertama.
     * Input : demographic + username + password + consent + phaseCode + locale + deviceInfo
     *         + registrationMetrics ['first_submit_criteria'=>0..5, 'weak_submit_count'=>n]
     * Output: ['participant' => Participant, 'session' => GameSession]
     * Transaction: schools(findOrCreate) → participants (username, password_hash, metrik)
     *              → participant_consents → game_sessions → session_progress
     *              → event session_started {via:'register'}
     * Setelah commit: session()->regenerate(true), set participant_id + game_session_id.
     */
    public function registerAndStart(array $input): array;

    /**
     * Login siswa, lalu melanjutkan atau membuat sesi pada fase yang dipilih.
     * - findByUsername; tidak ada / sandi salah / nonaktif → hasil 'invalid' (pesan identik)
     * - isLocked() → hasil 'locked' + sisa menit
     * - gagal → registerFailedLogin(); berhasil → clearFailedLogin()
     * - cari game_sessions active/paused untuk (participant, study aktif, phase)
     *     ada   → status 'active', event session_resumed {via:'login'}
     *     tidak → startNewSession(), event session_started {via:'login'}
     * - session()->regenerate(true), set participant_id + game_session_id
     * Output: ['status'=>'ok'|'invalid'|'locked'|'must_change', 'session'=>?GameSession]
     */
    public function login(string $username, string $password, string $phaseCode, string $locale, array $device): array;

    /** Siswa mengganti sandinya sendiri (wajib setelah reset guru). Event password_changed. */
    public function changePassword(int $participantId, string $current, string $new): array;

    /**
     * Guru/admin mereset sandi siswa. Membuat sandi sementara yang memenuhi kebijakan,
     * set must_change_password = 1, audit participant_password_reset.
     * Sandi sementara dikembalikan SEKALI untuk ditunjukkan guru, tidak disimpan di mana pun.
     */
    public function resetPasswordByStaff(int $participantId, int $staffId): string;

    public function logout(): void;
        // abandon attempt in_progress? TIDAK — attempt tetap in_progress agar dapat dilanjutkan.
        // Hanya event session_paused + session()->destroy().

    /**
     * Membuat sesi baru untuk peserta yang sudah terdaftar (mis. posttest).
     */
    public function startNewSession(int $participantId, string $phaseCode, string $locale, array $device): GameSession;

    /** Mengubah bahasa TANPA menyentuh progres/skor. */
    public function setLocale(int $sessionId, string $locale): void;

    /** Update last_active_at + akumulasi duration_ms. */
    public function heartbeat(int $sessionId): void;

    /** Menandai sesi selesai bila 3 level tuntas. */
    public function completeIfFinished(int $sessionId): bool;

    public function abandon(int $sessionId, string $reason = 'idle'): void;
}
```

**Aturan penting di `registerAndStart`:**

* `participant_code` dibuat server, tidak pernah dari input.
* `username` disimpan huruf kecil; divalidasi `valid_username`, `not_reserved_username`, unik.
* Kata sandi divalidasi ulang dengan `PasswordPolicy` **di dalam service** sebelum `password_hash()`, meskipun controller sudah memvalidasi.
* `pw_first_submit_criteria` dan `pw_weak_submit_count` dihitung controller dari percobaan kirim formulir yang disimpan sementara di session (lihat 04), lalu ditulis saat akun dibuat. Isi sandi tidak pernah ikut disimpan.
* `session_code` = `random_code(16)` (32 hex) — pengenal internal sesi untuk export dan log. Identitas login siswa dipegang session CI4 (`participant_id`, `game_session_id`), bukan cookie khusus.
* Bila `research_studies.require_consent = 1` dan `parent_guardian_consented = 0`, registrasi **ditolak**.
* `release_id` diambil dari `game_releases` yang aktif saat itu dan **dikunci** untuk sesi tersebut.

### 4. `ChallengeService`

**Tanggung jawab:** membuka node, menerima jawaban, menutup attempt. Ini inti permainan.

```php
class ChallengeService
{
    /**
     * Membuka node: membuat attempt, memilih item, menyiapkan payload pemain.
     * - Cek node milik level yang sudah unlock (unlock_mode study)
     * - Bila ada attempt in_progress untuk node ini → dipakai ulang (resume)
     * - Bila tidak: attempt_no = nextAttemptNo(), pilih item via
     *   ChallengeItemModel::pickForAttempt(nodeId, itemsPerRound, study.item_selection_mode,
     *                                      participant_code . '|' . nodeId)
     * - Simpan selected_item_ids_json, scorable_items
     * - Buat item_responses placeholder (status pending)
     * - Catat event challenge_opened
     * Output: ['attempt' => ChallengeAttempt, 'payload' => array]  (payload TANPA kunci jawaban)
     *
     * Isi payload tambahan per engine:
     *   rumpang + use_word_bank → 'word_bank': teks jawaban item terpilih (locale sesi)
     *                             + distractor_count pengecoh acak dari config.distractors, diacak.
     *                             Pemetaan kata→rumpang TIDAK dikirim.
     *   boleh                   → 'verdict_options' dari config node, 'require_reason'
     *   item ber-passage        → 'passages': {id: {title, body, media}} sekali per passage
     *   cari                    → TANPA 'items'; 'objects' [{ref, x, y, w, media}] (target &
     *                             jebakan identik, ref = token HMAC per attempt) + 'clues'
     *                             [{item_id, text}] hanya untuk target yang dinilai
     *   semua engine            → 'hints': [{id, item_id|null, sequence}] (id saja, teks baru
     *                             dikirim useHint()) + 'hints_count'
     */
    public function openNode(GameSession $session, int $nodeId): array;

    /**
     * Menerima satu jawaban item. Dipanggil untuk pilihan & cari (per item),
     * atau dipanggil berulang oleh submitCheck() untuk rumpang/boleh/puzzle.
     *
     * Langkah:
     *  1. validasi attempt milik session dan status in_progress
     *  2. validasi item ada di selected_item_ids_json
     *  3. ambil answer_key_json dari database (bukan dari request)
     *  4. hitung benar/salah server-side lewat grader per interaction_type
     *  5. bila item_responses.first_answer_json masih NULL → isi, set first_pass_correct
     *  6. selalu update final_answer_json, is_correct, answered_at, duration_ms
     *  7. bila jawaban berubah → change_count++, attempt.answer_change_count++
     *  8. tulis event answer_submitted / answer_changed
     * Semua dalam satu transaction.
     *
     * allow_retry = false (pilihan): butir yang sudah dijawab tidak dinilai ulang;
     * kiriman ulang dibalas hasil tersimpan dengan already_answered = true.
     *
     * Output: ['correct'=>bool, 'first_pass'=>bool, 'wrong_click'=>bool, 'decoy'=>bool,
     *          'already_answered'=>bool, 'feedback'=>?string,
     *          'correct_option_key'=>?string  // hanya sesudah dijawab & hanya bila allow_retry = false
     *          'progress'=>['answered'=>n, 'total'=>n]]   // total tidak menghitung jebakan
     */
    public function submitAnswer(ChallengeAttempt $attempt, int $itemId, array $answer, array $meta): array;

    /**
     * Tombol "Periksa jawaban" untuk engine batch (rumpang, boleh, puzzle).
     * - attempt.check_count++
     * - retry_count = max(0, check_count - 1)
     * - menilai semua item yang dikirim sekaligus
     * - pemeriksaan pertama menetapkan first_pass_correct seluruh item
     * - event challenge_checked
     * Output: ['results'=>[itemId=>bool], 'correct_count'=>n, 'total'=>n, 'all_correct'=>bool]
     */
    public function submitCheck(ChallengeAttempt $attempt, array $answers): array;

    /**
     * Id butir yang belum dijawab/dilewati — syarat ChallengeApiController::complete().
     * Hanya butir yang memang diminta dijawab (expectsAnswer): objek jebakan `cari`
     * punya baris item_responses tetapi tidak pernah menahan attempt tetap terbuka.
     */
    public function pendingItemIds(ChallengeAttempt $attempt): array;

    /** Mencatat pemakaian hint. attempt.hint_count++, item_responses.hint_used = 1 */
    public function useHint(ChallengeAttempt $attempt, int $hintId, ?int $itemId): array;

    /**
     * Menutup attempt: memanggil ScoringService, update session_progress,
     * membuka level berikutnya bila 5 node level tuntas.
     * Output: ['score'=>float,'stars'=>int,'first_pass_accuracy'=>float,
     *          'level_completed'=>bool,'next_level_unlocked'=>?int, 'shards'=>int]
     */
    public function completeAttempt(ChallengeAttempt $attempt): array;

    /** Attempt ditinggalkan (pindah layar / tutup tab). */
    public function abandonAttempt(ChallengeAttempt $attempt): void;
}
```

**Grader per `interaction_type`** — ditaruh di `ChallengeService` sebagai method privat, bukan kelas terpisah (hanya 9 cabang, tidak perlu pola strategy):

| interaction_type | Cara menilai |
|---|---|
| `puzzle_arrange` | `answer.order` sama persis dengan `answer_key_json.order`. Simpan `pieces_correct` di payload event |
| `ordering` | `answer.order` (array key) sama persis dengan kunci |
| `fill_blank_bank` | `mb_strtolower(trim(answer.text))` sama dengan kunci pada locale sesi |
| `fill_blank_free` | `mb_strtolower(trim(answer.text))` ada di `accept_{locale}`; bandingkan juga ke `accept_id` |
| `verdict_card` | `answer.verdict === answer_key_json.verdict`; `answer.verdict` wajib salah satu `node.config.verdict_options` |
| `verdict_reason` | sama seperti di atas; `reason_text` disimpan, `reason_review_status = 'pending'`, **alasan tidak memengaruhi benar/salah** |
| `single_choice` | `answer.option_key === answer_key_json.option_key` |
| `source_trust` | sama seperti `single_choice` |
| `find_object` | Klien mengirim `answer.object` (token objek per attempt); `ChallengeService::resolveObjectAnswer()` menerjemahkannya ke id butir yang diklik, lalu benar bila id itu = id butir target. `answer.item_id` mentah dari klien diabaikan. Klik objek salah → `wrong_click_count++`, event `wrong_target_clicked`; bila itu klik **pertama** untuk petunjuk tersebut, `first_answer_json` diisi dan `first_pass_correct = 0` (tetap 0 walau objek benar ditemukan sesudahnya). Butir tetap terbuka sampai objek benar diklik |

**Aturan first-pass per engine** (ini menentukan ukuran utama penelitian):

| Engine | Kapan first-pass ditetapkan |
|---|---|
| `puzzle` | pada pemeriksaan pertama |
| `rumpang` | pada pemeriksaan pertama, per blank |
| `boleh` | pada pemeriksaan pertama, per kartu (termasuk pilihan Pendapat) |
| `pilihan` | jawaban pertama = jawaban final (`allow_retry = false`, ditegakkan server: jawaban berikutnya untuk butir yang sama diabaikan) |
| `cari` | klik pertama untuk tiap petunjuk — klik pertama yang salah menutup first-pass sebagai salah |

### 5. `ScoringService`

**Tanggung jawab:** satu-satunya tempat yang menghitung skor.

```php
class ScoringService
{
    /**
     * Menghitung skor satu attempt dan menuliskannya ke baris attempt.
     * Profile diambil dari node.scoring_profile_id, atau profil aktif bila NULL.
     * scoring_version disalin ke attempt agar hasil historis dapat direproduksi.
     */
    public function scoreAttempt(ChallengeAttempt $attempt): array
    {
        // scorable = jumlah item_responses milik attempt yang item-nya scorable = 1
        // firstPassAccuracy = firstPassCorrect / scorable * 100
        // finalAccuracy     = finalCorrect / scorable * 100
        // hintPenalty  = hint_count  * profile.hint_penalty_per_use
        // retryPenalty = retry_count * profile.retry_penalty_per_extra_attempt
        // independence = clamp(100 - hintPenalty - retryPenalty, 0, 100)
        // score = clamp(w1*firstPass + w2*final + w3*independence, 0, 100)
        // stars = lihat tabel
    }

    public function levelScore(int $sessionId, int $levelId): array;
        // weighted mean 5 node berdasarkan scorable_items
        // SUM(score * scorable_items) / SUM(scorable_items)

    public function totalScore(int $sessionId): float;
        // mean dari 3 level score (level yang belum dikerjakan dihitung 0)

    public function recompute(int $attemptId, string $profileCode, string $version): array;
        // untuk command gelita:score:recompute
}
```

Bintang:

```
3  jika score >= profile.three_star_min_score DAN first_pass_accuracy >= profile.three_star_min_first_pass
2  jika score >= profile.two_star_min_score
1  jika status = 'completed'
0  jika belum selesai / ditinggalkan
```

**Waktu tidak menjadi penalti.** Durasi disimpan dan dianalisis sebagai indikator proses, bukan pengurang skor, agar siswa lambat tetapi teliti tidak dirugikan.

### 6. `EventService`

**Tanggung jawab:** menulis raw event dengan idempotency.

```php
class EventService
{
    /**
     * Menerima batch event dari client.
     * - maksimum config('Gelita')->maxEventsPerBatch per request
     * - event_type harus terdaftar
     * - client_event_id duplikat → dihitung sebagai 'duplicate', tidak ditulis ulang
     * - event_uuid dibuat server
     * - sequence_no diambil dari client; bila kosong, server memakai nextSequenceNo()
     * - server_received_at = now(micro)
     * Output: ['accepted'=>n, 'duplicate'=>n, 'rejected'=>[['index'=>i,'reason'=>'...']]]
     */
    public function ingest(GameSession $session, array $events): array;

    /** Menulis satu event dari sisi server (dipanggil Service lain). */
    public function record(GameSession $session, string $type, array $payload = [], array $refs = []): void;

    /**
     * Telemetry audio terstruktur → audio_usage_events + raw event dalam satu transaction.
     * Idempotent terhadap client_event_id; true = ditulis, false = duplikat.
     */
    public function recordAudio(GameSession $session, array $audioEvent): bool;
}
```

Rincian yang dikunci implementasi:

* Rujukan event dari klien memakai nama kontrak 04/06 — `level_id`, `node_id`, `attempt_id`, `item_id` — dan nama kolom lengkap (`challenge_node_id`, …) tetap diterima.
* `occurred_at` klien (ISO 8601, mis. `2026-09-21T02:14:22.481Z`) dikonversi ke zona aplikasi dengan pecahan detik utuh (`2026-09-21 09:14:22.481000`); waktu yang tidak dapat diurai diganti waktu server. Dikunci `tests/unit/EventTimeTest.php`.
* `GameEventLogModel::timeline()` mengurutkan `occurred_at`, lalu `sequence_no`, lalu `id` (aturan 8 di 01_DATABASE.md): `sequence_no` klien dimulai ulang setiap halaman dimuat.

`record()` dipanggil di dalam transaction milik Service pemanggil, sehingga "simpan jawaban + simpan event + update progres" benar-benar atomik.

### 7. `AnalyticsService`

**Tanggung jawab:** menyediakan dataset ternormalisasi untuk dashboard dan export. Tidak menggambar chart, tidak membentuk HTML.

```php
class AnalyticsService
{
    public function summary(array $filters): array;
        // participant_count, session_count, completion_rate, avg_score,
        // avg_first_pass_accuracy, avg_duration_ms, hint_usage, audio_usage,
        // pretest_posttest_delta

    public function levelBreakdown(array $filters): array;
        // per level: avg score, avg first-pass, completion, avg duration

    public function nodeDifficulty(array $filters): array;
        // per node: first_pass, final, retry, hint, median duration, skip rate,
        // difficulty_index (rumus di 01_DATABASE.md)

    public function itemAnalysis(array $filters): array;
        // per item: muncul, benar, p, D (27% atas vs bawah), rata detik,
        // distraktor tersering. Ini yang dipakai tab Butir Soal.

    public function indicatorMastery(array $filters): array;
        // per learning_indicator: evidence_count, correct_count, mastery_ratio,
        // rata response time. Bukan label biner.

    public function participantProfile(int $participantId, ?int $schoolScope): array;
        // demographic sesuai hak akses, skor per level, total, completion,
        // mean response time, attempts, changes, hints, audio, node tersulit,
        // indikator, fase, perbandingan pre/post bila kompatibel

    public function prePostComparison(array $filters): array;

    public function digitalSecurityLiteracy(array $filters): array;
        // Dari participants: distribusi syarat sandi terpenuhi pada percobaan pertama (0–5),
        // proporsi yang langsung kuat, rata-rata penolakan sandi lemah.
        // Dari item Wonosobo node 5: ketepatan per digital_pillar.
        // Tidak pernah mengakses password_hash.
        // Hanya membandingkan sesi dengan release_id + scoring_version + content_version
        // yang kompatibel. Pasangan tidak kompatibel diberi label terpisah,
        // tidak dicampur ke dalam perhitungan delta.

    public function eventTimeline(int $sessionId, int $limit = 500): array;
}
```

**`$filters` yang dikenali seluruh method:**

```php
[
  'study_id'    => ?int,
  'phase_code'  => ?string,   // umum|pretest|posttest
  'level_id'    => ?int,
  'node_id'     => ?int,
  'school_id'   => ?int,      // WAJIB diisi filter untuk role guru
  'class_level' => ?string,
  'province_code' => ?string,
  'date_from'   => ?string,
  'date_to'     => ?string,
  'locale'      => ?string,
]
```

**Aturan otorisasi:** `AnalyticsService` menerima `?int $schoolScope`. Bila bukan NULL, setiap query **wajib** menambahkan `participants.school_id = $schoolScope`. Ini diterapkan di Service, bukan hanya di filter route, supaya tidak bisa dilewati lewat jalur lain.

### 8. `ContentImportService`

**Tanggung jawab:** memuat workbook bank soal (XLSX) ke tabel konten dalam satu transaction. Dipakai panel admin dan `php spark gelita:bank:import`. Struktur sheet workbook ditetapkan di 07_FEATURE_INTEGRATION.md.

```php
class ContentImportService
{
    /**
     * Membaca workbook, memvalidasi seluruh baris, TANPA menulis database.
     * Output: ['ok'=>bool, 'summary'=>[per node: jumlah item, passage, opsi, hint],
     *          'errors'=>[['sheet'=>'items','row'=>14,'message'=>'…'], …],
     *          'warnings'=>[… review_status needs_verification, bank < minimum …]]
     */
    public function preview(string $xlsxPath): array;

    /**
     * Menjalankan impor. Mode 'upsert' (default): baris dicocokkan lewat
     * passage_key / item_key / option_key; baris yang sudah ada diperbarui.
     * Item yang sudah punya item_responses TIDAK boleh diubah answer_key-nya —
     * baris itu ditolak dengan pesan jelas agar data lama tetap sebanding.
     * Seluruh impor satu transaction; gagal satu → batal semua.
     * Setelah sukses: ContentRepository::flush(), audit 'content_import'.
     */
    public function import(string $xlsxPath, int $staffId, string $mode = 'upsert'): array;

    /** Menghasilkan workbook templat kosong dengan header & contoh baris */
    public function template(): string;
}
```

---

## Aturan Sistem

1. `answer_key_json` tidak pernah keluar dari server. Payload pemain dibentuk lewat `ChallengeItem::toPlayerArray()`.
2. Semua penilaian benar/salah dilakukan `ChallengeService`, memakai kunci dari database. Field `correct` yang dikirim browser diabaikan.
3. Operasi "simpan jawaban + tulis event + update attempt" dibungkus satu transaction `$db->transStart()` / `transComplete()`.
4. Satu attempt hanya boleh menerima jawaban untuk item yang ada di `selected_item_ids_json`.
5. `ScoringService` adalah satu-satunya tempat rumus skor ditulis. Tidak boleh ada perhitungan skor di controller, view, atau JavaScript.
6. `scoring_version` dan `release_id` disalin ke baris hasil saat scoring, sehingga hasil lama tetap dapat direproduksi setelah formula berubah.
7. `EventService::ingest()` idempotent terhadap `client_event_id` per session.
8. Model tidak memanggil Service. Service boleh memanggil Model dan Service lain.
9. Query yang berpotensi N+1 (item + option, session + progress, attempt + responses) wajib memakai eager load batch.
10. `AnalyticsService` tidak pernah mengembalikan nama peserta **maupun username** bila pemanggil meminta mode anonim.
11. `password_hash` tidak pernah keluar dari `ParticipantModel`/`StaffUserModel` ke view, API, export, atau log. Gunakan `Participant::toSafeArray()`.
12. Aturan kekuatan sandi hanya diputuskan `PasswordPolicy`; validasi controller dan service sama-sama memanggilnya.

---

## Dependency

Dari **01_DATABASE.md**: nama tabel, kolom, tipe data, FK, aturan uniqueness, rumus scoring, daftar `event_type` dan `interaction_type`.

Dari **02_PROJECT_FOUNDATION.md**: `BaseController`, helper `gelita`/`content`, `Config\Gelita`, service container, ruleset validasi terdaftar.

---

## Hasil Akhir

Setelah tahap ini selesai:

* 29 Model, 11 Entity, 8 Service, dan 1 ruleset validasi sudah ada dan dapat di-autoload.
* `SessionService::registerAndStart()` menolak sandi `kedu2026` (level sedang) dan menerima `Kedu#2026`; `login()` mengunci akun siswa setelah 8 kali gagal.
* `ContentImportService::preview()` terhadap workbook bank soal melaporkan ringkasan 15 node dan daftar galat per baris tanpa menulis database.
* `model(ChallengeNodeModel::class)->find(1)` mengembalikan Entity dengan `config_json` sudah berupa array.
* `service('contentRepository')->levels()` mengembalikan 3 level urut Temanggung → Magelang → Wonosobo.
* `ScoringService::scoreAttempt()` dapat diuji langsung dengan data attempt buatan dan menghasilkan skor serta bintang sesuai formula.
* `EventService::ingest()` menolak duplikat `client_event_id` dan `event_type` tak dikenal.
* Belum ada route/controller (tahap 4), tetapi seluruh logika data dan bisnis sudah dapat dipanggil dari `php spark` atau tinker.
