<?php

namespace App\Services;

use App\Libraries\ExcelWriter;
use App\Models\AuditLogModel;
use App\Models\DataExportModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * Pembangun berkas export penelitian (FITUR 14 & 15).
 *
 * Service ini adalah lapis ketiga otorisasi: hak pemohon dibaca ulang dari
 * `staff_users`, bukan dari isi formulir. Pemohon non-admin selalu
 * - dibatasi `school_id` sekolahnya (tanpa sekolah → scope 0, tidak cocok apa pun),
 * - anonim, apa pun nilai kolom `anonymized`,
 * - tanpa sheet `Raw Events` dan tanpa kolom kunci jawaban.
 *
 * Kolom identitas (nama, nama pengguna, nama sekolah) pada mode anonim tidak
 * dikosongkan melainkan tidak dibuat sama sekali; `password_hash`,
 * `failed_login_count`, dan `locked_until` tidak pernah dibaca.
 */
class ExportService
{
    /** Urutan resmi sheet workbook (07_FEATURE_INTEGRATION.md FITUR 14). */
    public const SHEETS = [
        'Participants', 'Sessions', 'Levels', 'Challenge Summary', 'Item Responses',
        'Raw Events', 'Audio Usage', 'Indicators', 'Demographic Summary', 'Feedback',
    ];

    public const ADMIN_ONLY_SHEETS = ['Raw Events'];

    /** Kolom yang hanya ada pada export beridentitas. */
    public const IDENTITY_COLUMNS = ['display_name', 'username', 'school_name'];

    /** Kolom yang hanya ada untuk role admin. */
    public const ADMIN_ONLY_COLUMNS = ['answer_key'];

    /** Kunci filter yang diterima dari scope_json. */
    public const FILTER_KEYS = [
        'study_id', 'phase_code', 'level_id', 'school_id', 'class_level',
        'province_code', 'locale', 'date_from', 'date_to',
    ];

    private const CHUNK = 2000;

    /** @var array<string, array<int|string, mixed>> cache peta id → label per build */
    private array $maps = [];

    /**
     * Membangun berkas untuk satu baris data_exports berstatus queued/running.
     * Tidak melempar exception: kegagalan dicatat sebagai status `failed`.
     *
     * @return array<string, mixed> baris data_exports setelah build
     */
    public function build(int $exportId): array
    {
        $exports = model(DataExportModel::class);
        $export  = $exports->find($exportId);

        if ($export === null) {
            throw new \RuntimeException("Export {$exportId} tidak ditemukan.");
        }

        if (! in_array($export['status'], ['queued', 'running'], true)) {
            return $export;
        }

        $path = null;

        try {
            $context = $this->context($export);

            if ((int) $export['anonymized'] !== (int) $context['anonymized']) {
                $exports->update($exportId, ['anonymized' => $context['anonymized'] ? 1 : 0]);
            }

            $exports->update($exportId, ['status' => 'running']);

            $path = $this->targetPath($exportId, (string) $export['format']);

            if ($export['format'] === 'xlsx') {
                ['sha' => $sha, 'rows' => $rows] = $this->writeXlsx($path, $context);
            } elseif ($export['format'] === 'pdf') {
                ['sha' => $sha, 'rows' => $rows] = service('reportService')->render($path, $context);
            } else {
                throw new \DomainException("Format export '{$export['format']}' belum didukung.");
            }

            $exports->markDone($exportId, $path, $sha, $rows);
        } catch (\Throwable $e) {
            if ($path !== null && is_file($path)) {
                unlink($path);
            }

            log_message('error', 'Export {id} gagal: {msg}', ['id' => $exportId, 'msg' => $e->getMessage()]);

            $message = $e instanceof \DomainException || $e instanceof \OverflowException
                ? $e->getMessage()
                : 'Export gagal dibuat: ' . $e->getMessage();

            $exports->markFailed($exportId, $message);
        }

        return $exports->find($exportId);
    }

    /** Nama lama di dokumen tahap 7; sama dengan build() untuk baris berformat xlsx. */
    public function buildXlsx(int $exportId): array
    {
        return $this->build($exportId);
    }

