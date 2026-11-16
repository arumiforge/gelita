<?php

namespace App\Controllers\Admin;

use App\Libraries\StaffAccess;
use App\Libraries\StaffGuideContent;

/**
 * Staff guide (/admin/panduan-staf): one static page that explains the
 * panel per role, open to every staff role (dashboard.view).
 *
 * The reader's own role comes first and open; chapters written only for
 * other roles follow, folded. Text lives in App\Libraries\StaffGuideContent.
 */
class StaffGuideController extends BaseAdminController
{
    public function index(): string
    {
        $role     = (string) session('staff_role');
        $chapters = self::order(StaffGuideContent::chapters(), $role);

        return $this->panel('admin/guide/index', lang('Admin.guideTitle'), [
            'chapters' => $chapters,
            'role'     => $role,
            'roleName' => StaffAccess::label($role),
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
            $chapter['open'] = in_array($role, $chapter['roles'], true);
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
