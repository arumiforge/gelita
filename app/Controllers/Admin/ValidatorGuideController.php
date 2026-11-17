<?php

namespace App\Controllers\Admin;

use App\Libraries\GuideSourceCheck;
use App\Libraries\StaffAccess;
use App\Libraries\ValidatorGuideContent as Guide;
use App\Libraries\ValidatorGuideFacts;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * Validator guide (/admin/panduan-validator): what the expert validators
 * need from the validation package, per panel (ahli materi, the two B.5a
 * raters, pengamat budaya, ahli media). Public like the staff guide and
 * offered beside the admin login card (owner, 17 November 2026):
 * validators hold student test accounts, never staff accounts.
 *
 * Same layout and views as the staff guide (layouts/guide, admin/guide/*),
 * with a panel picker (`?panel=`) for every reader. Two things keep it
 * current: live facts of this install (ValidatorGuideFacts) and, for
 * signed-in staff only, a note on a section whose research-document
 * sources changed since the guide was last checked (GuideSourceCheck).
 */
class ValidatorGuideController extends BaseAdminController
{
    public function index(): string
    {
        $reader   = $this->reader();
        $chapters = self::order(Guide::chapters(), $reader['role']);
        $sections = Guide::sectionsById($chapters);

        return view('admin/guide/validator-overview', $reader + self::site() + [
            'chapters'  => $chapters,
            'popular'   => array_values(array_filter(array_map(static fn (string $id): ?array => $sections[$id] ?? null, Guide::POPULAR))),
            'current'   => '',
            'pageTitle' => lang('Admin.vguideTitle'),
        ]);
    }

    public function page(string $slug): string
    {
        $reader   = $this->reader();
        $chapters = self::order(Guide::chapters(), $reader['role']);
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
            'pageTitle' => $section['title'] . ' · ' . lang('Admin.vguideTitle'),
            'previous'  => $index > 0 ? $sections[$ids[$index - 1]] : null,
            'next'      => $sections[$ids[$index + 1] ?? ''] ?? null,
            'terms'     => ! empty($section['glossary']) ? [] : Guide::termsIn($section),
            'facts'     => (new ValidatorGuideFacts())->rows($section['facts'] ?? []),
            'stale'     => $reader['isStaff'] ? array_keys(GuideSourceCheck::forValidatorGuide()->problems($section['sources'] ?? [])) : [],
        ]);
    }

    /**
     * Panel chapters first for the picked panel; chapters for every panel
     * keep their place. Adds `for` (the panels a folded chapter is for).
     *
     * @param list<array<string, mixed>> $chapters
     *
     * @return list<array<string, mixed>>
     */
    public static function order(array $chapters, string $panel): array
    {
        $out = Guide::orderFor($chapters, $panel, array_keys(Guide::PANELS), 'panels');

        foreach ($out as &$chapter) {
            $chapter['for'] = implode(', ', array_map(static fn (string $p): string => Guide::PANELS[$p], $chapter['panels']));
        }

        return $out;
    }

    /**
     * Who reads: the panel picked with ?panel= (staff and guests alike;
     * validators never sign in here). isStaff only decides the top bar and
     * the source-change notes.
     *
     * @return array{isStaff: bool, role: string, roleName: string, query: string}
     */
    private function reader(): array
    {
        $staff   = service('staffContext');
        $isStaff = $staff !== null && (int) $staff->is_active === 1 && StaffAccess::isRole((string) $staff->role)
            && (int) $staff->must_change_password !== 1;

        $panel = (string) ($this->request->getGet('panel') ?? '');
        $panel = isset(Guide::PANELS[$panel]) ? $panel : '';

        return [
            'isStaff'  => $isStaff,
            'role'     => $panel,
            'roleName' => $panel === '' ? '' : Guide::PANELS[$panel],
            'query'    => $panel !== '' ? '?panel=' . $panel : '',
        ];
    }

    /** @return array<string, mixed> */
    private static function site(): array
    {
        return [
            'guide' => Guide::class,
            'site'  => [
                'title'      => lang('Admin.vguideTitle'),
                'param'      => 'panel',
                'pickLabel'  => lang('Admin.vguidePickPanel'),
                'options'    => Guide::PANELS,
                'pickAlways' => true,
                'signIn'     => false,
                'mineBadge'  => lang('Admin.vguideYourPanel'),
            ],
        ];
    }
}
