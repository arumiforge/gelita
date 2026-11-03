<?php

use App\Models\ParticipantModel;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\FieldWorld;

/**
 * Who may use a token (research revision 10, part 2: 28 K8, K12, K20,
 * §19.1 `TokenMembershipTest`).
 *
 * @internal
 */
final class TokenMembershipTest extends CIUnitTestCase
{
    use FieldWorld;

    private \CodeIgniter\Database\BaseConnection $conn;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conn = $this->createFieldWorld();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $this->dropResetTables();
        parent::tearDown();
    }

    public function testAMainSessionTokenRefusesAnotherClass(): void
    {
        $this->fieldSession($this->conn, 2, 'GHJKMN');
        $otherRombel = $this->student($this->conn, 'S03-B01', 'budi', 1, 'B');
        $otherSchool = $this->student($this->conn, 'S04-A01', 'citra', 2, 'A');

        $service = service('fieldSessions');
        $this->assertSame('ok', $service->checkToken('GHJKMN', model(ParticipantModel::class)->find(1))['status']);
        $this->assertSame('other_class', $service->checkToken('GHJKMN', model(ParticipantModel::class)->find($otherRombel))['status']);
        $this->assertSame('other_class', $service->checkToken('GHJKMN', model(ParticipantModel::class)->find($otherSchool))['status']);
    }

    public function testAMakeupTokenOnlyLetsMembersInAndAbsentStudentsStartTicked(): void
    {
        $present = $this->student($this->conn, 'S03-A02', 'dewi');
        $absent  = $this->student($this->conn, 'S03-A03', 'eko');
        $main    = $this->fieldSession($this->conn, 2, 'GHJKMN', ['closed_at' => date('Y-m-d H:i:s', time() - 60)]);

        // Student 1 and dewi came to the main session; eko did not
        foreach ([1, $present] as $pid) {
            $this->conn->table('game_sessions')->insert([
                'session_code' => 'M-' . $pid, 'participant_id' => $pid, 'study_id' => 1, 'release_id' => 1, 'field_session_id' => $main['id'], 'meeting_no' => 2,
            ]);
        }

        $service = service('fieldSessions');
        $mainRow = model(\App\Models\FieldSessionModel::class)->find($main['id']);
        $this->assertSame([$absent], $service->defaultMakeupMembers($mainRow), 'only the absent student starts ticked');

        $makeup = $service->createMakeup($main['id'], ['session_date' => date('Y-m-d'), 'opens' => '00:00', 'closes' => '03:59'], [$absent], 2);
        $this->assertSame('S03-A-P2-S', $makeup['field_session']['code']);

        // The make-up opens now for the check
        $this->conn->table('field_sessions')->where('id', $makeup['field_session']['id'])->update([
            'opens_at' => date('Y-m-d H:i:s', time() - 60), 'closes_at' => date('Y-m-d H:i:s', time() + 3600),
        ]);
        $service->forget();

        $this->assertSame('ok', $service->checkToken($makeup['token'], model(ParticipantModel::class)->find($absent))['status']);
        $this->assertSame('not_member', $service->checkToken($makeup['token'], model(ParticipantModel::class)->find($present))['status']);
        $this->assertSame(1, $this->conn->table('audit_logs')->where('action', 'field_session_members_changed')->countAllResults());
    }

    public function testAnAccountFromAnotherStudyIsRefused(): void
    {
        $this->fieldSession($this->conn, 2, 'GHJKMN');
        $this->conn->table('research_studies')->insert(['code' => 'KG3A', 'name' => 'Uji kelompok kecil', 'status' => 'closed']);
        $this->conn->table('game_sessions')->insert(['session_code' => 'OLD', 'participant_id' => 1, 'study_id' => 2, 'release_id' => 1, 'status' => 'completed']);

        $this->assertSame('other_study', service('fieldSessions')->checkToken('GHJKMN', model(ParticipantModel::class)->find(1))['status']);
    }
}
