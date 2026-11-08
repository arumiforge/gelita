<?php

namespace App\Libraries;

/**
 * Menu panel admin (Dok 3 §8.3) — satu sumber untuk sidebar dan breadcrumb
 * topbar.
 *
 * An item shows only when the role holds one of its capabilities
 * (App\Libraries\StaffAccess, revision 10 part 1, 27 §5.4); the route is
 * still guarded by the `staffCan` filter (404 without the right).
 */
final class AdminMenu
{
    /**
     * [href, kunci label Admin.*, ikon, kemampuan (any one), kunci grup|null].
     *
     * @var list<array{0: string, 1: string, 2: string, 3: list<string>, 4: string|null}>
     */
    private const ITEMS = [
        ['admin',                     'Admin.menuDashboard',    'grid',      ['dashboard.view'], null],
        ['admin/peserta',             'Admin.menuParticipants', 'users',     ['participants.accounts', 'participants.research'], 'Admin.groupData'],
        ['admin/peserta/reset-sandi', 'Admin.menuResetByCode',  'key',       ['participants.reset_by_code'], 'Admin.groupData'],
        // Research revision 10, part 2 (28 §15): the slots reserved in 27 §8
        ['admin/sesi-lapangan',       'Admin.menuFieldSessions', 'clock',    ['field_sessions.manage'], 'Admin.groupData'],
        ['admin/pemantauan',          'Admin.menuMonitoring',   'eye',       ['monitoring.view'], 'Admin.groupData'],
        ['admin/sesi',                'Admin.menuSessions',     'clock',     ['research.browse'], 'Admin.groupData'],
        ['admin/data',                'Admin.menuRawData',      'database',  ['research.browse'], 'Admin.groupData'],
        ['admin/masukan',             'Admin.menuFeedback',     'message',   ['research.browse'], 'Admin.groupData'],
        ['admin/ekspor',              'Admin.menuExport',       'download',  ['research.export'], 'Admin.groupData'],
        // Part 3 (29 §11): the slot reserved in 27 §8
        ['admin/log-teknis',          'Admin.menuTechLog',      'database',  ['tech_logs.view'], 'Admin.groupData'],
        // Part 4 (30 §14): shown only while a field-mode study exists (FIELD_MODE_ONLY)
        ['admin/permintaan-data',     'Admin.menuDataRequests', 'shield',    ['deletion.request', 'deletion.confirm', 'deletion.execute'], 'Admin.groupData'],
        ['admin/tindak-lanjut',       'Admin.menuRiskFollowUp', 'message',   ['risk_flags.view'], 'Admin.groupData'],
        ['admin/konten',              'Admin.menuContent',      'puzzle',    ['content.view'], 'Admin.groupContent'],
        ['admin/konten/impor-bank',   'Admin.menuBankImport',   'upload',    ['content.edit'], 'Admin.groupContent'],
        ['admin/konten/narasi',       'Admin.menuNarration',    'sound',     ['content.view'], 'Admin.groupContent'],
        ['admin/media',               'Admin.menuMedia',        'image',     ['content.view'], 'Admin.groupContent'],
        ['admin/media/kelengkapan',   'Admin.menuChecklist',    'clipboard', ['content.view'], 'Admin.groupContent'],
        ['admin/studi',               'Admin.menuStudy',        'flask',     ['studies.view'], 'Admin.groupSettings'],
        ['admin/studi/rilis',         'Admin.menuReleases',     'tag',       ['releases.manage'], 'Admin.groupSettings'],
        ['admin/sekolah',             'Admin.menuSchools',      'building',  ['schools.manage'], 'Admin.groupSettings'],
        ['admin/staf',                'Admin.menuStaff',        'key',       ['staff.manage'], 'Admin.groupSettings'],
        ['admin/tata-kelola',         'Admin.menuGovernance',   'shield',    ['deletion.execute', 'audit.view', 'retention.run'], 'Admin.groupSettings'],
    ];

    /** Part 4 (30 §0.1): items that show only while a study with field mode exists. */
    private const FIELD_MODE_ONLY = ['admin/permintaan-data', 'admin/tindak-lanjut'];

    private static function fieldModeStudyExists(): bool
    {
        try {
            // A plain builder: a failed query must not leave a condition on the shared model
            return db_connect()->table('research_studies')->where('field_mode', 1)->countAllResults() > 0;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Menu yang berhak dilihat, dikelompokkan berurutan.
     *
     * @param callable(string): bool $can capability check of the signed-in staff member
     *
     * @return list<array{href: string, label: string, icon: string, group: string|null, active: bool}>
     */
    public static function items(callable $can, string $path): array
    {
        $active = self::activeHref($path);
        $out    = [];

        foreach (self::ITEMS as [$href, $label, $icon, $capabilities, $group]) {
            if (array_filter($capabilities, $can) === []) {
                continue;
            }

            if (in_array($href, self::FIELD_MODE_ONLY, true) && ! self::fieldModeStudyExists()) {
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
