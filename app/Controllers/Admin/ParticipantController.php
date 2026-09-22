<?php

namespace App\Controllers\Admin;

use App\Models\ParticipantModel;
use App\Models\SchoolModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Daftar peserta, profil capaian, dan reset kata sandi siswa.
 *
 * Guru hanya melihat dan mereset peserta di sekolahnya; peserta di luar
 * cakupan dibalas 404, bukan 403, agar keberadaannya tidak terkonfirmasi.
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

        $search = trim((string) ($this->request->getGet('q') ?? ''));

        if ($search !== '') {
            $participants->groupStart()
                ->like('participants.participant_code', $search)
                ->orLike('participants.username', $search)
                ->orLike('participants.display_name', $search)
                ->groupEnd();
        }

        $rows = $participants->orderBy('participants.created_at', 'DESC')->paginate(self::PER_PAGE);

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
            'participant' => $participant,
            'profile'     => $profile,
            'isAdmin'     => $this->isAdmin(),
        ]);
    }

    /**
     * Reset sandi siswa. Sandi sementara ditampilkan SEKALI pada respons ini:
     * tidak disimpan, tidak dikirim lewat flash, dan tidak masuk log.
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

        return $this->panel('admin/participants/reset-result', 'Kata sandi sementara', [
            'participant'       => $participant,
            'temporaryPassword' => $temporary,
        ]);
    }

    /** Peserta dalam cakupan pemanggil, atau 404. */
    private function requireInScope(int $participantId): \App\Entities\Participant
    {
        $participant = model(ParticipantModel::class)->find($participantId);
        $scope       = $this->schoolScope();

        if ($participant === null || ($scope !== null && (int) $participant->school_id !== $scope)) {
            throw PageNotFoundException::forPageNotFound("Peserta {$participantId} tidak ditemukan.");
        }

        return $participant;
    }
}
