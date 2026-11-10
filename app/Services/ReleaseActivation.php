<?php

namespace App\Services;

use App\Models\AssetPackageModel;
use App\Models\AuditLogModel;
use App\Models\GameReleaseModel;
use App\Models\ResearchStudyModel;
use RuntimeException;

/**
 * Activating a game release, shared by Rilis (`StudyController`) and the
 * package switch on Rilis → Paket aset (`PackageController`), so both go
 * through the same running-study lock and the same audit entry
 * (docs/rencana-paket-aset.md §6.3, §7.2, owner decision P10).
 *
 * Running-study lock: while a study is *berjalan*, activating a release whose
 * asset package differs from the package students get now (switching package
 * mode on or off, or publishing a rebuilt package) is refused unless the
 * admin ticks a confirmation and writes a reason. Releases that keep the same
 * package (e.g. a new content version) need no confirmation, and neither does
 * going back to the package the study locked when it started
 * (`research_studies.package_code`; owner, 9 November 2026).
 *
 * Every activation is audited as `release_activate` with `release_code`,
 * `package_from`, `package_to`, `reason` (null when none was needed), and the
 * running study's code.
 */
class ReleaseActivation
{
    public const ACTIVATED          = 'activated';
    public const NEEDS_CONFIRMATION = 'needs_confirmation';
    public const NOT_FOUND          = 'not_found';

    /** Longest reason kept in the audit log. */
    public const REASON_MAX = 500;

    private AssetPackageService $packages;

    public function __construct(?AssetPackageService $packages = null)
    {
        $this->packages = $packages ?? service('assetPackages');
    }

    /**
     * What activating a release with this asset_version would change for a
     * running study, or null when no confirmation is needed (no running
     * study, or the package stays the same).
     *
     * @param array<string, mixed> $target release row (only asset_version is read)
     *
     * @return array{study: array<string, mixed>, package_from: string|null, package_to: string|null}|null
     */
    public function lockFor(array $target): ?array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();

        if ($study === null) {
            return null;
        }

        $from = $this->packages->releasePackageCode();
        $to   = $this->packages->packageOfRelease($target);

