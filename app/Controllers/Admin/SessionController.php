<?php

namespace App\Controllers\Admin;

use App\Libraries\AdminTable;
use App\Models\GameSessionModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Sesi bermain (Dok 3 §8.3): tabel sesi, dan detail berisi ringkasan,
 * pengerjaan di sesi itu, serta linimasa event lengkap (urutan, waktu klien,
 * waktu server, event, layar/tantangan/soal, payload JSON).
 */
class SessionController extends BaseAdminController
{
    public function index(): string|ResponseInterface
    {
        $filters = $this->scopedFilters();
        $columns = self::listColumns();
        $builder = $this->listBuilder($filters);
        $sorts   = self::sortable($columns);

        if ($this->wantsCsv()) {
            $sort = AdminTable::validSort((string) $this->request->getGet('sort'), $sorts) ?? '-started_at';

            return $this->csvResponse('sesi', static fn ($handle) => AdminTable::streamCsv($builder, $columns, $handle, $sort, 'gs.id'));
        }

        $params = AdminTable::params($this->request, $sorts, '-started_at');

        return $this->panel('admin/sessions/index', lang('Admin.menuSessions'), [
            'filters' => $filters,
            'table'   => AdminTable::page($builder, $columns, $params['page'], $params['per_page'], $params['sort'], 'gs.id'),
        ]);
    }

    public function show(int $sessionId): string|ResponseInterface
    {
        $session = $this->requireInScope($sessionId);
        $columns = self::timelineColumns();
        $builder = $this->timelineBuilder($sessionId, trim((string) ($this->request->getGet('type') ?? '')));
        $sorts   = self::sortable($columns);

        if ($this->wantsCsv()) {
            $sort = AdminTable::validSort((string) $this->request->getGet('sort'), $sorts) ?? 'sequence_no';

            return $this->csvResponse('linimasa-' . $sessionId, static fn ($handle) => AdminTable::streamCsv($builder, $columns, $handle, $sort, 'e.id'));
        }

        $params   = AdminTable::params($this->request, $sorts, 'sequence_no');
        $timeline = AdminTable::page($builder, $columns, $params['page'], $params['per_page'], $params['sort'], 'e.id');
        $refs     = $this->nodeRefs();

        foreach ($timeline['rows'] as &$row) {
            $row['place'] = self::place($row, $refs);
        }
        unset($row);

        $participant = model(ParticipantModel::class)->find($session->participant_id);

        return $this->panel('admin/sessions/show', 'Sesi ' . $session->session_code, [
            'session'     => $session->toArray(),
            'participant' => $participant === null ? null : ['id' => $participant->id, 'participant_code' => $participant->participant_code],
            'progress'    => db_connect()->table('session_progress')->where('session_id', $sessionId)->get()->getRowArray(),
            'attempts'    => $this->attemptsOf($sessionId, $refs),
            'timeline'    => $timeline,
            'types'       => $this->eventTypes($sessionId),
            'type'        => trim((string) ($this->request->getGet('type') ?? '')),
            'breadcrumb'  => [(string) $session->session_code => null],
        ]);
    }

    /** Alamat lama linimasa: kini bagian dari detail sesi. */
    public function timeline(int $sessionId): RedirectResponse
    {
        $this->requireInScope($sessionId);

        return redirect()->to(site_url('admin/sesi/' . $sessionId) . '#linimasa');
    }

