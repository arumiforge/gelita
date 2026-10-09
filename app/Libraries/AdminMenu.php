<?php

namespace App\Libraries;

/**
 * Menu panel admin (Dok 3 §8.3) — satu sumber untuk sidebar dan breadcrumb
 * topbar.
 *
 * Menu yang tidak berhak diakses TIDAK dirender untuk guru; rutenya tetap
 * dijaga filter `staffRole:admin` (404 untuk guru).
 */
final class AdminMenu
{
    /**
     * [href, kunci label Admin.*, ikon, khusus admin, kunci grup|null].
     *
     * @var list<array{0: string, 1: string, 2: string, 3: bool, 4: string|null}>
     */
    private const ITEMS = [
        ['admin',                   'Admin.menuDashboard',    'grid',      false, null],
        ['admin/peserta',           'Admin.menuParticipants', 'users',     false, 'Admin.groupData'],
        ['admin/sesi',              'Admin.menuSessions',     'clock',     false, 'Admin.groupData'],
        ['admin/data',              'Admin.menuRawData',      'database',  false, 'Admin.groupData'],
        ['admin/masukan',           'Admin.menuFeedback',     'message',   false, 'Admin.groupData'],
        ['admin/ekspor',            'Admin.menuExport',       'download',  false, 'Admin.groupData'],
        ['admin/konten',            'Admin.menuContent',      'puzzle',    true,  'Admin.groupContent'],
        ['admin/konten/impor-bank', 'Admin.menuBankImport',   'upload',    true,  'Admin.groupContent'],
        ['admin/konten/narasi',     'Admin.menuNarration',    'sound',     true,  'Admin.groupContent'],
        ['admin/media',             'Admin.menuMedia',        'image',     true,  'Admin.groupContent'],
        ['admin/media/kelengkapan', 'Admin.menuChecklist',    'clipboard', true,  'Admin.groupContent'],
        ['admin/studi',             'Admin.menuStudy',        'flask',     true,  'Admin.groupSettings'],
        ['admin/studi/rilis',       'Admin.menuReleases',     'tag',       true,  'Admin.groupSettings'],
        ['admin/sekolah',           'Admin.menuSchools',      'building',  true,  'Admin.groupSettings'],
        ['admin/staf',              'Admin.menuStaff',        'key',       true,  'Admin.groupSettings'],
        ['admin/tata-kelola',       'Admin.menuGovernance',   'shield',    true,  'Admin.groupSettings'],
    ];

    /**
     * Menu yang berhak dilihat, dikelompokkan berurutan.
     *
     * @return list<array{href: string, label: string, icon: string, group: string|null, active: bool}>
     */
    public static function items(bool $isAdmin, string $path): array
    {
        $active = self::activeHref($path);
        $out    = [];

        foreach (self::ITEMS as [$href, $label, $icon, $adminOnly, $group]) {
            if ($adminOnly && ! $isAdmin) {
                continue;
            }

            $out[] = [
                'href'   => $href,
                'label'  => lang($label),
                'icon'   => $icon,
                'group'  => $group === null ? null : lang($group),
                'active' => $href === $active,
            ];
        }

        return $out;
    }

    /**
     * Menu aktif = awalan terpanjang dari path (konten vs konten/impor-bank).
     * `admin/dashboard` adalah alias Ringkasan.
     */
    public static function activeHref(string $path): ?string
    {
        $path = trim($path, '/');

        if ($path === 'admin/dashboard') {
            return 'admin';
        }

        $active = null;

        foreach (self::ITEMS as [$href]) {
            $matches = $href === 'admin'
                ? $path === 'admin'
                : ($path === $href || str_starts_with($path, $href . '/'));

            if ($matches && strlen($href) > strlen((string) $active)) {
                $active = $href;
            }
        }

        return $active;
    }

    /**
     * Breadcrumb topbar: Panel › grup › menu › (judul halaman bila berbeda).
     * Halaman dapat menambahkan langkah sendiri lewat `$extra`
     * ([label => href|null]).
     *
     * @param array<string, string|null> $extra
     *
     * @return list<array{label: string, href: string|null}>
     */
    public static function crumbs(string $path, ?string $pageTitle, array $extra = []): array
    {
        $crumbs = [['label' => lang('Admin.panelTitle'), 'href' => 'admin']];
        $active = self::activeHref($path);

        foreach (self::ITEMS as [$href, $label, , , $group]) {
            if ($href !== $active || $href === 'admin') {
                continue;
            }

            if ($group !== null) {
                $crumbs[] = ['label' => lang($group), 'href' => null];
            }

            $crumbs[] = ['label' => lang($label), 'href' => $href];
        }

        foreach ($extra as $label => $href) {
            $crumbs[] = ['label' => (string) $label, 'href' => $href];
        }

        $last = end($crumbs);

        if ($pageTitle !== null && $pageTitle !== '' && $last['label'] !== $pageTitle && $extra === []) {
            $crumbs[] = ['label' => $pageTitle, 'href' => null];
        }

        // Langkah terakhir adalah halaman ini: bukan tautan
        $crumbs[array_key_last($crumbs)]['href'] = null;

        return $crumbs;
    }
}
