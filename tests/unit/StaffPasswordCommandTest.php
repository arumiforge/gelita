<?php

use App\Libraries\PasswordPolicy;
use App\Models\StaffUserModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * `gelita:staff:password` — jalan keluar admin yang lupa sandi tanpa admin
 * lain. Sandi sementaranya dibuat oleh generator yang sama dengan reset di
 * `/admin/staf`. Test ini tidak memerlukan tabel staff_users.
 *
 * @internal
 */
final class StaffPasswordCommandTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testCommandIsDiscovered(): void
    {
        $commands = service('commands')->getCommands();

        $this->assertArrayHasKey('gelita:staff:password', $commands);
        $this->assertSame('App\Commands\StaffPassword', $commands['gelita:staff:password']['class']);
    }

    public function testCommandSyncsTimezone(): void
    {
        // pre_system (tempat zona waktu DB disetel) tidak terpicu di CLI
        $source = (string) file_get_contents(APPPATH . 'Commands/StaffPassword.php');

        $this->assertStringContainsString('db_sync_timezone()', $source);
    }

    public function testMissingUsernameShowsUsage(): void
    {
        command('gelita:staff:password');

        $this->assertStringContainsString('php spark gelita:staff:password <username>', $this->getStreamFilterBuffer());
    }

    public function testTemporaryPasswordMeetsPolicy(): void
    {
        $policy = new PasswordPolicy();
        $first  = StaffUserModel::temporaryPassword();

        $this->assertSame(16, strlen($first));
        $this->assertTrue($policy->check($first)['acceptable']);
        $this->assertNotSame($first, StaffUserModel::temporaryPassword());
    }

    public function testPanelAndCommandShareTheResetPath(): void
    {
        $controller = (string) file_get_contents(APPPATH . 'Controllers/Admin/StaffController.php');
        $command    = (string) file_get_contents(APPPATH . 'Commands/StaffPassword.php');

        $this->assertStringContainsString('resetToTemporary(', $controller);
        $this->assertStringContainsString('resetToTemporary(', $command);
    }
}
