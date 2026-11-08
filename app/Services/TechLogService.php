<?php

namespace App\Services;

use App\Libraries\EventCatalog;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

/**
 * The admin's tech log (research revision 10, part 3: 29 §5.6, K15),
 * `/admin/log-teknis`: device rows, technical events, and the technical
 * columns of game sessions, to look into device and app problems. Never
 * answers, scores, tries, help, navigation, or research events.
 *
 * Students show as codes; the only search is by participant code. Same
 * table contract as RawDataService::page(), so the admin table component
 * and BaseAdminController::rawPage() serve both.
 */
class TechLogService
{
    public const TABS = ['perangkat', 'event', 'sesi'];

    public const PER_PAGE = RawDataService::PER_PAGE;

    /** tab => column => [label, type, sort expression or null] */
    private const COLUMNS = [
        'perangkat' => [
            'session_code'     => ['Kode sesi', 'code', 'game_sessions.session_code'],
            'participant_code' => ['Kode siswa', 'code', 'participants.participant_code'],
            'via'              => ['Tercatat lewat', 'code', 'device_logs.via'],
            'created_at'       => ['Dicatat', 'datetime', 'device_logs.created_at'],
            'device_type'      => ['Jenis perangkat', 'code', 'device_logs.device_type'],
            'os_name'          => ['Sistem operasi', 'text', 'device_logs.os_name'],
            'os_version'       => ['Versi SO', 'code', 'device_logs.os_version'],
            'browser_name'     => ['Peramban', 'text', 'device_logs.browser_name'],
            'browser_version'  => ['Versi peramban', 'code', 'device_logs.browser_version'],
            'browser_engine'   => ['Mesin peramban', 'text', 'device_logs.browser_engine'],
            'is_bot'           => ['Bot', 'bool', 'device_logs.is_bot'],
            'screen_w'         => ['Lebar layar (px)', 'int', 'device_logs.screen_w'],
            'screen_h'         => ['Tinggi layar (px)', 'int', 'device_logs.screen_h'],
            'viewport_w'       => ['Lebar jendela (px)', 'int', 'device_logs.viewport_w'],
            'viewport_h'       => ['Tinggi jendela (px)', 'int', 'device_logs.viewport_h'],
            'orientation'      => ['Orientasi', 'text', 'device_logs.orientation'],
            'is_standalone'    => ['Aplikasi terpasang', 'bool', 'device_logs.is_standalone'],
            'corrected'        => ['Koreksi', 'code', 'device_logs.corrected'],
            'detector'         => ['Pengurai', 'text', 'device_logs.detector'],
        ],
        'event' => [
            'session_code'       => ['Kode sesi', 'code', 'game_sessions.session_code'],
            'participant_code'   => ['Kode siswa', 'code', 'participants.participant_code'],
            'event_type'         => ['Jenis event', 'code', 'game_event_logs.event_type'],
            'sequence_no'        => ['Urutan event', 'int', 'game_event_logs.sequence_no'],
            'occurred_at'        => ['Waktu kejadian (jam perangkat)', 'datetime', 'game_event_logs.occurred_at'],
            'server_received_at' => ['Diterima server', 'datetime', 'game_event_logs.server_received_at'],
            'clock_offset_ms'    => ['Selisih jam (mdtk)', 'ms', 'game_event_logs.clock_offset_ms'],
            'payload'            => ['Rincian (JSON)', 'json', null],
        ],
        'sesi' => [
            'session_code'       => ['Kode sesi', 'code', 'game_sessions.session_code'],
            'participant_code'   => ['Kode siswa', 'code', 'participants.participant_code'],
            'status'             => ['Status', 'code', 'game_sessions.status'],
            'started_at'         => ['Mulai', 'datetime', 'game_sessions.started_at'],
            'last_active_at'     => ['Terakhir aktif', 'datetime', 'game_sessions.last_active_at'],
            'os_name'            => ['Sistem operasi', 'text', 'game_sessions.os_name'],
            'browser_name'       => ['Peramban', 'text', 'game_sessions.browser_name'],
            'is_touch'           => ['Layar sentuh', 'bool', 'game_sessions.is_touch'],
            'app_client_version' => ['Versi aplikasi & paket', 'code', 'game_sessions.app_client_version'],
            'package_code'       => ['Kode paket aset', 'code', 'game_sessions.package_code'],
            'package_mode'       => ['Mode paket', 'code', 'game_sessions.package_mode'],
            'release_code'       => ['Rilis', 'code', 'game_releases.release_code'],
        ],
    ];