    /** @return list<array<string, mixed>> */
    public static function listColumns(): array
    {
        return [
            ['key' => 'session_code', 'label' => 'Kode sesi', 'type' => 'code', 'sort' => 'gs.session_code'],
            ['key' => 'participant_code', 'label' => 'Kode siswa', 'type' => 'code', 'sort' => 'p.participant_code'],
            ['key' => 'school_name', 'label' => 'Sekolah', 'type' => 'text', 'sort' => 'school_name'],
            ['key' => 'study_code', 'label' => 'Penelitian', 'type' => 'code', 'sort' => 'rs.code'],
            ['key' => 'locale', 'label' => 'Bahasa', 'type' => 'label:locale', 'sort' => 'gs.locale'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status', 'sort' => 'gs.status'],
            ['key' => 'started_at', 'label' => 'Mulai', 'type' => 'datetime', 'sort' => 'gs.started_at'],
            ['key' => 'last_active_at', 'label' => 'Aktif terakhir', 'type' => 'datetime', 'sort' => 'gs.last_active_at'],
            ['key' => 'duration_ms', 'label' => 'Durasi (m:dd)', 'type' => 'ms', 'sort' => 'gs.duration_ms'],
            ['key' => 'device_type', 'label' => 'Perangkat', 'type' => 'label:device', 'sort' => 'gs.device_type'],
            ['key' => 'completed_nodes', 'label' => 'Tantangan selesai', 'type' => 'int', 'sort' => 'sp.completed_nodes'],
            ['key' => 'total_stars', 'label' => 'Bintang', 'type' => 'int', 'sort' => 'sp.total_stars'],
        ];
    }

    /** @return list<array<string, mixed>> */
    public static function timelineColumns(): array
    {
        return [
            ['key' => 'sequence_no', 'label' => 'Urutan', 'type' => 'int', 'sort' => 'e.sequence_no'],
            ['key' => 'occurred_at', 'label' => 'Waktu klien', 'type' => 'datetime', 'sort' => 'e.occurred_at'],
            ['key' => 'server_received_at', 'label' => 'Waktu server', 'type' => 'datetime', 'sort' => 'e.server_received_at'],
            ['key' => 'event_type', 'label' => 'Event', 'type' => 'code', 'sort' => 'e.event_type'],
            ['key' => 'place', 'label' => 'Layar/tantangan/soal', 'type' => 'code'],
            ['key' => 'payload_json', 'label' => 'Payload (JSON)', 'type' => 'json'],
        ];
    }

    /** @param array<string, mixed> $filters sudah dipaksa ke cakupan sekolah */
    private function listBuilder(array $filters): BaseBuilder
    {
        $builder = db_connect()->table('game_sessions gs')
            ->select('gs.id, gs.session_code, gs.participant_id, p.participant_code, COALESCE(s.name, p.school_name_snapshot) AS school_name', false)
            ->select('rs.code AS study_code, gs.locale, gs.status, gs.started_at, gs.last_active_at, gs.duration_ms, gs.device_type', false)
            ->select('sp.completed_nodes, sp.total_stars', false)
            ->join('participants p', 'p.id = gs.participant_id')
            ->join('schools s', 's.id = p.school_id', 'left')
            ->join('research_studies rs', 'rs.id = gs.study_id', 'left')
            ->join('session_progress sp', 'sp.session_id = gs.id', 'left')
            ->where('p.deleted_at', null);

        foreach (['school_id' => 'p.school_id', 'study_id' => 'gs.study_id', 'locale' => 'gs.locale'] as $key => $column) {
            if (isset($filters[$key])) {
                $builder->where($column, $filters[$key]);
            }
        }

        if (isset($filters['participant_code'])) {
            $builder->like('p.participant_code', (string) $filters['participant_code']);
        }

        if (isset($filters['date_from'])) {
            $builder->where('gs.started_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $builder->where('gs.started_at <=', $filters['date_to'] . ' 23:59:59');
        }

        return $builder;
    }

    private function timelineBuilder(int $sessionId, string $type): BaseBuilder
    {
        $builder = db_connect()->table('game_event_logs e')
            ->select('e.id, e.sequence_no, e.occurred_at, e.server_received_at, e.event_type, e.challenge_node_id, e.challenge_item_id, e.payload_json')
            ->select('ci.item_key')
            ->join('challenge_items ci', 'ci.id = e.challenge_item_id', 'left')
            ->where('e.session_id', $sessionId)
            ->where('e.deleted_at', null);

        if ($type !== '') {
            $builder->where('e.event_type', $type);
        }

        return $builder;
    }

    /** @return array<string, string> kode event → label */
    private function eventTypes(int $sessionId): array
    {
        $types = array_column(db_connect()->table('game_event_logs')
            ->select('event_type')->distinct()
            ->where('session_id', $sessionId)->where('deleted_at', null)
            ->orderBy('event_type', 'ASC')
            ->get()->getResultArray(), 'event_type');

        return array_combine($types, array_map(static fn (string $t): string => admin_label('event', $t) . ' (' . $t . ')', $types)) ?: [];
    }

    /**
     * Pengerjaan tantangan di sesi ini.
     *
     * @param array<int, string> $refs
     *
     * @return array<string, mixed> bentuk tabel data-table
     */
    private function attemptsOf(int $sessionId, array $refs): array
    {
        $rows = db_connect()->table('challenge_attempts')
            ->where('session_id', $sessionId)
            ->orderBy('started_at', 'ASC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['node_ref'] = $refs[(int) $row['challenge_node_id']] ?? '#' . $row['challenge_node_id'];
            $row['variant']  = strtoupper((string) $row['variant_group']);
        }
        unset($row);

        $columns = [
            ['key' => 'id', 'label' => 'ID pengerjaan', 'type' => 'int'],
            ['key' => 'node_ref', 'label' => 'Tantangan', 'type' => 'code'],
            ['key' => 'variant', 'label' => 'Variasi', 'type' => 'code'],
            ['key' => 'attempt_no', 'label' => 'Ke-', 'type' => 'int'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ['key' => 'completion_reason', 'label' => 'Alasan selesai', 'type' => 'label:completion'],
            ['key' => 'first_pass_correct', 'label' => 'Berhasil pada Percobaan Pertama', 'type' => 'int'],
            ['key' => 'final_correct', 'label' => 'Benar akhir', 'type' => 'int'],
            ['key' => 'scorable_items', 'label' => 'Soal dinilai', 'type' => 'int'],
            ['key' => 'check_count', 'label' => 'Cek', 'type' => 'int'],
            ['key' => 'hint_count', 'label' => 'Petunjuk', 'type' => 'int'],
            ['key' => 'stars', 'label' => 'Bintang', 'type' => 'int'],
            ['key' => 'started_at', 'label' => 'Mulai', 'type' => 'datetime'],
            ['key' => 'completed_at', 'label' => 'Selesai', 'type' => 'datetime'],
            ['key' => 'duration_ms', 'label' => 'Durasi (m:dd)', 'type' => 'ms'],
        ];

        return [
            'rows' => $rows, 'total' => count($rows), 'page' => 1, 'per_page' => max(1, count($rows)), 'sort' => null,
            'columns' => array_map(static fn (array $c): array => $c + ['sortable' => false], $columns),
        ];
    }

    /**
     * Layar/tantangan/soal satu event: `tmg-2 · tmg-2-a-03`, atau layar dari payload.
     *
     * @param array<string, mixed> $row
     * @param array<int, string>   $refs
     */
    private static function place(array $row, array $refs): ?string
    {
        if ($row['challenge_node_id'] !== null) {
            $ref = $refs[(int) $row['challenge_node_id']] ?? '#' . $row['challenge_node_id'];

            return $row['item_key'] !== null ? $ref . ' · ' . $row['item_key'] : $ref;
        }

        $payload = json_decode((string) $row['payload_json'], true);

        return is_array($payload) && isset($payload['screen']) && is_string($payload['screen']) ? $payload['screen'] : null;
    }

    /** @param list<array<string, mixed>> $columns */
    private static function sortable(array $columns): array
    {
        return array_column(array_filter($columns, static fn (array $c): bool => ! empty($c['sort'])), 'key');
    }

    /** Sesi dalam cakupan pemanggil, atau 404. */
    private function requireInScope(int $sessionId): \App\Entities\GameSession
    {
        $session = model(GameSessionModel::class)->find($sessionId);

        if ($session === null) {
            throw PageNotFoundException::forPageNotFound("Sesi {$sessionId} tidak ditemukan.");
        }

        $scope = $this->schoolScope();

        if ($scope !== null) {
            $participant = model(ParticipantModel::class)->find($session->participant_id);

            if ($participant === null || (int) $participant->school_id !== $scope) {
                throw PageNotFoundException::forPageNotFound("Sesi {$sessionId} tidak ditemukan.");
            }
        }

        return $session;
    }
}
