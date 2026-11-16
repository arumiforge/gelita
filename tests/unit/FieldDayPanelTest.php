<?php

use CodeIgniter\HTTP\Method;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\FieldWorld;

/**
 * The staff panel on a visit day (field-day recommendations, 16 November
 * 2026): today's field sessions on Ringkasan, the printable session card,
 * the seat plan of the L2 page, and "Perlu perhatian" on Pemantauan. Codes
 * and tokens for researchers and the head researcher only (28 §12, K2).
 *
 * @internal
 */
final class FieldDayPanelTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    /** @var array{id: int, token: string} */
    private array $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();

        // Student 1 is in with lost records and a seat; S03-A02 never came and has no seat
        $this->student($this->conn, 'S03-A02', 'dewi');
        $this->fs = $this->fieldSession($this->conn, 1, 'GHJKMN');
        $this->conn->table('game_sessions')->insert([
            'session_code' => 'F1', 'participant_id' => 1, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $this->fs['id'],
            'meeting_no' => 1, 'status' => 'active', 'last_active_at' => date('Y-m-d H:i:s'),
        ]);
        $f1 = (int) $this->conn->insertID();
        $this->conn->table('game_event_logs')->insert([
            'event_uuid' => 'd1', 'client_event_id' => 'c1', 'session_id' => $f1, 'participant_id' => 1,
            'event_type' => 'events_dropped', 'payload_json' => '{"dropped":4}', 'occurred_at' => date('Y-m-d H:i:s'),
        ]);
        $this->conn->table('participant_seats')->insert([
            'participant_id' => 1, 'study_id' => 1, 'row_no' => 2, 'seat_no' => 3, 'booklet' => 'B3', 'pre_form' => 'B', 'post_form' => 'A',
        ]);
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testRingkasanShowsTodaysSessionsFirstForResearchers(): void
    {
        $this->as('peneliti', 2);
        $html = (string) $this->send(Method::GET, 'admin')->getBody();

        $this->assertStringContainsString(lang('Admin.fieldDayTitle'), $html);
        $this->assertStringContainsString('S03-A-P1', $html);
        $this->assertStringContainsString('GHJKMN', $html, 'the token');
        $this->assertMatchesRegularExpression('/<b>1<\/b> \/ 2/', $html, 'signed in / roster');
        $this->assertStringContainsString('admin/sesi-lapangan/' . $this->fs['id'] . '/cetak', $html);
        $this->assertLessThan(strpos($html, 'Siswa terdaftar'), strpos($html, lang('Admin.fieldDayTitle')), 'before the counts');
        $this->assertStringNotContainsString('SD Lapangan', $html);
    }

    public function testRingkasanSaysSoWhenNoSessionIsToday(): void
    {
        $this->conn->table('field_sessions')->update(['session_date' => '2030-01-15', 'opens_at' => '2030-01-15 07:30:00', 'closes_at' => '2030-01-15 09:30:00']);
        $this->as('ketua', 3);

        $html = (string) $this->send(Method::GET, 'admin')->getBody();
        $this->assertStringContainsString(lang('Admin.fieldDayNone'), $html);
        $this->assertStringNotContainsString('GHJKMN', $html);
    }

    public function testTheAdminRingkasanHasNoTokensOrCodes(): void
    {
        $this->as('admin', 1);
        $html = (string) $this->send(Method::GET, 'admin')->getBody();

        $this->assertStringNotContainsString(lang('Admin.fieldDayTitle'), $html);
        $this->assertStringNotContainsString('GHJKMN', $html);
        $this->assertStringNotContainsString('S03-A0', $html);
    }

    public function testPrintableCardHasCodesTokenAndAnEmptyPlan(): void
    {
        $this->as('peneliti', 2);
        $this->assertStringContainsString('/cetak', (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs['id'])->getBody(), 'linked from the session page');

        $html = (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs['id'] . '/cetak')->getBody();
        $this->assertStringContainsString('S03-A-P1', $html);
        $this->assertStringContainsString('GHJKMN', $html);
        $this->assertStringContainsString('S03', $html);
        $this->assertSame(30, substr_count($html, 'class="print-seat"'), 'default 5 rows × 6 seats');
        $this->assertMatchesRegularExpression('/Baris 2 (·|&middot;) Kursi 4<\/span>\s*<b class="print-seat-booklet">B4</', $html);
        $this->assertStringContainsString(lang('Admin.printCardTest_pre'), $html, 'P1 holds the pre-test');

        $p2 = $this->fieldSession($this->conn, 2, 'PQRSTU');
        $later = (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $p2['id'] . '/cetak')->getBody();
        $this->assertStringContainsString('Temanggung', $later, 'region name');
        $this->assertStringContainsString('print-seat-booklet is-blank', $later, 'after P1 the booklet follows the student, not the seat');
        $this->assertDoesNotMatchRegularExpression('/print-seat-booklet">B[1-4]</', $later);
        $this->assertStringContainsString('S03-A01</span> <b>B3</b>', $html, 'roster codes with their booklet');
        $this->assertStringNotContainsString('SD Lapangan', $html, 'codes only');
        $this->assertStringNotContainsString('DEWI', $html, 'never names');
        $this->assertStringNotContainsString('admin-sidebar', $html, 'only the card prints');
    }

    public function testPrintableCardIsResearchersOnly(): void
    {
        $this->as('admin', 1);
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs['id'] . '/cetak');
    }

    public function testSeatPageDrawsThePlanAndKeepsTheForm(): void
    {
        $this->as('peneliti', 2);
        $html = (string) $this->send(Method::GET, 'admin/sesi-lapangan/' . $this->fs['id'] . '/kursi')->getBody();

        $this->assertStringContainsString('data-seat-plan', $html);
        $this->assertSame(6, substr_count($html, 'class="seat-cell is-'), '2 rows × 3 seats');
        $this->assertMatchesRegularExpression('/seat-cell is-b3">\s*<b class="seat-cell-booklet">B3<\/b>\s*<span class="mono">S03-A01<\/span>/', $html);
        $this->assertMatchesRegularExpression('/data-seat-unseated>.*S03-A02/s', $html, 'no seat yet');
        $this->assertStringContainsString('name="seats[1][row]"', $html, 'form fields unchanged');
    }

    public function testMonitoringListsTheCodesBehindEachAmberCount(): void
    {
        $this->as('peneliti', 2);
        $html = (string) $this->send(Method::GET, 'admin/pemantauan')->getBody();

        $this->assertStringContainsString(lang('Admin.attentionTitle'), $html);
        $this->assertStringContainsString('data-alert-sound', $html, 'opt-in alert sound');
        $this->assertMatchesRegularExpression('/data-monitoring-initial>\[\{.*"participant_code":"S03-A02"/s', $html, 'baseline for the first refresh');
        $this->assertMatchesRegularExpression('/data-m="list-not-entered">S03-A02</', $html);
        $this->assertMatchesRegularExpression('/data-m="list-dropped">S03-A01 \(4\)</', $html);
        $this->assertMatchesRegularExpression('/data-m="list-no-seat">S03-A02</', $html);

        $this->as('admin', 1);
        $admin = (string) $this->send(Method::GET, 'admin/pemantauan')->getBody();
        $this->assertStringNotContainsString(lang('Admin.attentionTitle'), $admin, 'admin: numbers only (K2)');
        $this->assertStringNotContainsString('S03-A0', $admin, 'the baseline carries no codes for the admin');
        $this->assertStringContainsString('data-alert-sound', $admin, 'the admin may hear rising counts too');
    }

    private function as(string $role, int $id): void
    {
        if ($id === 3 && $this->conn->table('staff_users')->where('id', 3)->countAllResults() === 0) {
            $this->conn->table('staff_users')->insert(['id' => 3, 'username' => 'ketua_uji', 'password_hash' => 'x', 'role' => 'ketua', 'display_name' => 'Ketua', 'is_active' => 1]);
        }

        $_SESSION = ['staff_id' => $id, 'staff_role' => $role, 'staff_last_seen' => time()];
    }
}