    private const KEYS = ['perangkat' => 'device_logs.id', 'event' => 'game_event_logs.id', 'sesi' => 'game_sessions.id'];

    private const DEFAULT_SORT = ['perangkat' => '-created_at', 'event' => '-occurred_at', 'sesi' => '-started_at'];

    /**
     * One page of a tab (RawDataService::page() contract).
     *
     * @param array<string, mixed> $filters participant_code only
     *
     * @return array<string, mixed>
     */
    public function page(string $tab, array $filters, int $page, int $perPage, ?string $sort, bool $readable = false): array
    {
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : self::PER_PAGE[0];
        $total   = $this->query($tab, $filters)->countAllResults();
        $page    = min(max(1, $page), max(1, (int) ceil($total / $perPage)));
        $sort    = $this->resolveSort($tab, $sort);

        $builder   = $this->query($tab, $filters);
        $direction = str_starts_with($sort, '-') ? 'DESC' : 'ASC';
        $builder->orderBy(self::COLUMNS[$tab][ltrim($sort, '-')][2], $direction)->orderBy(self::KEYS[$tab], $direction);

        $rows = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return [
            'rows'     => array_map(fn (array $row): array => $this->shape($tab, $row), $rows),
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'sort'     => $sort,
            'columns'  => $this->columnSpecs($tab),
        ];
    }

    /**
     * Every row of a tab as CSV (UTF-8 with BOM, codes as headings).
     *
     * @param array<string, mixed> $filters
     * @param resource             $handle
     */
    public function streamCsv(string $tab, array $filters, $handle): int
    {
        $columns = array_keys(self::COLUMNS[$tab] ?? throw new \InvalidArgumentException("Unknown tab '{$tab}'."));

        fwrite($handle, "\xEF\xBB\xBF");
        RawDataService::writeCsvLine($handle, $columns);

        $count = 0;
        $last  = null;

        do {
            $builder = $this->query($tab, $filters);

            if ($last !== null) {
                $builder->where(self::KEYS[$tab] . ' >', $last);
            }

            $batch = $builder->orderBy(self::KEYS[$tab], 'ASC')->limit(RawDataService::CHUNK)->get()->getResultArray();

            foreach ($batch as $row) {
                $shaped = $this->shape($tab, $row);
                RawDataService::writeCsvLine($handle, array_map(static fn (string $c): mixed => $shaped[$c] ?? null, $columns));
                $count++;
                $last = (int) $row['_key'];
            }
        } while (count($batch) === RawDataService::CHUNK);

        return $count;
    }

    /** @return list<string> */
    public function sortable(string $tab): array
    {
        return array_keys(array_filter(self::COLUMNS[$tab] ?? [], static fn (array $c): bool => $c[2] !== null));
    }

    /** @return list<array{key: string, label: string, type: string}> */
    public function columnSpecs(string $tab): array
    {
        $out = [];

        foreach (self::COLUMNS[$tab] ?? throw new \InvalidArgumentException("Unknown tab '{$tab}'.") as $key => [$label, $type]) {
            $out[] = ['key' => $key, 'label' => $label, 'type' => $type];
        }

        return $out;
    }

    /**
     * Technical numbers for the admin's dashboard card (27 §8): active
     * sessions, events lost on devices in the last 7 days, OS and browser.
     *
     * @return array{active_sessions: int, dropped_7d: int, os_browser: list<array{os: string, browser: string, n: int}>}
     */
    public function summary(): array
    {
        $db      = $this->db();
        $dropped = 0;

        $rows = $db->table('game_event_logs')->select('payload_json')
            ->where('event_type', 'events_dropped')
            ->where('occurred_at >=', date('Y-m-d H:i:s', time() - 7 * 86400))
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $dropped += (int) ((json_decode((string) $row['payload_json'], true) ?: [])['dropped'] ?? 0);
        }

