<?php

namespace App\Controllers\Admin;

use App\Models\AuditLogModel;
use App\Models\GameReleaseModel;
use App\Models\ResearchPhaseModel;
use App\Models\ResearchStudyModel;
use App\Models\ScoringProfileModel;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Studi penelitian, fase aktif, rilis konten, dan profil skoring.
 *
 * Mengaktifkan rilis atau profil skoring mengubah cara data berikutnya
 * diberi skor, jadi setiap perubahan dicatat di `audit_logs`.
 */
class StudyController extends BaseAdminController
{
    public function index(): string
    {
        $studies = model(ResearchStudyModel::class)->orderBy('created_at', 'DESC')->findAll();
        $phases  = [];

        foreach ($studies as $study) {
            $phases[(int) $study['id']] = model(ResearchPhaseModel::class)->forStudy((int) $study['id']);
        }

        return $this->panel('admin/study/index', 'Studi & fase', [
            'studies' => $studies,
            'phases'  => $phases,
            'locales' => config('Gelita')->locales,
            'phaseCodes' => config('Gelita')->phases,
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
            return $this->back('admin/studi', 'Studi belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_create', 'research_study', (int) $studyId);

        return $this->done('admin/studi', 'Studi baru sudah dibuat.');
    }

    public function update(int $studyId): RedirectResponse
    {
        $studies = model(ResearchStudyModel::class);

        if ($studies->find($studyId) === null) {
            return $this->back('admin/studi', 'Studi tidak ditemukan.');
        }

        if (! $this->validate($this->studyRules($studyId))) {
            return redirect()->to(site_url('admin/studi'))->with('errors', $this->validator->getErrors());
        }

        if (! $studies->update($studyId, $this->studyPayload(true))) {
            return $this->back('admin/studi', 'Studi belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_update', 'research_study', $studyId);

        return $this->done('admin/studi', 'Perubahan studi sudah disimpan.');
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
            'scoring_version' => ['label' => 'Versi aturan penilaian', 'rules' => 'required|max_length[20]'],
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

    // -------------------------------------------------------------- skoring

    public function scoringProfiles(): string
    {
        return $this->panel('admin/study/scoring', 'Aturan penilaian', [
            'profiles' => model(ScoringProfileModel::class)->orderBy('code', 'ASC')->orderBy('version', 'ASC')->findAll(),
            'active'   => model(ScoringProfileModel::class)->active(),
        ]);
    }

    public function storeScoringProfile(): RedirectResponse
    {
        $profiles = model(ScoringProfileModel::class);
        $back     = 'admin/studi/skoring';

        $rules = [
            'code'                => ['label' => 'Nama aturan', 'rules' => 'required|max_length[50]'],
            'version'             => ['label' => 'Nomor versi baru', 'rules' => 'required|max_length[20]'],
            'first_pass_weight'   => ['label' => 'Bobot benar sejak percobaan pertama', 'rules' => 'required|decimal'],
            'final_weight'        => ['label' => 'Bobot benar di akhir', 'rules' => 'required|decimal'],
            'independence_weight' => ['label' => 'Bobot kemandirian', 'rules' => 'required|decimal'],
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(site_url($back))->with('errors', $this->validator->getErrors());
        }

        $payload = [
            'code'                           => (string) $this->request->getPost('code'),
            'version'                        => (string) $this->request->getPost('version'),
            'first_pass_weight'              => (float) $this->request->getPost('first_pass_weight'),
            'final_weight'                   => (float) $this->request->getPost('final_weight'),
            'independence_weight'            => (float) $this->request->getPost('independence_weight'),
            'hint_penalty_per_use'           => (float) ($this->request->getPost('hint_penalty_per_use') ?? 0),
            'retry_penalty_per_extra_attempt' => (float) ($this->request->getPost('retry_penalty_per_extra_attempt') ?? 0),
            'three_star_min_score'           => (float) ($this->request->getPost('three_star_min_score') ?? 0),
            'three_star_min_first_pass'      => (float) ($this->request->getPost('three_star_min_first_pass') ?? 0),
            'two_star_min_score'             => (float) ($this->request->getPost('two_star_min_score') ?? 0),
            'is_active'                      => 0,
        ];

        $existing = $profiles->findVersion($payload['code'], $payload['version']);

        if ($existing !== null) {
            return $this->back($back, 'Aturan dengan nama dan nomor versi itu sudah ada. Pakai nomor versi lain.');
        }

        $profileId = $profiles->insert($payload, true);

        if ($profileId === false) {
            return $this->back($back, 'Aturan penilaian belum dapat disimpan: ' . $this->modelErrors($profiles));
        }

        if ($this->request->getPost('activate')) {
            $profiles->activate((int) $profileId);
            $this->audit('scoring_activate', 'scoring_profile', (int) $profileId);
        }

        $this->audit('scoring_create', 'scoring_profile', (int) $profileId);

        return $this->done($back, 'Versi aturan penilaian sudah disimpan.');
    }

    // -------------------------------------------------------------- bantuan

    /** @return array<string, array{label: string, rules: string}> */
    private function studyRules(?int $studyId = null): array
    {
        $unique = $studyId === null
            ? 'is_unique[research_studies.code]'
            : 'is_unique[research_studies.code,id,' . $studyId . ']';

        return [
            'code'               => ['label' => 'Kode singkat studi', 'rules' => 'required|max_length[50]|' . $unique],
            'name'               => ['label' => 'Nama studi', 'rules' => 'required|max_length[200]'],
            'status'             => ['label' => 'Status', 'rules' => 'required|in_list[draft,active,closed]'],
            'default_locale'     => ['label' => 'Bahasa awal permainan', 'rules' => 'required|valid_locale'],
            'unlock_mode'        => ['label' => 'Cara wilayah terbuka', 'rules' => 'required|in_list[sequential,free]'],
            'item_selection_mode' => ['label' => 'Pemilihan soal', 'rules' => 'required|in_list[fixed,random]'],
            'active_phase_code'  => ['label' => 'Fase yang sedang berjalan', 'rules' => 'required|in_list[umum,pretest,posttest]'],
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
            'item_selection_mode' => (string) $this->request->getPost('item_selection_mode'),
            'require_consent'     => $this->request->getPost('require_consent') ? 1 : 0,
            'active_phase_code'   => (string) $this->request->getPost('active_phase_code'),
            'allow_phase_choice'  => $this->request->getPost('allow_phase_choice') ? 1 : 0,
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
