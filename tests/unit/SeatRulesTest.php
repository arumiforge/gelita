<?php

use App\Libraries\SeatRules;
use App\Models\FieldSessionModel;
use App\Models\ParticipantSeatModel;
use App\Services\SeatService;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Database\FieldWorld;

/**
 * Booklet and test form from row and seat (research revision 10, part 2:
 * 28 §11, K10, §19.1 `SeatRulesTest`).
 *
 * @internal
 */
final class SeatRulesTest extends CIUnitTestCase
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

    public function testBookletAndFormsFollowRowAndSeat(): void
    {
        $this->assertSame(['B1', 'B2', 'B3', 'B4'], [SeatRules::booklet(1, 1), SeatRules::booklet(1, 2), SeatRules::booklet(2, 1), SeatRules::booklet(2, 2)]);
        $this->assertSame(['pre' => 'A', 'post' => 'B'], SeatRules::forms(1, 1), 'row + seat even');
        $this->assertSame(['pre' => 'B', 'post' => 'A'], SeatRules::forms(1, 2), 'row + seat odd');

        $this->assertTrue(SeatRules::balance(['B1', 'B2', 'B3', 'B4', 'B1'])['balanced']);
        $this->assertFalse(SeatRules::balance(['B1', 'B1', 'B2'])['balanced'], 'B1 two, B3 none');
        $this->assertFalse(SeatRules::validNo(0));
        $this->assertFalse(SeatRules::validNo('x'));
        $this->assertTrue(SeatRules::validNo('12'));
    }

    public function testSavingComputesWarnsAndLocks(): void
    {
        $second = $this->student($this->conn, 'S03-A02', 'dewi');
        $fs     = $this->fieldSession($this->conn, 1, 'GHJKMN');
        $row    = model(FieldSessionModel::class)->find($fs['id']);
        $seats  = new SeatService();

        $result = $seats->save($row, [1 => ['row' => '1', 'seat' => '1'], $second => ['row' => '1', 'seat' => '3']], 2);
        $this->assertSame([2, 0], [$result['saved'], $result['changed']]);
        $this->assertSame('B1', model(ParticipantSeatModel::class)->bookletFor(1, 1));
        $this->assertSame('A', model(ParticipantSeatModel::class)->findFor(1, 1)['pre_form']);

        $summary = $seats->summary($seats->roster($row));
        $this->assertFalse($summary['balanced'], 'both B1: warn, but saved');
        $this->assertTrue($summary['all_have_form']);

        // A change is audited with old and new values
        $this->assertSame(1, $seats->save($row, [$second => ['row' => '2', 'seat' => '2']], 2)['changed']);
        $this->assertSame('B4', model(ParticipantSeatModel::class)->bookletFor($second, 1));
        $audit = $this->conn->table('audit_logs')->where('action', 'seat_changed')->get()->getRowArray();
        $this->assertStringContainsString('"from":{"row_no":1,"seat_no":3,"booklet":"B1"}', (string) $audit['metadata_json']);
        $this->assertSame(2, $this->conn->table('audit_logs')->where('action', 'seat_saved')->countAllResults());

        // Locked once the pre-test started (part 8 sets locked_at)
        $this->conn->table('participant_seats')->where('participant_id', 1)->update(['locked_at' => date('Y-m-d H:i:s')]);
        $locked = $seats->save($row, [1 => ['row' => '2', 'seat' => '1']], 2);
        $this->assertSame(['S03-A01'], $locked['locked']);
        $this->assertSame('B1', model(ParticipantSeatModel::class)->bookletFor(1, 1));

        $this->assertSame(['S03-A02'], $seats->save($row, [$second => ['row' => '99', 'seat' => '1']], 2)['invalid']);
        $this->assertNull(model(ParticipantSeatModel::class)->bookletFor(1, 999), 'no seat in another study');
    }
}
