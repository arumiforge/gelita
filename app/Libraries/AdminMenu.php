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
     * [href, Admin.* label key, icon, capabilities (any one), group key|null].
     *
     * Groups (staff panel guide pass, 16 November 2026): field work first,
     * because that is what researchers open under time pressure; content
     * production is folded away for roles that cannot edit it.
     *
     * @var list<array{0: string, 1: string, 2: string, 3: list<string>, 4: string|null}>
     */
    private const ITEMS = [
        ['admin',                     'Admin.menuDashboard',    'grid',      ['dashboard.view'], null],
        // Research revision 10, part 2 (28 §15): the slots reserved in 27 §8
        ['admin/sesi-lapangan',       'Admin.menuFieldSessions', 'clock',    ['field_sessions.manage'], 'Admin.groupField'],
        ['admin/pemantauan',          'Admin.menuMonitoring',   'eye',       ['monitoring.view'], 'Admin.groupField'],
        ['admin/peserta',             'Admin.menuParticipants', 'users',     ['participants.accounts', 'participants.research'], 'Admin.groupField'],
        ['admin/peserta/reset-sandi', 'Admin.menuResetByCode',  'key',       ['participants.reset_by_code'], 'Admin.groupField'],
        ['admin/sesi',                'Admin.menuSessions',     'clock',     ['research.browse'], 'Admin.groupData'],
        ['admin/data',                'Admin.menuRawData',      'database',  ['research.browse'], 'Admin.groupData'],
        ['admin/masukan',             'Admin.menuFeedback',     'message',   ['research.browse'], 'Admin.groupData'],
        ['admin/ekspor',              'Admin.menuExport',       'download',  ['research.export'], 'Admin.groupData'],
        // Part 4 (30 §14): shown only while a field-mode study exists (FIELD_MODE_ONLY)
        ['admin/permintaan-data',     'Admin.menuDataRequests', 'shield',    ['deletion.request', 'deletion.confirm', 'deletion.execute'], 'Admin.groupData'],
        ['admin/tindak-lanjut',       'Admin.menuRiskFollowUp', 'message',   ['risk_flags.view'], 'Admin.groupData'],
        // Part 3 (29 §11): the slot reserved in 27 §8
        ['admin/log-teknis',          'Admin.menuTechLog',      'database',  ['tech_logs.view'], 'Admin.groupData'],
        ['admin/konten',              'Admin.menuContent',      'puzzle',    ['content.view'], 'Admin.groupContent'],
        ['admin/konten/impor-bank',   'Admin.menuBankImport',   'upload',    ['content.edit'], 'Admin.groupContent'],
        ['admin/konten/narasi',       'Admin.menuNarration',    'sound',     ['content.view'], 'Admin.groupContent'],
        ['admin/media',               'Admin.menuMedia',        'image',     ['content.view'], 'Admin.groupContent'],
        ['admin/media/kelengkapan',   'Admin.menuChecklist',    'clipboard', ['content.view'], 'Admin.groupContent'],
        ['admin/studi',               'Admin.menuStudy',        'flask',     ['studies.view'], 'Admin.groupSettings'],
        // Head researcher only (owner, 17 November 2026): status of the research-revision documents
        ['admin/status-dokumen',      'Admin.menuDocStatus',    'book',      ['research_docs.view'], 'Admin.groupSettings'],
        ['admin/studi/rilis',         'Admin.menuReleases',     'tag',       ['releases.manage'], 'Admin.groupSettings'],
        ['admin/sekolah',             'Admin.menuSchools',      'building',  ['schools.manage'], 'Admin.groupSettings'],
        ['admin/staf',                'Admin.menuStaff',        'key',       ['staff.manage'], 'Admin.groupSettings'],
        ['admin/tata-kelola',         'Admin.menuGovernance',   'shield',    ['deletion.execute', 'audit.view', 'retention.run'], 'Admin.groupSettings'],
        // Admin only (owner, 18 November 2026): PHP, extensions, php.ini, database, folders
        ['admin/cek-server',          'Admin.menuServerCheck',  'check',     ['server.check'], 'Admin.groupSettings'],
        // Every role: the staff guide (/admin/panduan-staf)
        ['admin/panduan-staf',        'Admin.menuGuide',        'book',      ['dashboard.view'], 'Admin.groupHelp'],
    ];

    /**
     * Groups folded away by default unless the role holds the capability
     * (or the open page is inside): researchers rarely edit content in the
     * field (recommendation 3 of the field-day pass, PR #388).
     */
    private const COLLAPSED_UNLESS = ['Admin.groupContent' => 'content.edit'];

    /**
     * Sub-menu under an item while a page below it is open:
     * [item href, path pattern with the id, [[suffix, label key, icon, capability]]].
     * Only pages every session has; the make-up form stays a button on the
     * session page because it exists only for a closed main session.
     */
    private const CHILDREN = [
        ['admin/sesi-lapangan', '#^admin/sesi-lapangan/(\d+)(?:/|$)#', [
            ['',               'Admin.menuSessionOverview', 'clock',     'field_sessions.manage'],
            ['/izin',          'Admin.consentTitle',        'shield',    'field_sessions.manage'],
            ['/kursi',         'Admin.fieldSessionSeats',   'users',     'field_sessions.manage'],
            ['/lembar/unggah', 'Admin.menuSessionSheets',   'upload',    'field_sessions.manage'],
        ]],
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
     * The sidebar as groups: the items without a group first (Ringkasan),
     * then one entry per group with its items, whether it starts open, and
     * the sub-menu of the open item (field session pages).
     *
     * @param callable(string): bool $can capability check of the signed-in staff member
     *
     * @return list<array{key: string|null, label: string|null, open: bool, items: list<array{href: string, label: string, icon: string, active: bool, children: list<array{href: string, label: string, icon: string, active: bool}>}>}>
     */
    public static function groups(callable $can, string $path): array
    {
        $path   = trim($path, '/');
        $active = self::activeHref($path);
        $groups = [];

        foreach (self::items($can, $path) as $item) {
            $key = self::groupKeyOf($item['href']);

            if ($groups === [] || end($groups)['key'] !== $key) {
                $groups[] = [
                    'key'   => $key,
                    'label' => $item['group'],
                    'open'  => $key === null || ! isset(self::COLLAPSED_UNLESS[$key]) || $can(self::COLLAPSED_UNLESS[$key]),
                    'items' => [],
                ];
            }

            $item['children'] = $item['href'] === $active ? self::children($item['href'], $path, $can) : [];
            unset($item['group']);

            $last                     = array_key_last($groups);
            $groups[$last]['items'][] = $item;
            $groups[$last]['open']    = $groups[$last]['open'] || $item['active'];
        }

        return $groups;
    }

    /** Group key (Admin.group*) of a menu href, null for the ungrouped items. */
    private static function groupKeyOf(string $href): ?string
    {
        foreach (self::ITEMS as [$itemHref, , , , $group]) {
            if ($itemHref === $href) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Sub-menu of an item for the open path (empty when the path has none).
     *
     * @param callable(string): bool $can
     *
     * @return list<array{href: string, label: string, icon: string, active: bool}>
     */
    public static function children(string $href, string $path, callable $can): array
    {
        $path = trim($path, '/');

        foreach (self::CHILDREN as [$parent, $pattern, $children]) {
            if ($parent !== $href || preg_match($pattern, $path, $match) !== 1) {
                continue;
            }

            $base = $parent . '/' . $match[1];
            $out  = [];

            foreach ($children as [$suffix, $label, $icon, $capability]) {
                if (! $can($capability)) {
                    continue;
                }

                $childHref = $base . $suffix;
                $out[]     = [
                    'href'   => $childHref,
                    'label'  => lang($label),
                    'icon'   => $icon,
                    // The overview is current only on its own page; the others on their pages and below
                    'active' => $suffix === '' ? $path === $childHref : ($path === $childHref || str_starts_with($path, $childHref . '/')),
                ];
            }

            return $out;
        }

        return [];
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
