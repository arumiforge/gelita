<?php

namespace App\Controllers\Admin;

use App\Libraries\ResearchReadiness;
use App\Models\AuditLogModel;
use App\Models\GameReleaseModel;
use App\Models\ResearchStudyModel;
use App\Services\ReleaseActivation;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Research studies and game releases. One play stage; a study sets the
 * period and how regions unlock.
 *
 * Activating a release changes what the next game sessions use, so every
 * change is recorded in `audit_logs`. Activation goes through
 * ReleaseActivation, shared with Rilis → Paket aset (PackageController):
 * while a study is running, a release with another asset package needs a
 * confirmation and a reason (docs/rencana-paket-aset.md §7.2). A study locks
 * the active release's package when it becomes *berjalan*.
 */
class StudyController extends BaseAdminController
{
    public function index(): string
    {
        return $this->panel('admin/study/index', lang('Admin.menuStudy'), [
            'studies'     => model(ResearchStudyModel::class)->orderBy('created_at', 'DESC')->findAll(),
            'locales'     => config('Gelita')->locales,
            'readiness'   => (new ResearchReadiness())->check(),
            // Study package lock (§7.2): compared with what students get now
            'packageCode' => service('assetPackages')->releasePackageCode(),
        ]);
    }

    public function store(): RedirectResponse
    {
        $studies = model(ResearchStudyModel::class);

        if (! $this->validate($this->studyRules())) {
            return redirect()->to(site_url('admin/studi'))->with('errors', $this->validator->getErrors());
        }

        if (($blocked = $this->readinessGuard(null)) !== null) {
            return $blocked;
        }

        if (($refused = $this->researchGuard($this->studyPayload(), null)) !== null) {
            return $this->back('admin/studi', $refused);
        }

        $studyId = $studies->insert($this->studyPayload() + $this->packageLock(null) + $this->closing(null), true);

        if ($studyId === false) {
            return $this->back('admin/studi', 'Penelitian belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_create', 'research_study', (int) $studyId, $this->packageLock(null));

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

        $row      = $studies->find($studyId);
        $previous = $row['status'] ?? null;

        if (($blocked = $this->readinessGuard($previous)) !== null) {
            return $blocked;
        }

        $lock    = $this->packageLock($previous);
        $payload = $this->studyPayload(true);

        if (($refused = $this->researchGuard($payload, $row)) !== null) {
            return $this->back('admin/studi', $refused);
        }

        $payload += $this->closing($row);

        // Field mode and meeting plan stay put while a field session is open (28 §5.1, §5.2)
        $fieldChanged = (int) $payload['field_mode'] !== (int) ($row['field_mode'] ?? 0)
            || $payload['meeting_plan'] !== (string) ($row['meeting_plan'] ?? 'lima_pertemuan');

        if ($fieldChanged && service('fieldSessions')->hasOpenSessions($studyId)) {
            return $this->back('admin/studi', lang('Admin.fieldModeLockedOpen'));
        }

        if (! $studies->update($studyId, $payload + $lock)) {
            return $this->back('admin/studi', 'Penelitian belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_update', 'research_study', $studyId, $lock);

        if ($fieldChanged) {
            $this->audit('study_field_mode_changed', 'research_study', $studyId, [
                'from' => ['field_mode' => (int) ($row['field_mode'] ?? 0), 'meeting_plan' => $row['meeting_plan'] ?? null],
                'to'   => ['field_mode' => (int) $payload['field_mode'], 'meeting_plan' => $payload['meeting_plan']],
            ]);
        }

        return $this->done('admin/studi', 'Perubahan penelitian sudah disimpan.');
    }

    // ------------------------------------------------ part 7 (33 §6, §10)

    /** Study settings => [form field, unit factor to ms/px, min, max, range message]. */
    public const SETTINGS = [
        'idle_threshold_ms'   => ['idle_threshold_s', 1000, 30000, 1800000, 'Admin.studyIdleRange'],
        'rapid_guess_ms'      => ['rapid_guess_s', 1000, 500, 10000, 'Admin.studyRapidRange'],
        'test_rapid_guess_ms' => ['test_rapid_guess_s', 1000, 500, 10000, 'Admin.studyRapidRange'],
        'min_stage_px'        => ['min_stage_px', 1, 640, 1280, 'Admin.studyMinStageRange'],
    ];

    /** Thresholds that part 9's *Kunci ambang* (35 §5, `thresholds_locked_at`) freezes. */
    public const LOCKED_BY_THRESHOLDS = ['idle_threshold_ms', 'rapid_guess_ms', 'test_rapid_guess_ms'];

    /**
     * POST /admin/studi/{id}/pengaturan (ketua, `studies.manage`): the idle
     * and rapid-guess thresholds (shown in seconds) and the smallest stage
     * width (pixels). A field left out keeps its value. The idle threshold
     * may never be below the logging limit `inputIdleLogMs`. Once the
     * thresholds are locked (`thresholds_locked_at`, part 9) only
     * `min_stage_px` may change. Audit `study_settings_changed` with the old
     * and new values of what changed.
     */
    public function updateSettings(int $studyId): RedirectResponse
    {
        $studies = model(ResearchStudyModel::class);
        $row     = $studies->find($studyId);

        if ($row === null) {
            return $this->back('admin/studi', 'Penelitian tidak ditemukan.');
        }

        $changes = [];

        foreach (self::SETTINGS as $column => [$field, $factor, $min, $max, $range]) {
            $raw = trim((string) $this->request->getPost($field));

            if ($raw === '') {
                continue;
            }

            $number = str_replace(',', '.', $raw);

            if (! is_numeric($number)) {
                return $this->back('admin/studi', lang($range));
            }

            $value = (int) round((float) $number * $factor);

            if ($column === 'idle_threshold_ms' && ($value < $min || $value < (int) config('Gelita')->inputIdleLogMs)) {
                return $this->back('admin/studi', lang('Admin.studyIdleTooLow'));
            }

            if ($value < $min || $value > $max) {
                return $this->back('admin/studi', lang($range));
            }

            if ($value !== (int) ($row[$column] ?? 0)) {
                $changes[$column] = $value;
            }
        }

        if (! empty($row['thresholds_locked_at']) && array_intersect(array_keys($changes), self::LOCKED_BY_THRESHOLDS) !== []) {
            return $this->back('admin/studi', lang('Admin.studyThresholdsLocked'));
        }

        if ($changes === []) {
            return $this->done('admin/studi', lang('Admin.studySettingsUnchanged'));
        }

        if (! $studies->update($studyId, $changes)) {
            return $this->back('admin/studi', 'Pengaturan belum dapat disimpan: ' . $this->modelErrors($studies));
        }

        $this->audit('study_settings_changed', 'research_study', $studyId, [
            'from' => array_map(static fn (string $column): int => (int) ($row[$column] ?? 0), array_combine(array_keys($changes), array_keys($changes))),
            'to'   => $changes,
        ]);
        service('fieldSessions')->forget();

        return $this->done('admin/studi', lang('Admin.studySettingsSaved'));
    }

    // ---------------------------------------------------------------- rilis

    public function releases(): string
    {
        $packages = service('assetPackages');
        $releases = model(GameReleaseModel::class)->orderBy('id', 'DESC')->findAll();
        $code     = $packages->releasePackageCode();

        return $this->panel('admin/study/releases', lang('Admin.menuReleases'), [
            'releases'        => $releases,
            'active'          => model(GameReleaseModel::class)->activeOrNull(),
            'packageCode'     => $code,
            'packageOn'       => $code !== null && $packages->enabled(),
            'killSwitch'      => ! $packages->enabled(),
            'changed'         => $packages->changedSinceActive(),
            'runningStudy'    => model(ResearchStudyModel::class)->activeStudy(),
            // release id => package it names (null = none), for the running-study lock
            'releasePackages' => array_combine(
                array_map(static fn (array $row): int => (int) $row['id'], $releases),
                array_map(static fn (array $row): ?string => $packages->packageOfRelease($row), $releases),
            ),
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
        $result = (new ReleaseActivation())->activate($releaseId, [
            'confirmed' => (string) $this->request->getPost('confirm_running_study') === '1',
            'reason'    => (string) $this->request->getPost('reason'),
            'staff_id'  => $this->staffId(),
        ]);

        if ($result['status'] === ReleaseActivation::NOT_FOUND) {
            return $this->back('admin/studi/rilis', 'Versi permainan tidak ditemukan.');
        }

        if ($result['status'] === ReleaseActivation::NEEDS_CONFIRMATION) {
            return redirect()->to(site_url('admin/studi/rilis/' . $releaseId . '/aktifkan'))
                ->with('error', lang('Admin.releaseNeedsReason', [$result['study']['name']]));
        }

        return $this->done('admin/studi/rilis', 'Versi permainan sudah diaktifkan. Sesi bermain baru memakai versi ini.');
    }

    /**
     * Confirmation page for a release that changes the asset package during
     * a running study (§7.2): what changes, a confirmation tick, and a reason.
     * Without a running study or a package change it goes straight back.
     */
    public function confirmActivation(int $releaseId): string|RedirectResponse
    {
        $release = model(GameReleaseModel::class)->find($releaseId);

        if ($release === null) {
            return $this->back('admin/studi/rilis', 'Versi permainan tidak ditemukan.');
        }

        $lock = (new ReleaseActivation())->lockFor($release);

        if ($lock === null) {
            return redirect()->to(site_url('admin/studi/rilis'));
        }

        return $this->panel('admin/study/confirm-activation', lang('Admin.releaseConfirmTitle'), [
            'breadcrumb' => [lang('Admin.releaseConfirmTitle') => null],
            'release'    => $release,
            'lock'       => $lock,
        ]);
    }

    // -------------------------------------------------------------- bantuan

    /**
     * Mengubah status ke *berjalan* saat daftar siap penelitian belum hijau
     * wajib disertai centang konfirmasi (Dok 3 §8.3).
     */
    private function readinessGuard(?string $currentStatus): ?RedirectResponse
    {
        if ($this->request->getPost('status') !== 'active' || $currentStatus === 'active') {
            return null;
        }

        if ((string) $this->request->getPost('confirm_not_ready') === '1' || (new ResearchReadiness())->check()['ready']) {
            return null;
        }

        return $this->back('admin/studi', 'Daftar siap penelitian belum terpenuhi. Centang "Tetap jalankan" bila penelitian memang harus dimulai sekarang.');
    }

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
            'field_mode'         => ['label' => lang('Admin.fieldModeLabel'), 'rules' => 'permit_empty|in_list[0,1]'],
            'meeting_plan'       => ['label' => lang('Admin.meetingPlanLabel'), 'rules' => 'permit_empty|in_list[' . implode(',', array_keys(config('Gelita')->meetingPlans)) . ']'],
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
            // Part 4 (30 §11): a field-mode study keeps its data 7305 days unless more is typed
            'retention_days'      => (int) ($this->request->getPost('retention_days') ?: ((string) $this->request->getPost('field_mode') === '1' ? config('Gelita')->researchRetentionDays : 365)),
            'default_locale'      => (string) $this->request->getPost('default_locale'),
            'unlock_mode'         => (string) $this->request->getPost('unlock_mode'),
            // Off unless ticked (28 K1)
            'field_mode'          => (string) $this->request->getPost('field_mode') === '1' ? 1 : 0,
            'meeting_plan'        => (string) ($this->request->getPost('meeting_plan') ?: 'lima_pertemuan'),
        ];

        if ($forUpdate) {
            unset($payload['code']);
        }

        return $payload;
    }

    /**
     * Study package lock (§7.2): when the status changes to *berjalan*, the
     * study keeps the package the active release names (null = package mode
     * off). Empty when the status does not change to *berjalan*.
     *
     * @return array{package_code?: string|null}
     */
    private function packageLock(?string $previousStatus): array
    {
        if ($this->request->getPost('status') !== 'active' || $previousStatus === 'active') {
            return [];
        }

        return ['package_code' => service('assetPackages')->releasePackageCode()];
    }

    /**
     * Part 4 (30 §0.1, §11, §10.2): a field-mode study needs the research
     * privacy switch on and at least 7305 days of retention; a study whose
     * identities were replaced cannot be opened again.
     *
     * @param array<string, mixed>      $payload
     * @param array<string, mixed>|null $row     the stored study (null = new)
     */
    private function researchGuard(array $payload, ?array $row): ?string
    {
        if ($row !== null && $row['identities_replaced_at'] !== null && $payload['status'] !== 'closed') {
            return lang('Admin.closureReplaced');
        }

        if ((int) $payload['field_mode'] !== 1) {
            return null;
        }

        if (! config('Gelita')->researchPrivacy) {
            return lang('Admin.researchPrivacyRequired');
        }

        return (int) $payload['retention_days'] < config('Gelita')->researchRetentionDays ? lang('Admin.retentionMinStudy') : null;
    }

    /**
     * `closed_at` is filled when the status becomes *closed* and emptied when
     * the study is opened again (30 §11).
     *
     * @param array<string, mixed>|null $row
     *
     * @return array{closed_at?: string|null}
     */
    private function closing(?array $row): array
    {
        $closed = $this->request->getPost('status') === 'closed';

        if ($closed && ($row === null || $row['closed_at'] === null)) {
            return ['closed_at' => date('Y-m-d H:i:s')];
        }

        return ! $closed && $row !== null && $row['closed_at'] !== null ? ['closed_at' => null] : [];
    }

    // Part 8 (34 §7.4, K15): hidden time limits of the test module, its own route and method

    /** POST /admin/studi/{id}/instrumen (`studies.manage`); audit `study_instrument_settings_changed` with old and new values. */
    public function updateInstrumentSettings(int $studyId): RedirectResponse
    {
        $studies = model(ResearchStudyModel::class);
        $row     = $studies->find($studyId);

        if ($row === null) {
            return $this->back('admin/studi', 'Penelitian tidak ditemukan.');
        }

        $old = service('instruments')->settings($row);
        $new = [];

        foreach (array_keys(config('Gelita')->instrumentDefaults) as $key) {
            $value = trim((string) $this->request->getPost($key));

            if (! ctype_digit($value) || (int) $value < 1 || (int) $value > 240) {
                return $this->back('admin/studi', lang('Admin.instrumentSettingsInvalid'));
            }

            $new[$key] = (int) $value;
        }

        if ($new !== $old) {
            $studies->update($studyId, ['instrument_settings' => json_encode($new)]);
            $this->audit('study_instrument_settings_changed', 'research_study', $studyId, ['from' => $old, 'to' => $new]);
        }

        return $this->done('admin/studi', lang('Admin.instrumentSettingsSaved'));
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $action, string $targetType, int $targetId, array $metadata = []): void
    {
        model(AuditLogModel::class)->record($action, [
            'staff_user_id' => $this->staffId(),
            'target_type'   => $targetType,
            'target_id'     => (string) $targetId,
            'metadata'      => $metadata === [] ? null : $metadata,
        ]);
    }
}
