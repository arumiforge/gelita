<?php

namespace App\Controllers\Admin;

use App\Entities\Participant;
use App\Models\ParticipantConsentModel;
use App\Models\ParticipantModel;
use App\Models\SchoolModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Daftar peserta, profil capaian, dan reset kata sandi siswa.
 *
 * Guru hanya melihat dan mereset peserta di sekolahnya; peserta di luar
 * cakupan dibalas 404, bukan 403, agar keberadaannya tidak terkonfirmasi.
 *
 * Peserta dikirim ke view sebagai array aman (Participant::toSafeArray() +
 * kolom tampilan yang diperlukan), tidak pernah sebagai entity — entity ikut
 * membawa `password_hash` (aturan 11, 05_VIEW_UI.md).
 */
class ParticipantController extends BaseAdminController
{
    private const PER_PAGE = 25;

    public function index(): string
    {
        $participants = model(ParticipantModel::class);
        $scope        = $this->schoolScope();
        $filters      = $this->readFilters();

        $participants->where('participants.deleted_at', null);

        if ($scope !== null) {
            $participants->where('participants.school_id', $scope);
        }

        if (isset($filters['class_level'])) {
            $participants->where('participants.class_level', $filters['class_level']);
        }

        if (isset($filters['province_code'])) {
            $participants->where('participants.province_code', $filters['province_code']);
        }

        if ($scope === null && isset($filters['school_id'])) {
            $participants->where('participants.school_id', $filters['school_id']);
        }

        $search = trim((string) ($this->request->getGet('q') ?? ''));

        if ($search !== '') {
            $participants->groupStart()
                ->like('participants.participant_code', $search)
                ->orLike('participants.username', $search)
                ->orLike('participants.display_name', $search)
                ->groupEnd();
        }

        $entities = $participants->orderBy('participants.created_at', 'DESC')->paginate(self::PER_PAGE);
        $stats    = $this->listStats(array_map(static fn (Participant $p): int => $p->id, $entities));

        $rows = array_map(fn (Participant $p): array => $this->viewRow($p) + ($stats[$p->id] ?? [
            'session_count' => 0, 'shards' => 0, 'mean_first_pass' => null, 'last_active_at' => null,
        ]), $entities);

        return $this->panel('admin/participants/index', 'Peserta', [
            'filters' => $filters,
            'rows'    => $rows,
            'pager'   => $participants->pager,
            'search'  => $search,
            'schools' => model(SchoolModel::class)->activeList(),
            'isAdmin' => $this->isAdmin(),
        ]);
    }

