<?php

namespace App\Controllers\Admin;

use App\Entities\Participant;
use App\Libraries\AdminTable;
use App\Models\DeviceLogModel;
use App\Models\ParticipantModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Siswa (Dok 3 §8.3): daftar ber-filter, detail bertab, dan reset sandi.
 *
 * Guru hanya melihat dan mereset siswa di sekolahnya; siswa di luar
 * cakupan dibalas 404, bukan 403, agar keberadaannya tidak terkonfirmasi.
 * Kolom identitas (nama, nama pengguna) tidak pernah diambil untuk guru.
 *
 * Siswa dikirim ke view sebagai array aman (Participant::toSafeArray() +
 * kolom tampilan), tidak pernah sebagai entity — entity ikut membawa
 * `password_hash` (aturan 11, 05_VIEW_UI.md).
 */
class ParticipantController extends BaseAdminController
{
    /** Tab detail siswa → judul. */
    public const TABS = [
        'profil'     => 'Profil',
        'sesi'       => 'Sesi & perangkat',
        'progres'    => 'Progres',
        'pengerjaan' => 'Pengerjaan',
        'jawaban'    => 'Jawaban',
        'navigasi'   => 'Navigasi',
        'bantuan'    => 'Bantuan',
        'masukan'    => 'Kritik & saran',
    ];

    /** Tab yang isinya lembar data mentah (RawDataService) satu siswa. */
    private const RAW_TABS = ['pengerjaan', 'jawaban', 'navigasi', 'bantuan'];

