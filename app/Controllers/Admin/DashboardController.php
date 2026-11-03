<?php

namespace App\Controllers\Admin;

use App\Libraries\ResearchReadiness;
use App\Services\RawDataService;

/**
 * Ringkasan panel (Dok 3 §8.3): four count tiles for every role; per role
 * (revision 10, part 1, 27 §8):
 * - admin: content readiness and retention warnings, no per-student lists
 *   (events are research data);
 * - peneliti and ketua: the 20 latest events and the 10 latest students,
 *   coded, with the school as its code (`school_ref`), never its name.
 */
class DashboardController extends BaseAdminController
{
    public function index(): string
    {
        $research = $this->can('participants.research');
        $system   = $this->can('content.edit');

        return $this->panel('admin/dashboard', lang('Admin.menuDashboard'), [
            'kpi'             => $this->kpi(),
            'events'          => $research ? $this->recentEvents() : null,
            'progress'        => $research ? $this->recentProgress() : null,
            'readiness'       => $system ? (new ResearchReadiness())->check() : null,
            'research'        => $research,
            // pratinjau penghapusan buatan retensi: hanya admin yang boleh memutuskan
            'retentionAlerts' => $this->can('retention.run') ? service('retentionService')->pendingRetentionRequests() : [],
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
        $summary = $this->analytics()->summary([]);

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
    private function recentEvents(): array
    {
        // Read before the builder: another query in between drops its table aliases
        $refs    = $this->nodeRefs();
        $builder = db_connect()->table('game_event_logs e')
            ->select('e.id, e.session_id, e.occurred_at, e.event_type, e.challenge_node_id, e.payload_json, p.participant_code')
            ->join('participants p', 'p.id = e.participant_id')
            ->where('e.deleted_at', null)
            ->where('p.deleted_at', null);

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
     * 10 siswa terbaru: kode, kode sekolah, wilayah saat ini, tantangan selesai
     * x/12, bintang, aktif terakhir (sesi terakhir masing-masing).
     *
     * @return array<string, mixed> bentuk tabel components/data-table
     */
    private function recentProgress(): array
    {
        $db     = db_connect();
        $total  = count($this->nodeRefs());
        $latest = $db->table('game_sessions')->select('MAX(id)', false)->where('participant_id = p.id', null, false)->getCompiledSelect();

        $builder = $db->table('participants p')
            ->select('p.id, p.participant_code, p.school_id, l.name_id AS level_name')
            ->select('sp.completed_nodes, sp.total_stars, gs.last_active_at', false)
            ->join($db->prefixTable('game_sessions') . ' gs', "gs.id = ({$latest})", 'left', false)
            ->join('participant_progress sp', 'sp.participant_id = gs.participant_id AND sp.study_id = gs.study_id', 'left')
            ->join('levels l', 'l.id = sp.current_level_id', 'left')
            ->where('p.deleted_at', null);

        $rows = array_map(static function (array $row) use ($total): array {
            $row['done']       = (int) ($row['completed_nodes'] ?? 0) . '/' . $total;
            $row['school_ref'] = RawDataService::schoolRef($row['school_id']);

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
                ['key' => 'school_ref', 'label' => 'Kode sekolah', 'type' => 'code', 'sortable' => false],
                ['key' => 'level_name', 'label' => 'Wilayah saat ini', 'type' => 'text', 'sortable' => false],
                ['key' => 'done', 'label' => 'Tantangan selesai', 'type' => 'text', 'sortable' => false],
                ['key' => 'total_stars', 'label' => 'Bintang', 'type' => 'int', 'sortable' => false],
                ['key' => 'last_active_at', 'label' => 'Aktif terakhir', 'type' => 'datetime', 'sortable' => false],
            ],
        ];
    }
}
