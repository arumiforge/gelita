<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * The field session pages in the panel, walked as a researcher (research
 * revision 10, part 2: 28 §6, §11, §15; the panel half of the manual check
 * in §19.3).
 *
 * @internal
 */
final class FieldSessionPanelTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
        $_SESSION   = ['staff_id' => 2, 'staff_role' => 'peneliti', 'staff_last_seen' => time()];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testCreateShowSeatsCloseAndMakeup(): void
    {
        $this->send(Method::GET, 'admin/sesi-lapangan/baru')->assertOK();

        $created = $this->send(Method::POST, 'admin/sesi-lapangan/baru', [
            'school_id' => '1', 'rombel' => 'a', 'meeting_no' => '2', 'session_date' => '2030-01-15', 'opens' => '07:30', 'closes' => '09:30',
        ]);
        $id = (int) $this->conn->table('field_sessions')->where('code', 'S03-A-P2')->get()->getRow('id');
        $this->assertGreaterThan(0, $id);
        $this->assertRedirectsTo('admin/sesi-lapangan/' . $id, $created);

        $list = (string) $this->send(Method::GET, 'admin/sesi-lapangan')->getBody();
        $this->assertStringContainsString('S03-A-P2', $list);
        $this->assertStringNotContainsString('SD Lapangan', $list, 'codes only, never the school name');

        $token = (string) $this->conn->table('field_session_tokens')->where('field_session_id', $id)->get()->getRow('token');
        $this->assertStringContainsString($token, (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $id)->getBody());

        $seats = $this->send(Method::POST, 'admin/sesi-lapangan/' . $id . '/kursi', ['seats' => [1 => ['row' => '2', 'seat' => '1']]]);
        $this->assertRedirectsTo('admin/sesi-lapangan/' . $id . '/kursi', $seats);
        $this->assertStringContainsString('B3', (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $id . '/kursi')->getBody());

        $this->send(Method::POST, 'admin/sesi-lapangan/' . $id . '/tutup');
        $this->assertNotNull($this->conn->table('field_sessions')->where('id', $id)->get()->getRow('closed_at'));

        $form = (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $id . '/susulan')->getBody();
        $this->assertStringContainsString('S03-A01', $form);
        $this->assertMatchesRegularExpression('/value="1" checked/', $form, 'absent student starts ticked');

        $this->send(Method::POST, 'admin/sesi-lapangan/' . $id . '/susulan', ['session_date' => '2030-01-16', 'opens' => '07:30', 'closes' => '09:00', 'members' => ['1']]);
        $this->assertSame(1, $this->conn->table('field_sessions')->where('code', 'S03-A-P2-S')->countAllResults());
        $this->assertSame(1, $this->conn->table('field_session_members')->where('participant_id', 1)->countAllResults());
    }

    public function testTheAdminHasNoFieldSessionPages(): void
    {
        $_SESSION = ['staff_id' => 1, 'staff_role' => 'admin', 'staff_last_seen' => time()];

        $this->send(Method::GET, 'admin/pemantauan')->assertOK();

        // staffCan answers a missing capability as "not found"
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/sesi-lapangan');
    }

    public function testTheFieldModeSwitchIsLockedWhileASessionIsOpen(): void
    {
        $this->fieldSession($this->conn, 2, 'GHJKMN');
        $_SESSION = ['staff_id' => 3, 'staff_role' => 'ketua', 'staff_last_seen' => time()];
        $this->conn->table('staff_users')->insert(['id' => 3, 'username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1]);
        $study = $this->conn->table('research_studies')->get()->getRowArray();

        $this->send(Method::POST, 'admin/studi/' . $study['id'], [
            'code' => $study['code'], 'name' => $study['name'], 'status' => 'active', 'default_locale' => 'id',
            'unlock_mode' => 'sequential', 'meeting_plan' => 'lima_pertemuan', 'confirm_not_ready' => '1',   // field_mode unticked
        ]);

        $this->assertSame(lang('Admin.fieldModeLockedOpen'), $_SESSION['error'] ?? null);
        $this->assertSame('1', (string) $this->conn->table('research_studies')->get()->getRow('field_mode'));
    }
}
