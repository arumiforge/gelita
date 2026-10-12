<?php

namespace App\Controllers\Admin;

use App\Models\AssetPackageModel;
use App\Models\AuditLogModel;
use App\Models\ResearchStudyModel;
use App\Services\AssetPackageBuilder;
use App\Services\AssetPackageService;
use App\Services\ReleaseActivation;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use Throwable;

/**
 * Rilis → Paket aset (docs/rencana-paket-aset.md §6.3, §8.7, §7.2): the
 * package mode switch, the batch build with progress, the build summary, the
 * changed-asset notice, the lab online permission, and staff preview links.
 * Admin only, like activating a release.
 *
 * Package mode is not a setting of its own: switching on publishes a package
 * by creating and activating a release that names it, switching off
 * activates a release without one (ReleaseActivation, the same flow and
 * running-study lock as Rilis).
 *
 * The build has no job queue: the page drives it with one request per batch
 * (public/assets/js/admin/package-build.js). Without JavaScript the same form
 * processes batches for up to NOJS_SECONDS and comes back to the page with a
 * Lanjutkan button.
 */
class PackageController extends BaseAdminController
{
    private const PAGE = 'admin/studi/rilis/paket';

    /** Time budget of one batch request driven by the page. */
    private const BATCH_SECONDS = 4;

    /** Time budget of one batch request without JavaScript. */
    private const NOJS_SECONDS = 20;

    /** Files per builder step inside one batch request. */
    private const STEP_FILES = 5;

    public function index(): string
    {
        $packages = service('assetPackages');
        $model    = model(AssetPackageModel::class);
        $code     = $packages->releasePackageCode();
        $building = $packages->runningBuild();
        // While a build runs, an older unpublished build is not offered for publishing
        $pending  = $building === null ? $this->pendingPackage($code) : null;
        $history  = $model->where('status !=', AssetPackageModel::STATUS_BUILDING)->orderBy('id', 'DESC')->findAll(10);
        $preview  = session(AssetPackageService::PREVIEW_SESSION_KEY);

        return $this->panel('admin/study/package', lang('Admin.packageTitle'), [
            'breadcrumb'   => [lang('Admin.packageTitle') => null],
            'enabled'      => $packages->enabled(),
            'activeCode'   => $code,
            'active'       => $code === null ? null : $packages->summary($code),
            'verification' => $code === null ? null : $packages->verification($code),
            'building'     => $building === null ? null : $building + ['progress' => $this->builder()->progress((string) $building['package_code'])],
            'pending'      => $pending === null ? null : $packages->summary($pending),
            'changed'      => $packages->changedSinceActive(),
            'onlineLab'    => $packages->onlineLabAllowed(),
            'history'      => $history,
            'preview'      => is_string($preview) ? $preview : null,
            'runningStudy' => model(ResearchStudyModel::class)->activeStudy(),
        ]);
    }

    /**
     * Nyalakan / Bangun ulang paket: start a build from everything active now.
     * A build already in progress is resumed instead of starting a second one.
     */
    public function startBuild(): ResponseInterface
    {
        $packages = service('assetPackages');
        $running  = $packages->runningBuild();

        try {
            $progress = $running !== null
                ? $this->builder()->progress((string) $running['package_code'])
                : $this->builder()->start(['staff_id' => $this->staffId()]);
        } catch (Throwable $e) {
            log_message('error', 'Package build could not start: {message}', ['message' => $e->getMessage()]);

            return $this->answer(false, lang('Admin.packageBuildStartFailed'));
        }

        if ($this->request->isAJAX()) {
            return $this->ok($this->progressData($progress));
        }

        return redirect()->to(site_url(self::PAGE))->with('message', lang('Admin.packageBuildStarted'));
    }