    public function index(): string|ResponseInterface
    {
        $filters = $this->readFilters();
        $columns = AdminTable::visibleColumns(self::listColumns(), $this->isAdmin());
        $builder = $this->listBuilder($filters);

        if ($this->wantsCsv()) {
            $sort = AdminTable::validSort((string) $this->request->getGet('sort'), $this->sortable($columns)) ?? '-created_at';

            return $this->tableCsv('siswa', $builder, $columns, $sort, 'p.id', $this->scopedFilters($filters));
        }

        $params = AdminTable::params($this->request, $this->sortable($columns), '-created_at');

        return $this->panel('admin/participants/index', lang('Admin.menuParticipants'), [
            'filters' => $filters,
            'table'   => AdminTable::page($builder, $columns, $params['page'], $params['per_page'], $params['sort'], 'p.id'),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    public function show(int $participantId): string|ResponseInterface
    {
        $participant = $this->requireInScope($participantId);
        $tab         = (string) ($this->request->getGet('tab') ?? 'profil');
        $tab         = array_key_exists($tab, self::TABS) ? $tab : 'profil';
        $data        = [
            'participant' => $this->viewRow($participant),
            'tab'         => $tab,
            'tabs'        => self::TABS,
            'isAdmin'     => $this->isAdmin(),
            'breadcrumb'  => [$participant->participant_code => null],
        ];

        if (in_array($tab, self::RAW_TABS, true)) {
            $raw = $this->participantRaw($participantId, $tab);

            if ($raw instanceof ResponseInterface) {
                return $raw;
            }

            $data += $raw;
        } else {
            $data += match ($tab) {
                'sesi'    => ['sessions' => $this->sessionsOf($participantId), 'devices' => model(DeviceLogModel::class)->forParticipant($participantId)],
                'progres' => ['progress' => $this->progressOf($participantId)],
                'masukan' => ['feedback' => $this->feedbackOf($participantId)],
                default   => [],
            };
        }

        return $this->panel('admin/participants/show', $participant->participant_code, $data);
    }

    /**
     * Reset sandi siswa. Sandi sementara ditampilkan SEKALI pada respons ini:
     * tidak disimpan, tidak dikirim lewat flash, dan tidak masuk log. Respons
     * tidak boleh di-cache browser maupun proxy.
     */
    public function resetPassword(int $participantId): string|RedirectResponse
    {
        $participant = $this->requireInScope($participantId);

        if ((string) $this->request->getPost('confirm') !== 'RESET') {
            return $this->back(
                'admin/peserta/' . $participantId,
                'Ketik RESET (huruf besar) di kotak konfirmasi untuk membuat sandi sementara.',
            );
        }

        $temporary = service('sessionService')->resetPasswordByStaff($participantId, $this->staffId());

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return $this->panel('admin/participants/reset-result', 'Kata sandi sementara', [
            'participant'       => $this->viewRow($participant),
            'temporaryPassword' => $temporary,
            'breadcrumb'        => [$participant->participant_code => 'admin/peserta/' . $participantId, 'Sandi sementara' => null],
        ]);
    }

    /**
     * Kolom daftar siswa (§8.3). `identity` = hanya admin.
     *
     * @return list<array<string, mixed>>
     */
    public static function listColumns(): array
    {
        return [
            ['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code', 'sort' => 'p.participant_code'],
            ['key' => 'display_name', 'label' => 'Nama', 'type' => 'text', 'sort' => 'p.display_name', 'identity' => true],
            ['key' => 'username', 'label' => 'Nama pengguna', 'type' => 'code', 'sort' => 'p.username', 'identity' => true],
            ['key' => 'school_name', 'label' => 'Sekolah', 'type' => 'text', 'sort' => 'school_name'],
            ['key' => 'district_name', 'label' => 'Kab/Kota', 'type' => 'text', 'sort' => 'district_name'],
            ['key' => 'gender', 'label' => 'JK', 'type' => 'label:gender', 'sort' => 'p.gender'],
            ['key' => 'age', 'label' => 'Umur (tahun)', 'type' => 'int', 'sort' => 'p.age'],
            ['key' => 'created_at', 'label' => 'Terdaftar', 'type' => 'datetime', 'sort' => 'p.created_at'],
            ['key' => 'assented_at', 'label' => 'Setuju ikut', 'type' => 'datetime', 'sort' => 'p.assented_at'],
            ['key' => 'completed_nodes', 'label' => 'Tantangan selesai', 'type' => 'int', 'sort' => 'completed_nodes'],
            ['key' => 'last_level', 'label' => 'Wilayah terakhir', 'type' => 'text', 'sort' => 'last_level'],
            ['key' => 'total_stars', 'label' => 'Bintang', 'type' => 'int', 'sort' => 'total_stars'],
            ['key' => 'last_active_at', 'label' => 'Aktif terakhir', 'type' => 'datetime', 'sort' => 'last_active_at'],
        ];
    }

    /**
     * Query daftar siswa dalam cakupan pemanggil. Progres diambil dari sesi
     * terakhir tiap siswa. Filter: cari, sekolah, kab/kota, JK, umur, status
     * progres (belum mulai/berjalan/selesai), tanggal daftar.
     *
     * @param array<string, mixed> $filters
     */
    private function listBuilder(array $filters): BaseBuilder
    {
        $db     = db_connect();
        $scope  = $this->schoolScope();
        $latest = $db->table('game_sessions')->select('MAX(id)', false)->where('participant_id = p.id', null, false)->getCompiledSelect();
        $total  = max(1, count($this->nodeRefs()));

        $builder = $db->table('participants p')
            ->select('p.id, p.participant_code, p.gender, p.age, p.created_at, p.assented_at', false)
            ->select('COALESCE(s.name, p.school_name_snapshot) AS school_name, p.district_name_snapshot AS district_name', false)
            ->select('COALESCE(sp.completed_nodes, 0) AS completed_nodes, sp.total_stars, l.name_id AS last_level, gs.last_active_at', false)
            ->join('schools s', 's.id = p.school_id', 'left')
            ->join($db->prefixTable('game_sessions') . ' gs', "gs.id = ({$latest})", 'left', false)
            ->join('session_progress sp', 'sp.session_id = gs.id', 'left')
            ->join('levels l', 'l.id = sp.current_level_id', 'left')
            ->where('p.deleted_at', null);

        if ($this->isAdmin()) {
            $builder->select('p.display_name, p.username');
        }

        if ($scope !== null) {
            $builder->where('p.school_id', $scope);
        } elseif (isset($filters['school_id'])) {
            $builder->where('p.school_id', (int) $filters['school_id']);
        }

        foreach (['district_code' => 'p.district_code', 'gender' => 'p.gender', 'age' => 'p.age', 'class_level' => 'p.class_level', 'province_code' => 'p.province_code'] as $key => $column) {
            if (isset($filters[$key])) {
                $builder->where($column, $filters[$key]);
            }
        }

        if (isset($filters['date_from'])) {
            $builder->where('p.created_at >=', $filters['date_from'] . ' 00:00:00');
        }

        if (isset($filters['date_to'])) {
            $builder->where('p.created_at <=', $filters['date_to'] . ' 23:59:59');
        }

        match ($filters['progress'] ?? null) {
            'belum'    => $builder->where('COALESCE(sp.completed_nodes, 0) = 0', null, false),
            'berjalan' => $builder->where('sp.completed_nodes >', 0)->where('sp.completed_nodes <', $total),
            'selesai'  => $builder->where('sp.completed_nodes >=', $total),
            default    => null,
        };

        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $builder->groupStart()->like('p.participant_code', $search);

            // Identity columns are admin-only; matching them for a teacher
            // would confirm a username the teacher is not allowed to see.
            if ($this->isAdmin()) {
                $builder->orLike('p.username', $search)->orLike('p.display_name', $search);
            }

            $builder->groupEnd();
        }

        return $builder;
    }

    /** @param list<array<string, mixed>> $columns */
    private function sortable(array $columns): array
    {
        return array_column(array_filter($columns, static fn (array $c): bool => ! empty($c['sort'])), 'key');
    }

    /**
     * Tab data mentah satu siswa lewat RawDataService. Jawaban membawa
     * riwayat percobaan per baris (lembar `percobaan`).
     *
     * @return array<string, mixed>|ResponseInterface
     */
    private function participantRaw(int $participantId, string $tab): array|ResponseInterface
    {
        $raw = $this->rawService();

        if ($raw === null) {
            return ['table' => null];
        }

        $dimension = ['pengerjaan' => 'pengerjaan', 'jawaban' => 'jawaban', 'navigasi' => 'navigasi', 'bantuan' => 'bantuan'][$tab];
        $filters   = $this->scopedFilters(['participant_id' => $participantId]);

        if ($this->wantsCsv()) {
            return $this->rawCsv($dimension, $filters);
        }

        $table  = $this->rawPage($raw, $dimension, $filters);
        $tries  = [];

        if ($tab === 'jawaban') {
            // The whole history, not one page: page() caps at PER_PAGE, which
            // left answers past the first 25 tries without their history.
            $history = $raw->all('percobaan', $filters);

            foreach ($history['rows'] as $row) {
                $tries[($row['attempt_id'] ?? '') . '|' . ($row['item_key'] ?? '')][] = $row;
            }

            $tries['_columns'] = $history['columns'];
        }

        return ['table' => $table, 'tries' => $tries];
    }

    /** Siswa dalam cakupan pemanggil, atau 404. */
    private function requireInScope(int $participantId): Participant
    {
        $participant = model(ParticipantModel::class)->find($participantId);
        $scope       = $this->schoolScope();

        if ($participant === null || $participant->deleted_at !== null || ($scope !== null && (int) $participant->school_id !== $scope)) {
            throw PageNotFoundException::forPageNotFound("Siswa {$participantId} tidak ditemukan.");
        }

        return $participant;
    }

    /**
     * Bentuk tampilan panel: toSafeArray() + snapshot wilayah, metrik literasi
     * keamanan digital, dan waktu penting. Tanpa password_hash & kolom
     * throttle; tanpa nama & nama pengguna untuk guru.
     *
     * @return array<string, mixed>
     */
    private function viewRow(Participant $participant): array
    {
        $row = $participant->toSafeArray() + [
            'province_name'            => $participant->province_name_snapshot,
            'district_name'            => $participant->district_name_snapshot,
            'country_name'             => $participant->country_name_snapshot,
            'pw_first_submit_criteria' => $participant->pw_first_submit_criteria,
            'pw_weak_submit_count'     => $participant->pw_weak_submit_count,
            'password_changed_at'      => $participant->password_changed_at,
            'must_change_password'     => $participant->mustChangePassword(),
            'last_login_at'            => $participant->last_login_at,
            'intro_seen_at'            => $participant->intro_seen_at,
            'assented_at'              => $participant->assented_at,
            'created_at'               => $participant->created_at,
        ];

        if (! $this->isAdmin()) {
            unset($row['username'], $row['display_name']);
        }

        return $row;
    }

    /**
     * Sesi siswa beserta progres, terbaru dulu.
     *
     * @return array<string, mixed> bentuk tabel data-table
     */
    private function sessionsOf(int $participantId): array
    {
        $rows = db_connect()->table('game_sessions gs')
            ->select('gs.id, gs.session_code, gs.status, gs.locale, gs.started_at, gs.last_active_at, gs.ended_at, gs.duration_ms', false)
            ->select('gs.device_type, gs.os_name, gs.browser_name, gs.screen_size, sp.completed_nodes, sp.total_stars', false)
            ->join('session_progress sp', 'sp.session_id = gs.id', 'left')
            ->where('gs.participant_id', $participantId)
            ->orderBy('gs.started_at', 'DESC')
            ->get()
            ->getResultArray();

        return self::staticTable($rows, [
            ['key' => 'session_code', 'label' => 'Kode sesi', 'type' => 'code'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ['key' => 'locale', 'label' => 'Bahasa', 'type' => 'label:locale'],
            ['key' => 'started_at', 'label' => 'Mulai', 'type' => 'datetime'],
            ['key' => 'last_active_at', 'label' => 'Aktif terakhir', 'type' => 'datetime'],
            ['key' => 'duration_ms', 'label' => 'Durasi (m:dd)', 'type' => 'ms'],
            ['key' => 'device_type', 'label' => 'Perangkat', 'type' => 'label:device'],
            ['key' => 'os_name', 'label' => 'Sistem operasi', 'type' => 'text'],
            ['key' => 'browser_name', 'label' => 'Peramban', 'type' => 'text'],
            ['key' => 'screen_size', 'label' => 'Layar', 'type' => 'code'],
            ['key' => 'completed_nodes', 'label' => 'Tantangan selesai', 'type' => 'int'],
            ['key' => 'total_stars', 'label' => 'Bintang', 'type' => 'int'],
        ]);
    }

    /**
     * Progres 12 tantangan (satu baris per node aktif) dari pengerjaan
     * terakhir siswa di node itu: status, variasi, percobaan, Berhasil pada
     * Percobaan Pertama x dari y, bintang, durasi.
     *
     * @return array<string, mixed> bentuk tabel data-table
     */
    private function progressOf(int $participantId): array
    {
        $content  = service('contentRepository');
        $attempts = db_connect()->table('challenge_attempts ca')
            ->select('ca.challenge_node_id, ca.attempt_no, ca.variant_group, ca.status, ca.completion_reason, ca.first_pass_correct, ca.scorable_items, ca.stars, ca.duration_ms, ca.completed_at', false)
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->where('gs.participant_id', $participantId)
            ->orderBy('ca.id', 'ASC')
            ->get()
            ->getResultArray();

        $byNode = [];

        foreach ($attempts as $attempt) {
            $node                = (int) $attempt['challenge_node_id'];
            $attempt['attempts'] = ($byNode[$node]['attempts'] ?? 0) + 1;
            $byNode[$node]       = $attempt;
        }

        $rows = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $last   = $byNode[$node->id] ?? null;
                $rows[] = [
                    'node_ref'     => node_ref((string) $level->code, (int) $node->sequence),
                    'level'        => $level->text('name', 'id'),
                    'title'        => $node->text('title', 'id'),
                    'engine'       => engine_label((string) $node->engine_type),
                    'status'       => $last['status'] ?? 'locked',
                    'variant'      => $last === null ? null : strtoupper((string) $last['variant_group']),
                    'attempts'     => $last['attempts'] ?? 0,
                    'first_pass'   => $last === null ? null : $last['first_pass_correct'] . ' dari ' . $last['scorable_items'],
                    'stars'        => $last['stars'] ?? null,
                    'duration_ms'  => $last['duration_ms'] ?? null,
                    'completed_at' => $last['completed_at'] ?? null,
                ];
            }
        }

        return self::staticTable($rows, [
            ['key' => 'node_ref', 'label' => 'Kode', 'type' => 'code'],
            ['key' => 'level', 'label' => 'Wilayah', 'type' => 'text'],
            ['key' => 'title', 'label' => 'Tantangan', 'type' => 'text'],
            ['key' => 'engine', 'label' => 'Jenis', 'type' => 'text'],
            ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
            ['key' => 'variant', 'label' => 'Variasi', 'type' => 'code'],
            ['key' => 'attempts', 'label' => 'Pengerjaan', 'type' => 'int'],
            ['key' => 'first_pass', 'label' => 'Berhasil pada Percobaan Pertama', 'type' => 'text'],
            ['key' => 'stars', 'label' => 'Bintang', 'type' => 'int'],
            ['key' => 'duration_ms', 'label' => 'Durasi (m:dd)', 'type' => 'ms'],
            ['key' => 'completed_at', 'label' => 'Selesai', 'type' => 'datetime'],
        ]);
    }

    /** @return array<string, mixed> bentuk tabel data-table */
    private function feedbackOf(int $participantId): array
    {
        $rows = db_connect()->table('participant_feedback f')
            ->select('f.id, f.rating, f.liked_most, f.hardest_part, f.new_learning, f.suggestion, f.submitted_at, gs.session_code', false)
            ->join('game_sessions gs', 'gs.id = f.session_id', 'left')
            ->where('f.participant_id', $participantId)
            ->orderBy('f.submitted_at', 'DESC')
            ->get()
            ->getResultArray();

        return self::staticTable($rows, FeedbackController::answerColumns(withSession: true));
    }

    /**
     * Tabel satu halaman tanpa urut server (tab berisi sedikit baris).
     *
     * @param list<array<string, mixed>> $rows
     * @param list<array<string, mixed>> $columns
     *
     * @return array<string, mixed>
     */
    private static function staticTable(array $rows, array $columns): array
    {
        return [
            'rows'     => $rows,
            'total'    => count($rows),
            'page'     => 1,
            'per_page' => max(1, count($rows)),
            'sort'     => null,
            'columns'  => array_map(static fn (array $c): array => $c + ['sortable' => false], $columns),
        ];
    }
}
