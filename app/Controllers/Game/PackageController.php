<?php

namespace App\Controllers\Game;

use App\Controllers\BaseController;
use App\Models\AssetPackageModel;
use App\Services\AssetPackageBuilder;
use CodeIgniter\HTTP\RedirectResponse;

/**
 * Asset package screens on the device (docs/rencana-paket-aset.md Stage 3,
 * §5.6, §8.1, §8.7, P7, P11). Both open without login, like the welcome
 * screen: what they touch is this browser's copy of the package, which holds
 * public images and sounds only (§9.1), never student data.
 *
 *   /siapkan        "Siapkan GELITA": downloads and verifies the full package
 *                   before Mulai (game/prepare.js). The welcome screen and the
 *                   pages before a game session (/mulai, /masuk, /daftar)
 *                   send the device here while it needs the package
 *                   (game/package-gate.js); the server cannot tell, since the
 *                   package lives in the browser.
 *   /periksa-paket  "Periksa paket" for teachers: full sha256 re-check,
 *                   download what is missing, remove the package
 *                   (game/package-inspect.js). Works with package mode off
 *                   too, so a device can free the space (§6.3).
 */
class PackageController extends BaseController
{
    /** Pages /siapkan may continue to (`?lanjut=`); anything else goes to /mulai. */
    private const NEXT = '#^(?:mulai|masuk|daftar(?:/[1-3])?)$#';

    public function prepare(): string|RedirectResponse
    {
        $next = (string) $this->request->getGet('lanjut');
        $next = preg_match(self::NEXT, $next) === 1 ? $next : 'mulai';

        $code = service('assetPackages')->activeCode();

        // Package mode off: no download screen (§8.7)
        if ($code === null) {
            return redirect()->to(site_url($next));
        }

        return view('game/prepare', [
            'locale' => $this->locale,
            'next'   => $next,
            'groups' => $this->groups($code),
        ]);
    }

    public function inspect(): string
    {
        return view('game/package-inspect', ['locale' => $this->locale]);
    }

    /**
     * Parts of the package in download order (§5.1) with their size, for the
     * progress rows. Empty groups are left out, as the downloader does.
     *
     * @return list<array{name: string, label: string, bytes: int}>
     */
    private function groups(string $code): array
    {
        $row   = model(AssetPackageModel::class)->findByCode($code);
        $sizes = array_map('intval', (array) json_decode((string) ($row['groups_json'] ?? '[]'), true));
        $order = array_flip(AssetPackageBuilder::groupOrder());

        uksort($sizes, static fn (string $a, string $b): int => ($order[$a] ?? PHP_INT_MAX) <=> ($order[$b] ?? PHP_INT_MAX) ?: strcmp($a, $b));

        $groups = [];

        foreach ($sizes as $name => $bytes) {
            if ($bytes > 0) {
                $groups[] = ['name' => (string) $name, 'label' => self::groupLabel((string) $name), 'bytes' => $bytes];
            }
        }

        return $groups;
    }

    /** "Kode permainan", "Inti (semua layar)", "Wilayah Temanggung", "Penutup". */
    public static function groupLabel(string $group): string
    {
        if (str_starts_with($group, 'wilayah-')) {
            return lang('Package.groupRegion', [ucfirst(substr($group, 8))]);
        }

        return match ($group) {
            'kode'    => lang('Package.groupKode'),
            'inti'    => lang('Package.groupInti'),
            'penutup' => lang('Package.groupPenutup'),
            default   => ucfirst($group),
        };
    }
}
