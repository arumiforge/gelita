<?php

namespace App\Controllers\Admin;

use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent;

/**
 * Staff guide (/admin/panduan-staf): one static page that explains the
 * panel per role. Public (owner, 16 November 2026): it is offered beside the
 * login card, so a new staff member can read it before signing in. It holds
 * no data and no secrets (StaffGuideTest).
 *
 * A signed-in staff member reads it in the panel layout with their own role
 * first and open. A guest reads it in a plain layout and may pick a role
 * (`?peran=`); without one, only the chapters for everyone start open. Text
 * lives in App\Libraries\StaffGuideContent.
 */
class StaffGuideController extends BaseAdminController
{
    public function index(): string
    {
        $staff   = service('staffContext');
        $isStaff = $staff !== null && (int) $staff->is_active === 1 && StaffAccess::isRole((string) $staff->role)
            && (int) $staff->must_change_password !== 1;

        if ($isStaff) {
            $role = (string) $staff->role;

            return $this->panel('admin/guide/index', lang('Admin.guideTitle'), [
                'chapters' => self::order(StaffGuideContent::chapters(), $role),
                'role'     => $role,
                'roleName' => StaffAccess::label($role),
                'isStaff'  => true,
            ]);
        }

        $picked = (string) ($this->request->getGet('peran') ?? '');
        $role   = StaffAccess::isRole($picked) ? $picked : '';

        return view('admin/guide/index', [
            'chapters' => self::order(StaffGuideContent::chapters(), $role),
            'role'     => $role,
            'roleName' => $role === '' ? '' : StaffAccess::label($role),
            'isStaff'  => false,
        ]);
    }

    /**
     * Chapters for everyone keep their place at the start and end; the
     * chapters written for the reader's role come right after the first one,
     * then the other roles' chapters, folded.
     *
     * @param list<array<string, mixed>> $chapters
     *
     * @return list<array<string, mixed>>
     */
    public static function order(array $chapters, string $role): array
    {
        $all   = StaffAccess::ROLES;
        $first = [];
        $own   = [];
        $other = [];
        $last  = [];

        foreach ($chapters as $index => $chapter) {
            $forAll          = array_diff($all, $chapter['roles']) === [];
            $chapter['open'] = $forAll || in_array($role, $chapter['roles'], true);
            $chapter['mine'] = $chapter['open'] && ! $forAll;

            if ($forAll) {
                $index === 0 ? $first[] = $chapter : $last[] = $chapter;
            } elseif ($chapter['open']) {
                $own[] = $chapter;
            } else {
                $other[] = $chapter;
            }
        }

        // The ketua's own chapter before the shared field-work chapter
        usort($own, static fn (array $a, array $b): int => count($a['roles']) <=> count($b['roles']));

        return [...$first, ...$own, ...$other, ...$last];
    }
}
