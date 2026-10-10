<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AssetPackageModel;
use App\Services\AssetPackageService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * `GET /api/paket/aktif` (docs/rencana-paket-aset.md §5.3, §6.3): which asset
 * package devices should hold right now. Small, never cached, and open without
 * login, because the device checks it on the welcome screen before Mulai.
 * It only returns public package metadata, no student data.
 *
 * `package_code` is null when package mode is off: the active release names
 * no built package, or the server kill switch `gelita.paket.aktif` is false
 * (then `disabled_by_server` is true).
 */
class PackageApiController extends BaseController
{
    public function active(): ResponseInterface
    {
        $packages = service('assetPackages');
        $code     = $packages->activeCode();
        $row      = $code === null ? null : model(AssetPackageModel::class)->findByCode($code);
        $map      = $code === null ? null : $packages->map($code);

        $this->response->setHeader('Cache-Control', 'no-store');

        if ($code === null || $map === null) {
            return $this->ok([
                'package_code'       => null,
                'manifest_url'       => null,
                'manifest_sha256'    => null,
                'total_bytes'        => null,
                'file_count'         => null,
                'disabled_by_server' => ! $packages->enabled(),
            ]);
        }

        return $this->ok([
            'package_code'       => $code,
            'manifest_url'       => AssetPackageService::manifestUrl($code),
            'manifest_sha256'    => (string) $map['manifest_sha256'],
            'total_bytes'        => $row === null ? null : (int) $row['total_bytes'],
            'file_count'         => $row === null ? null : (int) $row['file_count'],
            'disabled_by_server' => false,
        ]);
    }
}
