<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\GameReleaseModel;
use App\Models\ResearchStudyModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Studi penelitian dan rilis konten. Satu tahap bermain (tanpa fase, tanpa
 * pengaturan persetujuan atau mode pemilihan soal — perombakan kelas 5).
 *
 * Mengaktifkan rilis mengubah konten yang dipakai sesi berikutnya, jadi
 * setiap perubahan dicatat di `audit_logs`.
 */
class StudyController extends BaseAdminController
{
    public function index(): string
    {
        return $this->panel('admin/study/index', 'Penelitian', [
            'studies' => model(ResearchStudyModel::class)->orderBy('created_at', 'DESC')->findAll(),
            'locales' => config('Gelita')->locales,
        ]);
    }

    public function store(): RedirectResponse
    {
        $studies = model(ResearchStudyModel::class);

        if (! $this->validate($this->studyRules())) {
            return redirect()->to(site_url('admin/studi'))->with('errors', $this->validator->getErrors());
        }

        $studyId = $studies->insert($this->studyPayload(), true);

        if ($studyId === false) {
            return $this->back('admin/studi', 'Penelitian belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_create', 'research_study', (int) $studyId);

        return $this->done('admin/studi', 'Penelitian baru sudah dibuat.');
    }

    public function update(int $studyId): RedirectResponse
    {
        $studies = model(ResearchStudyModel::class);

        if ($studies->find($studyId) === null) {
            return $this->back('admin/studi', 'Penelitian tidak ditemukan.');
        }

        if (! $this->validate($this->studyRules($studyId))) {
            return redirect()->to(site_url('admin/studi'))->with('errors', $this->validator->getErrors());
        }

        if (! $studies->update($studyId, $this->studyPayload(true))) {
            return $this->back('admin/studi', 'Penelitian belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_update', 'research_study', $studyId);

        return $this->done('admin/studi', 'Perubahan penelitian sudah disimpan.');
    }

    // ---------------------------------------------------------------- rilis

    public function releases(): string
    {
        return $this->panel('admin/study/releases', 'Versi permainan', [
            'releases' => model(GameReleaseModel::class)->orderBy('id', 'DESC')->findAll(),
            'active'   => model(GameReleaseModel::class)->activeOrNull(),
        ]);
    }

    public function storeRelease(): RedirectResponse
    {
        $releases = model(GameReleaseModel::class);

        $rules = [
            'release_code'    => ['label' => 'Nama versi', 'rules' => 'required|max_length[50]|is_unique[game_releases.release_code]'],
            'app_version'     => ['label' => 'Versi aplikasi', 'rules' => 'required|max_length[20]'],
            'content_version' => ['label' => 'Versi konten', 'rules' => 'required|max_length[20]'],
            'scoring_version' => ['label' => 'Versi aturan bintang', 'rules' => 'required|max_length[20]'],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url('admin/studi/rilis'))->with('errors', $this->validator->getErrors());
        }

        $releaseId = $releases->insert([
            'release_code'    => (string) $this->request->getPost('release_code'),
            'app_version'     => (string) $this->request->getPost('app_version'),
            'content_version' => (string) $this->request->getPost('content_version'),
            'asset_version'   => (string) ($this->request->getPost('asset_version') ?: '1'),
            'scoring_version' => (string) $this->request->getPost('scoring_version'),
            'notes'           => $this->request->getPost('notes') ?: null,
            'is_active'       => 0,
        ], true);

        if ($releaseId === false) {
            return $this->back('admin/studi/rilis', 'Versi permainan belum dapat disimpan: ' . $this->modelErrors($releases));
        }

        $this->audit('release_create', 'game_release', (int) $releaseId);

        return $this->done('admin/studi/rilis', 'Versi baru sudah disimpan. Tekan Aktifkan bila sudah siap dipakai.');
    }

    public function activateRelease(int $releaseId): RedirectResponse
    {
        $releases = model(GameReleaseModel::class);

        if ($releases->find($releaseId) === null) {
            return $this->back('admin/studi/rilis', 'Versi permainan tidak ditemukan.');
        }

        $releases->activate($releaseId);

        // content_version ikut jadi kunci cache konten
        service('contentRepository')->flush();

        $this->audit('release_activate', 'game_release', $releaseId);

        return $this->done('admin/studi/rilis', 'Versi permainan sudah diaktifkan. Sesi bermain baru memakai versi ini.');
    }

    // -------------------------------------------------------------- bantuan

    /** @return array<string, array{label: string, rules: string}> */
    private function studyRules(?int $studyId = null): array
    {
        $unique = $studyId === null
            ? 'is_unique[research_studies.code]'
            : 'is_unique[research_studies.code,id,' . $studyId . ']';

        return [
            'code'               => ['label' => 'Kode singkat penelitian', 'rules' => 'required|max_length[50]|' . $unique],
            'name'               => ['label' => 'Nama penelitian', 'rules' => 'required|max_length[200]'],
            'status'             => ['label' => 'Status', 'rules' => 'required|in_list[draft,active,closed]'],
            'default_locale'     => ['label' => 'Bahasa awal permainan', 'rules' => 'required|valid_locale'],
            'unlock_mode'        => ['label' => 'Cara wilayah terbuka', 'rules' => 'required|in_list[sequential,free]'],
            'retention_days'     => ['label' => 'Lama penyimpanan data', 'rules' => 'permit_empty|is_natural'],
        ];
    }

    /**
     * Kode studi tidak ikut diperbarui: aturan `is_unique[...,id,{id}]` milik
     * model tidak dapat menyelesaikan placeholder `{id}` pada update (data
     * update tidak memuat kolom id), sehingga menyertakannya akan selalu
     * ditolak sebagai duplikat dirinya sendiri.
     *
     * @return array<string, mixed>
     */
    private function studyPayload(bool $forUpdate = false): array
    {
        $payload = [
            'code'                => (string) $this->request->getPost('code'),
            'name'                => (string) $this->request->getPost('name'),
            'description'         => $this->request->getPost('description') ?: null,
            'year_label'          => $this->request->getPost('year_label') ?: null,
            'status'              => (string) $this->request->getPost('status'),
            'retention_days'      => (int) ($this->request->getPost('retention_days') ?: 365),
            'default_locale'      => (string) $this->request->getPost('default_locale'),
            'unlock_mode'         => (string) $this->request->getPost('unlock_mode'),
        ];

        if ($forUpdate) {
            unset($payload['code']);
        }

        return $payload;
    }

    private function audit(string $action, string $targetType, int $targetId): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $this->staffId(),
            'target_type'   => $targetType,
            'target_id'     => (string) $targetId,
        ]);
    }
}