    /**
     * Kolom satu sheet menurut mode anonim dan role pemohon.
     *
     * @return list<string>
     */
    public function columns(string $sheet, bool $anonymized, bool $isAdmin): array
    {
        $columns = match ($sheet) {
            'Participants' => [
                'participant_code', 'display_name', 'username', 'school_name', 'school_ref',
                'age', 'class_level', 'gender', 'country', 'province', 'district', 'registered_at',
                'consent_version', 'participant_consented', 'guardian_consented', 'consented_at',
                'consent_withdrawn_at', 'pw_first_submit_criteria', 'pw_weak_submit_count',
            ],
            'Sessions' => [
                'session_code', 'participant_code', 'study_code', 'phase_code', 'release_code',
                'content_version', 'scoring_version', 'locale', 'status', 'started_at', 'last_active_at',
                'ended_at', 'duration_ms', 'device_type', 'os_name', 'browser_name', 'screen_size',
                'is_touch', 'shards', 'completed_levels', 'total_stars', 'total_score',
            ],
            'Levels' => [
                'session_code', 'participant_code', 'phase_code', 'level_code', 'level_sequence',
                'completed_nodes', 'total_nodes', 'level_score', 'stars', 'duration_ms',
            ],
            'Challenge Summary' => [
                'attempt_id', 'session_code', 'participant_code', 'phase_code', 'level_code',
                'node_sequence', 'engine_type', 'variant_code', 'attempt_no', 'status',
                'scorable_items', 'first_pass_correct', 'final_correct', 'first_pass_accuracy',
                'final_accuracy', 'check_count', 'hint_count', 'retry_count', 'answer_change_count',
                'audio_use_count', 'independence', 'score', 'stars', 'scoring_version',
                'started_at', 'completed_at', 'duration_ms',
            ],
            'Item Responses' => [
                'response_id', 'attempt_id', 'session_code', 'participant_code', 'phase_code',
                'level_code', 'node_sequence', 'item_key', 'interaction_type', 'indicator',
                'display_order', 'status', 'first_answer', 'final_answer', 'answer_key',
                'reason_text', 'first_pass_correct', 'is_correct', 'change_count', 'hint_used',
                'wrong_click_count', 'answered_at', 'duration_ms',
            ],
            'Raw Events' => [
                'event_uuid', 'session_code', 'participant_code', 'phase_code', 'level_code',
                'node_sequence', 'attempt_id', 'item_key', 'event_type', 'sequence_no',
                'client_event_id', 'occurred_at', 'server_received_at', 'payload',
            ],
            'Audio Usage' => [
                'session_code', 'participant_code', 'phase_code', 'audio_asset_id', 'asset_key',
                'context_code', 'character_code', 'audio_locale', 'asset_duration_ms', 'attempt_id',
                'action', 'play_index', 'listened_ms', 'completed', 'occurred_at',
            ],
            'Indicators' => [
                'participant_code', 'phase_code', 'indicator_code', 'indicator_name',
                'evidence_count', 'correct_count', 'mastery_ratio', 'mean_response_ms',
            ],
            'Demographic Summary' => [
                'dimension', 'group', 'participants', 'sessions', 'completed_sessions',
                'completion_rate', 'mean_total_score',
            ],
            'Feedback' => [
                'participant_code', 'session_code', 'phase_code', 'rating', 'liked_most',
                'hardest_part', 'new_learning', 'suggestion', 'submitted_at',
            ],
            default => throw new \InvalidArgumentException("Sheet '{$sheet}' tidak dikenali."),
        };

        if ($anonymized) {
            $columns = array_diff($columns, self::IDENTITY_COLUMNS);
        }

        if (! $isAdmin) {
            $columns = array_diff($columns, self::ADMIN_ONLY_COLUMNS);
        }

        return array_values($columns);
    }

    /**
     * Sheet yang boleh diterima pemohon, dalam urutan resmi.
     *
     * @param list<string> $requested
     *
     * @return list<string>
     */
    public function allowedSheets(array $requested, bool $isAdmin): array
    {
        $allowed = $isAdmin ? self::SHEETS : array_values(array_diff(self::SHEETS, self::ADMIN_ONLY_SHEETS));
        $picked  = array_values(array_intersect($allowed, array_map('strval', $requested)));

        return $picked === [] ? $allowed : $picked;
    }

    /**
     * Filter dari scope_json yang disaring: hanya kunci resmi, tanggal
     * berformat Y-m-d, dan school_id dipaksa untuk pemohon non-admin.
     *
     * @param array<string, mixed> $filters
     *
     * @return array<string, int|string>
     */
    public function sanitizeFilters(array $filters, ?int $schoolScope): array
    {
        $out = [];

        foreach (self::FILTER_KEYS as $key) {
            $value = $filters[$key] ?? null;

            if (! is_scalar($value) || trim((string) $value) === '') {
                continue;
            }

            $value = trim((string) $value);

            if (in_array($key, ['date_from', 'date_to'], true)
                && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
                continue;
            }

            $out[$key] = in_array($key, ['study_id', 'level_id', 'school_id'], true) ? (int) $value : $value;
        }

        if ($schoolScope !== null) {
            $out['school_id'] = $schoolScope;
        }

        return $out;
    }

    // ------------------------------------------------------------ konteks

    /**
     * Hak dan cakupan export, dibaca ulang dari staff_users.
     *
     * @return array{export_id: int, filters: array<string, int|string>, sheets: list<string>,
     *               anonymized: bool, is_admin: bool, school_scope: ?int, template: string,
     *               participant_id: ?int, requested_by: int, requester: string}
     */
    public function context(array $export): array
    {
        $staff = $this->db()->table('staff_users')
            ->select('id, username, display_name, role, school_id, is_active')
            ->where('id', (int) $export['requested_by'])
            ->get()
            ->getRowArray();

        if ($staff === null || ! (int) $staff['is_active']) {
            throw new \DomainException('Pemohon export tidak ditemukan atau sudah nonaktif.');
        }

        $isAdmin     = $staff['role'] === 'admin';
        $schoolScope = $isAdmin ? null : (int) ($staff['school_id'] ?? 0);

        $scope = is_array($export['scope_json'])
            ? $export['scope_json']
            : (json_decode((string) $export['scope_json'], true) ?: []);

        $participantId = (int) ($scope['participant_id'] ?? 0);

        return [
            'export_id'      => (int) $export['id'],
            'filters'        => $this->sanitizeFilters((array) ($scope['filters'] ?? []), $schoolScope),
            'sheets'         => $this->allowedSheets((array) ($scope['sheets'] ?? []), $isAdmin),
            'anonymized'     => $isAdmin ? (bool) (int) $export['anonymized'] : true,
            'is_admin'       => $isAdmin,
            'school_scope'   => $schoolScope,
            'template'       => ($scope['template'] ?? '') === 'participant' ? 'participant' : 'study',
            'participant_id' => $participantId > 0 ? $participantId : null,
            'requested_by'   => (int) $staff['id'],
            'requester'      => (string) ($staff['display_name'] ?: $staff['username']),
        ];
    }