        return self::needsConfirmation($study, $from, $to) ? ['study' => $study, 'package_from' => $from, 'package_to' => $to] : null;
    }

    /**
     * The running study when students now get another package than the one
     * it locked (research-ready checklist, `gelita:content:verify
     * --siap-penelitian`), else null.
     *
     * @return array<string, mixed>|null
     */
    public function driftedStudy(): ?array
    {
        $study = model(ResearchStudyModel::class)->activeStudy();

        return $study !== null && ($study['package_code'] ?? null) !== $this->packages->activeCode() ? $study : null;
    }

    /**
     * Whether moving students from package $from to $to needs the tick and a
     * reason: only during a running study, only when the package changes, and
     * not when $to is the package the study locked (NULL = no package).
     *
     * @param array<string, mixed>|null $study the running study, or null
     */
    public static function needsConfirmation(?array $study, ?string $from, ?string $to): bool
    {
        return $study !== null && $from !== $to && $to !== ($study['package_code'] ?? null);
    }

    /**
     * Activate a release, unless the running-study lock needs a confirmation
     * that was not given.
     *
     * @param array{confirmed?: bool, reason?: string|null, staff_id?: int|null, via?: string} $options
     *
     * @return array{status: string, release: array<string, mixed>|null, package_from: string|null,
     *               package_to: string|null, study: array<string, mixed>|null}
     */
    public function activate(int $releaseId, array $options = []): array
    {
        $releases = model(GameReleaseModel::class);
        $target   = $releases->find($releaseId);

        if ($target === null) {
            return ['status' => self::NOT_FOUND, 'release' => null, 'package_from' => null, 'package_to' => null, 'study' => null];
        }

        $from   = $this->packages->releasePackageCode();
        $to     = $this->packages->packageOfRelease($target);
        $lock   = $this->lockFor($target);
        $reason = self::cleanReason($options['reason'] ?? null);

        if ($lock !== null && (($options['confirmed'] ?? false) !== true || $reason === null)) {
            return ['status' => self::NEEDS_CONFIRMATION, 'release' => $target, 'package_from' => $from, 'package_to' => $to, 'study' => $lock['study']];
        }

        if (! $releases->activate($releaseId)) {
            throw new RuntimeException("Release {$releaseId} could not be activated.");
        }

        // content_version is part of the content cache key; the package memo follows the release
        service('contentRepository')->flush();
        $this->packages->forget();
        $this->syncPackageStatuses($to);

        $study = model(ResearchStudyModel::class)->activeStudy();

        model(AuditLogModel::class)->record('release_activate', [
            'staff_user_id' => $options['staff_id'] ?? null,
            'target_type'   => 'game_release',
            'target_id'     => (string) $releaseId,
            'metadata'      => [
                'release_code'  => $target['release_code'],
                'package_from'  => $from,
                'package_to'    => $to,
                'reason'        => $lock === null ? null : $reason,
                'running_study' => $study['code'] ?? null,
                'via'           => $options['via'] ?? 'rilis',
            ],
        ]);

        return ['status' => self::ACTIVATED, 'release' => $target, 'package_from' => $from, 'package_to' => $to, 'study' => $study];
    }

    /**
     * New inactive release for the package switch (§6.3): same app, content,
     * and scoring version as the active release, asset_version = the package
     * code, or '' for "no package". Release code = the package code, or
     * `tanpa-paket-YYYY-MM-DD-NN`, with a numeric suffix when taken.
     *
     * @return array<string, mixed> the new release row
     */
    public function createForPackage(?string $packageCode, ?int $staffId = null): array
    {
        $releases = model(GameReleaseModel::class);
        $active   = $releases->activeOrNull();

        if ($active === null) {
            throw new RuntimeException('There is no active release to copy the versions from.');
        }

        $base = $packageCode ?? 'tanpa-paket-' . date('Y-m-d');
        $code = $packageCode ?? $base . '-01';

        for ($n = 2; $releases->where('release_code', $code)->countAllResults() > 0; $n++) {
            $code = $base . '-' . str_pad((string) $n, 2, '0', STR_PAD_LEFT);
        }

        $note = $packageCode === null
            ? lang('Admin.packageAutoReleaseOff', [$active['release_code']])
            : lang('Admin.packageAutoReleaseOn', [$packageCode, $active['release_code']]);

        $id = $releases->insert([
            'release_code'    => $code,
            'app_version'     => $active['app_version'],
            'content_version' => $active['content_version'],
            'asset_version'   => $packageCode ?? '',
            'scoring_version' => $active['scoring_version'],
            'notes'           => $note,
            'is_active'       => 0,
        ], true);

        if ($id === false) {
            throw new RuntimeException('Release could not be created: ' . implode(' ', $releases->errors()));
        }

        model(AuditLogModel::class)->record('release_create', [
            'staff_user_id' => $staffId,
            'target_type'   => 'game_release',
            'target_id'     => (string) $id,
            'metadata'      => ['release_code' => $code, 'package_code' => $packageCode, 'automatic' => true],
        ]);

        return $releases->find($id);
    }

    /**
     * Package statuses follow the active release: the package it names is
     * `terbit`, a package that was `terbit` before becomes `ditarik`, and
     * packages that were never published stay `dibangun`.
     */
    private function syncPackageStatuses(?string $active): void
    {
        $model = model(AssetPackageModel::class);

        $withdraw = $model->builder()->where('status', AssetPackageModel::STATUS_PUBLISHED);

        if ($active !== null) {
            $withdraw->where('package_code !=', $active);
        }

        $withdraw->update(['status' => AssetPackageModel::STATUS_WITHDRAWN, 'updated_at' => date('Y-m-d H:i:s')]);

        if ($active !== null) {
            $model->builder()->where('package_code', $active)->update([
                'status'       => AssetPackageModel::STATUS_PUBLISHED,
                'published_at' => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /** Trimmed reason, or null when empty. */
    public static function cleanReason(?string $reason): ?string
    {
        $reason = trim((string) $reason);

        return $reason === '' ? null : mb_substr($reason, 0, self::REASON_MAX);
    }
}