    /**
     * One batch of a running build (POST, driven by the page). When the last
     * file is done the package is finished (manifest + URL map) and the page
     * shows the summary.
     */
    public function stepBuild(string $code): ResponseInterface
    {
        $row = AssetPackageService::validCode($code) ? model(AssetPackageModel::class)->findByCode($code) : null;

        if ($row === null) {
            return $this->answer(false, lang('Admin.packageNotFound'));
        }

        try {
            if ($row['status'] === AssetPackageModel::STATUS_BUILDING) {
                $progress = $this->runBatch($code, $this->request->isAJAX() ? self::BATCH_SECONDS : self::NOJS_SECONDS);

                if ($progress['finished']) {
                    $this->finishOnce($code);
                }
            }
        } catch (Throwable $e) {
            log_message('error', 'Package build {code} failed: {message}', ['code' => $code, 'message' => $e->getMessage()]);

            return $this->answer(false, lang('Admin.packageBuildFailed'));
        }

        $row      = model(AssetPackageModel::class)->findByCode($code);
        $finished = $row !== null && $row['status'] !== AssetPackageModel::STATUS_BUILDING;

        if ($this->request->isAJAX()) {
            return $this->ok($finished
                ? ['package_code' => $code, 'done' => (int) $row['file_count'], 'total' => (int) $row['file_count'], 'finished' => true, 'percent' => 100]
                : $this->progressData($this->builder()->progress($code)));
        }

        return redirect()->to(site_url(self::PAGE))->with('message', lang($finished ? 'Admin.packageBuildFinished' : 'Admin.packageBuildContinue'));
    }

    /**
     * Confirm the summary: create a release with this package and activate it
     * (switch on, or publish a rebuild). Running study → confirmation + reason.
     */
    public function publish(string $code): RedirectResponse
    {
        $packages = service('assetPackages');
        $row      = AssetPackageService::validCode($code) ? model(AssetPackageModel::class)->findByCode($code) : null;

        if ($row === null || $row['status'] === AssetPackageModel::STATUS_BUILDING || $packages->map($code) === null) {
            return $this->back(self::PAGE, lang('Admin.packageNotFound'));
        }

        if ($packages->releasePackageCode() === $code) {
            return $this->back(self::PAGE, lang('Admin.packageAlreadyActive', [$code]));
        }

        return $this->switchTo($code);
    }

    /** Matikan: activate a release without a package. */
    public function switchOff(): RedirectResponse
    {
        if (service('assetPackages')->releasePackageCode() === null) {
            return $this->back(self::PAGE, lang('Admin.packageAlreadyOff'));
        }

        return $this->switchTo(null);
    }

    /** "Izinkan mode daring untuk perangkat lab" (§8.7). */
    public function saveOnlineLab(): RedirectResponse
    {
        $allowed = (string) $this->request->getPost('online_lab_allowed') === '1';

        try {
            service('assetPackages')->setOnlineLabAllowed($allowed, $this->staffId());
        } catch (Throwable $e) {
            log_message('error', 'Lab online permission could not be saved: {message}', ['message' => $e->getMessage()]);

            return $this->back(self::PAGE, lang('Admin.packageSettingFailed'));
        }

        model(AuditLogModel::class)->record('package_online_lab', [
            'staff_user_id' => $this->staffId(),
            'target_type'   => 'asset_package',
            'metadata'      => ['online_lab_allowed' => $allowed],
        ]);

        return $this->done(self::PAGE, lang($allowed ? 'Admin.packageOnlineLabOn' : 'Admin.packageOnlineLabOff'));
    }

    /** Periksa paket: re-hash the active package now (gelita:paket:periksa). */
    public function verify(): RedirectResponse
    {
        $packages = service('assetPackages');
        $code     = $packages->releasePackageCode();

        if ($code === null) {
            return $this->back(self::PAGE, lang('Admin.packageVerifyNone'));
        }

        $result = $packages->verification($code, true);

        return $result['ok']
            ? $this->done(self::PAGE, lang('Admin.packageVerifyOk', [$code]))
            : $this->back(self::PAGE, lang('Admin.packageVerifyBad', [$code, count($result['issues'])]));
    }

    // -------------------------------------------------------------- helpers