    public function show(int $participantId): string
    {
        $participant = $this->requireInScope($participantId);

        try {
            $profile = $this->analytics()->participantProfile($participantId, $this->schoolScope());
        } catch (\RuntimeException) {
            throw PageNotFoundException::forPageNotFound("Peserta {$participantId} tidak ditemukan.");
        }

        return $this->panel('admin/participants/show', 'Profil peserta', [
            'participant' => $this->viewRow($participant),
            'profile'     => $profile,
            'consent'     => model(ParticipantConsentModel::class)->latestFor($participantId),
            'sessions'    => $this->sessionsOf($participantId),
            'levels'      => service('contentRepository')->levels(),
            'nodeTitles'  => $this->nodeTitles(),
            'isAdmin'     => $this->isAdmin(),
        ]);
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
                'Ketik RESET pada kotak konfirmasi untuk mengatur ulang kata sandi.',
            );
        }

        $temporary = service('sessionService')->resetPasswordByStaff($participantId, $this->staffId());

        $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $this->response->setHeader('Pragma', 'no-cache');

        return $this->panel('admin/participants/reset-result', 'Kata sandi sementara', [
            'participant'       => $this->viewRow($participant),
            'temporaryPassword' => $temporary,
        ]);
    }

    /** Peserta dalam cakupan pemanggil, atau 404. */
    private function requireInScope(int $participantId): Participant
    {
        $participant = model(ParticipantModel::class)->find($participantId);
        $scope       = $this->schoolScope();

        if ($participant === null || ($scope !== null && (int) $participant->school_id !== $scope)) {
            throw PageNotFoundException::forPageNotFound("Peserta {$participantId} tidak ditemukan.");
        }

        return $participant;
    }

    /**
     * Bentuk tampilan panel: toSafeArray() + snapshot wilayah, metrik literasi
     * keamanan digital, dan waktu penting. Tanpa password_hash & kolom throttle.
     *
     * @return array<string, mixed>
     */
    private function viewRow(Participant $participant): array
    {
        return $participant->toSafeArray() + [
            'province_name'            => $participant->province_name_snapshot,
            'district_name'            => $participant->district_name_snapshot,
            'country_name'             => $participant->country_name_snapshot,
            'pw_first_submit_criteria' => $participant->pw_first_submit_criteria,
            'pw_weak_submit_count'     => $participant->pw_weak_submit_count,
            'password_changed_at'      => $participant->password_changed_at,
            'must_change_password'     => $participant->mustChangePassword(),
            'last_login_at'            => $participant->last_login_at,
            'created_at'               => $participant->created_at,
        ];
    }

    /**
     * Ringkasan per peserta untuk satu halaman daftar: jumlah sesi, serpihan
     * terbanyak dalam satu sesi, rata-rata tepat sejak awal, dan aktivitas terakhir.
     *
     * @param list<int> $ids
     *
     * @return array<int, array<string, mixed>>
     */
    private function listStats(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $db  = db_connect();
        $out = [];

        $sessions = $db->table('game_sessions gs')
            ->select('gs.participant_id, COUNT(*) AS session_count, MAX(gs.last_active_at) AS last_active_at', false)
            ->select('MAX(COALESCE(sp.completed_nodes, 0)) AS shards', false)
            ->join('session_progress sp', 'sp.session_id = gs.id', 'left')
            ->whereIn('gs.participant_id', $ids)
            ->groupBy('gs.participant_id')
            ->get()
            ->getResultArray();

        foreach ($sessions as $row) {
            $out[(int) $row['participant_id']] = [
                'session_count'   => (int) $row['session_count'],
                'shards'          => (int) $row['shards'],
                'last_active_at'  => $row['last_active_at'],
                'mean_first_pass' => null,
            ];
        }

        $accuracy = $db->table('challenge_attempts ca')
            ->select('gs.participant_id, AVG(ca.first_pass_accuracy) AS mean_first_pass', false)
            ->join('game_sessions gs', 'gs.id = ca.session_id')
            ->whereIn('gs.participant_id', $ids)
            ->where('ca.status', 'completed')
            ->groupBy('gs.participant_id')
            ->get()
            ->getResultArray();

        foreach ($accuracy as $row) {
            $id = (int) $row['participant_id'];

            if (isset($out[$id])) {
                $out[$id]['mean_first_pass'] = round((float) $row['mean_first_pass'], 1);
            }
        }

        return $out;
    }

    /**
     * Sesi peserta beserta fase dan progres, terbaru dulu.
     *
     * @return list<array<string, mixed>>
     */
    private function sessionsOf(int $participantId): array
    {
        return db_connect()->table('game_sessions gs')
            ->select('gs.id, gs.session_code, gs.status, gs.locale, gs.started_at, gs.last_active_at, gs.duration_ms', false)
            ->select('rp.code AS phase_code, sp.completed_nodes, sp.total_score, sp.total_stars', false)
            ->join('research_phases rp', 'rp.id = gs.phase_id', 'left')
            ->join('session_progress sp', 'sp.session_id = gs.id', 'left')
            ->where('gs.participant_id', $participantId)
            ->orderBy('gs.started_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** @return array<int, string> id node → "Wilayah · judul" */
    private function nodeTitles(): array
    {
        $content = service('contentRepository');
        $titles  = [];

        foreach ($content->levels() as $level) {
            foreach ($content->nodesForLevel($level->id) as $node) {
                $titles[$node->id] = $level->text('name', 'id') . ' · ' . $node->sequence . '. ' . $node->text('title', 'id');
            }
        }

        return $titles;
    }
}
