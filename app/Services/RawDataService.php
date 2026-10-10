<?php

namespace App\Services;

use App\Entities\ChallengeItem;
use App\Entities\ChallengeNode;
use App\Libraries\AnswerText;
use App\Libraries\ExportLabels;
use App\Models\ChallengeItemModel;
use App\Models\ChallengeNodeModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * Data mentah penelitian per dimensi (Dok 3 §8.4) — satu tempat query untuk
 * tabel `/admin/data/{dimensi}`, CSV per tabel, dan ekspor XLSX/CSV-ZIP.
 * Pengganti query ekspor lama dan analitik olahan.
 *
 * Dimensi = kode lembar ekspor §9.2 (`self::DIMENSIONS`); lembar `kamus`
 * dibangun ExportService dari ExportLabels, bukan dari query.
 *
 * Cakupan (`StaffScope`, lapis ketiga otorisasi) diterapkan di sini:
 * - guru: `participants.school_id` sekolahnya dipaksakan di setiap query,
 *   kolom identitas tidak pernah dibaca, tanpa dimensi `event`, tanpa
 *   kolom `answer_key`;
 * - admin anonim: kolom identitas tidak dibuat; `school_ref` tetap.
 * `password_hash`, `failed_login_count`, `locked_until`, dan `ip_hash`
 * tidak pernah dibaca.
 *
 * Nilai baris (`shape()`): kode mentah apa adanya, waktu ISO 8601 zona
 * Asia/Jakarta dengan milidetik, durasi milidetik (int), boolean 1/0, JSON
 * sebagai teks. Mode berlabel (`$labeled`) mengganti kode dengan label
 * (ExportLabels::label(), nama wilayah, jawaban dalam kalimat).
 * `$readableAnswers` writes only the answer columns (first/final answer,
 * try answer, answer key) as sentences and leaves every other value coded;
 * the raw data screen uses it by default (AnswerText).
 *
 * `page()` dan `streamCsv()` memakai cakupan yang diikat lewat konstruktor
 * atau `withScope()`; tanpa cakupan keduanya menolak (LogicException).
 *
 * ## Kontrak `page()` (dipakai tabel admin; bentuknya dipertahankan)
 *
 * ```php
 * [
 *   'rows'     => list<array<string, int|string|null>>, // kunci = columns[*].key, nilai mentah berkode (= CSV)
 *   'total'    => int,          // jumlah baris semua halaman dengan filter yang sama
 *   'page'     => int,          // halaman yang dipakai, 1-based, dijepit ke 1..halaman terakhir
 *   'per_page' => int,          // 25 | 50 | 100 (nilai lain → 25)
 *   'sort'     => ?string,      // urutan yang dipakai: "kolom" (naik) atau "-kolom" (turun); null = bawaan
 *   'columns'  => list<array{key: string, label: string, type: string}>,
 * ]
 * ```
 *
 * `type` ∈ `code|text|int|ms|datetime|bool|enum|json` (ExportLabels).
 * Kolom yang dapat diurutkan: `sortable($dimension)`. Label nilai untuk
 * tampilan: `ExportLabels::label($dimension, $key, $value)`.
 */
class RawDataService
{
    /** Kode dimensi = kode lembar ekspor §9.2, urutan resmi (tanpa `kamus`). */
    public const DIMENSIONS = [
        'peserta', 'sesi', 'perangkat', 'progres', 'pengerjaan', 'jawaban', 'percobaan',
        'navigasi', 'bantuan', 'selesai', 'event', 'masukan', 'butir',
    ];

    /** Dimensi yang hanya untuk admin. */
    public const ADMIN_ONLY_DIMENSIONS = ['event'];

    /** Answer columns that `$readableAnswers` writes as sentences (AnswerText). */
    public const ANSWER_COLUMNS = ['first_answer', 'final_answer', 'answer', 'answer_key'];

    /** Kolom yang hanya ada pada data beridentitas (admin, tidak anonim). */
    public const IDENTITY_COLUMNS = ['display_name', 'username', 'school_name', 'school_npsn'];

    /** Kolom yang hanya ada untuk admin. */
    public const ADMIN_ONLY_COLUMNS = ['answer_key'];

    /** Kunci filter yang dikenali; kunci lain diabaikan. */
    public const FILTER_KEYS = [
        'study_id', 'participant_id', 'participant_code', 'school_id', 'class_level', 'province_code',
        'locale', 'level_id', 'node_id', 'variant_group', 'date_from', 'date_to',
    ];

    public const PER_PAGE = [25, 50, 100];

    public const NAVIGATION_EVENTS = [
        'screen_viewed', 'overlay_opened', 'dialogue_advanced', 'library_opened', 'library_page_viewed',
        'locale_changed', 'page_hidden', 'page_visible', 'viewport_changed', 'reading_progress',
        'package_changed', 'events_dropped',
    ];

    public const COMPLETION_EVENTS = [
        'challenge_completed', 'challenge_abandoned', 'challenge_tries_exhausted', 'clue_failed',
        'level_completed', 'session_completed', 'intro_completed', 'feedback_submitted',
    ];

    /** Ukuran potongan iterasi keyset (`rows()`, `streamCsv()`). */
    public const CHUNK = 2000;