    /** Create the release for $code (null = off) and activate it through ReleaseActivation. */
    private function switchTo(?string $code): RedirectResponse
    {
        $activation = new ReleaseActivation();
        $confirmed  = (string) $this->request->getPost('confirm_running_study') === '1';
        $reason     = ReleaseActivation::cleanReason((string) $this->request->getPost('reason'));
        $lock       = $activation->lockFor(['asset_version' => $code ?? '']);

        // Checked before the release is created, so a refusal leaves no orphan release
        if ($lock !== null && (! $confirmed || $reason === null)) {
            return $this->back(self::PAGE, lang('Admin.packageNeedsReason', [$lock['study']['name']]));
        }

        try {
            $release = $activation->createForPackage($code, $this->staffId());
            $result  = $activation->activate((int) $release['id'], [
                'confirmed' => $confirmed,
                'reason'    => $reason,
                'staff_id'  => $this->staffId(),
                'via'       => 'paket',
            ]);
        } catch (Throwable $e) {
            log_message('error', 'Package switch failed: {message}', ['message' => $e->getMessage()]);

            return $this->back(self::PAGE, lang('Admin.packageSwitchFailed'));
        }

        if ($result['status'] !== ReleaseActivation::ACTIVATED) {
            return $this->back(self::PAGE, lang('Admin.packageNeedsReason', [$result['study']['name'] ?? '']));
        }

        return $this->done(self::PAGE, $code === null
            ? lang('Admin.packageSwitchedOff', [$release['release_code']])
            : lang('Admin.packageSwitchedOn', [$code, $release['release_code']]));
    }

    /**
     * Run builder steps until the time budget is used or the plan is done.
     *
     * @return array{package_code: string, done: int, total: int, finished: bool}
     */
    private function runBatch(string $code, int $seconds): array
    {
        $until    = microtime(true) + $seconds;
        $progress = $this->builder()->progress($code);

        while (! $progress['finished'] && microtime(true) < $until) {
            $progress = $this->builder()->step($code, self::STEP_FILES);
        }

        return $progress;
    }

    /**
     * finish() exactly once, even when two batch requests (two tabs) reach
     * the end together: the second one waits for the lock and then finds the
     * package already built.
     */
    private function finishOnce(string $code): void
    {
        $packages = service('assetPackages');
        $lockPath = $packages->dataDir() . $code . '.finish.lock';
        $handle   = fopen($lockPath, 'c');

        if ($handle === false || ! flock($handle, LOCK_EX)) {
            throw new \RuntimeException("Finish lock of {$code} could not be taken.");
        }

        try {
            $row = model(AssetPackageModel::class)->findByCode($code);

            if ($row !== null && $row['status'] === AssetPackageModel::STATUS_BUILDING) {
                $this->builder()->finish($code);
            }
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
            @unlink($lockPath);
        }
    }

    /**
     * Newest finished package that was never published and is newer than
     * the package students have: the build summary waiting for confirmation.
     */
    private function pendingPackage(?string $activeCode): ?string
    {
        $model  = model(AssetPackageModel::class);
        $active = $activeCode === null ? null : $model->findByCode($activeCode);
        $query  = $model->where('status', AssetPackageModel::STATUS_BUILT)->orderBy('id', 'DESC');

        if ($active !== null) {
            $query->where('id >', (int) $active['id']);
        }

        $row = $query->first();

        return $row === null ? null : (string) $row['package_code'];
    }

    /**
     * @param array{package_code: string, done: int, total: int, finished: bool} $progress
     *
     * @return array<string, mixed>
     */
    private function progressData(array $progress): array
    {
        return $progress + [
            'percent'  => $progress['total'] > 0 ? (int) floor($progress['done'] * 100 / $progress['total']) : 0,
            'step_url' => site_url(self::PAGE . '/bangun/' . $progress['package_code'] . '/lanjut'),
        ];
    }

    /** JSON for the page's batch driver, a redirect with a message otherwise. */
    private function answer(bool $ok, string $message): ResponseInterface
    {
        if ($this->request->isAJAX()) {
            return $ok ? $this->ok(['message' => $message]) : $this->fail('PACKAGE_BUILD', $message, 409);
        }

        return $ok ? $this->done(self::PAGE, $message) : $this->back(self::PAGE, $message);
    }

    private function builder(): AssetPackageBuilder
    {
        return new AssetPackageBuilder(service('assetPackages'));
    }
}