    // --------------------------------------------------------------- XLSX

    /**
     * @param array<string, mixed> $context hasil context()
     *
     * @return array{sha: string, rows: int}
     */
    public function writeXlsx(string $path, array $context): array
    {
        if (in_array('Raw Events', $context['sheets'], true)) {
            $this->assertRawEventVolume($context['filters']);
        }

        $writer = new ExcelWriter($path);

        try {
            foreach ($context['sheets'] as $sheet) {
                $columns = $this->columns($sheet, $context['anonymized'], $context['is_admin']);

                $writer->startSheet($sheet, $columns);

                foreach ($this->rows($sheet, $context) as $row) {
                    $writer->row(array_map(static fn (string $col) => $row[$col] ?? null, $columns));
                }
            }

            $sha = $writer->finish();
        } catch (\Throwable $e) {
            $writer->abort();

            throw $e;
        }

        return ['sha' => $sha, 'rows' => $writer->rowCount()];
    }

    /**
     * Estimasi COUNT(*) sebelum penulisan; di atas ambang, export ditolak.
     *
     * @param array<string, int|string> $filters
     */
    public function assertRawEventVolume(array $filters): void
    {
        $limit = config('Gelita')->exportMaxRawEvents;
        $count = $this->eventBuilder($filters)->countAllResults();

        if ($count > $limit) {
            throw new \OverflowException(sprintf(
                'Raw Events berisi sekitar %s baris, melebihi batas %s. Persempit rentang tanggal atau cakupan, lalu minta export lagi.',
                number_format($count, 0, ',', '.'),
                number_format($limit, 0, ',', '.'),
            ));
        }
    }

    /**
     * Generator baris satu sheet (array asosiatif kolom → nilai).
     *
     * @param array<string, mixed> $context
     *
     * @return iterable<array<string, mixed>>
     */
    public function rows(string $sheet, array $context): iterable
    {
        $filters = $context['filters'];

        return match ($sheet) {
            'Participants'        => $this->participantRows($filters, $context['anonymized']),
            'Sessions'            => $this->sessionRows($filters),
            'Levels'              => $this->levelRows($filters),
            'Challenge Summary'   => $this->attemptRows($filters),
            'Item Responses'      => $this->responseRows($filters, $context['is_admin']),
            'Raw Events'          => $context['is_admin'] ? $this->eventRows($filters) : [],
            'Audio Usage'         => $this->audioRows($filters),
            'Indicators'          => $this->indicatorRows($filters),
            'Demographic Summary' => $this->demographicRows($filters, $context['anonymized']),
            'Feedback'            => $this->feedbackRows($filters),
            default               => throw new \InvalidArgumentException("Sheet '{$sheet}' tidak dikenali."),
        };
    }

    // ---------------------------------------------------------- per sheet

