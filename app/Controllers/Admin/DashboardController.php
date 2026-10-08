<?php

namespace App\Controllers\Admin;

use App\Libraries\ResearchReadiness;

/**
 * Ringkasan panel (Dok 3 §8.3): empat KPI hitungan, peringatan kesiapan
 * konten (admin), 20 aktivitas terbaru, dan progres 10 siswa terbaru.
 * Semua query dibatasi cakupan sekolah pemanggil.
 */
class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        $scope = $this->schoolScope();

        return $this->panel('admin/dashboard', lang('Admin.menuDashboard'), [
            'kpi'             => $this->kpi(),
            'events'          => $this->recentEvents($scope),
            'progress'        => $this->recentProgress($scope),
            'readiness'       => $this->isAdmin() ? (new ResearchReadiness())->check() : null,
            'isAdmin'         => $this->isAdmin(),
            // pratinjau penghapusan buatan retensi: hanya admin yang boleh memutuskan
            'retentionAlerts' => $this->isAdmin() ? service('retentionService')->pendingRetentionRequests() : [],
        ]);
    }

    /**
     * Hitungan: siswa terdaftar · sesi aktif sekarang (≤ 10 menit) ·
     * pengerjaan selesai hari ini · kritik & saran — dari
     * AnalyticsService::summary() (cakupan sekolah pemanggil).
     *
     * @return array{participants: int, active_sessions: int, completed_today: int, feedback: int}
     */
    private function kpi(): array
    {
        $summary = $this->analytics()->summary($this->scopedFilters([]));

        return [
            'participants'    => (int) $summary['participant_count'],
            'active_sessions' => (int) $summary['active_sessions'],
            'completed_today' => (int) $summary['completed_attempts_today'],
            'feedback'        => (int) $summary['feedback_count'],
        ];
    }

    /**
     * 20 event terakhir: waktu, kode siswa, kejadian, layar/tantangan.
     *
     * @return array<string, mixed> bentuk tabel components/data-table
     */
    private function recentEvents(?int $scope): array
    {
        $builder = db_connect()->table('game_event_logs e')
            ->select('e.id, e.session_id, e.occurred_at, e.event_type, e.challenge_node_id, e.payload_json, p.participant_code')
            ->join('participants p', 'p.id = e.participant_id')
            ->where('e.deleted_at', null)
            ->where('p.deleted_at', null);

        if ($scope !== null) {
            $builder->where('p.school_id', $scope);
        }

        $refs = $this->nodeRefs();
        $rows = array_map(static function (array $row) use ($refs): array {
            $payload       = json_decode((string) $row['payload_json'], true);
            $row['event']  = admin_label('event', $row['event_type']);
            $row['place']  = $row['challenge_node_id'] !== null
                ? ($refs[(int) $row['challenge_node_id']] ?? '#' . $row['challenge_node_id'])
                : (is_array($payload) ? ($payload['screen'] ?? null) : null);

            return $row;
        }, $builder->orderBy('e.id', 'DESC')->limit(20)->get()->getResultArray());

        return [
            'rows'     => $rows,
            'total'    => count($rows),
            'page'     => 1,
            'per_page' => 20,
            'sort'     => null,
            'columns'  => [
                ['key' => 'occurred_at', 'label' => 'Waktu', 'type' => 'datetime', 'sortable' => false],
                ['key' => 'participant_code', 'label' => 'Kode siswa', 'type' => 'code', 'sortable' => false],
                ['key' => 'event', 'label' => 'Kejadian', 'type' => 'text', 'sortable' => false],
                ['key' => 'place', 'label' => 'Layar/tantangan', 'type' => 'code', 'sortable' => false],
            ],
        ];
    }

    /**
     * 10 siswa terbaru: kode, sekolah, wilayah saat ini, tantangan selesai
     * x/12, bintang, aktif terakhir (sesi terakhir masing-masing).
     *
     * @return array<string, mixed> bentuk tabel components/data-table
     */
    private function recentProgress(?int $scope): array
    {
        $db     = db_connect();
        $latest = $db->table('game_sessions')->select('MAX(id)', false)->where('participant_id = p.id', null, false)->getCompiledSelect();

        $builder = $db->table('participants p')
            ->select('p.id, p.participant_code, s.name AS school_name, l.name_id AS level_name')
            ->select('sp.completed_nodes, sp.total_stars, gs.last_active_at', false)
            ->join('schools s', 's.id = p.school_id', 'left')
            ->join($db->prefixTable('game_sessions') . ' gs', "gs.id = ({$latest})", 'left', false)
            ->join('session_progress sp', 'sp.session_id = gs.id', 'left')
            ->join('levels l', 'l.id = sp.current_level_id', 'left')
            ->where('p.deleted_at', null);

        if ($scope !== null) {
            $builder->where('p.school_id', $scope);
        }

        $total = count($this->nodeRefs());
        $rows  = array_map(static function (array $row) use ($total): array {
            $row['done'] = (int) ($row['completed_nodes'] ?? 0) . '/' . $total;

            return $row;
        }, $builder->orderBy('p.id', 'DESC')->limit(10)->get()->getResultArray());

        return [
            'rows'     => $rows,
            'total'    => count($rows),
            'page'     => 1,
            'per_page' => 10,
            'sort'     => null,
            'columns'  => [
                ['key' => 'participant_code', 'label' => 'Kode', 'type' => 'code', 'sortable' => false],
                ['key' => 'school_name', 'label' => 'Sekolah', 'type' => 'text', 'sortable' => false],
                ['key' => 'level_name', 'label' => 'Wilayah saat ini', 'type' => 'text', 'sortable' => false],
                ['key' => 'done', 'label' => 'Tantangan selesai', 'type' => 'text', 'sortable' => false],
                ['key' => 'total_stars', 'label' => 'Bintang', 'type' => 'int', 'sortable' => false],
                ['key' => 'last_active_at', 'label' => 'Aktif terakhir', 'type' => 'datetime', 'sortable' => false],
            ],
        ];
    }
}