    /**
     * Kolom per dimensi, urut resmi §9.2: kolom → ekspresi SQL untuk
     * pengurutan, atau null bila nilainya dihitung di PHP (tidak dapat
     * diurutkan).
     *
     * @var array<string, array<string, list<string>|string|null>>
     */
    private const COLUMNS = [
        'peserta' => [
            'participant_code' => 'participants.participant_code', 'display_name' => 'participants.display_name',
            'username' => 'participants.username', 'school_name' => 'participants.school_name_snapshot',
            'school_npsn' => null, 'school_ref' => 'participants.school_id', 'age' => 'participants.age',
            'gender' => 'participants.gender', 'class_level' => 'participants.class_level',
            'country' => 'participants.country_name_snapshot', 'province' => 'participants.province_name_snapshot',
            'district' => 'participants.district_name_snapshot', 'registered_at' => 'participants.created_at',
            'assented_at' => 'participants.assented_at', 'intro_seen_at' => 'participants.intro_seen_at',
            'pw_first_submit_criteria' => 'participants.pw_first_submit_criteria',
            'pw_weak_submit_count' => 'participants.pw_weak_submit_count',
        ],
        'sesi' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'study_code' => 'research_studies.code', 'release_code' => 'game_releases.release_code',
            'app_client_version' => 'game_sessions.app_client_version', 'package_code' => 'game_sessions.package_code',
            'package_mode' => 'game_sessions.package_mode',
            'locale' => 'game_sessions.locale', 'status' => 'game_sessions.status',
            'started_at' => 'game_sessions.started_at', 'last_active_at' => 'game_sessions.last_active_at',
            'ended_at' => 'game_sessions.ended_at', 'duration_ms' => 'game_sessions.duration_ms',
            'device_type' => 'game_sessions.device_type', 'os_name' => 'game_sessions.os_name',
            'browser_name' => 'game_sessions.browser_name', 'screen_size' => 'game_sessions.screen_size',
            'is_touch' => 'game_sessions.is_touch',
        ],
        'perangkat' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'via' => 'device_logs.via', 'created_at' => 'device_logs.created_at', 'device_type' => 'device_logs.device_type',
            'device_brand' => 'device_logs.device_brand', 'device_model' => 'device_logs.device_model',
            'os_name' => 'device_logs.os_name', 'os_version' => 'device_logs.os_version',
            'os_platform' => 'device_logs.os_platform', 'browser_name' => 'device_logs.browser_name',
            'browser_version' => 'device_logs.browser_version', 'browser_engine' => 'device_logs.browser_engine',
            'is_bot' => 'device_logs.is_bot', 'screen_w' => 'device_logs.screen_w', 'screen_h' => 'device_logs.screen_h',
            'viewport_w' => 'device_logs.viewport_w', 'viewport_h' => 'device_logs.viewport_h',
            'pixel_ratio' => 'device_logs.pixel_ratio', 'touch_points' => 'device_logs.touch_points',
            'orientation' => 'device_logs.orientation', 'is_standalone' => 'device_logs.is_standalone',
            'language' => 'device_logs.language', 'timezone' => 'device_logs.timezone',
            'corrected' => 'device_logs.corrected', 'detector' => 'device_logs.detector',
            'user_agent' => 'device_logs.user_agent', 'client_hints' => null,
        ],
        'progres' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'current_level' => 'session_progress.current_level_id', 'current_node_ref' => 'session_progress.current_node_id',
            'unlocked_level_sequence' => 'session_progress.unlocked_level_sequence',
            'completed_nodes' => 'session_progress.completed_nodes', 'total_nodes' => null,
            'completed_levels' => 'session_progress.completed_levels', 'total_stars' => 'session_progress.total_stars',
            'updated_at' => 'session_progress.updated_at',
        ],
        'pengerjaan' => [
            'attempt_id' => 'challenge_attempts.id', 'session_code' => 'game_sessions.session_code',
            'participant_code' => 'participants.participant_code', 'level' => 'levels.sequence',
            'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'node_sequence' => 'challenge_nodes.sequence',
            'engine_type' => 'challenge_nodes.engine_type', 'variant_group' => 'challenge_attempts.variant_group',
            'variant_title' => null, 'attempt_no' => 'challenge_attempts.attempt_no', 'status' => 'challenge_attempts.status',
            'completion_reason' => 'challenge_attempts.completion_reason', 'scorable_items' => 'challenge_attempts.scorable_items',
            'first_pass_correct' => 'challenge_attempts.first_pass_correct', 'final_correct' => 'challenge_attempts.final_correct',
            'check_count' => 'challenge_attempts.check_count', 'max_tries' => 'challenge_attempts.max_tries',
            'hint_count' => 'challenge_attempts.hint_count', 'answer_change_count' => 'challenge_attempts.answer_change_count',
            'audio_use_count' => 'challenge_attempts.audio_use_count', 'stars' => 'challenge_attempts.stars',
            'started_at' => 'challenge_attempts.started_at', 'completed_at' => 'challenge_attempts.completed_at',
            'duration_ms' => 'challenge_attempts.duration_ms',
        ],
        'jawaban' => [
            'attempt_id' => 'challenge_attempts.id', 'session_code' => 'game_sessions.session_code',
            'participant_code' => 'participants.participant_code', 'level' => 'levels.sequence',
            'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'variant_group' => 'challenge_attempts.variant_group',
            'item_key' => 'challenge_items.item_key', 'interaction_type' => 'challenge_items.interaction_type',
            'bloom_level' => 'challenge_items.bloom_level', 'skill' => null, 'indicator' => null,
            'prompt' => null, 'display_order' => 'item_responses.display_order', 'status' => 'item_responses.status',
            'try_count' => 'item_responses.try_count', 'first_answer' => null, 'final_answer' => null, 'answer_key' => null,
            'first_pass_correct' => 'item_responses.first_pass_correct', 'is_correct' => 'item_responses.is_correct',
            'solved_on_try' => 'item_responses.solved_on_try', 'change_count' => 'item_responses.change_count',
            'hint_used' => 'item_responses.hint_used', 'wrong_click_count' => 'item_responses.wrong_click_count',
            'started_at' => 'item_responses.started_at', 'answered_at' => 'item_responses.answered_at',
            'duration_ms' => 'item_responses.duration_ms',
        ],
        'percobaan' => [
            'attempt_id' => 'challenge_attempts.id', 'session_code' => 'game_sessions.session_code',
            'participant_code' => 'participants.participant_code',
            'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'variant_group' => 'challenge_attempts.variant_group',
            'item_key' => 'challenge_items.item_key', 'try_no' => 'item_tries.try_no', 'check_no' => 'item_tries.check_no',
            'answer' => null, 'is_correct' => 'item_tries.is_correct', 'hint_opened_before' => 'item_tries.hint_opened_before',
            'first_input_ms' => 'item_tries.first_input_ms', 'last_input_ms' => 'item_tries.last_input_ms',
            'occurred_at' => 'item_tries.occurred_at',
        ],
        'navigasi' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'sequence_no' => 'game_event_logs.sequence_no', 'occurred_at' => 'game_event_logs.occurred_at',
            'server_received_at' => 'game_event_logs.server_received_at',
            'clock_offset_ms' => 'game_event_logs.clock_offset_ms', 'event_type' => 'game_event_logs.event_type',
            'screen' => null, 'from_screen' => null, 'level' => 'game_event_logs.level_id',
            'node_ref' => 'game_event_logs.challenge_node_id', 'attempt_id' => 'game_event_logs.challenge_attempt_id',
            'payload' => null,
        ],
        'bantuan' => [
            'session_code' => 'bantuan.session_code', 'participant_code' => 'bantuan.participant_code',
            'occurred_at' => 'bantuan.occurred_at', 'jenis' => 'bantuan.jenis', 'level' => 'bantuan.level_id',
            'node_ref' => 'bantuan.node_id', 'attempt_id' => 'bantuan.attempt_id', 'item_key' => null, 'hint_id' => null,
            'asset_key' => 'bantuan.asset_key', 'audio_context' => 'bantuan.audio_context',
            'audio_locale' => 'bantuan.audio_locale', 'action' => 'bantuan.action', 'play_index' => 'bantuan.play_index',
            'listened_ms' => 'bantuan.listened_ms', 'completed' => 'bantuan.completed',
        ],
        'selesai' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'occurred_at' => 'game_event_logs.occurred_at', 'event_type' => 'game_event_logs.event_type',
            'level' => 'game_event_logs.level_id', 'node_ref' => 'game_event_logs.challenge_node_id',
            'attempt_id' => 'game_event_logs.challenge_attempt_id', 'payload' => null,
        ],
        'event' => [
            'id' => 'game_event_logs.id', 'event_uuid' => 'game_event_logs.event_uuid',
            'client_event_id' => 'game_event_logs.client_event_id', 'session_id' => 'game_event_logs.session_id',
            'session_code' => 'game_sessions.session_code', 'participant_id' => 'game_event_logs.participant_id',
            'participant_code' => 'participants.participant_code', 'level_id' => 'game_event_logs.level_id',
            'challenge_node_id' => 'game_event_logs.challenge_node_id',
            'challenge_attempt_id' => 'game_event_logs.challenge_attempt_id',
            'challenge_item_id' => 'game_event_logs.challenge_item_id', 'event_type' => 'game_event_logs.event_type',
            'sequence_no' => 'game_event_logs.sequence_no', 'occurred_at' => 'game_event_logs.occurred_at',
            'server_received_at' => 'game_event_logs.server_received_at',
            'clock_offset_ms' => 'game_event_logs.clock_offset_ms', 'payload_json' => null,
        ],
        'masukan' => [
            'participant_code' => 'participants.participant_code', 'session_code' => 'game_sessions.session_code',
            'rating' => 'participant_feedback.rating', 'liked_most' => null, 'hardest_part' => null,
            'new_learning' => null, 'suggestion' => null, 'submitted_at' => 'participant_feedback.submitted_at',
        ],
        'butir' => [
            'item_key' => 'challenge_items.item_key', 'level' => 'levels.sequence',
            'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'variant_group' => 'challenge_items.variant_group',
            'variant_title' => null, 'sequence' => 'challenge_items.sequence',
            'interaction_type' => 'challenge_items.interaction_type', 'bloom_level' => 'challenge_items.bloom_level',
            'skill' => null, 'indicator' => null, 'prompt' => null, 'passage_key' => 'reading_passages.passage_key',
            'answer_key' => null, 'decoy' => null, 'scorable' => 'challenge_items.scorable',
            'is_active' => 'challenge_items.is_active', 'media_asset_key' => 'media_assets.asset_key',
        ],
    ];

    /** Kunci unik tiap dimensi untuk iterasi keyset dan pemecah seri urutan. */
    private const KEYS = [
        'peserta'    => 'participants.id',
        'sesi'       => 'game_sessions.id',
        'perangkat'  => 'device_logs.id',
        'progres'    => 'session_progress.session_id',
        'pengerjaan' => 'challenge_attempts.id',
        'jawaban'    => 'item_responses.id',
        'percobaan'  => 'item_tries.id',
        'navigasi'   => 'game_event_logs.id',
        'bantuan'    => 'bantuan._key',
        'selesai'    => 'game_event_logs.id',
        'event'      => 'game_event_logs.id',
        'masukan'    => 'participant_feedback.id',
        'butir'      => 'challenge_items.id',
    ];

    /** Urutan bawaan tabel admin (terbaru dulu untuk data kejadian). */
    private const DEFAULT_SORT = [
        'peserta'    => 'participant_code',
        'sesi'       => '-started_at',
        'perangkat'  => '-created_at',
        'progres'    => '-updated_at',
        'pengerjaan' => '-started_at',
        'jawaban'    => '-started_at',
        'percobaan'  => '-occurred_at',
        'navigasi'   => '-occurred_at',
        'bantuan'    => '-occurred_at',
        'selesai'    => '-occurred_at',
        'event'      => '-occurred_at',
        'masukan'    => '-submitted_at',
        'butir'      => 'node_ref',
    ];

    /** @var array<string, mixed> peta id → isi konten, dimuat sekali per objek */
    private array $maps = [];

    public function __construct(private ?StaffScope $scope = null)
    {
    }

    /** Salinan yang terikat cakupan pemanggil (untuk page() dan streamCsv()). */
    public function withScope(StaffScope $scope): static
    {
        $copy        = clone $this;
        $copy->scope = $scope;

        return $copy;
    }

    // ------------------------------------------------------------- kontrak

    /**
     * Builder satu dimensi dalam cakupan $scope, sudah terfilter, tanpa
     * urutan dan batas. Setiap baris memuat `_key` (kunci unik) dan kolom
     * mentah; ubah ke kolom §9.2 dengan `shape()`.
     *
     * @param array<string, mixed> $filters kunci FILTER_KEYS; selain itu diabaikan
     *
     * @throws \InvalidArgumentException dimensi tidak dikenal
     * @throws \DomainException          dimensi tidak boleh dibaca $scope
     */
    public function query(string $dimension, array $filters, StaffScope $scope): BaseBuilder
    {
        $this->assertAllowed($dimension, $scope);

        $filters = $this->sanitizeFilters($filters);

        return match ($dimension) {
            'peserta'    => $this->participantQuery($filters, $scope),
            'sesi'       => $this->sessionQuery($filters, $scope),
            'perangkat'  => $this->deviceQuery($filters, $scope),
            'progres'    => $this->progressQuery($filters, $scope),
            'pengerjaan' => $this->attemptQuery($filters, $scope),
            'jawaban'    => $this->responseQuery($filters, $scope),
            'percobaan'  => $this->tryQuery($filters, $scope),
            'navigasi'   => $this->eventQuery($filters, $scope, self::NAVIGATION_EVENTS),
            'bantuan'    => $this->helpQuery($filters, $scope),
            'selesai'    => $this->eventQuery($filters, $scope, self::COMPLETION_EVENTS),
            'event'      => $this->eventQuery($filters, $scope, null),
            'masukan'    => $this->feedbackQuery($filters, $scope),
            'butir'      => $this->itemQuery($filters),
        };
    }

    /**
     * Satu halaman tabel admin dalam cakupan terikat. Bentuk hasil: lihat
     * docblock kelas ("Kontrak page()").
     *
     * @param array<string, mixed> $filters
     *
     * @return array{rows: list<array<string, int|string|null>>, total: int, page: int, per_page: int,
     *               sort: ?string, columns: list<array{key: string, label: string, type: string}>}
     */
    public function page(string $dimension, array $filters, int $page, int $perPage, ?string $sort, bool $readableAnswers = false): array
    {
        $scope   = $this->boundScope();
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];
        $total   = $this->query($dimension, $filters, $scope)->countAllResults();
        $last    = max(1, (int) ceil($total / $perPage));
        $page    = min(max(1, $page), $last);
        $sort    = $this->resolveSort($dimension, $sort);

        $builder = $this->query($dimension, $filters, $scope);
        $this->applySort($builder, $dimension, $sort);

        $rows = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'rows'     => array_map(fn (array $row): array => $this->shape($dimension, $row, $scope, false, $readableAnswers), $rows),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'sort'     => $sort,
            'columns'  => $this->columnSpecs($dimension, $scope, $readableAnswers),
        ];
    }

    /**
     * Menulis seluruh baris satu dimensi (cakupan terikat) sebagai CSV ke
     * $handle: UTF-8 dengan BOM, pemisah koma, baris judul = kode kolom,
     * nilai berkode (sama dengan `page()['rows']`). `$readableAnswers`
     * matches the same flag of `page()`, so the CSV equals the table on screen.
     *
     * @param array<string, mixed> $filters
     * @param resource             $handle
     *
     * @return int jumlah baris data (tanpa judul)
     */
    public function streamCsv(string $dimension, array $filters, $handle, bool $readableAnswers = false): int
    {
        $scope   = $this->boundScope();
        $columns = $this->columns($dimension, $scope);

        fwrite($handle, "\xEF\xBB\xBF");
        self::writeCsvLine($handle, $columns);

        $count = 0;

        foreach ($this->rows($dimension, $filters, $scope, false, $readableAnswers) as $row) {
            self::writeCsvLine($handle, array_map(static fn (string $c): mixed => $row[$c] ?? null, $columns));
            $count++;
        }

        return $count;
    }

    /**
     * Every row of one dimension in the bound scope, shaped like
     * `page()['rows']`, ordered by the unique key (keyset iteration). For
     * small sets that must not be cut at a page size, e.g. one student's
     * try history on the Jawaban tab.
     *
     * @param array<string, mixed> $filters
     *
     * @return array{rows: list<array<string, int|string|null>>, columns: list<array{key: string, label: string, type: string}>}
     */
    public function all(string $dimension, array $filters, bool $readableAnswers = false): array
    {
        $scope = $this->boundScope();

        return [
            'rows'    => iterator_to_array($this->rows($dimension, $filters, $scope, false, $readableAnswers), false),
            'columns' => $this->columnSpecs($dimension, $scope, $readableAnswers),
        ];
    }

    // --------------------------------------------------------- pelengkap

    /**
     * Semua baris satu dimensi lewat iterasi keyset (memori tetap kecil),
     * urut kunci unik, sudah di-`shape()`.
     *
     * @param array<string, mixed> $filters
     *
     * @return \Generator<array<string, int|string|null>>
     */
    public function rows(string $dimension, array $filters, StaffScope $scope, bool $labeled = false, bool $readableAnswers = false): \Generator
    {
        $key  = self::KEYS[$dimension] ?? throw new \InvalidArgumentException("Dimensi '{$dimension}' tidak dikenal.");
        $last = null;

        do {
            $builder = $this->query($dimension, $filters, $scope);

            if ($last !== null) {
                $builder->where($key . ' >', $last);
            }

            $batch = $builder->orderBy($key, 'ASC')->limit(self::CHUNK)->get()->getResultArray();

            foreach ($batch as $row) {
                yield $this->shape($dimension, $row, $scope, $labeled, $readableAnswers);
            }

            if ($batch !== []) {
                $last = (int) $batch[array_key_last($batch)]['_key'];
            }
        } while (count($batch) === self::CHUNK);
    }

    /** Jumlah baris satu dimensi dalam cakupan $scope. */
    public function count(string $dimension, array $filters, StaffScope $scope): int
    {
        return $this->query($dimension, $filters, $scope)->countAllResults();
    }

    /**
     * Kolom satu dimensi yang boleh dilihat $scope, urutan §9.2.
     *
     * @return list<string>
     */
    public function columns(string $dimension, StaffScope $scope): array
    {
        $columns = array_keys(self::COLUMNS[$dimension] ?? throw new \InvalidArgumentException("Dimensi '{$dimension}' tidak dikenal."));

        if ($scope->anonymous) {
            $columns = array_diff($columns, self::IDENTITY_COLUMNS);
        }

        if (! $scope->isAdmin) {
            $columns = array_diff($columns, self::ADMIN_ONLY_COLUMNS);
        }

        return array_values($columns);
    }

    /** @return list<array{key: string, label: string, type: string}> */
    public function columnSpecs(string $dimension, StaffScope $scope, bool $readableAnswers = false): array
    {
        return array_map(static function (string $column) use ($dimension, $readableAnswers): array {
            $spec = ExportLabels::column($dimension, $column);
            $type = $readableAnswers && in_array($column, self::ANSWER_COLUMNS, true) ? 'text' : $spec['type'];

            return ['key' => $column, 'label' => $spec['label'], 'type' => $type];
        }, $this->columns($dimension, $scope));
    }

    /**
     * Kolom yang dapat diurutkan di tabel admin.
     *
     * @return list<string>
     */
    public function sortable(string $dimension): array
    {
        return array_keys(array_filter(self::COLUMNS[$dimension] ?? [], static fn ($expr): bool => $expr !== null));
    }

    /** Dimensi yang boleh dibaca $scope, urutan resmi. @return list<string> */
    public function dimensions(StaffScope $scope): array
    {
        return $scope->isAdmin ? self::DIMENSIONS : array_values(array_diff(self::DIMENSIONS, self::ADMIN_ONLY_DIMENSIONS));
    }

    public function allows(string $dimension, StaffScope $scope): bool
    {
        return in_array($dimension, $this->dimensions($scope), true);
    }

    /**
     * Filter yang disaring: hanya FILTER_KEYS, tanggal Y-m-d, id bilangan bulat.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, int|string>
     */
    public function sanitizeFilters(array $filters): array
    {
        $out = [];

        foreach (self::FILTER_KEYS as $key) {
            $value = $filters[$key] ?? null;

            if (! is_scalar($value) || trim((string) $value) === '') {
                continue;
            }

            $value = trim((string) $value);

            if (in_array($key, ['date_from', 'date_to'], true) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
                continue;
            }

            if (str_ends_with($key, '_id')) {
                if (! ctype_digit($value)) {
                    continue;
                }

                $value = (int) $value;
            }

            $out[$key] = $value;
        }

        return $out;
    }

    /**
     * Baris query → kolom §9.2 dalam cakupan $scope (kolom yang tidak boleh
     * dilihat tidak ikut). Answer columns are sentences when $labeled or
     * $readableAnswers is set; other values are labelled only when $labeled.
     *
     * @param array<string, mixed> $row
     *
     * @return array<string, int|string|null>
     */
    public function shape(string $dimension, array $row, StaffScope $scope, bool $labeled = false, bool $readableAnswers = false): array
    {
        $answers = $labeled || $readableAnswers;
        $values  = match ($dimension) {
            'peserta'    => $this->participantValues($row),
            'sesi'       => $row,
            'perangkat'  => ['client_hints' => $row['client_hints_json'] ?? null] + $row,
            'progres'    => $this->progressValues($row),
            'pengerjaan' => $this->attemptValues($row),
            'jawaban'    => $this->responseValues($row, $answers),
            'percobaan'  => $this->tryValues($row, $answers),
            'navigasi'   => $this->navigationValues($row),
            'bantuan'    => $this->helpValues($row),
            'selesai'    => $this->eventValues($row),
            'event'      => $row,
            'masukan'    => $row,
            'butir'      => $this->itemValues($row, $answers),
            default      => throw new \InvalidArgumentException("Dimensi '{$dimension}' tidak dikenal."),
        };

        $out = [];

        foreach ($this->columns($dimension, $scope) as $column) {
            $value = $this->normalize($dimension, $column, $values[$column] ?? null);

            if ($labeled) {
                $value = in_array($column, ['level', 'current_level'], true)
                    ? $this->levelName($value)
                    : ExportLabels::label($dimension, $column, $value);
            }

            $out[$column] = $value;
        }

        return $out;
    }

    /** Satu baris CSV (RFC 4180, pemisah koma, akhir baris \n). */
    public static function writeCsvLine($handle, array $values): void
    {
        fputcsv($handle, array_map(static fn (mixed $v): string => $v === null ? '' : (string) $v, $values), ',', '"', '');
    }

    // ------------------------------------------------------------ query

    private function participantQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->db()->table('participants')
            ->select('participants.id AS _key, participants.participant_code, participants.school_id')
            ->select('participants.age, participants.gender, participants.class_level')
            ->select('participants.country_name_snapshot, participants.province_name_snapshot')
            ->select('participants.district_name_snapshot, participants.created_at, participants.assented_at')
            ->select('participants.intro_seen_at, participants.pw_first_submit_criteria, participants.pw_weak_submit_count')
            ->where('participants.deleted_at', null);

        if (! $scope->anonymous) {
            $builder->select('participants.display_name, participants.username, participants.school_name_snapshot')
                ->select('schools.code AS school_npsn')
                ->join('schools', 'schools.id = participants.school_id', 'left');
        }

        $this->applyParticipantFilters($builder, $filters, $scope, 'participants.created_at');

        // Filter milik sesi: siswa yang punya sesi dalam cakupan itu
        $sessionFilters = array_intersect_key($filters, array_flip(['study_id', 'locale']));

        if ($sessionFilters !== []) {
            $builder->whereIn('participants.id', static function (BaseBuilder $sub) use ($sessionFilters): BaseBuilder {
                $sub->select('game_sessions.participant_id')->from('game_sessions');

                foreach ($sessionFilters as $key => $value) {
                    $sub->where('game_sessions.' . $key, $value);
                }

                return $sub;
            });
        }

        return $builder;
    }

    private function sessionQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->sessionBase('game_sessions')
            ->select('game_sessions.id AS _key, game_sessions.session_code, participants.participant_code')
            ->select('research_studies.code AS study_code, game_releases.release_code')
            ->select('game_sessions.app_client_version, game_sessions.package_code, game_sessions.package_mode')
            ->select('game_sessions.locale, game_sessions.status, game_sessions.started_at, game_sessions.last_active_at')
            ->select('game_sessions.ended_at, game_sessions.duration_ms, game_sessions.device_type')
            ->select('game_sessions.os_name, game_sessions.browser_name, game_sessions.screen_size, game_sessions.is_touch')
            ->join('research_studies', 'research_studies.id = game_sessions.study_id', 'left')
            ->join('game_releases', 'game_releases.id = game_sessions.release_id', 'left');

        $this->applySessionFilters($builder, $filters, $scope, 'game_sessions.started_at');

        return $builder;
    }

    private function deviceQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->sessionBase('device_logs', 'device_logs.session_id')
            ->select('device_logs.id AS _key, game_sessions.session_code, participants.participant_code')
            ->select('device_logs.via, device_logs.created_at, device_logs.device_type, device_logs.device_brand')
            ->select('device_logs.device_model, device_logs.os_name, device_logs.os_version, device_logs.os_platform')
            ->select('device_logs.browser_name, device_logs.browser_version, device_logs.browser_engine, device_logs.is_bot')
            ->select('device_logs.screen_w, device_logs.screen_h, device_logs.viewport_w, device_logs.viewport_h')
            ->select('device_logs.pixel_ratio, device_logs.touch_points, device_logs.orientation, device_logs.is_standalone')
            ->select('device_logs.language, device_logs.timezone, device_logs.corrected, device_logs.detector')
            ->select('device_logs.user_agent, device_logs.client_hints_json');

        $this->applySessionFilters($builder, $filters, $scope, 'device_logs.created_at');

        return $builder;
    }

    private function progressQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->sessionBase('session_progress', 'session_progress.session_id')
            ->select('session_progress.session_id AS _key, game_sessions.session_code, participants.participant_code')
            ->select('session_progress.current_level_id, session_progress.current_node_id')
            ->select('session_progress.unlocked_level_sequence, session_progress.completed_nodes')
            ->select('session_progress.completed_levels, session_progress.total_stars, session_progress.updated_at');

        $this->applySessionFilters($builder, $filters, $scope, 'game_sessions.started_at');

        return $builder;
    }

    private function attemptQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->attemptBase('challenge_attempts')
            ->select('challenge_attempts.id AS _key, challenge_attempts.id AS attempt_id')
            ->select('game_sessions.session_code, participants.participant_code')
            ->select('levels.code AS level_code, challenge_attempts.challenge_node_id')
            ->select('challenge_nodes.sequence AS node_sequence, challenge_nodes.engine_type')
            ->select('challenge_attempts.variant_group, challenge_attempts.attempt_no, challenge_attempts.status')
            ->select('challenge_attempts.completion_reason, challenge_attempts.scorable_items')
            ->select('challenge_attempts.first_pass_correct, challenge_attempts.final_correct')
            ->select('challenge_attempts.check_count, challenge_attempts.max_tries, challenge_attempts.hint_count')
            ->select('challenge_attempts.answer_change_count, challenge_attempts.audio_use_count, challenge_attempts.stars')
            ->select('challenge_attempts.started_at, challenge_attempts.completed_at, challenge_attempts.duration_ms');

        $this->applyAttemptFilters($builder, $filters, $scope, 'challenge_attempts.started_at');

        return $builder;
    }

    private function responseQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->attemptBase('item_responses', 'item_responses.challenge_attempt_id')
            ->join('challenge_items', 'challenge_items.id = item_responses.challenge_item_id')
            ->select('item_responses.id AS _key, challenge_attempts.id AS attempt_id')
            ->select('game_sessions.session_code, participants.participant_code')
            ->select('levels.code AS level_code, challenge_nodes.sequence AS node_sequence')
            ->select('challenge_attempts.variant_group, item_responses.challenge_item_id')
            ->select('challenge_items.item_key, challenge_items.interaction_type, challenge_items.bloom_level')
            ->select('item_responses.display_order, item_responses.status, item_responses.try_count')
            ->select('item_responses.first_answer_json, item_responses.final_answer_json')
            ->select('item_responses.first_pass_correct, item_responses.is_correct, item_responses.solved_on_try')
            ->select('item_responses.change_count, item_responses.hint_used, item_responses.wrong_click_count')
            ->select('item_responses.started_at, item_responses.answered_at, item_responses.duration_ms');

        $this->applyAttemptFilters($builder, $filters, $scope, 'challenge_attempts.started_at');

        return $builder;
    }

    private function tryQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->attemptBase('item_tries', 'item_tries.challenge_attempt_id')
            ->join('challenge_items', 'challenge_items.id = item_tries.challenge_item_id')
            ->select('item_tries.id AS _key, challenge_attempts.id AS attempt_id')
            ->select('game_sessions.session_code, participants.participant_code')
            ->select('levels.code AS level_code, challenge_nodes.sequence AS node_sequence')
            ->select('challenge_attempts.variant_group, item_tries.challenge_item_id, challenge_items.item_key')
            ->select('item_tries.try_no, item_tries.check_no, item_tries.answer_json, item_tries.is_correct')
            ->select('item_tries.hint_opened_before, item_tries.first_input_ms, item_tries.last_input_ms')
            ->select('item_tries.occurred_at');

        $this->applyAttemptFilters($builder, $filters, $scope, 'item_tries.occurred_at');

        return $builder;
    }

    /** @param list<string>|null $types null = semua event (Event Mentah) */
    private function eventQuery(array $filters, StaffScope $scope, ?array $types): BaseBuilder
    {
        $builder = $this->sessionBase('game_event_logs', 'game_event_logs.session_id')
            ->select('game_event_logs.id AS _key, game_event_logs.id, game_event_logs.event_uuid')
            ->select('game_event_logs.client_event_id, game_event_logs.session_id, game_sessions.session_code')
            ->select('game_event_logs.participant_id, participants.participant_code, game_event_logs.level_id')
            ->select('game_event_logs.challenge_node_id, game_event_logs.challenge_attempt_id')
            ->select('game_event_logs.challenge_item_id, game_event_logs.event_type, game_event_logs.sequence_no')
            ->select('game_event_logs.occurred_at, game_event_logs.server_received_at, game_event_logs.clock_offset_ms')
            ->select('game_event_logs.payload_json')
            ->where('game_event_logs.deleted_at', null);

        if ($types !== null) {
            $builder->whereIn('game_event_logs.event_type', $types);
        }

        $this->applySessionFilters($builder, $filters, $scope, 'game_event_logs.occurred_at');
        $this->applyContentFilters($builder, $filters, 'game_event_logs.level_id', 'game_event_logs.challenge_node_id', null);

        return $builder;
    }

    /**
     * Petunjuk yang dibuka (event `hint_opened`) dan aksi pemutar suara
     * (`audio_usage_events`) dalam satu tabel: UNION dua query yang
     * masing-masing sudah terfilter, dibungkus sebagai `bantuan`.
     * `_key` = id × 2 (petunjuk) atau id × 2 + 1 (audio), unik dan stabil.
     */
    private function helpQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $db = $this->db();

        $hints = $this->sessionBase('game_event_logs', 'game_event_logs.session_id')
            ->select($db->protectIdentifiers('game_event_logs', true) . '.id * 2 AS _key', false)
            ->select('game_sessions.session_code, participants.participant_code, game_event_logs.occurred_at')
            ->select("'petunjuk' AS jenis", false)
            ->select('game_event_logs.level_id, game_event_logs.challenge_node_id AS node_id')
            ->select('game_event_logs.challenge_attempt_id AS attempt_id, game_event_logs.challenge_item_id AS item_id')
            ->select('game_event_logs.payload_json')
            ->select('NULL AS asset_key, NULL AS audio_context, NULL AS audio_locale, NULL AS action', false)
            ->select('NULL AS play_index, NULL AS listened_ms, NULL AS completed', false)
            ->where('game_event_logs.event_type', 'hint_opened')
            ->where('game_event_logs.deleted_at', null);

        $this->applySessionFilters($hints, $filters, $scope, 'game_event_logs.occurred_at');
        $this->applyContentFilters($hints, $filters, 'game_event_logs.level_id', 'game_event_logs.challenge_node_id', null);

        $audio = $this->sessionBase('audio_usage_events', 'audio_usage_events.session_id')
            ->join('challenge_attempts', 'challenge_attempts.id = audio_usage_events.challenge_attempt_id', 'left')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id', 'left')
            ->join('audio_assets', 'audio_assets.id = audio_usage_events.audio_asset_id', 'left')
            ->join('media_assets', 'media_assets.id = audio_assets.media_asset_id', 'left')
            ->select($db->protectIdentifiers('audio_usage_events', true) . '.id * 2 + 1 AS _key', false)
            ->select('game_sessions.session_code, participants.participant_code, audio_usage_events.occurred_at')
            ->select("'audio' AS jenis", false)
            ->select('challenge_nodes.level_id, challenge_attempts.challenge_node_id AS node_id')
            ->select('audio_usage_events.challenge_attempt_id AS attempt_id')
            ->select('NULL AS item_id, NULL AS payload_json', false)
            ->select('media_assets.asset_key, audio_assets.context_code AS audio_context, audio_assets.locale AS audio_locale')
            ->select('audio_usage_events.action, audio_usage_events.play_index')
            ->select('audio_usage_events.listened_ms, audio_usage_events.completed');

        $this->applySessionFilters($audio, $filters, $scope, 'audio_usage_events.occurred_at');
        $this->applyContentFilters($audio, $filters, 'challenge_nodes.level_id', 'challenge_attempts.challenge_node_id', null);

        return $db->newQuery()->fromSubquery($hints->unionAll($audio), 'bantuan')->select('bantuan.*', false);
    }

    private function feedbackQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->db()->table('participant_feedback')
            ->join('participants', 'participants.id = participant_feedback.participant_id')
            ->join('game_sessions', 'game_sessions.id = participant_feedback.session_id', 'left')
            ->select('participant_feedback.id AS _key, participants.participant_code, game_sessions.session_code')
            ->select('participant_feedback.rating, participant_feedback.liked_most, participant_feedback.hardest_part')
            ->select('participant_feedback.new_learning, participant_feedback.suggestion, participant_feedback.submitted_at')
            ->where('participants.deleted_at', null);

        $this->applySessionFilters($builder, $filters, $scope, 'participant_feedback.submitted_at');

        return $builder;
    }

    /** Bank soal: konten, bukan data siswa — tanpa cakupan sekolah. */
    private function itemQuery(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('challenge_items')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_items.challenge_node_id')
            ->join('levels', 'levels.id = challenge_nodes.level_id')
            ->join('reading_passages', 'reading_passages.id = challenge_items.passage_id', 'left')
            ->join('media_assets', 'media_assets.id = challenge_items.media_asset_id', 'left')
            ->select('challenge_items.id AS _key, challenge_items.id AS challenge_item_id, challenge_items.item_key')
            ->select('levels.code AS level_code, challenge_nodes.sequence AS node_sequence')
            ->select('challenge_items.challenge_node_id, challenge_items.variant_group, challenge_items.sequence')
            ->select('challenge_items.interaction_type, challenge_items.bloom_level, reading_passages.passage_key')
            ->select('challenge_items.scorable, challenge_items.is_active, media_assets.asset_key AS media_asset_key');

        $this->applyContentFilters($builder, $filters, 'challenge_nodes.level_id', 'challenge_items.challenge_node_id', 'challenge_items.variant_group');

        return $builder;
    }

    // ----------------------------------------------------------- dasar query

    /** $table yang menunjuk sesi (atau tabel sesi itu sendiri) + sesi + peserta. */
    private function sessionBase(string $table, ?string $sessionColumn = null): BaseBuilder
    {
        $builder = $this->db()->table($table);

        if ($sessionColumn !== null) {
            $builder->join('game_sessions', 'game_sessions.id = ' . $sessionColumn);
        }

        return $builder->join('participants', 'participants.id = game_sessions.participant_id')
            ->where('participants.deleted_at', null);
    }

    /** $table yang menunjuk pengerjaan (atau tabelnya sendiri) + sesi + peserta + tantangan + wilayah. */
    private function attemptBase(string $table, ?string $attemptColumn = null): BaseBuilder
    {
        $builder = $this->db()->table($table);

        if ($attemptColumn !== null) {
            $builder->join('challenge_attempts', 'challenge_attempts.id = ' . $attemptColumn);
        }

        return $builder->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->join('levels', 'levels.id = challenge_nodes.level_id')
            ->where('participants.deleted_at', null);
    }

    /** Cakupan sekolah + filter milik siswa + rentang tanggal pada $dateColumn. */
    private function applyParticipantFilters(BaseBuilder $builder, array $filters, StaffScope $scope, string $dateColumn): void
    {
        if (! $scope->isAdmin) {
            $builder->where('participants.school_id', (int) $scope->schoolId);
        }

        $map = [
            'participant_id'   => 'participants.id',
            'participant_code' => 'participants.participant_code',
            'school_id'        => 'participants.school_id',
            'class_level'      => 'participants.class_level',
            'province_code'    => 'participants.province_code',
        ];

        foreach ($map as $key => $column) {
            if (isset($filters[$key])) {
                $builder->where($column, $filters[$key]);
            }
        }

        if (isset($filters['date_from'])) {
            $builder->where($dateColumn . ' >=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $builder->where($dateColumn . ' <=', $filters['date_to'] . ' 23:59:59.999999');
        }
    }

    private function applySessionFilters(BaseBuilder $builder, array $filters, StaffScope $scope, string $dateColumn): void
    {
        $this->applyParticipantFilters($builder, $filters, $scope, $dateColumn);

        foreach (['study_id' => 'game_sessions.study_id', 'locale' => 'game_sessions.locale'] as $key => $column) {
            if (isset($filters[$key])) {
                $builder->where($column, $filters[$key]);
            }
        }
    }

    private function applyAttemptFilters(BaseBuilder $builder, array $filters, StaffScope $scope, string $dateColumn): void
    {
        $this->applySessionFilters($builder, $filters, $scope, $dateColumn);
        $this->applyContentFilters($builder, $filters, 'challenge_nodes.level_id', 'challenge_attempts.challenge_node_id', 'challenge_attempts.variant_group');
    }

    private function applyContentFilters(BaseBuilder $builder, array $filters, string $levelColumn, string $nodeColumn, ?string $variantColumn): void
    {
        if (isset($filters['level_id'])) {
            $builder->where($levelColumn, $filters['level_id']);
        }

        if (isset($filters['node_id'])) {
            $builder->where($nodeColumn, $filters['node_id']);
        }

        if ($variantColumn !== null && isset($filters['variant_group'])) {
            $builder->where($variantColumn, $filters['variant_group']);
        }
    }

    // ------------------------------------------------------------ urutan

    /** "kolom" / "-kolom" yang sah untuk dimensi ini, selain itu urutan bawaan. */
    private function resolveSort(string $dimension, ?string $sort): string
    {
        $sort   = trim((string) $sort);
        $column = ltrim($sort, '-');

        return $column !== '' && in_array($column, $this->sortable($dimension), true)
            ? $sort
            : self::DEFAULT_SORT[$dimension];
    }

    private function applySort(BaseBuilder $builder, string $dimension, string $sort): void
    {
        $direction = str_starts_with($sort, '-') ? 'DESC' : 'ASC';
        $column    = ltrim($sort, '-');

        foreach ((array) self::COLUMNS[$dimension][$column] as $expression) {
            $builder->orderBy($expression, $direction);
        }

        // Pemecah seri: urutan stabil antarhalaman
        $builder->orderBy(self::KEYS[$dimension], $direction);
    }

    // ------------------------------------------------------------ nilai

    private function participantValues(array $row): array
    {
        return [
            'participant_code'         => $row['participant_code'],
            'display_name'             => $row['display_name'] ?? null,
            'username'                 => $row['username'] ?? null,
            'school_name'              => $row['school_name_snapshot'] ?? null,
            'school_npsn'              => $row['school_npsn'] ?? null,
            'school_ref'               => self::schoolRef($row['school_id']),
            'age'                      => $row['age'],
            'gender'                   => $row['gender'],
            'class_level'              => $row['class_level'],
            'country'                  => $row['country_name_snapshot'],
            'province'                 => $row['province_name_snapshot'],
            'district'                 => $row['district_name_snapshot'],
            'registered_at'            => $row['created_at'],
            'assented_at'              => $row['assented_at'],
            'intro_seen_at'            => $row['intro_seen_at'],
            'pw_first_submit_criteria' => $row['pw_first_submit_criteria'],
            'pw_weak_submit_count'     => $row['pw_weak_submit_count'],
        ];
    }

    private function progressValues(array $row): array
    {
        $levels = $this->levelMap();

        return $row + [
            'current_level'    => $levels[(int) $row['current_level_id']]['code'] ?? null,
            'current_node_ref' => $this->nodeRefById($row['current_node_id']),
            'total_nodes'      => $this->activeNodeCount(),
        ];
    }

    private function attemptValues(array $row): array
    {
        $node = $this->nodeMap()[(int) $row['challenge_node_id']] ?? null;

        return $row + [
            'level'         => $row['level_code'],
            'node_ref'      => node_ref((string) $row['level_code'], (int) $row['node_sequence']),
            'variant_title' => $node?->variantTitle($row['variant_group'], 'id'),
        ];
    }

    private function responseValues(array $row, bool $labeled): array
    {
        $item = $this->itemMap()[(int) $row['challenge_item_id']] ?? null;

        return $row + [
            'level'        => $row['level_code'],
            'node_ref'     => node_ref((string) $row['level_code'], (int) $row['node_sequence']),
            'skill'        => $item?->conf('skill'),
            'indicator'    => $this->indicatorCode($item),
            'prompt'       => $item?->prompt_id,
            'first_answer' => $this->answer($item, $row['first_answer_json'], $labeled),
            'final_answer' => $this->answer($item, $row['final_answer_json'], $labeled),
            'answer_key'   => $this->answerKey($item, $labeled),
        ];
    }

    private function tryValues(array $row, bool $labeled): array
    {
        $item = $this->itemMap()[(int) $row['challenge_item_id']] ?? null;

        return $row + [
            'node_ref' => node_ref((string) $row['level_code'], (int) $row['node_sequence']),
            'answer'   => $this->answer($item, $row['answer_json'], $labeled),
        ];
    }

    private function navigationValues(array $row): array
    {
        $payload = $this->decode($row['payload_json']);

        return $this->eventValues($row) + [
            'screen'      => is_scalar($payload['screen'] ?? null) ? (string) $payload['screen'] : null,
            'from_screen' => $row['event_type'] === 'screen_viewed' && is_scalar($payload['from'] ?? null) ? (string) $payload['from'] : null,
        ];
    }

    private function eventValues(array $row): array
    {
        $levels = $this->levelMap();

        return [
            'level'      => $levels[(int) $row['level_id']]['code'] ?? null,
            'node_ref'   => $this->nodeRefById($row['challenge_node_id']),
            'attempt_id' => $row['challenge_attempt_id'],
            'payload'    => $row['payload_json'],
        ] + $row;
    }

    private function helpValues(array $row): array
    {
        $levels  = $this->levelMap();
        $payload = $this->decode($row['payload_json']);
        $item    = $this->itemMap()[(int) $row['item_id']] ?? null;

        return [
            'level'    => $levels[(int) $row['level_id']]['code'] ?? null,
            'node_ref' => $this->nodeRefById($row['node_id']),
            'item_key' => $item?->item_key,
            'hint_id'  => is_numeric($payload['hint_id'] ?? null) ? (int) $payload['hint_id'] : null,
        ] + $row;
    }

    private function itemValues(array $row, bool $labeled): array
    {
        $item = $this->itemMap()[(int) $row['challenge_item_id']] ?? null;
        $node = $this->nodeMap()[(int) $row['challenge_node_id']] ?? null;

        return $row + [
            'level'         => $row['level_code'],
            'node_ref'      => node_ref((string) $row['level_code'], (int) $row['node_sequence']),
            'variant_title' => $node?->variantTitle($row['variant_group'], 'id'),
            'skill'         => $item?->conf('skill'),
            'indicator'     => $this->indicatorCode($item),
            'prompt'        => $item?->prompt_id,
            'answer_key'    => $this->answerKey($item, $labeled),
            'decoy'         => $item === null || $item->interaction_type !== 'find_object' ? null : ($item->isDecoy() ? 1 : 0),
        ];
    }

    /** Jawaban: JSON tersimpan apa adanya (berkode) atau kalimat (berlabel). */
    private function answer(?ChallengeItem $item, mixed $json, bool $labeled): ?string
    {
        if ($json === null || $json === '') {
            return null;
        }

        if (! $labeled) {
            return (string) $json;
        }

        return $item === null ? AnswerText::fromData($json) : AnswerText::response($item, $json);
    }

    private function answerKey(?ChallengeItem $item, bool $labeled): ?string
    {
        if ($item === null) {
            return null;
        }

        if ($labeled) {
            return AnswerText::key($item);
        }

        $raw = $item->toRawArray()['answer_key_json'] ?? null;

        return $raw === null ? null : (string) $raw;
    }

    /** Nilai siap tulis menurut jenis kolom (ExportLabels). */
    private function normalize(string $dimension, string $column, mixed $value): int|string|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match (ExportLabels::column($dimension, $column)['type']) {
            'int', 'ms', 'bool' => (int) $value,
            'datetime'          => self::isoTime($value),
            default             => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        };
    }

    /** Waktu database (waktu setempat) → ISO 8601 dengan milidetik dan zona. */
    public static function isoTime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $time = $value instanceof \DateTimeInterface
                ? \DateTimeImmutable::createFromInterface($value)
                : new \DateTimeImmutable((string) $value, new \DateTimeZone(config('App')->appTimezone));
        } catch (\Exception) {
            return (string) $value;
        }

        return $time->setTimezone(new \DateTimeZone(config('App')->appTimezone))->format('Y-m-d\TH:i:s.vP');
    }

    /** Kode samaran sekolah: stabil, tanpa nama. */
    public static function schoolRef(mixed $schoolId): ?string
    {
        return $schoolId === null || (int) $schoolId <= 0 ? null : sprintf('SCH-%06d', (int) $schoolId);
    }

    // ---------------------------------------------------------------- peta

    /** @return array<int, array{code: string, name: string, sequence: int}> */
    private function levelMap(): array
    {
        if (! isset($this->maps['levels'])) {
            $this->maps['levels'] = [];

            foreach ($this->db()->table('levels')->select('id, code, name_id, sequence')->get()->getResultArray() as $row) {
                $this->maps['levels'][(int) $row['id']] = [
                    'code'     => (string) $row['code'],
                    'name'     => (string) ($row['name_id'] ?: $row['code']),
                    'sequence' => (int) $row['sequence'],
                ];
            }
        }

        return $this->maps['levels'];
    }

    /** Kode wilayah → nama (mode berlabel). */
    private function levelName(mixed $code): mixed
    {
        foreach ($this->levelMap() as $level) {
            if ($level['code'] === $code) {
                return $level['name'];
            }
        }

        return $code;
    }

    /** @return array<int, ChallengeNode> */
    private function nodeMap(): array
    {
        return $this->maps['nodes'] ??= array_column(
            array_map(static fn (ChallengeNode $n): array => ['id' => $n->id, 'node' => $n], model(ChallengeNodeModel::class)->findAll()),
            'node',
            'id',
        );
    }

    private function nodeRefById(mixed $nodeId): ?string
    {
        if ($nodeId === null || $nodeId === '') {
            return null;
        }

        $node  = $this->nodeMap()[(int) $nodeId] ?? null;
        $level = $node === null ? null : ($this->levelMap()[$node->level_id] ?? null);

        return $node === null || $level === null ? null : node_ref($level['code'], $node->sequence);
    }

    private function activeNodeCount(): int
    {
        return $this->maps['active_nodes'] ??= count(array_filter($this->nodeMap(), static fn (ChallengeNode $n): bool => (bool) $n->is_active));
    }

    /**
     * Semua soal, termasuk yang diistirahatkan. Bank soal hanya ratusan
     * baris, jadi dimuat sekali per objek.
     *
     * @return array<int, ChallengeItem>
     */
    private function itemMap(): array
    {
        if (! isset($this->maps['items'])) {
            $this->maps['items'] = [];

            foreach (model(ChallengeItemModel::class)->findAll() as $item) {
                $this->maps['items'][(int) $item->id] = $item;
            }
        }

        return $this->maps['items'];
    }

    private function indicatorCode(?ChallengeItem $item): ?string
    {
        if ($item === null) {
            return null;
        }

        $this->maps['indicators'] ??= array_column(
            $this->db()->table('learning_indicators')->select('id, code')->get()->getResultArray(),
            'code',
            'id',
        );

        $indicatorId = $item->indicator_id ?? (($this->nodeMap()[$item->challenge_node_id] ?? null)?->indicator_id);

        return $indicatorId === null ? null : ($this->maps['indicators'][$indicatorId] ?? null);
    }

    /** @return array<string, mixed> */
    private function decode(mixed $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $data = json_decode((string) $json, true);

        return is_array($data) ? $data : [];
    }

    // ------------------------------------------------------------- cakupan

    private function assertAllowed(string $dimension, StaffScope $scope): void
    {
        if (! in_array($dimension, self::DIMENSIONS, true)) {
            throw new \InvalidArgumentException("Dimensi '{$dimension}' tidak dikenal.");
        }

        if (! $this->allows($dimension, $scope)) {
            throw new \DomainException('Data ini hanya untuk admin.');
        }
    }

    private function boundScope(): StaffScope
    {
        return $this->scope ?? throw new \LogicException('RawDataService belum diikat ke cakupan staf (withScope()).');
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
