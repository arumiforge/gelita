<?php

use App\Libraries\AdminMenu;
use App\Libraries\StaffAccess;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Sidebar groups and sub-menu (staff panel guide pass, 16 November 2026):
 * no item a role lacks the right for, content folded for researchers unless
 * a content page is open, the field-session pages as a sub-menu with one
 * current page, and the staff guide for every role.
 *
 * @internal
 */
final class AdminMenuGroupsTest extends CIUnitTestCase
{
    public function testEveryRoleSeesOnlyItsItemsAndTheGuide(): void
    {
        foreach (StaffAccess::ROLES as $role) {
            $can    = static fn (string $capability): bool => StaffAccess::allows($role, $capability);
            $hrefs  = [];
            $groups = AdminMenu::groups($can, 'admin');

            foreach ($groups as $group) {
                foreach ($group['items'] as $item) {
                    $hrefs[] = $item['href'];
                }
            }

            $this->assertSame($hrefs, array_column(AdminMenu::items($can, 'admin'), 'href'), "{$role}: groups keep the menu order");
            $this->assertContains('admin/panduan-staf', $hrefs, $role);
            $this->assertNull($groups[0]['key'], "{$role}: Ringkasan first, without a group");
            $this->assertSame(count($groups), count(array_unique(array_column($groups, 'key'))), "{$role}: each group once");
        }

        $peneliti = static fn (string $capability): bool => StaffAccess::allows('peneliti', $capability);
        $hrefs    = array_column(AdminMenu::items($peneliti, 'admin'), 'href');

        foreach (['admin/data', 'admin/ekspor', 'admin/staf', 'admin/studi', 'admin/konten/impor-bank', 'admin/log-teknis'] as $hidden) {
            $this->assertNotContains($hidden, $hrefs, "peneliti: {$hidden}");
        }
    }

    public function testFieldWorkComesFirstAndContentFoldsForResearchers(): void
    {
        $peneliti = static fn (string $capability): bool => StaffAccess::allows('peneliti', $capability);
        $admin    = static fn (string $capability): bool => StaffAccess::allows('admin', $capability);

        $groups = $this->byKey(AdminMenu::groups($peneliti, 'admin'));
        $this->assertSame('Admin.groupField', array_keys($groups)[1]);
        $this->assertSame(['admin/sesi-lapangan', 'admin/pemantauan', 'admin/peserta', 'admin/peserta/reset-sandi'], array_column($groups['Admin.groupField']['items'], 'href'));
        $this->assertTrue($groups['Admin.groupField']['open']);
        $this->assertFalse($groups['Admin.groupContent']['open'], 'folded for a role that cannot edit content');

        $inside = $this->byKey(AdminMenu::groups($peneliti, 'admin/konten/narasi'));
        $this->assertTrue($inside['Admin.groupContent']['open'], 'open while one of its pages is open');

        $this->assertTrue($this->byKey(AdminMenu::groups($admin, 'admin'))['Admin.groupContent']['open'], 'the admin edits content');
    }

    public function testFieldSessionPagesBecomeASubMenu(): void
    {
        $peneliti = static fn (string $capability): bool => StaffAccess::allows('peneliti', $capability);
        $item     = $this->item(AdminMenu::groups($peneliti, 'admin/sesi-lapangan/7/kursi'), 'admin/sesi-lapangan');

        $this->assertTrue($item['active']);
        $this->assertSame(
            ['admin/sesi-lapangan/7', 'admin/sesi-lapangan/7/izin', 'admin/sesi-lapangan/7/kursi', 'admin/sesi-lapangan/7/lembar/unggah'],
            array_column($item['children'], 'href'),
        );
        $this->assertSame([false, false, true, false], array_column($item['children'], 'active'), 'one current page');

        $overview = $this->item(AdminMenu::groups($peneliti, 'admin/sesi-lapangan/7'), 'admin/sesi-lapangan');
        $this->assertSame([true, false, false, false], array_column($overview['children'], 'active'));

        $list = $this->item(AdminMenu::groups($peneliti, 'admin/sesi-lapangan'), 'admin/sesi-lapangan');
        $this->assertSame([], $list['children'], 'no sub-menu on the list page');
        $this->assertSame([], $this->item(AdminMenu::groups($peneliti, 'admin/sesi-lapangan/baru'), 'admin/sesi-lapangan')['children']);
    }

    /** @return array<string, array<string, mixed>> */
    private function byKey(array $groups): array
    {
        return array_column($groups, null, 'key');
    }

    /** @return array<string, mixed> */
    private function item(array $groups, string $href): array
    {
        foreach ($groups as $group) {
            foreach ($group['items'] as $item) {
                if ($item['href'] === $href) {
                    return $item;
                }
            }
        }

        $this->fail("{$href} missing");
    }
}
