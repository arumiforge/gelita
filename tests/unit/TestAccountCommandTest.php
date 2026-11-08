<?php

use App\Services\TestAccountService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\GradeFiveWorld;

/**
 * `gelita:uji:akun` (research revision 10, part 2: 28 §13, K15,
 * §19.1 `TestAccountCommandTest`).
 *
 * @internal
 */
final class TestAccountCommandTest extends CIUnitTestCase
{
    use GradeFiveWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createGradeFiveWorld();
    }

    protected function tearDown(): void
    {
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testRefusesInProductionAndBesideRealStudents(): void
    {
        $service = new TestAccountService();

        $this->assertStringContainsString('production', (string) $service->refusal('production'));
        $this->assertStringContainsString('uji.', (string) $service->refusal('development'), 'the world has a real student, jaka5');

        $this->conn->table('game_sessions')->delete(['participant_id' => 1]);
        $this->conn->table('participants')->delete(['id' => 1]);
        $this->assertNull($service->refusal('development'));

        $this->conn->table('research_studies')->update(['status' => 'closed']);
        $this->assertStringContainsString('studi aktif', (string) $service->refusal('development'));
    }

    public function testCreatesTheDefaultListAndResetKeepsTheAccount(): void
    {
        $this->conn->table('game_sessions')->delete(['participant_id' => 1]);
        $this->conn->table('participants')->delete(['id' => 1]);
        $service = new TestAccountService();

        $rows = $service->create(TestAccountService::DEFAULT_NAMES);
        $this->assertCount(13, $rows);
        $this->assertSame(['uji.bahasa1', 'UJI-01', 'created'], [$rows[0]['username'], $rows[0]['code'], $rows[0]['status']]);
        $this->assertSame('UJI-13', $rows[12]['code']);
        $this->assertTrue((new \App\Libraries\PasswordPolicy())->check((string) $rows[0]['password'], 'uji.bahasa1')['acceptable']);

        $school = $this->conn->table('schools')->where('research_code', 'V01')->get()->getRowArray();
        $this->assertSame('Sekolah Uji Validator', $school['name']);
        $account = $this->conn->table('participants')->where('username', 'uji.bahasa1')->get()->getRowArray();
        $this->assertSame([(int) $school['id'], 'A', 11], [(int) $account['school_id'], $account['rombel'], (int) $account['age']]);
        $this->assertSame(\App\Services\ConsentService::ASSENT_STUDY, $account['assent_version'], 'research assent text (owner, 8 Nov 2026)');
        $this->assertSame(13, $this->conn->table('audit_logs')->where('action', 'test_account_created')->where('staff_user_id', null)->countAllResults());

        // Existing accounts are skipped
        $again = $service->create(['uji.bahasa1', 'uji.cadangan3']);
        $this->assertSame(['exists', 'created'], array_column($again, 'status'));
        $this->assertSame('UJI-14', $again[1]['code']);

        // --reset deletes the play data and keeps the account
        $id = (int) $account['id'];
        $this->conn->table('game_sessions')->insert(['session_code' => 'U1', 'participant_id' => $id, 'study_id' => 1, 'release_id' => 1]);
        $sid = (int) $this->conn->insertID();
        $this->conn->table('challenge_attempts')->insert(['session_id' => $sid, 'challenge_node_id' => 1, 'selected_item_ids_json' => '[]', 'status' => 'completed']);
        $this->conn->table('participant_progress')->insert(['participant_id' => $id, 'study_id' => 1, 'completed_nodes' => 1]);
        $this->conn->table('participant_seats')->insert(['participant_id' => $id, 'study_id' => 1, 'row_no' => 1, 'seat_no' => 1, 'booklet' => 'B1', 'pre_form' => 'A', 'post_form' => 'B']);

        $deleted = $service->reset('uji.bahasa1');

        $this->assertSame(1, $deleted['game_sessions']);
        foreach (['game_sessions', 'participant_progress', 'participant_seats'] as $table) {
            $this->assertSame(0, $this->conn->table($table)->where('participant_id', $id)->countAllResults(), $table);
        }
        $this->assertSame(0, $this->conn->table('challenge_attempts')->where('session_id', $sid)->countAllResults());
        $this->assertSame(1, $this->conn->table('participants')->where('id', $id)->countAllResults(), 'the account stays');
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'test_account_reset')->countAllResults());

        $this->expectException(\InvalidArgumentException::class);
        $service->reset('jaka5');
    }
}