        $spread = $db->table('game_sessions')->select('os_name, browser_name')->selectCount('id', 'n')
            ->groupBy('os_name, browser_name')->orderBy('n', 'DESC')->limit(5)->get()->getResultArray();

        return [
            'active_sessions' => $db->table('game_sessions')->where('status', 'active')->countAllResults(),
            'dropped_7d'      => $dropped,
            'os_browser'      => array_map(static fn (array $r): array => [
                'os' => (string) ($r['os_name'] ?? '—'), 'browser' => (string) ($r['browser_name'] ?? '—'), 'n' => (int) $r['n'],
            ], $spread),
        ];
    }

    /** @param array<string, mixed> $filters */
    private function query(string $tab, array $filters): BaseBuilder
    {
        $db = $this->db();

        $builder = match ($tab) {
            'perangkat' => $db->table('device_logs')
                ->join('game_sessions', 'game_sessions.id = device_logs.session_id')
                ->select('device_logs.id AS _key, game_sessions.session_code, device_logs.via, device_logs.created_at')
                ->select('device_logs.device_type, device_logs.os_name, device_logs.os_version, device_logs.browser_name')
                ->select('device_logs.browser_version, device_logs.browser_engine, device_logs.is_bot, device_logs.screen_w')
                ->select('device_logs.screen_h, device_logs.viewport_w, device_logs.viewport_h, device_logs.orientation')
                ->select('device_logs.is_standalone, device_logs.corrected, device_logs.detector'),
            'event' => $db->table('game_event_logs')
                ->join('game_sessions', 'game_sessions.id = game_event_logs.session_id')
                ->select('game_event_logs.id AS _key, game_sessions.session_code, game_event_logs.event_type')
                ->select('game_event_logs.sequence_no, game_event_logs.occurred_at, game_event_logs.server_received_at')
                ->select('game_event_logs.clock_offset_ms, game_event_logs.payload_json')
                ->whereIn('game_event_logs.event_type', EventCatalog::ADMIN)
                ->where('game_event_logs.deleted_at', null),
            'sesi' => $db->table('game_sessions')
                ->join('game_releases', 'game_releases.id = game_sessions.release_id', 'left')
                ->select('game_sessions.id AS _key, game_sessions.session_code, game_sessions.status, game_sessions.started_at')
                ->select('game_sessions.last_active_at, game_sessions.os_name, game_sessions.browser_name, game_sessions.is_touch')
                ->select('game_sessions.app_client_version, game_sessions.package_code, game_sessions.package_mode, game_releases.release_code'),
            default => throw new \InvalidArgumentException("Unknown tab '{$tab}'."),
        };

        $builder->join('participants', 'participants.id = game_sessions.participant_id')
            ->select('participants.participant_code')
            ->where('participants.deleted_at', null);

        $code = trim((string) ($filters['participant_code'] ?? ''));

        if ($code !== '') {
            $builder->where('participants.participant_code', $code);
        }

        return $builder;
    }

    /** @return array<string, int|string|null> */
    private function shape(string $tab, array $row): array
    {
        $out = [];

        foreach (self::COLUMNS[$tab] as $key => [, $type]) {
            $value = $key === 'payload' ? ($row['payload_json'] ?? null) : ($row[$key] ?? null);

            $out[$key] = match (true) {
                $value === null || $value === ''           => null,
                $type === 'datetime'                       => RawDataService::isoTime($value),
                in_array($type, ['int', 'ms', 'bool'], true) => (int) $value,
                default                                    => (string) $value,
            };
        }

        return $out;
    }

    private function resolveSort(string $tab, ?string $sort): string
    {
        $sort = trim((string) $sort);

        return $sort !== '' && in_array(ltrim($sort, '-'), $this->sortable($tab), true) ? $sort : self::DEFAULT_SORT[$tab];
    }

    private function db(): BaseConnection
    {
        return db_connect();
    }
}
