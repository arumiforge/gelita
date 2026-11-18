<?php

namespace App\Controllers\Admin;

use App\Libraries\GelitaGuideContent as Guide;
use App\Libraries\GuideSourceCheck;
use App\Libraries\StaffAccess;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * GELITA guide (/admin/panduan-gelita, "Mengenal GELITA"): the whole
 * project and research from beginning to end, with the reasons and
 * references for every decision (owner, 17 November 2026). Public and in
 * full detail, offered on the admin login page beside the staff and
 * validator guides.
 *
 * Same layout and views as the other guides (layouts/guide, admin/guide/*),
 * without a reader picker: every chapter is for every reader. Signed-in
 * staff see a note on a page whose source documents changed since the
 * guide was last checked (GuideSourceCheck::forGelitaGuide()).
 */
class GelitaGuideController extends BaseAdminController
{
    public function index(): string
    {
        $chapters = Guide::chapters();
        $sections = Guide::sectionsById($chapters);

        return view('admin/guide/gelita-overview', $this->reader() + self::site() + [
            'chapters'  => $chapters,
            'popular'   => array_values(array_filter(array_map(static fn (string $id): ?array => $sections[$id] ?? null, Guide::POPULAR))),
            'current'   => '',
            'pageTitle' => lang('Admin.gguideTitle'),
        ]);
    }

    public function page(string $slug): string
    {
        $reader   = $this->reader();
        $chapters = Guide::chapters();
        $sections = Guide::sectionsById($chapters);

        if (! isset($sections[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $ids     = array_keys($sections);
        $index   = array_search($slug, $ids, true);
        $section = $sections[$slug];

        return view('admin/guide/page', $reader + self::site() + [
            'chapters'  => $chapters,
            'section'   => $section,
            'current'   => $slug,
            'pageTitle' => $section['title'] . ' · ' . lang('Admin.gguideTitle'),
            'previous'  => $index > 0 ? $sections[$ids[$index - 1]] : null,
            'next'      => $sections[$ids[$index + 1] ?? ''] ?? null,
            'terms'     => ! empty($section['glossary']) ? [] : Guide::termsIn($section),
            'facts'     => [],
            'stale'     => $reader['isStaff'] ? array_keys(GuideSourceCheck::forGelitaGuide()->problems($section['sources'] ?? [])) : [],
        ]);
    }

    /**
     * Every reader reads the same guide; isStaff only decides the top bar
     * and the source-change notes.
     *
     * @return array{isStaff: bool, role: string, roleName: string, query: string}
     */
    private function reader(): array
    {
        $staff   = service('staffContext');
        $isStaff = $staff !== null && (int) $staff->is_active === 1 && StaffAccess::isRole((string) $staff->role)
            && (int) $staff->must_change_password !== 1;

        return ['isStaff' => $isStaff, 'role' => '', 'roleName' => '', 'query' => ''];
    }

    /** @return array<string, mixed> */
    private static function site(): array
    {
        return [
            'guide' => Guide::class,
            'site'  => [
                'title'      => lang('Admin.gguideTitle'),
                'param'      => '',
                'pickLabel'  => '',
                'options'    => [],
                'pickAlways' => false,
                'signIn'     => false,
                'mineBadge'  => '',
                'staleKey'   => 'Admin.gguideStale',
            ],
        ];
    }
}