    /** @return \Generator<array<string, mixed>> */
    private function participantRows(array $filters, bool $anonymized): \Generator
    {
        $sessionFilter = $this->sessionFilterSubquery($filters);

        $query = function () use ($anonymized, $sessionFilter): BaseBuilder {
            $builder = $this->db()->table('participants')
                ->select('participants.id AS _id, participants.participant_code, participants.school_id')
                ->select('participants.age, participants.class_level, participants.gender')
                ->select('participants.country_name_snapshot, participants.province_name_snapshot')
                ->select('participants.district_name_snapshot, participants.created_at')
                ->select('participants.pw_first_submit_criteria, participants.pw_weak_submit_count')
                ->select('pc.consent_version, pc.participant_consented, pc.parent_guardian_consented')
                ->select('pc.consented_at, pc.withdrawn_at')
                ->join(
                    'participant_consents pc',
                    'pc.id = (SELECT MAX(c2.id) FROM participant_consents c2 WHERE c2.participant_id = participants.id)',
                    'left',
                    false,
                )
                ->where('participants.deleted_at', null)
                ->whereIn('participants.id', $sessionFilter);

            if (! $anonymized) {
                $builder->select('participants.display_name, participants.username, participants.school_name_snapshot');
            }

            return $builder;
        };

        foreach ($this->chunked($query, 'participants.id') as $row) {
            $out = [
                'participant_code'         => $row['participant_code'],
                'school_ref'               => $this->schoolRef($row['school_id']),
                'age'                      => $this->intOrNull($row['age']),
                'class_level'              => $row['class_level'],
                'gender'                   => $row['gender'],
                'country'                  => $row['country_name_snapshot'],
                'province'                 => $row['province_name_snapshot'],
                'district'                 => $row['district_name_snapshot'],
                'registered_at'            => $row['created_at'],
                'consent_version'          => $row['consent_version'],
                'participant_consented'    => $this->intOrNull($row['participant_consented']),
                'guardian_consented'       => $this->intOrNull($row['parent_guardian_consented']),
                'consented_at'             => $row['consented_at'],
                'consent_withdrawn_at'     => $row['withdrawn_at'],
                'pw_first_submit_criteria' => $this->intOrNull($row['pw_first_submit_criteria']),
                'pw_weak_submit_count'     => (int) $row['pw_weak_submit_count'],
            ];

            if (! $anonymized) {
                $out['display_name'] = $row['display_name'];
                $out['username']     = $row['username'];
                $out['school_name']  = $row['school_name_snapshot'];
            }

            yield $out;
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function sessionRows(array $filters): \Generator
    {
        $releases = $this->releaseMap();
        $studies  = $this->studyMap();

        $query = fn (): BaseBuilder => $this->sessionBuilder($filters)
            ->select('game_sessions.id AS _id, game_sessions.*, participants.participant_code', false)
            ->select('research_phases.code AS phase_code', false)
            ->select('sp.completed_nodes, sp.completed_levels, sp.total_stars, sp.total_score', false)
            ->join('session_progress sp', 'sp.session_id = game_sessions.id', 'left');

        foreach ($this->chunked($query, 'game_sessions.id') as $row) {
            $release = $releases[(int) $row['release_id']] ?? [];

            yield [
                'session_code'     => $row['session_code'],
                'participant_code' => $row['participant_code'],
                'study_code'       => $studies[(int) $row['study_id']] ?? null,
                'phase_code'       => $row['phase_code'],
                'release_code'     => $release['release_code'] ?? null,
                'content_version'  => $release['content_version'] ?? null,
                'scoring_version'  => $release['scoring_version'] ?? null,
                'locale'           => $row['locale'],
                'status'           => $row['status'],
                'started_at'       => $row['started_at'],
                'last_active_at'   => $row['last_active_at'],
                'ended_at'         => $row['ended_at'],
                'duration_ms'      => (int) $row['duration_ms'],
                'device_type'      => $row['device_type'],
                'os_name'          => $row['os_name'],
                'browser_name'     => $row['browser_name'],
                'screen_size'      => $row['screen_size'],
                'is_touch'         => $this->intOrNull($row['is_touch']),
                'shards'           => (int) ($row['completed_nodes'] ?? 0),
                'completed_levels' => (int) ($row['completed_levels'] ?? 0),
                'total_stars'      => (int) ($row['total_stars'] ?? 0),
                'total_score'      => (float) ($row['total_score'] ?? 0),
            ];
        }
    }

    /**
     * Skor level mengikuti ScoringService::levelScore(): attempt completed
     * terbaik per node (skor tertinggi, attempt_no terbesar bila seri), rata-rata
     * berbobot scorable_items. Dihitung per potongan sesi agar tidak N×3 query.
     *
     * @return \Generator<array<string, mixed>>
     */
    private function levelRows(array $filters): \Generator
    {
        $levels     = $this->levelMap();
        $nodeCounts = $this->activeNodeCounts();

        if (isset($filters['level_id'])) {
            $levels = array_intersect_key($levels, [(int) $filters['level_id'] => true]);
        }

        $sessionQuery = fn (): BaseBuilder => $this->sessionBuilder($filters)
            ->select('game_sessions.id AS _id, game_sessions.session_code, participants.participant_code', false)
            ->select('research_phases.code AS phase_code', false);

        foreach ($this->chunkedBatches($sessionQuery, 'game_sessions.id') as $sessions) {
            $best = [];
            $rows = $this->db()->table('challenge_attempts ca')
                ->select('ca.session_id, ca.challenge_node_id, ca.score, ca.stars, ca.scorable_items, ca.duration_ms, cn.level_id')
                ->join('challenge_nodes cn', 'cn.id = ca.challenge_node_id')
                ->whereIn('ca.session_id', array_column($sessions, '_id'))
                ->where('ca.status', 'completed')
                ->orderBy('ca.session_id', 'ASC')
                ->orderBy('ca.challenge_node_id', 'ASC')
                ->orderBy('ca.score', 'ASC')
                ->orderBy('ca.attempt_no', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $best[(int) $row['session_id']][(int) $row['level_id']][(int) $row['challenge_node_id']] = $row;
            }

            foreach ($sessions as $session) {
                foreach ($levels as $levelId => $level) {
                    $attempts = $best[(int) $session['_id']][$levelId] ?? [];
                    $weighted = 0.0;
                    $weight   = 0;
                    $stars    = 0;
                    $duration = 0;

                    foreach ($attempts as $attempt) {
                        $items = max(1, (int) $attempt['scorable_items']);
                        $weighted += (float) $attempt['score'] * $items;
                        $weight += $items;
                        $stars += (int) $attempt['stars'];
                        $duration += (int) $attempt['duration_ms'];
                    }

                    yield [
                        'session_code'     => $session['session_code'],
                        'participant_code' => $session['participant_code'],
                        'phase_code'       => $session['phase_code'],
                        'level_code'       => $level['code'],
                        'level_sequence'   => $level['sequence'],
                        'completed_nodes'  => count($attempts),
                        'total_nodes'      => $nodeCounts[$levelId] ?? 0,
                        'level_score'      => $weight > 0 ? round($weighted / $weight, 2) : 0.0,
                        'stars'            => $stars,
                        'duration_ms'      => $duration,
                    ];
                }
            }
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function attemptRows(array $filters): \Generator
    {
        $levels = $this->levelMap();

        $query = fn (): BaseBuilder => $this->attemptBuilder($filters)
            ->select('challenge_attempts.id AS _id, challenge_attempts.*', false)
            ->select('game_sessions.session_code, participants.participant_code', false)
            ->select('research_phases.code AS phase_code', false)
            ->select('challenge_nodes.level_id, challenge_nodes.sequence AS node_sequence', false)
            ->select('challenge_nodes.engine_type, challenge_nodes.variant_code', false);

        foreach ($this->chunked($query, 'challenge_attempts.id') as $row) {
            yield [
                'attempt_id'          => (int) $row['_id'],
                'session_code'        => $row['session_code'],
                'participant_code'    => $row['participant_code'],
                'phase_code'          => $row['phase_code'],
                'level_code'          => $levels[(int) $row['level_id']]['code'] ?? null,
                'node_sequence'       => (int) $row['node_sequence'],
                'engine_type'         => $row['engine_type'],
                'variant_code'        => $row['variant_code'],
                'attempt_no'          => (int) $row['attempt_no'],
                'status'              => $row['status'],
                'scorable_items'      => (int) $row['scorable_items'],
                'first_pass_correct'  => (int) $row['first_pass_correct'],
                'final_correct'       => (int) $row['final_correct'],
                'first_pass_accuracy' => (float) $row['first_pass_accuracy'],
                'final_accuracy'      => (float) $row['final_accuracy'],
                'check_count'         => (int) $row['check_count'],
                'hint_count'          => (int) $row['hint_count'],
                'retry_count'         => (int) $row['retry_count'],
                'answer_change_count' => (int) $row['answer_change_count'],
                'audio_use_count'     => (int) $row['audio_use_count'],
                'independence'        => (float) $row['independence'],
                'score'               => (float) $row['score'],
                'stars'               => (int) $row['stars'],
                'scoring_version'     => $row['scoring_version'],
                'started_at'          => $row['started_at'],
                'completed_at'        => $row['completed_at'],
                'duration_ms'         => $this->intOrNull($row['duration_ms']),
            ];
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function responseRows(array $filters, bool $isAdmin): \Generator
    {
        $levels = $this->levelMap();
        $items  = $this->itemMap();

        $query = fn (): BaseBuilder => $this->attemptBuilder($filters)
            ->join('item_responses ir', 'ir.challenge_attempt_id = challenge_attempts.id')
            ->select('ir.id AS _id, ir.challenge_item_id, ir.display_order, ir.status', false)
            ->select('ir.first_answer_json, ir.final_answer_json, ir.reason_text', false)
            ->select('ir.first_pass_correct, ir.is_correct, ir.change_count, ir.hint_used', false)
            ->select('ir.wrong_click_count, ir.answered_at, ir.duration_ms', false)
            ->select('challenge_attempts.id AS attempt_id, game_sessions.session_code', false)
            ->select('participants.participant_code, research_phases.code AS phase_code', false)
            ->select('challenge_nodes.level_id, challenge_nodes.sequence AS node_sequence', false);

        foreach ($this->chunked($query, 'ir.id') as $row) {
            $item = $items[(int) $row['challenge_item_id']] ?? [];
            $out  = [
                'response_id'        => (int) $row['_id'],
                'attempt_id'         => (int) $row['attempt_id'],
                'session_code'       => $row['session_code'],
                'participant_code'   => $row['participant_code'],
                'phase_code'         => $row['phase_code'],
                'level_code'         => $levels[(int) $row['level_id']]['code'] ?? null,
                'node_sequence'      => (int) $row['node_sequence'],
                'item_key'           => $item['item_key'] ?? null,
                'interaction_type'   => $item['interaction_type'] ?? null,
                'indicator'          => $item['indicator'] ?? null,
                'display_order'      => (int) $row['display_order'],
                'status'             => $row['status'],
                'first_answer'       => $row['first_answer_json'],
                'final_answer'       => $row['final_answer_json'],
                'reason_text'        => $row['reason_text'],
                'first_pass_correct' => $this->intOrNull($row['first_pass_correct']),
                'is_correct'         => $this->intOrNull($row['is_correct']),
                'change_count'       => (int) $row['change_count'],
                'hint_used'          => (int) $row['hint_used'],
                'wrong_click_count'  => (int) $row['wrong_click_count'],
                'answered_at'        => $row['answered_at'],
                'duration_ms'        => $this->intOrNull($row['duration_ms']),
            ];

            if ($isAdmin) {
                $out['answer_key'] = $item['answer_key'] ?? null;
            }

            yield $out;
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function eventRows(array $filters): \Generator
    {
        $levels = $this->levelMap();
        $nodes  = $this->nodeMap();
        $items  = $this->itemMap();

        $query = fn (): BaseBuilder => $this->eventBuilder($filters)
            ->select('game_event_logs.id AS _id, game_event_logs.event_uuid, game_event_logs.level_id', false)
            ->select('game_event_logs.challenge_node_id, game_event_logs.challenge_attempt_id', false)
            ->select('game_event_logs.challenge_item_id, game_event_logs.event_type', false)
            ->select('game_event_logs.sequence_no, game_event_logs.client_event_id', false)
            ->select('game_event_logs.occurred_at, game_event_logs.server_received_at', false)
            ->select('game_event_logs.payload_json, game_sessions.session_code', false)
            ->select('participants.participant_code, research_phases.code AS phase_code', false);

        foreach ($this->chunked($query, 'game_event_logs.id') as $row) {
            $nodeId = $this->intOrNull($row['challenge_node_id']);

            yield [
                'event_uuid'         => $row['event_uuid'],
                'session_code'       => $row['session_code'],
                'participant_code'   => $row['participant_code'],
                'phase_code'         => $row['phase_code'],
                'level_code'         => $levels[(int) $row['level_id']]['code'] ?? null,
                'node_sequence'      => $nodeId === null ? null : ($nodes[$nodeId]['sequence'] ?? null),
                'attempt_id'         => $this->intOrNull($row['challenge_attempt_id']),
                'item_key'           => $items[(int) $row['challenge_item_id']]['item_key'] ?? null,
                'event_type'         => $row['event_type'],
                'sequence_no'        => (int) $row['sequence_no'],
                'client_event_id'    => $row['client_event_id'],
                'occurred_at'        => $row['occurred_at'],
                'server_received_at' => $row['server_received_at'],
                'payload'            => $row['payload_json'],
            ];
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function audioRows(array $filters): \Generator
    {
        $query = fn (): BaseBuilder => $this->sessionBuilder($filters)
            ->join('audio_usage_events aue', 'aue.session_id = game_sessions.id')
            ->join('audio_assets aa', 'aa.id = aue.audio_asset_id', 'left')
            ->join('media_assets ma', 'ma.id = aa.media_asset_id', 'left')
            ->select('aue.id AS _id, aue.audio_asset_id, aue.challenge_attempt_id, aue.action', false)
            ->select('aue.play_index, aue.listened_ms, aue.completed, aue.occurred_at', false)
            ->select('aa.context_code, aa.character_code, aa.locale AS audio_locale, aa.duration_ms', false)
            ->select('ma.asset_key, game_sessions.session_code, participants.participant_code', false)
            ->select('research_phases.code AS phase_code', false);

        foreach ($this->chunked($query, 'aue.id') as $row) {
            yield [
                'session_code'      => $row['session_code'],
                'participant_code'  => $row['participant_code'],
                'phase_code'        => $row['phase_code'],
                'audio_asset_id'    => (int) $row['audio_asset_id'],
                'asset_key'         => $row['asset_key'],
                'context_code'      => $row['context_code'],
                'character_code'    => $row['character_code'],
                'audio_locale'      => $row['audio_locale'],
                'asset_duration_ms' => $this->intOrNull($row['duration_ms']),
                'attempt_id'        => $this->intOrNull($row['challenge_attempt_id']),
                'action'            => $row['action'],
                'play_index'        => (int) $row['play_index'],
                'listened_ms'       => $this->intOrNull($row['listened_ms']),
                'completed'         => (int) $row['completed'],
                'occurred_at'       => $row['occurred_at'],
            ];
        }
    }

    /**
     * Penguasaan per peserta × fase × indikator. Bukti = butir terjawab;
     * benar = tepat sejak awal (first_pass_correct), sama dengan dasbor.
     *
     * @return \Generator<array<string, mixed>>
     */
    private function indicatorRows(array $filters): \Generator
    {
        $rows = $this->attemptBuilder($filters)
            ->join('item_responses ir', 'ir.challenge_attempt_id = challenge_attempts.id')
            ->join('challenge_items ci', 'ci.id = ir.challenge_item_id')
            ->join('learning_indicators li', 'li.id = COALESCE(ci.indicator_id, challenge_nodes.indicator_id)', 'inner', false)
            ->select('participants.participant_code, research_phases.code AS phase_code', false)
            ->select('li.code AS indicator_code, li.name_id', false)
            ->select('COUNT(*) AS evidence_count', false)
            ->select('SUM(CASE WHEN ir.first_pass_correct = 1 THEN 1 ELSE 0 END) AS correct_count', false)
            ->select('AVG(ir.duration_ms) AS mean_response_ms', false)
            ->where('ir.status', 'answered')
            ->groupBy('participants.participant_code, research_phases.code, li.code, li.name_id')
            ->orderBy('participants.participant_code', 'ASC')
            ->orderBy('research_phases.code', 'ASC')
            ->orderBy('li.code', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $evidence = (int) $row['evidence_count'];

            yield [
                'participant_code' => $row['participant_code'],
                'phase_code'       => $row['phase_code'],
                'indicator_code'   => $row['indicator_code'],
                'indicator_name'   => $row['name_id'],
                'evidence_count'   => $evidence,
                'correct_count'    => (int) $row['correct_count'],
                'mastery_ratio'    => $evidence > 0 ? round((int) $row['correct_count'] / $evidence, 4) : 0.0,
                'mean_response_ms' => (int) round((float) ($row['mean_response_ms'] ?? 0)),
            ];
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function demographicRows(array $filters, bool $anonymized): \Generator
    {
        $dimensions = [
            'gender'      => 'participants.gender',
            'class_level' => 'participants.class_level',
            'school'      => 'participants.school_id',
            'province'    => 'participants.province_name_snapshot',
            'phase'       => 'research_phases.code',
        ];

        $schools = $anonymized ? [] : $this->schoolNames();

        foreach ($dimensions as $dimension => $column) {
            $rows = $this->sessionBuilder($filters)
                ->join('session_progress sp', 'sp.session_id = game_sessions.id', 'left')
                ->select($column . ' AS bucket', false)
                ->select('COUNT(DISTINCT game_sessions.participant_id) AS participants', false)
                ->select('COUNT(*) AS sessions', false)
                ->select("SUM(CASE WHEN game_sessions.status = 'completed' THEN 1 ELSE 0 END) AS completed_sessions", false)
                ->select("AVG(CASE WHEN game_sessions.status = 'completed' THEN sp.total_score END) AS mean_total_score", false)
                ->groupBy($column)
                ->orderBy($column, 'ASC')
                ->get()
                ->getResultArray();

            foreach ($rows as $row) {
                $bucket = $row['bucket'];

                if ($dimension === 'school') {
                    $bucket = $anonymized || $bucket === null
                        ? $this->schoolRef($bucket)
                        : ($schools[(int) $bucket] ?? $this->schoolRef($bucket));
                }

                $sessions  = (int) $row['sessions'];
                $completed = (int) $row['completed_sessions'];

                yield [
                    'dimension'          => $dimension,
                    'group'              => $bucket === null || $bucket === '' ? '(kosong)' : (string) $bucket,
                    'participants'       => (int) $row['participants'],
                    'sessions'           => $sessions,
                    'completed_sessions' => $completed,
                    'completion_rate'    => $sessions > 0 ? round($completed / $sessions, 4) : 0.0,
                    'mean_total_score'   => $row['mean_total_score'] === null ? null : round((float) $row['mean_total_score'], 2),
                ];
            }
        }
    }

    /** @return \Generator<array<string, mixed>> */
    private function feedbackRows(array $filters): \Generator
    {
        $query = fn (): BaseBuilder => $this->sessionBuilder($filters)
            ->join('participant_feedback pf', 'pf.session_id = game_sessions.id')
            ->select('pf.id AS _id, pf.rating, pf.liked_most, pf.hardest_part, pf.new_learning', false)
            ->select('pf.suggestion, pf.submitted_at, game_sessions.session_code', false)
            ->select('participants.participant_code, research_phases.code AS phase_code', false);

        foreach ($this->chunked($query, 'pf.id') as $row) {
            yield [
                'participant_code' => $row['participant_code'],
                'session_code'     => $row['session_code'],
                'phase_code'       => $row['phase_code'],
                'rating'           => (int) $row['rating'],
                'liked_most'       => $row['liked_most'],
                'hardest_part'     => $row['hardest_part'],
                'new_learning'     => $row['new_learning'],
                'suggestion'       => $row['suggestion'],
                'submitted_at'     => $row['submitted_at'],
            ];
        }
    }

    // ------------------------------------------------------------ builder

    /** Sesi + peserta + fase, terfilter. Filter wilayah tidak berlaku pada sesi. */
    private function sessionBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('game_sessions')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters, ['level_id', 'node_id']);
    }

    private function attemptBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('challenge_attempts')
            ->join('game_sessions', 'game_sessions.id = challenge_attempts.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->join('challenge_nodes', 'challenge_nodes.id = challenge_attempts.challenge_node_id')
            ->where('participants.deleted_at', null);

        return apply_research_filters($builder, $filters);
    }

    /** Raw event yang belum di-soft-delete; filter wilayah memakai kolom level_id event. */
    private function eventBuilder(array $filters): BaseBuilder
    {
        $builder = $this->db()->table('game_event_logs')
            ->join('game_sessions', 'game_sessions.id = game_event_logs.session_id')
            ->join('participants', 'participants.id = game_sessions.participant_id')
            ->join('research_phases', 'research_phases.id = game_sessions.phase_id')
            ->where('participants.deleted_at', null)
            ->where('game_event_logs.deleted_at', null);

        if (isset($filters['level_id'])) {
            $builder->where('game_event_logs.level_id', (int) $filters['level_id']);
        }

        return apply_research_filters($builder, $filters, ['level_id', 'node_id']);
    }

    /** Subquery id peserta yang punya sesi dalam cakupan filter. */
    private function sessionFilterSubquery(array $filters): \Closure
    {
        return static function (BaseBuilder $sub) use ($filters): BaseBuilder {
            $sub->select('game_sessions.participant_id')
                ->from('game_sessions')
                ->join('participants', 'participants.id = game_sessions.participant_id')
                ->join('research_phases', 'research_phases.id = game_sessions.phase_id');

            return apply_research_filters($sub, $filters, ['level_id', 'node_id']);
        };
    }

    /**
     * Iterasi keyset per potongan: memori tetap kecil walau tabelnya besar.
     * Setiap builder wajib memilih kolom id sebagai `_id`.
     *
     * @param \Closure(): BaseBuilder $query
     *
     * @return \Generator<array<string, mixed>>
     */
    private function chunked(\Closure $query, string $idColumn): \Generator
    {
        foreach ($this->chunkedBatches($query, $idColumn) as $batch) {
            yield from $batch;
        }
    }

    /**
     * @param \Closure(): BaseBuilder $query
     *
     * @return \Generator<list<array<string, mixed>>>
     */
    private function chunkedBatches(\Closure $query, string $idColumn): \Generator
    {
        $lastId = 0;

        do {
            $rows = $query()
                ->where($idColumn . ' >', $lastId)
                ->orderBy($idColumn, 'ASC')
                ->limit(self::CHUNK)
                ->get()
                ->getResultArray();

            if ($rows !== []) {
                $lastId = (int) $rows[array_key_last($rows)]['_id'];

                yield $rows;
            }
        } while (count($rows) === self::CHUNK);
    }

    // --------------------------------------------------------------- peta

    /** @return array<int, array{code: string, sequence: int}> */
    private function levelMap(): array
    {
        return $this->maps['levels'] ??= array_column(array_map(
            static fn (array $r): array => ['id' => (int) $r['id'], 'code' => (string) $r['code'], 'sequence' => (int) $r['sequence']],
            $this->db()->table('levels')->select('id, code, sequence')->orderBy('sequence', 'ASC')->get()->getResultArray(),
        ), null, 'id');
    }

    /** @return array<int, array{level_id: int, sequence: int}> */
    private function nodeMap(): array
    {
        return $this->maps['nodes'] ??= array_column(array_map(
            static fn (array $r): array => ['id' => (int) $r['id'], 'level_id' => (int) $r['level_id'], 'sequence' => (int) $r['sequence']],
            $this->db()->table('challenge_nodes')->select('id, level_id, sequence')->get()->getResultArray(),
        ), null, 'id');
    }

    /** @return array<int, int> level_id → jumlah node aktif */
    private function activeNodeCounts(): array
    {
        $rows = $this->db()->table('challenge_nodes')
            ->select('level_id, COUNT(*) AS total')
            ->where('is_active', 1)
            ->groupBy('level_id')
            ->get()
            ->getResultArray();

        return array_map('intval', array_column($rows, 'total', 'level_id'));
    }

    /** @return array<int, array{item_key: string, interaction_type: string, indicator: ?string, answer_key: ?string}> */
    private function itemMap(): array
    {
        if (isset($this->maps['items'])) {
            return $this->maps['items'];
        }

        $rows = $this->db()->table('challenge_items ci')
            ->select('ci.id, ci.item_key, ci.interaction_type, ci.answer_key_json')
            ->select('COALESCE(li.code, lin.code) AS indicator', false)
            ->join('challenge_nodes cn', 'cn.id = ci.challenge_node_id')
            ->join('learning_indicators li', 'li.id = ci.indicator_id', 'left')
            ->join('learning_indicators lin', 'lin.id = cn.indicator_id', 'left')
            ->get()
            ->getResultArray();

        $out = [];

        foreach ($rows as $row) {
            $out[(int) $row['id']] = [
                'item_key'         => (string) $row['item_key'],
                'interaction_type' => (string) $row['interaction_type'],
                'indicator'        => $row['indicator'],
                'answer_key'       => $row['answer_key_json'],
            ];
        }

        return $this->maps['items'] = $out;
    }

    /** @return array<int, array<string, mixed>> */
    private function releaseMap(): array
    {
        return $this->maps['releases'] ??= array_column(
            $this->db()->table('game_releases')->select('id, release_code, content_version, scoring_version')->get()->getResultArray(),
            null,
            'id',
        );
    }

    /** @return array<int, string> */
    private function studyMap(): array
    {
        return $this->maps['studies'] ??= array_column(
            $this->db()->table('research_studies')->select('id, code')->get()->getResultArray(),
            'code',
            'id',
        );
    }

    /** @return array<int, string> */
    private function schoolNames(): array
    {
        return $this->maps['schools'] ??= array_column(
            $this->db()->table('schools')->select('id, name')->get()->getResultArray(),
            'name',
            'id',
        );
    }

    /** Kode samaran sekolah: stabil, tanpa nama. */
    private function schoolRef(mixed $schoolId): ?string
    {
        return $schoolId === null || (int) $schoolId <= 0 ? null : sprintf('SCH-%06d', (int) $schoolId);
    }

    private function intOrNull(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    // -------------------------------------------------------------- berkas

    /** writable/exports/{id}-{timestamp}.{ext} — di luar public/. */
    private function targetPath(int $exportId, string $format): string
    {
        return WRITEPATH . 'exports' . DIRECTORY_SEPARATOR . $exportId . '-' . date('Ymd-His') . '.' . $format;
    }

    /**
     * Membuang berkas export kedaluwarsa. Dipakai RetentionService.
     *
     * @return list<int> id export yang berkasnya dibuang
     */
    public function purgeExpired(?int $staffId = null): array
    {
        $exports = model(DataExportModel::class);
        $removed = [];

        foreach ($exports->expired() as $export) {
            $path = (string) ($export['file_path'] ?? '');

            if ($path === '') {
                continue;   // berkasnya sudah dibuang pada retensi sebelumnya
            }

            if (is_file($path) && $this->isInsideExportDir($path)) {
                unlink($path);
            }

            // `status` tetap `done`: daftar statusnya ditetapkan skema
            // (queued|running|done|failed). Yang hilang adalah berkasnya.
            $exports->update((int) $export['id'], [
                'file_path'     => null,
                'error_message' => 'Berkas kedaluwarsa dan sudah dihapus.',
            ]);

            $removed[] = (int) $export['id'];
        }

        if ($removed !== []) {
            model(AuditLogModel::class)->record('export_purge', [
                'staff_user_id' => $staffId,
                'target_type'   => 'data_export',
                'metadata'      => ['export_ids' => $removed],
            ]);
        }

        return $removed;
    }

    /** Jalur berkas harus berada di writable/exports/ — pertahanan terhadap file_path yang rusak. */
    public function isInsideExportDir(string $path): bool
    {
        $dir  = realpath(WRITEPATH . 'exports');
        $real = realpath($path);

        return $dir !== false && $real !== false && str_starts_with($real, $dir . DIRECTORY_SEPARATOR);
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
