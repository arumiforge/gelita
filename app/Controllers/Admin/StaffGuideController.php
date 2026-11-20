<?php

namespace App\Controllers\Admin;

use App\Libraries\GuideDownloads;
use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Staff guide (/admin/panduan-staf): a small documentation site that
 * explains the panel per role. Public (owner, 16 November 2026): it is
 * offered beside the login card, so a new staff member can read it before
 * signing in. It holds no data and no secrets (StaffGuideTest).
 *
 * Docs layout (owner, 16 November 2026, after the Claude Code docs): a menu
 * of chapters and topics on the left, one page per topic
 * (/admin/panduan-staf/{section id}), a first page with cards, and
 * previous/next links. Everyone reads it in layouts/guide; a signed-in
 * staff member has their role's chapter first and a way back to the panel.
 * A guest may pick a role (`?peran=`), which the guide's links carry along.
 * Text lives in App\Libraries\StaffGuideContent.
 *
 * Downloads (owner, 20 November 2026): the print versions of the research
 * documents for field work and the blank field sheets, listed on the topic
 * page marked `downloads` and served by download() from GuideDownloads.
 */
class StaffGuideController extends BaseAdminController
{
    public function index(): string
    {
        $reader   = $this->reader();
        $chapters = self::order(StaffGuideContent::chapters(), $reader['role']);
        $sections = StaffGuideContent::sectionsById($chapters);

        return view('admin/guide/overview', $reader + $this->site() + [
            'chapters' => $chapters,
            'popular'  => array_values(array_filter(array_map(static fn (string $id): ?array => $sections[$id] ?? null, StaffGuideContent::POPULAR))),
            'current'   => '',
            'pageTitle' => lang('Admin.guideTitle'),
        ]);
    }

    public function page(string $slug): string
    {
        $reader   = $this->reader();
        $chapters = self::order(StaffGuideContent::chapters(), $reader['role']);
        $sections = StaffGuideContent::sectionsById($chapters);

        if (! isset($sections[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $ids   = array_keys($sections);
        $index = array_search($slug, $ids, true);

        return view('admin/guide/page', $reader + $this->site() + [
            'chapters' => $chapters,
            'section'  => $sections[$slug],
            'current'  => $slug,
            'pageTitle' => $sections[$slug]['title'] . ' · ' . lang('Admin.guideTitle'),
            'previous' => $index > 0 ? $sections[$ids[$index - 1]] : null,
            'next'     => $sections[$ids[$index + 1] ?? ''] ?? null,
            'terms'    => ! empty($sections[$slug]['glossary']) ? [] : StaffGuideContent::termsIn($sections[$slug]),
            'facts'    => [],
            'stale'    => [],
            // `downloads`: true lists the print page's group, a string names another group (GuideDownloads)
            'downloads'  => empty($sections[$slug]['downloads']) ? [] : GuideDownloads::rows(GuideDownloads::forGroup(
                GuideDownloads::STAFF,
                $sections[$slug]['downloads'] === true ? GuideDownloads::DEFAULT_GROUP : (string) $sections[$slug]['downloads'],
            )),
            'showPanels' => false,
        ]);
    }

    /** A file the staff guide offers (GuideDownloads); anything else is not found. */
    public function download(string $key): ResponseInterface
    {
        return GuideDownloads::respond(GuideDownloads::STAFF, $key, $this->request, $this->response);
    }

    /**
     * Who reads: a signed-in staff member (their role), or a guest with the
     * role they picked (or none). $query keeps a guest's pick on every link.
     *
     * @return array{isStaff: bool, role: string, roleName: string, query: string}
     */
    private function reader(): array
    {
        $staff   = service('staffContext');
        $isStaff = $staff !== null && (int) $staff->is_active === 1 && StaffAccess::isRole((string) $staff->role)
            && (int) $staff->must_change_password !== 1;

        $role = $isStaff ? (string) $staff->role : (string) ($this->request->getGet('peran') ?? '');
        $role = StaffAccess::isRole($role) ? $role : '';

        return [
            'isStaff'  => $isStaff,
            'role'     => $role,
            'roleName' => $role === '' ? '' : StaffAccess::label($role),
            'query'    => ! $isStaff && $role !== '' ? '?peran=' . $role : '',
        ];
    }

    /**
     * Chapters for everyone keep their place at the start and end; the
     * chapters written for the reader's role come right after the first one,
     * then the other roles' chapters, folded in the menu.
     *
     * @param list<array<string, mixed>> $chapters
     *
     * @return list<array<string, mixed>>
     */
    public static function order(array $chapters, string $role): array
    {
        $out = StaffGuideContent::orderFor($chapters, $role, StaffAccess::ROLES, 'roles');

        foreach ($out as &$chapter) {
            $chapter['for'] = implode(', ', array_map(static fn (string $r): string => StaffAccess::label($r), $chapter['roles']));
        }

        return $out;
    }

    /**
     * What the shared guide views need to know about this guide.
     *
     * @return array<string, mixed>
     */
    private function site(): array
    {
        return [
            'guide' => StaffGuideContent::class,
            'site'  => [
                'title'      => lang('Admin.guideTitle'),
                'param'      => 'peran',
                'pickLabel'  => lang('Admin.guidePickRole'),
                'options'    => array_combine(StaffAccess::ROLES, array_map(static fn (string $r): string => StaffAccess::label($r), StaffAccess::ROLES)),
                'pickAlways' => false,
                'signIn'     => true,
                'mineBadge'  => lang('Admin.guideYourRole'),
            ],
        ];
    }
}
