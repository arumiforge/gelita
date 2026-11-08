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
 * - anonymous (`peneliti`, `ketua`): identity columns are never read, and
 *   the validators' test accounts (`uji.*`, school V01) and simulated
 *   students (`sim_*`) never appear, except in a one-student view (29 §7.1);
 * - full detail (`ketua`, 29 K1): the `event` and `kode_pilihan` sheets and
 *   the `answer_key` column; without it neither.
 * There is no school scope since revision 10 part 1 (27 §5.5).
 *
 * Revision 10, part 3 (29 §7): event times of the research sheets are
 * corrected (`occurred_at_corrected` = occurred_at + clock_offset_ms, with
 * `offset_known`), internal ids become codes, the admin-only device and
 * package columns moved to TechLogService (`/admin/log-teknis`), and the
 * `bantuan` sheet has one row per playback.
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
    /**
     * Dimension codes = download sheet codes, official order (29 §7.2,
     * §19 no. 13), without `kamus`. Field sheets l1_… – l6_… come from
     * FieldSheetSpec::imported(). Later parts add their sheets here in the
     * order of 29 §7.2: `turunan_*` (35), `tes_jawaban`, `tes_bagian`,
     * `angket`, `sikap`, `risiko_gabungan` (34) after `kode_pilihan`.
     */
    public const DIMENSIONS = [
        'peserta', 'sesi_lapangan', 'sesi', 'progres', 'pengerjaan', 'jawaban', 'percobaan',
        'navigasi', 'bantuan', 'selesai', 'event', 'masukan', 'butir', 'kode_pilihan',
        'izin',   // part 4 (30 keputusan no. 9, §10.1): coded consent per student, ketua only
        'l1_kondisi', 'l1_penyimpangan', 'l2_kursi', 'l3_bantuan', 'l4_bromp', 'l5_wawancara', 'l6_sesi',
    ];

    /** Dimensions only for `ketua` (full detail, 29 K1): raw events and the bank codes per choice. */
    public const FULL_DETAIL_DIMENSIONS = ['event', 'kode_pilihan', 'izin'];

    /** Answer columns that `$readableAnswers` writes as sentences (AnswerText). */
    public const ANSWER_COLUMNS = ['first_answer', 'final_answer', 'answer', 'answer_key'];

    /** Kolom yang hanya ada pada data beridentitas (admin, tidak anonim). */
    public const IDENTITY_COLUMNS = ['display_name', 'username', 'school_name', 'school_npsn'];

    /** Columns only for `ketua` (29 K1). */
    public const FULL_DETAIL_COLUMNS = ['answer_key'];

    /** Kunci filter yang dikenali; kunci lain diabaikan. `province_code` is admin data (29 §7.1). */
    public const FILTER_KEYS = [
        'study_id', 'participant_id', 'participant_code', 'school_id', 'class_level',
        'locale', 'level_id', 'node_id', 'variant_group', 'date_from', 'date_to',
        'field_session_code', 'meeting_no',   // part 3 (29 §7.1)
    ];

    public const PER_PAGE = [25, 50, 100];

    /** Event lists of the `navigasi` and `selesai` sheets, owned by the event catalog (29 §4). */
    public const NAVIGATION_EVENTS = \App\Libraries\EventCatalog::NAVIGATION;

    public const COMPLETION_EVENTS = \App\Libraries\EventCatalog::COMPLETION;

    /** Ukuran potongan iterasi keyset (`rows()`, `streamCsv()`). */
    public const CHUNK = 2000;

    /**
     * Kolom per dimensi, urut resmi §9.2: kolom → ekspresi SQL untuk
     * pengurutan, atau null bila nilainya dihitung di PHP (tidak dapat
     * diurutkan). Field sheets are built from FieldSheetSpec (columnMap()).
     * `occurred_at_corrected` sorts by the raw time (the offset is small).
     *
     * @var array<string, array<string, list<string>|string|null>>
     */
    private const COLUMNS = [
        'peserta' => [
            'participant_code' => 'participants.participant_code', 'display_name' => 'participants.display_name',
            'username' => 'participants.username', 'school_name' => 'participants.school_name_snapshot',
            'school_npsn' => null, 'school_code' => 'schools.research_code', 'rombel' => 'participants.rombel',
            'booklet' => null, 'age' => 'participants.age',
            'gender' => 'participants.gender', 'class_level' => 'participants.class_level',
            'registered_at' => 'participants.created_at',
            'assented_at' => 'participants.assented_at', 'intro_seen_at' => 'participants.intro_seen_at',
            'pw_first_submit_criteria' => 'participants.pw_first_submit_criteria',
            'pw_weak_submit_count' => 'participants.pw_weak_submit_count',
            // Part 8 (34 §11.4) adds pre_form … post_capped here.
        ],
        'sesi_lapangan' => [
            'field_session_code' => 'field_sessions.code', 'school_code' => 'schools.research_code',
            'rombel' => 'field_sessions.rombel', 'meeting_no' => 'field_sessions.meeting_no',
            'makeup_no' => 'field_sessions.makeup_no', 'main_session_code' => null,
            'region_opened' => null, 'session_date' => 'field_sessions.session_date',
            'opens_at' => 'field_sessions.opens_at', 'closes_at' => 'field_sessions.closes_at',
            'closed_at' => 'field_sessions.closed_at',
        ],
        'sesi' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'study_code' => 'research_studies.code', 'field_session_code' => 'field_sessions.code',
            'meeting_no' => 'game_sessions.meeting_no', 'release_code' => 'game_releases.release_code',
            'locale' => 'game_sessions.locale', 'status' => 'game_sessions.status',
            'started_at' => 'game_sessions.started_at', 'last_active_at' => 'game_sessions.last_active_at',
            'ended_at' => 'game_sessions.ended_at', 'duration_ms' => 'game_sessions.duration_ms',
            'device_type' => 'game_sessions.device_type', 'screen_size' => 'game_sessions.screen_size',
        ],
        'progres' => [
            'study_code' => 'research_studies.code', 'participant_code' => 'participants.participant_code',
            'current_level' => 'participant_progress.current_level_id', 'current_node_ref' => 'participant_progress.current_node_id',
            'unlocked_level_sequence' => 'participant_progress.unlocked_level_sequence',
            'completed_nodes' => 'participant_progress.completed_nodes', 'total_nodes' => null,
            'completed_levels' => 'participant_progress.completed_levels', 'total_stars' => 'participant_progress.total_stars',
            'updated_at' => 'participant_progress.updated_at',
        ],
        'pengerjaan' => [
            'attempt_id' => 'challenge_attempts.id', 'session_code' => 'game_sessions.session_code',
            'participant_code' => 'participants.participant_code', 'field_session_code' => 'field_sessions.code',
            'meeting_no' => 'game_sessions.meeting_no', 'level' => 'levels.sequence',
            'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'node_sequence' => 'challenge_nodes.sequence',
            'engine_type' => 'challenge_nodes.engine_type', 'variant_group' => 'challenge_attempts.variant_group',
            'variant_title' => null, 'variant_source' => 'challenge_attempts.variant_source',
            'booklet_at_open' => 'challenge_attempts.booklet_at_open',
            'attempt_no' => 'challenge_attempts.attempt_no', 'status' => 'challenge_attempts.status',
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
            'participant_code' => 'participants.participant_code', 'field_session_code' => 'field_sessions.code',
            'level' => 'levels.sequence',
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
            'participant_code' => 'participants.participant_code', 'field_session_code' => 'field_sessions.code',
            'node_ref' => ['levels.sequence', 'challenge_nodes.sequence'], 'variant_group' => 'challenge_attempts.variant_group',
            'item_key' => 'challenge_items.item_key', 'try_no' => 'item_tries.try_no', 'check_no' => 'item_tries.check_no',
            'answer' => null, 'is_correct' => 'item_tries.is_correct', 'error_code' => 'item_tries.error_code',
            'manual_code' => 'item_tries.manual_code', 'hint_opened_before' => 'item_tries.hint_opened_before',
            'ready_ms' => 'item_tries.ready_ms',
            'first_input_ms' => 'item_tries.first_input_ms', 'last_input_ms' => 'item_tries.last_input_ms',
            'occurred_at' => 'item_tries.occurred_at',
            // Part 6 (32 §6.4, §10): one Skenario Digital try; empty for the other engines
            'action_key' => 'item_tries.action_key', 'action_score' => 'item_tries.action_score',
            'reason_code' => 'item_tries.reason_code', 'reason_ready_ms' => 'item_tries.reason_ready_ms',
            'action_last_input_ms' => 'item_tries.action_last_input_ms', 'reason_last_input_ms' => 'item_tries.reason_last_input_ms',
        ],
        'navigasi' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'field_session_code' => 'field_sessions.code',
            'sequence_no' => 'game_event_logs.sequence_no', 'occurred_at_corrected' => 'game_event_logs.occurred_at',
            'offset_known' => null, 'event_type' => 'game_event_logs.event_type',
            'screen' => null, 'from_screen' => null, 'level' => 'game_event_logs.level_id',
            'node_ref' => 'game_event_logs.challenge_node_id', 'attempt_id' => 'game_event_logs.challenge_attempt_id',
            'payload' => null,
        ],
        'bantuan' => [
            'session_code' => 'bantuan.session_code', 'participant_code' => 'bantuan.participant_code',
            'field_session_code' => 'bantuan.field_session_code',
            'occurred_at_corrected' => 'bantuan.occurred_at', 'jenis' => 'bantuan.jenis', 'level' => 'bantuan.level_id',
            'node_ref' => 'bantuan.node_id', 'attempt_id' => 'bantuan.attempt_id', 'item_key' => null, 'hint_id' => null,
            'asset_key' => 'bantuan.asset_key', 'audio_context' => 'bantuan.audio_context',
            'audio_locale' => 'bantuan.audio_locale', 'action' => 'bantuan.action', 'play_index' => 'bantuan.play_index',
            'listened_ms' => 'bantuan.listened_ms', 'completed' => 'bantuan.completed',
        ],
        'selesai' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'field_session_code' => 'field_sessions.code',
            'occurred_at_corrected' => 'game_event_logs.occurred_at', 'offset_known' => null,
            'event_type' => 'game_event_logs.event_type',
            'level' => 'game_event_logs.level_id', 'node_ref' => 'game_event_logs.challenge_node_id',
            'attempt_id' => 'game_event_logs.challenge_attempt_id', 'payload' => null,
        ],
        'event' => [
            'session_code' => 'game_sessions.session_code', 'participant_code' => 'participants.participant_code',
            'field_session_code' => 'field_sessions.code', 'level' => 'game_event_logs.level_id',
            'node_ref' => 'game_event_logs.challenge_node_id', 'attempt_id' => 'game_event_logs.challenge_attempt_id',
            'item_key' => null, 'event_type' => 'game_event_logs.event_type',
            'sequence_no' => 'game_event_logs.sequence_no', 'occurred_at_corrected' => 'game_event_logs.occurred_at',
            'offset_known' => null, 'payload' => null,
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
            // Research revision 10, part 5 (31 §13.3; names of 11 → Variabel butir)
            'round' => 'challenge_items.round', 'variant' => 'challenge_items.variant_group', 'booklet_shared' => null,
            'dimension' => 'challenge_items.dimension', 'subaspect' => 'challenge_items.subaspect',
            'akm_level' => 'challenge_items.akm_level', 'akm_level_source' => 'challenge_items.akm_level_source',
            'pirls_process' => 'challenge_items.pirls_process', 'bloom_level_source' => 'challenge_items.bloom_level_source',
            'text_genre' => 'reading_passages.text_genre', 'testlet_key' => 'challenge_items.testlet_key',
            'd2_topic' => 'challenge_items.d2_topic', 'd3_clue_type' => 'challenge_items.d3_clue_type',
            'gap_count' => null, 'distractor_word_count' => null, 'passage_words' => null, 'decoy_count' => null,
            'option_count' => null, 'reason_option_count' => null, 'puzzle_grid' => null, 'tap_min_dp' => null,
            'review_status' => 'challenge_items.review_status', 'reference_source' => 'challenge_items.reference_source',
        ],
        // Bank code per choice (31 §13.3), ketua only like the key
        'kode_pilihan' => [
            'item_key' => 'kode_pilihan.item_key', 'node_ref' => null, 'variant' => 'kode_pilihan.variant_group',
            'choice_type' => 'kode_pilihan.choice_type', 'choice_ref' => 'kode_pilihan.choice_ref',
            'error_code' => 'kode_pilihan.error_code', 'is_main' => 'kode_pilihan.is_main', 'option_score' => 'kode_pilihan.option_score',
        ],
        // Coded consent per student (30 §10.1, ConsentService::REGISTER_COLUMNS), for the ketua's check against L7
        'izin' => [
            'participant_code' => 'participants.participant_code', 'parent_consent_on' => 'participant_consents.parent_consent_on',
            'recording_consent' => 'participant_consents.recording_consent', 'recording_withdrawn_on' => 'participant_consents.recording_withdrawn_on',
            'risk_items_consent' => 'participant_consents.risk_items_consent', 'risk_items_withdrawn_on' => 'participant_consents.risk_items_withdrawn_on',
            'assented_at' => 'participants.assented_at', 'assent_version' => 'participants.assent_version',
            'status' => 'participant_consents.status', 'status_on' => 'participant_consents.status_on',
        ],
    ];

    /** Kunci unik tiap dimensi untuk iterasi keyset dan pemecah seri urutan. */
    private const KEYS = [
        'peserta'       => 'participants.id',
        'sesi_lapangan' => 'field_sessions.id',
        'sesi'          => 'game_sessions.id',
        'progres'       => 'participant_progress.id',
        'pengerjaan'    => 'challenge_attempts.id',
        'jawaban'       => 'item_responses.id',
        'percobaan'     => 'item_tries.id',
        'navigasi'      => 'game_event_logs.id',
        'bantuan'       => 'bantuan._key',
        'selesai'       => 'game_event_logs.id',
        'event'         => 'game_event_logs.id',
        'masukan'       => 'participant_feedback.id',
        'butir'         => 'challenge_items.id',
        'kode_pilihan'  => 'kode_pilihan._key',
        'izin'          => 'participant_consents.participant_id',
    ];

    /** Urutan bawaan tabel admin (terbaru dulu untuk data kejadian). */
    private const DEFAULT_SORT = [
        'peserta'       => 'participant_code',
        'sesi_lapangan' => 'field_session_code',
        'sesi'          => '-started_at',
        'progres'       => '-updated_at',
        'pengerjaan'    => '-started_at',
        'jawaban'       => '-started_at',
        'percobaan'     => '-occurred_at',
        'navigasi'      => '-occurred_at_corrected',
        'bantuan'       => '-occurred_at_corrected',
        'selesai'       => '-occurred_at_corrected',
        'event'         => '-occurred_at_corrected',
        'masukan'       => '-submitted_at',
        'butir'         => 'node_ref',
        'kode_pilihan'  => 'item_key',
        'izin'          => 'participant_code',
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
            'sesi_lapangan' => $this->fieldSessionQuery($filters, $scope),
            'sesi'       => $this->sessionQuery($filters, $scope),
            'progres'    => $this->progressQuery($filters, $scope),
            'pengerjaan' => $this->attemptQuery($filters, $scope),
            'jawaban'    => $this->responseQuery($filters, $scope),
            'percobaan'  => $this->tryQuery($filters, $scope),
            'navigasi'   => $this->eventQuery($filters, $scope, self::NAVIGATION_EVENTS),
            'bantuan'    => $this->helpQuery($filters, $scope),
            'selesai'    => $this->eventQuery($filters, $scope, self::COMPLETION_EVENTS),
            'event'      => $this->eventQuery($filters, $scope, \App\Libraries\EventCatalog::RESEARCH),
            'masukan'    => $this->feedbackQuery($filters, $scope),
            'butir'      => $this->itemQuery($filters),
            'kode_pilihan' => $this->choiceCodeQuery($filters),
            'izin'       => $this->consentQuery($filters, $scope),
            default      => $this->fieldSheetQuery($dimension, $filters, $scope),
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
        $key  = self::keyOf($dimension);
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
        $columns = array_keys(self::columnMap($dimension));

        if ($scope->anonymous) {
            $columns = array_diff($columns, self::IDENTITY_COLUMNS);
        }

        if (! $scope->fullDetail) {
            $columns = array_diff($columns, self::FULL_DETAIL_COLUMNS);
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
        return array_keys(array_filter(in_array($dimension, self::DIMENSIONS, true) ? self::columnMap($dimension) : [], static fn ($expr): bool => $expr !== null));
    }

    /** Dimensi yang boleh dibaca $scope, urutan resmi. @return list<string> */
    public function dimensions(StaffScope $scope): array
    {
        return $scope->fullDetail ? self::DIMENSIONS : array_values(array_diff(self::DIMENSIONS, self::FULL_DETAIL_DIMENSIONS));
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

            if (str_ends_with($key, '_id') || $key === 'meeting_no') {
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
            'sesi_lapangan' => $this->fieldSessionValues($row),
            'sesi'       => $row,
            'progres'    => $this->progressValues($row),
            'pengerjaan' => $this->attemptValues($row),
            'jawaban'    => $this->responseValues($row, $answers),
            'percobaan'  => $this->tryValues($row, $answers),
            'navigasi'   => $this->navigationValues($row),
            'bantuan'    => $this->helpValues($row),
            'selesai'    => $this->eventValues($row),
            'event'      => $this->eventValues($row) + ['item_key' => ($this->itemMap()[(int) $row['challenge_item_id']] ?? null)?->item_key],
            'masukan'    => $row,
            'butir'      => $this->itemValues($row, $answers),
            'kode_pilihan' => $row + ['node_ref' => $this->nodeRefById($row['challenge_node_id'])],
            'izin'       => $row,
            default      => $this->fieldSheetValues($row),
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
            ->join('schools', 'schools.id = participants.school_id', 'left')
            ->select('participants.id AS _key, participants.participant_code, participants.school_id')
            ->select('schools.research_code AS school_code, participants.rombel')
            ->select('participants.age, participants.gender, participants.class_level')
            ->select('participants.created_at, participants.assented_at')
            ->select('participants.intro_seen_at, participants.pw_first_submit_criteria, participants.pw_weak_submit_count')
            ->where('participants.deleted_at', null);

        // Booklet of the study downloaded (one seat per student and study, 28 §11)
        if (isset($filters['study_id'])) {
            $builder->join('participant_seats', 'participant_seats.participant_id = participants.id AND participant_seats.study_id = ' . (int) $filters['study_id'], 'left')
                ->select('participant_seats.booklet');
        }

        if (! $scope->anonymous) {
            $builder->select('participants.display_name, participants.username, participants.school_name_snapshot')
                ->select('schools.code AS school_npsn');
        }

        $this->applyParticipantFilters($builder, $filters, $scope, 'participants.created_at');

        // Filter milik sesi: siswa yang punya sesi dalam cakupan itu
        $sessionFilters = array_intersect_key($filters, array_flip(['study_id', 'locale', 'meeting_no', 'field_session_code']));

        if ($sessionFilters !== []) {
            $builder->whereIn('participants.id', static function (BaseBuilder $sub) use ($sessionFilters): BaseBuilder {
                $sub->select('game_sessions.participant_id')->from('game_sessions')
                    ->join('field_sessions', 'field_sessions.id = game_sessions.field_session_id', 'left');

                foreach ($sessionFilters as $key => $value) {
                    $sub->where($key === 'field_session_code' ? 'field_sessions.code' : 'game_sessions.' . $key, $value);
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
            ->select('field_sessions.code AS field_session_code, game_sessions.meeting_no')
            ->select('game_sessions.locale, game_sessions.status, game_sessions.started_at, game_sessions.last_active_at')
            ->select('game_sessions.ended_at, game_sessions.duration_ms, game_sessions.device_type, game_sessions.screen_size')
            ->join('research_studies', 'research_studies.id = game_sessions.study_id', 'left')
            ->join('game_releases', 'game_releases.id = game_sessions.release_id', 'left');

        $this->applySessionFilters($builder, $filters, $scope, 'game_sessions.started_at');

        return $builder;
    }

    private function progressQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        // One row per student and study (participant_progress, 28 §8)
        $builder = $this->db()->table('participant_progress')
            ->join('participants', 'participants.id = participant_progress.participant_id')
            ->join('research_studies', 'research_studies.id = participant_progress.study_id', 'left')
            ->select('participant_progress.id AS _key, research_studies.code AS study_code, participants.participant_code')
            ->select('participant_progress.current_level_id, participant_progress.current_node_id')
            ->select('participant_progress.unlocked_level_sequence, participant_progress.completed_nodes')
            ->select('participant_progress.completed_levels, participant_progress.total_stars, participant_progress.updated_at')
            ->where('participants.deleted_at', null);

        $this->applyParticipantFilters($builder, $filters, $scope, 'participant_progress.updated_at');

        if (isset($filters['study_id'])) {
            $builder->where('participant_progress.study_id', $filters['study_id']);
        }

        return $builder;
    }

    /** Field sessions of the study (28 §17), without their token history. */
    private function fieldSessionQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->db()->table('field_sessions')
            ->join('schools', 'schools.id = field_sessions.school_id', 'left')
            ->select('field_sessions.id AS _key, field_sessions.code AS field_session_code, schools.research_code AS school_code')
            ->select('field_sessions.study_id, field_sessions.rombel, field_sessions.meeting_no, field_sessions.makeup_no')
            ->select('field_sessions.main_session_id, field_sessions.region_code, field_sessions.session_date')
            ->select('field_sessions.opens_at, field_sessions.closes_at, field_sessions.closed_at');

        $this->applyFieldSessionFilters($builder, $filters, $scope);

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
            ->select('field_sessions.code AS field_session_code, game_sessions.meeting_no')
            ->select('challenge_attempts.variant_source, challenge_attempts.booklet_at_open')
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
            ->select('challenge_attempts.variant_group, item_responses.challenge_item_id, field_sessions.code AS field_session_code')
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
            ->select('item_tries.occurred_at, item_tries.ready_ms, item_tries.error_code, item_tries.manual_code')
            ->select('item_tries.action_key, item_tries.action_score, item_tries.reason_code, item_tries.reason_ready_ms')
            ->select('item_tries.action_last_input_ms, item_tries.reason_last_input_ms')
            ->select('field_sessions.code AS field_session_code');

        $this->applyAttemptFilters($builder, $filters, $scope, 'item_tries.occurred_at');

        return $builder;
    }

    /** @param list<string>|null $types event types of the sheet (the `event` sheet: every research type) */
    private function eventQuery(array $filters, StaffScope $scope, ?array $types): BaseBuilder
    {
        $builder = $this->sessionBase('game_event_logs', 'game_event_logs.session_id')
            ->select('game_event_logs.id AS _key, game_event_logs.id, game_event_logs.event_uuid')
            ->select('game_event_logs.client_event_id, game_event_logs.session_id, game_sessions.session_code')
            ->select('game_event_logs.participant_id, participants.participant_code, game_event_logs.level_id')
            ->select('game_event_logs.challenge_node_id, game_event_logs.challenge_attempt_id')
            ->select('game_event_logs.challenge_item_id, game_event_logs.event_type, game_event_logs.sequence_no')
            ->select('game_event_logs.occurred_at, game_event_logs.server_received_at, game_event_logs.clock_offset_ms')
            ->select('game_event_logs.payload_json, field_sessions.code AS field_session_code')
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
            ->select('game_sessions.session_code, participants.participant_code, field_sessions.code AS field_session_code')
            ->select('game_event_logs.occurred_at, game_event_logs.clock_offset_ms')
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

        // One row per playback (29 §5.4): its opening action, the largest
        // listened_ms, and whether any row of it completed
        $usage = $db->protectIdentifiers('audio_usage_events', true);
        $same  = "x.session_id = {$usage}.session_id AND x.audio_asset_id = {$usage}.audio_asset_id AND x.play_index = {$usage}.play_index";

        $audio = $this->sessionBase('audio_usage_events', 'audio_usage_events.session_id')
            ->join('challenge_attempts', 'challenge_attempts.id = audio_usage_events.challenge_attempt_id', 'left')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id', 'left')
            ->join('audio_assets', 'audio_assets.id = audio_usage_events.audio_asset_id', 'left')
            ->join('media_assets', 'media_assets.id = audio_assets.media_asset_id', 'left')
            ->select($db->protectIdentifiers('audio_usage_events', true) . '.id * 2 + 1 AS _key', false)
            ->select('game_sessions.session_code, participants.participant_code, field_sessions.code AS field_session_code')
            ->select('audio_usage_events.occurred_at')
            // WAVE-E seam (part 7, 33 K10): audio_usage_events.clock_offset_ms corrects this time
            ->select('NULL AS clock_offset_ms', false)
            ->select("'audio' AS jenis", false)
            ->select('challenge_nodes.level_id, challenge_attempts.challenge_node_id AS node_id')
            ->select('audio_usage_events.challenge_attempt_id AS attempt_id')
            ->select('NULL AS item_id, NULL AS payload_json', false)
            ->select('media_assets.asset_key, audio_assets.context_code AS audio_context, audio_assets.locale AS audio_locale')
            ->select('audio_usage_events.action, audio_usage_events.play_index')
            ->select("(SELECT MAX(x.listened_ms) FROM {$usage} x WHERE {$same}) AS listened_ms", false)
            ->select("(SELECT MAX(x.completed) FROM {$usage} x WHERE {$same}) AS completed", false)
            ->whereIn('audio_usage_events.action', \App\Models\AudioUsageEventModel::OPENING_ACTIONS);

        $this->applySessionFilters($audio, $filters, $scope, 'audio_usage_events.occurred_at');
        $this->applyContentFilters($audio, $filters, 'challenge_nodes.level_id', 'challenge_attempts.challenge_node_id', null);

        return $db->newQuery()->fromSubquery($hints->unionAll($audio), 'bantuan')->select('bantuan.*', false);
    }

    private function feedbackQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->db()->table('participant_feedback')
            ->join('participants', 'participants.id = participant_feedback.participant_id')
            ->join('game_sessions', 'game_sessions.id = participant_feedback.session_id', 'left')
            ->join('field_sessions', 'field_sessions.id = game_sessions.field_session_id', 'left')
            ->select('participant_feedback.id AS _key, participants.participant_code, game_sessions.session_code')
            ->select('participant_feedback.rating, participant_feedback.liked_most, participant_feedback.hardest_part')
            ->select('participant_feedback.new_learning, participant_feedback.suggestion, participant_feedback.submitted_at')
            ->where('participants.deleted_at', null);

        $this->applySessionFilters($builder, $filters, $scope, 'participant_feedback.submitted_at');

        return $builder;
    }

    /**
     * Coded consent of the study's students (part 4, 30 §10.1): codes only;
     * downloading it does not stamp `consent_register_exported_at`.
     */
    private function consentQuery(array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->db()->table('participant_consents')
            ->join('participants', 'participants.id = participant_consents.participant_id')
            ->select('participant_consents.participant_id AS _key, participants.participant_code')
            ->select('participant_consents.parent_consent_on, participant_consents.recording_consent, participant_consents.recording_withdrawn_on')
            ->select('participant_consents.risk_items_consent, participant_consents.risk_items_withdrawn_on')
            ->select('participants.assented_at, participants.assent_version, participant_consents.status, participant_consents.status_on')
            ->where('participants.deleted_at', null);

        if (isset($filters['study_id'])) {
            $builder->where('participant_consents.study_id', (int) $filters['study_id']);
        }

        $this->applyParticipantFilters($builder, array_diff_key($filters, array_flip(['date_from', 'date_to'])), $scope, 'participants.created_at');

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
            ->select('challenge_items.scorable, challenge_items.is_active, media_assets.asset_key AS media_asset_key')
            ->select('challenge_items.round, challenge_items.variant_group AS variant, challenge_items.dimension, challenge_items.subaspect')
            ->select('challenge_items.akm_level, challenge_items.akm_level_source, challenge_items.pirls_process, challenge_items.bloom_level_source')
            ->select('reading_passages.text_genre, challenge_items.testlet_key, challenge_items.d2_topic, challenge_items.d3_clue_type')
            ->select('challenge_items.review_status, challenge_items.reference_source, challenge_items.surface_json');

        $this->applyContentFilters($builder, $filters, 'challenge_nodes.level_id', 'challenge_items.challenge_node_id', 'challenge_items.variant_group');

        return $builder;
    }

    /**
     * Bank code per choice (31 §13.3): every item_error_codes row and every
     * Skenario Digital option, as they stand when downloaded.
     */
    private function choiceCodeQuery(array $filters): BaseBuilder
    {
        $db = $this->db();

        $codes = $db->table('item_error_codes')
            ->join('challenge_items', 'challenge_items.id = item_error_codes.challenge_item_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_items.challenge_node_id')
            ->select($db->protectIdentifiers('item_error_codes', true) . '.id * 2 AS _key', false)
            ->select('challenge_items.item_key, challenge_items.challenge_node_id, challenge_items.variant_group')
            ->select('item_error_codes.choice_type, item_error_codes.choice_ref, item_error_codes.error_code, item_error_codes.is_main')
            ->select('NULL AS option_score', false);
        $this->applyContentFilters($codes, $filters, 'challenge_nodes.level_id', 'challenge_items.challenge_node_id', 'challenge_items.variant_group');

        $options = $db->table('scenario_options')
            ->join('challenge_items', 'challenge_items.id = scenario_options.challenge_item_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_items.challenge_node_id')
            ->select($db->protectIdentifiers('scenario_options', true) . '.id * 2 + 1 AS _key', false)
            ->select('challenge_items.item_key, challenge_items.challenge_node_id, challenge_items.variant_group')
            ->select('scenario_options.kind AS choice_type, scenario_options.option_key AS choice_ref, scenario_options.code AS error_code')
            ->select('NULL AS is_main', false)
            ->select('scenario_options.score AS option_score');
        $this->applyContentFilters($options, $filters, 'challenge_nodes.level_id', 'challenge_items.challenge_node_id', 'challenge_items.variant_group');

        return $db->newQuery()->fromSubquery($codes->unionAll($options), 'kode_pilihan')->select('kode_pilihan.*', false);
    }

    /** Rows of the imported field sheet $sheet that count: main imports not superseded (29 §10.4). */
    private function fieldSheetQuery(string $sheet, array $filters, StaffScope $scope): BaseBuilder
    {
        $builder = $this->db()->table('field_sheet_rows')
            ->join('field_sheet_imports', 'field_sheet_imports.id = field_sheet_rows.import_id')
            ->join('field_sessions', 'field_sessions.id = field_sheet_imports.field_session_id')
            ->select('field_sheet_rows.id AS _key, field_sheet_rows.row_no, field_sheet_rows.values_json')
            ->select('field_sessions.code AS field_session_code')
            ->where('field_sheet_imports.sheet', $sheet)
            ->where('field_sheet_imports.purpose', 'main')
            ->where('field_sheet_imports.superseded_at', null);

        if (isset($filters['participant_id'])) {
            $builder->where('field_sheet_rows.participant_id', $filters['participant_id']);
        }

        $this->applyFieldSessionFilters($builder, $filters, $scope);

        return $builder;
    }

    /** Filters on `field_sessions` (study, school, code, meeting, date), without validators' test schools. */
    private function applyFieldSessionFilters(BaseBuilder $builder, array $filters, StaffScope $scope): void
    {
        $map = [
            'study_id'           => 'field_sessions.study_id',
            'school_id'          => 'field_sessions.school_id',
            'field_session_code' => 'field_sessions.code',
            'meeting_no'         => 'field_sessions.meeting_no',
        ];

        foreach ($map as $key => $column) {
            if (isset($filters[$key])) {
                $builder->where($column, $filters[$key]);
            }
        }

        if (isset($filters['date_from'])) {
            $builder->where('field_sessions.session_date >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $builder->where('field_sessions.session_date <=', $filters['date_to']);
        }

        if ($scope->anonymous) {
            $builder->whereNotIn('field_sessions.school_id', $this->testSchoolIds());
        }
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
            ->join('field_sessions', 'field_sessions.id = game_sessions.field_session_id', 'left')
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
            ->join('field_sessions', 'field_sessions.id = game_sessions.field_session_id', 'left')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->join('levels', 'levels.id = challenge_nodes.level_id')
            ->where('participants.deleted_at', null);
    }

    /** Cakupan sekolah + filter milik siswa + rentang tanggal pada $dateColumn. */
    private function applyParticipantFilters(BaseBuilder $builder, array $filters, StaffScope $scope, string $dateColumn): void
    {
        $map = [
            'participant_id'   => 'participants.id',
            'participant_code' => 'participants.participant_code',
            'school_id'        => 'participants.school_id',
            'class_level'      => 'participants.class_level',
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

        // Research data never holds the validators' test accounts or simulated
        // students (29 §7.1); a one-student view still shows them.
        if ($scope->anonymous && ! isset($filters['participant_id'])) {
            $builder->notLike('participants.username', \App\Libraries\Simulation\Simulator::USERNAME_PREFIX, 'after')
                ->notLike('participants.username', \App\Services\TestAccountService::PREFIX, 'after')
                ->groupStart()
                ->where('participants.school_id', null)
                ->orWhereNotIn('participants.school_id', $this->testSchoolIds())
                ->groupEnd();
        }
    }

    /** @return list<int> schools of the validators' test install (V01); [0] when none, so NOT IN stays valid */
    private function testSchoolIds(): array
    {
        return $this->maps['test_schools'] ??= array_map('intval', array_column(
            $this->db()->table('schools')->select('id')->where('research_code', \App\Services\TestAccountService::SCHOOL_CODE)->get()->getResultArray(),
            'id',
        )) ?: [0];
    }

    private function applySessionFilters(BaseBuilder $builder, array $filters, StaffScope $scope, string $dateColumn): void
    {
        $this->applyParticipantFilters($builder, $filters, $scope, $dateColumn);

        foreach (['study_id' => 'game_sessions.study_id', 'locale' => 'game_sessions.locale', 'meeting_no' => 'game_sessions.meeting_no', 'field_session_code' => 'field_sessions.code'] as $key => $column) {
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
            : (self::DEFAULT_SORT[$dimension] ?? 'sheet_row');
    }

    private function applySort(BaseBuilder $builder, string $dimension, string $sort): void
    {
        $direction = str_starts_with($sort, '-') ? 'DESC' : 'ASC';
        $column    = ltrim($sort, '-');

        foreach ((array) self::columnMap($dimension)[$column] as $expression) {
            $builder->orderBy($expression, $direction);
        }

        // Pemecah seri: urutan stabil antarhalaman
        $builder->orderBy(self::keyOf($dimension), $direction);
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
            'school_code'              => $row['school_code'] ?? null,
            'rombel'                   => $row['rombel'] ?? null,
            'booklet'                  => $row['booklet'] ?? null,
            'age'                      => $row['age'],
            'gender'                   => $row['gender'],
            'class_level'              => $row['class_level'],
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
        ] + self::correctedTime($row) + $row;
    }

    /**
     * `occurred_at_corrected` = occurred_at + clock_offset_ms and
     * `offset_known` (29 K8): without an offset the raw time, known = 0;
     * events written by the server have offset 0.
     *
     * @param array<string, mixed> $row occurred_at, clock_offset_ms
     *
     * @return array{occurred_at_corrected: ?string, offset_known: int}
     */
    public static function correctedTime(array $row): array
    {
        $offset = $row['clock_offset_ms'] ?? null;
        $time   = $row['occurred_at'] ?? null;

        if ($time === null || $time === '' || $offset === null || $offset === '') {
            return ['occurred_at_corrected' => $time, 'offset_known' => 0];
        }

        $at = new \DateTimeImmutable((string) $time, new \DateTimeZone(config('App')->appTimezone));
        $ms = (int) $offset;
        $at = $at->modify(sprintf('%+d seconds %+d microseconds', intdiv($ms, 1000), ($ms % 1000) * 1000));

        return ['occurred_at_corrected' => $at->format('Y-m-d H:i:s.u'), 'offset_known' => 1];
    }

    private function fieldSessionValues(array $row): array
    {
        $this->maps['studies'] ??= [];
        $studyId = (int) $row['study_id'];
        $this->maps['studies'][$studyId] ??= model(\App\Models\ResearchStudyModel::class)->find($studyId);

        $regions = service('fieldSessions')->planEntry($row + ['study_id' => $studyId], $this->maps['studies'][$studyId])['regions'];

        $this->maps['field_codes'] ??= array_column($this->db()->table('field_sessions')->select('id, code')->get()->getResultArray(), 'code', 'id');

        return $row + [
            'region_opened'     => $regions === [] ? null : implode(',', $regions),
            'main_session_code' => $this->maps['field_codes'][(int) $row['main_session_id']] ?? null,
        ];
    }

    /** Field sheet row: the typed values under their spec codes, beside the session code. */
    private function fieldSheetValues(array $row): array
    {
        return ['field_session_code' => $row['field_session_code'], 'sheet_row' => $row['row_no']] + $this->decode($row['values_json']);
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
        ] + self::correctedTime($row) + $row;
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
            'booklet_shared' => $row['variant_group'] === \App\Libraries\BankRules::SHARED_GROUP ? 1 : 0,
        ] + $this->surfaceValues($row['surface_json'] ?? null);
    }

    /**
     * Surface-load columns of one item (SurfaceLoad, frozen at import).
     *
     * @return array<string, int|float|null>
     */
    private function surfaceValues(mixed $json): array
    {
        $load = is_string($json) ? (json_decode($json, true) ?: []) : (array) ($json ?? []);
        $out  = [];

        foreach (\App\Libraries\SurfaceLoad::KEYS as $key) {
            $out[$key] = $load[$key] ?? null;
        }

        return $out;
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
            throw new \DomainException('Data ini hanya untuk ketua peneliti.');
        }
    }

    /**
     * Column map of one dimension: COLUMNS, or for a field sheet the session
     * code, the row number, and the FieldSheetSpec columns.
     *
     * @return array<string, list<string>|string|null>
     */
    private static function columnMap(string $dimension): array
    {
        if (isset(self::COLUMNS[$dimension])) {
            return self::COLUMNS[$dimension];
        }

        if (! in_array($dimension, self::DIMENSIONS, true) || ! \App\Libraries\FieldSheetSpec::has($dimension)) {
            throw new \InvalidArgumentException("Dimensi '{$dimension}' tidak dikenal.");
        }

        $map = ['field_session_code' => 'field_sessions.code', 'sheet_row' => 'field_sheet_rows.row_no'];

        foreach (array_keys(\App\Libraries\FieldSheetSpec::columns($dimension)) as $code) {
            $map[$code] ??= null;
        }

        return $map;
    }

    private static function keyOf(string $dimension): string
    {
        if (isset(self::KEYS[$dimension])) {
            return self::KEYS[$dimension];
        }

        self::columnMap($dimension);   // throws for an unknown dimension

        return 'field_sheet_rows.id';
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
